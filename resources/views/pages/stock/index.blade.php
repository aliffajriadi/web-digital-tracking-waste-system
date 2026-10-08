@extends('layouts.app')

@section('title', 'Stok Gudang | WasteTracking')
@section('page-title', 'Stok Gudang')
@section('breadcrumb', 'Operasional')

@php use App\Services\StockService; @endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <x-page-header title="Stok Gudang" subtitle="Dihitung otomatis: sampah masuk − sampah keluar − dipakai pengolahan. Hasil olahan: diproduksi − keluar.">
        <a href="{{ route('admin.processed-waste-data.create') }}" class="btn btn-secondary"><i data-lucide="recycle" class="w-4 h-4"></i> Catat Olahan</a>
        <a href="{{ route('admin.waste-out.index', ['create' => 1]) }}" class="btn btn-primary"><i data-lucide="arrow-up-from-line" class="w-4 h-4"></i> Catat Sampah Keluar</a>
    </x-page-header>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card label="Sampah mentah di gudang" :value="StockService::format($summary['total_raw']) . ' kg'" icon="package" tone="brand" />
        <x-stat-card label="Hasil olahan siap keluar" :value="StockService::format($summary['total_processed']) . ' kg'" icon="recycle" tone="violet" />
        <x-stat-card label="Jenis tersedia" :value="$summary['available'] . ' / ' . $summary['items']" icon="layers" tone="blue" :hint="$summary['empty'] . ' jenis kosong'" />
        <x-stat-card label="Peringatan B3" :value="$b3Alerts->filter(fn ($a) => $a['sisa_hari'] <= StockService::B3_WARNING_DAYS)->count()" icon="flask-conical" :tone="$b3Alerts->isEmpty() ? 'slate' : 'red'" hint="Mendekati/lewat masa simpan" />
    </div>

    @if($summary['negative'] > 0)
        <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
            <i data-lucide="alert-octagon" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"></i>
            <div class="text-sm">
                <p class="font-semibold text-red-700">{{ $summary['negative'] }} jenis sampah memiliki stok minus.</p>
                <p class="text-red-600/80 text-xs mt-0.5">Ini berasal dari data lama sebelum validasi stok diterapkan. Periksa transaksi keluar/olahan pada jenis tersebut.
                    <a href="{{ route('admin.stock.index', ['status' => 'negative']) }}" class="font-semibold underline">Tampilkan</a></p>
            </div>
        </div>
    @endif

    @if($b3Alerts->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="flask-conical" class="w-4 h-4 text-red-500"></i>
                <h3 class="text-sm font-bold text-slate-800">Masa Simpan Limbah B3</h3>
                <span class="text-xs text-slate-400">· dihitung dari stok tertua yang belum keluar</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 text-left">Limbah</th>
                            <th class="px-5 py-3 text-left">Stok</th>
                            <th class="px-5 py-3 text-left">Masuk Tertua</th>
                            <th class="px-5 py-3 text-left">Batas Simpan</th>
                            <th class="px-5 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($b3Alerts as $alert)
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-800">{{ $alert['waste_name'] }}</p>
                                    <p class="text-[11px] text-slate-400"><span class="font-mono">{{ $alert['waste_code'] }}</span> · Bahaya {{ $alert['danger_level'] }}/5 · Simpan maks {{ $alert['retention_period_day'] }} hari</p>
                                </td>
                                <td class="px-5 py-3 text-slate-700">{{ StockService::format($alert['stock']) }} {{ $alert['unit'] }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ \Carbon\Carbon::parse($alert['created_at'])->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ \Carbon\Carbon::parse($alert['deadline'])->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    @if($alert['sisa_hari'] < 0)
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-700">Lewat {{ abs($alert['sisa_hari']) }} hari</span>
                                    @elseif($alert['sisa_hari'] <= 3)
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-orange-100 text-orange-700">Sisa {{ $alert['sisa_hari'] }} hari</span>
                                    @elseif($alert['sisa_hari'] <= StockService::B3_WARNING_DAYS)
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700">Sisa {{ $alert['sisa_hari'] }} hari</span>
                                    @else
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600">Aman · {{ $alert['sisa_hari'] }} hari</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <form method="GET" class="px-5 py-4 border-b border-slate-100 flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari jenis sampah…" class="form-input pl-10">
            </div>
            <select name="category" class="form-input md:w-48" onchange="this.form.submit()">
                <option value="">Semua kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
                <option value="processed" @selected(request('category') === 'processed')>Hasil Olahan</option>
            </select>
            <select name="status" class="form-input md:w-44" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <option value="available" @selected(request('status') === 'available')>Tersedia</option>
                <option value="empty" @selected(request('status') === 'empty')>Kosong</option>
                <option value="negative" @selected(request('status') === 'negative')>Minus</option>
                <option value="b3" @selected(request('status') === 'b3')>Limbah B3</option>
            </select>
            <button class="btn btn-secondary">Cari</button>
            @if(request()->hasAny(['search', 'category', 'status']))
                <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>

        @php $maxStock = max(1, $rows->max('stock')); @endphp
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Jenis</th>
                        <th class="px-5 py-3 text-left">Kategori</th>
                        <th class="px-5 py-3 text-left w-1/3">Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($rows as $row)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if($row['photo_url'])
                                        <img src="{{ $row['photo_url'] }}" alt="" class="w-9 h-9 rounded-lg object-cover border border-slate-100">
                                    @else
                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center {{ $row['type'] === 'processed' ? 'bg-violet-50 text-violet-500' : 'bg-brand-50 text-brand-600' }}">
                                            <i data-lucide="{{ $row['type'] === 'processed' ? 'recycle' : 'package' }}" class="w-4 h-4"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $row['name'] }}</p>
                                        @if($row['is_b3'])
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-red-50 text-red-500">B3 {{ $row['b3_code'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-xs text-slate-500">{{ $row['category'] }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-28 text-sm font-bold {{ $row['stock'] < 0 ? 'text-red-600' : ($row['stock'] > 0 ? 'text-slate-800' : 'text-slate-300') }}">
                                        {{ StockService::format($row['stock']) }} {{ $row['unit'] }}
                                    </span>
                                    <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                        <div class="h-full rounded-full {{ $row['type'] === 'processed' ? 'bg-violet-400' : 'bg-brand-400' }}" style="width: {{ $row['stock'] > 0 ? max(2, $row['stock'] / $maxStock * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state icon="warehouse" title="Tidak ada data stok" message="Coba ubah filter pencarian." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
