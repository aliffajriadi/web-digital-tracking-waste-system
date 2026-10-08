<?php

namespace Tests\Feature\Api;

use App\Models\DataCollectorBuyer;
use App\Models\IotAuthSession;
use App\Models\Report;
use App\Models\CategoryReport;
use App\Models\WasteEntry;
use App\Models\WasteOutMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aturan bisnis & keamanan data API mobile.
 */
class BusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_processed_waste_rejected_when_raw_material_exceeds_stock(): void
    {
        $setup = $this->setupMasterData();
        $pic = $this->createPic();
        $this->seedStock($pic, $setup['subCategory'], 3);

        $this->actingAs($pic, 'sanctum')->postJson('/api/processed-waste-data', [
            'id_processed_waste' => $setup['processedWaste']->id,
            'measured_qty' => 2,
            'raw_materials' => json_encode([['id_waste_sub_category' => $setup['subCategory']->id, 'measured_qty' => 5]]),
        ])->assertStatus(422)->assertJsonValidationErrors(['raw_materials']);

        $this->assertDatabaseCount('processed_waste_data', 0);
    }

    public function test_processed_stock_cannot_go_out_more_than_produced(): void
    {
        $setup = $this->setupMasterData();
        $pic = $this->createPic();

        $this->actingAs($pic, 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method' => $setup['method']->id,
            'items' => json_encode([['id_sub_category' => 'p_' . $setup['processedWaste']->id, 'quantity' => 1]]),
        ])->assertStatus(422)->assertJsonValidationErrors(['items']);
    }

    public function test_same_item_twice_is_summed_for_stock_check(): void
    {
        $setup = $this->setupMasterData();
        $pic = $this->createPic();
        $this->seedStock($pic, $setup['subCategory'], 10);

        $this->actingAs($pic, 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method' => $setup['method']->id,
            'items' => json_encode([
                ['id_sub_category' => $setup['subCategory']->id, 'quantity' => 6],
                ['id_sub_category' => $setup['subCategory']->id, 'quantity' => 6],
            ]),
        ])->assertStatus(422);
    }

    public function test_selling_method_requires_buyer_and_revenue(): void
    {
        $setup = $this->setupMasterData();
        $pic = $this->createPic();
        $this->seedStock($pic, $setup['subCategory'], 10);
        $selling = WasteOutMethod::create(['name' => 'Dijual', 'is_selling' => true]);

        $this->actingAs($pic, 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method' => $selling->id,
            'items' => json_encode([['id_sub_category' => $setup['subCategory']->id, 'quantity' => 2]]),
        ])->assertStatus(422)->assertJsonValidationErrors(['id_buyer', 'total_revenue']);
    }

    public function test_waste_out_accepts_comma_decimal_revenue(): void
    {
        $setup = $this->setupMasterData();
        $pic = $this->createPic();
        $this->seedStock($pic, $setup['subCategory'], 10);
        $selling = WasteOutMethod::create(['name' => 'Dijual', 'is_selling' => true]);
        $buyer = DataCollectorBuyer::create(['name' => 'Budi', 'phone_number' => '08', 'address' => 'Batam', 'email' => 'b@x.com']);

        $this->actingAs($pic, 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method' => $selling->id,
            'id_buyer' => $buyer->id,
            'total_revenue' => '15000,50',
            'items' => json_encode([['id_sub_category' => $setup['subCategory']->id, 'quantity' => 2.5]]),
        ])->assertCreated();

        $this->assertDatabaseHas('waste_selling_data', ['id_buyer' => $buyer->id, 'total_revenue' => 15000.50]);
    }

    public function test_future_transaction_time_is_rejected(): void
    {
        $setup = $this->setupMasterData();
        $pic = $this->createPic();

        $this->actingAs($pic, 'sanctum')->postJson('/api/waste-entry', [
            'id_waste_sub_category' => $setup['subCategory']->id,
            'id_source_location_waste' => $setup['location']->id,
            'measured_qty' => 5,
            'created_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertStatus(422)->assertJsonValidationErrors(['created_at']);
    }

    public function test_zero_or_negative_quantity_is_rejected(): void
    {
        $setup = $this->setupMasterData();
        $pic = $this->createPic();

        foreach ([0, -3] as $qty) {
            $this->actingAs($pic, 'sanctum')->postJson('/api/waste-entry', [
                'id_waste_sub_category' => $setup['subCategory']->id,
                'id_source_location_waste' => $setup['location']->id,
                'measured_qty' => $qty,
            ])->assertStatus(422)->assertJsonValidationErrors(['measured_qty']);
        }
    }

    public function test_inactive_sub_category_cannot_receive_entries(): void
    {
        $setup = $this->setupMasterData();
        $pic = $this->createPic();
        $setup['subCategory']->update(['is_active' => false]);

        $this->actingAs($pic, 'sanctum')->postJson('/api/waste-entry', [
            'id_waste_sub_category' => $setup['subCategory']->id,
            'id_source_location_waste' => $setup['location']->id,
            'measured_qty' => 5,
        ])->assertStatus(422)->assertJsonValidationErrors(['id_waste_sub_category']);
    }

    public function test_history_search_does_not_leak_other_users_waste_out(): void
    {
        $setup = $this->setupMasterData();
        $me = $this->createPic('me@test.com', 'password', '111');
        $other = $this->createPic('other@test.com', 'password', '222');
        $this->seedStock($other, $setup['subCategory'], 10);

        $this->actingAs($other, 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method' => $setup['method']->id,
            'items' => json_encode([['id_sub_category' => $setup['subCategory']->id, 'quantity' => 2]]),
        ])->assertCreated();

        $response = $this->actingAs($me, 'sanctum')
            ->getJson('/api/riwayat-laporan?type=2&search=' . urlencode($setup['subCategory']->name))
            ->assertOk();

        $this->assertSame(0, $response->json('total'));
    }

    public function test_pic_cannot_view_other_users_entry_detail(): void
    {
        $setup = $this->setupMasterData();
        $me = $this->createPic('me@test.com', 'password', '111');
        $other = $this->createPic('other@test.com', 'password', '222');
        $entry = WasteEntry::create([
            'id_user' => $other->id,
            'id_waste_sub_category' => $setup['subCategory']->id,
            'measured_qty' => 5,
        ]);

        $this->actingAs($me, 'sanctum')->getJson("/api/laporan-harian/{$entry->id}")->assertNotFound();
        $this->actingAs($other, 'sanctum')->getJson("/api/laporan-harian/{$entry->id}")->assertOk();
    }

    public function test_pic_cannot_view_other_users_report_detail(): void
    {
        $me = $this->createPic('me@test.com', 'password', '111');
        $other = $this->createPic('other@test.com', 'password', '222');
        $category = CategoryReport::create(['name' => 'Alat rusak']);
        $report = Report::create([
            'id_user' => $other->id,
            'id_category_report' => $category->id,
            'title' => 'Timbangan rusak',
            'content' => 'Tidak menyala',
        ]);

        $this->actingAs($me, 'sanctum')->getJson("/api/laporan-kendala/{$report->id}")->assertNotFound();
    }

    public function test_waste_out_detail_is_owner_only(): void
    {
        $setup = $this->setupMasterData();
        $me = $this->createPic('me@test.com', 'password', '111');
        $other = $this->createPic('other@test.com', 'password', '222');
        $this->seedStock($other, $setup['subCategory'], 10);

        $id = $this->actingAs($other, 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method' => $setup['method']->id,
            'items' => json_encode([['id_sub_category' => $setup['subCategory']->id, 'quantity' => 2]]),
        ])->json('data.id');

        $this->actingAs($other, 'sanctum')->getJson("/api/waste-out/{$id}")
            ->assertOk()
            ->assertJsonPath('data.items.0.quantity', 2);
        $this->actingAs($me, 'sanctum')->getJson("/api/waste-out/{$id}")->assertNotFound();
    }

    public function test_deactivated_pic_token_is_rejected(): void
    {
        $pic = $this->createPic();
        $token = $pic->createToken('mobile_token')->plainTextToken;
        $pic->update(['is_active' => false]);

        $this->withToken($token)->getJson('/api/dashboard-data')->assertUnauthorized();
    }

    public function test_admin_account_cannot_use_mobile_api(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'sanctum')->getJson('/api/dashboard-data')->assertUnauthorized();
    }

    public function test_mobile_login_with_nik(): void
    {
        $this->createPic('pic@test.com', 'rahasia123', '9988');

        $this->postJson('/api/login', ['nik' => '9988', 'password' => 'rahasia123'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'full_name', 'nik']]);

        $this->postJson('/api/login', ['nik' => '9988', 'password' => 'salah'])->assertUnauthorized();
        $this->postJson('/api/login', ['nik' => '0000', 'password' => 'rahasia123'])->assertUnauthorized();
    }

    public function test_logout_unpairs_iot_device(): void
    {
        $pic = $this->createPic();
        $token = $pic->createToken('mobile_token')->plainTextToken;
        IotAuthSession::create(['code' => 'AB12', 'status' => 'paired', 'id_user' => $pic->id]);

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseMissing('iot_auth_sessions', ['code' => 'AB12']);
    }

    public function test_dashboard_returns_today_weight_totals(): void
    {
        $setup = $this->setupMasterData();
        $pic = $this->createPic();
        $this->seedStock($pic, $setup['subCategory'], 12.5);
        $this->seedStock($pic, $setup['subCategory'], 7.5);

        $this->actingAs($pic, 'sanctum')->getJson('/api/dashboard-data')
            ->assertOk()
            ->assertJsonPath('today_summary.total_masuk', 2)
            ->assertJsonPath('today_summary.berat_masuk', 20);
    }
}
