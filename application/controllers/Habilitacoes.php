<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Habilitacoes extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/habilitacoes');
        $this->load->model('habilitacoes_model');
        $this->load->model('auditorias_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_habilitacoes'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_habilitacoes'), 'admin/habilitacoes');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("habilitacoes", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['clientes'] = $this->habilitacoes_model->get_clientes()->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/habilitacoes/index', $this->data);
        }
	}

	public function historico($cliente)
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("habilitacoes", "historico"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            $historicos = array();
            foreach ($this->habilitacoes_model->get_historico($cliente)->result() as $historico) {
            	$historico->has_historicos = $this->habilitacoes_model->verificarHistoricos($historico->id);
            	$historicos[] = $historico;
            }
            $this->data['historico'] = $historicos;
            
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/habilitacoes/historico', $this->data);
        }
	}

	public function carregar_historico() {
		$p = $_POST;
        $retorno = array();
        $array_hist = array();

        if(empty($p['id_habilitacao'])):
            $retorno['erro'] = true;
            $retorno['msg'] = "Id da habilitação não foi enviado!";
            $this->session->set_flashdata('message', show_alert_error($retorno['msg']));
            exit(json_encode($retorno));
        endif;

        $res = $this->habilitacoes_model->resgatarHistorico($p['id_habilitacao']);

        if($res->num_rows() > 0) {
        	foreach ($res->result() as $value) {
        		$acao = $value->acao == 'D' ? 'Desabilitar' : 'Habilitar';
                $prdt = array(
                    'id' => $value->id,
                    'observacao' => $value->observacao,
                    'data_cadastro' => $value->data_cadastro,
                    'acao' =>  $acao,
                    'usuario' => $value->nome
                );
                array_push($array_hist, $prdt);
        	}
        }
        $retorno['erro'] = false;
        $retorno['data'] = $array_hist;
        exit(json_encode($retorno));
    }

	public function carregar_produtos() {
		$arr_prdmdl = array(
            'produtos_disponiveis' => array(),
            'produtos_habilitados' => array()
        );
        $arr_retorno = $this->habilitacoes_model->getProdutosHabilitacao($_POST["id_cliente"]);

        //Produtos Disponíveis
        if(!empty($arr_retorno['produtos_disponiveis'])):
            foreach($arr_retorno['produtos_disponiveis'] as $produto):
                if(!$produto['status']):
                    $arr_prdmdl['produtos_disponiveis'][] = array(
                        'id' => $produto['id'],
                        'id_cliente' => $_POST["id_cliente"],
                        'produto' => $produto['nome'],
                        'aplicacao' => $produto['aplicacao'],
                        'versao' => $this->habilitacoes_model->resgatarUltimaVersaoProduto($produto['id']),
                        'modulos' => $this->habilitacoes_model->getModulosProduto($produto['id'])
                    );
                endif;
            endforeach;
        endif;

        //Produtos Habilitados
        if(!empty($arr_retorno['produtos_habilitados'])):
            foreach($arr_retorno['produtos_habilitados'] as $produto):
                $arr_prdmdl['produtos_habilitados'][] = array(
                        'id' => $produto['id'],
                        'id_habilitacao' => $produto['id_habilitacao'],
                        'id_cliente' => $_POST["id_cliente"],
                        'produto' => $produto['nome'],
                        'aplicacao' => $produto['aplicacao'],
                        'demonstracao' => $produto['demonstracao'],
                        'periodo_inicio' => convertDateEUToBR($produto['periodo_inicio']),
                        'periodo_fim' => convertDateEUToBR($produto['periodo_fim']),
                        'vencimento' => $this->habilitacoes_model->resgatarDiasVencimento(date('Y-m-d'), $produto['periodo_fim'], $produto['status']),
                        'status' => $produto['status'],
                        'modulos' => $this->habilitacoes_model->getModulosHabilitados($produto['id'], $produto['id_habilitacao']),
                        'contrato_sax' => $produto['contrato_sax']
                    );
            endforeach;
        endif;

        exit(json_encode($arr_prdmdl));
	}

	public function cadastrar_habilitacao() {
		$p = $_POST;
        $retorno = array();

        //Checar se marcou habilitação
        if(!isset($p['inputProdutoHabilitar'])):
            $retorno['erro'] = true;
            $retorno['msg'] = "Por favor marque se deseja habilitar o produto!";
            $this->session->set_flashdata('message', show_alert_error($retorno['msg']));
            exit(json_encode($retorno));
        else:
            //Checar se demonstração
            $demonstracao = (isset($p['inputHabilitarDemo'])) ? trim(addslashes($p['inputHabilitarDemo'])) : 'N';
            if($demonstracao == 'N'):
                //Verificar se foi preenchido o período
                if(empty($p['inputHabilitacaoProdutoInicio']) || empty($p['inputHabilitacaoProdutoFim'])):
                    $retorno['erro'] = true;
		            $retorno['msg'] = "Por favor preencha o período de habilitação!";
            		$this->session->set_flashdata('message', show_alert_error($retorno['msg']));
		            exit(json_encode($retorno));
                endif;
                $periodo_inicio = convertDateBRToEU($p['inputHabilitacaoProdutoInicio']);
                $periodo_fim = convertDateBRToEU($p['inputHabilitacaoProdutoFim']);
            else:
                $periodo_inicio = date('Y-m-d');
                $periodo_fim = date('Y-m-d', strtotime("+30 days"));
            endif;
            
            $data = array(
            	"id_produto" => (int)base64_decode($p['inputIdProduto']),
            	"id_cliente" => (int)base64_decode($p['inputIdCliente']),
            	"periodo_inicio" => $periodo_inicio,
            	"periodo_fim" => $periodo_fim,
            	"data_cadastro" => date('Y-m-d H:i:s'),
            	"cadastrado_por" => $this->session->userdata('user_id'),
            	"status" => $p['inputProdutoHabilitar'],
            	"demonstracao" => $demonstracao,
                "contrato_sax" => $p['inputContratoSax']
            );

            $retorno_hbltc = $this->habilitacoes_model->inserirHabilitacaoProduto($data);

            if($retorno_hbltc == false):
            	$retorno['erro'] = true;
	            $retorno['msg'] = "Ocorreu um erro ao habilitar o produto para o cliente, entre em contato com o administrador do sistema.";
            	$this->session->set_flashdata('message', show_alert_error($retorno['msg']));
	            exit(json_encode($retorno));
            else:
            	$this->auditorias_model->create("habilitacoes", "cadastrar_habilitacao", 'id='.$retorno_hbltc.'id_produto='.$data["id_produto"].',id_cliente='.$data["id_cliente"].''.',cadastrado_por='.$data["cadastrado_por"].',status='.$data["status"]);

                if($p['inputModulos'] > 0):
                    $modulos = array();
                    foreach($p['inputModuloId'] as $key => $value):
                    	$modulos[] = array(
                    		"id_habilitacao" => $retorno_hbltc,
                    		"id_modulo" => (int)$key,
                    		"usuarios" => @(int)$p['inputHabilitacaoModuloUsu-'.$key],
                    		"status" => $p['inputHabilitacaoModuloStatus'][$key]
                    	);
                    endforeach;
                    $retorno = $this->habilitacoes_model->registrarModulos($modulos);
                else:
                    $retorno['erro'] = false;
		            $retorno['msg'] = "Produto habilitado para o cliente com sucesso!";
                endif;
                $this->session->set_flashdata('message', show_alert_success($retorno['msg']));
                exit(json_encode($retorno));
            endif;
        endif;
	}

	public function atualizar_habilitacao() {
        $p = $_POST;
        $retorno = array();

        //Checar se demonstração
        $demonstracao = (isset($p['inputHabilitarDemo'])) ? trim(addslashes($p['inputHabilitarDemo'])) : 'N';
        if($demonstracao == 'N'):
            //Verificar se foi preenchido o período
            if(empty($p['inputHabilitacaoProdutoInicio']) || empty($p['inputHabilitacaoProdutoFim'])):
                $retorno['erro'] = true;
	            $retorno['msg'] = "Por favor preencha o período de habilitação!";
            	$this->session->set_flashdata('message', show_alert_error($retorno['msg']));
	            exit(json_encode($retorno));
            endif;
            $periodo_inicio = convertDateBRToEU($p['inputHabilitacaoProdutoInicio']);
            $periodo_fim = convertDateBRToEU($p['inputHabilitacaoProdutoFim']);
        else:
            $periodo_inicio = date('Y-m-d');
            $periodo_fim = date('Y-m-d', strtotime("+30 days"));
        endif;

        $data = array(
        	"id" => (int)base64_decode($p['inputIdHabilitacao']),
        	"id_produto" => (int)base64_decode($p['inputIdProduto']),
        	"id_cliente" => (int)base64_decode($p['inputIdCliente']),
        	"periodo_inicio" => $periodo_inicio,
        	"periodo_fim" => $periodo_fim,
        	"data_atualizacao" => date('Y-m-d H:i:s'),
        	"atualizado_por" => $this->session->userdata('user_id'),
        	"demonstracao" => $demonstracao,
            "contrato_sax" => $p['inputContratoSax']
        );

        $retorno_hbltc = $this->habilitacoes_model->atualizarHabilitacaoProduto($data);

        if($retorno_hbltc == false):
        	$retorno['erro'] = true;
            $retorno['msg'] = "Ocorreu um erro ao atualizar o produto para o cliente, entre em contato com o administrador do sistema.";
            $this->session->set_flashdata('message', show_alert_error($retorno['msg']));
            exit(json_encode($retorno));
        else:
        	$this->auditorias_model->create("habilitacoes", "cadastrar_habilitacao", 'id='.$data["id"].',id_cliente='.$data["id_cliente"].',atualizado_por='.$data["atualizado_por"]);

            if($p['inputModulos'] > 0):
                $modulos = array();
            	$i = 0;
                foreach($p['inputHabilitacaoModuloId'] as $key => $value):
                	$modulos[$i] = array(
                		"id_habilitacao" => $retorno_hbltc,
                		"id_modulo" => (int)$key,
                		"usuarios" => @(int)$p['inputHabilitacaoModuloUsu-'.$key],
                		"status" => $p['inputHabilitacaoModuloStatus'][$key]
                	);

                    //Verificar se módulo já possui habilitação e insere o id para executar update
                    if(!empty($value) && $value > 0):
                    	$modulos[$i]["id"] = $value;
                    endif;

                    $i++;
                endforeach;
                $retorno = $this->habilitacoes_model->registrarModulos($modulos);
            else:
                $retorno['erro'] = false;
	            $retorno['msg'] = "Produto habilitado atualizado para o cliente com sucesso!";
            endif;
            $this->session->set_flashdata('message', show_alert_success($retorno['msg']));
            exit(json_encode($retorno));
        endif;      
	}

	public function desabilitar_habilitacao() {
		$p = $_POST;
        $retorno = array();

        if(empty($p['inputHabilitacaoId'])):
            $retorno['erro'] = true;
            $retorno['msg'] = "Id da habilitação não foi enviada!";
            $this->session->set_flashdata('message', show_alert_error($retorno['msg']));
            exit(json_encode($retorno));
        endif;

        if(empty($p['inputHabilitacaoJustificativa'])):
            $retorno['erro'] = true;
            $retorno['msg'] = "Por favor preencha a justificativa!";
            $this->session->set_flashdata('message', show_alert_error($retorno['msg']));
            exit(json_encode($retorno));
        endif;

        $data = array(
        	"id" => (int)base64_decode($p['inputHabilitacaoId']),
        	"data_atualizacao" => date('Y-m-d H:i:s'),
        	"atualizado_por" => $this->session->userdata('user_id'),
        	"status" => $p['inputStatus']
        );

        $data_historico = array(
        	"id_habilitacao" => (int)base64_decode($p['inputHabilitacaoId']),
        	"observacao" => trim(addslashes($p['inputHabilitacaoJustificativa'])),
        	"data_cadastro" => date('Y-m-d H:i:s'),
        	"cadastrado_por" => $this->session->userdata('user_id'),
        	"acao" => ($p['inputStatus'] == 'N') ? 'D' : 'R'
        );

        $retorno = $this->habilitacoes_model->alterarHabilitacaoProduto($data, $data_historico);
        if($retorno["erro"])
        	$this->session->set_flashdata('message', show_alert_error($retorno['msg']));
        else
    		$this->session->set_flashdata('message', show_alert_success($retorno['msg']));
        
        redirect('habilitacoes/index', 'refresh');
	}
}
