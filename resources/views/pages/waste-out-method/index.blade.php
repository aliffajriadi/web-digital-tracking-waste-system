@extends('layouts.app')

@section('title', 'Metode Keluar Sampah | WasteTracking')
@section('page-title', 'Metode Keluar Sampah')

@section('content')
<div class="max-w-7xl mx-auto space-y-5"
     x-data="{ openAdd: false, openEdit: false, editItem: null }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-gray-800">Metode Keluar Sampah</h2>
            <p class="text-xs text-gray-400 mt-0.5">Kelola kategori/metode pengeluaran sampah (dijual, dibuang, dibakar, dll.).</p>
        </div>
        <button @click="openAdd = true"
            class="inline-flex items-center gap-2 bg-[#3DBFA6] hover:bg-[#32aa94] text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Metode
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <span class="text-xs font-bold text-gray-600">{{ $methods->total() }} metode</span>
            <form method="GET">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari metode..."
                        class="w-full sm:w-56 h-9 pl-9 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider w-10">#</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Foto</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama Metode</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Penggunaan</th>
                    <th class="px-6 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($methods as $i => $method)
                @php
                    $ml = strtolower($method->name);
                    $isSell    = str_contains($ml, 'jual') || str_contains($ml, 'sell');
                    $isDiscard = str_contains($ml, 'buang') || str_contains($ml, 'landfill') || str_contains($ml, 'tpa');
                    $isBurn    = str_contains($ml, 'bakar') || str_contains($ml, 'burn');
                    if ($isSell) { $icon = 'shopping-cart'; $color = 'green'; }
                    elseif ($isDiscard) { $icon = 'trash-2'; $color = 'orange'; }
                    elseif ($isBurn) { $icon = 'flame'; $color = 'red'; }
                    else { $icon = 'send'; $color = 'blue'; }
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-6 py-4 text-xs text-gray-400">{{ $methods->firstItem() + $i }}</td>
                    <td class="px-6 py-4">
                        @if($method->photo)
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-50 border border-gray-100">
                                <img src="{{ asset('storage/' . $method->photo) }}" class="w-full h-full object-cover" alt="{{ $method->name }}">
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center text-gray-200">
                                <i data-lucide="image" class="w-4 h-4"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-{{ $color }}-50 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="{{ $icon }}" class="w-4 h-4 text-{{ $color }}-500"></i>
                            </div>
                            <span class="text-xs font-semibold text-gray-800">{{ $method->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500 max-w-xs truncate">{{ $method->description ?? '-' }}</td>
                    <td class="px-6 py-4">
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-teal-50 text-teal-600">
                            {{ $method->waste_out_data_count }} kali digunakan
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            <button
                                @click="openEdit = true; editItem = {{ json_encode(['id' => $method->id, 'name' => $method->name, 'description' => $method->description]) }}"
                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 flex items-center justify-center transition-colors">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.waste-out-method.destroy', $method) }}"
                                onsubmit="return confirm('Hapus metode \'{{ $method->name }}\'?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition-colors">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-14 text-center">
                        <i data-lucide="send" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                        <p class="text-sm text-gray-400">Belum ada metode keluar sampah</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($methods->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $methods->links() }}</div>
        @endif
    </div>

    <!-- Modal Tambah -->
    <div x-show="openAdd" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.outside="openAdd = false" x-transition class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center">
                        <i data-lucide="plus-circle" class="w-5 h-5 text-teal-500"></i>
                    </div>
                    <h2 class="text-sm font-extrabold text-gray-800">Tambah Metode Keluar</h2>
                </div>
                <button @click="openAdd = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.waste-out-method.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                        Nama Metode <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="name" required
                        placeholder="cth: Dijual, Dibuang ke TPA, Dibakar..."
                        value="{{ old('name') }}"
                        class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    @error('name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="2"
                        placeholder="Penjelasan singkat tentang metode ini..."
                        class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Icon / Foto (Input Gambar)</label>
                    <input type="file" name="photo" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-600 hover:file:bg-teal-100 cursor-pointer">
                </div>

                <!-- Preview Hint -->
                <div class="bg-amber-50 border border-amber-100 rounded-xl px-4 py-3 flex items-start gap-2.5">
                    <i data-lucide="info" class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5"></i>
                    <p class="text-[11px] text-amber-700 leading-relaxed">
                        Nama yang mengandung kata <strong>jual/sell</strong> akan otomatis memunculkan field pembeli & harga.
                        Kata <strong>buang/tpa/landfill</strong> akan memunculkan field lokasi pembuangan.
                    </p>
                </div>

                <div class="flex justify-end gap-2.5 pt-1">
                    <button type="button" @click="openAdd = false"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-bold hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="px-4 py-2.5 rounded-xl bg-[#3DBFA6] text-white text-sm font-bold hover:bg-[#32aa94]">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="openEdit" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.outside="openEdit = false" x-transition class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                        <i data-lucide="pencil" class="w-5 h-5 text-blue-500"></i>
                    </div>
                    <h2 class="text-sm font-extrabold text-gray-800">Edit Metode</h2>
                </div>
                <button @click="openEdit = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <template x-if="editItem">
                <form method="POST" :action="`/admin/waste-out-method/${editItem.id}`" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Nama Metode <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="name" :value="editItem.name" required
                            class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
                        <textarea name="description" rows="2" x-text="editItem.description"
                            class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6] resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Ganti Icon / Foto</label>
                        <input type="file" name="photo" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                    <div class="flex justify-end gap-2.5 pt-1">
                        <button type="button" @click="openEdit = false"
                            class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-bold hover:bg-gray-50">Batal</button>
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl bg-blue-500 text-white text-sm font-bold hover:bg-blue-600">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
