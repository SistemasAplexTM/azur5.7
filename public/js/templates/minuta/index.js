$(document).ready(function() {
    $('#data_1 .input-group.date').datepicker({
        language: 'es',
        todayBtn: "linked",
        keyboardNavigation: false,
        calendarWeeks: true,
        autoclose: true,
        format: 'mm/dd/yyyy',
    });
    $('.rango_fecha').daterangepicker({
        "locale": {
            "applyLabel": "Aplicar",
            "cancelLabel": "Cancelar",
            "fromLabel": "Desde",
            "toLabel": "Hasta",
            "customRangeLabel": "Custom",
            "daysOfWeek": ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
            "monthNames": ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio",
                "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            "firstDay": 1
        }
    });
    $('#btn-crear_minuta').on('click', function(){
        $('#modalCrearMinuta').modal('show');
        objVue.setSelects();
    });
});
$(window).load(function() {
    $('#tbl-minuta').DataTable({
        ajax: 'minuta/all',
        "order": [[ 0, "desc" ]],
        columns: [{
            data: 'creacion',
            name: 'creacion'
        },{
            "render": function(data, type, full, meta) {
                return '<strong>' + full.name_minuta + ' ' + full.mes + '</strong><div style="font-size: x-small;">'+full.uds+'</div>';
            }
        }, {
            data: 'tipo_unidad_servicio',
            name: 'tipo_unidad_servicio'
        }, {
            data: 'cliente',
            name: 'cliente'
        }, {
            data: 'fecha_inicio',
            name: 'fecha_inicio'
        }, {
            data: 'fecha_fin',
            name: 'fecha_fin'
        }, {
            sortable: false,
            "render": function(data, type, full, meta) {
                var btn_edit = "<a href='minuta/"+full.id+"/edit' class='btn btn-outline btn-success btn-xs' data-toggle='tooltip' data-placement='top' title='Ver mirnuta'><i class='fa fa-eye'></i></a> ";
                var btn_delete = " <a onclick=\"eliminar(" + full.id + "," + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                var btn_print = ' <div class="btn-group">'+
                                  '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'+
                                   '<i class="fa fa-print"></i> <span class="caret"></span>'+
                                  '</button>'+
                                  '<ul class="dropdown-menu dropdown-menu-right pull-right">'+
                                   ' <li><a href="minuta/'+full.id+'/getPedidoCompleto" target="_blank"><i class="fa fa-clipboard"></i> Pedido completo</a></li>'+
                                    '<li><a onclick="printForProductType('+full.id+')"><i class="fa fa-tasks"></i> Por tipo de producto</a></li>'+
                                  '</ul>'+
                                '</div> ';
                return btn_edit + btn_print + btn_delete;
            }
        }],
        'columnDefs': [
            {"targets": [ 1 ], width: 500, }
        ],
    });
});

function workingDays(dateFrom, dateTo) {
  var from = moment(dateFrom, 'DD/MM/YYY'),
    to = moment(dateTo, 'DD/MM/YYY'),
    days = 0;
    
  while (!from.isAfter(to)) {
    // Si no es sabado ni domingo
    if (from.isoWeekday() !== 6 && from.isoWeekday() !== 7) {
      days++;
    }
    from.add(1, 'days');
  }
  return days;
}

function printForProductType(minuta_id){
    $('#modalTipoProducto').modal('show');
    objVue.getProductType(minuta_id);
}

