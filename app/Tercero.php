<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tercero extends Model
{
    use SoftDeletes;
    public $table = "terceros";
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'document_nit',
        'name',
        'address',
        'phone',
        'email'
    ];

    public function tiposProducto()
    {
        return $this->belongsToMany(
            AdminTable::class,                      // Modelo relacionado
            'tercero_tipo_producto_pivot',          // Tabla pivot
            'tercero_id',                           // FK del modelo actual en la tabla pivot
            'tipo_producto_id'                      // FK del modelo relacionado en la tabla pivot
        );
    }
}
