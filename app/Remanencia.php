<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Remanencia extends Model
{
    use SoftDeletes;
    public $table       = "remanencias";
    
    protected $fillable = [
        'minuta_id',
        'unidad_servicio_id',
        'products_id',
        'cantidad',
        'descripcion',
        'created_at',
    ];
}
