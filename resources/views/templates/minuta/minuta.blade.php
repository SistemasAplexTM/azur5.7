@extends('layouts.app')
@section('title', 'Minutas')
@section('breadcrumb')
@endsection

@section('content')
<style type="text/css">
</style>
    <div class="row" id="minuta">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>{{ $minuta->name_minuta }}{{ $minuta->mes }}</h5>
                </div>
                <div class="ibox-content">
                <!--***** contenido ******-->
                	<minuta-component :minuta="{{ json_encode($minuta) }}" :menus="{{ json_encode($menus) }}" :unidades="{{ json_encode($unidades) }}" :name_minuta="{{ json_encode($minuta->name_minuta . $minuta->mes) }}"></minuta-component>
                        
            </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script src="{{ asset('js/templates/minuta/minuta.js') }}"></script>
@endsection