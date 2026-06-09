@extends('layouts.app')

@section('title', 'Sampah Masuk | WasteTracking')
@section('page-title', 'Monitoring Sampah Masuk')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-gray-800">Data Sampah Masuk</h2>
            <p class="text-xs text-gray-400 mt-0.5">Rekaman entri sampah yang dicatat oleh PIC.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[180px]">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama PIC atau jenis sampah..."
                class="w-full h-9 pl-9 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
        </div>
        <div class="flex items-center gap-2">
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="h-9 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600 focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
            <span class="text-gray-300 text-xs">—</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="h-9 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600 focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
        </div>
        <button type="submit" class="h-9 px-4 bg-gray-800 text-white text-xs font-bold rounded-xl hover:bg-gray-700">
            <i data-lucide="filter" class="w-3.5 h-3.5 inline mr-1"></i>Filter
        </button>
        @if(request()->anyFilled(['search','date_from','date_to']))
        <a href="{{ route('admin.waste-entry.index') }}" class="h-9 px-4 border border-gray-200 text-gray-500 text-xs font-bold rounded-xl hover:bg-gray-50 flex items-center">Reset</a>
        @endif
    </form>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
            <span class="text-xs font-bold text-gray-600">{{ $entries->total() }} data ditemukan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">PIC</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jenis Sampah</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kuantitas</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sumber</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Catatan</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Waktu</th>
                        <th class="px-5 py-3.5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($entries as $i => $entry)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5 text-xs text-gray-400">{{ $entries->firstItem() + $i }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($entry->user?->picDetail?->full_name ?? 'U', 0, 2)) }}
                                </div>
                                <span class="text-xs font-medium text-gray-700">
                                    {{ $entry->user?->picDetail?->full_name ?? $entry->user?->email ?? '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-xs font-semibold text-gray-800">{{ $entry->subCategory?->name ?? '-' }}</p>
                            <p class="text-[10px] text-gray-400">{{ $entry->subCategory?->category?->name ?? '' }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="text-xs font-bold text-gray-800">{{ number_format($entry->measured_qty, 2) }}</span>
                            <span class="text-[10px] text-gray-400">{{ $entry->subCategory?->unitMeasured?->symbol }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500">{{ $entry->sourceLocation?->name ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-xs text-gray-500 max-w-[150px] truncate">{{ $entry->notes ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-[11px] text-gray-400">{{ $entry->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <a href="{{ route('admin.waste-entry.show', $entry->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg text-[10px] font-bold hover:bg-[#3DBFA6] hover:text-white transition-all">
                                <i data-lucide="eye" class="w-3 h-3"></i>
                                Cek Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-14 text-center">
                            <i data-lucide="inbox" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-400">Tidak ada data sampah masuk</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($entries->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $entries->links() }}</div>
        @endif
    </div>

</div>
@endsection
