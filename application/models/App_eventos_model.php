<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_eventos_model extends CI_Model {

    protected $table = 'app_eventos';

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
        $query = $this->db->select('*, DATE_FORMAT(data, "%d/%m/%Y") as data_formatada');
        if($id != null) {
            $query->where('id', $id);
        }
        return $query->get($this->table);
    }

    /**
     * get_unidades
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_unidades()
    {
        return $this->db->select('u.*')
            ->join('user_unidade uu', 'u.id = uu.unidade_id')
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->get('unidades u');
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

    public function send_push($evento)
    {
        $this->load->library('push_android');
        $this->load->library('push_ios');

        $users = $this->db->select('ad.id_aluno, ad.device_id, ad.device_info, ad.device_token, u.first_name, u.last_name')
            ->join('users u', 'ad.id_aluno = u.id')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->where('u.active', '1')
            ->where('uu.unidade_id IN ('.implode(",", unserialize($evento->unidades)).')')
            ->where('ad.device_token IS NOT NULL')
            ->get('aluno_device ad');

        if($users->num_rows() > 0) {
            foreach ($users->result() as $value) {
                if(user_agent($value->device_info) == 'ios'):
                    if($this->push_ios->notify($value->device_token, array("title"=>$evento->titulo,"body"=>$evento->texto))):
                        $this->set_message("Notificação enviada ao aluno ".$value->first_name." ".$value->last_name.".<br>");
                    else:
                        $this->set_message("Notificação não enviada ao aluno ".$value->first_name." ".$value->last_name.".<br>");
                    endif;
                elseif(user_agent($value->device_info) == 'android'):
                    if($this->push_android->notify($value->device_token, array("title"=>$evento->titulo,"body"=>$evento->texto))):
                        $this->set_message("Notificação enviada ao aluno ".$value->first_name." ".$value->last_name.".<br>");
                    else:
                        $this->set_message("Notificação não enviada ao aluno ".$value->first_name." ".$value->last_name.".<br>");
                    endif;
                endif;
            }
        }
        return true;
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
