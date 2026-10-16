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
                        <h3 class="box-title"><?php echo anchor('empresas/create', '<i class="fa fa-plus"></i> '. lang('empresas_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="col-md-10"><?php echo lang('empresas_name');?></th>
                                    <th><?php echo lang('empresas_action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($empresas as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->nome, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo anchor("empresas/edit/".$values->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?></td>
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
