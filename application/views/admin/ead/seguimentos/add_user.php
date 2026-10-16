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
                    <div class="box-header">
                        <div class="form-group">
                            <?php
                                if(count($users) > 0){
                                    foreach ($users as $user) {
                                        echo '<input type="hidden" id="data_expiracao_'.$user->user_id.'" value="'.$user->data_expiracao.'">';
                                    }
                                }
                            ?>
                            <label for="user">Usuário:</label>
                            <select name="user" class="singleInputDynamic" id="user">
                                <option value="">Selecione</option>
                                <?php 
                                    if(count($users) > 0){
                                        foreach ($users as $user) {
                                            echo '<option value="'.$user->user_id.'">'.$user->first_name.' '.$user->last_name.'</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="button" name="btn_add_user" value="Adicionar" class="btn btn-primary btn-flat btn_add_user">
                        </div>
                    </div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('seguimentos_users')." ".$seguimento_label; ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if(count($users_seg > 0)) : ?>
                            <?php echo form_open(current_url(), array('class' => '', 'id' => 'form-users_seguimentos')); ?>
                                <div class="form-group div_form_users_seguimento">
                                    <?php foreach ($users_seg as $user) : ?>
                                        <input type="hidden" name="users[]" id="hidden-user-<?php echo $user->user_id ?>" value="<?php echo $user->user_id ?>">
                                        <ul class="list-group" id="ul-user-<?php echo $user->user_id ?>">
                                            <li class="list-group-item "><label>#</label><?php echo $user->user_id ?> &nbsp; <?php echo $user->first_name." ".$user->last_name ?> <?php echo form_button(array('type' => 'button', 'class' => 'btn btn-danger btn-lista', 'style' => 'float: right;', 'content' => '<i class="fa fa-times" aria-hidden="true"></i>', 'onclick' => "remove_user_seguimento('".$user->user_id."')")); ?></li>
                                            <li class="list-group-item"><label>Data Expiração:</label> <?php echo $user->data_expiracao ?></li>
                                        </ul> 
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-group">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo anchor('ead_seguimentos', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
