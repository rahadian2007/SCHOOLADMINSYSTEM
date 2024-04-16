<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class ApiController extends Controller {
  
  protected function constructResponse($count, $data)
  {
    return response()->json([
      'count' => $count,
      'data' => $data,
    ]);
  }

}