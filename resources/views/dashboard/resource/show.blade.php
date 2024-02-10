@extends('dashboard.base')

@section('content')

<x-containers.container>
    <x-containers.card>
        <x-slot name="title">Detil {{ $form->name }}</x-slot>
        <table class="table">
            <tbody>
                @foreach($columns as $column)
                    <tr>
                        <td class="font-weight-bold">
                            {{ $column['name'] }}
                        </td>
                        <td>
                            @if ($column['type'] == 'default')
                                {{ $column['value'] }}
                            @elseif ($column['type'] == 'file')
                                <a href="{{ $column['value'] }}"
                                  class="btn btn-primary"
                                  target="_blank"
                                >
                                  Open file
                                </a>
                            @elseif ($column['type'] == 'image')
                                <img src="{{ $column['value'] }}"
                                  class="img-thumbnail"
                                  style="max-width:200px;"
                                />
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <a 
            href="{{ route('resource.index', $form->id) }}"
            class="btn btn-default"
        >
            Kembali
        </a>
    </x-containers.card>
</x-containers.container>

@endsection
