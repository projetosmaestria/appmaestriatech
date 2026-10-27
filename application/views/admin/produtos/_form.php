<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Formulário compartilhado entre criação ($modo = 'create') e edição ($modo = 'edit') de produto.
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
        <?php echo form_open(current_url(), array('id' => 'form-produto')); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>Identificação</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-7 form-group<?php echo $erro('nome') ?>">
                                <label for="nome">Nome <span class="text-red">*</span></label>
                                <input type="text" id="nome" name="nome" class="form-control" maxlength="255" value="<?php echo html_escape($valores['nome']) ?>">
                            </div>
                            <div class="col-sm-5 form-group<?php echo $erro('aplicacao') ?>">
                                <label for="aplicacao">Aplicação <span class="text-red">*</span></label>
                                <input type="text" id="aplicacao" name="aplicacao" class="form-control" maxlength="100" list="aplicacoes-usadas" placeholder="Ex.: Delphi, PHP/MySQL, Aplicativo" value="<?php echo html_escape($valores['aplicacao']) ?>">
                                <datalist id="aplicacoes-usadas">
                                    <?php foreach ($aplicacoes as $aplicacao) { ?><option value="<?php echo html_escape($aplicacao) ?>"></option><?php } ?>
                                </datalist>
                                <p class="help-block">Plataforma ou tecnologia do produto.</p>
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
                            <p class="help-block">Produto inativo não aparece para novas habilitações nem na abertura de chamados.</p>
                        </div>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">2</span>Descrição <small class="text-muted">(opcional)</small></h3>
                    </div>
                    <div class="box-body">
                        <textarea id="descricao" name="descricao" class="form-control" rows="5" placeholder="O que o produto faz e para quem."><?php echo html_escape($valores['descricao']) ?></textarea>
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
                                <dt>Produto</dt><dd id="resumo-nome"></dd>
                                <dt>Aplicação</dt><dd id="resumo-aplicacao"></dd>
                                <?php if ($editando && $uso) { ?>
                                    <dt>Versão publicada</dt><dd><?php echo $uso->versao ? html_escape($uso->versao) : '<span class="text-muted">—</span>' ?></dd>
                                    <dt>Módulos ativos</dt><dd><?php echo (int) $uso->modulos ?><?php if ($can_modulos) { ?> · <a href="<?php echo base_url('produtos_modulos?produto=' . (int) $produto->id) ?>">ver módulos</a><?php } ?></dd>
                                    <dt>Clientes com habilitação vigente</dt><dd><?php echo (int) $uso->clientes ?></dd>
                                <?php } ?>
                            </dl>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-check"></i> <?php echo $editando ? 'Salvar alterações' : 'Criar produto' ?></button>
                            <?php echo anchor('produtos', lang('actions_cancel'), array('class' => 'btn btn-default btn-block')); ?>
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
        function espelhar(campo, destino) {
            $(campo).on('input', function() {
                var valor = $.trim($(this).val());
                $(destino).text(valor || 'Não informado').toggleClass('is-empty', !valor);
            }).trigger('input');
        }
        espelhar('#nome', '#resumo-nome');
        espelhar('#aplicacao', '#resumo-aplicacao');
    });
</script>
<?php $this->load->view('admin/_partials/cadastro_estilos'); ?>
