<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Materiais_model extends CI_Model {

    protected $table = 'materiais';

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
    $query = $this->db->select('m.*, DATE_FORMAT(m.created_at, "%d/%m/%Y") as data_criacao, l.titulo as titulo_livro, ul.titulo as titulo_unidade')
                      ->from('materiais as m')
                      ->join('livros as l', 'l.id = m.livro', 'left')
                      ->join('unidade_livro as ul', 'ul.id = m.unidade', 'left')
                      ->where('m.empresa_id', $this->session->userdata('empresa_id'));

    if($id != null) {
        $query->where('m.id', $id);
    }

    return $query->get();
 }

    public function get_livros()
    {
        $query = $this->db->select('*')
            ->where('empresa', $this->session->userdata('empresa_id'));
        return $query->get('livros');  
    }

    public function get_unidades_livro($livro, $tipo = "")
    {
        $query = $this->db->select('*')
            ->where('livro', $livro);

        if($tipo != "") {
            $query->where('tipo', $tipo);
        }

        return $query->get('unidade_livro');  
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();

        $this->set_message('creation_successful');

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

    public function activate($id = NULL)
    {
        if (!isset($id))
        {
            $this->set_error('activate_unsuccessful');
            return FALSE;
        }

        $data = array(
            'actived' => 'S'
        );

        $this->db->update($this->table, $data, array('id' => $id));

        $return = $this->db->affected_rows() == 1;
        if ($return){
            $this->set_message('activate_successful');
        } else{
            $this->set_error('activate_unsuccessful');
        }
        return $return;
    }


    public function deactivate($id = NULL)
    {
        if (!isset($id))
        {
            $this->set_error('deactivate_unsuccessful');
            return FALSE;
        }

        $data = array(
            'actived' => 'N'
        );

        $this->db->update($this->table, $data, array('id' => $id));

        $return = $this->db->affected_rows() == 1;
        if ($return)
            $this->set_message('deactivate_successful');
        else
            $this->set_error('deactivate_unsuccessful');

        return $return;
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

    public function get_groups($user_id = null) {
        $query = $this->db->select('g.*');
        if($user_id != null) {
            $query->join('users_groups ug', 'ug.group_id = g.id');
            $query->where('ug.user_id', $user_id);
        }
        $query->where('g.empresa_id', $this->session->userdata('empresa_id'));
        $query = $query->get('groups g');

        if ($query->num_rows() > 0)
        {
            $return_array = [];
            foreach ($query->result() as $group) {
                $return_array[$group->id] = $group->description;
            }
            return $return_array;
        }
        else
        {
            return FALSE;
        }
    }

    public function get_groups_by_in($ids = null) {
        if($ids == null) {
            return false;
        }
        $query = $this->db->select('*');
        $query->where('id IN ('.$ids.')');
        $query = $query->get('groups');

        return $query->result();
    }
}
