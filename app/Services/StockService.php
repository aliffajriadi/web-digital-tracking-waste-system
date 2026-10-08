<?php

namespace App\Services;

use App\Models\ProcessedWaste;
use App\Models\WasteSubCategory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stok tidak disimpan di tabel tersendiri, melainkan dihitung dari transaksi:
 *  - Sampah mentah  : masuk (waste_entry) - keluar (data_waste_out) - dipakai olahan (waste_raw_materials)
 *  - Hasil olahan   : diproduksi (processed_waste_data) - keluar (data_waste_out)
 */
class StockService
{
    private const EPSILON = 0.0001;

    /** Hari sebelum batas simpan B3 yang mulai diberi peringatan. */
    public const B3_WARNING_DAYS = 10;

    /**
     * @return array<int, float> [id_waste_sub_category => stok]
     */
    public function rawStocks(?array $ids = null): array
    {
        $in = $this->sumBy('waste_entry', 'id_waste_sub_category', $ids);
        $out = $this->sumBy('data_waste_out', 'id_waste_sub_category', $ids, fn ($q) => $q->where('is_processed_waste', false));
        $used = $this->sumBy('waste_raw_materials', 'id_waste_sub_category', $ids);

        $keys = array_unique(array_merge(array_keys($in), array_keys($out), array_keys($used), $ids ?? []));
        $stocks = [];
        foreach ($keys as $id) {
            $stocks[$id] = round(($in[$id] ?? 0) - ($out[$id] ?? 0) - ($used[$id] ?? 0), 4);
        }

        return $stocks;
    }

    /**
     * @return array<int, float> [id_processed_waste => stok]
     */
    public function processedStocks(?array $ids = null): array
    {
        $in = $this->sumBy('processed_waste_data', 'id_processed_waste', $ids);
        $out = $this->sumBy('data_waste_out', 'id_processed_waste', $ids, fn ($q) => $q->where('is_processed_waste', true));

        $keys = array_unique(array_merge(array_keys($in), array_keys($out), $ids ?? []));
        $stocks = [];
        foreach ($keys as $id) {
            $stocks[$id] = round(($in[$id] ?? 0) - ($out[$id] ?? 0), 4);
        }

        return $stocks;
    }

