@extends('layouts.app')

@section('title', 'Kategori Laporan Kendala | WasteTracking')
@section('page-title', 'Kategori Laporan')

@section('content')
<div class="max-w-7xl mx-auto space-y-5" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    editData: { id: '', name: '' },
    deleteData: { id: '', name: '' },
    openEditModal(id, name) {
        this.editData = { id, name };
        this.showEditModal = true;
    },
    openDeleteModal(id, name) {
        this.deleteData = { id, name };
        this.showDeleteModal = true;
    }
}">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-gray-800">Kategori Laporan Kendala</h2>
            <p class="text-xs text-gray-400 mt-0.5">Kelola kategori laporan (seperti Kendala Login, Bug, dsb) untuk PIC.</p>
        </div>
        <button @click="showAddModal = true"
            class="flex items-center justify-center gap-2 h-9 px-4 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold rounded-xl transition-colors">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Kategori
        </button>
    </div>

    <!-- Search -->
    <form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex gap-3">
        <div class="relative flex-1">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kategori laporan..."
                class="w-full h-9 pl-9 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
        </div>
        <button type="submit" class="h-9 px-4 bg-gray-800 text-white text-xs font-bold rounded-xl">Cari</button>
        @if(request('search'))
        <a href="{{ route('admin.category-report.index') }}" class="h-9 px-4 border border-gray-200 text-gray-500 text-xs font-bold rounded-xl hover:bg-gray-50 flex items-center">Reset</a>
        @endif
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center">
            <span class="text-xs font-bold text-gray-600">{{ $categories->total() }} kategori</span>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider w-16">#</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama Kategori</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Laporan</th>
                    <th class="px-6 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($categories as $i => $category)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-6 py-4 text-xs text-gray-400">{{ $categories->firstItem() + $i }}</td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-gray-800">{{ $category->name }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $category->reports_count > 0 ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-500' }}">
                            {{ $category->reports_count }} Laporan
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}')"
                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors">
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            </button>
                            <button @click="openDeleteModal({{ $category->id }}, '{{ addslashes($category->name) }}')"
                                class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-14 text-center">
                        <i data-lucide="folder-open" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                        <p class="text-sm text-gray-400">Belum ada kategori laporan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($categories->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $categories->links() }}</div>
        @endif
    </div>

    <!-- Modal Tambah -->
    <div x-show="showAddModal" class="fixed inset-0 z-[100] flex items-center justify-center" x-cloak>
        <div x-show="showAddModal" x-transition.opacity class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showAddModal = false"></div>
        <div x-show="showAddModal" x-transition.scale.95 class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden m-4">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-800">Tambah Kategori Baru</h3>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('admin.category-report.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Bug Aplikasi, Kendala Login..."
                        class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2.5 text-xs font-bold text-gray-500 hover:text-gray-700">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white text-xs font-bold rounded-xl hover:bg-gray-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="showEditModal" class="fixed inset-0 z-[100] flex items-center justify-center" x-cloak>
        <div x-show="showEditModal" x-transition.opacity class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showEditModal = false"></div>
        <div x-show="showEditModal" x-transition.scale.95 class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden m-4">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-800">Edit Kategori</h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="`/admin/category-report/${editData.id}`" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editData.name" required
                        class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2.5 text-xs font-bold text-gray-500 hover:text-gray-700">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white text-xs font-bold rounded-xl hover:bg-gray-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center" x-cloak>
        <div x-show="showDeleteModal" x-transition.opacity class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div x-show="showDeleteModal" x-transition.scale.95 class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden m-4">
            <div class="p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="alert-triangle" class="w-7 h-7"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Hapus Kategori?</h3>
                <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus kategori <span class="font-bold text-gray-700" x-text="deleteData.name"></span>? Data tidak dapat dikembalikan jika dihapus.</p>
                <div class="flex gap-3 justify-center">
                    <button type="button" @click="showDeleteModal = false" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200">Batal</button>
                    <form :action="`/admin/category-report/${deleteData.id}`" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-red-500 rounded-xl hover:bg-red-600">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
