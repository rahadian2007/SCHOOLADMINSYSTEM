@extends('dashboard.base')

@section('content')
<x-containers.container size="6">
  <x-containers.card>
    <x-slot name="title">{{ $user->name }}</x-slot>
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-center">
          <img src="/svg/illustration-personal-info.svg" width="480" />
        </div>
      </div>
    </div>
    <table class="table">
      <tr>
        <td>Name</td>
        <td>: {{ $user->name }}</td>
      </tr>
      <tr>
        <td>Email</td>
        <td>: {{ $user->email }}</td>
      </tr>
    </table>
    <x-forms.button href="{{ route('users.index') }}" preset="default">{{ __('Back') }}</x-forms.button>
  </x-containers.card>
</x-containers.container>
@endsection


@section('javascript')

@endsection