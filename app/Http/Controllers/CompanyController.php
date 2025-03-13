<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
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

        $dataRequest = array_merge($request->all(), ['logo' => $request->file('logo')]);
        // Definir las reglas de validación
        $rules = [
            'name'    => 'required|string|max:255',
            'nit'     => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'phone'   => 'required|string|max:50',
            'logo' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($value->getMimeType(), $allowedMimes)) {
                        $fail("The $attribute must be an image.");
                    }
                },
                'max:2048'
            ],
        ];

        // Crear el validador
        $validator = Validator::make($dataRequest, $rules);

        // Si falla la validación, devolver los errores
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'code'  => 422
            ], 422);
        }
        try {
            $data = new Company;
            $data->fill($request->except('logo'));

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');

                // Directorio donde se guardará: public/img
                $directory = public_path('img');

                // Crea el directorio si no existe
                File::makeDirectory($directory, 0755, true, true);

                // Genera un nombre único para la imagen
                $filename = uniqid() . '_' . $logo->getClientOriginalName();

                // Guarda la imagen en public/img/logos
                $logo->move($directory, $filename);

                // Guarda la ruta relativa en la base de datos (ej: img/logos/abc123.jpg)
                $data->logo = 'img/' . $filename;
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
        $rules = [
            'name'    => 'required|string|max:255',
            'nit'     => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'phone'   => 'required|string|max:50',
            'logo' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Validar solo si existe el archivo
                    if ($value && !in_array($value->getMimeType(), ['image/jpeg', 'image/png', 'image/gif'])) {
                        $fail("The $attribute must be an image.");
                    }
                },
                'max:2048'
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'code'  => 422
            ], 422);
        }

        try {
            $data = Company::findOrFail($id);
            $oldLogo = $data->logo; // Guardar referencia al logo antiguo

            // Actualizar primero los demás campos
            $data->fill($request->except('logo'));
            $data->delivery_person_info = json_encode([
                'delivery_person_name' => $request->input('delivery_person_name'),
                'delivery_person_document' => $request->input('delivery_person_document')
            ]);
            $data->updated_at = now();

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $directory = public_path('img'); // Usar mismo directorio que store

                // Generar nombre único conservando extensión
                $filename = uniqid() . '_' . $logo->getClientOriginalName();

                // Mover archivo a directorio destino
                $logo->move($directory, $filename);

                // Actualizar ruta en modelo
                $data->logo = 'img/' . $filename;

                // Eliminar logo anterior si existe
                if ($oldLogo && file_exists(public_path($oldLogo))) {
                    File::delete(public_path($oldLogo));
                }
            }

            if ($data->save()) { // Single save operation
                $this->AddToLog('Company updated (id: ' . $data->id . ')');
                return response()->json([
                    "data"   => $data,
                    "code"   => 200,
                    "status" => 200
                ]);
            }

            return response()->json([
                "error"  => 'Error saving record',
                "code"   => 600,
                "status" => 500
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
                "code"  => 600
            ], 500);
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

    public function getCompanyById($id)
    {
        $data = Company::find($id);
        if ($data) {
            return response()->json([
                'code' => 200,
                'data' => $data,
            ]);
        } else {
            return response()->json(['code' => 404, 'error' => 'Company not found'], 404);
        }
    }
}
