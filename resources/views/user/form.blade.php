@extends('dashboard.base')

@section('content')
<x-containers.container size="6">
  <x-containers.card>
    <x-slot name="title">
      {{ $user->name ?? 'Tambah User Baru' }}
    </x-slot>
    <form method="POST" action="/users/{{ $user->id }}">
        @csrf
        @if ($user->id)
          @method('PUT')
        @else
          @method('POST')
        @endif
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">
                  <svg class="c-icon c-icon-sm">
                      <use xlink:href="/assets/icons/coreui/free-symbol-defs.svg#cui-user"></use>
                  </svg>
                </span>
            </div>
            <input class="form-control" type="text" placeholder="{{ __('Name') }}" name="name" value="{{ $user->name }}" required autofocus>
        </div>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">@</span>
            </div>
            <input class="form-control" type="text" placeholder="{{ __('E-Mail Address') }}" name="email" value="{{ $user->email }}">
          </div>
          
        <input class="form-control mb-3" type="text" placeholder="{{ __('Phone number') }}" name="phone" value="{{ $user->detail ? $user->detail->phone : '' }}" required>
        <div class="d-flex mb-3">
          <input class="form-control mr-1" type="text" placeholder="{{ __('Birth place') }}" name="place_of_birth" value="{{ $user->detail ? $user->detail->place_of_birth : '' }}">
          <input class="form-control" type="date" placeholder="{{ __('Birth date') }}" name="date_of_birth" value="{{ $user->detail ? $user->detail->date_of_birth : '' }}">
        </div>
        <textarea class="form-control mb-3" type="text" placeholder="{{ __('Address') }}" name="address">{{ $user->detail ? $user->detail->address : '' }}</textarea>

        <x-forms.button type="submit">{{ __('Save') }}</x-forms.button>
        <x-forms.button href="{{ url()->previous() }}" preset="default">{{ __('Back') }}</x-forms.button>
    </form>
  </x-containers.card>
</x-containers.container>
@endsection

@section('javascript')

@endsection