<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Formulário compartilhado entre criação ($modo = 'create') e edição ($modo = 'edit') de módulo.
$editando = $modo === 'edit';
$erro = function ($campo) {
    return form_error($campo) !== '' ? ' has-error' : '';
};
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content cadastro-form">
        <?php if (isset($message)) { echo $message; } ?>
        <?php echo form_open(current_url(), array('id' => 'form-modulo')); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>Identificação</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6 form-group<?php echo $erro('produto') ?>">
                                <label for="produto">Produto <span class="text-red">*</span></label>
                                <select id="produto" name="produto" class="form-control">
                                    <option value="">Selecione</option>
                                    <?php foreach ($produtos as $produto) { ?>
                                        <option value="<?php echo (int) $produto->id ?>" <?php echo $valores['produto'] === (int) $produto->id ? 'selected' : '' ?>><?php echo html_escape($produto->nome) . ($produto->status === 'S' ? '' : ' (inativo)') ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-6 form-group<?php echo $erro('nome') ?>">
                                <label for="nome">Nome do módulo <span class="text-red">*</span></label>
                                <input type="text" id="nome" name="nome" class="form-control" maxlength="255" value="<?php echo html_escape($valores['nome']) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status <span class="text-red">*</span></label>
                            <div>
                                <div class="btn-group cadastro-segmentos" data-toggle="buttons">
                                    <label class="btn btn-default <?php echo $valores['status'] === 'S' ? 'active' : '' ?>"><input type="radio" name="status" value="S" autocomplete="off" <?php echo $valores['status'] === 'S' ? 'checked' : '' ?>><i class="fa fa-check text-green"></i> Ativo</label>
                                    <label class="btn btn-default <?php echo $valores['status'] === 'N' ? 'active' : '' ?>"><input type="radio" name="status" value="N" autocomplete="off" <?php echo $valores['status'] === 'N' ? 'checked' : '' ?>><i class="fa fa-ban text-muted"></i> Inativo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">2</span>Valores</h3>
                        <span class="box-subtitle">Usados como referência nas propostas e contratos.</span>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php foreach (array('valor_licenca' => 'Licença', 'valor_manutencao' => 'Manutenção (mensal)', 'valor_implantacao' => 'Implantação') as $campo => $rotulo) { ?>
                                <div class="col-sm-4 form-group<?php echo $erro($campo) ?>">
                                    <label for="<?php echo $campo ?>"><?php echo $rotulo ?> <span class="text-red">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon">R$</span>
                                        <input type="text" id="<?php echo $campo ?>" name="<?php echo $campo ?>" class="form-control text-right js-moeda" inputmode="decimal" value="<?php echo html_escape($valores[$campo]) ?>">
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">3</span>Descrição <small class="text-muted">(opcional)</small></h3>
                    </div>
                    <div class="box-body">
                        <textarea id="descricao" name="descricao" class="form-control" rows="4" placeholder="O que o módulo oferece."><?php echo html_escape($valores['descricao']) ?></textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sticky-column">
                    <div class="box box-default cadastro-resumo">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-list-alt"></i> Resumo</h3>
                        </div>
                        <div class="box-body">
                            <dl>
                                <dt>Módulo</dt><dd id="resumo-nome"></dd>
                                <dt>Produto</dt><dd id="resumo-produto"></dd>
                                <?php if ($editando) { ?><dt>Clientes com acesso</dt><dd><?php echo (int) $clientes ?></dd><?php } ?>
                            </dl>
                            <?php if ($editando && $clientes > 0) { ?>
                                <p class="cadastro-dica"><i class="fa fa-info-circle"></i> Alterar os valores não muda contratos já fechados.</p>
                            <?php } ?>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-check"></i> <?php echo $editando ? 'Salvar alterações' : 'Criar módulo' ?></button>
                            <?php echo anchor('produtos_modulos' . ($valores['produto'] ? '?produto=' . (int) $valores['produto'] : ''), lang('actions_cancel'), array('class' => 'btn btn-default btn-block')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </section>
</div>
<script type="text/javascript">
    window.addEventListener('load', function() {
        $('#nome').on('input', function() {
            var valor = $.trim($(this).val());
            $('#resumo-nome').text(valor || 'Não informado').toggleClass('is-empty', !valor);
        }).trigger('input');
        $('#produto').on('change', function() {
            $('#resumo-produto').text($(this).val() ? $(this).find('option:selected').text() : 'Não informado').toggleClass('is-empty', !$(this).val());
        }).trigger('change');
        if ($.fn.maskMoney) {
            $('.js-moeda').maskMoney({thousands: '.', decimal: ',', allowZero: true});
        }
    });
</script>
<?php $this->load->view('admin/_partials/cadastro_estilos'); ?>
