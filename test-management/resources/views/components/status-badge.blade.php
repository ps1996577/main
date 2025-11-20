@props(['status' => 'draft'])
@php
    $map = [
        'ready' => ['label' => 'Gotowy', 'classes' => 'bg-green-100 text-green-800'],
        'draft' => ['label' => 'Szkic', 'classes' => 'bg-amber-100 text-amber-800'],
        'deprecated' => ['label' => 'Wycofany', 'classes' => 'bg-gray-200 text-gray-700'],
    ];
    $config = $map[$status] ?? $map['draft'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {$config['classes']}"]) }}>
    {{ $config['label'] }}
</span>
