<!DOCTYPE html>
<html>
<head>
	<title>Minuta</title>
</head>
<body>
	
	<table border="1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th>MENU</th>
				@if(count($uds) > 0)
					@foreach($uds as $ud)
						<th>{{ $ud->name_uds }}</th>
					@endforeach
				@endif
				<th>TOTAL PEDIDO</th>
				<th>UNIDAD MEDIDA</th>
			</tr>
		</thead>
		<tbody>
			@if(count($data) > 0)
				@foreach($data as $dt)
					<tr>
						<td>{{ $dt->MENU }}</td>
						<?php $arr = (array)$dt; ?>
						@foreach($uds as $ud)
							<td>{{ $arr[str_replace(' ', '_', $ud->name_uds)] }}</td>
						@endforeach
						<td>{{ $dt->TOTAL_PEDIDO }}</td>
						<td>{{ $dt->UNIDAD_MEDIDA }}</td>
					</tr>
				@endforeach
			@endif
		</tbody>
	</table>
</body>
</html>