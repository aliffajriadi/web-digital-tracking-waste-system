<?php

namespace Tests\Feature\Stok;

use App\Models\WasteEntry;
use App\Models\WasteOutData;
use App\Models\DataWasteOut;
use App\Models\ProcessedWasteData;
use App\Models\WasteRawMaterials;
use App\Models\WasteOutMethod;
use App\Models\WasteDestinations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stok dalam aplikasi ini bukan tabel "stok" tersendiri, melainkan
 * dihitung dari selisih waste_entry (masuk) vs data_waste_out (keluar).
 * Test ini memverifikasi integritas data stok melalui transaksi.
 */
class StokTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // TC-014 (Stok) - Setelah sampah masuk, qty terakumulasi di database
    // =========================================================================
    public function test_stok_increases_after_waste_entry(): void
    {
        // Arrange
        $setup = $this->setupMasterData();
        $pic   = $this->createPic();

        // Act: simpan 3 entri sampah masuk
        WasteEntry::create([
            'id_user'               => $pic->id,
            'id_waste_sub_category' => $setup['subCategory']->id,
            'measured_qty'          => 30.0,
        ]);
        WasteEntry::create([
            'id_user'               => $pic->id,
            'id_waste_sub_category' => $setup['subCategory']->id,
            'measured_qty'          => 20.0,
        ]);

        // Assert: total stok masuk = 50 kg
        $totalIn = WasteEntry::where('id_waste_sub_category', $setup['subCategory']->id)
                             ->sum('measured_qty');
        $this->assertEquals(50.0, $totalIn);
    }

    // =========================================================================
    // TC-017 (Stok Pengolahan) - Stok raw material tersimpan per pengolahan
    // =========================================================================
    public function test_stok_raw_materials_recorded_per_processing(): void
    {
        // Arrange
        $setup = $this->setupMasterData();
        $pic   = $this->createPic();

        $pwd = ProcessedWasteData::create([
            'id_user'            => $pic->id,
            'id_processed_waste' => $setup['processedWaste']->id,
            'measured_qty'       => 15.0,
        ]);

        // Act: catat bahan baku yang digunakan
        WasteRawMaterials::create([
            'id_processed_waste_data' => $pwd->id,
            'id_waste_sub_category'   => $setup['subCategory']->id,
            'measured_qty'            => 20.0,
        ]);

        // Assert: total bahan baku yang dikonsumsi dalam pengolahan ini
        $totalRaw = WasteRawMaterials::where('id_processed_waste_data', $pwd->id)
                                      ->sum('measured_qty');
        $this->assertEquals(20.0, $totalRaw);
    }

    // =========================================================================
    // TC-021 (Stok) - Sampah keluar melebihi stok ditolak oleh API
    // =========================================================================
    public function test_stok_out_exceeding_stock_is_rejected(): void
    {
        // Arrange: stok masuk hanya 10 kg
        $setup = $this->setupMasterData();
        $pic   = $this->createPic('pic@test.com');
        $this->seedStock($pic, $setup['subCategory'], 10.0);

        // Act: coba keluarkan 100 kg (melebihi stok)
        $response = $this->actingAs($pic, 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method'  => $setup['method']->id,
            'id_waste_destination' => $setup['destination']->id,
            'items' => json_encode([['id_sub_category' => $setup['subCategory']->id, 'quantity' => 100]]),
        ]);

        // Assert: ditolak dan tidak ada data yang tersimpan
        $response->assertStatus(422)->assertJsonValidationErrors(['items']);
        $this->assertDatabaseCount('waste_out_data', 0);
        $this->assertDatabaseCount('data_waste_out', 0);
    }

    // =========================================================================
    // TC-021b (Stok) - Endpoint stok menghitung masuk - keluar - diolah
    // =========================================================================
    public function test_stock_endpoint_calculates_remaining_stock(): void
    {
        $setup = $this->setupMasterData();
        $pic   = $this->createPic();
        $this->seedStock($pic, $setup['subCategory'], 30);

        $this->actingAs($pic, 'sanctum')->postJson('/api/waste-out', [
            'id_waste_out_method' => $setup['method']->id,
            'items' => json_encode([['id_sub_category' => $setup['subCategory']->id, 'quantity' => 5]]),
        ])->assertCreated();

        $this->actingAs($pic, 'sanctum')->postJson('/api/processed-waste-data', [
            'id_processed_waste' => $setup['processedWaste']->id,
            'measured_qty'       => 4,
            'raw_materials'      => json_encode([['id_waste_sub_category' => $setup['subCategory']->id, 'measured_qty' => 10]]),
        ])->assertCreated();

        $data = collect($this->actingAs($pic, 'sanctum')->getJson('/api/waste-stocks')->assertOk()->json('data'));

        $raw = $data->firstWhere('id', $setup['subCategory']->id);
        $this->assertEquals(15.0, $raw['stock']); // 30 - 5 - 10
        $processed = $data->firstWhere('id', 'p_' . $setup['processedWaste']->id);
        $this->assertEquals(4.0, $processed['stock']);
    }
}
