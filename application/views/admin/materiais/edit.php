<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <!-- <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-info"></i> Informação!</h4>
            O tamanho do banner para upload é de 1242px de largura por 505px de altura.
        </div> -->
        <?php if(isset($message)) {echo $message;} ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('materiais_edit'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal', 'id' => 'form-edit_materiais')); ?>
                            <div class="form-group">
                                <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="status_ativo"><input type="radio" id="status_ativo" name="status" value="S" <?php echo ($status == "S") ? "checked":"" ?>><?php echo lang('status_ativo') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="status_inativo"><input type="radio" id="status_inativo" name="status" value="N" <?php echo ($status == "N") ? "checked":"" ?>><?php echo lang('status_inativo') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('titulo', 'titulo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($titulo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('grupos', 'grupos', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($grupos);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('livro', 'livro', array('class' => 'col-sm-2 control-label '), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($livro);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('unidade', 'unidade', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($unidade);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('arquivo', 'arquivo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <label class='btn btn-primary' for='arquivo'>
                                        <input id='arquivo' type='file' name='arquivo' style='display:none' onchange="jQuery('#upload-file-info').html(this.files[0].name)"> Selecionar Anexo... 
                                    </label>
                                    <span class='label label-info' id='upload-file-info'><?php echo $caminho ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('materiais', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
        $( "#livro" ).change(function() {
            $.ajax({
                url: '<?php echo base_url('app_exercicios/get_unidades_livro') ?>',
                type: 'POST',
                data: {livro:$(this).val()},
                success: function(data){
                    $('#unidade').html(data)
                }
            });
        });

        $( "#form-edit_app_unidades_livro" ).submit(function( event ) {
            updateAllMessageForms();
        });

        function updateAllMessageForms()
        {
            for (instance in CKEDITOR.instances) {
                    CKEDITOR.instances[instance].updateElement();
            }
        }
    };
</script>
