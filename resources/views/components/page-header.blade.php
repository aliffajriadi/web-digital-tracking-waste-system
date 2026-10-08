@props(['title', 'subtitle' => null])
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if(trim($slot))
        <div class="flex flex-wrap items-center gap-2 sm:flex-shrink-0">{{ $slot }}</div>
    @endif
</div>
