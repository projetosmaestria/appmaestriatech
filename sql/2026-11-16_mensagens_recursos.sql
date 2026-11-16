-- Mensagens internas: confirmação de leitura/entrega, respostas, edição/exclusão,
-- fixação, busca, menções (@), encaminhamento, indicador de digitação, notificações por conversa e favoritos.
-- Rode depois de 2026-11-16_mensagens.sql, 2026-11-16_mensagens_anexos.sql e 2026-11-16_mensagens_grupos.sql.
-- Pode rodar mais de uma vez; ajuste o nome do banco se for diferente no servidor.
USE xkeysti1;

SET @tabela := 'mensagem';

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'entregue_em') = 0,
    'ALTER TABLE `mensagem` ADD `entregue_em` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'resposta_id') = 0,
    'ALTER TABLE `mensagem` ADD `resposta_id` INT UNSIGNED NULL DEFAULT NULL, ADD KEY `idx_mensagem_resposta` (`resposta_id`)', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'editada_em') = 0,
    'ALTER TABLE `mensagem` ADD `editada_em` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'excluida_em') = 0,
    'ALTER TABLE `mensagem` ADD `excluida_em` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'encaminhada') = 0,
    'ALTER TABLE `mensagem` ADD `encaminhada` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'fixada_em') = 0,
    'ALTER TABLE `mensagem` ADD `fixada_em` DATETIME NULL DEFAULT NULL, ADD KEY `idx_mensagem_fixada` (`empresa_id`, `fixada_em`)', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'fixada_por') = 0,
    'ALTER TABLE `mensagem` ADD `fixada_por` INT NULL DEFAULT NULL', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mensagem_grupo_membro' AND COLUMN_NAME = 'ultima_entregue_id') = 0,
    'ALTER TABLE `mensagem_grupo_membro` ADD `ultima_entregue_id` INT UNSIGNED NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

CREATE TABLE IF NOT EXISTS `mensagem_leitura` (
    `mensagem_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `entregue_em` DATETIME NULL DEFAULT NULL,
    `lida_em` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`mensagem_id`, `user_id`),
    KEY `idx_mensagem_leitura_user` (`user_id`, `lida_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_oculta` (
    `mensagem_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `oculta_em` DATETIME NOT NULL,
    PRIMARY KEY (`mensagem_id`, `user_id`),
    KEY `idx_mensagem_oculta_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_favorita` (
    `mensagem_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`mensagem_id`, `user_id`),
    KEY `idx_mensagem_favorita_user` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_mencao` (
    `mensagem_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `empresa_id` INT NOT NULL,
    `lida_em` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`mensagem_id`, `user_id`),
    KEY `idx_mensagem_mencao_user` (`empresa_id`, `user_id`, `lida_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_digitando` (
    `empresa_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `chave` VARCHAR(24) NOT NULL,
    `estado` VARCHAR(15) NOT NULL DEFAULT 'digitando',
    `atualizado_em` DATETIME NOT NULL,
    PRIMARY KEY (`empresa_id`, `user_id`),
    KEY `idx_mensagem_digitando_chave` (`empresa_id`, `chave`, `atualizado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_conversa_config` (
    `empresa_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `chave` VARCHAR(24) NOT NULL,
    `modo_notif` VARCHAR(20) NOT NULL DEFAULT 'todas',
    `silenciada_ate` DATETIME NULL DEFAULT NULL,
    `atualizado_em` DATETIME NOT NULL,
    PRIMARY KEY (`empresa_id`, `user_id`, `chave`),
    KEY `idx_mensagem_config_user` (`empresa_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
