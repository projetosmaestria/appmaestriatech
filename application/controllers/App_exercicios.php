<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_exercicios extends Admin_Controller {

	public $tipos = array(
        "1"=>"Change the meaning of the sentence",
        "2"=>"Complete with words from the box",
        "3"=>"Choose the right alternative",
        "4"=>"Complete with",
        "5"=>"Open Question(s)",
        "6"=>"Speaking"
    );

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/app_exercicios');
        $this->load->model('app_exercicios_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_app_exercicios'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_app_exercicios'), 'app_exercicios');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_exercicios", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['exercicios'] = $this->app_exercicios_model->get(null, @$_POST["livro"], @$_POST["tipo"], @$_POST['unidade'])->result();
            $this->data['message'] = $this->session->flashdata('message');

            $livros_array = array(""=>"Selecione");
            foreach ($this->app_exercicios_model->get_livros()->result() as $livro) {
            	$livros_array[$livro->id] = $livro->titulo;
            }
			$this->data['livro'] = array(
				'name'  => 'livro',
				'id'    => 'livro',
                'class' => 'form-control',
				'options' => $livros_array,
  				'selected' => $this->input->post('livro')
			);

			$unidades_arr = array(""=>"Selecione um livro");
			$tipo_arr = array(""=>"Selecione um livro");
			if($this->input->post('livro')) {
				$unidades_arr = array(""=>"Selecione");
				$tipo_arr = array(""=>"Selecione", "O"=>"Obrigatório", "C"=>"Complementar");
				if($this->input->post('livro')) {
					$unidades = $this->app_exercicios_model->get_unidades_livro($_POST["livro"], @$_POST["tipo"]);
					foreach ($unidades->result() as $value) {
						$tipo = ($value->tipo == "C") ? "Complementar" : "Obrigatória";
						$unidades_arr[$value->id] = $value->unidade." - ".$value->titulo." (".$tipo.")";
					}
				}
			}
			$this->data['unidade'] = array(
				'name'  => 'unidade',
				'id'    => 'unidade',
                'class' => 'form-control',
				'options' => $unidades_arr,
  				'selected' => $this->input->post('unidade')
			);

			$this->data['tipo'] = array(
				'name'  => 'tipo',
				'id'    => 'tipo',
                'class' => 'form-control',
				'options' => $tipo_arr,
  				'selected' => $this->input->post('tipo')
			);
			
            /* Load Template */
            $this->template->admin_render('admin/app/exercicios/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_exercicios", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_exercicios_create'), 'app_exercicios/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('unidade', 'lang:validation_unidade_label', 'required');
		$this->form_validation->set_rules('livro', 'lang:validation_livro_label', 'required');
		$this->form_validation->set_rules('ordem', 'lang:validation_ordem_label', 'required|is_numeric|trim');
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('tipo', 'lang:validation_tipo_label', 'required');
		$this->form_validation->set_rules('tera_audio', 'lang:validation_tera_audio_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');

		if ($this->input->post("tera_audio") == "S") {
			if (empty($_FILES['audio']['name'])) {
		    	$this->form_validation->set_rules('audio', 'lang:validation_audio_label', 'required');
	    	}
		}

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"unidade" => $this->input->post("unidade"),
				"livro" => $this->input->post("livro"),
				"titulo_exercicio" => $this->input->post("titulo"),
				"tipo_exercicio" => $this->input->post("tipo"),
				"ordem_exercicio" => $this->input->post("ordem"),
				"exemplo" => $this->input->post("exemplo"),
				"audio" => $this->input->post("tera_audio"),
				"ativo" => $this->input->post("status"),
				"data_cadastro" => date('Y-m-d H:i:s')
			);

			if($id = $this->app_exercicios_model->create($dados)){
				if($this->input->post("tera_audio") == "S") {
					if(!file_exists(FCPATH.'upload/exercicios/midia/'.$dados["livro"].'/'.$dados["tipo_exercicio"].'/')) {
	   					mkdir(FCPATH.'upload/exercicios/midia/'.$dados["livro"].'/'.$dados["tipo_exercicio"].'/', 0777, true);
	     			}
				}

				if($this->input->post("tera_audio") == "S") {
					$max_size = 3;
			        $config['upload_path']          = './upload/exercicios/midia/'.$dados["livro"].'/'.$dados["tipo_exercicio"].'/';
			        $config['allowed_types']        = 'mp3';
		        	$config['max_size']         	= (2000*700)*$max_size;
		        	$config['file_name'] 			= md5(uniqid(time()));
					$this->load->library('upload', $config);

					if ($this->upload->do_upload('audio'))
					{
						$dados["arquivo"] = 'upload/exercicios/midia/'.$dados["livro"].'/'.$dados["tipo_exercicio"].'/'.$this->upload->data('file_name');	
						$dados["titulo_arquivo"] = $_FILES['audio']['name'];
						$dados["texto_audio"] = $this->input->post("texto_audio");
					} else {
						$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
					}
				}

				if($this->input->post("tera_audio") == "S") {
					$this->app_exercicios_model->update($id, $dados);
				}

				$this->session->set_flashdata('message', show_alert_success($this->app_exercicios_model->messages()));
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->app_exercicios_model->messages()));

			redirect('app_exercicios', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_exercicios_model->errors() ? show_alert_error($this->app_exercicios_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
            $this->data['tera_audio'] = $this->form_validation->set_value('tera_audio');

            $livros_array = array(""=>"Selecione");
            foreach ($this->app_exercicios_model->get_livros()->result() as $livro) {
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
				$unidades = $this->app_exercicios_model->get_unidades_livro($_POST["livro"]);
				foreach ($unidades->result() as $value) {
					$tipo = ($value->tipo == "C") ? "Complementar" : "Obrigatória";
					$unidades_arr[$value->id] = $value->unidade." - ".$value->titulo." (".$tipo.")";
				}
			}
			$this->data['unidade'] = array(
				'name'  => 'unidade',
				'id'    => 'unidade',
                'class' => 'form-control',
				'options' => $unidades_arr,
  				'selected' => $this->form_validation->set_value('unidade')
			);
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo')
			);
			$tipos_array = array(""=>"Selecione");
			$tipos_array = $tipos_array + $this->tipos;
			$this->data['tipo'] = array(
				'name'  => 'tipo',
				'id'    => 'tipo',
                'class' => 'form-control',
				'options' => $tipos_array,
				'selected' => $this->form_validation->set_value('tipo')
			);
			$this->data['ordem'] = array(
				'name'  => 'ordem',
				'id'    => 'ordem',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('ordem')
			);
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo')
			);
			$this->data['exemplo'] = array(
				'name'  => 'exemplo',
				'id'    => 'exemplo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('exemplo')) ? $this->input->post('exemplo') : ''
			);
			$this->data['texto_audio'] = array(
				'name'  => 'texto_audio',
				'id'    => 'texto_audio',
                'class' => 'form-control',
                'disabled' => 1,
				'value' => ($this->input->post('texto_audio')) ? $this->input->post('texto_audio') : ''
			);

            /* Load Template */
            $this->template->admin_render('admin/app/exercicios/create', $this->data);
		}
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_exercicios", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_exercicios_edit'), 'app_exercicios/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $exerc = $this->app_exercicios_model->get($id)->row();
        // exit("<pre>".print_r($this->input->post(),true)."</pre>");
		/* Validate form input */
		
		$this->form_validation->set_rules('unidade', 'lang:validation_unidade_label', 'required');
		$this->form_validation->set_rules('livro', 'lang:validation_livro_label', 'required');
		$this->form_validation->set_rules('ordem', 'lang:validation_ordem_label', 'required|is_numeric|trim');
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('tipo', 'lang:validation_tipo_label', 'required');
		$this->form_validation->set_rules('tera_audio', 'lang:validation_tera_audio_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');

		if ($this->input->post("tera_audio") == "S" && $exerc->audio == "N") {
			if (empty($_FILES['audio']['name'])) {
		    	$this->form_validation->set_rules('audio', 'lang:validation_audio_label', 'required');
	    	}
		}

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"unidade" => $this->input->post("unidade"),
				"livro" => $this->input->post("livro"),
				"titulo_exercicio" => $this->input->post("titulo"),
				"tipo_exercicio" => $this->input->post("tipo"),
				"ordem_exercicio" => $this->input->post("ordem"),
				"exemplo" => $this->input->post("exemplo"),
				"audio" => $this->input->post("tera_audio"),
				"ativo" => $this->input->post("status"),
				"data_atualizacao" => date('Y-m-d H:i:s')
			);

			if($this->app_exercicios_model->update($id, $dados)){
				if($this->input->post("tera_audio") == "S") {
	     			if(!file_exists(FCPATH.'upload/exercicios/midia/'.$dados["livro"].'/'.$dados["tipo_exercicio"].'/')) {
	   					mkdir(FCPATH.'upload/exercicios/midia/'.$dados["livro"].'/'.$dados["tipo_exercicio"].'/', 0777, true);
	     			}
				}

				if($this->input->post("tera_audio") == "S") {
					$dados["texto_audio"] = $this->input->post("texto_audio");

					if (!empty($_FILES['audio']['name'])) {
						$max_size = 3;
				        $config['upload_path']          = './upload/exercicios/midia/'.$dados["livro"].'/'.$dados["tipo_exercicio"].'/';
				        $config['allowed_types']        = 'mp3';
			        	$config['max_size']         	= (2000*700)*$max_size;
			        	$config['file_name'] 			= md5(uniqid(time()));
						$this->load->library('upload', $config);


						if ($this->upload->do_upload('audio'))
						{
							$dados["arquivo"] = 'upload/exercicios/midia/'.$dados["livro"].'/'.$dados["tipo_exercicio"].'/'.$this->upload->data('file_name');	
							$dados["titulo_arquivo"] = $_FILES['audio']['name'];
							if($exerc->audio == "S" && file_exists(FCPATH.$exerc->arquivo)) {
								unlink(FCPATH.$exerc->arquivo);
							}
						} else {
							$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
						}
					}
				} else {
					if($exerc->audio == "S" && file_exists(FCPATH.$exerc->arquivo)) {
						$dados["texto_audio"] = "";
						$dados["titulo_arquivo"] = "";
						$dados["arquivo"] = "";
						unlink(FCPATH.$exerc->arquivo);
					}
				}

				$this->app_exercicios_model->update($id, $dados);

				$this->session->set_flashdata('message', show_alert_success("Exercício alterado com sucesso."));
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->app_exercicios_model->messages()));

			redirect('app_exercicios', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_exercicios_model->errors() ? show_alert_error($this->app_exercicios_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status', $exerc->ativo);
            $this->data['tera_audio'] = $this->form_validation->set_value('tera_audio', $exerc->audio);
            $this->data['exerc_model'] = $exerc;

            $livros_array = array(""=>"Selecione");
            foreach ($this->app_exercicios_model->get_livros()->result() as $livro) {
            	$livros_array[$livro->id] = $livro->titulo;
            }
			$this->data['livro'] = array(
				'name'  => 'livro',
				'id'    => 'livro',
                'class' => 'form-control',
				'options' => $livros_array,
  				'selected' => $this->form_validation->set_value('livro', $exerc->livro)
			);

			$unidades_arr = array(""=>"Selecione um livro");
			$unidades = $this->app_exercicios_model->get_unidades_livro($this->form_validation->set_value('livro', $exerc->livro));
			foreach ($unidades->result() as $value) {
				$tipo = ($value->tipo == "C") ? "Complementar" : "Obrigatória";
				$unidades_arr[$value->id] = $value->unidade." - ".$value->titulo." (".$tipo.")";
			}
			$this->data['unidade'] = array(
				'name'  => 'unidade',
				'id'    => 'unidade',
                'class' => 'form-control',
				'options' => $unidades_arr,
  				'selected' => $this->form_validation->set_value('unidade', $exerc->unidade)
			);
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo', $exerc->titulo_exercicio)
			);
			$tipos_array = array(""=>"Selecione");
			$tipos_array = $tipos_array + $this->tipos;
			$this->data['tipo'] = array(
				'name'  => 'tipo',
				'id'    => 'tipo',
                'class' => 'form-control',
				'options' => $tipos_array,
				'selected' => $this->form_validation->set_value('tipo', $exerc->tipo_exercicio)
			);
			$this->data['ordem'] = array(
				'name'  => 'ordem',
				'id'    => 'ordem',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('ordem', $exerc->ordem_exercicio)
			);
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo', $exerc->titulo_exercicio)
			);
			$this->data['exemplo'] = array(
				'name'  => 'exemplo',
				'id'    => 'exemplo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('exemplo')) ? $this->input->post('exemplo') : $exerc->exemplo
			);
			$this->data['texto_audio'] = array(
				'name'  => 'texto_audio',
				'id'    => 'texto_audio',
                'class' => 'form-control',
				'value' => ($this->input->post('texto_audio')) ? $this->input->post('texto_audio') : $exerc->texto_audio
			);
			if($this->form_validation->set_value('tera_audio', $exerc->audio) == "N")
				$this->data['texto_audio']['disabled'] = true;

            /* Load Template */
            $this->template->admin_render('admin/app/exercicios/edit', $this->data);
		}
	}

	public function perguntas($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_exercicios", "perguntas"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_exercicios_perguntas'), 'app_exercicios/perguntas');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $exerc = $this->app_exercicios_model->get($id)->row();

		if ($_POST)
		{
        	// exit("<pre>".print_r($this->input->post(),true)."</pre>");

        	if(count($_POST["sentenca"]) > 0) {
        		foreach ($_POST["sentenca"] as $k => $perg) {
        			if(is_numeric(trim($_POST["ordem"][$k])) && trim($perg) != "") {
		        		$dados = array(
							"ordem" => trim($_POST["ordem"][$k]),
							"sentenca" => trim($perg)
						);

		        		if(@isset($_POST["sentencaId"][$k])) {
		        			$dados["ativo"] = $_POST["sentencaStatus"][$k];
		        			$dados["data_atualizacao"] = date("Y-m-d H:i:s");
		        			$this->app_exercicios_model->update_pergunta($_POST["sentencaId"][$k], $dados);
		        		} else {
		        			$dados["exercicio"] = $id;
		        			$dados["tipo_exercicio"] = $exerc->tipo_exercicio;
		        			$dados["ativo"] = "S";
		        			$dados["data_cadastro"] = date("Y-m-d H:i:s");
		        			$this->app_exercicios_model->create_pergunta($dados);
		        		}
					}
				}
        	}

        	$this->session->set_flashdata('message', show_alert_success("Perguntas do exercício ".$id." atualizadas com sucesso."));

			redirect('app_exercicios', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_exercicios_model->errors() ? show_alert_error($this->app_exercicios_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['exercicio'] = $exerc;
            $this->data['perguntas'] = $this->app_exercicios_model->get_perguntas($id);

            /* Load Template */
            $this->template->admin_render('admin/app/exercicios/perguntas', $this->data);
		}
	}

    function activate($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_exercicios", "activate"))
		{
			redirect('auth', 'refresh');
		}
        $id = (int) $id;
		$activation = $this->app_exercicios_model->activate($id);
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_exercicios_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_exercicios_model->messages()));
		redirect('app_exercicios', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_exercicios", "deactivate"))
		{
			redirect('auth', 'refresh');
		} 
		$id = (int) $id;
		$deactivation = $this->app_exercicios_model->deactivate($id); 
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_exercicios_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_exercicios_model->messages()));
		redirect('app_exercicios', 'refresh');
	}

	public function get_unidades_livro() {
		$unidades = $this->app_exercicios_model->get_unidades_livro($_POST["livro"]);
		$html = "<option value=''>Selecione</option>";
		foreach ($unidades->result() as $value) {
			$tipo = ($value->tipo == "C") ? "Complementar" : "Obrigatória";
			$html .= "<option value='".$value->id."'>".$value->unidade." - ".$value->titulo." (".$tipo.")</option>";
		}
		exit($html);
	}

	public function get_tipo_unidade() {
		$unidades = $this->app_exercicios_model->get_unidades_livro($_POST["livro"], $_POST["tipo"]);
		$html = "<option value=''>Selecione</option>";
		foreach ($unidades->result() as $value) {
			$tipo = ($value->tipo == "C") ? "Complementar" : "Obrigatória";
			$html .= "<option value='".$value->id."'>".$value->unidade." - ".$value->titulo." (".$tipo.")</option>";
		}
		exit($html);
	}

	public function get_audio_base64()
	{
		if($_POST["id"] == null) {
			return false;
		}

		$audio = $this->app_exercicios_model->get($_POST["id"])->row();
		
		$path = FCPATH.$audio->arquivo;
		
		exit('<p><b>Ordem: </b>'.$audio->ordem.'<p><b>Título: </b>'.$audio->titulo_exercicio.'</p><p><b>Livro: </b>'.$audio->titulo_livro.'</p><p><b>Unidade: </b>'.$audio->titulo_unidade.'</p><audio preload="none" controls><source src="https://app.xkeysti.com.br/v2/'.$audio->arquivo.'" type="audio/mp3"></audio><script>$( "audio" ).audioPlayer();</script>'); 
	}
}
