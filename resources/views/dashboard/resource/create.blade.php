@extends('dashboard.base')

@section('content')
<x-containers.container>
    <x-containers.card>
        <x-slot name="title">Tambah {{ $form->name }}</x-slot>
        <form method="POST"
            action="{{ route('resource.store', $form->id) }}"
            enctype="multipart/form-data"
        >
            @csrf
            @include('dashboard.resource.form-fields')
            @include('dashboard.resource.form-footer-buttons')
        </form>
    </x-containers.card>
</x-containers.container>

@endsection