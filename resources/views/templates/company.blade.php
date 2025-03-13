@extends('layouts.app')
@section('title', 'Company')
@section('breadcrumb')
{{-- bread crumbs --}}
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Administración</h2>
        <ol class="breadcrumb">
            <li class="active">
                <strong>Company</strong>
            </li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row" id="company">
    <form id="formCompany" enctype="multipart/form-data" class="form-horizontal" role="form">
        <div class="col-lg-5">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Registro de empresa</h5>
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
                            <div class="form-group" :class="{ 'has-error': errors.has('nit') }">
                                <div class="col-sm-5">
                                    <label for="nit" class="control-label gcore-label-top">Nit:</label>
                                </div>
                                <div class="col-sm-7">
                                    <input v-model="nit" name="nit" placeholder="Nit" class="form-control" type="text">
                                    <small class="help-block">@{{ errors.first('nit') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">                            
                        <div class="col-lg-12">
                            <div class="form-group" :class="{ 'has-error': errors.has('address') }">
                                <div class="col-sm-5">
                                    <label for="address" class="control-label gcore-label-top">Dirección:</label>
                                </div>
                                <div class="col-sm-7">
                                    <input v-model="address" name="address" placeholder="Dirección" class="form-control" type="text" v-validate.disable="'required'">
                                    <small class="help-block">@{{ errors.first('address') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">                            
                        <div class="col-lg-12">
                            <div class="form-group" :class="{ 'has-error': errors.has('phone') }">
                                <div class="col-sm-5">
                                    <label for="phone" class="control-label gcore-label-top">Teléfono:</label>
                                </div>
                                <div class="col-sm-7">
                                    <input v-model="phone" name="phone" placeholder="Teléfono" class="form-control" type="text" v-validate.disable="'required'">
                                    <small class="help-block">@{{ errors.first('phone') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">                            
                        <div class="col-lg-12">
                            <div class="form-group" :class="{ 'has-error': errors.has('logo') }">
                                <div class="col-sm-5">
                                    <label for="logo" class="control-label gcore-label-top">Logo:</label>
                                </div>
                                <div class="col-sm-7">
                                    <input v-model="logo" name="logo" class="form-control" type="file">
                                    <small class="help-block">@{{ errors.first('logo') }}</small>
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
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/templates/company.js') }}"></script>
@endsection
