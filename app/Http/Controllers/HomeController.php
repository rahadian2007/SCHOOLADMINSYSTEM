<?php

namespace App\Http\Controllers;

use App\Helpers\BcaHelper;
use Illuminate\Support\Facades\Auth;

class HomeController extends PaymentController
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $response = BcaHelper::getTransferVaStatus();

        return response()->json($response);
    }
}
