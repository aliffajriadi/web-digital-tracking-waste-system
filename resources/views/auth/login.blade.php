@extends('layouts.auth')

@section('title', 'Login Admin | WasteTracking')

@section('content')
<div class="w-full max-w-[400px]">

    <!-- Card Login -->
    <div class="bg-white rounded-3xl border border-white/20 shadow-2xl p-8 md:p-10">

        <!-- Logo -->
        <div class="flex justify-center mb-5">
            <div class="w-16 h-16 bg-white rounded-2xl shadow-md flex items-center justify-center">
                <img src="{{ asset('images/Politeknik_Negeri_Batam.png') }}"
                    alt="Logo Polibatam"
                    class="h-12 w-12 object-contain">
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-xl font-extrabold text-gray-800">Admin Login</h1>
            <p class="text-gray-400 text-[10px] mt-1.5 uppercase tracking-[0.2em] font-semibold">
                Digital Waste Tracking System
            </p>
        </div>

        <!-- Error message -->
        @if($errors->any())
        <div class="mb-5 px-4 py-3.5 bg-red-50 border border-red-100 rounded-2xl flex items-start gap-3">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5"></i>
            <p class="text-xs text-red-600 font-medium">{{ $errors->first() }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            <!-- Email -->
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">
                    Alamat Email
                </label>
                <input type="email" name="email"
                    value="{{ old('email') }}"
                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-[#34a88e]/20 focus:border-[#34a88e] transition-all"
                    placeholder="admin@polibatam.ac.id" required autofocus>
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1">
                    Kata Sandi
                </label>
                <div class="relative">
                    <input id="password" type="password" name="password"
                        class="w-full px-5 py-4 pr-12 bg-gray-50 border border-gray-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-[#34a88e]/20 focus:border-[#34a88e] transition-all"
                        placeholder="••••••••" required>

                    <button type="button" onclick="togglePassword()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                        <i id="eyeIcon" data-lucide="eye"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center gap-2.5">
                <input type="checkbox" id="remember" name="remember"
                    class="w-4 h-4 rounded border-gray-300 text-[#34a88e] focus:ring-[#34a88e]/20 cursor-pointer">
                <label for="remember" class="text-xs text-gray-500 cursor-pointer">Ingat saya</label>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-gradient-to-r from-[#34a88e] to-[#2bb8a0] hover:from-[#2d917a] hover:to-[#28a899] text-white font-bold py-4 rounded-2xl shadow-lg shadow-teal-500/20 transition-all transform hover:scale-[1.01]">
                Masuk ke Dashboard
            </button>
        </form>
    </div>

    <!-- Footer -->
    <p class="text-center text-white/60 text-[10px] mt-6 uppercase tracking-[0.2em] font-medium">
        &copy; 2026 Teknik Informatika — Polibatam
    </p>
</div>
@endsection

@push('scripts')
<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }
    lucide.createIcons();
}
</script>
@endpush