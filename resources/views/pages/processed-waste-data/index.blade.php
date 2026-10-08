@extends('layouts.app')

@section('title', 'Pengolahan Sampah | WasteTracking')
@section('page-title', 'Pengolahan Sampah')
@section('breadcrumb', 'Operasional')

@php use App\Services\StockService; @endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <x-page-header title="Pengolahan Sampah" subtitle="Riwayat pengolahan sampah mentah menjadi produk (kompos, pupuk cair, dll).">
        <a href="{{ route('admin.processed-waste-data.create') }}" class="btn btn-primary"><i data-lucide="plus" class="w-4 h-4"></i> Catat Pengolahan</a>
    </x-page-header>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <form method="GET" class="px-5 py-4 border-b border-slate-100 flex flex-col md:flex-row gap-3 md:items-end">
            <div class="md:w-56">
                <label class="form-label">Jenis Olahan</label>
                <select name="processed" class="form-input" onchange="this.form.submit()">
                    <option value="">Semua jenis</option>
                    @foreach($processedWastes as $pw)
                        <option value="{{ $pw->id }}" @selected(request('processed') == $pw->id)>{{ $pw->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="form-label">Dari</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input"></div>
            <div><label class="form-label">Sampai</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input"></div>
            <div class="flex gap-2">
                <button class="btn btn-secondary">Terapkan</button>
                @if(request()->hasAny(['processed', 'date_from', 'date_to']))
                    <a href="{{ route('admin.processed-waste-data.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Waktu</th>
                        <th class="px-5 py-3 text-left">Hasil Olahan</th>
                        <th class="px-5 py-3 text-left">Bahan Baku</th>
                        <th class="px-5 py-3 text-left">Dicatat Oleh</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($processedData as $item)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3.5">
                                <p class="text-sm text-slate-700">{{ $item->created_at?->translatedFormat('d M Y') }}</p>
                                <p class="text-[11px] text-slate-400">{{ $item->created_at?->format('H:i') }} WIB</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-slate-800">{{ $item->processedWaste?->name ?? '-' }}</p>
                                <p class="text-xs text-violet-600 font-bold">{{ StockService::format((float) $item->measured_qty) }} {{ $item->processedWaste?->unitMeasured?->symbol ?? 'kg' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ $item->rawMaterials->count() }} jenis · {{ StockService::format((float) $item->rawMaterials->sum('measured_qty')) }} kg
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">{{ $item->user?->picDetail?->full_name ?? $item->user?->adminDetail?->full_name ?? $item->user?->email ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.processed-waste-data.show', $item) }}" class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200">
                                    Detail <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">
                            <x-empty-state icon="recycle" title="Belum ada data pengolahan" message="Data pengolahan dari PIC maupun admin akan tampil di sini.">
                                <a href="{{ route('admin.processed-waste-data.create') }}" class="btn btn-primary"><i data-lucide="plus" class="w-4 h-4"></i> Catat Pengolahan</a>
                            </x-empty-state>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($processedData->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $processedData->links() }}</div>
        @endif
    </div>
</div>
@endsection
