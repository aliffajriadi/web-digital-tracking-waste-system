<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteEntry;
use App\Models\WasteOutData;
use App\Models\ProcessedWasteData;
use App\Models\User;
use App\Models\WasteSubCategory;
use App\Models\WasteSellingData;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Summary stats
        $stats = [
            'waste_entry_count'     => WasteEntry::count(),
            'waste_out_count'       => WasteOutData::count(),
            'processed_waste_count' => ProcessedWasteData::count(),
            'pic_count'             => User::where('role_id', 2)->count(),
            'total_revenue'         => WasteSellingData::sum('total_revenue'),
        ];

        // Monthly waste entry (last 6 months)
        $monthlyEntry = WasteEntry::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(measured_qty) as total_qty'),
                DB::raw('COUNT(*) as total_count')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->values();

        // Top waste subcategories
        $topWaste = WasteEntry::select('id_waste_sub_category', DB::raw('SUM(measured_qty) as total'))
            ->groupBy('id_waste_sub_category')
            ->orderByDesc('total')
            ->limit(5)
            ->with('subCategory')
            ->get();

        // Recent waste entries
        $recentEntries = WasteEntry::with(['user.picDetail', 'subCategory.category'])
            ->latest()
            ->limit(8)
            ->get();

        return view('pages.dashboard-admin.index', compact('stats', 'monthlyEntry', 'topWaste', 'recentEntries'));
    }
}
