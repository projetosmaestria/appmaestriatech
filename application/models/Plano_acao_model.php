<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Plano_acao_model extends CI_Model {

    protected $table = 'plano_acao';

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
    public function get($id = null, $where = "", $order = array())
    {
        $query = $this->db->select('p.*, concat(u.first_name, " ", u.last_name) as nome_resp, CASE WHEN p.status IN ("AI") THEN "A Iniciar" WHEN p.status IN ("EA") THEN "Em Andamento" ELSE "Finalizado" END AS status')
            ->join('users u', 'u.id = p.responsavel');
        $query->where("p.empresa_id", $this->session->userdata('empresa_id'));
        if($id != null)
            $query->where('p.id', $id);
        if($where != "") {
            $query->where($where);
        }
        if($order != array()) {
            foreach ($order as $key => $_order) {
                $query->order_by($key, $_order);
            }
        }
        return $query->get($this->table." p");
    }

    public function get_users($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');

        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }
        return $this->db->select('u.*, u.id as user_id')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->join('unidades un', 'uu.unidade_id = un.id')
            ->where('un.id IN ('.implode(",", $unids_array).')')
            ->where('un.empresa_id', $this->session->userdata('empresa_id'))
            ->group_by('u.id')
            ->get("users u");
    }

    public function get_unidades($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');

        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('u.*')
            ->where('u.id IN ('.implode(",", $unids_array).')')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->get("unidades u");

         return $query;
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        if($this->db->insert($this->table, $data)) {
            $id = $this->db->insert_id();
            $this->set_message('creation_successful');
        } else {
            $this->set_error('creation_unsuccessful');
            return FALSE;
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

    public function get_apontamentos($plano_id = null)
    {   
        if($plano_id == null)
            return false;

        $query = $this->db->select('a.*');
        $query->join('chamado c', 'c.id = a.chamado_id');
        if($plano_id != null)
            $query->where('c.plano_acao', $plano_id);
        return $query->get("chamado_apontamento a");
    }

    public function get_chamado($where = null)
    {   
        $query = $this->db->select('c.*');
        if($where != null)
            $query->where($where);
        return $query->get("chamado c");
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

    public function get_tarefas($plano_id = null)
    {   
        if($plano_id == null)
            return false;

        $query = $this->db->select('c.*');
        $query->where('c.plano_acao', $plano_id);
        return $query->get("chamado c");
    }

    public function get_relatorio($id = null, $where = "", $order = array())
    {
        $query = $this->db->select('p.*, concat(u.first_name, " ", u.last_name) as nome_resp, CASE WHEN p.status IN ("AI") THEN "A Iniciar" WHEN p.status IN ("EA") THEN "Em Andamento" ELSE "Finalizado" END AS status')
            ->join('users u', 'u.id = p.responsavel');
        if($id != null)
            $query->where('p.id', $id);
        if($where != "") {
            $query->where($where);
        }
        if($order != array()) {
            foreach ($order as $key => $_order) {
                $query->order_by($key, $_order);
            }
        }
        return $query->get($this->table." p");
    }

    public function get_chamados_gantt($plano_ids)
    {
        if (!$plano_ids) {
            return array();
        }
        return $this->db->select(
            'c.id, c.plano_acao, c.titulo, c.status_id, c.created, c.data_inicio, c.data_prazo, c.data_finalizacao, ' .
            'c.horas_prevista, st.descricao AS status, n.descricao AS nivel, un.unidade, ' .
            'CONCAT(u.first_name, " ", u.last_name) AS responsavel'
        )
            ->from('chamado c')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->join('chamado_status st', 'st.id = c.status_id', 'left')
            ->join('nivel n', 'n.id = c.nivel_id', 'left')
            ->join('users u', 'u.id = c.responsavel_id', 'left')
            ->where('un.empresa_id', $this->session->userdata('empresa_id'))
            ->where_in('c.plano_acao', array_map('intval', $plano_ids))
            ->order_by('c.data_inicio IS NULL', 'asc', false)
            ->order_by('COALESCE(c.data_inicio, c.created)', 'asc', false)
            ->order_by('c.id', 'asc')
            ->get()
            ->result();
    }

    /**
     * Lista de projetos com filtros. $filters aceita: status (array de AI/EA/FI), q, responsavel,
     * unidade, tipo, inicio_de, inicio_ate, termino_de, termino_ate, atrasados.
     */
    public function get_lista($filters, $limit = null, $offset = 0)
    {
        $this->apply_lista_filters($filters);
        $this->db->select('p.*, p.status AS status_codigo, CONCAT(u.first_name, " ", u.last_name) AS nome_resp, un.unidade', false)
            ->order_by('FIELD(p.status, "EA", "AI", "FI")', '', false)
            ->order_by('p.termino_estimado', 'asc')
            ->order_by('p.id', 'desc');
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    public function count_lista($filters)
    {
        $this->apply_lista_filters($filters);
        return $this->db->count_all_results();
    }

    private function apply_lista_filters($filters)
    {
        $this->db->from($this->table . ' p')
            ->join('users u', 'u.id = p.responsavel', 'left')
            ->join('unidades un', 'un.id = p.unidade_id', 'left')
            ->where('p.empresa_id', $this->session->userdata('empresa_id'));
        if (!empty($filters['status'])) {
            $this->db->where_in('p.status', $filters['status']);
        }
        if (!empty($filters['q'])) {
            $this->db->group_start()
                ->like('p.desc', $filters['q'])
                ->or_like('p.txt', $filters['q'])
                ->or_like('CONCAT(u.first_name, " ", u.last_name)', $filters['q'], 'both', false)
                ->or_like('un.unidade', $filters['q']);
            if (ctype_digit($filters['q'])) {
                $this->db->or_where('p.id', (int) $filters['q']);
            }
            $this->db->group_end();
        }
        if (!empty($filters['responsavel'])) {
            $this->db->where('p.responsavel', (int) $filters['responsavel']);
        }
        if (!empty($filters['unidade'])) {
            $this->db->where('p.unidade_id', (int) $filters['unidade']);
        }
        if (!empty($filters['tipo'])) {
            $this->db->where('p.tipo', $filters['tipo']);
        }
        foreach (array('inicio_de' => array('p.inicio_estimado >=', ' 00:00:00'), 'inicio_ate' => array('p.inicio_estimado <=', ' 23:59:59'),
            'termino_de' => array('p.termino_estimado >=', ' 00:00:00'), 'termino_ate' => array('p.termino_estimado <=', ' 23:59:59')) as $key => $rule) {
            if (!empty($filters[$key])) {
                $this->db->where($rule[0], $filters[$key] . $rule[1]);
            }
        }
        if (!empty($filters['atrasados'])) {
            $this->db->where('p.status !=', 'FI')->where('p.termino_estimado <', date('Y-m-d H:i:s'));
        }
    }

    public function get_resumo_chamados($plano_ids)
    {
        if (!$plano_ids) {
            return array();
        }
        $rows = $this->db->select(
            'c.plano_acao, COUNT(*) AS total, ' .
            'SUM(CASE WHEN c.status_id IN (3, 5) OR c.data_finalizacao IS NOT NULL THEN 1 ELSE 0 END) AS concluidos, ' .
            'SUM(CASE WHEN c.status_id NOT IN (3, 5) AND c.data_finalizacao IS NULL AND c.data_prazo < NOW() THEN 1 ELSE 0 END) AS atrasados',
            false
        )
            ->from('chamado c')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->where('un.empresa_id', $this->session->userdata('empresa_id'))
            ->where_in('c.plano_acao', array_map('intval', $plano_ids))
            ->group_by('c.plano_acao')
            ->get()
            ->result();
        $result = array();
        foreach ($rows as $row) {
            $result[(int) $row->plano_acao] = $row;
        }
        return $result;
    }
}
