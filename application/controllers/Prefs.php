<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Prefs extends Admin_Controller {


    public function __construct()

    {

        parent::__construct();


        /* Load :: Common */

        $this->load->model('preferences_model');

        $this->lang->load('admin/preferences');

        $this->load->helper('functions_helper');


        /* Breadcrumbs :: Common */

        $this->breadcrumbs->unshift(1, lang('menu_preferences'), 'admin/prefs');

    }


	public function index()

	{

        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("prefs", "index"))

        {

            redirect('auth', 'refresh');

        }

        else

        {

            /* Title Page */

            $this->page_title->push(lang('menu_preferences'), lang('menu_interfaces'));

            $this->data['pagetitle'] = $this->page_title->show();


            /* Breadcrumbs :: Common */

            $this->breadcrumbs->unshift(2, lang('menu_int_admin'), 'admin/prefs/index');


            /* Breadcrumbs */

            $this->data['breadcrumb'] = $this->breadcrumbs->show();


            /* Validate form input */

            $this->form_validation->set_rules('user_panel', 'lang:prefs_user_panel', 'required|is_numeric');

            $this->form_validation->set_rules('empresa_sidebar', 'lang:prefs_empresa_sidebar', 'required|is_numeric');

            $this->form_validation->set_rules('sidebar_form', 'lang:prefs_sidebar_form', 'required|is_numeric');

            $this->form_validation->set_rules('messages_menu', 'lang:prefs_messages_menu', 'required|is_numeric');

            $this->form_validation->set_rules('notifications_menu', 'lang:prefs_notifications_menu', 'required|is_numeric');

            $this->form_validation->set_rules('tasks_menu', 'lang:prefs_tasks_menu', 'required|is_numeric');

            $this->form_validation->set_rules('user_menu', 'lang:prefs_user_menu', 'required|is_numeric');

            $this->form_validation->set_rules('ctrl_sidebar', 'lang:prefs_ctrl_sidebar', 'required|is_numeric');

            $this->form_validation->set_rules('transition_page', 'lang:prefs_transition_page', 'required|is_numeric');

            $this->form_validation->set_rules('grupo_admin', 'lang:prefs_grupo_admin', 'required');

            $this->form_validation->set_rules('qtd_alunos', 'lang:prefs_qtd_alunos', 'required|is_numeric');

            $this->form_validation->set_rules('relatorio_tutor', 'lang:prefs_relatorio_tutor', 'required');

            $this->form_validation->set_rules('relatorio_supervisor', 'lang:prefs_relatorio_supervisor', 'required');

            $this->form_validation->set_rules('horas_expiracao_cupom', 'lang:prefs_horas_expiracao_cupom', 'required|is_numeric');
            $this->form_validation->set_rules('cadastro_user_completo', 'lang:cadastro_user_completo', 'required|is_numeric');


            $this->data['admin_pref_interface'] = $this->preferences_model->get_interface('admin_preferences');


            $grupos_responsavel_chamado = $this->preferences_model->get_groups();

            $this->data['grupos_responsavel_chamado'] = array(

                'name'  => 'grupos_responsavel_chamado[]',

                'id'    => 'grupos_responsavel_chamado',

                'class' => 'multipleSelect',

                'multiple' => 'multiple',

                'options' => $grupos_responsavel_chamado,

                'selected' => $this->form_validation->set_value('grupos_responsavel_chamado', unserialize($this->data['admin_pref_interface']->grupos_responsavel_chamado))

            );


            $cats_grupo_responsavel = $this->preferences_model->get_cats();

            $this->data['cats_grupo_responsavel'] = array(

                'name'  => 'cats_grupo_responsavel[]',

                'id'    => 'cats_grupo_responsavel',

                'class' => 'form-control',

                'multiple' => 'multiple',

                'options' => $cats_grupo_responsavel,

                'selected' => $this->form_validation->set_value('cats_grupo_responsavel', unserialize($this->data['admin_pref_interface']->cats_grupo_responsavel))

            );

            $grupos_dev_chamado = $this->preferences_model->get_groups();
            $this->data['grupos_dev_chamado'] = array(
                'name'  => 'grupos_dev_chamado[]',
                'id'    => 'grupos_dev_chamado',
                'class' => 'multipleSelect',
                'multiple' => 'multiple',
                'options' => $grupos_dev_chamado,
                'selected' => $this->form_validation->set_value('grupos_dev_chamado', unserialize($this->data['admin_pref_interface']->grupos_dev_chamado))
            );


            $group_options = array();

            $group_select_options = array(""=>"Selecione");

            foreach ($this->preferences_model->get_groups() as $key => $g) {

                $group_options[$key] = $g;

                $group_select_options[$key] = $g;

            }

            $this->data['grupo_admin'] = array(

                'name'  => 'grupo_admin',

                'id'    => 'grupo_admin',

                'class' => 'form-control',

                'options' => $group_options,

                'selected' => ($this->form_validation->set_value('grupo_admin', $this->data['admin_pref_interface']->grupo_admin)),

            );

            $this->data['grupo_tutor'] = array(

                'name'  => 'grupo_tutor',

                'id'    => 'grupo_tutor',

                'class' => 'form-control',

                'options' => $group_select_options,

                'selected' => ($this->form_validation->set_value('grupo_tutor', $this->data['admin_pref_interface']->grupo_tutor)),

            );

            $this->data['grupo_supervisor'] = array(

                'name'  => 'grupo_supervisor',

                'id'    => 'grupo_supervisor',

                'class' => 'form-control',

                'options' => $group_select_options,

                'selected' => ($this->form_validation->set_value('grupo_supervisor', $this->data['admin_pref_interface']->grupo_supervisor)),

            );

            $this->data['grupo_aluno'] = array(

                'name'  => 'grupo_aluno',

                'id'    => 'grupo_aluno',

                'class' => 'form-control',

                'options' => $group_select_options,

                'selected' => ($this->form_validation->set_value('grupo_aluno', $this->data['admin_pref_interface']->grupo_aluno)),

            );

            $this->data['grupo_professor'] = array(

                'name'  => 'grupo_professor',

                'id'    => 'grupo_professor',

                'class' => 'form-control',

                'options' => $group_select_options,

                'selected' => ($this->form_validation->set_value('grupo_professor', $this->data['admin_pref_interface']->grupo_professor)),

            );

            $this->data['qtd_alunos'] = array(

                'name'  => 'qtd_alunos',

                'id'    => 'qtd_alunos',

                'class' => 'form-control',

                'value' => ($this->form_validation->set_value('qtd_alunos', $this->data['admin_pref_interface']->qtd_alunos)),

            );

            $this->data['qtd_creditos_ead'] = array(

                'name'  => 'qtd_creditos_ead',

                'id'    => 'qtd_creditos_ead',

                'class' => 'form-control',

                'value' => ($this->form_validation->set_value('qtd_creditos_ead', $this->data['admin_pref_interface']->qtd_creditos_ead)),

            );

            $this->data['grupo_vendedor'] = array(

                'name'  => 'grupo_vendedor',

                'id'    => 'grupo_vendedor',

                'class' => 'form-control',

                'options' => $group_select_options,

                'selected' => ($this->form_validation->set_value('grupo_vendedor', $this->data['admin_pref_interface']->grupo_vendedor)),

            );

            $this->data['horas_expiracao_cupom'] = array(

                'name'  => 'horas_expiracao_cupom',

                'id'    => 'horas_expiracao_cupom',

                'class' => 'form-control',

                'value' => ($this->form_validation->set_value('horas_expiracao_cupom', $this->data['admin_pref_interface']->horas_expiracao_cupom)),

            );
			
			$this->data['relatorio_apontamentos'] = array(
                'name'  => 'relatorio_apontamentos[]',
                'id'    => 'relatorio_apontamentos',
                'class' => 'multipleSelect',
                'multiple' => 'multiple',
                'options' => $grupos_responsavel_chamado,
                'selected' => $this->form_validation->set_value('relatorio_apontamentos', unserialize($this->data['admin_pref_interface']->relatorio_apontamentos))
            );

            $this->data['relatorio_planos_acao'] = array(
                'name'  => 'relatorio_planos_acao[]',
                'id'    => 'relatorio_planos_acao',
                'class' => 'multipleSelect',
                'multiple' => 'multiple',
                'options' => $grupos_responsavel_chamado,
                'selected' => $this->form_validation->set_value('relatorio_planos_acao', unserialize($this->data['admin_pref_interface']->relatorio_planos_acao))
            );


            if ($this->form_validation->run() == TRUE)

            {

                $data = array(

                    'user_panel'                => (bool) $this->input->post('user_panel'),

                    'empresa_sidebar'           => (bool) $this->input->post('empresa_sidebar'),

                    'sidebar_form'              => (bool) $this->input->post('sidebar_form'),

                    'messages_menu'             => (bool) $this->input->post('messages_menu'),

                    'notifications_menu'        => (bool) $this->input->post('notifications_menu'),

                    'tasks_menu'                => (bool) $this->input->post('tasks_menu'),

                    'user_menu'                 => (bool) $this->input->post('user_menu'),

                    'ctrl_sidebar'              => (bool) $this->input->post('ctrl_sidebar'),

                    'transition_page'           => (bool) $this->input->post('transition_page'),

                    'grupos_responsavel_chamado'=> serialize($this->input->post('grupos_responsavel_chamado')),

                    'cats_grupo_responsavel'    => serialize($this->input->post('cats_grupo_responsavel')),

                    'grupo_admin'               => $this->input->post('grupo_admin'),

                    'grupo_tutor'               => $this->input->post('grupo_tutor'),

                    'grupo_supervisor'          => $this->input->post('grupo_supervisor'),

                    'grupo_aluno'               => $this->input->post('grupo_aluno'),

                    'grupo_professor'           => $this->input->post('grupo_professor'),

                    'grupo_vendedor'            => $this->input->post('grupo_vendedor'),

                    'qtd_alunos'                => $this->input->post('qtd_alunos'),

                    'qtd_creditos_ead'          => $this->input->post('qtd_creditos_ead'),

                    'relatorio_tutor'           => $this->input->post('relatorio_tutor'),

                    'relatorio_supervisor'      => $this->input->post('relatorio_supervisor'),

                    'horas_expiracao_cupom'     => $this->input->post('horas_expiracao_cupom'),
                    'relatorio_apontamentos'    => serialize($this->input->post('relatorio_apontamentos')),
                    'relatorio_planos_acao'     => serialize($this->input->post('relatorio_planos_acao')),
                    'grupos_dev_chamado'        => serialize($this->input->post('grupos_dev_chamado')),
                    'cadastro_user_completo'    => (bool) $this->input->post('cadastro_user_completo')

                );


                $this->preferences_model->update_interfaces('admin_preferences', $data);


                redirect('prefs/index', 'refresh');

            }

            else

            {

                $this->data['message_admin'] = (validation_errors()) ? show_alert_error(validation_errors()) : NULL;


                /* Load Template */

                $this->template->admin_render('admin/prefs/interfaces/admin', $this->data);

            }

        }

	}


    public function dashboard()

    {

        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("prefs", "dashboard"))

        {

            redirect('auth', 'refresh');

        }

        else

        {

            if($_POST) {

                $this->preferences_model->update_interfaces('admin_preferences', array('dashboards'=>serialize($this->input->post('dash'))));

                redirect('prefs/dashboard', 'refresh');

            }


            /* Title Page */

            $this->page_title->push(lang('menu_preferences'), lang('menu_interfaces'));

            $this->data['pagetitle'] = $this->page_title->show();


            /* Breadcrumbs :: Common */

            $this->breadcrumbs->unshift(2, lang('menu_int_dashboard'), 'admin/prefs/dashboard');

            $this->data['breadcrumb'] = $this->breadcrumbs->show();


            /* Data */

            $this->data['message_admin']        = (validation_errors()) ? validation_errors() : NULL;

            $this->data['admin_pref_interface'] = $this->preferences_model->get_interface('admin_preferences');


            $path    = APPPATH.'views/admin/dashboard/';

            $files = array_diff(scandir($path), array('.', '..'));


            $dashboards_array = array();

            foreach ($files as $file) {

                if (strtolower(substr($file, strrpos($file, '.') + 1)) == 'php') {

                    if($file != "scripts.php")

                        $dashboards_array[] = str_replace(".php", "", $file);

                }

            }


            $inputs = array();

            $grupos = $this->preferences_model->get_groups();

            $dashboards_pref = unserialize($this->data['admin_pref_interface']->dashboards);

            foreach ($dashboards_array as $dash) {

                $inputs[] = array(

                    'name'  => 'dash['.$dash.'][]',

                    'id'    => $dash,

                    'class' => 'multipleSelect',

                    'multiple' => 'multiple',

                    'options' => $grupos,

                    'selected' => @$dashboards_pref[$dash]

                );

            }


            $this->data['inputs'] = $inputs;


            $this->template->admin_render('admin/prefs/interfaces/dashboard', $this->data);

        }

    }



    public function email()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("prefs", "email"))
        {
            redirect('auth', 'refresh');
        }
        else
        {
            /* Title Page */
            $this->page_title->push(lang('menu_preferences'), lang('menu_interfaces'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(2, lang('menu_int_admin'), 'admin/prefs/email');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            /* Validate form input */
            if(@$_POST["acao"] == "att" || @$_POST["acao"] == "test") {
                $this->form_validation->set_rules('protocol', 'lang:prefs_protocol', 'required');
                $this->form_validation->set_rules('host', 'lang:prefs_host', 'required');
                $this->form_validation->set_rules('port', 'lang:prefs_port', 'required|is_numeric');
                $this->form_validation->set_rules('user', 'lang:prefs_user', 'required');
                $this->form_validation->set_rules('pass', 'lang:prefs_pass', 'required');
                $this->form_validation->set_rules('from_email', 'lang:prefs_from_email', 'required');
                $this->form_validation->set_rules('from_name', 'lang:prefs_from_name', 'required');
            }

            if(@$_POST["acao"] == "att_saida" || @$_POST["acao"] == "test_saida") {
                $this->form_validation->set_rules('protocol_saida', 'lang:prefs_protocol', 'required');
                $this->form_validation->set_rules('host_saida', 'lang:prefs_host', 'required');
                $this->form_validation->set_rules('port_saida', 'lang:prefs_port', 'required|is_numeric');
                $this->form_validation->set_rules('user_saida', 'lang:prefs_user', 'required');
                $this->form_validation->set_rules('pass_saida', 'lang:prefs_pass', 'required');

                if($this->input->post('cadastrar_usu') == 1) {
                    $this->form_validation->set_rules('unidade_padrao', 'lang:prefs_unidade_padrao', 'required');
                }
            }

            /* Data */
            $this->data['config_email'] = $this->preferences_model->get_email_config();

            $this->data['protocol'] = array(
                'name'  => 'protocol',
                'id'    => 'protocol',
                'class' => 'form-control',
                'placeholder' => 'ex: smtp',
                'value' => ($this->form_validation->set_value('protocol', $this->data['config_email']->protocol))
            );

            $this->data['host'] = array(
                'name'  => 'host',
                'id'    => 'host',
                'class' => 'form-control',
                'placeholder' => 'ex: smtp.xkeysti.com.br',
                'value' => ($this->form_validation->set_value('host', $this->data['config_email']->host))
            );

            $this->data['port'] = array(
                'name'  => 'port',
                'id'    => 'port',
                'class' => 'form-control',
                'placeholder' => 'ex: 587',
                'value' => ($this->form_validation->set_value('port', $this->data['config_email']->port))
            );

            $this->data['user'] = array(
                'name'  => 'user',
                'id'    => 'user',
                'class' => 'form-control',
                'placeholder' => 'ex: contato=xkeysti.com.br',
                'value' => ($this->form_validation->set_value('user', $this->data['config_email']->user))
            );

            $this->data['pass'] = array(
                'name'  => 'pass',
                'id'    => 'pass',
                'type'  => 'password',
                'class' => 'form-control',
                'value' => ($this->form_validation->set_value('pass', $this->data['config_email']->pass))
            );

            $this->data['from_email'] = array(
                'name'  => 'from_email',
                'id'    => 'from_email',
                'class' => 'form-control',
                'value' => ($this->form_validation->set_value('from_email', $this->data['config_email']->from_email))
            );

            $this->data['from_name'] = array(
                'name'  => 'from_name',
                'id'    => 'from_name',
                'class' => 'form-control',
                'value' => ($this->form_validation->set_value('from_name', $this->data['config_email']->from_name))
            );

            $this->data['protocol_saida'] = array(
                'name'  => 'protocol_saida',
                'id'    => 'protocol_saida',
                'class' => 'form-control',
                'placeholder' => 'ex: novalidate-cert',
                'value' => ($this->form_validation->set_value('protocol_saida', $this->data['config_email']->protocol_saida))
            );

            $this->data['host_saida'] = array(
                'name'  => 'host_saida',
                'id'    => 'host_saida',
                'class' => 'form-control',
                'placeholder' => 'ex: mbox.xkeysti.com.br',
                'value' => ($this->form_validation->set_value('host_saida', $this->data['config_email']->host_saida))
            );

            $this->data['port_saida'] = array(
                'name'  => 'port_saida',
                'id'    => 'port_saida',
                'class' => 'form-control',
                'placeholder' => 'ex: 143',
                'value' => ($this->form_validation->set_value('port_saida', $this->data['config_email']->port_saida))
            );

            $this->data['user_saida'] = array(
                'name'  => 'user_saida',
                'id'    => 'user_saida',
                'class' => 'form-control',
                'placeholder' => 'ex: ellan=xkeysti.com.br',
                'value' => ($this->form_validation->set_value('user_saida', $this->data['config_email']->user_saida))
            );

            $this->data['pass_saida'] = array(
                'name'  => 'pass_saida',
                'id'    => 'pass_saida',
                'type'  => 'password',
                'class' => 'form-control',
                'value' => ($this->form_validation->set_value('pass_saida', $this->data['config_email']->pass_saida))
            );

            $this->data['cadastrar_usu'] = array(
                'name'  => 'cadastrar_usu',
                'id'    => 'cadastrar_usu',
                'class' => 'checkbox',
                'value' => '1',
                'checked' => ($this->input->post('cadastrar_usu')) ? $this->input->post('cadastrar_usu') : $this->data['config_email']->cadastrar_usu,
            );

            $this->data['unidade_padrao'] = array(
                'name'  => 'unidade_padrao',
                'id'    => 'unidade_padrao',
                'class' => 'form-control',
                'options' => $this->preferences_model->get_unidades(),
                'selected' => ($this->form_validation->set_value('unidade_padrao', $this->data['config_email']->unidade_padrao)),
            );

            if (
                ($this->form_validation->run() == TRUE && (@$_POST["acao"] == "att" || @$_POST["acao"] == "test")) ||
                ($this->form_validation->run() == TRUE && (@$_POST["acao"] == "att_saida" || @$_POST["acao"] == "test_saida")) ||
                ($_POST && (@$_POST["acao"] == "remover" || $_POST["acao"] == "remover_saida")) 
            ) {
                if(@$_POST["acao"] == "remover") {
                    $data = array(
                        'test'          => 0
                    );
                    $this->preferences_model->update_config_email($data);
                }
                elseif(@$_POST["acao"] == "remover_saida") {
                    $data = array(
                        'test_saida'    => 0
                    );
                }
                elseif(@$_POST["acao"] == "test" || @$_POST["acao"] == "att") {
                    $data = array(
                        'protocol'      => $this->input->post('protocol'),
                        'host'          => $this->input->post('host'),
                        'port'          => $this->input->post('port'),
                        'user'          => $this->input->post('user'),
                        'pass'          => $this->input->post('pass'),
                        'from_email'    => $this->input->post('from_email'),
                        'from_name'     => $this->input->post('from_name')
                    );
                    
                    if($this->_teste_envio(array('protocol' => $this->input->post('protocol'), 'smtp_host' => $this->input->post('host'), 'smtp_port' => $this->input->post('port'), 'smtp_user' => $this->input->post('user'), 'smtp_pass' => $this->input->post('pass'), 'mailtype'  => 'html', 'charset'   => 'iso-8859-1'), $this->input->post('from_email'), $this->input->post('from_name'))) {
                        $data['test'] = 1;
                        $this->session->set_flashdata('message', show_alert_success("Configuração de entrada salva."));
                    } else {
                        $data['test'] = 0;
                        $this->session->set_flashdata('message', show_alert_error($this->email->print_debugger(array('body'))));
                    }

                    $this->preferences_model->update_config_email($data);
                }
                elseif(@$_POST["acao"] == "test_saida" || @$_POST["acao"] == "att_saida") {
                    $data = array(
                        'protocol_saida'=> $this->input->post('protocol_saida'),
                        'host_saida'    => $this->input->post('host_saida'),
                        'port_saida'    => $this->input->post('port_saida'),
                        'user_saida'    => $this->input->post('user_saida'),
                        'pass_saida'    => $this->input->post('pass_saida'),
                        'cadastrar_usu' => $this->input->post('cadastrar_usu'),
                        'unidade_padrao'=> $this->input->post('unidade_padrao')
                    );

                    if($this->_teste_saida(array('protocol' => $this->input->post('protocol_saida'), 'host' => $this->input->post('host_saida'), 'port' => $this->input->post('port_saida'), 'user' => $this->input->post('user_saida'), 'pass' => $this->input->post('pass_saida')))) {
                        $this->session->set_flashdata('message', show_alert_success("Configuração de saída salva."));
                        $data['test_saida'] = 1;
                    } else {
                        $data['test_saida'] = 0;
                        $this->session->set_flashdata('message', show_alert_error(imap_last_error()));
                    }
                    $this->preferences_model->update_config_email($data);
                }   

                redirect('prefs/email', 'refresh');
            }
            else
            {
                $this->data['message_admin'] = (validation_errors()) ? show_alert_error(validation_errors()) : NULL;

                /* Load Template */
                $this->template->admin_render('admin/prefs/interfaces/email', $this->data);
            }
        }
    }


    private function _teste_envio($email_config, $from_email, $from_name) {

        /*$this->load->library(array('email'));

        $this->email->initialize($email_config);

        $this->email->clear();

        $this->email->from($from_email, $from_name);

        $this->email->to($from_email);

        $this->email->subject("Teste de envio de e-mail");

        $this->email->message("E-mail enviado com sucesso.");


        if ($this->email->send() == TRUE) {

            return true;

        } else {

            return false;

        }*/

        $this->load->library("phpmailer_library");

        $mail = $this->phpmailer_library->load();
        $mail->isSMTP();
        $mail->Host     = $email_config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $email_config['smtp_user'];
        $mail->Password = $email_config['smtp_pass'];
        $mail->SMTPSecure = 'tsl';
        $mail->Port     = $email_config['smtp_port'];

        $mail->setFrom($from_email, utf8_decode($from_name));

        $mail->addAddress($from_email);
        $mail->Subject = "Teste de envio de e-mail";
        $mail->isHTML(true);

        $mail->Body = "E-mail enviado com sucesso.";

        // Send email
        if(!$mail->send()){
            return false;
        }else{
            return true;
        }

    }


    private function _teste_saida($data) {

        if($mbox = @imap_open("{".$data["host"].":".$data["port"]."/".$data["protocol"]."}", $data["user"], $data["pass"])) {

            imap_close($mbox);

            return true;

        } else {

            return false;

        }

    }


	/*public function reset_interfaces_admin()

	{

		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("prefs", "reset_interfaces_admin"))

		{

			redirect('auth', 'refresh');

		}

        else

        {

            $data = array(

                'user_panel'         => '0',

                'empresa_sidebar'    => '0',

                'sidebar_form'       => '0',

                'messages_menu'      => '0',

                'notifications_menu' => '0',

                'tasks_menu'         => '0',

                'user_menu'          => '1',

                'ctrl_sidebar'       => '0',

                'transition_page'    => '0',

                'qtd_alunos'         => ''

            );


            $this->preferences_model->update_interfaces('admin_preferences', $data);


            redirect('', 'refresh');

        }

	}*/

}
