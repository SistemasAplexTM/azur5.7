$(document).ready(function() {
    // $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.params = function(params) {
        params._token = $('meta[name="csrf-token"]').attr('content');
        return params;
    };
    getMenus('cdi');
    getMenus('hcb');
});

function getMenus(type_menu) {
    $('#tbl-menus_' + type_menu).DataTable({
        ajax: 'menus/all/' + type_menu,
        columns: [{
            data: 'name',
            name: 'name'
        }, {
            data: 'tipo_uds',
            name: 'tipo_uds'
        }, {
            sortable: false,
            "render": function(data, type, full, meta) {
                var btns = '';
                var params = [
                    full.id, "'" + full.name + "'", full.cliente_id, "'" + full.cliente + "'", full.tipo_us_id, "'" + full.tipo_uds + "'"
                ];
                if (permission_update) {
                    var btn_edit = "<a onclick=\"edit(" + params + ")\" class='btn btn-outline btn-success btn-xs' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fa fa-edit'></i></a> ";
                    btns += btn_edit;
                }
                if (permission_delete) {
                    var btn_delete = " <a onclick=\"eliminar(" + full.id + "," + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                    btns += btn_delete;
                }
                return btns;
            }
        }]
    });
}

function edit(id, name, cliente_id, cliente, tipo_us_id, tipo_us) {
    var data = {
        id: id,
        name: name,
        cliente_id: cliente_id,
        cliente: cliente,
        tipo_us_id: tipo_us_id,
        tipo_us: tipo_us
    };
    objVue.edit(data);
}

function eliminarDetalle(id, op) {
    var data = {
        id: id,
        logical: op
    };
    objVue.delete(data, 'detail');
}

function rollBackDelete(id, table) {
    var data = {
        id: id
    };
    objVue.rollBackDelete(data, table);
}
var objVue = new Vue({
    el: '#menus',
    watch: {
        tipo_us_id: function(values) {
            if (values != null) {
                if (values.id == 1) {
                    this.cdi_menu = true;
                    this.hcb_menu = false;
                } else {
                    this.cdi_menu = false;
                    this.hcb_menu = true;
                }
            }
        }
    },
    mounted: function() {
        this.getProductos();
        this.getClientes();
        this.getGrupoEdad();
        this.getTipoUnidadServicio();
        const dict = {
            custom: {
                name: {
                    required: 'El nombre es obligatorio.'
                },
                age_group_id: {
                    required: 'El grupo de edad es obligatorio.'
                },
                tipo_us_id: {
                    required: 'Este campo es obligatorio.'
                }
            }
        };
        this.$validator.localize('es', dict);
    },
    data: {
        id: null,
        name: null,
        product_id: null,
        peso: null,
        cliente_id: null,
        cliente: [],
        tipo_us_id: null,
        tipo_us: [],
        products: [],
        age_group_id: null,
        age_groups: [],
        editar: 0,
        cdi_menu: true,
        hcb_menu: false
    },
    methods: {
        resetForm: function() {
            this.id = null;
            this.name = null;
            this.peso = null;
            this.cliente_id = null;
            this.product_id = null;
            this.tipo_us_id = null;
            this.editar = 0;
            this.errors.clear();
            if ($.fn.DataTable.isDataTable('#tbl-menus_detalle')) {
                $('#tbl-menus_detalle tbody').empty();
                $('#tbl-menus_detalle').dataTable().fnDestroy();
                $("#tbl-menus_detalle tbody tr").remove();
            }
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
        getGrupoEdad: function() {
            let me = this;
            axios.get('administracion/grupo_edad/getDataSelect').then(function(response) {
                me.age_groups = response.data.data;
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        rollBackDelete: function(data, table) {
            var urlRestaurar = 'menus/restaurar/' + data.id + '/' + table;
            axios.get(urlRestaurar).then(response => {
                toastr.success('Registro restaurado.');
                if (table == 'detail') {
                    refreshTable('tbl-menus_detalle');
                } else {
                    refreshTable('tbl-menus');
                }
            });
        },
        delete: function(data, table) {
            axios.get('menus/destroy/' + data.id + '/' + table).then(response => {
                if (table == 'detail') {
                    refreshTable('tbl-menus_detalle');
                } else {
                    refreshTable('tbl-menus');
                }
                toastr.success("<div><p>Registro eliminado exitosamente.</p><button type='button' onclick=\"rollBackDelete(" + data.id + ", '" + table + "')\" id='okBtn' class='btn btn-xs btn-danger pull-right'><i class='fa fa-reply'></i> Restaurar</button></div>");
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
                        'tipo_us_id': this.tipo_us_id.id,
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
                        'tipo_us_id': this.tipo_us_id.id,
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
            this.tipo_us_id = {
                id: data['tipo_us_id'],
                name: data['tipo_us']
            };
            this.listMenuDetail();
            this.editar = 1;
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
                'age_group_id': me.age_group_id.id,
                'cantidad': me.peso,
            }).then(function(response) {
                if (response.data['code'] == 200) {
                    toastr.success('Registro agregado correctamente.');
                    toastr.options.closeButton = true;
                    me.product_id = null;
                    me.peso = null;
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
                    "render": function(data, type, full, meta) {
                        var ge1 = '<span>1 a 3: <strong><a data-name="peso" data-pk="' + full.cantidad_1_3_id + '" class="td_edit" data-type="text" data-placement="top" data-title="' + full.unidad_medida + '">' + full.cantidad_1_3 + '</a></strong></span>';
                        var ge2 = '<span style="float: right;">4 a 5: <strong><a data-name="peso" data-pk="' + full.cantidad_4_5_id + '" class="td_edit" data-type="text" data-placement="top" data-title="' + full.unidad_medida + '">' + full.cantidad_4_5 + '</a></strong></span>';
                        return ge1 + ge2;
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
                "drawCallback": function() {
                    /* EDITABLE FIELD */
                    $(".td_edit").editable({
                        ajaxOptions: {
                            type: 'post',
                            dataType: 'json'
                        },
                        url: "menus/updateDetailMenu",
                        validate: function(value) {
                            if ($.trim(value) == '') {
                                return 'Este campo es obligatorio!';
                            }
                        }
                    });
                },
            });
        },
        setUnidadMedida: function(val) {
            this.product_id = val;
            if (val != null) {
                $('#unidad_medida').html(val.unidad_medida);
            }
        }
    },
});