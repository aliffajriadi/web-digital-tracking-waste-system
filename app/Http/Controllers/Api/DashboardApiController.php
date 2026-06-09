<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\WasteEntry; 
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    public function getDashboardData(Request $request)
    {
        try {
            // 1. Membaca user yang sah dari kiriman Token Bearer Flutter
            $user = Auth::user(); 

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Sesi login tidak valid.'], 401);
            }

            $today = Carbon::today();

            // 2. Ambil data profil berdasarkan id_user yang sedang login (bukan NIK lagi, ini lebih akurat)
            $picDetail = DB::table('pic_detail')->where('id_user', $user->id)->first();

            // 3. Ambil Kategori (Tetap semua untuk keperluan menu pilihan di Flutter)
            $categories = DB::table('waste_category')->get();

            // 4. Ambil Riwayat (Ganti 'id_pic' menjadi 'id_user' sesuai database kamu!)
            $recentEntries = WasteEntry::with(['subCategory.unitMeasured', 'sourceLocation'])
                ->where('id_user', $user->id) 
                ->whereDate('created_at', $today)
                ->orderBy('created_at', 'desc')
                ->get();

            // 5. HITUNG RINGKASAN METRIK HARIAN (Semua diubah ke 'id_user')
            $totalMasukHariIni = DB::table('waste_entry')
                ->where('id_user', $user->id)
                ->whereDate('created_at', $today)
                ->count();

            $totalKeluarHariIni = DB::table('waste_out_data')
                ->where('id_user', $user->id)
                ->whereDate('created_at', $today)
                ->count();

            $totalDiolahHariIni = DB::table('processed_waste_data')
                ->where('id_user', $user->id)
                ->whereDate('created_at', $today)
                ->count();

            $photo = $picDetail && property_exists($picDetail, 'photo') ? $picDetail->photo : null;

            return response()->json([
                'success' => true,
                'full_name' => $picDetail->full_name ?? $user->name,
                'user_photo' => $photo, 
                'categories' => $categories,
                'recent_entries' => $recentEntries,
                'today_summary' => [
                    'total_masuk' => $totalMasukHariIni,
                    'sampah_keluar' => $totalKeluarHariIni,
                    'sudah_diolah' => $totalDiolahHariIni,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error Laravel: ' . $e->getMessage()
            ], 500);
        }
    }
}