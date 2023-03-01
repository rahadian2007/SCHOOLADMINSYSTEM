{{-- This file is used to store sidebar items, inside the Backpack admin panel --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

@foreach (\Backpack\MenuCRUD\app\Models\MenuItem::getTree(); as $item)
    @if (sizeof($item->children) > 0)
        <ul class="nav pb-3" href="{{$item->url()}}">
            <a class="no-underline hover:underline pb-3 px-3">{{ $item->name }}</a>
            @foreach ($item->children as $child)
            <li class="nav-item mb-3">
                <a class="no-underline hover:underline p-3 ml-2" href="{{$item->url()}}">
                    {{ $child->name }}
                </a>
            </li>
            @endforeach
        </ul>
    @else
        <a class="no-underline hover:underline p-3" href="{{$item->url()}}">
            {{ $item->name }}
        </a>
    @endif
@endforeach 
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('menu-item') }}'><i class='nav-icon la la-list'></i> <span>Menu</span></a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('payment') }}"><i class="nav-icon la la-question"></i> Payments</a></li>