<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contratos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/contratos');
        $this->load->model('contratos_model');
        $this->load->model('ead_segments_model');
        $this->load->model('ead_users_model');
        $this->load->model('preferences_model');
        $this->load->model('auditorias_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_contratos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_contratos'), 'contratos');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contratos", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            if($_POST) {
                if(@$_POST["data_pedido_inicio"] != "") {
                    $data_pedido_inicio = convertDateBRToEU($_POST["data_pedido_inicio"]);
                    $this->data['data_pedido_inicio'] = $data_pedido_inicio;
                } else
                    $data_pedido_inicio = "";
                if(@$_POST["data_pedido_fim"] != "") {
                    $data_pedido_fim = convertDateBRToEU($_POST["data_pedido_fim"]);
                    $this->data['data_pedido_fim'] = $data_pedido_fim;
                } else
                    $data_pedido_fim = "";
                if(@$_POST["data_matricula_inicio"] != "") {
                    $data_matricula_inicio = convertDateBRToEU($_POST["data_matricula_inicio"]);
                    $this->data['data_matricula_inicio'] = $data_matricula_inicio;
                } else
                    $data_matricula_inicio = "";
                if(@$_POST["data_matricula_fim"] != "") {
                    $data_matricula_fim = convertDateBRToEU($_POST["data_matricula_fim"]);
                    $this->data['data_matricula_fim'] = $data_matricula_fim;
                } else
                    $data_matricula_fim = "";
                if(@$_POST["data_acesso_inicio"] != "") {
                    $data_acesso_inicio = convertDateBRToEU($_POST["data_acesso_inicio"]);
                    $this->data['data_acesso_inicio'] = $data_acesso_inicio;
                } else
                    $data_acesso_inicio = "";
                if(@$_POST["data_acesso_fim"] != "") {
                    $data_acesso_fim = convertDateBRToEU($_POST["data_acesso_fim"]);
                    $this->data['data_acesso_fim'] = $data_acesso_fim;
                } else
                    $data_acesso_fim = "";
                

                if(@$_POST["status"]) 
                    $status = $_POST["status"];
                else
                    $status = array();

                if(@$_POST["cursos"]) 
                    $cursos = $_POST["cursos"];
                else
                    $cursos = array();

                if(@$_POST["filial"]) 
                    $filial = $_POST["filial"];
                else
                    $filial = array();

                if(@$_POST["tipo"]) {
                    $tipo = $_POST["tipo"];
                    $this->data['tipo'] = $tipo;
                } else
                    $tipo = "";
                if(@$_POST["level"]) 
                    $level = $_POST["level"];
                else
                    $level = array();

                $where = "";
                if($data_pedido_inicio != "")
                    $where .= " AND cad_orca.data_pedido >= '".$data_pedido_inicio."'";
                if($data_pedido_fim != "") {
                    $where .= " AND cad_orca.data_pedido <= '".$data_pedido_fim."'";
                }
                if($data_matricula_inicio != "")
                    $where .= " AND cad_orca.data_matricula >= '".$data_matricula_inicio."'";
                if($data_matricula_fim != "") {
                    $where .= " AND cad_orca.data_matricula <= '".$data_matricula_fim."'";
                }
                if($data_acesso_inicio != "")
                    $where .= " AND ead_users.ultimo_acesso >= '".$data_acesso_inicio."'";
                if($data_acesso_fim != "") {
                    $where .= " AND ead_users.ultimo_acesso <= '".$data_acesso_fim."'";
                }
                if($tipo != "") {
                    $where .= " AND cad_orca.tipo_saxweb = '".$tipo."'";
                }
                if(!empty($cursos)) {
                    $where .= " AND cad_orca.lig_id_curso IN (".implode(",", $cursos).")";
                }
                if(!empty($filial)) {
                    $where .= " AND cad_orca.filial IN (".implode(",", $filial).")";
                }
                if(!empty($status)) {
                    $where .= " AND cad_orca.status IN (".implode(",", $status).")";
                }
                if(!empty($level)) {
                    $where .= " AND ead_users.level = ".$level;
                }

                $contratos = $this->contratos_model->get(
                    array(
                        'select'=>'cad_orca.*, users.first_name, users.last_name, cad_status.descricao as status_desc, curso.DESCRICAO as curso_desc, unidades.unidade, ead_users.level, ead_users.ultimo_acesso', 
                        'join' => array(
                            array('cad_status', 'cad_status.id = cad_orca.status', 'inner'), 
                            array('users', 'users.id = cad_orca.user_id', 'inner'), 
                            array('curso', 'curso.ID_CURSO = cad_orca.lig_id_curso', 'left'), 
                            array('ead_users', 'users.id = ead_users.user_id', 'left')), 
                        'where'=>'unidades.empresa_id = '.$this->session->userdata('empresa_id').$where,
                        'order_by'=>array('cad_orca.id', 'desc')
                    ))->result();
            } else {
                $status = array(12);
                $contratos = $this->contratos_model->get(
                    array(
                        'select'=>'cad_orca.*, users.first_name, users.last_name, cad_status.descricao as status_desc, curso.DESCRICAO as curso_desc, unidades.unidade, ead_users.level, ead_users.ultimo_acesso', 
                        'join' => array(
                            array('cad_status', 'cad_status.id = cad_orca.status', 'inner'), 
                            array('users', 'users.id = cad_orca.user_id', 'inner'), 
                            array('curso', 'curso.ID_CURSO = cad_orca.lig_id_curso', 'left'),
                            array('ead_users', 'users.id = ead_users.user_id', 'left')), 
                        'where'=>'cad_orca.status IN ('.implode(",", $status).') AND unidades.empresa_id = '.$this->session->userdata('empresa_id') ,
                        'order_by'=>array('cad_orca.id', 'desc')
                    ))->result();
            }

            $k=0;
            foreach ($contratos as $contrato) {
                $segments_array = array();
                if(@$contrato->id) {
                    $segments = $this->contratos_model->get_segments($contrato->id);
                    if(@$segments->num_rows() > 0) {
                        foreach ($segments->result() as $seg) {
                            $segments_array[] = $seg->segment_label;
                        }
                    }
                }

                $contratos[$k]->seguimentos = $segments_array;
                $contratos[$k]->level = @$contrato->level;

                $cliente_voxy = false;
                $contratos[$k]->has_pdf = false;
                if($contrato->tipo_saxweb == 2) {
                    $cliente_voxy = $this->contratos_model->is_voxy_user($contrato->user_id);

                    if($this->ion_auth->has_permissao("contratos", "ver_pdf")) {
                        $contratos[$k]->has_pdf = true;
                    }
                }

                $contratos[$k]->cliente_voxy = $cliente_voxy;
                if($contratos[$k]->has_pesquisa = $this->contratos_model->has_pesquisa($contrato->id)) {
                    $contratos[$k]->pesquisa_perfil = $this->contratos_model->get_pesquisa($contrato->id);
                }

                $contratos[$k]->can_anular = false;
                $parametro = $this->contratos_model->get_parametro($contrato->filial);
                if($parametro->LIG_STATUS_CONTRATO_01 == $contrato->status) {
                    $contratos[$k]->can_anular = true;
                }

                $contratos[$k]->can_alterar_desempenho = false;
                if($this->ion_auth->has_permissao("contratos", "alterar_desempenho")) {
                    $contratos[$k]->can_alterar_desempenho = true;
                }

                $unidade = $this->contratos_model->get_unidade($contrato->filial);

                if($unidade->num_rows() == 1) {
                    if($unidade->row()->iugu_status == "S") {
                        
                        $contratos[$k]->can_gerar_segunda_via = false;
                        $has_iugu_entrada = $this->contratos_model->has_iugu_entrada($contrato->id);
                        if($parametro->LIG_STATUS_CONTRATO_01 == $contrato->status && $contrato->tipo_saxweb == 2 && $has_iugu_entrada->num_rows() == 1 && $this->ion_auth->has_permissao("iugu", "gerar_segunda_via_entrada")) {
                            $contratos[$k]->can_gerar_segunda_via = true;
                        }

                        $contratos[$k]->can_cartao_recorrencia = false;
                        if($this->ion_auth_model->is_recorrencia($contrato->id, $contrato->user_id) && $this->ion_auth->has_permissao("iugu", "cartao_recorrencia")) {
                            $contratos[$k]->can_cartao_recorrencia = true;
                        }

                        $contratos[$k]->can_alterar_assinatura = false;
                        if($this->ion_auth_model->is_assinatura($contrato->id) && $this->ion_auth->has_permissao("iugu", "alterar_assinatura")) {
                            $contratos[$k]->can_alterar_assinatura = true;
                        }

                        $contratos[$k]->can_incluir_assinatura = false;
                        if(!$this->ion_auth_model->is_assinatura($contrato->id) && !$this->ion_auth_model->is_recorrencia($contrato->id, $contrato->user_id) && $this->ion_auth->has_permissao("iugu", "alterar_assinatura")) {
                            $contratos[$k]->can_incluir_assinatura = true;
                        }
                    }
                }

                $k++;
            }

            $this->data['contratos'] = $contratos;

            /*if(count($this->data['contatos']) == 0) {
                $this->data['message'] .= show_alert_warning("Não há contatos cadastrados nos últimos 3 dias. Utilize o filtro para busca em períodos maiores.");
            }*/
           
            $unidades = $this->contratos_model->get_filiais()->result();
            $unidades_array = array();
            foreach ($unidades as $unid) {
                $unidades_array[$unid->id] = $unid->unidade;
            }
            $this->data['filial'] = array(
                'name'  => 'filial[]',
                'id'    => 'filial',
                'type'  => 'text',
                'class' => 'form-control multipleSelect',
                'multiple' => 'multiple',
                'options' => $unidades_array,
                'selected' => $this->input->post('filial')
            );

            $status_array = array();
            foreach ($this->contratos_model->get_status_orca()->result() as $value) {
                $status_array[$value->id] = $value->descricao;
            }
            $this->data['status'] = array(
                'name'  => 'status[]',
                'id'    => 'status',
                'type'  => 'text',
                'class' => 'form-control multipleSelect',
                'multiple' => 'multiple',
                'options' => $status_array,
                'selected' => $status
            );
            $this->data['tipo'] = array(
                'name'  => 'tipo',
                'id'    => 'tipo',
                'type'  => 'text',
                'class' => 'form-control',
                'options' => array(''=>'Selecione', '1'=>'Padrão', '2'=>'EAD'),
                'selected' => $this->input->post('tipo')
            );
            $cursos_array = array();
            foreach ($this->contratos_model->get_cursos()->result() as $value) {
                $cursos_array[$value->ID_CURSO] = $value->DESCRICAO;
            }
            $this->data['cursos'] = array(
                'name'  => 'cursos[]',
                'id'    => 'cursos',
                'type'  => 'text',
                'class' => 'form-control multipleSelect',
                'multiple' => 'multiple',
                'options' => $cursos_array,
                'selected' => $this->input->post('cursos')
            );

            $this->data['level'] = array(
                'name'  => 'level',
                'id'    => 'level',
                'type'  => 'text',
                'class' => 'form-control',
                'options' => array(""=>"", "0"=>"0", "1"=>"1", "2"=>"2", "3"=>"3", "4"=>"4"),
                'selected' => $this->input->post('level')
            );

            $seguimentos_usuario_array = array();
            $seguimentos_usuario_session = $this->contratos_model->get_segments()->result();
            if(count($seguimentos_usuario_session) > 0) {
                foreach ($seguimentos_usuario_session as $seg) {
                    $seguimentos_usuario_array[] = $seg->segment_label;
                }
            }
            $this->data['seguimentos_lista'] = $seguimentos_usuario_array;

            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/contratos/index', $this->data);
        }
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contratos", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_produtos_create'), 'contratos/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
        $this->form_validation->set_rules('filial', 'lang:create_validation_filial_label', 'required');
        $this->form_validation->set_rules('cliente_id', 'lang:create_validation_cliente_label', 'required');
		$this->form_validation->set_rules('curso', 'lang:create_validation_curso_label', 'required');

        $produtos = $this->contratos_model->get_produtos($this->input->post('curso'))->result_array();
        $servicos = $this->contratos_model->get_servicos($this->input->post('curso'))->result_array();

        if($_POST) {
            $condicoes = $this->contratos_model->get_condicoes($this->input->post('curso'))->result_array();
            $curso = $this->contratos_model->get_cursos($this->input->post('curso'))->row();
            //exit("<pre>".print_r($curso,true)."</pre>");

            $total_produtos = 0;
            $total_servicos = 0;
            if(count($produtos) > 0) {
                foreach ($produtos as $prod) {
                    $total_produtos += $prod["VALOR"];
                }
            }
            if(count($servicos) > 0) {
                foreach ($servicos as $serv) {
                    $total_servicos += $serv["VALOR"];
                }
            }

    		$dados["filial"] = $this->input->post('filial');
    		$dados["user_id"] = $this->input->post('cliente_id');
    		$dados["lig_id_curso"] = $this->input->post('curso');
    		$dados["situacao"] = 'EA';
    		$dados["tipo_saxweb"] = $curso->tipo;
            $dados["status"] = $this->contratos_model->get_status_matricula($this->input->post('filial'))->row()->LIG_STATUS_CONTRATO_01;
            $dados["data_matricula"] = "0000-00-00";
            $dados["data_cadastro"] = date('Y-m-d');
            $dados["data_pedido"] = date('Y-m-d');
            $dados["hora_cadastro"] = date('H:i:s');
            $dados["hora_pedido"] = date('H:i:s');
            $dados["total_produtos"] = $total_produtos;
            $dados["total_servicos"] = $total_servicos;
            $dados["total_geral"] = $total_produtos + $total_servicos;
    		$dados["tipo_saxweb"] = $curso->tipo;
        }

		if ($this->form_validation->run() == TRUE && $id = $this->contratos_model->create($dados))
		{
            /*$condicoes = $this->contratos_model->get_condicoes($this->input->post('curso'))->result_array();
            if(count($condicoes) > 0) {
                foreach ($condicoes as $cond) {
                    $dados_condicoes = array(
                        "ORCAMENTO" => $id,
                        "FORMA_DE_PAGAMENTO" => $cond["FormaReceb"],
                        "TIPO_RECEBIMENTO" => $cond["Id_TipoReceb"],
                        "TIPO_CONDICAO_ID" => $cond["ID"],
                        "NUM_PARCELAS" => $cond["NUM_PARCELAS"],
                        "PLANO_FINANCEIRO_ID" => $cond["Id_planofin"],
                        "VALORDESCONTOPONTUALIDADE" => $cond["VALOR_DESCONTO_PONTUALIDADE"],
                        "CARENCIADESCONTOPONTUALIDADE" => $cond["CARENCIA_DESCONTO_PONTUALIDADE"],
                        "INTERVALO" => $cond["TIPOVALORDESCONTOPONTUALIDADE"]
                    );
                    $this->contratos_model->create_condicao_orcamento($dados_condicoes);
                }
            }
            if(count($produtos) > 0) {
                foreach ($produtos as $prod) {
                    $dados_produto = array(
                        "orc_id" => $id,
                        "prod_id" => $prod["prod_id"],
                        "nome" => $prod["DESCRICAO"],
                        "preco" => $prod["VALOR"],
                        "quantidade_item" => 1,
                        "total_item" => $prod["VALOR"] * 1,
                        "peso_item" => $prod["PESO"]
                    );
                    $this->contratos_model->create_produto_orcamento($dados_produto);

                    $dados_produto_parcela = array(
                        "PRODUTO_CONDICOES_ORCA" => $id,
                        "VALOR" => $prod["VALOR"],
                        //"VENCIMENTO" => $prod["DESCRICAO"],
                        "TIPO_RECEBIMENTO" => $prod["VALOR"],
                        "PARCELA" => 1,
                        "CODIGO_PARCELA_GERADA" => $prod["VALOR"] * 1
                    );
                    $this->contratos_model->create_produto_parcela_orcamento($dados_produto_parcela);
                }
            }
            if(count($servicos) > 0) {
                foreach ($servicos as $serv) {
                    $dados_servico = array(
                        "orc_id" => $id,
                        "serv_id" => $serv["serv_id"],
                        "nome" => $serv["APLICACAO"],
                        "preco" => $serv["VALOR"],
                        "quantidade_item" => 1,
                        "total_item" => $serv["VALOR"] * 1
                    );
                    $this->contratos_model->create_servico_orcamento($dados_servico);
                }
            }*/

            if(count($this->input->post('turmas')) > 0) {
                foreach ($this->input->post('turmas') as $turma) {
                    $this->contratos_model->create_turma_aluno(array("aluno"=>$this->input->post('cliente_id'),"turma"=>$turma));
                }
            }

			$this->session->set_flashdata('message', show_alert_success($this->contratos_model->messages()));
			redirect('contratos', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->contratos_model->errors() ? show_alert_error($this->contratos_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $unidades = $this->contratos_model->get_unidades()->result();
            $unidades_array = array(""=>"Selecione");
            foreach ($unidades as $unid) {
                if($unid->LIG_STATUS_CONTRATO_01 != "")
            	   $unidades_array[$unid->id] = $unid->unidade;
            }

            $this->data['filial'] = array(
				'name'  => 'filial',
				'id'    => 'filial',
				'type'  => 'text',
                'class' => 'form-control singleInputDynamic',
                'options' => $unidades_array,
				'value' => $this->form_validation->set_value('filial')
			);
			$this->data['cliente'] = array(
				'name'  => 'cliente',
				'id'    => 'cliente',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('cliente')
			);
			$this->data['responsavel'] = array(
				'name'  => 'responsavel',
				'id'    => 'responsavel',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('responsavel')
			);
			$this->data['users'] = $this->contratos_model->get_users()->result();
			$cursos = $this->contratos_model->get_cursos()->result();
            $cursos_array = array(""=>"Selecione");
            foreach ($cursos as $curso) {
            	$cursos_array[$curso->ID_CURSO] = $curso->DESCRICAO;
            }
			$this->data['curso'] = array(
				'name'  => 'curso',
				'id'    => 'curso',
				'type'  => 'text',
                'class' => 'form-control singleInputDynamic',
                'onchange' => 'selecao_curso(this.value)',
                'options' => $cursos_array,
				'value' => $this->form_validation->set_value('curso')
			);

            /* Load Template */
            $this->template->admin_render('admin/contratos/create', $this->data);
		}
	}

	/*public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/produtos/delete');
        }
	}*/

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("produtos", "edit"))
		{
			redirect('auth', 'refresh');
		}
		// Cópia antiga da edição de produto: a tela agora fica em produtos/edit.
		redirect('produtos/edit/' . (int) $id, 'refresh');

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_produtos_edit'), 'produtos/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$produto = $this->produtos_model->get(array('select'=>'*', 'where'=>'id = '.$id, 'order_by'=>array('id','desc')))->row();

		/* Validate form input */
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');
		$this->form_validation->set_rules('nome', 'lang:create_validation_nome_label', 'trim|required');
		$this->form_validation->set_rules('aplicacao', 'lang:create_validation_aplicacao_label', 'trim|required');		

		$dados["status"] = $this->input->post('status');
		$dados["nome"] = $this->input->post('nome');
		$dados["aplicacao"] = $this->input->post('aplicacao');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["data_atualizacao"] = date('Y-m-d H:i:s');
		$dados["atualizado_por"] = $this->session->userdata('user_id');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$produto_update = $this->produtos_model->update($id, $dados);

				if ($produto_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->produto_update->errors()));
				}

				redirect('produtos', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->produto_update->errors() ? show_alert_error($this->produto_update->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['produto']   = $produto;
		$this->data['status'] = $this->form_validation->set_value('status', $produto->status);
		$this->data['nome'] = array(
			'name'  => 'nome',
			'id'    => 'nome',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('nome', $produto->nome)
		);
		$this->data['aplicacao'] = array(
			'name'  => 'aplicacao',
			'id'    => 'aplicacao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('aplicacao', $produto->aplicacao)
		);
		$this->data['descricao'] = array(
			'name'  => 'descricao',
			'id'    => 'descricao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('descricao', $produto->descricao)
		);

        /* Load Template */
        $this->template->admin_render('admin/produtos/edit', $this->data);
	}


	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("produtos", "activate"))
		{
			$activation = $this->produtos_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->produtos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->produtos_model->messages()));
		redirect('produtos', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("produtos", "deactivate"))
		{
			$deactivation = $this->produtos_model->deactivate($id);
		}
		if(@$deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->produtos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error(lang('permission_denied')));
		redirect('produtos', 'refresh');
	}

	public function selecao_curso() {
		$curso = $_POST["curso"];

		$condicoes = $this->contratos_model->get_condicoes($curso)->result_array();
		$turmas = $this->contratos_model->get_turmas($curso)->result_array();
		$disciplinas = $this->contratos_model->get_disciplinas($curso)->result_array();
		$produtos = $this->contratos_model->get_produtos($curso)->result_array();
        $servicos = $this->contratos_model->get_servicos($curso)->result_array();
		$parcelas = $this->contratos_model->get_parcelas($curso);

		exit(json_encode(array("condicoes"=>$condicoes, "turmas"=>$turmas, "disciplinas"=>$disciplinas, "produtos"=>$produtos, "servicos"=>$servicos, "parcelas"=>$parcelas)));
	}

	public function add_in_segment()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contratos", "add_in_segment"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
			$this->load->library('voxy');

            $msgs = "";
            $post_seguimentos = $this->input->post('seguimentos');
            if(count($post_seguimentos) > 0) {
                $admin_pref = $this->preferences_model->get_interface('admin_preferences');
                $qtd_alunos = $admin_pref->qtd_alunos;
                foreach ($post_seguimentos as $key_seg => $seg) {
                    $seg_id = $this->ead_segments_model->get_by_label($seg)->row()->id;
                    $num_rows = $this->ead_segments_model->get_users($seg)->num_rows();
                    $seguimentos_contratos = $this->ead_users_model->get_segments($_POST["contrato_id"])->result();
                    foreach ($seguimentos_contratos as $seguimento_contrato) {
                        if($seguimento_contrato->id == $seg_id) {
                            $qtd_alunos++;
                            break;
                        }
                    }
                    if($num_rows >= $qtd_alunos) {
                        $msgs .= show_alert_error("O seguimento ".$seg." já esta completo.");
                        unset($post_seguimentos[$key_seg]);
                    }
                }
            }
            $this->ead_segments_model->remove_in_segment(array("contrato_id"=>$_POST["contrato_id"]));
            if(count($post_seguimentos) > 0) {
                $params = array();
                $seguimentos_usuario_array = array();
                $seguimentos_usuario_session = $this->ead_segments_model->get()->result();
                if(count($seguimentos_usuario_session) > 0) {
                    foreach ($seguimentos_usuario_session as $seg) {
                        $seguimentos_usuario_array[] = $seg->segment_label;
                    }
                }

                foreach ($post_seguimentos as $seg) {
                    if(in_array($seg, $seguimentos_usuario_array)) {
                    	$contrato = $this->contratos_model->get($data = array('select'=>'cad_orca.*', 'where' => array("cad_orca.id = ".$_POST["contrato_id"]), 'order_by'=>array('cad_orca.id', 'desc')))->row();
                        $this->voxy->make_request("/partners/segments/{$seg}/users/{$contrato->user_id}", "POST", $params);
                    }

                    $seg_id = $this->ead_segments_model->get_by_label($seg)->row()->id;
                    $this->ead_segments_model->add_in_segment(array("contrato_id"=>$_POST['contrato_id'], "segment_id"=>$seg_id));
                }
            }

            $msgs .= show_alert_success("Seguimentos do contrato #".$_POST['contrato_id']." atualizados.");
            $this->session->set_flashdata('message', $msgs);
            $this->auditorias_model->create("ead_segments", "add_in_segment", "Seguimentos do contrato #".$_POST['contrato_id']." atualizados.");

            redirect('contratos/index', 'refresh');
        }
    }

    public function ver_parcelas($id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contratos", "ver_parcelas"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $this->data['pagetitle'] = "<h1>".lang('menu_contratos_parcelas')."</h1>";
            
            /* Breadcrumbs */
            $this->breadcrumbs->unshift(2, lang('menu_contratos_parcelas'), 'contratos/parcelas');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['parcelas'] = $this->contratos_model->get_parcelas_contrato($id);

            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/contratos/parcelas', $this->data);
        }
    }

    public function anular($id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contratos", "anular"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $contrato = $this->contratos_model->get(array('select'=>'cad_orca.*', 'where'=>'cad_orca.id = '.$_POST['contrato_id'], 'order_by'=>array('cad_orca.id', 'desc')));
            if($contrato->num_rows() == 0) {
                $this->session->set_flashdata('message', show_alert_error("Contrato não existente ou seu usuário não possui privilégios."));
                redirect('contratos', 'refresh');
            }

            $status_anulado = $this->contratos_model->get_status_anulado($contrato->row()->filial)->row()->LIG_ID_ANULADO;


            if($this->ion_auth_model->is_assinatura($_POST["contrato_id"]) && $this->ion_auth->has_permissao("iugu", "suspender_assinatura")) {

                $assinatura = $this->contratos_model->get_iugu_subscriptions($_POST["contrato_id"]);

                if($assinatura->num_rows() == 1) {
                  
                    $unidade = $this->contratos_model->get_unidade($contrato->row()->filial);

                    $this->load->library('iugu_library');

                    $iugu = new Iugu_library();
                    $iugu->setToken($unidade->row()->iugu_token);

                    $retorno = $iugu->suspender_assinatura($assinatura->row()->iugu_id);

                    if($retorno["erro"] == true) {
                        $this->session->set_flashdata('message', show_alert_error($retorno["msg"]));
                        redirect('contratos', 'refresh');
                    }
                }

            }


            if($this->contratos_model->update($_POST['contrato_id'], array("status" => $status_anulado, "ultima_atualizacao" => date("Y-m-d H:i:s"), "sincronizado" => "N"))) {
                $this->session->set_flashdata('message', show_alert_success("Contrato #".$_POST["contrato_id"]." anulado."));

                $this->auditorias_model->create("iugu", "suspender_assinatura", "Suspensa assinatura na Iugu do contrato: ".$_POST["contrato_id"].".");

                //provisorio
                $this->ion_auth->historico_create(array("LIG_ID_CLIENTE"=>$contrato->row()->user_id,"LIG_ID_USUARIO"=>$this->session->userdata('user_id'),"REGISTRO"=>"true","STATUS"=>"8","MENSAGEM"=>"Contrato ".$_POST["contrato_id"]." anulado.","DATA_REGISTRO"=>date("Y-m-d"),"HORA_REGISTRO"=>date("H:i:s")));
            } else {
                $this->session->set_flashdata('message', show_alert_error("Ocorreu um erro ao anular o contrato, tente novamente ou entre em contato com o administrador."));
            }
            redirect('contratos', 'refresh');
        }
    }

    public function alterar_desempenho()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contratos", "alterar_desempenho"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            if($this->contratos_model->update($_POST['contrato_id'], array("desempenho_geral" => $_POST["desempenho_geral"], "ultima_atualizacao" => date("Y-m-d H:i:s"), "sincronizado" => "N"))) {
                $this->session->set_flashdata('message', show_alert_success("Contrato #".$_POST["contrato_id"].", desempenho geral alterado para '".$_POST["desempenho_geral"]."'."));

                $contrato = $this->contratos_model->get(array('select'=>'cad_orca.user_id', 'where'=>'cad_orca.id = '.$_POST['contrato_id'], 'order_by'=>array('cad_orca.id', 'desc')));
                //provisorio
                $this->ion_auth->historico_create(array("LIG_ID_CLIENTE"=>$contrato->row()->user_id,"LIG_ID_USUARIO"=>$this->session->userdata('user_id'),"REGISTRO"=>"true","STATUS"=>"6","MENSAGEM"=>"Contrato ".$_POST["contrato_id"].", desempenho geral alterado para '".$_POST["desempenho_geral"]."'.","DATA_REGISTRO"=>date("Y-m-d"),"HORA_REGISTRO"=>date("H:i:s")));
            } else {
                $this->session->set_flashdata('message', show_alert_error("Ocorreu um erro ao alterar o contrato, tente novamente ou entre em contato com o administrador."));
            }
            redirect('contratos', 'refresh');
        }
    }

    public function ver_pdf($contrato_id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contratos", "ver_pdf"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $nome_arquivo = "contrato".$contrato_id.".pdf";
            $caminho_arquivo = "./upload/contratos/".$nome_arquivo;
            if(file_exists("./upload/contratos/".$nome_arquivo)) {
                $this->load->helper('download');
                $data = file_get_contents(str_replace("https", "http", base_url($caminho_arquivo)));
                force_download($nome_arquivo, $data);
            } else {
                $this->session->set_flashdata('message', show_alert_error("Arquivo PDF do contrato não encontrado."));
                redirect('contratos', 'refresh');
            }
        }
    }
	
	public function verificar_anulacao() {
        $html = "";

        if($this->ion_auth_model->is_assinatura($_POST["orc_id"]) && $this->ion_auth->has_permissao("iugu", "suspender_assinatura")) {
            
            $assinatura = $this->contratos_model->get_iugu_subscriptions($_POST["orc_id"]);

            if($assinatura->num_rows() == 1)
                $html .= "<p>Esta ação suspenderá a assinatura: <b>".$assinatura->row()->iugu_id."</b></p>";

        }

        $html .= "<p>Deseja realmente anular o contrato <b>#".$_POST["orc_id"]."</b> ?</p>";

        $retorno["erro"] = false;
        $retorno["html"] = $html;

        exit(json_encode($retorno));
    }
	
}
