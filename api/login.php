<?php
/**
 * POST api/login.php  { "email": "...", "password": "..." }
 *
 * Valida as credenciais e, no sucesso, devolve os nomes dos grupos do usuário
 * e o CNPJ de cada unidade que ele acessa:
 * { "login": "success", "grupos": ["Admin"], "empresas": [{ "cnpj": "..." }] }
 * Aceita os campos como formulário ou no corpo JSON.
 *
 * A conexão vem de application/config/database.php, pelo mesmo CI_ENV do
 * index.php.
 */
header('Content-Type: application/json; charset=utf-8');

// As falhas de login respondem com HTTP 200, no mesmo formato do saxwebv2.
function responder_login($dados)
{
    echo json_encode($dados);
    exit;
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Allow: POST');
    responder_login(array('login' => 'failure', 'msg' => 'Método não permitido.'));
}

$email = isset($_POST['email']) ? $_POST['email'] : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
if ($email === null && $password === null) {
    $json = json_decode(file_get_contents('php://input'), true);
    if (is_array($json)) {
        $email = isset($json['email']) ? $json['email'] : null;
        $password = isset($json['password']) ? $json['password'] : null;
    }
}
$email = trim((string) $email);
$password = (string) $password;

if ($email === '' || $password === '') {
    responder_login(array('login' => 'failure'));
}

// database.php exige BASEPATH e escolhe o banco pelo ENVIRONMENT.
define('BASEPATH', true);
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');
$db = array();
require dirname(__DIR__) . '/application/config/database.php';
if (!isset($db['default']['hostname'])) {
    header('HTTP/1.1 500 Internal Server Error');
    responder_login(array('login' => 'failure', 'msg' => 'Banco de dados não configurado.'));
}

$cfg = $db['default'];
$conn = @new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($conn->connect_errno) {
    header('HTTP/1.1 500 Internal Server Error');
    responder_login(array('login' => 'failure', 'msg' => 'Erro ao conectar ao banco de dados.'));
}
$conn->set_charset(!empty($cfg['char_set']) ? $cfg['char_set'] : 'utf8');

// Mesmas regras do Ion_auth_model::login (usuário ativo e vinculado a uma
// empresa), com o e-mail escapado.
$res = $conn->query("SELECT u.id, u.password
        FROM users u
        JOIN empresas_users eu ON eu.user_id = u.id
        WHERE u.email = '" . $conn->real_escape_string($email) . "' AND u.active = 1
        ORDER BY u.id DESC
        LIMIT 1");
$usuario = $res ? $res->fetch_assoc() : null;

// Mesma verificação da biblioteca Bcrypt do Ion_auth.
if (!$usuario || $usuario['password'] === '' || crypt($password, $usuario['password']) !== $usuario['password']) {
    $conn->close();
    responder_login(array('login' => 'failure'));
}

$user_id = (int) $usuario['id'];

// Só entram unidades e grupos das empresas a que o usuário está vinculado.
$empresas = array();
$res = $conn->query("SELECT DISTINCT TRIM(u.cnpj) AS cnpj
        FROM user_unidade uu
        JOIN unidades u ON u.id = uu.unidade_id
        JOIN empresas_users eu ON eu.empresa_id = u.empresa_id AND eu.user_id = uu.user_id
        WHERE uu.user_id = $user_id AND TRIM(u.cnpj) <> ''
        ORDER BY cnpj");
while ($res && ($row = $res->fetch_assoc())) {
    $empresas[] = array('cnpj' => (string) $row['cnpj']);
}

$grupos = array();
$res = $conn->query("SELECT DISTINCT g.name
        FROM users_groups ug
        JOIN `groups` g ON g.id = ug.group_id
        JOIN empresas_users eu ON eu.empresa_id = g.empresa_id AND eu.user_id = ug.user_id
        WHERE ug.user_id = $user_id
        ORDER BY g.name");
while ($res && ($row = $res->fetch_assoc())) {
    $grupos[] = (string) $row['name'];
}

$conn->close();

responder_login(array(
    'login' => 'success',
    'grupos' => $grupos,
    'empresas' => $empresas,
));
