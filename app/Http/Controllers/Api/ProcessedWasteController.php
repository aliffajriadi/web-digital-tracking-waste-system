<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessedWaste;
use App\Models\ProcessedWasteData;
use App\Models\WasteRawMaterials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessedWasteController extends Controller
{
    public function index()
    {
        try {
            // Mengambil semua data dari tabel processed_waste beserta relasi unit_measured jika diperlukan
            $processedWaste = ProcessedWaste::all();

            // Mengembalikan data ke Flutter dengan format JSON sukses
            return response()->json([
                'success' => true,
                'message' => 'Daftar jenis olahan berhasil dimuat',
                'data'    => $processedWaste
            ], 200);

        } catch (\Exception $e) {
            // Jika ada error internal server
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_processed_waste' => 'required|exists:processed_waste,id',
            'measured_qty'       => 'required|numeric',
            'created_at'         => 'required|date_format:Y-m-d H:i:s',
            'raw_materials'      => 'required|json', // Harap dikirim sebagai string JSON dari Flutter
        ]);

        DB::beginTransaction();
        try {
            $olahan = new ProcessedWasteData();
            $olahan->id_user = $request->user()->id; // Mengambil ID PIC yang sedang login
            $olahan->id_processed_waste = $request->id_processed_waste;
            $olahan->measured_qty = $request->measured_qty;
            $olahan->notes = $request->notes;
            $olahan->created_at = $request->created_at;
            $olahan->save();

            $rawMaterials = json_decode($request->raw_materials, true);
            foreach ($rawMaterials as $material) {
                WasteRawMaterials::create([
                    'id_processed_waste_data' => $olahan->id,
                    'id_waste_sub_category' => $material['id_waste_sub_category'],
                    'measured_qty' => $material['measured_qty'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data hasil pengolahan sampah berhasil dicatat!',
                'data'    => $olahan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data olahan: ' . $e->getMessage()
            ], 500);
        }
    }
}