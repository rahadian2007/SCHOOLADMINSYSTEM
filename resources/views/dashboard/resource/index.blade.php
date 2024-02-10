@extends('dashboard.base')

@section('css')

@endsection

@section('content')

<div class="container-fluid">
  <div class="fade-in">
    <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-header"><h4>{{ $form->name }}</h4></div>
            <div class="card-body">
                @if(Session::has('message'))
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-success" role="alert">{{ Session::get('message') }}</div>
                        </div>
                    </div>
                @endif
                @if( $enableButtons['add'] == 1 )
                    <div class="row">
                        <div class="col-12">
                            <a 
                                href="{{ route('resource.create', $form->id ) }}"
                                class="btn btn-primary mb-3"
                            >
                            Tambah {{ $form->name }} Baru
                            </a>
                        </div>
                    </div>
                @endif
                <div class="row">
                    <div class="col-12">
                        <table class="table table-responsive-sm table-striped">
                            <thead>
                                <tr>
                                    @foreach($header as $head)
                                        <th>{{ $head->name }}</th>
                                    @endforeach
                                    
                                    @php
                                    $isActionColAvail = $enableButtons['read'] == 1 ||
                                        $enableButtons['edit'] == 1 ||
                                        $enableButtons['delete'] == 1
                                    @endphp

                                    @if ($isActionColAvail)
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($datas as $data)
                                    <tr>
                                        @foreach($header as $head)
                                            @if (!empty($head->relation_table))
                                                <td>{{ $data['relation_' . $head->column_name] }}</td>
                                            @else
                                                <td>{{ $data[$head->column_name] }}</td>
                                            @endif
                                        @endforeach

                                        @if ($isActionColAvail)
                                            <td>
                                                <div class="btn-group dropdown w-100">
                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Opsi</button>
                                                    <div class="dropdown-menu" x-placement="top-start" style="position: absolute; transform: translate3d(0px, -2px, 0px); top: 0px; right: 0px; will-change: transform;">
                                                        @if ($enableButtons['read'] == 1)
                                                            <a href="{{ route('resource.show', [ 'table' => $form->id, 'resource' => $data['id'] ] ) }}" class="dropdown-item">
                                                                Detil
                                                            </a>
                                                        @endif
                                                        @if ($enableButtons['edit'] == 1)
                                                            <a href="{{ route('resource.edit', [ 'table' => $form->id, 'resource' => $data['id'] ] ) }}" class="dropdown-item">
                                                                Edit
                                                            </a>
                                                        @endif
                                                        @if ($enableButtons['delete'] == 1)
                                                            <form action="{{ route('resource.destroy', [
                                                                    'table' => $form->id,
                                                                    'resource' => $data['id']
                                                                ]) }}"
                                                                method="POST"
                                                            >
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="dropdown-item">Hapus</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {!! $pagination !!}
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('javascript')

@endsection