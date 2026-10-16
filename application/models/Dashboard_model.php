<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model {

    protected $messages;

    protected $errors;

    public function __construct()
    {
        parent::__construct();
        $this->messages    = array();
        $this->errors      = array();
    }


    public function get_count_record($table)
    {
        $query = $this->db->where('empresa_id', $this->session->userdata('empresa_id'))->get($table);
        return $query->num_rows();
    }


    public function disk_totalspace($dir = DIRECTORY_SEPARATOR)
    {
        return disk_total_space($dir);
    }


    public function disk_freespace($dir = DIRECTORY_SEPARATOR)
    {
        return disk_free_space($dir);
    }


    public function disk_usespace($dir = DIRECTORY_SEPARATOR)
    {
        return $this->disk_totalspace($dir) - $this->disk_freespace($dir);
    }


    public function disk_freepercent($dir = DIRECTORY_SEPARATOR, $display_unit = FALSE)
    {
        if ($display_unit === FALSE)
        {
            $unit = NULL;
        }
        else
        {
            $unit = ' %';
        }

        return round(($this->disk_freespace($dir) * 100) / $this->disk_totalspace($dir), 0).$unit;
    }


    public function disk_usepercent($dir = DIRECTORY_SEPARATOR, $display_unit = FALSE)
    {
        if ($display_unit === FALSE)
        {
            $unit = NULL;
        }
        else
        {
            $unit = ' %';
        }

        return round(($this->disk_usespace($dir) * 100) / $this->disk_totalspace($dir), 0).$unit;
    }


    public function memory_usage()
    {
        return memory_get_usage();
    }


    public function memory_peak_usage($real = TRUE)
    {
        if ($real)
        {
            return memory_get_peak_usage(TRUE);
        }
        else
        {
            return memory_get_peak_usage(FALSE);
        }
    }


    public function memory_usepercent($real = TRUE, $display_unit = FALSE)
    {
        if ($display_unit === FALSE)
        {
            $unit = NULL;
        }
        else
        {
            $unit = ' %';
        }

        return round(($this->memory_usage() * 100) / $this->memory_peak_usage($real), 0).$unit;
    }

    public function get_tutores() {
        return $this->db->select('u.*')
            ->join('ead_segments es', 'es.tutor_id = u.id')
            ->join('ead_contratos_segments ecs', 'ecs.segment_id = es.id')
            ->join('cad_orca c', 'c.id = ecs.contrato_id')
            ->where('c.user_id', $this->session->userdata('user_id'))
            ->group_by('u.id')
            ->get('users u');
    }

    public function get_start_and_end_date($week, $year) {
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $ret['week_start'] = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $ret['week_end'] = $dto->format('Y-m-d');
        return $ret;
    }

    public function get_count_resps($datas, $user_id = null) {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');
        
        return $this->db->select('u.first_name, u.last_name, e.unidade, r.data_resposta, r.aluno, ul.unidade as unidade_livro, ul.titulo, ul.tipo, rac.id as id_correcao, rac.status, rac.data_envio')
            ->join('users u', 'r.aluno = u.id')
            ->join('pergunta_exercicio pe', 'r.pergunta = pe.id')
            ->join('exercicios e', 'pe.exercicio = e.id')
            ->join('unidade_livro ul', 'e.unidade = ul.id')
            ->join('resposta_aluno_correcao rac', 'rac.id_aluno = r.aluno AND `rac`.`id_unidade` = `ul`.`id`')
            ->join('livros l', 'l.id = ul.livro')
            ->where('r.aluno', $user_id)
            ->where('l.empresa', $this->session->userdata('empresa_id'))
            ->where('r.data_resposta BETWEEN "'.$datas['week_start'].'" AND "'.$datas['week_end'].'"')
            ->group_by('ul.id, u.id')
            ->get("resposta_aluno r")
            ->num_rows();
    }

    public function get_respostas_lista($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');

        return $this->db->select('u.first_name, u.last_name, e.unidade, r.data_resposta, r.aluno, ul.unidade as unidade_livro, ul.titulo, ul.tipo, rac.id as id_correcao, rac.status, rac.data_envio')
            ->join('users u', 'r.aluno = u.id')
            ->join('pergunta_exercicio pe', 'r.pergunta = pe.id')
            ->join('exercicios e', 'pe.exercicio = e.id')
            ->join('unidade_livro ul', 'e.unidade = ul.id')
            ->join('resposta_aluno_correcao rac', 'rac.id_aluno = r.aluno AND `rac`.`id_unidade` = `ul`.`id`')
            ->join('livros l', 'l.id = ul.livro')
            ->where('r.aluno', $user_id)
            ->where('l.empresa', $this->session->userdata('empresa_id'))
            ->group_by('ul.id, u.id')
            ->get("resposta_aluno r");
    }

    public function get_dispositivos()
    {
        return $this->db->select('*')
            ->where('id_aluno', $this->session->userdata('user_id'))
            ->get("aluno_device");
    }

    public function get_unidades($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');

		return $this->db->select('uu.*, u.unidade')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->join('empresas e', 'e.id = u.empresa_id')
            ->where('uu.user_id', $user_id)
            ->where('e.id', $this->session->userdata('empresa_id'))
            ->get('user_unidade uu');
			
    }

    public function get_cad_status()
    {
        return $this->db->select('*')
            ->get("cad_status");
    }

    public function get_alunos($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');

        $admin_pref = $this->db->get_where('admin_preferences', array('empresa_id'=>$this->session->userdata('empresa_id')))->row();
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        return $this->db->select('u.*, p.*')
            ->join('pessoas p', 'u.id = p.user_id')
            ->join('user_unidade uu', 'u.id = uu.user_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('uu.unidade_id IN ('.implode(",", $unids_array).')')
            ->where('ug.group_id', $admin_pref->grupo_aluno)
            ->group_by('u.id')
            ->distinct()
            ->get("users u");
    }

    public function get_alunos_map($status = "", $idade = "")
    {
        $user_id = $this->session->userdata('user_id');

        $admin_pref = $this->db->get_where('admin_preferences', array('empresa_id'=>$this->session->userdata('empresa_id')))->row();
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $this->db->select('u.*, p.*')
            ->join('pessoas p', 'u.id = p.user_id')
            ->join('user_unidade uu', 'u.id = uu.user_id')
            ->join('users_groups ug', 'ug.user_id = u.id');
        if($status != "") {
            $this->db->join('cad_orca c', 'u.id = c.user_id');
            $this->db->where('c.status', $status);
        }
        $this->db->where('uu.unidade_id IN ('.implode(",", $unids_array).')')
            ->where('ug.group_id', $admin_pref->grupo_aluno);
        if($idade != "") {
            if($idade == "0") {
                $data = date('Y-m-d', strtotime("-15 years", strtotime(date('Y-m-d'))));
                $this->db->where('p.data_nascimento > "'.$data.'"');
            } elseif($idade == "1") {
                $data_inicio = date('Y-m-d', strtotime("-16 years", strtotime(date('Y-m-d'))));
                $data_fim = date('Y-m-d', strtotime("-18 years", strtotime(date('Y-m-d'))));
                $this->db->where('p.data_nascimento < "'.$data_inicio.'" AND p.data_nascimento > "'.$data_fim.'"');
            } elseif($idade == "2") {
                $data_inicio = date('Y-m-d', strtotime("-19 years", strtotime(date('Y-m-d'))));
                $data_fim = date('Y-m-d', strtotime("-23 years", strtotime(date('Y-m-d'))));
                $this->db->where('p.data_nascimento < "'.$data_inicio.'" AND p.data_nascimento > "'.$data_fim.'"');
            } elseif($idade == "3") {
                $data_inicio = date('Y-m-d', strtotime("-24 years", strtotime(date('Y-m-d'))));
                $data_fim = date('Y-m-d', strtotime("-29 years", strtotime(date('Y-m-d'))));
                $this->db->where('p.data_nascimento < "'.$data_inicio.'" AND p.data_nascimento > "'.$data_fim.'"');
            } elseif($idade == "4") {
                $data_inicio = date('Y-m-d', strtotime("-30 years", strtotime(date('Y-m-d'))));
                $data_fim = date('Y-m-d', strtotime("-35 years", strtotime(date('Y-m-d'))));
                $this->db->where('p.data_nascimento < "'.$data_inicio.'" AND p.data_nascimento > "'.$data_fim.'"');
            } elseif($idade == "5") {
                $data_inicio = date('Y-m-d', strtotime("-36 years", strtotime(date('Y-m-d'))));
                $data_fim = date('Y-m-d', strtotime("-50 years", strtotime(date('Y-m-d'))));
                $this->db->where('p.data_nascimento < "'.$data_inicio.'" AND p.data_nascimento > "'.$data_fim.'"');
            } elseif($idade == "6") {
                $data = date('Y-m-d', strtotime("-51 years", strtotime(date('Y-m-d'))));
                $this->db->where('p.data_nascimento < "'.$data.'"');
            }
        }

        return $this->db->group_by('u.id')
            ->get("users u");
    }

    public function get_respostas_alunos($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');
        
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        return $this->db->select('r.id')
            ->join('pergunta_exercicio pe', 'r.pergunta = pe.id')
            ->join('exercicios e', 'pe.exercicio = e.id')
            ->join('unidade_livro ul', 'e.unidade = ul.id')
            ->join('resposta_aluno_correcao rac', 'rac.id_aluno = r.aluno')
            ->join('users u', 'u.id = r.aluno')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->join('livros l', 'l.id = ul.livro')
            ->where('l.empresa', $this->session->userdata('empresa_id'))
            ->where('r.data_resposta >= DATE(NOW()) - INTERVAL 7 DAY')
            ->where('uu.unidade_id IN ('.implode(",", $unids_array).')')
            ->group_by('u.id, u.id')
            ->get("resposta_aluno r");
    }

    public function get_respostas($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');
        
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        return $this->db->select('r.id')
            ->join('pergunta_exercicio pe', 'r.pergunta = pe.id')
            ->join('exercicios e', 'pe.exercicio = e.id')
            ->join('unidade_livro ul', 'e.unidade = ul.id')
            ->join('resposta_aluno_correcao rac', 'rac.id_aluno = r.aluno')
            ->join('users u', 'u.id = r.aluno')
            ->join('user_unidade uu', 'uu.user_id = u.id')
            ->join('livros l', 'l.id = ul.livro')
            ->where('l.empresa', $this->session->userdata('empresa_id'))
            ->where('r.data_resposta >= DATE(NOW()) - INTERVAL 7 DAY')
            ->where('uu.unidade_id IN ('.implode(",", $unids_array).')')
            ->group_by('ul.id, u.id')
            ->get("resposta_aluno r");
    }

    public function get_chamados($where = "", $unidades = array(0))
    {
        return $this->db->select('c.id')
            ->join('unidades u', 'u.id = c.unidade_id')
            ->where($where)
            ->where('c.unidade_id IN ('.implode(",", $unidades).')')
            ->get("chamado c");
    }

    public function get_chamados_compartilhados($unidades = array(0))
    {        
        $query = $this->db->select('c.id, c.interessados')
            ->join('unidades u', 'u.id = c.unidade_id')
            ->where('c.unidade_id IN ('.implode(",", $unidades).')')
            ->get("chamado c");

        $retorno_chamados = array();
        foreach ($query->result() as $value) {
            if($value->interessados != null){
                $array_interessados = unserialize($value->interessados);
                if(is_array($array_interessados)) {
                    if(in_array($this->session->userdata('user_id'), $array_interessados)) {
                        $retorno_chamados[] = $value;
                    }
                }
            }
        }

        return count($retorno_chamados);
    }

    public function get_chamados_abertos($current_date, $unidades = array(0))
    {
        return $this->db->select('c.*')
            ->where('c.unidade_id IN ('.implode(",", $unidades).')')
            ->where('MONTH(c.created) = MONTH("'.$current_date.'")')
            ->where('YEAR(c.created) = YEAR("'.$current_date.'")')
            ->get("chamado c");
    }

    public function get_chamados_finalizados($current_date, $unidades = array(0))
    {
        return $this->db->select('c.*')
            ->where('c.unidade_id IN ('.implode(",", $unidades).')')
            ->where('MONTH(c.data_finalizacao) = MONTH("'.$current_date.'")')
            ->where('YEAR(c.data_finalizacao) = YEAR("'.$current_date.'")')
            ->get("chamado c");
    }

    public function get_chamados_aguardando_teste($where = "", $unidades = array(0))
    {
        return $this->db->select('c.*, CONCAT(u.first_name, " ", u.last_name) as dono, un.unidade, cp.descricao as prioridade_desc')
            ->join('users u','u.id = c.dono_id')
            ->join('unidades un','un.id = c.unidade_id')
            ->join('chamado_prioridade cp', 'cp.id = c.prioridade_id')
            ->where('c.unidade_id IN ('.implode(",", $unidades).')')
            ->where($where)
            ->get("chamado c");
    }

    public function get_chamados_responsavel($where = "")
    {        
        $unidades = $this->db->select('uu.*, u.unidade')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('u.actived', 's')
            ->get("user_unidade uu")->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        return $this->db->select('c.*, u.unidade, cs.descricao as catalogo_desc, s.descricao as status_desc, cp.descricao as prioridade_desc')
            ->join('unidades u', 'u.id = c.unidade_id')
            ->join('catalogo_servico cs', 'cs.id = c.catalogoservico_id')
            ->join('chamado_status s', 's.id = c.status_id')
            ->join('chamado_prioridade cp', 'cp.id = c.prioridade_id')
            ->where($where)
            ->where('c.unidade_id IN ('.implode(",", $unids_array).')')
            ->get("chamado c");
    }

    public function get_chamados_avaliados($start, $end, $unidades = array(0))
    {
        $query = $this->db->select('c.*, un.unidade, CONCAT(u.first_name, " ", u.last_name) as dono, ca.created as data_avaliacao')
            ->join('users u','u.id = c.dono_id')
            ->join('unidades un','un.id = c.unidade_id')
            ->join('chamado_avaliacao ca','ca.chamado_id = c.id', 'left')
            ->where('c.unidade_id IN ('.implode(",", $unidades).')')
            ->group_by('c.id')
            ->having('ca.created >= "'.$start.' 00:00:00" AND ca.created <= "'.$end.' 23:59:59"')
            ->get("chamado c");

        $result = array();
        $media = 0;
        $qtd = 0;
        foreach ($query->result() as $value) {
            $respostas = $this->db->get_where('chamado_avaliacao', array('chamado_id' => $value->id));
            if($this->is_avaliado($value->id, $respostas)) {
                foreach ($respostas->result() as $resp) {
                    if($resp->pergunta_id == 1)
                        $value->atendimento = $resp->resposta;
                    elseif($resp->pergunta_id == 2)
                        $value->prazo = $resp->resposta;
                    elseif($resp->pergunta_id == 3)
                        $value->satisfacao = $resp->resposta;
                    $media += $resp->resposta;
                    $qtd++;
                }
                $result[] = $value;
            }
        }

        $media_qtd = 0;
        if($qtd > 0){
            $media_qtd = $media/$qtd;
        }
        return array("chamados" => $result, "media" => @round($media_qtd));
    }

    private function is_avaliado($id = null, $respostas = null) {
        if($id == null)
            return false;

        $qtd_aval = $this->db->get_where('avaliacao_pergunta', array('actived' => 1))->num_rows();
        // $qtd_aval_chamado = $this->db->get_where('chamado_avaliacao', array('chamado_id' => $id))->num_rows();
        $qtd_aval_chamado = $respostas->num_rows();
        if($qtd_aval == $qtd_aval_chamado)
            return true;
        else
            return false;
    }

    public function get_contratos_ead($id = null)
    {
        //if($user_id == null)
        $user_id = $this->session->userdata('user_id');
        
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('c.*, c.data_matricula, u.first_name, u.last_name, un.unidade, cu.DESCRICAO as curso_desc, s.descricao as status_desc, ecs.segment_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('unidades un', 'un.id = c.filial')
            ->join('parametro p', 'p.LIG_ID_FILIAL = un.id AND c.status != p.LIG_ID_ANULADO')
            ->join('curso cu', 'cu.ID_CURSO = c.lig_id_curso', 'left')
            ->join('cad_status s', 's.id = c.status')
            ->join('ead_contratos_segments ecs', 'ecs.contrato_id = c.id', 'left')
            ->where('c.tipo_saxweb', '2')
            ->where('un.empresa_id', $this->session->userdata('empresa_id'))
            ->where('un.id IN ('.implode(",", $unids_array).')');
        
        if($id != null) {
            $query->where('c.id', $id);
        }
        $query->order_by('c.data_pedido', 'desc');
        $query->limit(20);
        return $query->group_by('c.id')
            ->get("cad_orca c");
    }

    public function get_contratos_ead_cancelados_por_tutor($user_id = null)
    {
        if($user_id == null)
            $user_id = $this->session->userdata('user_id');
        
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('c.*, c.data_matricula, u.first_name, u.last_name, un.unidade, cu.DESCRICAO as curso_desc, s.descricao as status_desc, ecs.segment_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('unidades un', 'un.id = c.filial')
            ->join('curso cu', 'cu.ID_CURSO = c.lig_id_curso', 'left')
            ->join('cad_status s', 's.id = c.status')
            ->join('ead_contratos_segments ecs', 'ecs.contrato_id = c.id')
            ->join('ead_segments es', 'ecs.segment_id = es.id')
            ->join('parametro p', 'p.LIG_ID_FILIAL = c.filial AND p.LIG_STATUS_CONTRATO_06 = c.status')
            ->where('c.tipo_saxweb', '2')
            ->where('es.tutor_id', $user_id)
            ->where('un.id IN ('.implode(",", $unids_array).')')
            ->where('c.ultima_atualizacao BETWEEN NOW() - INTERVAL 30 DAY AND NOW()');
        
        // if($id != null) {
        //     $query->where('c.id', $id);
        // }
        $query->order_by('c.id', 'desc');
        return $query->group_by('c.id')
            ->get("cad_orca c");
    }

    public function get_contratos_ead_cancelados()
    {
        $user_id = $this->session->userdata('user_id');
        
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('c.*, c.data_matricula, u.first_name, u.last_name, un.unidade, cu.DESCRICAO as curso_desc, s.descricao as status_desc, ecs.segment_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('unidades un', 'un.id = c.filial')
            ->join('curso cu', 'cu.ID_CURSO = c.lig_id_curso', 'left')
            ->join('cad_status s', 's.id = c.status')
            ->join('ead_contratos_segments ecs', 'ecs.contrato_id = c.id', 'left')
            ->join('ead_segments es', 'ecs.segment_id = es.id', 'left')
            ->join('parametro p', 'p.LIG_ID_FILIAL = c.filial AND p.LIG_STATUS_CONTRATO_06 = c.status')
            ->where('c.tipo_saxweb', '2')
            ->where('un.id IN ('.implode(",", $unids_array).')')
            ->where('c.ultima_atualizacao BETWEEN NOW() - INTERVAL 30 DAY AND NOW()');
        
        $query->order_by('c.id', 'desc');
        return $query->group_by('c.id')
            ->get("cad_orca c");
    }

    public function get_nivel_alunos($id = null, $inicio = null, $fim = null) {
        $query = $this->db->select('t.*')
            ->join('ead_contratos_segments cs', 'cs.contrato_id = t.orc_id')
            ->join('ead_segments s', 's.id = cs.segment_id')
            ->join('cad_orca c', 'c.id = t.orc_id')
            ->join('unidades u', 'c.filial = u.id');

        if($id != null)
            $query->where('s.tutor_id', $id);

        if($inicio != null) {
            $query->where("t.data_hora_fim >= '".$this->convertDateBRToEU($inicio)."'");
        }
        if($fim != null) {
            $query->where("t.data_hora_fim <= '".$this->convertDateBRToEU($fim)."'");
        }

        return $query->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->order_by('t.orc_id', 'asc')
            ->order_by('t.id', 'desc')
            ->get("tutoria t");
    }

    public function get_alunos_meta_atigida($id = null) {
        if($id == null)
            $id = $this->session->userdata('user_id');

        return $this->db->select('p.*, t.*, p.id as pesquisa_id')
            ->join('ead_contratos_segments cs', 'cs.contrato_id = p.orc_id')
            ->join('ead_segments s', 's.id = cs.segment_id')
            ->join('tutoria t', 't.orc_id = p.orc_id')
            ->join('cad_orca c', 'c.id = p.orc_id')
            ->join('unidades u', 'c.filial = u.id')
            ->where('s.tutor_id', $id)
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('t.meta_porcentagem', "100")
            ->order_by('p.orc_id', 'asc')
            ->order_by('p.id', 'desc')
            ->get("pesquisa_perfil p");
    }

    public function get_json_grafico_metas_atingidas($tutor_id = null, $inicio = null, $fim = null) {
        $contratos = $this->get_contratos_ead_no_limit($tutor_id)->result();
        
        $k = 0;
        $array_porcentagem_meta = array();
        foreach ($contratos as $k => $contrato)
        {               
            $tutorias = $this->get_ultima_tutoria_ead($contrato->id, $inicio, $fim);
            if($tutorias->num_rows() > 0) {
                if($contratos[$k]->has_pesquisa = $this->has_pesquisa_grafico($contrato->id, $inicio, $fim)) {
                    $contratos[$k]->pesquisa_perfil = $this->get_pesquisa_grafico($contrato->id, $inicio, $fim);
                    $array_porcentagem_meta[] = array("y" => $contratos[$k]->pesquisa_perfil->created_at, "Contrato ".$contrato->id => $contratos[$k]->pesquisa_perfil->meta_porcentagem);
                }
                foreach ($tutorias->result() as $tutoria) {
                    $array_porcentagem_meta[] = array("y" => $tutoria->data_hora_fim, "Contrato ".$contrato->id => $tutoria->meta_porcentagem);
                }
            } else {
                if($contratos[$k]->has_pesquisa = $this->has_pesquisa_grafico($contrato->id, $inicio, $fim)) {
                    $contratos[$k]->pesquisa_perfil = $this->get_pesquisa_grafico($contrato->id, $inicio, $fim);
                    $array_porcentagem_meta[] = array("y" => $contratos[$k]->pesquisa_perfil->created_at, "Contrato ".$contrato->id => $contratos[$k]->pesquisa_perfil->meta_porcentagem);
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

        $x = array();
        $labels = array();
        $indexs = array();
        foreach ($elements as $value) {
            foreach ($value as $contrato => $value2) {
                if($contrato != "y") {
                    if(!in_array($contrato, $x))
                        $x[] = $contrato;
                    if(!in_array($contrato, $labels))
                        $labels[] = $contrato;
                    $indexs[] = $contrato;
                }
            }
        }

        foreach ($elements as $_i => $value) {
            foreach ($indexs as $key) {
                if(!array_key_exists($key, $value)) {
                    $elements[$_i][$key] = null;
                }
            }
        }

        return array("elements" => $elements, "x" => $x, "labels" => $labels);
    }

    private function has_pesquisa_grafico($contrato_id, $inicio = null, $fim = null) {
        $query = $this
                ->db
                ->select('*')
                ->where("orc_id", $contrato_id);
        if($inicio != null) {
            $query->where("created_at >= '".$this->convertDateBRToEU($inicio)."'");
        }
        if($fim != null) {
            $query->where("created_at <= '".$this->convertDateBRToEU($fim)."'");
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
            $query->where("created_at >= '".$this->convertDateBRToEU($inicio)."'");
        }
        if($fim != null) {
            $query->where("created_at <= '".$this->convertDateBRToEU($fim)."'");
        }
        $res = $query->get('pesquisa_perfil')->row();
        return $res;
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
            $query->where("t.data_hora_fim >= '".$this->convertDateBRToEU($inicio)."'");
        }
        if($fim != null) {
            $query->where("t.data_hora_fim <= '".$this->convertDateBRToEU($fim)."'");
        }
        $res = $query->order_by('t.data_hora_fim', 'desc')
                ->limit(1)
                ->get('tutoria t');
        return $res;
    }

    private function convertDateBRToEU($date)
    {
        $date = str_replace('/', '-', $date);
        return date('Y-m-d', strtotime($date));
    }

    private function get_contratos_ead_no_limit($tutor_id = null)
    {        
        $user_id = $this->session->userdata('user_id');
        
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('c.*, c.data_matricula, u.first_name, u.last_name, un.unidade, cu.DESCRICAO as curso_desc, s.descricao as status_desc, ecs.segment_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('unidades un', 'un.id = c.filial')
            ->join('parametro p', 'p.LIG_ID_FILIAL = un.id AND c.status != p.LIG_ID_ANULADO')
            ->join('curso cu', 'cu.ID_CURSO = c.lig_id_curso', 'left')
            ->join('cad_status s', 's.id = c.status')
            ->join('ead_contratos_segments ecs', 'ecs.contrato_id = c.id', 'left')
            ->join('ead_segments es', 'es.id = ecs.segment_id', 'left')
            ->where('c.tipo_saxweb', '2')
            ->where('un.id IN ('.implode(",", $unids_array).')');
        
        if($tutor_id != null) {
            $query->where('es.tutor_id', $tutor_id);
        }
        $query->order_by('c.id', 'desc');
        return $query->group_by('c.id')
            ->get("cad_orca c");
    }

    public function get_desistentes() {
        $admin_pref = $this->db->get_where('admin_preferences', array('empresa_id'=>$this->session->userdata('empresa_id')))->row();
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$this->session->userdata('user_id')))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        return $this->db->select('u.*, CONCAT(u.first_name, " ", u.last_name) as nome_aluno, co.id as contrato_id, eu.ultimo_acesso')
            ->join('cad_orca co', 'co.user_id = u.id')
            ->join('ead_contratos_segments cs', 'cs.contrato_id = co.id', 'left')
            ->join('ead_segments s', 's.id = cs.segment_id', 'left')
            ->join('ead_users eu', 'eu.user_id = u.id')
            ->join('unidades un', 'co.filial = un.id')
            ->join('parametro p', 'p.LIG_ID_FILIAL = un.id AND (co.status = p.LIG_ID_STATUS_DESISTENCIA OR co.status = p.LIG_STATUS_CONTRATO_05)')
            ->where('co.filial IN ('.implode(",", $unids_array).')')
            ->where('un.empresa_id', $this->session->userdata('empresa_id'))
            ->where('co.tipo_saxweb', 2)
            ->distinct()
            ->get("users u");
    }

    public function get_ultimo_acesso_tutores() {
        $admin_pref = $this->db->get_where('admin_preferences', array('empresa_id'=>$this->session->userdata('empresa_id')))->row();
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$this->session->userdata('user_id')))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $tutores = $this->db->select('u.*')
            ->join('user_unidade uu', 'u.id = uu.user_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('uu.unidade_id IN ('.implode(",", $unids_array).')')
            ->where('ug.group_id', $admin_pref->grupo_tutor)
            ->group_by('u.id')
            ->order_by('u.last_login', 'desc')
            ->distinct()
            ->limit(24)
            ->get("users u");

        $tutores_obj = $tutores->result();
        if($tutores->num_rows() > 0) {
            foreach ($tutores_obj as $key => $value) {
                $alunos_tutor = $this->db->select('u.*, co.filial, co.status')
                    ->join('cad_orca co', 'co.user_id = u.id')
                    ->join('ead_contratos_segments cs', 'cs.contrato_id = co.id')
                    ->join('ead_segments s', 's.id = cs.segment_id')
                    ->join('unidades un', 'co.filial = un.id')
                    ->join('parametro p', 'p.LIG_ID_FILIAL = un.id AND co.status = p.LIG_STATUS_CONTRATO_03')
                    ->where('s.tutor_id', $value->id)
                    ->where('un.empresa_id', $this->session->userdata('empresa_id'))
                    ->distinct()
                    ->get("users u");

                $tutores_obj[$key]->qtd_alunos = $alunos_tutor->num_rows();
                // exit($this->db->last_query());
            }
        }

        return $tutores_obj;
    }

    public function get_detalhes_tutores() {
        $admin_pref = $this->db->get_where('admin_preferences', array('empresa_id'=>$this->session->userdata('empresa_id')))->row();
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$this->session->userdata('user_id')))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $tutores = $this->db->select('u.*, CONCAT(u.first_name, " ", u.last_name) as nome_tutor')
            ->join('user_unidade uu', 'u.id = uu.user_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('uu.unidade_id IN ('.implode(",", $unids_array).')')
            ->where('ug.group_id', $admin_pref->grupo_tutor)
            ->group_by('u.id')
            ->order_by('u.last_login', 'desc')
            ->distinct()
            ->get("users u");

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
                    ->where('un.empresa_id', $this->session->userdata('empresa_id'))
                    ->distinct()
                    ->get("users u");*/

                $alunos_validos = $this->db->select('u.*, c.filial, c.status')
                    ->join('unidades u', 'c.filial = u.id')
                    ->join('parametro p', 'p.LIG_ID_FILIAL = u.id')
                    ->join('ead_contratos_segments cs', 'cs.contrato_id = c.id')
                    ->join('ead_segments s', 's.id = cs.segment_id')
                    ->where('s.tutor_id', $value->id)
                    ->where('u.empresa_id', $this->session->userdata('empresa_id'))
                    ->where('c.tipo_saxweb', 2)
                    ->where('c.status in (p.LIG_STATUS_CONTRATO_02, p.LIG_STATUS_CONTRATO_03)')
                    ->get("cad_orca c");
                $tutores_obj[$key]->qtd_alunos_validos = $alunos_validos->num_rows();

                //DESISTENTES
                /*$alunos_desistentes = $this->db->select('u.*, co.filial, co.status')
                    ->join('cad_orca co', 'co.user_id = u.id')
                    ->join('ead_contratos_segments cs', 'cs.contrato_id = co.id')
                    ->join('ead_segments s', 's.id = cs.segment_id')
                    ->join('unidades un', 'co.filial = un.id')
                    ->join('parametro p', 'p.LIG_ID_FILIAL = un.id AND (co.status = p.LIG_ID_STATUS_DESISTENCIA OR co.status = p.LIG_STATUS_CONTRATO_05 OR co.status = p.LIG_STATUS_CONTRATO_04)')
                    ->where('s.tutor_id', $value->id)
                    ->where('un.empresa_id', $this->session->userdata('empresa_id'))
                    ->distinct()
                    ->get("users u");*/
                $alunos_desistentes = $this->db->select('u.*, c.filial, c.status')
                    ->join('unidades u', 'c.filial = u.id')
                    ->join('parametro p', 'p.LIG_ID_FILIAL = u.id')
                    ->join('ead_contratos_segments cs', 'cs.contrato_id = c.id')
                    ->join('ead_segments s', 's.id = cs.segment_id')
                    ->where('s.tutor_id', $value->id)
                    ->where('u.empresa_id', $this->session->userdata('empresa_id'))
                    ->where('c.tipo_saxweb', 2)
                    ->where('c.status in (p.LIG_ID_STATUS_DESISTENCIA, p.LIG_STATUS_CONTRATO_05, p.LIG_STATUS_CONTRATO_04)')
                    ->get("cad_orca c");
                $tutores_obj[$key]->qtd_alunos_desistentes = $alunos_desistentes->num_rows();
            }
        }

        return $tutores_obj;
    }

    public function is_voxy_user($user_id) {
        $num_rows = $this->db->get_where('ead_users', array("user_id" => $user_id))->num_rows();
        if($num_rows > 0)
            return true;
        else
            return false;
    }

    public function has_pesquisa($contrato_id) {
        $num_rows = $this->db->get_where('pesquisa_perfil', array("orc_id" => $contrato_id))->num_rows();
        if($num_rows > 0)
            return true;
        else
            return false;
    }

    public function get_parametro($filial) {
        $return = $this->db->get_where('parametro', array("LIG_ID_FILIAL" => $filial))->row();
        return $return;
    }

    public function get_pesquisa($contrato_id) {
        $return = $this->db->get_where('pesquisa_perfil', array("orc_id" => $contrato_id))->row();
        return $return;
    }

    public function get_segments($contrato_id = null)
    {
        $query = $this->db->select('s.*, u.first_name, u.last_name')
            ->join('users u', 'u.id = s.tutor_id', 'left');
        if($contrato_id != null) {
            $query->join('ead_contratos_segments ecs', 'ecs.segment_id = s.id');
            $query->where('ecs.contrato_id', $contrato_id);
        }
        return $query->get('ead_segments s');
    }

    public function get_materiais($id = null, $where = '')
    {
        $grupos_usuario = $this->db->select("ug.*")
            ->join("groups g", "g.id = ug.group_id")
            ->where("ug.user_id", $this->session->userdata('user_id'))
            ->where("g.empresa_id", $this->session->userdata('empresa_id'))
            ->get("users_groups ug")->result();

        $grupos_usuario_array = array();
        foreach ($grupos_usuario as $value) {
            $grupos_usuario_array[] = $value->group_id;
        }
        
        $query = $this->db->select('m.*, DATE_FORMAT(m.created_at, "%d/%m/%Y") as data_criacao, l.titulo as titulo_livro, ul.titulo as titulo_unidade')
                      ->join('livros as l', 'l.id = m.livro', 'left')
                      ->join('unidade_livro as ul', 'ul.id = m.unidade', 'left')
                      ->where('m.empresa_id', $this->session->userdata('empresa_id'));
        $query->where('m.actived', 'S');
        if($id != null) {
            $query->where('m.id', $id);
        }
        if ($where != '') {
            $query->where($where);
        }
        $materiais = $query->get("materiais m")->result();

        $materiais_retorno = array();
        foreach ($materiais as $key => $material) {
            $grupos_material = unserialize($material->grupos);
            foreach ($grupos_material as $value) {
                if(in_array($value, $grupos_usuario_array)) {
                    $materiais_retorno[] = $material;
                    break;
                }
            }
        }

        return $materiais_retorno;
    }

    public function get_correcoes_professores()
    {
        $retorno = array();
        $admin_pref = $this->db->get_where('admin_preferences', array('empresa_id'=>$this->session->userdata('empresa_id')))->row();

        $professores = $this->db->select("u.*")
            ->join("empresas_users eu", "eu.user_id = u.id")
            ->join("users_groups ug", "ug.user_id = u.id")
            ->where("ug.group_id", $admin_pref->grupo_professor)
            ->where("eu.empresa_id", $this->session->userdata('empresa_id'))
            ->get("users u");

        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$this->session->userdata('user_id')))->result();
        $unids_array = array(0);
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        if($professores->num_rows() > 0) {
            foreach ($professores->result() as $prof) {
                $turmas = $this->db->select("t.ID")
                    ->join("turma_horario th", "th.LIG_ID_TURMA = t.ID")
                    ->where("th.LIG_ID_PROFESSOR", $prof->id)
                    ->where('t.unidade IN ('.implode(",", $unids_array).')')
                    ->distinct()
                    ->get("turma t")->result();

                $turmas_array = array();
                foreach ($turmas as $t) {
                    $turmas_array[] = $t->ID;
                }

                if(count($turmas_array) > 0) {
                    $maior_2_dias = $this->db->select('rac.*')
                        ->join('users u', 'r.aluno = u.id')
                        ->join('pergunta_exercicio pe', 'r.pergunta = pe.id')
                        ->join('exercicios e', 'pe.exercicio = e.id')
                        ->join('unidade_livro ul', 'e.unidade = ul.id')
                        ->join('resposta_aluno_correcao rac', 'rac.id_aluno = r.aluno AND rac.id_unidade = ul.id')
                        ->join('livros l', 'l.id = ul.livro')
                        ->join('turma_aluno ta', 'ta.aluno = u.id')
                        ->where("ta.turma IN (".implode(",", $turmas_array).")")
                        ->where("rac.`status` != 'C'")
                        ->where("rac.data_envio < DATE_SUB(NOW(), INTERVAL 2 DAY)")
                        ->where("l.empresa", $this->session->userdata('empresa_id'))
                        ->group_by('ul.id, u.id')
                        ->get("resposta_aluno r")->num_rows();

                    $total = $this->db->select('rac.*')
                        ->join('users u', 'r.aluno = u.id')
                        ->join('pergunta_exercicio pe', 'r.pergunta = pe.id')
                        ->join('exercicios e', 'pe.exercicio = e.id')
                        ->join('unidade_livro ul', 'e.unidade = ul.id')
                        ->join('resposta_aluno_correcao rac', 'rac.id_aluno = r.aluno AND rac.id_unidade = ul.id')
                        ->join('livros l', 'l.id = ul.livro')
                        ->join('turma_aluno ta', 'ta.aluno = u.id')
                        ->where("ta.turma IN (".implode(",", $turmas_array).")")
                        ->where("rac.`status` != 'C'")
                        ->where("l.empresa", $this->session->userdata('empresa_id'))
                        ->group_by('ul.id, u.id')
                        ->get("resposta_aluno r")->num_rows();
                        
                    if($total > 0)
                        $retorno[] = array("professor"=>$prof->first_name." ".$prof->last_name,"maior_2_dias"=>$maior_2_dias, "total"=>$total);
                }
            }
        }

        return $retorno;
    }

    public function has_pagamento_entrada_aluno() {
        //verificando se foi criada entrada
        $rows_bb = $this->db->select('i.*')
            ->join('cad_orca orc', 'orc.id = i.cad_orca_id')
            ->join('condicoes_orcamento o', 'o.ORCAMENTO = i.cad_orca_id')
            ->join('tipo_condicao_parcela_orcamento cond', 'cond.ID = o.TIPO_CONDICAO_ID')
            ->join('tip_rece r', 'r.CODIGO = o.TIPO_RECEBIMENTO')
            ->join('iugu_users u', '(u.user_id = orc.user_id or u.user_id = orc.responsavel_financeiro)')
            ->join('parametro p', 'p.LIG_ID_FILIAL = orc.filial')
            ->where("i.tipo", "M")
            ->where("cond.entrada_matricula", "True")
            ->where("(orc.user_id = ".$this->session->userdata('user_id')." OR orc.responsavel_financeiro = ".$this->session->userdata('user_id').")")
            ->where("p.LIG_STATUS_CONTRATO_06 != orc.status")
            ->where("p.LIG_ID_ANULADO != orc.status")
            ->get("iugu_invoices i")->num_rows();
        
        if($rows_bb > 0) {
            return true;
        } else {
            $rows_bb2 = $this->db->select('orc.id')
                ->join('iugu_users u', '(u.user_id = orc.user_id or u.user_id = orc.responsavel_financeiro)')
                ->join('parametro p', 'p.LIG_ID_FILIAL = orc.filial')
                ->join('cupom cu', 'cu.orc_id = orc.id')
                ->where("cu.isento_matricula", "1")
                ->where("(cu.entrada = '' OR cu.entrada IS NULL)")
                ->where("(orc.user_id = ".$this->session->userdata('user_id')." OR orc.responsavel_financeiro = ".$this->session->userdata('user_id').")")
                ->where("p.LIG_STATUS_CONTRATO_06 != orc.status")
                ->where("p.LIG_ID_ANULADO != orc.status")
                ->get("cad_orca orc")->num_rows();

            if($rows_bb2 > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function has_pagamento_contrato_aluno() {
        //verificando se foi criado o pagamento por cartão de crédito
        $rows_cc = $this->db->select('i.*')
            ->join('cad_orca orc', 'orc.id = i.cad_orca_id')
            ->join('condicoes_orcamento o', 'o.ORCAMENTO = i.cad_orca_id')
            ->join('tipo_condicao_parcela_orcamento cond', 'cond.ID = o.TIPO_CONDICAO_ID')
            ->join('tip_rece r', 'r.CODIGO = o.TIPO_RECEBIMENTO')
            ->join('iugu_users u', '(u.user_id = orc.user_id or u.user_id = orc.responsavel_financeiro)')
            ->join('parametro p', 'p.LIG_ID_FILIAL = orc.filial')
            ->where("i.tipo", "C")
            ->where("cond.entrada_matricula", "False")
            ->where("(orc.user_id = ".$this->session->userdata('user_id')." OR orc.responsavel_financeiro = ".$this->session->userdata('user_id').")")
            ->where("p.LIG_STATUS_CONTRATO_06 != orc.status")
            ->where("p.LIG_ID_ANULADO != orc.status")
            ->get("iugu_invoices i")->num_rows();

        //verificando se foi criado o pagamento por cartão recorrente
        $rows_sub = $this->db->select('i.*')
            ->join('cad_orca orc', 'orc.id = i.cad_orca_id')
            ->join('condicoes_orcamento o', 'o.ORCAMENTO = i.cad_orca_id')
            ->join('tipo_condicao_parcela_orcamento cond', 'cond.ID = o.TIPO_CONDICAO_ID')
            ->join('tip_rece r', 'r.CODIGO = o.TIPO_RECEBIMENTO')
            ->join('iugu_users u', '(u.user_id = orc.user_id or u.user_id = orc.responsavel_financeiro)')
            ->join('parametro p', 'p.LIG_ID_FILIAL = orc.filial')
            ->where("cond.entrada_matricula", "False")
            ->where("(orc.user_id = ".$this->session->userdata('user_id')." OR orc.responsavel_financeiro = ".$this->session->userdata('user_id').")")
            ->where("p.LIG_STATUS_CONTRATO_06 != orc.status")
            ->where("p.LIG_ID_ANULADO != orc.status")
            ->get("iugu_subscriptions i")->num_rows();

        if($rows_cc > 0 || $rows_sub > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_contrato_aluno() {
        $contrato = $this->db->select('c.*')
            ->where("c.user_id", $this->session->userdata('user_id'))
            ->get("cad_orca c");
        return $contrato;
    }

    public function get_condicao($cad_orca_id) {
        $condicao = $this->db->select('cond.*, f.NUM_PARCELAS')
            ->join('condicoes_orcamento o', 'o.TIPO_CONDICAO_ID = cond.ID')
            ->join('cad_fpgt f', 'f.CODIGO = cond.Id_formpag')
            ->where("o.ORCAMENTO", $cad_orca_id)
            ->where("cond.entrada_matricula", "False")
            ->get("tipo_condicao_parcela_orcamento cond");
        return $condicao;
    }

    public function get_iugu_user($user_id) {
        $user = $this->db->select('*')
            ->where("user_id", $user_id)
            ->get("iugu_users");
        return $user;
    }

    public function get_tutorias_realizadas($id = null) {
        //if($id == null)
            //$id = $this->session->userdata('user_id');

        $this->db->select('t.*, CONCAT(u.first_name, " ", u.last_name) as nome_aluno, CONCAT(ut.first_name, " ", ut.last_name) as nome_tutor')
            ->join('cad_orca c', 'c.id = t.orc_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('users ut', 't.tutor_id = ut.id');

        if($id != null) {
            $this->db->where("t.tutor_id", $id)
                ->limit(30);
        } else {
            $this->db->where('t.data_hora_inicio BETWEEN NOW() - INTERVAL 10 DAY AND NOW()');
        }

        $tutorias = $this->db->order_by('t.id', 'desc')
            ->get("tutoria t");
        return $tutorias;
    }

    public function get_planos_acao() {
        $planos = $this->db->select('*')
            ->where("empresa_id", $this->session->userdata('empresa_id'))
            ->where("responsavel", $this->session->userdata('user_id'))
            ->get("plano_acao");
        return $planos;
    }

    public function get_audios($where = null) {
        $query_livros = $this->db->select('l.id')
            ->join('livros l', 'al.livro = l.id')
            ->where('l.empresa', $this->session->userdata('empresa_id'))
            ->distinct()
            ->get('aluno_livro al');

        $array_livros = array(0);
        if($query_livros->num_rows() > 0) {
            foreach ($query_livros->result() as $value) {
                $array_livros[] = $value->id;
            }
            
            $implode_livros = implode(",", $array_livros);

            $query = $this->db->select('a.id, a.titulo, a.ativo, a.ordem, l.titulo as livro, ul.unidade, ul.titulo as unidade, ul.unidade as num_unidade, DATE_FORMAT(a.data_cadastro,"%d/%m/%Y %H:%i") as data_cad, l.titulo as livro, a.caminho_audio, a.livro as livro_id, a.unidade as unidade_id, a.titulo_audio');
            $query->join('livros l', 'a.livro = l.id');
            $query->join('unidade_livro ul', 'a.unidade = ul.id');
            if($where != "") {
                $query->where($where);         
            }
            $query->where('l.id IN ('.$implode_livros.')');
            $query->order_by('a.id, a.livro, a.unidade, a.titulo');
            return $query->get('audios a');
        } else {
            return false;
        }        
    }

    public function get_curso($user_id = null)
    {
        $this->load->database();

        if($user_id == null) {
            $user_id = $this->session->userdata('user_id');
        }

        $unidades = $this->get_unidades($user_id);

        if (empty($unidades)) {
            return [];
        }
    
        $unidade_ids = array_column($unidades->result_array(), 'unidade_id');
    
        $query = $this->db->select('c.ID_CURSO, c.DESCRICAO')
                          ->from('curso c')
                          ->join('curso_filial cf', 'cf.LIG_ID_CURSO = c.ID_CURSO')
                          ->where_in('cf.LIG_ID_FILIAL', $unidade_ids)
                          
                          ->group_by('c.ID_CURSO')
                          ->get();
    
        return $query->result_array();
    }


    /**
     * get_livro
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_livro($curso = null, $user_id = null)
{
    $this->load->database();

    // Se não for fornecido o user_id, pega o ID do usuário logado
    if($user_id == null) {
        $user_id = $this->session->userdata('user_id');
    }

    // Obtém as unidades vinculadas ao usuário
    $unidades = $this->get_unidades($user_id);

    // Se não houver unidades, retorna um array vazio
    if (empty($unidades)) {
        return [];
    }

    // Extrai os IDs das unidades vinculadas ao usuário
    $unidade_ids = array_column($unidades->result_array(), 'unidade_id');

    $query = $this->db->distinct()
                    ->select('l.*, c.DESCRICAO as curso')
                    ->join('curso c', 'c.ID_CURSO = l.LIG_ID_CURSO_LIVRO', 'left');

    // Filtra pelos cursos, se o parâmetro $curso for passado
    if($curso != null) {
        if (is_array($curso)) {
            $query->where_in('l.LIG_ID_CURSO_LIVRO', $curso);
        } else {
            $query->where('l.LIG_ID_CURSO_LIVRO', $curso);
        }
    }

    // Adiciona o filtro de unidades, similar ao que é feito na função get_curso
    $query->join('curso_filial cf', 'cf.LIG_ID_CURSO = l.LIG_ID_CURSO_LIVRO', 'left')
          ->where_in('cf.LIG_ID_FILIAL', $unidade_ids);  // Filtra pelas unidades vinculadas ao usuário

    $query->where('l.empresa', $this->session->userdata('empresa_id'));
    $query->where('l.ativo', 'S');

    $query->order_by('l.titulo asc');

    return $query->get('livros l');
}


    /**
     * get_unidades_livro
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_unidades_livro($livro = null)
    {
        $query = $this->db->select('*');
        if($livro != null) {
            $query->where('livro', $livro);
        }
        $query->where('ativo', 'S');
        $query->where('tipo', 'O');
        $query->order_by('unidade, titulo');
        return $query->get('unidade_livro');
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

    public function get_eventos($id = null)
    {
        $admin_pref = $this->db->get_where('admin_preferences', array('empresa_id'=>$this->session->userdata('empresa_id')))->row();
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$this->session->userdata('user_id')))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('e.*, m.descricao as midia_desc, DATE_FORMAT(e.created_at, "%d/%m/%Y %H:%i:%s") as data_formatada, u.unidade as unidade_desc');
        $query->join('midias m', 'e.midia_id = m.id');
        $query->join('unidades u', 'u.id = e.unidade_id');
        if($id != null)
            $query->where('e.id', $id);
        $query->where('m.empresa_id', $this->session->userdata('empresa_id'));
        $query->where('e.fb_id != "" and e.fb_id is not null');
        $query->where('e.unidade_id IN ('.implode(",", $unids_array).')');
        $query->order_by('e.id', 'desc');
        return $query->get("eventos e");
    }

    public function get_unidade_anuncio($id)
    {
        $query = $this->db->select('u.*')
            ->where('u.id', $id);
        return $query->get("unidades u");
    }

    public function has_iugu_entrada($orc_id) {
        $sel = $this->db->get_where('iugu_invoices', array("tipo" => 'M', 'cad_orca_id' => $orc_id));
        return $sel;
    }

    public function get_compras($situacao = "")
    {
        $query = $this->db->select('c.*, u.first_name as nome_comp, u.last_name as sobrenome_comp, uf.first_name as nome_forn, uf.last_name as sobrenome_forn, tp.TIPO as tipo_pedido_desc')
            ->join('users u', 'u.id = c.comprador')
            ->join('users uf', 'uf.id = c.fornecedor')
            // ->join('cad_forn f', 'f.CODIGO = c.fornecedor')
            ->join('unidades un', 'un.id = c.filial')
            // ->join('cad_status s', 's.id = c.status')
            ->join('tipos_pedidos tp', 'tp.CODIGO = c.tipo_pedido', 'left')
            ->where('(c.data_baixa = "" OR c.data_baixa = "0000-00-00" OR c.data_baixa IS NULL)');
        
        if($situacao != "")
            $query->where('c.situacao', $situacao);
        else
            $query->where('(c.situacao != "FI" AND c.situacao != "EA" AND c.situacao != "BM" AND c.situacao != "RE" AND c.situacao != "CA")');

        $query->where('(c.data_receb != "" AND c.data_receb != "0000-00-00" AND c.data_receb IS NOT NULL)')
            ->where('un.empresa_id', $this->session->userdata("empresa_id"))
            ->order_by('c.data_receb asc');
        return $query->get("cad_comp c");
    }

    public function get_prod_compra($id = null)
    {
        if($id == null)
            return false;

        $query = $this->db->select('pc.*, p.DESCRICAO')
            ->join('cad_prod p', 'p.CODIGO = pc.produto')
            ->where('pc.ordem', $id)
            ->order_by('pc.sequencia asc');
        return $query->get("pro_comp pc");
    }

    public function get_vendas($situacao = "")
    {
        $query = $this->db->select('c.*, u.first_name, u.last_name, tp.TIPO as tipo_pedido_desc')
            ->join('users u', 'u.id = c.vendedor')
            ->join('tipos_pedidos tp', 'tp.CODIGO = c.tipo_pedido', 'left')
            ->join('unidades un', 'un.id = c.filial');
            // ->join('cad_status s', 's.id = c.status')
            // ->where('(c.data_baixa = "" OR c.data_baixa = "0000-00-00" OR c.data_baixa IS NULL)')

        if($situacao != "")
            $query->where('c.situacao', $situacao);
        else
            $query->where('(c.situacao != "FI" AND c.situacao != "EA" AND c.situacao != "BM" AND c.situacao != "RE" AND c.situacao != "CA")');

        $query->where('(c.previsao_entrega != "" AND c.previsao_entrega != "0000-00-00" AND c.previsao_entrega IS NOT NULL )')
            ->where('un.empresa_id', $this->session->userdata("empresa_id"))
            ->order_by('c.previsao_entrega asc');
        return $query->get("cad_orca c");
    }

    public function get_prod_venda($id = null)
    {
        if($id == null)
            return false;

        $query = $this->db->select('pc.*, p.DESCRICAO')
            ->join('cad_prod p', 'p.CODIGO = pc.prod_id')
            ->where('pc.orc_id', $id)
            ->order_by('pc.sequencia asc');
        return $query->get("pro_orca pc");
    }

    public function get_grafico_compra_entregas($situacao = "")
    {
        $query = $this->db->select('c.*, u.first_name, u.last_name, uf.first_name as nome_forn, uf.last_name as sobrenome_forn')
            ->join('users u', 'u.id = c.comprador')
            ->join('users uf', 'uf.id = c.fornecedor')
            ->join('unidades un', 'un.id = c.filial')
            // ->join('cad_status s', 's.id = c.status')
            ->where('(c.data_baixa = "" OR c.data_baixa = "0000-00-00" OR c.data_baixa IS NULL)');

        if($situacao != "")
            $query->where('c.situacao', $situacao);
        else
            $query->where('(c.situacao != "FI" AND c.situacao != "EA" AND c.situacao != "BM" AND c.situacao != "RE" AND c.situacao != "CA")');
        
        $query->where('(c.data_receb != "" AND c.data_receb != "0000-00-00" AND c.data_receb IS NOT NULL)')
            ->where('un.empresa_id', $this->session->userdata("empresa_id"))
            ->order_by('c.data_receb asc');
        $entregas = $query->get("cad_comp c");

        $grafico = array();
        $entrega_hoje = 0;
        $entrega_futura = 0;
        $entrega_atrasada = 0;
        if($entregas->num_rows() > 0) {
            foreach ($entregas->result() as $value) {
                if($value->data_receb == date("Y-m-d"))
                    $entrega_hoje++;
                elseif($value->data_receb > date("Y-m-d"))
                    $entrega_futura++;
                elseif($value->data_receb < date("Y-m-d"))
                    $entrega_atrasada++;
            }
            $grafico = array(array(
                "value" => $entrega_hoje,
                "color" => "#0073b7",
                "highlight" => "#0073b7",
                "label" => "Entrega Hoje"
            ), array(
                "value" => $entrega_futura,
                "color" => "#00a65a",
                "highlight" => "#00a65a",
                "label" => "Entrega Futura"
            ), array(
                "value" => $entrega_atrasada,
                "color" => "#dd4b39",
                "highlight" => "#dd4b39",
                "label" => "Entrega Atrasada"
            ));
        }
        return json_encode($grafico);
    }

    public function get_grafico_venda_entregas($situacao = "")
    {
        $query = $this->db->select('c.*, u.first_name, u.last_name')
            ->join('users u', 'u.id = c.vendedor')
            ->join('unidades un', 'un.id = c.filial');
            // ->join('cad_status s', 's.id = c.status')
            // ->where('(c.data_baixa = "" OR c.data_baixa = "0000-00-00" OR c.data_baixa IS NULL)')

        if($situacao != "")
            $query->where('c.situacao', $situacao);
        else
            $query->where('(c.situacao != "FI" AND c.situacao != "EA" AND c.situacao != "BM" AND c.situacao != "RE" AND c.situacao != "CA")');

        $query->where('(c.previsao_entrega != "" AND c.previsao_entrega != "0000-00-00" AND c.previsao_entrega IS NOT NULL )')
            ->where('un.empresa_id', $this->session->userdata("empresa_id"))
            ->order_by('c.previsao_entrega asc');
        $entregas = $query->get("cad_orca c");

        $grafico = array();
        $entrega_hoje = 0;
        $entrega_futura = 0;
        $entrega_atrasada = 0;
        if($entregas->num_rows() > 0) {
            foreach ($entregas->result() as $value) {
                if($value->previsao_entrega == date("Y-m-d"))
                    $entrega_hoje++;
                elseif($value->previsao_entrega > date("Y-m-d"))
                    $entrega_futura++;
                elseif($value->previsao_entrega < date("Y-m-d"))
                    $entrega_atrasada++;
            }
            $grafico = array(array(
                "value" => $entrega_hoje,
                "color" => "#0073b7",
                "highlight" => "#0073b7",
                "label" => "Entrega Hoje"
            ), array(
                "value" => $entrega_futura,
                "color" => "#00a65a",
                "highlight" => "#00a65a",
                "label" => "Entrega Futura"
            ), array(
                "value" => $entrega_atrasada,
                "color" => "#dd4b39",
                "highlight" => "#dd4b39",
                "label" => "Entrega Atrasada"
            ));
        }
        return json_encode($grafico);
    }

    public function get_sincronizacao_status($unidade_id)
    {
        $query = $this->db->select('*')
            ->where('unidade_id', $unidade_id)
            ->limit(1);
        $sinc = $query->get("sincronizacao_status");
        return $sinc;
    }

    public function get_contratos_ead_renovar($id = null)
    {
        //if($user_id == null)
        $user_id = $this->session->userdata('user_id');
        
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$user_id))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select('c.*, c.data_matricula, u.first_name, u.last_name, un.unidade, cu.DESCRICAO as curso_desc, s.descricao as status_desc, is.iugu_id')
            ->join('users u', 'c.user_id = u.id')
            ->join('unidades un', 'un.id = c.filial')
            ->join('parametro p', 'p.LIG_ID_FILIAL = un.id AND c.status != p.LIG_ID_ANULADO AND c.status != p.LIG_STATUS_CONTRATO_06 AND c.status != p.LIG_ID_CONCLUIDO')
            ->join('curso cu', 'cu.ID_CURSO = c.lig_id_curso', 'left')
            ->join('cad_status s', 's.id = c.status')
            ->join('iugu_subscriptions is', 'is.cad_orca_id = c.id')
            ->where('c.tipo_saxweb', '2')
            ->where('is.status', '1')
            ->where('un.empresa_id', $this->session->userdata('empresa_id'))
            ->where('un.id IN ('.implode(",", $unids_array).')');
        
        if($id != null) {
            $query->where('c.id', $id);
        }
        $query->order_by('c.data_pedido', 'desc');
        $res = $query->group_by('c.id')
            ->get("cad_orca c");

        $contratos = array();
        if($res->num_rows() > 0) {
            foreach ($res->result() as $value) {
                $parcelas = $this->db->select('p.id, ct.VENCIMENTO')
                    ->join('ct_receb ct', 'ct.PARCELA_ID = p.id')
                    ->join('parcela_servico_condicoes_orca ps', 'ps.CODIGO_PARCELA_GERADA = p.id')
                    ->join('servico_condicoes_orcamento sc', 'ps.SERVICO_CONDICOES_ORCA = sc.CODIGO')
                    ->join('condicoes_orcamento co', 'co.CODIGO = sc.CONDICOES_ORCAMENTO')
                    ->join('tipo_condicao_parcela_orcamento t', 'co.TIPO_CONDICAO_ID = t.ID')
                    ->where('t.entrada_matricula', 'False')
                    ->where('p.orc_id', $value->id)
                    ->where('p.parcela', $value->qnt_parcelas)
                    ->where('ct.VALOR_PAGO != 0')
                    ->get('par_orca p');
                if($parcelas->num_rows() > 0) {
                    $value->vencimento = $parcelas->row()->VENCIMENTO;
                    $contratos[] = $value;
                }
            }
        }
        // exit("<pre>".print_r($res->result(),true)."</pre>");
        return $contratos;
    }
    
    public function get_interface($table)
    {
        $query = $this->db->get_where($table, array("empresa_id"=>$this->session->userdata('empresa_id')));

        if ($query->num_rows() > 0)
        {
            return $query->row();
        }
        else
        {
            return FALSE;
        }
    }

    public function get_contatos_interno($id = null, $where = "", $limit = null)
    {
        $query = $this->db->select('c.*, u.first_name, u.last_name, DATE_FORMAT(c.created_at, "%d/%m/%Y %H:%i:%s") as data_envio');
        $query->join("users u", "u.id = c.user_id_de");
        $query->where("c.user_id_para = '".$this->session->userdata('user_id')."'");
        if($where != "")
            $query->where($where);
        if($id != null)
            $query->where('c.id', $id);
        $query->order_by("c.id", "desc");
        if($limit != null)
            $query->limit($limit);
        return $query->get("contatos_interno c");
    }

    public function get_groups($user_id = null) {
        $query = $this->db->select('g.*');
        if($user_id != null) {
            $query->join('users_groups ug', 'ug.group_id = g.id');
            $query->where('ug.user_id', $user_id);
        }
        $query->where('g.empresa_id', $this->session->userdata('empresa_id'));
        $query = $query->get('groups g');

        if ($query->num_rows() > 0)
        {
            $return_array = [];
            foreach ($query->result() as $group) {
                $return_array[$group->id] = $group->description;
            }
            return $return_array;
        }
        else
        {
            return FALSE;
        }
    }

    public function get_dashboard($mostrar)
    {
        $query = $this->db->select('*');
        $query->where("empresa_id", $this->session->userdata('empresa_id'));
        $query->where("mostrar IN (".$mostrar.")");
        $query = $query->get("dashboard");
        return $query;
    }

    public function generate_dashboard($mostrar = "'A'") {
        $dashboards = $this->get_dashboard($mostrar);
        $grupos_user = $this->get_groups($this->session->userdata('user_id'));
        $data_page = array();
        $array_pages = array();
        $load_scripts = true;
        if($dashboards->num_rows() > 0) {
            foreach ($dashboards->result() as $dash) {
                $has_in_group = false;
                foreach (unserialize($dash->grupo) as $g) {
                    if (array_key_exists($g, $grupos_user)) {
                        $has_in_group = true;
                    }
                }

                if($has_in_group == true) {
                    $show = false;
                    if($dash->mostrar == "A" || $dash->mostrar == "M") {
                        $show = true;
                    } else {
                        if($this->session->userdata("modulo_id") == $dash->modulo) {
                            $show = true;
                        }
                    }
                    if ($show == true) {                
                        if($dash->dashboard == "padrao") {
                            $data_page['count_users']       = $this->get_count_record('empresas_users');
                            $data_page['count_groups']      = $this->get_count_record('groups');
                            $data_page['memory_usage']      = $this->memory_usage();
                            $data_page['memory_peak_usage'] = $this->memory_peak_usage(TRUE);
                            $data_page['memory_usepercent'] = $this->memory_usepercent(TRUE, FALSE);
                        } elseif($dash->dashboard == "gestor") {
                            $data_page['msg'] = "<p><h4>Olá, Gestores!</h4></p>
                                <p>Assista os Vídeos sobre o curso de Cuidador do CEBRAC e conheça os detalhes fundamentais para tornar este produto um grande sucesso de vendas em sua unidade!</p>
                                <p>Os vídeos são temáticos e cada um deles aborda um aspecto importante sobre o curso e sua implantação.</p>
                                <p>Todos eles foram desenvolvidos para apoiar e facilitar o seu trabalho de implantação do curso, ambientando toda a equipe para a sua divulgação.</p>
                                <p>Não se esqueça de acessar seu email para conhecer a sugestão das etapas de implantação do curso na sua Unidade.</p>
                                <p>Os vídeos disponibilizados aqui poderão ser usados na divulgação para a equipe interna e para treinamento dos setores envolvidos.</p>
                                <p>Acesse os vídeos por meio do menu que está a esquerda!</p>
                                <br>
                                <p>Ótimas vendas!</p>";
                        } elseif($dash->dashboard == "aluno_ead") {
                            $retorno_aval = $this->voxy->make_request("/partners/users/{$this->session->userdata('user_id')}/assessments", "GET");
                            
                            if(@$retorno_aval['status_code'] == 200) {
                                $content = json_decode($retorno_aval["content"], true);
                                if(count($content) > 0) {
                                    $data_page['adjusted_score'] = $content[0]['adjusted_score'];
                                    $data_page['total_score'] = $content[0]['total_score'];
                                    $data_page['possible_points'] = $content[0]['possible_points'];
                                    $data_page['reading_score'] = $content[0]['reading_score'];
                                    $data_page['reading_level_name'] = $content[0]['reading_level_name'];
                                    $data_page['grammar_score'] = $content[0]['grammar_score'];
                                    $data_page['grammar_level_name'] = $content[0]['grammar_level_name'];
                                    $data_page['listening_score'] = $content[0]['listening_score'];
                                    $data_page['listening_level_name'] = $content[0]['listening_level_name'];
                                    $data_page['overall_level_name'] = $content[0]['overall_level_name'];
                                }
                            }
                            
                            $retorno_login = $this->voxy->make_request("/partners/users/{$this->session->userdata('user_id')}/auth_token", "GET");
                            if(@$retorno_login["status_code"] == 200) {
                                $content_login = json_decode($retorno_login["content"]);
                                $data_page['url_login'] = $content_login->actions->start;
                            }

                            $tutores = array(""=>"Selecione");
                            $tutores_model = $this->get_tutores();
                            if($tutores_model) {
                                foreach ($tutores_model->result() as $usr) {
                                    $tutores[$usr->id] = $usr->first_name." ".$usr->last_name;
                                }
                            }
                            $data_page['tutor'] = array(
                                'name'  => 'tutor',
                                'id'    => 'tutor',
                                'class' => 'form-control',
                                'options' => $tutores,
                                'selected' => ''
                            );

                            $data_page['has_pagamento_entrada_aluno'] = $this->has_pagamento_entrada_aluno();
                            $data_page['has_pagamento_contrato_aluno'] = $this->has_pagamento_contrato_aluno();
                        } elseif($dash->dashboard == "aluno_app") {
                            $ddate = date('Y-m-d');
                            $date = new DateTime($ddate);
                            $week = $date->format("W");
                            $year = $date->format("Y");

                            $retorno = array();
                            $k = 0;
                            $j = 1;
                            for($i = 0; $i < 7; $i++) {
                                if(($week-$j) == 0) {
                                    $year--;
                                    $ddate = $year.'-12-31';
                                    $date = new DateTime($ddate);
                                    $week = $date->format("W");
                                    $j = 0;
                                }
                                $week_show = ($week-$j);
                                $between_dates = $this->get_start_and_end_date(($week-($j-1)), $year);
                                $count_resps = $this->get_count_resps($between_dates);
                                $retorno[$k]['y'] = $year.' W'.$week_show;
                                $retorno[$k]['qtd'] = $count_resps;
                                $k++;
                                $j++;
                            }
                            $data_page['grafico_audios'] = json_encode($retorno);
                            $data_page['app_respostas'] = $this->get_respostas_lista()->result();
                            $data_page['dispositivos'] = $this->get_dispositivos()->result();
                            
                            $where = "";
                            if($_POST) {
                                if($this->input->post('livro')) {
                                    $where .= "a.livro = '".$this->input->post('livro')."'";
                                }
                                if($this->input->post('unidade_livro')) {
                                    if($where != "")
                                        $where .= " AND ";
                                    $where .= "a.unidade = '".$this->input->post('unidade_livro')."'";
                                }
                            }

                            $data_page['audios'] = $this->get_audios($where);
                            $data_page['materiais'] = $this->get_materiais($where);
                            $livros = $this->get_livro()->result();
                            $livros_array = array(""=>"Selecione");
                            foreach ($livros as $value) {
                                $livros_array[$value->id] = $value->titulo;
                            }
                            $data_page['livros'] = array(
                                'name'  => 'livro',
                                'id'    => 'livro',
                                'onchange' => "loadUnidadesLivro(this.value, '".base_url()."')",
                                'class' => 'form-control',
                                'options' => $livros_array,
                                'selected' => ($this->input->post('livro')) ? $this->input->post('livro') : ''
                            );

                            $unidades_array = array(""=>"Selecione");
                            if($this->input->post('livro')) {
                                $unidades_livro = $this->get_unidades_livro($this->input->post('livro'))->result();
                                foreach ($unidades_livro as $value) {
                                    $unidades_array[$value->id] = $value->unidade." - ".$value->titulo;
                                }
                            }
                            $data_page['unidades'] = array(
                                'name'  => 'unidade_livro',
                                'id'    => 'unidade_livro',
                                'class' => 'form-control',
                                'options' => $unidades_array,
                                'selected' => ($this->input->post('unidade_livro')) ? $this->input->post('unidade_livro') : ''
                            );
                        } elseif($dash->dashboard == "franqueadora_app") {
                            $data_page['unidades'] = $this->get_unidades()->num_rows();
                            $data_page['alunos'] = $this->get_alunos()->num_rows();
                            $data_page['respostas_alunos'] = $this->get_respostas_alunos()->num_rows();
                            $data_page['respostas'] = $this->get_respostas()->num_rows();
                            // exit("<pre>".print_r($this->db->last_query(),true)."</pre>");
                        } elseif($dash->dashboard == "chamados") {
                            //$data_page['abertos'] = $this->get_chamados('(c.responsavel_id = "'.$this->session->userdata('user_id').'" OR c.dono_id = "'.$this->session->userdata('user_id').'") AND c.status_id = "1"')->num_rows();

                            if(@$_GET["filtro_range"]) {
                                $explode = explode(" - ", $_GET["filtro_range"]);
                                $start = convertDateBRToEU($explode[0]);
                                $end = convertDateBRToEU($explode[1]);
                            } else {
                                $start = date('Y-m-01');
                                $end = date('Y-m-d');
                            }

                            $unidades = $this->get_unidades()->result();
                            $unids_array = array();
                            $unids_array_show = array();
                            foreach ($unidades as $unid) {
                                $unids_array[] = $unid->unidade_id;
                                $unids_array_show[$unid->unidade_id] = $unid->unidade;
                            }

                            if(@$_GET["filtro_unidades"]) {
                                $avaliacoes = $this->get_chamados_avaliados($start, $end, $_GET["filtro_unidades"]);
                            } else {
                                $avaliacoes = $this->get_chamados_avaliados($start, $end, $unids_array);
                            }

                            $data_page['abertos'] = $this->get_chamados('c.status_id = "1"', $unids_array)->num_rows();
                            $data_page['aguardando_teste'] = $this->get_chamados_aguardando_teste('c.status_id = "8" AND responsavel_id = '.$this->session->userdata('user_id'), $unids_array);
                            $data_page['avaliacoes'] = $avaliacoes["chamados"];
                            $data_page['media_avaliacoes'] = $avaliacoes["media"];
                            $data_page['filtro_range'] = array(
                                'name'  => 'filtro_range',
                                'id'    => 'filtro_range',
                                'class' => 'form-control',
                                'value' => convertDateEUToBR($start)." - ".convertDateEUToBR($end)
                            );
                            $data_page['filtro_range_aval'] = array(
                                'name'  => 'filtro_range',
                                'id'    => 'filtro_range_aval',
                                'class' => 'form-control',
                                'value' => convertDateEUToBR($start)." - ".convertDateEUToBR($end)
                            );
                            $data_page['filtro_unidades'] = array(
                                'name'  => 'filtro_unidades[]',
                                'id'    => 'filtro_unidades',
                                'class' => 'multipleSelect',
                                'multiple' => 'multiple',
                                'options' => $unids_array_show,
                                'selected' => ''
                            );
                            // exit("<pre>".print_r($_GET,true)."</pre>");
                            $data_page['compartilhados'] = $this->get_chamados_compartilhados($unids_array);
                            $data_page['avaliacao'] = $this->get_chamados('(c.responsavel_id = "'.$this->session->userdata('user_id').'" OR c.dono_id = "'.$this->session->userdata('user_id').'") AND c.status_id = "3"', $unids_array)->num_rows();

                            $retorno = array();
                            $k = 0;
                            $ymax = 0;
                            for($i = 6; $i >= 0; $i--) {
                                $date = strtotime(date('Y-m-d').' -'.$i.' months');
                                $retorno[$k]['month'] = date('m/Y', $date);
                                $retorno[$k]['qc'] = $this->get_chamados_abertos(date('Y-m-d', $date), $unids_array)->num_rows();
                                $retorno[$k]['q'] = $this->get_chamados_finalizados(date('Y-m-d', $date), $unids_array)->num_rows();
                                if($retorno[$k]['q'] > $ymax)
                                    $ymax = $retorno[$k]['q'];
                                if($retorno[$k]['qc'] > $ymax)
                                    $ymax = $retorno[$k]['qc'];
                                $k++;
                            }
                            
                            //exit("<pre>".print_r($retorno,true)."</pre>");
                            $data_page['ymax'] = $ymax;
                            $data_page['grafico_chamados'] = json_encode($retorno);
                        } elseif($dash->dashboard == "mapa_calor") {                                
                            $status = $this->get_cad_status();
                            $alunos = $this->get_alunos();
                            $array_alunos = array();
                            if($alunos->num_rows() > 0) {
                                foreach ($alunos->result() as $aluno) {
                                    if($aluno->latitude != "" && $aluno->longitude != "") {
                                        if($aluno->endereco != "" && ($aluno->numero != "" && $aluno->numero != 0) && $aluno->bairro != "") {
                                            $morada1 = $aluno->endereco.", ".$aluno->numero." - ".$aluno->bairro;
                                        } else {
                                            $morada1 = "";
                                        }

                                        if($aluno->cidade != "" && $aluno->uf != "") {
                                            $morada2 = $aluno->cidade."/".$aluno->uf;
                                        } else {
                                            $morada2 = "";
                                        }

                                        $array_alunos[] = array(
                                            "lat" => $aluno->latitude,
                                            "lng" => $aluno->longitude,
                                            "nome" => $aluno->first_name." ".$aluno->last_name,
                                            "morada1" => $morada1,
                                            "morada2" => $morada2,
                                            "codPostal" => $aluno->cep,
                                            "email" => $aluno->email,
                                            "celular" => $aluno->celular
                                        );
                                    }
                                }
                            }
                            // exit("<pre>".print_r($array_alunos,true)."</pre>");
                            $data_page["status"] = $status;
                            $data_page["alunos"] = json_encode($array_alunos);
                        } elseif($dash->dashboard == "contratos_ead") {
                            $data_page['contratos'] = $this->get_contratos_ead()->result();
                            $seguimentos_usuario_array = array();
                            $seguimentos_usuario_session = $this->get_segments()->result();
                            if(count($seguimentos_usuario_session) > 0) {
                                foreach ($seguimentos_usuario_session as $seg) {
                                    $seguimentos_usuario_array[] = $seg->segment_label;
                                }
                            }
                            $data_page['seguimentos_lista'] = $seguimentos_usuario_array;
                            
                            $k = 0;
                            foreach ($data_page['contratos'] as $k => $contrato)
                            {
                                $contrato = $this->get_contratos_ead($contrato->id)->row();

                                $segments_array = array();
                                if(@$contrato->id) {
                                    $segments = $this->get_segments($contrato->id);
                                    if(@$segments->num_rows() > 0) {
                                        foreach ($segments->result() as $seg) {
                                            $segments_array[] = $seg->segment_label;
                                        }
                                    }
                                }

                                $data_page['contratos'][$k]->seguimentos = $segments_array;
                                $data_page['contratos'][$k]->cliente_voxy = $this->is_voxy_user($contrato->user_id);
                                if($data_page['contratos'][$k]->has_pesquisa = $this->has_pesquisa($contrato->id)) {
                                    $data_page['contratos'][$k]->pesquisa_perfil = $this->get_pesquisa($contrato->id);
                                }

                                $data_page['contratos'][$k]->can_anular = false;
                                $parametro = $this->get_parametro($contrato->filial);
                                if($parametro->LIG_STATUS_CONTRATO_01 == $contrato->status) {
                                    $data_page['contratos'][$k]->can_anular = true;
                                }

                                $data_page['contratos'][$k]->can_gerar_segunda_via = false;
                                $has_iugu_entrada = $this->has_iugu_entrada($contrato->id);
                                if($parametro->LIG_STATUS_CONTRATO_01 == $contrato->status && $contrato->tipo_saxweb == 2 && $has_iugu_entrada->num_rows() == 1 && $this->ion_auth->has_permissao("iugu", "gerar_segunda_via_entrada")) {
                                    $data_page['contratos'][$k]->can_gerar_segunda_via = true;
                                }

                                $data_page['contratos'][$k]->can_cartao_recorrencia = false;
                                if($this->ion_auth_model->is_recorrencia($contrato->id, $contrato->user_id) && $this->ion_auth->has_permissao("iugu", "cartao_recorrencia")) {
                                    $data_page['contratos'][$k]->can_cartao_recorrencia = true;
                                }

                                $data_page['contratos'][$k]->can_alterar_assinatura = false;
                                if($this->ion_auth_model->is_recorrencia($contrato->id, $contrato->user_id) && $this->ion_auth->has_permissao("iugu", "alterar_assinatura")) {
                                    $data_page['contratos'][$k]->can_alterar_assinatura = true;
                                }

                                $k++;
                            }
                            // exit("<pre>".print_r($data_page['contratos'],true)."</pre>");
                        } elseif($dash->dashboard == "materiais") {
                            $data_page['materiais'] = $this->get_materiais();
                        }elseif($dash->dashboard == "correcoes_professores") {
                            $data_page['correcoes'] = $this->get_correcoes_professores();
                        } elseif($dash->dashboard == "supervisor") {
                            //$data_page['ultimo_acesso_tutores'] = $this->get_ultimo_acesso_tutores();
                            $data_page['detalhes_tutores'] = $this->get_detalhes_tutores();
                            $data_page['cancelamentos'] = $this->get_contratos_ead_cancelados();
                            $tutorias_realizadas = $this->get_tutorias_realizadas()->result();
                            foreach ($tutorias_realizadas as $_key => $tut) {
                                if($tut->desempenho_geral == "higher")
                                    $desempenho_geral = '<i class="fa fa-stop" style="color:#00a65a"><label style="display:none;">higher</label></i>';
                                if($tut->desempenho_geral == "higher_medium")
                                    $desempenho_geral = '<i class="fa fa-stop" style="color:#f3d312"><label style="display:none;">higher_medium</label></i>';
                                if($tut->desempenho_geral == "medium")
                                    $desempenho_geral = '<i class="fa fa-stop" style="color:#ff851b"><label style="display:none;">medium</label></i>';
                                if($tut->desempenho_geral == "emerging")
                                    $desempenho_geral = '<i class="fa fa-stop" style="color:#dd4b39"><label style="display:none;">emerging</label></i>';
                                $tutorias_realizadas[$_key]->desempenho_geral = $desempenho_geral;
                            }
                            $data_page['tutorias_realizadas'] = $tutorias_realizadas;
                            $data_page['desistentes'] = $this->get_desistentes();

                            $tutores_array = array(""=>"Todos");
                            if(count($data_page['detalhes_tutores']) > 0) {
                                foreach ($data_page['detalhes_tutores'] as $t) {
                                    $tutores_array[$t->id] = $t->email;
                                }
                            }

                            $data_page['tutores_filtro'] = array(
                                'name'  => 'tutores',
                                'id'    => 'tutores',
                                'class' => 'form-control',
                                'options' => $tutores_array,
                                'selected' => ''
                            );
                            $data_page['tutores'] = $this->get_desistentes();

                            $nivel_alunos = $this->get_nivel_alunos();
                            $na_array = array(
                                "higher" => 0,
                                "higher_medium" => 0,
                                "medium" => 0,
                                "emerging" => 0,
                                "total" => 0
                            );
                            if($nivel_alunos->num_rows() > 0) {
                                $last_na = "";
                                foreach ($nivel_alunos->result() as $na) {
                                    if($last_na != $na->orc_id) {
                                        $na_array[$na->desempenho_geral] = $na_array[$na->desempenho_geral] + 1;
                                        $na_array["total"] = $na_array["total"] + 1;
                                    }
                                    $last_na = $na->orc_id;
                                }
                            }
                            $na_json = array(
                                array(
                                    "value" => $na_array['higher'],
                                    "color" => '#00a65a',
                                    "highlight" => '#00a65a',
                                    "label" => 'Higher'
                                ),
                                array(
                                    "value" => $na_array['higher_medium'],
                                    "color" => '#f3d312',
                                    "highlight" => '#f3d312',
                                    "label" => 'Higher Medium'
                                ),
                                array(
                                    "value" => $na_array['medium'],
                                    "color" => '#ff851b',
                                    "highlight" => '#ff851b',
                                    "label" => 'Medium'
                                ),
                                array(
                                    "value" => $na_array['emerging'],
                                    "color" => '#dd4b39',
                                    "highlight" => '#dd4b39',
                                    "label" => 'Emerging'
                                )
                            );

                            //------ comentado dia 20/02/2020 ------
                            // $data_page['json_nivel_alunos'] = json_encode($na_json);

                            //$metas_atingidas = $this->get_json_grafico_metas_atingidas();

                            // $data_page['json_metas_atingidas'] = json_encode(array_values($metas_atingidas["elements"]));
                            // $data_page['json_x_metas_atingidas'] = json_encode($metas_atingidas["x"]);
                            // $data_page['json_labels_metas_atingidas'] = json_encode($metas_atingidas["labels"]);
                            //------ ------
                        } elseif($dash->dashboard == "planos_acao") {
                            $data_page['planos_acao'] = $this->get_planos_acao();
                        } elseif($dash->dashboard == "chamados_responsavel") {
                            $data_page['chamados'] = $this->get_chamados_responsavel('c.responsavel_id = "'.$this->session->userdata('user_id').'" AND c.status_id != 3 AND c.status_id != 5');
                        } elseif($dash->dashboard == "grupo_anuncio") {
                            $data_page['eventos'] = $this->get_eventos()->result();
                            // exit($this->db->last_query());
                            // exit("<pre>".print_r($data_page['eventos'],true)."</pre>");
                        } elseif($dash->dashboard == "compra_venda") {
                            $data_page['compras'] = $this->get_compras(@$_GET["situacao_compra"]);
                            $data_page['vendas'] = $this->get_vendas(@$_GET["situacao_venda"]);
                            $data_page['graficoCompra'] = $this->get_grafico_compra_entregas(@$_GET["situacao_compra"]);
                            $data_page['graficoVenda'] = $this->get_grafico_venda_entregas(@$_GET["situacao_venda"]);
                            $data_page['filtro_situacao_compra'] = array(
                                'name'  => 'filtro_situacao_compra',
                                'id'    => 'filtro_situacao_compra',
                                'class' => 'form-control',
                                'options' => array(""=>"", "EA"=>"Em andamento", "AI" => "Aprovado integral", "AP"=>"Aprovado parcial", "RE" => "Reprovado", "FP"=>"Faturado parcial", "CA"=>"Cancelado", "BM"=>"Baixa manual", "FI"=>"Faturado integral"),
                                'selected' => (@$_GET["situacao_compra"]) ? $_GET["situacao_compra"] : ''
                            );
                            $data_page['filtro_situacao_venda'] = array(
                                'name'  => 'filtro_situacao_venda',
                                'id'    => 'filtro_situacao_venda',
                                'class' => 'form-control',
                                'options' => array(""=>"", "EA"=>"Em andamento", "AI" => "Aprovado integral", "AP"=>"Aprovado parcial", "RE" => "Reprovado", "FP"=>"Faturado parcial", "CA"=>"Cancelado", "BM"=>"Baixa manual"),
                                'selected' => (@$_GET["situacao_venda"]) ? $_GET["situacao_venda"] : ''
                            );
                            //567 provisorio trilar
                            $data_page['sinc'] = $this->get_sincronizacao_status(567);
                            $load_scripts = false;
                        } elseif($dash->dashboard == "contratos_renovar") {
                            $data_page['contratos'] = $this->get_contratos_ead_renovar();
                            $data_page['can_suspender'] = false;
                            if($this->ion_auth->has_permissao("iugu", "suspender_assinatura")) {
                                $data_page['can_suspender'] = true;
                            }
                            $data_page['can_alterar'] = false;
                            if($this->ion_auth->has_permissao("iugu", "alterar_assinatura")) {
                                $data_page['can_alterar'] = true;
                            }

                            $status = $this->ion_auth_model->get_historico_status()->result();
                            $status_array = array(""=>"Selecione");
                            foreach ($status as $st) {
                                $status_array[$st->id] = $st->descricao;
                            }
                            $data_page['status'] = array(
                                'name'  => 'status',
                                'id'    => 'status',
                                'class' => 'form-control',
                                'options' => $status_array,
                                'selected' => ''
                            );
                            $data_page['motivo'] = array(
                                'name'  => 'motivo',
                                'id'    => 'motivo',
                                'class' => 'form-control',
                                'value' => ''
                            );
                            //exit("<pre>".print_r($data_page['contratos'],true)."</pre>");
                        } elseif($dash->dashboard == "mensagens") {
                            $data_page['contatos'] = $this->get_contatos_interno(null, "c.visualizada = 'N'", 5);
                            //exit("<pre>".print_r($data_page['contratos'],true)."</pre>");
                        }

                        $data['array_pages'][] = array('admin/dashboard/'.$dash->dashboard.'.php', @$data_page);
                        break;
                    }
                }
            }
        }
        $data['data_page'] = $data_page;
        $data['load_scripts'] = $load_scripts;
        return $data;
    }
	
	public function get_unidade($unidade_id = null)
    {
        $query = $this->db->select('u.*');

        if($unidade_id != null)
            $query->where('u.id', $unidade_id);

        return $query->get("unidades u");
    }

    public function get_audios_exercicios($where = null) {

        $query = $this->db->select('a.id, a.titulo_exercicio, a.ordem_exercicio, a.arquivo, ul.unidade as num_unidade');
        $query->join('unidade_livro ul', 'a.unidade = ul.id');
        if($where != "") {
            $query->where($where);            
        }
        $query->where('a.audio', 'S');
        $query->order_by('a.id, a.unidade, a.titulo_exercicio');
        return $query->get('exercicios a');

    }
}
