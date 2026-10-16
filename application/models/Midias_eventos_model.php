<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Midias_eventos_model extends CI_Model {

    protected $table = 'eventos';

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
    public function get($data = array('select'=>'e.*', 'order_by'=>array('e.id', 'desc')))
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
        return $query->get($this->table." e");
    }

    public function get_midias() {
        return $this->db->get_where("midias", array("empresa_id" => $this->session->userdata('empresa_id')));
    }

    public function get_unidades() {
        $query_unids = $this->db->select('u.*');
        $query_unids->join("unidades u", "u.id = uu.unidade_id");
        $query_unids->where("uu.user_id", $this->session->userdata('user_id'));
        $query_unids->where("u.empresa_id", $this->session->userdata('empresa_id'));
        $query_unids->order_by("u.unidade", "asc");
        $query_unids = $query_unids->get("user_unidade uu");

        return $query_unids;
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        $existing_midia = $this->db->get_where('midias', array('id' => $data["midia_id"]))->num_rows();
        if($existing_midia == 0)
        {
            $this->set_error('midia_not_exist');
            return FALSE;
        }

        if($this->db->insert($this->table, $data)) {
            $this->set_message('creation_evento_successful');
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

    public function activate($id = NULL)
    {
        if (!isset($id))
        {
            $this->set_error('activate_unsuccessful');
            return FALSE;
        }

        $data = array(
            'status' => '1'
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
            'status' => '0'
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

    public function get_anuncios($evento_id) {
        return $this->db->get_where("eventos_anuncios", array("evento_id" => $evento_id));
    }

    public function delete_anuncio($fb_id, $evento_id) {
        if($this->db->delete("eventos_anuncios", array('fb_id' => $fb_id, 'evento_id' => $evento_id))) {
            //$this->set_message('update_successful');
            return true;
        } else {
            //$this->set_message('update_successful');
            return false;
        }
    }

    public function exists_in_evento($fb_id, $evento_id) {
        $query = $this->db->get_where("eventos_anuncios", array('fb_id' => $fb_id, 'evento_id' => $evento_id));
        if($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function create_anuncio($fb_id, $evento_id)
    {
        $this->db->insert("eventos_anuncios", array('fb_id'=>$fb_id, 'evento_id'=>$evento_id));
        $id = $this->db->insert_id();
        return $id;
    }
}
