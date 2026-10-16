<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_podcasts extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/app_podcasts');
        $this->load->model('app_podcasts_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_app_podcasts'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_app_podcasts'), 'app_podcasts');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_podcasts", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $where = "";
            if($_POST) {
            	if($this->input->post('livro')) {
            		$where .= "p.livro = '".$this->input->post('livro')."'";
            	}
            }
            $this->data['app_podcasts'] = $this->app_podcasts_model->get(null, $where)->result();
            $livros = $this->app_podcasts_model->get_livro()->result();
            $livros_array = array(""=>"Selecione");
            foreach ($livros as $value) {
            	$livros_array[$value->id] = $value->titulo;
            }
            $this->data['livros'] = array(
                'name'  => 'livro',
                'id'    => 'livro',
                'class' => 'form-control',
                'options' => $livros_array,
                'selected' => ($this->input->post('livro')) ? $this->input->post('livro') : ''
            );
            $this->data['message'] = $this->session->flashdata('message');
            /* Load Template */
            $this->template->admin_render('admin/app/podcasts/index', $this->data);
        }
    }

    public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_podcasts", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_podcasts_create'), 'app_podcasts/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('livro', 'lang:validation_livro_label', 'required');
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');
		$this->form_validation->set_rules('tipo', 'lang:validation_tipo_label', 'required');
		$this->form_validation->set_rules('publicacao', 'lang:validation_publicacao_label', 'required');
		if (empty($_FILES['arquivo']['name'])) {
		    $this->form_validation->set_rules('arquivo', 'lang:validation_arquivo_label', 'required');
		}
		if (empty($_FILES['imagem']['name'])) {
		    $this->form_validation->set_rules('imagem', 'lang:validation_imagem_label', 'required');
		}

		if ($this->form_validation->run() == TRUE && (isset($_FILES) && ! empty($_FILES['arquivo']['name'])))
		{
			if(!file_exists(FCPATH.'upload/podcasts/midia/')) {
				mkdir(FCPATH.'upload/podcasts/midia/', 0777, true);
			}
			if(!file_exists(FCPATH.'upload/podcasts/imagens/')) {
				mkdir(FCPATH.'upload/podcasts/imagens/', 0777, true);
			}
			$max_size = 10;
	        $config['upload_path']          = FCPATH.'upload/podcasts/midia/';
	        $config['allowed_types']        = 'mp3';
        	$config['max_size']         	= (1024*1024)*$max_size;

			$old_name_midia = $_FILES['arquivo']['name'];
			$new_name_midia = md5(uniqid(time()));
			$config['file_name'] = $new_name_midia;

			$this->load->library('upload', $config);

			if ($this->upload->do_upload('arquivo'))
			{
				$dados = array(
					"livro" => $this->input->post("livro"),
					"titulo" => $this->input->post("titulo"),
					"tipo" => $this->input->post("tipo"),
					"texto" => $this->input->post("texto"),
					"ativo" => $this->input->post("status"),
					"publicacao" => $this->input->post("publicacao"),
					"data_publicacao" => date('Y-m-d H:i:s'),
					"caminho_midia" => 'upload/podcasts/midia/'.$this->upload->data('file_name'),
					"titulo_midia" => $old_name_midia
				);

				$old_name_img = $_FILES['imagem']['name'];
				$new_name_img = md5(uniqid(time()));
				$config = array();
				$config['file_name'] = $new_name_img;
				$max_size = 2;
		        $config['upload_path']          = FCPATH.'upload/podcasts/imagens/';
		        $config['allowed_types']        = 'jpg|png|jpeg';
		    	$config['max_size']         	= (1024*1024)*$max_size;

				$this->upload->initialize($config);

				if($this->upload->do_upload('imagem')) {
					$dados["caminho_img"] = 'upload/podcasts/imagens/'.$this->upload->data('file_name');
					$dados["titulo_imagem"] = $old_name_img;

					$sizes = getImgSizes();

				   	$this->load->library('image_lib');
					foreach($sizes as $_a => $_b) {
						$config = array();
						$max_size = 2;
				        $config['upload_path']   = FCPATH.'upload/podcasts/imagens/';
				        $config['allowed_types'] = 'jpg|png|jpeg';
				    	$config['max_size']      = (1024*1024)*$max_size;
				    	$config['file_name'] 	 = $new_name_img."_".$_a;
				    	// exit("<pre>".print_r($config,true)."</pre>");
						$this->upload->initialize($config);
						
						if($this->upload->do_upload('imagem')) {
							$configB = array();
							$configB['image_library'] = 'gd2';        
						   	$configB['source_image']  = $config['upload_path'].$this->upload->data('file_name'); 
						   	$configB['maintain_ratio'] = TRUE;
						   	$configB['width'] = $_b["width"];
						   	$configB['height'] = $_b["height"];
						   	$this->image_lib->initialize($configB);
						   	$this->image_lib->resize();
					   	}
					}
				}

				if($id = $this->app_podcasts_model->create($dados)) {
					$this->session->set_flashdata('message', show_alert_success($this->app_podcasts_model->messages()));
				}
				else
					$this->session->set_flashdata('message', show_alert_error($this->app_podcasts_model->messages()));
			} else {
				$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
			}
			redirect('app_podcasts', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_podcasts_model->errors() ? show_alert_error($this->app_podcasts_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo'),
				'maxlength' => '100'
			);
			$this->data['texto'] = array(
				'name'  => 'texto',
				'id'    => 'texto',
			    'rows'        => '5',
			    'cols'        => '10',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('texto')
			);
			$livros = $this->app_podcasts_model->get_livro()->result();
			$livros_array = array(""=>"Selecione");
			foreach ($livros as $value) {
				$livros_array[$value->id] = $value->titulo;
			}
            $this->data['livro'] = array(
                'name'  => 'livro',
                'id'    => 'livro',
                'class' => 'form-control',
                'onchange' => "loadUnidadesLivro(this.value, '".base_url()."')",
                'options' => $livros_array,
                'selected' => (count($this->input->post('livro')) > 0) ? $this->input->post('livro') : ''
            );

            /* Load Template */
            $this->template->admin_render('admin/app/podcasts/create', $this->data);
		}
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_podcasts", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_podcasts_edit'), 'app_podcasts/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $app_podcast = $this->app_podcasts_model->get($id)->row();

		/* Validate form input */
		$this->form_validation->set_rules('livro', 'lang:validation_livro_label', 'required');
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');
		$this->form_validation->set_rules('tipo', 'lang:validation_tipo_label', 'required');
		$this->form_validation->set_rules('publicacao', 'lang:validation_publicacao_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"livro" => $this->input->post("livro"),
				"titulo" => $this->input->post("titulo"),
				"tipo" => $this->input->post("tipo"),
				"texto" => $this->input->post("texto"),
				"ativo" => $this->input->post("status"),
				"publicacao" => $this->input->post("publicacao")
			);

			if(isset($_FILES)) {
				$this->load->library('upload');
			}

			if(isset($_FILES) && !empty($_FILES['arquivo']['name'])) {
				$max_size = 10;
		        $config['upload_path']          = FCPATH.'upload/podcasts/midia/';
		        $config['allowed_types']        = 'mp3';
	        	$config['max_size']         	= (1024*1024)*$max_size;

				$old_name_midia = $_FILES['arquivo']['name'];
				$new_name_midia = md5(uniqid(time()));
				$config['file_name'] = $new_name_midia;

				$this->upload->initialize($config);

				if ($this->upload->do_upload('arquivo'))
				{
					if(file_exists(FCPATH.$app_podcast->caminho_midia)) {
						unlink(FCPATH.$app_podcast->caminho_midia);
					}
					
					$dados["caminho_midia"] = 'upload/podcasts/midia/'.$this->upload->data('file_name');
					$dados["titulo_midia"] = $old_name_midia;
				} else {
					$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
				}
			}

			if(isset($_FILES) && !empty($_FILES['imagem']['name'])) {
				$old_name_img = $_FILES['imagem']['name'];
				$new_name_img = md5(uniqid(time()));
				$config = array();
				$config['file_name'] = $new_name_img;
				$max_size = 2;
		        $config['upload_path']          = FCPATH.'upload/podcasts/imagens/';
		        $config['allowed_types']        = 'jpg|png|jpeg';
		    	$config['max_size']         	= (1024*1024)*$max_size;

				$this->upload->initialize($config);

				if($this->upload->do_upload('imagem')) {
					if(file_exists(FCPATH.$app_podcast->caminho_img)) {
						unlink(FCPATH.$app_podcast->caminho_img);
					}
					$nome_sem_ext = explode(".",$app_podcast->caminho_img);

					$sizes = getImgSizes();

					$dados["caminho_img"] = 'upload/podcasts/imagens/'.$this->upload->data('file_name');
					$dados["titulo_imagem"] = $old_name_img;

				   	$this->load->library('image_lib');
					foreach($sizes as $_a => $_b) {
						if(file_exists(FCPATH.$nome_sem_ext[0]."_".$_a.".".$nome_sem_ext[0])) {
							unlink(FCPATH.$nome_sem_ext[0]."_".$_a.".".$nome_sem_ext[0]);
						}

						$config = array();
						$max_size = 2;
				        $config['upload_path']   = FCPATH.'upload/podcasts/imagens/';
				        $config['allowed_types'] = 'jpg|png|jpeg';
				    	$config['max_size']      = (1024*1024)*$max_size;
				    	$config['file_name'] 	 = $new_name_img."_".$_a;
				    	// exit("<pre>".print_r($config,true)."</pre>");
						$this->upload->initialize($config);
						
						if($this->upload->do_upload('imagem')) {
							$configB = array();
							$configB['image_library'] = 'gd2';        
						   	$configB['source_image']  = $config['upload_path'].$this->upload->data('file_name'); 
						   	$configB['maintain_ratio'] = TRUE;
						   	$configB['width'] = $_b["width"];
						   	$configB['height'] = $_b["height"];
						   	$this->image_lib->initialize($configB);
						   	$this->image_lib->resize();
					   	}
					}
				}
			}

			if($id = $this->app_podcasts_model->update($id, $dados))
				$this->session->set_flashdata('message', show_alert_success($this->app_podcasts_model->messages()));
			
			redirect('app_podcasts', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_audios_model->errors() ? show_alert_error($this->app_audios_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo', $app_podcast->titulo),
				'maxlength' => '100'
			);
			$this->data['texto'] = array(
				'name'  => 'texto',
				'id'    => 'texto',
			    'rows'  => '5',
			    'cols'  => '10',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('texto', $app_podcast->texto)
			);
			$livros = $this->app_podcasts_model->get_livro()->result();
			$livros_array = array(""=>"Selecione");
			foreach ($livros as $value) {
				$livros_array[$value->id] = $value->titulo;
			}
            $this->data['livro'] = array(
                'name'  => 'livro',
                'id'    => 'livro',
                'class' => 'form-control',
                'onchange' => "loadUnidadesLivro(this.value, '".base_url()."')",
                'options' => $livros_array,
                'selected' => (count($this->input->post('livro')) > 0) ? $this->input->post('livro') : $app_podcast->livro
            );
			$this->data['podcast'] = $app_podcast;


            /* Load Template */
            $this->template->admin_render('admin/app/podcasts/edit', $this->data);
		}
	}

	function activate($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_podcasts", "activate"))
		{
			redirect('auth', 'refresh');
		}
        $id = (int) $id;
		$activation = $this->app_podcasts_model->activate($id);
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_podcasts_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_podcasts_model->messages()));
		redirect('app_podcasts', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_podcasts", "deactivate"))
		{
			redirect('auth', 'refresh');
		}
		$id = (int) $id;
		$deactivation = $this->app_podcasts_model->deactivate($id);
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_podcasts_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_podcasts_model->messages()));
		redirect('app_podcasts', 'refresh');
	}

	public function get_podcast_base64()
	{
		if($_POST["id"] == null) {
			return false;
		}

		$podcast = $this->app_podcasts_model->get($_POST["id"])->row();
		
		$path = FCPATH.$podcast->caminho_midia;

		exit('<p><b>Título: </b>'.$podcast->titulo.'</p><p><b>Livro: </b>'.$podcast->livro_titulo.'</p><audio preload="none" controls><source src="'.base_url($podcast->caminho_midia).'" type="audio/mp3"></audio><script>$( "audio" ).audioPlayer();</script>'); 
	}
}
