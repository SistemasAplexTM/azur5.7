<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use DateTime;
use App\Documento;
use App\UnidadServicio;
use App\Minuta;
use App\Remanencia;
use App\Exports\InvoicesExportView;
use App\Exports\InvoicesExport;
use App\Exports\ExportProvider;
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

    public function index()
    {
        // $this->assignPermissionsJavascript('menus');
        return view('templates/minuta/index');
    }

    public function store(Request $request)
    {
        /* LLENO EL ARREGLO DE FESTIVOS */
        $this->festivos();

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
                            $consecutive = DB::select("CALL getConsecutivoByTipoDocumento(?,?)",array(1, $id_doc));
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
                            $edad = 24;
                            for ($z = 0; $z < 2; $z++) {
                                if ($z == 1) {
                                    $edad = 25;
                                }
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
                                        'cantidad_final'     => -($value_md->coverage * $value_md->cantidad),
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
            ->groupBY(DB::raw("CONVERT(SUBSTR(b.`name`, 5), UNSIGNED INTEGER)"), 'c.feriado')
            ->orderBy(DB::raw("CONVERT(SUBSTR(b.`name`, 5), UNSIGNED INTEGER)"))
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
        $unidades = DB::table('minuta_documento_pivot AS a')
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
                'c.tipo_unidad_servicio_id')
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

        return view('templates/minuta/minuta', compact(
            'minuta',
            'unidades',
            'menus'
        ));
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

    public function getMenusUnidadesByMinuta($id_minuta, $id_us)
    {
        $fecha = DB::table('minuta_documento_pivot AS a')
            ->join('documento AS b', 'a.documento_id', 'b.id')
            ->select(DB::raw("min(b.fecha) AS fecha"))
            ->where('a.minuta_id', $id_minuta)
            ->first();

        DB::statement(DB::raw("SET @unidad_fecha = DAY('" . $fecha->fecha . "') - 1;"));
        DB::statement(DB::raw("SET @unidad_fecha1 = '" . $fecha->fecha . "'"));
        DB::statement(DB::raw("SET @unidad_servicio = " . $id_us . ";"));

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
                DB::raw("Round( (Sum( If((b.feriado = 0 and b.numero_dia >= 1 and  b.numero_dia <= 5 and  c.edad_id = 24), c.cantidad_unit, 0 ))),2) as 'st-1'"),
                DB::raw("Round( (Sum( If((b.feriado = 0 and b.numero_dia >= 1 and  b.numero_dia <= 5 and  c.edad_id = 25), c.cantidad_unit, 0 ))),2) as 'st-2'"),
                /* st-3 = st-1 * covertura */
                DB::raw("Round( (Sum( If((b.feriado = 0 and b.numero_dia >= 1 and  b.numero_dia <= 5 and  c.edad_id = 24), c.cantidad_unit, 0 ))),2) * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 24) as 'st-3'"),
                /* st-4 = st-2 * covertura */
                DB::raw("Round( (Sum( If((b.feriado = 0 and b.numero_dia >= 1 and  b.numero_dia <= 5 and  c.edad_id = 25), c.cantidad_unit, 0 ))),2) * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 25)  as 'st-4'"),
                /* GRAN TOTAL (st-5 = st-3 + st-4) */
                DB::raw("Round( (Sum( If((b.feriado = 0 and b.numero_dia >= 1 and  b.numero_dia <= 5 and  c.edad_id = 24), c.cantidad_unit, 0 ))),2) * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 24) + Round( (Sum( If((b.feriado = 0 and DATEDIFF(b.fecha,@unidad_fecha1) +1) >= 1 and  b.numero_dia <= 5 and  c.edad_id = 25, c.cantidad_unit, 0 ))),2) * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 25)  as 'st-5'"),
                /* GRAN TOTAL (st-6 = (st-3 + st-4)/ b.conversion) */
                DB::raw("IF((Round((Round( (Sum( If((b.feriado = 0 and b.numero_dia >= 1 and  b.numero_dia <= 5 and  c.edad_id = 24), c.cantidad_unit, 0 ))),2) * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 24) + Round( (Sum( If((b.feriado = 0 and DATEDIFF(b.fecha,@unidad_fecha1) +1) >= 1 and  b.numero_dia <= 5 and  c.edad_id = 25, c.cantidad_unit, 0 ))),2) * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 25))/d.conversion,0) = 0),1,(Round((Round( (Sum( If((b.feriado = 0 and b.numero_dia >= 1 and  b.numero_dia <= 5 and  c.edad_id = 24), c.cantidad_unit, 0 ))),2) * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 24) + Round( (Sum( If((b.feriado = 0 and DATEDIFF(b.fecha,@unidad_fecha1) +1) >= 1 and  b.numero_dia <= 5 and  c.edad_id = 25, c.cantidad_unit, 0 ))),2) * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 25))/d.conversion,0))) as 'st-6'")
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
            ->havingRaw("(Round( (Sum( If((b.feriado = 0 and b.numero_dia >= 1 and  b.numero_dia <= 5 and  c.edad_id = 24), c.cantidad_unit, 0 ))),2) * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 24) + Round( (Sum( If(b.feriado = 0 and b.numero_dia >= 1 and  b.numero_dia <= 5 and  c.edad_id = 25, c.cantidad_unit, 0 ))),2) * (select y.coverage from pivot_unidad_servicio_edad as y where y.unidad_servicio_id = @unidad_servicio and y.grupo_edad_id = 25)) > 0")
            ->get();
        return \DataTables::of($data)->make(true);
    }

    /*
    var $send = Es para la funcion .. define si descarga el pedido o manda
    los datos a una funcion externa
    */
    public function getPedidoCompleto($id_minuta, $product_type = null, $id_uds = null, $name_minuta = null, $remanencia = false, $send = false)
    {
        $title = 'Pedido completo';
        if($remanencia){
          $title .= ' con remanencias';
        }

        $wereUds = [['a.minuta_id', $id_minuta]];
        if($id_uds != null and $id_uds != 'null'){
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
                    $select .= " IF ((Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND  b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 24) + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 25)) / d.conversion,0) = 0),1,Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 24) + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 25)) / d.conversion,0)) AS '" . str_replace(' ', '_', $value->name_uds) . "'";

                    $total .= "IF ((Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND  b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 24) + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 25)) / d.conversion,0) = 0),1,Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 24) + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 25)) / d.conversion,0))";

                    $having .= "(Round((Sum(IF ((b.feriado = 0  AND b.unidad_servicio_id = ".$value->uds." AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = ".$value->uds." AND y.grupo_edad_id = 24) + Round((Sum(IF ((b.feriado = 0  AND b.unidad_servicio_id = ".$value->uds." AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = ".$value->uds." AND y.grupo_edad_id = 25)) > 0";

                    $flag = false;
                }else{
                    $select .= ", IF ((Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND  b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 24) + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 25)) / d.conversion,0) = 0),1,Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 24) + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 25)) / d.conversion,0)) AS '" . str_replace(' ', '_', $value->name_uds) . "' ";

                    $total .= " + IF ((Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND  b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 24) + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 25)) / d.conversion,0) = 0),1,Round((Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 24) + Round((Sum(IF ((b.feriado = 0 AND b.unidad_servicio_id = " . $value->uds . " AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = " . $value->uds . " AND y.grupo_edad_id = 25)) / d.conversion,0))";

                    $having .= " AND (Round((Sum(IF ((b.feriado = 0  AND b.unidad_servicio_id = ".$value->uds." AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 24),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = ".$value->uds." AND y.grupo_edad_id = 24) + Round((Sum(IF ((b.feriado = 0  AND b.unidad_servicio_id = ".$value->uds." AND b.numero_dia >= 1 AND b.numero_dia <= 5 AND c.edad_id = 25),c.cantidad_unit,0))),2) * (SELECT y.coverage FROM pivot_unidad_servicio_edad AS y WHERE y.unidad_servicio_id = ".$value->uds." AND y.grupo_edad_id = 25)) > 0";
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
            if($id_uds != null and $id_uds != 'null'){
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
        }else {
          Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
              $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
          });
          // return view('exportView/minutaAll', compact('data', 'uds'));
          if($id_uds != null and $id_uds != 'null'){
              $title = $uds[0]->name_uds;
              return Excel::download(new InvoicesExportView("exportView.minuta", $data, $remanencias, $uds[0]->name_uds, $name_minuta, $remanencia), 'Minuta '.$title.'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
          }else{
              return Excel::download(new InvoicesExport("exportView.minutaAll",
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

    public function saveRemanencia(Request $request, $id_minuta){
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

    public function getRemanenciasByMinuta($id_minuta, $uds_id){
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

    public function excelProveedores($data)
    {
      $ids = explode(',', $data);
      $minutas = array();
      for ($i=0; $i < count($ids); $i++) {
        $minutas[] = $this->getPedidoCompleto($ids[$i],null, null, null, true, true);
      }

      $menu = array();
      foreach ($minutas as $key => $value) {
        foreach ($value['datos'] as $keyv => $val) {
          if (!in_array($val->MENU, $menu)) {
              $menu[] = $val->MENU;
          }
        }
      }
      echo '<pre>';
      print_r($menu);
      echo '</pre>';

      exit();

      // Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
      //     $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
      // });
      // return Excel::download(new ExportProvider("exportView.proveedor", $minutas), 'Minuta Proveedor.xlsx', \Maatwebsite\Excel\Excel::XLSX);

    }

}
