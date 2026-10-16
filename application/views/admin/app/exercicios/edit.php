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
                        <h3 class="box-title"><?php echo lang('app_exercicios_edit'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal', 'id' => 'form-edit_app_unidades_livro')); ?>
                            <div class="form-group">
                                <?php echo lang('ativo', 'ativo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="ativo_s"><input type="radio" id="ativo_s" name="status" value="S" <?php echo ($status == "S") ? 'checked="checked"' : '' ?>><?php echo lang('ativo_s') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="ativo_n"><input type="radio" id="ativo_n" name="status" value="N" <?php echo ($status == "N") ? 'checked="checked"' : '' ?>><?php echo lang('ativo_n') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('livro', 'livro', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($livro);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('unidade', 'unidade', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($unidade);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('tipo', 'tipo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($tipo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('ordem', 'ordem', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($ordem);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('titulo', 'titulo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($titulo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('exemplo', 'exemplo', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($exemplo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('tera_audio', 'tera_audio', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="tera_audio_s"><input type="radio" id="tera_audio_s" name="tera_audio" value="S" <?php echo ($tera_audio == "S") ? 'checked="checked"' : '' ?>><?php echo lang('tera_audio_s') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="tera_audio_n"><input type="radio" id="tera_audio_n" name="tera_audio" value="N" <?php echo ($tera_audio == "N") ? 'checked="checked"' : '' ?>><?php echo lang('tera_audio_n') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('audio', 'audio', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <label class='btn btn-primary <?php echo ($tera_audio == "S") ? "" : "disabled" ?>' for='audio'>
                                        <input id='audio' type='file' name='audio' accept=".mp3" style='display:none' onchange="jQuery('#upload-file-info').html(this.files[0].name)"> Selecionar Áudio... 
                                    </label>
                                    <span class='label label-info' id='upload-file-info'><?php echo ($tera_audio == "S") ? $exerc_model->titulo_arquivo : '' ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('texto_audio', 'texto_audio', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($texto_audio);?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo anchor('app_exercicios', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
        CKEDITOR.replace('texto_audio');

        $('#tera_audio_s').click(function (e) {
            $("label[for='audio']").removeClass('disabled');
            CKEDITOR.instances['texto_audio'].setReadOnly(false);
        });
        $('#tera_audio_n').click(function (e) {
            $("label[for='audio']").addClass('disabled');
            CKEDITOR.instances['texto_audio'].setReadOnly(true);
        });

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