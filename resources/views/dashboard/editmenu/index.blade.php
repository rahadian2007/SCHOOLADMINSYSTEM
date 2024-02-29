@extends('dashboard.base')

@section('content')

<div class="container-fluid">
  <div class="fade-in">
    <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-header"><h4>Menu Elements</h4></div>
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a class="btn btn-primary mb-3" href="{{ route('menu.create') }}">
                        Add new menu element
                    </a>
                    <div class="">
                        <form action="{{ route('menu.index') }}" methos="GET">
                            <div class="d-flex">
                                <select class="form-control d-flex w-100 mr-2" name="menu">
                                    @foreach($menulist as $menu1)
                                        @if($menu1->id == $thisMenu)
                                            <option value="{{ $menu1->id }}" selected>{{ $menu1->name }}</option>
                                        @else
                                            <option value="{{ $menu1->id }}">{{ $menu1->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary w-100">Switch View</button>
                            </div>
                        </form>
                    </div>
                </div>
<?php

    function renderDropdownForMenuEdit($data, $role){
        if(array_key_exists('slug', $data) && $data['slug'] === 'dropdown'){
            echo '<tr>';
            echo '<td class="text-center">';
            if($data['hasIcon'] === true && $data['iconType'] === 'coreui'){
                echo '<i class="' . $data['icon'] . '"></i>';
            }
            echo '</td>';
            echo '<td>' . $data['slug'] . '</td>';
            echo '<td>' . $data['name'] . '</td>';
            echo '<td></td>';
            echo '<td>' . $data['sequence'] . '</td>';
            echo '<td>';
            echo '<a class="btn btn-success" href="' . route('menu.up', ['id' => $data['id']]) . '"><i class="cil-arrow-thick-top"></i></a>';
            echo '</td>';
            echo '<td>';
            echo '<a class="btn btn-success" href="' . route('menu.down', ['id' => $data['id']]) . '"><i class="cil-arrow-thick-bottom"></i></a>';
            echo '</td>';
            echo '<td>';
            echo '<div class="btn-group dropdown w-100">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Opsi</button>
                    <div class="dropdown-menu" x-placement="top-start" style="position: absolute; transform: translate3d(0px, -2px, 0px); top: 0px; left: 0px; will-change: transform;">
                        <a class="dropdown-item" href="'.route('menu.show', ['id' => $data['id']]).'">Detail</a>
                        <a class="dropdown-item" href="'.route('menu.edit', ['id' => $data['id']]).'">Edit</a>
                        <a class="dropdown-item text-danger" href="'.route('menu.delete', ['id' => $data['id']]).'">Hapus</a>
                    </div>
                </div>';
            echo '</td>';
            echo '</tr>';
            renderDropdownForMenuEdit( $data['elements'], $role );
        }else{
            for($i = 0; $i < count($data); $i++){
                if( $data[$i]['slug'] === 'link' ){
                    echo '<tr>';
                    echo '<td class="text-center">';
                    echo '<i class="cil-arrow-thick-to-right"></i>';
                    echo '</td>';
                    echo '<td>' . $data[$i]['slug'] . '</td>';
                    echo '<td>' . $data[$i]['name'] . '</td>';
                    echo '<td>' . $data[$i]['href'] . '</td>';
                    echo '<td>' . $data[$i]['sequence'] . '</td>';
                    echo '<td>';
                    echo '<a class="btn btn-success" href="' . route('menu.up', ['id' => $data[$i]['id']]) . '"><i class="cil-arrow-thick-top"></i></a>';
                    echo '</td>';
                    echo '<td>';
                    echo '<a class="btn btn-success" href="' . route('menu.down', ['id' => $data[$i]['id']]) . '"><i class="cil-arrow-thick-bottom"></i></a>';
                    echo '</td>';
                    echo '<td>';
                    echo '<div class="btn-group dropdown w-100">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Opsi</button>
                        <div class="dropdown-menu" x-placement="top-start" style="position: absolute; transform: translate3d(0px, -2px, 0px); top: 0px; left: 0px; will-change: transform;">
                            <a class="dropdown-item" href="'.route('menu.show', ['id' => $data[$i]['id']]).'">Detail</a>
                            <a class="dropdown-item" href="'.route('menu.edit', ['id' => $data[$i]['id']]).'">Edit</a>
                            <a class="dropdown-item text-danger" href="'.route('menu.delete', ['id' => $data[$i]['id']]).'">Hapus</a>
                        </div>
                    </div>';
                    echo '</td>';
                    echo '</tr>';
                }elseif( $data[$i]['slug'] === 'dropdown' ){
                    renderDropdownForMenuEdit( $data[$i], $role );
                }
            }
        }
    }

              ?>


                <table class="table table-striped table-bordered datatable">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Link</th>
                            <th>Sequence</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>

                @foreach($menuToEdit as $menuel)
                    @if($menuel['slug'] === 'link')
                        <tr>
                            <td class="text-center">
                                @if($menuel['hasIcon'] === true)
                                    @if($menuel['iconType'] === 'coreui')
                                    <i class="{{ $menuel['icon'] }}"></i> 
                                    @endif
                                @endif 
                            </td>
                            <td>
                                {{ $menuel['slug'] }}
                            </td>
                            <td>
                                {{ $menuel['name'] }}
                            </td>
                            <td>
                                {{ $menuel['href'] }}
                            </td>
                            <td>
                                {{ $menuel['sequence'] }}
                            </td>
                            <td>
                                <a class="btn btn-success" href="{{ route('menu.up', ['id' => $menuel['id']]) }}">
                                    <i class="cil-arrow-thick-top"></i> 
                                </a>
                            </td>
                            <td>
                                <a class="btn btn-success" href="{{ route('menu.down', ['id' => $menuel['id']]) }}">
                                    <i class="cil-arrow-thick-bottom"></i>  
                                </a>
                            </td>
                            <td>
                                <div class="btn-group dropdown w-100">
                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Opsi</button>
                                    <div class="dropdown-menu" x-placement="top-start" style="position: absolute; transform: translate3d(0px, -2px, 0px); top: 0px; left: 0px; will-change: transform;">
                                        <a class="dropdown-item" href="{{ route('menu.show', ['id' => $menuel['id']]) }}">Detail</a>
                                        <a class="dropdown-item" href="{{ route('menu.edit', ['id' => $menuel['id']]) }}">Edit</a>
                                        <a class="dropdown-item text-danger" href="{{ route('menu.delete', ['id' => $menuel['id']]) }}">Hapus</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @elseif($menuel['slug'] === 'dropdown')
                      <?php renderDropdownForMenuEdit($menuel, $role) ?>
                    @elseif($menuel['slug'] === 'title')
                        <tr>
                            <td class="text-center">
                                @if($menuel['hasIcon'] === true)
                                    @if($menuel['iconType'] === 'coreui')
                                        <i class="{{ $menuel['icon'] }}"></i> 
                                    @endif
                                @endif 
                            </td>
                            <td>
                                {{ $menuel['slug'] }}
                            </td>
                            <td>
                                {{ $menuel['name'] }}
                            </td>
                            <td>
                                
                            </td>
                            <td>
                                {{ $menuel['sequence'] }}
                            </td>
                            <td>
                                <a class="btn btn-success" href="{{ route('menu.up', ['id' => $menuel['id']]) }}">
                                    <i class="cil-arrow-thick-top"></i> 
                                </a>
                            </td>
                            <td>
                                <a class="btn btn-success" href="{{ route('menu.down', ['id' => $menuel['id']]) }}">
                                    <i class="cil-arrow-thick-bottom"></i>  
                                </a>
                            </td>
                            <td>
                                <div class="btn-group dropdown w-100">
                                    <button class="btn dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Opsi</button>
                                    <div class="dropdown-menu" x-placement="top-start" style="position: absolute; transform: translate3d(0px, -2px, 0px); top: 0px; left: 0px; will-change: transform;">
                                        <a class="dropdown-item" href="{{ route('menu.show', ['id' => $menuel['id']]) }}">Detail</a>
                                        <a class="dropdown-item" href="{{ route('menu.edit', ['id' => $menuel['id']]) }}">Edit</a>
                                        <a class="dropdown-item text-danger" href="{{ route('menu.delete', ['id' => $menuel['id']]) }}">Hapus</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach

                    </tbody>
                </table>

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