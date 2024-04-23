@extends('dashboard.base')

@section('content')
  <x-containers.container>
    <div class="row">
      <div class="col-md-6">
        <x-containers.card>
          <x-slot name="title">Pengaturan Kantin</x-slot>
          <form method="POST" action="{{ route('canteen.settings.update') }}">
            @csrf
            @method('PUT')
            <div class="form-group">
              <label for="name">General Commission (%)</label>
              <input type="text" name="commission_percent" value="{{ $commissionPercent ? $commissionPercent->value : old('commission_percent') }}" class="form-control" placeholder="Persentase Komisi" />
            </div>
            <input type="submit" value="Update" class="btn btn-primary" />
          </form>
        </x-containers.card>
      </div>
    </div>
  </x-containers.container>
@endsection
