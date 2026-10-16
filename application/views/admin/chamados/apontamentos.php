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
                              </div>
                              <?php echo form_open('', array('class' => 'form-horizontal', 'id' => 'form-apontamentos_filtro')); ?>
                                <div class="box-body">
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_usuarios', 'filtro_usuarios', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_dropdown($usuarios);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_filiais', 'filtro_filiais', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_dropdown($filial);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_inicio', 'filtro_inicio', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerInicio'>
                                            <?php echo form_input(array('id'=>'datetimepickerInicio','name'=>'data_inicio','value'=>isset($filtro_data_inicio) ? $filtro_data_inicio : date('01/m/Y'),'class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_fim', 'filtro_fim', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerFim'>
                                            <?php echo form_input(array('id'=>'datetimepickerFim','name'=>'data_fim','value'=>isset($filtro_data_fim) ? $filtro_data_fim : date('d/m/Y'),'class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                      <div class="col-sm-12 text-right">
                                        <button type="submit" name="btn_filtrar" class="btn btn-flat"><i class="fa fa-search"></i> Filtrar</button>
                                      </div>
                                  </div>
                                </div>
                              <?php form_close(); ?>
                            </div>
                          </div>
                        </div>
                        <table id="apont_datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('chamados_data');?></th>
                                    <th><?php echo lang('chamados_cliente');?></th>
                                    <th><?php echo lang('chamado');?></th>
                                    <th><?php echo lang('chamados_titulo_descricao');?></th>
                                    <th><?php echo lang('chamados_usuario');?></th>
                                    <th><?php echo lang('chamados_esforco');?></th>
                                    <th><?php echo lang('chamados_hora_inicio');?></th>
                                    <th><?php echo lang('chamados_hora_termino');?></th>
                                    <th>Tipo</th>
                                    <th><?php echo lang('actions');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($apontamentos->result() as $values):?>
                                <tr>
                                    <input type="hidden" name="data-<?php echo $values->id; ?>" id="data-<?php echo $values->id; ?>" value="<?php echo date("d/m/Y", strtotime($values->data)); ?>">
                                    <input type="hidden" name="esforco-<?php echo $values->id; ?>" id="esforco-<?php echo $values->id; ?>" value="<?php echo date("H:i", strtotime($values->esforco)); ?>">
                                    <input type="hidden" name="hora_inicio-<?php echo $values->id; ?>" id="hora_inicio-<?php echo $values->id; ?>" value="<?php echo date("H:i", strtotime($values->hora_inicio)); ?>">
                                    <input type="hidden" name="hora_termino-<?php echo $values->id; ?>" id="hora_termino-<?php echo $values->id; ?>" value="<?php echo date("H:i", strtotime($values->hora_termino)); ?>">
                                    <input type="hidden" name="observacao-<?php echo $values->id; ?>" id="observacao-<?php echo $values->id; ?>" value="<?php echo htmlspecialchars($values->observacao, ENT_QUOTES, 'UTF-8'); ?>">
                                    <td data-order="<?php echo strtotime($values->data); ?>"><?php echo date("d/m/Y" , strtotime($values->data)); ?></td>
                                    <td><?php  echo htmlspecialchars($values->client_apont, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo $values->chamado_id; ?></td>                                    
                                    <td><?php echo htmlspecialchars($values->chamado_titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->nome_usuario, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo date("H:i", strtotime($values->esforco)) ?></td>
                                    <td><?php echo date("H:i", strtotime($values->hora_inicio)) ?></td>
                                    <td><?php echo date("H:i", strtotime($values->hora_termino)) ?></td>
                                    <td><?php echo $values->tipo_descricao; ?></td>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <?php if($this->ion_auth->in_group(array(1, 3))) { ?>
                                                    <li><a href="javascript:void(0);" id="<?php echo $values->id ?>" class="btn_apontamento_editar"><i class="fa fa-edit"></i>Editar</a></li>
                                                <?php } ?>
                                                <?php if($this->ion_auth->in_group(array(1, 3))) { ?>
                                                    <li><a href="javascript:void(0);" id="<?php echo $values->id ?>" class="btn_apontamento_excluir"><i class="fa fa-trash"></i>Excluir</a></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
<?php endforeach;?>
                            </tbody>
                            <tfoot>
                              <tr>
                                <td colspan="8">Total Esforço: <?php echo $total_esforco ?></td>
                              </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>

<!-- Modal -->
<div id="modalProblem" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">OK</h4>
      </div>
      <div class="modal-body">
        <?php echo form_open(base_url(), array('class' => 'form-horizontal', 'id' => 'form-problem')); ?>
        <?php echo form_close();?>
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>

<?php if($this->ion_auth->in_group(array(1, 3))) { ?>
<!-- Modal -->
<div id="apontamentoExcluirModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Excluir Apontamento</h4>
      </div>
      <div class="modal-body">
        Deseja realmente excluir o apontamento ?
      </div>
      <div class="modal-footer">
        <?php echo form_open(base_url("chamados/apontamento_excluir"), array('class' => 'form-horizontal', 'id' => 'form-apontamento_excluir')); ?>
          <input type='hidden' id='apontamento_id_excluir' name='apontamento_id_excluir' value=''>
          <button type="submit" class="btn btn-danger">Sim</button>
          <button type="button" data-dismiss="modal" class="btn btn btn-default">Não</button>
        <?php echo form_close();?>
      </div>
    </div>
  </div>
</div>
<?php } ?>

<?php if($this->ion_auth->in_group(array(1, 3))) { ?>
<div id="apontarModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">
        <?php echo form_open(base_url('chamados/apontamento_editar'), array('class' => 'form-horizontal', 'id' => 'form-apontar_chamado')); ?>
            <input type='hidden' id='apontamento_id' name='apontamento_id' value=''>
            <div class="form-group">
                <?php echo lang('chamados_data', 'chamados_data', array('class' => 'col-sm-4 control-label'), true); ?>
                <div class="col-sm-3">
                    <div class="input-group date" id='datetimepicker'>
                        <?php echo form_input(array('id'=>'data','name'=>'data','value'=>date('d/m/Y'),'class'=>'form-control'));?>
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('chamados_hora_inicio', 'chamados_hora_inicio', array('class' => 'col-sm-4 control-label'), false); ?>
                <div class="col-sm-2">
                    <div class="input-group date" id='datetimehouronlypicker1'>
                        <?php echo form_input(array('id'=>'hora_inicio','name'=>'hora_inicio','value'=>'','class'=>'form-control'));?>
                        <span class="input-group-addon">
                            <i class="fa fa-clock-o"></i>
                        </span>
                    </div>
                    <em> (opcional) </em>
                </div>
            </div>
            <div class='form-group'>
                <label for='esforco' class="col-sm-4 control-label">Esforço <em>*</em></label>
                <div class="col-sm-2">
                    <?php echo form_input(array('id'=>'esforco','name'=>'esforco','value'=>'00:00','class'=>'form-control required', 'onkeypress'=>"formatTime(this)",'maxlength'=>"5"));?>
                    <em> (ex: 01:30) </em>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('chamados_hora_termino', 'chamados_hora_termino', array('class' => 'col-sm-4 control-label'), false); ?>
                <div class="col-sm-2">
                    <div class="input-group date" id='datetimehouronlypicker3'>
                        <?php echo form_input(array('id'=>'hora_termino','name'=>'hora_termino','value'=>'','class'=>'form-control'));?>
                        <span class="input-group-addon">
                            <i class="fa fa-clock-o"></i>
                        </span>
                    </div>
                    <em> (opcional) </em>
                </div>
            </div>
            <div class='form-group'>
                <label for='tipo' class="col-sm-4 control-label">Tipo <em>*</em></label>
                <div class="col-sm-6">
                    <select id="tipo" name="tipo" class="form-control required">
                        <option value="">Selecione</option>
                        <?php foreach ($tipos_apontamento as $tipo_ap) { ?>
                            <option value="<?php echo $tipo_ap->id; ?>"><?php echo $tipo_ap->descricao; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class='form-group'>
                <label for='obs' class="col-sm-4 control-label">Observação <em>*</em></label> 
                <div class="col-sm-6">
                    <textarea id='observacao' name='observacao' class='form-control required' rows='3' placeholder='Digite aqui...'></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 text-right">
                    <div class="btn-group">
                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit_chamado'))); ?>
                    </div>
                </div>
            </div>
        <?php echo form_close(); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>

  </div>
</div>
<?php } ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
  window.onload = function() {
    $('.multipleSelect').fastselect();

      $('#apont_datatable').DataTable({
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
      dom: 'Bfrtip',
        buttons: [
            'csv', 'excel', 'pdf'
        ],
      "columnDefs": [ 
        { "responsivePriority": 1, targets: 0 },
        { "responsivePriority": 2, targets: -1 }
      ],
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

    $(document).on("submit", "#form-apontar_chamado", function(e) {
        var tipo = $("#tipo").val();
        var observacao = $("#observacao").val();
        if(observacao !== undefined && observacao !== null) {
            observacao = observacao.trim();
        } else {
            observacao = "";
        }
        
        if (!tipo && !observacao) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Erro!",
                text: "Os campos Tipo e Observação são obrigatórios."
            });
            return false;
        } else if (!tipo) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Erro!",
                text: "O campo Tipo é obrigatório."
            });
            return false;
        } else if (!observacao) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Erro!",
                text: "O campo Observação é obrigatório."
            });
            return false;
        }
    });

  };
</script>