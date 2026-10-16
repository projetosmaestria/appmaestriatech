<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produtos_modulos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/produtos');
        $this->load->model('produtos_model');
        $this->load->model('produtos_modulos_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_produtos_modulos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_produtos_modulos'), 'produtos_modulos');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos_modulos", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['modulos'] = $this->produtos_modulos_model->get(array('select'=>'modulos.*, produtos.nome as nome_produto', 'join' => array(array('produtos', 'produtos.id = modulos.id_produto')), 'where'=>'produtos.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('modulos.id', 'desc')))->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/produtos/modulos/index', $this->data);
        }
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos_modulos", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_produtos_modulos_create'), 'produtos_modulos/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('nome', 'lang:create_validation_nome_label', 'trim|required');
		$this->form_validation->set_rules('produto', 'lang:create_validation_produto_label', 'required');
		$this->form_validation->set_rules('valor_licenca', 'lang:create_validation_valor_licenca_label', 'required|is_money');
		$this->form_validation->set_rules('valor_manutencao', 'lang:create_validation_valor_manutencao_label', 'required|is_money');
		$this->form_validation->set_rules('valor_implantacao', 'lang:create_validation_valor_implantacao_label', 'required|is_money');

		$dados["status"] = $this->input->post('status');
		$dados["nome"] = $this->input->post('nome');
		$dados["id_produto"] = $this->input->post('produto');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["valor_licenca"] = $this->input->post('valor_licenca');
		$dados["valor_manutencao"] = $this->input->post('valor_manutencao');
		$dados["valor_implantacao"] = $this->input->post('valor_implantacao');
		$dados["data_cadastro"] = date('Y-m-d H:i:s');
		$dados["cadastrado_por"] = $this->session->userdata('user_id');

		if ($this->form_validation->run() == TRUE && $id = $this->produtos_modulos_model->create($dados))
		{
			//$this->produtos_modulos_model->create_habilitacoes_produto($this->input->post('produto'), $id);

			$this->session->set_flashdata('message', show_alert_success($this->produtos_modulos_model->messages()));
			redirect('produtos_modulos', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->produtos_modulos_model->errors() ? show_alert_error($this->produtos_modulos_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');

			$produto_options = array(""=>"Selecione");
			$produtos = $this->produtos_model->get(array('select'=>'produtos.*', 'where'=>'produtos.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('produtos.id', 'desc')));
			if($produtos->num_rows() > 0):
	        	foreach ($produtos->result() as $value) {
	        		$produto_options[$value->id] = $value->nome;       		
	        	}
	        endif;
			$this->data['produto'] = array(
				'name'  => 'produto',
				'id'    => 'produto1',
	            'class' => 'form-control',
	            'options' => $produto_options,
				'selected' => $this->form_validation->set_value('produto')
			);
			$this->data['nome'] = array(
				'name'  => 'nome',
				'id'    => 'nome',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('nome')
			);
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);
			$this->data['valor_licenca'] = array(
				'name'  => 'valor_licenca',
				'id'    => 'valor_licenca',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('valor_licenca')
			);
			$this->data['valor_manutencao'] = array(
				'name'  => 'valor_manutencao',
				'id'    => 'valor_manutencao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('valor_manutencao')
			);
			$this->data['valor_implantacao'] = array(
				'name'  => 'valor_implantacao',
				'id'    => 'valor_implantacao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('valor_implantacao')
			);

            /* Load Template */
            $this->template->admin_render('admin/produtos/modulos/create', $this->data);
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
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("produtos_modulos", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_produtos_modulos_edit'), 'produtos_modulos/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$produto_modulo = $this->produtos_modulos_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('nome', 'lang:create_validation_nome_label', 'trim|required');
		$this->form_validation->set_rules('produto', 'lang:create_validation_produto_label', 'required');
		$this->form_validation->set_rules('valor_licenca', 'lang:create_validation_valor_licenca_label', 'required|is_money');
		$this->form_validation->set_rules('valor_manutencao', 'lang:create_validation_valor_manutencao_label', 'required|is_money');
		$this->form_validation->set_rules('valor_implantacao', 'lang:create_validation_valor_implantacao_label', 'required|is_money');	

		$dados["status"] = $this->input->post('status');
		$dados["nome"] = $this->input->post('nome');
		$dados["id_produto"] = $this->input->post('produto');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["valor_licenca"] = $this->input->post('valor_licenca');
		$dados["valor_manutencao"] = $this->input->post('valor_manutencao');
		$dados["valor_implantacao"] = $this->input->post('valor_implantacao');
		$dados["data_atualizacao"] = date('Y-m-d H:i:s');
		$dados["atualizado_por"] = $this->session->userdata('user_id');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$produto_modulo_update = $this->produtos_modulos_model->update($id, $dados);

				if ($produto_modulo_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->produtos_modulos_model->messages()));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->produtos_modulos_model->errors()));
				}

				redirect('produtos_modulos', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->produtos_modulos_model->errors() ? show_alert_error($this->produtos_modulos_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['produto_modulo']   = $produto_modulo;
		$this->data['status'] = $this->form_validation->set_value('status', $produto_modulo->status);
		$produto_options = array(""=>"Selecione");
			$produtos = $this->produtos_model->get(array('select'=>'produtos.*', 'where'=>'produtos.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('produtos.id', 'desc')));
			if($produtos->num_rows() > 0):
	        	foreach ($produtos->result() as $value) {
	        		$produto_options[$value->id] = $value->nome;       		
	        	}
	        endif;
			$this->data['produto'] = array(
				'name'  => 'produto',
				'id'    => 'produto1',
	            'class' => 'form-control',
	            'options' => $produto_options,
				'selected' => $this->form_validation->set_value('produto', $produto_modulo->id_produto)
			);
		$this->data['nome'] = array(
			'name'  => 'nome',
			'id'    => 'nome',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('nome', $produto_modulo->nome)
		);
		$this->data['valor_licenca'] = array(
			'name'  => 'valor_licenca',
			'id'    => 'valor_licenca',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('valor_licenca', $produto_modulo->valor_licenca)
		);
		$this->data['valor_manutencao'] = array(
			'name'  => 'valor_manutencao',
			'id'    => 'valor_manutencao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('valor_manutencao', $produto_modulo->valor_manutencao)
		);
		$this->data['valor_implantacao'] = array(
			'name'  => 'valor_implantacao',
			'id'    => 'valor_implantacao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('valor_implantacao', $produto_modulo->valor_implantacao)
		);
		$this->data['descricao'] = array(
			'name'  => 'descricao',
			'id'    => 'descricao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('descricao', $produto_modulo->descricao)
		);

        /* Load Template */
        $this->template->admin_render('admin/produtos/modulos/edit', $this->data);
	}


	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("produtos_modulos", "activate"))
		{
			$activation = $this->produtos_modulos_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->produtos_modulos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->produtos_modulos_model->messages()));
		redirect('produtos_modulos', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("produtos_modulos", "deactivate"))
		{
			$deactivation = $this->produtos_modulos_model->deactivate($id);
		}
		if(@$deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->produtos_modulos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error(lang('permission_denied')));
		redirect('produtos_modulos', 'refresh');
	}
	
}
