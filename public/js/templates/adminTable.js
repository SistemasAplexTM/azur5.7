$(document).ready(function() {
    //
});
$(window).load(function() {
    var table = $('#tbl-admin_table').DataTable({
        ajax: table_name + '/all',
        columns: [{
            data: "name",
            name: 'name'
        }, {
            data: "description",
            name: 'description'
        }, {
            sortable: false,
            "render": function(data, type, full, meta) {
                var params = [
                    full.id, "'" + full.name + "'", "'" + full.description + "'"
                ];
                var btn_edit = "<a onclick=\"edit(" + params + ")\" class='btn btn-outline btn-success btn-xs' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fa fa-edit'></i></a> ";
                var btn_delete = " <a onclick=\"eliminar(" + full.id + "," + true + ")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                return btn_edit + btn_delete;
            }
        }]
    });
});

function edit(id, name, description) {
    var data = {
        id: id,
        name: name,
        description: description
    };
    objVue.edit(data);
}
const objVue = new Vue({
    el: '#admin_table',
    data: {
        name: null,
        description: null,
        editar: 0,
    },
    methods: {
        resetForm: function() {
            this.id = null;
            this.name = null;
            this.description = null;
            this.editar = 0;
            this.errors.clear();
        },
        rollBackDelete: function(data) {
            axios.get('administracion/' + table_name + '/restaurar/' + data.id).then(response => {
                toastr.success('Registro restaurado.');
                refreshTable('tbl-admin_table');
            });
        },
        delete: function(data) {
            axios.delete('administracion/' + table_name + '/delete/' + data.id).then(response => {
                refreshTable('tbl-admin_table');
                toastr.success("<div><p>Registro eliminado exitosamente.</p><button type='button' onclick='deshacerEliminar(" + data.id + ")' id='okBtn' class='btn btn-xs btn-danger pull-right'><i class='fa fa-reply'></i> Restaurar</button></div>");
                toastr.options.closeButton = true;
            });
        },
        store: function(name, description) {
            let me = this;
            this.$validator.validateAll().then((result) => {
                if (result) {
                    axios.post('../administracion', {
                        'name': me.name,
                        'description': me.description,
                        'table': table_name
                    }).then(function(response) {
                        toastr.success('Registro creado correctamente.');
                        toastr.options.closeButton = true;
                        me.resetForm();
                        refreshTable('tbl-admin_table');
                    }).catch(function(error) {
                        console.log(error);
                        toastr.warning(response.data['error']);
                        toastr.options.closeButton = true;
                    });
                }
            }).catch(function(error) {
                console.log(error);
                toastr.warning('Error al intentar registrar.');
            });
        },
        update: function() {
            let me = this;
            this.$validator.validateAll().then((result) => {
                if (result) {
                    axios.put('../administracion/update/' + table_name + '/' + me.id, {
                        'name': me.name,
                        'description': me.description
                    }).then(function(response) {
                        if (response.data['code'] == 200) {
                            toastr.success('Registro Actualizado correctamente');
                            toastr.options.closeButton = true;
                            me.editar = 0;
                            me.resetForm();
                            refreshTable('tbl-admin_table');
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
            this.editar = 1;
            this.mostrar_password = false;
        },
        cancel: function() {
            var me = this;
            me.resetForm();
        },
    }
});