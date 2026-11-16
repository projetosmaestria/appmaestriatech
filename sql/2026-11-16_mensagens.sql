-- Mensagens internas: conversas 1:1 entre usuários da mesma empresa (tela Mensagens, envelope e sino).
-- Acesso pelos grupos marcados em Preferências > "Grupos com acesso às mensagens"; sem nada marcado,
-- valem o grupo admin e os grupos desenvolvedor, financeiro, supervisor e suporte.
-- Ajuste o nome do banco se for diferente no servidor.
USE xkeysti1;

CREATE TABLE IF NOT EXISTS `mensagem` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` INT NOT NULL,
    `de_id` INT NOT NULL,
    `para_id` INT NOT NULL,
    `texto` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL,
    `lida_em` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_mensagem_recebidas` (`empresa_id`, `para_id`, `lida_em`),
    KEY `idx_mensagem_conversa` (`empresa_id`, `de_id`, `para_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Grupos liberados (array serializado de ids, como grupos_dev_chamado). NULL = regra padrão por nome.
SET @existe := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_preferences' AND COLUMN_NAME = 'grupos_mensagens');
SET @sql := IF(@existe = 0, 'ALTER TABLE `admin_preferences` ADD `grupos_mensagens` TEXT NULL DEFAULT NULL', 'SELECT 1');
PREPARE comando FROM @sql;
EXECUTE comando;
DEALLOCATE PREPARE comando;

-- Menu "Mensagens" na navegação principal, habilitado em todas as empresas.
-- Ele só aparece para quem tem acesso às mensagens (regra em menu_helper, não nas permissões por grupo).
INSERT INTO `menus` (`nome`, `tipo`, `icon_class`, `pai_id`, `ativo`, `order`, `controlador`, `metodo`)
SELECT 'Mensagens', 0, '<i class="fa fa-comments-o"></i>', NULL, 1, 5, 'mensagens', 'index'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `controlador` = 'mensagens' AND `metodo` = 'index');

INSERT INTO `menus_empresas` (`menus_id`, `empresas_id`)
SELECT m.`id`, e.`id`
FROM `menus` m
JOIN `empresas` e
WHERE m.`controlador` = 'mensagens' AND m.`metodo` = 'index'
  AND NOT EXISTS (SELECT 1 FROM `menus_empresas` me WHERE me.`menus_id` = m.`id` AND me.`empresas_id` = e.`id`);
