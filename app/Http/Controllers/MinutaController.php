<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use DateTime;
use App\AdminTable;
use App\Documento;
use App\Minuta;
use App\Tercero;
use App\Remanencia;
use App\UnidadServicio;
use App\Exports\InvoicesExportView;
use App\Exports\InvoicesExport;
use App\Exports\ExportProvider;
use App\Exports\ExportProviderFull;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Maatwebsite\Excel\Sheet;

class MinutaController extends Controller
{
    private $hoy;
    private $festivos;
    private $ano;
    private $pascua_mes;
    private $pascua_dia;

    private $coverage_1_3;
    private $coverage_4_5;

    public function index()
    {
        // $this->assignPermissionsJavascript('menus');
        return view('templates/minuta/index');
    }

    public function store(Request $request)
    {
        /* LLENO EL ARREGLO DE FESTIVOS */
        $this->festivos();
        /* OBTENER LOS GRUPO EDAD REGISTRADOS */
        $ageGroups = $this->getAgeGroup();

        $fecha_inicio = new DateTime($request->fecha_inicio);
        $fecha_fin    = new DateTime($request->fecha_fin);
        $diff         = $fecha_inicio->diff($fecha_fin);
        $dias         = $diff->days;
        $cont         = 0; //contador para los menus
        //para comparar con el contador para menus. si es mayor esta variable, reinicializo el contador en 0 para
        // que me registre desde el primer menu de la lista.
        $cont_menus = count($request->menus) - 1;

        DB::beginTransaction();
        try {
            $data             = (new Minuta)->fill($request->all());
            $data->created_at = date('Y-m-d H:i:s');
            if ($data->save()) {
                $this->AddToLog('Minuta creada (id :' . $data->id . ')');
                /* REGISTRO UN DOCUMENTO POR UNIDAD DE SERVICIO EN CADA FECHA */
                foreach ($request->unidades as $key => $value) {
                    for ($i = 0; $i <= $dias; $i++) {
                        /* SUMO DIAS DE UNO EN UNO */
                        $nuevafecha = strtotime('+' . $i . ' day', strtotime($request->fecha_inicio));
                        $nuevafecha = date('Y-m-d', $nuevafecha);
                        /* VALIDO QUE LA NUEVA FECHA NO SEA NI SABADO (6) NI DOMINGO (0) PARA PODER REGISTRAR */
                        if (date("w", strtotime($nuevafecha)) != 0 and date("w", strtotime($nuevafecha)) != 6) {
                            if ($cont > $cont_menus) {
                                $cont = 0;
                            }
                            /* INSERCION DEL DOCUMENTO O EL MENU EN SI */
                            $id_doc = DB::table('documento')->insertGetId([
                                'tipo_documento_id'  => 1,
                                'terceros_id'        => 1,
                                'users_id'           => Auth::user()->id,
                                'unidad_servicio_id' => $value['id'],
                                'fecha'              => $nuevafecha,
                                'observacion'        => ((in_array($nuevafecha, $request->exclusiones)) ? ($request->exclusiones_motivo[array_search($nuevafecha, $request->exclusiones)]) : ''),
                                'feriado'            => ($this->esFestivo(date('d', strtotime($nuevafecha)), date('m', strtotime($nuevafecha)))) ? 1 : ((in_array($nuevafecha, $request->exclusiones)) ? 1 : 0),
                                'numero_dia'         => $cont + 1,
                                'created_at'         => date('Y-m-d H:i:s'),
                            ]);

                            /* INSERCION DE TABLA AUXILIAR CONSECUTIVO */
                            $consecutive = DB::select("CALL getConsecutivoByTipoDocumento(?,?)", array(1, $id_doc));
                            $consecutivo        = $consecutive[0]->consecutivo;
                            $data2              = Documento::findOrFail($id_doc);
                            $data2->consecutivo = $consecutivo;
                            $data2->save();
                            /* INSERCION DE TABLA PIVOT MINUTA_DOCUMENTO_PIVOT */
                            DB::table('minuta_documento_pivot')->insert([
                                'documento_id' => $id_doc,
                                'minuta_id'    => $data->id,
                                'menu_id'      => $request->menus[$cont]['id'],
                            ]);
                            for ($z = 0; $z < count($ageGroups); $z++) {
                                $edad = $ageGroups[$z]->id;
                                // if ($z == 1) {
                                //     $edad = 25;
                                // }
                                /* TOMAMOS LOS MENUS DETALLES DE LOS MENUS QUE SE ELIGIERON */
                                $menu_detalle = DB::table('menu_detalle AS a')
                                    ->leftJoin(DB::raw("(SELECT z.menu_detalle_id, z.grupo_edad_id, z.cantidad FROM pivot_menu_detalle_cantidad AS z WHERE z.grupo_edad_id = " . $edad . " AND z.deleted_at IS NULL) AS b"), 'a.id', 'b.menu_detalle_id')
                                    ->join('products AS c', 'a.product_id', 'c.id')
                                    ->join('admin_table AS d', 'c.unidad_medida_id', 'd.id')
                                    ->select(
                                        'a.id',
                                        'a.menu_id',
                                        'a.product_id',
                                        'd.name AS unidad_medida',
                                        'a.unidad_medida_real',
                                        'a.conversion',
                                        'b.cantidad AS cantidad_unit',
                                        DB::raw("round((b.cantidad / a.conversion),4) AS cantidad"),
                                        'b.grupo_edad_id',
                                        DB::raw("IFNULL((SELECT
                                                    SUM(q.coverage) AS coverage
                                                FROM
                                                    pivot_unidad_servicio_edad AS q
                                                INNER JOIN unidad_servicio AS w ON q.unidad_servicio_id = w.id
                                                INNER JOIN admin_table AS e ON q.grupo_edad_id = e.id
                                                WHERE
                                                    q.grupo_edad_id = " . $edad . "
                                                AND q.unidad_servicio_id = " . $value['id'] . "
                                            ),0) AS coverage")
                                    )
                                    ->where([['a.menu_id', $request->menus[$cont]['id']], ['a.deleted_at', NULL]])
                                    ->get();
                                foreach ($menu_detalle as $value_md) {
                                    /* INSERCION DEL DOCUMENTO DETALLE */
                                    $id_det = DB::table('documento_detalle')->insertGetId([
                                        'documento_id'       => $id_doc,
                                        'products_id'        => $value_md->product_id,
                                        'transaccion'        => 2, //salida
                                        'cantidad'           => $value_md->coverage * $value_md->cantidad,
                                        'cantidad_final'     => - ($value_md->coverage * $value_md->cantidad),
                                        'unidad_medida_real' => $value_md->unidad_medida_real,
                                        'cantidad_unit'      => $value_md->cantidad_unit,
                                        'unidad_medida'      => $value_md->unidad_medida,
                                        'costo'              => 0,
                                        'edad_id'            => $value_md->grupo_edad_id,
                                        'coverage'           => $value_md->coverage,
                                        'created_at'         => date('Y-m-d H:i:s'),
                                    ]);
                                }
                            }
                            $cont++;
                        }
                    }
                }

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

    public function edit($id)
    {
        // $minuta   = Minuta::findOrFail($id);
        DB::statement(DB::raw("SET lc_time_names = 'es_CO';"));
        $minuta = Minuta::join('admin_table AS b', 'minuta.tipo_unidad_servicio_id', 'b.id')
            ->join('clientes AS c', 'minuta.clientes_id', 'c.id')
            ->join(DB::raw("(
                SELECT
                    Min(CONVERT(SUBSTRING(x.`name`, 6), UNSIGNED INTEGER)) AS menu_ini,
                    Max(CONVERT(SUBSTRING(x.`name`, 6), UNSIGNED INTEGER)) AS menu_fin,
                    z.minuta_id
                FROM
                    minuta_documento_pivot AS z
                INNER JOIN menu AS x ON z.menu_id = x.id
                GROUP BY
                    z.minuta_id
            ) AS d"), 'minuta.id', 'd.minuta_id')
            ->select(
                'minuta.id',
                'minuta.fecha_inicio',
                'minuta.fecha_fin',
                'minuta.tipo_unidad_servicio_id',
                'minuta.clientes_id',
                'b.name AS tipo_unidad_servicio',
                'c.name AS cliente',
                DB::raw("CONCAT_WS(' ',date_format(minuta.fecha_fin, '%M'),'de',YEAR(minuta.fecha_fin),')') AS mes"),
                DB::raw("CONCAT_WS(' ', b.`name`, 'MENU',d.menu_ini,'al',d.menu_fin,' - (del',day(minuta.fecha_inicio),'al',day(minuta.fecha_fin),'de ') AS name_minuta")
            )
            ->where('minuta.id', $id)
            ->first();
        $menus = DB::table('minuta_documento_pivot AS a')
            ->Join('menu AS b', 'a.menu_id', 'b.id')
            ->Join('documento AS c', 'c.id', 'a.documento_id')
            ->select(DB::raw("CONVERT(SUBSTR(b.`name`, 5), UNSIGNED INTEGER) AS menu"), 'c.feriado')
            ->where('a.minuta_id', $id)
            ->groupBY("a.id", DB::raw("CONVERT(SUBSTR(b.`name`, 5), UNSIGNED INTEGER)"), 'c.feriado')
            ->orderBy(DB::raw("a.id"))
            ->get();

        /* VALIDO QUE LOS MENUS SEAN 5, SINO, COMPLETO LAS 5 POSICIONES REPITIENDO MENUS EN ELLAS  */
        $cont = 5 - count($menus);
        if ($cont != 0) {
            $z = 0;
            for ($i = 0; $i < $cont; $i++) {
                if ($z > count($menus)) {
                    $z = 0;
                }
                $menus[] = (object) array('menu' => $menus[$z]->menu);
                $z++;
            }
        }
        $unidades = $this->getUnidades($id);

        return view('templates/minuta/minuta', compact(
            'minuta',
            'unidades',
            'menus'
        ));
    }

    public function getUnidades($id)
    {
        $data = DB::table('minuta_documento_pivot AS a')
            ->Join('documento AS b', 'a.documento_id', 'b.id')
            ->Join('unidad_servicio AS c', 'b.unidad_servicio_id', 'c.id')
            ->leftJoin(DB::raw('(SELECT
                            z.documento_id,
                            z.coverage
                        FROM
                            documento_detalle AS z
                        WHERE
                            z.edad_id = 24
                        GROUP BY
                            z.documento_id,
                            z.coverage
                    ) AS d'), 'b.id', 'd.documento_id')
            ->leftJoin(DB::raw('(SELECT
                            z.documento_id,
                            z.coverage
                        FROM
                            documento_detalle AS z
                        WHERE
                            z.edad_id = 25
                        GROUP BY
                            z.documento_id,
                            z.coverage
                    ) AS e'), 'b.id', 'e.documento_id')
            ->select(
                'c.name',
                'd.coverage AS coverage_1_3',
                'e.coverage AS coverage_4_5',
                'c.id',
                'c.cliente_id',
                'c.tipo_unidad_servicio_id'
            )
            ->groupBy(
                'c.name',
                'c.id',
                'c.cliente_id',
                'c.tipo_unidad_servicio_id',
                'd.coverage',
                'e.coverage'
            )
            ->where('a.minuta_id', $id)
            ->get();
        return $data;
    }

    public function update(Request $request, $id)
    {
        try {
            $data = Minuta::findOrFail($id);
            $data->update($request->all());
            $this->AddToLog('Minuta editada (id :' . $data->id . ')');
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

    public function destroy($id)
    {
        try {
            $data = Minuta::findOrFail($id);
            if ($data->delete()) {
                $this->AddToLog('Minuta eliminado (id :' . $data->id . ')');
                $answer = array(
                    "code" => 200,
                );
            }
        } catch (Exception $e) {
            return $e;
        }
    }

    public function restaurar($id)
    {
        $data             = Minuta::withTrashed()->findOrFail($id);
        $data->deleted_at = null;
        $data->save();
        $answer = array(
            'code' => 200,
        );
        return $answer;
    }

    public function getAll()
    {
        $data = $this->sqlListMinuta();
        return \DataTables::of($data)->make(true);
    }

    public function getDataSelect()
    {
        $data = DB::table('minuta')
            ->select('id', 'name', 'age_group')
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
            ->select('menu_detalle.id', 'menu_detalle.weight', 'b.id AS product_id', 'b.name AS product', 'c.name AS unidad_medida', 'c.description AS unidad_medida_ab')
            ->where('menu_detalle.menu_id', $id_menu)
            ->get();
        return \DataTables::of($data)->make(true);
    }

    public function getMenusUnidadesByMinuta($id_minuta, $id_us, $coverage_1_3, $coverage_4_5)
    {
        $this->coverage_1_3 = $coverage_1_3;
        $this->coverage_4_5 = $coverage_4_5;

        $fecha = DB::table('minuta_documento_pivot AS a')
            ->join('documento AS b', 'a.documento_id', 'b.id')
            ->select(DB::raw("min(b.fecha) AS fecha"))
            ->where('a.minuta_id', $id_minuta)
            ->first();
        // print_r($fecha);
        // echo $id_minuta . ' - ' . $id_us;
        // exit();
        DB::statement(DB::raw("SET @unidad_fecha = DAY('" . $fecha->fecha . "') - 1;"));
        DB::statement(DB::raw("SET @unidad_fecha1 = '" . $fecha->fecha . "'"));
        DB::statement(DB::raw("SET @unidad_servicio = " . $id_us . ";"));

        // DB::connection()->enableQueryLog();
        $data = DB::table('minuta_documento_pivot AS a')
            ->join('documento AS b', 'a.documento_id', 'b.id')
            ->join('documento_detalle AS c', 'c.documento_id', 'b.id')
            ->join('products AS d', 'c.products_id', 'd.id')
            ->join('admin_table AS u', 'd.unidad_medida_id', 'u.id')
            ->join('admin_table AS e', 'e.id', 'c.edad_id')
            ->join('pivot_unidad_servicio_edad AS f', function ($join) {
                $join->on('f.grupo_edad_id', '=', 'e.id')
                    ->on('f.unidad_servicio_id', '=', 'b.unidad_servicio_id');
            })
            ->select(
                'a.minuta_id',
                'c.products_id',
                'd.name AS producto',
                'c.unidad_medida_real',
                'c.unidad_medida',
                DB::raw("Round( (Sum( If(b.numero_dia = 1 and  c.edad_id = 24, c.cantidad_unit, NULL ))),2) as '1'"),
                DB::raw("Round( (Sum( If(b.numero_dia = 2 and  c.edad_id = 24, c.cantidad_unit, NULL ))),2) as '2'"),
                DB::raw("Round( (Sum( If(b.numero_dia = 3 and  c.edad_id = 24, c.cantidad_unit, NULL ))),2) as '3'"),
                DB::raw("Round( (Sum( If(b.numero_dia = 4 and  c.edad_id = 24, c.cantidad_unit, NULL ))),2) as '4'"),
                DB::raw("Round( (Sum( If(b.numero_dia = 5 and  c.edad_id = 24, c.cantidad_unit, NULL ))),2) as '5'"),
                DB::raw("Round( (Sum( If(b.numero_dia = 1 and  c.edad_id = 25, c.cantidad_unit, NULL ))),2) as '6'"),
                DB::raw("Round( (Sum( If(b.numero_dia = 2 and  c.edad_id = 25, c.cantidad_unit, NULL ))),2) as '7'"),
                DB::raw("Round( (Sum( If(b.numero_dia = 3 and  c.edad_id = 25, c.cantidad_unit, NULL ))),2) as '8'"),
                DB::raw("Round( (Sum( If(b.numero_dia = 4 and  c.edad_id = 25, c.cantidad_unit, NULL ))),2) as '9'"),
                DB::raw("Round( (Sum( If(b.numero_dia = 5 and  c.edad_id = 25, c.cantidad_unit, NULL ))),2) as '10'"),
                DB::raw($this->st1() . " as 'st-1'"),
                DB::raw($this->st2() . " as 'st-2'"),
                /* st-3 = st-1 * covertura */
                DB::raw($this->st3() . " as 'st-3'"),
                /* st-4 = st-2 * covertura */
                DB::raw($this->st4() . " as 'st-4'"),
                /* GRAN TOTAL (st-5 = st-3 + st-4) */
                DB::raw($this->st5() . " as 'st-5'"),
                /* GRAN TOTAL (st-6 = (st-3 + st-4)/ b.conversion) */
                DB::raw($this->st6() . " as 'st-6'")
            )
            ->where([
                ['b.unidad_servicio_id', $id_us],
                ['a.minuta_id', $id_minuta],
            ])
            ->groupBy(
                'a.minuta_id',
                'c.products_id',
                'd.name',
                'c.unidad_medida_real',
                'c.unidad_medida',
                'd.conversion'
            )
            // ->havingRaw("(".$this->st3()." + ". $this->st4() .") > 0")
            ->get();
        // return DB::getQueryLog();
        return \DataTables::of($data)->make(true);
    }

    public function st1()
    {
        return 'Round( (Sum( If((b.feriado = 0 and b.numero_dia >= 1 and  b.numero_dia <= 5 and  c.edad_id = 24), c.cantidad_unit, 0 ))),2)';
    }
    public function st2()
    {
        return 'Round( (Sum( If((b.feriado = 0 and b.numero_dia >= 1 and  b.numero_dia <= 5 and  c.edad_id = 25), c.cantidad_unit, 0 ))),2)';
    }
    public function st3()
    {
        return $this->st1() . ' * ' . $this->coverage_1_3;
        //   return $this->st1() . ' * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 24)';
    }
    public function st4()
    {
        return $this->st2() . ' * ' . $this->coverage_4_5;
        //   return $this->st2() . ' * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 25)';
    }
    public function st5()
    {
        return $this->st3() . ' + ' . $this->st4();
    }
    public function st6()
    {
        // return 'IF((Round(('. $this->st5() .')/d.conversion,0) = 0),1,(Round(('. $this->st5() .')/d.conversion,0)))';
        return 'IF((' . $this->st5() . ' = 0), 0, IF((Round((' . $this->st5() . ')/d.conversion,0) = 0),1,(Round((' . $this->st5() . ')/d.conversion,0))) )';
    }

    /*
    var $send = Es para la funcion .. define si descarga el pedido o manda
    los datos a una funcion externa
    */
    public function getPedidoCompleto($id_minuta, $coverage_1_3, $coverage_4_5, $product_type = null, $id_uds = null, $name_minuta = null, $remanencia = false, $send = false)
    {
        $this->coverage_1_3 = $coverage_1_3;
        $this->coverage_4_5 = $coverage_4_5;

        $title = 'Pedido completo';
        if ($remanencia) {
            $title .= ' con remanencias';
        }

        $wereUds = [['a.minuta_id', $id_minuta]];
        if ($id_uds != null and $id_uds != 'null') {
            $wereUds[] = ['b.unidad_servicio_id', $id_uds];
        }
        $uds = DB::table('minuta_documento_pivot AS a')
            ->join('documento AS b', 'a.documento_id', 'b.id')
            ->join('unidad_servicio AS c', 'b.unidad_servicio_id', 'c.id')
            ->join('admin_table AS d', 'c.tipo_unidad_servicio_id', 'd.id')
            ->select(DB::raw("DISTINCT b.unidad_servicio_id AS uds"), "c.name AS name_uds", "d.name AS tipo_uds")
            ->where($wereUds)
            ->get();

        $where = [['a.minuta_id', $id_minuta]];
        if ($product_type != null and $product_type != 'null') {
            $where[] = ['d.tipo_producto_id', $product_type];
            $product_type_data = DB::table('admin_table AS a')
                ->select('a.name')
                ->where([['a.table_name', 'tipo_producto'], ['a.id', $product_type]])
                ->first();
            $title = $product_type_data->name;
        }

        if (count($uds) > 0) {
            $select = "";
            $having = "";
            $total = "";
            $cont   = 1;
            $flag   = true;
            foreach ($uds as $value) {
                if ($flag) {
                    $select .= " IF ((Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND  b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * " . $this->coverage_1_3 . " + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * " . $this->coverage_4_5 . ") / d.conversion,0) = 0),1,Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * " . $this->coverage_1_3 . " + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * " . $this->coverage_4_5 . ") / d.conversion,0)) AS '" . str_replace(' ', '_', $value->name_uds) . "'";

                    $total .= "IF ((Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND  b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * " . $this->coverage_1_3 . " + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * " . $this->coverage_4_5 . ") / d.conversion,0) = 0),1,Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * " . $this->coverage_1_3 . " + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * " . $this->coverage_4_5 . ") / d.conversion,0))";

                    $having .= "(Round((Sum(IF ((b.feriado = 0  AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * " . $this->coverage_1_3 . " + Round((Sum(IF ((b.feriado = 0  AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * " . $this->coverage_4_5 . ") > 0";

                    $flag = false;
                } else {
                    $select .= ", IF ((Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND  b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * " . $this->coverage_1_3 . " + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * " . $this->coverage_4_5 . ") / d.conversion,0) = 0),1,Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * " . $this->coverage_1_3 . " + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * " . $this->coverage_4_5 . ") / d.conversion,0)) AS '" . str_replace(' ', '_', $value->name_uds) . "' ";

                    $total .= " + IF ((Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND  b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * " . $this->coverage_1_3 . " + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * " . $this->coverage_4_5 . ") / d.conversion,0) = 0),1,Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * " . $this->coverage_1_3 . " + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * " . $this->coverage_4_5 . ") / d.conversion,0))";

                    $having .= " AND (Round((Sum(IF ((b.feriado = 0  AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * " . $this->coverage_1_3 . " + Round((Sum(IF ((b.feriado = 0  AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * " . $this->coverage_4_5 . ") > 0";
                }
                $cont++;
            }

            $fecha = DB::table('minuta_documento_pivot AS a')
                ->join('documento AS b', 'a.documento_id', 'b.id')
                ->select(DB::raw("min(b.fecha) AS fecha"))
                ->where('a.minuta_id', $id_minuta)
                ->first();

            DB::statement(DB::raw("SET @unidad_fecha1 = '" . $fecha->fecha . "'"));
            $data = DB::table('minuta_documento_pivot AS a')
                ->join('documento AS b', 'a.documento_id', 'b.id')
                ->join('documento_detalle AS c', 'c.documento_id', 'b.id')
                ->join('products AS d', 'c.products_id', 'd.id')
                ->join('admin_table AS u', 'd.unidad_medida_id', 'u.id')
                ->join('admin_table AS e', 'e.id', 'c.edad_id')
                ->join('pivot_unidad_servicio_edad AS f', function ($join) {
                    $join->on('f.grupo_edad_id', '=', 'e.id')
                        ->on('f.unidad_servicio_id', '=', 'b.unidad_servicio_id');
                })
                ->select(
                    'd.name AS MENU',
                    DB::raw($select),
                    DB::raw($total . ' AS TOTAL_PEDIDO'),
                    "c.unidad_medida_real AS UNIDAD_MEDIDA"
                )
                ->where($where)
                ->groupBy(
                    'a.minuta_id',
                    'c.products_id',
                    'd.name',
                    'd.conversion',
                    "c.unidad_medida_real"
                )
                ->orderBy('d.name')
                ->havingRaw($having)
                ->get();

            $wereR = [['a.minuta_id', $id_minuta], ['a.deleted_at', NULL]];
            if ($id_uds != null and $id_uds != 'null') {
                $wereR[] = ['a.unidad_servicio_id', $id_uds];
            }
            $remanencias = DB::table('remanencias AS a')
                ->join('products AS b', 'a.products_id', 'b.id')
                ->join('unidad_servicio AS c', 'a.unidad_servicio_id', 'c.id')
                ->select('b.name AS producto', 'a.cantidad', 'a.unidad_servicio_id AS uds_id', 'c.name AS uds_name')
                ->where($wereR)
                ->get();
            $title .= ' ' . $uds[0]->tipo_uds;
        }
        if ($send) {
            return array(
                'datos' => $data,
                'uds' => $uds,
                'remanencias' => $remanencias,
                'remanencia' => $remanencia
            );
        } else {
            Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
                $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
            });
            // return view('exportView/minutaAll', compact('data', 'uds'));
            if ($id_uds != null and $id_uds != 'null') {
                $title = $uds[0]->name_uds;
                return Excel::download(new InvoicesExportView("exportView.minuta", $data, $remanencias, $uds[0]->name_uds, $name_minuta, $remanencia), 'Minuta ' . $title . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
            } else {
                return Excel::download(new InvoicesExport(
                    "exportView.minutaAll",
                    array(
                        'datos' => $data,
                        'uds' => $uds,
                        'remanencias' => $remanencias,
                        'remanencia' => $remanencia
                    )
                ), $title . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
            }
        }
    }

    public function getProductsMinuta($id_minuta, $uds_id)
    {
        try {
            $data = DB::table('minuta_documento_pivot AS a')
                ->join('documento AS b', 'a.documento_id', 'b.id')
                ->join('documento_detalle AS c', 'c.documento_id', 'b.id')
                ->join('products AS d', 'c.products_id', 'd.id')
                ->join('admin_table AS u', 'd.unidad_medida_id', 'u.id')
                ->select(
                    'd.id',
                    'd.name',
                    'c.unidad_medida_real',
                    'c.unidad_medida',
                    'u.description AS um'
                )
                ->where([
                    ['b.unidad_servicio_id', $uds_id],
                    ['a.minuta_id', $id_minuta],
                ])
                ->groupBy(
                    'd.id',
                    'd.name',
                    'c.unidad_medida_real',
                    'c.unidad_medida',
                    'u.description'
                )
                ->orderBy('d.name', 'ASC')
                ->get();
            $answer = array(
                'data' => $data,
            );
            return $answer;
        } catch (Exception $e) {
            return $e;
        }
    }

    public function saveRemanencia(Request $request, $id_minuta)
    {
        DB::beginTransaction();
        try {
            $id_doc = DB::table('remanencias')->insertGetId([
                'minuta_id'          => $request->minuta_id,
                'unidad_servicio_id' => $request->unidad_servicio_id,
                'products_id'        => $request->product_id,
                'cantidad'           => $request->cantidad,
                'descripcion'        => $request->descripcion,
                'created_at'         => date('Y-m-d H:i:s'),
            ]);
            $uds  = UnidadServicio::findOrFail($request->unidad_servicio_id);
            $this->AddToLog('Remanencia agregada a la UDS (' . $uds->name . ') en la minuta (' . $request->minuta_id . ')');
            DB::commit();
            $answer = array(
                "datos"  => $id_doc,
                "code"   => 200,
                "status" => 200,
            );
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

    public function getRemanenciasByMinuta($id_minuta, $uds_id)
    {
        $data = DB::table('remanencias AS a')
            ->join('products AS b', 'a.products_id', 'b.id')
            ->join('admin_table AS c', 'b.unidad_medida_id', 'c.id')
            ->select(
                'a.id',
                'a.cantidad',
                'a.descripcion',
                'b.id AS product_id',
                'b.name',
                'c.description AS um'
            )
            ->where([
                ['a.minuta_id', $id_minuta],
                ['a.unidad_servicio_id', $uds_id],
                ['a.deleted_at', null]
            ])
            ->get();

        return \DataTables::of($data)->make(true);
    }

    public function eliminarRemanencia($id_minuta, $id)
    {
        try {
            $data = Remanencia::findOrFail($id);
            if ($data->delete()) {
                $this->AddToLog('Remanencia eliminada (id :' . $data->id . ')');
                $answer = array(
                    "code" => 200,
                );
            }
        } catch (Exception $e) {
            return $e;
        }
    }

    public function restaurarRemanencia($id_minuta, $id)
    {
        $data             = Remanencia::withTrashed()->findOrFail($id);
        $data->deleted_at = null;
        $data->save();
        $answer = array(
            'code' => 200,
        );
        return $answer;
    }

    public function excelProveedores($data, $name, $product_type_id, $provider_id, $complete = false)
    {
        $type_product = AdminTable::findOrFail($product_type_id);
        $ids = explode(',', $data);

        $minutas = array();
        for ($i = 0; $i < count($ids); $i++) {
            $unidades = $this->getUnidades($ids[$i]);
            for ($u=0; $u < count($unidades); $u++) {
                $minutas[] = $this->getPedidoCompleto($ids[$i], $unidades[$u]->coverage_1_3, $unidades[$u]->coverage_4_5, $product_type_id, null, null, true, true);
            }
        }
        $dm = explode('-', $name);
        $dm[0] = trim(substr($dm[0], 3));
        $dm[1] = trim(substr(trim($dm[1]), 1, -1));

        $meses = array("ENERO", "FEBRERO", "MARZO", "ABRIL", "Mayo", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE");
        $dm[2] = strftime($meses[date('n') - 1] . " %d DE %Y");

        $menu = array();
        $remanencias = array();
        $menuTotalPedido = array();
        $contR = 0; //Contador para las remanencias
        $provider = Tercero::findOrFail($provider_id);

        Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
            $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
        });

        if ($complete === 'true') {
            $menu = $this->getComplete($minutas);
            $cont = 0;
            $abc = array();
            // GENERAR LETRAS DEL ABECEDARIO PARA EL EXCEL
            for ($i = 65; $i <= 90; $i++) {
                $abc[] = chr($i);
            }

            return Excel::download(new ExportProviderFull("exportView.proveedorFull", $menu, $dm, $provider, $type_product, $abc), 'Proveedor ' . $type_product->name . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        } else {
            foreach ($minutas as $key => $value) {
                foreach ($value['datos'] as $keyv => $val) {
                    if (array_search($val->MENU, array_column($menu, 'menu')) !== false) {
                        $menu[$keyv]['pedido'] += $val->TOTAL_PEDIDO;
                    } else {
                        $menu[$keyv]['menu'] = $val->MENU;
                        $menu[$keyv]['pedido'] = $val->TOTAL_PEDIDO;
                        $menu[$keyv]['unidad_medida'] = $val->UNIDAD_MEDIDA;
                        $menu[$keyv]['valor'] = 0;
                        $menu[$keyv]['valor_total'] = 0;
                    }
                }
                foreach ($value['remanencias'] as $keyr => $valr) {
                    if (array_search($valr->producto, array_column($remanencias, 'menu')) !== false) {
                        $cont_ = array_search($valr->producto, array_column($remanencias, 'menu'));
                        $remanencias[$cont_]['remanencia'] += $valr->cantidad;
                    } else {
                        $remanencias[$contR]['menu'] = $valr->producto;
                        $remanencias[$contR]['remanencia'] = $valr->cantidad;
                        $contR++;
                    }
                }
            }
            foreach ($menu as $key => $value) {
                $rem = array_column($remanencias, 'menu');
                $found_key = array_search($value['menu'], $rem);

                $keyR = array_search($value['menu'], array_column($remanencias, 'menu'));
                if (array_search($value['menu'], array_column($remanencias, 'menu')) !== false) {
                    $menu[$key]['pedido'] -= $remanencias[$keyR]['remanencia'];
                }
            }

            return Excel::download(new ExportProvider("exportView.proveedor", $menu, $dm, $provider, $type_product), 'Proveedor ' . $type_product->name . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }
    }

    public function getComplete($minuta)
    {
        $data = array();
        $uds = array();
        $remanencias = array();
        $menu = array();
        foreach ($minuta as $key => $value) {
            foreach ($value['datos'] as $ke => $val) {
                $data[] = $val;
            }
            foreach ($value['uds'] as $ke => $val) {
                $uds[] = $val;
            }
            foreach ($value['remanencias'] as $ke => $val) {
                $remanencias[] = $val;
            }
        }

        // UNIR LOS DATOS QUE TENGAN EL MISMO NOMBRE DEL INGREDIENTE EN UN SOLO
        // ARREGLO
        foreach ($data as $ke => $val) {
            $k = array_search($val->MENU, array_column($menu, 'MENU'));
            if (array_search($val->MENU, array_column($menu, 'MENU')) !== false) {
                //FUNCION QUE UNE ARREGLOS EN LA POSICION QUE ENCONTRO COINCIDENCIA
                $menu[$k] = (object) array_merge((array) $menu[$k], (array) $val);
            } else {
                $menu[] = $val;
            }
        }
        // echo '<pre>';
        // print_r($remanencias);
        // print_r($menu);
        // echo '</pre>';
        // exit();
        $pos = array();
        // RESTAR REMANENCIAS AL MENU CREADO
        foreach ($menu as $key => $value) {
            foreach ($remanencias as $ke => $val) {
                if (($i = array_search($value->MENU, (array)$val)) !== FALSE) {
                    // CREAMOS EL NOMBRE DE LA POSICION CON UN GUION DE LA UDS PARA BUSCAR LUEGO EN EL ARREGLO
                    $us = str_replace(' ', '_', $remanencias[$ke]->uds_name);
                    // BUSCAMOS LA CANTIDAD A DESCONTAR DE LA REMANENCIA
                    $rem = $remanencias[$ke]->cantidad;
                    // CONVERTIMOS EN ARRAY EL OBJETO QUE ESTAMOS RECORRIENDO
                    // PARA PODER RESTAR LA REMANENCIA DE EL Y REASIGNAR SU VALOR
                    $arr = (array)$value;
                    $arr[$us] = $arr[$us] - $rem;
                    // ASIGNAMOS EL NUEVO VALOR A LA POSICION QUE PERTENECE
                    $menu[$key]->$us = $arr[$us];
                }
            }
            // if (array_search($value->MENU, array_column($remanencias, 'producto')) !== false) {
            //   // TOMAMOS LA POSICION DE LA REMANENCIA
            //   $pos = array_search($value->MENU, array_column($remanencias, 'producto'));
            //
            //   // CREAMOS EL NOMBRE DE LA POSICION CON UN GUION DE LA UDS PARA BUSCAR LUEGO EN EL ARREGLO
            //   $us = str_replace(' ', '_', $remanencias[$pos]->uds_name);
            //   // echo $us . ' - '.$value->MENU.' <br>';
            //   // BUSCAMOS LA CANTIDAD A DESCONTAR DE LA REMANENCIA
            //   $rem = $remanencias[$pos]->cantidad;
            //
            //   // CONVERTIMOS EN ARRAY EL OBJETO QUE ESTAMOS RECORRIENDO
            //   // PARA PODER RESTAR LA REMANENCIA DE EL Y REASIGNAR SU VALOR
            //   $arr = (array)$value;
            //   $arr[$us] = $arr[$us] - $rem;
            //
            //   // CONVERTIMOS NUEVAMENTE EN OBJETO EL ARREGLO MODIFICADO PARA ASIGNARSELO
            //   // A LA POSISCION DEL ARREGLO PADRE DEL MENU
            //   $menu[$key] = (object)$arr;
            // }
        }

        return array('menu' => $menu, 'uds' => $uds);
    }

    public function sqlListMinuta()
    {
        // DB::connection()->enableQueryLog();
        DB::statement(DB::raw("SET lc_time_names = 'es_CO';"));
        $data = Minuta::join('admin_table AS b', 'minuta.tipo_unidad_servicio_id', 'b.id')
            ->join('clientes AS c', 'minuta.clientes_id', 'c.id')
            ->join(DB::raw("(
                SELECT
                    Min(CONVERT(SUBSTRING(x.`name`, 6), UNSIGNED INTEGER)) AS menu_ini,
                    Max(CONVERT(SUBSTRING(x.`name`, 6), UNSIGNED INTEGER)) AS menu_fin,
                    z.minuta_id
                FROM
                    minuta_documento_pivot AS z
                INNER JOIN menu AS x ON z.menu_id = x.id
                GROUP BY
                    z.minuta_id
            ) AS d"), 'minuta.id', 'd.minuta_id')
            ->select(
                'minuta.id',
                'minuta.fecha_inicio',
                'minuta.fecha_fin',
                'minuta.tipo_unidad_servicio_id',
                'minuta.clientes_id',
                'b.name AS tipo_unidad_servicio',
                'c.name AS cliente',
                DB::raw("CONCAT_WS(' ',date_format(minuta.fecha_fin, '%M'),'de',YEAR(minuta.fecha_fin), ')') AS mes"),
                DB::raw("CONCAT_WS(' ', b.`name`, 'MENU',d.menu_ini,'al',d.menu_fin,' - (del',day(minuta.fecha_inicio),'al',day(minuta.fecha_fin),'de ') AS name_minuta"),
                DB::raw("(
                        SELECT
                            GROUP_CONCAT(
                                DISTINCT o.name
                                ORDER BY
                                    o.name SEPARATOR ', '
                            ) AS uds
                        FROM
                            minuta_documento_pivot AS m
                        INNER JOIN documento AS n ON m.documento_id = n.id
                        INNER JOIN unidad_servicio AS o ON n.unidad_servicio_id = o.id
                        WHERE
                            m.minuta_id = minuta.id
                    ) AS uds"),
                DB::raw("date_format(minuta.created_at, '%Y-%m-%d') AS creacion")
            )
            ->get();
        // return DB::getQueryLog();
        return $data;
    }

    public function minutaJson($minuta_id)
    {
        $data =  DB::select(DB::raw(
            " 
                    SELECT
                    a.id,
                    a.fecha_inicio,
                    a.fecha_fin,
                    b.documento_id,
                    f.`name` AS unidad_servicio,
                    c.fecha,
                    c.numero_dia,
                    h.`name` AS grupo_edad,
                    d.unidad_medida_real AS unidad_pedido,
                    d.cantidad_unit,
                    d.unidad_medida,
                    d.coverage,
                    e.`name` AS producto,
                    e.conversion
                    FROM
                    minuta AS a
                    INNER JOIN minuta_documento_pivot AS b ON b.minuta_id = a.id
                    INNER JOIN documento AS c ON b.documento_id = c.id
                    INNER JOIN documento_detalle AS d ON d.documento_id = c.id
                    INNER JOIN products AS e ON d.products_id = e.id
                    INNER JOIN unidad_servicio AS f ON c.unidad_servicio_id = f.id
                    INNER JOIN admin_table AS h ON h.id = d.edad_id
                    WHERE
                    a.id = $minuta_id"
        ));
        return $data;
    }

    public function getAgeGroup()
    {
        return DB::select(DB::raw("
            SELECT
            a.id,
            a.table_name,
            a.`name`,
            a.description
            FROM
            admin_table AS a
            WHERE
            a.deleted_at IS NULL AND
            a.table_name = 'grupo_edad'"));
    }
}
