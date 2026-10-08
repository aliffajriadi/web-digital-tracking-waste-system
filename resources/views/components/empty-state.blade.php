@props(['icon' => 'inbox', 'title' => 'Belum ada data', 'message' => null])
<div class="px-6 py-14 text-center">
    <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3">
        <i data-lucide="{{ $icon }}" class="w-7 h-7 text-slate-300"></i>
    </div>
    <p class="text-sm font-semibold text-slate-600">{{ $title }}</p>
    @if($message)<p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">{{ $message }}</p>@endif
    @if(trim($slot))<div class="mt-4">{{ $slot }}</div>@endif
</div>
