<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccount extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'number', 'is_active', 'outstanding', 'description'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
