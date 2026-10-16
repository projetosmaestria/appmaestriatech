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
                        <h3 class="box-title"><?php echo anchor('catalogo_servicos/create', '<i class="fa fa-plus"></i> '. lang('catalogo_servicos_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('nome');?></th>
                                    <th><?php echo lang('produto');?></th>
                                    <th><?php echo lang('modulo');?></th>
                                    <th><?php echo lang('modulo_categoria');?></th>
                                    <th><?php echo lang('nivel');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($catalogo_servicos as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->descricao, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->desc_produto, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->desc_modulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->desc_categoria, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->desc_nivel, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($values->actived == '1') ? anchor('catalogo_servicos/deactivate/'.$values->id, '<span class="label label-success">'.lang('status_ativo').'</span>') : anchor('catalogo_servicos/activate/'. $values->id, '<span class="label label-default">'.lang('status_inativo').'</span>'); ?></td>
                                    <td class="text-center">
                                        <?php echo anchor("catalogo_servicos/edit/".$values->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?>
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