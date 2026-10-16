<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if(isset($message)) {echo $message;} ?>
        <div id="msg"></div>
        <div class="row">
            <div class="col-md-9">
              <div class="box box-primary">
                <div class="box-body no-padding">
                  <!-- THE CALENDAR -->
                  <div id="calendar"></div>
                </div>
                <!-- /.box-body -->

                <div class="overlay" style="display:none;">
                  <i class="fa fa-refresh fa-spin"></i>
                </div>
              </div>
              <!-- /. box -->
            </div>
            <!-- /.col -->

            <div class="col-md-3">
                <?php if($can_config_google_calendar) { ?>
                  <div class="box box-solid">
                      <div class="box-header with-border">
                          <h4 class="box-title">Google Calendar</h4>
                      </div>
                      <div class="box-body">
                          <?php echo (@$user->client_id) ? '<p>Client ID: '.substr($user->client_id, 0, 50).'...</p>' : '' ?>
                          <?php echo (@$user->client_secret) ? '<p>Client Secret: '.substr($user->client_secret, 0, 50).'...</p>' : '' ?>
                      </div>
                      <!-- /.box-body -->
                      <div class="box-footer clearfix no-border" style="padding-top: 0px !important;">
                          <?php echo (@$login_google_calendar) ? '<a href="'.$login_google_calendar.'" class="btn btn-success btn-flat pull-left"><i class="fa fa-refresh"></i> Sincronizar</a>' : ''; ?>
                          <a href="<?php echo base_url('agenda/configurar_google_calendar/') ?>" class="btn btn-default btn-flat pull-right"><i class="fa fa-gear"></i> Configurar</a>
                      </div>
                  </div>
                <?php } ?>

                <?php if($can_criar_agenda) { ?>
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h4 class="box-title">Criar Agenda</h4>
                        </div>
                        <div class="box-body">
                            <div class="input-group">
                                <input id="new-agenda" type="text" class="form-control" placeholder="Descrição da Agenda">

                                <div class="input-group-btn">
                                  <button id="add-new-agenda" type="button" class="btn btn-primary btn-flat">Add</button>
                                </div>
                                <!-- /btn-group -->
                            </div>
                            <!-- /input-group -->
                        </div>
                        <!-- /.box-body -->
                        <div class="overlay" style="display:none;">
                          <i class="fa fa-refresh fa-spin"></i>
                        </div>
                    </div>
                <?php } ?>



                <?php if(isset($agenda_first)) { ?>
                    <div class="box box-solid">
                        <div class="box-header with-border">
                          <h4 class="box-title">Eventos</h4>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                              <div class="radio">
                                <label>
                                  <input type="radio" name="eventosRadio" id="eventosRadio1" value="filtrar" checked="">
                                  Mostrando apenas eventos alocados após o dia atual e não alocados
                                </label>
                              </div>
                              <div class="radio">
                                <label>
                                  <input type="radio" name="eventosRadio" id="eventosRadio2" value="todos">
                                  Mostrar todos
                                </label>
                              </div>
                            </div>
                          <!-- the events -->
                          <div id="external-events">
                            <?php
                                if(count($agenda_first->eventos_all) > 0) {
                                    foreach ($agenda_first->eventos_all as $value) { 
                                        if($can_atualizar_evento) {
                                            echo '<div id="'.$value['id'].'" class="external-event';

                                            if(@$value['start'] != "") {
                                                if($value["setado"] == "S" && date('Y-m-d H:i:s', strtotime($value['start'])) < date('Y-m-d H:i:s')) {
                                                    echo ' evento_passado';
                                                }
                                            }

                                            echo '" style="color:white; background-color:'.$value['backgroundColor'];

                                            if($value["setado"] == "S" && date('Y-m-d H:i:s', strtotime($value['start'])) < date('Y-m-d H:i:s')) {
                                                echo ' ;display:none;';
                                            }

                                            echo '"';

                                            if($value['setado'] == "S") {
                                                echo ' disabled="disabled"';
                                            }

                                            echo '>';

                                            if($value['start'] != "" && $value['setado'] == "S")
                                                echo "<span class='data'>".date('d/m/Y H:i', strtotime($value['start']))."</span> - <span class='title'>".$value['title'].'</span>';
                                            else
                                                echo "<span class='data'></span> - <span class='title'>".$value['title'].'</span>';

                                            echo "<span class='desc' style='display:none;'>".$value['desc'].'</span>';

                                            if($value['setado'] == "S") {
                                                echo '<button type="button" class="close remove_evento" data-dismiss="alert" aria-hidden="true" title="Remover" alt="Remover"><i class="fa fa-close"></i></button>';
                                            }
                                            echo '<button type="button" class="close editar_evento" title="Editar" alt="Editar" style="margin-right:5px;"><i class="fa fa-edit"></i></button>';
                                            if($value['setado'] == "S" && $can_registrar_tutoria) {
                                              echo "<button type='button' class='close tutoria_evento' title='Tutoria' alt='Tutoria' style='margin-right:5px;' onclick='window.location.href=\"".base_url('ead_tutoria/registrar_tutoria/evento/'.$value['id'])."\"'><i class='fa fa-graduation-cap'></i></button>";
                                            }
                                            echo '</div>';
                                        } else {
                                            echo '<div disabled="disabled" class="external-event';

                                            if($value["setado"] == "S" && date('Y-m-d H:i:s', strtotime($value['start'])) < date('Y-m-d H:i:s')) {
                                                echo ' evento_passado';
                                            }

                                            echo '" style="color:white; background-color:'.$value['backgroundColor'];

                                            if($value["setado"] == "S" && date('Y-m-d H:i:s', strtotime($value['start'])) < date('Y-m-d H:i:s')) {
                                                echo ' ;display:none;';
                                            }

                                            echo '">';

                                            if($value['start'] != "" && $value['setado'] == "S")
                                                echo "<span class='data'>".date('d/m/Y H:i', strtotime($value['start']))."</span> - <span class='title'>".$value['title'].'</span>';
                                            else
                                                "<span class='data'></span> - <span class='title'>".$value['title'].'</span>';

                                            echo "<span class='desc' style='display:none;'>".$value['desc'].'</span>';
                                            echo '</div>';
                                        }
                                    }
                                } else {
                                    echo "<p class='sem_eventos'>Não há evento criado no momento.</p>";
                                }
                            ?>
                          </div>
                        </div>
                        <div class="overlay" style="display:none;">
                          <i class="fa fa-refresh fa-spin"></i>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /. box -->
                <?php } ?>

                <?php if($can_criar_evento) { ?>
                    <div class="box box-solid">
                        <div class="box-header with-border">
                          <h3 class="box-title">Criar Evento</h3>
                        </div>
                        <div class="box-body">
                          <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                            <!--<button type="button" id="color-chooser-btn" class="btn btn-info btn-block dropdown-toggle" data-toggle="dropdown">Color <span class="caret"></span></button>-->
                            <ul class="fc-color-picker" id="color-chooser">
                              <li><a class="text-aqua" href="#"><i class="fa fa-square"></i></a></li>
                              <li><a class="text-blue" href="#"><i class="fa fa-square"></i></a></li>
                              <li><a class="text-light-blue" href="#"><i class="fa fa-square"></i></a></li>
                              <li><a class="text-teal" href="#"><i class="fa fa-square"></i></a></li>
                              <li><a class="text-yellow" href="#"><i class="fa fa-square"></i></a></li>
                              <li><a class="text-orange" href="#"><i class="fa fa-square"></i></a></li>
                              <li><a class="text-green" href="#"><i class="fa fa-square"></i></a></li>
                              <li><a class="text-lime" href="#"><i class="fa fa-square"></i></a></li>
                              <li><a class="text-red" href="#"><i class="fa fa-square"></i></a></li>
                              <li><a class="text-purple" href="#"><i class="fa fa-square"></i></a></li>
                              <li><a class="text-fuchsia" href="#"><i class="fa fa-square"></i></a></li>
                              <li><a class="text-muted" href="#"><i class="fa fa-square"></i></a></li>
                            </ul>
                          </div>
                          <!-- /btn-group -->
                          <div class="form-group">
                            <input id="new-event" type="text" class="form-control" placeholder="Título do Evento">
                          </div>
                          <div class="form-group">
                            <textarea id="new-event-text" class="form-control" placeholder="Descrição do Evento"></textarea>
                          </div>
                          <!-- /input-group -->
                        </div>
                        <div class="box-footer clearfix no-border" style="padding-top: 0px !important;">
                            <button type="submit" id="add-new-event" class="btn btn-primary btn-flat pull-right">Adicionar</button>
                        </div>
                        <div class="overlay" style="display:none;">
                          <i class="fa fa-refresh fa-spin"></i>
                        </div>
                    </div>
                <?php } ?>

                <?php if($can_atualizar_evento) { ?>
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h4 class="box-title">Alterar Agenda</h4>
                        </div>
                        <div class="box-body">
                            <?php echo form_dropdown($agenda);?>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer clearfix no-border" style="padding-top: 0px !important;">
                          <?php if($agenda_vinculada_google) { ?>
                          <span class="pull-left" style="color:orange;" ><i class="fa fa-google" aria-hidden="true"></i>oogle Calendar</span>
                          <?php } ?>
                          <a href="<?php echo base_url('agenda/configurar_agenda/'.$agenda_first->id) ?>" class="btn btn-default btn-flat pull-right"><i class="fa fa-gear"></i> Configurar</a>
                        </div>
                        <div class="overlay" style="display:none;">
                          <i class="fa fa-refresh fa-spin"></i>
                        </div>
                    </div>
                <?php } ?>

                <?php if($can_compartilhar_agenda) { ?>
                    <div class="box box-solid">
                        <form action="<?php echo base_url('agenda/compartilhar_agenda') ?>" method="post" id="form-agenda2">
                            <input type="hidden" name="agenda_compartilhar" value="<?php echo $agenda_first->id ?>">
                            <div class="box-header with-border">
                                <h4 class="box-title">Compartilhar Agenda</h4>
                            </div>
                            <div class="box-body">
                                <?php echo form_dropdown($compartilhar);?>
                            </div>
                            <div class="box-body">
                                <?php echo form_dropdown($role);?>
                            </div>
                            <div class="box-footer clearfix no-border" style="padding-top: 0px !important;">
                                <button type="submit" class="btn btn-primary btn-flat pull-right">Compartilhar</button>
                            </div>
                            <!-- /.box-body -->
                            <div class="overlay" style="display:none;">
                              <i class="fa fa-refresh fa-spin"></i>
                            </div>
                        </form>
                    </div>
                <?php } ?>
            </div>
            <!-- /.col -->
        </div>

        </div>
    </section>
