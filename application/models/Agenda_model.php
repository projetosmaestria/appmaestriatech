<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Agenda_model extends CI_Model {

    protected $table = 'agenda';

    protected $messages;

    protected $errors;

    public function __construct()
    {
        parent::__construct();
        $this->messages    = array();
        $this->errors      = array();
    }

    /**
     * get_agenda
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_agenda($id = null)
    {
        $query = $this->db->select('a.*, e.role');        
        $query->join('agenda_envolvido e', 'e.agenda_id = a.id');
        $query->where('e.user_id', $this->session->userdata('user_id'));
        $query->where('a.empresa_id', $this->session->userdata('empresa_id'));
        $query->where('a.ativa', "S");
        if($id != null) {
            $query->where('a.id', $id);
        }
        $query->order_by('a.descricao', 'asc');
        return $query->get($this->table.' a');
    }

    public function get_usuarios_compartilhar($agenda_id)
    {
        $user_unidade = $this->_get_users_unid();
        if(empty($user_unidade))
            $user_unidade[] = 0;

        $usuarios_agenda = $this->db->get_where('agenda_envolvido', array("agenda_id" => $agenda_id));
        $uagenda_array = array();
        if($usuarios_agenda->num_rows() > 0) {
            foreach ($usuarios_agenda->result() as $uagenda) {
                $uagenda_array[] = $uagenda->user_id;
            }
        }
        if(empty($uagenda_array))
            $uagenda_array[] = 0;

        $query = $this->db->select('u.*, concat(u.first_name, " ", u.last_name) as nome')       
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->join('unidades un', 'un.id = uu.unidade_id')
            ->where('un.empresa_id', $this->session->userdata('empresa_id'))
            ->where('un.id IN ('.implode(",", $user_unidade).')')
            ->where('u.id NOT IN ('.implode(",", $uagenda_array).')')
            ->where('u.active', 1)
            ->order_by('nome', 'asc')
            ->distinct()
            ->get('users u');

        $us = array();
        if($query->num_rows() > 0) {
            foreach ($query->result() as $u) {
                if($this->has_permissao("agenda", "index", $u->id)) {
                    $us[] = $u;
                }
            }
        }

        return $us;
    }

    public function get_usuarios_compartilhados($agenda_id)
    {
        $query = $this->db->select('concat(u.first_name, " ", u.last_name) as nome_usu, u.id as id_usu, u.email as email_usu, concat(uu.first_name, " ", uu.last_name) as nome_por, uu.id as id_por, ae.created_at')       
            ->join('users u', 'ae.user_id = u.id')
            ->join('users uu', 'ae.criado_por = uu.id')
            ->where('ae.agenda_id', $agenda_id)
            ->where('u.id != '.$this->session->userdata('user_id'))
            ->order_by('nome_usu', 'asc')
            ->distinct()
            ->get('agenda_envolvido ae');

        return $query;
    }

    public function get_usuario_compartilhado($agenda_id, $user_id)
    {
        $query = $this->db->select('*')       
            ->join('users u', 'ae.user_id = u.id')
            ->join('users uu', 'ae.criado_por = uu.id')
            ->where('ae.agenda_id', $agenda_id)
            ->where('u.id', $user_id)
            ->limit(1)
            ->distinct()
            ->get('agenda_envolvido ae');

        return $query;
    }

    public function remover_usuarios_envolvido($agenda_id, $usuarios = null)
    {
        if(count($usuarios) > 0) {
            foreach ($usuarios as $value) {
                if($value != $this->session->userdata('user_id'))
                    $this->db->where('user_id', $value);
            }
            $this->db->where('agenda_id', $agenda_id);
            $this->db->delete('agenda_envolvido');
        } else {
            if($usuarios == null) {
                $this->db->where('agenda_id', $agenda_id);
                $this->db->delete('agenda_envolvido');
            }
        }

        return;
    }

    public function excluir_evento($agenda_id = null)
    {
        if($agenda_id == null)
            return false;

        $this->db->where('agenda_id', $agenda_id);
        $this->db->delete('agenda_evento');

        return;
    }

    public function excluir_agenda($id = null)
    {
        if($id == null)
            return false;

        // $this->db->where('id', $id);
        // $this->db->delete('agenda');

        $this->db->update('agenda', array("ativa" => "N", "google_id" => ""), array('id' => $id));

        $this->db->insert('auditorias', array("controlador"=>'agenda',"metodo"=>'excluir_agenda',"obs"=>'Excluíndo agenda #'.$id.'.',"empresa_id"=>$this->session->userdata("empresa_id"),"user_id"=>$this->session->userdata("user_id")));
    }


    private function has_permissao($controlador, $metodo, $id = null)
    {
        $permissoes = array();
        foreach ($this->ion_auth_model->get_users_groups_permissoes($id)->result() as $key => $value) {
            $unserialized = unserialize($value->permissoes);
            if(is_array($unserialized)) {
                foreach ($unserialized as $key => $perm) {
                    if(array_key_exists($key, $permissoes)) {
                        if($perm) {
                            foreach ($perm as $_p) {
                                if(!in_array($_p, $permissoes[$key])) {
                                    $permissoes[$key][] = $_p;
                                }
                            }
                        }
                    } else {
                        $permissoes[$key] = $perm;
                    }
                }
            }
        }
        if(isset($permissoes[$controlador]) && is_array($permissoes[$controlador])){
            if(in_array($metodo, $permissoes[$controlador]))
                return true;
            else
                return false;
        }
        else
            return false;
    }

    private function _get_users_unid($id=FALSE)
    {
        // if no id was passed use the current users id
        $id || $id = $this->session->userdata('user_id');

        $query = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $id)
            ->get('user_unidade uu');

        $retorno = array();
        if($query->num_rows()) {
            foreach ($query->result() as $key => $value) {
                $retorno[] = $value->unidade_id;
            }
        }
        return $retorno;
    }

    public function get_evento($agenda_id = null)
    {
        if($agenda_id == null)
            return false;

        $query = $this->db->select('a.*');        
        $query->where('a.agenda_id', $agenda_id);
        $query->order_by('a.inicio', 'desc');
        return $query->get('agenda_evento a');
    }

    public function get_evento_by_id($id = null)
    {
        if($id == null)
            return false;

        $query = $this->db->select('*');        
        $query->where('id', $id);
        return $query->get('agenda_evento');
    }

    public function has_acesso($agenda_id = null, $roles = array())
    {
        if($agenda_id == null)
            return false;

        $this->db->where("user_id", $this->session->userdata('user_id'));
        $this->db->where("agenda_id", $agenda_id);

        if(!empty($roles)) {
            $where_role = "";
            $i=0;
            foreach ($roles as $role) {
                if($i > 0)
                    $where_role .= " OR ";
                $where_role .= "role = '".$role."'";
                $i++;
            }            
            $this->db->where($where_role);
        }

        $num_rows = $this->db->get_where('agenda_envolvido')->num_rows();
        if($num_rows > 0)
            return true;
        else
            return false;
    }

    public function exist_evento($data)
    {
        $num_rows = $this->db->get_where('agenda_evento', $data)->num_rows();
        if($num_rows > 0)
            return true;
        else
            return false;
    }

    public function criar_agenda($data = array())
    {
        $data['empresa_id'] = $this->session->userdata('empresa_id');
        $data['dono'] = $this->session->userdata('user_id');
        if($this->db->insert('agenda', $data)) {
            $id = $this->db->insert_id();

            $this->db->insert('agenda_envolvido', array("agenda_id" => $id, "user_id" => $this->session->userdata('user_id'), "criado_por" => $this->session->userdata('user_id'), "role" => "owner"));
        }

        return $id;
    }

    public function criar_evento($data = array())
    {
        if($this->db->insert('agenda_evento', $data)) {
            $id = $this->db->insert_id();
        }

        return $id;
    }

    public function compartilhar_agenda($data = array())
    {
        $num_rows = $this->db->get_where('agenda_envolvido', $data)->num_rows();
        if($num_rows == 0) {
            if($this->db->insert('agenda_envolvido', $data)) {
                $id = $this->db->insert_id();
            }
        }

        return true;
    }

    public function atualizar_evento($id = FALSE, $data = array())
    {
        if (empty($id)) return FALSE;

        $this->db->update('agenda_evento', $data, array('id' => $id));

        return TRUE;
    }

    public function get_user()
    {
        $query = $this->db->get_where('agenda_user', array('user_id', $this->session->userdata('user_id')));
        return $query;
    }

    public function salvar_config_google_calendar($data = array())
    {
        $exist = $this->db->get_where('agenda_user', array("user_id" => $data['user_id']));
        if($exist->num_rows() > 0) {
            $this->db->update('agenda_user', $data, array('user_id' => $data['user_id']));
        } else {
            $this->db->insert('agenda_user', $data);
            $id = $this->db->insert_id();
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
