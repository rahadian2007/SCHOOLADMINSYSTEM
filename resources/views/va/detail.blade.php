@extends('dashboard.base')

@section('content')
<x-containers.container size="6">
  <x-containers.card>
    <x-slot name="title">{{ $va->number }}</x-slot>
    <table class="table">
      <tr>
        <td>User Name</td>
        <td>: {{ $va->user->name }}</td>
      </tr>
      <tr>
        <td>VA Number</td>
        <td>: {{ $va->number }}</td>
      </tr>
      <tr>
        <td>Status</td>
        <td>: {{ $va->is_active ? 'Aktif' : 'Tidak Aktif' }}</td>
      </tr>
    </table>
    <x-forms.button href="{{ route('va.index') }}" preset="default">{{ __('Back') }}</x-forms.button>
  </x-containers.card>
</x-containers.container>
@endsection


@section('javascript')

@endsection