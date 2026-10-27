-- Modelos dos e-mails de atualização do chamado no mesmo padrão visual do e-mail de abertura (tipo 5):
--   6 novo diálogo, 7 novo anexo, 8 reagendado, 9 escalado, 10 status atualizado (inclui o encerramento).
-- A empresa 1 (XKEY) recebe a versão com logo e identificação da XKEY.
-- As empresas que ainda usam o modelo padrão (nunca editado) recebem a versão neutra.
-- Modelos personalizados de outras empresas ficam como estão.
-- Ajuste o nome do banco se for diferente no servidor.
USE xkeysti1;

SET @modelo = '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f4f7;">
<tr><td align="center" style="padding:24px 12px;">
<table width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e3e7ec;border-radius:8px;font-family:Arial,Helvetica,sans-serif;color:#333333;">
	{cabecalho}
	<tr>
		<td style="padding:24px 28px 8px;">
			<p style="margin:0 0 6px;font-size:20px;font-weight:bold;line-height:1.3;color:#222222;">{titulo}</p>
			<p style="margin:0 0 16px;font-size:13px;color:#777777;">
				{unidade_desc} &middot; Status <b>{status}</b>
				&nbsp;<span style="display:inline-block;padding:2px 10px;border-radius:10px;background:{prioridade_cor};color:#ffffff;font-size:12px;font-weight:bold;">Prioridade {prioridade}</span>
			</p>
			{destaque}
			<table cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
				<tr><td style="background:#3c8dbc;border-radius:4px;">
					<a href="{link_chamado}" style="display:inline-block;padding:11px 22px;font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;">Ver chamado</a>
				</td></tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="padding:8px 28px 0;">
			{corpo}
		</td>
	</tr>
	<tr>
		<td style="padding:28px 28px 24px;">
			<p style="margin:0;padding-top:16px;border-top:1px solid #eeeeee;font-size:12px;line-height:1.5;color:#999999;">
				Voc&ecirc; recebeu este e-mail porque est&aacute; ligado a este chamado{assinatura}. Para responder ou acompanhar, use o bot&atilde;o <b>Ver chamado</b>.
			</p>
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
			<p style="margin:0;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#a9c3d6;">{evento_rotulo} &middot; XKEY</p>
			<p style="margin:6px 0 0;font-size:24px;font-weight:bold;color:#ffffff;">Chamado #{chamado_id} {evento_titulo}</p>
			<p style="margin:6px 0 0;font-size:14px;line-height:1.4;color:#dce6ee;">{evento_texto}</p>
		</td>
	</tr>';

SET @cabecalho_padrao = '<tr>
		<td style="background:#3c8dbc;padding:22px 28px;border-radius:8px 8px 0 0;">
			<p style="margin:0;font-size:13px;color:#dcebf5;">{evento_rotulo}</p>
			<p style="margin:4px 0 0;font-size:24px;font-weight:bold;color:#ffffff;">Chamado #{chamado_id} {evento_titulo}</p>
		</td>
	</tr>';

SET @detalhes = '<p style="margin:16px 0 8px;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#3c8dbc;">Detalhes</p>
			<table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
				<tr><td width="38%" style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Cliente</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{unidade_desc}</td></tr>
				<tr><td style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Produto / m&oacute;dulo</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{produto_desc} / {modulo_desc}</td></tr>
				<tr><td style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Categoria</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{categoria_desc}</td></tr>
				<tr><td style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Tipo de servi&ccedil;o</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{tipo_servico_desc}</td></tr>
				<tr><td style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">N&iacute;vel</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{nivel}</td></tr>
				<tr><td style="padding:7px 0;color:#777777;">Previs&atilde;o</td><td style="padding:7px 0;font-weight:bold;">{previsao}</td></tr>
			</table>';

SET @equipe = '<p style="margin:24px 0 8px;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#3c8dbc;">Equipe</p>
			<table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
				<tr><td width="38%" style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Solicitante</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{dono}</td></tr>
				<tr><td style="padding:7px 0;border-bottom:1px solid #eeeeee;color:#777777;">Respons&aacute;vel</td><td style="padding:7px 0;border-bottom:1px solid #eeeeee;font-weight:bold;">{responsavel}</td></tr>
				<tr><td style="padding:7px 0;color:#777777;">Programador</td><td style="padding:7px 0;font-weight:bold;">{desenvolvedor}</td></tr>
			</table>';

