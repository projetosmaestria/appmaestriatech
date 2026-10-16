<?php
require_once "Ion_auth_model.php";
header('Content-Type: application/json; charset=utf-8');

$ion_auth = new Ion_auth_model();

if ($ion_auth->login($_POST['email'], $_POST['password']))
{
    echo json_encode(['login' => 'success']);
}
else
{
    echo json_encode(['login' => 'failure']);
}