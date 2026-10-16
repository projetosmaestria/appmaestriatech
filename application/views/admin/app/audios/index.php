<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if($this->session->flashdata('message') != "") {echo $this->session->flashdata('message');} ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo anchor('app_audios/create', '<i class="fa fa-plus"></i> '. lang('app_audios_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
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
                              <?php echo form_open(base_url('app_audios/index'), array('class' => 'form-horizontal', 'id' => 'form-app_audios')); ?>
                                <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
                                <div class="box-body">
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_livros', 'filtro_livros', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_dropdown($livros);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_unidades', 'filtro_unidades', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_dropdown($unidades);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                      <div class="col-sm-4 text-right">
                                        <button type="submit" name="btn_filtrar" class="btn btn-flat"><i class="fa fa-search"></i> Filtrar</button>
                                      </div>
                                  </div>
                                </div>
                                <!-- /.box-body -->
                              <?php form_close(); ?>
                            </div>
                          </div>
                        </div>
                        <table id="datatable" class="table table-striped table-hover display responsive nowrap">
                            <thead>
                                <tr>
                                    <th><?php echo lang('ordem');?></th>
                                    <th><?php echo lang('titulo');?></th>
                                    <th><?php echo lang('livro');?></th>
                                    <th><?php echo lang('unidade');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('acao');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($app_audios as $values):?>
                                <tr>
                                    <td><?php echo $values->ordem; ?></td>
                                    <td><?php echo htmlspecialchars($values->titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->livro, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($values->ativo == 'S') ? anchor('app_audios/deactivate/'.$values->id, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('app_audios/activate/'. $values->id, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                    <td>
                                        <?php echo anchor("app_audios/edit/".$values->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?>
                                        <button type="button" class="btn btn-default btn-lista" onclick="loadAudioBase64('<?php echo $values->id ?>', '<?php echo base_url() ?>')"><i class="fa fa-play"></i></button>
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

    <!-- Modal -->
    <div class="modal fade" id="modalAudioBase64" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Ouvir Áudio</h4>                        
                </div><!--/modal-header-->
                <div class="modal-body">
                    
                </div><!--/.modal-body-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>
                </div><!--/.modal-footer-->
            </div><!--/.modal-content-->
        </div><!--/.modal-dialog-->
    </div><!--/.modal-->
</div>