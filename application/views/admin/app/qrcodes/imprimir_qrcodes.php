<style type="text/css">
	* {font-family: "Arial"; font-size: 14px;}
	.container {
		width: 100vw;
	}
	table {border-collapse: separate;position:absolute;top:30%;}
	.page_break { page-break-before: always; }
</style>
<div class="container">
	<!-- <h1 style="text-align:center;">Impressão de QRCodes</h1> -->
	<table width="100%" border="0">
		<?php $i=0; foreach ($qrcodes_data as $value) { ?>
			<?php 
				if($i == 0) {
					$primeiro_book = $value->livro;
				}
				if($i != 0 && $value->livro == $primeiro_book && $value->kit == "S") {
					echo '</table><div class="page_break"></div><table width="100%" border="0">';
				}
			?>
			<?php if($i == 0 || $i % 3 == 0) { ?>
				<?php if($i != 0) { ?>
					</tr>
				<?php } ?>
				<tr>
			<?php } ?>
			<td align="center">
				<p><?php echo $value->codigo." - ".$value->titulo ?></p>
				<p><img src="<?php echo $value->qrcode ?>"></p>
			</td>
		<?php $i++; } ?>
		</tr>
	</table>
</div>

<script type="text/php">
    /*if ( isset($pdf) ) {
        $pdf->page_text(35, 18, "Página: {PAGE_NUM} de {PAGE_COUNT}", '', 6, array(0,0,0));
    }*/
</script> 