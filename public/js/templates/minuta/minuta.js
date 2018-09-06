$(document).ready(function() {
	$('#modalRemanencias').on('hidden.bs.modal', function() {
        var table = $('#tbl-remanencias').DataTable();
        table.clear();
    });
});

function eliminarRemanencia(id,logical){
    var data =
    {
        id:id,
        logical:logical
    };
    objVue.delete(data);    
}

var objVue = new Vue({
    el: '#minuta',
    data: {
        name: null,
    },
    methods: {
        delete: function(data) {
            axios.delete('eliminarRemanencia/' + data.id).then(response => {
                refreshTable('tbl-remanencias');
                toastr.success("<div><p>Registro eliminado exitosamente.</p><button type='button' onclick='deshacerEliminar(" + data.id + ")' id='okBtn' class='btn btn-xs btn-danger pull-right'><i class='fa fa-reply'></i> Restaurar</button></div>");
                toastr.options.closeButton = true;
            });
        },
        rollBackDelete: function(data) {
            axios.get('restaurarRemanencia/' + data.id).then(response => {
                toastr.success('Registro restaurado.');
                refreshTable('tbl-remanencias');
            });
        },
    },
});