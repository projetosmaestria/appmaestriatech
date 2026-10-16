<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Empresas_model extends CI_Model {

    protected $table = 'empresas';

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
    public function get($data = array('select'=>'empresas.*', 'order_by'=>array('empresas.id', 'desc')))
    {
        $query = $this->db->select($data['select']);
        if(@$data['join']) {
            foreach ($data['join'] as $join) {
                $query->join($join[0], $join[1]);
            }
        }
        if(@$data['where']) {
            $query->where($data['where']);
        }
        $query->order_by($data['order_by'][0], $data['order_by'][1]);
        return $query->get($this->table);
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($nome = '')
    {
        $this->load->helper('functions_helper');

        if($nome == "")
        {
            $this->set_error('name_required');
            return FALSE;
        }

        // bail if the group name already exists
        $existing = $this->db->get_where($this->table, array('nome' => $nome))->num_rows();
        if($existing !== 0)
        {
            $this->set_error('already_exists');
            return FALSE;
        }

        $data = array('nome'=>$nome,'create_on'=>time());

        if($this->db->insert($this->table, $data)) {
            $id = $this->db->insert_id();

            $this->db->insert('empresas_users', array("empresa_id"=>$id, "user_id"=>$this->session->userdata('user_id')));
            $this->db->insert('groups', array("empresa_id"=>$id, "name"=>"admin", "description"=>"Administrador","bgcolor"=>"#f44336","permissoes"=>serialize(get_all_permissoes())));
            $id_group = $this->db->insert_id();
            $this->db->insert('users_groups', array("group_id"=>$id_group, "user_id"=>$this->session->userdata('user_id')));
            $this->db->insert('admin_preferences', array("grupo_admin"=>$id_group, "empresa_id"=>$id, "empresa_sidebar"=>1, "sidebar_form"=>1, "messages_menu"=>1, "notifications_menu"=>1, "tasks_menu"=>1, "ctrl_sidebar"=>1));
            $this->db->insert('config_email', array("empresa_id"=>$id));
        }

        $this->set_message('creation_successful');

        return $id;
    }

    public function create_email_padrao($data)
    {
        return $this->db->insert("email_padrao", $data);
    }

    /**
     * update
     *
     * @return bool
     * @author Ellan Garbelini
     **/
    public function update($id = FALSE, $nome = '')
    {
        if (empty($id)) return FALSE;

        $data = array();

        if (!empty($nome))
        {
            $existing = $this->db->get_where($this->table, array('nome' => $nome))->row();
            if(isset($existing->id) && $existing->id != $id)
            {
                $this->set_error('already_exists');
                return FALSE;
            }

            $data['nome'] = $nome;
        }

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
