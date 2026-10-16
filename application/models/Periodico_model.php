<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Periodico_model extends CI_Model {

    protected $table = 'editora_periodico';

    protected $messages;

    protected $errors;

    public function __construct()
    {
        parent::__construct();
        $this->messages    = array();
        $this->errors      = array();
    }

    /**
     * get
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get($id = null, $unidade_id = null)
    {
        $unids_array = array(0);

        if($unidade_id == null) {
            $unidades = $this->db->get_where('user_unidade', array('user_id'=>$this->session->userdata('user_id')))->result();
            foreach ($unidades as $unid) {
                $unids_array[] = $unid->unidade_id;
            }
        } else {
            $unids_array[] = $unidade_id;
        }

        /*$query = $this->db->select("i.*, p.nomePeriodico, u.unidade");
        $query->join('editora_periodico p', 'p.id = i.periodico_id');
        $query->join('unidades u', 'u.id = p.unidade_id');
        $query->where('u.id IN ('.implode(",", $unids_array).')');
        $query->where('u.empresa_id', $this->session->userdata('empresa_id'));
        
        if($id != null)
            $query->where('p.id', $id);
        
        $query->order_by("i.created_at", "desc");
        return $query->get($this->table." i");*/

        $query = $this->db->select("p.*,u.unidade, pe.periodicidade, u.iugu_status, u.iugu_token");
        $query->join('editora_periodicidade pe', 'pe.id = p.id_periodicidade');
        $query->join('unidades u', 'u.id = p.unidade_id');
        $query->where('u.id IN ('.implode(",", $unids_array).')');
        $query->where('u.empresa_id', $this->session->userdata('empresa_id'));
        
        if($id != null)
            $query->where('p.id', $id);
        
        return $query->get($this->table." p");
    }

    public function getIuguPlanPeriodico($periodico_id = null, $plano_id = null)
    {
        $query = $this->db->select("p.*");
        if($periodico_id != null)
            $query->where('p.periodico_id', $periodico_id);
        if($plano_id != null)
            $query->where('p.plano', $plano_id);
        
        return $query->get("iugu_plan_periodico p");
    }


    /**
     * update
     *
     * @return bool
     * @author Ellan Garbelini
     **/
    public function createByPeriodico($id, $planos = array())
    {
        if (empty($id)) return FALSE;

        $this->db->delete('iugu_plan_periodico', array("periodico_id" => $id));
        
        foreach ($planos as $plano) {
            //$exist = $this->db->get_where('iugu_plan_periodico', array("periodico_id" => $id, "plano" => $plano));
            //if($exist->num_rows() == 0)
                $this->db->insert("iugu_plan_periodico", array("periodico_id" => $id, "plano" => $plano));
        }

        $this->set_message('update_successful');

        return TRUE;
    }

    public function createByPlano($plano, $ids = array())
    {
        if (empty($plano)) return FALSE;

        $this->db->delete('iugu_plan_periodico', array("plano" => $plano));
        
        foreach ($ids as $id) {
            //$exist = $this->db->get_where('iugu_plan_periodico', array("periodico_id" => $id, "plano" => $plano));
            //if($exist->num_rows() == 0)
                $this->db->insert("iugu_plan_periodico", array("periodico_id" => $id, "plano" => $plano));
        }

        $this->set_message('update_successful');

        return TRUE;
    }


    public function set_message($message)
    {
        $this->messages[] = $message;

        return $message;
    }

    public function messages()
    {
        $_output = '';
        foreach ($this->messages as $message)
        {
            $messageLang = $this->lang->line($message) ? $this->lang->line($message) : '##' . $message . '##';
            $_output .= $messageLang;
        }

        return $_output;
    }

    public function set_error($error)
    {
        $this->errors[] = $error;

        return $error;
    }

    public function errors()
    {
        $_output = '';
        foreach ($this->errors as $error)
        {
            $errorLang = $this->lang->line($error) ? $this->lang->line($error) : '##' . $error . '##';
            $_output .= $errorLang;
        }

        return $_output;
    }


    public function get_iugu_plano($plano) {
        $return = $this->db->get_where('iugu_plan', array("plano" => $plano))->row();
        return $return;
    }

    public function insert_iugu_plan($data) {
        $return = $this->db->get_where('iugu_plan', array("plano" => $data["plano"]));
        if($return->num_rows() == 0)
            $this->db->insert("iugu_plan", $data);
        else
            $this->db->update("iugu_plan", $data, array('plano' => $data["plano"]));
        return true;
    }

}
