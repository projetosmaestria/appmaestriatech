<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kanban_model extends CI_Model {

    protected $table = 'kanban_quadro';

    protected $messages;

    protected $errors;

    public function __construct()
    {
        parent::__construct();
        $this->messages    = array();
        $this->errors      = array();
        $this->load->model('chamados_model');
    }

    /**
     * get
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_quadros($id = null)
    {
        $query = $this->db->select('*')
            ->where('empresa_id', $this->session->userdata('empresa_id'));

        if($id != null)
            $query->where('id', $id);

        return $query->get("kanban_quadro");
    }

    
    public function get_colunas($quadro = null)
    {
        if($quadro == null)
            return false;

        // Recupera as colunas do Kanban com nomes baseados nos status dos chamados
        $query = $this->db->select('kanban_quadro_coluna.*, COALESCE(chamado_status.descricao, kanban_quadro_coluna.nome) as nome')
            ->join('chamado_status', 'chamado_status.id = kanban_quadro_coluna.status', 'left')
            ->where('kanban_quadro_coluna.quadro', $quadro)
            ->order_by('kanban_quadro_coluna.ordem', 'asc')
            ->get("kanban_quadro_coluna");

        return $query;
    }

    public function get_coluna_chamado($quadro = null)
    {
        if($quadro == null)
            return false;

        // Retorna a primeira coluna com o nome dinâmico
        $query = $this->db->select('kanban_quadro_coluna.*, COALESCE(chamado_status.descricao, kanban_quadro_coluna.nome) as nome')
            ->join('chamado_status', 'chamado_status.id = kanban_quadro_coluna.status', 'left')
            ->where('kanban_quadro_coluna.quadro', $quadro)
            ->where('kanban_quadro_coluna.ordem', '1');

        return $query->get("kanban_quadro_coluna");
    }

    public function get_coluna($id = null)
    {
        if($id == null)
            return false;

        // Retorna os dados da coluna pelo ID com nome dinâmico
        $query = $this->db->select('kanban_quadro_coluna.*, COALESCE(chamado_status.descricao, kanban_quadro_coluna.nome) as nome')
            ->join('chamado_status', 'chamado_status.id = kanban_quadro_coluna.status', 'left')
            ->where('kanban_quadro_coluna.id', $id)
            ->order_by('kanban_quadro_coluna.ordem', 'asc')
            ->get("kanban_quadro_coluna");

        return $query;
    }

    public function get_coluna_by_status($quadro = null, $status = null)
    {
        if($status == 10)
            $status = 1;

        if($quadro == null)
            return false;

        // Busca a coluna pelo ID do status associado trazendo o nome dinâmico
        $query = $this->db->select('kanban_quadro_coluna.*, COALESCE(chamado_status.descricao, kanban_quadro_coluna.nome) as nome')
            ->join('chamado_status', 'chamado_status.id = kanban_quadro_coluna.status', 'left')
            ->where('kanban_quadro_coluna.quadro', $quadro)
            ->where('kanban_quadro_coluna.status', $status)
            ->order_by('kanban_quadro_coluna.ordem', 'asc')
            ->get("kanban_quadro_coluna");

        return $query;
    }
    
    public function get_atribuicao($quadro = null, $unidade = [])
    {
        $where_unidade = "";
        if (!empty($unidade)) {
            $unidades_clean = array_map('intval', (array)$unidade);
            $where_unidade = " AND c.unidade_id IN (" . implode(",", $unidades_clean) . ") ";
        }

        $where_quadro = "";
        if (!empty($quadro)) {
            $quadro_id = (int)$quadro;
            $where_quadro = " t.quadro = '".$quadro_id."' ";
        } else {
            $where_quadro = " 1 = 1 ";
        }

        $query = $this->db->query("
            (SELECT c.responsavel_id AS responsavel_id, u.email 
            FROM kanban_tarefa t 
            JOIN chamado c ON t.tarefa = c.id 
            JOIN users u ON c.responsavel_id = u.id 
            WHERE " . $where_quadro . $where_unidade . " 
            GROUP BY c.responsavel_id 
            ORDER BY u.email ASC)
            UNION 
            (SELECT c.desenvolvedor_id AS responsavel_id, u.email 
            FROM kanban_tarefa t 
            JOIN chamado c ON t.tarefa = c.id 
            JOIN users u ON c.desenvolvedor_id = u.id 
            WHERE " . $where_quadro . $where_unidade . " 
            GROUP BY c.desenvolvedor_id 
            ORDER BY u.email ASC);
        ");

        return $query;
    }


    public function get_tarefas($quadro = null, $coluna = null, $user = null, $unidade = [], $count_only = false)
    {
        if ($quadro == null) {
            return false;
        }

        if ($count_only) {
            $this->db->where('quadro', $quadro);
            if ($coluna != null) {
                $this->db->where('coluna', $coluna);
            }
            return $this->db->count_all_results("kanban_tarefa");
        }

        $query = $this->db->select('t.*, c.titulo, col.status, c.descricao, c.created, c.data_prazo, CONCAT(u.first_name, " ", u.last_name) as resp, CONCAT(d.first_name, " ", d.last_name) as dono, c.responsavel_id, u.email, c.desenvolvedor_id, CONCAT(dev.first_name, " ", dev.last_name) as dev, p.descricao as prioridade, un.unidade, c.unidade_id')
            ->join('chamado c', 't.tarefa = c.id')
            ->join('users u', 'c.responsavel_id = u.id')
            ->join('users d', 'c.dono_id = d.id')
            ->join('users dev', 'c.desenvolvedor_id = dev.id', 'left')
            ->join('kanban_quadro_coluna col', 't.coluna = col.id')
            ->join('chamado_prioridade p', 'p.id = c.prioridade_id')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->where('t.quadro', $quadro);

        if($coluna != null)
            $query->where('t.coluna', $coluna);

        if($user != null) {
            $query->where('(c.responsavel_id = '.(int)$user.' OR c.desenvolvedor_id = '.(int)$user.')');
        }

        if(!empty($unidade)) {
            $unidades_clean = array_map('intval', (array)$unidade);
            $query->where_in('c.unidade_id', $unidades_clean);
        }

        $query->order_by('t.coluna', 'asc');
        $query->order_by('t.ordem', 'asc');

        $res = $query->get("kanban_tarefa t");
        return $res;
    }

    public function insert_tarefa_user($data = array()) {
        if($data) {
            if($this->db->insert_batch("kanban_tarefa_user", $data)) {
                return true;
            } else {
                return FALSE;
            }
        }
    }

    public function get_tarefas_user($quadro = null, $coluna = null, $user = null, $unidade = [], $count_only = false)
    {
        if ($quadro == null) {
            return false;
        }

        if ($count_only) {
            $this->db->where('quadro', $quadro);
            if ($coluna != null) {
                $this->db->where('coluna', $coluna);
            }
            if ($user != null) {
                $this->db->where('user', $user);
            }
            return $this->db->count_all_results("kanban_tarefa_user");
        }

        //$query = $this->db->select('t.quadro, t.tarefa, t.coluna, t.ordem, t.user')
        $query = $this->db->select('t.*, c.titulo, col.status, c.descricao, c.created, c.data_prazo, CONCAT(u.first_name, " ", u.last_name) as resp, CONCAT(d.first_name, " ", d.last_name) as dono, c.responsavel_id, u.email, c.desenvolvedor_id, CONCAT(dev.first_name, " ", dev.last_name) as dev, p.descricao as prioridade, un.unidade, c.unidade_id')
            ->join('chamado c', 't.tarefa = c.id')
            ->join('users u', 'c.responsavel_id = u.id')
            ->join('users d', 'c.dono_id = d.id')
            ->join('users dev', 'c.desenvolvedor_id = dev.id', 'left')
            ->join('kanban_quadro_coluna col', 't.coluna = col.id')
            ->join('chamado_prioridade p', 'p.id = c.prioridade_id')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->where('t.quadro', $quadro)
            ->where('t.coluna', $coluna);

        if($user != null) {
            $query->where('t.user', (int)$user);
            $query->where('(c.responsavel_id = '.(int)$user.' OR c.desenvolvedor_id = '.(int)$user.')');
        }

        if(!empty($unidade)) {
            $unidades_clean = array_map('intval', (array)$unidade);
            $query->where_in('c.unidade_id', $unidades_clean);
        }

        $query->order_by('t.user', 'asc');
        $query->order_by('t.coluna', 'asc');
        $query->order_by('t.ordem', 'asc');

        $res = $query->get("kanban_tarefa_user t");
        return $res;
    }

    public function get_tarefa($id)
    {
        if($id == null)
            return false;

        $query = $this->db->select('t.*, c.*, CONCAT(u.first_name, " ", u.last_name) as resp, CONCAT(d.first_name, " ", d.last_name) as dono, DATE_FORMAT(c.created, "%d/%m/%Y %H:%i") as criada_em, DATE_FORMAT(c.data_prazo, "%d/%m/%Y %H:%i") as data_prazo, DATE_FORMAT(c.data_inicio, "%d/%m/%Y %H:%i") as data_inicio, DATE_FORMAT(c.data_finalizacao, "%d/%m/%Y %H:%i") as data_finalizacao, p.desc as plano_acao_desc, c.responsavel_id, c.status_id')
            ->join('chamado c', 't.tarefa = c.id')
            ->join('users u', 'c.responsavel_id = u.id')
            ->join('users d', 'c.dono_id = d.id')
            ->join('kanban_quadro_coluna col', 't.coluna = col.id')
            ->join('plano_acao p', 'p.id = c.plano_acao', 'left')
            ->where('c.id', $id);

        $res = $query->get("kanban_tarefa t");
        return $res;
    }

    public function get_tarefa_user($id, $user)
    {
        if($id == null)
            return false;

        $query = $this->db->select('t.*, c.*, CONCAT(u.first_name, " ", u.last_name) as resp, CONCAT(d.first_name, " ", d.last_name) as dono, DATE_FORMAT(c.created, "%d/%m/%Y %H:%i") as criada_em, DATE_FORMAT(c.data_prazo, "%d/%m/%Y %H:%i") as data_prazo, DATE_FORMAT(c.data_inicio, "%d/%m/%Y %H:%i") as data_inicio, DATE_FORMAT(c.data_finalizacao, "%d/%m/%Y %H:%i") as data_finalizacao, p.desc as plano_acao_desc')
            ->join('chamado c', 't.tarefa = c.id')
            ->join('users u', 'c.responsavel_id = u.id')
            ->join('users d', 'c.dono_id = d.id')
            ->join('kanban_quadro_coluna col', 't.coluna = col.id')
            ->join('plano_acao p', 'p.id = c.plano_acao', 'left')
            ->where('c.id', $id)
            ->where('t.user', $user);

        $res = $query->get("kanban_tarefa_user t");
        return $res;
    }

    public function has_tarefa_user($id, $user)
    {
        if($id == null)
            return false;

        $query = $this->db->select('t.*')
            ->where('t.tarefa', $id)
            ->where('t.user', $user);

        $res = $query->get("kanban_tarefa_user t");
        return $res;
    }


    public function get_historico($tarefa)
    {
        if($tarefa == null)
            return false;

        $query = $this->db->select('h.*, s.descricao as status_desc, DATE_FORMAT(h.created_at, "%d/%m/%Y %H:%i") as criada_em, CONCAT(u.first_name, " ", u.last_name) as responsavel')
            ->join('chamado_status s', 'h.status = s.id')
            ->join('users u', 'h.responsavel = u.id')
            ->where('h.tarefa', $tarefa)
            ->order_by('h.created_at', 'desc');

        $res = $query->get("kanban_tarefa_hist h");
        return $res;
    }

    public function get_anexo($tarefa)
    {
        if($tarefa == null)
            return false;

        $query = $this->db->select('a.*, DATE_FORMAT(a.created, "%d/%m/%Y %H:%i") as criada_em, CONCAT(u.first_name, " ", u.last_name) as responsavel')
            ->join('users u', 'a.responsavel_id = u.id')
            ->where('a.chamado_id', $tarefa)
            ->order_by('a.created', 'desc');

        $res = $query->get("chamado_anexo a");
        return $res;
    }


    public function get_apontamento($tarefa) {
        $return = $this->db->get_where('chamado_apontamento', array("chamado_id" => $tarefa));
        return $return;
    }

    public function get_apontamento_ativo($chamado_id = null) {
        $query = $this->db->select('a.*');
        if($chamado_id != null) {
            $query->where('a.chamado_id', $chamado_id);
        }
        $query->where('a.user_id', $this->session->userdata('user_id'));
        $query->where('a.esforco', "00:00:00");
        $query->where('a.hora_termino', "00:00:00");
        $query->order_by("a.created_at", "desc");
        $res = $query->get("chamado_apontamento a");
        return $res;
    }

    public function update_tarefa($data = array(), $where = array())
    {
        //if (!isset($where["quadro"]) || !isset($where["tarefa"])) return FALSE;
        $this->db->update("kanban_tarefa", $data, $where);
  
        return TRUE;
    }

    public function update_tarefa_user($data = array(), $where = array())
    {
        //if (!isset($where["quadro"]) || !isset($where["tarefa"])) return FALSE;
        $this->db->update("kanban_tarefa_user", $data, $where);
  
       return TRUE;

    }

    public function insert_tarefa_hist($data = array()) {

        if($this->db->insert("kanban_tarefa_hist", $data)) {
            $id = $this->db->insert_id();

            // $dados["chamado_id"] = $data["tarefa"];
            // $dados["responsavel_id"] = $this->session->userdata("user_id");
            // $dados["descricao"] = "Status do chamado alterado para ".$this->chamados_model->get_status_name($data['status']).".";
            // $dados["data_envio"] = date("Y-m-d H:i:s");

            // $chamado = $this->chamados_model->get(array(
            //     'select'=>'c.id, c.dono_id, u.email as email_dono, c.status_id, c.responsavel_id, c.interessados, u.first_name, u.last_name, us.email as email_resp, un.unidade as cliente',
            //     'join'=>array(array('users u','u.id = c.dono_id','left'), array('users us','us.id = c.responsavel_id','left'), array('unidades un','un.id = c.unidade_id','inner')),
            //     'where'=>'c.id = "'.$data["tarefa"].'"',
            //     'order_by'=>array('c.id', 'desc')))->row();

            // //adicionar flag retrabalho
            // if(($chamado->status_id == 8 || $chamado->status_id == 3 || $chamado->status_id == 5) && $data['status'] == 1) {
            //     $dados["retrabalho"] = 1;
            // }

            // $this->chamados_model->create_dialogo($dados);

            $this->set_message('tarefa_hist_created');
        } else {
            $this->set_error('tarefa_hist_uncreated');
            return FALSE;
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


    public function atualizarKanban($post, $id, $incluir = false) {
        $quadros_sel = array(1);
                
        if(@$post['quadro'] && @$post['quadro'] != 1)
            $quadros_sel[] = $post['quadro'];

        foreach($quadros_sel as $q) {

            if($this->get_tarefa($id)->num_rows() == 0) {
                if($incluir) {
                    $array_sel = array();

                    if($q) {
                        $array_sel["quadro"] = $q;
                    }
                    if(isset($post['coluna']) && $post['coluna']) {
                        $array_sel["coluna"] = $post['coluna'];
                    } else {
                        $sel_primeira_coluna = $this->get_coluna_chamado($q)->row();
                        $array_sel["coluna"] = $sel_primeira_coluna->id;
                    }
                
                    $qtd_tarefas = $this->get_tarefas(@$array_sel["quadro"], @$array_sel["coluna"], null, [], true);
                    $array_qry = array("tarefa" => $id, "ordem" => $qtd_tarefas);
                    $array_qry = array_merge($array_qry, $array_sel);
                    $this->db->insert("kanban_tarefa", $array_qry);

                    $users_to_add = array();
                    if(isset($post['responsavel']) && $post['responsavel']) {
                        $users_to_add[] = $post['responsavel'];
                    }
                    if(isset($post['desenvolvedor']) && $post['desenvolvedor'] != "" && !in_array($post['desenvolvedor'], $users_to_add)) {
                        $users_to_add[] = $post['desenvolvedor'];
                    }

                    foreach($users_to_add as $user_id) {
                        $qtd_tarefas_user = $this->get_tarefas_user(@$array_sel["quadro"], @$array_sel["coluna"], $user_id, [], true);
                        if($qtd_tarefas_user >= 0) {
                            $array_qry_user = array("user" => $user_id, "tarefa" => $id, "ordem" => $qtd_tarefas_user);
                            $array_qry_user = array_merge($array_qry_user, $array_sel);
                            $this->insert_tarefa_user([$array_qry_user]);
                        }
                    }
                }
            } else {
                $tarefa = $this->get_tarefa($id);
                $post['pos'] = 9999999;
                $post['quadro'] = $q;

                if(@$post["status"]) {
                    if($this->get_coluna_by_status($q, $post["status"])->num_rows() > 0)
                        $post['coluna'] = $this->get_coluna_by_status($q, $post["status"])->row()->id;
                }
                else {
                    if($this->get_coluna_by_status($q, $tarefa->row()->status_id)->num_rows() > 0)
                        $post['coluna'] = $this->get_coluna_by_status($q, $tarefa->row()->status_id)->row()->id;
                }
                $post['tarefa'] = $id;
                $this->db->delete('kanban_tarefa_user', array('tarefa' => $id));
                if(@!$post['responsavel'])
                    $post['responsavel'] = $tarefa->row()->responsavel_id;
                
                $array_qry_user = array();
                $array_qry_user[] = array("quadro" => $q, "coluna" => $tarefa->row()->coluna, "user" => $post['responsavel'], "tarefa" => $id, "ordem" => $post['pos']);
                
                if($tarefa->row()->desenvolvedor_id != "" && $tarefa->row()->desenvolvedor_id != 0 && $post['responsavel'] != $tarefa->row()->desenvolvedor_id) {
                    $array_qry_user[] = array("quadro" => $q, "coluna" => $tarefa->row()->coluna, "user" => $tarefa->row()->desenvolvedor_id, "tarefa" => $id, "ordem" => $post['pos']);
                }
                $this->insert_tarefa_user($array_qry_user);
                if(@$post['coluna']) {
                    $post['usuario'] = $post['responsavel'];
                    $this->reodenarTarefas($q, $this->get_coluna($tarefa->row()->coluna), $tarefa, $post);
                }
            }
        }
    }

    public function reodenarTarefas($quadro, $coluna, $tarefa, $post) {
        $posicao_item = (int)$post["pos"] * 10;

        if($tarefa->row()->ordem != $posicao_item || $tarefa->row()->coluna != $post['coluna']) {
            $itens_organizar = $this->get_tarefas($post['quadro'], $post['coluna']);
            $itens_organizar_users = $this->get_tarefas_user($post['quadro'], $post['coluna'], $post['usuario']);
            
            $array_aux = $this->percorrerTarefas($itens_organizar, $post, $posicao_item, $tarefa);
            $array_aux_users = $this->percorrerTarefas($itens_organizar_users, $post, $posicao_item, $tarefa);

            foreach(array_values($array_aux) as $key => $aux) {
                $this->update_tarefa(
                    array("quadro" => $post['quadro'], "coluna" => $post['coluna'], "ordem" => $key),
                    array("tarefa" => $aux->tarefa));
                $status_change = $coluna->row()->status;
                if(@$post['status'])
                    $status_change = $post['status'];
                if($status_change == 1 && $tarefa->row()->status_id == 10) {
                    $status_change = 10;
                }
                $this->db->update("chamado", ["status_id" => $status_change], ["id" => $post['tarefa']]);
            }
            foreach(array_values($array_aux_users) as $key => $aux) {
                if(@!$aux->user) 
                    $aux->user = $aux->responsavel_id;
                $this->update_tarefa_user(
                    array("quadro" => $post['quadro'], "coluna" => $post['coluna'], "ordem" => $key),
                    array("tarefa" => $aux->tarefa));
            }

            if($this->insert_tarefa_hist(
                array(
                    "tarefa" => $post['tarefa'], 
                    "responsavel" => $this->session->userdata('user_id'), 
                    "status" => $coluna->row()->status,
                    "obs" => "Status da tarefa atualizada.",
                    "created_at" => date('Y-m-d H:i:s')
                )
            )) {
                //$retorno['data'] = $this->kanban_model->message();
            }
            
        }

    }

    private function percorrerTarefas($itens_organizar, $post, $posicao_item, $tarefa) {
        $array_aux = [];
        foreach ($itens_organizar->result() as $value) {
                    
            if($value->tarefa != $post['tarefa']) {

                $value->ordem = $value->ordem * 10;
                
                if($value->ordem > $posicao_item) {
                    $array_aux[$value->ordem + 2] = $value;
                } elseif($value->ordem <= $posicao_item) {
                    if($value->ordem != 0)
                        $array_aux[$value->ordem - 1] = $value;
                    elseif($value->ordem == 0 && $posicao_item == 0)
                        $array_aux[$value->ordem + 1] = $value;
                    else
                        $array_aux[$value->ordem] = $value;
                }
            }
        }
        $array_aux[$posicao_item] = $tarefa->row();
        ksort($array_aux, SORT_NUMERIC);

        return $array_aux;
    }

    public function getMetricasChart1($data_inicio, $data_fim, $usuario = null, $unidade = []) {
        $sql = "
            SELECT c.id, n.descricao as nivel, DATE_FORMAT(d.data_envio,'%d/%m/%Y') as data_envio FROM chamado c 
            JOIN chamado_dialogo d ON c.id = d.chamado_id
            JOIN nivel n ON n.id = c.nivel_id
            WHERE d.descricao like '%Aguardando Testes%' AND
            d.data_envio BETWEEN '".$data_inicio."' AND '".$data_fim."'
        ";

        if($usuario != null) {
            $sql .= " AND (c.desenvolvedor_id = ".(int)$usuario." OR c.responsavel_id = ".(int)$usuario.")";
        }

        if(!empty($unidade)) {
            $unidades_clean = array_map('intval', (array)$unidade);
            $sql .= " AND c.unidade_id IN (" . implode(",", $unidades_clean) . ")";
        }

        $sql .= " ORDER BY d.data_envio ASC";

        $query = $this->db->query($sql);
        return $query;
    }
    
}
