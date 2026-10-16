<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setores_model extends CI_Model {

    protected $table = 'setores';

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
    public function get($data = array('select'=>'setores.*', 'order_by'=>array('setores.id', 'desc')))
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
    public function create($data = array())
    {
        if($data["descricao"] == "")
        {
            $this->set_error('descricao_required');
            return FALSE;
        }
        if($data["actived"] == "")
        {
            $this->set_error('status_required');
            return FALSE;
        }

        $existing_empresa = $this->db->get_where('empresas', array('id' => $data["empresa_id"]))->num_rows();
        if($existing_empresa == 0)
        {
            $this->set_error('empresa_not_exist');
            return FALSE;
        }

        $existing = $this->db->get_where($this->table, array('descricao' => $data["descricao"], 'empresa_id' => $data["empresa_id"]))->num_rows();
        if($existing !== 0)
        {
            $this->set_error('already_exists');
            return FALSE;
        }

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

        $existing = $this->db->get_where($this->table, array('descricao' => $data["descricao"], 'empresa_id' => $data["empresa_id"]))->row();
        if(isset($existing->id) && $existing->id != $id)
        {
            $this->set_error('already_exists');
            return FALSE;
        }

        $existing_empresa = $this->db->get_where('empresas', array('id' => $data["empresa_id"]))->num_rows();
        if($existing_empresa == 0)
        {
            $this->set_error('empresa_not_exist');
            return FALSE;
        }

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
            'actived' => '1'
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
            'actived' => '0'
        );

        $this->db->update($this->table, $data, array('id' => $id));

        $return = $this->db->affected_rows() == 1;
        if ($return)
            $this->set_message('deactivate_successful');
        else
            $this->set_error('deactivate_unsuccessful');

        return $return;
    }

    /**
     * get_empresas
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_empresas()
    {
        $query = $this->db->select('*');
        $query->order_by('nome', 'asc');
        return $query->get('empresas');
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
