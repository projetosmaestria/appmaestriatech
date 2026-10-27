<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Formulário compartilhado entre criação ($modo = 'create') e edição ($modo = 'edit') de filial (unidade).
$editando = $modo === 'edit';
$pj = $valores['tipo'] === 'J';
$erro = function ($campo) {
    return form_error($campo) !== '' ? ' has-error' : '';
};
$campo = function ($nome, $rotulo, $obrigatorio, $atributos = '') use ($valores, $erro) {
    return '<label for="' . $nome . '">' . $rotulo . ($obrigatorio ? ' <span class="text-red">*</span>' : '') . '</label>'
        . '<input type="text" id="' . $nome . '" name="' . $nome . '" class="form-control" value="' . html_escape($valores[$nome]) . '" ' . $atributos . '>';
};
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content cadastro-form">
        <?php if (isset($message)) { echo $message; } ?>
        <?php if ($editando) { ?>
            <div class="cadastro-heading">
                <span class="cadastro-heading-id">#<?php echo (int) $unidade->id ?></span>
                <span class="cadastro-heading-nome"><?php echo html_escape($unidade->unidade) ?></span>
                <?php echo $unidade->actived === 's' ? '<span class="label label-success">Ativa</span>' : '<span class="label label-default">Inativa</span>' ?>
            </div>
        <?php } ?>
        <?php echo form_open(current_url(), array('id' => 'form-unidade')); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>Identificação</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>Tipo <span class="text-red">*</span></label>
                                <div>
                                    <div class="btn-group cadastro-segmentos" data-toggle="buttons">
                                        <label class="btn btn-default <?php echo $pj ? 'active' : '' ?>"><input type="radio" name="tipo" value="J" autocomplete="off" <?php echo $pj ? 'checked' : '' ?>><i class="fa fa-building"></i> Pessoa jurídica</label>
                                        <label class="btn btn-default <?php echo !$pj ? 'active' : '' ?>"><input type="radio" name="tipo" value="F" autocomplete="off" <?php echo !$pj ? 'checked' : '' ?>><i class="fa fa-user"></i> Pessoa física</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label>Status <span class="text-red">*</span></label>
                                <div>
                                    <div class="btn-group cadastro-segmentos" data-toggle="buttons">
                                        <label class="btn btn-default <?php echo $valores['status'] === 's' ? 'active' : '' ?>"><input type="radio" name="status" value="s" autocomplete="off" <?php echo $valores['status'] === 's' ? 'checked' : '' ?>><i class="fa fa-check text-green"></i> Ativa</label>
                                        <label class="btn btn-default <?php echo $valores['status'] === 'n' ? 'active' : '' ?>"><input type="radio" name="status" value="n" autocomplete="off" <?php echo $valores['status'] === 'n' ? 'checked' : '' ?>><i class="fa fa-ban text-muted"></i> Inativa</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row js-pj" <?php echo $pj ? '' : 'style="display:none;"' ?>>
                            <div class="col-sm-4 form-group<?php echo $erro('cnpj') ?>"><?php echo $campo('cnpj', 'CNPJ', true, 'maxlength="18" inputmode="numeric" placeholder="00.000.000/0000-00"') ?></div>
                            <div class="col-sm-8 form-group<?php echo $erro('razao_social') ?>"><?php echo $campo('razao_social', 'Razão social', true, 'maxlength="80"') ?></div>
                            <div class="col-sm-8 form-group<?php echo $erro('fantasia') ?>"><?php echo $campo('fantasia', 'Nome fantasia', true, 'maxlength="150"') ?><p class="help-block">É o nome que aparece nas telas e nos chamados.</p></div>
                        </div>
                        <div class="row js-pf" <?php echo $pj ? 'style="display:none;"' : '' ?>>
                            <div class="col-sm-8 form-group<?php echo $erro('nome') ?>"><?php echo $campo('nome', 'Nome', true, 'maxlength="150"') ?></div>
                            <div class="col-sm-4 form-group<?php echo $erro('cpf') ?>"><?php echo $campo('cpf', 'CPF', true, 'maxlength="14" inputmode="numeric" placeholder="000.000.000-00"') ?></div>
                            <div class="col-sm-4 form-group<?php echo $erro('rg') ?>"><?php echo $campo('rg', 'RG', true, 'maxlength="20"') ?></div>
                        </div>
                        <?php if ($caracteristicas) { ?>
                            <div class="row">
                                <div class="col-sm-6 form-group" style="margin-bottom:0;">
                                    <label for="caracteristica">Característica</label>
                                    <select id="caracteristica" name="caracteristica" class="form-control">
                                        <option value="">Nenhuma</option>
                                        <?php foreach ($caracteristicas as $caracteristica) { ?>
                                            <option value="<?php echo (int) $caracteristica->id ?>" <?php echo (string) $valores['caracteristica'] === (string) $caracteristica->id ? 'selected' : '' ?>><?php echo html_escape($caracteristica->descricao) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">2</span>Contato</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6 form-group"><?php echo $campo('gestor', 'Gestor', false, 'maxlength="60"') ?></div>
                            <div class="col-sm-6 form-group"><?php echo $campo('responsavel', 'Responsável pelo encaminhamento', false, 'maxlength="80"') ?></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3 form-group<?php echo $erro('telefone1') ?>"><?php echo $campo('telefone1', 'Telefone', true, 'maxlength="15" inputmode="tel"') ?></div>
                            <div class="col-sm-3 form-group"><?php echo $campo('telefone2', 'Outro telefone', false, 'maxlength="15" inputmode="tel"') ?></div>
                            <div class="col-sm-6 form-group<?php echo $erro('email') ?>"><?php echo $campo('email', 'E-mail', true, 'maxlength="150" inputmode="email"') ?></div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;"><?php echo $campo('site', 'Site', false, 'maxlength="150" placeholder="www.exemplo.com.br"') ?></div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">3</span>Endereço</h3>
                        <span class="box-subtitle">Informe o CEP para preencher o restante.</span>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-3 form-group<?php echo $erro('cep') ?>">
                                <?php echo $campo('cep', 'CEP', true, 'maxlength="9" inputmode="numeric" placeholder="00000-000"') ?>
                                <p class="help-block" id="cep-status"></p>
                            </div>
                            <div class="col-sm-7 form-group<?php echo $erro('logradouro') ?>"><?php echo $campo('logradouro', 'Logradouro', true) ?></div>
                            <div class="col-sm-2 form-group<?php echo $erro('numero') ?>"><?php echo $campo('numero', 'Número', true, 'maxlength="10"') ?></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 form-group<?php echo $erro('bairro') ?>"><?php echo $campo('bairro', 'Bairro', true) ?></div>
                            <div class="col-sm-5 form-group<?php echo $erro('cidade') ?>"><?php echo $campo('cidade', 'Cidade', true) ?></div>
                            <div class="col-sm-2 form-group<?php echo $erro('uf') ?>">
                                <label for="uf">UF <span class="text-red">*</span></label>
                                <select id="uf" name="uf" class="form-control">
                                    <option value=""></option>
                                    <?php foreach ($ufs as $sigla => $nome_uf) { ?>
                                        <option value="<?php echo html_escape($sigla) ?>" title="<?php echo html_escape($nome_uf) ?>" <?php echo $valores['uf'] === $sigla ? 'selected' : '' ?>><?php echo html_escape($sigla) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($editando) { ?>
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><span class="step-number">4</span>Dados do APP</h3>
                            <span class="box-subtitle">Endereço do node e token da API de cada produto habilitado. Só produtos ativos podem ser alterados.</span>
                        </div>
                        <div class="box-body table-responsive no-padding">
                            <?php if ($produtos_app) { ?>
                                <table class="table lista-tabela">
                                    <thead><tr><th>Produto</th><th>URL do node</th><th>Token da API</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($produtos_app as $produto) { $bloqueado = $produto->status !== 'S'; ?>
                                            <tr class="<?php echo $bloqueado ? 'is-inativo' : 'is-ativo' ?>">
                                                <td>
                                                    <div class="lista-nome"><?php echo html_escape($produto->nome) ?></div>
                                                    <div class="lista-detalhe"><?php echo $bloqueado ? 'Desabilitado' : 'Até ' . date('d/m/Y', strtotime($produto->periodo_fim)) ?></div>
                                                </td>
                                                <td><input type="text" name="produtos_app[<?php echo (int) $produto->id_produto ?>][url_node]" value="<?php echo html_escape($produto->url_node) ?>" class="form-control input-sm" <?php echo $bloqueado ? 'disabled' : '' ?>></td>
                                                <td><input type="text" name="produtos_app[<?php echo (int) $produto->id_produto ?>][token_api]" value="<?php echo html_escape($produto->token_api) ?>" class="form-control input-sm" autocomplete="off" <?php echo $bloqueado ? 'disabled' : '' ?>></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } else { ?>
                                <p class="text-muted" style="padding: 10px 15px; margin: 0;">Nenhum produto habilitado para esta filial.</p>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="col-md-4">
                <div class="sticky-column">
                    <div class="box box-default cadastro-resumo">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-list-alt"></i> Resumo</h3>
                        </div>
                        <div class="box-body">
                            <dl>
                                <dt>Filial</dt><dd id="resumo-nome"></dd>
                                <dt>Documento</dt><dd id="resumo-documento"></dd>
                                <dt>Cidade</dt><dd id="resumo-cidade"></dd>
                                <?php if ($editando && $numeros) { ?>
                                    <dt>Produtos vigentes</dt><dd><?php echo (int) $numeros->produtos ?></dd>
                                    <dt>Usuários ativos com acesso</dt><dd><?php echo (int) $numeros->usuarios ?></dd>
                                    <dt>Chamados nos últimos 90 dias</dt><dd><?php echo (int) $numeros->chamados_90 ?></dd>
                                <?php } ?>
                            </dl>
                            <?php if ($integracoes) { ?>
                                <div class="unidade-integracoes">
                                    <div class="text-muted small">Configurações da filial</div>
                                    <?php foreach ($integracoes as $metodo => $acao) { ?>
                                        <a href="<?php echo base_url('unidades/' . $metodo . '/' . (int) $unidade->id) ?>"><i class="fa <?php echo $acao[0] ?>"></i> <?php echo html_escape($acao[1]) ?></a>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-check"></i> <?php echo $editando ? 'Salvar alterações' : 'Criar filial' ?></button>
                            <?php echo anchor('unidades', lang('actions_cancel'), array('class' => 'btn btn-default btn-block')); ?>
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
        var form = $('#form-unidade');

        function texto(id, valor) { $(id).text(valor || 'Não informado').toggleClass('is-empty', !valor); }
        function pj() { return form.find('input[name="tipo"]:checked').val() !== 'F'; }

        function atualizar() {
            var juridica = pj();
            $('.js-pj').toggle(juridica);
            $('.js-pf').toggle(!juridica);
            texto('#resumo-nome', $.trim($(juridica ? '#fantasia' : '#nome').val()));
            texto('#resumo-documento', $.trim($(juridica ? '#cnpj' : '#cpf').val()));
            var cidade = $.trim($('#cidade').val());
            texto('#resumo-cidade', cidade ? cidade + ($('#uf').val() ? ' / ' + $('#uf').val() : '') : '');
        }

        form.on('change', 'input[name="tipo"]', atualizar);
        form.on('input change', 'input, select', atualizar);

        // Busca o endereço pelo CEP (mesmo serviço usado antes: unidades/buscar_endereco).
        var ultimoCep = '';
        $('#cep').on('blur', function() {
            var cep = $(this).val().replace(/\D/g, '');
            if (cep.length !== 8 || cep === ultimoCep) { return; }
            ultimoCep = cep;
            $('#cep-status').text('Buscando endereço...');
            $.post('<?php echo base_url('unidades/buscar_endereco') ?>', {cep: cep})
                .done(function(resposta) {
                    var dados = null;
                    try { dados = typeof resposta === 'string' ? $.parseJSON(resposta) : resposta; } catch (e) { dados = null; }
                    var item = dados && dados[0] ? dados[0] : null;
                    if (!item) { $('#cep-status').text('CEP não encontrado; preencha o endereço.'); return; }
                    if (item.logradouro_nome) { $('#logradouro').val(item.logradouro_nome); }
                    if (item.bairro_nome) { $('#bairro').val(item.bairro_nome); }
                    if (item.cidade_nome) { $('#cidade').val(item.cidade_nome); }
                    if (item.uf) { $('#uf').val(item.uf); }
                    $('#cep-status').text('');
                    atualizar();
                    $('#numero').focus();
                })
                .fail(function() { $('#cep-status').text('Não foi possível buscar o CEP; preencha o endereço.'); });
        });

        atualizar();
    });
</script>
<?php $this->load->view('admin/_partials/cadastro_estilos'); ?>
<?php $this->load->view('admin/_partials/lista_estilos'); ?>
<style type="text/css">
    .unidade-integracoes { border-top: 1px solid #f0f0f0; padding-top: 10px; }
    .unidade-integracoes a { display: block; padding: 3px 0; }
    .unidade-integracoes .fa { width: 18px; color: #777; }
</style>