SET @descricao = '<p style="margin:24px 0 8px;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#3c8dbc;">Descri&ccedil;&atilde;o</p>
			<div style="padding:14px 16px;background:#f7f9fb;border:1px solid #eeeeee;border-radius:4px;font-size:14px;line-height:1.5;color:#444444;">{descricao}</div>';

SET @caixa_inicio = '<div style="margin:0 0 16px;padding:14px 16px;background:#f7f9fb;border:1px solid #e3e7ec;border-left:4px solid #3c8dbc;border-radius:4px;">';
SET @caixa_fim = '</div>';

-- 6: novo diálogo
SET @destaque_6 = CONCAT(@caixa_inicio, '<div style="font-size:12px;color:#888888;"><b style="color:#333333;">{dialogo_autor}</b> escreveu em {dialogo_data}{dialogo_interno}</div><div style="margin-top:6px;font-size:15px;line-height:1.5;color:#333333;">{dialogo_texto}</div>', @caixa_fim);
-- 7: novo anexo
SET @destaque_7 = CONCAT(@caixa_inicio, '<div style="font-size:12px;color:#888888;"><b style="color:#333333;">{anexo_autor}</b> anexou em {anexo_data}</div>{anexo_arquivos}{anexo_descricao}', @caixa_fim);
-- 8: reagendado
SET @destaque_8 = CONCAT(@caixa_inicio, '<div style="font-size:12px;color:#888888;text-transform:uppercase;letter-spacing:1px;">Nova previs&atilde;o de resolu&ccedil;&atilde;o</div><div style="margin-top:4px;font-size:20px;font-weight:bold;color:#2f4a5c;">{previsao}</div>', @caixa_fim);
-- 9: escalado
SET @destaque_9 = CONCAT(@caixa_inicio, '<div style="font-size:14px;line-height:1.5;color:#333333;">O chamado foi escalado e agora est&aacute; com <b>{responsavel}</b>.</div>', @caixa_fim, '{msg_adicional}');
-- 10: status atualizado / encerramento
SET @destaque_10 = CONCAT(@caixa_inicio, '<div style="font-size:12px;color:#888888;text-transform:uppercase;letter-spacing:1px;">Novo status</div><div style="margin-top:4px;font-size:20px;font-weight:bold;color:#2f4a5c;">{chamado_status}</div>{status_observacao}', @caixa_fim);

SET @corpo_conversa = '{timeline}';
SET @corpo_completo = CONCAT(@detalhes, @equipe);
SET @corpo_escalado = CONCAT(@detalhes, @equipe, @descricao);

-- Monta o modelo final de um tipo. Uso: REPLACE em cadeia com cabeçalho, destaque, corpo e textos do evento.
-- Empresa 1 (XKEY)
UPDATE email_padrao
SET assunto = 'Novo diálogo no chamado #{chamado_id} | XKEY Chamados - {titulo}',
	mensagem = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_xkey), '{destaque}', @destaque_6), '{corpo}', @corpo_conversa), '{assinatura}', ' na XKEY'),
		'{evento_rotulo}', 'Novo di&aacute;logo'), '{evento_titulo}', 'recebeu uma mensagem'), '{evento_texto}', 'H&aacute; uma nova mensagem em um chamado ligado a voc&ecirc;.'),
	data_atualizacao = NOW()
WHERE id > 0 AND tipo_email = 6 AND empresa_id = 1;

UPDATE email_padrao
SET assunto = 'Novo anexo no chamado #{chamado_id} | XKEY Chamados - {titulo}',
	mensagem = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_xkey), '{destaque}', @destaque_7), '{corpo}', @corpo_conversa), '{assinatura}', ' na XKEY'),
		'{evento_rotulo}', 'Novo anexo'), '{evento_titulo}', 'recebeu um anexo'), '{evento_texto}', 'Um arquivo foi adicionado a um chamado ligado a voc&ecirc;.'),
	data_atualizacao = NOW()
