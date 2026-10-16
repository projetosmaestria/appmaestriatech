<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Treinamentos_progresso_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('treinamentos_model');
        $this->load->model('treinamentos_etapas_model');
    }

    /**
     * Retorna o progresso de um usuario em um treinamento
     */
    public function get_progresso_treinamento($treinamento_id, $user_id)
    {
        $this->db->where('treinamento_id', (int)$treinamento_id);
        $this->db->where('user_id', (int)$user_id);
        $prog = $this->db->get('treinamento_progresso')->row();

        if (!$prog) {
            // Inicializa se nao existir
            $this->iniciar_treinamento($treinamento_id, $user_id);
            $this->db->where('treinamento_id', (int)$treinamento_id);
            $this->db->where('user_id', (int)$user_id);
            $prog = $this->db->get('treinamento_progresso')->row();
        }

        return $prog;
    }

    /**
     * Inicializa o registro de progresso de um usuario em um treinamento
     */
    public function iniciar_treinamento($treinamento_id, $user_id, $etapa_id = null, $acao = null)
    {
        $this->db->where('treinamento_id', (int)$treinamento_id);
        $this->db->where('user_id', (int)$user_id);
        $existe = $this->db->get('treinamento_progresso')->row();

        $this->db->where('treinamento_id', (int)$treinamento_id);
        $this->db->where('ativo', 1);
        $totalEtapas = $this->db->count_all_results('treinamento_etapas');

        $now = date('Y-m-d H:i:s');
        if (!$existe) {
            $this->db->insert('treinamento_progresso', array(
                'treinamento_id' => (int)$treinamento_id,
                'user_id' => (int)$user_id,
                'etapas_concluidas' => 0,
                'total_etapas' => $totalEtapas,
                'percentual' => 0.00,
                'status' => 'em_andamento',
                'ultima_etapa_id' => $etapa_id ? (int)$etapa_id : null,
                'ultima_acao' => $acao ? $acao : 'Iniciou o treinamento',
                'data_inicio' => $now,
                'ultima_atividade' => $now,
                'created_at' => $now
            ));
        } else {
            $updateData = array(
                'total_etapas' => $totalEtapas,
                'ultima_atividade' => $now
            );
            if ($etapa_id) {
                $updateData['ultima_etapa_id'] = (int)$etapa_id;
            }
            if ($acao) {
                $updateData['ultima_acao'] = $acao;
            }
            $this->db->where('id', $existe->id);
            $this->db->update('treinamento_progresso', $updateData);
        }
    }

    /**
     * Registra o acesso do usuario a uma etapa especifica e sua atividade atual
     */
    public function registrar_acesso_etapa($treinamento_id, $etapa_id, $user_id, $acao = null)
    {
        $etapa = $this->treinamentos_etapas_model->get_etapa($etapa_id);
        $nomeEtapa = $etapa ? $etapa->titulo : 'Etapa ' . $etapa_id;
        $descricaoAcao = $acao ? $acao : 'Visualizando: ' . $nomeEtapa;

        $this->iniciar_treinamento($treinamento_id, $user_id, $etapa_id, $descricaoAcao);

        // Atualizar tambem o registro de progresso da etapa
        $progEtapa = $this->get_progresso_etapa($etapa_id, $user_id);
        $now = date('Y-m-d H:i:s');
        if (!$progEtapa) {
            $this->db->insert('treinamento_etapa_progresso', array(
                'treinamento_id' => (int)$treinamento_id,
                'etapa_id' => (int)$etapa_id,
                'user_id' => (int)$user_id,
                'video_tempo_assistido' => 0,
                'video_concluido' => 0,
                'atividades_concluidas' => 0,
                'status' => 'em_andamento',
                'created_at' => $now
            ));
        }
    }

    /**
     * Retorna lista de treinamentos em andamento para a secao 'Continue de onde parou'
     */
    public function get_treinamentos_em_andamento($user_id, $limit = 6)
    {
        $this->db->select('tp.*, t.titulo, t.imagem_capa, t.carga_horaria_minutos, t.nivel, m.nome as modulo_nome');
        $this->db->from('treinamento_progresso tp');
        $this->db->join('treinamentos t', 't.id = tp.treinamento_id', 'inner');
        $this->db->join('modulos m', 'm.id = t.modulo_id', 'left');
        $this->db->where('tp.user_id', (int)$user_id);
        $this->db->where('tp.status', 'em_andamento');
        $this->db->where('t.ativo', 1);
        $this->db->order_by('tp.ultima_atividade DESC, tp.id DESC');
        $this->db->limit((int)$limit);

        $result = $this->db->get()->result();

        // Para cada um, buscar qual a etapa atual a continuar
        foreach ($result as &$item) {
            $item->proxima_etapa = $this->get_etapa_a_continuar($item->treinamento_id, $user_id);
        }

        return $result;
    }

    /**
     * Determina qual etapa o usuario deve continuar
     */
    public function get_etapa_a_continuar($treinamento_id, $user_id)
    {
        $etapas = $this->treinamentos_etapas_model->get_etapas($treinamento_id);
        if (empty($etapas)) return null;

        foreach ($etapas as $ep) {
            $epProg = $this->get_progresso_etapa($ep->id, $user_id);
            if (!$epProg || $epProg->status !== 'concluido') {
                return $ep;
            }
        }

        return $etapas[0];
    }

    /**
     * Retorna o progresso de uma etapa para um usuario
     */
    public function get_progresso_etapa($etapa_id, $user_id)
    {
        $this->db->where('etapa_id', (int)$etapa_id);
        $this->db->where('user_id', (int)$user_id);
        return $this->db->get('treinamento_etapa_progresso')->row();
    }

    /**
     * Salva o tempo assistido do video e marca como concluido se alcancar o fim
     */
    public function salvar_progresso_video($etapa_id, $user_id, $tempo_segundos, $concluido = false)
    {
        $etapa = $this->treinamentos_etapas_model->get_etapa($etapa_id);
        if (!$etapa) return false;

        $prog = $this->get_progresso_etapa($etapa_id, $user_id);
        $now = date('Y-m-d H:i:s');

        // Atualizar ultima atividade do treinamento
        $this->iniciar_treinamento($etapa->treinamento_id, $user_id);

        if (!$prog) {
            $data = array(
                'treinamento_id' => $etapa->treinamento_id,
                'etapa_id' => (int)$etapa_id,
                'user_id' => (int)$user_id,
                'video_tempo_assistido' => (int)$tempo_segundos,
                'video_concluido' => $concluido ? 1 : 0,
                'status' => 'em_andamento',
                'created_at' => $now
            );
            $this->db->insert('treinamento_etapa_progresso', $data);
        } else {
            $data = array(
                'video_tempo_assistido' => max((int)$prog->video_tempo_assistido, (int)$tempo_segundos),
                'updated_at' => $now
            );
            if ($concluido || $prog->video_concluido == 1) {
                $data['video_concluido'] = 1;
            }
            $this->db->where('id', $prog->id);
            $this->db->update('treinamento_etapa_progresso', $data);
        }

        return true;
    }

    /**
     * Alterna o estado de uma atividade orientativa pelo usuario
     */
    public function toggle_atividade($atividade_id, $user_id)
    {
        $atividade = $this->treinamentos_etapas_model->get_atividade($atividade_id);
        if (!$atividade) return false;

        $this->db->where('atividade_id', (int)$atividade_id);
        $this->db->where('user_id', (int)$user_id);
        $prog = $this->db->get('treinamento_atividade_progresso')->row();

        $now = date('Y-m-d H:i:s');
        $novoStatus = 1;

        if (!$prog) {
            $this->db->insert('treinamento_atividade_progresso', array(
                'atividade_id' => (int)$atividade_id,
                'etapa_id' => $atividade->etapa_id,
                'treinamento_id' => $atividade->treinamento_id,
                'user_id' => (int)$user_id,
                'concluida' => 1,
                'data_conclusao' => $now
            ));
            $novoStatus = 1;
        } else {
            $novoStatus = ($prog->concluida == 1) ? 0 : 1;
            $this->db->where('id', $prog->id);
            $this->db->update('treinamento_atividade_progresso', array(
                'concluida' => $novoStatus,
                'data_conclusao' => ($novoStatus == 1) ? $now : null
            ));
        }

        // Atualizar se todas as atividades foram concluidas
        $this->atualizar_status_atividades_etapa($atividade->etapa_id, $user_id);

        return $novoStatus;
    }

    /**
     * Atualiza no registro da etapa se todas as atividades obrigatórias foram cumpridas
     */
    public function atualizar_status_atividades_etapa($etapa_id, $user_id)
    {
        $atividadesObrigatorias = $this->db->where(array('etapa_id' => (int)$etapa_id, 'ativo' => 1, 'obrigatoria' => 1))
            ->get('treinamento_atividades')->result();

        $todasConcluidas = 1;
        if (!empty($atividadesObrigatorias)) {
            foreach ($atividadesObrigatorias as $at) {
                $chk = $this->db->where(array('atividade_id' => $at->id, 'user_id' => (int)$user_id, 'concluida' => 1))
                    ->count_all_results('treinamento_atividade_progresso');
                if ($chk == 0) {
                    $todasConcluidas = 0;
                    break;
                }
            }
        }

        $prog = $this->get_progresso_etapa($etapa_id, $user_id);
        if ($prog) {
            $this->db->where('id', $prog->id);
            $this->db->update('treinamento_etapa_progresso', array(
                'atividades_concluidas' => $todasConcluidas,
                'updated_at' => date('Y-m-d H:i:s')
            ));
        }

        return $todasConcluidas;
    }

    /**
     * Retorna IDs das atividades concluidas de uma etapa pelo usuario
     */
    public function get_atividades_concluidas_ids($etapa_id, $user_id)
    {
        $this->db->select('atividade_id');
        $this->db->where('etapa_id', (int)$etapa_id);
        $this->db->where('user_id', (int)$user_id);
        $this->db->where('concluida', 1);
        $res = $this->db->get('treinamento_atividade_progresso')->result();

        $ids = array();
        foreach ($res as $r) {
            $ids[] = (int)$r->atividade_id;
        }
        return $ids;
    }

    /**
     * Verifica se o usuario pode acessar uma etapa especifica.
     * Regra: Se existirem etapas anteriores ativas, todas as anteriores devem estar concluidas.
     */
    public function verificar_etapa_liberada($treinamento_id, $etapa_id, $user_id)
    {
        $etapaAtual = $this->treinamentos_etapas_model->get_etapa($etapa_id);
        if (!$etapaAtual || $etapaAtual->treinamento_id != $treinamento_id) {
            return false;
        }

        // Se for a primeira etapa, sempre liberada
        $etapasAnteriores = $this->db->where('treinamento_id', (int)$treinamento_id)
            ->where('ordem <', (int)$etapaAtual->ordem)
            ->where('ativo', 1)
            ->get('treinamento_etapas')->result();

        if (empty($etapasAnteriores)) {
            return true;
        }

        // Checar se todas as etapas anteriores estao concluidas
        foreach ($etapasAnteriores as $ant) {
            $progAnt = $this->get_progresso_etapa($ant->id, $user_id);
            if (!$progAnt || $progAnt->status !== 'concluido') {
                return false;
            }
        }

        return true;
    }

    /**
     * Valida no backend se a etapa cumpre todos os requisitos para ser concluida
     */
    public function verificar_etapa_concluivel($etapa_id, $user_id)
    {
        $etapa = $this->treinamentos_etapas_model->get_etapa($etapa_id);
        if (!$etapa) {
            return array('permitido' => false, 'motivo' => 'Etapa não encontrada.');
        }

        $prog = $this->get_progresso_etapa($etapa_id, $user_id);

        // 1. Validar video obrigatorio
        if ($etapa->video_obrigatorio == 1 && $etapa->video_tipo !== 'nenhum' && !empty($etapa->video_url)) {
            if (!$prog || $prog->video_concluido != 1) {
                return array('permitido' => false, 'motivo' => 'Você precisa assistir ao vídeo obrigatório integralmente antes de concluir esta etapa.');
            }
        }

        // 2. Validar atividades orientativas obrigatorias
        $atividadesObrigatorias = $this->db->where(array('etapa_id' => (int)$etapa_id, 'ativo' => 1, 'obrigatoria' => 1))
            ->get('treinamento_atividades')->result();

        if (!empty($atividadesObrigatorias)) {
            foreach ($atividadesObrigatorias as $at) {
                $chk = $this->db->where(array('atividade_id' => $at->id, 'user_id' => (int)$user_id, 'concluida' => 1))
                    ->count_all_results('treinamento_atividade_progresso');
                if ($chk == 0) {
                    return array('permitido' => false, 'motivo' => 'Existem atividades práticas obrigatórias pendentes de marcação nesta etapa.');
                }
            }
        }

        return array('permitido' => true, 'motivo' => '');
    }

    /**
     * Conclui a etapa e recalcula o progresso do treinamento
     */
    public function concluir_etapa($etapa_id, $user_id)
    {
        $validacao = $this->verificar_etapa_concluivel($etapa_id, $user_id);
        if (!$validacao['permitido']) {
            return $validacao;
        }

        $etapa = $this->treinamentos_etapas_model->get_etapa($etapa_id);
        $now = date('Y-m-d H:i:s');

        $prog = $this->get_progresso_etapa($etapa_id, $user_id);
        if (!$prog) {
            $this->db->insert('treinamento_etapa_progresso', array(
                'treinamento_id' => $etapa->treinamento_id,
                'etapa_id' => (int)$etapa_id,
                'user_id' => (int)$user_id,
                'video_concluido' => 1,
                'atividades_concluidas' => 1,
                'status' => 'concluido',
                'data_conclusao' => $now,
                'created_at' => $now
            ));
        } else {
            $this->db->where('id', $prog->id);
            $this->db->update('treinamento_etapa_progresso', array(
                'status' => 'concluido',
                'data_conclusao' => $now,
                'updated_at' => $now
            ));
        }

        // Recalcular progresso geral
        $this->recalcular_progresso_treinamento($etapa->treinamento_id, $user_id);

        return array('permitido' => true, 'motivo' => 'Etapa concluída com sucesso!');
    }

    /**
     * Recalcula o percentual de conclusao e status geral do treinamento
     */
    public function recalcular_progresso_treinamento($treinamento_id, $user_id)
    {
        $totalEtapas = $this->db->where('treinamento_id', (int)$treinamento_id)
            ->where('ativo', 1)
            ->count_all_results('treinamento_etapas');

        if ($totalEtapas == 0) {
            $percentual = 100.00;
            $etapasConcluidas = 0;
            $status = 'concluido';
        } else {
            // Contar etapas ativas concluidas
            $this->db->from('treinamento_etapa_progresso tep');
            $this->db->join('treinamento_etapas te', 'te.id = tep.etapa_id', 'inner');
            $this->db->where('tep.treinamento_id', (int)$treinamento_id);
            $this->db->where('tep.user_id', (int)$user_id);
            $this->db->where('tep.status', 'concluido');
            $this->db->where('te.ativo', 1);
            $etapasConcluidas = $this->db->count_all_results();

            $percentual = round(($etapasConcluidas / $totalEtapas) * 100, 2);
            $status = ($percentual >= 100.00) ? 'concluido' : 'em_andamento';
        }

        $now = date('Y-m-d H:i:s');
        $this->db->where('treinamento_id', (int)$treinamento_id);
        $this->db->where('user_id', (int)$user_id);
        $existe = $this->db->get('treinamento_progresso')->row();

        $dadosProg = array(
            'etapas_concluidas' => $etapasConcluidas,
            'total_etapas' => $totalEtapas,
            'percentual' => $percentual,
            'status' => $status,
            'ultima_atividade' => $now,
            'updated_at' => $now
        );

        if ($status === 'concluido' && (!$existe || empty($existe->data_conclusao))) {
            $dadosProg['data_conclusao'] = $now;
        }

        if (!$existe) {
            $dadosProg['treinamento_id'] = (int)$treinamento_id;
            $dadosProg['user_id'] = (int)$user_id;
            $dadosProg['data_inicio'] = $now;
            $dadosProg['created_at'] = $now;
            $this->db->insert('treinamento_progresso', $dadosProg);
        } else {
            $this->db->where('id', $existe->id);
            $this->db->update('treinamento_progresso', $dadosProg);
        }

        return $percentual;
    }

    /**
     * Retorna resumo estatistico do usuario para a secao 'Meu Progresso'
     */
    public function get_resumo_progresso_usuario($user_id)
    {
        $this->db->select('
            COUNT(CASE WHEN tp.status = "concluido" THEN 1 END) as total_concluidos,
            COUNT(CASE WHEN tp.status = "em_andamento" THEN 1 END) as total_em_andamento,
            COUNT(tp.id) as total_iniciados,
            COALESCE(AVG(tp.percentual), 0) as media_percentual
        ');
        $this->db->from('treinamento_progresso tp');
        $this->db->join('treinamentos t', 't.id = tp.treinamento_id', 'inner');
        $this->db->where('tp.user_id', (int)$user_id);
        $this->db->where('t.ativo', 1);

        $row = $this->db->get()->row();

        // Total de treinamentos disponiveis para o usuario
        $totalDisponiveis = $this->db->where('ativo', 1)->count_all_results('treinamentos');

        return array(
            'concluidos' => (int)($row->total_concluidos ?? 0),
            'em_andamento' => (int)($row->total_em_andamento ?? 0),
            'total_disponiveis' => $totalDisponiveis,
            'percentual_geral' => round((float)($row->media_percentual ?? 0), 1)
        );
    }
}
