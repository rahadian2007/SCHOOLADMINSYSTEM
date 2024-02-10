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
                                                <div class="d-flex">
                                                @if ($enableButtons['read'] == 1)
                                                    <a href="{{ route('resource.show', [ 'table' => $form->id, 'resource' => $data['id'] ] ) }}"
                                                        class="btn btn-primary mr-2"
                                                    >
                                                        Detil
                                                    </a>
                                                @endif

                                                @if ($enableButtons['edit'] == 1)
                                                    <a href="{{ route('resource.edit', [ 'table' => $form->id, 'resource' => $data['id'] ] ) }}"
                                                        class="btn btn-primary mr-2"
                                                    >
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
                                                        <button class="btn btn-danger">Hapus</button>
                                                    </form>
                                                @endif
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