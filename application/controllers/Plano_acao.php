<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Plano_acao extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/plano_acao');
        $this->load->model('plano_acao_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_plano_acao'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_plano_acao'), 'plano_acao');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("plano_acao", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            $status_options = array(
                'abertos' => array('AI', 'EA'),
                'AI' => array('AI'),
                'EA' => array('EA'),
                'FI' => array('FI'),
                'todos' => array(),
            );
            $status_filter = $this->input->get('filtro_status', true);
            if (!isset($status_options[$status_filter])) {
                $status_filter = 'abertos';
            }
            $parse_date = function ($value) {
                $date = DateTime::createFromFormat('!d/m/Y', (string) $value);
                return $date ? $date->format('Y-m-d') : '';
            };
            $only_mine = $this->input->get('meus') === '1';
            $filters = array(
                'status' => $status_options[$status_filter],
                'q' => trim((string) $this->input->get('q', true)),
                'responsavel' => $only_mine ? (int) $this->session->userdata('user_id') : (int) $this->input->get('filtro_responsavel'),
                'unidade' => (int) $this->input->get('filtro_unidade'),
                'tipo' => in_array($this->input->get('filtro_tipo'), array('I', 'D'), true) ? $this->input->get('filtro_tipo') : '',
                'inicio_de' => $parse_date($this->input->get('inicio_de')),
                'inicio_ate' => $parse_date($this->input->get('inicio_ate')),
                'termino_de' => $parse_date($this->input->get('termino_de')),
                'termino_ate' => $parse_date($this->input->get('termino_ate')),
                'atrasados' => $this->input->get('atrasados') === '1',
            );

            $offset = max(0, (int) $this->input->get('per_page'));
            $total = $this->plano_acao_model->count_lista($filters);
            $planos = $this->plano_acao_model->get_lista($filters, 15, $offset);
            $resumo = $this->plano_acao_model->get_resumo_chamados(array_map(function ($plano) {
                return (int) $plano->id;
            }, $planos));
            foreach ($planos as $plano) {
                $times = array();
                foreach ($this->plano_acao_model->get_apontamentos($plano->id)->result() as $apont) {
                    $times[] = $apont->esforco;
                }
                $plano->horas_utilizadas = AddPlayTime($times);
                $plano->resumo = isset($resumo[(int) $plano->id]) ? $resumo[(int) $plano->id] : null;
            }

            $this->pagination->initialize(get_pagination_config(current_url(), $total, $offset));
            $this->data['pagination'] = $this->pagination->create_links();
            $this->data['planos'] = $planos;
            $this->data['total_planos'] = $total;
            $this->data['filters'] = $filters;
            $this->data['status_filter'] = $status_filter;
            $this->data['only_mine'] = $only_mine;
            $this->data['filter_dates'] = array(
                'inicio_de' => $this->input->get('inicio_de', true),
                'inicio_ate' => $this->input->get('inicio_ate', true),
                'termino_de' => $this->input->get('termino_de', true),
                'termino_ate' => $this->input->get('termino_ate', true),
            );
            $this->data['limpar'] = $_GET ? 1 : 0;
            $this->data['usuarios_filtro'] = $this->plano_acao_model->get_users()->result();
            $this->data['unidades_filtro'] = $this->plano_acao_model->get_unidades()->result();
            $this->data['can_create'] = $this->ion_auth->has_permissao("plano_acao", "create");
            $this->data['can_exportar_pdf'] = $this->ion_auth->has_permissao("plano_acao", "exportar_pdf");
            $this->data['can_edit'] = $this->ion_auth->has_permissao("plano_acao", "edit");

            /* Load Template */
            $this->template->admin_render('admin/plano_acao/index', $this->data);
        }
    }

    /**
     * Cronograma (Gantt) somente para visualização.
     * Sem $id mostra todos os projetos; com $id mostra o projeto e seus chamados.
     */
    public function gantt($id = null)
    {
        $permission = $id ? 'view' : 'index';
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao('plano_acao', $permission)) {
            redirect('auth/login', 'refresh');
            return;
        }

        $include_finished = $this->input->get('finalizados') === '1';
        if ($id) {
            $projects = $this->plano_acao_model->get((int) $id)->result();
            if (!$projects) {
                redirect('plano_acao', 'refresh');
                return;
            }
        } else {
            $projects = $this->plano_acao_model->get(null, $include_finished ? '' : "p.status != 'FI'", array('p.inicio_estimado' => 'asc'))->result();
        }

        $project_ids = array_map(function ($project) {
            return (int) $project->id;
        }, $projects);
        $tickets_by_project = array();
        foreach ($this->plano_acao_model->get_chamados_gantt($project_ids) as $ticket) {
            $tickets_by_project[(int) $ticket->plano_acao][] = $ticket;
        }

        $tasks = array();
        $summary = array('total' => 0, 'done' => 0, 'late' => 0, 'estimated' => 0, 'active' => 0);
        $project_rows = array();
        foreach ($projects as $project) {
            $tickets = isset($tickets_by_project[(int) $project->id]) ? $tickets_by_project[(int) $project->id] : array();
            $ticket_tasks = array();
            $done = 0;
            $late = 0;
            foreach ($tickets as $ticket) {
                $task = $this->gantt_ticket_task($ticket);
                $ticket_tasks[] = $task;
                $done += $task['situacao'] === 'done' ? 1 : 0;
                $late += $task['situacao'] === 'late' ? 1 : 0;
                if ($id) {
                    $summary['total']++;
                    $summary[$task['situacao']]++;
                    $summary['estimated'] += $task['estimado'] ? 1 : 0;
                }
            }

            $project_task = $this->gantt_project_task($project, $ticket_tasks);
            $project_task['progress'] = $tickets ? round($done / count($tickets) * 100) : ($project->status === 'Finalizado' ? 100 : 0);
            $project_rows[] = array(
                'project' => $project,
                'task' => $project_task,
                'tickets' => count($tickets),
                'done' => $done,
                'late' => $late,
            );
            $tasks[] = $project_task;
            if ($id) {
                $tasks = array_merge($tasks, $ticket_tasks);
            }
        }

        if ($id) {
            $this->breadcrumbs->unshift(2, lang('plano_acao_view'), 'plano_acao/view/' . (int) $id);
            $this->page_title->push('Cronograma: ' . html_escape($projects[0]->desc));
        } else {
            $this->breadcrumbs->unshift(2, 'Cronograma', 'plano_acao/gantt');
            $this->page_title->push('Cronograma dos projetos');
        }
        $this->data['pagetitle'] = $this->page_title->show();
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['project_id'] = $id ? (int) $id : null;
        $this->data['can_edit'] = $this->ion_auth->has_permissao('plano_acao', 'edit');
        $this->data['can_novo_chamado'] = $this->ion_auth->has_permissao('chamados', 'create');
        $this->data['project_rows'] = $project_rows;
        $this->data['tickets'] = $id ? array_slice($tasks, 1) : array();
        $this->data['summary'] = $summary;
        $this->data['include_finished'] = $include_finished;
        $this->data['gantt_tasks'] = array_map(function ($task) {
            return array(
                'id' => $task['id'],
                'name' => $task['name'],
                'start' => $task['start'],
                'end' => $task['end'],
                'progress' => $task['progress'],
                'custom_class' => $task['custom_class'],
                'description' => $task['description'],
                'url' => $task['url'],
                'periodo' => $task['periodo'],
                'situacao_label' => $task['situacao_label'],
            );
        }, $tasks);

        $this->template->admin_render('admin/plano_acao/gantt', $this->data);
    }

    private function gantt_ticket_task($ticket)
    {
        $level_hours = array(1 => 2, 2 => 4, 3 => 8, 4 => 16);
        $start = $ticket->data_inicio ?: $ticket->created;
        $start_date = date('Y-m-d', strtotime($start));
        $finished = in_array((int) $ticket->status_id, array(3, 5), true) || !empty($ticket->data_finalizacao);
        $estimated = false;
        if ($finished && !empty($ticket->data_finalizacao)) {
            $end_date = date('Y-m-d', strtotime($ticket->data_finalizacao));
        } elseif (!empty($ticket->data_prazo)) {
            $end_date = date('Y-m-d', strtotime($ticket->data_prazo));
        } else {
            // Sem previsão: estima a duração pelas horas previstas ou pelo nível (8 h por dia útil).
            $level = (int) preg_replace('/\D/', '', (string) $ticket->nivel);
            $hours = (float) $ticket->horas_prevista > 0 ? (float) $ticket->horas_prevista : (isset($level_hours[$level]) ? $level_hours[$level] : 8);
            $end_date = date('Y-m-d', strtotime($start_date . ' +' . max(0, (int) ceil($hours / 8) - 1) . ' days'));
            $estimated = true;
        }
        if ($end_date < $start_date) {
            $end_date = $start_date;
        }

        if ($finished) {
            $situacao = 'done';
            $label = 'Concluído';
        } elseif (!empty($ticket->data_prazo) && $ticket->data_prazo < date('Y-m-d H:i:s')) {
            $situacao = 'late';
            $label = 'Atrasado';
        } else {
            $situacao = 'active';
            $label = 'Em andamento';
        }

        return array(
            'id' => 'c' . (int) $ticket->id,
            'chamado_id' => (int) $ticket->id,
            'name' => '#' . (int) $ticket->id . ' ' . $ticket->titulo,
            'titulo' => $ticket->titulo,
            'start' => $start_date,
            'end' => $end_date,
            'progress' => $finished ? 100 : 0,
            // O Frappe Gantt aceita uma única classe por barra (sem espaços).
            'custom_class' => 'gantt-' . $situacao . ($estimated ? '-estimado' : ''),
            'description' => trim(($ticket->status ?: '') . ($ticket->responsavel ? ' · ' . $ticket->responsavel : '')),
            'url' => base_url('chamados/details/' . (int) $ticket->id),
            'periodo' => date('d/m/Y', strtotime($start_date)) . ' a ' . date('d/m/Y', strtotime($end_date)),
            'situacao' => $situacao,
            'situacao_label' => $label . ($estimated ? ' (prazo estimado)' : ''),
            'estimado' => $estimated,
            'status' => $ticket->status,
            'responsavel' => $ticket->responsavel,
            'unidade' => $ticket->unidade,
        );
    }

    private function gantt_project_task($project, $ticket_tasks)
    {
        $start_date = $project->inicio_estimado ? date('Y-m-d', strtotime($project->inicio_estimado)) : null;
        $end_date = $project->termino_estimado ? date('Y-m-d', strtotime($project->termino_estimado)) : null;
        foreach ($ticket_tasks as $task) {
            $start_date = (!$start_date || $task['start'] < $start_date) ? $task['start'] : $start_date;
            $end_date = (!$end_date || $task['end'] > $end_date) ? $task['end'] : $end_date;
        }
        $start_date = $start_date ?: date('Y-m-d');
        $end_date = ($end_date && $end_date >= $start_date) ? $end_date : $start_date;

        $finished = $project->status === 'Finalizado';
        $late = !$finished && $project->termino_estimado && $project->termino_estimado < date('Y-m-d H:i:s');
        $situacao = $finished ? 'done' : ($late ? 'late' : 'active');

        return array(
            'id' => 'p' . (int) $project->id,
            'name' => $project->desc,
            'start' => $start_date,
            'end' => $end_date,
            'progress' => 0,
            'custom_class' => 'gantt-projeto-' . $situacao,
            'description' => trim($project->status . ($project->nome_resp ? ' · ' . $project->nome_resp : '')),
            'url' => base_url('plano_acao/gantt/' . (int) $project->id),
            'periodo' => date('d/m/Y', strtotime($start_date)) . ' a ' . date('d/m/Y', strtotime($end_date)),
            'situacao' => $situacao,
            'situacao_label' => $finished ? 'Finalizado' : ($late ? 'Atrasado' : $project->status),
        );
    }

    public function create()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("plano_acao", "create"))
        {
            redirect('auth', 'refresh');
        }

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_plano_acao_create'), 'plano_acao/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $opcoes = $this->opcoes_formulario_projeto();
        $this->regras_formulario_projeto($opcoes);
        list($inicio, $termino, $periodo_invalido) = $this->periodo_formulario_projeto();

        // O início e o término reais são preenchidos pelo sistema (primeiro chamado aberto e finalização).
        $dados = array_merge($this->dados_formulario_projeto($inicio, $termino), array(
            "empresa_id" => $this->session->userdata('empresa_id'),
            "inicio_real" => null,
            "termino_real" => null,
        ));

        if ($this->form_validation->run() == TRUE && !$periodo_invalido && $id = $this->plano_acao_model->create($dados))
        {
            $this->session->set_flashdata('message', show_alert_success('Projeto criado. Adicione os chamados pelo botão "Novo chamado" e acompanhe pelo cronograma.'));
            redirect('plano_acao/gantt/' . (int) $id, 'refresh');
            return;
        }

        if ($_POST) {
            $this->data['message'] = $this->erros_formulario_projeto($periodo_invalido);
        }

        $this->data = array_merge($this->data, $opcoes);
        $this->data['modo'] = 'create';
        $this->data['valores'] = array(
            'desc' => $this->form_validation->set_value('desc'),
            'txt' => $this->form_validation->set_value('txt'),
            'tipo' => $this->form_validation->set_value('tipo', 'I'),
            'responsavel' => $this->form_validation->set_value('responsavel', (string) $this->session->userdata('user_id')),
            'filial' => $this->form_validation->set_value('filial'),
            'inicio_estimado' => $this->form_validation->set_value('inicio_estimado', date('d/m/Y')),
            'termino_estimado' => $this->form_validation->set_value('termino_estimado'),
            'horas_prevista' => $this->form_validation->set_value('horas_prevista'),
        );

        /* Load Template */
        $this->template->admin_render('admin/plano_acao/create', $this->data);
    }

    public function edit($id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("plano_acao", "edit"))
        {
            redirect('auth', 'refresh');
        }

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_plano_acao_edit'), 'plano_acao/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $id = (int) $id;
        $plano = $this->db->get_where('plano_acao', array('id' => $id, 'empresa_id' => (int) $this->session->userdata('empresa_id')))->row();
        if (!$plano) {
            $this->session->set_flashdata('message', show_alert_error('Projeto não encontrado.'));
            redirect('plano_acao', 'refresh');
            return;
        }
        $pendentes = $this->plano_acao_model->get_chamado('c.plano_acao = ' . $id . ' AND c.status_id != 3 AND c.status_id != 5')->num_rows();
        $total_chamados = $this->plano_acao_model->get_chamado('c.plano_acao = ' . $id)->num_rows();

        $opcoes = $this->opcoes_formulario_projeto();
        $this->regras_formulario_projeto($opcoes);
        $this->form_validation->set_rules('inicio_real', 'Início real', 'validar_data');
        $this->form_validation->set_rules('termino_real', 'Término real', 'validar_data');
        list($inicio, $termino, $periodo_invalido) = $this->periodo_formulario_projeto();

        if ($_POST)
        {
            $inicio_real = $this->input->post('inicio_real') ? convertDateBRToEU($this->input->post('inicio_real')) : null;
            $termino_real = $this->input->post('termino_real') ? convertDateBRToEU($this->input->post('termino_real')) : null;
            $erro_finalizacao = $termino_real && $pendentes > 0;

            $dados = array_merge($this->dados_formulario_projeto($inicio, $termino), array(
                "inicio_real" => $inicio_real,
                "termino_real" => $termino_real,
            ));
            // Preencher o término real finaliza o projeto; apagá-lo reabre um projeto finalizado.
            if ($termino_real) {
                $dados["status"] = "FI";
            } elseif ($plano->status === 'FI') {
                $dados["status"] = $inicio_real ? "EA" : "AI";
            }

            if ($this->form_validation->run() == TRUE && !$periodo_invalido && !$erro_finalizacao)
            {
                if ($this->plano_acao_model->update($id, $dados)) {
                    $this->session->set_flashdata('message', show_alert_success($termino_real && $plano->status !== 'FI' ? 'Projeto finalizado.' : 'Projeto atualizado.'));
                } else {
                    $this->session->set_flashdata('message', show_alert_error($this->plano_acao_model->errors()));
                }
                redirect('plano_acao/gantt/' . $id, 'refresh');
                return;
            }

            $this->data['message'] = $this->erros_formulario_projeto($periodo_invalido,
                $erro_finalizacao ? 'O projeto só pode ser finalizado quando todos os chamados vinculados estiverem "Em avaliação" ou "Avaliado" (' . $pendentes . ' pendente' . ($pendentes === 1 ? '' : 's') . ').' : '');
        }

        $data_br = function ($valor) {
            return $valor ? date('d/m/Y', strtotime($valor)) : '';
        };
        $this->data = array_merge($this->data, $opcoes);
        $this->data['modo'] = 'edit';
        $this->data['plano'] = $plano;
        $this->data['execucao'] = array('pendentes' => $pendentes, 'total' => $total_chamados);
        $this->data['valores'] = array(
            'desc' => $this->form_validation->set_value('desc', $plano->desc),
            'txt' => $this->form_validation->set_value('txt', $plano->txt),
            'tipo' => $this->form_validation->set_value('tipo', $plano->tipo),
            'responsavel' => $this->form_validation->set_value('responsavel', (string) $plano->responsavel),
            'filial' => $this->form_validation->set_value('filial', (string) $plano->unidade_id),
            'inicio_estimado' => $this->form_validation->set_value('inicio_estimado', $data_br($plano->inicio_estimado)),
            'termino_estimado' => $this->form_validation->set_value('termino_estimado', $data_br($plano->termino_estimado)),
            'horas_prevista' => $this->form_validation->set_value('horas_prevista', $plano->horas_prevista !== null ? str_replace('.', ',', rtrim(rtrim($plano->horas_prevista, '0'), '.')) : ''),
            'inicio_real' => $this->form_validation->set_value('inicio_real', $data_br($plano->inicio_real)),
            'termino_real' => $this->form_validation->set_value('termino_real', $data_br($plano->termino_real)),
        );

        /* Load Template */
        $this->template->admin_render('admin/plano_acao/edit', $this->data);
    }

    private function opcoes_formulario_projeto()
    {
        $usuarios = array("" => "Selecione");
        foreach ($this->plano_acao_model->get_users()->result() as $value) {
            $usuarios[$value->id] = trim($value->first_name . " " . $value->last_name);
        }
        $unidades = array("" => "Todos os clientes");
        foreach ($this->plano_acao_model->get_unidades()->result() as $value) {
            $unidades[$value->id] = $value->unidade;
        }
        return array(
            'usuarios_options' => $usuarios,
            'unidades_options' => $unidades,
            'tipos' => array("I" => "Implantação", "D" => "Desenvolvimento"),
        );
    }

    private function regras_formulario_projeto($opcoes)
    {
        $this->form_validation->set_rules('desc', 'lang:desc', 'trim|required|max_length[150]');
        $this->form_validation->set_rules('tipo', 'lang:tipo', 'required|in_list[I,D]');
        $this->form_validation->set_rules('responsavel', 'lang:responsavel', 'required|in_list[' . implode(',', array_filter(array_keys($opcoes['usuarios_options']))) . ']');
        $this->form_validation->set_rules('filial', 'Cliente', 'in_list[' . implode(',', array_keys($opcoes['unidades_options'])) . ']');
        $this->form_validation->set_rules('inicio_estimado', 'lang:inicio_estimado', 'required|validar_data');
        $this->form_validation->set_rules('termino_estimado', 'lang:termino_estimado', 'required|validar_data');
        $this->form_validation->set_rules('horas_prevista', 'lang:horas_prevista', 'required|validar_decimal');
    }

    private function periodo_formulario_projeto()
    {
        $inicio = $this->input->post('inicio_estimado') ? convertDateBRToEU($this->input->post('inicio_estimado')) : '';
        $termino = $this->input->post('termino_estimado') ? convertDateBRToEU($this->input->post('termino_estimado')) : '';
        return array($inicio, $termino, $_POST && $inicio && $termino && $termino < $inicio);
    }

    private function dados_formulario_projeto($inicio, $termino)
    {
        return array(
            "unidade_id" => $this->input->post('filial') ? (int) $this->input->post('filial') : null,
            "tipo" => $this->input->post('tipo'),
            "responsavel" => (int) $this->input->post('responsavel'),
            "desc" => $this->input->post('desc'),
            "txt" => $this->input->post('txt'),
            "inicio_estimado" => $inicio,
            "termino_estimado" => $termino,
            "horas_prevista" => $this->input->post('horas_prevista') ? str_replace(",", ".", $this->input->post('horas_prevista')) : null,
        );
    }

    private function erros_formulario_projeto($periodo_invalido, $erro_extra = '')
    {
        $erros = validation_errors();
        if ($periodo_invalido) {
            $erros .= '<p>O término estimado deve ser igual ou posterior ao início.</p>';
        }
        if ($erro_extra !== '') {
            $erros .= '<p>' . $erro_extra . '</p>';
        }
        return $erros !== '' ? show_alert_error($erros) : show_alert_error($this->plano_acao_model->errors());
    }

    public function view($id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("plano_acao", "view"))
        {
            redirect('auth', 'refresh');
        }

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_plano_acao_view'), 'plano_acao/view');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
        $plano_acao = $this->plano_acao_model->get($id)->row();

        $this->data['desc'] = array(
            'name'  => 'desc',
            'id'    => 'desc',
            'type'  => 'text',
            'class' => 'form-control',
            'disabled' => 'disabled',
            'value' => $this->form_validation->set_value('desc', $plano_acao->desc)
        );
        $this->data['txt'] = array(
            'name'  => 'txt',
            'id'    => 'txt',
            'type'  => 'text',
            'class' => 'form-control',
            'disabled' => 'disabled',
            'value' => $this->form_validation->set_value('txt', $plano_acao->txt)
        );
        $array_users = array(""=>"Selecione");
        foreach ($this->plano_acao_model->get_users()->result() as $value) {
            $array_users[$value->id] = $value->first_name." ".$value->last_name;
        }
        $this->data['usuarios'] = array(
            'name'  => 'responsavel',
            'id'    => 'responsavel',
            'disabled' => 'disabled',
            'class' => 'form-control',
            'options' => $array_users,
            'selected' => $this->form_validation->set_value('usuarios', $plano_acao->responsavel)
        );

        $inicio_estimado = explode(" ", $plano_acao->inicio_estimado);
        $inicio_estimado_data = convertDateEUToBR($inicio_estimado[0]);
        $this->data['inicio_estimado'] = array(
            'name'  => 'inicio_estimado',
            'id'    => 'inicio_estimado',
            'disabled' => 'disabled',
            'type'  => 'text',
            'class' => 'form-control',
            'value' => $this->form_validation->set_value('inicio_estimado', $inicio_estimado_data." ".$inicio_estimado[1])
        );
        $termino_estimado = explode(" ", $plano_acao->termino_estimado);
        $termino_estimado_data = convertDateEUToBR($termino_estimado[0]);
        $this->data['termino_estimado'] = array(
            'name'  => 'termino_estimado',
            'id'    => 'termino_estimado',
            'disabled' => 'disabled',
            'type'  => 'text',
            'class' => 'form-control',
            'value' => $this->form_validation->set_value('termino_estimado', $termino_estimado_data." ".$termino_estimado[1])
        );

        if($plano_acao->inicio_real != "") {
            $inicio_real = explode(" ", $plano_acao->inicio_real);
            $inicio_real_data = convertDateEUToBR($inicio_real[0]);
            $this->data['inicio_real'] = array(
                'name'  => 'inicio_real',
                'id'    => 'inicio_real',
                'disabled' => 'disabled',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('inicio_real', $inicio_real_data." ".$inicio_real[1])
            );
        } else {
            $this->data['inicio_real'] = array(
                'name'  => 'inicio_real',
                'id'    => 'inicio_real',
                'disabled' => 'disabled',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('inicio_real', "")
            );
        }
        if($plano_acao->termino_real != "") {
            $termino_real = explode(" ", $plano_acao->termino_real);
            $termino_real_data = convertDateEUToBR($termino_real[0]);
            $this->data['termino_real'] = array(
                'name'  => 'termino_real',
                'id'    => 'termino_real',
                'disabled' => 'disabled',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('termino_real', $termino_real_data." ".$termino_real[1])
            );
        } else {
            $this->data['termino_real'] = array(
                'name'  => 'termino_real',
                'id'    => 'termino_real',
                'disabled' => 'disabled',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('termino_real', '')
            );
        }

        $array_unids = array(""=>"Selecione");
        foreach ($this->plano_acao_model->get_unidades()->result() as $value) {
            $array_unids[$value->id] = $value->unidade;
        }
        $this->data['filial'] = array(
            'name'  => 'filial',
            'id'    => 'filial',
            'class' => 'form-control',
            'disabled' => 'disabled',
            'options' => $array_unids,
            'selected' => $this->form_validation->set_value('filial', $plano_acao->unidade_id)
        );
        $this->data['horas_prevista'] = ($plano_acao->horas_prevista != "" && $plano_acao->horas_prevista != null) ? $plano_acao->horas_prevista : "";

        $this->data['tipo'] = array(
            'name'  => 'tipo',
            'id'    => 'tipo',
            'class' => 'form-control',
            'disabled' => 'disabled',
            'options' => array("" => "Selecione", "I" => "Implantação", "D" => "Desenvolvimento"),
            'selected' => $this->form_validation->set_value('tipo', $plano_acao->tipo)
        );

        /* Load Template */
        $this->template->admin_render('admin/plano_acao/view', $this->data);
    }

    public function finalizar($id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("plano_acao", "finalizar"))
        {
            redirect('auth', 'refresh');
        }

        $plano_acao = $this->plano_acao_model->get($id)->row();

        $chamados_vinculados = $this->plano_acao_model->get_chamado('c.plano_acao = "'.$id.'" AND c.status_id != 3 AND c.status_id != 5');
        if($this->input->post('termino_real') != "" && $chamados_vinculados->num_rows() > 0) {
            $this->data['message'] = show_alert_error('O projeto somente poderá ser finalizado se todos os chamados vinculados estiverem com status "Em avaliação" ou "Avaliado".');
        } else {
            $dados["status"] = "FI";
            $dados["termino_real"] = date("Y-m-d H:i:s");
            $plano_acao_update = $this->plano_acao_model->update($id, $dados);

            if ($plano_acao_update)
            {
                $this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_plano_acao_saved')));
            }
            else
            {
                $this->session->set_flashdata('message', show_alert_error($this->plano_acao_model->errors()));
            }
        }

        redirect('plano_acao', 'refresh');
    }

    public function exportar_pdf()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("plano_acao", "exportar_pdf"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {

            $ids = array_filter(array_map('intval', explode(',', (string) $this->input->post('ids'))));
            if($ids) {
                $planos = $this->plano_acao_model->get(null, "p.id IN (".implode(',', $ids).")", array('p.tipo' => 'asc', 'p.status' => 'asc'))->result();
                
                if(count($planos) > 0) {
                    foreach ($planos as $key => $plano) {
                        $times = array();
                        $apontamentos = $this->plano_acao_model->get_apontamentos($plano->id);
                        if($apontamentos->num_rows() > 0) {
                            foreach ($apontamentos->result() as $apont) {
                                $times[] = $apont->esforco;             
                            }
                        }

                        $planos[$key]->horas_utilizadas = AddPlayTime($times);
                        $planos[$key]->tarefas = $this->plano_acao_model->get_tarefas($plano->id)->result();
                    }
                }

                $this->load->library('pdf');
                $nome_relatorio = "pdf-planos_acao-".time();
                $caminho_arquivo = "./upload/relatorios/".$nome_relatorio;
                $this->pdf->load_view_save('admin/email/planos_acao.tpl.php', $caminho_arquivo, array("planos" => $planos));
                $this->load->helper('download');
                $data = file_get_contents(str_replace("https", "http", base_url($caminho_arquivo.".pdf")));
                unlink(FCPATH."upload/relatorios/".$nome_relatorio.".pdf");
                force_download($nome_relatorio.".pdf", $data);
            }
        }
    }
}
