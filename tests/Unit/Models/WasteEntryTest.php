<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Role;
use App\Models\WasteEntry;
use App\Models\WasteCategory;
use App\Models\WasteSubCategory;
use App\Models\UnitMeasured;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WasteEntryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa nama tabel model adalah 'waste_entry'
     */
    public function test_table_name_is_correct(): void
    {
        $entry = new WasteEntry();
        $this->assertEquals('waste_entry', $entry->getTable());
    }

    /**
     * Test bahwa kolom fillable sudah sesuai
     */
    public function test_fillable_attributes(): void
    {
        $entry = new WasteEntry();
        $expected = [
            'id_user',
            'id_waste_sub_category',
            'id_source_location_waste',
            'measured_qty',
            'notes',
            'created_at',
        ];
        $this->assertEquals($expected, $entry->getFillable());
    }

    /**
     * Helper: buat data prerequisite untuk WasteEntry
     */
    private function createPrerequisites(): array
    {
        $role = Role::create(['name' => 'PIC']);

        $user = User::create([
            'email'     => 'pic@waste.com',
            'password'  => bcrypt('secret'),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        $category = WasteCategory::create([
            'name'        => 'Sampah Organik',
            'description' => 'Kategori sampah organik',
        ]);

        $unit = UnitMeasured::create([
            'name'   => 'Kilogram',
            'type'   => 'weight',
            'symbol' => 'kg',
        ]);

        $subCategory = WasteSubCategory::create([
            'id_waste_category'     => $category->id,
            'name'                  => 'Sisa Makanan',
            'id_unit_measured'      => $unit->id,
            'default_measured_qty'  => 10.0,
        ]);

        return [$user, $subCategory];
    }

    /**
     * Test bahwa WasteEntry bisa dibuat dengan data valid
     */
    public function test_can_create_waste_entry(): void
    {
        [$user, $subCategory] = $this->createPrerequisites();

        $entry = WasteEntry::create([
            'id_user'               => $user->id,
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 15.5,
            'notes'                 => 'Test entry',
        ]);

        $this->assertDatabaseHas('waste_entry', [
            'id_user'               => $user->id,
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 15.5,
        ]);
        $this->assertNotNull($entry->id);
    }

    /**
     * Test relasi belongsTo ke User
     */
    public function test_belongs_to_user(): void
    {
        [$user, $subCategory] = $this->createPrerequisites();

        $entry = WasteEntry::create([
            'id_user'               => $user->id,
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 5.0,
        ]);

        $this->assertInstanceOf(User::class, $entry->user);
        $this->assertEquals($user->id, $entry->user->id);
    }

    /**
     * Test relasi belongsTo ke WasteSubCategory
     */
    public function test_belongs_to_sub_category(): void
    {
        [$user, $subCategory] = $this->createPrerequisites();

        $entry = WasteEntry::create([
            'id_user'               => $user->id,
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 8.0,
        ]);

        $this->assertInstanceOf(WasteSubCategory::class, $entry->subCategory);
        $this->assertEquals($subCategory->id, $entry->subCategory->id);
        $this->assertEquals('Sisa Makanan', $entry->subCategory->name);
    }

    /**
     * Test bahwa notes bisa berisi nilai otomatis IoT
     */
    public function test_notes_can_be_iot_label(): void
    {
        [$user, $subCategory] = $this->createPrerequisites();

        $entry = WasteEntry::create([
            'id_user'               => $user->id,
            'id_waste_sub_category' => $subCategory->id,
            'measured_qty'          => 12.3,
            'notes'                 => 'Timbangan Otomatis (IoT)',
        ]);

        $this->assertEquals('Timbangan Otomatis (IoT)', $entry->notes);
    }
}
