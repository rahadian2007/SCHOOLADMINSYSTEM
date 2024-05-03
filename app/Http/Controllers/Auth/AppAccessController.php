<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class AppAccessController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:api', ['except' => ['login']]);
  }

  public function login()
  {
    try {
      $credentials = request(['email', 'password']);
      $token = auth('api')->attempt($credentials);
      
      if (!$token) {
        return response()->json(['error' => 'Unauthorized'], 401);
      }
  
      $user = auth('api')->user();
      $isAuthorized = str_contains($user->menuroles, 'admin')
          || str_contains($user->menuroles, 'cashier');
          
      if ($isAuthorized) {
        return $this->respondWithToken($token);
      } else {
        throw new \Exception('Permission denied');
      }
    } catch (\Exception $e) {
      Log::error($e);
      return response()->json(['error' => 'Permission denied'], 403);
    }
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