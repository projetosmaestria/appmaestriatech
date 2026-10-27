<?php
$linhas = '';
foreach ($chamados as $value) {
    $link_avaliacao = base_url('auth/avaliacao_chamado/' . $value->dono_id . '/' . sha1($value->id . $value->dono_id));
    $linhas .= '<tr>' .
        '<td style="padding:10px 12px;border-bottom:1px solid #eeeeee;">' .
            '<div style="font-size:12px;color:#888888;">Chamado #' . (int) $value->id . ' &middot; ' . html_escape($value->unidade) . ' &middot; finalizado em ' . date('d/m/Y', strtotime($value->data_finalizacao)) . '</div>' .
            '<div style="margin-top:2px;font-size:14px;font-weight:bold;color:#222222;">' . html_escape($value->titulo) . '</div>' .
            '<div style="font-size:12px;color:#888888;">' . html_escape($value->catalogo_desc) . '</div>' .
        '</td>' .
        '<td align="right" style="padding:10px 12px;border-bottom:1px solid #eeeeee;white-space:nowrap;">' .
            '<a href="' . $link_avaliacao . '" style="display:inline-block;padding:7px 14px;border-radius:4px;background:#3c8dbc;color:#ffffff;font-size:13px;font-weight:bold;text-decoration:none;">Avaliar</a>' .
        '</td>' .
    '</tr>';
}
$conteudo = '<p style="margin:0 0 12px;font-size:16px;color:#222222;">Ol&aacute;, <b>' . html_escape($dono) . '</b>.</p>' .
    '<p style="margin:0 0 16px;">Estes atendimentos foram conclu&iacute;dos e ainda n&atilde;o recebemos sua opini&atilde;o. A avalia&ccedil;&atilde;o &eacute; r&aacute;pida e nos ajuda a melhorar a qualidade do atendimento.</p>' .
    '<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eeeeee;border-radius:4px;border-collapse:separate;">' . $linhas . '</table>' .
    '<p style="margin:16px 0 0;font-size:13px;color:#777777;">Sabemos que seu tempo &eacute; valioso e agradecemos sua colabora&ccedil;&atilde;o.</p>';
echo email_layout_chamado(array(
    'empresa_id' => isset($empresa_id) ? $empresa_id : 0,
    'rotulo' => 'Avalia&ccedil;&atilde;o pendente',
    'titulo' => count($chamados) . ' atendimento' . (count($chamados) === 1 ? '' : 's') . ' aguardando sua avalia&ccedil;&atilde;o',
    'conteudo' => $conteudo,
));
