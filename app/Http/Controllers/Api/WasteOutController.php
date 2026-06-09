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

    public function getBuyers()
    {
        try {
            $data = DB::table('data_collector_buyer')->get(); 
            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getDestinations()
    {
        try {
            $data = DB::table('waste_destinations')->get(); 
            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
                // If id_sub_category starts with p_, it means it's processed waste
                $isProcessed = false;
                $idSub = null;
                $idProcessed = null;
                
                $itemStrId = (string)$item['id_sub_category'];
                if (str_starts_with($itemStrId, 'p_')) {
                    $isProcessed = true;
                    $idProcessed = str_replace('p_', '', $itemStrId);
                } else {
                    $idSub = $item['id_sub_category'];
                }

                DB::table('data_waste_out')->insert([
                    'id_waste_out_data'     => $wasteOutDataId,
                    'is_processed_waste'    => $isProcessed, 
                    'id_waste_sub_category' => $idSub, 
                    'id_processed_waste'    => $idProcessed,  
                    'measured_qty'          => $item['quantity'], 
                ]);
            }

            // Jika penjualan, simpan ke waste_selling_data
            if ($request->has('id_buyer') && $request->id_buyer != null) {
                DB::table('waste_selling_data')->insert([
                    'id_waste_out_data' => $wasteOutDataId,
                    'total_revenue'     => $request->total_revenue ?? 0,
                    'id_buyer'          => $request->id_buyer,
                    'created_at'        => now(),
                    'updated_at'        => now(),
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