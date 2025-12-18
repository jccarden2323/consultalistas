<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reportapi extends Model
{   
    protected $table = 'reportapi';
    protected $primaryKey = 'idreportdoc';
    public $timestamps = false;

    public $incrementing = false; 

    protected $fillable = [
        'idreportdoc',
        'reportjson',
        'estadojob',
        'fechareport',
        'reportjsonprocesado'
    ];
}





