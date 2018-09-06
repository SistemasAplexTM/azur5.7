$(document).ready(function() {
    //  
});
$(window).load(function() {
    $('#tbl-product').DataTable({
        ajax: 'product/all',
        columns: [{
            data: 'name',
            name: 'name'
        }, {
            data: 'description',
            name: 'description'
        }, {
            data: 'unidad_medida',
            name: 'unidad_medida'
        }, {
            data: 'tipo_producto',
            name: 'tipo_producto'
        }, {
            sortable: false,
            "render": function(data, type, full, meta) {
                var params = [
                    full.id, "'" + full.name + "'", "'" + full.description + "'", full.unidad_medida_id, "'" + full.unidad_medida + "'", full.tipo_producto_id, "'" + full.tipo_producto + "'", full.conversion
                ];
                var btn_edit = "<a onclick=\"edit(" + params + ")\" class='btn btn-outline btn-success btn-xs' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fa fa-edit'></i></a> ";
                var btn_delete = " <a onclick=\"eliminar(" + full.id + "," + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                return btn_edit + btn_delete;
            }
        }]
    });
});

function edit(id, name, description, unidad_medida_id, unidad_medida, tipo_producto_id, tipo_producto, conversion) {
    var data = {
        id: id,
        name: name,
        description: description,
        unidad_medida_id: unidad_medida_id,
        unidad_medida: unidad_medida,
        tipo_producto_id: tipo_producto_id,
        tipo_producto: tipo_producto,
        conversion: conversion,
    };
    objVue.edit(data);
}
var objVue = new Vue({
    el: '#product',
    mounted: function() {
        this.getUnidadMedida();
        this.getTipoProducto();
        const dict = {
            custom: {
                name: {
                    required: 'El nombre es obligatorio.'
                },
                description: {
                    required: 'El descripción es obligatorio.'
                },
                conversion: {
                    required: 'El conversión es obligatorio.'
                },
                unidad_medida_id: {
                    required: 'La unidad de medida es obligatoria.'
                }
            }
        };
        this.$validator.localize('es', dict);
    },
    data: {
        name: null,
        description: null,
        conversion: null,
        unidad_medida_id: null,
        unidad_medidas: [],
        tipo_producto_id: null,
        tipo_producto: [],
        editar: 0
    },
    methods: {
        resetForm: function() {
            this.id = null;
            this.name = null;
            this.description = null;
            this.conversion = null;
            this.unidad_medida_id = null;
            this.tipo_producto_id = null;
            this.editar = 0;
            this.errors.clear();
        },
        rollBackDelete: function(data) {
            var urlRestaurar = 'product/restaurar/' + data.id;
            axios.get(urlRestaurar).then(response => {
                toastr.success('Registro restaurado.');
                refreshTable('tbl-product');
            });
        },
        delete: function(data) {
            axios.delete('product/' + data.id).then(response => {
                refreshTable('tbl-product');
                toastr.success("<div><p>Registro eliminado exitosamente.</p><button type='button' onclick='deshacerEliminar(" + data.id + ")' id='okBtn' class='btn btn-xs btn-danger pull-right'><i class='fa fa-reply'></i> Restaurar</button></div>");
                toastr.options.closeButton = true;
            });
        },
        store: function() {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    let me = this;
                    axios.post('product', {
                        'name': this.name,
                        'description': this.description,
                        'conversion': this.conversion,
                        'unidad_medida_id': this.unidad_medida_id.id,
                        'tipo_producto_id': this.tipo_producto_id.id,
                    }).then(function(response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro creado correctamente.');
                            toastr.options.closeButton = true;
                            me.resetForm();
                            refreshTable('tbl-product');
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
                    axios.put('product/' + this.id, {
                        'name': this.name,
                        'description': this.description,
                        'conversion': this.conversion,
                        'unidad_medida_id': this.unidad_medida_id.id,
                        'tipo_producto_id': this.tipo_producto_id.id,
                    }).then(function(response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro Actualizado correctamente');
                            toastr.options.closeButton = true;
                            me.editar = 0;
                            me.resetForm();
                            refreshTable('tbl-product');
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
            this.description = data['description'];
            this.conversion = data['conversion'];
            if(data['description'] == 'null'){
                this.description = null;
            }
            this.unidad_medida_id = {
                id: data['unidad_medida_id'],
                name: data['unidad_medida']
            };
            this.tipo_producto_id = {
                id: data['tipo_producto_id'],
                name: data['tipo_producto']
            };
            this.editar = 1;
            this.mostrar_password = false;
        },
        cancel: function() {
            var me = this;
            me.resetForm();
        },
        getUnidadMedida: function() {
            let me = this;
            axios.get('administracion/unidad_de_medida/getDataSelect').then(function(response) {
                me.unidad_medidas = response.data.data;
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
        getTipoProducto: function() {
            let me = this;
            axios.get('administracion/tipo_producto/getDataSelect').then(function(response) {
                me.tipo_producto = response.data.data;
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error.');
                toastr.options.closeButton = true;
            });
        },
    },
});