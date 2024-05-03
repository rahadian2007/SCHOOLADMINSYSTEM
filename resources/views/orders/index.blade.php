@extends('dashboard.base')
@section('content')
    <x-containers.container>
        @include('orders.order-data', [
            'commissionsToday' => $commissionsToday,
            'salesToday' => $salesToday,
        ])
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