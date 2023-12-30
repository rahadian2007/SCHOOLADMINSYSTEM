@extends('dashboard.base')

@section('content')
<x-containers.container size="12">
  <div class="row">
    <div class="col-md-6">
      <x-containers.card>
        <x-slot name="title">Detil Virtual Account</x-slot>
        <div class="row">
          <div class="col-12">
            <div class="d-flex justify-content-center">
              <img src="/svg/illustration-profile.svg" width="480" />
            </div>
          </div>
        </div>
        <table class="table">
          <tr>
            <td>Nama</td>
            <td>: {{ $va->user->name }}</td>
          </tr>
          <tr>
            <td>Nomor Virtual Account</td>
            <td>: {{ $va->number }}</td>
          </tr>
          <tr>
            <td>Tagihan Terakhir</td>
            <td>: @currency($va->outstanding)</td>
          </tr>
        </table>
         <div class="d-flex justify-content-between">
          <x-forms.button href="{{ url()->previous() }}" preset="default">{{ __('Back') }}</x-forms.button>
          <!-- <form method="POST" action="{{ route('va.status-update', ['va' => $va->id]) }}">
            @csrf
            <button type="submit" class="btn btn-dark">{{ __('Status Update') }}</button>
          </form> -->
        </div>
      </x-containers.card>
    </div>
    <div class="col-md-6">
      <x-containers.card>
        <x-slot name="title">History Pembayaran</x-slot>
        <table class="table table-striped table-hover table-sm">
          <thead>
            <th>Waktu</th>
            <th class="text-right">Total Pembayaran</th>
            <th>Status</th>
          </thead>
          <tbody>
            @if (!isset($payments) || sizeof($payments) <= 0)
              <tr>
                <td colspan="3">Belum ada history</td>
              </tr>
            @else
              @foreach ($payments as $payment)
              <tr>
                <td>{{ $payment->created_at->format('d M Y H:i:s', 'Asia/Jakarta') }}</td>
                <td class="text-right">@currency(json_decode($payment->paidAmount)->value)</td>
                <td>
                  @if ($payment->paymentFlagStatus === '00')
                  <span class="text-success font-weight-bold">BERHASIL</span>
                  @else
                  <span class="text-danger font-weight-bold">GAGAL</span>
                  @endif
              </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </x-containers.card>
    </div>
  </div>
</x-containers.container>
@endsection


@section('javascript')

@endsection