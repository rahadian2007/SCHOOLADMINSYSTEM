@extends('dashboard.base')

@section('css')

@endsection

@section('content')
<x-containers.container>
    <div class="row">
        <div class="col-md-12">
            <x-containers.card>
                <x-slot name="title">Form Produk</x-slot>
                @if ($product->id)
                    <form method="POST" action="{{ route('products.update', [$product->id]) }}" enctype="multipart/form-data">
                        @method('put')
                @else
                    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                @endif
                    @csrf
                    <div class="form-group">
                        <label for="name">Nama Produk</label>
                        <input type="text" name="name" value="{{ $product->name ?? old('name') }}" class="form-control" placeholder="Nama produk" />
                    </div>
                    <div class="form-group">
                        <label for="name">Gambar Produk</label>
                        <input type="file" name="img" value="{{ old('feat_img_url') }}" class="form-control" />
                        @if ($product->featImg)
                        <img src="/public/{{ $product->featImg->id }}/{{ $product->featImg->file_name }}" width="240" class="my-4" />
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="base_price">Harga Modal</label>
                                <input type="text" name="base_price" value="{{ $product->base_price ?? old('base_price') }}" class="form-control" placeholder="Harga modal" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="selling_price">Harga Jual</label>
                                <input type="text" name="selling_price" value="{{ $product->selling_price ?? old('selling_price') }}" class="form-control" placeholder="Harga jual" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="commission">Komisi</label>
                                <input type="text" name="commission" value="{{ $product->commission ?? old('commission') }}" class="form-control" placeholder="Komisi" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="discount">Diskon (Rp)</label>
                                <input type="text" name="discount" value="{{ $product->discount ?? old('discount') }}" class="form-control" placeholder="Diskon (Rp)" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="commission">Kategori</label>
                                {!!
                                    Form::select(
                                        'product_category_id',
                                        $productCategories,
                                        $product->category ? $product->category->id : null,
                                        [
                                            'class' => 'form-control mb-2',
                                        ]
                                    )
                                !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="discount">Vendor Penjual</label>
                                {!!
                                    Form::select(
                                        'product_vendor_id',
                                        $productVendors,
                                        $product->vendor ? $product->vendor->id : null,
                                        [
                                            'class' => 'form-control mb-2',
                                        ]
                                    )
                                !!}
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                    <x-forms.button href="{{ url()->previous() }}" preset="default">
                        {{ __('Back') }}
                    </x-forms.button>
                </form>
            </x-containers.card>
        </div>
    </div>
</x-containers.container>
@endsection

@section('javascript')
@endsection