    /**
     * Pastikan stok cukup untuk semua kebutuhan. Kebutuhan dengan id yang sama dijumlahkan.
     *
     * @param  array<int, float>  $rawNeeds        [id_waste_sub_category => qty]
     * @param  array<int, float>  $processedNeeds  [id_processed_waste => qty]
     * @param  string  $errorKey  key error validasi yang dikembalikan ke client
     *
     * @throws ValidationException
     */
    public function ensureAvailable(array $rawNeeds, array $processedNeeds = [], string $errorKey = 'items'): void
    {
        $errors = [];

        if ($rawNeeds) {
            $stocks = $this->rawStocks(array_keys($rawNeeds));
            $subs = WasteSubCategory::with('unitMeasured')->whereIn('id', array_keys($rawNeeds))->get()->keyBy('id');
            foreach ($rawNeeds as $id => $qty) {
                $available = max(0, $stocks[$id] ?? 0);
                if ($qty - $available > self::EPSILON) {
                    $sub = $subs->get($id);
                    $errors[] = sprintf(
                        'Stok %s tidak cukup (tersedia %s %s, diminta %s %s).',
                        $sub?->name ?? "#{$id}",
                        self::format($available),
                        $sub?->unitMeasured?->symbol ?? 'kg',
                        self::format($qty),
                        $sub?->unitMeasured?->symbol ?? 'kg',
                    );
                }
            }
        }

        if ($processedNeeds) {
            $stocks = $this->processedStocks(array_keys($processedNeeds));
            $items = ProcessedWaste::with('unitMeasured')->whereIn('id', array_keys($processedNeeds))->get()->keyBy('id');
            foreach ($processedNeeds as $id => $qty) {
                $available = max(0, $stocks[$id] ?? 0);
                if ($qty - $available > self::EPSILON) {
                    $item = $items->get($id);
                    $errors[] = sprintf(
                        'Stok olahan %s tidak cukup (tersedia %s %s, diminta %s %s).',
                        $item?->name ?? "#{$id}",
                        self::format($available),
                        $item?->unitMeasured?->symbol ?? 'kg',
                        self::format($qty),
                        $item?->unitMeasured?->symbol ?? 'kg',
                    );
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages([$errorKey => $errors]);
        }
    }

    /**
     * Daftar stok lengkap untuk tampilan (mobile & admin).
     */
    public function stockList(bool $onlyActive = true): Collection
    {
        $subs = WasteSubCategory::with(['category', 'unitMeasured', 'b3Detail'])
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->orderBy('id_waste_category')
            ->orderBy('name')
            ->get();
        $rawStocks = $this->rawStocks($subs->pluck('id')->all());

        $processed = ProcessedWaste::with('unitMeasured')->orderBy('name')->get();
        $processedStocks = $this->processedStocks($processed->pluck('id')->all());

        $rows = $subs->map(fn ($sub) => [
            'id' => $sub->id,
            'type' => 'raw',
            'name' => $sub->name,
            'category' => $sub->category?->name ?? 'Umum',
            'id_category' => $sub->id_waste_category,
            'stock' => (float) ($rawStocks[$sub->id] ?? 0),
            'unit' => $sub->unitMeasured?->symbol ?? 'kg',
            'is_b3' => $sub->id_waste_b3_detail !== null,
            'b3_code' => $sub->b3Detail?->waste_code,
            'photo_url' => $sub->photo_url,
        ]);

        $processedRows = $processed->map(fn ($p) => [
            'id' => 'p_' . $p->id,
            'type' => 'processed',
            'name' => $p->name,
            'category' => 'Hasil Olahan',
            'id_category' => null,
            'stock' => (float) ($processedStocks[$p->id] ?? 0),
            'unit' => $p->unitMeasured?->symbol ?? 'kg',
            'is_b3' => false,
            'b3_code' => null,
            'photo_url' => $p->photo ? asset('storage/' . $p->photo) : null,
        ]);

        return $rows->concat($processedRows)->values();
    }

    /**
     * Peringatan masa simpan limbah B3 yang masih ada di gudang.
     * Umur stok dihitung FIFO: entri tertua dianggap keluar/diolah lebih dulu.
     */
    public function b3Alerts(?int $warningDays = self::B3_WARNING_DAYS): Collection
    {
        $subs = WasteSubCategory::with(['b3Detail', 'unitMeasured'])
            ->whereNotNull('id_waste_b3_detail')
            ->get();

        if ($subs->isEmpty()) {
            return collect();
        }

        $ids = $subs->pluck('id')->all();
        $stocks = $this->rawStocks($ids);
        $consumed = [];
        foreach ($this->sumBy('data_waste_out', 'id_waste_sub_category', $ids, fn ($q) => $q->where('is_processed_waste', false)) as $id => $qty) {
            $consumed[$id] = ($consumed[$id] ?? 0) + $qty;
        }
        foreach ($this->sumBy('waste_raw_materials', 'id_waste_sub_category', $ids) as $id => $qty) {
            $consumed[$id] = ($consumed[$id] ?? 0) + $qty;
        }

        $now = Carbon::now();
        $alerts = collect();

        foreach ($subs as $sub) {
            $stock = $stocks[$sub->id] ?? 0;
            if ($stock <= self::EPSILON || !$sub->b3Detail) {
                continue;
            }

            $remainingConsumed = $consumed[$sub->id] ?? 0;
            $oldest = null;
            $entries = DB::table('waste_entry')
                ->where('id_waste_sub_category', $sub->id)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(['measured_qty', 'created_at']);

            foreach ($entries as $entry) {
                $remainingConsumed -= (float) $entry->measured_qty;
                if ($remainingConsumed < -self::EPSILON) {
                    $oldest = Carbon::parse($entry->created_at);
                    break;
                }
            }

            if (!$oldest) {
                continue;
            }

            $retention = (int) $sub->b3Detail->retention_period_day;
            $deadline = $oldest->copy()->addDays($retention);
            $daysLeft = (int) floor($now->diffInDays($deadline, false));

            if ($warningDays !== null && $daysLeft > $warningDays) {
                continue;
            }

            $alerts->push([
                'id' => $sub->id,
                'id_waste_sub_category' => $sub->id,
                'waste_code' => $sub->b3Detail->waste_code,
                'waste_name' => $sub->name,
                'description' => $sub->b3Detail->description,
                'danger_level' => (int) $sub->b3Detail->danger_level,
                'retention_period_day' => $retention,
                'created_at' => $oldest->toDateTimeString(),
                'deadline' => $deadline->toDateTimeString(),
                'sisa_hari' => $daysLeft,
                'stock' => (float) $stock,
                'unit' => $sub->unitMeasured?->symbol ?? 'kg',
                'status' => $daysLeft < 0 ? 'expired' : ($daysLeft <= 3 ? 'critical' : 'warning'),
            ]);
        }

        return $alerts->sortBy('sisa_hari')->values();
    }

    public static function format(float $qty): string
    {
        return rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',');
    }

    /**
     * @return array<int, float>
     */
    private function sumBy(string $table, string $column, ?array $ids, ?callable $scope = null): array
    {
        $query = DB::table($table)
            ->select($column, DB::raw('SUM(measured_qty) as total'))
            ->whereNotNull($column)
            ->groupBy($column);

        if ($ids !== null) {
            $query->whereIn($column, $ids);
        }
        if ($scope) {
            $scope($query);
        }

        return $query->pluck('total', $column)->map(fn ($v) => (float) $v)->all();
    }
}
