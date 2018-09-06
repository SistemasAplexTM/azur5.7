<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoDetalle extends Model
{
    use SoftDeletes;
    public $table = "documento_detalle";
    protected $dates = ['deleted_at'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'documento_id',
        'products_id',
        'transaccion', 
        'cantdiad',
        'cantdiad_final',
        'unidad_medida_real',
        'cantidad_unit',
        'unidad_medida',
        'costo',
        'edad_id',
        'coverage',
        'observacion'
    ];
}
