<?php

namespace App\Http\Controllers;

use App\Menu;
use App\MenuDetalle;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $this->assignPermissionsJavascript('menus');
        return view('templates/menus');
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
            $data             = (new Menu)->fill($request->all());
            $data->created_at = date('Y-m-d H:i:s');
            if ($data->save()) {
                $this->AddToLog('Menu creado (id :' . $data->id . ')');
                $answer = array(
                    "datos"  => $data,
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
     * @param  \App\Menu  $Menu
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data = Menu::findOrFail($id);
            $data->update($request->all());
            $this->AddToLog('Menu editado (id :' . $data->id . ')');
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
    public function destroy($id, $table = false)
    {
        try {
            if ($table == 'detail') {
                $data = MenuDetalle::findOrFail($id);
                if ($data->delete()) {
                    $this->AddToLog('Menu detalle eliminado (id :' . $data->id . ')');
                    $answer = array(
                        "code" => 200,
                    );
                }
            } else {
                $data = Menu::findOrFail($id);
                if ($data->delete()) {
                    $this->AddToLog('Menu eliminado (id :' . $data->id . ')');
                    $answer = array(
                        "code" => 200,
                    );
                }
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
    public function restaurar($id, $table = false)
    {
        if ($table == 'detail') {
            $data             = MenuDetalle::withTrashed()->findOrFail($id);
            $data->deleted_at = null;
            $data->save();
            $answer = array(
                'code' => 200,
            );
        } else {
            $data             = Menu::withTrashed()->findOrFail($id);
            $data->deleted_at = null;
            $data->save();
            $answer = array(
                'code' => 200,
            );
        }
        return $answer;
    }

    /**
     * Obtener todos los registros de la tabla para el datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll($type)
    {
      if($type == 1){
        $type = 'cdi';
      }else{
        if($type == 2){
          $type = 'hcb';
        }
      }
        $data = Menu::join('clientes as b', 'menu.cliente_id', 'b.id')
            ->join('admin_table AS c', 'menu.tipo_us_id', 'c.id')
            ->select('menu.id', 'menu.name', 'menu.cliente_id', 'b.name AS cliente', 'menu.tipo_us_id', 'c.name AS tipo_uds')
            ->where('c.name', $type)
            ->get();
        return \DataTables::of($data)->make(true);
    }

    public function getDataSelect($tipo_us_id)
    {
        $data = DB::table('menu')
            ->select('id', 'name')
            ->where('tipo_us_id', $tipo_us_id)
            ->get();
        $answer = array(
            'data' => $data,
        );
        return $answer;
    }

    public function getAllDetalle($id_menu)
    {
        $data = MenuDetalle::join('products AS b', 'menu_detalle.product_id', 'b.id')
            ->join('admin_table AS c', 'b.unidad_medida_id', 'c.id')
            ->leftJoin(DB::raw("(SELECT
                                z.id,
                                z.menu_detalle_id,
                                z.grupo_edad_id,
                                z.cantidad
                            FROM
                                pivot_menu_detalle_cantidad AS z
                            WHERE
                                z.grupo_edad_id = 24
                        ) AS d"), 'menu_detalle.id', 'd.menu_detalle_id')
            ->leftJoin(DB::raw("(SELECT
                                z.id,
                                z.menu_detalle_id,
                                z.grupo_edad_id,
                                z.cantidad
                            FROM
                                pivot_menu_detalle_cantidad AS z
                            WHERE
                                z.grupo_edad_id = 25
                        ) AS e"), 'menu_detalle.id', 'e.menu_detalle_id')
            ->select(
                'menu_detalle.id',
                'menu_detalle.unidad_medida_real AS um_pedido',
                'b.id AS product_id',
                'b.name AS product',
                'c.name AS unidad_medida',
                'c.description AS unidad_medida_ab',
                'd.id AS cantidad_1_3_id',
                'd.cantidad AS cantidad_1_3',
                'e.id AS cantidad_4_5_id',
                'e.cantidad AS cantidad_4_5'
            )
            ->where('menu_detalle.menu_id', $id_menu)
            ->get();
        return \DataTables::of($data)->make(true);
    }

    public function addMenuDetail(Request $request)
    {
            $product = Product::join('admin_table AS b', 'products.unidad_medida_id', 'b.id')
            ->select('products.id', 'products.conversion', 'b.name AS unidad_medida', 'b.description AS unidad_medida_ab')
            ->where('products.id',  $request->product_id)
            ->first();
        DB::beginTransaction();
        try {
            $id = DB::table('menu_detalle')->insertGetId(
                [
                    'menu_id'    => $request->menu_id,
                    'product_id' => $request->product_id,
                    'unidad_medida' => $product->unidad_medida_ab,
                    'unidad_medida_real' => $product->unidad_medida,
                    'conversion' => $product->conversion,
                    'created_at' => date('Y-m-d H:i:s'),
                ]
            );
            for ($i=24; $i < 26; $i++) {
              DB::table('pivot_menu_detalle_cantidad')->insert(
                [
                  'menu_detalle_id'    => $id,
                  'grupo_edad_id' => $i,
                  'cantidad' => ($request->age_group_id == $i) ? $request->cantidad : 0
                ]
              );
            }
            if ($id) {
                $this->AddToLog('Menu detalle creado (id :' . $id . ')');
                $answer = array(
                    "datos"  => $id,
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
            DB::commit();
            return $answer;
        } catch (Exception $e) {
            DB::rollback();
            $answer = array(
                "error" => $e,
                "code"  => 600,
            );
            return $answer;
        }
    }

    public function updateDetailMenu(Request $request)
    {
        try {
          if($request->name == 'um_pedido'){
            $pivot = DB::table('pivot_menu_detalle_cantidad')->where('id', $request->pk)->first();
            MenuDetalle::where('id', $pivot->menu_detalle_id)->update(['unidad_medida_real' => $request->value]);
            $this->AddToLog('Menu detalle editado (Edicion unidad medida final en la minuta al producto: '. $request->product_id .')');
          }else{
            DB::table('pivot_menu_detalle_cantidad')
            ->where('id', $request->pk)
            ->update(['cantidad' => $request->value]);
            $this->AddToLog('Menu detalle editado (tbl_detalle_cantidad id ' . $request->pk . ')');
          }
            $answer = array(
                "datos"  => '',
                "code"   => 200,
                "status" => 200,
            );
            return $answer;
        } catch (Exception $e) {
            $answer = array(
                "error"  => $e,
                "code"   => 600,
                "status" => 500,
            );
            return $answer;
        }
    }

    public function changeUnitFinal(Request $request)
    {
      try {
          MenuDetalle::where('product_id', $request->product_id)->update(['unidad_medida_real' => $request->unidad_medida_real]);
          $this->AddToLog('Menu detalle editado (Edicion general de la unidad medida final en la minuta al producto: '. $request->product_id .')');
          $answer = array(
              "datos"  => '',
              "code"   => 200,
              "status" => 200,
          );
          return $answer;
      } catch (\Exception $e) {
          $error = '';
          if (isset($e->errorInfo) and $e->errorInfo) {
              foreach ($e->errorInfo as $key => $value) {
                  $error .= $key . ' - ' . $value . ' <br> ';
              }
          } else { $error = $e;}
          $answer = array(
              "error"  => $error,
              "code"   => 600,
              "status" => 500,
          );
          return $answer;
      }
    }

    public function copyMenu($menu_id, $menu_id_copy)
    {
      try {
          /* ELIMINO LOS DETALLES DEL MENU SELECCIONADO */
          MenuDetalle::where([['menu_id', $menu_id_copy], ['deleted_at', null]])->delete();
          /* COPIO LOS DETALLES DEL MENU ELEGIDO */
          $data = MenuDetalle::where([['menu_id', $menu_id], ['deleted_at', null]])->get();
          foreach ($data as $key => $value) {
            /* INSERTO EL NUEVO DETALLE DEL MENU A COPIAR */
            $id = DB::table('menu_detalle')->insertGetId(
                [
                    'menu_id'    => $menu_id_copy,
                    'product_id' => $value->product_id,
                    'unidad_medida' => $value->unidad_medida,
                    'unidad_medida_real' => $value->unidad_medida_real,
                    'conversion' => $value->conversion,
                    'created_at' => date('Y-m-d H:i:s'),
                ]
            );
            /* OBTENGO LAS CANTIDADES DEL MENU COPIADO PARA REGISTRARLOS EN EL MENU A COPIAR*/
            $pivot = DB::table('pivot_menu_detalle_cantidad AS a')
                ->select(
                  'a.id',
                  'a.menu_detalle_id',
                  'a.grupo_edad_id',
                  'a.cantidad')
                ->where('menu_detalle_id', $value->id)
                ->get();

            foreach ($pivot as $key2 => $val) {
              // INSERTO LAS CANTIDADES AL NUEVO MENU
              DB::table('pivot_menu_detalle_cantidad')->insert(
                [
                  'menu_detalle_id'    => $id,
                  'grupo_edad_id' => $val->grupo_edad_id,
                  'cantidad' => $val->cantidad
                ]
              );
            }
          }
          $answer = array(
              "datos"  => $data,
              "code"   => 200
          );
          return $answer;
      } catch (\Exception $e) {
          $answer = array(
              "error"  => $e,
              "code"   => 600,
              "status" => 500,
          );
          return $answer;
      }
    }
}
