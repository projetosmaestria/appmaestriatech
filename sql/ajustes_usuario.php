<?php
// Script de Ajustes de Banco e Limpeza de Dados Ficticios
$m = new mysqli('localhost', 'xkey', 'Alfa7410147', 'rodney');
if ($m->connect_error) { die($m->connect_error); }
$m->set_charset("utf8");

echo "=== AJUSTES DE BANCO E LIMPEZA DE DADOS FICTÍCIOS ===\n";

// 1. Adicionar colunas de controle de onde o usuario parou e o que esta vendo
$chkCols = $m->query("SHOW COLUMNS FROM treinamento_progresso LIKE 'ultima_etapa_id'");
if ($chkCols && $chkCols->num_rows == 0) {
    $m->query("ALTER TABLE treinamento_progresso ADD COLUMN `ultima_etapa_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `etapas_concluidas`");
    $m->query("ALTER TABLE treinamento_progresso ADD COLUMN `ultima_acao` VARCHAR(255) NULL DEFAULT NULL AFTER `ultima_atividade`");
    echo "  [OK] Colunas 'ultima_etapa_id' e 'ultima_acao' adicionadas a treinamento_progresso.\n";
}

// 2. Limpar todos os progressos e avaliacoes ficticias
$m->query("TRUNCATE TABLE treinamento_progresso");
$m->query("TRUNCATE TABLE treinamento_etapa_progresso");
$m->query("TRUNCATE TABLE treinamento_atividade_progresso");
$m->query("TRUNCATE TABLE treinamento_avaliacoes");
echo "  [OK] Tabelas de progresso e avaliações limpas. Nenhum curso fictício em andamento.\n";

// 3. Deixar apenas 1 treinamento de testes
$m->query("DELETE FROM treinamentos WHERE id > 1");
$m->query("UPDATE treinamentos SET 
    titulo = 'Treinamento de Teste - Financeiro: Formas de Pagamento',
    slug = 'treinamento-teste-financeiro-formas-pagamento',
    descricao_curta = 'Treinamento de teste para configuração e parametrização de formas de pagamento no módulo Financeiro.',
    destaque = 1,
    novidade = 0,
    ativo = 1
    WHERE id = 1");
echo "  [OK] Mantido apenas 1 treinamento de testes (ID 1).\n";

// Deixar apenas etapas do treinamento 1
$m->query("DELETE FROM treinamento_etapas WHERE treinamento_id > 1");
$m->query("DELETE FROM treinamento_atividades WHERE treinamento_id > 1");
$m->query("DELETE FROM treinamento_arquivos WHERE treinamento_id > 1");
echo "  [OK] Etapas de cursos removidos limpas.\n";

// 4. Ajustar Banners - remover termo Academy
$m->query("DELETE FROM treinamento_banners WHERE id > 2");
$m->query("UPDATE treinamento_banners SET 
    titulo = 'Plataforma de Treinamentos MaestriaTech',
    subtitulo = 'Capacitação Contínua',
    descricao = 'Aprenda a utilizar os módulos e funcionalidades do sistema de forma prática e descomplicada com vídeos e tutoriais passo a passo.',
    texto_botao = 'Ver Catálogo',
    url_botao = 'treinamentos'
    WHERE id = 1");

$m->query("UPDATE treinamento_banners SET 
    titulo = 'Módulo Financeiro: Formas de Pagamento',
    subtitulo = 'Treinamento de Teste',
    descricao = 'Aprenda como cadastrar novas formas de pagamento, configurar taxas operacionais e conciliar títulos financeiros.',
    texto_botao = 'Acessar Treinamento',
    url_botao = 'treinamentos/ver/1'
    WHERE id = 2");
echo "  [OK] Banners ajustados sem o termo Academy.\n";

// 5. Ajustar Menus - retirar icones dos submenus e encurtar nomes para nao cortar
$m->query("UPDATE menus SET nome = 'Treinamentos' WHERE id = 225");
$m->query("UPDATE menus SET nome = 'Catálogo', icon_class = '' WHERE id = 226");
$m->query("UPDATE menus SET nome = 'Meus Treinamentos', icon_class = '' WHERE id = 227");
$m->query("UPDATE menus SET nome = 'Gerenciar Treinamentos', icon_class = '' WHERE id = 228");
$m->query("UPDATE menus SET nome = 'Banners', icon_class = '' WHERE id = 229");
$m->query("UPDATE menus SET nome = 'Acompanhamento', icon_class = '' WHERE id = 230");
echo "  [OK] Submenus ajustados: sem ícones e com nomes organizados sem corte.\n";

// 6. Garantir que apenas Admin (Grupo 1) tenha acesso a treinamentos_admin
$groups = [2, 3]; // franqueadora_suporte e desenvolvedor
foreach ($groups as $gid) {
    $r = $m->query("SELECT permissoes FROM `groups` WHERE id = {$gid}");
    if ($r && $r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $perms = unserialize($row['permissoes']);
        if (is_array($perms)) {
            unset($perms['treinamentos_admin']);
            // Mantem apenas treinamentos (aluno)
            $perms['treinamentos'] = ['index', 'modulo', 'ver', 'detalhes', 'etapa', 'concluir_etapa', 'salvar_progresso_video', 'toggle_atividade', 'avaliar', 'download_arquivo', 'meu_progresso'];
            $ser = serialize($perms);
            $stmt = $m->prepare("UPDATE `groups` SET permissoes = ? WHERE id = ?");
            $stmt->bind_param('si', $ser, $gid);
            $stmt->execute();
        }
    }
}
echo "  [OK] Permissões restritas: apenas Administradores possuem acesso a 'treinamentos_admin'.\n";

echo "\nConcluído com sucesso!\n";
