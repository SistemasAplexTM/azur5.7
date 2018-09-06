<?php

namespace App\Http\Controllers;

use App\Tercero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TerceroController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('templates/tercero');
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
            $data             = (new Tercero)->fill($request->all());
            $data->created_at = date('Y-m-d H:i:s');
            if ($data->save()) {
                $this->AddToLog('Tercero creado (id :'.$data->id.')');
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
     * @param  \App\Tercero  $Tercero
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data           = Tercero::findOrFail($id);
            $data->update($request->all());
            $this->AddToLog('Tercero editado (id :'.$data->id.')');
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
            $data = Tercero::findOrFail($id);
            if ($data->delete()) {
                $this->AddToLog('Tercero eliminado (id :'.$data->id.')');
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
        $data = Tercero::get();
        return \DataTables::of($data)->make(true);
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
}
