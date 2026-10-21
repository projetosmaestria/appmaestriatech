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
}
