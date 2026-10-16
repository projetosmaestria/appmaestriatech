<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_alunos_model extends CI_Model {

    protected $table = 'users';

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
    public function get()
    {
        $query_aluno_grupo = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();
        if($query_aluno_grupo->grupo_aluno != "") {
            $query_unids = $this->db->select('uu.*')
                ->join('unidades u', 'u.id = uu.unidade_id')
                ->where('u.empresa_id', $this->session->userdata('empresa_id'))
                ->where('uu.user_id', $this->session->userdata('user_id'))
                ->get('user_unidade uu');

            $unids = array(0);
            foreach ($query_unids->result() as $value) {
                $unids[] = $value->unidade_id;
            }

            $query = $this->db->select('u.*, p.*, u.id as user_id')
                ->join('pessoas p', 'u.id = p.user_id', 'left')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('user_unidade uu', 'uu.user_id = u.id', 'left')
                ->join('unidades un', 'un.id = uu.unidade_id', 'left')
                ->where('ug.group_id', $query_aluno_grupo->grupo_aluno)
                ->where('uu.unidade_id IN ('.implode(",",$unids).')');
            $query->group_by('u.id');
            return $query->get($this->table." u")->result();
        }
        return null;        
    }

    public function get_unidades($user_id)
    {
        return $this->db->select('u.unidade')
            ->join('user_unidade uu', 'u.id = uu.unidade_id')
            ->where('uu.user_id', $user_id)
            ->get('unidades u'); 
    }

    public function get_pessoa($user_id)
    {
        return $this->db->get_where('pessoas', array('user_id' => $user_id));  
    }

    public function get_aluno($user_id)
    {
        return $this->db->get_where($this->table, array('id' => $user_id));  
    }

    public function get_turmas($user_id)
    {
        return $this->db->select('t.DESCRICAO')
            ->join('turma_aluno ta', 'ta.turma = t.ID')
            ->where('ta.aluno', $user_id)
            ->get('turma t'); 
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        $this->db->insert("pessoas", $data);
        $id = $this->db->insert_id();

        $this->set_message('account_creation_successful');

        return $id;
    }

    /**
     * update
     *
     * @return bool
     * @author Ellan Garbelini
     **/
    public function update($user_id = FALSE, $data = array())
    {
        if (empty($user_id)) return FALSE;

        $this->db->update("pessoas", $data, array('user_id' => $user_id));

        $this->set_message('update_successful');

        return TRUE;
    }

    public function get_dispositivos($id = null)
    {
        if($id == null) {
            return false;
        }
        return $this->db->select('*')
            ->where('id_aluno', $id)
            ->get("aluno_device");
    }

    public function get_historico_dispositivos($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');

        return $this->db->get_where('aluno_device_historico', array('aluno' => $user_id)); 
    }

    public function get_livros($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');
        return $this->db->select('al.qrcode, al.data_cadastro, l.titulo, l.id')
            ->join('livros l', 'l.id = al.livro')
            //->where('l.empresa', $this->session->userdata('empresa_id'))
            ->where('al.aluno', $user_id)
            ->get('aluno_livro al');
    }

    public function remover_dispositivo($id) {
        $device = $this->db->get_where('aluno_device', array('id' => $id))->row();
        $admin_preferences = $this->db->get_where("admin_preferences", array("empresa_id" => $this->session->userdata("empresa_id")))->row();
        $verify_user_is_admin = $this->db->get_where('users_groups', array('user_id' => $this->session->userdata('user_id')))->result();
        $is_admin = false;
        foreach ($verify_user_is_admin as $value) {
            if($admin_preferences->grupo_admin == $value->group_id)
                $is_admin = true;
        }
        
        if($is_admin == false) {
            if($this->db->get_where('aluno_device_historico', array('device_id' => $device->device_id, 'device_info' => $device->device_info))->num_rows() >= 5) {
                $this->set_message('device_max_exceded');
                return false;
            }
        }
        
        $this->db->delete('aluno_device', array('id' => $id));
        if ($this->db->affected_rows() == 0) {
            $this->set_message('device_not_exist');
            return false;
        } else {
            $this->db->insert("aluno_device_historico", array('aluno' => $device->id_aluno, 'device_id' => $device->device_id, 'device_info' => $device->device_info, 'liberado_em' => date('Y-m-d H:i:s'), 'liberado_por' => $this->session->userdata('user_id')));
            $id = $this->db->insert_id();
            $this->set_message('device_removed');
            return true;
        }
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

    function allserver_side_count()
    {   
        /*$query = $this
                ->db
                ->join("empresas_users", "empresas_users.user_id = users.id", "left")
                ->where("(empresas_users.empresa_id = ".$this->session->userdata('empresa_id')." OR empresas_users.user_id IS NULL)")
                ->group_by('users.id')
                ->get('users');*/

        $query_aluno_grupo = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();
        if($query_aluno_grupo->grupo_aluno != "") {
            $query_unids = $this->db->select('uu.*')
                ->join('unidades u', 'u.id = uu.unidade_id')
                ->where('u.empresa_id', $this->session->userdata('empresa_id'))
                ->where('uu.user_id', $this->session->userdata('user_id'))
                ->get('user_unidade uu');
            $unids = array(0);
            foreach ($query_unids->result() as $value) {
                $unids[] = $value->unidade_id;
            }

            $query = $this->db->select('u.*, p.*, u.id as user_id')
                ->join('pessoas p', 'u.id = p.user_id', 'left')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('user_unidade uu', 'uu.user_id = u.id', 'left')
                ->join('unidades un', 'un.id = uu.unidade_id', 'left')
                ->where('ug.group_id', $query_aluno_grupo->grupo_aluno)
                ->where('uu.unidade_id IN ('.implode(",",$unids).')')
                ->group_by('u.id')
                ->get($this->table." u");
        
            return $query->num_rows(); 
        } else {
            return null;  
        }
     

    }
    
    function allserver_side($limit,$start,$col,$dir)
    {   
       /*$query = $this
                ->db
                ->select('users.*, users.id as user_id')
                ->join("empresas_users", "empresas_users.user_id = users.id", "left")
                ->where("(empresas_users.empresa_id = ".$this->session->userdata('empresa_id')." OR empresas_users.user_id IS NULL)")
                ->limit($limit,$start)
                ->group_by('users.id')
                ->order_by($col,$dir)
                ->get('users');*/

        $query_aluno_grupo = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();
        if($query_aluno_grupo->grupo_aluno != "") {
            $query_unids = $this->db->select('uu.*')
                ->join('unidades u', 'u.id = uu.unidade_id')
                ->where('u.empresa_id', $this->session->userdata('empresa_id'))
                ->where('uu.user_id', $this->session->userdata('user_id'))
                ->get('user_unidade uu');
            $unids = array(0);
            foreach ($query_unids->result() as $value) {
                $unids[] = $value->unidade_id;
            }

            $query = $this->db->select('u.*, p.*, u.id as user_id')
                ->join('pessoas p', 'u.id = p.user_id', 'left')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('user_unidade uu', 'uu.user_id = u.id', 'left')
                ->join('unidades un', 'un.id = uu.unidade_id', 'left')
                ->where('ug.group_id', $query_aluno_grupo->grupo_aluno)
                ->where('uu.unidade_id IN ('.implode(",",$unids).')')
                ->limit($limit,$start)
                ->group_by('u.id')
                ->order_by($col,$dir)
                ->get($this->table." u");
        
            if($query->num_rows()>0)
            {
                return $query->result(); 
            }
            else
            {
                return null;
            }
        } else {
            return null;  
        }
        
    }
   
    function server_side_search($limit,$start,$search,$col,$dir)
    {
        /*$query = $this
                ->db
                ->select("users.*, users.id as user_id, CONCAT(first_name, ' ', last_name) as 'full_name'")
                ->join("empresas_users", "empresas_users.user_id = users.id", "left")
                ->where("(empresas_users.empresa_id = ".$this->session->userdata('empresa_id')." OR empresas_users.user_id IS NULL)")
                ->like("CONCAT(first_name, ' ', last_name)",$search)
                ->or_like('email',$search)
                ->limit($limit,$start)
                ->group_by('users.id')
                ->order_by($col,$dir)
                ->get('users');*/

        $query_aluno_grupo = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();
        if($query_aluno_grupo->grupo_aluno != "") {
            $query_unids = $this->db->select('uu.*')
                ->join('unidades u', 'u.id = uu.unidade_id')
                ->where('u.empresa_id', $this->session->userdata('empresa_id'))
                ->where('uu.user_id', $this->session->userdata('user_id'))
                ->get('user_unidade uu');
            $unids = array(0);
            foreach ($query_unids->result() as $value) {
                $unids[] = $value->unidade_id;
            }

            $query = $this->db->select('u.*, p.*, u.id as user_id')
                ->join('pessoas p', 'u.id = p.user_id', 'left')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('user_unidade uu', 'uu.user_id = u.id', 'left')
                ->join('unidades un', 'un.id = uu.unidade_id', 'left')
                ->where("(ug.group_id = ".$query_aluno_grupo->grupo_aluno." AND uu.unidade_id IN (".implode(",",$unids).")) AND (CONCAT(u.first_name, ' ', u.last_name) LIKE '%".$search."%' OR u.email LIKE '%".$search."%')")
                ->limit($limit,$start)
                ->group_by('u.id')
                ->order_by($col,$dir)
                ->get($this->table." u");
        
            if($query->num_rows()>0)
            {
                return $query->result();  
            }
            else
            {
                return null;
            }
        } else {
            return null;  
        }
    }

    function server_side_search_count($search)
    {
        $query_aluno_grupo = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();
        if($query_aluno_grupo->grupo_aluno != "") {
            $query_unids = $this->db->select('uu.*')
                ->join('unidades u', 'u.id = uu.unidade_id')
                ->where('u.empresa_id', $this->session->userdata('empresa_id'))
                ->where('uu.user_id', $this->session->userdata('user_id'))
                ->get('user_unidade uu');
            $unids = array(0);
            foreach ($query_unids->result() as $value) {
                $unids[] = $value->unidade_id;
            }

            $query = $this->db->select('u.*, p.*, u.id as user_id')
                ->join('pessoas p', 'u.id = p.user_id', 'left')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('user_unidade uu', 'uu.user_id = u.id', 'left')
                ->join('unidades un', 'un.id = uu.unidade_id', 'left')
                ->where("(ug.group_id = ".$query_aluno_grupo->grupo_aluno." AND uu.unidade_id IN (".implode(",",$unids).")) AND (CONCAT(u.first_name, ' ', u.last_name) LIKE '%".$search."%' OR u.email LIKE '%".$search."%')")
                ->group_by('u.id')
                ->get($this->table." u");
                
            return $query->num_rows();
        } else {
            return null;
        }
        
        /*$query = $this
                ->db
                ->join("empresas_users", "empresas_users.user_id = users.id", "left")
                ->where("(empresas_users.empresa_id = ".$this->session->userdata('empresa_id')." OR empresas_users.user_id IS NULL)")
                ->like("CONCAT(first_name, ' ', last_name)",$search)
                ->or_like('email',$search)
                ->group_by('users.id')
                ->get('users');
    
        return $query->num_rows();*/
    }
	
	public function get_unidade_livro($livro_id = "")
    {
        if($livro_id == "")
            return false;

        return $this->db->select('id')
            ->where('livro', $livro_id)
            ->get('unidade_livro');
    }

    public function get_exercicios($livro_id = "")
    {
        if($livro_id == "")
            return false;

        return $this->db->select('id')
            ->where('livro IN ('.$livro_id.')')
            ->get('exercicios');
    }

    public function get_perguntas($exerc_id = "")
    {
        if($exerc_id == "")
            return false;

        return $this->db->select('id')
            ->where('exercicio IN ('.$exerc_id.')')
            ->get('pergunta_exercicio');
    }

    public function remover_respostas($aluno_id = null, $perguntas_id = "", $livros = "") {

        if($aluno_id == null || $perguntas_id == "")
            return false;

        $this->db->where('aluno', $aluno_id);
        $this->db->where('pergunta IN ('.$perguntas_id.')');
        @$this->db->delete('resposta_aluno');

        if ($this->db->affected_rows() == 0) {
            $this->set_message('respostas_nao_removidas');
            return false;
        } else {
            $this->set_message('respostas_removidas');

            $this->db->insert("cad_clie_auditoria", array('empresa_id' => $this->session->userdata('empresa_id'), 'LIG_ID_CLIENTE' => $aluno_id, 'LIG_ID_USUARIO' => $this->session->userdata('user_id'), 'REGISTRO' => true, 'STATUS' => 7, 'MENSAGEM' => 'Resposta(s) do(s) livro(s) "'.$livros.'" removida(s).', 'DATA_REGISTRO' => date('Y-m-d'), 'HORA_REGISTRO' => date('H:i:s')));

            return true;
        }
    }

    public function remover_correcoes($aluno_id = null, $unidades = "") {

        if($aluno_id == null || $unidades == "")
            return false;
        
        $this->db->where('id_aluno', $aluno_id);
        $this->db->where('id_unidade IN ('.$unidades.')');
        $this->db->delete('resposta_aluno_correcao');

        if ($this->db->affected_rows() == 0) {
            $this->set_message('correcoes_nao_removidas');
            return false;
        } else {
            $this->set_message('correcoes_removidas');
            return true;
        }

    }
}
