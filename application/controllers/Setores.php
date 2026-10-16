<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setores extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/setores');
        $this->load->model('setores_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_setores'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_setores'), 'setores');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("setores", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['setores'] = $this->setores_model->get(array('select'=>'setores.*', 'where'=>'empresa_id = "'.$this->session->userdata('empresa_id').'"' , 'order_by'=>array('setores.id', 'desc')))->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/setores/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("setores", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_setores_create'), 'setores/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('descricao', 'lang:setor_validation_descricao_label', 'trim|required');
		//$this->form_validation->set_rules('empresa', 'lang:setor_validation_empresa_label', 'required');
		$this->form_validation->set_rules('status', 'lang:setor_validation_status_label', 'required');

		$dados = array(
			"empresa_id" => $this->session->userdata("empresa_id"),
			"descricao" => $this->input->post("descricao"),
			"actived" => $this->input->post("status")
		);

		if ($this->form_validation->run() == TRUE && $id = $this->setores_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->setores_model->messages()));
			redirect('setores', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->setores_model->errors() ? show_alert_error($this->setores_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            /*$empresa_options = array(""=>"Selecione");
			foreach ($this->setores_model->get_empresas()->result() as $emp) {
				$empresa_options[$emp->id] = $emp->nome;
			}
			$this->data['empresa'] = array(
				'name'  => 'empresa',
				'id'    => 'empresa',
	            'class' => 'form-control',
	            'options' => $empresa_options,
				'selected' => $this->form_validation->set_value('empresa'),
			);*/
            $this->data['status'] = $this->form_validation->set_value('status');
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);

            /* Load Template */
            $this->template->admin_render('admin/setores/create', $this->data);
		}
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("setores", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/setores/delete');
        }
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("setores", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_setores_edit'), 'setores/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$setor = $this->setores_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		/* Validate form input */
        $this->form_validation->set_rules('descricao', $this->lang->line('setor_validation_descricao_label'), 'required');
		//$this->form_validation->set_rules('empresa', 'lang:setor_validation_empresa_label', 'required');
        $this->form_validation->set_rules('status', $this->lang->line('setor_validation_status_label'), 'required');

		if (isset($_POST) && ! empty($_POST))
		{	
			$dados = array(
				"empresa_id" => $this->session->userdata("empresa_id"),
				"descricao" => $this->input->post("descricao"),
				"actived" => $this->input->post("status")
			);
			if ($this->form_validation->run() == TRUE)
			{
				$setor_update = $this->setores_model->update($id, $dados);

				if ($setor_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_setor_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->setores_model->errors()));
				}

				redirect('setores', 'refresh');
			} else {
        		$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->setores_model->errors() ? show_alert_error($this->setores_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['setor']   = $setor;

        /*$empresa_options = array(""=>"Selecione");
		foreach ($this->setores_model->get_empresas()->result() as $emp) {
			$empresa_options[$emp->id] = $emp->nome;
		}
		$this->data['empresa'] = array(
			'name'  => 'empresa',
			'id'    => 'empresa',
            'class' => 'form-control',
            'options' => $empresa_options,
			'selected' => $this->form_validation->set_value('empresa', $unidade->empresa_id),
		);*/
		$this->data['status'] = $this->form_validation->set_value('status', $setor->actived);
		$this->data['descricao'] = array(
			'type'    => 'text',
			'name'    => 'descricao',
			'id'      => 'descricao',
			'value'   => $this->form_validation->set_value('descricao', $setor->descricao),
            'class'   => 'form-control'
		);

        /* Load Template */
        $this->template->admin_render('admin/setores/edit', $this->data);
	}

	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("setores", "activate"))
		{
			$activation = $this->setores_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->setores_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->setores_model->messages()));
		redirect('setores', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("setores", "deactivate"))
		{
			$deactivation = $this->setores_model->deactivate($id);
		}
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->setores_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->setores_model->messages()));
		redirect('setores', 'refresh');
	}
}
