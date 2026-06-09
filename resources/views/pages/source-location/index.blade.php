@extends('layouts.app')

@section('title', 'Sumber Sampah | WasteTracking')
@section('page-title', 'Sumber Sampah')

@section('content')
<div class="max-w-5xl mx-auto space-y-5" x-data="{ openAdd: false, openEdit: false, editItem: null }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-gray-800">Sumber Lokasi Sampah</h2>
            <p class="text-xs text-gray-400 mt-0.5">Lokasi-lokasi asal pengumpulan sampah.</p>
        </div>
        <button @click="openAdd = true"
            class="inline-flex items-center gap-2 bg-[#3DBFA6] hover:bg-[#32aa94] text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Sumber
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
            <span class="text-xs font-bold text-gray-600">{{ $locations->total() }} lokasi</span>
            <form method="GET">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari lokasi..."
                        class="w-full sm:w-56 h-9 pl-9 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Foto</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama Lokasi</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Alamat</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Entri</th>
                    <th class="px-6 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($locations as $i => $loc)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-6 py-4 text-xs text-gray-400">{{ $locations->firstItem() + $i }}</td>
                    <td class="px-6 py-4">
                        @if($loc->photo)
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-50 border border-gray-100">
                                <img src="{{ asset('storage/' . $loc->photo) }}" class="w-full h-full object-cover" alt="{{ $loc->name }}">
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center text-gray-200">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold text-gray-800">{{ $loc->name }}</span>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500">{{ $loc->address ?? '-' }}</td>
                    <td class="px-6 py-4">
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-teal-50 text-teal-600">
                            {{ $loc->waste_entries_count ?? 0 }} entri
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            <button
                                @click="openEdit = true; editItem = {{ json_encode(['id' => $loc->id, 'name' => $loc->name, 'address' => $loc->address]) }}"
                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 flex items-center justify-center">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.source-location.destroy', $loc) }}"
                                onsubmit="return confirm('Hapus lokasi ini?')">
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
                        <i data-lucide="map-pin" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                        <p class="text-sm text-gray-400">Belum ada sumber lokasi</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($locations->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $locations->links() }}</div>
        @endif
    </div>

    <!-- Modal Tambah -->
    <div x-show="openAdd" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.outside="openAdd = false" x-transition class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-gray-800">Tambah Lokasi Sumber</h2>
                <button @click="openAdd = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.source-location.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama Lokasi</label>
                    <input type="text" name="name" required placeholder="cth: Gedung A Polibatam" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Alamat</label>
                    <textarea name="address" rows="2" placeholder="Alamat lengkap..." class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Foto Lokasi (Input Gambar)</label>
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
                <h2 class="text-sm font-extrabold text-gray-800">Edit Lokasi</h2>
                <button @click="openEdit = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <template x-if="editItem">
                <form method="POST" :action="`/admin/source-location/${editItem.id}`" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama Lokasi</label>
                        <input type="text" name="name" :value="editItem.name" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Alamat</label>
                        <textarea name="address" rows="2" x-text="editItem.address" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
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
