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
                        <h3 class="box-title"><?php echo lang('app_livros_create'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_app_livros')); ?>
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
                                <?php echo lang('curso', 'curso', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($curso);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('disciplina', 'disciplina', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($disciplina);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('titulo', 'titulo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($titulo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('autor', 'autor', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($autor);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('editora', 'editora', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($editora);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('nr_edicao', 'nr_edicao', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($nr_edicao);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('ano_publicacao', 'ano_publicacao', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($ano_publicacao);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('cor', 'cor', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-3">
                                    <?php echo form_input($group_bgcolor); ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo anchor('app_livros', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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