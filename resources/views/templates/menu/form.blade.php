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
    <div class="col-lg-12">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5>Registro de menus</h5>
          <div class="ibox-tools">
            <button type="button" class="btn btn-success" title="Menus" data-toggle="modal" data-target="#modalList"
              id="menus"><i class="fa fa-file-excel-o"></i> Menus Registrados</button>
          </div>
        </div>
        <div class="ibox-content">
          <!--***** contenido ******-->
          <div class="row">
            <div class="col-lg-5">
              <div class="form-group" :class="{ 'has-error': errors.has('cliente_id') }">
                <label for="cliente_id" class="control-label gcore-label-top">Cliente:</label>

                <el-select v-model="cliente_id" clearable placeholder="Cliente" value-key="id">
                  <el-option v-for="item in cliente" :key="item.id" :label="item.name" :value="item">
                  </el-option>
                </el-select>

                {{-- <v-select name="cliente_id" placeholder="Cliente" v-model="cliente_id" label="name" :filterable="false"
                  :options="cliente" v-validate.disable="'required'"></v-select> --}}
                <small class="help-block">@{{ errors.first('cliente_id') }}</small>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group" :class="{ 'has-error': errors.has('tipo_us_id') }">
                <label for="tipo_us_id" class="control-label gcore-label-top">Tipo US:</label>
                <el-select v-model="tipo_us_id" clearable placeholder="Tipo US" value-key="id">
                  <el-option v-for="item in tipo_us" :key="item.id" :label="item.name" :value="item">
                  </el-option>
                </el-select>
                {{-- <v-select name="tipo_us_id" v-model="tipo_us_id" label="name" :filterable="false" :options="tipo_us"
                  v-validate.disable="'required'" placeholder="Tipo US"></v-select> --}}
                <small class="help-block">@{{ errors.first('tipo_us_id') }}</small>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group" :class="{ 'has-error': errors.has('name') }">
                <label for="name" class="control-label gcore-label-top">Nombre:</label>
                <el-input placeholder="Nombre del menu" v-model="name" clearable>
                </el-input>
                {{-- <input v-model="name" name="name" placeholder="Nombre del menu" class="form-control" type="text"
                  v-validate.disable="'required'" /> --}}
                <small class="help-block">@{{ errors.first('name') }}</small>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="hr-line-dashed" style="margin-bottom: 5px;margin-top: 5px"></div>
            <div class="col-lg-12">
              <h2>Detalle del menú</h2>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-5">
              <el-select v-model="product_id" clearable placeholder="Producto" value-key="id" @change="setUnidadMedida">
                <el-option v-for="item in products" :key="item.id" :label="item.name" :value="item">
                </el-option>
              </el-select>
              {{-- <v-select name="product_id" placeholder="Producto" v-model="product_id" label="name" :options="products"
                :on-change="setUnidadMedida"></v-select> --}}
            </div>
            <div class="col-lg-3">
              <el-select v-model="age_group_id" clearable placeholder="Grupo edad" value-key="id">
                <el-option v-for="item in age_groups" :key="item.id" :label="item.name" :value="item">
                </el-option>
              </el-select>
              {{-- <v-select name="age_group_id" placeholder="Grupo edad" v-model="age_group_id" label="name"
                :filterable="false" :options="age_groups"></v-select> --}}
            </div>
            <div class="col-lg-3">
              <el-input placeholder="Cantidad" v-model="peso">
                <template slot="prepend">@{{ prefix_um }}</template>
              </el-input>

              {{-- <div class="input-group">
                <span class="input-group-addon" id="unidad_medida">Kl</span>
                <input type="number" class="form-control" name="peso" v-model="peso" placeholder="Cantidad" min="0">
              </div> --}}
            </div>
            <div class="col-lg-1">
              <el-button type="success" round title="Agregar" @click="addMenuDetail()"><i class="fa fa-plus"></i>
              </el-button>
              {{-- <a type="button" class="btn btn-primary" data-toggle="tooltip" title="Agregar" @click="addMenuDetail()"><i
                  class="fa fa-plus"></i></a> --}}
            </div>
            <div class="col-lg-12">
              <div class="table-responsive" style="margin-top: 20px;">
                <table id="tbl-menus_detalle" class="table table-striped table-hover" style="width: 100%;">
                  {{-- <thead>
                    <tr>
                      <th>Producto</th>
                      <th>Grupo edad/Cantidad</th>
                      <th>U.M</th>
                      <th>U.M Pedido</th>
                      <th></th>
                    </tr>
                  </thead> --}}
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
  </form>
  @include('templates.menu.list')
</div>

@endsection
@section('scripts')
<script src="{{ asset('js/templates/menus.js') }}"></script>
@endsection