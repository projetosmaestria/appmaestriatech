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
                        <h3 class="box-title"><?php echo lang('unidades_recursos_ws'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-recursos_ws_unidade')); ?>
                            <div class="form-group">
                                <?php echo lang('token', 'token', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-5">
                                    <?php echo form_input($token);?>
                                    <?php echo form_button($btn_token);?>                                    
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('recursos_ws', 'recursos_ws', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo $recursos_ws ?>
                                </div>
                            </div>
                            <div style="display:none;" id="tabelas_cliente_ws">
                                <?php foreach ($inputs as $input) { ?>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo $input['id']; ?></label>
                                        <div class="col-sm-10">
                                            <?php echo form_dropdown($input);?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('unidades', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
<script type="text/javascript">
    window.onload = function() {
        $('.multipleSelect').fastselect();

        if($('#0').is(':checked')) {
            $("#tabelas_cliente_ws").show();
        }
            
        $('#0').click(function() {
            if (!$(this).is(':checked')) {
                $("#tabelas_cliente_ws").hide();
            } else {
                $("#tabelas_cliente_ws").show();
            }
        });

        $('#0-2').click(function() {
            if (!$(this).is(':checked')) {
                $("#tabelas_cliente_ws").hide();
            } else {
                $("#tabelas_cliente_ws").show();
            }
        });
    };
</script>