<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Chamados_model extends CI_Model
{

    protected $table = 'chamado';

    protected $messages;

    protected $errors;

    public function __construct()
    {
        parent::__construct();
        $this->messages    = array();
        $this->errors      = array();
    }

    /**
     * get_unidades_permitidas
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_unidades_permitidas($user_id, $empresa_id)
    {
        return $this->db->select('uu.unidade_id, u.unidade, u.razao_social')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->join('empresas e', 'e.id = u.empresa_id')
            ->where('uu.user_id', $user_id)
            ->where('e.id', $empresa_id)
            ->get('user_unidade uu');
    }

    /**
     * get
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get($data = array('select' => 'c.*', 'order_by' => array('c.id', 'desc')), $limit = null, $offset = null, $group_by = null, $having = "")
    {
        $query = $this->db->select($data['select']);
        if (@$data['join']) {
            foreach ($data['join'] as $join) {
                $query->join($join[0], $join[1], $join[2]);
            }
        }
        if (@$data['where']) {
            $query->where($data['where']);
        }

        if ($group_by != null) {
            $query->group_by($group_by);
        }
        if ($having != "") {
            $query->having($having);
        }

        $query->order_by($data['order_by'][0], $data['order_by'][1]);
        if ($limit)
            $query->limit($limit, $offset);
        $query->distinct();
        return $query->get($this->table . ' as c');
    }

    /**
     * rows
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function countAll($where = "")
    {
        $query = $this->db->select();
        if ($where != "")
            $query->where($where);
        return $query->get($this->table)->num_rows();
        //return $this->db->count_all($this->table);
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

    /**
     * get_status_n_obrigatorios
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_status_n_obrigatorios($resolvido = false)
    {
        $query = $this->db->select('id, descricao, status, created');
        if ($resolvido == false) {
            $query->where('(status = 1 AND automatico = 0 AND id != 3) OR id = 1');
        } else {
            $query->where('(status = 1 AND automatico = 0) OR id = 1');
        }
        $query->order_by('descricao', 'asc');
        return $query->get($this->table . "_status");
    }

    /**
     * get_status
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_status($id = null)
    {
        $query = $this->db->select('id, descricao, status, created');
        if ($id != null)
            $query->where('id', $id);
        return $query->get($this->table . "_status");
    }

    /**
     * get_prioridades
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_prioridades()
    {
        $query = $this->db->select('*');
        $query->where('status', '1');
        $query->order_by('descricao', 'asc');
        return $query->get($this->table . "_prioridade");
    }

    /**
     * get_planos_acao
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_planos_acao()
    {
        $query = $this->db->select('*');
        $query->where('empresa_id', $this->session->userdata('empresa_id'));
        $query->order_by('desc', 'asc');
        return $query->get("plano_acao");
    }

    /**
     * get_dialogos
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_dialogos($id = null)
    {
        if ($id == null) {
            return false;
        }

        $query_pref = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();

        $where = 'd.chamado_id = ' . $id;
        if (count(unserialize($query_pref->grupos_responsavel_chamado)) > 0 || count(unserialize($query_pref->grupos_dev_chamado)) > 0) {
            $wheres = array_merge(unserialize($query_pref->grupos_responsavel_chamado), unserialize($query_pref->grupos_dev_chamado));
            $where_in_responsavel = @implode(",", array_unique($wheres));
            $q_responsavel = $this->db->select('u.id, u.first_name, u.last_name')
                ->join('users u', 'ug.user_id = u.id')
                ->join('groups g', 'g.id = ug.group_id')
                ->where('ug.group_id IN (' . $where_in_responsavel . ')')
                ->where('g.empresa_id', $this->session->userdata('empresa_id'))
                ->where('u.id', $this->session->userdata('user_id'))
                ->distinct()
                ->get('users_groups ug');

            if ($q_responsavel->num_rows() > 0) {
                $where .= ' AND (d.interno = 0 OR d.interno = 1)';
            } else {
                $where .= ' AND d.interno = 0';
            }
        } else {
            $where .= ' AND d.interno = 0';
        }

        $query = $this->db->select('d.*, u.first_name, u.last_name');
        $query->join('users u', 'u.id = d.responsavel_id', 'left');
        $query->where($where);
        $query->order_by('d.data_envio', 'desc');
        return $query->get($this->table . "_dialogo d");
    }

    public function get_user_name($id)
    {
        if ($id == null) {
            return false;
        }
        $query = $this->db->get_where('users', array('id' => $id))->row();
        return $query->first_name . " " . $query->last_name;
    }

    public function get_status_name($id)
    {
        if ($id == null) {
            return false;
        }
        $query = $this->db->get_where('chamado_status', array('id' => $id))->row();
        return $query->descricao;
    }

    /**
     * create_dialogo
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function create_dialogo($data = null)
    {
        if ($data == null) {
            return false;
        }
        $this->db->insert('chamado_dialogo', $data);
        $id = $this->db->insert_id();

        $this->set_message('creation_dialog_successful');
        return $id;
    }

    /**
     * get_anexos
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_anexos($id = null)
    {
        if ($id == null) {
            return false;
        }
        $query = $this->db->select('a.*, u.first_name, u.last_name');
        $query->join('users u', 'u.id = a.responsavel_id', 'left');
        $query->where('a.chamado_id', $id);
        $query->order_by('a.created', 'desc');
        return $query->get($this->table . "_anexo a");
    }

    /**
     * create_anexo
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function create_anexo($data = null)
    {
        if ($data == null) {
            return false;
        }
        $this->db->insert('chamado_anexo', $data);
        $id = $this->db->insert_id();

        $this->set_message('creation_anexo_successful');
        return $id;
    }

    /**
     * get_avaliacoes
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_avaliacoes()
    {
        $query = $this->db->select('ap.*');
        $query->where('ap.actived', '1');
        $query->order_by('ap.descricao', 'asc');
        return $query->get("avaliacao_pergunta ap");
    }

    /**
     * get_resposta
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_resposta($data)
    {
        $query = $this->db->select('ca.*');
        $query->where('ca.pergunta_id', $data['pergunta']);
        $query->where('ca.chamado_id', $data['chamado']);
        $query->limit(1);
        return $query->get("chamado_avaliacao ca");
    }

    /**
     * salvar_avaliacao_chamado
     *
     * @return boolean
     * @author Ellan Garbelini
     **/
    public function salvar_avaliacao_chamado($data)
    {
        $verifica_existencia = $this->db->select('id')->where("chamado_id = '" . $data['chamado_id'] . "' AND pergunta_id = '" . $data['pergunta_id'] . "'")->get('chamado_avaliacao')->row();

        if (!$verifica_existencia) {
            $this->db->insert('chamado_avaliacao', $data);
            $id = $this->db->insert_id();
            return $id;
        } else {
            if ($this->ion_auth->has_permissao("chamados", "desavaliar_chamado")) {
                $this->db->update('chamado_avaliacao', $data, array('id' => $verifica_existencia->id));

                $return = $this->db->affected_rows() == 1;
                return $return;
            }
        }
    }

    /**
     * excluir_avaliacao_chamado
     *
     * @return boolean
     * @author Ellan Garbelini
     **/
    public function excluir_avaliacao_chamado($data)
    {
        $verifica_existencia = $this->db->select('id')->where("chamado_id = '" . $data['chamado_id'] . "' AND pergunta_id = '" . $data['pergunta_id'] . "'")->get('chamado_avaliacao')->row();
        if (!$verifica_existencia) {
            return FALSE;
        } else {
            $this->db->delete('chamado_avaliacao', array('id' => $verifica_existencia->id));
            $return = $this->db->affected_rows() == 1;
            return $return;
        }
    }

    public function can_avaliado($id = null)
    {
        if ($id == null)
            return false;

        $qtd_aval = $this->db->get_where('avaliacao_pergunta', array('actived' => 1))->num_rows();
        $qtd_aval_chamado = $this->db->get_where('chamado_avaliacao', array('chamado_id' => $id))->num_rows();
        if ($qtd_aval == $qtd_aval_chamado)
            return true;
        else
            return false;
    }

    /**
     * get_produtos_habilitados
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_produtos_habilitados($unidade_id)
    {
        return $this->db->select('p.id, p.nome, hp.contrato_sax')
            ->join('habilitacoes_produto hp', 'hp.id_produto = p.id')
            ->where('hp.id_cliente', $unidade_id)
            ->where('hp.status', 'S')
            ->order_by('p.nome', 'asc')
            ->get('produtos p');
    }

    /**
     * get_modulos_habilitados
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_modulos_habilitados($unidade_id, $produto_id)
    {
        return $this->db->select('m.id, m.nome')
            ->join('habilitacoes_modulo hm', 'hp.id = hm.id_habilitacao')
            ->join('modulos m', 'm.id = hm.id_modulo')
            ->where('hp.id_cliente', $unidade_id)
            ->where('hp.id_produto', $produto_id)
            ->where('hp.status', 'S')
            ->where('hm.status IN("L","C")')
            ->group_by('m.id')
            ->order_by('m.nome', 'asc')
            ->get('habilitacoes_produto hp');
    }

    /**
     * get_modulo_categorias
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_modulo_categorias($modulo_id)
    {
        $is_responsavel = $this->is_full_create();
        $where = "";

        if ($is_responsavel == false) {
            $query_pref = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();

            if ($query_pref->cats_grupo_responsavel != null) {
                if (count(unserialize($query_pref->cats_grupo_responsavel)) > 0) {
                    $where = 'id NOT IN (' . implode(",", unserialize($query_pref->cats_grupo_responsavel)) . ')';
                }
            }
        }

        $query = $this->db->select('id, nome')
            ->where('id_modulo', $modulo_id)
            ->where('status', "S");
        if ($is_responsavel == false && $where != "") {
            $query->where($where);
        }

        return $query->order_by('nome', 'asc')->get('modulos_categoria');
    }

    /**
     * get_catalogos_servico_ativos
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_catalogos_servico_ativos($categoria_id = null)
    {
        $query = $this->db->select('id, nivel_id, descricao, tempo_padrao, prazo_padrao, created, actived')
            ->where('actived', 1);
        if ($categoria_id != null)
            $query->where('modulo_categoria_id', $categoria_id);

        return $query->order_by('descricao', 'asc')
            ->get('catalogo_servico');
    }

    /**
     * get_parametro
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_parametro()
    {
        return $this->db->select("id, expediente_inicio, expediente_fim, user_id")
            ->limit(1)
            ->get("chamado_parametro");
    }

    /**
     * get_catalogo_servico
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_catalogo_servico($servico_id)
    {
        return $this->db->select('id, nivel_id, descricao, tempo_padrao, prazo_padrao, created, actived')
            ->where('id', $servico_id)
            ->limit(1)
            ->get('catalogo_servico');
    }

    public function get_produto($id)
    {
        return $this->db->select('id, nome')
            ->where('id', $id)
            ->limit(1)
            ->get('produtos');
    }

    public function get_modulo($id)
    {
        return $this->db->select('id, nome')
            ->where('id', $id)
            ->limit(1)
            ->get('modulos');
    }

    public function get_mcategoria($id)
    {
        return $this->db->select('id, nome, layout')
            ->where('id', $id)
            ->limit(1)
            ->get('modulos_categoria');
    }

    /**
     * get_users_unidade
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_users_unidade($unidade_id)
    {
        return $this->db->select('u.*')
            ->join('users u', 'u.id = uu.user_id')
            ->where('uu.unidade_id', $unidade_id)
            ->where('u.active', 1)
            ->order_by('u.first_name', 'asc')
            ->get('user_unidade uu');
    }

    /**
     * get_users_interessados_unidade
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_users_interessados_unidade($unidade_id)
    {
        $query_pref = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();
        $query = $this->db->select('u.*')
            ->join('users u', 'u.id = uu.user_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->join('groups g', 'g.id = ug.group_id')
            ->where('g.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.unidade_id', $unidade_id)
            ->where('u.active', 1);

        if (count(unserialize($query_pref->grupos_responsavel_chamado)) > 0) {
            $query->where('ug.group_id NOT IN (' . implode(",", unserialize($query_pref->grupos_responsavel_chamado)) . ')');
        }

        $result = $query->order_by('u.first_name', 'asc')
            ->distinct()
            ->get('user_unidade uu');

        return $result;
    }

    /**
     * get_groups
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_groups()
    {
        $return = array();
        $query_pref = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();

        if (count(unserialize($query_pref->grupos_responsavel_chamado)) > 0) {
            $where_in_responsavel = @implode(",", unserialize($query_pref->grupos_responsavel_chamado));
            $q_responsavel = $this->db->select('u.id, u.first_name, u.last_name')
                ->join('users u', 'ug.user_id = u.id')
                ->join('groups g', 'g.id = ug.group_id')
                ->where('ug.group_id IN (' . $where_in_responsavel . ')')
                ->where('g.empresa_id', $this->session->userdata('empresa_id'))
                ->distinct()
                ->get('users_groups ug');

            $return = $q_responsavel;
        }

        return $return;
    }

    public function get_groups_dev()
    {
        $return = array();
        $query_pref = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();

        if ($query_pref->grupos_dev_chamado) {
            if (count(unserialize($query_pref->grupos_dev_chamado)) > 0) {
                $where_in_dev = @implode(",", unserialize($query_pref->grupos_dev_chamado));
                $q_dev = $this->db->select('u.id, u.first_name, u.last_name, CONCAT(u.first_name, " ", u.last_name) as nome, u.email')
                    ->join('users u', 'ug.user_id = u.id')
                    ->join('groups g', 'g.id = ug.group_id')
                    ->where('ug.group_id IN (' . $where_in_dev . ')')
                    ->where('g.empresa_id', $this->session->userdata('empresa_id'))
                    ->distinct()
                    ->get('users_groups ug');

                $return = $q_dev;
            }
        }

        return $return;
    }

    /**
     * is_full_create
     *
     * @return bool
     * @author Ellan Garbelini
     **/
    public function is_full_create()
    {
        $q_responsavel = $this->db->select('ug.*')
            ->join('groups g', 'g.id = ug.group_id')
            ->where('ug.user_id', $this->session->userdata('user_id'))
            ->where('g.empresa_id', $this->session->userdata('empresa_id'))
            ->get('users_groups ug');

        if ($q_responsavel->num_rows() > 0) {
            $query_pref = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();
            $grupos_responsavel_chamado = unserialize($query_pref->grupos_responsavel_chamado);

            foreach ($q_responsavel->result() as $user) {
                if (in_array($user->group_id, $grupos_responsavel_chamado)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function get_email($user_id)
    {
        return $this->db->get_where('users', array('id' => $user_id))->row()->email;
    }

    public function get_emails($user_ids)
    {
        if (empty($user_ids)) return array();
        return $this->db->select('id, email')
            ->where_in('id', $user_ids)
            ->get('users')
            ->result_array();
    }

    public function get_users_in_groups($user_ids, $group_ids)
    {
        if (empty($user_ids) || empty($group_ids)) return array();
        $res = $this->db->select('user_id')
            ->where_in('user_id', $user_ids)
            ->where_in('group_id', $group_ids)
            ->distinct()
            ->get('users_groups')
            ->result_array();
        
        $ids = array();
        foreach($res as $row) {
            $ids[] = $row['user_id'];
        }
        return $ids;
    }

    public function get_usuarios()
    {
        $query_unids = $this->db->select('uu.*');
        $query_unids->join("unidades u", "u.id = uu.unidade_id");
        $query_unids->where("u.empresa_id", $this->session->userdata('empresa_id'));
        $query_unids->where("uu.user_id", $this->session->userdata('user_id'));
        $query_unids = $query_unids->get("user_unidade uu");

        $unidades_where = array();
        foreach ($query_unids->result() as $unid) {
            $unidades_where[] = $unid->unidade_id;
        }

        $q = $this->db->select('u.id, CONCAT(u.first_name, " ", u.last_name) as nome, u.email')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->join('unidades un', 'uu.unidade_id = un.id')
            ->where('un.empresa_id', $this->session->userdata('empresa_id'))
            ->where('u.active', 1)
            ->where("un.id IN (" . implode(",", $unidades_where) . ")")
            ->order_by('nome asc')
            ->distinct()
            ->get('users u');

        return $q;
    }

    public function get_plano_acao($where = "")
    {
        $q = $this->db->select('p.*')
            ->where("p.empresa_id", $this->session->userdata('empresa_id'));

        if ($where != "") {
            $q->where("(" . $where . ")");
        }

        $res = $q->get('plano_acao p');

        return $res;
    }

    public function get_apontamentos($chamado_id = null, $where = "")
    {
        $query = $this->db->select('a.*, CONCAT(u.first_name, " ", u.last_name) as nome_usuario, upper(un.unidade) as client_apont, c.titulo as chamado_titulo, t.descricao as tipo_descricao');
        $query->join('users u', 'u.id = a.user_id');
        $query->join('chamado c', 'c.id = a.chamado_id');
        $query->join('unidades un', 'un.id = c.unidade_id');
        $query->join('chamado_apontamento_tipo t', 't.id = a.tipo', 'left');
        if ($chamado_id != null)
            $query->where('a.chamado_id', $chamado_id);
        $query->where('a.data >=', date('Y-m-d', strtotime('-1 year')));
        if ($where != "")
            $query->where($where);
        return $query->get("chamado_apontamento a");
    }

    public function get_apontamentos_relatorio($filtros)
    {
        $this->db->select(
            'a.id, a.chamado_id, a.user_id, a.data, a.hora_inicio, a.hora_termino, a.esforco, a.observacao, ' .
            'TIME_TO_SEC(a.esforco) AS segundos, CONCAT(u.first_name, " ", u.last_name) AS nome_usuario, ' .
            'un.id AS unidade_id, un.unidade AS cliente, c.titulo AS chamado_titulo, st.descricao AS status',
            false
        )
            ->from('chamado_apontamento a')
            ->join('users u', 'u.id = a.user_id')
            ->join('chamado c', 'c.id = a.chamado_id')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->join('chamado_status st', 'st.id = c.status_id', 'left')
            ->where('un.empresa_id', (int) $this->session->userdata('empresa_id'))
            ->where_in('c.unidade_id', $filtros['unidades_permitidas'] ? $filtros['unidades_permitidas'] : array(0))
            ->where('a.data >=', $filtros['inicio'])
            ->where('a.data <=', $filtros['fim']);
        if (!empty($filtros['usuarios'])) {
            $this->db->where_in('a.user_id', $filtros['usuarios']);
        }
        if (!empty($filtros['clientes'])) {
            $this->db->where_in('c.unidade_id', $filtros['clientes']);
        }
        if (!empty($filtros['chamado'])) {
            $this->db->where('a.chamado_id', (int) $filtros['chamado']);
        }
        return $this->db->order_by('a.data', 'desc')->order_by('a.hora_inicio', 'desc')->get()->result();
    }

    // Confere se o apontamento pertence a um chamado da empresa logada.
    public function apontamento_da_empresa($apontamento_id)
    {
        return $this->db->from('chamado_apontamento a')
            ->join('chamado c', 'c.id = a.chamado_id')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->where('a.id', (int) $apontamento_id)
            ->where('un.empresa_id', (int) $this->session->userdata('empresa_id'))
            ->count_all_results() > 0;
    }

    
    public function salvar_apontamento($data)
    {
        $hora_termino = explode(" ", @$data['hora_termino']);
        if (@$data["apontamento_id"]) {
            $verifica_existencia = $this->db->select('id')->where("id = '" . $data['apontamento_id'] . "'")->get('chamado_apontamento')->row();
            if ($verifica_existencia) {
                $this->db->update('chamado_apontamento', array('data' => convertDateBRToEU($data['data']), 'hora_inicio' => $data['hora_inicio'], 'hora_termino' => $hora_termino[0], 'esforco' => $data['esforco'], 'observacao' => addslashes($data['observacao']), 'tipo' => @$data['tipo']), array('id' => $data["apontamento_id"]));
            }
            return true;
        } else {
            $this->db->insert('chamado_apontamento', array('chamado_id' => $data['chamado_id'], 'user_id' => $this->session->userdata('user_id'), 'data' => convertDateBRToEU($data['data']), 'hora_inicio' => $data['hora_inicio'], 'hora_termino' => $hora_termino[0], 'esforco' => @$data['esforco'], 'observacao' => addslashes(@$data['observacao']), 'tipo' => @$data['tipo'], 'created_at' => date('Y-m-d H:i:s')));
            $id = $this->db->insert_id();

            return $id;
        }
    }

    public function excluir_apontamento($apontamento_id)
    {
        $this->db->delete('chamado_apontamento', array('id' => $apontamento_id));
        $return = $this->db->affected_rows() == 1;
        if ($return) {
            return true;
        } else {
            return false;
        }
    }

    public function get_apontamento_ativo($chamado_id = null)
    {
        $query = $this->db->select('a.*');
        if ($chamado_id != null) {
            $query->where('a.chamado_id', $chamado_id);
        }
        $query->where('a.user_id', $this->session->userdata('user_id'));
        $query->where('a.esforco', "00:00:00");
        $query->where('a.hora_termino', "00:00:00");
        $query->order_by("a.created_at", "desc");
        $res = $query->get("chamado_apontamento a");
        return $res;
    }

    public function activate($id = NULL)
    {
        if (!isset($id)) {
            $this->set_error('activate_unsuccessful');
            return FALSE;
        }

        $data = array(
            'actived' => 's'
        );

        $this->db->update($this->table, $data, array('id' => $id));

        $return = $this->db->affected_rows() == 1;
        if ($return) {
            $this->set_message('activate_successful');
        } else {
            $this->set_error('activate_unsuccessful');
        }
        return $return;
    }


    public function deactivate($id = NULL)
    {
        if (!isset($id)) {
            $this->set_error('deactivate_unsuccessful');
            return FALSE;
        }

        $data = array(
            'actived' => 'n'
        );

        $this->db->update($this->table, $data, array('id' => $id));

        $return = $this->db->affected_rows() == 1;
        if ($return)
            $this->set_message('deactivate_successful');
        else
            $this->set_error('deactivate_unsuccessful');

        return $return;
    }

    public function salvar_prioridade($chamado_id, $prioridade)
    {
        $this->db->update('chamado', array("prioridade_id" => $prioridade), array('id' => $chamado_id));
    }

    public function atualizar_plano_acao($data, $id)
    {
        $this->db->update('plano_acao', $data, array('id' => $id));
    }

    public function set_message($message)
    {
        $this->messages[] = $message;

        return $message;
    }

    public function messages()
    {
        $_output = '';
        foreach ($this->messages as $message) {
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
        foreach ($this->errors as $error) {
            $errorLang = $this->lang->line($error) ? $this->lang->line($error) : '' . $error . '';
            $_output .= $errorLang;
        }

        return $_output;
    }

    public function insert_tarefa($data = array())
    {

        if ($this->db->insert("kanban_tarefa", $data)) {
            $id = $this->db->insert_id();
            $this->set_message('tarefa_created');
            return $id;
        } else {
            $this->set_error('tarefa_uncreated');
            return FALSE;
        }
    }

    public function get_tarefas($quadro = null, $coluna = null)
    {
        if ($quadro == null)
            return false;

        $query = $this->db->select('t.id')
            ->where('t.quadro', $quadro);

        if ($coluna != null)
            $query->where('t.coluna', $coluna);

        $query->order_by('t.coluna', 'asc');
        $query->order_by('t.ordem', 'asc');

        $res = $query->get("kanban_tarefa t");
        return $res;
    }

    public function get_quadros($id = null, $where = "", $group_by = "")
    {
        $query = $this->db->select('q.*')
            ->join('kanban_tarefa t', 't.quadro = q.id', 'left')
            ->where('q.empresa_id', $this->session->userdata('empresa_id'));

        if ($where != "")
            $query->where($where);

        if ($id != null)
            $query->where('q.id', $id);

        if ($group_by != "")
            $query->group_by($group_by);

        return $query->get("kanban_quadro q");
    }

    public function get_coluna($quadro = null)
    {
        if ($quadro == null)
            return false;

        // Recupera a coluna com o nome dinâmico correspondente ao status do chamado
        $query = $this->db->select('kanban_quadro_coluna.*, COALESCE(chamado_status.descricao, kanban_quadro_coluna.nome) as nome')
            ->join('chamado_status', 'chamado_status.id = kanban_quadro_coluna.status', 'left')
            ->where('kanban_quadro_coluna.quadro', $quadro)
            ->where('kanban_quadro_coluna.ordem', '1');

        return $query->get("kanban_quadro_coluna");
    }

    public function get_quadro_atual($tarefa)
    {
        if ($tarefa == null)
            return false;

        $query = $this->db->select('quadro')
            ->where('tarefa', $tarefa)
            ->order_by('id', 'desc')
            ->limit(1);

        return $query->get("kanban_tarefa");
    }

    public function get_niveis_ativos($servico_id = null)
    {
        $query = $this->db->select('n.id, n.descricao, n.actived')
            ->where('n.actived', 1)
            ->where('n.empresa_id', $this->session->userdata('empresa_id'));
        if ($servico_id != null) {
            $query->join('catalogo_servico s', 's.nivel_id = n.id');
            $query->where('s.id', $servico_id);
        }

        return $query->order_by('n.descricao', 'asc')
            ->get('nivel n');
    }

    /**
     * Associar checklists a um chamado
     * 
     * @param int $chamado_id
     * @param array $checklists_selecionados
     * @return bool
     */
    public function associar_checklists($chamado_id, $checklists_selecionados)
    {
        $this->load->model('checklists_model');
        $this->load->model('checklists_etapas_model');
        $this->load->model('chamados_checklists_model');
        $this->load->model('chamados_checklists_etapas_model');

        $success = true;

        foreach ($checklists_selecionados as $item) {
            $checklist_id = $item['checklist_id'];
            $etapas_selecionadas = isset($item['etapas']) ? $item['etapas'] : [];

            // Buscar o checklist original
            $checklist_original = $this->checklists_model->get($checklist_id);

            if (!$checklist_original) {
                continue;
            }

            // Inserir o checklist no chamado
            $chamado_checklist_data = array(
                'chamado_id' => $chamado_id,
                'checklist_original_id' => $checklist_original->id,
                'nome' => $checklist_original->nome,
                'descricao' => $checklist_original->descricao,
                'controlador' => $checklist_original->controlador,
                'metodo' => $checklist_original->metodo,
                'data_criacao' => date('Y-m-d H:i:s'),
                'status' => 1
            );

            $chamado_checklist_id = $this->chamados_checklists_model->insert($chamado_checklist_data);

            if (!$chamado_checklist_id) {
                $success = false;
                continue;
            }

            // Se não selecionou etapas específicas, busca todas do checklist
            if (empty($etapas_selecionadas)) {
                $etapas = $this->checklists_etapas_model->get_by_checklist($checklist_id);

                $etapas_data = array();
                foreach ($etapas as $etapa) {
                    $etapas_data[] = array(
                        'chamado_id' => $chamado_id,
                        'chamado_checklist_id' => $chamado_checklist_id,
                        'etapa_original_id' => $etapa->id,
                        'nome' => $etapa->nome,
                        'descricao' => $etapa->descricao,
                        'data_inicio' => $etapa->data_inicio,
                        'data_prazo' => $etapa->data_prazo,
                        'responsavel' => $etapa->responsavel,
                        'ordem' => $etapa->ordem,
                        'concluida' => 0,
                        'status' => 1,
                        'data_criacao' => date('Y-m-d H:i:s')
                    );
                }

                if (!empty($etapas_data)) {
                    $this->chamados_checklists_etapas_model->insert_batch($etapas_data);
                }
            } else {
                // Inserir apenas as etapas selecionadas
                $etapas_data = array();
                foreach ($etapas_selecionadas as $etapa_id) {
                    $etapa = $this->checklists_etapas_model->get($etapa_id);

                    if ($etapa) {
                        $etapas_data[] = array(
                            'chamado_id' => $chamado_id,
                            'chamado_checklist_id' => $chamado_checklist_id,
                            'etapa_original_id' => $etapa->id,
                            'nome' => $etapa->nome,
                            'descricao' => $etapa->descricao,
                            'data_inicio' => $etapa->data_inicio,
                            'data_prazo' => $etapa->data_prazo,
                            'responsavel' => $etapa->responsavel,
                            'ordem' => $etapa->ordem,
                            'concluida' => 0,
                            'status' => 1,
                            'data_criacao' => date('Y-m-d H:i:s')
                        );
                    }
                }

                if (!empty($etapas_data)) {
                    $this->chamados_checklists_etapas_model->insert_batch($etapas_data);
                }
            }
        }

        return $success;
    }

    /**
     * Get checklists associados a um chamado
     * 
     * @param int $chamado_id
     * @return array
     */
    public function get_checklists_do_chamado($chamado_id)
    {
        $this->load->model('chamados_checklists_model');
        return $this->chamados_checklists_model->get_com_etapas($chamado_id);
    }

    public function get_chamados_pendentes_user($user_id, $empresa_id = null)
    {
        if (!$user_id) {
            return array();
        }

        $this->db->select('c.id, c.protocolo, c.titulo, c.descricao, c.created, c.data_prazo, c.status_id, cst.descricao as status_desc, cp.descricao as prioridade_desc, un.unidade as cliente')
            ->from($this->table . ' c')
            ->join('chamado_status cst', 'cst.id = c.status_id', 'inner')
            ->join('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'left')
            ->join('unidades un', 'un.id = c.unidade_id', 'left')
            ->where('c.status_id', 1)
            ->group_start()
                ->where('c.responsavel_id', (int) $user_id)
                ->or_where('c.desenvolvedor_id', (int) $user_id)
                ->or_where('c.dono_id', (int) $user_id)
            ->group_end();

        if ($empresa_id) {
            $this->db->where('un.empresa_id', (int) $empresa_id);
        }

        $this->db->order_by('c.id', 'desc');

        return $this->db->get()->result();
    }
}
