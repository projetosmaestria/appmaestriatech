CREATE TABLE IF NOT EXISTS `kanban_colaborador_discord` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `colaborador_id` int NOT NULL,
  `discord_id` varchar(20) NOT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kanban_colaborador_discord` (`empresa_id`, `colaborador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kanban_discord_envio` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `colaborador_id` int NOT NULL,
  `data` date NOT NULL,
  `enviado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kanban_discord_envio` (`empresa_id`, `colaborador_id`, `data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
