<?php

namespace App\Http\Controllers;

use App\Helpers\BcaHelper;

class PaymentController extends Controller
{
    public function __construct()
    {
        BcaHelper::evalAccessToken();
    }
}
