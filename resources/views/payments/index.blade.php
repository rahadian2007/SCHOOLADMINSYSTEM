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
            <x-slot name="addNew">
                <x-forms.button href="{{ route('payments.create') }}">
                    Tambah {{ __('Payment') }}
                </x-forms.button>
            </x-slot>
            <x-slot name="filters">
                <div class="d-flex" style="gap: 12px;">
                    <x-forms.select placeholder="Pilih Status" id="filter-status">
                        @foreach($statusOpts as $option)
                        <option value="{{ $option['value'] }}" {{ request('status') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                        @endforeach
                    </x-forms.select>
                    <x-forms.select placeholder="Pilih Periode" id="filter-period">
                        @foreach($periodOpts as $option)
                        <option value="{{ $option['value'] }}" {{ request('period') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                        @endforeach
                    </x-forms.select>
                </div>
            </x-slot>
            <table class="table table-responsive-sm table-striped">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Nomor Virtual Account</th>
                        <th>Tanggal Pembayaran</th>
                        <th class="text-right">Nominal Pembayaran</th>
                        <th>Metode Pembayaran</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td>
                            @if ($payment->va)
                            <a href="{{ route('users.show', $payment->va->user_id) }}">
                                {{ $payment->virtualAccountName }}
                            </a>
                            @else
                            {{ $payment->virtualAccountName }}
                            @endif
                        </td>
                        <td>
                            @if ($payment->va)
                            <a href="{{ route('va.show', $payment->va->id) }}">{{ $payment->virtualAccountNumber }}</a>
                            @else
                            {{ $payment->virtualAccountNumber }}
                            @endif
                        </td>
                        <td>
                            <div>{{ $payment->created_at->format('d M Y', 'Asia/Jakarta') }}</div>
                            <small>{{ $payment->created_at->format('H:i:s', 'Asia/Jakarta') }}</small>
                        </td>
                        <td class="text-right">@currency(json_decode($payment->paidAmount)->value)</td>
                        <td>
                            <div>
                                {{ $payment->paymentTypee === 'T' ? 'TRANSFER' : ($payment->paymentTypee === 'C' ? 'CASH' : 'VA') }}
                            </div>
                            @if ($payment->paymentProof)
                            <small>
                                <a href="{{ $payment->paymentProof }}" class="font-size-sm" target="_blank">Bukti Bayar</a>
                            </small>
                            @endif
                        </td>
                        <td>
                            @if ($payment->paymentFlagStatus === '00')
                            <span class="text-success font-weight-bold">BERHASIL</span>
                            @else
                            <span class="text-danger font-weight-bold">GAGAL</span>
                            @endif
                        </td>
                        <td>
                            @if ($payment->freeTexts)
                            <small class="text-sm">{{ $payment->freeTexts }}</small>
                            @endif
                            @if ($payment->accNameSource)
                            <small>
                                Dari {{ $payment->accNameSource }}
                                @if ($payment->accNumberSource)
                                ({{ $payment->accNumberSource }})
                                @endif
                            </small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @if(sizeof($payments) === 0)
                    <tr>
                        <td colspan="5">Tidak ada data</td>
                    </tr>
                    @endif
                </tbody>
            </table>
            {{ $payments->appends(request()->query())->links() }}
        </x-containers.card>
    </x-containers.container>
@endsection

@section('javascript')
<script src="{{ asset('js/filters/payment.js') }}"></script>
@endsection

