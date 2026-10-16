<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contatos_interno_model extends CI_Model {

    protected $table = 'contatos_interno';

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
    public function get($id = null, $where = "", $limit = null)
    {
        $query = $this->db->select('c.*, u.first_name, u.last_name, DATE_FORMAT(c.created_at, "%d/%m/%Y %H:%i:%s") as data_envio');
        $query->join("users u", "u.id = c.user_id_de");
        $query->where("c.user_id_para = '".$this->session->userdata('user_id')."'");
        if($where != "")
            $query->where($where);
        if($id != null)
            $query->where('c.id', $id);
        $query->order_by("c.id", "desc");
        if($limit != null)
            $query->limit($limit);
        return $query->get($this->table." c");
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array()) {
        if($this->db->insert($this->table, $data)) {
            $this->set_message('creation_successful');
            return true;
        }
        else {
            $this->set_message('creation_unsuccessful');
            return false;
        }
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
}