</div>

<?php if(@$agenda_first) { ?>
<script>
  window.onload = function() {

    function init_events(ele) {
      ele.each(function () {

        var eventObject = {
          title: $.trim($(this).text())
        };

        $(this).data('eventObject', eventObject);

        if($(this).attr('disabled') == "disabled") {
            var disabled = true;
        } else {
            var disabled = false;
        }

        $(this).draggable({
          zIndex        : 1070,
          revert        : true, 
          revertDuration: 0,
          disabled: disabled  
        });

      })
    };

    init_events($('#external-events div.external-event'));

    var arrayJson;

    arrayJson = <?php echo json_encode($agenda_first->eventos_s) ?>;
    arrayJson.forEach(function(agenda_first){
        var start_data_part_um = agenda_first.start.split(" ");
        var start_data_hora = start_data_part_um[1].split(":");
        var start_data_part_dois = start_data_part_um[0].split("-");

        var end_data_part_um = agenda_first.end.split(" ");
        var end_data_hora = end_data_part_um[1].split(":");
        var end_data_part_dois = end_data_part_um[0].split("-");

        if(agenda_first.start != agenda_first.end && (start_data_part_um[1] != "00:00:00" && end_data_part_um[1] != "00:00:00")) {
            agenda_first.start = new Date(start_data_part_dois[0], start_data_part_dois[1] - 1, start_data_part_dois[2], start_data_hora[0], start_data_hora[1]);
            agenda_first.end = new Date(end_data_part_dois[0], end_data_part_dois[1] - 1, end_data_part_dois[2], end_data_hora[0], end_data_hora[1]);
        } else {
            var allDay = true;
            agenda_first.start = new Date(start_data_part_dois[0], start_data_part_dois[1] - 1, start_data_part_dois[2], end_data_hora[0], end_data_hora[1]);
            agenda_first.end = new Date(end_data_part_dois[0], end_data_part_dois[1] - 1, end_data_part_dois[2], end_data_hora[0], end_data_hora[1]);
            if((start_data_part_um[1] != "00:00:00" && end_data_part_um[1] != "00:00:00") && start_data_part_um[1] == end_data_part_um[1]) {
                agenda_first.end.setMinutes(agenda_first.end.getMinutes() + 30);
                allDay = false;
            }
            agenda_first.allDay = allDay;
        }
    });

    $('#calendar').fullCalendar({
      header    : {
        left  : 'prev,next today',
        center: 'title',
        right : 'month,agendaWeek,agendaDay'
      },
      buttonText: {
        today: 'hoje',
        month: 'mês',
        week : 'semana',
        day  : 'dia'
      },
      defaultView: 'agendaWeek',
      events : arrayJson,
      editable  : true,
      droppable : true, 
      <?php if(!$can_atualizar_evento) { ?>
      disableDragging: true,
      editable: false,
      <?php } ?>
      drop      : function (date, allDay) { 
        $.ajax({
            url: '<?php echo base_url('agenda/atualizar_evento') ?>',
            type: 'POST',
            data: {id: $(this).attr("id"), data_inicio: date.format(), data_fim: date.format(), agenda : '<?php echo $agenda_first->id ?>'},
            success: function(data){
                
            }
        });

        var originalEventObject = $(this).data('eventObject');

        var copiedEventObject = $.extend({}, originalEventObject);

        var all_day = false;
        var date_format = date.format().split("T");
        var data_format = date_format[0].split("-");
        if(date_format[1] == null) {
            all_day = true;
        } else {
            var hora_format = date_format[1].split(":");   
        }

        copiedEventObject.start           = date;
        copiedEventObject.allDay          = all_day;
        copiedEventObject.backgroundColor = $(this).css('background-color');
        copiedEventObject.borderColor     = $(this).css('background-color');
        copiedEventObject.id              = $(this).attr("id");
        
        $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);

        if(date_format[1] == null) {
            $(this).children('.data').html(data_format[2] + "/" + data_format[1] + "/" + data_format[0]);
        } else {
            $(this).children('.data').html(data_format[2] + "/" + data_format[1] + "/" + data_format[0] + " " + hora_format[0] + ":" + hora_format[1]);
        }
        $(this).prepend('<button type="button" class="close remove_evento" data-dismiss="alert" aria-hidden="true" title="Remover" alt="Remover"><i class="fa fa-close"></i></button>');
        <?php if($can_registrar_tutoria) { ?>
        $(this).append("<button type='button' class='close tutoria_evento' style='margin-right:5px;' data-dismiss='alert' aria-hidden='true' title='Tutoria' alt='Tutoria' onclick='window.location.href=\"<?php echo base_url('ead_tutoria/registrar_tutoria/evento/') ?>"+$(this).attr("id")+"\"'><i class='fa fa-graduation-cap'></i></button>");
        <?php } ?>

        $(this).draggable({disabled : true});
      },
      eventDrop : function(event, delta, revertFunc, jsEvent, ui, view) {
        $('.overlay').show();
        $.ajax({
            url: '<?php echo base_url('agenda/atualizar_evento') ?>',
            type: 'POST',
            data: {id: event.id, data_inicio: event.start.format().replace("T", " "), data_fim: event.start.format().replace("T", " ")},
            success: function(data){
                
            }
        });

        var date_format = event.start.format().split("T");
        var data_format = date_format[0].split("-");
        if(date_format[1] != null) {
            var hora_format = date_format[1].split(":");
            $("#external-events #"+event.id).children('.data').html(data_format[2]+"/"+data_format[1]+"/"+data_format[0]+" "+hora_format[0]+":"+hora_format[1]);
        } else {
            $("#external-events #"+event.id).children('.data').html(data_format[2]+"/"+data_format[1]+"/"+data_format[0]);
        }

        $('.overlay').hide();
      },
      eventResize: function (event, delta, revertFunc, jsEvent, ui, view) {
        $('.overlay').show();
        $.ajax({
            url: '<?php echo base_url('agenda/atualizar_evento') ?>',
            type: 'POST',
            data: {id: event.id, data_inicio: event.start.format().replace("T", " "), data_fim: event.end.format().replace("T", " ")},
            success: function(data){
                
            }
        });
        $('.overlay').hide();
      }
    });

    var currColor = 'rgb(0, 115, 183)';
    var colorChooser = $('#color-chooser-btn');

    $('#color-chooser > li > a').click(function (e) {
      e.preventDefault();
      currColor = $(this).css('color');
      $('#add-new-event').css({ 'background-color': currColor, 'border-color': currColor });
    });

    $('#add-new-event').click(function (e) {
      e.preventDefault();

      $('.overlay').show();

      var val = $('#new-event').val();
      if (val.length == 0) {
        return;
      }

      var val_desc = $('#new-event-text').val();

      if($(this).val() != null && $(this).val() != "") {
        var evento_id = $(this).val();
        $.ajax({
            url: '<?php echo base_url('agenda/atualizar_evento') ?>',
            type: 'POST',
            data: {id: evento_id, titulo:val, desc:val_desc, cor: rgb2hex(currColor), agenda : '<?php echo $agenda_first->id ?>'},
            success: function(data){
               $('#msg').html('<div class="alert alert-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-close"></i></button> <h4><i class="icon fa fa-check"></i> Evento atualizado!</h4></div>');
            }
        });
        $('#external-events #'+evento_id).children('.title').html(val);
        $('#external-events #'+evento_id).children('.desc').html(val_desc);
        $('#new-event').val('');
        $('#new-event-text').val('');
        $(this).html("Adicionar");
        $(this).removeAttr('value');
    } else {
        $.ajax({
            url: '<?php echo base_url('agenda/criar_evento') ?>',
            type: 'POST',
            data: {titulo:val, desc:val_desc, cor: rgb2hex(currColor), agenda : '<?php echo $agenda_first->id ?>'},
            success: function(data){
                var result = $.parseJSON(data);
                if(result.erro == false) {
                    $('.sem_eventos').remove();
                    var event = $('<div />');
                    event.css({
                        'background-color': currColor,
                        'border-color'    : currColor,
                        'color'           : '#fff'
                    }).addClass('external-event');
                    event.attr('id', result.data.id);
                    event.html("<span class='data'></span> - <span class='title'>"+val+'</span><button type="button" class="close editar_evento" title="Editar" alt="Editar" style="margin-right:5px;"><i class="fa fa-edit"></i></button>');
                    $('#external-events').prepend(event);

                    init_events(event);

                    $('#new-event').val('');
                    $('#new-event-text').val('');
                    $('#msg').html('<div class="alert alert-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-close"></i></button> <h4><i class="icon fa fa-check"></i> Evento criado!</h4> Você pode arrastar para o evento e soltar no calendário para agendá-lo.</div>');
                }
            }
        });
      }

      $('.overlay').hide();
    });

    $('#att-new-event').click(function (e) {
      alert("teste");
    });

    $('#add-new-agenda').click(function (e) {
      e.preventDefault();

      $('.overlay').show();

      var val = $('#new-agenda').val();
      if (val.length == 0) {
        return;
      }

      $.ajax({
        url: '<?php echo base_url('agenda/criar_agenda') ?>',
        type: 'POST',
        data: {descricao:val},
        success: function(data){
            var result = $.parseJSON(data);
            if(result.erro == false) {
                $('#agenda').append('<option value="'+result.data.id+'">'+result.data.descricao+'</option>');
                $('#msg').html('<div class="alert alert-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-close"></i></button> <h4><i class="icon fa fa-check"></i> Agenda criada!</h4> Você pode selecionar sua nova agenda em "Alterar Agenda".</div>'); 
            }
        }
      });

      $('#new-agenda').val('');
      $('.overlay').hide();
    });

    $('#agenda').change(function (e) {
        if($(this).val() != "") {
            $('#form-agenda').submit();
            window.location.href = '<?php echo base_url('agenda/index/') ?>'+$(this).val();
        }
    });

    $(document).on("click", ".remove_evento", function() {
        $('.overlay').show();

        var attr_id = $(this).parent().attr('id');
        var parent = $(this).parent();
        var element = $(this);

        $.ajax({
            url: '<?php echo base_url('agenda/remover_evento') ?>',
            type: 'POST',
            data: {id:attr_id, agenda : '<?php echo $agenda_first->id ?>'},
            success: function(data){
                $('#calendar').fullCalendar('removeEvents', attr_id);

                parent.children('.data').html('');
                parent.children('.tutoria_evento').remove();
                element.remove();

                $('#msg').html('<div class="alert alert-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-close"></i></button> <h4><i class="icon fa fa-check"></i> Evento removido!</h4> Você pode alocar o evento novamente caso queira arrastando-o para o calendário.</div>'); 
            }
        });

        parent.draggable({disabled : false});
        $('.overlay').hide();
    });

    $(document).on("click", ".editar_evento", function() {
        $('.overlay').show();

        var attr_id = $(this).parent().attr('id');
        var parent = $(this).parent();
        var element = $(this);

        var title = parent.children('.title').html();
        var desc = parent.children('.desc').html();
        $("#new-event").val(title);
        $("#new-event-text").val(desc);

        $("#add-new-event").val(attr_id);
        $("#add-new-event").html("Salvar");

        $('.overlay').hide();
    });

    var hexDigits = new Array
        ("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"); 

    function rgb2hex(rgb) {
        rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
    }

    function hex(x) {
        return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
    }

    $('input[type=radio][name=eventosRadio]').change(function() {
        if (this.value == 'todos') {
            $('.evento_passado').fadeIn();
        }
        else if (this.value == 'filtrar') {
            $('.evento_passado').fadeOut();
        }
    });

  };
</script>
<?php } else { ?>
<script>
  window.onload = function() {
    $('#add-new-agenda').click(function (e) {
      e.preventDefault();

      $('.overlay').show();

      var val = $('#new-agenda').val();
      if (val.length == 0) {
        return;
      }

      $.ajax({
        url: '<?php echo base_url('agenda/criar_agenda') ?>',
        type: 'POST',
        data: {descricao:val},
        success: function(data){
            var result = $.parseJSON(data);
            if(result.erro == false) {
                $('#agenda').append('<option value="'+result.data.id+'">'+result.data.descricao+'</option>');
                window.location.href = '<?php echo base_url('agenda/index/') ?>';
            }
        }
      });

      $('#new-agenda').val('');
      $('.overlay').hide();
    });

  };
</script>
<?php } ?>