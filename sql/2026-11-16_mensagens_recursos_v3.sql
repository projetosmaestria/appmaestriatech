-- Mensagens internas (fase 3): reações com emojis, enquetes em canais, reuniões em canais e usuário Bot de chamados.
-- Rode depois de 2026-11-16_mensagens_recursos_v2.sql. Pode rodar mais de uma vez com segurança.
USE xkeysti1;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_bot') = 0,
    'ALTER TABLE `users` ADD `is_bot` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mensagem' AND COLUMN_NAME = 'tipo_conteudo') = 0,
    'ALTER TABLE `mensagem` ADD `tipo_conteudo` VARCHAR(20) NOT NULL DEFAULT ''normal''', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

INSERT INTO `users` (`ip_address`, `username`, `password`, `email`, `created_on`, `active`, `first_name`, `last_name`, `is_bot`)
SELECT '127.0.0.1', 'bot.chamados', '!BOT_SEM_LOGIN!', 'bot.chamados@saxweb.local', UNIX_TIMESTAMP(), 1, 'Bot', 'Chamados', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `is_bot` = 1);

CREATE TABLE IF NOT EXISTS `mensagem_reacao` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `mensagem_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `emoji` VARCHAR(32) NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_reacao` (`mensagem_id`, `user_id`, `emoji`),
    KEY `idx_reacao_msg` (`mensagem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mensagem_enquete` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` INT NOT NULL,
    `canal_id` INT UNSIGNED NOT NULL,
    `mensagem_id` INT UNSIGNED NOT NULL,
    `criador_id` INT NOT NULL,
    `pergunta` VARCHAR(255) NOT NULL,
    `anonima` TINYINT(1) NOT NULL DEFAULT 0,
    `multipla` TINYINT(1) NOT NULL DEFAULT 0,
    `encerrar_em` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_enquete_msg` (`mensagem_id`),
    KEY `idx_enquete_canal` (`canal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_enquete_opcao` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `enquete_id` INT UNSIGNED NOT NULL,
    `texto` VARCHAR(150) NOT NULL,
    `ordem` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_opcao_enquete` (`enquete_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_enquete_voto` (
    `enquete_id` INT UNSIGNED NOT NULL,
    `opcao_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`enquete_id`, `opcao_id`, `user_id`),
    KEY `idx_voto_user` (`enquete_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_reuniao` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` INT NOT NULL,
    `canal_id` INT UNSIGNED NOT NULL,
    `mensagem_id` INT UNSIGNED NOT NULL,
    `criador_id` INT NOT NULL,
    `titulo` VARCHAR(160) NOT NULL,
    `descricao` TEXT NULL DEFAULT NULL,
    `inicio_em` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_reuniao_msg` (`mensagem_id`),
    KEY `idx_reuniao_canal` (`canal_id`, `inicio_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_reuniao_participante` (
    `reuniao_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `status` VARCHAR(15) NOT NULL DEFAULT 'pendente',
    `respondido_em` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`reuniao_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
