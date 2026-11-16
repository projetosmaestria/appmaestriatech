<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notificações de chamado para quem está vinculado a ele: solicitante (dono), responsável e desenvolvedor.
 *
 * Os pontos de alteração (diálogo, anexo, prioridade, Kanban, planejamento, edição) chamam registrar().
 * Os eventos ficam em memória e são gravados no fim da requisição, e assim tudo o que uma ação faz de uma vez
 * (escalar grava status, responsável, prazo e mensagem) vira uma única notificação por usuário.
 * Quem fez a alteração não é notificado, e eventos internos/restritos só chegam a quem vê os itens internos
 * na tela do chamado (grupos de responsável/desenvolvedor da empresa, como em Chamados_model::usuario_ve_internos).
 */
class Chamado_notificacoes_model extends CI_Model
{
    // Importância de cada tipo: o mais importante define o título e o ícone quando há vários eventos.
    private $pesos = array('novo' => 6, 'escalado' => 5, 'status' => 4, 'anexo' => 3, 'apontamento' => 3, 'dialogo' => 2, 'alteracao' => 1);

    private $icones = array(
        'novo' => array('fa-ticket', '#00a65a'),
        'escalado' => array('fa-share', '#605ca8'),
        'status' => array('fa-exchange', '#f39c12'),
        'anexo' => array('fa-paperclip', '#00c0ef'),
        'apontamento' => array('fa-clock-o', '#605ca8'),
        'dialogo' => array('fa-comment', '#3c8dbc'),
        'alteracao' => array('fa-pencil', '#777777'),
    );

    private static $pendentes = array();
    private static $gravacao_agendada = false;

