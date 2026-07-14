<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteOutData;
use App\Models\DataWasteOut;
use App\Models\WasteSellingData;
use App\Models\AttachmentWasteOutData;
use App\Models\WasteOutMethod;
use App\Models\WasteDestinations;
use App\Models\DataCollectorBuyer;
use App\Models\WasteSubCategory;
use App\Models\ProcessedWaste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WasteOutController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteOutData::with(['wasteOutMethod', 'wasteDestination', 'attachment'])->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $wasteOuts = $query->paginate(15)->withQueryString();
        
        $methods = WasteOutMethod::all();
        $destinations = WasteDestinations::all();
        $buyers = DataCollectorBuyer::all();
        $subCategories = WasteSubCategory::all();
        $processedWastes = ProcessedWaste::all();

        return view('pages.waste-out.index', compact('wasteOuts', 'methods', 'destinations', 'buyers', 'subCategories', 'processedWastes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_waste_out_method' => 'required|exists:waste_out_method,id',
            'id_waste_destination' => 'nullable|exists:waste_destinations,id',
            'notes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Selling data
            'id_buyer' => 'required_if:id_waste_out_method,1|nullable|exists:data_collector_buyer,id',
            'total_revenue' => 'required_if:id_waste_out_method,1|nullable|numeric|min:0',
            // Items
            'items' => 'required|array|min:1',
            'items.*.is_processed' => 'required|boolean',
            'items.*.id_waste_sub_category' => 'required_if:items.*.is_processed,0|nullable|exists:waste_sub_category,id',
            'items.*.id_processed_waste' => 'required_if:items.*.is_processed,1|nullable|exists:processed_waste,id',
            'items.*.measured_qty' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $wasteOut = WasteOutData::create([
                'id_user'              => auth()->id(),
                'id_waste_out_method'  => $request->id_waste_out_method,
                'id_waste_destination' => $request->id_waste_destination,
                'notes'                => $request->notes,
                'created_at'           => now(),
            ]);

            // Handle Image
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('attachments/waste-out', 'public');
                AttachmentWasteOutData::create([
                    'id_waste_out_data' => $wasteOut->id,
                    'path' => $path,
                ]);
            }

            // Handle Selling Data
            if ($request->id_waste_out_method == 1) { // Assuming 1 is Selling
                WasteSellingData::create([
                    'id_waste_out_data' => $wasteOut->id,
                    'id_buyer' => $request->id_buyer,
                    'total_revenue' => $request->total_revenue,
                ]);
            }

            // Handle Items
            foreach ($request->items as $item) {
                DataWasteOut::create([
                    'id_waste_out_data' => $wasteOut->id,
                    'is_processed_waste' => $item['is_processed'],
                    'id_waste_sub_category' => !$item['is_processed'] ? $item['id_waste_sub_category'] : null,
                    'id_processed_waste' => $item['is_processed'] ? $item['id_processed_waste'] : null,
                    'measured_qty' => $item['measured_qty'],
                ]);
            }

            DB::commit();
            return back()->with('success', 'Data sampah keluar berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(WasteOutData $wasteOut)
    {
        $wasteOut->load(['wasteOutMethod', 'wasteDestination', 'dataWasteOut.wasteSubCategory', 'dataWasteOut.processedWaste', 'sellingData.buyer', 'attachment']);
        return view('pages.waste-out.show', compact('wasteOut'));
    }
}
