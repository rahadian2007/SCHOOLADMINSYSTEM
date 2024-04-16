<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'total_order_value', 'total_paid', 'status', 'payment_method_id'
    ];

    public function paymentMethod()
    {
        return $this->hasOne('\App\Models\PaymentMethod', 'id', 'payment_method_id');
    }

    public function orderItems()
    {
        return $this->hasMany('\App\Models\OrderItem', 'order_id', 'id');
    }
}
