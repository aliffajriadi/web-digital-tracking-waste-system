@extends('layouts.app')

@section('title', 'Kelola PIC | WasteTracking')
@section('page-title', 'Kelola PIC')
@section('breadcrumb', 'Akun')

@php
    // Buka kembali modal yang gagal divalidasi beserta isiannya
    $failedForm = $errors->any() ? old('_form') : null;
    $editOld = $failedForm && str_starts_with($failedForm, 'edit-')
        ? ['id' => (int) substr($failedForm, 5), 'full_name' => old('full_name'), 'nik' => old('nik'), 'email' => old('email'), 'phone' => old('phone')]
        : null;
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-5"
     x-data="{ modal: @js($failedForm === 'add' ? 'add' : ($editOld ? 'edit' : null)), edit: @js($editOld), showPass: false }">

    <x-page-header title="Pengguna PIC" subtitle="Akun petugas lapangan untuk aplikasi mobile WasteTrack. Login aplikasi memakai NIK.">
        <button @click="modal = 'add'" class="btn btn-primary">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Tambah PIC
        </button>
    </x-page-header>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <form method="GET" class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIK, atau email…" class="form-input pl-10">
            </div>
            <select name="status" class="form-input sm:w-44" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
            </select>
            <button class="btn btn-secondary">Cari</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Pengguna</th>
                        <th class="px-5 py-3 text-left">NIK</th>
                        <th class="px-5 py-3 text-left">Kontak</th>
                        <th class="px-5 py-3 text-left">Input Masuk</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $user)
                        @php $name = $user->picDetail?->full_name ?? $user->email; @endphp
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if($user->photo)
                                        <img src="{{ asset('storage/' . $user->photo) }}" alt="" class="w-9 h-9 rounded-xl object-cover">
                                    @else
                                        <div class="w-9 h-9 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold">
                                            {{ collect(explode(' ', $name))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ $name }}</p>
                                        <p class="text-[11px] text-slate-400">Terdaftar {{ $user->created_at?->translatedFormat('d M Y') ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-600">{{ $user->picDetail?->nik ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <p class="text-xs text-slate-700">{{ $user->email }}</p>
                                <p class="text-[11px] text-slate-400">{{ $user->picDetail?->phone ?: 'Tanpa nomor HP' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">{{ number_format($user->waste_entries_count) }} transaksi</td>
                            <td class="px-5 py-3.5">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>Aktif</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500"><span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}"
                                          @if($user->is_active)
                                              data-confirm="{{ $name }} tidak akan bisa login ke aplikasi dan sesi yang sedang berjalan akan diputus."
                                              data-confirm-title="Nonaktifkan akun?" data-confirm-ok="Nonaktifkan"
                                          @endif>
                                        @csrf @method('PATCH')
                                        <button type="submit" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center {{ $user->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}">
                                            <i data-lucide="{{ $user->is_active ? 'user-x' : 'user-check' }}" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    <button type="button" title="Edit"
                                            @click="edit = @js(['id' => $user->id, 'full_name' => $user->picDetail?->full_name, 'nik' => $user->picDetail?->nik, 'email' => $user->email, 'phone' => $user->picDetail?->phone]); modal = 'edit'"
                                            class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          data-confirm="Hapus akun {{ $name }}? Akun yang sudah memiliki transaksi tidak dapat dihapus, nonaktifkan saja." data-confirm-title="Hapus akun?">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Hapus" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <x-empty-state icon="users" title="Belum ada pengguna PIC" message="Tambahkan akun PIC agar petugas dapat mencatat sampah dari aplikasi mobile.">
                                <button @click="modal = 'add'" class="btn btn-primary"><i data-lucide="user-plus" class="w-4 h-4"></i> Tambah PIC</button>
                            </x-empty-state>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- Modal Tambah --}}
    <x-modal name="add" title="Tambah Pengguna PIC" subtitle="PIC login ke aplikasi menggunakan NIK dan kata sandi ini.">
        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_form" value="add">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="add_name">Nama Lengkap</label>
                    <input id="add_name" type="text" name="full_name" required maxlength="100" value="{{ $failedForm === 'add' ? old('full_name') : '' }}" class="form-input">
                    @if($failedForm === 'add') @error('full_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror @endif
                </div>
                <div>
                    <label class="form-label" for="add_nik">NIK</label>
                    <input id="add_nik" type="text" name="nik" required maxlength="20" inputmode="numeric" value="{{ $failedForm === 'add' ? old('nik') : '' }}" class="form-input font-mono">
                    @if($failedForm === 'add') @error('nik')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror @endif
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="add_email">Email</label>
                    <input id="add_email" type="email" name="email" required value="{{ $failedForm === 'add' ? old('email') : '' }}" class="form-input">
                    @if($failedForm === 'add') @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror @endif
                </div>
                <div>
                    <label class="form-label" for="add_phone">No. HP <span class="normal-case font-normal text-slate-400">(opsional)</span></label>
                    <input id="add_phone" type="tel" name="phone" maxlength="25" value="{{ $failedForm === 'add' ? old('phone') : '' }}" class="form-input">
                </div>
            </div>
            <div>
                <label class="form-label" for="add_password">Kata Sandi Awal</label>
                <div class="relative">
                    <input id="add_password" :type="showPass ? 'text' : 'password'" name="password" required minlength="8" class="form-input pr-11" placeholder="Minimal 8 karakter">
                    <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" aria-label="Tampilkan kata sandi">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
                @if($failedForm === 'add') @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror @endif
            </div>
            <div class="flex justify-end gap-2.5 pt-2">
                <button type="button" @click="modal = null" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Akun</button>
            </div>
        </form>
    </x-modal>

    {{-- Modal Edit --}}
    <x-modal name="edit" title="Edit Pengguna PIC">
        <template x-if="edit">
            <form method="POST" :action="`{{ url('admin/users') }}/${edit.id}`" class="p-6 space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="_form" :value="`edit-${edit.id}`">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="full_name" x-model="edit.full_name" required maxlength="100" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">NIK</label>
                        <input type="text" name="nik" x-model="edit.nik" required maxlength="20" class="form-input font-mono">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" x-model="edit.email" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">No. HP</label>
                        <input type="tel" name="phone" x-model="edit.phone" maxlength="25" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label">Reset Kata Sandi <span class="normal-case font-normal text-slate-400">(kosongkan jika tidak diganti)</span></label>
                    <input :type="showPass ? 'text' : 'password'" name="password" minlength="8" class="form-input" placeholder="Minimal 8 karakter">
                    <p class="text-[11px] text-slate-400 mt-1">Jika diisi, PIC akan otomatis keluar dari aplikasi dan perlu login ulang.</p>
                </div>
                @if($editOld)
                    <div class="rounded-xl bg-red-50 border border-red-100 px-3.5 py-2.5 text-xs text-red-600">
                        @foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach
                    </div>
                @endif
                <div class="flex justify-end gap-2.5 pt-2">
                    <button type="button" @click="modal = null" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </template>
    </x-modal>
</div>
@endsection
