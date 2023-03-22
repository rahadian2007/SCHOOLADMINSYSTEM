@extends('dashboard.base')

@section('content')
    <x-containers.container>
        <x-containers.card>
            <x-slot name="addNew">
                <x-forms.button href="{{ route('va.create') }}">Tambah {{ __('Users') }}</x-forms.button>
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
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <a href="{{ url('/users/' . $user->id) }}" class="btn btn-block btn-primary">View</a>
                            <a href="{{ url('/users/' . $user->id . '/edit') }}" class="btn btn-block btn-primary">Edit</a>
                            @if( $you->id !== $user->id )
                            <form action="{{ route('users.destroy', $user->id ) }}" method="POST">
                                @method('DELETE')
                                @csrf
                                <button class="btn btn-block btn-danger">Delete User</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </x-containers.card>
    </x-containers.container>
@endsection

@section('javascript')

@endsection

