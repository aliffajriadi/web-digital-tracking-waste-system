<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Tambahkan ini
use Carbon\Carbon; // Tambahkan ini

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        try {
            // 1. Ambil ID user yang sedang login dari Token
            $user = Auth::user();

            $laporan = DB::table('waste_entry')
                ->leftJoin('waste_sub_category', 'waste_entry.id_waste_sub_category', '=', 'waste_sub_category.id')
                ->leftJoin('waste_category', 'waste_sub_category.id_waste_category', '=', 'waste_category.id')
                ->leftJoin('unit_measured', 'waste_sub_category.id_unit_measured', '=', 'unit_measured.id')
                ->select(
                    'waste_entry.id', 
                    'waste_entry.measured_qty',
                    'waste_entry.created_at',
                    'waste_sub_category.name as sub_name',
                    'waste_category.name as cat_name',   
                    'unit_measured.symbol as unit_symbol' 
                )
                // 2. Filter agar yang muncul CUMA milik petugas yang login
                ->where('waste_entry.id_user', $user->id) 
                ->orderBy('waste_entry.created_at', 'desc')
                ->get(); // Hapus limit(5) agar semua laporan hari ini/milik dia muncul

            $data = $laporan->map(function ($item) {
                $catName = $item->cat_name ?? 'Umum';
                $subName = $item->sub_name ?? 'Sampah';
                $waktu = $item->created_at ? Carbon::parse($item->created_at)->format('H:i') . " WIB" : "-";
                $unit = $item->unit_symbol ?? 'Kg';

                return [
                    "id" => $item->id, 
                    // Sesuaikan dengan key yang dicari di Flutter: item['kategori']
                    "kategori" => $subName, 
                    "waktu" => $waktu,
                    "jumlah" => $item->measured_qty . " " . $unit,
                    "isBotol" => str_contains(strtolower($subName), 'botol'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $laporan = DB::table('waste_entry')
                ->leftJoin('waste_sub_category', 'waste_entry.id_waste_sub_category', '=', 'waste_sub_category.id')
                ->leftJoin('waste_category', 'waste_sub_category.id_waste_category', '=', 'waste_category.id')
                ->leftJoin('unit_measured', 'waste_sub_category.id_unit_measured', '=', 'unit_measured.id')
                ->leftJoin('source_location_waste', 'waste_entry.id_source_location_waste', '=', 'source_location_waste.id')
                ->leftJoin('attachment_waste_entry', 'waste_entry.id', '=', 'attachment_waste_entry.id_waste_entry')
                ->select(
                    'waste_entry.id',
                    'waste_entry.measured_qty',
                    'waste_entry.notes',
                    'waste_entry.created_at',
                    'waste_sub_category.name as sub_name',
                    'waste_category.name as cat_name',
                    'unit_measured.symbol as unit_symbol',
                    'source_location_waste.name as location_name',
                    'attachment_waste_entry.path as photo_path'
                )
                ->where('waste_entry.id', '=', $id)
                ->first();

            if (!$laporan) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $data = [
                "id" => $laporan->id,
                "sub_kategori" => $laporan->sub_name ?? 'Sampah',
                "kategori_gabung" => ($laporan->cat_name ?? 'Umum') . ", " . ($laporan->sub_name ?? 'Sampah'),
                "waktu_tanggal" => $laporan->created_at ? Carbon::parse($laporan->created_at)->translatedFormat('l, d M Y') : "-",
                "waktu_jam" => $laporan->created_at ? Carbon::parse($laporan->created_at)->format('H:i') . " WIB" : "-",
                "jumlah" => $laporan->measured_qty ?? '0',
                "satuan" => $laporan->unit_symbol ?? 'Kg',
                "sumber" => $laporan->location_name ?? 'TIDAK DIKETAHUI',
                "catatan" => $laporan->notes ?? 'Tidak ada catatan.',
                "foto" => $laporan->photo_path ? asset('storage/' . $laporan->photo_path) : null
            ];

            return response()->json(['success' => true, 'data' => $data]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function stocks(Request $request)
    {
        try {
            $user = Auth::user();
            
            $subCategories = DB::table('waste_sub_category')
                ->join('waste_category', 'waste_sub_category.id_waste_category', '=', 'waste_category.id')
                ->join('unit_measured', 'waste_sub_category.id_unit_measured', '=', 'unit_measured.id')
                ->select(
                    'waste_sub_category.id',
                    'waste_sub_category.name as sub_name',
                    'waste_category.name as cat_name',
                    'unit_measured.symbol as unit_symbol'
                )
                ->where('waste_sub_category.is_active', true)
                ->get();
                
            $data = [];
            foreach($subCategories as $sub) {
                $masuk = DB::table('waste_entry')->where('id_waste_sub_category', $sub->id)->sum('measured_qty');
                $keluar = DB::table('data_waste_out')
                            ->where('is_processed_waste', false)
                            ->where('id_waste_sub_category', $sub->id)
                            ->sum('measured_qty');
                $diolah = DB::table('waste_raw_materials')->where('id_waste_sub_category', $sub->id)->sum('measured_qty');
                
                $stok = $masuk - $keluar - $diolah;
                
                $data[] = [
                    "id" => $sub->id,
                    "kategori" => $sub->sub_name,
                    "jenis_kategori" => $sub->cat_name,
                    "jumlah" => floatval($stok) . " " . $sub->unit_symbol,
                    "isBotol" => str_contains(strtolower($sub->sub_name), 'botol'),
                    "waktu" => "-"
                ];
            }
            
            $processed = DB::table('processed_waste')
                ->join('unit_measured', 'processed_waste.id_unit_measured', '=', 'unit_measured.id')
                ->select(
                    'processed_waste.id',
                    'processed_waste.name as sub_name',
                    'unit_measured.symbol as unit_symbol'
                )->get();
                
            foreach($processed as $proc) {
                $masuk = DB::table('processed_waste_data')->where('id_processed_waste', $proc->id)->sum('measured_qty');
                $keluar = DB::table('data_waste_out')
                            ->where('is_processed_waste', true)
                            ->where('id_processed_waste', $proc->id)
                            ->sum('measured_qty');
                
                $stok = $masuk - $keluar;
                
                $data[] = [
                    "id" => "p_" . $proc->id,
                    "kategori" => $proc->sub_name,
                    "jenis_kategori" => "Hasil Olahan",
                    "jumlah" => floatval($stok) . " " . $proc->unit_symbol,
                    "isBotol" => false,
                    "waktu" => "-"
                ];
            }
            
            return response()->json(['success' => true, 'data' => $data]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}