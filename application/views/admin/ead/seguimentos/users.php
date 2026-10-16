<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('seguimentos_users')." ".$seguimento_label; ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if(count($users > 0)) : ?>
                            <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-users_seguimentos')); ?>
                                <?php foreach ($users as $user) : ?>
                                    <!-- <input type="hidden" name="users[]" id="hidden-user-<?php echo $user->user_id ?>" value="<?php echo $user->user_id ?>"> -->
                                    <ul class="list-group" id="ul-user-<?php echo $user->user_id ?>">
                                        <li class="list-group-item "><label>#</label><?php echo $user->user_id ?> &nbsp; <?php echo $user->first_name." ".$user->last_name ?> <?php //echo form_button(array('type' => 'button', 'class' => 'btn btn-danger btn-lista', 'style' => 'float: right;', 'content' => '<i class="fa fa-times" aria-hidden="true"></i>', 'onclick' => "remove_user_seguimento('".$user->user_id."')")); ?></li>
                                        <li class="list-group-item">
                                            <p><label>Código Contrato:</label> <?php echo $user->contrato_id ?></p>
                                            <p><label>Data Expiração:</label> <?php echo $user->data_expiracao ?></p>
                                        </li>
                                    </ul> 
                                <?php endforeach; ?>
                                <div class="form-group">
                                <div class="col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo anchor('ead_seguimentos', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            </div>
                            <?php echo form_close();?>
                        <?php endif; ?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>
