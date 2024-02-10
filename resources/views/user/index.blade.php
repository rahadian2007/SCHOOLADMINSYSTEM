@extends('dashboard.base')

@section('content')
<x-containers.container>
  <div class="row">
    <div class="col-sm-6 col-lg-4">
        <div class="d-flex justify-content-center">
            <img src="/svg/illustration-users.svg" width="220" />
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
      <div class="card text-white bg-primary">
        <div class="card-body pb-0">
          <div class="text-value-xl">@numeric($usersCount)</div>
          <div>Jumlah {{ $pageType }}</div>
        </div>
        <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
          <canvas class="chart" height="70"></canvas>
        </div>
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
            <x-forms.button href="{{ url('/users/' . $user->id) }}">View</x-forms.button>
            <x-forms.button href="{{ url('/users/' . $user->id . '/edit') }}" preset="warning" class="mx-1">Edit</x-forms.button>
            @if( $you->id !== $user->id )
            <form action="{{ route('users.destroy', $user->id ) }}" method="POST">
              @method('DELETE')
              @csrf
              <button class="btn btn-block btn-danger">Delete</button>
            </form>
            @endif
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