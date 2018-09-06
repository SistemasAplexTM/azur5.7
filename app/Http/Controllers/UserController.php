<?php

namespace App\Http\Controllers;

use App\User;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('templates/user');
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
            $data             = (new User)->fill($request->all());
            $data->password   = bcrypt($request->password);
            $data->created_at = date('Y-m-d H:i:s');
            if ($data->save()) {
                $this->AddToLog('Usuario creado (id :'.$data->id.')');
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data           = User::findOrFail($id);
            $data->password = bcrypt($request->password);
            $data->update($request->all());
            $this->AddToLog('Usuario editado (id :'.$data->id.')');
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
            $data = User::findOrFail($id);
            if ($data->delete()) {
                $this->AddToLog('Usuario eliminado (id :'.$data->id.')');
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
        $data             = User::withTrashed()->findOrFail($id);
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
        $data = User::select('users.id', 'users.name', 'users.email')->get();
        return \DataTables::of($data)->make(true);
    }

    public function getDataSelect($table)
    {
        $data = DB::table($table)
            ->select('id', 'descripcion as name')
            ->where([
                ['deleted_at', null],
            ])->get();
        $answer = array(
            'data' => $data,
        );
        return $answer;
    }

    public function validarUsername(Request $request)
    {
        try {
            $dataUser = DB::table('users')->select('name')->where('name', $request->name)->first();
            if (count($dataUser) > 0) {
                $answer = array(
                    "valid"   => false,
                    "message" => "El nombre de usuario ya existe en la base de datos.",
                );
            } else {
                $answer = array(
                    "valid"   => true,
                    "message" => "",
                );
            }
            return $answer;
        } catch (Exception $e) {
            return $e;
        }
    }

    public function validar(Request $request)
    {
        try {
            $dataUser = DB::table('users')->select('email')->where('email', $request->email)->first();
            if (count($dataUser) > 0) {
                $answer = array(
                    "valid"   => false,
                    "message" => "El email ya existe en la base de datos.",
                );
            } else {
                $answer = array(
                    "valid"   => true,
                    "message" => "",
                );
            }
            return $answer;
        } catch (Exception $e) {
            return $e;
        }
    }
}
