<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use App\Models\AdminDetail;
use App\Models\PicDetail;
use App\Models\WasteCategory;
use App\Models\WasteSubCategory;
use App\Models\UnitMeasured;
use App\Models\WasteOutMethod;
use App\Models\WasteDestinations;
use App\Models\ProcessedWaste;
use App\Models\SourceLocationWaste;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    // =========================================================================
    // HELPER: Buat Admin User (role_id = 1)
    // =========================================================================
    protected function createAdmin(string $email = 'admin@test.com', string $password = 'password'): User
    {
        $role = Role::firstOrCreate(['name' => 'admin']);

        $user = User::create([
            'email'     => $email,
            'password'  => Hash::make($password),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        AdminDetail::create([
            'id_user'   => $user->id,
            'full_name' => 'Super Admin Test',
        ]);

        return $user;
    }

    // =========================================================================
    // HELPER: Buat PIC User (role_id = 2)
    // =========================================================================
    protected function createPic(string $email = 'pic@test.com', string $password = 'password'): User
    {
        $role = Role::firstOrCreate(['name' => 'pic']);
        // Pastikan role_id PIC = 2
        if ($role->id !== 2) {
            // Create admin role dulu jika belum ada agar PIC mendapat id=2
            Role::firstOrCreate(['name' => 'admin']);
            $role = Role::firstOrCreate(['name' => 'pic']);
        }

        $user = User::create([
            'email'     => $email,
            'password'  => Hash::make($password),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        PicDetail::create([
            'id_user'   => $user->id,
            'full_name' => 'PIC Test',
            'nik'       => '1234567890123456',
        ]);

        return $user;
    }

    // =========================================================================
    // HELPER: Setup data master dasar (unit, kategori, sub-kategori)
    // =========================================================================
    protected function setupMasterData(): array
    {
        $unit = UnitMeasured::create([
            'name'   => 'kg',
            'type'   => 'weight',
            'symbol' => 'kg',
        ]);

        $category = WasteCategory::create([
            'name'        => 'Organik',
            'description' => 'Limbah organik',
        ]);

        $subCategory = WasteSubCategory::create([
            'id_waste_category'    => $category->id,
            'name'                 => 'Sisa Makanan',
            'id_unit_measured'     => $unit->id,
            'default_measured_qty' => 0,
        ]);

        $location = SourceLocationWaste::create(['name' => 'Kantin']);

        $method = WasteOutMethod::create([
            'name'        => 'Landfill',
            'description' => 'Dibuang ke TPA',
        ]);

        $destination = WasteDestinations::create([
            'name'     => 'TPA Punggur',
            'location' => 'Batam',
        ]);

        $processedWaste = ProcessedWaste::create([
            'name'                 => 'Pupuk Kompos',
            'description'          => 'Hasil pengolahan',
            'id_unit_measured'     => $unit->id,
            'default_measured_qty' => 0,
        ]);

        return compact('unit', 'category', 'subCategory', 'location', 'method', 'destination', 'processedWaste');
    }
}
