<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'product_id', 'qty', 'subtotal', 'discount_percent',
        'discount_nominal', 'commission_percent', 'commission_nominal',
        'alt_name', 'alt_unit_price',
    ];

    public function product()
    {
        return $this->hasOne('\App\Models\Product', 'id', 'product_id');
    }

    public function order()
    {
        return $this->belongsTo('\App\Models\Order', 'order_id', 'id');
    }

}
