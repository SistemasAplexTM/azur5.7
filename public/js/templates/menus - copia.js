$(document).ready(function() {
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.params = function (params) {
        params._token = $('meta[name="csrf-token"]').attr('content');
        return params;
    };
});
$(window).load(function() {
    $('#tbl-menus').DataTable({
        ajax: 'menus/all',
        columns: [{
            data: 'name',
            name: 'name'
        }, {
            sortable: false,
            "render": function(data, type, full, meta) {
                var btns = '';
                var params = [
                    full.id, "'" + full.name + "'", full.cliente_id, "'" + full.cliente + "'"
                ];
                if(permission_update){
                    var btn_edit = "<a onclick=\"edit(" + params + ")\" class='btn btn-outline btn-success btn-xs' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fa fa-edit'></i></a> ";
                    btns += btn_edit;
                }
                if(permission_delete){
                    var btn_delete = " <a onclick=\"eliminar(" + full.id + "," + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                    btns += btn_delete;
                }
                return btns;
            }
        }]
    });
});

function edit(id, name, cliente_id, cliente) {
    var data = {
        id: id,
        name: name,
        cliente_id: cliente_id,
        cliente: cliente
    };
    objVue.edit(data);
}

function eliminarDetalle(id,op){
    var data = {
        id: id,
        logical: op
    };
    objVue.delete(data, 'detail');
}

function rollBackDelete(id,table){
    var data = {
        id: id
    };
    objVue.rollBackDelete(data, table);
}

