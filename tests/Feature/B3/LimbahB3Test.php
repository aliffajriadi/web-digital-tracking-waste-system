<?php

namespace Tests\Feature\B3;

use App\Models\WasteB3Detail;
use App\Models\WasteCategory;
use App\Models\WasteSubCategory;
use App\Models\UnitMeasured;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LimbahB3Test extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // TC-006 - Input data limbah B3 valid
    // =========================================================================
    public function test_tc006_admin_can_create_b3_detail_with_valid_data(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->post(route('admin.waste-b3.store'), [
            'waste_code'           => 'D001',
            'description'          => 'Limbah Asam',
            'retention_period_day' => 90,
            'danger_level'         => 3,
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('waste_b3_detail', [
            'waste_code'           => 'D001',
            'description'          => 'Limbah Asam',
            'retention_period_day' => 90,
            'danger_level'         => 3,
        ]);
    }

    // =========================================================================
    // TC-006b - Update data limbah B3 valid
    // =========================================================================
    public function test_tc006b_admin_can_update_b3_detail(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        $b3 = WasteB3Detail::create([
            'waste_code'           => 'D001',
            'description'          => 'Lama',
            'retention_period_day' => 30,
            'danger_level'         => 2,
        ]);

        // Act
        $response = $this->actingAs($admin)->put(route('admin.waste-b3.update', $b3->id), [
            'waste_code'           => 'D002',
            'description'          => 'Limbah Baru',
            'retention_period_day' => 180,
            'danger_level'         => 5,
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('waste_b3_detail', [
            'id'          => $b3->id,
            'waste_code'  => 'D002',
            'danger_level'=> 5,
        ]);
    }

    // =========================================================================
    // TC-006c - Hapus data limbah B3
    // =========================================================================
    public function test_tc006c_admin_can_delete_b3_detail(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        $b3 = WasteB3Detail::create([
            'waste_code'           => 'D099',
            'description'          => 'Hapus ini',
            'retention_period_day' => 10,
            'danger_level'         => 1,
        ]);

        // Act
        $response = $this->actingAs($admin)->delete(route('admin.waste-b3.destroy', $b3->id));

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('waste_b3_detail', ['id' => $b3->id]);
    }

    // =========================================================================
    // TC-007 - Input B3 invalid: waste_code kosong
    // =========================================================================
    public function test_tc007_create_b3_fails_without_waste_code(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->post(route('admin.waste-b3.store'), [
            'waste_code'           => '',
            'description'          => 'Ada deskripsi',
            'retention_period_day' => 90,
            'danger_level'         => 3,
        ]);

        // Assert
        $response->assertSessionHasErrors('waste_code');
        $this->assertDatabaseCount('waste_b3_detail', 0);
    }

    // =========================================================================
    // TC-007b - Input B3 invalid: danger_level di luar rentang (1-5)
    // =========================================================================
    public function test_tc007b_create_b3_fails_if_danger_level_exceeds_max(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->post(route('admin.waste-b3.store'), [
            'waste_code'           => 'D001',
            'description'          => 'Deskripsi',
            'retention_period_day' => 30,
            'danger_level'         => 6, // maks 5
        ]);

        // Assert
        $response->assertSessionHasErrors('danger_level');
        $this->assertDatabaseCount('waste_b3_detail', 0);
    }

    // =========================================================================
    // TC-007c - Input B3 invalid: waste_code duplikat
    // =========================================================================
    public function test_tc007c_create_b3_fails_with_duplicate_waste_code(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        WasteB3Detail::create([
            'waste_code'           => 'D001',
            'description'          => 'Sudah ada',
            'retention_period_day' => 30,
            'danger_level'         => 2,
        ]);

        // Act
        $response = $this->actingAs($admin)->post(route('admin.waste-b3.store'), [
            'waste_code'           => 'D001', // duplikat
            'description'          => 'Coba lagi',
            'retention_period_day' => 60,
            'danger_level'         => 3,
        ]);

        // Assert
        $response->assertSessionHasErrors('waste_code');
        $this->assertDatabaseCount('waste_b3_detail', 1); // tetap 1
    }

    // =========================================================================
    // TC-025 - Input B3: sub-kategori dengan B3 detail terlampir
    // =========================================================================
    public function test_tc025_can_create_subcategory_linked_to_b3_detail(): void
    {
        // Arrange
        $admin    = $this->createAdmin();
        $category = WasteCategory::create(['name' => 'B3', 'description' => 'Bahan Berbahaya']);
        $unit     = UnitMeasured::create(['name' => 'kg', 'type' => 'weight', 'symbol' => 'kg']);
        $b3 = WasteB3Detail::create([
            'waste_code'           => 'D001',
            'description'          => 'Limbah Asam',
            'retention_period_day' => 90,
            'danger_level'         => 3,
        ]);

        // Act: buat sub-kategori yang terhubung ke b3_detail
        $response = $this->actingAs($admin)->post(route('admin.waste-subcategory.store'), [
            'id_waste_category'    => $category->id,
            'name'                 => 'Asam Sulfat Bekas',
            'id_unit_measured'     => $unit->id,
            'default_measured_qty' => 0,
            'id_waste_b3_detail'   => $b3->id,
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('waste_sub_category', [
            'name'               => 'Asam Sulfat Bekas',
            'id_waste_b3_detail' => $b3->id,
        ]);
    }

    // =========================================================================
    // TC-027 - Hitung masa simpan B3: retention_period_day tersimpan benar
    // =========================================================================
    public function test_tc027_retention_period_day_is_stored_correctly(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $this->actingAs($admin)->post(route('admin.waste-b3.store'), [
            'waste_code'           => 'B001',
            'description'          => 'Oli Bekas',
            'retention_period_day' => 365,
            'danger_level'         => 4,
        ]);

        // Assert: pastikan masa simpan 365 hari tersimpan
        $b3 = WasteB3Detail::where('waste_code', 'B001')->first();
        $this->assertNotNull($b3);
        $this->assertEquals(365, $b3->retention_period_day);
    }

    // =========================================================================
    // TC-028 - Warning B3: endpoint getWarnings mengembalikan data jika ada B3
    // =========================================================================
    public function test_tc028_get_warnings_returns_b3_data_when_exists(): void
    {
        // Arrange: B3 dengan masa simpan sangat pendek (1 hari) → pasti expired → sisa ≤ 10
        WasteB3Detail::create([
            'waste_code'           => 'D001',
            'description'          => 'Limbah Kritis',
            'retention_period_day' => 1,
            'danger_level'         => 5,
        ]);

        // Act
        $response = $this->getJson('/api/waste-b3-notifications');

        // Assert: endpoint bisa diakses, berhasil, dan mengembalikan array data
        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data',
                 ]);

        // Ada setidaknya 1 warning karena masa simpan 1 hari (sisa ≤ 10)
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    // =========================================================================
    // TC-029 - Tidak ada warning: endpoint mengembalikan data kosong
    //          ketika tidak ada B3 di database
    // =========================================================================
    public function test_tc029_get_warnings_returns_empty_when_no_b3_data(): void
    {
        // Arrange: tidak ada data B3 sama sekali

        // Act
        $response = $this->getJson('/api/waste-b3-notifications');

        // Assert: sukses tapi data kosong
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data'    => [],
                 ]);
    }
}
