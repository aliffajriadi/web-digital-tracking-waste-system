<?php

namespace Tests\Feature\SampahKeluar;

use App\Models\WasteOutData;
use App\Models\DataWasteOut;
use App\Models\WasteSellingData;
use App\Models\DataCollectorBuyer;
use App\Models\WasteOutMethod;
use App\Models\WasteDestinations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampahKeluarTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPER: setup semua prerequisite waste out via API
    // =========================================================================
    private function setupWasteOutPrerequisites(): array
    {
        $setup = $this->setupMasterData();
        $pic   = $this->createPic();

        // Method selling (id=1 setelah data ada)
        $sellingMethod = WasteOutMethod::create([
            'name'        => 'Penjualan',
            'description' => 'Dijual ke pengepul',
        ]);

        $buyer = DataCollectorBuyer::create([
            'name'         => 'Bapak Budi',
            'phone_number' => '08123456789',
            'address'      => 'Batam Center',
            'email'        => 'budi@example.com',
        ]);

        return array_merge($setup, compact('pic', 'sellingMethod', 'buyer'));
    }

    // =========================================================================
    // TC-018 - Sampah keluar berhasil via API (method non-penjualan)
    // =========================================================================
    public function test_tc018_pic_can_store_waste_out_via_api(): void
    {
        // Arrange
        $setup = $this->setupWasteOutPrerequisites();
        $pic   = $setup['pic'];

        // Gunakan method Landfill (bukan selling)
        $landfillMethod = $setup['method']; // method dari setupMasterData
        $destination    = $setup['destination'];
        $subCategory    = $setup['subCategory'];

        $items = json_encode([
            [
                'id_sub_category' => $subCategory->id,
                'quantity'        => 25.0,
            ]
        ]);

        // Act
        $response = $this->actingAs($pic, 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method'  => $landfillMethod->id,
            'id_waste_destination' => $destination->id,
            'notes'                => 'Keluar ke TPA',
            'created_at'           => now()->format('Y-m-d H:i:s'),
            'items'                => $items,
        ]);

        // Assert
        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('waste_out_data', [
            'id_waste_out_method' => $landfillMethod->id,
            'notes'               => 'Keluar ke TPA',
        ]);

        $this->assertDatabaseHas('data_waste_out', [
            'is_processed_waste'    => false,
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 25.0,
        ]);
    }

    // =========================================================================
    // TC-018b - Sampah keluar berhasil: method penjualan dengan data buyer
    // =========================================================================
    public function test_tc018b_waste_out_with_selling_method_stores_selling_data(): void
    {
        // Arrange
        $setup          = $this->setupWasteOutPrerequisites();
        $pic            = $setup['pic'];
        $sellingMethod  = $setup['sellingMethod'];
        $buyer          = $setup['buyer'];
        $destination    = $setup['destination'];
        $subCategory    = $setup['subCategory'];

        $items = json_encode([
            [
                'id_sub_category' => $subCategory->id,
                'quantity'        => 15.0,
            ]
        ]);

        // Act
        $response = $this->actingAs($pic, 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method'  => $sellingMethod->id,
            'id_waste_destination' => $destination->id,
            'notes'                => 'Dijual ke pengepul',
            'created_at'           => now()->format('Y-m-d H:i:s'),
            'items'                => $items,
            'id_buyer'             => $buyer->id,
            'total_revenue'        => 500000,
        ]);

        // Assert
        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $wasteOut = WasteOutData::first();
        $this->assertNotNull($wasteOut);

        $this->assertDatabaseHas('waste_selling_data', [
            'id_waste_out_data' => $wasteOut->id,
            'id_buyer'          => $buyer->id,
            'total_revenue'     => 500000,
        ]);
    }

    // =========================================================================
    // TC-018c - Sampah keluar berhasil via Admin Web Panel
    // =========================================================================
    public function test_tc018c_admin_can_store_waste_out_via_web_panel(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        $setup = $this->setupMasterData();

        // Buat method 'Penjualan' terlebih dahulu agar dapat id=1
        // (karena controller hard-code: if id_waste_out_method == 1 → require buyer)
        $sellingMethod = WasteOutMethod::create(['name' => 'Penjualan', 'description' => 'Dijual']);

        // Buat method Landfill setelah Penjualan agar id != 1
        $landfillMethod = WasteOutMethod::create(['name' => 'Landfill', 'description' => 'TPA']);

        $destination = $setup['destination'];
        $subCategory = $setup['subCategory'];

        // Pastikan landfill method bukan id=1
        $this->assertNotEquals(1, $landfillMethod->id);

        // Act: gunakan Landfill (id != 1) agar tidak perlu buyer
        $response = $this->actingAs($admin)->post(route('admin.waste-out.store'), [
            'id_waste_out_method'  => $landfillMethod->id,
            'id_waste_destination' => $destination->id,
            'notes'                => 'Keluar via admin web',
            'items' => [
                [
                    'is_processed'          => 0,
                    'id_waste_sub_category' => $subCategory->id,
                    'measured_qty'          => 30.0,
                ]
            ],
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('waste_out_data', [
            'id_waste_out_method' => $landfillMethod->id,
        ]);
        $this->assertDatabaseHas('data_waste_out', [
            'measured_qty' => 30.0,
        ]);
    }

    // =========================================================================
    // TC-021 - Sampah keluar gagal: validasi field wajib kosong
    // =========================================================================
    public function test_tc021_waste_out_fails_without_required_fields(): void
    {
        // Arrange
        $pic = $this->createPic();

        // Act: kirim tanpa field apapun
        $response = $this->actingAs($pic, 'sanctum')->postJson('/api/waste-out', []);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'id_waste_out_method',
                     'created_at',
                     'items',
                 ]);

        $this->assertDatabaseCount('waste_out_data', 0);
    }

    // =========================================================================
    // TC-021b - Sampah keluar gagal: id_waste_out_method tidak ada
    // =========================================================================
    public function test_tc021b_waste_out_fails_with_nonexistent_method(): void
    {
        // Arrange
        $setup = $this->setupMasterData();
        $pic   = $setup['pic'] ?? $this->createPic();

        $items = json_encode([
            ['id_sub_category' => $setup['subCategory']->id, 'quantity' => 10.0]
        ]);

        // Act
        $response = $this->actingAs($this->createPic('pic@test.com'), 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method'  => 99999, // tidak ada
            'created_at'           => now()->format('Y-m-d H:i:s'),
            'items'                => $items,
        ]);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_waste_out_method']);
    }

    // =========================================================================
    // TC-021c - Sampah keluar gagal: items bukan JSON yang valid
    // =========================================================================
    public function test_tc021c_waste_out_fails_if_items_is_not_valid_json(): void
    {
        // Arrange
        $setup  = $this->setupMasterData();
        $pic    = $this->createPic();
        $method = $setup['method'];

        // Act
        $response = $this->actingAs($pic, 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method' => $method->id,
            'created_at'          => now()->format('Y-m-d H:i:s'),
            'items'               => 'ini bukan json', // invalid JSON
        ]);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['items']);
    }

    // =========================================================================
    // TC-021d - Sampah keluar: endpoint butuh autentikasi sanctum
    // =========================================================================
    public function test_tc021d_waste_out_api_requires_authentication(): void
    {
        // Act: tanpa auth
        $response = $this->postJson('/api/waste-out', [
            'id_waste_out_method' => 1,
            'created_at'          => now()->format('Y-m-d H:i:s'),
            'items'               => json_encode([]),
        ]);

        // Assert
        $response->assertStatus(401);
    }
}
