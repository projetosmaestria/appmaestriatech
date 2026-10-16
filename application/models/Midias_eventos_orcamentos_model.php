<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Midias_eventos_orcamentos_model extends CI_Model {

    protected $table = 'eventos_orcamentos';

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
    public function get($id = null, $where = "")
    {
        $query_unids = $this->db->select('uu.*');
        $query_unids->join("unidades u", "u.id = uu.unidade_id");
        $query_unids->where("u.empresa_id", $this->session->userdata('empresa_id'));
        $query_unids->where("uu.user_id", $this->session->userdata('user_id'));
        $query_unids = $query_unids->get("user_unidade uu");

        $unidades_where = array();
        foreach ($query_unids->result() as $unid) {
            $unidades_where[] = $unid->unidade_id;
        }

        $query = $this->db->select('eo.*, u.unidade, m.descricao as midia_desc, e.descricao as evento_desc, e.id as evento_id, DATE_FORMAT(data, "%d/%m/%Y") as data_formatada');
        $query->join("eventos e", "e.fb_id = eo.adset");
        $query->join("midias m", "m.id = e.midia_id");
        $query->join("unidades u", "u.id = e.unidade_id");
        $query->where("u.empresa_id", $this->session->userdata('empresa_id'));
        $query->where("u.id IN (".implode(",", $unidades_where).")");
        if($id != null)
            $query->where('eo.id', $id);
        if($where != "") {
            $query->where($where);
        }
        $query->order_by("eo.data", "desc");
        return $query->get($this->table." eo");
    }

    public function get_unidades() {
        $query_unids = $this->db->select('u.*');
        $query_unids->join("unidades u", "u.id = uu.unidade_id");
        $query_unids->where("uu.user_id", $this->session->userdata('user_id'));
        $query_unids->where("u.empresa_id", $this->session->userdata('empresa_id'));
        $query_unids = $query_unids->get("user_unidade uu");

        $unidades = array(""=>"Selecione");
        foreach ($query_unids->result() as $unid) {
            $unidades[$unid->id] = $unid->unidade;
        }
        return $unidades;
    }

    public function get_midias() {
        $query_midias = $this->db->select('m.*');
        $query_midias->where("m.empresa_id", $this->session->userdata('empresa_id'));
        $query_midias = $query_midias->get("midias m");

        $midias = array(""=>"Selecione");
        foreach ($query_midias->result() as $mid) {
            $midias[$mid->id] = $mid->descricao;
        }
        return $midias;
    }

    public function get_eventos($unidade) {
        $query_eventos = $this->db->select('e.*');
        $query_eventos->where("e.unidade_id", $unidade);
        $query_eventos = $query_eventos->get("eventos e");

        $eventos = array(""=>"Selecione");
        foreach ($query_eventos->result() as $eve) {
            $eventos[$eve->id] = $eve->descricao;
        }
        return $eventos;
    }

    public function get_qtd_leads($evento_id, $where) {
        $query = $this->db->select('*');
        $query->where("evento_id", $evento_id);
        $query->where($where);
        $query = $query->get("contatos");

        return $query->num_rows();
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
