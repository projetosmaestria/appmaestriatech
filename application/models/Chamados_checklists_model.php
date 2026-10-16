<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Chamados_checklists_model extends CI_Model
{

    protected $table = 'chamados_checklists';
    protected $etapas_table = 'chamados_checklists_etapas';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get checklists by chamado ID
     * 
     * @param int $chamado_id
     * @return object
     */
    public function get_by_chamado($chamado_id)
    {
        $this->db->where('chamado_id', $chamado_id);
        $this->db->where('status', 1);
        $this->db->order_by('data_criacao', 'DESC');
        return $this->db->get($this->table)->result();
    }

    /**
     * Get checklist by chamado ID and original checklist ID
     * 
     * @param int $chamado_id
     * @param int $checklist_original_id
     * @return object
     */
    public function get_by_original($chamado_id, $checklist_original_id)
    {
        $this->db->where('chamado_id', $chamado_id);
        $this->db->where('checklist_original_id', $checklist_original_id);
        $this->db->where('status', 1);
        $query = $this->db->get($this->table);

        log_message('error', 'SQL get_by_original: ' . $this->db->last_query());
        log_message('error', 'Resultados: ' . $query->num_rows());

        return $query->row();
    }

    /**
     * Get single checklist by ID
     * 
     * @param int $id
     * @return object
     */
    public function get($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table)->row();
    }

    /**
     * Insert a new checklist association
     * 
     * @param array $data
     * @return int
     */
    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update a checklist association
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * 
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $data = array(
            'status' => 0,
            'data_atualizacao' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * 
     * 
     * @param int $chamado_id
     * @return bool
     */
    public function delete_by_chamado($chamado_id)
    {
        $data = array(
            'status' => 0,
            'data_atualizacao' => date('Y-m-d H:i:s')
        );
        $this->db->where('chamado_id', $chamado_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * 
     * 
     * @param int $chamado_id
     * @return array
     */
    public function get_com_etapas($chamado_id)
    {
        // Busca todos os checklists do chamado
        $checklists = $this->get_by_chamado($chamado_id);

        $this->load->model('chamados_checklists_etapas_model');

        foreach ($checklists as $checklist) {
            // Busca etapas deste checklist com todos os campos necessários
            $checklist->etapas = $this->chamados_checklists_etapas_model->get_by_chamado_checklist_completo($chamado_id, $checklist->id);
        }

        return $checklists;
    }

    /**
     * @param int $chamado_id
     * @return int
     */
    public function count_by_chamado($chamado_id)
    {
        $this->db->where('chamado_id', $chamado_id);
        $this->db->where('status', 1);
        return $this->db->count_all_results($this->table);
    }
}