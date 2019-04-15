<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\AdminTable;

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
             foreach ($request->presentaciones_cdi as $key => $value_cdi) {
              DB::table('pivot_producto_presentacion')
              ->insert(['producto_id' => $data->id, 'tipo_uds_id' => 1, 'presentacion_id' => $value_cdi]);
             }
             foreach ($request->presentaciones_hcb as $key => $value_hcb) {
              DB::table('pivot_producto_presentacion')
              ->insert(['producto_id' => $data->id, 'tipo_uds_id' => 2, 'presentacion_id' => $value_hcb]);
             }
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
            DB::table('pivot_producto_presentacion')->where('producto_id', $id)->delete();
            foreach ($request->presentaciones_cdi as $key => $value_cdi) {
             DB::table('pivot_producto_presentacion')
             ->insert(['producto_id' => $id, 'tipo_uds_id' => 1, 'presentacion_id' => $value_cdi]);
            }
            foreach ($request->presentaciones_hcb as $key => $value_hcb) {
             DB::table('pivot_producto_presentacion')
             ->insert(['producto_id' => $id, 'tipo_uds_id' => 2, 'presentacion_id' => $value_hcb]);
            }
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
    public function getAll($category)
    {
        $data = Product::leftJoin('admin_table AS b', 'products.unidad_medida_id', 'b.id')
        ->join('admin_table AS c', 'products.tipo_producto_id', 'c.id')
        ->select('products.id', 'products.name', 'products.description', 'b.id AS unidad_medida_id', 'b.name AS unidad_medida', 'b.description AS abreviatura', 'products.tipo_producto_id', 'products.category_id', 'c.name AS tipo_producto', 'products.conversion')
        ->where('products.category_id', $category)
        ->get();
        return \DataTables::of($data)->make(true);
    }

    public function getDataSelect()
    {
        $data = DB::table('products as a')
            ->join('admin_table as b', 'a.unidad_medida_id', 'b.id')
            ->select('a.id', 'a.name', 'a.description', 'b.description as unidad_medida')
            ->get();
        $answer = array(
            'data' => $data,
        );
        return $answer;
    }

    public function getPresentaciones()
    {
     return AdminTable::where('table_name', 'presentacion')->get();
    }

    public function presentacionSeleccionada($id)
    {
     $data = DB::table('pivot_producto_presentacion as a')
     ->select('a.presentacion_id', 'a.tipo_uds_id')
     ->where('a.producto_id', $id)
     ->get();
     $cdi = [];
     $hcb = [];
     foreach ($data as $key => $value) {
      if ($value->tipo_uds_id == 1) {
       $cdi[] = $value->presentacion_id;
      }else{
       $hcb[] = $value->presentacion_id;
      }
     }
     return ['cdi' => $cdi, 'hcb' => $hcb];
    }

}
