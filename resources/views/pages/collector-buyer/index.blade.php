@extends('layouts.app')

@section('title', 'Pengepul & Pembeli | WasteTracking')
@section('page-title', 'Pengepul & Pembeli')

@section('content')
<div class="max-w-7xl mx-auto space-y-5" x-data="{ openAdd: false, openEdit: false, editItem: null }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-gray-800">Pengepul & Pembeli</h2>
            <p class="text-xs text-gray-400 mt-0.5">Data pihak ketiga pengepul dan pembeli sampah.</p>
        </div>
        <button @click="openAdd = true"
            class="inline-flex items-center gap-2 bg-[#3DBFA6] hover:bg-[#32aa94] text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Data
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
            <span class="text-xs font-bold text-gray-600">{{ $collectors->total() }} mitra terdaftar</span>
            <form method="GET">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."
                        class="w-full sm:w-64 h-9 pl-9 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Telepon</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Website</th>
                    <th class="px-6 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($collectors as $item)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-400 to-orange-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($item->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">{{ $item->name }}</p>
                                <p class="text-[10px] text-gray-400 truncate max-w-[180px]">{{ $item->address }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-600">{{ $item->email }}</td>
                    <td class="px-6 py-4 text-xs text-gray-600">{{ $item->phone_number }}</td>
                    <td class="px-6 py-4">
                        @if($item->website)
                            <a href="{{ $item->website }}" target="_blank" class="text-xs text-blue-500 hover:underline truncate block max-w-[120px]">
                                {{ parse_url($item->website, PHP_URL_HOST) }}
                            </a>
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            <button
                                @click="openEdit = true; editItem = {{ json_encode(['id' => $item->id, 'name' => $item->name, 'phone_number' => $item->phone_number, 'address' => $item->address, 'email' => $item->email, 'website' => $item->website, 'notes' => $item->notes]) }}"
                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 flex items-center justify-center">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.collector-buyer.destroy', $item) }}"
                                onsubmit="return confirm('Hapus data {{ $item->name }}?')">
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
                    <td colspan="5" class="px-6 py-14 text-center">
                        <i data-lucide="building-2" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                        <p class="text-sm text-gray-400">Belum ada data pengepul/pembeli</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($collectors->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $collectors->links() }}</div>
        @endif
    </div>

    <!-- Modal Tambah -->
    <div x-show="openAdd" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.outside="openAdd = false" x-transition class="w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-gray-800">Tambah Pengepul/Pembeli</h2>
                <button @click="openAdd = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.collector-buyer.store') }}" class="p-6 grid grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama Perusahaan/Individu</label>
                    <input type="text" name="name" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Email</label>
                    <input type="email" name="email" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nomor Telepon</label>
                    <input type="text" name="phone_number" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Website</label>
                    <input type="url" name="website" placeholder="https://..." class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Alamat</label>
                    <textarea name="address" required rows="2" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
                </div>
                <div class="col-span-2 flex justify-end gap-2.5 pt-1">
                    <button type="button" @click="openAdd = false" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-bold hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#3DBFA6] text-white text-sm font-bold hover:bg-[#32aa94]">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="openEdit" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.outside="openEdit = false" x-transition class="w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-gray-800">Edit Data</h2>
                <button @click="openEdit = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <template x-if="editItem">
                <form method="POST" :action="`/admin/collector-buyer/${editItem.id}`" class="p-6 grid grid-cols-2 gap-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama</label>
                        <input type="text" name="name" :value="editItem.name" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Email</label>
                        <input type="email" name="email" :value="editItem.email" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Telepon</label>
                        <input type="text" name="phone_number" :value="editItem.phone_number" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Website</label>
                        <input type="url" name="website" :value="editItem.website" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Alamat</label>
                        <textarea name="address" rows="2" x-text="editItem.address" required class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Catatan</label>
                        <textarea name="notes" rows="2" x-text="editItem.notes" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
                    </div>
                    <div class="col-span-2 flex justify-end gap-2.5 pt-1">
                        <button type="button" @click="openEdit = false" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-bold hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-500 text-white text-sm font-bold hover:bg-blue-600">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
