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
        $orderItemsQuery = OrderItem::query();
        
        // Vendor filter
        $vendorIdFilter = request('vendor');

        if ($vendorIdFilter) {
            $orderItemsQuery = $orderItemsQuery
                ->whereHas('product', function ($q1) use ($vendorIdFilter) {
                    return $q1
                        ->whereHas('vendor', function ($q2) use ($vendorIdFilter) {
                            return $q2->where('id', $vendorIdFilter);
                        });
                });
        }

        // Order date filter
        $startDate = request('start-date');
        $endDate = request('end-date');

        if ($startDate && $endDate) {
            $orderItemsQuery = $orderItemsQuery
                ->whereHas('order', function($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                });
        } else if ($startDate && !$endDate) {
            $orderItemsQuery = $orderItemsQuery
                ->whereHas('order', function($q) use ($startDate) {
                    return $q->whereDate('created_at', $startDate);
                });
        } else if (!$startDate && $endDate) {
            $orderItemsQuery = $orderItemsQuery
                ->whereHas('order', function($q) use ($startDate) {
                    return $q->whereDate('created_at', $endDate);
                });
        }
        
        $orderItems = $orderItemsQuery->paginate(10);

        $salesToday = null;
        if ($startDate && $endDate) {
            $salesToday = $orderItemsQuery
                ->whereHas('order', function($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                })->sum('subtotal');
        } else if ($startDate && !$endDate) {
            $salesToday = $orderItemsQuery
                ->whereHas('order', function($q) use ($startDate) {
                    return $q->whereDate('created_at', $startDate);
                })->sum('subtotal');
        } else if (!$startDate && $endDate) {
            $salesToday = $orderItemsQuery
                ->whereHas('order', function($q) use ($startDate) {
                    return $q->whereDate('created_at', $endDate);
                })->sum('subtotal');
        } else {{
            $salesToday = $orderItemsQuery
                ->whereHas('order', function ($q) {
                    return $q->whereDate('created_at', Carbon::today());
                })
                ->sum('subtotal');
        }}

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
