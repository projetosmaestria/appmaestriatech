-- Mensagens internas: anexos (imagem, vídeo, áudio ou documento) em uma mensagem.
-- Os arquivos ficam em upload/mensagens/ (acesso direto bloqueado) e são entregues por mensagens_api/arquivo/{id},
-- só para quem participa da conversa. Rode depois de 2026-11-16_mensagens.sql; pode rodar mais de uma vez.
-- Ajuste o nome do banco se for diferente no servidor.
USE xkeysti1;

SET @tabela := 'mensagem';

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'anexo') = 0,
    'ALTER TABLE `mensagem` ADD `anexo` VARCHAR(100) NULL DEFAULT NULL COMMENT ''nome do arquivo gravado em upload/mensagens/''', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'anexo_nome') = 0,
    'ALTER TABLE `mensagem` ADD `anexo_nome` VARCHAR(255) NULL DEFAULT NULL COMMENT ''nome original do arquivo''', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'anexo_tipo') = 0,
    'ALTER TABLE `mensagem` ADD `anexo_tipo` VARCHAR(10) NULL DEFAULT NULL COMMENT ''imagem, video, audio ou arquivo''', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;

SET @sql := IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tabela AND COLUMN_NAME = 'anexo_tamanho') = 0,
    'ALTER TABLE `mensagem` ADD `anexo_tamanho` INT UNSIGNED NULL DEFAULT NULL COMMENT ''bytes''', 'SELECT 1');
PREPARE comando FROM @sql; EXECUTE comando; DEALLOCATE PREPARE comando;
