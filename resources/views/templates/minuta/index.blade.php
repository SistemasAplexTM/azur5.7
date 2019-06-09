@extends('layouts.app')
@section('title', 'Minutas')
@section('breadcrumb')
{{-- bread crumbs --}}
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Documentos</h2>
        <ol class="breadcrumb">
            <li class="active">
                <strong>Minutas</strong>
            </li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<style type="text/css">
  table.dataTable tbody tr.selected{
    background-color: #d4e4fb;
  }
  .ibox-tools a.btn-success{
    color: #fff;
  }
</style>
    <div class="row" id="minuta">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Registro de minutas</h5>
                    <div class="ibox-tools">
                      <a class="btn btn-success" title="Excel proveedores" id="excel"><i class="fa fa-file"></i> Excel</a>
                      <a class="btn btn-primary" title="Crear minuta" id="btn-crear_minuta"><i class="fa fa-plus"></i> Crear minuta</a>
                    </div>
                </div>
                <div class="ibox-content">
                <!--***** contenido ******-->
                <div class="table-responsive">
                    <table id="tbl-minuta" class="table table-striped table-hover table-bordered" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Creación</th>
                                <th>Minuta</th>
                                <th>Tipo US</th>
                                <th>Cliente</th>
                                <th>Fecha inicio</th>
                                <th>Fecha fin</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>
	    <!-- modal crear minuta -->
	    <div class="modal fade bs-example-modal-lg" id="modalCrearMinuta" tabindex="" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		    <div class="modal-dialog modal-lg">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-file-text"></i> Crear Minuta</h4>
		            </div>
		            <div class="modal-body">
		            	<form id="formMinuta" enctype="multipart/form-data" class="form-horizontal" role="form" autocomplete="off">
			                <div class="row">
		                        <div class="col-lg-8">
	                                    <label for="fechas" class="control-label gcore-label-top">Seleccione la semana:</label>
			                            <div class="input-group">
		                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
		                                    <input class="form-control rango_fecha" type="text" id="fechas" name="fechas" value="" placeholder="mm/dd/aaaa - mm/dd/aaaa"/>
		                                </div>
		                                <small class="help-block" id="ms_fechas"></small>
		                        </div>
		                        <div class="col-lg-4">
	                                <div class="form-group" :class="{ 'has-error': errors.has('tipo_us_id') }">
	                                        <label for="tipo_us_id" class="control-label gcore-label-top">Tipo US:</label>
	                                        <v-select name="tipo_us_id" v-model="tipo_us_id" label="name" :filterable="false" :options="tipo_us" v-validate.disable="'required'" placeholder="Tipo US"></v-select>
	                                        <small class="help-block">@{{ errors.first('tipo_us_id') }}</small>
	                                </div>
		                        </div>
		                    </div>
		                    <div class="row" style="margin-bottom: 10px;">
		                    	<div class="col-lg-12">
		                    		<a class="" data-toggle="collapse" href="#exclusion_fechas" aria-expanded="false" aria-controls="exclusion_fechas">
									  <i class="fa fa-calendar"></i> Excluir fechas
									</a>
									<div class="collapse row" id="exclusion_fechas" style="margin-top: 10px;">
									  <div class="col-sm-3">
									  	<div class="form-group" id="data_1">
										    <div class="input-group date">
			                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="text" class="form-control" id="ex_1" placeholder="mm/dd/aaaa">
			                                </div>
		                                </div>
		                                <input type="text" id="ex_des_1" class="form-control" placeholder="Motivo de exclusión">
									  </div>
									  <div class="col-sm-3">
									    <div class="form-group" id="data_1">
										    <div class="input-group date">
			                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="text" class="form-control" id="ex_2" placeholder="mm/dd/aaaa">
			                                </div>
		                                </div>
		                                <input type="text" id="ex_des_2" class="form-control" placeholder="Motivo de exclusión">
									  </div>
									  <div class="col-sm-3">
									    <div class="form-group" id="data_1">
										    <div class="input-group date">
			                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="text" class="form-control" id="ex_3" placeholder="mm/dd/aaaa">
			                                </div>
		                                </div>
		                                <input type="text" id="ex_des_3" class="form-control" placeholder="Motivo de exclusión">
									  </div>
									  <div class="col-sm-3">
									    <div class="form-group" id="data_1">
										    <div class="input-group date">
			                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="text" class="form-control" id="ex_4" placeholder="mm/dd/aaaa">
			                                </div>
		                                </div>
		                                <input type="text" id="ex_des_4" class="form-control" placeholder="Motivo de exclusión">
									  </div>
									</div>
		                    	</div>
		                    </div>
		                    <div class="row">
		                    	<div class="col-lg-12">
	                                <div class="form-group" :class="{ 'has-error': errors.has('cliente_id') }">
                                        <label for="cliente_id" class="control-label gcore-label-top">Clientes:</label>
                                        <v-select name="cliente_id" v-model="cliente_id" label="name" :options="clientes" v-validate.disable="'required'" placeholder="Cliente"></v-select>
                                        <small class="help-block">@{{ errors.first('cliente_id') }}</small>
                                    </div>
		                        </div>
		                    </div>
		                    <div class="row">
		                    	<div class="col-lg-12">
	                                <div class="form-group" :class="{ 'has-error': errors.has('us_id') }">
                                        <label for="us_id" class="control-label gcore-label-top">Unidades de servicio:</label>
                                        <v-select name="us_id" v-model="us_id" label="name" :options="unidades" v-validate.disable="'required'" placeholder="Unidades de servicio" :disabled="disabled_us" multiple></v-select>
                                        <small class="help-block">@{{ errors.first('us_id') }}</small>
                                    </div>
		                        </div>
		                    </div>
		                    <div class="row">
		                    	<div class="col-lg-12">
	                                <div class="form-group" :class="{ 'has-error': errors.has('menu_id') }">
                                        <label for="menu_id" class="control-label gcore-label-top">Seleccione los menus a utilizar en esta semana:</label>
                                        <v-select name="menu_id" v-model="menu_id" label="name" :options="menu_id.length < 5 ? menus : []" v-validate.disable="'required'" placeholder="Menus" :disabled="disabled_menu"  multiple></v-select>
                                        <small class="help-block">@{{ errors.first('menu_id') }}</small>
                                    </div>
		                        </div>
		                    </div>
	                    </form>
		            </div>
		            <div class="modal-footer">
		                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
		                <button type="button" class="btn btn-primary" @click="store()"><i class="fa fa-save"></i> Crear</button>
		            </div>
		        </div>
		    </div>
		</div>

		<!-- modal imprimir por tipo de producto -->
	    <div class="modal fade bs-example" id="modalTipoProducto" tabindex="" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-tasks"></i> Imprimir pedido por tipo de producto</h4>
		            </div>
		            <div class="modal-body">
		            	<form id="formPrint" enctype="multipart/form-data" class="form-horizontal" role="form" autocomplete="off">
			                <div class="row">
		                    	<div class="col-lg-6">
	                                <div class="form-group">
                                        <label for="produc_type_id" class="control-label gcore-label-top">Tipo de proucto:</label>
                                        <v-select name="produc_type_id" v-model="produc_type_id" label="name" :options="produc_types"  placeholder="Tipo"></v-select>
                                        <small class="help-block">@{{ errors.first('produc_type_id') }}</small>
                                    </div>
		                        </div>
		                        <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="remanencia_tipo_prod">Incluir remanencias</label>
                                        <div class="checkbox checkbox-success checkbox-inline">
                                            <input type="checkbox" id="remanencia_tipo_prod" name="remanencia_tipo_prod" value="t" v-model="remanencia_tipo_prod">
                                            <label for="remanencia_tipo_prod">Restar las remanencias de cada UDS</label>
                                        </div>
                                    </div>
                                </div>
		                    </div>
	                    </form>
		            </div>
		            <div class="modal-footer">
		                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
		                <button type="button" class="btn btn-primary" @click="imprimirPedido()"><i class="fa fa-save"></i> Imprimir</button>
		            </div>
		        </div>
		    </div>
		</div>
    </div>

@endsection

@section('scripts')
<script src="{{ asset('js/templates/minuta/index.js') }}"></script>
@endsection
