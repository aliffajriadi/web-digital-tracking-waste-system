<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ValidatesTransactions;
use App\Http\Controllers\Controller;
use App\Models\ProcessedWaste;
use App\Models\ProcessedWasteData;
use App\Models\WasteRawMaterials;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProcessedWasteController extends Controller
{
    use ValidatesTransactions;

    public function __construct(private StockService $stock)
    {
    }

    public function index()
    {
        $items = ProcessedWaste::with('unitMeasured')->orderBy('name')->get();
        $stocks = $this->stock->processedStocks($items->pluck('id')->all());

        $data = $items->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'photo' => $p->photo,
            'photo_url' => $p->photo ? asset('storage/' . $p->photo) : null,
            'unit' => $p->unitMeasured?->symbol ?? 'kg',
            'default_measured_qty' => (float) $p->default_measured_qty,
            'stock' => (float) ($stocks[$p->id] ?? 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar jenis olahan berhasil dimuat',
            'data'    => $data,
        ], 200);
    }

    public function store(Request $request)
    {
        $this->normalizeDecimal($request, 'measured_qty');
        $this->decodeJsonField($request, 'raw_materials');

        $request->validate([
            'id_processed_waste' => 'required|exists:processed_waste,id',
            'measured_qty'       => 'required|numeric|gt:0|max:100000',
            'notes'              => 'nullable|string|max:1000',
            'created_at'         => $this->transactionTimeRules(),
            'raw_materials'      => 'required|array|min:1',
            'raw_materials.*.id_waste_sub_category' => [
                'required',
                Rule::exists('waste_sub_category', 'id'),
            ],
            'raw_materials.*.measured_qty' => 'required|numeric|gt:0',
        ], array_merge($this->transactionTimeMessages(), [
            'raw_materials.required' => 'Bahan baku olahan wajib diisi minimal 1.',
            'raw_materials.min' => 'Bahan baku olahan wajib diisi minimal 1.',
            'raw_materials.*.measured_qty.gt' => 'Berat bahan baku harus lebih dari 0.',
            'measured_qty.gt' => 'Berat hasil olahan harus lebih dari 0.',
        ]));

        // Gabungkan bahan baku yang sama lalu pastikan stoknya cukup
        $needs = [];
        foreach ($request->raw_materials as $material) {
            $id = (int) $material['id_waste_sub_category'];
            $needs[$id] = ($needs[$id] ?? 0) + (float) $material['measured_qty'];
        }

        $olahan = DB::transaction(function () use ($request, $needs) {
            $this->stock->ensureAvailable($needs, [], 'raw_materials');

            $olahan = ProcessedWasteData::create([
                'id_user' => $request->user()->id,
                'id_processed_waste' => $request->id_processed_waste,
                'measured_qty' => $request->measured_qty,
                'notes' => $request->notes,
                'created_at' => $this->transactionTime($request),
            ]);

            foreach ($needs as $idSub => $qty) {
                WasteRawMaterials::create([
                    'id_processed_waste_data' => $olahan->id,
                    'id_waste_sub_category' => $idSub,
                    'measured_qty' => $qty,
                ]);
            }

            return $olahan;
        });

        return response()->json([
            'success' => true,
            'message' => 'Data hasil pengolahan sampah berhasil dicatat!',
            'data'    => $olahan,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $data = ProcessedWasteData::with(['processedWaste.unitMeasured', 'rawMaterials.wasteSubCategory.unitMeasured'])
            ->where('id_user', $request->user()->id)
            ->find($id);

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $data->id,
                'name' => $data->processedWaste?->name ?? 'Produk Olahan',
                'quantity' => (float) $data->measured_qty,
                'unit' => $data->processedWaste?->unitMeasured?->symbol ?? 'kg',
                'notes' => $data->notes,
                'created_at' => $data->created_at?->toDateTimeString(),
                'date_label' => $data->created_at?->translatedFormat('l, d M Y'),
                'time_label' => $data->created_at?->format('H:i') . ' WIB',
                'raw_materials' => $data->rawMaterials->map(fn ($m) => [
                    'name' => $m->wasteSubCategory?->name ?? 'Sampah',
                    'quantity' => (float) $m->measured_qty,
                    'unit' => $m->wasteSubCategory?->unitMeasured?->symbol ?? 'kg',
                ])->values(),
            ],
        ]);
    }
}
