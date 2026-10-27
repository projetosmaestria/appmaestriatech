<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Dashboard extends Admin_Controller {


    public function __construct()

    {

        parent::__construct();


        /* Load :: Common */

        $this->lang->load('admin/dashboard');

        $this->load->helper('number');

        $this->load->model('dashboard_model');

        $this->load->model('preferences_model');

        $this->load->model('contatos_interno_model');
        $this->load->model('auditorias_model');

        $this->load->helper('functions_helper');

        $this->load->library('voxy');

    }



    public function index()

    {

        if ( ! $this->ion_auth->logged_in())

        {

            redirect('auth/login', 'refresh');

        }

        else

        {

            /* Title Page */

            $this->page_title->push(lang('menu_dashboard'));

            $this->data['pagetitle'] = $this->page_title->show();


            /* Breadcrumbs */

            $this->data['breadcrumb'] = $this->breadcrumbs->show();            

            $this->data['message'] = $this->session->flashdata('message');


            $this->data['admin_pref_interface'] = $this->preferences_model->get_interface('admin_preferences');

            $dashboards_pref = ($this->data['admin_pref_interface']->dashboards) ? unserialize($this->data['admin_pref_interface']->dashboards) : array();

            $grupos_user = $this->preferences_model->get_groups($this->session->userdata('user_id'));

            if($_POST) {

                if($this->input->post('contato_tutor') == 1) {

                    $this->form_validation->set_rules('tutor', 'lang:tutor', 'required|is_numeric');

                    $this->form_validation->set_rules('assunto', 'lang:assunto', 'trim|required');

                    $this->form_validation->set_rules('mensagem', 'lang:mensagem', 'trim|required');


                    if ($this->form_validation->run() == TRUE) {

                        $data["user_id_de"] = $this->session->userdata('user_id');

                        $data["user_id_para"] = $this->input->post('tutor');

                        $data["assunto"] = $this->input->post('assunto');

                        $data["mensagem"] = $this->input->post('mensagem');

                        if($this->contatos_interno_model->create($data)) {

                            $this->data['message'] = show_alert_success("Mensagem enviada ao seu tutor.");

                            $usuario_de = $this->ion_auth->user($this->session->userdata('user_id'))->row();

                            $usuario_to = $this->ion_auth->user($this->input->post('tutor'))->row();

                            $msg = "Contato interno enviado de ".$usuario_to->first_name." ".$usuario_to->last_name." para ".$usuario_de->first_name." ".$usuario_de->last_name.": ".$this->input->post('mensagem'); 


                            //provisorio

                            $this->ion_auth->historico_create(array("LIG_ID_CLIENTE"=>$this->input->post('tutor'),"LIG_ID_USUARIO"=>$this->session->userdata('user_id'),"REGISTRO"=>"true","STATUS"=>"11","MENSAGEM"=>$msg,"DATA_REGISTRO"=>date("Y-m-d"),"HORA_REGISTRO"=>date("H:i:s")));

                            $this->ion_auth->historico_create(array("LIG_ID_CLIENTE"=>$this->session->userdata('user_id'),"LIG_ID_USUARIO"=>$this->input->post('tutor'),"REGISTRO"=>"true","STATUS"=>"11","MENSAGEM"=>$msg,"DATA_REGISTRO"=>date("Y-m-d"),"HORA_REGISTRO"=>date("H:i:s")));

                        }

                        else                            

                            $this->data['message'] = show_alert_error("Houve um problema ao enviar a mensagem, por favor tente novamente ou entre em contato com o administrador.");

                    }

                }

            }


            $array_pages = array();

            $load_scripts = true;
            $load_scripts_dashboard_livros = false;

            if(count($dashboards_pref) > 0 && $grupos_user != false) {

                foreach ($dashboards_pref as $key => $dash) {

                    foreach ($dash as $dash_id) {

                        if (array_key_exists($dash_id, $grupos_user)) {

                            if($key == "padrao") {

                                $data_page['count_users']       = $this->dashboard_model->get_count_record('empresas_users');

                                $data_page['count_groups']      = $this->dashboard_model->get_count_record('groups');

                                $data_page['memory_usage']      = $this->dashboard_model->memory_usage();

                                $data_page['memory_peak_usage'] = $this->dashboard_model->memory_peak_usage(TRUE);

                                $data_page['memory_usepercent'] = $this->dashboard_model->memory_usepercent(TRUE, FALSE);

                            } 
                            
                            elseif 
                                ($key == "materiais") {
                                    $data_page['materiais'] = $this->dashboard_model->get_materiais();
                                }
                            
                            elseif($key == "gestor") {

                                $data_page['msg'] = "<p><h4>Olá, Gestores!</h4></p>

                                    <p>Assista os Vídeos sobre o curso de Cuidador do CEBRAC e conheça os detalhes fundamentais para tornar este produto um grande sucesso de vendas em sua unidade!</p>

                                    <p>Os vídeos são temáticos e cada um deles aborda um aspecto importante sobre o curso e sua implantação.</p>

                                    <p>Todos eles foram desenvolvidos para apoiar e facilitar o seu trabalho de implantação do curso, ambientando toda a equipe para a sua divulgação.</p>

                                    <p>Não se esqueça de acessar seu email para conhecer a sugestão das etapas de implantação do curso na sua Unidade.</p>

                                    <p>Os vídeos disponibilizados aqui poderão ser usados na divulgação para a equipe interna e para treinamento dos setores envolvidos.</p>

                                    <p>Acesse os vídeos por meio do menu que está a esquerda!</p>

                                    <br>

                                    <p>Ótimas vendas!</p>";

                            } elseif($key == "aluno_ead") {

                                $retorno_aval = $this->voxy->make_request("/partners/users/{$this->session->userdata('user_id')}/assessments", "GET");

                                

                                if(@$retorno_aval['status_code'] == 200) {

                                    $content = json_decode($retorno_aval["content"], true);

                                    if(count($content) > 0) {

                                        $data_page['adjusted_score'] = $content[0]['adjusted_score'];

                                        $data_page['total_score'] = $content[0]['total_score'];

                                        $data_page['possible_points'] = $content[0]['possible_points'];

                                        $data_page['reading_score'] = $content[0]['reading_score'];

                                        $data_page['reading_level_name'] = $content[0]['reading_level_name'];

                                        $data_page['grammar_score'] = $content[0]['grammar_score'];

                                        $data_page['grammar_level_name'] = $content[0]['grammar_level_name'];

                                        $data_page['listening_score'] = $content[0]['listening_score'];

                                        $data_page['listening_level_name'] = $content[0]['listening_level_name'];

                                        $data_page['overall_level_name'] = $content[0]['overall_level_name'];

                                    }

                                }

                                

                                $retorno_login = $this->voxy->make_request("/partners/users/{$this->session->userdata('user_id')}/auth_token", "GET");

                                if(@$retorno_login["status_code"] == 200) {

                                    $content_login = json_decode($retorno_login["content"]);

                                    $data_page['url_login'] = $content_login->actions->start;

                                }


                                $tutores = array(""=>"Selecione");

                                $tutores_model = $this->dashboard_model->get_tutores();

                                if($tutores_model) {

                                    foreach ($tutores_model->result() as $usr) {

                                        $tutores[$usr->id] = $usr->first_name." ".$usr->last_name;

                                    }

                                }

                                $this->data['tutor'] = array(

                                    'name'  => 'tutor',

                                    'id'    => 'tutor',

                                    'class' => 'form-control',

                                    'options' => $tutores,

                                    'selected' => ''

                                );


                                $this->data['has_pagamento_entrada_aluno'] = $this->dashboard_model->has_pagamento_entrada_aluno();

                                $this->data['has_pagamento_contrato_aluno'] = $this->dashboard_model->has_pagamento_contrato_aluno();

                            } elseif($key == "aluno_app") {

                                $ddate = date('Y-m-d');

                                $date = new DateTime($ddate);

                                $week = $date->format("W");

                                $year = $date->format("Y");


                                $retorno = array();

                                $k = 0;

                                $j = 1;

                                for($i = 0; $i < 7; $i++) {

                                    if(($week-$j) == 0) {

                                        $year--;

                                        $ddate = $year.'-12-31';

                                        $date = new DateTime($ddate);

                                        $week = $date->format("W");

                                        $j = 0;

                                    }

                                    $week_show = ($week-$j);

                                    $between_dates = $this->dashboard_model->get_start_and_end_date(($week-($j-1)), $year);

                                    $count_resps = $this->dashboard_model->get_count_resps($between_dates);

                                    $retorno[$k]['y'] = $year.' W'.$week_show;

                                    $retorno[$k]['qtd'] = $count_resps;

                                    $k++;

                                    $j++;

                                }

                                $data_page['grafico_audios'] = json_encode($retorno);

                                $data_page['app_respostas'] = $this->dashboard_model->get_respostas_lista()->result();

                                $data_page['dispositivos'] = $this->dashboard_model->get_dispositivos()->result();

                                

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


                                $data_page['audios'] = $this->dashboard_model->get_audios($where);
                                $data_page['materiais'] = $this->dashboard_model->get_materiais($where);
                                $data_page['materiais_unidade'] = $this->dashboard_model->get_materiais($where);

                                $livros = $this->dashboard_model->get_livro()->result();

                                $livros_array = array(""=>"Selecione");

                                foreach ($livros as $value) {

                                    $livros_array[$value->id] = $value->titulo;

                                }

                                $data_page['livros'] = array(

                                    'name'  => 'livro',

                                    'id'    => 'livro',

                                    'onchange' => "loadUnidadesLivro(this.value, '".base_url()."')",

                                    'class' => 'form-control',

                                    'options' => $livros_array,

                                    'selected' => ($this->input->post('livro')) ? $this->input->post('livro') : ''

                                );


                                $unidades_array = array(""=>"Selecione");

                                if($this->input->post('livro')) {

                                    $unidades_livro = $this->dashboard_model->get_unidades_livro($this->input->post('livro'))->result();

                                    foreach ($unidades_livro as $value) {

                                        $unidades_array[$value->id] = $value->unidade." - ".$value->titulo;

                                    }

                                }

                                $data_page['unidades'] = array(

                                    'name'  => 'unidade_livro',

                                    'id'    => 'unidade_livro',

                                    'class' => 'form-control',

                                    'options' => $unidades_array,

                                    'selected' => ($this->input->post('unidade_livro')) ? $this->input->post('unidade_livro') : ''

                                );

                            } elseif($key == "franqueadora_app") {

                                $data_page['unidades'] = $this->dashboard_model->get_unidades()->num_rows();

                                $data_page['alunos'] = $this->dashboard_model->get_alunos()->num_rows();

                                $data_page['respostas_alunos'] = $this->dashboard_model->get_respostas_alunos()->num_rows();

                                $data_page['respostas'] = $this->dashboard_model->get_respostas()->num_rows();

                                // exit("<pre>".print_r($this->db->last_query(),true)."</pre>");

                            } elseif($key == "chamados") {

                                //$data_page['abertos'] = $this->dashboard_model->get_chamados('(c.responsavel_id = "'.$this->session->userdata('user_id').'" OR c.dono_id = "'.$this->session->userdata('user_id').'") AND c.status_id = "1"')->num_rows();


                                if (@$_GET["filtro_range"]) {

                                    $explode = explode(" - ", $_GET["filtro_range"]);

                                    $start = convertDateBRToEU($explode[0]);

                                    $end = convertDateBRToEU($explode[1]);
                                } else {

                                    $start = date('Y-m-01');

                                    $end = date('Y-m-d');
                                }


                                $unidades = $this->dashboard_model->get_unidades()->result();

                                $unids_array = array();

                                $unids_array_show = array();

                                foreach ($unidades as $unid) {

                                    $unids_array[] = $unid->unidade_id;

                                    $unids_array_show[$unid->unidade_id] = $unid->unidade;
                                }


                                if (@$_GET["filtro_unidades"]) {

                                    $avaliacoes = $this->dashboard_model->get_chamados_avaliados($start, $end, $_GET["filtro_unidades"]);
                                } else {

                                    $avaliacoes = $this->dashboard_model->get_chamados_avaliados($start, $end, $unids_array);
                                }


                                $data_page['abertos'] = $this->dashboard_model->get_chamados('c.status_id = "1"', $unids_array)->num_rows();

                                $data_page['sugestao'] = $this->dashboard_model->get_chamados('c.status_id = "9"', $unids_array)->num_rows();

                                $data_page['implantacao'] = $this->dashboard_model->get_chamados('c.status_id = "10"', $unids_array)->num_rows();

                                $data_page['aguardando_teste'] = $this->dashboard_model->get_chamados_aguardando_teste('c.status_id = "8" AND responsavel_id = ' . $this->session->userdata('user_id'), $unids_array);

                                $data_page['avaliacoes'] = $avaliacoes["chamados"];

                                $data_page['media_avaliacoes'] = $avaliacoes["media"];

                                $data_page['filtro_range'] = array(

                                    'name'  => 'filtro_range',

                                    'id'    => 'filtro_range',

                                    'class' => 'form-control',

                                    'value' => convertDateEUToBR($start) . " - " . convertDateEUToBR($end)

                                );

                                $data_page['filtro_range_aval'] = array(

                                    'name'  => 'filtro_range',

                                    'id'    => 'filtro_range_aval',

                                    'class' => 'form-control',

                                    'value' => convertDateEUToBR($start) . " - " . convertDateEUToBR($end)

                                );

                                $data_page['filtro_unidades'] = array(

                                    'name'  => 'filtro_unidades[]',

                                    'id'    => 'filtro_unidades',

                                    'class' => 'multipleSelect',

                                    'multiple' => 'multiple',

                                    'options' => $unids_array_show,

                                    'selected' => ''

                                );

                                // exit("<pre>".print_r($_GET,true)."</pre>");

                                $data_page['compartilhados'] = $this->dashboard_model->get_chamados_compartilhados($unids_array);

                                $data_page['meus_chamados'] = $this->dashboard_model->get_chamados('(c.responsavel_id = "' . $this->session->userdata('user_id') . '" AND c.status_id != 5)', $unids_array)->num_rows();
                                $data_page['meus_atrasados'] = $this->dashboard_model->get_chamados('c.responsavel_id = ' . (int) $this->session->userdata('user_id') . ' AND c.status_id NOT IN (3, 5) AND c.data_finalizacao IS NULL AND c.data_prazo < "' . date('Y-m-d H:i:s') . '"', $unids_array)->num_rows();
                                $data_page['sem_responsavel'] = $this->dashboard_model->get_chamados('(c.responsavel_id IS NULL OR c.responsavel_id = 0) AND c.status_id NOT IN (3, 5)', $unids_array)->num_rows();
                                $data_page['usuario_nome'] = $this->ion_auth->user()->row()->first_name;


                                $retorno = array();

                                $k = 0;

                                $ymax = 0;

                                for ($i = 6; $i >= 0; $i--) {

                                    $date = strtotime(date('Y-m-d') . ' -' . $i . ' months');

                                    $retorno[$k]['month'] = date('m/Y', $date);

                                    $retorno[$k]['qc'] = $this->dashboard_model->get_chamados_abertos(date('Y-m-d', $date), $unids_array)->num_rows();

                                    $retorno[$k]['q'] = $this->dashboard_model->get_chamados_finalizados(date('Y-m-d', $date), $unids_array)->num_rows();

                                    if ($retorno[$k]['q'] > $ymax)

                                        $ymax = $retorno[$k]['q'];

                                    if ($retorno[$k]['qc'] > $ymax)

                                        $ymax = $retorno[$k]['qc'];

                                    $k++;
                                }



                                //exit("<pre>".print_r($retorno,true)."</pre>");

                                $data_page['ymax'] = $ymax;

                                $data_page['grafico_chamados'] = json_encode($retorno);

                                // Buscar tarefas pendentes do usuário logado
                                $this->load->model('chamados_checklists_etapas_model');
                                $data_page['tarefas_pendentes'] = $this->chamados_checklists_etapas_model->get_pending_tasks_by_user($this->session->userdata('user_id'));
                                $data_page['total_tarefas_pendentes'] = $this->chamados_checklists_etapas_model->count_pending_tasks_by_user($this->session->userdata('user_id'));

                            } elseif($key == "mapa_calor") {                                

                                $status = $this->dashboard_model->get_cad_status();

                                $alunos = $this->dashboard_model->get_alunos();

                                $array_alunos = array();

                                if($alunos->num_rows() > 0) {

                                    foreach ($alunos->result() as $aluno) {

                                        if($aluno->latitude != "" && $aluno->longitude != "") {

                                            if($aluno->endereco != "" && ($aluno->numero != "" && $aluno->numero != 0) && $aluno->bairro != "") {

                                                $morada1 = $aluno->endereco.", ".$aluno->numero." - ".$aluno->bairro;

                                            } else {

                                                $morada1 = "";

                                            }


                                            if($aluno->cidade != "" && $aluno->uf != "") {

                                                $morada2 = $aluno->cidade."/".$aluno->uf;

                                            } else {

                                                $morada2 = "";

                                            }


                                            $array_alunos[] = array(

                                                "lat" => $aluno->latitude,

                                                "lng" => $aluno->longitude,

                                                "nome" => $aluno->first_name." ".$aluno->last_name,

                                                "morada1" => $morada1,

                                                "morada2" => $morada2,

                                                "codPostal" => $aluno->cep,

                                                "email" => $aluno->email,

                                                "celular" => $aluno->celular

                                            );

                                        }

                                    }

                                }

                                // exit("<pre>".print_r($array_alunos,true)."</pre>");

                                $data_page["status"] = $status;

                                $data_page["alunos"] = json_encode($array_alunos);

                            } elseif($key == "contratos_ead") {

                                $data_page['contratos'] = $this->dashboard_model->get_contratos_ead()->result();

                                $seguimentos_usuario_array = array();

                                $seguimentos_usuario_session = $this->dashboard_model->get_segments()->result();

                                if(count($seguimentos_usuario_session) > 0) {

                                    foreach ($seguimentos_usuario_session as $seg) {

                                        $seguimentos_usuario_array[] = $seg->segment_label;

                                    }

                                }

                                $data_page['seguimentos_lista'] = $seguimentos_usuario_array;

                                

                                $k = 0;

                                foreach ($data_page['contratos'] as $k => $contrato)

                                {

                                    $contrato = $this->dashboard_model->get_contratos_ead($contrato->id)->row();


                                    $segments_array = array();

                                    if(@$contrato->id) {

                                        $segments = $this->dashboard_model->get_segments($contrato->id);

                                        if(@$segments->num_rows() > 0) {

                                            foreach ($segments->result() as $seg) {

                                                $segments_array[] = $seg->segment_label;

                                            }

                                        }

                                    }


                                    $data_page['contratos'][$k]->seguimentos = $segments_array;

                                    $data_page['contratos'][$k]->cliente_voxy = $this->dashboard_model->is_voxy_user($contrato->user_id);

                                    if($data_page['contratos'][$k]->has_pesquisa = $this->dashboard_model->has_pesquisa($contrato->id)) {

                                        $data_page['contratos'][$k]->pesquisa_perfil = $this->dashboard_model->get_pesquisa($contrato->id);

                                    }


                                    $data_page['contratos'][$k]->can_anular = false;

                                    $parametro = $this->dashboard_model->get_parametro($contrato->filial);

                                    if($parametro->LIG_STATUS_CONTRATO_01 == $contrato->status) {

                                        $data_page['contratos'][$k]->can_anular = true;

                                    }


                                    $data_page['contratos'][$k]->can_gerar_segunda_via = false;

                                    $has_iugu_entrada = $this->dashboard_model->has_iugu_entrada($contrato->id);

                                    if($parametro->LIG_STATUS_CONTRATO_01 == $contrato->status && $contrato->tipo_saxweb == 2 && $has_iugu_entrada->num_rows() == 1 && $this->ion_auth->has_permissao("iugu", "gerar_segunda_via_entrada")) {

                                        $data_page['contratos'][$k]->can_gerar_segunda_via = true;

                                    }


                                    $data_page['contratos'][$k]->can_cartao_recorrencia = false;

                                    if($this->ion_auth_model->is_recorrencia($contrato->id, $contrato->user_id) && $this->ion_auth->has_permissao("iugu", "cartao_recorrencia")) {

                                        $data_page['contratos'][$k]->can_cartao_recorrencia = true;

                                    }


                                    $data_page['contratos'][$k]->can_alterar_assinatura = false;

                                    if($this->ion_auth_model->is_recorrencia($contrato->id, $contrato->user_id) && $this->ion_auth->has_permissao("iugu", "alterar_assinatura")) {

                                        $data_page['contratos'][$k]->can_alterar_assinatura = true;

                                    }
                                    
                                    $unidade = $this->dashboard_model->get_unidade($contrato->filial);

                                    if($unidade->num_rows() == 1) {
                                        if($unidade->row()->iugu_status == "S") {

                                            $data_page['contratos'][$k]->can_gerar_segunda_via = false;
                                            $has_iugu_entrada = $this->dashboard_model->has_iugu_entrada($contrato->id);
                                            if($parametro->LIG_STATUS_CONTRATO_01 == $contrato->status && $contrato->tipo_saxweb == 2 && $has_iugu_entrada->num_rows() == 1 && $this->ion_auth->has_permissao("iugu", "gerar_segunda_via_entrada")) {
                                                $data_page['contratos'][$k]->can_gerar_segunda_via = true;
                                            }

                                            $data_page['contratos'][$k]->can_cartao_recorrencia = false;
                                            if($this->ion_auth_model->is_recorrencia($contrato->id, $contrato->user_id) && $this->ion_auth->has_permissao("iugu", "cartao_recorrencia")) {
                                                $data_page['contratos'][$k]->can_cartao_recorrencia = true;
                                            }

                                            $data_page['contratos'][$k]->can_alterar_assinatura = false;
                                            if($this->ion_auth_model->is_recorrencia($contrato->id, $contrato->user_id) && $this->ion_auth->has_permissao("iugu", "alterar_assinatura")) {
                                                $data_page['contratos'][$k]->can_alterar_assinatura = true;
                                            }

                                            $data_page['contratos'][$k]->can_incluir_assinatura = false;
                                            if(!$this->ion_auth->is_assinatura($contrato->id) && !$this->ion_auth->is_recorrencia($contrato->id, $contrato->user_id) && $this->ion_auth->has_permissao("iugu", "alterar_assinatura")) {
                                                $data_page['contratos'][$k]->can_incluir_assinatura = true;
                                            }
                                        }
                                    }


                                    $k++;

                                }

                                // exit("<pre>".print_r($data_page['contratos'],true)."</pre>");

                            } elseif($key == "correcoes_professores") {

                                $data_page['correcoes'] = $this->dashboard_model->get_correcoes_professores();

                            } elseif($key == "supervisor") {

                                //$data_page['ultimo_acesso_tutores'] = $this->dashboard_model->get_ultimo_acesso_tutores();

                                $data_page['detalhes_tutores'] = $this->dashboard_model->get_detalhes_tutores();

                                $data_page['cancelamentos'] = $this->dashboard_model->get_contratos_ead_cancelados();

                                $tutorias_realizadas = $this->dashboard_model->get_tutorias_realizadas()->result();

                                foreach ($tutorias_realizadas as $_key => $tut) {

                                    if($tut->desempenho_geral == "higher")

                                        $desempenho_geral = '<i class="fa fa-stop" style="color:#00a65a"><label style="display:none;">higher</label></i>';

                                    if($tut->desempenho_geral == "higher_medium")

                                        $desempenho_geral = '<i class="fa fa-stop" style="color:#f3d312"><label style="display:none;">higher_medium</label></i>';

                                    if($tut->desempenho_geral == "medium")

                                        $desempenho_geral = '<i class="fa fa-stop" style="color:#ff851b"><label style="display:none;">medium</label></i>';

                                    if($tut->desempenho_geral == "emerging")

                                        $desempenho_geral = '<i class="fa fa-stop" style="color:#dd4b39"><label style="display:none;">emerging</label></i>';

                                    $tutorias_realizadas[$_key]->desempenho_geral = $desempenho_geral;

                                }

                                $data_page['tutorias_realizadas'] = $tutorias_realizadas;

                                $data_page['desistentes'] = $this->dashboard_model->get_desistentes();


                                $tutores_array = array(""=>"Todos");

                                if(count($data_page['detalhes_tutores']) > 0) {

                                    foreach ($data_page['detalhes_tutores'] as $t) {

                                        $tutores_array[$t->id] = $t->email;

                                    }

                                }


                                $data_page['tutores_filtro'] = array(

                                    'name'  => 'tutores',

                                    'id'    => 'tutores',

                                    'class' => 'form-control',

                                    'options' => $tutores_array,

                                    'selected' => ''

                                );

                                $data_page['tutores'] = $this->dashboard_model->get_desistentes();


                                $nivel_alunos = $this->dashboard_model->get_nivel_alunos();

                                $na_array = array(

                                    "higher" => 0,

                                    "higher_medium" => 0,

                                    "medium" => 0,

                                    "emerging" => 0,

                                    "total" => 0

                                );

                                if($nivel_alunos->num_rows() > 0) {

                                    $last_na = "";

                                    foreach ($nivel_alunos->result() as $na) {

                                        if($last_na != $na->orc_id) {

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


                                //------ comentado dia 20/02/2020 ------

                                // $data_page['json_nivel_alunos'] = json_encode($na_json);


                                //$metas_atingidas = $this->dashboard_model->get_json_grafico_metas_atingidas();


                                // $data_page['json_metas_atingidas'] = json_encode(array_values($metas_atingidas["elements"]));

                                // $data_page['json_x_metas_atingidas'] = json_encode($metas_atingidas["x"]);

                                // $data_page['json_labels_metas_atingidas'] = json_encode($metas_atingidas["labels"]);

                                //------ ------

                            } elseif($key == "planos_acao") {

                                $data_page['planos_acao'] = $this->dashboard_model->get_planos_acao();

                            } elseif($key == "chamados_responsavel") {

                                $data_page['chamados'] = $this->dashboard_model->get_chamados_responsavel('c.responsavel_id = "'.$this->session->userdata('user_id').'" AND c.status_id != 3 AND c.status_id != 5');

                            } elseif($key == "grupo_anuncio") {

                                $data_page['eventos'] = $this->dashboard_model->get_eventos()->result();

                                // exit($this->db->last_query());

                                // exit("<pre>".print_r($data_page['eventos'],true)."</pre>");

                            } elseif($key == "compra_venda") {

                                $data_page['compras'] = $this->dashboard_model->get_compras(@$_GET["situacao_compra"]);

                                $data_page['vendas'] = $this->dashboard_model->get_vendas(@$_GET["situacao_venda"]);

                                $data_page['graficoCompra'] = $this->dashboard_model->get_grafico_compra_entregas(@$_GET["situacao_compra"]);

                                $data_page['graficoVenda'] = $this->dashboard_model->get_grafico_venda_entregas(@$_GET["situacao_venda"]);

                                $data_page['filtro_situacao_compra'] = array(

                                    'name'  => 'filtro_situacao_compra',

                                    'id'    => 'filtro_situacao_compra',

                                    'class' => 'form-control',

                                    'options' => array(""=>"", "EA"=>"Em andamento", "AI" => "Aprovado integral", "AP"=>"Aprovado parcial", "RE" => "Reprovado", "FP"=>"Faturado parcial", "CA"=>"Cancelado", "BM"=>"Baixa manual", "FI"=>"Faturado integral"),

                                    'selected' => (@$_GET["situacao_compra"]) ? $_GET["situacao_compra"] : ''

                                );

                                $data_page['filtro_situacao_venda'] = array(

                                    'name'  => 'filtro_situacao_venda',

                                    'id'    => 'filtro_situacao_venda',

                                    'class' => 'form-control',

                                    'options' => array(""=>"", "EA"=>"Em andamento", "AI" => "Aprovado integral", "AP"=>"Aprovado parcial", "RE" => "Reprovado", "FP"=>"Faturado parcial", "CA"=>"Cancelado", "BM"=>"Baixa manual"),

                                    'selected' => (@$_GET["situacao_venda"]) ? $_GET["situacao_venda"] : ''

                                );

                                //567 provisorio trilar

                                $data_page['sinc'] = $this->dashboard_model->get_sincronizacao_status(567);

                                $load_scripts = false;

                            } elseif($key == "contratos_renovar") {

                                $data_page['contratos'] = $this->dashboard_model->get_contratos_ead_renovar();

                                $data_page['can_suspender'] = false;

                                if($this->ion_auth->has_permissao("iugu", "suspender_assinatura")) {

                                    $data_page['can_suspender'] = true;

                                }

                                $data_page['can_alterar'] = false;

                                if($this->ion_auth->has_permissao("iugu", "alterar_assinatura")) {

                                    $data_page['can_alterar'] = true;

                                }


                                $status = $this->ion_auth_model->get_historico_status()->result();

                                $status_array = array(""=>"Selecione");

                                foreach ($status as $st) {

                                    $status_array[$st->id] = $st->descricao;

                                }

                                $data_page['status'] = array(

                                    'name'  => 'status',

                                    'id'    => 'status',

                                    'class' => 'form-control',

                                    'options' => $status_array,

                                    'selected' => ''

                                );

                                $data_page['motivo'] = array(

                                    'name'  => 'motivo',

                                    'id'    => 'motivo',

                                    'class' => 'form-control',

                                    'value' => ''

                                );

                                //exit("<pre>".print_r($data_page['contratos'],true)."</pre>");

                            } elseif($key == "tutor") {

                                $data_page['contatos'] = $this->contatos_interno_model->get(null, "c.visualizada = 'N'", 5);

                                //exit("<pre>".print_r($data_page['contratos'],true)."</pre>");

                            }elseif($key == "mensagens") {
                                $data_page['contatos'] = $this->contatos_interno_model->get(null, "c.visualizada = 'N'", 5);
                                //exit("<pre>".print_r($data_page['contratos'],true)."</pre>");
                            }elseif($key == "livros") {
                                if (@$_GET['curso']) {
                                    $curso_id = $_GET['curso'];
                                    $data_page['livros'] = $this->dashboard_model->get_livro([$curso_id])->result();
                                } else {
                                    $data_page['livros'] = $this->dashboard_model->get_livro()->result();
                                }
                                $load_scripts_dashboard_livros = true;
                            }


                            $array_pages[] = array('admin/dashboard/'.$key.'.php', @$data_page);
                            
                            // REMOVIDO: Não adiciona mais o arquivo separado de tarefas pendentes
                            // Agora as tarefas pendentes estão dentro do próprio arquivo chamados.php

                            break;

                        }

                    }

                }

            }

            $data_page['base_url'] = base_url();

            if($load_scripts == true)
                $array_pages[] = array('admin/dashboard/scripts.php', @$data_page);

            if(@$load_scripts_dashboard_livros == true)
                $array_pages[] = array('admin/dashboard/scripts_dashboard_livros.php', @$data_page);


            $this->template->admin_multiple_render($array_pages, $this->data);

        }

    }


    public function sincronizar_anuncios($id)

    {

        $evento = $this->dashboard_model->get_eventos($id)->row();

        $unidades = $this->dashboard_model->get_unidade_anuncio($evento->unidade_id);


        if($unidades->num_rows() > 0) {

            foreach ($unidades->result_array() as $unidade_info) {         

                if($unidade_info['fb_expires_at'] <= date("Y-m-d H:i:s")) {


                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/oauth/access_token?client_id=" . $unidade_info['fb_app_id'] . "&client_secret=" . $unidade_info['fb_app_secret'] . "&grant_type=fb_exchange_token&fb_exchange_token=" . $unidade_info['fb_access_token']);

                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

                    $result = curl_exec($ch);

                    $refreshData = json_decode($result, true);


                    if($refreshData["access_token"]) {

                        $data["fb_access_token"] = $refreshData["access_token"];

                    }

                    if(@$refreshData["expires_in"]) {

                        $data["fb_expires_at"] = date('Y/m/d H:i:s', strtotime("+".$refreshData["expires_in"]." seconds"));

                    }


                    $this->dashboard_model->update_unidades_anuncio($unidade_info['id'], array('fb_access_token'=>$data["fb_access_token"],'fb_expires_at'=>$data["fb_expires_at"]));

                }


                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v3.3/".$evento->fb_id."?access_token=" . $unidade_info['fb_access_token']."&fields=ads");

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

                $result = curl_exec($ch);

                $adSetData = json_decode($result, true);


                if($adSetData["error"]) {

                    $this->session->set_flashdata('message', show_alert_error($adSetData["error"]["message"]));

                } else {

                    $this->session->set_flashdata('message', show_alert_success($adSetData));

                }

        

                //exit("<pre>".print_r($adSetData,true)."</pre>");

            }

        }


        redirect('', 'refresh');

    }


    public function remover_dispositivo()

    {

        if($this->input->post('dispositivo_id') != "") {

            if($this->dashboard_model->remover_dispositivo($this->input->post('dispositivo_id'))) {

                $this->session->set_flashdata('message', show_alert_success($this->dashboard_model->messages()));

            } else {

                $this->session->set_flashdata('message', show_alert_error($this->dashboard_model->messages()));

            }

        }

        redirect('', 'refresh');

    }


    public function get_alunos_map()

    {

        $status = $_POST["status"];

        $idade = $_POST["idade"];

        $alunos = $this->dashboard_model->get_alunos_map($status, $idade);

        

        $array_alunos = array();

        if($alunos->num_rows() > 0) {

            foreach ($alunos->result() as $aluno) {

                if($aluno->latitude != "" && $aluno->longitude != "") {

                    if($aluno->endereco != "" && ($aluno->numero != "" && $aluno->numero != 0) && $aluno->bairro != "") {

                        $morada1 = $aluno->endereco.", ".$aluno->numero." - ".$aluno->bairro;

                    } else {

                        $morada1 = "";

                    }


                    if($aluno->cidade != "" && $aluno->uf != "") {

                        $morada2 = $aluno->cidade."/".$aluno->uf;

                    } else {

                        $morada2 = "";

                    }


                    $array_alunos[] = array(

                        "lat" => $aluno->latitude,

                        "lng" => $aluno->longitude,

                        "nome" => $aluno->first_name." ".$aluno->last_name,

                        "morada1" => $morada1,

                        "morada2" => $morada2,

                        "codPostal" => $aluno->cep,

                        "email" => $aluno->email,

                        "celular" => $aluno->celular

                    );

                }

            }

        }

        // exit("<pre>".print_r($array_alunos,true)."</pre>");

        exit(json_encode($array_alunos));

    }


    public function pagar_entrada_ead() {
        $cad_orca_id = $this->dashboard_model->get_contrato_aluno()->row()->id;
        $condicao = $this->dashboard_model->get_condicao($cad_orca_id)->row();
        $customer_id = $this->dashboard_model->get_iugu_user($this->session->userdata('user_id'))->row()->iugu_id;
        $cad_orca_id = $cad_orca_id;
        $parcela = $condicao->NUM_PARCELAS;
        $condicao = $condicao->ID;

        $this->auditorias_model->create("dashboard", "pagar_entrada_ead", "Aluno acessou o pagamento da entrada do EAD.");

        $string = base64_encode('matricula|'.$customer_id.'|'.$cad_orca_id.'|'.$condicao.'|'.$parcela.'|'.$this->session->userdata('user_id'));

        if(ENVIRONMENT == "production")
            $url = "https://matriculaead.bslidiomas.com.br/matricula.php";
        elseif(ENVIRONMENT == "development")
            $url = "https://matriculaead.bslidiomas.com.br/matricula.php";
        else
            $url = "https://localhost/bsl-institucional-ead/matricula.php";

        redirect($url.'?redirect=saxweb&info='.$string, 'refresh');
    }

    public function pagar_contrato_ead() {
        $cad_orca_id = $this->dashboard_model->get_contrato_aluno()->row()->id;
        $condicao = $this->dashboard_model->get_condicao($cad_orca_id)->row();
        $customer_id = $this->dashboard_model->get_iugu_user($this->session->userdata('user_id'))->row()->iugu_id;
        $cad_orca_id = $cad_orca_id;
        $parcela = $condicao->NUM_PARCELAS;
        $condicao = $condicao->ID;

        $this->auditorias_model->create("dashboard", "pagar_contrato_ead", "Aluno acessou o pagamento do contrato do EAD.");

        $string = base64_encode('pagamento|'.$customer_id.'|'.$cad_orca_id.'|'.$condicao.'|'.$parcela.'|'.$this->session->userdata('user_id'));

        if(ENVIRONMENT == "production")
            $url = "https://matriculaead.bslidiomas.com.br/pagamento.php";
        elseif(ENVIRONMENT == "development")
            $url = "https://matriculaead.bslidiomas.com.br/pagamento.php";
        else
            $url = "https://localhost/bsl-institucional-ead/pagamento.php";

        redirect($url.'?redirect=saxweb&info='.$string, 'refresh');
    }


    public function carregar_graficos_supervisos() {

        // exit("<pre>".print_r($_POST,true)."</pre>");


        if($_POST["tutor"] != "") {

            if($this->ion_auth->is_tutor_ead($_POST["tutor"]) == false) {

                exit(json_encode(array("erro" => true, "msg" => "Tutor não encontrado.")));

            }

        }


        $nivel_alunos = $this->dashboard_model->get_nivel_alunos($_POST["tutor"], $_POST["inicio"], $_POST["fim"]);

        $na_array = array(

            "higher" => 0,

            "higher_medium" => 0,

            "medium" => 0,

            "emerging" => 0,

            "total" => 0

        );

        if($nivel_alunos->num_rows() > 0) {

            $last_na = "";

            foreach ($nivel_alunos->result() as $na) {

                if($last_na != $na->orc_id) {

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


        $json_nivel_alunos = json_encode($na_json);


        

        $metas_atingidas = $this->dashboard_model->get_json_grafico_metas_atingidas($_POST["tutor"], $_POST["inicio"], $_POST["fim"]);


        $json_metas_atingidas = json_encode(array_values($metas_atingidas["elements"]));

        $json_x_metas_atingidas = json_encode($metas_atingidas["x"]);

        $json_labels_metas_atingidas = json_encode($metas_atingidas["labels"]);



        $html['grafico1'] = '<div class="chart-responsive col-md-8"><canvas id="pieChart" height="150"></canvas></div><div class="col-md-4"> <ul class="chart-legend clearfix"> <li><i class="fa fa-circle text-green"></i> Higher</li> <li><i class="fa fa-circle text-yellow"></i> Higher Medium</li> <li><i class="fa fa-circle text-orange"></i> Medium</li> <li><i class="fa fa-circle text-red"></i> Emerging</li> </ul> </div>';

        $html['grafico1'] .= "

            <script>

                var pieChartCanvas = $('#pieChart').get(0).getContext('2d');

                var pieChart       = new Chart(pieChartCanvas);

                var PieData        = ".$json_nivel_alunos.";

                var pieOptions     = {

                segmentShowStroke    : true,

                segmentStrokeColor   : '#fff',

                segmentStrokeWidth   : 1,

                percentageInnerCutout: 50, 

                animationSteps       : 100,

                animationEasing      : 'easeOutBounce',

                animateRotate        : true,

                animateScale         : false,

                responsive           : true,

                maintainAspectRatio  : false,

                legendTemplate       : '<ul class=\'<%=name.toLowerCase()%>-legend\'><% for (var i=0; i<segments.length; i++){%><li><span style=\'background-color:<%=segments[i].fillColor%>\'></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',

                tooltipTemplate      : '<%=value %> aluno(s) <%=label%>'

                };

                pieChart.Doughnut(PieData, pieOptions);

            </script>";


        $html['grafico2'] = '<div class="chart-responsive col-md-12">

            <div class="chart" id="line-chart" style="height: 150px;"></div>

        </div>';

        $html['grafico2'] .= "

            <script>

                var line = new Morris.Line({

                    element   : 'line-chart',

                    data      : ".$json_metas_atingidas.", 

                    xkey      : 'y', 

                    ykeys     : ".$json_x_metas_atingidas.", 

                    labels    : ".$json_labels_metas_atingidas.", 

                    ymin: 0, 

                    ymax: 100, 

                    gridIntegers: true, 

                    lineColors: ['#a0d0e0'], 

                    hideHover : 'auto', 

                    resize: true, yLabelFormat: function(y){return y != Math.round(y)?'':y;}, xLabelAngle: 40, setAxisAlignFirstX: true, parseTime: false }); 

            </script>";


        exit(json_encode(array("erro" => false, "data" => $html)));

        // exit("<pre>".print_r($_POST,true)."</pre>");

    }


    public function buscar_prod_compra() {

        $prods = $this->dashboard_model->get_prod_compra($_POST["id"]);

        $html = '';

        if($prods->num_rows() > 0) {

            $html .= '<table class="table table-striped">';

            $html .= '<tr><th>Sequência</th><th>Descrição</th><th>Preço</th><th>Und</th><th>Qtd</th><th>Desconto</th></tr>';

            foreach ($prods->result() as $prod) {

                $html .= '<tr>';

                $html .= '<td>'.$prod->sequencia.'</td>';

                $html .= '<td>'.$prod->DESCRICAO.'</td>';

                $html .= '<td>R$ '.number_format($prod->preco, 2, ",", ".").'</td>';

                $html .= '<td>'.$prod->unidade.'</td>';

                $html .= '<td>'.intval($prod->quantidade).'</td>';

                $html .= '<td>R$ '.number_format($prod->desconto, 2, ",", ".").'</td>';

                $html .= '</tr>';

            }

            $html .= '</table>';

        }

        exit(json_encode(array("erro" => false, "data" => $html)));

    }


    public function buscar_prod_venda() {

        $prods = $this->dashboard_model->get_prod_venda($_POST["id"]);

        $html = '';

        if($prods->num_rows() > 0) {

            $html .= '<table class="table table-striped">';

            $html .= '<tr><th>Sequência</th><th>Descrição</th><th>Preço</th><th>Und</th><th>Qtd</th><th>Desconto</th></tr>';

            foreach ($prods->result() as $prod) {

                $html .= '<tr>';

                $html .= '<td>'.$prod->sequencia.'</td>';

                $html .= '<td>'.$prod->DESCRICAO.'</td>';

                $html .= '<td>R$ '.number_format($prod->preco, 2, ",", ".").'</td>';

                $html .= '<td>'.$prod->unidade.'</td>';

                $html .= '<td>'.intval($prod->quantidade_item).'</td>';

                $html .= '<td>R$ '.number_format($prod->total_desconto, 2, ",", ".").'</td>';

                $html .= '</tr>';

            }

            $html .= '</table>';

        }

        exit(json_encode(array("erro" => false, "data" => $html)));

    }


     public function get_unidades_livro() {
        $unids = $this->dashboard_model->get_unidades_livro($_GET['id'])->result();
        /*foreach($unids as $k => $unid){
            $unid->video_yt = unserialize($unid->video_yt);
            if(is_array($unid->video_yt) && count($unid->video_yt) > 0) {
                foreach($unid->video_yt as $j => $v) {
                    if (strpos($v, 'vimeo') !== false) {
                        $lastElement = @end(explode('/', $v));
                        $curl =  $this->get_thumbnail_vimeo($lastElement);
                        if($curl != false) {
                            $curl = json_decode($curl);
                            //exit("<pre>".print_r($curl,true)."</pre>");
                            $unid->video_yt[$j] = ['titulo'=>$curl->title,'thumb'=>$curl->thumbnail_url,'url'=>$v];
                        } else {
                            $unid->video_yt[$j] = ['titulo'=>$unid->titulo,'thumb'=>'','url'=>$v];
                        }
                    } else {
                        $lastElement = @end(explode('/', $v));
                        $unid->video_yt[$j] = ['titulo'=>$unid->titulo,'thumb'=>'https://img.youtube.com/vi/'.$lastElement.'/0.jpg','url'=>$v];
                    }
                }
            }
            $unids[$k] = $unid;
        }*/
        $where = "a.livro = '".$_GET['id']."'";
        $audios = $this->dashboard_model->get_audios($where)->result();
        $materiais = $this->dashboard_model->get_materiais(null, "m.livro = '".$_GET['id']."'");
        exit(json_encode(array("erro" => false, "data" => ['unids' => $unids, 'audios' => $audios, 'materiais' => $materiais])));
    }

    public function get_videos_unidade() {
        $this->load->model('app_unidades_livro_model');

        $unid = $this->app_unidades_livro_model->get($_GET['id'])->row();
    
        $unid->video_yt = unserialize($unid->video_yt);
        if(is_array($unid->video_yt) && count($unid->video_yt) > 0) {
            foreach($unid->video_yt as $j => $v) {
                if (strpos($v, 'vimeo') !== false) {
                    $lastElement = @end(explode('/', $v));
                    /*$curl =  $this->get_thumbnail_vimeo($lastElement);
                    if($curl != false) {
                        $curl = json_decode($curl);
                        //exit("<pre>".print_r($curl,true)."</pre>");
                        $unid->video_yt[$j] = ['titulo'=>$curl->title,'thumb'=>$curl->thumbnail_url,'url'=>$v];
                    } else {
                        $unid->video_yt[$j] = ['titulo'=>$unid->titulo,'thumb'=>'','url'=>$v];
                    }*/
                    
                    $unid->video_yt[$j] = ['titulo'=>$unid->titulo,'thumb'=>'https://vumbnail.com/'.$lastElement.'.jpg','url'=>$v];
                } else {
                    $lastElement = @end(explode('/', $v));
                    $unid->video_yt[$j] = ['titulo'=>$unid->titulo,'thumb'=>'https://img.youtube.com/vi/'.$lastElement.'/0.jpg','url'=>$v];
                }
            }
        }
        $unid = $unid;

        $where = "a.unidade = '".$_GET['id']."'";
        $audios = $this->dashboard_model->get_audios($where)->result();
        $audios_exercicios = $this->dashboard_model->get_audios_exercicios($where)->result();
        $materiais = $this->dashboard_model->get_materiais(null, "m.unidade = '".$_GET['id']."'");
        $materiais_unidade = $this->dashboard_model->get_materiais(null, "m.unidade = '".$_GET['id']."'");

        exit(json_encode(array("erro" => false, "data" => ['unid' => $unid, 'audios' => $audios, 'audios_exercicios' => $audios_exercicios, 'materiais' => $materiais, 'materiais_unidades' => $materiais_unidade])));
    }

    public function get_thumbnail_vimeo($id) {
        //https://vimeo.com/api/oembed.json?url=https://vimeo.com/835616572

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://vimeo.com/api/oembed.json?url=https://vimeo.com/".$id,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "Referer: REFERER_URL"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          return false;
        } else {
          return $response;
        }
    }

    public function get_curso($unidade_id = null)
    {
        $curso = $this->dashboard_model->get_curso();
        exit(json_encode(array("erro" => false, "data" => $curso)));
    }
}