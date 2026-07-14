<?php

namespace Tests\Feature\Master;

use App\Models\WasteCategory;
use App\Models\WasteSubCategory;
use App\Models\UnitMeasured;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // TC-004 - Kelola data master: Tambah kategori sampah (valid)
    // =========================================================================
    public function test_tc004_admin_can_create_waste_category_with_valid_data(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->post(route('admin.waste-category.store'), [
            'name'        => 'Organik',
            'description' => 'Limbah organik yang mudah terurai',
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('waste_category', [
            'name'        => 'Organik',
            'description' => 'Limbah organik yang mudah terurai',
        ]);
    }

    // =========================================================================
    // TC-004b - Update kategori sampah (valid)
    // =========================================================================
    public function test_tc004b_admin_can_update_waste_category(): void
    {
        // Arrange
        $admin    = $this->createAdmin();
        $category = WasteCategory::create([
            'name'        => 'Kategori Lama',
            'description' => 'Deskripsi lama',
        ]);

        // Act
        $response = $this->actingAs($admin)->put(route('admin.waste-category.update', $category->id), [
            'name'        => 'Kategori Baru',
            'description' => 'Deskripsi baru',
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('waste_category', ['name' => 'Kategori Baru']);
        $this->assertDatabaseMissing('waste_category', ['name' => 'Kategori Lama']);
    }

    // =========================================================================
    // TC-004c - Hapus kategori sampah (valid - tanpa sub-kategori)
    // =========================================================================
    public function test_tc004c_admin_can_delete_category_without_subcategories(): void
    {
        // Arrange
        $admin    = $this->createAdmin();
        $category = WasteCategory::create(['name' => 'Kategori Hapus', 'description' => '-']);

        // Act
        $response = $this->actingAs($admin)->delete(route('admin.waste-category.destroy', $category->id));

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('waste_category', ['id' => $category->id]);
    }

    // =========================================================================
    // TC-005 - Kelola data master invalid: name kosong
    // =========================================================================
    public function test_tc005_create_category_fails_without_name(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->post(route('admin.waste-category.store'), [
            'name'        => '',
            'description' => 'Deskripsi ada, nama kosong',
        ]);

        // Assert
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('waste_category', 0);
    }

    // =========================================================================
    // TC-005b - Hapus kategori gagal karena masih punya sub-kategori
    // =========================================================================
    public function test_tc005b_cannot_delete_category_that_has_subcategories(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        $unit  = UnitMeasured::create(['name' => 'kg', 'type' => 'weight', 'symbol' => 'kg']);

        $category = WasteCategory::create(['name' => 'Organik', 'description' => '-']);
        WasteSubCategory::create([
            'id_waste_category'    => $category->id,
            'name'                 => 'Sisa Makanan',
            'id_unit_measured'     => $unit->id,
            'default_measured_qty' => 0,
        ]);

        // Act
        $response = $this->actingAs($admin)->delete(route('admin.waste-category.destroy', $category->id));

        // Assert: kategori tidak terhapus
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('waste_category', ['id' => $category->id]);
    }

    // =========================================================================
    // TC-004d - Tambah sub-kategori (valid)
    // =========================================================================
    public function test_tc004d_admin_can_create_waste_subcategory(): void
    {
        // Arrange
        $admin    = $this->createAdmin();
        $category = WasteCategory::create(['name' => 'Organik', 'description' => '-']);
        $unit     = UnitMeasured::create(['name' => 'kg', 'type' => 'weight', 'symbol' => 'kg']);

        // Act
        $response = $this->actingAs($admin)->post(route('admin.waste-subcategory.store'), [
            'id_waste_category'    => $category->id,
            'name'                 => 'Sisa Makanan',
            'id_unit_measured'     => $unit->id,
            'default_measured_qty' => 0,
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('waste_sub_category', [
            'name'             => 'Sisa Makanan',
            'id_waste_category'=> $category->id,
        ]);
    }

    // =========================================================================
    // TC-005c - Tambah sub-kategori invalid: kategori tidak ada
    // =========================================================================
    public function test_tc005c_create_subcategory_fails_with_nonexistent_category(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        $unit  = UnitMeasured::create(['name' => 'kg', 'type' => 'weight', 'symbol' => 'kg']);

        // Act
        $response = $this->actingAs($admin)->post(route('admin.waste-subcategory.store'), [
            'id_waste_category'    => 9999, // tidak ada
            'name'                 => 'Sub Palsu',
            'id_unit_measured'     => $unit->id,
            'default_measured_qty' => 0,
        ]);

        // Assert
        $response->assertSessionHasErrors('id_waste_category');
        $this->assertDatabaseCount('waste_sub_category', 0);
    }

    // =========================================================================
    // TC-034 - Toggle status IoT (aktif/nonaktif integrasi)
    // TC ini tidak bisa diimplementasikan karena tidak ada endpoint toggle IoT
    // di web routes (hanya ada generate-code, pair, unpair di api routes).
    // Akan diimplementasikan di WasteSubCategoryController test sebagai gantinya:
    // Test update is_active sub-kategori
    // =========================================================================
    public function test_tc034_subcategory_is_active_can_be_toggled(): void
    {
        // Arrange
        $admin    = $this->createAdmin();
        $category = WasteCategory::create(['name' => 'Anorganik', 'description' => '-']);
        $unit     = UnitMeasured::create(['name' => 'kg', 'type' => 'weight', 'symbol' => 'kg']);

        $sub = WasteSubCategory::create([
            'id_waste_category'    => $category->id,
            'name'                 => 'Botol Plastik',
            'id_unit_measured'     => $unit->id,
            'default_measured_qty' => 0,
            'is_active'            => true,
        ]);

        // Act: update is_active ke false
        $response = $this->actingAs($admin)->put(route('admin.waste-subcategory.update', $sub->id), [
            'id_waste_category'    => $category->id,
            'name'                 => 'Botol Plastik',
            'id_unit_measured'     => $unit->id,
            'default_measured_qty' => 0,
            'is_active'            => 0, // nonaktifkan
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('waste_sub_category', [
            'id'        => $sub->id,
            'is_active' => false,
        ]);
    }
}
