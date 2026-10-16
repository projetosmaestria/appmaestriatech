<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crons_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * get
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get($data = array('select'=>'nivel.*', 'order_by'=>array('nivel.id', 'desc')))
    {
        $query = $this->db->select($data['select']);
        if(@$data['join']) {
            foreach ($data['join'] as $join) {
                $query->join($join[0], $join[1]);
            }
        }
        if(@$data['where']) {
            $query->where($data['where']);
        }
        $query->where('empresa_id', $this->session->userdata('empresa_id'));
        $query->order_by($data['order_by'][0], $data['order_by'][1]);
        return $query->get($this->table);
    }

    public function has_evasao($data) {
        $res = $this->db->get_where('evasao', array('data' => $data));
        if($res->num_rows() > 0)
            return true;
        else
            return false;
    }

    public function get_evasao($data) {
        return $this->db->get_where('evasao', array('data' => $data));
    }

    public function insert_evasao($resultado, $data)
    {
        $this->db->insert('evasao', array('data' => $data, 'frequentes' => 0, 'evasao' => 0, 'resultado' => serialize($resultado)));
        $id = $this->db->insert_id();
        return $id;
    }

    public function update_evasao($id, $data) {
        $this->db->update('evasao', $data, array("id" => $id));
    }

    public function insert_evasao_orc($data)
    {
        $this->db->insert('evasao_orc', $data);
        $id = $this->db->insert_id();
        return $id;
    }

    public function get_orcamentos($id = null) {
        $query = $this->db->select('c.*, p.*, u.empresa_id')
            ->join('parametro p', 'p.LIG_ID_FILIAL = c.filial')
            //->join('ead_users eu', 'eu.user_id = c.user_id', 'left')
            ->join('unidades u', 'u.id = c.filial')
            ->where('c.tipo_saxweb', 2)
            ->where('c.status != p.LIG_STATUS_CONTRATO_06')
            ->where('c.status != p.LIG_ID_ANULADO')
            ->where('u.empresa_id', 3)
            // ->where('c.status != p.LIG_ID_RESGATE_FINANCEIRO')
            // ->where('c.status != p.LIG_ID_RESGATE_PEDAGOGICO')
            ->where('c.status != p.LIG_ID_CONCLUIDO');

        if($id != null) {
            $query->where('c.id', $id);
        }

        $result = $query->get('cad_orca c');
        return $result;
    }

    public function get_orcamento_user($user_id = null) {
        $query = $this->db->select('c.*, p.*')
            ->join('parametro p', 'p.LIG_ID_FILIAL = c.filial')
            //->join('ead_users eu', 'eu.user_id = c.user_id')
            ->where('c.tipo_saxweb', 2);

        if($user_id != null) {
            $query->where('c.user_id', $user_id);
        }

        $result = $query->get('cad_orca c');
        return $result;
    }

    public function update_orcamento($id, $data) {
        $this->db->update('cad_orca', $data, array("id" => $id));
    }

    public function update_user_ead($id, $data) {
        $this->db->update('ead_users', $data, array("user_id" => $id));
    }

    public function get_ultimo_exercicio($user_id) {
        $query = $this->db->select('*')
            ->where('id_aluno', $user_id)
            ->limit(1)
            ->order_by('data_envio', 'desc')
            ->get('resposta_aluno_correcao');
        return $query;
    }

    public function get_user_ead($user_id) {
        $query = $this->db->select('eu.*')
            ->join('ead_users eu', 'eu.user_id = u.id')
            ->where('u.id', $user_id)
            ->limit(1)
            ->get('users u');
        return $query;
    }

    public function historico_create($data = array())
    {
        //$data["empresa_id"] = $this->session->userdata("empresa_id");
        $this->db->insert('cad_clie_auditoria', $data);
    }

    public function get_tutores()
    {
        $retorno = array();

        $empresas_relatorio_habilitado = $this->db->select('e.*')
            ->join('admin_preferences p', 'p.empresa_id = e.id')
            ->where('p.relatorio_tutor', 1)
            ->get("empresas e");

        if($empresas_relatorio_habilitado->num_rows() > 0) {
            foreach($empresas_relatorio_habilitado->result() as $e) {
                $admin_pref = $this->db->get_where('admin_preferences', array('empresa_id'=>$e->id))->row();

                $retorno[] = $this->db->select('u.*, g.empresa_id')
                    ->join('users_groups ug', 'ug.user_id = u.id')
                    ->join('groups g', 'g.id = ug.group_id')
                    ->where('ug.group_id', $admin_pref->grupo_tutor)
                    ->group_by('u.id')
                    ->distinct()
                    ->get("users u");
            }
        }

        return $retorno;
    }

    public function get_supervisores()
    {
        $retorno = array();

        $empresas_relatorio_habilitado = $this->db->select('e.*')
            ->join('admin_preferences p', 'p.empresa_id = e.id')
            ->where('p.relatorio_supervisor', 1)
            ->get("empresas e");

        if($empresas_relatorio_habilitado->num_rows() > 0) {
            foreach($empresas_relatorio_habilitado->result() as $e) {
                $admin_pref = $this->db->get_where('admin_preferences', array('empresa_id'=>$e->id))->row();

                $retorno[] = $this->db->select('u.*, g.empresa_id')
                    ->join('users_groups ug', 'ug.user_id = u.id')
                    ->join('groups g', 'g.id = ug.group_id')
                    ->where('ug.group_id', $admin_pref->grupo_supervisor)
                    ->group_by('u.id')
                    ->distinct()
                    ->get("users u");
            }
        }

        return $retorno;
    }

    public function get_nivel_alunos($empresa_id, $id = null, $inicio = null, $fim = null) {
        /*$query = $this->db->select('t.*')
            ->join('ead_contratos_segments cs', 'cs.contrato_id = t.orc_id')
            ->join('ead_segments s', 's.id = cs.segment_id')
            ->join('cad_orca c', 'c.id = t.orc_id')
            ->join('unidades u', 'c.filial = u.id')
            ->join('parametro p', 'u.id = p.LIG_ID_FILIAL')
            ->where('c.status not in (p.LIG_ID_CONCLUIDO, LIG_ID_RESGATE_PEDAGOGICO, LIG_ID_RESGATE_FINANCEIRO, p.LIG_ID_ANULADO, p.LIG_STATUS_CONTRATO_06, p.LIG_STATUS_CONTRATO_01)');

        if($id != null)
            $query->where('s.tutor_id', $id);

        if($inicio != null) {
            $query->where("t.data_hora_fim >= '".$inicio."'");
        }
        if($fim != null) {
            $query->where("t.data_hora_fim <= '".$fim."'");
        }

        return $query->where('u.empresa_id', $empresa_id)
            ->order_by('t.orc_id', 'asc')
            ->order_by('t.id', 'desc')
            ->get("tutoria t");*/

        $query = $this->db->select('c.*')
            ->join('unidades u', 'c.filial = u.id')
            ->join('parametro p', 'u.id = p.LIG_ID_FILIAL');

        if($id != null) {
            $query->join('ead_contratos_segments cs', 'cs.contrato_id = c.id');
            $query->join('ead_segments s', 's.id = cs.segment_id');
            $query->where('s.tutor_id', $id);
        }

        // $query->where('c.status not in (p.LIG_ID_CONCLUIDO, LIG_ID_RESGATE_PEDAGOGICO, LIG_ID_RESGATE_FINANCEIRO, p.LIG_ID_ANULADO, p.LIG_STATUS_CONTRATO_06, p.LIG_STATUS_CONTRATO_01)')
        $query->where('c.status in (p.LIG_ID_STATUS_DESISTENCIA, p.LIG_STATUS_CONTRATO_05,p.LIG_STATUS_CONTRATO_02, p.LIG_STATUS_CONTRATO_03)')
            ->where('u.empresa_id', $empresa_id)
            ->where('c.tipo_saxweb', 2);

        return $query->order_by('c.id', 'asc')
            ->get("cad_orca c");
    }

    public function get_metas_alunos($id = null, $inicio = null, $fim = null) {
        $contratos = $this->get_contratos_ead_no_limit($id)->result();
        
        $k = 0;
        $array_porcentagem_meta = array();
        foreach ($contratos as $k => $contrato)
        {               
            $tutorias = $this->get_ultima_tutoria_ead($contrato->id, $inicio, $fim);
            if($tutorias->num_rows() > 0) {
                if($contratos[$k]->has_pesquisa = $this->has_pesquisa_grafico($contrato->id, $inicio, $fim)) {
                    $contratos[$k]->pesquisa_perfil = $this->get_pesquisa_grafico($contrato->id, $inicio, $fim);
                    $array_porcentagem_meta[] = array("y" => $contratos[$k]->pesquisa_perfil->created_at, $contrato->id => $contratos[$k]->pesquisa_perfil->meta_porcentagem);
                }
                foreach ($tutorias->result() as $tutoria) {
                    $array_porcentagem_meta[] = array("y" => $tutoria->data_hora_fim, $contrato->id => $tutoria->meta_porcentagem);
                }
            } else {
                if($contratos[$k]->has_pesquisa = $this->has_pesquisa_grafico($contrato->id, $inicio, $fim)) {
                    $contratos[$k]->pesquisa_perfil = $this->get_pesquisa_grafico($contrato->id, $inicio, $fim);
                    $array_porcentagem_meta[] = array("y" => $contratos[$k]->pesquisa_perfil->created_at, $contrato->id => $contratos[$k]->pesquisa_perfil->meta_porcentagem);
                }
            }
            $k++;
        }          

        $aux = array();
        foreach ($array_porcentagem_meta as $key => $value) {
            $aux[$key] = $value['y'];
        }
        array_multisort($aux, SORT_ASC, $array_porcentagem_meta);

        foreach ($array_porcentagem_meta as $k => $row) {
            $array_porcentagem_meta[$k]["y"] = date("d/m/Y", strtotime($row["y"]));
        }

        $last_date = "";
        $elements = array();
        $last_index = 0;
        foreach ($array_porcentagem_meta as $k => $row) {
            if($row["y"] == $last_date) {
                foreach ($row as $key => $value) {
                    if($key != "y") {
                        $element_key = $key;
                        $element_value = $value;
                        break;
                    }
                }
                $elements[$last_index][$element_key] = $element_value;
                $last_index = $last_index;
            } else {
                $elements[$k] = $row;
                $last_index = $k;
            }
            $last_date = $row["y"];
        }

        foreach ($elements as $_i => $value) {
            foreach ($indexs as $key) {
                if(!array_key_exists($key, $value)) {
                    $elements[$_i][$key] = null;
                }
            }
        }

        return $elements;
    }

    private function get_ultima_tutoria_ead($orc_id, $inicio = null, $fim = null) {
        $query = $this
                ->db
                ->select('t.*')
                ->join("cad_orca o", "t.orc_id = o.id")
                ->join("unidades u", "u.id = o.filial")
                ->where("u.empresa_id", $this->session->userdata('empresa_id'))
                ->where("o.id", $orc_id);
        if($inicio != null) {
            $query->where("t.data_hora_fim >= '".$inicio."'");
        }
        if($fim != null) {
            $query->where("t.data_hora_fim <= '".$fim."'");
        }
        $res = $query->order_by('t.data_hora_fim', 'desc')
                ->limit(1)
                ->get('tutoria t');
        return $res;
    }

    private function get_contratos_ead_no_limit($user_id)
    {                
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('c.*, c.data_matricula, u.first_name, u.last_name, un.unidade, cu.DESCRICAO as curso_desc, s.descricao as status_desc, ecs.segment_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('unidades un', 'un.id = c.filial')
            ->join('parametro p', 'p.LIG_ID_FILIAL = un.id AND c.status != p.LIG_ID_ANULADO')
            ->join('curso cu', 'cu.ID_CURSO = c.lig_id_curso')
            ->join('cad_status s', 's.id = c.status')
            ->join('ead_contratos_segments ecs', 'ecs.contrato_id = c.id', 'left')
            ->join('ead_segments es', 'es.id = ecs.segment_id', 'left')
            ->where('c.tipo_saxweb', '2')
            ->where('un.id IN ('.implode(",", $unids_array).')');
        
        $query->order_by('c.id', 'desc');
        return $query->group_by('c.id')
            ->get("cad_orca c");
    }

    private function has_pesquisa_grafico($contrato_id, $inicio = null, $fim = null) {
        $query = $this
                ->db
                ->select('*')
                ->where("orc_id", $contrato_id);
        if($inicio != null) {
            $query->where("created_at >= '".$inicio."'");
        }
        if($fim != null) {
            $query->where("created_at <= '".$fim."'");
        }
        $num_rows = $query->get('pesquisa_perfil')->num_rows();

        if($num_rows > 0)
            return true;
        else
            return false;
    }

    private function get_pesquisa_grafico($contrato_id, $inicio = null, $fim = null) {
        $query = $this
                ->db
                ->select('*')
                ->where("orc_id", $contrato_id);
        if($inicio != null) {
            $query->where("created_at >= '".$inicio."'");
        }
        if($fim != null) {
            $query->where("created_at <= '".$fim."'");
        }
        $res = $query->get('pesquisa_perfil')->row();
        return $res;
    }

    public function get_tutorias_realizadas($id = null) {
        $this->db->select('t.*, CONCAT(u.first_name, " ", u.last_name) as nome_aluno, CONCAT(ut.first_name, " ", ut.last_name) as nome_tutor')
            ->join('cad_orca c', 'c.id = t.orc_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('users ut', 't.tutor_id = ut.id');

        if($id != null) {
            $this->db->where("t.tutor_id", $id)
                ->limit(10);
        } else {
            $this->db->where('t.data_hora_inicio BETWEEN NOW() - INTERVAL 7 DAY AND NOW()');
        }

        $tutorias = $this->db->order_by('t.data_hora_inicio', 'asc')
            ->get("tutoria t");
        return $tutorias;
    }

    public function get_alunos_meta_atigida($id = null, $empresa_id) {
        return $this->db->select('p.*, t.*, p.id as pesquisa_id')
            ->join('ead_contratos_segments cs', 'cs.contrato_id = p.orc_id')
            ->join('ead_segments s', 's.id = cs.segment_id')
            ->join('tutoria t', 't.orc_id = p.orc_id')
            ->join('cad_orca c', 'c.id = p.orc_id')
            ->join('unidades u', 'c.filial = u.id')
            ->where('s.tutor_id', $id)
            ->where('u.empresa_id', $empresa_id)
            ->where('t.meta_porcentagem', "100")
            ->order_by('p.orc_id', 'asc')
            ->order_by('p.id', 'desc')
            ->limit(10)
            ->get("pesquisa_perfil p");
    }

    public function get_contratos_ead_cancelados_por_tutor($user_id = null)
    {        
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('c.*, c.data_matricula, u.first_name, u.last_name, un.unidade, cu.DESCRICAO as curso_desc, s.descricao as status_desc, ecs.segment_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('unidades un', 'un.id = c.filial')
            ->join('curso cu', 'cu.ID_CURSO = c.lig_id_curso')
            ->join('cad_status s', 's.id = c.status')
            ->join('ead_contratos_segments ecs', 'ecs.contrato_id = c.id')
            ->join('ead_segments es', 'ecs.segment_id = es.id')
            ->join('parametro p', 'p.LIG_ID_FILIAL = c.filial AND p.LIG_STATUS_CONTRATO_06 = c.status')
            ->where('c.tipo_saxweb', '2')
            ->where('es.tutor_id', $user_id)
            ->where('un.id IN ('.implode(",", $unids_array).')')
            ->where('c.ultima_atualizacao BETWEEN NOW() - INTERVAL 7 DAY AND NOW()');
        
        $query->order_by('c.id', 'desc');
        return $query->group_by('c.id')
            ->get("cad_orca c");
    }

    public function get_contratos_ead_cancelados($user_id)
    {
        $user_id = $user_id;
        
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('c.*, c.data_matricula, u.first_name, u.last_name, un.unidade, cu.DESCRICAO as curso_desc, s.descricao as status_desc, ecs.segment_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('unidades un', 'un.id = c.filial')
            ->join('curso cu', 'cu.ID_CURSO = c.lig_id_curso')
            ->join('cad_status s', 's.id = c.status')
            ->join('ead_contratos_segments ecs', 'ecs.contrato_id = c.id', 'left')
            ->join('ead_segments es', 'ecs.segment_id = es.id', 'left')
            ->join('parametro p', 'p.LIG_ID_FILIAL = c.filial AND p.LIG_STATUS_CONTRATO_06 = c.status')
            ->where('c.tipo_saxweb', '2')
            ->where('un.id IN ('.implode(",", $unids_array).')')
            ->where('c.ultima_atualizacao BETWEEN NOW() - INTERVAL 7 DAY AND NOW()');
        
        $query->order_by('c.id', 'desc');
        return $query->group_by('c.id')
            ->get("cad_orca c");
    }

    public function get_detalhes_tutores($empresa_id, $user_id) {
        $admin_pref = $this->db->get_where('admin_preferences', array('empresa_id'=>$empresa_id))->row();
        /*$unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }*/

        /*$tutores = $this->db->select('u.*, CONCAT(u.first_name, " ", u.last_name) as nome_tutor')
            ->join('user_unidade uu', 'u.id = uu.user_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            //->where('uu.unidade_id IN ('.implode(",", $unids_array).')')
            ->where('ug.group_id', $admin_pref->grupo_tutor)
            ->group_by('u.id')
            ->order_by('u.last_login', 'desc')
            ->distinct()
            ->get("users u");*/

        $tutores = $this->db->select('us.*, CONCAT(us.first_name, " ", us.last_name) as nome_tutor, s.tutor_id')
            ->join('unidades u', 'c.filial = u.id')
            ->join('parametro p', 'u.id = p.LIG_ID_FILIAL')
            ->join('ead_contratos_segments cs', 'cs.contrato_id = c.id')
            ->join('ead_segments s', 's.id = cs.segment_id')
            ->join('users us', 's.tutor_id = us.id')
            ->where('u.empresa_id', $empresa_id)
            ->where('c.tipo_saxweb', 2)
            ->distinct()
            ->get("cad_orca c");

        // exit("<pre>".print_r($tutores->result(),true)."</pre>");

        $tutores_obj = $tutores->result();
        if($tutores->num_rows() > 0) {
            foreach ($tutores_obj as $key => $value) {
                //ATIVO, MATRICULA
                /*$alunos_validos = $this->db->select('u.*, co.filial, co.status')
                    ->join('cad_orca co', 'co.user_id = u.id')
                    ->join('ead_contratos_segments cs', 'cs.contrato_id = co.id')
                    ->join('ead_segments s', 's.id = cs.segment_id')
                    ->join('unidades un', 'co.filial = un.id')
                    ->join('parametro p', 'p.LIG_ID_FILIAL = un.id AND (co.status = p.LIG_STATUS_CONTRATO_02 OR co.status = p.LIG_STATUS_CONTRATO_03)')
                    ->where('s.tutor_id', $value->id)
                    ->where('un.empresa_id', $empresa_id)
                    ->distinct()
                    ->get("users u");
                $tutores_obj[$key]->qtd_alunos_validos = $alunos_validos->num_rows();

                //DESISTENTES
                $alunos_desistentes = $this->db->select('u.*, co.filial, co.status')
                    ->join('cad_orca co', 'co.user_id = u.id')
                    ->join('ead_contratos_segments cs', 'cs.contrato_id = co.id')
                    ->join('ead_segments s', 's.id = cs.segment_id')
                    ->join('unidades un', 'co.filial = un.id')
                    ->join('parametro p', 'p.LIG_ID_FILIAL = un.id AND (co.status = p.LIG_ID_STATUS_DESISTENCIA OR co.status = p.LIG_STATUS_CONTRATO_05)')
                    ->where('s.tutor_id', $value->id)
                    ->where('un.empresa_id', $empresa_id)
                    ->distinct()
                    ->get("users u");
                $tutores_obj[$key]->qtd_alunos_desistentes = $alunos_desistentes->num_rows();*/

                //ATIVO, MATRICULA
                $alunos_validos = $this->db->select('c.*')
                    ->join('unidades u', 'c.filial = u.id')
                    ->join('parametro p', 'p.LIG_ID_FILIAL = u.id')
                    ->join('ead_contratos_segments cs', 'cs.contrato_id = c.id')
                    ->join('ead_segments s', 's.id = cs.segment_id')
                    ->where('s.tutor_id', $value->tutor_id)
                    ->where('u.empresa_id', $empresa_id)
                    ->where('c.tipo_saxweb', 2)
                    ->where('c.status in (p.LIG_STATUS_CONTRATO_02, p.LIG_STATUS_CONTRATO_03)')
                    ->get("cad_orca c");
                $tutores_obj[$key]->qtd_alunos_validos = $alunos_validos->num_rows();

                //DESISTENTES
                $alunos_desistentes = $this->db->select('c.*')
                    ->join('unidades u', 'c.filial = u.id')
                    ->join('parametro p', 'p.LIG_ID_FILIAL = u.id')
                    ->join('ead_contratos_segments cs', 'cs.contrato_id = c.id')
                    ->join('ead_segments s', 's.id = cs.segment_id')
                    ->where('s.tutor_id', $value->tutor_id)
                    ->where('u.empresa_id', $empresa_id)
                    ->where('c.tipo_saxweb', 2)
                    ->where('c.status in (p.LIG_ID_STATUS_DESISTENCIA, p.LIG_STATUS_CONTRATO_05, p.LIG_STATUS_CONTRATO_04)')
                    ->get("cad_orca c");
                $tutores_obj[$key]->qtd_alunos_desistentes = $alunos_desistentes->num_rows();
            }
        }

        return $tutores_obj;
    }

    public function get_evasao_by_date($user_id, $empresa_id, $data) {
        $unidades = $this->db->select('un.unidade_id')
            ->join('unidades u', 'u.id = un.unidade_id')
            ->where('un.user_id', $user_id)
            ->where('u.empresa_id', $empresa_id)
            ->get('user_unidade un')
            ->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }
        
        $query = $this->db->select('eo.*, o.filial')
            ->join('evasao e', 'e.id = eo.evasao_id')
            ->join('cad_orca o', 'o.id = eo.orc_id')
            ->where('e.data', $data)
            ->where('o.filial IN ('.implode(",", $unids_array).')')
            ->get("evasao_orc eo");

        return $query;
    }

    public function get_contratos_sem_tutor($empresa_id) {
        $query = $this->db->select('c.*, s.tutor_id')
            ->join('unidades u', 'c.filial = u.id')
            ->join('parametro p', 'u.id = p.LIG_ID_FILIAL')
            ->join('ead_contratos_segments cs', 'cs.contrato_id = c.id', 'left')
            ->join('ead_segments s', 's.id = cs.segment_id', 'left')
            // ->where('c.status not in (p.LIG_ID_CONCLUIDO, LIG_ID_RESGATE_PEDAGOGICO, LIG_ID_RESGATE_FINANCEIRO, p.LIG_ID_ANULADO, p.LIG_STATUS_CONTRATO_06, p.LIG_STATUS_CONTRATO_01)')
            ->where('c.status in (p.LIG_ID_STATUS_DESISTENCIA, p.LIG_STATUS_CONTRATO_05, p.LIG_STATUS_CONTRATO_02, p.LIG_STATUS_CONTRATO_03, p.LIG_STATUS_CONTRATO_04)')
            ->where('(s.tutor_id IS NULL OR s.tutor_id = "")')
            ->where('u.empresa_id', $empresa_id)
            ->where('c.tipo_saxweb', 2);
        return $query->order_by('c.id', 'asc')
            ->get("cad_orca c");
    }

    public function get_desistentes($empresa_id, $user_id, $momento = "antes") {
        $admin_pref = $this->db->get_where('admin_preferences', array('empresa_id'=>$empresa_id))->row();
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $qry_momento = "";
        if($momento == "antes") {
            $qry_momento = "e.ultimo_acesso BETWEEN NOW() - INTERVAL 22 DAY AND NOW()";
        } else {
            $qry_momento = "e.ultimo_acesso < '".date("Y-m-d", strtotime("-21 days"))."'";
        }

        return $this->db->select('u.*, e.ultimo_acesso, CONCAT(u.first_name, " ", u.last_name) as nome_aluno, co.id as contrato_id, CONCAT(su.first_name, " ", su.last_name) as nome_tutor')
            ->join('ead_users e', 'e.user_id = u.id')
            ->join('cad_orca co', 'co.user_id = u.id')
            ->join('unidades un', 'co.filial = un.id')
            ->join('ead_contratos_segments cs', 'cs.contrato_id = co.id', 'left')
            ->join('ead_segments s', 's.id = cs.segment_id', 'left')
            ->join('users su', 'su.id = s.tutor_id', 'left')
            ->join('parametro p', 'p.LIG_ID_FILIAL = un.id AND (co.status = p.LIG_ID_STATUS_DESISTENCIA OR co.status = p.LIG_STATUS_CONTRATO_05)')
            ->where('co.filial IN ('.implode(",", $unids_array).')')
            ->where('un.empresa_id', $empresa_id)
            ->where('co.tipo_saxweb', 2)
            ->where($qry_momento)
            ->distinct()
            ->get("users u");
    }

    public function update_data_relatorio_supervisor($empresa_id) {
        $this->db->update('admin_preferences', array('data_relatorio_supervisor' => date('Y-m-d')), array("empresa_id" => $empresa_id));
    }

    public function update_data_relatorio_tutor($empresa_id) {
        $this->db->update('admin_preferences', array('data_relatorio_tutor' => date('Y-m-d')), array("empresa_id" => $empresa_id));
    }

    public function get_data_relatorio_tutor($empresa_id) {
        return $this->db->get_where('admin_preferences', array('empresa_id'=>$empresa_id))->row()->data_relatorio_tutor;
    }

    public function get_data_relatorio_supervisor($empresa_id) {
        return $this->db->get_where('admin_preferences', array('empresa_id'=>$empresa_id))->row()->data_relatorio_supervisor;
    }

    public function ativo_relatorio_tutor($empresa_id) {
        return $this->db->get_where('admin_preferences', array('empresa_id'=>$empresa_id))->row()->relatorio_tutor;
    }

    public function ativo_relatorio_supervisor($empresa_id) {
        return $this->db->get_where('admin_preferences', array('empresa_id'=>$empresa_id))->row()->relatorio_supervisor;
    }

    public function get_configs_saida() {
        return $this->db->get_where('config_email', array('test_saida'=>1));
    }

    public function get_user_by_email($email) {
        $query = $this->db->select('*')
            ->where('email', $email)
            ->limit(1)
            ->get('users');
        return $query;
    }

    public function get_unid_padrao_usu($user_id, $empresa_id) {
        $query = $this->db->select('uu.unidade_id, u.unidade')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('uu.user_id', $user_id)
            ->where('uu.padrao', 1)
            ->where('u.empresa_id', $empresa_id)
            ->limit(1)
            ->get('user_unidade uu');
        return $query;
    }

    public function create_chamado($data = array())
    {
        $this->db->insert('chamado', $data);
        $id = $this->db->insert_id();
        return $id;
    }

    public function create_chamado_anexo($data = array())
    {
        $this->db->insert('chamado_anexo', $data);
        return true;
    }
    public function create_chamado_dialogo($data = array())
    {
        $this->db->insert('chamado_dialogo', $data);
        return true;
    }

    public function has_chamado($id) {
        $res = $this->db->get_where('chamado', array('id' => $id));
        if($res->num_rows() > 0)
            return true;
        else
            return false;
    }

    public function update_config_email($id, $data) {
        $this->db->update('config_email', $data, array("id" => $id));
    }

    public function get_email_padrao($empresa_id, $tipo_email)
    {
        return $this->db->select('*')
            ->where('empresa_id', $empresa_id)
            ->where('tipo_email', $tipo_email)
            ->get("email_padrao");
    }

    public function get_chamado($id)
    {
        $query = $this->db->select('u.email')
            ->join('users u', 'u.id = c.responsavel_id')
            ->where('c.id', $id)
            ->limit(1)
            ->get('chamado c');
        return $query;
    }

    public function get_dialogos($id = null)
    {
        if($id == null){
            return false;
        }
        $query = $this->db->select('d.*, u.first_name, u.last_name');
        $query->join('users u', 'u.id = d.responsavel_id', 'left');
        $query->where('d.chamado_id', $id);
        $query->order_by('d.data_envio', 'desc');
        return $query->get("chamado_dialogo d");
    }

    public function get_anexos($id = null)
    {
        if($id == null){
            return false;
        }
        $query = $this->db->select('a.*, u.first_name, u.last_name');
        $query->join('users u', 'u.id = a.responsavel_id', 'left');
        $query->where('a.chamado_id', $id);
        $query->order_by('a.created', 'desc');
        return $query->get("chamado_anexo a");
    }

    public function get_chamados_em_avaliacao()
    {
        $query = $this->db->select('c.id, c.titulo, c.data_finalizacao, c.dono_id, u.first_name, u.last_name, u.email, un.empresa_id, un.unidade, cs.descricao as catalogo_desc')
            ->join('catalogo_servico cs','cs.id = c.catalogoservico_id','left')
            ->join('users u', 'u.id = c.dono_id')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->where('c.data_finalizacao >= "'.date("Y-m-d H:i:s", strtotime("-30 days")).'" AND c.data_finalizacao <= "'.date("Y-m-d H:i:s").'"')
            ->order_by("u.email", "asc")
            ->get("chamado c");

        $chamados_nao_avaliados = array();
        foreach ($query->result() as $key => $value) {
            if(!$this->is_avaliado($value->id)) {
                $chamados_nao_avaliados[] = $value;
            }
        }
        return $chamados_nao_avaliados;
    }

    public function update_chamados_em_avaliacao() {
        $query = $this->db->select('c.id')
            ->where('c.data_finalizacao < "'.date("Y-m-d", strtotime("-30 days")).'" AND c.status_id = "3"')
            ->get("chamado c");

        if($query->num_rows() > 0) {
            foreach ($query->result() as $value) {
                $this->db->insert('chamado_dialogo', array("chamado_id" => $value->id, "responsavel_id" => 1, "descricao" => "Prazo para avaliação do chamado expirado."));
            }
        }

        $this->db->query("update chamado SET status_id = '5' where data_finalizacao  < '".date("Y-m-d", strtotime("-30 days"))."' and status_id = '3'");

        return true;
    }

    private function is_avaliado($id = null) {
        if($id == null)
            return false;

        $qtd_aval = $this->db->get_where('avaliacao_pergunta', array('actived' => 1))->num_rows();
        $qtd_aval_chamado = $this->db->get_where('chamado_avaliacao', array('chamado_id' => $id))->num_rows();
        if($qtd_aval == $qtd_aval_chamado)
            return true;
        else
            return false;
    }

    public function get_parametro($filial) {
        $res = $this->db->get_where('parametro', array('LIG_ID_FILIAL' => $filial));
        return $res;
    }

    public function get_apontamento($where = "") {
        $query = $this->db->select('a.*, c.titulo, s.descricao, CONCAT(u.first_name, " ", u.last_name) as nome, un.unidade')
            ->join('users u', 'u.id = a.user_id')
            ->join('chamado c', 'c.id = a.chamado_id')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->join('chamado_status s', 's.id = c.status_id');
        if($where != "")
            $query->where($where);
        $query->order_by('a.user_id', 'asc');
        $query->order_by('a.data', 'desc');
        $query = $query->get("chamado_apontamento a");
        return $query;
    }

    public function get_acumulado_mes($data, $user) {
        $date_explode = explode("-", $data);
        $query = $this->db->select('esforco')
            ->where('YEAR(data) = "'.$date_explode[1].'" AND MONTH(data) = "'.$date_explode[0].'"')
            ->where('user_id', $user)
            ->get('chamado_apontamento');
        
        $times = array();
        foreach($query->result() as $apont) {
            $times[] = $apont->esforco;
        }

        return AddPlayTime($times);
    }

    public function get_admin_prefs() {
        return $this->db->get_where('admin_preferences');
    }

    public function get_users_by_group($group) {
        $query = $this->db->select('ug.user_id, u.email')
            ->join('users u', 'u.id = ug.user_id')
            ->where('ug.group_id', $group)
            ->get('users_groups ug');
        return $query;
    }

    public function insert_user($data = array())
    {
        $this->db->insert('users', $data);
        $id = $this->db->insert_id();
        return $id;
    }

    public function get_user_by_id($id) {
        $query = $this->db->select('*')
            ->where('id', $id)
            ->limit(1)
            ->get('users');
        return $query;
    }

    public function insert_empresas_users($data = array())
    {
        $this->db->insert('empresas_users', $data);
        $id = $this->db->insert_id();
        return $id;
    }

    public function insert_user_unidade($data = array())
    {
        $qtd = $this->db->get_where('user_unidade', array('user_id' => $data["user_id"], "unidade_id" => $data["unidade_id"]))->num_rows();
        if($qtd == 0) {
            $this->db->insert('user_unidade', $data);
            $id = $this->db->insert_id();
            return $id;
        }
        return true;
    }

    public function insert_pessoas($data = array())
    {
        $this->db->insert('pessoas', $data);
        $id = $this->db->insert_id();
        return $id;
    }

    public function get_tini_api() {
        $res = $this->db->get_where('tini_api');
        return $res;
    }

    public function insert_unidade($data = array())
    {
        $this->db->insert('unidades', $data);
        $id = $this->db->insert_id();
        return $id;
    }

    public function update_tini_api($id, $data) {
        $this->db->update('tini_api', $data, array("id" => $id));
    }

    public function insert_users_groups($data = array())
    {
        $this->db->insert('users_groups', $data);
        $id = $this->db->insert_id();
        return $id;
    }
}
