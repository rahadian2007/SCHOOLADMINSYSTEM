<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Settlement;
use App\Models\ProductVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    public function index()
    {
        $data = Settlement::paginate(10);
        
        return view('settlements.index', compact('data'));
    }

    public function show($id)
    {
        $data = Settlement::findOrFail($id);
        $vendor = $data->vendor->id;
        $startDate = $data->start_date;
        $endDate = $data->end_date;

        $soldItems = Product::select(
            'products.*',
            DB::raw('sum(order_items.qty) as qty_sold'),
            DB::raw('sum(order_items.qty * products.selling_price) as revenue'),
            DB::raw('sum((order_items.commission_percent  / 100) * order_items.qty * products.selling_price) as commission')
        )
            ->whereHas('vendor', function ($q) use ($vendor, $startDate, $endDate) {
                $q->where('id', $vendor);
            })
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.id')
            ->get();

        return view('settlements.detail', compact('data', 'soldItems'));
    }

    public function create()
    {
        $data = new Settlement();
        $vendors = ProductVendor::pluck('name', 'id');
        $soldItems = [];

        $vendor = request('vendor');
        $startDate = request('start-date');
        $endDate = request('end-date');
        
        if ($vendor && $startDate && $endDate) {
            $soldItems = Product::select(
                'products.id',
                'products.name',
                'products.selling_price',
                DB::raw('sum(order_items.qty) as qty_sold'),
                DB::raw('sum(order_items.qty * products.selling_price) as revenue'),
                DB::raw('sum((order_items.commission_percent  / 100) * order_items.qty * products.selling_price) as commission')
            )
                ->whereHas('vendor', function ($q) use ($vendor, $startDate, $endDate) {
                    $q->where('id', $vendor);
                })
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->groupBy('products.id')
                ->get();
        }

        $revenueDb = 0;
        $commissionDb = 0;

        foreach ($soldItems as $item) {
            $revenueDb += $item->revenue;
            $commissionDb += $item->commission;
        }

        return view('settlements.form', compact(
            'data', 'vendors', 'soldItems', 'revenueDb', 'commissionDb'
        ));
    }

    public function store()
    {
        try {
            $isExist = Settlement::where('product_vendor_id', request('vendor'))
                ->where('start_date', request('start-date'))
                ->where('end_date', request('end-date'))
                ->exists();

            if ($isExist) {
                throw new \Exception('Settlement sudah pernah dibuat');
            }

            $settlement = Settlement::create([
                'product_vendor_id' => request('vendor'),
                'start_date' => request('start-date'),
                'end_date' => request('end-date'),
                'settlement_revenue' => request('settlement-revenue'),
                'settlement_commission' => request('settlement-commission'),
                'notes' => request('notes'),
                'status' => 'settled',
            ]);

            return redirect('/settlements')
                ->with('message', 'Berhasil membuat settlement');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $settlement = Settlement::findOrFail($id);
            $settlement->delete();

            return redirect()
                ->back()
                ->with('message', 'Berhasil menghapus settlement');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
