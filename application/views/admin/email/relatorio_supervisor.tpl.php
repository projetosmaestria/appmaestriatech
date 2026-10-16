<html>
<head>
<!--<link type="text/css" rel="stylesheet" href="<?php echo $bootstrap_css; ?>">
<link type="text/css" rel="stylesheet" href="<?php echo $adminlte_css; ?>">-->
</head>
<body>
<div class="container">
	<div align="center">
		<table class="MsoNormalTable" style="width: 750pt;" width="750" cellspacing="0" cellpadding="0" border="0">
			<tbody style="">
				<tr style="">
					<td colspan="2" style="padding: 0cm 0cm 11.25pt;"><p class="MsoNormal" style="line-height: 21pt;"><span style="font-size: 13.5pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"><br style="">Relatório Semanal <small>Supervisor: <?php echo $supervisor ?></small> </span></p></td>
				</tr>

				<tr style="">
					<td style="padding: 0cm;"><p class="MsoNormal" style="">&nbsp;</p></td>
					<!-- <td style="padding: 0cm;"><p class="MsoNormal" style="">&nbsp;</p></td> -->
				</tr>
				<!--<tr style="">
					<td style="background: rgb(255, 246, 191) none repeat scroll 0% 0%; padding: 6pt 0cm 6pt 3.75pt;"><p class="MsoNormal" style=""><span style="font-size: 10pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(81, 71, 33);">&nbsp; <img style="width: 0.1666in; height: 0.1666in; display: none;" id="_x0000_i1025" saveddisplaymode="" width="16" height="16"><strong style=""><span style="font-family: &quot;arial&quot;, sans-serif;">Nível dos Alunos</span></strong> </span><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"></span></p></td>
					<td style="background: rgb(255, 246, 191) none repeat scroll 0% 0%; padding: 6pt 0cm 6pt 3.75pt;"><p class="MsoNormal" style=""><span style="font-size: 10pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(81, 71, 33);">&nbsp; <img style="width: 0.1666in; height: 0.1666in; display: none;" id="_x0000_i1025" saveddisplaymode="" width="16" height="16"><strong style=""><span style="font-family: &quot;arial&quot;, sans-serif;">Metas</span></strong> </span><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"></span></p></td>
				</tr>
				<tr style="">
					<td colspan="2" style="background: rgb(250, 250, 250) none repeat scroll 0% 0%; padding: 0cm;">
						<div style="" align="center">
							<table class="MsoNormalTable" style="width: 730pt;" width="730" cellspacing="0" cellpadding="0" border="0">
								<tbody style="">
									<tr style="">
										<td colspan="4" style="padding: 0cm;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">&nbsp;</span></p></td>
									</tr>
									<tr style="">
										<td style="padding: 0cm;"><p class="MsoNormal" style="">
											<ul class="chart-legend clearfix">
									            <li><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><b>(<?php echo $json_nivel_alunos['higher'] ?>)</b> Higher</span></li>
									            <li><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><b>(<?php echo $json_nivel_alunos['higher_medium'] ?>)</b> Higher Medium</span></li>
									            <li><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><b>(<?php echo $json_nivel_alunos['medium'] ?>)</b> Medium</span></li>
									            <li><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><b>(<?php echo $json_nivel_alunos['emerging'] ?>)</b> Emerging</span></li>
									            <li><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><b>(<?php echo $json_nivel_alunos['total'] - ($json_nivel_alunos['higher'] + $json_nivel_alunos['higher_medium'] + $json_nivel_alunos['medium'] + $json_nivel_alunos['emerging']) ?>)</b> Sem Nível</span></li>
									            <li><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><b>(<?php echo $json_nivel_alunos['total']?>)</b> Total</span></li>
									        </ul>
										</span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style="">
											<?php if (count($metas_alunos) > 0) { ?>
										    	<?php foreach ($metas_alunos as $meta) { ?>
								    				<ul class="chart-legend col-md-4">
								    					<?php $i=0; foreach ($meta as $k => $item) { ?>
											            	<li><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo ($i == 0)?'Data: ':'Contrato: '.$k ?>
											            		<?php echo ($i != 0) ? " - <b>(".$item."%)</b>" : $item ?>
										            		</span></li>
										    			<?php $i++; } ?>
										          	</ul>
										    	<?php } ?>
									    	<?php } ?>
										</span></p></td>
									</tr>
								</tbody>
							</table>
					        <div style="color: #31708f; background-color: #d9edf7; border-color: #bce8f1; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-radius: 4px;"> Os níveis dos alunos apresentados são com base em contratos que <strong>não</strong> estão com os seguintes status: CANCELADO, PRÉ-MATRICULA, RESGATE FINANCEIRO, RESGATE PEDAGÓGICO, CONCLUÍDO e ANULADO
					        </div>
						</div>
					</td>
				</tr>-->
				<tr style="">
					<td style="background: rgb(255, 246, 191) none repeat scroll 0% 0%; padding: 6pt 0cm 6pt 3.75pt;"><p class="MsoNormal" style=""><span style="font-size: 10pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(81, 71, 33);">&nbsp; <img style="width: 0.1666in; height: 0.1666in; display: none;" id="_x0000_i1025" saveddisplaymode="" width="16" height="16"><strong style=""><span style="font-family: &quot;arial&quot;, sans-serif;">Informações Contratos</span></strong> </span><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"></span></p></td>
				</tr>
				<tr style="">
					<td style="background: rgb(250, 250, 250) none repeat scroll 0% 0%; padding: 0cm;">
							<table class="MsoNormalTable" style="width: 45%; float: left; margin-left: 10px;" cellspacing="0" cellpadding="0" border="0">
								<tbody style="">
									<tr style="">
										<td colspan="4" style="padding: 0cm;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">&nbsp;</span></p></td>
									</tr>
									<tr style="">
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Contratos Válidos</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Ant.</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Atual</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
									</tr>
									<tr style="height: 18.75pt;">
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Ativos </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_ativos_anterior ?> </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_ativos_atual ?> </span></p></div></td>
									</tr>
									<tr>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Nunca Veio </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_nuncaveio_anterior ?> </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_nuncaveio_atual ?> </span></p></div></td>
									</tr>
									<tr>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Desistentes </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_desistentes_anterior ?> </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_desistentes_atual ?> </span></p></div></td>
									</tr>
									<tr>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Matriculas </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_matriculas_anterior ?> </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_matriculas_atual ?> </span></p></div></td>
									</tr>
									<tr>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><b>Total</b> </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_ativos_anterior+$count_contratos_nuncaveio_anterior+$count_contratos_desistentes_anterior+$count_contratos_matriculas_anterior ?> </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_ativos_atual+$count_contratos_nuncaveio_atual+$count_contratos_desistentes_atual+$count_contratos_matriculas_atual ?> </span></p></div></td>
									</tr>
								</tbody>
							</table>
							<table class="MsoNormalTable" style="width: 45%; float: right; margin-left: 10px; margin-right: 10px;" cellspacing="0" cellpadding="0" border="0">
								<tbody style="">
									<tr style="">
										<td colspan="4" style="padding: 0cm;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">&nbsp;</span></p></td>
									</tr>
									<tr style="">
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Contratos em Revisão</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Ant</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Atual</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
									</tr>
									<tr style="height: 18.75pt;">
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Resgate Pedagógico </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_rpedag_anterior ?> </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_rpedag_atual ?> </span></p></div></td>
									</tr>
									<tr>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Resgate Financeiro </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_rfinan_anterior ?> </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_rfinan_atual ?> </span></p></div></td>
									</tr>
									<tr>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Pré Matrícula </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_prematricula_anterior ?> </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_prematricula_atual ?> </span></p></div></td>
									</tr>
									<tr>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><b>Total</b> </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_rpedag_anterior+$count_contratos_rfinan_anterior+$count_contratos_prematricula_anterior ?> </span></p></div></td>
										<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $count_contratos_rpedag_atual+$count_contratos_rfinan_atual+$count_contratos_prematricula_atual ?> </span></p></div></td>
									</tr>
								</tbody>
							</table>
					</td>
				</tr>
			</tbody>
		</table>

		<table class="MsoNormalTable" style="width: 750pt;" width="750" cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr style="">
					<td style="padding: 0cm;"><p class="MsoNormal" style="">&nbsp;</p></td>
				</tr>
				<tr style="">
					<td style="background: rgb(255, 246, 191) none repeat scroll 0% 0%; padding: 6pt 0cm 6pt 3.75pt;"><p class="MsoNormal" style=""><span style="font-size: 10pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(81, 71, 33);">&nbsp; <img style="width: 0.1666in; height: 0.1666in; display: none;" id="_x0000_i1025" saveddisplaymode="" width="16" height="16">&nbsp; Informações <strong style=""><span style="font-family: &quot;arial&quot;, sans-serif;">Tutores</span></strong> </span><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"></span></p></td>
				</tr>
				<tr style="">
					<td style="background: rgb(250, 250, 250) none repeat scroll 0% 0%; padding: 0cm;">
						<div style="" align="center">
							<table class="MsoNormalTable" style="width: 730pt;" width="730" cellspacing="0" cellpadding="0" border="0">
								<tbody style="">
									<tr style="">
										<td colspan="4" style="padding: 0cm;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">&nbsp;</span></p></td>
									</tr>
									<tr style="">
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Tutor</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">E-mail</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Último Acesso</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Qtd Alunos Válidos</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Qtd Alunos Desistentes/Nunca Veio</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
									</tr>

									<?php if(count($detalhes_tutores) > 0): ?>
										<?php $qtd_alunos_validos_total = 0; ?>
										<?php $qtd_alunos_desistentes_total = 0; ?>
			        					<?php foreach($detalhes_tutores as $tutor): ?>
											<tr style="height: 18.75pt;">
												<td style="width: 150pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="150"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($tutor->nome_tutor, ENT_QUOTES, 'UTF-8'); ?> </span></p></div></td>
												<td style="width: 100pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="100"><p class="MsoNormal"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($tutor->email, ENT_QUOTES, 'UTF-8'); ?> </span></p></td>
												<td style="width: 56.25pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="50"><p class="MsoNormal"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo date('d/m/Y H:i:s', $tutor->last_login); ?> </span></p></td>
												<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style="text-align: center;" align="center"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $tutor->qtd_alunos_validos; ?> </span></p></td>
												<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style="text-align: center;" align="center"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $tutor->qtd_alunos_desistentes; ?> </span></p></td>
											</tr>
										<?php $qtd_alunos_validos_total += $tutor->qtd_alunos_validos; ?>
										<?php $qtd_alunos_desistentes_total += $tutor->qtd_alunos_desistentes; ?>
									<?php endforeach;?>
										<tr style="height: 18.75pt;">
											<td style="width: 150pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="150"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Totais</span></p></div></td>
											<td style="width: 100pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="100"><p class="MsoNormal"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
												<td style="width: 56.25pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="50"><p class="MsoNormal"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></td>
											<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style="text-align: center;" align="center"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $qtd_alunos_validos_total; ?> </span></p></td>
											<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style="text-align: center;" align="center"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $qtd_alunos_desistentes_total; ?> </span></p></td>
										</tr>
							        <?php else: ?>
							        	<tr style="height: 18.75pt;"><td colspan="5" align="center"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Não há registros para mostrar.</span></p></td></tr>
							        <?php endif; ?>
								</tbody>
							</table>
					        <div style="color: #31708f; background-color: #d9edf7; border-color: #bce8f1; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-radius: 4px;"> <strong>Total contratos sem tutoria:</strong> <?php echo $contratos_sem_tutor->num_rows() ?>
			        		</div>
						</div>
					</td>
				</tr>

				<tr style="">
					<td style="padding: 0cm;"><p class="MsoNormal" style="">&nbsp;</p></td>
				</tr>
				<tr style="">
					<td style="background: rgb(255, 246, 191) none repeat scroll 0% 0%; padding: 6pt 0cm 6pt 3.75pt;"><p class="MsoNormal" style=""><span style="font-size: 10pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(81, 71, 33);">&nbsp; <img style="width: 0.1666in; height: 0.1666in; display: none;" id="_x0000_i1025" saveddisplaymode="" width="16" height="16">&nbsp; Contratos Cancelados nos Últimos 7 dias </span><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"></span></p></td>
				</tr>
				<tr style="">
					<td style="background: rgb(250, 250, 250) none repeat scroll 0% 0%; padding: 0cm;">
						<div style="" align="center">
							<table class="MsoNormalTable" style="width: 730pt;" width="730" cellspacing="0" cellpadding="0" border="0">
								<tbody style="">
									<tr style="">
										<td colspan="4" style="padding: 0cm;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">&nbsp;</span></p></td>
									</tr>
									<tr style="">
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Código</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Filial</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Cliente</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Curso</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Status</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Data Pedido</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Data Matrícula</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Preço Produtos</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Preço Serviços</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Preço Total</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
									</tr>

									<?php $i = 0; if($cancelamentos->num_rows() > 0): ?>
			             				<?php foreach ($cancelamentos->result() as $cancel): ?>
											<tr style="height: 18.75pt;">
												<td style="width: 20pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="20"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $cancel->id; ?> </span></p></div></td>
												<td style="width: 56.25pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="75"><p class="MsoNormal"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($cancel->unidade, ENT_QUOTES, 'UTF-8'); ?> </span></p></td>
												<td style="width: 56.25pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="75"><p class="MsoNormal"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($cancel->first_name." ".$cancel->last_name, ENT_QUOTES, 'UTF-8'); ?> </span></p></td>
												<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($cancel->curso_desc, ENT_QUOTES, 'UTF-8'); ?> </span></p></td>
												<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($cancel->status_desc, ENT_QUOTES, 'UTF-8'); ?> </span></p></td>
												<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo date("d/m/Y" , strtotime($cancel->data_pedido)); ?> </span></p></td>
												<?php if($cancel->data_matricula == "0000-00-00") { ?>
							                      	<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">- </span></p></td>
							                    <?php } else { ?>
							                      	<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo date("d/m/Y" , strtotime($cancel->data_matricula)); ?> </span></p></td>
							                    <?php } ?>
							                    <td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo "R$".number_format($cancel->total_produtos, 2, ",", "."); ?> </span></p></td>
							                    <td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo "R$".number_format($cancel->total_servicos, 2, ",", "."); ?> </span></p></td>
							                    <td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo "R$".number_format($cancel->total_geral, 2, ",", "."); ?> </span></p></td>
											</tr>
										<?php $i++; endforeach;?>
							        <?php else: ?>
							        	<tr style="height: 18.75pt;"><td colspan="10" align="center"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Não há registros para mostrar.</span></p></td></tr>
							        <?php endif; ?>
								</tbody>
							</table>
							<div style="color: #31708f; background-color: #d9edf7; border-color: #bce8f1; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-radius: 4px;"> <strong>Total contratos cancelados nos últimos 7 dias:</strong> <?php echo $i ?>
			        		</div>
						</div>
					</td>
				</tr>

				<tr style="">
					<td style="padding: 0cm;"><p class="MsoNormal" style="">&nbsp;</p></td>
				</tr>
				<tr style="">
					<td style="background: rgb(255, 246, 191) none repeat scroll 0% 0%; padding: 6pt 0cm 6pt 3.75pt;"><p class="MsoNormal" style=""><span style="font-size: 10pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(81, 71, 33);">&nbsp; <img style="width: 0.1666in; height: 0.1666in; display: none;" id="_x0000_i1025" saveddisplaymode="" width="16" height="16">&nbsp; Tutorias nos Últimos 7 dias </span><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"></span></p></td>
				</tr>
				<tr style="">
					<td style="background: rgb(250, 250, 250) none repeat scroll 0% 0%; padding: 0cm;">
						<div style="" align="center">
							<table class="MsoNormalTable" style="width: 730pt;" width="730" cellspacing="0" cellpadding="0" border="0">
								<tbody style="">
									<tr style="">
										<td colspan="4" style="padding: 0cm;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">&nbsp;</span></p></td>
									</tr>

									<tr style="">
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Contrato</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Tutor</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Aluno</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Data/Hora Início</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Data/Hora Fim</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Meta Porcentagem</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Desempenho Geral</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
									</tr>

									<?php  $i = 0; if(count($tutorias_realizadas) > 0): ?>
			          					<?php foreach ($tutorias_realizadas as $tutoria): ?>
											<tr style="height: 18.75pt;">
												<td style="width: 30pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="30"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $tutoria->orc_id; ?> </span></p></div></td>
												<td style="width: 100pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="100"><p class="MsoNormal"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $tutoria->nome_tutor; ?> </span></p></td>
												<td style="width: 200pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="200"><p class="MsoNormal"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $tutoria->nome_aluno; ?> </span></p></td>
												<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo date("d/m/Y H:i", strtotime($tutoria->data_hora_inicio)); ?> </span></p></td>
												<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo date("d/m/Y H:i", strtotime($tutoria->data_hora_fim)); ?> </span></p></td>
												<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style="text-align: center;" align="center"><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $tutoria->meta_porcentagem; ?> </span></p></td>
												<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $tutoria->desempenho_geral; ?> </span></p></td>
											</tr>
										<?php $i++; endforeach;?>
							        <?php else: ?>
							        	<tr style="height: 18.75pt;"><td colspan="7" align="center"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Não há registros para mostrar.</span></p></td></tr>
							        <?php endif; ?>
								</tbody>
							</table>
							<div style="color: #31708f; background-color: #d9edf7; border-color: #bce8f1; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-radius: 4px;"> <strong>Total tutorias nos últimos 7 dias:</strong> <?php echo $i ?>
			        		</div>
						</div>
					</td>
				</tr>


				<tr style="">
					<td style="padding: 0cm;"><p class="MsoNormal" style="">&nbsp;</p></td>
				</tr>
				<tr style="">
					<td style="background: rgb(255, 246, 191) none repeat scroll 0% 0%; padding: 6pt 0cm 6pt 3.75pt;"><p class="MsoNormal" style=""><span style="font-size: 10pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(81, 71, 33);">&nbsp; <img style="width: 0.1666in; height: 0.1666in; display: none;" id="_x0000_i1025" saveddisplaymode="" width="16" height="16">&nbsp; Alunos Desistentes nos Últimos 15 dias </span><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"></span></p></td>
				</tr>
				<tr style="">
					<td style="background: rgb(250, 250, 250) none repeat scroll 0% 0%; padding: 0cm;">
						<div style="" align="center">
							<table class="MsoNormalTable" style="width: 730pt;" width="730" cellspacing="0" cellpadding="0" border="0">
								<tbody style="">
									<tr style="">
										<td colspan="4" style="padding: 0cm;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">&nbsp;</span></p></td>
									</tr>

									<tr style="">
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Contrato</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Aluno</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">E-mail</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Último Acesso</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Tutor</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
									</tr>

									<?php $i = 0; if($desistentes_antes->num_rows() > 0): ?>
			        					<?php foreach($desistentes_antes->result() as $aluno): ?>
											<tr style="height: 18.75pt;">
												<td style="width: 40pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="40"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $aluno->contrato_id; ?> </span></p></div></td>
												<td style="width: 180pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="180"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($aluno->nome_aluno, ENT_QUOTES, 'UTF-8'); ?> </span></p></td>
												<td style="width: 200pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="200"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($aluno->email, ENT_QUOTES, 'UTF-8'); ?> </span></p></td>
												<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo date('d/m/Y', strtotime($aluno->ultimo_acesso)); ?> </span></p></td>
												<td style="width: 180pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="180"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($aluno->nome_tutor, ENT_QUOTES, 'UTF-8'); ?> </span></p></td>
											</tr>
										<?php $i++; endforeach;?>
							        <?php else: ?>
							        	<tr style="height: 18.75pt;"><td colspan="4" align="center"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Não há registros para mostrar.</span></p></td></tr>
							        <?php endif; ?>
								</tbody>
							</table>
							<div style="color: #31708f; background-color: #d9edf7; border-color: #bce8f1; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-radius: 4px;"> <strong>Total alunos desistentes nos últimos 15 dias:</strong> <?php echo $i ?>
			        		</div>
						</div>
					</td>
				</tr>

				<tr style="">
					<td style="padding: 0cm;"><p class="MsoNormal" style="">&nbsp;</p></td>
				</tr>
				<tr style="">
					<td style="background: rgb(255, 246, 191) none repeat scroll 0% 0%; padding: 6pt 0cm 6pt 3.75pt;"><p class="MsoNormal" style=""><span style="font-size: 10pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(81, 71, 33);">&nbsp; <img style="width: 0.1666in; height: 0.1666in; display: none;" id="_x0000_i1025" saveddisplaymode="" width="16" height="16">&nbsp; Alunos Desistentes após 15 dias </span><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"></span></p></td>
				</tr>
				<tr style="">
					<td style="background: rgb(250, 250, 250) none repeat scroll 0% 0%; padding: 0cm;">
						<div style="" align="center">
							<table class="MsoNormalTable" style="width: 730pt;" width="730" cellspacing="0" cellpadding="0" border="0">
								<tbody style="">
									<tr style="">
										<td colspan="4" style="padding: 0cm;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">&nbsp;</span></p></td>
									</tr>

									<tr style="">
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Contrato</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Aluno</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">E-mail</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Último Acesso</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
										<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Tutor</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
									</tr>

									<?php $i = 0; if($desistentes_apos->num_rows() > 0): ?>
			        					<?php foreach($desistentes_apos->result() as $aluno) : ?>
											<tr style="height: 18.75pt;">
												<td style="width: 40pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="40"><div style=""><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $aluno->contrato_id; ?> </span></p></div></td>
												<td style="width: 180pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="180"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($aluno->nome_aluno, ENT_QUOTES, 'UTF-8'); ?> </span></p></td>
												<td style="width: 200pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="200"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($aluno->email, ENT_QUOTES, 'UTF-8'); ?> </span></p></td>
												<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo date('d/m/Y', strtotime($aluno->ultimo_acesso)); ?> </span></p></td>
												<td style="width: 180pt; border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;" width="180"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo htmlspecialchars($aluno->nome_tutor, ENT_QUOTES, 'UTF-8'); ?> </span></p></td>
											</tr>
										<?php $i++; endforeach;?>
							        <?php else: ?>
							        	<tr style="height: 18.75pt;"><td colspan="4" align="center"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Não há registros para mostrar.</span></p></td></tr>
							        <?php endif; ?>
								</tbody>
							</table>
							<div style="color: #31708f; background-color: #d9edf7; border-color: #bce8f1; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-radius: 4px;"> <strong>Total alunos desistentes após 15 dias:</strong> <?php echo $i ?>
			        		</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

</div>
</body>
</html>

<!--<script type="text/php">
    if ( isset($pdf) ) {
        $pdf->page_text(35, 18, "Página: {PAGE_NUM} de {PAGE_COUNT}", '', 6, array(0,0,0));
    }
</script>-->