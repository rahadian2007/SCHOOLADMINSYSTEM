@extends('dashboard.base')

@section('css')

@endsection

@section('content')
<x-containers.container size="6">
    <x-containers.card>
        <x-slot name="title">Form Tambah VA</x-slot>
        @if ($va->id)
        <form method="POST" action="{{ route('va.update', $va->id) }}">
            @method('PUT')
        @else
        <form method="POST" action="{{ route('va.store') }}">
        @endif
            @csrf
            <div class="form-group">
                <label for="user">Nama Siswa</label>
                {!! Form::select('user_id', $userOptions, $va->user && $va->user->id ? $va->user->id : null, ['class' => 'form-control mb-2']) !!}
            </div>
            <div class="form-group">
                <label for="user">Nomor Virtual Account</label>
                <input type="text" name="number" value="{{ $va->number }}" placeholder="e.g. 123321" class="form-control" />
                <small>Harus unik dan max. 12 angka</small>
            </div>
            <div class="form-group">
                <label for="user">Rincian Tagihan (Rp)</label>
                <table id="bill-details-table">
                    <thead>
                        <tr>
                            <th>Komponen Tagihan</th>
                            <th>Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input id="detail-name" type="text" name="outstanding" class="form-control"/>
                            </td>
                            <td>
                                <input id="detail-value" type="number" name="outstanding" class="form-control"/>
                            </td>
                            <td><button id="add-bill-detail" class="btn btn-light">Tambah Rincian</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="form-group">
                <label for="user">Total Tagihan (Rp)</label>
                <input type="number" id="total-bill" name="outstanding" value="{{ $va->outstanding }}" class="form-control" readonly/>
            </div>
            <div class="form-group">
                <label>Status</label>
                <div>
                    {!! Form::radio('is_active', 1, $va->is_active) !!}
                    <label for="is_active">Aktif</label>
                </div>
                <div>
                    {!! Form::radio('is_active', 0, !$va->is_active) !!}
                    <label for="is_active">Tidak Aktif</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                Save
            </button>
            <x-forms.button href="{{ url()->previous() }}" preset="default">{{ __('Back') }}</x-forms.button>
        </form>
    </x-containers.card>
</x-containers.container>

@endsection

@section('javascript')
<script>
    const billDetails = {!! $va->description && is_array(json_decode($va->description)) ? $va->description : '[]' !!}
    const billDetailsTable = document.getElementById('bill-details-table')
    const body = billDetailsTable.querySelector('tbody')

    function renderBillDetails() {
        document.querySelectorAll('[id^=detail-row]').forEach((element) => {
            element.remove()
        })
        billDetails.forEach(({ name, value }, index) => {
            body.insertAdjacentHTML('afterbegin', `
            <tr id="detail-row-${index}">
                <td>
                    <span>${name}</span>
                    <input type="hidden" name="detail-name[]" value="${name}" />
                </td>
                <td>
                    <span>${value}</span>
                    <input type="hidden" name="detail-value[]" value="${value}" />
                </td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="removeDetail(${index})">X</button>
                </td>
            </tr>
            `)
        })
    }

    function calculateTotal() {
        document.getElementById('total-bill').value = billDetails.reduce((acc, item) => {
            return parseInt(acc) + parseInt(item.value)
        }, 0)
    }

    const addBillDetailBtn = document.getElementById('add-bill-detail')
    addBillDetailBtn.addEventListener('click', function(event) {
        event.preventDefault()

        const name = document.getElementById('detail-name').value
        const value = document.getElementById('detail-value').value

        const isDetailEmpty = !name
        if (isDetailEmpty) {
            alert('Lengkapi dahulu rincian tagihan')
            return
        }
        
        const isFieldInvalid = !value
        if (isFieldInvalid) {
            alert('Nilai tagihan tidak valid')
            return
        }
        
        billDetails.push({ name, value })
        renderBillDetails()
        document.getElementById('detail-name').value = ''
        document.getElementById('detail-value').value = ''
        calculateTotal()
    })

    function removeDetail(index) {
        billDetails.splice(index, 1)
        renderBillDetails()
        calculateTotal()
    }

    renderBillDetails()
</script>
@endsection