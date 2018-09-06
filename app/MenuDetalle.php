<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuDetalle extends Model
{
    use SoftDeletes;
    public $table = "menu_detalle";
    protected $dates = ['deleted_at'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','menu_id', 'product_id', 'weight_1_3', 'weight_4_5'
    ];
}
