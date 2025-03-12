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
				<th colspan="2">Cll 34 9 195</th>
				<th>{{ $provider->name }}</th>
			</tr>
			<tr>
				<th colspan="2">TELEFONO {{ $provider->phone }}</th>
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
				@foreach ($data['uds'] as $val)
					<th>{{ $val->name_uds }}</th>
				@endforeach
				<th>TOTAL PEDIDO</th>
				<th>UNIDAD MEDIDA</th>
				<th>VALOR UNITARIO</th>
				<th>VALOR TOTAL</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($data['menu'] as $value)
				<tr>
					<td>{{ $value->MENU }}</td>
					<?php
						$tot = 0;
						$arr = (array)$value;
					?>
					@foreach ($data['uds'] as $ud)
						@if(isset($arr[str_replace(' ', '_', $ud->name_uds)]))
							<td>{{ $arr[str_replace(' ', '_', $ud->name_uds)] }}</td>
							<?php $tot += $arr[str_replace(' ', '_', $ud->name_uds)] ?>
						@else
							<td></td>
						@endif
					@endforeach
					<td>{{ $tot }}</td>
					<td>{{ $value->UNIDAD_MEDIDA }}</td>
					<td>0</td>
					<td>0</td>
				</tr>
			@endforeach
		</tbody>
		<tfoot>
			<tr>
				<th></th>
			</tr>
			<tr><th>ENTREGAR EN AZUR  </th></tr>
		</tfoot>
	</table>
</body>
</html>
