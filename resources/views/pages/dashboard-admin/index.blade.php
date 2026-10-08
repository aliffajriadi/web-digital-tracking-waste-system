@extends('layouts.app')

@section('title', 'Dashboard | WasteTracking')
@section('page-title', 'Dashboard')

@php use App\Services\StockService; @endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-brand-600 via-brand-500 to-brand-400 p-6 md:p-7 text-white shadow-lg">
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-5">
            <div>
                <p class="text-sm text-white/80">{{ now()->translatedFormat('l, d F Y') }}</p>
                <h2 class="text-2xl font-extrabold mt-1">Halo, {{ auth()->user()->adminDetail->full_name ?? 'Administrator' }}</h2>
                <p class="text-sm text-white/80 mt-1">Hari ini tercatat {{ StockService::format($stats['in_today']) }} kg sampah masuk dari {{ $stats['in_today_count'] }} transaksi.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.stock.index') }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-xl bg-white text-brand-700 text-sm font-bold hover:bg-brand-50">
                    <i data-lucide="warehouse" class="w-4 h-4"></i> Lihat Stok
                </a>
                <a href="{{ route('admin.report.index') }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-xl bg-white/15 text-white text-sm font-bold hover:bg-white/25">
                    <i data-lucide="file-bar-chart" class="w-4 h-4"></i> Laporan
                </a>
            </div>
        </div>
        <div class="absolute -right-10 -top-10 w-44 h-44 rounded-full bg-white/10"></div>
        <div class="absolute right-24 -bottom-16 w-56 h-56 rounded-full bg-white/5"></div>
    </div>

    @if($b3Alerts->isNotEmpty() || $negativeStock->isNotEmpty())
        <div class="grid gap-3 md:grid-cols-2">
            @if($b3Alerts->isNotEmpty())
                <a href="{{ route('admin.stock.index', ['status' => 'b3']) }}" class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 hover:bg-red-100/60">
                    <i data-lucide="flask-conical" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"></i>
                    <div class="text-sm">
                        <p class="font-semibold text-red-700">{{ $b3Alerts->count() }} limbah B3 mendekati/melewati batas simpan</p>
                        <p class="text-xs text-red-600/80 mt-0.5">{{ $b3Alerts->take(3)->pluck('waste_name')->implode(', ') }}{{ $b3Alerts->count() > 3 ? ', …' : '' }}</p>
                    </div>
                </a>
            @endif
            @if($negativeStock->isNotEmpty())
                <a href="{{ route('admin.stock.index', ['status' => 'negative']) }}" class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 hover:bg-amber-100/60">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5"></i>
                    <div class="text-sm">
                        <p class="font-semibold text-amber-700">{{ $negativeStock->count() }} jenis sampah stoknya minus</p>
                        <p class="text-xs text-amber-700/80 mt-0.5">Data lama sebelum validasi stok. Periksa transaksinya.</p>
                    </div>
                </a>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card label="Masuk hari ini" :value="StockService::format($stats['in_today']) . ' kg'" icon="arrow-down-to-line" tone="blue" :hint="'Bulan ini ' . StockService::format($stats['in_month']) . ' kg'" :href="route('admin.waste-entry.index', ['date_from' => today()->toDateString()])" />
        <x-stat-card label="Keluar hari ini" :value="StockService::format($stats['out_today']) . ' kg'" icon="arrow-up-from-line" tone="orange" :hint="number_format($stats['waste_out_count']) . ' transaksi total'" :href="route('admin.waste-out.index')" />
        <x-stat-card label="Diolah hari ini" :value="StockService::format($stats['processed_today']) . ' kg'" icon="recycle" tone="violet" :hint="number_format($stats['processed_waste_count']) . ' transaksi total'" :href="route('admin.processed-waste-data.index')" />
        <x-stat-card label="Pendapatan bulan ini" :value="'Rp ' . number_format($stats['revenue_month'], 0, ',', '.')" icon="wallet" tone="emerald" :hint="'Total Rp ' . number_format($stats['total_revenue'], 0, ',', '.')" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 md:p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Sampah Masuk vs Keluar</h3>
                    <p class="text-xs text-slate-400 mt-0.5">14 hari terakhir (kg)</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-brand-500"></span>Masuk</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-orange-400"></span>Keluar</span>
                </div>
            </div>
            <div class="relative h-[260px]"><canvas id="trendChart" aria-label="Grafik sampah masuk dan keluar"></canvas></div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Stok Terbanyak</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Siap diolah / dikeluarkan</p>
                </div>
                <a href="{{ route('admin.stock.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">Semua</a>
            </div>
            @php $maxStock = max(1, $topStock->max('stock')); @endphp
            <div class="space-y-3.5">
                @forelse($topStock as $row)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-medium text-slate-700 truncate pr-2">{{ $row['name'] }}</span>
                            <span class="font-bold text-slate-800 flex-shrink-0">{{ StockService::format($row['stock']) }} {{ $row['unit'] }}</span>
                        </div>
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $row['type'] === 'processed' ? 'bg-violet-400' : 'bg-brand-400' }}" style="width: {{ max(3, $row['stock'] / $maxStock * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="warehouse" title="Gudang kosong" message="Belum ada stok sampah yang tersimpan." />
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Sampah Masuk Terbaru</h3>
                <a href="{{ route('admin.waste-entry.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($recentEntries as $entry)
                    <a href="{{ route('admin.waste-entry.show', $entry) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50/70">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="{{ $entry->notes === 'Timbangan Otomatis (IoT)' ? 'scale' : 'arrow-down-to-line' }}" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800 truncate">{{ $entry->subCategory?->name ?? '-' }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ $entry->user?->picDetail?->full_name ?? 'Admin' }} · {{ $entry->created_at?->diffForHumans() }}</p>
                        </div>
                        <span class="text-sm font-bold text-slate-700 flex-shrink-0">{{ StockService::format((float) $entry->measured_qty) }} {{ $entry->subCategory?->unitMeasured?->symbol ?? 'kg' }}</span>
                    </a>
                @empty
                    <x-empty-state icon="inbox" title="Belum ada sampah masuk" />
                @endforelse
            </div>
        </div>

        <div class="space-y-5">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Sampah Masuk Terbanyak <span class="font-normal text-slate-400">· bulan ini</span></h3>
                <ol class="space-y-3">
                    @forelse($topWaste as $i => $item)
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-500 text-[11px] font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                            <span class="flex-1 text-sm text-slate-700 truncate">{{ $item->subCategory?->name ?? '-' }}</span>
                            <span class="text-xs font-bold text-slate-800">{{ StockService::format((float) $item->total) }} {{ $item->subCategory?->unitMeasured?->symbol ?? 'kg' }}</span>
                        </li>
                    @empty
                        <li class="text-xs text-slate-400">Belum ada data bulan ini.</li>
                    @endforelse
                </ol>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-stat-card label="PIC aktif" :value="$stats['pic_active'] . ' / ' . $stats['pic_count']" icon="users" tone="brand" :href="route('admin.users.index')" />
                <x-stat-card label="Kendala 7 hari" :value="$stats['reports_week']" icon="message-square-warning" :tone="$stats['reports_week'] ? 'red' : 'slate'" :href="route('admin.pic-report.index')" />
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const trend = @json($trend);
        new Chart(document.getElementById('trendChart'), {
            type: 'bar',
            data: {
                labels: trend.map(t => t.label),
                datasets: [
                    { label: 'Masuk', data: trend.map(t => t.in), backgroundColor: '#1fa88c', borderRadius: 6, maxBarThickness: 18 },
                    { label: 'Keluar', data: trend.map(t => t.out), backgroundColor: '#fb923c', borderRadius: 6, maxBarThickness: 18 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${formatNumber(c.raw)} kg` } },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, border: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                },
            },
        });
    });
</script>
@endpush
