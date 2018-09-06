<!DOCTYPE html>
<html>
<head>
	<title>Minuta</title>
</head>
<body>
	{{-- <pre>
	{{ print_r($data) }}
	</pre> --}}
	<table border="1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th colspan="2"></th>
			</tr>
			<tr>
				<th colspan="2"></th>
				<th>CLIENTE: CAIP INDUSTRIAL LOS MANGOS</th>
			</tr>
			<tr>
				<th colspan="2"></th>
				<th>Nit: 890.318.793-8</th>
			</tr>
			<tr>
				<th colspan="2"></th>
				<th>PROYECTO CDI INSTITUCIONAL </th>
			</tr>
			<tr>
				<th colspan="2"></th>
				<th>NOMBRE DE LA UDS: CDI HERRADURA</th>
			</tr>
			<tr>
				<th colspan="2"></th>
				<th>ENTREGA DE RACIONES -  MENU 24, 25, Y MENU  3</th>
			</tr>
			<tr>
				<th colspan="2">CALLE 49 No.3GN - 42 BARRIO VIPASA</th>
				<th>DIAS DE ATENCION: JUEVES 16, VIERNES 17 MARTES 21   DE AGOSTO DE 2018</th>
			</tr>
			<tr>
				<th colspan="2">TELEFONO 410 2392</th>
				<th>FECHA DE ENTREGA :14 DE AGOSTO DE 2018 SE ENTREGA 4 DIAS YA QUE EL LUNES 20 DE AGOSTO ES FESTIVO</th>
			</tr>

			<tr>
				<th colspan="2"></th>
			</tr>
			<tr>
				<th colspan="2"></th>
			</tr>
			<tr>
				<th><div style="border: solid 1px #000;">INGREDIENTES</div></th>
				<th>TOTAL EN LITROS KILOS O UNIDADES</th>
				<th>PEDIDO</th>
				<th>OBSERVACION</th>
			</tr>
		</thead>
		<tbody>
			@if(count($data) > 0)
				@foreach($data as $dt)
					<tr>
						<td>{{ $dt->MENU }}</td>
						<td>{{ $dt->TOTAL_PEDIDO }}</td>
						<td>{{ $dt->UNIDAD_MEDIDA }}</td>
						<td></td>
					</tr>
				@endforeach
			@endif
		</tbody>
		<tfoot>
			<tr><th></th></tr>
			<tr><th></th></tr>
			<tr><th>NOMBRE DE QUIEN RECIBE</th></tr>
			<tr><th>FIRMA</th></tr>
			<tr><th>C.C. No.</th></tr>
			<tr><th></th></tr>
			<tr><th></th></tr>
			<tr><th>NOMBRE : WILMER JULIAN CASTRO ORTIZ</th></tr>
			<tr><th>CEDULA: 1143.831.917 de Cali</th></tr>
			<tr><th>FIRMA:__________________________________</th></tr>
			<tr><th></th></tr>
		</tfoot>
	</table>
</body>
</html>