<?php

namespace App\Http\Controllers;

use App\UnidadServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnidadServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('templates/unidadServicio');
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
            $data             = (new UnidadServicio)->fill($request->all());
            $data->created_at = date('Y-m-d H:i:s');
            if ($data->save()) {
                $this->AddToLog('Unidad de servicio creada (id :'.$data->id.')');
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
     * @param  \App\UnidadServicio  $UnidadServicio
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data           = UnidadServicio::findOrFail($id);
            $data->update($request->all());
            $this->AddToLog('Unidad de servicio editada (id :'.$data->id.')');
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
            $data = UnidadServicio::findOrFail($id);
            if ($data->delete()) {
                $this->AddToLog('Unidad de servicio eliminada (id :'.$data->id.')');
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
        $data             = UnidadServicio::withTrashed()->findOrFail($id);
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
        $data = UnidadServicio::join('clientes AS b', 'unidad_servicio.cliente_id', 'b.id')
        ->join('admin_table AS c', 'unidad_servicio.tipo_unidad_servicio_id', 'c.id')
        ->join(DB::raw("(SELECT
                            a.unidad_servicio_id,
                            a.grupo_edad_id,
                            b.`name`,
                            a.coverage
                        FROM
                            pivot_unidad_servicio_edad AS a
                        INNER JOIN admin_table AS b ON a.grupo_edad_id = b.id
                        WHERE
                            a.grupo_edad_id = 24
                    ) AS d"), 'unidad_servicio.id', 'd.unidad_servicio_id')
        ->join(DB::raw("(SELECT
                            a.unidad_servicio_id,
                            a.grupo_edad_id,
                            b.`name`,
                            a.coverage
                        FROM
                            pivot_unidad_servicio_edad AS a
                        INNER JOIN admin_table AS b ON a.grupo_edad_id = b.id
                        WHERE
                            a.grupo_edad_id = 25
                    ) AS e"), 'unidad_servicio.id', 'e.unidad_servicio_id')
        ->select('unidad_servicio.id', 'unidad_servicio.cliente_id', 'unidad_servicio.tipo_unidad_servicio_id', 'unidad_servicio.name', 'unidad_servicio.address', 'unidad_servicio.phone', 'b.name AS cliente', 'c.name AS tipo_us', 'd.coverage AS coverage_1_3','e.coverage AS coverage_4_5')
        ->get();
        return \DataTables::of($data)->make(true);
    }

    public function getDataSelect()
    {
        $data = DB::table('clientes')
            ->select('id', 'name', 'address', 'phone')
            ->get();
        $answer = array(
            'data' => $data,
        );
        return $answer;
    }

    public function getDataByCliente($cliente_id, $tipo_us)
    {
        $data = DB::table('unidad_servicio')
            ->select('id', 'name')
            ->where([
                ['cliente_id', $cliente_id],
                ['tipo_unidad_servicio_id', $tipo_us]
            ])
            ->get();
        $answer = array(
            'data' => $data,
        );
        return $answer;
    }

    public function getGrupoEdadByUs($us_id)
    {
        $data = DB::table('pivot_unidad_servicio_edad AS a')
            ->join('admin_table AS b', 'a.grupo_edad_id', 'b.id')
            ->select('a.id', 'a.unidad_servicio_id', 'a.grupo_edad_id', 'a.coverage', 'b.name AS grupo_edad')
            ->where('a.unidad_servicio_id', $us_id)
            ->get();
        $answer = array(
            'data' => $data,
        );
        return $answer;
    }

    public function addGrupoEtareo(Request $request)
    {
        try {
            /* INSERCION DE TABLA PIVOT GUIA_WRH_PIVOT */
            $id = DB::table('pivot_unidad_servicio_edad')->insertGetId(
                [
                    /* VALORES POR DEFECTO AL CREAR EL DOCUMENTO INICIAL */
                    'unidad_servicio_id'=> $request->unidad_servicio_id,
                    'grupo_edad_id'     => $request->age_group_id,
                    'coverage'          => $request->coverage
                ]);
            $this->AddToLog('Grupo edad agregado a unidad de servicio (us_id :'.$request->unidad_servicio_id.') grupo edad id (us_id :'.$request->age_group_id.') id registro creado (us_id :'.$request->id.')');
            $answer = array(
                "datos"  => $request->all(),
                "code"   => 200,
                "status" => 200,
            );
            return $answer;
        } catch (Exception $e) {
            $answer = array(
                "error" => $e,
                "code"  => 600,
            );
            return $answer;
        }
    }

    public function deleteGrupoEdad($id)
    {
        try {
            DB::table('pivot_unidad_servicio_edad')->where('id', $id)->delete();
            
            $this->AddToLog('Grupo edad eliminado de unidad de servicio (id :'.$id.')');
            $answer = array(
                "code" => 200,
            );
            
        } catch (Exception $e) {
            return $e;
        }
    }

    public function updateCoverage(Request $request)
    {
        try {
            DB::table('pivot_unidad_servicio_edad')
            ->where('id', $request->pk)
            ->update(['coverage' => $request->value]);
            $this->AddToLog('Grupo edad editado de unidad de servicio (id :'.$request->pk.')');
            $answer = array(
                "datos"  => $request->all(),
                "code"   => 200,
                "status" => 200,
            );
            return $answer;
        } catch (Exception $e) {
            $answer = array(
                "error" => $e,
                "code"  => 600,
            );
            return $answer;
        }
    }

}
