<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chamados_categoria extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/chamados');
        $this->load->model('chamados_categoria_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_chamados_categories'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_chamados_categoria'), 'chamados/categoria');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("chamados_categoria", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['categorias'] = $this->chamados_categoria_model->get(array('select'=>'chamado_categoria.*, setores.descricao as setor', 'join'=>array(array('setores','setores.id = chamado_categoria.setor_id')),'where'=>'setores.empresa_id = "'.$this->session->userdata('empresa_id').'"' , 'order_by'=>array('chamado_categoria.id', 'desc')))->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->data['setores_filtro'] = $this->chamados_categoria_model->get_setores()->result();
            $this->data['can_create'] = $this->ion_auth->has_permissao('chamados_categoria', 'create');
            $this->data['can_edit'] = $this->ion_auth->has_permissao('chamados_categoria', 'edit');
            $this->template->admin_render('admin/chamados/categoria/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("chamados_categoria", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_chamados_categoria_create'), 'chamados_categoria/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('descricao', $this->lang->line('categoria_validation_descricao_label'), 'required');
		$this->form_validation->set_rules('setor', 'lang:categoria_validation_setor_label', 'required|in_list[' . implode(',', $this->ids_setores_empresa()) . ']');
        $this->form_validation->set_rules('status', $this->lang->line('categoria_validation_status_label'), 'required');

		$dados = array(
			"descricao" => $this->input->post("descricao"),
			"setor_id" => $this->input->post("setor"),
			"status" => $this->input->post("status")
		);

		if ($this->form_validation->run() == TRUE && $id = $this->chamados_categoria_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->chamados_categoria_model->messages()));
			redirect('chamados_categoria', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->chamados_categoria_model->errors() ? show_alert_error($this->chamados_categoria_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);

			$setores = $this->chamados_categoria_model->get_setores()->result_array();
			$setor_options = array(""=>"Selecione");
			foreach ($setores as $setor) {
				$setor_options[$setor['id']] = $setor['descricao'];
			}
			$this->data['setor'] = array(
				'name'  => 'setor',
				'id'    => 'setor',
                'class' => 'form-control',
                'options' => $setor_options,
				'selected' => $this->form_validation->set_value('setor'),
			);

            /* Load Template */
            $this->template->admin_render('admin/chamados/categoria/create', $this->data);
		}
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("chamados_categoria", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/chamados/categoria/delete');
        }
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("chamados_categoria", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_chamados_categoria_edit'), 'chamados_categoria/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		// Só edita categorias de setores da empresa logada.
		$categoria = $this->chamados_categoria_model->get(array(
			'select' => 'chamado_categoria.*',
			'join' => array(array('setores', 'setores.id = chamado_categoria.setor_id')),
			'where' => 'chamado_categoria.id = ' . (int) $id . ' AND setores.empresa_id = ' . (int) $this->session->userdata('empresa_id'),
			'order_by' => array('chamado_categoria.id', 'desc')
		))->row();
		if (!$categoria) {
			$this->session->set_flashdata('message', show_alert_error('Categoria não encontrada.'));
			redirect('chamados_categoria', 'refresh');
		}

		/* Validate form input */
        $this->form_validation->set_rules('descricao', $this->lang->line('categoria_validation_descricao_label'), 'required');
		$this->form_validation->set_rules('setor', 'lang:categoria_validation_setor_label', 'required|in_list[' . implode(',', $this->ids_setores_empresa()) . ']');
        $this->form_validation->set_rules('status', $this->lang->line('categoria_validation_status_label'), 'required');

		if (isset($_POST) && ! empty($_POST))
		{	
			$dados = array(
				"setor_id" => $this->input->post("setor"),
				"descricao" => $this->input->post("descricao"),
				"status" => $this->input->post("status")
			);
			if ($this->form_validation->run() == TRUE)
			{
				$categoria_update = $this->chamados_categoria_model->update((int) $id, $dados);

				if ($categoria_update)
				{
					$this->session->set_flashdata('message', show_alert_success('Categoria atualizada.'));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->chamados_categoria_model->errors()));
				}

				redirect('chamados_categoria', 'refresh');
			} else {
        		$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->chamados_categoria_model->errors() ? show_alert_error($this->chamados_categoria_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}
		
        $setores = $this->chamados_categoria_model->get_setores()->result_array();
		$setor_options = array(""=>"Selecione");
		foreach ($setores as $setor) {
			$setor_options[$setor['id']] = $setor['descricao'];
		}
		$this->data['setor'] = array(
			'name'  => 'setor',
			'id'    => 'setor',
            'class' => 'form-control',
            'options' => $setor_options,
			'selected' => $this->form_validation->set_value('setor', $categoria->setor_id),
		);
		$this->data['status'] = $this->form_validation->set_value('status', $categoria->status);
		$this->data['descricao'] = array(
			'type'    => 'text',
			'name'    => 'descricao',
			'id'      => 'descricao',
			'value'   => $this->form_validation->set_value('descricao', $categoria->descricao),
            'class'   => 'form-control'
		);

        /* Load Template */
        $this->data['categoria'] = $categoria;
        $this->template->admin_render('admin/chamados/categoria/edit', $this->data);
	}

	public function activate($id)
	{
        $id = (int) $id;
		$activation = false;
		if ($this->ion_auth->has_permissao("chamados_categoria", "activate"))
		{
			$activation = $this->chamados_categoria_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->chamados_categoria_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->chamados_categoria_model->messages()));
		redirect('chamados_categoria', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		$deactivation = false;
		if ($this->ion_auth->has_permissao("chamados_categoria", "deactivate"))
		{
			$deactivation = $this->chamados_categoria_model->deactivate($id);
		}
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->chamados_categoria_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->chamados_categoria_model->messages()));
		redirect('chamados_categoria', 'refresh');
	}

	// IDs dos setores ativos da empresa logada, para validar o setor escolhido no formulário.
	private function ids_setores_empresa()
	{
		$ids = array();
		foreach ($this->chamados_categoria_model->get_setores()->result() as $setor) {
			$ids[] = (int) $setor->id;
		}
		return $ids ? $ids : array(0);
	}
}
