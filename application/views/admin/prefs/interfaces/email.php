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
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class=""><?php echo anchor('prefs/index', lang('menu_int_admin')); ?></li>
                        <li class=""><?php echo anchor('prefs/dashboard', lang('menu_int_dashboard')); ?></li>
                        <li class="active"><?php echo anchor('prefs/email', lang('menu_int_email')); ?></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_admin">
                            <?php if($this->session->flashdata('message') != "") {echo $this->session->flashdata('message');} ?>
                            <?php if($message_admin != "") {echo $message_admin;} ?>
                            <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-interface_email')); ?>
                                <div class="box">
                                    <div class="box-header with-border">
                                        <h3 class="box-title"><?php echo lang('saida'); ?></h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_protocol'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($protocol);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_host'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($host);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_port'); ?></label>
                                            <div class="col-sm-1">
                                                <?php echo form_input($port);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_user'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($user);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_pass'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($pass);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_from_email'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($from_email);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_from_name'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($from_name);?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer">
                                        <div class="form-group">
                                            <div class="col-sm-offset-2 col-sm-10">
                                                <div class="btn-group">
                                                    <?php 
                                                        if($config_email->test == 0) {
                                                            echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'name' => 'acao', 'content' => lang('actions_test'), 'value' => 'test')); 
                                                        } else {
                                                            echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'name' => 'acao', 'content' => lang('actions_update'), 'value' => 'att')); 
                                                            echo form_button(array('type' => 'submit', 'class' => 'btn btn-danger btn-flat', 'name' => 'acao', 'value' => 'remover', 'content' => lang('actions_remove'))); 
                                                        }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box">
                                    <div class="box-header with-border">
                                        <h3 class="box-title"><?php echo lang('entrada'); ?></h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_protocol'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($protocol_saida);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_host'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($host_saida);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_port'); ?></label>
                                            <div class="col-sm-1">
                                                <?php echo form_input($port_saida);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_user'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($user_saida);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_pass'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($pass_saida);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_cadastrar_usu'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_checkbox($cadastrar_usu);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?php echo lang('prefs_unidade_padrao'); ?></label>
                                            <div class="col-sm-10">
                                                <?php echo form_dropdown($unidade_padrao);?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer">
                                        <div class="form-group">
                                            <div class="col-sm-offset-2 col-sm-10">
                                                <div class="btn-group">
                                                    <?php 
                                                        if($config_email->test_saida == 0) {
                                                            echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'name' => 'acao', 'content' => lang('actions_test'), 'value' => 'test_saida')); 
                                                        } else {
                                                            echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'name' => 'acao', 'content' => lang('actions_update'), 'value' => 'att_saida')); 
                                                            echo form_button(array('type' => 'submit', 'class' => 'btn btn-danger btn-flat', 'name' => 'acao', 'value' => 'remover_saida', 'content' => lang('actions_remove'))); 
                                                        }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php echo form_close();?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>