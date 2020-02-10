
$(document).ready(function () {

    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.params = function (params) {
        params._token = $('meta[name="csrf-token"]').attr('content');
        return params;
    };
    getMenus('cdi');
    getMenus('hcb');
    getMenus('hi');
});

function getMenus(type_menu) {
    $('#tbl-menus_' + type_menu).DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ajax: 'menus/all/' + type_menu,
        columns: [{
            data: 'name',
            name: 'name'
        }, {
            data: 'tipo_uds',
            name: 'tipo_uds'
        }, {
            sortable: false,
            "render": function (data, type, full, meta) {
                var btns = '';
                var params = [
                    full.id, "'" + full.name + "'", full.cliente_id, "'" + full.cliente + "'", full.tipo_us_id, "'" + full.tipo_uds + "'"
                ];
                // if (permission_update) {
                var btn_edit = "<a onclick=\"edit(" + params + ")\" class='btn btn-outline btn-success btn-xs' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fa fa-edit'></i></a> ";
                btns += btn_edit;
                // }
                var btn_copy = "<a onclick=\"copy(" + params + ")\" class='btn btn-outline btn-info btn-xs' data-toggle='tooltip' data-placement='top' title='Copiar'><i class='fa fa-copy'></i></a> ";
                btns += btn_copy;
                // if (permission_delete) {
                var btn_delete = " <a onclick=\"deleteMenu(" + full.id + ", '" + type_menu + "', " + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                btns += btn_delete;
                // }
                return btns;
            }
        }]
    });
}

