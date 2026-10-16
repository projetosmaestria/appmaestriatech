<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhooks_model extends CI_Model {

    protected $messages;

    protected $errors;

    public function __construct()
    {
        parent::__construct();
        $this->messages    = array();
        $this->errors      = array();
    }

    /**
     * get_unidade_by_page_id
     *
     * @return object
     * @author Ellan Garbelini
     **/
    public function get_unidade_by_page_id($page_id)
    {
        return $this->db->get_where("unidades", array("fb_page_id" => $page_id, "fb_status_api" => "S", "empresa_id" => $this->session->userdata('empresa_id')));
    }

    /**
     * update_unidade
     *
     * @return bool
     * @author Ellan Garbelini
     **/
    public function update_unidade($id = FALSE, $data = array())
    {
        if (empty($id)) return FALSE;
        $this->db->update("unidades", $data, array('id' => $id));
        return TRUE;
    }

    /**
     * get_anuncio_by_fb_id
     *
     * @return object
     * @author Ellan Garbelini
     **/
    public function get_anuncio_by_fb_id($fb_id)
    {
        $query = $this->db->select('e.*');
        $query->join('midias m', 'm.id = e.midia_id');
        $query->where('e.fb_id', $fb_id);
        $query->where('m.empresa_id', $this->session->userdata('empresa_id'));
        return $query->get('eventos e');
    }

    /**
     * insere_anuncio
     *
     * @return int
     * @author Ellan Garbelini
     **/
    public function insere_anuncio($data = array())
    {
        if($this->db->insert('eventos', $data)) {
            $id = $this->db->insert_id();
        }

        return $id;
    }

    /**
     * insere_lead
     *
     * @return int
     * @author Ellan Garbelini
     **/
    public function insere_lead($data = array())
    {
        $existing = $this->db->get_where('contatos', array('lead_id' => $data["lead_id"], 'unidade_id' => $data["unidade_id"]))->num_rows();
        if($existing > 0)
        {
            return FALSE;
        }

        if($this->db->insert('contatos', $data)) {
            $id = $this->db->insert_id();
        }

        return $id;
    }
}
