<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mensagens internas: conversas diretas (1:1) e grupos (3 ou mais participantes), com anexos,
 * áudio, emojis, confirmação de leitura/entrega, respostas, edição/exclusão, fixação, busca,
 * menções (@), encaminhamento, indicador de digitação, notificações por conversa e favoritos.
 *
 * Só participam os usuários ATIVOS dos grupos liberados em Preferências > "Grupos com acesso às mensagens".
 * Sem nada configurado, valem o grupo admin da empresa e os grupos chamados desenvolvedor, financeiro,
 * supervisor e suporte (o nome é comparado sem acento e sem diferenciar maiúsculas; "Suporte N1" também conta).
 * Os grupos "Apontamentos" e "Clientes" entram sempre, com ou sem configuração.
 *
 * Clientes (usuário só do grupo "Clientes"): veem apenas clientes vinculados a uma mesma unidade (user_unidade),
 * o bot e um contato "Suporte". O "Suporte" é a conversa direta com um atendente (users.suporte_ativo = 'S'),
 * mas o cliente não escolhe nem vê qual: o nome e a foto aparecem como "Suporte". Ver agente_suporte().
 *
 * Cada conversa tem uma chave: "u{id}" para a conversa direta com o usuário e "g{id}" para o grupo.
 * Mensagem direta: para_id = destinatário e grupo_id NULL. Mensagem de grupo: grupo_id preenchido e para_id = 0.
 *
 * Emojis: a conexão com o banco é utf8 (3 bytes) e não grava caracteres de 4 bytes. Eles são guardados como
 * entidade numérica (&#128512;) e voltam ao normal na leitura (codificar_emojis / decodificar_emojis).
 */
class Mensagens_model extends CI_Model
{
    const TAMANHO_MAXIMO = 4000;
    const PASTA_ANEXOS = 'upload/mensagens/';
    const MINIMO_GRUPO = 3; // participantes, contando o criador
    const TAMANHO_NOME_GRUPO = 80;
    const JANELA_DIGITANDO_SEG = 6;

    // Início do nome dos grupos liberados por padrão (sem acento, minúsculo).
    private $grupos_padrao = array('admin', 'desenvolv', 'financ', 'supervis', 'suport');
    // Grupos que entram sempre, mesmo com "Grupos com acesso às mensagens" configurado.
    private $grupos_sempre = array('apont', 'client');
    // Início do nome do grupo de clientes (sem acento, minúsculo).
    private $inicio_grupo_cliente = 'client';
    // Depois de tanto tempo sem conversa, o "Suporte" do cliente volta a ser distribuído entre os atendentes ativos.
    const HORAS_ATENDIMENTO_SUPORTE = 24;
    private $cores_autor = array('#3c8dbc', '#00a65a', '#605ca8', '#d81b60', '#f39c12', '#00838f', '#6d4c41', '#c0392b');

    private static $cache_grupos = array();
    private static $cache_grupos_cliente = array();
    private static $cache_perfil = array();
    private static $cache_unidades = array();
    private static $cache_agentes = array();
    private static $cache_agente = array();
    private static $cache_acesso = array();
    private static $cache_tabelas = array();
    private static $cache_colunas = array();

    /* ---------- Estrutura disponível (scripts sql/2026-11-16_mensagens*.sql) ---------- */

    public function disponivel()
    {
        return $this->tem_tabela('mensagem');
    }

    public function aceita_anexos()
    {
        return $this->disponivel() && $this->tem_coluna_msg('anexo');
    }

    public function aceita_grupos()
    {
        return $this->disponivel() && $this->tem_tabela('mensagem_grupo') && $this->tem_coluna_msg('grupo_id');
    }

    public function aceita_recursos()
    {
        return $this->disponivel() && $this->tem_coluna_msg('entregue_em') && $this->tem_tabela('mensagem_leitura');
    }

    public function aceita_canais()
    {
        return $this->disponivel() && $this->tem_tabela('mensagem_canal') && $this->tem_coluna_msg('canal_id');
    }

    private function tem_tabela($tabela)
    {
        if (!isset(self::$cache_tabelas[$tabela])) {
            self::$cache_tabelas[$tabela] = $this->db->table_exists($tabela);
        }
        return self::$cache_tabelas[$tabela];
    }

    private function tem_coluna_msg($coluna)
    {
        if (!isset(self::$cache_colunas[$coluna])) {
            self::$cache_colunas[$coluna] = $this->disponivel() && $this->db->field_exists($coluna, 'mensagem');
        }
        return self::$cache_colunas[$coluna];
    }

    private function tem_coluna($tabela, $coluna)
    {
        $chave = $tabela . '.' . $coluna;
        if (!isset(self::$cache_colunas[$chave])) {
            self::$cache_colunas[$chave] = $this->tem_tabela($tabela) && $this->db->field_exists($coluna, $tabela);
        }
        return self::$cache_colunas[$chave];
    }

    // "g12" => grupo 12; "c4" => canal 4; "u5" ou "5" => conversa direta com o usuário 5.
    public static function ler_chave($chave)
    {
        $chave = trim((string) $chave);
        if (preg_match('/^g(\d+)$/', $chave, $m)) {
            return array('tipo' => 'grupo', 'id' => (int) $m[1]);
        }
        if (preg_match('/^c(\d+)$/', $chave, $m)) {
            return array('tipo' => 'canal', 'id' => (int) $m[1]);
        }
        if (preg_match('/^u?(\d+)$/', $chave, $m) && (int) $m[1] > 0) {
            return array('tipo' => 'direta', 'id' => (int) $m[1]);
        }
        return null;
    }

    /* ---------- Acesso ---------- */

    // Ids dos grupos de usuários da empresa que podem usar as mensagens.
    public function grupos_permitidos($empresa_id)
    {
        $empresa_id = (int) $empresa_id;
        if (isset(self::$cache_grupos[$empresa_id])) {
            return self::$cache_grupos[$empresa_id];
        }
        $grupos = array();
        $pref = $this->db->get_where('admin_preferences', array('empresa_id' => $empresa_id))->row();
        if ($pref && isset($pref->grupos_mensagens) && $pref->grupos_mensagens) {
            $grupos = array_filter(array_map('intval', (array) @unserialize($pref->grupos_mensagens)));
        }
        $inicios = $this->grupos_sempre;
        if (!$grupos) {
            if ($pref && (int) $pref->grupo_admin > 0) {
                $grupos[] = (int) $pref->grupo_admin;
            }
            $inicios = array_merge($this->grupos_padrao, $this->grupos_sempre);
        }
        $rows = $this->db->select('id, name')->where('empresa_id', $empresa_id)->get('groups')->result();
        foreach ($rows as $row) {
            $nome = $this->normalizar($row->name);
            foreach ($inicios as $inicio) {
                if (strpos($nome, $inicio) === 0) {
                    $grupos[] = (int) $row->id;
                    break;
                }
            }
        }
        // Só valem grupos da própria EMPRESA: um id de grupo de outra empresa gravado nas preferências
        // (formulário manipulado ou dado antigo) faria usuários de outra empresa aparecerem aqui.
        self::$cache_grupos[$empresa_id] = $this->grupos_da_empresa($grupos, $empresa_id);
        return self::$cache_grupos[$empresa_id];
    }

    // Mantém só os ids de grupos (tabela groups) que pertencem à EMPRESA informada.
    private function grupos_da_empresa($grupos_ids, $empresa_id)
    {
        $grupos_ids = array_values(array_unique(array_filter(array_map('intval', (array) $grupos_ids))));
        if (!$grupos_ids) {
            return array();
        }
        $da_empresa = array();
        $rows = $this->db->select('id')->where('empresa_id', (int) $empresa_id)->where_in('id', $grupos_ids)->get('groups')->result();
        foreach ($rows as $row) {
            $da_empresa[] = (int) $row->id;
        }
        return $da_empresa;
    }

    public function tem_acesso($user_id, $empresa_id)
    {
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        $chave = $empresa_id . '-' . $user_id;
        if (isset(self::$cache_acesso[$chave])) {
            return self::$cache_acesso[$chave];
        }
        $grupos = $this->grupos_permitidos($empresa_id);
        $ok = false;
        if ($user_id > 0 && $grupos && $this->disponivel()) {
            $ok = $this->db->join('users u', 'u.id = ug.user_id')
                ->where('ug.user_id', $user_id)
                ->where_in('ug.group_id', $grupos)
                ->where('u.active', 1)
                ->count_all_results('users_groups ug') > 0;
        }
        self::$cache_acesso[$chave] = $ok;
        return $ok;
    }

    /* ---------- Clientes e atendimento "Suporte" ---------- */

    // Ids dos grupos "Clientes" da empresa.
    public function grupos_cliente($empresa_id)
    {
        $empresa_id = (int) $empresa_id;
        if (!isset(self::$cache_grupos_cliente[$empresa_id])) {
            $ids = array();
            $rows = $this->db->select('id, name')->where('empresa_id', $empresa_id)->get('groups')->result();
            foreach ($rows as $row) {
                if (strpos($this->normalizar($row->name), $this->inicio_grupo_cliente) === 0) {
                    $ids[] = (int) $row->id;
                }
            }
            self::$cache_grupos_cliente[$empresa_id] = $ids;
        }
        return self::$cache_grupos_cliente[$empresa_id];
    }

    /**
     * Perfil de cada usuário dentro das mensagens: 'cliente' quando, entre os grupos liberados, ele só está em
     * grupos "Clientes"; 'equipe' quando está em qualquer outro grupo liberado. Quem não tem grupo liberado fica fora.
     */
    private function perfis($user_ids, $empresa_id)
    {
        $empresa_id = (int) $empresa_id;
        $saida = array();
        $buscar = array();
        foreach (array_unique(array_map('intval', (array) $user_ids)) as $uid) {
            if ($uid <= 0) {
                continue;
            }
            $chave = $empresa_id . '-' . $uid;
            if (array_key_exists($chave, self::$cache_perfil)) {
                if (self::$cache_perfil[$chave] !== null) {
                    $saida[$uid] = self::$cache_perfil[$chave];
                }
            } else {
                $buscar[] = $uid;
            }
        }
        if (!$buscar) {
            return $saida;
        }
        $grupos = $this->grupos_permitidos($empresa_id);
        $achados = array();
        if ($grupos) {
            $clientes = array_flip($this->grupos_cliente($empresa_id));
            foreach (array_chunk($buscar, 500) as $lote) {
                $rows = $this->db->select('user_id, group_id')->where_in('user_id', $lote)->where_in('group_id', $grupos)->get('users_groups')->result();
                foreach ($rows as $row) {
                    $uid = (int) $row->user_id;
                    $eh_grupo_cliente = isset($clientes[(int) $row->group_id]);
                    // Qualquer grupo liberado que não seja de clientes faz a pessoa ser da equipe.
                    if (!isset($achados[$uid]) || !$eh_grupo_cliente) {
                        $achados[$uid] = $eh_grupo_cliente ? 'cliente' : 'equipe';
                    }
                }
            }
        }
        foreach ($buscar as $uid) {
            $perfil = isset($achados[$uid]) ? $achados[$uid] : null;
            self::$cache_perfil[$empresa_id . '-' . $uid] = $perfil;
            if ($perfil !== null) {
                $saida[$uid] = $perfil;
            }
        }
        return $saida;
    }

    /**
     * Quem pode abrir chamado a partir do atendimento ao cliente nas Mensagens: atendente do suporte
     * (users.suporte_ativo = 'S'), grupo admin ou grupo de desenvolvedores da empresa. Cliente nunca.
     * (A permissão chamados/create é conferida à parte, no controller.)
     */
    public function pode_abrir_chamado_atendimento($user_id, $empresa_id)
    {
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        if ($user_id <= 0 || !$this->tem_acesso($user_id, $empresa_id) || $this->eh_cliente($user_id, $empresa_id)) {
            return false;
        }
        if ($this->tem_coluna('users', 'suporte_ativo')
            && $this->db->where('id', $user_id)->where('suporte_ativo', 'S')->count_all_results('users') > 0) {
            return true;
        }
        $grupos = $this->db->select('g.name')
            ->join('groups g', 'g.id = ug.group_id')
            ->where('ug.user_id', $user_id)
            ->where('g.empresa_id', $empresa_id)
            ->get('users_groups ug')
            ->result();
        foreach ($grupos as $g) {
            $nome = $this->normalizar($g->name);
            if ($nome === 'admin' || strpos($nome, 'administrador') === 0 || strpos($nome, 'desenvolv') === 0) {
                return true;
            }
        }
        return false;
    }

    // Unidades da empresa vinculadas ao usuário (id, unidade, razao_social), em ordem de nome.
    public function unidades_do_cliente($user_id, $empresa_id)
    {
        $ids = $this->unidades_usuario($user_id, $empresa_id);
        if (!$ids) {
            return array();
        }
        return $this->db->select('id, unidade, razao_social')
            ->where_in('id', $ids)
            ->where('empresa_id', (int) $empresa_id)
            ->order_by('unidade', 'ASC')
            ->get('unidades')
            ->result();
    }

    public function eh_cliente($user_id, $empresa_id)
    {
        $perfis = $this->perfis(array((int) $user_id), $empresa_id);
        return isset($perfis[(int) $user_id]) && $perfis[(int) $user_id] === 'cliente';
    }

    // Unidades da EMPRESA às quais o usuário está vinculado.
    private function unidades_usuario($user_id, $empresa_id)
    {
        $chave = (int) $empresa_id . '-' . (int) $user_id;
        if (!isset(self::$cache_unidades[$chave])) {
            $ids = array();
            $rows = $this->db->select('uu.unidade_id')
                ->join('unidades un', 'un.id = uu.unidade_id')
                ->where('uu.user_id', (int) $user_id)
                ->where('un.empresa_id', (int) $empresa_id)
                ->get('user_unidade uu')
                ->result();
            foreach ($rows as $row) {
                $ids[] = (int) $row->unidade_id;
            }
            self::$cache_unidades[$chave] = array_values(array_unique($ids));
        }
        return self::$cache_unidades[$chave];
    }

    // Dos $candidatos, os que têm alguma unidade em comum com o cliente.
    private function clientes_da_mesma_unidade($cliente_id, $candidatos, $empresa_id)
    {
        $candidatos = array_values(array_unique(array_filter(array_map('intval', (array) $candidatos))));
        $unidades = $this->unidades_usuario($cliente_id, $empresa_id);
        if (!$candidatos || !$unidades) {
            return array();
        }
        $ids = array();
        foreach (array_chunk($candidatos, 500) as $lote) {
            $rows = $this->db->distinct()->select('user_id')->where_in('unidade_id', $unidades)->where_in('user_id', $lote)->get('user_unidade')->result();
            foreach ($rows as $row) {
                $ids[] = (int) $row->user_id;
            }
        }
        return $ids;
    }

    // Atendentes ativos (users.suporte_ativo = 'S') da equipe da empresa, em ordem de id.
    public function agentes_suporte($empresa_id)
    {
        $empresa_id = (int) $empresa_id;
        if (isset(self::$cache_agentes[$empresa_id])) {
            return self::$cache_agentes[$empresa_id];
        }
        $agentes = array();
        $grupos = $this->grupos_permitidos($empresa_id);
        if ($grupos && $this->tem_coluna('users', 'suporte_ativo')) {
            $rows = $this->db->distinct()->select('u.id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where_in('ug.group_id', $grupos)
                ->where('u.active', 1)
                ->where('u.suporte_ativo', 'S')
                ->order_by('u.id', 'ASC')
                ->get('users u')
                ->result();
            $ids = array();
            foreach ($rows as $row) {
                $ids[] = (int) $row->id;
            }
            $perfis = $this->perfis($ids, $empresa_id);
            foreach ($ids as $id) {
                if (isset($perfis[$id]) && $perfis[$id] === 'equipe') {
                    $agentes[] = $id;
                }
            }
        }
        self::$cache_agentes[$empresa_id] = $agentes;
        return $agentes;
    }

    // Usuários dos grupos de desenvolvedores da empresa (nome do grupo começa com "desenvolv", sem acento e sem diferenciar maiúsculas).
    public function usuarios_desenvolvedores($empresa_id)
    {
        static $cache = array();
        $empresa_id = (int) $empresa_id;
        if (isset($cache[$empresa_id])) {
            return $cache[$empresa_id];
        }
        $grupos = array();
        $rows = $this->db->select('id, name')->where('empresa_id', $empresa_id)->get('groups')->result();
        foreach ($rows as $row) {
            if (strpos($this->normalizar($row->name), 'desenvolv') === 0) {
                $grupos[] = (int) $row->id;
            }
        }
        $ids = array();
        if ($grupos) {
            $rows = $this->db->distinct()->select('user_id')->where_in('group_id', $grupos)->get('users_groups')->result();
            foreach ($rows as $row) {
                $ids[] = (int) $row->user_id;
            }
        }
        $cache[$empresa_id] = $ids;
        return $ids;
    }

    /**
     * Quem atende o cliente quando ele fala com "Suporte" (0 se não há ninguém).
     * Continua com quem da equipe conversou com ele por último, se essa pessoa ainda é atendente ativa ou se a
     * conversa é das últimas HORAS_ATENDIMENTO_SUPORTE horas. Senão, distribui entre os atendentes ativos
     * (sempre o mesmo para o mesmo cliente enquanto a lista de atendentes não mudar).
     */
    public function agente_suporte($cliente_id, $empresa_id)
    {
        $cliente_id = (int) $cliente_id;
        $empresa_id = (int) $empresa_id;
        $chave = $empresa_id . '-' . $cliente_id;
        if (isset(self::$cache_agente[$chave])) {
            return self::$cache_agente[$chave];
        }
        $agentes = $this->agentes_suporte($empresa_id);
        $agente = 0;
        $sql = 'SELECT IF(m.de_id = ?, m.para_id, m.de_id) AS outro, MAX(m.id) AS ultima_id, MAX(m.created_at) AS ultima_em'
            . ' FROM mensagem m WHERE m.empresa_id = ? AND (m.de_id = ? OR m.para_id = ?) AND m.para_id > 0'
            . $this->sql_so_diretas('m')
            . ' GROUP BY outro ORDER BY ultima_id DESC';
        $rows = $this->db->query($sql, array($cliente_id, $empresa_id, $cliente_id, $cliente_id))->result();
        $outros = array();
        foreach ($rows as $row) {
            $outros[] = (int) $row->outro;
        }
        $perfis = $this->perfis($outros, $empresa_id);
        foreach ($rows as $row) {
            $outro = (int) $row->outro;
            if ($outro === $cliente_id || !isset($perfis[$outro]) || $perfis[$outro] !== 'equipe') {
                continue;
            }
            $recente = strtotime($row->ultima_em) >= time() - self::HORAS_ATENDIMENTO_SUPORTE * 3600;
            if (in_array($outro, $agentes, true) || ($recente && $this->tem_acesso($outro, $empresa_id))) {
                $agente = $outro;
            }
            break;
        }
        if (!$agente && $agentes) {
            $agente = $agentes[$cliente_id % count($agentes)];
        }
        self::$cache_agente[$chave] = $agente;
        return $agente;
    }

    /**
     * Se o usuário pode ter conversa direta com o outro. Equipe fala com todos que têm acesso; cliente só com
     * clientes de uma mesma unidade e com o atendente que agente_suporte() escolheu para ele. O bot é tratado à parte.
     */
    public function pode_conversar($user_id, $outro_id, $empresa_id)
    {
        $user_id = (int) $user_id;
        $outro_id = (int) $outro_id;
        if ($user_id <= 0 || $outro_id <= 0 || $user_id === $outro_id || !$this->tem_acesso($outro_id, $empresa_id)) {
            return false;
        }
        if (!$this->eh_cliente($user_id, $empresa_id)) {
            return true;
        }
        if ($this->eh_cliente($outro_id, $empresa_id)) {
            return count($this->clientes_da_mesma_unidade($user_id, array($outro_id), $empresa_id)) > 0;
        }
        return $outro_id === $this->agente_suporte($user_id, $empresa_id);
    }

    // Para o cliente, qualquer pessoa da equipe aparece como "Suporte".
    public function ver_como_suporte($user_id, $outro_id, $empresa_id)
    {
        if ((int) $outro_id <= 0 || !$this->eh_cliente($user_id, $empresa_id)) {
            return false;
        }
        $perfis = $this->perfis(array((int) $outro_id), $empresa_id);
        return isset($perfis[(int) $outro_id]) && $perfis[(int) $outro_id] === 'equipe';
    }

    private function mascarar_suporte($contato)
    {
        $contato['nome'] = 'Suporte';
        $contato['foto'] = null;
        $contato['is_suporte'] = true;
        $contato['status_texto'] = '';
        return $contato;
    }

    /* ---------- Pessoas e lista de conversas ---------- */

    /**
     * Pessoas com quem o usuário pode conversar, com o resumo da conversa direta com cada uma
     * (usado em "Nova conversa" e na escolha de participantes de grupo).
     */
    public function contatos($user_id, $empresa_id)
    {
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        $grupos = $this->grupos_permitidos($empresa_id);
        if (!$grupos) {
            return array();
        }
        $tem_bot_col = $this->db->field_exists('is_bot', 'users');
        $campo_bot = $tem_bot_col ? 'u.is_bot' : '0 AS is_bot';
        $usuarios = $this->db->select('u.id, u.first_name, u.last_name, ' . $campo_bot . ', ' . $this->campo_foto('u'), false)
            ->distinct()
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where_in('ug.group_id', $grupos)
            ->where('u.active', 1)
            ->where('u.id !=', $user_id)
            ->get('users u')
            ->result();
        // Cliente: só clientes de uma mesma unidade e o atendente do "Suporte" (sem mostrar quem é).
        $sou_cliente = $this->eh_cliente($user_id, $empresa_id);
        $agente_id = 0;
        if ($sou_cliente) {
            $ids = array();
            foreach ($usuarios as $ux) {
                $ids[] = (int) $ux->id;
            }
            $perfis = $this->perfis($ids, $empresa_id);
            $clientes_ids = array();
            foreach ($ids as $id) {
                if (isset($perfis[$id]) && $perfis[$id] === 'cliente') {
                    $clientes_ids[] = $id;
                }
            }
            $vizinhos = array_flip($this->clientes_da_mesma_unidade($user_id, $clientes_ids, $empresa_id));
            $agente_id = $this->agente_suporte($user_id, $empresa_id);
            $filtrados = array();
            foreach ($usuarios as $ux) {
                if (isset($vizinhos[(int) $ux->id]) || (int) $ux->id === $agente_id) {
                    $filtrados[] = $ux;
                }
            }
            $usuarios = $filtrados;
        }
        if ($tem_bot_col) {
            $bots = $this->db->select('id, first_name, last_name, is_bot, ' . $this->campo_foto(''), false)
                ->where('is_bot', 1)
                ->where('active', 1)
                ->where('id !=', $user_id)
                ->get('users')
                ->result();
            if ($bots) {
                $ja_ids = array();
                foreach ($usuarios as $ux) {
                    $ja_ids[(int) $ux->id] = true;
                }
                foreach ($bots as $b) {
                    if (!isset($ja_ids[(int) $b->id])) {
                        $usuarios[] = $b;
                    }
                }
            }
        }
        if (!$usuarios) {
            return array();
        }

        $resumo = $this->resumo_diretas($user_id, $empresa_id);
        $configs = $this->configs_conversa($user_id, $empresa_id);
        $mencoes = $this->mencoes_nao_lidas_por_conversa($user_id, $empresa_id);
        $this->load->model('mensagens_recursos_model');
        $status_map = $this->mensagens_recursos_model->status_usuarios($empresa_id);
        $rascunhos = $this->mensagens_recursos_model->rascunhos_usuario($empresa_id, $user_id);
        $etiquetas = $this->mensagens_recursos_model->etiquetas_por_conversa($empresa_id, $user_id);
        $fotos = $this->fotos($usuarios);
        // Selos "Suporte" e "Desenvolvedor" na escolha de pessoas. Só para a equipe: o cliente não pode saber
        // quem é o atendente por trás do contato "Suporte".
        $atendentes = $sou_cliente ? array() : array_flip($this->agentes_suporte($empresa_id));
        $desenvolvedores = $sou_cliente ? array() : array_flip($this->usuarios_desenvolvedores($empresa_id));
        // Marca quem é cliente (botão "Abrir chamado" do atendimento). Também só para a equipe.
        $perfis_contatos = array();
        if (!$sou_cliente) {
            $ids_contatos = array();
            foreach ($usuarios as $ux) {
                $ids_contatos[] = (int) $ux->id;
            }
            $perfis_contatos = $this->perfis($ids_contatos, $empresa_id);
        }
        $contatos = array();
        foreach ($usuarios as $u) {
            $id = (int) $u->id;
            $eh_bot = !empty($u->is_bot);
            $chave = 'u' . $id;
            $r = isset($resumo[$id]) ? $resumo[$id] : null;
            $cfg = isset($configs[$chave]) ? $configs[$chave] : array('modo_notif' => 'todas', 'silenciada_ate' => null, 'silenciada' => false, 'marcada_nao_lida' => false);
            $st = $eh_bot
                ? array('status' => 'online', 'rotulo' => 'Bot automático', 'texto' => 'Atualizações de chamados')
                : (isset($status_map[$id]) ? $status_map[$id] : array('status' => 'offline', 'rotulo' => 'Offline', 'texto' => ''));
            $contato = array(
                'chave' => $chave,
                'tipo' => 'direta',
                'id' => $id,
                'nome' => $this->nome($u),
                'foto' => $fotos[$id],
                'is_bot' => $eh_bot,
                'is_suporte' => false,
                'suporte_ativo' => isset($atendentes[$id]),
                'desenvolvedor' => isset($desenvolvedores[$id]),
                'cliente' => isset($perfis_contatos[$id]) && $perfis_contatos[$id] === 'cliente',
                'somente_leitura' => $eh_bot,
                'status' => $st['status'],
                'status_rotulo' => $st['rotulo'],
                'status_texto' => $st['texto'],
                'nao_lidas' => $r ? (int) $r['nao_lidas'] : 0,
                'marcada_nao_lida' => !empty($cfg['marcada_nao_lida']),
                'mencoes' => isset($mencoes[$chave]) ? (int) $mencoes[$chave] : 0,
                'silenciada' => $cfg['silenciada'],
                'modo_notif' => $cfg['modo_notif'],
                'rascunho' => isset($rascunhos[$chave]) ? $rascunhos[$chave] : '',
                'etiquetas' => isset($etiquetas[$chave]) ? $etiquetas[$chave] : array(),
                'ultima' => $r ? $r['ultima'] : '',
                'ultima_minha' => $r ? $r['ultima_minha'] : false,
                'ultima_autor' => '',
                'ultima_id' => $r ? (int) $r['ultima_id'] : 0,
                'ultima_em' => $r ? $r['ultima_em'] : null,
                'quando' => $r ? $this->quando($r['ultima_em']) : '',
            );
            $contatos[] = ($sou_cliente && $id === $agente_id) ? $this->mascarar_suporte($contato) : $contato;
        }
        usort($contatos, array($this, 'ordenar_conversas'));
        return $contatos;
    }

    // Lista da tela: conversas diretas que já têm mensagem (ou rascunho/etiqueta/marcada), grupos e canais.
    public function conversas($user_id, $empresa_id, $contatos = null)
    {
        if ($contatos === null) {
            $contatos = $this->contatos($user_id, $empresa_id);
        }
        $lista = array();
        foreach ($contatos as $c) {
            if ($c['ultima_id'] > 0 || $c['rascunho'] !== '' || !empty($c['marcada_nao_lida']) || !empty($c['etiquetas']) || !empty($c['is_bot'])) {
                $lista[] = $c;
            }
        }
        $lista = array_merge($lista, $this->grupos_do_usuario($user_id, $empresa_id), $this->canais_do_usuario($user_id, $empresa_id));
        $this->load->model('mensagens_recursos_model');
        $ult_reacoes = $this->mensagens_recursos_model->ultimas_reacoes_por_conversa($empresa_id, $user_id);
        foreach ($lista as &$item) {
            $ch = $item['chave'];
            if (isset($ult_reacoes[$ch])) {
                $rx = $ult_reacoes[$ch];
                if (empty($item['ultima_em']) || strtotime($rx['created_at']) >= strtotime($item['ultima_em'])) {
                    $item['ultima'] = $rx['texto'];
                    $item['ultima_minha'] = false;
                    $item['ultima_autor'] = '';
                    $item['quando'] = $this->quando($rx['created_at']);
                }
            }
            unset($item['ultima_em']);
        }
        unset($item);
        usort($lista, array($this, 'ordenar_conversas'));
        return $lista;
    }

    public function ordenar_conversas($a, $b)
    {
        if ($a['ultima_id'] !== $b['ultima_id']) {
            return $b['ultima_id'] - $a['ultima_id'];
        }
        return strcasecmp($a['nome'], $b['nome']);
    }

    // Por pessoa: última mensagem direta trocada (visível ao usuário) e quantas dela o usuário ainda não leu.
    private function resumo_diretas($user_id, $empresa_id)
    {
        $filtro_oculta = $this->sql_nao_oculta('m', $user_id);
        $sql = 'SELECT IF(m.de_id = ?, m.para_id, m.de_id) AS outro, MAX(m.id) AS ultima_id,'
            . ' SUM(m.para_id = ? AND m.lida_em IS NULL' . $this->sql_nao_excluida('m') . ') AS nao_lidas'
            . ' FROM mensagem m WHERE m.empresa_id = ? AND (m.de_id = ? OR m.para_id = ?)'
            . $this->sql_so_diretas('m') . $filtro_oculta
            . ' GROUP BY outro';
        $rows = $this->db->query($sql, array($user_id, $user_id, $empresa_id, $user_id, $user_id))->result();
        $resumo = array();
        $ids = array();
        foreach ($rows as $row) {
            $resumo[(int) $row->outro] = array('ultima_id' => (int) $row->ultima_id, 'nao_lidas' => (int) $row->nao_lidas, 'ultima' => '', 'ultima_minha' => false, 'ultima_em' => null);
            $ids[] = (int) $row->ultima_id;
        }
        if ($ids) {
            $ultimas = $this->db->select('id, de_id, para_id, texto, created_at, ' . $this->campos_extras('') . ', ' . $this->campos_anexo(''), false)
                ->where_in('id', $ids)->get('mensagem')->result();
            foreach ($ultimas as $m) {
                $outro = (int) $m->de_id === $user_id ? (int) $m->para_id : (int) $m->de_id;
                if (isset($resumo[$outro])) {
                    $resumo[$outro]['ultima'] = $this->previa($m, 80);
                    $resumo[$outro]['ultima_minha'] = (int) $m->de_id === $user_id;
                    $resumo[$outro]['ultima_em'] = $m->created_at;
                }
            }
        }
        return $resumo;
    }

    // Grupos ativos do usuário, com a última mensagem e as não lidas.
    private function grupos_do_usuario($user_id, $empresa_id)
    {
        if (!$this->aceita_grupos()) {
            return array();
        }
        $user_id = (int) $user_id;
        $filtro_oculta = $this->sql_nao_oculta('m', $user_id);
        $sql = 'SELECT g.id, g.nome, g.criador_id,'
            . ' (SELECT COUNT(*) FROM mensagem_grupo_membro x WHERE x.grupo_id = g.id AND x.saiu_em IS NULL) AS membros,'
            . ' (SELECT MAX(m.id) FROM mensagem m WHERE m.grupo_id = g.id' . $filtro_oculta . ') AS ultima_id,'
            . ' (SELECT COUNT(*) FROM mensagem m WHERE m.grupo_id = g.id AND m.id > gm.ultima_lida_id AND m.de_id <> ? AND m.sistema = 0' . $this->sql_nao_excluida('m') . $filtro_oculta . ') AS nao_lidas'
            . ' FROM mensagem_grupo g'
            . ' JOIN mensagem_grupo_membro gm ON gm.grupo_id = g.id AND gm.user_id = ? AND gm.saiu_em IS NULL'
            . ' WHERE g.empresa_id = ? AND g.excluido_em IS NULL';
        $rows = $this->db->query($sql, array($user_id, $user_id, (int) $empresa_id))->result();
        if (!$rows) {
            return array();
        }
        $ids = array();
        foreach ($rows as $row) {
            if ((int) $row->ultima_id > 0) {
                $ids[] = (int) $row->ultima_id;
            }
        }
        $ultimas = array();
        if ($ids) {
            $msgs = $this->db->select('m.id, m.de_id, m.texto, m.created_at, m.sistema, ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', u.first_name, u.last_name', false)
                ->join('users u', 'u.id = m.de_id', 'left')
                ->where_in('m.id', $ids)
                ->get('mensagem m')
                ->result();
            foreach ($msgs as $m) {
                $ultimas[(int) $m->id] = $m;
            }
        }
        $configs = $this->configs_conversa($user_id, $empresa_id);
        $mencoes = $this->mencoes_nao_lidas_por_conversa($user_id, $empresa_id);
        $this->load->model('mensagens_recursos_model');
        $rascunhos = $this->mensagens_recursos_model->rascunhos_usuario($empresa_id, $user_id);
        $etiquetas = $this->mensagens_recursos_model->etiquetas_por_conversa($empresa_id, $user_id);
        $grupos = array();
        foreach ($rows as $row) {
            $m = isset($ultimas[(int) $row->ultima_id]) ? $ultimas[(int) $row->ultima_id] : null;
            $chave = 'g' . (int) $row->id;
            $cfg = isset($configs[$chave]) ? $configs[$chave] : array('modo_notif' => 'todas', 'silenciada_ate' => null, 'silenciada' => false, 'marcada_nao_lida' => false);
            $grupos[] = array(
                'chave' => $chave,
                'tipo' => 'grupo',
                'id' => (int) $row->id,
                'nome' => $this->decodificar_emojis($row->nome),
                'foto' => null,
                'membros' => (int) $row->membros,
                'nao_lidas' => (int) $row->nao_lidas,
                'marcada_nao_lida' => !empty($cfg['marcada_nao_lida']),
                'mencoes' => isset($mencoes[$chave]) ? (int) $mencoes[$chave] : 0,
                'silenciada' => $cfg['silenciada'],
                'modo_notif' => $cfg['modo_notif'],
                'rascunho' => isset($rascunhos[$chave]) ? $rascunhos[$chave] : '',
                'etiquetas' => isset($etiquetas[$chave]) ? $etiquetas[$chave] : array(),
                'ultima' => $m ? $this->previa($m, 80) : '',
                'ultima_minha' => $m && (int) $m->de_id === $user_id && !$m->sistema && empty($m->excluida_em),
                'ultima_autor' => ($m && !$m->sistema && empty($m->excluida_em) && (int) $m->de_id !== $user_id) ? trim($m->first_name) : '',
                'ultima_id' => (int) $row->ultima_id,
                'ultima_em' => $m ? $m->created_at : null,
                'quando' => $m ? $this->quando($m->created_at) : '',
            );
        }
        return $grupos;
    }

    // Canais visíveis aos grupos do usuário, com última mensagem, não lidas e permissão de envio.
    private function canais_do_usuario($user_id, $empresa_id)
    {
        if (!$this->aceita_canais()) {
            return array();
        }
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        $filtro_oculta = $this->sql_nao_oculta('m', $user_id);
        $sql = 'SELECT c.id, c.nome, c.descricao, c.criador_id,'
            . ' COALESCE(cl.ultima_lida_id, 0) AS ultima_lida_id,'
            . ' (SELECT MAX(m.id) FROM mensagem m WHERE m.canal_id = c.id' . $filtro_oculta . ') AS ultima_id,'
            . ' (SELECT COUNT(*) FROM mensagem m WHERE m.canal_id = c.id AND m.id > COALESCE(cl.ultima_lida_id, 0) AND m.de_id <> ? AND m.sistema = 0' . $this->sql_nao_excluida('m') . $filtro_oculta . ') AS nao_lidas'
            . ' FROM mensagem_canal c'
            . ' LEFT JOIN mensagem_canal_leitura cl ON cl.canal_id = c.id AND cl.user_id = ?'
            . ' WHERE c.empresa_id = ? AND c.excluido_em IS NULL'
            . ' AND (c.criador_id = ? OR EXISTS (SELECT 1 FROM mensagem_canal_grupo cg JOIN users_groups ug ON ug.group_id = cg.group_id WHERE cg.canal_id = c.id AND ug.user_id = ?))';
        $rows = $this->db->query($sql, array($user_id, $user_id, $empresa_id, $user_id, $user_id))->result();
        if (!$rows) {
            return array();
        }
        $ids = array();
        foreach ($rows as $row) {
            if ((int) $row->ultima_id > 0) {
                $ids[] = (int) $row->ultima_id;
            }
        }
        $ultimas = array();
        if ($ids) {
            $msgs = $this->db->select('m.id, m.de_id, m.texto, m.created_at, m.sistema, ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', u.first_name, u.last_name', false)
                ->join('users u', 'u.id = m.de_id', 'left')
                ->where_in('m.id', $ids)
                ->get('mensagem m')
                ->result();
            foreach ($msgs as $m) {
                $ultimas[(int) $m->id] = $m;
            }
        }
        $configs = $this->configs_conversa($user_id, $empresa_id);
        $mencoes = $this->mencoes_nao_lidas_por_conversa($user_id, $empresa_id);
        $this->load->model('mensagens_recursos_model');
        $rascunhos = $this->mensagens_recursos_model->rascunhos_usuario($empresa_id, $user_id);
        $etiquetas = $this->mensagens_recursos_model->etiquetas_por_conversa($empresa_id, $user_id);
        $canais = array();
        foreach ($rows as $row) {
            $m = isset($ultimas[(int) $row->ultima_id]) ? $ultimas[(int) $row->ultima_id] : null;
            $chave = 'c' . (int) $row->id;
            $cfg = isset($configs[$chave]) ? $configs[$chave] : array('modo_notif' => 'todas', 'silenciada_ate' => null, 'silenciada' => false, 'marcada_nao_lida' => false);
            $canais[] = array(
                'chave' => $chave,
                'tipo' => 'canal',
                'id' => (int) $row->id,
                'nome' => $this->decodificar_emojis($row->nome),
                'descricao' => (string) $row->descricao,
                'foto' => null,
                'nao_lidas' => (int) $row->nao_lidas,
                'marcada_nao_lida' => !empty($cfg['marcada_nao_lida']),
                'mencoes' => isset($mencoes[$chave]) ? (int) $mencoes[$chave] : 0,
                'silenciada' => $cfg['silenciada'],
                'modo_notif' => $cfg['modo_notif'],
                'rascunho' => isset($rascunhos[$chave]) ? $rascunhos[$chave] : '',
                'etiquetas' => isset($etiquetas[$chave]) ? $etiquetas[$chave] : array(),
                'ultima' => $m ? $this->previa($m, 80) : '',
                'ultima_minha' => $m && (int) $m->de_id === $user_id && !$m->sistema && empty($m->excluida_em),
                'ultima_autor' => ($m && !$m->sistema && empty($m->excluida_em) && (int) $m->de_id !== $user_id) ? trim($m->first_name) : '',
                'ultima_id' => (int) $row->ultima_id,
                'ultima_em' => $m ? $m->created_at : null,
                'quando' => $m ? $this->quando($m->created_at) : '',
            );
        }
        return $canais;
    }

    /**
     * Dados de uma pessoa com quem o usuário pode conversar (null se ela não tiver acesso às mensagens).
     * Com $viewer_id, também confere se quem está vendo pode conversar com ela (regra dos clientes) e,
     * para o cliente, mostra a pessoa da equipe como "Suporte".
     */
    public function contato($user_id, $empresa_id, $viewer_id = 0)
    {
        $tem_bot_col = $this->db->field_exists('is_bot', 'users');
        $campo_bot = $tem_bot_col ? 'is_bot' : '0 AS is_bot';
        $u = $this->db->select('id, first_name, last_name, ' . $campo_bot . ', ' . $this->campo_foto(''), false)->where('id', (int) $user_id)->where('active', 1)->get('users')->row();
        if (!$u) {
            return null;
        }
        $eh_bot = !empty($u->is_bot);
        if (!$eh_bot && !$this->tem_acesso($user_id, $empresa_id)) {
            return null;
        }
        if (!$eh_bot && (int) $viewer_id > 0 && !$this->pode_conversar($viewer_id, $user_id, $empresa_id)) {
            return null;
        }
        $fotos = $this->fotos(array($u));
        $this->load->model('mensagens_recursos_model');
        $st = $eh_bot
            ? array('status' => 'online', 'rotulo' => 'Bot automático', 'texto' => 'Atualizações de chamados')
            : $this->mensagens_recursos_model->status_usuario($empresa_id, (int) $u->id);
        $contato = array(
            'chave' => 'u' . (int) $u->id,
            'tipo' => 'direta',
            'id' => (int) $u->id,
            'nome' => $this->nome($u),
            'foto' => $fotos[(int) $u->id],
            'is_bot' => $eh_bot,
            'is_suporte' => false,
            'somente_leitura' => $eh_bot,
            'status' => $st['status'],
            'status_rotulo' => $st['rotulo'],
            'status_texto' => $st['texto'],
            // Só a equipe sabe quem é cliente (botão "Abrir chamado" do atendimento).
            'cliente' => !$eh_bot && (int) $viewer_id > 0 && !$this->eh_cliente($viewer_id, $empresa_id) && $this->eh_cliente($u->id, $empresa_id),
        );
        if (!$eh_bot && (int) $viewer_id > 0 && $this->ver_como_suporte($viewer_id, $u->id, $empresa_id)) {
            $contato = $this->mascarar_suporte($contato);
        }
        return $contato;
    }

    /* ---------- Conversa direta ---------- */

    /**
     * Mensagens entre os dois usuários. Sem $depois_id: as $limite mais recentes; com ele, só as que vieram depois
     * (atualização da conversa aberta). Antigas primeiro.
     */
    public function conversa($user_id, $outro_id, $empresa_id, $depois_id = 0, $limite = 100, $antes_id = 0)
    {
        $user_id = (int) $user_id;
        $outro_id = (int) $outro_id;
        $this->db->select('m.id, m.de_id, m.para_id, m.texto, m.created_at, m.lida_em, ' . ($this->aceita_grupos() ? 'm.sistema' : '0 AS sistema') . ', ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m'), false)
            ->from('mensagem m')
            ->where('m.empresa_id', (int) $empresa_id)
            ->group_start()
                ->group_start()->where('m.de_id', $user_id)->where('m.para_id', $outro_id)->group_end()
                ->or_group_start()->where('m.de_id', $outro_id)->where('m.para_id', $user_id)->group_end()
            ->group_end();
        if ($this->aceita_grupos()) {
            $this->db->where('m.grupo_id IS NULL', null, false);
        }
        if ($this->aceita_canais()) {
            $this->db->where('m.canal_id IS NULL', null, false);
        }
        $this->aplicar_filtro_oculta('m', $user_id);
        return $this->listar_mensagens($user_id, $depois_id, $limite, 'm.', $empresa_id, null, $antes_id);
    }

    // Marca como lidas (e entregues) as mensagens diretas que o outro mandou para o usuário.
    public function marcar_lidas($user_id, $outro_id, $empresa_id)
    {
        $agora = date('Y-m-d H:i:s');
        $this->db->set('lida_em', $agora);
        if ($this->tem_coluna_msg('entregue_em')) {
            $this->db->set('entregue_em', 'COALESCE(entregue_em, ' . $this->db->escape($agora) . ')', false);
        }
        $this->db->where('empresa_id', (int) $empresa_id)
            ->where('para_id', (int) $user_id)
            ->where('de_id', (int) $outro_id)
            ->where('lida_em IS NULL', null, false);
        if ($this->aceita_grupos()) {
            $this->db->where('grupo_id IS NULL', null, false);
        }
        if ($this->aceita_canais()) {
            $this->db->where('canal_id IS NULL', null, false);
        }
        $this->db->update('mensagem');
        $afetadas = $this->db->affected_rows();
        $this->marcar_mencoes_lidas_direta($user_id, $outro_id, $empresa_id);
        return $afetadas;
    }

    // Maior id das mensagens do usuário que o outro já leu (para o "visto" na conversa direta).
    public function ultima_lida_pelo_outro($user_id, $outro_id, $empresa_id)
    {
        $row = $this->db->select_max('id')
            ->where('empresa_id', (int) $empresa_id)
            ->where('de_id', (int) $user_id)
            ->where('para_id', (int) $outro_id)
            ->where('lida_em IS NOT NULL', null, false)
            ->get('mensagem')
            ->row();
        return $row ? (int) $row->id : 0;
    }

    // Maior id entregue e maior id visualizada pelo outro usuário na conversa direta.
    public function status_direta($user_id, $outro_id, $empresa_id)
    {
        $lida_ate = $this->ultima_lida_pelo_outro($user_id, $outro_id, $empresa_id);
        $entregue_ate = $lida_ate;
        if ($this->tem_coluna_msg('entregue_em')) {
            $row = $this->db->select_max('id')
                ->where('empresa_id', (int) $empresa_id)
                ->where('de_id', (int) $user_id)
                ->where('para_id', (int) $outro_id)
                ->where('entregue_em IS NOT NULL', null, false)
                ->get('mensagem')
                ->row();
            $entregue_ate = max($lida_ate, $row ? (int) $row->id : 0);
        }
        return array('entregue_ate' => $entregue_ate, 'lida_ate' => $lida_ate);
    }

    // Marca mensagens pendentes do usuário como entregues (quando o cliente faz polling ou abre páginas).
    public function marcar_entregues($user_id, $empresa_id)
    {
        if (!$this->disponivel() || !$this->tem_coluna_msg('entregue_em')) {
            return;
        }
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        if ($user_id <= 0 || $empresa_id <= 0) {
            return;
        }
        $agora = date('Y-m-d H:i:s');

        $this->db->where('empresa_id', $empresa_id)
            ->where('para_id', $user_id)
            ->where('entregue_em IS NULL', null, false);
        if ($this->aceita_grupos()) {
            $this->db->where('grupo_id IS NULL', null, false);
        }
        if ($this->aceita_canais()) {
            $this->db->where('canal_id IS NULL', null, false);
        }
        $this->db->update('mensagem', array('entregue_em' => $agora));

        if ($this->aceita_grupos() && $this->tem_tabela('mensagem_leitura') && $this->tem_coluna('mensagem_grupo_membro', 'ultima_entregue_id')) {
            $sql = 'SELECT gm.grupo_id, gm.ultima_entregue_id, (SELECT MAX(m.id) FROM mensagem m WHERE m.grupo_id = gm.grupo_id) AS max_id'
                . ' FROM mensagem_grupo_membro gm'
                . ' JOIN mensagem_grupo g ON g.id = gm.grupo_id AND g.empresa_id = ? AND g.excluido_em IS NULL'
                . ' WHERE gm.user_id = ? AND gm.saiu_em IS NULL';
            $grupos = $this->db->query($sql, array($empresa_id, $user_id))->result();
            foreach ($grupos as $g) {
                $max_id = (int) $g->max_id;
                $ult_ent = (int) $g->ultima_entregue_id;
                if ($max_id > $ult_ent) {
                    $this->db->query(
                        'INSERT INTO mensagem_leitura (mensagem_id, user_id, entregue_em) '
                        . 'SELECT m.id, ?, ? FROM mensagem m '
                        . 'WHERE m.grupo_id = ? AND m.id > ? AND m.id <= ? AND m.de_id <> ? AND m.sistema = 0 '
                        . 'ON DUPLICATE KEY UPDATE mensagem_leitura.entregue_em = COALESCE(mensagem_leitura.entregue_em, VALUES(entregue_em))',
                        array($user_id, $agora, (int) $g->grupo_id, $ult_ent, $max_id, $user_id)
                    );
                    $this->db->where('grupo_id', (int) $g->grupo_id)
                        ->where('user_id', $user_id)
                        ->where('ultima_entregue_id <', $max_id)
                        ->update('mensagem_grupo_membro', array('ultima_entregue_id' => $max_id));
                }
            }
        }
    }

    /* ---------- Grupos ---------- */

    // Grupo ativo do qual o usuário participa (null se não participa, foi removido ou o grupo foi excluído).
    public function grupo_do_membro($grupo_id, $user_id, $empresa_id)
    {
        if (!$this->aceita_grupos()) {
            return null;
        }
        return $this->db->select('g.id, g.nome, g.criador_id, g.created_at')
            ->join('mensagem_grupo_membro gm', 'gm.grupo_id = g.id')
            ->where('g.id', (int) $grupo_id)
            ->where('g.empresa_id', (int) $empresa_id)
            ->where('g.excluido_em IS NULL', null, false)
            ->where('gm.user_id', (int) $user_id)
            ->where('gm.saiu_em IS NULL', null, false)
            ->get('mensagem_grupo g')
            ->row();
    }

    // Cabeçalho da conversa de grupo.
    public function grupo_para_conversa($grupo, $user_id)
    {
        return array(
            'chave' => 'g' . (int) $grupo->id,
            'tipo' => 'grupo',
            'id' => (int) $grupo->id,
            'nome' => $this->decodificar_emojis($grupo->nome),
            'foto' => null,
            'membros' => count($this->membros($grupo->id)),
            'sou_criador' => (int) $grupo->criador_id === (int) $user_id,
        );
    }

    // Participantes que não saíram (e, por padrão, só usuários ativos), o criador primeiro e depois por ordem de entrada.
    public function membros($grupo_id, $somente_ativos = true)
    {
        $grupo_id = (int) $grupo_id;
        $grupo = $this->db->select('criador_id')->where('id', $grupo_id)->get('mensagem_grupo')->row();
        $criador_id = $grupo ? (int) $grupo->criador_id : 0;
        // Ordenação no SQL (o usort do PHP 5.6 não é estável): criador, depois quem entrou primeiro.
        $this->db->select('u.id, u.first_name, u.last_name, gm.entrou_em, ' . $this->campo_foto('u'), false)
            ->join('users u', 'u.id = gm.user_id')
            ->where('gm.grupo_id', $grupo_id)
            ->where('gm.saiu_em IS NULL', null, false);
        if ($somente_ativos) {
            $this->db->where('u.active', 1);
        }
        $rows = $this->db->order_by('(gm.user_id = ' . $criador_id . ')', 'DESC', false)
            ->order_by('gm.entrou_em', 'ASC')
            ->order_by('gm.user_id', 'ASC')
            ->get('mensagem_grupo_membro gm')
            ->result();
        $fotos = $this->fotos($rows);
        $membros = array();
        foreach ($rows as $u) {
            $membros[] = array('id' => (int) $u->id, 'nome' => $this->nome($u), 'foto' => $fotos[(int) $u->id], 'criador' => (int) $u->id === $criador_id);
        }
        return $membros;
    }

    public function conversa_grupo($user_id, $grupo_id, $depois_id = 0, $limite = 100, $empresa_id = 0, $antes_id = 0)
    {
        $this->db->select('m.id, m.de_id, m.para_id, m.texto, m.created_at, m.lida_em, m.sistema, ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', u.first_name, u.last_name', false)
            ->from('mensagem m')
            ->join('users u', 'u.id = m.de_id', 'left')
            ->where('m.grupo_id', (int) $grupo_id);
        $this->aplicar_filtro_oculta('m', (int) $user_id);
        return $this->listar_mensagens((int) $user_id, $depois_id, $limite, 'm.', (int) $empresa_id, (int) $grupo_id, $antes_id);
    }

    // Carrega mensagens anteriores a $antes_id (ou a partir de $ate_id até $antes_id para saltar até uma citação/busca).
    public function conversa_anteriores($alvo, $user_id, $empresa_id, $antes_id, $ate_id = 0)
    {
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        $antes_id = (int) $antes_id;
        $ate_id = (int) $ate_id;
        $grupo_id = null;

        $this->db->select('m.id, m.de_id, m.para_id, m.texto, m.created_at, m.lida_em, ' . ($this->aceita_grupos() ? 'm.sistema' : '0 AS sistema') . ', ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', u.first_name, u.last_name', false)
            ->from('mensagem m')
            ->join('users u', 'u.id = m.de_id', 'left')
            ->where('m.empresa_id', $empresa_id);
        if ($alvo['tipo'] === 'grupo') {
            $grupo_id = (int) $alvo['grupo']->id;
            $this->db->where('m.grupo_id', $grupo_id);
        } elseif ($alvo['tipo'] === 'canal') {
            $this->db->where('m.canal_id', (int) $alvo['canal']->id);
        } else {
            $outro = (int) $alvo['contato']['id'];
            $this->db->group_start()
                ->group_start()->where('m.de_id', $user_id)->where('m.para_id', $outro)->group_end()
                ->or_group_start()->where('m.de_id', $outro)->where('m.para_id', $user_id)->group_end()
            ->group_end();
            if ($this->aceita_grupos()) {
                $this->db->where('m.grupo_id IS NULL', null, false);
            }
            if ($this->aceita_canais()) {
                $this->db->where('m.canal_id IS NULL', null, false);
            }
        }
        $this->aplicar_filtro_oculta('m', $user_id);

        if ($antes_id > 0) {
            $this->db->where('m.id <', $antes_id);
        }
        if ($ate_id > 0) {
            $this->db->where('m.id >=', $ate_id)->order_by('m.id', 'ASC')->limit(300);
            $rows = $this->db->get()->result();
        } else {
            $this->db->order_by('m.id', 'DESC')->limit(50);
            $rows = array_reverse($this->db->get()->result());
        }
        return $this->enriquecer_mensagens($rows, $user_id, $empresa_id, $grupo_id);
    }

    // Status atualizado das minhas últimas mensagens no grupo (para ticks em tempo real e trava de edição).
    public function status_recentes_grupo($grupo_id, $user_id, $ate_id)
    {
        $ate_id = (int) $ate_id;
        if ($ate_id <= 0) {
            return array();
        }
        $rows = $this->db->select('id')
            ->where('grupo_id', (int) $grupo_id)
            ->where('de_id', (int) $user_id)
            ->where('sistema', 0)
            ->where('id <=', $ate_id)
            ->order_by('id', 'DESC')
            ->limit(60)
            ->get('mensagem')
            ->result();
        if (!$rows) {
            return array();
        }
        $ids = array();
        foreach ($rows as $r) {
            $ids[] = (int) $r->id;
        }
        $calc = $this->calcular_status_grupo((int) $grupo_id, $ids, (int) $user_id);
        $saida = array();
        foreach ($calc as $mid => $st) {
            $saida[(string) $mid] = array(
                'status' => $st['status'],
                'lidas_grupo' => $st['lida_cnt'],
                'total_grupo' => $st['total_outros'],
                'pode_editar' => !$st['ja_lida'] && $this->tem_coluna_msg('editada_em'),
            );
        }
        return $saida;
    }

    // Tudo o que já chegou no grupo conta como lido e entregue pelo usuário.
    public function marcar_lido_grupo($user_id, $grupo_id)
    {
        $user_id = (int) $user_id;
        $grupo_id = (int) $grupo_id;
        $ultima = $this->db->select_max('id')->where('grupo_id', $grupo_id)->get('mensagem')->row();
        $max_id = $ultima ? (int) $ultima->id : 0;

        $membro = $this->db->select('ultima_lida_id')
            ->where('grupo_id', $grupo_id)->where('user_id', $user_id)->where('saiu_em IS NULL', null, false)
            ->get('mensagem_grupo_membro')->row();
        $ult_lida = $membro ? (int) $membro->ultima_lida_id : 0;

        if ($max_id > $ult_lida && $this->tem_tabela('mensagem_leitura')) {
            $agora = date('Y-m-d H:i:s');
            $this->db->query(
                'INSERT INTO mensagem_leitura (mensagem_id, user_id, entregue_em, lida_em) '
                . 'SELECT m.id, ?, ?, ? FROM mensagem m '
                . 'WHERE m.grupo_id = ? AND m.id > ? AND m.id <= ? AND m.de_id <> ? AND m.sistema = 0 '
                . 'ON DUPLICATE KEY UPDATE mensagem_leitura.entregue_em = COALESCE(mensagem_leitura.entregue_em, VALUES(entregue_em)), mensagem_leitura.lida_em = COALESCE(mensagem_leitura.lida_em, VALUES(lida_em))',
                array($user_id, $agora, $agora, $grupo_id, $ult_lida, $max_id, $user_id)
            );
        }

        $this->db->where('grupo_id', $grupo_id)
            ->where('user_id', $user_id)
            ->where('saiu_em IS NULL', null, false);
        if ($this->tem_coluna('mensagem_grupo_membro', 'ultima_entregue_id')) {
            $this->db->set('ultima_entregue_id', 'GREATEST(ultima_entregue_id, ' . $max_id . ')', false);
        }
        $this->db->set('ultima_lida_id', 'GREATEST(ultima_lida_id, ' . $max_id . ')', false);
        $this->db->update('mensagem_grupo_membro');

        $this->marcar_mencoes_lidas_grupo($user_id, $grupo_id);
    }

    /**
     * Cria o grupo com o criador e os participantes escolhidos (precisa de MINIMO_GRUPO no total).
     * Devolve o id do grupo ou uma mensagem de erro (string).
     */
    public function criar_grupo($empresa_id, $criador_id, $nome, $membros)
    {
        if (!$this->aceita_grupos()) {
            return 'Os grupos ainda não estão disponíveis: aplique o script sql/2026-11-16_mensagens_grupos.sql no banco.';
        }
        $nome = trim(preg_replace('/\s+/u', ' ', (string) $nome));
        if ($nome === '') {
            return 'Dê um nome para o grupo.';
        }
        if (mb_strlen($nome, 'UTF-8') > self::TAMANHO_NOME_GRUPO) {
            return 'O nome do grupo pode ter até ' . self::TAMANHO_NOME_GRUPO . ' caracteres.';
        }
        $membros = $this->membros_validos($membros, $criador_id, $empresa_id);
        if (count($membros) + 1 < self::MINIMO_GRUPO) {
            return 'Escolha pelo menos ' . (self::MINIMO_GRUPO - 1) . ' pessoas: o grupo precisa ter ' . self::MINIMO_GRUPO . ' participantes ou mais, contando você.';
        }
        $agora = date('Y-m-d H:i:s');
        $this->db->trans_start();
        $this->db->insert('mensagem_grupo', array(
            'empresa_id' => (int) $empresa_id,
            'nome' => $this->codificar_emojis($nome),
            'criador_id' => (int) $criador_id,
            'created_at' => $agora,
        ));
        $grupo_id = (int) $this->db->insert_id();
        foreach (array_merge(array((int) $criador_id), $membros) as $user_id) {
            $this->db->insert('mensagem_grupo_membro', array('grupo_id' => $grupo_id, 'user_id' => $user_id, 'entrou_em' => $agora));
        }
        $this->aviso_grupo($grupo_id, $empresa_id, $criador_id, $this->nome_usuario($criador_id) . ' criou o grupo "' . $nome . '".');
        $this->marcar_lido_grupo($criador_id, $grupo_id);
        $this->db->trans_complete();
        return $this->db->trans_status() ? $grupo_id : 'Não foi possível criar o grupo. Tente de novo.';
    }

    // Adiciona participantes (só o criador). Quem entra não recebe as mensagens antigas como não lidas.
    public function adicionar_membros($grupo, $empresa_id, $autor_id, $membros)
    {
        $ativos = array();
        foreach ($this->membros($grupo->id, false) as $m) {
            $ativos[$m['id']] = true;
        }
        $novos = array();
        foreach ($this->membros_validos($membros, $autor_id, $empresa_id) as $user_id) {
            if (!isset($ativos[$user_id])) {
                $novos[] = $user_id;
            }
        }
        if (!$novos) {
            return 'Escolha pelo menos uma pessoa que ainda não está no grupo.';
        }
        $agora = date('Y-m-d H:i:s');
        $ultima = $this->db->select_max('id')->where('grupo_id', (int) $grupo->id)->get('mensagem')->row();
        $max_id = $ultima ? (int) $ultima->id : 0;
        $nomes = array();
        foreach ($novos as $user_id) {
            $existe = $this->db->where('grupo_id', (int) $grupo->id)->where('user_id', $user_id)->count_all_results('mensagem_grupo_membro') > 0;
            $dados = array('entrou_em' => $agora, 'saiu_em' => null, 'ultima_lida_id' => $max_id);
            if ($this->tem_coluna('mensagem_grupo_membro', 'ultima_entregue_id')) {
                $dados['ultima_entregue_id'] = $max_id;
            }
            if ($existe) {
                $this->db->where('grupo_id', (int) $grupo->id)->where('user_id', $user_id)->update('mensagem_grupo_membro', $dados);
            } else {
                $this->db->insert('mensagem_grupo_membro', array_merge($dados, array('grupo_id' => (int) $grupo->id, 'user_id' => $user_id)));
            }
            $nomes[] = $this->nome_usuario($user_id);
        }
        $this->aviso_grupo($grupo->id, $empresa_id, $autor_id, $this->nome_usuario($autor_id) . ' adicionou ' . $this->juntar_nomes($nomes) . '.');
        return true;
    }

    public function remover_membro($grupo, $empresa_id, $autor_id, $user_id)
    {
        $this->db->where('grupo_id', (int) $grupo->id)->where('user_id', (int) $user_id)->where('saiu_em IS NULL', null, false)
            ->update('mensagem_grupo_membro', array('saiu_em' => date('Y-m-d H:i:s')));
        if ($this->db->affected_rows() < 1) {
            return 'Essa pessoa não está mais no grupo.';
        }
        $this->aviso_grupo($grupo->id, $empresa_id, $autor_id, $this->nome_usuario($autor_id) . ' removeu ' . $this->nome_usuario($user_id) . ' do grupo.');
        return true;
    }

    // Sai do grupo. Se quem sai é o criador, o participante mais antigo assume; sem ninguém, o grupo é excluído.
    public function sair_do_grupo($grupo, $empresa_id, $user_id)
    {
        $this->db->where('grupo_id', (int) $grupo->id)->where('user_id', (int) $user_id)->where('saiu_em IS NULL', null, false)
            ->update('mensagem_grupo_membro', array('saiu_em' => date('Y-m-d H:i:s')));
        $restantes = $this->membros($grupo->id, false);
        if (!$restantes) {
            $this->db->where('id', (int) $grupo->id)->update('mensagem_grupo', array('excluido_em' => date('Y-m-d H:i:s')));
            return true;
        }
        $this->aviso_grupo($grupo->id, $empresa_id, $user_id, $this->nome_usuario($user_id) . ' saiu do grupo.');
        if ((int) $grupo->criador_id === (int) $user_id) {
            $restantes_ativos = $this->membros($grupo->id);
            $novo = $restantes_ativos ? $restantes_ativos[0]['id'] : $restantes[0]['id'];
            $this->db->where('id', (int) $grupo->id)->update('mensagem_grupo', array('criador_id' => $novo));
            $this->aviso_grupo($grupo->id, $empresa_id, $novo, $this->nome_usuario($novo) . ' agora administra o grupo.');
        }
        return true;
    }

    // Exclui o grupo para todos (só o criador). As mensagens ficam no banco, mas ninguém acessa mais.
    public function excluir_grupo($grupo)
    {
        $this->db->where('id', (int) $grupo->id)->update('mensagem_grupo', array('excluido_em' => date('Y-m-d H:i:s')));
        return true;
    }

    private function aviso_grupo($grupo_id, $empresa_id, $autor_id, $texto)
    {
        $this->db->insert('mensagem', array(
            'empresa_id' => (int) $empresa_id,
            'de_id' => (int) $autor_id,
            'para_id' => 0,
            'grupo_id' => (int) $grupo_id,
            'sistema' => 1,
            'texto' => $this->codificar_emojis($texto),
            'created_at' => date('Y-m-d H:i:s'),
        ));
    }

    // Ids únicos, sem o próprio usuário e só de quem tem acesso às mensagens na empresa.
    // Cliente só monta grupo com clientes de uma mesma unidade (o atendente do "Suporte" não entra).
    private function membros_validos($membros, $proprio_id, $empresa_id)
    {
        $validos = array();
        $sou_cliente = $this->eh_cliente($proprio_id, $empresa_id);
        foreach ((array) $membros as $user_id) {
            $user_id = (int) $user_id;
            if ($user_id <= 0 || $user_id === (int) $proprio_id || in_array($user_id, $validos, true) || !$this->pode_conversar($proprio_id, $user_id, $empresa_id)) {
                continue;
            }
            if ($sou_cliente && !$this->eh_cliente($user_id, $empresa_id)) {
                continue;
            }
            $validos[] = $user_id;
        }
        return $validos;
    }

    private function juntar_nomes($nomes)
    {
        if (count($nomes) <= 1) {
            return implode('', $nomes);
        }
        $ultimo = array_pop($nomes);
        return implode(', ', $nomes) . ' e ' . $ultimo;
    }

    private function nome_usuario($user_id)
    {
        $u = $this->db->select('id, first_name, last_name')->where('id', (int) $user_id)->get('users')->row();
        return $u ? $this->nome($u) : 'Alguém';
    }

    /* ---------- Canais de mensagens ---------- */

    public function grupos_empresa($empresa_id)
    {
        return $this->db->select('id, name, description')
            ->where('empresa_id', (int) $empresa_id)
            ->order_by('name', 'ASC')
            ->get('groups')
            ->result();
    }

    public function canal_do_usuario($canal_id, $user_id, $empresa_id)
    {
        if (!$this->aceita_canais()) {
            return null;
        }
        $user_id = (int) $user_id;
        $canal = $this->db->where('id', (int) $canal_id)
            ->where('empresa_id', (int) $empresa_id)
            ->where('excluido_em IS NULL', null, false)
            ->get('mensagem_canal')
            ->row();
        if (!$canal) {
            return null;
        }
        if ((int) $canal->criador_id === $user_id) {
            return $canal;
        }
        $tem_grupo = $this->db->join('users_groups ug', 'ug.group_id = cg.group_id')
            ->where('cg.canal_id', (int) $canal->id)
            ->where('ug.user_id', $user_id)
            ->count_all_results('mensagem_canal_grupo cg') > 0;
        return $tem_grupo ? $canal : null;
    }

    public function pode_enviar_canal($canal_id, $user_id, $empresa_id)
    {
        $canal = $this->canal_do_usuario($canal_id, $user_id, $empresa_id);
        if (!$canal) {
            return false;
        }
        $total_escritores = $this->db->where('canal_id', (int) $canal->id)->count_all_results('mensagem_canal_escritor');
        if ($total_escritores < 1) {
            return true;
        }
        return $this->db->where('canal_id', (int) $canal->id)
            ->where('user_id', (int) $user_id)
            ->count_all_results('mensagem_canal_escritor') > 0;
    }

    public function canal_para_conversa($canal, $user_id, $empresa_id)
    {
        $grupos = $this->db->select('g.id, g.name')
            ->join('groups g', 'g.id = cg.group_id')
            ->where('cg.canal_id', (int) $canal->id)
            ->get('mensagem_canal_grupo cg')->result();
        $escritores = $this->db->select('user_id')->where('canal_id', (int) $canal->id)->get('mensagem_canal_escritor')->result();
        $escritores_ids = array();
        foreach ($escritores as $e) {
            $escritores_ids[] = (int) $e->user_id;
        }
        $grupos_ids = array();
        $grupos_nomes = array();
        foreach ($grupos as $g) {
            $grupos_ids[] = (int) $g->id;
            $grupos_nomes[] = $g->name;
        }
        return array(
            'chave' => 'c' . (int) $canal->id,
            'tipo' => 'canal',
            'id' => (int) $canal->id,
            'nome' => $this->decodificar_emojis($canal->nome),
            'descricao' => (string) $canal->descricao,
            'foto' => null,
            'sou_criador' => (int) $canal->criador_id === (int) $user_id,
            'pode_enviar' => $this->pode_enviar_canal($canal->id, $user_id, $empresa_id),
            'grupos_ids' => $grupos_ids,
            'grupos_nomes' => $grupos_nomes,
            'escritores_ids' => $escritores_ids,
        );
    }

    // Usuários ativos que veem o canal. Com $viewer_id de um cliente, só aparecem quem ele pode ver (pode_conversar).
    public function membros_canal($canal_id, $empresa_id, $viewer_id = 0)
    {
        $canal = $this->db->where('id', (int) $canal_id)->where('empresa_id', (int) $empresa_id)->get('mensagem_canal')->row();
        if (!$canal) {
            return array();
        }
        $gids = array();
        foreach ($this->db->select('group_id')->where('canal_id', (int) $canal->id)->get('mensagem_canal_grupo')->result() as $r) {
            $gids[] = (int) $r->group_id;
        }
        // Um grupo de outra empresa gravado no canal (dado antigo ou formulário manipulado) não traz usuários de fora.
        $gids = $this->grupos_da_empresa($gids, $empresa_id);
        $this->db->select('u.id, u.first_name, u.last_name, ' . $this->campo_foto('u'), false)
            ->distinct()
            ->from('users u')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('u.active', 1)
            ->group_start()
                ->where('u.id', (int) $canal->criador_id);
        if ($gids) {
            $this->db->or_where_in('ug.group_id', $gids);
        }
        $rows = $this->db->group_end()->order_by('u.first_name', 'ASC')->get()->result();
        $viewer_id = (int) $viewer_id;
        if ($viewer_id > 0 && $this->eh_cliente($viewer_id, $empresa_id)) {
            $ids = array();
            foreach ($rows as $u) {
                $ids[] = (int) $u->id;
            }
            $perfis = $this->perfis($ids, $empresa_id);
            $clientes_ids = array();
            foreach ($ids as $id) {
                if (isset($perfis[$id]) && $perfis[$id] === 'cliente') {
                    $clientes_ids[] = $id;
                }
            }
            $vizinhos = array_flip($this->clientes_da_mesma_unidade($viewer_id, $clientes_ids, $empresa_id));
            $visiveis = array();
            foreach ($rows as $u) {
                if ((int) $u->id === $viewer_id || isset($vizinhos[(int) $u->id])) {
                    $visiveis[] = $u;
                }
            }
            $rows = $visiveis;
        }
        $fotos = $this->fotos($rows);
        $membros = array();
        foreach ($rows as $u) {
            $membros[] = array('id' => (int) $u->id, 'nome' => $this->nome($u), 'foto' => $fotos[(int) $u->id]);
        }
        return $membros;
    }

    public function conversa_canal($user_id, $canal_id, $depois_id = 0, $limite = 100, $empresa_id = 0, $antes_id = 0)
    {
        $this->db->select('m.id, m.de_id, m.para_id, m.texto, m.created_at, m.lida_em, m.sistema, ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', u.first_name, u.last_name', false)
            ->from('mensagem m')
            ->join('users u', 'u.id = m.de_id', 'left')
            ->where('m.canal_id', (int) $canal_id);
        $this->aplicar_filtro_oculta('m', (int) $user_id);
        return $this->listar_mensagens((int) $user_id, $depois_id, $limite, 'm.', (int) $empresa_id, null, $antes_id);
    }

    public function marcar_lido_canal($user_id, $canal_id)
    {
        if (!$this->aceita_canais()) {
            return;
        }
        $ultima = $this->db->select_max('id')->where('canal_id', (int) $canal_id)->get('mensagem')->row();
        $max_id = $ultima ? (int) $ultima->id : 0;
        $this->db->query(
            'INSERT INTO mensagem_canal_leitura (canal_id, user_id, ultima_lida_id, ultima_entregue_id) VALUES (?, ?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE ultima_lida_id = GREATEST(ultima_lida_id, VALUES(ultima_lida_id)), ultima_entregue_id = GREATEST(ultima_entregue_id, VALUES(ultima_entregue_id))',
            array((int) $canal_id, (int) $user_id, $max_id, $max_id)
        );
        $this->marcar_mencoes_lidas_canal((int) $user_id, (int) $canal_id);
    }

    public function criar_canal($empresa_id, $criador_id, $nome, $descricao, $grupos_ids, $escritores_ids)
    {
        if (!$this->aceita_canais()) {
            return 'Os canais ainda não estão disponíveis: aplique o script sql/2026-11-16_mensagens_recursos_v2.sql.';
        }
        $nome = trim(preg_replace('/\s+/u', ' ', (string) $nome));
        if ($nome === '') {
            return 'Dê um nome para o canal.';
        }
        if (mb_strlen($nome, 'UTF-8') > self::TAMANHO_NOME_GRUPO) {
            return 'O nome do canal pode ter até ' . self::TAMANHO_NOME_GRUPO . ' caracteres.';
        }
        $grupos_ids = $this->grupos_da_empresa($grupos_ids, $empresa_id);
        if (!$grupos_ids) {
            return 'Selecione pelo menos um grupo de usuários que pode visualizar este canal.';
        }
        $escritores_ids = $this->escritores_validos($escritores_ids, $criador_id, $empresa_id);

        $agora = date('Y-m-d H:i:s');
        $this->db->trans_start();
        $this->db->insert('mensagem_canal', array(
            'empresa_id' => (int) $empresa_id,
            'nome' => $this->codificar_emojis($nome),
            'descricao' => mb_substr(trim((string) $descricao), 0, 255, 'UTF-8'),
            'criador_id' => (int) $criador_id,
            'created_at' => $agora,
        ));
        $canal_id = (int) $this->db->insert_id();
        foreach ($grupos_ids as $gid) {
            $this->db->insert('mensagem_canal_grupo', array('canal_id' => $canal_id, 'group_id' => $gid));
        }
        foreach ($escritores_ids as $uid) {
            $this->db->insert('mensagem_canal_escritor', array('canal_id' => $canal_id, 'user_id' => $uid));
        }
        $this->db->insert('mensagem', array(
            'empresa_id' => (int) $empresa_id,
            'de_id' => (int) $criador_id,
            'para_id' => 0,
            'canal_id' => $canal_id,
            'sistema' => 1,
            'texto' => $this->codificar_emojis($this->nome_usuario($criador_id) . ' criou o canal "' . $nome . '".'),
            'created_at' => $agora,
        ));
        $this->marcar_lido_canal($criador_id, $canal_id);
        $this->db->trans_complete();
        return $this->db->trans_status() ? $canal_id : 'Não foi possível criar o canal.';
    }

    public function atualizar_canal($canal, $nome, $descricao, $grupos_ids, $escritores_ids)
    {
        $nome = trim(preg_replace('/\s+/u', ' ', (string) $nome));
        if ($nome === '') {
            return 'Dê um nome para o canal.';
        }
        $grupos_ids = $this->grupos_da_empresa($grupos_ids, $canal->empresa_id);
        if (!$grupos_ids) {
            return 'Selecione pelo menos um grupo de usuários que pode visualizar este canal.';
        }
        $escritores_ids = $this->escritores_validos($escritores_ids, $canal->criador_id, $canal->empresa_id);

        $this->db->trans_start();
        $this->db->where('id', (int) $canal->id)->update('mensagem_canal', array(
            'nome' => $this->codificar_emojis($nome),
            'descricao' => mb_substr(trim((string) $descricao), 0, 255, 'UTF-8'),
        ));
        $this->db->where('canal_id', (int) $canal->id)->delete('mensagem_canal_grupo');
        foreach ($grupos_ids as $gid) {
            $this->db->insert('mensagem_canal_grupo', array('canal_id' => (int) $canal->id, 'group_id' => $gid));
        }
        $this->db->where('canal_id', (int) $canal->id)->delete('mensagem_canal_escritor');
        foreach ($escritores_ids as $uid) {
            $this->db->insert('mensagem_canal_escritor', array('canal_id' => (int) $canal->id, 'user_id' => $uid));
        }
        $this->db->trans_complete();
        return $this->db->trans_status() ? true : 'Não foi possível atualizar o canal.';
    }

    // Quem pode escrever no canal: só usuários com acesso às mensagens nesta EMPRESA. Sem ninguém válido, fica o criador.
    private function escritores_validos($escritores_ids, $criador_id, $empresa_id)
    {
        $validos = array();
        foreach (array_unique(array_filter(array_map('intval', (array) $escritores_ids))) as $uid) {
            if ($uid === (int) $criador_id || $this->tem_acesso($uid, $empresa_id)) {
                $validos[] = $uid;
            }
        }
        return $validos ? $validos : array((int) $criador_id);
    }

    public function excluir_canal($canal)
    {
        $this->db->where('id', (int) $canal->id)->update('mensagem_canal', array('excluido_em' => date('Y-m-d H:i:s')));
        return true;
    }

    /* ---------- Envio, resposta, edição, exclusão e encaminhamento ---------- */

    /**
     * Grava a mensagem direta ($para_id) ou de grupo ($grupo_id, com $para_id = 0).
     * $anexo (opcional): array('arquivo', 'nome', 'tipo', 'tamanho') de um arquivo já salvo em PASTA_ANEXOS.
     */
    public function enviar($de_id, $para_id, $empresa_id, $texto, $anexo = null, $grupo_id = null, $resposta_id = null, $mencoes = array(), $encaminhada = false, $canal_id = null, $transcricao = null, $chamado_id = null, $tipo_conteudo = 'normal')
    {
        $texto = trim(str_replace("\r\n", "\n", (string) $texto));
        if (($texto === '' && !$anexo) || mb_strlen($texto, 'UTF-8') > self::TAMANHO_MAXIMO) {
            return null;
        }
        if ((int) $para_id > 0 && !$grupo_id && !$canal_id) {
            $this->load->model('mensagens_recursos_model');
            if ($this->mensagens_recursos_model->eh_bot($para_id)) {
                return null;
            }
        }
        $dados = array(
            'empresa_id' => (int) $empresa_id,
            'de_id' => (int) $de_id,
            'para_id' => (int) $para_id,
            'texto' => $this->codificar_emojis($texto),
            'created_at' => date('Y-m-d H:i:s'),
        );
        if ($chamado_id && $this->tem_coluna_msg('chamado_id')) {
            $dados['chamado_id'] = (int) $chamado_id;
        }
        if ($tipo_conteudo !== 'normal' && $this->tem_coluna_msg('tipo_conteudo')) {
            $dados['tipo_conteudo'] = $tipo_conteudo;
        }
        if ($grupo_id && $this->aceita_grupos()) {
            $dados['grupo_id'] = (int) $grupo_id;
            $dados['para_id'] = 0;
        }
        if ($canal_id && $this->aceita_canais()) {
            $dados['canal_id'] = (int) $canal_id;
            $dados['para_id'] = 0;
        }
        if ($anexo && $this->aceita_anexos()) {
            $dados['anexo'] = $anexo['arquivo'];
            $dados['anexo_nome'] = mb_substr($anexo['nome'], 0, 255, 'UTF-8');
            $dados['anexo_tipo'] = $anexo['tipo'];
            $dados['anexo_tamanho'] = (int) $anexo['tamanho'];
        }
        if ($transcricao !== null && trim((string) $transcricao) !== '' && $this->tem_coluna_msg('transcricao')) {
            $dados['transcricao'] = $this->codificar_emojis(trim((string) $transcricao));
        }
        if ($resposta_id && $this->tem_coluna_msg('resposta_id')) {
            $ref = $this->mensagem_na_conversa((int) $resposta_id, (int) $de_id, (int) $para_id, (int) $empresa_id, $grupo_id ? (int) $grupo_id : null, $canal_id ? (int) $canal_id : null);
            if ($ref) {
                $dados['resposta_id'] = (int) $ref->id;
            }
        }
        if ($encaminhada && $this->tem_coluna_msg('encaminhada')) {
            $dados['encaminhada'] = 1;
        }

        $this->db->insert('mensagem', $dados);
        $id = (int) $this->db->insert_id();
        if (!$id) {
            return null;
        }
        if (!empty($dados['grupo_id'])) {
            $this->marcar_lido_grupo($de_id, $dados['grupo_id']);
        }
        if (!empty($dados['canal_id'])) {
            $this->marcar_lido_canal($de_id, $dados['canal_id']);
        }
        if ($this->tem_tabela('mensagem_mencao')) {
            $this->registrar_mencoes($id, (int) $de_id, (int) $para_id, (int) $empresa_id, $grupo_id ? (int) $grupo_id : null, $texto, $mencoes, $canal_id ? (int) $canal_id : null);
        }
        $chave_dig = $canal_id ? ('c' . (int) $canal_id) : ($grupo_id ? ('g' . (int) $grupo_id) : ('u' . (int) $para_id));
        $this->definir_digitando((int) $de_id, (int) $empresa_id, $chave_dig, '');

        $row = $this->buscar_registro_exibicao($id);
        $lista = $this->enriquecer_mensagens(array($row), (int) $de_id, (int) $empresa_id, $grupo_id ? (int) $grupo_id : null);
        return $lista ? $lista[0] : null;
    }

    public function buscar_mensagem_exibicao($mensagem_id, $user_id, $empresa_id = 0)
    {
        $row = $this->buscar_registro_exibicao((int) $mensagem_id);
        if (!$row) {
            return null;
        }
        $grupo_id = !empty($row->grupo_id) ? (int) $row->grupo_id : null;
        $lista = $this->enriquecer_mensagens(array($row), (int) $user_id, (int) $empresa_id, $grupo_id);
        return $lista ? $lista[0] : null;
    }

    // Edita uma mensagem de texto/legenda. Bloqueia se já foi confirmada a leitura pelo destinatário/grupo.
    public function editar_mensagem($mensagem_id, $user_id, $empresa_id, $novo_texto, $mencoes = array())
    {
        if (!$this->tem_coluna_msg('editada_em')) {
            return 'Edição indisponível: aplique o script sql/2026-11-16_mensagens_recursos.sql no banco.';
        }
        $m = $this->mensagem_do_usuario((int) $mensagem_id, (int) $user_id, (int) $empresa_id);
        if (!$m) {
            return 'Mensagem não encontrada.';
        }
        if ((int) $m->de_id !== (int) $user_id) {
            return 'Você só pode editar suas próprias mensagens.';
        }
        if (!empty($m->sistema) || !empty($m->excluida_em)) {
            return 'Esta mensagem não pode ser editada.';
        }
        if ($this->mensagem_ja_foi_lida($m, (int) $user_id)) {
            return 'Esta mensagem já foi visualizada e não pode mais ser editada.';
        }
        $novo_texto = trim(str_replace("\r\n", "\n", (string) $novo_texto));
        if ($novo_texto === '' && empty($m->anexo)) {
            return 'A mensagem não pode ficar vazia.';
        }
        if (mb_strlen($novo_texto, 'UTF-8') > self::TAMANHO_MAXIMO) {
            return 'A mensagem passa de ' . self::TAMANHO_MAXIMO . ' caracteres.';
        }

        $agora = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $m->id)->update('mensagem', array(
            'texto' => $this->codificar_emojis($novo_texto),
            'editada_em' => $agora,
        ));
        if ($this->tem_tabela('mensagem_mencao')) {
            $this->db->where('mensagem_id', (int) $m->id)->delete('mensagem_mencao');
            $this->registrar_mencoes((int) $m->id, (int) $user_id, (int) $m->para_id, (int) $empresa_id, $m->grupo_id ? (int) $m->grupo_id : null, $novo_texto, $mencoes, !empty($m->canal_id) ? (int) $m->canal_id : null);
        }
        $row = $this->buscar_registro_exibicao((int) $m->id);
        $lista = $this->enriquecer_mensagens(array($row), (int) $user_id, (int) $empresa_id, $m->grupo_id ? (int) $m->grupo_id : null);
        return $lista ? $lista[0] : 'Não foi possível atualizar a mensagem.';
    }

    // Exclui para o próprio usuário (oculta) ou para todos (apaga conteúdo/anexo e marca excluida_em).
    public function excluir_mensagem($mensagem_id, $user_id, $empresa_id, $para_todos = false)
    {
        $m = $this->mensagem_do_usuario((int) $mensagem_id, (int) $user_id, (int) $empresa_id);
        if (!$m) {
            return 'Mensagem não encontrada.';
        }
        if (!$para_todos) {
            if (!$this->tem_tabela('mensagem_oculta')) {
                return 'Exclusão indisponível: aplique o script sql/2026-11-16_mensagens_recursos.sql no banco.';
            }
            $this->db->query(
                'INSERT IGNORE INTO mensagem_oculta (mensagem_id, user_id, oculta_em) VALUES (?, ?, ?)',
                array((int) $m->id, (int) $user_id, date('Y-m-d H:i:s'))
            );
            if ($this->tem_tabela('mensagem_favorita')) {
                $this->db->where('mensagem_id', (int) $m->id)->where('user_id', (int) $user_id)->delete('mensagem_favorita');
            }
            return array('modo' => 'para_mim', 'id' => (int) $m->id);
        }

        if (!$this->tem_coluna_msg('excluida_em')) {
            return 'Exclusão para todos indisponível: aplique o script sql/2026-11-16_mensagens_recursos.sql no banco.';
        }
        if ((int) $m->de_id !== (int) $user_id) {
            return 'Você só pode excluir para todos as mensagens que você enviou.';
        }
        if (!empty($m->sistema)) {
            return 'Avisos do sistema não podem ser excluídos.';
        }
        if (!empty($m->excluida_em)) {
            return array('modo' => 'para_todos', 'id' => (int) $m->id);
        }
        if (!empty($m->anexo)) {
            @unlink(FCPATH . self::PASTA_ANEXOS . basename($m->anexo));
        }
        $dados = array(
            'texto' => '',
            'excluida_em' => date('Y-m-d H:i:s'),
        );
        if ($this->aceita_anexos()) {
            $dados['anexo'] = null;
            $dados['anexo_nome'] = null;
            $dados['anexo_tipo'] = null;
            $dados['anexo_tamanho'] = null;
        }
        if ($this->tem_coluna_msg('fixada_em')) {
            $dados['fixada_em'] = null;
            $dados['fixada_por'] = null;
        }
        $this->db->where('id', (int) $m->id)->update('mensagem', $dados);
        if ($this->tem_tabela('mensagem_mencao')) {
            $this->db->where('mensagem_id', (int) $m->id)->delete('mensagem_mencao');
        }
        if ($this->tem_tabela('mensagem_favorita')) {
            $this->db->where('mensagem_id', (int) $m->id)->delete('mensagem_favorita');
        }
        $row = $this->buscar_registro_exibicao((int) $m->id);
        $lista = $this->enriquecer_mensagens(array($row), (int) $user_id, (int) $empresa_id, $m->grupo_id ? (int) $m->grupo_id : null);
        return array('modo' => 'para_todos', 'id' => (int) $m->id, 'mensagem' => $lista ? $lista[0] : null);
    }

    // Encaminha texto e/ou anexo (duplicando o arquivo físico) para uma ou mais conversas/grupos.
    public function encaminhar_mensagem($mensagem_id, $user_id, $empresa_id, $destinos)
    {
        $m = $this->mensagem_do_usuario((int) $mensagem_id, (int) $user_id, (int) $empresa_id);
        if (!$m || !empty($m->sistema) || !empty($m->excluida_em)) {
            return 'Mensagem não encontrada ou indisponível para encaminhar.';
        }
        $alvos = array();
        foreach ((array) $destinos as $chave) {
            $alvo = $this->ler_chave($chave);
            if (!$alvo) {
                continue;
            }
            if ($alvo['tipo'] === 'grupo') {
                $grupo = $this->grupo_do_membro($alvo['id'], $user_id, $empresa_id);
                if ($grupo) {
                    $alvos['g' . $grupo->id] = array('tipo' => 'grupo', 'grupo' => $grupo);
                }
            } elseif ($alvo['tipo'] === 'canal') {
                if ($this->pode_enviar_canal($alvo['id'], $user_id, $empresa_id)) {
                    $alvos['c' . $alvo['id']] = array('tipo' => 'canal', 'id' => $alvo['id']);
                }
            } elseif ($this->pode_conversar($user_id, $alvo['id'], $empresa_id)) {
                $alvos['u' . $alvo['id']] = array('tipo' => 'direta', 'id' => $alvo['id']);
            }
        }
        if (!$alvos) {
            return 'Escolha pelo menos uma conversa, grupo ou canal válido.';
        }

        $texto = $this->decodificar_emojis($m->texto);
        $transcricao = !empty($m->transcricao) ? $this->decodificar_emojis($m->transcricao) : null;
        $enviadas = array();
        foreach ($alvos as $chave => $alvo) {
            $anexo = null;
            if (!empty($m->anexo)) {
                $anexo = $this->duplicar_anexo($m);
                if (!$anexo) {
                    return 'Não foi possível copiar o anexo para encaminhar.';
                }
            }
            if ($alvo['tipo'] === 'grupo') {
                $nova = $this->enviar($user_id, 0, $empresa_id, $texto, $anexo, $alvo['grupo']->id, null, array(), true, null, $transcricao);
            } elseif ($alvo['tipo'] === 'canal') {
                $nova = $this->enviar($user_id, 0, $empresa_id, $texto, $anexo, null, null, array(), true, $alvo['id'], $transcricao);
            } else {
                $nova = $this->enviar($user_id, $alvo['id'], $empresa_id, $texto, $anexo, null, null, array(), true, null, $transcricao);
            }
            if ($nova) {
                $enviadas[$chave] = $nova;
            } elseif ($anexo) {
                @unlink(FCPATH . self::PASTA_ANEXOS . $anexo['arquivo']);
            }
        }
        return $enviadas ? $enviadas : 'Não foi possível encaminhar a mensagem.';
    }

    private function duplicar_anexo($m)
    {
        $origem = FCPATH . self::PASTA_ANEXOS . basename($m->anexo);
        if (!is_file($origem)) {
            return null;
        }
        $ext = strtolower(pathinfo($m->anexo, PATHINFO_EXTENSION));
        $novo_nome = md5(uniqid(mt_rand(), true)) . ($ext !== '' ? '.' . $ext : '');
        $destino = FCPATH . self::PASTA_ANEXOS . $novo_nome;
        if (!@copy($origem, $destino)) {
            return null;
        }
        return array(
            'arquivo' => $novo_nome,
            'nome' => (string) $m->anexo_nome,
            'tipo' => (string) $m->anexo_tipo,
            'tamanho' => (int) $m->anexo_tamanho,
        );
    }

    // Verifica se outra pessoa já leu a mensagem (trava de edição).
    public function mensagem_ja_foi_lida($m, $autor_id)
    {
        if (!empty($m->canal_id)) {
            if ($this->tem_tabela('mensagem_canal_leitura')) {
                return $this->db->where('canal_id', (int) $m->canal_id)
                    ->where('user_id !=', (int) $autor_id)
                    ->where('ultima_lida_id >=', (int) $m->id)
                    ->count_all_results('mensagem_canal_leitura') > 0;
            }
            return false;
        }
        if (empty($m->grupo_id)) {
            return $m->lida_em !== null;
        }
        if ($this->tem_tabela('mensagem_leitura')) {
            $lida = $this->db->where('mensagem_id', (int) $m->id)
                ->where('user_id !=', (int) $autor_id)
                ->where('lida_em IS NOT NULL', null, false)
                ->count_all_results('mensagem_leitura');
            if ($lida > 0) {
                return true;
            }
        }
        $membro_lido = $this->db->where('grupo_id', (int) $m->grupo_id)
            ->where('user_id !=', (int) $autor_id)
            ->where('saiu_em IS NULL', null, false)
            ->where('ultima_lida_id >=', (int) $m->id)
            ->count_all_results('mensagem_grupo_membro');
        return $membro_lido > 0;
    }

    /* ---------- Fixar, Favoritar e Visualizações ---------- */

    public function fixar_mensagem($mensagem_id, $user_id, $empresa_id, $fixar = true)
    {
        if (!$this->tem_coluna_msg('fixada_em')) {
            return 'Fixação indisponível: aplique o script sql/2026-11-16_mensagens_recursos.sql no banco.';
        }
        $m = $this->mensagem_do_usuario((int) $mensagem_id, (int) $user_id, (int) $empresa_id);
        if (!$m || !empty($m->sistema) || !empty($m->excluida_em)) {
            return 'Esta mensagem não pode ser fixada.';
        }
        $dados = $fixar
            ? array('fixada_em' => date('Y-m-d H:i:s'), 'fixada_por' => (int) $user_id)
            : array('fixada_em' => null, 'fixada_por' => null);
        $this->db->where('id', (int) $m->id)->update('mensagem', $dados);
        return true;
    }

    public function fixadas_conversa($chave, $user_id, $empresa_id)
    {
        if (!$this->tem_coluna_msg('fixada_em')) {
            return array();
        }
        $alvo = $this->ler_chave($chave);
        if (!$alvo) {
            return array();
        }
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        $this->db->select('m.id, m.de_id, m.para_id, m.texto, m.created_at, m.lida_em, m.sistema, ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', u.first_name, u.last_name', false)
            ->from('mensagem m')
            ->join('users u', 'u.id = m.de_id', 'left')
            ->where('m.empresa_id', $empresa_id)
            ->where('m.fixada_em IS NOT NULL', null, false)
            ->where('m.excluida_em IS NULL', null, false);
        $grupo_id = null;
        if ($alvo['tipo'] === 'grupo') {
            if (!$this->grupo_do_membro($alvo['id'], $user_id, $empresa_id)) {
                return array();
            }
            $grupo_id = (int) $alvo['id'];
            $this->db->where('m.grupo_id', $grupo_id);
        } elseif ($alvo['tipo'] === 'canal') {
            if (!$this->canal_do_usuario($alvo['id'], $user_id, $empresa_id)) {
                return array();
            }
            $this->db->where('m.canal_id', (int) $alvo['id']);
        } else {
            $outro = (int) $alvo['id'];
            $this->db->group_start()
                ->group_start()->where('m.de_id', $user_id)->where('m.para_id', $outro)->group_end()
                ->or_group_start()->where('m.de_id', $outro)->where('m.para_id', $user_id)->group_end()
            ->group_end();
            if ($this->aceita_grupos()) {
                $this->db->where('m.grupo_id IS NULL', null, false);
            }
            if ($this->aceita_canais()) {
                $this->db->where('m.canal_id IS NULL', null, false);
            }
        }
        $this->aplicar_filtro_oculta('m', $user_id);
        $rows = $this->db->order_by('m.fixada_em', 'DESC')->order_by('m.id', 'DESC')->limit(50)->get()->result();
        return $this->enriquecer_mensagens($rows, $user_id, $empresa_id, $grupo_id);
    }

    public function alternar_favorita($mensagem_id, $user_id, $empresa_id)
    {
        if (!$this->tem_tabela('mensagem_favorita')) {
            return 'Favoritos indisponíveis: aplique o script sql/2026-11-16_mensagens_recursos.sql no banco.';
        }
        $m = $this->mensagem_do_usuario((int) $mensagem_id, (int) $user_id, (int) $empresa_id);
        if (!$m || !empty($m->sistema) || !empty($m->excluida_em)) {
            return 'Esta mensagem não pode ser favoritada.';
        }
        $existe = $this->db->where('mensagem_id', (int) $m->id)->where('user_id', (int) $user_id)->count_all_results('mensagem_favorita') > 0;
        if ($existe) {
            $this->db->where('mensagem_id', (int) $m->id)->where('user_id', (int) $user_id)->delete('mensagem_favorita');
            return false;
        }
        $this->db->insert('mensagem_favorita', array(
            'mensagem_id' => (int) $m->id,
            'user_id' => (int) $user_id,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return true;
    }

    public function favoritas($user_id, $empresa_id, $chave = '')
    {
        if (!$this->tem_tabela('mensagem_favorita')) {
            return array();
        }
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        $this->db->select('m.id, m.de_id, m.para_id, m.grupo_id, ' . ($this->aceita_canais() ? 'm.canal_id' : 'NULL AS canal_id') . ', m.texto, m.created_at, m.lida_em, m.sistema, ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', u.first_name, u.last_name, f.created_at AS favoritada_em', false)
            ->from('mensagem_favorita f')
            ->join('mensagem m', 'm.id = f.mensagem_id')
            ->join('users u', 'u.id = m.de_id', 'left')
            ->where('f.user_id', $user_id)
            ->where('m.empresa_id', $empresa_id)
            ->where('m.excluida_em IS NULL', null, false);
        $this->aplicar_filtro_acesso_conversa($user_id, $empresa_id, $chave);
        $this->aplicar_filtro_oculta('m', $user_id);
        $rows = $this->db->order_by('f.created_at', 'DESC')->limit(100)->get()->result();
        return $this->formatar_resultados_lista($rows, $user_id, $empresa_id);
    }

    public function visualizacoes_mensagem($mensagem_id, $user_id, $empresa_id)
    {
        $m = $this->mensagem_do_usuario((int) $mensagem_id, (int) $user_id, (int) $empresa_id);
        if (!$m || !empty($m->sistema)) {
            return null;
        }
        if (empty($m->grupo_id)) {
            $outro_id = ((int) $m->de_id === (int) $user_id) ? (int) $m->para_id : (int) $m->de_id;
            $u = $this->db->select('id, first_name, last_name, ' . $this->campo_foto(''), false)->where('id', $outro_id)->get('users')->row();
            if (!$u) {
                return array('tipo' => 'direta', 'lidas' => array(), 'entregues' => array(), 'pendentes' => array());
            }
            $fotos = $this->fotos(array($u));
            $item = array(
                'id' => (int) $u->id,
                'nome' => $this->nome($u),
                'foto' => $fotos[(int) $u->id],
                'lida_em' => $m->lida_em ? date('d/m H:i', strtotime($m->lida_em)) : null,
                'entregue_em' => !empty($m->entregue_em) ? date('d/m H:i', strtotime($m->entregue_em)) : null,
            );
            return array(
                'tipo' => 'direta',
                'lidas' => $m->lida_em ? array($item) : array(),
                'entregues' => (!$m->lida_em && !empty($m->entregue_em)) ? array($item) : array(),
                'pendentes' => (!$m->lida_em && empty($m->entregue_em)) ? array($item) : array(),
            );
        }

        $grupo_id = (int) $m->grupo_id;
        $membros = $this->db->select('u.id, u.first_name, u.last_name, gm.ultima_lida_id, ' . ($this->tem_coluna('mensagem_grupo_membro', 'ultima_entregue_id') ? 'gm.ultima_entregue_id' : '0 AS ultima_entregue_id') . ', ' . $this->campo_foto('u'), false)
            ->join('users u', 'u.id = gm.user_id')
            ->where('gm.grupo_id', $grupo_id)
            ->where('gm.saiu_em IS NULL', null, false)
            ->where('gm.user_id !=', (int) $m->de_id)
            ->order_by('u.first_name', 'ASC')
            ->get('mensagem_grupo_membro gm')
            ->result();

        $leituras = array();
        if ($this->tem_tabela('mensagem_leitura')) {
            $lrows = $this->db->where('mensagem_id', (int) $m->id)->get('mensagem_leitura')->result();
            foreach ($lrows as $lr) {
                $leituras[(int) $lr->user_id] = $lr;
            }
        }
        $fotos = $this->fotos($membros);
        $lidas = array();
        $entregues = array();
        $pendentes = array();
        foreach ($membros as $mb) {
            $uid = (int) $mb->id;
            $lr = isset($leituras[$uid]) ? $leituras[$uid] : null;
            $foi_lida = ($lr && $lr->lida_em) || ((int) $mb->ultima_lida_id >= (int) $m->id);
            $foi_entregue = $foi_lida || ($lr && $lr->entregue_em) || ((int) $mb->ultima_entregue_id >= (int) $m->id);
            $item = array(
                'id' => $uid,
                'nome' => $this->nome($mb),
                'foto' => $fotos[$uid],
                'lida_em' => ($lr && $lr->lida_em) ? date('d/m H:i', strtotime($lr->lida_em)) : null,
                'entregue_em' => ($lr && $lr->entregue_em) ? date('d/m H:i', strtotime($lr->entregue_em)) : null,
            );
            if ($foi_lida) {
                $lidas[] = $item;
            } elseif ($foi_entregue) {
                $entregues[] = $item;
            } else {
                $pendentes[] = $item;
            }
        }
        return array('tipo' => 'grupo', 'lidas' => $lidas, 'entregues' => $entregues, 'pendentes' => $pendentes);
    }

    /* ---------- Busca de mensagens ---------- */

    public function buscar_mensagens($user_id, $empresa_id, $filtros)
    {
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        $q = trim((string) (isset($filtros['q']) ? $filtros['q'] : ''));
        $autor_id = isset($filtros['usuario']) ? (int) $filtros['usuario'] : 0;
        $de = trim((string) (isset($filtros['de']) ? $filtros['de'] : ''));
        $ate = trim((string) (isset($filtros['ate']) ? $filtros['ate'] : ''));
        $tipo = trim((string) (isset($filtros['tipo']) ? $filtros['tipo'] : ''));
        $chave = trim((string) (isset($filtros['conversa']) ? $filtros['conversa'] : ''));

        $this->db->select('m.id, m.de_id, m.para_id, ' . ($this->aceita_grupos() ? 'm.grupo_id' : 'NULL AS grupo_id') . ', ' . ($this->aceita_canais() ? 'm.canal_id' : 'NULL AS canal_id') . ', m.texto, m.created_at, m.lida_em, ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', u.first_name, u.last_name', false)
            ->from('mensagem m')
            ->join('users u', 'u.id = m.de_id', 'left')
            ->where('m.empresa_id', $empresa_id);
        if ($this->aceita_grupos()) {
            $this->db->where('m.sistema', 0);
        }
        if ($this->tem_coluna_msg('excluida_em')) {
            $this->db->where('m.excluida_em IS NULL', null, false);
        }
        $this->aplicar_filtro_acesso_conversa($user_id, $empresa_id, $chave);
        $this->aplicar_filtro_oculta('m', $user_id);

        if ($q !== '') {
            $q_cod = $this->codificar_emojis($q);
            $this->db->group_start()
                ->like('m.texto', $q_cod);
            if ($this->aceita_anexos()) {
                $this->db->or_like('m.anexo_nome', $q_cod);
            }
            $this->db->group_end();
        }
        if ($autor_id > 0) {
            $this->db->where('m.de_id', $autor_id);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $de)) {
            $this->db->where('m.created_at >=', $de . ' 00:00:00');
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $ate)) {
            $this->db->where('m.created_at <=', $ate . ' 23:59:59');
        }
        if ($this->aceita_anexos() && $tipo !== '') {
            if ($tipo === 'texto') {
                $this->db->where('m.anexo IS NULL', null, false);
            } elseif (in_array($tipo, array('imagem', 'video', 'audio', 'arquivo'), true)) {
                $this->db->where('m.anexo_tipo', $tipo);
            }
        }
        $rows = $this->db->order_by('m.id', 'DESC')->limit(50)->get()->result();
        return $this->formatar_resultados_lista($rows, $user_id, $empresa_id);
    }

    private function aplicar_filtro_acesso_conversa($user_id, $empresa_id, $chave)
    {
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        if ($chave !== '') {
            $alvo = $this->ler_chave($chave);
            if ($alvo && $alvo['tipo'] === 'grupo' && $this->aceita_grupos()) {
                $this->db->join('mensagem_grupo_membro gm', 'gm.grupo_id = m.grupo_id AND gm.user_id = ' . $user_id . ' AND gm.saiu_em IS NULL')
                    ->join('mensagem_grupo g', 'g.id = m.grupo_id AND g.empresa_id = ' . $empresa_id . ' AND g.excluido_em IS NULL')
                    ->where('m.grupo_id', (int) $alvo['id']);
                return;
            }
            if ($alvo && $alvo['tipo'] === 'canal' && $this->aceita_canais()) {
                if (!$this->canal_do_usuario($alvo['id'], $user_id, $empresa_id)) {
                    $this->db->where('1 = 0', null, false);
                    return;
                }
                $this->db->where('m.canal_id', (int) $alvo['id']);
                return;
            }
            if ($alvo && $alvo['tipo'] === 'direta') {
                $outro = (int) $alvo['id'];
                $this->db->group_start()
                    ->group_start()->where('m.de_id', $user_id)->where('m.para_id', $outro)->group_end()
                    ->or_group_start()->where('m.de_id', $outro)->where('m.para_id', $user_id)->group_end()
                ->group_end();
                if ($this->aceita_grupos()) {
                    $this->db->where('m.grupo_id IS NULL', null, false);
                }
                if ($this->aceita_canais()) {
                    $this->db->where('m.canal_id IS NULL', null, false);
                }
                return;
            }
        }

        if ($this->aceita_grupos()) {
            $cond_canal = $this->aceita_canais()
                ? ' OR (m.canal_id IS NOT NULL AND EXISTS (SELECT 1 FROM mensagem_canal c WHERE c.id = m.canal_id AND c.empresa_id = ' . $empresa_id . ' AND c.excluido_em IS NULL AND (c.criador_id = ' . $user_id . ' OR EXISTS (SELECT 1 FROM mensagem_canal_grupo cg JOIN users_groups ug ON ug.group_id = cg.group_id WHERE cg.canal_id = c.id AND ug.user_id = ' . $user_id . '))))'
                : '';
            $cond_sem_canal = $this->aceita_canais() ? 'm.canal_id IS NULL AND ' : '';
            $this->db->where(
                '((' . $cond_sem_canal . 'm.grupo_id IS NULL AND (m.de_id = ' . $user_id . ' OR m.para_id = ' . $user_id . ')) OR '
                . '(m.grupo_id IS NOT NULL AND EXISTS (SELECT 1 FROM mensagem_grupo_membro gm '
                . 'JOIN mensagem_grupo g ON g.id = gm.grupo_id AND g.empresa_id = ' . $empresa_id . ' AND g.excluido_em IS NULL '
                . 'WHERE gm.grupo_id = m.grupo_id AND gm.user_id = ' . $user_id . ' AND gm.saiu_em IS NULL))'
                . $cond_canal . ')',
                null,
                false
            );
        } else {
            $this->db->group_start()->where('m.de_id', $user_id)->or_where('m.para_id', $user_id)->group_end();
        }
    }

    private function formatar_resultados_lista($rows, $user_id, $empresa_id)
    {
        if (!$rows) {
            return array();
        }
        $nomes_usuarios = array();
        $nomes_grupos = array();
        $nomes_canais = array();
        $ids_u = array();
        $ids_g = array();
        $ids_c = array();
        foreach ($rows as $r) {
            if (!empty($r->canal_id)) {
                $ids_c[(int) $r->canal_id] = true;
            } elseif (!empty($r->grupo_id)) {
                $ids_g[(int) $r->grupo_id] = true;
            } else {
                $outro = ((int) $r->de_id === $user_id) ? (int) $r->para_id : (int) $r->de_id;
                $ids_u[$outro] = true;
            }
        }
        if ($ids_u) {
            $urows = $this->db->select('id, first_name, last_name')->where_in('id', array_keys($ids_u))->get('users')->result();
            foreach ($urows as $u) {
                $nomes_usuarios[(int) $u->id] = $this->nome($u);
            }
        }
        if ($ids_g && $this->aceita_grupos()) {
            $grows = $this->db->select('id, nome')->where_in('id', array_keys($ids_g))->get('mensagem_grupo')->result();
            foreach ($grows as $g) {
                $nomes_grupos[(int) $g->id] = $this->decodificar_emojis($g->nome);
            }
        }
        if ($ids_c && $this->aceita_canais()) {
            $crows = $this->db->select('id, nome')->where_in('id', array_keys($ids_c))->get('mensagem_canal')->result();
            foreach ($crows as $c) {
                $nomes_canais[(int) $c->id] = $this->decodificar_emojis($c->nome);
            }
        }

        $mensagens = $this->enriquecer_mensagens($rows, $user_id, $empresa_id);
        $saida = array();
        foreach ($rows as $idx => $r) {
            $m = $mensagens[$idx];
            if (!empty($r->canal_id)) {
                $cid = (int) $r->canal_id;
                $m['conversa_chave'] = 'c' . $cid;
                $m['conversa_tipo'] = 'canal';
                $m['conversa_nome'] = isset($nomes_canais[$cid]) ? '# ' . $nomes_canais[$cid] : 'Canal #' . $cid;
            } elseif (!empty($r->grupo_id)) {
                $gid = (int) $r->grupo_id;
                $m['conversa_chave'] = 'g' . $gid;
                $m['conversa_tipo'] = 'grupo';
                $m['conversa_nome'] = isset($nomes_grupos[$gid]) ? $nomes_grupos[$gid] : 'Grupo #' . $gid;
            } else {
                $outro = ((int) $r->de_id === $user_id) ? (int) $r->para_id : (int) $r->de_id;
                $m['conversa_chave'] = 'u' . $outro;
                $m['conversa_tipo'] = 'direta';
                $m['conversa_nome'] = $this->ver_como_suporte($user_id, $outro, $empresa_id)
                    ? 'Suporte'
                    : (isset($nomes_usuarios[$outro]) ? $nomes_usuarios[$outro] : 'Usuário #' . $outro);
            }
            $saida[] = $m;
        }
        return $saida;
    }

    /* ---------- Menções (@) ---------- */

    private function registrar_mencoes($mensagem_id, $de_id, $para_id, $empresa_id, $grupo_id, $texto, $mencoes_explicitas, $canal_id = null)
    {
        $candidatos = array();
        if ($canal_id) {
            foreach ($this->membros_canal($canal_id, $empresa_id, $de_id) as $mb) {
                if ((int) $mb['id'] !== (int) $de_id) {
                    $candidatos[(int) $mb['id']] = $mb['nome'];
                }
            }
        } elseif ($grupo_id) {
            foreach ($this->membros($grupo_id) as $mb) {
                if ((int) $mb['id'] !== (int) $de_id) {
                    $candidatos[(int) $mb['id']] = $mb['nome'];
                }
            }
        } elseif ($para_id > 0 && $this->pode_conversar($de_id, $para_id, $empresa_id)) {
            $candidatos[(int) $para_id] = $this->nome_usuario($para_id);
        }
        if (!$candidatos) {
            return;
        }

        $encontrados = array();
        foreach ((array) $mencoes_explicitas as $uid) {
            $uid = (int) $uid;
            if (isset($candidatos[$uid])) {
                $encontrados[$uid] = true;
            }
        }
        $texto_norm = $this->normalizar($texto);
        if (strpos($texto_norm, '@') !== false) {
            // @todos no canal notifica todos os usuários dos grupos com acesso ao canal.
            if ($canal_id && preg_match('/(?:^|\s)@todos(?=$|\s|[,.;:!?])/u', $texto_norm)) {
                foreach (array_keys($candidatos) as $uid) {
                    $encontrados[$uid] = true;
                }
            }
            foreach ($candidatos as $uid => $nome) {
                $nome_norm = $this->normalizar($nome);
                if ($nome_norm !== '' && preg_match('/(?:^|\s)@' . preg_quote($nome_norm, '/') . '(?=$|\s|[,.;:!?])/u', $texto_norm)) {
                    $encontrados[$uid] = true;
                }
            }
        }
        foreach (array_keys($encontrados) as $uid) {
            $this->db->query(
                'INSERT IGNORE INTO mensagem_mencao (mensagem_id, user_id, empresa_id, lida_em) VALUES (?, ?, ?, NULL)',
                array((int) $mensagem_id, (int) $uid, (int) $empresa_id)
            );
        }
    }

    private function marcar_mencoes_lidas_canal($user_id, $canal_id)
    {
        if (!$this->tem_tabela('mensagem_mencao')) {
            return;
        }
        $this->db->query(
            'UPDATE mensagem_mencao mm JOIN mensagem m ON m.id = mm.mensagem_id '
            . 'SET mm.lida_em = ? '
            . 'WHERE mm.user_id = ? AND mm.lida_em IS NULL AND m.canal_id = ?',
            array(date('Y-m-d H:i:s'), (int) $user_id, (int) $canal_id)
        );
    }

    private function marcar_mencoes_lidas_direta($user_id, $outro_id, $empresa_id)
    {
        if (!$this->tem_tabela('mensagem_mencao')) {
            return;
        }
        $sql = 'UPDATE mensagem_mencao mm JOIN mensagem m ON m.id = mm.mensagem_id '
            . 'SET mm.lida_em = ? '
            . 'WHERE mm.user_id = ? AND mm.empresa_id = ? AND mm.lida_em IS NULL '
            . 'AND m.de_id = ? AND m.para_id = ?';
        if ($this->aceita_grupos()) {
            $sql .= ' AND m.grupo_id IS NULL';
        }
        $this->db->query($sql, array(date('Y-m-d H:i:s'), (int) $user_id, (int) $empresa_id, (int) $outro_id, (int) $user_id));
    }

    private function marcar_mencoes_lidas_grupo($user_id, $grupo_id)
    {
        if (!$this->tem_tabela('mensagem_mencao')) {
            return;
        }
        $this->db->query(
            'UPDATE mensagem_mencao mm JOIN mensagem m ON m.id = mm.mensagem_id '
            . 'SET mm.lida_em = ? '
            . 'WHERE mm.user_id = ? AND mm.lida_em IS NULL AND m.grupo_id = ?',
            array(date('Y-m-d H:i:s'), (int) $user_id, (int) $grupo_id)
        );
    }

    private function mencoes_nao_lidas_por_conversa($user_id, $empresa_id)
    {
        $mapa = array();
        if (!$this->tem_tabela('mensagem_mencao')) {
            return $mapa;
        }
        $rows = $this->db->select('m.de_id, ' . ($this->aceita_grupos() ? 'm.grupo_id' : 'NULL AS grupo_id') . ', ' . ($this->aceita_canais() ? 'm.canal_id' : 'NULL AS canal_id'), false)
            ->from('mensagem_mencao mm')
            ->join('mensagem m', 'm.id = mm.mensagem_id')
            ->where('mm.user_id', (int) $user_id)
            ->where('mm.empresa_id', (int) $empresa_id)
            ->where('mm.lida_em IS NULL', null, false)
            ->get()
            ->result();
        foreach ($rows as $r) {
            $chave = !empty($r->canal_id) ? 'c' . (int) $r->canal_id : (!empty($r->grupo_id) ? 'g' . (int) $r->grupo_id : 'u' . (int) $r->de_id);
            if (!isset($mapa[$chave])) {
                $mapa[$chave] = 0;
            }
            $mapa[$chave]++;
        }
        return $mapa;
    }

    /* ---------- Indicador de digitação / gravação ---------- */

    public function definir_digitando($user_id, $empresa_id, $chave, $estado)
    {
        if (!$this->tem_tabela('mensagem_digitando')) {
            return;
        }
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        $estado = trim((string) $estado);
        if (!in_array($estado, array('digitando', 'gravando'), true) || !$this->ler_chave($chave)) {
            $this->db->where('empresa_id', $empresa_id)->where('user_id', $user_id)->delete('mensagem_digitando');
            return;
        }
        $this->db->query(
            'REPLACE INTO mensagem_digitando (empresa_id, user_id, chave, estado, atualizado_em) VALUES (?, ?, ?, ?, ?)',
            array($empresa_id, $user_id, (string) $chave, $estado, date('Y-m-d H:i:s'))
        );
    }

    public function quem_esta_digitando($user_id, $empresa_id, $chave_conversa)
    {
        if (!$this->tem_tabela('mensagem_digitando')) {
            return array();
        }
        $alvo = $this->ler_chave($chave_conversa);
        if (!$alvo) {
            return array();
        }
        $limite = date('Y-m-d H:i:s', time() - self::JANELA_DIGITANDO_SEG);
        $this->db->select('u.id, u.first_name, d.estado')
            ->from('mensagem_digitando d')
            ->join('users u', 'u.id = d.user_id')
            ->where('d.empresa_id', (int) $empresa_id)
            ->where('d.atualizado_em >=', $limite);
        if ($alvo['tipo'] === 'grupo') {
            $this->db->where('d.chave', 'g' . (int) $alvo['id'])
                ->where('d.user_id !=', (int) $user_id);
        } elseif ($alvo['tipo'] === 'canal') {
            $this->db->where('d.chave', 'c' . (int) $alvo['id'])
                ->where('d.user_id !=', (int) $user_id);
        } else {
            $this->db->where('d.chave', 'u' . (int) $user_id)
                ->where('d.user_id', (int) $alvo['id']);
        }
        $rows = $this->db->get()->result();
        $lista = array();
        foreach ($rows as $r) {
            $suporte = $alvo['tipo'] === 'direta' && $this->ver_como_suporte($user_id, $r->id, $empresa_id);
            $lista[] = array(
                'id' => (int) $r->id,
                'nome' => $suporte ? 'Suporte' : trim($r->first_name),
                'estado' => $r->estado === 'gravando' ? 'gravando' : 'digitando',
            );
        }
        return $lista;
    }

    public function digitando_nas_conversas($user_id, $empresa_id)
    {
        $mapa = array();
        if (!$this->tem_tabela('mensagem_digitando')) {
            return $mapa;
        }
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        $limite = date('Y-m-d H:i:s', time() - self::JANELA_DIGITANDO_SEG);
        $rows = $this->db->select('d.user_id, d.chave, d.estado, u.first_name')
            ->from('mensagem_digitando d')
            ->join('users u', 'u.id = d.user_id')
            ->where('d.empresa_id', $empresa_id)
            ->where('d.user_id !=', $user_id)
            ->where('d.atualizado_em >=', $limite)
            ->get()
            ->result();
        $pode_ver = array();
        foreach ($rows as $r) {
            if ($r->chave === 'u' . $user_id) {
                $mapa['u' . (int) $r->user_id] = array(
                    'estado' => $r->estado,
                    'texto' => $r->estado === 'gravando' ? 'gravando áudio…' : 'digitando…',
                );
            } elseif (strpos($r->chave, 'g') === 0 || strpos($r->chave, 'c') === 0) {
                // Só grupos e canais dos quais o usuário participa.
                if (!isset($pode_ver[$r->chave])) {
                    $alvo = $this->ler_chave($r->chave);
                    $pode_ver[$r->chave] = $alvo && (
                        ($alvo['tipo'] === 'grupo' && $this->grupo_do_membro($alvo['id'], $user_id, $empresa_id))
                        || ($alvo['tipo'] === 'canal' && $this->canal_do_usuario($alvo['id'], $user_id, $empresa_id))
                    );
                }
                if (!$pode_ver[$r->chave]) {
                    continue;
                }
                $mapa[$r->chave] = array(
                    'estado' => $r->estado,
                    'texto' => trim($r->first_name) . ($r->estado === 'gravando' ? ' está gravando áudio…' : ' está digitando…'),
                );
            }
        }
        return $mapa;
    }

    /* ---------- Notificações por conversa / silenciamento ---------- */

    public function obter_config_conversa($user_id, $empresa_id, $chave)
    {
        $padrao = array('modo_notif' => 'todas', 'silenciada_ate' => null, 'silenciada' => false, 'marcada_nao_lida' => false);
        if (!$this->tem_tabela('mensagem_conversa_config')) {
            return $padrao;
        }
        $row = $this->db->where('empresa_id', (int) $empresa_id)
            ->where('user_id', (int) $user_id)
            ->where('chave', (string) $chave)
            ->get('mensagem_conversa_config')
            ->row();
        if (!$row) {
            return $padrao;
        }
        $silenciada = !empty($row->silenciada_ate) && strtotime($row->silenciada_ate) > time();
        return array(
            'modo_notif' => in_array($row->modo_notif, array('todas', 'mencoes', 'nenhuma'), true) ? $row->modo_notif : 'todas',
            'silenciada_ate' => $silenciada ? $row->silenciada_ate : null,
            'silenciada' => $silenciada,
            'marcada_nao_lida' => !empty($row->marcada_nao_lida),
        );
    }

    public function salvar_config_conversa($user_id, $empresa_id, $chave, $modo_notif, $silenciar_minutos = null)
    {
        if (!$this->tem_tabela('mensagem_conversa_config')) {
            return 'Configuração indisponível: aplique o script sql/2026-11-16_mensagens_recursos.sql no banco.';
        }
        if (!$this->ler_chave($chave)) {
            return 'Conversa inválida.';
        }
        if (!in_array($modo_notif, array('todas', 'mencoes', 'nenhuma'), true)) {
            $modo_notif = 'todas';
        }
        $atual = $this->obter_config_conversa($user_id, $empresa_id, $chave);
        $silenciada_ate = $atual['silenciada_ate'];
        if ($silenciar_minutos !== null && $silenciar_minutos !== '') {
            $min = (int) $silenciar_minutos;
            if ($min === 0) {
                $silenciada_ate = null;
            } elseif ($min < 0) {
                $silenciada_ate = '2099-12-31 23:59:59';
            } else {
                $silenciada_ate = date('Y-m-d H:i:s', time() + ($min * 60));
            }
        }
        $this->db->query(
            'REPLACE INTO mensagem_conversa_config (empresa_id, user_id, chave, modo_notif, silenciada_ate, atualizado_em) VALUES (?, ?, ?, ?, ?, ?)',
            array((int) $empresa_id, (int) $user_id, (string) $chave, $modo_notif, $silenciada_ate, date('Y-m-d H:i:s'))
        );
        return $this->obter_config_conversa($user_id, $empresa_id, $chave);
    }

    private function configs_conversa($user_id, $empresa_id)
    {
        $mapa = array();
        if (!$this->tem_tabela('mensagem_conversa_config')) {
            return $mapa;
        }
        $rows = $this->db->where('empresa_id', (int) $empresa_id)->where('user_id', (int) $user_id)->get('mensagem_conversa_config')->result();
        $agora = time();
        foreach ($rows as $row) {
            $silenciada = !empty($row->silenciada_ate) && strtotime($row->silenciada_ate) > $agora;
            $mapa[$row->chave] = array(
                'modo_notif' => in_array($row->modo_notif, array('todas', 'mencoes', 'nenhuma'), true) ? $row->modo_notif : 'todas',
                'silenciada_ate' => $silenciada ? $row->silenciada_ate : null,
                'silenciada' => $silenciada,
                'marcada_nao_lida' => !empty($row->marcada_nao_lida),
            );
        }
        return $mapa;
    }

    /* ---------- Anexos ---------- */

    // Anexo de uma mensagem, só se o usuário participa da conversa (direta ou grupo ativo) e não foi excluída.
    public function anexo($mensagem_id, $user_id, $empresa_id)
    {
        if (!$this->aceita_anexos()) {
            return null;
        }
        $campos = 'id, de_id, para_id, anexo, anexo_nome, anexo_tipo, anexo_tamanho'
            . ($this->aceita_grupos() ? ', grupo_id' : ', NULL AS grupo_id')
            . ($this->aceita_canais() ? ', canal_id' : ', NULL AS canal_id')
            . ($this->tem_coluna_msg('excluida_em') ? ', excluida_em' : ', NULL AS excluida_em');
        $m = $this->db->select($campos, false)
            ->where('id', (int) $mensagem_id)
            ->where('empresa_id', (int) $empresa_id)
            ->get('mensagem')
            ->row();
        if (!$m || !$m->anexo || !empty($m->excluida_em)) {
            return null;
        }
        if (!empty($m->canal_id)) {
            return $this->canal_do_usuario($m->canal_id, $user_id, $empresa_id) ? $m : null;
        }
        if ($m->grupo_id) {
            return $this->grupo_do_membro($m->grupo_id, $user_id, $empresa_id) ? $m : null;
        }
        return ((int) $m->de_id === (int) $user_id || (int) $m->para_id === (int) $user_id) ? $m : null;
    }

    /* ---------- Cabeçalho (envelope, sino e avisos) ---------- */

    /**
     * Não lidas agrupadas por conversa (pessoa ou grupo), mais recentes primeiro.
     * Com $depois_id, também devolve as mensagens novas (id maior) respeitando configurações/silenciamento.
     */
    public function nao_lidas($user_id, $empresa_id, $depois_id = null)
    {
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        $resultado = array('total' => 0, 'remetentes' => array(), 'novas' => array(), 'lembretes' => array(), 'ultimo_id' => 0);
        if (!$this->disponivel()) {
            return $resultado;
        }
        $this->load->model('mensagens_recursos_model');
        $this->mensagens_recursos_model->processar_agendadas();
        $this->mensagens_recursos_model->heartbeat_status($empresa_id, $user_id);
        $this->marcar_entregues($user_id, $empresa_id);
        if ($depois_id !== null) {
            $resultado['lembretes'] = $this->mensagens_recursos_model->lembretes_vencidos($empresa_id, $user_id);
        }

        $filtro_oculta = $this->sql_nao_oculta('m', $user_id);
        $this->db->select('m.id, m.de_id, m.texto, m.created_at, ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', NULL AS grupo_id, NULL AS grupo_nome, NULL AS canal_id, NULL AS canal_nome, u.first_name, u.last_name, ' . $this->campo_foto('u'), false)
            ->join('users u', 'u.id = m.de_id')
            ->where('m.empresa_id', $empresa_id)
            ->where('m.para_id', $user_id)
            ->where('m.lida_em IS NULL', null, false);
        if ($this->tem_coluna_msg('excluida_em')) {
            $this->db->where('m.excluida_em IS NULL', null, false);
        }
        if ($this->aceita_grupos()) {
            $this->db->where('m.grupo_id IS NULL', null, false);
            $this->db->where('m.sistema', 0);
        }
        if ($this->aceita_canais()) {
            $this->db->where('m.canal_id IS NULL', null, false);
        }
        if ($filtro_oculta !== '') {
            $this->aplicar_filtro_oculta('m', $user_id);
        }
        $rows = $this->db->order_by('m.id', 'DESC')->limit(200)->get('mensagem m')->result();

        if ($this->aceita_grupos()) {
            $this->db->select('m.id, m.de_id, m.texto, m.created_at, ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', g.id AS grupo_id, g.nome AS grupo_nome, NULL AS canal_id, NULL AS canal_nome, u.first_name, u.last_name, ' . $this->campo_foto('u'), false)
                ->join('mensagem_grupo_membro gm', 'gm.grupo_id = m.grupo_id AND gm.user_id = ' . $user_id . ' AND gm.saiu_em IS NULL')
                ->join('mensagem_grupo g', 'g.id = m.grupo_id AND g.excluido_em IS NULL')
                ->join('users u', 'u.id = m.de_id')
                ->where('m.empresa_id', $empresa_id)
                ->where('m.id > gm.ultima_lida_id', null, false)
                ->where('m.de_id !=', $user_id)
                ->where('m.sistema', 0);
            if ($this->tem_coluna_msg('excluida_em')) {
                $this->db->where('m.excluida_em IS NULL', null, false);
            }
            if ($filtro_oculta !== '') {
                $this->aplicar_filtro_oculta('m', $user_id);
            }
            $grupo_rows = $this->db->order_by('m.id', 'DESC')->limit(200)->get('mensagem m')->result();
            $rows = array_merge($rows, $grupo_rows);
        }

        if ($this->aceita_canais()) {
            $this->db->select('m.id, m.de_id, m.texto, m.created_at, ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', NULL AS grupo_id, NULL AS grupo_nome, c.id AS canal_id, c.nome AS canal_nome, u.first_name, u.last_name, ' . $this->campo_foto('u'), false)
                ->join('mensagem_canal c', 'c.id = m.canal_id AND c.empresa_id = ' . $empresa_id . ' AND c.excluido_em IS NULL')
                ->join('mensagem_canal_leitura cl', 'cl.canal_id = c.id AND cl.user_id = ' . $user_id, 'left')
                ->join('users u', 'u.id = m.de_id')
                ->where('m.empresa_id', $empresa_id)
                ->where('m.id > COALESCE(cl.ultima_lida_id, 0)', null, false)
                ->where('m.de_id !=', $user_id)
                ->where('m.sistema', 0)
                ->where('(c.criador_id = ' . $user_id . ' OR EXISTS (SELECT 1 FROM mensagem_canal_grupo cg JOIN users_groups ug ON ug.group_id = cg.group_id WHERE cg.canal_id = c.id AND ug.user_id = ' . $user_id . '))', null, false);
            if ($this->tem_coluna_msg('excluida_em')) {
                $this->db->where('m.excluida_em IS NULL', null, false);
            }
            if ($filtro_oculta !== '') {
                $this->aplicar_filtro_oculta('m', $user_id);
            }
            $canal_rows = $this->db->order_by('m.id', 'DESC')->limit(200)->get('mensagem m')->result();
            $rows = array_merge($rows, $canal_rows);
        }

        if (count($rows) > 1) {
            usort($rows, function ($a, $b) {
                return (int) $b->id - (int) $a->id;
            });
        }

        $configs = $this->configs_conversa($user_id, $empresa_id);
        $mencoes_msg = array();
        if ($depois_id !== null && $this->tem_tabela('mensagem_mencao') && $rows) {
            $mids = array();
            foreach ($rows as $r) {
                if ((int) $r->id > (int) $depois_id) {
                    $mids[] = (int) $r->id;
                }
            }
            if ($mids) {
                $mrows = $this->db->select('mensagem_id')->where('user_id', $user_id)->where_in('mensagem_id', $mids)->get('mensagem_mencao')->result();
                foreach ($mrows as $mr) {
                    $mencoes_msg[(int) $mr->mensagem_id] = true;
                }
            }
        }
        $mencoes_conv = $this->mencoes_nao_lidas_por_conversa($user_id, $empresa_id);

        $fotos = $this->fotos($rows, 'de_id');
        $sou_cliente = $this->eh_cliente($user_id, $empresa_id);
        foreach ($rows as $m) {
            $de = (int) $m->de_id;
            $grupo = (int) $m->grupo_id;
            $canal = (int) $m->canal_id;
            // Para o cliente, a equipe é o "Suporte"; conversa antiga com outro atendente não aparece.
            $como_suporte = $sou_cliente && !$grupo && !$canal && $this->ver_como_suporte($user_id, $de, $empresa_id);
            if ($como_suporte && !$this->pode_conversar($user_id, $de, $empresa_id)) {
                continue;
            }
            $foto_de = $como_suporte ? null : $fotos[$de];
            $chave = $canal ? 'c' . $canal : ($grupo ? 'g' . $grupo : 'u' . $de);
            $cfg = isset($configs[$chave]) ? $configs[$chave] : array('modo_notif' => 'todas', 'silenciada' => false);
            $autor = $como_suporte ? 'Suporte' : $this->nome($m);
            $resultado['total']++;
            $resultado['ultimo_id'] = max($resultado['ultimo_id'], (int) $m->id);
            if (!isset($resultado['remetentes'][$chave])) {
                $nome_conv = $canal ? ('# ' . $this->decodificar_emojis($m->canal_nome)) : ($grupo ? $this->decodificar_emojis($m->grupo_nome) : $autor);
                $resultado['remetentes'][$chave] = array(
                    'chave' => $chave,
                    'grupo' => (bool) ($grupo || $canal),
                    'nome' => $nome_conv,
                    'foto' => ($grupo || $canal) ? null : $foto_de,
                    'quantidade' => 0,
                    'mencoes' => isset($mencoes_conv[$chave]) ? (int) $mencoes_conv[$chave] : 0,
                    'silenciada' => $cfg['silenciada'],
                    'ultima' => (($grupo || $canal) ? trim($m->first_name) . ': ' : '') . $this->previa($m, 90),
                    'quando' => $this->quando($m->created_at),
                    'url' => site_url('mensagens/index/' . $chave),
                );
            }
            $resultado['remetentes'][$chave]['quantidade']++;

            if ($depois_id !== null && (int) $m->id > (int) $depois_id) {
                $foi_mencionado = isset($mencoes_msg[(int) $m->id]);
                $pode_avisar = !$cfg['silenciada'] && $cfg['modo_notif'] !== 'nenhuma'
                    && ($cfg['modo_notif'] !== 'mencoes' || $foi_mencionado);
                if ($pode_avisar) {
                    $titulo_notif = $canal ? ($autor . ' em #' . $this->decodificar_emojis($m->canal_nome)) : ($grupo ? $autor . ' em ' . $this->decodificar_emojis($m->grupo_nome) : $autor);
                    $resultado['novas'][] = array(
                        'id' => (int) $m->id,
                        'chave' => $chave,
                        'nome' => $titulo_notif,
                        'foto' => $foto_de,
                        'mencionado' => $foi_mencionado,
                        'texto' => ($foi_mencionado ? '@ Mencionou você: ' : '') . $this->previa($m, 140),
                        'url' => site_url('mensagens/index/' . $chave),
                    );
                }
            }
        }
        $resultado['remetentes'] = array_values($resultado['remetentes']);
        return $resultado;
    }

    // Maior id de mensagem já recebida pelo usuário (ponto de partida dos avisos na tela).
    public function ultimo_id_recebido($user_id, $empresa_id)
    {
        if (!$this->disponivel()) {
            return 0;
        }
        $row = $this->db->select_max('id')->where('empresa_id', (int) $empresa_id)->where('para_id', (int) $user_id)->get('mensagem')->row();
        $maior = $row ? (int) $row->id : 0;
        if ($this->aceita_grupos()) {
            $grupo = $this->db->select_max('m.id')
                ->join('mensagem_grupo_membro gm', 'gm.grupo_id = m.grupo_id AND gm.user_id = ' . (int) $user_id . ' AND gm.saiu_em IS NULL')
                ->where('m.empresa_id', (int) $empresa_id)
                ->where('m.de_id !=', (int) $user_id)
                ->get('mensagem m')
                ->row();
            $maior = max($maior, $grupo ? (int) $grupo->id : 0);
        }
        // Canais visíveis ao usuário (mesma regra de nao_lidas): sem isso, mensagem de canal não lida voltava como "nova" a cada página.
        if ($this->aceita_canais()) {
            $user_id = (int) $user_id;
            $canal = $this->db->select_max('m.id')
                ->join('mensagem_canal c', 'c.id = m.canal_id AND c.empresa_id = ' . (int) $empresa_id . ' AND c.excluido_em IS NULL')
                ->where('m.empresa_id', (int) $empresa_id)
                ->where('m.de_id !=', $user_id)
                ->where('(c.criador_id = ' . $user_id . ' OR EXISTS (SELECT 1 FROM mensagem_canal_grupo cg JOIN users_groups ug ON ug.group_id = cg.group_id WHERE cg.canal_id = c.id AND ug.user_id = ' . $user_id . '))', null, false)
                ->get('mensagem m')
                ->row();
            $maior = max($maior, $canal ? (int) $canal->id : 0);
        }
        return $maior;
    }

    /* ---------- Sincronização ao vivo (edições/exclusões/fixações) ---------- */

    public function mensagens_sincronizadas($chave, $user_id, $empresa_id, $ate_id, $janela_seg = 20)
    {
        $ate_id = (int) $ate_id;
        if ($ate_id <= 0 || !$this->tem_coluna_msg('editada_em')) {
            return array();
        }
        $alvo = $this->ler_chave($chave);
        if (!$alvo) {
            return array();
        }
        $desde = date('Y-m-d H:i:s', time() - (int) $janela_seg);
        $this->db->select('m.id, m.de_id, m.para_id, m.texto, m.created_at, m.lida_em, ' . ($this->aceita_grupos() ? 'm.sistema' : '0 AS sistema') . ', ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', u.first_name, u.last_name', false)
            ->from('mensagem m')
            ->join('users u', 'u.id = m.de_id', 'left')
            ->where('m.empresa_id', (int) $empresa_id)
            ->where('m.id <=', $ate_id);
        $grupo_id = null;
        if ($alvo['tipo'] === 'grupo') {
            $grupo_id = (int) $alvo['id'];
            $this->db->where('m.grupo_id', $grupo_id);
        } elseif ($alvo['tipo'] === 'canal') {
            $this->db->where('m.canal_id', (int) $alvo['id']);
        } else {
            $outro = (int) $alvo['id'];
            $this->db->group_start()
                ->group_start()->where('m.de_id', (int) $user_id)->where('m.para_id', $outro)->group_end()
                ->or_group_start()->where('m.de_id', $outro)->where('m.para_id', (int) $user_id)->group_end()
            ->group_end();
            if ($this->aceita_grupos()) {
                $this->db->where('m.grupo_id IS NULL', null, false);
            }
            if ($this->aceita_canais()) {
                $this->db->where('m.canal_id IS NULL', null, false);
            }
        }
        $this->db->group_start()
            ->where('m.editada_em >=', $desde)
            ->or_where('m.excluida_em >=', $desde)
            ->or_where('m.fixada_em >=', $desde)
        ->group_end();
        $this->aplicar_filtro_oculta('m', (int) $user_id);
        $rows = $this->db->order_by('m.id', 'ASC')->get()->result();
        return $this->enriquecer_mensagens($rows, (int) $user_id, (int) $empresa_id, $grupo_id);
    }

    /* ---------- Emojis ---------- */

    // Caracteres fora do plano básico (a maioria dos emojis) viram &#NNNNN; para caber no utf8 de 3 bytes.
    public function codificar_emojis($texto)
    {
        return preg_replace_callback('/[\x{10000}-\x{10FFFF}]/u', function ($m) {
            $codigo = unpack('N', mb_convert_encoding($m[0], 'UTF-32BE', 'UTF-8'));
            return '&#' . $codigo[1] . ';';
        }, (string) $texto);
    }

    public function decodificar_emojis($texto)
    {
        return preg_replace_callback('/&#(\d{5,7});/', function ($m) {
            $codigo = (int) $m[1];
            if ($codigo < 0x10000 || $codigo > 0x10FFFF) {
                return $m[0];
            }
            return mb_convert_encoding(pack('N', $codigo), 'UTF-8', 'UTF-32BE');
        }, (string) $texto);
    }

    /* ---------- Apoio ---------- */

    // Executa a consulta já montada no query builder (from/where) e devolve as mensagens antigas primeiro.
    private function listar_mensagens($user_id, $depois_id, $limite, $prefixo = '', $empresa_id = 0, $grupo_id = null, $antes_id = 0)
    {
        if ((int) $depois_id > 0) {
            $this->db->where($prefixo . 'id >', (int) $depois_id)->order_by($prefixo . 'id', 'ASC');
            $rows = $this->db->get()->result();
        } elseif ((int) $antes_id > 0) {
            $this->db->where($prefixo . 'id <', (int) $antes_id)->order_by($prefixo . 'id', 'DESC')->limit((int) $limite);
            $rows = array_reverse($this->db->get()->result());
        } else {
            $this->db->order_by($prefixo . 'id', 'DESC')->limit((int) $limite);
            $rows = array_reverse($this->db->get()->result());
        }
        return $this->enriquecer_mensagens($rows, $user_id, $empresa_id, $grupo_id);
    }

    private function buscar_registro_exibicao($id)
    {
        return $this->db->select('m.id, m.de_id, m.para_id, ' . ($this->aceita_grupos() ? 'm.grupo_id, m.sistema' : 'NULL AS grupo_id, 0 AS sistema') . ', m.texto, m.created_at, m.lida_em, ' . $this->campos_extras('m') . ', ' . $this->campos_anexo('m') . ', u.first_name, u.last_name', false)
            ->from('mensagem m')
            ->join('users u', 'u.id = m.de_id', 'left')
            ->where('m.id', (int) $id)
            ->get()
            ->row();
    }

    // Enriquece um lote de mensagens com resposta citada, favoritos, status de entrega/leitura e permissão de edição.
    private function enriquecer_mensagens($rows, $user_id, $empresa_id = 0, $grupo_id = null)
    {
        if (!$rows) {
            return array();
        }
        $user_id = (int) $user_id;
        $ids = array();
        $resp_ids = array();
        $minhas_grupo_ids = array();
        foreach ($rows as $r) {
            if (!$r) {
                continue;
            }
            $ids[] = (int) $r->id;
            if (!empty($r->resposta_id)) {
                $resp_ids[] = (int) $r->resposta_id;
            }
            if ($grupo_id && (int) $r->de_id === $user_id && empty($r->sistema)) {
                $minhas_grupo_ids[] = (int) $r->id;
            }
        }

        // Respostas citadas em lote
        $respostas = array();
        if ($resp_ids && $this->tem_coluna_msg('resposta_id')) {
            $rrows = $this->db->select('m.id, m.de_id, m.texto, ' . ($this->tem_coluna_msg('excluida_em') ? 'm.excluida_em' : 'NULL AS excluida_em') . ', ' . $this->campos_anexo('m') . ', u.first_name, u.last_name', false)
                ->from('mensagem m')
                ->join('users u', 'u.id = m.de_id', 'left')
                ->where_in('m.id', array_values(array_unique($resp_ids)))
                ->get()
                ->result();
            foreach ($rrows as $rr) {
                $excluida = !empty($rr->excluida_em);
                $respostas[(int) $rr->id] = array(
                    'id' => (int) $rr->id,
                    'autor' => ((int) $rr->de_id === $user_id) ? 'Você' : trim($rr->first_name . ' ' . $rr->last_name),
                    'texto' => $excluida ? 'Mensagem excluída' : $this->previa($rr, 100),
                    'anexo_tipo' => (!$excluida && !empty($rr->anexo_tipo)) ? $rr->anexo_tipo : null,
                    'excluida' => $excluida,
                );
            }
        }

        // Favoritas do usuário em lote
        $favoritas = array();
        if ($ids && $this->tem_tabela('mensagem_favorita')) {
            $frows = $this->db->select('mensagem_id')->where('user_id', $user_id)->where_in('mensagem_id', $ids)->get('mensagem_favorita')->result();
            foreach ($frows as $fr) {
                $favoritas[(int) $fr->mensagem_id] = true;
            }
        }

        // Status de entrega/leitura em lote para minhas mensagens em grupo
        $status_grupo = array();
        if ($grupo_id && $minhas_grupo_ids) {
            $status_grupo = $this->calcular_status_grupo($grupo_id, $minhas_grupo_ids, $user_id);
        }

        // Chamados, reações, enquetes e reuniões em lote
        $chamados = array();
        $chamado_ids = array();
        foreach ($rows as $r) {
            if ($r && !empty($r->chamado_id)) {
                $chamado_ids[] = (int) $r->chamado_id;
            }
        }
        if ($chamado_ids) {
            $crows = $this->db->select('id, protocolo, titulo')->where_in('id', array_values(array_unique($chamado_ids)))->get('chamado')->result();
            foreach ($crows as $cr) {
                $chamados[(int) $cr->id] = array(
                    'id' => (int) $cr->id,
                    'protocolo' => (string) $cr->protocolo,
                    'titulo' => (string) $cr->titulo,
                    'url' => site_url('chamados/details/' . (int) $cr->id),
                );
            }
        }

        $this->load->model('mensagens_recursos_model');
        $reacoes = $this->mensagens_recursos_model->reacoes_das_mensagens($ids, $user_id);
        $enquetes = $this->mensagens_recursos_model->enquetes_das_mensagens($ids, $user_id);
        $reunioes = $this->mensagens_recursos_model->reunioes_das_mensagens($ids, $user_id);

        $lista = array();
        foreach ($rows as $m) {
            if (!$m) {
                continue;
            }
            $lista[] = $this->para_exibicao($m, $user_id, $respostas, $favoritas, $status_grupo, $grupo_id, $chamados, $reacoes, $enquetes, $reunioes);
        }
        return $lista;
    }

    private function calcular_status_grupo($grupo_id, $msg_ids, $user_id)
    {
        $grupo_id = (int) $grupo_id;
        $membros = $this->db->select('user_id, ultima_lida_id, ' . ($this->tem_coluna('mensagem_grupo_membro', 'ultima_entregue_id') ? 'ultima_entregue_id' : '0 AS ultima_entregue_id'), false)
            ->where('grupo_id', $grupo_id)
            ->where('saiu_em IS NULL', null, false)
            ->where('user_id !=', (int) $user_id)
            ->get('mensagem_grupo_membro')
            ->result();
        $total_outros = count($membros);

        $leituras = array();
        if ($this->tem_tabela('mensagem_leitura')) {
            $lrows = $this->db->where_in('mensagem_id', $msg_ids)->where('user_id !=', (int) $user_id)->get('mensagem_leitura')->result();
            foreach ($lrows as $lr) {
                $leituras[(int) $lr->mensagem_id][(int) $lr->user_id] = $lr;
            }
        }

        $resultado = array();
        foreach ($msg_ids as $mid) {
            $lida_cnt = 0;
            $entregue_cnt = 0;
            foreach ($membros as $mb) {
                $uid = (int) $mb->user_id;
                $lr = isset($leituras[$mid][$uid]) ? $leituras[$mid][$uid] : null;
                $lida = ($lr && $lr->lida_em) || ((int) $mb->ultima_lida_id >= $mid);
                $entregue = $lida || ($lr && $lr->entregue_em) || ((int) $mb->ultima_entregue_id >= $mid);
                if ($lida) {
                    $lida_cnt++;
                }
                if ($entregue) {
                    $entregue_cnt++;
                }
            }
            $status = 'enviada';
            if ($total_outros > 0 && $lida_cnt >= $total_outros) {
                $status = 'lida';
            } elseif ($lida_cnt > 0 || $entregue_cnt > 0) {
                $status = 'entregue';
            }
            $resultado[$mid] = array(
                'status' => $status,
                'lida_cnt' => $lida_cnt,
                'entregue_cnt' => $entregue_cnt,
                'total_outros' => $total_outros,
                'ja_lida' => $lida_cnt > 0,
            );
        }
        return $resultado;
    }

    private function para_exibicao($m, $user_id, $respostas = array(), $favoritas = array(), $status_grupo = array(), $grupo_id = null, $chamados = array(), $reacoes = array(), $enquetes = array(), $reunioes = array())
    {
        $data = strtotime($m->created_at);
        $excluida = !empty($m->excluida_em);
        $anexo = null;
        if (!$excluida && !empty($m->anexo)) {
            $anexo = array(
                'url' => site_url('mensagens_api/arquivo/' . (int) $m->id),
                'baixar' => site_url('mensagens_api/arquivo/' . (int) $m->id) . '?baixar=1',
                'nome' => (string) $m->anexo_nome,
                'tipo' => in_array($m->anexo_tipo, array('imagem', 'video', 'audio'), true) ? $m->anexo_tipo : 'arquivo',
                'tamanho' => $this->tamanho_legivel((int) $m->anexo_tamanho),
                'extensao' => strtolower(pathinfo((string) $m->anexo_nome, PATHINFO_EXTENSION)),
            );
        }
        $minha = (int) $m->de_id === (int) $user_id;
        $sistema = !empty($m->sistema);
        $autor = isset($m->first_name) ? trim($m->first_name . ' ' . $m->last_name) : '';

        $eh_canal = !empty($m->canal_id);
        $eh_grupo = $grupo_id ? true : !empty($m->grupo_id);
        $status = null;
        $pode_editar = false;
        $lidas_grupo = 0;
        $total_grupo = 0;
        if ($minha && !$sistema && !$excluida) {
            if ($eh_canal) {
                $status = 'enviada';
                $pode_editar = !$this->mensagem_ja_foi_lida($m, $user_id) && $this->tem_coluna_msg('editada_em');
            } elseif ($eh_grupo && isset($status_grupo[(int) $m->id])) {
                $sg = $status_grupo[(int) $m->id];
                $status = $sg['status'];
                $lidas_grupo = $sg['lida_cnt'];
                $total_grupo = $sg['total_outros'];
                $pode_editar = !$sg['ja_lida'] && $this->tem_coluna_msg('editada_em');
            } elseif (!$eh_grupo) {
                if ($m->lida_em !== null) {
                    $status = 'lida';
                } elseif (!empty($m->entregue_em)) {
                    $status = 'entregue';
                } else {
                    $status = 'enviada';
                }
                $pode_editar = ($m->lida_em === null) && $this->tem_coluna_msg('editada_em');
            }
        }

        return array(
            'id' => (int) $m->id,
            'minha' => $minha,
            'de_id' => (int) $m->de_id,
            'autor' => $autor,
            'cor' => $this->cores_autor[(int) $m->de_id % count($this->cores_autor)],
            'sistema' => $sistema,
            'texto' => $excluida ? 'Mensagem excluída' : $this->decodificar_emojis($m->texto),
            'tipo_conteudo' => (!$excluida && !empty($m->tipo_conteudo)) ? (string) $m->tipo_conteudo : 'normal',
            'transcricao' => (!$excluida && !empty($m->transcricao)) ? $this->decodificar_emojis($m->transcricao) : '',
            'chamado' => (!$excluida && !empty($m->chamado_id) && isset($chamados[(int) $m->chamado_id])) ? $chamados[(int) $m->chamado_id] : null,
            'reacoes' => (!$excluida && isset($reacoes[(int) $m->id])) ? $reacoes[(int) $m->id] : array(),
            'enquete' => (!$excluida && isset($enquetes[(int) $m->id])) ? $enquetes[(int) $m->id] : null,
            'reuniao' => (!$excluida && isset($reunioes[(int) $m->id])) ? $reunioes[(int) $m->id] : null,
            'anexo' => $anexo,
            'hora' => date('H:i', $data),
            'dia' => $this->dia($data),
            'data' => date('d/m/Y H:i', $data),
            'lida' => $m->lida_em !== null,
            'entregue' => !empty($m->entregue_em),
            'status' => $status,
            'lidas_grupo' => $lidas_grupo,
            'total_grupo' => $total_grupo,
            'resposta' => (!$excluida && !empty($m->resposta_id) && isset($respostas[(int) $m->resposta_id])) ? $respostas[(int) $m->resposta_id] : null,
            'editada' => !$excluida && !empty($m->editada_em),
            'editada_em' => (!empty($m->editada_em)) ? date('d/m/Y H:i', strtotime($m->editada_em)) : null,
            'excluida' => $excluida,
            'encaminhada' => !$excluida && !empty($m->encaminhada),
            'fixada' => !$excluida && !empty($m->fixada_em),
            'favorita' => !$excluida && isset($favoritas[(int) $m->id]),
            'pode_editar' => $pode_editar,
        );
    }

    public function mensagem_do_usuario($mensagem_id, $user_id, $empresa_id)
    {
        $campos = 'id, de_id, para_id, texto, created_at, lida_em, '
            . ($this->aceita_grupos() ? 'grupo_id, sistema' : 'NULL AS grupo_id, 0 AS sistema') . ', '
            . ($this->aceita_canais() ? 'canal_id' : 'NULL AS canal_id') . ', '
            . $this->campos_extras('') . ', ' . $this->campos_anexo('');
        $m = $this->db->select($campos, false)
            ->where('id', (int) $mensagem_id)
            ->where('empresa_id', (int) $empresa_id)
            ->get('mensagem')
            ->row();
        if (!$m) {
            return null;
        }
        if ($this->tem_tabela('mensagem_oculta')) {
            $oculta = $this->db->where('mensagem_id', (int) $m->id)->where('user_id', (int) $user_id)->count_all_results('mensagem_oculta') > 0;
            if ($oculta) {
                return null;
            }
        }
        if (!empty($m->canal_id)) {
            return $this->canal_do_usuario($m->canal_id, $user_id, $empresa_id) ? $m : null;
        }
        if (!empty($m->grupo_id)) {
            return $this->grupo_do_membro($m->grupo_id, $user_id, $empresa_id) ? $m : null;
        }
        return ((int) $m->de_id === (int) $user_id || (int) $m->para_id === (int) $user_id) ? $m : null;
    }

    private function mensagem_na_conversa($mensagem_id, $user_id, $para_id, $empresa_id, $grupo_id, $canal_id = null)
    {
        $m = $this->mensagem_do_usuario($mensagem_id, $user_id, $empresa_id);
        if (!$m || !empty($m->sistema)) {
            return null;
        }
        if ($canal_id) {
            return ((int) $m->canal_id === (int) $canal_id) ? $m : null;
        }
        if ($grupo_id) {
            return ((int) $m->grupo_id === (int) $grupo_id) ? $m : null;
        }
        if (!empty($m->grupo_id) || !empty($m->canal_id)) {
            return null;
        }
        $par = ((int) $m->de_id === $user_id && (int) $m->para_id === $para_id)
            || ((int) $m->de_id === $para_id && (int) $m->para_id === $user_id);
        return $par ? $m : null;
    }

    // Texto curto para listas e avisos; sem texto, descreve o anexo.
    private function previa($m, $tamanho)
    {
        if (!empty($m->excluida_em)) {
            return 'Mensagem excluída';
        }
        $texto = $this->trecho($this->decodificar_emojis($m->texto), $tamanho);
        if ($texto !== '' || empty($m->anexo)) {
            return $texto;
        }
        $rotulos = array('imagem' => 'Foto', 'video' => 'Vídeo', 'audio' => 'Áudio');
        return isset($rotulos[$m->anexo_tipo]) ? $rotulos[$m->anexo_tipo] : 'Arquivo: ' . $this->trecho($m->anexo_nome, $tamanho - 9);
    }

    private function sql_so_diretas($alias)
    {
        $sql = $this->aceita_grupos() ? ' AND ' . $alias . '.grupo_id IS NULL' : '';
        if ($this->aceita_canais()) {
            $sql .= ' AND ' . $alias . '.canal_id IS NULL';
        }
        return $sql;
    }

    private function sql_nao_excluida($alias)
    {
        return $this->tem_coluna_msg('excluida_em') ? ' AND ' . $alias . '.excluida_em IS NULL' : '';
    }

    private function sql_nao_oculta($alias, $user_id)
    {
        if (!$this->tem_tabela('mensagem_oculta')) {
            return '';
        }
        return ' AND NOT EXISTS (SELECT 1 FROM mensagem_oculta mo WHERE mo.mensagem_id = ' . $alias . '.id AND mo.user_id = ' . (int) $user_id . ')';
    }

    private function aplicar_filtro_oculta($alias, $user_id)
    {
        if ($this->tem_tabela('mensagem_oculta')) {
            $this->db->where('NOT EXISTS (SELECT 1 FROM mensagem_oculta mo WHERE mo.mensagem_id = ' . $alias . '.id AND mo.user_id = ' . (int) $user_id . ')', null, false);
        }
    }

    private function campos_extras($alias)
    {
        $p = $alias !== '' ? $alias . '.' : '';
        $cols = array('entregue_em', 'resposta_id', 'editada_em', 'excluida_em', 'encaminhada', 'fixada_em', 'fixada_por', 'transcricao', 'chamado_id', 'tipo_conteudo');
        $partes = array();
        foreach ($cols as $c) {
            $partes[] = $this->tem_coluna_msg($c) ? ($p . $c) : ('NULL AS ' . $c);
        }
        return implode(', ', $partes);
    }

    // users.foto vem de sql/2026-11-12_users_foto.sql; sem a coluna, todos ficam com o avatar padrão.
    private function campo_foto($alias)
    {
        static $existe = null;
        if ($existe === null) {
            $existe = $this->db->field_exists('foto', 'users');
        }
        $prefixo = $alias !== '' ? $alias . '.' : '';
        return $existe ? $prefixo . 'foto' : 'NULL AS foto';
    }

    private function campos_anexo($alias)
    {
        $prefixo = $alias !== '' ? $alias . '.' : '';
        if ($this->aceita_anexos()) {
            return $prefixo . 'anexo, ' . $prefixo . 'anexo_nome, ' . $prefixo . 'anexo_tipo, ' . $prefixo . 'anexo_tamanho';
        }
        return 'NULL AS anexo, NULL AS anexo_nome, NULL AS anexo_tipo, NULL AS anexo_tamanho';
    }

    private function fotos($usuarios, $campo_id = 'id')
    {
        $this->load->library('foto_usuario');
        $padrao = base_url($this->config->item('avatar_dir') . '/m_001.png');
        $fotos = array();
        foreach ((array) $usuarios as $u) {
            $id = (int) $u->$campo_id;
            if (!isset($fotos[$id])) {
                $url = !empty($u->foto) ? $this->foto_usuario->url($u->foto) : null;
                $fotos[$id] = $url ? $url : $padrao;
            }
        }
        return $fotos;
    }

    private function nome($u)
    {
        $nome = trim($u->first_name . ' ' . $u->last_name);
        return $nome !== '' ? $nome : 'Usuário #' . (isset($u->id) ? (int) $u->id : 0);
    }

    private function trecho($texto, $tamanho)
    {
        $texto = trim(preg_replace('/\s+/u', ' ', (string) $texto));
        return mb_strlen($texto, 'UTF-8') > $tamanho ? rtrim(mb_substr($texto, 0, $tamanho - 1, 'UTF-8')) . '…' : $texto;
    }

    private function tamanho_legivel($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }
        return max(1, (int) round($bytes / 1024)) . ' KB';
    }

    private function dia($timestamp)
    {
        $data = date('Y-m-d', $timestamp);
        if ($data === date('Y-m-d')) {
            return 'Hoje';
        }
        if ($data === date('Y-m-d', strtotime('-1 day'))) {
            return 'Ontem';
        }
        return date('d/m/Y', $timestamp);
    }

    private function quando($data)
    {
        if (!$data) {
            return '';
        }
        $t = strtotime($data);
        if (date('Y-m-d', $t) === date('Y-m-d')) {
            return date('H:i', $t);
        }
        if (date('Y-m-d', $t) === date('Y-m-d', strtotime('-1 day'))) {
            return 'ontem';
        }
        return date('d/m', $t);
    }

    private function normalizar($texto)
    {
        $texto = mb_strtolower(trim((string) $texto), 'UTF-8');
        $de = array('á', 'à', 'â', 'ã', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'ô', 'õ', 'ö', 'ú', 'ù', 'û', 'ü', 'ç');
        $para = array('a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c');
        return str_replace($de, $para, $texto);
    }
}
