<?php

namespace App\Http\Controllers;

use App\Models\User;

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
        $userOptions = User::pluck('name', 'id');
        return view('va.form', compact('userOptions'));
    }
}
