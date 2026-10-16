<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Materiais extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/materiais');
        $this->load->model('materiais_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_materiais'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_materiais'), 'materiais');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("materiais", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $materiais = $this->materiais_model->get()->result_array();
            foreach ($materiais as $key => $value) {
            	$materiais[$key]["grupos"] = $this->materiais_model->get_groups_by_in(implode(",", unserialize($value["grupos"])));
            }
            $this->data['materiais'] = $materiais;
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/materiais/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("materiais", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_materiais_create'), 'materiais/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');
		//$this->form_validation->set_rules('grupos', 'lang:validation_grupos_label', 'required');
		if (empty($_FILES['arquivo']['name'])) {
		    $this->form_validation->set_rules('arquivo', 'lang:validation_arquivo_label', 'required');
		}

		if ($this->form_validation->run() == TRUE && (isset($_FILES) && ! empty($_FILES['arquivo']['name'])))
		{
			$max_size = 35;
	        $config['upload_path']          = './upload/materiais/';
	        $config['allowed_types']        = 'jpg|png|jpeg|zip|rar|pdf|doc|docx|xls|xlsx|ppt|pptx';
        	$config['max_size']         	= (2000*700)*$max_size;
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('arquivo'))
			{
				$dados = array(
					"titulo" => $this->input->post("titulo"),
					"empresa_id" => $this->session->userdata("empresa_id"),
					"grupos" => serialize($this->input->post("grupos")),
					"unidade" => $this->input->post("unidade"),
					"livro" => $this->input->post("livro"),
					"caminho" => $this->upload->data('file_name'),
					"actived" => $this->input->post("status")
				);

				if($id = $this->materiais_model->create($dados))
					$this->session->set_flashdata('message', show_alert_success($this->materiais_model->messages()));
			} else {
				$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
			}
			redirect('materiais', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->materiais_model->errors() ? show_alert_error($this->materiais_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
            
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo')
			);
			$group_options = array();
            $group_select_options = array(""=>"Selecione");
            foreach ($this->materiais_model->get_groups() as $key => $g) {
                $group_options[$key] = $g;
                $group_select_options[$key] = $g;
            }
            $this->data['grupos'] = array(
                'name'  => 'grupos[]',
                'id'    => 'grupos',
                'class' => 'multipleSelect',
                'multiple' => 'multiple',
                'options' => $group_options,
                'selected' => $this->form_validation->set_value('grupos'),
            );

			/* Load dados livros e unidades*/
			$livros_array = array(""=>"Selecione");
            foreach ($this->materiais_model->get_livros()->result() as $livro) {
            	$livros_array[$livro->id] = $livro->titulo;
            }
			$this->data['livro'] = array(
				'name'  => 'livro',
				'id'    => 'livro',
                'class' => 'form-control',
				'options' => $livros_array,
  				'selected' => $this->form_validation->set_value('livro')
			);

			$unidades_arr = array(""=>"Selecione um livro");
			if($this->input->post('livro')) {
				$unidades = $this->materiais_model->get_unidades_livro($_POST["livro"]);
				foreach ($unidades->result() as $value) {
					$unidades_arr[$value->id] = $value->unidade." - ".$value->titulo;
				}
			}
			$this->data['unidade'] = array(
				'name'  => 'unidade',
				'id'    => 'unidade',
                'class' => 'form-control',
				'options' => $unidades_arr,
  				'selected' => $this->form_validation->set_value('unidade')
			);

            /* Load Template */
            $this->template->admin_render('admin/materiais/create', $this->data);
		}
	}

	// public function delete()
	// {
 //        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("niveis", "delete"))
 //        {
 //            redirect('auth/login', 'refresh');
 //        }
 //        elseif ( ! $this->ion_auth->is_admin())
	// 	{
 //            return show_error('You must be an administrator to view this page.');
 //        }
 //        else
 //        {
 //            $this->load->view('admin/niveis/delete');
 //        }
	// }

	public function edit($id)
{
	if (! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("materiais", "edit"))
	{
		redirect('auth', 'refresh');
	}

	/* Breadcrumbs */
	$this->breadcrumbs->unshift(2, lang('menu_materiais_edit'), 'materiais/edit');
	$this->data['breadcrumb'] = $this->breadcrumbs->show();

	$material = $this->materiais_model->get($id)->row();

	/* Validate form input */
	$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
	$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');

	if ($this->form_validation->run() == TRUE)
	{
		$dados = array(
			"titulo" => $this->input->post("titulo"),
			"grupos" => serialize($this->input->post("grupos")),
			"unidade" => $this->input->post("unidade") ?: null,  // Grava unidade como null se estiver vazia
			"livro" => $this->input->post("livro"),
			"actived" => $this->input->post("status")
		);

		if (isset($_FILES) && !empty($_FILES['arquivo']['name'])) {
			$max_size = 35;
			$config['upload_path'] = './upload/materiais/';
			$config['allowed_types'] = 'jpg|png|jpeg|zip|rar|pdf';
			$config['max_size'] = (2000 * 700) * $max_size;
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('arquivo'))
			{
				unlink(FCPATH . "upload/materiais/" . $material->caminho);
				$dados["caminho"] = $this->upload->data('file_name');
			}
		}

		if ($id = $this->materiais_model->update($id, $dados))
			$this->session->set_flashdata('message', show_alert_success($this->materiais_model->messages()));
		redirect('materiais', 'refresh');
	}
	else
	{
		if ($_POST)
			$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->materiais_model->errors() ? show_alert_error($this->materiais_model->errors()) : show_alert_error($this->session->flashdata('message'))));

		$this->data['status'] = $this->form_validation->set_value('status', $material->actived);
		$this->data['titulo'] = array(
			'name'  => 'titulo',
			'id'    => 'titulo',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => $this->form_validation->set_value('titulo', $material->titulo)
		);
		$this->data['materiais_model'] = $material;
		$group_options = array();
		$group_select_options = array(""=>"Selecione");
		foreach ($this->materiais_model->get_groups() as $key => $g) {
			$group_options[$key] = $g;
			$group_select_options[$key] = $g;
		}
		$this->data['grupos'] = array(
			'name'  => 'grupos[]',
			'id'    => 'grupos',
			'class' => 'multipleSelect',
			'multiple' => 'multiple',
			'options' => $group_options,
			'selected' => $this->form_validation->set_value('grupos', unserialize($material->grupos)),
		);

		/* Load dados livros e unidades*/
		$livros_array = array(""=>"Selecione");
		foreach ($this->materiais_model->get_livros()->result() as $livro) {
			$livros_array[$livro->id] = $livro->titulo;
		}
		$this->data['livro'] = array(
			'name'  => 'livro',
			'id'    => 'livro',
			'class' => 'form-control',
			'options' => $livros_array,
			  'selected' => $this->form_validation->set_value('livro')
		);

		$unidades_arr = array(""=>"Selecione um livro");
		if($this->input->post('livro')) {
			$unidades = $this->materiais_model->get_unidades_livro($_POST["livro"]);
			foreach ($unidades->result() as $value) {
				$unidades_arr[$value->id] = $value->unidade." - ".$value->titulo;
			}
		}
		$this->data['unidade'] = array(
			'name'  => 'unidade',
			'id'    => 'unidade',
			'class' => 'form-control',
			'options' => $unidades_arr,
			  'selected' => $this->form_validation->set_value('unidade')
		);

		$this->data['caminho'] = $material->caminho;

		/* Load Template */
		$this->template->admin_render('admin/materiais/edit', $this->data);
	}
}

	function activate($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("materiais", "activate"))
		{
			redirect('auth', 'refresh');
		}
        $id = (int) $id;
		$activation = $this->materiais_model->activate($id);
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->materiais_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->materiais_model->messages()));
		redirect('materiais', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("materiais", "deactivate"))
		{
			redirect('auth', 'refresh');
		}
		$id = (int) $id;
		$deactivation = $this->materiais_model->deactivate($id);
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->materiais_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->materiais_model->messages()));
		redirect('materiais', 'refresh');
	}
}
