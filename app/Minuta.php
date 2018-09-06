<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Minuta extends Model
{
    use SoftDeletes;
    public $table = "minuta";
    protected $dates = ['deleted_at'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'tipo_unidad_servicio_id',
        'clientes_id',
        'fecha_inicio', 
        'fecha_fin'
    ];
}
