<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Ead_segments_model extends CI_Model {

    protected $table = 'ead_segments';

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
        $query = $this->db->select('s.*, u.first_name, u.last_name')
            ->join('users u', 'u.id = s.tutor_id', 'left');

        if($id != null)
            $query->where("s.id", $id);

        return $query->get($this->table.' s');
    }

    /**
     * get_users
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_users($segment_label = "")
    {
        $query = $this->db->select('es.*, u.first_name, u.last_name, u.id as user_id, DATE_FORMAT(eu.expiration_date, "%d/%m/%Y") as data_expiracao, c.id as contrato_id');
        $query->join('ead_segments es', 'es.id = eus.segment_id');
        $query->join('cad_orca c', 'c.id = eus.contrato_id');
        $query->join('users u', 'c.user_id = u.id');
        $query->join('ead_users eu', 'eu.user_id = u.id');
        if($segment_label != "")
            $query->where('es.segment_label', $segment_label);
        $query->group_by('u.id');
        return $query->get("ead_contratos_segments eus");
    }

    public function add_in_segment($data) {
        $this->db->insert('ead_contratos_segments', $data);
        return true;
    }

    public function remove_in_segment($data) {
        $this->db->delete('ead_contratos_segments', $data);
        return true;
    }

    /**
     * get_by_label
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_by_label($label)
    {
        $query = $this->db->select('id');
        $query->where("segment_label", $label);
        return $query->get($this->table);
    }

    /**
     * get_tutores
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_tutores()
    {
        $admin_preferences = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();
        $usuarios_tutoria = $this->db->select('u.*')
            ->join('users u', 'u.id = ug.user_id')
            ->where('ug.group_id', $admin_preferences->grupo_tutor)
            ->where('u.active', 1)
            ->get('users_groups ug');
        return $usuarios_tutoria;
    }

    /**
     * get_segments_by_tutor
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_segments_by_tutor($tutor)
    {
        return $this->db->get_where($this->table, array('tutor_id' => $tutor));
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        if($data['segment_label'] == "")
        {
            $this->set_error('segment_label_required');
            return FALSE;
        }

        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();

        $this->set_message('creation_successful');

        return $id;
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
            $this->set_message('update_unsuccessful');
        }
        return $return;
    }

    public function activate($id = NULL)
    {
        if (!isset($id))
        {
            $this->set_error('activate_unsuccessful');
            return FALSE;
        }

        $data = array(
            'active' => '1'
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
            'active' => '0'
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
