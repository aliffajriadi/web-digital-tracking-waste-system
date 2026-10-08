<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessedWasteData;
use App\Models\Report;
use App\Models\WasteEntry;
use App\Models\WasteOutData;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportLogController extends Controller
{
    private const LIMIT_PER_TYPE = 300;

    /**
     * Riwayat gabungan milik PIC yang sedang login.
     * type: 1=Masuk, 2=Keluar, 3=Olahan, 4=Kendala (kosong = semua)
     */
    public function history(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'type' => 'nullable|in:1,2,3,4',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $userId = $request->user()->id;
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type');
        $from = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : null;
        $to = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : null;

        $dateScope = function ($query) use ($from, $to) {
            if ($from) {
                $query->where('created_at', '>=', $from);
            }
            if ($to) {
                $query->where('created_at', '<=', $to);
            }
        };

        $allLogs = collect();

        /* 1. INPUT MASUK */
        if (empty($type) || $type == 1) {
            $masuk = WasteEntry::where('id_user', $userId)
                ->with(['subCategory.unitMeasured'])
                ->when($search !== '', fn ($q) => $q->whereHas('subCategory', fn ($s) => $s->where('name', 'like', "%{$search}%")))
                ->tap($dateScope)
                ->orderByDesc('created_at')
                ->limit(self::LIMIT_PER_TYPE)
                ->get()
                ->map(fn ($item) => $this->row(
                    $item->id,
                    'input_masuk',
                    'Masuk: ' . ($item->subCategory->name ?? 'Sampah'),
                    $this->qty($item->measured_qty, $item->subCategory?->unitMeasured?->symbol),
                    $item->created_at,
                ));

            $allLogs = $allLogs->merge($masuk);
        }

        /* 2. INPUT KELUAR (satu baris per transaksi) */
        if (empty($type) || $type == 2) {
            $keluar = WasteOutData::where('id_user', $userId)
                ->with(['wasteOutMethod', 'dataWasteOut.wasteSubCategory.unitMeasured', 'dataWasteOut.processedWaste.unitMeasured'])
                ->when($search !== '', function ($q) use ($search) {
                    // Dikelompokkan agar filter id_user tidak ikut terlewati oleh OR
                    $q->where(function ($w) use ($search) {
                        $w->whereHas('dataWasteOut.wasteSubCategory', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('dataWasteOut.processedWaste', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('wasteOutMethod', fn ($s) => $s->where('name', 'like', "%{$search}%"));
                    });
                })
                ->tap($dateScope)
                ->orderByDesc('created_at')
                ->limit(self::LIMIT_PER_TYPE)
                ->get()
                ->map(function ($item) {
                    $details = $item->dataWasteOut;
                    $names = $details->map(fn ($d) => $d->is_processed_waste
                        ? ($d->processedWaste->name ?? 'Produk Olahan')
                        : ($d->wasteSubCategory->name ?? 'Sampah'))->unique()->values();
                    $units = $details->map(fn ($d) => $d->is_processed_waste
                        ? ($d->processedWaste?->unitMeasured?->symbol ?? 'kg')
                        : ($d->wasteSubCategory?->unitMeasured?->symbol ?? 'kg'))->unique();

                    $title = 'Keluar: ' . $names->take(2)->implode(', ') . ($names->count() > 2 ? ' +' . ($names->count() - 2) : '');
                    $amount = $units->count() === 1
                        ? $this->qty($details->sum('measured_qty'), $units->first())
                        : $details->count() . ' item';

                    return $this->row($item->id, 'input_keluar', $title, $amount, $item->created_at, $item->wasteOutMethod?->name);
                });

            $allLogs = $allLogs->merge($keluar);
        }

        /* 3. HASIL OLAHAN */
        if (empty($type) || $type == 3) {
            $olahan = ProcessedWasteData::where('id_user', $userId)
                ->with(['processedWaste.unitMeasured'])
                ->when($search !== '', fn ($q) => $q->whereHas('processedWaste', fn ($s) => $s->where('name', 'like', "%{$search}%")))
                ->tap($dateScope)
                ->orderByDesc('created_at')
                ->limit(self::LIMIT_PER_TYPE)
                ->get()
                ->map(fn ($item) => $this->row(
                    $item->id,
                    'olahan',
                    'Olahan: ' . ($item->processedWaste->name ?? 'Produk Jadi'),
                    $this->qty($item->measured_qty, $item->processedWaste?->unitMeasured?->symbol),
                    $item->created_at,
                ));

            $allLogs = $allLogs->merge($olahan);
        }

        /* 4. LAPORAN KENDALA */
        if (empty($type) || $type == 4) {
            $kendala = Report::where('id_user', $userId)
                ->with(['categoryReport'])
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($w) use ($search) {
                        $w->where('title', 'like', "%{$search}%")
                          ->orWhere('content', 'like', "%{$search}%");
                    });
                })
                ->tap($dateScope)
                ->orderByDesc('id')
                ->limit(self::LIMIT_PER_TYPE)
                ->get()
                ->map(fn ($item) => $this->row(
                    $item->id,
                    'kendala',
                    'Kendala: ' . ($item->categoryReport->name ?? 'Umum'),
                    $item->title ?? 'Laporan',
                    $item->created_at ?? Carbon::now(),
                ));

            $allLogs = $allLogs->merge($kendala);
        }

        $sortedLogs = $allLogs->sortByDesc('timestamp')->values();
        $groupedData = $sortedLogs->groupBy('date_group')->toArray();

        $categories = [
            ['id' => '', 'name' => 'Semua'],
            ['id' => '1', 'name' => 'Masuk'],
            ['id' => '2', 'name' => 'Keluar'],
            ['id' => '3', 'name' => 'Olahan'],
            ['id' => '4', 'name' => 'Kendala'],
        ];

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'total' => $sortedLogs->count(),
            'data' => empty($groupedData) ? new \stdClass() : (object) $groupedData,
        ]);
    }

    private function row(int $id, string $type, string $title, string $amount, ?Carbon $createdAt, ?string $subtitle = null): array
    {
        return [
            'id' => $id,
            'type_log' => $type,
            'title' => $title,
            'subtitle' => $subtitle,
            'time' => $createdAt ? $createdAt->format('H:i') . ' WIB' : '-',
            'amount' => $amount,
            'timestamp' => $createdAt ? $createdAt->timestamp : 0,
            'date_group' => $createdAt ? $createdAt->translatedFormat('l, d M Y') : 'Tanpa Tanggal',
        ];
    }

    private function qty($qty, ?string $unit): string
    {
        return StockService::format((float) $qty) . ' ' . ($unit ?: 'kg');
    }
}
