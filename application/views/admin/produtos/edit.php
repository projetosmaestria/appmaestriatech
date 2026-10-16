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
                        <h3 class="box-title"><?php echo lang('produtos_edit'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-edit_produto')); ?>
                            <div class="form-group">
                                <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="status_ativo"><input type="radio" id="status_ativo" name="status" value="S"
                                        <?php
                                            echo ($status == "S" || $status == "")?' checked="checked"':'';
                                        ?>
                                        ><?php echo lang('status_ativo') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="status_inativo"><input type="radio" id="status_inativo" name="status" value="N"
                                        <?php
                                            echo ($status == "N")?' checked="checked"':'';
                                        ?>
                                        ><?php echo lang('status_inativo') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('nome', 'nome', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($nome);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('aplicacao', 'aplicacao', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($aplicacao);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('descricao', 'descricao', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($descricao);?>
                                </div>
                            </div> 
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('produtos', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            </div>
                        <?php echo form_close();?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>