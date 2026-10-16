<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// exit("<pre>".print_r($grafico,true)."</pre>");
?>

<section class="col-lg-12 connectedSortable">
  <div class="box box-info">
    <div class="box-header with-border">
      <i class="fa fa-file-text-o"></i>
      <h3 class="box-title">Últimos 20 contratos EAD</h3>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="box-body">
      <table id="datatable_contrato_ead" class="table">
        <thead>
            <tr>
                <th><?php echo lang('id');?></th>
                <th><?php echo lang('filial');?></th>
                <th><?php echo lang('cliente');?></th>
                <th><?php echo lang('curso');?></th>
                <th><?php echo lang('status');?></th>
                <th><?php echo lang('data_pedido');?></th>
                <th><?php echo lang('data_matricula');?></th>
                <th><?php echo lang('preco_produtos');?></th>
                <th><?php echo lang('preco_servicos');?></th>
                <th><?php echo lang('preco_total');?></th>
                <th><?php echo lang('actions');?></th>
            </tr>
        </thead>
        <tbody>
      <?php foreach ($contratos as $contrato):?>
            <?php $style = ($contrato->segment_id == "") ? "style='background-color:#FFBB99'" : "" ?>
            <tr <?php echo $style ?>>
                <input type="hidden" id="seguimentos_<?php echo $contrato->id ?>" value="<?php echo htmlentities(json_encode($contrato->seguimentos)) ?>">
                <td><?php echo $contrato->id; ?></td>
                <td><?php echo htmlspecialchars($contrato->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($contrato->first_name." ".$contrato->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($contrato->curso_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($contrato->status_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                <td data-order="<?php echo strtotime($contrato->data_pedido);?>"><?php echo date("d/m/Y" , strtotime($contrato->data_pedido)); ?></td>
                <?php if($contrato->data_matricula == "0000-00-00") { ?>
                  <td data-order="00/00/0000">-</td>
                <?php } else { ?>
                  <td data-order="<?php echo date("d/m/Y" , strtotime($contrato->data_matricula)); ?>"><?php echo date("d/m/Y" , strtotime($contrato->data_matricula)); ?></td>
                <?php } ?>
                <td><?php echo htmlspecialchars("R$".number_format($contrato->total_produtos, 2, ",", "."), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars("R$".number_format($contrato->total_servicos, 2, ",", "."), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars("R$".number_format($contrato->total_geral, 2, ",", "."), ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                  <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                      <ul class="dropdown-menu" role="menu">
                          <li><a href="<?php echo base_url("users/profile/".$contrato->user_id) ?>"><i class="fa fa-user" aria-hidden="true"></i>Ver Profile Cliente</a></li>
                          <li><a href="<?php echo base_url("users/historico/".$contrato->user_id) ?>"><i class="fa fa-history"></i>Histórico Cliente</a></li>
                          <?php if($contrato->cliente_voxy == true): ?>
                              <li><a href="javascript:void(0);" id="<?php echo $contrato->id ?>" class="btn_ead_contrato_seguimento"><i class="fa fa-user-circle-o"></i>Vincular Seguimento</a></li>
                          <?php endif; ?>
                          <?php if($contrato->cliente_voxy == false): ?>
                              <li><a href="javascript:void(0);" id="<?php echo $contrato->user_id ?>" class="btn_ead_user_create"><i class="fa fa-external-link-square"></i>Criar Usuário EAD</a></li>
                          <?php endif; ?>
                          <li><a href="<?php echo base_url("contratos/ver_parcelas/".$contrato->id) ?>"><i class="fa fa-bars" aria-hidden="true"></i>Parcelas</a></li>
                          <?php if($contrato->has_pesquisa == false): ?>
                            <li><a href="<?php echo base_url("pesquisa_perfil/create/".$contrato->id) ?>"><i class="fa fa-tasks" aria-hidden="true"></i>Criar Pesquisa Perfil</a></li>
                          <?php else: ?>
                            <li><a href="<?php echo base_url("pesquisa_perfil/view/".$contrato->pesquisa_perfil->id) ?>"><i class="fa fa-tasks" aria-hidden="true"></i>Ver Pesquisa Perfil</a></li>
                          <?php endif; ?>
                          <?php if($contrato->can_anular == true): ?>
                              <li><a href="javascript:void(0);" id="<?php echo $contrato->id ?>" class="btn_anular_contrato"><i class="fa fa-ban"></i>Anular Contrato</a></li>
                          <?php endif; ?>
                          <?php if($contrato->can_gerar_segunda_via == true): ?>
                              <li><a href="javascript:void(0);" id="<?php echo $contrato->id ?>" class="btn_2a_via"><i class="fa fa-barcode"></i>2a Via Entrada (Iugu)</a></li>
                          <?php endif; ?>
                          <?php if($contrato->can_cartao_recorrencia == true): ?>
                              <li><a href="<?php echo base_url("iugu/cartao_recorrencia/".$contrato->id."/".$contrato->user_id) ?>"><i class="fa fa-credit-card" aria-hidden="true"></i>Ver Cartão da Recorrência (Iugu)</a></li>
                          <?php endif; ?>
                          <?php if($contrato->can_alterar_assinatura == true): ?>
                              <li><a href="<?php echo base_url("iugu/alterar_assinatura/".$contrato->id) ?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>Alterar Assinatura (Iugu)</a></li>
                          <?php endif; ?>
						  <?php if($contrato->can_incluir_assinatura == true): ?>
                              <li><a href="<?php echo base_url("iugu/alterar_assinatura/".$contrato->id) ?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>Incluir Assinatura (Iugu)</a></li>
                          <?php endif; ?>
                      </ul>
                  </div>
              </td>
            </tr>
      <?php endforeach;?>
        </tbody>
      </table>
    </div>
    <div class="box-footer">
      <div class="form-group">
        <div class="col-sm-1"><p></p><div class="legend aberta"></div>Sem Seguimento<p></p></div>
      </div>
    <!-- /.box-body -->
  </div>
  <!-- /.box -->
</section>

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