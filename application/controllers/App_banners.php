<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_banners extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/app_banners');
        $this->load->model('app_banners_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_app_banners'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_app_banners'), 'app_banners');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_banners", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['app_banners'] = $this->app_banners_model->get()->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/app/banners/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_banners", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_banners_create'), 'app_banners/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');
		if($this->input->post('periodo_indeterminado') != 'S') {
			$this->form_validation->set_rules('periodo_de', 'lang:validation_periodo_de_label', 'required|validar_data');
			$this->form_validation->set_rules('periodo_ate', 'lang:validation_periodo_ate_label', 'required|validar_data');
		}
		if (empty($_FILES['imagem']['name'])) {
		    $this->form_validation->set_rules('imagem', 'lang:validation_imagem_label', 'required');
		}

		if ($this->form_validation->run() == TRUE && (isset($_FILES) && ! empty($_FILES['imagem']['name'])))
		{
			$max_size = 35;
	        $config['upload_path']          = './upload/app_banner/';
	        $config['allowed_types']        = 'jpg|png|jpeg';
        	$config['max_size']         	= (2000*700)*$max_size;
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('imagem'))
			{
				$dados = array(
					"titulo" => $this->input->post("titulo"),
					"empresa_id" => $this->session->userdata("empresa_id"),
					"link" => $this->input->post("link"),
					"periodo_indeterminado" => (@$this->input->post("periodo_indeterminado") == 'S') ? 'S' : 'N',
					"inicio" => @convertDateBRToEU($this->input->post("periodo_de")),
					"fim" => @convertDateBRToEU($this->input->post("periodo_ate")),
					"data_publicacao" => ($this->input->post("data_publicacao") != "")? convertDateBRToEU($this->input->post("data_publicacao")) : null,
					"caminho" => $this->upload->data('file_name'),
					"actived" => $this->input->post("status"),
				);

				if($id = $this->app_banners_model->create($dados))
					$this->session->set_flashdata('message', show_alert_success($this->app_banners_model->messages()));
			} else {
				$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
			}
			redirect('app_banners', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_banners_model->errors() ? show_alert_error($this->app_banners_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
            if($_POST) {
            	$this->data['periodo_indeterminado_check'] = (@$this->input->post('periodo_indeterminado') == 'S') ? 1 : 0;
				$this->data['periodo_indeterminado'] = array(
					'name'  => 'periodo_indeterminado',
					'id'    => 'periodo_indeterminado',
	                'class' => 'checkbox',
	                'checked' => (@$this->input->post('periodo_indeterminado') == 'S') ? 1 : 0,
					'value' => 'S'
				);
            }
            else {
            	$this->data['periodo_indeterminado_check'] = 1;
				$this->data['periodo_indeterminado'] = array(
					'name'  => 'periodo_indeterminado',
					'id'    => 'periodo_indeterminado',
	                'class' => 'checkbox',
	                'checked' => 1,
					'value' => 'S'
				);
            }
            $this->data['data_publicacao'] = array(
				'name'  => 'data_publicacao',
				'id'    => 'data_publicacao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->input->post('data_publicacao')
			);
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo')
			);
			$this->data['link'] = array(
				'name'  => 'link',
				'id'    => 'link',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->input->post('link')
			);
			$this->data['periodo_de'] = array(
				'name'  => 'periodo_de',
				'id'    => 'periodo_de',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('periodo_de')
			);
			$this->data['periodo_ate'] = array(
				'name'  => 'periodo_ate',
				'id'    => 'periodo_ate',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('periodo_ate')
			);

            /* Load Template */
            $this->template->admin_render('admin/app/banners/create', $this->data);
		}
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("niveis", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/niveis/delete');
        }
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_banners", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_banners_edit'), 'app_banners/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $app_banner = $this->app_banners_model->get($id)->row();

		/* Validate form input */
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');
		if($this->input->post('periodo_indeterminado') != 'S') {
			$this->form_validation->set_rules('periodo_de', 'lang:validation_periodo_de_label', 'required|validar_data');
			$this->form_validation->set_rules('periodo_ate', 'lang:validation_periodo_ate_label', 'required|validar_data');
		}

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"titulo" => $this->input->post("titulo"),
				"empresa_id" => $this->session->userdata("empresa_id"),
				"link" => $this->input->post("link"),
				"periodo_indeterminado" => (@$this->input->post("periodo_indeterminado") == 'S') ? 'S' : 'N',
				"inicio" => @convertDateBRToEU($this->input->post("periodo_de")),
				"fim" => @convertDateBRToEU($this->input->post("periodo_ate")),
				"data_publicacao" => ($this->input->post("data_publicacao") != "") ? convertDateBRToEU($this->input->post("data_publicacao")) : null,
				"actived" => $this->input->post("status"),
			);

			if(isset($_FILES) && !empty($_FILES['imagem']['name'])) {
				$max_size = 35;
		        $config['upload_path']          = './upload/app_banner/';
		        $config['allowed_types']        = 'jpg|png|jpeg';
	        	$config['max_size']             = (2000*700)*$max_size;
				$this->load->library('upload', $config);

				if ($this->upload->do_upload('imagem'))
				{
					unlink(FCPATH."upload/app_banner/".$app_banner->caminho);
					$dados["caminho"] = $this->upload->data('file_name');
				}
			}

			if($id = $this->app_banners_model->update($id, $dados))
				$this->session->set_flashdata('message', show_alert_success($this->app_banners_model->messages()));
			redirect('app_banners', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_banners_model->errors() ? show_alert_error($this->app_banners_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status', $app_banner->actived);
            if($_POST) {
            	$this->data['periodo_indeterminado_check'] = (@$this->input->post('periodo_indeterminado') == 'S') ? 1 : 0;
				$this->data['periodo_indeterminado'] = array(
					'name'  => 'periodo_indeterminado',
					'id'    => 'periodo_indeterminado',
	                'class' => 'checkbox',
	                'checked' => (@$this->input->post('periodo_indeterminado') == 'S') ? 1 : 0,
					'value' => 'S'
				);
            }
            else {
            	$this->data['periodo_indeterminado_check'] = ($app_banner->periodo_indeterminado == 'S') ? 1 : 0;
				$this->data['periodo_indeterminado'] = array(
					'name'  => 'periodo_indeterminado',
					'id'    => 'periodo_indeterminado',
	                'class' => 'checkbox',
	                'checked' => ($app_banner->periodo_indeterminado == 'S') ? 1 : 0,
					'value' => 'S'
				);
            }
            $this->data['data_publicacao'] = array(
				'name'  => 'data_publicacao',
				'id'    => 'data_publicacao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('data_publicacao')) ? $this->input->post('data_publicacao') : convertDateEUToBR($app_banner->data_publicacao)
			);
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo', $app_banner->titulo)
			);
			$this->data['link'] = array(
				'name'  => 'link',
				'id'    => 'link',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('link'))?$this->input->post('link'):$app_banner->link
			);
			$this->data['periodo_de'] = array(
				'name'  => 'periodo_de',
				'id'    => 'periodo_de',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('periodo_de', convertDateEUToBR($app_banner->inicio))
			);
			$this->data['periodo_ate'] = array(
				'name'  => 'periodo_ate',
				'id'    => 'periodo_ate',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('periodo_ate', convertDateEUToBR($app_banner->fim))
			);
			$this->data['caminho'] = $app_banner->caminho;

            /* Load Template */
            $this->template->admin_render('admin/app/banners/edit', $this->data);
		}
	}

	function activate($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_banners", "activate"))
		{
			redirect('auth', 'refresh');
		}
        $id = (int) $id;
		$activation = $this->app_banners_model->activate($id);
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_banners_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_banners_model->messages()));
		redirect('app_banners', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_banners", "deactivate"))
		{
			redirect('auth', 'refresh');
		}
		$id = (int) $id;
		$deactivation = $this->app_banners_model->deactivate($id);
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_banners_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_banners_model->messages()));
		redirect('app_banners', 'refresh');
	}
}
