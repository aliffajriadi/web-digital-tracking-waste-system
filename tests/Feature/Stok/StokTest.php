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
    // TC-021 (Stok) - Stok keluar lebih besar dari yang masuk:
    //                 aplikasi tidak memvalidasi stok (tidak ada cek stok)
    //                 Data tetap tersimpan (behavior aplikasi saat ini)
    // =========================================================================
    public function test_stok_out_can_exceed_stok_in_because_no_stock_validation(): void
    {
        // Arrange: stok masuk hanya 10 kg
        $setup = $this->setupMasterData();
        $admin = $this->createAdmin();
        $pic   = $this->createPic('pic@test.com');

        WasteEntry::create([
            'id_user'               => $pic->id,
            'id_waste_sub_category' => $setup['subCategory']->id,
            'measured_qty'          => 10.0,
        ]);

        $method      = $setup['method'];
        $destination = $setup['destination'];

        // Act: coba keluarkan 100 kg (melebihi stok)
        $wasteOut = WasteOutData::create([
            'id_user'              => $pic->id,
            'id_waste_out_method'  => $method->id,
            'id_waste_destination' => $destination->id,
            'notes'                => 'Keluar melebihi stok',
        ]);

        DataWasteOut::create([
            'id_waste_out_data'     => $wasteOut->id,
            'is_processed_waste'    => false,
            'id_waste_sub_category' => $setup['subCategory']->id,
            'measured_qty'          => 100.0, // melebihi stok
        ]);

        // Assert: aplikasi tidak memblokir (tidak ada validasi stok di DB layer)
        $this->assertDatabaseHas('data_waste_out', [
            'id_waste_out_data' => $wasteOut->id,
            'measured_qty'      => 100.0,
        ]);

        // Selisih stok bisa negatif (10 - 100 = -90)
        $totalIn  = WasteEntry::where('id_waste_sub_category', $setup['subCategory']->id)->sum('measured_qty');
        $totalOut = DataWasteOut::where('id_waste_sub_category', $setup['subCategory']->id)->sum('measured_qty');
        $this->assertEquals(10.0, $totalIn);
        $this->assertEquals(100.0, $totalOut);
        $this->assertLessThan(0, $totalIn - $totalOut);
    }
}
