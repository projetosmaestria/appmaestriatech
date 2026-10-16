<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_audios extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/app_audios');
        $this->load->model('app_audios_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_app_audios'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_app_audios'), 'app_audios');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_audios", "index"))
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
            		$where .= "a.livro = '".$this->input->post('livro')."'";
            	}
            	if($this->input->post('unidade_livro')) {
            		if($where != "")
            			$where .= " AND ";
            		$where .= "a.unidade = '".$this->input->post('unidade_livro')."'";
            	}
            }
            $this->data['app_audios'] = $this->app_audios_model->get(null, $where)->result();
            $livros = $this->app_audios_model->get_livro()->result();
            $livros_array = array(""=>"Selecione");
            foreach ($livros as $value) {
            	$livros_array[$value->id] = $value->titulo;
            }
            $this->data['livros'] = array(
                'name'  => 'livro',
                'id'    => 'livro',
                'onchange' => "loadUnidadesLivro(this.value, '".base_url()."')",
                'class' => 'form-control',
                'options' => $livros_array,
                'selected' => ($this->input->post('livro')) ? $this->input->post('livro') : ''
            );

            $unidades_array = array(""=>"Selecione");
            if($this->input->post('livro')) {
            	$unidades_livro = $this->app_audios_model->get_unidades_livro($this->input->post('livro'))->result();
            	foreach ($unidades_livro as $value) {
	            	$unidades_array[$value->id] = $value->unidade." - ".$value->titulo;
	            }
            }
            $this->data['unidades'] = array(
                'name'  => 'unidade_livro',
                'id'    => 'unidade_livro',
                'class' => 'form-control',
                'options' => $unidades_array,
                'selected' => ($this->input->post('unidade_livro')) ? $this->input->post('unidade_livro') : ''
            );
            $this->data['message'] = $this->session->flashdata('message');
            /* Load Template */
            $this->template->admin_render('admin/app/audios/index', $this->data);
        }
    }

    public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_audios", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_audios_create'), 'app_audios/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('livro', 'lang:validation_livro_label', 'required');
		$this->form_validation->set_rules('unidade_livro', 'lang:validation_unidade_label', 'required');
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('ordem', 'lang:validation_ordem_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');
		if (empty($_FILES['arquivo']['name'])) {
		    $this->form_validation->set_rules('arquivo', 'lang:validation_arquivo_label', 'required');
		}

		if ($this->form_validation->run() == TRUE && (isset($_FILES) && ! empty($_FILES['arquivo']['name'])))
		{
			if(!file_exists(FCPATH.'upload/audios/'.$this->input->post("livro").'/'.$this->input->post("unidade_livro").'/')) {
				mkdir(FCPATH.'upload/audios/'.$this->input->post("livro").'/'.$this->input->post("unidade_livro").'/', 0777, true);
			}
			$max_size = 10;
	        $config['upload_path']          = FCPATH.'upload/audios/'.$this->input->post("livro").'/'.$this->input->post("unidade_livro").'/';
	        $config['allowed_types']        = 'mp3';
        	$config['max_size']         	= (1024*1024)*$max_size;

			$old_name = $_FILES['arquivo']['name'];
			$new_name = md5(uniqid(time()));
			$config['file_name'] = $new_name;

			$this->load->library('upload', $config);

			if ($this->upload->do_upload('arquivo'))
			{
				$dados = array(
					"livro" => $this->input->post("livro"),
					"unidade" => $this->input->post("unidade_livro"),
					"titulo" => $this->input->post("titulo"),
					"ordem" => $this->input->post("ordem"),
					"ativo" => $this->input->post("status"),
					"caminho_audio" => 'upload/audios/'.$this->input->post("livro").'/'.$this->input->post("unidade_livro").'/'.$this->upload->data('file_name'),
					"titulo_audio" => $old_name
				);

				if($id = $this->app_audios_model->create($dados)) {
					$this->session->set_flashdata('message', show_alert_success($this->app_audios_model->messages()));
				}
				else
					$this->session->set_flashdata('message', show_alert_error($this->app_audios_model->messages()));
			} else {
				$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
			}
			redirect('app_audios', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_audios_model->errors() ? show_alert_error($this->app_audios_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo'),
				'maxlength' => '100'
			);
			$this->data['ordem'] = array(
				'name'  => 'ordem',
				'id'    => 'ordem',
				'type'  => 'number',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('ordem'),
				'maxlength' => '5'
			);
			$livros = $this->app_audios_model->get_livro()->result();
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
            $this->data['unidade_livro'] = array(
                'name'  => 'unidade_livro',
                'id'    => 'unidade_livro',
                'class' => 'form-control',
                'options' => array(""=>"Selecione"),
                'selected' => (count($this->input->post('unidade_livro')) > 0) ? $this->input->post('unidade_livro') : ''
            );

            /* Load Template */
            $this->template->admin_render('admin/app/audios/create', $this->data);
		}
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_audios", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_audios_edit'), 'app_audios/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $app_audio = $this->app_audios_model->get($id)->row();

		/* Validate form input */
		$this->form_validation->set_rules('livro', 'lang:validation_livro_label', 'required');
		$this->form_validation->set_rules('unidade_livro', 'lang:validation_unidade_label', 'required');
		$this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('ordem', 'lang:validation_ordem_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"livro" => $this->input->post("livro"),
				"unidade" => $this->input->post("unidade_livro"),
				"titulo" => $this->input->post("titulo"),
				"ordem" => $this->input->post("ordem"),
				"ativo" => $this->input->post("status")
			);

			if(isset($_FILES) && !empty($_FILES['arquivo']['name'])) {
				if(!file_exists(FCPATH.'upload/audios/'.$this->input->post("livro").'/'.$this->input->post("unidade_livro").'/')) {
					mkdir(FCPATH.'upload/audios/'.$this->input->post("livro").'/'.$this->input->post("unidade_livro").'/', 0777, true);
				}
				$max_size = 10;
		        $config['upload_path']          = FCPATH.'upload/audios/'.$this->input->post("livro").'/'.$this->input->post("unidade_livro").'/';
		        $config['allowed_types']        = 'mp3';
	        	$config['max_size']         	= (1024*1024)*$max_size;

				$old_name = $_FILES['arquivo']['name'];
				$new_name = md5(uniqid(time()));
				$config['file_name'] = $new_name;

				$this->load->library('upload', $config);

				if ($this->upload->do_upload('arquivo'))
				{
					unlink(FCPATH."/".$app_audio->caminho_audio);
					$dados["caminho_audio"] = 'upload/audios/'.$this->input->post("livro").'/'.$this->input->post("unidade_livro").'/'.$this->upload->data('file_name');
					$dados["titulo_audio"] = $old_name;
				} else {
					$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
				}
			}

			if($id = $this->app_audios_model->update($id, $dados))
				$this->session->set_flashdata('message', show_alert_success($this->app_audios_model->messages()));
			
			redirect('app_audios', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_audios_model->errors() ? show_alert_error($this->app_audios_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo', $app_audio->titulo),
				'maxlength' => '100'
			);
			$this->data['ordem'] = array(
				'name'  => 'ordem',
				'id'    => 'ordem',
				'type'  => 'number',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('ordem', $app_audio->ordem),
				'maxlength' => '5'
			);
			$livros = $this->app_audios_model->get_livro()->result();
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
                'selected' => (count($this->input->post('livro')) > 0) ? $this->input->post('livro') : $app_audio->livro_id
            );
            $unidades_livro = $this->app_audios_model->get_unidades_livro($app_audio->livro_id)->result();
			$unidades_livro_array = array(""=>"Selecione");
			foreach ($unidades_livro as $value) {
				$unidades_livro_array[$value->id] = $value->titulo;
			}
            $this->data['unidade_livro'] = array(
                'name'  => 'unidade_livro',
                'id'    => 'unidade_livro',
                'class' => 'form-control',
                'options' => $unidades_livro_array,
                'selected' => (count($this->input->post('unidade_livro')) > 0) ? $this->input->post('unidade_livro') : $app_audio->unidade_id
            );
			$this->data['audio'] = $app_audio;


            /* Load Template */
            $this->template->admin_render('admin/app/audios/edit', $this->data);
		}
	}

	public function get_unidades_livro()
	{
		$unidades_livro = $this->app_audios_model->get_unidades_livro($_POST["livro"]);
		$html = "<option value=''>Selecione</option>";
		if($unidades_livro->num_rows() > 0) {
			foreach ($unidades_livro->result() as $value) {
				$html .= "<option value='".$value->id."'>".$value->unidade." - ".$value->titulo."</option>";
			}
		}
		exit($html);
	}

	function activate($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_audios", "activate"))
		{
			redirect('auth', 'refresh');
		}
        $id = (int) $id;
		$activation = $this->app_audios_model->activate($id);
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_audios_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_audios_model->messages()));
		redirect('app_audios', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_audios", "deactivate"))
		{
			redirect('auth', 'refresh');
		}
		$id = (int) $id;
		$deactivation = $this->app_audios_model->deactivate($id);
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_audios_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_audios_model->messages()));
		redirect('app_audios', 'refresh');
	}

	public function get_audio_base64()
	{
		if($_POST["id"] == null) {
			return false;
		}

		$audio = $this->app_audios_model->get($_POST["id"])->row();
		
		$path = FCPATH.$audio->caminho_audio;

		/*$type = pathinfo($path, PATHINFO_EXTENSION);
    	$base64 = "";
    	if(is_file($path)) {
			$data = file_get_contents($path);
			$base64 = 'data:audio/' . $type . ';base64,' . base64_encode($data);
		}*/

		//exit('<p><b>Ordem: </b>'.$audio->ordem.'<p><b>Título: </b>'.$audio->titulo.'</p><p><b>Livro: </b>'.$audio->livro.'</p><p><b>Unidade: </b>'.$audio->unidade.'</p><audio preload="none" controls><source src="'.base_url($audio->caminho_audio).'" type="audio/mp3"></audio><script>$( "audio" ).audioPlayer();</script>'); 
		
		exit('<p><b>Ordem: </b>'.$audio->ordem.'<p><b>Título: </b>'.$audio->titulo.'</p><p><b>Livro: </b>'.$audio->livro.'</p><p><b>Unidade: </b>'.$audio->unidade.'</p><audio preload="none" controls><source src="https://cursos.bslidiomas.com.br/'.$audio->caminho_audio.'" type="audio/mp3"></audio><script>$( "audio" ).audioPlayer();</script>'); 
	}
}
