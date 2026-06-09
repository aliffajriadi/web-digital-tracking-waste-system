<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryReport;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportSubmissionController extends Controller
{
    // Fungsi untuk mengambil daftar kategori (Untuk Dropdown Flutter)
    public function getCategories()
    {
        try {
            // Ambil data kategori, pastikan Model CategoryReport sudah dibuat
            $categories = CategoryReport::select('id', 'name')->get();
            
            return response()->json([
                'success' => true,
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeKendala(Request $request)
    {
        // 1. Validasi (Attachment dibuat nullable agar tidak wajib upload foto)
        $request->validate([
            'id_category_report' => 'required|exists:category_report,id',
            'title'              => 'required|string|max:255',
            'content'            => 'required|string',
            'attachment'         => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // 2. Simpan ke tabel reports
            // Menggunakan $request->user()->id jauh lebih aman daripada mengambil ID dari Flutter
            $report = Report::create([
                'id_user'            => $request->user()->id, 
                'id_category_report' => $request->id_category_report,
                'title'              => $request->title,
                'content'            => $request->content,
            ]);

            // 3. Simpan lampiran jika ada
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                
                // Beri nama unik agar tidak tertimpa
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('attachments/reports', $filename, 'public');

                // Gunakan Query Builder atau Model Attachment jika ada
                DB::table('attachment_report')->insert([
                    'id_report'  => $report->id,
                    'path'       => $path,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Laporan kendala berhasil disimpan!',
                'data'    => $report
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Fungsi untuk mengambil detail satu laporan kendala berdasarkan ID
    public function showKendala($id)
    {
        try {
            // 1. Ambil data report mentah
            $report = Report::find($id);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan kendala tidak ditemukan.'
                ], 404);
            }

            // 2. Ambil nama kategori langsung dari tabel induknya
            $category = DB::table('category_report')
                ->where('id', $report->id_category_report)
                ->first();

            // 3. Ambil data lampiran foto
            $attachment = DB::table('attachment_report')
                ->where('id_report', $id)
                ->first();

            // 4. Susun respond JSON yang super aman dari data null
            $responseData = [
                'id' => $report->id,
                'id_user' => $report->id_user,
                'category_name' => $category ? $category->name : 'Kategori Umum',
                'title' => $report->title,
                'content' => $report->content,
                'attachment_path' => $attachment ? asset('storage/' . $attachment->path) : null,
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail laporan: ' . $e->getMessage()
            ], 500);
        }
    }
}