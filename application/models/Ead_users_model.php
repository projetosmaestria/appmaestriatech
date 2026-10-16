<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Ead_users_model extends CI_Model {

    protected $table = 'ead_users';

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
        $query = $this->db->select('eu.*, u.first_name, u.last_name, u.last_login, eu.level, DATE_FORMAT(eu.expiration_date, "%d/%m/%Y") as data_expiracao, DATE_FORMAT(co.data_matricula, "%d/%m/%Y") as data_matricula, DATE_FORMAT(eu.date_of_next_vpa, "%d/%m/%Y") as data_proximo_vpa, eu.ultimo_acesso, co.id as orc_id, co.desempenho_geral, s.descricao as status_desc');
        $query->join('users u', 'u.id = eu.user_id');
        $query->join('cad_orca co', 'co.user_id = u.id', 'left');
        $query->join('cad_status s', 's.id = co.status', 'left');
        if($join != NULL) {
            foreach ($join as $value) {
                $query->join($value[0], $value[1]);
            }
        }
        if($where != "") {
            $query->where($where);
        }
        if($id != NULL)
            $query->where('u.id', $id);
        $query->where('co.tipo_saxweb', 2);
        $query->distinct();
        return $query->get($this->table.' eu');
    }

    public function get_count($id = NULL, $where = "", $join = NULL)
    {
        $query = $this->db->select('eu.id');
        $query->join('users u', 'u.id = eu.user_id');
        $query->join('cad_orca co', 'co.user_id = u.id', 'left');
        if($join != NULL) {
            foreach ($join as $value) {
                $query->join($value[0], $value[1]);
            }
        }
        if($where != "") {
            $query->where($where);
        }
        if($id != NULL)
            $query->where('u.id', $id);
        $query->where('co.tipo_saxweb', 2);
        $query->distinct();
        return $query->get($this->table.' eu');
    }

    /**
     * get_segments
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_segments($contrato_id = NULL)
    {
        if($contrato_id == NULL)
            return false;

        return $this->db->select('es.*')
            ->join('ead_segments es', 'es.id = ecs.segment_id')
            ->join('cad_orca c', 'c.id = ecs.contrato_id')
            ->where('c.id IN('.$contrato_id.')')
            ->distinct()
            ->get('ead_contratos_segments ecs');
    }

    public function get_contratos($user_id = NULL)
    {
        if($user_id == NULL)
            return false;

        return $this->db->select('c.*')
            ->where('c.user_id', $user_id)
            ->where('c.tipo_saxweb', 2)
            ->get('cad_orca c');
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        if($data['user_id'] == "")
        {
            $this->set_error('user_id_required');
            return FALSE;
        }

        /*$existing = $this->db->get_where($this->table, array('user_id' => $data['user_id']))->num_rows();
        if($existing > 0) {
            $this->set_error('user_existing');
            return FALSE;
        }*/

        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();

        $this->set_message('creation_successful');

        return $id;
    }

    public function exists_in_segment($data) {
        $existing = $this->db->get_where('ead_users_segments', array('user_id' => $data['user_id'], 'segment_id' => $data['segment_id']))->num_rows();
        if($existing > 0) {
            return true;
        }
        return false;
    }

    public function remove_in_segments($user_id) {
        $this->db->delete('ead_users_segments', array("user_id"=>$user_id));
        return true;
    }

    public function delete($seg) {
        $this->db->delete($this->table, array('segment_label' => $seg));

        if ($this->db->affected_rows() == 0)
        {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function update($id = NULL, $data = null) {
        if (!isset($id))
        {
            $this->set_error('update_unsuccessful');
            return FALSE;
        }

        $this->db->update($this->table, $data, array('user_id' => $id));

        $return = $this->db->affected_rows() == 1;
        if ($return){
            $this->set_message('update_successful');
        } else{
            $this->set_error('update_unsuccessful');
        }
        return $return;
    }

    public function update_orcamento($id = NULL, $data = NULL) {
        if (!isset($id))
        {
            $this->set_error('update_unsuccessful');
            return FALSE;
        }

        $this->db->update('cad_orca', $data, array('id' => $id));

        return true;
    }

    public function update_pesquisa($id = NULL, $data = NULL) {
        if (!isset($id))
        {
            $this->set_error('update_unsuccessful');
            return FALSE;
        }

        $this->db->update('pesquisa_perfil', $data, array('orc_id' => $id));

        return true;
    }

    public function get_orcamento($id = NULL)
    {
        if($id == NULL)
            return false;

        return $this->db->select('*')
            ->where('id', $id)
            ->get('cad_orca');
    }

    public function get_pesquisa($orc_id = NULL)
    {
        if($orc_id == NULL)
            return false;

        return $this->db->select('*')
            ->where('orc_id', $orc_id)
            ->get('pesquisa_perfil');
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

    public function get_ultimo_contato($user_id) {
        return $this->db->select('*')
            ->where('user_id_de', $user_id)
            ->or_where('user_id_para', $user_id)
            ->order_by('created_at desc')
            ->limit(1)
            ->get('contatos_interno');
    }

    public function get_observacoes_tutor($tutor_id = null)
    {
        if($tutor_id == null)
            return false;

        return $this->db->select('h.*, u.first_name as nome_usu, u.last_name as sobrenome_usu, c.first_name as nome_cli, c.last_name as sobrenome_cli')
            ->join('users c', 'c.id = h.LIG_ID_CLIENTE')
            ->join('users u', 'u.id = h.LIG_ID_USUARIO')
            ->where('h.LIG_ID_CLIENTE', $tutor_id)
            ->where('h.STATUS', "OBSERVACÕES TUTOR")
            ->where('h.empresa_id', $this->session->userdata('empresa_id'))
            ->order_by('DATA_REGISTRO', 'DESC')
            ->order_by('HORA_REGISTRO', 'DESC')
            ->get("cad_clie_auditoria h");
    }

    public function observacao_create($data = array())
    {
        $data["empresa_id"] = $this->session->userdata('empresa_id');
        if($this->db->insert('cad_clie_auditoria', $data)) {
            $id = $this->db->insert_id();
            $this->set_message('creation_historico_successful');
        } else {
            $this->set_error('creation_historico_unsuccessful');
            return FALSE;
        }
        return $id;
    }
}
