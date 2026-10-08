<?php

namespace Tests\Feature\Admin;

use App\Models\CategoryReport;
use App\Models\DataCollectorBuyer;
use App\Models\DataWasteOut;
use App\Models\ProcessedWasteData;
use App\Models\Report;
use App\Models\WasteB3Detail;
use App\Models\WasteEntry;
use App\Models\WasteOutData;
use App\Models\WasteOutMethod;
use App\Models\WasteRawMaterials;
use App\Models\WasteSellingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    private function seedData(): array
    {
        $setup = $this->setupMasterData();
        $admin = $this->createAdmin();
        $pic = $this->createPic();

        $b3 = WasteB3Detail::create(['waste_code' => 'B105d', 'description' => 'Oli bekas', 'retention_period_day' => 2, 'danger_level' => 4]);
        $setup['subCategory']->update(['id_waste_b3_detail' => $b3->id]);

        $entry = WasteEntry::create([
            'id_user' => $pic->id,
            'id_waste_sub_category' => $setup['subCategory']->id,
            'id_source_location_waste' => $setup['location']->id,
            'measured_qty' => 40,
            'created_at' => now()->subDays(5),
        ]);

        $selling = WasteOutMethod::create(['name' => 'Dijual', 'is_selling' => true]);
        $buyer = DataCollectorBuyer::create(['name' => 'Budi', 'phone_number' => '08', 'address' => 'Batam', 'email' => 'b@x.com']);
        $out = WasteOutData::create(['id_user' => $pic->id, 'id_waste_out_method' => $selling->id, 'id_waste_destination' => null]);
        DataWasteOut::create(['id_waste_out_data' => $out->id, 'is_processed_waste' => false, 'id_waste_sub_category' => $setup['subCategory']->id, 'measured_qty' => 5]);
        WasteSellingData::create(['id_waste_out_data' => $out->id, 'id_buyer' => $buyer->id, 'total_revenue' => 10000]);

        $processed = ProcessedWasteData::create(['id_user' => $pic->id, 'id_processed_waste' => $setup['processedWaste']->id, 'measured_qty' => 3]);
        WasteRawMaterials::create(['id_processed_waste_data' => $processed->id, 'id_waste_sub_category' => $setup['subCategory']->id, 'measured_qty' => 6]);

        $category = CategoryReport::create(['name' => 'Alat rusak']);
        $report = Report::create(['id_user' => $pic->id, 'id_category_report' => $category->id, 'title' => 'Timbangan', 'content' => 'Mati']);

        return compact('admin', 'pic', 'entry', 'out', 'processed', 'report');
    }

    public function test_all_admin_pages_render(): void
    {
        $d = $this->seedData();

        $routes = [
            route('admin.dashboard'),
            route('admin.stock.index'),
            route('admin.stock.index', ['status' => 'b3']),
            route('admin.waste-entry.index', ['search' => 'Sisa', 'date_from' => now()->subMonth()->toDateString()]),
            route('admin.waste-entry.show', $d['entry']),
            route('admin.waste-out.index', ['create' => 1]),
            route('admin.waste-out.show', $d['out']),
            route('admin.processed-waste-data.index'),
            route('admin.processed-waste-data.create'),
            route('admin.processed-waste-data.show', $d['processed']),
            route('admin.report.index'),
            route('admin.report.export.pdf'),
            route('admin.pic-report.index'),
            route('admin.pic-report.show', $d['report']),
            route('admin.waste-category.index'),
            route('admin.waste-subcategory.index', ['status' => 'active']),
            route('admin.waste-b3.index', ['search' => 'B1']),
            route('admin.processed-waste.index'),
            route('admin.unit-measured.index'),
            route('admin.source-location.index'),
            route('admin.collector-buyer.index'),
            route('admin.waste-out-method.index'),
            route('admin.category-report.index'),
            route('admin.users.index', ['status' => 'active']),
            route('admin.profile'),
        ];

        foreach ($routes as $url) {
            $response = $this->actingAs($d['admin'])->get($url);
            $this->assertSame(200, $response->status(), "Halaman gagal dirender: {$url} — " . $response->exception?->getMessage());
        }
    }

    public function test_processed_detail_shows_raw_materials(): void
    {
        $d = $this->seedData();

        $this->actingAs($d['admin'])
            ->get(route('admin.processed-waste-data.show', $d['processed']))
            ->assertOk()
            ->assertSee('Pupuk Kompos')
            ->assertSee('Sisa Makanan');
    }

    public function test_stock_page_shows_remaining_stock_and_b3_alert(): void
    {
        $d = $this->seedData();

        // 40 masuk - 5 keluar - 6 diolah = 29
        $this->actingAs($d['admin'])
            ->get(route('admin.stock.index'))
            ->assertOk()
            ->assertSee('29 kg')
            ->assertSee('B105d');
    }

    public function test_admin_waste_out_rejects_exceeding_stock(): void
    {
        $setup = $this->setupMasterData();
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.waste-out.store'), [
            'id_waste_out_method' => $setup['method']->id,
            'items' => [['is_processed' => 0, 'id_waste_sub_category' => $setup['subCategory']->id, 'measured_qty' => 10]],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseCount('waste_out_data', 0);
    }

    public function test_cannot_delete_subcategory_used_in_transactions(): void
    {
        $d = $this->seedData();
        $sub = WasteEntry::first()->subCategory;

        $this->actingAs($d['admin'])
            ->delete(route('admin.waste-subcategory.destroy', $sub))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('waste_sub_category', ['id' => $sub->id]);
    }

    public function test_cannot_delete_pic_with_transactions_and_deactivation_revokes_tokens(): void
    {
        $d = $this->seedData();
        $d['pic']->createToken('mobile_token');

        $this->actingAs($d['admin'])->delete(route('admin.users.destroy', $d['pic']))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $d['pic']->id]);

        $this->actingAs($d['admin'])->patch(route('admin.users.toggle-status', $d['pic']))->assertSessionHas('success');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_admin_account_cannot_be_modified_from_pic_page(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->patch(route('admin.users.toggle-status', $admin))->assertNotFound();
    }

    public function test_source_location_address_is_saved(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.source-location.store'), [
            'name' => 'Gedung Utama',
            'address' => 'Jl. Ahmad Yani',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('source_location_waste', ['name' => 'Gedung Utama', 'address' => 'Jl. Ahmad Yani']);
    }

    public function test_pic_nik_must_be_unique(): void
    {
        $admin = $this->createAdmin();
        $this->createPic('a@test.com', 'password', '555');

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'full_name' => 'Dobel',
            'nik' => '555',
            'email' => 'b@test.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('nik');
    }

    public function test_waste_entry_search_keeps_date_filter(): void
    {
        $d = $this->seedData();

        // Entri dibuat 5 hari lalu; filter mulai hari ini tidak boleh menampilkannya walau nama cocok
        $this->actingAs($d['admin'])
            ->get(route('admin.waste-entry.index', ['search' => 'Sisa', 'date_from' => now()->toDateString()]))
            ->assertOk()
            ->assertDontSee(route('admin.waste-entry.show', $d['entry']));
    }
}
