<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Config_email_model extends CI_Model {

    protected $table = 'email_padrao';

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
    public function get($id = null)
    {
        $query = $this->db->select('*');
        $query->where('empresa_id', $this->session->userdata('empresa_id'));
        if($id != null)
            $query->where('id', $id);
        $query->order_by('assunto', 'asc');
        return $query->get($this->table);
    }

   
    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        $exist = $this->db->get_where($this->table, array('orc_id' => $data["orc_id"]))->num_rows();
        if($exist > 0)
        {
            $this->set_error('exist');
            return FALSE;
        }

        if($this->db->insert($this->table, $data)) {
            $this->set_message('creation_successful');
            $id = $this->db->insert_id();
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
