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
                        <h3 class="box-title"><?php echo anchor('app_banners/create', '<i class="fa fa-plus"></i> '. lang('app_banners_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('titulo');?></th>
                                    <!-- <th><?php echo lang('empresa');?></th> -->
                                    <th><?php echo lang('periodo_inicio');?></th>
                                    <th><?php echo lang('periodo_fim');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('acao');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($app_banners as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <!-- <td><?php echo htmlspecialchars($values->empresa_desc, ENT_QUOTES, 'UTF-8'); ?></td> -->
                                    <td><?php 
                                        if($values->periodo_indeterminado == "N") {
                                            echo $values->inicio;
                                        } else {
                                            echo "-";
                                        }
                                    ?></td>
                                    <td><?php 
                                        if($values->periodo_indeterminado == "N") {
                                            echo $values->fim;
                                        } else {
                                            echo "-";
                                        }
                                    ?></td>
                                    <td><?php echo ($values->actived == 'S') ? anchor('app_banners/deactivate/'.$values->id, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('app_banners/activate/'. $values->id, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                    <td><?php echo anchor("app_banners/edit/".$values->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?></td>
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
