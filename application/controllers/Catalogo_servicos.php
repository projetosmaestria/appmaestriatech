<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalogo_servicos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/catalogo_servicos');
        $this->load->model('catalogo_servicos_model');
        $this->load->model('produtos_model');
        $this->load->model('produtos_modulos_model');
        $this->load->model('produtos_modulos_categorias_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_catalogo_servicos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_catalogo_servicos'), 'catalogo_servicos');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("catalogo_servicos", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['catalogo_servicos'] = $this->catalogo_servicos_model->get(array('select'=>'catalogo_servico.*, nivel.descricao as desc_nivel, modulos.nome as desc_modulo, produtos.nome as desc_produto, modulos_categoria.nome as desc_categoria', 'join' => array(array('nivel', 'nivel.id = catalogo_servico.nivel_id'),array('modulos_categoria', 'modulos_categoria.id = catalogo_servico.modulo_categoria_id'),array('modulos', 'modulos.id = modulos_categoria.id_modulo'),array('produtos', 'produtos.id = modulos.id_produto')), 'where'=>'produtos.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('catalogo_servico.id', 'desc')))->result();
            $this->data['message'] = $this->session->flashdata('message');
            /* Load Template */
            $this->template->admin_render('admin/catalogo_servicos/index', $this->data);
        }
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("catalogo_servicos", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_catalogo_servicos_create'), 'catalogo_servicos/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('produto', 'lang:create_validation_produto_label', 'required');
		$this->form_validation->set_rules('modulo', 'lang:create_validation_modulo_label', 'required');
		$this->form_validation->set_rules('categoria', 'lang:create_validation_categoria_label', 'required');
		$this->form_validation->set_rules('nivel', 'lang:create_validation_nivel_label', 'required');
		$this->form_validation->set_rules('tempo_padrao', 'lang:create_validation_tempo_padrao_label', 'required|numeric');
		$this->form_validation->set_rules('prazo_padrao', 'lang:create_validation_prazo_padrao_label', 'required|numeric');

		$dados["actived"] = $this->input->post('status');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["tempo_padrao"] = $this->input->post('tempo_padrao');
		$dados["prazo_padrao"] = $this->input->post('prazo_padrao');
		$dados["nivel_id"] = $this->input->post('nivel');
		$dados["modulo_categoria_id"] = $this->input->post('categoria');
		$dados["created"] = date('Y-m-d H:i:s');

		if ($this->form_validation->run() == TRUE && $id = $this->catalogo_servicos_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->catalogo_servicos_model->messages()));
			redirect('catalogo_servicos', 'refresh');
		} else {
			if($_POST) {
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->catalogo_servicos_model->errors() ? show_alert_error($this->catalogo_servicos_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            	if($this->input->post('produto')) {
			        $produto_modulo_options = array(""=>"Selecione");
					$produtos_modulos = $this->produtos_modulos_model->get(array('select'=>'modulos.*', 'where'=>'id_produto = "'.$this->input->post('produto').'"', 'order_by'=>array('modulos.id', 'desc')));
					if($produtos_modulos->num_rows() > 0):
			        	foreach ($produtos_modulos->result() as $value) {
			        		$produto_modulo_options[$value->id] = $value->nome;       		
			        	}
			        endif;
		        }
		        if($this->input->post('modulo')) {
			        $produto_modulo_categoria_options = array(""=>"Selecione");
					$produtos_modulos_categorias = $this->produtos_modulos_categorias_model->get(array('select'=>'modulos_categoria.*', 'where'=>'id_modulo = "'.$this->input->post('modulo').'"', 'order_by'=>array('modulos_categoria.id', 'desc')));
					if($produtos_modulos_categorias->num_rows() > 0):
			        	foreach ($produtos_modulos_categorias->result() as $value) {
			        		$produto_modulo_categoria_options[$value->id] = $value->nome;       		
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
			if(!$this->input->post('modulo'))
				$produto_modulo_categoria_options = array(""=>"Selecione");
			$this->data['categoria'] = array(
				'name'  => 'categoria',
				'id'    => 'categoria',
	            'class' => 'form-control',
	            'options' => $produto_modulo_categoria_options,
				'selected' => $this->form_validation->set_value('categoria')
			);
			$nivel_options = array(""=>"Selecione");
			$niveis = $this->catalogo_servicos_model->get_niveis('actived = 1');
			if($niveis->num_rows() > 0):
	        	foreach ($niveis->result() as $value) {
	        		$nivel_options[$value->id] = $value->descricao;       		
	        	}
	        endif;
			$this->data['nivel'] = array(
				'name'  => 'nivel',
				'id'    => 'nivel',
	            'class' => 'form-control',
	            'options' => $nivel_options,
				'selected' => $this->form_validation->set_value('nivel')
			);
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);
			$this->data['texto'] = array(
   				'name'  => 'texto',
   				'id'    => 'texto',
   				'type'  => 'text',
                'class' => 'form-control',
  				'value' => $this->form_validation->set_value('texto')
   			);
			$this->data['tempo_padrao'] = array(
				'name'  => 'tempo_padrao',
				'id'    => 'tempo_padrao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('tempo_padrao')
			);
			$this->data['prazo_padrao'] = array(
				'name'  => 'prazo_padrao',
				'id'    => 'prazo_padrao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('prazo_padrao')
			);

            /* Load Template */
            $this->template->admin_render('admin/catalogo_servicos/create', $this->data);
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
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("catalogo_servicos", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_catalogo_servicos_edit'), 'catalogo_servicos/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$catalogo_servico = $this->catalogo_servicos_model->get(array('select'=>'catalogo_servico.*, modulos_categoria.id as categoria_id, modulos.id as modulo_id, produtos.id as produto_id', 'join'=>array(array('modulos_categoria', 'modulos_categoria.id = catalogo_servico.modulo_categoria_id'), array('modulos', 'modulos.id = modulos_categoria.id_modulo'), array('produtos', 'produtos.id = modulos.id_produto')), 'where'=>'catalogo_servico.id = '.$id, 'order_by'=>array('catalogo_servico.id','desc')))->row();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('produto', 'lang:create_validation_produto_label', 'required');
		$this->form_validation->set_rules('modulo', 'lang:create_validation_modulo_label', 'required');
		$this->form_validation->set_rules('categoria', 'lang:create_validation_categoria_label', 'required');
		$this->form_validation->set_rules('nivel', 'lang:create_validation_nivel_label', 'required');
		$this->form_validation->set_rules('tempo_padrao', 'lang:create_validation_tempo_padrao_label', 'required|numeric');
		$this->form_validation->set_rules('prazo_padrao', 'lang:create_validation_prazo_padrao_label', 'required|numeric');

		$dados["actived"] = $this->input->post('status');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["texto"] = $this->input->post('texto');
		$dados["tempo_padrao"] = $this->input->post('tempo_padrao');
		$dados["prazo_padrao"] = $this->input->post('prazo_padrao');
		$dados["nivel_id"] = $this->input->post('nivel');
		$dados["modulo_categoria_id"] = $this->input->post('categoria');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$catalogo_servico_update = $this->catalogo_servicos_model->update($id, $dados);

				if ($catalogo_servico_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->catalogo_servicos_model->messages()));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->catalogo_servicos_model->errors()));
				}

				redirect('catalogo_servicos', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->catalogo_servicos_model->errors() ? show_alert_error($this->catalogo_servicos_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['catalogo_servico'] = $catalogo_servico;
		$this->data['status'] = $this->form_validation->set_value('status', $catalogo_servico->actived);
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
			'selected' => $this->form_validation->set_value('produto', $catalogo_servico->produto_id)
		);
		$modulo_options = array(""=>"Selecione");
		$modulos = $this->produtos_modulos_model->get(array('select'=>'modulos.*', 'where'=>'id_produto = "'.$this->form_validation->set_value('produto', $catalogo_servico->produto_id).'"', 'order_by'=>array('modulos.id', 'desc')));
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
			'selected' => $this->form_validation->set_value('modulo', $catalogo_servico->modulo_id)
		);
		$categoria_options = array(""=>"Selecione");
		$categorias = $this->produtos_modulos_categorias_model->get(array('select'=>'modulos_categoria.*', 'where'=>'id_modulo = "'.$this->form_validation->set_value('modulo', $catalogo_servico->modulo_id).'"', 'order_by'=>array('modulos_categoria.id', 'desc')));
		if($categorias->num_rows() > 0):
        	foreach ($categorias->result() as $value) {
        		$categoria_options[$value->id] = $value->nome;       		
        	}
        endif;
		$this->data['categoria'] = array(
			'name'  => 'categoria',
			'id'    => 'categoria_edit',
            'class' => 'form-control',
            'options' => $categoria_options,
			'selected' => $this->form_validation->set_value('categoria', $catalogo_servico->modulo_categoria_id)
		);
		$nivel_options = array(""=>"Selecione");
			$niveis = $this->catalogo_servicos_model->get_niveis('actived = 1');
			if($niveis->num_rows() > 0):
	        	foreach ($niveis->result() as $value) {
	        		$nivel_options[$value->id] = $value->descricao;       		
	        	}
	        endif;
			$this->data['nivel'] = array(
				'name'  => 'nivel',
				'id'    => 'nivel',
	            'class' => 'form-control',
	            'options' => $nivel_options,
				'selected' => $this->form_validation->set_value('nivel', $catalogo_servico->nivel_id)
			);
		$this->data['descricao'] = array(
			'name'  => 'descricao',
			'id'    => 'descricao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('descricao', $catalogo_servico->descricao)
		);
		$this->data['texto'] = array(
			'name'  => 'texto',
			'id'    => 'texto',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('texto', $catalogo_servico->texto)
   		);
		$this->data['tempo_padrao'] = array(
			'name'  => 'tempo_padrao',
			'id'    => 'tempo_padrao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('tempo_padrao', $catalogo_servico->tempo_padrao)
		);
		$this->data['prazo_padrao'] = array(
			'name'  => 'prazo_padrao',
			'id'    => 'prazo_padrao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('prazo_padrao', $catalogo_servico->prazo_padrao)
		);

        /* Load Template */
        $this->template->admin_render('admin/catalogo_servicos/edit', $this->data);
	}


	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("catalogo_servicos", "activate"))
		{
			$activation = $this->catalogo_servicos_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->catalogo_servicos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->catalogo_servicos_model->messages()));
		redirect('catalogo_servicos', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("catalogo_servicos", "deactivate"))
		{
			$deactivation = $this->catalogo_servicos_model->deactivate($id);
		}
		if(@$deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->catalogo_servicos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error(lang('permission_denied')));
		redirect('catalogo_servicos', 'refresh');
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
