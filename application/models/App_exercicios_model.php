<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_exercicios_model extends CI_Model {

    protected $table = 'exercicios';

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
    public function get($id = null, $livro_id = "", $tipo = "", $unidade = "")
    {
        $query = $this->db->select('e.*, ul.tipo, ul.unidade as ordem_unidade, ul.titulo as titulo_unidade, l.titulo as titulo_livro')
            ->join('unidade_livro ul', 'ul.id = e.unidade')
            ->join('livros l', 'l.id = e.livro')
            ->where('l.empresa', $this->session->userdata('empresa_id'));
        if($id != null) {
            $query->where('e.id', $id);
        }
        if($livro_id != "") {
            $query->where('l.id', $livro_id);
        }
        if($tipo != "") {
            $query->where('ul.tipo', $tipo);
        }
        if($unidade != "") {
            $query->where('e.unidade', $unidade);
        }
        return $query->get($this->table." e");  
    }

    public function get_livros()
    {
        $query = $this->db->select('*')
            ->where('empresa', $this->session->userdata('empresa_id'));
        return $query->get('livros');  
    }

    public function get_unidades_livro($livro)
    {
        $query = $this->db->select('*')
            ->where('livro', $livro)
            ->where('tipo', 'O');

        return $query->get('unidade_livro');  
    }

    public function get_perguntas($id)
    {
        $query = $this->db->select('*')
            ->where('exercicio', $id);
        return $query->get('pergunta_exercicio');
    }

    public function create_pergunta($data = array())
    {
        $this->db->insert("pergunta_exercicio", $data);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_pergunta($id = FALSE, $data = array())
    {
        if (empty($id)) return FALSE;

        $this->db->update("pergunta_exercicio", $data, array('id' => $id));

        return TRUE;
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

        // $this->set_message('update_successful');

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
            'ativo' => 'S'
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
            'ativo' => 'N'
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
