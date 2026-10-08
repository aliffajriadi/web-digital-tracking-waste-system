<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessedWaste;
use App\Models\ProcessedWasteData;
use App\Models\User;
use App\Models\WasteRawMaterials;
use App\Models\WasteSubCategory;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessedWasteDataController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcessedWasteData::with(['processedWaste.unitMeasured', 'user.picDetail', 'user.adminDetail', 'rawMaterials'])->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('processed')) {
            $query->where('id_processed_waste', $request->processed);
        }

        $processedData = $query->paginate(15)->withQueryString();
        $processedWastes = ProcessedWaste::orderBy('name')->get();

        return view('pages.processed-waste-data.index', compact('processedData', 'processedWastes'));
    }

    public function create(StockService $stock)
    {
        $processedWastes = ProcessedWaste::with('unitMeasured')->orderBy('name')->get();
        $subCategories = WasteSubCategory::with(['unitMeasured', 'category'])->where('is_active', true)->orderBy('name')->get();
        $users = User::where('role_id', 2)->where('is_active', true)->with('picDetail')->get();
        $rawStocks = $stock->rawStocks($subCategories->pluck('id')->all());

        return view('pages.processed-waste-data.create', compact('processedWastes', 'subCategories', 'users', 'rawStocks'));
    }

    public function store(Request $request, StockService $stock)
    {
        $request->validate([
            'id_user' => 'nullable|exists:users,id',
            'id_processed_waste' => 'required|exists:processed_waste,id',
            'measured_qty' => 'required|numeric|gt:0',
            'notes' => 'nullable|string|max:1000',
            'created_at' => ['nullable', 'date', 'before_or_equal:now'],
            'raw_materials' => 'required|array|min:1',
            'raw_materials.*.id_waste_sub_category' => 'required|exists:waste_sub_category,id',
            'raw_materials.*.measured_qty' => 'required|numeric|gt:0',
        ], [
            'raw_materials.required' => 'Tambahkan minimal satu bahan baku.',
            'raw_materials.*.measured_qty.gt' => 'Berat bahan baku harus lebih dari 0.',
            'measured_qty.gt' => 'Berat hasil olahan harus lebih dari 0.',
            'created_at.before_or_equal' => 'Tanggal pengolahan tidak boleh di masa depan.',
        ]);

        $needs = [];
        foreach ($request->raw_materials as $material) {
            $id = (int) $material['id_waste_sub_category'];
            $needs[$id] = ($needs[$id] ?? 0) + (float) $material['measured_qty'];
        }

        DB::transaction(function () use ($request, $stock, $needs) {
            $stock->ensureAvailable($needs, [], 'raw_materials');

            $processedData = ProcessedWasteData::create([
                // Jika tidak dipilih PIC, transaksi dicatat atas nama admin yang menginput
                'id_user' => $request->id_user ?: auth()->id(),
                'id_processed_waste' => $request->id_processed_waste,
                'measured_qty' => $request->measured_qty,
                'notes' => $request->notes,
                'created_at' => $request->filled('created_at') ? Carbon::parse($request->created_at) : now(),
            ]);

            foreach ($needs as $idSub => $qty) {
                WasteRawMaterials::create([
                    'id_processed_waste_data' => $processedData->id,
                    'id_waste_sub_category' => $idSub,
                    'measured_qty' => $qty,
                ]);
            }
        });

        return redirect()->route('admin.processed-waste-data.index')->with('success', 'Data pengolahan sampah berhasil ditambahkan.');
    }

    public function show(ProcessedWasteData $processedWasteData)
    {
        $processedWasteData->load(['processedWaste.unitMeasured', 'user.picDetail', 'user.adminDetail', 'rawMaterials.wasteSubCategory.unitMeasured', 'rawMaterials.wasteSubCategory.category']);

        return view('pages.processed-waste-data.show', ['data' => $processedWasteData]);
    }
}
