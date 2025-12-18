<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class personas extends Model
{
    
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'ppersonadoc';
    protected $table = 'personas';


    protected $casts = [
        'reportjsonprocesado' => 'array'
    ];
    
}
