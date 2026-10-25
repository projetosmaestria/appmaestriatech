<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menus extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/menus');
        $this->load->model('menus_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_menus'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_menus'), 'menus');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("menus", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['menus'] = $this->menus_model->get(array('select'=>'m.*, mm.nome as pai', 'join'=>array(array('menus mm', 'mm.id = m.pai_id', 'left')),'where'=>'' , 'order_by'=>'m.id desc'))->result();            
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/menus/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("menus", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_menus_create'), 'menus/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('nome', 'lang:create_menu_validation_nome_label', 'required|trim');
		$this->form_validation->set_rules('status', 'lang:create_menu_validation_status_label', 'required');
		$this->form_validation->set_rules('controlador', 'lang:create_menu_validation_controlador_label', 'required');
		$this->form_validation->set_rules('metodo', 'lang:create_menu_validation_metodo_label', 'required');
		$this->form_validation->set_rules('tipo', 'lang:create_menu_validation_tipo_label', 'required|is_numeric|greater_than_equal_to[0]|less_than_equal_to[1]');
		$this->form_validation->set_rules('order', 'lang:create_menu_validation_order_label', 'is_numeric');

		$pai_id = $this->input->post('pai_id');
		if($pai_id == "")
			$pai_id = null;
		$dados = array(
			"empresas" => $this->input->post('empresas'),
			//"modulos" => $this->input->post('modulos'),
			"nome" => $this->input->post('nome'),
			"tipo" => $this->input->post('tipo'),
			"icon_class" => trim($this->input->post('icon_class')),
			"pai_id" => $pai_id,
			"ativo" => $this->input->post('status'),
			"order" => $this->input->post('order'),
			"controlador" => $this->input->post('controlador'),
			"metodo" => $this->input->post('metodo')
			);
		if ($this->form_validation->run() == TRUE && $id = $this->menus_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->menus_model->messages()));
			redirect('menus', 'refresh');
		} else {
			if($_POST)
           		$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->menus_model->errors() ? show_alert_error($this->menus_model->errors()) : show_alert_error($this->session->flashdata('message'))));
            
            $todos_menus = $this->menus_model->get(array('select'=>'m.*', 'order_by'=>'m.tipo asc, m.order asc'))->result_array();
            $menuFinal = [];
			construirMenu($todos_menus, $menuFinal, null);

            $html_menu = '<ul id="menu_pai">';
			foreach($menuFinal as $menu){
				$html_menu .= getHtml($menu, $pai_id);
			}
			$html_menu .= '</ul>';

            $this->data['menu'] = $html_menu;
            $this->data['menus_tree'] = $menuFinal;
            $this->data['todos_menus'] = $todos_menus;
            $this->data['pais_options'] = get_menus_hierarquia_options($todos_menus, null);
            $this->data['pai_id_selecionado'] = ($this->input->post('pai_id') !== null) ? $this->input->post('pai_id') : '';

            $nome_pai_selecionado = 'Nenhum (Menu Raiz / 1º Nível)';
            if (!empty($this->data['pai_id_selecionado'])) {
                foreach ($todos_menus as $m) {
                    if ((int)$m['id'] === (int)$this->data['pai_id_selecionado']) {
                        $nome_pai_selecionado = $m['nome'];
                        break;
                    }
                }
            }
            $this->data['nome_pai_selecionado'] = $nome_pai_selecionado;
            $this->data['menu_atual_id'] = null;

            $this->data['controladores'] = get_controladores();

			$this->data['icon_class'] = array(
				'name'  => 'icon_class',
				'id'    => 'icon_class',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->input->post('icon_class')
			);

			$this->data['nome'] = array(
				'name'  => 'nome',
				'id'    => 'nome',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('nome')
			);

			$this->data['tipo'] = array(
				'name'  => 'tipo',
				'id'    => 'tipo',
	            'class' => 'form-control',
	            'options' => array("0"=>lang('menu_main_navigation'), "1"=>lang('menu_administration')),
				'selected' => ($this->form_validation->set_value('tipo') ? $this->form_validation->set_value('tipo') : 0),
			);

			$controlador_options = array(""=>"Selecione");
			foreach (get_controladores() as $controlador) {
				$controlador_options[strtolower($controlador)] = $controlador;
			}
			$this->data['controlador'] = array(
				'name'  => 'controlador',
				'id'    => 'controlador',
	            'class' => 'form-control',
	            'options' => $controlador_options,
				'selected' => ($this->form_validation->set_value('controlador') ? $this->form_validation->set_value('controlador') : ''),
				'onchange' => 'getMetodos()'
			);
			$metodo_options = array();
			if($this->form_validation->set_value('controlador')) {
				foreach (get_metodos($this->form_validation->set_value('controlador')) as $metodo) {
					$metodo_options[strtolower($metodo)] = $metodo;
				}
			} else {
				$metodo_options[] = "Selecione";
			}
			$this->data['metodo'] = array(
				'name'  => 'metodo',
				'id'    => 'metodo',
	            'class' => 'form-control',
	            'options' => $metodo_options,
				'selected' => ($this->form_validation->set_value('metodo') ? $this->form_validation->set_value('metodo') : ''),
			);
			$empresas_options = array();
			foreach ($this->menus_model->get_empresas()->result() as $emp) {
				$empresas_options[$emp->id] = $emp->nome;
			}
			$this->data['empresas_options'] = $empresas_options;
			$this->data['menus_empresas'] = (is_array($this->input->post('empresas')) ? $this->input->post('empresas') : array());
			$this->data['empresas'] = array(
				'name'  => 'empresas[]',
				'id'    => 'empresas',
	            'class' => 'form-control multipleSelect',
	            'options' => $empresas_options,
				'selected' => $this->input->post('empresas'),
			);

			

			$this->data['order'] = array(
				'name'  => 'order',
				'id'    => 'order',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('order')
			);

			$this->data['status'] = $this->form_validation->set_value('status');

            /* Load Template */
            $this->template->admin_render('admin/menus/create', $this->data);
		}
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("menus", "delete"))
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
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("menus", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_empresas_edit'), 'empresas/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$menu_model = $this->menus_model->get(array('select'=>'m.*', 'where'=>'m.id = '.$id, 'order_by'=>'m.id desc'))->row();
		$menus_empresas_model = $this->menus_model->get_menus_empresas($menu_model->id)->result_array();
		
		$menus_empresas = array();
		if(count($menus_empresas_model) > 0) {
			foreach ($menus_empresas_model as $value) {
				$menus_empresas[] = $value['empresas_id'];
			}
		}

		/* Validate form input */
        $this->form_validation->set_rules('nome', 'lang:create_menu_validation_nome_label', 'required|trim');
		$this->form_validation->set_rules('status', 'lang:create_menu_validation_status_label', 'required');
		$this->form_validation->set_rules('controlador', 'lang:create_menu_validation_controlador_label', 'required');
		$this->form_validation->set_rules('metodo', 'lang:create_menu_validation_metodo_label', 'required');
		$this->form_validation->set_rules('tipo', 'lang:create_menu_validation_tipo_label', 'required|is_numeric|greater_than_equal_to[0]|less_than_equal_to[1]');
		$this->form_validation->set_rules('order', 'lang:create_menu_validation_order_label', 'is_numeric');

		if (isset($_POST) && ! empty($_POST))
		{
			$pai_id = $this->input->post('pai_id');
			if($pai_id == "")
				$pai_id = null;
			$dados = array(
				"nome" => $this->input->post('nome'),
				"tipo" => $this->input->post('tipo'),
				"icon_class" => trim($this->input->post('icon_class')),
				"pai_id" => $pai_id,
				"ativo" => $this->input->post('status'),
				"order" => $this->input->post('order'),
				"controlador" => $this->input->post('controlador'),
				"metodo" => $this->input->post('metodo')
			);
			if ($this->form_validation->run() == TRUE)
			{
				$menu_update = $this->menus_model->update($id, $dados, $this->input->post('empresas'));

				if ($menu_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_menu_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->ion_auth->errors()));
				}

				redirect('menus', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->ion_auth->errors() ? show_alert_error($this->ion_auth->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['menu_model'] = $menu_model;

        $todos_menus = $this->menus_model->get(array('select'=>'m.*', 'order_by'=>'m.tipo asc, m.order asc'))->result_array();
        $menuFinal = [];
		construirMenu($todos_menus, $menuFinal, null);

        $html_menu = '<ul id="menu_pai">';
		foreach($menuFinal as $menu){
			$html_menu .= getHtml($menu, $menu_model->pai_id);
		}
		$html_menu .= '</ul>';

        $this->data['menu'] = $html_menu;
        $this->data['menus_tree'] = $menuFinal;
        $this->data['todos_menus'] = $todos_menus;
        $this->data['pais_options'] = get_menus_hierarquia_options($todos_menus, $menu_model->id);
        $this->data['pai_id_selecionado'] = ($this->input->post('pai_id') !== null ? $this->input->post('pai_id') : $menu_model->pai_id);

        $nome_pai_selecionado = 'Nenhum (Menu Raiz / 1º Nível)';
        if (!empty($this->data['pai_id_selecionado'])) {
            foreach ($todos_menus as $m) {
                if ((int)$m['id'] === (int)$this->data['pai_id_selecionado']) {
                    $nome_pai_selecionado = $m['nome'];
                    break;
                }
            }
        }
        $this->data['nome_pai_selecionado'] = $nome_pai_selecionado;
        $this->data['menu_atual_id'] = $menu_model->id;

        $this->data['controladores'] = get_controladores();
        $this->data['pai_id'] = $menu_model->pai_id;

		$this->data['icon_class'] = array(
			'name'  => 'icon_class',
			'id'    => 'icon_class',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('icon_class', $menu_model->icon_class)
		);

		$this->data['nome'] = array(
			'name'  => 'nome',
			'id'    => 'nome',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('nome', $menu_model->nome)
		);

		$this->data['tipo'] = array(
			'name'  => 'tipo',
			'id'    => 'tipo',
            'class' => 'form-control',
            'options' => array("0"=>lang('menu_main_navigation'), "1"=>lang('menu_administration')),
			'selected' => ($this->input->post('tipo') ? $this->input->post('tipo') : $menu_model->tipo),
		);

		$controlador_options = array(""=>"Selecione");
		foreach (get_controladores() as $controlador) {
			$controlador_options[strtolower($controlador)] = $controlador;
		}
		$this->data['controlador'] = array(
			'name'  => 'controlador',
			'id'    => 'controlador',
            'class' => 'form-control',
            'options' => $controlador_options,
			'selected' => $this->form_validation->set_value('controlador', $menu_model->controlador),
			'onchange' => 'getMetodos()'
		);
		$metodo_options = array();
		foreach (get_metodos($this->form_validation->set_value('controlador', $menu_model->controlador)) as $metodo) {
			$metodo_options[strtolower($metodo)] = $metodo;
		}
		$this->data['metodo'] = array(
			'name'  => 'metodo',
			'id'    => 'metodo',
            'class' => 'form-control',
            'options' => $metodo_options,
			'selected' => $this->form_validation->set_value('metodo', $menu_model->metodo),
		);
		$empresas_options = array();
		foreach ($this->menus_model->get_empresas()->result() as $emp) {
			$empresas_options[$emp->id] = $emp->nome;
		}
		$this->data['empresas_options'] = $empresas_options;
		$this->data['menus_empresas'] = ($this->input->post('empresas') ? $this->input->post('empresas') : $menus_empresas);
		$this->data['empresas'] = array(
			'name'  => 'empresas[]',
			'id'    => 'empresas',
            'class' => 'form-control multipleSelect',
            'options' => $empresas_options,
			'selected' => ($this->input->post('empresas') ? $this->input->post('empresas') : $menus_empresas),
		);


		$this->data['order'] = array(
			'name'  => 'order',
			'id'    => 'order',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('order', $menu_model->order)
		);

		$this->data['status'] = $this->form_validation->set_value('status', $menu_model->ativo);

        /* Load Template */
        $this->template->admin_render('admin/menus/edit', $this->data);
	}

	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("menus", "activate"))
		{
			$activation = $this->menus_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->menus_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->menus_model->messages()));
		redirect('menus', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("menus", "deactivate"))
		{
			$deactivation = $this->menus_model->deactivate($id);
		}
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->menus_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->menus_model->messages()));
		redirect('menus', 'refresh');
	}

	public function buscar_metodos()
	{
		get_controladores();
		if($_POST['controlador'] != "") {
			$metodos = get_metodos($_POST['controlador']);
			foreach ($metodos as $metodo) {
				$metodo_options[strtolower($metodo)] = $metodo;
			}
		}
		echo json_encode($metodo_options);
		exit;
	}


    public function busca_sidebar()
    {
        $menuFinal = [];
        construirMenuSidebar($this->menus_model->get(array('select'=>'m.*', 'join'=>array(array('menus_empresas me','me.menus_id = m.id','join'), array('menus_modulos mm','mm.menu_id = m.id','join')), 'where'=>'m.ativo=1 AND me.empresas_id='.$this->session->userdata('empresa_id').' AND mm.modulo_id = '.$this->session->userdata('modulo_id'), 'order_by'=>'m.tipo asc, m.order asc'))->result_array(), $menuFinal, null);
        adicionarSubmenusKanban($menuFinal);

        $user_groups = $this->ion_auth->get_users_groups();
        $user_groups_array = array();
        foreach ($user_groups->result() as $group) {
            if(is_array(unserialize($group->permissoes)))
                $user_groups_array = array_merge(unserialize($group->permissoes), $user_groups_array);
        }
        $html_menu = "";
        $last_tipo = "";

        // exit("<pre>".print_r($menuFinal,true)."</pre>");

        foreach($menuFinal as $menu){
            if($menu['tipo'] != $last_tipo && $menu['tipo'] == 0)
                $html_menu .= '<li class="header text-uppercase">'.lang('menu_main_navigation').'</li>';
            elseif($menu['tipo'] != $last_tipo && $menu['tipo'] == 1)
                $html_menu .= '<li class="header text-uppercase">'.lang('menu_administration').'</li>';

            if($_POST["q"] != "")
        		$html_menu .= getHtmlSidebarAjax($menu, null, $user_groups_array, $_POST["q"]);
        	else
        		$html_menu .= getHtmlSidebar($menu, null, $user_groups_array);
            
            $last_tipo = $menu['tipo'];
        }

        exit($html_menu);
    }
}
