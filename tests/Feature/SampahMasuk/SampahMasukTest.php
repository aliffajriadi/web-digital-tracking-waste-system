<?php

namespace Tests\Feature\SampahMasuk;

use App\Models\WasteEntry;
use App\Models\WasteSubCategory;
use App\Models\WasteCategory;
use App\Models\UnitMeasured;
use App\Models\SourceLocationWaste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampahMasukTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPER lokal: buat prerequisite untuk waste entry via API (PIC)
    // =========================================================================
    private function setupApiPrerequisites(): array
    {
        $data = $this->setupMasterData();
        $pic  = $this->createPic();
        return array_merge($data, compact('pic'));
    }

    // =========================================================================
    // TC-012 - Sampah masuk valid: PIC berhasil input data via API
    // =========================================================================
    public function test_tc012_pic_can_store_waste_entry_via_api(): void
    {
        // Arrange
        $setup = $this->setupApiPrerequisites();
        $pic         = $setup['pic'];
        $subCategory = $setup['subCategory'];
        $location    = $setup['location'];

        // Act
        $response = $this->actingAs($pic, 'sanctum')->postJson('/api/waste-entry', [
            'id_waste_sub_category'   => $subCategory->id,
            'id_source_location_waste'=> $location->id,
            'measured_qty'            => 25.5,
            'created_at'              => now()->format('Y-m-d H:i:s'),
        ]);

        // Assert
        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('waste_entry', [
            'id_user'               => $pic->id,
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 25.5,
        ]);
    }

    // =========================================================================
    // TC-012b - Sampah masuk valid: data tersimpan dengan notes opsional
    // =========================================================================
    public function test_tc012b_waste_entry_can_include_optional_notes(): void
    {
        // Arrange
        $setup = $this->setupApiPrerequisites();

        // Act
        $response = $this->actingAs($setup['pic'], 'sanctum')->postJson('/api/waste-entry', [
            'id_waste_sub_category'    => $setup['subCategory']->id,
            'id_source_location_waste' => $setup['location']->id,
            'measured_qty'             => 10.0,
            'notes'                    => 'Sampah dari kantin hari Senin',
            'created_at'               => now()->format('Y-m-d H:i:s'),
        ]);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('waste_entry', [
            'notes' => 'Sampah dari kantin hari Senin',
        ]);
    }

    // =========================================================================
    // TC-013 - Sampah masuk invalid: field wajib kosong
    // =========================================================================
    public function test_tc013_waste_entry_fails_without_required_fields(): void
    {
        // Arrange
        $setup = $this->setupApiPrerequisites();

        // Act: kirim tanpa field apapun
        $response = $this->actingAs($setup['pic'], 'sanctum')->postJson('/api/waste-entry', []);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'id_waste_sub_category',
                     'id_source_location_waste',
                     'measured_qty',
                     'created_at',
                 ]);

        $this->assertDatabaseCount('waste_entry', 0);
    }

    // =========================================================================
    // TC-013b - Sampah masuk invalid: measured_qty bukan angka
    // =========================================================================
    public function test_tc013b_waste_entry_fails_if_measured_qty_is_not_numeric(): void
    {
        // Arrange
        $setup = $this->setupApiPrerequisites();

        // Act
        $response = $this->actingAs($setup['pic'], 'sanctum')->postJson('/api/waste-entry', [
            'id_waste_sub_category'    => $setup['subCategory']->id,
            'id_source_location_waste' => $setup['location']->id,
            'measured_qty'             => 'bukan_angka',
            'created_at'               => now()->format('Y-m-d H:i:s'),
        ]);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['measured_qty']);
    }

    // =========================================================================
    // TC-013c - Sampah masuk: endpoint butuh autentikasi sanctum
    // =========================================================================
    public function test_tc013c_waste_entry_api_requires_authentication(): void
    {
        // Arrange: tidak ada auth
        $setup = $this->setupApiPrerequisites();

        // Act
        $response = $this->postJson('/api/waste-entry', [
            'id_waste_sub_category'    => $setup['subCategory']->id,
            'id_source_location_waste' => $setup['location']->id,
            'measured_qty'             => 10.0,
            'created_at'               => now()->format('Y-m-d H:i:s'),
        ]);

        // Assert: 401 Unauthorized
        $response->assertStatus(401);
    }

    // =========================================================================
    // TC-014 - Update stok otomatis: setelah waste entry disimpan,
    //          data bisa dilihat di admin panel (waste-entry index)
    // =========================================================================
    public function test_tc014_waste_entry_visible_in_admin_panel_after_creation(): void
    {
        // Arrange: simpan waste entry lewat DB langsung
        $admin = $this->createAdmin();
        $setup = $this->setupMasterData();
        $pic   = $this->createPic('pic@test.com');

        WasteEntry::create([
            'id_user'                 => $pic->id,
            'id_waste_sub_category'   => $setup['subCategory']->id,
            'id_source_location_waste'=> $setup['location']->id,
            'measured_qty'            => 50.0,
            'notes'                   => 'Entri otomatis test',
        ]);

        // Act: admin melihat daftar waste entry
        $response = $this->actingAs($admin)->get(route('admin.waste-entry.index'));

        // Assert: halaman berhasil dimuat dengan data
        $response->assertStatus(200);
        $response->assertSee('50'); // qty terlihat di halaman
    }

    // =========================================================================
    // TC-014b - Setelah waste entry disimpan, total qty terekam dengan benar
    // =========================================================================
    public function test_tc014b_multiple_waste_entries_are_stored_separately(): void
    {
        // Arrange
        $setup = $this->setupApiPrerequisites();
        $pic   = $setup['pic'];

        // Act: simpan 2 entri berbeda
        $this->actingAs($pic, 'sanctum')->postJson('/api/waste-entry', [
            'id_waste_sub_category'    => $setup['subCategory']->id,
            'id_source_location_waste' => $setup['location']->id,
            'measured_qty'             => 20.0,
            'created_at'               => now()->format('Y-m-d H:i:s'),
        ]);

        $this->actingAs($pic, 'sanctum')->postJson('/api/waste-entry', [
            'id_waste_sub_category'    => $setup['subCategory']->id,
            'id_source_location_waste' => $setup['location']->id,
            'measured_qty'             => 30.0,
            'created_at'               => now()->format('Y-m-d H:i:s'),
        ]);

        // Assert: 2 record tersimpan
        $this->assertDatabaseCount('waste_entry', 2);
        $totalQty = WasteEntry::sum('measured_qty');
        $this->assertEquals(50.0, $totalQty);
    }
}
