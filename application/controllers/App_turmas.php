<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_turmas extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/app_turmas');
        $this->load->model('app_turmas_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_app_turmas'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_app_turmas'), 'app_turmas');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_turmas", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
        	$where = [];
        	if($_POST) {
        		$where[] = 't.status = "'.$_POST['status'].'"';
        	} else {
        		$where[] = 't.status = "1"';
        	}

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['app_turmas'] = $this->app_turmas_model->get(null, $where)->result();
            $this->data['message'] = $this->session->flashdata('message');

            $status_arr = array("1"=>"Ativo", "0"=>"Inativo");
			$this->data['status'] = array(
				'name'  => 'status',
				'id'    => 'status',
                'class' => 'form-control',
				'options' => $status_arr,
  				'selected' => ($this->input->post('status')) ? $this->input->post('status') : '1'
			);

            /* Load Template */
            $this->template->admin_render('admin/app/turmas/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_turmas", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_turmas_create'), 'app_turmas/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('descricao', 'lang:validation_descricao_label', 'trim|required');
		$this->form_validation->set_rules('unidade', 'lang:validation_unidade_label', 'required');
		$this->form_validation->set_rules('data_inicio', 'lang:validation_data_inicio_label', 'required|validar_data');
		$this->form_validation->set_rules('data_termino', 'lang:validation_data_termino_label', 'required|validar_data');
		$this->form_validation->set_rules('turma_horario_professor[]', 'lang:validation_turma_horario_label', 'required');
		$this->form_validation->set_rules('curso', 'lang:validation_curso_label', 'required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');
		$this->form_validation->set_rules('permitir_matricula', 'lang:validation_permitir_matricula_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			//exit("<pre>".print_r($_POST,true)."</pre>");
			$dados = array(
				"LIG_ID_CURSO" => $this->input->post("curso"),
				"DESCRICAO" => $this->input->post("descricao"),
				"OBS" => $this->input->post("obs"),
				"CAPACIDADE_ALUNO" => $this->input->post("capacidade"),
				"DATA_INICIO" => convertDateBRToEU($this->input->post("data_inicio")),
				"DATA_TERMINO" => convertDateBRToEU($this->input->post("data_termino")),
				"unidade" => $this->input->post("unidade"),
				"DATA_CADASTRO" => date('Y-m-d'),
				"LIG_ID_PACOTE" => $this->input->post("livro"),
				"status" => $this->input->post("status"),
				"permitir_matricula" => $this->input->post("permitir_matricula")
			);

			if($id = $this->app_turmas_model->create($dados)){
				if(count($this->input->post("turma_horario_professor")) > 0) {
					$array_horarios = array();
					
					for($i = 0; $i < count($this->input->post("turma_horario_professor")); $i++) {
						$dados_horario = array(
							"ID_CONTROLE" => '0',
							"LIG_ID_TURMA" => $id,
							"LIG_ID_SUBPACOTE" => '0',
							"LIG_ID_SALA" => '0',
							"LIG_ID_PROFESSOR" => $this->input->post("turma_horario_professor")[$i],
							"DIA_SEMANA" => $this->input->post("turma_horario_dia")[$i],
							"HORA_INICIAL" => $this->input->post("turma_horario_inicio")[$i],
							"HORA_FINAL" => $this->input->post("turma_horario_fim")[$i],
							"DATA_CADASTRO" => date('Y-m-d'),
							"SINCRONIZADO" => '0'
						);
						$this->app_turmas_model->create_horario($dados_horario);
					}
				}
				$this->session->set_flashdata('message', show_alert_success($this->app_turmas_model->messages()));
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->app_turmas_model->messages()));
			redirect('app_turmas', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_turmas_model->errors() ? show_alert_error($this->app_turmas_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
            $this->data['permitir_matricula'] = $this->form_validation->set_value('permitir_matricula');
            $this->data['data_inicio'] = array(
				'name'  => 'data_inicio',
				'id'    => 'data_inicio',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('data_inicio')
			);
			$this->data['data_termino'] = array(
				'name'  => 'data_termino',
				'id'    => 'data_termino',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('data_termino')
			);
			$this->data['capacidade'] = array(
				'name'  => 'capacidade',
				'id'    => 'capacidade',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->input->post('capacidade')
			);
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);
			$this->data['obs'] = array(
				'name'  => 'obs',
				'id'    => 'obs',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->input->post('obs')
			);

			$turma_horario = array();
            if(count($this->input->post('turma_horario_professor')) > 0) {
            	for($i = 0; $i < count($this->input->post('turma_horario_professor')); $i++) {
	            	$turma_horario[] = array("professor" => $_POST["turma_horario_professor"][$i], "dia" => $_POST["turma_horario_dia"][$i], "inicio" => $_POST["turma_horario_inicio"][$i], "fim" => $_POST["turma_horario_fim"][$i]);
	            }
	            $this->data['horarios'] = $turma_horario;
	            $pref = $this->app_turmas_model->get_admin_preferences();
	            $this->data['professores'] = $this->app_turmas_model->get_professores($pref->grupo_professor, $this->input->post('unidade'));
            }

			$unidades = $this->app_turmas_model->get_unidades()->result();
			$unidades_array = array(""=>"Selecione");
			foreach ($unidades as $value) {
				$unidades_array[$value->id] = $value->unidade;
			}
            $this->data['unidade'] = array(
                'name'  => 'unidade',
                'id'    => 'unidade_turmas',
                'class' => 'singleInputDynamic',
                'options' => $unidades_array,
                'selected' => (count($this->input->post('unidade')) > 0) ? $this->input->post('unidade') : ''
            );

            $this->data['curso'] = array(
                'name'  => 'curso',
                'id'    => 'curso',
                'class' => 'form-control',
                'options' => array(""=>"Selecione uma unidade"),
                'selected' => (count($this->input->post('curso')) > 0) ? $this->input->post('curso') : ''
            );

			$this->data['livro'] = array(
				'name'  => 'livro',
				'id'    => 'livro',
				'class' => 'form-control',
				'options' => array(""=>"Selecione um curso"),
				'selected' => (count($this->input->post('livro'))) ? $this->input->post('livro') : ''
			);


			// $livros = $this->app_turmas_model->get_livros()->result();
			// $livros_array = array(""=>"Selecione");
			// foreach ($livros as $value) {
			// 	$livros_array[$value->id] = $value->titulo;
			// }
            // $this->data['livro'] = array(
            //     'name'  => 'livro',
            //     'id'    => 'livro',
            //     'class' => 'singleInputDynamic',
            //     'options' => array(""=>"Selecione um curso"),
            //     'selected' => (count($this->input->post('livro')) > 0) ? $this->input->post('livro') : ''
            // );

            /* Load Template */
            $this->template->admin_render('admin/app/turmas/create', $this->data);
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
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_turmas", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_turmas_edit'), 'app_turmas/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $app_turma = $this->app_turmas_model->get($id)->row();
        //exit("<pre>".print_r($app_turma,true)."</pre>");
		/* Validate form input */
		$this->form_validation->set_rules('descricao', 'lang:validation_descricao_label', 'trim|required');
		$this->form_validation->set_rules('unidade', 'lang:validation_unidade_label', 'required');
		$this->form_validation->set_rules('data_inicio', 'lang:validation_data_inicio_label', 'required|validar_data');
		$this->form_validation->set_rules('data_termino', 'lang:validation_data_termino_label', 'required|validar_data');
		$this->form_validation->set_rules('turma_horario_professor[]', 'lang:validation_turma_horario_label', 'required');
		$this->form_validation->set_rules('curso', 'lang:validation_curso_label', 'required');
		$this->form_validation->set_rules('status', 'lang:validation_status_label', 'required');
		$this->form_validation->set_rules('permitir_matricula', 'lang:validation_permitir_matricula_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"LIG_ID_CURSO" => $this->input->post("curso"),
				"DESCRICAO" => $this->input->post("descricao"),
				"OBS" => $this->input->post("obs"),
				"CAPACIDADE_ALUNO" => $this->input->post("capacidade"),
				"DATA_INICIO" => convertDateBRToEU($this->input->post("data_inicio")),
				"DATA_TERMINO" => convertDateBRToEU($this->input->post("data_termino")),
				"unidade" => $this->input->post("unidade"),
				"DATA_CADASTRO" => date('Y-m-d'),
				"LIG_ID_PACOTE" => $this->input->post("livro"),
				"status" => $this->input->post("status"),
				"permitir_matricula" => $this->input->post("permitir_matricula")
			);

			if($this->app_turmas_model->update($id, $dados)) {
				if(count($this->input->post("turma_horario_professor")) > 0) {
					if($this->app_turmas_model->delete_horario($id)) {
						$array_horarios = array();
						
						for($i = 0; $i < count($this->input->post("turma_horario_professor")); $i++) {
							$dados_horario = array(
								"ID_CONTROLE" => '0',
								"LIG_ID_TURMA" => $id,
								"LIG_ID_SUBPACOTE" => '0',
								"LIG_ID_SALA" => '0',
								"LIG_ID_PROFESSOR" => $this->input->post("turma_horario_professor")[$i],
								"DIA_SEMANA" => $this->input->post("turma_horario_dia")[$i],
								"HORA_INICIAL" => $this->input->post("turma_horario_inicio")[$i],
								"HORA_FINAL" => $this->input->post("turma_horario_fim")[$i],
								"DATA_CADASTRO" => date('Y-m-d'),
								"SINCRONIZADO" => '0'
							);
							$this->app_turmas_model->create_horario($dados_horario);
						}
					}
				}
				$this->session->set_flashdata('message', show_alert_success($this->app_turmas_model->messages()));
			}
			else
				$this->session->set_flashdata('message', show_alert_error($this->app_turmas_model->messages()));
			redirect('app_turmas', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_turmas_model->errors() ? show_alert_error($this->app_turmas_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status', $app_turma->status);
            $this->data['permitir_matricula'] = $this->form_validation->set_value('permitir_matricula', $app_turma->permitir_matricula);
            $this->data['data_inicio'] = array(
				'name'  => 'data_inicio',
				'id'    => 'data_inicio',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('data_inicio', convertDateEUToBR($app_turma->DATA_INICIO))
			);
			$this->data['data_termino'] = array(
				'name'  => 'data_termino',
				'id'    => 'data_termino',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('data_termino', convertDateEUToBR($app_turma->DATA_TERMINO))
			);
			$this->data['capacidade'] = array(
				'name'  => 'capacidade',
				'id'    => 'capacidade',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('capacidade')) ? $this->input->post('capacidade') : $app_turma->CAPACIDADE_ALUNO
			);
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao', $app_turma->DESCRICAO)
			);
			$this->data['obs'] = array(
				'name'  => 'obs',
				'id'    => 'obs',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => ($this->input->post('obs')) ? $this->input->post('obs') : $app_turma->OBS
			);
			$unidades = $this->app_turmas_model->get_unidades()->result();
			$unidades_array = array(""=>"Selecione");
			foreach ($unidades as $value) {
				$unidades_array[$value->id] = $value->unidade;
			}
            $this->data['unidade'] = array(
                'name'  => 'unidade',
                'id'    => 'unidade_turmas',
                'class' => 'singleInputDynamic',
                'options' => $unidades_array,
                'selected' => ($this->input->post('unidade')) ? $this->input->post('unidade') : $app_turma->unidade_id
            );

            $this->data['horarios'] = $this->app_turmas_model->get_horarios($app_turma->ID)->result();

            $pref = $this->app_turmas_model->get_admin_preferences();
            $this->data['professores'] = $this->app_turmas_model->get_professores($pref->grupo_professor, $app_turma->unidade_id);

            $cursos = $this->app_turmas_model->get_cursos(($this->input->post('unidade')) ? $this->input->post('unidade') : $app_turma->unidade_id)->result();
			$cursos_array = array(""=>"Selecione");
			foreach ($cursos as $value) {
				$cursos_array[$value->ID_CURSO] = $value->DESCRICAO;
			}
            $this->data['curso'] = array(
                'name'  => 'curso',
                'id'    => 'curso',
                'class' => 'form-control',
                'options' => $cursos_array,
                'selected' => (count($this->input->post('curso')) > 0) ? $this->input->post('curso') : $app_turma->LIG_ID_CURSO
            );

			$livros = $this->app_turmas_model->get_livros(($this->input->post('curso')) ? $this->input->post('curso') : $app_turma->LIG_ID_CURSO)->result();
			$livros_array = array(""=>"Selecione");
			foreach ($livros as $value) {
				$livros_array[$value->id] = $value->titulo;
			}
            $this->data['livro'] = array(
                'name'  => 'livro',
                'id'    => 'livro',
                'class' => 'form-control',
                'options' => $livros_array,
                'selected' => ($this->input->post('livro')) ? $this->input->post('livro') : $app_turma->LIG_ID_PACOTE
            );
			

            /* Load Template */
            $this->template->admin_render('admin/app/turmas/edit', $this->data);
		}
	}

	public function add_user($id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_turmas", "add_user"))
        {
            redirect('auth/login', 'refresh');
        }

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_turmas_alunos'), 'app_turmas/add_users');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->data['turma'] = $this->app_turmas_model->get($id)->row();

        if ($_POST) {  
            $mensagens = "";
            $mensagens_success = "";
            $mensagens_error = "";

            if(count($this->input->post('users_remove')) > 0) {
                foreach ($this->input->post('users_remove') as $user) {
                    $this->app_turmas_model->delete_aluno($user, $id);
                }
            }
            if(count($this->input->post('users')) > 0) {
                foreach ($this->input->post('users') as $user_to_add) {
                    if($this->app_turmas_model->exists_in_turma($user_to_add, $id) == false) { 
                        if($this->app_turmas_model->create_aluno($user_to_add, $id)){
                            $mensagens_success .= "<p>Usuário #".$user_to_add." adicionado no seguimento.</p>";
                            //$this->auditorias_model->create("ead_seguimentos", "add_user", "Usuário #".$user_to_add." adicionado no seguimento ".$seg.".");
                        }else {
                            $mensagem_erro = "";
                            $mensagens_error .= "<p>".$mensagem_erro->error_message."</p>";
                        }
                    }
                }
            }
            if($mensagens_success != "")
                $mensagens .= show_alert_success($mensagens_success);
            if($mensagens_error != "")
                $mensagens .= show_alert_error($mensagens_error);
            $this->session->set_flashdata('message', $mensagens);
            
            redirect('app_turmas', 'refresh');
        } else {
			$pref = $this->app_turmas_model->get_admin_preferences();
            $this->data['users'] = $this->app_turmas_model->get_alunos($pref->grupo_aluno, $this->data['turma']->unidade_id)->result();
            $this->data['users_turma'] = $this->app_turmas_model->get_alunos_turma($id)->result();

            /* Load Template */
            $this->template->admin_render('admin/app/turmas/add_user', $this->data);
        }
    }

    public function notificacao($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_turmas", "notificacao"))
		{
			redirect('auth', 'refresh');
		}

		/* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_turmas_notificacao'), 'app_turmas/notificacao');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->form_validation->set_rules('titulo', 'lang:validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('texto', 'lang:validation_texto_label', 'trim|required');

		if ($this->form_validation->run() == TRUE) {
			$app_turma = $this->app_turmas_model->get($id)->row();
	        
	        if($this->app_turmas_model->send_push($app_turma->ID, array("title"=>$_POST["titulo"], "body"=>$_POST["texto"]))) {
	        	$this->session->set_flashdata('message', show_alert_success($this->app_turmas_model->messages()));     
	        } else {
	        	$this->session->set_flashdata('message', show_alert_error($this->app_turmas_model->messages()));  
	        }	
        	redirect('app_turmas/index', 'refresh');
        }
        if($_POST)
        	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_turmas_model->errors() ? show_alert_error($this->app_turmas_model->errors()) : show_alert_error($this->session->flashdata('message'))));
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
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('texto'),
			'maxlength' => '3000'
		);
        /* Load Template */
        $this->template->admin_render('admin/app/turmas/notificacao', $this->data);
	}

	public function get_professores(){
		$pref = $this->app_turmas_model->get_admin_preferences();
        $html = "<option value=''>Selecione o Professor</option>";
		if($pref->grupo_professor != "") {
			$professores = $this->app_turmas_model->get_professores($pref->grupo_professor, $_POST['unidade']);
	        foreach($professores->result() as $prof) {
	            $html .= "<option value='".$prof->id."'";
	            if($prof->id == @$_POST['professor'])
	                $html .= " selected";
	            $html .= ">".$prof->first_name." ".$prof->last_name."</option>";
	        }
        }
        exit($html);
    }

	public function get_cursos(){
		$retorno = array();
		$cursos = $this->app_turmas_model->get_cursos($_POST['id']);
		$i=0;
        foreach($cursos->result() as $c) {
            $retorno[$i]['id'] = $c->ID_CURSO;
            $retorno[$i]['nome'] = $c->DESCRICAO;
            $i++;
        }
        exit(json_encode($retorno));
    }
	public function get_livros(){
		$retorno = array();
		$livros = $this->app_turmas_model->get_livros($_POST['id']);
		$i=0;
        foreach($livros->result() as $l) {
            $retorno[$i]['id'] = $l->id;
            $retorno[$i]['nome'] = $l->titulo;
            $i++;
        }
        exit(json_encode($retorno));
    }

    function activate($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_turmas", "activate"))
		{
			redirect('auth', 'refresh');
		}
        $id = (int) $id;
		$activation = $this->app_turmas_model->activate($id);
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_turmas_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_turmas_model->messages()));
		redirect('app_turmas', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("app_turmas", "deactivate"))
		{
			redirect('auth', 'refresh');
		} 
		$id = (int) $id;
		$deactivation = $this->app_turmas_model->deactivate($id); 
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->app_turmas_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->app_turmas_model->messages()));
		redirect('app_turmas', 'refresh');
	}

}
