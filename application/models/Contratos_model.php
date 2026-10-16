<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contratos_model extends CI_Model {

    protected $table = 'cad_orca';

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
    public function get($data = array('select'=>'cad_orca.*', 'order_by'=>array('cad_orca.id', 'desc')))
    {
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$this->session->userdata('user_id')))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        $query = $this->db->select($data['select']);
        if(@$data['join']) {
            foreach ($data['join'] as $join) {
                $query->join($join[0], $join[1], $join[2]);
            }
        }
        $query->join('unidades', 'unidades.id = cad_orca.filial');
        if(@$data['where']) {
            $query->where($data['where']);
        }
        $query->where('unidades.id IN ('.implode(",", $unids_array).')');
        $query->where('unidades.empresa_id', $this->session->userdata('empresa_id'));
        //$query->order_by($data['order_by'][0], $data['order_by'][1]);
        return $query->get($this->table);
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

        $query = $this->db->select('u.*, p.LIG_STATUS_CONTRATO_01')
            ->join('parametro p', 'p.LIG_ID_FILIAL = u.id')
            ->where('u.id IN ('.implode(",", $unids_array).')')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->get("unidades u");

         return $query;
    }

    public function get_filiais($user_id = null)
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

    public function get_unidade($unidade_id = null)
    {
        $query = $this->db->select('u.*');

        if($unidade_id != null)
            $query->where('u.id', $unidade_id);

        return $query->get("unidades u");
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

    public function get_cursos($id = null)
    {
        $query = $this->db->select('*');

        if($id != null)
            $query->where('ID_CURSO', $id);
            
        return $query->get("curso");
    }

    public function get_condicoes($curso_id)
    {
        return $this->db->select('t.*, p.DESCRICAO as fpgt_desc, p.NUM_PARCELAS, r.NOME as rece_desc')
            ->join('curso_desconto c', 'c.TIPO_CONDICAO_PARCELA_ID = t.ID')
            ->join('cad_fpgt p', 'p.CODIGO = t.Id_formpag', 'left')
            ->join('tip_rece r', 'r.CODIGO = t.Id_TipoReceb', 'left')
            ->where('c.CURSO_ID', $curso_id)
            ->get("tipo_condicao_parcela_orcamento t");
    }

    public function get_turmas($curso_id)
    {
        return $this->db->select('*, DATE_FORMAT(DATA_INICIO, "%d/%m/%Y") as DATA_INICIO')
            ->where('LIG_ID_CURSO', $curso_id)
            ->get("turma");
    }

    public function get_disciplinas($curso_id)
    {
        return $this->db->select('d.*')
            ->join('curso_disciplina c', 'c.LIG_ID_CURSO = d.ID_DISCIPLINA')
            ->where('c.LIG_ID_CURSO', $curso_id)
            ->get("disciplina d");
    }

    public function get_produtos($curso_id)
    {
        return $this->db->select('p.CODIGO as prod_id, p.DESCRICAO, p.PESO, tp.VALOR')
            ->join('curso_desconto c', 'c.TIPO_CONDICAO_PARCELA_ID = t.ID')
            ->join('tipo_condicao_parcela_produtos tp', 'tp.LIG_ID_TIPO_CONDICAO = t.ID')
            ->join('cad_prod p', 'p.CODIGO = tp.LIG_ID_PRODUTO')
            ->where('c.CURSO_ID', $curso_id)
            ->get("tipo_condicao_parcela_orcamento t");
    }

    public function get_servicos($curso_id)
    {
        return $this->db->select('s.CODIGO as serv_id, s.APLICACAO, ts.VALOR')
            ->join('curso_desconto c', 'c.TIPO_CONDICAO_PARCELA_ID = t.ID')
            ->join('tipo_condicao_parcela_servicos ts', 'ts.LIG_ID_TIPO_CONDICAO = t.ID')
            ->join('cad_serv s', 's.CODIGO = ts.LIG_ID_SERVICO')
            ->where('c.CURSO_ID', $curso_id)
            ->get("tipo_condicao_parcela_orcamento t");
    }

    public function get_status_matricula($filial_id)
    {
        return $this->db->select('LIG_STATUS_CONTRATO_01')
            ->where('LIG_ID_FILIAL', $filial_id)
            ->get("parametro");
    }

    public function get_status_anulado($filial_id)
    {
        return $this->db->select('LIG_ID_ANULADO')
            ->where('LIG_ID_FILIAL', $filial_id)
            ->get("parametro");
    }

    public function get_status_orca()
    {
        return $this->db->select('id, descricao')
            ->where('empresa_id', $this->session->userdata('empresa_id'))
            ->where('mostrar_contrato', "True")
            ->get("cad_status");
    }

    public function get_parcelas($curso_id)
    {
        $condicoes = $this->db->select('t.*, p.DESCRICAO as fpgt_desc, p.NUM_PARCELAS, r.NOME as rece_desc, p.INTERVALO, p.DIAS_ENTRADA, t.CalcParc')
            ->join('curso_desconto c', 'c.TIPO_CONDICAO_PARCELA_ID = t.ID')
            ->join('cad_fpgt p', 'p.CODIGO = t.Id_formpag', 'left')
            ->join('tip_rece r', 'r.CODIGO = t.Id_TipoReceb', 'left')
            ->where('c.CURSO_ID', $curso_id)
            ->get("tipo_condicao_parcela_orcamento t");

        $res_condicoes = array();
        $parcelas = array();
        if($condicoes->num_rows() > 0) {
            $i = 0;
            $prod_total = 0;
            $serv_total = 0;
            $res_condicoes = $condicoes->result();
            foreach ($res_condicoes as $cond) {
                $produtos = $this->db->get_where('tipo_condicao_parcela_produtos', array('LIG_ID_TIPO_CONDICAO'=>$cond->ID));
                if($produtos->num_rows() > 0) {
                    foreach ($produtos->result() as $prod) {
                        $prod_total += $prod->VALOR;
                    }
                }
                $servicos = $this->db->get_where('tipo_condicao_parcela_servicos', array('LIG_ID_TIPO_CONDICAO'=>$cond->ID));
                if($servicos->num_rows() > 0) {
                    foreach ($servicos->result() as $serv) {
                        $serv_total += $serv->VALOR;
                    }
                }
                $res_condicoes[$i]->prod_total = $prod_total;
                $res_condicoes[$i]->serv_total = $serv_total;
                $i++;

                $p = 0;
                for($k = 1; $k <= $cond->NUM_PARCELAS; $k++) {
                    $parcelas[$p]["condicao"] = $cond->DESCRICAO;
                    $parcelas[$p]["parcela"] = $k.'/'.$cond->NUM_PARCELAS;
                    if($cond->CalcParc == 0)
                        $parcelas[$p]["valor"] = number_format($prod_total + $serv_total, 2, ",", ".");
                    else
                        $parcelas[$p]["valor"] = number_format(($prod_total + $serv_total) / $cond->NUM_PARCELAS, 2, ",", ".");
                    if($k == 1) {
                        $ultima_data = date('Y-m-d', strtotime("+ ".$cond->DIAS_ENTRADA." days"));
                    } else {
                        $ultima_data = date('Y-m-d', strtotime($ultima_data." + ".$cond->INTERVALO." days"));
                    }
                    $parcelas[$p]["vencimento"] = date('d/m/Y', strtotime($ultima_data));
                    $parcelas[$p]["recebimento"] = $cond->rece_desc;
                    $p++;
                }
            }
        }
        return $parcelas;
        //exit("<Pre>".print_r($parcelas,true)."</pre>");
    }

    public function get_parcelas_contrato($contrato_id)
    {
        //$parcelas = $this->db->select('p.*, r.NOME as tipo_recebimento_desc, cr.VALOR as valor_receb, cr.VALOR_PAGO, cr.DATA_PGTO, i.status, i.tipo, i.iugu_id')
        $parcelas = $this->db->select('p.*, r.NOME as tipo_recebimento_desc, cr.VALOR as valor_receb, cr.VALOR_PAGO, cr.DATA_PGTO, cr.CODIGO')
            ->join('tip_rece r', 'r.CODIGO = p.tipo_recebimento')
            ->join('ct_receb cr', 'cr.PARCELA_ID = p.id')
          //  ->join('iugu_invoices i', 'i.ct_receb_id = cr.CODIGO', 'left')
            ->where('p.orc_id', $contrato_id)
            ->distinct()
            ->get("par_orca p");

        $arr = array();
        foreach ($parcelas->result() as $value) {
            $iugu_invoice = $this->db->select('CASE WHEN status IN ("pending") THEN "Pendente" WHEN status IN ("expired") THEN "Expirada" WHEN status IN ("paid") THEN "Paga" WHEN status IN ("canceled") THEN "Cancelada" WHEN status IN ("authorized") THEN "Autorizada" WHEN status IN ("partially_paid") THEN "Paga parcial" WHEN status IN ("in_protest") THEN "Em protesto" ELSE "-" END AS status_desc, status, tipo, iugu_id')->where('cad_orca_id', $contrato_id)->where('ct_receb_id', $value->CODIGO)->order_by('id', 'desc')->get('iugu_invoices');
            if($iugu_invoice->num_rows()) {
                $value->status = $iugu_invoice->row()->status;
                $value->status_desc = $iugu_invoice->row()->status_desc;
                $value->tipo = $iugu_invoice->row()->tipo;
                $value->iugu_id = $iugu_invoice->row()->iugu_id;
            } else {
                $value->status = "";
                $value->status_desc = "-";
                $value->tipo = "";
                $value->iugu_id = "";
            }
            $arr[] = $value;
        }

        return $arr;
        //exit("<Pre>".print_r($parcelas,true)."</pre>");
    }

    public function get_parcela($id = null)
    {
        if($id == null)
            return false;
        
        $parcela = $this->db->select('p.*, r.NOME as tipo_recebimento_desc, cr.VALOR as valor_receb, cr.VALOR_PAGO, cr.DATA_PGTO, i.status, i.tipo, i.ct_receb_id')
            ->join('tip_rece r', 'r.CODIGO = p.tipo_recebimento')
            ->join('ct_receb cr', 'cr.PARCELA_ID = p.id')
            ->join('iugu_invoices i', 'i.ct_receb_id = cr.CODIGO')
            ->where('p.id', $id)
            ->limit(1)
            ->get("par_orca p");

        return $parcela;
        //exit("<Pre>".print_r($parcelas,true)."</pre>");
    }

    public function get_parametro($filial) {
        $return = $this->db->get_where('parametro', array("LIG_ID_FILIAL" => $filial))->row();
        return $return;
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

    public function create_condicao_orcamento($data = array())
    {
        if($this->db->insert("condicoes_orcamento", $data)) {
            $id = $this->db->insert_id();
        } else {
            return FALSE;
        }

        return $id;
    }

    public function create_produto_orcamento($data = array())
    {
        if($this->db->insert("pro_orca", $data)) {
            $id = $this->db->insert_id();
        } else {
            return FALSE;
        }

        return $id;
    }

    public function create_servico_orcamento($data = array())
    {
        if($this->db->insert("ser_orca", $data)) {
            $id = $this->db->insert_id();
        } else {
            return FALSE;
        }

        return $id;
    }

    public function create_turma_aluno($data = array())
    {
        if($this->db->insert("turma_aluno", $data)) {
            $id = $this->db->insert_id();
        } else {
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

    public function activate($id = NULL)
    {
        if (!isset($id))
        {
            $this->set_error('activate_unsuccessful');
            return FALSE;
        }

        $data = array(
            'status' => 'S'
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
            'status' => 'N'
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

    public function has_iugu_entrada($orc_id) {
        $sel = $this->db->get_where('iugu_invoices', array("tipo" => 'M', 'cad_orca_id' => $orc_id));
        return $sel;
    }
	
	public function get_iugu_subscriptions($orc_id) {
        $sel = $this->db->get_where('iugu_subscriptions', array('cad_orca_id' => $orc_id));
        return $sel;
    }

}
