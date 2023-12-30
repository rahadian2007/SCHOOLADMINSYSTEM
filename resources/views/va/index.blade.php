@extends('dashboard.base')

@section('content')
    <x-containers.container>
        <div class="row">
            <div class="col-sm-6 col-lg-3">
                <div class="d-flex justify-content-center">
                    <img src="/svg/illustration-payment.svg" width="220" />
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card text-white bg-info">
                <div class="card-body pb-0">
                    <div class="text-value-lg">@numeric($vaCount)</div>
                    <div>Jumlah Virtual Account</div>
                </div>
                <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
                    <canvas class="chart" height="70"></canvas>
                </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card text-white bg-warning">
                <div class="card-body pb-0">
                    <div class="text-value-lg">@currency($totalBill)</div>
                    <div>Total Tagihan</div>
                </div>
                <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
                    <canvas class="chart" height="70"></canvas>
                </div>
                </div>
            </div>
        </div>
        
        <x-containers.card searchEnabled>
            <x-slot name="addNew">
                <x-forms.button href="{{ route('va.create') }}">Tambah {{ __('Virtual Account') }}</x-forms.button>
            </x-slot>
            <table class="table table-responsive-sm table-striped">
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Virtual Account</th>
                        <th class="text-right">Tagihan Terakhir</th>
                        <th>Rincian</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vas as $va)
                    <tr>
                        <td>{{ $va->user->name }}</td>
                        <td>{{ $va->number }}</td>
                        <td class="text-right">@currency($va->outstanding)</td>
                        <td>
                            @php
                            $descriptions = $va->description ? json_decode($va->description) : null;
                            @endphp

                            @if ($descriptions && is_array($descriptions))
                            @foreach($descriptions as $item)
                            <p>
                                <span>{{ $item->name }}:</span>
                                <span>@currency($item->value)</span>
                            </p>
                            @endforeach
                            @else
                            {{ $va->description }}
                            @endif
                        </td>
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

