<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Condicoes_orcamento_model extends CI_Model {

    protected $table = 'tipo_condicao_parcela_orcamento';

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
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$this->session->userdata('user_id')))->result();
        $unids_array = array(0);
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select("c.*, f.DESCRICAO as forma_pag, f.NUM_PARCELAS, r.NOME as tipo_receb, u.unidade, u.iugu_status, u.iugu_token");
        $query->join('cad_fpgt f', 'f.CODIGO = c.Id_formpag');
        $query->join('tip_rece r', 'r.CODIGO = c.Id_TipoReceb');
        $query->join('unidades u', 'u.id = c.unidade_id');
        $query->where('u.id IN ('.implode(",", $unids_array).')');
        
        if($id != null)
            $query->where('c.ID', $id);
        
        $query->order_by("c.DESCRICAO", "asc");
        return $query->get($this->table." c");
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

        $this->db->update($this->table, $data, array('ID' => $id));

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
            'ativo' => '1'
        );

        $this->db->update($this->table, $data, array('ID' => $id));

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
            'ativo' => '0'
        );

        $this->db->update($this->table, $data, array('ID' => $id));

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

}
