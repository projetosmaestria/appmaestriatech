<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_historicos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/users_historicos');
        $this->load->model('users_historicos_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_users_historico'), 'users_historicos/status');
    }


	public function status()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("users_historicos", "status"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
	        /* Title Page :: Common */
	        $this->page_title->push(lang('menu_users_historicos_status'));
	        $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs */
        	$this->breadcrumbs->unshift(2, lang('menu_users_historicos_status'), 'users_historicos/status');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['status'] = $this->users_historicos_model->get()->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/users/historicos/status', $this->data);
        }
	}

	public function status_create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("users_historicos", "status_create"))
		{
			redirect('auth', 'refresh');
		}

        $this->page_title->push(lang('menu_users_historicos_status'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_users_historicos_status_create'), 'users_historicos/status_create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('descricao', 'lang:create_validation_descricao_label', 'trim|required');

		$dados["status"] = $this->input->post('status');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["created_at"] = date('Y-m-d H:i:s');
		$dados["empresa_id"] = $this->session->userdata('empresa_id');

		if ($this->form_validation->run() == TRUE && $id = $this->users_historicos_model->status_create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->users_historicos_model->messages()));
			redirect('users_historicos/status', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->users_historicos_model->errors() ? show_alert_error($this->users_historicos_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);

            /* Load Template */
            $this->template->admin_render('admin/users/historicos/status_create', $this->data);
		}
	}

	public function status_edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("users_historicos", "status_edit"))
		{
			redirect('auth', 'refresh');
		}

        $this->page_title->push(lang('menu_users_historicos_status'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_users_historicos_status_edit'), 'users_historicos/status_edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
        $status = $this->users_historicos_model->get($id)->row();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('descricao', 'lang:create_validation_descricao_label', 'trim|required');	

		$dados["status"] = $this->input->post('status');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["updated_at"] = date('Y-m-d H:i:s');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$status_update = $this->users_historicos_model->status_update($id, $dados);

				if ($status_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->users_historicos_model->errors()));
				}

				redirect('users_historicos/status', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->users_historicos_model->errors() ? show_alert_error($this->users_historicos_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		$this->data['status'] = $this->form_validation->set_value('status', $status->status);
		$this->data['descricao'] = array(
			'name'  => 'descricao',
			'id'    => 'descricao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('descricao', $status->descricao)
		);

        /* Load Template */
        $this->template->admin_render('admin/users/historicos/status_edit', $this->data);
	}


	function status_activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("users_historicos", "status_activate"))
		{
			$activation = $this->users_historicos_model->status_activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->users_historicos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->users_historicos_model->messages()));
		redirect('users_historicos/status', 'refresh');
	}


	public function status_deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("users_historicos", "status_deactivate"))
		{
			$deactivation = $this->users_historicos_model->status_deactivate($id);
		}
		if(@$deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->users_historicos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error(lang('permission_denied')));
		redirect('users_historicos/status', 'refresh');
	}
	
}
