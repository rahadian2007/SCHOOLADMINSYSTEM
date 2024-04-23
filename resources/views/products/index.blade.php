@extends('dashboard.base')

@section('content')
    <x-containers.container>
        <x-containers.card searchEnabled>
            <x-slot name="addNew">
                <x-forms.button href="{{ route('products.create') }}">
                    Tambah {{ __('Produk') }}
                </x-forms.button>
            </x-slot>
            <table class="table table-responsive-sm table-striped">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th class="text-right">Harga Modal</th>
                        <th class="text-right">Harga Jual</th>
                        <th class="text-right">Komisi (%)</th>
                        <th class="text-right">Diskon</th>
                        <th>Kategori</th>
                        <th>Vendor Penjual</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{$product->name}}</td>
                        <td class="text-right">@currency($product->base_price ?? '-')</td>
                        <td class="text-right">@currency($product->selling_price)</td>
                        <td class="text-right">{{$product->commission_percent ?? $product->commission_nominal ?? ($commissionPercent ? $commissionPercent->value . '(G)' : null) ?? '-'}}</td>
                        <td class="text-right">{{$product->discount_percent ?? $product->discount_nominal ?? '-'}}</td>
                        <td>{{$product->category->name ?? '-'}}</td>
                        <td>{{$product->vendor->name ?? '-'}}</td>
                        <td>
                            <div class="btn-group dropdown w-100">
                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Opsi</button>
                                <div class="dropdown-menu" x-placement="top-start" style="position: absolute; transform: translate3d(0px, -2px, 0px); top: 0px; right: 0px; will-change: transform;">
                                    <a href="{{ route('products.show', [ $product->id ] ) }}" class="dropdown-item">
                                        Detil
                                    </a>
                                    <a href="{{ route('products.edit', [ $product->id ] ) }}" class="dropdown-item">
                                        Edit
                                    </a>
                                    <form action="{{ route('products.destroy', [
                                            $product->id
                                        ]) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $products->appends(request()->query())->links() }}
        </x-containers.card>
    </x-containers.container>
@endsection
