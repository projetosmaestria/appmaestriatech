<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Treinamentos_banners_model extends CI_Model {

    protected $table = 'treinamento_banners';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retorna os banners ativos para o carrossel do dashboard
     */
    public function get_banners_ativos($empresa_id = 1)
    {
        $now = date('Y-m-d H:i:s');
        $this->db->from($this->table);
        $this->db->where('ativo', 1);
        if ($empresa_id) {
            $this->db->where('(empresa_id = ' . (int)$empresa_id . ' OR empresa_id IS NULL)');
        }
        $this->db->where('(data_inicio IS NULL OR data_inicio <= "' . $now . '")');
        $this->db->where('(data_fim IS NULL OR data_fim >= "' . $now . '")');
        $this->db->order_by('ordem ASC, id DESC');

        return $this->db->get()->result();
    }

    /**
     * Retorna todos os banners para gestao administrativa
     */
    public function get_all_banners($empresa_id = 1)
    {
        $this->db->from($this->table);
        if ($empresa_id) {
            $this->db->where('(empresa_id = ' . (int)$empresa_id . ' OR empresa_id IS NULL)');
        }
        $this->db->order_by('ordem ASC, id DESC');

        return $this->db->get()->result();
    }

    /**
     * Retorna um banner por ID
     */
    public function get_banner($id)
    {
        $this->db->where('id', (int)$id);
        return $this->db->get($this->table)->row();
    }

    /**
     * Inserir banner
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if (!isset($data['ordem']) || empty($data['ordem'])) {
            $this->db->select_max('ordem');
            $max = $this->db->get($this->table)->row();
            $data['ordem'] = ($max && $max->ordem) ? ((int)$max->ordem + 1) : 1;
        }

        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Atualizar banner
     */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int)$id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Excluir banner
     */
    public function delete($id)
    {
        $banner = $this->get_banner($id);
        if ($banner && !empty($banner->imagem) && file_exists(FCPATH . $banner->imagem)) {
            // Se for arquivo em upload, remover
            if (strpos($banner->imagem, 'upload/treinamentos') !== false) {
                @unlink(FCPATH . $banner->imagem);
            }
        }

        $this->db->where('id', (int)$id);
        return $this->db->delete($this->table);
    }
}
