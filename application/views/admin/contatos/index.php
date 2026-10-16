<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if(isset($message)) {echo $message;} ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                  <?php if($can_create) { ?>
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo anchor('contatos/create', '<i class="fa fa-plus"></i> '. lang('contatos_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                  <?php } ?>
                    <div class="box-body">
                        <div class="row">
                          <div class="col-sm-12">
                            <div class="box box-default collapsed-box">
                              <div class="box-header with-border" style="cursor:default !important;">
                                <h3 class="box-title">Filtros Adicionais</h3>

                                <div class="box-tools pull-right">
                                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                  </button>
                                </div>
                                <!-- /.box-tools -->
                              </div>
                              <?php echo form_open('', array('class' => 'form-horizontal', 'id' => 'form-contatos')); ?>
                                <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
                                <div class="box-body">
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_q', 'filtro_q', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_input(array('id'=>'filtro_q','name'=>'q','value'=>@$q,'class'=>'form-control'));?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_unid', 'filtro_unid', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_dropdown($unidades);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_idade_de', 'filtro_idade_de', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-1">
                                            <?php echo form_input($idade_de);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_idade_ate', 'filtro_idade_ate', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-1">
                                            <?php echo form_input($idade_ate);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_inicio_label', 'filtro_inicio_label', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerInicio'>
                                            <?php echo form_input(array('id'=>'datetimepickerInicio','name'=>'data_filtro_inicio','value'=>'','class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_fim_label', 'filtro_fim_label', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerFim'>
                                            <?php echo form_input(array('id'=>'datetimepickerFim','name'=>'data_filtro_fim','value'=>'','class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_exportados_label', 'filtro_exportados_label', array('class' => 'col-sm-1 control-label'), false); ?>
                                        <div class="col-sm-2">
                                            <label class="radio-inline">
                                                <input type="radio" name="exportados_filtro" id="exportados_todos" value="" <?php echo (@$exportado == "" || @!isset($exportado)) ? 'checked="checked"' : '' ?>> Todos
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="exportados_filtro" id="exportados_sim" value="S" <?php echo (@$exportado == "S") ? 'checked="checked"' : '' ?>> Sim
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="exportados_filtro" id="exportados_nao" value="N" <?php echo (@$exportado == "N") ? 'checked="checked"' : '' ?>> Não
                                            </label>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                      <div class="col-sm-4 text-right">
                                        <button type="submit" name="btn_filtrar" class="btn btn-flat btn_form_contato"><i class="fa fa-search"></i> Filtrar</button>
                                      </div>
                                  </div>
                                </div>
                                <!-- /.box-body -->
                              <?php form_close(); ?>
                            </div>
                          </div>
                        </div>
                        <div class="row col-sm-12">
                            <table id="datatable_select_contatos" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th><?php echo lang('codigo');?></th>
                                        <th><?php echo lang('midia');?></th>
                                        <th><?php echo lang('evento');?></th>
                                        <th><?php echo lang('unidade');?></th>
                                        <th><?php echo lang('nome');?></th>
                                        <th><?php echo lang('celular');?></th>
                                        <th><?php echo lang('cidade');?></th>
                                        <th><?php echo lang('idade');?></th>
                                        <th><?php echo lang('visualizado');?></th>
                                        <th><?php echo lang('data');?></th>
                                        <th><?php echo lang('exportado');?></th>
                                        <th><?php echo lang('acao');?></th>
                                    </tr>
                                </thead>
                                <tbody>
    <?php foreach ($contatos as $values):?>
                                    <tr id="<?php echo $values->id ?>">
                                        <td></td>
                                        <td><?php echo $values->id; ?></td>
                                        <td><?php echo htmlspecialchars($values->midia_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->evento_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->nome, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->celular, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->cidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->idade, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php 
                                            if($values->visualizado == "N")
                                                echo "Não";
                                            else
                                                echo "Sim";
                                        ?></td>
                                        <td data-order="<?php echo strtotime($values->created_at);?>"><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($values->created_at)), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php 
                                            if($values->exportado == "N")
                                                echo "Não";
                                            else
                                                echo "Sim";
                                        ?></td>
                                        <td>
                                            <div class="btn-group btn-orders-all">
                                              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                              <ul class="dropdown-menu" role="menu">
                                                  <?php echo '<li><a href="javascript:void(0);" class="btn_view_contato" data-id="'.$values->id.'"><i class="fa fa-eye"></i> '.lang('actions_see').'</a></li>'; ?>
                                                  <?php echo ($can_create_oportunidade) ? '<li>'.anchor("oportunidades/create/".$values->id, '<i class="fa fa-plus-square"></i>'.lang('criar_oportunidade'), array()).'</li>' : ''; ?>
                                              </ul>
                                            </div>
                                        </td>
                                    </tr>
    <?php endforeach;?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>

<div style="display:none;">
<?php 
  foreach ($select_unidades_acoes as $key => $value) {
    echo "<div id='select_unidades_acoes_".$key."'>";
    echo $value;
    echo "</div>";
  }
?>
</div>

<!-- Modal -->
<div id="contatoModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Detalhes Contato</h4>
      </div>
      <div class="modal-body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>

  </div>
</div>
<style type="text/css">
    table.dataTable tr th.select-checkbox.selected::after {
        content: "✔";
        margin-top: -17px;
        margin-left: 0px;
        text-align: center;
        text-shadow: rgb(176, 190, 217) 1px 1px, rgb(176, 190, 217) -1px -1px, rgb(176, 190, 217) 1px -1px, rgb(176, 190, 217) -1px 1px;
    }

    table.dataTable th.select-checkbox::before {
        content: '';
        margin-top: -12px;
        margin-left: -1px;
        border: 1px solid black;
        border-radius: 3px;
    }

    table.dataTable th.select-checkbox::before, table.dataTable th.select-checkbox::after {
        display: block;
        width: 12px;
        height: 12px;
        box-sizing: border-box;
    }

    .toolbar {
        float: left;
    }
</style>
<script type="text/javascript">
    window.onload = function() {
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

        $('.multipleSelect').fastselect();

        $(".btn-box-tool").click(function() {
          $("#datatable2_filter").toggle();
          $('input[type="search"]').val('').keyup();
        });

        let datatable_contatos = $('#datatable_select_contatos').DataTable( {
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
                selector: 'tr>td:nth-child(1), tr>td:nth-child(2), tr>td:nth-child(3), tr>td:nth-child(4), tr>td:nth-child(5), tr>td:nth-child(6), tr>td:nth-child(7), tr>td:nth-child(8), tr>td:nth-child(9)'
            },
            "order": [[ 10, 'desc' ]],
        });

        <?php if($has_exportar || $has_alterar_unidade) { ?>
        $("div.toolbar").html('<form class="form-horizontal" id="form-acoes" action="<?php echo base_url('contatos') ?>" method="post"><input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>"><label>Ações: </label><?php echo $select_acoes ?><button type="submit" class="btn">Enviar</button></form>');
        <?php } ?>

        datatable_contatos.on("click", "th.select-checkbox", function() {
            if ($("th.select-checkbox").hasClass("selected")) {
                datatable_contatos.rows().deselect();
                $("th.select-checkbox").removeClass("selected");
            } else {
                datatable_contatos.rows().select();
                $("th.select-checkbox").addClass("selected");
            }
        });

        $( "#acoes" ).change(function() {
          if($(this).val() == "alterar_unidade") {
            $("#empresas").show();
            $("#unidades").show();
          } else {
            $("#empresas").hide();
            $("#unidades").hide();
          }
        });

        $( "#empresas" ).change(function() {
          if($(this).val() == "") {
            $("#unidades").html('<option value="">Selecione a Unidade</option>');
          }
          $("#unidades").html($('#select_unidades_acoes_'+$(this).val()).html());
        });

        $( "#form-acoes" ).submit(function( event ) {
          var table = $('#datatable_select_contatos').DataTable();
          var rows = table.rows({selected:  true});
          var num = rows.count();
          if(num == 0) {
            alert("Selecione algum contato para a ação desejada!");
            return false;
          }
          var data = rows.data();
          var url_submit = $('#base_url').val()+'contatos/'+$("#acoes").val();
          $('#form-acoes').attr('action', url_submit);
          var ids = "";
          var ids_count = 0;
          $.each( data, function( key, value ) {
            if(ids_count > 0) {
              ids += ", ";
            }
            ids += value["DT_RowId"];
            ids_count++;
          });
          $('#contatos').remove();
          $('<input>').attr({
              type: 'hidden',
              id: 'contatos',
              name: 'contatos',
              value: ids
          }).appendTo('#form-acoes');
        });
    }
</script>