<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Niveis extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/niveis');
        $this->load->model('niveis_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_niveis'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_niveis'), 'niveis');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("niveis", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['niveis'] = $this->niveis_model->get()->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/niveis/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("niveis", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_niveis_create'), 'niveis/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('descricao', 'lang:nivel_validation_descricao_label', 'trim|required|alpha_dash');
		$this->form_validation->set_rules('status', 'lang:nivel_validation_status_label', 'required');

		$dados = array(
			"descricao" => $this->input->post("descricao"),
			"empresa_id" => $this->session->userdata("empresa_id"),
			"actived" => $this->input->post("status")
		);

		if ($this->form_validation->run() == TRUE && $id = $this->niveis_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->niveis_model->messages()));
			redirect('niveis', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->niveis_model->errors() ? show_alert_error($this->niveis_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);

            /* Load Template */
            $this->template->admin_render('admin/niveis/create', $this->data);
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
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("niveis", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_niveis_edit'), 'niveis/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$nivel = $this->niveis_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		/* Validate form input */
        $this->form_validation->set_rules('descricao', $this->lang->line('nivel_validation_descricao_label'), 'required|alpha_dash');
        $this->form_validation->set_rules('status', $this->lang->line('nivel_validation_status_label'), 'required');

		if (isset($_POST) && ! empty($_POST))
		{	
			$dados = array(
				"descricao" => $this->input->post("descricao"),
				"actived" => $this->input->post("status"),
				"cor" => $this->input->post("cor")
			);
			if ($this->form_validation->run() == TRUE)
			{
				$nivel_update = $this->niveis_model->update($id, $dados);

				if ($nivel_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_nivel_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->niveis_model->errors()));
				}

				redirect('niveis', 'refresh');
			} else {
        		$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->niveis_model->errors() ? show_alert_error($this->niveis_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['nivel']   = $nivel;

		$this->data['status'] = $this->form_validation->set_value('status', $nivel->actived);
		$this->data['descricao'] = array(
			'type'    => 'text',
			'name'    => 'descricao',
			'id'      => 'descricao',
			'value'   => $this->form_validation->set_value('descricao', $nivel->descricao),
            'class'   => 'form-control'
		);
		$this->data['cor'] = array(
			'type'     => 'text',
			'name'     => 'cor',
			'id'       => 'group_bgcolor',
			'value'    => $this->form_validation->set_value('cor', $nivel->cor),
			'data-src' => $nivel->cor,
            'class'    => 'form-control'
		);

        /* Load Template */
        $this->template->admin_render('admin/niveis/edit', $this->data);
	}

	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("niveis", "activate"))
		{
			$activation = $this->niveis_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->niveis_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->niveis_model->messages()));
		redirect('niveis', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("niveis", "deactivate"))
		{
			$deactivation = $this->niveis_model->deactivate($id);
		}
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->niveis_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->niveis_model->messages()));
		redirect('niveis', 'refresh');
	}
}
