<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Disciplinas extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/disciplinas');
        $this->load->model('disciplinas_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_disciplinas'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_disciplinas'), 'disciplinas');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("disciplinas", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['disciplinas'] = $this->disciplinas_model->get()->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/pedagogico/disciplinas/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("disciplinas", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_disciplinas_create'), 'disciplinas/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('descricao', 'lang:validation_descricao_label', 'trim|required');
		$this->form_validation->set_rules('sigla', 'lang:validation_sigla_label', 'trim|required');
		$this->form_validation->set_rules('valor', 'lang:validation_valor_label', 'trim|required|is_money');
		$this->form_validation->set_rules('situacao', 'lang:validation_situacao_label', 'required');
		// $this->form_validation->set_rules('curso', 'lang:validation_curso_label', 'required');
		// $this->form_validation->set_rules('pacote', 'lang:validation_pacote_label', 'required');
		// $this->form_validation->set_rules('pacotesub', 'lang:validation_pacotesub_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			//exit("<pre>".print_r($_POST,true)."</pre>");
			$dados = array(
				"DESCRICAO" => $this->input->post("descricao"),
				"empresa_id" => $this->session->userdata("empresa_id"),
				"SIGLA" => $this->input->post("sigla"),
				"VALOR" => str_replace(',', '.', str_replace('.', '', $this->input->post("valor"))),
				"DATA_CADASTRO" => date('Y-m-d'),
				"DATA_ALTERACAO" => date('Y-m-d'),
				"SITUACAO" => $this->input->post("situacao")
			);

			if($id = $this->disciplinas_model->create($dados)){
				$this->session->set_flashdata('message', show_alert_success($this->disciplinas_model->messages()));
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->disciplinas_model->messages()));
			redirect('disciplinas', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->disciplinas_model->errors() ? show_alert_error($this->disciplinas_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['situacao'] = $this->form_validation->set_value('situacao');
			$this->data['valor'] = array(
				'name'  => 'valor',
				'id'    => 'valor',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('valor')
			);
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);
			$this->data['sigla'] = array(
				'name'  => 'sigla',
				'id'    => 'sigla',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('sigla')
			);

            /* Load Template */
            $this->template->admin_render('admin/pedagogico/disciplinas/create', $this->data);
		}
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("disciplinas", "delete"))
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
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("disciplinas", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_disciplinas_edit'), 'disciplinas/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $disciplina = $this->disciplinas_model->get($id)->row();
        //exit("<pre>".print_r($app_turma,true)."</pre>");
		/* Validate form input */
		
		$this->form_validation->set_rules('descricao', 'lang:validation_descricao_label', 'trim|required');
		$this->form_validation->set_rules('sigla', 'lang:validation_sigla_label', 'trim|required');
		$this->form_validation->set_rules('valor', 'lang:validation_valor_label', 'trim|required|is_money');
		$this->form_validation->set_rules('situacao', 'lang:validation_situacao_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"DESCRICAO" => $this->input->post("descricao"),
				"SIGLA" => $this->input->post("sigla"),
				"VALOR" => str_replace(',', '.', str_replace('.', '', $this->input->post("valor"))),
				"DATA_ALTERACAO" => date('Y-m-d'),
				"SITUACAO" => $this->input->post("situacao")
			);

			if($this->disciplinas_model->update($id, $dados)) {
				$this->session->set_flashdata('message', show_alert_success($this->disciplinas_model->messages()));
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->disciplinas_model->messages()));
			redirect('disciplinas', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->disciplinas_model->errors() ? show_alert_error($this->disciplinas_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['situacao'] = $this->form_validation->set_value('situacao', $disciplina->SITUACAO);
            $this->data['valor'] = array(
				'name'  => 'valor',
				'id'    => 'valor',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('valor', $disciplina->VALOR)
			);
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao', $disciplina->DESCRICAO)
			);
			$this->data['sigla'] = array(
				'name'  => 'sigla',
				'id'    => 'sigla',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('sigla', $disciplina->SIGLA)
			);

            /* Load Template */
            $this->template->admin_render('admin/pedagogico/disciplinas/edit', $this->data);
		}
	}

	public function vincular_curso($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("disciplinas", "vincular_curso"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_disciplinas_vincular_curso'), 'disciplinas/vincular_curso');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $vinculos = $this->disciplinas_model->get_vinculos($id)->result();
        //exit("<pre>".print_r($app_turma,true)."</pre>");
		/* Validate form input */
		
		$this->form_validation->set_rules('curso', 'lang:validation_curso_label', 'required');
		$this->form_validation->set_rules('pacote', 'lang:validation_pacote_label', 'required');
		$this->form_validation->set_rules('pacotesub', 'lang:validation_pacotesub_label', 'required');
		$this->form_validation->set_rules('prioridade', 'lang:validation_prioridade_label', 'trim|required|is_numeric');
		$this->form_validation->set_rules('media', 'lang:validation_media_label', 'trim|required|is_money');
		$this->form_validation->set_rules('perc_frequencia', 'lang:validation_perc_frequencia_label', 'trim|required|is_money');

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"LIG_ID_CURSO" => $this->input->post("curso"),
				"LIG_ID_PACOTE" => $this->input->post("pacote"),
				"LIG_ID_PACOTESUB" => $this->input->post("pacotesub"),
				"PRIORIDADE" => $this->input->post("prioridade"),
				"LIG_ID_DISCIPLINA" => $id,
				"PERC_FREQUENCIA" => str_replace(',', '.', str_replace('.', '', $this->input->post("perc_frequencia"))),
				"MEDIA" => str_replace(',', '.', str_replace('.', '', $this->input->post("media")))
			);

			if($this->disciplinas_model->update($id, $dados)) {
				$this->session->set_flashdata('message', show_alert_success($this->disciplinas_model->messages()));
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->disciplinas_model->messages()));
			redirect('disciplinas', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->disciplinas_model->errors() ? show_alert_error($this->disciplinas_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $cursos_array = array(""=>"Selecione");
            foreach ($this->disciplinas_model->get_cursos()->result() as $curso) {
            	$cursos_array[$curso->ID_CURSO] = $curso->DESCRICAO;
            }
            $this->data['curso'] = array(
				'name'  => 'curso',
				'id'    => 'curso',
                'class' => 'form-control',
				'options' => $cursos_array,
				'selected' => $this->form_validation->set_value('curso')
			);

			$pacotes_array = array(""=>"Selecione");
            foreach ($this->disciplinas_model->get_pacotes()->result() as $pacote) {
            	$pacotes_array[$pacote->ID_PACOTE] = $pacote->DESCRICAO;
            }
            $this->data['pacote'] = array(
				'name'  => 'pacote',
				'id'    => 'pacote',
                'class' => 'form-control',
				'options' => $pacotes_array,
				'selected' => $this->form_validation->set_value('pacote')
			);

			$pacotessub_array = array(""=>"Selecione");
            $this->data['pacotesub'] = array(
				'name'  => 'pacotesub',
				'id'    => 'pacotesub',
                'class' => 'form-control',
				'options' => $pacotessub_array,
				'selected' => $this->form_validation->set_value('pacote')
			);

			$this->data['prioridade'] = array(
				'name'  => 'prioridade',
				'id'    => 'prioridade',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('prioridade')
			);

			$this->data['media'] = array(
				'name'  => 'media',
				'id'    => 'media',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('media')
			);

			$this->data['perc_frequencia'] = array(
				'name'  => 'perc_frequencia',
				'id'    => 'perc_frequencia',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('perc_frequencia')
			);

			$this->data['vinculos'] = $vinculos;
			$this->data['disciplina'] = $this->disciplinas_model->get($id)->row();

            /* Load Template */
            $this->template->admin_render('admin/pedagogico/disciplinas/add_vinculo', $this->data);
		}
	}

	public function add_vinculo() {		
		if($_POST["curso"] == "" || $_POST["pacote"] == "" || $_POST["pacotesub"] == "" || $_POST["disciplina"] == "" || $_POST["prioridade"] == "") {
			exit(json_encode(array("erro" => true, "msg" => "Selecione todas informações antes de salvar.")));
		}

		$data = array(
			"LIG_ID_CURSO" => $_POST["curso"],
			"LIG_ID_PACOTE" => $_POST["pacote"],
			"LIG_ID_PACOTESUB" => $_POST["pacotesub"],
			"LIG_ID_DISCIPLINA" => $_POST["disciplina"],
			"PRIORIDADE" => $_POST["prioridade"],
			"MEDIA" => $_POST["media"],
			"PERC_FREQUENCIA" => $_POST["perc_frequencia"],
			"DATA_CADASTRO" => date('Y-m-d'),
		);
		
		if($id = $this->disciplinas_model->create_vinculo($data)) {
			$vinculo = $this->disciplinas_model->get_vinculo_by_id($id)->row();
			$html = "<ul class='list-group ul-vinculo-".$id."'>
				<li class='list-group-item '>
					<button type='button' class='btn btn-danger btn-lista' style='float: right;' onclick='remove_vinculo_disciplina(\"".$id."\")'><i class='fa fa-times' aria-hidden='true'></i></button>
					<label>Curso:</label> ".$vinculo->curso_desc."<br>
					<label>Pacote:</label> ".$vinculo->pacote_desc."<br>
					<label>SubPacote:</label> ".$vinculo->pacotesub_desc."<br>
					<label>Média:</label> ".$vinculo->MEDIA."<br>
					<label>Percentual Frequência:</label> ".$vinculo->PERC_FREQUENCIA."<br>
					<label>Prioridade:</label> ".$vinculo->PRIORIDADE."
				</li></ul>
			";
			exit(json_encode(array("erro" => false, "data" => $html)));
		} else {
			exit(json_encode(array("erro" => true, "msg" => "Houve algum erro ao salvar, tente novamente ou entre em contato com o administrador.")));
		}
	}

	public function remove_vinculo() {		
		if($_POST["id"]) {
			if($this->disciplinas_model->remove_vinculo($_POST["id"]))
				exit(json_encode(array("erro" => false)));
			else
				exit(json_encode(array("erro" => true)));
		}
		exit(json_encode(array("erro" => true)));
	}

	public function get_pacotesub() {
		$sub_pacotes = $this->disciplinas_model->get_pacotessub($_POST['id']);
		$html = "<option value=''>Selecione</option>";
		foreach ($sub_pacotes->result() as $key => $value) {
			$html .= "<option value='".$value->ID_PACOTE_SUB."'>".$value->DESCRICAO."</option>";
		}
		exit($html);
	}


    function activate($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("disciplinas", "activate"))
		{
			redirect('auth', 'refresh');
		}
        $id = (int) $id;
		$activation = $this->disciplinas_model->activate($id);
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->disciplinas_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->disciplinas_model->messages()));
		redirect('disciplinas', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("disciplinas", "deactivate"))
		{
			redirect('auth', 'refresh');
		} 
		$id = (int) $id;
		$deactivation = $this->disciplinas_model->deactivate($id); 
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->disciplinas_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->disciplinas_model->messages()));
		redirect('disciplinas', 'refresh');
	}

}
