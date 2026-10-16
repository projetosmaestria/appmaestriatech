<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cupom_model extends CI_Model {

    protected $table = 'cupom';

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
    public function get($id = null, $where = "")
    {
        $query_unids = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
            $unids[] = $value->unidade_id;
        }

        $query = $this->db->select('c.*, CONCAT(v.first_name, " ", v.last_name) as vendedor, o.id as orc_id, CONCAT(u.first_name, " ", u.last_name) as utilizado')
            ->join('users v', 'v.id = c.vendedor_id')
            ->join('cad_orca o', 'o.id = c.orc_id', 'left')
            ->join('users u', 'u.id = o.user_id', 'left');
        $query->where('c.unidade_id IN ('.implode(",", $unids).')'.$where);
        if($id != null) {
            $query->where('c.id', $id);
        }
        $query->order_by('c.data_criacao', 'desc');
        return $query->get($this->table.' c');
    }

    public function get_contratos($where = "") {
        $query = $this->db->select('c.*, CONCAT(u.first_name, " ", u.last_name) as cliente');
        $query->join('unidades un', 'c.filial = un.id');
        $query->join('users u', 'u.id = c.user_id');
        $query->join('curso cs', 'cs.ID_CURSO = c.lig_id_curso');
        $query->where('un.empresa_id', $this->session->userdata('empresa_id'));
        if($where != "") {
            $query->where("c.id NOT IN (".$where.")");
        }
        return $query->get('cad_orca c');
    }

    public function get_contratos_ids() {
        $query = $this->db->select('p.orc_id');
        $query->join('cad_orca c', 'c.id = p.orc_id');
        $query->join('unidades un', 'c.filial = un.id');
        $query->join('users u', 'u.id = c.user_id');
        $query->where('un.empresa_id', $this->session->userdata('empresa_id'));
        $query->distinct();
        return $query->get($this->table.' p');
    }

    public function get_vendedores($unidade_id = null) {
        $unids = array(0);

        if($unidade_id != null)
            $unids[] = $unidade_id;
        else {
            $query_unids = $this->db->select('uu.*')
                ->join('unidades u', 'u.id = uu.unidade_id')
                ->where('u.empresa_id', $this->session->userdata('empresa_id'))
                ->where('uu.user_id', $this->session->userdata('user_id'))
                ->get('user_unidade uu');
            foreach ($query_unids->result() as $value) {
                $unids[] = $value->unidade_id;
            }
        }

        $query_vendedor_grupo = $this->get_admin_preferences();

        $query = $this->db->select('v.*');
        $query->join('user_unidade uu', 'uu.user_id = v.id');
        $query->join('users_groups ug', 'ug.user_id = v.id');
        $query->where('ug.group_id', $query_vendedor_grupo->grupo_vendedor);
        $query->where('uu.unidade_id IN ('.implode(",",$unids).')');
        $query->where('v.active', 1);
        $query->order_by('v.first_name', 'asc');
        $query->distinct();
        return $query->get('users v');
    }

    public function get_admin_preferences($emp_id = null) {
        if($emp_id == null)
            $emp_id = $this->session->userdata('empresa_id');

        return $this->db->get_where('admin_preferences', array('empresa_id' => $emp_id))->row();
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        if($this->db->insert($this->table, $data)) {
            $this->set_message('creation_successful');
            $id = $this->db->insert_id();
        }

        return $id;
    }

    public function remove_vinculo($cupom_id) {
        $this->db->delete($this->table."_condicao", array('cupom_id' => $cupom_id));
        return true;
    }
    public function create_vinculo($cupom_id, $condicoes = array())
    {
        if(count($condicoes) > 0) {
            foreach ($condicoes as $cond) {
                $exist = $this->db->get_where($this->table."_condicao", array("cupom_id"=>$cupom_id, "condicao_id"=>$cond["id"], "order"=>$cond["order"]));
                if($exist->num_rows() == 0)
                    $this->db->insert($this->table."_condicao", array("cupom_id"=>$cupom_id, "condicao_id"=>$cond["id"], "order"=>$cond["order"]));
            }
        }
        return true;
    }

    public function remove_vinculo_disciplina($cupom_id) {
        $this->db->delete($this->table."_disciplina", array('cupom_id' => $cupom_id));
        return true;
    }
    public function create_vinculo_disciplina($cupom_id, $disciplinas = array())
    {
        if(count($disciplinas) > 0) {
            foreach ($disciplinas as $disc) {
                $exist = $this->db->get_where($this->table."_disciplina", array("cupom_id"=>$cupom_id, "disciplina_id"=>$disc));
                if($exist->num_rows() == 0)
                    $this->db->insert($this->table."_disciplina", array("cupom_id"=>$cupom_id, "disciplina_id"=>$disc));
            }
        }
        return true;
    }

    public function get_vinculo($cupom_id)
    {
        return $this->db->get_where($this->table."_condicao", array('cupom_id' => $cupom_id));
    }

    public function cupom_exist($cupom)
    {
        $exist = $this->db->select('*')
            ->where("cupom", $cupom)
            ->where('(orc_id != "" AND orc_id IS NOT NULL)')
            ->where('data_expiracao > "'.date("Y-m-d H:i:s").'"')
            ->get($this->table);
        
        if($exist->num_rows() == 0)
            return false;
        return true;
    }

    public function get_historico($cupom_id)
    {
        $query = $this->db->select('a.*, u.first_name, u.last_name');
        $query->join('users u', 'u.id = a.user_id');
        $query->where("a.empresa_id", $this->session->userdata('empresa_id'));
        $query->where('a.controlador', "cupom");
        $query->where('a.metodo', "enviar");
        $query->where('a.obs LIKE ("%cupom {'.$cupom_id.'}%")');
        $query->order_by("a.id", "desc");

        return $query->get('auditorias a');
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

    /*public function get_condicoes($user_id = null, $entrada = "FALSE")
    {
        $query_unids = $this->db->select('uu.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        $unids = array(0);
        foreach ($query_unids->result() as $value) {
            $unids[] = $value->unidade_id;
        }

        $query = $this->db->select('c.*, p.DESCRICAO as pag_desc, r.NOME, u.unidade')
            ->join('cad_fpgt p', 'p.CODIGO = c.Id_formpag')
            ->join('tip_rece r', 'r.CODIGO = c.Id_TipoReceb')
            ->join('unidades u', 'u.id = c.unidade_id')
            ->where("c.unidade_id IN (".implode(",", $unids).")")
            ->where("UPPER(c.entrada_matricula)", $entrada);

        if($user_id != null) {
            $query->join('user_condicao uc', 'uc.condicao_id = c.ID')
                ->where("uc.user_id", $user_id);
        }

        $query = $this->db->order_by('c.DESCRICAO', 'asc')->get("tipo_condicao_parcela_orcamento c");
        return $query;
    }*/

    public function get_condicoes($unidade_id = null, $vendedor_id = null, $curso_id = null)
    {
        if($unidade_id == null || $curso_id == null)
            return false;

        if($vendedor_id == null)
            $vendedor_id = $this->session->userdata('user_id');

        $query = $this->db->select('c.*')
            ->join('user_condicao uc', 'uc.condicao_id = c.ID')
            //->join('curso_condicao cc', 'cc.LIG_ID_TIPO_CONDICAO = c.ID')
            ->join('curso_desconto cd', 'cd.TIPO_CONDICAO_PARCELA_ID = c.ID')
            ->join('unidades u', 'u.id = c.unidade_id')
            ->where("uc.user_id", $vendedor_id)
            ->where("c.unidade_id", $unidade_id)
            // ->where("cc.LIG_ID_CURSO", $curso_id)
            ->where("cd.CURSO_ID", $curso_id)
            ->order_by('u.unidade', 'asc')
            ->get("tipo_condicao_parcela_orcamento c");

        return $query;
    }

    public function get_turmas($unidade_id = null, $curso_id = null)
    {
        if($unidade_id == null || $curso_id == null)
            return false;

        $query = $this->db->select('t.*')
            ->where("t.unidade", $unidade_id)
            ->where("t.LIG_ID_CURSO", $curso_id)
            ->where("t.permitir_matricula", 'S')
            ->order_by('t.DESCRICAO', 'asc')
            ->get("turma t");

        return $query;
    }

    public function get_unidades_by_condicao($user_id = null)
    {
        if($user_id == null)
            return false;

        $query = $this->db->select('u.*, u.unidade')
            ->join('user_condicao uc', 'uc.condicao_id = c.ID')
            ->join('unidades u', 'u.id = c.unidade_id')
            ->where("uc.user_id", $user_id)
            ->order_by('u.unidade', 'asc')
            ->distinct()
            ->get("tipo_condicao_parcela_orcamento c");
        return $query;
    }

    public function get_cursos($unidade_id = null)
    {
        if($unidade_id == null)
            return false;

        $query = $this->db->select('c.*')
            ->join('curso_filial cl', 'cl.LIG_ID_CURSO = c.ID_CURSO')
            ->where("cl.LIG_ID_FILIAL", $unidade_id)
            ->where("c.empresa_id", $this->session->userdata('empresa_id'))
            ->order_by('c.DESCRICAO', 'asc')
            ->get("curso c");

        return $query;
    }

    public function get_curso($id = null)
    {
        if($id == null)
            return false;

        $query = $this->db->select('c.*')
            ->join('curso_filial cl', 'cl.LIG_ID_CURSO = c.ID_CURSO')
            ->where("c.ID_CURSO", $id)
            ->order_by('c.DESCRICAO', 'asc')
            ->get("curso c");

        return $query;
    }

    public function get_condicao($id)
    {
        if($id == null)
            return false;

        $query = $this->db->select('c.*, p.NUM_PARCELAS')
            ->join('cad_fpgt p', 'c.Id_formpag = p.CODIGO')
            ->where("c.ID", $id)
            ->order_by('c.DESCRICAO', 'asc')
            ->get("tipo_condicao_parcela_orcamento c");

        return $query;
    }

    public function get_valor_servicos($id)
    {
        if($id == null)
            return false;

        $query = $this->db->select('VALOR')
            ->where("LIG_ID_TIPO_CONDICAO", $id)
            ->get("tipo_condicao_parcela_servicos");

        $valor = 0;

        foreach ($query->result() as $key => $value) {
            $valor += $value->VALOR;
        }
        return $valor;
    }

    public function get_disciplinas($id)
    {
        if($id == null)
            return false;

        $query = $this->db->select('d.*')
            ->join('curso_disciplina cd', 'cd.LIG_ID_DISCIPLINA = d.ID_DISCIPLINA')
            ->where("cd.LIG_ID_CURSO", $id)
            ->order_by('d.DESCRICAO', 'asc')
            ->get("disciplina d");

        return $query;
    }

    public function get_condicao_by_orcamento($orc_id)
    {
        if($orc_id == null)
            return false;

        $query = $this->db->select('c.*, t.DESCRICAO')
            ->join('tipo_condicao_parcela_orcamento t', 'c.TIPO_CONDICAO_ID = t.ID')
            ->where("c.ORCAMENTO", $orc_id)
            ->order_by('c.CODIGO', 'asc')
            ->get("condicoes_orcamento c");

        return $query;
    }
}
