<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$url_empresas = site_url('integracao/empresas');
$url_usuarios = site_url('integracao/usuarios');
$nome_criador = $token_ativo ? trim($token_ativo->first_name . ' ' . $token_ativo->last_name) : '';
?>
            <div class="content-wrapper">
                <section class="content-header">
                    <?php echo $pagetitle; ?>
                    <?php echo $breadcrumb; ?>
                </section>

                <section class="content integracao-prefs">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs">
                                    <li class=""><?php echo anchor('prefs/index', lang('menu_int_admin')); ?></li>
                                    <li class=""><?php echo anchor('prefs/dashboard', lang('menu_int_dashboard')); ?></li>
                                    <li class=""><?php echo anchor('prefs/email', lang('menu_int_email')); ?></li>
                                    <li class="active"><?php echo anchor('prefs/integracao', 'Integração'); ?></li>
                                </ul>
                                <div class="tab-content">
                                    <?php if (!empty($message)) { echo $message; } ?>

                                    <?php if (!$tabela_ok) { ?>
                                        <div class="alert alert-warning">Aplique o script <code>sql/2026-11-17_integracao_token.sql</code> no banco para usar a integração.</div>
                                    <?php } else { ?>

                                        <?php if ($token_novo !== '') { ?>
                                            <div class="integracao-token-novo">
                                                <p><strong><i class="fa fa-key"></i> Token gerado.</strong> Copie agora: por segurança, ele não será mostrado de novo.</p>
                                                <div class="input-group">
                                                    <input type="text" class="form-control integracao-token-campo" id="integracao-token" value="<?php echo html_escape($token_novo) ?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-primary btn-flat" id="integracao-copiar"><i class="fa fa-copy"></i> Copiar</button>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <h4 class="integracao-titulo">Token da API</h4>
                                        <p class="text-muted">Chave que o sistema externo envia para usar os endpoints abaixo. Ela vale para os clientes e usuários desta empresa e não pede e-mail nem senha.</p>

                                        <?php if ($token_ativo) { ?>
                                            <table class="table integracao-status">
                                                <tr><th>Situação</th><td><span class="label label-success">Ativo</span></td></tr>
                                                <tr><th>Começa com</th><td><code><?php echo html_escape($token_ativo->prefixo) ?>…</code></td></tr>
                                                <tr><th>Gerado em</th><td><?php echo html_escape(date('d/m/Y H:i', strtotime($token_ativo->created_at))) ?><?php echo $nome_criador !== '' ? ' por ' . html_escape($nome_criador) : '' ?></td></tr>
                                                <tr><th>Último uso</th><td><?php echo $token_ativo->ultimo_uso_em ? html_escape(date('d/m/Y H:i', strtotime($token_ativo->ultimo_uso_em))) : 'Ainda não usado' ?></td></tr>
                                            </table>
                                        <?php } else { ?>
                                            <p><span class="label label-default">Nenhum token ativo</span></p>
                                        <?php } ?>

                                        <div class="integracao-acoes">
                                            <?php echo form_open('prefs/integracao', array('class' => 'js-integracao-confirmar', 'data-confirmar' => $token_ativo ? 'Gerar um novo token? O token atual deixa de funcionar na hora e o sistema que o usa precisa receber o novo.' : '')); ?>
                                                <input type="hidden" name="nonce" value="<?php echo html_escape($nonce) ?>">
                                                <input type="hidden" name="acao" value="gerar">
                                                <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-refresh"></i> <?php echo $token_ativo ? 'Gerar novo token' : 'Gerar token' ?></button>
                                            <?php echo form_close(); ?>
                                            <?php if ($token_ativo) { ?>
                                                <?php echo form_open('prefs/integracao', array('class' => 'js-integracao-confirmar', 'data-confirmar' => 'Revogar o token? Os sistemas que o usam perdem o acesso na hora.')); ?>
                                                    <input type="hidden" name="nonce" value="<?php echo html_escape($nonce) ?>">
                                                    <input type="hidden" name="acao" value="revogar">
                                                    <button type="submit" class="btn btn-default btn-flat"><i class="fa fa-ban"></i> Revogar</button>
                                                <?php echo form_close(); ?>
                                            <?php } ?>
                                        </div>

                                        <h4 class="integracao-titulo">Como usar</h4>
                                        <p>Envie o token no cabeçalho <code>Authorization: Bearer &lt;token&gt;</code> (ou <code>X-Api-Token: &lt;token&gt;</code>). As respostas são em JSON.</p>

                                        <div class="integracao-endpoint">
                                            <p><span class="label label-success">GET</span> <code><?php echo html_escape($url_empresas) ?></code></p>
                                            <p class="text-muted">Lista os clientes (unidades) ativos com CNPJ e nome fantasia.</p>
<pre>curl -H "Authorization: Bearer SEU_TOKEN" <?php echo html_escape($url_empresas) ?>


{ "ok": true, "total": 1, "empresas": [{ "cnpj": "12345678000190", "nome_fantasia": "Cliente X" }] }</pre>
                                        </div>

                                        <div class="integracao-endpoint">
                                            <p><span class="label label-primary">POST</span> <code><?php echo html_escape($url_usuarios) ?></code></p>
                                            <p class="text-muted">Cadastra o usuário já ativo no grupo "Clientes", vinculado às unidades dos CNPJs (um ou vários, em lista), e envia o e-mail para ele criar a senha. A resposta traz <code>logs</code> com o que deu certo e o que deu errado.</p>
<pre>curl -X POST -H "Authorization: Bearer SEU_TOKEN" -H "Content-Type: application/json" \
  -d '{"nome":"Maria","sobrenome":"Silva","email":"maria@cliente.com.br","celular":"(44) 99999-0000","cnpj":["12.345.678/0001-90","98.765.432/0001-10"]}' \
  <?php echo html_escape($url_usuarios) ?></pre>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

<script type="text/javascript">
    // O jQuery é carregado no rodapé.
    window.addEventListener('load', function() {
        $('.js-integracao-confirmar').on('submit', function(event) {
            var texto = $(this).attr('data-confirmar');
            if (texto && !window.confirm(texto)) {
                event.preventDefault();
            }
        });
        $('#integracao-copiar').on('click', function() {
            var $botao = $(this);
            var campo = document.getElementById('integracao-token');
            campo.select();
            var copiado = function() { $botao.find('i').attr('class', 'fa fa-check'); };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(campo.value).then(copiado, function() { document.execCommand('copy'); copiado(); });
            } else {
                document.execCommand('copy');
                copiado();
            }
        });
    });
</script>
<style type="text/css">
    .integracao-prefs .tab-content { padding: 15px 20px; }
    .integracao-titulo { margin: 18px 0 6px; font-weight: 600; }
    .integracao-titulo:first-of-type { margin-top: 4px; }
    .integracao-token-novo { background: #eaf6ee; border: 1px solid #00a65a; border-radius: 4px; padding: 12px 14px; margin-bottom: 10px; }
    .integracao-token-campo { font-family: Menlo, Consolas, monospace; background: #fff !important; }
    .integracao-status { max-width: 560px; margin-bottom: 10px; }
    .integracao-status th { width: 140px; color: #777; font-weight: 600; border-top: 0 !important; border-bottom: 1px solid #d2d6de; }
    .integracao-status td { border-top: 0 !important; border-bottom: 1px solid #d2d6de; }
    .integracao-acoes { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
    .integracao-acoes form { margin: 0; }
    .integracao-endpoint { border-top: 1px solid #d2d6de; padding-top: 10px; margin-top: 10px; }
    .integracao-endpoint pre { white-space: pre-wrap; word-break: break-all; font-size: 12px; }
</style>
