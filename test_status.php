<?php
$mysqli = new mysqli('xmysql.rodney.x-br.com', 'rodney', 'b7gVA@K2QXtrKjsz', 'rodney');
$result = $mysqli->query('SELECT id, descricao FROM chamado_status');
while($row = $result->fetch_assoc()) {
    echo $row['id'] . ' => ' . $row['descricao'] . "\n";
}
