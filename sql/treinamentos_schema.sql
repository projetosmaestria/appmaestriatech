-- ===================================================================
-- MODULO DE TREINAMENTOS - ACADEMY (MAESTRIATECH)
-- Script de criacao de tabelas, indices e dados iniciais
-- ===================================================================

-- 1. Banners em carrossel para o Dashboard da Academy
CREATE TABLE IF NOT EXISTS `treinamento_banners` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresa_id` INT(11) UNSIGNED NOT NULL DEFAULT 1,
  `titulo` VARCHAR(255) NOT NULL,
  `subtitulo` VARCHAR(255) NULL DEFAULT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `imagem` VARCHAR(255) NOT NULL,
  `texto_botao` VARCHAR(100) NULL DEFAULT 'Começar agora',
  `url_botao` VARCHAR(255) NULL DEFAULT NULL,
  `treinamento_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `modulo_id` INT(11) NULL DEFAULT NULL,
  `ordem` INT(11) NOT NULL DEFAULT 0,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `data_inicio` DATETIME NULL DEFAULT NULL,
  `data_fim` DATETIME NULL DEFAULT NULL,
  `cadastrado_por` INT(11) UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tb_empresa_ativo` (`empresa_id`, `ativo`),
  INDEX `idx_tb_ordem` (`ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2. Tabela principal de Treinamentos
