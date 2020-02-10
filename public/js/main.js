/*-- Acá van las funciones que se ejecutan al cargar el documento --*/
/* funcion para los tooltips de los botones */
$(function () {
  $('body').tooltip({
    selector: 'a[rel="tooltip"], [data-toggle="tooltip"]'
  });
  $.fn.datepicker.dates['es'] = {
    days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"],
    daysShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab", "Dom"],
    daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa", "Do"],
    months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Augosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
    monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
    today: "Hoy",
    clear: "Limpiar",
    format: "dd/mm/yyyy",
    titleFormat: "MM yyyy", /* Leverages same syntax as 'format' */
    weekStart: 0
  };
});

$(document).ready(function () {
  $('.ladda-button').ladda('bind', { timeout: 5000 });

  $.extend(true, $.fn.dataTable.defaults, {
    "language": {
      "paginate": {
        "previous": "Anterior",
        "next": "Siguiente",
      },
      /*"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json",*/
      "info": "Registros del _START_ al _END_  de un total de _TOTAL_",
      "search": "Buscar",
      "lengthMenu": "Mostrar _MENU_ Registros",
      "infoEmpty": "Mostrando registros del 0 al 0",
      "emptyTable": "No hay datos disponibles en la tabla",
      "infoFiltered": "(Filtrando para _MAX_ Registros totales)",
      "zeroRecords": "No se encontraron registros coincidentes",
    },

  });

});


/*-- Función para recargar datatables --*/
function refreshTable(tabla) {
  $('#' + tabla).dataTable()._fnAjaxUpdate();
};
/*-- Función para pasar el id de jQuery  a vue para eliminarlo --*/
function eliminar(id, logical) {
  var data =
  {
    id: id,
    logical: logical
  };
  objVue.delete(data);
}
/*-- Función para pasar el id de jQuery  a vue para deshacer el eliminado --*/
function deshacerEliminar(id) {
  var data =
  {
    id: id
  };
  objVue.rollBackDelete(data);
}

/* COMPROBAR SI UN NUMERO ES ENTERO */
function isInteger(numero) {
  numero = parseFloat(numero);
  if (numero % 1 == 0) {
    return numero;
  } else {
    return numero.toFixed(2);
  }
}
