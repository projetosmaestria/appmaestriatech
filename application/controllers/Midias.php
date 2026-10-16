<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Midias extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/midias');
        $this->load->model('midias_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_midias'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_midias'), 'admin/midias');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("midias", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['midias'] = $this->midias_model->get(array('select'=>'midias.*, DATE_FORMAT(created_at, "%d/%m/%Y %H:%i:%s") as data_formatada', 'where'=>'midias.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('midias.id', 'desc')))->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/midias/index', $this->data);
        }
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("midias", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_midias_create'), 'midias/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('descricao', 'lang:create_validation_descricao_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');

		$dados["descricao"] = $this->input->post('descricao');
		$dados["status"] = $this->input->post('status');
		$dados["empresa_id"] = $this->session->userdata('empresa_id');

		if ($this->form_validation->run() == TRUE && $id = $this->midias_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->midias_model->messages()));
			redirect('midias', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->midias_model->errors() ? show_alert_error($this->midias_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->input->post('status');
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);

            /* Load Template */
            $this->template->admin_render('admin/midias/create', $this->data);
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
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("midias", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_midias_edit'), 'midias/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$midia = $this->midias_model->get(array('select'=>'*', 'where'=>'id = '.$id.' AND empresa_id = "'.$this->session->userdata('empresa_id').'"', 'order_by'=>array('id','desc')))->row();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('descricao', 'lang:create_validation_descricao_label', 'trim|required');

		$dados["status"] = $this->input->post('status');
		$dados["descricao"] = $this->input->post('descricao');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$midia_update = $this->midias_model->update($id, $dados);

				if ($midia_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_midia_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->midias_model->errors()));
				}

				redirect('midias', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->midias_model->errors() ? show_alert_error($this->midias_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['midia']   = $midia;

		$this->data['status'] = $this->form_validation->set_value('status', $midia->status);
		$this->data['descricao'] = array(
			'name'  => 'descricao',
			'id'    => 'descricao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('descricao', $midia->descricao)
		);

        /* Load Template */
        $this->template->admin_render('admin/midias/edit', $this->data);
	}


	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("midias", "activate"))
		{
			$activation = $this->midias_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->midias_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->midias_model->messages()));
		redirect('midias', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("midias", "deactivate"))
		{
			$deactivation = $this->midias_model->deactivate($id);
		}
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->midias_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->midias_model->messages()));
		redirect('midias', 'refresh');
	}
	
}
