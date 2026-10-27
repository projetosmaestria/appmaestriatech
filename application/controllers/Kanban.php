<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kanban extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/kanban');
        $this->load->model('chamados_model');
        $this->load->model('kanban_model');
        $this->load->model('kanban_dashboard_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_kanban'));
        //$this->data['pagetitle'] = "<h1>".lang('menu_kanban')." <small>Control panel</small></h1>";

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_kanban'), 'kanban');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("kanban", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            $this->data['message'] = $this->session->flashdata('message');

            $unidades_permitidas_ids = $this->get_unidades_permitidas_ids();

            $quadros = $this->kanban_model->get_quadros()->result();
            $quadros_return = array();

            if (empty($quadros)) {
                $this->data['quadros'] = array();
                $this->data['quadros_sel'] = 0;
                $this->data['colunas_sel'] = array();
                $this->data['unidades_permitidas_ids'] = $unidades_permitidas_ids;
                $this->data['pagetitle'] = "<h1>".lang('menu_kanban')."</h1>";
                $this->data['can_metricas'] = $this->ion_auth->has_permissao("kanban", "metricas");
                $this->data['chamados_atrasados'] = array();
                $this->data['can_edit_overdue_schedule'] = false;
                $this->data['overdue_collaborator_id'] = (int) $this->session->userdata('user_id');
                $this->data['overdue_priorities'] = array();
                $this->data['overdue_levels'] = array();
                $this->data['selected_status_ids'] = array();
                $this->data['status_filter_query'] = '';
                $this->template->admin_render('admin/kanban/index', $this->data);
                return;
            }

            $unidades_permitidas = $this->chamados_model->get_unidades_permitidas($this->session->userdata('user_id'), $this->session->userdata('empresa_id'));
            $unids_array = array(''=>'Todos');
            foreach ($unidades_permitidas->result() as $key => $value) {
                $unids_array[$value->unidade_id] = $value->unidade;
            }

            $colunas_array_sel = array();
            foreach ($quadros as $quadro) {
                $quadros_return[$quadro->id] = $quadro;
                $filtro_usuarios_array = array();
                $filtro_unidades_array = array();
                
                $colunas = $this->kanban_model->get_colunas($quadro->id)->result_array();

                $colunas_array = array();
                $atribuicao_array = array(""=>"Todos");

                if(isset($_GET['btn_filtrar'])) {
                    if($_GET['btn_filtrar'] == $quadro->id) {
                        if(isset($_GET['filtro_usuarios'][$_GET['btn_filtrar']])) {
                            if($_GET['filtro_usuarios'][$_GET['btn_filtrar']] !== "") {
                                $filtro_usuarios_array[$quadro->id] = $_GET['filtro_usuarios'][$_GET['btn_filtrar']];
                            }
                        }
                        if(isset($_GET['filtro_unidades'][$_GET['btn_filtrar']])) {
                            if(count(@$_GET['filtro_unidades'][$_GET['btn_filtrar']]) > 0) {
                                foreach ($_GET['filtro_unidades'][$_GET['btn_filtrar']] as $unid) {
                                    if($unid !== "") {
                                        $filtro_unidades_array[$quadro->id][] = (int)$unid;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $filtro_usuarios_array[$quadro->id] = $this->session->userdata('user_id');
                }

                // Define as unidades permitidas para a consulta
                if (!empty($filtro_unidades_array[$quadro->id])) {
                    $unidades_busca = array_values(array_intersect($filtro_unidades_array[$quadro->id], $unidades_permitidas_ids));
                    if (empty($unidades_busca)) {
                        $unidades_busca = array(0);
                    }
                } else {
                    $unidades_busca = $unidades_permitidas_ids;
                }

                foreach ($colunas as $key => $value) {
                    $colunas_array[$value['id']] = $value['nome'];
                    if(@isset($_GET["filtro_colunas"][$_GET["btn_filtrar"]])) {
                        if(in_array($value['id'], $_GET["filtro_colunas"][$_GET["btn_filtrar"]])) {
                            $colunas_array_sel[$quadro->id][] = $value['id'];
                        }
                    } else {
                        if (trim(strtolower($value['nome'])) != 'avaliado') {
                            $colunas_array_sel[$quadro->id][] = $value['id'];
                        }
                    }

                    if(@$filtro_usuarios_array[$quadro->id]) {
                        $tarefas_geral = $this->kanban_model->get_tarefas($quadro->id, $value['id'], @$filtro_usuarios_array[$quadro->id], $unidades_busca)->result_array();

                        foreach ($tarefas_geral as $k => $v) {
                            unset($tarefas_geral[$k]['id']);
                            unset($tarefas_geral[$k]['titulo']);
                            unset($tarefas_geral[$k]['status']);
                            unset($tarefas_geral[$k]['descricao']);
                            unset($tarefas_geral[$k]['created']);
                            unset($tarefas_geral[$k]['data_prazo']);
                            unset($tarefas_geral[$k]['resp']);
                            unset($tarefas_geral[$k]['dono']);
                            unset($tarefas_geral[$k]['responsavel_id']);
                            unset($tarefas_geral[$k]['dev']);
                            unset($tarefas_geral[$k]['desenvolvedor_id']);
                            unset($tarefas_geral[$k]['email']);
                            unset($tarefas_geral[$k]['prioridade']);
                            unset($tarefas_geral[$k]['unidade']);
                            unset($tarefas_geral[$k]['unidade_id']);
                            $tarefas_geral[$k]['user'] = $filtro_usuarios_array[$quadro->id];
                        }
                        $tarefas_user = $this->kanban_model->get_tarefas_user($quadro->id, $value['id'], @$filtro_usuarios_array[$quadro->id], $unidades_busca)->result_array();

                        if(count($tarefas_user) == 0 && count($tarefas_geral) > 0) {
                            $i = 0;
                            foreach ($tarefas_geral as $k1 => $v1) {
                                $tarefas_geral[$k1]['ordem'] = $i;
                                $i++;
                            }
                            $this->kanban_model->insert_tarefa_user($tarefas_geral);
                            $tarefas_user = $this->kanban_model->get_tarefas_user($quadro->id, $value['id'], @$filtro_usuarios_array[$quadro->id], $unidades_busca)->result_array();
                        }

                        $value['tarefas'] = $tarefas_user;
                    } else {
                        $value['tarefas'] = $this->kanban_model->get_tarefas($quadro->id, $value['id'], null, $unidades_busca)->result_array();
                    }

                    $quadros_return[$quadro->id]->colunas[$key] = $value;
                }

                foreach ($this->kanban_model->get_atribuicao($quadro->id, $unidades_busca)->result() as $atrib) {
                    $atribuicao_array[$atrib->responsavel_id] = $atrib->email;
                }

                $this->data['filtros']['filtro_usuarios'][$quadro->id] = array(
                    'name'  => 'filtro_usuarios['.$quadro->id.']',
                    'id'    => 'filtro_usuarios-'.$quadro->id,
                    'class' => 'multipleSelect',
                    'options' => $atribuicao_array,
                    'selected' => @$filtro_usuarios_array[$quadro->id]
                );

                $this->data['filtros']['filtro_colunas'][$quadro->id] = array(
                    'name'  => 'filtro_colunas['.$quadro->id.'][]',
                    'id'    => 'filtro_colunas-'.$quadro->id,
                    'class' => 'multipleSelect',
                    'multiple' => 'multiple',
                    'options' => $colunas_array,
                    'selected' => @$colunas_array_sel[$quadro->id]
                );

                $this->data['filtros']['filtro_unidades'][$quadro->id] = array(
                    'name'  => 'filtro_unidades['.$quadro->id.'][]',
                    'id'    => 'filtro_unidades-'.$quadro->id,
                    'class' => 'multipleSelect',
                    'multiple' => 'multiple',
                    'options' => $unids_array,
                    'selected' => @$filtro_unidades_array[$quadro->id]
                );

            }

            $this->data['quadros'] = $quadros_return;
            $quadros_keys = array_keys($quadros_return);
            $default_quadro = !empty($quadros_keys) ? (string)$quadros_keys[0] : '1';
            $this->data['quadros_sel'] = (isset($_GET["btn_filtrar"]) && isset($quadros_return[$_GET["btn_filtrar"]])) ? $_GET["btn_filtrar"] : $default_quadro;
            $this->data['colunas_sel'] = $colunas_array_sel;
            $this->data['unidades_permitidas_ids'] = $unidades_permitidas_ids;

            $quadro_nome = isset($quadros_return[$this->data['quadros_sel']]) ? $quadros_return[$this->data['quadros_sel']]->nome : '';
            $this->data['pagetitle'] = "<h1>".lang('menu_kanban')." <small>".$quadro_nome."</small></h1>";
            
            $this->data['can_metricas'] = $this->ion_auth->has_permissao("kanban", "metricas");
            $statuses = $this->chamados_model->get_status()->result();
            $default_status_ids = $this->get_default_dashboard_statuses($statuses);
            $this->data['chamados_atrasados'] = $this->kanban_model->get_chamados_atrasados(
                $this->session->userdata('user_id'),
                $this->session->userdata('empresa_id'),
                $default_status_ids,
                $unidades_permitidas_ids
            );
            $this->data['can_edit_overdue_schedule'] = $this->ion_auth->is_admin();
            $this->data['overdue_collaborator_id'] = (int) $this->session->userdata('user_id');
            $this->data['overdue_priorities'] = $this->chamados_model->get_prioridades()->result();
            $this->data['overdue_levels'] = $this->chamados_model->get_niveis_ativos()->result();
            $this->data['selected_status_ids'] = $default_status_ids;
            $this->data['status_filter_query'] = http_build_query(array(
                'filtrar_status' => 1,
                'status_ids' => $default_status_ids,
            ));
            
            /* Load Template */
            $this->template->admin_render('admin/kanban/index', $this->data);
        }
	}

    public function q_geral() {

        $retorno = array();
        
        if(!isset($_POST['id'])) {
            exit(false);
        }

        $tarefa = $this->kanban_model->get_tarefa($_POST['id']);
        if($tarefa->num_rows() > 0) {

            // Validação de acesso à unidade da tarefa
            if (!$this->tem_acesso_unidade($tarefa->row()->unidade_id)) {
                $retorno['erro'] = true;
                $retorno['data'] = "Acesso não autorizado para chamados desta unidade!";
                exit(json_encode($retorno));
            }

            foreach ($tarefa->result() as $k => $t) {

                $tarefa->result()[$k]->has_outro_apontamento['confirm'] = false;

                $apontamento_ativo = $this->kanban_model->get_apontamento_ativo($t->id);
                $tarefa->result()[$k]->apontamento_ativo['qtd'] = 0;
                if($apontamento_ativo->num_rows() > 0) {

                    $tarefa->result()[$k]->apontamento_ativo['qtd'] = $apontamento_ativo->num_rows();
                    $tarefa->result()[$k]->apontamento_ativo['id'] = $apontamento_ativo->row()->id;

                    $hora_inicio = new DateTime($apontamento_ativo->row()->data." ".$apontamento_ativo->row()->hora_inicio);
                    $esforco = new DateTime(date("Y-m-d H:i:s"));
                    $diff = $hora_inicio->diff($esforco);
                    $horas = ($diff->days * 24) + $diff->format('%h');
                    $minutos = $diff->format('%i');
                    $segundos = $diff->format('%s');

                    $tarefa->result()[$k]->apontamento_ativo['hora_inicio'] = $apontamento_ativo->row()->hora_inicio;
                    $tarefa->result()[$k]->apontamento_ativo['horas'] = $horas;
                    $tarefa->result()[$k]->apontamento_ativo['minutos'] = $minutos;
                    $tarefa->result()[$k]->apontamento_ativo['segundos'] = $segundos;
                } else {

                    $has_apontamento = $this->kanban_model->get_apontamento_ativo();
                    if($has_apontamento->num_rows() > 0) {
                        $tarefa->result()[$k]->has_outro_apontamento['confirm'] = true;
                        $tarefa->result()[$k]->has_outro_apontamento['id'] = $has_apontamento->row()->chamado_id;
                    } 
                }

                $tarefa->result()[$k]->historico = $this->kanban_model->get_historico($t->id)->result();
                $tarefa->result()[$k]->esforco = $this->get_total_esforco($t->id);
                $tarefa->result()[$k]->anexo = $this->kanban_model->get_anexo($t->id)->result();
            }

            if($tarefa->row()->status_id == 8 || $tarefa->row()->status_id == 14)
                $status_n_obrigatorios = $this->chamados_model->get_status_n_obrigatorios(true)->result();
            else
                $status_n_obrigatorios = $this->chamados_model->get_status_n_obrigatorios()->result();

            foreach ($status_n_obrigatorios as $stat) {
                $status_options[$stat->id] = $stat->descricao;
            }

            $prioridades = $this->chamados_model->get_prioridades()->result();
            foreach ($prioridades as $pri) {
                $prioridade_options[$pri->id] = $pri->descricao;
            }

            $retorno['erro'] = false;
            $retorno['data'] = ['tarefa' => $tarefa->row(), 'status_options' => $status_options, 'prioridade_options' => $prioridade_options];
        } else {
            $retorno['erro'] = true;
            $retorno['data'] = "Tarefa não encontrada!";
        }
        
        exit(json_encode($retorno));
    }

    private function get_total_esforco($tarefa) {
        $times = array();
        $apontamentos = $this->kanban_model->get_apontamento($tarefa);
        
        foreach ($apontamentos->result() as $apont) {
            $times[] = $apont->esforco;
        }
        return AddPlayTime($times);
    }

    public function alterar_status_tarefa() {
        $retorno = array();
        
        if(!isset($_POST['quadro']) || !isset($_POST['coluna']) || !isset($_POST['tarefa'])) {
            exit(false);
        }

        $tarefa = $this->kanban_model->get_tarefa($_POST['tarefa']);
        if(!$tarefa || $tarefa->num_rows() == 0) {
            exit(false);
        }

        // Validação de acesso à unidade da tarefa
        if (!$this->tem_acesso_unidade($tarefa->row()->unidade_id)) {
            $retorno['erro'] = true;
            $retorno['data'] = "Acesso não autorizado para chamados desta unidade!";
            exit(json_encode($retorno));
        }

        $quadro = $this->kanban_model->get_quadros($_POST['quadro']);
        $coluna = $this->kanban_model->get_coluna($_POST['coluna']);

        $this->kanban_model->reodenarTarefas($quadro, $coluna, $tarefa, $_POST);

        $retorno['erro'] = false;
        $retorno['data'] = $this->kanban_model->messages();

        exit(json_encode($retorno));
    }
	
    public function metricas()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("kanban", "metricas"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            $this->data['message'] = $this->session->flashdata('message');

            $this->data['pagetitle'] = "<h1>".lang('menu_kanban')." <small>Métricas</small></h1>";

            $unidades_permitidas_ids = $this->get_unidades_permitidas_ids();

            $data_inicio = date("Y-m-d H:i:s", strtotime("-7 days"));
            $data_fim = date("Y-m-d H:i:s");
            $filtro = [];
            if(@$_GET['filtro_usuarios']) {
                $filtro[] = "Usuário: ".$this->chamados_model->get_user_name($_GET['filtro_usuarios']);
            }
            if(@$_GET['data_filtro_inicio'] || @$_GET['data_filtro_fim']) {
                if($_GET['data_filtro_inicio']) {
                    $filtro[] = "Data Início: ".$_GET['data_filtro_inicio'];
                    $data_inicio = convertDateBRToEU($_GET['data_filtro_inicio'])." 00:00:00";
                }
                if($_GET['data_filtro_fim']) {
                    $filtro[] = "Data Fim: ".$_GET['data_filtro_fim'];
                    $data_fim = convertDateBRToEU($_GET['data_filtro_fim'])." 23:59:59";
                }
            }

            $diferenca_datas = get_diff_dates($data_inicio, $data_fim);

            if($diferenca_datas->days > 30) {
                $this->session->set_flashdata('message', show_alert_error("O período entre as datas não pode ser maior que 30 dias."));
                redirect('kanban/metricas', 'refresh');
            }

            $res = $this->kanban_model->getMetricasChart1($data_inicio, $data_fim, @$_GET['filtro_usuarios'], $unidades_permitidas_ids);
            
            // echo "<Pre>".print_r($diferenca_datas->days,true)."</pre>";
            // exit;
            
            $data_atual = "";
            $data_lista = [];
            foreach($res->result() as $data) {
                if($data->data_envio != $data_atual) {
                    $data_lista[$data->data_envio]["Nível 1"] = 0;
                    $data_lista[$data->data_envio]["Nível 2"] = 0;
                    $data_lista[$data->data_envio]["Nível 3"] = 0;
                    $data_lista[$data->data_envio]["Nível 4"] = 0;
                    $data_atual = $data->data_envio;
                }
                if($data->nivel != "Nível 0")
                    $data_lista[$data->data_envio][$data->nivel]++;
            }

            $labels = [];
            $nivel1 = [];
            $nivel2 = [];
            $nivel3 = [];
            $nivel4 = [];
            foreach($data_lista as $data => $lista) {
                $labels[] = $data;
                foreach($lista as $kn => $nivel) {
                    if($kn == "Nível 1")
                        $nivel1[] = $nivel;
                    elseif($kn == "Nível 2")
                        $nivel2[] = $nivel;
                    elseif($kn == "Nível 3")
                        $nivel3[] = $nivel;
                    elseif($kn == "Nível 4")
                        $nivel4[] = $nivel;
                }
            }

            $json_data = ["labels" => $labels, "niveis" => ["nivel1" => $nivel1, "nivel2" => $nivel2, "nivel3" => $nivel3, "nivel4" => $nivel4]];
            
            // echo "<Pre>".print_r($json_data,true)."</pre>";
            // exit;

            $this->data['json_data'] = json_encode($json_data);
            $this->data['filtros'] = $filtro;

            $atribuicao_array = [''=>'Selecione um usuário'];
            foreach ($this->kanban_model->get_atribuicao(null, $unidades_permitidas_ids)->result() as $atrib) {
                $atribuicao_array[$atrib->responsavel_id] = $atrib->email;
            }

            $this->data['filtro_usuarios'] = array(
                'name'  => 'filtro_usuarios',
                'id'    => 'filtro_usuarios',
                'class' => 'singleInputDynamic form-control',
                //'multiple' => 'multiple',
                'options' => $atribuicao_array,
                'selected' => @$_GET['filtro_usuarios']
            );

            /* Load Template */
            $this->template->admin_render('admin/kanban/metricas', $this->data);
        }
    }

    /**
     * Retorna os IDs das unidades permitidas para o usuário logado
     *
     * @return array
     */
    private function get_unidades_permitidas_ids()
    {
        $unidades_permitidas = $this->chamados_model->get_unidades_permitidas(
            $this->session->userdata('user_id'),
            $this->session->userdata('empresa_id')
        )->result();

        $unidade_in_where = array(0);
        foreach ($unidades_permitidas as $unid) {
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

    public function planejamento()
    {
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
            return;
        }

        $is_admin = $this->ion_auth->is_admin();
        if (!$is_admin && !$this->ion_auth->has_permissao('kanban', 'index')) {
            show_error('Você não tem permissão para acessar este painel.', 403);
            return;
        }

        $user_id = (int) $this->session->userdata('user_id');
        $company_id = (int) $this->session->userdata('empresa_id');

        if ($this->input->post('action') === 'save_access') {
            if (!$is_admin) {
                show_error('Somente administradores podem configurar acessos.', 403);
                return;
            }
            $viewer_id = (int) $this->input->post('viewer_id');
            $collaborator_ids = $this->input->post('collaborators');
            if (!is_array($collaborator_ids)) {
                $collaborator_ids = array();
            }
            if (!$this->kanban_dashboard_model->save_access($company_id, $viewer_id, $collaborator_ids)) {
                show_error('Não foi possível salvar as permissões. Verifique os usuários selecionados.', 400);
                return;
            }
            $this->session->set_flashdata('message', show_alert_success('Permissões atualizadas.'));
            redirect('kanban/planejamento?config=1&viewer_id=' . $viewer_id, 'refresh');
            return;
        }

        if ($this->input->post('action') === 'save_goal') {
            if (!$is_admin) {
                show_error('Somente administradores podem configurar metas.', 403);
                return;
            }
            $collaborator_id = (int) $this->input->post('collaborator_id');
            $goal_hours = $this->input->post('goal_hours');
            if (!is_numeric($goal_hours) || (float) $goal_hours < 0 || (float) $goal_hours > 24 ||
                !$this->kanban_dashboard_model->save_goal($company_id, $collaborator_id, (float) $goal_hours)) {
                show_error('Informe um colaborador válido e uma meta diária entre 0 e 24 horas.', 400);
                return;
            }
            $this->session->set_flashdata('message', show_alert_success('Meta diária atualizada.'));
            redirect('kanban/planejamento?config=1', 'refresh');
            return;
        }

        if ($this->input->post('action') === 'save_discord') {
            if (!$is_admin) {
                show_error('Somente administradores podem configurar o Discord dos colaboradores.', 403);
                return;
            }
            if (!$this->kanban_dashboard_model->save_discord_ids($company_id, $this->input->post('discord_ids'), $user_id)) {
                show_error('Não foi possível salvar. O ID do Discord deve conter somente números (17 a 20 dígitos).', 400);
                return;
            }
            $this->session->set_flashdata('message', show_alert_success('Discord dos colaboradores atualizado.'));
            redirect('kanban/planejamento?config=1', 'refresh');
            return;
        }

        if ($this->input->post('action') === 'save_unavailability') {
            if (!$is_admin) {
                show_error('Somente administradores podem cadastrar indisponibilidades.', 403);
                return;
            }
            $start_date = $this->input->post('availability_start', true);
            $end_date = $this->input->post('availability_end', true);
            $collaborator_id = (int) $this->input->post('availability_collaborator_id');
            if (!$this->valid_dashboard_date($start_date) ||
                !$this->valid_dashboard_date($end_date) ||
                $start_date > $end_date ||
                !$this->kanban_dashboard_model->save_unavailability(
                    $company_id,
                    $collaborator_id > 0 ? $collaborator_id : null,
                    $this->input->post('availability_type', true),
                    $this->input->post('availability_title', true),
                    $start_date,
                    $end_date,
                    $user_id
                )) {
                show_error('Não foi possível cadastrar a indisponibilidade. Confira tipo, colaborador e período.', 400);
                return;
            }
            $this->session->set_flashdata('message', show_alert_success('Indisponibilidade cadastrada e adicionada à agenda.'));
            redirect(
                'kanban/planejamento?config=1&tab=availability&colaborador=' . (int) $this->input->post('colaborador') .
                '&inicio=' . rawurlencode($this->input->post('inicio')) .
                '&fim=' . rawurlencode($this->input->post('fim')),
                'refresh'
            );
            return;
        }

        if ($this->input->post('action') === 'delete_unavailability') {
            if (!$is_admin) {
                show_error('Somente administradores podem remover indisponibilidades.', 403);
                return;
            }
            $availability_id = (int) $this->input->post('availability_id');
            if ($availability_id <= 0 ||
                !$this->kanban_dashboard_model->delete_unavailability($company_id, $availability_id, $user_id)) {
                show_error('Não foi possível remover a indisponibilidade selecionada.', 400);
                return;
            }
            $this->session->set_flashdata('message', show_alert_success('Indisponibilidade removida da agenda.'));
            redirect(
                'kanban/planejamento?config=1&tab=availability&colaborador=' . (int) $this->input->post('colaborador') .
                '&inicio=' . rawurlencode($this->input->post('inicio')) .
                '&fim=' . rawurlencode($this->input->post('fim')),
                'refresh'
            );
            return;
        }

        if ($this->input->post('action') === 'update_schedule') {
            if (!$is_admin) {
                show_error('Somente administradores podem alterar prioridade e prazo.', 403);
                return;
            }
            $ticket_id = (int) $this->input->post('ticket_id');
            $collaborator_id = (int) $this->input->post('collaborator_id');
            $scheduled_date = $this->input->post('scheduled_date', true);
            $deadline = $this->input->post('deadline', true);
            $priority_id = (int) $this->input->post('priority_id');
            $level_id = (int) $this->input->post('level_id');
            if ($this->valid_dashboard_date($scheduled_date) &&
                $this->kanban_dashboard_model->is_date_unavailable($company_id, $collaborator_id, $scheduled_date)) {
                show_error('Não é possível agendar atividades: o colaborador está indisponível nessa data.', 400);
                return;
            }
            if (!$this->valid_dashboard_date($scheduled_date) ||
                !$this->valid_dashboard_datetime($deadline) ||
                $level_id <= 0 ||
                !$this->kanban_dashboard_model->update_schedule(
                    $company_id,
                    $ticket_id,
                    $collaborator_id,
                    $scheduled_date,
                    str_replace('T', ' ', $deadline) . ':00',
                    $priority_id,
                    $level_id,
                    $user_id
                )) {
                show_error('Não foi possível atualizar a atividade. Confira data, prazo, prioridade e nível.', 400);
                return;
            }
            $this->session->set_flashdata('message', show_alert_success('Atividade atualizada.'));
            $status_query = '';
            if ($this->input->get('filtrar_status', true) === '1') {
                $redirect_status_ids = $this->input->get('status_ids');
                if (!is_array($redirect_status_ids)) {
                    $redirect_status_ids = array();
                }
                $safe_redirect_status_ids = array();
                foreach ($redirect_status_ids as $status_id) {
                    if (is_scalar($status_id) && ctype_digit((string) $status_id)) {
                        $safe_redirect_status_ids[] = (int) $status_id;
                    }
                }
                $status_query = '&' . http_build_query(array(
                    'filtrar_status' => 1,
                    'status_ids' => array_values(array_unique($safe_redirect_status_ids)),
                ));
            }
            $redirect_start_date = $this->input->get('inicio', true);
            $redirect_end_date = $this->input->get('fim', true);
            $max_redirect_end_date = $this->valid_dashboard_date($redirect_start_date)
                ? (new DateTime($redirect_start_date))->modify('+89 days')->format('Y-m-d')
                : null;
            if (!$this->valid_dashboard_date($redirect_start_date) ||
                !$this->valid_dashboard_date($redirect_end_date) ||
                $redirect_start_date > $redirect_end_date ||
                $redirect_end_date > $max_redirect_end_date) {
                $redirect_start_date = $scheduled_date;
                $redirect_end_date = $scheduled_date;
            }
            redirect(
                'kanban/planejamento?colaborador=' . $collaborator_id .
                '&inicio=' . rawurlencode($redirect_start_date) .
                '&fim=' . rawurlencode($redirect_end_date) .
                $status_query,
                'refresh'
            );
            return;
        }

        $start_date = $this->input->get('inicio', true) ?: date('Y-m-d', strtotime('monday this week'));
        $end_date = $this->input->get('fim', true) ?: date('Y-m-d', strtotime('friday this week'));
        $max_end_date = $this->valid_dashboard_date($start_date)
            ? (new DateTime($start_date))->modify('+89 days')->format('Y-m-d')
            : null;
        if (!$this->valid_dashboard_date($start_date) || !$this->valid_dashboard_date($end_date) ||
            $start_date > $end_date || $end_date > $max_end_date) {
            show_error('Selecione um período válido de até 90 dias.', 400);
            return;
        }

        $collaborators = $this->kanban_dashboard_model->get_visible_collaborators($company_id, $user_id, $is_admin);
        $statuses = $this->chamados_model->get_status()->result();
        $collaborator_ids = array_map(function ($collaborator) {
            return (int) $collaborator->id;
        }, $collaborators);

        $requested_id = $this->input->get('colaborador', true);
        if ($requested_id !== null && $requested_id !== '') {
            $selected_id = (int) $requested_id;
            if (!in_array($selected_id, $collaborator_ids, true)) {
                show_error('Você não tem permissão para visualizar este colaborador.', 403);
                return;
            }
        } else {
            $selected_id = in_array($user_id, $collaborator_ids, true)
                ? $user_id
                : (isset($collaborator_ids[0]) ? $collaborator_ids[0] : 0);
        }

        $valid_status_ids = array_map(function ($status) {
            return (int) $status->id;
        }, $statuses);
        $filter_submitted = $this->input->get('filtrar_status', true) === '1';
        if ($filter_submitted) {
            $requested_status_ids = $this->input->get('status_ids');
            if (!is_array($requested_status_ids)) {
                $requested_status_ids = array();
            }
            $safe_requested_status_ids = array();
            foreach ($requested_status_ids as $status_id) {
                if (is_scalar($status_id) && ctype_digit((string) $status_id)) {
                    $safe_requested_status_ids[] = (int) $status_id;
                }
            }
            $selected_status_ids = array_values(array_intersect(
                $valid_status_ids,
                array_unique($safe_requested_status_ids)
            ));
        } else {
            $selected_status_ids = $this->get_default_dashboard_statuses($statuses);
        }

        $this->page_title->clear();
        $this->page_title->push('Planejamento da equipe');
        $this->data['pagetitle'] = $this->page_title->show();
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['is_admin'] = $is_admin;
        $this->data['collaborators'] = $collaborators;
        $this->data['statuses'] = $statuses;
        $this->data['selected_status_ids'] = $selected_status_ids;
        $this->data['status_filter_query'] = http_build_query(array(
            'filtrar_status' => 1,
            'status_ids' => $selected_status_ids,
        ));
        $this->data['selected_id'] = $selected_id;
        $this->data['start_date'] = $start_date;
        $this->data['end_date'] = $end_date;
        $this->data['chamados_atrasados'] = $this->kanban_model->get_chamados_atrasados(
            $selected_id,
            $company_id,
            $selected_status_ids
        );
        $this->data['chamados_sem_planejamento'] = $this->kanban_model->get_chamados_sem_planejamento(
            $selected_id,
            $company_id,
            $selected_status_ids
        );
        $this->data['can_edit_overdue_schedule'] = $is_admin;
        $this->data['overdue_collaborator_id'] = $selected_id;
        $this->data['overdue_priorities'] = $this->chamados_model->get_prioridades()->result();
        $this->data['overdue_levels'] = $this->chamados_model->get_niveis_ativos()->result();
        $this->data['dashboard'] = $this->kanban_dashboard_model->get_dashboard(
            $company_id,
            $collaborator_ids,
            $selected_id,
            $start_date,
            $end_date,
            $selected_status_ids
        );
        $comparison_collaborators = $this->kanban_dashboard_model->get_comparison_collaborators($company_id);
        $visible_collaborator_ids = array_flip($collaborator_ids);
        $comparison_collaborators = array_values(array_filter($comparison_collaborators, function ($collaborator) use ($visible_collaborator_ids) {
            return isset($visible_collaborator_ids[(int) $collaborator->id]);
        }));
        $this->data['comparison'] = $this->kanban_dashboard_model->get_comparison(
            $company_id,
            $comparison_collaborators,
            $start_date,
            $end_date
        );
        $this->data['priorities'] = $this->chamados_model->get_prioridades()->result();
        $this->data['unavailabilities'] = $this->kanban_dashboard_model->get_company_unavailability(
            $company_id,
            $start_date,
            $end_date
        );

        if ($is_admin) {
            $this->data['users'] = $this->kanban_dashboard_model->get_company_users($company_id);
            $this->data['configured_viewer_id'] = (int) ($this->input->get('viewer_id', true) ?: $user_id);
            $this->data['configured_access'] = $this->kanban_dashboard_model->get_access(
                $company_id,
                $this->data['configured_viewer_id']
            );
            $this->data['configured_goal_id'] = (int) ($this->input->get('goal_user_id', true) ?: $user_id);
            $this->data['configured_goal'] = $this->kanban_dashboard_model->get_goal(
                $company_id,
                $this->data['configured_goal_id']
            );
        }

        $this->template->admin_render('admin/kanban/planejamento', $this->data);
    }

    public function planejamento_apontamentos()
    {
        $this->output->set_content_type('application/json');
        if (!$this->ion_auth->logged_in()) {
            $this->output->set_status_header(401)->set_output(json_encode(array('erro' => 'Sessão expirada.')));
            return;
        }

        $is_admin = $this->ion_auth->is_admin();
        if (!$is_admin && !$this->ion_auth->has_permissao('kanban', 'index')) {
            $this->output->set_status_header(403)->set_output(json_encode(array('erro' => 'Você não tem permissão para acessar este painel.')));
            return;
        }

        $user_id = (int) $this->session->userdata('user_id');
        $company_id = (int) $this->session->userdata('empresa_id');
        $collaborator_id = (int) $this->input->get('colaborador', true);
        $start_date = $this->input->get('inicio', true);
        $end_date = $this->input->get('fim', true);

        $max_end_date = $this->valid_dashboard_date($start_date)
            ? (new DateTime($start_date))->modify('+89 days')->format('Y-m-d')
            : null;
        if (!$this->valid_dashboard_date($start_date) || !$this->valid_dashboard_date($end_date) ||
            $start_date > $end_date || $end_date > $max_end_date) {
            $this->output->set_status_header(400)->set_output(json_encode(array('erro' => 'Selecione um período válido de até 90 dias.')));
            return;
        }

        $visible_ids = array_map(function ($collaborator) {
            return (int) $collaborator->id;
        }, $this->kanban_dashboard_model->get_visible_collaborators($company_id, $user_id, $is_admin));
        if (!in_array($collaborator_id, $visible_ids, true)) {
            $this->output->set_status_header(403)->set_output(json_encode(array('erro' => 'Você não tem permissão para visualizar este colaborador.')));
            return;
        }

        $items = array();
        foreach ($this->kanban_dashboard_model->get_logged_tickets($company_id, $collaborator_id, $start_date, $end_date) as $row) {
            $items[] = array(
                'data' => date('d/m/Y', strtotime($row->data)),
                'chamado_id' => (int) $row->chamado_id,
                'titulo' => $row->titulo,
                'status' => $row->status,
                'unidade' => $row->unidade,
                'horas' => round((float) $row->horas, 2),
                'planejado' => (bool) $row->planejado,
                'url' => base_url('chamados/details/' . (int) $row->chamado_id),
            );
        }
        $this->output->set_output(json_encode(array('itens' => $items)));
    }

    private function valid_dashboard_date($date)
    {
        if (!is_string($date)) {
            return false;
        }
        $parsed = DateTime::createFromFormat('!Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    }

    private function valid_dashboard_datetime($datetime)
    {
        if (!is_string($datetime) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $datetime)) {
            return false;
        }
        $parsed = DateTime::createFromFormat('!Y-m-d\TH:i', $datetime);
        return $parsed && $parsed->format('Y-m-d\TH:i') === $datetime;
    }

    private function get_default_dashboard_statuses($statuses)
    {
        $selected = array();
        foreach ($statuses as $status) {
            $description = mb_strtolower(trim($status->descricao), 'UTF-8');
            if (in_array($description, array('aberto', 'fazendo', 'em andamento'), true)) {
                $selected[] = (int) $status->id;
            }
        }
        return $selected;
    }
}
