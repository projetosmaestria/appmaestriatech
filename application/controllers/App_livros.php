<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_livros extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/app_livros');
        $this->load->model('app_livros_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_app_livros'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_app_livros'), 'app_livros');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_livros", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['livros'] = $this->app_livros_model->get()->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/app/livros/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_livros", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_livros_create'), 'app_livros/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"LIG_ID_CURSO_LIVRO" => $this->input->post("curso"),
				"LIG_ID_DISCIPLINA" => $this->input->post("disciplina"),
				"titulo" => $this->input->post("titulo"),
				"cor" => $this->input->post("group_bgcolor"),
				"autor" => $this->input->post("autor"),
				"editora" => $this->input->post("editora"),
				"nr_edicao" => $this->input->post("nr_edicao"),
				"ano_publicacao" => $this->input->post("ano_publicacao"),
				"ativo" => $this->input->post("status"),
				"empresa" => $this->session->userdata("empresa_id"),
				"data_cadastro" => date('Y-m-d H:i:s')
			);

			if($id = $this->app_livros_model->create($dados))
				$this->session->set_flashdata('message', show_alert_success($this->app_livros_model->messages()));
			else
				$this->session->set_flashdata('message', show_alert_error($this->app_livros_model->messages()));

			redirect('app_livros', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_livros_model->errors() ? show_alert_error($this->app_livros_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
			
			$cursos_array = array("" => "Selecione");
			$cursos = $this->app_livros_model->get_curso();
			foreach ($cursos->result() as $curso) {
				$cursos_array[$curso->ID_CURSO] = $curso->DESCRICAO;
			}

			$this->data['curso'] = array(
				'name'     => 'curso',
				'id'       => 'curso',
				'class'    => 'form-control',
				'options'  => $cursos_array,
				'selected' => $this->form_validation->set_value('curso')
			);


            $disciplinas_array = array(""=>"Selecione");
            foreach ($this->app_livros_model->get_disciplinas()->result() as $disciplina) {
            	$disciplinas_array[$disciplina->ID_DISCIPLINA] = $disciplina->DESCRICAO;
            }

			$this->data['disciplina'] = array(
				'name'  => 'disciplina',
				'id'    => 'disciplina',
                'class' => 'form-control',
				'options' => $disciplinas_array,
  				'selected' => $this->form_validation->set_value('disciplina')
			);

			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo')
			);
			$this->data['autor'] = array(
				'name'  => 'autor',
				'id'    => 'autor',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('autor')) ? $this->input->post('autor') : ''
			);
			$this->data['editora'] = array(
				'name'  => 'editora',
				'id'    => 'editora',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('editora')) ? $this->input->post('editora') : ''
			);
			$this->data['nr_edicao'] = array(
				'name'  => 'nr_edicao',
				'id'    => 'nr_edicao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('nr_edicao')) ? $this->input->post('nr_edicao') : ''
			);
			$this->data['ano_publicacao'] = array(
				'name'  => 'ano_publicacao',
				'id'    => 'ano_publicacao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('ano_publicacao')) ? $this->input->post('ano_publicacao') : ''
			);
			$this->data['group_bgcolor'] = array(
				'type'     => 'text',
				'name'     => 'group_bgcolor',
				'id'       => 'group_bgcolor',
				'value'    => ($this->input->post('group_bgcolor')) ? $this->input->post('group_bgcolor') : '',
				'data-src' => "#00000",
	            'class'    => 'form-control'
			);

            /* Load Template */
            $this->template->admin_render('admin/app/livros/create', $this->data);
		}
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_livros", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_livros_edit'), 'app_livros/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $livro = $this->app_livros_model->get($id)->row();
        // exit("<pre>".print_r($this->input->post(),true)."</pre>");
		/* Validate form input */
		
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"LIG_ID_CURSO_LIVRO" => $this->input->post("curso"),
				"LIG_ID_DISCIPLINA" => $this->input->post("disciplina"),
				"titulo" => $this->input->post("titulo"),
				"cor" => $this->input->post("group_bgcolor"),
				"autor" => $this->input->post("autor"),
				"editora" => $this->input->post("editora"),
				"nr_edicao" => $this->input->post("nr_edicao"),
				"ano_publicacao" => $this->input->post("ano_publicacao"),
				"ativo" => $this->input->post("status")
			);

			if($this->app_livros_model->update($id, $dados))
				$this->session->set_flashdata('message', show_alert_success($this->app_livros_model->messages()));
			else
				$this->session->set_flashdata('message', show_alert_error($this->app_livros_model->messages()));

			redirect('app_livros', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_livros_model->errors() ? show_alert_error($this->app_livros_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status', $livro->ativo);

			$cursos_array = array("" => "Selecione");
			$cursos = $this->app_livros_model->get_curso();
			foreach ($cursos->result() as $curso) {
				$cursos_array[$curso->ID_CURSO] = $curso->DESCRICAO;
			}

			$this->data['curso'] = array(
				'name'     => 'curso',
				'id'       => 'curso',
				'class'    => 'form-control',
				'options'  => $cursos_array,
				'selected' => $this->form_validation->set_value('curso', $livro->LIG_ID_CURSO_LIVRO)
			);


            $disciplinas_array = array(""=>"Selecione");
            foreach ($this->app_livros_model->get_disciplinas()->result() as $disciplina) {
            	$disciplinas_array[$disciplina->ID_DISCIPLINA] = $disciplina->DESCRICAO;
            }


			$this->data['disciplina'] = array(
				'name'  => 'disciplina',
				'id'    => 'disciplina',
                'class' => 'form-control',
				'options' => $disciplinas_array,
  				'selected' => $this->form_validation->set_value('disciplina', $livro->LIG_ID_DISCIPLINA)
			);

			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo', $livro->titulo)
			);
			$this->data['autor'] = array(
				'name'  => 'autor',
				'id'    => 'autor',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('autor')) ? $this->input->post('autor') : $livro->autor
			);
			$this->data['editora'] = array(
				'name'  => 'editora',
				'id'    => 'editora',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('editora')) ? $this->input->post('editora') : $livro->editora
			);
			$this->data['nr_edicao'] = array(
				'name'  => 'nr_edicao',
				'id'    => 'nr_edicao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('nr_edicao')) ? $this->input->post('nr_edicao') : $livro->nr_edicao
			);
			$this->data['ano_publicacao'] = array(
				'name'  => 'ano_publicacao',
				'id'    => 'ano_publicacao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('ano_publicacao')) ? $this->input->post('ano_publicacao') : $livro->ano_publicacao
			);
			$this->data['group_bgcolor'] = array(
				'type'     => 'text',
				'name'     => 'group_bgcolor',
				'id'       => 'group_bgcolor',
				'value'    => ($this->input->post('group_bgcolor')) ? $this->input->post('group_bgcolor') : $livro->cor,
				'data-src' => $livro->cor,
	            'class'    => 'form-control'
			);

            /* Load Template */
            $this->template->admin_render('admin/app/livros/edit', $this->data);
		}
	}

    function activate($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_livros", "activate"))
		{
			redirect('auth', 'refresh');
		}
        $id = (int) $id;
		$activation = $this->app_livros_model->activate($id);
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_livros_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_livros_model->messages()));
		redirect('app_livros', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_livros", "deactivate"))
		{
			redirect('auth', 'refresh');
		} 
		$id = (int) $id;
		$deactivation = $this->app_livros_model->deactivate($id); 
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_livros_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_livros_model->messages()));
		redirect('app_livros', 'refresh');
	}
}
