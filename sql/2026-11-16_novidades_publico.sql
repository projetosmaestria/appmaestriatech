-- Novidades: público por unidade e por grupo de usuários.
-- Sem unidade escolhida: aparece para todos. Com unidade(s): admins e usuários vinculados a elas (user_unidade).
-- Sem grupo escolhido: aparece para todos. Com grupo(s): só usuários desses grupos (users_groups).
-- Quando as duas opções são usadas, o usuário precisa atender às duas.
-- Ajuste o nome do banco se for diferente no servidor.
USE xkeysti1;

CREATE TABLE IF NOT EXISTS `novidades_unidades` (
    `novidade_id` INT UNSIGNED NOT NULL,
    `unidade_id` INT NOT NULL,
    PRIMARY KEY (`novidade_id`, `unidade_id`),
    KEY `idx_novidades_unidades_unidade` (`unidade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `novidades_grupos` (
    `novidade_id` INT UNSIGNED NOT NULL,
    `group_id` INT NOT NULL,
    PRIMARY KEY (`novidade_id`, `group_id`),
    KEY `idx_novidades_grupos_grupo` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
