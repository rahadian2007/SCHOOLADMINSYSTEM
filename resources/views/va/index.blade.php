@extends('dashboard.base')

@section('content')
    <x-containers.container>
        <x-containers.card searchEnabled>
            <x-slot name="addNew">
                <x-forms.button href="{{ route('va.create') }}">Tambah {{ __('Virtual Account') }}</x-forms.button>
            </x-slot>
            <table class="table table-responsive-sm table-striped">
                <thead>
                    <tr>
                        <th>Nama Orang Tua</th>
                        <th>Virtual Account</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vas as $va)
                    <tr>
                        <td>{{ $va->user->name }}</td>
                        <td>{{ $va->number }}</td>
                        <td>{{ $va->is_active ? 'Aktif' : 'Tidak Aktif' }}</td>
                        <td class="d-flex">
                            <x-forms.button href="{{ route('va.show', $va->id) }}">View</x-forms.button>
                            <x-forms.button href="{{ route('va.edit', $va->id) }}" preset="warning" class="mx-1">Edit</x-forms.button>
                            <form action="{{ route('va.destroy', $va->id ) }}" method="POST">
                                @method('DELETE')
                                @csrf
                                <x-forms.button type="submit" preset="danger">Delete</x-forms.button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    @if(sizeof($vas) === 0)
                    <tr>
                        <td colspan="4">Tidak ada data</td>
                    </tr>
                    @endif
                </tbody>
            </table>
            {{ $vas->links() }}
        </x-containers.card>
    </x-containers.container>
@endsection

@section('javascript')

@endsection

