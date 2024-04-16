@extends('dashboard.base')

@section('content')
<x-containers.container size="12">
  <div class="row">
    <div class="col-md-12">
      <x-containers.card>
        <x-slot name="title">Detil Produk</x-slot>
        <div class="d-flex">
          <img src="/public/{{$product->featImg->id}}/{{$product->featImg->file_name}}" height="120" />
          <table class="table table-stripped ml-4">
            <tr>
              <td style="width: 180px">Nama Produk</td>
              <td>{{$product->name}}</td>
            </tr>
            <tr>
              <td>Harga Modal</td>
              <td>{{$product->base_price}}</td>
            </tr>
            <tr>
              <td>Harga Jual</td>
              <td>{{$product->selling_price}}</td>
            </tr>
            <tr>
              <td>Komisi</td>
              <td>{{$product->commision ?? '-'}}</td>
            </tr>
            <tr>
              <td>Diskon</td>
              <td>{{$product->discount ?? 0}}</td>
            </tr>
            <tr>
              <td>Kategori</td>
              <td>{{$product->category->name ?? '-'}}</td>
            </tr>
            <tr>
              <td>Vendor Penjual</td>
              <td>{{$product->vendor->name ?? '-'}}</td>
            </tr>
            <tr>
              <td>Stok</td>
              <td>{{$product->stock ?? '-'}}</td>
            </tr>
            <tr>
              <td>Deskripsi</td>
              <td>{{$product->description ?? '-'}}</td>
            </tr>
          </table>
        </div>
        <x-forms.button href="{{ route('products.index') }}" preset="default">{{ __('Back') }}</x-forms.button>
      </x-containers.card>
    </div>
  </div>
</x-containers.container>
@endsection


@section('javascript')

@endsection