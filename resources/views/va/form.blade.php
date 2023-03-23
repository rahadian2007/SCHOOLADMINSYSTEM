@extends('dashboard.base')

@section('css')

@endsection

@section('content')
<x-containers.container size="6">
    <x-containers.card>
        <x-slot name="title">Form Tambah VA</x-slot>
        <form method="POST" action="{{ route('bread.store') }}">
            @csrf
            <div class="form-group">
                <label for="user">Nama Orang Tua</label>
                {!! Form::select('user', $userOptions, null, ['class' => 'form-control mb-2']) !!}
            </div>
            <div class="form-group">
                <label for="user">Nomor Virtual Account</label>
                <input type="text" value="" readonly class="form-control" />
            </div>
            <div class="form-group">
                <label for="user">Nominal Transaksi</label>
                <input type="number" value="" class="form-control" />
            </div>
            <button type="submit" class="btn btn-primary">
                Save
            </button>
            <x-forms.button href="{{ route('va.index') }}" preset="default">
                Back
            </x-forms.button>
        </form>
    </x-containers.card>
</x-containers.container>

@endsection

@section('javascript')


@endsection