@extends('dashboard.base')

@section('css')

@endsection

@section('content')
<x-containers.container>
    <x-containers.card>
        <x-slot name="title">Form Tambah VA</x-slot>
        <form method="POST" action="{{ route('bread.store') }}">
            @csrf
            <input name="marker" value="createForm" type="hidden">

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