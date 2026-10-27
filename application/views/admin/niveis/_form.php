<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Formulário compartilhado entre criação e edição de nível.
$field_error_class = function ($field) {
    return form_error($field) !== '' ? ' has-error' : '';
};
$required_mark = ' <span class="text-red">*</span>';
$ativo = (string) $status === '' ? true : (int) $status === 1;
$cor_atual = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $cor_valor) ? $cor_valor : '#3c8dbc';
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content nivel-form">
        <?php if (isset($message)) { echo $message; } ?>
        <?php echo form_open(current_url(), array('id' => $form_id)); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $titulo_formulario; ?></h3>
                        <span class="box-subtitle">O nível indica o esforço esperado do chamado e define as horas previstas no planejamento da equipe.</span>
                    </div>
                    <div class="box-body">
                        <div class="form-group<?php echo $field_error_class('descricao'); ?>">
                            <label for="descricao"><?php echo lang('descricao') . $required_mark; ?></label>
                            <?php echo form_input(array_merge($descricao, array('placeholder' => 'Ex.: Nível 2', 'autocomplete' => 'off', 'maxlength' => 100))); ?>
                            <p class="help-block" id="nivel-estimativa"></p>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group<?php echo $field_error_class('cor'); ?>">
                                <label for="cor_picker"><?php echo lang('cor'); ?></label>
                                <div class="input-group nivel-cor">
                                    <span class="input-group-addon"><input type="color" id="cor_picker" value="<?php echo html_escape($cor_atual) ?>" aria-label="Escolher cor"></span>
                                    <input type="text" name="cor" id="cor" class="form-control" value="<?php echo html_escape($cor_atual) ?>" maxlength="7" pattern="#[0-9a-fA-F]{6}">
                                </div>
                                <p class="help-block">Usada para identificar o nível nas listas.</p>
                            </div>
                            <div class="col-sm-6 form-group<?php echo $field_error_class('status'); ?>">
                                <label><?php echo lang('status') . $required_mark; ?></label>
                                <div>
                                    <div class="btn-group nivel-status" data-toggle="buttons">
                                        <label class="btn btn-default <?php echo $ativo ? 'active' : '' ?>">
                                            <input type="radio" name="status" value="1" autocomplete="off" <?php echo $ativo ? 'checked' : '' ?>> <i class="fa fa-check-circle text-green"></i> <?php echo lang('status_ativo') ?>
                                        </label>
                                        <label class="btn btn-default <?php echo $ativo ? '' : 'active' ?>">
                                            <input type="radio" name="status" value="0" autocomplete="off" <?php echo $ativo ? '' : 'checked' ?>> <i class="fa fa-ban text-muted"></i> <?php echo lang('status_inativo') ?>
                                        </label>
                                    </div>
                                </div>
                                <p class="help-block">Níveis inativos deixam de aparecer para novos chamados.</p>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary', 'content' => '<i class="fa fa-check"></i> ' . $texto_botao)); ?>
                        <?php echo anchor('niveis', lang('actions_cancel'), array('class' => 'btn btn-default')); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </section>
</div>
<script type="text/javascript">
    (function() {
        var picker = document.getElementById('cor_picker');
        var text = document.getElementById('cor');
        var description = document.getElementById('descricao');
        var estimate = document.getElementById('nivel-estimativa');
        var hours = {1: 2, 2: 4, 3: 8, 4: 16};

        picker.addEventListener('input', function() {
            text.value = picker.value;
        });
        text.addEventListener('input', function() {
            if (/^#[0-9a-fA-F]{6}$/.test(text.value)) {
                picker.value = text.value;
            }
        });

        // Mesma regra do planejamento: o número na descrição define as horas previstas.
        function updateEstimate() {
            var number = parseInt((description.value.match(/\d+/) || [''])[0], 10);
            estimate.textContent = hours[number]
                ? 'Estimativa no planejamento: ' + hours[number] + ' h por chamado.'
                : 'Sem número de 1 a 4 na descrição, o planejamento estima 0 h para chamados deste nível.';
        }
        description.addEventListener('input', updateEstimate);
        updateEstimate();
    })();
</script>
<style type="text/css">
    .nivel-form .box { border-top-width: 3px; }
    .nivel-form .box-header .box-title { font-size: 16px; }
    .nivel-form .box-header .box-subtitle { display: block; margin-top: 4px; color: #777; font-size: 12px; }
    .nivel-form .form-group label { font-weight: 600; }
    .nivel-form .help-block { margin-bottom: 0; font-size: 12px; }
    .nivel-form .nivel-cor .input-group-addon { padding: 2px 6px; }
    .nivel-form .nivel-cor input[type="color"] { width: 32px; height: 26px; padding: 0; border: 0; background: none; cursor: pointer; }
    .nivel-form .nivel-status .btn { min-width: 110px; }
    .nivel-form .nivel-status .btn.active { background: #eaf2f8; border-color: #3c8dbc; box-shadow: none; }
</style>
