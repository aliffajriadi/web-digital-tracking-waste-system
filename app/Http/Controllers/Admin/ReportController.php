<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteEntry;
use App\Models\WasteOutData;
use App\Models\DataWasteOut;
use App\Models\ProcessedWasteData;
use App\Models\WasteSubCategory;
use App\Models\WasteCategory;
use App\Models\WasteSellingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Get base query with filters applied.
     */
    private function buildFilteredQuery(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::now()->startOfMonth();
        $dateTo   = $request->date_to   ? Carbon::parse($request->date_to)->endOfDay()     : Carbon::now()->endOfDay();

        return [$dateFrom, $dateTo];
    }

    /**
     * Main report dashboard.
     */
    public function index(Request $request)
    {
        [$dateFrom, $dateTo] = $this->buildFilteredQuery($request);

        // === WASTE ENTRY (SAMPAH MASUK) ===
        $wasteEntries = WasteEntry::with(['user.picDetail', 'subCategory.category', 'subCategory.unitMeasured', 'sourceLocation', 'attachment'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalWasteIn = $wasteEntries->sum('measured_qty');

        // === WASTE OUT (SAMPAH KELUAR) ===
        $wasteOutRecords = WasteOutData::with(['wasteOutMethod', 'wasteDestination', 'user.picDetail', 'dataWasteOut.wasteSubCategory.unitMeasured', 'dataWasteOut.processedWaste'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalWasteOut = DataWasteOut::whereHas('wasteOutData', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('created_at', [$dateFrom, $dateTo]);
        })->sum('measured_qty');

        // === PROCESSED WASTE (SAMPAH DIOLAH) ===
        $processedWasteRecords = ProcessedWasteData::with(['processedWaste.unitMeasured', 'user.picDetail', 'rawMaterials.wasteSubCategory'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalProcessed = $processedWasteRecords->sum('measured_qty');

        // === CHART: Monthly Waste In (last 12 months from date range) ===
        $monthlyWasteIn = WasteEntry::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(measured_qty) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // === CHART: Waste by Sub-Category ===
        $wasteByCategory = WasteEntry::select(
                'id_waste_sub_category',
                DB::raw('SUM(measured_qty) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('id_waste_sub_category')
            ->orderByDesc('total')
            ->with('subCategory.category', 'subCategory.unitMeasured')
            ->limit(10)
            ->get();

        // === CHART: Waste by Source Location ===
        $wasteByLocation = WasteEntry::select(
                'id_source_location_waste',
                DB::raw('SUM(measured_qty) as total')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('id_source_location_waste')
            ->with('sourceLocation')
            ->orderByDesc('total')
            ->get();

        // === CHART: Daily trend (within selected range, max 60 days) ===
        $dailyTrend = WasteEntry::select(
                DB::raw("DATE(created_at) as date"),
                DB::raw('SUM(measured_qty) as total')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // === STATS SUMMARY ===
        $stats = [
            'total_waste_in'    => round($totalWasteIn, 2),
            'total_waste_out'   => round($totalWasteOut, 2),
            'total_processed'   => round($totalProcessed, 2),
            'total_entries'     => $wasteEntries->count(),
            'total_out_records' => $wasteOutRecords->count(),
            'unique_pics'       => $wasteEntries->pluck('user.picDetail.full_name')->filter()->unique()->count(),
            'total_revenue'     => WasteSellingData::whereBetween('created_at', [$dateFrom, $dateTo])->sum('total_revenue'),
        ];

        return view('pages.report.index', compact(
            'wasteEntries',
            'wasteOutRecords',
            'processedWasteRecords',
            'monthlyWasteIn',
            'wasteByCategory',
            'wasteByLocation',
            'dailyTrend',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Export to Excel (CSV format, opens in Excel).
     */
    public function exportExcel(Request $request)
    {
        [$dateFrom, $dateTo] = $this->buildFilteredQuery($request);

        $wasteEntries = WasteEntry::with(['user.picDetail', 'subCategory.category', 'subCategory.unitMeasured', 'sourceLocation'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $wasteOutRecords = WasteOutData::with(['wasteOutMethod', 'wasteDestination', 'user.picDetail', 'dataWasteOut.wasteSubCategory.unitMeasured'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $processedWasteRecords = ProcessedWasteData::with(['processedWaste.unitMeasured', 'user.picDetail'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'Laporan_Sampah_' . $dateFrom->format('d-m-Y') . '_sd_' . $dateTo->format('d-m-Y') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($wasteEntries, $wasteOutRecords, $processedWasteRecords, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fputs($handle, "\xEF\xBB\xBF");

            // ── HEADER INFO ──
            fputcsv($handle, ['LAPORAN PENGELOLAAN SAMPAH']);
            fputcsv($handle, ['Sistem Monitoring Rumah Sampah - WasteTracking']);
            fputcsv($handle, ['Periode', $dateFrom->format('d M Y') . ' s/d ' . $dateTo->format('d M Y')]);
            fputcsv($handle, ['Dicetak Pada', now()->format('d M Y, H:i') . ' WIB']);
            fputcsv($handle, []);

            // ── RINGKASAN ──
            fputcsv($handle, ['RINGKASAN LAPORAN']);
            fputcsv($handle, ['Total Sampah Masuk (Kg)', $wasteEntries->sum('measured_qty')]);
            fputcsv($handle, ['Total Transaksi Masuk', $wasteEntries->count()]);
            fputcsv($handle, ['Total Transaksi Keluar', $wasteOutRecords->count()]);
            fputcsv($handle, ['Total Sampah Diolah', $processedWasteRecords->count()]);
            fputcsv($handle, []);

            // ── SHEET 1: SAMPAH MASUK ──
            fputcsv($handle, ['A. DATA SAMPAH MASUK (WASTE ENTRY)']);
            fputcsv($handle, ['No', 'Tanggal & Waktu', 'PIC', 'Kategori', 'Sub Kategori', 'Jumlah', 'Satuan', 'Sumber Lokasi', 'Catatan']);

            foreach ($wasteEntries as $i => $entry) {
                fputcsv($handle, [
                    $i + 1,
                    Carbon::parse($entry->created_at)->format('d/m/Y H:i'),
                    $entry->user?->picDetail?->full_name ?? $entry->user?->email ?? '-',
                    $entry->subCategory?->category?->name ?? '-',
                    $entry->subCategory?->name ?? '-',
                    floatval($entry->measured_qty),
                    $entry->subCategory?->unitMeasured?->symbol ?? 'Kg',
                    $entry->sourceLocation?->name ?? '-',
                    $entry->notes ?? '-',
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Total', '', '', '', '', $wasteEntries->sum('measured_qty'), '', '', '']);
            fputcsv($handle, []);

            // ── SHEET 2: SAMPAH KELUAR ──
            fputcsv($handle, ['B. DATA SAMPAH KELUAR (WASTE OUT)']);
            fputcsv($handle, ['No', 'Tanggal & Waktu', 'PIC', 'Metode', 'Tujuan', 'Item Sampah', 'Jumlah', 'Satuan', 'Catatan']);

            $rowNum = 1;
            foreach ($wasteOutRecords as $out) {
                foreach ($out->dataWasteOut as $detail) {
                    fputcsv($handle, [
                        $rowNum++,
                        Carbon::parse($out->created_at)->format('d/m/Y H:i'),
                        $out->user?->picDetail?->full_name ?? $out->user?->email ?? '-',
                        $out->wasteOutMethod?->name ?? '-',
                        $out->wasteDestination?->name ?? '-',
                        $detail->wasteSubCategory?->name ?? ($detail->processedWaste?->name ?? '-'),
                        floatval($detail->measured_qty),
                        $detail->wasteSubCategory?->unitMeasured?->symbol ?? 'Kg',
                        $out->notes ?? '-',
                    ]);
                }
            }
            fputcsv($handle, []);

            // ── SHEET 3: SAMPAH DIOLAH ──
            fputcsv($handle, ['C. DATA SAMPAH DIOLAH (PROCESSED WASTE)']);
            fputcsv($handle, ['No', 'Tanggal & Waktu', 'PIC', 'Produk Olahan', 'Jumlah', 'Satuan', 'Catatan']);

            foreach ($processedWasteRecords as $i => $proc) {
                fputcsv($handle, [
                    $i + 1,
                    Carbon::parse($proc->created_at)->format('d/m/Y H:i'),
                    $proc->user?->picDetail?->full_name ?? $proc->user?->email ?? '-',
                    $proc->processedWaste?->name ?? '-',
                    floatval($proc->measured_qty),
                    $proc->processedWaste?->unitMeasured?->symbol ?? 'Kg',
                    $proc->notes ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export PDF view (print-friendly).
     */
    public function exportPdf(Request $request)
    {
        [$dateFrom, $dateTo] = $this->buildFilteredQuery($request);

        $wasteEntries = WasteEntry::with(['user.picDetail', 'subCategory.category', 'subCategory.unitMeasured', 'sourceLocation'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $wasteOutRecords = WasteOutData::with(['wasteOutMethod', 'wasteDestination', 'user.picDetail', 'dataWasteOut.wasteSubCategory.unitMeasured', 'dataWasteOut.processedWaste'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $processedWasteRecords = ProcessedWasteData::with(['processedWaste.unitMeasured', 'user.picDetail'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $wasteByCategory = WasteEntry::select(
                'id_waste_sub_category',
                DB::raw('SUM(measured_qty) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('id_waste_sub_category')
            ->with('subCategory.category', 'subCategory.unitMeasured')
            ->orderByDesc('total')
            ->get();

        $stats = [
            'total_waste_in'    => round($wasteEntries->sum('measured_qty'), 2),
            'total_waste_out'   => round(DataWasteOut::whereHas('wasteOutData', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))->sum('measured_qty'), 2),
            'total_processed'   => round($processedWasteRecords->sum('measured_qty'), 2),
            'total_entries'     => $wasteEntries->count(),
            'total_out_records' => $wasteOutRecords->count(),
            'total_revenue'     => WasteSellingData::whereBetween('created_at', [$dateFrom, $dateTo])->sum('total_revenue'),
        ];

        return view('pages.report.pdf', compact(
            'wasteEntries',
            'wasteOutRecords',
            'processedWasteRecords',
            'wasteByCategory',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }
}
