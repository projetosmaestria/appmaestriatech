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
                                    <h3 class="box-title"><?php echo anchor('cursos/create', '<i class="fa fa-plus"></i> '. lang('create_curso'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                                </div>
                                <div class="box-body">
                                    <table id="datatable" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th><?php echo lang('descricao');?></th>
                                                <th><?php echo lang('sigla');?></th>
                                                <th><?php echo lang('categoria');?></th>
                                                <th><?php echo lang('data_cadastro');?></th>
                                                <th><?php echo lang('status');?></th>
                                                <th><?php echo lang('action');?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php foreach ($cursos as $curso):?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($curso->DESCRICAO, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($curso->SIGLA, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($curso->status_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td data-order="<?php echo strtotime($curso->DATA_CADASTRO);?>"><?php echo date("d/m/Y", strtotime($curso->DATA_CADASTRO)); ?></td>
                                                <td><?php echo ($curso->ATIVO == 'S') ? anchor('cursos/deactivate/'.$curso->ID_CURSO, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('cursos/activate/'. $curso->ID_CURSO, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                                <td>
                                                    <?php echo anchor('cursos/edit/'.$curso->ID_CURSO, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?>
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
