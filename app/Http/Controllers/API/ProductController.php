<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\ProductCategory;

class ProductController extends ApiController {

  public function index()
  {
    $count = Product::count();
    $data = Product::with('featImg:id,conversions_disk,file_name')->get();
    
    return $this->constructResponse($count, $data);
  }

  public function categories()
  {
    $count = ProductCategory::count();
    $data = ProductCategory::get();

    return $this->constructResponse($count, $data);
  }
}