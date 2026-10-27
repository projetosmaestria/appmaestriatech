<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Formulário compartilhado entre criação ($modo = 'create') e edição ($modo = 'edit') de grupo.
$editando = $modo === 'edit';
$required_mark = ' <span class="text-red">*</span>';
$field_error_class = function ($field) {
    return form_error($field) !== '' ? ' has-error' : '';
};
$marcado = function ($controlador, $metodo) use ($marcadas) {
    return isset($marcadas[$controlador]) && is_array($marcadas[$controlador]) && in_array($metodo, $marcadas[$controlador]);
};
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content cadastro-form">
        <?php if (isset($message)) { echo $message; } ?>
        <?php echo form_open(current_url(), array('id' => 'form-grupo')); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>Identificação</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6 form-group<?php echo $field_error_class('group_name'); ?>">
                                <label for="group_name">Nome<?php echo $required_mark; ?></label>
                                <input type="text" id="group_name" name="group_name" class="form-control" maxlength="20" value="<?php echo html_escape($valores['group_name']) ?>" <?php echo $is_admin_group ? 'readonly' : '' ?>>
                                <?php if ($is_admin_group) { ?><p class="help-block">O nome do grupo de administradores não pode ser alterado.</p><?php } ?>
                            </div>
                            <div class="col-sm-4 form-group<?php echo $field_error_class('group_description'); ?>">
                                <label for="group_description">Descrição</label>
                                <input type="text" id="group_description" name="group_description" class="form-control" maxlength="100" value="<?php echo html_escape($valores['group_description']) ?>">
                            </div>
                            <div class="col-sm-2 form-group<?php echo $field_error_class('group_bgcolor'); ?>">
                                <label for="group_bgcolor">Cor</label>
                                <input type="color" id="group_bgcolor" name="group_bgcolor" class="form-control cadastro-cor" value="<?php echo html_escape($valores['group_bgcolor']) ?>">
                            </div>
                        </div>
                        <label class="cadastro-opcao">
                            <input type="checkbox" name="limited" value="1" <?php echo $valores['limited'] ? 'checked' : '' ?>>
                            <span><b>Informações limitadas ao usuário</b><br><small class="text-muted">Os membros veem apenas os registros ligados a eles.</small></span>
                        </label>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">2</span>Permissões</h3>
                        <span class="box-subtitle">O que os membros do grupo podem acessar e fazer em cada tela.</span>
                    </div>
                    <div class="box-body">
                        <div class="permissoes-toolbar">
                            <div class="input-group permissoes-busca">
                                <input type="search" id="permissoes-busca" class="form-control" placeholder="Buscar tela ou ação...">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            </div>
                            <label class="permissoes-so-marcadas"><input type="checkbox" id="permissoes-so-marcadas"> Só telas com permissão</label>
                            <a href="#" class="js-expandir" data-abrir="1">expandir tudo</a>
                            <a href="#" class="js-expandir" data-abrir="0">recolher tudo</a>
                        </div>
                        <?php if (empty($catalogo)) { ?>
                            <p class="text-muted">Nenhuma tela habilitada para esta empresa.</p>
                        <?php } ?>
                        <div class="permissoes-lista">
                            <?php foreach ($catalogo as $controlador => $item) {
                                $qtd = 0;
                                foreach ($item['metodos'] as $metodo) { if ($marcado($controlador, $metodo)) { $qtd++; } }
                            ?>
                                <div class="permissoes-tela js-tela" data-busca="<?php echo html_escape(mb_strtolower($item['nome'] . ' ' . $controlador, 'UTF-8')) ?>">
                                    <div class="permissoes-tela-topo js-alternar">
                                        <i class="fa fa-angle-right permissoes-seta"></i>
                                        <strong><?php echo html_escape($item['nome']) ?></strong>
                                        <small class="text-muted"><?php echo html_escape($controlador) ?></small>
                                        <span class="permissoes-contador js-contador"><?php echo $qtd ?> de <?php echo count($item['metodos']) ?></span>
                                        <span class="permissoes-atalhos">
                                            <a href="#" class="js-marcar" data-marcar="1">todas</a>
                                            <a href="#" class="js-marcar" data-marcar="0">nenhuma</a>
                                        </span>
                                    </div>
                                    <div class="permissoes-metodos" style="display:none;">
                                        <?php foreach ($item['metodos'] as $metodo) { ?>
                                            <label class="permissoes-metodo" data-busca="<?php echo html_escape(mb_strtolower($rotulos->rotulo($metodo) . ' ' . $metodo, 'UTF-8')) ?>">
                                                <input type="checkbox" name="permissoes[<?php echo html_escape($controlador) ?>][]" value="<?php echo html_escape($metodo) ?>" <?php echo $marcado($controlador, $metodo) ? 'checked' : '' ?>>
                                                <?php echo html_escape($rotulos->rotulo($metodo)) ?>
                                                <?php if ($rotulos->rotulo($metodo) !== ucfirst(str_replace('_', ' ', $metodo))) { ?><small class="text-muted"><?php echo html_escape($metodo) ?></small><?php } ?>
                                            </label>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
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
                                <dt>Grupo</dt><dd><span class="label" id="resumo-grupo" style="background: <?php echo html_escape($valores['group_bgcolor']) ?>;"><?php echo html_escape($valores['group_name'] !== '' ? $valores['group_name'] : 'Novo grupo') ?></span></dd>
                                <?php if ($editando) { ?>
                                    <dt>Usuários ativos</dt><dd><?php echo (int) $usuarios_ativos ?></dd>
                                <?php } ?>
                                <dt>Telas com acesso</dt><dd id="resumo-telas">0</dd>
                                <dt>Ações permitidas</dt><dd id="resumo-acoes">0</dd>
                            </dl>
                            <?php if ($editando && $usuarios_ativos > 0) { ?>
                                <p class="cadastro-dica"><i class="fa fa-info-circle"></i> As mudanças valem para os <?php echo (int) $usuarios_ativos ?> usuários do grupo a partir do próximo acesso a cada tela.</p>
                            <?php } ?>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-block" id="btn-salvar"><i class="fa fa-check"></i> <?php echo $editando ? 'Salvar alterações' : 'Criar grupo' ?></button>
                            <?php echo anchor('groups', lang('actions_cancel'), array('class' => 'btn btn-default btn-block')); ?>
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
        var form = $('#form-grupo');

        function atualizar() {
            var telas = 0, acoes = 0;
            $('.js-tela').each(function() {
                var marcados = $(this).find('.permissoes-metodo input:checked').length;
                var total = $(this).find('.permissoes-metodo input').length;
                $(this).find('.js-contador').text(marcados + ' de ' + total).toggleClass('is-ativo', marcados > 0);
                $(this).toggleClass('tem-permissao', marcados > 0);
                if (marcados) { telas++; acoes += marcados; }
            });
            $('#resumo-telas').text(telas);
            $('#resumo-acoes').text(acoes);
            $('#resumo-grupo').text($.trim($('#group_name').val()) || 'Novo grupo').css('background', $('#group_bgcolor').val());
            filtrar();
        }

        function filtrar() {
            var termo = $.trim($('#permissoes-busca').val()).toLowerCase();
            var soMarcadas = $('#permissoes-so-marcadas').is(':checked');
            $('.js-tela').each(function() {
                var tela = $(this);
                var nomeCombina = !termo || tela.data('busca').indexOf(termo) > -1;
                var algumMetodo = false;
                tela.find('.permissoes-metodo').each(function() {
                    var combina = nomeCombina || String($(this).data('busca')).indexOf(termo) > -1;
                    $(this).toggle(combina);
                    if (combina) { algumMetodo = true; }
                });
                var visivel = (nomeCombina || algumMetodo) && (!soMarcadas || tela.hasClass('tem-permissao'));
                tela.toggle(visivel);
                if (termo && visivel) { abrir(tela, true); }
            });
        }

        function abrir(tela, sim) {
            tela.find('.permissoes-metodos').toggle(sim);
            tela.find('.permissoes-seta').toggleClass('fa-angle-right', !sim).toggleClass('fa-angle-down', sim);
        }

        form.on('click', '.js-alternar', function(event) {
            if ($(event.target).closest('a').length) { return; }
            var tela = $(this).closest('.js-tela');
            abrir(tela, !tela.find('.permissoes-metodos').is(':visible'));
        });
        form.on('click', '.js-marcar', function(event) {
            event.preventDefault();
            var tela = $(this).closest('.js-tela');
            // Respeita a busca: só as ações que o filtro não escondeu.
            tela.find('.permissoes-metodo').filter(function() { return this.style.display !== 'none'; })
                .find('input').prop('checked', $(this).data('marcar') === 1);
            atualizar();
        });
        $('.js-expandir').on('click', function(event) {
            event.preventDefault();
            var sim = $(this).data('abrir') === 1;
            $('.js-tela:visible').each(function() { abrir($(this), sim); });
        });
        form.on('change', '.permissoes-metodo input', atualizar);
        $('#group_name, #group_bgcolor').on('input change', atualizar);
        $('#permissoes-busca').on('input', filtrar);
        $('#permissoes-so-marcadas').on('change', filtrar);
        form.on('submit', function() {
            $('#btn-salvar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');
        });
        atualizar();
        // Abre as telas que já têm permissão, para ver o que o grupo pode fazer.
        $('.js-tela.tem-permissao').each(function() { abrir($(this), true); });
    });
