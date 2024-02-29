@extends('dashboard.base')

@php
$statusOpts = [
    [
        'value' => '01',
        'label' => 'PENDING',
    ],
    [
        'value' => '00',
        'label' => 'BERHASIL',
    ],
];

$periodOpts = [
    [
        'value' => '',
        'label' => 'SEMUA',
    ],
    [
        'value' => 'today',
        'label' => 'HARI INI',
    ],
    [
        'value' => 'last-7-days',
        'label' => '7 HARI TERAKHIR',
    ],
    [
        'value' => 'last-30-days',
        'label' => '30 HARI TERAKHIR',
    ],
];
@endphp

@section('content')
    <x-containers.container>
        <x-containers.card searchEnabled>
            <table class="table table-responsive-sm table-striped">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Vendor Penjual</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            {{ $products->appends(request()->query())->links() }}
        </x-containers.card>
    </x-containers.container>
@endsection
