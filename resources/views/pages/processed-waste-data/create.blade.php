@extends('layouts.app')

@section('title', 'Tambah Data Pengolahan | WasteTracking')
@section('page-title', 'Tambah Pengolahan Sampah')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-extrabold text-gray-800">Tambah Data Pengolahan</h2>
            <p class="text-xs text-gray-400 mt-0.5">Catat proses pengolahan dan bahan baku yang digunakan.</p>
        </div>
        <a href="{{ route('admin.processed-waste-data.index') }}" class="h-9 px-4 bg-white border border-gray-200 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-50 flex items-center">
            Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="bg-red-50 text-red-500 p-4 rounded-xl text-sm mb-4">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.processed-waste-data.store') }}" method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- PIC -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">PIC (Penanggung Jawab)</label>
                <select name="id_user" required class="w-full h-10 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    <option value="">Pilih PIC</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('id_user') == $user->id ? 'selected' : '' }}>
                            {{ $user->picDetail->full_name ?? $user->email }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Jenis Olahan -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">Jenis Olahan (Hasil)</label>
                <select name="id_processed_waste" required class="w-full h-10 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    <option value="">Pilih Hasil Olahan</option>
                    @foreach($processedWastes as $pw)
                        <option value="{{ $pw->id }}" {{ old('id_processed_waste') == $pw->id ? 'selected' : '' }}>
                            {{ $pw->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Kuantitas Hasil -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">Kuantitas Hasil Olahan</label>
                <input type="number" step="0.01" name="measured_qty" value="{{ old('measured_qty') }}" required placeholder="Contoh: 10" class="w-full h-10 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">Catatan (Opsional)</label>
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Catatan proses pengolahan..." class="w-full h-10 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
            </div>
        </div>

        <hr class="border-gray-100">

        <!-- Bahan Baku -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Bahan Baku (Raw Materials)</h3>
                    <p class="text-xs text-gray-400">Pilih sampah yang digunakan dan akan dikurangi dari stok.</p>
                </div>
                <button type="button" id="add-material-btn" class="h-8 px-3 bg-teal-50 text-[#3DBFA6] text-xs font-bold rounded-lg hover:bg-teal-100 flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Bahan Baku
                </button>
            </div>

            <div id="materials-container" class="space-y-3">
                <!-- Item row 1 -->
                <div class="material-row flex gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Sub Kategori Sampah</label>
                        <select name="raw_materials[0][id_waste_sub_category]" required class="w-full h-10 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                            <option value="">Pilih Sampah</option>
                            @foreach($subCategories as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-1/3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Kuantitas Digunakan</label>
                        <input type="number" step="0.01" name="raw_materials[0][measured_qty]" required placeholder="Qty" class="w-full h-10 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                    <button type="button" class="remove-material-btn h-10 px-3 bg-red-50 text-red-500 rounded-xl hover:bg-red-100 flex-shrink-0" disabled>
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="pt-4 flex justify-end">
            <button type="submit" class="h-10 px-6 bg-[#3DBFA6] text-white text-sm font-bold rounded-xl hover:bg-[#2aa08e] shadow-lg shadow-[#3DBFA6]/20 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan Pengolahan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('materials-container');
        const addBtn = document.getElementById('add-material-btn');
        let materialCount = 1;

        const subCategories = @json($subCategories);

        function updateRemoveButtons() {
            const rows = container.querySelectorAll('.material-row');
            rows.forEach((row, index) => {
                const btn = row.querySelector('.remove-material-btn');
                if (rows.length === 1) {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
        }

        addBtn.addEventListener('click', () => {
            let options = '<option value="">Pilih Sampah</option>';
            subCategories.forEach(sub => {
                options += `<option value="${sub.id}">${sub.name}</option>`;
            });

            const html = `
                <div class="material-row flex gap-3 items-end">
                    <div class="flex-1">
                        <select name="raw_materials[${materialCount}][id_waste_sub_category]" required class="w-full h-10 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                            ${options}
                        </select>
                    </div>
                    <div class="w-1/3">
                        <input type="number" step="0.01" name="raw_materials[${materialCount}][measured_qty]" required placeholder="Qty" class="w-full h-10 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                    </div>
                    <button type="button" class="remove-material-btn h-10 px-3 bg-red-50 text-red-500 rounded-xl hover:bg-red-100 flex-shrink-0">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            `;

            const div = document.createElement('div');
            div.innerHTML = html.trim();
            const newRow = div.firstChild;
            container.appendChild(newRow);

            newRow.querySelector('.remove-material-btn').addEventListener('click', function() {
                newRow.remove();
                updateRemoveButtons();
            });

            materialCount++;
            lucide.createIcons();
            updateRemoveButtons();
        });

        // Initialize remove buttons
        container.querySelectorAll('.remove-material-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.material-row').remove();
                updateRemoveButtons();
            });
        });

        updateRemoveButtons();
    });
</script>
@endpush
