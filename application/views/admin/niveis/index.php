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
                        <h3 class="box-title"><?php echo anchor('niveis/create', '<i class="fa fa-plus"></i> '. lang('niveis_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="col-md-6"><?php echo lang('descricao');?></th>
                                    <th class="col-md-2"><?php echo lang('cor');?></th>
                                    <th class="col-md-3"><?php echo lang('condicoes');?></th>
                                    <th class="col-md-1"><?php echo lang('acao');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($niveis as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->descricao, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><i class="fa fa-stop" style="color:<?php echo $values->cor; ?>"></i></td>
                                    <td><?php echo ($values->actived == 1) ? anchor('niveis/deactivate/'.$values->id, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('niveis/activate/'. $values->id, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                    <td><?php echo anchor("niveis/edit/".$values->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?></td>
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
