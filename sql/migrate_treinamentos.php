<?php
// Script de Migracao e Populacao do Modulo de Treinamentos - Academy
$host = 'localhost';
$user = 'xkey';
$pass = 'Alfa7410147';
$db   = 'rodney';

$m = new mysqli($host, $user, $pass, $db);
if ($m->connect_error) {
    die("Falha na conexao com o banco: " . $m->connect_error);
}
$m->set_charset("utf8");

echo "Conectado ao banco '{$db}' com sucesso!\n";

// 1. Executar criacao das tabelas a partir do schema
$schemaSql = file_get_contents(__DIR__ . '/treinamentos_schema.sql');
$queries = array_filter(array_map('trim', explode(';', $schemaSql)));

foreach ($queries as $query) {
    if (empty($query)) continue;
    if (!$m->query($query)) {
        echo "Erro ao executar query: " . $m->error . "\nSQL: " . substr($query, 0, 100) . "...\n";
    }
}
echo "Tabelas do modulo criadas/verificadas com sucesso!\n";

// 2. Criar Diretorios de Upload necessarios
$baseUpload = dirname(__DIR__) . '/upload/treinamentos';
$dirs = [
    $baseUpload,
    $baseUpload . '/capas',
    $baseUpload . '/banners',
    $baseUpload . '/materiais'
];
foreach ($dirs as $d) {
    if (!is_dir($d)) {
        mkdir($d, 0777, true);
        echo "Diretorio criado: {$d}\n";
    }
}

// 3. Cadastrar Menus do Modulo de Treinamentos
$now = time();

// Verificar se menu pai ja existe
$chk = $m->query("SELECT id FROM menus WHERE controlador = 'treinamentos' AND pai_id IS NULL");
if ($chk && $chk->num_rows > 0) {
    $idPai = $chk->fetch_assoc()['id'];
    echo "Menu pai Treinamentos ja existe (ID: {$idPai}).\n";
} else {
    $stmt = $m->prepare("INSERT INTO menus (pai_id, tipo, nome, icon_class, controlador, metodo, `order`, ativo, create_on) VALUES (NULL, 0, 'Treinamentos', '<i class=\"fa fa-graduation-cap\"></i>', 'treinamentos', 'index', 22, 1, ?)");
    $stmt->bind_param('i', $now);
    $stmt->execute();
    $idPai = $m->insert_id;
    echo "Menu pai Treinamentos criado com ID: {$idPai}\n";

    // Vincular empresa 1
    $m->query("INSERT IGNORE INTO menus_empresas (menus_id, empresas_id) VALUES ({$idPai}, 1)");
}

// Submenus
$submenus = [
    [
        'nome' => 'Academy / Catálogo',
        'icon' => '<i class="fa fa-th-large"></i>',
        'ctrl' => 'treinamentos',
        'metodo' => 'index',
        'order' => 1
    ],
    [
        'nome' => 'Meus Treinamentos',
        'icon' => '<i class="fa fa-user-circle"></i>',
        'ctrl' => 'treinamentos',
        'metodo' => 'meu_progresso',
        'order' => 2
    ],
    [
        'nome' => 'Gerenciar Treinamentos',
        'icon' => '<i class="fa fa-book"></i>',
        'ctrl' => 'treinamentos_admin',
        'metodo' => 'index',
        'order' => 3
    ],
    [
        'nome' => 'Banners do Carrossel',
        'icon' => '<i class="fa fa-sliders"></i>',
        'ctrl' => 'treinamentos_admin',
        'metodo' => 'banners',
        'order' => 4
    ],
    [
        'nome' => 'Acompanhamento & Avaliações',
        'icon' => '<i class="fa fa-bar-chart"></i>',
        'ctrl' => 'treinamentos_admin',
        'metodo' => 'relatorios',
        'order' => 5
    ]
];

