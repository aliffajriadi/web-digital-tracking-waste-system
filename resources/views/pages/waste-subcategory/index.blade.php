@extends('layouts.app')

@section('title', 'Sub-Kategori Sampah | WasteTracking')
@section('page-title', 'Sub-Kategori Sampah')

@section('content')
<div class="max-w-7xl mx-auto space-y-5" x-data="{ openAdd: false, openEdit: false, editItem: null }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-gray-800">Sub-Kategori Sampah</h2>
            <p class="text-xs text-gray-400 mt-0.5">Detail jenis sampah berdasarkan kategorinya.</p>
        </div>
        <button @click="openAdd = true"
            class="inline-flex items-center gap-2 bg-[#3DBFA6] hover:bg-[#32aa94] text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Sub-Kategori
        </button>
    </div>

    <!-- Filter + Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <form method="GET" class="px-6 py-4 border-b border-gray-50 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[160px]">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama..."
                    class="w-full h-9 pl-9 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
            </div>
            <select name="category"
                class="h-9 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600 focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="h-9 px-4 bg-gray-800 text-white text-xs font-bold rounded-xl">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Foto</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Satuan</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Qty Default</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">B3</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($subCategories as $i => $item)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-4 text-xs text-gray-400">{{ $subCategories->firstItem() + $i }}</td>
                        <td class="px-5 py-4">
                            @if($item->photo)
                                <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-50 border border-gray-100">
                                    <img src="{{ asset('storage/' . $item->photo) }}" class="w-full h-full object-cover" alt="{{ $item->name }}">
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center text-gray-200">
                                    <i data-lucide="image" class="w-4 h-4"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-xs font-semibold text-gray-800">{{ $item->name }}</p>
                            @if($item->description)
                            <p class="text-[10px] text-gray-400 truncate max-w-[180px]">{{ $item->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-[11px] px-2.5 py-1 rounded-full bg-teal-50 text-teal-600 font-semibold">
                                {{ $item->category?->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-xs text-gray-600">{{ $item->unitMeasured?->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-xs text-gray-600">{{ number_format($item->default_measured_qty, 2) }}</td>
                        <td class="px-5 py-4">
                            @if($item->b3Detail)
                                <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-red-50 text-red-500">
                                    {{ $item->b3Detail->waste_code }}
                                </span>
                            @else
                                <span class="text-[10px] text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($item->is_active)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-full bg-green-50 text-green-600">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-full bg-gray-100 text-gray-400">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                <button
                                    @click="openEdit = true; editItem = {{ json_encode(['id' => $item->id, 'name' => $item->name, 'description' => $item->description, 'id_waste_category' => $item->id_waste_category, 'id_waste_b3_detail' => $item->id_waste_b3_detail, 'id_unit_measured' => $item->id_unit_measured, 'default_measured_qty' => $item->default_measured_qty, 'is_active' => $item->is_active]) }}"
                                    class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 flex items-center justify-center">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.waste-subcategory.destroy', $item) }}"
                                    onsubmit="return confirm('Hapus sub-kategori ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-14 text-center">
                            <i data-lucide="tag" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-400">Belum ada sub-kategori</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subCategories->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $subCategories->links() }}</div>
        @endif
    </div>

    <!-- Modal Tambah -->
    <div x-show="openAdd" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.outside="openAdd = false" x-transition class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-sm font-extrabold text-gray-800">Tambah Sub-Kategori</h2>
                <button @click="openAdd = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.waste-subcategory.store') }}" enctype="multipart/form-data" class="p-6 grid grid-cols-2 gap-4">
                @csrf
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama</label>
                    <input type="text" name="name" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Kategori</label>
                    <select name="id_waste_category" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Satuan Ukur</label>
                    <select name="id_unit_measured" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                        <option value="">-- Pilih Satuan --</option>
                        @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Qty Default</label>
                    <input type="number" name="default_measured_qty" step="0.01" min="0" required
                        class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Limbah B3 (Opsional)</label>
                    <select name="id_waste_b3_detail" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                        <option value="">-- Bukan B3 --</option>
                        @foreach($b3Details as $b3)
                        <option value="{{ $b3->id }}">{{ $b3->waste_code }} - {{ Str::limit($b3->description, 30) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="is_active" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Foto Sub-Kategori (Input Gambar)</label>
                    <input type="file" name="photo" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-600 hover:file:bg-teal-100 cursor-pointer">
                </div>
                <div class="col-span-2 flex justify-end gap-2.5 pt-2">
                    <button type="button" @click="openAdd = false" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-bold hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#3DBFA6] text-white text-sm font-bold hover:bg-[#32aa94]">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="openEdit" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.outside="openEdit = false" x-transition class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-sm font-extrabold text-gray-800">Edit Sub-Kategori</h2>
                <button @click="openEdit = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <template x-if="editItem">
                <form method="POST" :action="`/admin/waste-subcategory/${editItem.id}`" enctype="multipart/form-data" class="p-6 grid grid-cols-2 gap-4">
                    @csrf @method('PUT')
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama</label>
                        <input type="text" name="name" :value="editItem.name" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Kategori</label>
                        <select name="id_waste_category" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" :selected="editItem.id_waste_category == {{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
                        <textarea name="description" rows="2" x-text="editItem.description" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Satuan Ukur</label>
                        <select name="id_unit_measured" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                            @foreach($units as $unit)
                            <option value="{{ $unit->id }}" :selected="editItem.id_unit_measured == {{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Qty Default</label>
                        <input type="number" name="default_measured_qty" step="0.01" min="0" :value="editItem.default_measured_qty" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Limbah B3</label>
                        <select name="id_waste_b3_detail" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                            <option value="">-- Bukan B3 --</option>
                            @foreach($b3Details as $b3)
                            <option value="{{ $b3->id }}" :selected="editItem.id_waste_b3_detail == {{ $b3->id }}">{{ $b3->waste_code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                        <select name="is_active" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                            <option value="1" :selected="editItem.is_active == 1">Aktif</option>
                            <option value="0" :selected="editItem.is_active == 0">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Ganti Foto</label>
                        <input type="file" name="photo" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                    <div class="col-span-2 flex justify-end gap-2.5 pt-2">
                        <button type="button" @click="openEdit = false" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-bold hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-500 text-white text-sm font-bold hover:bg-blue-600">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
