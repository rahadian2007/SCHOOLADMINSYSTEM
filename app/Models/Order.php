<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'total_order_value', 'total_paid', 'status', 'payment_method_id', 'deleted_at'
    ];

    public function paymentMethod()
    {
        return $this->hasOne('\App\Models\PaymentMethod', 'id', 'payment_method_id');
    }

    public function orderItems()
    {
        return $this->hasMany('\App\Models\OrderItem', 'order_id', 'id');
    }

    public function getDiscountAttribute()
    {
        $totalDiscount = 0;
        
        foreach ($this->orderItems as $item) {
            $percentage = (($item->discount_percent ?? 0) / 100) * $item->product->selling_price;
            $nominal = $item->discount_nominal ?? 0;
            $totalDiscount += $percentage + $nominal;
        }

        return $totalDiscount;
    }
}
