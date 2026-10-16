<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contatos_model extends CI_Model {

    protected $table = 'contatos';

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

        $query = $this->db->select('c.*, u.unidade, m.descricao as midia_desc, e.descricao as evento_desc');
        $query->join("unidades u", "u.id = c.unidade_id");
        $query->join("eventos e", "e.id = c.evento_id");
        $query->join("midias m", "m.id = e.midia_id");
        $query->where("u.empresa_id", $this->session->userdata('empresa_id'));
        $query->where("u.id IN (".implode(",", $unidades_where).")");
        if($id != null)
            $query->where('c.id', $id);
        if($where != "") {
            $query->where($where);
        }
        $query->order_by("c.id", "desc");
        return $query->get($this->table." c");
    }

    public function get_to_export($data_inicio = "", $data_fim = "", $filter = "", $contatos = "", $unidades = array())
    {
        $encoding = mb_internal_encoding();
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

        $query = $this->db->select('u.unidade, c.nome, c.celular, c.email, c.visualizado, DATE_FORMAT(c.created_at, "%d/%m/%Y") as data, m.descricao as midia_desc, e.descricao as evento_desc, c.cidade, c.estado, c.facebook, c.instagram, c.mensagem, c.idade');
        $query->join("unidades u", "u.id = c.unidade_id");
        $query->join("eventos e", "e.id = c.evento_id");
        $query->join("midias m", "m.id = e.midia_id");
        $query->where("u.empresa_id", $this->session->userdata('empresa_id'));
        $query->where("u.id IN (".implode(",", $unidades_where).")");
        if($data_inicio != "")
            $query->where("c.created_at >= '".$data_inicio."'");
        if($data_fim != "")
            $query->where("c.created_at <= '".$data_fim."'");
        if($filter != "")
            $query->where("(UPPER(e.descricao) LIKE '%".mb_strtoupper($filter, $encoding)."%' OR UPPER(m.descricao) LIKE '%".mb_strtoupper($filter, $encoding)."%' OR UPPER(c.nome) LIKE '%".mb_strtoupper($filter, $encoding)."%' OR c.celular LIKE '%".$filter."%' OR (UPPER(c.cidade) LIKE '%".mb_strtoupper($filter, $encoding)."%')");
        /*if($exportado != "")
            $query->where("exportado", $exportado);*/
        $contatos[] = 0;
        $query->where("c.id IN(".implode(",", $contatos).")");
        $query->order_by("c.id", "desc");
        return $query->get($this->table." c");
    }

    public function get_unidades($empresa_id = null) {
        $query_unids = $this->db->select('u.*');
        $query_unids->join("unidades u", "u.id = uu.unidade_id");
        $query_unids->where("uu.user_id", $this->session->userdata('user_id'));

        if($empresa_id != null) {
            $query_unids->where("u.empresa_id", $empresa_id);
        } else {
            $query_unids->where("u.empresa_id", $this->session->userdata('empresa_id'));
        }

        $query_unids = $query_unids->get("user_unidade uu");

        $unidades = array();
        foreach ($query_unids->result() as $unid) {
            $unidades[$unid->id] = $unid->unidade;
        }
        return $unidades;
    }

    public function get_empresas() {
        $query_emps = $this->db->select('e.*');
        $query_emps->join("empresas e", "e.id = eu.empresa_id");
        $query_emps->where("eu.user_id", $this->session->userdata('user_id'));
        $query_emps = $query_emps->get("empresas_users eu");

        $empresas = array();
        foreach ($query_emps->result() as $emp) {
            $empresas[$emp->id] = $emp->nome;
        }
        return $empresas;
    }

    /**
     * update
     *
     * @return bool
     * @author Ellan Garbelini
     **/
    public function update($id = FALSE, $data = array(), $where = array())
    {
        if($id) {
            $this->db->where('id', $id);
        }
        if($where) {
            $this->db->where($where);
        }
        $this->db->update($this->table, $data);

        return TRUE;
    }

    public function update_exportados($data_inicio = "", $data_fim = "", $filter = "", $unidades = array(), $contatos = array())
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

        $encoding = mb_internal_encoding();
        $where = " WHERE";
        if($data_inicio != "")
            $where .= ' c.created_at >= "'.$data_inicio.'" ';
        if($data_fim != "") {
            if($where != " WHERE") 
                $where .= "AND";
            $where .= ' c.created_at <= "'.$data_fim.'" ';
        }
        if($filter != "") {
            if($where != " WHERE") 
                $where .= "AND";
             $where .= " (UPPER(e.descricao) LIKE '%".strtoupper($filter, $encoding)."%' OR UPPER(m.descricao) LIKE '%".strtoupper($filter, $encoding)."%' OR UPPER(c.nome) LIKE '%".strtoupper($filter, $encoding)."%' OR c.celular LIKE '%".$filter."%') ";
        }
        if($where != " WHERE") 
                $where .= "AND";
        $where .= ' c.exportado = "N" ';

        //if($unidades) {
            if($where != " WHERE") 
                $where .= "AND";
            $where .= " u.id IN (".implode(",", $unidades_where).") ";
        //}

        $contatos[] = 0;
        if($where != " WHERE") 
            $where .= "AND";
        $where .= " c.id IN (".implode(",", $contatos).") ";

        $sql = 
            "UPDATE contatos c " .
            "JOIN unidades u ON u.id = c.unidade_id " .
            "SET c.exportado = 'S', ".
            "c.data_exportacao = '".date('Y-m-d H:i:s')."' ".
            $where;
            
        $this->db->query($sql);
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

    public function get_anexos($contato_id = null) {
        if($contato_id == null)
            return false;

        $query = $this->db->select('caminho, nome');
        $query->where("contato_id", $contato_id);
        $query = $query->get("contatos_anexo");

        return $query;
    }
}
