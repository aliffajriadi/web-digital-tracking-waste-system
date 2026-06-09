@extends('layouts.app')

@section('title', 'Limbah B3 | WasteTracking')
@section('page-title', 'Limbah B3')

@section('content')
<div class="max-w-7xl mx-auto space-y-5" x-data="{ openAdd: false, openEdit: false, editItem: null }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-gray-800">Limbah B3</h2>
            <p class="text-xs text-gray-400 mt-0.5">Bahan Berbahaya dan Beracun yang terdaftar.</p>
        </div>
        <button @click="openAdd = true"
            class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah B3
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <span class="text-xs font-bold text-gray-600">{{ $b3Details->total() }} data B3</span>
            <form method="GET">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau deskripsi..."
                        class="w-full sm:w-64 h-9 pl-9 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-400">
                </div>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kode Limbah</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Masa Simpan</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Level Bahaya</th>
                    <th class="px-6 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($b3Details as $item)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-red-50 text-red-600 font-mono">{{ $item->waste_code }}</span>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-700 max-w-xs">{{ $item->description }}</td>
                    <td class="px-6 py-4 text-xs text-gray-600">{{ $item->retention_period_day }} hari</td>
                    <td class="px-6 py-4">
                        <div class="flex gap-1">
                            @for($l = 1; $l <= 5; $l++)
                            <div class="w-4 h-4 rounded {{ $l <= $item->danger_level ? 'bg-red-500' : 'bg-gray-100' }}"></div>
                            @endfor
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            <button
                                @click="openEdit = true; editItem = {{ json_encode(['id' => $item->id, 'waste_code' => $item->waste_code, 'description' => $item->description, 'retention_period_day' => $item->retention_period_day, 'danger_level' => $item->danger_level]) }}"
                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 flex items-center justify-center">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.waste-b3.destroy', $item) }}"
                                onsubmit="return confirm('Hapus data B3 {{ $item->waste_code }}?')">
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
                        <i data-lucide="flask-conical" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                        <p class="text-sm text-gray-400">Belum ada data limbah B3</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($b3Details->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $b3Details->links() }}</div>
        @endif
    </div>

    <!-- Modal Tambah -->
    <div x-show="openAdd" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.outside="openAdd = false" x-transition class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-gray-800">Tambah Limbah B3</h2>
                <button @click="openAdd = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.waste-b3.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Kode Limbah</label>
                        <input type="text" name="waste_code" required placeholder="B301" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400 font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Masa Simpan (hari)</label>
                        <input type="number" name="retention_period_day" required min="1" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea name="description" required rows="2" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Level Bahaya (1-5)</label>
                    <input type="number" name="danger_level" required min="1" max="5" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400">
                </div>
                <div class="flex justify-end gap-2.5 pt-1">
                    <button type="button" @click="openAdd = false" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-bold hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-red-500 text-white text-sm font-bold hover:bg-red-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="openEdit" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.outside="openEdit = false" x-transition class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-gray-800">Edit Limbah B3</h2>
                <button @click="openEdit = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <template x-if="editItem">
                <form method="POST" :action="`/admin/waste-b3/${editItem.id}`" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Kode Limbah</label>
                            <input type="text" name="waste_code" :value="editItem.waste_code" required class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400 font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Masa Simpan (hari)</label>
                            <input type="number" name="retention_period_day" :value="editItem.retention_period_day" required min="1" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
                        <textarea name="description" rows="2" x-text="editItem.description" required class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Level Bahaya (1-5)</label>
                        <input type="number" name="danger_level" :value="editItem.danger_level" required min="1" max="5" class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400">
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
