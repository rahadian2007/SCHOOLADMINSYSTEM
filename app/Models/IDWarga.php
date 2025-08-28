<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IDWarga extends Model
{
    // Nama tabel di database
    protected $table = 'ID_Warga';

    // Primary key
    protected $primaryKey = 'ID';

    // Primary key auto-increment dan tipe integer
    public $incrementing = true;
    protected $keyType = 'int';

    // Nonaktifkan timestamps jika tabel tidak punya created_at dan updated_at
    public $timestamps = false;

    // Mass assignable fields (penting supaya create() bisa jalan)
    protected $fillable = ['Nama_Warga'];
}