    /**
     * Guarda um evento do chamado para ser notificado no fim da requisição.
     *
     * @param int         $chamado_id
     * @param string      $tipo      novo, escalado, status, anexo, dialogo ou alteracao
     * @param string      $texto     resumo do que mudou (texto puro)
     * @param int|null    $autor_id  quem fez a alteração; padrão: usuário da sessão
     * @param bool        $interno   true quando o evento é interno/restrito (só vai para quem vê os itens internos)
     * @param string|null $alvo      campo do chamado que recebeu o chamado (responsavel_id ou desenvolvedor_id), para o título "escalado para você"
     */
    public function registrar($chamado_id, $tipo, $texto, $autor_id = null, $interno = false, $alvo = null)
    {
        $chamado_id = (int) $chamado_id;
        if (!$chamado_id || !isset($this->pesos[$tipo])) {
            return;
        }
        $autor_id = $autor_id === null ? (int) $this->session->userdata('user_id') : (int) $autor_id;
        $texto = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $texto)));

        if (!isset(self::$pendentes[$chamado_id])) {
            self::$pendentes[$chamado_id] = array();
        }
        // O mesmo evento registrado por dois caminhos (ex.: diálogo de status + movimentação no Kanban) conta uma vez.
        foreach (self::$pendentes[$chamado_id] as $evento) {
            if ($evento['tipo'] === $tipo && $evento['texto'] === $texto) {
                return;
            }
        }
        self::$pendentes[$chamado_id][] = array(
            'tipo' => $tipo,
            'texto' => $texto,
            'autor_id' => $autor_id,
            'interno' => (bool) $interno,
            'alvo' => in_array($alvo, array('responsavel_id', 'desenvolvedor_id'), true) ? $alvo : null,
        );

        // redirect() encerra a requisição com exit: a gravação no shutdown cobre também esse caminho.
        if (!self::$gravacao_agendada) {
            self::$gravacao_agendada = true;
            register_shutdown_function(array($this, 'gravar_pendentes'));
        }
    }

    /**
     * Registra um diálogo gravado em chamado_dialogo. As mensagens automáticas do sistema têm texto fixo,
     * e é por ele que o tipo é reconhecido; o resto é mensagem escrita por alguém.
     */
    public function registrar_dialogo($dialogo)
    {
        $texto = trim((string) (isset($dialogo['descricao']) ? $dialogo['descricao'] : ''));
        if (empty($dialogo['chamado_id']) || $texto === '') {
            return;
        }
        $alvo = null;
        if (preg_match('/^Chamado aberto por:/u', $texto)) {
            $tipo = 'novo';
        } elseif (preg_match('/^Chamado escalado para:/u', $texto)) {
            $tipo = 'escalado';
            $alvo = 'responsavel_id';
        } elseif (preg_match('/^Desenvolvedor responsável:/u', $texto)) {
            $tipo = 'escalado';
            $alvo = 'desenvolvedor_id';
        } elseif (preg_match('/^Status do chamado alterado para/u', $texto)) {
            $tipo = 'status';
        } elseif (preg_match('/^(Serviço do chamado alterado|Prazo do chamado alterado|Previsão de resolução do chamado|Planejado para )/u', $texto)) {
            $tipo = 'alteracao';
        } else {
            $tipo = 'dialogo';
        }
        $this->registrar(
            $dialogo['chamado_id'],
            $tipo,
            $texto,
            isset($dialogo['responsavel_id']) ? $dialogo['responsavel_id'] : null,
            !empty($dialogo['interno']),
            $alvo
        );
    }

    public function registrar_anexo($anexo)
    {
        if (empty($anexo['chamado_id']) || empty($anexo['anexo'])) {
            return;
        }
        $this->registrar(
            $anexo['chamado_id'],
            'anexo',
            'Anexo: ' . $anexo['anexo'],
            isset($anexo['responsavel_id']) ? $anexo['responsavel_id'] : null,
            !empty($anexo['interno'])
        );
    }

    public function nome_status($status_id)
    {
        $status = $this->db->select('descricao')->where('id', (int) $status_id)->get('chamado_status')->row();
        return $status ? $status->descricao : '';
    }

    public function gravar_pendentes()
    {
        $pendentes = self::$pendentes;
        self::$pendentes = array();
        foreach ($pendentes as $chamado_id => $eventos) {
            try {
                $this->gravar_chamado($chamado_id, $eventos);
            } catch (Exception $e) {
                log_message('error', 'Notificação do chamado ' . $chamado_id . ' não gravada: ' . $e->getMessage());
            }
        }
    }

    private function extrair_interessados($bruto)
    {
        $ids = array();
        if (empty($bruto)) {
            return $ids;
        }
        $lista = is_array($bruto) ? $bruto : @unserialize($bruto);
        if (!is_array($lista)) {
            $json = @json_decode((string) $bruto, true);
            $lista = is_array($json) ? $json : explode(',', (string) $bruto);
        }
        foreach ($lista as $item) {
            $id = (int) $item;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return array_values(array_unique($ids));
    }

    private function gravar_chamado($chamado_id, $eventos)
    {
        $chamado = $this->db->select('c.id, c.protocolo, c.titulo, c.dono_id, c.responsavel_id, c.desenvolvedor_id, c.interessados, un.empresa_id, un.unidade AS cliente, cst.descricao AS status_desc')
            ->join('unidades un', 'un.id = c.unidade_id', 'inner')
            ->join('chamado_status cst', 'cst.id = c.status_id', 'left')
            ->where('c.id', (int) $chamado_id)
            ->get('chamado c')
            ->row();
        if (!$chamado) {
            return;
        }

        $autores = array();
        foreach ($eventos as $evento) {
            $autores[$evento['autor_id']] = true;
        }
        $destinatarios = array();
        foreach (array('dono_id', 'responsavel_id', 'desenvolvedor_id') as $campo) {
            $user_id = (int) $chamado->$campo;
            if ($user_id > 0 && !isset($autores[$user_id])) {
                $destinatarios[$user_id] = true;
            }
        }
        foreach ($this->extrair_interessados($chamado->interessados) as $int_id) {
            if ($int_id > 0 && !isset($autores[$int_id])) {
                $destinatarios[$int_id] = true;
            }
        }
        if (!$destinatarios) {
            return;
        }

        $tem_interno = false;
        foreach ($eventos as $evento) {
            $tem_interno = $tem_interno || $evento['interno'];
        }
        $equipe = $tem_interno ? $this->usuarios_equipe(array_keys($destinatarios), $chamado->empresa_id) : array();

        $agora = date('Y-m-d H:i:s');
        $linhas = array();
        $mensagens_bot = array();
        foreach (array_keys($destinatarios) as $user_id) {
            $principal = null;
            $textos = array();
            foreach ($eventos as $evento) {
                if ($evento['interno'] && !isset($equipe[$user_id])) {
                    continue;
                }
                if ($principal === null || $this->pesos[$evento['tipo']] > $this->pesos[$principal['tipo']]) {
                    $principal = $evento;
                }
                if ($evento['texto'] !== '') {
                    $textos[] = $evento['texto'];
                }
            }
            if ($principal === null) {
                continue;
            }
            $texto = implode(' · ', array_unique($textos));
            if (mb_strlen($texto, 'UTF-8') > 480) {
                $texto = rtrim(mb_substr($texto, 0, 477, 'UTF-8')) . '...';
            }
            $titulo_notif = $this->titulo($principal, $chamado, $user_id);
            $linhas[] = array(
                'user_id' => $user_id,
                'empresa_id' => (int) $chamado->empresa_id,
                'chamado_id' => (int) $chamado->id,
                'autor_id' => $principal['autor_id'] ? $principal['autor_id'] : null,
                'tipo' => $principal['tipo'],
                'titulo' => $titulo_notif,
                'texto' => $texto,
                'created_at' => $agora,
            );
            $mensagens_bot[$user_id] = array('titulo' => $titulo_notif, 'texto' => $texto);
        }
        if ($linhas) {
            $this->db->insert_batch('chamado_notificacao', $linhas);
            $this->enviar_mensagens_bot($chamado, $mensagens_bot);
        }

        // Limpeza ocasional: lidas há mais de 90 dias saem da tabela.
        if (mt_rand(1, 200) === 1) {
            $this->db->where('lida_em <', date('Y-m-d H:i:s', strtotime('-90 days')))->delete('chamado_notificacao');
        }
    }

    private function enviar_mensagens_bot($chamado, $mensagens_bot)
    {
        if (!$mensagens_bot || !file_exists(APPPATH . 'models/Mensagens_model.php') || !file_exists(APPPATH . 'models/Mensagens_recursos_model.php')) {
            return;
        }
        $this->load->model('mensagens_model');
        $this->load->model('mensagens_recursos_model');
        if (!$this->mensagens_model->disponivel()) {
            return;
        }
        $bot_id = $this->mensagens_recursos_model->obter_ou_criar_bot();
        if ($bot_id <= 0) {
            return;
        }
        foreach ($mensagens_bot as $user_id => $info) {
            $user_id = (int) $user_id;
            if ($user_id <= 0 || $user_id === $bot_id || !$this->mensagens_model->tem_acesso($user_id, (int) $chamado->empresa_id)) {
                continue;
            }
            $corpo = $info['titulo'] . "\n"
                . 'Chamado: #' . (int) $chamado->id . (!empty($chamado->titulo) ? ' — ' . trim($chamado->titulo) : '');
            if (!empty($chamado->cliente)) {
                $corpo .= "\nCliente: " . trim($chamado->cliente);
            }
            if (!empty($chamado->status_desc)) {
                $corpo .= "\nStatus: " . trim($chamado->status_desc);
            }
            if ($info['texto'] !== '') {
                $corpo .= "\nAtualização: " . $info['texto'];
            }
            $this->mensagens_model->enviar($bot_id, $user_id, (int) $chamado->empresa_id, $corpo, null, null, null, array(), false, null, null, (int) $chamado->id, 'bot');
        }
    }

    private function titulo($evento, $chamado, $user_id)
    {
        $numero = '#' . (int) $chamado->id;
        switch ($evento['tipo']) {
            case 'novo':
                return (int) $chamado->dono_id === $user_id ? 'Chamado ' . $numero . ' aberto para você' : 'Novo chamado ' . $numero . ' para você';
            case 'escalado':
                $alvo = $evento['alvo'];
                return ($alvo && (int) $chamado->$alvo === $user_id) ? 'Chamado ' . $numero . ' escalado para você' : 'Chamado ' . $numero . ' foi escalado';
            case 'status':
                return 'Status do chamado ' . $numero . ' alterado';
            case 'anexo':
                return 'Novo anexo no chamado ' . $numero;
            case 'apontamento':
                return 'Apontamento no chamado ' . $numero;
            case 'dialogo':
                return 'Nova mensagem no chamado ' . $numero;
        }
        return 'Chamado ' . $numero . ' atualizado';
    }

    // Usuários que veem diálogos/anexos internos: membros dos grupos de responsável ou desenvolvedor da empresa.
    private function usuarios_equipe($user_ids, $empresa_id)
    {
        $equipe = array();
        $user_ids = array_map('intval', (array) $user_ids);
        $pref = $this->db->get_where('admin_preferences', array('empresa_id' => (int) $empresa_id))->row();
        if (!$user_ids || !$pref) {
            return $equipe;
        }
        $grupos = array_merge((array) @unserialize($pref->grupos_responsavel_chamado), (array) @unserialize($pref->grupos_dev_chamado));
        $grupos = array_unique(array_filter(array_map('intval', $grupos)));
        if (!$grupos) {
            return $equipe;
        }
        $rows = $this->db->select('ug.user_id')
            ->join('groups g', 'g.id = ug.group_id')
            ->where_in('ug.group_id', $grupos)
            ->where('g.empresa_id', (int) $empresa_id)
            ->where_in('ug.user_id', $user_ids)
            ->get('users_groups ug')
            ->result();
        foreach ($rows as $row) {
            $equipe[(int) $row->user_id] = true;
        }
        return $equipe;
    }

    /* ---------- Leitura (sino, avisos na tela) ---------- */

    // Não lidas primeiro e, depois, as lidas dos últimos 7 dias.
    public function listar($user_id, $empresa_id, $limite = 15)
    {
        $rows = $this->db->select('n.*, c.titulo AS chamado_titulo, u.first_name AS autor_nome, u.last_name AS autor_sobrenome')
            ->join('chamado c', 'c.id = n.chamado_id', 'left')
            ->join('users u', 'u.id = n.autor_id', 'left')
            ->where('n.user_id', (int) $user_id)
            ->where('n.empresa_id', (int) $empresa_id)
            ->group_start()
                ->where('n.lida_em IS NULL', null, false)
                ->or_where('n.created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->group_end()
            ->order_by('(n.lida_em IS NULL)', 'DESC', false)
            ->order_by('n.id', 'DESC')
            ->limit((int) $limite)
            ->get('chamado_notificacao n')
            ->result();
        return $this->para_exibicao($rows);
    }

    public function contar_nao_lidas($user_id, $empresa_id)
    {
        return (int) $this->db->where('user_id', (int) $user_id)
            ->where('empresa_id', (int) $empresa_id)
            ->where('lida_em IS NULL', null, false)
            ->count_all_results('chamado_notificacao');
    }

    // Não lidas que ainda não apareceram como aviso na tela; já ficam marcadas como exibidas.
    public function novas_para_aviso($user_id, $empresa_id, $limite = 10)
    {
        $rows = $this->db->select('n.*, c.titulo AS chamado_titulo, u.first_name AS autor_nome, u.last_name AS autor_sobrenome')
            ->join('chamado c', 'c.id = n.chamado_id', 'left')
            ->join('users u', 'u.id = n.autor_id', 'left')
            ->where('n.user_id', (int) $user_id)
            ->where('n.empresa_id', (int) $empresa_id)
            ->where('n.lida_em IS NULL', null, false)
            ->where('n.exibida_em IS NULL', null, false)
            ->order_by('n.id', 'DESC')
            ->limit((int) $limite)
            ->get('chamado_notificacao n')
            ->result();
        if ($rows) {
            $ids = array();
            foreach ($rows as $row) {
                $ids[] = (int) $row->id;
            }
            $this->db->where('user_id', (int) $user_id)->where_in('id', $ids)
                ->update('chamado_notificacao', array('exibida_em' => date('Y-m-d H:i:s')));
        }
        return $this->para_exibicao($rows);
    }

    // Marca uma notificação como lida e devolve o chamado dela (ou 0 se não for do usuário).
    public function marcar_lida($id, $user_id, $empresa_id)
    {
        $notificacao = $this->db->select('id, chamado_id')
            ->where('id', (int) $id)
            ->where('user_id', (int) $user_id)
            ->where('empresa_id', (int) $empresa_id)
            ->get('chamado_notificacao')
            ->row();
        if (!$notificacao) {
            return 0;
        }
        $this->marcar_lidas_chamado($notificacao->chamado_id, $user_id);
        return (int) $notificacao->chamado_id;
    }

    // Abrir o chamado conta como leitura de todas as notificações dele.
    public function marcar_lidas_chamado($chamado_id, $user_id)
    {
        $agora = date('Y-m-d H:i:s');
        $this->db->where('user_id', (int) $user_id)
            ->where('chamado_id', (int) $chamado_id)
            ->where('lida_em IS NULL', null, false)
            ->update('chamado_notificacao', array('lida_em' => $agora, 'exibida_em' => $agora));
        return $this->db->affected_rows();
    }

    public function marcar_todas_lidas($user_id, $empresa_id)
    {
        $agora = date('Y-m-d H:i:s');
        $this->db->where('user_id', (int) $user_id)
            ->where('empresa_id', (int) $empresa_id)
            ->where('lida_em IS NULL', null, false)
            ->update('chamado_notificacao', array('lida_em' => $agora, 'exibida_em' => $agora));
        return $this->db->affected_rows();
    }

    private function para_exibicao($rows)
    {
        $itens = array();
        foreach ((array) $rows as $row) {
            $icone = isset($this->icones[$row->tipo]) ? $this->icones[$row->tipo] : $this->icones['alteracao'];
            $autor = trim($row->autor_nome . ' ' . $row->autor_sobrenome);
            $itens[] = array(
                'id' => (int) $row->id,
                'chamado_id' => (int) $row->chamado_id,
                'tipo' => $row->tipo,
                'icone' => $icone[0],
                'cor' => $icone[1],
                'titulo' => $row->titulo,
                'chamado_titulo' => (string) $row->chamado_titulo,
                'texto' => $row->texto,
                'autor' => $autor !== '' ? $autor : 'Sistema',
                'quando' => $this->tempo_relativo($row->created_at),
                'data' => date('d/m/Y H:i', strtotime($row->created_at)),
                'lida' => $row->lida_em !== null,
                'url' => site_url('notificacoes/abrir/' . (int) $row->id),
            );
        }
        return $itens;
    }

    private function tempo_relativo($data)
    {
        $segundos = max(0, time() - strtotime($data));
        if ($segundos < 60) {
            return 'agora';
        }
        if ($segundos < 3600) {
            return 'há ' . floor($segundos / 60) . ' min';
        }
        if ($segundos < 86400) {
            return 'há ' . floor($segundos / 3600) . ' h';
        }
        $dias = floor($segundos / 86400);
        return $dias == 1 ? 'ontem' : 'há ' . $dias . ' dias';
    }
}
