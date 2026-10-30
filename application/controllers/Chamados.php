<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Chamados extends Admin_Controller
{

	/*public $nomenclatura = array(
		"index" => array("inicial","descricao teste"),
	);*/

	public function __construct()
	{
		parent::__construct();

		/* Load :: Common */
		$this->lang->load('admin/chamados');
		$this->load->model('chamados_model');
		$this->load->model('kanban_model');
		$this->load->model('checklists_model');
		$this->load->helper('functions_helper');
		//$this->load->library(array('email'));
		$this->load->library('phpmailer_library');

		/*$email_config_db = $this->ion_auth->get_email_config()->row();

        if($email_config_db->test == 1) {
            $email_config = rowToConfigEmail($email_config_db);
        } else {
            $email_config = $this->config->item('email_config', 'ion_auth');
        }

		if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
		{
			$this->email->initialize($email_config);
		}*/

		/* Title Page :: Common */
		$this->page_title->push(lang('menu_chamados'));
		$this->data['pagetitle'] = $this->page_title->show();

		/* Breadcrumbs :: Common */
		$this->breadcrumbs->unshift(1, lang('menu_chamados'), 'chamados');
	}

	//filtros listagem
	/*public function mostrar_resolvidos() {
    	$mostrar_resolvidos = (@$this->input->post('mostrar_resolvidos') == 1) ? 1 : 0;
    	$mostrar_responsaveis = (@$this->input->post('mostrar_responsaveis') == 1) ? 1 : 0;
		$this->session->set_userdata('mostrar_resolvidos', $mostrar_resolvidos);
		$this->session->set_userdata('mostrar_responsaveis', $mostrar_responsaveis);
		redirect('chamados/index', 'refresh');
    }
    public function q() {
    	$mostrar_resolvidos = (@$this->input->post('mostrar_resolvidos') == 1) ? 1 : 0;
    	$mostrar_responsaveis = (@$this->input->post('mostrar_responsaveis') == 1) ? 1 : 0;
		$this->session->set_userdata('mostrar_resolvidos', $mostrar_resolvidos);
		$this->session->set_userdata('mostrar_responsaveis', $mostrar_responsaveis);

    	if(@$this->input->post('limpar') == 1) {
    		$this->session->unset_userdata('q');
		} else {
			if(@$this->input->post('q') || @$this->input->post('filtro_dono') || @$this->input->post('filtro_status') || @$this->input->post('filtro_prior') || @$this->input->post('filtro_unidade') || @$this->input->post('filtro_avaliacao')) {
				$this->session->set_userdata('q', array($this->input->post('filtro_dono'), $this->input->post('filtro_status'), $this->input->post('filtro_prior'), urlencode(base64_encode($this->input->post('q'))), $this->input->post('filtro_unidade'), $this->input->post('filtro_avaliacao')));
			}
		}
		redirect('chamados/index', 'refresh');
    }*/

	public function index()
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("chamados", "index")) {
			redirect('auth/login', 'refresh');
		} else {
			$grupos_permissoes = $this->ion_auth->get_users_groups_permissoes();
			$usuario_limitado = false;
			if ($grupos_permissoes->num_rows() > 0) {
				foreach ($grupos_permissoes->result() as $value) {
					$permissoes = unserialize($value->permissoes);

					if (count($permissoes) > 0) {
						foreach ($permissoes as $_k => $_p) {
							if ($_k == "chamados") {
								if ($value->limited == 1) {
									$usuario_limitado = true;
								}
							}
						}
					}
				}
			}

			$unidades_permitidas_usuario = $this->chamados_model->get_unidades_permitidas($this->session->userdata('user_id'), $this->session->userdata('empresa_id'))->result();
			$unidade_in_where = array(0);
			$unidade_filtro = array();
			foreach ($unidades_permitidas_usuario as $unid) {
				$unidade_in_where[] = $unid->unidade_id;
				$unidade_filtro[$unid->unidade_id] = $unid->unidade;
			}

			if ($this->ion_auth->has_permissao("chamados", "reagendar"))
				$this->data['acesso_reagendar'] = true;

			$where = "1 = 1";
			$st = "";
			$q = "";
			$prior = "";
			$dono = "";
			$plano_acao = "";
			$cliente = "";
			$avaliacao_de = "";
			$avaliacao_ate = "";
			$data_filtro_inicio_abertura = "";
			$data_filtro_fim_abertura = "";
			$data_filtro_inicio_encerramento = "";
			$data_filtro_fim_encerramento = "";


			$unico_filtro = false;
			$offset = 0;

			if ($_GET) {
				if ($this->input->get('per_page')) {
					$offset = $this->input->get('per_page');
				}

				if ($this->input->get('filtro_plano_acao')) {
					$plano_acao = (int) $this->input->get('filtro_plano_acao');
					$where .= " AND c.plano_acao = " . $plano_acao;
				}

				if ($this->input->get('filtro_dono') || $usuario_limitado == true) {
					if ($usuario_limitado == true) {
						$dono = (int) $this->session->userdata('user_id');
						$where .= ' AND (c.dono_id = ' . $dono . ' OR c.responsavel_id = ' . $dono . ' OR c.desenvolvedor_id = ' . $dono . ')';
					} else {
						$dono = (int) $this->input->get('filtro_dono');
						$where .= ' AND c.dono_id = ' . $dono;
					}
				}
				if ($this->input->get('filtro_dev')) {
					$dev = (int) $this->input->get('filtro_dev');
					$where .= ' AND c.desenvolvedor_id = ' . $dev;
				}
				if ($this->input->get('filtro_status')) {
					$st = (int) $this->input->get('filtro_status');
					if ($this->input->get('mostrar_resolvidos') != 1) {
						$where .= ' AND (c.status_id != 5 AND c.status_id = ' . $st . ')';
					} else {
						$where .= ' AND c.status_id = ' . $st;
					}
				}
				if ($this->input->get('filtro_prior')) {
					$prior = (int) $this->input->get('filtro_prior');
					$where .= ' AND c.prioridade_id = ' . $prior;
				}
				if (trim((string) $this->input->get('q')) !== '') {
					$q = trim((string) $this->input->get('q'));
					$like = $this->db->escape_str($q);
					$like_upper = $this->db->escape_str(mb_strtoupper($q));
					$create = explode(' ', $q);
					$like_date = $this->db->escape_str(implode("-", array_reverse(explode("/", $create[0]))));
					$where .= ' AND (c.id like "%' . $like . '%" OR UPPER(c.titulo) like "%' . $like_upper . '%" OR c.created like "%' . $like_date . '%" OR UPPER(u.first_name) like "%' . $like_upper . '%" OR UPPER(u.last_name) like "%' . $like_upper . '%" OR UPPER(cst.descricao) like "%' . $like_upper . '%" OR UPPER(cp.descricao) like "%' . $like_upper . '%" OR UPPER(un.unidade) like "%' . $like_upper . '%" OR UPPER(cs.descricao) like "%' . $like_upper . '%")';
				}
				if ($this->input->get('filtro_unidade')) {
					$cliente = (int) $this->input->get('filtro_unidade');
					$where .= ' AND un.id = ' . $cliente;
				}
				if ($this->input->get('data_filtro_inicio_abertura') || $this->input->get('data_filtro_fim_abertura')) {
					$where .= ' AND (';
					if ($this->input->get('data_filtro_inicio_abertura')) {
						$data_filtro_inicio_abertura = convertDateBRToEU($this->input->get('data_filtro_inicio_abertura'));
						$where .= ' c.created >= ' . $this->db->escape($data_filtro_inicio_abertura) . ' ';
					}
					if ($this->input->get('data_filtro_fim_abertura')) {
						$data_filtro_fim_abertura = convertDateBRToEU($this->input->get('data_filtro_fim_abertura'));
						if ($this->input->get('data_filtro_inicio_abertura'))
							$where .= ' AND';
						$where .= ' c.created <= ' . $this->db->escape($data_filtro_fim_abertura . ' 23:59:59') . ' ';
					}
					$where .= ' )';
				}
				if ($this->input->get('data_filtro_inicio_encerramento') || $this->input->get('data_filtro_fim_encerramento')) {
					$where .= ' AND (';
					if ($this->input->get('data_filtro_inicio_encerramento')) {
						$data_filtro_inicio_encerramento = convertDateBRToEU($this->input->get('data_filtro_inicio_encerramento'));
						$where .= ' c.data_finalizacao >= ' . $this->db->escape($data_filtro_inicio_encerramento) . ' ';
					}
					if ($this->input->get('data_filtro_fim_encerramento')) {
						$data_filtro_fim_encerramento = convertDateBRToEU($this->input->get('data_filtro_fim_encerramento'));
						if ($this->input->get('data_filtro_inicio_encerramento'))
							$where .= ' AND';
						$where .= ' c.data_finalizacao <= ' . $this->db->escape($data_filtro_fim_encerramento . ' 23:59:59') . ' ';
					}
					$where .= ' )';
				}
			} else {
				if ($usuario_limitado == true) {
					$dono = $this->session->userdata('user_id');
					$where .= ' AND c.dono_id = ' . $dono;
				}
			}

			if ($this->input->get('mostrar_resolvidos') != 1 && @!$this->input->get('filtro_status')) {
				$where .= ' AND c.status_id != 5';
			}
			if ($this->input->get('mostrar_responsaveis') == 1 && @!$this->input->get('filtro_dono')) {
				$where .= ' AND c.responsavel_id = ' . (int) $this->session->userdata('user_id');
			}

			$where .= ' AND c.unidade_id IN (' . implode(",", $unidade_in_where) . ')';

			if ($this->input->get('filtro_avaliacao_de') || $this->input->get('filtro_avaliacao_ate')) {
				$average = "";
				if ($this->input->get('filtro_avaliacao_de') != "") {
					$avaliacao_de = (int) $this->input->get('filtro_avaliacao_de');
					$average .= "AVG(ca.resposta) >= " . $avaliacao_de;
				}
				if ($this->input->get('filtro_avaliacao_ate') != "") {
					if ($average != "")
						$average .= " AND ";
					$avaliacao_ate = (int) $this->input->get('filtro_avaliacao_ate');
					$average .= "AVG(ca.resposta) <= " . $avaliacao_ate;
				}
				$chamados = $this->chamados_model->get(array(
					'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, ud.first_name as dono_first_name, ud.last_name as dono_last_name, AVG(ca.resposta) AS average, c.responsavel_id',
					'join' => array(array('users u', 'u.id = c.responsavel_id', 'left'), array('users ud', 'ud.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner'), array('chamado_avaliacao ca', 'ca.chamado_id = c.id', 'left')),
					'where' => $where,
					'order_by' => array('c.id', 'desc')
				), 15, $offset, "c.id", $average);
				$chamados_num_rows = $this->chamados_model->get(array(
					'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade',
					'join' => array(array('users u', 'u.id = c.responsavel_id', 'left'), array('users ud', 'ud.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner'), array('chamado_avaliacao ca', 'ca.chamado_id = c.id', 'left')),
					'where' => $where,
					'order_by' => array('c.id', 'desc')
				), null, null, "c.id", $average)->num_rows();
			} else {
				$chamados = $this->chamados_model->get(array(
					'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, ud.first_name as dono_first_name, ud.last_name as dono_last_name, c.responsavel_id',
					'join' => array(array('users u', 'u.id = c.responsavel_id', 'left'), array('users ud', 'ud.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner')),
					'where' => $where,
					'order_by' => array('c.id', 'desc')
				), 15, $offset);
				$chamados_num_rows = $this->chamados_model->get(array(
					'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade',
					'join' => array(array('users u', 'u.id = c.responsavel_id', 'left'), array('users ud', 'ud.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner')),
					'where' => $where,
					'order_by' => array('c.id', 'desc')
				))->num_rows();
			}
			// exit($this->db->last_query());
			//$config = get_pagination_config(current_url().'?'.$_SERVER['QUERY_STRING'], $chamados_num_rows, $offset);
			$config = get_pagination_config(current_url(), $chamados_num_rows, $offset);
			$this->pagination->initialize($config);
			$this->data['pagination'] = $this->pagination->create_links();

			/* Breadcrumbs */
			$this->data['breadcrumb'] = $this->breadcrumbs->show();
			$this->data['chamados'] = $chamados->result();
			$this->data['usuarios'] = $this->chamados_model->get_usuarios()->result();
			$this->data['status'] = $this->chamados_model->get_status()->result();
			$this->data['prioridades'] = $this->chamados_model->get_prioridades()->result();
			$this->data['planos_acao'] = $this->chamados_model->get_planos_acao()->result();
			$this->data['message'] = $this->session->flashdata('message');
			$this->data['mostrar_resolvidos'] = @$this->input->get('mostrar_resolvidos');
			$this->data['mostrar_responsaveis'] = (@$this->input->get('mostrar_responsaveis') || $usuario_limitado) ? 1 : 0;
			$this->data['usuario_limitado'] = $usuario_limitado;
			$this->data['unidade_filtro'] = $unidade_filtro;
			$this->data['q'] = $q;
			$this->data['dono'] = $dono;
			$this->data['cliente'] = $cliente;
			$this->data['st'] = $st;
			$this->data['pr'] = $prior;
			$this->data['avaliacao_de'] = $avaliacao_de;
			$this->data['avaliacao_ate'] = $avaliacao_ate;
			$this->data['plano_acao'] = $plano_acao;
			$this->data['limpar'] = ($_GET) ? 1 : 0;
			$this->data['inicio_abertura'] = $data_filtro_inicio_abertura;
			$this->data['fim_abertura'] = $data_filtro_fim_abertura;
			$this->data['inicio_encerramento'] = $data_filtro_inicio_encerramento;
			$this->data['fim_encerramento'] = $data_filtro_fim_encerramento;

			$desenvolvedores = $this->chamados_model->get_groups_dev();
			$this->data['desenvolvedores'] = is_object($desenvolvedores) ? $desenvolvedores->result() : array();
			$this->data['dev'] = isset($dev) ? $dev : '';
			$this->data['total_chamados'] = $chamados_num_rows;

			/* Load Template */
			$this->template->admin_render('admin/chamados/index', $this->data);
		}
	}

	public function create()
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("chamados", "create")) {
			redirect('auth', 'refresh');
		}

		$grupos_usuarios_chamado = $this->chamados_model->get_groups();
		if (! @$grupos_usuarios_chamado) {
			$this->session->set_flashdata('message', show_alert_error("Você deve parametrizar o grupo de usuários responsáveis pelo chamado antes."));
			redirect('chamados', 'refresh');
		}

		$is_full_create = $this->chamados_model->is_full_create();
		$this->data['is_full_create'] = $is_full_create;

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_chamados_create'), 'chamados/create');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('unidade', 'lang:create_validation_unidade_label', 'required');
		$this->form_validation->set_rules('produto', 'lang:create_validation_produto_label', 'required');
		$this->form_validation->set_rules('modulo', 'lang:create_validation_modulo_label', 'required');
		$this->form_validation->set_rules('titulo', 'lang:create_validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('desc', 'lang:create_validation_desc_label', 'trim|required');
		$this->form_validation->set_rules('categoria', 'lang:create_validation_categoria_label', 'required');
		$this->form_validation->set_rules('tipo_servico', 'lang:create_validation_tipo_label', 'required');
		$this->form_validation->set_rules('nivel', 'lang:create_validation_nivel_label', 'required');

		if ($is_full_create == true) {
			if ($this->input->post('dono'))
				$this->form_validation->set_rules('dono', 'lang:create_validation_dono_label', 'required');
			$this->form_validation->set_rules('responsavel', 'lang:create_validation_responsavel_label', 'required');
			$this->form_validation->set_rules('prioridade', 'lang:create_validation_prioridade_label', 'required');

			$dados["data_inicio"] = date('Y-m-d H:i:s');
		}

		$dados["referencia_id"] = ($this->input->post('referencia')) ? $this->input->post('referencia') : null;
		$dados["unidade_id"] = $this->input->post('unidade');
		$dados["produto_id"] = $this->input->post('produto');
		$dados["modulo_id"] = $this->input->post('modulo');
		$dados["categoria_id"] = $this->input->post('categoria');
		$dados["titulo"] = $this->input->post('titulo');
		$dados["descricao"] = $this->input->post('desc');
		$dados["catalogoservico_id"] = $this->input->post('tipo_servico');
		$dados["dono_id"] = ($this->input->post('dono')) ? $this->input->post('dono') : $this->session->userdata('user_id');
		$dados["responsavel_id"] = $this->input->post('responsavel');
		$dados["prioridade_id"] = ($this->input->post('prioridade')) ? $this->input->post('prioridade') : 1;
		$dados["interessados"] = serialize($this->input->post('interessados'));
		$dados["atualizado_por"] = $this->session->userdata('user_id');
		$dados["plano_acao"] = $this->input->post('plano_acao');
		$dados["nivel_id"] = $this->input->post('nivel');
		$dados["desenvolvedor_id"] = $this->input->post('desenvolvedor');

		$produtos_habilitados = $this->chamados_model->get_produtos_habilitados($this->input->post('unidade'));
		if ($produtos_habilitados->num_rows() > 0):
			foreach ($produtos_habilitados->result() as $value) {
				if ($value->id == $this->input->post('produto')) {
					$dados["contrato_sax"] = $value->contrato_sax;
				}
			}
		endif;

		if ($this->form_validation->run() == TRUE && $id = $this->chamados_model->create($dados)) {
			//kanban----
			$back_to_kanban = false;
			$this->kanban_model->atualizarKanban($this->input->post(), $id, true);
			/** fim kanban **/

			$erros_anexos = $this->salvar_anexos_abertura($id);

			$chamado = $this->chamados_model->get(array(
				'select' => 'c.id, c.categoria_id, c.catalogoservico_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id, p.nome as produto_desc, m.nome as modulo_desc, mc.nome as mcategoria_desc, ud.first_name as first_name_dono, ud.last_name as last_name_dono, u.email as email_resp, ud.email as email_dono, c.interessados, r.id as chamado_referencia_id, r.titulo as chamado_referencia_titulo, c.plano_acao, n.descricao as nivel_desc, c.desenvolvedor_id, udev.first_name as first_name_dev, udev.last_name as last_name_dev, udev.email as email_dev',
				'join' => array(array('produtos p', 'p.id = c.produto_id', 'left'), array('modulos m', 'm.id = c.modulo_id', 'left'), array('modulos_categoria mc', 'mc.id = c.categoria_id', 'left'), array('users u', 'u.id = c.responsavel_id', 'left'), array('users ud', 'ud.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner'), array('chamado r', 'r.id = c.referencia_id', 'left'), array('nivel n', 'n.id = c.nivel_id', 'left'), array('users udev', 'udev.id = c.desenvolvedor_id', 'left')),
				'where' => 'c.id = "' . $id . '"',
				'order_by' => array('c.id', 'desc')
			))->row();


			if ($is_full_create == true && ($chamado->plano_acao != "" && $chamado->plano_acao != 0)) {
				$this->atualizar_plano_acao($chamado->plano_acao);
			}

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

			list($padrao_email_ass, $padrao_email_msg) = $this->montar_email_chamado($id, $chamado);

			/*$this->email->clear();
			$this->email->from($from_email, utf8_decode($from_name));

			$this->email->to(array_unique($emails_to));
            $this->email->subject(utf8_decode($padrao_email_ass));
            $this->email->message(utf8_decode($padrao_email_msg));
			$this->email->send();*/

			$mail = $this->phpmailer_library->load();
			$mail->isSMTP();
			$mail->Host     = $email_config['smtp_host'];
			$mail->SMTPAuth = true;
			$mail->Username = $email_config['smtp_user'];
			$mail->Password = $email_config['smtp_pass'];
			$mail->SMTPSecure = 'tsl';
			$mail->Port     = $email_config['smtp_port'];

			$mail->setFrom($from_email, utf8_decode($from_name));

			if (is_array(unserialize($chamado->interessados))) {
				foreach (unserialize($chamado->interessados) as $int) {
					$mail->addAddress($this->chamados_model->get_email($int));
				}
			}
			$mail->addAddress($chamado->email_dono);
			$mail->addAddress($chamado->email_resp);
			$mail->addAddress($chamado->email_dev);

			$mail->Subject = utf8_decode($padrao_email_ass);
			$mail->isHTML(true);

			$mail->Body = utf8_decode($padrao_email_msg);
			$mail->send();

			// Aviso por WhatsApp desabilitado por enquanto no Maestriatech.
			// $this->avisar_whatsapp_solicitante($id, 'abertura');

			$mensagem_criacao = show_alert_success($this->chamados_model->messages());
			if ($erros_anexos) {
				$mensagem_criacao .= show_alert_error('Alguns anexos não foram enviados:<br>' . implode('<br>', $erros_anexos));
			}
			$this->session->set_flashdata('message', $mensagem_criacao);

			$dados_diag = array();
			$dados_diag["chamado_id"] = $id;
			$dados_diag["responsavel_id"] = $this->session->userdata('user_id');
			$dados_diag["descricao"] = 'Chamado aberto por: ' . $this->chamados_model->get_user_name($this->session->userdata('user_id')) . '.';
			$dados_diag["data_envio"] = date("Y-m-d H:i:s");

			$dialogo_create = $this->chamados_model->create_dialogo($dados_diag);

			if ($back_to_kanban == true)
				redirect('kanban/index/', 'refresh');

			redirect('chamados/details/' . $id, 'refresh');
		} else {

			$unidade_selecionada = '';
			if ($this->uri->segment(3) == "planoacao") {
				$plano_unidade = $this->chamados_model->get_plano_acao("p.id = " . $this->uri->segment(4));
				if ($plano_unidade->num_rows() > 0) {
					$unidade_selecionada = $plano_unidade->row()->unidade_id;
				}
			}

			if ($_POST || $unidade_selecionada != '') {
				if ($_POST)
					$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->chamados_model->errors() ? show_alert_error($this->chamados_model->errors()) : show_alert_error($this->session->flashdata('message'))));

				if ($this->input->post('unidade') || $unidade_selecionada != '') {
					if ($this->input->post('unidade'))
						$produtos_habilitados = $this->chamados_model->get_produtos_habilitados($this->input->post('unidade'));
					else
						$produtos_habilitados = $this->chamados_model->get_produtos_habilitados($unidade_selecionada);
					$produto_options = array("" => "Selecione");
					if ($produtos_habilitados->num_rows() > 0):
						foreach ($produtos_habilitados->result() as $value) {
							$produto_options[$value->id] = $value->nome;
						}
					endif;

					if ($this->input->post('unidade')) {
						$users_unidade = $this->chamados_model->get_users_unidade($this->input->post('unidade'));
						$users_unidade_interessados = $this->chamados_model->get_users_interessados_unidade($this->input->post('unidade'));
					} else {
						$users_unidade = $this->chamados_model->get_users_unidade($unidade_selecionada);
						$users_unidade_interessados = $this->chamados_model->get_users_interessados_unidade($unidade_selecionada);
					}
					$dono_options = array("" => "Selecione");
					$interessados_options = array();
					if ($users_unidade->num_rows() > 0):
						foreach ($users_unidade->result() as $value) {
							$dono_options[$value->id] = $value->first_name . " " . $value->last_name;
						}
					endif;
					if ($users_unidade_interessados->num_rows() > 0):
						foreach ($users_unidade_interessados->result() as $value) {
							$interessados_options[$value->id] = $value->first_name . " " . $value->last_name . " (" . $value->email . ")";
						}
					endif;
				}
				if ($this->input->post('produto')) {
					$modulos_habilitados = $this->chamados_model->get_modulos_habilitados($this->input->post('unidade'), $this->input->post('produto'));
					$modulo_options = array("" => "Selecione");
					if ($modulos_habilitados->num_rows() > 0):
						foreach ($modulos_habilitados->result() as $value) {
							$modulo_options[$value->id] = $value->nome;
						}
					endif;
				}
				if ($this->input->post('modulo')) {
					$modulo_categorias = $this->chamados_model->get_modulo_categorias($this->input->post('modulo'));
					$modulo_categoria_options = array("" => "Selecione");
					if ($modulo_categorias->num_rows() > 0):
						foreach ($modulo_categorias->result() as $value) {
							$modulo_categoria_options[$value->id] = $value->nome;
						}
					endif;
				}
				if ($this->input->post('categoria')) {
					$tipo_servicos = $this->chamados_model->get_catalogos_servico_ativos($this->input->post('categoria'));
					$tipo_servicos_options = array("" => "Selecione");
					if ($tipo_servicos->num_rows() > 0):
						foreach ($tipo_servicos->result() as $serv) {
							$tipo_servicos_options[$serv->id] = $serv->descricao;
						}
					endif;
				}
			}

			$unidades_permitidas_usuario = $this->chamados_model->get_unidades_permitidas($this->session->userdata('user_id'), $this->session->userdata('empresa_id'))->result();
			$unidade_in_where = array(0);
			$unidade_options = array("" => "Selecione");
			foreach ($unidades_permitidas_usuario as $unid) {
				$unidade_in_where[] = $unid->unidade_id;
				$unidade_options[$unid->unidade_id] = $unid->unidade . " - " . $unid->razao_social;
			}
			$chamados_referencia = $this->chamados_model->get(array(
				'select' => 'c.id, c.titulo',
				'join' => array(array('unidades un', 'un.id = c.unidade_id', 'inner')),
				'where' => 'c.status_id = 1 AND c.unidade_id IN (' . implode(",", $unidade_in_where) . ')',
				'order_by' => array('c.id', 'desc')
			));
			$referencia_options = array("" => "Selecione");
			foreach ($chamados_referencia->result() as $ch) {
				$referencia_options[$ch->id] = $ch->id . " - " . $ch->titulo;
			}
			$this->data['referencia'] = array(
				'name'  => 'referencia',
				'id'    => 'referencia',
				'class' => 'singleInputDynamic form-control',
				'options' => $referencia_options,
				'selected' => ($this->input->post('referencia')) ? $this->input->post('referencia') : ''
			);

			$quadros = $this->chamados_model->get_quadros(null, 'status != "Finalizado"');
			$quadro_options = array();
			foreach ($quadros->result() as $q) {
				$quadro_options[$q->id] = $q->nome;
			}
			$this->data['quadro'] = array(
				'name'  => 'quadro',
				'id'    => 'quadro',
				'class' => 'singleInputDynamic form-control',
				'options' => $quadro_options,
				'selected' => ($this->input->post('quadro')) ? $this->input->post('quadro') : @$_GET["quadro"]
			);

			$this->data['unidade'] = array(
				'name'  => 'unidade',
				'id'    => 'unidade',
				'class' => 'singleInputDynamic form-control',
				'options' => $unidade_options,
				'selected' => $this->form_validation->set_value('unidade', $unidade_selecionada)
			);

			if (!$this->input->post('unidade') && $unidade_selecionada == '')
				$produto_options = array("" => "Selecione");
			$this->data['produto'] = array(
				'name'  => 'produto',
				'id'    => 'produto',
				'class' => 'form-control',
				'options' => $produto_options,
				'selected' => $this->form_validation->set_value('produto')
			);

			if (!$this->input->post('produto'))
				$modulo_options = array("" => "Selecione");
			$this->data['modulo'] = array(
				'name'  => 'modulo',
				'id'    => 'modulo',
				'class' => 'form-control',
				'options' => $modulo_options,
				'selected' => $this->form_validation->set_value('modulo')
			);

			if (!$this->input->post('modulo'))
				$modulo_categoria_options = array("" => "Selecione");
			$this->data['categoria'] = array(
				'name'  => 'categoria',
				'id'    => 'categoria',
				'class' => 'form-control',
				'options' => $modulo_categoria_options,
				'selected' => $this->form_validation->set_value('categoria')
			);

			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo')
			);

			$this->data['desc'] = array(
				'name'  => 'desc',
				'id'    => 'desc',
				'class' => 'form-control',
				'value' => $this->form_validation->set_value('desc')
			);

			if (!$this->input->post('unidade') && $unidade_selecionada == "")
				$interessados_options = array();
			$this->data['interessados'] = array(
				'name'  => 'interessados[]',
				'id'    => 'interessados',
				'class' => 'form-control',
				'multiple' => 'multiple',
				'options' => $interessados_options,
				'selected' => $this->input->post('interessados')
			);

			if (!$this->input->post('categoria'))
				$tipo_servicos_options = array("" => "Selecione");
			$this->data['tipo_servico'] = array(
				'name'  => 'tipo_servico',
				'id'    => 'tipo_servico',
				'class' => 'form-control',
				'options' => $tipo_servicos_options,
				'selected' => $this->form_validation->set_value('tipo_servico')
			);

			$niveis = $this->chamados_model->get_niveis_ativos();
			$niveis_options = array("" => "Selecione");
			foreach ($niveis->result() as $n) {
				$niveis_options[$n->id] = $n->descricao;
			}
			$select_nivel = "";
			if ($this->form_validation->set_value('nivel')) {
				$select_nivel = $this->form_validation->set_value('nivel');
			} elseif ($this->form_validation->set_value('tipo_servico')) {
				$niveis = $this->chamados_model->get_niveis_ativos($this->form_validation->set_value('tipo_servico'));
				if ($niveis->num_rows() == 1) {
					$select_nivel = $niveis->row()->id;
				}
			}
			$this->data['nivel'] = array(
				'name'  => 'nivel',
				'id'    => 'nivel',
				'class' => 'form-control',
				'options' => $niveis_options,
				'selected' => $select_nivel
			);

			if ($is_full_create == true) {
				foreach ($this->chamados_model->get_prioridades()->result() as $prior) {
					$prioridade_options[$prior->id] = $prior->descricao;
				}
				$this->data['prioridade'] = array(
					'name'  => 'prioridade',
					'id'    => 'prioridade',
					'class' => 'form-control',
					'options' => $prioridade_options,
					'selected' => $this->form_validation->set_value('prioridade', 2),
				);

				if (!$this->input->post('unidade') && $unidade_selecionada == "")
					$dono_options = array("" => "Selecione");
				$this->data['dono'] = array(
					'name'  => 'dono',
					'id'    => 'dono',
					'class' => 'form-control',
					'options' => $dono_options,
					'selected' => $this->form_validation->set_value('dono')
				);

				$grupos_responsavel_chamado_options = array("" => "Selecione");
				foreach ($grupos_usuarios_chamado->result() as $usr) {
					$grupos_responsavel_chamado_options[$usr->id] = $usr->first_name . " " . $usr->last_name;
				}
				$this->data['responsavel'] = array(
					'name'  => 'responsavel',
					'id'    => 'responsavel',
					'class' => 'singleInputDynamic form-control',
					'options' => $grupos_responsavel_chamado_options,
					'selected' => $this->form_validation->set_value('responsavel', $this->session->userdata('user_id'))
				);

				$grupos_usuarios_chamado_dev = $this->chamados_model->get_groups_dev();
				$grupos_dev_chamado_options = array("" => "Selecione");
				foreach ($grupos_usuarios_chamado_dev->result() as $usr) {
					$grupos_dev_chamado_options[$usr->id] = $usr->first_name . " " . $usr->last_name;
				}
				$this->data['desenvolvedor'] = array(
					'name'  => 'desenvolvedor',
					'id'    => 'desenvolvedor',
					'class' => 'singleInputDynamic form-control',
					'options' => $grupos_dev_chamado_options,
					'selected' => $this->form_validation->set_value('desenvolvedor')
				);
			}

			$plano_acao_options = array("" => "Selecione");
			if ($unidade_selecionada != '') {
				foreach ($this->chamados_model->get_plano_acao("p.unidade_id = '" . $unidade_selecionada . "' OR p.unidade_id IS NULL")->result() as $plano) {
					$plano_acao_options[$plano->id] = $plano->desc;
				}
			} else {
				foreach ($this->chamados_model->get_plano_acao("p.unidade_id IS NULL")->result() as $plano) {
					$plano_acao_options[$plano->id] = $plano->desc;
				}
			}
			$this->data['plano_acao'] = array(
				'name'  => 'plano_acao',
				'id'    => 'plano_acao',
				'class' => 'form-control',
				'options' => $plano_acao_options,
				'selected' => ($this->uri->segment(3) == "planoacao") ? $this->uri->segment(4) : $this->form_validation->set_value('plano_acao')
			);

			/* Load Template */
			$this->template->admin_render('admin/chamados/create', $this->data);
		}
	}

	/**
	 * Assunto e corpo do e-mail de abertura (modelo tipo 5), usado ao criar e ao editar o chamado.
	 */
	private function montar_email_chamado($id, $chamado = null)
	{
		return $this->aplicar_email_chamado(5, $id);
	}

	/**
	 * Monta assunto e corpo de um modelo de e-mail de chamado (tipos 5 a 10) com os marcadores comuns
	 * e os específicos do evento ($extras). Mensagens internas só entram na conversa quando
	 * $incluir_internos é verdadeiro (e-mail enviado apenas para a equipe).
	 */
	private function aplicar_email_chamado($tipo, $chamado_id, $extras = array(), $incluir_internos = false, $pular_recentes = 0)
	{
		$padrao_email = $this->ion_auth->get_email_padrao($tipo)->row();
		$chamado = $this->dados_email_chamado($chamado_id);
		$marcadores = $this->marcadores_email_chamado($chamado);
		$marcadores['{timeline}'] = $this->timeline_email_html($chamado_id, $incluir_internos, $pular_recentes);

		$mensagem = strtr($padrao_email->mensagem, array_merge($marcadores, $extras));
		$assunto = strtr($padrao_email->assunto, array(
			'{chamado_id}' => $chamado->id,
			'{titulo}' => $chamado->titulo,
			'{chamado_status}' => isset($extras['{chamado_status}']) ? html_entity_decode(strip_tags($extras['{chamado_status}']), ENT_QUOTES, 'UTF-8') : $chamado->status,
		));
		return array($assunto, $mensagem);
	}

	private function dados_email_chamado($chamado_id)
	{
		return $this->db->select(
			'c.id, c.titulo, c.descricao, c.created, c.data_prazo, c.referencia_id, c.status_id, un.unidade AS cliente, ' .
			'p.nome AS produto_desc, m.nome AS modulo_desc, mc.nome AS mcategoria_desc, cs.descricao AS catalogo_desc, ' .
			'n.descricao AS nivel_desc, cst.descricao AS status, cp.descricao AS prioridade, ' .
			'r.id AS chamado_referencia_id, r.titulo AS chamado_referencia_titulo, u.first_name, u.last_name, ' .
			'ud.first_name AS first_name_dono, ud.last_name AS last_name_dono, udev.first_name AS first_name_dev, udev.last_name AS last_name_dev',
			false
		)
			->from('chamado c')
			->join('unidades un', 'un.id = c.unidade_id')
			->join('produtos p', 'p.id = c.produto_id', 'left')
			->join('modulos m', 'm.id = c.modulo_id', 'left')
			->join('modulos_categoria mc', 'mc.id = c.categoria_id', 'left')
			->join('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left')
			->join('nivel n', 'n.id = c.nivel_id', 'left')
			->join('chamado_status cst', 'cst.id = c.status_id', 'left')
			->join('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'left')
			->join('chamado r', 'r.id = c.referencia_id', 'left')
			->join('users u', 'u.id = c.responsavel_id', 'left')
			->join('users ud', 'ud.id = c.dono_id', 'left')
			->join('users udev', 'udev.id = c.desenvolvedor_id', 'left')
			->where('c.id', (int) $chamado_id)
			->get()
			->row();
	}

	private function marcadores_email_chamado($chamado)
	{
		$nao_definido = '<span style="color:#999999;">N&atilde;o definido</span>';
		$texto = function ($valor) use ($nao_definido) {
			$valor = trim((string) $valor);
			return $valor !== '' ? html_escape($valor) : $nao_definido;
		};
		$referencia = '';
		if ($chamado->referencia_id != "") {
			$referencia = '<p style="margin:0 0 16px;padding:10px 14px;background:#fff8e5;border-left:3px solid #f39c12;font-size:13px;color:#555555;"><b>Relacionado ao chamado #' . (int) $chamado->chamado_referencia_id . '</b> &ndash; ' . html_escape($chamado->chamado_referencia_titulo) . '</p>';
		}

		return array(
			'{link_chamado}' => base_url('chamados/details/' . $chamado->id),
			'{logo_url}' => base_url('assets/images/xkey-chamados.png'),
			'{chamado_id}' => $chamado->id,
			'{titulo}' => html_escape($chamado->titulo),
			'{descricao}' => nl2br(html_escape($chamado->descricao)),
			'{unidade_desc}' => $texto($chamado->cliente),
			'{produto_desc}' => $texto($chamado->produto_desc),
			'{modulo_desc}' => $texto($chamado->modulo_desc),
			'{categoria_desc}' => $texto($chamado->mcategoria_desc),
			'{tipo_servico_desc}' => $texto($chamado->catalogo_desc),
			'{nivel}' => $texto($chamado->nivel_desc),
			'{status}' => $texto($chamado->status),
			'{prioridade}' => $texto($chamado->prioridade),
			'{prioridade_cor}' => $this->cor_prioridade_email($chamado->prioridade),
			'{dono}' => $texto($chamado->first_name_dono . ' ' . $chamado->last_name_dono),
			'{responsavel}' => $texto($chamado->first_name . ' ' . $chamado->last_name),
			'{desenvolvedor}' => $texto($chamado->first_name_dev . ' ' . $chamado->last_name_dev),
			'{data_abertura}' => date('d/m/Y', strtotime($chamado->created)) . ' &agrave;s ' . date('H:i', strtotime($chamado->created)),
			'{previsao}' => $chamado->data_prazo ? date('d/m/Y', strtotime($chamado->data_prazo)) . ' &agrave;s ' . date('H:i', strtotime($chamado->data_prazo)) : $nao_definido,
			'{referencia}' => $referencia,
			'{anexos}' => $this->bloco_anexos_email($chamado->id),
		);
	}

	/**
	 * Últimas interações do chamado com estilos inline (os e-mails não carregam o CSS do painel).
	 */
	private function timeline_email_html($chamado_id, $incluir_internos = false, $pular_recentes = 0, $limite = 5)
	{
		$this->db->select('d.descricao, d.data_envio AS created, d.interno, u.first_name, u.last_name, NULL AS anexo', false)
			->from('chamado_dialogo d')
			->join('users u', 'u.id = d.responsavel_id', 'left')
			->where('d.chamado_id', (int) $chamado_id);
		if (!$incluir_internos) {
			$this->db->where('d.interno', 0);
		}
		$itens = $this->db->order_by('d.data_envio', 'desc')->limit($limite + $pular_recentes)->get()->result_array();
		$anexos = $this->db->select('a.descricao, a.created, 0 AS interno, u.first_name, u.last_name, a.anexo', false)
			->from('chamado_anexo a')
			->join('users u', 'u.id = a.responsavel_id', 'left')
			->where('a.chamado_id', (int) $chamado_id)
			->order_by('a.created', 'desc')
			->limit($limite + $pular_recentes)
			->get()
			->result_array();
		$itens = array_merge($itens, $anexos);
		usort($itens, function ($a, $b) {
			return strtotime($b['created']) - strtotime($a['created']);
		});
		$itens = array_slice($itens, $pular_recentes, $limite);

		$titulo = '<p style="margin:24px 0 8px;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#3c8dbc;">&Uacute;ltimas intera&ccedil;&otilde;es</p>';
		if (!$itens) {
			return $titulo . '<p style="margin:0;font-size:14px;color:#999999;">Nenhuma intera&ccedil;&atilde;o anterior.</p>';
		}

		$linhas = '';
		foreach ($itens as $item) {
			$autor = trim($item['first_name'] . ' ' . $item['last_name']);
			$selo = !empty($item['interno']) ? ' <span style="display:inline-block;padding:1px 6px;border-radius:3px;background:#f39c12;color:#ffffff;font-size:11px;">Interno</span>' : '';
			$corpo = trim((string) $item['descricao']) !== '' ? '<div style="margin-top:4px;font-size:14px;line-height:1.5;color:#444444;">' . nl2br(html_escape($item['descricao'])) . '</div>' : '';
			if (!empty($item['anexo'])) {
				$corpo .= '<div style="margin-top:6px;font-size:13px;">&#128206; <a href="' . base_url('upload/anexo/' . rawurlencode($item['anexo'])) . '" style="color:#3c8dbc;text-decoration:none;font-weight:bold;">' . html_escape($item['anexo']) . '</a></div>';
			}
			$linhas .= '<tr><td style="padding:10px 12px;border-bottom:1px solid #eeeeee;">' .
				'<div style="font-size:12px;color:#888888;"><b style="color:#333333;">' . html_escape($autor !== '' ? $autor : 'Sistema') . '</b> &middot; ' . date('d/m/Y H:i', strtotime($item['created'])) . $selo . '</div>' .
				$corpo .
			'</td></tr>';
		}

		return $titulo . '<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eeeeee;border-radius:4px;border-collapse:separate;">' . $linhas . '</table>' .
			'<p style="margin:8px 0 0;font-size:12px;"><a href="' . base_url('chamados/details/' . (int) $chamado_id) . '" style="color:#3c8dbc;">Ver a conversa completa no chamado</a></p>';
	}

	/**
	 * Envia ao solicitante o modelo de WhatsApp do evento ('abertura' ou 'encerramento')
	 * e registra o resultado como diálogo interno. Nunca interrompe o fluxo do chamado.
	 */
	private function avisar_whatsapp_solicitante($chamado_id, $evento)
	{
		$this->config->load('flwchat', true);
		$template_id = $this->config->item('flwchat_modelo_' . $evento, 'flwchat');
		$this->load->library('flwchat_notifier');
		if (!$template_id || !$this->flwchat_notifier->is_configured()) {
			return;
		}

		$chamado = $this->db->select(
			'c.id, c.titulo, un.empresa_id, dono.first_name AS nome_dono, p.celular, pri.descricao AS prioridade, ' .
			'CONCAT(resp.first_name, " ", resp.last_name) AS responsavel', false
		)
			->from('chamado c')
			->join('unidades un', 'un.id = c.unidade_id')
			->join('users dono', 'dono.id = c.dono_id', 'left')
			->join('pessoas p', 'p.user_id = c.dono_id', 'left')
			->join('users resp', 'resp.id = c.responsavel_id', 'left')
			->join('chamado_prioridade pri', 'pri.id = c.prioridade_id', 'left')
			->where('c.id', (int) $chamado_id)
			->limit(1)
			->get()
			->row();
		if (!$chamado || (int) $chamado->empresa_id !== (int) $this->config->item('flwchat_empresa_id', 'flwchat')) {
			return;
		}

		$telefone = Flwchat_notifier::normalize_phone($chamado->celular);
		if ($telefone === '') {
			$this->registrar_aviso_whatsapp($chamado_id, 'Aviso de ' . $evento . ' não enviado pelo WhatsApp: o solicitante não tem celular válido no cadastro.');
			return;
		}

		$nome = trim($chamado->nome_dono) !== '' ? strtok(trim($chamado->nome_dono), ' ') : 'cliente';
		if ($evento === 'abertura') {
			$parametros = array(
				'[NOME]' => $nome,
				'[CHAMADO]' => '#' . $chamado->id,
				'[ASSUNTO]' => $chamado->titulo,
				'[PRIORIDADE]' => $chamado->prioridade,
				'[ATENDENTE]' => trim($chamado->responsavel) !== '' ? trim($chamado->responsavel) : 'Equipe XKEY',
				'[NUMERO-CHAMADO]' => (string) $chamado->id,
			);
		} else {
			$parametros = array(
				'[NOME]' => $nome,
				'[CHAMADO]' => '#' . $chamado->id . ' - ' . $chamado->titulo,
				'[NUMERO-CHAMADO]' => (string) $chamado->id,
			);
		}

		$telefone_desc = '+' . $telefone;
		if ($this->flwchat_notifier->send_template($telefone, $template_id, $parametros, 'chamado-' . $chamado->id . '-' . $evento)) {
			$this->registrar_aviso_whatsapp($chamado_id, 'Aviso de ' . $evento . ' enviado pelo WhatsApp para ' . $telefone_desc . '.');
		} else {
			log_message('error', 'FLW Chat: chamado ' . $chamado->id . ' (' . $evento . ') - ' . $this->flwchat_notifier->last_error());
			$this->registrar_aviso_whatsapp($chamado_id, 'Falha ao enviar o aviso de ' . $evento . ' pelo WhatsApp para ' . $telefone_desc . ': ' . $this->flwchat_notifier->last_error());
		}
	}

	/**
	 * Avisa por mensagem direta no Discord o responsável pelo chamado encerrado, quando ele tem
	 * Discord cadastrado no planejamento do Kanban. Nunca interrompe o fluxo do chamado.
	 */
	private function avisar_discord_encerramento($chamado_id)
	{
		$this->load->model('kanban_dashboard_model');
		$this->load->library('discord_notifier');
		if (!$this->discord_notifier->is_configured() || !$this->kanban_dashboard_model->has_discord_tables()) {
			return;
		}

		$chamado = $this->db->select('c.id, c.titulo, c.responsavel_id, un.empresa_id, un.unidade AS cliente')
			->from('chamado c')
			->join('unidades un', 'un.id = c.unidade_id')
			->where('c.id', (int) $chamado_id)
			->limit(1)
			->get()
			->row();
		if (!$chamado || (int) $chamado->responsavel_id <= 0) {
			return;
		}

		$discord_ids = $this->kanban_dashboard_model->get_discord_ids((int) $chamado->empresa_id);
		if (empty($discord_ids[(int) $chamado->responsavel_id])) {
			return;
		}

		$escape = function ($text) {
			return addcslashes(trim((string) $text), '\\*_~`|>[]');
		};
		$mensagem = '✅ **Chamado [#' . (int) $chamado->id . '](<' . base_url('chamados/details/' . (int) $chamado->id) . '>) encerrado**' .
			"\n" . $escape($chamado->titulo) .
			"\nCliente: " . $escape($chamado->cliente) .
			"\nEncerrado por " . $escape($this->chamados_model->get_user_name($this->session->userdata('user_id'))) . ' em ' . date('d/m/Y') . ' às ' . date('H:i') . '.';

		if (!$this->discord_notifier->send_direct_message($discord_ids[(int) $chamado->responsavel_id], $mensagem)) {
			log_message('error', 'Discord: aviso de encerramento do chamado ' . (int) $chamado->id . ' - ' . $this->discord_notifier->last_error());
		}
	}

	/**
	 * Usuário dos grupos de responsável ou programador do chamado, que pode marcar mensagens como internas.
	 */
	private function pode_marcar_interno()
	{
		$query_pref = $this->db->get_where('admin_preferences', array('empresa_id' => $this->session->userdata('empresa_id')))->row();
		if (!$query_pref) {
			return false;
		}
		$grupos = array_merge((array) @unserialize($query_pref->grupos_responsavel_chamado), (array) @unserialize($query_pref->grupos_dev_chamado));
		$grupos = array_unique(array_filter(array_map('intval', $grupos)));
		if (!$grupos) {
			return false;
		}
		return $this->db->join('groups g', 'g.id = ug.group_id')
			->where_in('ug.group_id', $grupos)
			->where('g.empresa_id', $this->session->userdata('empresa_id'))
			->where('ug.user_id', $this->session->userdata('user_id'))
			->count_all_results('users_groups ug') > 0;
	}

	private function registrar_aviso_whatsapp($chamado_id, $descricao)
	{
		$this->chamados_model->create_dialogo(array(
			'chamado_id' => (int) $chamado_id,
			'responsavel_id' => $this->session->userdata('user_id'),
			'descricao' => $descricao,
			'data_envio' => date('Y-m-d H:i:s'),
			'interno' => 1,
		));
	}

	private function cor_prioridade_email($prioridade)
	{
		$cores = array('alta' => '#dd4b39', 'média' => '#f39c12', 'media' => '#f39c12', 'normal' => '#f39c12', 'baixa' => '#00a65a');
		$chave = mb_strtolower(trim($prioridade), 'UTF-8');
		return isset($cores[$chave]) ? $cores[$chave] : '#777777';
	}

	/**
	 * Lista os anexos do chamado para o marcador {anexos} do e-mail de abertura.
	 */
	private function bloco_anexos_email($chamado_id)
	{
		$anexos = $this->chamados_model->get_anexos($chamado_id)->result();
		$titulo = '<p style="margin:24px 0 8px;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#3c8dbc;">Anexos (' . count($anexos) . ')</p>';
		if (!$anexos) {
			return $titulo . '<p style="margin:0;font-size:14px;color:#999999;">Nenhum arquivo anexado na abertura.</p>';
		}

		$linhas = '';
		foreach (array_reverse($anexos) as $anexo) {
			$arquivo = FCPATH . 'upload/anexo/' . $anexo->anexo;
			$tamanho = is_file($arquivo) ? filesize($arquivo) : 0;
			$tamanho_desc = $tamanho >= 1048576 ? number_format($tamanho / 1048576, 1, ',', '.') . ' MB' : max(1, round($tamanho / 1024)) . ' KB';
			$linhas .= '<tr>' .
				'<td style="padding:10px 12px;border-bottom:1px solid #eeeeee;font-size:14px;">' .
					'<a href="' . base_url('upload/anexo/' . rawurlencode($anexo->anexo)) . '" style="color:#3c8dbc;text-decoration:none;font-weight:bold;">' . html_escape($anexo->anexo) . '</a>' .
				'</td>' .
				'<td align="right" style="padding:10px 12px;border-bottom:1px solid #eeeeee;font-size:12px;color:#999999;white-space:nowrap;">' . ($tamanho ? $tamanho_desc : '') . '</td>' .
			'</tr>';
		}

		return $titulo . '<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eeeeee;border-radius:4px;border-collapse:separate;">' . $linhas . '</table>';
	}

	/**
	 * Grava os arquivos enviados no campo anexo[] da abertura do chamado.
	 * Retorna a lista de erros, sem impedir a criação do chamado.
	 */
	private function salvar_anexos_abertura($chamado_id, $descricao = 'Anexo enviado na abertura do chamado.')
	{
		if (empty($_FILES['anexo']['name']) || !is_array($_FILES['anexo']['name'])) {
			return array();
		}

		$config = get_upload_config();
		if (!is_dir($config['upload_path'])) {
			@mkdir($config['upload_path'], 0755, true);
		}
		$this->load->library('upload', $config);

		$erros = array();
		$arquivos = $_FILES['anexo'];
		foreach ($arquivos['name'] as $key => $nome) {
			if ($arquivos['error'][$key] === UPLOAD_ERR_NO_FILE || $nome === '') {
				continue;
			}

			$_FILES['anexo_abertura'] = array(
				'name' => removerAcentos($nome),
				'type' => $arquivos['type'][$key],
				'tmp_name' => $arquivos['tmp_name'][$key],
				'error' => $arquivos['error'][$key],
				'size' => $arquivos['size'][$key],
			);

			$this->upload->initialize($config);
			if ($this->upload->do_upload('anexo_abertura')) {
				$this->chamados_model->create_anexo(array(
					'chamado_id' => $chamado_id,
					'responsavel_id' => $this->session->userdata('user_id'),
					'descricao' => $descricao,
					'anexo' => $this->upload->data('file_name'),
					'created' => date('Y-m-d H:i:s'),
				));
			} else {
				$erros[] = html_escape($nome) . ': ' . strip_tags($this->upload->display_errors('', ''));
			}
		}
		unset($_FILES['anexo_abertura']);

		return $erros;
	}

	public function edit($id)
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("chamados", "edit")) {
			redirect('auth', 'refresh');
		}

		$grupos_usuarios_chamado = $this->chamados_model->get_groups();
		if (! @$grupos_usuarios_chamado) {
			$this->session->set_flashdata('message', show_alert_error("Você deve parametrizar o grupo de usuários responsáveis pelo chamado antes."));
			redirect('chamados', 'refresh');
		}

		$chamado = $this->chamados_model->get(array(
			'select' => 'c.id, c.categoria_id, c.catalogoservico_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id, p.nome as produto_desc, m.nome as modulo_desc, mc.nome as mcategoria_desc, ud.first_name as first_name_dono, ud.last_name as last_name_dono, c.interessados, c.plano_acao, ud.email as email_dono, u.email as email_resp, c.nivel_id, c.desenvolvedor_id',
			'join' => array(array('produtos p', 'p.id = c.produto_id', 'left'), array('modulos m', 'm.id = c.modulo_id', 'left'), array('modulos_categoria mc', 'mc.id = c.categoria_id', 'left'), array('users u', 'u.id = c.responsavel_id', 'left'), array('users ud', 'ud.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner')),
			'where' => 'c.id = "' . $id . '"',
			'order_by' => array('c.id', 'desc')
		))->row();

		$is_full_create = $this->chamados_model->is_full_create();
		$this->data['is_full_create'] = $is_full_create;

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_chamados_create'), 'chamados/create');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('unidade', 'lang:create_validation_unidade_label', 'required');
		$this->form_validation->set_rules('produto', 'lang:create_validation_produto_label', 'required');
		$this->form_validation->set_rules('modulo', 'lang:create_validation_modulo_label', 'required');
		$this->form_validation->set_rules('titulo', 'lang:create_validation_titulo_label', 'trim|required');
		$this->form_validation->set_rules('desc', 'lang:create_validation_desc_label', 'trim|required');
		$this->form_validation->set_rules('categoria', 'lang:create_validation_categoria_label', 'required');
		$this->form_validation->set_rules('tipo_servico', 'lang:create_validation_tipo_label', 'required');
		$this->form_validation->set_rules('nivel', 'lang:create_validation_nivel_label', 'required');

		if ($is_full_create == true) {
			if ($this->input->post('dono'))
				$this->form_validation->set_rules('dono', 'lang:create_validation_dono_label', 'required');
			$this->form_validation->set_rules('responsavel', 'lang:create_validation_responsavel_label', 'required');
			$this->form_validation->set_rules('prioridade', 'lang:create_validation_prioridade_label', 'required');
		}

		$dados["referencia_id"] = ($this->input->post('referencia')) ? $this->input->post('referencia') : null;
		$dados["unidade_id"] = $this->input->post('unidade');
		$dados["produto_id"] = $this->input->post('produto');
		$dados["modulo_id"] = $this->input->post('modulo');
		$dados["categoria_id"] = $this->input->post('categoria');
		$dados["titulo"] = $this->input->post('titulo');
		$dados["descricao"] = $this->input->post('desc');
		$dados["catalogoservico_id"] = $this->input->post('tipo_servico');
		$dados["dono_id"] = ($this->input->post('dono')) ? $this->input->post('dono') : $this->session->userdata('user_id');
		$dados["responsavel_id"] = $this->input->post('responsavel');
		$dados["prioridade_id"] = ($this->input->post('prioridade')) ? $this->input->post('prioridade') : 1;
		$dados["interessados"] = serialize($this->input->post('interessados'));
		$dados["atualizado_por"] = $this->session->userdata('user_id');
		$dados["plano_acao"] = $this->input->post('plano_acao');
		$dados["nivel_id"] = $this->input->post('nivel');
		$dados["desenvolvedor_id"] = $this->input->post('desenvolvedor');

		// Só aceita destinos internos (ex.: kanban) para o retorno após salvar.
		$redirect = (string) $this->input->post('redirect');
		if (!preg_match('~^[a-z0-9_/-]+$~i', $redirect)) {
			$redirect = '';
		}

		if ($this->form_validation->run() == TRUE && $this->chamados_model->update($id, $dados)) {
			$erros_anexos = $this->salvar_anexos_abertura($id, 'Anexo enviado na edição do chamado.');

			//kanban----
			$back_to_kanban = false;
			if ($this->input->post('quadro') || $this->input->post('coluna')) {
				$back_to_kanban = true;

				$this->kanban_model->atualizarKanban($this->input->post(), $id, false);
			}
			//-----

			$chamado = $this->chamados_model->get(array(
				'select' => 'c.id, c.categoria_id, c.catalogoservico_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id, p.nome as produto_desc, m.nome as modulo_desc, mc.nome as mcategoria_desc, ud.first_name as first_name_dono, ud.last_name as last_name_dono, c.interessados, c.plano_acao, ud.email as email_dono, u.email as email_resp, r.id as chamado_referencia_id, r.titulo as chamado_referencia_titulo, n.descricao as nivel_desc, c.desenvolvedor_id, udev.first_name as first_name_dev, udev.last_name as last_name_dev, udev.email as email_dev',
				'join' => array(array('produtos p', 'p.id = c.produto_id', 'left'), array('modulos m', 'm.id = c.modulo_id', 'left'), array('modulos_categoria mc', 'mc.id = c.categoria_id', 'left'), array('users u', 'u.id = c.responsavel_id', 'left'), array('users ud', 'ud.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner'), array('chamado r', 'r.id = c.referencia_id', 'left'), array('nivel n', 'n.id = c.nivel_id', 'left'), array('users udev', 'udev.id = c.desenvolvedor_id', 'left')),
				'where' => 'c.id = "' . $id . '"',
				'order_by' => array('c.id', 'desc')
			))->row();

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

			list($padrao_email_ass, $padrao_email_msg) = $this->montar_email_chamado($id, $chamado);

			/*$this->email->clear();
			$this->email->from($from_email, utf8_decode($from_name));

			$emails_to = array();
			if(is_array(unserialize($chamado->interessados))) {
				foreach (unserialize($chamado->interessados) as $int) {
					$emails_to[] = $this->chamados_model->get_email($int);
				}
			}
			$emails_to[] = $chamado->email_dono;
			$emails_to[] = $chamado->email_resp;

			$this->email->to(array_unique($emails_to));
            $this->email->subject(utf8_decode($padrao_email_ass));
            $this->email->message(utf8_decode($padrao_email_msg));
			$this->email->send();*/

			$mail = $this->phpmailer_library->load();
			$mail->isSMTP();
			$mail->Host     = $email_config['smtp_host'];
			$mail->SMTPAuth = true;
			$mail->Username = $email_config['smtp_user'];
			$mail->Password = $email_config['smtp_pass'];
			$mail->SMTPSecure = 'tsl';
			$mail->Port     = $email_config['smtp_port'];

			$mail->setFrom($from_email, utf8_decode($from_name));

			if (is_array(unserialize($chamado->interessados))) {
				foreach (unserialize($chamado->interessados) as $int) {
					$mail->addAddress($this->chamados_model->get_email($int));
				}
			}
			$mail->addAddress($chamado->email_dono);
			$mail->addAddress($chamado->email_resp);
			$mail->addAddress($chamado->email_dev);

			$mail->Subject = utf8_decode($padrao_email_ass);
			$mail->isHTML(true);

			$mail->Body = utf8_decode($padrao_email_msg);
			$mail->send();

			$mensagem_edicao = show_alert_success($this->chamados_model->messages());
			if ($erros_anexos) {
				$mensagem_edicao .= show_alert_error('Alguns anexos não foram enviados:<br>' . implode('<br>', $erros_anexos));
			}
			$this->session->set_flashdata('message', $mensagem_edicao);

			if ($redirect == "")
				redirect('chamados/details/' . $id, 'refresh');
			else
				redirect($redirect, 'refresh');
		} else {
			if ($_POST) {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->chamados_model->errors() ? show_alert_error($this->chamados_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}

			if ($this->input->post('unidade') || $chamado->unidade_id) {
				$produtos_habilitados = $this->chamados_model->get_produtos_habilitados(($this->input->post('unidade')) ? $this->input->post('unidade') : $chamado->unidade_id);
				$produto_options = array("" => "Selecione");
				if ($produtos_habilitados->num_rows() > 0):
					foreach ($produtos_habilitados->result() as $value) {
						$produto_options[$value->id] = $value->nome;
					}
				endif;

				$users_unidade = $this->chamados_model->get_users_unidade(($this->input->post('unidade')) ? $this->input->post('unidade') : $chamado->unidade_id);
				$users_unidade_interessados = $this->chamados_model->get_users_interessados_unidade(($this->input->post('unidade')) ? $this->input->post('unidade') : $chamado->unidade_id);
				$dono_options = array("" => "Selecione");
				$interessados_options = array();
				if ($users_unidade->num_rows() > 0):
					foreach ($users_unidade->result() as $value) {
						$dono_options[$value->id] = $value->first_name . " " . $value->last_name;
					}
				endif;
				if ($users_unidade_interessados->num_rows() > 0):
					foreach ($users_unidade_interessados->result() as $value) {
						$interessados_options[$value->id] = $value->first_name . " " . $value->last_name . " (" . $value->email . ")";
					}
				endif;
			}
			if ($this->input->post('produto') || $chamado->produto_id) {
				$modulos_habilitados = $this->chamados_model->get_modulos_habilitados(($this->input->post('unidade')) ? $this->input->post('unidade') : $chamado->unidade_id, ($this->input->post('produto')) ? $this->input->post('produto') : $chamado->produto_id);
				$modulo_options = array("" => "Selecione");
				if ($modulos_habilitados->num_rows() > 0):
					foreach ($modulos_habilitados->result() as $value) {
						$modulo_options[$value->id] = $value->nome;
					}
				endif;
			}
			if ($this->input->post('modulo') || $chamado->modulo_id) {
				$modulo_categorias = $this->chamados_model->get_modulo_categorias(($this->input->post('modulo')) ? $this->input->post('modulo') : $chamado->modulo_id);
				$modulo_categoria_options = array("" => "Selecione");
				if ($modulo_categorias->num_rows() > 0):
					foreach ($modulo_categorias->result() as $value) {
						$modulo_categoria_options[$value->id] = $value->nome;
					}
				endif;
			}
			if ($this->input->post('categoria') || $chamado->categoria_id) {
				$tipo_servicos = $this->chamados_model->get_catalogos_servico_ativos(($this->input->post('categoria')) ? $this->input->post('categoria') : $chamado->categoria_id);
				$tipo_servicos_options = array("" => "Selecione");
				if ($tipo_servicos->num_rows() > 0):
					foreach ($tipo_servicos->result() as $serv) {
						$tipo_servicos_options[$serv->id] = $serv->descricao;
					}
				endif;
			}

			$unidades_permitidas_usuario = $this->chamados_model->get_unidades_permitidas($this->session->userdata('user_id'), $this->session->userdata('empresa_id'))->result();
			$unidade_in_where = array(0);
			$unidade_options = array("" => "Selecione");
			foreach ($unidades_permitidas_usuario as $unid) {
				$unidade_in_where[] = $unid->unidade_id;
				$unidade_options[$unid->unidade_id] = $unid->unidade . " - " . $unid->razao_social;
			}
			$chamados_referencia = $this->chamados_model->get(array(
				'select' => 'c.id, c.titulo',
				'join' => array(array('unidades un', 'un.id = c.unidade_id', 'inner')),
				'where' => 'c.status_id = 1 AND c.unidade_id IN (' . implode(",", $unidade_in_where) . ') AND c.id != ' . $id,
				'order_by' => array('c.id', 'desc')
			));
			$referencia_options = array("" => "Selecione");
			foreach ($chamados_referencia->result() as $ch) {
				$referencia_options[$ch->id] = $ch->id . " - " . $ch->titulo;
			}
			$this->data['referencia'] = array(
				'name'  => 'referencia',
				'id'    => 'referencia',
				'class' => 'singleInputDynamic form-control',
				'options' => $referencia_options,
				'selected' => ($this->input->post('referencia')) ? $this->input->post('referencia') : $chamado->referencia_id
			);

			$quadros = $this->chamados_model->get_quadros(null, '(q.status != "Finalizado" OR (q.status = "Finalizado" AND t.id = ' . $chamado->id . '))', 'q.id');

			$quadro_options = array();
			foreach ($quadros->result() as $q) {
				$quadro_options[$q->id] = $q->nome;
			}
			$quadro_atual = $this->chamados_model->get_quadro_atual($chamado->id)->row();
			$this->data['quadro'] = array(
				'name'  => 'quadro',
				'id'    => 'quadro',
				'class' => 'singleInputDynamic form-control',
				'options' => $quadro_options,
				'selected' => ($this->input->post('quadro')) ? $this->input->post('quadro') : @$quadro_atual->quadro
			);

			$this->data['unidade'] = array(
				'name'  => 'unidade',
				'id'    => 'unidade',
				'class' => 'singleInputDynamic form-control',
				'options' => $unidade_options,
				'selected' => $this->form_validation->set_value('unidade', $chamado->unidade_id)
			);

			$this->data['produto'] = array(
				'name'  => 'produto',
				'id'    => 'produto',
				'class' => 'form-control',
				'options' => $produto_options,
				'selected' => $this->form_validation->set_value('produto', $chamado->produto_id)
			);

			$this->data['modulo'] = array(
				'name'  => 'modulo',
				'id'    => 'modulo',
				'class' => 'form-control',
				'options' => @$modulo_options,
				'selected' => $this->form_validation->set_value('modulo', $chamado->modulo_id)
			);
			if (!isset($modulo_options)) {
				$this->data['modulo']['options'] = array("" => "Selecione");
			}

			$this->data['categoria'] = array(
				'name'  => 'categoria',
				'id'    => 'categoria',
				'class' => 'form-control',
				'options' => @$modulo_categoria_options,
				'selected' => $this->form_validation->set_value('categoria', $chamado->categoria_id)
			);
			if (!isset($modulo_categoria_options)) {
				$this->data['categoria']['options'] = array("" => "Selecione");
			}

			$niveis = $this->chamados_model->get_niveis_ativos();
			$niveis_options = array("" => "Selecione");
			foreach ($niveis->result() as $n) {
				$niveis_options[$n->id] = $n->descricao;
			}
			$this->data['nivel'] = array(
				'name'  => 'nivel',
				'id'    => 'nivel',
				'class' => 'form-control',
				'options' => $niveis_options,
				'selected' => $this->form_validation->set_value('nivel', $chamado->nivel_id)
			);

			$this->data['titulo'] = array(
				'name'  => 'titulo',
				'id'    => 'titulo',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => $this->form_validation->set_value('titulo', $chamado->titulo)
			);

			$this->data['desc'] = array(
				'name'  => 'desc',
				'id'    => 'desc',
				'class' => 'form-control',
				'value' => $this->form_validation->set_value('desc', $chamado->descricao)
			);

			$this->data['interessados'] = array(
				'name'  => 'interessados[]',
				'id'    => 'interessados',
				'class' => 'form-control',
				'multiple' => 'multiple',
				'options' => $interessados_options,
				'selected' => ($this->input->post('interessados')) ? $this->input->post('interessados') : unserialize($chamado->interessados)
			);

			$this->data['tipo_servico'] = array(
				'name'  => 'tipo_servico',
				'id'    => 'tipo_servico',
				'class' => 'form-control',
				'options' => $tipo_servicos_options,
				'selected' => $this->form_validation->set_value('tipo_servico', $chamado->catalogoservico_id)
			);

			if ($is_full_create == true) {
				foreach ($this->chamados_model->get_prioridades()->result() as $prior) {
					$prioridade_options[$prior->id] = $prior->descricao;
				}
				$this->data['prioridade'] = array(
					'name'  => 'prioridade',
					'id'    => 'prioridade',
					'class' => 'form-control',
					'options' => $prioridade_options,
					'selected' => $this->form_validation->set_value('prioridade', $chamado->prioridade_id),
				);

				$this->data['dono'] = array(
					'name'  => 'dono',
					'id'    => 'dono',
					'class' => 'form-control',
					'options' => $dono_options,
					'selected' => $this->form_validation->set_value('dono', $chamado->dono_id)
				);

				$grupos_responsavel_chamado_options = array("" => "Selecione");
				foreach ($grupos_usuarios_chamado->result() as $usr) {
					$grupos_responsavel_chamado_options[$usr->id] = $usr->first_name . " " . $usr->last_name;
				}
				$this->data['responsavel'] = array(
					'name'  => 'responsavel',
					'id'    => 'responsavel',
					'class' => 'singleInputDynamic form-control',
					'options' => $grupos_responsavel_chamado_options,
					'selected' => $this->form_validation->set_value('responsavel', $chamado->responsavel_id)
				);

				$grupos_usuarios_chamado_dev = $this->chamados_model->get_groups_dev();
				$grupos_dev_chamado_options = array("" => "Selecione");
				foreach ($grupos_usuarios_chamado_dev->result() as $usr) {
					$grupos_dev_chamado_options[$usr->id] = $usr->first_name . " " . $usr->last_name;
				}
				$this->data['desenvolvedor'] = array(
					'name'  => 'desenvolvedor',
					'id'    => 'desenvolvedor',
					'class' => 'singleInputDynamic form-control',
					'options' => $grupos_dev_chamado_options,
					'selected' => $this->form_validation->set_value('desenvolvedor', $chamado->desenvolvedor_id)
				);
			}

			$plano_acao_options = array("" => "Selecione");
			foreach ($this->chamados_model->get_plano_acao("p.unidade_id = '" . $chamado->unidade_id . "' OR p.unidade_id IS NULL")->result() as $plano) {
				$plano_acao_options[$plano->id] = $plano->desc;
			}
			$this->data['plano_acao'] = array(
				'name'  => 'plano_acao',
				'id'    => 'plano_acao',
				'class' => 'form-control',
				'options' => $plano_acao_options,
				'selected' => $this->form_validation->set_value('plano_acao', $chamado->plano_acao)
			);

			/* Load Template */
			$this->data['chamado'] = $chamado;
			$this->data['anexos_existentes'] = $this->chamados_model->get_anexos($id)->result();

			$this->template->admin_render('admin/chamados/edit', $this->data);
		}
	}

	public function delete()
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("chamados", "delete")) {
			redirect('auth/login', 'refresh');
		} elseif (! $this->ion_auth->is_admin()) {
			return show_error('You must be an administrator to view this page.');
		} else {
			$this->load->view('admin/unidades/delete');
		}
	}

	public function details($id)
	{
		if (! $this->ion_auth->logged_in() or ! $id or empty($id) or ! $this->ion_auth->has_permissao("chamados", "details")) {
			redirect('auth', 'refresh');
		}

		// carregar checklists e suas etapas para modal precoce
		$this->load->model('checklists_model');
		$this->load->model('checklists_etapas_model');
		$this->load->model('chamados_checklists_model');
		$this->load->model('chamados_checklists_etapas_model');

		$checklists = $this->checklists_model->get();
		$this->data['checklists'] = $checklists;
		$etapas_arr = array();
		if ($checklists->num_rows() > 0) {
			foreach ($checklists->result() as $cl) {
				$etapas_arr[$cl->id] = $this->checklists_etapas_model->get_by_checklist($cl->id);
			}
		}
		$this->data['checklist_etapas'] = $etapas_arr;

		// CARREGAR CHECKLISTS JÁ ASSOCIADOS AO CHAMADO
		$checklists_associados = $this->chamados_checklists_model->get_com_etapas($id);
		$this->data['checklists_associados'] = $checklists_associados;

		// CARREGAR USUÁRIOS PARA O SELECT DE RESPONSÁVEL
		$usuarios = $this->chamados_model->get_usuarios()->result();
		$usuarios_options = array("" => "Selecione um responsável");
		foreach ($usuarios as $usr) {
			$usuarios_options[$usr->id] = $usr->nome;
		}
		$this->data['usuarios_options'] = $usuarios_options;

		if ($this->ion_auth->has_permissao("chamados", "edit"))
			$this->data['acesso_edit'] = true;
		if ($this->ion_auth->has_permissao("chamados", "escalar"))
			$this->data['acesso_escalar'] = true;
		$this->data['acesso_planejar'] = $this->pode_planejar_equipe();
		$this->data['data_planejada'] = null;
		if ($this->data['acesso_planejar']) {
			$this->load->model('kanban_dashboard_model');
			$this->data['data_planejada'] = $this->kanban_dashboard_model->get_planned_date((int) $this->session->userdata('empresa_id'), $id);
		}
		if ($this->ion_auth->has_permissao("chamados", "status"))
			$this->data['acesso_status'] = true;
		if ($this->ion_auth->has_permissao("chamados", "imprimir"))
			$this->data['acesso_imprimir'] = true;
		if ($this->ion_auth->has_permissao("chamados", "reagendar"))
			$this->data['acesso_reagendar'] = true;
		if ($this->ion_auth->has_permissao("chamados", "apontamento_manual_criar"))
			$this->data['acesso_apontar_manual'] = true;
		if ($this->ion_auth->has_permissao("chamados", "apontamento_automatico_criar"))
			$this->data['acesso_apontar_automatico'] = true;
		if ($this->ion_auth->has_permissao("chamados", "associar_checklist"))
			$this->data['acesso_associar_checklist'] = true;

		if ($this->pode_marcar_interno()) {
			$this->data['acesso_interno'] = true;
		}

		$grupos_usuarios_chamado = $this->chamados_model->get_groups();
		if (! @$grupos_usuarios_chamado) {
			$this->session->set_flashdata('message', show_alert_error("Você deve parametrizar o grupo de usuários responsáveis pelo chamado antes."));
			redirect('chamados', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_chamados_details'), 'chamados/details');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Variables */
		$chamado = $this->chamados_model->get(array(
			'select' => 'c.id, c.categoria_id, c.catalogoservico_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id, p.nome as produto_desc, m.nome as modulo_desc, mc.nome as mcategoria_desc, ud.first_name as first_name_dono, ud.last_name as last_name_dono, r.id as chamado_referencia_id, r.titulo as chamado_referencia_titulo, c.horas_prevista, CONCAT(at.first_name, " ", at.last_name) as atualizado_por_nome, n.descricao as nivel_desc, c.nivel_id, c.desenvolvedor_id, dev.first_name as first_name_dev, dev.last_name as last_name_dev',
			'join' => array(array('produtos p', 'p.id = c.produto_id', 'left'), array('modulos m', 'm.id = c.modulo_id', 'left'), array('modulos_categoria mc', 'mc.id = c.categoria_id', 'left'), array('users u', 'u.id = c.responsavel_id', 'left'), array('users ud', 'ud.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner'), array('chamado r', 'r.id = c.referencia_id', 'left'), array('users at', 'at.id = c.atualizado_por', 'left'), array('nivel n', 'n.id = c.nivel_id', 'left'), array('users dev', 'dev.id = c.desenvolvedor_id', 'left')),
			'where' => 'c.id = "' . $id . '"',
			'order_by' => array('c.id', 'desc')
		))->row();

		if ($chamado->empresa_id != $this->session->userdata('empresa_id')) {
			redirect('chamados', 'refresh');
		}

		$this->page_title->clear();

		if ($chamado->referencia_id == "")
			$this->page_title->push(lang('chamados_details') . ": " . $chamado->id);
		else
			$this->page_title->push(lang('chamados_details') . ": " . $chamado->id . " <small>(Referência: <a target='_blank' href='" . base_url("chamados/details/" . $chamado->chamado_referencia_id) . "'>Chamado " . $chamado->chamado_referencia_id . "</a>)</small>");

		$this->data['pagetitle'] = $this->page_title->show();

		$this->data['chamado']	= $chamado;

		$this->data['show_avaliacao'] = false;
		$this->data['has_avaliacao'] = false;
		$this->data['has_prioridade'] = false;

		$where = "";
		$where .= "a.user_id IN (" . $this->session->userdata('user_id') . ")";
		// $this->data['apontamentos'] = $this->chamados_model->get_apontamentos($chamado->id, "a.user_id = ".$this->session->userdata('user_id'));
		$this->data['apontamentos'] = $this->chamados_model->get_apontamentos($chamado->id);

		if ($this->ion_auth->has_permissao("chamados", "alterar_prioridade"))
			$this->data['has_prioridade'] = true;

		if ($chamado->status_id == 5 || ($chamado->status_id == 3 && $chamado->dono_id == $this->session->userdata('user_id')))
			$this->data['show_avaliacao'] = true;

		if ($chamado->dono_id == $this->session->userdata('user_id') && $this->ion_auth->has_permissao("chamados", "avaliar_chamado") && $chamado->status_id == 3) {
			$data_chamado = new DateTime($chamado->data_finalizacao);
			$data_atual = new DateTime(date("Y-m-d H:i:s"));

			$dateInterval = $data_chamado->diff($data_atual);

			if ($dateInterval->days <= 30) {
				$this->data['has_avaliacao'] = true;
			}
		}
		if ($this->ion_auth->has_permissao("chamados", "desavaliar_chamado")) {
			$this->data['has_desavaliacao'] = true;
		} else {
			$this->data['has_desavaliacao'] = false;
		}

		$avaliacoes = $this->chamados_model->get_avaliacoes()->result_array();
		$i = 0;
		foreach ($avaliacoes as $aval) {
			$this->data['avaliacoes'][$i]['id'] = $aval['id'];
			$this->data['avaliacoes'][$i]['descricao'] = $aval['descricao'];
			$this->data['avaliacoes'][$i]['resposta'] = @$this->chamados_model->get_resposta(array('pergunta' => $aval['id'], 'chamado' => $chamado->id))->row()->resposta;
			$i++;
		}

		$i = 0;
		$dialogos = array();
		foreach ($this->chamados_model->get_dialogos($chamado->id)->result() as $value) {
			$dialogos[$i]['id'] = $value->id;
			$dialogos[$i]['chamado_id'] = $value->chamado_id;
			$dialogos[$i]['responsavel_id'] = $value->responsavel_id;
			$dialogos[$i]['descricao'] = $value->descricao;
			$dialogos[$i]['created'] = $value->data_envio;
			$dialogos[$i]['data_leitura'] = $value->data_leitura;
			$dialogos[$i]['first_name'] = $value->first_name;
			$dialogos[$i]['last_name'] = $value->last_name;
			$dialogos[$i]['interno'] = $value->interno;
			$i++;
		}
		$anexos = $this->chamados_model->get_anexos($chamado->id)->result_array();

		$timeline = array_merge($dialogos, $anexos);

		usort($timeline, function ($a, $b) {
			return strtotime($b['created']) - strtotime($a['created']);
		});

		$this->data['timeline'] = $timeline;

		if ($chamado->status_id == 8)
			$status_n_obrigatorios = $this->chamados_model->get_status_n_obrigatorios(true)->result();
		else
			$status_n_obrigatorios = $this->chamados_model->get_status_n_obrigatorios()->result();

		foreach ($status_n_obrigatorios as $stat) {
			$status_options[$stat->id] = $stat->descricao;
		}
		$this->data['status'] = array(
			'name'  => 'status',
			'id'    => 'status',
			'class' => 'form-control',
			'options' => $status_options,
			'selected' => $this->form_validation->set_value('status', $chamado->status_id),
		);

		foreach ($this->chamados_model->get_prioridades()->result() as $prior) {
			$prioridade_options[$prior->id] = $prior->descricao;
		}
		$this->data['prioridade'] = array(
			'name'  => 'prioridade',
			'id'    => 'prioridade',
			'class' => 'form-control',
			'options' => $prioridade_options,
			'onchange' => 'atualizar_prioridade(' . $chamado->id . ', this.value)',
			'selected' => $this->form_validation->set_value('prioridade', $chamado->prioridade_id),
		);

		$grupos_responsavel_chamado_options = array("" => "Selecione");
		foreach ($grupos_usuarios_chamado->result() as $usr) {
			$grupos_responsavel_chamado_options[$usr->id] = $usr->first_name . " " . $usr->last_name;
		}
		$this->data['responsavel'] = array(
			'name'  => 'responsavel',
			'id'    => 'responsavel',
			'class' => 'singleInputDynamic form-control',
			'options' => $grupos_responsavel_chamado_options,
			'selected' => $this->form_validation->set_value('responsavel')
		);

		$grupos_usuarios_chamado_dev = $this->chamados_model->get_groups_dev();
		$grupos_dev_chamado_options = array("" => "Selecione");
		foreach ($grupos_usuarios_chamado_dev->result() as $usr) {
			$grupos_dev_chamado_options[$usr->id] = $usr->first_name . " " . $usr->last_name;
		}
		$this->data['desenvolvedor'] = array(
			'name'  => 'desenvolvedor',
			'id'    => 'desenvolvedor',
			'class' => 'singleInputDynamic form-control',
			'options' => $grupos_dev_chamado_options,
			'selected' => $this->form_validation->set_value('desenvolvedor')
		);

		$produtos_habilitados = $this->chamados_model->get_produtos_habilitados($chamado->unidade_id);
		$produto_options = array("" => "Selecione");
		if ($produtos_habilitados->num_rows() > 0):
			foreach ($produtos_habilitados->result() as $value) {
				$produto_options[$value->id] = $value->nome;
			}
		endif;

		$modulos_habilitados = $this->chamados_model->get_modulos_habilitados($chamado->unidade_id, $chamado->produto_id);
		$modulo_options = array("" => "Selecione");
		if ($modulos_habilitados->num_rows() > 0):
			foreach ($modulos_habilitados->result() as $value) {
				$modulo_options[$value->id] = $value->nome;
			}
		endif;

		$modulo_categorias = $this->chamados_model->get_modulo_categorias($chamado->modulo_id);
		$modulo_categoria_options = array("" => "Selecione");
		if ($modulo_categorias->num_rows() > 0):
			foreach ($modulo_categorias->result() as $value) {
				$modulo_categoria_options[$value->id] = $value->nome;
			}
		endif;

		$tipo_servicos_options = array("" => "Selecione");
		if ($chamado->categoria_id != "") {
			$tipo_servicos = $this->chamados_model->get_catalogos_servico_ativos($chamado->categoria_id);
			if ($tipo_servicos->num_rows() > 0):
				foreach ($tipo_servicos->result() as $serv) {
					$tipo_servicos_options[$serv->id] = $serv->descricao;
				}
			endif;
		}

		$niveis_options = array("" => "Selecione");
		$niveis = $this->chamados_model->get_niveis_ativos();
		if ($niveis->num_rows() > 0):
			foreach ($niveis->result() as $n) {
				$niveis_options[$n->id] = $n->descricao;
			}
		endif;

		$this->data['produto'] = array(
			'name'  => 'produto',
			'id'    => 'produto_modulo_categoria_edit',
			'class' => 'form-control',
			'options' => $produto_options,
			'selected' => $this->form_validation->set_value('produto', $chamado->produto_id)
		);

		$this->data['modulo'] = array(
			'name'  => 'modulo',
			'id'    => 'modulo_edit',
			'class' => 'form-control',
			'options' => $modulo_options,
			'selected' => $this->form_validation->set_value('modulo', $chamado->modulo_id)
		);

		$this->data['categoria'] = array(
			'name'  => 'categoria',
			'id'    => 'categoria_edit',
			'class' => 'form-control',
			'options' => $modulo_categoria_options,
			'selected' => $this->form_validation->set_value('categoria', $chamado->categoria_id)
		);

		$this->data['tipo_servico'] = array(
			'name'  => 'tipo_servico',
			'id'    => 'tipo_servico',
			'class' => 'form-control',
			'options' => $tipo_servicos_options,
			'onchange' => "calculaPrazoChamado('" . base_url() . "')",
			'selected' => $this->form_validation->set_value('tipo_servico', $chamado->catalogoservico_id)
		);

		$this->data['nivel'] = array(
			'name'  => 'nivel',
			'id'    => 'nivel',
			'class' => 'form-control',
			'options' => $niveis_options,
			'selected' => $this->form_validation->set_value('nivel', $chamado->nivel_id)
		);

		$this->data['apontamento_ativo'] = $this->chamados_model->get_apontamento_ativo($id);

		// Para o popup de apontamento: horas que o usuário já apontou hoje (todos os chamados) e a meta diária dele.
		$horas_hoje = $this->db->select('COALESCE(SUM(TIME_TO_SEC(esforco)), 0) AS segundos', false)
			->where('user_id', (int) $this->session->userdata('user_id'))
			->where('data', date('Y-m-d'))
			->get('chamado_apontamento')
			->row();
		$this->data['apontado_hoje_segundos'] = $horas_hoje ? (int) $horas_hoje->segundos : 0;
		$this->data['meta_diaria_horas'] = null;
		if ($this->db->table_exists('kanban_dashboard_meta')) {
			$this->load->model('kanban_dashboard_model');
			$this->data['meta_diaria_horas'] = $this->kanban_dashboard_model->get_goal(
				(int) $this->session->userdata('empresa_id'),
				(int) $this->session->userdata('user_id')
			);
		}

		$chamados_vinculados = $this->chamados_model->get(array(
			'select' => 'c.id, c.titulo, cst.descricao as status',
			'join' => array(array('chamado_status cst', 'cst.id = c.status_id', 'inner')),
			'where' => 'c.referencia_id = "' . $id . '"',
			'order_by' => array('c.id', 'desc')
		));

		$this->data['has_chamados_vinculados'] = false;
		if ($chamados_vinculados->num_rows() > 0) {
			$this->data['has_chamados_vinculados'] = true;
			$this->data['chamados_vinculados'] = $chamados_vinculados;
		}

		$unidades_permitidas_usuario = $this->chamados_model->get_unidades_permitidas($this->session->userdata('user_id'), $this->session->userdata('empresa_id'))->result();
		$unidade_in_where = array(0);
		$unidade_options = array("" => "Selecione");
		foreach ($unidades_permitidas_usuario as $unid) {
			$unidade_in_where[] = $unid->unidade_id;
			$unidade_options[$unid->unidade_id] = $unid->unidade . " - " . $unid->razao_social;
		}

		$this->data['unidade'] = array(
			'name'  => 'unidade',
			'id'    => 'unidade',
			'class' => 'singleInputDynamic form-control',
			'options' => $unidade_options,
			'selected' => $this->form_validation->set_value('unidade', $chamado->unidade_id)
		);

		if ($chamado->horas_prevista != "" && $chamado->horas_prevista != null) {
			if ($this->input->post('horas_prevista'))
				$this->data['horas_prevista'] = str_replace(",", ".", $this->input->post('horas_prevista'));
			else
				$this->data['horas_prevista'] = $chamado->horas_prevista;
		} else {
			if ($this->input->post('horas_prevista'))
				$this->data['horas_prevista'] = str_replace(",", ".", $this->input->post('horas_prevista'));
			else
				$this->data['horas_prevista'] = "";
		}

		$times = array();
		if ($this->data['apontamentos']->num_rows()) {
			foreach ($this->data['apontamentos']->result() as $key => $value) {
				$times[] = $value->esforco;
			}
		}
		$this->data['esforco'] = AddPlayTime($times);

		/* Load Template */
		$this->template->admin_render('admin/chamados/details', $this->data);
	}

	public function dialogo($id)
	{
		if (! $this->ion_auth->logged_in() or ! $id or empty($id) or ! $this->ion_auth->has_permissao("chamados", "dialogo")) {
			redirect('auth', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_chamados_details'), 'chamados/details');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Variables */
		$chamado = $this->chamados_model->get(array(
			'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id',
			'join' => array(array('users u', 'u.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner')),
			'where' => 'c.id = "' . $id . '"',
			'order_by' => array('c.id', 'desc')
		))->row();

		if ($chamado->empresa_id != $this->session->userdata('empresa_id')) {
			redirect('chamados', 'refresh');
		}

		$this->page_title->clear();
		$this->page_title->push(lang('chamados_details') . ": " . $chamado->id);
		$this->data['pagetitle'] = $this->page_title->show();

		/* Validate form input */
		$this->form_validation->set_rules('dialogo', 'lang:create_validation_dialogo_label', 'trim|required');

		if (isset($_POST) && ! empty($_POST)) {
			if ($this->form_validation->run() == TRUE) {
				$dados["chamado_id"] = $id;
				$dados["responsavel_id"] = $this->session->userdata('user_id');
				$dados["descricao"] = $this->input->post('dialogo');
				$dados["data_envio"] = date("Y-m-d H:i:s");
				$dados["interno"] = (@$_POST['interno']) ? $_POST['interno'] : 0;

				$dialogo_create = $this->chamados_model->create_dialogo($dados);

				if ($dialogo_create) {
					// SEMPRE PREPARA O EMAIL, MAS FILTRA DESTINATÁRIOS SE FOR INTERNO
					$eh_interno = (@$_POST['interno']) ? $_POST['interno'] : 0;

					$i = 0;
					$dialogos = array();
					foreach ($this->chamados_model->get_dialogos($id)->result() as $value) {
						$dialogos[$i]['id'] = $value->id;
						$dialogos[$i]['chamado_id'] = $value->chamado_id;
						$dialogos[$i]['responsavel_id'] = $value->responsavel_id;
						$dialogos[$i]['descricao'] = $value->descricao;
						$dialogos[$i]['created'] = $value->data_envio;
						$dialogos[$i]['data_leitura'] = $value->data_leitura;
						$dialogos[$i]['first_name'] = $value->first_name;
						$dialogos[$i]['last_name'] = $value->last_name;
						$i++;
					}
					$anexos = $this->chamados_model->get_anexos($id)->result_array();

					$timeline = array_merge($dialogos, $anexos);

					usort($timeline, function ($a, $b) {
						return strtotime($b['created']) - strtotime($a['created']);
					});

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

					$autor_dialogo = trim($this->chamados_model->get_user_name($this->session->userdata('user_id')));
					list($padrao_email_ass, $padrao_email_msg) = $this->aplicar_email_chamado(6, $id, array(
						'{dialogo_autor}' => html_escape($autor_dialogo),
						'{dialogo_data}' => date('d/m/Y') . ' &agrave;s ' . date('H:i'),
						'{dialogo_texto}' => nl2br(html_escape($this->input->post('dialogo'))),
						'{dialogo_interno}' => $eh_interno ? ' <span style="display:inline-block;padding:1px 6px;border-radius:3px;background:#f39c12;color:#ffffff;font-size:11px;">Interno</span>' : '',
					), (bool) $eh_interno, 1);

					$mail = $this->phpmailer_library->load();
					$mail->isSMTP();
					$mail->Host     = $email_config['smtp_host'];
					$mail->SMTPAuth = true;
					$mail->Username = $email_config['smtp_user'];
					$mail->Password = $email_config['smtp_pass'];
					$mail->SMTPSecure = 'tsl';
					$mail->Port     = $email_config['smtp_port'];

					$mail->setFrom($from_email, utf8_decode($from_name));

					$chamado_email = $this->chamados_model->get(array(
						'select' => 'c.id, c.dono_id, u.email as email_dono, c.responsavel_id, c.interessados, u.first_name, u.last_name, us.email as email_resp, c.plano_acao, udev.email as email_dev',
						'join' => array(array('users u', 'u.id = c.dono_id', 'left'), array('users us', 'us.id = c.responsavel_id', 'left'), array('users udev', 'udev.id = c.desenvolvedor_id', 'left')),
						'where' => 'c.id = "' . $id . '"',
						'order_by' => array('c.id', 'desc')
					))->row();

					// GRUPOS QUE NÃO RECEBEM EMAIL QUANDO É INTERNO
					$grupos_excluidos = array(2, 11, 35, 58, 68);

					// ADICIONAR DESTINATÁRIOS
					if (is_array(unserialize($chamado_email->interessados))) {
						foreach (unserialize($chamado_email->interessados) as $int) {
							// Se for interno, não adiciona usuários dos grupos excluídos
							if ($eh_interno) {
								$adicionar = true;
								foreach ($grupos_excluidos as $grupo_id) {
									if ($this->ion_auth->in_group($grupo_id, $int)) {
										$adicionar = false;
										break;
									}
								}
								if ($adicionar) {
									$mail->addAddress($this->chamados_model->get_email($int));
								}
							} else {
								// Se não for interno, adiciona todos
								$mail->addAddress($this->chamados_model->get_email($int));
							}
						}
					}

					// DONO
					if ($eh_interno) {
						$adicionar_dono = true;
						foreach ($grupos_excluidos as $grupo_id) {
							if ($this->ion_auth->in_group($grupo_id, $chamado_email->dono_id)) {
								$adicionar_dono = false;
								break;
							}
						}
						if ($adicionar_dono) {
							$mail->addAddress($chamado_email->email_dono);
						}
					} else {
						$mail->addAddress($chamado_email->email_dono);
					}

					// RESPONSÁVEL
					if ($eh_interno) {
						$adicionar_responsavel = true;
						foreach ($grupos_excluidos as $grupo_id) {
							if ($this->ion_auth->in_group($grupo_id, $chamado_email->responsavel_id)) {
								$adicionar_responsavel = false;
								break;
							}
						}
						if ($adicionar_responsavel) {
							$mail->addAddress($chamado_email->email_resp);
						}
					} else {
						$mail->addAddress($chamado_email->email_resp);
					}

					// DESENVOLVEDOR
					if ($chamado_email->desenvolvedor_id) {
						if ($eh_interno) {
							$adicionar_dev = true;
							foreach ($grupos_excluidos as $grupo_id) {
								if ($this->ion_auth->in_group($grupo_id, $chamado_email->desenvolvedor_id)) {
									$adicionar_dev = false;
									break;
								}
							}
							if ($adicionar_dev) {
								$mail->addAddress($chamado_email->email_dev);
							}
						} else {
							$mail->addAddress($chamado_email->email_dev);
						}
					}

					$mail->Subject = utf8_decode($padrao_email_ass);
					$mail->isHTML(true);
					$mail->Body = utf8_decode($padrao_email_msg);

					// Só envia se houver destinatários
					if (count($mail->getToAddresses()) > 0) {
						$mail->send();
					}

					$this->data['message'] = show_alert_success($this->chamados_model->messages());
				} else {
					$this->data['message'] = show_alert_error($this->chamados_model->errors());
				}
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->chamados_model->errors() ? show_alert_error($this->chamados_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		redirect('chamados/details/' . $id, 'refresh');
	}

	public function anexo($id)
	{
		if (! $this->ion_auth->logged_in() or ! $id or empty($id) or ! $this->ion_auth->has_permissao("chamados", "anexo")) {
			redirect('auth', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_chamados_details'), 'chamados/details');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Variables */
		$chamado = $this->chamados_model->get(array(
			'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id',
			'join' => array(array('users u', 'u.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner')),
			'where' => 'c.id = "' . $id . '"',
			'order_by' => array('c.id', 'desc')
		))->row();

		if ($chamado->empresa_id != $this->session->userdata('empresa_id')) {
			redirect('chamados', 'refresh');
		}

		$this->page_title->clear();
		$this->page_title->push(lang('chamados_details') . ": " . $chamado->id);
		$this->data['pagetitle'] = $this->page_title->show();

		if (isset($_FILES) && ! empty($_FILES)) {
			$this->load->library('upload', get_upload_config());

			//exit("<pre>".print_r($_FILES,true)."</pre>");

			$images = array();
			$count_upload = 0;
			$upload_msgs = "";
			foreach ($_FILES['anexo']['name'] as $key => $anexo) {
				$_FILES['images[]']['name'] = removerAcentos($_FILES['anexo']['name'][$key]);
				$_FILES['images[]']['type'] = $_FILES['anexo']['type'][$key];
				$_FILES['images[]']['tmp_name'] = $_FILES['anexo']['tmp_name'][$key];
				$_FILES['images[]']['error'] = $_FILES['anexo']['error'][$key];
				$_FILES['images[]']['size'] = $_FILES['anexo']['size'][$key];

				if ($this->upload->do_upload('images[]')) {
					$dados["chamado_id"] = $id;
					$dados["responsavel_id"] = $this->session->userdata('user_id');
					$dados["descricao"] = $this->input->post('anexo_desc');
					$dados["anexo"] = $this->upload->data('file_name');
					$dados["created"] = date("Y-m-d H:i:s");

					$anexo_create = $this->chamados_model->create_anexo($dados);

					$count_upload++;
					if ($upload_msgs != "")
						$upload_msgs .= ", ";
					$upload_msgs .= $this->upload->data('file_name');
				} else {
					$this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
				}
			}

			if ($count_upload > 0) {
				$i = 0;
				$dialogos = array();
				foreach ($this->chamados_model->get_dialogos($id)->result() as $value) {
					$dialogos[$i]['id'] = $value->id;
					$dialogos[$i]['chamado_id'] = $value->chamado_id;
					$dialogos[$i]['responsavel_id'] = $value->responsavel_id;
					$dialogos[$i]['descricao'] = $value->descricao;
					$dialogos[$i]['created'] = $value->data_envio;
					$dialogos[$i]['data_leitura'] = $value->data_leitura;
					$dialogos[$i]['first_name'] = $value->first_name;
					$dialogos[$i]['last_name'] = $value->last_name;
					$i++;
				}
				$anexos = $this->chamados_model->get_anexos($id)->result_array();

				$timeline = array_merge($dialogos, $anexos);

				usort($timeline, function ($a, $b) {
					return strtotime($b['created']) - strtotime($a['created']);
				});

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

				$arquivos_email = '';
				foreach (array_filter(array_map('trim', explode(',', $upload_msgs))) as $arquivo) {
					$arquivos_email .= '<div style="margin-top:4px;font-size:14px;">&#128206; <a href="' . base_url('upload/anexo/' . rawurlencode($arquivo)) . '" style="color:#3c8dbc;text-decoration:none;font-weight:bold;">' . html_escape($arquivo) . '</a></div>';
				}
				list($padrao_email_ass, $padrao_email_msg) = $this->aplicar_email_chamado(7, $id, array(
					'{anexo_autor}' => html_escape(trim($this->chamados_model->get_user_name($this->session->userdata('user_id')))),
					'{anexo_data}' => date('d/m/Y') . ' &agrave;s ' . date('H:i'),
					'{anexo_arquivos}' => $arquivos_email,
					'{anexo_descricao}' => trim((string) $this->input->post('anexo_desc')) !== '' ? '<div style="margin-top:8px;font-size:14px;line-height:1.5;color:#444444;">' . nl2br(html_escape($this->input->post('anexo_desc'))) . '</div>' : '',
				), false, $count_upload);

				$this->email->clear();
				$this->email->from($from_email, utf8_decode($from_name));

				$chamado = $this->chamados_model->get(array(
					'select' => 'c.id, c.dono_id, u.email as email_dono, c.responsavel_id, c.interessados, u.first_name, u.last_name, us.email as email_resp, c.plano_acao, udev.email as email_dev',
					'join' => array(array('users u', 'u.id = c.dono_id', 'left'), array('users us', 'us.id = c.responsavel_id', 'left'), array('users udev', 'udev.id = c.desenvolvedor_id', 'left')),
					'where' => 'c.id = "' . $id . '"',
					'order_by' => array('c.id', 'desc')
				))->row();

				/*$emails_to = array();
				if(is_array(unserialize($chamado->interessados))) {
					foreach (unserialize($chamado->interessados) as $int) {
						$emails_to[] = $this->chamados_model->get_email($int);
					}
				}
				$emails_to[] = $chamado->email_dono;
				$emails_to[] = $chamado->email_resp;

				$this->email->to(array_unique($emails_to));
	            $this->email->subject(utf8_decode($padrao_email_ass));
	            $this->email->message(utf8_decode($padrao_email_msg));
				$this->email->send();*/

				$mail = $this->phpmailer_library->load();
				$mail->isSMTP();
				$mail->Host     = $email_config['smtp_host'];
				$mail->SMTPAuth = true;
				$mail->Username = $email_config['smtp_user'];
				$mail->Password = $email_config['smtp_pass'];
				$mail->SMTPSecure = 'tsl';
				$mail->Port     = $email_config['smtp_port'];

				$mail->setFrom($from_email, utf8_decode($from_name));

				if (is_array(unserialize($chamado->interessados))) {
					foreach (unserialize($chamado->interessados) as $int) {
						$mail->addAddress($this->chamados_model->get_email($int));
					}
				}
				$mail->addAddress($chamado->email_dono);
				$mail->addAddress($chamado->email_resp);
				$mail->addAddress($chamado->email_dev);

				$mail->Subject = utf8_decode($padrao_email_ass);
				$mail->isHTML(true);

				$mail->Body = utf8_decode($padrao_email_msg);
				$mail->send();

				$this->session->set_flashdata('message', show_alert_success("Anexo " . $upload_msgs . " adicionado com sucesso."));
			}
		}

		redirect('chamados/details/' . $id, 'refresh');
	}

	public function teste_ajax()
	{
		log_message('error', '=== TESTE AJAX RECEBIDO ===');
		log_message('error', 'POST: ' . json_encode($_POST));
		log_message('error', 'is_ajax: ' . ($this->input->is_ajax_request() ? 'SIM' : 'NÃO'));

		echo json_encode(array(
			'success' => true,
			'message' => 'TESTE OK',
			'post_data' => $_POST,
			'is_ajax' => $this->input->is_ajax_request()
		));
	}

	public function associar_checklist()
	{
		// Garantir que é uma requisição AJAX
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		// Forçar header JSON
		header('Content-Type: application/json');

		try {
			if (! $this->ion_auth->logged_in()) {
				throw new Exception('Usuário não autenticado');
			}

			if (! $this->ion_auth->has_permissao("chamados", "associar_checklist")) {
				throw new Exception('Sem permissão');
			}

			$chamado_id = $this->input->post('chamado_id');
			$checklists = $this->input->post('checklists');
			$etapas = $this->input->post('etapas');
			$datas_inicio_checklists = $this->input->post('data_inicio_checklist');

			// Capturar etapas personalizadas
			$etapas_personalizadas_selecionadas = $this->input->post('etapas_personalizadas_selecionadas');
			$etapas_personalizadas_dados = $this->input->post('etapas_personalizadas_dados');

			if (!$chamado_id) {
				throw new Exception('ID do chamado não fornecido');
			}

			// Carrega modelos
			$this->load->model('chamados_checklists_model');
			$this->load->model('chamados_checklists_etapas_model');
			$this->load->model('checklists_etapas_model');
			$this->load->model('checklists_model');

			// Verifica se algum checklist selecionado já está associado
			$checklists_ja_associados = array();
			$checklists_nomes = array();

			if ($checklists && is_array($checklists)) {
				foreach ($checklists as $checklist_id) {
					$existente = $this->chamados_checklists_model->get_by_original($chamado_id, $checklist_id);
					if ($existente) {
						$checklist_original = $this->checklists_model->get($checklist_id);
						if ($checklist_original) {
							$checklists_ja_associados[] = $checklist_original->nome . ' (#' . $checklist_id . ')';
						} else {
							$checklists_ja_associados[] = 'Checklist #' . $checklist_id;
						}
					} else {
						$checklist_original = $this->checklists_model->get($checklist_id);
						if ($checklist_original) {
							$checklists_nomes[] = $checklist_original->nome;
						}
					}
				}
			}

			if (!empty($checklists_ja_associados)) {
				echo json_encode(array(
					'success' => false,
					'error' => 'Os seguintes checklists já estão associados a este chamado: ' . implode(', ', $checklists_ja_associados)
				));
				return;
			}

			$inserted_count = 0;
			$etapas_inseridas = 0;
			$checklists_criados_nomes = array();

			$etapas_para_inserir = array();
			$checklists_processados = array();
			$datas_checklists = array();

			// Processa checklists selecionados
			if ($checklists && is_array($checklists)) {

				foreach ($checklists as $checklist_id) {
					$checklists_processados[] = $checklist_id;

					$data_inicio = isset($datas_inicio_checklists[$checklist_id]) && !empty($datas_inicio_checklists[$checklist_id])
						? $datas_inicio_checklists[$checklist_id]
						: date('Y-m-d');

					$datas_checklists[$checklist_id] = $data_inicio;

					$checklist_pai = $this->chamados_checklists_model->get_by_original($chamado_id, $checklist_id);

					if (!$checklist_pai) {
						$checklist_original = $this->checklists_model->get($checklist_id);
						if ($checklist_original) {
							$ch_data = array(
								'chamado_id' => $chamado_id,
								'checklist_original_id' => $checklist_id,
								'nome' => $checklist_original->nome,
								'descricao' => isset($checklist_original->descricao) ? $checklist_original->descricao : '',
								'data_inicio' => $data_inicio,
								'status' => 1,
								'data_criacao' => date('Y-m-d H:i:s')
							);

							$ch_checklist_id = $this->chamados_checklists_model->insert($ch_data);
							if ($ch_checklist_id) {
								$inserted_count++;
								$checklists_criados_nomes[] = $checklist_original->nome;
							}
						}
					}
				}
			}

			// Processa checklists que têm etapas selecionadas individualmente
			if ($etapas && is_array($etapas)) {
				foreach ($etapas as $checklist_id => $etapas_ids) {
					if (!is_array($etapas_ids) || empty($etapas_ids)) {
						continue;
					}

					if (in_array($checklist_id, $checklists_processados)) {
						$data_inicio = isset($datas_inicio_checklists[$checklist_id]) && !empty($datas_inicio_checklists[$checklist_id])
							? $datas_inicio_checklists[$checklist_id]
							: date('Y-m-d');
						$datas_checklists[$checklist_id] = $data_inicio;
						continue;
					}

					$data_inicio = isset($datas_inicio_checklists[$checklist_id]) && !empty($datas_inicio_checklists[$checklist_id])
						? $datas_inicio_checklists[$checklist_id]
						: date('Y-m-d');

					$datas_checklists[$checklist_id] = $data_inicio;

					$checklist_pai = $this->chamados_checklists_model->get_by_original($chamado_id, $checklist_id);

					if (!$checklist_pai) {
						$checklist_original = $this->checklists_model->get($checklist_id);
						if ($checklist_original) {
							$ch_data = array(
								'chamado_id' => $chamado_id,
								'checklist_original_id' => $checklist_id,
								'nome' => $checklist_original->nome,
								'descricao' => isset($checklist_original->descricao) ? $checklist_original->descricao : '',
								'data_inicio' => $data_inicio,
								'status' => 1,
								'data_criacao' => date('Y-m-d H:i:s')
							);

							$ch_checklist_id = $this->chamados_checklists_model->insert($ch_data);
							if ($ch_checklist_id) {
								$inserted_count++;
								$checklists_criados_nomes[] = $checklist_original->nome;
							}
						}
					}

					$checklists_processados[] = $checklist_id;
				}
			}

			// Processa checklists que têm etapas personalizadas
			if ($etapas_personalizadas_selecionadas && is_array($etapas_personalizadas_selecionadas)) {
				foreach ($etapas_personalizadas_selecionadas as $checklist_id => $etapas_ids) {
					if (!is_array($etapas_ids) || empty($etapas_ids)) {
						continue;
					}

					if (in_array($checklist_id, $checklists_processados)) {
						$data_inicio = isset($datas_inicio_checklists[$checklist_id]) && !empty($datas_inicio_checklists[$checklist_id])
							? $datas_inicio_checklists[$checklist_id]
							: date('Y-m-d');

						if (!isset($datas_checklists[$checklist_id])) {
							$datas_checklists[$checklist_id] = $data_inicio;
						}
						continue;
					}

					$data_inicio = isset($datas_inicio_checklists[$checklist_id]) && !empty($datas_inicio_checklists[$checklist_id])
						? $datas_inicio_checklists[$checklist_id]
						: date('Y-m-d');

					$datas_checklists[$checklist_id] = $data_inicio;

					$checklist_pai = $this->chamados_checklists_model->get_by_original($chamado_id, $checklist_id);

					if (!$checklist_pai) {
						$checklist_original = $this->checklists_model->get($checklist_id);
						if ($checklist_original) {
							$ch_data = array(
								'chamado_id' => $chamado_id,
								'checklist_original_id' => $checklist_id,
								'nome' => $checklist_original->nome,
								'descricao' => isset($checklist_original->descricao) ? $checklist_original->descricao : '',
								'data_inicio' => $data_inicio,
								'status' => 1,
								'data_criacao' => date('Y-m-d H:i:s')
							);

							$ch_checklist_id = $this->chamados_checklists_model->insert($ch_data);
							if ($ch_checklist_id) {
								$inserted_count++;
								$checklists_criados_nomes[] = $checklist_original->nome;
							}
						}
					}

					$checklists_processados[] = $checklist_id;
				}
			}

			// Coleta todas as etapas que serão inseridas
			if ($etapas && is_array($etapas)) {
				foreach ($etapas as $checklist_id => $etapas_ids) {
					if (!is_array($etapas_ids)) {
						continue;
					}

					foreach ($etapas_ids as $etapa_id) {
						$etapa_original = $this->checklists_etapas_model->get($etapa_id);
						if ($etapa_original) {
							$etapas_para_inserir[] = array(
								'checklist_id' => $checklist_id,
								'etapa_original' => $etapa_original,
								'tipo' => 'etapa_original'
							);
						}
					}
				}
			}

			if ($etapas_personalizadas_selecionadas && is_array($etapas_personalizadas_selecionadas)) {
				foreach ($etapas_personalizadas_selecionadas as $checklist_id => $etapas_ids) {
					if (!is_array($etapas_ids)) {
						continue;
					}

					foreach ($etapas_ids as $etapa_id_temp) {
						if (isset($etapas_personalizadas_dados[$checklist_id][$etapa_id_temp])) {
							$dados_etapa = $etapas_personalizadas_dados[$checklist_id][$etapa_id_temp];
							$etapas_para_inserir[] = array(
								'checklist_id' => $checklist_id,
								'dados_personalizados' => $dados_etapa,
								'tipo' => 'etapa_personalizada'
							);
						}
					}
				}
			}

			// Agrupa etapas por checklist e ordenar
			$etapas_por_checklist = array();
			foreach ($etapas_para_inserir as $etapa_info) {
				$checklist_id = $etapa_info['checklist_id'];

				if (!isset($etapas_por_checklist[$checklist_id])) {
					$etapas_por_checklist[$checklist_id] = array();
				}

				if ($etapa_info['tipo'] == 'etapa_original') {
					$etapa_info['ordem'] = isset($etapa_info['etapa_original']->ordem) ? $etapa_info['etapa_original']->ordem : 0;
				} else {
					$etapa_info['ordem'] = isset($etapa_info['dados_personalizados']['ordem']) ? $etapa_info['dados_personalizados']['ordem'] : 0;
				}

				$etapas_por_checklist[$checklist_id][] = $etapa_info;
			}

			$etapas_inseridas_detalhes = array();

			// Inserir as etapas em ordem
			foreach ($etapas_por_checklist as $checklist_id => $etapas_checklist) {
				usort($etapas_checklist, function ($a, $b) {
					$ordemA = isset($a['ordem']) ? $a['ordem'] : 0;
					$ordemB = isset($b['ordem']) ? $b['ordem'] : 0;
					return $ordemA - $ordemB;
				});

				$checklist_pai = $this->chamados_checklists_model->get_by_original($chamado_id, $checklist_id);

				if (!$checklist_pai) {
					log_message('error', 'Checklist pai não encontrado para ID: ' . $checklist_id);
					continue;
				}

				$data_inicio_atual = $checklist_pai->data_inicio;
				$ultima_ordem_inserida = -1;

				foreach ($etapas_checklist as $etapa_info) {
					$etapa_existente = false;

					if ($etapa_info['tipo'] == 'etapa_original') {
						$etapa_original = $etapa_info['etapa_original'];

						$this->db->where('chamado_id', $chamado_id);
						$this->db->where('chamado_checklist_id', $checklist_pai->id);
						$this->db->where('etapa_original_id', $etapa_original->id);
						$etapa_existente = $this->db->get('chamados_checklists_etapas')->row();
					}

					if ($etapa_existente) {
						if ($etapa_existente->data_prazo) {
							$proxima_data = new DateTime($etapa_existente->data_prazo);
							$proxima_data->modify('+1 day');
							while ($proxima_data->format('N') >= 6) {
								$proxima_data->modify('+1 day');
							}
							$data_inicio_atual = $proxima_data->format('Y-m-d');
						} else {
							$proxima_data = new DateTime($etapa_existente->data_inicio);
							$proxima_data->modify('+1 day');
							while ($proxima_data->format('N') >= 6) {
								$proxima_data->modify('+1 day');
							}
							$data_inicio_atual = $proxima_data->format('Y-m-d');
						}

						$ultima_ordem_inserida = $etapa_existente->ordem;
						continue;
					}

					$data_inicio_etapa = $data_inicio_atual;
					$data_prazo_etapa = null;
					$prazo_padrao = 0;
					$ordem_atual = 0;
					$nome_etapa = '';
					$descricao_etapa = '';
					$responsavel = null;

					if ($etapa_info['tipo'] == 'etapa_personalizada') {
						$dados_etapa = $etapa_info['dados_personalizados'];
						$prazo_padrao = isset($dados_etapa['prazo_padrao']) ? (int)$dados_etapa['prazo_padrao'] : 0;
						$ordem_atual = isset($dados_etapa['ordem']) ? (int)$dados_etapa['ordem'] : 0;
						$nome_etapa = isset($dados_etapa['nome']) ? $dados_etapa['nome'] : 'Etapa sem nome';
						$descricao_etapa = isset($dados_etapa['descricao']) ? $dados_etapa['descricao'] : '';
						$responsavel = isset($dados_etapa['responsavel']) && !empty($dados_etapa['responsavel']) ? $dados_etapa['responsavel'] : null;

						if ($prazo_padrao > 0) {
							$data_prazo_etapa = $this->calcular_data_com_weekend($data_inicio_etapa, $prazo_padrao);
						}
					} else {
						$etapa_original = $etapa_info['etapa_original'];
						$prazo_padrao = isset($etapa_original->prazo_padrao) ? (int)$etapa_original->prazo_padrao : 0;
						$ordem_atual = isset($etapa_original->ordem) ? (int)$etapa_original->ordem : 0;
						$nome_etapa = $etapa_original->nome;
						$descricao_etapa = isset($etapa_original->descricao) ? $etapa_original->descricao : '';

						if ($prazo_padrao > 0) {
							$data_prazo_etapa = $this->calcular_data_com_weekend($data_inicio_etapa, $prazo_padrao);
						}
					}

					// Prepara dados para inserção
					if ($etapa_info['tipo'] == 'etapa_personalizada') {
						$et_data = array(
							'chamado_id' => $chamado_id,
							'chamado_checklist_id' => $checklist_pai->id,
							'etapa_original_id' => 0,
							'nome' => $nome_etapa,
							'descricao' => $descricao_etapa,
							'responsavel' => $responsavel,
							'ordem' => $ordem_atual,
							'data_inicio' => $data_inicio_etapa,
							'data_prazo' => $data_prazo_etapa,
							'concluida' => 0,
							'status' => 1,
							'data_criacao' => date('Y-m-d H:i:s')
						);
					} else {
						$etapa_original = $etapa_info['etapa_original'];
						$et_data = array(
							'chamado_id' => $chamado_id,
							'chamado_checklist_id' => $checklist_pai->id,
							'etapa_original_id' => $etapa_original->id,
							'nome' => $etapa_original->nome,
							'descricao' => isset($etapa_original->descricao) ? $etapa_original->descricao : '',
							'responsavel' => $responsavel,
							'ordem' => $ordem_atual,
							'data_inicio' => $data_inicio_etapa,
							'data_prazo' => $data_prazo_etapa,
							'concluida' => 0,
							'status' => 1,
							'data_criacao' => date('Y-m-d H:i:s')
						);
					}

					$etapa_inserida_id = $this->chamados_checklists_etapas_model->insert($et_data);
					if ($etapa_inserida_id) {
						$etapas_inseridas++;

						$etapas_inseridas_detalhes[] = array(
							'nome' => $nome_etapa,
							'checklist_nome' => $checklist_pai->nome,
							'data_inicio' => $data_inicio_etapa,
							'data_prazo' => $data_prazo_etapa,
							'tipo' => $etapa_info['tipo'] == 'etapa_personalizada' ? 'personalizada' : 'padrão'
						);
					}

					if ($data_prazo_etapa) {
						$proxima_data = new DateTime($data_prazo_etapa);
						$proxima_data->modify('+1 day');
						while ($proxima_data->format('N') >= 6) {
							$proxima_data->modify('+1 day');
						}
						$data_inicio_atual = $proxima_data->format('Y-m-d');
					} else {
						$proxima_data = new DateTime($data_inicio_etapa);
						$proxima_data->modify('+1 day');
						while ($proxima_data->format('N') >= 6) {
							$proxima_data->modify('+1 day');
						}
						$data_inicio_atual = $proxima_data->format('Y-m-d');
					}

					$ultima_ordem_inserida = $ordem_atual;
				}
			}

			if ($inserted_count > 0 || $etapas_inseridas > 0) {
				$usuario = $this->chamados_model->get_user_name($this->session->userdata('user_id'));
				$mensagem = "Associação de Checklists\n";
				$mensagem .= "Realizado por: {$usuario}\n\n";

				if ($inserted_count > 0) {
					$mensagem .= "Checklists associados ({$inserted_count}):\n";
					foreach ($checklists_criados_nomes as $checklist_nome) {
						$mensagem .= "• {$checklist_nome}\n\n";
					}
				}

				if ($etapas_inseridas > 0) {
					$mensagem .= "Etapas adicionadas ({$etapas_inseridas}):\n";

					$etapas_agrupadas = array();
					foreach ($etapas_inseridas_detalhes as $etapa) {
						$checklist_nome = $etapa['checklist_nome'];
						if (!isset($etapas_agrupadas[$checklist_nome])) {
							$etapas_agrupadas[$checklist_nome] = array();
						}
						$etapas_agrupadas[$checklist_nome][] = $etapa;
					}

					$multiplos_checklists = count($etapas_agrupadas) > 1;

					foreach ($etapas_agrupadas as $checklist_nome => $etapas_lista) {
						if ($multiplos_checklists) {
							$mensagem .= "{$checklist_nome}:\n";
						}
						foreach ($etapas_lista as $etapa) {
							$indent = $multiplos_checklists ? "  " : "";
							$mensagem .= "{$indent}• {$etapa['nome']} ({$etapa['tipo']})";

							if ($etapa['data_inicio']) {
								$mensagem .= "\n{$indent}  Inicio: " . date('d/m/Y', strtotime($etapa['data_inicio']));
							}
							if ($etapa['data_prazo']) {
								$mensagem .= "\n{$indent}  Prazo: " . date('d/m/Y', strtotime($etapa['data_prazo']));
							}
							$mensagem .= "\n\n";
						}
					}
				}

				$dados_dialogo = array(
					'chamado_id'     => $chamado_id,
					'responsavel_id' => $this->session->userdata('user_id'),
					'descricao'      => $mensagem,
					'data_envio'     => date('Y-m-d H:i:s'),
					'interno'        => 0
				);

				$dialogo_id = $this->chamados_model->create_dialogo($dados_dialogo);

				if ($dialogo_id) {
					log_message('error', 'Dialogo de associacao de checklists criado com ID: ' . $dialogo_id);
				} else {
					log_message('error', 'Falha ao criar dialogo de associacao de checklists');
				}
			}

			echo json_encode(array(
				'success' => true,
				'message' => 'Operação realizada com sucesso',
				'inserted_checklists' => $inserted_count,
				'inserted_etapas' => $etapas_inseridas
			));
		} catch (Exception $e) {
			log_message('error', 'Exceção: ' . $e->getMessage());
			echo json_encode(array(
				'success' => false,
				'error' => $e->getMessage()
			));
		}
	}

	private function calcular_data_com_weekend($data_inicio, $dias_prazo)
	{
		if ($dias_prazo <= 0) {
			return $data_inicio;
		}

		$data = new DateTime($data_inicio);
		$dias_adicionados = 0;

		while ($dias_adicionados < $dias_prazo) {
			$data->modify('+1 day');
			// Verifica se o dia atual não é sábado (6) nem domingo (7) (formato 'N', 1=segunda, 7=domingo)
			if ($data->format('N') < 6) {
				$dias_adicionados++;
			}
		}

		return $data->format('Y-m-d');
	}

	private function get_unidades_permitidas_ids()
	{
		$unidades_permitidas_usuario = $this->chamados_model->get_unidades_permitidas(
			$this->session->userdata('user_id'),
			$this->session->userdata('empresa_id')
		)->result();

		$unidade_in_where = array(0);
		foreach ($unidades_permitidas_usuario as $unid) {
			$unidade_in_where[] = (int) $unid->unidade_id;
		}

		return $unidade_in_where;
	}

	/**
	 * Verifica se o usuário logado tem permissão de acesso à unidade informada
	 *
	 * @param int $unidade_id
	 * @return bool
	 */
	private function tem_acesso_unidade($unidade_id)
	{
		$unidades_permitidas = $this->get_unidades_permitidas_ids();
		return in_array((int) $unidade_id, $unidades_permitidas);
	}

	/**
	 * Verifica se o usuário logado tem permissão de acesso ao chamado informado
	 *
	 * @param int $chamado_id
	 * @return bool
	 */
	private function tem_acesso_chamado($chamado_id)
	{
		if (empty($chamado_id)) {
			return false;
		}

		$chamado = $this->db->select('c.unidade_id, un.empresa_id')
			->join('unidades un', 'un.id = c.unidade_id')
			->where('c.id', (int) $chamado_id)
			->get('chamado c')
			->row();

		if (!$chamado || $chamado->empresa_id != $this->session->userdata('empresa_id') || !$this->tem_acesso_unidade($chamado->unidade_id)) {
			return false;
		}

		return $this->tem_acesso_unidade($chamado->unidade_id);
	}

	
	public function avaliar_chamado()
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("chamados", "avaliar_chamado")) {
			return false;
		}

		$data = $_POST;
		$data['user_id'] = $this->session->userdata('user_id');

		$chamado = $this->chamados_model->get(array(
			'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id',
			'join' => array(array('users u', 'u.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner')),
			'where' => 'c.id = "' . $_POST["chamado_id"] . '"',
			'order_by' => array('c.id', 'desc')
		))->row();

		if ($chamado->dono_id == $this->session->userdata('user_id')) {
			if ($this->chamados_model->salvar_avaliacao_chamado($data)) {
				if ($this->chamados_model->can_avaliado($_POST["chamado_id"])) {
					$this->chamados_model->update($_POST["chamado_id"], array('status_id' => 5));
					exit(true);
				} //else {
				//if($chamado->status_id != 3)
				//$this->chamados_model->update($_POST["chamado_id"], array('status_id'=>3));
				//}
			}
		}
		exit(false);
	}

	public function desavaliar_chamado()
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("chamados", "desavaliar_chamado")) {
			return false;
		}

		$data = $_POST;
		//$data['user_id'] = $this->session->userdata('user_id');

		$chamado = $this->chamados_model->get(array(
			'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id',
			'join' => array(array('users u', 'u.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner')),
			'where' => 'c.id = "' . $_POST["chamado_id"] . '"',
			'order_by' => array('c.id', 'desc')
		))->row();

		if ($chamado->dono_id == $this->session->userdata('user_id')) {
			$this->chamados_model->excluir_avaliacao_chamado($data);
		}

		return true;
	}

	public function reagendar()
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("chamados", "reagendar")) {
			redirect('auth', 'refresh');
		}

		$data_hora = explode(" ", $_POST["data_reagendar"]);
		$data = explode("/", $data_hora[0]);
		if (checkdate($data[1], $data[0], $data[2])) {
			if (date("Y-m-d H:i") < $data[2] . "-" . $data[1] . "-" . $data[0] . " " . $data_hora[1]) {
				if ($this->chamados_model->update($_POST["chamado_id"], array("data_prazo" => $data[2] . "-" . $data[1] . "-" . $data[0] . " " . $data_hora[1] . ":00", "atualizado_por" => $this->session->userdata("user_id")))) {
					$dados["chamado_id"] = $_POST["chamado_id"];
					$dados["responsavel_id"] = $this->session->userdata("user_id");
					$dados["descricao"] = "Previsão de resolução do chamado #" . $_POST["chamado_id"] . " para " . $_POST["data_reagendar"] . ".";
					$dados["data_envio"] = date("Y-m-d H:i:s");

					$this->chamados_model->create_dialogo($dados);

					$chamado = $this->chamados_model->get(array(
						'select' => 'c.id, c.categoria_id, c.catalogoservico_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id, p.nome as produto_desc, m.nome as modulo_desc, mc.nome as mcategoria_desc, ud.first_name as first_name_dono, ud.last_name as last_name_dono, u.email as email_resp, ud.email as email_dono, c.interessados, n.descricao as nivel_desc, c.desenvolvedor_id, udev.first_name as first_name_dev, udev.last_name as last_name_dev, udev.email as email_dev',
						'join' => array(array('produtos p', 'p.id = c.produto_id', 'left'), array('modulos m', 'm.id = c.modulo_id', 'left'), array('modulos_categoria mc', 'mc.id = c.categoria_id', 'left'), array('users u', 'u.id = c.responsavel_id', 'left'), array('users ud', 'ud.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner'), array('nivel n', 'n.id = c.nivel_id', 'left'), array('users udev', 'udev.id = c.desenvolvedor_id', 'left')),
						'where' => 'c.id = "' . $_POST["chamado_id"] . '"',
						'order_by' => array('c.id', 'desc')
					))->row();

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

					list($padrao_email_ass, $padrao_email_msg) = $this->aplicar_email_chamado(8, $_POST["chamado_id"]);

					/*$this->email->clear();
					$this->email->from($from_email, utf8_decode($from_name));

					$emails_to = array();
					if(is_array(unserialize($chamado->interessados))) {
						foreach (unserialize($chamado->interessados) as $int) {
							$emails_to[] = $this->chamados_model->get_email($int);
						}
					}
					$emails_to[] = $chamado->email_dono;
					$emails_to[] = $chamado->email_resp;

					$this->email->to(array_unique($emails_to));
		            $this->email->subject(utf8_decode($padrao_email_ass));
		            $this->email->message(utf8_decode($padrao_email_msg));
					$this->email->send();*/

					$mail = $this->phpmailer_library->load();
					$mail->isSMTP();
					$mail->Host     = $email_config['smtp_host'];
					$mail->SMTPAuth = true;
					$mail->Username = $email_config['smtp_user'];
					$mail->Password = $email_config['smtp_pass'];
					$mail->SMTPSecure = 'tsl';
					$mail->Port     = $email_config['smtp_port'];

					$mail->setFrom($from_email, utf8_decode($from_name));

					if (is_array(unserialize($chamado->interessados))) {
						foreach (unserialize($chamado->interessados) as $int) {
							$mail->addAddress($this->chamados_model->get_email($int));
						}
					}
					$mail->addAddress($chamado->email_dono);
					$mail->addAddress($chamado->email_resp);
					$mail->addAddress($chamado->email_dev);

					$mail->Subject = utf8_decode($padrao_email_ass);
					$mail->isHTML(true);

					$mail->Body = utf8_decode($padrao_email_msg);
					$mail->send();
				}
				$this->session->set_flashdata('message', show_alert_success("Prazo para resolução alterado com sucesso."));
			} else {
				$this->session->set_flashdata('message', show_alert_error("Data inválida."));
			}
		} else {
			$this->session->set_flashdata('message', show_alert_error("Data inválida."));
		}
		redirect('chamados', 'refresh');
	}

	// Mesma regra do planejamento da equipe (Kanban::planejamento), onde só administradores alteram a data prevista.
	private function pode_planejar_equipe()
	{
		return $this->ion_auth->is_admin() && $this->db->table_exists('kanban_tarefa_planejamento');
	}

	public function escalar()
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("chamados", "escalar")) {
			redirect('auth', 'refresh');
		}

		//$this->form_validation->set_rules('responsavel', 'lang:create_validation_responsavel_label', 'required');
		$this->form_validation->set_rules('produto', 'lang:create_validation_produto_label', 'required');
		$this->form_validation->set_rules('modulo', 'lang:create_validation_modulo_label', 'required');
		$this->form_validation->set_rules('categoria', 'lang:create_validation_categoria_label', 'required');
		$this->form_validation->set_rules('tipo_servico', 'lang:create_validation_tipo_label', 'required');
		$this->form_validation->set_rules('unidade', 'lang:create_validation_unidade_label', 'required');
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('nivel', 'lang:create_validation_nivel_label', 'required');
		if ($this->input->post('horas_prevista'))
			$this->form_validation->set_rules('horas_prevista', 'lang:create_validation_horas_prevista_label', 'required|validar_decimal');

		if ($this->form_validation->run() == TRUE) {
			$chamado = $this->chamados_model->get(array(
				'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, c.desenvolvedor_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id, c.catalogoservico_id',
				'join' => array(array('users u', 'u.id = c.responsavel_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner')),
				'where' => 'c.id = "' . $_POST["chamado_id"] . '"',
				'order_by' => array('c.id', 'desc')
			))->row();

			if (!$this->ion_auth->has_permissao("chamados", "reagendar")) {
				$catalogo_servico = $this->chamados_model->get_catalogo_servico($this->input->post('tipo_servico'))->row();
				$parametro = $this->chamados_model->get_parametro()->row();
				$calculaPrazo = calcula_horas_uteis($catalogo_servico->prazo_padrao, $parametro->expediente_inicio, $parametro->expediente_fim, new DateTime($chamado->created))->format("Y-m-d H:i:s");
			} else {
				$data_reagendar = explode(" ", $_POST["data_reagendar"]);
				$data_reagendar_verif = explode("/", $data_reagendar[0]);
				if (@checkdate($data_reagendar_verif[1], $data_reagendar_verif[0], $data_reagendar_verif[2])) {
					$calculaPrazo = convertDateBRToEU($data_reagendar[0]) . " " . $data_reagendar[1];
				} else {
					$catalogo_servico = $this->chamados_model->get_catalogo_servico($this->input->post('tipo_servico'))->row();
					$parametro = $this->chamados_model->get_parametro()->row();
					$calculaPrazo = calcula_horas_uteis($catalogo_servico->prazo_padrao, $parametro->expediente_inicio, $parametro->expediente_fim, new DateTime($chamado->created))->format("Y-m-d H:i:s");
				}
			}

			$data = array(
				"responsavel_id" => $chamado->responsavel_id,
				"produto_id" => $_POST["produto"],
				"unidade_id" => $_POST["unidade"],
				"status_id" => $_POST["status"],
				"modulo_id" => $_POST["modulo"],
				"categoria_id" => $_POST["categoria"],
				"catalogoservico_id" => $_POST["tipo_servico"],
				"data_prazo" => $calculaPrazo,
				"atualizado_por" => $this->session->userdata("user_id"),
				"data_inicio" => date('Y-m-d H:i:s'),
				"horas_prevista" => $this->input->post('horas_prevista') ? str_replace(",", ".", $this->input->post('horas_prevista')) : null,
				"nivel_id" => $_POST["nivel"]
			);
			if ($_POST["responsavel"])
				$data['responsavel_id'] = $_POST["responsavel"];
			if ($_POST["desenvolvedor"])
				$data['desenvolvedor_id'] = $_POST["desenvolvedor"];

			if ($_POST["status"] == 3) {
				$data['data_finalizacao'] = date('Y-m-d H:i:s');
				$data['hash'] = sha1($chamado->id . $chamado->dono_id);
			}

			// Data planejada no planejamento da equipe: validada antes de salvar para não escalar pela metade.
			$data_planejada = null;
			$data_planejada_anterior = null;
			$data_planejada_txt = trim((string) $this->input->post('data_planejada'));
			if ($data_planejada_txt !== '' && $this->pode_planejar_equipe()) {
				$partes = explode('/', $data_planejada_txt);
				if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data_planejada_txt) || !checkdate((int) $partes[1], (int) $partes[0], (int) $partes[2])) {
					$this->session->set_flashdata('message', show_alert_error("Data planejada inválida. Use o formato dd/mm/aaaa."));
					redirect('chamados/details/' . (int) $_POST["chamado_id"], 'refresh');
				}
				$empresa_planejamento = (int) $this->session->userdata('empresa_id');
				if ((int) $chamado->empresa_id !== $empresa_planejamento) {
					$this->session->set_flashdata('message', show_alert_error("O chamado é de outra empresa. Selecione a empresa do chamado para planejá-lo."));
					redirect('chamados/details/' . (int) $_POST["chamado_id"], 'refresh');
				}
				$data_planejada = sprintf('%04d-%02d-%02d', $partes[2], $partes[1], $partes[0]);
				// Na agenda, o chamado conta para o programador quando há um; senão, para o responsável.
				$colaborador_planejado = (int) ($this->input->post('desenvolvedor') ? $this->input->post('desenvolvedor') : $chamado->desenvolvedor_id);
				if ($colaborador_planejado <= 0) {
					$colaborador_planejado = (int) $data['responsavel_id'];
				}
				$this->load->model('kanban_dashboard_model');
				if ($colaborador_planejado > 0 && $this->kanban_dashboard_model->is_date_unavailable($empresa_planejamento, $colaborador_planejado, $data_planejada)) {
					$this->session->set_flashdata('message', show_alert_error("Não foi possível escalar: " . html_escape($this->chamados_model->get_user_name($colaborador_planejado)) . " está indisponível em " . $data_planejada_txt . " (folga, férias ou feriado). Escolha outra data planejada."));
					redirect('chamados/details/' . (int) $_POST["chamado_id"], 'refresh');
				}
				$data_planejada_anterior = $this->kanban_dashboard_model->get_planned_date($empresa_planejamento, $chamado->id);
			}

			// Restrito: os registros desta escalada ficam internos e o e-mail não vai para os grupos do cliente.
			$restrito = $this->input->post('restrito') && $this->pode_marcar_interno();
			$dados = array('interno' => $restrito ? 1 : 0);

			if ($this->chamados_model->update($_POST["chamado_id"], $data)) {
				if ($data_planejada !== null && $data_planejada !== $data_planejada_anterior) {
					$this->kanban_dashboard_model->save_planned_date((int) $chamado->empresa_id, $chamado->id, $data_planejada, $this->session->userdata("user_id"));
					$this->chamados_model->create_dialogo(array(
						"chamado_id" => $_POST["chamado_id"],
						"responsavel_id" => $this->session->userdata("user_id"),
						"descricao" => "Planejado para " . date("d/m/Y", strtotime($data_planejada)) . " no planejamento da equipe.",
						"data_envio" => date("Y-m-d H:i:s"),
						"interno" => $dados['interno'],
					));
				} else {
					$data_planejada = null;
				}

				/** kanban **/
				$this->kanban_model->atualizarKanban($this->input->post(), $_POST["chamado_id"], false);
				/** fim kanban **/

				// Aviso por Discord desabilitado por enquanto no Maestriatech.
				// if ((int) $_POST["status"] === 3 && (int) $chamado->status_id !== 3) {
				// 	$this->avisar_discord_encerramento((int) $_POST["chamado_id"]);
				// }

				//adicionar flag retrabalho
				if (($chamado->status_id == 8 || $chamado->status_id == 3 || $chamado->status_id == 5) && $_POST['status'] == 1) {
					$dados["retrabalho"] = 1;
					$dados["chamado_id"] = $_POST["chamado_id"];
					$dados["responsavel_id"] = $this->session->userdata("user_id");
					$dados["descricao"] = "Status do chamado alterado para " . $this->chamados_model->get_status_name($_POST["status"]) . ".";
					$dados["data_envio"] = date("Y-m-d H:i:s");
					$this->chamados_model->create_dialogo($dados);
				}
				unset($dados["retrabalho"]);

				if ($_POST["responsavel"]) {
					$dados["chamado_id"] = $_POST["chamado_id"];
					$dados["responsavel_id"] = $this->session->userdata("user_id");
					$dados["descricao"] = "Chamado escalado para: " . $this->chamados_model->get_user_name($data['responsavel_id']) . ".";
					$dados["data_envio"] = date("Y-m-d H:i:s");

					$this->chamados_model->create_dialogo($dados);
				}

				if ($_POST["desenvolvedor"]) {
					$dados["chamado_id"] = $_POST["chamado_id"];
					$dados["responsavel_id"] = $this->session->userdata("user_id");
					$dados["descricao"] = "Desenvolvedor responsável: " . $this->chamados_model->get_user_name($data['desenvolvedor_id']) . ".";
					$dados["data_envio"] = date("Y-m-d H:i:s");

					$this->chamados_model->create_dialogo($dados);
				}

				if ($chamado->catalogoservico_id != $_POST["tipo_servico"]) {
					$dados["chamado_id"] = $_POST["chamado_id"];
					$dados["responsavel_id"] = $this->session->userdata("user_id");
					$dados["descricao"] = "Serviço do chamado alterado para: " . $this->chamados_model->get_produto($this->input->post('produto'))->row()->nome . " > " . $this->chamados_model->get_modulo($this->input->post('modulo'))->row()->nome . " > " . $this->chamados_model->get_mcategoria($this->input->post('categoria'))->row()->nome . " > " . $this->chamados_model->get_catalogo_servico($this->input->post('tipo_servico'))->row()->descricao . ".";
					$dados["data_envio"] = date("Y-m-d H:i:s");

					$this->chamados_model->create_dialogo($dados);
				}
				$prazo_antigo = $chamado->data_prazo;
				if ($chamado->data_prazo != $calculaPrazo . ":00") {
					$dados["chamado_id"] = $_POST["chamado_id"];
					$dados["responsavel_id"] = $this->session->userdata("user_id");
					$dados["descricao"] = "Prazo do chamado alterado para: " . date("d/m/Y H:i", strtotime($calculaPrazo)) . ".";
					$dados["data_envio"] = date("Y-m-d H:i:s");

					$this->chamados_model->create_dialogo($dados);
				}

				if (trim($this->input->post('dialogo')) != "") {
					$dados["chamado_id"] = $_POST["chamado_id"];
					$dados["responsavel_id"] = $this->session->userdata("user_id");
					$dados["descricao"] = $this->input->post('dialogo');
					$dados["data_envio"] = date("Y-m-d H:i:s");
					$this->chamados_model->create_dialogo($dados);
				}

				if (isset($_FILES) && ! empty($_FILES)) {
					$this->load->library('upload', get_upload_config());
					if ($this->upload->do_upload('anexo')) {
						$dados_anexo["chamado_id"] = $_POST["chamado_id"];
						$dados_anexo["responsavel_id"] = $this->session->userdata('user_id');
						$dados_anexo["anexo"] = $this->upload->data('file_name');
						$dados_anexo["created"] = date("Y-m-d H:i:s");

						$anexo_create = $this->chamados_model->create_anexo($dados_anexo);
					}
				}

				$chamado = $this->chamados_model->get(array(
					'select' => 'c.id, c.interessados, c.categoria_id, c.catalogoservico_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id, p.nome as produto_desc, m.nome as modulo_desc, mc.nome as mcategoria_desc, ud.first_name as first_name_dono, ud.last_name as last_name_dono, u.email as email_resp, ud.email as email_dono, c.plano_acao, n.descricao as nivel_desc, c.desenvolvedor_id, udev.first_name as first_name_dev, udev.last_name as last_name_dev, udev.email as email_dev',
					'join' => array(array('produtos p', 'p.id = c.produto_id', 'left'), array('modulos m', 'm.id = c.modulo_id', 'left'), array('modulos_categoria mc', 'mc.id = c.categoria_id', 'left'), array('users u', 'u.id = c.responsavel_id', 'left'), array('users ud', 'ud.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner'), array('nivel n', 'n.id = c.nivel_id', 'left'), array('users udev', 'udev.id = c.desenvolvedor_id', 'left')),
					'where' => 'c.id = "' . $_POST["chamado_id"] . '"',
					'order_by' => array('c.id', 'desc')
				))->row();

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

				$msg_adicional = "";
				if ($prazo_antigo != $calculaPrazo . ":00") {
					$msg_adicional .= "<b>Nova previsão:</b> " . date("d/m/Y H:i", strtotime($calculaPrazo));
				}
				if ($data_planejada !== null) {
					if ($msg_adicional != "")
						$msg_adicional .= "<br>";
					$msg_adicional .= "<b>Planejado para:</b> " . date("d/m/Y", strtotime($data_planejada));
				}

				if (trim($this->input->post('dialogo')) != "") {
					if ($msg_adicional != "")
						$msg_adicional .= "<br>";
					$msg_adicional .= "<b>Mensagem de quem escalou:</b><br>" . nl2br(html_escape($this->input->post('dialogo')));
				}
				list($padrao_email_ass, $padrao_email_msg) = $this->aplicar_email_chamado(9, $_POST["chamado_id"], array(
					'{msg_adicional}' => $msg_adicional !== '' ? '<div style="margin:0 0 16px;padding:12px 14px;background:#f7f9fb;border-left:3px solid #3c8dbc;font-size:14px;line-height:1.5;color:#444444;">' . $msg_adicional . '</div>' : '',
				), $restrito);

				/*$this->email->clear();
				$this->email->from($from_email, utf8_decode($from_name));

				$emails_to = array();
				if(is_array(unserialize($chamado->interessados))) {
					foreach (unserialize($chamado->interessados) as $int) {
						$emails_to[] = $this->chamados_model->get_email($int);
					}
				}
				$emails_to[] = $chamado->email_dono;
				$emails_to[] = $chamado->email_resp;

				$this->email->to(array_unique($emails_to));
	            $this->email->subject(utf8_decode($padrao_email_ass));
	            $this->email->message(utf8_decode($padrao_email_msg));
				$this->email->send();*/

				$mail = $this->phpmailer_library->load();
				$mail->isSMTP();
				$mail->Host     = $email_config['smtp_host'];
				$mail->SMTPAuth = true;
				$mail->Username = $email_config['smtp_user'];
				$mail->Password = $email_config['smtp_pass'];
				$mail->SMTPSecure = 'tsl';
				$mail->Port     = $email_config['smtp_port'];

				$mail->setFrom($from_email, utf8_decode($from_name));

				// Mesmos grupos que o diálogo interno deixa de fora do e-mail.
				$grupos_excluidos = array(2, 11, 35, 58, 68);
				$pode_receber = function ($user_id) use ($restrito, $grupos_excluidos) {
					if (!$restrito || !$user_id) {
						return true;
					}
					foreach ($grupos_excluidos as $grupo_id) {
						if ($this->ion_auth->in_group($grupo_id, $user_id)) {
							return false;
						}
					}
					return true;
				};

				if (is_array(unserialize($chamado->interessados))) {
					foreach (unserialize($chamado->interessados) as $int) {
						if ($pode_receber($int)) {
							$mail->addAddress($this->chamados_model->get_email($int));
						}
					}
				}
				if ($pode_receber($chamado->dono_id))
					$mail->addAddress($chamado->email_dono);
				if ($pode_receber($chamado->responsavel_id))
					$mail->addAddress($chamado->email_resp);
				if ($pode_receber($chamado->desenvolvedor_id))
					$mail->addAddress($chamado->email_dev);

				$mail->Subject = utf8_decode($padrao_email_ass);
				$mail->isHTML(true);

				$mail->Body = utf8_decode($padrao_email_msg);
				if (count($mail->getToAddresses()) > 0) {
					$mail->send();
				}

				if ($chamado->plano_acao != "" && $chamado->plano_acao != 0) {
					$this->atualizar_plano_acao($chamado->plano_acao);
				}
			}

			$this->session->set_flashdata('message', show_alert_success($restrito ? "Chamado escalado com sucesso (restrito)." : "Chamado escalado com sucesso."));
		} else {
			$this->session->set_flashdata('message', show_alert_error(validation_errors()));
		}
		redirect('chamados/details/' . $_POST["chamado_id"], 'refresh');
	}

	public function calculaPrazo()
	{
		$chamado = $this->chamados_model->get(array(
			'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id',
			'join' => array(array('users u', 'u.id = c.responsavel_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner')),
			'where' => 'c.id = "' . $_POST["chamado_id"] . '"',
			'order_by' => array('c.id', 'desc')
		))->row();
		$calculaPrazo = "";
		$horas_prevista = "";
		if ($_POST['tipo_servico']) {
			$catalogo_servico = $this->chamados_model->get_catalogo_servico($_POST['tipo_servico'])->row();
			$parametro = $this->chamados_model->get_parametro()->row();
			$calculaPrazo = date("d/m/Y H:i", strtotime(calcula_horas_uteis($catalogo_servico->prazo_padrao, $parametro->expediente_inicio, $parametro->expediente_fim, new DateTime($chamado->created))->format("Y-m-d H:i:s")));
			$horas_prevista = str_replace(".", ",", $catalogo_servico->tempo_padrao);
		}
		exit(json_encode(array("calculaPrazo" => $calculaPrazo, "horas_prevista" => $horas_prevista)));
	}

	public function status()
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("chamados", "status")) {
			redirect('auth', 'refresh');
		}

		$chamado = $this->chamados_model->get(array(
			'select' => 'c.id, c.dono_id, u.email as email_dono, c.status_id, c.responsavel_id, c.interessados, u.first_name, u.last_name, us.email as email_resp, un.unidade as cliente',
			'join' => array(array('users u', 'u.id = c.dono_id', 'left'), array('users us', 'us.id = c.responsavel_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner')),
			'where' => 'c.id = "' . $_POST["chamado_id"] . '"',
			'order_by' => array('c.id', 'desc')
		))->row();

		if ($_POST["status"] == 3 && $chamado->status_id != 8) {
			$this->session->set_flashdata('message', show_alert_error('Status "Em Avaliação" apenas permitido para chamados resolvidos.'));
			redirect('chamados/details/' . $_POST["chamado_id"], 'refresh');
		}

		if ($_POST["status"] == 3 || $_POST["status"] == 5) {
			$chamados_vinculados = $this->chamados_model->get(array(
				'select' => 'c.id, c.titulo, c.status_id',
				'join' => array(),
				'where' => 'c.referencia_id = "' . $_POST["chamado_id"] . '" AND c.status_id != 3 AND c.status_id != 5',
				'order_by' => array('c.id', 'desc')
			));
			if ($chamados_vinculados->num_rows() > 0) {
				$this->session->set_flashdata('message', show_alert_error('O chamado somente poderá ser fechado se todos os chamados vinculados estiverem com status "Em avaliação" ou "Avaliado".'));
				redirect('chamados/details/' . $_POST["chamado_id"], 'refresh');
			}
		}

		//$this->form_validation->set_rules('responsavel', 'lang:create_validation_responsavel_label', 'required');
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');

		if ($this->form_validation->run() == TRUE) {
			$data = array(
				"responsavel_id" => $chamado->responsavel_id,
				"status_id" => $_POST["status"],
				"atualizado_por" => $this->session->userdata("user_id")
			);
			if (@$_POST["responsavel"])
				$data['responsavel_id'] = $_POST["responsavel"];
			if ($_POST["status"] == 3) {
				$data['data_finalizacao'] = date('Y-m-d H:i:s');
				$data['hash'] = sha1($chamado->id . $chamado->dono_id);
			}

			$this->chamados_model->update($_POST["chamado_id"], $data);
			$this->session->set_flashdata('message', show_alert_success("Chamado atualizado com sucesso."));

			/** kanban **/
			$this->kanban_model->atualizarKanban($this->input->post(), $_POST["chamado_id"], false);
			/** fim kanban **/

			$dados["chamado_id"] = $_POST["chamado_id"];
			$dados["responsavel_id"] = $this->session->userdata("user_id");
			$dados["descricao"] = "Status do chamado alterado para " . $this->chamados_model->get_status_name($_POST["status"]) . ".";
			$dados["data_envio"] = date("Y-m-d H:i:s");

			//adicionar flag retrabalho
			if (($chamado->status_id == 8 || $chamado->status_id == 3 || $chamado->status_id == 5) && $_POST['status'] == 1) {
				$dados["retrabalho"] = 1;
			}

			$this->chamados_model->create_dialogo($dados);

			// Avisos por WhatsApp e Discord desabilitados por enquanto no Maestriatech.
			// if ((int) $_POST["status"] === 3 && (int) $chamado->status_id !== 3) {
			// 	$this->avisar_whatsapp_solicitante((int) $_POST["chamado_id"], 'encerramento');
			// 	$this->avisar_discord_encerramento((int) $_POST["chamado_id"]);
			// }

			$i = 0;
			$dialogos = array();
			foreach ($this->chamados_model->get_dialogos($_POST["chamado_id"])->result() as $value) {
				$dialogos[$i]['id'] = $value->id;
				$dialogos[$i]['chamado_id'] = $value->chamado_id;
				$dialogos[$i]['responsavel_id'] = $value->responsavel_id;
				$dialogos[$i]['descricao'] = $value->descricao;
				$dialogos[$i]['created'] = $value->data_envio;
				$dialogos[$i]['data_leitura'] = $value->data_leitura;
				$dialogos[$i]['first_name'] = $value->first_name;
				$dialogos[$i]['last_name'] = $value->last_name;
				$i++;
			}
			$anexos = $this->chamados_model->get_anexos($_POST["chamado_id"])->result_array();

			$timeline = array_merge($dialogos, $anexos);

			usort($timeline, function ($a, $b) {
				return strtotime($b['created']) - strtotime($a['created']);
			});

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

			$novo_status = $this->chamados_model->get_status($_POST["status"])->row()->descricao;
			$observacao_status = '';
			if ($_POST["status"] == 3) {
				$observacao_status = '<div style="margin-top:6px;font-size:13px;color:#555555;">O atendimento foi conclu&iacute;do. O solicitante receber&aacute; um e-mail para avaliar o chamado.</div>';
			}
			list($padrao_email_ass, $padrao_email_msg) = $this->aplicar_email_chamado(10, $_POST["chamado_id"], array(
				'{chamado_status}' => html_escape($novo_status),
				'{status_observacao}' => $observacao_status,
			));

			$this->email->clear();
			$this->email->from($from_email, utf8_decode($from_name));

			$chamado = $this->chamados_model->get(array(
				'select' => 'c.id, c.dono_id, u.email as email_dono, c.responsavel_id, c.interessados, u.first_name, u.last_name, us.email as email_resp, c.plano_acao, udev.email as email_dev',
				'join' => array(array('users u', 'u.id = c.dono_id', 'left'), array('users us', 'us.id = c.responsavel_id', 'left'), array('users udev', 'udev.id = c.desenvolvedor_id', 'left')),
				'where' => 'c.id = "' . $_POST["chamado_id"] . '"',
				'order_by' => array('c.id', 'desc')
			))->row();

			/*$emails_to = array();
			if(is_array(unserialize($chamado->interessados))) {
				foreach (unserialize($chamado->interessados) as $int) {
					$emails_to[] = $this->chamados_model->get_email($int);
				}
			}
			$emails_to[] = $chamado->email_dono;
			$emails_to[] = $chamado->email_resp;

			$this->email->to(array_unique($emails_to));
            $this->email->subject(utf8_decode($padrao_email_ass));
            $this->email->message(utf8_decode($padrao_email_msg));
			$this->email->send();*/

			$mail = $this->phpmailer_library->load();
			$mail->isSMTP();
			$mail->Host     = $email_config['smtp_host'];
			$mail->SMTPAuth = true;
			$mail->Username = $email_config['smtp_user'];
			$mail->Password = $email_config['smtp_pass'];
			$mail->SMTPSecure = 'tsl';
			$mail->Port     = $email_config['smtp_port'];

			$mail->setFrom($from_email, utf8_decode($from_name));

			if (is_array(unserialize($chamado->interessados))) {
				foreach (unserialize($chamado->interessados) as $int) {
					$mail->addAddress($this->chamados_model->get_email($int));
				}
			}
			$mail->addAddress($chamado->email_dono);
			$mail->addAddress($chamado->email_resp);
			$mail->addAddress($chamado->email_dev);

			$mail->Subject = utf8_decode($padrao_email_ass);
			$mail->isHTML(true);

			$mail->Body = utf8_decode($padrao_email_msg);
			$mail->send();

			if ($_POST["status"] == 3) {
				//$this->email->clear();
				$mail->clearAllRecipients();

				//$this->email->from($from_email, utf8_decode($from_name));

				$chamado_avaliacao = $this->dados_email_chamado($_POST["chamado_id"]);
				$data = array(
					"dono" => $chamado->first_name . " " . $chamado->last_name,
					"link" => base_url('auth/avaliacao_chamado/' . $chamado->dono_id . '/' . $data["hash"]),
					"chamado_id" => $_POST["chamado_id"],
					"titulo" => $chamado_avaliacao ? $chamado_avaliacao->titulo : '',
					"cliente" => $chamado->cliente,
					"empresa_id" => $this->session->userdata('empresa_id'),
				);
				$msg_avaliacao = $this->load->view("admin/email/avaliacao_chamado.tpl.php", $data, true);

				/*$this->email->to($chamado->email_dono);
	            $this->email->subject(utf8_decode("Avaliação chamado #".$_POST["chamado_id"]));
	            $this->email->message(utf8_decode($msg_avaliacao));
				$this->email->send();*/

				$mail->addAddress($chamado->email_dono);
				$mail->Subject = utf8_decode("Avalie o atendimento do chamado #" . $_POST["chamado_id"] . ($chamado_avaliacao ? " - " . $chamado_avaliacao->titulo : ""));
				$mail->Body = utf8_decode($msg_avaliacao);
				$mail->send();
			}
		} else {
			$this->session->set_flashdata('message', show_alert_error(validation_errors()));
		}
		redirect('chamados/details/' . $_POST["chamado_id"], 'refresh');
	}

	public function imprimir($id)
	{
		log_message('error', '=== INICIANDO IMPRIMIR - ID: ' . $id);
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("chamados", "imprimir")) {
			log_message('error', 'IMPRIMIR - Sem permissão ou não logado');
			redirect('auth', 'refresh');
		}

		// Carregar modelos de checklists
		$this->load->model('chamados_checklists_model');
		$this->load->model('chamados_checklists_etapas_model');

		// BUSCAR CHECKLISTS ASSOCIADOS AO CHAMADO
		$checklists_associados = $this->chamados_checklists_model->get_com_etapas($id);

		$chamado = $this->chamados_model->get(array(
			'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id, mc.nome as categoria_desc, p.nome as produto_desc, m.nome as modulo_desc, n.descricao as nivel_desc, ud.first_name as first_name_dono, ud.last_name as last_name_dono',
			'join' => array(
				array('users u', 'u.id = c.responsavel_id', 'left'),
				array('users ud', 'ud.id = c.dono_id', 'left'),
				array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'),
				array('unidades un', 'un.id = c.unidade_id', 'inner'),
				array('chamado_status cst', 'cst.id = c.status_id', 'inner'),
				array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner'),
				array('produtos p', 'p.id = c.produto_id', 'inner'),
				array('modulos m', 'm.id = c.modulo_id', 'inner'),
				array('modulos_categoria mc', 'mc.id = c.categoria_id', 'inner'),
				array('nivel n', 'n.id = c.nivel_id', 'left')
			),
			'where' => 'c.id = "' . $id . '"',
			'order_by' => array('c.id', 'desc')
		))->row();

		if (!$chamado) {
			log_message('error', 'IMPRIMIR - Chamado não encontrado para o ID: ' . $id);
			$this->session->set_flashdata('message', show_alert_error("Chamado não encontrado."));
			redirect('chamados', 'refresh');
		}

		// Já buscamos os checklists com etapas acima usando $this->chamados_checklists_model->get_com_etapas($id)
		$this->data['checklists_associados'] = $checklists_associados;

		$i = 0;
		$dialogos = array();
		foreach ($this->chamados_model->get_dialogos($chamado->id)->result() as $value) {
			$dialogos[$i]['id'] = $value->id;
			$dialogos[$i]['chamado_id'] = $value->chamado_id;
			$dialogos[$i]['responsavel_id'] = $value->responsavel_id;
			$dialogos[$i]['descricao'] = $value->descricao;
			$dialogos[$i]['created'] = $value->data_envio;
			$dialogos[$i]['data_leitura'] = $value->data_leitura;
			$dialogos[$i]['first_name'] = $value->first_name;
			$dialogos[$i]['last_name'] = $value->last_name;
			$dialogos[$i]['tipo'] = "dialogo";
			$i++;
		}
		$anexos = $this->chamados_model->get_anexos($chamado->id)->result_array();

		$timeline = array_merge($dialogos, $anexos);

		usort($timeline, function ($a, $b) {
			return strtotime($b['created']) - strtotime($a['created']);
		});

		$this->data['timeline'] = $timeline;
		$this->data['chamado'] = $chamado;
		$this->data['apontamentos'] = $this->chamados_model->get_apontamentos($chamado->id);

		$times = array();
		if ($this->data['apontamentos']->num_rows()) {
			foreach ($this->data['apontamentos']->result() as $key => $value) {
				$times[] = $value->esforco;
			}
		}

		$this->data['esforco'] = AddPlayTime($times);

		$this->load->library('pdf');

		$this->pdf->load_view('admin/chamados/imprimir', "relatorio-chamado-" . $id, $this->data);
	}

	public function get_produtos_habilitados()
	{
		$produtos_habilitados = $this->chamados_model->get_produtos_habilitados($_POST["id"]);

		$select = array();
		if ($produtos_habilitados->num_rows() > 0):
			foreach ($produtos_habilitados->result() as $value) {
				$prdt = array(
					'id' => $value->id,
					'nome' => $value->nome
				);
				array_push($select, $prdt);
			}
		else:
			$select = false;
		endif;

		exit(json_encode($select));
	}

	public function get_modulos_habilitados()
	{
		$modulos_habilitados = $this->chamados_model->get_modulos_habilitados($_POST["id_unidade"], $_POST["id_produto"]);

		$select = array();
		if ($modulos_habilitados->num_rows() > 0):
			foreach ($modulos_habilitados->result() as $value) {
				$prdt = array(
					'id' => $value->id,
					'nome' => $value->nome
				);
				array_push($select, $prdt);
			}
		else:
			$select = false;
		endif;

		exit(json_encode($select));
	}

	public function get_modulo_categorias()
	{
		$modulo_categorias = $this->chamados_model->get_modulo_categorias($_POST["id"]);

		$select = array();
		if ($modulo_categorias->num_rows() > 0):
			foreach ($modulo_categorias->result() as $value) {
				$prdt = array(
					'id' => $value->id,
					'nome' => $value->nome
				);
				array_push($select, $prdt);
			}
		else:
			$select = false;
		endif;

		exit(json_encode($select));
	}

	public function get_servicos()
	{
		$servicos = $this->chamados_model->get_catalogos_servico_ativos($_POST["id"]);

		$select = array();
		if ($servicos->num_rows() > 0):
			foreach ($servicos->result() as $value) {
				$prdt = array(
					'id' => $value->id,
					'nome' => $value->descricao
				);
				array_push($select, $prdt);
			}
		else:
			$select = false;
		endif;

		exit(json_encode(array("select" => $select, "layout" => $this->chamados_model->get_mcategoria($_POST["id"])->row()->layout)));
	}

	public function get_users_unidade()
	{
		$users_unidade = $this->chamados_model->get_users_interessados_unidade($_POST["id"]);

		$select = array();
		if ($users_unidade->num_rows() > 0):
			foreach ($users_unidade->result() as $value) {
				$user = array(
					'id' => $value->id,
					'nome' => $value->first_name . " " . $value->last_name,
					'email' =>  $value->email
				);
				array_push($select, $user);
			}
		else:
			$select = false;
		endif;

		exit(json_encode($select));
	}

	public function get_plano_acao()
	{
		$planos = $this->chamados_model->get_plano_acao("p.unidade_id = '" . $_POST["id"] . "' OR p.unidade_id IS NULL");

		$select = array();
		if ($planos->num_rows() > 0):
			foreach ($planos->result() as $value) {
				$p = array(
					'id' => $value->id,
					'nome' => $value->desc
				);
				array_push($select, $p);
			}
		else:
			$select = false;
		endif;

		exit(json_encode($select));
	}

	public function get_checklists()
	{
		$checklists = $this->checklists_model->get();
		$select = array();
		if ($checklists->num_rows() > 0) {
			foreach ($checklists->result() as $cl) {
				$c = array(
					'id' => $cl->id,
					'nome' => $cl->nome
				);
				array_push($select, $c);
			}
		} else {
			$select = false;
		}

		exit(json_encode($select));
	}

	public function alterar_prioridade()
	{
		if ($_POST["chamado_id"] && $_POST["prioridade"] && $this->ion_auth->has_permissao("chamados", "alterar_prioridade")) {
			$chamado = $this->chamados_model->get(array(
				'select' => 'c.id, c.categoria_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id, c.desenvolvedor_id',
				'join' => array(array('users u', 'u.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner')),
				'where' => 'c.id = "' . $_POST["chamado_id"] . '"',
				'order_by' => array('c.id', 'desc')
			))->row();

			if ($chamado->dono_id == $this->session->userdata('user_id') || $chamado->responsavel_id == $this->session->userdata('user_id') || $chamado->desenvolvedor_id == $this->session->userdata('user_id')) {
				$prior = $this->chamados_model->salvar_prioridade($_POST["chamado_id"], $_POST["prioridade"]);
			}
		}
		return true;
	}

	public function apontamento_automatico_criar()
	{
		$retorno = array();
		$data_hora = explode(" ", $_POST["data"]);
		$data = explode("/", $data_hora[0]);
		if ($_POST["chamado_id"] && checkdate($data[1], $data[0], $data[2]) && $this->ion_auth->has_permissao("chamados", "apontamento_automatico_criar")) {
			$id = $this->chamados_model->salvar_apontamento($_POST);
			$retorno["erro"] = false;
			$retorno["data"] = $id;
		} else {
			$retorno["erro"] = true;
			$retorno["data"] = "Problema ao iniciar apontamento, tente novamente ou entre em contato com o administrador.";
		}
		exit(json_encode($retorno));
	}

	public function apontamento_manual_criar()
	{
		if (!isset($_POST["data"])) {
			$this->session->set_flashdata('message', show_alert_error("Data inválida."));
			redirect('chamados/details/' . $_POST["chamado_id"], 'refresh');
		}
		$data_hora = explode(" ", $_POST["data"]);
		$data = explode("/", $data_hora[0]);
		if ($_POST["chamado_id"] && checkdate($data[1], $data[0], $data[2]) && $this->ion_auth->has_permissao("chamados", "apontamento_manual_criar")) {
			$this->chamados_model->salvar_apontamento($_POST);
			$this->session->set_flashdata('message', show_alert_success("Apontamento criado."));
		} else {
			if (!checkdate($data[1], $data[0], $data[2]))
				$this->session->set_flashdata('message', show_alert_error("Data inválida."));
			else
				$this->session->set_flashdata('message', show_alert_error("Houve um problema, tente novamente."));
		}
		redirect('chamados/details/' . $_POST["chamado_id"], 'refresh');
	}

	public function apontamento_editar()
	{
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao("chamados", "apontamento_editar")) {
			redirect('auth/login', 'refresh');
			return;
		}
		$data = explode("/", explode(" ", (string) $this->input->post('data'))[0]);
		if (count($data) !== 3 || !checkdate((int) $data[1], (int) $data[0], (int) $data[2])) {
			$this->session->set_flashdata('message', show_alert_error("Data inválida."));
		} elseif (!$this->chamados_model->apontamento_da_empresa($this->input->post('apontamento_id'))) {
			$this->session->set_flashdata('message', show_alert_error("Apontamento não encontrado."));
		} else {
			$this->chamados_model->salvar_apontamento($_POST);
			$this->session->set_flashdata('message', show_alert_success("Apontamento editado."));
		}
		$this->redirect_apontamentos();
	}

	public function apontamento_excluir()
	{
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao("chamados", "apontamento_excluir")) {
			redirect('auth/login', 'refresh');
			return;
		}
		$apontamento_id = (int) $this->input->post('apontamento_id_excluir');
		if ($apontamento_id && $this->chamados_model->apontamento_da_empresa($apontamento_id) && $this->chamados_model->excluir_apontamento($apontamento_id)) {
			$this->session->set_flashdata('message', show_alert_success("Apontamento excluído."));
		} else {
			$this->session->set_flashdata('message', show_alert_error("Não foi possível excluir o apontamento."));
		}
		$this->redirect_apontamentos();
	}

	public function apontamentos()
	{
		if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("chamados", "apontamentos")) {
			redirect('auth/login', 'refresh');
			return;
		}
		$this->page_title->clear();
		$this->page_title->push(lang('menu_chamados_apontamentos'));
		$this->data['pagetitle'] = $this->page_title->show();
		$this->breadcrumbs->unshift(2, lang('menu_chamados_apontamentos'), 'chamados/apontamentos');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		$data_valida = function ($valor) {
			$data = DateTime::createFromFormat('!Y-m-d', (string) $valor);
			return ($data && $data->format('Y-m-d') === $valor) ? $valor : null;
		};
		$inteiros = function ($valores) {
			return is_array($valores) ? array_values(array_unique(array_filter(array_map('intval', $valores)))) : array();
		};

		// Unidades que o usuário pode ver: limitam a consulta e as opções do filtro de clientes.
		$unidade_options = array();
		foreach ($this->chamados_model->get_unidades_permitidas($this->session->userdata('user_id'), $this->session->userdata('empresa_id'))->result() as $unid) {
			$unidade_options[(int) $unid->unidade_id] = $unid->unidade;
		}
		$usuarios_options = array();
		foreach ($this->chamados_model->get_usuarios()->result() as $usu) {
			$usuarios_options[(int) $usu->id] = $usu->nome;
		}

		$inicio = $data_valida($this->input->get('inicio', true)) ?: date('Y-m-01');
		$fim = $data_valida($this->input->get('fim', true)) ?: date('Y-m-d');
		if ($inicio > $fim) {
			list($inicio, $fim) = array($fim, $inicio);
		}
		$filtros = array(
			'inicio' => $inicio,
			'fim' => $fim,
			'usuarios' => array_values(array_intersect($inteiros($this->input->get('usuarios')), array_keys($usuarios_options))),
			'clientes' => array_values(array_intersect($inteiros($this->input->get('clientes')), array_keys($unidade_options))),
			'chamado' => (int) $this->input->get('chamado'),
			'unidades_permitidas' => array_keys($unidade_options),
		);

		$apontamentos = $this->chamados_model->get_apontamentos_relatorio($filtros);

		// Resumos do período (total, por usuário e por cliente) usados na tela e no PDF.
		$resumo = array('segundos' => 0, 'registros' => count($apontamentos), 'chamados' => array(), 'usuarios' => array(), 'clientes' => array());
		foreach ($apontamentos as $apontamento) {
			$segundos = (int) $apontamento->segundos;
			$resumo['segundos'] += $segundos;
			$resumo['chamados'][(int) $apontamento->chamado_id] = true;
			foreach (array('usuarios' => array($apontamento->user_id, $apontamento->nome_usuario), 'clientes' => array($apontamento->unidade_id, $apontamento->cliente)) as $grupo => $item) {
				if (!isset($resumo[$grupo][$item[0]])) {
					$resumo[$grupo][$item[0]] = array('nome' => $item[1], 'segundos' => 0, 'registros' => 0, 'chamados' => array());
				}
				$resumo[$grupo][$item[0]]['segundos'] += $segundos;
				$resumo[$grupo][$item[0]]['registros']++;
				$resumo[$grupo][$item[0]]['chamados'][(int) $apontamento->chamado_id] = true;
			}
		}
		foreach (array('usuarios', 'clientes') as $grupo) {
			usort($resumo[$grupo], function ($x, $y) {
				return $y['segundos'] - $x['segundos'];
			});
		}

		$this->data['apontamentos'] = $apontamentos;
		$this->data['resumo'] = $resumo;
		$this->data['filtros'] = $filtros;
		$this->data['usuarios_options'] = $usuarios_options;
		$this->data['unidade_options'] = $unidade_options;
		$this->data['query_atual'] = (string) $this->input->server('QUERY_STRING');
		$this->data['message'] = $this->session->flashdata('message');
		$this->data['can_apontamento_editar'] = $this->ion_auth->has_permissao("chamados", "apontamento_editar");
		$this->data['can_apontamento_excluir'] = $this->ion_auth->has_permissao("chamados", "apontamento_excluir");

		$this->template->admin_render('admin/chamados/apontamentos', $this->data);
	}

	// Volta para a tela de apontamentos com os filtros que estavam aplicados.
	private function redirect_apontamentos()
	{
		$query = (string) $this->input->post('voltar_query');
		redirect('chamados/apontamentos' . (preg_match('/^[A-Za-z0-9_\-\[\]%=&.]*$/', $query) && $query !== '' ? '?' . $query : ''), 'refresh');
	}

	private function atualizar_plano_acao($id)
	{
		$plano_acao = $this->chamados_model->get_plano_acao("id = " . $id);
		if ($plano_acao->num_rows() > 0) {
			if ($plano_acao->row()->status == "AI")
				$this->chamados_model->atualizar_plano_acao(array("status" => "EA", "inicio_real" => date("Y-m-d H:i:s")), $id);
		}
	}

	public function check_can_apontar()
	{
		$retorno = array();

		$has_apontamento = $this->chamados_model->get_apontamento_ativo();

		if ($has_apontamento->num_rows() > 0) {
			$retorno["erro"] = true;
			$retorno["data"] = $has_apontamento->row()->chamado_id;
		} else {
			$retorno["erro"] = false;
		}

		exit(json_encode($retorno));
	}

	public function get_niveis()
	{
		$niveis = $this->chamados_model->get_niveis_ativos($_POST["id"]);

		$select = array();
		if ($niveis->num_rows() > 0):
			foreach ($niveis->result() as $value) {
				$prdt = array(
					'id' => $value->id,
					'nome' => $value->descricao
				);
				array_push($select, $prdt);
			}
		else:
			$select = false;
		endif;

		exit(json_encode(array("select" => $select)));
	}

	public function verificar_checklists_associados()
	{
		// Usaremos a classe Output do CodeIgniter para evitar corrompimento do ob_gzhandler
		$this->output->set_content_type('application/json', 'utf-8');

		// Garantir que é uma requisição AJAX
		if (!$this->input->is_ajax_request()) {
			$this->output->set_status_header(404)
				->set_output(json_encode(['success' => false, 'error' => 'Requisição inválida']));
			return;
		}

		try {
			if (! $this->ion_auth->logged_in()) {
				throw new Exception('Usuário não autenticado');
			}

			$chamado_id = $this->input->post('chamado_id');
			$checklists_ids_json = $this->input->post('checklists_ids');

			if (!$chamado_id || !$checklists_ids_json) {
				throw new Exception('Parâmetros inválidos');
			}

			$checklists_ids = json_decode($checklists_ids_json, true);

			if (!is_array($checklists_ids) || empty($checklists_ids)) {
				throw new Exception('Lista de checklists inválida');
			}

			log_message('error', 'checklists_ids decodificado: ' . print_r($checklists_ids, true));

			// Carregar modelos
			$this->load->model('chamados_checklists_model');
			$this->load->model('checklists_model');

			$associados = array();

			foreach ($checklists_ids as $checklist_id) {
				// USAR O MODEL CORRETAMENTE - buscar no banco
				$checklist_existente = $this->chamados_checklists_model->get_by_original($chamado_id, $checklist_id);

				log_message('error', 'Verificando checklist ID: ' . $checklist_id . ' - Resultado: ' . ($checklist_existente ? 'ENCONTRADO' : 'NÃO ENCONTRADO'));

				if ($checklist_existente) {
					// Buscar nome do checklist original
					$checklist_original = $this->checklists_model->get($checklist_id);

					if ($checklist_original) {
						$associados[] = $checklist_original->nome . ' (#' . $checklist_id . ')';
					} else {
						$associados[] = 'Checklist #' . $checklist_id;
					}
				}
			}

			log_message('error', 'Checklists já associados encontrados: ' . print_r($associados, true));

			// Criar resposta
			$resposta = array(
				'success' => true,
				'associados' => $associados
			);

			// Log da resposta
			log_message('error', 'Resposta JSON: ' . json_encode($resposta));

			// Enviar resposta
			$this->output->set_output(json_encode($resposta));
			return;
		} catch (Exception $e) {
			log_message('error', 'Exceção em verificar_checklists_associados: ' . $e->getMessage());
			$resposta = array(
				'success' => false,
				'error' => utf8_encode($e->getMessage())
			);
			$json = json_encode($resposta);
			$this->output->set_output($json !== false ? $json : '{"success":false,"error":"Erro interno de decodificação no servidor."}');
			return;
		} catch (Throwable $e) {
			log_message('error', 'Erro Fatal em verificar_checklists_associados: ' . $e->getMessage());
			$resposta = array(
				'success' => false,
				'error' => 'Erro interno do servidor: ' . utf8_encode($e->getMessage())
			);
			$json = json_encode($resposta);
			$this->output->set_output($json !== false ? $json : '{"success":false,"error":"Erro interno de decodificação no servidor fatal."}');
			return;
		}
	}

	public function atualizar_etapa_checklist()
	{
		// Configurar cabeçalhos corretamente
		$this->output->set_content_type('application/json');
		$this->output->set_header('Content-Encoding: none');

		// Log para debug
		log_message('error', '=== MÉTODO atualizar_etapa_checklist CHAMADO ===');
		log_message('error', 'POST: ' . json_encode($_POST));
		log_message('error', 'is_ajax_request: ' . ($this->input->is_ajax_request() ? 'SIM' : 'NÃO'));

		if (!$this->input->is_ajax_request()) {
			log_message('error', 'Não é requisição AJAX');
			$this->output->set_status_header(404);
			$this->output->set_output(json_encode(['success' => false, 'message' => 'Requisição inválida']));
			return;
		}

		try {
			if (!$this->ion_auth->logged_in()) {
				throw new Exception('Usuário não autenticado');
			}

			$etapa_id = $this->input->post('etapa_id');
			$data_inicio = $this->input->post('data_inicio');
			$data_prazo = $this->input->post('data_prazo');
			$responsavel = $this->input->post('responsavel');
			$concluida = $this->input->post('concluida');

			log_message('error', 'Dados recebidos: etapa_id=' . $etapa_id . ', data_inicio=' . $data_inicio . ', data_prazo=' . $data_prazo . ', responsavel=' . $responsavel . ', concluida=' . $concluida);

			if (!$etapa_id) {
				throw new Exception('ID da etapa não fornecido');
			}

			$this->load->model('chamados_checklists_etapas_model');
			$this->load->model('chamados_checklists_model');

			// Buscar a etapa atual antes da atualização
			$etapa_atual = $this->chamados_checklists_etapas_model->get($etapa_id);

			if (!$etapa_atual) {
				throw new Exception('Etapa não encontrada');
			}

			// Buscar o chamado_id associado a esta etapa
			$chamado_id = $etapa_atual->chamado_id;

			if (!$chamado_id) {
				throw new Exception('Chamado não encontrado para esta etapa');
			}

			// Buscar o checklist pai para obter o nome
			$checklist_pai = $this->chamados_checklists_model->get($etapa_atual->chamado_checklist_id);

			// VALIDAÇÃO: Se está tentando concluir a etapa, verificar se tem responsável
			if ($concluida == 1) {
				// Verificar se tem responsável (pode vir do POST ou do banco)
				$responsavel_valido = false;

				if (!empty($responsavel)) {
					$responsavel_valido = true;
				} elseif (!empty($etapa_atual->responsavel)) {
					$responsavel_valido = true;
					$responsavel = $etapa_atual->responsavel;
				}

				if (!$responsavel_valido) {
					$this->output->set_output(json_encode(array(
						'success' => false,
						'message' => 'Para concluir uma etapa, é necessário selecionar um responsável.',
						'campo' => 'responsavel'
					)));
					return;
				}
			}

			$dados = array();
			$alteracoes = array();

			// Verificar mudanças em cada campo
			if ($data_inicio !== null && $data_inicio !== '') {
				if ($etapa_atual->data_inicio != $data_inicio) {
					$dados['data_inicio'] = $data_inicio;
					$alteracoes['data_inicio'] = array(
						'antigo' => $etapa_atual->data_inicio ? date('d/m/Y', strtotime($etapa_atual->data_inicio)) : 'não definido',
						'novo' => $data_inicio ? date('d/m/Y', strtotime($data_inicio)) : 'não definido'
					);
				}
			} else {
				if ($etapa_atual->data_inicio !== null) {
					$dados['data_inicio'] = null;
					$alteracoes['data_inicio'] = array(
						'antigo' => date('d/m/Y', strtotime($etapa_atual->data_inicio)),
						'novo' => 'removido'
					);
				}
			}

			if ($data_prazo !== null && $data_prazo !== '') {
				if ($etapa_atual->data_prazo != $data_prazo) {
					$dados['data_prazo'] = $data_prazo;
					$alteracoes['data_prazo'] = array(
						'antigo' => $etapa_atual->data_prazo ? date('d/m/Y', strtotime($etapa_atual->data_prazo)) : 'não definido',
						'novo' => $data_prazo ? date('d/m/Y', strtotime($data_prazo)) : 'não definido'
					);
				}
			} else {
				if ($etapa_atual->data_prazo !== null) {
					$dados['data_prazo'] = null;
					$alteracoes['data_prazo'] = array(
						'antigo' => date('d/m/Y', strtotime($etapa_atual->data_prazo)),
						'novo' => 'removido'
					);
				}
			}

			if ($responsavel !== null && $responsavel !== '') {
				if ($etapa_atual->responsavel != $responsavel) {
					$dados['responsavel'] = $responsavel;

					// Buscar nome do responsável
					$this->load->model('ion_auth_model');
					$responsavel_antigo_nome = $etapa_atual->responsavel ? $this->chamados_model->get_user_name($etapa_atual->responsavel) : 'não definido';
					$responsavel_novo_nome = $responsavel ? $this->chamados_model->get_user_name($responsavel) : 'não definido';

					$alteracoes['responsavel'] = array(
						'antigo' => $responsavel_antigo_nome,
						'novo' => $responsavel_novo_nome
					);
				}
			} else {
				if ($etapa_atual->responsavel !== null) {
					$dados['responsavel'] = null;
					$responsavel_antigo_nome = $this->chamados_model->get_user_name($etapa_atual->responsavel);
					$alteracoes['responsavel'] = array(
						'antigo' => $responsavel_antigo_nome,
						'novo' => 'removido'
					);
				}
			}

			$status_alterado = false;
			if ($concluida !== null) {
				$concluida_int = intval($concluida);
				if ($etapa_atual->concluida != $concluida_int) {
					$dados['concluida'] = $concluida_int;
					$status_alterado = true;

					$alteracoes['status'] = array(
						'antigo' => $etapa_atual->concluida == 1 ? 'Concluído' : 'Pendente',
						'novo' => $concluida_int == 1 ? 'Concluído' : 'Pendente'
					);

					if ($concluida_int == 1) {
						$dados['data_conclusao'] = date('Y-m-d H:i:s');
						$dados['responsavel_conclusao'] = $this->session->userdata('user_id');
					} else {
						$dados['data_conclusao'] = null;
						$dados['responsavel_conclusao'] = null;
					}
				}
			}

			$dados['data_atualizacao'] = date('Y-m-d H:i:s');

			log_message('error', 'Alterações detectadas: ' . json_encode($alteracoes));

			// Se não houve alterações, retorna sucesso sem criar diálogo
			if (empty($dados)) {
				$this->output->set_output(json_encode(array(
					'success' => true,
					'message' => 'Nenhuma alteração detectada',
					'data' => array(
						'concluida' => $etapa_atual->concluida,
						'responsavel_conclusao' => $etapa_atual->responsavel_conclusao
					)
				)));
				return;
			}

			// Atualizar a etapa
			if ($this->chamados_checklists_etapas_model->update($etapa_id, $dados)) {
				log_message('error', 'Update realizado com sucesso');

				// GERAR DIÁLOGO COM AS ALTERAÇÕES
				$this->gerar_dialogo_alteracao_etapa($chamado_id, $etapa_atual, $checklist_pai, $alteracoes, $status_alterado);

				// Buscar os dados atualizados para retornar
				$etapa_atualizada = $this->chamados_checklists_etapas_model->get($etapa_id);

				$this->output->set_output(json_encode(array(
					'success' => true,
					'message' => 'Etapa atualizada com sucesso',
					'data' => array(
						'concluida' => $etapa_atualizada->concluida,
						'responsavel_conclusao' => $etapa_atualizada->responsavel_conclusao
					)
				)));
			} else {
				log_message('error', 'Falha no update');
				throw new Exception('Erro ao atualizar etapa');
			}
		} catch (Exception $e) {
			log_message('error', 'Exceção: ' . $e->getMessage());
			$this->output->set_output(json_encode(array(
				'success' => false,
				'message' => $e->getMessage()
			)));
		}
	}

	/**
	 * Gera um diálogo no chamado com as alterações realizadas na etapa
	 */
	public function atualizar_todas_etapas_checklist()
	{
		$this->output->set_content_type('application/json');
		$this->output->set_header('Content-Encoding: none');

		if (!$this->input->is_ajax_request()) {
			$this->output->set_status_header(404);
			$this->output->set_output(json_encode(['success' => false, 'message' => 'Requisição inválida']));
			return;
		}

		try {
			if (!$this->ion_auth->logged_in()) {
				throw new Exception('Usuário não autenticado');
			}

			$etapas = $this->input->post('etapas');
			if (is_string($etapas)) {
				$etapas = json_decode($etapas, true);
			}

			if (empty($etapas) || !is_array($etapas)) {
				throw new Exception('Nenhuma etapa enviada para atualização');
			}

			$this->load->model('chamados_checklists_etapas_model');
			$this->load->model('chamados_checklists_model');
			$this->load->model('ion_auth_model');

			// Pré-validação: se alguma etapa for concluída, exigir responsável
			foreach ($etapas as $item) {
				$etapa_id = isset($item['etapa_id']) ? $item['etapa_id'] : null;
				if (!$etapa_id) continue;

				$concluida = isset($item['concluida']) ? intval($item['concluida']) : 0;
				$responsavel = isset($item['responsavel']) && $item['responsavel'] !== '' ? $item['responsavel'] : null;

				if ($concluida == 1) {
					$etapa_banco = $this->chamados_checklists_etapas_model->get($etapa_id);
					if (!$etapa_banco) continue;

					$resp_valido = !empty($responsavel) || !empty($etapa_banco->responsavel);
					if (!$resp_valido) {
						$nome_etapa = !empty($etapa_banco->nome) ? "\"{$etapa_banco->nome}\"" : "ID {$etapa_id}";
						$this->output->set_output(json_encode(array(
							'success' => false,
							'message' => "Para concluir a etapa {$nome_etapa}, é necessário selecionar um responsável.",
							'etapa_id' => $etapa_id,
							'campo' => 'responsavel'
						)));
						return;
					}
				}
			}

			$total_atualizadas = 0;
			$etapas_atualizadas = array();

			foreach ($etapas as $item) {
				$etapa_id = isset($item['etapa_id']) ? $item['etapa_id'] : null;
				if (!$etapa_id) continue;

				$etapa_atual = $this->chamados_checklists_etapas_model->get($etapa_id);
				if (!$etapa_atual) continue;

				$chamado_id = $etapa_atual->chamado_id;
				$checklist_pai = $this->chamados_checklists_model->get($etapa_atual->chamado_checklist_id);

				$data_inicio = isset($item['data_inicio']) ? $item['data_inicio'] : null;
				$data_prazo = isset($item['data_prazo']) ? $item['data_prazo'] : null;
				$responsavel = isset($item['responsavel']) && $item['responsavel'] !== '' ? $item['responsavel'] : null;
				$concluida = isset($item['concluida']) ? intval($item['concluida']) : null;

				$dados = array();
				$alteracoes = array();

				if ($data_inicio !== null && $data_inicio !== '') {
					if ($etapa_atual->data_inicio != $data_inicio) {
						$dados['data_inicio'] = $data_inicio;
						$alteracoes['data_inicio'] = array(
							'antigo' => $etapa_atual->data_inicio ? date('d/m/Y', strtotime($etapa_atual->data_inicio)) : 'não definido',
							'novo' => $data_inicio ? date('d/m/Y', strtotime($data_inicio)) : 'não definido'
						);
					}
				} else {
					if ($etapa_atual->data_inicio !== null) {
						$dados['data_inicio'] = null;
						$alteracoes['data_inicio'] = array(
							'antigo' => date('d/m/Y', strtotime($etapa_atual->data_inicio)),
							'novo' => 'removido'
						);
					}
				}

				if ($data_prazo !== null && $data_prazo !== '') {
					if ($etapa_atual->data_prazo != $data_prazo) {
						$dados['data_prazo'] = $data_prazo;
						$alteracoes['data_prazo'] = array(
							'antigo' => $etapa_atual->data_prazo ? date('d/m/Y', strtotime($etapa_atual->data_prazo)) : 'não definido',
							'novo' => $data_prazo ? date('d/m/Y', strtotime($data_prazo)) : 'não definido'
						);
					}
				} else {
					if ($etapa_atual->data_prazo !== null) {
						$dados['data_prazo'] = null;
						$alteracoes['data_prazo'] = array(
							'antigo' => date('d/m/Y', strtotime($etapa_atual->data_prazo)),
							'novo' => 'removido'
						);
					}
				}

				if ($responsavel !== null && $responsavel !== '') {
					if ($etapa_atual->responsavel != $responsavel) {
						$dados['responsavel'] = $responsavel;
						$responsavel_antigo_nome = $etapa_atual->responsavel ? $this->chamados_model->get_user_name($etapa_atual->responsavel) : 'não definido';
						$responsavel_novo_nome = $responsavel ? $this->chamados_model->get_user_name($responsavel) : 'não definido';
						$alteracoes['responsavel'] = array(
							'antigo' => $responsavel_antigo_nome,
							'novo' => $responsavel_novo_nome
						);
					}
				} else {
					if ($etapa_atual->responsavel !== null) {
						$dados['responsavel'] = null;
						$responsavel_antigo_nome = $this->chamados_model->get_user_name($etapa_atual->responsavel);
						$alteracoes['responsavel'] = array(
							'antigo' => $responsavel_antigo_nome,
							'novo' => 'removido'
						);
					}
				}

				$status_alterado = false;
				if ($concluida !== null) {
					$concluida_int = intval($concluida);
					if ($etapa_atual->concluida != $concluida_int) {
						$dados['concluida'] = $concluida_int;
						$status_alterado = true;
						$alteracoes['status'] = array(
							'antigo' => $etapa_atual->concluida == 1 ? 'Concluído' : 'Pendente',
							'novo' => $concluida_int == 1 ? 'Concluído' : 'Pendente'
						);

						if ($concluida_int == 1) {
							$dados['data_conclusao'] = date('Y-m-d H:i:s');
							$dados['responsavel_conclusao'] = $this->session->userdata('user_id');
						} else {
							$dados['data_conclusao'] = null;
							$dados['responsavel_conclusao'] = null;
						}
					}
				}

				if (!empty($dados)) {
					$dados['data_atualizacao'] = date('Y-m-d H:i:s');
					if ($this->chamados_checklists_etapas_model->update($etapa_id, $dados)) {
						$this->gerar_dialogo_alteracao_etapa($chamado_id, $etapa_atual, $checklist_pai, $alteracoes, $status_alterado);
						$total_atualizadas++;
						$etapas_atualizadas[] = array(
							'etapa_id' => $etapa_id,
							'concluida' => isset($dados['concluida']) ? $dados['concluida'] : $etapa_atual->concluida,
							'data_prazo' => isset($dados['data_prazo']) ? $dados['data_prazo'] : $etapa_atual->data_prazo
						);
					}
				}
			}

			$msg = $total_atualizadas > 0 
				? ($total_atualizadas == 1 ? '1 etapa atualizada com sucesso!' : "{$total_atualizadas} etapas atualizadas com sucesso!")
				: 'Nenhuma alteração detectada nas etapas.';

			$this->output->set_output(json_encode(array(
				'success' => true,
				'message' => $msg,
				'total_atualizadas' => $total_atualizadas,
				'etapas_atualizadas' => $etapas_atualizadas
			)));
		} catch (Exception $e) {
			log_message('error', 'Exceção em atualizar_todas_etapas_checklist: ' . $e->getMessage());
			$this->output->set_output(json_encode(array(
				'success' => false,
				'message' => $e->getMessage()
			)));
		}
	}

	/**
	 * Gera um diálogo no chamado com as alterações realizadas na etapa
	 */
	
	private function gerar_dialogo_alteracao_etapa($chamado_id, $etapa_atual, $checklist_pai, $alteracoes, $status_alterado = false)
	{
		// Nome da etapa
		$nome_etapa = $etapa_atual->nome;

		// Nome do checklist
		$nome_checklist = $checklist_pai ? $checklist_pai->nome : 'Checklist';

		// Usuário que fez a alteração
		$usuario = $this->chamados_model->get_user_name($this->session->userdata('user_id'));

		// Construir mensagem do diálogo
		$mensagem = "Alterações realizadas na etapa \"{$nome_etapa}\" do checklist \"{$nome_checklist}\" por {$usuario}:\n\n";

		$alteracoes_lista = array();

		foreach ($alteracoes as $campo => $valores) {
			switch ($campo) {
				case 'data_inicio':
					$alteracoes_lista[] = "• Data de início: {$valores['antigo']} alterado para {$valores['novo']}";
					break;
				case 'data_prazo':
					$alteracoes_lista[] = "• Data de prazo: {$valores['antigo']} alterado para {$valores['novo']}";
					break;
				case 'responsavel':
					$alteracoes_lista[] = "• Responsável: {$valores['antigo']} alterado para {$valores['novo']}";
					break;
				case 'status':
					if ($valores['novo'] == 'Concluído') {
						$alteracoes_lista[] = "• Etapa concluída!";
					} else {
						$alteracoes_lista[] = "• Etapa reaberta (pendente)";
					}
					break;
			}
		}

		if (!empty($alteracoes_lista)) {
			$mensagem .= implode("\n", $alteracoes_lista);
		} else {
			$mensagem .= "Alterações realizadas na etapa.";
		}

		// Insere diálogo no banco
		$dados_dialogo = array(
			'chamado_id' => $chamado_id,
			'responsavel_id' => $this->session->userdata('user_id'),
			'descricao' => $mensagem,
			'data_envio' => date('Y-m-d H:i:s'),
			'interno' => 0
		);

		$this->chamados_model->create_dialogo($dados_dialogo);

		log_message('error', 'Diálogo de alteração criado para chamado ID: ' . $chamado_id);
	}

	public function excluir_etapa_personalizada()
	{
		// Configurar cabeçalhos corretamente ANTES de qualquer saída
		$this->output->set_content_type('application/json');
		$this->output->set_header('Content-Encoding: none');
		$this->output->set_header('Cache-Control: no-cache, must-revalidate');

		// Log para debug
		log_message('error', '=== MÉTODO excluir_etapa_personalizada CHAMADO ===');
		log_message('error', 'POST: ' . json_encode($_POST));

		// Desabilitar compressão para este método
		if (function_exists('apache_setenv')) {
			@apache_setenv('no-gzip', '1');
		}
		@ini_set('zlib.output_compression', 'Off');

		try {
			if (!$this->input->is_ajax_request()) {
				$this->output->set_output(json_encode(['success' => false, 'message' => 'Requisição inválida']));
				return;
			}

			if (!$this->ion_auth->logged_in()) {
				$this->output->set_output(json_encode(['success' => false, 'message' => 'Usuário não autenticado']));
				return;
			}

			$etapa_id = $this->input->post('etapa_id');

			log_message('error', 'etapa_id recebido: ' . $etapa_id);

			if (!$etapa_id) {
				$this->output->set_output(json_encode(['success' => false, 'message' => 'ID da etapa não fornecido']));
				return;
			}

			$this->load->model('chamados_checklists_etapas_model');
			$this->load->model('chamados_checklists_model');

			// Buscar a etapa antes de excluir
			$etapa = $this->chamados_checklists_etapas_model->get($etapa_id);

			if (!$etapa) {
				$this->output->set_output(json_encode(['success' => false, 'message' => 'Etapa não encontrada']));
				return;
			}

			// Verificar se é uma etapa personalizada (etapa_original_id == 0)
			if ($etapa->etapa_original_id != 0) {
				$this->output->set_output(json_encode(['success' => false, 'message' => 'Apenas etapas personalizadas podem ser excluídas']));
				return;
			}

			$chamado_id = $etapa->chamado_id;

			// Buscar o checklist pai
			$checklist_pai = $this->chamados_checklists_model->get($etapa->chamado_checklist_id);
			$nome_checklist = $checklist_pai ? $checklist_pai->nome : 'Checklist';
			$nome_etapa = $etapa->nome;

			// Usuário que está excluindo
			$usuario = $this->chamados_model->get_user_name($this->session->userdata('user_id'));

			// Excluir a etapa
			$excluido = $this->chamados_checklists_etapas_model->delete($etapa_id);

			if ($excluido) {
				// Gerar diálogo informando a exclusão
				$mensagem = "Etapa personalizada \"{$nome_etapa}\" do checklist \"{$nome_checklist}\" foi excluída por {$usuario}.";

				$dados_dialogo = array(
					'chamado_id' => $chamado_id,
					'responsavel_id' => $this->session->userdata('user_id'),
					'descricao' => $mensagem,
					'data_envio' => date('Y-m-d H:i:s'),
					'interno' => 0
				);

				$this->chamados_model->create_dialogo($dados_dialogo);

				$this->output->set_output(json_encode(array(
					'success' => true,
					'message' => 'Etapa excluída com sucesso'
				)));
			} else {
				$this->output->set_output(json_encode(array(
					'success' => false,
					'message' => 'Erro ao excluir etapa'
				)));
			}
		} catch (Exception $e) {
			log_message('error', 'Exceção em excluir_etapa_personalizada: ' . $e->getMessage());
			$this->output->set_output(json_encode(array(
				'success' => false,
				'message' => $e->getMessage()
			)));
		}
	}

	public function adicionar_etapa_checklist_existente()
	{
		// Configurar cabeçalhos corretamente
		$this->output->set_content_type('application/json');
		$this->output->set_header('Content-Encoding: none');

		// Log para debug
		log_message('error', '=== MÉTODO adicionar_etapa_checklist_existente CHAMADO ===');
		log_message('error', 'POST: ' . json_encode($_POST));

		if (!$this->input->is_ajax_request()) {
			$this->output->set_output(json_encode(['success' => false, 'error' => 'Requisição inválida']));
			return;
		}

		try {
			if (!$this->ion_auth->logged_in()) {
				throw new Exception('Usuário não autenticado');
			}

			if (!$this->ion_auth->has_permissao("chamados", "associar_checklist")) {
				throw new Exception('Sem permissão para adicionar etapas');
			}

			$chamado_id = $this->input->post('chamado_id');
			$chamado_checklist_id = $this->input->post('chamado_checklist_id');
			$nome = trim($this->input->post('nome'));
			$descricao = trim($this->input->post('descricao'));
			$prazo_padrao = intval($this->input->post('prazo_padrao'));
			$data_inicio = $this->input->post('data_inicio');
			$data_prazo = $this->input->post('data_prazo');

			if (!$chamado_id || !$chamado_checklist_id || !$nome) {
				throw new Exception('Parâmetros inválidos: ID do chamado, ID do checklist e nome da etapa são obrigatórios');
			}

			// Carregar modelos
			$this->load->model('chamados_checklists_model');
			$this->load->model('chamados_checklists_etapas_model');

			// Verificar se o checklist existe e pertence ao chamado
			$checklist = $this->chamados_checklists_model->get($chamado_checklist_id);

			if (!$checklist) {
				throw new Exception('Checklist não encontrado');
			}

			if ($checklist->chamado_id != $chamado_id) {
				throw new Exception('Este checklist não pertence ao chamado informado');
			}

			// Calcular a próxima ordem para a etapa
			$etapas_existentes = $this->chamados_checklists_etapas_model->get_by_chamado_checklist($chamado_id, $chamado_checklist_id);
			$proxima_ordem = count($etapas_existentes);

			// Se não foi fornecida data de início, usar a data atual
			if (empty($data_inicio)) {
				$data_inicio = date('Y-m-d');
			}

			// Se prazo padrão > 0 e não foi fornecida data prazo, calcular
			if ($prazo_padrao > 0 && empty($data_prazo)) {
				$data_prazo = $this->calcular_data_com_weekend($data_inicio, $prazo_padrao);
			}

			// Inserir a nova etapa
			$etapa_data = array(
				'chamado_id' => $chamado_id,
				'chamado_checklist_id' => $chamado_checklist_id,
				'etapa_original_id' => 0, // 0 indica que é uma etapa personalizada
				'nome' => $nome,
				'descricao' => $descricao,
				'ordem' => $proxima_ordem,
				'data_inicio' => $data_inicio,
				'data_prazo' => $data_prazo ? $data_prazo : null,
				'concluida' => 0,
				'status' => 1,
				'data_criacao' => date('Y-m-d H:i:s')
			);

			$etapa_id = $this->chamados_checklists_etapas_model->insert($etapa_data);

			if (!$etapa_id) {
				throw new Exception('Erro ao inserir etapa no banco de dados');
			}

			// Gerar diálogo no chamado informando a adição da etapa
			$usuario = $this->chamados_model->get_user_name($this->session->userdata('user_id'));

			$mensagem = "Nova etapa personalizada adicionada\n";
			$mensagem .= "• Adicionada por: {$usuario}\n\n";
			$mensagem .= "• Checklist: {$checklist->nome}\n";
			$mensagem .= "• Etapa: {$nome}\n";

			if ($descricao) {
				$mensagem .= "• Descrição: {$descricao}\n";
			}

			if ($data_inicio) {
				$mensagem .= "• Data de início: " . date('d/m/Y', strtotime($data_inicio)) . "\n";
			}

			if ($prazo_padrao > 0) {
				$mensagem .= "• Prazo padrão: {$prazo_padrao} dia(s)\n";
				if ($data_prazo) {
					$mensagem .= "• Data de prazo calculada: " . date('d/m/Y', strtotime($data_prazo)) . "\n";
				}
			} elseif ($data_prazo) {
				$mensagem .= "• Data de prazo: " . date('d/m/Y', strtotime($data_prazo)) . "\n";
			}

			$dados_dialogo = array(
				'chamado_id' => $chamado_id,
				'responsavel_id' => $this->session->userdata('user_id'),
				'descricao' => $mensagem,
				'data_envio' => date('Y-m-d H:i:s'),
				'interno' => 0
			);

			$this->chamados_model->create_dialogo($dados_dialogo);

			$this->output->set_output(json_encode(array(
				'success' => true,
				'message' => 'Etapa adicionada com sucesso!',
				'etapa_id' => $etapa_id
			)));
		} catch (Exception $e) {
			log_message('error', 'Exceção em adicionar_etapa_checklist_existente: ' . $e->getMessage());
			$this->output->set_output(json_encode(array(
				'success' => false,
				'error' => $e->getMessage()
			)));
		}
	}

	public function obter_ultima_etapa_checklist()
	{
		// Configurar cabeçalhos corretamente
		$this->output->set_content_type('application/json');
		$this->output->set_header('Content-Encoding: none');

		if (!$this->input->is_ajax_request()) {
			$this->output->set_output(json_encode(['success' => false, 'error' => 'Requisição inválida']));
			return;
		}

		try {
			if (!$this->ion_auth->logged_in()) {
				throw new Exception('Usuário não autenticado');
			}

			$chamado_id = $this->input->post('chamado_id');
			$checklist_id = $this->input->post('checklist_id');

			if (!$chamado_id || !$checklist_id) {
				throw new Exception('Parâmetros inválidos');
			}

			$this->load->model('chamados_checklists_etapas_model');

			// Buscar a última etapa do checklist (maior ordem)
			$this->db->select('*');
			$this->db->from('chamados_checklists_etapas');
			$this->db->where('chamado_id', $chamado_id);
			$this->db->where('chamado_checklist_id', $checklist_id);
			$this->db->order_by('ordem', 'DESC');
			$this->db->limit(1);

			$query = $this->db->get();

			$tem_etapas = ($query->num_rows() > 0);
			$ultima_etapa = null;

			if ($tem_etapas) {
				$ultima_etapa = $query->row();
			}

			$this->output->set_output(json_encode([
				'success' => true,
				'tem_etapas' => $tem_etapas,
				'ultima_etapa' => $ultima_etapa
			]));
		} catch (Exception $e) {
			log_message('error', 'Exceção em obter_ultima_etapa_checklist: ' . $e->getMessage());
			$this->output->set_output(json_encode([
				'success' => false,
				'error' => $e->getMessage()
			]));
		}
	}

	/**
	 * Retorna JSON com os chamados pendentes (status Aberto = 1) do usuário logado
	 */
	public function ajax_pendentes()
	{
		if (!$this->ion_auth->logged_in()) {
			$this->output
				->set_status_header(401)
				->set_content_type('application/json')
				->set_output(json_encode(array('status' => 'error', 'message' => 'Não autenticado')));
			return;
		}

		$user_id = $this->session->userdata('user_id');
		$empresa_id = $this->session->userdata('empresa_id');

		$this->load->model('chamados_model');
		$chamados = $this->chamados_model->get_chamados_pendentes_user($user_id, $empresa_id);

		$formatted = array();
		foreach ($chamados as $c) {
			$descricao_limpa = trim(strip_tags($c->descricao));
			if (mb_strlen($descricao_limpa, 'UTF-8') > 110) {
				$descricao_curta = mb_substr($descricao_limpa, 0, 110, 'UTF-8') . '...';
			} else {
				$descricao_curta = $descricao_limpa ?: ($c->titulo ?: 'Sem descrição informada');
			}

			$formatted[] = array(
				'id' => (int) $c->id,
				'protocolo' => $c->protocolo,
				'titulo' => htmlspecialchars($c->titulo, ENT_QUOTES, 'UTF-8'),
				'descricao' => htmlspecialchars($descricao_curta, ENT_QUOTES, 'UTF-8'),
				'cliente' => $c->cliente ? htmlspecialchars($c->cliente, ENT_QUOTES, 'UTF-8') : '',
				'prioridade' => $c->prioridade_desc ?: 'Normal',
				'created' => $c->created ? date('d/m/Y H:i', strtotime($c->created)) : '',
				'prazo' => $c->data_prazo ? date('d/m/Y H:i', strtotime($c->data_prazo)) : null,
				'atrasado' => ($c->data_prazo && strtotime($c->data_prazo) < time()),
				'url' => site_url('chamados/details/' . $c->id)
			);
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array(
				'status' => 'success',
				'count' => count($formatted),
				'chamados' => $formatted
			)));
	}
}
