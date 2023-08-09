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
    </table>
    <x-forms.button href="{{ route('va.index') }}" preset="default">{{ __('Back') }}</x-forms.button>
  </x-containers.card>
</x-containers.container>
<x-containers.container size="6">
  <x-containers.card>
    <x-slot name="title">History Transaksi</x-slot>
    <table class="table">
      <thead>
        <th>Waktu</th>
        <th>Total Pembayaran</th>
      </thead>
      <tbody>
        @if (!isset($payments) || sizeof($payments) <= 0)
          <tr>
            <td colspan="2">Belum ada history</td>
          </tr>
        @else
          @foreach ($payments as $payment)
          <tr>
            <td>: {{ $payment->created_at }}</td>
            <td>: {{ $payment->paidAmount['value'] }}</td>
          </tr>
          @endforeach
        @endif
      </tbody>
    </table>
    <x-forms.button href="{{ route('va.index') }}" preset="default">{{ __('Back') }}</x-forms.button>
  </x-containers.card>
</x-containers.container>
@endsection


@section('javascript')

@endsection