function deleteMenu(id, type_menu, logical) {
    objVue.delete({ id: id, type_menu: type_menu }, 'menu', logical);
}
function edit(id, name, cliente_id, cliente, tipo_us_id, tipo_us, modal) {
    var data = {
        id: id,
        name: name,
        cliente_id: cliente_id,
        cliente: cliente,
        tipo_us_id: tipo_us_id,
        tipo_us: tipo_us,
        modal: modal
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

function rollBackDelete(id, type_menu, table) {
    var data = { id: id, type_menu: type_menu };
    objVue.rollBackDelete(data, table);
}

function copy(id, menu, cliente_id, cliente, tipo_us_id, tipo_us) {
    objVue.name_menu = menu;
    objVue.name_uds = tipo_us;
    objVue.id_menu_copy = id;
    $('#modalCopy').modal('show');
    edit(id, menu, cliente_id, cliente, tipo_us_id, tipo_us, true)
}

var objVue = new Vue({
    el: '#menus',
    watch: {
        tipo_us_id: function (values) {
            console.log(values);

            if (values != null) {
                if (values.id == 1) {
                    this.cdi_menu = true;
                    this.hcb_menu = false;
                    this.cdi_calida_menu = false;
                    this.cdi_semillas_menu = false;
                    this.hi_menu = false;
                    this.hi_calenitos_menu = false;
                } else {
                    if (values.id == 2) {
                        this.cdi_menu = false;
                        this.hcb_menu = true;
                        this.cdi_calida_menu = false;
                        this.cdi_semillas_menu = false;
                        this.hi_menu = false;
                        this.hi_calenitos_menu = false;
                    } else {
                        if (values.id == 64) {
                            this.cdi_menu = false;
                            this.hcb_menu = false;
                            this.cdi_calida_menu = true;
                            this.cdi_semillas_menu = false;
                            this.hi_menu = false;
                            this.hi_calenitos_menu = false;
                        } else {
                            if (values.id == 65) {
                                this.cdi_menu = false;
                                this.hcb_menu = false;
                                this.cdi_calida_menu = false;
                                this.cdi_semillas_menu = true;
                                this.hi_menu = false;
                                this.hi_calenitos_menu = false;
                            } else {
                                if (values.id == 63) {
                                    this.cdi_menu = false;
                                    this.hcb_menu = false;
                                    this.cdi_calida_menu = false;
                                    this.cdi_semillas_menu = false;
                                    this.hi_menu = true;
                                    this.hi_calenitos_menu = false;
                                } else {
                                    this.cdi_menu = false;
                                    this.hcb_menu = false;
                                    this.cdi_calida_menu = false;
                                    this.cdi_semillas_menu = false;
                                    this.hi_menu = false;
                                    this.hi_calenitos_menu = true;
                                }
                            }
                        }

                    }
                }
            }
        },
        tipo_uds_id: function (val) {
            this.menus_id = null;
            this.getMenusChage(val.name);
        }
    },
    mounted: function () {
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
        product_id_change: null,
        um_pedido: null,
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
        hcb_menu: false,
        cdi_calida_menu: false,
        cdi_semillas_menu: false,
        hi_menu: false,
        hi_calenitos_menu: false,
        hi_vicente_menu: false,
        menus: [],
        menus_id: null,
        tipo_uds_id: null,
        id_menu_copy: null,
        name_menu: null,
        name_uds: null,
        loading: false,
        coping: 'Copiar',
        prefix_um: 'Kl'
    },
    methods: {
        copyMenu() {
            this.loading = true;
            this.coping = 'Copiando..';
            let me = this;
            if (me.menus_id !== null && me.id_menu_copy !== null) {
                axios.get('menus/copyMenu/' + me.menus_id + '/' + me.id_menu_copy).then(function (response) {
                    if (response.data['code'] == 200) {
                        // refreshTable('tbl-menus_detalle');
                        toastr.success('Registros copiados exitosamente');
                    }
                    me.loading = false;
                    me.coping = 'Copiar';
                }).catch(function (error) {
                    me.loading = false;
                    me.coping = 'Copiar';
                    console.log(error);
                    toastr.warning('Error.');
                    toastr.options.closeButton = true;
                });
            } else {
                toastr.warning('Seleccione todos los campos por favor.');
                me.loading = false;
                me.coping = 'Copiar';
            }
        },
        getMenusChage(type_id) {
            let me = this;
            axios.get('menus/all/' + type_id).then(function (response) {
                me.menus = response.data.data;
            }).catch(function (error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        resetTableDetail() {
            if ($.fn.DataTable.isDataTable('#tbl-menus_detalle')) {
                $('#tbl-menus_detalle tbody').empty();
                $('#tbl-menus_detalle').dataTable().fnDestroy();
                $("#tbl-menus_detalle tbody tr").remove();
            }
        },
        resetForm: function () {
            this.id = null;
            this.name = null;
            this.peso = null;
            this.cliente_id = null;
            this.product_id = null;
            this.tipo_us_id = null;
            this.editar = 0;
            this.errors.clear();
            this.resetTableDetail();
        },
        getTipoUnidadServicio: function () {
            let me = this;
            axios.get('administracion/tipo_unidad_servicio/getDataSelect').then(function (response) {
                me.tipo_us = response.data.data;
            }).catch(function (error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        getGrupoEdad: function () {
            let me = this;
            axios.get('administracion/grupo_edad/getDataSelect').then(function (response) {
                me.age_groups = response.data.data;
            }).catch(function (error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        rollBackDelete: function (data, table) {
            var urlRestaurar = 'menus/restaurar/' + data.id + '/' + table;
            axios.get(urlRestaurar).then(response => {
                toastr.success('Registro restaurado.');
                if (table == 'detail') {
                    this.listMenuDetail();
                } else {
                    refreshTable('tbl-menus_' + data.type_menu);
                }
            });
        },
        delete: function (data, table) {
            axios.get('menus/destroy/' + data.id + '/' + table).then(response => {
                if (table == 'detail') {
                    this.listMenuDetail();
                } else {
                    refreshTable('tbl-menus_' + data.type_menu);
                }
                console.log('data: ', data);

                toastr.success("<div><p>Registro eliminado exitosamente.</p><button type='button' onclick=\"rollBackDelete(" + data.id + ", '" + data.type_menu + "', '" + table + "')\" id='okBtn' class='btn btn-xs btn-danger pull-right'><i class='fa fa-reply'></i> Restaurar</button></div>");
                toastr.options.closeButton = true;
            }).catch(function (error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        store: function () {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    let me = this;
                    axios.post('menus', {
                        'name': this.name,
                        'cliente_id': this.cliente_id.id,
                        'tipo_us_id': this.tipo_us_id.id,
                    }).then(function (response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro creado correctamente.');
                            toastr.options.closeButton = true;
                            console.log('registro');

                            // refreshTable('tbl-menus');
                            me.id = response.data['datos'].id;
                            console.log('registro ok');
                            me.createMenuDetail();
                        } else {
                            toastr.warning(response.data['error']);
                            toastr.options.closeButton = true;
                        }
                    }).catch(function (error) {
                        console.log(error);
                        toastr.error("Error. - " + error, {
                            timeOut: 50000
                        });
                    });
                } else {
                    console.log(errors);
                    toastr.warning('Error en la validacion');
                }
            }).catch(function (error) {
                toastr.warning('Error al intentar registrar.');
            });
        },
        update: function () {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    var me = this;
                    axios.put('menus/' + this.id, {
                        'name': this.name,
                        'cliente_id': this.cliente_id.id,
                        'tipo_us_id': this.tipo_us_id.id,
                    }).then(function (response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro Actualizado correctamente');
                            toastr.options.closeButton = true;
                            me.editar = 0;
                            me.resetForm();
                            // refreshTable('tbl-menus');
                        } else {
                            toastr.warning(response.data['error']);
                            toastr.options.closeButton = true;
                            console.log(response.data);
                        }
                    }).catch(function (error) {
                        console.log(error);
                        toastr.error("Error. - " + error, {
                            timeOut: 50000
                        });
                    });
                }
            }).catch(function (error) {
                console.log(error);
                toastr.warning('Error al intentar registrar.');
            });
        },
        edit: function (data) {
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
            if (!data.modal) {
                $('#modalList').modal('hide');
            }
        },
        cancel: function () {
            var me = this;
            me.resetForm();
        },
        getProductos: function () {
            let me = this;
            axios.get('product/getDataSelect').then(function (response) {
                me.products = response.data.data;
            }).catch(function (error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        getClientes: function () {
            let me = this;
            axios.get('clientes/getDataSelect').then(function (response) {
                me.cliente = response.data.data;
            }).catch(function (error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        createMenuDetail: function () {
            let me = this;
            axios.post('menus/addMenuDetail', {
                'menu_id': me.id,
                'product_id': me.product_id.id,
                'age_group_id': me.age_group_id.id,
                'cantidad': me.peso,
            }).then(function (response) {
                if (response.data['code'] == 200) {
                    toastr.success('Registro agregado correctamente.');
                    toastr.options.closeButton = true;
                    me.product_id = null;
                    me.peso = null;
                    if (!$.fn.DataTable.isDataTable('#tbl-menus_detalle')) {
                        me.listMenuDetail();
                    } else {
                        me.resetTableDetail();
                        me.listMenuDetail();
                        // refreshTable('tbl-menus_detalle');
                    }
                } else {
                    toastr.warning(response.data['error']);
                    toastr.options.closeButton = true;
                }
            }).catch(function (error) {
                console.log(error);
                toastr.error("Error. - " + error, {
                    timeOut: 50000
                });
            });
        },
        addMenuDetail: function () {
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
        listMenuDetail: async function () {
            let me = this;
            me.resetTableDetail();
            dataSet = await me.getDataDetail();
            await me.generateDatatableDetail(dataSet);
            // $('#tbl-menus_detalle').DataTable({
            //     data: dataSet,
            //     "columns": me.my_columns(dataSet)
            // processing: true,
            // serverSide: true,
            // searching: true,
            // ajax: 'menus/allDetalle/' + this.id,
            // lengthMenu: [[40, 50, 80, 100, 200, -1], [40, 50, 80, 100, 200, "All"]],
            // columns: [{
            //     data: 'product',
            //     name: 'product'
            // }, {
            //     "render": function (data, type, full, meta) {
            //         var ge1 = '<span>1 a 3: <strong><a data-name="peso" data-pk="' + full.cantidad_1_3_id + '" class="td_edit" data-type="text" data-placement="top" data-title="' + full.unidad_medida + '">' + full.cantidad_1_3 + '</a></strong></span>';
            //         var ge2 = '<span style="float: right;">4 a 5: <strong><a data-name="peso" data-pk="' + full.cantidad_4_5_id + '" class="td_edit" data-type="text" data-placement="top" data-title="' + full.unidad_medida + '">' + full.cantidad_4_5 + '</a></strong></span>';
            //         return ge1 + ge2;
            //     }
            // }, {
            //     data: 'unidad_medida_ab',
            //     name: 'unidad_medida_ab',
            //     "className": "text-center",
            // }, {
            //     "render": function (data, type, full, meta) {
            // return '<a data-name="um_pedido" data-pk="' + full.cantidad_1_3_id + '" class="td_edit" data-type="text" data-placement="top" data-title="U.M Pedido">' + full.um_pedido + '</a>';
            //     },
            //     "className": "text-center",
            // }, {
            //     sortable: false,
            //     "render": function (data, type, full, meta) {
            //         var btn_delete = " <a onclick=\"eliminarDetalle(" + full.id + "," + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar' style='margin-right: 13px;'><i class='fa fa-trash'></i></a> ";
            //         return btn_delete;
            //     }
            // }],
            // "drawCallback": function () {
            //     /* EDITABLE FIELD */
            //     $(".td_edit").editable({
            //         ajaxOptions: {
            //             type: 'post',
            //             dataType: 'json'
            //         },
            //         url: "menus/updateDetailMenu",
            //         validate: function (value) {
            //             if ($.trim(value) == '') {
            //                 return 'Este campo es obligatorio!';
            //             }
            //         }
            //     });
            // },
            // });
        },
        generateDatatableDetail(dataSet) {
            let me = this;
            $('#tbl-menus_detalle').DataTable({
                lengthMenu: [[40, 50, 80, 100, 200, -1], [40, 50, 80, 100, 200, "All"]],
                data: dataSet,
                "columns": me.my_columns(dataSet),
                "drawCallback": function () {
                    /* EDITABLE FIELD */
                    $(".td_edit").editable({
                        ajaxOptions: {
                            type: 'post',
                            dataType: 'json'
                        },
                        url: "menus/updateDetailMenu",
                        validate: function (value) {
                            if ($.trim(value) == '') {
                                return 'Este campo es obligatorio!';
                            }
                        }
                    });
                },
            });
        },
        async getDataDetail() {
            let me = this;
            var data = [];
            await axios.get('menus/allDetalle/' + me.id).then(function (response) {
                data = response.data.data;
                let fields = [];
                me.getFieldsEdit(data).then(function (response) {
                    fields = response;
                    $.each(data, function (key, value) {
                        for (let index = 0; index < fields.length; index++) {
                            const idField = fields[index].slice(5);
                            data[key][fields[index]] = '<a data-name="peso" data-mode="popup" data-pk="' + data[key]['id_' + idField] + '" class="td_edit" data-type="text" data-placement="top" data-title="' + value.unidad_medida + '">' + data[key][fields[index]] + '</a>';
                        }
                        data[key].um_pedido = '<a data-name="um_pedido" data-pk="' + value.ge_id + '" class="td_edit" data-type="text" data-placement="top" data-title="U.M Pedido">' + value.um_pedido + '</a>';
                        data[key].detalle = " <a onclick=\"eliminarDetalle(" + value.detalle + "," + true + ", " + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar' style='margin-right: 13px;'><i class='fa fa-trash'></i></a> "
                    });
                });
            }).catch(function (error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
            return data;
        },
        my_columns(data) {
            var my_columns = [];
            $.each(data[0], function (key, value) {
                var my_item = {};
                if (key != 'id' && key != 'unidad_medida' && key != 'U' && !key.includes('id_') && !key.includes('_id')) {
                    if (key === 'um_pedido') {
                        my_item.data = key;
                        my_item.title = 'U.M Pedido';
                    } else {
                        if (key === 'detalle') {
                            my_item.data = key;
                            my_item.title = 'Acciones';
                        } else {
                            if (key.includes('cant_')) {
                                my_item.data = key;
                                my_item.title = my_item.data.slice(5);// quitar la palabra cant_
                            } else {
                                my_item.data = key;
                                my_item.title = key;
                            }
                        }
                    }
                    my_columns.push(my_item);
                }
            });
            return my_columns;
        },
        async getFieldsEdit(data) {
            var my_columns = [];
            await $.each(data[0], function (key, value) {
                if (key.includes('cant_')) {
                    my_columns.push(key);
                }
            });
            return my_columns;
        },
        setUnidadMedida: function (val) {
            this.product_id = val;
            if (val != null) {
                this.prefix_um = val.unidad_medida;
            }
        },
        saveChage: function () {
            let me = this;
            axios.post('menus/changeUnitFinal', {
                'product_id': me.product_id_change.id,
                'unidad_medida_real': me.um_pedido,
            }).then(function (response) {
                if (response.data['code'] == 200) {
                    toastr.success('Registro actualizado correctamente.');
                    toastr.options.closeButton = true;
                    me.product_id_change = null;
                    me.um_pedido = null;
                    if (!$.fn.DataTable.isDataTable('#tbl-menus_detalle')) {
                        me.listMenuDetail();
                    } else {
                        // refreshTable('tbl-menus_detalle');
                    }
                } else {
                    toastr.warning(response.data['error']);
                    toastr.options.closeButton = true;
                }
            }).catch(function (error) {
                console.log(error);
                toastr.error("Error. - " + error, {
                    timeOut: 50000
                });
            });
        }
    },
});
