<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Formulário compartilhado entre criação ($modo = 'create') e edição ($modo = 'edit') de serviço do catálogo.
$editando = $modo === 'edit';
$erro = function ($campo) {
    return form_error($campo) !== '' ? ' has-error' : '';
};
$dados_arvore = array('modulos' => array(), 'categorias' => array());
foreach ($arvore['modulos'] as $modulo) {
    $dados_arvore['modulos'][] = array('id' => (int) $modulo->id, 'pai' => (int) $modulo->id_produto, 'nome' => $modulo->nome, 'ativo' => $modulo->status === 'S');
}
foreach ($arvore['categorias'] as $categoria) {
    $dados_arvore['categorias'][] = array('id' => (int) $categoria->id, 'pai' => (int) $categoria->id_modulo, 'nome' => $categoria->nome, 'ativo' => $categoria->status === 'S');
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content cadastro-form">
        <?php if (isset($message)) { echo $message; } ?>
        <?php echo form_open(current_url(), array('id' => 'form-servico')); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>Serviço</h3>
                        <span class="box-subtitle">Como o serviço aparece na abertura de chamados.</span>
                    </div>
                    <div class="box-body">
                        <div class="form-group<?php echo $erro('descricao') ?>">
                            <label for="descricao">Nome do serviço <span class="text-red">*</span></label>
                            <input type="text" id="descricao" name="descricao" class="form-control" maxlength="255" placeholder="Ex.: Erro na emissão de NF-e" value="<?php echo html_escape($valores['descricao']) ?>">
                        </div>
                        <div class="row">
                            <div class="col-sm-4 form-group">
                                <label for="produto">Produto <span class="text-red">*</span></label>
                                <select id="produto" name="produto" class="form-control">
                                    <option value="">Selecione</option>
                                    <?php foreach ($arvore['produtos'] as $produto) { ?>
                                        <option value="<?php echo (int) $produto->id ?>" <?php echo $valores['produto'] === (int) $produto->id ? 'selected' : '' ?>><?php echo html_escape($produto->nome) . ($produto->status === 'S' ? '' : ' (inativo)') ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-4 form-group">
                                <label for="modulo">Módulo <span class="text-red">*</span></label>
                                <select id="modulo" name="modulo" class="form-control"></select>
                            </div>
                            <div class="col-sm-4 form-group<?php echo $erro('categoria') ?>">
                                <label for="categoria">Categoria <span class="text-red">*</span></label>
                                <select id="categoria" name="categoria" class="form-control"></select>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Status <span class="text-red">*</span></label>
                            <div>
                                <div class="btn-group cadastro-segmentos" data-toggle="buttons">
                                    <label class="btn btn-default <?php echo $valores['status'] === '1' ? 'active' : '' ?>"><input type="radio" name="status" value="1" autocomplete="off" <?php echo $valores['status'] === '1' ? 'checked' : '' ?>><i class="fa fa-check text-green"></i> Ativo</label>
                                    <label class="btn btn-default <?php echo $valores['status'] === '0' ? 'active' : '' ?>"><input type="radio" name="status" value="0" autocomplete="off" <?php echo $valores['status'] === '0' ? 'checked' : '' ?>><i class="fa fa-ban text-muted"></i> Inativo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">2</span>Atendimento</h3>
                        <span class="box-subtitle">Preenchidos automaticamente no chamado quando este serviço é escolhido.</span>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-4 form-group<?php echo $erro('nivel') ?>">
                                <label for="nivel">Nível <span class="text-red">*</span></label>
                                <select id="nivel" name="nivel" class="form-control">
                                    <option value="">Selecione</option>
                                    <?php foreach ($niveis as $nivel) { ?>
                                        <option value="<?php echo (int) $nivel->id ?>" data-cor="<?php echo html_escape($nivel->cor) ?>" <?php echo $valores['nivel'] === (int) $nivel->id ? 'selected' : '' ?>><?php echo html_escape($nivel->descricao) . ($nivel->actived == 1 ? '' : ' (inativo)') ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-4 form-group<?php echo $erro('tempo_padrao') ?>">
                                <label for="tempo_padrao">Tempo estimado <span class="text-red">*</span></label>
                                <div class="input-group">
                                    <input type="text" id="tempo_padrao" name="tempo_padrao" class="form-control" inputmode="decimal" maxlength="8" placeholder="Ex.: 1,5" value="<?php echo html_escape($valores['tempo_padrao']) ?>">
                                    <span class="input-group-addon">h</span>
                                </div>
                                <p class="help-block">Vira as horas previstas do chamado.</p>
                            </div>
                            <div class="col-sm-4 form-group<?php echo $erro('prazo_padrao') ?>">
                                <label for="prazo_padrao">Prazo de entrega <span class="text-red">*</span></label>
                                <div class="input-group">
                                    <input type="number" id="prazo_padrao" name="prazo_padrao" class="form-control" min="0" step="1" value="<?php echo html_escape($valores['prazo_padrao']) ?>">
                                    <span class="input-group-addon">h úteis</span>
                                </div>
                                <p class="help-block">Contado no expediente, a partir da abertura.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">3</span>Orientações <small class="text-muted">(opcional)</small></h3>
                    </div>
                    <div class="box-body">
                        <textarea id="texto" name="texto" class="form-control" rows="4" placeholder="O que verificar ou pedir ao cliente neste tipo de chamado."><?php echo html_escape($valores['texto']) ?></textarea>
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
                                <dt>Serviço</dt><dd id="resumo-nome"></dd>
                                <dt>Classificação</dt><dd id="resumo-caminho"></dd>
                                <dt>Nível</dt><dd id="resumo-nivel"></dd>
                                <dt>Tempo / prazo</dt><dd id="resumo-tempo"></dd>
                                <?php if ($editando) { ?><dt>Chamados nos últimos 90 dias</dt><dd><?php echo (int) $uso_90 ?></dd><?php } ?>
                            </dl>
                            <?php if ($editando && $uso_90 > 0) { ?>
                                <p class="cadastro-dica"><i class="fa fa-info-circle"></i> As mudanças valem para os próximos chamados; os já abertos mantêm prazo e horas.</p>
                            <?php } ?>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-check"></i> <?php echo $editando ? 'Salvar alterações' : 'Criar serviço' ?></button>
                            <?php echo anchor('catalogo_servicos', lang('actions_cancel'), array('class' => 'btn btn-default btn-block')); ?>
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
        var arvore = <?php echo json_encode($dados_arvore) ?>;
        var selecionados = {modulo: <?php echo (int) $valores['modulo'] ?>, categoria: <?php echo (int) $valores['categoria'] ?>};

        function preencher(select, itens, pai, atual, vazio) {
            select.empty().append($('<option value=""></option>').text(pai ? 'Selecione' : vazio));
            var filhos = $.grep(itens, function(item) { return item.pai === pai; });
            $.each(filhos, function(i, item) {
                select.append($('<option></option>').val(item.id).text(item.nome + (item.ativo ? '' : ' (inativo)')).prop('selected', item.id === atual));
            });
            select.prop('disabled', !pai || !filhos.length);
            if (pai && !filhos.length) { select.find('option').first().text('Nenhum cadastrado'); }
        }

        function cascata() {
            var produto = parseInt($('#produto').val(), 10) || 0;
            preencher($('#modulo'), arvore.modulos, produto, selecionados.modulo, 'Escolha o produto');
            var modulo = parseInt($('#modulo').val(), 10) || 0;
            preencher($('#categoria'), arvore.categorias, modulo, selecionados.categoria, 'Escolha o módulo');
            resumo();
        }

        function resumo() {
            function texto(id, valor) { $(id).text(valor || 'Não informado').toggleClass('is-empty', !valor); }
            texto('#resumo-nome', $.trim($('#descricao').val()));
            var caminho = $.map(['#produto', '#modulo', '#categoria'], function(id) { return $(id).val() ? $(id).find('option:selected').text() : null; });
            texto('#resumo-caminho', caminho.join(' › '));
            texto('#resumo-nivel', $('#nivel').val() ? $('#nivel option:selected').text() : '');
            var tempo = $.trim($('#tempo_padrao').val()), prazo = $.trim($('#prazo_padrao').val());
            texto('#resumo-tempo', tempo || prazo ? (tempo ? tempo + ' h' : '—') + ' / ' + (prazo ? prazo + ' h úteis' : '—') : '');
        }

        $('#produto').on('change', function() { selecionados.modulo = 0; selecionados.categoria = 0; cascata(); });
        $('#modulo').on('change', function() {
            selecionados.modulo = parseInt($(this).val(), 10) || 0;
            selecionados.categoria = 0;
            preencher($('#categoria'), arvore.categorias, selecionados.modulo, 0, 'Escolha o módulo');
            resumo();
        });
        $('#categoria, #nivel').on('change', resumo);
        $('#descricao, #tempo_padrao, #prazo_padrao').on('input', resumo);
        cascata();
    });
</script>
<?php $this->load->view('admin/_partials/cadastro_estilos'); ?>