var objVue = new Vue({
    el: '#minuta',
    mounted: function() {
        const dict = {
            custom: {
                tipo_us_id: {
                    required: 'Este campo es obligatorio.'
                },
                cliente_id: {
                    required: 'Este campo es obligatorio.'
                },
                us_id: {
                    required: 'Este campo es obligatorio.'
                },
                menu_id: {
                    required: 'Este campo es obligatorio.'
                }
            }
        };
        this.$validator.localize('es', dict);
    },
    watch:{
        cliente_id:function(value){
            this.setUnidades();
            this.setMenus();
            this.menu_id = [];
            this.us_id = null;
        },
        tipo_us_id:function(value){
            this.setUnidades();
            this.setMenus();
            this.menu_id = [];
            this.us_id = null;
        },
    },
    data: {
        tipo_us_id: null,
        cliente_id: null,
        clientes: [],
        produc_type_id: null,
        produc_types: [],
        tipo_us_id: null,
        tipo_us: [],
        menu_id: [],
        menus: [],
        us_id: null,
        unidades: [],
        disabled_us: true,
        disabled_menu: true,
        minuta_id_print: null,
    },
    methods: {
        setSelects: function(){
            this.getClientes();
            this.getTipoUnidadServicio();
        },
        setMenus: function(){
            if(this.tipo_us_id != null && this.cliente_id != null){
                this.disabled_menu = false;
                this.getMenus();
            }else{
                this.disabled_menu = true;
                this.menu_id = [];
            }
        },
        setUnidades: function(){
            if(this.tipo_us_id != null && this.cliente_id != null){
                this.disabled_us = false;
                this.getUnidades();
            }else{
                this.disabled_us = true;
                this.us_id = null;
            }
        },
        rollBackDelete: function(data) {
            var urlRestaurar = 'minuta/restaurar/' + data.id;
            axios.get(urlRestaurar).then(response => {
                toastr.success('Registro restaurado.');
                refreshTable('tbl-minuta');
            });
        },
        delete: function(data) {
            axios.delete('minuta/' + data.id).then(response => {
                refreshTable('tbl-minuta');
                toastr.success("<div><p>Registro eliminado exitosamente.</p><button type='button' onclick='deshacerEliminar(" + data.id + ")' id='okBtn' class='btn btn-xs btn-danger pull-right'><i class='fa fa-reply'></i> Restaurar</button></div>");
                toastr.options.closeButton = true;
            });
        },
        store: function() {
            $('#ms_fechas').html('');
            $('#ms_fechas').parent().removeClass('has-error');
            if($('#fechas').val() == ''){
                $('#ms_fechas').html('Este campo es obligatorio.');
                $('#ms_fechas').parent().addClass('has-error');
                return false;
            }
            var dates = $('#fechas').val();
            dates = dates.split("-");
            var fecha1 = moment(dates[0].trim());
            var fecha2 = moment(dates[1].trim());
            /* VALIDO QUE NO SELECCIONE MAS DE 5 DIAS O MENOS DE 5 DIAS */
            // if(fecha2.diff(fecha1, 'days') != 4){//esta era la primer forma de validar los 5 dias
            // console.log(workingDays(fecha1, fecha2));
            if(workingDays(fecha1, fecha2) != 5){
                $('#ms_fechas').html('Debe seleccionar un rango de fechas no mayor ni menor a 5 dias. (dias seleccionados: '+workingDays(fecha1, fecha2)+')');
                $('#ms_fechas').parent().addClass('has-error');
                return false;
            }

            this.$validator.validateAll().then((result) => {
                if (result) {
                    let me = this;
                    var fechas = $('#fechas').val();
                    var arr = fechas.split("-");
                    var fecha_inicio = moment(arr[0]).format('YYYY-MM-DD');
                    var fecha_fin = moment(arr[1]).format('YYYY-MM-DD');
                    var fechas_exclusiones = [
                        ($('#ex_1').val() != '') ? moment($('#ex_1').val()).format('YYYY-MM-DD') : '',
                        ($('#ex_2').val() != '') ? moment($('#ex_2').val()).format('YYYY-MM-DD') : '',
                        ($('#ex_3').val() != '') ? moment($('#ex_3').val()).format('YYYY-MM-DD') : '',
                        ($('#ex_4').val() != '') ? moment($('#ex_4').val()).format('YYYY-MM-DD') : ''
                    ]
                    var motivo_exclusiones = [
                        $('#ex_des_1').val(),
                        $('#ex_des_2').val(),
                        $('#ex_des_3').val(),
                        $('#ex_des_4').val()
                    ]
                    axios.post('minuta', {
                        'tipo_unidad_servicio_id': this.tipo_us_id.id,
                        'clientes_id': this.cliente_id.id,
                        'fecha_inicio': fecha_inicio,
                        'fecha_fin': fecha_fin,
                        'unidades': this.us_id,
                        'menus': this.menu_id,
                        'exclusiones' : fechas_exclusiones,
                        'exclusiones_motivo' : motivo_exclusiones,
                    }).then(function(response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro creado correctamente.');
                            toastr.options.closeButton = true;
                            refreshTable('tbl-minuta');
                            $('#modalCrearMinuta').modal('hide');

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
        getProductType: function(minuta_id) {
            let me = this;
            this.minuta_id_print = minuta_id;
            axios.get('administracion/tipo_producto/getDataSelect').then(function(response) {
                me.produc_types = response.data.data;
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        getClientes: function() {
            let me = this;
            axios.get('clientes/getDataSelect').then(function(response) {
                me.clientes = response.data.data;
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        getUnidades: function() {
            let me = this;
            axios.get('unidadServicio/getDataByCliente/'+me.cliente_id.id+'/'+me.tipo_us_id.id).then(function(response) {
                me.unidades = response.data.data;
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        getTipoUnidadServicio: function() {
            let me = this;
            axios.get('administracion/tipo_unidad_servicio/getDataSelect').then(function(response) {
                me.tipo_us = response.data.data;
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        getMenus: function() {
            let me = this;
            axios.get('menus/getDataSelect/'+me.tipo_us_id.id).then(function(response) {
                me.menus = response.data.data;
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        imprimirPedido: function(){
            window.open('minuta/'+this.minuta_id_print+'/getPedidoCompleto/' + this.produc_type_id.id, '_blank');
        }
    },
});