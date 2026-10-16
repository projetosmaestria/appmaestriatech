<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_eventos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/app_eventos');
        $this->load->model('app_eventos_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_app_eventos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_app_eventos'), 'app_eventos');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_eventos", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['app_eventos'] = $this->app_eventos_model->get()->result();
            $minhas_unids = $this->app_eventos_model->get_unidades()->result();
            $minhas_unids_array = [];
            foreach($minhas_unids as $uni) {
            	$minhas_unids_array[] = $uni->id;
            }
           	
            foreach($this->data['app_eventos'] as $key => $event) {
            	$unids = unserialize($event->unidades);
            	foreach($unids as $u) {
            		if(!in_array($u, $minhas_unids_array)) {
            			unset($this->data['app_eventos'][$key]);
            		}
            	}
            }
           	//exit("<pre>".print_r($this->data['app_eventos'], true)."</pre>");
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/app/eventos/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_eventos", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_eventos_create'), 'app_eventos/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('texto', 'lang:validation_texto_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');
		$this->form_validation->set_rules('data', 'lang:validation_data_label', 'required|validar_data');
		$this->form_validation->set_rules('unidades[]', 'lang:validation_unidades_label', 'required');

		//exit("<pre>".print_r($_POST,true)."</pre>");

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"titulo" => $this->input->post("titulo"),
				"texto" => $this->input->post("texto"),
				"data" => ($this->input->post("data") != "") ? convertDateBRToEU($this->input->post("data")) : null,
				"actived" => $this->input->post("status"),
				"unidades" => serialize($this->input->post("unidades"))
			);

			if($id = $this->app_eventos_model->create($dados)) {
				$this->session->set_flashdata('message', show_alert_success($this->app_eventos_model->messages()));
				if($this->input->post('enviar') == 1) {
					$this->notification($id);
				}
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->app_eventos_model->messages()));
			redirect('app_eventos', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_eventos_model->errors() ? show_alert_error($this->app_eventos_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
            $this->data['data'] = array(
				'name'  => 'data',
				'id'    => 'data',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->input->post('data')
			);
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo'),
				'maxlength' => '100'
			);
			$this->data['texto'] = array(
				'name'  => 'texto',
				'id'    => 'texto',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('texto'),
				'maxlength' => '3000'
			);
			$unidades = $this->app_eventos_model->get_unidades()->result();
			$unidades_array = array();
			foreach ($unidades as $value) {
				$unidades_array[$value->id] = $value->unidade;
			}
            $this->data['unidades'] = array(
                'name'  => 'unidades[]',
                'id'    => 'unidades',
                'class' => 'multipleSelect',
                'multiple' => 'multiple',
                'options' => $unidades_array,
                'selected' => (count($this->input->post('unidades')) > 0) ? $this->input->post('unidades') : ''
            );
            $this->data['enviar'] = array(
				'name'  => 'enviar',
				'id'    => 'enviar',
				'type'  => 'checkbox',
                'class' => 'checkbox',
				'value' => 1,
				'checked'  => false
			);

            /* Load Template */
            $this->template->admin_render('admin/app/eventos/create', $this->data);
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
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_eventos", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_eventos_edit'), 'app_eventos/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $app_evento = $this->app_eventos_model->get($id)->row();

		/* Validate form input */
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');
		$this->form_validation->set_rules('texto', 'lang:validation_texto_label', 'trim|required');
		$this->form_validation->set_rules('data', 'lang:validation_data_label', 'required|validar_data');
		$this->form_validation->set_rules('unidades[]', 'lang:validation_unidades_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"titulo" => $this->input->post("titulo"),
				"texto" => $this->input->post("texto"),
				"data" => ($this->input->post("data") != "") ? convertDateBRToEU($this->input->post("data")) : null,
				"actived" => $this->input->post("status"),
				"unidades" => serialize($this->input->post("unidades"))
			);

			if($id = $this->app_eventos_model->update($id, $dados))
				$this->session->set_flashdata('message', show_alert_success($this->app_eventos_model->messages()));
			else
				$this->session->set_flashdata('message', show_alert_error($this->app_eventos_model->messages()));
			redirect('app_eventos', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_eventos_model->errors() ? show_alert_error($this->app_eventos_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status', $app_evento->actived);
			$this->data['data'] = array(
				'name'  => 'data',
				'id'    => 'data',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('data', convertDateEUToBR($app_evento->data))
			);
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'titulo',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo', $app_evento->titulo),
				'maxlength' => '100'
			);
			$this->data['texto'] = array(
				'name'  => 'texto',
				'id'    => 'texto',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('texto', $app_evento->texto),
				'maxlength' => '3000'
			);
			$unidades = $this->app_eventos_model->get_unidades()->result();
			$unidades_array = array();
			foreach ($unidades as $value) {
				$unidades_array[$value->id] = $value->unidade;
			}
            $this->data['unidades'] = array(
                'name'  => 'unidades[]',
                'id'    => 'unidades',
                'class' => 'multipleSelect',
                'multiple' => 'multiple',
                'options' => $unidades_array,
                'selected' => (count($this->input->post('unidades')) > 0) ? $this->input->post('unidades') : unserialize($app_evento->unidades)
            );

            /* Load Template */
            $this->template->admin_render('admin/app/eventos/edit', $this->data);
		}
	}

	function activate($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_eventos", "activate"))
		{
			redirect('auth', 'refresh');
		}
        $id = (int) $id;
		$activation = $this->app_eventos_model->activate($id);
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_eventos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_eventos_model->messages()));
		redirect('app_eventos', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_eventos", "deactivate"))
		{
			redirect('auth', 'refresh');
		} 
		$id = (int) $id;
		$deactivation = $this->app_eventos_model->deactivate($id); 
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_eventos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_eventos_model->messages()));
		redirect('app_eventos', 'refresh');
	}

	public function notification($id) {
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_eventos", "notification"))
		{
			redirect('auth', 'refresh');
		} 
        $app_evento = $this->app_eventos_model->get($id)->row();
        
        if($this->app_eventos_model->send_push($app_evento)) {
        	$this->app_eventos_model->update($id, array("notificado"=>"Y", "data_notificacao"=>date("Y-m-d H:i:s")));
        	$this->session->set_flashdata('message', show_alert_success($this->app_eventos_model->messages()));        	
        }
        redirect('app_eventos', 'refresh');
	}
}
