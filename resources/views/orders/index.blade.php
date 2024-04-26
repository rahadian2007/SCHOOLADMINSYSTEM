@extends('dashboard.base')
@section('content')
    <x-containers.container>
        <div class="row">
            <div class="col-sm-12 col-lg-4">
                <div class="d-flex justify-content-center">
                    <img src="/svg/illustration-dashboard.svg" width="220" />
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card text-white bg-info">
                <div class="card-body pb-0">
                    <div class="text-value-xl">@currency($commissionsToday)</div>
                    <div>Bagi Hasil Hari Ini</div>
                </div>
                <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
                    <canvas class="chart" height="70"></canvas>
                </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card text-white bg-warning">
                <div class="card-body pb-0">
                    <div class="text-value-xl">@currency($salesToday)</div>
                    <div>Omzet Hari Ini</div>
                </div>
                <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
                    <canvas class="chart" height="70"></canvas>
                </div>
                </div>
            </div>
        </div>
        <x-containers.card>
            <x-slot name="filters">
                <div class="d-flex" style="gap: 12px;">
                    <x-forms.select placeholder="Pilih Vendor" id="filter-vendor">
                        <option value="" {{ !request('vendor') ? 'selected' : '' }}>Semua</option>
                        @foreach($vendors as $id => $name)
                        <option value="{{ $id }}" {{ request('vendor') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </x-forms.select>
                    <input id="date-picker-start" name="date-picker-start" class="form-control" placeholder="Tanggal awal" />
                    <input id="date-picker-end" name="date-picker-end" class="form-control" placeholder="Tanggal akhir" />
                    <button id="filter-reset" class="btn btn-info">Reset</button>
                </div>
            </x-slot>
            <table class="table table-responsive-sm table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Total Pembelian</th>
                        <th class="text-right">Diskon</th>
                        <th class="text-right">Komisi (%)</th>
                        <th>Kategori</th>
                        <th>Vendor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderItems as $item)
                    <tr>
                        <td>{{$item->order->created_at}}</td>
                        <td>{{$item->product->name}}</td>
                        <td class="text-right">{{$item->qty}}</td>
                        <td class="text-right">@currency($item->subtotal)</td>
                        <td class="text-right">
                            @if ($item->dicount_percent)
                                @currency($item->subtotal * ($item->dicount_percent ?? 0) / 100) ({{$item->dicount_percent}}%)
                            @else
                                @currency($item->dicount_nominal ?? 0)
                            @endif
                        </td>
                        <td class="text-right">
                            @if ($item->commission_percent)
                                @currency($item->subtotal * ($item->commission_percent ?? 0) / 100) ({{$item->commission_percent}}%)
                            @else
                                @currency($item->commission_nominal ?? 0)
                            @endif
                        </td>
                        <td>{{$item->product->category->name}}</td>
                        <td>{{$item->product->vendor->name}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $orderItems->appends(request()->query())->links() }}
        </x-containers.card>
    </x-containers.container>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('js/filters/order.js') }}"></script>
@endsection