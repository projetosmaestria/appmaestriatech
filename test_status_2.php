<?php
$mysqli = new mysqli('mysql.xkeysti.com.br', 'xkeysti1', 'WtUV]!3_T6V,', 'xkeysti1');
if ($mysqli->connect_errno) { echo "Erro: " . $mysqli->connect_error; exit; }
$result = $mysqli->query('SELECT id, descricao FROM chamado_status');
while($row = $result->fetch_assoc()) {
    echo $row['id'] . ' => ' . $row['descricao'] . "\n";
}
