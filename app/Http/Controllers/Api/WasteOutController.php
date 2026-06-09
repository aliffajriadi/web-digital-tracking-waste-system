<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WasteOutController extends Controller
{
    public function index()
    {
        try {
            $methods = DB::table('waste_out_method')->get();
            
            return response()->json([
                'success' => true,
                'data'    => $methods
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal mengambil metode keluar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. Mengambil Daftar Subkategori Sampah (Untuk Dropdown di Flutter)
     */
    public function getSubcategories()
    {
        try {
            $data = DB::table('waste_sub_category')->get(); 
            
            return response()->json([
                'success' => true,
                'data'    => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal mengambil subkategori sampah: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. Menyimpan Seluruh Transaksi Sampah Keluar ke 3 Tabel Sekaligus
     */
    public function store(Request $request)
    {
        // Validasi input kiriman dari Flutter sesuai skema database asli kamu
        $request->validate([
            'id_waste_out_method'  => 'required|exists:waste_out_method,id',
            'id_waste_destination' => 'nullable', 
            'notes'                => 'nullable|string',
            'created_at'           => 'required|date_format:Y-m-d H:i:s',
            'items'                => 'required|json', 
            'photo'                => 'nullable|image|max:2048' 
        ]);

        // Mulai database transaction demi keamanan data
        DB::beginTransaction();

        try {
            // DI SINI PERUBAHANNYA: Tambahkan id_user ke dalam query insert
            $wasteOutDataId = DB::table('waste_out_data')->insertGetId([
                'id_user'              => $request->user()->id, // <== TAMBAHKAN BARIS INI ==
                'id_waste_out_method'  => $request->id_waste_out_method,
                'id_waste_destination' => $request->id_waste_destination,
                'notes'                => $request->notes,
                'created_at'           => $request->created_at,
                'updated_at'           => now(), 
            ]);

            if ($request->hasFile('photo')) {
                // Simpan fisik file ke folder storage/app/public/waste_out_photos
                $photoPath = $request->file('photo')->store('waste_out_photos', 'public');

                DB::table('attachment_waste_out_data')->insert([
                    'id_waste_out_data' => $wasteOutDataId, 
                    'path'              => $photoPath
                ]);
            }

            $items = json_decode($request->items, true);
            
            foreach ($items as $item) {
                DB::table('data_waste_out')->insert([
                    'id_waste_out_data'     => $wasteOutDataId,
                    'is_processed_waste'    => false, 
                    'id_waste_sub_category' => $item['id_sub_category'], 
                    'id_processed_waste'    => null,  
                    'measured_qty'          => $item['quantity'], 
                ]);
            }

            // Jika semua langkah berhasil tanpa eror, kunci data ke database
            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Laporan sampah keluar berhasil disimpan dengan sukses!'
            ], 201);

        } catch (\Exception $e) {
            // Jika ada salah satu langkah yang gagal/eror, batalkan semua perubahan data (aman)
            DB::rollBack();

            return response()->json([
                'success' => false, 
                'message' => 'Gagal menyimpan data transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}