{{-- Port of the demo's Badge (outline pill + optional status dot).
     variant: outline|default|success|warning|destructive|info
     dot: optional Tailwind bg class for the leading dot, e.g. "bg-emerald-500". --}}
@props(['variant' => 'outline', 'dot' => null])
@php
$styles = [
    'outline'     => 'border-gray-200 text-gray-700',
    'default'     => 'border-transparent bg-gray-900 text-white',
    'success'     => 'border-transparent bg-emerald-500/15 text-emerald-600',
    'warning'     => 'border-transparent bg-amber-500/15 text-amber-600',
    'destructive' => 'border-transparent bg-red-500/15 text-red-600',
    'info'        => 'border-transparent bg-blue-500/15 text-blue-600',
];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-md border px-2 py-0.5 text-xs font-medium ' . ($styles[$variant] ?? $styles['outline'])]) }}>
    @if($dot)<span aria-hidden="true" class="size-1.5 rounded-full {{ $dot }}"></span>@endif
    {{ $slot }}
</span>
