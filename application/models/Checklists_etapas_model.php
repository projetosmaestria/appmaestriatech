<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Checklists_etapas_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get etapas by checklist ID
     * 
     * @param int $checklist_id
     * @return object
     */
    public function get_by_checklist($checklist_id)
    {
        $this->db->where('checklist_id', $checklist_id);
        $this->db->where('status', 1);
        $this->db->order_by('ordem', 'ASC');
        return $this->db->get('checklists_etapas')->result();
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
        return $this->db->get('checklists_etapas')->row();
    }

    /**
     * Insert a new etapa
     * 
     * @param array $data
     * @return int
     */
    public function insert($data)
    {
        $this->db->insert('checklists_etapas', $data);
        return $this->db->insert_id();
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
        return $this->db->update('checklists_etapas', $data);
    }

    /**
     * Delete an etapa (soft delete - set status to 0)
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
        return $this->db->update('checklists_etapas', $data);
    }

    /**
     * Hard delete an etapa
     * 
     * @param int $id
     * @return bool
     */
    public function force_delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('checklists_etapas');
    }

    /**
     * Delete all etapas by checklist ID (soft delete)
     * 
     * @param int $checklist_id
     * @return bool
     */
    public function delete_by_checklist($checklist_id)
    {
        $data = array(
            'status' => 0,
            'data_atualizacao' => date('Y-m-d H:i:s')
        );
        $this->db->where('checklist_id', $checklist_id);
        return $this->db->update('checklists_etapas', $data);
    }

    /**
     * Count etapas by checklist ID
     * 
     * @param int $checklist_id
     * @return int
     */
    public function count_by_checklist($checklist_id)
    {
        $this->db->where('checklist_id', $checklist_id);
        $this->db->where('status', 1);
        return $this->db->count_all_results('checklists_etapas');
    }

    /**
     * Update ordem das etapas
     * 
     * @param int $checklist_id
     * @param array $ordens (array of id => ordem)
     * @return bool
     */
    public function update_ordem($checklist_id, $ordens)
    {
        foreach ($ordens as $id => $ordem)
        {
            $data = array(
                'ordem' => $ordem,
                'data_atualizacao' => date('Y-m-d H:i:s')
            );
            $this->db->where('id', $id);
            $this->db->where('checklist_id', $checklist_id);
            $this->db->update('checklists_etapas', $data);
        }
        return true;
    }

    /**
     * Get all etapas
     * 
     * @return object
     */
    public function get_all()
    {
        $this->db->order_by('checklist_id', 'ASC');
        $this->db->order_by('ordem', 'ASC');
        return $this->db->get('checklists_etapas')->result();
    }

    /**
     * Get etapas by status
     * 
     * @param int $status
     * @return object
     */
    public function get_by_status($status = 1)
    {
        $this->db->where('status', $status);
        $this->db->order_by('checklist_id', 'ASC');
        $this->db->order_by('ordem', 'ASC');
        return $this->db->get('checklists_etapas')->result();
    }

    /**
     * Get etapas with prazo_padrao defined
     * 
     * @param int $checklist_id
     * @return object
     */
    public function get_with_prazo_padrao($checklist_id = null)
    {
        $this->db->where('prazo_padrao IS NOT NULL');
        $this->db->where('prazo_padrao >', 0);
        $this->db->where('status', 1);
        
        if ($checklist_id) {
            $this->db->where('checklist_id', $checklist_id);
        }
        
        $this->db->order_by('checklist_id', 'ASC');
        $this->db->order_by('ordem', 'ASC');
        return $this->db->get('checklists_etapas')->result();
    }

    /**
     * Update prazo_padrao for an etapa
     * 
     * @param int $id
     * @param int $prazo_padrao
     * @return bool
     */
    public function update_prazo_padrao($id, $prazo_padrao)
    {
        $data = array(
            'prazo_padrao' => $prazo_padrao,
            'data_atualizacao' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $id);
        return $this->db->update('checklists_etapas', $data);
    }

    /**
     * Get average prazo_padrao for a checklist
     * 
     * @param int $checklist_id
     * @return float
     */
    public function get_avg_prazo_padrao($checklist_id)
    {
        $this->db->select_avg('prazo_padrao');
        $this->db->where('checklist_id', $checklist_id);
        $this->db->where('status', 1);
        $this->db->where('prazo_padrao IS NOT NULL');
        $query = $this->db->get('checklists_etapas');
        $result = $query->row();
        return $result ? (float)$result->prazo_padrao : 0;
    }

    /**
     * Get total prazo_padrao for a checklist (soma dos prazos)
     * 
     * @param int $checklist_id
     * @return int
     */
    public function get_total_prazo_padrao($checklist_id)
    {
        $this->db->select_sum('prazo_padrao');
        $this->db->where('checklist_id', $checklist_id);
        $this->db->where('status', 1);
        $this->db->where('prazo_padrao IS NOT NULL');
        $query = $this->db->get('checklists_etapas');
        $result = $query->row();
        return $result ? (int)$result->prazo_padrao : 0;
    }

    /**
     * Validate if prazo_padrao is integer
     * 
     * @param int $prazo_padrao
     * @return bool
     */
    public function validate_prazo_padrao($prazo_padrao)
    {
        if ($prazo_padrao === null || $prazo_padrao === '') {
            return true; // Campo opcional
        }
        return ctype_digit((string)$prazo_padrao) && intval($prazo_padrao) >= 0;
    }
}