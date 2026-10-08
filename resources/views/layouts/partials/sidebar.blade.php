@php
    $menu = [
        ['type' => 'link', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],

        ['type' => 'heading', 'label' => 'Operasional'],
        ['type' => 'link', 'route' => 'admin.stock.index', 'match' => 'admin.stock.*', 'icon' => 'warehouse', 'label' => 'Stok Gudang', 'badge' => $b3AlertCount ?? 0],
        ['type' => 'link', 'route' => 'admin.waste-entry.index', 'match' => 'admin.waste-entry.*', 'icon' => 'arrow-down-to-line', 'label' => 'Sampah Masuk'],
        ['type' => 'link', 'route' => 'admin.processed-waste-data.index', 'match' => 'admin.processed-waste-data.*', 'icon' => 'recycle', 'label' => 'Pengolahan'],
        ['type' => 'link', 'route' => 'admin.waste-out.index', 'match' => 'admin.waste-out.*', 'icon' => 'arrow-up-from-line', 'label' => 'Sampah Keluar'],

        ['type' => 'heading', 'label' => 'Laporan'],
        ['type' => 'link', 'route' => 'admin.report.index', 'match' => 'admin.report.*', 'icon' => 'bar-chart-3', 'label' => 'Laporan Data Sampah'],
        ['type' => 'link', 'route' => 'admin.pic-report.index', 'match' => 'admin.pic-report.*', 'icon' => 'message-square-warning', 'label' => 'Laporan Kendala PIC'],

        ['type' => 'heading', 'label' => 'Data Master'],
        ['type' => 'group', 'label' => 'Jenis Sampah', 'icon' => 'layers', 'items' => [
            ['route' => 'admin.waste-category.index', 'match' => 'admin.waste-category.*', 'label' => 'Kategori'],
            ['route' => 'admin.waste-subcategory.index', 'match' => 'admin.waste-subcategory.*', 'label' => 'Sub-Kategori'],
            ['route' => 'admin.waste-b3.index', 'match' => 'admin.waste-b3.*', 'label' => 'Limbah B3'],
            ['route' => 'admin.processed-waste.index', 'match' => 'admin.processed-waste.*', 'label' => 'Jenis Olahan'],
            ['route' => 'admin.unit-measured.index', 'match' => 'admin.unit-measured.*', 'label' => 'Satuan Ukur'],
        ]],
        ['type' => 'group', 'label' => 'Alur & Mitra', 'icon' => 'route', 'items' => [
            ['route' => 'admin.source-location.index', 'match' => 'admin.source-location.*', 'label' => 'Sumber Sampah'],
            ['route' => 'admin.waste-out-method.index', 'match' => 'admin.waste-out-method.*', 'label' => 'Metode Keluar'],
            ['route' => 'admin.collector-buyer.index', 'match' => 'admin.collector-buyer.*', 'label' => 'Pengepul / Pembeli'],
            ['route' => 'admin.category-report.index', 'match' => 'admin.category-report.*', 'label' => 'Kategori Kendala'],
        ]],

        ['type' => 'heading', 'label' => 'Akun'],
        ['type' => 'link', 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'users', 'label' => 'Kelola PIC'],
        ['type' => 'link', 'route' => 'admin.profile', 'match' => 'admin.profile*', 'icon' => 'user-round-cog', 'label' => 'Profil Saya'],
    ];
@endphp

<aside class="fixed top-0 left-0 z-40 w-72 h-screen bg-gradient-to-b from-brand-500 to-brand-700 text-white shadow-2xl flex flex-col"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" aria-label="Navigasi utama">

    <div class="h-16 flex items-center justify-between px-5 border-b border-white/15 flex-shrink-0">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm flex-shrink-0">
                <img src="{{ asset('images/Politeknik_Negeri_Batam.png') }}" alt="Logo Polibatam" class="h-8 w-8 object-contain">
            </div>
            <div>
                <p class="text-sm font-bold tracking-wide leading-tight">WasteTracking</p>
                <p class="text-[10px] font-medium text-white/70 leading-tight uppercase tracking-widest">Admin Panel</p>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden w-8 h-8 rounded-lg hover:bg-white/15 flex items-center justify-center" aria-label="Tutup menu">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <nav class="flex-1 px-3 py-4 overflow-y-auto">
        <ul class="space-y-0.5">
            @foreach($menu as $item)
                @if($item['type'] === 'heading')
                    <li class="pt-4 pb-1.5 px-4 text-[10px] font-bold text-white/45 uppercase tracking-[0.15em]">{{ $item['label'] }}</li>
                @elseif($item['type'] === 'link')
                    @php $active = request()->routeIs($item['match']); @endphp
                    <li>
                        <a href="{{ route($item['route']) }}" @if($active) aria-current="page" @endif
                           class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all {{ $active ? 'bg-white text-brand-700 shadow-sm' : 'text-white/85 hover:bg-white/15 hover:text-white' }}">
                            <i data-lucide="{{ $item['icon'] }}" class="w-[18px] h-[18px] flex-shrink-0"></i>
                            <span class="flex-1">{{ $item['label'] }}</span>
                            @if(!empty($item['badge']))
                                <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center" title="Peringatan limbah B3">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    </li>
                @else
                    @php $groupActive = collect($item['items'])->contains(fn ($i) => request()->routeIs($i['match'])); @endphp
                    <li x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open" :aria-expanded="open"
                                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all {{ $groupActive ? 'text-white bg-white/10' : 'text-white/85 hover:bg-white/15 hover:text-white' }}">
                            <i data-lucide="{{ $item['icon'] }}" class="w-[18px] h-[18px] flex-shrink-0"></i>
                            <span class="flex-1 text-left">{{ $item['label'] }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="open && 'rotate-180'"></i>
                        </button>
                        <ul x-show="open" x-transition.opacity x-cloak class="mt-0.5 ml-[26px] pl-4 border-l border-white/20 space-y-0.5">
                            @foreach($item['items'] as $sub)
                                @php $active = request()->routeIs($sub['match']); @endphp
                                <li>
                                    <a href="{{ route($sub['route']) }}" @if($active) aria-current="page" @endif
                                       class="block px-3 py-2 rounded-lg text-[13px] transition-all {{ $active ? 'bg-white text-brand-700 font-semibold' : 'text-white/75 hover:bg-white/15 hover:text-white' }}">
                                        {{ $sub['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>

    <div class="p-3 border-t border-white/15 flex-shrink-0">
        <form method="POST" action="{{ route('logout') }}" data-confirm="Anda akan keluar dari panel admin." data-confirm-title="Keluar?" data-confirm-ok="Keluar" data-confirm-tone="neutral">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium">
                <i data-lucide="log-out" class="w-[18px] h-[18px]"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
