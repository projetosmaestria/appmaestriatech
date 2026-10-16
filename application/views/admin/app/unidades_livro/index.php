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
                        <h3 class="box-title"><?php echo anchor('app_unidades_livro/create', '<i class="fa fa-plus"></i> '. lang('create_unidades_livro'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
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
                              <?php echo form_open('', array('class' => 'form-horizontal', 'id' => 'form-exercicios')); ?>
                                <div class="box-body">
                                    <div class="form-group">
                                        <?php echo lang('livro', 'livro', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-10">
                                            <?php echo form_dropdown($livro);?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <?php echo lang('tipo', 'tipo', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-10">
                                            <?php echo form_dropdown($tipo);?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                          <div class="text-right col-sm-12">
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
                                    <th><?php echo lang('unidade');?></th>
                                    <th><?php echo lang('titulo');?></th>
                                    <th><?php echo lang('livro');?></th>
                                    <th><?php echo lang('tipo');?></th>
                                    <th><?php echo lang('data_cadastro');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($unidades_livro as $unidade):?>
                                <tr>
                                    <td><?php echo $unidade->unidade; ?></td>
                                    <td><?php echo htmlspecialchars($unidade->titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($unidade->titulo_livro, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($unidade->tipo == "O") ? "Obrigatória" : "Complementar"; ?></td>
                                    <td data-order="<?php echo strtotime($unidade->data_cadastro);?>"><?php echo date("d/m/Y H:i", strtotime($unidade->data_cadastro)); ?></td>
                                    <td><?php echo ($unidade->ativo == 'S') ? anchor('app_unidades_livro/deactivate/'.$unidade->id, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('app_unidades_livro/activate/'. $unidade->id, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                    <td>
                                        <?php echo anchor('app_unidades_livro/edit/'.$unidade->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?>
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