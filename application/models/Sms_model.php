<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_model extends CI_Model {

    protected $table = 'envios_sms';

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
    public function get($id = null, $where = "", $unidades = array())
    {
        $query_unids = $this->db->select('uu.*');
        $query_unids->join("unidades u", "u.id = uu.unidade_id");
        $query_unids->where("u.empresa_id", $this->session->userdata('empresa_id'));
        $query_unids->where("uu.user_id", $this->session->userdata('user_id'));
        $query_unids = $query_unids->get("user_unidade uu");

        $unidades_session = array();
        foreach ($query_unids->result() as $unid) {
            $unidades_session[] = $unid->unidade_id;
        }
        if($unidades) {
            $unidades_where = array_intersect($unidades, $unidades_session);
        } else {
            $unidades_where = $unidades_session;
        }

        $query = $this->db->select('s.*, u.unidade');
        $query->join("unidades u", "u.id = s.unidade_id");
        $query->where("u.empresa_id", $this->session->userdata('empresa_id'));
        $query->where("u.id IN (".implode(",", $unidades_where).")");
        if($id != null)
            $query->where('s.id', $id);
        if($where != "") {
            $query->where($where);
        }
        $query->order_by("s.id", "desc");
        return $query->get($this->table." s");
    }

    public function get_unidades() {
        $query_unids = $this->db->select('u.*');
        $query_unids->join("unidades u", "u.id = uu.unidade_id");
        $query_unids->where("uu.user_id", $this->session->userdata('user_id'));
        $query_unids->where("u.empresa_id", $this->session->userdata('empresa_id'));
        $query_unids = $query_unids->get("user_unidade uu");

        $unidades = array();
        foreach ($query_unids->result() as $unid) {
            $unidades[$unid->id] = $unid->unidade;
        }
        return $unidades;
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
}
