<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Treinamentos_etapas_model extends CI_Model {

    protected $table = 'treinamento_etapas';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retorna todas as etapas ativas de um treinamento ordenadas
     */
    public function get_etapas($treinamento_id, $somente_ativas = true)
    {
        $this->db->select('te.*,
            (SELECT COUNT(ta.id) FROM treinamento_arquivos ta WHERE ta.etapa_id = te.id) as total_arquivos,
            (SELECT COUNT(tat.id) FROM treinamento_atividades tat WHERE tat.etapa_id = te.id AND tat.ativo = 1) as total_atividades,
            (SELECT COUNT(tat.id) FROM treinamento_atividades tat WHERE tat.etapa_id = te.id AND tat.ativo = 1 AND tat.obrigatoria = 1) as total_atividades_obrigatorias');
        $this->db->from($this->table . ' te');
        $this->db->where('te.treinamento_id', (int)$treinamento_id);
        if ($somente_ativas) {
            $this->db->where('te.ativo', 1);
        }
        $this->db->order_by('te.ordem ASC, te.id ASC');

        return $this->db->get()->result();
    }

    /**
     * Retorna uma etapa por ID
     */
    public function get_etapa($etapa_id)
    {
        $this->db->select('te.*, t.titulo as treinamento_titulo, t.modulo_id, t.avaliacao_obrigatoria');
        $this->db->from($this->table . ' te');
        $this->db->join('treinamentos t', 't.id = te.treinamento_id', 'inner');
        $this->db->where('te.id', (int)$etapa_id);

        return $this->db->get()->row();
    }

    /**
     * Retorna a primeira etapa de um treinamento
     */
    public function get_primeira_etapa($treinamento_id)
    {
        $this->db->from($this->table);
        $this->db->where('treinamento_id', (int)$treinamento_id);
        $this->db->where('ativo', 1);
        $this->db->order_by('ordem ASC, id ASC');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    /**
     * Retorna a proxima etapa de um treinamento
     */
    public function get_proxima_etapa($treinamento_id, $ordem_atual)
    {
        $this->db->from($this->table);
        $this->db->where('treinamento_id', (int)$treinamento_id);
        $this->db->where('ordem >', (int)$ordem_atual);
        $this->db->where('ativo', 1);
        $this->db->order_by('ordem ASC, id ASC');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    /**
     * Retorna a etapa anterior de um treinamento
     */
    public function get_etapa_anterior($treinamento_id, $ordem_atual)
    {
        $this->db->from($this->table);
        $this->db->where('treinamento_id', (int)$treinamento_id);
        $this->db->where('ordem <', (int)$ordem_atual);
        $this->db->where('ativo', 1);
        $this->db->order_by('ordem DESC, id DESC');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    /**
     * Cria uma nova etapa
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if (!isset($data['ordem']) || empty($data['ordem'])) {
            $this->db->select_max('ordem');
            $this->db->where('treinamento_id', (int)$data['treinamento_id']);
            $max = $this->db->get($this->table)->row();
            $data['ordem'] = ($max && $max->ordem) ? ((int)$max->ordem + 1) : 1;
        }

        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Atualiza uma etapa
     */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int)$id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Exclui uma etapa
     */
    public function delete($id)
    {
        $this->db->where('id', (int)$id);
        return $this->db->delete($this->table);
    }

    // -------------------------------------------------------------
    // MATERIAIS DE APOIO / ARQUIVOS
    // -------------------------------------------------------------

    public function get_arquivos($etapa_id)
    {
        $this->db->where('etapa_id', (int)$etapa_id);
        $this->db->order_by('id ASC');
        return $this->db->get('treinamento_arquivos')->result();
    }

    public function get_arquivo($arquivo_id)
    {
        $this->db->where('id', (int)$arquivo_id);
        return $this->db->get('treinamento_arquivos')->row();
    }

    public function add_arquivo($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if ($this->db->insert('treinamento_arquivos', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function delete_arquivo($arquivo_id)
    {
        $arquivo = $this->get_arquivo($arquivo_id);
        if ($arquivo) {
            $filePath = FCPATH . $arquivo->caminho;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $this->db->where('id', (int)$arquivo_id);
            return $this->db->delete('treinamento_arquivos');
        }
        return false;
    }

    // -------------------------------------------------------------
    // ATIVIDADES ORIENTATIVAS
    // -------------------------------------------------------------

    public function get_atividades($etapa_id, $somente_ativas = true)
    {
        $this->db->where('etapa_id', (int)$etapa_id);
        if ($somente_ativas) {
            $this->db->where('ativo', 1);
        }
        $this->db->order_by('ordem ASC, id ASC');
        return $this->db->get('treinamento_atividades')->result();
    }

    public function get_atividade($atividade_id)
    {
        $this->db->where('id', (int)$atividade_id);
        return $this->db->get('treinamento_atividades')->row();
    }

    public function add_atividade($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if (!isset($data['ordem']) || empty($data['ordem'])) {
            $this->db->select_max('ordem');
            $this->db->where('etapa_id', (int)$data['etapa_id']);
            $max = $this->db->get('treinamento_atividades')->row();
            $data['ordem'] = ($max && $max->ordem) ? ((int)$max->ordem + 1) : 1;
        }

        if ($this->db->insert('treinamento_atividades', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function update_atividade($atividade_id, $data)
    {
        $this->db->where('id', (int)$atividade_id);
        return $this->db->update('treinamento_atividades', $data);
    }

    public function delete_atividade($atividade_id)
    {
        $this->db->where('id', (int)$atividade_id);
        return $this->db->delete('treinamento_atividades');
    }
}
