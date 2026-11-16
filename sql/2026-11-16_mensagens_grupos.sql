-- Mensagens internas: grupos de conversa (3 ou mais participantes, contando o criador).
-- Cada participante guarda até qual mensagem leu (ultima_lida_id). Quem sai ou é removido ganha saiu_em e perde o acesso.
-- Grupo excluído pelo criador fica com excluido_em preenchido (some para todos; o histórico fica no banco).
-- Rode depois de 2026-11-16_mensagens.sql e 2026-11-16_mensagens_anexos.sql; pode rodar mais de uma vez.
-- Ajuste o nome do banco se for diferente no servidor.
USE xkeysti1;

CREATE TABLE IF NOT EXISTS `mensagem_grupo` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` INT NOT NULL,
    `nome` VARCHAR(80) NOT NULL,
    `criador_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL,
    `excluido_em` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_mensagem_grupo_empresa` (`empresa_id`, `excluido_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensagem_grupo_membro` (
    `grupo_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `entrou_em` DATETIME NOT NULL,
    `saiu_em` DATETIME NULL DEFAULT NULL,
    `ultima_lida_id` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`grupo_id`, `user_id`),
    KEY `idx_mensagem_grupo_membro_user` (`user_id`, `saiu_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Mensagem de grupo: grupo_id preenchido e para_id = 0. sistema = 1 para avisos como "Fulano saiu do grupo".
SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mensagem' AND COLUMN_NAME = 'grupo_id') = 0,
    'ALTER TABLE `mensagem` ADD `grupo_id` INT UNSIGNED NULL DEFAULT NULL, ADD KEY `idx_mensagem_grupo` (`grupo_id`, `id`)', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mensagem' AND COLUMN_NAME = 'sistema') = 0,
    'ALTER TABLE `mensagem` ADD `sistema` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;
