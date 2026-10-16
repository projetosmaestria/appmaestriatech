<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_respostas_model extends CI_Model {

    protected $table = 'resposta_aluno';

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
    public function get($q = array(), $limit = null, $offset = null, $sort_by = 'data_envio', $order = 'asc')
    {
        $unids = $this->_get_users_unid();
        $unids_array = array();
        if(count($unids) > 0) {
            foreach ($unids as $unid) {
                $unids_array[] = $unid;
            }
        }

        $unids_where = array();
        if($q[0]) {
            $unids_where = array_intersect($q[0], $unids_array);
        } else {
            $unids_where = $unids_array;
        }

        $where = "";
        if($q[1] != 1)
            $where .= ' AND rac.status = "E"';
        if($q[2] != "") {
            $where .= " AND (CONCAT(TRIM(u.first_name), ' ', TRIM(u.last_name)) LIKE '%".base64_decode(urldecode($q[2]))."%'";
            $where .= " OR CONCAT(ul.unidade, ' - ', ul.titulo) LIKE '%".base64_decode(urldecode($q[2]))."%'";
            $where .= " OR DATE_FORMAT(rac.data_envio, '%d/%m/%Y %H:%i') LIKE '%".base64_decode(urldecode($q[2]))."%')";
        }

        $where .= " AND l.empresa = '".$this->session->userdata('empresa_id')."'";

        if($q[3]) {
            $turma = $this->db->select('*')->where('ID IN ('.implode(",", $q[3]).')')->get('turma');
            $livros = array();
            foreach ($turma->result() as $key => $value) {
                $livros[] = $value->LIG_ID_PACOTE;
            }
        }

        $query = $this->db->select('u.first_name, u.last_name, e.unidade, r.data_resposta, r.aluno, ul.unidade as unidade_livro, ul.titulo, ul.tipo, rac.id as id_correcao, rac.status, rac.data_envio, CASE WHEN rac.status IN ("C") THEN "Sim" ELSE "Não" END AS status_convert, CASE WHEN ul.tipo IN ("C") THEN "Complementar" ELSE "Obrigatória" END AS tipo, DATE_FORMAT(rac.data_envio, "%d/%m/%Y %H:%i") as data_envio_formatada, CASE WHEN rac.prazo IN ("S") THEN "Sim" ELSE "Não" END AS prazo_convert')
            ->join('users u', 'r.aluno = u.id')
            ->join('pergunta_exercicio pe', 'r.pergunta = pe.id')
            ->join('exercicios e', 'pe.exercicio = e.id')
            ->join('unidade_livro ul', 'e.unidade = ul.id')
            ->join('resposta_aluno_correcao rac', 'rac.id_aluno = r.aluno AND rac.id_unidade = ul.id')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->join('livros l', 'l.id = ul.livro');

        if($unids_where)
            $query->where('uu.unidade_id IN ('.implode(",", $unids_where).')'.$where);
        else
            $query->where($where);

        if($q[3]) {
            $query->join('turma_aluno ta', 'ta.aluno = u.id')
                ->join('turma t', 't.ID = ta.turma');
            $query->where('t.ID IN ('.implode(",", $q[3]).')');

            $query->where('l.id IN ('.implode(",", $livros).')');
        }

        if($q[4]) {
            if($q[3]) {
                $query->join('turma_horario th', 'th.LIG_ID_TURMA = ta.turma')
                    ->join('users p', 'p.id = th.LIG_ID_PROFESSOR');
            } else {
                $query->join('turma_aluno ta', 'ta.aluno = u.id')
                ->join('turma t', 't.ID = ta.turma')
                ->join('turma_horario th', 'th.LIG_ID_TURMA = ta.turma')
                ->join('users p', 'p.id = th.LIG_ID_PROFESSOR');
            }
            $query->where('th.LIG_ID_PROFESSOR IN ('.implode(",", $q[4]).')');
        }

        if($q[5]) {
            $query->where('rac.prazo', $q[5]);
        }

        if($limit != null)
            $query->limit($limit, $offset);
        $result = $query
            ->order_by($sort_by, $order)
            ->group_by('ul.id, u.id')
            ->get($this->table." r");

        return $result;
    }

    public function get_count_prazo($q = array(), $prazo)
    {
        $unids = $this->_get_users_unid();
        $unids_array = array();
        if(count($unids) > 0) {
            foreach ($unids as $unid) {
                $unids_array[] = $unid;
            }
        }

        $unids_where = array();
        if($q[0]) {
            $unids_where = array_intersect($q[0], $unids_array);
        } else {
            $unids_where = $unids_array;
        }

        $where = "";
        if($q[1] != 1)
            $where .= ' AND rac.status = "E"';
        if($q[2] != "") {
            $where .= " AND (CONCAT(TRIM(u.first_name), ' ', TRIM(u.last_name)) LIKE '%".base64_decode(urldecode($q[2]))."%'";
            $where .= " OR CONCAT(ul.unidade, ' - ', ul.titulo) LIKE '%".base64_decode(urldecode($q[2]))."%'";
            $where .= " OR DATE_FORMAT(rac.data_envio, '%d/%m/%Y %H:%i') LIKE '%".base64_decode(urldecode($q[2]))."%')";
        }

        $where .= " AND l.empresa = '".$this->session->userdata('empresa_id')."'";

        if($q[3]) {
            $turma = $this->db->select('*')->where('ID IN ('.implode(",", $q[3]).')')->get('turma');
            $livros = array();
            foreach ($turma->result() as $key => $value) {
                $livros[] = $value->LIG_ID_PACOTE;
            }
        }

        $query = $this->db->select('COUNT(*)')
            ->join('users u', 'r.aluno = u.id')
            ->join('pergunta_exercicio pe', 'r.pergunta = pe.id')
            ->join('exercicios e', 'pe.exercicio = e.id')
            ->join('unidade_livro ul', 'e.unidade = ul.id')
            ->join('resposta_aluno_correcao rac', 'rac.id_aluno = r.aluno AND rac.id_unidade = ul.id')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->join('livros l', 'l.id = ul.livro');

        if($unids_where)
            $query->where('uu.unidade_id IN ('.implode(",", $unids_where).')'.$where);
        else
            $query->where($where);

        if($q[3]) {
            $query->join('turma_aluno ta', 'ta.aluno = u.id')
                ->join('turma t', 't.ID = ta.turma');
            $query->where('t.ID IN ('.implode(",", $q[3]).')');

            $query->where('l.id IN ('.implode(",", $livros).')');
        }

        if($q[4]) {
            if($q[3]) {
                $query->join('turma_horario th', 'th.LIG_ID_TURMA = ta.turma')
                    ->join('users p', 'p.id = th.LIG_ID_PROFESSOR');
            } else {
                $query->join('turma_aluno ta', 'ta.aluno = u.id')
                ->join('turma t', 't.ID = ta.turma')
                ->join('turma_horario th', 'th.LIG_ID_TURMA = ta.turma')
                ->join('users p', 'p.id = th.LIG_ID_PROFESSOR');
            }
            $query->where('th.LIG_ID_PROFESSOR IN ('.implode(",", $q[4]).')');
        }

        $query->where('rac.prazo', $prazo);

        $result = $query
            ->group_by('ul.id, u.id')
            ->get($this->table." r");

        return $result->num_rows();
    }

    /**
     * get_para_professores
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_para_professores($q = array(), $limit = null, $offset = null, $sort_by = 'data_envio', $order = 'asc')
    {
        $unids = $this->_get_users_unid();
        $unids_array = array();
        if(count($unids) > 0) {
            foreach ($unids as $unid) {
                $unids_array[] = $unid;
            }
        }

        $unids_where = array();
        if($q[3]) {
            $unids_where = array_intersect($q[3], $unids_array);
        } else {
            $unids_where = $unids_array;
        }

        $turmas = $this->get_turmas_professor($this->session->userdata('user_id'));
        $turmas_array = array(0);
        if(count($turmas) > 0) {
            foreach ($turmas as $key => $turma) {
                $turmas_array[] = $key;
            }
        }

        $where = " th.LIG_ID_PROFESSOR = ".$this->session->userdata('user_id');
        $where .= " AND ta.turma IN (".implode(",", $turmas_array).")";
        // if($q[0] != "")
        //     $where .= ' AND t.ID = '.$q[0];
        if($q[0] != 1)
            $where .= ' AND rac.status = "E"';
        if($q[1] != "") {
            $where .= " AND (CONCAT(TRIM(u.first_name), ' ', TRIM(u.last_name)) LIKE '%".base64_decode(urldecode($q[1]))."%'";
            $where .= " OR CONCAT(ul.unidade, ' - ', ul.titulo) LIKE '%".base64_decode(urldecode($q[1]))."%'";
            $where .= " OR DATE_FORMAT(rac.data_envio, '%d/%m/%Y %H:%i') LIKE '%".base64_decode(urldecode($q[1]))."%')";
        }
        $where .= " AND l.empresa = '".$this->session->userdata('empresa_id')."'";

        $query = $this->db->select('u.first_name, u.last_name, e.unidade, r.data_resposta, r.aluno, ul.unidade as unidade_livro, ul.titulo, ul.tipo, rac.id as id_correcao, rac.status, rac.data_envio, CASE WHEN rac.status IN ("C") THEN "Sim" ELSE "Não" END AS status_convert, CASE WHEN ul.tipo IN ("C") THEN "Complementar" ELSE "Obrigatória" END AS tipo, DATE_FORMAT(rac.data_envio, "%d/%m/%Y %H:%i") as data_envio_formatada, CASE WHEN rac.prazo IN ("S") THEN "Sim" ELSE "Não" END AS prazo_convert')
            ->join('users u', 'r.aluno = u.id')
            ->join('pergunta_exercicio pe', 'r.pergunta = pe.id')
            ->join('exercicios e', 'pe.exercicio = e.id')
            ->join('unidade_livro ul', 'e.unidade = ul.id')
            ->join('resposta_aluno_correcao rac', 'rac.id_aluno = r.aluno AND rac.id_unidade = ul.id')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->join('livros l', 'l.id = ul.livro')
            //->join('user_unidade uu', 'uu.user_id = u.id')
            //->join('turma t', 't.unidade = uu.unidade_id')
            //->join('turma_horario th', 'th.LIG_ID_TURMA = t.ID');
            ->join('turma_aluno ta', 'ta.aluno = u.id')
            // ->join('turma t', 'ta.turma = t.ID AND t.LIG_ID_PACOTE = e.livro');

            ->join('turma t', 'ta.turma = t.ID')
            ->join('turma_horario th', 'th.LIG_ID_TURMA = t.ID');

        if($unids_where)
            $query->where('uu.unidade_id IN ('.implode(",", $unids_where).') AND '.$where);
        else
            $query->where($where);

        if($q[4]) {
            //$query->join('turma t', 't.ID = ta.turma');
            $query->where('t.ID IN ('.implode(",", $q[4]).')');
        }

        if($q[2]) {
            $query->where('rac.prazo', $q[2]);
        }

        if($limit != null)
            $query->limit($limit, $offset);
        $result = $query
            ->order_by($sort_by, $order)
            ->group_by('ul.id, u.id')
            ->get($this->table." r");

        return $result;
    }

    public function get_prof_count_prazo($q = array(), $prazo)
    {
        $unids = $this->_get_users_unid();
        $unids_array = array();
        if(count($unids) > 0) {
            foreach ($unids as $unid) {
                $unids_array[] = $unid;
            }
        }

        $unids_where = array();
        if($q[3]) {
            $unids_where = array_intersect($q[3], $unids_array);
        } else {
            $unids_where = $unids_array;
        }

        $turmas = $this->get_turmas_professor($this->session->userdata('user_id'));
        $turmas_array = array(0);
        if(count($turmas) > 0) {
            foreach ($turmas as $key => $turma) {
                $turmas_array[] = $key;
            }
        }

        $where = " th.LIG_ID_PROFESSOR = ".$this->session->userdata('user_id');
        $where .= " AND ta.turma IN (".implode(",", $turmas_array).")";

        // if($q[0] != "")
        //     $where .= ' AND t.ID = '.$q[0];
        if($q[0] != 1)
            $where .= ' AND rac.status = "E"';
        if($q[1] != "") {
            $where .= " AND (CONCAT(TRIM(u.first_name), ' ', TRIM(u.last_name)) LIKE '%".base64_decode(urldecode($q[1]))."%'";
            $where .= " OR CONCAT(ul.unidade, ' - ', ul.titulo) LIKE '%".base64_decode(urldecode($q[1]))."%'";
            $where .= " OR DATE_FORMAT(rac.data_envio, '%d/%m/%Y %H:%i') LIKE '%".base64_decode(urldecode($q[1]))."%')";
        }
        $where .= " AND l.empresa = '".$this->session->userdata('empresa_id')."'";

        $query = $this->db->select('COUNT(*)')
            ->join('users u', 'r.aluno = u.id')
            ->join('pergunta_exercicio pe', 'r.pergunta = pe.id')
            ->join('exercicios e', 'pe.exercicio = e.id')
            ->join('unidade_livro ul', 'e.unidade = ul.id')
            ->join('resposta_aluno_correcao rac', 'rac.id_aluno = r.aluno AND rac.id_unidade = ul.id')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->join('livros l', 'l.id = ul.livro')
            //->join('user_unidade uu', 'uu.user_id = u.id')
            //->join('turma t', 't.unidade = uu.unidade_id')
            //->join('turma_horario th', 'th.LIG_ID_TURMA = t.ID');
            ->join('turma_aluno ta', 'ta.aluno = u.id')
            // ->join('turma t', 'ta.turma = t.ID AND t.LIG_ID_PACOTE = e.livro');
            
            ->join('turma t', 'ta.turma = t.ID')
            ->join('turma_horario th', 'th.LIG_ID_TURMA = t.ID');


        if($unids_where)
            $query->where('uu.unidade_id IN ('.implode(",", $unids_where).') AND '.$where);
        else
            $query->where($where);

        if($q[4]) {
            //$query->join('turma t', 't.ID = ta.turma');
            $query->where('t.ID IN ('.implode(",", $q[4]).')');
        }

        $query->where('rac.prazo', $prazo);

        $result = $query
            ->group_by('ul.id, u.id')
            ->get($this->table." r");

        return $result->num_rows();
    }

    /**
     * get_users_unid
     *
     * @return array
     * @author Ellan Garbelini
     **/
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

    /**
     * get_users_unidades
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_users_unidades($id=FALSE)
    {
        // if no id was passed use the current users id
        $id || $id = $this->session->userdata('user_id');

        $query = $this->db->select('uu.*, u.unidade')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $id)
            ->get('user_unidade uu');

        $retorno = array();
        if($query->num_rows()) {
            foreach ($query->result() as $key => $value) {
                $retorno[$value->unidade_id] = $value->unidade;
            }
        }
        return $retorno;
    }

    /**
     * get_unidade
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_unidades($id = null)
    {
        $query = $this->db->select('*');
        if($id != null) {
            $query->where('id', $id);
        }
        return $query->get('unidade_livro');
    }

    /**
     * get_alunos
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_alunos($grupo_aluno, $id = null)
    {
        $query = $this->db->select('u.*, u.id as user_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', $grupo_aluno);
        if($id != null){
            $query->where('u.id', $id);
        }
        return $query->get('users u');
    }

    public function get_admin_preferences($emp_id = null) {
        if($emp_id == null)
            $emp_id = $this->session->userdata('empresa_id');

        return $this->db->get_where('admin_preferences', array('empresa_id' => $emp_id))->row();
    }

    public function get_turmas_professor($user_id = null) {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');

        $unids = $this->_get_users_unid();
        $unids_array = array();
        if(count($unids) > 0) {
            foreach ($unids as $unid) {
                $unids_array[] = $unid;
            }
        }
        
        $query = $this->db->select('t.ID, t.DESCRICAO')
            ->join('turma_horario th', 'th.LIG_ID_TURMA = t.ID')
            ->where('th.LIG_ID_PROFESSOR', $user_id);

        if($unids_array)
            $query->where('t.unidade IN ('.implode(",", $unids_array).')');

        $res = $query->group_by('t.ID')
            ->get('turma t');

        $retorno = array();
        if($res->num_rows() > 0) {
            foreach ($res->result() as $value) {
                $retorno[$value->ID] = $value->DESCRICAO;
            }
        }

        return $retorno;
    }

    public function get_turmas($user_id = null) {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');

        $unids = $this->_get_users_unid();
        $unids_array = array();
        if(count($unids) > 0) {
            foreach ($unids as $unid) {
                $unids_array[] = $unid;
            }
        }
        
        $query = $this->db->select('t.ID, t.DESCRICAO, l.titulo')
            ->join('livros l', 'l.id = t.LIG_ID_PACOTE');

        if($unids_array)
            $query->where('t.unidade IN ('.implode(",", $unids_array).')');

        $res = $query->group_by('t.ID')
            ->get('turma t');

        $retorno = array();
        if($res->num_rows() > 0) {
            foreach ($res->result() as $value) {
                $retorno[$value->ID] = $value->DESCRICAO." - ".$value->titulo;
            }
        }

        return $retorno;
    }

    public function get_professores($user_id = null) {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');

        $unids = $this->_get_users_unid();
        $unids_array = array();
        if(count($unids) > 0) {
            foreach ($unids as $unid) {
                $unids_array[] = $unid;
            }
        }

        $pref = $this->get_admin_preferences();

        $res = $this->db->select('u.*')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->where('ug.group_id', $pref->grupo_professor)
            ->where('uu.unidade_id IN ('.implode(",", $unids_array).')')
            ->where('u.active', 1)
            ->get('users u');

        $retorno = array();
        if($res->num_rows() > 0) {
            foreach ($res->result() as $value) {
                $retorno[$value->id] = $value->first_name." ".$value->last_name;
            }
        }

        return $retorno;
    }

    public function get_exercicios_respostas_enviadas($unidade, $aluno) {
        return $this->db->select('e.id, e.ordem_exercicio, e.titulo_exercicio, e.tipo_exercicio, e.audio, e.arquivo, ul.unidade, rac.id as id_correcao, rac.status, rac.data_envio, rac.prazo')
            ->join('pergunta_exercicio pe', 'pe.id = ra.pergunta')
            ->join('exercicios e', 'e.id = pe.exercicio')
            ->join('unidade_livro ul', 'ul.id = e.unidade')
            ->join('users u', 'u.id = ra.aluno')
            ->join('resposta_aluno_correcao rac', 'rac.id_aluno = ra.aluno AND e.unidade = rac.id_unidade')
            ->where('u.id', $aluno)
            ->where('e.unidade', $unidade)
            ->group_by('e.id')
            ->order_by('e.ordem_exercicio, e.id')
            ->get($this->table." ra");
    }

    public function _answers_keys($exercicio_id) {
        $pattern = '/\((.+?)\)/is';

        $query = $this->db->select('pe.id, pe.ordem, pe.sentenca')
            ->join('pergunta_exercicio pe', 'pe.exercicio = e.id')
            ->where('e.id', $exercicio_id)
            ->get('exercicios e');

        if($query->num_rows() > 0) {
            foreach ($query->result() as $value) {
                preg_match('/\[(.*?)\]/si', $value->sentenca, $matches);
                $opcao[$value->id] = array(
                    'ordem' => $value->ordem,
                    'chave' => $matches[1]
                );
            }
        } else {
            return FALSE;
        }
        return $opcao;
    }

    public function _get_perguntas_exercicios($exercicio_id, $aluno_id){
        $query = $this->db->select('pe.id, pe.ordem, pe.sentenca, ra.id as id_resposta, ra.resposta, ra.observacao, ra.opcao')
            ->join('resposta_aluno ra', 'ra.pergunta = pe.id')
            ->join('users u', 'ra.aluno = u.id')
            ->where('pe.exercicio', $exercicio_id)
            ->where('ra.aluno', $aluno_id)
            ->get('pergunta_exercicio pe');

        if($query->num_rows() > 0) {
            foreach($query->result() as $value){
                $perguntas[$value->id] = array(
                    'ordem' => $value->ordem,
                    'sentenca' => $value->sentenca,
                    'id_resposta' => $value->id_resposta,
                    'resposta' => $value->resposta,
                    'observacao' => $value->observacao,
                    'opcao' => $value->opcao
                );
            }
        } else {
            return FALSE;
        }
        return $perguntas;
    }

    public function _replace_string($string, $tipo){
        if($tipo == 2):
            $pattern = '/\[(.*?)\]/si';
        else:
            $pattern = '/\{(.*?)\}/si';
        endif;
        return preg_replace($pattern, '__________', $string);
    }

    public function _check_tipo_tem_resposta($pergunta_id){
        $query = $this->db->select('pe.id, pe.sentenca')
            ->where('pe.id', $pergunta_id)
            ->get('pergunta_exercicio pe');

        if($query->num_rows() > 0) {
            $pergunta = $query->row();
            $correct_answer = $this->_recover_correct_answer($pergunta_id);
            
            if($correct_answer != ""):
                return TRUE;
            else:
                return FALSE;
            endif;
        } else {
            return FALSE;
        }
    }

    private function _recover_correct_answer($pergunta_id){
        $pattern = '/\((.+?)\)/is';

        $query = $this->db->select('pe.id, pe.sentenca')
            ->where('pe.id', $pergunta_id)
            ->get('pergunta_exercicio pe');

        if($query->num_rows() > 0) {
            $pergunta = $query->row();
            preg_match('/\[(.*?)\]/si', $pergunta->sentenca, $matches);
            $answer = @$matches[1];
        } else {
            return FALSE;
        }
        return $answer;
    }

    public function _check_right_answer($pergunta_id, $resposta){
        $query = $this->db->select('pe.id, pe.sentenca')
            ->where('pe.id', $pergunta_id)
            ->get('pergunta_exercicio pe');

        if($query->num_rows() > 0) {
            $pergunta = $query->row();
            $correct_answer = $this->_recover_correct_answer($pergunta_id);
            
            if($correct_answer == $resposta):
                return TRUE;
            else:
                return FALSE;
            endif;
        } else {
            return FALSE;
        }
    }

    public function _count_perguntas($unidade_id, $aluno_id){
        return $this->db->select('pe.id')
            ->join('exercicios e', 'pe.exercicio = e.id')
            ->join('unidade_livro ul', 'e.unidade = ul.id')
            ->join('resposta_aluno ra', 'ra.pergunta = pe.id')
            ->join('users u', 'ra.aluno = u.id')
            ->where('ul.id', $unidade_id)
            ->where('u.id', $aluno_id)
            ->get('pergunta_exercicio pe')
            ->num_rows();
    }

    public function registrar_correcao($params){ 
        $query = $this->db->select('id_aluno, id_unidade, id_professor')
            ->where('id_aluno', $params['aluno'])
            ->where('id_unidade', $params['unidade'])
            ->where('status', 'C')
            ->get('resposta_aluno_correcao');

        if($query->num_rows() > 0):
            $this->set_message('correcao_ja_efetuada');
            return false;
        endif;

        foreach($params['obsevacoes'] as $key => $value):
            if(!empty($value)):
                $observacao_prf = addslashes($value);
                $this->db->update('resposta_aluno', array('observacao' => $observacao_prf, 'data_observacao' => date('Y-m-d H:i:s')), array('id' => $key));
            endif;
        endforeach;

        foreach($params['correcoes'] as $key => $value):
            if(!empty($value)):
                $this->db->update('resposta_aluno', array('opcao' => $value, 'data_observacao' => date('Y-m-d H:i:s')), array('id' => $key));
            endif;
        endforeach;

        if($this->db->update('resposta_aluno_correcao', array('id_professor' => $this->session->userdata('user_id'), 'status' => 'C', 'data_correcao' => date('Y-m-d H:i:s'), 'prazo' => $params["prazo"]), array('id_aluno' => $params['aluno'], 'id_unidade' => $params['unidade']))) {
            $this->set_message('correcao_successful');
            return true;
        }
        return false;
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
