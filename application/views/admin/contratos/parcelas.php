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
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('vencimento');?></th>
                                    <th><?php echo lang('parcela');?></th>
                                    <th><?php echo lang('valor');?></th>
                                    <th><?php echo lang('data_pgto');?></th>
                                    <th><?php echo lang('valor_pago');?></th>
                                    <th><?php echo lang('desconto_pontualidade');?></th>
                                    <th><?php echo lang('tipo_recebimento');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($parcelas as $values):?>
                                <tr>
                                    <td data-order="<?php echo strtotime($values->vencimento);?>"><?php echo date("d/m/Y", strtotime($values->vencimento)) ?></td>
                                    <td><?php echo $values->parcela ?></td>
                                    <td><?php echo "R$ ".number_format($values->valor_receb, 2, ",", ".") ?></td>
                                    <td><?php echo ($values->DATA_PGTO == "0000-00-00" ) ? '-' : date("d/m/Y", strtotime($values->DATA_PGTO)) ?></td>
                                    <td><?php echo "R$ ".number_format($values->VALOR_PAGO, 2, ",", ".") ?></td>
                                    <td><?php echo "R$ ".number_format($values->valor_desconto_pontualidade, 2, ",", ".") ?></td>
                                    <td><?php echo htmlspecialchars($values->tipo_recebimento_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo $values->status_desc; ?></td>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="javascript:void(0);" id="<?php echo $values->id ?>" class="btn_hist_parcela" alt="Histórico (Iugu)" title="Histórico (Iugu)"><i class="fa fa-history"></i> Histórico (Iugu)</a></li>
                                                <?php 
                                                    /*if($values->status == "expired") {
                                                        echo '<li><a href="javascript:void(0);" id="'.$values->id.'" class="btn_2a_via_parcela" alt="2a Via Parcela (Iugu)" title="2a Via Parcela (Iugu)"><i class="fa fa-barcode"></i> 2a Via Parcela (Iugu)</a></li>';
                                                    }*/
                                                    if($values->status == "pending") {
														echo '<li><a href="javascript:void(0);" id="'.$values->id.'" class="btn_2a_via_parcela" alt="2a Via Parcela (Iugu)" title="2a Via Parcela (Iugu)"><i class="fa fa-barcode"></i> 2a Via Parcela (Iugu)</a></li>';
														echo '<li><a href="'.base_url('iugu/imprimir_fatura/'.$values->iugu_id).'" id="'.$values->id.'" alt="Imprimir Fatura (Iugu)" title="Imprimir Fatura (Iugu)" target="_blank"><i class="fa fa-print"></i> Imprimir Fatura (Iugu)</a></li>';
                                                    }
                                                    if($values->status == "pending" || ($values->status == "paid" && $values->DATA_PGTO == "0000-00-00" && $values->VALOR_PAGO == 0)) {
                                                      echo '<li><a href="'.base_url('iugu/conferir_fatura/'.$values->iugu_id).'" alt="Conferir Fatura (Iugu)" title="Conferir Fatura (Iugu)"><i class="fa fa-search-plus"></i> Conferir Fatura (Iugu)</a></li>';
                                                    }
                                                ?> 
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
<div id="2aViaIuguParcelaModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <?php echo form_open(base_url(), array('class' => 'form-horizontal', 'id' => 'form-parcela_2via')); ?>
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Gerar 2a via Iugu</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
                <label class="col-sm-12">Deseja realmente gerar a 2a via da parcela na IUGU para o contrato <b>#<span id="span_parcela_2via"></span></b> ?</label>
            </div>
            <div class="form-group">
                <label for="expiracao" class="col-sm-3 control-label">Dias Expiração</label>
                <div class="col-sm-2">
                    <input type="text" name="expiracao" value="1" id="expiracao" class="form-control">
                </div>
            </div>
          </div>
          <div class="modal-footer">
              <input type='hidden' id='parcela_id_2via' name='parcela_id' value=''>
              <input type='hidden' id='base_url' name='base_url' value='<?php echo base_url();?>'>
              <button type="submit" class="btn btn-danger">Sim</button>
              <button type="button" data-dismiss="modal" class="btn btn btn-default">Não</button>
          </div>
        <?php echo form_close();?>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="historicoParcelaIuguModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Histórico Parcela <span id="span_parcela_hist"></span> Iugu</h4>
      </div>
      <div class="modal-body bodyHistoricoParcela">
        <div class="overlay" style="display:none;">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
      </div>
      <div class="modal-footer">
          <button type="button" data-dismiss="modal" class="btn btn btn-default">Fechar</button>
      </div>
    </div>
  </div>
</div>