<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Recursos complementares do módulo de mensagens:
 * agendamento, rascunhos, marcar como não lida, etiquetas, lembretes, notas privadas,
 * transcrição de áudio, criação de chamados, status de disponibilidade, caixa de mídias e chamadas WebRTC.
 */
class Mensagens_recursos_model extends CI_Model
{
    const LIMITE_INATIVIDADE_CHAMADA_SEG = 25;
    const INTERVALO_LIMPEZA_CHAMADA_SEG = 10;
    const LIMITE_ONLINE_SEG = 120;

    private static $cache_tabelas = array();

    private function tem_tabela($tabela)
    {
        if (!isset(self::$cache_tabelas[$tabela])) {
            self::$cache_tabelas[$tabela] = $this->db->table_exists($tabela);
        }
        return self::$cache_tabelas[$tabela];
    }

    /* ---------- 1. Agendamento de mensagens ---------- */

    public function agendar($empresa_id, $user_id, $chave, $texto, $enviar_em, $resposta_id = null, $mencoes = array())
    {
        if (!$this->tem_tabela('mensagem_agendada')) {
            return 'Agendamento indisponível: aplique o script sql/2026-11-16_mensagens_recursos_v2.sql.';
        }
        $texto = trim(str_replace("\r\n", "\n", (string) $texto));
        if ($texto === '') {
            return 'Escreva a mensagem para agendar.';
        }
        if (mb_strlen($texto, 'UTF-8') > Mensagens_model::TAMANHO_MAXIMO) {
            return 'A mensagem passa de ' . Mensagens_model::TAMANHO_MAXIMO . ' caracteres.';
        }
        $ts = strtotime(str_replace('T', ' ', (string) $enviar_em));
        if (!$ts || $ts <= time() + 30) {
            return 'Escolha uma data e horário futuros para o agendamento.';
        }
        $this->load->model('mensagens_model');
        $this->db->insert('mensagem_agendada', array(
            'empresa_id' => (int) $empresa_id,
            'user_id' => (int) $user_id,
            'chave' => (string) $chave,
            'texto' => $this->mensagens_model->codificar_emojis($texto),
            'resposta_id' => $resposta_id ? (int) $resposta_id : null,
            'mencoes' => $mencoes ? json_encode(array_values(array_map('intval', (array) $mencoes))) : null,
            'enviar_em' => date('Y-m-d H:i:s', $ts),
            'status' => 'pendente',
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return (int) $this->db->insert_id();
    }

    public function listar_agendadas($empresa_id, $user_id, $chave = '')
    {
        if (!$this->tem_tabela('mensagem_agendada')) {
            return array();
        }
        $this->load->model('mensagens_model');
        $this->db->where('empresa_id', (int) $empresa_id)
            ->where('user_id', (int) $user_id)
            ->where('status', 'pendente');
        if ($chave !== '') {
            $this->db->where('chave', (string) $chave);
        }
        $rows = $this->db->order_by('enviar_em', 'ASC')->get('mensagem_agendada')->result();
        $lista = array();
        foreach ($rows as $r) {
            $lista[] = array(
                'id' => (int) $r->id,
                'chave' => $r->chave,
                'texto' => $this->mensagens_model->decodificar_emojis($r->texto),
                'enviar_em' => date('Y-m-d\TH:i', strtotime($r->enviar_em)),
                'enviar_em_br' => date('d/m/Y H:i', strtotime($r->enviar_em)),
            );
        }
        return $lista;
    }

    public function editar_agendada($id, $empresa_id, $user_id, $texto, $enviar_em)
    {
        if (!$this->tem_tabela('mensagem_agendada')) {
            return 'Agendamento indisponível.';
        }
        $row = $this->db->where('id', (int) $id)
            ->where('empresa_id', (int) $empresa_id)
            ->where('user_id', (int) $user_id)
            ->where('status', 'pendente')
            ->get('mensagem_agendada')->row();
        if (!$row) {
            return 'Agendamento não encontrado ou já enviado.';
        }
        $texto = trim(str_replace("\r\n", "\n", (string) $texto));
        if ($texto === '') {
            return 'A mensagem não pode ficar vazia.';
        }
        $ts = strtotime(str_replace('T', ' ', (string) $enviar_em));
        if (!$ts || $ts <= time() + 10) {
            return 'Escolha uma data e horário futuros.';
        }
        $this->load->model('mensagens_model');
        $this->db->where('id', (int) $row->id)->update('mensagem_agendada', array(
            'texto' => $this->mensagens_model->codificar_emojis($texto),
            'enviar_em' => date('Y-m-d H:i:s', $ts),
        ));
        return true;
    }

    public function cancelar_agendada($id, $empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_agendada')) {
            return 'Agendamento indisponível.';
        }
        $this->db->where('id', (int) $id)
            ->where('empresa_id', (int) $empresa_id)
            ->where('user_id', (int) $user_id)
            ->where('status', 'pendente')
            ->update('mensagem_agendada', array('status' => 'cancelada'));
        return $this->db->affected_rows() > 0 ? true : 'Agendamento não encontrado ou já processado.';
    }

    // Despacha mensagens agendadas cujo horário já chegou.
    public function processar_agendadas()
    {
        static $executado = false;
        if ($executado || !$this->tem_tabela('mensagem_agendada')) {
            return 0;
        }
        $executado = true;

        $agora = date('Y-m-d H:i:s');
        $pendentes = $this->db->where('status', 'pendente')
            ->where('enviar_em <=', $agora)
            ->order_by('enviar_em', 'ASC')
            ->limit(20)
            ->get('mensagem_agendada')
            ->result();
        if (!$pendentes) {
            return 0;
        }

        $this->load->model('mensagens_model');
        $enviadas = 0;
        foreach ($pendentes as $ag) {
            // Reserva atomicamente para evitar disparo duplicado em requisições concorrentes.
            $this->db->where('id', (int) $ag->id)
                ->where('status', 'pendente')
                ->update('mensagem_agendada', array('status' => 'processando'));
            if ($this->db->affected_rows() < 1) {
                continue;
            }
            $alvo = Mensagens_model::ler_chave($ag->chave);
            if (!$alvo || !$this->mensagens_model->tem_acesso($ag->user_id, $ag->empresa_id)) {
                $this->db->where('id', (int) $ag->id)->update('mensagem_agendada', array('status' => 'cancelada'));
                continue;
            }
            $texto = $this->mensagens_model->decodificar_emojis($ag->texto);
            $mencoes = $ag->mencoes ? (array) @json_decode($ag->mencoes, true) : array();
            $msg = null;
            if ($alvo['tipo'] === 'grupo') {
                if ($this->mensagens_model->grupo_do_membro($alvo['id'], $ag->user_id, $ag->empresa_id)) {
                    $msg = $this->mensagens_model->enviar($ag->user_id, 0, $ag->empresa_id, $texto, null, $alvo['id'], $ag->resposta_id, $mencoes);
                }
            } elseif ($alvo['tipo'] === 'canal') {
                if ($this->mensagens_model->pode_enviar_canal($alvo['id'], $ag->user_id, $ag->empresa_id)) {
                    $msg = $this->mensagens_model->enviar($ag->user_id, 0, $ag->empresa_id, $texto, null, null, $ag->resposta_id, $mencoes, false, $alvo['id']);
                }
            } elseif ($alvo['tipo'] === 'direta' && $this->mensagens_model->pode_conversar($ag->user_id, $alvo['id'], $ag->empresa_id)) {
                $msg = $this->mensagens_model->enviar($ag->user_id, $alvo['id'], $ag->empresa_id, $texto, null, null, $ag->resposta_id, $mencoes);
            }
            $this->db->where('id', (int) $ag->id)->update('mensagem_agendada', array(
                'status' => $msg ? 'enviada' : 'cancelada',
                'mensagem_id' => $msg ? (int) $msg['id'] : null,
            ));
            if ($msg) {
                $enviadas++;
            }
        }
        return $enviadas;
    }

    /* ---------- 2. Rascunhos automáticos ---------- */

    public function salvar_rascunho($empresa_id, $user_id, $chave, $texto)
    {
        if (!$this->tem_tabela('mensagem_rascunho') || !Mensagens_model::ler_chave($chave)) {
            return;
        }
        $texto = (string) $texto;
        if (trim($texto) === '') {
            $this->db->where('empresa_id', (int) $empresa_id)
                ->where('user_id', (int) $user_id)
                ->where('chave', (string) $chave)
                ->delete('mensagem_rascunho');
            return;
        }
        $this->load->model('mensagens_model');
        $this->db->query(
            'REPLACE INTO mensagem_rascunho (empresa_id, user_id, chave, texto, atualizado_em) VALUES (?, ?, ?, ?, ?)',
            array((int) $empresa_id, (int) $user_id, (string) $chave, $this->mensagens_model->codificar_emojis($texto), date('Y-m-d H:i:s'))
        );
    }

    public function rascunhos_usuario($empresa_id, $user_id)
    {
        $mapa = array();
        if (!$this->tem_tabela('mensagem_rascunho')) {
            return $mapa;
        }
        $this->load->model('mensagens_model');
        $rows = $this->db->where('empresa_id', (int) $empresa_id)->where('user_id', (int) $user_id)->get('mensagem_rascunho')->result();
        foreach ($rows as $r) {
            $mapa[$r->chave] = $this->mensagens_model->decodificar_emojis($r->texto);
        }
        return $mapa;
    }

    /* ---------- 3. Marcar conversa como não lida ---------- */

    public function marcar_conversa_nao_lida($empresa_id, $user_id, $chave, $nao_lida = true)
    {
        if (!$this->tem_tabela('mensagem_conversa_config') || !$this->db->field_exists('marcada_nao_lida', 'mensagem_conversa_config')) {
            return 'Recurso indisponível: aplique o script sql/2026-11-16_mensagens_recursos_v2.sql.';
        }
        if (!Mensagens_model::ler_chave($chave)) {
            return 'Conversa inválida.';
        }
        $val = $nao_lida ? 1 : 0;
        $agora = date('Y-m-d H:i:s');
        $this->db->query(
            'INSERT INTO mensagem_conversa_config (empresa_id, user_id, chave, modo_notif, silenciada_ate, marcada_nao_lida, atualizado_em) '
            . 'VALUES (?, ?, ?, \'todas\', NULL, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE marcada_nao_lida = VALUES(marcada_nao_lida), atualizado_em = VALUES(atualizado_em)',
            array((int) $empresa_id, (int) $user_id, (string) $chave, $val, $agora)
        );
        return true;
    }

    /* ---------- 4. Categorias e etiquetas de conversas ---------- */

    public function garantir_etiquetas_padrao($empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_etiqueta')) {
            return;
        }
        $qtd = $this->db->where('empresa_id', (int) $empresa_id)->where('user_id', (int) $user_id)->count_all_results('mensagem_etiqueta');
        if ($qtd > 0) {
            return;
        }
        $agora = date('Y-m-d H:i:s');
        $padroes = array(
            array('Urgente', '#dd4b39'),
            array('Cliente', '#00a65a'),
            array('Financeiro', '#f39c12'),
            array('Suporte', '#3c8dbc'),
        );
        foreach ($padroes as $p) {
            $this->db->insert('mensagem_etiqueta', array(
                'empresa_id' => (int) $empresa_id,
                'user_id' => (int) $user_id,
                'nome' => $p[0],
                'cor' => $p[1],
                'created_at' => $agora,
            ));
        }
    }

