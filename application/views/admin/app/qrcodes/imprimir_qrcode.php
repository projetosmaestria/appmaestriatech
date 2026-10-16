<style type="text/css">
	* {font-family: "Arial"; font-size: 14px;}
	.container {
		width: 100vw;
	    display: flex;
	    flex-direction: row;
	    justify-content: center;
	    align-items: center
	}
	table {border-collapse: separate;position:absolute;top:30%;}
	.page_break { page-break-before: always; }
</style>
<div class="container">
	<!-- <h1 style="text-align:center;">Impressão de QRCode</h1> -->
	<table width="100%" border="0" align="center">
		<tr>
			<td>GERADO EM: <?php echo $qrcode_data->data ?></td>
			<td>LIVRO: <?php echo $qrcode_data->titulo ?></td>
		</tr>
		<tr>
			<td>ID: <?php echo $qrcode_data->id ?></td>
			<td>GERADO POR: <?php echo $qrcode_data->first_name." ".$qrcode_data->last_name ?></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><img src="<?php echo $qrcode_data->qrcode ?>"></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><?php echo $qrcode_data->codigo ?></td>
		</tr>
	</table>
</div>

<script type="text/php">
    /*if ( isset($pdf) ) {
        $pdf->page_text(35, 18, "Página: {PAGE_NUM} de {PAGE_COUNT}", '', 6, array(0,0,0));
    }*/
</script> 