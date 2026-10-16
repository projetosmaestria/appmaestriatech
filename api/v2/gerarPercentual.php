<?php 
	echo "<pre>".print_r($argv,true)."</pre>";
	require_once ('conexao.php');
	$conexao = new Conexao();
	$mysqli = $conexao->getDatabase();
	
	$qry_token = 'SELECT ad.id_aluno '
				. 'FROM users u '
				. 'INNER JOIN aluno_device ad '
				. 'ON ad.id_aluno = u.id '
				. 'WHERE ad.device_id = "'.$argv[2].'"'
				. 'AND ad.token = "'.$argv[1].'"';
	echo $qry_token;
	$qry_result = $mysqli->query($qry_token);
	$qry_rows = mysqli_num_rows($qry_result);

	if($qry_rows > 0):
		$dados = mysqli_fetch_assoc($qry_result);
	
		$qry_exec_unidade = 'SELECT livro FROM unidade_livro WHERE id = '.$argv[3];
		$qry_result_unidade = $mysqli->query($qry_exec_unidade);
		$qry_rows_unidade = mysqli_num_rows($qry_result_unidade);
		
		if($qry_rows_unidade == 1):
			$row_unidade = mysqli_fetch_assoc($qry_result_unidade);
			
			$qry_exec_percent = 'SELECT ((COUNT(*) * 100) / (SELECT COUNT(*) FROM pergunta_exercicio pe INNER JOIN exercicios e ON pe.exercicio = e.id INNER JOIN unidade_livro ul ON ul.id = e.unidade WHERE e.livro = "'.$row_unidade["livro"].'" AND ul.tipo = "O")) AS percentual '
						. 'FROM resposta_aluno ra '
						. 'INNER JOIN pergunta_exercicio pe '
						. 'ON ra.pergunta = pe.id '
						. 'INNER JOIN exercicios e '
						. 'ON pe.exercicio = e.id '
						. 'INNER JOIN unidade_livro ul ON ul.id = e.unidade '
						. 'INNER JOIN aluno_device ad '
						. 'ON ad.id_aluno = ra.aluno '
						. 'WHERE ad.device_id = "'.$argv[2].'" '
						. 'AND ad.token = "'.$argv[1].'" '
						. 'AND e.livro = "'.$row_unidade["livro"].'" AND ul.tipo= "O"';
			echo $qry_exec_percent;
			$qry_result_percent = $mysqli->query($qry_exec_percent);
			$qry_rows_percent = mysqli_num_rows($qry_result_percent);

			if($qry_rows_percent == 1):
				$row_percent = mysqli_fetch_assoc($qry_result_percent);			
				$sql_update = "UPDATE aluno_livro SET "
							. " percentual = '".round($row_percent['percentual'])."'"
							. " WHERE aluno = ".$dados["id_aluno"]." "
							. " AND livro = ".$row_unidade["livro"];
				echo $sql_update;
				$mysqli->query($sql_update);
			endif;
		endif;
	endif;
	
	exit;
?>