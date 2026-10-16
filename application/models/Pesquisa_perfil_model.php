<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pesquisa_perfil_model extends CI_Model {

    protected $table = 'pesquisa_perfil';

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
        $query = $this->db->select('p.*, p.id as pesquisa_id, u.*, CONCAT(u.first_name, " ", u.last_name) as cliente, DATE_FORMAT(p.created_at, "%d/%m/%Y %H:%i:%s") as data_formatada, cs.DESCRICAO as curso');
        $query->join('cad_orca c', 'c.id = p.orc_id');
        $query->join('unidades un', 'c.filial = un.id');
        $query->join('users u', 'u.id = c.user_id');
        $query->join('curso cs', 'cs.ID_CURSO = c.lig_id_curso');
        $query->where('un.empresa_id', $this->session->userdata('empresa_id'));
        if($id != null) {
            $query->where('p.id', $id);
        }
        $query->order_by('p.created_at', 'desc');
        return $query->get($this->table.' p');
    }

    public function get_contratos($where = "") {
        $query = $this->db->select('c.*, CONCAT(u.first_name, " ", u.last_name) as cliente');
        $query->join('unidades un', 'c.filial = un.id');
        $query->join('users u', 'u.id = c.user_id');
        $query->join('curso cs', 'cs.ID_CURSO = c.lig_id_curso');
        $query->where('un.empresa_id', $this->session->userdata('empresa_id'));
        if($where != "") {
            $query->where("c.id NOT IN (".$where.")");
        }
        return $query->get('cad_orca c');
    }

    public function get_contratos_ids() {
        $query = $this->db->select('p.orc_id');
        $query->join('cad_orca c', 'c.id = p.orc_id');
        $query->join('unidades un', 'c.filial = un.id');
        $query->join('users u', 'u.id = c.user_id');
        $query->where('un.empresa_id', $this->session->userdata('empresa_id'));
        $query->distinct();
        return $query->get($this->table.' p');
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
