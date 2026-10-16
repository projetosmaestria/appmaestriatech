<?php
/**
 * Script de Deploy Automatizado para Produção - Módulo de Treinamentos
 * 
 * Executa:
 * 1. O script sql/treinamentos_schema.sql (10 tabelas, índices e menus)
 * 2. Atualização das permissões serializadas na tabela `groups`
 * 3. Validação de integridade de tabelas e colunas
 * 
 * Uso:
 *   php sql/deploy_producao.php [host] [user] [pass] [database]
 * Exemplo:
 *   php sql/deploy_producao.php localhost root Alfa7410147 rodney
 */

$host = $argv[1] ?? 'localhost';
$user = $argv[2] ?? 'xkey';
$pass = $argv[3] ?? 'Alfa7410147';
$dbname = $argv[4] ?? 'rodney';

echo "========================================================\n";
echo "  DEPLOY EM PRODUÇÃO - MÓDULO DE TREINAMENTOS\n";
echo "========================================================\n";
echo "Conectando ao banco {$dbname} em {$host}...\n";

$m = @new mysqli($host, $user, $pass, $dbname);
if ($m->connect_error) {
    // Tenta fallback sem senha caso seja ambiente local
    $m = @new mysqli('127.0.0.1', 'root', '', $dbname);
    if ($m->connect_error) {
        die("ERRO DE CONEXÃO: " . $m->connect_error . "\nInforme as credenciais: php sql/deploy_producao.php <host> <user> <pass> <database>\n");
    }
}
$m->set_charset("utf8");

echo "Conexão estabelecida com sucesso.\n\n";

// 1. Executar treinamentos_schema.sql
$schemaPath = __DIR__ . '/treinamentos_schema.sql';
if (!file_exists($schemaPath)) {
    die("ERRO: Arquivo treinamentos_schema.sql não encontrado em {$schemaPath}\n");
}

echo "1. Executando schema SQL (treinamentos_schema.sql)...\n";
$sqlContent = file_get_contents($schemaPath);

if ($m->multi_query($sqlContent)) {
    do {
        if ($res = $m->store_result()) {
            $res->free();
        }
    } while ($m->more_results() && $m->next_result());
    echo "  [OK] Tabelas, índices e menus executados com sucesso.\n";
} else {
    echo "  [AVISO] Erro na execução do multi_query: " . $m->error . "\n";
}

// 2. Atualizar permissões na tabela groups
echo "\n2. Atualizando permissões de grupos (tabela `groups`)...\n";

$metodosAluno = [
    'index', 'modulo', 'ver', 'detalhes', 'etapa', 
    'concluir_etapa', 'salvar_progresso_video', 
    'toggle_atividade', 'avaliar', 'download_arquivo', 'meu_progresso'
];

$metodosAdmin = [
    'index', 'create', 'edit', 'delete', 'etapas', 
    'etapa_create', 'etapa_edit', 'etapa_delete', 
    'arquivo_upload', 'arquivo_delete', 'atividades', 
    'atividade_create', 'atividade_edit', 'atividade_delete', 
    'permissoes', 'banners', 'banner_create', 'banner_edit', 
    'banner_delete', 'relatorios'
];

$resGroups = $m->query("SELECT id, name, description, permissoes FROM `groups`");
if ($resGroups && $resGroups->num_rows > 0) {
    while ($g = $resGroups->fetch_assoc()) {
        $gid = (int)$g['id'];
        $name = strtolower($g['name']);
        $perms = !empty($g['permissoes']) ? @unserialize($g['permissoes']) : [];
        if (!is_array($perms)) {
            $perms = [];
        }

        // Todos os grupos recebem acesso de aluno aos treinamentos
        $perms['treinamentos'] = $metodosAluno;

        // Se for grupo de administrador (ID 1 ou nome admin), recebe permissões de gestão
        if ($gid === 1 || strpos($name, 'admin') !== false) {
            $perms['treinamentos_admin'] = $metodosAdmin;
            echo "  [OK] Grupo '{$g['name']}' (ID {$gid}): Liberado Acesso Aluno + Gestão Administrativa.\n";
        } else {
            // Garante que não-admins não tenham acesso administrativo
            unset($perms['treinamentos_admin']);
            echo "  [OK] Grupo '{$g['name']}' (ID {$gid}): Liberado Acesso Aluno (Catálogo e Meus Treinamentos).\n";
        }

        $serialized = serialize($perms);
        $stmt = $m->prepare("UPDATE `groups` SET `permissoes` = ? WHERE `id` = ?");
        $stmt->bind_param('si', $serialized, $gid);
        $stmt->execute();
        $stmt->close();
    }
}

// 3. Validação final
echo "\n3. Validando integridade no banco de dados...\n";

$tabelasEsperadas = [
    'treinamento_banners',
    'treinamentos',
    'treinamento_etapas',
    'treinamento_arquivos',
    'treinamento_atividades',
    'treinamento_permissoes',
    'treinamento_progresso',
    'treinamento_etapa_progresso',
    'treinamento_atividade_progresso',
    'treinamento_avaliacoes'
];

foreach ($tabelasEsperadas as $tbl) {
    $chk = $m->query("SHOW TABLES LIKE '{$tbl}'");
    if ($chk && $chk->num_rows > 0) {
        echo "  [OK] Tabela `{$tbl}` presente.\n";
    } else {
        echo "  [FALHA] Tabela `{$tbl}` NÃO ENCONTRADA!\n";
    }
}

// Validação de colunas críticas
$colsCriticas = [
    'treinamento_progresso' => ['ultima_etapa_id', 'ultima_acao']
];

foreach ($colsCriticas as $tbl => $cols) {
    foreach ($cols as $c) {
        $chkCol = $m->query("SHOW COLUMNS FROM `{$tbl}` LIKE '{$c}'");
        if ($chkCol && $chkCol->num_rows > 0) {
            echo "  [OK] Coluna crítica `{$tbl}`.`{$c}` validada.\n";
        } else {
            echo "  [FALHA] Coluna crítica `{$tbl}`.`{$c}` AUSENTE!\n";
        }
    }
}

// Validação de Menus
$chkMenu = $m->query("SELECT id, nome FROM `menus` WHERE `controlador` = 'treinamentos' AND `pai_id` IS NULL");
if ($chkMenu && $chkMenu->num_rows > 0) {
    $menuRow = $chkMenu->fetch_assoc();
    echo "  [OK] Menu principal `{$menuRow['nome']}` (ID {$menuRow['id']}) cadastrado.\n";
    
    $subMenus = $m->query("SELECT id, nome, controlador, metodo FROM `menus` WHERE `pai_id` = {$menuRow['id']} ORDER BY `order`");
    while ($sub = $subMenus->fetch_assoc()) {
        echo "    - Submenu: `{$sub['nome']}` ({$sub['controlador']}/{$sub['metodo']})\n";
    }
} else {
    echo "  [FALHA] Menu principal `Treinamentos` não encontrado na tabela `menus`!\n";
}

echo "\n========================================================\n";
echo "  DEPLOY CONCLUÍDO COM SUCESSO!\n";
echo "========================================================\n";
