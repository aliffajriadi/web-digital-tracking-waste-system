<aside
    class="fixed top-0 left-0 z-40 w-72 h-screen bg-gradient-to-b from-[#1fa88c] to-[#158f77] text-white border-r border-teal-400/20 shadow-2xl"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    <div class="h-20 flex items-center px-5 border-b border-white/15">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm flex-shrink-0">
                <img src="{{ asset('images/Politeknik_Negeri_Batam.png') }}"
                     alt="Logo Polibatam"
                     class="h-8 w-8 object-contain">
            </div>
            <div>
                <h2 class="text-sm font-bold tracking-wide leading-tight">WasteTracking</h2>
                <p class="text-[10px] font-medium text-white/70 leading-tight uppercase tracking-widest">Admin Panel</p>
            </div>
        </div>
    </div>

    <div class="h-[calc(100%-80px)] px-3 py-5 overflow-y-auto">
        <ul class="space-y-0.5">

            <li>
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="layout-dashboard" class="w-4.5 h-4.5 flex-shrink-0"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="pt-3 pb-1">
                <p class="px-4 text-[9px] font-bold text-white/40 uppercase tracking-[0.15em]">Monitoring</p>
            </li>

            <li>
                <a href="{{ route('admin.waste-entry.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.waste-entry.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="inbox" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Sampah Masuk</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.waste-out.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.waste-out.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="send" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Sampah Keluar</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.processed-waste-data.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.processed-waste-data.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="cpu" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Pengolahan</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.report.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.report.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="file-text" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Laporan</span>
                </a>
            </li>

            <li class="pt-3 pb-1">
                <p class="px-4 text-[9px] font-bold text-white/40 uppercase tracking-[0.15em]">Data Master</p>
            </li>

            <li>
                <a href="{{ route('admin.waste-category.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.waste-category.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="layers" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Kategori Sampah</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.waste-subcategory.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.waste-subcategory.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="tag" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Sub-Kategori Sampah</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.waste-b3.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.waste-b3.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="flask-conical" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Limbah B3</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.processed-waste.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.processed-waste.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="recycle" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Jenis Olahan</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.unit-measured.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.unit-measured.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="ruler" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Satuan Ukur</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.source-location.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.source-location.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="map-pin" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Sumber Sampah</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.collector-buyer.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.collector-buyer.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="building-2" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Pengepul / Pembeli</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.waste-out-method.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.waste-out-method.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="send" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Metode Keluar</span>
                </a>
            </li>

            <li class="pt-3 pb-1">
                <p class="px-4 text-[9px] font-bold text-white/40 uppercase tracking-[0.15em]">Akun</p>
            </li>

            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="users" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Kelola Pengguna</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.profile') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/80 hover:bg-white/15 hover:text-white transition-all text-sm font-medium {{ request()->routeIs('admin.profile') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="user-round" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Profil Saya</span>
                </a>
            </li>

        </ul>

        <div class="mt-6 px-1">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-white/70 hover:bg-red-500/20 hover:text-red-200 transition-all text-sm font-medium">
                    <i data-lucide="log-out" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>