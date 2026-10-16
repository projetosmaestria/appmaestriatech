<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Iugu_model extends CI_Model {

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
    public function get_contratos($data = array('select'=>'cad_orca.*', 'order_by'=>array('cad_orca.id', 'desc')))
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
        $query->order_by($data['order_by'][0], $data['order_by'][1]);
        return $query->get('cad_orca');
    }

    public function get_unidade($unidade_id = null)
    {
        $query = $this->db->select('u.*');

        if($unidade_id != null)
            $query->where('u.id', $unidade_id);

        return $query->get("unidades u");
    }

    public function get_parametros($unidade_id = null)
    {
        if($unidade_id == null)
            return false;

        $query = $this->db->select('p.*');
        $query->where('p.LIG_ID_FILIAL', $unidade_id);

        return $query->get("parametro p");
    }

    public function get_status($id = null)
    {
        if($id == null)
            return false;

        $query = $this->db->select('s.*');
        $query->where('s.id', $id);

        return $query->get("cad_status s");
    }

    public function get_iugu_invoice($orc_id, $tipo) {
        $query = $this->db->select('*');
        $query->where('tipo', $tipo);
        $query->where('cad_orca_id', $orc_id);
        $query->order_by('id', 'desc');
        return $query->get("iugu_invoices")->row();
    }

    public function get_entrada_valor($orc_id) {
        $entrada = $this->db->select('p.*')
            ->join('servico_condicoes_orcamento s', 's.CODIGO = p.SERVICO_CONDICOES_ORCA')
            ->join('condicoes_orcamento c', 'c.CODIGO = s.CONDICOES_ORCAMENTO')
            ->join('cad_orca o', 'o.id = c.ORCAMENTO')
            ->join('tipo_condicao_parcela_orcamento t', 'c.TIPO_CONDICAO_ID = t.ID')
            ->where('o.id', $orc_id)
            ->where('t.entrada_matricula', 'True')
            ->distinct()
            ->get("parcela_servico_condicoes_orca p");

        return $entrada;
    }

    public function create_invoice($data = array())
    {
        $this->db->insert("iugu_invoices", $data);
        return TRUE;
    }

    public function update_invoice($id = FALSE, $data = array(), $where = array())
    {
        if (empty($id)) return FALSE;

        $this->db->update('iugu_invoices', $data, $where);

        return TRUE;
    }

    public function get_responsavel($user_id = null)
    {
        if($user_id == null)
            return false;

        return $this->db->select('u.*, p.*')
            ->join('pessoas p', 'u.id = p.user_id')
            ->where('p.dependente', $user_id)
            ->get("users u");
    }

    public function get_contrato($id = NULL)
    {
        if($id == NULL)
            return false;

        return $this->db->select('c.*')
            ->where('c.id', $id)
            ->where('c.tipo_saxweb', 2)
            ->get('cad_orca c');
    }

    // public function get_contrato_by_parcela($id = NULL)
    // {
    //     if($id == NULL)
    //         return false;

    //     return $this->db->select('c.id')
    //         ->join('par_orca p', 'p.orc_id = c.id')
    //         ->where('p.id', $id)
    //         ->distinct()
    //         ->get('cad_orca c');
    // }

    public function get_parcela($id = NULL)
    {
        if($id == NULL)
            return false;

        return $this->db->select('p.*, i.ct_receb_id, i.iugu_id')
            ->join('ct_receb ct', 'ct.PARCELA_ID = p.id')
            ->join('iugu_invoices i', 'i.ct_receb_id = ct.CODIGO')
            ->where('p.id', $id)
            ->get('par_orca p');
    }

    public function update_cartao_recorrente($iugu_customer_id, $iugu_id) {
        $this->db->update("iugu_users_cc", array("iugu_id" => $iugu_id), array('iugu_customer_id' => $iugu_customer_id));

        $return = $this->db->affected_rows() == 1;
        if ($return){
            // $this->set_message('update_successful');
        } else{
            $this->db->insert("iugu_users_cc", array("iugu_id" => $iugu_id, 'iugu_customer_id' => $iugu_customer_id));
        }
        $this->set_message('update_successful');
        return $return;
    }

    public function get_iugu_user($user_id) {
        $return = $this->db->get_where('iugu_users', array("user_id" => $user_id))->row();
        return $return;
    }

    public function get_iugu_user_cc($customer_id) {
        $user = $this->db->select('*')
            ->where("iugu_customer_id", $customer_id)
            ->order_by('id', 'desc')
            ->limit(1)
            ->get("iugu_users_cc");
        return $user;
    }

    public function has_iugu_user_cc($customer_id) {
        $return = $this->db->get_where('iugu_users_cc', array("iugu_customer_id" => $customer_id));
        if($return->num_rows() > 0)
            return true;
        else
            return false;
    }

    public function insert_iugu_user_cc_pendent($data) {
        $return = $this->db->get_where('iugu_users_cc_pendent', $data);
        if($return->num_rows() == 0)
            $this->db->insert("iugu_users_cc_pendent", $data);
        return true;
    }

    public function remove_iugu_user_cc_pendent($data) {
        if($this->db->delete("iugu_users_cc_pendent", $data)) {
            return true;
        }
        return false;
    }

    public function get_iugu_users_pendent() {
        $unidades = $this->db->get_where('user_unidade', array('user_id'=>$this->session->userdata('user_id')))->result();
        $unids_array = array();
        foreach ($unidades as $unid) {
            $unids_array[] = $unid->unidade_id;
        }

        /*$users = $this->db->select('up.*, u.id as user_id, s.cad_orca_id')
            ->join("iugu_subscriptions s", "s.iugu_id = up.iugu_subscription_id")
            ->join("cad_orca c", "c.id = s.cad_orca_id")
            ->join("users u", "u.id = c.user_id")
            ->where('c.filial IN ('.implode(",", $unids_array).')')
            ->get("iugu_users_cc_pendent up");*/
        $users = $this->db->select('up.*, up.cad_orca_id, u.user_id')
            ->join("iugu_users u", "u.iugu_id = up.iugu_customer_id")
            ->join("cad_orca o", "up.cad_orca_id = o.id")
            ->join("unidades un", "un.id = o.filial")
            ->where('un.empresa_id', $this->session->userdata('empresa_id'))
            ->where('o.filial IN ('.implode(",", $unids_array).')')
            ->get("iugu_users_cc_pendent up");
        return $users;
    }

    public function get_contrato_by_iugu_assinatura($iugu_id) {
        $return = $this->db->select('is.*')
            ->join("iugu_invoices i", "i.cad_orca_id = is.cad_orca_id")
            ->where('i.iugu_id', $iugu_id)
            ->distinct()
            ->get('iugu_subscriptions is');
        return $return;
    }

    public function get_iugu_assinatura($contrato_id) {
        $return = $this->db->get_where('iugu_subscriptions', array("cad_orca_id" => $contrato_id));
        return $return;
    }

    public function has_unidade($id = NULL)
    {
        if($id == NULL)
            return false;

        $query = $this->db->select('*')
            ->where('user_id', $this->session->userdata('user_id'))
            ->where('unidade_id', $id)
            ->get('user_unidade');

        if($query->num_rows() > 0)
            return true;
        else
            return false;
    }

    public function get_planos_habilitados($contrato_id = null) {
        if($contrato_id == null)
            return false;
        
        $parcela_atual = 0;

        $parcela_atual = $this->db->select('c.NUM_PARCELAS')
            ->join("tipo_condicao_parcela_orcamento t", "t.ID = c.TIPO_CONDICAO_ID")
            ->where("t.entrada_matricula", "False")
            ->where("c.ORCAMENTO", $contrato_id)
            ->get("condicoes_orcamento c")->row()->NUM_PARCELAS;

        $parcelas_habilitadas = $this->db->select('t.plano_iugu, t.DESCRICAO, t.ID, p.NUM_PARCELAS')
            ->join("cad_fpgt p", "p.CODIGO = t.Id_formpag")
            ->where("t.entrada_matricula", "False")
            ->where("t.plano_iugu IS NOT NULL")
            ->where("p.NUM_PARCELAS >= ".$parcela_atual)
            ->order_by('p.NUM_PARCELAS', 'asc')
            ->order_by('t.DESCRICAO', 'asc')
            ->get("tipo_condicao_parcela_orcamento t");

        return $parcelas_habilitadas;
    }

    public function get_condicao_atual($contrato_id = null) {
        if($contrato_id == null)
            return false;
        
        return $this->db->select('t.DESCRICAO, c.TIPO_CONDICAO_ID')
            ->join("tipo_condicao_parcela_orcamento t", "t on t.ID = c.TIPO_CONDICAO_ID")
            ->where("t.entrada_matricula", "False")
            ->where("c.ORCAMENTO", $contrato_id)
            ->get("condicoes_orcamento c");
    }

    public function get_condicao($id = null) {
        if($id == null)
            return false;
        
        return $this->db->select('t.plano_iugu, t.DESCRICAO, t.ID, t.VALOR_DESCONTO_PONTUALIDADE, p.NUM_PARCELAS')
            ->join("cad_fpgt p", "p.CODIGO = t.Id_formpag")
            ->where("t.plano_iugu IS NOT NULL")
            ->where("t.ID", $id)
            ->get("tipo_condicao_parcela_orcamento t");
    }

    private function vencimentos($data, $numero, $convert = false)
    {
        if($convert == true) {
            $data = str_replace('/', '-', $data);
            $data = date('Y-m-d', strtotime($data));
        }

        $parc = array();
        $parc[] = $data;
        list($ano, $mes, $dia) = explode("-", $data);
        for($i = 1; $i < $numero;$i++)
        {
            $mes++;
            if ((int)$mes == 13)
            {
                $ano++;
                $mes = 1;
            }
            $tira = $dia;
            while (!checkdate($mes, $tira, $ano))
            {
                $tira--;
            }
            $parc[] = sprintf("%s-%02d-%02d", $ano, $mes, $tira);
        }
        return $parc;
    }

    public function update_condicao($condicao_id = null, $contrato_id = null, $retorno, $post) {
        if($condicao_id == null || $contrato_id == null)
            return false;

        $condicao_atual = $this->db->select('c.CODIGO, c.TIPO_CONDICAO_ID')
            ->join("tipo_condicao_parcela_orcamento t", "t.ID = c.TIPO_CONDICAO_ID")
            ->where("t.entrada_matricula", "False")
            ->where("c.ORCAMENTO", $contrato_id)
            ->get("condicoes_orcamento c");

        $servico_condicao_atual = $this->db->select('c.CODIGO')
            ->where("c.CONDICOES_ORCAMENTO", $condicao_atual->row()->CODIGO)
            ->get("servico_condicoes_orcamento c");

        $tipo_condicao_atual = $this->db->select('t.ID, t.VALOR_DESCONTO_PONTUALIDADE, t.Id_TipoReceb, t.CARENCIA_DESCONTO_PONTUALIDADE, t.TIPOVALORDESCONTOPONTUALIDADE, t.Id_planofin, p.NUM_PARCELAS')
            ->join("cad_fpgt p", "p.CODIGO = t.Id_formpag")
            ->where("t.ID", $condicao_atual->row()->TIPO_CONDICAO_ID)
            ->get("tipo_condicao_parcela_orcamento t");

        $tipo_condicao = $this->db->select('t.ID, t.VALOR_DESCONTO_PONTUALIDADE, t.Id_TipoReceb, t.CARENCIA_DESCONTO_PONTUALIDADE, t.TIPOVALORDESCONTOPONTUALIDADE, t.Id_planofin, p.NUM_PARCELAS')
            ->join("cad_fpgt p", "p.CODIGO = t.Id_formpag")
            ->where("t.ID", $condicao_id)
            ->get("tipo_condicao_parcela_orcamento t");

        $desconto = 0;
        $i = 0;
        foreach ($post["quantity"] as $key => $value) {
            if(@$post["price"][$key] && @$post["quantity"][$key]) {
                if(str_replace("R$ ", "", $post["price"][$key]) != "" && str_replace("R$ ", "", $post["price"][$key]) != 0) {
                    $desconto += str_replace(",", ".", str_replace("R$ ", "", $post["price"][$key]));
                }
                /*if(@$post["condicao_desconto"])
                    $subitems[$i]["price_cents"] = -str_replace(".", "", $post["condicao_desconto"]);
                else
                    $subitems[$i]["price_cents"] = str_replace(".", "", str_replace(",", "", str_replace("R$ ", "", $post["price"][$key])));*/
                $i++;
            }
        }

        if($i == 0)
            $desconto = $tipo_condicao->row()->VALOR_DESCONTO_PONTUALIDADE;

        if($this->db->update("condicoes_orcamento", array(
            "NUM_PARCELAS" => $tipo_condicao->row()->NUM_PARCELAS, 
            "PLANO_FINANCEIRO_ID" => $tipo_condicao->row()->Id_planofin,
            "TIPO_RECEBIMENTO" => $tipo_condicao->row()->Id_TipoReceb,
            "TIPOVALORDESCONTOPONTUALIDADE" => $tipo_condicao->row()->TIPOVALORDESCONTOPONTUALIDADE,
            "VALORDESCONTOPONTUALIDADE" => abs($desconto),
            "CARENCIADESCONTOPONTUALIDADE" => $tipo_condicao->row()->CARENCIA_DESCONTO_PONTUALIDADE,
            "TIPO_CONDICAO_ID" => $tipo_condicao->row()->ID,
            "SINCRONIZADO" => 0
        ), array('ORCAMENTO' => $contrato_id, "TIPO_CONDICAO_ID" => $condicao_atual->row()->TIPO_CONDICAO_ID))) {
            $this->update_par_orca($condicao_id, $contrato_id, $retorno, $condicao_atual, $servico_condicao_atual, $tipo_condicao_atual, $tipo_condicao, $post);
        }
        return false;
    }

    public function update_par_orca($condicao_id = null, $contrato_id = null, $retorno, $condicao_atual, $servico_condicao_atual, $tipo_condicao_atual, $tipo_condicao, $post) {
        $parcelas = $this->db->select('p.*, ct.DATA_PGTO')
            ->join("ct_receb ct", "p.id = PARCELA_ID")
            ->where("p.tipo_recebimento", $tipo_condicao_atual->row()->Id_TipoReceb)
            ->where("p.orc_id", $contrato_id)
            ->order_by("p.parcela", "asc")
            ->get("par_orca p");

        $p = 0;
        foreach($parcelas->result() as $parc) {
            //if($parc->vencimento > date("Y-m-d")) {
                $p++;
            //}   

        }

        $cad_orca = $this->db->get_where('cad_orca', array("id" => $contrato_id))->row();
        $ct_receb_exist = $this->db->select('c.*')
            ->like("c.DOCUMENTO", $contrato_id."/")
            ->where("c.FILIAL", $cad_orca->filial)
            ->where("c.CLIENTE", $cad_orca->user_id)
            ->where("c.TIPO_CONDICAO_PARCELA_ID", $tipo_condicao_atual->row()->ID)
            ->get("ct_receb c");

        $vencimentos = $this->vencimentos($post["expires_at"], $p, true);
        $venc_count = 0;
        foreach($parcelas->result() as $parc) {
            // if($parc->DATA_PGTO == "0000-00-00" && strtotime($parc->vencimento) > strtotime(date("Y-m-d"))) {
            if($parc->DATA_PGTO == "0000-00-00") {
                $data_par_orca = array(
                    "vencimento" => $vencimentos[$venc_count],
                    "valor" => number_format($retorno["price_cents"] / 100, 2, ',', '.'),
                    "valor_desconto_pontualidade" => $tipo_condicao->row()->VALOR_DESCONTO_PONTUALIDADE,
                    "carencia_desconto_pontualidade" => $tipo_condicao->row()->CARENCIA_DESCONTO_PONTUALIDADE,
                    "tipo_valor_desconto_pontualidade" => $tipo_condicao->row()->TIPOVALORDESCONTOPONTUALIDADE,
                    "tipo_recebimento" => $tipo_condicao->row()->Id_TipoReceb
                );
                $data_ser_cond = array(
                    "VENCIMENTO" => $vencimentos[$venc_count],
                    "VALOR" => number_format($retorno["price_cents"] / 100, 2, ',', '.'),
                    "VALORDESCONTOPONTUALIDADE" => $tipo_condicao->row()->VALOR_DESCONTO_PONTUALIDADE,
                    "CARENCIADESCONTOPONTUALIDADE" => $tipo_condicao->row()->CARENCIA_DESCONTO_PONTUALIDADE,
                    "TIPOVALORDESCONTOPONTUALIDADE" => $tipo_condicao->row()->TIPOVALORDESCONTOPONTUALIDADE,
                    "PLANO_FINANCEIRO_ID" => $tipo_condicao->row()->Id_planofin,
                    "TIPO_RECEBIMENTO" => $tipo_condicao->row()->Id_TipoReceb,
                    "SINCRONIZADO" => 0
                );
                $data_ct_receb = array(
                    "VENCIMENTO" => $vencimentos[$venc_count],
                    "DOCUMENTO" => "XA".str_pad($contrato_id, 8, '0', STR_PAD_LEFT)."/".str_pad($parc->parcela, 2, '0', STR_PAD_LEFT)."-".str_pad($tipo_condicao->row()->NUM_PARCELAS, 2, '0', STR_PAD_LEFT),
                    "VALOR" => number_format($retorno["price_cents"] / 100, 2, ',', '.'),
                    "VALORDESCONTOPONTUALIDADE" => $tipo_condicao->row()->VALOR_DESCONTO_PONTUALIDADE,
                    "CARENCIADESCONTOPONTUALIDADE" => $tipo_condicao->row()->CARENCIA_DESCONTO_PONTUALIDADE,
                    "TIPO_RECEBIMENTO" => $tipo_condicao->row()->Id_TipoReceb,
                    "TIPO_CONDICAO_PARCELA_ID" => $tipo_condicao->row()->ID,
                    "DATA_ATUALIZADA" => date("Y-m-d"),
                    "CENTRO_CUSTO" => $tipo_condicao->row()->Id_planofin,
                    "SINCRONIZADO" => 0
                );
                $this->db->update("par_orca", $data_par_orca, array('orc_id' => $contrato_id, "tipo_recebimento" => $tipo_condicao_atual->row()->Id_TipoReceb, "parcela" => $parc->parcela));
                $this->db->update("parcela_servico_condicoes_orca", $data_ser_cond, array('SERVICO_CONDICOES_ORCA' => $servico_condicao_atual->row()->CODIGO, "PARCELA" => $parc->parcela, "CODIGO_PARCELA_GERADA" => $parc->id));
                $this->db->update("ct_receb", $data_ct_receb, array("PARCELA" => $parc->parcela, "PARCELA_ID" => $parc->id));

                $venc_count++;
            }
        }

        if($tipo_condicao_atual->row()->NUM_PARCELAS < $tipo_condicao->row()->NUM_PARCELAS) {
            $vencimentos = $this->vencimentos(end($vencimentos), ($tipo_condicao->row()->NUM_PARCELAS - $tipo_condicao_atual->row()->NUM_PARCELAS) + 1);
            $v = 1;

            //verificar existe ct_receb
            $can_create_ct_receb = false;

            if($ct_receb_exist->num_rows() == $tipo_condicao_atual->row()->NUM_PARCELAS)
                $can_create_ct_receb = true;

            for($i = $tipo_condicao_atual->row()->NUM_PARCELAS + 1; $i <= $tipo_condicao->row()->NUM_PARCELAS; $i++) {
                $data_par_orca = array(
                    'orc_id' => $contrato_id,
                    "tipo_recebimento" => $tipo_condicao->row()->Id_TipoReceb,
                    "parcela" => $i,
                    "vencimento" => $vencimentos[$v],
                    "valor" => number_format($retorno["price_cents"] / 100, 2, ',', '.'),
                    "valor_desconto_pontualidade" => $tipo_condicao->row()->VALOR_DESCONTO_PONTUALIDADE,
                    "carencia_desconto_pontualidade" => $tipo_condicao->row()->CARENCIA_DESCONTO_PONTUALIDADE,
                    "tipo_valor_desconto_pontualidade" => $tipo_condicao->row()->TIPOVALORDESCONTOPONTUALIDADE
                );
                $this->db->insert("par_orca", $data_par_orca);
                $parc_id = $this->db->insert_id();

                $data_ser_cond = array(
                    "SERVICO_CONDICOES_ORCA" => $servico_condicao_atual->row()->CODIGO,
                    "CODIGO_PARCELA_GERADA" => $parc_id,
                    "PARCELA" => $i,
                    "VALOR" => number_format($retorno["price_cents"] / 100, 2, ',', '.'),
                    "VALORDESCONTOPONTUALIDADE" => $tipo_condicao->row()->VALOR_DESCONTO_PONTUALIDADE,
                    "CARENCIADESCONTOPONTUALIDADE" => $tipo_condicao->row()->CARENCIA_DESCONTO_PONTUALIDADE,
                    "TIPOVALORDESCONTOPONTUALIDADE" => $tipo_condicao->row()->TIPOVALORDESCONTOPONTUALIDADE,
                    "PLANO_FINANCEIRO_ID" => $tipo_condicao->row()->Id_planofin,
                    "VENCIMENTO" => $vencimentos[$v],
                    "TIPO_RECEBIMENTO" => $tipo_condicao->row()->Id_TipoReceb,
                    "SINCRONIZADO" => 0
                );
                $this->db->insert("parcela_servico_condicoes_orca", $data_ser_cond);

                //if($can_create_ct_receb == true) {
                    $data_ct_receb = array(
                        "FILIAL" => $cad_orca->filial,
                        "CLIENTE" => $cad_orca->user_id,
                        "DOCUMENTO" => "XA".str_pad($contrato_id, 8, '0', STR_PAD_LEFT)."/".str_pad($i, 2, '0', STR_PAD_LEFT)."-".str_pad($tipo_condicao->row()->NUM_PARCELAS, 2, '0', STR_PAD_LEFT),
                        "PARCELA" => $i,
                        "PARCELA_ID" => $parc_id,
                        "VALOR" => number_format($retorno["price_cents"] / 100, 2, ',', '.'),
                        "VALORDESCONTOPONTUALIDADE" => $tipo_condicao->row()->VALOR_DESCONTO_PONTUALIDADE,
                        "CARENCIADESCONTOPONTUALIDADE" => $tipo_condicao->row()->CARENCIA_DESCONTO_PONTUALIDADE,
                        "VENCIMENTO" => $vencimentos[$v],
                        "COMPETENCIA" => date("m/Y", strtotime($vencimentos[$v])),
                        "TIPO_RECEBIMENTO" => $tipo_condicao->row()->Id_TipoReceb,
                        "TIPO_CONDICAO_PARCELA_ID" => $tipo_condicao->row()->ID,
                        "CENTRO_CUSTO" => $tipo_condicao->row()->Id_planofin,
                        "VENDEDOR" => $cad_orca->vendedor,
                        "SINCRONIZADO" => 0
                    );
                    $this->db->insert("ct_receb", $data_ct_receb);
                //}

                $v++;
            }
        }

        return true;
    }

    public function update_orcamento($id = FALSE, $data = array())
    {
        if (empty($id)) return FALSE;

        $this->db->update('cad_orca', $data, array('id' => $id));

        return TRUE;
    }

    public function update_subscriptions($id = FALSE, $data = array())
    {
        if (empty($id)) return FALSE;

        $this->db->update('iugu_subscriptions', $data, array('iugu_id' => $id));

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
}