    public function listar_etiquetas($empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_etiqueta')) {
            return array();
        }
        $this->garantir_etiquetas_padrao($empresa_id, $user_id);
        $rows = $this->db->where('empresa_id', (int) $empresa_id)
            ->where('user_id', (int) $user_id)
            ->order_by('nome', 'ASC')
            ->get('mensagem_etiqueta')
            ->result();
        $lista = array();
        foreach ($rows as $r) {
            $lista[] = array('id' => (int) $r->id, 'nome' => $r->nome, 'cor' => $r->cor);
        }
        return $lista;
    }

    public function criar_etiqueta($empresa_id, $user_id, $nome, $cor)
    {
        if (!$this->tem_tabela('mensagem_etiqueta')) {
            return 'Etiquetas indisponíveis.';
        }
        $nome = trim(preg_replace('/\s+/u', ' ', (string) $nome));
        if ($nome === '') {
            return 'Informe o nome da etiqueta.';
        }
        if (mb_strlen($nome, 'UTF-8') > 40) {
            return 'O nome pode ter até 40 caracteres.';
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', (string) $cor)) {
            $cor = '#3c8dbc';
        }
        $this->db->insert('mensagem_etiqueta', array(
            'empresa_id' => (int) $empresa_id,
            'user_id' => (int) $user_id,
            'nome' => $nome,
            'cor' => $cor,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return array('id' => (int) $this->db->insert_id(), 'nome' => $nome, 'cor' => $cor);
    }

    public function excluir_etiqueta($id, $empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_etiqueta')) {
            return false;
        }
        $this->db->where('id', (int) $id)->where('empresa_id', (int) $empresa_id)->where('user_id', (int) $user_id)->delete('mensagem_etiqueta');
        if ($this->db->affected_rows() > 0 && $this->tem_tabela('mensagem_conversa_etiqueta')) {
            $this->db->where('etiqueta_id', (int) $id)->where('user_id', (int) $user_id)->delete('mensagem_conversa_etiqueta');
            return true;
        }
        return false;
    }

    public function alternar_etiqueta_conversa($empresa_id, $user_id, $chave, $etiqueta_id)
    {
        if (!$this->tem_tabela('mensagem_conversa_etiqueta') || !Mensagens_model::ler_chave($chave)) {
            return 'Etiquetas indisponíveis.';
        }
        $valida = $this->db->where('id', (int) $etiqueta_id)->where('empresa_id', (int) $empresa_id)->where('user_id', (int) $user_id)->count_all_results('mensagem_etiqueta') > 0;
        if (!$valida) {
            return 'Etiqueta não encontrada.';
        }
        $where = array('empresa_id' => (int) $empresa_id, 'user_id' => (int) $user_id, 'chave' => (string) $chave, 'etiqueta_id' => (int) $etiqueta_id);
        $existe = $this->db->where($where)->count_all_results('mensagem_conversa_etiqueta') > 0;
        if ($existe) {
            $this->db->where($where)->delete('mensagem_conversa_etiqueta');
            return false;
        }
        $this->db->insert('mensagem_conversa_etiqueta', $where);
        return true;
    }

    public function etiquetas_por_conversa($empresa_id, $user_id)
    {
        $mapa = array();
        if (!$this->tem_tabela('mensagem_conversa_etiqueta') || !$this->tem_tabela('mensagem_etiqueta')) {
            return $mapa;
        }
        $rows = $this->db->select('ce.chave, e.id, e.nome, e.cor')
            ->from('mensagem_conversa_etiqueta ce')
            ->join('mensagem_etiqueta e', 'e.id = ce.etiqueta_id')
            ->where('ce.empresa_id', (int) $empresa_id)
            ->where('ce.user_id', (int) $user_id)
            ->order_by('e.nome', 'ASC')
            ->get()
            ->result();
        foreach ($rows as $r) {
            if (!isset($mapa[$r->chave])) {
                $mapa[$r->chave] = array();
            }
            $mapa[$r->chave][] = array('id' => (int) $r->id, 'nome' => $r->nome, 'cor' => $r->cor);
        }
        return $mapa;
    }

    /* ---------- 5. Lembretes vinculados a mensagens ---------- */

    public function criar_lembrete($empresa_id, $user_id, $mensagem_id, $chave, $nota, $lembrar_em)
    {
        if (!$this->tem_tabela('mensagem_lembrete')) {
            return 'Lembretes indisponíveis.';
        }
        $ts = strtotime(str_replace('T', ' ', (string) $lembrar_em));
        if (!$ts || $ts <= time() - 60) {
            return 'Informe uma data e hora válidas para o lembrete.';
        }
        $nota = trim((string) $nota);
        if ($nota === '') {
            $nota = 'Responder mensagem';
        }
        $this->load->model('mensagens_model');
        $this->db->insert('mensagem_lembrete', array(
            'empresa_id' => (int) $empresa_id,
            'user_id' => (int) $user_id,
            'mensagem_id' => (int) $mensagem_id,
            'chave' => (string) $chave,
            'nota' => mb_substr($this->mensagens_model->codificar_emojis($nota), 0, 255, 'UTF-8'),
            'lembrar_em' => date('Y-m-d H:i:s', $ts),
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return (int) $this->db->insert_id();
    }

    public function listar_lembretes($empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_lembrete')) {
            return array();
        }
        $this->load->model('mensagens_model');
        $rows = $this->db->select('l.*, m.texto AS msg_texto')
            ->from('mensagem_lembrete l')
            ->join('mensagem m', 'm.id = l.mensagem_id', 'left')
            ->where('l.empresa_id', (int) $empresa_id)
            ->where('l.user_id', (int) $user_id)
            ->where('l.concluido_em IS NULL', null, false)
            ->order_by('l.lembrar_em', 'ASC')
            ->get()
            ->result();
        $lista = array();
        $agora = time();
        foreach ($rows as $r) {
            $lista[] = array(
                'id' => (int) $r->id,
                'mensagem_id' => (int) $r->mensagem_id,
                'chave' => $r->chave,
                'nota' => $this->mensagens_model->decodificar_emojis($r->nota),
                'msg_texto' => $this->mensagens_model->decodificar_emojis((string) $r->msg_texto),
                'lembrar_em' => date('d/m/Y H:i', strtotime($r->lembrar_em)),
                'vencido' => strtotime($r->lembrar_em) <= $agora,
            );
        }
        return $lista;
    }

    public function concluir_lembrete($id, $empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_lembrete')) {
            return false;
        }
        $this->db->where('id', (int) $id)
            ->where('empresa_id', (int) $empresa_id)
            ->where('user_id', (int) $user_id)
            ->update('mensagem_lembrete', array('concluido_em' => date('Y-m-d H:i:s')));
        return $this->db->affected_rows() > 0;
    }

    public function excluir_lembrete($id, $empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_lembrete')) {
            return false;
        }
        $this->db->where('id', (int) $id)
            ->where('empresa_id', (int) $empresa_id)
            ->where('user_id', (int) $user_id)
            ->delete('mensagem_lembrete');
        return $this->db->affected_rows() > 0;
    }

    // Devolve e marca como notificados os lembretes cujo horário já venceu.
    public function lembretes_vencidos($empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_lembrete')) {
            return array();
        }
        $agora = date('Y-m-d H:i:s');
        $rows = $this->db->where('empresa_id', (int) $empresa_id)
            ->where('user_id', (int) $user_id)
            ->where('concluido_em IS NULL', null, false)
            ->where('notificado_em IS NULL', null, false)
            ->where('lembrar_em <=', $agora)
            ->order_by('lembrar_em', 'ASC')
            ->limit(10)
            ->get('mensagem_lembrete')
            ->result();
        if (!$rows) {
            return array();
        }
        $this->load->model('mensagens_model');
        $ids = array();
        $avisos = array();
        foreach ($rows as $r) {
            $ids[] = (int) $r->id;
            $avisos[] = array(
                'id' => (int) $r->id,
                'mensagem_id' => (int) $r->mensagem_id,
                'chave' => $r->chave,
                'nota' => $this->mensagens_model->decodificar_emojis($r->nota),
                'url' => site_url('mensagens/index/' . $r->chave),
            );
        }
        $this->db->where_in('id', $ids)->update('mensagem_lembrete', array('notificado_em' => $agora));
        return $avisos;
    }

    /* ---------- 10. Notas privadas por conversa ---------- */

    public function listar_notas($empresa_id, $user_id, $chave)
    {
        if (!$this->tem_tabela('mensagem_nota')) {
            return array();
        }
        $this->load->model('mensagens_model');
        $rows = $this->db->where('empresa_id', (int) $empresa_id)
            ->where('user_id', (int) $user_id)
            ->where('chave', (string) $chave)
            ->order_by('updated_at', 'DESC')
            ->get('mensagem_nota')
            ->result();
        $lista = array();
        foreach ($rows as $r) {
            $lista[] = array(
                'id' => (int) $r->id,
                'texto' => $this->mensagens_model->decodificar_emojis($r->texto),
                'data' => date('d/m/Y H:i', strtotime($r->updated_at)),
            );
        }
        return $lista;
    }

    public function salvar_nota($empresa_id, $user_id, $chave, $texto, $nota_id = 0)
    {
        if (!$this->tem_tabela('mensagem_nota') || !Mensagens_model::ler_chave($chave)) {
            return 'Notas privadas indisponíveis.';
        }
        $texto = trim(str_replace("\r\n", "\n", (string) $texto));
        if ($texto === '') {
            return 'Escreva o conteúdo da nota privada.';
        }
        $this->load->model('mensagens_model');
        $agora = date('Y-m-d H:i:s');
        $cod = $this->mensagens_model->codificar_emojis($texto);
        if ((int) $nota_id > 0) {
            $this->db->where('id', (int) $nota_id)
                ->where('empresa_id', (int) $empresa_id)
                ->where('user_id', (int) $user_id)
                ->update('mensagem_nota', array('texto' => $cod, 'updated_at' => $agora));
            return true;
        }
        $this->db->insert('mensagem_nota', array(
            'empresa_id' => (int) $empresa_id,
            'user_id' => (int) $user_id,
            'chave' => (string) $chave,
            'texto' => $cod,
            'created_at' => $agora,
            'updated_at' => $agora,
        ));
        return (int) $this->db->insert_id();
    }

    public function excluir_nota($id, $empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_nota')) {
            return false;
        }
        $this->db->where('id', (int) $id)
            ->where('empresa_id', (int) $empresa_id)
            ->where('user_id', (int) $user_id)
            ->delete('mensagem_nota');
        return $this->db->affected_rows() > 0;
    }

    /* ---------- 11. Transcrição automática de áudio ---------- */

    public function salvar_transcricao($mensagem_id, $empresa_id, $texto)
    {
        if (!$this->db->field_exists('transcricao', 'mensagem')) {
            return false;
        }
        $this->load->model('mensagens_model');
        $texto = trim((string) $texto);
        $this->db->where('id', (int) $mensagem_id)
            ->where('empresa_id', (int) $empresa_id)
            ->update('mensagem', array('transcricao' => $texto !== '' ? $this->mensagens_model->codificar_emojis($texto) : null));
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Tenta transcrever um anexo de áudio via API externa se houver chave configurada (OPENAI_API_KEY ou GEMINI_API_KEY).
     * Sem chave configurada, retorna null e mensagem explicativa para fallback no navegador.
     */
    public function transcrever_arquivo_servidor($caminho_arquivo, $nome_original)
    {
        if (!is_file($caminho_arquivo) || !function_exists('curl_init')) {
            return array('ok' => false, 'motivo' => 'sem_suporte');
        }
        $openai_key = getenv('OPENAI_API_KEY');
        if ($openai_key) {
            $cfile = function_exists('curl_file_create')
                ? curl_file_create($caminho_arquivo, 'application/octet-stream', $nome_original)
                : '@' . $caminho_arquivo;
            $ch = curl_init('https://api.openai.com/v1/audio/transcriptions');
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_TIMEOUT => 45,
                CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $openai_key),
                CURLOPT_POSTFIELDS => array('file' => $cfile, 'model' => 'whisper-1', 'language' => 'pt'),
            ));
            $resp = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $json = @json_decode($resp, true);
            if ($code >= 200 && $code < 300 && !empty($json['text'])) {
                return array('ok' => true, 'texto' => trim($json['text']));
            }
        }
        return array('ok' => false, 'motivo' => 'sem_api');
    }

    /* ---------- 16. Transformar mensagem em chamado ---------- */

    public function opcoes_chamado($empresa_id)
    {
        $unidades = $this->db->select('id, unidade, razao_social')
            ->where('empresa_id', (int) $empresa_id)
            ->where('actived', 's')
            ->order_by('unidade', 'ASC')
            ->get('unidades')->result();
        if (!$unidades) {
            $unidades = $this->db->select('id, unidade, razao_social')
                ->where('empresa_id', (int) $empresa_id)
                ->order_by('unidade', 'ASC')
                ->get('unidades')->result();
        }
        $status = $this->db->select('id, descricao')->where('status', 1)->order_by('id', 'ASC')->get('chamado_status')->result();
        $prioridades = $this->db->select('id, descricao')->where('status', 1)->order_by('id', 'ASC')->get('chamado_prioridade')->result();

        return array(
            'unidades' => $unidades,
            'status' => $status,
            'prioridades' => $prioridades,
        );
    }

    public function criar_chamado_da_mensagem($m, $user_id, $empresa_id, $dados_form)
    {
        $titulo = trim((string) (isset($dados_form['titulo']) ? $dados_form['titulo'] : ''));
        $descricao = trim((string) (isset($dados_form['descricao']) ? $dados_form['descricao'] : ''));
        $unidade_id = (int) (isset($dados_form['unidade_id']) ? $dados_form['unidade_id'] : 0);
        $responsavel_id = (int) (isset($dados_form['responsavel_id']) ? $dados_form['responsavel_id'] : 0);
        $status_id = (int) (isset($dados_form['status_id']) ? $dados_form['status_id'] : 1);
        $prioridade_id = (int) (isset($dados_form['prioridade_id']) ? $dados_form['prioridade_id'] : 2);
        $prazo = trim((string) (isset($dados_form['prazo']) ? $dados_form['prazo'] : ''));

        if ($titulo === '') {
            return 'Informe o título do chamado.';
        }
        if ($unidade_id <= 0) {
            return 'Selecione a unidade/cliente do chamado.';
        }
        $unidade = $this->db->where('id', $unidade_id)->where('empresa_id', (int) $empresa_id)->get('unidades')->row();
        if (!$unidade) {
            return 'Unidade inválida para esta empresa.';
        }
        if ($descricao === '') {
            $descricao = 'Chamado originado a partir de mensagem interna.';
        }
        // Responsável precisa ser da empresa; status e prioridade precisam existir e estar ativos.
        if ($responsavel_id > 0) {
            $da_empresa = $this->db->join('groups g', 'g.id = ug.group_id')
                ->where('ug.user_id', $responsavel_id)
                ->where('g.empresa_id', (int) $empresa_id)
                ->count_all_results('users_groups ug') > 0;
            if (!$da_empresa) {
                return 'Responsável inválido para esta empresa.';
            }
        }
        if ($status_id > 0 && !$this->db->where('id', $status_id)->where('status', 1)->count_all_results('chamado_status')) {
            return 'Status inválido.';
        }
        if ($prioridade_id > 0 && !$this->db->where('id', $prioridade_id)->where('status', 1)->count_all_results('chamado_prioridade')) {
            return 'Prioridade inválida.';
        }

        $data_prazo = null;
        if ($prazo !== '') {
            $ts = strtotime(str_replace('T', ' ', $prazo));
            if ($ts) {
                $data_prazo = date('Y-m-d H:i:s', $ts);
            }
        }

        $agora = date('Y-m-d H:i:s');
        $insert = array(
            'unidade_id' => $unidade_id,
            'dono_id' => (int) $user_id,
            'status_id' => $status_id > 0 ? $status_id : 1,
            'responsavel_id' => $responsavel_id > 0 ? $responsavel_id : null,
            'prioridade_id' => $prioridade_id > 0 ? $prioridade_id : 2,
            'protocolo' => date('YmdHi') . '00',
            'titulo' => mb_substr($titulo, 0, 200, 'UTF-8'),
            'descricao' => $descricao,
            'data_inicio' => $agora,
            'data_prazo' => $data_prazo,
            'modified' => $agora,
            'plano_acao' => 0,
            'hash' => '',
            'atualizado_por' => (int) $user_id,
        );
        // cobrar_por_hora só existe no banco do saxwebv2.
        if ($this->db->field_exists('cobrar_por_hora', 'chamado')) {
            $insert['cobrar_por_hora'] = 0;
        }
        $this->db->insert('chamado', $insert);
        $chamado_id = (int) $this->db->insert_id();
        if (!$chamado_id) {
            return 'Não foi possível criar o chamado.';
        }

        // Copia o anexo da mensagem para o chamado se houver.
        if (!empty($m->anexo) && function_exists('get_upload_config')) {
            $origem = FCPATH . Mensagens_model::PASTA_ANEXOS . basename($m->anexo);
            if (is_file($origem)) {
                $cfg = get_upload_config();
                $pasta_ch = rtrim($cfg['upload_path'], '/\\') . '/';
                if (is_dir($pasta_ch) || @mkdir($pasta_ch, 0755, true)) {
                    $ext = strtolower(pathinfo($m->anexo, PATHINFO_EXTENSION));
                    $novo = md5(uniqid(mt_rand(), true)) . ($ext !== '' ? '.' . $ext : '');
                    if (@copy($origem, $pasta_ch . $novo)) {
                        $this->db->insert('chamado_anexo', array(
                            'chamado_id' => $chamado_id,
                            'responsavel_id' => (int) $user_id,
                            'descricao' => 'Anexo importado da mensagem: ' . (string) $m->anexo_nome,
                            'anexo' => $novo,
                            'interno' => 0,
                        ));
                    }
                }
            }
        }

        if ($this->db->field_exists('chamado_id', 'mensagem')) {
            $this->db->where('id', (int) $m->id)->update('mensagem', array('chamado_id' => $chamado_id));
        }

        if (file_exists(APPPATH . 'models/Chamado_notificacoes_model.php')) {
            $this->load->model('chamado_notificacoes_model');
            $this->chamado_notificacoes_model->registrar($chamado_id, 'novo', $titulo, (int) $user_id, false);
        }

        $row = $this->db->select('id, protocolo, titulo')->where('id', $chamado_id)->get('chamado')->row();
        return array(
            'id' => $chamado_id,
            'protocolo' => $row ? $row->protocolo : (string) $chamado_id,
            'titulo' => $row ? $row->titulo : $titulo,
            'url' => site_url('chamados/details/' . $chamado_id),
        );
    }

    /* ---------- Chamado a partir do atendimento ao cliente ---------- */

    /**
     * Confere se $user_id (equipe, nunca cliente) pode abrir chamado pela conversa direta $chave. O outro lado da
     * conversa (cliente ou colega da equipe) é o solicitante. Unidades: as vinculadas a ele; se não tiver nenhuma
     * (comum na equipe), todas as ativas da empresa. A primeira é a sugerida. Devolve string de erro ou array.
     */
    public function atendimento_para_chamado($chave, $user_id, $empresa_id)
    {
        $this->load->model('mensagens_model');
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        if (!$this->mensagens_model->pode_abrir_chamado_atendimento($user_id, $empresa_id)) {
            return 'Clientes não abrem chamado pelas Mensagens.';
        }
        $alvo = Mensagens_model::ler_chave($chave);
        if (!$alvo || $alvo['tipo'] !== 'direta') {
            return 'Abra a conversa direta com a pessoa para criar o chamado.';
        }
        $cliente_id = (int) $alvo['id'];
        if ($cliente_id === $user_id || !$this->mensagens_model->pode_conversar($user_id, $cliente_id, $empresa_id)) {
            return 'Conversa indisponível para abrir chamado.';
        }
        $campo_bot = $this->db->field_exists('is_bot', 'users') ? 'is_bot' : '0 AS is_bot';
        $u = $this->db->select('id, first_name, last_name, email, ' . $campo_bot, false)->where('id', $cliente_id)->get('users')->row();
        if (!$u || !empty($u->is_bot)) {
            return 'Não é possível abrir chamado nesta conversa.';
        }
        $eh_cliente = $this->mensagens_model->eh_cliente($cliente_id, $empresa_id);
        $linhas = $this->mensagens_model->unidades_do_cliente($cliente_id, $empresa_id);
        if (!$linhas && !$eh_cliente) {
            // Colega da equipe sem unidade vinculada: escolhe entre as unidades ativas da empresa.
            $linhas = $this->db->select('id, unidade, razao_social')->where('empresa_id', $empresa_id)->where('actived', 's')->order_by('unidade', 'ASC')->get('unidades')->result();
        }
        $unidades = array();
        $ids = array();
        foreach ($linhas as $un) {
            $unidades[] = array('id' => (int) $un->id, 'nome' => (string) $un->unidade, 'razao_social' => (string) $un->razao_social);
            $ids[] = (int) $un->id;
        }
        if (!$unidades) {
            return $eh_cliente ? 'O cliente não está vinculado a nenhuma unidade da empresa.' : 'A empresa não tem unidades ativas.';
        }
        return array(
            'chave' => 'u' . $cliente_id,
            'cliente' => array('id' => $cliente_id, 'nome' => trim($u->first_name . ' ' . $u->last_name), 'email' => (string) $u->email),
            // Colega da equipe: o solicitante pode ser ele ou quem abre (ex.: suporte abrindo para o desenvolvedor).
            'eh_cliente' => $eh_cliente,
            'unidades' => $unidades,
            'unidades_ids' => $ids,
        );
    }

    // Mensagens da conversa direta entre $user_id e o cliente (sem excluídas nem ocultas), filtradas por $ids (opcional).
    private function mensagens_do_atendimento($atendimento, $user_id, $empresa_id, $ids = null, $limite = 0)
    {
        $this->load->model('mensagens_model');
        $user_id = (int) $user_id;
        $cliente_id = (int) $atendimento['cliente']['id'];
        $this->db->select('m.id, m.de_id, m.texto, m.created_at, m.anexo, m.anexo_nome', false)
            ->from('mensagem m')
            ->where('m.empresa_id', (int) $empresa_id)
            ->group_start()
                ->group_start()->where('m.de_id', $user_id)->where('m.para_id', $cliente_id)->group_end()
                ->or_group_start()->where('m.de_id', $cliente_id)->where('m.para_id', $user_id)->group_end()
            ->group_end();
        if ($this->mensagens_model->aceita_grupos()) {
            $this->db->where('m.grupo_id IS NULL', null, false);
        }
        if ($this->db->field_exists('excluida_em', 'mensagem')) {
            $this->db->where('m.excluida_em IS NULL', null, false);
        }
        if ($this->tem_tabela('mensagem_oculta')) {
            $this->db->where('NOT EXISTS (SELECT 1 FROM mensagem_oculta mo WHERE mo.mensagem_id = m.id AND mo.user_id = ' . $user_id . ')', null, false);
        }
        if ($ids !== null) {
            if (!$ids) {
                return array();
            }
            $this->db->where_in('m.id', $ids);
        }
        $this->db->order_by('m.id', 'DESC');
        if ($limite > 0) {
            $this->db->limit((int) $limite);
        }
        return array_reverse($this->db->get()->result());
    }

    /**
     * Texto das últimas $qtd mensagens do atendimento, para ir na descrição do chamado
     * ("[19/11/2026 10:20] Ana Souza: texto"). A descrição é exibida escapada e o banco é utf8 de 3 bytes:
     * emojis de 4 bytes (guardados como &#NNNNN;) saem do texto.
     */
    public function transcricao_atendimento($atendimento, $user_id, $empresa_id, $qtd)
    {
        $qtd = min(200, max(0, (int) $qtd));
        if ($qtd <= 0) {
            return '';
        }
        $linhas = array();
        $eu = $this->db->select('first_name, last_name')->where('id', (int) $user_id)->get('users')->row();
        $nomes = array(
            (int) $user_id => $eu ? trim($eu->first_name . ' ' . $eu->last_name) : 'Suporte',
            (int) $atendimento['cliente']['id'] => $atendimento['cliente']['nome'],
        );
        foreach ($this->mensagens_do_atendimento($atendimento, $user_id, $empresa_id, null, $qtd) as $m) {
            $texto = preg_replace('/&#(\d{5,7});/', '', (string) $m->texto);
            if (preg_match('~^https://media\.giphy\.com/media/~', trim($texto))) {
                $texto = '[GIF]';
            }
            if (!empty($m->anexo)) {
                $anexo = '[anexo: ' . (string) $m->anexo_nome . ']';
                $texto = trim($texto) === '' || trim($texto) === (string) $m->anexo_nome ? $anexo : $anexo . ' ' . $texto;
            }
            $texto = trim(preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $texto));
            if ($texto === '') {
                continue;
            }
            $autor = isset($nomes[(int) $m->de_id]) ? $nomes[(int) $m->de_id] : '';
            $linhas[] = '[' . date('d/m/Y H:i', strtotime($m->created_at)) . '] ' . $autor . ': ' . $texto;
        }
        if (!$linhas) {
            return '';
        }
        return "Conversa nas Mensagens com " . $atendimento['cliente']['nome'] . ":\n" . implode("\n", $linhas);
    }

    /**
     * Copia para o chamado os anexos das mensagens escolhidas (prints, gravações, áudios, arquivos).
     * Só aceita mensagens da própria conversa de atendimento. Devolve a lista de erros (vazia se tudo deu certo).
     */
    public function importar_anexos_para_chamado($chamado_id, $ids, $atendimento, $user_id, $empresa_id)
    {
        $ids = array_slice(array_values(array_unique(array_filter(array_map('intval', (array) $ids)))), 0, 50);
        if (!$ids || !function_exists('get_upload_config')) {
            return array();
        }
        $cfg = get_upload_config();
        $pasta_ch = rtrim($cfg['upload_path'], '/\\') . '/';
        if (!is_dir($pasta_ch) && !@mkdir($pasta_ch, 0755, true)) {
            return array('Pasta de anexos do chamado indisponível.');
        }
        $erros = array();
        $tem_interno = $this->db->field_exists('interno', 'chamado_anexo');
        foreach ($this->mensagens_do_atendimento($atendimento, $user_id, $empresa_id, $ids) as $m) {
            if (empty($m->anexo)) {
                continue;
            }
            $origem = FCPATH . Mensagens_model::PASTA_ANEXOS . basename($m->anexo);
            $ext = strtolower(pathinfo($m->anexo, PATHINFO_EXTENSION));
            $novo = md5(uniqid(mt_rand(), true)) . ($ext !== '' ? '.' . $ext : '');
            if (!is_file($origem) || !@copy($origem, $pasta_ch . $novo)) {
                $erros[] = html_escape((string) $m->anexo_nome) . ': arquivo indisponível.';
                continue;
            }
            $linha = array(
                'chamado_id' => (int) $chamado_id,
                'responsavel_id' => (int) $user_id,
                'descricao' => 'Anexo das Mensagens: ' . (string) $m->anexo_nome,
                'anexo' => $novo,
            );
            if ($tem_interno) {
                $linha['interno'] = 0;
            }
            $this->db->insert('chamado_anexo', $linha);
        }
        return $erros;
    }

    /* ---------- 18. Status de disponibilidade personalizado ---------- */

    public function definir_status_usuario($empresa_id, $user_id, $status, $texto = '')
    {
        if (!$this->tem_tabela('mensagem_status_usuario')) {
            return 'Status indisponível.';
        }
        $validos = array('online', 'reuniao', 'ocupado', 'atendimento', 'ausente', 'offline');
        if (!in_array($status, $validos, true)) {
            $status = 'online';
        }
        $texto = mb_substr(trim((string) $texto), 0, 80, 'UTF-8');
        $agora = date('Y-m-d H:i:s');
        $this->db->query(
            'REPLACE INTO mensagem_status_usuario (empresa_id, user_id, status, texto, atualizado_em, visto_por_ultimo) VALUES (?, ?, ?, ?, ?, ?)',
            array((int) $empresa_id, (int) $user_id, $status, $texto !== '' ? $texto : null, $agora, $agora)
        );
        return $this->status_usuario($empresa_id, $user_id);
    }

    public function heartbeat_status($empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_status_usuario') || (int) $user_id <= 0 || (int) $empresa_id <= 0) {
            return;
        }
        $agora = date('Y-m-d H:i:s');
        $this->db->query(
            'INSERT INTO mensagem_status_usuario (empresa_id, user_id, status, texto, atualizado_em, visto_por_ultimo) '
            . 'VALUES (?, ?, \'online\', NULL, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE visto_por_ultimo = VALUES(visto_por_ultimo)',
            array((int) $empresa_id, (int) $user_id, $agora, $agora)
        );
    }

    public function status_usuario($empresa_id, $user_id)
    {
        $mapa = $this->status_usuarios($empresa_id, array((int) $user_id));
        return isset($mapa[(int) $user_id]) ? $mapa[(int) $user_id] : array('status' => 'offline', 'rotulo' => 'Offline', 'texto' => '');
    }

    public function status_usuarios($empresa_id, $user_ids = array())
    {
        $mapa = array();
        if (!$this->tem_tabela('mensagem_status_usuario')) {
            return $mapa;
        }
        $this->db->where('empresa_id', (int) $empresa_id);
        if ($user_ids) {
            $this->db->where_in('user_id', array_map('intval', (array) $user_ids));
        }
        $rows = $this->db->get('mensagem_status_usuario')->result();
        $rotulos = array(
            'online' => 'Online',
            'reuniao' => 'Em reunião',
            'ocupado' => 'Ocupado',
            'atendimento' => 'Em atendimento',
            'ausente' => 'Ausente',
            'offline' => 'Offline',
        );
        $agora = time();
        foreach ($rows as $r) {
            $ativo_recentemente = ($agora - strtotime($r->visto_por_ultimo)) <= self::LIMITE_ONLINE_SEG;
            $st = ($r->status === 'offline' || !$ativo_recentemente) ? 'offline' : $r->status;
            $mapa[(int) $r->user_id] = array(
                'status' => $st,
                'status_configurado' => $r->status,
                'rotulo' => isset($rotulos[$st]) ? $rotulos[$st] : 'Online',
                'texto' => (string) $r->texto,
            );
        }
        return $mapa;
    }

    /* ---------- 19. Caixa de mídias ---------- */

    public function midias_conversa($chave, $user_id, $empresa_id, $tipo = '')
    {
        $this->load->model('mensagens_model');
        $alvo = Mensagens_model::ler_chave($chave);
        if (!$alvo || !$this->mensagens_model->aceita_anexos()) {
            return array();
        }
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;

        $this->db->select('m.id, m.de_id, m.texto, m.created_at, m.anexo, m.anexo_nome, m.anexo_tipo, m.anexo_tamanho, ' . ($this->db->field_exists('transcricao', 'mensagem') ? 'm.transcricao' : 'NULL AS transcricao') . ', u.first_name, u.last_name', false)
            ->from('mensagem m')
            ->join('users u', 'u.id = m.de_id', 'left')
            ->where('m.empresa_id', $empresa_id)
            ->where('m.anexo IS NOT NULL', null, false);
        if ($this->db->field_exists('excluida_em', 'mensagem')) {
            $this->db->where('m.excluida_em IS NULL', null, false);
        }
        if ($this->tem_tabela('mensagem_oculta')) {
            $this->db->where('NOT EXISTS (SELECT 1 FROM mensagem_oculta mo WHERE mo.mensagem_id = m.id AND mo.user_id = ' . $user_id . ')', null, false);
        }

        if ($alvo['tipo'] === 'grupo') {
            if (!$this->mensagens_model->grupo_do_membro($alvo['id'], $user_id, $empresa_id)) {
                return array();
            }
            $this->db->where('m.grupo_id', (int) $alvo['id']);
        } elseif ($alvo['tipo'] === 'canal') {
            if (!$this->mensagens_model->canal_do_usuario($alvo['id'], $user_id, $empresa_id)) {
                return array();
            }
            $this->db->where('m.canal_id', (int) $alvo['id']);
        } else {
            $outro = (int) $alvo['id'];
            $this->db->group_start()
                ->group_start()->where('m.de_id', $user_id)->where('m.para_id', $outro)->group_end()
                ->or_group_start()->where('m.de_id', $outro)->where('m.para_id', $user_id)->group_end()
            ->group_end();
            if ($this->mensagens_model->aceita_grupos()) {
                $this->db->where('m.grupo_id IS NULL', null, false);
            }
            if ($this->db->field_exists('canal_id', 'mensagem')) {
                $this->db->where('m.canal_id IS NULL', null, false);
            }
        }

        if (in_array($tipo, array('imagem', 'video', 'audio', 'arquivo'), true)) {
            $this->db->where('m.anexo_tipo', $tipo);
        }

        $rows = $this->db->order_by('m.id', 'DESC')->limit(150)->get()->result();
        $itens = array();
        foreach ($rows as $r) {
            $bytes = (int) $r->anexo_tamanho;
            $tam = $bytes >= 1048576 ? number_format($bytes / 1048576, 1, ',', '.') . ' MB' : max(1, (int) round($bytes / 1024)) . ' KB';
            $itens[] = array(
                'id' => (int) $r->id,
                'autor' => ((int) $r->de_id === $user_id) ? 'Você' : trim($r->first_name . ' ' . $r->last_name),
                'data' => date('d/m/Y H:i', strtotime($r->created_at)),
                'url' => site_url('mensagens_api/arquivo/' . (int) $r->id),
                'baixar' => site_url('mensagens_api/arquivo/' . (int) $r->id) . '?baixar=1',
                'nome' => (string) $r->anexo_nome,
                'tipo' => in_array($r->anexo_tipo, array('imagem', 'video', 'audio'), true) ? $r->anexo_tipo : 'arquivo',
                'tamanho' => $tam,
                'transcricao' => $r->transcricao ? $this->mensagens_model->decodificar_emojis($r->transcricao) : '',
            );
        }
        return $itens;
    }

    /* ---------- 20 & 2. Ligação por voz e Compartilhamento de tela (WebRTC signaling) ---------- */

    public static function chave_chamada($chave, $user_id)
    {
        $alvo = Mensagens_model::ler_chave($chave);
        if (!$alvo) {
            return trim((string) $chave);
        }
        if ($alvo['tipo'] === 'grupo') {
            return 'g' . (int) $alvo['id'];
        }
        if ($alvo['tipo'] === 'canal') {
            return 'c' . (int) $alvo['id'];
        }
        $a = min((int) $alvo['id'], (int) $user_id);
        $b = max((int) $alvo['id'], (int) $user_id);
        return 'u' . $a . '-' . $b;
    }

    public function chamada_ativa_conversa($empresa_id, $chave, $user_id = 0)
    {
        if (!$this->tem_tabela('mensagem_chamada')) {
            return null;
        }
        $this->limpar_participantes_inativos($empresa_id);
        $chave_busca = $user_id > 0 ? self::chave_chamada($chave, $user_id) : (string) $chave;
        $row = $this->db->where('empresa_id', (int) $empresa_id)
            ->where('chave', $chave_busca)
            ->where('status', 'ativa')
            ->order_by('id', 'DESC')
            ->get('mensagem_chamada')
            ->row();
        if (!$row) {
            return null;
        }
        $participantes = $this->participantes_chamada((int) $row->id);
        if (!$participantes) {
            $this->encerrar_e_registrar_chamada((int) $row->id);
            return null;
        }
        return array(
            'id' => (int) $row->id,
            'chave' => $row->chave,
            'tipo' => $row->tipo,
            'iniciada_por' => (int) $row->iniciada_por,
            'participantes' => $participantes,
        );
    }

    /**
     * Entra na ligação ativa da conversa ou, se não houver, inicia uma nova.
     * $pode_iniciar = false (cliente): só entra numa ligação que alguém já iniciou; nunca cria.
     */
    public function iniciar_ou_entrar_chamada($empresa_id, $user_id, $chave, $tipo = 'voz', $compartilhando_tela = false, $pode_iniciar = true)
    {
        if (!$this->tem_tabela('mensagem_chamada')) {
            return 'Chamadas indisponíveis: aplique o script sql/2026-11-16_mensagens_recursos_v2.sql.';
        }
        $agora = date('Y-m-d H:i:s');
        $chave_canonica = self::chave_chamada($chave, $user_id);
        $ativa = $this->chamada_ativa_conversa($empresa_id, $chave_canonica, $user_id);
        if (!$ativa && !$pode_iniciar) {
            return 'Você não pode iniciar ligações. Quando o suporte ligar para você, é só atender.';
        }
        if (!$ativa) {
            $this->db->insert('mensagem_chamada', array(
                'empresa_id' => (int) $empresa_id,
                'chave' => $chave_canonica,
                'iniciada_por' => (int) $user_id,
                'tipo' => $tipo === 'tela' ? 'tela' : 'voz',
                'status' => 'ativa',
                'created_at' => $agora,
            ));
            $chamada_id = (int) $this->db->insert_id();
        } else {
            $chamada_id = (int) $ativa['id'];
        }

        $this->db->query(
            'REPLACE INTO mensagem_chamada_participante (chamada_id, user_id, entrou_em, saiu_em, microfone_mudo, compartilhando_tela, falando, heartbeat_em) '
            . 'VALUES (?, ?, ?, NULL, 0, ?, 0, ?)',
            array($chamada_id, (int) $user_id, $agora, $compartilhando_tela ? 1 : 0, $agora)
        );
        return $this->estado_chamada($chamada_id, $user_id, 0, $empresa_id);
    }

    public function chamadas_recebidas($empresa_id, $user_id)
    {
        $empresa_id = (int) $empresa_id;
        $user_id = (int) $user_id;
        if ($empresa_id <= 0 || $user_id <= 0 || !$this->tem_tabela('mensagem_chamada')) {
            return array();
        }
        $this->limpar_participantes_inativos($empresa_id);
        $rows = $this->db->select('c.id, c.chave, c.iniciada_por, c.tipo, c.created_at')
            ->from('mensagem_chamada c')
            ->where('c.empresa_id', $empresa_id)
            ->where('c.status', 'ativa')
            ->where('NOT EXISTS (SELECT 1 FROM mensagem_chamada_participante p WHERE p.chamada_id = c.id AND p.user_id = ' . $user_id . ' AND p.saiu_em IS NULL)', null, false)
            ->order_by('c.id', 'DESC')
            ->get()
            ->result();
        if (!$rows) {
            return array();
        }

        $this->load->model('mensagens_model');
        $lista = array();
        $tem_foto = $this->db->field_exists('foto', 'users');
        foreach ($rows as $r) {
            $conv_chave = '';
            $titulo = '';
            $eh_direta = false;
            if (preg_match('/^u(\d+)-(\d+)$/', (string) $r->chave, $m)) {
                $u1 = (int) $m[1];
                $u2 = (int) $m[2];
                if ($user_id !== $u1 && $user_id !== $u2) {
                    continue;
                }
                $outro = ($user_id === $u1) ? $u2 : $u1;
                // Cliente só recebe ligação de quem pode conversar com ele (clientes da unidade ou o seu "Suporte").
                if ($outro <= 0 || !$this->mensagens_model->pode_conversar($user_id, $outro, $empresa_id)) {
                    continue;
                }
                $conv_chave = 'u' . $outro;
                $eh_direta = true;
            } elseif (preg_match('/^g(\d+)$/', (string) $r->chave, $m)) {
                $grupo = $this->mensagens_model->grupo_do_membro((int) $m[1], $user_id, $empresa_id);
                if (!$grupo) {
                    continue;
                }
                $conv_chave = 'g' . (int) $grupo->id;
                $titulo = (string) $grupo->nome;
            } elseif (preg_match('/^c(\d+)$/', (string) $r->chave, $m)) {
                $canal = $this->mensagens_model->canal_do_usuario((int) $m[1], $user_id, $empresa_id);
                if (!$canal) {
                    continue;
                }
                $conv_chave = 'c' . (int) $canal->id;
                $titulo = '# ' . $canal->nome;
            } else {
                continue;
            }

            $participantes = $this->participantes_chamada((int) $r->id);
            if (!$participantes) {
                $this->encerrar_e_registrar_chamada((int) $r->id);
                continue;
            }

            $iniciador = $this->db->select('id, first_name, last_name' . ($tem_foto ? ', foto' : ', NULL AS foto'), false)
                ->where('id', (int) $r->iniciada_por)
                ->get('users')
                ->row();
            $iniciador_nome = $iniciador ? trim($iniciador->first_name . ' ' . $iniciador->last_name) : 'Usuário';
            $iniciador_foto = $this->url_avatar($iniciador ? $iniciador->foto : null);
            if ($eh_direta && $this->mensagens_model->ver_como_suporte($user_id, (int) $r->iniciada_por, $empresa_id)) {
                $iniciador_nome = 'Suporte';
                $iniciador_foto = $this->url_avatar(null);
            }

            $lista[] = array(
                'chamada_id' => (int) $r->id,
                'chave' => $conv_chave,
                'titulo' => $titulo !== '' ? $titulo : $iniciador_nome,
                'iniciador_id' => (int) $r->iniciada_por,
                'iniciador_nome' => $iniciador_nome,
                'iniciador_foto' => $iniciador_foto,
                'tipo' => $r->tipo,
                'eh_direta' => $eh_direta,
                'participantes' => count($participantes),
                'url' => site_url('mensagens/index/' . $conv_chave . '?atender_chamada=' . (int) $r->id),
            );
        }
        return $lista;
    }

    public function recusar_chamada($empresa_id, $user_id, $chamada_id)
    {
        if (!$this->tem_tabela('mensagem_chamada')) {
            return;
        }
        $row = $this->db->where('id', (int) $chamada_id)
            ->where('empresa_id', (int) $empresa_id)
            ->where('status', 'ativa')
            ->get('mensagem_chamada')
            ->row();
        if (!$row) {
            return;
        }
        if (preg_match('/^u(\d+)-(\d+)$/', (string) $row->chave, $m)) {
            $u1 = (int) $m[1];
            $u2 = (int) $m[2];
            if (($u1 === (int) $user_id || $u2 === (int) $user_id) && (int) $row->iniciada_por !== (int) $user_id) {
                $agora = date('Y-m-d H:i:s');
                $this->db->where('chamada_id', (int) $row->id)
                    ->where('saiu_em IS NULL', null, false)
                    ->update('mensagem_chamada_participante', array('saiu_em' => $agora, 'compartilhando_tela' => 0, 'falando' => 0));
                $this->encerrar_e_registrar_chamada((int) $row->id, 'recusada');
            }
        }
    }

    // Chamada da empresa da sessão (null se for de outra empresa ou não existir).
    private function chamada_da_empresa($chamada_id, $empresa_id)
    {
        if ((int) $chamada_id <= 0 || (int) $empresa_id <= 0) {
            return null;
        }
        return $this->db->where('id', (int) $chamada_id)->where('empresa_id', (int) $empresa_id)->get('mensagem_chamada')->row();
    }

    // O usuário está (ou esteve, com $so_ativo = false) na chamada.
    private function participa_da_chamada($chamada_id, $user_id, $so_ativo = true)
    {
        $this->db->where('chamada_id', (int) $chamada_id)->where('user_id', (int) $user_id);
        if ($so_ativo) {
            $this->db->where('saiu_em IS NULL', null, false);
        }
        return $this->db->count_all_results('mensagem_chamada_participante') > 0;
    }

    /**
     * Estado da chamada para quem participa dela (ou já participou), dentro da empresa da sessão.
     * Devolve null quando a chamada é de outra empresa ou o usuário nunca entrou nela.
     */
    public function estado_chamada($chamada_id, $user_id, $ultimo_sinal_id = 0, $empresa_id = 0)
    {
        if (!$this->tem_tabela('mensagem_chamada')) {
            return null;
        }
        $row = $this->chamada_da_empresa($chamada_id, $empresa_id);
        if (!$row || !$this->participa_da_chamada($row->id, $user_id, false)) {
            return null;
        }
        $this->limpar_participantes_inativos($empresa_id);
        $row = $this->db->where('id', (int) $row->id)->get('mensagem_chamada')->row();
        if (!$row || $row->status !== 'ativa') {
            return array('id' => (int) $chamada_id, 'status' => 'encerrada', 'participantes' => array(), 'sinais' => array(), 'ultimo_sinal_id' => (int) $ultimo_sinal_id);
        }
        $this->db->where('chamada_id', (int) $chamada_id)
            ->where('user_id', (int) $user_id)
            ->where('saiu_em IS NULL', null, false)
            ->update('mensagem_chamada_participante', array('heartbeat_em' => date('Y-m-d H:i:s')));

        // Só entrega sinais de quem está na chamada (descarta sinais forjados ou de quem já saiu).
        $sinais = $this->db->where('chamada_id', (int) $chamada_id)
            ->where('para_id', (int) $user_id)
            ->where('id >', (int) $ultimo_sinal_id)
            ->where('de_id IN (SELECT p.user_id FROM mensagem_chamada_participante p WHERE p.chamada_id = ' . (int) $chamada_id . ' AND p.saiu_em IS NULL)', null, false)
            ->order_by('id', 'ASC')
            ->get('mensagem_chamada_sinal')
            ->result();
        $lista_sinais = array();
        $maior_sinal = (int) $ultimo_sinal_id;
        foreach ($sinais as $s) {
            $maior_sinal = max($maior_sinal, (int) $s->id);
            $lista_sinais[] = array(
                'id' => (int) $s->id,
                'de_id' => (int) $s->de_id,
                'tipo' => $s->tipo,
                'dados' => @json_decode($s->dados, true),
            );
        }

        return array(
            'id' => (int) $row->id,
            'chave' => $row->chave,
            'tipo' => $row->tipo,
            'status' => $row->status,
            'participantes' => $this->participantes_chamada((int) $row->id),
            'sinais' => $lista_sinais,
            'ultimo_sinal_id' => $maior_sinal,
        );
    }

    // Só altera a própria linha de participante (o WHERE por user_id já impede mexer na de outra pessoa).
    public function atualizar_participante_chamada($chamada_id, $user_id, $mudo, $tela, $falando)
    {
        if (!$this->tem_tabela('mensagem_chamada_participante')) {
            return;
        }
        $this->db->where('chamada_id', (int) $chamada_id)
            ->where('user_id', (int) $user_id)
            ->where('saiu_em IS NULL', null, false)
            ->update('mensagem_chamada_participante', array(
                'microfone_mudo' => $mudo ? 1 : 0,
                'compartilhando_tela' => $tela ? 1 : 0,
                'falando' => $falando ? 1 : 0,
                'heartbeat_em' => date('Y-m-d H:i:s'),
            ));
    }

    /**
     * Sinal WebRTC (offer/answer/ice) entre dois participantes ativos da mesma chamada, na empresa da sessão.
     * Sem essa conferência, qualquer usuário poderia mandar uma "offer" para alguém em ligação e receber o áudio dele.
     */
    public function enviar_sinal_chamada($chamada_id, $de_id, $para_id, $tipo, $dados, $empresa_id = 0)
    {
        if (!$this->tem_tabela('mensagem_chamada_sinal')) {
            return false;
        }
        if (!in_array($tipo, array('offer', 'answer', 'ice'), true)) {
            return false;
        }
        $de_id = (int) $de_id;
        $para_id = (int) $para_id;
        $chamada = $this->chamada_da_empresa($chamada_id, $empresa_id);
        if (!$chamada || $chamada->status !== 'ativa' || $de_id === $para_id
            || !$this->participa_da_chamada($chamada->id, $de_id) || !$this->participa_da_chamada($chamada->id, $para_id)) {
            return false;
        }
        $dados = is_string($dados) ? $dados : json_encode($dados);
        if (strlen($dados) > 65536) {
            return false; // uma descrição SDP real fica bem abaixo disso
        }
        $this->db->insert('mensagem_chamada_sinal', array(
            'chamada_id' => (int) $chamada_id,
            'de_id' => (int) $de_id,
            'para_id' => (int) $para_id,
            'tipo' => $tipo,
            'dados' => $dados,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return true;
    }

    // Sai da chamada. Só quem participa (ou participou) dela, na empresa da sessão, pode sair ou encerrar a ligação direta.
    public function sair_chamada($chamada_id, $user_id, $empresa_id = 0)
    {
        if (!$this->tem_tabela('mensagem_chamada_participante')) {
            return;
        }
        $chamada_id = (int) $chamada_id;
        $user_id = (int) $user_id;
        $chamada_empresa = $this->chamada_da_empresa($chamada_id, $empresa_id);
        if (!$chamada_empresa || !$this->participa_da_chamada($chamada_id, $user_id, false)) {
            return;
        }
        $agora = date('Y-m-d H:i:s');
        $this->db->where('chamada_id', $chamada_id)
            ->where('user_id', $user_id)
            ->update('mensagem_chamada_participante', array('saiu_em' => $agora, 'compartilhando_tela' => 0, 'falando' => 0));

        $chamada = $this->db->select('id, chave')->where('id', $chamada_id)->get('mensagem_chamada')->row();
        $eh_direta = $chamada && preg_match('/^u\d+-\d+$/', (string) $chamada->chave);
        $restantes = $this->db->where('chamada_id', $chamada_id)->where('saiu_em IS NULL', null, false)->count_all_results('mensagem_chamada_participante');

        if ($eh_direta || $restantes < 1) {
            if ($eh_direta) {
                $this->db->where('chamada_id', $chamada_id)
                    ->where('saiu_em IS NULL', null, false)
                    ->update('mensagem_chamada_participante', array('saiu_em' => $agora, 'compartilhando_tela' => 0, 'falando' => 0));
            }
            $this->encerrar_e_registrar_chamada($chamada_id);
        }
    }

    private function formatar_duracao_chamada($segundos)
    {
        $segundos = max(1, (int) $segundos);
        if ($segundos < 60) {
            return $segundos . ' segundo' . ($segundos === 1 ? '' : 's');
        }
        $minutos = (int) floor($segundos / 60);
        $resto = $segundos % 60;
        $txt = $minutos . ' minuto' . ($minutos === 1 ? '' : 's');
        if ($resto > 0 && $minutos < 10) {
            $txt .= ' e ' . $resto . ' segundo' . ($resto === 1 ? '' : 's');
        }
        return $txt;
    }

    private function nome_usuario_chamada($user_id)
    {
        $u = $this->db->select('first_name, last_name')->where('id', (int) $user_id)->get('users')->row();
        return $u ? trim($u->first_name . ' ' . $u->last_name) : 'Usuário';
    }

    private function encerrar_e_registrar_chamada($chamada_id, $motivo = 'encerrada')
    {
        $chamada_id = (int) $chamada_id;
        $agora = date('Y-m-d H:i:s');
        $this->db->where('id', $chamada_id)
            ->where('status', 'ativa')
            ->update('mensagem_chamada', array('status' => 'encerrada', 'encerrada_em' => $agora));
        if ($this->db->affected_rows() < 1) {
            return;
        }

        $chamada = $this->db->where('id', $chamada_id)->get('mensagem_chamada')->row();
        if (!$chamada) {
            return;
        }

        $participantes = $this->db->select('user_id, entrou_em')
            ->where('chamada_id', $chamada_id)
            ->order_by('entrou_em', 'ASC')
            ->get('mensagem_chamada_participante')
            ->result();
        $unicos = array();
        $inicio_conectado = null;
        foreach ($participantes as $p) {
            $uid = (int) $p->user_id;
            if (!isset($unicos[$uid])) {
                $unicos[$uid] = true;
                if (count($unicos) === 2) {
                    $inicio_conectado = $p->entrou_em;
                }
            }
        }

        $conectada = count($unicos) >= 2 && $inicio_conectado !== null;
        $duracao = $conectada ? $this->formatar_duracao_chamada(max(1, strtotime($agora) - strtotime($inicio_conectado))) : '';

        $this->load->model('mensagens_model');
        $iniciador_id = (int) $chamada->iniciada_por;
        $iniciador_nome = $this->nome_usuario_chamada($iniciador_id);

        $registro = array(
            'empresa_id' => (int) $chamada->empresa_id,
            'de_id' => $iniciador_id,
            'para_id' => 0,
            'sistema' => 1,
            'created_at' => $agora,
        );

        if (preg_match('/^u(\d+)-(\d+)$/', (string) $chamada->chave, $m)) {
            $u1 = (int) $m[1];
            $u2 = (int) $m[2];
            $dest_id = ($iniciador_id === $u1) ? $u2 : $u1;
            $dest_nome = $this->nome_usuario_chamada($dest_id);
            if ($conectada) {
                $texto = '📞 Ligação feita para ' . $dest_nome . ' que durou ' . $duracao . '.';
            } elseif ($motivo === 'recusada') {
                $texto = '📞 Ligação para ' . $dest_nome . ' recusada.';
            } else {
                $texto = '📞 Ligação perdida para ' . $dest_nome . '.';
            }
            $registro['para_id'] = $dest_id;
            $registro['lida_em'] = $agora;
        } elseif (preg_match('/^g(\d+)$/', (string) $chamada->chave, $m)) {
            $registro['grupo_id'] = (int) $m[1];
            $texto = $conectada
                ? ('📞 Ligação iniciada por ' . $iniciador_nome . ' durou ' . $duracao . ' (' . count($unicos) . ' participantes).')
                : ('📞 Ligação no grupo iniciada por ' . $iniciador_nome . ' foi encerrada sem outros participantes.');
        } elseif (preg_match('/^c(\d+)$/', (string) $chamada->chave, $m)) {
            $registro['canal_id'] = (int) $m[1];
            $texto = $conectada
                ? ('📞 Ligação no canal iniciada por ' . $iniciador_nome . ' durou ' . $duracao . ' (' . count($unicos) . ' participantes).')
                : ('📞 Ligação no canal iniciada por ' . $iniciador_nome . ' foi encerrada sem outros participantes.');
        } else {
            return;
        }

        $registro['texto'] = $this->mensagens_model->codificar_emojis($texto);
        $this->db->insert('mensagem', $registro);
    }

    private function url_avatar($foto)
    {
        if (!empty($foto) && is_file(FCPATH . 'upload/avatar/' . $foto)) {
            return base_url('upload/avatar/' . $foto);
        }
        return base_url('upload/avatar/m_001.png');
    }

    private function participantes_chamada($chamada_id)
    {
        $tem_foto = $this->db->field_exists('foto', 'users');
        $rows = $this->db->select('p.user_id, p.microfone_mudo, p.compartilhando_tela, p.falando, u.first_name, u.last_name' . ($tem_foto ? ', u.foto' : ', NULL AS foto'), false)
            ->from('mensagem_chamada_participante p')
            ->join('users u', 'u.id = p.user_id')
            ->where('p.chamada_id', (int) $chamada_id)
            ->where('p.saiu_em IS NULL', null, false)
            ->order_by('p.entrou_em', 'ASC')
            ->get()
            ->result();
        $lista = array();
        foreach ($rows as $r) {
            $lista[] = array(
                'id' => (int) $r->user_id,
                'user_id' => (int) $r->user_id,
                'nome' => trim($r->first_name . ' ' . $r->last_name),
                'foto' => $this->url_avatar($r->foto),
                'mudo' => !empty($r->microfone_mudo),
                'tela' => !empty($r->compartilhando_tela),
                'falando' => !empty($r->falando),
            );
        }
        return $lista;
    }

    /**
     * Tira da chamada quem parou de mandar sinal de vida e encerra chamadas sem ninguém.
     * Só olha as chamadas ativas da empresa e roda no máximo a cada INTERVALO_LIMPEZA_CHAMADA_SEG por empresa
     * (o cabeçalho consulta as chamadas a cada poucos segundos em todas as telas).
     */
    private function limpar_participantes_inativos($empresa_id)
    {
        $empresa_id = (int) $empresa_id;
        if ($empresa_id <= 0) {
            return;
        }
        static $feitas = array();
        if (isset($feitas[$empresa_id])) {
            return;
        }
        $feitas[$empresa_id] = true;
        // Marca de tempo na pasta temporária do sistema (application/cache não existe em todas as instalações);
        // o md5 do caminho separa instalações diferentes no mesmo servidor. Sem conseguir gravar, apenas limpa sempre.
        $marca = rtrim(sys_get_temp_dir(), '/\\') . '/saxweb_' . md5(FCPATH) . '_limpeza_chamadas_' . $empresa_id;
        if (is_file($marca) && time() - (int) @filemtime($marca) < self::INTERVALO_LIMPEZA_CHAMADA_SEG) {
            return;
        }
        @touch($marca);

        $agora = date('Y-m-d H:i:s');
        $limite = date('Y-m-d H:i:s', time() - self::LIMITE_INATIVIDADE_CHAMADA_SEG);
        $this->db->query(
            'UPDATE mensagem_chamada_participante p JOIN mensagem_chamada c ON c.id = p.chamada_id'
            . ' SET p.saiu_em = ?, p.compartilhando_tela = 0, p.falando = 0'
            . ' WHERE c.empresa_id = ? AND c.status = \'ativa\' AND p.saiu_em IS NULL AND p.heartbeat_em < ?',
            array($agora, $empresa_id, $limite)
        );
        $sem_ativos = $this->db->query(
            'SELECT c.id FROM mensagem_chamada c '
            . 'WHERE c.empresa_id = ? AND c.status = \'ativa\' AND NOT EXISTS (SELECT 1 FROM mensagem_chamada_participante p WHERE p.chamada_id = c.id AND p.saiu_em IS NULL)',
            array($empresa_id)
        )->result();
        foreach ($sem_ativos as $c) {
            $this->encerrar_e_registrar_chamada((int) $c->id);
        }
    }

    /* ---------- 2. Reações às mensagens ---------- */

    public function alternar_reacao($mensagem_id, $user_id, $empresa_id, $emoji)
    {
        if (!$this->tem_tabela('mensagem_reacao')) {
            return 'Reações indisponíveis: aplique o script sql/2026-11-16_mensagens_recursos_v3.sql.';
        }
        $emoji = trim((string) $emoji);
        if ($emoji === '' || mb_strlen($emoji, 'UTF-8') > 8) {
            return 'Emoji inválido.';
        }
        $this->load->model('mensagens_model');
        $m = $this->mensagens_model->mensagem_do_usuario((int) $mensagem_id, (int) $user_id, (int) $empresa_id);
        if (!$m || !empty($m->excluida_em) || !empty($m->sistema)) {
            return 'Mensagem indisponível para reação.';
        }

        $emoji_codificado = $this->mensagens_model->codificar_emojis($emoji);
        $existe = $this->db->where('mensagem_id', (int) $m->id)
            ->where('user_id', (int) $user_id)
            ->where('emoji', $emoji_codificado)
            ->get('mensagem_reacao')
            ->row();
        if ($existe) {
            $this->db->where('id', (int) $existe->id)->delete('mensagem_reacao');
        } else {
            $this->db->insert('mensagem_reacao', array(
                'mensagem_id' => (int) $m->id,
                'user_id' => (int) $user_id,
                'emoji' => $emoji_codificado,
                'created_at' => date('Y-m-d H:i:s'),
            ));
        }
        $mapa = $this->reacoes_das_mensagens(array((int) $m->id), (int) $user_id);
        return isset($mapa[(int) $m->id]) ? $mapa[(int) $m->id] : array();
    }

    public function reacoes_das_mensagens($msg_ids, $user_id)
    {
        $mapa = array();
        if (!$msg_ids || !$this->tem_tabela('mensagem_reacao')) {
            return $mapa;
        }
        $this->load->model('mensagens_model');
        $rows = $this->db->select('r.mensagem_id, r.user_id, r.emoji, u.first_name, u.last_name')
            ->from('mensagem_reacao r')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->where_in('r.mensagem_id', array_values(array_unique(array_map('intval', (array) $msg_ids))))
            ->order_by('r.id', 'ASC')
            ->get()
            ->result();
        $agrupado = array();
        foreach ($rows as $r) {
            $mid = (int) $r->mensagem_id;
            $em = $this->mensagens_model->decodificar_emojis((string) $r->emoji);
            if (!isset($agrupado[$mid][$em])) {
                $agrupado[$mid][$em] = array(
                    'emoji' => $em,
                    'quantidade' => 0,
                    'eu' => false,
                    'usuarios' => array(),
                );
            }
            $agrupado[$mid][$em]['quantidade']++;
            if ((int) $r->user_id === (int) $user_id) {
                $agrupado[$mid][$em]['eu'] = true;
            }
            $nome = trim($r->first_name . ' ' . $r->last_name);
            if ($nome !== '') {
                $agrupado[$mid][$em]['usuarios'][] = $nome;
            }
        }
        foreach ($agrupado as $mid => $emojis) {
            $mapa[$mid] = array_values($emojis);
        }
        return $mapa;
    }

    public function reacoes_conversa($chave, $user_id, $empresa_id, $ate_id = 0)
    {
        if (!$this->tem_tabela('mensagem_reacao') || (int) $ate_id <= 0) {
            return array();
        }
        $this->load->model('mensagens_model');
        $alvo = Mensagens_model::ler_chave($chave);
        if (!$alvo) {
            return array();
        }
        $this->db->select('m.id')->from('mensagem m')
            ->where('m.empresa_id', (int) $empresa_id)
            ->where('m.id <=', (int) $ate_id);
        if ($alvo['tipo'] === 'grupo') {
            $this->db->where('m.grupo_id', (int) $alvo['id']);
        } elseif ($alvo['tipo'] === 'canal') {
            $this->db->where('m.canal_id', (int) $alvo['id']);
        } else {
            $outro = (int) $alvo['id'];
            $this->db->group_start()
                ->group_start()->where('m.de_id', (int) $user_id)->where('m.para_id', $outro)->group_end()
                ->or_group_start()->where('m.de_id', $outro)->where('m.para_id', (int) $user_id)->group_end()
            ->group_end();
            if ($this->mensagens_model->aceita_grupos()) {
                $this->db->where('m.grupo_id IS NULL', null, false);
            }
            if ($this->mensagens_model->aceita_canais()) {
                $this->db->where('m.canal_id IS NULL', null, false);
            }
        }
        $this->db->where('EXISTS (SELECT 1 FROM mensagem_reacao mr WHERE mr.mensagem_id = m.id)', null, false);
        $ids_rows = $this->db->order_by('m.id', 'DESC')->limit(150)->get()->result();
        if (!$ids_rows) {
            return array();
        }
        $ids = array();
        foreach ($ids_rows as $ir) {
            $ids[] = (int) $ir->id;
        }
        return $this->reacoes_das_mensagens($ids, (int) $user_id);
    }

    public function ultimo_reacao_id($empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_reacao')) {
            return 0;
        }
        $row = $this->db->select('MAX(r.id) AS max_id', false)
            ->from('mensagem_reacao r')
            ->join('mensagem m', 'm.id = r.mensagem_id')
            ->where('m.empresa_id', (int) $empresa_id)
            ->where('m.de_id', (int) $user_id)
            ->where('r.user_id !=', (int) $user_id)
            ->get()
            ->row();
        return $row ? (int) $row->max_id : 0;
    }

    public function reacoes_para_notificar($empresa_id, $user_id, $depois_id)
    {
        $depois_id = (int) $depois_id;
        if (!$this->tem_tabela('mensagem_reacao') || $depois_id < 0) {
            return array();
        }
        $this->load->model('mensagens_model');
        $campos_alvo = 'm.id AS mensagem_id, m.texto, m.anexo_nome, m.de_id, m.para_id'
            . ($this->mensagens_model->aceita_grupos() ? ', m.grupo_id' : ', NULL AS grupo_id')
            . ($this->mensagens_model->aceita_canais() ? ', m.canal_id' : ', NULL AS canal_id');
        $rows = $this->db->select('r.id, r.emoji, r.user_id, r.created_at, u.first_name, u.last_name, ' . $campos_alvo, false)
            ->from('mensagem_reacao r')
            ->join('mensagem m', 'm.id = r.mensagem_id')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->where('m.empresa_id', (int) $empresa_id)
            ->where('m.de_id', (int) $user_id)
            ->where('r.user_id !=', (int) $user_id)
            ->where('r.id >', $depois_id)
            ->order_by('r.id', 'ASC')
            ->limit(20)
            ->get()
            ->result();
        $lista = array();
        foreach ($rows as $r) {
            $chave = !empty($r->canal_id) ? ('c' . (int) $r->canal_id) : (!empty($r->grupo_id) ? ('g' . (int) $r->grupo_id) : ('u' . (int) $r->user_id));
            $autor = trim($r->first_name . ' ' . $r->last_name);
            if ($autor === '') {
                $autor = 'Usuário';
            }
            $emoji = $this->mensagens_model->decodificar_emojis((string) $r->emoji);
            $previa = trim((string) $this->mensagens_model->decodificar_emojis((string) $r->texto));
            if ($previa === '' && !empty($r->anexo_nome)) {
                $previa = (string) $r->anexo_nome;
            }
            if (mb_strlen($previa, 'UTF-8') > 60) {
                $previa = mb_substr($previa, 0, 60, 'UTF-8') . '…';
            }
            $lista[] = array(
                'id' => (int) $r->id,
                'mensagem_id' => (int) $r->mensagem_id,
                'chave' => $chave,
                'autor' => $autor,
                'emoji' => $emoji,
                'texto' => $autor . ' reagiu sua mensagem com ' . $emoji,
                'previa' => $previa,
                'url' => site_url('mensagens/index/' . $chave),
            );
        }
        return $lista;
    }

    public function ultimas_reacoes_por_conversa($empresa_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_reacao')) {
            return array();
        }
        $this->load->model('mensagens_model');
        $user_id = (int) $user_id;
        $empresa_id = (int) $empresa_id;
        $sel_grupo = $this->mensagens_model->aceita_grupos() ? 'm.grupo_id' : 'NULL AS grupo_id';
        $sel_canal = $this->mensagens_model->aceita_canais() ? 'm.canal_id' : 'NULL AS canal_id';
        $rows = $this->db->select('r.id, r.user_id AS reator_id, r.emoji, r.created_at, m.de_id AS msg_de_id, m.para_id AS msg_para_id, ' . $sel_grupo . ', ' . $sel_canal . ', u.first_name, u.last_name', false)
            ->from('mensagem_reacao r')
            ->join('mensagem m', 'm.id = r.mensagem_id')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->where('m.empresa_id', $empresa_id)
            ->order_by('r.id', 'DESC')
            ->limit(150)
            ->get()
            ->result();
        $mapa = array();
        foreach ($rows as $r) {
            if (!empty($r->canal_id)) {
                $chave = 'c' . (int) $r->canal_id;
            } elseif (!empty($r->grupo_id)) {
                $chave = 'g' . (int) $r->grupo_id;
            } elseif ((int) $r->msg_de_id === $user_id || (int) $r->msg_para_id === $user_id) {
                $outro = ((int) $r->msg_de_id === $user_id) ? (int) $r->msg_para_id : (int) $r->msg_de_id;
                $chave = 'u' . $outro;
            } else {
                continue;
            }
            if (isset($mapa[$chave])) {
                continue;
            }
            $emoji = $this->mensagens_model->decodificar_emojis((string) $r->emoji);
            $reator_id = (int) $r->reator_id;
            $reator_nome = trim($r->first_name);
            if ($reator_nome === '') {
                $reator_nome = 'Usuário';
            }
            if ($reator_id === $user_id) {
                $texto = 'Você reagiu com ' . $emoji;
            } elseif ((int) $r->msg_de_id === $user_id) {
                $texto = $reator_nome . ' reagiu sua mensagem com ' . $emoji;
            } else {
                $texto = $reator_nome . ' reagiu com ' . $emoji;
            }
            $mapa[$chave] = array(
                'created_at' => $r->created_at,
                'texto' => $texto,
            );
        }
        return $mapa;
    }

    /* ---------- 3. Enquetes nos canais ---------- */

    public function criar_enquete($canal, $user_id, $empresa_id, $pergunta, $opcoes, $anonima = false, $multipla = false, $encerrar_em = '')
    {
        if (!$this->tem_tabela('mensagem_enquete') || !$this->tem_tabela('mensagem_enquete_opcao')) {
            return 'Enquetes indisponíveis: aplique o script sql/2026-11-16_mensagens_recursos_v3.sql.';
        }
        $pergunta = mb_substr(trim((string) $pergunta), 0, 255, 'UTF-8');
        if ($pergunta === '') {
            return 'Informe a pergunta da enquete.';
        }
        $limpas = array();
        foreach ((array) $opcoes as $op) {
            $txt = mb_substr(trim((string) $op), 0, 150, 'UTF-8');
            if ($txt !== '') {
                $limpas[] = $txt;
            }
        }
        $limpas = array_values(array_unique($limpas));
        if (count($limpas) < 2) {
            return 'Adicione pelo menos 2 alternativas diferentes.';
        }
        if (count($limpas) > 10) {
            $limpas = array_slice($limpas, 0, 10);
        }
        $data_enc = null;
        if (trim((string) $encerrar_em) !== '') {
            $ts = strtotime(str_replace('T', ' ', trim((string) $encerrar_em)));
            if (!$ts) {
                return 'Prazo da enquete inválido.';
            }
            if ($ts <= time()) {
                return 'O prazo de votação precisa ser no futuro.';
            }
            $data_enc = date('Y-m-d H:i:s', $ts);
        }

        $this->load->model('mensagens_model');
        $agora = date('Y-m-d H:i:s');
        $this->db->trans_start();
        $msg_data = array(
            'empresa_id' => (int) $empresa_id,
            'de_id' => (int) $user_id,
            'para_id' => 0,
            'canal_id' => (int) $canal->id,
            'texto' => $this->mensagens_model->codificar_emojis('Enquete: ' . $pergunta),
            'created_at' => $agora,
        );
        if ($this->db->field_exists('tipo_conteudo', 'mensagem')) {
            $msg_data['tipo_conteudo'] = 'enquete';
        }
        $this->db->insert('mensagem', $msg_data);
        $mensagem_id = (int) $this->db->insert_id();
        $this->db->insert('mensagem_enquete', array(
            'empresa_id' => (int) $empresa_id,
            'canal_id' => (int) $canal->id,
            'mensagem_id' => $mensagem_id,
            'criador_id' => (int) $user_id,
            'pergunta' => $pergunta,
            'anonima' => $anonima ? 1 : 0,
            'multipla' => $multipla ? 1 : 0,
            'encerrar_em' => $data_enc,
            'created_at' => $agora,
        ));
        $enquete_id = (int) $this->db->insert_id();
        foreach ($limpas as $idx => $op_txt) {
            $this->db->insert('mensagem_enquete_opcao', array(
                'enquete_id' => $enquete_id,
                'texto' => $op_txt,
                'ordem' => $idx + 1,
            ));
        }
        $this->db->trans_complete();
        if (!$this->db->trans_status() || !$mensagem_id) {
            return 'Não foi possível criar a enquete.';
        }
        $this->mensagens_model->marcar_lido_canal((int) $user_id, (int) $canal->id);
        return $this->mensagens_model->buscar_mensagem_exibicao($mensagem_id, (int) $user_id, (int) $empresa_id);
    }

    public function votar_enquete($enquete_id, $opcao_id, $user_id, $empresa_id)
    {
        if (!$this->tem_tabela('mensagem_enquete') || !$this->tem_tabela('mensagem_enquete_voto')) {
            return 'Enquetes indisponíveis.';
        }
        $eq = $this->db->where('id', (int) $enquete_id)->where('empresa_id', (int) $empresa_id)->get('mensagem_enquete')->row();
        if (!$eq) {
            return 'Enquete não encontrada.';
        }
        $this->load->model('mensagens_model');
        if (!$this->mensagens_model->canal_do_usuario((int) $eq->canal_id, (int) $user_id, (int) $empresa_id)) {
            return 'Você não tem acesso a este canal.';
        }
        if ($eq->encerrar_em && strtotime($eq->encerrar_em) <= time()) {
            return 'O prazo para votação desta enquete já foi encerrado.';
        }
        $op = $this->db->where('id', (int) $opcao_id)->where('enquete_id', (int) $eq->id)->get('mensagem_enquete_opcao')->row();
        if (!$op) {
            return 'Opção inválida.';
        }

        $ja_votou = $this->db->where('enquete_id', (int) $eq->id)
            ->where('opcao_id', (int) $op->id)
            ->where('user_id', (int) $user_id)
            ->count_all_results('mensagem_enquete_voto') > 0;
        if ($ja_votou) {
            $this->db->where('enquete_id', (int) $eq->id)
                ->where('opcao_id', (int) $op->id)
                ->where('user_id', (int) $user_id)
                ->delete('mensagem_enquete_voto');
        } else {
            if (empty($eq->multipla)) {
                $this->db->where('enquete_id', (int) $eq->id)->where('user_id', (int) $user_id)->delete('mensagem_enquete_voto');
            }
            $this->db->insert('mensagem_enquete_voto', array(
                'enquete_id' => (int) $eq->id,
                'opcao_id' => (int) $op->id,
                'user_id' => (int) $user_id,
                'created_at' => date('Y-m-d H:i:s'),
            ));
        }

        $mapa = $this->enquetes_das_mensagens(array((int) $eq->mensagem_id), (int) $user_id);
        return isset($mapa[(int) $eq->mensagem_id]) ? $mapa[(int) $eq->mensagem_id] : null;
    }

    public function enquetes_das_mensagens($msg_ids, $user_id)
    {
        $mapa = array();
        if (!$msg_ids || !$this->tem_tabela('mensagem_enquete')) {
            return $mapa;
        }
        $rows = $this->db->where_in('mensagem_id', array_values(array_unique(array_map('intval', (array) $msg_ids))))
            ->get('mensagem_enquete')
            ->result();
        return $this->formatar_enquetes($rows, (int) $user_id);
    }

    public function enquetes_canal($canal_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_enquete')) {
            return array();
        }
        $rows = $this->db->where('canal_id', (int) $canal_id)->order_by('id', 'DESC')->limit(50)->get('mensagem_enquete')->result();
        return $this->formatar_enquetes($rows, (int) $user_id);
    }

    private function formatar_enquetes($enquetes, $user_id)
    {
        $mapa = array();
        if (!$enquetes) {
            return $mapa;
        }
        $eq_ids = array();
        foreach ($enquetes as $eq) {
            $eq_ids[] = (int) $eq->id;
        }
        $opcoes_rows = $this->db->where_in('enquete_id', $eq_ids)->order_by('ordem', 'ASC')->order_by('id', 'ASC')->get('mensagem_enquete_opcao')->result();
        $votos_rows = $this->db->select('v.enquete_id, v.opcao_id, v.user_id, u.first_name, u.last_name')
            ->from('mensagem_enquete_voto v')
            ->join('users u', 'u.id = v.user_id', 'left')
            ->where_in('v.enquete_id', $eq_ids)
            ->get()
            ->result();

        $opcoes_por_eq = array();
        foreach ($opcoes_rows as $op) {
            $opcoes_por_eq[(int) $op->enquete_id][] = $op;
        }
        $votos_por_op = array();
        $votantes_unicos = array();
        foreach ($votos_rows as $v) {
            $votos_por_op[(int) $v->opcao_id][] = $v;
            $votantes_unicos[(int) $v->enquete_id][(int) $v->user_id] = true;
        }

        $agora = time();
        foreach ($enquetes as $eq) {
            $eid = (int) $eq->id;
            $ops = isset($opcoes_por_eq[$eid]) ? $opcoes_por_eq[$eid] : array();
            $total_votos = 0;
            foreach ($ops as $op) {
                $total_votos += isset($votos_por_op[(int) $op->id]) ? count($votos_por_op[(int) $op->id]) : 0;
            }
            $lista_ops = array();
            foreach ($ops as $op) {
                $vlist = isset($votos_por_op[(int) $op->id]) ? $votos_por_op[(int) $op->id] : array();
                $meu_voto = false;
                $nomes = array();
                foreach ($vlist as $v) {
                    if ((int) $v->user_id === $user_id) {
                        $meu_voto = true;
                    }
                    if (empty($eq->anonima)) {
                        $n = trim($v->first_name . ' ' . $v->last_name);
                        if ($n !== '') {
                            $nomes[] = $n;
                        }
                    }
                }
                $qtd = count($vlist);
                $lista_ops[] = array(
                    'id' => (int) $op->id,
                    'texto' => (string) $op->texto,
                    'votos' => $qtd,
                    'percentual' => $total_votos > 0 ? (int) round(($qtd / $total_votos) * 100) : 0,
                    'meu_voto' => $meu_voto,
                    'votantes' => $nomes,
                );
            }
            $encerrada = $eq->encerrar_em && strtotime($eq->encerrar_em) <= $agora;
            $mapa[(int) $eq->mensagem_id] = array(
                'id' => $eid,
                'mensagem_id' => (int) $eq->mensagem_id,
                'pergunta' => (string) $eq->pergunta,
                'anonima' => !empty($eq->anonima),
                'multipla' => !empty($eq->multipla),
                'encerrar_em' => $eq->encerrar_em,
                'encerrar_em_fmt' => $eq->encerrar_em ? date('d/m/Y H:i', strtotime($eq->encerrar_em)) : '',
                'encerrada' => $encerrada,
                'total_votos' => $total_votos,
                'total_votantes' => isset($votantes_unicos[$eid]) ? count($votantes_unicos[$eid]) : 0,
                'opcoes' => $lista_ops,
            );
        }
        return $mapa;
    }

    /* ---------- 7. Eventos e reuniões nos canais ---------- */

    public function criar_reuniao($canal, $user_id, $empresa_id, $titulo, $descricao, $inicio_em, $participantes = array())
    {
        if (!$this->tem_tabela('mensagem_reuniao') || !$this->tem_tabela('mensagem_reuniao_participante')) {
            return 'Reuniões indisponíveis: aplique o script sql/2026-11-16_mensagens_recursos_v3.sql.';
        }
        $titulo = mb_substr(trim((string) $titulo), 0, 160, 'UTF-8');
        if ($titulo === '') {
            return 'Informe o título da reunião.';
        }
        $ts = strtotime(str_replace('T', ' ', trim((string) $inicio_em)));
        if (!$ts) {
            return 'Informe uma data e horário válidos para a reunião.';
        }
        $data_ini = date('Y-m-d H:i:s', $ts);
        $descricao = trim((string) $descricao);

        $this->load->model('mensagens_model');
        $membros_canal = $this->mensagens_model->membros_canal((int) $canal->id, (int) $empresa_id, (int) $user_id);
        $membros_validos = array((int) $user_id => true);
        foreach ($membros_canal as $mb) {
            $membros_validos[(int) $mb['id']] = true;
        }
        $parts_ids = array((int) $user_id);
        foreach ((array) $participantes as $pid) {
            $pid = (int) $pid;
            if ($pid > 0 && isset($membros_validos[$pid]) && !in_array($pid, $parts_ids, true)) {
                $parts_ids[] = $pid;
            }
        }

        $agora = date('Y-m-d H:i:s');
        $this->db->trans_start();
        $msg_data = array(
            'empresa_id' => (int) $empresa_id,
            'de_id' => (int) $user_id,
            'para_id' => 0,
            'canal_id' => (int) $canal->id,
            'texto' => $this->mensagens_model->codificar_emojis('Reunião: ' . $titulo . ' · ' . date('d/m H:i', $ts)),
            'created_at' => $agora,
        );
        if ($this->db->field_exists('tipo_conteudo', 'mensagem')) {
            $msg_data['tipo_conteudo'] = 'reuniao';
        }
        $this->db->insert('mensagem', $msg_data);
        $mensagem_id = (int) $this->db->insert_id();
        $this->db->insert('mensagem_reuniao', array(
            'empresa_id' => (int) $empresa_id,
            'canal_id' => (int) $canal->id,
            'mensagem_id' => $mensagem_id,
            'criador_id' => (int) $user_id,
            'titulo' => $titulo,
            'descricao' => $descricao !== '' ? $descricao : null,
            'inicio_em' => $data_ini,
            'created_at' => $agora,
        ));
        $reuniao_id = (int) $this->db->insert_id();
        foreach ($parts_ids as $pid) {
            $st = ($pid === (int) $user_id) ? 'confirmado' : 'pendente';
            $this->db->insert('mensagem_reuniao_participante', array(
                'reuniao_id' => $reuniao_id,
                'user_id' => $pid,
                'status' => $st,
                'respondido_em' => $st === 'confirmado' ? $agora : null,
            ));
        }
        $this->db->trans_complete();
        if (!$this->db->trans_status() || !$mensagem_id) {
            return 'Não foi possível agendar a reunião.';
        }
        $this->mensagens_model->marcar_lido_canal((int) $user_id, (int) $canal->id);
        return $this->mensagens_model->buscar_mensagem_exibicao($mensagem_id, (int) $user_id, (int) $empresa_id);
    }

    public function responder_reuniao($reuniao_id, $user_id, $empresa_id, $status)
    {
        if (!$this->tem_tabela('mensagem_reuniao') || !$this->tem_tabela('mensagem_reuniao_participante')) {
            return 'Reuniões indisponíveis.';
        }
        if (!in_array($status, array('confirmado', 'recusado', 'pendente'), true)) {
            return 'Status de presença inválido.';
        }
        $reu = $this->db->where('id', (int) $reuniao_id)->where('empresa_id', (int) $empresa_id)->get('mensagem_reuniao')->row();
        if (!$reu) {
            return 'Reunião não encontrada.';
        }
        $this->load->model('mensagens_model');
        if (!$this->mensagens_model->canal_do_usuario((int) $reu->canal_id, (int) $user_id, (int) $empresa_id)) {
            return 'Você não participa deste canal.';
        }
        $agora = date('Y-m-d H:i:s');
        $this->db->query(
            'REPLACE INTO mensagem_reuniao_participante (reuniao_id, user_id, status, respondido_em) VALUES (?, ?, ?, ?)',
            array((int) $reu->id, (int) $user_id, $status, $agora)
        );
        $mapa = $this->reunioes_das_mensagens(array((int) $reu->mensagem_id), (int) $user_id);
        return isset($mapa[(int) $reu->mensagem_id]) ? $mapa[(int) $reu->mensagem_id] : null;
    }

    public function reunioes_das_mensagens($msg_ids, $user_id)
    {
        $mapa = array();
        if (!$msg_ids || !$this->tem_tabela('mensagem_reuniao')) {
            return $mapa;
        }
        $rows = $this->db->where_in('mensagem_id', array_values(array_unique(array_map('intval', (array) $msg_ids))))
            ->get('mensagem_reuniao')
            ->result();
        return $this->formatar_reunioes($rows, (int) $user_id);
    }

    public function reunioes_canal($canal_id, $user_id)
    {
        if (!$this->tem_tabela('mensagem_reuniao')) {
            return array();
        }
        $rows = $this->db->where('canal_id', (int) $canal_id)->order_by('id', 'DESC')->limit(50)->get('mensagem_reuniao')->result();
        return $this->formatar_reunioes($rows, (int) $user_id);
    }

    private function formatar_reunioes($reunioes, $user_id)
    {
        $mapa = array();
        if (!$reunioes) {
            return $mapa;
        }
        $ids = array();
        foreach ($reunioes as $r) {
            $ids[] = (int) $r->id;
        }
        $prows = $this->db->select('p.reuniao_id, p.user_id, p.status, u.first_name, u.last_name')
            ->from('mensagem_reuniao_participante p')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->where_in('p.reuniao_id', $ids)
            ->order_by('p.status', 'ASC')
            ->get()
            ->result();
        $por_reu = array();
        foreach ($prows as $p) {
            $por_reu[(int) $p->reuniao_id][] = $p;
        }
        foreach ($reunioes as $r) {
            $rid = (int) $r->id;
            $parts = isset($por_reu[$rid]) ? $por_reu[$rid] : array();
            $conf = 0;
            $rec = 0;
            $pend = 0;
            $meu_st = '';
            $lista_parts = array();
            foreach ($parts as $p) {
                if ((int) $p->user_id === $user_id) {
                    $meu_st = $p->status;
                }
                if ($p->status === 'confirmado') {
                    $conf++;
                } elseif ($p->status === 'recusado') {
                    $rec++;
                } else {
                    $pend++;
                }
                $lista_parts[] = array(
                    'user_id' => (int) $p->user_id,
                    'nome' => trim($p->first_name . ' ' . $p->last_name),
                    'status' => $p->status,
                );
            }
            $mapa[(int) $r->mensagem_id] = array(
                'id' => $rid,
                'mensagem_id' => (int) $r->mensagem_id,
                'canal_id' => (int) $r->canal_id,
                'chave' => 'c' . (int) $r->canal_id,
                'titulo' => (string) $r->titulo,
                'descricao' => (string) $r->descricao,
                'inicio_em' => $r->inicio_em,
                'inicio_em_fmt' => date('d/m/Y \à\s H:i', strtotime($r->inicio_em)),
                'meu_status' => $meu_st,
                'confirmados' => $conf,
                'recusados' => $rec,
                'pendentes' => $pend,
                'participantes' => $lista_parts,
            );
        }
        return $mapa;
    }

    /* ---------- 8. Bot de Chamados ---------- */

    public function eh_bot($user_id)
    {
        if ((int) $user_id <= 0 || !$this->db->field_exists('is_bot', 'users')) {
            return false;
        }
        return $this->db->where('id', (int) $user_id)->where('is_bot', 1)->count_all_results('users') > 0;
    }

    public function obter_ou_criar_bot()
    {
        if (!$this->db->field_exists('is_bot', 'users')) {
            return 0;
        }
        $row = $this->db->select('id')->where('is_bot', 1)->where('active', 1)->order_by('id', 'ASC')->limit(1)->get('users')->row();
        if ($row) {
            return (int) $row->id;
        }
        $this->db->insert('users', array(
            'ip_address' => '127.0.0.1',
            'username' => 'bot.chamados',
            'password' => '!BOT_SEM_LOGIN!',
            'email' => 'bot.chamados@saxweb.local',
            'created_on' => time(),
            'active' => 1,
            'first_name' => 'Bot',
            'last_name' => 'Chamados',
            'is_bot' => 1,
        ));
        return (int) $this->db->insert_id();
    }
}
