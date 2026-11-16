-- Notificações de chamado: avisos na tela e no sino para o solicitante, o responsável e o desenvolvedor
-- quando alguém escala, comenta, anexa, muda o status ou altera o chamado.
-- Uma linha por usuário notificado. exibida_em: o aviso já apareceu na tela; lida_em: o usuário abriu o chamado
-- ou marcou como lida. As lidas há mais de 90 dias são apagadas automaticamente.
-- Ajuste o nome do banco se for diferente no servidor.
USE xkeysti1;

CREATE TABLE IF NOT EXISTS `chamado_notificacao` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `empresa_id` INT NOT NULL,
    `chamado_id` INT NOT NULL,
    `autor_id` INT NULL DEFAULT NULL,
    `tipo` VARCHAR(20) NOT NULL,
    `titulo` VARCHAR(150) NOT NULL,
    `texto` VARCHAR(500) NOT NULL DEFAULT '',
    `created_at` DATETIME NOT NULL,
    `exibida_em` DATETIME NULL DEFAULT NULL,
    `lida_em` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_chamado_notificacao_usuario` (`user_id`, `empresa_id`, `lida_em`),
    KEY `idx_chamado_notificacao_chamado` (`user_id`, `chamado_id`),
    KEY `idx_chamado_notificacao_lida` (`lida_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
