@extends('layouts.app')

@section('title', 'Detail Pengolahan | WasteTracking')
@section('page-title', 'Detail Pengolahan')
@section('breadcrumb')
    <a href="{{ route('admin.processed-waste-data.index') }}" class="hover:text-brand-600">Pengolahan</a>
@endsection

@php use App\Services\StockService; @endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    <x-page-header :title="$data->processedWaste?->name ?? 'Pengolahan'" :subtitle="$data->created_at?->translatedFormat('l, d F Y · H:i') . ' WIB'">
        <a href="{{ route('admin.processed-waste-data.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali</a>
    </x-page-header>

    <div class="grid sm:grid-cols-3 gap-4">
        <x-stat-card label="Hasil olahan" :value="StockService::format((float) $data->measured_qty) . ' ' . ($data->processedWaste?->unitMeasured?->symbol ?? 'kg')" icon="recycle" tone="violet" />
        <x-stat-card label="Total bahan baku" :value="StockService::format((float) $data->rawMaterials->sum('measured_qty')) . ' kg'" icon="package" tone="brand" :hint="$data->rawMaterials->count() . ' jenis sampah'" />
        <x-stat-card label="Dicatat oleh" :value="$data->user?->picDetail?->full_name ?? $data->user?->adminDetail?->full_name ?? '-'" icon="user-round" tone="slate" />
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100"><h3 class="text-sm font-bold text-slate-800">Bahan Baku yang Dipakai</h3></div>
        <table class="w-full text-sm">
            <tbody class="divide-y divide-slate-50">
                @forelse($data->rawMaterials as $m)
                    <tr>
                        <td class="px-5 py-3">
                            <p class="font-medium text-slate-800">{{ $m->wasteSubCategory?->name ?? '-' }}</p>
                            <p class="text-[11px] text-slate-400">{{ $m->wasteSubCategory?->category?->name }}</p>
                        </td>
                        <td class="px-5 py-3 text-right font-bold text-slate-700">{{ StockService::format((float) $m->measured_qty) }} {{ $m->wasteSubCategory?->unitMeasured?->symbol ?? 'kg' }}</td>
                    </tr>
                @empty
                    <tr><td><x-empty-state icon="package" title="Tidak ada bahan baku tercatat" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <h3 class="text-sm font-bold text-slate-800 mb-2">Catatan</h3>
        <p class="text-sm text-slate-600 whitespace-pre-line">{{ $data->notes ?: 'Tidak ada catatan.' }}</p>
    </div>
</div>
@endsection
