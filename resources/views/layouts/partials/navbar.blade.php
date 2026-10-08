@php
    $adminName = auth()->user()?->adminDetail?->full_name ?? 'Administrator';
    $initials = collect(explode(' ', $adminName))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp

<nav class="fixed top-0 z-30 w-full h-16 bg-white/90 backdrop-blur border-b border-slate-200/80 flex items-center justify-between px-4 md:px-6 transition-all duration-300"
     :class="sidebarOpen ? 'lg:pl-[312px]' : 'lg:pl-6'">

    <div class="flex items-center gap-3 min-w-0">
        <button @click="sidebarOpen = !sidebarOpen" aria-label="Buka/tutup menu"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 transition-colors flex-shrink-0">
            <i data-lucide="panel-left" class="w-5 h-5"></i>
        </button>
        <div class="min-w-0">
            <p class="text-[11px] text-slate-400 leading-none truncate">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-600">Admin</a>
                @hasSection('breadcrumb')
                    <span class="mx-1">/</span>@yield('breadcrumb')
                @endif
            </p>
            <h1 class="text-base font-bold text-slate-800 leading-tight truncate">@yield('page-title', 'Dashboard')</h1>
        </div>
    </div>

    <div class="flex items-center gap-1.5">
        {{-- Peringatan limbah B3 --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="relative w-10 h-10 flex items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100" aria-label="Peringatan limbah B3">
                <i data-lucide="bell" class="w-5 h-5"></i>
                @if(($b3AlertCount ?? 0) > 0)
                    <span class="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white">{{ $b3AlertCount }}</span>
                @endif
            </button>
            <div x-show="open" x-transition x-cloak
                 class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white border border-slate-100 rounded-2xl shadow-xl shadow-slate-200/60 overflow-hidden z-50">
                <div class="px-4 py-3 border-b border-slate-100">
                    <p class="text-sm font-bold text-slate-800">Peringatan Limbah B3</p>
                    <p class="text-[11px] text-slate-400">Mendekati atau melewati batas masa simpan</p>
                </div>
                <div class="max-h-80 overflow-y-auto divide-y divide-slate-50">
                    @forelse(($b3AlertsPreview ?? collect()) as $alert)
                        <a href="{{ route('admin.stock.index', ['status' => 'b3']) }}" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50">
                            <span class="mt-0.5 w-2 h-2 rounded-full flex-shrink-0 {{ $alert['status'] === 'expired' ? 'bg-red-500' : ($alert['status'] === 'critical' ? 'bg-orange-500' : 'bg-amber-400') }}"></span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-800 truncate">{{ $alert['waste_code'] }} · {{ $alert['waste_name'] }}</p>
                                <p class="text-[11px] text-slate-500">
                                    @if($alert['sisa_hari'] < 0)
                                        Lewat {{ abs($alert['sisa_hari']) }} hari dari batas simpan
                                    @elseif($alert['sisa_hari'] === 0)
                                        Batas simpan hari ini
                                    @else
                                        Sisa {{ $alert['sisa_hari'] }} hari
                                    @endif
                                    · {{ \App\Services\StockService::format($alert['stock']) }} {{ $alert['unit'] }}
                                </p>
                            </div>
                        </a>
                    @empty
                        <div class="px-4 py-8 text-center">
                            <i data-lucide="shield-check" class="w-8 h-8 text-emerald-300 mx-auto mb-2"></i>
                            <p class="text-xs text-slate-400">Tidak ada limbah B3 yang perlu ditindaklanjuti.</p>
                        </div>
                    @endforelse
                </div>
                <a href="{{ route('admin.stock.index') }}" class="block px-4 py-2.5 text-center text-xs font-semibold text-brand-600 hover:bg-brand-50 border-t border-slate-100">Lihat stok gudang</a>
            </div>
        </div>

        {{-- Profil --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="flex items-center gap-2.5 pl-1.5 pr-2.5 py-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-brand-100 text-brand-700 text-xs font-bold flex items-center justify-center">{{ $initials ?: 'A' }}</span>
                <span class="hidden md:block text-left">
                    <span class="block text-xs font-bold text-slate-800 leading-tight">{{ $adminName }}</span>
                    <span class="block text-[10px] text-slate-400 leading-tight">Administrator</span>
                </span>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div x-show="open" x-transition x-cloak
                 class="absolute right-0 mt-2 w-52 bg-white border border-slate-100 rounded-xl shadow-xl shadow-slate-200/60 p-1.5 z-50">
                <a href="{{ route('admin.profile') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-600 hover:bg-slate-50 rounded-lg">
                    <i data-lucide="user-round-cog" class="w-4 h-4 text-slate-400"></i> Profil Saya
                </a>
                <div class="border-t border-slate-100 my-1"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 font-semibold hover:bg-red-50 rounded-lg">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
