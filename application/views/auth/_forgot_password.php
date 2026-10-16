<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?>
<div class="login-logo">
    <a href="#"><b><?php echo $title_lg; ?></b></a>
</div>

<div class="login-box-body">
    <p class="login-box-msg"><?php echo lang('forgot_password_heading'); ?></p>
    <?php echo $message;?>
    <?php //echo sprintf(lang('forgot_password_subheading'), $identity_label);?>

    <?php echo form_open("auth/forgot_password");?>
        <div class="form-group has-feedback">
            <?php echo form_input($email);?>
            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        </div>
        <div class="row">
            <div class="col-xs-8"></div>
            <div class="col-xs-4">
                <?php echo form_submit('submit', lang('forgot_password_submit_btn'), array('class' => 'btn btn-primary btn-block btn-flat'));?>
            </div>
        </div>
    <?php echo form_close();?>
</div>