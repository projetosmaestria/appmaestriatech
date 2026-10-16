<?php
    exit;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    ini_set('display_errors', true);
    error_reporting(1);

    //require("const.php");
    require("api/v3/conexao.php");

    $conn = new Conexao();
    $conn = $conn->getDatabase();

    $qry = 'SELECT id FROM exercicios WHERE livro IN (33,34,35)';
    $qry_result = $conn->query($qry);
    $ids_exercicios = [];
    while($row = mysqli_fetch_assoc($qry_result)) {
        $ids_exercicios[] = $row['id'];
    }

    $qry2 = 'SELECT * FROM pergunta_exercicio WHERE exercicio IN ('.implode(",", $ids_exercicios).');';
    $qry_result2 = $conn->query($qry2);
    $id_inicio = 12827;
    $i = 0;
    while($row2 = mysqli_fetch_assoc($qry_result2)) {
        if($i > 0) {
            if($last_ex != $row2["exercicio"]) {
                // echo "<pre>".print_r($row2,true)."</pre>";
                // echo $last_ex;
                // exit;
                if(($last_ex + 1) != $row2["exercicio"]) {
                    $id_inicio += $row2["exercicio"] - $last_ex;
                    $last_ex = $row2["exercicio"];
                    continue;
                }
                $id_inicio++;
            }
        }
        $last_ex = $row2["exercicio"];

        echo 'INSERT INTO pergunta_exercicio (exercicio, tipo_exercicio, ordem, sentenca, ativo, data_cadastro, data_atualizacao) VALUES ('.$id_inicio.', '.$row2["tipo_exercicio"].', '.$row2["ordem"].', "'.$row2["sentenca"].'", "'.$row2["ativo"].'", "'.$row2["data_cadastro"].'", "'.$row2["data_atualizacao"].'");';
        echo "<br>";

        $i++;
    }

    
    exit;
    //exit("<pre>".print_r($rows,true)."</pre>");