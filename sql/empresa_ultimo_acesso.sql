-- Guarda quando o usuário escolheu cada empresa no seletor de empresas.
-- No login, o sistema abre a última empresa escolhida.
-- Ajuste o nome do banco se for diferente no servidor.
USE xkeysti1;

ALTER TABLE empresas_users
  ADD COLUMN ultimo_acesso DATETIME NULL DEFAULT NULL,
  ADD KEY idx_empresas_users_ultimo_acesso (user_id, ultimo_acesso);
