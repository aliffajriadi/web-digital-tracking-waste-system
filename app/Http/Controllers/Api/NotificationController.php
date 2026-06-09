<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function getWarnings(Request $request)
    {
        try {
            // Kita coba ambil data dari tabel detailnya langsung terlebih dahulu 
            // untuk memastikan tabel ini bisa diakses tanpa crash karena join
            $transaksiB3 = DB::table('waste_b3_detail')->get();

            $filteredNotifications = [];
            $now = Carbon::now();

            foreach ($transaksiB3 as $item) {
                // Karena kita belum tahu pasti nama tabel transaksi masukmu,
                // kita simulasikan tanggal masuknya menggunakan tanggal hari ini (created_at diganti waktu sekarang)
                // Ini dilakukan untuk menguji apakah koneksi API ke Flutter sudah berhasil atau belum
                $tglMasuk = Carbon::now()->subDays(rand(1, 5)); 
                $tglExpired = $tglMasuk->copy()->addDays((int)$item->retention_period_day);
                
                // Hitung sisa hari
                $sisaHari = $now->diffInDays($tglExpired, false); 

                // Filter masa simpan kritis (sisa hari <= 10)
                if ($sisaHari <= 10) {
                    $filteredNotifications[] = [
                        'id' => $item->id,
                        'waste_code' => $item->waste_code,
                        'waste_name' => $item->description, // Menggunakan kolom description dari tabel detail
                        'retention_period_day' => $item->retention_period_day,
                        'created_at' => $tglMasuk->toDateTimeString(),
                        'sisa_hari' => (int) $sisaHari,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data peringatan berhasil dimuat',
                'data' => $filteredNotifications
            ], 200);

        } catch (\Exception $e) {
            // JIKA TERJADI EROR, SKRIP INI AKAN MENANGKAP PESANNYA 
            // Dan mengirimkan teks eror asli ke Flutter (bukan halaman HTML lagi)
            return response()->json([
                'success' => false,
                'message' => 'Eror Terjadi: ' . $e->getMessage() . ' pada baris ' . $e->getLine()
            ], 200); // Kita paksa status 200 agar Flutter berhasil membaca teks erornya
        }
    }
}