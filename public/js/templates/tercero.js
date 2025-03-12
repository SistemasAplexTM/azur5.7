$(document).ready(function () {
    $('#tbl-tercero').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ajax: 'tercero/all',
        columns: [{
            data: 'document_nit',
            name: 'document_nit'
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
            sortable: false,
            "render": function (data, type, full, meta) {
                var params = [
                    full.id, "'" + full.name + "'", "'" + full.document_nit + "'", "'" + full.address + "'", "'" + full.phone + "'"
                ];
                var btn_edit = "<a onclick=\"edit(" + params + ")\" class='btn btn-outline btn-success btn-xs' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fa fa-edit'></i></a> ";
                var btn_delete = " <a onclick=\"eliminar(" + full.id + "," + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                return btn_edit + btn_delete;
            }
        }]
    });
});

function edit(id, name, document_nit, address, phone) {
    var data = {
        id: id,
        name: name,
        document_nit: document_nit,
        address: address,
        phone: phone
    };
    objVue.edit(data);
}

var objVue = new Vue({
    el: '#tercero',
    data: {
        name: null,
        document_nit: null,
        address: null,
        phone: null,
        editar: 0
    },
    methods: {
        resetForm: function () {
            this.name = null;
            this.document_nit = null;
            this.address = null;
            this.phone = null;
            this.editar = 0;
            this.errors.clear();
        },
        store: function () {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    let me = this;
                    axios.post('tercero', {
                        'name': this.name,
                        'document_nit': this.document_nit,
                        'address': this.address,
                        'phone': this.phone,
                    }).then(function (response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro creado correctamente.');
                            me.resetForm();
                            refreshTable('tbl-tercero');
                        } else {
                            toastr.warning(response.data['error']);
                        }
                    }).catch(function (error) {
                        toastr.error("Error. - " + error);
                    });
                } else {
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
                    axios.put('tercero/' + this.id, {
                        'name': this.name,
                        'document_nit': this.document_nit,
                        'address': this.address,
                        'phone': this.phone,
                    }).then(function (response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro Actualizado correctamente');
                            me.editar = 0;
                            me.resetForm();
                            refreshTable('tbl-tercero');
                        } else {
                            toastr.warning(response.data['error']);
                        }
                    }).catch(function (error) {
                        toastr.error("Error. - " + error);
                    });
                }
            }).catch(function (error) {
                toastr.warning('Error al intentar registrar.');
            });
        },
        edit: function (data) {
            this.id = data['id'];
            this.name = data['name'];
            this.document_nit = data['document_nit'];
            this.address = data['address'];
            this.phone = data['phone'];
            this.editar = 1;
        },
        cancel: function () {
            this.resetForm();
        }
    },
});
