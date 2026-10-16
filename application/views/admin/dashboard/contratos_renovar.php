<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// exit("<pre>".print_r($grafico,true)."</pre>");
?>

<section class="col-lg-12 connectedSortable">
  <div class="box box-info">
    <div class="box-header with-border">
      <i class="fa fa-file-text-o"></i>
      <h3 class="box-title">Contratos EAD a renovar</h3>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="box-body">
      <table id="datatable_contrato_ead_renovar" class="table">
        <thead>
            <tr>
                <th><?php echo lang('id');?></th>
                <th><?php echo lang('filial');?></th>
                <th><?php echo lang('cliente');?></th>
                <th><?php echo lang('curso');?></th>
                <th><?php echo lang('status');?></th>
                <th><?php echo lang('data_pedido');?></th>
                <th><?php echo lang('data_matricula');?></th>
                <th><?php echo lang('data_vencimento');?></th>
                <th><?php echo lang('actions');?></th>
            </tr>
        </thead>
        <tbody>
      <?php foreach ($contratos as $contrato):?>
            <tr>
                <input type="hidden" id="iugu_id_<?php echo $contrato->id ?>" value="<?php echo $contrato->iugu_id ?>">
                <td><?php echo $contrato->id; ?></td>
                <td><?php echo htmlspecialchars($contrato->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($contrato->first_name." ".$contrato->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($contrato->curso_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($contrato->status_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                <td data-order="<?php echo strtotime($contrato->data_pedido);?>"><?php echo date("d/m/Y" , strtotime($contrato->data_pedido)); ?></td>
                <?php if($contrato->data_matricula == "0000-00-00") { ?>
                  <td data-order="00/00/0000">-</td>
                <?php } else { ?>
                  <td data-order="<?php echo strtotime($contrato->data_matricula); ?>"><?php echo date("d/m/Y" , strtotime($contrato->data_matricula)); ?></td>
                <?php } ?>
                <td data-order="<?php echo strtotime($contrato->vencimento); ?>"><?php echo date("d/m/Y" , strtotime($contrato->vencimento)); ?></td>
                <td>
                  <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                      <ul class="dropdown-menu" role="menu">
                          <?php if($can_suspender == true): ?>
                              <li><a href="javascript:void(0);" id="<?php echo $contrato->id ?>" class="btn_suspender_assinatura"><i class="fa fa-ban"></i>Suspender Assinatura (Iugu)</a></li>
                          <?php endif; ?>
                          <?php if($can_alterar == true): ?>
                              <li><a href="<?php echo base_url("iugu/alterar_assinatura/".$contrato->id) ?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>Alterar Assinatura (Iugu)</a></li>
                          <?php endif; ?>
                      </ul>
                  </div>
              </td>
            </tr>
      <?php endforeach;?>
        </tbody>
      </table>
    </div>
  </div>
  <!-- /.box -->
</section>

<!-- Modal -->
<div id="suspenderAssinaturaModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Suspender Assinatura</h4>
      </div>
      <div class="modal-body">
        Deseja realmente suspender a assinatura <b>#<span id="span_suspender_assinatura"></span></b> ?
      </div>
      <div class="modal-footer">
        <?php echo form_open(base_url(), array('class' => 'form-horizontal', 'id' => 'form-assinatura_suspender')); ?>
          <input type='hidden' id='base_url' name='base_url' value='<?php echo base_url();?>'>
          <input type='hidden' id='iugu_id' name='iugu_id' value=''>
          <div class="form-group">
            <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), true); ?>
            <div class="col-sm-10">
                <?php echo form_dropdown($status);?>
            </div>
          </div>
          <div class="form-group">
            <?php echo lang('motivo', 'motivo', array('class' => 'col-sm-2 control-label'), true); ?>
            <div class="col-sm-10">
                <?php echo form_textarea($motivo);?>
            </div>
          </div>
          <button type="submit" class="btn btn-danger">Sim</button>
          <button type="button" data-dismiss="modal" class="btn btn btn-default">Não</button>
        <?php echo form_close();?>
      </div>
    </div>
  </div>
</div>