</script>
<?php $this->load->view('admin/_partials/cadastro_estilos'); ?>
<style type="text/css">
    .cadastro-cor { padding: 2px 4px; height: 34px; }
    .permissoes-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 14px; margin-bottom: 10px; }
    .permissoes-busca { flex: 1 1 240px; max-width: 360px; }
    .permissoes-so-marcadas { font-weight: 400 !important; margin: 0; }
    .permissoes-toolbar a { font-size: 12px; }
    .permissoes-tela { border: 1px solid #e3e8ee; border-left: 3px solid #d2d6de; border-radius: 4px; margin-bottom: 6px; }
    .permissoes-tela.tem-permissao { border-left-color: #3c8dbc; }
    .permissoes-tela-topo { display: flex; align-items: center; gap: 8px; padding: 8px 10px; cursor: pointer; user-select: none; }
    .permissoes-tela-topo:hover { background: #f7f9fb; }
    .permissoes-seta { width: 10px; color: #999; }
    .permissoes-contador { margin-left: auto; font-size: 12px; color: #999; }
    .permissoes-contador.is-ativo { color: #3c8dbc; font-weight: 600; }
    .permissoes-atalhos a { margin-left: 8px; font-size: 12px; }
    .permissoes-metodos { display: flex; flex-wrap: wrap; gap: 4px 6px; padding: 4px 10px 10px 28px; }
    .permissoes-metodo { display: inline-flex; align-items: center; gap: 5px; margin: 0; padding: 4px 9px; border: 1px solid #e3e8ee; border-radius: 14px; font-weight: 400 !important; cursor: pointer; }
    .permissoes-metodo input { margin: 0; }
    .permissoes-metodo small { font-size: 10px; }
</style>