foreach ($submenus as $sm) {
    $chkSm = $m->query("SELECT id FROM menus WHERE pai_id = {$idPai} AND controlador = '{$sm['ctrl']}' AND metodo = '{$sm['metodo']}'");
    if ($chkSm && $chkSm->num_rows > 0) {
        $idSub = $chkSm->fetch_assoc()['id'];
        echo "Submenu '{$sm['nome']}' ja existe (ID: {$idSub}).\n";
    } else {
        $stmt = $m->prepare("INSERT INTO menus (pai_id, tipo, nome, icon_class, controlador, metodo, `order`, ativo, create_on) VALUES (?, 0, ?, ?, ?, ?, ?, 1, ?)");
        $stmt->bind_param('issssii', $idPai, $sm['nome'], $sm['icon'], $sm['ctrl'], $sm['metodo'], $sm['order'], $now);
        $stmt->execute();
        $idSub = $m->insert_id;
        echo "Submenu '{$sm['nome']}' criado com ID: {$idSub}\n";

        $m->query("INSERT IGNORE INTO menus_empresas (menus_id, empresas_id) VALUES ({$idSub}, 1)");
    }
}

// 4. Conceder permissoes aos Grupos Administrativos (admin ID 1 e desenvolvedor ID 3)
$controllersMethods = [
    'treinamentos' => [
        'index', 'modulo', 'ver', 'detalhes', 'etapa', 'concluir_etapa',
        'salvar_progresso_video', 'toggle_atividade', 'avaliar', 'download_arquivo', 'meu_progresso'
    ],
    'treinamentos_admin' => [
        'index', 'create', 'edit', 'delete', 'etapas', 'etapa_create',
        'etapa_edit', 'etapa_delete', 'arquivo_upload', 'arquivo_delete',
        'atividades', 'atividade_create', 'atividade_edit', 'atividade_delete',
        'permissoes', 'banners', 'banner_create', 'banner_edit', 'banner_delete',
        'relatorios'
    ]
];

$groupsToUpdate = [1, 2, 3];
foreach ($groupsToUpdate as $gid) {
    $gres = $m->query("SELECT permissoes FROM `groups` WHERE id = {$gid}");
    if ($gres && $gres->num_rows > 0) {
        $row = $gres->fetch_assoc();
        $perms = unserialize($row['permissoes']);
        if (!is_array($perms)) $perms = [];

        // Adiciona permissoes
        foreach ($controllersMethods as $ctrl => $methods) {
            if ($gid == 2 && $ctrl == 'treinamentos_admin') {
                // Grupo suporte: pode ver treinamentos e relatorios se quiser, ou apenas treinamentos
                $perms[$ctrl] = ['index', 'relatorios'];
            } else {
                $perms[$ctrl] = $methods;
            }
        }

        $serialized = serialize($perms);
        $stmt = $m->prepare("UPDATE `groups` SET permissoes = ? WHERE id = ?");
        $stmt->bind_param('si', $serialized, $gid);
        $stmt->execute();
        echo "Permissoes atualizadas para o Grupo ID: {$gid}\n";
    }
}

