@php
$preset = $attributes && $attributes['preset'] ? $attributes['preset'] : 'primary';
$type = $attributes && $attributes['type'] ? $attributes['type'] : 'primary';
$size = $attributes && $attributes['size'] ? $attributes['size'] : 'md';
$additionalClasses = $attributes && $attributes['class'] ? $attributes['class'] : '';
@endphp

@if ($type === 'submit')
<button type="submit" class="btn btn-{{ $preset }} btn-{{ $size }} {{ $additionalClasses }}" {{ $attributes }}>
    {{ $slot }}
</button>
@else
<a class="btn btn-{{ $preset }} btn-{{ $size }} {{ $additionalClasses }}" {{ $attributes }}>
    {{ $slot }}
</a>
@endif
