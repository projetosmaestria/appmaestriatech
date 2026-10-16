<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_podcasts_model extends CI_Model {

    protected $table = 'podcasts';

    protected $messages;

    protected $errors;

    public function __construct()
    {
        parent::__construct();
        $this->messages    = array();
        $this->errors      = array();
        //$this->load->helper('functions_helper');
    }

    /**
     * get
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get($id = null, $where = "")
    {
        $query = $this->db->select('p.id, p.livro, l.titulo as livro_titulo, p.tipo, p.titulo, p.texto, p.caminho_midia, p.titulo_midia, p.caminho_img, p.titulo_imagem, p.data_publicacao, p.data_atualizacao, p.publicacao, p.ativo, p.notificado, p.data_notificacao');
        $query->join('livros l', 'p.livro = l.id');
        if($id != null) {
            $query->where('p.id', $id);
        }
        $query->where('l.empresa', $this->session->userdata('empresa_id'));
        if($where != "") {
            $query->where($where);            
        }
        $query->order_by('p.id, p.livro, p.titulo');
        return $query->get($this->table.' p');
    }

    /**
     * get_livro
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_livro($id = null)
    {
        $query = $this->db->select('*');
        if($id != null) {
            $query->where('id', $id);
        }
        $query->where('empresa', $this->session->userdata('empresa_id'));
        $query->order_by('titulo asc');
        return $query->get('livros');
    }

    /**
     * get_unidades_livro
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_unidades_livro($livro = null)
    {
        $query = $this->db->select('*');
        if($livro != null) {
            $query->where('livro', $livro);
        }
        $query->where('ativo', 'S');
        $query->where('tipo', 'O');
        $query->order_by('unidade, titulo');
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
