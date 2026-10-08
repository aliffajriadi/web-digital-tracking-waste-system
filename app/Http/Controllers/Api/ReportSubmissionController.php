<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoryReport;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportSubmissionController extends Controller
{
    // Daftar kategori kendala (untuk dropdown Flutter)
    public function getCategories()
    {
        $categories = CategoryReport::select('id', 'name')->orderBy('name')->get();

        return response()->json(['success' => true, 'data' => $categories], 200);
    }

    public function storeKendala(Request $request)
    {
        $request->validate([
            'id_category_report' => 'required|exists:category_report,id',
            'title'              => 'required|string|max:255',
            'content'            => 'required|string|max:5000',
            'attachment'         => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
        ], [
            'id_category_report.required' => 'Kategori kendala wajib dipilih.',
            'title.required' => 'Judul laporan wajib diisi.',
            'content.required' => 'Deskripsi kendala wajib diisi.',
        ]);

        $path = null;

        try {
            $report = DB::transaction(function () use ($request, &$path) {
                $report = Report::create([
                    'id_user'            => $request->user()->id,
                    'id_category_report' => $request->id_category_report,
                    'title'              => $request->title,
                    'content'            => $request->content,
                ]);

                if ($request->hasFile('attachment')) {
                    // Nama file dibuat acak oleh Laravel, bukan dari nama file asli pengguna
                    $path = $request->file('attachment')->store('attachments/reports', 'public');
                    DB::table('attachment_report')->insert([
                        'id_report' => $report->id,
                        'path'      => $path,
                    ]);
                }

                return $report;
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan laporan. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan kendala berhasil dikirim!',
            'data'    => $report,
        ], 201);
    }

    // Detail satu laporan kendala milik PIC yang sedang login
    public function showKendala(Request $request, $id)
    {
        $report = Report::with(['categoryReport', 'attachment'])
            ->where('id_user', $request->user()->id)
            ->find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan kendala tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $report->id,
                'id_user' => $report->id_user,
                'category_name' => $report->categoryReport->name ?? 'Kategori Umum',
                'title' => $report->title,
                'content' => $report->content,
                'attachment_path' => $report->attachment ? asset('storage/' . $report->attachment->path) : null,
                'created_at' => $report->created_at?->toDateTimeString(),
                'date_label' => $report->created_at?->translatedFormat('l, d M Y'),
                'time_label' => $report->created_at ? $report->created_at->format('H:i') . ' WIB' : null,
            ],
        ], 200);
    }
}
