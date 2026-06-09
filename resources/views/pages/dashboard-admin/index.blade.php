@extends('layouts.app')

@section('title', 'Dashboard | WasteTracking')
@section('page-title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-7">

    <!-- Welcome Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1aa88e] via-[#17b89c] to-[#0f9e87] p-7 text-white shadow-lg">
        <div class="relative z-10">
            <p class="text-sm font-medium text-white/75 mb-1">Selamat datang kembali 👋</p>
            <h2 class="text-2xl font-extrabold">
                {{ auth()->user()->adminDetail->full_name ?? 'Administrator' }}
            </h2>
            <p class="text-sm text-white/70 mt-1">
                {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }} — Sistem Monitoring Rumah Sampah Digital
            </p>
        </div>
        <!-- Decorative circles -->
        <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full bg-white/10"></div>
        <div class="absolute -right-4 -bottom-12 w-56 h-56 rounded-full bg-white/5"></div>
        <div class="absolute right-32 -bottom-6 w-24 h-24 rounded-full bg-white/10"></div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Sampah Masuk -->
        <div class="stat-card bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i data-lucide="inbox" class="w-5 h-5 text-blue-500"></i>
                </div>
                <span class="text-[10px] font-bold text-blue-400 bg-blue-50 px-2 py-1 rounded-full uppercase tracking-wide">Total</span>
            </div>
            <p class="text-2xl font-extrabold text-gray-800">{{ number_format($stats['waste_entry_count']) }}</p>
            <p class="text-xs text-gray-400 mt-1 font-medium">Sampah Masuk</p>
        </div>

        <!-- Sampah Keluar -->
        <div class="stat-card bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center">
                    <i data-lucide="send" class="w-5 h-5 text-orange-500"></i>
                </div>
                <span class="text-[10px] font-bold text-orange-400 bg-orange-50 px-2 py-1 rounded-full uppercase tracking-wide">Total</span>
            </div>
            <p class="text-2xl font-extrabold text-gray-800">{{ number_format($stats['waste_out_count']) }}</p>
            <p class="text-xs text-gray-400 mt-1 font-medium">Sampah Keluar</p>
        </div>

        <!-- Pengolahan -->
        <div class="stat-card bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center">
                    <i data-lucide="cpu" class="w-5 h-5 text-purple-500"></i>
                </div>
                <span class="text-[10px] font-bold text-purple-400 bg-purple-50 px-2 py-1 rounded-full uppercase tracking-wide">Total</span>
            </div>
            <p class="text-2xl font-extrabold text-gray-800">{{ number_format($stats['processed_waste_count']) }}</p>
            <p class="text-xs text-gray-400 mt-1 font-medium">Pengolahan</p>
        </div>

        <!-- PIC Aktif -->
        <div class="stat-card bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-teal-50 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5 text-teal-500"></i>
                </div>
                <span class="text-[10px] font-bold text-teal-400 bg-teal-50 px-2 py-1 rounded-full uppercase tracking-wide">PIC</span>
            </div>
            <p class="text-2xl font-extrabold text-gray-800">{{ number_format($stats['pic_count']) }}</p>
            <p class="text-xs text-gray-400 mt-1 font-medium">Pengguna PIC</p>
        </div>

    </div>

    <!-- Chart + Top Waste -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Chart Bulanan -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Tren Sampah Masuk</h3>
                    <p class="text-xs text-gray-400 mt-0.5">6 bulan terakhir</p>
                </div>
                <span class="text-[10px] font-bold text-[#3DBFA6] bg-teal-50 px-3 py-1.5 rounded-full uppercase tracking-wide">Bulanan</span>
            </div>
            <div class="relative h-[250px] w-full">
                <canvas id="wasteEntryChart"></canvas>
            </div>
        </div>

        <!-- Top Sampah -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="mb-5">
                <h3 class="text-sm font-bold text-gray-800">Sampah Terbanyak</h3>
                <p class="text-xs text-gray-400 mt-0.5">Berdasarkan kuantitas masuk</p>
            </div>
            <div class="space-y-4">
                @php
                    $colors = ['bg-blue-500', 'bg-teal-500', 'bg-purple-500', 'bg-orange-500', 'bg-pink-500'];
                    $maxTotal = $topWaste->max('total') ?: 1;
                @endphp
                @forelse($topWaste as $i => $item)
                    @php
                        $pct = round(($item->total / $maxTotal) * 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-semibold text-gray-700 truncate max-w-[160px]">
                                {{ $item->subCategory?->name ?? '-' }}
                            </span>
                            <span class="text-xs text-gray-400">{{ number_format($item->total, 2) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="{{ $colors[$i % 5] }} h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <i data-lucide="inbox" class="w-8 h-8 text-gray-200 mx-auto mb-2"></i>
                        <p class="text-xs text-gray-400">Belum ada data</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Entries -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Aktivitas Terbaru</h3>
                <p class="text-xs text-gray-400 mt-0.5">Sampah masuk paling baru</p>
            </div>
            <a href="{{ route('admin.waste-entry.index') }}"
               class="text-[11px] font-bold text-[#3DBFA6] hover:text-[#2aa08e] flex items-center gap-1.5">
                Lihat Semua <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/70">
                    <tr>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">PIC</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jenis Sampah</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kuantitas</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentEntries as $entry)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($entry->user?->picDetail?->full_name ?? 'U', 0, 2)) }}
                                </div>
                                <span class="text-xs font-medium text-gray-700">
                                    {{ $entry->user?->picDetail?->full_name ?? $entry->user?->email ?? '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            <div>
                                <p class="text-xs font-medium text-gray-800">{{ $entry->subCategory?->name ?? '-' }}</p>
                                <p class="text-[10px] text-gray-400">{{ $entry->subCategory?->category?->name ?? '' }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="text-xs font-semibold text-gray-700">{{ number_format($entry->measured_qty, 2) }}</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="text-[11px] text-gray-400">{{ $entry->created_at?->diffForHumans() ?? '-' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <i data-lucide="inbox" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-400">Belum ada data sampah masuk</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const monthlyData = @json($monthlyEntry);

    const labels  = monthlyData.map(d => {
        const [y, m] = d.month.split('-');
        const date = new Date(y, m - 1);
        return date.toLocaleString('id-ID', { month: 'short', year: '2-digit' });
    });
    const counts = monthlyData.map(d => parseFloat(d.total_qty) || 0);

    const ctx = document.getElementById('wasteEntryChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Total Kuantitas',
                data: counts,
                backgroundColor: 'rgba(61, 191, 166, 0.12)',
                borderColor: '#3DBFA6',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f8fafc',
                    cornerRadius: 8,
                    padding: 10,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11 } }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    border: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11 } }
                }
            }
        }
    });
</script>
@endpush