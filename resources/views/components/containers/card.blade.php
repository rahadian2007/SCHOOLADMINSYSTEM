<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        @if (isset($title))
            <b>{{ $title }}</b>
        @endif
        @if (isset($addNew))
            {{ $addNew }}
        @endif
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
</div>