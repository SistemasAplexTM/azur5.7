<style type="text/css">
    .exclusion{
        background-color: yellow !important;
    }
</style>
<template>
    <div class="row">
        <div class="col-lg-12">
        	<button type="button" class="btn btn-outline btn-success" @click="viewMenu(unidad)" v-for="unidad in unidades"><i class="fa fa-home"></i> {{ unidad.name }}</button>
		</div>
		<div class="col-lg-12">
			<!-- {{ minuta }} -->
        	<!-- {{ unidades }} -->
        	<div class="table-responsive">
                <table id="tbl-cobertura" class="table table-striped table-hover table-bordered" style="width: 100%;">
        			<thead>
        				<tr>
        					<th colspan="18" class="text-center"><h3><i class="fa fa-home"></i> {{ name_unidad }}</h3></th>
        				</tr>
        				<tr>
        					<th class="text-center" rowspan="4" style="vertical-align: middle;">Ingredientes</th>
        					<th colspan="5">Cobertura: {{ coverage_1_3 }}</th>
        					<th class="text-center" rowspan="4" style="vertical-align: middle;">SUB.TOTAL</th>
        					<th colspan="5">Cobertura: {{ coverage_4_5 }}</th>
                            <th class="text-center" rowspan="4" style="vertical-align: middle;">SUB.TOTAL</th>
                            <th class="text-center" rowspan="4" style="vertical-align: middle;">SUB.TOTA 1</th>
                            <th class="text-center" rowspan="4" style="vertical-align: middle;">SUB.TOTA 2</th>
                            <th class="text-center" rowspan="4" style="vertical-align: middle;">GRAN TOTAL</th>
                            <th class="text-center" rowspan="4" style="vertical-align: middle;">TOTAL EN LIBRAS, LT O UNIDADES</th>
        					<th class="text-center" rowspan="4" style="vertical-align: middle;">PEDIDO</th>
        				</tr>
        				<tr>
        					<th class="text-center" colspan="5">1-3 años 11 meses</th>
        					<th class="text-center" colspan="5">4-5 años 11 meses</th>
        				</tr>
        				<tr>
        					<th class="text-center" colspan="5">Menus</th>
        					<th class="text-center" colspan="5">Menus</th>
        				</tr>
        				<tr>
                            <th class="text-center" v-for="menu in menus" :class="[menu.feriado == 1 ? 'exclusion' : '']">{{ menu.menu }}</th>
                            <th class="text-center" v-for="menu in menus" :class="[menu.feriado == 1 ? 'exclusion' : '']">{{ menu.menu }}</th>
        				</tr>
        			</thead>
        			<tbody>

        			</tbody>
	        	</table>
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
                                        <small class="help-block">{{ errors.first('produc_type_id') }}</small>
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


        <!-- modal agregar remanencias -->
        <div class="modal fade bs-example" id="modalRemanencias" tabindex="" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel"><i class="fa fa-retweet"></i> Remanecnias de {{ name_unidad }}</h4>
                    </div>
                    <div class="modal-body">
                        <form id="formRemanencias" enctype="multipart/form-data" class="form-horizontal" role="form" autocomplete="off">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group" :class="{ 'has-error': errors.has('product_id') }">
                                        <label for="product_id" class="control-label gcore-label-top">Producto:</label>
                                        <v-select name="product_id" placeholder="Producto" v-model="product_id" label="name" :options="products" :on-change="setUnidadMedida" v-validate.disable="'required'"></v-select>
                                        <small class="help-block">{{ errors.first('product_id') }}</small>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group" :class="{ 'has-error': errors.has('cantidad') }">
                                        <label for="cantidad" class="control-label gcore-label-top">Cantidad:</label>
                                        <div class="input-group" >
                                            <span class="input-group-addon" id="unidad_medida">&nbsp;</span>
                                            <input type="number" class="form-control" name="cantidad" v-model="cantidad" placeholder="Cantidad" min="0" v-validate.disable="'required'">
                                        </div>
                                        <small class="help-block">{{ errors.first('cantidad') }}</small>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="form-group">
                                        <label for="descripcion" class="control-label gcore-label-top">Observación:</label>
                                            <input type="text" class="form-control" name="descripcion" v-model="descripcion" placeholder="Observación">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="btn" class="control-label gcore-label-top" style="width: 100%;">&nbsp;</label>
                                        <a @click="saveRemanencia" class="btn btn-primary" data-toggle='tooltip' title="Guardar"><i class="fa fa-save"></i> Guardar</a>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="table-responsive">
                                        <table id="tbl-remanencias" class="table table-striped table-hover" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>UM</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
    	props: {
			minuta: {
		      type: Object,
		      required: true
		    },
            menus: {
              type: Array,
              required: true
            },
		    unidades: {
		      type: Array,
		      required: true
		    },
            name_minuta: {
              type: String,
              required: false
            },
    	},
    	mounted(){
    		const dict = {
                custom: {
                    product_id: {
                        required: 'Este campo es obligatorio.'
                    },
                    cantidad: {
                        required: 'Este campo es obligatorio.'
                    }
                }
            };
            this.$validator.localize('es', dict);
    	},
        data () {
	        return {
	            coverage_1_3: null,
	            coverage_4_5: null,
	            name_unidad: null,
                produc_type_id: null,
                produc_types: [],
                unidad_medida_id: null,
                unidad_medidas: [],
                product_id: null,
                products: [],
                cantidad: 0,
                uds_id: null,
                descripcion: null,
                remanencia_tipo_prod: false,
		    }
		},
        methods:{
            imprimirPedido: function(){
                window.open('../'+ this.minuta.id +'/getPedidoCompleto/' + this.produc_type_id.id + '/' + null + '/' + null + '/' + this.remanencia_tipo_prod, '_blank');
            },
            getProductType: function() {
                let me = this;
                axios.get('../../administracion/tipo_producto/getDataSelect').then(function(response) {
                    me.produc_types = response.data.data;
                }).catch(function(error) {
                    console.log(error);
                    toastr.warning('Error.');
                    toastr.options.closeButton = true;
                });
            },
            viewMenu: function(unidad){
            	this.coverage_1_3 = unidad.coverage_1_3;
            	this.coverage_4_5 = unidad.coverage_4_5;
            	this.name_unidad = unidad.name;
            	if ($.fn.DataTable.isDataTable('#tbl-cobertura')) {
                    $('#tbl-cobertura tbody').empty();
                    $('#tbl-cobertura').dataTable().fnDestroy();
                }
            	this.datatable(unidad.id);
                this.uds_id = unidad.id;
            },
            getUnidadMedida: function() {
                let me = this;
                axios.get('../../administracion/unidad_de_medida/getDataSelect').then(function(response) {
                    me.unidad_medidas = response.data.data;
                }).catch(function(error) {
                    console.log(error);
                    toastr.warning('Error.');
                    toastr.options.closeButton = true;
                });
            },
            getProductos: function() {
                let me = this;
                axios.get('getProductsMinuta/'+ this.uds_id).then(function(response) {
                    me.products = response.data.data;
                }).catch(function(error) {
                    console.log(error);
                    toastr.warning('Error.');
                    toastr.options.closeButton = true;
                });
            },
            setUnidadMedida: function(val) {
                this.product_id = val;
                if (val != null) {
                    $('#unidad_medida').html(val.um);
                }
            },
            getDataRemanecnias(){
                this.getProductos();
                this.datatableRemanencias();
                // this.getUnidadMedida();
            },
            saveRemanencia(){
                let me = this;
                this.$validator.validateAll(['product_id', 'cantidad']).then((result) => {
                    if (result) {
                        axios.post('saveRemanencia', {
                            'minuta_id': this.minuta.id,
                            'unidad_servicio_id': this.uds_id,
                            'product_id': this.product_id.id,
                            'cantidad': this.cantidad,
                            'descripcion': this.descripcion
                        }).then(function(response) {
                            if (response.data['code'] == 200) {
                                toastr.success('Registro creado correctamente.');
                                toastr.options.closeButton = true;
                                refreshTable('tbl-remanencias');
                                me.cantidad = 0;
                                me.descripcion = null;
                            } else {
                                toastr.warning(response.data['error']);
                                toastr.options.closeButton = true;
                            }
                        }).catch(function(error) {
                            console.log(error);
                            toastr.error("Error. - " + error, {
                                timeOut: 50000
                            });
                        });
                    } else {
                        console.log(errors);
                        toastr.warning('Error en la validacion');
                    }
                }).catch(function(error) {
                    console.log(error);
                    toastr.warning('Error al intentar registrar.');
                });
            },
            datatableRemanencias(){
                if ($.fn.DataTable.isDataTable('#tbl-remanencias')) {
                    $('#tbl-remanencias tbody').empty();
                    $('#tbl-remanencias').dataTable().fnDestroy();
                }
                $('#tbl-remanencias').DataTable({
                    ajax: 'getRemanenciasByMinuta/'+ this.uds_id,
                    columns: [{
                        data: 'name',
                        name: 'name'
                    }, {
                        data: 'cantidad',
                        name: 'cantidad'
                    }, {
                        data: 'um',
                        name: 'um'
                    }, {
                        sortable: false,
                        "render": function(data, type, full, meta) {
                            var btn_delete = " <a onclick=\"eliminarRemanencia(" + full.id + "," + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                            return btn_delete;
                        }
                    }]
                });
            },
            datatable: function(id_us){
                let me = this;
            	$('#tbl-cobertura').DataTable({
                    dom: "<'row'<'col-sm-9 text-right'f><'col-sm-3 text-right'B><'floatright'>rtip>",
                    buttons: [
                        {
                            extend: 'print',
                            text: '<i class="fa fa-retweet" aria-hidden="true"></i> Remanencias',
                            titleAttr: 'Imprimir',
                            action: function ( e, dt, node, config ) {
                                $('#modalRemanencias').modal('show');
                                me.getDataRemanecnias();
                            }
                        },
                        {
                            extend: 'collection',
                            text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir <i class="fa fa-angle-double-down" aria-hidden="true"></i>',
                            buttons: [
                                {
                                    text: '<i class="fa fa-print" aria-hidden="true"></i> Pedido completo',
                                    action: function ( e, dt, node, config ) {
                                        window.open("../"+me.minuta.id+"/getPedidoCompleto");
                                    }
                                },
                                {
                                    text: '<i class="fa fa-print" aria-hidden="true"></i> Completo con remanencias',
                                    action: function ( e, dt, node, config ) {
                                        window.open("../"+me.minuta.id+"/getPedidoCompleto/"+ null + "/"+ null + "/"+ null + "/" + true);
                                    }
                                },
                                {
                                    text: '<i class="fa fa-print" aria-hidden="true"></i> Pedido solo UDS',
                                    action: function ( e, dt, node, config ) {
                                        window.open("../"+me.minuta.id+"/getPedidoCompleto/"+ null + "/" + id_us + "/" + me.name_minuta);
                                    }
                                },
                                {
                                    text: '<i class="fa fa-tasks"></i> Por tipo de producto',
                                    action: function ( e, dt, node, config ) {
                                        me.getProductType();
                                        $('#modalTipoProducto').modal('show');
                                    }
                                }
                            ]
                        }

                    ],
                    "paging":   false,
    		        ajax: 'getMenusUnidadesByMinuta/'+id_us,
    		        columns: [{
    		            data: 'producto',
    		            name: 'producto'
    		        }, {
    		            data: '1',
    		            name: '1'
    		        }, {
    		            data: '2',
    		            name: '2'
    		        }, {
    		            data: '3',
    		            name: '3'
    		        }, {
    		            data: '4',
    		            name: '4'
    		        }, {
    		            data: '5',
    		            name: '5'
    		        }, {
    		            data: 'st-1',
    		            name: 'st-1'
    		        }, {
    		            data: '6',
    		            name: '6'
    		        }, {
    		            data: '7',
    		            name: '7'
    		        }, {
    		            data: '8',
    		            name: '8'
    		        }, {
    		            data: '9',
    		            name: '9'
    		        }, {
    		            data: '10',
    		            name: '10'
    		        }, {
    		            data: 'st-2',
    		            name: 'st-2'
    		        }, {
                        data: 'st-3',
                        name: 'st-3'
                    }, {
                        data: 'st-4',
                        name: 'st-4'
                    }, {
                        data: 'st-5',
                        name: 'st-5'
                    }, {
                        data: 'cantidad',
                        name: 'cantidad'
                    }, {
                        data: 'presentacion',
                        name: 'presentacion'
                    }],
                    'columnDefs': [
                        { className: "", "targets": [ 0 ], width: 200, }
                    ],
                    'rowCallback': function(row, data, index){
                        $(row).find('td:eq(6)').css('color', 'navy').css('font-weight', 'bold').css('font-size', '15px').css('border-color', 'blue');
                        $(row).find('td:eq(12)').css('color', 'orange').css('font-weight', 'bold').css('font-size', '15px').css('border-color', 'orange');

                        $(row).find('td:eq(13)').css('color', 'navy').css('font-weight', 'bold').css('font-size', '15px');
                        $(row).find('td:eq(14)').css('color', 'orange').css('font-weight', 'bold').css('font-size', '15px');

                        $(row).find('td:eq(15)').css('color', 'black').css('font-weight', 'bold').css('font-size', '15px').css('background-color','rgb(209, 243, 209)');
                        $(row).find('td:eq(16)').css('color', 'black').css('font-weight', 'bold').css('font-size', '15px').css('background-color','rgb(209, 243, 209)');
                        $(row).find('td:eq(17)').css('color', 'black').css('font-weight', 'bold').css('font-size', '15px').css('background-color','rgb(209, 243, 209)');
                    // if(data[2].toUpperCase() == 'EE'){
                    //     $(row).find('td:eq(2)').css('color', 'blue');
                    // }
    		        }
                });
            }
	    }
    }
</script>