var objVue = new Vue({
    el: '#menus',
    mounted: function() {
        this.getProductos();
        this.getClientes();
        const dict = {
            custom: {
                name: {
                    required: 'El nombre es obligatorio.'
                },
                age_group_id: {
                    required: 'El grupo de edad es obligatorio.'
                }
            }
        };
        this.$validator.localize('es', dict);
    },
    data: {
        id: null,
        name: null,
        product_id: null,
        peso_1_3: null,
        peso_4_5: null,
        cliente_id: null,
        cliente: [],
        products: [],
        editar: 0
    },
    methods: {
        resetForm: function() {
            this.id = null;
            this.name = null;
            this.peso_1_3 = null;
            this.peso_4_5 = null;
            this.cliente_id = null;
            this.product_id = null;
            this.editar = 0;
            this.errors.clear();
            if ($.fn.DataTable.isDataTable('#tbl-menus_detalle')) {
                $('#tbl-menus_detalle tbody').empty();
                $('#tbl-menus_detalle').dataTable().fnDestroy();
                $("#tbl-menus_detalle tbody tr").remove();
            }
        },
        rollBackDelete: function(data, table) {
            var urlRestaurar = 'menus/restaurar/' + data.id + '/' + table;
            axios.get(urlRestaurar).then(response => {
                toastr.success('Registro restaurado.');
                if(table == 'detail'){
                    refreshTable('tbl-menus_detalle');
                }else{
                    refreshTable('tbl-menus');
                }
            });
        },
        delete: function(data, table) {
            axios.get('menus/destroy/' + data.id+'/'+table).then(response => {
                if(table == 'detail'){
                    refreshTable('tbl-menus_detalle');
                }else{
                    refreshTable('tbl-menus');
                }
                toastr.success("<div><p>Registro eliminado exitosamente.</p><button type='button' onclick=\"rollBackDelete(" + data.id + ", '"+table+"')\" id='okBtn' class='btn btn-xs btn-danger pull-right'><i class='fa fa-reply'></i> Restaurar</button></div>");
                toastr.options.closeButton = true;
            });
        },
        store: function() {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    let me = this;
                    axios.post('menus', {
                        'name': this.name,
                        'cliente_id': this.cliente_id.id,
                    }).then(function(response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro creado correctamente.');
                            toastr.options.closeButton = true;
                            refreshTable('tbl-menus');
                            me.id = response.data['datos'].id;
                            me.createMenuDetail();
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
                toastr.warning('Error al intentar registrar.');
            });
        },
        update: function() {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    var me = this;
                    axios.put('menus/' + this.id, {
                        'name': this.name,
                        'cliente_id': this.cliente_id.id,
                    }).then(function(response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro Actualizado correctamente');
                            toastr.options.closeButton = true;
                            me.editar = 0;
                            me.resetForm();
                            refreshTable('tbl-menus');
                        } else {
                            toastr.warning(response.data['error']);
                            toastr.options.closeButton = true;
                            console.log(response.data);
                        }
                    }).catch(function(error) {
                        console.log(error);
                        toastr.error("Error. - " + error, {
                            timeOut: 50000
                        });
                    });
                }
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error al intentar registrar.');
            });
        },
        edit: function(data) {
            this.resetForm();
            this.id = data['id'];
            this.name = data['name'];
            this.cliente_id = {
                id: data['cliente_id'],
                name: data['cliente']
            };
            this.listMenuDetail();
            this.editar = 1;
            this.mostrar_password = false;
        },
        cancel: function() {
            var me = this;
            me.resetForm();
        },
        getProductos: function() {
            let me = this;
            axios.get('product/getDataSelect').then(function(response) {
                me.products = response.data.data;
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        getClientes: function() {
            let me = this;
            axios.get('clientes/getDataSelect').then(function(response) {
                me.cliente = response.data.data;
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        createMenuDetail: function() {
            let me = this;
            axios.post('menus/addMenuDetail', {
                'menu_id': me.id,
                'product_id': me.product_id.id,
                'weight_1_3': me.peso_1_3,
                'weight_4_5': me.peso_4_5,
            }).then(function(response) {
                if (response.data['code'] == 200) {
                    toastr.success('Registro agregado correctamente.');
                    toastr.options.closeButton = true;
                    me.product_id = null;
                    me.peso_1_3 = null;
                    me.peso_4_5 = null;
                    if (!$.fn.DataTable.isDataTable('#tbl-menus_detalle')) {
                        me.listMenuDetail();
                    } else {
                        refreshTable('tbl-menus_detalle');
                    }
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
        },
        addMenuDetail: function() {
            let me = this;
            if (this.name != null) {
                if (!$.fn.DataTable.isDataTable('#tbl-menus_detalle')) {
                    this.store();
                    this.editar = 1;
                } else {
                    this.createMenuDetail();
                }
            } else {
                toastr.warning('Porfavor ingresa el nombre del menu y selecciona el grupo de edad para continuar.');
                toastr.options.closeButton = true;
            }
        },
        listMenuDetail: function() {
            let me = this;
            $('#tbl-menus_detalle').DataTable({
                ajax: 'menus/allDetalle/' + this.id,
                columns: [{
                    data: 'product',
                    name: 'product'
                }, {
                    "render": function (data, type, full, meta) {
                        return '<a data-name="weight_1_3" data-pk="'+full.id+'" class="td_edit" data-type="text" data-placement="right" data-title="'+full.unidad_medida+'">'+full.weight_1_3+'</a>';
                    }
                }, {
                    "render": function (data, type, full, meta) {
                        return '<a data-name="weight_4_5" data-pk="'+full.id+'" class="td_edit" data-type="text" data-placement="right" data-title="'+full.unidad_medida+'">'+full.weight_4_5+'</a>';
                    }
                }, {
                    data: 'unidad_medida_ab',
                    name: 'unidad_medida_ab'
                }, {
                    sortable: false,
                    "render": function(data, type, full, meta) {
                        var btn_delete = " <a onclick=\"eliminarDetalle(" + full.id + "," + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar' style='margin-right: 13px;'><i class='fa fa-trash'></i></a> ";
                        return btn_delete;
                    }
                }],
                "drawCallback": function () {
                        /* EDITABLE FIELD */
                        $(".td_edit").editable({
                            ajaxOptions: {
                                type: 'post',
                                dataType: 'json'
                            },
                            url: "menus/updateDetailMenu",
                            validate:function(value){
                                if($.trim(value) == ''){
                                    return 'Este campo es obligatorio!';
                                }
                            }
                        });
                    },
            });
        },
        setUnidadMedida: function(val){
            this.product_id = val;
            if(val != null){
                $('#unidad_medida_1_3').html(val.unidad_medida);                
                $('#unidad_medida_4_5').html(val.unidad_medida);                
            }
        }
    },
});