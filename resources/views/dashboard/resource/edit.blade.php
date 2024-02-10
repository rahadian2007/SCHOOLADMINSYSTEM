@extends('dashboard.base')

@section('content')

<x-containers.container>
    <x-containers.card>
        <x-slot name="title">Ubah {{ $form->name }}</x-slot>
        <form method="POST"
            action="{{
                route('resource.update', [
                    'table' => $form->id,
                    'resource' => $id,
                ])
            }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')
            @include('dashboard.resource.form-fields')
            @include('dashboard.resource.form-footer-buttons')
        </form>
    </x-containers.card>
</x-containers.container>

@endsection