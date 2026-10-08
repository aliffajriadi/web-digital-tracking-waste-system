<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteEntry;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    public function __construct(private StockService $stock)
    {
    }

    public function getDashboardData(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $picDetail = DB::table('pic_detail')->where('id_user', $user->id)->first();

        $categories = DB::table('waste_category')->orderBy('name')->get()->map(function ($cat) {
            $cat->photo_url = $cat->photo ? asset('storage/' . $cat->photo) : null;
            return $cat;
        });

        // Riwayat sampah masuk hari ini milik PIC
        $recentEntries = WasteEntry::with(['subCategory.unitMeasured', 'sourceLocation'])
            ->where('id_user', $user->id)
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $entryToday = DB::table('waste_entry')
            ->where('id_user', $user->id)
            ->whereDate('created_at', $today);

        $outToday = DB::table('waste_out_data')
            ->where('id_user', $user->id)
            ->whereDate('created_at', $today);

        $processedToday = DB::table('processed_waste_data')
            ->where('id_user', $user->id)
            ->whereDate('created_at', $today);

        $outWeightToday = DB::table('data_waste_out')
            ->join('waste_out_data', 'waste_out_data.id', '=', 'data_waste_out.id_waste_out_data')
            ->where('waste_out_data.id_user', $user->id)
            ->whereDate('waste_out_data.created_at', $today)
            ->sum('data_waste_out.measured_qty');

        return response()->json([
            'success' => true,
            'full_name' => $picDetail->full_name ?? $user->email,
            'user_photo' => $user->photo,
            'user_photo_url' => $user->photo ? asset('storage/' . $user->photo) : null,
            'categories' => $categories,
            'recent_entries' => $recentEntries,
            'today_summary' => [
                // Jumlah transaksi (key lama)
                'total_masuk' => (clone $entryToday)->count(),
                'sampah_keluar' => (clone $outToday)->count(),
                'sudah_diolah' => (clone $processedToday)->count(),
                // Total berat
                'berat_masuk' => (float) (clone $entryToday)->sum('measured_qty'),
                'berat_keluar' => (float) $outWeightToday,
                'berat_diolah' => (float) (clone $processedToday)->sum('measured_qty'),
            ],
            'b3_alert_count' => $this->stock->b3Alerts()->count(),
        ], 200);
    }
}
