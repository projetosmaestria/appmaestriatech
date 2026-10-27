-- Novo modelo do e-mail de abertura de chamado (tipo_email = 5).
-- Marcadores novos: {anexos}, {data_abertura}, {prioridade_cor}, {logo_url}.
-- A empresa 1 (XKEY) recebe a versão com logo, identificação da XKEY e o aviso de backup.
-- As empresas que ainda usam o modelo padrão (nunca editado) recebem a versão neutra.
-- Modelos personalizados ficam como estão.

SET @modelo = '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f4f7;">
<tr><td align="center" style="padding:24px 12px;">
<table width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e3e7ec;border-radius:8px;font-family:Arial,Helvetica,sans-serif;color:#333333;">
	{cabecalho}
	<tr>
		<td style="padding:24px 28px 8px;">
			<p style="margin:0 0 6px;font-size:20px;font-weight:bold;line-height:1.3;color:#222222;">{titulo}</p>
			<p style="margin:0 0 16px;font-size:13px;color:#777777;">
				Aberto em {data_abertura} por <b>{dono}</b>
				&nbsp;<span style="display:inline-block;padding:2px 10px;border-radius:10px;background:{prioridade_cor};color:#ffffff;font-size:12px;font-weight:bold;">Prioridade {prioridade}</span>
			</p>
			{referencia}
			<table cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
				<tr><td style="background:#3c8dbc;border-radius:4px;">
					<a href="{link_chamado}" style="display:inline-block;padding:11px 22px;font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;">Ver chamado</a>
				</td></tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="padding:8px 28px 0;">
			<p style="margin:16px 0 8px;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#3c8dbc;">Detalhes</p>
			<table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
				<tr><td width="38%" style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Cliente</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{unidade_desc}</td></tr>
				<tr><td style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Produto / m&oacute;dulo</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{produto_desc} / {modulo_desc}</td></tr>
				<tr><td style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Categoria</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{categoria_desc}</td></tr>
				<tr><td style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Tipo de servi&ccedil;o</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{tipo_servico_desc}</td></tr>
				<tr><td style="padding:7px 0;color:#777777;">N&iacute;vel</td><td style="padding:7px 0;font-weight:bold;">{nivel}</td></tr>
			</table>

			<p style="margin:24px 0 8px;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#3c8dbc;">Equipe</p>
			<table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
				<tr><td width="38%" style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Solicitante</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{dono}</td></tr>
				<tr><td style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Respons&aacute;vel</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{responsavel}</td></tr>
				<tr><td style="padding:7px 0;color:#777777;">Programador</td><td style="padding:7px 0;font-weight:bold;">{desenvolvedor}</td></tr>
			</table>

			<p style="margin:24px 0 8px;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#3c8dbc;">Descri&ccedil;&atilde;o</p>
			<div style="padding:14px 16px;background:#f7f9fb;border:1px solid #eeeeee;border-radius:4px;font-size:14px;line-height:1.5;color:#444444;">{descricao}</div>

			{anexos}
		</td>
	</tr>
	<tr>
		<td style="padding:28px 28px 24px;">
			<p style="margin:0;padding-top:16px;border-top:1px solid #eeeeee;font-size:12px;line-height:1.5;color:#999999;">
				Voc&ecirc; recebeu este e-mail porque est&aacute; ligado a este chamado{assinatura}. Para responder ou acompanhar, use o bot&atilde;o <b>Ver chamado</b>.
			</p>
			{rodape}
		</td>
	</tr>
</table>
</td></tr>
</table>';

SET @cabecalho_xkey = '<tr>
		<td align="center" style="padding:24px 28px 20px;border-radius:8px 8px 0 0;">
			<img src="{logo_url}" width="220" alt="XKEY Chamados" style="display:block;width:220px;max-width:100%;height:auto;border:0;">
		</td>
	</tr>
	<tr>
		<td style="background:#2f4a5c;padding:22px 28px;">
			<p style="margin:0;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#a9c3d6;">Abertura de chamado &middot; XKEY</p>
			<p style="margin:6px 0 0;font-size:24px;font-weight:bold;color:#ffffff;">Chamado #{chamado_id} registrado</p>
			<p style="margin:6px 0 0;font-size:14px;line-height:1.4;color:#dce6ee;">Um novo chamado foi aberto na central de atendimento da XKEY.</p>
		</td>
	</tr>';

SET @cabecalho_padrao = '<tr>
		<td style="background:#3c8dbc;padding:22px 28px;border-radius:8px 8px 0 0;">
			<p style="margin:0;font-size:13px;color:#dcebf5;">Novo chamado aberto</p>
			<p style="margin:4px 0 0;font-size:24px;font-weight:bold;color:#ffffff;">Chamado #{chamado_id}</p>
		</td>
	</tr>';

SET @rodape_backup = '<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;">
				<tr>
					<td style="padding:16px 18px;background:#fff4e5;border:1px solid #f5c27a;border-left:5px solid #f39c12;border-radius:4px;">
						<p style="margin:0 0 6px;font-size:15px;font-weight:bold;color:#8a5a00;">&#9888; Importante: fa&ccedil;a seu backup diariamente!</p>
						<p style="margin:0;font-size:13px;line-height:1.5;color:#6b4e16;">Em caso de problemas em seu sistema, ele ser&aacute; fundamental para a restaura&ccedil;&atilde;o imediata do funcionamento de sua empresa.</p>
					</td>
				</tr>
			</table>';

UPDATE email_padrao
SET assunto = 'Abertura de chamado #{chamado_id} | XKEY Chamados - {titulo}',
	mensagem = REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_xkey), '{assinatura}', ' na XKEY'), '{rodape}', @rodape_backup),
	data_atualizacao = NOW()
WHERE tipo_email = 5 AND empresa_id = 1;

UPDATE email_padrao
SET mensagem = REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_padrao), '{assinatura}', ''), '{rodape}', '')
WHERE tipo_email = 5 AND empresa_id <> 1 AND data_atualizacao IS NULL;
