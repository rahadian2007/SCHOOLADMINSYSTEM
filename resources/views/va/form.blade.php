@extends('dashboard.base')

@section('css')

@endsection

@section('content')
<x-containers.container size="6">
    <x-containers.card>
        <x-slot name="title">Form Tambah VA</x-slot>
        @if ($va->id)
        <form method="POST" action="{{ route('va.update', $va->id) }}">
            @method('PUT')
        @else
        <form method="POST" action="{{ route('va.store') }}">
        @endif
            @csrf
            <div class="form-group">
                <label for="user">Nama Siswa</label>
                {!! Form::select('user_id', $userOptions, $va->user && $va->user->id ? $va->user->id : null, ['class' => 'form-control mb-2']) !!}
            </div>
            <div class="form-group">
                <label for="user">Nomor Virtual Account</label>
                <input type="text" name="number" value="{{ $va->number }}" placeholder="e.g. 123321" class="form-control" />
                <small>Harus unik dan max. 12 angka</small>
            </div>
            <div class="form-group">
                <label for="user">Jumlah Tagihan (Rp)</label>
                <input type="number" name="outstanding" value="{{ $va->outstanding }}" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="user">Deskripsi</label>
                <textarea name="description" class="form-control">{{ $va->description }}</textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <div>
                    {!! Form::radio('is_active', 1, $va->is_active) !!}
                    <label for="is_active">Aktif</label>
                </div>
                <div>
                    {!! Form::radio('is_active', 0, !$va->is_active) !!}
                    <label for="is_active">Tidak Aktif</label>
                </div>
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