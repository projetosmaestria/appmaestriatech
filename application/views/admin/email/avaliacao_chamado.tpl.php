<?php
$conteudo = '<p style="margin:0 0 12px;font-size:16px;color:#222222;">Ol&aacute;, <b>' . html_escape($dono) . '</b>.</p>' .
    '<p style="margin:0 0 12px;">O atendimento do chamado abaixo foi conclu&iacute;do. Sua avalia&ccedil;&atilde;o leva menos de um minuto e nos ajuda a melhorar.</p>';
if (!empty($chamado_id)) {
    $conteudo .= '<div style="margin:0 0 4px;padding:14px 16px;background:#f7f9fb;border:1px solid #e3e7ec;border-left:4px solid #3c8dbc;border-radius:4px;">' .
        '<div style="font-size:12px;color:#888888;">Chamado #' . (int) $chamado_id . (!empty($cliente) ? ' &middot; ' . html_escape($cliente) : '') . '</div>' .
        (!empty($titulo) ? '<div style="margin-top:4px;font-size:15px;font-weight:bold;color:#222222;">' . html_escape($titulo) . '</div>' : '') .
        '</div>';
}
echo email_layout_chamado(array(
    'empresa_id' => isset($empresa_id) ? $empresa_id : 0,
    'rotulo' => 'Avalia&ccedil;&atilde;o do atendimento',
    'titulo' => !empty($chamado_id) ? 'Chamado #' . (int) $chamado_id . ' conclu&iacute;do' : 'Atendimento conclu&iacute;do',
    'texto' => 'Conte para n&oacute;s como foi o atendimento.',
    'conteudo' => $conteudo,
    'botao_texto' => 'Avaliar atendimento',
    'botao_link' => $link,
));
