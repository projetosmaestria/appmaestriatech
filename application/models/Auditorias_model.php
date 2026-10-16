<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditorias_model extends CI_Model {

    protected $table = 'auditorias';

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
    public function get()
    {
        $query = $this->db->select('a.*, u.first_name, u.last_name');
        $query->join('users u', 'u.id = a.user_id');
        $query->where("a.empresa_id", $this->session->userdata('empresa_id'));
        $query->order_by("a.id", "desc");
        return $query->get($this->table.' a');
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($controlador, $metodo, $obs)
    {
        if($controlador == "")
        {
            $this->set_error('controlador_required');
            return FALSE;
        }
        if($metodo == "")
        {
            $this->set_error('metodo_required');
            return FALSE;
        }

        $this->db->insert($this->table, array("controlador"=>$controlador,"metodo"=>$metodo,"obs"=>$obs,"empresa_id"=>$this->session->userdata("empresa_id"),"user_id"=>$this->session->userdata("user_id")));
        $id = $this->db->insert_id();

        $this->set_message('creation_successful');

        return $id;
    }

    public function historico_create($data = array())
    {
        $data["empresa_id"] = $this->session->userdata('empresa_id');
        if($this->db->insert('cad_clie_auditoria', $data)) {
            $id = $this->db->insert_id();
            $this->set_message('creation_historico_successful');
        } else {
            $this->set_error('creation_historico_unsuccessful');
            return FALSE;
        }
        return $id;
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
