<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class AppAccessController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:api', ['except' => ['login']]);
  }

  public function login()
  {
    $credentials = request(['email', 'password']);

    if (! $token = auth('api')->attempt($credentials)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    return $this->respondWithToken($token);
  }

  protected function respondWithToken($token)
  {
      return response()->json([
          'access_token' => $token,
          'token_type' => 'bearer',
          'expires_in' => auth('api')->factory()->getTTL() * 60
      ]);
  }
}