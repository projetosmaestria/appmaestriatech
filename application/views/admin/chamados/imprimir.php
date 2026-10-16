<style type="text/css">
	* {
		font-family: "Arial", sans-serif;
		font-size: 14px;
	}

	body {
		margin: 20px;
		background: #fff;
	}

	small {
		font-size: 10px;
	}

	h1 {
		font-size: 24px;
		font-weight: bold;
		margin: 0 0 10px 0;
		color: #333;
	}

	h2 {
		font-size: 18px;
		font-weight: bold;
		margin: 20px 0 10px 0;
		color: #444;
		border-bottom: 2px solid #4CAF50;
		padding-bottom: 5px;
		page-break-after: avoid;
		break-after: avoid;
	}

	h3 {
		font-size: 16px;
		font-weight: bold;
		margin: 10px 0;
		color: #555;
		page-break-after: avoid;
		break-after: avoid;
	}

	.container {
		width: 100% !important;
		max-width: 1200px;
		margin: 0 auto;
	}

	p {
		margin-top: 10px;
		margin-bottom: 10px;
		line-height: 1.5;
	}

	.info-table {
		width: 100%;
		border-collapse: collapse;
		margin-bottom: 20px;
		background: #fff;
		page-break-inside: avoid;
		break-inside: avoid;
	}

	.info-table td {
		padding: 8px 10px;
		border: 1px solid #ddd;
		vertical-align: top;
	}

	.info-table td:first-child {
		background-color: #f8f9fa;
		font-weight: bold;
		width: 18%;
	}

	.info-table td:nth-child(2) {
		width: 32%;
	}

	.info-table td:nth-child(3) {
		background-color: #f8f9fa;
		font-weight: bold;
		width: 18%;
	}

	.info-table td:nth-child(4) {
		width: 32%;
	}

	.descricao-box {
		border: 1px solid #ddd;
		border-radius: 4px;
		padding: 15px;
		margin-bottom: 20px;
		background: #fafafa;
		page-break-inside: avoid;
		break-inside: avoid;
	}

	.descricao-box h3 {
		margin-top: 0;
		color: #4CAF50;
		border-left: 3px solid #4CAF50;
		padding-left: 10px;
	}

	.descricao-conteudo {
		line-height: 1.6;
		padding: 10px;
		background: #fff;
		border-radius: 4px;
	}

	.checklist-box {
		border: 1px solid #ddd;
		border-radius: 8px;
		margin-bottom: 25px;
		page-break-inside: avoid;
		break-inside: avoid;
		background: #fff;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	}

	.checklist-header {
		background: #4CAF50;
		color: white;
		padding: 12px 15px;
		border-radius: 6px 6px 0 0;
	}

	.checklist-header h4 {
		margin: 0;
		font-size: 16px;
		color: white;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.checklist-body {
		padding: 15px;
	}

	.checklist-descricao {
		background: #f9f9f9;
		padding: 10px 12px;
		margin-bottom: 15px;
		border-left: 3px solid #4CAF50;
		border-radius: 4px;
	}

	.badge {
		display: inline-block;
		padding: 3px 10px;
		font-size: 12px;
		font-weight: bold;
		border-radius: 20px;
		background: #fff;
		color: #4CAF50;
	}

	.checklist-table {
		width: 100%;
		border-collapse: collapse;
		margin-top: 10px;
	}

	.checklist-table th {
		background-color: #f0f0f0;
		padding: 10px;
		border: 1px solid #ddd;
		font-weight: bold;
		text-align: left;
		font-size: 13px;
	}

	.checklist-table td {
		padding: 8px 10px;
		border: 1px solid #ddd;
		vertical-align: top;
	}

	.checklist-table tr.concluida {
		background-color: #e8f5e9;
	}

	.checklist-table tr.atrasada {
		background-color: #ffebee;
	}

	.checklist-table tr.pendente {
		background-color: #fff3e0;
	}

	.status-concluido {
		color: #2e7d32;
		font-weight: bold;
	}

	.status-atrasado {
		color: #c62828;
		font-weight: bold;
	}

	.status-pendente {
		color: #f57c00;
		font-weight: bold;
	}

	.historico-container {
		margin-bottom: 20px;
	}

	.historico-item {
		margin-bottom: 15px;
		padding: 12px;
		border-left: 4px solid #4CAF50;
		background: #f9f9f9;
		border-radius: 4px;
		page-break-inside: avoid;
		break-inside: avoid;
	}

	.historico-data {
		font-weight: bold;
		color: #2c3e50;
		margin-bottom: 5px;
		font-size: 12px;
	}

	.historico-usuario {
		color: #4CAF50;
		font-weight: bold;
		margin-bottom: 8px;
	}

	.historico-conteudo {
		line-height: 1.5;
		color: #333;
		padding-left: 10px;
		border-left: 2px solid #ddd;
	}

	.historico-conteudo p {
		margin: 0 0 5px 0;
	}

	.apontamento-container {
		margin-bottom: 20px;
		page-break-inside: avoid;
		break-inside: avoid;
	}

	.apontamento-table {
		width: 100%;
		border-collapse: collapse;
		margin-bottom: 20px;
	}

	.apontamento-table th {
		background-color: #f0f0f0;
		padding: 10px;
		border: 1px solid #ddd;
		font-weight: bold;
		text-align: center;
	}

	.apontamento-table td {
		padding: 8px;
		border: 1px solid #ddd;
		text-align: center;
	}

	.apontamento-table td:last-child {
		text-align: left;
	}

	.resumo-horas {
		background: #e8f5e9;
		padding: 10px 15px;
		border-radius: 4px;
		margin-top: 10px;
		text-align: right;
		font-weight: bold;
	}

	.assinatura {
		margin-top: 40px;
		padding-top: 20px;
		border-top: 1px solid #ddd;
		page-break-inside: avoid;
		break-inside: avoid;
	}

	.checklists-container {
		margin-bottom: 20px;
	}

	.checklist-item {
		border: 2px solid #4CAF50;
		border-radius: 8px;
		margin-bottom: 20px;
		overflow: hidden;
		page-break-inside: avoid;
		break-inside: avoid;
	}

	.checklist-header-main {
		background: #4CAF50;
		color: white;
		padding: 10px 15px;
		font-weight: bold;
	}

	.checklist-content {
		padding: 15px;
	}

	.etapas-table {
		width: 100%;
		border-collapse: collapse;
		margin-top: 10px;
	}

	.etapas-table th {
		background: #f0f0f0;
		border: 1px solid #ddd;
		padding: 10px;
		text-align: left;
		font-size: 12px;
	}

	.etapas-table td {
		border: 1px solid #ddd;
		padding: 8px;
		vertical-align: top;
		font-size: 12px;
	}

	.etapa-concluida {
		background-color: #d4edda;
	}

	.etapa-atrasada {
		background-color: #f8d7da;
	}

	.etapa-pendente {
		background-color: #fff3cd;
	}

	.etapa-descricao {
		font-size: 11px;
		color: #666;
		margin-top: 4px;
	}

	.empty-message {
		padding: 15px;
		background: #f9f9f9;
		border-left: 3px solid #999;
		border-radius: 4px;
		margin-bottom: 20px;
	}

	@media print {
		body {
			margin: 0;
			padding: 10px;
		}

		.checklist-item {
			page-break-inside: avoid;
			break-inside: avoid;
		}

		.historico-item {
			page-break-inside: avoid;
			break-inside: avoid;
		}

		.apontamento-container {
			page-break-inside: avoid;
			break-inside: avoid;
		}

		.assinatura {
			page-break-inside: avoid;
			break-inside: avoid;
		}

		.etapa-concluida {
			background-color: #d4edda !important;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}

		.etapa-atrasada {
			background-color: #f8d7da !important;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}

		.etapa-pendente {
			background-color: #fff3cd !important;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}
	}
</style>

<div class="container">
	<!-- Cabeçalho -->
	<table style="width:100%; margin-bottom: 20px;">
		<td align="left">
			<h1><?php echo htmlspecialchars($chamado->cliente, ENT_QUOTES, 'UTF-8') ?></h1>
			<strong>Protocolo:</strong> #<?php echo $chamado->id ?>
		</td>
		<td align="right">
			<strong>Data de Abertura:</strong><br>
			<?php echo date("d/m/Y H:i:s", strtotime($chamado->created)) ?>
		</td>
		</tr>
	</table>

	<!-- Título do Chamado -->
	<h2><?php echo htmlspecialchars($chamado->titulo, ENT_QUOTES, 'UTF-8') ?></h2>

	<!-- Informações do Chamado -->
	<table class="info-table">
		<tr>
			<td><strong>Situação:</strong></td>
			<td><?php echo htmlspecialchars($chamado->status, ENT_QUOTES, 'UTF-8') ?></td>
			<td><strong>Início:</strong></td>
			<td><?php echo ($chamado->data_inicio) ? date("d/m/Y H:i:s", strtotime($chamado->data_inicio)) : '-' ?></td>
		</tr>
		<tr>
			<td><strong>Prioridade:</strong></td>
			<td><?php echo htmlspecialchars($chamado->prioridade, ENT_QUOTES, 'UTF-8') ?></td>
			<td><strong>Data Prevista:</strong></td>
			<td><?php echo ($chamado->data_prazo) ? date("d/m/Y H:i:s", strtotime($chamado->data_prazo)) : '-' ?></td>
		</tr>
		<tr>
			<td><strong>Responsável:</strong></td>
			<td><?php echo htmlspecialchars($chamado->first_name . " " . $chamado->last_name, ENT_QUOTES, 'UTF-8') ?></td>
			<td><strong>Dono:</strong></td>
			<td><?php echo htmlspecialchars($chamado->first_name_dono . " " . $chamado->last_name_dono, ENT_QUOTES, 'UTF-8') ?></td>
		</tr>
		<tr>
			<td><strong>Produto:</strong></td>
			<td><?php echo htmlspecialchars($chamado->produto_desc, ENT_QUOTES, 'UTF-8') ?></td>
			<td><strong>Módulo:</strong></td>
			<td><?php echo htmlspecialchars($chamado->modulo_desc, ENT_QUOTES, 'UTF-8') ?></td>
		</tr>
		<tr>
			<td><strong>Categoria:</strong></td>
			<td><?php echo htmlspecialchars($chamado->categoria_desc, ENT_QUOTES, 'UTF-8') ?></td>
			<td><strong>Serviço:</strong></td>
			<td><?php echo htmlspecialchars($chamado->catalogo_desc, ENT_QUOTES, 'UTF-8') ?></td>
		</tr>
		<tr>
			<td><strong>Nível:</strong></td>
			<td><?php echo htmlspecialchars($chamado->nivel_desc, ENT_QUOTES, 'UTF-8') ?></td>
			<td><strong>Tempo Gasto:</strong></td>
			<td><?php echo $esforco ?></td>
		</tr>
	</table>

	<!-- Descrição -->
	<div class="descricao-box">
		<h3>Descrição</h3>
		<div class="descricao-conteudo">
			<?php echo nl2br(htmlspecialchars($chamado->descricao, ENT_QUOTES, 'UTF-8')); ?>
		</div>
	</div>

	<!-- CHECKLISTS ASSOCIADOS -->
	<h2>Checklists Associados</h2>

	<div class="checklists-container">
		<?php
		// Verificar se existem checklists
		if (isset($checklists_associados) && is_array($checklists_associados) && count($checklists_associados) > 0):
		?>
			<?php foreach ($checklists_associados as $checklist): ?>
				<div class="checklist-item">
					<div class="checklist-header-main">
						Checklist: <?php echo htmlspecialchars($checklist->nome, ENT_QUOTES, 'UTF-8'); ?>
						<span style="float: right; background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 12px; font-size: 12px;">
							<?php echo isset($checklist->etapas) ? count($checklist->etapas) : 0; ?> etapas
						</span>
					</div>
					<div class="checklist-content">
						<?php if (!empty($checklist->descricao)): ?>
							<div style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-left: 3px solid #4CAF50; border-radius: 4px;">
								<strong>Descrição:</strong><br>
								<?php echo nl2br(htmlspecialchars($checklist->descricao, ENT_QUOTES, 'UTF-8')); ?>
							</div>
						<?php endif; ?>

						<?php if ($checklist->data_inicio): ?>
							<p style="margin: 0 0 10px 0;"><strong>Data de início:</strong> <?php echo date('d/m/Y', strtotime($checklist->data_inicio)); ?></p>
						<?php endif; ?>

						<?php if (isset($checklist->etapas) && !empty($checklist->etapas)): ?>
							<table class="etapas-table">
								<thead>
									<tr>
										<th width="5%">#</th>
										<th width="35%">Etapa</th>
										<th width="15%">Data Início</th>
										<th width="15%">Data Prazo</th>
										<th width="20%">Responsável</th>
										<th width="10%">Status</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($checklist->etapas as $index => $etapa):
										$row_class = '';
										$status_text = '';

										if ($etapa->concluida == 1) {
											$row_class = 'etapa-concluida';
											$status_text = 'Concluído';
										} elseif ($etapa->data_prazo && $etapa->data_prazo < date('Y-m-d')) {
											$row_class = 'etapa-atrasada';
											$status_text = 'Atrasado';
										} else {
											$row_class = 'etapa-pendente';
											$status_text = 'Pendente';
										}

										$responsavel_nome = '-';
										if ($etapa->responsavel) {
											$CI = &get_instance();
											$resp_query = $CI->db->select('first_name, last_name')->from('users')->where('id', $etapa->responsavel)->get();
											if ($resp_query->num_rows() > 0) {
												$resp = $resp_query->row();
												$responsavel_nome = htmlspecialchars($resp->first_name . ' ' . $resp->last_name, ENT_QUOTES, 'UTF-8');
											}
										}
									?>
										<tr class="<?php echo $row_class; ?>">
											<td style="text-align: center;"><?php echo $index + 1; ?></td>
											<td>
												<strong><?php echo htmlspecialchars($etapa->nome, ENT_QUOTES, 'UTF-8'); ?></strong>
												<?php if (!empty($etapa->descricao)): ?>
													<div class="etapa-descricao">
														<?php echo nl2br(htmlspecialchars($etapa->descricao, ENT_QUOTES, 'UTF-8')); ?>
													</div>
												<?php endif; ?>
											</td>
											<td><?php echo ($etapa->data_inicio) ? date('d/m/Y', strtotime($etapa->data_inicio)) : '-'; ?></td>
											<td><?php echo ($etapa->data_prazo) ? date('d/m/Y', strtotime($etapa->data_prazo)) : '-'; ?></td>
											<td><?php echo $responsavel_nome; ?></td>
											<td><strong><?php echo $status_text; ?></strong></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php else: ?>
							<div class="empty-message" style="border-left-color: #ffc107; margin-bottom: 0;">
								ℹ️ Nenhuma etapa associada a este checklist.
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<div class="empty-message">
				ℹ️ Nenhum checklist associado a este chamado.
			</div>
		<?php endif; ?>
	</div>

	<!-- Histórico de Diálogos e Anexos -->
	<h2>Histórico do Chamado</h2>

	<div class="historico-container">
		<?php if (count($timeline) > 0): ?>
			<?php foreach ($timeline as $dialog): ?>
				<div class="historico-item">
					<div class="historico-data">
						Data: <?php echo date("d/m/Y H:i:s", strtotime($dialog['created'])) ?>
					</div>
					<div class="historico-usuario">
						Usuário: <?php echo htmlspecialchars($dialog['first_name'] . " " . $dialog['last_name'], ENT_QUOTES, 'UTF-8') ?>
						<small style="color: #666;">(<?php echo (@$dialog['tipo'] == "dialogo") ? "diálogo" : "anexo" ?>)</small>
					</div>
					<div class="historico-conteudo">
						<?php if (@$dialog['anexo']): ?>
							📎 Anexo: <?php echo htmlspecialchars($dialog['anexo'], ENT_QUOTES, 'UTF-8') ?>
						<?php else: ?>
							<?php
							$conteudo = nl2br(htmlspecialchars($dialog['descricao'], ENT_QUOTES, 'UTF-8'));
							echo $conteudo;
							?>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<div class="empty-message">
				ℹ️ Não há histórico no chamado.
			</div>
		<?php endif; ?>
	</div>

	<!-- Apontamentos -->
	<h2>Apontamentos de Horas</h2>

	<div class="apontamento-container">
		<?php if ($apontamentos->num_rows() > 0): ?>
			<table class="apontamento-table">
				<thead>
					<tr>
						<th width="15%">Data</th>
						<th width="10%">Esforço</th>
						<th width="10%">Início</th>
						<th width="10%">Término</th>
						<th width="55%">Observação</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($apontamentos->result() as $apont): ?>
						<tr>
							<td><?php echo date("d/m/Y", strtotime($apont->data)) ?></td>
							<td><strong><?php echo date("H:i:s", strtotime($apont->esforco)) ?></strong></td>
							<td><?php echo date("H:i", strtotime($apont->hora_inicio)) ?></td>
							<td><?php echo date("H:i", strtotime($apont->hora_termino)) ?></td>
							<td style="text-align: left;"><?php echo ($apont->observacao) ? nl2br(htmlspecialchars($apont->observacao, ENT_QUOTES, 'UTF-8')) : '-' ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="resumo-horas">
				Total de horas apontadas: <strong><?php echo $esforco ?></strong>
			</div>
		<?php else: ?>
			<div class="empty-message">
				ℹ️ Não há apontamentos registrados para este chamado.
			</div>
		<?php endif; ?>
	</div>

	<!-- Assinatura -->
	<div class="assinatura">
		<p>_______________________________________________</p>
		<p><strong><?php echo htmlspecialchars($chamado->cliente, ENT_QUOTES, 'UTF-8') ?></strong></p>
		<small>Documento gerado automaticamente em <?php echo date("d/m/Y H:i:s") ?></small>
	</div>
</div>

<script type="text/php">
	if ( isset($pdf) ) {
		$pdf->page_text(35, 18, "Página: {PAGE_NUM} de {PAGE_COUNT}", '', 8, array(0,0,0));
	}
</script>