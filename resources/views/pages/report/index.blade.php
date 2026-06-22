@extends('layouts.app')

@section('title', 'Laporan Lengkap | WasteTracking')
@section('page-title', 'Laporan Pengelolaan Sampah')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-gray-800">Laporan Pengelolaan Sampah</h2>
            <p class="text-xs text-gray-400 mt-0.5">
                Periode: <strong>{{ $dateFrom->format('d M Y') }}</strong> s/d <strong>{{ $dateTo->format('d M Y') }}</strong>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.report.export.excel', request()->query()) }}"
               class="flex items-center gap-2 h-9 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors">
                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i> Export Excel
            </a>
            <a href="{{ route('admin.report.export.pdf', request()->query()) }}" target="_blank"
               class="flex items-center gap-2 h-9 px-4 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition-colors">
                <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- FILTER --}}
    <form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}"
                class="h-9 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
        </div>
        <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to', $dateTo->format('Y-m-d')) }}"
                class="h-9 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
        </div>
        <button type="submit" class="h-9 px-5 bg-gray-800 text-white text-xs font-bold rounded-xl hover:bg-gray-700 transition-colors">
            <i data-lucide="filter" class="w-3.5 h-3.5 inline mr-1"></i>Terapkan Filter
        </button>
        <a href="{{ route('admin.report.index') }}" class="h-9 px-4 border border-gray-200 text-gray-500 text-xs font-medium rounded-xl hover:bg-gray-50 flex items-center transition-colors">Reset</a>
    </form>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3">
        @php $cards = [
            ['label'=>'Total Masuk (Kg)', 'value'=>number_format($stats['total_waste_in'],2,',','.'), 'icon'=>'arrow-down-to-line', 'color'=>'emerald'],
            ['label'=>'Total Keluar (Kg)', 'value'=>number_format($stats['total_waste_out'],2,',','.'), 'icon'=>'arrow-up-from-line', 'color'=>'blue'],
            ['label'=>'Total Diolah', 'value'=>number_format($stats['total_processed'],2,',','.'), 'icon'=>'recycle', 'color'=>'violet'],
            ['label'=>'Pendapatan (Rp)', 'value'=>number_format($stats['total_revenue'],0,',','.'), 'icon'=>'wallet', 'color'=>'green'],
            ['label'=>'Transaksi Masuk', 'value'=>$stats['total_entries'], 'icon'=>'package', 'color'=>'amber'],
            ['label'=>'Transaksi Keluar', 'value'=>$stats['total_out_records'], 'icon'=>'truck', 'color'=>'rose'],
            ['label'=>'PIC Aktif', 'value'=>$stats['unique_pics'], 'icon'=>'users', 'color'=>'sky'],
        ]; @endphp
        @foreach($cards as $card)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 stat-card">
            <div class="w-8 h-8 rounded-xl bg-{{ $card['color'] }}-50 flex items-center justify-center mb-2">
                <i data-lucide="{{ $card['icon'] }}" class="w-4 h-4 text-{{ $card['color'] }}-500"></i>
            </div>
            <p class="text-lg font-extrabold text-gray-800 leading-tight">{{ $card['value'] }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">{{ $card['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- CHARTS ROW --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Line Chart: Daily Trend --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-700 mb-4">Tren Sampah Masuk Harian</h3>
            <canvas id="dailyTrendChart" height="90"></canvas>
        </div>
        {{-- Doughnut: By Category --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-700 mb-4">Komposisi per Kategori</h3>
            <canvas id="categoryChart" height="180"></canvas>
        </div>
    </div>

    {{-- Bar Chart: By SubCategory --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-bold text-gray-700 mb-4">Volume per Sub-Kategori Sampah</h3>
        <canvas id="subCategoryChart" height="60"></canvas>
    </div>

    {{-- TABS --}}
    <div x-data="{ tab: 'masuk' }" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex border-b border-gray-100 px-4 pt-3 gap-1">
            @foreach([['masuk','Sampah Masuk','arrow-down-to-line'],['keluar','Sampah Keluar','arrow-up-from-line'],['olah','Sampah Diolah','recycle']] as [$t,$label,$icon])
            <button @click="tab='{{ $t }}'"
                :class="tab==='{{ $t }}' ? 'border-b-2 border-emerald-500 text-emerald-600 font-bold' : 'text-gray-400 hover:text-gray-600'"
                class="flex items-center gap-1.5 px-4 py-2.5 text-xs transition-colors">
                <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i>{{ $label }}
            </button>
            @endforeach
        </div>

        {{-- TAB: SAMPAH MASUK --}}
        <div x-show="tab==='masuk'" class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tanggal & Waktu</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">PIC</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sub Kategori</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jumlah</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Lokasi</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Foto</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($wasteEntries as $i => $entry)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-gray-400">{{ $i+1 }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($entry->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $entry->user?->picDetail?->full_name ?? $entry->user?->email ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[10px] font-bold">{{ $entry->subCategory?->category?->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $entry->subCategory?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600">
                            {{ number_format(floatval($entry->measured_qty),2,',','.') }}
                            <span class="text-gray-400 font-normal">{{ $entry->subCategory?->unitMeasured?->symbol ?? 'Kg' }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $entry->sourceLocation?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($entry->attachment?->path)
                                <a href="{{ asset('storage/'.$entry->attachment->path) }}" target="_blank" class="text-blue-500 hover:underline">
                                    <i data-lucide="image" class="w-4 h-4 inline"></i>
                                </a>
                            @else
                                <span class="text-gray-300 text-[10px]">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-400 max-w-[120px] truncate">{{ $entry->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-10 text-center text-gray-300 text-xs">Tidak ada data sampah masuk</td></tr>
                    @endforelse
                </tbody>
                @if($wasteEntries->count())
                <tfoot class="bg-emerald-50">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-xs font-bold text-gray-600">TOTAL</td>
                        <td class="px-4 py-3 text-right text-xs font-extrabold text-emerald-700">
                            {{ number_format($wasteEntries->sum('measured_qty'),2,',','.') }} Kg
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- TAB: SAMPAH KELUAR --}}
        <div x-show="tab==='keluar'" class="overflow-x-auto" x-cloak>
            <table class="w-full text-xs">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">PIC</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Metode</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tujuan</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Item Sampah</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jumlah</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($wasteOutRecords as $i => $out)
                        @foreach($out->dataWasteOut as $detail)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-4 py-3 text-gray-400">{{ $i+1 }}</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($out->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $out->user?->picDetail?->full_name ?? $out->user?->email ?? '-' }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 text-[10px] font-bold">{{ $out->wasteOutMethod?->name ?? '-' }}</span></td>
                            <td class="px-4 py-3 text-gray-600">{{ $out->wasteDestination?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $detail->wasteSubCategory?->name ?? ($detail->processedWaste?->name ?? '-') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-blue-600">
                                {{ number_format(floatval($detail->measured_qty),2,',','.') }}
                                <span class="text-gray-400 font-normal">{{ $detail->wasteSubCategory?->unitMeasured?->symbol ?? 'Kg' }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 max-w-[120px] truncate">{{ $out->notes ?? '-' }}</td>
                        </tr>
                        @endforeach
                    @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-300 text-xs">Tidak ada data sampah keluar</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TAB: SAMPAH DIOLAH --}}
        <div x-show="tab==='olah'" class="overflow-x-auto" x-cloak>
            <table class="w-full text-xs">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">PIC</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Produk Olahan</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jumlah</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Satuan</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($processedWasteRecords as $i => $proc)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-gray-400">{{ $i+1 }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($proc->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $proc->user?->picDetail?->full_name ?? $proc->user?->email ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full bg-violet-50 text-violet-600 text-[10px] font-bold">{{ $proc->processedWaste?->name ?? '-' }}</span></td>
                        <td class="px-4 py-3 text-right font-bold text-violet-600">{{ number_format(floatval($proc->measured_qty),2,',','.') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $proc->processedWaste?->unitMeasured?->symbol ?? 'Kg' }}</td>
                        <td class="px-4 py-3 text-gray-400 max-w-[140px] truncate">{{ $proc->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-300 text-xs">Tidak ada data sampah diolah</td></tr>
                    @endforelse
                </tbody>
                @if($processedWasteRecords->count())
                <tfoot class="bg-violet-50">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-xs font-bold text-gray-600">TOTAL</td>
                        <td class="px-4 py-3 text-right text-xs font-extrabold text-violet-700">{{ number_format($processedWasteRecords->sum('measured_qty'),2,',','.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const colors = ['#10b981','#3b82f6','#8b5cf6','#f59e0b','#ef4444','#06b6d4','#ec4899','#84cc16','#f97316','#6366f1'];

// Daily Trend Chart
const dailyLabels = @json($dailyTrend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m')));
const dailyData   = @json($dailyTrend->pluck('total'));
new Chart(document.getElementById('dailyTrendChart'), {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Volume (Kg)',
            data: dailyData,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.08)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#10b981',
            pointRadius: dailyLabels.length > 30 ? 2 : 4,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } }
});

// Category Doughnut
const catLabels = @json($wasteByCategory->map(fn($w) => $w->subCategory?->name ?? 'Lainnya'));
const catData   = @json($wasteByCategory->pluck('total'));
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: catLabels,
        datasets: [{ data: catData, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 8 } } }, cutout: '65%' }
});

// Sub-Category Bar
new Chart(document.getElementById('subCategoryChart'), {
    type: 'bar',
    data: {
        labels: catLabels,
        datasets: [{
            label: 'Total (Kg)',
            data: catData,
            backgroundColor: colors,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } }
    }
});
</script>
@endpush
