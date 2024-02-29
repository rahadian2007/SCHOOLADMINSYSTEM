@extends('dashboard.base')

@php
$periodOpts = [
    [
        'value' => '',
        'label' => 'SEMUA',
    ],
    [
        'value' => 'today',
        'label' => 'HARI INI',
    ],
    [
        'value' => 'last-7-days',
        'label' => '7 HARI TERAKHIR',
    ],
    [
        'value' => 'last-30-days',
        'label' => '30 HARI TERAKHIR',
    ],
];
@endphp

@section('content')
<div class="container-fluid">
  <div class="fade-in">
    <div class="row">
    <div class="col-6">
        <div class="d-flex">
          <div class="card text-white bg-info mr-3">
            <div class="card-body d-flex">
              <div>
                <div class="text-value-lg">@numeric($usersCount)</div>
                <div>Jumlah Siswa</div>
              </div>
              <img src="/svg/illustration-users.svg" width="128" />
            </div>
          </div>
          <div class="card text-white bg-info">
            <div class="card-body d-flex">
              <div>
                <div class="text-value-lg">@numeric($vaCount)</div>
                <div>Jumlah Virtual Account</div>
              </div>
              <img src="/svg/illustration-profile.svg" width="128" />
            </div>
          </div>
        </div>
      </div>
      <div class="col-6">
        <x-containers.card>
          <x-slot name="title">
            SUMMARY PEMBAYARAN
          </x-slot>
          <x-slot name="filters">
            <div>
                <x-forms.select placeholder="SEMUA PERIODE" id="filter-period">
                    @foreach($periodOpts as $option)
                    <option value="{{ $option['value'] }}" {{ request('period') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </x-forms.select>
            </div>
          </x-slot>
          <div class="row">
            <div class="col-6">
              <div class="card border border-light text-success shadow">
                <div class="card-body text-right">
                  <div class="text-value-lg">@currency($totalSuccessPayment)</div>
                  <div>Total Pembayaran Berhasil</div>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="card border border-light">
                <div class="card-body text-right">
                  <div class="text-value-lg">@currency($totalBill)</div>
                  <div>Total Tagihan</div>
                </div>
              </div>
            </div>
          </div>
        </x-containers.card>
      </div>
    </div>
  </div>
</div>

@endsection

@section('javascript')
    <script src="{{ asset('js/Chart.min.js') }}"></script>
    <script src="{{ asset('js/coreui-chartjs.bundle.js') }}"></script>
    <!-- <script src="{{ asset('js/main.js') }}" defer></script> -->
    <script src="{{ asset('js/filters/payment.js') }}"></script>
@endsection
