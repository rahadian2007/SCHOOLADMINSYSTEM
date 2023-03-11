@extends(backpack_view('blank'))
<div class="row">
    @php
        if (config('backpack.base.show_getting_started')) {
            $widgets['before_content'][] = [
                'type'        => 'view',
                'view'        => 'backpack::inc.getting_started',
            ];
        } else {
            $widgets['before_content'][] = [
                'type' => 'div',
                'class' => 'row',
                'content' => [
                    [
                        'type' => 'card',
                        'content' => [
                            'header' => 'Jumlah Pengguna',
                            'body' => \App\Models\User::count() . ' orang',
                        ],
                    ], [
                        'type' => 'card',
                        'content' => [
                            'header' => 'Jumlah Pembayaran',
                            'body' => \App\Models\Payment::count(),
                        ],
                    ]
                ],
            ];
        }
    @endphp
</div>

@section('content')
@endsection
