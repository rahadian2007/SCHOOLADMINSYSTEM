<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VirtualAccountController extends Controller
{
    public function index()
    {
        $vas = [];
        $data = compact('vas');
        return view('va.index', $data);
    }

    public function create()
    {
        return view('va.form');
    }
}
