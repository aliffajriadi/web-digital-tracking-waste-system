@extends('layouts.app')

@section('title', 'Data Pengolahan | WasteTracking')
@section('page-title', 'Monitoring Pengolahan')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <div>
        <h2 class="text-lg font-extrabold text-gray-800">Data Pengolahan Sampah</h2>
        <p class="text-xs text-gray-400 mt-0.5">Rekaman proses pengolahan sampah yang dilakukan PIC.</p>
    </div>

    <!-- Filter -->
    <form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-500 font-semibold">Dari:</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="h-9 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600 focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
            <label class="text-xs text-gray-500 font-semibold">s/d:</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="h-9 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600 focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
        </div>
        <button type="submit" class="h-9 px-4 bg-gray-800 text-white text-xs font-bold rounded-xl hover:bg-gray-700">Filter</button>
        @if(request()->anyFilled(['date_from','date_to']))
        <a href="{{ route('admin.processed-waste-data.index') }}" class="h-9 px-4 border border-gray-200 text-gray-500 text-xs font-bold rounded-xl hover:bg-gray-50 flex items-center">Reset</a>
        @endif
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
            <span class="text-xs font-bold text-gray-600">{{ $processedData->total() }} data</span>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">PIC</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jenis Olahan</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kuantitas</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Catatan</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($processedData as $i => $item)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-6 py-4 text-xs text-gray-400">{{ $processedData->firstItem() + $i }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr($item->user?->picDetail?->full_name ?? 'U', 0, 2)) }}
                            </div>
                            <span class="text-xs font-medium text-gray-700">
                                {{ $item->user?->picDetail?->full_name ?? $item->user?->email ?? '-' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-purple-50 text-purple-600">
                            {{ $item->processedWaste?->name ?? '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold text-gray-800">{{ number_format($item->measured_qty, 2) }}</span>
                        <span class="text-[10px] text-gray-400">{{ $item->processedWaste?->unitMeasured?->symbol }}</span>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500 max-w-[150px] truncate">{{ $item->notes ?? '-' }}</td>
                    <td class="px-6 py-4 text-[11px] text-gray-400">{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-14 text-center">
                        <i data-lucide="cpu" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                        <p class="text-sm text-gray-400">Tidak ada data pengolahan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($processedData->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $processedData->links() }}</div>
        @endif
    </div>

</div>
@endsection
