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
                        <h3 class="box-title"><?php echo lang('cursos_edit'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-edit_cursos')); ?>
                            <div class="form-group">
                                <?php echo lang('ativo', 'ativo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="ativo_s"><input type="radio" id="ativo_s" name="ativo" value="S" <?php echo ($ativo == "S") ? "checked":"" ?>><?php echo lang('ativo_s') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="ativo_n"><input type="radio" id="ativo_n" name="ativo" value="N" <?php echo ($ativo == "N") ? "checked":"" ?>><?php echo lang('ativo_n') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('categoria', 'categoria', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($categoria);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('sigla', 'sigla', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($sigla);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('descricao', 'descricao', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($descricao);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('obs', 'obs', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($obs);?>
                                </div>
                            </div>      
                            <div class="form-group">
                                <?php echo lang('idade_minima', 'idade_minima', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($idade_minima);?>
                                </div>
                            </div>          
                            <div class="form-group">
                                <?php echo lang('aula_matricula', 'aula_matricula', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($aula_matricula);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('tipo_avaliacao', 'tipo_avaliacao', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($tipo_avaliacao);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('tipo', 'tipo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($tipo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('cursos', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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