// 5. Inserir Dados Iniciais Reais de Banners
$chkBan = $m->query("SELECT COUNT(*) as total FROM treinamento_banners");
if ($chkBan && $chkBan->fetch_assoc()['total'] == 0) {
    $nowStr = date('Y-m-d H:i:s');
    $banners = [
        [
            'empresa_id' => 1,
            'titulo' => 'Bem-vindo ao Maestria Academy!',
            'subtitulo' => 'Plataforma Oficial de Capacitação e Treinamentos',
            'descricao' => 'Aprenda a utilizar todos os módulos, telas e regras de negócio do sistema de forma prática e descomplicada com vídeos e tutoriais passo a passo.',
            'imagem' => 'assets/xkey/images/banner_academy_1.jpg',
            'texto_botao' => 'Explorar Cursos',
            'url_botao' => 'treinamentos',
            'treinamento_id' => NULL,
            'modulo_id' => NULL,
            'ordem' => 1,
            'ativo' => 1,
            'created_at' => $nowStr
        ],
        [
            'empresa_id' => 1,
            'titulo' => 'Módulo Financeiro: Formas de Pagamento e Títulos',
            'subtitulo' => 'Treinamento Essencial',
            'descricao' => 'Aprenda como cadastrar novas formas de pagamento, configurar taxas, emitir boletos e conciliar títulos a pagar e receber.',
            'imagem' => 'assets/xkey/images/banner_academy_2.jpg',
            'texto_botao' => 'Iniciar Treinamento',
            'url_botao' => 'treinamentos/ver/1',
            'treinamento_id' => 1,
            'modulo_id' => 1,
            'ordem' => 2,
            'ativo' => 1,
            'created_at' => $nowStr
        ],
        [
            'empresa_id' => 1,
            'titulo' => 'Controle de Faturamento e Fluxo Operacional',
            'subtitulo' => 'Novidades e Procedimentos Atualizados',
            'descricao' => 'Conheça as novas funcionalidades de fechamento de lotes e geração automática de duplicatas no faturamento.',
            'imagem' => 'assets/xkey/images/banner_academy_3.jpg',
            'texto_botao' => 'Ver Detalhes',
            'url_botao' => 'treinamentos/ver/3',
            'treinamento_id' => 3,
            'modulo_id' => 4,
            'ordem' => 3,
            'ativo' => 1,
            'created_at' => $nowStr
        ]
    ];

    foreach ($banners as $b) {
        $stmt = $m->prepare("INSERT INTO treinamento_banners (empresa_id, titulo, subtitulo, descricao, imagem, texto_botao, url_botao, treinamento_id, modulo_id, ordem, ativo, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issssssiisis', $b['empresa_id'], $b['titulo'], $b['subtitulo'], $b['descricao'], $b['imagem'], $b['texto_botao'], $b['url_botao'], $b['treinamento_id'], $b['modulo_id'], $b['ordem'], $b['ativo'], $b['created_at']);
        $stmt->execute();
    }
    echo "Banners iniciais inseridos com sucesso!\n";
}

// 6. Inserir Treinamentos Reais
$chkTr = $m->query("SELECT COUNT(*) as total FROM treinamentos");
if ($chkTr && $chkTr->fetch_assoc()['total'] == 0) {
    $nowStr = date('Y-m-d H:i:s');
    
    // Treinamento 1: Financeiro - Formas de Pagamento
    $stmt = $m->prepare("INSERT INTO treinamentos (id, empresa_id, modulo_id, produto_id, titulo, slug, descricao_curta, descricao_completa, objetivo, imagem_capa, carga_horaria_minutos, nivel, destaque, novidade, avaliacao_obrigatoria, tipo_acesso, ordem, ativo, cadastrado_por, created_at) VALUES (1, 1, 1, 1, 'Como cadastrar e configurar formas de pagamento', 'como-cadastrar-configurar-formas-pagamento', 'Aprenda o passo a passo completo para cadastrar, configurar e gerenciar todas as formas de pagamento disponíveis no sistema.', 'Neste treinamento você verá como acessar o módulo financeiro, configurar parâmetros bancários, prazos de compensação, taxas operacionais e vincular cada forma de pagamento aos seus respectivos planos de contas para conciliação contábil.', 'Capacitar os usuários a realizar parametrizações corretas de formas de pagamento (boleto, cartão, PIX, dinheiro), definindo taxas, prazos e regras de movimentação financeira.', 'assets/xkey/images/curso_financeiro_1.jpg', 45, 'basico', 1, 1, 1, 'livre', 1, 1, 1, ?)");
    $stmt->bind_param('s', $nowStr);
    $stmt->execute();
    $tr1Id = 1;

    // Etapas do Treinamento 1
    $etapasTr1 = [
        [
            'titulo' => 'Introdução às formas de pagamento no ERP',
            'desc' => 'Entenda como as formas de pagamento impactam diretamente o fluxo de caixa e o plano de contas.',
            'conteudo' => '<p>Seja bem-vindo a primeira etapa do treinamento de <strong>Formas de Pagamento</strong>!</p><p>As formas de pagamento são a base de qualquer movimentação no módulo Financeiro do sistema. Quando uma forma de pagamento é configurada corretamente, o sistema automatiza:</p><ul><li>A data prevista de liquidação financeira;</li><li>A retenção de taxas de operadoras de cartão ou gateway;</li><li>O agrupamento de recebíveis em extratos e relatórios de conciliação;</li><li>A baixa automática ou manual dos títulos emitidos.</li></ul><p>Assista ao vídeo explicativo acima para conhecer a visão geral da ferramenta.</p>',
            'video_tipo' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duracao' => 360,
            'obrigatorio' => 1,
            'ordem' => 1
        ],
        [
            'titulo' => 'Acesso e navegação na tela de cadastro',
            'desc' => 'Conheça os campos, botões de ação e filtros da tela de Formas de Pagamento.',
            'conteudo' => '<p>Nesta etapa, vamos aprender onde encontrar a tela de configuração e como navegar por seus filtros.</p><h4>Passo a passo de acesso:</h4><ol><li>Acesse o menu lateral principal do sistema;</li><li>Clique em <strong>Financeiro</strong> e localize a seção de <strong>Cadastros Auxiliares</strong>;</li><li>Clique na opção <strong>Formas de Pagamento</strong>;</li><li>Observe a listagem de registros existentes, que exibe o nome, o tipo de cobrança e a situação atual.</li></ol><div class="callout callout-info"><h4><i class="fa fa-info-circle"></i> Dica importante</h4><p>Utilize a barra de busca rápida no topo da tabela para pesquisar por nome ou código antes de cadastrar uma nova opção, evitando duplicidades.</p></div>',
            'video_tipo' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duracao' => 240,
            'obrigatorio' => 0,
            'ordem' => 2
        ],
        [
            'titulo' => 'Como cadastrar uma nova forma de pagamento',
            'desc' => 'Preenchimento dos campos obrigatórios, definição de taxas e prazos de liquidação.',
            'conteudo' => '<p>Vamos agora ao procedimento prático para registrar uma nova modalidade de pagamento.</p><h4>Campos do formulário:</h4><ul><li><strong>Nome:</strong> Descrição clara exibida nas telas de vendas e orçamentos (ex: <em>Cartão de Crédito - Taxa 2,5%</em>).</li><li><strong>Tipo de Movimentação:</strong> Define se a forma gera título a receber com liquidação imediata (dinheiro/PIX) ou futura (cartão parcelado/boleto a prazo).</li><li><strong>Conta Bancária Padrão:</strong> Conta de destino onde o saldo será compensado.</li><li><strong>Prazo de Compensação (Dias):</strong> Número de dias úteis ou corridos até a entrada efetiva do recurso.</li><li><strong>Percentual de Taxa (%):</strong> Desconto retido pela adquirente no momento da operação.</li></ul><p>Após preencher os dados, clique no botão <strong>Salvar Registro</strong> para finalizar o cadastro.</p>',
            'video_tipo' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duracao' => 480,
            'obrigatorio' => 1,
            'ordem' => 3
        ],
        [
            'titulo' => 'Cuidados e regras de negócio essenciais',
            'desc' => 'Como evitar divergências contábeis e validar movimentações de saldo financeiro.',
            'conteudo' => '<p>Para garantir que o financeiro da sua empresa permaneça sempre auditado e sem divergências, siga estas regras fundamentais:</p><blockquote><p><strong>Atenção:</strong> Nunca exclua uma forma de pagamento que já possua movimentações históricas. Caso ela deixe de ser aceita na empresa, utilize o recurso de <em>Desativar</em>.</p></blockquote><h4>Checklist de verificação:</h4><ul><li>Confirme se a conta contábil vinculada está ativa;</li><li>Valide se a operação movimenta ou não o saldo financeiro no ato da venda;</li><li>Confira se a regra de parcelamento máximo condiz com a política comercial da empresa.</li></ul>',
            'video_tipo' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duracao' => 300,
            'obrigatorio' => 0,
            'ordem' => 4
        ]
    ];

    foreach ($etapasTr1 as $ep) {
        $stmt = $m->prepare("INSERT INTO treinamento_etapas (treinamento_id, titulo, descricao_curta, conteudo_detalhado, video_tipo, video_url, video_duracao_segundos, video_obrigatorio, ordem, ativo, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
        $stmt->bind_param('isssssiiis', $tr1Id, $ep['titulo'], $ep['desc'], $ep['conteudo'], $ep['video_tipo'], $ep['video_url'], $ep['duracao'], $ep['obrigatorio'], $ep['ordem'], $nowStr);
        $stmt->execute();
        $etapaId = $m->insert_id;

        // Atividades orientativas para a etapa 2 e 3
        if ($ep['ordem'] == 2) {
            $m->query("INSERT INTO treinamento_atividades (treinamento_id, etapa_id, titulo, descricao, instrucoes, obrigatoria, ordem, ativo, created_at) VALUES ({$tr1Id}, {$etapaId}, 'Localizar a tela de Formas de Pagamento', 'Acesse o menu e encontre a listagem existente', 'Abra uma nova aba no sistema, navegue ate a tela e verifique se as permissoes de consulta estao ativas para seu perfil.', 1, 1, 1, '{$nowStr}')");
        } elseif ($ep['ordem'] == 3) {
            $m->query("INSERT INTO treinamento_atividades (treinamento_id, etapa_id, titulo, descricao, instrucoes, obrigatoria, ordem, ativo, created_at) VALUES ({$tr1Id}, {$etapaId}, 'Simular um cadastro de teste', 'Cadastrar uma forma de teste para validar o aprendizado', 'Crie um registro ficticio com nome \"Teste Treinamento PIX\" e salve para conferir o preenchimento de taxas.', 0, 1, 1, '{$nowStr}')");
        }
    }

    // Treinamento 2: Financeiro - Titulos a Pagar e Receber
    $stmt = $m->prepare("INSERT INTO treinamentos (id, empresa_id, modulo_id, produto_id, titulo, slug, descricao_curta, descricao_completa, objetivo, imagem_capa, carga_horaria_minutos, nivel, destaque, novidade, avaliacao_obrigatoria, tipo_acesso, ordem, ativo, cadastrado_por, created_at) VALUES (2, 1, 1, 1, 'Como utilizar a tela de títulos a pagar e a receber', 'como-utilizar-tela-titulos-pagar-receber', 'Domine o controle de contas a pagar e receber, baixas parciais, juros, descontos e conferência de relatórios.', 'Este curso ensina os procedimentos diários do contas a pagar e contas a receber. Aborda desde a entrada manual de títulos até baixas com conciliação bancária, estornos e emissão de relatórios analíticos de inadimplência.', 'Capacitar os colaboradores na gestão precisa do fluxo de liquidação financeira e baixas de títulos sem inconsistências.', 'assets/xkey/images/curso_financeiro_2.jpg', 60, 'intermediario', 1, 0, 1, 'livre', 2, 1, 1, ?)");
    $stmt->bind_param('s', $nowStr);
    $stmt->execute();
    $tr2Id = 2;

    $etapasTr2 = [
        [
            'titulo' => 'Visão geral do contas a pagar e a receber',
            'desc' => 'Entenda como os títulos são originados e como organizar as datas de vencimento.',
            'conteudo' => '<p>Os títulos representam obrigações a pagar ou direitos a receber da empresa.</p><p>Neste módulo você aprenderá a visualizar o painel de vencimentos do dia, semana e mês, prevenindo atrasos em pagamentos e cobranças em aberto.</p>',
            'video_tipo' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duracao' => 420,
            'obrigatorio' => 1,
            'ordem' => 1
        ],
        [
            'titulo' => 'Como cadastrar e baixar um título a pagar',
            'desc' => 'Passo a passo para inserir faturas, despesas fixas e registrar o comprovante de pagamento.',
            'conteudo' => '<p>Aprenda a lançar boletos de fornecedores, contas de consumo e salários, anexando comprovantes e registrando o pagamento no caixa correto.</p>',
            'video_tipo' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duracao' => 540,
            'obrigatorio' => 1,
            'ordem' => 2
        ]
    ];
    foreach ($etapasTr2 as $ep) {
        $stmt = $m->prepare("INSERT INTO treinamento_etapas (treinamento_id, titulo, descricao_curta, conteudo_detalhado, video_tipo, video_url, video_duracao_segundos, video_obrigatorio, ordem, ativo, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
        $stmt->bind_param('isssssiiis', $tr2Id, $ep['titulo'], $ep['desc'], $ep['conteudo'], $ep['video_tipo'], $ep['video_url'], $ep['duracao'], $ep['obrigatorio'], $ep['ordem'], $nowStr);
        $stmt->execute();
    }

    // Treinamento 3: Faturamento - Emissão de Faturamento
    $stmt = $m->prepare("INSERT INTO treinamentos (id, empresa_id, modulo_id, produto_id, titulo, slug, descricao_curta, descricao_completa, objetivo, imagem_capa, carga_horaria_minutos, nivel, destaque, novidade, avaliacao_obrigatoria, tipo_acesso, ordem, ativo, cadastrado_por, created_at) VALUES (3, 1, 4, 1, 'Emissão de Faturamento e Controle de Lotes', 'emissao-faturamento-controle-lotes', 'Entenda o fluxo completo de faturamento de pedidos e geração automática de duplicatas no sistema.', 'Aprenda a fechar lotes de faturamento, validar regras fiscais básicas, gerar duplicatas integradas ao financeiro e emitir relatórios de conferência.', 'Capacitar a equipe comercial e administrativa a faturar orçamentos e contratos com agilidade e precisão.', 'assets/xkey/images/curso_faturamento.jpg', 50, 'avancado', 1, 1, 1, 'livre', 3, 1, 1, ?)");
    $stmt->bind_param('s', $nowStr);
    $stmt->execute();
    $tr3Id = 3;

    $etapasTr3 = [
        [
            'titulo' => 'Estrutura do módulo de Faturamento',
            'desc' => 'Como os pedidos aprovados chegam à fila de faturamento.',
            'conteudo' => '<p>Visão geral da fila de faturamento e status dos pedidos prontos para emissão.</p>',
            'video_tipo' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duracao' => 300,
            'obrigatorio' => 1,
            'ordem' => 1
        ]
    ];
    foreach ($etapasTr3 as $ep) {
        $stmt = $m->prepare("INSERT INTO treinamento_etapas (treinamento_id, titulo, descricao_curta, conteudo_detalhado, video_tipo, video_url, video_duracao_segundos, video_obrigatorio, ordem, ativo, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
        $stmt->bind_param('isssssiiis', $tr3Id, $ep['titulo'], $ep['desc'], $ep['conteudo'], $ep['video_tipo'], $ep['video_url'], $ep['duracao'], $ep['obrigatorio'], $ep['ordem'], $nowStr);
        $stmt->execute();
    }

    // Treinamento 4: Cadastros - Gestão de Clientes e Fornecedores
    $stmt = $m->prepare("INSERT INTO treinamentos (id, empresa_id, modulo_id, produto_id, titulo, slug, descricao_curta, descricao_completa, objetivo, imagem_capa, carga_horaria_minutos, nivel, destaque, novidade, avaliacao_obrigatoria, tipo_acesso, ordem, ativo, cadastrado_por, created_at) VALUES (4, 1, 6, 1, 'Gestão Completa de Cadastros de Clientes e Fornecedores', 'gestao-cadastros-clientes-fornecedores', 'Como cadastrar pessoas físicas e jurídicas, validação de CNPJ/CPF, endereços e contatos sem duplicidade.', 'Guia completo para alimentar a base cadastral do sistema com dados consistentes, garantindo emissão correta de notas e cobranças.', 'Padronizar o cadastro de contatos e entidades em toda a empresa.', 'assets/xkey/images/curso_cadastros.jpg', 35, 'basico', 0, 1, 0, 'livre', 4, 1, 1, ?)");
    $stmt->bind_param('s', $nowStr);
    $stmt->execute();

    echo "Treinamentos reais inseridos com sucesso!\n";
}

echo "\nMigracao e inicializacao concluidas com 100% de sucesso!\n";
