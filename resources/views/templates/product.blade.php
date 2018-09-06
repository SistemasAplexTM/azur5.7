@extends('layouts.app')
@section('title', 'Productos')
@section('breadcrumb')
{{-- bread crumbs --}}
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Administración</h2>
        <ol class="breadcrumb">
            <li class="active">
                <strong>Productos</strong>
            </li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<style type="text/css">
</style>
    <div class="row" id="product">
        <form id="formProduct" enctype="multipart/form-data" class="form-horizontal" role="form">
            <div class="col-lg-5">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Registro de productos</h5>
                        <div class="ibox-tools">
                            
                        </div>
                    </div>
                    <div class="ibox-content">
                        <!--***** contenido ******-->
                        <div class="row">                            
                            <div class="col-lg-12">
                                    <div class="form-group" :class="{ 'has-error': errors.has('name') }">
                                        <div class="col-sm-5">
                                            <label for="name" class="control-label gcore-label-top">Nombre:</label>
                                        </div>
                                        <div class="col-sm-7">
                                            <input v-model="name" name="name" placeholder="Nombre" class="form-control" type="text" v-validate.disable="'required'"/>
                                            <small class="help-block">@{{ errors.first('name') }}</small>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                    <div class="form-group" :class="{ 'has-error': errors.has('description') }">
                                        <div class="col-sm-5">
                                            <label for="description" class="control-label gcore-label-top">Descripcion:</label>
                                        </div>
                                        <div class="col-sm-7">
                                            <input v-model="description" name="description" placeholder="Descripción" class="form-control" type="text">
                                            <small class="help-block">@{{ errors.first('description') }}</small>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                    <div class="form-group" :class="{ 'has-error': errors.has('unidad_medida_id') }">
                                        <div class="col-sm-5">
                                            <label for="unidad_medida_id" class="control-label gcore-label-top">Unidad de medida:</label>
                                        </div>
                                        <div class="col-sm-7">
                                            <v-select name="unidad_medida_id" v-model="unidad_medida_id" label="name" :filterable="false" :options="unidad_medidas" v-validate.disable="'required'" placeholder="U. medida"></v-select>
                                            <small class="help-block">@{{ errors.first('unidad_medida_id') }}</small>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                    <div class="form-group" :class="{ 'has-error': errors.has('conversion') }">
                                        <div class="col-sm-5">
                                            <label for="conversion" class="control-label gcore-label-top">Conversión:</label>
                                        </div>
                                        <div class="col-sm-7">
                                            <input v-model="conversion" name="conversion" placeholder="Conversión" class="form-control" type="text">
                                            <small class="help-block">@{{ errors.first('conversion') }}</small>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                    <div class="form-group" :class="{ 'has-error': errors.has('tipo_producto_id') }">
                                        <div class="col-sm-5">
                                            <label for="tipo_producto_id" class="control-label gcore-label-top">Tipo de producto:</label>
                                        </div>
                                        <div class="col-sm-7">
                                            <v-select name="tipo_producto_id" v-model="tipo_producto_id" label="name" :filterable="false" :options="tipo_producto" v-validate.disable="'required'" placeholder="Tipo producto"></v-select>
                                            <small class="help-block">@{{ errors.first('tipo_producto_id') }}</small>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            @include('layouts.buttons')
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="col-lg-7">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Productos</h5>
                    <div class="ibox-tools">

                    </div>
                </div>
                <div class="ibox-content">
                    <!--***** contenido ******-->
                    <div class="table-responsive">
                        <table id="tbl-product" class="table table-striped table-hover table-bordered" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Unidad de medida</th>
                                    <th>Tipo producto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Unidad de medida</th>
                                    <th>Tipo producto</th>
                                    <th>Acciones</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>             
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/templates/product.js') }}"></script>
@endsection