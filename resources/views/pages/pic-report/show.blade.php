@extends('layouts.app')

@section('title', 'Detail Laporan Kendala | WasteTracking')
@section('page-title', 'Detail Laporan Kendala')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.pic-report.index') }}"
            class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <h2 class="text-lg font-extrabold text-gray-800">Detail Laporan Kendala</h2>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex flex-wrap items-start gap-4 justify-between">
            <div>
                <h3 class="text-base font-extrabold text-gray-800">{{ $report->title ?? 'Tanpa Judul' }}</h3>
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-600">
                        {{ $report->categoryReport?->name ?? '-' }}
                    </span>
                    <span class="text-xs text-gray-400">
                        Dilaporkan oleh: <strong>{{ $report->user?->picDetail?->full_name ?? $report->user?->email ?? '-' }}</strong>
                    </span>
                    <span class="text-gray-300">•</span>
                    <span class="text-xs text-gray-400 flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        {{ $report->created_at ? $report->created_at->format('d M Y, H:i') . ' WIB' : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($report->attachment)
            <div class="mb-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Lampiran Foto</p>
                <div class="rounded-xl overflow-hidden border border-gray-100 shadow-sm max-w-sm">
                    <img src="{{ asset('storage/' . $report->attachment->path) }}" alt="Lampiran Laporan" class="w-full h-auto object-cover">
                </div>
            </div>
            @endif

            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Isi Laporan / Keterangan</p>
                <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                    {!! nl2br(e($report->content ?? 'Tidak ada isi laporan.')) !!}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
