<html>
<body>
	<div class="container">
		<div align="center">
			<table class="MsoNormalTable" style="width: 750pt;" width="750" cellspacing="0" cellpadding="0" border="0">
				<tbody>					
					<?php if ($apontamentos->num_rows() > 0) { $i = 1; ?>
						<?php $last_user = 0; foreach ($apontamentos->result() as $apont) { ?>
							<?php if($last_user != $apont->user_id) { ?>
								<?php if($last_user != 0) { ?>
													</tbody>
												</table>
												<div style="color: #31708f; background-color: #d9edf7; border-color: #bce8f1; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-radius: 4px;"> <strong>Total de Esforço:</strong> <?php echo $total_esforco[$last_user] ?></div>
											</div>
										</td>
									</tr>
									<?php gerar_acumulados($last_user, $datas_acumulado, $total_acumulado); ?>
								<?php } ?>
								<tr style="">
									<td colspan="2" style="padding: 0cm 0cm 11.25pt;"><p class="MsoNormal" style="line-height: 21pt;"><span style="font-size: 13.5pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"><br style="">Responsável: <small><?php echo $apont->nome ?></small> </span></p></td>
								</tr>
								<tr style="">
									<td style="background: rgb(255, 246, 191) none repeat scroll 0% 0%; padding: 6pt 0cm 6pt 3.75pt;"><p class="MsoNormal" style=""><span style="font-size: 10pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(81, 71, 33);">&nbsp; <img style="width: 0.1666in; height: 0.1666in; display: none;" id="_x0000_i1025" saveddisplaymode="" width="16" height="16"><strong style=""><span style="font-family: &quot;arial&quot;, sans-serif;">Apontamentos</span><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"></span></p></td>
								</tr>
								<tr style="">
									<td style="background: rgb(250, 250, 250) none repeat scroll 0% 0%; padding: 0cm;">
										<div style="" align="center">
											<table class="MsoNormalTable" style="width: 750pt;" width="750" cellspacing="0" cellpadding="0" border="0">
												<tbody style="">
													<tr style="">
														<td colspan="4" style="padding: 0cm;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">&nbsp;</span></p></td>
													</tr>
													<tr>
														<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Data</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
														<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Chamado</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
														<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Descrição</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
														<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Filial</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
														<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Status</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
														<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">H. Início</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
														<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">H. Fim</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
														<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">Esforço</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>
													</tr>
								<?php $last_user = $apont->user_id; ?>
							<?php } ?>
													<tr style="height: 18.75pt;">
														<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo date("d/m/Y", strtotime($apont->data)) ?></span></p>
														</td>
														<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $apont->chamado_id ?></span></p>
														</td>
														<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $apont->titulo ?></span></p>
														</td>
														<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $apont->unidade ?></span></p>
														</td>
														<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo $apont->descricao ?></span></p>
														</td>
														<td align="center" style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo date("H:i", strtotime($apont->hora_inicio)) ?></span></p>
														</td>
														<td align="center" style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo date("H:i", strtotime($apont->hora_termino)) ?></span></p>
														</td>
														<td align="center" style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"><?php echo date("H:i", strtotime($apont->esforco)) ?></span></p>
														</td>
													</tr>
							<?php if($apontamentos->num_rows() == $i) { ?>
													</tbody>
												</table>
												<div style="color: #31708f; background-color: #d9edf7; border-color: #bce8f1; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-top-color: transparent; border-right-color: transparent; border-bottom-color: transparent; border-left-color: transparent; border-radius: 4px;"> <strong>Total de Esforço:</strong> <?php echo $total_esforco[$apont->user_id] ?></div>
											</div>
										</td>
									</tr>
								<?php gerar_acumulados($apont->user_id, $datas_acumulado, $total_acumulado); ?>
							<?php } ?>
							<?php $last_user = $apont->user_id; ?>
							<?php $i++; ?>
						<?php } ?>
					<?php } ?>

				</tbody>
			</table>
		</div>
	</div>
</body>

</html>
<?php
	function gerar_acumulados($user, $datas_acumulado, $total_acumulado) {
		echo '<tr style="">
			<td style="padding: 0cm;"><p class="MsoNormal" style="">&nbsp;</p></td>
		</tr>';
		echo '<tr style="">
			<td style="background: rgb(255, 246, 191) none repeat scroll 0% 0%; padding: 6pt 0cm 6pt 3.75pt;"><p class="MsoNormal" style=""><span style="font-size: 10pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(81, 71, 33);">&nbsp; <img style="width: 0.1666in; height: 0.1666in; display: none;" id="_x0000_i1025" saveddisplaymode="" width="16" height="16"><strong style=""><span style="font-family: &quot;arial&quot;, sans-serif;">Acumulado do Mês</span><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(83, 83, 83);"></span></p></td>
		</tr>';
		echo '<tr style="">
			<td colspan="4" style="padding: 0cm;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">&nbsp;</span></p></td>
		</tr>';
		echo '<tr style="">
			<td style="background: rgb(250, 250, 250) none repeat scroll 0% 0%; padding: 0cm;">
				<div style="" align="center">';
		echo '<table class="MsoNormalTable" style="width: 750pt;" width="750" cellspacing="0" cellpadding="0" border="0"><tr>';
		foreach ($datas_acumulado as $dt) { 
			echo '<td style="padding: 0cm;"><p class="MsoNormal" style=""><b style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">'.$dt['extenso'].'</span></b><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);"></span></p></td>';
		}
		echo "</tr><tr>";
		foreach ($datas_acumulado as $dt) { 
			echo '<td style="border-color: currentcolor currentcolor rgb(222, 222, 222); border-style: none none solid; border-width: medium medium 1pt; border-image: none 100% / 1 / 0 stretch; padding: 3.75pt 7.5pt 3.75pt 0cm; height: 18.75pt;"><p class="MsoNormal" style=""><span style="font-size: 9pt; font-family: &quot;arial&quot;, sans-serif; color: rgb(52, 73, 94);">'.$total_acumulado[$dt['code']][$user].'</span></p></td>';
		}
		echo "</tr></table></div></td></tr>";
		echo '<tr style="">
			<td style="padding: 0cm;"><p class="MsoNormal" style="">&nbsp;<hr></p></td>
		</tr>';
	}
?>