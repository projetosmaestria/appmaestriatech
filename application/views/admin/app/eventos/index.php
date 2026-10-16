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
                        <h3 class="box-title"><?php echo anchor('app_eventos/create', '<i class="fa fa-plus"></i> '. lang('app_eventos_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('titulo');?></th>
                                    <th><?php echo lang('data');?></th>
                                    <th><?php echo lang('notificado');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('acao');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($app_eventos as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->data_formatada, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($values->notificado == "Y") ? "Sim" : "Não"; ?></td>
                                    <td><?php echo ($values->actived == 'S') ? anchor('app_eventos/deactivate/'.$values->id, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('app_eventos/activate/'. $values->id, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                    <td>
                                        <?php echo anchor("app_eventos/edit/".$values->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?>
                                        &nbsp;
                                        <?php echo anchor("app_eventos/notification/".$values->id, '<i class="fa fa-mobile fa-lg"></i>', array('class'=>'btn btn-success btn-lista', 'title'=>lang('actions_notification'))); ?>
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
