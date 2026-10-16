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

            $planos = $this->plano_acao_model->get();
            $planos_array = array();
            foreach ($planos->result() as $value) {
                $times = array();
                $apontamentos = $this->plano_acao_model->get_apontamentos($value->id);
                if($apontamentos->num_rows() > 0) {
                    foreach ($apontamentos->result() as $apont) {
                        $times[] = $apont->esforco;             
                    }
                }

                $value->horas_utilizadas = AddPlayTime($times);
                $planos_array[] = $value;
            }

            $this->data['planos'] = $planos_array;
            $this->data['can_create'] = $this->ion_auth->has_permissao("plano_acao", "create");
            $this->data['can_exportar_pdf'] = $this->ion_auth->has_permissao("plano_acao", "exportar_pdf");
            /*$this->data['status'] = array(
                'name'  => 'status',
                'id'    => 'status',
                'class' => 'form-control',
                'options' => array("" => "Selecione", "1" => "Aberto", "3" => "Em Avaliação", "5" => "Avaliado", "6" => "Aguardando Retorno do Cliente", "7" => "Suspenso", "8" => "Aguardando Testes com o Cliente"),
                'selected' => (@$_POST) ? ($this->input->post('status')) ? $this->input->post('status') : '' : ''
            );*/

            /* Load Template */
            $this->template->admin_render('admin/plano_acao/index', $this->data);
        }
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

        /* Validate form input */
        $this->form_validation->set_rules('responsavel', 'lang:responsavel', 'required');
        $this->form_validation->set_rules('desc', 'lang:desc', 'trim|required');
        $this->form_validation->set_rules('inicio_estimado', 'lang:inicio_estimado', 'required|validar_data');
        $this->form_validation->set_rules('termino_estimado', 'lang:termino_estimado', 'required|validar_data');
        $this->form_validation->set_rules('horas_prevista', 'lang:horas_prevista', 'required|validar_decimal');
        $this->form_validation->set_rules('tipo', 'lang:tipo', 'required');
        /*$this->form_validation->set_rules('inicio_real', 'lang:inicio_real', 'required|validar_data');
        $this->form_validation->set_rules('termino_real', 'lang:termino_real', 'required|validar_data');*/

        $dados["empresa_id"] = $this->session->userdata('empresa_id');
        $dados["unidade_id"] = $this->input->post('filial') ? $this->input->post('filial') : null;
        $dados["tipo"] = $this->input->post('tipo');
        $dados["responsavel"] = $this->input->post('responsavel');
        $dados["desc"] = $this->input->post('desc');
        $dados["txt"] = $this->input->post('txt');
        $dados["inicio_estimado"] = convertDateBRToEU($this->input->post('inicio_estimado'));
        $dados["termino_estimado"] = convertDateBRToEU($this->input->post('termino_estimado'));
        $dados["horas_prevista"] = $this->input->post('horas_prevista') ? str_replace(",", ".", $this->input->post('horas_prevista')) : null;
        $dados["inicio_real"] = ($this->input->post('inicio_real') != "") ? convertDateBRToEU($this->input->post('inicio_real')) : null;
        $dados["termino_real"] = ($this->input->post('termino_real') != "") ? convertDateBRToEU($this->input->post('termino_real')) : null;


        if ($this->form_validation->run() == TRUE && $id = $this->plano_acao_model->create($dados))
        {
            $this->session->set_flashdata('message', show_alert_success($this->plano_acao_model->messages()));
            redirect('plano_acao', 'refresh');
        } else {
            if($_POST)
                $this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->plano_acao_model->errors() ? show_alert_error($this->plano_acao_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['desc'] = array(
                'name'  => 'desc',
                'id'    => 'desc',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('desc')
            );
            $this->data['txt'] = array(
                'name'  => 'txt',
                'id'    => 'txt',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('txt')
            );
            $array_users = array(""=>"Selecione");
            foreach ($this->plano_acao_model->get_users()->result() as $value) {
                $array_users[$value->id] = $value->first_name." ".$value->last_name;
            }
            $this->data['usuarios'] = array(
                'name'  => 'responsavel',
                'id'    => 'responsavel',
                'class' => 'singleInputDynamic form-control',
                'options' => $array_users,
                'selected' => $this->form_validation->set_value('usuarios')
            );
            $this->data['inicio_estimado'] = array(
                'name'  => 'inicio_estimado',
                'id'    => 'inicio_estimado',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('inicio_estimado')
            );
            $this->data['termino_estimado'] = array(
                'name'  => 'termino_estimado',
                'id'    => 'termino_estimado',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('termino_estimado')
            );
            $this->data['inicio_real'] = array(
                'name'  => 'inicio_real',
                'id'    => 'inicio_real',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('inicio_real')
            );
            $this->data['termino_real'] = array(
                'name'  => 'termino_real',
                'id'    => 'termino_real',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('termino_real')
            );

            $array_unids = array(""=>"Selecione");
            foreach ($this->plano_acao_model->get_unidades()->result() as $value) {
                $array_unids[$value->id] = $value->unidade;
            }
            $this->data['filial'] = array(
                'name'  => 'filial',
                'id'    => 'filial',
                'class' => 'singleInputDynamic form-control',
                'options' => $array_unids,
                'selected' => $this->form_validation->set_value('filial')
            );

            $this->data['tipo'] = array(
                'name'  => 'tipo',
                'id'    => 'tipo',
                'class' => 'form-control',
                'options' => array("" => "Selecione", "I" => "Implantação", "D" => "Desenvolvimento"),
                'selected' => $this->form_validation->set_value('tipo')
            );

            /* Load Template */
            $this->template->admin_render('admin/plano_acao/create', $this->data);
        }
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

        /* Variables */
        $plano_acao = $this->plano_acao_model->get($id)->row();

        /* Validate form input */
        $this->form_validation->set_rules('responsavel', 'lang:responsavel', 'required');
        $this->form_validation->set_rules('desc', 'lang:desc', 'trim|required');
        $this->form_validation->set_rules('inicio_estimado', 'lang:inicio_estimado', 'required|validar_data');
        $this->form_validation->set_rules('termino_estimado', 'lang:termino_estimado', 'required|validar_data');
        $this->form_validation->set_rules('horas_prevista', 'lang:horas_prevista', 'required|validar_decimal');
        $this->form_validation->set_rules('tipo', 'lang:tipo', 'required');
        // $this->form_validation->set_rules('status', 'lang:status', 'required');
        /*$this->form_validation->set_rules('inicio_real', 'lang:inicio_real', 'required|validar_data');
        $this->form_validation->set_rules('termino_real', 'lang:termino_real', 'required|validar_data');*/

        if (isset($_POST) && ! empty($_POST))
        {
            $chamados_vinculados = $this->plano_acao_model->get_chamado('c.plano_acao = "'.$id.'" AND c.status_id != 3 AND c.status_id != 5');
            if($this->input->post('termino_real') != "" && $chamados_vinculados->num_rows() > 0) {
                $this->data['message'] = show_alert_error('O plano de ação somente poderá ser finalizado se todos os chamados vinculados estiverem com status "Em avaliação" ou "Avaliado".');
            } else {
                $dados["tipo"] = $this->input->post('tipo');
                $dados["responsavel"] = $this->input->post('responsavel');
                $dados["unidade_id"] = $this->input->post('filial');
                $dados["desc"] = $this->input->post('desc');
                $dados["txt"] = $this->input->post('txt');
                $inicio_estimado = explode(" ", $this->input->post('inicio_estimado'));
                $termino_estimado = explode(" ", $this->input->post('termino_estimado'));
                $dados["inicio_estimado"] = convertDateBRToEU($inicio_estimado[0])." ".$inicio_estimado[1];
                $dados["termino_estimado"] = convertDateBRToEU($termino_estimado[0])." ".$termino_estimado[1];
                $dados["horas_prevista"] = $this->input->post('horas_prevista') ? str_replace(",", ".", $this->input->post('horas_prevista')) : null;
                if($this->input->post('inicio_real') != "") {
                    $inicio_real = explode(" ", $this->input->post('inicio_real'));
                    $dados["inicio_real"] = convertDateBRToEU($inicio_real[0])." ".$inicio_real[1];
                } else {
                    $dados["inicio_real"] = null;
                }
                if($this->input->post('termino_real') != "") {
                    $chamados_vinculados = $this->plano_acao_model->get_chamado('c.plano_acao = "'.$id.'" AND c.status_id != 3 AND c.status_id != 5');
                    if($chamados_vinculados->num_rows() > 0) {
                        // $this->session->set_flashdata('message', show_alert_error('O plano de ação somente poderá ser finalizado se todos os chamados vinculados estiverem com status "Em avaliação" ou "Avaliado".'));
                    } else {
                        $dados["status"] = "FI";

                        $termino_real = explode(" ", $this->input->post('termino_real'));
                        $dados["termino_real"] = convertDateBRToEU($termino_real[0])." ".$termino_real[1];
                    }
                } else {
                    $dados["termino_real"] = null;
                }

                if ($this->form_validation->run() == TRUE)
                {
                    $plano_acao_update = $this->plano_acao_model->update($id, $dados);

                    if ($plano_acao_update)
                    {
                        $this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_plano_acao_saved')));
                    }
                    else
                    {
                        $this->session->set_flashdata('message', show_alert_error($this->plano_acao_model->errors()));
                    }

                    redirect('plano_acao', 'refresh');
                } else {
                    $this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->plano_acao_model->errors() ? show_alert_error($this->plano_acao_model->errors()) : show_alert_error($this->session->flashdata('message'))));
                }
            }
        }

        $this->data['desc'] = array(
            'name'  => 'desc',
            'id'    => 'desc',
            'type'  => 'text',
            'class' => 'form-control',
            'value' => $this->form_validation->set_value('desc', $plano_acao->desc)
        );
        $this->data['txt'] = array(
            'name'  => 'txt',
            'id'    => 'txt',
            'type'  => 'text',
            'class' => 'form-control',
            'value' => $this->form_validation->set_value('txt', $plano_acao->txt)
        );
        $array_users = array(""=>"Selecione");
        foreach ($this->plano_acao_model->get_users()->result() as $value) {
            $array_users[$value->id] = $value->first_name." ".$value->last_name;
        }
        $this->data['usuarios'] = array(
            'name'  => 'responsavel',
            'id'    => 'responsavel',
            'class' => 'singleInputDynamic form-control',
            'options' => $array_users,
            'selected' => $this->form_validation->set_value('usuarios', $plano_acao->responsavel)
        );

        $inicio_estimado = explode(" ", $plano_acao->inicio_estimado);
        $inicio_estimado_data = convertDateEUToBR($inicio_estimado[0]);
        $this->data['inicio_estimado'] = array(
            'name'  => 'inicio_estimado',
            'id'    => 'inicio_estimado',
            'type'  => 'text',
            'class' => 'form-control',
            'value' => $this->form_validation->set_value('inicio_estimado', $inicio_estimado_data." ".$inicio_estimado[1])
        );
        $termino_estimado = explode(" ", $plano_acao->termino_estimado);
        $termino_estimado_data = convertDateEUToBR($termino_estimado[0]);
        $this->data['termino_estimado'] = array(
            'name'  => 'termino_estimado',
            'id'    => 'termino_estimado',
            'type'  => 'text',
            'class' => 'form-control',
            'value' => $this->form_validation->set_value('termino_estimado', $termino_estimado_data." ".$termino_estimado[1])
        );

        if($plano_acao->inicio_real != "") {
            $inicio_real = explode(" ", $plano_acao->inicio_real);
            $inicio_real_data = convertDateEUToBR($inicio_real[0]);
            $inicio_real_input = $inicio_real_data." ".$inicio_real[1];
        } else {
            $inicio_real_input = "";
        }
        $this->data['inicio_real'] = array(
            'name'  => 'inicio_real',
            'id'    => 'inicio_real',
            'type'  => 'text',
            'class' => 'form-control',
            'value' => $this->form_validation->set_value('inicio_real', $inicio_real_input)
        );
        if($plano_acao->termino_real != "") {
            $termino_real = explode(" ", $plano_acao->termino_real);
            $termino_real_data = convertDateEUToBR($termino_real[0]);
            $termino_real_input = $termino_real_data." ".$termino_real[1];
        } else {
            $termino_real_input = "";
        }
        $this->data['termino_real'] = array(
            'name'  => 'termino_real',
            'id'    => 'termino_real',
            'type'  => 'text',
            'class' => 'form-control',
            'value' => $this->form_validation->set_value('termino_real', $termino_real_input)
        );

        $array_unids = array(""=>"Selecione");
        foreach ($this->plano_acao_model->get_unidades()->result() as $value) {
            $array_unids[$value->id] = $value->unidade;
        }
        $this->data['filial'] = array(
            'name'  => 'filial',
            'id'    => 'filial',
            'class' => 'singleInputDynamic form-control',
            'options' => $array_unids,
            'selected' => $this->form_validation->set_value('filial', $plano_acao->unidade_id)
        );
        if($plano_acao->horas_prevista != "" && $plano_acao->horas_prevista != null) {
            if($this->input->post('horas_prevista'))
                $this->data['horas_prevista'] = str_replace(",", ".", $this->input->post('horas_prevista'));
            else
                $this->data['horas_prevista'] = $plano_acao->horas_prevista;
        } else {
            if($this->input->post('horas_prevista'))
                $this->data['horas_prevista'] = str_replace(",", ".", $this->input->post('horas_prevista'));
            else
                $this->data['horas_prevista'] = "";
        }

        // $this->data['status'] = array(
        //     'name'  => 'status',
        //     'id'    => 'status',
        //     'class' => 'form-control',
        //     'options' => array("AI" => "A Iniciar", "EA" => "Em Andamento", "FI" => "Finalizado"),
        //     'selected' => $this->form_validation->set_value('status', $plano_acao->status)
        // );

        $this->data['tipo'] = array(
                'name'  => 'tipo',
                'id'    => 'tipo',
                'class' => 'form-control',
                'options' => array("" => "Selecione", "I" => "Implantação", "D" => "Desenvolvimento"),
                'selected' => $this->form_validation->set_value('tipo', $plano_acao->tipo)
            );

        /* Load Template */
        $this->template->admin_render('admin/plano_acao/edit', $this->data);
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
            $this->data['message'] = show_alert_error('O plano de ação somente poderá ser finalizado se todos os chamados vinculados estiverem com status "Em avaliação" ou "Avaliado".');
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

            if($_POST["ids"] != "") {
                $planos = $this->plano_acao_model->get(null, "p.id IN (".$_POST["ids"].")", array('p.tipo' => 'asc', 'p.status' => 'asc'))->result();
                
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
