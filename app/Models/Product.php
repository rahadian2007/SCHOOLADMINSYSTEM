<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'base_price', 'selling_price', 'discount_percent',
        'discount_nominal', 'commission_percent', 'commission_nominal',
        'feat_product_img_url', 'product_category_id', 'product_vendor_id',
        'stock', 'description',
    ];

    public function featImg()
    {
        return $this->hasOne('\App\Models\Media', 'id', 'feat_product_img_url');
    }

    public function category()
    {
        return $this->belongsTo('\App\Models\ProductCategory', 'product_category_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo('\App\Models\ProductVendor', 'product_vendor_id', 'id');
    }
}
