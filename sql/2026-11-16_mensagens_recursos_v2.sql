-- Mensagens internas (fase 2): agendamento, rascunhos, marcar como não lida, etiquetas/categorias,
-- lembretes, notas privadas, transcrição de áudio, conversão em chamados, status de disponibilidade,
-- caixa de mídias, ligação por voz, compartilhamento de tela e canais de mensagens.
-- Rode depois de 2026-11-16_mensagens_recursos.sql. Pode rodar mais de uma vez.
USE xkeysti1;

SET @tabela := 'mensagem';

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'transcricao') = 0,
    'ALTER TABLE `mensagem` ADD `transcricao` TEXT NULL DEFAULT NULL', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'chamado_id') = 0,
    'ALTER TABLE `mensagem` ADD `chamado_id` INT NULL DEFAULT NULL', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'canal_id') = 0,
    'ALTER TABLE `mensagem` ADD `canal_id` INT UNSIGNED NULL DEFAULT NULL, ADD KEY `idx_mensagem_canal` (`canal_id`, `id`)', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mensagem_conversa_config' AND COLUMN_NAME = 'marcada_nao_lida') = 0,
    'ALTER TABLE `mensagem_conversa_config` ADD `marcada_nao_lida` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

CREATE TABLE IF NOT EXISTS `mensagem_agendada` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `chave` VARCHAR(24) NOT NULL,
    `texto` TEXT NOT NULL,
    `resposta_id` INT UNSIGNED NULL DEFAULT NULL,
    `mencoes` TEXT NULL DEFAULT NULL,
    `enviar_em` DATETIME NOT NULL,
    `status` VARCHAR(15) NOT NULL DEFAULT 'pendente',
    `mensagem_id` INT UNSIGNED NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_agendada_envio` (`status`, `enviar_em`),
    KEY `idx_agendada_user` (`empresa_id`, `user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_rascunho` (
    `empresa_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `chave` VARCHAR(24) NOT NULL,
    `texto` TEXT NOT NULL,
    `atualizado_em` DATETIME NOT NULL,
    PRIMARY KEY (`empresa_id`, `user_id`, `chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_etiqueta` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `nome` VARCHAR(40) NOT NULL,
    `cor` VARCHAR(7) NOT NULL DEFAULT '#3c8dbc',
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_etiqueta_user` (`empresa_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_conversa_etiqueta` (
    `empresa_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `chave` VARCHAR(24) NOT NULL,
    `etiqueta_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`empresa_id`, `user_id`, `chave`, `etiqueta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_lembrete` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `mensagem_id` INT UNSIGNED NOT NULL,
    `chave` VARCHAR(24) NOT NULL,
    `nota` VARCHAR(255) NOT NULL,
    `lembrar_em` DATETIME NOT NULL,
    `notificado_em` DATETIME NULL DEFAULT NULL,
    `concluido_em` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_lembrete_due` (`user_id`, `concluido_em`, `lembrar_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_nota` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `chave` VARCHAR(24) NOT NULL,
    `texto` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_nota_conversa` (`empresa_id`, `user_id`, `chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_status_usuario` (
    `empresa_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'online',
    `texto` VARCHAR(80) NULL DEFAULT NULL,
    `atualizado_em` DATETIME NOT NULL,
    `visto_por_ultimo` DATETIME NOT NULL,
    PRIMARY KEY (`empresa_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_chamada` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` INT NOT NULL,
    `chave` VARCHAR(24) NOT NULL,
    `iniciada_por` INT NOT NULL,
    `tipo` VARCHAR(15) NOT NULL DEFAULT 'voz',
    `status` VARCHAR(15) NOT NULL DEFAULT 'ativa',
    `created_at` DATETIME NOT NULL,
    `encerrada_em` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_chamada_ativa` (`empresa_id`, `chave`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_chamada_participante` (
    `chamada_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `entrou_em` DATETIME NOT NULL,
    `saiu_em` DATETIME NULL DEFAULT NULL,
    `microfone_mudo` TINYINT(1) NOT NULL DEFAULT 0,
    `compartilhando_tela` TINYINT(1) NOT NULL DEFAULT 0,
    `falando` TINYINT(1) NOT NULL DEFAULT 0,
    `heartbeat_em` DATETIME NOT NULL,
    PRIMARY KEY (`chamada_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_chamada_sinal` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `chamada_id` INT UNSIGNED NOT NULL,
    `de_id` INT NOT NULL,
    `para_id` INT NOT NULL,
    `tipo` VARCHAR(20) NOT NULL,
    `dados` MEDIUMTEXT NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_sinal_destino` (`chamada_id`, `para_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_canal` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` INT NOT NULL,
    `nome` VARCHAR(80) NOT NULL,
    `descricao` VARCHAR(255) NULL DEFAULT NULL,
    `criador_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL,
    `excluido_em` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_canal_empresa` (`empresa_id`, `excluido_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_canal_grupo` (
    `canal_id` INT UNSIGNED NOT NULL,
    `group_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`canal_id`, `group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_canal_escritor` (
    `canal_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    PRIMARY KEY (`canal_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_canal_leitura` (
    `canal_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `ultima_lida_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `ultima_entregue_id` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`canal_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
