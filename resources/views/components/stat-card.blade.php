@props(['label', 'value', 'icon', 'tone' => 'brand', 'hint' => null, 'href' => null])
@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'orange' => 'bg-orange-50 text-orange-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'red' => 'bg-red-50 text-red-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'slate' => 'bg-slate-100 text-slate-600',
    ];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif class="stat-card block bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
    <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $tones[$tone] ?? $tones['brand'] }}">
            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
        </div>
        <p class="text-xs font-semibold text-slate-500 leading-tight">{{ $label }}</p>
    </div>
    <p class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $value }}</p>
    @if($hint)<p class="text-xs text-slate-400 mt-1">{{ $hint }}</p>@endif
</{{ $tag }}>
