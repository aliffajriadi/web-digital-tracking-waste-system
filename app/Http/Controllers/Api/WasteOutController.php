<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ValidatesTransactions;
use App\Http\Controllers\Controller;
use App\Models\ProcessedWaste;
use App\Models\WasteOutData;
use App\Models\WasteOutMethod;
use App\Models\WasteSubCategory;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class WasteOutController extends Controller
{
    use ValidatesTransactions;

    public function __construct(private StockService $stock)
    {
    }

    public function index()
    {
        $methods = WasteOutMethod::orderBy('name')->get()->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'description' => $m->description,
            'is_selling' => (bool) $m->is_selling,
            'photo' => $m->photo,
            'photo_url' => $m->photo ? asset('storage/' . $m->photo) : null,
        ]);

        return response()->json(['success' => true, 'data' => $methods], 200);
    }

    /**
     * Dipakai perangkat IoT untuk menu pilihan jenis sampah, jadi dibuat ringkas.
     */
    public function getSubcategories()
    {
        $data = WasteSubCategory::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'id_waste_category', 'id_unit_measured'])
            ->makeHidden('photo_url');

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    public function getBuyers()
    {
        $data = DB::table('data_collector_buyer')->orderBy('name')->get(['id', 'name', 'phone_number', 'address']);

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    public function getDestinations()
    {
        $data = DB::table('waste_destinations')->orderBy('name')->get(['id', 'name', 'location']);

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /**
     * Menyimpan transaksi sampah keluar ke waste_out_data, data_waste_out,
     * lampiran foto, dan data penjualan (jika metode penjualan).
     */
    public function store(Request $request)
    {
        $this->normalizeDecimal($request, 'total_revenue');
        $this->decodeJsonField($request, 'items');

        $request->validate([
            'id_waste_out_method'  => 'required|exists:waste_out_method,id',
            'id_waste_destination' => 'nullable|exists:waste_destinations,id',
            'id_buyer'             => 'nullable|exists:data_collector_buyer,id',
            'total_revenue'        => 'nullable|numeric|min:0',
            'notes'                => 'nullable|string|max:1000',
            'created_at'           => $this->transactionTimeRules(),
            'items'                => 'required|array|min:1',
            'items.*.id_sub_category' => 'required',
            'items.*.quantity'     => 'required|numeric|gt:0',
            'photo'                => 'nullable|image|max:5120',
        ], array_merge($this->transactionTimeMessages(), [
            'items.required' => 'Daftar item sampah tidak boleh kosong.',
            'items.min' => 'Daftar item sampah tidak boleh kosong.',
            'items.*.quantity.gt' => 'Berat setiap item harus lebih dari 0.',
        ]));

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

        [$rawNeeds, $processedNeeds] = $this->parseItems($request->items);

        $photoPath = null;

        try {
            $wasteOutId = DB::transaction(function () use ($request, $rawNeeds, $processedNeeds, &$photoPath) {
                $this->stock->ensureAvailable($rawNeeds, $processedNeeds);

                $createdAt = $this->transactionTime($request);
                $wasteOutDataId = DB::table('waste_out_data')->insertGetId([
                    'id_user'              => $request->user()->id,
                    'id_waste_out_method'  => $request->id_waste_out_method,
                    'id_waste_destination' => $request->id_waste_destination,
                    'notes'                => $request->notes,
                    'created_at'           => $createdAt,
                    'updated_at'           => now(),
                ]);

                foreach ($rawNeeds as $idSub => $qty) {
                    DB::table('data_waste_out')->insert([
                        'id_waste_out_data'     => $wasteOutDataId,
                        'is_processed_waste'    => false,
                        'id_waste_sub_category' => $idSub,
                        'id_processed_waste'    => null,
                        'measured_qty'          => $qty,
                    ]);
                }
                foreach ($processedNeeds as $idProcessed => $qty) {
                    DB::table('data_waste_out')->insert([
                        'id_waste_out_data'     => $wasteOutDataId,
                        'is_processed_waste'    => true,
                        'id_waste_sub_category' => null,
                        'id_processed_waste'    => $idProcessed,
                        'measured_qty'          => $qty,
                    ]);
                }

                if ($request->filled('id_buyer')) {
                    DB::table('waste_selling_data')->insert([
                        'id_waste_out_data' => $wasteOutDataId,
                        'total_revenue'     => $request->total_revenue ?? 0,
                        'id_buyer'          => $request->id_buyer,
                        'created_at'        => $createdAt,
                        'updated_at'        => now(),
                    ]);
                }

                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('waste_out_photos', 'public');
                    DB::table('attachment_waste_out_data')->insert([
                        'id_waste_out_data' => $wasteOutDataId,
                        'path'              => $photoPath,
                    ]);
                }

                return $wasteOutDataId;
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data transaksi. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data sampah keluar berhasil disimpan!',
            'data' => ['id' => $wasteOutId],
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $out = WasteOutData::with([
                'wasteOutMethod',
                'wasteDestination',
                'dataWasteOut.wasteSubCategory.unitMeasured',
                'dataWasteOut.processedWaste.unitMeasured',
                'sellingData.buyer',
                'attachment',
            ])
            ->where('id_user', $request->user()->id)
            ->find($id);

        if (!$out) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $selling = $out->sellingData;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $out->id,
                'method' => $out->wasteOutMethod?->name ?? '-',
                'destination' => $out->wasteDestination?->name,
                'notes' => $out->notes,
                'created_at' => $out->created_at?->toDateTimeString(),
                'date_label' => $out->created_at?->translatedFormat('l, d M Y'),
                'time_label' => $out->created_at?->format('H:i') . ' WIB',
                'buyer' => $selling?->buyer?->name,
                'total_revenue' => $selling ? (float) $selling->total_revenue : null,
                'photo_url' => $out->attachment ? asset('storage/' . $out->attachment->path) : null,
                'items' => $out->dataWasteOut->map(fn ($d) => [
                    'name' => $d->is_processed_waste
                        ? ($d->processedWaste?->name ?? 'Produk Olahan')
                        : ($d->wasteSubCategory?->name ?? 'Sampah'),
                    'is_processed' => (bool) $d->is_processed_waste,
                    'quantity' => (float) $d->measured_qty,
                    'unit' => $d->is_processed_waste
                        ? ($d->processedWaste?->unitMeasured?->symbol ?? 'kg')
                        : ($d->wasteSubCategory?->unitMeasured?->symbol ?? 'kg'),
                ])->values(),
            ],
        ]);
    }

    /**
     * Item dari mobile berformat {id_sub_category: 5 | "p_3", quantity: 2.5}.
     * Awalan "p_" menandakan hasil olahan.
     *
     * @return array{0: array<int, float>, 1: array<int, float>}
     */
    private function parseItems(array $items): array
    {
        $raw = [];
        $processed = [];

        foreach ($items as $index => $item) {
            $key = (string) $item['id_sub_category'];
            $qty = (float) $item['quantity'];

            if (str_starts_with($key, 'p_')) {
                $id = (int) substr($key, 2);
                $processed[$id] = ($processed[$id] ?? 0) + $qty;
            } elseif (ctype_digit($key)) {
                $id = (int) $key;
                $raw[$id] = ($raw[$id] ?? 0) + $qty;
            } else {
                throw ValidationException::withMessages(["items.{$index}.id_sub_category" => 'Item sampah tidak valid.']);
            }
        }

        $missingRaw = array_diff(array_keys($raw), WasteSubCategory::whereIn('id', array_keys($raw))->pluck('id')->all());
        $missingProcessed = array_diff(array_keys($processed), ProcessedWaste::whereIn('id', array_keys($processed))->pluck('id')->all());
        if ($missingRaw || $missingProcessed) {
            throw ValidationException::withMessages(['items' => 'Sebagian item sampah tidak ditemukan.']);
        }

        return [$raw, $processed];
    }
}
