CREATE TABLE IF NOT EXISTS `kanban_tarefa_planejamento` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `chamado_id` int NOT NULL,
  `data_prevista` date NOT NULL,
  `created_by` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kanban_planejamento_chamado` (`empresa_id`, `chamado_id`),
  KEY `idx_kanban_planejamento_dia` (`empresa_id`, `data_prevista`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kanban_dashboard_acesso` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `visualizador_id` int NOT NULL,
  `colaborador_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kanban_dashboard_acesso` (`empresa_id`, `visualizador_id`, `colaborador_id`),
  KEY `idx_kanban_dashboard_colaborador` (`empresa_id`, `colaborador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kanban_dashboard_meta` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `colaborador_id` int NOT NULL,
  `horas_meta` decimal(5,2) NOT NULL DEFAULT 8.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kanban_dashboard_meta` (`empresa_id`, `colaborador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kanban_indisponibilidade` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `colaborador_id` int DEFAULT NULL,
  `tipo` enum('feriado','ferias','indisponivel') NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kanban_indisponibilidade_periodo` (`empresa_id`, `ativo`, `data_inicio`, `data_fim`),
  KEY `idx_kanban_indisponibilidade_colaborador` (`empresa_id`, `colaborador_id`, `ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
