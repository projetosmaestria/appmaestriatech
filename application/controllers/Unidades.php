<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unidades extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/unidades');
        $this->load->model('unidades_model');
        $this->load->library('Curl');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_unidades'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_unidades'), 'admin/unidades');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("unidades", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['unidades'] = $this->unidades_model->get(array('select'=>'unidades.*', 'where'=>'unidades.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('unidades.id', 'desc')))->result();
            $this->data['message'] = $this->session->flashdata('message');

			$this->data['can_recursos_ws'] = $this->ion_auth->has_permissao("unidades", "recursos_ws");
			$this->data['can_facebook_api'] = $this->ion_auth->has_permissao("unidades", "facebook_api");
			$this->data['can_parametros_contrato'] = $this->ion_auth->has_permissao("unidades", "parametros_contrato");
			$this->data['can_edit'] = $this->ion_auth->has_permissao("unidades", "edit");
			$this->data['can_create'] = $this->ion_auth->has_permissao("unidades", "create");
			$this->data['can_rd_station'] = $this->ion_auth->has_permissao("unidades", "rd_station");
			$this->data['can_iugu'] = $this->ion_auth->has_permissao("unidades", "iugu");

            /* Load Template */
            $this->template->admin_render('admin/unidades/index', $this->data);
        }
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("unidades", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_create'), 'unidades/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		//$this->form_validation->set_rules('empresa', 'lang:create_validation_empresa_label', 'required');
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('tipo', 'lang:create_validation_tipo_label', 'required');
		$this->form_validation->set_rules('telefone1', 'lang:create_validation_telefone1_label', 'required');
		$this->form_validation->set_rules('email', 'lang:create_validation_email_label', 'trim|required|valid_email');
		$this->form_validation->set_rules('cep', 'lang:create_validation_cep_label', 'trim|required');
		$this->form_validation->set_rules('logradouro', 'lang:create_validation_logradouro_label', 'trim|required');
		$this->form_validation->set_rules('numero', 'lang:create_validation_numero_label', 'trim|required');
		$this->form_validation->set_rules('bairro', 'lang:create_validation_bairro_label', 'trim|required');
		$this->form_validation->set_rules('cidade', 'lang:create_validation_cidade_label', 'trim|required');
		$this->form_validation->set_rules('uf', 'lang:create_validation_uf_label', 'trim|required');
		if($this->input->post('tipo') == "J") {
			$this->form_validation->set_rules('cnpj', 'lang:create_validation_cnpj_label', 'required|validar_cnpj');
			$this->form_validation->set_rules('razao_social', 'lang:create_validation_razao_social_label', 'trim|required');
			$this->form_validation->set_rules('fantasia', 'lang:create_validation_fantasia_label', 'trim|required');
			$dados = array(
				"cnpj" => $this->input->post('cnpj'),
				"razao_social" => $this->input->post('razao_social'),
				"unidade" => $this->input->post('fantasia')
			);
		} else {
			$this->form_validation->set_rules('cpf', 'lang:create_validation_cpf_label', 'required|validar_cpf');
			$this->form_validation->set_rules('rg', 'lang:create_validation_rg_label', 'required');
			$this->form_validation->set_rules('nome', 'lang:create_validation_nome_label', 'trim|required');
			$dados = array(
				"cpf" => $this->input->post('cpf'),
				"rg" => $this->input->post('rg'),
				"unidade" => $this->input->post('nome')
			);
		}

		$dados["actived"] = $this->input->post('status');
		$dados["tipo_cliente"] = $this->input->post('tipo');
		$dados["tipo"] = $this->input->post('tipo');
		$dados["gestor"] = $this->input->post('gestor');
		$dados["email"] = $this->input->post('email');
		$dados["site"] = $this->input->post('site');
		$dados["endereco"] = $this->input->post('logradouro').", ".$this->input->post('bairro').", ".$this->input->post('numero');
		$dados["rua"] = $this->input->post('logradouro');
		$dados["numero"] = $this->input->post('numero');
		$dados["bairro"] = $this->input->post('bairro');
		$dados["cidade"] = $this->input->post('cidade');
		$dados["uf"] = $this->input->post('uf');
		$dados["cep"] = $this->input->post('cep');
		$dados["telefone1"] = $this->input->post('telefone1');
		$dados["telefone2"] = $this->input->post('telefone2');
		$dados["agente_encaminhamento"] = $this->input->post('responsavel');
		$dados["caracteristica"] = $this->input->post('caracteristica');
		//$dados["empresa_id"] = $this->input->post('empresa');
		$dados["empresa_id"] = $this->session->userdata('empresa_id');

		if ($this->form_validation->run() == TRUE && $id = $this->unidades_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->unidades_model->messages()));
			redirect('unidades', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->unidades_model->errors() ? show_alert_error($this->unidades_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            /*$empresa_options = array(""=>"Selecione");
			foreach ($this->unidades_model->get_empresas()->result() as $emp) {
				$empresa_options[$emp->id] = $emp->nome;
			}
			$this->data['empresa'] = array(
				'name'  => 'empresa',
				'id'    => 'empresa',
	            'class' => 'form-control',
	            'options' => $empresa_options,
				'selected' => ($this->form_validation->set_value('empresa') ? $this->form_validation->set_value('empresa') : $this->session->userdata('empresa_id')),
			);*/
			$this->data['status'] = $this->form_validation->set_value('status');
			$this->data['tipo'] = $this->form_validation->set_value('tipo');
			$car_options = array(""=>"Selecione");
			foreach ($this->unidades_model->get_caracteristicas()->result() as $carac) {
				$car_options[$carac->id] = $carac->descricao;
			}
			$this->data['caracteristica'] = array(
				'name'  => 'caracteristica',
				'id'    => 'caracteristica',
	            'class' => 'form-control',
	            'options' => $car_options,
				'selected' => $this->form_validation->set_value('caracteristica'),
			);
			$this->data['cnpj'] = array(
				'name'  => 'cnpj',
				'id'    => 'cnpj',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('cnpj')
			);
			$this->data['cpf'] = array(
				'name'  => 'cpf',
				'id'    => 'cpf',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('cpf')
			);
			$this->data['razao_social'] = array(
				'name'  => 'razao_social',
				'id'    => 'razao_social',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('razao_social')
			);
			$this->data['rg'] = array(
				'name'  => 'rg',
				'id'    => 'rg',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('rg')
			);
			$this->data['fantasia'] = array(
				'name'  => 'fantasia',
				'id'    => 'fantasia',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('fantasia')
			);
			$this->data['nome'] = array(
				'name'  => 'nome',
				'id'    => 'nome',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('nome')
			);
			$this->data['gestor'] = array(
				'name'  => 'gestor',
				'id'    => 'gestor',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('gestor')
			);
			$this->data['responsavel'] = array(
				'name'  => 'responsavel',
				'id'    => 'responsavel',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('responsavel')
			);
			$this->data['telefone1'] = array(
				'name'  => 'telefone1',
				'id'    => 'telefone1',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('telefone1')
			);
			$this->data['telefone2'] = array(
				'name'  => 'telefone2',
				'id'    => 'telefone2',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('telefone2')
			);
			$this->data['email'] = array(
				'name'  => 'email',
				'id'    => 'email',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('email')
			);
			$this->data['site'] = array(
				'name'  => 'site',
				'id'    => 'site',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('site')
			);
			$this->data['cep'] = array(
				'name'  => 'cep',
				'id'    => 'cep',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('cep'),
				'onblur' => "loadEndereco(this.value, '')"
			);
			$this->data['logradouro'] = array(
				'name'  => 'logradouro',
				'id'    => 'logradouro',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('logradouro')
			);
			$this->data['numero'] = array(
				'name'  => 'numero',
				'id'    => 'numero',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('numero')
			);
			$this->data['bairro'] = array(
				'name'  => 'bairro',
				'id'    => 'bairro',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('bairro')
			);
			$this->data['cidade'] = array(
				'name'  => 'cidade',
				'id'    => 'cidade',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('cidade')
			);
			$uf_options = array(""=>"Selecione");
			foreach (get_uf() as $uf_key => $uf_value) {
				$uf_options[$uf_key] = $uf_value;
			}
			$this->data['uf'] = array(
				'name'  => 'uf',
				'id'    => 'uf',
	            'class' => 'form-control',
	            'options' => $uf_options,
				'selected' => $this->form_validation->set_value('uf'),
			);

            /* Load Template */
            $this->template->admin_render('admin/unidades/create', $this->data);
		}
	}

	public function recursos_ws($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "recursos_ws"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_recursos_ws'), 'unidades/recursos_ws');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		$unidade = $this->unidades_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		/* Validate form input */
		if($this->input->post('token') && $unidade->token != $this->input->post('token')) {
			$this->form_validation->set_rules('token', 'lang:create_validation_token_label', 'required|is_unique[unidades.token]');
		} else {
			$this->form_validation->set_rules('token', 'lang:create_validation_token_label', 'required');
		}

		$dados["token"] = $this->input->post('token');
		$dados["recursos"] = serialize($this->input->post('recursos'));
		$dados["recursos_cliente"] = serialize($this->input->post('tab'));

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$unidade_update = $this->unidades_model->update($id, $dados);

				if ($unidade_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_unidade_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->unidades_model->errors()));
				}

				redirect('unidades', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->unidades_model->errors() ? show_alert_error($this->unidades_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		$this->data['token'] = array(
			'name'  => 'token',
			'id'    => 'token',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('token', $unidade->token),
			'readonly' => true
		);
		$this->data['recursos_ws'] = get_recursos_ws(unserialize($unidade->recursos));

		$inputs = array();
        $grupos = $this->unidades_model->get_groups(null, $unidade->empresa_id);
        $tabelas = get_tabelas_cliente_ws();
        $recursos_cliente = unserialize($unidade->recursos_cliente);
        foreach ($tabelas as $tab) {
            $inputs[] = array(
                'name'  => 'tab['.$tab.'][]',
                'id'    => $tab,
                'class' => 'multipleSelect',
                'multiple' => 'multiple',
                'options' => $grupos,
                'selected' => @$recursos_cliente[$tab]
            );
        }
        $this->data['inputs'] = $inputs;
		// echo "<pre>".print_r($inputs, true)."</pre>";
		// exit;
		$this->data['btn_token'] = array(
			'id'    => 'token',
			'content'    => 'Gerar',
			'type'  => 'button',
            'class' => 'btn btn-primary btn-flat',
			'onclick' => 'gerarToken();',
			'style' => 'margin-top:5px;'
		);

        /* Load Template */
        $this->template->admin_render('admin/unidades/recursos_ws', $this->data);
	}

	public function facebook_api($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "facebook_api"))
		{
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('app_id', 'lang:create_validation_app_id_label', 'required');
		$this->form_validation->set_rules('app_secret', 'lang:create_validation_app_secret_label', 'required');
		$this->form_validation->set_rules('access_token', 'lang:create_validation_access_token_label', 'required');
		$this->form_validation->set_rules('page_id', 'lang:create_validation_page_id_label', 'required');
		$this->form_validation->set_rules('midia', 'lang:create_validation_midia_label', 'required');
		$this->form_validation->set_rules('evento', 'lang:create_validation_evento_label', 'required');

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_facebook_api'), 'unidades/facebook_api');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		$unidade = $this->unidades_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		$dados["fb_app_id"] = $this->input->post('app_id');
		$dados["fb_app_secret"] = $this->input->post('app_secret');
		$dados["fb_access_token"] = $this->input->post('access_token');
		$dados["fb_page_id"] = $this->input->post('page_id');
		$dados["fb_page_name"] = $this->input->post('page_name');
		//$dados["fb_expires_at"] = date('Y/m/d H:i:s', strtotime("+".$this->input->post('expires_at')." seconds"));
		$dados["fb_expires_at"] = $this->input->post('expires_at');
		$dados["fb_status_api"] = $this->input->post('status');
		$dados["fb_midia_id"] = $this->input->post('midia');
		$dados["fb_evento_id"] = $this->input->post('evento');

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$fluxo = true;

				if($unidade->fb_access_token == "") {
					$extender_token = $this->unidades_model->extender_token($this->input->post('app_id'), $this->input->post('app_secret'));
					// exit("<pre>".print_r($extender_token,true)."</pre>");
					if($extender_token["erro"] == false) {
						$dados["fb_access_token"] = $extender_token["token"];
						$dados["fb_expires_at"] = $extender_token["expires_at"];
					} else {
						$this->data['message'] = show_alert_error($extender_token["msg"]);
						$fluxo = false;
					}
				}

				if($fluxo == true) {
					$unidade_update = $this->unidades_model->update($id, $dados);

					if ($unidade_update)
					{
						$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_unidade_saved')));
					}
					else
					{
						$this->session->set_flashdata('message', show_alert_error($this->unidades_model->errors()));
					}
					redirect('unidades', 'refresh');
				}
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->unidades_model->errors() ? show_alert_error($this->unidades_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		$this->data['id'] = $id;
		$this->data['status'] = $unidade->fb_status_api;
		$this->data['page_id'] = ($this->input->post('page_id')) ? $this->input->post('page_id') : $unidade->fb_page_id;
		$this->data['page_name'] = ($this->input->post('page_name')) ? $this->input->post('page_name') : $unidade->fb_page_name;

		$this->data['app_id'] = array(
			'name'  => 'app_id',
			'id'    => 'app_id',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('app_id', $unidade->fb_app_id)
		);
		$this->data['app_secret'] = array(
			'name'  => 'app_secret',
			'id'    => 'app_secret',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('app_secret', $unidade->fb_app_secret)
		);
		$this->data['access_token'] = array(
			'name'  => 'access_token',
			'id'    => 'access_token',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('access_token', $unidade->fb_access_token),
			'readonly' => true
		);
		$array_midias = array(""=>"Selecione");
		foreach($this->unidades_model->get_midias()->result() as $midia) {
			$array_midias[$midia->id] = $midia->descricao;
		}
		$this->data['midia'] = array(
			'name'  => 'midia',
			'id'    => 'midia',
            'onchange' => "loadEventos(this.value, ".$id.",'".base_url('unidades/')."')",
            'class' => 'form-control',
			'options' => $array_midias,
			'selected' => $this->form_validation->set_value('midia', $unidade->fb_midia_id)
		);

		$array_eventos = array(""=>"Selecione");
		if($unidade->fb_midia_id != "") {
			foreach($this->unidades_model->get_eventos($unidade->fb_midia_id, $id)->result() as $evento) {
				$array_eventos[$evento->id] = $evento->descricao;
			}
		}
		$this->data['evento'] = array(
			'name'  => 'evento',
			'id'    => 'evento',
            'class' => 'form-control',
			'options' => $array_eventos,
			'selected' => $this->form_validation->set_value('evento', $unidade->fb_evento_id)
		);
		$this->data['btn_access_token'] = array(
			'id' => 'btn_access_token',
			'content' => '<i class="fa fa-facebook-square" aria-hidden="true"></i> Continuar com Facebook',
			'type' => 'button',
            'class' => 'btn btn-primary btn-flat btn-facebook',
			'onclick' => 'gerarFbAccessToken();',
			'style' => 'margin-top:5px;'
		);

        /* Load Template */
        $this->template->admin_render('admin/unidades/facebook_api', $this->data);
	}

	public function has_fb_access_token() {
		$post = $_POST;
		$retorno = array();
		$exist = $this->unidades_model->get(array('select'=>'unidades.fb_access_token, unidades.fb_expires_at', 'where'=>'fb_app_id = "'.$post['app_id'].'" AND fb_expires_at > "'.date('Y-m-d H:i:s').'"', 'order_by'=>array('unidades.id', 'desc')));
        if($exist->num_rows() > 0)
        {
            $retorno["erro"] = false;
            $retorno['token'] = $exist->row()->fb_access_token;
            $retorno['expires_at'] = $exist->row()->fb_expires_at;
        } else {
        	$retorno["erro"] = true;
        }
        exit(json_encode($retorno));
	}

	public function update_fb_access_token() {
		$post = $_POST;
		$retorno = array();

		$retorno = $this->unidades_model->update_by_fb_app_id($post["app_id"], array("fb_access_token"=>$post["access_token"], "fb_expires_at"=>date('Y/m/d H:i:s', strtotime("+".$post["expires_at"]." seconds"))));
		exit(json_encode($retorno));
	}

	public function parametros_contrato($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "parametros_contrato"))
		{
			redirect('auth', 'refresh');
		}

		$unidade = $this->unidades_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();
		$status = $this->unidades_model->get_status($unidade->empresa_id);
		$historicos = $this->unidades_model->get_historicos_status($unidade->empresa_id);

		if($status->num_rows() == 0) {
			$this->session->set_flashdata('message', show_alert_error("É necessário importar os status do SAX para acessar a página."));
			redirect('unidades', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_parametros_contrato'), 'unidades/parametros_contrato');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		$config = $this->unidades_model->get_config_contrato($id)->row();

		if (isset($_POST) && !empty($_POST))
		{
			$dados["LIG_STATUS_CONTRATO_01"] = ($this->input->post('matricula_aluno') == "") ? null : $this->input->post('matricula_aluno');
			$dados["LIG_STATUS_CONTRATO_02"] = ($this->input->post('pagamentro_matricula') == "") ? null : $this->input->post('pagamentro_matricula');
			$dados["LIG_STATUS_CONTRATO_03"] = ($this->input->post('primeira_presenca') == "") ? null : $this->input->post('primeira_presenca');
			$dados["LIG_STATUS_CONTRATO_04"] = ($this->input->post('sem_presenca') == "") ? null : $this->input->post('sem_presenca');
			$dados["LIG_STATUS_CONTRATO_06"] = ($this->input->post('cancelamento_contrato') == "") ? null : $this->input->post('cancelamento_contrato');
			$dados["LIG_STATUS_CONTRATO_05"] = ($this->input->post('matricula_aluno_pauta') == "") ? null : $this->input->post('matricula_aluno_pauta');
			$dados["NUM_FALTAS_CONTRATO_05"] = ($this->input->post('num_faltas') == "") ? null : $this->input->post('num_faltas');
			$dados["LIG_ID_STATUS_TURMA01"] = ($this->input->post('inicio_turma') == "") ? null : $this->input->post('inicio_turma');
			$dados["LIG_ID_STATUS_TURMA02"] = ($this->input->post('lancamento_pauta_inicial') == "") ? null : $this->input->post('lancamento_pauta_inicial');
			$dados["LIG_ID_STATUS_TURMA03"] = ($this->input->post('lancamento_pauta_final') == "") ? null : $this->input->post('lancamento_pauta_final');
			$dados["LIG_ID_STATUS_DESISTENCIA"] = ($this->input->post('matricula_desistencia') == "") ? null : $this->input->post('matricula_desistencia');
			$dados["NUM_DIAS_DESISTENCIA"] = ($this->input->post('dias_desistencia') == "") ? null : $this->input->post('dias_desistencia');
			$dados["LIG_ID_STATUS_NUNCA_ACESSOU"] = ($this->input->post('matricula_nunca_acessou') == "") ? null : $this->input->post('matricula_nunca_acessou');
			$dados["NUM_DIAS_NUNCA_ACESSOU"] = ($this->input->post('dias_nunca_acessou') == "") ? null : $this->input->post('dias_nunca_acessou');
			$dados["LIG_ID_ANULADO"] = ($this->input->post('anulado') == "") ? null : $this->input->post('anulado');
			$dados["STATUS_HISTORICO"] = ($this->input->post('status_historico') == "") ? null : $this->input->post('status_historico');
			$dados["LIG_ID_FILIAL"] = $id;
			$dados["LIG_ID_RESGATE_FINANCEIRO"] = ($this->input->post('resgate_financeiro') == "") ? null : $this->input->post('resgate_financeiro');
			$dados["LIG_ID_RESGATE_PEDAGOGICO"] = ($this->input->post('resgate_pedagogico') == "") ? null : $this->input->post('resgate_pedagogico');
			$dados["LIG_ID_CONCLUIDO"] = ($this->input->post('concluido') == "") ? null : $this->input->post('concluido');

			if($config)
				$config_save = $this->unidades_model->config_update($id, $dados);
			else
				$config_save = $this->unidades_model->config_create(array_filter($dados));

			if ($config_save)
			{
				$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_config_saved')));
			}
			else
			{
				$this->session->set_flashdata('message', show_alert_error($this->unidades_model->errors()));
			}

			redirect('unidades', 'refresh');
		}

        $status_options = array(""=>"Selecione");
        foreach ($status->result() as $s) {
            $status_options[$s->id] = $s->descricao;
        }
        $this->data['matricula_aluno'] = array(
            'name'  => 'matricula_aluno',
            'id'    => 'matricula_aluno',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('matricula_aluno', @$config->LIG_STATUS_CONTRATO_01)),
        );
        $this->data['pagamentro_matricula'] = array(
            'name'  => 'pagamentro_matricula',
            'id'    => 'pagamentro_matricula',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('pagamentro_matricula', @$config->LIG_STATUS_CONTRATO_02)),
        );
        $this->data['primeira_presenca'] = array(
            'name'  => 'primeira_presenca',
            'id'    => 'primeira_presenca',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('primeira_presenca', @$config->LIG_STATUS_CONTRATO_03)),
        );
        $this->data['sem_presenca'] = array(
            'name'  => 'sem_presenca',
            'id'    => 'sem_presenca',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('sem_presenca', @$config->LIG_STATUS_CONTRATO_04)),
        );
        $this->data['cancelamento_contrato'] = array(
            'name'  => 'cancelamento_contrato',
            'id'    => 'cancelamento_contrato',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('cancelamento_contrato', @$config->LIG_STATUS_CONTRATO_06)),
        );
        $this->data['anulado'] = array(
            'name'  => 'anulado',
            'id'    => 'anulado',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('anulado', @$config->LIG_ID_ANULADO)),
        );
        $this->data['resgate_financeiro'] = array(
            'name'  => 'resgate_financeiro',
            'id'    => 'resgate_financeiro',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('resgate_financeiro', @$config->LIG_ID_RESGATE_FINANCEIRO)),
        );
        $this->data['resgate_pedagogico'] = array(
            'name'  => 'resgate_pedagogico',
            'id'    => 'resgate_pedagogico',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('resgate_pedagogico', @$config->LIG_ID_RESGATE_PEDAGOGICO)),
        );
        $this->data['concluido'] = array(
            'name'  => 'concluido',
            'id'    => 'concluido',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('concluido', @$config->LIG_ID_CONCLUIDO)),
        );
        $this->data['matricula_aluno_pauta'] = array(
            'name'  => 'matricula_aluno_pauta',
            'id'    => 'matricula_aluno_pauta',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('matricula_aluno_pauta', @$config->LIG_STATUS_CONTRATO_05)),
        );
        $this->data['num_faltas'] = array(
            'name'  => 'num_faltas',
            'id'    => 'num_faltas',
            'class' => 'form-control',
			'type'  => 'text',
            'value' => ($this->form_validation->set_value('num_faltas', @$config->NUM_FALTAS_CONTRATO_05)),
        );
        $this->data['inicio_turma'] = array(
            'name'  => 'inicio_turma',
            'id'    => 'inicio_turma',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('inicio_turma', @$config->LIG_ID_STATUS_TURMA01)),
        );
        $this->data['lancamento_pauta_inicial'] = array(
            'name'  => 'lancamento_pauta_inicial',
            'id'    => 'lancamento_pauta_inicial',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('lancamento_pauta_inicial', @$config->LIG_ID_STATUS_TURMA02)),
        );
        $this->data['lancamento_pauta_final'] = array(
            'name'  => 'lancamento_pauta_final',
            'id'    => 'lancamento_pauta_final',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('lancamento_pauta_final', @$config->LIG_ID_STATUS_TURMA03)),
        );
        $this->data['matricula_desistencia'] = array(
            'name'  => 'matricula_desistencia',
            'id'    => 'matricula_desistencia',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('matricula_desistencia', @$config->LIG_ID_STATUS_DESISTENCIA)),
        );
        $this->data['dias_desistencia'] = array(
            'name'  => 'dias_desistencia',
            'id'    => 'dias_desistencia',
            'class' => 'form-control',
			'type'  => 'text',
            'value' => ($this->form_validation->set_value('dias_desistencia', @$config->NUM_DIAS_DESISTENCIA)),
        );
        $this->data['matricula_nunca_acessou'] = array(
            'name'  => 'matricula_nunca_acessou',
            'id'    => 'matricula_nunca_acessou',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('matricula_nunca_acessou', @$config->LIG_ID_STATUS_NUNCA_ACESSOU)),
        );
        $historicos_options = array(""=>"Selecione");
        foreach ($historicos->result() as $h) {
            $historicos_options[$h->id] = $h->descricao;
        }
        $this->data['status_historico'] = array(
            'name'  => 'status_historico',
            'id'    => 'status_historico',
            'class' => 'form-control',
            'options' => $historicos_options,
            'selected' => ($this->form_validation->set_value('status_historico', @$config->STATUS_HISTORICO)),
        );
        $this->data['dias_nunca_acessou'] = array(
            'name'  => 'dias_nunca_acessou',
            'id'    => 'dias_nunca_acessou',
            'class' => 'form-control',
			'type'  => 'text',
            'value' => ($this->form_validation->set_value('dias_nunca_acessou', @$config->NUM_DIAS_NUNCA_ACESSOU)),
        );

        /* Load Template */
        $this->template->admin_render('admin/unidades/parametros_contrato', $this->data);
	}

	public function cursos($id)
	{
        if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['unidade'] = $this->unidades_model->get(array('select'=>'unidades.*', 'where'=>'unidades.id = '.$id.' and unidades.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('unidades.id', 'desc')))->result()[0];
            $this->data['message'] = $this->session->flashdata('message');
			$this->data['cursos'] = [];
			$this->data['cursos_filial'] = [];

			$sql = "SELECT c.id_curso, c.descricao, f.LIG_ID_FILIAL id_filial
					FROM curso c LEFT JOIN curso_filial f ON c.id_curso = f.LIG_ID_CURSO AND f.LIG_ID_FILIAL = $id
					WHERE c.empresa_id = ".$this->session->userdata('empresa_id')." AND c.ativo = 'S'
					GROUP BY c.id_curso ORDER BY c.descricao";

			foreach($this->db->query($sql)->result() as $curso){
				if($curso->id_filial == null){
					$this->data['cursos'][] = $curso;
				}else{
					$this->data['cursos_filial'][] = $curso;
				}
			}
			
            /* Load Template */
            $this->template->admin_render('admin/unidades/cursos', $this->data);
        }
	}

	public function cursoSalvar($id)
	{
		if (isset($_POST) && ! empty($_POST)){
			if($unidade = $this->db->query("SELECT id FROM unidades WHERE id = $id AND empresa_id = ".$this->session->userdata('empresa_id'))->result()){	
				if($curso = $this->db->query("SELECT id_curso FROM curso WHERE descricao = '".$_POST['curso']."'")->result()){
					$sql = "INSERT INTO curso_filial(lig_id_filial, lig_id_curso, data_cadastro) VALUES(".$unidade[0]->id.",".$curso[0]->id_curso.",now())";
					$this->db->query($sql);
				}
			}
		}

		redirect('unidades/cursos/'.$id);
	}

	public function cursoDeletar($id_unidade, $id_curso){
		if($curso_filial = $this->db->query("SELECT * FROM curso_filial WHERE lig_id_filial = $id_unidade AND lig_id_curso = $id_curso")->result()){
			$this->db->query("DELETE FROM curso_filial WHERE lig_id_filial = $id_unidade AND lig_id_curso = $id_curso");
		}
		redirect('unidades/cursos/'.$id_unidade);
	}


	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("unidades", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/unidades/delete');
        }
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_edit'), 'unidades/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$unidade = $this->unidades_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('tipo', 'lang:create_validation_tipo_label', 'required');
		$this->form_validation->set_rules('telefone1', 'lang:create_validation_telefone1_label', 'required');
		$this->form_validation->set_rules('email', 'lang:create_validation_email_label', 'trim|required|valid_email');
		$this->form_validation->set_rules('cep', 'lang:create_validation_cep_label', 'trim|required');
		$this->form_validation->set_rules('logradouro', 'lang:create_validation_logradouro_label', 'trim|required');
		$this->form_validation->set_rules('numero', 'lang:create_validation_numero_label', 'trim|required');
		$this->form_validation->set_rules('bairro', 'lang:create_validation_bairro_label', 'trim|required');
		$this->form_validation->set_rules('cidade', 'lang:create_validation_cidade_label', 'trim|required');
		$this->form_validation->set_rules('uf', 'lang:create_validation_uf_label', 'trim|required');
		if($this->input->post('tipo') == "J") {
			if($unidade->cnpj == $this->input->post('cnpj'))
				$this->form_validation->set_rules('cnpj', 'lang:create_validation_cnpj_label', 'required|validar_cnpj');
			else
				$this->form_validation->set_rules('cnpj', 'lang:create_validation_cnpj_label', 'required|validar_cnpj');
			$this->form_validation->set_rules('razao_social', 'lang:create_validation_razao_social_label', 'trim|required');
			$this->form_validation->set_rules('fantasia', 'lang:create_validation_fantasia_label', 'trim|required');
			$dados = array(
				"cnpj" => $this->input->post('cnpj'),
				"cpf" => "",
				"razao_social" => $this->input->post('razao_social'),
				"rg" => "",
				"unidade" => $this->input->post('fantasia')
			);
		} else {
			if($unidade->cpf == $this->input->post('cpf'))
				$this->form_validation->set_rules('cpf', 'lang:create_validation_cpf_label', 'required|validar_cpf');
			else
				$this->form_validation->set_rules('cpf', 'lang:create_validation_cpf_label', 'required|validar_cpf');
			$this->form_validation->set_rules('rg', 'lang:create_validation_rg_label', 'required');
			$this->form_validation->set_rules('nome', 'lang:create_validation_nome_label', 'trim|required');
			$dados = array(
				"cpf" => $this->input->post('cpf'),
				"cnpj" => "",
				"razao_social" => "",
				"rg" => $this->input->post('rg'),
				"unidade" => $this->input->post('nome')
			);
		}

		$dados["actived"] = $this->input->post('status');
		$dados["tipo_cliente"] = $this->input->post('tipo');
		$dados["tipo"] = $this->input->post('tipo');
		$dados["gestor"] = $this->input->post('gestor');
		$dados["email"] = $this->input->post('email');
		$dados["site"] = $this->input->post('site');
		$dados["endereco"] = $this->input->post('logradouro').", ".$this->input->post('bairro').", ".$this->input->post('numero');
		$dados["rua"] = $this->input->post('logradouro');
		$dados["numero"] = $this->input->post('numero');
		$dados["bairro"] = $this->input->post('bairro');
		$dados["cidade"] = $this->input->post('cidade');
		$dados["uf"] = $this->input->post('uf');
		$dados["cep"] = $this->input->post('cep');
		$dados["telefone1"] = $this->input->post('telefone1');
		$dados["telefone2"] = $this->input->post('telefone2');
		$dados["agente_encaminhamento"] = $this->input->post('responsavel');
		$dados["modified"] = date("Y-m-d H:i:s");
		$dados["empresa_id"] = $unidade->empresa_id;
		$dados["caracteristica"] = $this->input->post('caracteristica');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$unidade_update = $this->unidades_model->update($id, $dados);

				if ($unidade_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_unidade_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->unidades_model->errors()));
				}

				redirect('unidades', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->unidades_model->errors() ? show_alert_error($this->unidades_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['unidade']   = $unidade;

        /*if($unidade->rua == "" && $unidade->bairro == "") {
			$retorno_curl = $this->curl->get('app.xkeysti.com.br/api/v1/getEnderecoByCep?cep='.$unidade->cep);
			$data['rua'] = $retorno_curl[0]['logradouro_nome'];
			$data['bairro'] = $retorno_curl[0]['bairro_nome'];
			$data['cidade'] = $retorno_curl[0]['cidade_nome'];
			$data['uf'] = $retorno_curl[0]['uf'];
        }*/

		$empresa_options = array(""=>"Selecione");
		foreach ($this->unidades_model->get_empresas()->result() as $emp) {
			$empresa_options[$emp->id] = $emp->nome;
		}
		$this->data['empresa'] = array(
			'name'  => 'empresa',
			'id'    => 'empresa',
            'class' => 'form-control',
            'options' => $empresa_options,
			'selected' => $this->form_validation->set_value('empresa', $unidade->empresa_id),
		);
		$this->data['status'] = $this->form_validation->set_value('status', $unidade->actived);
		$this->data['tipo'] = $this->form_validation->set_value('tipo', $unidade->tipo);
		$car_options = array(""=>"Selecione");
		foreach ($this->unidades_model->get_caracteristicas()->result() as $carac) {
			$car_options[$carac->id] = $carac->descricao;
		}
		$this->data['caracteristica'] = array(
			'name'  => 'caracteristica',
			'id'    => 'caracteristica',
            'class' => 'form-control',
            'options' => $car_options,
			'selected' => $this->form_validation->set_value('caracteristica', $unidade->caracteristica),
		);
		$this->data['cnpj'] = array(
			'name'  => 'cnpj',
			'id'    => 'cnpj',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('cnpj', $unidade->cnpj)
		);
		$this->data['cpf'] = array(
			'name'  => 'cpf',
			'id'    => 'cpf',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('cpf', $unidade->cpf)
		);
		$this->data['razao_social'] = array(
			'name'  => 'razao_social',
			'id'    => 'razao_social',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('razao_social', $unidade->razao_social)
		);
		$this->data['rg'] = array(
			'name'  => 'rg',
			'id'    => 'rg',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('rg', $unidade->rg)
		);
		$this->data['fantasia'] = array(
			'name'  => 'fantasia',
			'id'    => 'fantasia',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('fantasia', $unidade->unidade)
		);
		$this->data['nome'] = array(
			'name'  => 'nome',
			'id'    => 'nome',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('nome', $unidade->unidade)
		);
		$this->data['gestor'] = array(
			'name'  => 'gestor',
			'id'    => 'gestor',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('gestor', $unidade->gestor)
		);
		$this->data['responsavel'] = array(
			'name'  => 'responsavel',
			'id'    => 'responsavel',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('responsavel', $unidade->agente_encaminhamento)
		);
		$this->data['telefone1'] = array(
			'name'  => 'telefone1',
			'id'    => 'telefone1',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('telefone1', $unidade->telefone1)
		);
		$this->data['telefone2'] = array(
			'name'  => 'telefone2',
			'id'    => 'telefone2',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('telefone2', $unidade->telefone2)
		);
		$this->data['email'] = array(
			'name'  => 'email',
			'id'    => 'email',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('email', $unidade->email)
		);
		$this->data['site'] = array(
			'name'  => 'site',
			'id'    => 'site',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('site', $unidade->site)
		);
		$this->data['cep'] = array(
			'name'  => 'cep',
			'id'    => 'cep',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('cep', $unidade->cep),
			'onblur' => "loadEndereco(this.value, '../')"
		);
		$this->data['logradouro'] = array(
			'name'  => 'logradouro',
			'id'    => 'logradouro',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('logradouro', $unidade->rua)
		);
		$this->data['numero'] = array(
			'name'  => 'numero',
			'id'    => 'numero',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('numero', $unidade->numero)
		);
		$this->data['bairro'] = array(
			'name'  => 'bairro',
			'id'    => 'bairro',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('bairro', $unidade->bairro)
		);
		$this->data['cidade'] = array(
			'name'  => 'cidade',
			'id'    => 'cidade',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('cidade', $unidade->cidade)
		);
		$uf_options = array(""=>"Selecione");
		foreach (get_uf() as $uf_key => $uf_value) {
			$uf_options[$uf_key] = $uf_value;
		}
		$this->data['uf'] = array(
			'name'  => 'uf',
			'id'    => 'uf',
            'class' => 'form-control',
            'options' => $uf_options,
			'selected' => $this->form_validation->set_value('uf', $unidade->uf),
		);

        /* Load Template */
        $this->template->admin_render('admin/unidades/edit', $this->data);
	}


	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("unidades", "activate"))
		{
			$activation = $this->unidades_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->unidades_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->unidades_model->messages()));
		redirect('unidades', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("unidades", "deactivate"))
		{
			$deactivation = $this->unidades_model->deactivate($id);
		}
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->unidades_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->unidades_model->messages()));
		redirect('unidades', 'refresh');
	}

	public function buscar_endereco() {
		$retorno = $this->curl->get('app.xkeysti.com.br/api/v1/getEnderecoByCep?cep='.$this->input->post('cep'));
		exit(json_decode($retorno));
	}

	public function buscar_eventos() {
		$eventos = $this->unidades_model->get_eventos($_POST["midia"], $_POST["unidade"]);
		$retorno = array();
		if($eventos->num_rows() > 0) {
			foreach ($eventos->result() as $value) {
				$retorno[$value->id] = $value->descricao;
			}
		}
		exit(json_encode($retorno));
	}

	public function rd_station($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "rd_station"))
		{
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('client_id', 'lang:create_validation_client_id_label', 'required');
		$this->form_validation->set_rules('client_secret', 'lang:create_validation_client_secret_label', 'required');
		$this->form_validation->set_rules('token', 'lang:create_validation_token_label', 'required');
		$this->form_validation->set_rules('refresh_token', 'lang:create_validation_refresh_token_label', 'required');

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_rd_station'), 'unidades/rd_station');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		$unidade = $this->unidades_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		$dados["rd_station_client_id"] = $this->input->post('client_id');
		$dados["rd_station_client_secret"] = $this->input->post('client_secret');
		$dados["rd_station_token"] = $this->input->post('token');
		$dados["rd_station_refresh_token"] = $this->input->post('refresh_token');
		$dados["rd_station_status"] = $this->input->post('status');

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$unidade_update = $this->unidades_model->update($id, $dados);

				if ($unidade_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_unidade_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->unidades_model->errors()));
				}
				redirect('unidades', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->unidades_model->errors() ? show_alert_error($this->unidades_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		$this->data['id'] = $id;
		$this->data['unidade'] = $unidade;
		$this->data['status'] = $unidade->rd_station_status;
		$this->data['client_id'] = array(
			'name'  => 'client_id',
			'id'    => 'client_id',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('client_id', $unidade->rd_station_client_id)
		);
		$this->data['client_secret'] = array(
			'name'  => 'client_secret',
			'id'    => 'client_secret',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('client_secret', $unidade->rd_station_client_secret)
		);
		$this->data['token'] = array(
			'name'  => 'token',
			'id'    => 'token',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('token', $unidade->rd_station_token),
			'readonly' => true
		);
		$this->data['btn_token'] = array(
			'id' => 'btn_token',
			'content' => 'Gerar',
			'type' => 'button',
            'class' => 'btn btn-primary btn-flat',
			'onclick' => 'gerarRdToken();',
			'style' => 'margin-top:5px;'
		);

        /* Load Template */
        $this->template->admin_render('admin/unidades/rd_station', $this->data);
	}
	
	public function iugu($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "iugu"))
		{
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('token', 'lang:create_validation_token_label', 'required');
		$this->form_validation->set_rules('acc_id', 'lang:create_validation_acc_id_label', 'required');
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_iugu'), 'unidades/iugu');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		$unidade = $this->unidades_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		$dados["iugu_token"] = $this->input->post('token');
		$dados["iugu_acc_id"] = $this->input->post('acc_id');
		$dados["iugu_status"] = $this->input->post('status');
		$dados["iugu_receb_boleto"] = serialize($this->input->post('receb_boleto'));
		$dados["iugu_receb_credito"] = serialize($this->input->post('receb_credito'));

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$unidade_update = $this->unidades_model->update($id, $dados);

				if ($unidade_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_unidade_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->unidades_model->errors()));
				}
				redirect('unidades', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->unidades_model->errors() ? show_alert_error($this->unidades_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		$this->data['id'] = $id;
		$this->data['status'] = ($this->input->post('status')) ? $this->input->post('status') : $unidade->iugu_status;

		$this->data['token'] = array(
			'name'  => 'token',
			'id'    => 'token',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('token', $unidade->iugu_token)
		);
		$this->data['acc_id'] = array(
			'name'  => 'acc_id',
			'id'    => 'acc_id',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('acc_id', $unidade->iugu_acc_id)
		);
		$array_receb = array();
		foreach($this->unidades_model->get_recebimentos($id)->result() as $receb) {
			$array_receb[$receb->CODIGO] = $receb->NOME;
		}
		$this->data['receb_boleto'] = array(
            'id'=>'receb_boleto',
            'name'=>'receb_boleto[]',
            'class'=>'multipleSelect', 
            'multiple'=>'multiple', 
            'options' => $array_receb, 
            'selected'=> ($this->input->post('receb_boleto')) ? $this->input->post('receb_boleto') : @unserialize($unidade->iugu_receb_boleto)
        );

		$this->data['receb_credito'] = array(
            'id'=>'receb_credito',
            'name'=>'receb_credito[]',
            'class'=>'multipleSelect', 
            'multiple'=>'multiple', 
            'options' => $array_receb, 
            'selected'=> ($this->input->post('receb_credito')) ? $this->input->post('receb_credito') : @unserialize($unidade->iugu_receb_credito)
        );

        /* Load Template */
        $this->template->admin_render('admin/unidades/iugu', $this->data);
	}
}
