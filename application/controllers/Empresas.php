<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Empresas extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/empresas');
        $this->load->model('empresas_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_empresas'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_empresas'), 'empresas');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("empresas", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['empresas'] = $this->empresas_model->get()->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/empresas/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("empresas", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_empresas_create'), 'empresas/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('empresa_name', 'lang:create_validation_name_label', 'required|trim');

		if ($this->form_validation->run() == TRUE && $id = $this->empresas_model->create($this->input->post('empresa_name')))
		{
			foreach (get_padrao_email() as $key => $value) {
				$message = $this->load->view("admin/email/".$value["id"].".tpl.php", array(), true);
				$this->empresas_model->create_email_padrao(array("empresa_id" => $id, "assunto" => $value["desc"], "tipo_email" => $key, "mensagem" => $message));
			}

			$this->session->set_flashdata('message', show_alert_success($this->empresas_model->messages()));
			redirect('empresas', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->empresas_model->errors() ? show_alert_error($this->empresas_model->errors()) : show_alert_error($this->session->flashdata('message'))));

			$this->data['empresa_name'] = array(
				'name'  => 'empresa_name',
				'id'    => 'empresa_name',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('empresa_name')
			);

            /* Load Template */
            $this->template->admin_render('admin/empresas/create', $this->data);
		}
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("empresas", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/empresas/delete');
        }
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("empresas", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_empresas_edit'), 'empresas/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$empresa = $this->empresas_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		/* Validate form input */
        $this->form_validation->set_rules('empresa_name', $this->lang->line('edit_empresa_validation_name_label'), 'required|trim');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$empresa_update = $this->empresas_model->update($id, $_POST['empresa_name']);

				if ($empresa_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_empresa_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->empresas_model->errors()));
				}

				redirect('empresas', 'refresh');
			} else {
        		$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->empresas_model->errors() ? show_alert_error($this->empresas_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['empresa']   = $empresa;

		$this->data['empresa_name'] = array(
			'type'    => 'text',
			'name'    => 'empresa_name',
			'id'      => 'empresa_name',
			'value'   => $this->form_validation->set_value('empresa_name', $empresa->nome),
            'class'   => 'form-control'
		);
        /* Load Template */
        $this->template->admin_render('admin/empresas/edit', $this->data);
	}
}
