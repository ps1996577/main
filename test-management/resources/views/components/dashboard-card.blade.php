@props([
    'label' => '',
    'value' => 0,
    'icon' => 'chart-bar',
    'tone' => 'default',
])
@php
    $tones = [
        'default' => 'text-slate-600 bg-slate-100',
        'success' => 'text-emerald-600 bg-emerald-100',
        'warning' => 'text-amber-600 bg-amber-100',
        'danger' => 'text-rose-600 bg-rose-100',
    ];
@endphp
@php
    $iconLetter = strtoupper(substr($icon, 0, 1));
@endphp
<div class="bg-white shadow rounded-xl p-6">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full {{ $tones[$tone] ?? $tones['default'] }}">
            <span class="text-base font-semibold">{{ $iconLetter }}</span>
        </span>
    </div>
    <p class="mt-4 text-3xl font-semibold text-gray-900">{{ number_format($value) }}</p>
</div>
