<?php
/**
 * API - Consulta de Habilitação de Produtos por Cliente
 *
 * Endpoint para verificar se determinado produto está habilitado
 * para um cliente com base em seu documento (CNPJ ou CPF).
 *
 * Parâmetros aceitos (via form-data, x-www-form-urlencoded ou JSON):
 * - documento (string): CNPJ ou CPF do cliente (com ou sem pontuação)
 * - produto   (string): Nome do produto cadastrado no sistema
 */

// Configuração de cabeçalhos HTTP
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Tratamento de requisições OPTIONS (Preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Inicialização da estrutura de resposta padrão
$resposta = array(
    'sucesso'    => false,
    'habilitado' => false,
    'mensagem'   => '',
    'dados'      => null
);

try {
    // Captura de dados da requisição (POST form-data, JSON raw ou GET fallback)
    $input = $_POST;
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $input = $json;
            }
        }
    }
    if (empty($input)) {
        $input = $_GET;
    }

    $documento = isset($input['documento']) ? trim((string)$input['documento']) : '';
    $produto   = isset($input['produto'])   ? trim((string)$input['produto'])   : '';

    // Validação de parâmetros obrigatórios
    if (empty($documento)) {
        http_response_code(400);
        $resposta['mensagem'] = 'Parâmetro obrigatório ausente: documento (CNPJ ou CPF).';
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if (empty($produto)) {
        http_response_code(400);
        $resposta['mensagem'] = 'Parâmetro obrigatório ausente: produto (nome do produto).';
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // Carregamento dinâmico da configuração de banco de dados do projeto
    $rootPath = dirname(__DIR__);
    if (!defined('BASEPATH')) {
        define('BASEPATH', $rootPath . '/system/');
    }
    if (!defined('ENVIRONMENT')) {
        define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');
    }

    $databaseConfigFile = $rootPath . '/application/config/database.php';
    if (!file_exists($databaseConfigFile)) {
        throw new Exception('Arquivo de configuração de banco de dados não localizado.');
    }
    require $databaseConfigFile;

    if (!isset($db['default'])) {
        throw new Exception('Configuração de banco de dados padrão não encontrada.');
    }
    $cfg = $db['default'];

    // Conexão MySQLi segura com charset UTF-8
    $conn = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
    if ($conn->connect_error) {
        throw new Exception('Erro ao conectar ao banco de dados: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8');

    // 1. Busca do Cliente (Unidade) pelo documento (CNPJ ou CPF)
    // Remove caracteres não numéricos para comparação flexível
    $docLimpo = preg_replace('/\D/', '', $documento);

    $sqlCliente = "SELECT id, empresa_id, unidade, razao_social, cnpj, cpf, tipo_cliente, actived 
                   FROM unidades 
                   WHERE (
                       REPLACE(REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', ''), ' ', '') = ?
                       OR REPLACE(REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', ''), ' ', '') = ?
                       OR cnpj = ?
                       OR cpf = ?
                   )
                   LIMIT 1";

    $stmtCliente = $conn->prepare($sqlCliente);
    if (!$stmtCliente) {
        throw new Exception('Erro na preparação da consulta de cliente: ' . $conn->error);
    }
    $stmtCliente->bind_param('ssss', $docLimpo, $docLimpo, $documento, $documento);
    $stmtCliente->execute();
    $resCliente = $stmtCliente->get_result();
    $cliente = $resCliente->fetch_assoc();
    $stmtCliente->close();

    if (!$cliente) {
        http_response_code(404);
        $resposta['mensagem'] = 'Cliente não encontrado com o documento informado.';
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // 2. Busca do Produto pelo nome
    // Tenta busca exata (case-insensitive)
    $sqlProduto = "SELECT id, empresa_id, nome, status 
                   FROM produtos 
                   WHERE LOWER(TRIM(nome)) = LOWER(TRIM(?)) 
                   LIMIT 1";
    $stmtProduto = $conn->prepare($sqlProduto);
    if (!$stmtProduto) {
        throw new Exception('Erro na preparação da consulta de produto: ' . $conn->error);
    }
    $stmtProduto->bind_param('s', $produto);
    $stmtProduto->execute();
    $resProduto = $stmtProduto->get_result();
    $produtoEncontrado = $resProduto->fetch_assoc();
    $stmtProduto->close();

    // Se não encontrou por busca exata, tenta correspondência parcial (LIKE)
    if (!$produtoEncontrado) {
        $likeTerm = '%' . $produto . '%';
        $sqlProdLike = "SELECT id, empresa_id, nome, status 
                        FROM produtos 
                        WHERE nome LIKE ? 
                        LIMIT 1";
        $stmtProdLike = $conn->prepare($sqlProdLike);
        if ($stmtProdLike) {
            $stmtProdLike->bind_param('s', $likeTerm);
            $stmtProdLike->execute();
            $resProdLike = $stmtProdLike->get_result();
            $produtoEncontrado = $resProdLike->fetch_assoc();
            $stmtProdLike->close();
        }
    }

    if (!$produtoEncontrado) {
        http_response_code(404);
        $resposta['mensagem'] = 'Produto não encontrado com o nome informado.';
        $documentoRaw = !empty($cliente['cnpj']) ? $cliente['cnpj'] : $cliente['cpf'];
        $documentoDigitos = preg_replace('/\D/', '', (string)$documentoRaw);
        $resposta['dados'] = array(
            'cliente' => array(
                'id'                  => (int)$cliente['id'],
                'nome'                => $cliente['unidade'],
                'razao_social'        => $cliente['razao_social'],
                'documento'           => $documentoDigitos,
                'documento_formatado' => $documentoRaw
            )
        );
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // 3. Busca da Habilitação do Produto para o Cliente
    $sqlHab = "SELECT id, id_produto, id_cliente, periodo_inicio, periodo_fim, status, demonstracao, data_cadastro 
               FROM habilitacoes_produto 
               WHERE id_cliente = ? AND id_produto = ? 
               ORDER BY id DESC 
               LIMIT 1";
    $stmtHab = $conn->prepare($sqlHab);
    if (!$stmtHab) {
        throw new Exception('Erro na preparação da consulta de habilitação: ' . $conn->error);
    }
    $stmtHab->bind_param('ii', $cliente['id'], $produtoEncontrado['id']);
    $stmtHab->execute();
    $resHab = $stmtHab->get_result();
    $habilitacao = $resHab->fetch_assoc();
    $stmtHab->close();

    $dataHoje = date('Y-m-d');
    $statusHabilitacao = $habilitacao ? $habilitacao['status'] : null;
    $periodoFim = ($habilitacao && !empty($habilitacao['periodo_fim']) && $habilitacao['periodo_fim'] !== '0000-00-00') ? $habilitacao['periodo_fim'] : null;
    $expirado = false;

    if ($periodoFim && $periodoFim < $dataHoje) {
        $expirado = true;
    }

    // Critérios para habilitação:
    // 1) Registro deve existir na tabela habilitacoes_produto
    // 2) Campo status deve ser 'S'
    // 3) Período fim não pode estar vencido (se informado)
    // 4) Cliente e Produto devem estar ativos no sistema
    $isHabilitado = false;
    $mensagem = '';

    if (!$habilitacao) {
        $mensagem = 'Cliente não possui registro de habilitação para este produto.';
    } elseif ($statusHabilitacao !== 'S') {
        $mensagem = 'Produto desabilitado para este cliente.';
    } elseif ($expirado) {
        $mensagem = 'A habilitação do produto expirou em ' . date('d/m/Y', strtotime($periodoFim)) . '.';
    } elseif (strtolower((string)$cliente['actived']) !== 's') {
        $mensagem = 'Cliente inativo no sistema.';
    } elseif ($produtoEncontrado['status'] !== 'S') {
        $mensagem = 'Produto inativo no sistema.';
    } else {
        $isHabilitado = true;
        $mensagem = 'Produto habilitado com sucesso.';
    }

    $resposta['sucesso']    = true;
    $resposta['habilitado'] = $isHabilitado;
    $resposta['mensagem']   = $mensagem;
    $documentoRaw = !empty($cliente['cnpj']) ? $cliente['cnpj'] : $cliente['cpf'];
    $documentoDigitos = preg_replace('/\D/', '', (string)$documentoRaw);

    $resposta['dados']      = array(
        'cliente' => array(
            'id'                  => (int)$cliente['id'],
            'nome'                => $cliente['unidade'],
            'razao_social'        => $cliente['razao_social'],
            'documento'           => $documentoDigitos,
            'documento_formatado' => $documentoRaw,
            'ativo'               => strtolower((string)$cliente['actived']) === 's'
        ),
        'produto' => array(
            'id'     => (int)$produtoEncontrado['id'],
            'nome'   => $produtoEncontrado['nome'],
            'status' => $produtoEncontrado['status']
        ),
        'habilitacao' => $habilitacao ? array(
            'id'             => (int)$habilitacao['id'],
            'status'         => $habilitacao['status'],
            'periodo_inicio' => $habilitacao['periodo_inicio'],
            'periodo_fim'    => $periodoFim,
            'expirado'       => $expirado,
            'demonstracao'   => $habilitacao['demonstracao'] === 'S'
        ) : null
    );

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $resposta['sucesso']    = false;
    $resposta['habilitado'] = false;
    $resposta['mensagem']   = 'Erro interno: ' . $e->getMessage();
}

echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
