<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
.main .login-logo p {
    margin-top: 0px;
    margin-bottom: 0px;
    font-size: 14px;
    font-weight: bold;
    color: #5e5d5d;
}
</style>
<div class="row main-row fill">    
    <div class="col-sm-12 main">
        <div class="row h-100 row-middle">
            <div class="col-sm-12">
                <div class="login-logo">
                    <img src="<?php echo base_url('assets/images/login/page-login-logo.png')?>">
                    <?php echo $message; ?>
                </div>
                <?php if(@$ct_receb_id) { ?>
                    <p><center>Digite sua senha:</center></p>
                    <?php echo form_open('auth/ver_boleto/'.$ct_receb_id); ?>
                        <div class="input-group col-md-4 col-md-offset-4">
                            <span class="input-group-addon">
                                <img src="<?php echo base_url('assets/images/login/page-login-icon-pass.png')?>">
                            </span>
                            <?php echo form_input($password);?>
                        </div>
                        <div class="input-group col-md-4 col-md-offset-4">
                            <?php echo form_submit('submit', 'Ver boleto', array('class' => 'btn btn-primary btn-block btn-flat col-sm-2'));?>
                        </div>
                    <?php echo form_close();?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>