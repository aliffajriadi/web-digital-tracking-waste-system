@extends('layouts.app')

@section('title', 'Sampah Keluar | WasteTracking')
@section('page-title', 'Sampah Keluar')
@section('breadcrumb', 'Operasional')

@php
    use App\Services\StockService;

    $itemOptions = $subCategories->map(fn ($s) => [
        'key' => '0:' . $s->id, 'is_processed' => 0, 'id' => $s->id, 'name' => $s->name,
        'unit' => $s->unitMeasured?->symbol ?? 'kg', 'stock' => (float) ($rawStocks[$s->id] ?? 0),
    ])->concat($processedWastes->map(fn ($p) => [
        'key' => '1:' . $p->id, 'is_processed' => 1, 'id' => $p->id, 'name' => $p->name . ' (olahan)',
        'unit' => $p->unitMeasured?->symbol ?? 'kg', 'stock' => (float) ($processedStocks[$p->id] ?? 0),
    ]))->values();

    $methodFlags = $methods->mapWithKeys(fn ($m) => [$m->id => (bool) $m->is_selling]);

    $oldItems = collect(old('items', []))->map(fn ($i) => [
        'key' => ($i['is_processed'] ?? 0) . ':' . (($i['is_processed'] ?? 0) ? ($i['id_processed_waste'] ?? '') : ($i['id_waste_sub_category'] ?? '')),
        'qty' => $i['measured_qty'] ?? '',
    ])->values();
    $openCreate = ($errors->any() && old('_form') === 'waste-out') || request('create');
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-5"
     x-data="wasteOutPage(@js($itemOptions), @js($methodFlags), @js($oldItems), @js(old('id_waste_out_method', '')), @js((bool) $openCreate))">

    <x-page-header title="Sampah Keluar" subtitle="Sampah atau hasil olahan yang dikirim ke TPA, dijual, atau diserahkan ke pihak lain.">
        <button @click="modal = 'create'" class="btn btn-primary"><i data-lucide="plus" class="w-4 h-4"></i> Catat Sampah Keluar</button>
    </x-page-header>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <form method="GET" class="px-5 py-4 border-b border-slate-100 flex flex-col md:flex-row gap-3 md:items-end">
            <div class="md:w-52">
                <label class="form-label">Metode</label>
                <select name="method" class="form-input" onchange="this.form.submit()">
                    <option value="">Semua metode</option>
                    @foreach($methods as $m)
                        <option value="{{ $m->id }}" @selected(request('method') == $m->id)>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="form-label">Dari</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input"></div>
            <div><label class="form-label">Sampai</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input"></div>
            <div class="flex gap-2">
                <button class="btn btn-secondary">Terapkan</button>
                @if(request()->hasAny(['method', 'date_from', 'date_to']))
                    <a href="{{ route('admin.waste-out.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Waktu</th>
                        <th class="px-5 py-3 text-left">Metode & Tujuan</th>
                        <th class="px-5 py-3 text-left">Item</th>
                        <th class="px-5 py-3 text-left">Pendapatan</th>
                        <th class="px-5 py-3 text-left">Dicatat Oleh</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($wasteOuts as $out)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3.5">
                                <p class="text-sm text-slate-700">{{ $out->created_at?->translatedFormat('d M Y') }}</p>
                                <p class="text-[11px] text-slate-400">{{ $out->created_at?->format('H:i') }} WIB</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $out->wasteOutMethod?->is_selling ? 'bg-emerald-50 text-emerald-600' : 'bg-orange-50 text-orange-600' }}">{{ $out->wasteOutMethod?->name ?? '-' }}</span>
                                <p class="text-[11px] text-slate-400 mt-1">{{ $out->wasteDestination?->name ?? 'Tanpa tujuan' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">
                                {{ $out->dataWasteOut->count() }} item · {{ StockService::format((float) $out->dataWasteOut->sum('measured_qty')) }} kg
                            </td>
                            <td class="px-5 py-3.5 text-xs font-semibold text-slate-700">
                                {{ $out->sellingData ? 'Rp ' . number_format($out->sellingData->total_revenue, 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">{{ $out->user?->picDetail?->full_name ?? $out->user?->adminDetail?->full_name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.waste-out.show', $out) }}" class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200">
                                    Detail <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <x-empty-state icon="arrow-up-from-line" title="Belum ada sampah keluar" message="Transaksi sampah keluar dari PIC maupun admin akan tampil di sini." />
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($wasteOuts->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $wasteOuts->links() }}</div>
        @endif
    </div>

    <x-modal name="create" title="Catat Sampah Keluar" subtitle="Stok gudang akan berkurang sesuai jumlah item." max-width="max-w-2xl">
        <form method="POST" action="{{ route('admin.waste-out.store') }}" enctype="multipart/form-data" class="p-6 space-y-5" @submit="validate($event)">
            @csrf
            <input type="hidden" name="_form" value="waste-out">

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Metode Keluar</label>
                    <select name="id_waste_out_method" x-model="method" required class="form-input">
                        <option value="">Pilih metode…</option>
                        @foreach($methods as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}{{ $m->is_selling ? ' (penjualan)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Tujuan <span class="normal-case font-normal text-slate-400">(opsional)</span></label>
                    <select name="id_waste_destination" class="form-input">
                        <option value="">Tanpa tujuan</option>
                        @foreach($destinations as $d)
                            <option value="{{ $d->id }}" @selected(old('id_waste_destination') == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div x-show="isSelling" x-transition class="grid md:grid-cols-2 gap-4 p-4 bg-emerald-50/60 rounded-2xl border border-emerald-100">
                <div>
                    <label class="form-label">Pembeli</label>
                    <select name="id_buyer" :required="isSelling" class="form-input bg-white">
                        <option value="">Pilih pembeli…</option>
                        @foreach($buyers as $b)
                            <option value="{{ $b->id }}" @selected(old('id_buyer') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Total Pendapatan (Rp)</label>
                    <input type="number" name="total_revenue" min="0" step="1" :required="isSelling" value="{{ old('total_revenue') }}" placeholder="0" class="form-input bg-white">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="form-label mb-0">Item Sampah</label>
                    <button type="button" @click="addItem()" class="text-xs font-bold text-brand-600 hover:underline">+ Tambah item</button>
                </div>
                <div class="space-y-2.5">
                    <template x-for="(item, index) in items" :key="item.uid">
                        <div class="rounded-xl border p-3" :class="exceeds(item) ? 'border-red-200 bg-red-50/40' : 'border-slate-100 bg-slate-50/50'">
                            <div class="flex flex-col sm:flex-row gap-2.5">
                                <select x-model="item.key" required class="form-input bg-white flex-1">
                                    <option value="">Pilih sampah / olahan…</option>
                                    <template x-for="opt in options" :key="opt.key">
                                        <option :value="opt.key" :selected="opt.key === item.key" :disabled="opt.stock <= 0" x-text="`${opt.name} — stok ${formatNumber(opt.stock)} ${opt.unit}`"></option>
                                    </template>
                                </select>
                                <div class="relative sm:w-36">
                                    <input type="number" step="0.01" min="0.01" x-model="item.qty" :name="`items[${index}][measured_qty]`" required placeholder="0" class="form-input bg-white pr-11">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400" x-text="opt(item)?.unit ?? 'kg'"></span>
                                </div>
                                <button type="button" @click="removeItem(index)" :disabled="items.length === 1" class="h-[42px] sm:w-[42px] rounded-xl bg-white border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center disabled:opacity-40" aria-label="Hapus item">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <input type="hidden" :name="`items[${index}][is_processed]`" :value="opt(item)?.is_processed ?? 0">
                            <input type="hidden" :name="`items[${index}][id_waste_sub_category]`" :value="opt(item) && !opt(item).is_processed ? opt(item).id : ''">
                            <input type="hidden" :name="`items[${index}][id_processed_waste]`" :value="opt(item) && opt(item).is_processed ? opt(item).id : ''">
                            <p x-show="item.key" class="text-xs mt-1.5" :class="exceeds(item) ? 'text-red-600 font-semibold' : 'text-slate-500'"
                               x-text="exceeds(item) ? `Melebihi stok! Tersedia ${formatNumber(available(item))} ${opt(item)?.unit}` : `Tersedia ${formatNumber(available(item))} ${opt(item)?.unit}`"></p>
                        </div>
                    </template>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Waktu Keluar</label>
                    <input type="datetime-local" name="created_at" value="{{ old('created_at', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Foto Bukti <span class="normal-case font-normal text-slate-400">(opsional)</span></label>
                    <input type="file" name="image" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:h-[42px] file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" rows="2" maxlength="1000" class="form-input">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2.5 pt-1">
                <button type="button" @click="modal = null" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </x-modal>
</div>
@endsection

@push('scripts')
<script>
    function wasteOutPage(options, methodFlags, oldItems, oldMethod, openCreate) {
        let seq = 0;
        const blank = () => ({ uid: ++seq, key: '', qty: '' });
        return {
            modal: openCreate ? 'create' : null,
            options,
            method: String(oldMethod || ''),
            items: oldItems.length ? oldItems.map(i => ({ uid: ++seq, ...i })) : [blank()],
            get isSelling() { return !!methodFlags[this.method]; },
            addItem() { this.items.push(blank()); this.$nextTick(() => lucide.createIcons()); },
            removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1); },
            opt(item) { return this.options.find(o => o.key === item.key); },
            available(item) {
                const o = this.opt(item); if (!o) return 0;
                const used = this.items.filter(r => r !== item && r.key === item.key).reduce((s, r) => s + (parseFloat(r.qty) || 0), 0);
                return Math.max(0, o.stock - used);
            },
            exceeds(item) { return item.key && (parseFloat(item.qty) || 0) > this.available(item) + 0.0001; },
            validate(e) {
                if (this.items.some(i => this.exceeds(i))) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    alert('Ada item yang melebihi stok tersedia. Periksa baris yang ditandai merah.');
                }
            },
            init() { this.$watch('modal', () => this.$nextTick(() => lucide.createIcons())); },
        };
    }
</script>
@endpush
