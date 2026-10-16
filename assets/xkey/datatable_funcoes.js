$(document).ready(function(){
  jQuery.extend( jQuery.fn.dataTableExt.oSort, {
      "date-uk-pre": function ( a ) {
          if (a == null || a == "") {
              return 0;
          }
          var ukDatea = a.split('/');
          return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
      },
   
      "date-uk-asc": function ( a, b ) {
          return ((a < b) ? -1 : ((a > b) ? 1 : 0));
      },
   
      "date-uk-desc": function ( a, b ) {
          return ((a < b) ? 1 : ((a > b) ? -1 : 0));
      }
  } );

  $('#datatable').DataTable({
    'paging'      : true,
    'lengthChange': false,
    'searching'   : true,
    'ordering'    : true,
    'info'        : true,
    'autoWidth'   : false,
    "oLanguage": {
      "sProcessing":   "Processando...",
      "sLengthMenu":   "Mostrar _MENU_ registros",
      "sZeroRecords":  "Não foram encontrados resultados",
      "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
      "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
      "sInfoFiltered": "",
      "sInfoPostFix":  "",
      "sSearch":       "Buscar:",
      "sUrl":          "",
      "oPaginate": {
          "sFirst":    "Primeiro",
          "sPrevious": "Anterior",
          "sNext":     "Seguinte",
          "sLast":     "Último"
      }
    },
    "columnDefs": [ 
      { "responsivePriority": 1, targets: 0 },
      { "responsivePriority": 2, targets: -1 }
    ],
  });

  $('#datatable_no_paging').DataTable({
    'paging'      : true,
    'lengthChange': false,
    'searching'   : false,
    'ordering'    : true,
    'info'        : true,
    'autoWidth'   : false,
    "oLanguage": {
      "sProcessing":   "Processando...",
      "sLengthMenu":   "Mostrar _MENU_ registros",
      "sZeroRecords":  "Não foram encontrados resultados",
      "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
      "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
      "sInfoFiltered": "",
      "sInfoPostFix":  "",
      "sSearch":       "Buscar:",
      "sUrl":          "",
      "oPaginate": {
          "sFirst":    "Primeiro",
          "sPrevious": "Anterior",
          "sNext":     "Seguinte",
          "sLast":     "Último"
      }
    },
    "columnDefs": [ 
      { "responsivePriority": 1, targets: 0 },
      { "responsivePriority": 2, targets: -1 }
    ],
    "order": [[ 2, 'desc' ]]
  });

  $('#datatable_order_id').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : true,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false,
      "oLanguage": {
        "sProcessing":   "Processando...",
        "sLengthMenu":   "Mostrar _MENU_ registros",
        "sZeroRecords":  "Não foram encontrados resultados",
        "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
        "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
        "sInfoFiltered": "",
        "sInfoPostFix":  "",
        "sSearch":       "Buscar:",
        "sUrl":          "",
        "oPaginate": {
            "sFirst":    "Primeiro",
            "sPrevious": "Anterior",
            "sNext":     "Seguinte",
            "sLast":     "Último"
        }
      },
      responsive: true,
      columnDefs: [
        { orderable: false, targets: -1 },
        { "responsivePriority": 1, targets: 0 },
        { "responsivePriority": 2, targets: -1 }
      ],
      "order": [[ 0, 'desc' ]]
    });

  $('#datatable_contrato_ead').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : true,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false,
      "oLanguage": {
        "sProcessing":   "Processando...",
        "sLengthMenu":   "Mostrar _MENU_ registros",
        "sZeroRecords":  "Não foram encontrados resultados",
        "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
        "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
        "sInfoFiltered": "",
        "sInfoPostFix":  "",
        "sSearch":       "Buscar:",
        "sUrl":          "",
        "oPaginate": {
            "sFirst":    "Primeiro",
            "sPrevious": "Anterior",
            "sNext":     "Seguinte",
            "sLast":     "Último"
        }
      },
      columnDefs: [
         { orderable: false, targets: -1 },
         { type: 'date-uk', targets: 6 }
      ],  
      "order": [[ 6, 'desc' ]]
    });

    $('#datatable_contrato_ead_renovar').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : true,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false,
      "oLanguage": {
        "sProcessing":   "Processando...",
        "sLengthMenu":   "Mostrar _MENU_ registros",
        "sZeroRecords":  "Não foram encontrados resultados",
        "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
        "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
        "sInfoFiltered": "",
        "sInfoPostFix":  "",
        "sSearch":       "Buscar:",
        "sUrl":          "",
        "oPaginate": {
            "sFirst":    "Primeiro",
            "sPrevious": "Anterior",
            "sNext":     "Seguinte",
            "sLast":     "Último"
        }
      },
      columnDefs: [
         { orderable: false, targets: -1 },
         { type: 'date-uk', targets: 6 }
      ],  
      "order": [[ 7, 'desc' ]]
    });

  $('#datatable_select').DataTable( {
    'paging'      : true,
    'lengthChange': false,
    'searching'   : true,
    'ordering'    : true,
    'info'        : true,
    'autoWidth'   : false,
    "oLanguage": {
      "sProcessing":   "Processando...",
      "sLengthMenu":   "Mostrar _MENU_ registros",
      "sZeroRecords":  "Não foram encontrados resultados",
      "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
      "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
      "sInfoFiltered": "",
      "sInfoPostFix":  "",
      "sSearch":       "Buscar:",
      "sUrl":          "",
      "oPaginate": {
          "sFirst":    "Primeiro",
          "sPrevious": "Anterior",
          "sNext":     "Seguinte",
          "sLast":     "Último"
      },
      "select": {
          "rows": {
              _: "%d itens selecionados",
              0: "Selecione o(s) item(ns)",
              1: "1 item selecionado"
          }
      }
    },
    "columnDefs": [ 
      { orderable: false, className: 'select-checkbox', targets:   0 },
      { "responsivePriority": 1, targets: 0 },
      { "responsivePriority": 2, targets: -2 }
    ],
    "select": {
        style:    'multi',
        selector: 'tr>td:nth-child(1), tr>td:nth-child(2), tr>td:nth-child(3), tr>td:nth-child(4), tr>td:nth-child(5), tr>td:nth-child(6)'
    },
    "order": [[ 1, 'asc' ]]
  });

  var oTable = $('#datatable2').DataTable({
    'paging'      : true,
    'lengthChange': false,
    'searching'   : true,
    'ordering'    : true,
    'info'        : true,
    'autoWidth'   : false,
    "oLanguage": {
      "sProcessing":   "Processando...",
      "sLengthMenu":   "Mostrar _MENU_ registros",
      "sZeroRecords":  "Não foram encontrados resultados",
      "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
      "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
      "sInfoFiltered": "",
      "sInfoPostFix":  "",
      "sSearch":       "Buscar:",
      "sUrl":          "",
      "oPaginate": {
          "sFirst":    "Primeiro",
          "sPrevious": "Anterior",
          "sNext":     "Seguinte",
          "sLast":     "Último"
      }
    },
    responsive: true,
    columnDefs: [
      { type: 'date-uk', targets: 0 },
      { "responsivePriority": 1, targets: 0 },
      { "responsivePriority": 2, targets: -2 }
     ]
  });

