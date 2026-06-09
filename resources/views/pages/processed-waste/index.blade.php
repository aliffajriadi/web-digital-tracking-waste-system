@extends('layouts.app')

@section('title', 'Jenis Olahan | WasteTracking')
@section('page-title', 'Jenis Olahan')

@section('content')
<div class="max-w-7xl mx-auto space-y-5" x-data="{ openAdd: false, openEdit: false, editItem: null }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-gray-800">Jenis Olahan Sampah</h2>
            <p class="text-xs text-gray-400 mt-0.5">Daftar jenis olahan yang dapat diproses.</p>
        </div>
        <button @click="openAdd = true"
            class="inline-flex items-center gap-2 bg-[#3DBFA6] hover:bg-[#32aa94] text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Olahan
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <span class="text-xs font-bold text-gray-600">{{ $processedWastes->total() }} jenis olahan</span>
            <form method="GET">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari jenis olahan..."
                        class="w-full sm:w-56 h-9 pl-9 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Foto</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama Olahan</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Satuan</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Qty Default</th>
                    <th class="px-6 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($processedWastes as $i => $item)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-6 py-4 text-xs text-gray-400">{{ $processedWastes->firstItem() + $i }}</td>
                    <td class="px-6 py-4">
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
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                                <i data-lucide="recycle" class="w-4 h-4 text-purple-500"></i>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">{{ $item->name }}</p>
                                @if($item->description)
                                <p class="text-[10px] text-gray-400 truncate max-w-[200px]">{{ $item->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-600">{{ $item->unitMeasured?->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-xs text-gray-600">{{ number_format($item->default_measured_qty, 2) }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            <button
                                @click="openEdit = true; editItem = {{ json_encode(['id' => $item->id, 'name' => $item->name, 'description' => $item->description, 'id_unit_measured' => $item->id_unit_measured, 'default_measured_qty' => $item->default_measured_qty]) }}"
                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 flex items-center justify-center">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.processed-waste.destroy', $item) }}"
                                onsubmit="return confirm('Hapus jenis olahan ini?')">
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
                    <td colspan="6" class="px-6 py-14 text-center">
                        <i data-lucide="recycle" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                        <p class="text-sm text-gray-400">Belum ada jenis olahan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($processedWastes->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $processedWastes->links() }}</div>
        @endif
    </div>

    <!-- Modal Tambah -->
    <div x-show="openAdd" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.outside="openAdd = false" x-transition class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-gray-800">Tambah Jenis Olahan</h2>
                <button @click="openAdd = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.processed-waste.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama Olahan</label>
                    <input type="text" name="name" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Satuan Ukur</label>
                        <select name="id_unit_measured" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                            <option value="">-- Pilih Satuan --</option>
                            @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Qty Default</label>
                        <input type="number" name="default_measured_qty" step="0.01" min="0" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Foto Produk Olahan (Input Gambar)</label>
                    <input type="file" name="photo" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-600 hover:file:bg-teal-100 cursor-pointer">
                </div>
                <div class="flex justify-end gap-2.5 pt-1">
                    <button type="button" @click="openAdd = false" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-bold hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#3DBFA6] text-white text-sm font-bold hover:bg-[#32aa94]">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="openEdit" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.outside="openEdit = false" x-transition class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-gray-800">Edit Jenis Olahan</h2>
                <button @click="openEdit = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <template x-if="editItem">
                <form method="POST" :action="`/admin/processed-waste/${editItem.id}`" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama Olahan</label>
                        <input type="text" name="name" :value="editItem.name" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
                        <textarea name="description" rows="2" x-text="editItem.description" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Satuan Ukur</label>
                            <select name="id_unit_measured" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}" :selected="editItem.id_unit_measured == {{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Qty Default</label>
                            <input type="number" name="default_measured_qty" :value="editItem.default_measured_qty" step="0.01" min="0" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Ganti Foto</label>
                        <input type="file" name="photo" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                    <div class="flex justify-end gap-2.5 pt-1">
                        <button type="button" @click="openEdit = false" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-bold hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-500 text-white text-sm font-bold hover:bg-blue-600">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
