@extends('layouts.app')

@section('title', 'Profil Saya | WasteTracking')
@section('page-title', 'Profil Saya')

@section('content')
<div class="max-w-5xl mx-auto space-y-7">

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="h-28 bg-gradient-to-r from-[#1aa88e] to-[#17b89c]"></div>
        <div class="px-7 pb-7">
            <div class="flex flex-col md:flex-row md:items-end gap-5 -mt-10 md:-mt-18">
                
                <div class="relative group w-24 h-24 md:w-28 md:h-28 rounded-2xl border-4 border-white bg-gray-50 shadow-sm overflow-hidden flex-shrink-0">
                    @if($user->adminDetail && $user->adminDetail->profile_image)
                        <img src="{{ asset('storage/' . $user->adminDetail->profile_image) }}" class="w-full h-full object-cover" alt="Foto Profil">
                    @else
                        <img src="{{ asset('images/default-avatar.jpg') }}" class="w-full h-full object-cover" alt="Default Avatar">
                    @endif

                    <button type="button" onclick="document.getElementById('avatarInput').click()" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <i data-lucide="camera" class="w-6 h-6 text-white"></i>
                    </button>

                    <div class="absolute bottom-1 right-1 w-6 h-6 bg-white border border-gray-200 shadow-sm rounded-lg flex items-center justify-center pointer-events-none">
                        <i data-lucide="camera" class="w-3.5 h-3.5 text-gray-500"></i>
                    </div>

                    <input type="file" id="avatarInput" class="hidden" accept="image/*">
                </div>

                <div class="pt-2 md:pb-1.5">
                    <h2 class="text-lg md:text-xl font-extrabold text-gray-800 tracking-wide">
                        {{ strtoupper($user->adminDetail?->full_name ?? 'Administrator') }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-3 ">
                        <span class="bg-teal-50 text-teal-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                            Administrator
                        </span>
                        <span class="flex items-center gap-1.5 text-xs text-gray-500">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-[#3DBFA6]"></i>
                            {{ $user->email }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-7">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center">
                    <i data-lucide="user-round" class="w-5 h-5 text-[#3DBFA6]"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Informasi Pribadi</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Perbarui data diri Anda.</p>
                </div>
            </div>

            @if($errors->has('full_name') || $errors->has('email'))
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-xs text-red-600">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                    <input type="text" name="full_name"
                        value="{{ old('full_name', $user->adminDetail?->full_name) }}"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]"
                        required>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Email</label>
                    <input type="email" name="email"
                        value="{{ old('email', $user->email) }}"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]"
                        required>
                </div>
                <button type="submit"
                    class="bg-[#3DBFA6] hover:bg-[#32aa94] text-white text-xs font-bold px-6 py-3 rounded-xl transition">
                    Simpan Perubahan
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-7">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                    <i data-lucide="lock-keyhole" class="w-5 h-5 text-red-400"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Ubah Password</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Ganti kata sandi berkala.</p>
                </div>
            </div>

            @if($errors->has('current_password'))
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-xs text-red-600">
                    {{ $errors->first('current_password') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Kata Sandi Sekarang</label>
                    <div class="relative">
                        <input id="oldPass" type="password" name="current_password"
                            class="w-full h-11 px-4 pr-11 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                        <button type="button" onclick="togglePass('oldPass', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#3DBFA6]">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Kata Sandi Baru</label>
                    <div class="relative">
                        <input id="newPass" type="password" name="password"
                            class="w-full h-11 px-4 pr-11 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                        <button type="button" onclick="togglePass('newPass', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#3DBFA6]">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi</label>
                    <input type="password" name="password_confirmation"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3DBFA6]/20 focus:border-[#3DBFA6]">
                </div>
                <button type="submit"
                    class="w-full bg-red-50 hover:bg-red-100 text-red-500 text-xs font-bold px-6 py-3 rounded-xl transition">
                    Perbarui Kata Sandi
                </button>
            </form>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
function togglePass(id, button) {
    const input = document.getElementById(id);
    const icon = button.querySelector('i');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.setAttribute('data-lucide', input.type === 'password' ? 'eye' : 'eye-off');
    lucide.createIcons();
}

// Tambahan Script Frontend ringan untuk demo ganti foto (opsional biar pas diklik ganti gambarnya langsung berubah di layar)
document.getElementById('avatarInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const imgElement = e.target.parentElement.querySelector('img');
            if (imgElement) imgElement.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush