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
                              <?php echo form_open(base_url('iugu/planos'), array('class' => 'form-horizontal', 'id' => 'form-iugu_planos')); ?>
                                <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
                                <div class="box-body">
                                    <?php echo lang('filtro_unidades', 'filtro_unidades', array('class' => 'control-label')); ?>
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <?php echo form_dropdown($unidades);?>
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
                                    <th><?php echo lang('filial');?></th>
                                    <th><?php echo lang('codigo');?></th>
                                    <th><?php echo lang('nome');?></th>
                                    <th><?php echo lang('identificador');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($planos as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values['unidade'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo $values['id'] ?></td>
                                    <td><?php echo htmlspecialchars($values['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values['identifier'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo base_url("iugu/ver_plano/".$values['unidade_id']."/".$values['identifier']) ?>"><i class="fa fa-search" aria-hidden="true"></i>Ver</a></li>
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

<?php if($mostrar_filtro) { ?>
<script>
    window.onload = function() {
        $(".btn-box-tool").click();
    }
</script>
<?php } ?>