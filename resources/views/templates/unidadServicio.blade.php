@extends('layouts.app')
@section('title', 'Unidad de servicio')
@section('breadcrumb')
{{-- bread crumbs --}}
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Administración</h2>
        <ol class="breadcrumb">
            <li class="active">
                <strong>Unidad de servicio</strong>
            </li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<style type="text/css">
</style>
    <div class="row" id="unidadServicio">
        <form id="formUnidadServicio" enctype="multipart/form-data" class="form-horizontal" role="form">
            <div class="col-lg-5">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Registro de unidades de servicio</h5>
                        <div class="ibox-tools">
                            
                        </div>
                    </div>
                    <div class="ibox-content">
                        <!--***** contenido ******-->
                        <div class="row">                            
                            <div class="col-lg-12">
                                    <div class="form-group" :class="{ 'has-error': errors.has('cliente_id') }">
                                        <div class="col-sm-4">
                                            <label for="cliente_id" class="control-label gcore-label-top">Clientes:</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <v-select name="cliente_id" v-model="cliente_id" label="name" :filterable="false" :options="clientes" v-validate.disable="'required'" placeholder="Cliente"></v-select>
                                            <small class="help-block">@{{ errors.first('cliente_id') }}</small>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                    <div class="form-group" :class="{ 'has-error': errors.has('tipo_us_id') }">
                                        <div class="col-sm-4">
                                            <label for="tipo_us_id" class="control-label gcore-label-top">Tipo US:</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <v-select name="tipo_us_id" v-model="tipo_us_id" label="name" :filterable="false" :options="tipo_us" v-validate.disable="'required'" placeholder="Tipo US"></v-select>
                                            <small class="help-block">@{{ errors.first('tipo_us_id') }}</small>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                    <div class="form-group" :class="{ 'has-error': errors.has('name') }">
                                        <div class="col-sm-4">
                                            <label for="name" class="control-label gcore-label-top">Nombre:</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input v-model="name" name="name" placeholder="Nombre" class="form-control" type="text" v-validate.disable="'required'"/>
                                            <small class="help-block">@{{ errors.first('name') }}</small>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                    <div class="form-group" :class="{ 'has-error': errors.has('address') }">
                                        <div class="col-sm-4">
                                            <label for="address" class="control-label gcore-label-top">Dirección:</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input v-model="address" name="address" placeholder="Dirección" class="form-control" type="text" v-validate.disable="'required'">
                                            <small class="help-block">@{{ errors.first('address') }}</small>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                    <div class="form-group" :class="{ 'has-error': errors.has('phone') }">
                                        <div class="col-sm-4">
                                            <label for="phone" class="control-label gcore-label-top">Teléfono:</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input v-model="phone" name="phone" placeholder="Teléfono" class="form-control" type="text" v-validate.disable="'required'">
                                            <small class="help-block">@{{ errors.first('phone') }}</small>
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
                    <h5>Unidades de servicio</h5>
                    <div class="ibox-tools">

                    </div>
                </div>
                <div class="ibox-content">
                    <!--***** contenido ******-->
                    <div class="table-responsive">
                        <table id="tbl-unidadServicio" class="table table-striped table-hover table-bordered" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Nombre</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                    <th>Cobertura</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Nombre</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                    <th>Cobertura</th>
                                    <th>Acciones</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>             
                </div>
            </div>
        </div>
        <div class="modal fade" tabindex="-1" role="dialog" id="md-grupo_edad">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Grupo edad</h4>
              </div>
              <div class="modal-body">
                <h3>Seleccione un grupo edad de la lista e ingrese la covertura.</h3>
                <div class="row">                        
                    <div class="col-lg-5">
                        <div class="form-group">
                            <label for="age_group_id" class="control-label gcore-label-top">Grupo de edad:</label>
                            <v-select name="age_group_id" placeholder="Grupo edad" v-model="age_group_id" label="name" :filterable="false" :options="age_groups"></v-select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="coverage" class="control-label gcore-label-top">Covertura:</label>
                            <input v-model="coverage" name="coverage" placeholder="Cobertura" class="form-control" type="text">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="btn" class="control-label" style="width: 100%;">&nbsp;</label>
                            <a class="btn btn-info" data-toggle="tooltip" title="Agregar" @click="addGrupoEtareo()"><i class="fa fa-plus"></i> Agregar</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <table id="tbl-grupoEtareo" class="table table-striped table-hover" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Grupo edad</th>
                                    <th>Covertura</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="grupo in grupoEtareo">
                                    <td>@{{ grupo.grupo_edad }}</td>
                                    <td><a class="td_edit" data-name="coverage" v-bind:data-pk="grupo.id">@{{ grupo.coverage }}</a></td>
                                    <td><a class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar' @click="deleteGrupoEdad(grupo.id)"><i class='fa fa-trash'></i></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/templates/unidadServicio.js') }}"></script>
@endsection