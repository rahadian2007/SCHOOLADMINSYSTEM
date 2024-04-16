<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function featImg()
    {
        return $this->hasOne('\App\Models\Media', 'id', 'feat_product_img_url');
    }
}
