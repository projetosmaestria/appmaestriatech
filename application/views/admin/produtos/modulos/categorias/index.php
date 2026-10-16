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
                        <h3 class="box-title"><?php echo anchor('produtos_modulos_categorias/create', '<i class="fa fa-plus"></i> '. lang('produtos_modulos_categorias_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('nome');?></th>
                                    <th><?php echo lang('produto');?></th>
                                    <th><?php echo lang('modulo');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($modulos_categorias as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->nome, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->nome_produto, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->nome_modulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($values->status == 'S') ? anchor('produtos_modulos_categorias/deactivate/'.$values->id, '<span class="label label-success">'.lang('status_ativo').'</span>') : anchor('produtos_modulos_categorias/activate/'. $values->id, '<span class="label label-default">'.lang('status_inativo').'</span>'); ?></td>
                                    <td class="text-center">
                                        <?php echo anchor("produtos_modulos_categorias/edit/".$values->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?>
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