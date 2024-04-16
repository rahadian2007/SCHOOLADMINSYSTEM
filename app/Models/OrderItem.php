<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'product_id', 'qty', 'subtotal', 'discount', 'alt_name', 'alt_unit_price'
    ];

    public function product()
    {
        return $this->hasOne('\App\Models\Product', 'id', 'product_id');
    }
}
