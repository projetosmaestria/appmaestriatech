<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Formulário compartilhado entre criação e edição de categoria de chamado.
$field_error_class = function ($field) {
    return form_error($field) !== '' ? ' has-error' : '';
};
$required_mark = ' <span class="text-red">*</span>';
$ativa = (string) $status === '' ? true : (int) $status === 1;
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content categoria-form">
        <?php if (isset($message)) { echo $message; } ?>
        <?php echo form_open(current_url(), array('id' => $form_id)); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $titulo_formulario; ?></h3>
                        <span class="box-subtitle">Agrupe os chamados por assunto e indique o setor que costuma atendê-los.</span>
                    </div>
                    <div class="box-body">
                        <div class="form-group<?php echo $field_error_class('descricao'); ?>">
                            <label for="descricao"><?php echo lang('chamados_desc') . $required_mark; ?></label>
                            <?php echo form_input(array_merge($descricao, array('placeholder' => 'Ex.: Fiscal, Financeiro, Integrações', 'autocomplete' => 'off', 'maxlength' => 150))); ?>
                        </div>
                        <div class="form-group<?php echo $field_error_class('setor'); ?>">
                            <label for="setor"><?php echo lang('chamados_setor') . $required_mark; ?></label>
                            <?php echo form_dropdown($setor); ?>
                            <p class="help-block">Lista os setores ativos da empresa.</p>
                        </div>
                        <div class="form-group<?php echo $field_error_class('status'); ?>">
                            <label><?php echo lang('chamados_status') . $required_mark; ?></label>
                            <div class="btn-group categoria-status" data-toggle="buttons">
                                <label class="btn btn-default <?php echo $ativa ? 'active' : '' ?>">
                                    <input type="radio" name="status" value="1" autocomplete="off" <?php echo $ativa ? 'checked' : '' ?>> <i class="fa fa-check-circle text-green"></i> <?php echo lang('actions_ativo') ?>
                                </label>
                                <label class="btn btn-default <?php echo $ativa ? '' : 'active' ?>">
                                    <input type="radio" name="status" value="0" autocomplete="off" <?php echo $ativa ? '' : 'checked' ?>> <i class="fa fa-ban text-muted"></i> <?php echo lang('actions_inativo') ?>
                                </label>
                            </div>
                            <p class="help-block">Categorias inativas deixam de aparecer para novos chamados.</p>
                        </div>
                    </div>
                    <div class="box-footer">
                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary', 'content' => '<i class="fa fa-check"></i> ' . $texto_botao)); ?>
                        <?php echo anchor('chamados_categoria', lang('actions_cancel'), array('class' => 'btn btn-default')); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </section>
</div>
<style type="text/css">
    .categoria-form .box { border-top-width: 3px; }
    .categoria-form .box-header .box-title { font-size: 16px; }
    .categoria-form .box-header .box-subtitle { display: block; margin-top: 4px; color: #777; font-size: 12px; }
    .categoria-form .form-group label { font-weight: 600; }
    .categoria-form .help-block { margin-bottom: 0; font-size: 12px; }
    .categoria-form .categoria-status .btn { min-width: 110px; }
    .categoria-form .categoria-status .btn.active { background: #eaf2f8; border-color: #3c8dbc; box-shadow: none; }
</style>
