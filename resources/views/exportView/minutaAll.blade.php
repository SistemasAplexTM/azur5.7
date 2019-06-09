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
							@if(!$reman || $reman == 'false')
								<td>{{ $arr[str_replace(' ', '_', $ud->name_uds)] }}</td>
							@else
								<td>
									<?php $remanencia = 0; ?>
									@if(count($remanencias) > 0)
										@foreach($remanencias as $re)
											@if($re->producto == $dt->MENU AND $re->uds_name == $ud->name_uds)
												<?php $remanencia += $re->cantidad; ?>
											@endif
										@endforeach
									@endif
									{{ (($arr[str_replace(' ', '_', $ud->name_uds)] - $remanencia) < 0) ? 0 : ($arr[str_replace(' ', '_', $ud->name_uds)] - $remanencia) }}
								</td>
							@endif
						@endforeach
						@if(!$reman || $reman == 'false')
							<td>{{ $dt->TOTAL_PEDIDO }}</td>
						@else
							<td>
								<?php $remanencia = 0; ?>
								@if(count($remanencias) > 0)
									@foreach($remanencias as $re)
										@if($re->producto == $dt->MENU)
											<?php $remanencia += $re->cantidad; ?>
										@endif
									@endforeach
								@endif
								{{ (($dt->TOTAL_PEDIDO - $remanencia) < 0) ? 0 : ($dt->TOTAL_PEDIDO - $remanencia) }}
							</td>
						@endif
						<td>{{ $dt->UNIDAD_MEDIDA }}</td>
					</tr>
				@endforeach
			@endif
		</tbody>
	</table>
</body>
</html>
