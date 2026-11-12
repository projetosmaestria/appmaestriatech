<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Perfil do usuário: dados só para leitura; foto e senha podem ser alteradas pelo próprio usuário (ou por quem edita usuários).
$pessoa = isset($user_info->pessoa) && $user_info->pessoa ? $user_info->pessoa : new stdClass();
$valor = function ($objeto, $campo) {
    return isset($objeto->$campo) ? trim((string) $objeto->$campo) : '';
};
// Campo somente leitura no mesmo visual do formulário de edição.
$campo = function ($rotulo, $texto, $classe = 'col-sm-4') {
    return '<div class="' . $classe . ' form-group"><label>' . $rotulo . '</label>'
        . '<input type="text" class="form-control perfil-readonly" value="' . html_escape($texto) . '" placeholder="Não informado" readonly tabindex="-1"></div>';
};
$nome_completo = trim($user_info->first_name . ' ' . $user_info->last_name);
$iniciais = strtoupper(mb_substr(trim((string) $user_info->first_name), 0, 1, 'UTF-8') . mb_substr(trim((string) $user_info->last_name), 0, 1, 'UTF-8'));
$nascimento = $valor($pessoa, 'data_nascimento');
$nascimento = ($nascimento !== '' && $nascimento !== '0000-00-00' && strtotime($nascimento)) ? date('d/m/Y', strtotime($nascimento)) : '';
$endereco = $valor($pessoa, 'endereco');
$tem_extras = !empty($user_info->is_aluno) || !empty($user_info->is_aluno_ead) || !empty($user_info->is_tutor_ead);
$aba_extra_ativa = !empty($user_info->is_aluno) ? 'aplicativo' : (!empty($user_info->is_aluno_ead) ? 'ead' : 'tutoria');
$tentativas = isset($user_info->tentativas_acesso) ? (array) $user_info->tentativas_acesso : array();
$cor_grupo = function ($cor) {
    return preg_match('/^#[0-9a-fA-F]{3,6}$/', (string) $cor) ? $cor : '#777777';
};
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content perfil-page">
        <?php if (isset($message)) { echo $message; } ?>

        <div class="perfil-heading">
            <span class="perfil-heading-avatar">
                <?php if ($foto_url) { ?>
                    <img src="<?php echo html_escape($foto_url) ?>" alt="">
                <?php } else { ?>
                    <?php echo html_escape($iniciais !== '' ? $iniciais : '?') ?>
                <?php } ?>
            </span>
            <span class="perfil-heading-id">#<?php echo (int) $user_info->id ?></span>
            <span class="perfil-heading-nome"><?php echo html_escape($nome_completo !== '' ? $nome_completo : $user_info->email) ?></span>
            <?php if ($user_info->active == 1) { ?>
                <span class="label label-success">Ativo</span>
            <?php } else { ?>
                <span class="label label-default">Inativo</span>
            <?php } ?>
            <span class="perfil-heading-meta">
                <?php if (!empty($user_info->created_on)) { ?>Cadastrado em <?php echo date('d/m/Y', (int) $user_info->created_on) ?><?php } ?>
                <?php if (!empty($user_info->last_login)) { ?> · Último acesso em <?php echo date('d/m/Y H:i', (int) $user_info->last_login) ?><?php } else { ?> · Ainda não acessou<?php } ?>
            </span>
            <?php if ($can_edit) { ?>
                <a href="<?php echo base_url('users/edit/' . (int) $user_info->id) ?>" class="btn btn-default btn-sm perfil-heading-acao"><i class="fa fa-pencil"></i> Editar cadastro</a>
            <?php } ?>
        </div>

        <div class="row">
            <div class="col-md-8">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">1</span>Acesso</h3>
                        <span class="box-subtitle">Nome e login do usuário.<?php echo $can_edit ? '' : ' Para alterar, fale com o administrador do sistema.' ?></span>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php echo $campo('Nome', $user_info->first_name, 'col-sm-6') ?>
                            <?php echo $campo('Sobrenome', $user_info->last_name, 'col-sm-6') ?>
                        </div>
                        <div class="row">
                            <?php echo $campo('E-mail (login)', $user_info->email, 'col-sm-12') ?>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">2</span>Permissões e unidades</h3>
                        <span class="box-subtitle">Grupos e unidades do usuário na empresa atual.</span>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>Grupos</label>
                            <div class="perfil-grupos">
                                <?php foreach ((array) $user_info->groups as $group) { ?>
                                    <span class="perfil-grupo"><i class="fa fa-circle" style="color:<?php echo html_escape($cor_grupo($group->bgcolor)) ?>"></i> <?php echo html_escape($group->name) ?></span>
                                <?php } ?>
                                <?php if (empty($user_info->groups)) { ?>
                                    <span class="text-muted">Nenhum grupo nesta empresa.</span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Unidades <span class="perfil-contador"><?php echo count((array) $user_info->empresas_unidades) ?></span></label>
                            <?php if (empty($user_info->empresas_unidades)) { ?>
                                <p class="text-muted" style="margin:0;">Nenhuma unidade vinculada nesta empresa.</p>
                            <?php } else { ?>
                                <ul class="perfil-unidades">
                                    <?php foreach ($user_info->empresas_unidades as $unidade) { ?>
                                        <li>
                                            <i class="fa fa-building-o text-muted"></i>
                                            <span><?php echo html_escape($unidade->unidade) ?></span>
                                            <?php if ($unidade->padrao == 1) { ?><span class="label label-primary">Padrão</span><?php } ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">3</span>Dados pessoais</h3>
                        <span class="box-subtitle">Documentos e contato.</span>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php echo $campo('CPF', $valor($pessoa, 'cpf')) ?>
                            <?php echo $campo('RG', $valor($pessoa, 'rg')) ?>
                            <?php echo $campo('Data de nascimento', $nascimento) ?>
                        </div>
                        <div class="row">
                            <?php echo $campo('Telefone', $valor($pessoa, 'telefone')) ?>
                            <?php echo $campo('Celular', $valor($pessoa, 'celular')) ?>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><span class="step-number">4</span>Endereço</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php echo $campo('CEP', $valor($pessoa, 'cep'), 'col-sm-3') ?>
                            <?php echo $campo('Logradouro', $endereco, 'col-sm-7') ?>
                            <?php echo $campo('Número', $valor($pessoa, 'numero'), 'col-sm-2') ?>
                        </div>
                        <div class="row">
                            <?php echo $campo('Bairro', $valor($pessoa, 'bairro'), 'col-sm-5') ?>
                            <?php echo $campo('Cidade', $valor($pessoa, 'cidade'), 'col-sm-5') ?>
                            <?php echo $campo('UF', $valor($pessoa, 'uf'), 'col-sm-2') ?>
                        </div>
                    </div>
                </div>

                <?php if ($tem_extras) { ?>
                    <div class="nav-tabs-custom perfil-extras">
                        <ul class="nav nav-tabs">
                            <?php if (!empty($user_info->is_aluno)) { ?>
                                <li class="<?php echo $aba_extra_ativa === 'aplicativo' ? 'active' : '' ?>"><a href="#aplicativo" data-toggle="tab">Aplicativo</a></li>
                            <?php } ?>
                            <?php if (!empty($user_info->is_aluno_ead)) { ?>
                                <li class="<?php echo $aba_extra_ativa === 'ead' ? 'active' : '' ?>"><a id="ead_tab" href="#ead" data-toggle="tab">EAD</a></li>
                            <?php } ?>
                            <?php if (!empty($user_info->is_tutor_ead)) { ?>
                                <li class="<?php echo $aba_extra_ativa === 'tutoria' ? 'active' : '' ?>"><a id="tutoria_tab" href="#tutoria" data-toggle="tab">Tutoria</a></li>
                            <?php } ?>
                        </ul>
                        <div class="tab-content">
                            <?php include __DIR__ . '/_profile_extras.php'; ?>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                <?php } ?>

            </div>

            <div class="col-md-4">
                <div class="sticky-column">

                    <div class="box box-default perfil-foto-box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-camera"></i> Foto</h3>
                        </div>
                        <div class="box-body text-center">
                            <div class="perfil-foto" id="perfil-foto">
                                <?php if ($foto_url) { ?>
                                    <img src="<?php echo html_escape($foto_url) ?>" alt="Foto de <?php echo html_escape($nome_completo) ?>" id="perfil-foto-img">
                                <?php } else { ?>
                                    <span id="perfil-foto-iniciais"><?php echo html_escape($iniciais !== '' ? $iniciais : '?') ?></span>
                                <?php } ?>
                            </div>
                            <?php if ($foto_original_url) { ?>
                                <a href="<?php echo html_escape($foto_original_url) ?>" target="_blank" class="small">Ver em tamanho original</a>
                            <?php } ?>

                            <?php if ($pode_alterar) { ?>
                                <?php echo form_open_multipart(current_url(), array('id' => 'form-foto', 'class' => 'perfil-foto-form')); ?>
                                    <input type="hidden" name="acao" value="foto">
                                    <label class="perfil-foto-drop" for="foto" id="perfil-foto-drop">
                                        <i class="fa fa-cloud-upload"></i>
                                        <span id="perfil-foto-texto">Arraste a foto aqui ou <strong>clique para escolher</strong></span>
                                        <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden">
                                    </label>
                                    <p class="help-block" id="perfil-foto-info">JPG, PNG, WEBP ou GIF. Fotos acima de 5 MB são compactadas automaticamente.</p>
                                    <button type="submit" class="btn btn-primary btn-block" id="btn-salvar-foto" disabled><i class="fa fa-check"></i> Salvar foto</button>
                                <?php echo form_close(); ?>
                                <?php if ($foto_url) { ?>
                                    <?php echo form_open(current_url(), array('id' => 'form-remover-foto')); ?>
                                        <input type="hidden" name="acao" value="remover_foto">
                                        <button type="submit" class="btn btn-default btn-block"><i class="fa fa-trash-o"></i> Remover foto</button>
                                    <?php echo form_close(); ?>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-lock"></i> Senha</h3>
                        </div>
                        <div class="box-body">
                            <p class="perfil-senha-status">
                                <?php if ($senha_dias_restantes === null) { ?>
                                    <span class="text-muted"><i class="fa fa-info-circle"></i> Sem registro da última troca de senha.</span>
                                <?php } elseif ($senha_dias_restantes <= 0) { ?>
                                    <span class="text-red"><i class="fa fa-exclamation-triangle"></i> Senha expirada. Troque a senha.</span>
                                <?php } elseif ($senha_dias_restantes <= 10) { ?>
                                    <span class="text-yellow"><i class="fa fa-clock-o"></i> A senha expira em <?php echo (int) $senha_dias_restantes ?> <?php echo $senha_dias_restantes == 1 ? 'dia' : 'dias' ?>.</span>
                                <?php } else { ?>
                                    <span class="text-green"><i class="fa fa-check-circle"></i> A senha expira em <?php echo (int) $senha_dias_restantes ?> dias.</span>
                                <?php } ?>
                            </p>

                            <?php if ($pode_alterar) { ?>
                                <?php echo form_open(current_url(), array('id' => 'form-senha', 'autocomplete' => 'off')); ?>
                                    <input type="hidden" name="acao" value="senha">
                                    <div class="form-group<?php echo form_error('password') !== '' ? ' has-error' : '' ?>">
                                        <label for="password">Nova senha</label>
                                        <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
                                    </div>
                                    <div class="form-group<?php echo form_error('password_confirm') !== '' ? ' has-error' : '' ?>">
                                        <label for="password_confirm">Confirmar nova senha</label>
                                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" autocomplete="new-password">
                                        <p class="help-block" id="senha-confere"></p>
                                    </div>
                                    <div class="form-group<?php echo form_error('senha_atual') !== '' ? ' has-error' : '' ?>">
                                        <label for="senha_atual"><?php echo $is_self ? 'Senha atual' : 'Sua senha' ?></label>
                                        <input type="password" id="senha_atual" name="senha_atual" class="form-control" autocomplete="current-password">
                                        <p class="help-block"><?php echo $is_self ? 'Para confirmar que é você.' : 'Digite a sua senha (de quem está alterando) para confirmar.' ?></p>
                                    </div>
                                    <p class="help-block">Use letras maiúsculas, minúsculas e números. A nova senha deve ser diferente da atual.</p>
                                    <button type="submit" class="btn btn-primary btn-block" id="btn-salvar-senha"><i class="fa fa-key"></i> Alterar senha</button>
                                <?php echo form_close(); ?>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-shield"></i> Acesso ao sistema</h3>
                        </div>
                        <div class="box-body">
                            <dl class="perfil-resumo">
                                <dt>Último acesso</dt>
                                <dd><?php echo !empty($user_info->last_login) ? date('d/m/Y H:i', (int) $user_info->last_login) : 'Ainda não acessou' ?></dd>
                                <dt>IP do cadastro</dt>
                                <dd><?php echo !empty($user_info->ip_address) ? html_escape($user_info->ip_address) : '-' ?></dd>
                                <?php if ($can_edit) { ?>
                                    <dt>Tentativas de acesso com erro</dt>
                                    <dd><?php echo count($tentativas) ?></dd>
                                <?php } ?>
                            </dl>
                            <?php if ($can_edit && $tentativas) { ?>
                                <ul class="perfil-tentativas">
                                    <?php foreach (array_slice(array_reverse($tentativas), 0, 10) as $ta) { ?>
                                        <li><span><?php echo date('d/m/Y H:i:s', (int) $ta->time) ?></span><span class="text-muted"><?php echo html_escape($ta->ip_address) ?></span></li>
                                    <?php } ?>
                                </ul>
                                <?php if (count($tentativas) > 10) { ?>
                                    <p class="help-block">Mostrando as 10 mais recentes.</p>
                                <?php } ?>
                                <?php echo form_open(current_url()); ?>
                                    <input type="hidden" name="acao" value="tentativas_acesso">
                                    <button type="submit" class="btn btn-danger btn-block btn-sm"><i class="fa fa-eraser"></i> Limpar tentativas de acesso</button>
                                <?php echo form_close(); ?>
                            <?php } ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    window.addEventListener('load', function() {
        <?php if ($tem_extras && $aba_extra_ativa === 'tutoria') { ?>
        // O gráfico da tutoria só é desenhado quando a aba aparece; aberta de início, dispara o evento.
        $('#tutoria_tab').trigger('shown.bs.tab');
        <?php } ?>

        $('#password, #password_confirm').on('keyup change', function() {
            var senha = $('#password').val(), confirma = $('#password_confirm').val(), aviso = $('#senha-confere');
            aviso.removeClass('text-red text-green');
            if (!confirma) { aviso.text(''); return; }
            if (senha === confirma) { aviso.addClass('text-green').text('As senhas conferem.'); }
            else { aviso.addClass('text-red').text('As senhas não conferem.'); }
        });
        $('#form-senha').on('submit', function(e) {
            if (!$('#password').val() || $('#password').val() !== $('#password_confirm').val() || !$('#senha_atual').val()) {
                e.preventDefault();
                $('#senha-confere').removeClass('text-green').addClass('text-red').text('Preencha a nova senha, a confirmação igual e a senha atual.');
                return;
            }
            $('#btn-salvar-senha').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Alterando...');
        });
        <?php if (!empty($abrir_senha)) { ?>
        $('#password').trigger('focus');
        <?php } ?>

        $('#form-remover-foto').on('submit', function(e) {
            if (!window.confirm('Remover a foto do perfil?')) { e.preventDefault(); }
        });

        var input = document.getElementById('foto');
        if (!input) { return; }
        var LIMITE = 5 * 1024 * 1024;
        var $info = $('#perfil-foto-info');
        var $salvar = $('#btn-salvar-foto');
        var podeTrocarArquivo = typeof DataTransfer !== 'undefined';

        function mb(bytes) {
            return (bytes / 1048576).toFixed(1).replace('.', ',') + ' MB';
        }

        function mostrarPrevia(arquivo) {
            var url = URL.createObjectURL(arquivo);
            $('#perfil-foto').html($('<img>').attr({ src: url, alt: '' }));
        }

        function usarArquivo(arquivo, aviso) {
            if (podeTrocarArquivo) {
                var transfer = new DataTransfer();
                transfer.items.add(arquivo);
                input.files = transfer.files;
            }
            mostrarPrevia(arquivo);
            $('#perfil-foto-texto').text(arquivo.name);
            $info.removeClass('text-red').text(aviso);
            $salvar.prop('disabled', false);
        }

        // Acima de 5 MB a foto é reduzida no navegador antes do envio (o servidor também compacta, se precisar).
        function compactar(arquivo, pronto) {
            var img = new Image();
            img.onload = function() {
                var lado = Math.max(img.naturalWidth, img.naturalHeight);
                var escala = Math.min(1, 3000 / lado);
                var qualidade = 0.85;
                var tentar = function() {
                    var canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(img.naturalWidth * escala));
                    canvas.height = Math.max(1, Math.round(img.naturalHeight * escala));
                    var ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#fff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob(function(blob) {
                        if (blob && blob.size <= LIMITE) {
                            URL.revokeObjectURL(img.src);
                            pronto(new File([blob], arquivo.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
                            return;
                        }
                        if (qualidade > 0.65) { qualidade -= 0.1; } else { escala *= 0.75; qualidade = 0.85; }
                        if (escala < 0.05) { pronto(null); return; }
                        tentar();
                    }, 'image/jpeg', qualidade);
                };
                tentar();
            };
            img.onerror = function() { pronto(null); };
            img.src = URL.createObjectURL(arquivo);
        }

        function escolher(arquivo) {
            if (!arquivo) { return; }
            if (!/^image\/(jpeg|png|gif|webp)$/.test(arquivo.type)) {
                $info.addClass('text-red').text('Escolha uma imagem JPG, PNG, WEBP ou GIF.');
                $salvar.prop('disabled', true);
                return;
            }
            if (arquivo.size <= LIMITE) {
                usarArquivo(arquivo, 'Foto de ' + mb(arquivo.size) + '. Clique em Salvar foto.');
                return;
            }
            if (!podeTrocarArquivo || !HTMLCanvasElement.prototype.toBlob) {
                usarArquivo(arquivo, 'Foto de ' + mb(arquivo.size) + ': será compactada pelo servidor ao salvar.');
                return;
            }
            $info.removeClass('text-red').html('<i class="fa fa-spinner fa-spin"></i> Compactando foto de ' + mb(arquivo.size) + '...');
            $salvar.prop('disabled', true);
            compactar(arquivo, function(compactado) {
                if (compactado) {
                    usarArquivo(compactado, 'Foto compactada de ' + mb(arquivo.size) + ' para ' + mb(compactado.size) + '. Clique em Salvar foto.');
                } else {
                    usarArquivo(arquivo, 'Foto de ' + mb(arquivo.size) + ': será compactada pelo servidor ao salvar.');
                }
            });
        }

        $(input).on('change', function() {
            escolher(this.files && this.files[0]);
        });
        $('#perfil-foto-drop')
            .on('dragenter dragover', function(e) {
                e.preventDefault();
                $(this).addClass('is-dragover');
            })
            .on('dragleave drop', function(e) {
                e.preventDefault();
                $(this).removeClass('is-dragover');
                if (e.type === 'drop' && e.originalEvent.dataTransfer.files.length) {
                    var arquivo = e.originalEvent.dataTransfer.files[0];
                    if (podeTrocarArquivo) {
                        escolher(arquivo);
                    } else {
                        $info.addClass('text-red').text('Seu navegador não aceita arrastar; clique para escolher a foto.');
                    }
                }
            });
        $('#form-foto').on('submit', function(e) {
            if (!input.files || !input.files.length) {
                e.preventDefault();
                return;
            }
            $salvar.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
        });
    });
</script>
<style type="text/css">
    .perfil-page .box { border-top-width: 3px; }
    .perfil-page .box-header .box-title { font-size: 16px; }
    .perfil-page .step-number { display: inline-block; width: 24px; height: 24px; line-height: 24px; margin-right: 6px; border-radius: 50%; background: #3c8dbc; color: #fff; text-align: center; font-size: 13px; font-weight: 600; }
    .perfil-page .box-header .box-subtitle { display: block; margin: 4px 0 0 30px; color: #777; font-size: 12px; }
    .perfil-page .form-group label { font-weight: 600; }
    .perfil-page .help-block { margin-bottom: 0; font-size: 12px; }
    .perfil-page .perfil-readonly[readonly] { background: #f7f9fb; cursor: default; }
    .perfil-heading { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 15px; }
    .perfil-heading-avatar { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: #3c8dbc; color: #fff; font-weight: 600; }
    .perfil-heading-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .perfil-heading-id { font-size: 18px; font-weight: 700; color: #777; }
    .perfil-heading-nome { font-size: 18px; font-weight: 600; }
    .perfil-heading-meta { color: #777; font-size: 12px; }
    .perfil-heading-acao { margin-left: auto; }
    .perfil-grupos { display: flex; flex-wrap: wrap; gap: 6px; }
    .perfil-grupo { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border: 1px solid #d2d6de; border-radius: 16px; font-size: 13px; }
    .perfil-grupo .fa { font-size: 9px; }
    .perfil-contador { margin-left: 4px; padding: 1px 7px; border-radius: 10px; background: #d2d6de; color: #444; font-size: 11px; font-weight: 600; }
    .perfil-unidades { max-height: 260px; overflow-y: auto; margin: 0; padding: 0; list-style: none; border: 1px solid #d2d6de; border-radius: 4px; }
    .perfil-unidades li { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-bottom: 1px solid #d2d6de; }
    .perfil-unidades li:last-child { border-bottom: 0; }
    .perfil-unidades li:nth-child(even) { background: #f7f9fb; }
    .perfil-unidades li span:first-of-type { flex: 1; }
    .perfil-extras .tab-content { padding: 15px 0 0; }
    .perfil-foto { display: inline-flex; align-items: center; justify-content: center; width: 160px; height: 160px; margin: 4px auto 8px; border-radius: 50%; overflow: hidden; background: #3c8dbc; color: #fff; font-size: 52px; font-weight: 600; box-shadow: 0 1px 3px rgba(0, 0, 0, .2); }
    .perfil-foto img { width: 100%; height: 100%; object-fit: cover; }
    .perfil-foto-form { margin-top: 12px; text-align: left; }
    .perfil-foto-drop { display: block; margin: 0 0 6px; padding: 16px 12px; border: 2px dashed #c9d3dd; border-radius: 6px; background: #f9fbfc; color: #667; text-align: center; cursor: pointer; font-weight: 400 !important; transition: border-color .15s, background .15s; word-break: break-word; }
    .perfil-foto-drop:hover, .perfil-foto-drop.is-dragover { border-color: #3c8dbc; background: #eef5fa; }
    .perfil-foto-drop .fa { display: block; margin-bottom: 4px; font-size: 24px; color: #3c8dbc; }
    .perfil-foto-drop strong { color: #3c8dbc; }
    #btn-salvar-foto { margin-top: 10px; }
    #form-remover-foto { margin-top: 6px; }
    .perfil-senha-status { margin-bottom: 12px; font-weight: 600; }
    .perfil-resumo { margin-bottom: 10px; }
    .perfil-resumo dt { color: #777; font-weight: 400; font-size: 12px; }
    .perfil-resumo dd { margin-bottom: 8px; font-weight: 600; word-break: break-word; }
    .perfil-tentativas { margin: 0 0 10px; padding: 0; list-style: none; font-size: 12px; }
    .perfil-tentativas li { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #eee; }
    body.dark-mode .perfil-page .perfil-readonly[readonly] { background: #121212 !important; }
    body.dark-mode .perfil-grupo, body.dark-mode .perfil-unidades, body.dark-mode .perfil-unidades li, body.dark-mode .perfil-tentativas li { border-color: #2d2d2d !important; }
    body.dark-mode .perfil-unidades li:nth-child(even) { background: #232323 !important; }
    body.dark-mode .perfil-contador { background: #333333 !important; color: #e2e8f0 !important; }
    body.dark-mode .perfil-foto-drop { background: #181818 !important; border-color: #333333 !important; color: #9ca3af !important; }
    body.dark-mode .perfil-foto-drop:hover, body.dark-mode .perfil-foto-drop.is-dragover { background: #1c2626 !important; border-color: #3c8dbc !important; }
    @media (min-width: 992px) {
        .perfil-page .sticky-column { position: sticky; top: 60px; }
    }
</style>
