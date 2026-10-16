<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Treinamentos_avaliacoes_model extends CI_Model {

    protected $table = 'treinamento_avaliacoes';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Salva ou atualiza a avaliacao de um treinamento por um usuario
     */
    public function salvar_avaliacao($treinamento_id, $user_id, $nota, $comentario = '')
    {
        $nota = max(1, min(5, (int)$nota));
        $comentario = trim($comentario);
        $now = date('Y-m-d H:i:s');

        $this->db->where('treinamento_id', (int)$treinamento_id);
        $this->db->where('user_id', (int)$user_id);
        $existe = $this->db->get($this->table)->row();

        if ($existe) {
            $this->db->where('id', $existe->id);
            return $this->db->update($this->table, array(
                'nota' => $nota,
                'comentario' => $comentario,
                'updated_at' => $now
            ));
        } else {
            return $this->db->insert($this->table, array(
                'treinamento_id' => (int)$treinamento_id,
                'user_id' => (int)$user_id,
                'nota' => $nota,
                'comentario' => $comentario,
                'created_at' => $now
            ));
        }
    }

    /**
     * Retorna a avaliacao dada por um usuario para um treinamento
     */
    public function get_avaliacao_usuario($treinamento_id, $user_id)
    {
        $this->db->where('treinamento_id', (int)$treinamento_id);
        $this->db->where('user_id', (int)$user_id);
        return $this->db->get($this->table)->row();
    }

    /**
     * Retorna estatisticas de avaliacao de um treinamento
     */
    public function get_estatisticas_treinamento($treinamento_id)
    {
        $this->db->select('
            COUNT(id) as total_avaliacoes,
            COALESCE(AVG(nota), 0) as media,
            COUNT(CASE WHEN nota = 5 THEN 1 END) as estrelas_5,
            COUNT(CASE WHEN nota = 4 THEN 1 END) as estrelas_4,
            COUNT(CASE WHEN nota = 3 THEN 1 END) as estrelas_3,
            COUNT(CASE WHEN nota = 2 THEN 1 END) as estrelas_2,
            COUNT(CASE WHEN nota = 1 THEN 1 END) as estrelas_1
        ');
        $this->db->where('treinamento_id', (int)$treinamento_id);
        $row = $this->db->get($this->table)->row();

        return array(
            'total' => (int)($row->total_avaliacoes ?? 0),
            'media' => round((float)($row->media ?? 0), 1),
            'distribuicao' => array(
                5 => (int)($row->estrelas_5 ?? 0),
                4 => (int)($row->estrelas_4 ?? 0),
                3 => (int)($row->estrelas_3 ?? 0),
                2 => (int)($row->estrelas_2 ?? 0),
                1 => (int)($row->estrelas_1 ?? 0),
            )
        );
    }

    /**
     * Retorna avaliacoes para os relatorios administrativos
     */
    public function get_relatorio_avaliacoes($params = array())
    {
        $this->db->select('ta.*, t.titulo as treinamento_titulo, m.nome as modulo_nome, u.first_name, u.last_name, u.email');
        $this->db->from($this->table . ' ta');
        $this->db->join('treinamentos t', 't.id = ta.treinamento_id', 'inner');
        $this->db->join('modulos m', 'm.id = t.modulo_id', 'left');
        $this->db->join('users u', 'u.id = ta.user_id', 'left');

        if (isset($params['treinamento_id']) && !empty($params['treinamento_id'])) {
            $this->db->where('ta.treinamento_id', (int)$params['treinamento_id']);
        }
        if (isset($params['modulo_id']) && !empty($params['modulo_id'])) {
            $this->db->where('t.modulo_id', (int)$params['modulo_id']);
        }
        if (isset($params['nota']) && !empty($params['nota'])) {
            $this->db->where('ta.nota', (int)$params['nota']);
        }
        if (isset($params['com_comentario']) && $params['com_comentario'] == 1) {
            $this->db->where('ta.comentario IS NOT NULL AND ta.comentario != ""');
        }

        $this->db->order_by('ta.created_at DESC, ta.id DESC');

        if (isset($params['limit'])) {
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            $this->db->limit((int)$params['limit'], $offset);
        }

        return $this->db->get()->result();
    }

    /**
     * Retorna resumo geral das avaliacoes de toda a plataforma
     */
    public function get_resumo_geral()
    {
        $this->db->select('
            COUNT(ta.id) as total_avaliacoes,
            COALESCE(AVG(ta.nota), 0) as media_geral,
            COUNT(CASE WHEN ta.nota = 5 THEN 1 END) as total_5,
            COUNT(CASE WHEN ta.nota = 4 THEN 1 END) as total_4,
            COUNT(CASE WHEN ta.nota = 3 THEN 1 END) as total_3,
            COUNT(CASE WHEN ta.nota = 2 THEN 1 END) as total_2,
            COUNT(CASE WHEN ta.nota = 1 THEN 1 END) as total_1
        ');
        $this->db->from($this->table . ' ta');
        $row = $this->db->get()->row();

        $mediaCalc = round((float)($row->media_geral ?? 0), 1);

        return array(
            'total' => (int)($row->total_avaliacoes ?? 0),
            'media' => $mediaCalc,
            'media_geral' => $mediaCalc,
            'distribuicao' => array(
                5 => (int)($row->total_5 ?? 0),
                4 => (int)($row->total_4 ?? 0),
                3 => (int)($row->total_3 ?? 0),
                2 => (int)($row->total_2 ?? 0),
                1 => (int)($row->total_1 ?? 0),
            )
        );
    }
}
