<?php

    $input = $_POST;

    $fp = fopen("bloco.txt", "a");

    $escreve = fwrite($fp, print_r($input, TRUE));
    $escreve = fwrite($fp, date('Y-m-d H:i:s'));
     
    fclose($fp);
