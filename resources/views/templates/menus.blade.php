@extends('layouts.app')
@section('title', 'Menus')
@section('breadcrumb')
{{-- bread crumbs --}}
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Administración</h2>
        <ol class="breadcrumb">
            <li class="active">
                <strong>Menus</strong>
            </li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<style type="text/css">
</style>
    <div class="row" id="menus">
        <form id="formMenus" enctype="multipart/form-data" class="form-horizontal" role="form">
        	<input type="hidden" name="id" v-model="id">
            <div class="col-lg-7">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Registro de menus</h5>
                        <div class="ibox-tools">
                            
                        </div>
                    </div>
                    <div class="ibox-content">
                        <!--***** contenido ******-->
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group" :class="{ 'has-error': errors.has('cliente_id') }">
                                    <label for="cliente_id" class="control-label gcore-label-top">Cliente:</label>
                                    <v-select name="cliente_id" placeholder="Cliente" v-model="cliente_id" label="name" :filterable="false" :options="cliente" v-validate.disable="'required'"></v-select>
                                    <small class="help-block">@{{ errors.first('cliente_id') }}</small>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group" :class="{ 'has-error': errors.has('tipo_us_id') }">
                                        <label for="tipo_us_id" class="control-label gcore-label-top">Tipo US:</label>
                                        <v-select name="tipo_us_id" v-model="tipo_us_id" label="name" :filterable="false" :options="tipo_us" v-validate.disable="'required'" placeholder="Tipo US"></v-select>
                                        <small class="help-block">@{{ errors.first('tipo_us_id') }}</small>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group" :class="{ 'has-error': errors.has('name') }">
                                    <label for="name" class="control-label gcore-label-top">Nombre:</label>
                                    <input v-model="name" name="name" placeholder="Nombre del menu" class="form-control" type="text" v-validate.disable="'required'"/>
                                    <small class="help-block">@{{ errors.first('name') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                        	<div class="hr-line-dashed" style="margin-bottom: 5px;margin-top: 5px"></div>
                        	<div class="col-lg-12"><h2>Detalle del menú</h2></div>
                        </div>
                        <div class="row">
                        	<div class="col-lg-5">
                        		<v-select name="product_id" placeholder="Producto" v-model="product_id" label="name" :options="products" :on-change="setUnidadMedida"></v-select>
                        	</div>
                        	<div class="col-lg-3">
                                <v-select name="age_group_id" placeholder="Grupo edad" v-model="age_group_id" label="name" :filterable="false" :options="age_groups"></v-select>
                        	</div>
                            <div class="col-lg-3">
                                <div class="input-group">
                                    <span class="input-group-addon" id="unidad_medida">Kl</span>
                                    <input type="number" class="form-control" name="peso" v-model="peso" placeholder="Cantidad" min="0">
                                </div>
                            </div>
                        	<div class="col-lg-1">
                        		<a type="button" class="btn btn-primary" data-toggle="tooltip" title="Agregar" @click="addMenuDetail()"><i class="fa fa-plus"></i></a>
                        	</div>
                        	<div class="col-lg-12">
                        		<div class="table-responsive" style="margin-top: 20px;">
			                        <table id="tbl-menus_detalle" class="table table-striped table-hover" style="width: 100%;">
			                            <thead>
			                                <tr>
			                                    <th>Producto</th>
                                                <th>Grupo edad/Cantidad</th>
			                                    <th>U.M</th>
			                                    <th></th>
			                                </tr>
			                            </thead>
			                        </table>
			                    </div> 
                        	</div>
                        </div>
                        
                        <div class="row">
                            @include('layouts.buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Menus</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content">
                        <!--***** contenido ******-->
                        <div class="row">
                        <!-- Nav tabs -->
                          <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" :class="{ 'active': cdi_menu }"><a href="#cdi" aria-controls="cdi" role="tab" data-toggle="tab">CDI</a></li>
                            <li role="presentation" :class="{ 'active': hcb_menu }"><a href="#hcb" aria-controls="hcb" role="tab" data-toggle="tab">HCB</a></li>
                          </ul>
                          <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade in" :class="{ 'active': cdi_menu }" id="cdi">
                                <div class="col-lg-12" style="margin-top: 20px;">
                                    <div class="table-responsive">
                                        <table id="tbl-menus_cdi" class="table table-striped table-hover table-bordered" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Tipo UDS</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane fade in" :class="{ 'active': hcb_menu }" id="hcb">
                                <div class="col-lg-12" style="margin-top: 20px;">
                                    <div class="table-responsive">
                                        <table id="tbl-menus_hcb" class="table table-striped table-hover table-bordered" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Tipo UDS</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>            
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/templates/menus.js') }}"></script>
@endsection