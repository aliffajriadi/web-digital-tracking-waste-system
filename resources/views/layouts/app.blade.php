<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="WasteTracking - Sistem Digital Monitoring Rumah Sampah Polibatam">
    <title>@yield('title', 'WasteTracking Admin')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

        /* Sidebar active link */
        .sidebar-link-active {
            background: rgba(255,255,255,0.2);
            color: white !important;
        }

        /* Smooth transition for sidebar */
        aside { transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); }

        /* Toast animation */
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }
        .toast-enter { animation: slideInRight 0.3s ease; }

        /* Stat card shimmer */
        .stat-card { transition: transform 0.2s, box-shadow 0.2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    </style>
</head>
<body class="bg-slate-50 antialiased m-0" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">

    <div x-show="sidebarOpen && window.innerWidth < 1024"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/40 z-30 lg:hidden"
         x-cloak></div>

    @include('layouts.partials.sidebar')

    @include('layouts.partials.navbar')

    <main class="pt-16 min-h-screen transition-all duration-300"
          :class="sidebarOpen ? 'lg:ml-72' : 'lg:ml-0'">
        <div class="px-5 md:px-8 py-7">
            @yield('content')
        </div>
    </main>

    @if(session('success'))
    <div id="toast-success"
         class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 bg-white border border-green-100 rounded-xl px-5 py-4 shadow-xl shadow-gray-200/50 toast-enter">
        <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-green-500"></i>
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-800">Berhasil!</p>
            <p class="text-xs text-gray-500">{{ session('success') }}</p>
        </div>
        <button onclick="document.getElementById('toast-success').remove()" class="ml-2 text-gray-300 hover:text-gray-500">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div id="toast-error"
         class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 bg-white border border-red-100 rounded-xl px-5 py-4 shadow-xl shadow-gray-200/50 toast-enter">
        <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-800">Gagal!</p>
            <p class="text-xs text-gray-500">{{ session('error') }}</p>
        </div>
        <button onclick="document.getElementById('toast-error').remove()" class="ml-2 text-gray-300 hover:text-gray-500">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif

    <script>
        lucide.createIcons();

        // Auto-hide toasts
        setTimeout(() => {
            const toasts = document.querySelectorAll('[id^="toast-"]');
             toasts.forEach(t => t.remove());
        }, 5000);
    </script>

    @stack('scripts')
</body>
</html>