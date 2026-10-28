<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends Admin_Controller
{

	public function __construct()
	{
		parent::__construct();

		/* Load :: Common */
		$this->lang->load('admin/users');
		$this->load->helper('functions_helper');
		$this->load->library('form_validation');

		/* Title Page :: Common */
		$this->page_title->push(lang('menu_users'));
		$this->data['pagetitle'] = $this->page_title->show();

		/* Breadcrumbs :: Common */
		$this->breadcrumbs->unshift(1, lang('menu_users'), 'users');
	}


	public function index()
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("users", "index")) {
			redirect('auth/login', 'refresh');
		} else {
			/* Breadcrumbs */
			$this->data['breadcrumb'] = $this->breadcrumbs->show();
			$this->data['message'] = $this->session->flashdata('message');

			/* Get all users */
			//$this->ion_auth->join(array("user_unidade", "users.id = user_unidade.user_id"));
			//$this->ion_auth->join(array("unidades", "unidades.id = user_unidade.unidade_id"));
			//    $this->ion_auth->join(array("empresas_users", "empresas_users.user_id = users.id"));
			// $this->ion_auth->where("empresas_users.empresa_id = ".$this->session->userdata('empresa_id'));
			//    $this->data['users'] = $this->ion_auth->users()->result();

			// foreach ($this->data['users'] as $k => $user)
			// {
			//     $this->data['users'][$k]->groups = $this->ion_auth->get_users_groups($user->id)->result();
			// }

			/* Load Template */
			$this->template->admin_render('admin/users/index', $this->data);
		}
	}

	public function create($tipo = null)
	{
		/* tipos user 
		* 1 = aluno
		*/

		if (!$this->ion_auth->logged_in()) {
			redirect('auth/login', 'refresh');
		} else {
			//if(($tipo == 1 && !$this->ion_auth->has_permissao("app_alunos", "create")) || ($tipo == null && !$this->ion_auth->has_permissao("users", "create"))) {
			if (!$this->ion_auth->has_permissao("app_alunos", "create") && !$this->ion_auth->has_permissao("users", "create")) {
				redirect('auth/login', 'refresh');
			}
			if (!$this->ion_auth->has_permissao("users", "create"))
				$tipo = 1;
		}

		$prefs = $this->ion_auth->get_admin_preferences();
		if ($tipo != null) {
			if ($tipo == 1) {
				if ($prefs->grupo_aluno != "") {
					$this->data['tipo_user'] = 1;
				} else {
					$this->data['tipo_user'] = 0;
				}
			} else {
				$this->data['tipo_user'] = 0;
			}
		} else {
			$this->data['tipo_user'] = 0;
		}

		if ($this->ion_auth->has_permissao("users", "create")) {
			$this->data['has_users_create'] = true;
		} else {
			$this->data['has_users_create'] = false;
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_users_create'), 'users/create');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();
		$this->data['is_admin'] = $this->ion_auth->is_admin();
		$this->data['prefs'] = $prefs;

		/* Variables */
		$tables = $this->config->item('tables', 'ion_auth');

		/* Validate form input */
		//$this->form_validation->set_rules('email', 'lang:users_email', 'required|valid_email|is_unique['.$tables['users'].'.email]');
		$this->form_validation->set_rules('email', 'lang:users_email', 'required|valid_email');
		if ($prefs->cadastro_user_completo == 1) {
			$this->form_validation->set_rules('first_name', 'lang:users_firstname', 'required');
			$this->form_validation->set_rules('last_name', 'lang:users_lastname', 'required');

			if ($this->input->post('password')) {
				$this->form_validation->set_rules('password', $this->lang->line('users_password'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]|is_password_strong');
				$this->form_validation->set_rules('password_confirm', $this->lang->line('users_password_confirm'), 'required');
			}
		}
		$this->form_validation->set_rules('unidades[]', 'lang:users_unidade', 'required');
		//if($this->input->post('cupom_vendedor'))
		//$this->form_validation->set_rules('email', 'lang:users_cupom_vendedor', 'is_unique[pessoas.cupom_vendedor]');

		$empresas_user_logado = $this->ion_auth->get_empresas_user($this->session->userdata('user_id'));

		if ($empresas_user_logado->num_rows() > 0) {
			$empresas_user_array = array();
			foreach ($empresas_user_logado->result() as $emp) {
				$empresas_user_array[] = $emp->empresa_id;
			}
		}

		foreach ($empresas_user_array as $emp_usr) {
			$dados_empresa[$emp_usr]["desc"] = $this->ion_auth->get_empresa($emp_usr)->row()->nome;
			$dados_empresa[$emp_usr]["unidades"] = $this->ion_auth->lista_unidades(array(
				'select' => 'unidades.*, empresas.nome as nome_empresa',
				'join' => array(
					array('empresas', 'unidades.empresa_id=empresas.id'),
					array('user_unidade', 'user_unidade.unidade_id=unidades.id')
				),
				'where' => 'unidades.empresa_id = "' . $emp_usr . '" AND user_unidade.user_id = ' . $this->session->userdata('user_id'),
				'order_by' => array('empresas.id', 'desc')
			))->result_array();
			$dados_empresa[$emp_usr]["setores"] = $this->ion_auth->lista_setores("empresa_id = " . $emp_usr)->result_array();
			$this->ion_auth->where("empresa_id = " . $emp_usr);
			$dados_empresa[$emp_usr]["grupos"] = $this->ion_auth->groups()->result_array();
			$dados_empresa[$emp_usr]["grupo_aluno"] = $this->ion_auth->get_admin_preferences($emp_usr)->grupo_aluno;
			$dados_empresa[$emp_usr]["grupo_vendedor"] = $this->ion_auth->get_admin_preferences($emp_usr)->grupo_vendedor;
			$dados_empresa[$emp_usr]["grupo_admin"] = $this->ion_auth->get_admin_preferences($emp_usr)->grupo_admin;
			$dados_empresa[$emp_usr]['currentEmps'] = (@$this->input->post('active')[$emp_usr] == 1) ? 1 : 0;
			$dados_empresa[$emp_usr]['currentGroups'] = $this->input->post('groups');
			$dados_empresa[$emp_usr]['currentSetores'] = @array_filter($this->input->post('setores'));
			$dados_empresa[$emp_usr]['currentUnids'] = $this->input->post('unidades');

			//aluno tipo = 1
			if (@$this->data['tipo_user'] == 1) {
				if ($emp_usr == $this->session->userdata('empresa_id'))
					$dados_empresa[$emp_usr]['currentEmps'] = 1;
				if (!is_array($dados_empresa[$emp_usr]['currentGroups'])) {
					$dados_empresa[$emp_usr]['currentGroups'] = array();
				}
				if (!in_array($prefs->grupo_aluno, $dados_empresa[$emp_usr]['currentGroups'])) {
					$dados_empresa[$emp_usr]['currentGroups'][] = $prefs->grupo_aluno;
				}
			} else {
				if (!is_array($dados_empresa[$emp_usr]['currentGroups'])) {
					$dados_empresa[$emp_usr]['currentGroups'] = array();
				}
			}
		}

		if ($this->form_validation->run() == TRUE) {
			$email    = strtolower($this->input->post('email'));
			$additional_data = [];
			$password = '';

			if ($prefs->cadastro_user_completo == 1) {
				$additional_data['first_name'] = $this->input->post('first_name');
				$additional_data['last_name'] = $this->input->post('last_name');
				if ($this->input->post('password')) {
					$password = $this->input->post('password');
				}
			}
		}

		if ($this->form_validation->run() == TRUE) {
			$this->ion_auth->where("email", $this->input->post('email'));
			$user_model = $this->ion_auth->users();
			$user_num_rows = $user_model->num_rows();
			$user = $user_model->row();

			if ($user_num_rows == 0) {
				$id = $this->ion_auth->register('', $password, $email, $additional_data, $this->input->post('active'), $this->input->post('groups'), array_filter($this->input->post('setores')), $this->input->post('unidades'));
				//if(@$tipo == 1) {
				$this->load->model('app_alunos_model');
				$data_aluno = array(
					'user_id' => $id,
					'data_nascimento' => ($this->input->post('data_nascimento') != "") ? convertDateBRToEU($this->input->post('data_nascimento')) : "",
					'telefone' => $this->input->post('telefone'),
					'celular' => $this->input->post('celular'),
					'cep' => $this->input->post('cep'),
					'endereco' => $this->input->post('logradouro'),
					'numero' => $this->input->post('numero'),
					'bairro' => $this->input->post('bairro'),
					'cidade' => $this->input->post('cidade'),
					'uf' => $this->input->post('uf'),
					//'cupom_vendedor' => $this->input->post('cupom_vendedor')
				);
				$this->app_alunos_model->create($data_aluno);
				//}
			} else {
				//update usuario existente

				$this->session->set_flashdata('message', show_alert_error("Usuário já existente. Favor solicitar a adição do grupo ao usuário em USUÁRIOS > LISTAR."));
				redirect('users', 'refresh');


				$this->ion_auth->update_user_info($empresas_user_array, $this->input->post('active'), $this->input->post('groups'), $this->input->post('unidades'), $this->input->post('setores'), $user->user_id);

				//if(@$tipo == 1) {
				$this->load->model('app_alunos_model');
				$data_aluno = array(
					'user_id' => $user->user_id,
					'data_nascimento' => ($this->input->post('data_nascimento') != "") ? convertDateBRToEU($this->input->post('data_nascimento')) : "",
					'telefone' => $this->input->post('telefone'),
					'celular' => $this->input->post('celular'),
					'cep' => $this->input->post('cep'),
					'endereco' => $this->input->post('logradouro'),
					'numero' => $this->input->post('numero'),
					'bairro' => $this->input->post('bairro'),
					'cidade' => $this->input->post('cidade'),
					'uf' => $this->input->post('uf'),
					'cpf' => $this->input->post('cpf'),
					'rg' => $this->input->post('rg'),
					//'cupom_vendedor' => $this->input->post('cupom_vendedor')
				);
				if ($this->ion_auth->has_pessoa($user->user_id) > 0) {
					$this->app_alunos_model->update($user->user_id, $data_aluno);
				} else {
					$this->app_alunos_model->create($data_aluno);
				}
				//}
			}
			if (@$tipo == 1) {
				$this->session->set_flashdata('message', show_alert_success($this->app_alunos_model->messages()));
				redirect('app_alunos', 'refresh');
			}
			$this->session->set_flashdata('message', show_alert_success($this->ion_auth->messages()));
			redirect('users', 'refresh');
		} else {
			if ($_POST)
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->ion_auth->errors() ? show_alert_error($this->ion_auth->errors()) : show_alert_error($this->session->flashdata('message'))));

			$this->data['empresa_selecionada'] = $this->session->userdata('empresa_id');
			$this->data['dados_empresa'] = $dados_empresa;
			$this->data['first_name'] = array(
				'name'  => 'first_name',
				'id'    => 'first_name',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->form_validation->set_value('first_name')
			);
			$this->data['last_name'] = array(
				'name'  => 'last_name',
				'id'    => 'last_name',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->form_validation->set_value('last_name')
			);
			$this->data['email'] = array(
				'name'  => 'email',
				'id'    => 'email',
				'type'  => 'email',
				'class' => 'form-control',
				'value' => $this->form_validation->set_value('email'),
			);
			$this->data['cpf'] = array(
				'name'  => 'cpf',
				'id'    => 'cpf',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->form_validation->set_value('cpf'),
			);
			$this->data['rg'] = array(
				'name'  => 'rg',
				'id'    => 'rg',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->form_validation->set_value('rg'),
			);
			//if(@$this->data['tipo_user'] == 1) {
			$this->data['data_nascimento'] = array(
				'name'  => 'data_nascimento',
				'id'    => 'data_nascimento',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->input->post('data_nascimento')
			);
			$this->data['telefone'] = array(
				'name'  => 'telefone',
				'id'    => 'telefone',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->input->post('telefone'),
			);
			$this->data['celular'] = array(
				'name'  => 'celular',
				'id'    => 'celular',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->input->post('celular'),
			);
			$this->data['cep'] = array(
				'name'  => 'cep',
				'id'    => 'cep',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->input->post('cep'),
			);
			$this->data['logradouro'] = array(
				'name'  => 'logradouro',
				'id'    => 'logradouro',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->input->post('logradouro'),
			);
			$this->data['numero'] = array(
				'name'  => 'numero',
				'id'    => 'numero',
				'type'  => 'text',
				'class' => 'form-control',
				'maxlength' => '10',
				'value' => $this->input->post('numero'),
			);
			$this->data['bairro'] = array(
				'name'  => 'bairro',
				'id'    => 'bairro',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->input->post('bairro'),
			);
			$this->data['cidade'] = array(
				'name'  => 'cidade',
				'id'    => 'cidade',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->input->post('cidade'),
			);
			$this->data['uf'] = array(
				'name'  => 'uf',
				'id'    => 'uf',
				'class' => 'form-control',
				'options' => array_merge(array('' => 'Selecione'), get_uf()),
				'selected' => $this->input->post('uf'),
			);
			//}

			$this->data['password'] = array(
				'name' => 'password',
				'id'   => 'password',
				'class' => 'form-control',
				'type' => 'password'
			);
			$this->data['password_confirm'] = array(
				'name' => 'password_confirm',
				'id'   => 'password_confirm',
				'class' => 'form-control',
				'type' => 'password'
			);
			$this->data['senha_atual'] = array(
				'name' => 'senha_atual',
				'id'   => 'senha_atual',
				'class' => 'form-control',
				'type' => 'password'
			);

			/*$this->data['cupom_vendedor'] = array(
				'name'  => 'cupom_vendedor',
				'id'    => 'cupom_vendedor',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->input->post('cupom_vendedor'),
			);*/

			/* Load Template */
			$this->template->admin_render('admin/users/create', $this->data);
		}
	}


	public function delete($id)
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("users", "delete") or ! $id or $id == null) {
			redirect('auth/login', 'refresh');
		}
		if (!$this->ion_auth->can_delete_user($id)) {
			$this->session->set_flashdata('message', show_alert_error("Usuário não está permitido ser deletado."));
			redirect('users', 'refresh');
		}
		if ($this->ion_auth->delete_user($id)) {
			$this->ion_auth->create_auditoria(array("user_id" => $this->session->userdata('user_id'), "empresa_id" => $this->session->userdata('empresa_id'), "controlador" => "users", "metodo" => "delete", "obs" => "Usuário " . $id . " deletado."));
			$this->session->set_flashdata('message', show_alert_success("Usuário deletado com sucesso."));
		}
		redirect('users', 'refresh');
	}


	public function edit($id)
	{
		$id = (int) $id;

		/* tipos user 
    	* 1 = aluno
		*/
		/*if($this->ion_auth->is_aluno($id))
			$this->data['tipo_user'] = 1;
		else
			$this->data['tipo_user'] = 0;
		*/

		//if($this->ion_auth->is_vendedor($id))
		//  $this->data['is_vendedor'] = 1;
		//else
		//  $this->data['is_vendedor'] = 0;


		if (!$this->ion_auth->logged_in() or ! $this->ion_auth->user()->row()->id == $id) {
			redirect('auth/login', 'refresh');
		} else {
			//if(($this->data['tipo_user'] == 1 && !$this->ion_auth->has_permissao("app_alunos", "edit")) || ($this->data['tipo_user'] == 0 && !$this->ion_auth->has_permissao("users", "edit"))) {
			if (!$this->ion_auth->has_permissao("app_alunos", "edit") && !$this->ion_auth->has_permissao("users", "edit")) {
				redirect('auth/login', 'refresh');
			}
		}

		if ($this->ion_auth->is_admin($id)) {
			if (!$this->ion_auth->is_admin($this->session->userdata('user_id'))) {
				$this->session->set_flashdata('message', show_alert_error("Para editar um usuário do grupo admin é preciso que você também esteja no grupo."));
				redirect('auth/login', 'refresh');
			}
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_users_edit'), 'users/edit');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();
		$this->data['is_admin'] = $this->ion_auth->is_admin();

		/* Validate form input */
		$this->form_validation->set_rules('first_name', 'lang:users_firstname', 'required');
		$this->form_validation->set_rules('last_name', 'lang:users_lastname', 'required');
		$this->form_validation->set_rules('unidades[]', 'lang:users_unidade', 'required');

		$pessoa = $this->ion_auth->get_pessoa($id)->row();
		//if(@$pessoa->cupom_vendedor != $this->input->post('cupom_vendedor') && $this->input->post('cupom_vendedor') != "")
		//$this->form_validation->set_rules('cupom_vendedor', 'lang:users_cupom_vendedor', 'is_unique[pessoas.cupom_vendedor]');

		$user = $this->ion_auth->user($id)->row();

		if ($user->active == '0') {
			if (! $this->ion_auth->has_permissao("users", "index")) {
				$this->session->set_flashdata('message', show_alert_error("Usuário não pode ser acessado."));
				redirect('users/index_ativos', 'refresh');
			}
		}

		if ($user->email != $this->input->post('email'))
			$this->form_validation->set_rules('email', 'lang:users_email', 'required|valid_email|is_unique[users.email]');
		else
			$this->form_validation->set_rules('email', 'lang:users_email', 'required|valid_email');


		//lista de todas unidades de todas empresas que o usuário da edição tem acesso filtrada pelas empresas que o usuário logado pode acessar
		$empresas_user_logado = $this->ion_auth->get_empresas_user($this->session->userdata('user_id'));

		if ($empresas_user_logado->num_rows() > 0) {
			$empresas_user_array = array();
			foreach ($empresas_user_logado->result() as $emp) {
				$empresas_user_array[] = $emp->empresa_id;
			}
		}

		/* Data */
		$this->load->model('app_alunos_model');
		$pessoa = $this->app_alunos_model->get_pessoa($user->user_id)->row();


		if ($this->ion_auth->has_permissao("users", "edit")) {
			$this->data['has_users_edit'] = true;
		} else {
			$this->data['has_users_edit'] = false;
		}

		if (isset($_POST) && ! empty($_POST)) {
			if ($this->_valid_csrf_nonce() === FALSE or $id != $this->input->post('id')) {
				show_error($this->lang->line('error_csrf'));
			}

			if ($this->input->post('password')) {
				$this->form_validation->set_rules('password', $this->lang->line('users_password'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]|is_password_strong');
				$this->form_validation->set_rules('password_confirm', $this->lang->line('users_password_confirm'), 'required');
			}

			if ($this->form_validation->run() == TRUE) {
				if ($this->ion_auth->check_login($this->session->userdata('email'), $this->input->post('senha_atual')) == false) {
					$this->data['message'] = show_alert_error("Senha atual do seu usuário incorreta.");
				} elseif ($this->ion_auth->check_login($user->email, $this->input->post('password'))) {
					$this->data['message'] = show_alert_error("A nova senha deverá ser diferente da antiga.");
				} else {
					$data = array(
						'first_name' => $this->input->post('first_name'),
						'last_name'  => $this->input->post('last_name'),
						'email'  => $this->input->post('email')
					);

					// VERIFICAR SE O USUÁRIO FOI ATIVADO NESTA EDIÇÃO
					$user_atual = $this->ion_auth->user($id)->row();
					$active_post = $this->input->post('active');
					$foi_ativado = false;

					if (is_array($active_post)) {
						$active_value = reset($active_post);
						if ($user_atual->active == 0 && $active_value == 1) {
							$foi_ativado = true;
						}
					}

					if ($this->input->post('password')) {
						$data['password'] = $this->input->post('password');
						$data['password_updated'] = date('Y-m-d H:i:s');
					}

					foreach ($empresas_user_array as $emp_usr) {
						$grupos_registrados_model = $this->ion_auth->get_users_groups($id, $emp_usr);
						$grupos_registrados = [];
						foreach ($grupos_registrados_model->result() as $value) {
							$grupos_registrados[] = $value->id;
						}
					}

					$this->ion_auth->update_user_info($empresas_user_array, $this->input->post('active'), $this->input->post('groups'), $this->input->post('unidades'), $this->input->post('setores'), $id);

					if ($this->ion_auth->update($user->id, $data)) {
						$msg_edicao_usuario = "Usuário " . $id . " alterado.";

						// SE FOI ATIVADO, REGISTRAR MENSAGEM ESPECÍFICA
						if ($foi_ativado) {
							$nome_usuario = $data['first_name'] . ' ' . $data['last_name'];
							$email_usuario = $data['email'];
							$msg_edicao_usuario = "Usuário ativado via edição - ID: " . $id . " - Nome: " . $nome_usuario . " - E-mail: " . $email_usuario;
						}

						if ($this->input->post('password'))
							$msg_edicao_usuario .= " Senha do usuário " . $id . " alterada.";

						$novos_grupos = ' Novos grupos: ' . implode(', ', $this->input->post('groups'));
						$antigos_grupos = ' Antigos grupos: ' . implode(', ', $grupos_registrados);

						// SÓ ADICIONA GRUPOS NA MENSAGEM SE NÃO FOI ATIVAÇÃO
						if (!$foi_ativado) {
							$msg_edicao_usuario .= $novos_grupos . $antigos_grupos;
						}

						$this->ion_auth->create_auditoria(array(
							"user_id" => $this->session->userdata('user_id'),
							"empresa_id" => $this->session->userdata('empresa_id'),
							"controlador" => "users",
							"metodo" => "edit",
							"obs" => $msg_edicao_usuario
						));

						$data = array();
						if ($this->input->post('data_nascimento')) {
							$data["data_nascimento"] = convertDateBRToEU($this->input->post('data_nascimento'));
						}
						if ($this->input->post('telefone')) {
							$data["telefone"] = $this->input->post('telefone');
						}
						if ($this->input->post('celular')) {
							$data["celular"] = $this->input->post('celular');
						}
						if ($this->input->post('cep')) {
							$data["cep"] = $this->input->post('cep');
						}
						if ($this->input->post('logradouro')) {
							$data["endereco"] = $this->input->post('logradouro');
						}
						if ($this->input->post('numero')) {
							$data["numero"] = $this->input->post('numero');
						}
						if ($this->input->post('bairro')) {
							$data["bairro"] = $this->input->post('bairro');
						}
						if ($this->input->post('cidade')) {
							$data["cidade"] = $this->input->post('cidade');
						}
						if ($this->input->post('uf')) {
							$data["uf"] = $this->input->post('uf');
						}
						if ($this->input->post('cpf')) {
							$data["cpf"] = $this->input->post('cpf');
						}
						if ($this->input->post('rg')) {
							$data["rg"] = $this->input->post('rg');
						}

						if (!empty($data))
							$this->ion_auth->update_pessoas($user->id, $data);

						$this->session->set_flashdata('message', show_alert_success($this->ion_auth->messages()));

						if (! $this->ion_auth->has_permissao("users", "index")) {
							$this->session->set_flashdata('message', show_alert_error("Usuário não pode ser acessado."));
							redirect('users/index_ativos', 'refresh');
						}

						redirect('users', 'refresh');
					} else {
						$this->session->set_flashdata('message', show_alert_error($this->ion_auth->errors()));

						redirect('auth', 'refresh');
					}
				}
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->ion_auth->errors() ? show_alert_error($this->ion_auth->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		//empresas usuário edição
		$empresas_registradas_model = $this->ion_auth->get_empresas_user($id);
		$empresas_registradas = [];
		foreach ($empresas_registradas_model->result() as $value) {
			$empresas_registradas[$value->empresa_id]['currentEmps'] = 1;
		}

		foreach ($empresas_user_array as $emp_usr) {
			$dados_empresa[$emp_usr]["desc"] = $this->ion_auth->get_empresa($emp_usr)->row()->nome;
			$dados_empresa[$emp_usr]["unidades"] = $this->ion_auth->lista_unidades(array(
				'select' => 'unidades.*, empresas.nome as nome_empresa',
				'join' => array(
					array('empresas', 'unidades.empresa_id=empresas.id'),
					array('user_unidade', 'user_unidade.unidade_id=unidades.id')
				),
				'where' => 'unidades.empresa_id = "' . $emp_usr . '" AND user_unidade.user_id = ' . $this->session->userdata('user_id'),
				'order_by' => array('empresas.id', 'desc')
			))->result_array();
			$dados_empresa[$emp_usr]["setores"] = $this->ion_auth->lista_setores("empresa_id = " . $emp_usr)->result_array();
			$this->ion_auth->where("empresa_id = " . $emp_usr);
			$dados_empresa[$emp_usr]["grupos"] = $this->ion_auth->groups()->result_array();
			$dados_empresa[$emp_usr]["grupo_aluno"] = $this->ion_auth->get_admin_preferences($emp_usr)->grupo_aluno;
			$dados_empresa[$emp_usr]["grupo_vendedor"] = $this->ion_auth->get_admin_preferences($emp_usr)->grupo_vendedor;
			$dados_empresa[$emp_usr]["grupo_admin"] = $this->ion_auth->get_admin_preferences($emp_usr)->grupo_admin;

			//empresas 
			$dados_empresa[$emp_usr]['currentEmps'] = ($this->input->post('active')) ? ((@$this->input->post('active')[$emp_usr] == 1) ? 1 : 0) : ((@$empresas_registradas[$emp_usr]['currentEmps'] == 1) ? 1 : 0);

			//grupos
			//grupos usuário edição
			$grupos_registrados_model = $this->ion_auth->get_users_groups($id, $emp_usr);
			$grupos_registrados = [];
			foreach ($grupos_registrados_model->result() as $value) {
				$grupos_registrados[] = $value->id;
			}
			$dados_empresa[$emp_usr]['currentGroups'] = ($this->input->post('groups')) ? $this->input->post('groups') : $grupos_registrados;

			//setores
			//setores usuário edição
			$setores_registrados_model = $this->ion_auth->get_users_setores($id, $emp_usr);
			$setores_registrados = [];
			foreach ($setores_registrados_model->result() as $value) {
				$setores_registrados[] = $value->id;
			}
			if ($this->input->post('setores') != null) {
				$dados_empresa[$emp_usr]['currentSetores'] = (@array_filter($this->input->post('setores'))) ? array_filter($this->input->post('setores')) : $setores_registrados;
			} else {
				$dados_empresa[$emp_usr]['currentSetores'] = $setores_registrados;
			}


			//unids
			//unids usuário edição
			$unids_registradas_model = $this->ion_auth->get_users_unid($id);
			$unids_registradas = [];
			foreach ($unids_registradas_model->result() as $value) {
				$unids_registradas[] = $value->unidade_id;
			}
			$dados_empresa[$emp_usr]['currentUnids'] = ($this->input->post('unidades')) ? $this->input->post('unidades') : $unids_registradas;
		}

		$this->data['empresa_selecionada'] = $this->session->userdata('empresa_id');
		$this->data['dados_empresa'] = $dados_empresa;

		// display the edit user form
		$this->data['csrf'] = $this->_get_csrf_nonce();

		// pass the user to the view
		$this->data['user']          = $user;

		$this->data['first_name'] = array(
			'name'  => 'first_name',
			'id'    => 'first_name',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => $this->form_validation->set_value('first_name', $user->first_name)
		);
		$this->data['last_name'] = array(
			'name'  => 'last_name',
			'id'    => 'last_name',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => $this->form_validation->set_value('last_name', $user->last_name)
		);
		$this->data['email'] = array(
			'name'  => 'email',
			'id'    => 'email',
			'type'  => 'email',
			'class' => 'form-control',
			'value' => $this->form_validation->set_value('email', $user->email),
		);
		$this->data['cpf'] = array(
			'name'  => 'cpf',
			'id'    => 'cpf',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => ($this->input->post('cpf')) ? $this->input->post('cpf') : @$pessoa->cpf,
		);
		$this->data['rg'] = array(
			'name'  => 'rg',
			'id'    => 'rg',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => ($this->input->post('rg')) ? $this->input->post('rg') : @$pessoa->rg,
		);
		$this->data['password'] = array(
			'name' => 'password',
			'id'   => 'password',
			'class' => 'form-control',
			'type' => 'password'
		);
		$this->data['password_confirm'] = array(
			'name' => 'password_confirm',
			'id'   => 'password_confirm',
			'class' => 'form-control',
			'type' => 'password'
		);
		$this->data['senha_atual'] = array(
			'name' => 'senha_atual',
			'id'   => 'senha_atual',
			'class' => 'form-control',
			'type' => 'password'
		);

		$this->data['data_nascimento'] = array(
			'name'  => 'data_nascimento',
			'id'    => 'data_nascimento',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => ($this->input->post('data_nascimento')) ? $this->input->post('data_nascimento') : @convertDateEUToBR($pessoa->data_nascimento)
		);
		$this->data['telefone'] = array(
			'name'  => 'telefone',
			'id'    => 'telefone',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => ($this->input->post('telefone')) ? $this->input->post('telefone') : @$pessoa->telefone
		);
		$this->data['celular'] = array(
			'name'  => 'celular',
			'id'    => 'celular',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => ($this->input->post('celular')) ? $this->input->post('celular') : @$pessoa->celular
		);
		$this->data['cep'] = array(
			'name'  => 'cep',
			'id'    => 'cep',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => ($this->input->post('cep')) ? $this->input->post('cep') : @$pessoa->cep
		);
		$this->data['logradouro'] = array(
			'name'  => 'logradouro',
			'id'    => 'logradouro',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => ($this->input->post('logradouro')) ? $this->input->post('logradouro') : @$pessoa->endereco
		);
		$this->data['numero'] = array(
			'name'  => 'numero',
			'id'    => 'numero',
			'type'  => 'text',
			'class' => 'form-control',
			'maxlength' => '10',
			'value' => ($this->input->post('numero')) ? $this->input->post('numero') : @$pessoa->numero
		);
		$this->data['bairro'] = array(
			'name'  => 'bairro',
			'id'    => 'bairro',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => ($this->input->post('bairro')) ? $this->input->post('bairro') : @$pessoa->bairro
		);
		$this->data['cidade'] = array(
			'name'  => 'cidade',
			'id'    => 'cidade',
			'type'  => 'text',
			'class' => 'form-control',
			'value' => ($this->input->post('cidade')) ? $this->input->post('cidade') : @$pessoa->cidade
		);
		$this->data['uf'] = array(
			'name'  => 'uf',
			'id'    => 'uf',
			'class' => 'form-control',
			'options' => array_merge(array('' => 'Selecione'), get_uf()),
			'selected' => ($this->input->post('uf')) ? $this->input->post('uf') : @$pessoa->uf
		);

		/* Load Template */
		$this->template->admin_render('admin/users/edit', $this->data);
	}


	function activate($id, $code = FALSE)
	{
		$id = (int) $id;

		if ($code !== FALSE) {
			$activation = $this->ion_auth->activate($id, $code);
		} else if ($this->ion_auth->logged_in() or $this->ion_auth->has_permissao("users", "activate")) {
			$user = $this->ion_auth->user($id)->row();
			if ($user->last_login == "") {
				$this->session->set_flashdata('message', show_alert_error('Este usuário ainda não completou seu cadastro, no momento não é possível ativá-lo.'));
				redirect('users', 'refresh');
			}
			$activation = $this->ion_auth->activate($id);
		}

		if ($activation) {
			// Buscar informações do usuário para o log
			$user_ativado = $this->ion_auth->user($id)->row();
			$nome_usuario = $user_ativado->first_name . ' ' . $user_ativado->last_name;
			$email_usuario = $user_ativado->email;

			// Criar registro na auditoria
			$this->ion_auth->create_auditoria(array(
				"user_id" => $this->session->userdata('user_id'),
				"empresa_id" => $this->session->userdata('empresa_id'),
				"controlador" => "users",
				"metodo" => "activate",
				"obs" => "Usuário ativado - ID: " . $id . " - Nome: " . $nome_usuario . " - E-mail: " . $email_usuario
			));

			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect('users', 'refresh');
		} else {
			$this->session->set_flashdata('message', $this->ion_auth->errors());
			redirect('auth/forgot_password', 'refresh');
		}
	}


	public function deactivate($id = NULL)
	{
		if (!$this->ion_auth->logged_in() or !$this->ion_auth->has_permissao("users", "deactivate")) {
			redirect('users', 'refresh');
		}

		$this->breadcrumbs->unshift(2, lang('menu_users_deactivate'), 'admin/users/deactivate');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		$this->form_validation->set_rules('confirm', 'lang:deactivate_validation_confirm_label', 'required');
		$this->form_validation->set_rules('id', 'lang:deactivate_validation_user_id_label', 'required|alpha_numeric');

		$id = (int) $id;

		if ($this->form_validation->run() === FALSE) {
			$user = $this->ion_auth->user($id)->row();

			$this->data['csrf']       = $this->_get_csrf_nonce();
			$this->data['id']         = (int) $user->id;
			$this->data['firstname']  = ! empty($user->first_name) ? htmlspecialchars($user->first_name, ENT_QUOTES, 'UTF-8') : NULL;
			$this->data['lastname']   = ! empty($user->last_name) ? ' ' . htmlspecialchars($user->last_name, ENT_QUOTES, 'UTF-8') : NULL;

			$this->template->admin_render('admin/users/deactivate', $this->data);
		} else {
			if ($this->input->post('confirm') == 'yes') {
				if ($this->_valid_csrf_nonce() === FALSE or $id != $this->input->post('id')) {
					show_error($this->lang->line('error_csrf'));
				}

				if ($this->ion_auth->logged_in() && $this->ion_auth->has_permissao("users", "deactivate")) {
					$this->ion_auth->deactivate($id);
					$this->ion_auth->create_auditoria(array("user_id" => $this->session->userdata('user_id'), "empresa_id" => $this->session->userdata('empresa_id'), "controlador" => "users", "metodo" => "deactivate", "obs" => "Usuário " . $id . " desativado."));
				}
			}

			redirect('users', 'refresh');
		}
	}

	public function profile($id)
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("users", "profile")) {
			redirect('auth', 'refresh');
		}

		$this->data['message'] = $this->session->flashdata('message');

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_users_profile'), 'groups/profile');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();
		$this->load->model('app_alunos_model');
		$this->load->model('dashboard_model');

		$this->data['can_edit'] = $this->ion_auth->has_permissao("users", "edit");

		/* Data */
		$id = (int) $id;

		$this->data['user_info'] = $this->ion_auth->user($id)->row();
		$this->data['user_info']->groups = $this->ion_auth->get_users_groups($id)->result();
		$this->data['user_info']->empresas_unidades = $this->ion_auth->get_empresas_unidades_user($id)->result();
		$this->data['user_info']->pessoa = $this->ion_auth_model->get_pessoa($id)->row();

		if ($this->data['user_info']->active == '0') {
			if (! $this->ion_auth->has_permissao("users", "index")) {
				$this->session->set_flashdata('message', show_alert_error("Usuário não pode ser acessado."));
				redirect('users/index_ativos', 'refresh');
			}
		}

		$unidades_logado = $this->ion_auth->get_empresas_unidades_user($this->session->userdata('user_id'))->result();
		$unidades_logado_array = array();
		foreach ($unidades_logado as $key => $value) {
			$unidades_logado_array[] = $value->id;
		}

		$verificar_unidade_logado = false;
		$unidade_logado_padrao = null;
		foreach ($this->data['user_info']->empresas_unidades as $key => $value) {
			if (in_array($value->id, $unidades_logado_array))
				$verificar_unidade_logado = true;

			if ($value->padrao == 1)
				$unidade_logado_padrao = $value->id;
		}
		if ($verificar_unidade_logado == false)
			redirect('auth', 'refresh');


		if ($_POST) {
			if ($this->data['can_edit'] == false && $this->session->userdata('user_id') != $id) {
				$this->session->set_flashdata('message', show_alert_error("Seu usuário não possui acesso à outros perfis."));
				redirect('users/profile/' . $id, 'refresh');
			}

			if (@$_POST["tentativas_acesso"]) {
				$this->ion_auth->clear_login_attempts($this->data['user_info']->email, 0);

				$this->ion_auth->create_auditoria(array("user_id" => $this->session->userdata('user_id'), "empresa_id" => $this->session->userdata('empresa_id'), "controlador" => "users", "metodo" => "profile", "obs" => "Removida(s) tentativa(s) de acesso(s) do usuário #" . $id . " - " . $this->data['user_info']->email));

				$this->session->set_flashdata('message', show_alert_success("Tentativa(s) de acesso removida(s)."));
				redirect('users/profile/' . $id, 'refresh');
			}

			if ($this->input->post('password')) {
				if ($this->ion_auth->check_login($this->session->userdata('email'), $this->input->post('senha_atual')) == false) {
					$this->session->set_flashdata('message', show_alert_error("Senha atual do seu usuário incorreta."));
					redirect('users/profile/' . $id, 'refresh');
				}

				if ($this->ion_auth->check_login($this->data['user_info']->email, $this->input->post('password'))) {
					$this->session->set_flashdata('message', show_alert_error("A nova senha deverá ser diferente da antiga."));
					redirect('users/profile/' . $id, 'refresh');
				}

				$this->form_validation->set_rules('password', $this->lang->line('users_password'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]|is_password_strong');
				$this->form_validation->set_rules('password_confirm', $this->lang->line('users_password_confirm'), 'required');
			}

			$this->ion_auth->update_unidade_padrao(array("unidades_usuario" => $this->data['user_info']->empresas_unidades, "unidade_padrao" => @$_POST["unidade_padrao"], "user_id" => $id));
			$unidade_logado_padrao = @$_POST["unidade_padrao"];

			if ($this->form_validation->run() == TRUE) {
				if ($this->input->post('password')) {
					$data['password'] = $this->input->post('password');
					$data['password_updated'] = date('Y-m-d H:i:s');
				}

				if ($this->ion_auth->update($id, $data)) {
					$msg_edicao_usuario = "Usuário " . $id . " alterado.";
					if ($this->input->post('password'))
						$msg_edicao_usuario .= " Senha do usuário " . $id . " alterada.";

					$this->ion_auth->create_auditoria(array("user_id" => $this->session->userdata('user_id'), "empresa_id" => $this->session->userdata('empresa_id'), "controlador" => "users", "metodo" => "profile", "obs" => $msg_edicao_usuario));

					$this->session->set_flashdata('message', show_alert_success("Perfil alterado com sucesso."));
					redirect('users/profile/' . $id, 'refresh');
				} else {
					$this->session->set_flashdata('message', show_alert_error($this->ion_auth->errors()));

					redirect('auth', 'refresh');
				}
			} else {
				$mensagem = show_alert_success('Perfil alterado com sucesso.');
				if (validation_errors()) {
					$mensagem = show_alert_error(validation_errors());
				} else if ($this->ion_auth->errors()) {
					$mensagem = show_alert_error($this->ion_auth->errors());
				} else if ($this->session->flashdata('message')) {
					$mensagem = show_alert_error($this->session->flashdata('message'));
				}
				$this->data['message'] = $mensagem;

				// $this->data['message'] = (
				// 	validation_errors() ? show_alert_error(validation_errors()) : (
				// 		$this->ion_auth->errors() ? show_alert_error($this->ion_auth->errors()) : (
				// 			$this->session->flashdata('message')) ? show_alert_error($this->session->flashdata('message')) : show_alert_success('Perfil alterado com sucesso.')
				// 		)
				// 	);
			}
		}

		$this->data['unidade_padrao'] = $unidade_logado_padrao;

		if ($this->ion_auth->is_aluno($id)) {
			$this->data['user_info']->is_aluno = true;
			$this->data['user_info']->turmas = $this->app_alunos_model->get_turmas($id)->result();
			$ddate = date('Y-m-d');
			$date = new DateTime($ddate);
			$week = $date->format("W");
			$year = $date->format("Y");

			$retorno = array();
			$k = 0;
			$j = 1;
			for ($i = 0; $i < 7; $i++) {
				if (($week - $j) == 0) {
					$year--;
					$ddate = $year . '-12-31';
					$date = new DateTime($ddate);
					$week = $date->format("W");
					$j = 0;
				}
				$week_show = ($week - $j);
				$between_dates = $this->dashboard_model->get_start_and_end_date(($week - ($j - 1)), $year);
				$count_resps = $this->dashboard_model->get_count_resps($between_dates, $id);
				$retorno[$k]['y'] = $year . ' W' . $week_show;
				$retorno[$k]['qtd'] = $count_resps;
				$k++;
				$j++;
			}
			$this->data['user_info']->grafico = json_encode($retorno);
			$this->data['user_info']->app_respostas = $this->dashboard_model->get_respostas_lista($id)->result();
		}

		if ($this->ion_auth->is_aluno_ead($id)) {
			$this->data['user_info']->is_aluno_ead = true;
			$this->data['user_info']->contratos = $this->ion_auth_model->get_contratos_ead($id)->result();
			$k = 0;
			$array_porcentagem_meta = array();
			foreach ($this->data['user_info']->contratos as $k => $contrato) {
				if ($this->data['user_info']->contratos[$k]->has_pesquisa = $this->ion_auth_model->has_pesquisa($contrato->id)) {
					$this->data['user_info']->contratos[$k]->pesquisa_perfil = $this->ion_auth_model->get_pesquisa($contrato->id);
					$array_porcentagem_meta[$contrato->id][] = array("y" => $this->data['user_info']->contratos[$k]->pesquisa_perfil->created_at, "item_" . $contrato->id => $this->data['user_info']->contratos[$k]->pesquisa_perfil->meta_porcentagem);
				}

				$this->data['user_info']->contratos[$k]->is_recorrencia = $this->ion_auth_model->is_recorrencia($contrato->id, $id);

				$tutorias = $this->ion_auth_model->get_tutoria_ead($contrato->id);
				if ($tutorias->num_rows() > 0) {
					foreach ($tutorias->result() as $tutoria) {
						$array_porcentagem_meta[$contrato->id][] = array("y" => $tutoria->data_hora_fim, "item_" . $contrato->id => $tutoria->meta_porcentagem);
					}
				}
				$k++;
			}

			foreach ($array_porcentagem_meta as $k => $row) {
				$aux = array();
				foreach ($row as $key => $value) {
					$aux[$key] = $value['y'];
				}
				array_multisort($aux, SORT_ASC, $row);

				$array_porcentagem_meta[$k] = $row;
			}

			foreach ($array_porcentagem_meta as $k => $row) {
				foreach ($row as $key => $value) {
					$array_porcentagem_meta[$k][$key]["y"] = date("d/m/Y", strtotime($value["y"]));
				}
			}

			$this->data['user_info']->porcentagem_meta = $array_porcentagem_meta;
			$this->data['user_info']->timeline = $this->ion_auth_model->get_timeline_profile($id);
			// exit("<pre>".print_r($this->data['user_info']->porcentagem_meta, true)."<pre>");
		}

		if ($this->ion_auth->is_tutor_ead($id)) {
			$this->data['user_info']->is_tutor_ead = true;

			$nivel_alunos = $this->dashboard_model->get_nivel_alunos($id);

			$na_array = array(
				"higher" => 0,
				"higher_medium" => 0,
				"medium" => 0,
				"emerging" => 0,
				"total" => 0
			);
			if ($nivel_alunos->num_rows() > 0) {
				$last_na = "";
				foreach ($nivel_alunos->result() as $na) {
					if ($last_na != $na->orc_id) {
						$na_array[$na->desempenho_geral] = $na_array[$na->desempenho_geral] + 1;
						$na_array["total"] = $na_array["total"] + 1;
					}
					$last_na = $na->orc_id;
				}
			}

			$na_json = array(
				array(
					"value" => $na_array['higher'],
					"color" => '#00a65a',
					"highlight" => '#00a65a',
					"label" => 'Higher'
				),
				array(
					"value" => $na_array['higher_medium'],
					"color" => '#f3d312',
					"highlight" => '#f3d312',
					"label" => 'Higher Medium'
				),
				array(
					"value" => $na_array['medium'],
					"color" => '#ff851b',
					"highlight" => '#ff851b',
					"label" => 'Medium'
				),
				array(
					"value" => $na_array['emerging'],
					"color" => '#dd4b39',
					"highlight" => '#dd4b39',
					"label" => 'Emerging'
				)
			);

			$this->data['user_info']->nivel_alunos = json_encode($na_json);

			$alunos_meta_atingida = $this->dashboard_model->get_alunos_meta_atigida($id)->result();

			foreach ($alunos_meta_atingida as $_key => $met) {
				if ($met->desempenho_geral == "higher")
					$desempenho_geral = '<i class="fa fa-stop" style="color:#00a65a"><label style="display:none;">higher</label></i>';
				if ($met->desempenho_geral == "higher_medium")
					$desempenho_geral = '<i class="fa fa-stop" style="color:#f3d312"><label style="display:none;">higher_medium</label></i>';
				if ($met->desempenho_geral == "medium")
					$desempenho_geral = '<i class="fa fa-stop" style="color:#ff851b"><label style="display:none;">medium</label></i>';
				if ($met->desempenho_geral == "emerging")
					$desempenho_geral = '<i class="fa fa-stop" style="color:#dd4b39"><label style="display:none;">emerging</label></i>';
				$alunos_meta_atingida[$_key]->desempenho_geral = $desempenho_geral;
			}
			$this->data['user_info']->alunos_meta_atingida = $alunos_meta_atingida;

			$tutorias_realizadas = $this->dashboard_model->get_tutorias_realizadas($id)->result();

			foreach ($tutorias_realizadas as $_key => $tut) {
				if ($tut->desempenho_geral == "higher")
					$desempenho_geral = '<i class="fa fa-stop" style="color:#00a65a"><label style="display:none;">higher</label></i>';
				if ($tut->desempenho_geral == "higher_medium")
					$desempenho_geral = '<i class="fa fa-stop" style="color:#f3d312"><label style="display:none;">higher_medium</label></i>';
				if ($tut->desempenho_geral == "medium")
					$desempenho_geral = '<i class="fa fa-stop" style="color:#ff851b"><label style="display:none;">medium</label></i>';
				if ($tut->desempenho_geral == "emerging")
					$desempenho_geral = '<i class="fa fa-stop" style="color:#dd4b39"><label style="display:none;">emerging</label></i>';
				$tutorias_realizadas[$_key]->desempenho_geral = $desempenho_geral;
			}
			$this->data['user_info']->tutorias_realizadas = $tutorias_realizadas;

			$this->data['user_info']->cancelamentos = $this->dashboard_model->get_contratos_ead_cancelados_por_tutor($id);
		}

		$this->data['password'] = array(
			'name' => 'password',
			'id'   => 'password',
			'class' => 'form-control',
			'type' => 'password'
		);

		$this->data['password_confirm'] = array(
			'name' => 'password_confirm',
			'id'   => 'password_confirm',
			'class' => 'form-control',
			'type' => 'password'
		);

		$this->data['senha_atual'] = array(
			'name' => 'senha_atual',
			'id'   => 'senha_atual',
			'class' => 'form-control',
			'type' => 'password'
		);

		if ($this->data['can_edit']) {
			$this->data['user_info']->tentativas_acesso = $this->ion_auth->get_attempts($this->data['user_info']->email);
		}

		/* Load Template */
		$this->template->admin_render('admin/users/profile', $this->data);
	}

	public function enviar_email($id)
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("users", "enviar_email")) {
			redirect('auth', 'refresh');
		}

		$user = $this->ion_auth_model->user($id)->row();

		if (@$user->id) {
			if ($user->active == 1 || $user->activation_code == "") {
				$this->session->set_flashdata('message', show_alert_info("Este usuário já se encontra ativo."));
				redirect('users', 'refresh');
			}

			// LOG para debug (só no log, não na tela)
			log_message('debug', '=== ENVIAR EMAIL ATIVAÇÃO ===');
			log_message('debug', 'User ID: ' . $id);
			log_message('debug', 'User Email: ' . $user->email);

			$this->load->library("phpmailer_library");

			$email_config_db = $this->ion_auth->get_email_config()->row();

			if ($email_config_db->test == 1) {
				$from_email = $email_config_db->from_email;
				$from_name = $email_config_db->from_name;
				$host = $email_config_db->host;
				$port = $email_config_db->port;
				$user_smtp = $email_config_db->user;
				$pass = $email_config_db->pass;
			} else {
				$email_config = $this->config->item('email_config', 'ion_auth');
				$from_email = $this->config->item('admin_email', 'ion_auth');
				$from_name = $this->config->item('site_title', 'ion_auth');
				$host = $email_config['smtp_host'];
				$port = $email_config['smtp_port'];
				$user_smtp = $email_config['smtp_user'];
				$pass = $email_config['smtp_pass'];
			}

			$data = array(
				'identity'   => $user->email,
				'id'         => $user->id,
				'email'      => $user->email,
				'activation' => $user->activation_code
			);

			// Buscar template de email
			if ($this->ion_auth_model->is_aluno($id)) {
				$padrao_email = $this->ion_auth->get_email_padrao(2)->row();
			} else {
				$padrao_email = $this->ion_auth->get_email_padrao(1)->row();
			}

			if (!$padrao_email) {
				log_message('error', 'Template de email não encontrado');
				$this->session->set_flashdata('message', show_alert_error("Template de email não encontrado."));
				redirect('users', 'refresh');
			}

			$padrao_email_msg = $padrao_email->mensagem;

			if ($this->ion_auth_model->is_aluno($id)) {
				$padrao_email_msg = str_replace("{email}", $data["email"], $padrao_email_msg);
				$padrao_email_msg = str_replace("{link_ativacao}", base_url("auth/activate/" . $data["id"] . "/" . $data["activation"]), $padrao_email_msg);
			} else {
				$padrao_email_msg = str_replace("{email}", $data["email"], $padrao_email_msg);
				$padrao_email_msg = str_replace("{link}", base_url("auth/activate/" . $data["id"] . "/" . $data["activation"]), $padrao_email_msg);
			}

			// Configurar PHPMailer - SEM DEBUG NA TELA
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->SMTPDebug = 0; // DESATIVADO - não mostrar nada na tela
			$mail->Host = $host;
			$mail->SMTPAuth = true;
			$mail->Username = $user_smtp;
			$mail->Password = $pass;
			$mail->SMTPSecure = 'ssl';
			$mail->Port = $port;
			$mail->CharSet = 'UTF-8';

			$mail->setFrom($from_email, utf8_decode($from_name));
			$mail->addAddress($user->email);
			$mail->Subject = utf8_decode($padrao_email->assunto);
			$mail->isHTML(true);
			$mail->Body = utf8_decode($padrao_email_msg);

			// Tentar enviar sem exibir nada
			ob_start(); // Iniciar buffer para capturar qualquer saída
			$send_result = $mail->send();
			$error_info = $mail->ErrorInfo;
			ob_end_clean(); // Limpar buffer e descartar qualquer saída

			if (!$send_result) {
				log_message('error', 'Erro ao enviar email: ' . $error_info);
				$this->session->set_flashdata('message', show_alert_error("Houve um problema ao enviar o e-mail, tente novamente ou entre em contato com o administrador."));
				redirect('users', 'refresh');
			} else {
				log_message('debug', 'Email enviado com sucesso para: ' . $user->email);
				$this->session->set_flashdata('message', show_alert_success("E-mail reenviado com sucesso."));
				redirect('users', 'refresh');
			}
		}
		$this->session->set_flashdata('message', show_alert_error("Usuário não encontrado."));
		redirect('users', 'refresh');
	}

	private function _get_csrf_nonce()
	{
		$this->load->helper('string');
		$key   = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return array($key => $value);
	}


	private function _valid_csrf_nonce()
	{
		if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE && $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue')) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function server_side()
	{
		$columns = array(
			0 => 'id',
			1 => 'first_name',
			2 => 'last_name',
			3 => 'email',
			4 => 'groups',
			5 => 'status',
			6 => 'actions'
		);

		$limit = $this->input->post('length');
		$start = $this->input->post('start');
		$order = $columns[$this->input->post('order')[0]['column']];
		$dir = $this->input->post('order')[0]['dir'];

		$totalData = $this->ion_auth->allserver_side_count();

		$totalFiltered = $totalData;

		if (empty($this->input->post('search')['value'])) {
			$posts = $this->ion_auth->allserver_side($limit, $start, $order, $dir);
		} else {
			$search = $this->input->post('search')['value'];

			$posts =  $this->ion_auth->server_side_search($limit, $start, $search, $order, $dir);

			$totalFiltered = $this->ion_auth->server_side_search_count($search);
		}

		$data = array();
		if (!empty($posts)) {
			foreach ($posts as $post) {
				$groups = "";
				foreach ($this->ion_auth->get_users_groups($post->user_id)->result() as $group) {
					$groups .= anchor('groups/edit/' . $group->id, '<span class="label" style="background:' . $group->bgcolor . ';">' . htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8') . '</span>');
				}
				$nestedData[0] = $post->id;
				$nestedData[1] = $post->first_name;
				$nestedData[2] = $post->last_name;
				$nestedData[3] = $post->email;
				$nestedData[4] = $groups;
				$nestedData[5] = ($post->active) ? '<a href="' . base_url('users/deactivate/' . $post->user_id) . '"><span class="label label-success">' . lang('users_active') . '</span></a>' : '<a href="' . base_url('users/activate/' . $post->user_id) . '"><span class="label label-default">' . lang('users_inactive') . '</span></a>';

				$actions = '<div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                    <ul class="dropdown-menu" role="menu">';

				$actions .= '<li>' . anchor('users/edit/' . $post->user_id, '<i class="fa fa-edit"></i>' . lang('actions_edit'), array('title' => lang('actions_edit'))) . '</li>';
				$actions .= '<li>' . anchor('users/profile/' . $post->user_id, '<i class="fa fa-eye"></i>' . lang('actions_see'), array('title' => lang('actions_see'))) . '</li>';
				if ($post->activation_code != "" && $post->active == 0) {
					$actions .= '<li>' . anchor('users/enviar_email/' . $post->user_id, '<i class="fa fa-envelope"></i>' . lang('actions_send_email'), array('title' => lang('actions_send_email'))) . '</li>';
				}
				if ($post->first_name == "" && $post->last_name == "" && $post->active == 0) {
					$actions .= '<li>' . anchor('users/delete/' . $post->user_id, '<i class="fa fa-trash"></i>' . lang('actions_delete'), array('title' => lang('actions_delete'))) . '</li>';
				}
				$actions .= '<li>' . anchor('users/historico/' . $post->user_id, '<i class="fa fa-history"></i>' . lang('actions_historico'), array('title' => lang('actions_historico'))) . '</li>';

				if ($this->ion_auth->is_vendedor($post->user_id)) {
					$actions .= '<li>' . anchor('users/condicoes/' . $post->user_id, '<i class="fa fa-credit-card"></i>' . lang('condicoes'), array('title' => lang('condicoes'))) . '</li>';
				}

				$actions .= '</ul></div>';

				$nestedData[6] = $actions;
				$data[] = $nestedData;
			}
		}

		$json_data = array(
			"draw"            => intval($this->input->post('draw')),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);

		exit(json_encode($json_data));
	}

	public function historico($user_id = null)
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("users", "historico") or $user_id == null) {
			if ($user_id == null) {
				$this->session->set_flashdata('message', show_alert_error("Usuário não encontrado!"));
				redirect('users', 'refresh');
			}

			redirect('auth/login', 'refresh');
		} else {
			/* Breadcrumbs */
			$this->breadcrumbs->unshift(2, lang('menu_users_historico'), 'users/historico');
			$this->data['pagetitle'] = "<h1>" . lang('menu_users_historico') . "</h1>";
			$this->data['breadcrumb'] = $this->breadcrumbs->show();


			$this->data['historico'] = $this->ion_auth_model->get_historico($user_id)->result();
			$this->data['user_id'] = $user_id;
			$this->data['message'] = $this->session->flashdata('message');

			/* Load Template */
			$this->template->admin_render('admin/users/historico', $this->data);
		}
	}

	public function historico_create($user_id = null)
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("users", "historico_create") or $user_id == null) {
			if ($user_id == null) {
				$this->session->set_flashdata('message', show_alert_error("Usuário não encontrado!"));
				redirect('users', 'refresh');
			}

			redirect('auth/login', 'refresh');
		} else {
			/* Breadcrumbs */
			$this->breadcrumbs->unshift(2, lang('menu_users_historico'), 'users/historico');
			$this->breadcrumbs->unshift(3, lang('menu_users_historico_create'), 'users/historico_create');
			$this->data['pagetitle'] = "<h1>" . lang('menu_users_historico_create') . "</h1>";
			$this->data['breadcrumb'] = $this->breadcrumbs->show();

			$this->data['user_id'] = $user_id;
			$this->data['message'] = $this->session->flashdata('message');
			$user = $this->ion_auth->user($user_id)->row();
			$user_criador = $this->ion_auth->user($this->session->userdata('user_id'))->row();

			$this->form_validation->set_rules('registro', 'lang:registro', 'required');
			$this->form_validation->set_rules('situacao', 'lang:situacao', 'required');
			if (@$this->input->post('enviar_sms') == 1) {
				$this->form_validation->set_rules('mensagem', 'lang:mensagem', 'max_length[1480]|required');
			}

			$dados_data = date('Y-m-d');
			$dados_hora = date('H:i:s');
			$dados = array(
				'LIG_ID_CLIENTE' => $user_id,
				'LIG_ID_USUARIO' => $this->session->userdata('user_id'),
				'REGISTRO' => $this->input->post('registro'),
				'STATUS' => $this->input->post('situacao'),
				'MENSAGEM' => $this->input->post('mensagem'),
				'DATA_REGISTRO' => $dados_data,
				'HORA_REGISTRO' => $dados_hora,
				'email_enviado' => (@$this->input->post('enviar_email') == 1) ? "S" : "N",
				'sms_enviado' => (@$this->input->post('enviar_sms') == 1) ? "S" : "N"
			);

			if ($this->form_validation->run() == TRUE && $id = $this->ion_auth_model->historico_create($dados)) {
				if (@$this->input->post('enviar_email') == 1) {
					$this->load->library(array('email'));

					$email_config_db = $this->ion_auth->get_email_config()->row();

					if ($email_config_db->test == 1) {
						$email_config = rowToConfigEmail($email_config_db);
						$from_email = $email_config_db->from_email;
						$from_name = $email_config_db->from_name;
					} else {
						$email_config = $this->config->item('email_config', 'ion_auth');
						$from_email = $this->config->item('admin_email', 'ion_auth');
						$from_name = $this->config->item('site_title', 'ion_auth');
					}

					if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config)) {
						$this->email->initialize($email_config);
					}

					$data = array(
						'status'   		=> $this->input->post('situacao'),
						'mensagem' 		=> $this->input->post('mensagem'),
						'criado_por'    => $user_criador->first_name . " " . $user_criador->last_name,
						'data_hora'     => date("d/m/Y", strtotime($dados_data)) . " " . $dados_hora,
						'empresa'     	=> $this->ion_auth->get_empresa($this->session->userdata('empresa_id'))->row()->nome
					);

					//$message = $this->load->view("admin/email/novo_historico.tpl.php", $data, true);

					$padrao_email = $this->ion_auth->get_email_padrao(4)->row();
					$padrao_email_msg = $padrao_email->mensagem;
					$padrao_email_msg = str_replace("{empresa}", $data["empresa"], $padrao_email_msg);
					$padrao_email_msg = str_replace("{status}", $data["status"], $padrao_email_msg);
					$padrao_email_msg = str_replace("{mensagem}", $data["mensagem"], $padrao_email_msg);
					$padrao_email_msg = str_replace("{criado_por}", $data["criado_por"], $padrao_email_msg);
					$padrao_email_msg = str_replace("{data_hora}", $data["data_hora"], $padrao_email_msg);

					$this->email->clear();
					$this->email->from($from_email, utf8_decode($from_name));
					$this->email->to($user->email);
					$this->email->subject(utf8_decode($padrao_email->assunto));
					$this->email->message(utf8_decode($padrao_email_msg));

					$this->email->send();
				}

				if (@$this->input->post('enviar_sms') == 1 && trim($this->input->post('mensagem')) != "") {
					$this->load->library('zenvia');
					$pessoa = $this->ion_auth->get_pessoa($user_id);
					if ($pessoa->num_rows() > 0) {
						$data = array();
						$data['from'] = "MaestriaTech";
						$data['to'] = formatCelApi($pessoa->row()->celular);
						$data['msg'] = trim($this->input->post('mensagem'));

						$zenvia = new Zenvia();
						$retorno_call = $zenvia->zenviaCallSingle("send-sms", $zenvia->createSingleJsonSms($data));
						$data_decode = json_decode($retorno_call["data"], true);

						//provisório
						$data["unidade_id"] = 519;
						$data["status_code"] = $data_decode["sendSmsResponse"]["statusCode"];
						$data["status_description"] = getCodesDescription($data_decode["sendSmsResponse"]["statusCode"], 'status');
						$data["detail_code"] = $data_decode["sendSmsResponse"]["detailCode"];
						$data["detail_description"] = getCodesDescription($data_decode["sendSmsResponse"]["detailCode"], 'detail');
						$data["remetente"] = $data["from"];
						$data["telefone"] = $data["to"];
						$data["mensagem"] = $data["msg"];
						unset($data["from"]);
						unset($data["to"]);
						unset($data["msg"]);
						$this->ion_auth->create_sms($data);

						// exit("<pre>".print_r($data_decode,true)."</pre>");
					}
				}

				$this->session->set_flashdata('message', show_alert_success($this->ion_auth_model->messages()));
				redirect('users/historico/' . $user_id, 'refresh');
			} else {
				if ($_POST)
					$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->ion_auth_model->errors() ? show_alert_error($this->ion_auth_model->errors()) : show_alert_error($this->session->flashdata('message'))));

				$this->data['registro'] = array(
					'name'  => 'registro',
					'id'    => 'registro',
					'class' => 'form-control',
					'options' => array('true' => 'True', 'false' => 'False'),
					'selected' => $this->form_validation->set_value('registro', $this->input->post('registro'))
				);

				$status = $this->ion_auth->get_historico_status()->result();
				$status_array = array("" => "Selecione");
				foreach ($status as $value) {
					$status_array[$value->id] = $value->descricao;
				}
				$this->data['situacao'] = array(
					'name'  => 'situacao',
					'id'    => 'situacao',
					'class' => 'form-control',
					'options' => $status_array,
					'selected' => $this->form_validation->set_value('situacao', $this->input->post('situacao'))
				);
				$this->data['mensagem'] = array(
					'name'  => 'mensagem',
					'id'    => 'mensagem',
					'type'  => 'text',
					'class' => 'form-control',
					'value' => $this->form_validation->set_value('mensagem', $this->input->post('mensagem'))
				);
				$this->data['enviar_email'] = array(
					'name'  => 'enviar_email',
					'id'    => 'enviar_email',
					'class' => 'checkbox',
					'value' => 1,
					'checked' => ($this->input->post('enviar_email') == 1) ? true : false
				);
				$this->data['enviar_sms'] = array(
					'name'  => 'enviar_sms',
					'id'    => 'enviar_sms',
					'class' => 'checkbox',
					'value' => 1,
					'checked' => ($this->input->post('enviar_sms') == 1) ? true : false
				);

				/* Load Template */
				$this->template->admin_render('admin/users/historico_create', $this->data);
			}
		}
	}

	public function condicoes($user_id = null)
	{
		$is_vendedor = $this->ion_auth->is_vendedor($user_id);
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("users", "condicoes") or $user_id == null or !$is_vendedor) {
			if ($user_id == null) {
				$this->session->set_flashdata('message', show_alert_error("Usuário não encontrado!"));
				redirect('users', 'refresh');
			}

			if (!$is_vendedor) {
				$this->session->set_flashdata('message', show_alert_error("Usuário não é um vendedor!"));
				redirect('users', 'refresh');
			}

			redirect('auth/login', 'refresh');
		} else {
			/* Breadcrumbs */
			$this->breadcrumbs->unshift(2, lang('menu_users_condicoes'), 'users/condicoes');
			$this->data['pagetitle'] = "<h1>" . lang('menu_users_condicoes') . "</h1>";
			$this->data['breadcrumb'] = $this->breadcrumbs->show();


			$this->data['condicoes'] = $this->ion_auth_model->get_condicoes($user_id)->result();
			$this->data['user_id'] = $user_id;
			$this->data['message'] = $this->session->flashdata('message');

			/* Load Template */
			$this->template->admin_render('admin/users/condicoes', $this->data);
		}
	}

	public function condicoes_vincular($user_id = null)
	{
		$is_vendedor = $this->ion_auth->is_vendedor($user_id);
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("users", "historico_create") or $user_id == null or !$is_vendedor) {
			if ($user_id == null) {
				$this->session->set_flashdata('message', show_alert_error("Usuário não encontrado!"));
				redirect('users', 'refresh');
			}

			if (!$is_vendedor) {
				$this->session->set_flashdata('message', show_alert_error("Usuário não é um vendedor!"));
			}

			redirect('auth/login', 'refresh');
		} else {
			/* Breadcrumbs */
			$this->breadcrumbs->unshift(2, lang('menu_users_condicoes'), 'users/condicoes');
			$this->breadcrumbs->unshift(3, lang('menu_users_condicoes_vincular'), 'users/condicoes_vincular');
			$this->data['pagetitle'] = "<h1>" . lang('menu_users_condicoes_vincular') . "</h1>";
			$this->data['breadcrumb'] = $this->breadcrumbs->show();

			$this->data['user_id'] = $user_id;
			$this->data['message'] = $this->session->flashdata('message');
			$user = $this->ion_auth->user($user_id)->row();

			if ($_POST) {
				$this->ion_auth->remove_user_cond($user_id);
				if (count($_POST["condicoes"]) > 0) {
					$data = array();
					foreach ($_POST["condicoes"] as $cond) {
						$data[] = array(
							'user_id' => $user_id,
							'condicao_id' => $cond
						);
					}
					$this->ion_auth->insert_user_cond($data);
				}
				$this->session->set_flashdata('message', show_alert_success("Vínculo realizado com sucesso."));
				redirect('users/condicoes/' . $user_id, 'refresh');
			} else {
				if ($_POST)
					$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->ion_auth_model->errors() ? show_alert_error($this->ion_auth_model->errors()) : show_alert_error($this->session->flashdata('message'))));

				$unidades = $this->ion_auth->get_unidades_permitidas();
				$unidades_array = array('' => 'Selecione');
				foreach ($unidades->result() as $value) {
					$unidades_array[$value->unidade_id] = $value->unidade;
				}
				$this->data['unidades'] = array(
					'name'  => 'unidades',
					'id'    => 'unidades',
					'class' => 'singleInputDynamic',
					'onchange' => 'loadCondicoesVincularUsuario(this.value)',
					'options' => $unidades_array,
					'selected' => ''
				);

				$this->data['condicoes'] = $condicoes = $this->ion_auth->get_condicoes();

				$users_cond = $this->ion_auth->get_users_cond($user_id);
				$uc_array = array();
				foreach ($users_cond->result() as $key => $value) {
					$uc_array[] = $value->condicao_id;
				}
				$this->data['users_cond'] = $uc_array;

				/* Load Template */
				$this->template->admin_render('admin/users/condicoes_vincular', $this->data);
			}
		}
	}

	public function server_side_ativos()
	{
		$columns = array(
			0 => 'id',
			1 => 'first_name',
			2 => 'last_name',
			3 => 'email',
			4 => 'groups',
			5 => 'status',
			6 => 'actions'
		);

		$limit = $this->input->post('length');
		$start = $this->input->post('start');
		$order = $columns[$this->input->post('order')[0]['column']];
		$dir = $this->input->post('order')[0]['dir'];

		$totalData = $this->ion_auth->allserver_side_count_ativos();

		$totalFiltered = $totalData;

		if (empty($this->input->post('search')['value'])) {
			$posts = $this->ion_auth->allserver_side_ativos($limit, $start, $order, $dir);
		} else {
			$search = $this->input->post('search')['value'];

			$posts =  $this->ion_auth->server_side_search_ativos($limit, $start, $search, $order, $dir);

			$totalFiltered = $this->ion_auth->server_side_search_count_ativos($search);
		}

		$data = array();
		if (!empty($posts)) {
			foreach ($posts as $post) {
				$groups = "";
				foreach ($this->ion_auth->get_users_groups($post->user_id)->result() as $group) {
					$groups .= anchor('groups/edit/' . $group->id, '<span class="label" style="background:' . $group->bgcolor . ';">' . htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8') . '</span>');
				}
				$nestedData[0] = $post->id;
				$nestedData[1] = $post->first_name;
				$nestedData[2] = $post->last_name;
				$nestedData[3] = $post->email;
				$nestedData[4] = $groups;
				$nestedData[5] = ($post->active) ? '<a href="' . base_url('users/deactivate/' . $post->user_id) . '"><span class="label label-success">' . lang('users_active') . '</span></a>' : '<a href="' . base_url('users/activate/' . $post->user_id) . '"><span class="label label-default">' . lang('users_inactive') . '</span></a>';

				$actions = '<div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                    <ul class="dropdown-menu" role="menu">';

				$actions .= '<li>' . anchor('users/edit/' . $post->user_id, '<i class="fa fa-edit"></i>' . lang('actions_edit'), array('title' => lang('actions_edit'))) . '</li>';
				$actions .= '<li>' . anchor('users/profile/' . $post->user_id, '<i class="fa fa-eye"></i>' . lang('actions_see'), array('title' => lang('actions_see'))) . '</li>';
				if ($post->activation_code != "" && $post->active == 0) {
					$actions .= '<li>' . anchor('users/enviar_email/' . $post->user_id, '<i class="fa fa-envelope"></i>' . lang('actions_send_email'), array('title' => lang('actions_send_email'))) . '</li>';
				}
				if ($post->first_name == "" && $post->last_name == "" && $post->active == 0) {
					$actions .= '<li>' . anchor('users/delete/' . $post->user_id, '<i class="fa fa-trash"></i>' . lang('actions_delete'), array('title' => lang('actions_delete'))) . '</li>';
				}
				$actions .= '<li>' . anchor('users/historico/' . $post->user_id, '<i class="fa fa-history"></i>' . lang('actions_historico'), array('title' => lang('actions_historico'))) . '</li>';

				if ($this->ion_auth->is_vendedor($post->user_id)) {
					$actions .= '<li>' . anchor('users/condicoes/' . $post->user_id, '<i class="fa fa-credit-card"></i>' . lang('condicoes'), array('title' => lang('condicoes'))) . '</li>';
				}

				$actions .= '</ul></div>';

				$nestedData[6] = $actions;
				$data[] = $nestedData;
			}
		}

		$json_data = array(
			"draw"            => intval($this->input->post('draw')),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);

		exit(json_encode($json_data));
	}

	public function index_ativos()
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("users", "index_ativos")) {
			redirect('auth/login', 'refresh');
		} else {
			/* Breadcrumbs */
			$this->data['breadcrumb'] = $this->breadcrumbs->show();
			$this->data['message'] = $this->session->flashdata('message');

			/* Get all users */
			//$this->ion_auth->join(array("user_unidade", "users.id = user_unidade.user_id"));
			//$this->ion_auth->join(array("unidades", "unidades.id = user_unidade.unidade_id"));
			//    $this->ion_auth->join(array("empresas_users", "empresas_users.user_id = users.id"));
			// $this->ion_auth->where("empresas_users.empresa_id = ".$this->session->userdata('empresa_id'));
			//    $this->data['users'] = $this->ion_auth->users()->result();

			// foreach ($this->data['users'] as $k => $user)
			// {
			//     $this->data['users'][$k]->groups = $this->ion_auth->get_users_groups($user->id)->result();
			// }

			/* Load Template */
			$this->template->admin_render('admin/users/index_ativos', $this->data);
		}
	}
}
