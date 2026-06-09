<nav class="fixed top-0 z-30 w-full h-16 bg-white/90 backdrop-blur-sm border-b border-gray-200/80 flex items-center justify-between px-5 shadow-sm transition-all duration-300"
     :class="sidebarOpen ? 'lg:pl-[304px]' : 'lg:pl-5'">

    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = !sidebarOpen"
            class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 transition-colors">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <div class="hidden sm:block">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest leading-none">Admin Panel</p>
            <h1 class="text-base font-bold text-gray-800 leading-tight">
                @yield('page-title', 'Dashboard')
            </h1>
        </div>
    </div>

    <div class="flex items-center gap-2" x-data="{ profileOpen: false }">

        <div class="relative" @click.outside="profileOpen = false">
            <button @click="profileOpen = !profileOpen"
                    class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl hover:bg-gray-100 transition-colors group">

                <div class="w-8 h-8 rounded-lg border-2 border-[#3DBFA6]/30 overflow-hidden bg-gray-100">
                    @if(auth()->check() && auth()->user()->adminDetail && auth()->user()->adminDetail->profile_image)
                        <img src="{{ asset('storage/' . auth()->user()->adminDetail->profile_image) }}"
                            class="w-full h-full object-cover"
                            alt="Foto Profil">
                    @else
                        <img src="{{ asset('images/default-avatar.jpg') }}"
                            class="w-full h-full object-cover"
                            alt="Default Avatar">
                    @endif
                </div>

                <div class="hidden md:block text-left">
                    <p class="text-xs font-bold text-gray-800 leading-tight">
                        {{ auth()->check() ? (auth()->user()->adminDetail->full_name ?? 'Administrator') : 'Administrator' }}
                    </p>
                    <p class="text-[10px] text-gray-400 leading-tight">Admin</p>
                </div>

                <span class="transition-transform duration-200" :class="{'rotate-180': profileOpen}">
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400"></i>
                </span>
            </button>

            <div x-show="profileOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 class="absolute right-0 mt-2 w-52 bg-white border border-gray-100 rounded-xl shadow-xl shadow-gray-200/50 p-1.5 z-50"
                 x-cloak>

                <a href="{{ route('admin.profile') }}"
                   class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                    <i data-lucide="user-round" class="w-4 h-4 text-gray-400"></i>
                    Profil Saya
                </a>

                <div class="border-t border-gray-100 my-1"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 font-semibold hover:bg-red-50 rounded-lg transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>