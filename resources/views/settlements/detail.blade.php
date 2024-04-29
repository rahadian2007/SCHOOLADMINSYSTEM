@extends('dashboard.base')

@section('content')
<x-containers.container size="12">
  <div class="row">
    <div class="col-md-12">
      <x-containers.card>
        <x-slot name="title">Detil Settlement</x-slot>
        <div class="d-flex">
          <table class="table table-stripped ml-4">
            <tr>
              <td style="width: 180px">Nama Vendor</td>
              <td>{{$data->vendor->name}}</td>
            </tr>
            <tr>
              <td>Tanggal Awal</td>
              <td>{{$data->start_date}}</td>
            </tr>
            <tr>
              <td>Tanggal Akhir</td>
              <td>{{$data->end_date}}</td>
            </tr>
            <tr>
              <td>Settlement Revenue</td>
              <td>@currency($data->settlement_revenue)</td>
            </tr>
            <tr>
              <td>Settlement Bagi Hasil</td>
              <td>@currency($data->settlement_commission)</td>
            </tr>
            <tr>
              <td>Keterangan</td>
              <td>{{$data->notes ?? '-'}}</td>
            </tr>
          </table>
        </div>
        <x-forms.button
          href="{{ route('settlements.index') }}"
          preset="default"
        >
          {{ __('Back') }}
        </x-forms.button>
      </x-containers.card>
    </div>
  </div>
</x-containers.container>
<x-containers.container size="12">
  <x-containers.card>
    <table class="table">
      <thead>
        <tr>
          <th>Nama Produk</th>
          <th class="text-right">Jumlah Terjual</th>
          <th class="text-right">Harga Satuan</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($soldItems as $item)
        <tr>
          <td>{{$item->vendor->name}}</td>
          <td class="text-right">{{$item->qty_sold}}</td>
          <td class="text-right">@currency($item->selling_price)</td>
        </tr>
        @endforeach
      </tbody>
    </x-containers.card>
  <table>
</x-containers.container>
@endsection