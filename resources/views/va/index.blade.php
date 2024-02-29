@extends('dashboard.base')

@php
$statusOpts = [
    [
        'label' => 'SEMUA',
        'value' => ''
    ],
    [
        'label' => 'LUNAS',
        'value' => 'paid'
    ],
    [
        'label' => 'BELUM LUNAS',
        'value' => 'unpaid'
    ],
];
$activeOpts = [
    [
        'label' => 'SEMUA',
        'value' => ''
    ],
    [
        'label' => 'AKTIF',
        'value' => 'active'
    ],
    [
        'label' => 'TIDAK AKTIF',
        'value' => 'inactive'
    ],
];
@endphp

@section('content')
    <x-containers.container>
        <div class="row">
            <div class="col-sm-12 col-lg-4">
                <div class="d-flex justify-content-center">
                    <img src="/svg/illustration-payment.svg" width="220" />
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card text-white bg-info">
                <div class="card-body pb-0">
                    <div class="text-value-xl">@numeric($vaCount)</div>
                    <div>Jumlah Virtual Account</div>
                </div>
                <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
                    <canvas class="chart" height="70"></canvas>
                </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card text-white bg-warning">
                <div class="card-body pb-0">
                    <div class="text-value-xl">@currency($totalBill)</div>
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
                <div class="d-flex">
                    <x-forms.button href="{{ route('va.create') }}">Tambah {{ __('Virtual Account') }}</x-forms.button>
                    <form method="POST" action="{{ route('va.export') }}">
                        @csrf
                        <x-forms.button type="submit" preset="light" class="d-flex align-items-center ml-1">
                            <i class="cil-cloud-download mr-2"></i>
                            Export (.xlsx)
                        </x-forms.button>
                    </form>
                </div>
            </x-slot>
            <x-slot name="filters">
                <div class="d-flex" style="gap: 12px;">
                    <x-forms.select placeholder="Pilih Status Pembayaran" id="filter-status-payment">
                        @foreach($statusOpts as $option)
                        <option value="{{ $option['value'] }}" {{ request('payment') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                        @endforeach
                    </x-forms.select>
                    <x-forms.select placeholder="Pilih Keaktifan" id="filter-status-active">
                        @foreach($activeOpts as $option)
                        <option value="{{ $option['value'] }}" {{ request('active') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                        @endforeach
                    </x-forms.select>
                </div>
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
                            <p class="mb-0 text-value-sm">
                                <span>{{ $item->name }}:</span>
                                <span>@currency($item->value)</span>
                            </p>
                            @endforeach
                            @else
                            {{ $va->description }}
                            @endif
                        </td>
                        <td>
                            @if ($va->is_active)
                            <span class="text-success">AKTIF</span>
                            @else
                            <span class="text-danger">TIDAK AKTIF</span>
                            @endif
                        </td>
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
                        <td colspan="6">Tidak ada data</td>
                    </tr>
                    @endif
                </tbody>
            </table>
            {{ $vas->links() }}
        </x-containers.card>
    </x-containers.container>
@endsection

@section('javascript')
<script>
    const statusFilter = document.getElementById('filter-status-payment');
    statusFilter.addEventListener('change', ({ target: { value }}) => {
        const url = new URL(window.location.href);
        if (value) {
            url.searchParams.set('payment', value);
        } else {
            url.searchParams.delete('payment');
        }
        url.searchParams.delete('page');
        window.location.href = url
    })

    const periodFilter = document.getElementById('filter-status-active');
    periodFilter.addEventListener('change', ({ target: { value }}) => {
        const url = new URL(window.location.href);
        if (value) {
            url.searchParams.set('active', value);
        } else {
            url.searchParams.delete('active');
        }
        url.searchParams.delete('page');
        window.location.href = url
    })
</script>
@endsection

