<!DOCTYPE html>
<html>
<head>
	<title>{{ $type_product->name }}</title>
</head>
<?php

?>
<body>
	<table border="1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th colspan="2"></th>
			</tr>
			<tr>
				<th colspan="2"></th>
				<th>ORDEN DE COMPRA: </th>
			</tr>
			<tr>
				<th colspan="2"></th>
				<th>ENTREGA DE RACIONES - HCB - CDI {{ $header[0] }}</th>
			</tr>
			<tr>
				<th colspan="2"></th>
				<th>DIAS DE ATENCION: {{ $header[1] }}</th>
			</tr>
			<tr>
				<th colspan="2"></th>
				<th></th>
			</tr>
			<tr>
				<th colspan="2"></th>
				<th>{{ $header[2] }}</th>
			</tr>
			<tr>
				<th colspan="2">{{ $company->address }}</th>
				<th>{{ $provider->name }}</th>
			</tr>
			<tr>
				<th colspan="2">TELEFONO {{ $company->phone }}</th>
				<th>NIT. {{ $provider->document_nit }}</th>
			</tr>

			<tr>
				<th colspan="2"></th>

			</tr>
			<tr>
				<th colspan="2"></th>

			</tr>
			<tr>
				<th><div style="border: solid 1px #000;">MENU</div></th>
				<th>TOTAL PEDIDO</th>
				<th>UNIDAD MEDIDA</th>
				<th>VALOR UNITARIO</th>
				<th>VALOR TOTAL</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($data as $value)
				<tr>
					<td>{{ $value['menu'] }}</td>
					<td>{{ $value['pedido'] }}</td>
					<td>{{ $value['unidad_medida'] }}</td>
					<td>{{ $value['valor'] }}</td>
					<td>{{ $value['valor_total'] }}</td>
				</tr>
			@endforeach
		</tbody>
		<tfoot>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th>TOTAL</th>
				<th>0</th>
			</tr>
			<tr><th>ENTREGAR EN AZUR  </th></tr>
		</tfoot>
	</table>
</body>
</html>
