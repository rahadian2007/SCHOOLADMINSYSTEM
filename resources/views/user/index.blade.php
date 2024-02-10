@extends('dashboard.base')

@section('content')
<x-containers.container>
  <div class="d-flex">
    <div class="card text-white bg-info">
      <div class="card-body d-flex">
        <div>
          <div class="text-value-xl">@numeric($usersCount)</div>
          <div>Jumlah {{ $pageType }}</div>
        </div>
        <img src="/svg/illustration-users.svg" width="180" />
      </div>
    </div>
  </div>
  <x-containers.card searchEnabled>
    <x-slot name="addNew">
      <x-forms.button href="{{ route('users.create') }}">
        New {{ __('Users') }}
      </x-forms.button>
    </x-slot>
    <table class="table table-responsive-sm table-striped">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>No. Telepon</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($users as $user)
        <tr>
          <td>{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td>{{ $user->detail && $user->detail->phone ? $user->detail->phone : '-' }}</td>
          <td class="d-flex">
            <div class="btn-group dropdown w-100">
              <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Opsi</button>
              <div class="dropdown-menu" x-placement="top-start" style="position: absolute; transform: translate3d(0px, -2px, 0px); top: 0px; right: 0px; will-change: transform;">
                <x-forms.button href="{{ url('/users/' . $user->id) }}" class="dropdown-item">View</x-forms.button>
                <x-forms.button href="{{ url('/users/' . $user->id . '/edit') }}" preset="warning" class="dropdown-item">Edit</x-forms.button>
                @if( $you->id !== $user->id )
                <form action="{{ route('users.destroy', $user->id ) }}" method="POST">
                  @method('DELETE')
                  @csrf
                  <button type="submit" class="dropdown-item text-danger">Delete</button>
                </form>
                @endif
              </div>
            </div>
          </td>
        </tr>
      @endforeach
      @if (sizeof($users) === 0)
      <tr>
        <td colspan="4">Data tidak tersedia</td>
      </tr>
      @endif
      </tbody>
    </table>
    {{ $users->links() }}
  </x-containers.card>
</x-containers.container>
@endsection

@section('javascript')
@endsection