CREATE TABLE IF NOT EXISTS `treinamentos` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresa_id` INT(11) UNSIGNED NOT NULL DEFAULT 1,
  `modulo_id` INT(11) NOT NULL,
  `produto_id` INT(11) NULL DEFAULT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NULL DEFAULT NULL,
  `descricao_curta` VARCHAR(500) NULL DEFAULT NULL,
  `descricao_completa` LONGTEXT NULL DEFAULT NULL,
  `objetivo` TEXT NULL DEFAULT NULL,
  `imagem_capa` VARCHAR(255) NULL DEFAULT NULL,
  `carga_horaria_minutos` INT(11) NOT NULL DEFAULT 0,
  `nivel` ENUM('basico', 'intermediario', 'avancado') NOT NULL DEFAULT 'basico',
  `destaque` TINYINT(1) NOT NULL DEFAULT 0,
  `novidade` TINYINT(1) NOT NULL DEFAULT 0,
  `avaliacao_obrigatoria` TINYINT(1) NOT NULL DEFAULT 0,
  `tipo_acesso` ENUM('livre', 'restrito') NOT NULL DEFAULT 'livre',
  `ordem` INT(11) NOT NULL DEFAULT 0,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `cadastrado_por` INT(11) UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tr_modulo` (`modulo_id`),
  INDEX `idx_tr_produto` (`produto_id`),
  INDEX `idx_tr_empresa` (`empresa_id`),
  INDEX `idx_tr_destaque` (`destaque`),
  INDEX `idx_tr_novidade` (`novidade`),
  INDEX `idx_tr_ativo` (`ativo`),
  INDEX `idx_tr_ordem` (`ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 3. Etapas dos Treinamentos
CREATE TABLE IF NOT EXISTS `treinamento_etapas` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `treinamento_id` INT(11) UNSIGNED NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao_curta` VARCHAR(500) NULL DEFAULT NULL,
  `conteudo_detalhado` LONGTEXT NULL DEFAULT NULL,
  `conteudo_html` LONGTEXT NULL DEFAULT NULL,
  `video_tipo` ENUM('youtube', 'vimeo', 'arquivo', 'upload', 'link_direto', 'nenhum') NOT NULL DEFAULT 'nenhum',
  `video_url` VARCHAR(500) NULL DEFAULT NULL,
  `video_duracao_segundos` INT(11) NOT NULL DEFAULT 0,
  `video_obrigatorio` TINYINT(1) NOT NULL DEFAULT 0,
  `ordem` INT(11) NOT NULL DEFAULT 1,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_te_treinamento` (`treinamento_id`, `ordem`),
  CONSTRAINT `fk_te_treinamento` FOREIGN KEY (`treinamento_id`) REFERENCES `treinamentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 4. Arquivos e Materiais de Apoio vinculados as Etapas
CREATE TABLE IF NOT EXISTS `treinamento_arquivos` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `treinamento_id` INT(11) UNSIGNED NOT NULL,
  `etapa_id` INT(11) UNSIGNED NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `nome_original` VARCHAR(255) NOT NULL,
  `nome_arquivo` VARCHAR(255) NOT NULL,
  `extensao` VARCHAR(20) NOT NULL,
  `tamanho_bytes` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `caminho` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_ta_treinamento` (`treinamento_id`),
  INDEX `idx_ta_etapa` (`etapa_id`),
  CONSTRAINT `fk_ta_treinamento` FOREIGN KEY (`treinamento_id`) REFERENCES `treinamentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ta_etapa` FOREIGN KEY (`etapa_id`) REFERENCES `treinamento_etapas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 5. Atividades Orientativas (Checklist de procedimentos praticos)
CREATE TABLE IF NOT EXISTS `treinamento_atividades` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `treinamento_id` INT(11) UNSIGNED NOT NULL,
  `etapa_id` INT(11) UNSIGNED NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `instrucoes` TEXT NULL DEFAULT NULL,
  `obrigatoria` TINYINT(1) NOT NULL DEFAULT 0,
  `ordem` INT(11) NOT NULL DEFAULT 1,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tat_treinamento` (`treinamento_id`),
  INDEX `idx_tat_etapa` (`etapa_id`),
  CONSTRAINT `fk_tat_treinamento` FOREIGN KEY (`treinamento_id`) REFERENCES `treinamentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tat_etapa` FOREIGN KEY (`etapa_id`) REFERENCES `treinamento_etapas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 6. Liberacao de Acessos Individuais ou por Perfil
CREATE TABLE IF NOT EXISTS `treinamento_permissoes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `treinamento_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `group_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tp_treinamento` (`treinamento_id`),
  INDEX `idx_tp_user` (`user_id`),
  INDEX `idx_tp_group` (`group_id`),
  CONSTRAINT `fk_tp_treinamento` FOREIGN KEY (`treinamento_id`) REFERENCES `treinamentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 7. Progresso Geral do Usuario no Treinamento
CREATE TABLE IF NOT EXISTS `treinamento_progresso` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `treinamento_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `etapas_concluidas` INT(11) NOT NULL DEFAULT 0,
  `ultima_etapa_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `etapa_atual_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `total_etapas` INT(11) NOT NULL DEFAULT 0,
  `percentual` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('nao_iniciado', 'em_andamento', 'concluido') NOT NULL DEFAULT 'nao_iniciado',
  `data_inicio` DATETIME NULL DEFAULT NULL,
  `data_conclusao` DATETIME NULL DEFAULT NULL,
  `ultima_atividade` DATETIME NULL DEFAULT NULL,
  `ultima_acao` VARCHAR(255) NULL DEFAULT NULL,
  `detalhes_log` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_tprog_user_treinamento` (`treinamento_id`, `user_id`),
  INDEX `idx_tprog_user` (`user_id`),
  INDEX `idx_tprog_status` (`status`),
  CONSTRAINT `fk_tprog_treinamento` FOREIGN KEY (`treinamento_id`) REFERENCES `treinamentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 8. Progresso do Usuario por Etapa
CREATE TABLE IF NOT EXISTS `treinamento_etapa_progresso` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `treinamento_id` INT(11) UNSIGNED NOT NULL,
  `etapa_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `video_tempo_assistido` INT(11) NOT NULL DEFAULT 0,
  `video_segundos_assistidos` INT(11) NOT NULL DEFAULT 0,
  `video_concluido` TINYINT(1) NOT NULL DEFAULT 0,
  `atividades_concluidas` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('nao_iniciado', 'em_andamento', 'concluido') NOT NULL DEFAULT 'nao_iniciado',
  `data_conclusao` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_teprog_user_etapa` (`etapa_id`, `user_id`),
  INDEX `idx_teprog_user_treinamento` (`user_id`, `treinamento_id`),
  CONSTRAINT `fk_teprog_treinamento` FOREIGN KEY (`treinamento_id`) REFERENCES `treinamentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_teprog_etapa` FOREIGN KEY (`etapa_id`) REFERENCES `treinamento_etapas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 9. Progresso das Atividades Orientativas por Usuario
CREATE TABLE IF NOT EXISTS `treinamento_atividade_progresso` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `atividade_id` INT(11) UNSIGNED NOT NULL,
  `etapa_id` INT(11) UNSIGNED NOT NULL,
  `treinamento_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `concluida` TINYINT(1) NOT NULL DEFAULT 0,
  `data_conclusao` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_tatprog_user_atividade` (`atividade_id`, `user_id`),
  INDEX `idx_tatprog_user_etapa` (`user_id`, `etapa_id`),
  CONSTRAINT `fk_tatprog_atividade` FOREIGN KEY (`atividade_id`) REFERENCES `treinamento_atividades` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 10. Avaliacoes de Satisfacao do Treinamento e Etapas (1 a 5 estrelas + sugestao)
CREATE TABLE IF NOT EXISTS `treinamento_avaliacoes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `treinamento_id` INT(11) UNSIGNED NOT NULL,
  `etapa_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `nota` TINYINT(1) UNSIGNED NOT NULL COMMENT '1=Muito ruim, 2=Ruim, 3=Regular, 4=Bom, 5=Excelente',
  `comentario` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_taval_user_etapa` (`etapa_id`, `user_id`),
  INDEX `idx_taval_treinamento` (`treinamento_id`),
  INDEX `idx_taval_etapa` (`etapa_id`),
  INDEX `idx_taval_nota` (`nota`),
  CONSTRAINT `fk_taval_treinamento` FOREIGN KEY (`treinamento_id`) REFERENCES `treinamentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
