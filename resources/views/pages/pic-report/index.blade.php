@extends('layouts.app')

@section('title', 'Laporan Kendala PIC | WasteTracking')
@section('page-title', 'Laporan Kendala')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-gray-800">Daftar Laporan Kendala PIC</h2>
            <p class="text-xs text-gray-400 mt-0.5">Catatan dan laporan kendala yang dibuat oleh PIC melalui mobile app.</p>
        </div>
    </div>

    <!-- Search -->
    <form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex gap-3">
        <div class="relative flex-1">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul laporan..."
                class="w-full h-9 pl-9 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
        </div>
        <button type="submit" class="h-9 px-4 bg-gray-800 text-white text-xs font-bold rounded-xl">Cari</button>
        @if(request('search'))
        <a href="{{ route('admin.pic-report.index') }}" class="h-9 px-4 border border-gray-200 text-gray-500 text-xs font-bold rounded-xl hover:bg-gray-50 flex items-center">Reset</a>
        @endif
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
            <span class="text-xs font-bold text-gray-600">{{ $reports->total() }} laporan</span>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Judul</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">PIC</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Isi Laporan</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Waktu</th>
                    <th class="px-6 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($reports as $i => $report)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-6 py-4 text-xs text-gray-400">{{ $reports->firstItem() + $i }}</td>
                    <td class="px-6 py-4">
                        <p class="text-xs font-semibold text-gray-800">{{ $report->title ?? 'Tanpa Judul' }}</p>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-600">
                        {{ $report->user?->picDetail?->full_name ?? $report->user?->email ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-600">
                            {{ $report->categoryReport?->name ?? '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500 max-w-[200px] truncate">
                        {{ Str::limit(strip_tags($report->content), 60) }}
                    </td>
                    <td class="px-6 py-4 text-[11px] text-gray-400">
                        {{ $report->created_at ? $report->created_at->format('d M Y, H:i') : '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-end">
                            <a href="{{ route('admin.pic-report.show', $report) }}"
                                class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 hover:bg-gray-100 flex items-center justify-center">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-14 text-center">
                        <i data-lucide="file-text" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                        <p class="text-sm text-gray-400">Belum ada laporan kendala</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($reports->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $reports->links() }}</div>
        @endif
    </div>

</div>
@endsection
