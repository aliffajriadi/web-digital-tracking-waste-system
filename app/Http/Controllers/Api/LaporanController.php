<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function __construct(private StockService $stock)
    {
    }

    /**
     * Daftar sampah masuk milik PIC yang sedang login.
     */
    public function index(Request $request)
    {
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
            ->where('waste_entry.id_user', $request->user()->id)
            ->orderBy('waste_entry.created_at', 'desc')
            ->limit(200)
            ->get();

        $data = $laporan->map(function ($item) {
            $subName = $item->sub_name ?? 'Sampah';
            $unit = $item->unit_symbol ?? 'kg';

            return [
                'id' => $item->id,
                'kategori' => $subName,
                'jenis_kategori' => $item->cat_name ?? 'Umum',
                'waktu' => $item->created_at ? Carbon::parse($item->created_at)->format('H:i') . ' WIB' : '-',
                'jumlah' => StockService::format((float) $item->measured_qty) . ' ' . $unit,
                'quantity' => (float) $item->measured_qty,
                'unit' => $unit,
                'created_at' => $item->created_at,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Detail satu data sampah masuk. Hanya boleh dilihat oleh PIC pemiliknya.
     */
    public function show(Request $request, $id)
    {
        $laporan = DB::table('waste_entry')
            ->leftJoin('waste_sub_category', 'waste_entry.id_waste_sub_category', '=', 'waste_sub_category.id')
            ->leftJoin('waste_category', 'waste_sub_category.id_waste_category', '=', 'waste_category.id')
            ->leftJoin('unit_measured', 'waste_sub_category.id_unit_measured', '=', 'unit_measured.id')
            ->leftJoin('source_location_waste', 'waste_entry.id_source_location_waste', '=', 'source_location_waste.id')
            ->leftJoin('attachment_waste_entry', 'waste_entry.id', '=', 'attachment_waste_entry.id_waste_entry')
            ->leftJoin('waste_b3_detail', 'waste_sub_category.id_waste_b3_detail', '=', 'waste_b3_detail.id')
            ->select(
                'waste_entry.id',
                'waste_entry.measured_qty',
                'waste_entry.notes',
                'waste_entry.created_at',
                'waste_sub_category.name as sub_name',
                'waste_category.name as cat_name',
                'unit_measured.symbol as unit_symbol',
                'source_location_waste.name as location_name',
                'attachment_waste_entry.path as photo_path',
                'waste_b3_detail.waste_code as b3_code'
            )
            ->where('waste_entry.id', $id)
            ->where('waste_entry.id_user', $request->user()->id)
            ->first();

        if (!$laporan) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $createdAt = $laporan->created_at ? Carbon::parse($laporan->created_at) : null;

        return response()->json(['success' => true, 'data' => [
            'id' => $laporan->id,
            'sub_kategori' => $laporan->sub_name ?? 'Sampah',
            'kategori' => $laporan->cat_name ?? 'Umum',
            'kategori_gabung' => ($laporan->cat_name ?? 'Umum') . ', ' . ($laporan->sub_name ?? 'Sampah'),
            'waktu_tanggal' => $createdAt?->translatedFormat('l, d M Y') ?? '-',
            'waktu_jam' => $createdAt ? $createdAt->format('H:i') . ' WIB' : '-',
            'jumlah' => (float) $laporan->measured_qty,
            'satuan' => $laporan->unit_symbol ?? 'kg',
            'sumber' => $laporan->location_name ?? 'Tidak diketahui',
            'catatan' => $laporan->notes,
            'b3_code' => $laporan->b3_code,
            'foto' => $laporan->photo_path ? asset('storage/' . $laporan->photo_path) : null,
        ]]);
    }

    /**
     * Stok gudang saat ini (sampah mentah per sub-kategori + hasil olahan).
     */
    public function stocks()
    {
        $data = $this->stock->stockList()->map(fn ($row) => array_merge($row, [
            // Key lama dipertahankan agar aplikasi versi sebelumnya tetap berjalan
            'kategori' => $row['name'],
            'jenis_kategori' => $row['category'],
            'jumlah' => StockService::format($row['stock']) . ' ' . $row['unit'],
            'waktu' => '-',
        ]));

        return response()->json(['success' => true, 'data' => $data]);
    }
}
