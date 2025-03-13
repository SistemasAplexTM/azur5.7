$(document).ready(function () {
    // Initialize DataTable for companies
    $('#tbl-companies').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ajax: 'companies/all',
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
    el: '#company',
    mounted: function () {
        const dict = {
            custom: {
                name: {
                    required: 'El nombre es obligatorio.'
                },
                nit: {
                    required: 'El NIT es obligatorio.'
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
        store: function () {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    let me = this;
                    axios.post('companies', {
                        'name': this.name,
                        'nit': this.nit,
                        'address': this.address,
                        'phone': this.phone,
                    }).then(function (response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro creado correctamente.');
                            toastr.options.closeButton = true;
                            me.resetForm();
                            refreshTable('tbl-companies');
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
                    toastr.warning('Error en la validación');
                }
            }).catch(function (error) {
                toastr.warning('Error al intentar registrar.');
            });
        },
        update: function () {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    var me = this;
                    axios.put('companies/' + this.id, {
                        'name': this.name,
                        'nit': this.nit,
                        'address': this.address,
                        'phone': this.phone,
                    }).then(function (response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro actualizado correctamente');
                            toastr.options.closeButton = true;
                            me.editar = 0;
                            me.resetForm();
                            refreshTable('tbl-companies');
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
            }).catch(function (error) {
                toastr.warning('Error al intentar actualizar.');
            });
        },
        edit: function (data) {
            this.id = data['id'];
            this.name = data['name'];
            this.nit = data['nit'];
            this.address = data['address'];
            this.phone = data['phone'];
            this.editar = 1;
        },
        cancel: function () {
            this.resetForm();
        }
    },
});
