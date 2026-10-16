<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?>
<div class="row main-row fill">
    <div class="col-sm-8 sidebar hidden-xs">
    </div>
    
    <div class="col-sm-4 col-xs-12 main">
        <div class="row h-100 row-middle">
            <div class="col-sm-offset-2 col-sm-8">
                <div class="login-logo">
                    <img src="<?php echo base_url('assets/images/login/page-login-logo.png')?>">
                    <p>Preencha seus dados iniciais</p>
                </div>
                <div class="">
                    <?php if(isset($message)) {echo $message;} ?>
                </div>
                <?php echo form_open('auth/activate');?>
                    <?php echo form_hidden($hidden);?>
                    <div class="form-group has-feedback">
                        <?php echo form_input($first_name);?>
                    </div>
                    <div class="form-group has-feedback">
                        <?php echo form_input($last_name);?>
                    </div>
                    <div class="form-group has-feedback">
                        <?php echo form_input($password);?>
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
						<div class="progress" style="margin:0">
                            <div class="pwstrength_viewport_progress"></div>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <?php echo form_input($password_confirm);?>
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    <div class="row">
                        <div class="col-xs-8">
                        </div>
                        <div class="col-xs-4">
                            <?php echo form_submit('submit', lang('auth_save'), array('class' => 'btn btn-primary btn-block btn-flat'));?>
                        </div>
                    </div>
                <?php echo form_close();?>
            </div>
        </div>
    </div>
</div>
