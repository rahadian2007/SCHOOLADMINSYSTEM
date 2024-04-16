<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;

class OrderController extends ApiController
{

  public function index()
  {
    $count = Order::count();
    $data = Order::with(['paymentMethod', 'orderItems', 'orderItems.product'])->get();
    Log::info($data);
    return $this->constructResponse($count, $data);
  }
  
  public function store()
  {
    try {
      Log::info(request()->except(['order_items']));
      Order::create(request()->except(['order_items']));
      Log::info(request()->get('order_items'));
      OrderItem::insert(request()->get('order_items'));
      
      return response()->json([
        'status' => 'success',
      ]);
    } catch (\Exception $error) {
      Log::error($error->getMessage());
      return response()->json([
        'status' => $error->getMessage(),
      ]);
    }
  }
  
}