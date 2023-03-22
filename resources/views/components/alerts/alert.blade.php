@php
$preset = $attributes && $attributes['preset'] ? $attributes['preset'] : 'success';
@endphp
<div class="row">
    <div class="col-12">
        <div class="alert alert-{{ $preset }}" role="alert">
            {{ $slot }}
        </div>
    </div>
</div>