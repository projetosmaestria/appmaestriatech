<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Plano_acao_model extends CI_Model {

    protected $table = 'plano_acao';

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
    public function get($id = null, $where = "", $order = array())
    {
        $query = $this->db->select('p.*, concat(u.first_name, " ", u.last_name) as nome_resp, CASE WHEN p.status IN ("AI") THEN "A Iniciar" WHEN p.status IN ("EA") THEN "Em Andamento" ELSE "Finalizado" END AS status')
            ->join('users u', 'u.id = p.responsavel');
        $query->where("p.empresa_id", $this->session->userdata('empresa_id'));
        if($id != null)
            $query->where('p.id', $id);
        if($where != "") {
            $query->where($where);
        }
        if($order != array()) {
            foreach ($order as $key => $_order) {
                $query->order_by($key, $_order);
            }
        }
        return $query->get($this->table." p");
    }

    public function get_users($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');

        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }
        return $this->db->select('u.*, u.id as user_id')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->join('unidades un', 'uu.unidade_id = un.id')
            ->where('un.id IN ('.implode(",", $unids_array).')')
            ->where('un.empresa_id', $this->session->userdata('empresa_id'))
            ->group_by('u.id')
            ->get("users u");
    }

    public function get_unidades($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');

        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('u.*')
            ->where('u.id IN ('.implode(",", $unids_array).')')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->get("unidades u");

         return $query;
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        if($this->db->insert($this->table, $data)) {
            $id = $this->db->insert_id();
            $this->set_message('creation_successful');
        } else {
            $this->set_error('creation_unsuccessful');
            return FALSE;
        }

        return $id;
    }
    
    /**
     * update
     *
     * @return bool
     * @author Ellan Garbelini
     **/
    public function update($id = FALSE, $data = array())
    {
        if (empty($id)) return FALSE;

        $this->db->update($this->table, $data, array('id' => $id));

        $this->set_message('update_successful');

        return TRUE;
    }

    public function get_apontamentos($plano_id = null)
    {   
        if($plano_id == null)
            return false;

        $query = $this->db->select('a.*');
        $query->join('chamado c', 'c.id = a.chamado_id');
        if($plano_id != null)
            $query->where('c.plano_acao', $plano_id);
        return $query->get("chamado_apontamento a");
    }

    public function get_chamado($where = null)
    {   
        $query = $this->db->select('c.*');
        if($where != null)
            $query->where($where);
        return $query->get("chamado c");
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

    public function get_tarefas($plano_id = null)
    {   
        if($plano_id == null)
            return false;

        $query = $this->db->select('c.*');
        $query->where('c.plano_acao', $plano_id);
        return $query->get("chamado c");
    }

    public function get_relatorio($id = null, $where = "", $order = array())
    {
        $query = $this->db->select('p.*, concat(u.first_name, " ", u.last_name) as nome_resp, CASE WHEN p.status IN ("AI") THEN "A Iniciar" WHEN p.status IN ("EA") THEN "Em Andamento" ELSE "Finalizado" END AS status')
            ->join('users u', 'u.id = p.responsavel');
        if($id != null)
            $query->where('p.id', $id);
        if($where != "") {
            $query->where($where);
        }
        if($order != array()) {
            foreach ($order as $key => $_order) {
                $query->order_by($key, $_order);
            }
        }
        return $query->get($this->table." p");
    }
}
