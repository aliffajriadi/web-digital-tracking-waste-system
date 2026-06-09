<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessedWasteData;
use App\Models\ProcessedWaste;
use App\Models\WasteSubCategory;
use App\Models\WasteRawMaterials;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessedWasteDataController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcessedWasteData::with(['processedWaste', 'user.picDetail'])->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $processedData = $query->paginate(15)->withQueryString();
        return view('pages.processed-waste-data.index', compact('processedData'));
    }

    public function create()
    {
        $processedWastes = ProcessedWaste::all();
        $subCategories = WasteSubCategory::all();
        $users = User::where('role_id', 2)->with('picDetail')->get(); // assuming PIC role is 2

        return view('pages.processed-waste-data.create', compact('processedWastes', 'subCategories', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_processed_waste' => 'required|exists:processed_waste,id',
            'measured_qty' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'raw_materials' => 'required|array|min:1',
            'raw_materials.*.id_waste_sub_category' => 'required|exists:waste_sub_category,id',
            'raw_materials.*.measured_qty' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $processedData = ProcessedWasteData::create([
                'id_user' => $request->id_user,
                'id_processed_waste' => $request->id_processed_waste,
                'measured_qty' => $request->measured_qty,
                'notes' => $request->notes,
                'created_at' => now(),
            ]);

            foreach ($request->raw_materials as $material) {
                WasteRawMaterials::create([
                    'id_processed_waste_data' => $processedData->id,
                    'id_waste_sub_category' => $material['id_waste_sub_category'],
                    'measured_qty' => $material['measured_qty'],
                ]);
            }

            DB::commit();
            return redirect()->route('admin.processed-waste-data.index')->with('success', 'Data pengolahan sampah berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(ProcessedWasteData $processedWasteData)
    {
        $processedWasteData->load(['processedWaste', 'user.picDetail']);
        return view('pages.processed-waste-data.show', compact('processedWasteData'));
    }
}
