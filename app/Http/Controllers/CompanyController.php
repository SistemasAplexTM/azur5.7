<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('templates/company');
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
            $request->validate([
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $data = new Company;
            $data->fill($request->except('logo'));

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
                $data->logo = $logoPath;
            }

            $data->created_at = date('Y-m-d H:i:s');
            if ($data->save()) {
                $this->AddToLog('Company created (id :' . $data->id . ')');
                $answer = array(
                    "datos"  => $request->all(),
                    "code"   => 200,
                    "status" => 200,
                );
            } else {
                $answer = array(
                    "error"  => 'Error while trying to save the record.',
                    "code"   => 600,
                    "status" => 500,
                );
            }
            return $answer;
        } catch (\Exception $e) {
            $answer = array(
                "error" => $e->getMessage(),
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
            $data           = Company::findOrFail($id);
            $request->validate([
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $data = Company::findOrFail($id);
            $data->update($request->except('logo'));

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
                $data->logo = $logoPath;
            }

            $this->AddToLog('Company updated (id :' . $data->id . ')');
            $answer = array(
                "datos" => $request->all(),
                "code"  => 200,
            );
            return $answer;
        } catch (\Exception $e) {
            $answer = array(
                "error" => $e->getMessage(),
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
            $data = Company::findOrFail($id);
            if ($data->delete()) {
                $this->AddToLog('Company deleted (id :' . $data->id . ')');
                $answer = array(
                    "code" => 200,
                );
            }
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Restore a deleted record
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restaurar($id)
    {
        $data             = Company::withTrashed()->findOrFail($id);
        $data->deleted_at = null;
        $data->save();
        $answer = array(
            'code' => 200,
        );
        return $answer;
    }

    /**
     * Get all records from the table for the datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        $data = Company::get();
        return \DataTables::of($data)->make(true);
    }
}
