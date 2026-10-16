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
                                    <h3 class="box-title"><?php echo anchor('menus/create', '<i class="fa fa-plus"></i> '. lang('menus_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                                </div>
                                <div class="box-body">
                                    <table id="datatable" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th><?php echo lang('menus_nome');?></th>
                                                <th><?php echo lang('menus_pai');?></th>
                                                <th><?php echo lang('menus_order');?></th>
                                                <th><?php echo lang('menus_condicoes');?></th>
                                                <th><?php echo lang('menus_action');?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php foreach ($menus as $values):?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($values->nome, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($values->pai, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo $values->order; ?></td>
                                                <td><?php echo ($values->ativo == 1) ? anchor('menus/deactivate/'.$values->id, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('menus/activate/'. $values->id, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                                <td><?php echo anchor("menus/edit/".$values->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning', 'title'=>lang('actions_edit'))); ?></td>
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
