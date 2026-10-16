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
                        <h3 class="box-title"><?php echo anchor('cupom/create', '<i class="fa fa-plus"></i> '. lang('cupom_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
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
                                        <?php echo lang('filtro_criacao_inicio', 'filtro_criacao_inicio', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerInicio'>
                                            <?php echo form_input(array('id'=>'datetimepickerInicio','name'=>'data_criacao_inicio','value'=>'','class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_criacao_fim', 'filtro_criacao_fim', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerFim'>
                                            <?php echo form_input(array('id'=>'datetimepickerFim','name'=>'data_criacao_fim','value'=>'','class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_expiracao_inicio', 'filtro_expiracao_inicio', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerInicio2'>
                                            <?php echo form_input(array('id'=>'datetimepickerInicio2','name'=>'data_expiracao_inicio','value'=>'','class'=>'form-control date_range_filter date'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_expiracao_fim', 'filtro_expiracao_fim', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-2 input-group date" id='datetimepickerFim2'>
                                            <?php echo form_input(array('id'=>'datetimepickerFim2','name'=>'data_expiracao_fim','value'=>'','class'=>'form-control date_range_filter date'));?>
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
                                <!-- /.box-body -->
                              <?php form_close(); ?>
                            </div>
                          </div>
                        </div>
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('cupom');?></th>
                                    <th><?php echo lang('data_criacao');?></th>
                                    <th><?php echo lang('data_expiracao');?></th>
                                    <th><?php echo lang('vendedor');?></th>
                                    <th><?php echo lang('utilizado_por');?></th>
                                    <th><?php echo lang('orcamento');?></th>
                                    <th><?php echo lang('actions');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($cupons as $values):?>
                                <tr>
                                    <td><?php echo $values->cupom; ?></td>
                                    <td data-order="<?php echo strtotime($values->data_criacao);?>"><?php echo date("d/m/Y", strtotime($values->data_criacao)); ?></td>
                                    <?php if($values->data_expiracao != "") { ?>
                                        <td data-order="<?php echo strtotime($values->data_expiracao);?>"><?php echo date("d/m/Y", strtotime($values->data_expiracao)); ?></td>
                                    <?php } else { ?>
                                        <td></td>  
                                    <?php } ?>
                                    <td><?php echo htmlspecialchars($values->vendedor, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->utilizado, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo $values->orc_id; ?></td>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <?php if($values->orc_id == "") { ?>
                                                <li><a href="<?php echo base_url("cupom/view_resend/".$values->id) ?>" title="Visualização/Reenvio"><i class="fa fa-eye"></i>Visualização/Reenvio</a></li>
                                                <li><a href="<?php echo base_url("cupom/edit/".$values->id) ?>" title="Editar"><i class="fa fa-edit"></i>Editar</a></li>
                                                <?php } else { ?>
                                                    <li><a href="<?php echo base_url("cupom/view_resend/".$values->id) ?>" title="Visualização"><i class="fa fa-eye"></i>Visualização</a></li>
                                                <?php } ?>
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


<script type="text/javascript">
      window.onload = function() {
         $('.multipleSelect').fastselect();
         $('#datetimepickerInicio').datetimepicker({
            defaultDate: "<?php echo @$data_criacao_inicio ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerFim').datetimepicker({
            defaultDate: "<?php echo @$data_criacao_fim ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerInicio2').datetimepicker({
            defaultDate: "<?php echo @$data_expiracao_inicio ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerFim2').datetimepicker({
            defaultDate: "<?php echo @$data_expiracao_fim ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
      };
</script>