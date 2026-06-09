@extends('layouts.app')

@section('title', 'Sampah Keluar | WasteTracking')
@section('page-title', 'Monitoring Sampah Keluar')

@section('content')
    <div class="max-w-7xl mx-auto space-y-5" x-data="{ 
        openAdd: false, 
        method: '', 
        items: [{ is_processed: 0, id_waste_sub_category: '', id_processed_waste: '', measured_qty: '' }],
        addItem() {
            this.items.push({ is_processed: 0, id_waste_sub_category: '', id_processed_waste: '', measured_qty: '' });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        }
    }">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-gray-800">Data Sampah Keluar</h2>
                <p class="text-xs text-gray-400 mt-0.5">Data sampah yang telah dikeluarkan dari rumah sampah.</p>
            </div>
            <button @click="openAdd = true"
                class="inline-flex items-center gap-2 bg-[#3DBFA6] hover:bg-[#32aa94] text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah Data
            </button>
        </div>

        <!-- Filter -->
        <form method="GET"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 font-semibold">Dari:</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="h-9 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600 focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                <label class="text-xs text-gray-500 font-semibold">s/d:</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="h-9 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600 focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
            </div>
            <button type="submit"
                class="h-9 px-4 bg-gray-800 text-white text-xs font-bold rounded-xl hover:bg-gray-700">Filter</button>
            @if (request()->anyFilled(['date_from', 'date_to']))
                <a href="{{ route('admin.waste-out.index') }}"
                    class="h-9 px-4 border border-gray-200 text-gray-500 text-xs font-bold rounded-xl hover:bg-gray-50 flex items-center">Reset</a>
            @endif
        </form>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <span class="text-xs font-bold text-gray-600">{{ $wasteOuts->total() }} data</span>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Metode Keluar</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tujuan</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Catatan</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Foto</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($wasteOuts as $i => $item)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4 text-xs text-gray-400">{{ $wasteOuts->firstItem() + $i }}</td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-orange-50 text-orange-600">
                                    {{ $item->method?->name ?? ($item->wasteOutMethod?->name ?? '-') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600">
                                {{ $item->destination?->name ?? ($item->wasteDestination?->name ?? '-') }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500 max-w-xs truncate">{{ $item->notes ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($item->attachment)
                                    <a href="{{ asset('storage/' . $item->attachment->path) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-teal-50 text-[#3DBFA6] hover:bg-teal-100">
                                        <i data-lucide="image" class="w-4 h-4"></i>
                                    </a>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-[11px] text-gray-400">{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.waste-out.show', $item) }}" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 hover:bg-gray-100 inline-flex items-center justify-center">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <i data-lucide="send" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                                <p class="text-sm text-gray-400">Tidak ada data sampah keluar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($wasteOuts->hasPages())
                <div class="px-6 py-4 border-t border-gray-50">{{ $wasteOuts->links() }}</div>
            @endif
        </div>

    </div>

    <!-- Modal Tambah -->
    <div x-show="openAdd" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
        <div @click.outside="openAdd = false" x-transition class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-100 my-8">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
                <h2 class="text-sm font-extrabold text-gray-800">Tambah Sampah Keluar</h2>
                <button @click="openAdd = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.waste-out.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Metode Keluar -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Metode Keluar</label>
                        <select name="id_waste_out_method" x-model="method" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                            <option value="">Pilih Metode</option>
                            @foreach($methods as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tujuan -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Tujuan</label>
                        <select name="id_waste_destination" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                            <option value="">Pilih Tujuan (Opsional)</option>
                            @foreach($destinations as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Selling Fields (if method is Selling - Assuming ID 1 is Selling based on typical setup) -->
                <div x-show="method == '1'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-orange-50 rounded-2xl border border-orange-100">
                    <div>
                        <label class="block text-[10px] font-bold text-orange-400 uppercase tracking-wider mb-1.5">Pembeli</label>
                        <select name="id_buyer" :required="method == '1'" class="w-full h-10 px-3.5 bg-white border border-orange-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            <option value="">Pilih Pembeli</option>
                            @foreach($buyers as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-orange-400 uppercase tracking-wider mb-1.5">Total Pendapatan (Rp)</label>
                        <input type="number" name="total_revenue" :required="method == '1'" placeholder="0" class="w-full h-10 px-3.5 bg-white border border-orange-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    </div>
                </div>

                <!-- Items Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Item Sampah</label>
                        <button type="button" @click="addItem()" class="text-[10px] font-bold text-[#3DBFA6] hover:underline">+ Tambah Item</button>
                    </div>
                    
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200 space-y-3 relative">
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="absolute top-2 right-2 text-gray-300 hover:text-red-500">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Tipe</label>
                                    <select :name="`items[${index}][is_processed]`" x-model="item.is_processed" class="w-full h-9 px-2 bg-white border border-gray-200 rounded-lg text-xs">
                                        <option value="0">Mentah (Sub-Kategori)</option>
                                        <option value="1">Olahan</option>
                                    </select>
                                </div>
                                
                                <div class="md:col-span-1">
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Pilih Sampah</label>
                                    <!-- Sub Category -->
                                    <select x-show="item.is_processed == 0" :name="`items[${index}][id_waste_sub_category]`" class="w-full h-9 px-2 bg-white border border-gray-200 rounded-lg text-xs">
                                        <option value="">Pilih Sub-Kategori</option>
                                        @foreach($subCategories as $sc)
                                            <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                                        @endforeach
                                    </select>
                                    <!-- Processed Waste -->
                                    <select x-show="item.is_processed == 1" :name="`items[${index}][id_processed_waste]`" class="w-full h-9 px-2 bg-white border border-gray-200 rounded-lg text-xs">
                                        <option value="">Pilih Jenis Olahan</option>
                                        @foreach($processedWastes as $pw)
                                            <option value="{{ $pw->id }}">{{ $pw->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Kuantitas</label>
                                    <input type="number" step="0.01" :name="`items[${index}][measured_qty]`" required placeholder="0.00" class="w-full h-9 px-3 bg-white border border-gray-200 rounded-lg text-xs">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Notes & Image -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Catatan</label>
                        <textarea name="notes" rows="3" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Foto Bukti (Input Gambar)</label>
                        <div class="relative h-24 border-2 border-dashed border-gray-200 rounded-xl flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 transition-colors">
                            <input type="file" name="image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="imagePreview = true">
                            <i data-lucide="image-plus" class="w-6 h-6 text-gray-300 mb-1"></i>
                            <p class="text-[10px] text-gray-400 font-medium">Klik untuk upload gambar</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-2">
                    <button type="button" @click="openAdd = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-bold hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#3DBFA6] text-white text-sm font-bold hover:bg-[#32aa94] shadow-sm shadow-teal-200">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // Re-init lucide icons when adding items
        lucide.createIcons();
    });
</script>
@endpush
