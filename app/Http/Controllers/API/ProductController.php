<?php

namespace App\Http\Controllers\API;

use App\Models\Product;

class ProductController extends ApiController {

  public function __construct()
  {
    $this->middleware('api');
  }

  public function index()
  {
    $productCount = Product::count();
    $products = Product::with('featImg:id,conversions_disk,file_name')->get();
    return $this->constructResponse($productCount, $products);
  }
}