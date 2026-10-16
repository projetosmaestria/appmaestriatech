<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_turmas_model extends CI_Model {

    protected $table = 'turma';

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
    public function get($id = null, $where = [])
    {
        $query_unids = $this->db->get_where('user_unidade', array('user_id'=>$this->session->userdata('user_id')));
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
            $unids[] = $value->unidade_id;
        }

        $query = $this->db->select('t.*, 
                            t.unidade as unidade_id, 
                            DATE_FORMAT(t.DATA_INICIO, "%d/%m/%Y") as data_inicio, 
                            DATE_FORMAT(t.DATA_TERMINO, "%d/%m/%Y") as data_termino, 
                            u.unidade, 
                            ld.titulo, 
                            c.DESCRICAO as curso')
            ->join('livros ld', 'ld.id = t.LIG_ID_PACOTE', 'left')
            ->join('unidades u', 'u.id = t.unidade', 'left')
            ->join('empresas e', 'e.id = u.empresa_id', 'left')
            ->join('curso c', 'c.ID_CURSO = t.LIG_ID_CURSO', 'left')
            // ->join('livros l', 'l.LIG_ID_CURSO_LIVRO = c.ID_CURSO', 'left')
            ->where_in('t.unidade', $unids)
            ->where('e.id', $this->session->userdata('empresa_id'));
            // ->group_by('t.id');

        if($id != null) {
            $query->where('t.ID', $id);
        }
        if($where) {
            foreach($where as $w) {
                $query->where($w);
            }
        }
        return $query->get($this->table." t");  
    }

     /**
     * get_horarios
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_horarios($turma_id = null)
    {
        $query = $this->db->select('*');
        if($turma_id != null) {
            $query->where('LIG_ID_TURMA', $turma_id);
        }
        return $query->get($this->table."_horario");  
    }

    /**
     * get_unidades
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_unidades()
    {
        $unids = $this->_get_users_unid();
        $unids_array = array(0);
        if($unids->num_rows() > 0) {
            foreach ($unids->result() as $unid) {
                $unids_array[] = $unid->unidade_id;
            }
        }

        $query = $this->db->select('*');
        $query->where('id IN ('.implode(",", $unids_array).')');
        $query->where('empresa_id', $this->session->userdata('empresa_id'));
        return $query->get('unidades');
    }

    private function _get_users_unid($id=FALSE)
    {
        // if no id was passed use the current users id
        $id || $id = $this->session->userdata('user_id');

        return $this->db->select('*')
            ->where('user_unidade.user_id', $id)
            ->get('user_unidade');
    }

    /**
     * get_livros
     *
     * @return array
     * @author Ellan Garbelini
     **/
    // public function get_livros()
    // {
    //     $query = $this->db->select('*');
    //     $query->where('empresa', $this->session->userdata('empresa_id'));
    //     $query->where('LIG_ID_CURSO_LIVRO', $_POST['id']);
    //     return $query->get('livros');
    // }

    public function get_livros($curso)
    {
        return $this->db->select('l.*')
            ->join('curso c', 'c.ID_CURSO = l.LIG_ID_CURSO_LIVRO')
            ->where('l.LIG_ID_CURSO_LIVRO', $curso)
            ->get('livros l');
    }

    public function get_cursos($unidade)
    {
        return $this->db->select('c.*')
            ->join('curso_filial cf', 'cf.LIG_ID_CURSO = c.ID_CURSO')
            ->where('cf.LIG_ID_FILIAL', $unidade)
            ->get('curso c');
    }

    /**
     * get_professores
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_professores($grupo_professor, $unidade)
    {
        return $this->db->select('u.*')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->where('ug.group_id', $grupo_professor)
            ->where('uu.unidade_id', $unidade)
            ->where('u.active', 1)
            ->get('users u');
    }

    /**
     * get_alunos
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_alunos($grupo_aluno, $unidade_id)
    {
        return $this->db->select('u.*, u.id as user_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->where('ug.group_id', $grupo_aluno)
            ->where('uu.unidade_id', $unidade_id)
            //->where('u.active', 1)
            ->get('users u');
    }

    /**
     * get_alunos_turma
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_alunos_turma($turma_id)
    {
        return $this->db->select('u.*, u.id as user_id')
            ->join('turma_aluno ta', 'ta.aluno = u.id')
            ->where('ta.turma', $turma_id)
            ->get('users u');
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
     * create_horario
     *
     * @author Ellan Garbelini
    */
    public function create_horario($data = array())
    {
        $this->db->insert($this->table."_horario", $data);
        $id = $this->db->insert_id();

        //$this->set_message('creation_horario_successful');

        return $id;
    }

    /**
     * create_aluno
     *
     * @author Ellan Garbelini
    */
    public function create_aluno($aluno, $turma)
    {
        $this->db->insert($this->table."_aluno", array('aluno'=>$aluno, 'turma'=>$turma));
        $id = $this->db->insert_id();

        //$this->set_message('creation_horario_successful');

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

        $this->db->update($this->table, $data, array('ID' => $id));

        $this->set_message('update_successful');

        return TRUE;
    }

    public function delete_horario($id) {
        if($this->db->delete($this->table."_horario", array('LIG_ID_TURMA' => $id, 'ID_CONTROLE' => '0'))) {
            //$this->set_message('update_successful');
            return true;
        } else {
            //$this->set_message('update_successful');
            return false;
        }
    }

    public function delete_aluno($aluno, $turma_id) {
        if($this->db->delete($this->table."_aluno", array('aluno' => $aluno, 'turma' => $turma_id))) {
            //$this->set_message('update_successful');
            return true;
        } else {
            //$this->set_message('update_successful');
            return false;
        }
    }

    public function exists_in_turma($aluno, $turma_id) {
        $query = $this->db->get_where($this->table."_aluno", array('aluno' => $aluno, 'turma' => $turma_id));
        if($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_admin_preferences($emp_id = null) {
        if($emp_id == null)
            $emp_id = $this->session->userdata('empresa_id');

        return $this->db->get_where('admin_preferences', array('empresa_id' => $emp_id))->row();
    }

    public function send_push($turma_id, $data)
    {
        $this->load->library('push_android');
        $this->load->library('push_ios');

        $users = $this->db->select('ad.id_aluno, ad.device_id, ad.device_info, ad.device_token, u.first_name, u.last_name')
            ->join('users u', 'ad.id_aluno = u.id')
            ->join('turma_aluno ta', 'ta.aluno = u.id')
            ->where('u.active', '1')
            ->where('ta.turma', $turma_id)
            ->where('ad.device_token IS NOT NULL')
            ->get('aluno_device ad');

        if($users->num_rows() > 0) {
            foreach ($users->result() as $value) {
                if(user_agent($value->device_info) == 'ios'):
                    if($this->push_ios->notify($value->device_token, array("title"=>$data["title"],"body"=>$data["body"]))):
                        $this->set_message("Notificação enviada ao aluno ".$value->first_name." ".$value->last_name.".<br>");
                    else:
                        $this->set_message("Notificação não enviada ao aluno ".$value->first_name." ".$value->last_name.".<br>");
                    endif;
                elseif(user_agent($value->device_info) == 'android'):
                    if($this->push_android->notify($value->device_token, array("title"=>$data["title"],"body"=>$data["body"]))):
                        $this->set_message("Notificação enviada ao aluno ".$value->first_name." ".$value->last_name.".<br>");
                    else:
                        $this->set_message("Notificação não enviada ao aluno ".$value->first_name." ".$value->last_name.".<br>");
                    endif;
                endif;
            }
        }
        return true;
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
