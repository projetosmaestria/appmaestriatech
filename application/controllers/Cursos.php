<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cursos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/cursos');
        $this->load->model('cursos_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_cursos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_cursos'), 'cursos');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("cursos", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['cursos'] = $this->cursos_model->get()->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/pedagogico/cursos/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("cursos", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_cursos_create'), 'cursos/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('descricao', 'lang:validation_descricao_label', 'trim|required');
		$this->form_validation->set_rules('categoria', 'lang:validation_categoria_label', 'required');
		$this->form_validation->set_rules('sigla', 'lang:validation_sigla_label', 'trim|required');
		$this->form_validation->set_rules('idade_minima', 'lang:validation_idade_minima_label', 'trim|required|numeric');
		$this->form_validation->set_rules('aula_matricula', 'lang:validation_aula_matricula_label', 'trim|required|numeric');
		$this->form_validation->set_rules('ativo', 'lang:validation_ativo_label', 'required');
		$this->form_validation->set_rules('tipo_avaliacao', 'lang:validation_tipo_avaliacao_label', 'required');
		$this->form_validation->set_rules('tipo', 'lang:validation_tipo_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			//exit("<pre>".print_r($_POST,true)."</pre>");
			$dados = array(
				"DESCRICAO" => $this->input->post("descricao"),
				"empresa_id" => $this->session->userdata("empresa_id"),
				"OBS" => $this->input->post("obs"),
				"LIG_ID_CATEGORIA" => $this->input->post("categoria"),
				"SIGLA" => $this->input->post("sigla"),
				"IDADE_MINIMA" => $this->input->post("idade_minima"),
				"AULA_MATRICULA" => $this->input->post("aula_matricula"),
				"DATA_CADASTRO" => date('Y-m-d'),
				"TIPO_AVALIACAO" => $this->input->post("tipo_avaliacao"),
				"FORMATO" => "MODULAR",
				"ATIVO" => $this->input->post("ativo"),
				"tipo" => $this->input->post("tipo")
			);

			if($id = $this->cursos_model->create($dados)){
				$this->session->set_flashdata('message', show_alert_success($this->cursos_model->messages()));
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->cursos_model->messages()));
			redirect('cursos', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->cursos_model->errors() ? show_alert_error($this->cursos_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['ativo'] = $this->form_validation->set_value('ativo');
			$this->data['idade_minima'] = array(
				'name'  => 'idade_minima',
				'id'    => 'idade_minima',
				'type'  => 'number',
                'class' => 'form-control',
				'value' => ($this->input->post('idade_minima')) ? $this->input->post('idade_minima') : 0
			);
			$this->data['aula_matricula'] = array(
				'name'  => 'aula_matricula',
				'id'    => 'aula_matricula',
				'type'  => 'number',
                'class' => 'form-control',
				'value' => ($this->input->post('aula_matricula')) ? $this->input->post('aula_matricula') : 0
			);
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);
			$this->data['sigla'] = array(
				'name'  => 'sigla',
				'id'    => 'sigla',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('sigla')
			);
			$this->data['obs'] = array(
				'name'  => 'obs',
				'id'    => 'obs',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->input->post('obs')
			);
            $status = $this->cursos_model->get_status()->result();
			$status_array = array(""=>"Selecione");
			foreach ($status as $s) {
				$status_array[$s->id] = $s->descricao;
			}
            $this->data['categoria'] = array(
                'name'  => 'categoria',
                'id'    => 'categoria',
                'class' => 'singleInputDynamic',
                'options' => $status_array,
                'selected' => (count($this->input->post('categoria')) > 0) ? $this->input->post('categoria') : ''
            );
            $this->data['tipo_avaliacao'] = array(
                'name'  => 'tipo_avaliacao',
                'id'    => 'tipo_avaliacao',
                'class' => 'form-control',
                'options' => array("" => "Selecione", "POR DISCIPLINA" => "POR DISCIPLINA", "POR PACOTE" => "POR PACOTE"),
                'selected' => (count($this->input->post('tipo_avaliacao')) > 0) ? $this->input->post('tipo_avaliacao') : ''
            );
            $this->data['tipo'] = array(
                'name'  => 'tipo',
                'id'    => 'tipo',
                'class' => 'form-control',
                'options' => array("" => "Selecione", "1" => "Normal", "2" => "EAD"),
                'selected' => (count($this->input->post('tipo')) > 0) ? $this->input->post('tipo') : ''
            );

            /* Load Template */
            $this->template->admin_render('admin/pedagogico/cursos/create', $this->data);
		}
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("niveis", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/niveis/delete');
        }
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("cursos", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_cursos_edit'), 'cursos/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $curso = $this->cursos_model->get($id)->row();
        //exit("<pre>".print_r($app_turma,true)."</pre>");
		/* Validate form input */
		
		$this->form_validation->set_rules('descricao', 'lang:validation_descricao_label', 'trim|required');
		$this->form_validation->set_rules('categoria', 'lang:validation_categoria_label', 'required');
		$this->form_validation->set_rules('sigla', 'lang:validation_sigla_label', 'trim|required');
		$this->form_validation->set_rules('idade_minima', 'lang:validation_idade_minima_label', 'trim|required|numeric');
		$this->form_validation->set_rules('aula_matricula', 'lang:validation_aula_matricula_label', 'trim|required|numeric');
		$this->form_validation->set_rules('ativo', 'lang:validation_ativo_label', 'required');
		$this->form_validation->set_rules('tipo_avaliacao', 'lang:validation_tipo_avaliacao_label', 'required');
		$this->form_validation->set_rules('tipo', 'lang:validation_tipo_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"DESCRICAO" => $this->input->post("descricao"),
				"OBS" => $this->input->post("obs"),
				"LIG_ID_CATEGORIA" => $this->input->post("categoria"),
				"SIGLA" => $this->input->post("sigla"),
				"IDADE_MINIMA" => $this->input->post("idade_minima"),
				"AULA_MATRICULA" => $this->input->post("aula_matricula"),
				"DATA_ALTERACAO" => date('Y-m-d'),
				"TIPO_AVALIACAO" => $this->input->post("tipo_avaliacao"),
				"ATIVO" => $this->input->post("ativo"),
				"tipo" => $this->input->post("tipo")
			);

			if($this->cursos_model->update($id, $dados)) {
				$this->session->set_flashdata('message', show_alert_success($this->cursos_model->messages()));
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->cursos_model->messages()));
			redirect('cursos', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->cursos_model->errors() ? show_alert_error($this->cursos_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['ativo'] = $this->form_validation->set_value('ativo', $curso->ATIVO);
            $this->data['idade_minima'] = array(
				'name'  => 'idade_minima',
				'id'    => 'idade_minima',
				'type'  => 'number',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('ativo', $curso->IDADE_MINIMA)
			);
			$this->data['aula_matricula'] = array(
				'name'  => 'aula_matricula',
				'id'    => 'aula_matricula',
				'type'  => 'number',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('ativo', $curso->AULA_MATRICULA)
			);
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao', $curso->DESCRICAO)
			);
			$this->data['sigla'] = array(
				'name'  => 'sigla',
				'id'    => 'sigla',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('sigla', $curso->SIGLA)
			);
			$this->data['obs'] = array(
				'name'  => 'obs',
				'id'    => 'obs',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('obs')) ? $this->input->post('obs') : $curso->OBS
			);
            $status = $this->cursos_model->get_status()->result();
			$status_array = array(""=>"Selecione");
			foreach ($status as $s) {
				$status_array[$s->id] = $s->descricao;
			}
            $this->data['categoria'] = array(
                'name'  => 'categoria',
                'id'    => 'categoria',
                'class' => 'singleInputDynamic',
                'options' => $status_array,
                'selected' => $this->form_validation->set_value('categoria', $curso->LIG_ID_CATEGORIA)
            );
            $this->data['tipo_avaliacao'] = array(
                'name'  => 'tipo_avaliacao',
                'id'    => 'tipo_avaliacao',
                'class' => 'form-control',
                'options' => array("" => "Selecione", "POR DISCIPLINA" => "POR DISCIPLINA", "POR PACOTE" => "POR PACOTE"),
                'selected' => $this->form_validation->set_value('tipo_avaliacao', $curso->TIPO_AVALIACAO)
            );
            $this->data['tipo'] = array(
                'name'  => 'tipo',
                'id'    => 'tipo',
                'class' => 'form-control',
                'options' => array("" => "Selecione", "1" => "Normal", "2" => "EAD"),
                'selected' => $this->form_validation->set_value('tipo', $curso->tipo)
            );

            /* Load Template */
            $this->template->admin_render('admin/pedagogico/cursos/edit', $this->data);
		}
	}

    function activate($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("cursos", "activate"))
		{
			redirect('auth', 'refresh');
		}
        $id = (int) $id;
		$activation = $this->cursos_model->activate($id);
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->cursos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->cursos_model->messages()));
		redirect('cursos', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("cursos", "deactivate"))
		{
			redirect('auth', 'refresh');
		} 
		$id = (int) $id;
		$deactivation = $this->cursos_model->deactivate($id); 
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->cursos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->cursos_model->messages()));
		redirect('cursos', 'refresh');
	}

}
