<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadServicio extends Model
{
    use SoftDeletes;
    public $table = "unidad_servicio";
    protected $dates = ['deleted_at'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name', 
        'cliente_id', 
        'tipo_unidad_servicio_id', 
        'address', 
        'phone'
    ];
}
