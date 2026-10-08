<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataWasteOut;
use App\Models\ProcessedWasteData;
use App\Models\Report;
use App\Models\User;
use App\Models\WasteEntry;
use App\Models\WasteOutData;
use App\Models\WasteSellingData;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(StockService $stock)
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $stats = [
            'waste_entry_count'     => WasteEntry::count(),
            'waste_out_count'       => WasteOutData::count(),
            'processed_waste_count' => ProcessedWasteData::count(),
            'pic_count'             => User::where('role_id', 2)->count(),
            'pic_active'            => User::where('role_id', 2)->where('is_active', true)->count(),
            'total_revenue'         => WasteSellingData::sum('total_revenue'),
            'revenue_month'         => WasteSellingData::where('created_at', '>=', $monthStart)->sum('total_revenue'),
            'in_today'              => (float) WasteEntry::whereDate('created_at', $today)->sum('measured_qty'),
            'in_today_count'        => WasteEntry::whereDate('created_at', $today)->count(),
            'out_today'             => (float) DataWasteOut::whereHas('wasteOutData', fn ($q) => $q->whereDate('created_at', $today))->sum('measured_qty'),
            'processed_today'       => (float) ProcessedWasteData::whereDate('created_at', $today)->sum('measured_qty'),
            'in_month'              => (float) WasteEntry::where('created_at', '>=', $monthStart)->sum('measured_qty'),
            'reports_week'          => Report::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        // Tren 14 hari terakhir: masuk vs keluar
        $start = Carbon::today()->subDays(13);
        $inDaily = WasteEntry::select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(measured_qty) as total'))
            ->where('created_at', '>=', $start)
            ->groupBy('d')
            ->pluck('total', 'd');
        $outDaily = DataWasteOut::join('waste_out_data', 'waste_out_data.id', '=', 'data_waste_out.id_waste_out_data')
            ->select(DB::raw('DATE(waste_out_data.created_at) as d'), DB::raw('SUM(data_waste_out.measured_qty) as total'))
            ->where('waste_out_data.created_at', '>=', $start)
            ->groupBy('d')
            ->pluck('total', 'd');

        $trend = collect(range(0, 13))->map(function ($i) use ($start, $inDaily, $outDaily) {
            $date = $start->copy()->addDays($i)->toDateString();
            return [
                'label' => Carbon::parse($date)->translatedFormat('d M'),
                'in' => round((float) ($inDaily[$date] ?? 0), 2),
                'out' => round((float) ($outDaily[$date] ?? 0), 2),
            ];
        });

        // Top sub-kategori bulan ini
        $topWaste = WasteEntry::select('id_waste_sub_category', DB::raw('SUM(measured_qty) as total'))
            ->where('created_at', '>=', $monthStart)
            ->groupBy('id_waste_sub_category')
            ->orderByDesc('total')
            ->limit(5)
            ->with('subCategory.unitMeasured')
            ->get();

        $recentEntries = WasteEntry::with(['user.picDetail', 'subCategory.category', 'subCategory.unitMeasured'])
            ->latest()
            ->limit(8)
            ->get();

        $stockRows = $stock->stockList();
        $topStock = $stockRows->where('stock', '>', 0)->sortByDesc('stock')->take(6)->values();
        $negativeStock = $stockRows->where('stock', '<', 0)->values();
        $b3Alerts = $stock->b3Alerts();

        return view('pages.dashboard-admin.index', compact(
            'stats', 'trend', 'topWaste', 'recentEntries', 'topStock', 'negativeStock', 'b3Alerts'
        ));
    }
}
