$(document).ready(function() {
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.params = function(params) {
        params._token = $('meta[name="csrf-token"]').attr('content');
        return params;
    };

    $("#md-grupo_edad").on("hidden.bs.modal", function () {
        $("#tbl-grupoEtareo tbody").empty();
    });
});
$(window).load(function() {
    $('#tbl-unidadServicio').DataTable({
        ajax: 'unidadServicio/all',
        columns: [{
            data: 'cliente',
            name: 'cliente'
        }, {
            data: 'name',
            name: 'name'
        }, {
            data: 'address',
            name: 'address'
        }, {
            data: 'phone',
            name: 'phone'
        }, {
            "render": function(data, type, full, meta) {
                return '1 a 3: <strong>'+full.coverage_1_3+'</strong><br>4 a 5: <strong>'+full.coverage_4_5+'</strong>';
            }
        }, {
            sortable: false,
            "render": function(data, type, full, meta) {
                var params = [
                    full.id, full.cliente_id, full.tipo_unidad_servicio_id, "'" + full.tipo_us + "'", "'" + full.cliente + "'", "'" + full.name + "'", "'" + full.address + "'", "'" + full.phone + "'"
                ];
                var btn_ge = "<a onclick=\"addGrupoEtareo(" + full.id + ")\" class='btn btn-outline btn-warning btn-xs' data-toggle='tooltip' data-placement='top' title='Agregar grupo edad'><i class='fa fa-plus'></i></a> ";
                var btn_edit = "<a onclick=\"edit(" + params + ")\" class='btn btn-outline btn-success btn-xs' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fa fa-edit'></i></a> ";
                var btn_delete = " <a onclick=\"eliminar(" + full.id + "," + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                return btn_ge + btn_edit + btn_delete;
            }
        }],
        'columnDefs': [{
            "targets": [0, 2],
            width: 100,
        }],
    });
});

function edit(id, cliente_id, tipo_unidad_servicio_id, tipo_us, cliente, name, address, phone) {
    var data = {
        id: id,
        name: name,
        address: address,
        phone: phone,
        cliente_id: cliente_id,
        cliente: cliente,
        tipo_unidad_servicio_id: tipo_unidad_servicio_id,
        tipo_us: tipo_us
    };
    objVue.edit(data);
}

function addGrupoEtareo(id){
    $('#md-grupo_edad').modal('show');
    objVue.getGrupoEdadByUs(id);
}
var objVue = new Vue({
    el: '#unidadServicio',
    mounted: function() {
        this.getClientes();
        this.getGrupoEdad();
        this.getTipoUnidadServicio();
        const dict = {
            custom: {
                name: {
                    required: 'El nombre es obligatorio.'
                },
                cliente_id: {
                    required: 'El cliente es obligatorio.'
                },
                address: {
                    required: 'La dirección es obligatoria.'
                },
                phone: {
                    required: 'El teléfono es obligatorio.'
                }
            }
        };
        this.$validator.localize('es', dict);
    },
    data: {
        name: null,
        address: null,
        phone: null,
        cliente_id: null,
        clientes: [],
        tipo_us_id: null,
        tipo_us: [],
        age_group_id: null,
        age_groups: [],
        grupoEtareo: [],
        coverage: 0,
        editar: 0
    },
    methods: {
        resetForm: function() {
            this.id = null;
            this.name = null;
            this.address = null;
            this.age_group_id = null;
            this.phone = null;
            this.cliente_id = null;
            this.tipo_us_id = null;
            this.editar = 0;
            this.errors.clear();
        },
        rollBackDelete: function(data) {
            var urlRestaurar = 'unidadServicio/restaurar/' + data.id;
            axios.get(urlRestaurar).then(response => {
                toastr.success('Registro restaurado.');
                refreshTable('tbl-unidadServicio');
            });
        },
        delete: function(data) {
            axios.delete('unidadServicio/' + data.id).then(response => {
                refreshTable('tbl-unidadServicio');
                toastr.success("<div><p>Registro eliminado exitosamente.</p><button type='button' onclick='deshacerEliminar(" + data.id + ")' id='okBtn' class='btn btn-xs btn-danger pull-right'><i class='fa fa-reply'></i> Restaurar</button></div>");
                toastr.options.closeButton = true;
            });
        },
        store: function() {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    let me = this;
                    axios.post('unidadServicio', {
                        'name': this.name,
                        'address': this.address,
                        'phone': this.phone,
                        'cliente_id': this.cliente_id.id,
                        'tipo_unidad_servicio_id': this.tipo_us_id.id,
                    }).then(function(response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro creado correctamente.');
                            toastr.options.closeButton = true;
                            me.resetForm();
                            refreshTable('tbl-unidadServicio');
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
                    axios.put('unidadServicio/' + this.id, {
                        'name': this.name,
                        'address': this.address,
                        'phone': this.phone,
                        'cliente_id': this.cliente_id.id,
                        'tipo_unidad_servicio_id': this.tipo_us_id.id,
                    }).then(function(response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro Actualizado correctamente');
                            toastr.options.closeButton = true;
                            me.editar = 0;
                            me.resetForm();
                            refreshTable('tbl-unidadServicio');
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
            this.id = data['id'];
            this.name = data['name'];
            this.address = data['address'];
            this.phone = data['phone'];
            this.cliente_id = {
                id: data['cliente_id'],
                name: data['cliente']
            };
            this.tipo_us_id = {
                id: data['tipo_unidad_servicio_id'],
                name: data['tipo_us']
            };
            this.editar = 1;
            this.mostrar_password = false;
        },
        cancel: function() {
            var me = this;
            me.resetForm();
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
        addGrupoEtareo: function() {
            let me = this;
            axios.post('unidadServicio/addGrupoEtareo', {
                'age_group_id': this.age_group_id.id,
                'coverage': this.coverage,
                'unidad_servicio_id': this.id
            }).then(function(response) {
                if (response.data['code'] == 200) {
                    toastr.success('Registro creado correctamente.');
                    toastr.options.closeButton = true;
                    this.age_group_id = null;
                    this.coverage = 0;
                    me.getGrupoEdadByUs(me.id);
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
        getGrupoEdadByUs: function(us_id) {
            let me = this;
            me.grupoEtareo = [];
            this.id = us_id;
            axios.get('unidadServicio/getGrupoEdadByUs/'+us_id).then(function(response) {
                me.grupoEtareo = response.data.data;
                setTimeout(function(){
                    $(".td_edit").editable({
                        ajaxOptions: {
                            type: 'post',
                            dataType: 'json'
                        },
                        url: "unidadServicio/updateCoverage",
                        validate:function(value){
                            if($.trim(value) == ''){
                                return 'Este campo es obligatorio!';
                            }
                        },
                        success: function(response, newValue) {
                            toastr.success('Actualización exitosa.');
                            toastr.options.closeButton = true;
                            refreshTable('tbl-unidadServicio');
                        }
                    });
                },300);
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        deleteGrupoEdad: function(id){
            let me = this;
            axios.delete('unidadServicio/deleteGrupoEdad/'+id).then(function(response) {
                toastr.success('Registro eliminado correctamente.');
                toastr.options.closeButton = true;
                me.getGrupoEdadByUs(me.id);
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        }
    },
});