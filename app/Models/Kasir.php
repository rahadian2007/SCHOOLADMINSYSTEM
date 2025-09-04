<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Kasir extends Authenticatable implements JWTSubject
{

    protected $table = 'kasir';

    protected $fillable = ['nama', 'password'];

    protected $hidden = ['password'];

    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}