$('#data_filtro_inicio').datetimepicker({
      locale:'pt-br',
      format: 'DD/MM/YYYY',
      showClose: true,
      icons: {
          close: 'closeText'
      }
  })/*.on("dp.change",function(date){ 
      var d = new Date(date.date);
      minDateFilter = new Date(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate()).getTime();
      console.log(d);
      oTable.draw();  
  })*/;

  $('#data_filtro_fim').datetimepicker({
      locale:'pt-br',
      format: 'DD/MM/YYYY',
      showClose: true,
      icons: {
          close: 'closeText'
      }
  })/*.on("dp.change",function(date){ 
      var d = new Date(date.date);
      //d.setDate(d.getDate()-1);
      console.log(d);
      maxDateFilter = new Date(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate()).getTime();
      oTable.draw();  
  })*/;

  var getTime = function(value, utc) {
    if(value != null) {
      // with dd/mm/yyyy then part[0] will be dd,
      // part[1] will be mm and part[2] will be yyyy
      var parts = value.split('/');
      // Date requires a zero referenced month
      parts[1] -= 1;
      if (utc) {
        return Date.UTC(parts[2], parts[1], parts[0]);
      }
      return new Date(parts[2], parts[1], parts[0]).getTime();
    }
  };

  var types = $.fn.dataTable.ext.type;

  // Add type detection
  types.detect.unshift(function(d) {
    var ms = getTime(d);
    var isValid = typeof ms === 'number' && !isNaN(ms);
    return isValid ? 'date-x' : null;
  });

  // Add sorting method - use an integer for the sorting
  types.order['date-x-pre'] = function(d) {
    return getTime(d);
  };

  // Date range filter
  var minDateFilter = '';
  var maxDateFilter = '';

  $.fn.dataTableExt.afnFiltering.push(
    function(oSettings, aData, iDataIndex) {
      if(oSettings.nTable.id == "datatable2") {
        if (typeof aData._date === 'undefined') {
          aData._date = getTimeBR(aData[5]);
        }
        if (minDateFilter && !isNaN(minDateFilter)) {
          console.log(aData._date +" <= "+ minDateFilter);
          if (aData._date <= minDateFilter) {
            return false;
          }
        }

        if (maxDateFilter && !isNaN(maxDateFilter)) {
          if (aData._date >= maxDateFilter) {
            return false;
          }
        }
        return true;
    } else {
      var row = oSettings.aoData[iDataIndex].nTr;
      return $(row).hasClass('template') ? false : true;
    }
  });

  function getTimeBR(data) {
    var arrayData = data.split('/');
    var campoDia = parseInt(arrayData[0]); 
    var campoMes = parseInt(arrayData[1]); 
    var campAno = parseInt(arrayData[2]);
 
    var dataUsuario = new Date();
    dataUsuario.setDate(campoDia);
    dataUsuario.setMonth(campoMes);
    //dataUsuario.setMonth(campoMes);
    dataUsuario.setFullYear(campAno);
    //dataUsuario.setUTCHours(0);
    //dataUsuario.setUTCMinutes(0);
    //dataUsuario.setUTCSeconds(0);

    return dataUsuario.getTime();
  }

  /*$('#exportados_filtro').on("click", function(e) {
      if ($("#exportados_filtro").is(":checked")) {
        oTable.columns(6)
          .search("Não")
          .draw();
      } else {
        oTable.columns(6)
          .search("")
          .draw();
      }
  });*/

  $('#prodsOportunidades').DataTable( {
    'paging'      : true,
    'lengthChange': false,
    'searching'   : true,
    'ordering'    : true,
    'info'        : true,
    'autoWidth'   : false,
    "oLanguage": {
        "sProcessing":   "Processando...",
        "sLengthMenu":   "Mostrar _MENU_ registros",
        "sZeroRecords":  "Não foram encontrados resultados",
        "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
        "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
        "sInfoFiltered": "",
        "sInfoPostFix":  "",
        "sSearch":       "Buscar:",
        "sUrl":          "",
        "oPaginate": {
            "sFirst":    "Primeiro",
            "sPrevious": "Anterior",
            "sNext":     "Seguinte",
            "sLast":     "Último"
        },
        "select": {
            "rows": {
                _: "%d itens selecionados",
                0: "Selecione o(s) item(ns)",
                1: "1 item selecionado"
            }
        }
    },
    columnDefs: [ {
        orderable: false,
        className: 'select-checkbox',
        targets:   0
    } ],
    select: {
        style:    'os',
        selector: 'tr'
    },
    order: [[ 1, 'asc' ]]
  });

  $('#leadsOportunidades').DataTable( {
    'paging'      : true,
    'lengthChange': false,
    'searching'   : true,
    'ordering'    : true,
    'info'        : true,
    'autoWidth'   : false,
    "oLanguage": {
        "sProcessing":   "Processando...",
        "sLengthMenu":   "Mostrar _MENU_ registros",
        "sZeroRecords":  "Não foram encontrados resultados",
        "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
        "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
        "sInfoFiltered": "",
        "sInfoPostFix":  "",
        "sSearch":       "Buscar:",
        "sUrl":          "",
        "oPaginate": {
            "sFirst":    "Primeiro",
            "sPrevious": "Anterior",
            "sNext":     "Seguinte",
            "sLast":     "Último"
        },
        "select": {
            "rows": {
                _: "%d itens selecionados",
                0: "Selecione o(s) item(ns)",
                1: "1 item selecionado"
            }
        }
    },
    columnDefs: [ {
        orderable: false,
        className: 'select-checkbox',
        targets:   0
    } ],
    select: {
        style:    'os',
        selector: 'tr'
    },
    order: [[ 1, 'asc' ]]
  });


  $('.produtosOportunidadeAccordion').DataTable( {
    "pageLength"  : 10,
    'paging'      : true,
    'lengthChange': false,
    'searching'   : true,
    'ordering'    : true,
    'info'        : true,
    'autoWidth'   : false,
    "oLanguage": {
      "sProcessing":   "Processando...",
      "sLengthMenu":   "Mostrar _MENU_ registros",
      "sZeroRecords":  "Não foram encontrados resultados",
      "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
      "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
      "sInfoFiltered": "",
      "sInfoPostFix":  "",
      "sSearch":       "Buscar:",
      "sUrl":          "",
      "oPaginate": {
          "sFirst":    "Primeiro",
          "sPrevious": "Anterior",
          "sNext":     "Seguinte",
          "sLast":     "Último"
      },
      "select": {
          "rows": {
              _: "%d itens selecionados",
              0: "Selecione o(s) item(ns)",
              1: "1 item selecionado"
          }
      }
    },
    "columnDefs": [ 
      { orderable: false, className: 'select-checkbox', targets:   0 },
      { "responsivePriority": 1, targets: 0 },
      { "responsivePriority": 2, targets: -2 }
    ],
    "select": {
        style:    'multi',
        selector: 'tr>td:nth-child(1), tr>td:nth-child(2), tr>td:nth-child(3), tr>td:nth-child(5)'
    },
    "order": [[ 1, 'asc' ]]
  });

});
