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
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo anchor('contratos/create', '<i class="fa fa-plus"></i> '. lang('contratos_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
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
                                <div class="box-body">
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
                                        <?php echo lang('filtro_status', 'filtro_status', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_dropdown($status);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_tipo', 'filtro_tipo', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_dropdown($tipo);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_cursos', 'filtro_cursos', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_dropdown($cursos);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_level', 'filtro_level', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_dropdown($level);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_pedido_inicio', 'filtro_pedido_inicio', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerInicio'>
                                            <?php echo form_input(array('id'=>'datetimepickerInicio','name'=>'data_pedido_inicio','value'=>'','class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_pedido_fim', 'filtro_pedido_fim', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerFim'>
                                            <?php echo form_input(array('id'=>'datetimepickerFim','name'=>'data_pedido_fim','value'=>'','class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_matricula_inicio', 'filtro_matricula_inicio', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerInicio2'>
                                            <?php echo form_input(array('id'=>'datetimepickerInicio2','name'=>'data_matricula_inicio','value'=>'','class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_matricula_fim', 'filtro_matricula_fim', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerFim2'>
                                            <?php echo form_input(array('id'=>'datetimepickerFim2','name'=>'data_matricula_fim','value'=>'','class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_acesso_inicio', 'filtro_acesso_inicio', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerInicio3'>
                                            <?php echo form_input(array('id'=>'datetimepickerInicio3','name'=>'data_acesso_inicio','value'=>'','class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_acesso_fim', 'filtro_acesso_fim', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerFim3'>
                                            <?php echo form_input(array('id'=>'datetimepickerFim3','name'=>'data_acesso_fim','value'=>'','class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                      <div class="col-sm-12 text-right">
                                        <button type="submit" name="btn_filtrar" class="btn btn-flat btn_form_contato"><i class="fa fa-search"></i> Filtrar</button>
                                      </div>
                                  </div>
                                </div>
                                <!-- /.box-body -->
                              <?php form_close(); ?>
                            </div>
                          </div>
                        </div>
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('id');?></th>
                                    <th><?php echo lang('filial');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('tipo');?></th>
                                    <th><?php echo lang('cliente');?></th>
                                    <th><?php echo lang('curso');?></th>
                                    <th><?php echo lang('level');?></th>
                                    <th><?php echo lang('dt_pedido');?></th>
                                    <th><?php echo lang('matricula');?></th>
                                    <th><?php echo lang('ultimo_acesso');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($contratos as $values):?>
                                <tr>
                                    <input type="hidden" id="seguimentos_<?php echo $values->id ?>" value="<?php echo htmlentities(json_encode($values->seguimentos)) ?>">
                                    <input type="hidden" id="desempenho_geral_<?php echo $values->id ?>" value="<?php echo $values->desempenho_geral ?>">
                                    <td><?php echo htmlspecialchars($values->id, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->unidade, ENT_QUOTES, 'UTF-8'); ?></td>  
                                    <td><?php echo htmlspecialchars($values->status_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($values->tipo_saxweb == 1)?'Padrão':'EAD'; ?></td>
                                    <td><?php echo htmlspecialchars($values->first_name." ".$values->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->curso_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo $values->level; ?></td>
                                    <td data-order="<?php echo strtotime($values->data_pedido);?>"><?php echo date("d/m/Y" , strtotime($values->data_pedido)); ?></td>
                                    <?php if($values->data_matricula == "0000-00-00" || $values->data_matricula == "") { ?>
                                      <td data-order="00000000">-</td>
                                    <?php } else { ?>
                                      <td data-order="<?php echo strtotime($values->data_matricula); ?>"><?php echo date("d/m/Y" , strtotime($values->data_matricula)); ?></td>
                                    <?php } ?>
                                    <?php if($values->ultimo_acesso == "0000-00-00" || $values->ultimo_acesso == "") { ?>
                                      <td data-order="00000000">-</td>
                                    <?php } else { ?>
                                      <td data-order="<?php echo strtotime($values->ultimo_acesso); ?>"><?php echo date("d/m/Y" , strtotime($values->ultimo_acesso)); ?></td>
                                    <?php } ?>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo base_url("users/profile/".$values->user_id) ?>"><i class="fa fa-user" aria-hidden="true"></i>Ver Profile Cliente</a></li>
                                                <li><a href="<?php echo base_url("users/historico/".$values->user_id) ?>"><i class="fa fa-history"></i>Histórico Cliente</a></li>
                                                <?php if($values->tipo_saxweb == 2 && $values->cliente_voxy == true){ ?>
                                                    <li><a href="javascript:void(0);" id="<?php echo $values->id ?>" class="btn_ead_contrato_seguimento"><i class="fa fa-user-circle-o"></i>Vincular Seguimento</a></li>
                                                <?php } ?>
                                                <?php if($values->tipo_saxweb == 2 && $values->cliente_voxy == false){ ?>
                                                    <li><a href="javascript:void(0);" id="<?php echo $values->user_id ?>" class="btn_ead_user_create"><i class="fa fa-external-link-square"></i>Criar Usuário EAD</a></li>
                                                <?php } ?>
                                                <li><a href="<?php echo base_url("contratos/ver_parcelas/".$values->id) ?>"><i class="fa fa-bars" aria-hidden="true"></i>Parcelas</a></li>
                                                <?php if($values->has_pesquisa == false){ ?>
                                                    <li><a href="<?php echo base_url("pesquisa_perfil/create/".$values->id) ?>"><i class="fa fa-tasks" aria-hidden="true"></i>Criar Pesquisa Perfil</a></li>
                                                <?php } else { ?>
                                                    <li><a href="<?php echo base_url("pesquisa_perfil/view/".$values->pesquisa_perfil->id) ?>"><i class="fa fa-tasks" aria-hidden="true"></i>Ver Pesquisa Perfil</a></li>
                                                <?php } ?>
                                                <?php if($values->can_alterar_desempenho == true){ ?>
                                                    <li><a href="javascript:void(0);" id="<?php echo $values->id ?>" class="btn_alterar_desempenho"><i class="fa fa-signal"></i>Alterar Desempenho</a></li>
                                                <?php } ?>
                                                <?php if($values->can_anular == true){ ?>
                                                    <li><a href="javascript:void(0);" id="<?php echo $values->id ?>" class="btn_anular_contrato"><i class="fa fa-ban"></i>Anular Contrato</a></li>
                                                <?php } ?>
                                                <?php if($values->can_gerar_segunda_via == true){ ?>
                                                    <li><a href="javascript:void(0);" id="<?php echo $values->id ?>" class="btn_2a_via"><i class="fa fa-barcode"></i>2a Via Entrada (Iugu)</a></li>
                                                <?php } 
                                                    if($values->can_cartao_recorrencia == true){ ?>
                                                    <li><a href="<?php echo base_url("iugu/cartao_recorrencia/".$values->id."/".$values->user_id) ?>"><i class="fa fa-credit-card" aria-hidden="true"></i>Ver Cartão da Recorrência (Iugu)</a></li>
                                                <?php }
                                                    if($values->can_alterar_assinatura == true){ ?>
													<li><a href="<?php echo base_url("iugu/ativar_assinatura/".$values->id) ?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>Ativar Assinatura (Iugu)</a></li>
                                                    <li><a href="<?php echo base_url("iugu/alterar_assinatura/".$values->id) ?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>Alterar Assinatura (Iugu)</a></li>
                                                <?php } 
                                                    if($values->can_incluir_assinatura == true){ ?>
                                                    <li><a href="<?php echo base_url("iugu/alterar_assinatura/".$values->id) ?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>Incluir Assinatura (Iugu)</a></li>
                                                <?php } 
                                                if($values->has_pdf == true){ 
                                                    echo '<li><a href="'.base_url("contratos/ver_pdf/".$values->id).'"><i class="fa fa-file-pdf-o" aria-hidden="true"></i>PDF Contrato</a></li>';
                                                } ?>
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

<!-- Modal -->
<div id="eadUserModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Integrar Usuário ao EAD</h4>
      </div>
      <div class="modal-body">
        <?php echo form_open(base_url(), array('class' => 'form-horizontal', 'id' => 'form-ead_user')); ?>
            <input type='hidden' id='user_id' name='user_id' value=''>
            <input type='hidden' id='base_url' name='base_url' value='<?php echo base_url();?>'>
            <div class="form-group">
                <?php echo lang('level', 'level', array('class' => 'col-sm-4 control-label'), true); ?>
                <div class="col-sm-6 input-group date">
                    <?php echo form_dropdown(array('id'=>'level','name'=>'level','options'=>array(""=>"Selecione","1"=>"1","2"=>"2","3"=>"3","4"=>"4"),'value'=>'','class'=>'form-control'));?>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('data_expiracao', 'data_expiracao', array('class' => 'col-sm-4 control-label'), true); ?>
                <div class="col-sm-6 input-group date" id='datetimepicker'>
                    <?php echo form_input(array('id'=>'data_expiracao','name'=>'data_expiracao','value'=>'','class'=>'form-control'));?>
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 text-right">
                    <div class="btn-group">
                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit_user'))); ?>
                    </div>
                </div>
            </div>
        <?php echo form_close();?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="eadContratoSeguimentoModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Vincular Contrato à Seguimento(s)</h4>
      </div>
      <div class="modal-body">
        <?php echo form_open(base_url('contratos/add_in_segment'), array('class' => 'form-horizontal', 'id' => 'form-ead_contrato_seguimento')); ?>
            <input type='hidden' id='contrato_id_seguimento' name='contrato_id' value=''>
            <input type='hidden' id='seguimentos_lista' name='seguimentos_lista' value='<?php echo json_encode($seguimentos_lista) ?>'>
            <div class="form-group">
                <?php echo lang('seguimento', 'seguimento', array('class' => 'col-sm-3 control-label'), true); ?>
                <div class="col-sm-8 selectSeguimentos">
                    <select class="multipleSelect" multiple name="seguimentos[]">
                        <?php foreach ($seguimentos_lista as $seg_list):
                            echo '<option selected value="'.$seg_list.'">'.$seg_list.'</option>';
                        endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-11 text-right">
                    <div class="btn-group">
                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                    </div>
                </div>
            </div>
        <?php echo form_close();?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="anularContratoModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Anular Contrato</h4>
      </div>
      <div class="modal-body html-anular">
        <div class="loading" style="display:none;">
            <i class="fa fa-refresh fa-spin" style="font-size: 30px;"></i>
        </div>
      </div>
      <div class="modal-footer">
        <?php echo form_open(base_url(), array('class' => 'form-horizontal', 'id' => 'form-contrato_anulado')); ?>
          <input type='hidden' id='contrato_id' name='contrato_id' value=''>
          <input type='hidden' id='base_url' name='base_url' value='<?php echo base_url();?>'>
          <button type="submit" class="btn btn-danger">Sim</button>
          <button type="button" data-dismiss="modal" class="btn btn btn-default">Não</button>
        <?php echo form_close();?>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="alterarDesempenhoModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Alterar Desempenho Contrato #<span id="span_contrato_desempenho"></span></h4>
      </div>
      <div class="modal-body">
        <?php echo form_open(base_url('contratos/alterar_desempenho'), array('class' => 'form-horizontal', 'id' => 'form-alterar_desempenho')); ?>
            <input type='hidden' id='contrato_id_desempenho' name='contrato_id' value=''>
            <div class="form-group">
                <?php echo lang('desempenho_geral', 'desempenho_geral', array('class' => 'col-sm-5 control-label'), true); ?>
                <div class="col-sm-7">
                    <div class="radio">
                        <label style="padding-left: 0px !important;" for="higher"><input type="radio" id="higher" name="desempenho_geral" value="higher" class="iradio_flat-green"><?php echo lang('higher') ?></label>
                    </div>
                    <div class="radio">
                        <label style="padding-left: 0px !important;" for="higher_medium"><input type="radio" id="higher_medium" name="desempenho_geral" value="higher_medium" class="iradio_flat-yellow"><?php echo lang('higher_medium') ?></label>
                    </div>
                    <div class="radio">
                        <label style="padding-left: 0px !important;" for="medium"><input type="radio" id="medium" name="desempenho_geral" value="medium" class="iradio_flat-orange"><?php echo lang('medium') ?></label>
                    </div>
                    <div class="radio">
                        <label style="padding-left: 0px !important; margin-right:5px;" for="emerging"><input type="radio" id="emerging" name="desempenho_geral" value="emerging" class="iradio_flat-red"><?php echo lang('emerging') ?></label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-11 text-right">
                    <div class="btn-group">
                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                    </div>
                </div>
            </div>
        <?php echo form_close();?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="2aViaIuguContratoModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <?php echo form_open(base_url(), array('class' => 'form-horizontal', 'id' => 'form-contrato_2via')); ?>
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Gerar 2a via Iugu</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
                <label class="col-sm-12">Deseja realmente gerar a 2a via da entrada na IUGU para o contrato <b>#<span id="span_contrato_2via"></span></b> ?</label>
            </div>
            <div class="form-group">
                <label for="expiracao" class="col-sm-3 control-label">Dias Expiração</label>
                <div class="col-sm-2">
                    <input type="text" name="expiracao" value="1" id="expiracao" class="form-control">
                </div>
            </div>
          </div>
          <div class="modal-footer">
              <input type='hidden' id='contrato_id_2via' name='contrato_id' value=''>
              <input type='hidden' id='base_url' name='base_url' value='<?php echo base_url();?>'>
              <button type="submit" class="btn btn-danger">Sim</button>
              <button type="button" data-dismiss="modal" class="btn btn btn-default">Não</button>
          </div>
        <?php echo form_close();?>
    </div>
  </div>
</div>

<script type="text/javascript">
      window.onload = function() {
         $('.multipleSelect').fastselect();
         $('#datetimepickerInicio').datetimepicker({
            defaultDate: "<?php echo @$data_pedido_inicio ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerFim').datetimepicker({
            defaultDate: "<?php echo @$data_pedido_fim ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerInicio2').datetimepicker({
            defaultDate: "<?php echo @$data_matricula_inicio ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerFim2').datetimepicker({
            defaultDate: "<?php echo @$data_matricula_fim ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerInicio3').datetimepicker({
            defaultDate: "<?php echo @$data_acesso_inicio ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerFim3').datetimepicker({
            defaultDate: "<?php echo @$data_acesso_fim ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });

        $('.iradio_flat-red').iCheck({
            checkboxClass: 'icheckbox_flat-red',
            radioClass: 'iradio_flat-red'
        });
        $('.iradio_flat-orange').iCheck({
            checkboxClass: 'icheckbox_flat-orange',
            radioClass: 'iradio_flat-orange'
        });
        $('.iradio_flat-yellow').iCheck({
            checkboxClass: 'icheckbox_flat-yellow',
            radioClass: 'iradio_flat-yellow'
        });
        $('.iradio_flat-green').iCheck({
            checkboxClass: 'icheckbox_flat-green',
            radioClass: 'iradio_flat-green'
        });
      };
</script>