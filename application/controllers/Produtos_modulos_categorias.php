<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produtos_modulos_categorias extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/produtos');
        $this->load->model('produtos_model');
        $this->load->model('produtos_modulos_model');
        $this->load->model('produtos_modulos_categorias_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_produtos_modulos_categorias'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_produtos_modulos_categorias'), 'produtos_modulos_categorias');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos_modulos_categorias", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['modulos_categorias'] = $this->produtos_modulos_categorias_model->get(array('select'=>'modulos_categoria.*, produtos.nome as nome_produto, modulos.nome as nome_modulo', 'join' => array(array('modulos', 'modulos.id = modulos_categoria.id_modulo'),array('produtos', 'produtos.id = modulos_categoria.id_produto')), 'where'=>'produtos.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('modulos_categoria.id', 'desc')))->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/produtos/modulos/categorias/index', $this->data);
        }
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos_modulos_categorias", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_produtos_modulos_categorias_create'), 'produtos_modulos_categorias/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('nome', 'lang:create_validation_nome_label', 'trim|required');
		$this->form_validation->set_rules('produto', 'lang:create_validation_produto_label', 'required');
		$this->form_validation->set_rules('modulo', 'lang:create_validation_modulo_label', 'required');

		$dados["status"] = $this->input->post('status');
		$dados["nome"] = $this->input->post('nome');
		$dados["id_produto"] = $this->input->post('produto');
		$dados["id_modulo"] = $this->input->post('modulo');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["layout"] = $this->input->post('layout');
		$dados["data_cadastro"] = date('Y-m-d H:i:s');
		$dados["cadastrado_por"] = $this->session->userdata('user_id');

		if ($this->form_validation->run() == TRUE && $id = $this->produtos_modulos_categorias_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->produtos_modulos_categorias_model->messages()));
			redirect('produtos_modulos_categorias', 'refresh');
		} else {
			if($_POST) {
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->produtos_modulos_categorias_model->errors() ? show_alert_error($this->produtos_modulos_categorias_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            	if($this->input->post('produto')) {
			        $produto_modulo_options = array(""=>"Selecione");
					$produtos_modulos = $this->produtos_modulos_model->get(array('select'=>'modulos.*', 'where'=>'id_produto = "'.$this->input->post('produto').'"', 'order_by'=>array('modulos.id', 'desc')));
					if($produtos_modulos->num_rows() > 0):
			        	foreach ($produtos_modulos->result() as $value) {
			        		$produto_modulo_options[$value->id] = $value->nome;       		
			        	}
			        endif;
		        }
			}

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
				'id'    => 'produto_modulo_categoria',
	            'class' => 'form-control',
	            'options' => $produto_options,
				'selected' => $this->form_validation->set_value('produto')
			);
			if(!$this->input->post('produto'))
				$produto_modulo_options = array(""=>"Selecione");
			$this->data['modulo'] = array(
				'name'  => 'modulo',
				'id'    => 'modulo',
	            'class' => 'form-control',
	            'options' => $produto_modulo_options,
				'selected' => $this->form_validation->set_value('modulo')
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
			$this->data['layout'] = array(
				'name'  => 'layout',
				'id'    => 'layout',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('layout')
			);

            /* Load Template */
            $this->template->admin_render('admin/produtos/modulos/categorias/create', $this->data);
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
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("produtos_modulos_categorias", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_produtos_modulos_categorias_edit'), 'produtos_modulos_categorias/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$produto_modulo_categoria = $this->produtos_modulos_categorias_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('nome', 'lang:create_validation_nome_label', 'trim|required');
		$this->form_validation->set_rules('produto', 'lang:create_validation_produto_label', 'required');
		$this->form_validation->set_rules('modulo', 'lang:create_validation_modulo_label', 'required');

		$dados["status"] = $this->input->post('status');
		$dados["nome"] = $this->input->post('nome');
		$dados["id_produto"] = $this->input->post('produto');
		$dados["id_modulo"] = $this->input->post('modulo');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["layout"] = $this->input->post('layout');
		$dados["data_atualizacao"] = date('Y-m-d H:i:s');
		$dados["atualizado_por"] = $this->session->userdata('user_id');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$produto_modulo_categoria_update = $this->produtos_modulos_categorias_model->update($id, $dados);

				if ($produto_modulo_categoria_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->produtos_modulos_categorias_model->messages()));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->produtos_modulos_categorias_model->errors()));
				}

				redirect('produtos_modulos_categorias', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->produtos_modulos_categorias_model->errors() ? show_alert_error($this->produtos_modulos_categorias_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['produto_modulo_categoria']   = $produto_modulo_categoria;
		$this->data['status'] = $this->form_validation->set_value('status', $produto_modulo_categoria->status);
		$produto_options = array(""=>"Selecione");
		$produtos = $this->produtos_model->get(array('select'=>'produtos.*', 'where'=>'produtos.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('produtos.id', 'desc')));
		if($produtos->num_rows() > 0):
        	foreach ($produtos->result() as $value) {
        		$produto_options[$value->id] = $value->nome;       		
        	}
        endif;
		$this->data['produto'] = array(
			'name'  => 'produto',
			'id'    => 'produto_modulo_categoria_edit',
            'class' => 'form-control',
            'options' => $produto_options,
			'selected' => $this->form_validation->set_value('produto', $produto_modulo_categoria->id_produto)
		);
		$modulo_options = array(""=>"Selecione");
		$modulos = $this->produtos_modulos_model->get(array('select'=>'modulos.*', 'where'=>'id_produto = "'.$this->form_validation->set_value('produto', $produto_modulo_categoria->id_produto).'"', 'order_by'=>array('modulos.id', 'desc')));
		if($modulos->num_rows() > 0):
        	foreach ($modulos->result() as $value) {
        		$modulo_options[$value->id] = $value->nome;       		
        	}
        endif;
		$this->data['modulo'] = array(
			'name'  => 'modulo',
			'id'    => 'modulo_edit',
            'class' => 'form-control',
            'options' => $modulo_options,
			'selected' => $this->form_validation->set_value('modulo', $produto_modulo_categoria->id_modulo)
		);
		$this->data['nome'] = array(
			'name'  => 'nome',
			'id'    => 'nome',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('nome', $produto_modulo_categoria->nome)
		);
		$this->data['descricao'] = array(
			'name'  => 'descricao',
			'id'    => 'descricao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('descricao', $produto_modulo_categoria->descricao)
		);
		$this->data['layout'] = array(
			'name'  => 'layout',
			'id'    => 'layout',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('layout', $produto_modulo_categoria->layout)
		);

        /* Load Template */
        $this->template->admin_render('admin/produtos/modulos/categorias/edit', $this->data);
	}


	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("produtos_modulos_categorias", "activate"))
		{
			$activation = $this->produtos_modulos_categorias_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->produtos_modulos_categorias_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->produtos_modulos_categorias_model->messages()));
		redirect('produtos_modulos_categorias', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("produtos_modulos_categorias", "deactivate"))
		{
			$deactivation = $this->produtos_modulos_categorias_model->deactivate($id);
		}
		if(@$deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->produtos_modulos_categorias_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error(lang('permission_denied')));
		redirect('produtos_modulos_categorias', 'refresh');
	}

	public function get_modulos_habilitados(){
		$modulos = $this->produtos_modulos_categorias_model->get_modulos($_POST["id"]);

        $select = array();
        if($modulos->num_rows() > 0):
        	foreach ($modulos->result() as $value) {
        		$prdt = array(
                    'id' => $value->id,
                    'nome' => $value->nome
                );
                array_push($select, $prdt);        		
        	}
        else:
            $select = false;
        endif;

        exit(json_encode($select));
    }
	
}
