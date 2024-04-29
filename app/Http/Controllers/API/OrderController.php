<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\Settings;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class OrderController extends ApiController
{

  public function index()
  {
    $count = Order::count();
    $data = Order::with(['paymentMethod', 'orderItems', 'orderItems.product'])
      ->whereNull('deleted_at')
      ->limit(8)
      ->get();
    return $this->constructResponse($count, $data);
  }
  
  public function store()
  {
    try {
      Order::create(request()->except(['order_items']));
      $generalCommissionPercent = Settings::where('key', 'commission_percent')->first();
      $orderItems = [];

      foreach(request()->get('order_items') as $item) {
        if (!$item['commission_percent'] && !$item['commission_nominal']) {
          $item['commission_percent'] = $generalCommissionPercent->value;
        }
        $orderItems[] = $item;
      }
      OrderItem::insert($orderItems);
      
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
  
  public function destroy()
  {
    try {
      $order = Order::find(request('id'));
      $order->update([
        'deleted_at' => Carbon::now(),
      ]);
      
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