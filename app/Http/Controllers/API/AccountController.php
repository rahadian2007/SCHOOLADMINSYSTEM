<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class AccountController extends Controller
{

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('api');
  }

  public function profile()
  {
    $user = auth('api')->user();
    return response()->json($user);
  }
}