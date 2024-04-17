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
                                <label for="commission">Komisi (%)</label>
                                <input type="text" name="commission_percent" value="{{ $product->commission_percent ?? old('commission_percent') }}" class="form-control" placeholder="Komisi dalam persen" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="discount">Komisi (Rp)</label>
                                <input type="text" name="commission_nominal" value="{{ $product->commission_nominal ?? old('commission_nominal') }}" class="form-control" placeholder="Diskon dalam rupiah" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="commission">Diskon (%)</label>
                                <input type="text" name="discount_percent" value="{{ $product->discount_percent ?? old('discount_percent') }}" class="form-control" placeholder="Diskon dalam persen" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="discount">Diskon (Rp)</label>
                                <input type="text" name="discount_nominal" value="{{ $product->discount_nominal ?? old('discount_nominal') }}" class="form-control" placeholder="Diskon dalam rupiah" />
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
<script>
    const commissionPercent = document.getElementsByName('commission_percent')?.[0]
    const commissionNominal = document.getElementsByName('commission_nominal')?.[0]
    const discountPercent = document.getElementsByName('discount_percent')?.[0]
    const discountNominal = document.getElementsByName('discount_nominal')?.[0]
    
    const evalCommissionPercent = ({target: {value}}) => {
        const intInput = parseInt(value)
        const floatInput = parseFloat(value)
        const isValidRange = intInput >= 0 && intInput <= 100 && floatInput >= 0 && floatInput <= 100
        const isValid = isValidRange && !isNaN(intInput) && !isNaN(floatInput)

        if (isValid) {
            commissionNominal.disabled = isValid
        } else {
            commissionNominal.val = ''
            alert('Pastikan angka komisi valid')
        }
    }
    
    const evalCommissionNominal = ({target: {value}}) => {
        const intInput = parseInt(value)
        const floatInput = parseFloat(value)
        const isValidRange = intInput > 0 && floatInput > 0
        const isValid = isValidRange && !isNaN(intInput) && !isNaN(floatInput)

        if (isValid) {
            commissionPercent.disabled = isValid
        } else {
            commissionPercent.val = ''
            alert('Pastikan angka komisi valid')
        }
    }
    
    const evalDiscountPercent = ({target: {value}}) => {
        const intInput = parseInt(value)
        const floatInput = parseFloat(value)
        const isValidRange = intInput >= 0 && intInput <= 100 && floatInput >= 0 && floatInput <= 100
        const isValid = isValidRange && !isNaN(intInput) && !isNaN(floatInput)

        if (isValid) {
            discountNominal.disabled = isValid
        } else {
            discountNominal.val = ''
            alert('Pastikan angka diskon valid')
        }
    }
    
    const evalDiscountNominal = ({target: {value}}) => {
        const intInput = parseInt(value)
        const floatInput = parseFloat(value)
        const isValidRange = intInput > 0 && floatInput > 0
        const isValid = isValidRange && !isNaN(intInput) && !isNaN(floatInput)

        if (isValid) {
            discountPercent.disabled = isValid
        } else {
            discountPercent.val = ''
            alert('Pastikan angka diskon valid')
        }
    }

    commissionPercent?.addEventListener('change', evalCommissionPercent)
    commissionNominal?.addEventListener('change', evalCommissionNominal)
    discountPercent?.addEventListener('change', evalDiscountPercent)
    discountNominal?.addEventListener('change', evalDiscountNominal)

    if ({{ $product->commission_percent ? 'true' : 'false'}}) {
        evalCommissionPercent({{ $product->commission_percent }})
    }

    if ({{ $product->commission_nominal ? 'true' : 'false'}}) {
        evalCommissionNominal({{ $product->commission_nominal }})
    }

    if ({{ $product->discount_percent ? 'true' : 'false'}}) {
        evalDiscountPercent({{ $product->discount_percent }})
    }

    if ({{ $product->discount_nominal ? 'true' : 'false'}}) {
        evalDiscountNominal({{ $product->discount_nominal }})
    }
</script>
@endsection