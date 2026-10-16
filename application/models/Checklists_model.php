<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Checklists_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all checklists
     * 
     * @return object
     */
    public function get($id = null)
    {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('checklists')->row();
        }
        $this->db->order_by('data_criacao', 'DESC');
        return $this->db->get('checklists');
    }

    /**
     * Insert a new checklist
     * 
     * @param array $data
     * @return int
     */
    public function insert($data)
    {
        $this->db->insert('checklists', $data);
        return $this->db->insert_id();
    }

    /**
     * Update a checklist
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('checklists', $data);
    }

    /**
     * Delete a checklist
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('checklists');
    }

    /**
     * Count total checklists
     * 
     * @return int
     */
    public function count_all()
    {
        return $this->db->count_all('checklists');
    }

    /**
     * Get checklists with pagination
     * 
     * @param int $limit
     * @param int $offset
     * @return object
     */
    public function get_paginated($limit, $offset)
    {
        $this->db->order_by('data_criacao', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get('checklists')->result();
    }
}