<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ValidatesTransactions;
use App\Http\Controllers\Controller;
use App\Models\WasteEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WasteEntryController extends Controller
{
    use ValidatesTransactions;

    public function store(Request $request)
    {
        $this->normalizeDecimal($request, 'measured_qty');

        // 1. Validasi data yang masuk dari Flutter
        $request->validate([
            'id_waste_sub_category' => [
                'required',
                Rule::exists('waste_sub_category', 'id')->where('is_active', true),
            ],
            'id_source_location_waste' => 'required|exists:source_location_waste,id',
            'measured_qty' => 'required|numeric|gt:0|max:100000',
            'notes' => 'nullable|string|max:1000',
            'created_at' => $this->transactionTimeRules(),
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ], array_merge($this->transactionTimeMessages(), [
            'id_waste_sub_category.exists' => 'Jenis sampah tidak ditemukan atau sudah dinonaktifkan.',
            'measured_qty.gt' => 'Berat harus lebih dari 0.',
        ]));

        $photoPath = null;

        try {
            $wasteEntry = DB::transaction(function () use ($request, &$photoPath) {
                // id_user diambil dari PIC yang sedang login via token sanctum
                $wasteEntry = WasteEntry::create([
                    'id_user' => $request->user()->id,
                    'id_waste_sub_category' => $request->id_waste_sub_category,
                    'id_source_location_waste' => $request->id_source_location_waste,
                    'measured_qty' => $request->measured_qty,
                    'notes' => $request->notes,
                    'created_at' => $this->transactionTime($request),
                ]);

                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('attachments', 'public');
                    DB::table('attachment_waste_entry')->insert([
                        'id_waste_entry' => $wasteEntry->id,
                        'path' => $photoPath,
                    ]);
                }

                return $wasteEntry;
            });
        } catch (\Throwable $e) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data sampah masuk berhasil disimpan!',
            'data' => ['id' => $wasteEntry->id],
        ], 201);
    }
}
