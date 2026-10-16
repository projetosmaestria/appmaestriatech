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
                        <h3 class="box-title"><?php echo lang('plano_acao_create'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_plano_acao')); ?>
                            <div class="form-group">
                                <?php echo lang('tipo', 'tipo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($tipo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('filial', 'filial', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($filial);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('inicio_estimado', 'inicio_estimado', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='datetimepicker_inicio_estimado'>
                                    <?php echo form_input($inicio_estimado);?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('termino_estimado', 'termino_estimado', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='datetimepicker_termino_estimado'>
                                    <?php echo form_input($termino_estimado);?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <div class='form-group'>
                                <?php echo lang('horas_prevista', 'horas_prevista', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input(array('id'=>'horas_prevista','name'=>'horas_prevista','value'=>'','class'=>'form-control required','maxlength'=>"5"));?>
                                    <em> (ex: 1,5h) </em>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('inicio_real', 'inicio_real', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-2 input-group date" id='datetimepicker_inicio_real'>
                                    <?php echo form_input($inicio_real);?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('termino_real', 'termino_real', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-2 input-group date" id='datetimepicker_termino_real'>
                                    <?php echo form_input($termino_real);?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('desc', 'desc', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($desc);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('txt', 'txt', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($txt);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('responsavel', 'responsavel', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($usuarios);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('plano_acao', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
<script>
    window.onload = function() {
        jQuery('#datetimepicker_inicio_real').datetimepicker({
            locale:'pt-br',
            format: 'DD/MM/YYYY H:mm',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        jQuery('#datetimepicker_termino_real').datetimepicker({
            locale:'pt-br',
            format: 'DD/MM/YYYY H:mm',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });

        jQuery('#datetimepicker_inicio_estimado').datetimepicker({
            locale:'pt-br',
            format: 'DD/MM/YYYY H:mm',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        jQuery('#datetimepicker_termino_estimado').datetimepicker({
            locale:'pt-br',
            format: 'DD/MM/YYYY H:mm',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
    };
</script>