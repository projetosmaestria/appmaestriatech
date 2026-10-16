<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Config_email extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/config_email');
        $this->load->model('config_email_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_config_email'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_config_email'), 'admin/config_email');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("config_email", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $configs = $this->config_email_model->get()->result();
            $padroes_email = get_padrao_email();
            foreach ($configs as $key => $value) {

            	$configs[$key]->tipo_email = $padroes_email[$value->tipo_email]["desc"];
            }

            $this->data['config_email'] = $configs;
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/config_email/index', $this->data);
        }
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("config_email", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_config_email_edit'), 'config_email/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$config_email = $this->config_email_model->get($id)->row();

		/* Validate form input */
		$this->form_validation->set_rules('assunto', 'lang:assunto', 'trim|required');
		$this->form_validation->set_rules('mensagem', 'lang:mensagem', 'required');

		$dados["assunto"] = $this->input->post('assunto');
		$dados["mensagem"] = $this->input->post('mensagem');
		$dados["data_atualizacao"] = date('Y-m-d H:i:s');
		$dados["atualizado_por"] = $this->session->userdata('user_id');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$config_email_updated = $this->config_email_model->update($id, $dados);

				if ($config_email_updated)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_config_email_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->config_email_model->errors()));
				}

				redirect('config_email', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->config_email_model->errors() ? show_alert_error($this->config_email_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['config_email'] = $config_email;

        $tipos_email = get_padrao_email();
        $this->data['tipo_email_desc'] = $tipos_email[$config_email->tipo_email]["desc"];

		$this->data['tipo_email'] = array(
			'name'  => 'tipo_email',
			'id'    => 'tipo_email',
			'type'  => 'text',
            'class' => 'form-control',
            'disabled' => 'disabled',
			'value' => $this->data['tipo_email_desc']
		);

		$this->data['assunto'] = array(
			'name'  => 'assunto',
			'id'    => 'assunto',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('assunto', $config_email->assunto)
		);

		$this->data['mensagem'] = array(
			'name'  => 'mensagem',
			'id'    => 'mensagem',
            'rows' => '10',
            'cols' => '80',
			'value' => $this->form_validation->set_value('mensagem', $config_email->mensagem)
		);

        /* Load Template */
        $this->template->admin_render('admin/config_email/edit', $this->data);
	}	

}
