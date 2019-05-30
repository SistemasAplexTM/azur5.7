<?php

namespace App\Http\Controllers;

use App\AdminTable;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use JavaScript;

class AdminTableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($table)
    {
        JavaScript::put([
            'table_name' => $table,
        ]);
        $table = str_replace('_', ' ', ucwords($table));
        return view('templates/adminTable')->with('table', $table);
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
            $this->validate($request, [
                'name' => 'required',
            ]);
            $data              = new AdminTable;
            $data->name        = $request->name;
            $data->description = $request->description;
            $data->table_name  = $table  = str_replace(' ', '_', strtolower($request->table));
            $data->save();
            $this->AddToLog('Registro de tabla admin creada (id :'.$data->id.')');
            $answer = array(
                "code" => 200,
            );
            return $answer;
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $type, $id)
    {
        // echo $type . ' idd '.$id;
        // exit();
        try {
            $data = AdminTable::findOrFail($id);
            $data->update($request->all());
            $this->AddToLog('Registro de tabla admin editada (id :'.$data->id.')');
            $answer = array(
                "code" => 200,
            );
            return $answer;
        } catch (Exception $e) {
            return $e;
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
            $data = AdminTable::findOrFail($id);
            if ($data->delete()) {
                $this->AddToLog('Registro de tabla admin eliminada (id :'.$data->id.')');
                $answer = array(
                    "code" => 200,
                );
            }
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getAll($table)
    {
        $data = AdminTable::where('table_name', $table)->get();
        return DataTables::of($data)->make(true);
    }
    
    public function getDataSelect($table)
    {
        $data   = AdminTable::select('id','name', 'description')
        ->where('table_name', $table)->get();
        return array(
            "code" => 200,
            "data" => $data
        );
    }
}
