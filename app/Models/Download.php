<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    protected $primaryKey = 'url';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['url'];

    protected $attributes = [
        'hostName' => '',
        'fileName' => '',
        'fileSize' => '',
        'target' => '',
        'progress' => 0,
        'state' => 'pending', // @todo access static value
    ];
}
