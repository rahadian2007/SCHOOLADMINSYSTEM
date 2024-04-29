@extends('dashboard.base')

@section('content')
    <x-containers.container>
        <x-containers.card>
            <x-slot name="addNew">
                <x-forms.button href="{{ route('settlements.create') }}">
                    Tambah {{ __('Settlement') }}
                </x-forms.button>
            </x-slot>
            <table class="table table-responsive-sm table-striped">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Tanggal Awal</th>
                        <th>Tanggal Akhir</th>
                        <th class="text-right">Settlement Revenue</th>
                        <th class="text-right">Settlement Bagi Hasil</th>
                        <th>Keterangan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr>
                        <td>{{ $item->vendor->name }}</td>
                        <td>{{ $item->start_date }}</td>
                        <td>{{ $item->end_date }}</td>
                        <td class="text-right">{{ $item->settlement_revenue }}</td>
                        <td class="text-right">{{ $item->settlement_commission }}</td>
                        <td>{{ $item->notes }}</td>
                        <td>
                            <div class="btn-group dropdown w-100">
                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Opsi</button>
                                <div class="dropdown-menu" x-placement="top-start" style="position: absolute; transform: translate3d(0px, -2px, 0px); top: 0px; right: 0px; will-change: transform;">
                                    <a href="{{ route('settlements.show', [ $item->id ] ) }}" class="dropdown-item">
                                        Detil
                                    </a>
                                    <form action="{{ route('settlements.destroy', [
                                            $item->id
                                        ]) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @if (sizeof($data) === 0)
                    <tr>
                        <td colspan="7">
                            Belum ada data settlement
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
            {{ $data->appends(request()->query())->links() }}
        </x-containers.card>
    </x-containers.container>
@endsection
