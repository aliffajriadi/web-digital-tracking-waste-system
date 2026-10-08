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
use App\Services\StockService;
use Carbon\Carbon;

class WasteOutController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteOutData::with(['wasteOutMethod', 'wasteDestination', 'attachment', 'user.picDetail', 'user.adminDetail', 'sellingData', 'dataWasteOut'])->latest();

        if ($request->filled('method')) {
            $query->where('id_waste_out_method', $request->method);
        }

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
        $subCategories = WasteSubCategory::with('unitMeasured')->where('is_active', true)->orderBy('name')->get();
        $processedWastes = ProcessedWaste::with('unitMeasured')->orderBy('name')->get();
        $stockService = app(StockService::class);
        $rawStocks = $stockService->rawStocks($subCategories->pluck('id')->all());
        $processedStocks = $stockService->processedStocks($processedWastes->pluck('id')->all());

        return view('pages.waste-out.index', compact('wasteOuts', 'methods', 'destinations', 'buyers', 'subCategories', 'processedWastes', 'rawStocks', 'processedStocks'));
    }

    public function store(Request $request, StockService $stock)
    {
        $request->validate([
            'id_waste_out_method' => 'required|exists:waste_out_method,id',
            'id_waste_destination' => 'nullable|exists:waste_destinations,id',
            'notes' => 'nullable|string|max:1000',
            'created_at' => ['nullable', 'date', 'before_or_equal:now'],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            // Selling data
            'id_buyer' => 'nullable|exists:data_collector_buyer,id',
            'total_revenue' => 'nullable|numeric|min:0',
            // Items
            'items' => 'required|array|min:1',
            'items.*.is_processed' => 'required|boolean',
            'items.*.id_waste_sub_category' => 'required_if:items.*.is_processed,0|nullable|exists:waste_sub_category,id',
            'items.*.id_processed_waste' => 'required_if:items.*.is_processed,1|nullable|exists:processed_waste,id',
            'items.*.measured_qty' => 'required|numeric|gt:0',
        ], [
            'items.required' => 'Tambahkan minimal satu item sampah.',
            'items.*.measured_qty.gt' => 'Berat setiap item harus lebih dari 0.',
            'created_at.before_or_equal' => 'Tanggal keluar tidak boleh di masa depan.',
        ]);

        $method = WasteOutMethod::findOrFail($request->id_waste_out_method);
        if ($method->is_selling) {
            $request->validate([
                'id_buyer' => 'required',
                'total_revenue' => 'required',
            ], [
                'id_buyer.required' => 'Pembeli wajib dipilih untuk metode penjualan.',
                'total_revenue.required' => 'Total pendapatan wajib diisi untuk metode penjualan.',
            ]);
        }

        $rawNeeds = [];
        $processedNeeds = [];
        foreach ($request->items as $item) {
            if ($item['is_processed']) {
                $id = (int) $item['id_processed_waste'];
                $processedNeeds[$id] = ($processedNeeds[$id] ?? 0) + (float) $item['measured_qty'];
            } else {
                $id = (int) $item['id_waste_sub_category'];
                $rawNeeds[$id] = ($rawNeeds[$id] ?? 0) + (float) $item['measured_qty'];
            }
        }

        DB::transaction(function () use ($request, $method, $stock, $rawNeeds, $processedNeeds) {
            $stock->ensureAvailable($rawNeeds, $processedNeeds);

            $wasteOut = WasteOutData::create([
                'id_user'              => auth()->id(),
                'id_waste_out_method'  => $request->id_waste_out_method,
                'id_waste_destination' => $request->id_waste_destination,
                'notes'                => $request->notes,
                'created_at'           => $request->filled('created_at') ? Carbon::parse($request->created_at) : now(),
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('attachments/waste-out', 'public');
                AttachmentWasteOutData::create([
                    'id_waste_out_data' => $wasteOut->id,
                    'path' => $path,
                ]);
            }

            if ($method->is_selling || $request->filled('id_buyer')) {
                WasteSellingData::create([
                    'id_waste_out_data' => $wasteOut->id,
                    'id_buyer' => $request->id_buyer,
                    'total_revenue' => $request->total_revenue ?? 0,
                ]);
            }

            foreach ($rawNeeds as $id => $qty) {
                DataWasteOut::create([
                    'id_waste_out_data' => $wasteOut->id,
                    'is_processed_waste' => false,
                    'id_waste_sub_category' => $id,
                    'id_processed_waste' => null,
                    'measured_qty' => $qty,
                ]);
            }
            foreach ($processedNeeds as $id => $qty) {
                DataWasteOut::create([
                    'id_waste_out_data' => $wasteOut->id,
                    'is_processed_waste' => true,
                    'id_waste_sub_category' => null,
                    'id_processed_waste' => $id,
                    'measured_qty' => $qty,
                ]);
            }
        });

        return back()->with('success', 'Data sampah keluar berhasil ditambahkan.');
    }

    public function show(WasteOutData $wasteOut)
    {
        $wasteOut->load(['user.picDetail', 'user.adminDetail', 'dataWasteOut.wasteSubCategory.unitMeasured', 'dataWasteOut.processedWaste.unitMeasured', 'wasteOutMethod', 'wasteDestination', 'dataWasteOut.wasteSubCategory', 'dataWasteOut.processedWaste', 'sellingData.buyer', 'attachment']);
        return view('pages.waste-out.show', compact('wasteOut'));
    }
}
