<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unidades extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/unidades');
        $this->load->model('unidades_model');
        $this->load->library('Curl');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_unidades'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_unidades'), 'admin/unidades');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("unidades", "index"))
        {
            redirect('auth/login', 'refresh');
        }

        $empresa_id = (int) $this->session->userdata('empresa_id');
        $this->data['indicadores'] = $this->db->query("SELECT COUNT(*) AS total,
                SUM(u.actived = 's') AS ativas,
                SUM(u.actived <> 's') AS inativas,
                SUM(u.actived = 's' AND EXISTS (SELECT 1 FROM habilitacoes_produto hp WHERE hp.id_cliente = u.id AND hp.status = 'S' AND hp.periodo_fim >= CURDATE())) AS com_produto
            FROM unidades u WHERE u.empresa_id = ?", array($empresa_id))->row();
        $this->data['ufs'] = $this->db->distinct()->select('uf')->where('empresa_id', $empresa_id)->where('uf !=', '')->where('uf IS NOT NULL', null, false)->order_by('uf')->get('unidades')->result();
        $this->data['caracteristicas'] = $this->unidades_model->get_caracteristicas()->result();
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['can_create'] = $this->ion_auth->has_permissao("unidades", "create");

        $this->template->admin_render('admin/unidades/index', $this->data);
	}

	public function server_side()
	{
        $this->output->set_content_type('application/json');
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao('unidades', 'index')) {
            $this->output->set_status_header(403)->set_output(json_encode(array('data' => array(), 'recordsTotal' => 0, 'recordsFiltered' => 0)));
            return;
        }

        $empresa_id = (int) $this->session->userdata('empresa_id');
        $status = (string) $this->input->post('status');
        $tipo = (string) $this->input->post('tipo');
        $filtros = array(
            'q' => trim((string) $this->input->post('q')),
            'status' => in_array($status, array('s', 'n'), true) ? $status : '',
            'tipo' => in_array($tipo, array('J', 'F'), true) ? $tipo : '',
            'uf' => preg_replace('/[^A-Z]/', '', strtoupper((string) $this->input->post('uf'))),
            'caracteristica' => (int) $this->input->post('caracteristica'),
        );
        $aplicar = function ($db, $filtros) use ($empresa_id) {
            $db->from('unidades u')->where('u.empresa_id', $empresa_id);
            if ($filtros['q'] !== '') {
                $digitos = preg_replace('/\D/', '', $filtros['q']);
                $db->group_start()->like('u.unidade', $filtros['q'])->or_like('u.razao_social', $filtros['q'])->or_like('u.gestor', $filtros['q'])->or_like('u.email', $filtros['q'])->or_like('u.cidade', $filtros['q']);
                if ($digitos !== '') {
                    $digitos = $db->escape_like_str($digitos);
                    $db->or_where("REPLACE(REPLACE(REPLACE(u.cnpj, '.', ''), '/', ''), '-', '') LIKE '%" . $digitos . "%' ESCAPE '!'", null, false);
                    $db->or_where("REPLACE(REPLACE(u.cpf, '.', ''), '-', '') LIKE '%" . $digitos . "%' ESCAPE '!'", null, false);
                }
                $db->group_end();
            }
            if ($filtros['status'] === 's') { $db->where('u.actived', 's'); }
            if ($filtros['status'] === 'n') { $db->where("(u.actived IS NULL OR u.actived <> 's')", null, false); }
            if ($filtros['tipo'] !== '') { $db->where('u.tipo', $filtros['tipo']); }
            if ($filtros['uf'] !== '') { $db->where('u.uf', $filtros['uf']); }
            if ($filtros['caracteristica'] > 0) { $db->where('u.caracteristica', $filtros['caracteristica']); }
        };

        $ordem = $this->input->post('order');
        $direcao = isset($ordem[0]['dir']) && $ordem[0]['dir'] === 'desc' ? 'DESC' : 'ASC';
        $colunas = array(0 => 'u.unidade', 2 => 'u.cidade');
        $coluna = isset($ordem[0]['column']) && isset($colunas[(int) $ordem[0]['column']]) ? $colunas[(int) $ordem[0]['column']] : 'u.unidade';
        $inicio = max(0, (int) $this->input->post('start'));
        $quantidade = (int) $this->input->post('length');
        if ($quantidade <= 0 || $quantidade > 100) {
            $quantidade = 25;
        }

        $this->db->select("u.id, u.unidade, u.razao_social, u.cnpj, u.cpf, u.tipo, u.gestor, u.telefone1, u.email, u.cidade, u.uf, u.actived,
            (SELECT COUNT(*) FROM habilitacoes_produto hp WHERE hp.id_cliente = u.id AND hp.status = 'S' AND hp.periodo_fim >= CURDATE()) AS produtos", false);
        $aplicar($this->db, $filtros);
        $this->db->order_by($coluna, $direcao)->limit($quantidade, $inicio);
        $unidades = $this->db->get()->result();

        $acoes_extras = array(
            'recursos_ws' => array('fa-server', 'Recursos Webservice'),
            'facebook_api' => array('fa-facebook-square', 'Facebook API'),
            'rd_station' => array('fa-registered', 'RD Station'),
            'parametros_contrato' => array('fa-cogs', 'Parâmetros de contrato'),
            'iugu' => array('fa-credit-card', 'Iugu'),
            'cursos' => array('fa-book', 'Cursos'),
        );
        $permitidas = array();
        foreach ($acoes_extras as $metodo => $acao) {
            if ($this->ion_auth->has_permissao('unidades', $metodo)) {
                $permitidas[$metodo] = $acao;
            }
        }
        $pode = array(
            'edit' => $this->ion_auth->has_permissao('unidades', 'edit'),
            'activate' => $this->ion_auth->has_permissao('unidades', 'activate'),
            'deactivate' => $this->ion_auth->has_permissao('unidades', 'deactivate'),
        );

        $linhas = array();
        foreach ($unidades as $unidade) {
            $ativa = $unidade->actived === 's';
            $documento = $unidade->cnpj != '' ? $unidade->cnpj : $unidade->cpf;
            $detalhes = array();
            if ($unidade->razao_social != '' && $unidade->razao_social !== $unidade->unidade) { $detalhes[] = html_escape($unidade->razao_social); }
            if ($documento != '') { $detalhes[] = html_escape($documento); }
            $contato = array();
            if ($unidade->gestor != '') { $contato[] = '<div>' . html_escape($unidade->gestor) . '</div>'; }
            $linha_contato = array();
            if ($unidade->telefone1 != '') { $linha_contato[] = html_escape($unidade->telefone1); }
            if ($unidade->email != '') { $linha_contato[] = html_escape($unidade->email); }
            if ($linha_contato) { $contato[] = '<div class="lista-detalhe">' . implode(' · ', $linha_contato) . '</div>'; }

            $botoes = '';
            if ($pode['edit']) {
                $botoes .= '<a href="' . base_url('unidades/edit/' . (int) $unidade->id) . '" class="btn btn-default btn-sm"><i class="fa fa-pencil"></i> Editar</a> ';
            }
            $menu = '';
            foreach ($permitidas as $metodo => $acao) {
                $menu .= '<li><a href="' . base_url('unidades/' . $metodo . '/' . (int) $unidade->id) . '"><i class="fa ' . $acao[0] . '"></i> ' . html_escape($acao[1]) . '</a></li>';
            }
            if ($ativa && $pode['deactivate']) {
                $menu .= ($menu ? '<li class="divider"></li>' : '') . '<li><a href="' . base_url('unidades/deactivate/' . (int) $unidade->id) . '" class="js-confirmar" data-confirmar="Inativar a filial &quot;' . html_escape($unidade->unidade) . '&quot;?"><i class="fa fa-ban"></i> Inativar</a></li>';
            } elseif (!$ativa && $pode['activate']) {
                $menu .= ($menu ? '<li class="divider"></li>' : '') . '<li><a href="' . base_url('unidades/activate/' . (int) $unidade->id) . '"><i class="fa fa-check"></i> Ativar</a></li>';
            }
            if ($menu) {
                $botoes .= '<div class="btn-group"><button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Mais ações"><i class="fa fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-right">' . $menu . '</ul></div>';
            }

            $linhas[] = array(
                '<div class="lista-nome">' . html_escape($unidade->unidade) . ($unidade->tipo === 'F' ? ' <span class="lista-chip">Pessoa física</span>' : '') . '</div>' . ($detalhes ? '<div class="lista-detalhe">' . implode(' · ', $detalhes) . '</div>' : ''),
                $contato ? implode('', $contato) : '<span class="text-muted">—</span>',
                $unidade->cidade != '' ? html_escape($unidade->cidade) . ($unidade->uf != '' ? ' / ' . html_escape($unidade->uf) : '') : '<span class="text-muted">—</span>',
                (int) $unidade->produtos > 0 ? (int) $unidade->produtos . ' vigente' . ((int) $unidade->produtos === 1 ? '' : 's') : '<span class="text-muted">Nenhum</span>',
                $ativa ? '<span class="label label-success">Ativa</span>' : '<span class="label label-default">Inativa</span>',
                $botoes,
                'DT_RowClass' => $ativa ? 'is-ativo' : 'is-inativo',
            );
        }

        $this->db->reset_query();
        $aplicar($this->db, array('q' => '', 'status' => '', 'tipo' => '', 'uf' => '', 'caracteristica' => 0));
        $total = (int) $this->db->count_all_results();
        $aplicar($this->db, $filtros);
        $filtrados = (int) $this->db->count_all_results();

        $this->output->set_output(json_encode(array(
            'draw' => (int) $this->input->post('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtrados,
            'data' => $linhas,
        )));
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("unidades", "create"))
		{
			redirect('auth', 'refresh');
		}
        $this->formulario(null);
	}

	public function recursos_ws($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "recursos_ws"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_recursos_ws'), 'unidades/recursos_ws');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		$unidade = $this->unidades_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		/* Validate form input */
		if($this->input->post('token') && $unidade->token != $this->input->post('token')) {
			$this->form_validation->set_rules('token', 'lang:create_validation_token_label', 'required|is_unique[unidades.token]');
		} else {
			$this->form_validation->set_rules('token', 'lang:create_validation_token_label', 'required');
		}

		$dados["token"] = $this->input->post('token');
		$dados["recursos"] = serialize($this->input->post('recursos'));
		$dados["recursos_cliente"] = serialize($this->input->post('tab'));

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$unidade_update = $this->unidades_model->update($id, $dados);

				if ($unidade_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_unidade_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->unidades_model->errors()));
				}

				redirect('unidades', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->unidades_model->errors() ? show_alert_error($this->unidades_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		$this->data['token'] = array(
			'name'  => 'token',
			'id'    => 'token',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('token', $unidade->token),
			'readonly' => true
		);
		$this->data['recursos_ws'] = get_recursos_ws(unserialize($unidade->recursos));

		$inputs = array();
        $grupos = $this->unidades_model->get_groups(null, $unidade->empresa_id);
        $tabelas = get_tabelas_cliente_ws();
        $recursos_cliente = unserialize($unidade->recursos_cliente);
        foreach ($tabelas as $tab) {
            $inputs[] = array(
                'name'  => 'tab['.$tab.'][]',
                'id'    => $tab,
                'class' => 'multipleSelect',
                'multiple' => 'multiple',
                'options' => $grupos,
                'selected' => @$recursos_cliente[$tab]
            );
        }
        $this->data['inputs'] = $inputs;
		// echo "<pre>".print_r($inputs, true)."</pre>";
		// exit;
		$this->data['btn_token'] = array(
			'id'    => 'token',
			'content'    => 'Gerar',
			'type'  => 'button',
            'class' => 'btn btn-primary btn-flat',
			'onclick' => 'gerarToken();',
			'style' => 'margin-top:5px;'
		);

        /* Load Template */
        $this->template->admin_render('admin/unidades/recursos_ws', $this->data);
	}

	public function facebook_api($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "facebook_api"))
		{
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('app_id', 'lang:create_validation_app_id_label', 'required');
		$this->form_validation->set_rules('app_secret', 'lang:create_validation_app_secret_label', 'required');
		$this->form_validation->set_rules('access_token', 'lang:create_validation_access_token_label', 'required');
		$this->form_validation->set_rules('page_id', 'lang:create_validation_page_id_label', 'required');
		$this->form_validation->set_rules('midia', 'lang:create_validation_midia_label', 'required');
		$this->form_validation->set_rules('evento', 'lang:create_validation_evento_label', 'required');

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_facebook_api'), 'unidades/facebook_api');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		$unidade = $this->unidades_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		$dados["fb_app_id"] = $this->input->post('app_id');
		$dados["fb_app_secret"] = $this->input->post('app_secret');
		$dados["fb_access_token"] = $this->input->post('access_token');
		$dados["fb_page_id"] = $this->input->post('page_id');
		$dados["fb_page_name"] = $this->input->post('page_name');
		//$dados["fb_expires_at"] = date('Y/m/d H:i:s', strtotime("+".$this->input->post('expires_at')." seconds"));
		$dados["fb_expires_at"] = $this->input->post('expires_at');
		$dados["fb_status_api"] = $this->input->post('status');
		$dados["fb_midia_id"] = $this->input->post('midia');
		$dados["fb_evento_id"] = $this->input->post('evento');

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$fluxo = true;

				if($unidade->fb_access_token == "") {
					$extender_token = $this->unidades_model->extender_token($this->input->post('app_id'), $this->input->post('app_secret'));
					// exit("<pre>".print_r($extender_token,true)."</pre>");
					if($extender_token["erro"] == false) {
						$dados["fb_access_token"] = $extender_token["token"];
						$dados["fb_expires_at"] = $extender_token["expires_at"];
					} else {
						$this->data['message'] = show_alert_error($extender_token["msg"]);
						$fluxo = false;
					}
				}

				if($fluxo == true) {
					$unidade_update = $this->unidades_model->update($id, $dados);

					if ($unidade_update)
					{
						$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_unidade_saved')));
					}
					else
					{
						$this->session->set_flashdata('message', show_alert_error($this->unidades_model->errors()));
					}
					redirect('unidades', 'refresh');
				}
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->unidades_model->errors() ? show_alert_error($this->unidades_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		$this->data['id'] = $id;
		$this->data['status'] = $unidade->fb_status_api;
		$this->data['page_id'] = ($this->input->post('page_id')) ? $this->input->post('page_id') : $unidade->fb_page_id;
		$this->data['page_name'] = ($this->input->post('page_name')) ? $this->input->post('page_name') : $unidade->fb_page_name;

		$this->data['app_id'] = array(
			'name'  => 'app_id',
			'id'    => 'app_id',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('app_id', $unidade->fb_app_id)
		);
		$this->data['app_secret'] = array(
			'name'  => 'app_secret',
			'id'    => 'app_secret',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('app_secret', $unidade->fb_app_secret)
		);
		$this->data['access_token'] = array(
			'name'  => 'access_token',
			'id'    => 'access_token',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('access_token', $unidade->fb_access_token),
			'readonly' => true
		);
		$array_midias = array(""=>"Selecione");
		foreach($this->unidades_model->get_midias()->result() as $midia) {
			$array_midias[$midia->id] = $midia->descricao;
		}
		$this->data['midia'] = array(
			'name'  => 'midia',
			'id'    => 'midia',
            'onchange' => "loadEventos(this.value, ".$id.",'".base_url('unidades/')."')",
            'class' => 'form-control',
			'options' => $array_midias,
			'selected' => $this->form_validation->set_value('midia', $unidade->fb_midia_id)
		);

		$array_eventos = array(""=>"Selecione");
		if($unidade->fb_midia_id != "") {
			foreach($this->unidades_model->get_eventos($unidade->fb_midia_id, $id)->result() as $evento) {
				$array_eventos[$evento->id] = $evento->descricao;
			}
		}
		$this->data['evento'] = array(
			'name'  => 'evento',
			'id'    => 'evento',
            'class' => 'form-control',
			'options' => $array_eventos,
			'selected' => $this->form_validation->set_value('evento', $unidade->fb_evento_id)
		);
		$this->data['btn_access_token'] = array(
			'id' => 'btn_access_token',
			'content' => '<i class="fa fa-facebook-square" aria-hidden="true"></i> Continuar com Facebook',
			'type' => 'button',
            'class' => 'btn btn-primary btn-flat btn-facebook',
			'onclick' => 'gerarFbAccessToken();',
			'style' => 'margin-top:5px;'
		);

        /* Load Template */
        $this->template->admin_render('admin/unidades/facebook_api', $this->data);
	}

	public function has_fb_access_token() {
		$post = $_POST;
		$retorno = array();
		$exist = $this->unidades_model->get(array('select'=>'unidades.fb_access_token, unidades.fb_expires_at', 'where'=>'fb_app_id = "'.$post['app_id'].'" AND fb_expires_at > "'.date('Y-m-d H:i:s').'"', 'order_by'=>array('unidades.id', 'desc')));
        if($exist->num_rows() > 0)
        {
            $retorno["erro"] = false;
            $retorno['token'] = $exist->row()->fb_access_token;
            $retorno['expires_at'] = $exist->row()->fb_expires_at;
        } else {
        	$retorno["erro"] = true;
        }
        exit(json_encode($retorno));
	}

	public function update_fb_access_token() {
		$post = $_POST;
		$retorno = array();

		$retorno = $this->unidades_model->update_by_fb_app_id($post["app_id"], array("fb_access_token"=>$post["access_token"], "fb_expires_at"=>date('Y/m/d H:i:s', strtotime("+".$post["expires_at"]." seconds"))));
		exit(json_encode($retorno));
	}

	public function parametros_contrato($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "parametros_contrato"))
		{
			redirect('auth', 'refresh');
		}

		$unidade = $this->unidades_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();
		$status = $this->unidades_model->get_status($unidade->empresa_id);
		$historicos = $this->unidades_model->get_historicos_status($unidade->empresa_id);

		if($status->num_rows() == 0) {
			$this->session->set_flashdata('message', show_alert_error("É necessário importar os status do SAX para acessar a página."));
			redirect('unidades', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_parametros_contrato'), 'unidades/parametros_contrato');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		$config = $this->unidades_model->get_config_contrato($id)->row();

		if (isset($_POST) && !empty($_POST))
		{
			$dados["LIG_STATUS_CONTRATO_01"] = ($this->input->post('matricula_aluno') == "") ? null : $this->input->post('matricula_aluno');
			$dados["LIG_STATUS_CONTRATO_02"] = ($this->input->post('pagamentro_matricula') == "") ? null : $this->input->post('pagamentro_matricula');
			$dados["LIG_STATUS_CONTRATO_03"] = ($this->input->post('primeira_presenca') == "") ? null : $this->input->post('primeira_presenca');
			$dados["LIG_STATUS_CONTRATO_04"] = ($this->input->post('sem_presenca') == "") ? null : $this->input->post('sem_presenca');
			$dados["LIG_STATUS_CONTRATO_06"] = ($this->input->post('cancelamento_contrato') == "") ? null : $this->input->post('cancelamento_contrato');
			$dados["LIG_STATUS_CONTRATO_05"] = ($this->input->post('matricula_aluno_pauta') == "") ? null : $this->input->post('matricula_aluno_pauta');
			$dados["NUM_FALTAS_CONTRATO_05"] = ($this->input->post('num_faltas') == "") ? null : $this->input->post('num_faltas');
			$dados["LIG_ID_STATUS_TURMA01"] = ($this->input->post('inicio_turma') == "") ? null : $this->input->post('inicio_turma');
			$dados["LIG_ID_STATUS_TURMA02"] = ($this->input->post('lancamento_pauta_inicial') == "") ? null : $this->input->post('lancamento_pauta_inicial');
			$dados["LIG_ID_STATUS_TURMA03"] = ($this->input->post('lancamento_pauta_final') == "") ? null : $this->input->post('lancamento_pauta_final');
			$dados["LIG_ID_STATUS_DESISTENCIA"] = ($this->input->post('matricula_desistencia') == "") ? null : $this->input->post('matricula_desistencia');
			$dados["NUM_DIAS_DESISTENCIA"] = ($this->input->post('dias_desistencia') == "") ? null : $this->input->post('dias_desistencia');
			$dados["LIG_ID_STATUS_NUNCA_ACESSOU"] = ($this->input->post('matricula_nunca_acessou') == "") ? null : $this->input->post('matricula_nunca_acessou');
			$dados["NUM_DIAS_NUNCA_ACESSOU"] = ($this->input->post('dias_nunca_acessou') == "") ? null : $this->input->post('dias_nunca_acessou');
			$dados["LIG_ID_ANULADO"] = ($this->input->post('anulado') == "") ? null : $this->input->post('anulado');
			$dados["STATUS_HISTORICO"] = ($this->input->post('status_historico') == "") ? null : $this->input->post('status_historico');
			$dados["LIG_ID_FILIAL"] = $id;
			$dados["LIG_ID_RESGATE_FINANCEIRO"] = ($this->input->post('resgate_financeiro') == "") ? null : $this->input->post('resgate_financeiro');
			$dados["LIG_ID_RESGATE_PEDAGOGICO"] = ($this->input->post('resgate_pedagogico') == "") ? null : $this->input->post('resgate_pedagogico');
			$dados["LIG_ID_CONCLUIDO"] = ($this->input->post('concluido') == "") ? null : $this->input->post('concluido');

			if($config)
				$config_save = $this->unidades_model->config_update($id, $dados);
			else
				$config_save = $this->unidades_model->config_create(array_filter($dados));

			if ($config_save)
			{
				$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_config_saved')));
			}
			else
			{
				$this->session->set_flashdata('message', show_alert_error($this->unidades_model->errors()));
			}

			redirect('unidades', 'refresh');
		}

        $status_options = array(""=>"Selecione");
        foreach ($status->result() as $s) {
            $status_options[$s->id] = $s->descricao;
        }
        $this->data['matricula_aluno'] = array(
            'name'  => 'matricula_aluno',
            'id'    => 'matricula_aluno',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('matricula_aluno', @$config->LIG_STATUS_CONTRATO_01)),
        );
        $this->data['pagamentro_matricula'] = array(
            'name'  => 'pagamentro_matricula',
            'id'    => 'pagamentro_matricula',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('pagamentro_matricula', @$config->LIG_STATUS_CONTRATO_02)),
        );
        $this->data['primeira_presenca'] = array(
            'name'  => 'primeira_presenca',
            'id'    => 'primeira_presenca',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('primeira_presenca', @$config->LIG_STATUS_CONTRATO_03)),
        );
        $this->data['sem_presenca'] = array(
            'name'  => 'sem_presenca',
            'id'    => 'sem_presenca',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('sem_presenca', @$config->LIG_STATUS_CONTRATO_04)),
        );
        $this->data['cancelamento_contrato'] = array(
            'name'  => 'cancelamento_contrato',
            'id'    => 'cancelamento_contrato',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('cancelamento_contrato', @$config->LIG_STATUS_CONTRATO_06)),
        );
        $this->data['anulado'] = array(
            'name'  => 'anulado',
            'id'    => 'anulado',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('anulado', @$config->LIG_ID_ANULADO)),
        );
        $this->data['resgate_financeiro'] = array(
            'name'  => 'resgate_financeiro',
            'id'    => 'resgate_financeiro',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('resgate_financeiro', @$config->LIG_ID_RESGATE_FINANCEIRO)),
        );
        $this->data['resgate_pedagogico'] = array(
            'name'  => 'resgate_pedagogico',
            'id'    => 'resgate_pedagogico',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('resgate_pedagogico', @$config->LIG_ID_RESGATE_PEDAGOGICO)),
        );
        $this->data['concluido'] = array(
            'name'  => 'concluido',
            'id'    => 'concluido',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('concluido', @$config->LIG_ID_CONCLUIDO)),
        );
        $this->data['matricula_aluno_pauta'] = array(
            'name'  => 'matricula_aluno_pauta',
            'id'    => 'matricula_aluno_pauta',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('matricula_aluno_pauta', @$config->LIG_STATUS_CONTRATO_05)),
        );
        $this->data['num_faltas'] = array(
            'name'  => 'num_faltas',
            'id'    => 'num_faltas',
            'class' => 'form-control',
			'type'  => 'text',
            'value' => ($this->form_validation->set_value('num_faltas', @$config->NUM_FALTAS_CONTRATO_05)),
        );
        $this->data['inicio_turma'] = array(
            'name'  => 'inicio_turma',
            'id'    => 'inicio_turma',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('inicio_turma', @$config->LIG_ID_STATUS_TURMA01)),
        );
        $this->data['lancamento_pauta_inicial'] = array(
            'name'  => 'lancamento_pauta_inicial',
            'id'    => 'lancamento_pauta_inicial',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('lancamento_pauta_inicial', @$config->LIG_ID_STATUS_TURMA02)),
        );
        $this->data['lancamento_pauta_final'] = array(
            'name'  => 'lancamento_pauta_final',
            'id'    => 'lancamento_pauta_final',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('lancamento_pauta_final', @$config->LIG_ID_STATUS_TURMA03)),
        );
        $this->data['matricula_desistencia'] = array(
            'name'  => 'matricula_desistencia',
            'id'    => 'matricula_desistencia',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('matricula_desistencia', @$config->LIG_ID_STATUS_DESISTENCIA)),
        );
        $this->data['dias_desistencia'] = array(
            'name'  => 'dias_desistencia',
            'id'    => 'dias_desistencia',
            'class' => 'form-control',
			'type'  => 'text',
            'value' => ($this->form_validation->set_value('dias_desistencia', @$config->NUM_DIAS_DESISTENCIA)),
        );
        $this->data['matricula_nunca_acessou'] = array(
            'name'  => 'matricula_nunca_acessou',
            'id'    => 'matricula_nunca_acessou',
            'class' => 'form-control',
            'options' => $status_options,
            'selected' => ($this->form_validation->set_value('matricula_nunca_acessou', @$config->LIG_ID_STATUS_NUNCA_ACESSOU)),
        );
        $historicos_options = array(""=>"Selecione");
        foreach ($historicos->result() as $h) {
            $historicos_options[$h->id] = $h->descricao;
        }
        $this->data['status_historico'] = array(
            'name'  => 'status_historico',
            'id'    => 'status_historico',
            'class' => 'form-control',
            'options' => $historicos_options,
            'selected' => ($this->form_validation->set_value('status_historico', @$config->STATUS_HISTORICO)),
        );
        $this->data['dias_nunca_acessou'] = array(
            'name'  => 'dias_nunca_acessou',
            'id'    => 'dias_nunca_acessou',
            'class' => 'form-control',
			'type'  => 'text',
            'value' => ($this->form_validation->set_value('dias_nunca_acessou', @$config->NUM_DIAS_NUNCA_ACESSOU)),
        );

        /* Load Template */
        $this->template->admin_render('admin/unidades/parametros_contrato', $this->data);
	}

	public function cursos($id)
	{
        if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['unidade'] = $this->unidades_model->get(array('select'=>'unidades.*', 'where'=>'unidades.id = '.$id.' and unidades.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('unidades.id', 'desc')))->result()[0];
            $this->data['message'] = $this->session->flashdata('message');
			$this->data['cursos'] = [];
			$this->data['cursos_filial'] = [];

			$sql = "SELECT c.id_curso, c.descricao, f.LIG_ID_FILIAL id_filial
					FROM curso c LEFT JOIN curso_filial f ON c.id_curso = f.LIG_ID_CURSO AND f.LIG_ID_FILIAL = $id
					WHERE c.empresa_id = ".$this->session->userdata('empresa_id')." AND c.ativo = 'S'
					GROUP BY c.id_curso ORDER BY c.descricao";

			foreach($this->db->query($sql)->result() as $curso){
				if($curso->id_filial == null){
					$this->data['cursos'][] = $curso;
				}else{
					$this->data['cursos_filial'][] = $curso;
				}
			}
			
            /* Load Template */
            $this->template->admin_render('admin/unidades/cursos', $this->data);
        }
	}

	public function cursoSalvar($id)
	{
		if (isset($_POST) && ! empty($_POST)){
			if($unidade = $this->db->query("SELECT id FROM unidades WHERE id = $id AND empresa_id = ".$this->session->userdata('empresa_id'))->result()){	
				if($curso = $this->db->query("SELECT id_curso FROM curso WHERE descricao = '".$_POST['curso']."'")->result()){
					$sql = "INSERT INTO curso_filial(lig_id_filial, lig_id_curso, data_cadastro) VALUES(".$unidade[0]->id.",".$curso[0]->id_curso.",now())";
					$this->db->query($sql);
				}
			}
		}

		redirect('unidades/cursos/'.$id);
	}

	public function cursoDeletar($id_unidade, $id_curso){
		if($curso_filial = $this->db->query("SELECT * FROM curso_filial WHERE lig_id_filial = $id_unidade AND lig_id_curso = $id_curso")->result()){
			$this->db->query("DELETE FROM curso_filial WHERE lig_id_filial = $id_unidade AND lig_id_curso = $id_curso");
		}
		redirect('unidades/cursos/'.$id_unidade);
	}


	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("unidades", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/unidades/delete');
        }
	}

	public function edit($id = null)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("unidades", "edit"))
		{
			redirect('auth', 'refresh');
		}
        $unidade = $this->unidade_da_empresa($id);
        if (!$unidade) {
            $this->session->set_flashdata('message', show_alert_error('Filial não encontrada.'));
            redirect('unidades', 'refresh');
        }
        $this->formulario($unidade);
	}

	public function activate($id = NULL)
	{
        $this->alterar_status($id, 'activate');
	}

	public function deactivate($id = NULL)
	{
        $this->alterar_status($id, 'deactivate');
	}

    private function alterar_status($id, $acao)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao('unidades', $acao)) {
            redirect('auth/login', 'refresh');
        }
        $unidade = $this->unidade_da_empresa($id);
        $ok = $unidade && ($acao === 'activate' ? $this->unidades_model->activate($unidade->id) : $this->unidades_model->deactivate($unidade->id));
        if ($ok) {
            $this->session->set_flashdata('message', show_alert_success('Filial "' . html_escape($unidade->unidade) . '" ' . ($acao === 'activate' ? 'ativada.' : 'inativada.')));
        } else {
            $this->session->set_flashdata('message', show_alert_error('Não foi possível alterar a filial.'));
        }
        redirect('unidades', 'refresh');
    }

    private function unidade_da_empresa($id)
    {
        return $this->db->where('id', (int) $id)
            ->where('empresa_id', (int) $this->session->userdata('empresa_id'))
            ->get('unidades')->row();
    }

    // Criação ($unidade = null) e edição compartilham o formulário admin/unidades/_form.
    private function formulario($unidade)
    {
        $editando = $unidade !== null;
        $this->breadcrumbs->unshift(2, lang($editando ? 'menu_unidades_edit' : 'menu_unidades_create'), $editando ? 'unidades/edit/' . (int) $unidade->id : 'unidades/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $tipo = $this->input->post('tipo') === 'F' ? 'F' : 'J';
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[s,n]');
        $this->form_validation->set_rules('tipo', 'Tipo', 'required|in_list[J,F]');
        if ($tipo === 'J') {
            $this->form_validation->set_rules('cnpj', 'CNPJ', 'required|validar_cnpj');
            $this->form_validation->set_rules('razao_social', 'Razão social', 'trim|required');
            $this->form_validation->set_rules('fantasia', 'Nome fantasia', 'trim|required');
        } else {
            $this->form_validation->set_rules('cpf', 'CPF', 'required|validar_cpf');
            $this->form_validation->set_rules('rg', 'RG', 'trim|required');
            $this->form_validation->set_rules('nome', 'Nome', 'trim|required');
        }
        $this->form_validation->set_rules('telefone1', 'Telefone', 'trim|required');
        $this->form_validation->set_rules('email', 'E-mail', 'trim|required|valid_email');
        $this->form_validation->set_rules('cep', 'CEP', 'trim|required');
        $this->form_validation->set_rules('logradouro', 'Logradouro', 'trim|required');
        $this->form_validation->set_rules('numero', 'Número', 'trim|required');
        $this->form_validation->set_rules('bairro', 'Bairro', 'trim|required');
        $this->form_validation->set_rules('cidade', 'Cidade', 'trim|required');
        $this->form_validation->set_rules('uf', 'UF', 'trim|required');

        if ($this->input->post() && $this->form_validation->run() == TRUE) {
            if ($tipo === 'J') {
                $dados = array('cnpj' => $this->input->post('cnpj'), 'cpf' => '', 'rg' => '', 'razao_social' => $this->input->post('razao_social'), 'unidade' => $this->input->post('fantasia'));
            } else {
                $dados = array('cpf' => $this->input->post('cpf'), 'rg' => $this->input->post('rg'), 'cnpj' => '', 'razao_social' => '', 'unidade' => $this->input->post('nome'));
            }
            $dados += array(
                'actived' => $this->input->post('status'),
                'tipo_cliente' => $tipo,
                'tipo' => $tipo,
                'gestor' => (string) $this->input->post('gestor'),
                'email' => $this->input->post('email'),
                'site' => (string) $this->input->post('site'),
                'endereco' => $this->input->post('logradouro') . ', ' . $this->input->post('bairro') . ', ' . $this->input->post('numero'),
                'rua' => $this->input->post('logradouro'),
                'numero' => $this->input->post('numero'),
                'bairro' => $this->input->post('bairro'),
                'cidade' => $this->input->post('cidade'),
                'uf' => $this->input->post('uf'),
                'cep' => $this->input->post('cep'),
                'telefone1' => $this->input->post('telefone1'),
                'telefone2' => (string) $this->input->post('telefone2'),
                'agente_encaminhamento' => (string) $this->input->post('responsavel'),
                'caracteristica' => $this->input->post('caracteristica') ? (int) $this->input->post('caracteristica') : null,
                'empresa_id' => (int) $this->session->userdata('empresa_id'),
            );

            if ($editando) {
                $dados['modified'] = date('Y-m-d H:i:s');
                $ok = $this->unidades_model->update($unidade->id, $dados);
                if ($ok) {
                    // URL do node e token da API por produto habilitado (aba "Dados do APP").
                    $produtos_post = $this->input->post('produtos_app');
                    if (is_array($produtos_post)) {
                        foreach ($produtos_post as $id_produto => $campos) {
                            $update_hp = array();
                            if (isset($campos['url_node'])) { $update_hp['url_node'] = $campos['url_node']; }
                            if (isset($campos['token_api'])) { $update_hp['token_api'] = $campos['token_api']; }
                            if ($update_hp) {
                                $this->db->update('habilitacoes_produto', $update_hp, array('id_cliente' => (int) $unidade->id, 'id_produto' => (int) $id_produto));
                            }
                        }
                    }
                }
            } else {
                $ok = $this->unidades_model->create($dados);
            }
            if ($ok) {
                $this->session->set_flashdata('message', show_alert_success('Filial "' . html_escape($dados['unidade']) . '" ' . ($editando ? 'atualizada.' : 'criada.')));
                redirect('unidades', 'refresh');
            }
            $this->data['message'] = show_alert_error($this->unidades_model->errors() ? $this->unidades_model->errors() : 'Não foi possível salvar a filial.');
        } elseif ($this->input->post()) {
            $this->data['message'] = show_alert_error(validation_errors());
        }

        $produtos_app = array();
        $numeros = null;
        if ($editando) {
            $produtos_app = $this->db->select('hp.id_produto, hp.url_node, hp.token_api, hp.status, hp.periodo_fim, p.nome')
                ->from('habilitacoes_produto hp')->join('produtos p', 'hp.id_produto = p.id')
                ->where('hp.id_cliente', (int) $unidade->id)->order_by('p.nome')->get()->result();
            $numeros = $this->db->query("SELECT
                    (SELECT COUNT(*) FROM habilitacoes_produto hp WHERE hp.id_cliente = ? AND hp.status = 'S' AND hp.periodo_fim >= CURDATE()) AS produtos,
                    (SELECT COUNT(DISTINCT uu.user_id) FROM user_unidade uu JOIN users us ON us.id = uu.user_id AND us.active = 1 WHERE uu.unidade_id = ?) AS usuarios,
                    (SELECT COUNT(*) FROM chamado c WHERE c.unidade_id = ? AND c.created >= CURDATE() - INTERVAL 90 DAY) AS chamados_90",
                array((int) $unidade->id, (int) $unidade->id, (int) $unidade->id))->row();
        }

        $post = $this->input->post();
        $atual = function ($campo, $coluna = null) use ($post, $editando, $unidade) {
            if ($post) {
                return (string) $this->input->post($campo);
            }
            $coluna = $coluna ? $coluna : $campo;
            return $editando && isset($unidade->$coluna) ? (string) $unidade->$coluna : '';
        };
        $tipo_atual = $post ? $tipo : ($editando && $unidade->tipo === 'F' ? 'F' : 'J');
        $this->data['valores'] = array(
            'tipo' => $tipo_atual,
            'status' => $post ? (string) $this->input->post('status') : ($editando ? ($unidade->actived === 's' ? 's' : 'n') : 's'),
            'cnpj' => $atual('cnpj'),
            'razao_social' => $atual('razao_social'),
            'fantasia' => $post ? (string) $this->input->post('fantasia') : ($editando && $tipo_atual === 'J' ? $unidade->unidade : ''),
            'cpf' => $atual('cpf'),
            'rg' => $atual('rg'),
            'nome' => $post ? (string) $this->input->post('nome') : ($editando && $tipo_atual === 'F' ? $unidade->unidade : ''),
            'caracteristica' => $atual('caracteristica'),
            'gestor' => $atual('gestor'),
            'responsavel' => $atual('responsavel', 'agente_encaminhamento'),
            'telefone1' => $atual('telefone1'),
            'telefone2' => $atual('telefone2'),
            'email' => $atual('email'),
            'site' => $atual('site'),
            'cep' => $atual('cep'),
            // Cadastros antigos têm o endereço só em "endereco".
            'logradouro' => ($post || !$editando || (string) $unidade->rua !== '') ? $atual('logradouro', 'rua') : (string) $unidade->endereco,
            'numero' => $atual('numero'),
            'bairro' => $atual('bairro'),
            'cidade' => $atual('cidade'),
            'uf' => $atual('uf'),
        );

        $integracoes = array();
        if ($editando) {
            foreach (array(
                'recursos_ws' => array('fa-server', 'Recursos Webservice'),
                'facebook_api' => array('fa-facebook-square', 'Facebook API'),
                'rd_station' => array('fa-registered', 'RD Station'),
                'parametros_contrato' => array('fa-cogs', 'Parâmetros de contrato'),
                'iugu' => array('fa-credit-card', 'Iugu'),
                'cursos' => array('fa-book', 'Cursos'),
            ) as $metodo => $acao) {
                if ($this->ion_auth->has_permissao('unidades', $metodo)) {
                    $integracoes[$metodo] = $acao;
                }
            }
        }

        $this->data['modo'] = $editando ? 'edit' : 'create';
        $this->data['unidade'] = $unidade;
        $this->data['numeros'] = $numeros;
        $this->data['produtos_app'] = $produtos_app;
        $this->data['integracoes'] = $integracoes;
        $this->data['caracteristicas'] = $this->unidades_model->get_caracteristicas()->result();
        $this->data['ufs'] = get_uf();

        $this->template->admin_render('admin/unidades/_form', $this->data);
    }

	public function buscar_endereco() {
		$retorno = $this->curl->get('app.xkeysti.com.br/api/v1/getEnderecoByCep?cep='.$this->input->post('cep'));
		exit(json_decode($retorno));
	}

	public function buscar_eventos() {
		$eventos = $this->unidades_model->get_eventos($_POST["midia"], $_POST["unidade"]);
		$retorno = array();
		if($eventos->num_rows() > 0) {
			foreach ($eventos->result() as $value) {
				$retorno[$value->id] = $value->descricao;
			}
		}
		exit(json_encode($retorno));
	}

	public function rd_station($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "rd_station"))
		{
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('client_id', 'lang:create_validation_client_id_label', 'required');
		$this->form_validation->set_rules('client_secret', 'lang:create_validation_client_secret_label', 'required');
		$this->form_validation->set_rules('token', 'lang:create_validation_token_label', 'required');
		$this->form_validation->set_rules('refresh_token', 'lang:create_validation_refresh_token_label', 'required');

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_rd_station'), 'unidades/rd_station');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		$unidade = $this->unidades_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		$dados["rd_station_client_id"] = $this->input->post('client_id');
		$dados["rd_station_client_secret"] = $this->input->post('client_secret');
		$dados["rd_station_token"] = $this->input->post('token');
		$dados["rd_station_refresh_token"] = $this->input->post('refresh_token');
		$dados["rd_station_status"] = $this->input->post('status');

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$unidade_update = $this->unidades_model->update($id, $dados);

				if ($unidade_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_unidade_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->unidades_model->errors()));
				}
				redirect('unidades', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->unidades_model->errors() ? show_alert_error($this->unidades_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		$this->data['id'] = $id;
		$this->data['unidade'] = $unidade;
		$this->data['status'] = $unidade->rd_station_status;
		$this->data['client_id'] = array(
			'name'  => 'client_id',
			'id'    => 'client_id',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('client_id', $unidade->rd_station_client_id)
		);
		$this->data['client_secret'] = array(
			'name'  => 'client_secret',
			'id'    => 'client_secret',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('client_secret', $unidade->rd_station_client_secret)
		);
		$this->data['token'] = array(
			'name'  => 'token',
			'id'    => 'token',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('token', $unidade->rd_station_token),
			'readonly' => true
		);
		$this->data['btn_token'] = array(
			'id' => 'btn_token',
			'content' => 'Gerar',
			'type' => 'button',
            'class' => 'btn btn-primary btn-flat',
			'onclick' => 'gerarRdToken();',
			'style' => 'margin-top:5px;'
		);

        /* Load Template */
        $this->template->admin_render('admin/unidades/rd_station', $this->data);
	}
	
	public function iugu($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("unidades", "iugu"))
		{
			redirect('auth', 'refresh');
		}

		$this->form_validation->set_rules('token', 'lang:create_validation_token_label', 'required');
		$this->form_validation->set_rules('acc_id', 'lang:create_validation_acc_id_label', 'required');
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_unidades_iugu'), 'unidades/iugu');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		$unidade = $this->unidades_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		$dados["iugu_token"] = $this->input->post('token');
		$dados["iugu_acc_id"] = $this->input->post('acc_id');
		$dados["iugu_status"] = $this->input->post('status');
		$dados["iugu_receb_boleto"] = serialize($this->input->post('receb_boleto'));
		$dados["iugu_receb_credito"] = serialize($this->input->post('receb_credito'));

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$unidade_update = $this->unidades_model->update($id, $dados);

				if ($unidade_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_unidade_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->unidades_model->errors()));
				}
				redirect('unidades', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->unidades_model->errors() ? show_alert_error($this->unidades_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		$this->data['id'] = $id;
		$this->data['status'] = ($this->input->post('status')) ? $this->input->post('status') : $unidade->iugu_status;

		$this->data['token'] = array(
			'name'  => 'token',
			'id'    => 'token',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('token', $unidade->iugu_token)
		);
		$this->data['acc_id'] = array(
			'name'  => 'acc_id',
			'id'    => 'acc_id',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('acc_id', $unidade->iugu_acc_id)
		);
		$array_receb = array();
		foreach($this->unidades_model->get_recebimentos($id)->result() as $receb) {
			$array_receb[$receb->CODIGO] = $receb->NOME;
		}
		$this->data['receb_boleto'] = array(
            'id'=>'receb_boleto',
            'name'=>'receb_boleto[]',
            'class'=>'multipleSelect', 
            'multiple'=>'multiple', 
            'options' => $array_receb, 
            'selected'=> ($this->input->post('receb_boleto')) ? $this->input->post('receb_boleto') : @unserialize($unidade->iugu_receb_boleto)
        );

		$this->data['receb_credito'] = array(
            'id'=>'receb_credito',
            'name'=>'receb_credito[]',
            'class'=>'multipleSelect', 
            'multiple'=>'multiple', 
            'options' => $array_receb, 
            'selected'=> ($this->input->post('receb_credito')) ? $this->input->post('receb_credito') : @unserialize($unidade->iugu_receb_credito)
        );

        /* Load Template */
        $this->template->admin_render('admin/unidades/iugu', $this->data);
	}
}
