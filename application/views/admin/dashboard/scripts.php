<script type="text/javascript">
  window.onload = function() {
    "use strict";
    
    <?php if(@$grafico_audios): ?>
    var line_audios = new Morris.Line({
      element: 'line-chart-audios',
      resize: true,
      data: <?php echo $grafico_audios ?>,
      xkey: 'y',
      ykeys: ['qtd'],
      labels: ['Qtd'],
      xLabels: 'week',
      lineColors: ['#3c8dbc'],
      hideHover: 'auto',
      gridIntegers: true,
      ymin: 0,
      yLabelFormat: function(y){return y != Math.round(y)?'':y;},
      xLabelFormat: function(d) {
        return d.getDate()+'/'+(d.getMonth()+1)+'/'+d.getFullYear(); 
        },  
    });
    <?php endif; ?>

    <?php if(@$grafico_chamados): ?>
    var line_chamados = new Morris.Line({
      element: 'line-chart-chamados',
      data: <?php echo $grafico_chamados ?>,
      xkey: 'month',
      ykeys: ['qc', 'q'],
      labels: ['Qtd Aberto', 'Qtd Fechado'],
      lineColors: ['#00a65a', '#D9534F'],
      lineWidth: '2px',
      hideHover: true,
      ymax: <?php echo $ymax ?>,
      gridIntegers: true,
      ymin: 0,
      yLabelFormat: function(y){return y != Math.round(y)?'':y;},
      xLabelAngle: 40,
      setAxisAlignFirstX: true,
      parseTime: false
    });
    <?php endif; ?>

    <?php if(@$json_nivel_alunos): ?>
    var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
    var pieChart       = new Chart(pieChartCanvas);
    var PieData        = <?php echo $json_nivel_alunos ?>;
    var pieOptions     = {
      segmentShowStroke    : true,
      segmentStrokeColor   : '#fff',
      segmentStrokeWidth   : 1,
      percentageInnerCutout: 50, 
      animationSteps       : 100,
      animationEasing      : 'easeOutBounce',
      animateRotate        : true,
      animateScale         : false,
      responsive           : true,
      maintainAspectRatio  : false,
      legendTemplate       : '<ul class=\'<%=name.toLowerCase()%>-legend\'><% for (var i=0; i<segments.length; i++){%><li><span style=\'background-color:<%=segments[i].fillColor%>\'></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',
      tooltipTemplate      : '<%=value %> aluno(s) <%=label%>'
    };
    pieChart.Doughnut(PieData, pieOptions);
    <?php endif; ?>

    <?php if(@$json_metas_atingidas): ?>
    var line_supervisor = new Morris.Line({
      element   : 'line-chart-supervisor',
      data      : <?php echo $json_metas_atingidas ?>, 
      xkey    : 'y', 
      ykeys     : <?php echo $json_x_metas_atingidas ?>, 
      labels    : <?php echo $json_labels_metas_atingidas ?>, 
      ymin: 0, 
      ymax: 100, 
      gridIntegers: true, 
      lineColors: ['#a0d0e0'], 
      hideHover : 'auto', 
      resize: true, yLabelFormat: function(y){return y != Math.round(y)?'':y;}, xLabelAngle: 40, setAxisAlignFirstX: true, parseTime: false }); 
    <?php endif; ?>


    $('#datatable_lista').DataTable({
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

    $('#datatable_device').DataTable({
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
         { orderable: false, targets: -1 }
      ],
      "order": [[ 1, 'asc' ]]
    });

    $('#datatable_audios').DataTable({
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
         { orderable: false, targets: -1 }
      ],
      "order": [[ 0, 'asc' ]]
    });

    $('#datatable_correcoes').DataTable({
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
      "order": [[ 1, 'asc' ]]
    });

    $('#datatable_order_chamados').DataTable({
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
         { orderable: false, targets: -1 }
      ],
      "order": [[ 4, 'desc' ]]
    });

    $( "#filtro_status" ).change(function() {
        var idade = "";
        if($('input[name=idade]:checked').val()) {
            idade = $('input[name=idade]:checked').val();
        }
        $.ajax({
          url: '<?php echo $base_url ?>'+'dashboard/get_alunos_map',
          type: 'POST',
          data: {status:$(this).val(), idade:idade},
          success: function(data){
            markersData = $.parseJSON(data);
            deleteMarkers();
            displayMarkers();
          }
        });
    });

    $("input[name='idade']").change(function(){
        var idade = "";
        if($(this).val()) {
            idade = $(this).val();
        }
        $.ajax({
          url: '<?php echo $base_url ?>'+'dashboard/get_alunos_map',
          type: 'POST',
          data: {status:$("#filtro_status").val(), idade:idade},
          success: function(data){
            markersData = $.parseJSON(data);
            deleteMarkers();
            displayMarkers();
          }
        });
    });

    $('#datatable_order_id_plano_acao').DataTable({
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
         { orderable: false, targets: -1 }
      ],
      "order": [[ 4, 'desc' ]]
    });

    $('#datatable_order_id2').DataTable({
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
         { orderable: false, targets: -1 }
      ],
      "order": [[ 0, 'desc' ]]
    });

    $('#datatable_order_id3').DataTable({
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
         { orderable: false, targets: -1 }
      ],
      "order": [[ 3, 'desc' ]]
    });

    $('#datatable_avaliacoes').DataTable({
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
         { orderable: false, targets: -1 }
      ],
      "order": [[ 5, 'desc' ]]
    });

    $('#datetimepickerInicio').datetimepicker({
        defaultDate: "<?php echo @$data_inicio ?>",
        locale:'pt-br',
        format: 'DD/MM/YYYY',
        showClose: true,
        icons: {
            close: 'closeText'
        }
    });
    $('#datetimepickerFim').datetimepicker({
        defaultDate: "<?php echo @$data_fim ?>",
        locale:'pt-br',
        format: 'DD/MM/YYYY',
        showClose: true,
        icons: {
            close: 'closeText'
        }
    });

    $( "#btn_filtrar" ).click(function() {
      $("#grafico1").html('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      $("#grafico2").html('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      $.ajax({
        url: '<?php echo base_url('dashboard/carregar_graficos_supervisos') ?>',
        type: 'POST',
        data: {tutor: $("#tutores").val(), inicio: $("#datetimepickerInicio").find("input").val(), fim: $("#datetimepickerFim").find("input").val()},
        success: function(data){
          var result = $.parseJSON(data);

          if(result.erro == false) {
            $("#grafico1").html(result.data.grafico1);
            $("#grafico2").html(result.data.grafico2);
          } else {
            alert(result.msg);
          }
        }
      });
    });

    $('#filtro_range').daterangepicker({
      ranges: {
        'Hoje': [moment(), moment()],
        'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
        'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
        'Este mês': [moment().startOf('month'), moment().endOf('month')],
        'Mês passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      },
      locale: {format: 'DD/MM/YYYY', separator: ' - ', applyLabel: 'Aplicar', cancelLabel: 'Cancelar', customRangeLabel: 'Personalizado'},
      "alwaysShowCalendars": true
    }, function(start, end, label) {
      $("#filtro_range").val(start.format('DD/MM/YYYY')+" - "+end.format('DD/MM/YYYY'));
      $('#form-grafico-aval').submit();
    });

    $('#filtro_range_aval').daterangepicker({
      ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      },
      "alwaysShowCalendars": true
    });

    $('.multipleSelect').fastselect();
  };
</script>