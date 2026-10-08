<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="WasteTracking - Sistem Digital Monitoring Rumah Sampah Polibatam">
    <title>@yield('title', 'WasteTracking Admin')</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#ecfdf8', 100: '#d1faee', 200: '#a7f3dd', 300: '#6ee7c7',
                            400: '#3dbfa6', 500: '#1fa88c', 600: '#158f77', 700: '#127261',
                            800: '#125b4f', 900: '#114b42',
                        },
                    },
                },
            },
        };
    </script>
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
        aside { transition: transform .25s cubic-bezier(.4,0,.2,1); }
        @keyframes slideInRight { from { transform: translateX(110%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .toast-enter { animation: slideInRight .3s ease; }
        .stat-card { transition: transform .2s, box-shadow .2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(15,23,42,.07); }
        /* Komponen form yang dipakai di seluruh halaman */
        .form-label { display:block; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; }
        .form-input { width:100%; height:42px; padding:0 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; font-size:14px; color:#1e293b; transition:border-color .15s, box-shadow .15s; }
        textarea.form-input { height:auto; padding:10px 14px; }
        .form-input:focus { outline:none; border-color:#1fa88c; box-shadow:0 0 0 3px rgba(31,168,140,.15); background:#fff; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; height:42px; padding:0 18px; border-radius:12px; font-size:14px; font-weight:700; transition:all .15s; }
        .btn-primary { background:#1fa88c; color:#fff; } .btn-primary:hover { background:#158f77; }
        .btn-secondary { background:#fff; color:#475569; border:1px solid #e2e8f0; } .btn-secondary:hover { background:#f8fafc; }
        .btn-danger { background:#ef4444; color:#fff; } .btn-danger:hover { background:#dc2626; }
        .btn[disabled] { opacity:.6; cursor:not-allowed; }
        .spin { animation: spin 1s linear infinite; } @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased m-0"
      x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
      @resize.window="if (window.innerWidth >= 1024) sidebarOpen = true">

    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[10000] focus:bg-white focus:px-3 focus:py-2 focus:rounded-lg">Lewati ke konten</a>

    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 bg-slate-900/40 z-30 lg:hidden" x-cloak></div>

    @include('layouts.partials.sidebar')
    @include('layouts.partials.navbar')

    <main id="main" class="pt-16 min-h-screen transition-all duration-300" :class="sidebarOpen ? 'lg:ml-72' : 'lg:ml-0'">
        <div class="px-4 md:px-8 py-6 md:py-8">
            @yield('content')
        </div>
    </main>

    {{-- ===== Toast notifikasi ===== --}}
    <div class="fixed bottom-5 right-5 left-5 sm:left-auto z-[9999] flex flex-col gap-3 sm:w-96" aria-live="polite">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.opacity
                 class="toast-enter flex items-start gap-3 bg-white border border-emerald-100 rounded-xl px-4 py-3.5 shadow-xl shadow-slate-200/60">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800">Berhasil</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-slate-300 hover:text-slate-500" aria-label="Tutup"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)" x-transition.opacity
                 class="toast-enter flex items-start gap-3 bg-white border border-red-100 rounded-xl px-4 py-3.5 shadow-xl shadow-slate-200/60">
                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800">Gagal</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-slate-300 hover:text-slate-500" aria-label="Tutup"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
        @endif

        {{-- Sebelumnya error validasi dari form modal tidak pernah ditampilkan --}}
        @if($errors->any())
            <div x-data="{ show: true }" x-show="show" x-transition.opacity
                 class="toast-enter flex items-start gap-3 bg-white border border-amber-200 rounded-xl px-4 py-3.5 shadow-xl shadow-slate-200/60">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800">Periksa kembali isian Anda</p>
                    <ul class="text-xs text-slate-500 mt-1 space-y-0.5 list-disc pl-4">
                        @foreach(collect($errors->all())->unique()->take(6) as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
                <button @click="show = false" class="text-slate-300 hover:text-slate-500" aria-label="Tutup"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
        @endif
    </div>

    {{-- ===== Modal konfirmasi global (dipakai oleh form dengan atribut data-confirm) ===== --}}
    <div x-data="confirmDialog()" x-show="open" x-cloak @keydown.escape.window="cancel()"
         class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div x-show="open" x-transition @click.outside="cancel()" role="alertdialog" aria-modal="true"
             class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4"
                 :class="danger ? 'bg-red-50 text-red-500' : 'bg-brand-50 text-brand-600'">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800" x-text="title"></h3>
            <p class="text-sm text-slate-500 mt-1.5" x-text="message"></p>
            <div class="flex justify-end gap-2.5 mt-6">
                <button type="button" class="btn btn-secondary" @click="cancel()">Batal</button>
                <button type="button" class="btn" :class="danger ? 'btn-danger' : 'btn-primary'" @click="confirm()" x-text="okLabel" x-ref="ok"></button>
            </div>
        </div>
    </div>

    <script>
        function confirmDialog() {
            return {
                open: false, title: '', message: '', okLabel: 'Ya, lanjutkan', danger: true, form: null,
                init() {
                    document.addEventListener('submit', (e) => {
                        const form = e.target;
                        if (!form.dataset.confirm || form.dataset.confirmed === '1') return;
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        this.form = form;
                        this.title = form.dataset.confirmTitle || 'Konfirmasi';
                        this.message = form.dataset.confirm;
                        this.okLabel = form.dataset.confirmOk || 'Ya, hapus';
                        this.danger = form.dataset.confirmTone !== 'neutral';
                        this.open = true;
                        this.$nextTick(() => { lucide.createIcons(); this.$refs.ok.focus(); });
                    }, true);
                },
                cancel() { this.open = false; this.form = null; },
                confirm() {
                    if (!this.form) return;
                    this.form.dataset.confirmed = '1';
                    this.open = false;
                    this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit();
                },
            };
        }

        // Cegah klik ganda: tombol submit dinonaktifkan dan diberi indikator loading
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (e.defaultPrevented || form.method.toLowerCase() === 'get' || form.dataset.noLoading !== undefined) return;
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach((btn) => {
                btn.disabled = true;
                btn.dataset.originalHtml = btn.innerHTML;
                btn.innerHTML = '<svg class="w-4 h-4 spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/><path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3"/></svg><span>Memproses…</span>';
            });
        });
        // Kembalikan tombol jika halaman dipulihkan dari cache (tombol back browser)
        window.addEventListener('pageshow', () => {
            document.querySelectorAll('button[data-original-html]').forEach((btn) => {
                btn.disabled = false;
                btn.innerHTML = btn.dataset.originalHtml;
            });
        });

        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
        document.addEventListener('alpine:initialized', () => lucide.createIcons());

        window.formatNumber = (n, d = 2) => Number(n || 0).toLocaleString('id-ID', { maximumFractionDigits: d });
    </script>

    @stack('scripts')
</body>
</html>
