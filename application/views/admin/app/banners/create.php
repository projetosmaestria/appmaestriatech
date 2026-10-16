<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-info"></i> Informação!</h4>
            O tamanho do banner para upload é de 1242px de largura por 505px de altura.
        </div>
        <?php if(isset($message)) {echo $message;} ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('app_banners_create'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_app_banners')); ?>
                            <div class="form-group">
                                <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="status_ativo"><input type="radio" id="status_ativo" name="status" value="S" checked="checked"><?php echo lang('status_ativo') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="status_inativo"><input type="radio" id="status_inativo" name="status" value="N"><?php echo lang('status_inativo') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('data_publicacao', 'data_publicacao', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-2 input-group date" id='datetimepicker'>
                                    <?php echo form_input($data_publicacao);?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('titulo', 'titulo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($titulo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('link', 'link', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($link);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('periodo_indeterminado', 'periodo_indeterminado', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_checkbox($periodo_indeterminado);?>
                                </div>
                            </div>
                            <div id="div_periodo_de" class="form-group 
                            <?php 
                                if($periodo_indeterminado_check) {
                                    echo " no-display";
                                }
                            ?>
                            ">
                                <?php echo lang('de', 'de', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='datetimepicker_de'>
                                    <?php echo form_input($periodo_de);?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <div id="div_periodo_ate" class="form-group 
                            <?php 
                                if($periodo_indeterminado_check) {
                                    echo " no-display";
                                }
                            ?>
                            ">
                                <?php echo lang('ate', 'ate', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='datetimepicker_ate'>
                                    <?php echo form_input($periodo_ate);?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('imagem', 'imagem', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <label class='btn btn-primary' for='imagem'>
                                        <input id='imagem' type='file' name='imagem' style='display:none' onchange="jQuery('#upload-file-info').html(this.files[0].name)"> Selecionar Anexo... 
                                    </label>
                                    <span class='label label-info' id='upload-file-info'></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('app_banners', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
        jQuery('#datetimepicker_de').datetimepicker({
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        jQuery('#datetimepicker_ate').datetimepicker({
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });

        jQuery('#periodo_indeterminado').change(function() {
            if(jQuery(this).is(":checked")) {
                jQuery("#div_periodo_de").fadeOut();
                jQuery("#div_periodo_ate").fadeOut();
            } else {
                jQuery("#div_periodo_de").fadeIn();
                jQuery("#div_periodo_ate").fadeIn();
            }  
        });
    };
</script>