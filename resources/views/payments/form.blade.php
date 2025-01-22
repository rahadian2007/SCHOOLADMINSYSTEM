@extends('dashboard.base')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<x-containers.container>
    <div class="row">
        <div class="col-md-6">
            <x-containers.card>
                <x-slot name="title">Form Tambah Pembayaran</x-slot>
                <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="user">Nomor Virtual Account - Nama Siswa</label>
                        {!! Form::select(
                            'va_id',
                            $vaOptions,
                            old('va_id'),
                            [
                                'class' => 'form-control mb-2',
                                'id' => 'va_select'
                            ])
                        !!}
                    </div>
                    <div class="form-group">
                        <label for="user">Total Pembayaran (Rp)</label>
                        <input type="hidden" id="outstanding" name="outstanding" value="" />
                        <input type="number" name="total_payment" value="{{ old('total_payment') }}" class="form-control" placeholder="Jumlah yang dibayarkan" />
                    </div>
                    <div class="form-group">
                        <label for="user">Metode Pembayaran</label>
                        {!!
                            Form::select(
                                'payment_method',
                                [
                                    'T' => 'Transfer',
                                    'C' => 'Cash',
                                ],
                                null,
                                [
                                    'id' => 'va_select',
                                    'class' => 'form-control mb-2',
                                ]
                            )
                        !!}
                    </div>
                    <div class="form-group">
                        <label for="user">Nomor Rekening Sumber (opsional)</label>
                        <input type="text" name="source_account_number" value="{{ old('source_account_number') }}" class="form-control" placeholder="Nomor rekening sumber" />
                    </div>
                    <div class="form-group">
                        <label for="user">Nama Pemilik Nomor Rekening Sumber (opsional)</label>
                        <input type="text" name="source_account_name" value="{{ old('source_account_name') }}" class="form-control" placeholder="Nama pemilik nomor rekening" />
                    </div>
                    <div class="form-group">
                        <label for="user">Bukti Pembayaran</label>
                        <input type="file" name="proof" value="{{ old('proof') }}" class="form-control" />
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                    <x-forms.button href="{{ url()->previous() }}" preset="default">
                        {{ __('Back') }}
                    </x-forms.button>
                </form>
            </x-containers.card>
        </div>
        <div class="col-md-6">
            <x-containers.card>
                <x-slot name="title">Detil Virtual Account</x-slot>
                <div class="form-group">
                    <label for="user">Rincian Tagihan (Rp)</label>
                    <table id="bill-details-table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Komponen Tagihan</th>
                                <th class="text-right">Nominal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="detail-row">
                                <td colspan="2">
                                    Belum ada data tagihan
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-group text-right">
                    <label for="user">Total Tagihan (Rp)</label>
                    <span id="total-bill"></span>
                </div>
            </x-vontainers.card>
        </div>
    </div>
</x-containers.container>
@endsection

@section('javascript')
<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const billDetails = {!! $vas ? $vas : '[]' !!};
    let billDetailsDisplay = []
    const billDetailsTable = document.getElementById('bill-details-table')
    const body = billDetailsTable.querySelector('tbody')

    function renderBillDetails() {
        document.querySelectorAll('[id^=detail-row]').forEach((element) => {
            element.remove()
        })
        billDetailsDisplay.forEach(({ name, value }, index) => {
            body.insertAdjacentHTML('afterbegin', `
            <tr id="detail-row-${index}">
                <td>
                    <span>${name}</span>
                </td>
                <td class="text-right">
                    <span>${value}</span>
                </td>
            </tr>
            `)
        })
    }

    const vaSelect = document.getElementById('va_select')
    vaSelect.addEventListener('change', function(event) {
        event.preventDefault()

        if (event.target.value) {
            const selectedVa = billDetails.find(({ id }) => id === parseInt(event.target.value))
            if (selectedVa?.description) {
                const { description } = selectedVa
                const descArr = JSON.parse(description)
                if (descArr?.length) {
                    billDetailsDisplay = []
                    descArr.forEach((desc) => {
                        billDetailsDisplay.push(desc)
                    })
                    renderBillDetails()
                    document.getElementById('total-bill').innerHTML = selectedVa.outstanding
                    document.getElementById('outstanding').innerHTML = selectedVa.outstanding
                }
            }
        }
    })
    
    $(document).ready(function() {
        $('#va_select').select2()
    })
</script>
@endsection