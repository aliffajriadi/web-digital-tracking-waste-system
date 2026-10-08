@extends('layouts.app')

@section('title', 'Catat Pengolahan | WasteTracking')
@section('page-title', 'Catat Pengolahan')
@section('breadcrumb')
    <a href="{{ route('admin.processed-waste-data.index') }}" class="hover:text-brand-600">Pengolahan</a>
@endsection

@php
    $subOptions = $subCategories->map(fn ($s) => [
        'id' => $s->id,
        'name' => $s->name,
        'category' => $s->category?->name,
        'unit' => $s->unitMeasured?->symbol ?? 'kg',
        'stock' => (float) ($rawStocks[$s->id] ?? 0),
    ])->values();
    $oldMaterials = collect(old('raw_materials', [['id_waste_sub_category' => '', 'measured_qty' => '']]))->values();
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-5"
     x-data="processedForm(@js($subOptions), @js($oldMaterials))">

    <x-page-header title="Catat Pengolahan Sampah" subtitle="Bahan baku yang dipakai otomatis mengurangi stok gudang.">
        <a href="{{ route('admin.processed-waste-data.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali</a>
    </x-page-header>

    <form action="{{ route('admin.processed-waste-data.store') }}" method="POST" class="space-y-5" @submit="validate($event)">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 md:p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4">1. Hasil Olahan</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="id_processed_waste">Jenis Olahan</label>
                    <select id="id_processed_waste" name="id_processed_waste" required class="form-input">
                        <option value="">Pilih hasil olahan…</option>
                        @foreach($processedWastes as $pw)
                            <option value="{{ $pw->id }}" @selected(old('id_processed_waste') == $pw->id)>{{ $pw->name }} ({{ $pw->unitMeasured?->symbol ?? 'kg' }})</option>
                        @endforeach
                    </select>
                    @error('id_processed_waste')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label" for="measured_qty">Jumlah Hasil</label>
                    <input id="measured_qty" type="number" step="0.01" min="0.01" name="measured_qty" value="{{ old('measured_qty') }}" required placeholder="Contoh: 10" class="form-input">
                    @error('measured_qty')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label" for="created_at">Waktu Pengolahan</label>
                    <input id="created_at" type="datetime-local" name="created_at" value="{{ old('created_at', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" class="form-input">
                    @error('created_at')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label" for="id_user">Dicatat atas nama <span class="normal-case font-normal text-slate-400">(opsional)</span></label>
                    <select id="id_user" name="id_user" class="form-input">
                        <option value="">Saya (admin)</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('id_user') == $user->id)>{{ $user->picDetail->full_name ?? $user->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label" for="notes">Catatan <span class="normal-case font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="notes" name="notes" rows="2" maxlength="1000" class="form-input" placeholder="Misal: kompos batch minggu ke-2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 md:p-6">
            <div class="flex items-center justify-between mb-4 gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">2. Bahan Baku</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Sampah mentah yang dipakai untuk pengolahan ini.</p>
                </div>
                <button type="button" @click="add()" class="btn btn-secondary h-9 text-xs"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Bahan</button>
            </div>

            @error('raw_materials')
                <div class="mb-4 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-xs text-red-600">
                    @foreach($errors->get('raw_materials') as $msgs)
                        @foreach((array) $msgs as $m)<p>{{ $m }}</p>@endforeach
                    @endforeach
                </div>
            @enderror

            <div class="space-y-3">
                <template x-for="(row, i) in rows" :key="row.key">
                    <div class="rounded-xl border p-3 sm:p-4" :class="exceeds(row) ? 'border-red-200 bg-red-50/40' : 'border-slate-100 bg-slate-50/50'">
                        <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
                            <div class="flex-1">
                                <label class="form-label">Jenis Sampah</label>
                                <select :name="`raw_materials[${i}][id_waste_sub_category]`" x-model="row.id" required class="form-input bg-white">
                                    <option value="">Pilih sampah…</option>
                                    <template x-for="opt in options" :key="opt.id">
                                        <option :value="opt.id" :selected="String(opt.id) === String(row.id)" :disabled="opt.stock <= 0"
                                                x-text="`${opt.name} — stok ${formatNumber(opt.stock)} ${opt.unit}`"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:w-44">
                                <label class="form-label">Jumlah Dipakai</label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0.01" :name="`raw_materials[${i}][measured_qty]`" x-model="row.qty" required class="form-input bg-white pr-12" placeholder="0">
                                    <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400" x-text="unitOf(row)"></span>
                                </div>
                            </div>
                            <button type="button" @click="remove(i)" :disabled="rows.length === 1"
                                    class="h-[42px] w-full sm:w-[42px] rounded-xl bg-white border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center disabled:opacity-40" aria-label="Hapus bahan">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <p class="text-xs mt-2" x-show="row.id" :class="exceeds(row) ? 'text-red-600 font-semibold' : 'text-slate-500'"
                           x-text="exceeds(row) ? `Melebihi stok! Tersedia ${formatNumber(available(row))} ${unitOf(row)}` : `Tersedia ${formatNumber(available(row))} ${unitOf(row)}`"></p>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
            <a href="{{ route('admin.processed-waste-data.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Simpan Pengolahan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function processedForm(options, initial) {
        let seq = 0;
        return {
            options,
            rows: initial.map(r => ({ key: ++seq, id: r.id_waste_sub_category ?? '', qty: r.measured_qty ?? '' })),
            add() { this.rows.push({ key: ++seq, id: '', qty: '' }); this.$nextTick(() => lucide.createIcons()); },
            remove(i) { if (this.rows.length > 1) this.rows.splice(i, 1); },
            opt(row) { return this.options.find(o => String(o.id) === String(row.id)); },
            unitOf(row) { return this.opt(row)?.unit ?? 'kg'; },
            // Stok tersedia dikurangi pemakaian jenis yang sama di baris lain
            available(row) {
                const o = this.opt(row); if (!o) return 0;
                const usedElsewhere = this.rows.filter(r => r !== row && String(r.id) === String(row.id))
                    .reduce((sum, r) => sum + (parseFloat(r.qty) || 0), 0);
                return Math.max(0, o.stock - usedElsewhere);
            },
            exceeds(row) { return row.id && (parseFloat(row.qty) || 0) > this.available(row) + 0.0001; },
            validate(e) {
                if (this.rows.some(r => this.exceeds(r))) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    alert('Ada bahan baku yang melebihi stok tersedia. Periksa baris yang ditandai merah.');
                }
            },
            init() { this.$nextTick(() => lucide.createIcons()); },
        };
    }
</script>
@endpush
