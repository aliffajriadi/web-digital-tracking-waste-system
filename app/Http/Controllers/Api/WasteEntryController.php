<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteEntry;
use App\Models\AttachmentWasteEntry; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WasteEntryController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi data yang masuk dari Flutter
        $request->validate([
            'id_waste_sub_category' => 'required',
            'id_source_location_waste' => 'required',
            'measured_qty' => 'required|numeric',
            'created_at' => 'required',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
        ]);

        // Gunakan Database Transaction agar jika salah satu simpan gagal, data tidak berantakan
        DB::beginTransaction();

        try {
            // 2. Simpan ke tabel waste_entry
            $wasteEntry = new WasteEntry();
            // id_user diambil otomatis dari PIC yang sedang login via token sanctum
            $wasteEntry->id_user = $request->user()->id; 
            $wasteEntry->id_waste_sub_category = $request->id_waste_sub_category;
            $wasteEntry->id_source_location_waste = $request->id_source_location_waste;
            $wasteEntry->measured_qty = $request->measured_qty;
            $wasteEntry->notes = $request->notes;
            $wasteEntry->created_at = $request->created_at;
            $wasteEntry->save();

            // 3. Proses upload file foto bukti sampah
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                // Menyimpan ke folder storage/app/public/attachments
                $path = $file->store('attachments', 'public'); 

                // 4. Simpan log gambar ke tabel attachment_waste_entry
                DB::table('attachment_waste_entry')->insert([
                    'id_waste_entry' => $wasteEntry->id,
                    'path' => $path, 
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi sampah berhasil disimpan!'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }
}