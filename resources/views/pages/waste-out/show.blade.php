@extends('layouts.app')

@section('title', 'Detail Sampah Keluar | WasteTracking')
@section('page-title', 'Detail Sampah Keluar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.waste-out.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar
        </a>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400">ID Transaksi:</span>
            <span class="text-xs font-bold text-gray-800">#{{ str_pad($wasteOut->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-800">Informasi Pengeluaran</h3>
                </div>
                <div class="p-6 grid grid-cols-2 gap-y-6">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Metode</p>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-orange-50 text-orange-600">
                            {{ $wasteOut->wasteOutMethod?->name ?? '-' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Waktu</p>
                        <p class="text-xs font-medium text-gray-800">{{ $wasteOut->created_at?->format('d F Y, H:i') }}</p>
                    </div>
                    @if($wasteOut->wasteDestination)
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tujuan</p>
                        <p class="text-xs font-medium text-gray-800">{{ $wasteOut->wasteDestination->name }}</p>
                        <p class="text-[9px] text-gray-400">{{ $wasteOut->wasteDestination->location }}</p>
                    </div>
                    @endif
                    
                    @if($wasteOut->sellingData)
                        <div class="col-span-2 grid grid-cols-2 gap-4 p-4 rounded-xl bg-orange-50/50 border border-orange-100">
                            <div>
                                <p class="text-[10px] font-bold text-orange-400 uppercase tracking-wider mb-1">Pendapatan</p>
                                <p class="text-sm font-extrabold text-orange-600">Rp {{ number_format($wasteOut->sellingData->total_revenue, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-orange-400 uppercase tracking-wider mb-1">Pembeli / Pengepul</p>
                                <p class="text-xs font-bold text-gray-800">{{ $wasteOut->sellingData->buyer?->name ?? '-' }}</p>
                                <p class="text-[9px] text-gray-400">{{ $wasteOut->sellingData->buyer?->address ?? '' }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="col-span-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Catatan</p>
                        <p class="text-xs text-gray-600 leading-relaxed">{{ $wasteOut->notes ?? 'Tidak ada catatan' }}</p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-800">Daftar Item Sampah</h3>
                    <span class="text-[10px] font-bold text-teal-600 bg-teal-50 px-2 py-1 rounded-full">{{ $wasteOut->dataWasteOut->count() }} Item</span>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jenis Sampah</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kuantitas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($wasteOut->dataWasteOut as $item)
                        <tr>
                            <td class="px-6 py-4">
                                @if($item->is_processed_waste)
                                    <p class="text-xs font-semibold text-gray-800">{{ $item->processedWaste?->name ?? '-' }}</p>
                                    <p class="text-[9px] text-purple-500 font-bold uppercase tracking-tight">Hasil Olahan</p>
                                @else
                                    <p class="text-xs font-semibold text-gray-800">{{ $item->wasteSubCategory?->name ?? '-' }}</p>
                                    <p class="text-[9px] text-gray-400 uppercase tracking-tight">{{ $item->wasteSubCategory?->category?->name ?? '' }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xs font-bold text-gray-800">{{ number_format($item->measured_qty, 2) }}</span>
                                <span class="text-[10px] text-gray-400 ml-1">
                                    {{ $item->is_processed_waste ? ($item->processedWaste?->unitMeasured?->symbol ?? 'kg') : ($item->wasteSubCategory?->unitMeasured?->symbol ?? 'kg') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50/30">
                        <tr>
                            <td class="px-6 py-4 text-xs font-bold text-gray-800">Total Kuantitas</td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-extrabold text-[#3DBFA6]">{{ number_format($wasteOut->dataWasteOut->sum('measured_qty'), 2) }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Sidebar / Attachment -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50">
                    <h3 class="text-sm font-bold text-gray-800">Bukti Foto</h3>
                </div>
                <div class="p-6">
                    @if($wasteOut->attachment)
                        <div class="rounded-xl overflow-hidden border border-gray-100 shadow-inner bg-gray-50">
                            <img src="{{ asset('storage/' . $wasteOut->attachment->path) }}" alt="Bukti Sampah Keluar" class="w-full h-auto object-cover hover:scale-105 transition-transform duration-500">
                        </div>
                        <a href="{{ asset('storage/' . $wasteOut->attachment->path) }}" target="_blank" class="mt-4 flex items-center justify-center gap-2 w-full py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-500 hover:bg-gray-50 transition-colors">
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
