
  let datatable_multiselect = $('#datatable_multiselect').DataTable( {
      "dom": '<"toolbar">frtip',
      'paging'      : true,
      'lengthChange': false,
      'searching'   : true,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false,
      'responsive'  : true,
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
        { orderable: false, className: 'select-checkbox', targets: 0 },
        { "responsivePriority": 1, targets: 0 },
        { "responsivePriority": 2, targets: -1 }
      ],
      "select": {
          style:    'multi',
          /*selector: 'tr>td:nth-child(1), tr>td:nth-child(2), tr>td:nth-child(3), tr>td:nth-child(4), tr>td:nth-child(5), tr>td:nth-child(6)'*/
      },
      "order": [[ 3, 'desc' ]],
  });

  datatable_multiselect.on("click", "th.select-checkbox", function() {
      if ($("th.select-checkbox").hasClass("selected")) {
          datatable_multiselect.rows().deselect();
          $("th.select-checkbox").removeClass("selected");
      } else {
          datatable_multiselect.rows().select();
          $("th.select-checkbox").addClass("selected");
      }
  });


  $( "#exportar_xls" ).click(function( event ) {
    event.preventDefault();

    var table = $('#datatable_multiselect').DataTable();
    var rows = table.rows({selected:  true});
    var num = rows.count();
    if(num == 0) {
      alert("Selecione algum item para exportar!");
      return false;
    }
    var data = rows.data();
    var url_submit = $("#url_exportar_xls").val();

    var ids = "";
    var ids_count = 0;
    $.each( data, function( key, value ) {
      if(ids_count > 0) {
        ids += ",";
      }
      ids += value[1];
      ids_count++;
    });

    var newForm = jQuery('<form>', {
        'action': url_submit,
        'method': 'post'
    }).append(jQuery('<input>', {
        'name': 'ids',
        'value': ids,
        'type': 'hidden'
    })).appendTo('body');
    
    newForm.submit();

  });
