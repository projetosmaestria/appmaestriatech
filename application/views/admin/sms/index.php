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
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                                </div>
                                <!-- /.box-tools -->
                              </div>
                              <?php echo form_open(base_url('sms/index'), array('class' => 'form-horizontal', 'id' => 'form-sms')); ?>
                                <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
                                <div class="box-body">
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
                                        <button type="submit" name="btn_filtrar" class="btn btn-flat" value="index"><i class="fa fa-search"></i> Buscar</button>
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
                                        <th class="col-md-2"><?php echo lang('unidade');?></th>
                                        <th class="col-md-2"><?php echo lang('remetente');?></th>
                                        <th class="col-md-2"><?php echo lang('telefone');?></th>
                                        <th class="col-md-2"><?php echo lang('status_code');?></th>
                                        <th class="col-md-2"><?php echo lang('status_description');?></th>
                                        <th class="col-md-2"><?php echo lang('data_cadastro');?></th>
                                        <th class="col-md-2"><?php echo lang('acao');?></th>
                                    </tr>
                                </thead>
                                <tbody>
    <?php foreach ($sms as $values):?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($values->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->remetente, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->telefone, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->status_code, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->status_description, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(date("d/m/Y", strtotime($values->data_cadastro)), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php 
                                                $data_btn = array(
                                                    'name'          => 'button',
                                                    'title'         => lang('view'),
                                                    'value'         => $values->id,
                                                    'class'         => 'btn btn-info btn-lista btn_view_sms',
                                                    'type'          => 'button',
                                                    'content'       => '<i class="fa fa-eye"></i>'
                                                );
                                            ?>
                                            <?php echo form_button($data_btn); ?>
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

<!-- Modal -->
<div id="smsModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Detalhes SMS</h4>
      </div>
      <div class="modal-body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>

  </div>
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
    }
</script>