@php
$size = $attributes && $attributes['size'] ? $attributes['size'] : 12;
$contentClass = 'col-sm-12 col-md-12';
switch ($size) {
    case 6:
        $contentClass = 'col-sm-12 col-md-6';
        break;
    default:
        $contentClass = 'col-sm-12 col-md-12';
        break;
}
@endphp

<div class="container-fluid">
    <div class="animated fadeIn">
        <div class="row">
            <div class="{{ $contentClass }}">
                @if(Session::has('message'))
                    <x-alerts.alert>
                        {{ Session::get('message') }}
                    </x-alerts.alert>
                @endif
                @if(Session::has('errors'))
                    <x-alerts.alert preset="danger">
                        {{ $errors->first() }}
                    </x-alerts.alert>
                @endif
                {{ $slot }}
            </div>
        </div>
    </div>
</div>