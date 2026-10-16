<html>
<body>
	<h1>Olá <?php echo $dono ?>,</h1>
	<p>Existem alguns atendimentos concluídos que ainda não sabemos sua opinião.</p>
	<p>Nós adoraríamos saber mais sobre sua experiência em relação ao trabalho prestado pela nossa equipe.</p>
	<p>Poderia nos dar seu feedback? Prometemos que é rápido e ajudará a melhorar nossa qualidade e as experiências dos clientes no futuro.</p>
	<p>Sabemos que seu tempo é valioso e agradecemos sua colaboração.</p>
	
	<table width="100%" border="1">
    	<tr>
   			<td width="15%"><b>Atendimento</b></td>
   			<td width="30%"><b>Descrição</b></td>
   			<td width="15%"><b>Serviço</b></td>
   			<td width="25%"><b>Unidade</b></td>
   			<td width="15%"><b>Finalizado em</b></td>
   		</tr>
   		<?php foreach ($chamados as $value) { ?>
   			<tr>
   				<td align="center"><?php echo '<a href="'.base_url('auth/avaliacao_chamado/'.$value->dono_id.'/'.sha1($value->id.$value->dono_id)).'">'.$value->id.'</a>' ?></td>
   				<td><?php echo $value->titulo ?></td>
   				<td><?php echo $value->catalogo_desc ?></td>
   				<td><?php echo $value->unidade ?></td>
   				<td><?php echo date("d/m/Y H:i", strtotime($value->data_finalizacao)) ?></td>
   			</tr>
   		<?php } ?>
	</table>
</body>
</html>