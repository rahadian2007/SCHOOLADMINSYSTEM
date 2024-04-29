<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_vendor_id', 'status', 'settlement_revenue', 'start_date',
        'end_date', 'notes', 'settlement_commission', 
    ];

    public function vendor()
    {
        return $this->belongsTo('\App\Models\ProductVendor', 'product_vendor_id', 'id');
    }
}
