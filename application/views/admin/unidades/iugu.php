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
                <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-iugu')); ?>
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo lang('unidades_iugu'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="status_ativo"><input type="radio" id="status_ativo" name="status" value="S" <?php echo ($status == "S")?'checked="checked"':'' ?>><?php echo lang('status_ativo') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="status_inativo"><input type="radio" id="status_inativo" name="status" value="N" <?php echo ($status == "N")?'checked="checked"':'' ?>><?php echo lang('status_inativo') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('token', 'token', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_input($token);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('acc_id', 'acc_id', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_input($acc_id);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('receb_boleto', 'receb_boleto', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($receb_boleto);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('receb_credito', 'receb_credito', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($receb_credito);?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="btn-group">
                            <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                            <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                            <?php echo anchor('unidades', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                        </div>
                    </div>
                <?php echo form_close();?>
             </div>
        </div>
    </section>
</div>


<script type="text/javascript">
    window.onload = function() {
        $('.multipleSelect').fastselect();
    };
</script>