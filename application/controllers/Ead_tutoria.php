<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ead_tutoria extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/ead');
        $this->load->helper('functions_helper');
        $this->load->model('ead_users_model');
        $this->load->model('ead_segments_model');
        $this->load->model('ead_tutorias_model');
        $this->load->model('auditorias_model');
        $this->load->model('preferences_model');
        $this->load->model('contatos_interno_model');
        $this->load->library('voxy');
    }


	public function tutoria_geral()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "tutoria_geral"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('menu_tutoria_geral'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_ead_tutoria'), 'ead_tutoria/tutoria_geral');
            $this->breadcrumbs->unshift(2, lang('menu_ead_tutoria_geral'), 'ead_tutoria/tutoria_geral');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            $where = "";
            if(@$_POST['filtro_tutor']) {
                $where .= " es.tutor_id = '".$_POST['filtro_tutor']."'";
            }
            if(@$_POST['filtro_seguimento']) {
                if($where != "")
                    $where .= " AND";
                $where .= " es.id = '".$_POST['filtro_seguimento']."'";
            }

            $join = array(array("ead_contratos_segments ecs", "ecs.contrato_id = co.id"), array("ead_segments es", "es.id = ecs.segment_id"));
            $users = $this->ead_users_model->get(null, $where, $join)->result_array();

            $i = 0;
            foreach ($users as $user) {
                $ultimo_contato = @$this->ead_users_model->get_ultimo_contato($user["user_id"])->row()->created_at;
                if($ultimo_contato != "")
                    $ultimo_contato = date("d/m/Y", strtotime($ultimo_contato));
                else
                    $ultimo_contato = "-";
                $users[$i]['ultimo_contato'] = $ultimo_contato;

                /*$retorno_aval = $this->voxy->make_request("/partners/users/{$user['user_id']}/assessments", "GET");
                if($retorno_aval['status_code'] == 200) {
                    $content = json_decode($retorno_aval["content"], true);
                    if(isset($content[0]))
                        $users[$i]['nivel_geral'] = $content[0]['overall_level_name'];
                    else
                        $users[$i]['nivel_geral'] = '-';
                } else {
                    $users[$i]['nivel_geral'] = '-';
                }*/

                if($user["desempenho_geral"] != "") {
                    if($user["desempenho_geral"] == "higher")
                        $desempenho_geral = '<i class="fa fa-stop" style="color:#00a65a"><label style="display:none;">higher</label></i>';
                    if($user["desempenho_geral"] == "higher_medium")
                        $desempenho_geral = '<i class="fa fa-stop" style="color:#f3d312"><label style="display:none;">higher_medium</label></i>';
                    if($user["desempenho_geral"] == "medium")
                        $desempenho_geral = '<i class="fa fa-stop" style="color:#ff851b"><label style="display:none;">medium</label></i>';
                    if($user["desempenho_geral"] == "emerging")
                        $desempenho_geral = '<i class="fa fa-stop" style="color:#dd4b39"><label style="display:none;">emerging</label></i>';
                    $users[$i]['nivel_geral'] = $desempenho_geral;
                }
                else
                    $users[$i]['nivel_geral'] = "-";

                /*if($user['expiration_date'] > date("Y-m-d")) {
                    $retorno_acesso = $this->voxy->make_request("/partners/users/{$user['user_id']}/auth_token", "GET");
                    if($retorno_acesso["status_code"] == 200) {
                        $content = json_decode($retorno_acesso["content"]);
                        $users[$i]['url_login'] = $content->actions->start;
                    } else {
                        $users[$i]['url_login'] = '';
                    }
                } else {
                    $users[$i]['url_login'] = '';
                }*/
                $i++;
            }

            $this->data['users'] = $users;

            $grupos_responsavel_chamado_options = array(""=>"Selecione");
            foreach ($this->ead_segments_model->get_tutores()->result() as $usr) {
                $grupos_responsavel_chamado_options[$usr->id] = $usr->first_name." ".$usr->last_name;
            }
            $this->data['filtro_tutor'] = array(
                'name'  => 'filtro_tutor',
                'id'    => 'filtro_tutor',
                'type'  => 'text',
                'class' => 'singleInputDynamic',
                'onchange' => 'buscar_seguimentos(this.value)',
                'options' => $grupos_responsavel_chamado_options,
                'selected' => $this->input->post('filtro_tutor')
            );

            if($this->input->post('filtro_tutor')) {
                $segments = $this->ead_segments_model->get_segments_by_tutor($this->input->post('filtro_tutor'))->result();
                $segments_array = array(""=>"Selecione");
                foreach ($segments as $seg) {
                    $segments_array[$seg->id] = $seg->segment_label;
                }
            }
            $this->data['filtro_seguimento'] = array(
                'name'  => 'filtro_seguimento',
                'id'    => 'filtro_seguimento',
                'type'  => 'text',
                'class' => 'form-control',
                'options' => ($this->input->post('filtro_tutor')) ? $segments_array : array(""=>"Selecione"),
                'selected' => $this->input->post('filtro_seguimento')
            );

            // exit("<pre>".print_r($this->data,true)."</pre>");

            /* Load Template */
            $this->template->admin_render('admin/ead/tutoria/tutoria_geral', $this->data);
        }
	}

    public function tutoria()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "tutoria"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('menu_tutoria'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_ead_tutoria'), 'ead_tutoria/tutoria');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            $where = "";
            $where .= " es.tutor_id = '".$this->session->userdata('user_id')."'";
            if(@$_POST['filtro_seguimento']) {
                if($where != "")
                    $where .= " AND";
                $where .= " es.id = '".$_POST['filtro_seguimento']."'";
            }

            $join = array(array("ead_contratos_segments ecs", "ecs.contrato_id = co.id"), array("ead_segments es", "es.id = ecs.segment_id"));
            $users = $this->ead_users_model->get(null, $where, $join)->result_array();

            $i = 0;
            foreach ($users as $user) {
                $ultimo_contato = @$this->ead_users_model->get_ultimo_contato($user["user_id"])->row()->created_at;
                if($ultimo_contato != "")
                    $ultimo_contato = date("d/m/Y", strtotime($this->ead_users_model->get_ultimo_contato($user["user_id"])->row()->created_at));
                else
                    $ultimo_contato = "-";
                $users[$i]['ultimo_contato'] = $ultimo_contato;

                /*$retorno_aval = $this->voxy->make_request("/partners/users/{$user['user_id']}/assessments", "GET");
                if($retorno_aval['status_code'] == 200) {
                    $content = json_decode($retorno_aval["content"], true);
                    if(isset($content[0]))
                        $users[$i]['nivel_geral'] = $content[0]['overall_level_name'];
                    else
                        $users[$i]['nivel_geral'] = '-';
                } else {
                    $users[$i]['nivel_geral'] = '-';
                }*/

                if($user["desempenho_geral"] != "") {
                    if($user["desempenho_geral"] == "higher")
                        $desempenho_geral = '<i class="fa fa-stop" style="color:#00a65a"><label style="display:none;">higher</label></i>';
                    if($user["desempenho_geral"] == "higher_medium")
                        $desempenho_geral = '<i class="fa fa-stop" style="color:#f3d312"><label style="display:none;">higher_medium</label></i>';
                    if($user["desempenho_geral"] == "medium")
                        $desempenho_geral = '<i class="fa fa-stop" style="color:#ff851b"><label style="display:none;">medium</label></i>';
                    if($user["desempenho_geral"] == "emerging")
                        $desempenho_geral = '<i class="fa fa-stop" style="color:#dd4b39"><label style="display:none;">emerging</label></i>';
                    $users[$i]['nivel_geral'] = $desempenho_geral;
                }
                else
                    $users[$i]['nivel_geral'] = "-";

                /*if($user['expiration_date'] > date("Y-m-d")) {
                    $retorno_acesso = $this->voxy->make_request("/partners/users/{$user['user_id']}/auth_token", "GET");
                    if($retorno_acesso["status_code"] == 200) {
                        $content = json_decode($retorno_acesso["content"]);
                        $users[$i]['url_login'] = $content->actions->start;
                    } else {
                        $users[$i]['url_login'] = '';
                    }
                } else {
                    $users[$i]['url_login'] = '';
                }*/
                $i++;
            }

            $this->data['users'] = $users;

            $segments = $this->ead_segments_model->get_segments_by_tutor($this->session->userdata('user_id'))->result();
            $segments_array = array(""=>"Selecione");
            foreach ($segments as $seg) {
                $segments_array[$seg->id] = $seg->segment_label;
            }
            $this->data['filtro_seguimento'] = array(
                'name'  => 'filtro_seguimento',
                'id'    => 'filtro_seguimento',
                'type'  => 'text',
                'class' => 'form-control',
                'options' => ($this->session->userdata('user_id')) ? $segments_array : array(""=>"Selecione"),
                'selected' => $this->input->post('filtro_seguimento')
            );

            //exit("<pre>".print_r($this->data,true)."</pre>");

            /* Load Template */
            $this->template->admin_render('admin/ead/tutoria/tutoria', $this->data);
        }
    }

    public function licoes_aluno($id = null)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "licoes_aluno") OR ! $id OR $id == null)
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('menu_licoes_aluno'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_ead_tutoria'), 'ead_tutoria/licoes_aluno');
            $this->breadcrumbs->unshift(2, lang('menu_ead_licoes_aluno'), 'ead_tutoria/licoes_aluno');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            $licoes = array();
            $params = array();
            
            $k = 0;
            for($i=1;$i>0;$i++) {
                $params["external_user_id"] = $id;
                $params["page"] = $i;
                
                $retorno = $this->voxy->make_request("/partners/lessons", "GET", $params);
                $content = json_decode($retorno["content"], true);
                if(empty($content["results"])) {
                    break;
                }
                foreach ($content["results"] as $licao) {
                    $format_dt_inicio = explode(".", $licao["date_started"]);
                    $format_dt_inicio = explode("T", $format_dt_inicio[0]);
                    
                    $licoes[$k] = array(
                        "titulo" => $licao["title"],
                        "inicio" => date("d/m/Y H:i:s", strtotime('-3 hours', strtotime(@$format_dt_inicio[0]." ".@$format_dt_inicio[1]))),
                        //"termino" => date("d/m/Y H:i:s", strtotime(@$format_dt_termino[0]." ".@$format_dt_termino[1])),
                        "inicio_td" => @$format_dt_inicio[0]." ".@$format_dt_inicio[1],
                        //"termino_td" => @$format_dt_termino[0]." ".@$format_dt_termino[1],
                        "listening_score" => round($licao["listening_score"], 2),
                        "speaking_score" => round($licao["speaking_score"], 2),
                        "grammar_score" => round($licao["grammar_score"], 2),
                        "reading_score" => round($licao["reading_score"], 2),
                        "writing_score" => round($licao["writing_score"], 2),
                        "vocabulary_score" => round($licao["vocabulary_score"], 2),
                        "total_score" => round($licao["total_score"], 2)
                    );        
                    
                    if($licao["date_completed"] != "") {
                        $format_dt_termino = explode(".", $licao["date_completed"]);
                        $format_dt_termino = explode("T", $format_dt_termino[0]);
                        
                        $licoes[$k]["termino"] = date("d/m/Y H:i:s", strtotime('-3 hours', strtotime(@$format_dt_termino[0]." ".@$format_dt_termino[1])));
                        $licoes[$k]["termino_td"] = @$format_dt_termino[0]." ".@$format_dt_termino[1];
                    } else {
                        $licoes[$k]["termino"] = "-";
                        $licoes[$k]["termino_td"] = "9999-12-31 23:59";
                    }
                    $k++;
                }
                //$licoes = array_merge($licoes, $content["results"]);
            }
            

            $this->data['licoes'] = $licoes;
            $this->data['user_id'] = $id;
            //exit("<pre>".print_r($licoes,true)."</pre>");

            /* Load Template */
            $this->template->admin_render('admin/ead/tutoria/licoes_aluno', $this->data);
        }
    }

    public function acessar_ead_aluno($id = null)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "acessar_ead_aluno") OR ! $id OR $id == null)
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $user = $this->ead_users_model->get($id)->row();

            if($user->expiration_date > date("Y-m-d")) {
                $retorno_acesso = $this->voxy->make_request("/partners/users/{$id}/auth_token", "GET");
                if($retorno_acesso["status_code"] == 200) {
                    $content = json_decode($retorno_acesso["content"]);
                    redirect($content->actions->start, 'refresh');
                } else {
                    $this->session->set_flashdata('message', show_alert_error("Aluno EAD não encontrado na plataforma."));
                    redirect('dashboard/index', 'refresh');
                }
            } else {
                $this->session->set_flashdata('message', show_alert_error("Aluno EAD expirado."));
                redirect('dashboard/index', 'refresh');
            }
        }
    }

    public function enviar_msg()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "tutoria"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $this->form_validation->set_rules('assunto', 'lang:assunto', 'trim|required');
            $this->form_validation->set_rules('mensagem', 'lang:mensagem', 'trim|required');

            if ($this->form_validation->run() == TRUE) {
                if(count(json_decode($this->input->post('users_to_hidden'))) == 0) {
                    $this->session->set_flashdata('message', show_alert_error("Selecione ao menos um aluno para receber a mensagem."));
                    redirect('ead_tutoria/tutoria', 'refresh');
                }

                foreach (json_decode($this->input->post('users_to_hidden')) as $user_to) {
                    $data["user_id_de"] = $this->session->userdata('user_id');
                    $data["user_id_para"] = $user_to;
                    $data["assunto"] = $this->input->post('assunto');
                    $data["mensagem"] = $this->input->post('mensagem');
                    if($this->contatos_interno_model->create($data)) {
                        $this->session->set_flashdata('message', show_alert_success("Mensagem enviada ao(s) seu(s) aluno(s)."));
                        $usuario_de = $this->ion_auth->user($this->session->userdata('user_id'))->row();
                        $usuario_to = $this->ion_auth->user($user_to)->row();
                        $msg = "Contato interno enviado de ".$usuario_to->first_name." ".$usuario_to->last_name." para ".$usuario_de->first_name." ".$usuario_de->last_name.": ".$this->input->post('mensagem');  

                        //provisorio
                        $this->ion_auth->historico_create(array("LIG_ID_CLIENTE"=>$user_to,"LIG_ID_USUARIO"=>$this->session->userdata('user_id'),"REGISTRO"=>"true","STATUS"=>"11","MENSAGEM"=>$msg,"DATA_REGISTRO"=>date("Y-m-d"),"HORA_REGISTRO"=>date("H:i:s")));
                        $this->ion_auth->historico_create(array("LIG_ID_CLIENTE"=>$this->session->userdata('user_id'),"LIG_ID_USUARIO"=>$user_to,"REGISTRO"=>"true","STATUS"=>"11","MENSAGEM"=>$msg,"DATA_REGISTRO"=>date("Y-m-d"),"HORA_REGISTRO"=>date("H:i:s")));
                    }
                    else       
                        $this->session->set_flashdata('message', show_alert_error("Houve um problema ao enviar a mensagem, por favor tente novamente ou entre em contato com o administrador.")); 
                }
            } else {
                $this->session->set_flashdata('message', show_alert_error("Preencha corretamente os campos Assunto e Mensagem do formulário.")); 
            }
            redirect('ead_tutoria/tutoria', 'refresh');
        }
    }

    public function buscar_seguimentos() {
        $segments = array();
        if($_POST['tutor']) {
            $segments = $this->ead_segments_model->get_segments_by_tutor($_POST['tutor'])->result_array();
        }
        exit(json_encode($segments));
    }

    public function registrar_tutoria($type, $id) {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "registrar_tutoria") OR ($type == null OR $id == null) ) 
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $referer = parse_url($_SERVER['HTTP_REFERER'],PHP_URL_PATH);
            if(strstr($referer, 'ead_tutoria/tutoria_geral')) {
                $redirect = 'ead_tutoria/tutoria_geral';
            } elseif(strstr($referer, 'ead_tutoria/tutoria')) {
                $redirect = 'ead_tutoria/tutoria';
            } else {
                $redirect = 'ead_tutoria/tutorias_executadas';
            }

            $mostrar_datas = false;
            $mostrar_orcamentos = false;

            if($type == "orcamento") {
                $this->data['tipo'] = "orcamento";
                $groups = $this->ion_auth->get_users_groups_permissoes($this->session->userdata('user_id'))->result();
                foreach ($groups as $value) {
                    $permissoes = unserialize($value->permissoes);

                    foreach ($permissoes as $k => $p) {
                        if($k == "ead_tutoria" && in_array("registrar_tutoria", $p)) {
                            if($value->limited == 0)
                                $mostrar_datas = true;
                        }
                    }
                }
            } elseif($type == "evento") {
                $this->data['tipo'] = "evento";
                $evento_agenda = $this->ead_tutorias_model->get_evento_agenda($id)->row();
                $mostrar_orcamentos = true;
                $orcamentos_array = array(""=>"Selecione um orçamento");
                foreach($this->ead_tutorias_model->get_contratos_ead()->result() as $orcamento) {
                    $orcamentos_array[$orcamento->id] = "Código Orç.: #".$orcamento->id." - Cliente: ".$orcamento->first_name." ".$orcamento->last_name;
                }
                $this->data['orcamentos'] = array(
                    'name'  => 'orcamento',
                    'id'    => 'orcamento',
                    'class' => 'form-control singleInputDynamic',
                    'options' => $orcamentos_array
                );
                $this->data['evento_agenda'] = $evento_agenda;
            } else {
                redirect('auth/login', 'refresh');
            }
            
            // exit("<pre>".print_r($mostrar_datas,true)."</pre>");

            /* Title Page :: Common */
            $this->page_title->push(lang('menu_registrar_tutoria'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_ead_tutoria'), 'ead_tutoria/registrar_tutoria');
            $this->breadcrumbs->unshift(2, lang('menu_ead_registrar_tutoria'), 'ead_tutoria/registrar_tutoria');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            /* Validate form input */
            if($mostrar_datas) {
                $this->form_validation->set_rules('data_hora_inicio', 'lang:data_hora_inicio', 'required|validar_data');
                $this->form_validation->set_rules('data_hora_fim', 'lang:data_hora_fim', 'required|validar_data');
            
                $dt_inicio = explode(" ", $this->input->post('data_hora_inicio'));
                $dt_fim = explode(" ", $this->input->post('data_hora_fim'));
            }
            if($type == "evento") {
                $this->form_validation->set_rules('orcamento', 'lang:orcamento', 'required');
            }
            $this->form_validation->set_rules('meta_porcentagem', 'lang:meta_porcentagem', 'required|is_numeric');
            //$this->form_validation->set_rules('desempenho_geral', 'lang:desempenho_geral', 'required');
            $this->form_validation->set_rules('qtd_atividades', 'lang:qtd_atividades', 'required');

            if ($this->form_validation->run() == TRUE)
            {
                $dados = array(
                    "tutor_id" => $this->session->userdata('user_id'),
                    "info_progresso" => $this->input->post('info_progresso'),
                    "meta_porcentagem" => $this->form_validation->set_value('meta_porcentagem'),
                    "analise_acesso" => $this->input->post('analise_acesso'),
                    "analise_aulas" => $this->input->post('analise_aulas'),
                    "qtd_atividades" => $this->form_validation->set_value('qtd_atividades'),
                    "analise_grupos" => $this->input->post('analise_grupos'),
                    //"desempenho_geral" => $this->form_validation->set_value('desempenho_geral')
                );

                if($type == "orcamento") {
                    $dados["orc_id"] = $id;
                    $dados["data_hora_inicio"] = (@$this->form_validation->set_value('data_hora_inicio')) ? convertDateBRToEU($dt_inicio[0])." ".$dt_inicio[1] : date('Y-m-d H:i:s');
                    $dados["data_hora_fim"] = (@$this->form_validation->set_value('data_hora_fim')) ? convertDateBRToEU($dt_fim[0])." ".$dt_fim[1] :date('Y-m-d H:i:s');
                } else {
                    $dados["orc_id"] = $this->input->post('orcamento');
                    $dados["data_hora_inicio"] = $evento_agenda->inicio;
                    $dados["data_hora_fim"] = $evento_agenda->fim;
                    $dados["evento_id"] = $id;
                }

                if ($tutoria_id = $this->ead_tutorias_model->create($dados)) {
                    $orcamento = $this->ead_users_model->get_orcamento($dados["orc_id"])->row();
                    //if($orcamento->desempenho_geral != $dados["desempenho_geral"]) {

                        //provisório alterado 24/04/2019
                        $this->ion_auth->historico_create(array("LIG_ID_CLIENTE"=>$orcamento->user_id,"LIG_ID_USUARIO"=>$this->session->userdata('user_id'),"REGISTRO"=>"true","STATUS"=>"7","MENSAGEM"=>$dados["info_progresso"],"DATA_REGISTRO"=>date("Y-m-d"),"HORA_REGISTRO"=>date("H:i:s")));
                        //$this->ead_users_model->update_orcamento($dados["orc_id"], array('desempenho_geral' => $dados["desempenho_geral"]));
                        $this->ead_users_model->update_pesquisa($dados["orc_id"], array('meta_porcentagem' => $dados["meta_porcentagem"]));
                    //}

                    $this->ead_users_model->update($orcamento->user_id, array('ultimo_acesso' => $dados["data_hora_fim"]));

                    $this->session->set_flashdata('message', show_alert_success("Tutoria cadastrada."));
                } else {
                    $this->session->set_flashdata('message', show_alert_error($this->ead_users_model->errors()));
                }
                if($this->input->post('redirect'))
                    $redirect = $this->input->post('redirect');

                redirect($redirect, 'refresh');
            } else {
                if($_POST)
                    $this->data['message'] = show_alert_error("Verifique os campos a serem preenchidos.");

                /*if($type == "orcamento") {
                    $orcamento = $this->ead_users_model->get_orcamento($id)->row();
                    $this->data['desempenho_geral'] = (@$this->form_validation->set_value('desempenho_geral'))?$this->form_validation->set_value('desempenho_geral'):$orcamento->desempenho_geral;
                } else {
                    $this->data['desempenho_geral'] = (@$this->form_validation->set_value('desempenho_geral'))?$this->form_validation->set_value('desempenho_geral'):'';
                }*/
                $this->data['redirect'] = $redirect;
                $this->data['mostrar_datas'] = $mostrar_datas;
                $this->data['data_hora_inicio'] = (@$this->form_validation->set_value('data_hora_inicio'))?$this->form_validation->set_value('data_hora_inicio'):'';
                $this->data['data_hora_fim'] = (@$this->form_validation->set_value('data_hora_fim'))?$this->form_validation->set_value('data_hora_fim'):'';
                $this->data['qtd_atividades'] = (@$this->form_validation->set_value('qtd_atividades'))?$this->form_validation->set_value('qtd_atividades'):'';
                $this->data['info_progresso'] = array(
                    'name'  => 'info_progresso',
                    'id'    => 'info_progresso',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => $this->input->post('info_progresso')
                );
                $options_meta = array();
                $i = 0;
                while($i <= 100) {
                    $options_meta[$i] = $i;
                    $i = $i + 10;
                }
                if($type == "orcamento") {
                    $pesquisa = $this->ead_users_model->get_pesquisa($id);
                    if($pesquisa->num_rows() > 0) {
                        $meta_porcentagem_selected = $pesquisa->row()->meta_porcentagem;
                    } else {
                        $meta_porcentagem_selected = $this->form_validation->set_value('meta_porcentagem');
                    }
                    $this->data['meta'] = ($pesquisa->num_rows() > 0) ? $pesquisa->row()->meta : '';
                } else {
                    $meta_porcentagem_selected = $this->form_validation->set_value('meta_porcentagem');

                    $this->data['meta'] = '';
                }
                $this->data['meta_porcentagem'] = array(
                    'name'  => 'meta_porcentagem',
                    'id'    => 'meta_porcentagem',
                    'class' => 'form-control',
                    'selected' => $meta_porcentagem_selected,
                    'options' => $options_meta
                );
                $this->data['analise_acesso'] = array(
                    'name'  => 'analise_acesso',
                    'id'    => 'analise_acesso',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => $this->input->post('analise_acesso')
                );
                $this->data['analise_aulas'] = array(
                    'name'  => 'analise_aulas',
                    'id'    => 'analise_aulas',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => $this->input->post('analise_aulas')
                );
                $this->data['analise_grupos'] = array(
                    'name'  => 'analise_grupos',
                    'id'    => 'analise_grupos',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => $this->input->post('analise_grupos')
                );

                /* Load Template */
                $this->template->admin_render('admin/ead/tutoria/registro', $this->data);
            }
        }
    }

    public function observacoes($tutor_id = null) {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "observacoes") OR $tutor_id == null)
        {
            if($tutor_id == null) {
                $this->session->set_flashdata('message', show_alert_error("Tutor não encontrado!"));
                redirect('dashboard', 'refresh');
            }

            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->breadcrumbs->unshift(1, lang('menu_ead_tutoria'), 'ead_tutoria/observacoes');
            $this->breadcrumbs->unshift(2, lang('menu_ead_tutoria_observacoes'), 'ead_tutoria/observacoes');
            $this->data['pagetitle'] = "<h1>".lang('menu_ead_tutoria_observacoes')."</h1>";
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            

            $this->data['historico'] = $this->ead_users_model->get_observacoes_tutor($tutor_id)->result();
            $this->data['user_id'] = $tutor_id;
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/ead/tutoria/observacoes', $this->data);
        }
    }

    public function observacao_create($tutor_id = null)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "observacao_create") OR $tutor_id == null)
        {
            if($tutor_id == null) {
                $this->session->set_flashdata('message', show_alert_error("Tutor não encontrado!"));
                redirect('users', 'refresh');
            }

            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->breadcrumbs->unshift(2, lang('menu_ead_tutoria_observacoes'), 'ead_tutoria/observacoes');
            $this->breadcrumbs->unshift(3, lang('menu_ead_tutoria_observacao_create'), 'ead_tutoria/observacao_create');
            $this->data['pagetitle'] = "<h1>".lang('menu_ead_tutoria_observacao_create')."</h1>";
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            $this->data['user_id'] = $tutor_id;
            $this->data['message'] = $this->session->flashdata('message');
            $user = $this->ion_auth->user($tutor_id)->row();
            $user_criador = $this->ion_auth->user($this->session->userdata('user_id'))->row();

            $this->form_validation->set_rules('mensagem', 'lang:mensagem', 'required');
            if(@$this->input->post('enviar_sms') == 1) {
                $this->form_validation->set_rules('mensagem', 'lang:mensagem', 'max_length[1480]|required');
            }

            $dados_data = date('Y-m-d');
            $dados_hora = date('H:i:s');
            $dados = array(
                'LIG_ID_CLIENTE' => $tutor_id,
                'LIG_ID_USUARIO' => $this->session->userdata('user_id'),
                'REGISTRO' => "true",
                'STATUS' => 'OBSERVACÕES TUTOR',
                'MENSAGEM' => $this->input->post('mensagem'),
                'DATA_REGISTRO' => $dados_data,
                'HORA_REGISTRO' => $dados_hora,
                'email_enviado' => (@$this->input->post('enviar_email') == 1)?"S":"N",
                'sms_enviado' => (@$this->input->post('enviar_sms') == 1) ? "S" : "N"
            );

            if ($this->form_validation->run() == TRUE && $id = $this->ead_users_model->observacao_create($dados))
            {
                if(@$this->input->post('enviar_email') == 1) {
                    // $this->load->library(array('email'));
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

                    $data = array(
                        'status'        => $this->input->post('situacao'),
                        'mensagem'      => $this->input->post('mensagem'),
                        'criado_por'    => $user_criador->first_name." ".$user_criador->last_name,
                        'data_hora'     => date("d/m/Y", strtotime($dados_data))." ".$dados_hora,
                        'empresa'       => $this->ion_auth->get_empresa($this->session->userdata('empresa_id'))->row()->nome
                    );

                    $padrao_email = $this->ion_auth->get_email_padrao(4)->row();
                    $padrao_email_msg = $padrao_email->mensagem;
                    $padrao_email_msg = str_replace("{empresa}", $data["empresa"], $padrao_email_msg);
                    $padrao_email_msg = str_replace("{status}", $data["status"], $padrao_email_msg);
                    $padrao_email_msg = str_replace("{mensagem}", $data["mensagem"], $padrao_email_msg);
                    $padrao_email_msg = str_replace("{criado_por}", $data["criado_por"], $padrao_email_msg);
                    $padrao_email_msg = str_replace("{data_hora}", $data["data_hora"], $padrao_email_msg);                    

                    // $this->email->clear();
                    // $this->email->from($from_email, utf8_decode($from_name));
                    // $this->email->to($user->email);
                    // $this->email->subject(utf8_decode($padrao_email->assunto));
                    // $this->email->message(utf8_decode($padrao_email_msg));

                    // $this->email->send();

                    $mail = $this->phpmailer_library->load();
                    $mail->isSMTP();
                    $mail->Host     = $email_config['smtp_host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $email_config['smtp_user'];
                    $mail->Password = $email_config['smtp_pass'];
                    $mail->SMTPSecure = 'tsl';
                    $mail->Port     = $email_config['smtp_port'];

                    $mail->setFrom($from_email, utf8_decode($from_name));

                    $mail->addAddress($user->email);

                    $mail->Subject = utf8_decode($padrao_email->assunto);
                    $mail->isHTML(true);

                    $mail->Body = utf8_decode($padrao_email_msg);
                    $mail->send();
                }

                if(@$this->input->post('enviar_sms') == 1 && trim($this->input->post('mensagem')) != "") {
                    $this->load->library('zenvia');
                    $pessoa = $this->ion_auth->get_pessoa($user_id);
                    if($pessoa->num_rows() > 0) {
                        $data = array();
                        $data['from'] = "Saxweb";
                        $data['to'] = formatCelApi($pessoa->row()->celular);
                        $data['msg'] = trim($this->input->post('mensagem'));

                        $zenvia = new Zenvia();
                        $retorno_call = $zenvia->zenviaCallSingle("send-sms", $zenvia->createSingleJsonSms($data));
                        $data_decode = json_decode($retorno_call["data"], true);
                        // exit("<pre>".print_r($data_decode,true)."</pre>");
                    }
                }

                $this->session->set_flashdata('message', show_alert_success($this->ead_users_model->messages()));
                redirect('ead_tutoria/observacoes/'.$tutor_id, 'refresh');
            } else {
                if($_POST)
                    $this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->ead_users_model->errors() ? show_alert_error($this->ead_users_model->errors()) : show_alert_error($this->session->flashdata('message'))));

                $this->data['registro'] = array(
                    'name'  => 'registro',
                    'id'    => 'registro',
                    'class' => 'form-control',
                    'options' => array('true'=>'True', 'false'=>'False'),
                    'selected' => $this->form_validation->set_value('registro', $this->input->post('registro'))
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
                $this->template->admin_render('admin/ead/tutoria/observacao_create', $this->data);
            }
        }
    }

    public function tutorias_executadas()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "tutorias_executadas"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $this->page_title->push(lang('menu_tutorias_executadas'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs */
            $this->breadcrumbs->unshift(1, lang('menu_ead_tutoria'), 'ead_tutoria/tutorias_executadas');
            $this->breadcrumbs->unshift(2, lang('menu_tutorias_executadas'), 'ead_tutoria/tutorias_executadas');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            $this->data['tutorias'] = $this->ead_tutorias_model->get()->result();
            // exit("<pre>".print_r($this->data['tutorias'],true)."</pre>");
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/ead/tutoria/index', $this->data);
        }
    }

    public function edit($id = null) {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "edit") OR $id == null)
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $mostrar_datas = false;
            $groups = $this->ion_auth->get_users_groups_permissoes($this->session->userdata('user_id'))->result();
            foreach ($groups as $value) {
                $permissoes = unserialize($value->permissoes);

                foreach ($permissoes as $k => $p) {
                    if($k == "ead_tutoria" && in_array("edit", $p)) {
                        if($value->limited == 0)
                            $mostrar_datas = true;
                    }
                }
            }

            /* Title Page :: Common */
            $this->page_title->push(lang('menu_edit_tutoria'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_ead_tutoria'), 'ead_tutoria/edit_tutoria');
            $this->breadcrumbs->unshift(2, lang('menu_ead_edit_tutoria'), 'ead_tutoria/edit_tutoria');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            /* Validate form input */
            /*if($mostrar_datas) {
                $this->form_validation->set_rules('data_hora_inicio', 'lang:data_hora_inicio', 'required|validar_data');
                $this->form_validation->set_rules('data_hora_fim', 'lang:data_hora_fim', 'required|validar_data');
            
                $dt_inicio = explode(" ", $this->input->post('data_hora_inicio'));
                $dt_fim = explode(" ", $this->input->post('data_hora_fim'));
            }*/
            $this->form_validation->set_rules('meta_porcentagem', 'lang:meta_porcentagem', 'required|is_numeric');
            //$this->form_validation->set_rules('desempenho_geral', 'lang:desempenho_geral', 'required');
            $this->form_validation->set_rules('qtd_atividades', 'lang:qtd_atividades', 'required');

            $tutoria = $this->ead_tutorias_model->get($id)->row();
            
            // exit("<pre>".print_r($tutoria,true)."</pre>");

            if($tutoria->evento_id != "") {
                $evento_agenda = $this->ead_tutorias_model->get_evento_agenda($tutoria->evento_id)->row();
                $this->data['evento_agenda'] = $evento_agenda;
            }

            if ($this->form_validation->run() == TRUE)
            {
                $dados = array(
                    "tutor_id" => $this->session->userdata('user_id'),
                    //"data_hora_inicio" => (@$this->form_validation->set_value('data_hora_inicio')) ? convertDateBRToEU($dt_inicio[0])." ".$dt_inicio[1] : date('Y-m-d H:i:s'),
                    //"data_hora_fim" => (@$this->form_validation->set_value('data_hora_fim')) ? convertDateBRToEU($dt_fim[0])." ".$dt_fim[1] :date('Y-m-d H:i:s'),
                    "info_progresso" => $this->input->post('info_progresso'),
                    "meta_porcentagem" => $this->form_validation->set_value('meta_porcentagem'),
                    "analise_acesso" => $this->input->post('analise_acesso'),
                    "analise_aulas" => $this->input->post('analise_aulas'),
                    "qtd_atividades" => $this->form_validation->set_value('qtd_atividades'),
                    "analise_grupos" => $this->input->post('analise_grupos'),
                    //"desempenho_geral" => $this->form_validation->set_value('desempenho_geral')
                );

                if ($this->ead_tutorias_model->update($id, $dados)) {
                    $orcamento = $this->ead_users_model->get_orcamento($tutoria->orc_id)->row();
                    //if($orcamento->desempenho_geral != $dados["desempenho_geral"]) {

                        //provisorio alterado 24/04/2019
                        $this->ion_auth->historico_create(array("LIG_ID_CLIENTE"=>$orcamento->user_id,"LIG_ID_USUARIO"=>$this->session->userdata('user_id'),"REGISTRO"=>"true","STATUS"=>"7","MENSAGEM"=>$dados["info_progresso"],"DATA_REGISTRO"=>date("Y-m-d"),"HORA_REGISTRO"=>date("H:i:s")));
                        //$this->ead_users_model->update_orcamento($tutoria->orc_id, array('desempenho_geral' => $dados["desempenho_geral"]));
                        $this->ead_users_model->update_pesquisa($tutoria->orc_id, array('meta_porcentagem' => $dados["meta_porcentagem"]));
                    //}

                    $this->session->set_flashdata('message', show_alert_success("Tutoria atualizada."));
                } else {
                    $this->session->set_flashdata('message', show_alert_error($this->ead_tutorias_model->errors()));
                }

                redirect('ead_tutoria/tutorias_executadas', 'refresh');
            } else {
                if($_POST)
                    $this->data['message'] = show_alert_error("Verifique os campos a serem preenchidos.");

                $orcamento = $this->ead_users_model->get_orcamento($tutoria->orc_id)->row();
                $this->data['tutoria'] = $tutoria;
                $this->data['mostrar_datas'] = $mostrar_datas;
                $data_hora_inicio = explode(" ", $tutoria->data_hora_inicio);
                $data_hora_fim = explode(" ", $tutoria->data_hora_fim);
                $this->data['data_hora_inicio'] = $this->form_validation->set_value('data_hora_inicio', convertDateEUToBR($data_hora_inicio[0])." ".$data_hora_inicio[1]);
                $this->data['data_hora_fim'] = $this->form_validation->set_value('data_hora_fim', convertDateEUToBR($data_hora_fim[0])." ".$data_hora_fim[1]);
                $this->data['qtd_atividades'] = $this->form_validation->set_value('qtd_atividades', $tutoria->qtd_atividades);
                //$this->data['desempenho_geral'] = $this->form_validation->set_value('desempenho_geral', $tutoria->desempenho_geral);
                $this->data['info_progresso'] = array(
                    'name'  => 'info_progresso',
                    'id'    => 'info_progresso',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => (@$this->input->post('info_progresso')) ? $this->input->post('info_progresso') : $tutoria->info_progresso
                );
                $options_meta = array();
                $i = 0;
                while($i <= 100) {
                    $options_meta[$i] = $i;
                    $i = $i + 10;
                }
                $pesquisa = $this->ead_users_model->get_pesquisa($tutoria->orc_id);
                if($pesquisa->num_rows() > 0) {
                    $meta_porcentagem_selected = $pesquisa->row()->meta_porcentagem;
                } else {
                    $meta_porcentagem_selected = $this->form_validation->set_value('meta_porcentagem', $tutoria->meta_porcentagem);
                }
                $this->data['meta_porcentagem'] = array(
                    'name'  => 'meta_porcentagem',
                    'id'    => 'meta_porcentagem',
                    'class' => 'form-control',
                    'selected' => $meta_porcentagem_selected,
                    'options' => $options_meta
                );
                $this->data['analise_acesso'] = array(
                    'name'  => 'analise_acesso',
                    'id'    => 'analise_acesso',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => (@$this->input->post('analise_acesso')) ? $this->input->post('analise_acesso') : $tutoria->analise_acesso
                );
                $this->data['analise_aulas'] = array(
                    'name'  => 'analise_aulas',
                    'id'    => 'analise_aulas',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => (@$this->input->post('analise_aulas')) ? $this->input->post('analise_aulas') : $tutoria->analise_aulas
                );
                $this->data['analise_grupos'] = array(
                    'name'  => 'analise_grupos',
                    'id'    => 'analise_grupos',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => (@$this->input->post('analise_grupos')) ? $this->input->post('analise_grupos') : $tutoria->analise_grupos
                );
                $this->data['meta'] = ($pesquisa->num_rows() > 0) ? $pesquisa->row()->meta : '';

                /* Load Template */
                $this->template->admin_render('admin/ead/tutoria/edit', $this->data);
            }
        }
    }

    public function view($id = null) {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "view") OR $id == null)
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $mostrar_datas = false;
            $groups = $this->ion_auth->get_users_groups_permissoes($this->session->userdata('user_id'))->result();
            foreach ($groups as $value) {
                $permissoes = unserialize($value->permissoes);

                foreach ($permissoes as $k => $p) {
                    if($k == "ead_tutoria" && in_array("edit", $p)) {
                        if($value->limited == 0)
                            $mostrar_datas = true;
                    }
                }
            }

            /* Title Page :: Common */
            $this->page_title->push(lang('menu_view_tutoria'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_ead_tutoria'), 'ead_tutoria/view_tutoria');
            $this->breadcrumbs->unshift(2, lang('menu_ead_view_tutoria'), 'ead_tutoria/view_tutoria');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            $tutoria = $this->ead_tutorias_model->get($id)->row();
            
            // exit("<pre>".print_r($tutoria,true)."</pre>");

            if($tutoria->evento_id != "") {
                $evento_agenda = $this->ead_tutorias_model->get_evento_agenda($tutoria->evento_id)->row();
                $this->data['evento_agenda'] = $evento_agenda;
            }

            $orcamento = $this->ead_users_model->get_orcamento($tutoria->orc_id)->row();
            $this->data['tutoria'] = $tutoria;
            $this->data['mostrar_datas'] = $mostrar_datas;
            $data_hora_inicio = explode(" ", $tutoria->data_hora_inicio);
            $data_hora_fim = explode(" ", $tutoria->data_hora_fim);
            $this->data['data_hora_inicio'] = convertDateEUToBR($data_hora_inicio[0])." ".$data_hora_inicio[1];
            $this->data['data_hora_fim'] = convertDateEUToBR($data_hora_fim[0])." ".$data_hora_fim[1];
            $this->data['qtd_atividades'] = $tutoria->qtd_atividades;
            $this->data['desempenho_geral'] = $tutoria->desempenho_geral;
            $this->data['info_progresso'] = array(
                'name'  => 'info_progresso',
                'id'    => 'info_progresso',
                'type'  => 'text',
                'class' => 'form-control',
                'disabled' => 'disabled',
                'value' => $tutoria->info_progresso
            );
            $options_meta = array();
            $i = 0;
            while($i <= 100) {
                $options_meta[$i] = $i;
                $i = $i + 10;
            }
            $pesquisa = $this->ead_users_model->get_pesquisa($tutoria->orc_id);
            if($pesquisa->num_rows() > 0) {
                $meta_porcentagem_selected = $pesquisa->row()->meta_porcentagem;
            } else {
                $meta_porcentagem_selected = $tutoria->meta_porcentagem;
            }
            $this->data['meta_porcentagem'] = array(
                'name'  => 'meta_porcentagem',
                'id'    => 'meta_porcentagem',
                'class' => 'form-control',
                'disabled' => 'disabled',
                'selected' => $meta_porcentagem_selected,
                'options' => $options_meta
            );
            $this->data['analise_acesso'] = array(
                'name'  => 'analise_acesso',
                'id'    => 'analise_acesso',
                'type'  => 'text',
                'class' => 'form-control',
                'disabled' => 'disabled',
                'value' => $tutoria->analise_acesso
            );
            $this->data['analise_aulas'] = array(
                'name'  => 'analise_aulas',
                'id'    => 'analise_aulas',
                'type'  => 'text',
                'class' => 'form-control',
                'disabled' => 'disabled',
                'value' => $tutoria->analise_aulas
            );
            $this->data['analise_grupos'] = array(
                'name'  => 'analise_grupos',
                'id'    => 'analise_grupos',
                'type'  => 'text',
                'class' => 'form-control',
                'disabled' => 'disabled',
                'value' => $tutoria->analise_grupos
            );
            $this->data['meta'] = ($pesquisa->num_rows() > 0) ? $pesquisa->row()->meta : '';

            /* Load Template */
            $this->template->admin_render('admin/ead/tutoria/view', $this->data);
        }
    }
}
