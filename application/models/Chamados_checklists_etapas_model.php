<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chamados_checklists_etapas_model extends CI_Model {

    protected $table = 'chamados_checklists_etapas';

    public function __construct()
    {
        parent::__construct();
    }

    /**
 * Get etapas by chamado checklist ID com todos os campos
 * 
 * @param int $chamado_id
 * @param int $chamado_checklist_id
 * @return array
 */
public function get_by_chamado_checklist_completo($chamado_id, $chamado_checklist_id = null)
{
    $this->db->select('ce.*, u.first_name as responsavel_first_name, u.last_name as responsavel_last_name');
    $this->db->from($this->table . ' ce');
    $this->db->where('ce.chamado_id', $chamado_id);
    $this->db->where('ce.status', 1);
    
    if ($chamado_checklist_id !== null) {
        $this->db->where('ce.chamado_checklist_id', $chamado_checklist_id);
    }
    
    // Left join com users para pegar nome do responsável (usando o campo 'responsavel')
    $this->db->join('users u', 'u.id = ce.responsavel', 'left');
    
    $this->db->order_by('ce.ordem', 'ASC');
    
    $query = $this->db->get();
    
    return $query->result();
}

/**
 * Get etapas by chamado checklist ID (método original modificado)
 * 
 * @param int $chamado_id
 * @param int $chamado_checklist_id
 * @return object
 */
public function get_by_chamado_checklist($chamado_id, $chamado_checklist_id = null)
{
    $this->db->select('ce.*, u.first_name as responsavel_first_name, u.last_name as responsavel_last_name');
    $this->db->from($this->table . ' ce');
    $this->db->where('ce.chamado_id', $chamado_id);
    $this->db->where('ce.status', 1);
    
    if ($chamado_checklist_id !== null) {
        $this->db->where('ce.chamado_checklist_id', $chamado_checklist_id);
    }
    
    $this->db->join('users u', 'u.id = ce.responsavel', 'left');
    $this->db->order_by('ce.ordem', 'ASC');
    
    return $this->db->get()->result();
}

    /**
     * Get single etapa by ID
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
     * Insert a new etapa
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
     * Insert multiple etapas
     * 
     * @param array $etapas
     * @return bool
     */
    public function insert_batch($etapas)
    {
        return $this->db->insert_batch($this->table, $etapas);
    }

    /**
     * Update an etapa
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
     * Delete an etapa (soft delete)
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
     * Delete all etapas by chamado checklist ID (soft delete)
     * 
     * @param int $chamado_id
     * @param int $chamado_checklist_id
     * @return bool
     */
    public function delete_by_chamado_checklist($chamado_id, $chamado_checklist_id)
    {
        $data = array(
            'status' => 0,
            'data_atualizacao' => date('Y-m-d H:i:s')
        );
        $this->db->where('chamado_id', $chamado_id);
        $this->db->where('chamado_checklist_id', $chamado_checklist_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Count etapas by chamado checklist ID
     * 
     * @param int $chamado_id
     * @param int $chamado_checklist_id
     * @return int
     */
    public function count_by_chamado_checklist($chamado_id, $chamado_checklist_id)
    {
        $this->db->where('chamado_id', $chamado_id);
        $this->db->where('chamado_checklist_id', $chamado_checklist_id);
        $this->db->where('status', 1);
        return $this->db->count_all_results($this->table);
    }

    /**
     * Concluir uma etapa
     * 
     * @param int $id
     * @return bool
     */
    public function concluir($id)
    {
        $data = array(
            'concluida' => 1,
            'data_conclusao' => date('Y-m-d H:i:s'),
            'data_atualizacao' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Reabrir uma etapa
     * 
     * @param int $id
     * @return bool
     */
    public function reabrir($id)
    {
        $data = array(
            'concluida' => 0,
            'data_conclusao' => null,
            'data_atualizacao' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Verificar se todas as etapas de um checklist estão concluídas
     * 
     * @param int $chamado_id
     * @param int $chamado_checklist_id
     * @return bool
     */
    public function todas_concluidas($chamado_id, $chamado_checklist_id)
    {
        $total = $this->count_by_chamado_checklist($chamado_id, $chamado_checklist_id);
        
        $this->db->where('chamado_id', $chamado_id);
        $this->db->where('chamado_checklist_id', $chamado_checklist_id);
        $this->db->where('concluida', 1);
        $this->db->where('status', 1);
        $concluidas = $this->db->count_all_results($this->table);
        
        return ($total > 0 && $total == $concluidas);
    }
}