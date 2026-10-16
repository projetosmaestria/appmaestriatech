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
                        <h3 class="box-title"><?php echo lang('app_unidades_livro_create'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_app_unidades_livro')); ?>
                            <div class="form-group">
                                <?php echo lang('ativo', 'ativo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="ativo_s"><input type="radio" id="ativo_s" name="status" value="S" checked="checked"><?php echo lang('ativo_s') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="ativo_n"><input type="radio" id="ativo_n" name="status" value="N"><?php echo lang('ativo_n') ?></label>
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
                                <div class="col-sm-1">
                                    <?php echo form_input($unidade);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('titulo', 'titulo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($titulo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('tipo', 'tipo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="tipo_o"><input type="radio" id="tipo_o" name="tipo" value="O" checked="checked"><?php echo lang('tipo_o') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="tipo_c"><input type="radio" id="tipo_c" name="tipo" value="C"><?php echo lang('tipo_c') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('grammar_file', 'grammar_file', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($grammar_file);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('texto_grammar_file', 'texto_grammar_file', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($texto_grammar_file);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('tera_audio', 'tera_audio', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="tera_audio_s"><input type="radio" id="tera_audio_s" name="tera_audio" value="S"><?php echo lang('tera_audio_s') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="tera_audio_n"><input type="radio" id="tera_audio_n" name="tera_audio" value="N" checked="checked"><?php echo lang('tera_audio_n') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('audio', 'audio', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <label class='btn btn-primary disabled' for='audio'>
                                        <input id='audio' type='file' name='audio' accept=".mp3" style='display:none' onchange="jQuery('#upload-file-info').html(this.files[0].name)"> Selecionar Áudio... 
                                    </label>
                                    <span class='label label-info' id='upload-file-info'></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('texto_audio', 'texto_audio', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($texto_audio);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('tera_video', 'tera_video', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="tera_video_s"><input type="radio" id="tera_video_s" name="tera_video" value="S"><?php echo lang('tera_video_s') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="tera_video_n"><input type="radio" id="tera_video_n" name="tera_video" value="N" checked="checked"><?php echo lang('tera_video_n') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('video', 'video', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <label class='btn btn-primary disabled' for='video'>
                                        <input id='video' type='file' name='video' accept=".mp4" style='display:none' onchange="jQuery('#upload-file-info2').html(this.files[0].name)"> Selecionar Vídeo... 
                                    </label>
                                    <span class='label label-info' id='upload-file-info2'></span>
                                </div>
                            </div>
                            <div class="form-group video_yt">
                                <?php echo lang('video_yt', 'video_yt', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-8">
                                    <input type="text" id="video_yt" name="video_yt[]" class="form-control" placeholder="Informe o Vídeo do Youtube" maxlength="500">
                                </div>
                                <div class="col-sm-2">
                                    <a href="#" class="btn btn-success adiciona-video_yt">Adicionar</a>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo anchor('app_unidades_livro', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
        CKEDITOR.replace('texto_grammar_file');

        $('#tera_audio_s').click(function (e) {
            $("label[for='audio']").removeClass('disabled');
            $("#texto_audio").attr('disabled', false);
        });
        $('#tera_audio_n').click(function (e) {
            $("label[for='audio']").addClass('disabled');
            $("#texto_audio").attr('disabled', true);
        });


        $('#tera_video_s').click(function (e) {
            $("label[for='video']").removeClass('disabled');
        });
        $('#tera_video_n').click(function (e) {
            $("label[for='video']").addClass('disabled');
        });
    };
</script>