<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\AdminDetail;
use App\Models\PicDetail;
use App\Models\UnitMeasured;
use App\Models\WasteCategory;
use App\Models\WasteSubCategory;
use App\Models\SourceLocationWaste;
use App\Models\WasteDestinations;
use App\Models\WasteOutMethod;
use App\Models\DataCollectorBuyer;
use App\Models\ProcessedWaste;
use App\Models\WasteEntry;
use App\Models\WasteOutData;
use App\Models\DataWasteOut;
use App\Models\ProcessedWasteData;
use App\Models\WasteSellingData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles & Users
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $picRole = Role::firstOrCreate(['name' => 'pic']);

        // Admin User
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'password' => Hash::make('admin'),
                'is_active' => true,
                'role_id' => $adminRole->id,
            ]
        );

        AdminDetail::firstOrCreate(
            ['id_user' => $adminUser->id],
            ['full_name' => 'Super Admin']
        );

        // PIC Users
        $picUsers = [];
        for ($p = 1; $p <= 2; $p++) {
            $user = User::firstOrCreate(
                ['email' => "pic{$p}@gmail.com"],
                [
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'role_id' => $picRole->id,
                ]
            );
            
            PicDetail::firstOrCreate(
                ['id_user' => $user->id],
                [
                    'full_name' => "PIC User {$p}",
                    'nik' => "1234567890{$p}",
                ]
            );
            $picUsers[] = $user;
        }

        // 2. Units Measured
        $units = [
            ['name' => 'kg', 'type' => 'weight', 'symbol' => 'kg'],
            ['name' => 'liter', 'type' => 'volume', 'symbol' => 'L'],
            ['name' => 'pcs', 'type' => 'count', 'symbol' => 'pcs'],
            ['name' => 'ton', 'type' => 'weight', 'symbol' => 't'],
        ];
        foreach ($units as $unit) {
            UnitMeasured::firstOrCreate(['name' => $unit['name']], $unit);
        }
        $kgUnit = UnitMeasured::where('name', 'kg')->first();

        // 3. Waste Categories
        $categories = [
            ['name' => 'Organik', 'description' => 'Limbah yang mudah terurai'],
            ['name' => 'Anorganik', 'description' => 'Limbah yang sulit terurai'],
            ['name' => 'B3', 'description' => 'Bahan Berbahaya dan Beracun'],
        ];
        foreach ($categories as $cat) {
            WasteCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
        $organikCat = WasteCategory::where('name', 'Organik')->first();
        $anorganikCat = WasteCategory::where('name', 'Anorganik')->first();

        // 4. Waste Sub Categories
        $subCategories = [
            [
                'id_waste_category' => $organikCat->id,
                'name' => 'Sisa Makanan',
                'description' => 'Sisa makanan dari kantin',
                'id_unit_measured' => $kgUnit->id,
                'default_measured_qty' => 0,
            ],
            [
                'id_waste_category' => $organikCat->id,
                'name' => 'Daun Kering',
                'description' => 'Sampah taman',
                'id_unit_measured' => $kgUnit->id,
                'default_measured_qty' => 0,
            ],
            [
                'id_waste_category' => $anorganikCat->id,
                'name' => 'Botol Plastik',
                'description' => 'Botol bekas minuman',
                'id_unit_measured' => $kgUnit->id,
                'default_measured_qty' => 0,
            ],
            [
                'id_waste_category' => $anorganikCat->id,
                'name' => 'Kertas/Karton',
                'description' => 'Kertas kantor dan dus',
                'id_unit_measured' => $kgUnit->id,
                'default_measured_qty' => 0,
            ],
        ];
        foreach ($subCategories as $sub) {
            WasteSubCategory::firstOrCreate(['name' => $sub['name']], $sub);
        }

        // 5. Source Locations
        $locations = [
            ['name' => 'Gedung Utama'],
            ['name' => 'Kantin'],
            ['name' => 'Workshop'],
            ['name' => 'Taman'],
        ];
        foreach ($locations as $loc) {
            SourceLocationWaste::firstOrCreate(['name' => $loc['name']], $loc);
        }

        // 6. Waste Out Methods
        $methods = [
            ['name' => 'Penjualan', 'description' => 'Dijual ke pengepul'],
            ['name' => 'Landfill', 'description' => 'Dibuang ke TPA'],
            ['name' => 'Insinerasi', 'description' => 'Dibakar'],
            ['name' => 'Komposting', 'description' => 'Diolah jadi kompos'],
        ];
        foreach ($methods as $method) {
            WasteOutMethod::firstOrCreate(['name' => $method['name']], $method);
        }
        $sellingMethod = WasteOutMethod::where('name', 'Penjualan')->first();

        // 7. Waste Destinations
        $destinations = [
            ['name' => 'TPA Punggur', 'location' => 'Telaga Punggur'],
            ['name' => 'Pengepul Plastik Jaya', 'location' => 'Batam Center'],
            ['name' => 'Unit Komposting Internal', 'location' => 'Area Belakang'],
        ];
        foreach ($destinations as $dest) {
            WasteDestinations::firstOrCreate(['name' => $dest['name']], $dest);
        }

        // 8. Data Collector / Buyer
        $buyers = [
            [
                'name' => 'Bapak Budi',
                'phone_number' => '08123456789',
                'address' => 'Batam Center',
                'email' => 'budi@example.com'
            ],
            [
                'name' => 'PT Daur Ulang Mandiri',
                'phone_number' => '0877665544',
                'address' => 'Tanjung Uncang',
                'email' => 'contact@daurulang.com'
            ],
        ];
        foreach ($buyers as $buyer) {
            DataCollectorBuyer::firstOrCreate(['name' => $buyer['name']], $buyer);
        }
        $buyerList = DataCollectorBuyer::all();

        // 9. Processed Waste
        $processedWastes = [
            [
                'name' => 'Pupuk Kompos',
                'description' => 'Hasil pengolahan sampah organik',
                'id_unit_measured' => $kgUnit->id,
                'default_measured_qty' => 0,
            ],
            [
                'name' => 'Pelet Plastik',
                'description' => 'Hasil cacahan plastik',
                'id_unit_measured' => $kgUnit->id,
                'default_measured_qty' => 0,
            ],
        ];
        foreach ($processedWastes as $pw) {
            ProcessedWaste::firstOrCreate(['name' => $pw['name']], $pw);
        }
        $pwTypes = ProcessedWaste::all();

        // 10. Waste Entries (Dummy Waste In)
        $subCats = WasteSubCategory::all();
        $locs = SourceLocationWaste::all();
        for ($i = 0; $i < 20; $i++) {
            WasteEntry::create([
                'id_user' => $picUsers[array_rand($picUsers)]->id, // PIC users input waste
                'id_waste_sub_category' => $subCats->random()->id,
                'id_source_location_waste' => $locs->random()->id,
                'measured_qty' => rand(10, 100),
                'notes' => 'Entry dummy data ke-' . ($i + 1),
                'created_at' => now()->subDays(rand(0, 30)),
            ]);
        }

        // 11. Processed Waste Data (Results of processing)
        for ($k = 0; $k < 10; $k++) {
            ProcessedWasteData::create([
                'id_processed_waste' => $pwTypes->random()->id,
                'id_user' => $adminUser->id,
                'measured_qty' => rand(5, 50),
                'notes' => 'Hasil pengolahan dummy ke-' . ($k + 1),
                'created_at' => now()->subDays(rand(0, 20)),
            ]);
        }

        // 12. Waste Out Data
        $methods = WasteOutMethod::all();
        $destinations = WasteDestinations::all();

        for ($j = 0; $j < 15; $j++) {
            $currentMethod = $methods->random();
            $wo = WasteOutData::create([
                'id_waste_out_method' => $currentMethod->id,
                'id_waste_destination' => $destinations->random()->id,
                'notes' => 'Waste out dummy data ke-' . ($j + 1),
                'created_at' => now()->subDays(rand(0, 15)),
            ]);

            // Add detail to Waste Out
            $isProcessed = (bool)rand(0, 1);
            DataWasteOut::create([
                'id_waste_out_data' => $wo->id,
                'is_processed_waste' => $isProcessed,
                'id_waste_sub_category' => $isProcessed ? null : $subCats->random()->id,
                'id_processed_waste' => $isProcessed ? $pwTypes->random()->id : null,
                'measured_qty' => rand(5, 50),
            ]);

            // If method is Selling, add selling data
            if ($currentMethod->id == $sellingMethod->id) {
                WasteSellingData::create([
                    'id_waste_out_data' => $wo->id,
                    'total_revenue' => rand(10000, 500000),
                    'id_buyer' => $buyerList->random()->id,
                ]);
            }
        }
    }
}
