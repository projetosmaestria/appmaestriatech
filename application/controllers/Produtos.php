<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produtos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/produtos');
        $this->load->model('produtos_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_produtos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_produtos'), 'admin/produtos');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['produtos'] = $this->produtos_model->get(array('select'=>'produtos.*', 'where'=>'produtos.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('produtos.id', 'desc')))->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/produtos/index', $this->data);
        }
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_produtos_create'), 'produtos/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('nome', 'lang:create_validation_nome_label', 'trim|required');
		$this->form_validation->set_rules('aplicacao', 'lang:create_validation_aplicacao_label', 'trim|required');

		$dados["status"] = $this->input->post('status');
		$dados["nome"] = $this->input->post('nome');
		$dados["aplicacao"] = $this->input->post('aplicacao');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["data_cadastro"] = date('Y-m-d H:i:s');
		$dados["cadastrado_por"] = $this->session->userdata('user_id');
		$dados["empresa_id"] = $this->session->userdata('empresa_id');

		if ($this->form_validation->run() == TRUE && $id = $this->produtos_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->produtos_model->messages()));
			redirect('produtos', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->produtos_model->errors() ? show_alert_error($this->produtos_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
			$this->data['nome'] = array(
				'name'  => 'nome',
				'id'    => 'nome',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('nome')
			);
			$this->data['aplicacao'] = array(
				'name'  => 'aplicacao',
				'id'    => 'aplicacao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('aplicacao')
			);
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);

            /* Load Template */
            $this->template->admin_render('admin/produtos/create', $this->data);
		}
	}

	/*public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/produtos/delete');
        }
	}*/

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("produtos", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_produtos_edit'), 'produtos/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$produto = $this->produtos_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('nome', 'lang:create_validation_nome_label', 'trim|required');
		$this->form_validation->set_rules('aplicacao', 'lang:create_validation_aplicacao_label', 'trim|required');		

		$dados["status"] = $this->input->post('status');
		$dados["nome"] = $this->input->post('nome');
		$dados["aplicacao"] = $this->input->post('aplicacao');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["data_atualizacao"] = date('Y-m-d H:i:s');
		$dados["atualizado_por"] = $this->session->userdata('user_id');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$produto_update = $this->produtos_model->update($id, $dados);

				if ($produto_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->produto_update->errors()));
				}

				redirect('produtos', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->produto_update->errors() ? show_alert_error($this->produto_update->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['produto']   = $produto;
		$this->data['status'] = $this->form_validation->set_value('status', $produto->status);
		$this->data['nome'] = array(
			'name'  => 'nome',
			'id'    => 'nome',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('nome', $produto->nome)
		);
		$this->data['aplicacao'] = array(
			'name'  => 'aplicacao',
			'id'    => 'aplicacao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('aplicacao', $produto->aplicacao)
		);
		$this->data['descricao'] = array(
			'name'  => 'descricao',
			'id'    => 'descricao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('descricao', $produto->descricao)
		);

        /* Load Template */
        $this->template->admin_render('admin/produtos/edit', $this->data);
	}


	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("produtos", "activate"))
		{
			$activation = $this->produtos_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->produtos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->produtos_model->messages()));
		redirect('produtos', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("produtos", "deactivate"))
		{
			$deactivation = $this->produtos_model->deactivate($id);
		}
		if(@$deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->produtos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error(lang('permission_denied')));
		redirect('produtos', 'refresh');
	}
	
}
