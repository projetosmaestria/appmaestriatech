<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Ead_tutorias_model extends CI_Model {

    protected $table = 'tutoria';

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
    public function get($id = NULL, $where = "", $join = NULL)
    {
        $query = $this->db->select('t.*, CONCAT(u.first_name, " ", u.last_name) as nome_tutor, CONCAT(us.first_name, " ", us.last_name) as nome_cliente');
        $query->join('users u', 'u.id = t.tutor_id');
        $query->join('cad_orca c', 'c.id = t.orc_id');
        $query->join('users us', 'us.id = c.user_id');
        if($join != NULL) {
            foreach ($join as $value) {
                $query->join($value[0], $value[1]);
            }
        }
        if($where != "") {
            $query->where($where);
        }
        if($id != NULL)
            $query->where('t.id', $id);
        return $query->get($this->table.' t');
    }

    public function create($data = array())
    {
        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update($id = NULL, $data) {
        if (!isset($id))
        {
            $this->set_error('update_unsuccessful');
            return FALSE;
        }

        $this->db->update($this->table, $data, array('id' => $id));

        $return = $this->db->affected_rows() == 1;
        if ($return){
            $this->set_message('update_successful');
        } else{
            $this->set_error('update_unsuccessful');
        }
        return $return;
    }

    public function get_evento_agenda($id = null)
    {
        if($id == null)
            return false;

        $query = $this->db->select('e.*, a.descricao');
        $query->join('agenda a', 'a.id = e.agenda_id');
        $query->where('e.id', $id);
        return $query->get('agenda_evento e');
    }

    public function get_contratos_ead($id = null)
    {
        $user_id = $this->session->userdata('user_id');
        
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('c.*, c.data_matricula, u.first_name, u.last_name, un.unidade, cu.DESCRICAO as curso_desc, s.descricao as status_desc, ecs.segment_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('unidades un', 'un.id = c.filial')
            ->join('parametro p', 'p.LIG_ID_FILIAL = un.id AND c.status != p.LIG_ID_ANULADO')
            ->join('curso cu', 'cu.ID_CURSO = c.lig_id_curso')
            ->join('cad_status s', 's.id = c.status')
            ->join('ead_contratos_segments ecs', 'ecs.contrato_id = c.id', 'left')
            ->where('c.tipo_saxweb', '2')
            ->where('un.id IN ('.implode(",", $unids_array).')');
        
        if($id != null) {
            $query->where('c.id', $id);
        }
        $query->order_by('c.id', 'desc');

        return $query->group_by('c.id')
            ->get("cad_orca c");
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
            $messageLang = $this->lang->line($message) ? $this->lang->line($message) : '' . $message . '';
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
            $errorLang = $this->lang->line($error) ? $this->lang->line($error) : '' . $error . '';
            $_output .= $errorLang;
        }

        return $_output;
    }

}
