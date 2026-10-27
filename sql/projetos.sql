-- Renomeia o menu "Plano de Ação" para "Projetos".
-- O controlador, as tabelas e o campo chamado.plano_acao permanecem os mesmos.
-- Ajuste o nome do banco se for diferente no servidor.
USE xkeysti1;

-- "id > 0" usa a chave primária para atender ao modo de atualização segura do MySQL Workbench.
UPDATE menus SET nome = 'Projetos' WHERE id > 0 AND controlador = 'plano_acao' AND metodo = 'index';
