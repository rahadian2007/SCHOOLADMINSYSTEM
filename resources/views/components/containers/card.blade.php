<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        @if (isset($title))
            <b>{{ $title }}</b>
        @endif
        @if (isset($addNew))
            {{ $addNew }}
        @endif
        @if (isset($attributes['searchEnabled']))
        <form method="GET" action="{{ request()->fullUrlWithQuery(['q' => '']) }}" class="w-25">
            <input type="text" class="form-control" name="q" placeholder="Search" value="{{ request()->input('q') }}" />
        </form>
        @endif
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
</div>