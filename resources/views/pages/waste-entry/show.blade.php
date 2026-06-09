@extends('layouts.app')

@section('title', 'Detail Sampah Masuk | WasteTracking')
@section('page-title', 'Detail Sampah Masuk')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.waste-entry.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar
        </a>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400">ID Entri:</span>
            <span class="text-xs font-bold text-gray-800">#{{ str_pad($wasteEntry->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-800">Informasi Sampah Masuk</h3>
                </div>
                <div class="p-6 grid grid-cols-2 gap-y-6">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">PIC Pencatat</p>
                        <p class="text-xs font-medium text-gray-800">{{ $wasteEntry->user?->picDetail?->full_name ?? $wasteEntry->user?->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">NIK PIC</p>
                        <p class="text-xs font-medium text-gray-800">{{ $wasteEntry->user?->picDetail?->nik ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Jenis Sampah</p>
                        <p class="text-xs font-bold text-[#3DBFA6]">{{ $wasteEntry->subCategory?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Kategori</p>
                        <p class="text-xs font-medium text-gray-800">{{ $wasteEntry->subCategory?->category?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Kuantitas</p>
                        <p class="text-xs font-bold text-gray-800">
                            {{ number_format($wasteEntry->measured_qty, 2) }} 
                            <span class="text-[10px] text-gray-400 font-normal">{{ $wasteEntry->subCategory?->unitMeasured?->symbol }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Sumber Lokasi</p>
                        <p class="text-xs font-medium text-gray-800">{{ $wasteEntry->sourceLocation?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Waktu Input</p>
                        <p class="text-xs font-medium text-gray-800">{{ $wasteEntry->created_at?->format('d F Y, H:i') }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Catatan</p>
                        <p class="text-xs text-gray-600 leading-relaxed">{{ $wasteEntry->notes ?? 'Tidak ada catatan' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Attachment -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50">
                    <h3 class="text-sm font-bold text-gray-800">Bukti Foto</h3>
                </div>
                <div class="p-6">
                    @if($wasteEntry->attachment)
                        <div class="rounded-xl overflow-hidden border border-gray-100 shadow-inner bg-gray-50">
                            <img src="{{ asset('storage/' . $wasteEntry->attachment->path) }}" alt="Bukti Sampah Masuk" class="w-full h-auto object-cover hover:scale-105 transition-transform duration-500">
                        </div>
                        <a href="{{ asset('storage/' . $wasteEntry->attachment->path) }}" target="_blank" class="mt-4 flex items-center justify-center gap-2 w-full py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-500 hover:bg-gray-50 transition-colors">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            Lihat Full Size
                        </a>
                    @else
                        <div class="py-10 flex flex-col items-center justify-center text-gray-300">
                            <i data-lucide="image-off" class="w-12 h-12 mb-2"></i>
                            <p class="text-xs font-medium">Tidak ada foto</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
