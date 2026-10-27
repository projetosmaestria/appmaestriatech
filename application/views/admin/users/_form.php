<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$field_error_class = function ($field) {
    return form_error($field) !== '' ? ' has-error' : '';
};
$required_mark = ' <span class="text-red">*</span>';
// Formulário compartilhado entre criação ($modo = 'create') e edição ($modo = 'edit') de usuário.
$editando = $modo === 'edit';
// Na criação, nome e senha só são pedidos quando o cadastro é completo; senão o usuário preenche na ativação.
$cadastro_completo = $editando || $prefs->cadastro_user_completo == 1;
// Aluno cadastrado por quem não tem permissão sobre usuários: o grupo de aluno vem marcado e travado.
$pode_gerenciar = $editando ? $has_users_edit : $has_users_create;
$grupos_travados = !$pode_gerenciar && @$tipo_user == 1;
$url_cancelar = (!$editando && @$tipo_user == 1) ? 'app_alunos' : 'users';
$multiplas_empresas = count($dados_empresa) > 1;
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content usuario-form">
        <?php if (isset($message)) { echo $message; } ?>
        <?php if ($editando) {
            $nome_usuario = trim($user->first_name . ' ' . $user->last_name);
        ?>
            <div class="usuario-edit-heading">
                <span class="usuario-edit-id">#<?php echo (int) $user->id ?></span>
                <span class="usuario-edit-nome"><?php echo html_escape($nome_usuario !== '' ? $nome_usuario : $user->email) ?></span>
                <?php if ($user->active == 1) { ?>
                    <span class="label label-success">Ativo</span>
                <?php } else { ?>
                    <span class="label label-default">Inativo</span>
                <?php } ?>
                <span class="usuario-edit-meta">
                    <?php if (!empty($user->created_on)) { ?>Cadastrado em <?php echo date('d/m/Y', (int) $user->created_on) ?><?php } ?>
                    <?php if (!empty($user->last_login)) { ?> · Último acesso em <?php echo date('d/m/Y H:i', (int) $user->last_login) ?><?php } else { ?> · Ainda não acessou<?php } ?>
                </span>
            </div>
        <?php } ?>
        <?php echo form_open(current_url(), array('id' => 'form-user')); ?>
        <?php if ($editando) { ?>
            <?php echo form_hidden('id', (int) $user->id); ?>
            <?php echo form_hidden($csrf); ?>
        <?php } ?>
        <div class="row">
            <div class="col-md-8">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>Acesso</h3>
                        <span class="box-subtitle">
                            <?php if ($editando) { ?>
                                Nome, login e senha do usuário.
                            <?php } elseif ($cadastro_completo) { ?>
                                Dados usados para entrar no sistema.
                            <?php } else { ?>
                                O usuário recebe um e-mail de ativação e preenche nome, sobrenome e senha no primeiro acesso.
                            <?php } ?>
                        </span>
                    </div>
                    <div class="box-body">
                        <?php if ($cadastro_completo) { ?>
                            <div class="row">
                                <div class="col-sm-6 form-group<?php echo $field_error_class('first_name'); ?>">
                                    <label for="first_name">Nome<?php echo $required_mark; ?></label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" maxlength="50" value="<?php echo html_escape($first_name['value']) ?>">
                                </div>
                                <div class="col-sm-6 form-group<?php echo $field_error_class('last_name'); ?>">
                                    <label for="last_name">Sobrenome<?php echo $required_mark; ?></label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" maxlength="50" value="<?php echo html_escape($last_name['value']) ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group<?php echo $field_error_class('email'); ?>">
                            <label for="email">E-mail<?php echo $required_mark; ?></label>
                            <input type="email" id="email" name="email" class="form-control usuario-email" maxlength="254" autocomplete="off" placeholder="nome@empresa.com.br" value="<?php echo html_escape($email['value']) ?>">
                            <?php if ($editando) { ?>
                                <p class="help-block">É o login do usuário. Ao trocar, ele passa a entrar com o novo e-mail.</p>
                            <?php } else { ?>
                                <p class="help-block">É o login do usuário. Se o e-mail já estiver cadastrado, peça a inclusão do grupo em Usuários &gt; Listar.</p>
                            <?php } ?>
                        </div>
                        <?php if ($cadastro_completo) { ?>
                            <div class="row">
                                <div class="col-sm-6 form-group<?php echo $field_error_class('password'); ?>">
                                    <label for="password"><?php echo $editando ? 'Nova senha' : 'Senha' ?> <small class="text-muted">(opcional)</small></label>
                                    <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
                                    <div class="progress" style="margin:4px 0 0;">
                                        <div class="pwstrength_viewport_progress"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6 form-group<?php echo $field_error_class('password_confirm'); ?>">
                                    <label for="password_confirm">Confirmar <?php echo $editando ? 'nova senha' : 'senha' ?></label>
                                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" autocomplete="new-password">
                                    <p class="help-block" id="senha-confere"></p>
                                </div>
                            </div>
                            <p class="help-block"><?php echo $editando ? 'Deixe em branco para manter a senha atual. ' : '' ?>Use letras maiúsculas, minúsculas e números.</p>
                        <?php } ?>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">2</span>Empresas e permissões</h3>
                        <span class="box-subtitle">Em quais empresas o usuário entra, com quais grupos e em quais unidades.</span>
                    </div>
                    <div class="box-body">
                        <?php if (empty($dados_empresa)) { ?>
                            <p class="text-muted">Você não está vinculado a nenhuma empresa.</p>
                        <?php } ?>
                        <div class="<?php echo $multiplas_empresas ? 'nav-tabs-custom' : '' ?>">
                            <?php if ($multiplas_empresas) { ?>
                                <ul class="nav nav-tabs">
                                    <?php foreach ($dados_empresa as $key => $emp) { ?>
                                        <li class="<?php echo $empresa_selecionada == $key ? 'active' : '' ?>">
                                            <a href="#tab_<?php echo (int) $key ?>" data-toggle="tab">
                                                <?php echo html_escape($emp['desc']) ?>
                                                <span class="label label-primary js-empresa-badge" data-empresa="<?php echo (int) $key ?>" style="display:none;"></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                            <div class="tab-content">
                                <?php foreach ($dados_empresa as $key => $emp) {
                                    $key = (int) $key;
                                    $ativo = (!$multiplas_empresas || $empresa_selecionada == $key) ? ' active' : '';
                                    $grupos_atuais = is_array($emp['currentGroups']) ? $emp['currentGroups'] : array();
                                    $setores_atuais = is_array($emp['currentSetores']) ? $emp['currentSetores'] : array();
                                    $unidades_atuais = is_array($emp['currentUnids']) ? $emp['currentUnids'] : array();
                                ?>
                                    <div class="tab-pane<?php echo $ativo ?> js-empresa" id="tab_<?php echo $key ?>" data-empresa="<?php echo $key ?>" data-nome="<?php echo html_escape($emp['desc']) ?>">
                                        <label class="usuario-vinculo">
                                            <input type="checkbox" class="js-vinculo" id="active-<?php echo $key ?>" name="active[<?php echo $key ?>]" value="1" <?php echo $emp['currentEmps'] == 1 ? 'checked' : '' ?>>
                                            <span>Vincular o usuário à empresa <b><?php echo html_escape($emp['desc']) ?></b></span>
                                        </label>

                                        <div class="row">
                                            <div class="col-sm-7 form-group">
                                                <label>Grupos</label>
                                                <div class="usuario-grupos">
                                                    <?php
                                                    $exibidos = 0;
                                                    foreach ($emp['grupos'] as $group) {
                                                        if (!$is_admin && $group['id'] == $emp['grupo_admin']) { continue; }
                                                        $exibidos++;
                                                        $marcado = in_array($group['id'], $grupos_atuais);
                                                        if ($grupos_travados && $marcado) {
                                                            echo '<input type="hidden" name="groups[]" value="' . (int) $group['id'] . '">';
                                                        }
                                                    ?>
                                                        <label class="usuario-grupo<?php echo $marcado ? ' is-checked' : '' ?>">
                                                            <input type="checkbox" class="js-grupo<?php echo $emp['grupo_aluno'] == $group['id'] ? ' grupo_aluno' : '' ?>" name="groups[]" value="<?php echo (int) $group['id'] ?>" <?php echo $marcado ? 'checked' : '' ?> <?php echo $grupos_travados ? 'disabled' : '' ?>>
                                                            <?php echo html_escape($group['name']) ?>
                                                        </label>
                                                    <?php } ?>
                                                    <?php if ($exibidos === 0) { ?>
                                                        <span class="text-muted">Nenhum grupo cadastrado nesta empresa.</span>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-5 form-group">
                                                <label for="setores-<?php echo $key ?>">Setor</label>
                                                <select name="setores[]" id="setores-<?php echo $key ?>" class="form-control js-setor">
                                                    <option value="">Nenhum</option>
                                                    <?php foreach ($emp['setores'] as $setor) { ?>
                                                        <option value="<?php echo (int) $setor['id'] ?>" <?php echo in_array($setor['id'], $setores_atuais) ? 'selected' : '' ?>><?php echo html_escape($setor['descricao']) ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group<?php echo $field_error_class('unidades[]'); ?>" style="margin-bottom:0;">
                                            <label>Unidades<?php echo $required_mark; ?></label>
                                            <span class="usuario-unidades-contador js-unidades-contador">Nenhuma selecionada</span>
                                            <?php if (empty($emp['unidades'])) { ?>
                                                <p class="text-muted">Você não tem acesso a unidades desta empresa.</p>
                                            <?php } else { ?>
                                                <table class="table table-condensed table-hover usuario-unidades js-unidades-table" style="width:100%;">
                                                    <thead>
                                                        <tr>
                                                            <th class="no-sort usuario-unidades-check"><input type="checkbox" class="js-unidades-todas" title="Marcar as unidades filtradas"></th>
                                                            <th>Nome fantasia</th>
                                                            <th>Razão social</th>
                                                            <th>E-mail</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($emp['unidades'] as $unid) { ?>
                                                            <tr>
                                                                <td class="usuario-unidades-check"><input type="checkbox" class="js-unidade" name="unidades[]" value="<?php echo (int) $unid['id'] ?>" <?php echo in_array($unid['id'], $unidades_atuais) ? 'checked' : '' ?>></td>
                                                                <td><?php echo html_escape($unid['unidade']) ?></td>
                                                                <td><?php echo html_escape($unid['razao_social']) ?></td>
                                                                <td><?php echo html_escape($unid['email']) ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">3</span>Dados pessoais <small class="text-muted">(opcional)</small></h3>
                        <span class="box-subtitle">Documentos e contato.</span>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-4 form-group">
                                <label for="cpf">CPF</label>
                                <input type="text" id="cpf" name="cpf" class="form-control" maxlength="14" inputmode="numeric" placeholder="000.000.000-00" value="<?php echo html_escape($cpf['value']) ?>">
                            </div>
                            <div class="col-sm-4 form-group">
                                <label for="rg">RG</label>
                                <input type="text" id="rg" name="rg" class="form-control" maxlength="20" value="<?php echo html_escape($rg['value']) ?>">
                            </div>
                            <div class="col-sm-4 form-group">
                                <label for="data_nascimento">Data de nascimento</label>
                                <div class="input-group date" id="datetimepicker">
                                    <input type="text" id="data_nascimento" name="data_nascimento" class="form-control" autocomplete="off" placeholder="dd/mm/aaaa" value="<?php echo html_escape($data_nascimento['value']) ?>">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 form-group">
                                <label for="telefone">Telefone</label>
                                <input type="text" id="telefone" name="telefone" class="form-control" maxlength="20" inputmode="tel" placeholder="(00) 0000-0000" value="<?php echo html_escape($telefone['value']) ?>">
                            </div>
                            <div class="col-sm-4 form-group">
                                <label for="celular">Celular</label>
                                <input type="text" id="celular" name="celular" class="form-control" maxlength="20" inputmode="tel" placeholder="(00) 00000-0000" value="<?php echo html_escape($celular['value']) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">4</span>Endereço <small class="text-muted">(opcional)</small></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-3 form-group">
                                <label for="cep">CEP</label>
                                <input type="text" id="cep" name="cep" class="form-control" maxlength="9" inputmode="numeric" placeholder="00000-000" value="<?php echo html_escape($cep['value']) ?>">
                            </div>
                            <div class="col-sm-7 form-group">
                                <label for="logradouro">Logradouro</label>
                                <input type="text" id="logradouro" name="logradouro" class="form-control" value="<?php echo html_escape($logradouro['value']) ?>">
                            </div>
                            <div class="col-sm-2 form-group">
                                <label for="numero">Número</label>
                                <input type="text" id="numero" name="numero" class="form-control" maxlength="10" value="<?php echo html_escape($numero['value']) ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 form-group">
                                <label for="bairro">Bairro</label>
                                <input type="text" id="bairro" name="bairro" class="form-control" value="<?php echo html_escape($bairro['value']) ?>">
                            </div>
                            <div class="col-sm-5 form-group">
                                <label for="cidade">Cidade</label>
                                <input type="text" id="cidade" name="cidade" class="form-control" value="<?php echo html_escape($cidade['value']) ?>">
                            </div>
                            <div class="col-sm-2 form-group">
                                <label for="uf">UF</label>
                                <select id="uf" name="uf" class="form-control">
                                    <?php foreach ($uf['options'] as $sigla => $nome) { ?>
                                        <option value="<?php echo html_escape($sigla) ?>" <?php echo (string) $uf['selected'] === (string) $sigla ? 'selected' : '' ?>><?php echo $sigla === '' ? html_escape($nome) : html_escape($sigla) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-4">
                <div class="sticky-column">
                    <div class="box box-default usuario-resumo">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-list-alt"></i> Resumo</h3>
                        </div>
                        <div class="box-body">
                            <dl>
                                <?php if ($cadastro_completo) { ?>
                                    <dt>Nome</dt><dd id="resumo-nome" class="is-empty">Não informado</dd>
                                <?php } ?>
                                <dt>E-mail</dt><dd id="resumo-email" class="is-empty">Não informado</dd>
                                <dt>Empresas</dt><dd id="resumo-empresas" class="is-empty">Nenhuma</dd>
                                <dt>Grupos</dt><dd id="resumo-grupos" class="is-empty">Nenhum</dd>
                                <dt>Unidades</dt><dd id="resumo-unidades" class="is-empty">Nenhuma</dd>
                            </dl>
                            <ul class="usuario-pendencias" id="resumo-pendencias"></ul>
                            <?php if ($editando) { ?>
                                <div class="form-group usuario-confirmacao" style="margin-bottom:0;">
                                    <label for="senha_atual">Sua senha<?php echo $required_mark; ?></label>
                                    <input type="password" id="senha_atual" name="senha_atual" class="form-control" autocomplete="current-password">
                                    <p class="help-block">Por segurança, confirme com a senha do seu usuário para salvar.</p>
                                </div>
                            <?php } elseif (!$cadastro_completo) { ?>
                                <p class="usuario-dica"><i class="fa fa-envelope-o"></i> Ao salvar, o usuário recebe um e-mail para ativar o cadastro e definir nome e senha.</p>
                            <?php } ?>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-block" id="btn-salvar-usuario"><i class="fa fa-check"></i> <?php echo $editando ? 'Salvar alterações' : 'Criar usuário' ?></button>
                            <?php echo anchor($url_cancelar, lang('actions_cancel'), array('class' => 'btn btn-default btn-block')); ?>
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
        var tabelas = [];
        var idioma = {
            sZeroRecords: 'Nenhuma unidade encontrada',
            sInfo: '_START_ a _END_ de _TOTAL_',
            sInfoEmpty: '0 unidades',
            sInfoFiltered: '(filtradas de _MAX_)',
            sSearch: '',
            sSearchPlaceholder: 'Buscar unidade...',
            oPaginate: { sFirst: 'Primeiro', sPrevious: 'Anterior', sNext: 'Seguinte', sLast: 'Último' }
        };

        $('.js-unidades-table').each(function() {
            var tabela = $(this).DataTable({
                paging: true,
                pageLength: 8,
                lengthChange: false,
                ordering: true,
                order: [[1, 'asc']],
                columnDefs: [{ orderable: false, targets: 0 }],
                autoWidth: false,
                dom: 'frtip',
                oLanguage: idioma
            });
            $(this).data('tabela', tabela);
            tabelas.push(tabela);
        });

        function tabelaDa(empresa) {
            return empresa.find('.js-unidades-table').data('tabela');
        }

        // Unidades marcadas da empresa, incluindo as que estão em outras páginas da tabela.
        function unidadesMarcadas(empresa) {
            var tabela = tabelaDa(empresa);
            return tabela ? tabela.$('.js-unidade:checked').length : 0;
        }

        $('.js-unidades-todas').on('click', function() {
            var empresa = $(this).closest('.js-empresa');
            var linhas = tabelaDa(empresa).rows({ search: 'applied' }).nodes();
            $('.js-unidade', linhas).prop('checked', this.checked);
            if (this.checked) { empresa.find('.js-vinculo').prop('checked', true); }
            atualizar();
        });

        // Marcar grupo ou unidade já vincula o usuário à empresa.
        $('#form-user').on('change', '.js-grupo, .js-unidade, .js-setor', function() {
            var marcado = $(this).is('select') ? $(this).val() !== '' : this.checked;
            if (marcado) { $(this).closest('.js-empresa').find('.js-vinculo').prop('checked', true); }
            if ($(this).hasClass('js-grupo')) { $(this).closest('.usuario-grupo').toggleClass('is-checked', this.checked); }
            atualizar();
        });
        $('#form-user').on('change keyup', 'input, select', atualizar);

        <?php if ($cadastro_completo) { ?>
        $('#password, #password_confirm').on('keyup change', function() {
            var senha = $('#password').val(), confirma = $('#password_confirm').val(), aviso = $('#senha-confere');
            aviso.removeClass('text-red text-green');
            if (!confirma) { aviso.text(''); return; }
            if (senha === confirma) { aviso.addClass('text-green').text('As senhas conferem.'); }
            else { aviso.addClass('text-red').text('As senhas não conferem.'); }
        });
        <?php } ?>

        $('#form-user').on('submit', function(e) {
            var form = this;
            if (!validar()) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: $('#resumo-pendencias').closest('.box').offset().top - 70 }, 200);
                return;
            }
            // O DataTables só mantém no formulário as linhas da página visível; as demais vão como campos ocultos.
            $.each(tabelas, function(i, tabela) {
                tabela.$('.js-unidade:checked').each(function() {
                    if (!$.contains(document, this)) {
                        $('<input type="hidden" name="unidades[]">').val(this.value).appendTo(form);
                    }
                });
            });
            $('#btn-salvar-usuario').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');
        });

        function texto(id, valor, vazio) {
            $(id).text(valor || vazio).toggleClass('is-empty', !valor);
        }

        function pendencias() {
            var lista = [];
            var email = $.trim($('#email').val());
            if (!email) { lista.push('Informe o e-mail.'); }
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { lista.push('O e-mail não parece válido.'); }
            <?php if ($cadastro_completo) { ?>
            if (!$.trim($('#first_name').val())) { lista.push('Informe o nome.'); }
            if (!$.trim($('#last_name').val())) { lista.push('Informe o sobrenome.'); }
            if ($('#password').val() !== $('#password_confirm').val()) { lista.push('As senhas não conferem.'); }
            <?php } ?>
            <?php if ($editando) { ?>
            if (!$('#senha_atual').val()) { lista.push('Confirme com a sua senha.'); }
            <?php } ?>
            if ($('.js-vinculo:checked').length === 0) { lista.push('Vincule o usuário a pelo menos uma empresa.'); }
            var unidades = 0;
            $('.js-empresa').each(function() { unidades += unidadesMarcadas($(this)); });
            if (unidades === 0) { lista.push('Selecione pelo menos uma unidade.'); }
            return lista;
        }

        var tentouSalvar = false;
        function validar() {
            tentouSalvar = true;
            atualizar();
            return pendencias().length === 0;
        }

        function atualizar() {
            <?php if ($cadastro_completo) { ?>
            texto('#resumo-nome', $.trim($('#first_name').val() + ' ' + $('#last_name').val()), 'Não informado');
            <?php } ?>
            texto('#resumo-email', $.trim($('#email').val()), 'Não informado');

            var empresas = [], grupos = [], totalUnidades = 0;
            $('.js-empresa').each(function() {
                var empresa = $(this), qtd = unidadesMarcadas(empresa);
                totalUnidades += qtd;
                if (empresa.find('.js-vinculo').is(':checked')) { empresas.push(empresa.data('nome')); }
                empresa.find('.js-grupo:checked').each(function() { grupos.push($.trim($(this).parent().text())); });
                empresa.find('.js-unidades-contador').text(qtd === 0 ? 'Nenhuma selecionada' : qtd + (qtd === 1 ? ' selecionada' : ' selecionadas'));
                $('.js-empresa-badge[data-empresa="' + empresa.data('empresa') + '"]').text(qtd).toggle(qtd > 0);
            });
            texto('#resumo-empresas', empresas.join(', '), 'Nenhuma');
            texto('#resumo-grupos', grupos.join(', '), 'Nenhum');
            texto('#resumo-unidades', totalUnidades > 0 ? totalUnidades + (totalUnidades === 1 ? ' unidade' : ' unidades') : '', 'Nenhuma');

            var lista = $('#resumo-pendencias').empty();
            if (tentouSalvar) {
                $.each(pendencias(), function(i, item) {
                    $('<li>').html('<i class="fa fa-exclamation-circle"></i> ').append(document.createTextNode(item)).appendTo(lista);
                });
            }
        }
        atualizar();
    });
</script>
<style type="text/css">
    .usuario-form .box { border-top-width: 3px; }
    .usuario-form .box-header .box-title { font-size: 16px; }
    .usuario-form .step-number { display: inline-block; width: 24px; height: 24px; line-height: 24px; margin-right: 6px; border-radius: 50%; background: #3c8dbc; color: #fff; text-align: center; font-size: 13px; font-weight: 600; }
    .usuario-form .box-header .box-subtitle { display: block; margin: 4px 0 0 30px; color: #777; font-size: 12px; }
    .usuario-form .form-group label { font-weight: 600; }
    .usuario-form .help-block { margin-bottom: 0; font-size: 12px; }
    .usuario-form .usuario-email { font-size: 15px; }
    .usuario-form .nav-tabs-custom { box-shadow: none; margin-bottom: 0; }
    .usuario-form .nav-tabs-custom > .tab-content { padding: 15px 0 0; }
    .usuario-form .nav-tabs-custom .label { margin-left: 4px; }
    .usuario-vinculo { display: flex; align-items: center; gap: 8px; margin: 0 0 15px; padding: 10px 12px; background: #f7f9fb; border: 1px solid #e3e8ee; border-radius: 4px; font-weight: 400 !important; cursor: pointer; }
    .usuario-vinculo input { margin: 0; }
    .usuario-grupos { display: flex; flex-wrap: wrap; gap: 6px; }
    .usuario-grupo { display: inline-flex; align-items: center; gap: 6px; margin: 0; padding: 5px 10px; border: 1px solid #d2d6de; border-radius: 16px; font-weight: 400 !important; cursor: pointer; }
    .usuario-grupo input { margin: 0; }
    .usuario-grupo.is-checked { background: #eaf2f8; border-color: #3c8dbc; }
    .usuario-unidades-contador { margin-left: 6px; color: #777; font-size: 12px; }
    .usuario-unidades { margin-top: 4px; }
    .usuario-unidades > tbody > tr > td { border-top: 0; border-bottom: 1px solid #d2d6de; }
    .usuario-unidades > tbody > tr:nth-child(even) > td { background: #f9fafc; }
    .usuario-unidades .usuario-unidades-check { width: 32px; text-align: center; }
    .usuario-form .dataTables_filter { float: none; text-align: left; }
    .usuario-form .dataTables_filter input { margin-left: 0; width: 260px; max-width: 100%; }
    .usuario-resumo dl { margin-bottom: 10px; }
    .usuario-resumo dt { color: #777; font-weight: 400; font-size: 12px; }
    .usuario-resumo dd { margin-bottom: 8px; font-weight: 600; word-break: break-word; }
    .usuario-resumo dd.is-empty { color: #bbb; font-weight: 400; font-style: italic; }
    .usuario-pendencias { margin: 0 0 10px; padding: 0; list-style: none; color: #dd4b39; font-size: 12px; }
    .usuario-pendencias li { margin-bottom: 4px; }
    .usuario-confirmacao { padding-top: 10px; border-top: 1px solid #f0f0f0; }
    .usuario-edit-heading { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 15px; }
    .usuario-edit-id { font-size: 18px; font-weight: 700; color: #777; }
    .usuario-edit-nome { font-size: 18px; font-weight: 600; }
    .usuario-edit-meta { color: #777; font-size: 12px; }
    .usuario-dica { margin: 0; padding: 10px; background: #f7f9fb; border-radius: 4px; font-size: 12px; color: #666; }
    @media (min-width: 992px) {
        .usuario-form .sticky-column { position: sticky; top: 60px; }
    }
</style>
