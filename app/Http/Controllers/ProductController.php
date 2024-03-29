<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('templates/product');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $data             = (new Product)->fill($request->all());
            $data->created_at = date('Y-m-d H:i:s');
            if ($data->save()) {
                $this->AddToLog('Producto creado (id :'.$data->id.')');
                $answer = array(
                    "datos"  => $request->all(),
                    "code"   => 200,
                    "status" => 200,
                );
            } else {
                $answer = array(
                    "error"  => 'Error al intentar Eliminar el registro.',
                    "code"   => 600,
                    "status" => 500,
                );
            }
            return $answer;
        } catch (\Exception $e) {
            $answer = array(
                "error" => $e,
                "code"  => 600,
            );
            return $answer;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data           = Product::findOrFail($id);
            $data->update($request->all());
            $this->AddToLog('Producto editado (id :'.$data->id.')');
            $answer = array(
                "datos" => $request->all(),
                "code"  => 200,
            );
            return $answer;

        } catch (\Exception $e) {
            $answer = array(
                "error" => $e,
                "code"  => 600,
            );
            return $answer;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $data = Product::findOrFail($id);
            if ($data->delete()) {
                $this->AddToLog('Producto eliminado (id :'.$data->id.')');
                $answer = array(
                    "code" => 200,
                );
            }
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Restaura registro eliminado
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restaurar($id)
    {
        $data             = Product::withTrashed()->findOrFail($id);
        $data->deleted_at = null;
        $data->save();
        $answer = array(
            'code' => 200,
        );
        return $answer;
    }

    /**
     * Obtener todos los registros de la tabla para el datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        $data = Product::leftJoin('admin_table AS b', 'products.unidad_medida_id', 'b.id')
        ->join('admin_table AS c', 'products.tipo_producto_id', 'c.id')
        ->select('products.id', 'products.name', 'products.description', 'b.id AS unidad_medida_id', 'b.name AS unidad_medida', 'b.description AS abreviatura', 'products.tipo_producto_id', 'c.name AS tipo_producto', 'products.conversion')
        ->where('products.deleted_at', null)
        ->get();
        return \DataTables::of($data)->make(true);
    }

    public function getDataSelect()
    {
        $data = DB::table('products as a')
            ->join('admin_table as b', 'a.unidad_medida_id', 'b.id')
            ->select('a.id', 'a.name', 'a.description', 'b.description as unidad_medida')
            ->where('a.deleted_at', null)
            ->get();
        $answer = array(
            'data' => $data,
        );
        return $answer;
    }

    public function replaceProducts()
    {
      $data = array(
            3 => 185,
            6 => 125,
            112 => 190,
            66 => 180,
            8 => 124,
            145 => 156,
            104 => 179,
            12 => 143,
            13 => 142,
            16 => 123,
            14 => 186,
            15 => 65,
            67 => 174,
            20 => 158,
            25 => 154,
            29 => 122,
            30 => 129,
            31 => 191,
            84 => 131,
            32 => 138,
            34 => 161,
            35 => 178,
            75 => 169,
            96 => 132,
            36 => 155,
            43 => 149,
            44 => 140,
            45 => 141,
            46 => 135,
            80 => 162,
            47 => 127,
            50 => 144,
            50 => 167,
            54 => 160,
            102 => 150,
            113 => 189,
            97 => 151,
            97 => 152,
            58 => 187,
            56 => 188,
            57 => 172,
            107 => 183,
            61 => 136,
            62 => 153
            );
        foreach ($data as $key => $value) {
          echo 'key: ' . $key . ' -> ' . $value . '<br>';
          // UPDATE `menu_detalle` SET `product_id`= 1 WHERE product_id = 1
          // DB::table('menu_detalle')
          //   ->where('product_id', $value)
          //   ->update(['product_id' => $key]);
          DB::table('products')
            ->where('id', $value)
            ->update(['deleted_at' => '2019-04-17 11:00:00']);
        }
        return;
    }

}
