<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cupom extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/cupom');
        $this->load->model('cupom_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_cupom'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_cupom'), 'admin/cupom');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("cupom", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
        	if(@$_POST["data_criacao_inicio"] != "") {
                $data_criacao_inicio = convertDateBRToEU($_POST["data_criacao_inicio"]);
                $this->data['data_criacao_inicio'] = $data_criacao_inicio;
            } else
                $data_criacao_inicio = "";
            if(@$_POST["data_criacao_fim"] != "") {
                $data_criacao_fim = convertDateBRToEU($_POST["data_criacao_fim"]);
                $this->data['data_criacao_fim'] = $data_criacao_fim;
            } else
                $data_criacao_fim = "";
            if(@$_POST["data_expiracao_inicio"] != "") {
                $data_expiracao_inicio = convertDateBRToEU($_POST["data_expiracao_inicio"]);
                $this->data['data_expiracao_inicio'] = $data_expiracao_inicio;
            } else
                $data_expiracao_inicio = "";
            if(@$_POST["data_expiracao_fim"] != "") {
                $data_expiracao_fim = convertDateBRToEU($_POST["data_expiracao_fim"]);
                $this->data['data_expiracao_fim'] = $data_expiracao_fim;
            } else
                $data_expiracao_fim = "";

            $where = "";
            if($data_criacao_inicio != "")
                $where .= " AND c.data_criacao >= '".$data_criacao_inicio."'";
            if($data_criacao_fim != "") {
                $where .= " AND c.data_criacao <= '".$data_criacao_fim."'";
            }
            if($data_expiracao_inicio != "")
                $where .= " AND c.data_expiracao >= '".$data_expiracao_inicio."'";
            if($data_expiracao_fim != "") {
                $where .= " AND c.data_expiracao <= '".$data_expiracao_fim."'";
            }

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            $grupos_permissoes = $this->ion_auth->get_users_groups_permissoes();
        	$usuario_limitado = false;
        	if($grupos_permissoes->num_rows() > 0) {
        		foreach ($grupos_permissoes->result() as $value) {
        			$permissoes = unserialize($value->permissoes);
        	
        			if(count($permissoes) > 0) {
        				foreach ($permissoes as $_k => $_p) {
    						if($value->limited == 1 && $_k == "cupom" && in_array("index", $_p)) {
		        				$usuario_limitado = true;
		        			}
        				}
        			}
        		}
        	}

        	if($usuario_limitado == true)
            	$where .= " AND (vendedor_id = ".$this->session->userdata('user_id')." OR criado_por = ".$this->session->userdata('user_id').")";

    		$this->data['cupons'] = $this->cupom_model->get(null, $where)->result();

            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/cupom/index', $this->data);
        }
	}

	public function isentar_matricula()
	{
	}

	public function alterar_vendedor()
	{
	}

	public function alterar_data_expiracao()
	{
	}

	public function create($id = null)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("cupom", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_cupom_create'), 'cupom/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('unidades', 'lang:unidades', 'required');
		$this->form_validation->set_rules('cursos', 'lang:cursos', 'required');

		if($_POST && $this->form_validation->set_value('cursos')) {
			$curso_verif = $this->cupom_model->get_curso($this->input->post('cursos'));
			if($curso_verif->row()->tipo != 2)
				$this->form_validation->set_rules('turmas', 'lang:turmas', 'required');
		}

		$this->form_validation->set_rules('tipo_entrada', 'lang:tipo_entrada', 'required');
		$this->form_validation->set_rules('condicoes[]', 'lang:condicoes', 'required');
		if($this->input->post('sequencial') == 1)
  			$this->form_validation->set_rules('data_inicio_sequencial', 'lang:data_inicio_sequencial', 'required|validar_data');

		if($this->ion_auth->has_permissao("cupom", "alterar_vendedor"))
			$this->form_validation->set_rules('vendedor', 'lang:vendedor', 'required');

		$prefs = $this->cupom_model->get_admin_preferences();
		if($this->ion_auth->has_permissao("cupom", "alterar_data_expiracao"))
			$this->form_validation->set_rules('data_expiracao', 'lang:data_expiracao', 'required|validar_data');
		else {
			if($prefs->horas_expiracao_cupom <= 0) {
				$this->session->set_flashdata('message', show_alert_error("Horas adicionais do cupom não preenchida nas preferências da empresa."));
				redirect('cupom', 'refresh');
			}
		}

		$this->data['can_alterar_vendedor'] = false;
		$this->data['can_alterar_data_expiracao'] = false;
		$this->data['can_isentar_matricula'] = false;

		$dados["vendedor_id"] = $this->session->userdata('user_id');
		
		if($this->ion_auth->has_permissao("cupom", "alterar_vendedor")) {
			$dados["vendedor_id"] = $this->input->post('vendedor');
			$this->data['can_alterar_vendedor'] = true;
		}
		
		$dados["data_expiracao"] = date("Y-m-d H:i", strtotime("+".$prefs->horas_expiracao_cupom." hours"));
		if($this->ion_auth->has_permissao("cupom", "alterar_data_expiracao")) {
			$separa_data_expiracao = explode(" ", $this->input->post("data_expiracao"));
			if($_POST)
				$dados["data_expiracao"] = convertDateBRToEU($separa_data_expiracao[0])." ".$separa_data_expiracao[1];
			$this->data['can_alterar_data_expiracao'] = true;
		}

		$dados["isento_matricula"] = 0;
		if($this->ion_auth->has_permissao("cupom", "isentar_matricula")) {
			$dados["isento_matricula"] = (@$this->input->post('isento_matricula')) ? $this->input->post('isento_matricula') : 0;
			$this->data['can_isentar_matricula'] = true;

			if(@$this->input->post('isento_matricula') == 0 || !@$this->input->post('isento_matricula')) {
				$this->form_validation->set_rules('entrada', 'lang:entrada', 'required');
			}
			$dados["entrada"] = ($this->input->post('entrada')) ? $this->input->post('entrada') : null;
		} else {
			$dados["entrada"] = $this->input->post('entrada');
		}

		$dados["sequencial"] = ($this->input->post('sequencial')) ? $this->input->post('sequencial') : 0;
		$vencimentos = array();
		if($this->input->post('sequencial') == 1) {
			$dados["data_inicio_sequencial"] = convertDateBRToEU($this->input->post('data_inicio_sequencial'));

			if(@count($_POST["condicao_hidden"]) > 0) {
				$k = 0;
				$dt_inicio = $dados["data_inicio_sequencial"];
				foreach ($_POST["condicao_hidden"] as $cond) {
					$condicao = $this->cupom_model->get_condicao($cond);

					$vencimentos[$k]["desc"] = $condicao->row()->DESCRICAO;
					$vencimentos[$k]["parc"] = $condicao->row()->NUM_PARCELAS;
					$vencimentos[$k]["primeira_parc"] = date("d/m/Y", strtotime($dt_inicio));
					$vencimentos[$k]["ultima_parc"] = date("d/m/Y", strtotime($dt_inicio.' +'.$condicao->row()->NUM_PARCELAS.'month'));
					$valor_servicos = $this->cupom_model->get_valor_servicos($cond);
					$vencimentos[$k]["vlr_bruto"] = number_format($valor_servicos, 2, ',', '');
					$vencimentos[$k]["desconto"] = number_format($condicao->row()->VALOR_DESCONTO_PONTUALIDADE, 2, ',', '');
					$vencimentos[$k]["vlr_liquido"] = number_format($valor_servicos - $condicao->row()->VALOR_DESCONTO_PONTUALIDADE, 2, ',', '');

					$dt_inicio = date("Y-m-d", strtotime($dt_inicio.' +'.($condicao->row()->NUM_PARCELAS + 1).'month'));
					$k++;
				}
			}
		} else {
			$dados["data_inicio_sequencial"] = null;
		}

		$cupom_gerado = $this->gerar_cupom_unico();

		$dados["unidade_id"] = $this->input->post('unidades');
		$dados["tipo_entrada"] = $this->input->post('tipo_entrada');
		$dados["criado_por"] = $this->session->userdata('user_id');
		$dados["cupom"] = $cupom_gerado;
		$dados["celular"] = $this->input->post('celular');
		$dados["email"] = $this->input->post('email');
		$dados["curso_id"] = $this->input->post('cursos');
		$dados["turma_id"] = $this->input->post('turmas');

		if ($this->form_validation->run() == TRUE && $id = $this->cupom_model->create($dados))
		{
			if($this->input->post('sequencial') == 1) {
				$array_post_condicoes = array();
				foreach ($_POST['condicao_hidden'] as $key => $value) {
					$array_post_condicoes[] = array(
						"id" => $value,
						"order" => $_POST['condicao_hidden_order'][$key],
					);
				}
			} else {
				$array_post_condicoes = array();
				foreach ($_POST['condicoes'] as $key => $value) {
					$array_post_condicoes[] = array(
						"id" => $value,
						"order" => 0,
					);
				}
			}
			$this->cupom_model->create_vinculo($id, $array_post_condicoes);

			$this->cupom_model->create_vinculo_disciplina($id, @$_POST['disciplinas']);

			$this->session->set_flashdata('message', show_alert_success($this->cupom_model->messages()));
			redirect('cupom/view_resend/'.$id, 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->cupom_model->errors() ? show_alert_error($this->cupom_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            if($this->ion_auth->has_permissao("cupom", "alterar_vendedor")) {
	            $vendedores_array = array(""=>"Selecione");
	            $vendedores = $this->cupom_model->get_vendedores(@$_POST["unidades"]);
	            if($vendedores->num_rows() > 0) {
	            	foreach ($vendedores->result() as $value) {
	            		$vendedores_array[$value->id] = $value->first_name." ".$value->last_name." - ".$value->email;
	            	}
	            }
	            $this->data['vendedor'] = array(
					'name'  => 'vendedor',
					'id'    => 'vendedor',
					'type'  => 'text',
	                'class' => 'form-control singleInputDynamic',
	                'options' => $vendedores_array,
		            'onchange' => 'loadUnidades(this.value)',
					'selected' => $this->form_validation->set_value('vendedor')
				);
			}
			
			$this->data['vencimentos'] = $vencimentos;

			$condicoes = $this->cupom_model->get_condicoes($this->form_validation->set_value('unidades'), $this->form_validation->set_value('vendedor'), $this->form_validation->set_value('cursos'));

			$html_entradas = '';
			$count_entradas = 0;
			$html_condicoes = '';
			$count_condicoes = 0;
			if($condicoes) {
				if($condicoes->num_rows() > 0) {
					$html_entradas = '<ul class="list-group">';
					$html_condicoes = '<ul class="list-group">';
					foreach ($condicoes->result() as $key => $value) {
		        		if(strtoupper($value->entrada_matricula) == "TRUE") {
		        			$count_entradas++;
			        		$html_entradas .= '<li class="list-group-item">
			                    <div class="radio">
			                        <label><input type="radio" name="entrada" value="'.$value->ID.'"';
			                if($value->ID == @$_POST['entrada'])
		                    	$html_entradas .= 'checked';
			                $html_entradas .= '>'.$value->DESCRICAO.'</label>
			                    </div>
			                </li>';
		                } else {
		                	$count_condicoes++;
		                	$html_condicoes .= '<li class="list-group-item">
		                        <div class="checkbox">
		                            <label><input type="checkbox" class="condicao" name="condicoes[]" value="'.$value->ID.'"';
		                    if(@$_POST['condicoes']) {
			                    if(in_array($value->ID, $_POST['condicoes']))
			                    	$html_condicoes .= 'checked';
		                    }
		                    $html_condicoes .='>'.$value->DESCRICAO.'</label>
		                        </div>
		                    </li>';
		                }
					}
			        $html_entradas .= '</ul>';
			        $html_condicoes .= '</ul>';
		        }
			}

			$this->data['entradas']["count"] = $count_entradas;
			$this->data['entradas']["html"] = $html_entradas;
			$this->data['condicoes']["count"] = $count_condicoes;
			$this->data['condicoes']["html"] = $html_condicoes;

			if(@$_POST["vendedor"] || $this->data['can_alterar_vendedor'] == false) {
				if($this->data['can_alterar_vendedor'] == false) {
					$unidades_array = array(''=>'Selecione');
	            	$unidades = $this->cupom_model->get_unidades_by_condicao($this->session->userdata('user_id'));
			        if($unidades->num_rows() > 0) {
			        	foreach ($unidades->result() as $value) {
			        		$unidades_array[$value->id] = $value->unidade;
			        	}
			        }
				} else {
	            	$unidades_array = array(''=>'Selecione');
	            	$unidades = $this->cupom_model->get_unidades_by_condicao($_POST["vendedor"]);
			        if($unidades->num_rows() > 0) {
			        	foreach ($unidades->result() as $value) {
			        		$unidades_array[$value->id] = $value->unidade;
			        	}
			        }
		        }

		        if(@$_POST["unidades"]) {
		        	$cursos_array = array(''=>'Selecione');
	            	$cursos = $this->cupom_model->get_cursos($_POST["unidades"]);
			        if($cursos->num_rows() > 0) {
			        	$cursos_array = array(''=>'Selecione um curso');
			        	foreach ($cursos->result() as $value) {
			        		$cursos_array[$value->ID_CURSO] = $value->DESCRICAO;
			        	}
			        }
		        } else {
		        	$cursos_array = array(''=>'Selecione uma unidade');
		        }

		        if(@$_POST["cursos"]) {
		        	$turmas_array = array(''=>'Selecione');
	            	$turmas = $this->cupom_model->get_turmas($_POST["unidades"], $_POST["cursos"]);
			        if($turmas->num_rows() > 0) {
			        	$turmas_array = array(''=>'Selecione um curso');
			        	foreach ($turmas->result() as $value) {
			        		$turmas_array[$value->ID] = $value->DESCRICAO;
			        	}
			        }

			        $disciplinas_array = array(''=>'Selecione');
	            	$disciplinas = $this->cupom_model->get_disciplinas($_POST["cursos"]);
			        if($disciplinas->num_rows() > 0) {
			        	$disciplinas_array = array();
			        	foreach ($disciplinas->result() as $value) {
			        		$disciplinas_array[$value->ID_DISCIPLINA] = $value->DESCRICAO;
			        	}
			        }
		        } else {
		        	$turmas_array = array(''=>'Selecione um curso');
            		$disciplinas_array = array();
		        }
			}
            else {
            	$unidades_array = array(''=>'Selecione um vendedor');
            	$cursos_array = array(''=>'Selecione uma unidade');
            	$turmas_array = array(''=>'Selecione um curso');
            	$disciplinas_array = array();
            }
            // $unidades = $this->ion_auth->get_unidades_permitidas();
            // foreach ($this->data['condicoes']->result() as $value) {
            // 	$unidades_array[$value->unidade_id] = $value->unidade;
            // }
            $this->data['unidades'] = array(
				'name'  => 'unidades',
				'id'    => 'unidades',
	            'class' => 'form-control',
	            'onchange' => 'loadCursos(this.value)',
	            'options' => $unidades_array,
				'selected' => $this->form_validation->set_value('unidades')
			);

			$this->data['tipo_entrada'] = array(
				'name'  => 'tipo_entrada',
				'id'    => 'tipo_entrada',
	            'class' => 'form-control',
	            'options' => array("ambos"=>"Ambos","boleto"=>"Boleto","cartao_credito"=>"Cartão de Crédito"),
				'selected' => $this->form_validation->set_value('tipo_entrada')
			);

			$this->data['data_expiracao'] = date("Y-m-d H:i", strtotime("+".$prefs->horas_expiracao_cupom." hours"));

			$this->data['celular'] = array(
				'name'  => 'celular',
				'id'    => 'celular',
				'type'  => 'text',
                'class' => 'form-control',
                'value' => ($this->input->post('celular'))?$this->input->post('celular'):''
			);
			$this->data['email'] = array(
				'name'  => 'email',
				'id'    => 'email',
				'type'  => 'text',
                'class' => 'form-control',
                'value' => ($this->input->post('email'))?$this->input->post('email'):''
			);
			$this->data['isento_matricula'] = array(
				'name'  => 'isento_matricula',
				'id'    => 'isento_matricula',
                'class' => 'checkbox',
                'checked' => (@$this->input->post('isento_matricula') == 1) ? 1 : 0,
				'value' => '1'
			);
			$this->data['isento_matricula_checked'] = (@$this->input->post('isento_matricula') == 1) ? 1 : 0;

            $this->data['cursos'] = array(
				'name'  => 'cursos',
				'id'    => 'cursos',
	            'class' => 'form-control list-curso',
	            'onchange' => 'loadCondicoes(this.value)',
	            'options' => $cursos_array,
				'selected' => $this->form_validation->set_value('cursos')
			);

			$this->data['sequencial'] = array(
				'name'  => 'sequencial',
				'id'    => 'sequencial',
                'class' => 'checkbox',
                'checked' => (@$this->input->post('sequencial') == 1) ? 1 : 0,
				'value' => '1'
			);
			$this->data['sequencial_checked'] = (@$this->input->post('sequencial') == 1) ? 1 : 0;


			$this->data['data_inicio_sequencial'] = (@$this->input->post('data_inicio_sequencial')) ? convertDateBRToEU($this->input->post('data_inicio_sequencial')) : "";

			$this->data['condicao_hidden'] = @$_POST["condicao_hidden"];
			$this->data['condicao_hidden_order'] = @$_POST["condicao_hidden_order"];

			$this->data['turmas'] = array(
				'name'  => 'turmas',
				'id'    => 'turmas',
	            'class' => 'form-control',
	            'options' => $turmas_array,
				'selected' => $this->form_validation->set_value('turmas')
			);

			$this->data['disciplinas'] = array(
				'name'  => 'disciplinas[]',
				'id'    => 'disciplinas',
				'class' => 'multipleSelect',
  				'multiple' => 'multiple',
	            'options' => $disciplinas_array,
				'selected' => (@$this->input->post('disciplinas')) ? $this->input->post('disciplinas') : ''
			);

            /* Load Template */
            $this->template->admin_render('admin/cupom/create', $this->data);
		}
	}

	private function gerar_cupom_unico() {
		$cupom_gerado = geraCupons(8);
		if($this->cupom_model->cupom_exist($cupom_gerado)) {
			$this->gerar_cupom_unico();
		}
		return $cupom_gerado;
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("cupom", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_cupom_edit'), 'cupom/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$cupom = $this->cupom_model->get($id);

		if($cupom->num_rows() == 0) {
        	$this->session->set_flashdata('message', show_alert_error("Cupom inexistente!"));
        	redirect('cupom', 'refresh');
        }
        $cupom = $cupom->row();

		/* Validate form input */
		$this->form_validation->set_rules('unidades', 'lang:unidades', 'required');
		$this->form_validation->set_rules('cursos', 'lang:cursos', 'required');
		$this->form_validation->set_rules('tipo_entrada', 'lang:tipo_entrada', 'required');
		$this->form_validation->set_rules('condicoes[]', 'lang:condicoes', 'required');

		if($this->ion_auth->has_permissao("cupom", "isentar_matricula")) {
			$dados["isento_matricula"] = (@$this->input->post('isento_matricula')) ? $this->input->post('isento_matricula') : 0;

			if(@$this->input->post('isento_matricula') == 0 || !@$this->input->post('isento_matricula')) {
				$this->form_validation->set_rules('entrada', 'lang:entrada', 'required');
			}
			$dados["entrada"] = ($this->input->post('entrada')) ? $this->input->post('entrada') : null;
		} else {
			$dados["entrada"] = $this->input->post('entrada');
		}

		if($_POST && $this->form_validation->set_value('cursos')) {
			$curso_verif = $this->cupom_model->get_curso($this->input->post('cursos'));
			if($curso_verif->row()->tipo != 2)
				$this->form_validation->set_rules('turmas', 'lang:turmas', 'required');
		}

		if($this->ion_auth->has_permissao("cupom", "alterar_vendedor"))
			$this->form_validation->set_rules('vendedor', 'lang:vendedor', 'required');

		if($this->ion_auth->has_permissao("cupom", "alterar_data_expiracao"))
			$this->form_validation->set_rules('data_expiracao', 'lang:data_expiracao', 'required|validar_data');

		$this->data['can_alterar_vendedor'] = false;
		$this->data['can_alterar_data_expiracao'] = false;

		if($this->ion_auth->has_permissao("cupom", "alterar_vendedor")) {
			$dados["vendedor_id"] = $this->input->post('vendedor');
			$this->data['can_alterar_vendedor'] = true;
		}

		$dados["isento_matricula"] = 0;
		$this->data['can_isentar_matricula'] = false;
		if($this->ion_auth->has_permissao("cupom", "isentar_matricula")) {
			$dados["isento_matricula"] = $this->input->post('isento_matricula');
			$this->data['can_isentar_matricula'] = true;
		}

		$dados["sequencial"] = ($this->input->post('sequencial')) ? $this->input->post('sequencial') : 0;
		$vencimentos = array();
		if($this->input->post('sequencial') == 1 || $cupom->sequencial == 1) {
			if($_POST && $this->input->post('sequencial') == 1) {
				$dados["data_inicio_sequencial"] = convertDateBRToEU($this->input->post('data_inicio_sequencial'));
				$rodar_condicoes = $_POST["condicao_hidden"];
			}
			else {
				$dados["data_inicio_sequencial"] = $cupom->data_inicio_sequencial;
				foreach ($this->cupom_model->get_vinculo($id)->result() as $value) {
					$rodar_condicoes[] = $value->condicao_id;
				}
			}

			if(@count($rodar_condicoes) > 0) {
				$k = 0;
				$dt_inicio = $dados["data_inicio_sequencial"];
				foreach ($rodar_condicoes as $cond) {
					$condicao = $this->cupom_model->get_condicao($cond);

					$vencimentos[$k]["desc"] = $condicao->row()->DESCRICAO;
					$vencimentos[$k]["parc"] = $condicao->row()->NUM_PARCELAS;
					$vencimentos[$k]["primeira_parc"] = date("d/m/Y", strtotime($dt_inicio));
					$vencimentos[$k]["ultima_parc"] = date("d/m/Y", strtotime($dt_inicio.' +'.$condicao->row()->NUM_PARCELAS.'month'));
					$valor_servicos = $this->cupom_model->get_valor_servicos($cond);
					$vencimentos[$k]["vlr_bruto"] = number_format($valor_servicos, 2, ',', '');
					$vencimentos[$k]["desconto"] = number_format($condicao->row()->VALOR_DESCONTO_PONTUALIDADE, 2, ',', '');
					$vencimentos[$k]["vlr_liquido"] = number_format($valor_servicos - $condicao->row()->VALOR_DESCONTO_PONTUALIDADE, 2, ',', '');

					$dt_inicio = date("Y-m-d", strtotime($dt_inicio.' +'.($condicao->row()->NUM_PARCELAS + 1).'month'));
					$k++;
				}
			}
		} else {
			$dados["data_inicio_sequencial"] = null;
		}
		
		$prefs = $this->cupom_model->get_admin_preferences();
		if($this->ion_auth->has_permissao("cupom", "alterar_data_expiracao")) {
			$separa_data_expiracao = explode(" ", $this->input->post("data_expiracao"));
			if($_POST)
				$dados["data_expiracao"] = convertDateBRToEU($separa_data_expiracao[0])." ".$separa_data_expiracao[1];
			$this->data['can_alterar_data_expiracao'] = true;
		}
		$dados["unidade_id"] = $this->input->post('unidades');
		$dados["tipo_entrada"] = $this->input->post('tipo_entrada');
		$dados["celular"] = $this->input->post('celular');
		$dados["email"] = $this->input->post('email');
		$dados["curso_id"] = $this->input->post('cursos');
		$dados["turma_id"] = $this->input->post('turmas');

		if ($this->form_validation->run() == TRUE && $this->cupom_model->update($id, $dados))
		{
			if($this->input->post('sequencial') == 1) {
				$array_post_condicoes = array();
				foreach ($_POST['condicao_hidden'] as $key => $value) {
					$array_post_condicoes[] = array(
						"id" => $value,
						"order" => $_POST['condicao_hidden_order'][$key],
					);
				}
			} else {
				$array_post_condicoes = array();
				foreach ($_POST['condicoes'] as $key => $value) {
					$array_post_condicoes[] = array(
						"id" => $value,
						"order" => 0,
					);
				}
			}
			$this->cupom_model->remove_vinculo($id);
			$this->cupom_model->create_vinculo($id, $array_post_condicoes);

			$this->cupom_model->remove_vinculo_disciplina($id);
			$this->cupom_model->create_vinculo_disciplina($id, @$_POST['disciplinas']);

			$this->session->set_flashdata('message', show_alert_success($this->cupom_model->messages()));
			redirect('cupom/view_resend/'.$id, 'refresh');
		} else {
			if($_POST)
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->cupom_model->errors() ? show_alert_error($this->cupom_model->errors()) : show_alert_error($this->session->flashdata('message'))));
		}

        $this->data['cupom'] = $cupom;

        if($this->ion_auth->has_permissao("cupom", "alterar_vendedor")) {
			$vendedores_array = array(""=>"Selecione");
	        $vendedores = $this->cupom_model->get_vendedores($this->form_validation->set_value('unidades', $cupom->unidade_id));
	        if($vendedores->num_rows() > 0) {
	        	foreach ($vendedores->result() as $value) {
	        		$vendedores_array[$value->id] = $value->first_name." ".$value->last_name." - ".$value->email;
	        	}
	        }
	        $this->data['vendedor'] = array(
				'name'  => 'vendedor',
				'id'    => 'vendedor',
	            'class' => 'form-control singleInputDynamic',
	            'options' => $vendedores_array,
	            'onchange' => 'loadUnidades(this.value)',
				'selected' => $this->form_validation->set_value('vendedor', $cupom->vendedor_id)
			);
		}

		$lista_cond_vinculadas = array();
		if($this->input->post('condicoes'))
			$lista_cond_vinculadas = $this->input->post('condicoes');
		else {
			$i = 0;
			foreach ($this->cupom_model->get_vinculo($id)->result() as $vinc) {
				$lista_cond_vinculadas[] = $vinc->condicao_id;
				$condicoes_hidden_array[$i] = $vinc->condicao_id;
				$condicoes_hidden_order_array[$i] = $vinc->order;
				$i++;
			}
			$condicoes_hidden_array = $lista_cond_vinculadas;
		}
		
		$this->data['condicoes_vinculadas'] = $lista_cond_vinculadas;


		$this->data['vencimentos'] = $vencimentos;

        $condicoes = $this->cupom_model->get_condicoes($this->form_validation->set_value('unidades', $cupom->unidade_id), $this->form_validation->set_value('vendedor', $cupom->vendedor_id), $this->form_validation->set_value('cursos', $cupom->curso_id));

		$html_entradas = '';
		$count_entradas = 0;
		$html_condicoes = '';
		$count_condicoes = 0;
		if($condicoes) {
			if($condicoes->num_rows() > 0) {
				$html_entradas = '<ul class="list-group">';
				$html_condicoes = '<ul class="list-group">';
				foreach ($condicoes->result() as $key => $value) {
	        		if(strtoupper($value->entrada_matricula) == "TRUE") {
	        			$count_entradas++;
		        		$html_entradas .= '<li class="list-group-item">
		                    <div class="radio">
		                        <label><input type="radio" name="entrada" value="'.$value->ID.'"';
		                if($value->ID == $cupom->entrada)
	                    	$html_entradas .= ' checked';
		                $html_entradas .= '>'.$value->DESCRICAO.'</label>
		                    </div>
		                </li>';
	                } else {
	                	$count_condicoes++;
	                	$html_condicoes .= '<li class="list-group-item">
	                        <div class="checkbox">
	                            <label><input type="checkbox" class="condicao" name="condicoes[]" value="'.$value->ID.'"';
	                    if(in_array($value->ID, $lista_cond_vinculadas))
	                    	$html_condicoes .= ' checked';
	                    $html_condicoes .= '>'.$value->DESCRICAO.'</label>
	                        </div>
	                    </li>';
	                }
				}
		        $html_entradas .= '</ul>';
		        $html_condicoes .= '</ul>';
	        }
		}

		$this->data['entradas']["count"] = $count_entradas;
		$this->data['entradas']["html"] = $html_entradas;
		$this->data['condicoes']["count"] = $count_condicoes;
		$this->data['condicoes']["html"] = $html_condicoes;


    	$unidades_array = array(''=>'Selecione');
    	$unidades = $this->cupom_model->get_unidades_by_condicao($this->form_validation->set_value('vendedor', $cupom->vendedor_id));
        if($unidades->num_rows() > 0) {
        	foreach ($unidades->result() as $value) {
        		$unidades_array[$value->id] = $value->unidade;
        	}
        }
	
        $this->data['unidades'] = array(
			'name'  => 'unidades',
			'id'    => 'unidades',
            'class' => 'form-control',
            'onchange' => 'loadCursos(this.value)',
            'options' => $unidades_array,
			'selected' => $this->form_validation->set_value('unidades', $cupom->unidade_id)
		);


		$this->data['tipo_entrada'] = array(
			'name'  => 'tipo_entrada',
			'id'    => 'tipo_entrada',
            'class' => 'form-control',
            'options' => array("ambos"=>"Ambos","boleto"=>"Boleto","cartao_credito"=>"Cartão de Crédito"),
			'selected' => $this->form_validation->set_value('tipo_entrada', $cupom->tipo_entrada)
		);

		if($this->ion_auth->has_permissao("cupom", "alterar_data_expiracao")) {
			if($_POST) {
				$separa_data_expiracao = explode(" ", $this->input->post("data_expiracao"));
				$this->data['data_expiracao'] = convertDateBRToEU($separa_data_expiracao[0])." ".$separa_data_expiracao[1];
			} else {
				$this->data['data_expiracao'] = $cupom->data_expiracao;
			}
		}

		$this->data['celular'] = array(
			'name'  => 'celular',
			'id'    => 'celular',
			'type'  => 'text',
            'class' => 'form-control',
            'value' => ($this->input->post('celular'))?$this->input->post('celular'):$cupom->celular
		);
		$this->data['email'] = array(
			'name'  => 'email',
			'id'    => 'email',
			'type'  => 'text',
            'class' => 'form-control',
            'value' => ($this->input->post('email'))?$this->input->post('email'):$cupom->email
		);
		$this->data['isento_matricula'] = array(
			'name'  => 'isento_matricula',
			'id'    => 'isento_matricula',
            'class' => 'checkbox',
            'checked' => (@$this->input->post('isento_matricula')) ? $this->input->post('isento_matricula') : $cupom->isento_matricula,
			'value' => '1'
		);
		$this->data['isento_matricula_checked'] = (@$this->input->post('isento_matricula')) ? $this->input->post('isento_matricula') : $cupom->isento_matricula;

		$cursos_array = array(''=>'Selecione');
    	$cursos = $this->cupom_model->get_cursos($this->form_validation->set_value('unidades', $cupom->unidade_id));
        if($cursos->num_rows() > 0) {
        	foreach ($cursos->result() as $value) {
        		$cursos_array[$value->ID_CURSO] = $value->DESCRICAO;
        	}
        }
        $this->data['cursos'] = array(
			'name'  => 'cursos',
			'id'    => 'cursos',
            'class' => 'form-control list-curso',
            'onchange' => 'loadCondicoes(this.value)',
            'options' => $cursos_array,
			'selected' => $this->form_validation->set_value('cursos', $cupom->curso_id)
		);

        if($_POST) {
			$this->data['sequencial'] = array(
				'name'  => 'sequencial',
				'id'    => 'sequencial',
		        'class' => 'checkbox',
		        'checked' => (@$this->input->post('sequencial') == 1) ? 1 : 0,
				'value' => '1'
			);
			$this->data['sequencial_checked'] = (@$this->input->post('sequencial') == 1) ? 1 : 0;

			$this->data['data_inicio_sequencial'] = (@$this->input->post('data_inicio_sequencial')) ? convertDateBRToEU($this->input->post('data_inicio_sequencial')) : "";

			$this->data['condicao_hidden'] = @$_POST["condicao_hidden"];
			$this->data['condicao_hidden_order'] = @$_POST["condicao_hidden_order"];
		} else {
			$this->data['sequencial'] = array(
				'name'  => 'sequencial',
				'id'    => 'sequencial',
		        'class' => 'checkbox',
		        'checked' => $cupom->sequencial,
				'value' => '1'
			);
			$this->data['sequencial_checked'] = $cupom->sequencial;

			$this->data['data_inicio_sequencial'] = $cupom->data_inicio_sequencial;

			$this->data['condicao_hidden'] = $condicoes_hidden_array;
			$this->data['condicao_hidden_order'] = $condicoes_hidden_array;
		}

        $turmas_array = array(''=>'Selecione');
    	$turmas = $this->cupom_model->get_turmas((@$_POST["unidades"])?$_POST["unidades"]:$cupom->unidade_id, (@$_POST["cursos"])?$_POST["cursos"]:$cupom->curso_id);
        if($turmas->num_rows() > 0) {
        	$turmas_array = array(''=>'Selecione um curso');
        	foreach ($turmas->result() as $value) {
        		$turmas_array[$value->ID] = $value->DESCRICAO;
        	}
        }
		$this->data['turmas'] = array(
			'name'  => 'turmas',
			'id'    => 'turmas',
            'class' => 'form-control',
            'options' => $turmas_array,
			'selected' => (@$this->input->post('turmas')) ? $this->input->post('turmas') : $cupom->turma_id
		);

        $disciplinas_array = array();
    	$disciplinas = $this->cupom_model->get_disciplinas($this->form_validation->set_value('cursos', $cupom->curso_id));
        if($disciplinas->num_rows() > 0) {
        	$disciplinas_array = array();
        	foreach ($disciplinas->result() as $value) {
        		$disciplinas_array[$value->ID_DISCIPLINA] = $value->DESCRICAO;
        	}
        }
		$this->data['disciplinas'] = array(
			'name'  => 'disciplinas[]',
			'id'    => 'disciplinas',
			'class' => 'multipleSelect',
				'multiple' => 'multiple',
            'options' => $disciplinas_array,
			'selected' => (@$this->input->post('disciplinas')) ? $this->input->post('disciplinas') : ''
		);

        /* Load Template */
        $this->template->admin_render('admin/cupom/edit', $this->data);
	}	

	public function view_resend($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("cupom", "view_resend"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_cupom_view'), 'cupom/view_resend');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
        $cupom = $this->cupom_model->get($id);
        if($cupom->num_rows() == 0) {
        	$this->session->set_flashdata('message', show_alert_error("Cupom inexistente!"));
        	redirect('cupom', 'refresh');
        }

		$cupom = $cupom->row();
		$cupom->celular_formatado = formatCelApi(trim($cupom->celular));

        if($_POST) {
        	if($_POST["acao"] == "sms") {
        		$this->load->library('zenvia');
                $data = array();
                $data['from'] = "MaestriaTech";
                $data['to'] = formatCelApi(trim($cupom->celular));
                if($cupom->unidade_id == 519)
                	$data['msg'] = "Utilize o cupom ".$cupom->cupom." para comprar seu curso em https://www.bslidiomas.com.br/ead";
                else
                	$data['msg'] = "Utilize o cupom ".$cupom->cupom." para comprar seu curso em https://www.bslidiomas.com.br/matricula";

                $zenvia = new Zenvia();
                $retorno_call = $zenvia->zenviaCallSingle("send-sms", $zenvia->createSingleJsonSms($data));
                if($retorno_call["erro"] != 1) {
                	$msg_envio = "Cupom {".$id."} enviado para o celular ".$cupom->celular;
                } else {
                	$msg_envio = "Erro ao enviar o cupom {".$id."}: ".$retorno_call["mensagem"];
                }
                $this->ion_auth->create_auditoria(array("user_id"=>$this->session->userdata('user_id'), "empresa_id"=>$this->session->userdata('empresa_id'), "controlador"=>"cupom", "metodo"=>"enviar", "obs"=>$msg_envio));
                //$data_decode = json_decode($retorno_call["data"], true);
        	}
        	if($_POST["acao"] == "email") {
        		//$this->load->library(array('email'));
        		$this->load->library('phpmailer_library');

                $email_config_db = $this->ion_auth->get_email_config()->row();

                if($email_config_db->test == 1) {
                    $email_config = rowToConfigEmail($email_config_db);
                    $from_email = $email_config_db->from_email;
                    $from_name = $email_config_db->from_name;
                } else {
                    $email_config = $this->config->item('email_config', 'ion_auth');
                    $from_email = $this->config->item('admin_email', 'ion_auth');
                    $from_name = $this->config->item('site_title', 'ion_auth');
                }

                // if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
                // {
                //     $this->email->initialize($email_config);
                // }

                $separa_data_expiracao = explode(" ", $cupom->data_expiracao);
                $data = array(
                    'cupom'          => $cupom->cupom,
                    'data_expiracao' => convertDateEUToBR($separa_data_expiracao[0])." ".$separa_data_expiracao[1]
                );

                $padrao_email = $this->ion_auth->get_email_padrao(12)->row();
                $padrao_email_msg = $padrao_email->mensagem;
                $padrao_email_msg = str_replace("{cupom}", $data["cupom"], $padrao_email_msg);
                $padrao_email_msg = str_replace("{data_expiracao}", $data["data_expiracao"], $padrao_email_msg);

                $padrao_email_ass = $padrao_email->assunto;
                $padrao_email_ass = str_replace("{cupom}", $data["cupom"], $padrao_email_ass);

                // $this->email->clear();
                // $this->email->from($from_email, utf8_decode($from_name));
                // $this->email->to($cupom->email);
                // $this->email->subject(utf8_decode($padrao_email_ass));
                // $this->email->message(utf8_decode($padrao_email_msg));

                // if($this->email->send()) {
                // 	$msg_envio = "Cupom {".$id."} enviado para o e-mail ".$cupom->email;
                // } else {
                // 	$msg_envio = "Erro ao enviar o cupom {".$id."}: ".$this->email->print_debugger();
                // }

                $mail = $this->phpmailer_library->load();
	            $mail->isSMTP();
	            $mail->Host     = $email_config['smtp_host'];
	            $mail->SMTPAuth = true;
	            $mail->Username = $email_config['smtp_user'];
	            $mail->Password = $email_config['smtp_pass'];
	            $mail->SMTPSecure = 'tsl';
	            $mail->Port     = $email_config['smtp_port'];

	            $mail->setFrom($from_email, utf8_decode($from_name));

				$mail->addAddress($cupom->email);

	            $mail->Subject = utf8_decode($padrao_email_ass);
	            $mail->isHTML(true);

	            $mail->Body = utf8_decode($padrao_email_msg);
				if($mail->send()) {
					$msg_envio = "Cupom {".$id."} enviado para o e-mail ".$cupom->email;
				} else {
					$msg_envio = "Erro ao enviar o cupom {".$id."}: ".$this->email->print_debugger();
				}


                $this->ion_auth->create_auditoria(array("user_id"=>$this->session->userdata('user_id'), "empresa_id"=>$this->session->userdata('empresa_id'), "controlador"=>"cupom", "metodo"=>"enviar", "obs"=>$msg_envio));
        	}
        	redirect('cupom/view_resend/'.$id, 'refresh');
        }

		$this->data['cupom'] = $cupom;

		if($cupom->orc_id != "")
			$this->data['condicoes'] = $this->cupom_model->get_condicao_by_orcamento($cupom->orc_id);

		$this->data['auditorias'] = $this->cupom_model->get_historico($id);

		$this->data['cupom_text'] = array(
			'name'  => 'cupom',
			'id'    => 'cupom',
			'type'  => 'text',
            'class' => 'form-control',
 			'readonly' => 'readonly',
            'value' => ($this->input->post('cupom'))?$this->input->post('cupom'):$cupom->cupom
		);
		$this->data['celular'] = array(
			'name'  => 'celular',
			'id'    => 'celular',
			'type'  => 'text',
            'class' => 'form-control',
 			'disabled' => 'disabled',
            'value' => ($this->input->post('celular'))?$this->input->post('celular'):$cupom->celular
		);
		$this->data['email'] = array(
			'name'  => 'email',
			'id'    => 'email',
			'type'  => 'text',
            'class' => 'form-control',
 			'disabled' => 'disabled',
            'value' => ($this->input->post('email'))?$this->input->post('email'):$cupom->email
		);
		
        /* Load Template */
        $this->template->admin_render('admin/cupom/view_resend', $this->data);
	}

	public function carregar_unidades() {
		$retorno = "<option value=''>Selecione</option>";

		if($_POST["vendedor_id"] != "") {
	        $unidades = $this->cupom_model->get_unidades_by_condicao($_POST["vendedor_id"]);
	        if($unidades->num_rows() > 0) {
	        	foreach ($unidades->result() as $value) {
	        		$retorno .= "<option value='".$value->id."'>".$value->unidade."</options>";
	        	}
	        }
        }

        exit($retorno);
	}

	public function carregar_cursos() {
		$retorno = array();
		$html_cursos = '';
		$count_cursos = 0;

        $cursos = $this->cupom_model->get_cursos($_POST["unidade_id"]);
        if($cursos) {
	        if($cursos->num_rows() > 0) {
	        	$html_cursos .= '<option value="">Selecione um curso</option>';
	        	foreach ($cursos->result() as $value) {
                	$count_cursos++;
                	$html_cursos .= '<option value="'.$value->ID_CURSO.'">'.$value->DESCRICAO.'</option>';
	        	}
	        } else {
	        	$html_cursos .= '<option value="">Não existe(m) curso(s) nessa unidade</option>';
	        }
        }

        $retorno["erro"] = false;
        $retorno["curso"]["html"] = $html_cursos;
        $retorno["curso"]["count"] = $count_cursos;
        exit(json_encode($retorno, true));
	}

	public function carregar_condicoes() {
		$retorno = array();
		$html_entradas = '<ul class="list-group">';
		$count_entradas = 0;
		$html_condicoes = '<ul class="list-group">';
		$count_condicoes = 0;
		$html_turmas = '';
		$count_turmas = 0;
		$html_disciplinas = '';
		$count_disciplinas = 0;

		if(@$_POST["vendedor_id"] == "")
			$_POST["vendedor_id"] = $this->session->userdata('user_id');

        $condicoes = $this->cupom_model->get_condicoes($_POST["unidade_id"], $_POST["vendedor_id"], $_POST["curso_id"]);
        $turmas = $this->cupom_model->get_turmas($_POST["unidade_id"], $_POST["curso_id"]);
        $disciplinas = $this->cupom_model->get_disciplinas($_POST["curso_id"]);

        if($condicoes) {
	        if($condicoes->num_rows() > 0) {
	        	foreach ($condicoes->result() as $value) {
	        		if(strtoupper($value->entrada_matricula) == "TRUE") {
	        			$count_entradas++;
		        		$html_entradas .= '<li class="list-group-item">
		                    <div class="radio">
		                        <label><input type="radio" name="entrada" value="'.$value->ID.'">'.$value->DESCRICAO.'</label>
		                    </div>
		                </li>';
	                } else {
	                	$count_condicoes++;
	                	$html_condicoes .= '<li class="list-group-item">
	                        <div class="checkbox">
	                            <label><input type="checkbox" class="condicao" name="condicoes[]" value="'.$value->ID.'">'.$value->DESCRICAO.'</label>
	                        </div>
	                    </li>';
	                }
	        	}
	        }
        }

        if($turmas) {
	        if($turmas->num_rows() > 0) {
            	$html_turmas .= '<option value="">Selecione</option>';
	        	foreach ($turmas->result() as $value) {
                	$count_turmas++;
                	$html_turmas .= '<option value="'.$value->ID.'">'.$value->DESCRICAO.'</option>';
	        	}
	        } else {
	        	$html_turmas .= '<option value="">Não há turma(s) disponível(is) para esse curso.</option>';
	        }
        }

        if($disciplinas) {
	        if($disciplinas->num_rows() > 0) {
	        	foreach ($disciplinas->result() as $value) {
                	$count_disciplinas++;
                	$html_disciplinas .= '<option value="'.$value->ID_DISCIPLINA.'" selected>'.$value->DESCRICAO.'</option>';
	        	}
	        } else {
	        	$html_disciplinas .= '<option value="">Não há disciplina(s) disponível(is) para esse curso.</option>';
	        }
        }

        $html_entradas .= '</ul>';
        $html_condicoes .= '</ul>';
        //$script = "<script>$('.condicao').change(function() { if($('#sequencial').is(':checked')) { if($(this).is(':checked')) { $('<input>').attr({type: 'hidden', id: 'condicao_hidden-'+$(this).val(), name: 'condicao_hidden[]', value: $(this).val()}).appendTo('.list-condicoes'); $('<input>').attr({type: 'hidden', id: 'condicao_hidden_order-'+$(this).val(), name: 'condicao_hidden_order[]', value: $('input[name*=\"condicao_hidden[]\"]').length }).appendTo('.list-condicoes'); } else { $('#condicao_hidden-'+$(this).val()).remove(); $('#condicao_hidden_order-'+$(this).val()).remove(); } $.ajax({url: '".base_url()."'+'cupom/carregar_vencimentos', type: 'POST', data: { condicoes:$('.list-condicoes input[name=\"condicao_hidden[]\"]').serialize(), dt_inicio: $('#datetimehourpickerSequencia').data('date') }, beforeSend: function( xhr ) { $('.vencimentos').find('.loading').show(); }, success: function(data){ var result = $.parseJSON(data); var html = ''; if(result.erro == false) { $.each(result.data, function(key, item) { html += '<tr><td>'+item.desc+'</td><td>'+item.parc.toString()+'</td><td>'+item.primeira_parc+'</td><td>'+item.ultima_parc+'</td><td>'+item.vlr_bruto+'</td><td>'+item.desconto+'</td><td>'+item.vlr_liquido+'</td></tr>'; }); } else {alert(result.msg); return false;} $('.vencimentos').find('.loading').hide(); $('#vencimentos_table').find('tbody').html(html); } }); } else { return false; } });</script>"; 
        $script = "<script type='text/javascript' src='".base_url('assets/xkey/funcoes.js')."'></script>";

        $retorno["erro"] = false;
        $retorno["entrada"]["html"] = $html_entradas;
        $retorno["entrada"]["count"] = $count_entradas;
        $retorno["condicao"]["html"] = $html_condicoes.$script;
        $retorno["condicao"]["count"] = $count_condicoes;
        $retorno["turma"]["html"] = $html_turmas;
        $retorno["turma"]["count"] = $count_turmas;
        $retorno["disciplina"]["html"] = $html_disciplinas;
        $retorno["disciplina"]["count"] = $count_disciplinas;
        exit(json_encode($retorno, true));
	}

	public function carregar_vencimentos() {
		if(@!$_POST["dt_inicio"]) {
			exit(json_encode(array('erro'=>true, 'msg'=>'Data inicial não recebida.')));
		}
		$data = explode("/", $_POST["dt_inicio"]);
		if(!checkdate($data[1], $data[0], $data[2])) {
			exit(json_encode(array('erro'=>true, 'msg'=>'Data inicial incorreta.')));
		}

		parse_str($_POST['condicoes'], $searcharray);
		$dt_inicio = convertDateBRToEU($_POST["dt_inicio"]);

		// exit("<pre>".print_r($searcharray["condicao_hidden"],true)."</pre>");

		$vencimentos = array();

		$k = 0;
		if(@count($searcharray["condicao_hidden"]) > 0) {
			foreach ($searcharray["condicao_hidden"] as $cond) {
				$condicao = $this->cupom_model->get_condicao($cond);
				/*for($i = 1; $i <= $condicao->row()->NUM_PARCELAS; $i++) {
					$vencimentos[$k]["parc"] = $k;
					$vencimentos[$k]["desc"] = $condicao->row()->DESCRICAO;
					$vencimentos[$k]["venc"] = date("d/m/Y", strtotime($dt_inicio.' +'.$k.'month'));
					$k++;
				}*/
				$vencimentos[$k]["desc"] = $condicao->row()->DESCRICAO;
				$vencimentos[$k]["parc"] = $condicao->row()->NUM_PARCELAS;
				$vencimentos[$k]["primeira_parc"] = date("d/m/Y", strtotime($dt_inicio));
				$vencimentos[$k]["ultima_parc"] = date("d/m/Y", strtotime($dt_inicio.' +'.($condicao->row()->NUM_PARCELAS - 1).'month'));
				$valor_servicos = $this->cupom_model->get_valor_servicos($cond);
				$vencimentos[$k]["vlr_bruto"] = number_format($valor_servicos, 2, ',', '');
				$vencimentos[$k]["desconto"] = number_format($condicao->row()->VALOR_DESCONTO_PONTUALIDADE, 2, ',', '');
				$vencimentos[$k]["vlr_liquido"] = number_format($valor_servicos - $condicao->row()->VALOR_DESCONTO_PONTUALIDADE, 2, ',', '');

				$dt_inicio = date("Y-m-d", strtotime($dt_inicio.' +'.($condicao->row()->NUM_PARCELAS).'month'));
				$k++;
			}
		}
		// exit("<pre>".print_r($vencimentos,true)."</pre>");
		exit(json_encode(array('erro'=>false, 'data'=>$vencimentos),true));
	}
}
