$(document).ready(function () {
    //  
});
$(window).load(function () {
    $('#tbl-clientes').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ajax: 'clientes/all',
        columns: [{
            data: 'name',
            name: 'name'
        }, {
            data: 'nit',
            name: 'nit'
        }, {
            data: 'address',
            name: 'address'
        }, {
            data: 'phone',
            name: 'phone'
        }, {
            sortable: false,
            "render": function (data, type, full, meta) {
                var params = [
                    full.id, "'" + full.name + "'", "'" + full.nit + "'", "'" + full.address + "'", "'" + full.phone + "'"
                ];
                var btn_edit = "<a onclick=\"edit(" + params + ")\" class='btn btn-outline btn-success btn-xs' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fa fa-edit'></i></a> ";
                var btn_delete = " <a onclick=\"eliminar(" + full.id + "," + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                return btn_edit + btn_delete;
            }
        }]
    });
});

function edit(id, name, nit, address, phone) {
    var data = {
        id: id,
        name: name,
        nit: nit,
        address: address,
        phone: phone
    };
    objVue.edit(data);
}
var objVue = new Vue({
    el: '#clientes',
    mounted: function () {
        const dict = {
            custom: {
                name: {
                    required: 'El nombre es obligatorio.'
                },
                nit: {
                    required: 'El descripción es obligatorio.'
                },
                address: {
                    required: 'La dirección es obligatoria.'
                },
                phone: {
                    required: 'el teléfono es obligatorio.'
                }
            }
        };
        this.$validator.localize('es', dict);
    },
    data: {
        name: null,
        nit: null,
        address: null,
        phone: null,
        editar: 0
    },
    methods: {
        resetForm: function () {
            this.id = null;
            this.name = null;
            this.nit = null;
            this.address = null;
            this.phone = null;
            this.editar = 0;
            this.errors.clear();
        },
        rollBackDelete: function (data) {
            var urlRestaurar = 'clientes/restaurar/' + data.id;
            axios.get(urlRestaurar).then(response => {
                toastr.success('Registro restaurado.');
                refreshTable('tbl-clientes');
            });
        },
        delete: function (data) {
            axios.delete('clientes/' + data.id).then(response => {
                refreshTable('tbl-clientes');
                toastr.success("<div><p>Registro eliminado exitosamente.</p><button type='button' onclick='deshacerEliminar(" + data.id + ")' id='okBtn' class='btn btn-xs btn-danger pull-right'><i class='fa fa-reply'></i> Restaurar</button></div>");
                toastr.options.closeButton = true;
            });
        },
        store: function () {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    let me = this;
                    axios.post('clientes', {
                        'name': this.name,
                        'nit': this.nit,
                        'address': this.address,
                        'phone': this.phone,
                    }).then(function (response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro creado correctamente.');
                            toastr.options.closeButton = true;
                            me.resetForm();
                            refreshTable('tbl-clientes');
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
                    axios.put('clientes/' + this.id, {
                        'name': this.name,
                        'nit': this.nit,
                        'address': this.address,
                        'phone': this.phone,
                    }).then(function (response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro Actualizado correctamente');
                            toastr.options.closeButton = true;
                            me.editar = 0;
                            me.resetForm();
                            refreshTable('tbl-clientes');
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
            this.id = data['id'];
            this.name = data['name'];
            this.nit = data['nit'];
            this.address = data['address'];
            this.phone = data['phone'];
            this.editar = 1;
            this.mostrar_password = false;
        },
        cancel: function () {
            var me = this;
            me.resetForm();
        }
    },
});