WHERE id > 0 AND tipo_email = 7 AND empresa_id = 1;

UPDATE email_padrao
SET assunto = 'Previsão do chamado #{chamado_id} atualizada | XKEY Chamados - {titulo}',
	mensagem = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_xkey), '{destaque}', @destaque_8), '{corpo}', @corpo_completo), '{assinatura}', ' na XKEY'),
		'{evento_rotulo}', 'Previs&atilde;o definida'), '{evento_titulo}', 'foi reagendado'), '{evento_texto}', 'A previs&atilde;o de resolu&ccedil;&atilde;o de um chamado ligado a voc&ecirc; foi definida.'),
	data_atualizacao = NOW()
WHERE id > 0 AND tipo_email = 8 AND empresa_id = 1;

UPDATE email_padrao
SET assunto = 'Chamado #{chamado_id} escalado | XKEY Chamados - {titulo}',
	mensagem = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_xkey), '{destaque}', @destaque_9), '{corpo}', @corpo_escalado), '{assinatura}', ' na XKEY'),
		'{evento_rotulo}', 'Chamado escalado'), '{evento_titulo}', 'foi escalado'), '{evento_texto}', 'Um chamado ligado a voc&ecirc; mudou de respons&aacute;vel ou de classifica&ccedil;&atilde;o.'),
	data_atualizacao = NOW()
WHERE id > 0 AND tipo_email = 9 AND empresa_id = 1;

UPDATE email_padrao
SET assunto = 'Chamado #{chamado_id}: {chamado_status} | XKEY Chamados - {titulo}',
	mensagem = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_xkey), '{destaque}', @destaque_10), '{corpo}', @corpo_conversa), '{assinatura}', ' na XKEY'),
		'{evento_rotulo}', 'Status atualizado'), '{evento_titulo}', 'foi atualizado'), '{evento_texto}', 'Houve uma mudan&ccedil;a de status em um chamado ligado a voc&ecirc;.'),
	data_atualizacao = NOW()
WHERE id > 0 AND tipo_email = 10 AND empresa_id = 1;

-- Demais empresas com o modelo padrão nunca editado: versão neutra (assunto mantido).
UPDATE email_padrao
SET mensagem = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_padrao), '{destaque}', @destaque_6), '{corpo}', @corpo_conversa), '{assinatura}', ''),
		'{evento_rotulo}', 'Novo di&aacute;logo'), '{evento_titulo}', 'recebeu uma mensagem')
WHERE id > 0 AND tipo_email = 6 AND empresa_id <> 1 AND data_atualizacao IS NULL;

UPDATE email_padrao
SET mensagem = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_padrao), '{destaque}', @destaque_7), '{corpo}', @corpo_conversa), '{assinatura}', ''),
		'{evento_rotulo}', 'Novo anexo'), '{evento_titulo}', 'recebeu um anexo')
WHERE id > 0 AND tipo_email = 7 AND empresa_id <> 1 AND data_atualizacao IS NULL;

UPDATE email_padrao
SET mensagem = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_padrao), '{destaque}', @destaque_8), '{corpo}', @corpo_completo), '{assinatura}', ''),
		'{evento_rotulo}', 'Previs&atilde;o definida'), '{evento_titulo}', 'foi reagendado')
WHERE id > 0 AND tipo_email = 8 AND empresa_id <> 1 AND data_atualizacao IS NULL;

UPDATE email_padrao
SET mensagem = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_padrao), '{destaque}', @destaque_9), '{corpo}', @corpo_escalado), '{assinatura}', ''),
		'{evento_rotulo}', 'Chamado escalado'), '{evento_titulo}', 'foi escalado')
WHERE id > 0 AND tipo_email = 9 AND empresa_id <> 1 AND data_atualizacao IS NULL;

UPDATE email_padrao
SET mensagem = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@modelo, '{cabecalho}', @cabecalho_padrao), '{destaque}', @destaque_10), '{corpo}', @corpo_conversa), '{assinatura}', ''),
		'{evento_rotulo}', 'Status atualizado'), '{evento_titulo}', 'foi atualizado')
WHERE id > 0 AND tipo_email = 10 AND empresa_id <> 1 AND data_atualizacao IS NULL;
