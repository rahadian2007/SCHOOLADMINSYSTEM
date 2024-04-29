@extends('dashboard.base')

@section('content')
<x-containers.container>
    <div class="row">
        <div class="col-md-12">
            <x-containers.card>
                <x-slot name="title">Form Settlement</x-slot>
                @if ($data->id)
                    <form method="POST" action="{{ route('settlements.update', [$data->id]) }}" enctype="multipart/form-data">
                        @method('put')
                @else
                    <form method="POST" action="{{ route('settlements.store') }}">
                @endif
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Vendor</label>
                                <x-forms.select placeholder="Pilih Vendor" id="filter-vendor" name="vendor">
                                    @foreach($vendors as $id => $name)
                                    <option value="{{ $id }}" {{ request('vendor') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="settlement-start-date">Tanggal Awal</label>
                                        <input id="settlement-start-date" name="start-date" class="form-control bg-white" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="settlement-end-date">Tanggal Akhir</label>
                                        <input id="settlement-end-date" name="end-date" class="form-control bg-white" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="settlement-db">Total Pendapatan yang Terdata</label>
                                        <input id="settlement-db" value="{{ $revenueDb }}" name="revenue-db" class="form-control text-right" readonly />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="settlement-db">Total Bagi Hasil yang Terdata</label>
                                        <input id="settlement-db" value="{{ $commissionDb }}" name="commission-db" class="form-control text-right" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="settlement-total">
                                            Settlement Total Pendapatan
                                        </label>
                                        <input
                                            placeholder="Realisasi pendapatan (Rp)"
                                            value="{{ $revenueDb }}"
                                            name="settlement-revenue"
                                            class="form-control text-right"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="settlement-total">
                                            Settlement Total Bagi Hasil
                                        </label>
                                        <input
                                            placeholder="Realisasi bagi hasil (Rp)"
                                            value="{{ $commissionDb }}"
                                            name="settlement-commission"
                                            class="form-control text-right"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="settlement-db">
                                            Keterangan
                                        </label>
                                        <textarea
                                            rows="5"
                                            name="notes"
                                            class="form-control"
                                            placeholder="Keterangan settlement"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>  
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                    <x-forms.button href="/settlements" preset="default">
                        {{ __('Back') }}
                    </x-forms.button>
                </form>
            </x-containers.card>
            <x-containers.card>
                <x-slot name="title">Produk Terjual</x-slot>
                <table class="table table-compact">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th class="text-right">Jumlah Terjual</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Pendapatan</th>
                            <th class="text-right">Bagi Hasil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($soldItems as $item)
                        <tr>
                            <td>{{$item->name}}</td>
                            <td class="text-right">{{$item->qty_sold}}</td>
                            <td class="text-right">@currency($item->selling_price)</td>
                            <td class="text-right">@currency($item->revenue)</td>
                            <td class="text-right">@currency($item->commission)</td>
                        </tr>
                        @endforeach
                        @if (sizeof($soldItems) === 0)
                        <tr>
                            <td colspan="7">
                                Tidak ada data penjualan
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </x-containers.card>
        </div>
    </div>
</x-containers.container>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('js/filters/settlement.js') }}"></script>
@endsection