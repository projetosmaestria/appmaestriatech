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
                        <h3 class="box-title"><?php echo anchor('chamados_categoria/create', '<i class="fa fa-plus"></i> '. lang('chamados_categoria_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="col-md-4"><?php echo lang('chamados_desc');?></th>
                                    <th class="col-md-4"><?php echo lang('chamados_setor');?></th>
                                    <th class="col-md-3"><?php echo lang('chamados_status');?></th>
                                    <th class="col-md-1"><?php echo lang('chamados_action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($categorias as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->descricao, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->setor, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($values->status == 1) ? anchor('chamados_categoria/deactivate/'.$values->id, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('chamados_categoria/activate/'. $values->id, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                    <td><?php echo anchor("chamados_categoria/edit/".$values->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?></td>
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
