{{--
    Modal yang dikendalikan variabel Alpine `modal` milik elemen induk.
    Buka dengan: @click="modal = 'nama'"
--}}
@props(['name', 'title', 'subtitle' => null, 'maxWidth' => 'max-w-lg'])
<div x-show="modal === '{{ $name }}'" x-cloak @keydown.escape.window="modal = null"
     class="fixed inset-0 z-[9998] flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm sm:p-4"
     role="dialog" aria-modal="true">
    <div x-show="modal === '{{ $name }}'" x-transition @click.outside="modal = null"
         class="w-full {{ $maxWidth }} bg-white sm:rounded-2xl rounded-t-2xl shadow-2xl max-h-[92vh] flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex items-start justify-between gap-4 flex-shrink-0">
            <div>
                <h3 class="text-base font-bold text-slate-800">{{ $title }}</h3>
                @if($subtitle)<p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>@endif
            </div>
            <button type="button" @click="modal = null" class="w-8 h-8 -mr-2 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400" aria-label="Tutup">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="overflow-y-auto">
            {{ $slot }}
        </div>
    </div>
</div>
