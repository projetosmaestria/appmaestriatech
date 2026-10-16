<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Disciplinas_model extends CI_Model {

    protected $table = 'disciplina';

    protected $messages;

    protected $errors;

    public function __construct()
    {
        parent::__construct();
        $this->messages    = array();
        $this->errors      = array();
        $this->load->helper('functions_helper');
    }

    /**
     * get
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get($id = null)
    {
        $query = $this->db->select('d.*')
        // $query = $this->db->select('d.*, d.DESCRICAO as disciplina_desc, c.DESCRICAO as curso_desc, p.DESCRICAO as pacote_desc, ps.DESCRICAO as pacotesub_desc')
            // ->join('curso_disciplina cd', 'cd.LIG_ID_DISCIPLINA = d.ID_DISCIPLINA', 'left')
            // ->join('curso c', 'cd.LIG_ID_CURSO = c.ID_CURSO', 'left')
            // ->join('pacote p', 'cd.LIG_ID_PACOTE = p.ID_PACOTE', 'left')
            // ->join('pacote_sub ps', 'ps.LIG_ID_PACOTE = p.ID_PACOTE', 'left')
            ->where('d.empresa_id', $this->session->userdata('empresa_id'));
        if($id != null) {
            $query->where('d.ID_DISCIPLINA', $id);
        }
        return $query->get($this->table." d");  
    }

    /**
     * get_cursos
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_cursos()
    {
        $query = $this->db->select('*');
        $query->where('empresa_id', $this->session->userdata('empresa_id'));
        return $query->get('curso');
    }

    /**
     * get_vinculos
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_vinculos($id)
    {
        $query = $this->db->select('cd.*, c.DESCRICAO as curso_desc, p.DESCRICAO as pacote_desc, ps.DESCRICAO as pacotesub_desc');
        $query->join('curso c', 'cd.LIG_ID_CURSO = c.ID_CURSO');
        $query->join('pacote p', 'cd.LIG_ID_PACOTE = p.ID_PACOTE');
        $query->join('pacote_sub ps', 'cd.LIG_ID_PACOTESUB = ps.ID_PACOTE_SUB');
        $query->where('cd.LIG_ID_DISCIPLINA', $id);
        return $query->get('curso_disciplina cd');
    }

    /**
     * get_vinculo_by_id
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_vinculo_by_id($id)
    {
        $query = $this->db->select('cd.*, c.DESCRICAO as curso_desc, p.DESCRICAO as pacote_desc, ps.DESCRICAO as pacotesub_desc');
        $query->join('curso c', 'cd.LIG_ID_CURSO = c.ID_CURSO');
        $query->join('pacote p', 'cd.LIG_ID_PACOTE = p.ID_PACOTE');
        $query->join('pacote_sub ps', 'cd.LIG_ID_PACOTESUB = ps.ID_PACOTE_SUB');
        $query->where('cd.ID_CONTROLE', $id);
        return $query->get('curso_disciplina cd');
    }

    /**
     * get_pacotes
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_pacotes()
    {
        $query = $this->db->select('*');
        $query->where('empresa_id', $this->session->userdata('empresa_id'));
        return $query->get('pacote');
    }


    /**
     * get_pacotessub
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_pacotessub($id)
    {
        $query = $this->db->select('*');
        $query->where('LIG_ID_PACOTE', $id);
        return $query->get('pacote_sub');
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

        $this->db->update($this->table, $data, array('ID_DISCIPLINA' => $id));

        $this->set_message('update_successful');

        return TRUE;
    }

    public function create_vinculo($data = array())
    {
        $this->db->insert("curso_disciplina", $data);
        $id = $this->db->insert_id();

        $this->set_message('creation_vinculo_successful');

        return $id;
    }

    public function remove_vinculo($id)
    {
        $this->db->delete("curso_disciplina", array("ID_CONTROLE" => $id));
        $this->set_message('remove_vinculo_successful');
        return true;
    }

    public function activate($id = NULL)
    {
        if (!isset($id))
        {
            $this->set_error('activate_unsuccessful');
            return FALSE;
        }

        $data = array(
            'situacao' => 'True'
        );

        $this->db->update($this->table, $data, array('ID_DISCIPLINA' => $id));

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
            'situacao' => 'False'
        );

        $this->db->update($this->table, $data, array('ID_DISCIPLINA' => $id));

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
            $messageLang = $this->lang->line($message) ? $this->lang->line($message) : $message;
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
