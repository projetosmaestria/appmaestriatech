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
                        <h3 class="box-title"><?php echo lang('app_podcasts_edit'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal', 'id' => 'form-edit_app_podcasts')); ?>
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
                                <?php echo lang('tipo', 'tipo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="tipo_podcast"><input type="radio" id="tipo_podcast" name="tipo" value="P" checked="checked"><?php echo lang('tipo_podcast') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="tipo_videoaula"><input type="radio" id="tipo_videoaula" name="tipo" value="V"><?php echo lang('tipo_videoaula') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('publicacao', 'publicacao', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="publicacao_alunos"><input type="radio" id="publicacao_alunos" name="publicacao" value="A" checked="checked"><?php echo lang('publicacao_alunos') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="publicacao_todos"><input type="radio" id="publicacao_todos" name="publicacao" value="T"><?php echo lang('publicacao_todos') ?></label>
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
                                <?php echo lang('titulo', 'titulo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($titulo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('arquivo', 'arquivo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <label class='btn btn-primary' for='arquivo'>
                                        <input id='arquivo' type='file' name='arquivo' style='display:none' onchange="jQuery('#upload-file-info').html(this.files[0].name)"> Selecionar Arquivo... 
                                    </label>
                                    <span class='label label-info' id='upload-file-info'><?php echo $podcast->titulo_midia ?></span>
                                </div>
                            </div>
                            <?php if(file_exists(FCPATH.$podcast->caminho_midia)) { ?>
                                <div class="form-group">
                                    <div class="col-sm-2"></div>
                                    <div class="col-sm-10">
                                        <audio preload="auto" controls>
                                            <source src="<?php echo base_url($podcast->caminho_midia) ?>" type="audio/mp3">
                                        </audio>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="form-group">
                                <?php echo lang('texto', 'texto', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($texto);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('imagem', 'imagem', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <label class='btn btn-primary' for='imagem'>
                                        <input id='imagem' type='file' name='imagem' style='display:none' onchange="jQuery('#upload-file-info2').html(this.files[0].name)"> Selecionar Imagem... 
                                    </label>
                                    <span class='label label-info' id='upload-file-info2'><?php echo $podcast->titulo_imagem ?></span>
                                </div>
                            </div>
                            <?php if(file_exists(FCPATH.$podcast->caminho_img)) { ?>
                                <div class="form-group">
                                    <div class="col-sm-2"></div>
                                    <div class="col-sm-10">
                                        <img class="img-responsive" src="<?php echo base_url($podcast->caminho_img) ?>">
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('app_podcasts', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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