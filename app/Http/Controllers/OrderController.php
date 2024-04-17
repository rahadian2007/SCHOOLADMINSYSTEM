<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVendor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $vendorIdFilter = request('vendor');

        $orderItemsQuery = OrderItem::query();
        
        if ($vendorIdFilter) {
            $orderItemsQuery = $orderItemsQuery->whereHas('product', function ($q1) use ($vendorIdFilter) {
                return $q1->whereHas('vendor', function ($q2) use ($vendorIdFilter) {
                    return $q2->where('id', $vendorIdFilter);
                });
            });
        }
        
        $orderItems = $orderItemsQuery->paginate(10);

        $salesToday = $orderItemsQuery
            ->whereHas('order', function ($q) {
                return $q->whereDate('created_at', Carbon::today());
            })
            ->sum('subtotal');

        $orderItemsCommissions = $orderItemsQuery->get();
        $commissionsToday = 0;

        foreach ($orderItemsCommissions as $item) {
            $commissionPercent = (($item->commission_percent ?? 0) / 100) * $item->subtotal;
            $commissionNominal = $item->commission_nominal * $item->qty;
            $commissionsToday += $commissionPercent + $commissionNominal;
        }

        $vendors = ProductVendor::pluck('name', 'id');

        return view(
            'orders.index',
            compact('orderItems', 'salesToday', 'commissionsToday', 'vendors')
        );
    }

    public function destroy($id)
    {
        try {
            $order = Order::find($id);
            $order->orderItems()->delete($id);
            $order->delete($id);

            return redirect()
                ->back()
                ->with('message', 'Pesanan berhasil dihapus');
        } catch (\Exception $error) {
            Log::error($error);
        }

    }
}
