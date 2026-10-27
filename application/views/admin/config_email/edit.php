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
                        <h3 class="box-title"><?php echo lang('config_email_edit'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-edit_config_email')); ?>
                            <div class="form-group">
                                <?php echo lang('tipo_email', 'tipo_email', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($tipo_email);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('assunto', 'assunto', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($assunto);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('mensagem', 'mensagem', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($mensagem);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo anchor('config_email', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
        // Sem o filtro de conteúdo, o CKEditor removeria os estilos inline dos modelos de e-mail.
        CKEDITOR.replace('mensagem', {allowedContent: true})
    };
</script>