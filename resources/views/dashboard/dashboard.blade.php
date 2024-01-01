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
      <div class="col-12">
        <div class="d-flex justify-content-center">
          <img src="/svg/illustration-dashboard.svg" width="480" />
        </div>
      </div>
    </div>
    <div class="row mt-4">
        <div class="col-sm-6 col-lg-6">
          <div class="card text-white bg-primary">
            <div class="card-body pb-0">
              <div class="text-value-lg">@numeric($usersCount)</div>
              <div>Jumlah Siswa</div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
              <canvas class="chart" height="70"></canvas>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-6">
          <div class="card text-white bg-info">
            <div class="card-body pb-0">
              <div class="text-value-lg">@numeric($vaCount)</div>
              <div>Jumlah Virtual Account</div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
              <canvas class="chart" height="70"></canvas>
            </div>
          </div>
        </div>
      </div>
      <x-containers.card>
        <x-slot name="title">
          SUMMARY PEMBAYARAN
        </x-slot>
        <x-slot name="filters">
          <div class="d-flex" style="gap: 12px;">
              <x-forms.select placeholder="SEMUA PERIODE" id="filter-period">
                  @foreach($periodOpts as $option)
                  <option value="{{ $option['value'] }}" {{ request('period') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                  @endforeach
              </x-forms.select>
          </div>
        </x-slot>
        <div class="row">
          <div class="col-sm-6 col-lg-6">
            <div class="card text-white bg-success">
              <div class="card-body pb-0">
                <div class="text-value-lg">@currency($totalSuccessPayment)</div>
                <div>Total Pembayaran Berhasil</div>
              </div>
              <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
                <canvas class="chart" height="70"></canvas>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-lg-6">
            <div class="card text-white bg-warning">
              <div class="card-body pb-0">
                <div class="text-value-lg">@currency($totalBill)</div>
                <div>Total Tagihan</div>
              </div>
              <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
                <canvas class="chart" height="70"></canvas>
              </div>
            </div>
          </div>
        </div>
      </x-containers.card>
    </div>
  </div>
</div>

@endsection

@section('javascript')
    <script src="{{ asset('js/Chart.min.js') }}"></script>
    <script src="{{ asset('js/coreui-chartjs.bundle.js') }}"></script>
    <script src="{{ asset('js/main.js') }}" defer></script>
    <script src="{{ asset('js/filters/payment.js') }}"></script>
@endsection
