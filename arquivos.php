//SELECT  * FROM `xkeysti1`.`resposta_aluno` WHERE audio = 'S' AND data_resposta <= '2020-12-31 23:59:59'

<?php
require_once ('api/v3/conexao.php');

$conexao = new Conexao();
$mysqli = $conexao->getDatabase();
$qry = "SELECT * FROM `xkeysti1`.`resposta_aluno` WHERE audio = 'S' AND data_resposta <= '2020-12-31 23:59:59'";
$res = $mysqli->query($qry);

$i=0;
while($row = mysqli_fetch_assoc($res)):
    
    //if($i == 500)
    	//exit("end");

    $exist = file_exists($_SERVER['DOCUMENT_ROOT']."/v2/".$row['resposta']);

    if($exist) {

    	$new_dir_format = explode("/", $row['resposta']);
    	$new_dir = $_SERVER['DOCUMENT_ROOT'].'/v2/upload/respostas_bkp/'.$new_dir_format[2].'/'.$new_dir_format[3].'/'.$new_dir_format[4].'/';
    	
    	mkdir($new_dir, 0777, true);
    	$cmd = 'mv "'.$_SERVER['DOCUMENT_ROOT'].'/v2/'.$row['resposta'].'" "'.$new_dir.$new_dir_format[5].'"';

		exec($cmd, $output, $return_val);

		if ($return_val == 0) {
		   echo "success";
		} else {
		   echo "failed";
		} 

    	$i++;

    }

	//exit("<pre>".print_r($exist,true)."</pre>");
endwhile;	

exit("ok");
//exit("<pre>".print_r($res,true)."</pre>");