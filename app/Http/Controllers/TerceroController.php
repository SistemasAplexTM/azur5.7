<?php

namespace App\Http\Controllers;

use App\AdminTable;
use App\Tercero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TerceroController extends Controller
{

    /**
     * Fetch product types from AdminTable where table_name is 'tipo_producto'.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductTypes()
    {
        $productTypes = AdminTable::where('table_name', 'tipo_producto')->get();
        return $productTypes;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productTypes = $this->getProductTypes();
        return view('templates/tercero', compact('productTypes'));
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
        // Creamos una instancia del modelo Tercero y llenamos con los datos excepto los tipos de producto
        $data = new Tercero;
        $data->fill($request->except('product_types'));
        $data->created_at = date('Y-m-d H:i:s');
        
        if ($data->save()) {
            // Extraemos los tipos de producto enviados
            $tiposProducto = $request->input('product_types', []);
            
            // Si llegan como string JSON, lo decodificamos
            if (is_string($tiposProducto)) {
                $tiposProducto = json_decode($tiposProducto, true);
            }
            
            // Extraemos solo los IDs (suponiendo que se envían como array de objetos)
            $tiposProductoIDs = array_map(function($tipo) {
                return is_array($tipo) ? $tipo['id'] : $tipo->id;
            }, $tiposProducto);
            
            // Sincronizamos la relación many-to-many en la tabla pivote
            $data->tiposProducto()->sync($tiposProductoIDs);
            
            $this->AddToLog('Tercero creado (id :' . $data->id . ')');
            $answer = [
                "datos"  => $request->all(),
                "code"   => 200,
                "status" => 200,
            ];
        } else {
            $answer = [
                "error"  => 'Error al intentar guardar el registro.',
                "code"   => 600,
                "status" => 500,
            ];
        }
        return $answer;
    } catch (\Exception $e) {
        $answer = [
            "error" => $e->getMessage(),
            "code"  => 600,
        ];
        return $answer;
    }
}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Tercero  $Tercero
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data           = Tercero::findOrFail($id);
            $data->update($request->except('product_types'));

            // Obtener los datos enviados
            $tiposProducto = $request->input('product_types', []);

            // Si el campo viene como string JSON, decodificarlo
            if (is_string($tiposProducto)) {
                $tiposProducto = json_decode($tiposProducto, true);
            }

            // Extraer solo los IDs
            $tiposProductoIDs = array_map(function ($tipo) {
                return is_array($tipo) ? $tipo['id'] : $tipo->id;
            }, $tiposProducto);

            // Sincronizamos la relación many-to-many en la tabla pivote
            $data->tiposProducto()->sync($tiposProductoIDs);

            $this->AddToLog('Tercero editado (id :' . $data->id . ')');
            $answer = array(
                "datos" => $request->all(),
                "code"  => 200,
            );
            return $answer;
        } catch (\Exception $e) {
            return array(
                "error" => $e->getMessage(),
                "code"  => 600,
            );
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
            $data = Tercero::findOrFail($id);
            if ($data->delete()) {
                $this->AddToLog('Tercero eliminado (id :' . $data->id . ')');
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
        $data             = Tercero::withTrashed()->findOrFail($id);
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
        $data = Tercero::with('tiposProducto')->get();
        return \DataTables::of($data)
            ->addColumn('tipos_producto', function ($tercero) {
                return $tercero->tiposProducto->map(function ($tipo) {
                    return [
                        'id' => $tipo->id,
                        'name' => $tipo->name
                    ];
                })->toArray();
            })->make(true);
    }

    public function getDataSelect()
    {
        $data = DB::table('Terceros as a')
            ->select('a.id', 'a.name', 'a.phone', 'a.email')
            ->get();
        $answer = array(
            'data' => $data,
        );
        return $answer;
    }

    public function getByProductType($produc_type_id)
    {
        $data = DB::table('tercero_tipo_producto_pivot as a')
            ->join('terceros AS b', 'b.id', 'a.tercero_id')
            ->join('admin_table AS c', 'c.id', 'a.tipo_producto_id')
            ->select('b.id', 'b.name')
            ->where([
                ['a.tipo_producto_id', $produc_type_id],
                ['a.deleted_at', null]
            ])
            ->groupBy('b.id', 'b.name')
            ->get();
        return $data;
    }
}
