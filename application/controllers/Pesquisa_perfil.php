<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pesquisa_perfil extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/pesquisa_perfil');
        $this->load->model('pesquisa_perfil_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_pesquisa_perfil'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_pesquisa_perfil'), 'admin/pesquisa_perfil');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("pesquisa_perfil", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['pesquisas'] = $this->pesquisa_perfil_model->get()->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/pesquisa_perfil/index', $this->data);
        }
	}

	public function create($id = null)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("pesquisa_perfil", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_pesquisa_perfil_create'), 'pesquisa_perfil/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('contrato', 'lang:contrato', 'required');
		$this->form_validation->set_rules('nome', 'lang:nome', 'trim|required');
		$this->form_validation->set_rules('idade', 'lang:idade', 'required|is_numeric');
		$this->form_validation->set_rules('genero', 'lang:genero', 'required');
		$this->form_validation->set_rules('civil', 'lang:civil', 'required');
		$this->form_validation->set_rules('formacao', 'lang:formacao', 'required');
		$this->form_validation->set_rules('ocupacao', 'lang:ocupacao', 'trim|required');
		$this->form_validation->set_rules('rendimento', 'lang:rendimento', 'required');
		$this->form_validation->set_rules('celular', 'lang:celular', 'trim|required');
		$this->form_validation->set_rules('pq_escolheu_curso', 'lang:pq_escolheu_curso', 'required');
		$this->form_validation->set_rules('pq_escolheu_bsl', 'lang:pq_escolheu_bsl', 'required');
		$this->form_validation->set_rules('ac_tutoria', 'lang:ac_tutoria', 'required');
		$this->form_validation->set_rules('espera_tutoria', 'lang:espera_tutoria', 'required');
		$this->form_validation->set_rules('opcao_contato', 'lang:opcao_contato', 'required');
		$this->form_validation->set_rules('periodo_contato', 'lang:periodo_contato', 'required');
		$this->form_validation->set_rules('contato_sabado', 'lang:contato_sabado', 'required');
		$this->form_validation->set_rules('horario_acesso', 'lang:horario_acesso', 'required');
		$this->form_validation->set_rules('ja_cursou_ead', 'lang:ja_cursou_ead', 'required');
		$this->form_validation->set_rules('concluiu_ead', 'lang:concluiu_ead', 'required');
		$this->form_validation->set_rules('ja_fez_ingles', 'lang:ja_fez_ingles', 'required');
		$this->form_validation->set_rules('pq_influencia', 'lang:pq_influencia', 'required');
		$this->form_validation->set_rules('meta', 'lang:meta', 'required');
		$this->form_validation->set_rules('meta_porcentagem', 'lang:meta_porcentagem', 'required|is_numeric');

		$dados["orc_id"] = $this->input->post('contrato');
		$dados["nome"] = $this->input->post('nome');
		$dados["idade"] = $this->input->post('idade');
		$dados["genero"] = $this->input->post('genero');
		$dados["civil"] = ($this->input->post('civil') == "Outro") ? $this->input->post('civil_outro_desc') : $this->input->post('civil');
		$dados["formacao"] = $this->input->post('formacao');
		$dados["formado_em"] = $this->input->post('formado');
		$dados["ocupacao"] = $this->input->post('ocupacao');
		$dados["rendimento"] = $this->input->post('rendimento');
		$dados["telefone"] = $this->input->post('telefone');
		$dados["skype"] = $this->input->post('skype');
		$dados["celular"] = $this->input->post('celular');
		$dados["pq_escolheu_curso"] = $this->input->post('pq_escolheu_curso');
		$dados["pq_escolheu_bsl"] = ($this->input->post('pq_escolheu_bsl') == "Outro") ? $this->input->post('pq_escolheu_bsl_outro_desc') : $this->input->post('pq_escolheu_bsl');
		$dados["ac_tutoria"] = $this->input->post('ac_tutoria');
		$dados["espera_tutoria"] = ($this->input->post('espera_tutoria') == "Outro") ? $this->input->post('espera_tutoria_outro_desc') : $this->input->post('espera_tutoria');
		$dados["opcao_contato"] = ($this->input->post('opcao_contato') == "Outro") ? $this->input->post('opcao_contato_outro_desc') : $this->input->post('opcao_contato');
		$dados["periodo_contato"] = ($this->input->post('periodo_contato') == "Outro") ? $this->input->post('periodo_contato_outro_desc') : $this->input->post('periodo_contato');
		$dados["contato_sabado"] = $this->input->post('contato_sabado');
		$dados["horario_acesso"] = ($this->input->post('horario_acesso') == "Outro") ? $this->input->post('horario_acesso_outro_desc') : $this->input->post('horario_acesso');
		$dados["ja_cursou_ead"] = $this->input->post('ja_cursou_ead');
		$dados["ja_cursou_ead_txt"] = $this->input->post('ja_cursou_ead_txt');
		$dados["concluiu_ead"] = $this->input->post('concluiu_ead');
		$dados["concluiu_ead_txt"] = $this->input->post('concluiu_ead_txt');
		$dados["ja_fez_ingles"] = $this->input->post('ja_fez_ingles');
		$dados["nivel_ingles"] = ($this->input->post('nivel_ingles') == "Outro") ? $this->input->post('nivel_ingles_outro_desc') : $this->input->post('nivel_ingles');
		$dados["concluiu_ingles"] = $this->input->post('concluiu_ingles');
		$dados["concluiu_ingles_txt"] = $this->input->post('concluiu_ingles_txt');
		$dados["pq_influencia"] = ($this->input->post('pq_influencia') == "Outro") ? $this->input->post('pq_influencia_outro_desc') : $this->input->post('pq_influencia_outro_desc');
		$dados["meta"] = ($this->input->post('meta') == "Outro") ? $this->input->post('meta_outro_desc') : $this->input->post('meta_outro_desc');
		$dados["meta_porcentagem"] = $this->input->post('meta_porcentagem');

		if ($this->form_validation->run() == TRUE && $id = $this->pesquisa_perfil_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->pesquisa_perfil_model->messages()));
			redirect('pesquisa_perfil', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->pesquisa_perfil_model->errors() ? show_alert_error($this->pesquisa_perfil_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $orc_ids = $this->pesquisa_perfil_model->get_contratos_ids();
            $orc_ids_array = array();
            if($orc_ids->num_rows() > 0) {
            	foreach ($orc_ids->result() as $value) {
            		$orc_ids_array[] = $value->orc_id;
            	}
            }
            
            $contratos = $this->pesquisa_perfil_model->get_contratos(implode(",", $orc_ids_array));
            
            $contratos_array = array(""=>"Selecione");
            if($contratos->num_rows() > 0) {
            	foreach ($contratos->result() as $value) {
            		$contratos_array[$value->id] = "Cod Contrato: ".$value->id." - Cliente: ".$value->cliente;
            	}
            }
            $this->data['contrato'] = array(
				'name'  => 'contrato',
				'id'    => 'contrato',
				'type'  => 'text',
                'class' => 'form-control singleInputDynamic',
                'options' => $contratos_array,
				'selected' => $this->form_validation->set_value('contrato', $id)
			);
			$this->data['nome'] = array(
				'name'  => 'nome',
				'id'    => 'nome',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('nome')
			);
			$this->data['idade'] = array(
				'name'  => 'idade',
				'id'    => 'idade',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('idade')
			);
			$this->data['formado'] = array(
				'name'  => 'formado',
				'id'    => 'formado',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('formado')
			);
			$this->data['ocupacao'] = array(
				'name'  => 'ocupacao',
				'id'    => 'ocupacao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('ocupacao')
			);
			$this->data['telefone'] = array(
				'name'  => 'telefone',
				'id'    => 'telefone',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('telefone')
			);
			$this->data['skype'] = array(
				'name'  => 'skype',
				'id'    => 'skype',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('skype')
			);
			$this->data['celular'] = array(
				'name'  => 'celular',
				'id'    => 'celular',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('celular')
			);
			$this->data['ja_cursou_ead_txt'] = array(
				'name'  => 'ja_cursou_ead_txt',
				'id'    => 'ja_cursou_ead_txt',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->input->post('ja_cursou_ead_txt'),
				'maxlength' => '3000'
			);
			$this->data['concluiu_ead_txt'] = array(
				'name'  => 'concluiu_ead_txt',
				'id'    => 'concluiu_ead_txt',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->input->post('concluiu_ead_txt'),
				'maxlength' => '3000'
			);
			$this->data['concluiu_ingles_txt'] = array(
				'name'  => 'concluiu_ingles_txt',
				'id'    => 'concluiu_ingles_txt',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->input->post('concluiu_ingles_txt'),
				'maxlength' => '3000'
			);
			$options_meta = array();
	        $i = 0;
	        while($i <= 100) {
	            $options_meta[$i] = $i;
	            $i = $i + 10;
	        }
			$this->data['meta_porcentagem'] = array(
				'name'  => 'meta_porcentagem',
				'id'    => 'meta_porcentagem',
                'class' => 'form-control',
                'options' => $options_meta,
				'selected' => $this->input->post('meta_porcentagem')
			);
			$this->data['genero'] = $this->form_validation->set_value('genero');
			$this->data['civil'] = $this->form_validation->set_value('civil');
			if($this->input->post('civil') == "Outro") {
				$this->data['civil_outro_desc'] = $this->input->post('civil_outro_desc');
			}
			$this->data['formacao'] = $this->form_validation->set_value('formacao');
			$this->data['rendimento'] = $this->form_validation->set_value('rendimento');
			$this->data['pq_escolheu_curso'] = $this->form_validation->set_value('pq_escolheu_curso');
			$this->data['pq_escolheu_bsl'] = $this->form_validation->set_value('pq_escolheu_bsl');
			if($this->input->post('pq_escolheu_bsl') == "Outro") {
				$this->data['pq_escolheu_bsl_outro_desc'] = $this->input->post('pq_escolheu_bsl_outro_desc');
			}
			$this->data['ac_tutoria'] = $this->form_validation->set_value('ac_tutoria');
			$this->data['espera_tutoria'] = $this->form_validation->set_value('espera_tutoria');
			if($this->input->post('espera_tutoria') == "Outro") {
				$this->data['espera_tutoria_outro_desc'] = $this->input->post('espera_tutoria_outro_desc');
			}
			$this->data['opcao_contato'] = $this->form_validation->set_value('opcao_contato');
			if($this->input->post('opcao_contato') == "Outro") {
				$this->data['opcao_contato_outro_desc'] = $this->input->post('opcao_contato_outro_desc');
			}
			$this->data['periodo_contato'] = $this->form_validation->set_value('periodo_contato');
			if($this->input->post('periodo_contato') == "Outro") {
				$this->data['periodo_contato_outro_desc'] = $this->input->post('periodo_contato_outro_desc');
			}
			$this->data['contato_sabado'] = $this->form_validation->set_value('contato_sabado');
			$this->data['horario_acesso'] = $this->form_validation->set_value('horario_acesso');
			if($this->input->post('horario_acesso') == "Outro") {
				$this->data['horario_acesso_outro_desc'] = $this->input->post('horario_acesso_outro_desc');
			}			
			$this->data['ja_cursou_ead'] = $this->form_validation->set_value('ja_cursou_ead');
			$this->data['concluiu_ead'] = $this->form_validation->set_value('concluiu_ead');
			$this->data['ja_fez_ingles'] = $this->form_validation->set_value('ja_fez_ingles');
			$this->data['nivel_ingles'] = $this->input->post('nivel_ingles');
			if($this->input->post('nivel_ingles') == "Outro") {
				$this->data['nivel_ingles_outro_desc'] = $this->input->post('nivel_ingles_outro_desc');
			}	
			$this->data['concluiu_ingles'] = $this->input->post('concluiu_ingles');
			$this->data['pq_influencia'] = $this->form_validation->set_value('pq_influencia');
			if($this->input->post('pq_influencia') == "Outro") {
				$this->data['pq_influencia_outro_desc'] = $this->input->post('pq_influencia_outro_desc');
			}	
			$this->data['meta'] = $this->form_validation->set_value('meta');
			if($this->input->post('meta') == "Outro") {
				$this->data['meta_outro_desc'] = $this->input->post('meta_outro_desc');
			}	

            /* Load Template */
            $this->template->admin_render('admin/pesquisa_perfil/create', $this->data);
		}
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("midias", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/midias/delete');
        }
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("pesquisa_perfil", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_pesquisa_perfil_edit'), 'pesquisa_perfil/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$pesquisa_perfil = $this->pesquisa_perfil_model->get($id)->row();

		/* Validate form input */
		$this->form_validation->set_rules('nome', 'lang:nome', 'trim|required');
		$this->form_validation->set_rules('idade', 'lang:idade', 'required|is_numeric');
		$this->form_validation->set_rules('genero', 'lang:genero', 'required');
		$this->form_validation->set_rules('civil', 'lang:civil', 'required');
		$this->form_validation->set_rules('formacao', 'lang:formacao', 'required');
		$this->form_validation->set_rules('ocupacao', 'lang:ocupacao', 'trim|required');
		$this->form_validation->set_rules('rendimento', 'lang:rendimento', 'required');
		$this->form_validation->set_rules('celular', 'lang:celular', 'trim|required');
		$this->form_validation->set_rules('pq_escolheu_curso', 'lang:pq_escolheu_curso', 'required');
		$this->form_validation->set_rules('pq_escolheu_bsl', 'lang:pq_escolheu_bsl', 'required');
		$this->form_validation->set_rules('ac_tutoria', 'lang:ac_tutoria', 'required');
		$this->form_validation->set_rules('espera_tutoria', 'lang:espera_tutoria', 'required');
		$this->form_validation->set_rules('opcao_contato', 'lang:opcao_contato', 'required');
		$this->form_validation->set_rules('periodo_contato', 'lang:periodo_contato', 'required');
		$this->form_validation->set_rules('contato_sabado', 'lang:contato_sabado', 'required');
		$this->form_validation->set_rules('horario_acesso', 'lang:horario_acesso', 'required');
		$this->form_validation->set_rules('ja_cursou_ead', 'lang:ja_cursou_ead', 'required');
		$this->form_validation->set_rules('concluiu_ead', 'lang:concluiu_ead', 'required');
		$this->form_validation->set_rules('ja_fez_ingles', 'lang:ja_fez_ingles', 'required');
		$this->form_validation->set_rules('pq_influencia', 'lang:pq_influencia', 'required');
		$this->form_validation->set_rules('meta', 'lang:meta', 'required');
		$this->form_validation->set_rules('meta_porcentagem', 'lang:meta_porcentagem', 'required|is_numeric');

		//$dados["orc_id"] = $this->input->post('contrato');
		$dados["nome"] = $this->input->post('nome');
		$dados["idade"] = $this->input->post('idade');
		$dados["genero"] = $this->input->post('genero');
		$dados["civil"] = ($this->input->post('civil') == "Outro") ? $this->input->post('civil_outro_desc') : $this->input->post('civil');
		$dados["formacao"] = $this->input->post('formacao');
		$dados["formado_em"] = $this->input->post('formado');
		$dados["ocupacao"] = $this->input->post('ocupacao');
		$dados["rendimento"] = $this->input->post('rendimento');
		$dados["telefone"] = $this->input->post('telefone');
		$dados["skype"] = $this->input->post('skype');
		$dados["celular"] = $this->input->post('celular');
		$dados["pq_escolheu_curso"] = $this->input->post('pq_escolheu_curso');
		$dados["pq_escolheu_bsl"] = ($this->input->post('pq_escolheu_bsl') == "Outro") ? $this->input->post('pq_escolheu_bsl_outro_desc') : $this->input->post('pq_escolheu_bsl');
		$dados["ac_tutoria"] = $this->input->post('ac_tutoria');
		$dados["espera_tutoria"] = ($this->input->post('espera_tutoria') == "Outro") ? $this->input->post('espera_tutoria_outro_desc') : $this->input->post('espera_tutoria');
		$dados["opcao_contato"] = ($this->input->post('opcao_contato') == "Outro") ? $this->input->post('opcao_contato_outro_desc') : $this->input->post('opcao_contato');
		$dados["periodo_contato"] = ($this->input->post('periodo_contato') == "Outro") ? $this->input->post('periodo_contato_outro_desc') : $this->input->post('periodo_contato');
		$dados["contato_sabado"] = $this->input->post('contato_sabado');
		$dados["horario_acesso"] = ($this->input->post('horario_acesso') == "Outro") ? $this->input->post('horario_acesso_outro_desc') : $this->input->post('horario_acesso');
		$dados["ja_cursou_ead"] = $this->input->post('ja_cursou_ead');
		$dados["ja_cursou_ead_txt"] = $this->input->post('ja_cursou_ead_txt');
		$dados["concluiu_ead"] = $this->input->post('concluiu_ead');
		$dados["concluiu_ead_txt"] = $this->input->post('concluiu_ead_txt');
		$dados["ja_fez_ingles"] = $this->input->post('ja_fez_ingles');
		$dados["nivel_ingles"] = ($this->input->post('nivel_ingles') == "Outro") ? $this->input->post('nivel_ingles_outro_desc') : $this->input->post('nivel_ingles');
		$dados["concluiu_ingles"] = $this->input->post('concluiu_ingles');
		$dados["concluiu_ingles_txt"] = $this->input->post('concluiu_ingles_txt');
		$dados["pq_influencia"] = ($this->input->post('pq_influencia') == "Outro") ? $this->input->post('pq_influencia_outro_desc') : $this->input->post('pq_influencia');
		$dados["meta"] = ($this->input->post('meta') == "Outro") ? $this->input->post('meta_outro_desc') : $this->input->post('meta');
		$dados["meta_porcentagem"] = $this->input->post('meta_porcentagem');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$pesquisa_update = $this->pesquisa_perfil_model->update($id, $dados);

				if ($pesquisa_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_pesquisa_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->pesquisa_perfil_model->errors()));
				}

				redirect('pesquisa_perfil', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->pesquisa_perfil_model->errors() ? show_alert_error($this->pesquisa_perfil_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['pesquisa']   = $pesquisa_perfil;

		$this->data['nome'] = array(
			'name'  => 'nome',
			'id'    => 'nome',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('nome', $pesquisa_perfil->nome)
		);
		$this->data['idade'] = array(
			'name'  => 'idade',
			'id'    => 'idade',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('idade', $pesquisa_perfil->idade)
		);
		$this->data['formado'] = array(
			'name'  => 'formado',
			'id'    => 'formado',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('formado', $pesquisa_perfil->formado_em)
		);
		$this->data['ocupacao'] = array(
			'name'  => 'ocupacao',
			'id'    => 'ocupacao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('ocupacao', $pesquisa_perfil->ocupacao)
		);
		$this->data['telefone'] = array(
			'name'  => 'telefone',
			'id'    => 'telefone',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('telefone', $pesquisa_perfil->telefone)
		);
		$this->data['skype'] = array(
			'name'  => 'skype',
			'id'    => 'skype',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('skype', $pesquisa_perfil->skype)
		);
		$this->data['celular'] = array(
			'name'  => 'celular',
			'id'    => 'celular',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('celular', $pesquisa_perfil->celular)
		);
		$this->data['ja_cursou_ead_txt'] = array(
			'name'  => 'ja_cursou_ead_txt',
			'id'    => 'ja_cursou_ead_txt',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => ($this->input->post('ja_cursou_ead_txt'))?$this->input->post('ja_cursou_ead_txt'):$pesquisa_perfil->ja_cursou_ead_txt,
			'maxlength' => '3000'
		);
		$this->data['concluiu_ead_txt'] = array(
			'name'  => 'concluiu_ead_txt',
			'id'    => 'concluiu_ead_txt',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => ($this->input->post('concluiu_ead_txt'))?$this->input->post('concluiu_ead_txt'):$pesquisa_perfil->concluiu_ead_txt,
			'maxlength' => '3000'
		);
		$this->data['concluiu_ingles_txt'] = array(
			'name'  => 'concluiu_ingles_txt',
			'id'    => 'concluiu_ingles_txt',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => ($this->input->post('concluiu_ingles_txt'))?$this->input->post('concluiu_ingles_txt'):$pesquisa_perfil->concluiu_ingles_txt,
			'maxlength' => '3000'
		);
		$options_meta = array();
        $i = 0;
        while($i <= 100) {
            $options_meta[$i] = $i;
            $i = $i + 10;
        }
		$this->data['meta_porcentagem'] = array(
			'name'  => 'meta_porcentagem',
			'id'    => 'meta_porcentagem',
            'class' => 'form-control',
            'options' => $options_meta,
			'selected' => $this->form_validation->set_value('meta_porcentagem', $pesquisa_perfil->meta_porcentagem)
		);
		$this->data['genero'] = $this->form_validation->set_value('genero', $pesquisa_perfil->genero);
		$this->data['civil'] = $this->form_validation->set_value('civil', $pesquisa_perfil->civil);
		if($this->input->post('civil') == "Outro") {
			$this->data['civil_outro_desc'] = ($this->input->post('civil_outro_desc'))?$this->input->post('civil_outro_desc'):$pesquisa_perfil->civil;
		}
		$this->data['formacao'] = $this->form_validation->set_value('formacao', $pesquisa_perfil->formacao);
		$this->data['rendimento'] = $this->form_validation->set_value('rendimento', $pesquisa_perfil->rendimento);
		$this->data['pq_escolheu_curso'] = $this->form_validation->set_value('pq_escolheu_curso', $pesquisa_perfil->pq_escolheu_curso);
		$this->data['pq_escolheu_bsl'] = $this->form_validation->set_value('pq_escolheu_bsl', $pesquisa_perfil->pq_escolheu_bsl);
		if($this->input->post('pq_escolheu_bsl') == "Outro") {
			$this->data['pq_escolheu_bsl_outro_desc'] = ($this->input->post('pq_escolheu_bsl_outro_desc'))?$this->input->post('pq_escolheu_bsl_outro_desc'):$pesquisa_perfil->pq_escolheu_bsl;
		}
		$this->data['ac_tutoria'] = $this->form_validation->set_value('ac_tutoria', $pesquisa_perfil->ac_tutoria);
		$this->data['espera_tutoria'] = $this->form_validation->set_value('espera_tutoria', $pesquisa_perfil->espera_tutoria);
		if($this->input->post('espera_tutoria') == "Outro") {
			$this->data['espera_tutoria_outro_desc'] = ($this->input->post('espera_tutoria_outro_desc'))?$this->input->post('espera_tutoria_outro_desc'):$pesquisa_perfil->espera_tutoria;
		}
		$this->data['opcao_contato'] = $this->form_validation->set_value('opcao_contato', $pesquisa_perfil->opcao_contato);
		if($this->input->post('opcao_contato') == "Outro") {
			$this->data['opcao_contato_outro_desc'] = ($this->input->post('opcao_contato_outro_desc'))?$this->input->post('opcao_contato_outro_desc'):$pesquisa_perfil->opcao_contato;
		}
		$this->data['periodo_contato'] = $this->form_validation->set_value('periodo_contato', $pesquisa_perfil->periodo_contato);
		if($this->input->post('periodo_contato') == "Outro") {
			$this->data['periodo_contato_outro_desc'] = ($this->input->post('periodo_contato_outro_desc'))?$this->input->post('periodo_contato_outro_desc'):$pesquisa_perfil->periodo_contato;
		}
		$this->data['contato_sabado'] = $this->form_validation->set_value('contato_sabado', $pesquisa_perfil->contato_sabado);
		$this->data['horario_acesso'] = $this->form_validation->set_value('horario_acesso', $pesquisa_perfil->horario_acesso);
		if($this->input->post('horario_acesso') == "Outro") {
			$this->data['horario_acesso_outro_desc'] = ($this->input->post('horario_acesso_outro_desc'))?$this->input->post('horario_acesso_outro_desc'):$pesquisa_perfil->horario_acesso;
		}			
		$this->data['ja_cursou_ead'] = $this->form_validation->set_value('ja_cursou_ead', $pesquisa_perfil->ja_cursou_ead);
		$this->data['concluiu_ead'] = $this->form_validation->set_value('concluiu_ead', $pesquisa_perfil->concluiu_ead);
		$this->data['ja_fez_ingles'] = $this->form_validation->set_value('ja_fez_ingles', $pesquisa_perfil->ja_fez_ingles);
		$this->data['nivel_ingles'] = ($this->input->post('nivel_ingles'))?$this->input->post('nivel_ingles'):$pesquisa_perfil->nivel_ingles;
		if($this->input->post('nivel_ingles') == "Outro") {
			$this->data['nivel_ingles_outro_desc'] = ($this->input->post('nivel_ingles_outro_desc'))?$this->input->post('nivel_ingles_outro_desc'):$pesquisa_perfil->nivel_ingles;
		}	
		$this->data['concluiu_ingles'] = ($this->input->post('concluiu_ingles'))?$this->input->post('concluiu_ingles'):$pesquisa_perfil->concluiu_ingles;
		$this->data['pq_influencia'] = $this->form_validation->set_value('pq_influencia', $pesquisa_perfil->pq_influencia);
		if($this->input->post('pq_influencia') == "Outro") {
			$this->data['pq_influencia_outro_desc'] = ($this->input->post('pq_influencia_outro_desc'))?$this->input->post('pq_influencia_outro_desc'):$pesquisa_perfil->pq_influencia;
		}	
		$this->data['meta'] = $this->form_validation->set_value('meta', $pesquisa_perfil->meta);
		/*if($this->input->post('meta') == "Outro") {
			$this->data['meta_outro_desc'] = ($this->input->post('meta_outro_desc'))?$this->input->post('meta_outro_desc'):$pesquisa_perfil->meta;
		}*/

        /* Load Template */
        $this->template->admin_render('admin/pesquisa_perfil/edit', $this->data);
	}	

	public function view($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("pesquisa_perfil", "view"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_pesquisa_perfil_view'), 'pesquisa_perfil/view');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
        $pq = $this->pesquisa_perfil_model->get($id);
        if($pq->num_rows() == 0) {
        	$this->session->set_flashdata('message', show_alert_error("Pesquisa de perfil não existe!"));
        	redirect('pesquisa_perfil', 'refresh');
        }
		$pesquisa_perfil = $pq->row();

		$this->data['pesquisa']   = $pesquisa_perfil;

		$this->data['nome'] = array(
			'name'  => 'nome',
			'id'    => 'nome',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $pesquisa_perfil->nome
		);
		$this->data['idade'] = array(
			'name'  => 'idade',
			'id'    => 'idade',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $pesquisa_perfil->idade
		);
		$this->data['formado'] = array(
			'name'  => 'formado',
			'id'    => 'formado',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $pesquisa_perfil->formado_em
		);
		$this->data['ocupacao'] = array(
			'name'  => 'ocupacao',
			'id'    => 'ocupacao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $pesquisa_perfil->ocupacao
		);
		$this->data['telefone'] = array(
			'name'  => 'telefone',
			'id'    => 'telefone',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $pesquisa_perfil->telefone
		);
		$this->data['skype'] = array(
			'name'  => 'skype',
			'id'    => 'skype',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $pesquisa_perfil->skype
		);
		$this->data['celular'] = array(
			'name'  => 'celular',
			'id'    => 'celular',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $pesquisa_perfil->celular
		);
		$this->data['ja_cursou_ead_txt'] = array(
			'name'  => 'ja_cursou_ead_txt',
			'id'    => 'ja_cursou_ead_txt',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $pesquisa_perfil->ja_cursou_ead_txt,
			'maxlength' => '3000'
		);
		$this->data['concluiu_ead_txt'] = array(
			'name'  => 'concluiu_ead_txt',
			'id'    => 'concluiu_ead_txt',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $pesquisa_perfil->concluiu_ead_txt,
			'maxlength' => '3000'
		);
		$this->data['concluiu_ingles_txt'] = array(
			'name'  => 'concluiu_ingles_txt',
			'id'    => 'concluiu_ingles_txt',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $pesquisa_perfil->concluiu_ingles_txt,
			'maxlength' => '3000'
		);
		$options_meta = array();
        $i = 0;
        while($i <= 100) {
            $options_meta[$i] = $i;
            $i = $i + 10;
        }
		$this->data['meta_porcentagem'] = array(
			'name'  => 'meta_porcentagem',
			'id'    => 'meta_porcentagem',
            'class' => 'form-control',
            'options' => $options_meta,
			'selected' => $this->form_validation->set_value('meta_porcentagem', $pesquisa_perfil->meta_porcentagem)
		);
		$this->data['genero'] = $pesquisa_perfil->genero;
		$this->data['civil'] = $pesquisa_perfil->civil;
		$this->data['formacao'] = $pesquisa_perfil->formacao;
		$this->data['rendimento'] = $pesquisa_perfil->rendimento;
		$this->data['pq_escolheu_curso'] = $pesquisa_perfil->pq_escolheu_curso;
		$this->data['pq_escolheu_bsl'] = $pesquisa_perfil->pq_escolheu_bsl;
		if($this->input->post('pq_escolheu_bsl') == "Outro") {
			$this->data['pq_escolheu_bsl_outro_desc'] = $pesquisa_perfil->pq_escolheu_bsl;
		}
		$this->data['ac_tutoria'] = $pesquisa_perfil->ac_tutoria;
		$this->data['espera_tutoria'] = $pesquisa_perfil->espera_tutoria;
		if($this->input->post('espera_tutoria') == "Outro") {
			$this->data['espera_tutoria_outro_desc'] = $pesquisa_perfil->espera_tutoria;
		}
		$this->data['opcao_contato'] = $pesquisa_perfil->opcao_contato;
		if($this->input->post('opcao_contato') == "Outro") {
			$this->data['opcao_contato_outro_desc'] = $pesquisa_perfil->opcao_contato;
		}
		$this->data['periodo_contato'] = $pesquisa_perfil->periodo_contato;
		if($this->input->post('periodo_contato') == "Outro") {
			$this->data['periodo_contato_outro_desc'] = $pesquisa_perfil->periodo_contato;
		}
		$this->data['contato_sabado'] = $pesquisa_perfil->contato_sabado;
		$this->data['horario_acesso'] = $pesquisa_perfil->horario_acesso;
		if($this->input->post('horario_acesso') == "Outro") {
			$this->data['horario_acesso_outro_desc'] = $pesquisa_perfil->horario_acesso;
		}			
		$this->data['ja_cursou_ead'] = $pesquisa_perfil->ja_cursou_ead;
		$this->data['concluiu_ead'] = $pesquisa_perfil->concluiu_ead;
		$this->data['ja_fez_ingles'] = $pesquisa_perfil->ja_fez_ingles;
		$this->data['nivel_ingles'] = $pesquisa_perfil->nivel_ingles;
		if($this->input->post('nivel_ingles') == "Outro") {
			$this->data['nivel_ingles_outro_desc'] = $pesquisa_perfil->nivel_ingles;
		}	
		$this->data['concluiu_ingles'] = $pesquisa_perfil->concluiu_ingles;
		$this->data['pq_influencia'] = $pesquisa_perfil->pq_influencia;
		if($this->input->post('pq_influencia') == "Outro") {
			$this->data['pq_influencia_outro_desc'] = $pesquisa_perfil->pq_influencia_outro_desc;
		}	
		$this->data['meta'] = $pesquisa_perfil->meta;
		if($this->input->post('meta') == "Outro") {
			$this->data['meta_outro_desc'] = $pesquisa_perfil->meta;
		}

        /* Load Template */
        $this->template->admin_render('admin/pesquisa_perfil/view', $this->data);
	}	
}
