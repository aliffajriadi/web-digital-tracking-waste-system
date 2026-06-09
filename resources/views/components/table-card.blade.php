{{-- Reusable table card wrapper --}}
{{--
    Props:
    - $title: string
    - $subtitle: string
    - $addLabel: string
    - $addRoute: optional
    - $searchName: optional
    - $searchValue: optional
    - $searchPlaceholder: optional
    - $filters: optional slot
--}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-gray-800">{{ $title }}</h3>
            @if(isset($subtitle))
                <p class="text-xs text-gray-400 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        {{ $actions ?? '' }}
    </div>
    {{ $slot }}
</div>
