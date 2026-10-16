<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Habilitacoes_model extends CI_Model {

    protected $table = 'habilitacoes';

    protected $messages;

    protected $errors;

    public function __construct()
    {
        parent::__construct();
        $this->messages    = array();
        $this->errors      = array();
    }

    /**
     * get_clientes
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_clientes()
    {
        $query = $this->db->select('u.*, e.nome')
            ->join('empresas e', 'e.id = u.empresa_id')
            ->where('u.empresa_id', $this->session->userdata("empresa_id"))
            ->where('u.actived', 's');
        return $query->get('unidades u');
    }

    public function get_historico($cliente)
    {
        $query = $this->db->select('hp.id, hp.id_produto, hp.id_cliente, hp.periodo_inicio, hp.periodo_fim, hp.data_cadastro, hp.status, hp.demonstracao, p.nome')
            ->join('produtos p', 'p.id = hp.id_produto')
            ->where('hp.id_cliente', $cliente)
            ->order_by('hp.periodo_fim', 'asc');
        return $query->get('habilitacoes_produto hp');
    }

    public function verificarHistoricos($id_habilitacao) {
        $query = $this->db->select('id')
            ->where('id_habilitacao', $id_habilitacao);
        $res = $query->get('historico_habilitacao');

        if($res->num_rows() > 0)
            return true;
        else
            return false;
    }

    public function resgatarHistorico($hist_id) {
        $query = $this->db->select("h.id, h.id_habilitacao, h.observacao, DATE_FORMAT(h.data_cadastro, '%d/%m/%Y %H:%i') as data_cadastro, h.cadastrado_por, h.acao, CONCAT(u.first_name, ' ',u.last_name) as nome")
            ->join('users u', 'u.id = h.cadastrado_por', 'left')
            ->where('h.id_habilitacao', $hist_id)
            ->order_by('h.data_cadastro', 'desc');
        return $query->get('historico_habilitacao h');
    }

    public function getProdutosHabilitacao($id_cliente) {
        $arr_prdts['produtos_disponiveis'] = array();
        $arr_prdts['produtos_habilitados'] = array();

        //Produtos Disponíveis
        $qry_res_dspnvs = $this->db->select('p.id, p.nome, p.aplicacao, hp.status')
            ->join('habilitacoes_produto hp', 'hp.id_produto = p.id AND hp.id_cliente = ' . (int)$id_cliente, 'left')
            ->where('p.status', 'S')
            ->where('p.empresa_id', $this->session->userdata("empresa_id"))
            ->order_by('p.nome')
            ->get('produtos p');

        if($qry_res_dspnvs->num_rows() > 0) {
            foreach ($qry_res_dspnvs->result_array() as $value) {
                array_push($arr_prdts['produtos_disponiveis'], $value);
            }
        } else {
            $arr_prdts['produtos_disponiveis'] = false;
        }

        //Produtos Habilitados
        $qry_res_hbltds = $this->db->select('p.id, p.nome, p.aplicacao, hp.id as id_habilitacao, hp.periodo_inicio, hp.periodo_fim, hp.status, hp.demonstracao, hp.contrato_sax')
            ->join('habilitacoes_produto hp', 'hp.id_produto = p.id')
            ->where('hp.id_cliente', $id_cliente)
            ->order_by('hp.periodo_fim ASC, p.nome')
            ->get('produtos p');

        if($qry_res_hbltds->num_rows() > 0) {
            foreach ($qry_res_hbltds->result_array() as $value) {
                array_push($arr_prdts['produtos_habilitados'], $value);
            }
        } else {
            $arr_prdts['produtos_habilitados'] = false;
        }

        return $arr_prdts;
    }

    public function resgatarUltimaVersaoProduto($id_produto) {
        $qry = $this->db->select('p.versao')
            ->where('p.id_produto', $id_produto)
            ->where('p.status', 'S')
            ->order_by('p.id DESC')
            ->limit('1')
            ->get('_produtos_changelog p');

        if($qry->num_rows() > 0) {
            $data = $qry->row_array();
        } else {
            $data['versao'] = '';
        }
        return $data['versao'];
    }

    public function getModulosProduto($id_produto) {
        $arr_mdls = array();

        $qry = $this->db->select('m.id, m.nome, m.valor_licenca, m.valor_manutencao, m.valor_implantacao, m.status')
            ->where('m.id_produto', $id_produto)
            ->where('m.status', 'S')
            ->order_by('m.nome')
            ->get('modulos m');

        if($qry->num_rows() > 0) {
            foreach ($qry->result_array() as $value) {
                array_push($arr_mdls, $value);
            }
        } else {
            $arr_mdls = false;
        }
        return $arr_mdls;
    }

    function resgatarDiasVencimento($inicio, $fim, $desabilitado) {
        if($desabilitado == 'N'):
            $linha_vencimento = 'linha-desabilitado';
        else:
            $from = date_create($inicio);
            $to = date_create($fim);
            $diff = date_diff($to,$from);

            $dias = $diff->days;
            if($dias <= 15):
                $linha_vencimento = 'linha-vencimento-15';
            elseif($dias <= 30 && $dias > 15):
                $linha_vencimento = 'linha-vencimento-30';
            elseif($dias <= 60 && $dias > 30):
                $linha_vencimento = 'linha-vencimento-60';
            else:
                $linha_vencimento = '';
            endif;
        endif;
        return $linha_vencimento;
    }

    public function getModulosHabilitados($id_produto, $id_habilitacao) {
        $arr_mdls = array(); 

        $qry = $this->db->select('m.id, m.nome, m.valor_licenca, m.valor_manutencao, m.valor_implantacao, hm.id as id_hbltd_mdl, hm.usuarios, hm.status')
            ->join('habilitacoes_modulo hm', 'm.id = hm.id_modulo AND hm.id_habilitacao = '.$id_habilitacao, 'left')
            ->where('m.id_produto', $id_produto)
            ->where('m.status', 'S')
            ->order_by('m.nome')
            ->get('modulos m');

        if($qry->num_rows() > 0) {
            foreach ($qry->result_array() as $value) {
                array_push($arr_mdls, $value);
            }
        } else {
            $arr_mdls = false;
        }

        return $arr_mdls;
    }

    public function inserirHabilitacaoProduto($data){
        if($this->db->insert('habilitacoes_produto', $data)) {
            return $this->db->insert_id();
        } else {
            return false;
        }
    }

    public function registrarModulos($modulos){
        foreach($modulos as $modulo):
            if(@isset($modulo["id"]) && @$modulo["id"] > 0):
                $modulo["data_atualizacao"] = date("Y-m-d H:i:s");
                $modulo["atualizado_por"] = $this->session->userdata('user_id');
                $retorno = $this->atualizarHabilitacaoModulo($modulo);
            else:
                $modulo["data_cadastro"] = date("Y-m-d H:i:s");
                $modulo["cadastrado_por"] = $this->session->userdata('user_id');
                $retorno = $this->inserirHabilitacaoModulo($modulo);
            endif;
        endforeach;
        return $retorno;
    }

    public function inserirHabilitacaoModulo($modulo) {
        $retorno = array();

        if($this->db->insert('habilitacoes_modulo', $modulo)):
            $id = $this->db->insert_id();

            $this->db->insert("auditorias", array("controlador"=>"habilitacoes","metodo"=>"cadastrar_habilitacao","obs"=>'id='.$id.',id_habilitacao='.$modulo['id_habilitacao'].',id_modulo='.$modulo['id_modulo'].',cadastrado_por='.$modulo['cadastrado_por'].',usuarios='.$modulo['usuarios'].',status='.$modulo['status'],"empresa_id"=>$this->session->userdata("empresa_id"),"user_id"=>$this->session->userdata("user_id")));

            $retorno["erro"] = false;
            $retorno["msg"] = "Produto e módulo(s) habilitado(s) para o cliente com sucesso!";
        else: 
            $retorno["erro"] = true;
            $retorno["msg"] = "Ocorreu um erro ao habilitar o produto para o cliente, entre em contato com o administrador do sistema!";
        endif;

        return $retorno;
    }

    public function atualizarHabilitacaoProduto($habilitacao) {
        if($this->db->update('habilitacoes_produto', $habilitacao, array('id' => $habilitacao['id']))):
            return $habilitacao['id'];
        else:
            return false;
        endif;
    }

    public function atualizarHabilitacaoModulo($modulo) {
        $retorno = array();
        
        if($this->db->update('habilitacoes_modulo', $modulo, array('id' => $modulo['id']))):
            $this->db->insert("auditorias", array("controlador"=>"habilitacoes","metodo"=>"atualizar_habilitacao","obs"=>'id='.$modulo['id'].',id_modulo='.$modulo['id_modulo'].',atualizado_por='.$modulo['atualizado_por'].',usuarios='.$modulo['usuarios'].',status='.$modulo['status'],"empresa_id"=>$this->session->userdata("empresa_id"),"user_id"=>$this->session->userdata("user_id")));

            $retorno["erro"] = false;
            $retorno["msg"] = "Produto e módulo(s) atualizado(s) para o cliente com sucesso!";
        else:
            $retorno["erro"] = true;
            $retorno["msg"] = "Ocorreu um erro ao atualizar o produto para o cliente, entre em contato com o administrador do sistema!";
        endif;

        return $retorno;
    }

    public function alterarHabilitacaoProduto($data, $data_historico) {
        $retorno = array();

        if($this->db->update('habilitacoes_produto', $data, array('id' => $data['id']))):
            $this->db->insert("historico_habilitacao", $data_historico);

            $retorno["erro"] = false;
            if($data['status'] == 'S'):
                $retorno["msg"] = "Produto reabilitado para o cliente com sucesso!";
            else:
                $retorno["msg"] = "Produto desabilitado para o cliente com sucesso!";
            endif;
        else:
            $retorno["erro"] = true;
            $retorno["msg"] = "Ocorreu um erro ao atualizar o produto habilitado, entre em contato com o administrador do sistema!";
        endif;

        return $retorno;
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
}
