@extends('layouts.app')

@section('title', 'Detail Laporan | WasteTracking')
@section('page-title', 'Detail Laporan')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.report.index') }}"
            class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <h2 class="text-lg font-extrabold text-gray-800">Detail Laporan</h2>
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
                        Oleh: <strong>{{ $report->user?->picDetail?->full_name ?? $report->user?->email ?? '-' }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                {!! nl2br(e($report->content ?? 'Tidak ada isi laporan.')) !!}
            </div>
        </div>
    </div>

</div>
@endsection
