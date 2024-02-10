@extends('dashboard.base')

@section('content')

<x-containers.container>
    <x-containers.card>
        <x-slot name="title">Hapus {{ $formName }}</x-slot>
        <form method="POST" action="{{ route('resource.destroy', ['table' => $table, 'resource' => $id ]) }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="marker" value="true">
            <p>Apakah Anda Yakin?</p>
            <button
                type="submit"
                class="btn btn-danger"
            >
                Hapus
            </button>
            <a 
                href="{{ route('resource.index', $table) }}"
                class="btn btn-default"
            >
                Kembali
            </a>
        </form>
    </x-containers.card>
</x-containers.container>

@endsection