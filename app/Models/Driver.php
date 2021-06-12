<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $primaryKey = 'name';
    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $attributes = [
        'login' => '',
        'password' => '',
        'cookie' => '',
    ];
}
