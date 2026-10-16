<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_unidades_livro extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/app_unidades_livro');
        $this->load->model('app_unidades_livro_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_app_unidades_livro'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_app_unidades_livro'), 'app_unidades_livro');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_unidades_livro", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['unidades_livro'] = $this->app_unidades_livro_model->get(null, @$_POST["livro"], @$_POST["tipo"])->result();
            $this->data['message'] = $this->session->flashdata('message');

            $livros_array = array(""=>"Selecione");
            foreach ($this->app_unidades_livro_model->get_livros()->result() as $livro) {
            	$livros_array[$livro->id] = $livro->titulo;
            }
			$this->data['livro'] = array(
				'name'  => 'livro',
				'id'    => 'livro',
                'class' => 'form-control',
				'options' => $livros_array,
  				'selected' => $this->input->post('livro')
			);
			$tipo_arr = array(""=>"Selecione", "O"=>"Obrigatório", "C"=>"Complementar");
			$this->data['tipo'] = array(
				'name'  => 'tipo',
				'id'    => 'tipo',
                'class' => 'form-control',
				'options' => $tipo_arr,
  				'selected' => $this->input->post('tipo')
			);

            /* Load Template */
            $this->template->admin_render('admin/app/unidades_livro/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_unidades_livro", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_unidades_livro_create'), 'app_unidades_livro/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('unidade', 'lang:validation_unidade_label', 'trim|required|is_numeric');
		$this->form_validation->set_rules('livro', 'lang:validation_livro_label', 'required');
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('tipo', 'lang:validation_tipo_label', 'required');
		$this->form_validation->set_rules('grammar_file', 'lang:validation_grammar_file_label', 'trim|required');
		$this->form_validation->set_rules('texto_grammar_file', 'lang:validation_texto_grammar_file_label', 'trim|required');
		$this->form_validation->set_rules('tera_audio', 'lang:validation_tera_audio_label', 'trim|required');
		$this->form_validation->set_rules('tera_video', 'lang:validation_tera_video_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');

		if ($this->input->post("tera_audio") == "S") {
			if (empty($_FILES['audio']['name'])) {
		    	$this->form_validation->set_rules('audio', 'lang:validation_audio_label', 'required');
	    	}
		}
		if ($this->input->post("tera_video") == "S") {
			if (empty($_FILES['video']['name'])) {
		    	$this->form_validation->set_rules('video', 'lang:validation_video_label', 'required');
	    	}
		}

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"unidade" => $this->input->post("unidade"),
				"livro" => $this->input->post("livro"),
				"titulo" => $this->input->post("titulo"),
				"grammar_file" => $this->input->post("grammar_file"),
				"texto_grammar" => $this->input->post("texto_grammar_file"),
				"audio_ativo" => $this->input->post("tera_audio"),
				"video_ativo" => $this->input->post("tera_video"),
				"ativo" => $this->input->post("status"),
				"data_cadastro" => date('Y-m-d H:i:s'),
				"tipo" => $this->input->post("tipo"),
				"video_yt" => (count(array_filter($this->input->post("video_yt[]"))) > 0 && is_array(array_filter($this->input->post("video_yt[]")))) ? serialize($this->input->post("video_yt[]")) : ''
			);

			if($id = $this->app_unidades_livro_model->create($dados)){
				if($this->input->post("tera_audio") == "S" || $this->input->post("tera_video") == "S") {
					if(!file_exists(FCPATH.'upload/unidade/midia/'.$dados["livro"].'/')) {
	   					mkdir(FCPATH.'upload/unidade/midia/'.$dados["livro"].'/', 0777, true);
	     			}
				}

				if($this->input->post("tera_audio") == "S") {
					$max_size = 3;
			        $config['upload_path']          = './upload/unidade/midia/'.$dados["livro"].'/';
			        $config['allowed_types']        = 'mp3';
		        	$config['max_size']         	= (2000*700)*$max_size;
		        	$config['file_name'] 			= md5(uniqid(time()));
					$this->load->library('upload', $config);

					if ($this->upload->do_upload('audio'))
					{
						$dados["audio"] = 'upload/unidade/midia/'.$dados["livro"].'/'.$this->upload->data('file_name');	
						$dados["audio_titulo"] = $_FILES['audio']['name'];
						$dados["audio_transcrito"] = $this->input->post("texto_audio");
					} else {
						$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
					}
				}

				if($this->input->post("tera_video") == "S") {
					$max_size = 50;
			        $config['upload_path']          = './upload/unidade/midia/'.$dados["livro"].'/';
			        $config['allowed_types']        = 'mp3';
		        	$config['max_size']         	= (2000*700)*$max_size;
		        	$config['file_name'] 			= md5(uniqid(time()));
					$this->load->library('upload', $config);

					if ($this->upload->do_upload('video'))
					{
						$dados["video"] = 'upload/unidade/midia/'.$dados["livro"].'/'.$this->upload->data('file_name');	
						$dados["video_titulo"] = $_FILES['video']['name'];
					} else {
						$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
					}
				}

				if($this->input->post("tera_audio") == "S" || $this->input->post("tera_video") == "S") {
					$this->app_unidades_livro_model->update($id, $dados);
				}

				$this->session->set_flashdata('message', show_alert_success($this->app_unidades_livro_model->messages()));
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->app_unidades_livro_model->messages()));

			redirect('app_unidades_livro', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_unidades_livro_model->errors() ? show_alert_error($this->app_unidades_livro_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
            $this->data['tera_audio'] = $this->form_validation->set_value('tera_audio');
            $this->data['tera_video'] = $this->form_validation->set_value('tera_video');
            $this->data['tipo'] = $this->form_validation->set_value('tipo');

            $livros_array = array(""=>"Selecione");
            foreach ($this->app_unidades_livro_model->get_livros()->result() as $livro) {
            	$livros_array[$livro->id] = $livro->titulo;
            }
			$this->data['livro'] = array(
				'name'  => 'livro',
				'id'    => 'livro',
				'type'  => 'text',
                'class' => 'form-control',
				'options' => $livros_array,
  				'selected' => $this->form_validation->set_value('livro')
			);
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo')
			);
			$this->data['unidade'] = array(
				'name'  => 'unidade',
				'id'    => 'unidade',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('unidade')
			);
			$this->data['grammar_file'] = array(
				'name'  => 'grammar_file',
				'id'    => 'grammar_file',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('grammar_file')
			);
			$this->data['texto_grammar_file'] = array(
				'name'  => 'texto_grammar_file',
				'id'    => 'texto_grammar_file',
	            'rows' => '10',
	            'cols' => '80',
				'value' => $this->form_validation->set_value('texto_grammar_file')
			);
			$this->data['texto_audio'] = array(
				'name'  => 'texto_audio',
				'id'    => 'texto_audio',
                'class' => 'form-control',
                'disabled' => 1,
				'value' => ($this->input->post('texto_audio')) ? $this->input->post('texto_audio') : ''
			);

            /* Load Template */
            $this->template->admin_render('admin/app/unidades_livro/create', $this->data);
		}
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_unidades_livro", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_unidades_livro_edit'), 'app_unidades_livro/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $unidade = $this->app_unidades_livro_model->get($id)->row();
        // exit("<pre>".print_r($this->input->post("video_yt[]"),true)."</pre>");
		/* Validate form input */
		
		$this->form_validation->set_rules('unidade', 'lang:validation_unidade_label', 'trim|required|is_numeric');
		$this->form_validation->set_rules('livro', 'lang:validation_livro_label', 'required');
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('tipo', 'lang:validation_tipo_label', 'required');
		$this->form_validation->set_rules('grammar_file', 'lang:validation_grammar_file_label', 'trim|required');
		$this->form_validation->set_rules('texto_grammar_file', 'lang:validation_texto_grammar_file_label', 'trim|required');
		$this->form_validation->set_rules('tera_audio', 'lang:validation_tera_audio_label', 'trim|required');
		$this->form_validation->set_rules('tera_video', 'lang:validation_tera_video_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');

		if ($this->input->post("tera_audio") == "S" && $unidade->audio_ativo == "N") {
			if (empty($_FILES['audio']['name'])) {
		    	$this->form_validation->set_rules('audio', 'lang:validation_audio_label', 'required');
	    	}
		}
		if ($this->input->post("tera_video") == "S" && $unidade->audio_ativo == "N") {
			if (empty($_FILES['video']['name'])) {
		    	$this->form_validation->set_rules('video', 'lang:validation_video_label', 'required');
	    	}
		}

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"unidade" => $this->input->post("unidade"),
				"livro" => $this->input->post("livro"),
				"titulo" => $this->input->post("titulo"),
				"grammar_file" => $this->input->post("grammar_file"),
				"texto_grammar" => $this->input->post("texto_grammar_file"),
				"audio_ativo" => $this->input->post("tera_audio"),
				"video_ativo" => $this->input->post("tera_video"),
				"ativo" => $this->input->post("status"),
				"data_atualizacao" => date('Y-m-d H:i:s'),
				"tipo" => $this->input->post("tipo"),
				"video_yt" => (count(array_filter($this->input->post("video_yt[]"))) > 0 && is_array(array_filter($this->input->post("video_yt[]")))) ? serialize($this->input->post("video_yt[]")) : ''
			);

			if($this->app_unidades_livro_model->update($id, $dados)){
				if($this->input->post("tera_audio") == "S" || $this->input->post("tera_video") == "S") {
					if(!file_exists(FCPATH.'upload/unidade/midia/'.$dados["livro"].'/')) {
	   					mkdir(FCPATH.'upload/unidade/midia/'.$dados["livro"].'/', 0777, true);
	     			}
				}

				if($this->input->post("tera_audio") == "S") {
					$dados["audio_transcrito"] = $this->input->post("texto_audio");

					if (!empty($_FILES['audio']['name'])) {
						$max_size = 3;
				        $config['upload_path']          = './upload/unidade/midia/'.$dados["livro"].'/';
				        $config['allowed_types']        = 'mp3';
			        	$config['max_size']         	= (2000*700)*$max_size;
			        	$config['file_name'] 			= md5(uniqid(time()));
						$this->load->library('upload', $config);

						if ($this->upload->do_upload('audio'))
						{
							$dados["audio"] = 'upload/unidade/midia/'.$dados["livro"].'/'.$this->upload->data('file_name');	
							$dados["audio_titulo"] = $_FILES['audio']['name'];
							if($unidade->audio_ativo == "S" && file_exists(FCPATH.$unidade->audio)) {
								unlink(FCPATH.$unidade->audio);
							}
						} else {
							$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
						}
					}
				} else {
					if($unidade->audio_ativo == "S" && file_exists(FCPATH.$unidade->audio)) {
						$dados["audio_transcrito"] = "";
						$dados["audio_titulo"] = "";
						$dados["audio"] = "";
						unlink(FCPATH.$unidade->audio);
					}
				}

				if($this->input->post("tera_video") == "S") {
					if (!empty($_FILES['video']['name'])) {
						$max_size = 50;
				        $config['upload_path']          = './upload/unidade/midia/'.$dados["livro"].'/';
				        $config['allowed_types']        = 'mp3';
			        	$config['max_size']         	= (2000*700)*$max_size;
			        	$config['file_name'] 			= md5(uniqid(time()));
						$this->load->library('upload', $config);

						if ($this->upload->do_upload('video'))
						{
							$dados["video"] = 'upload/unidade/midia/'.$dados["livro"].'/'.$this->upload->data('file_name');	
							$dados["video_titulo"] = $_FILES['video']['name'];
							if($unidade->video_ativo == "S" && file_exists(FCPATH.$unidade->video)) {
								unlink(FCPATH.$unidade->video);
							}
						} else {
							$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
						}
					}
				} else {
					if($unidade->video_ativo == "S" && file_exists(FCPATH.$unidade->video)) {
						$dados["video_titulo"] = "";
						$dados["video"] = "";
						unlink(FCPATH.$unidade->video);
					}
				}

				$this->app_unidades_livro_model->update($id, $dados);

				$this->session->set_flashdata('message', show_alert_success($this->app_unidades_livro_model->messages()));
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->app_unidades_livro_model->messages()));

			redirect('app_unidades_livro', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_unidades_livro_model->errors() ? show_alert_error($this->app_unidades_livro_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status', $unidade->ativo);
            $this->data['tera_audio'] = $this->form_validation->set_value('tera_audio', $unidade->audio_ativo);
            $this->data['tera_video'] = $this->form_validation->set_value('tera_video', $unidade->video_ativo);
            $this->data['tipo'] = $this->form_validation->set_value('tipo', $unidade->tipo);
            $this->data['unidade_model'] = $unidade;

            $livros_array = array(""=>"Selecione");
            foreach ($this->app_unidades_livro_model->get_livros()->result() as $livro) {
            	$livros_array[$livro->id] = $livro->titulo;
            }
			$this->data['livro'] = array(
				'name'  => 'livro',
				'id'    => 'livro',
				'type'  => 'text',
                'class' => 'form-control',
				'options' => $livros_array,
  				'selected' => $this->form_validation->set_value('livro', $unidade->livro)
			);
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo', $unidade->titulo)
			);
			$this->data['unidade'] = array(
				'name'  => 'unidade',
				'id'    => 'unidade',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('unidade', $unidade->unidade)
			);
			$this->data['grammar_file'] = array(
				'name'  => 'grammar_file',
				'id'    => 'grammar_file',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('grammar_file', $unidade->grammar_file)
			);
			$this->data['texto_grammar_file'] = array(
				'name'  => 'texto_grammar_file',
				'id'    => 'texto_grammar_file',
	            'rows' => '10',
	            'cols' => '80',
				'value' => $this->form_validation->set_value('texto_grammar_file', $unidade->texto_grammar)
			);
			$this->data['texto_audio'] = array(
				'name'  => 'texto_audio',
				'id'    => 'texto_audio',
                'class' => 'form-control',
				'value' => ($this->input->post('texto_audio')) ? $this->input->post('texto_audio') : $unidade->audio_transcrito
			);
			if($this->form_validation->set_value('tera_audio', $unidade->audio_ativo) == "N")
				$this->data['texto_audio']['disabled'] = true;

            /* Load Template */
            $this->template->admin_render('admin/app/unidades_livro/edit', $this->data);
		}
	}

    function activate($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_unidades_livro", "activate"))
		{
			redirect('auth', 'refresh');
		}
        $id = (int) $id;
		$activation = $this->app_unidades_livro_model->activate($id);
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_unidades_livro_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_unidades_livro_model->messages()));
		redirect('app_unidades_livro', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_unidades_livro", "deactivate"))
		{
			redirect('auth', 'refresh');
		} 
		$id = (int) $id;
		$deactivation = $this->app_unidades_livro_model->deactivate($id); 
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_unidades_livro_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_unidades_livro_model->messages()));
		redirect('app_unidades_livro', 'refresh');
	}

}
