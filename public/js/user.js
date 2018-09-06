$(window).load(function() {
    $('#tbl-user').DataTable({
        dom: "<'row'<'col-sm-9 text-right'f><'col-sm-3 text-right'B><'floatright'>rtip>",
        buttons: [
            {
                extend: 'print',
                text: '<i class="fa fa-retweet" aria-hidden="true"></i> Remanencias',
                titleAttr: 'Imprimir',
                action: function ( e, dt, node, config ) {
                    $('#modalRemanencias').modal('show');
                }
            },
            {
                extend: 'collection',
                text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir <i class="fa fa-angle-double-down" aria-hidden="true"></i>',
                buttons: [
                    {
                        text: '<i class="fa fa-print" aria-hidden="true"></i> Pedido completo',
                        action: function ( e, dt, node, config ) {
                            //
                        }
                    },
                    {
                        text: '<i class="fa fa-tasks"></i> Por tipo de producto',
                        action: function ( e, dt, node, config ) {
                            $('#modalTipoProducto').modal('show');
                        }
                    }
                ]
            }

        ],
        ajax: 'user/all',
        columns: [
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {
                sortable: false,
                "render": function (data, type, full, meta) {
                    var params = [
                        full.id, 
                        "'"+full.name+"'", 
                        "'"+full.email+"'"
                    ];
                    var btn_edit =  "<a onclick=\"edit(" + params + ")\" class='btn btn-outline btn-success btn-xs' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fa fa-edit'></i></a> ";
                    var btn_delete = " <a onclick=\"eliminar(" + full.id + ","+true+")\" class='btn btn-outline btn-danger btn-xs' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-trash'></i></a> ";
                    return btn_edit + btn_delete;
                }
            }
        ]
    });
});