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
                                <!-- /.box-tools -->
                              </div>
                              <?php echo form_open(base_url('midias_eventos_orcamentos/index'), array('class' => 'form-horizontal', 'id' => 'form-midias_eventos_orcamentos')); ?>
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
                                        <?php echo lang('filtro_midia', 'filtro_midia', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_dropdown($midias);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_evento', 'filtro_evento', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_dropdown($eventos);?>
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
                                      <div class="col-sm-4 text-right">
                                        <button type="button" name="btn_exportar" class="btn btn-success btn-flat btn_form_evento_orc" value="exportar"><i class="fa fa-download"></i> Exportar</button>&nbsp;
                                        <button type="submit" name="btn_filtrar" class="btn btn-flat btn_form_evento_orc" value="index"><i class="fa fa-search"></i> Buscar</button>
                                      </div>
                                  </div>
                                </div>
                                <!-- /.box-body -->
                              <?php form_close(); ?>
                            </div>
                          </div>
                        </div>
                        <div class="row col-sm-12">
                            <table id="datatable2" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="col-md-2"><?php echo lang('evento');?></th>
                                        <th class="col-md-2"><?php echo lang('midia');?></th>
                                        <th class="col-md-2"><?php echo lang('unidade');?></th>
                                        <th class="col-md-2"><?php echo lang('data');?></th>
                                        <th class="col-md-1"><?php echo lang('qtd_leads');?></th>
                                        <th class="col-md-1"><?php echo lang('valor_lead');?></th>
                                        <th class="col-md-1"><?php echo lang('valor_gasto');?></th>
                                    </tr>
                                </thead>
                                <tbody>
    <?php if(count($orcamentos) > 0):?>
        <?php foreach ($orcamentos as $values):?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($values->evento_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->midia_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td data-order="<?php echo strtotime($values->data);?>"><?php echo htmlspecialchars(date("d/m/Y", strtotime($values->data)), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td align="right"><?php echo $values->qtd_leads; ?></td>
                                        <td align="right"><?php echo "R$ ".number_format($values->valor_lead, 2, ",", "."); ?></td>
                                        <td align="right"><?php echo "R$ ".number_format($values->valor_gasto, 2, ",", "."); ?></td>
                                    </tr>
        <?php endforeach;?>
    <?php endif;?>
                                </tbody>
                                <tfoot style="background-color:#98FB98;">
                                    <tr>
                                        <!-- <th colspan="6" style="text-align:right">Total Valor Gasto:</th> -->
                                        <td colspan="4">Total dos itens filtrados:</td>
                                        <td align="right"><?php echo $total_leads ?></td>
                                        <td align="right"><?php echo "R$ ".number_format($total_leads_por_valor_gasto, 2, ",", ".") ?></td>
                                        <td align="right"><?php echo "R$ ".number_format($total_valor_gasto, 2, ",", ".") ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>

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
    }
</script>