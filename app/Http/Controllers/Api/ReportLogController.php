<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WasteEntry;
use App\Models\WasteOutData;
use App\Models\ProcessedWasteData;
use App\Models\Report;

class ReportLogController extends Controller
{
    public function history(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $search = $request->query('search');
            $type = $request->query('type'); 
            // 1=Masuk, 2=Keluar, 3=Olahan, 4=Kendala

            $allLogs = collect();

            /*1. INPUT MASUK*/
            if (empty($type) || $type == 1) {
                $queryMasuk = \App\Models\WasteEntry::where('id_user', $userId)
                    ->with(['subCategory'])
                    ->latest();

                if (!empty($search)) {
                    $queryMasuk->whereHas('subCategory', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
                }

                $masuk = $queryMasuk->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'type_log' => 'input_masuk',
                            'title' => 'Input Masuk: ' . ($item->subCategory->name ?? 'Sampah'),
                            'time' => $item->created_at ? $item->created_at->format('H:i') . ' WIB' : '-',
                            'amount' => number_format($item->measured_qty, 0, ',', '.') . ' Kg',
                            'timestamp' => $item->created_at ? $item->created_at->timestamp : 0,
                            'date_group' => $item->created_at
                                ? $item->created_at->translatedFormat('l, d M Y')
                                : 'Tanpa Tanggal',
                        ];
                    });

                $allLogs = $allLogs->merge($masuk);
            }

            /*2. INPUT KELUAR*/
            if (empty($type) || $type == 2) {
                $queryKeluar = \App\Models\WasteOutData::where('id_user', $userId)
                    ->with([
                        'dataWasteOut.wasteSubCategory',
                        'dataWasteOut.processedWaste'
                    ])
                    ->latest();

                if (!empty($search)) {
                    $queryKeluar->whereHas('dataWasteOut.wasteSubCategory', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    })->orWhereHas('dataWasteOut.processedWaste', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
                }

                $keluar = $queryKeluar->orderBy('id', 'desc')
                    ->get()
                    ->flatMap(function ($item) {
                        return $item->dataWasteOut->map(function ($detail) use ($item) {
                            $namaSampah = $detail->is_processed_waste == 1
                                ? ($detail->processedWaste->name ?? 'Produk Olahan')
                                : ($detail->wasteSubCategory->name ?? 'Sampah Mentah');

                            return [
                                'id' => $item->id,
                                'type_log' => 'input_keluar',
                                'title' => 'Input Keluar: ' . $namaSampah,
                                'time' => $item->created_at ? $item->created_at->format('H:i') . ' WIB' : '-',
                                'amount' => number_format($detail->measured_qty, 0, ',', '.') . ' Kg',
                                'timestamp' => $item->created_at ? $item->created_at->timestamp : 0,
                                'date_group' => $item->created_at
                                    ? $item->created_at->translatedFormat('l, d M Y')
                                    : 'Tanpa Tanggal',
                            ];
                        });
                    });

                $allLogs = $allLogs->merge($keluar);
            }

            /*3. HASIL OLAHAN*/ 
            if (empty($type) || $type == 3) {
                $queryOlahan = \App\Models\ProcessedWasteData::where('id_user', $userId)
                    ->with(['processedWaste'])
                    ->latest();

                if (!empty($search)) {
                    $queryOlahan->whereHas('processedWaste', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
                }

                $olahan = $queryOlahan->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'type_log' => 'olahan',
                            'title' => 'Olahan: ' . ($item->processedWaste->name ?? 'Produk Jadi'),
                            'time' => $item->created_at ? $item->created_at->format('H:i') . ' WIB' : '-',
                            'amount' => number_format($item->measured_qty, 0, ',', '.') . ' Kg',
                            'timestamp' => $item->created_at ? $item->created_at->timestamp : 0,
                            'date_group' => $item->created_at
                                ? $item->created_at->translatedFormat('l, d M Y')
                                : 'Tanpa Tanggal',
                        ];
                    });

                $allLogs = $allLogs->merge($olahan);
            }

            /*4. LAPORAN KENDALA*/  
            if (empty($type) || $type == 4) {
                $queryKendala = \App\Models\Report::where('id_user', $userId)
                    ->with(['categoryReport']);
              

                if (!empty($search)) {
                    $queryKendala->where(function ($q) use ($search) {
                        $q->where('title', 'LIKE', "%{$search}%")
                          ->orWhere('content', 'LIKE', "%{$search}%");
                    });
                }

                $kendala = $queryKendala->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($item) {
                        $createdAt = \Carbon\Carbon::now();
                        $namaKategori = $item->categoryReport ? $item->categoryReport->name : 'Umum';

                        return [
                            'id' => $item->id,
                            'type_log' => 'kendala',
                            'title' => 'Kendala: ' . $namaKategori,
                            'time' => $createdAt ? $createdAt->format('H:i') . ' WIB' : '-',
                            'amount' => $item->title ?? '1 Berkas', 
                            'timestamp' => $createdAt->timestamp,
                            'date_group' => $createdAt->translatedFormat('l, d M Y'),
                        ];
                    });

                $allLogs = $allLogs->merge($kendala);
            }

            /*SORTING & GROUPING DATA*/ 
            // 1. Urutkan semua data dari yang paling baru berdasarkan timestamp asli
            $sortedLogs = $allLogs->sortByDesc('timestamp')->values();

            // 2. Kelompokkan berdasarkan date_group yang konsisten
            $groupedData = $sortedLogs->groupBy('date_group')->toArray();

            /*FILTER MENU*/
            $categories = [
                ['id' => '', 'name' => 'Semua'],
                ['id' => '1', 'name' => 'Input Masuk'],
                ['id' => '2', 'name' => 'Input Keluar'],
                ['id' => '3', 'name' => 'Hasil Olahan'],
                ['id' => '4', 'name' => 'Laporan Kendala'],
            ];
            
            // 3. Return response dengan aman (jika kosong berikan objek {}, jika ada pastikan strukturnya Map/Object)
            return response()->json([
                'success' => true,
                'categories' => $categories,
                'data' => empty($groupedData) ? new \stdClass() : (object)$groupedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}