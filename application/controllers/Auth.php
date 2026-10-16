<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

        $this->lang->load('auth');
        $this->load->helper('functions_helper');
    }


    function index()
    {
        if (! $this->ion_auth->logged_in()) {
            redirect('auth/login');
        } else {
            redirect('/');
        }
    }


    function login()
    {
        if (! $this->ion_auth->logged_in()) {
            /* Load */
            $this->load->config('admin/dp_config');
            $this->load->config('common/dp_config');

            /* Valid form */
            $this->form_validation->set_rules('identity', 'lang:users_identity', 'required');
            $this->form_validation->set_rules('password', 'lang:users_password', 'required');

            /* Data */
            $this->data['title']               = $this->config->item('title');
            $this->data['title_lg']            = $this->config->item('title_lg');
            $this->data['auth_social_network'] = $this->config->item('auth_social_network');
            $this->data['forgot_password']     = $this->config->item('forgot_password');
            $this->data['new_membership']      = $this->config->item('new_membership');

            if ($this->form_validation->run() == TRUE) {
                $remember = (bool) $this->input->post('remember');

                if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                    if (! $this->ion_auth->is_admin()) {
                        //$this->session->set_flashdata('message', show_alert_success($this->ion_auth->messages()));
                        redirect('/');
                    } else {
                        /* Data */
                        $this->data['message'] = (validation_errors()) ? show_alert_error(validation_errors()) : show_alert_error($this->session->flashdata('message'));


                        //verificar se a senha ira expirar em 10 dias
                        $query = $this->db->select('u.password_updated')
                            ->where('u.email', $this->input->post('identity'))
                            ->limit(1)
                            ->get('users u');
                        $user = $query->row();

                        $data_inicial = $user->password_updated;
                        $data_final = date('Y-m-d H:i:s');

                        // Calcula a diferença em segundos entre as datas
                        $diferenca = strtotime($data_final) - strtotime($data_inicial);

                        //Calcula a diferença em dias
                        $dias = floor($diferenca / (60 * 60 * 24));

                        if ($dias > 80)
                            $this->session->set_flashdata('message', show_alert_info('Sua senha irá expirar em breve, para alterar acesse seu perfil ou vá para a página de login e clique em "Esqueci minha senha".'));


                        /* Load Template */
                        //$this->template->admin_render('admin/dashboard/index', $this->data);
                        redirect('dashboard/index');
                    }
                } else {
                    $this->session->set_flashdata('message', show_alert_error($this->ion_auth->errors()));
                    redirect('auth/login');
                }
            } else {
                if ($_POST)
                    $this->data['message'] = (validation_errors()) ? show_alert_error(validation_errors()) : show_alert_error($this->session->flashdata('message'));
                else
                    $this->data['message'] = ($this->session->flashdata('message')) ? $this->session->flashdata('message') : '';

                $this->data['identity'] = array(
                    'name'        => 'identity',
                    'id'          => 'identity',
                    'type'        => 'email',
                    'value'       => $this->form_validation->set_value('identity'),
                    'class'       => 'form-control',
                    'autofocus'   => 'autofocus',
                    'placeholder' => lang('auth_your_email')
                );
                $this->data['password'] = array(
                    'name'        => 'password',
                    'id'          => 'password',
                    'type'        => 'password',
                    'class'       => 'form-control',
                    'placeholder' => lang('auth_your_password')
                );

                /* Load Template */
                $this->template->auth_render('auth/login', $this->data);
            }
        } else {
            $this->session->set_flashdata('message', $this->session->flashdata('message'));
            redirect('/');
        }
    }


    function logout($src = NULL)
    {
        // Executa o logout na biblioteca Ion Auth
        $logout = $this->ion_auth->logout();

        // Salva a mensagem de logout na sessao
        $this->session->set_flashdata('message', $this->ion_auth->messages());

        // Redireciona o usuario para a pagina correspondente
        if ($src == 'admin') {
            redirect('auth/login');
        } else {
            redirect('/');
        }
    }

    // activate the user
    function activate($id = "", $code = false)
    {
        if ($_POST) {
            $id = $this->input->post('user_id');
            $code = $this->input->post('code');
        }

        if ($code !== false && $this->ion_auth->code_is_valid($id, $code)) {
            $this->load->config('admin/dp_config');
            /* Data */
            $this->data['title']               = $this->config->item('title');
            $this->data['title_lg']            = $this->config->item('title_lg');

            /* Valid form */
            $this->form_validation->set_rules('first_name', 'lang:users_firstname', 'required');
            $this->form_validation->set_rules('last_name', 'lang:users_lastname', 'required');
            $this->form_validation->set_rules('password', 'lang:auth_your_password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]|is_password_strong');
            $this->form_validation->set_rules('password_confirm', 'lang:confirm_your_password', 'required');

            if ($this->form_validation->run() == TRUE) {
                $username = strtolower($this->input->post('first_name')) . ' ' . strtolower($this->input->post('last_name'));
                $password = $this->input->post('password');

                $data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name'  => $this->input->post('last_name'),
                    'username'   => $username,
                    'password'   => $password
                );

                if ($this->ion_auth->update($id, $data)) {
                    $activation = $this->ion_auth->activate($id, $code);
                    if ($activation) {
                        // redirect them to the auth page
                        $this->session->set_flashdata('message', show_alert_success($this->ion_auth->messages()));
                        redirect("auth", 'refresh');
                    } else {
                        // redirect them to the forgot password page
                        $this->session->set_flashdata('message', show_alert_error($this->ion_auth->errors()));
                        redirect("auth/forgot_password", 'refresh');
                    }
                } else {
                    $this->session->set_flashdata('message', show_alert_error($this->ion_auth->errors()));
                }
            } else {
                if ($_POST)
                    $this->data['message'] = (validation_errors()) ? show_alert_error(validation_errors()) : show_alert_error($this->session->flashdata('message'));

                $this->data['hidden'] = array(
                    'user_id' => $id,
                    'code'  => $code
                );
                $this->data['first_name'] = array(
                    'name'  => 'first_name',
                    'id'    => 'first_name',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'placeholder' => lang('users_firstname'),
                    'value' => $this->form_validation->set_value('first_name'),
                );
                $this->data['last_name'] = array(
                    'name'  => 'last_name',
                    'id'    => 'last_name',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'placeholder' => lang('users_lastname'),
                    'value' => $this->form_validation->set_value('last_name'),
                );
                $this->data['password'] = array(
                    'name'  => 'password',
                    'id'    => 'password',
                    'type'  => 'password',
                    'class' => 'form-control',
                    'placeholder' => lang('auth_your_password'),
                    'value' => $this->form_validation->set_value('password'),
                );
                $this->data['password_confirm'] = array(
                    'name'  => 'password_confirm',
                    'id'    => 'password_confirm',
                    'type'  => 'password',
                    'class' => 'form-control',
                    'placeholder' => lang('confirm_your_password'),
                    'value' => $this->form_validation->set_value('password_confirm'),
                );

                /* Load Template */
                $this->template->auth_render('auth/activate', $this->data);
            }
        } else {
            $this->session->set_flashdata('message', show_alert_error($this->ion_auth->errors()));
            redirect("auth/login", 'refresh');
        }
        /*else if ($this->ion_auth->is_admin())
        {
            $activation = $this->ion_auth->activate($id);
        }*/
    }

    // deactivate the user
    function deactivate($id = NULL)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            // redirect them to the home page because they must be an administrator to view this
            return show_error('You must be an administrator to view this page.');
        }

        $id = (int) $id;

        $this->load->library('form_validation');
        $this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'required');
        $this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'required|alpha_numeric');

        if ($this->form_validation->run() == FALSE) {
            // insert csrf check
            $this->data['csrf'] = $this->_get_csrf_nonce();
            $this->data['user'] = $this->ion_auth->user($id)->row();

            $this->_render_page('auth/deactivate_user', $this->data);
        } else {
            // do we really want to deactivate?
            if ($this->input->post('confirm') == 'yes') {
                // do we have a valid request?
                if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id')) {
                    show_error($this->lang->line('error_csrf'));
                }

                // do we have the right userlevel?
                if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
                    $this->ion_auth->deactivate($id);
                }
            }

            // redirect them back to the auth page
            redirect('auth', 'refresh');
        }
    }

    function change_empresa()
    {
        if ($_POST && $this->ion_auth->valida_empresa_usuario($this->input->post('empresa'))) {
            $this->session->unset_userdata('notifications_ead');
            $this->session->unset_userdata('notifications_ead_count');
            $this->ion_auth->update_session($this->input->post('empresa'));
        }
        redirect('/');
    }

    function forgot_password()
    {
        /* Load */
        $this->load->library('phpmailer_library');
        $this->load->config('admin/dp_config');
        $this->load->config('common/dp_config');
        $this->data['title']               = $this->config->item('title');
        $this->data['title_lg']            = $this->config->item('title_lg');

        // setting validation rules by checking wheather identity is username or email
        if ($this->config->item('identity', 'ion_auth') != 'email') {
            $this->form_validation->set_rules('identity', $this->lang->line('forgot_password_identity_label'), 'required');
        } else {
            $this->form_validation->set_rules('email', $this->lang->line('forgot_password_validation_email_label'), 'required|valid_email');
        }


        if ($this->form_validation->run() == false) {
            // setup the input
            $this->data['email'] = array(
                'name'        => 'email',
                'id'          => 'email',
                'type'        => 'email',
                'value'       => $this->form_validation->set_value('email'),
                'class'       => 'form-control',
                'placeholder' => lang('auth_your_email')
            );

            if ($this->config->item('identity', 'ion_auth') != 'email') {
                $this->data['identity_label'] = $this->lang->line('forgot_password_identity_label');
            } else {
                $this->data['identity_label'] = $this->lang->line('forgot_password_email_identity_label');
            }

            // set any errors and display the form
            $this->data['message'] = (validation_errors()) ? show_alert_error(validation_errors()) : $this->session->flashdata('message');
            $this->template->auth_render('auth/forgot_password', $this->data);
        } else {
            $email = $this->input->post('email');

            $identity_column = $this->config->item('identity', 'ion_auth');
            $identity = $this->ion_auth->where($identity_column, $email)->users()->row();

            if (empty($identity)) {
                if ($this->config->item('identity', 'ion_auth') != 'email') {
                    $this->ion_auth->set_error('forgot_password_identity_not_found');
                } else {
                    $this->ion_auth->set_error('forgot_password_email_not_found');
                }

                $this->session->set_flashdata('message', show_alert_error($this->ion_auth->errors()));
                redirect("auth/forgot_password", 'refresh');
            }

            // run the forgotten password method to email an activation code to the user
            $this->ion_auth->forgotten_password($identity->{$this->config->item('identity', 'ion_auth')});

            // SEMPRE MOSTRAR MENSAGEM DE SUCESSO, POIS O EMAIL FOI ENVIADO
            $this->session->set_flashdata('message', show_alert_success('Redefinição de senha enviado por e-mail. Verifique sua caixa de entrada.'));
            redirect("auth/login", 'refresh');
        }
    }

    public function reset_password($code = NULL)
    {
        if (!$code) {
            show_404();
        }

        $user = $this->ion_auth->forgotten_password_check($code);

        if ($user) {
            /* Load */
            $this->load->config('admin/dp_config');
            $this->load->config('common/dp_config');
            $this->data['title']               = $this->config->item('title');
            $this->data['title_lg']            = $this->config->item('title_lg');

            // if the code is valid then display the password reset form

            $this->form_validation->set_rules('new', 'Nova Senha', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]|is_password_strong');
            $this->form_validation->set_rules('new_confirm', $this->lang->line('reset_password_validation_new_password_confirm_label'), 'required');

            if ($this->form_validation->run() == false) {
                // display the form

                // set the flash data error message if there is one
                $this->data['message'] = $this->session->flashdata('message');
                if ($_POST)
                    $this->data['message'] = (validation_errors()) ? show_alert_error(validation_errors()) : show_alert_error($this->session->flashdata('message'));

                $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
                $this->data['new_password'] = array(
                    'name' => 'new',
                    'id'   => 'password',
                    'type' => 'password',
                    'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
                    'class'       => 'form-control',
                    'placeholder' => lang('reset_password_new_password')
                );
                $this->data['new_password_confirm'] = array(
                    'name'    => 'new_confirm',
                    'id'      => 'new_confirm',
                    'type'    => 'password',
                    'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
                    'class'       => 'form-control',
                    'placeholder' => lang('reset_password_new_password_confirm')
                );
                $this->data['user_id'] = array(
                    'name'  => 'user_id',
                    'id'    => 'user_id',
                    'type'  => 'hidden',
                    'value' => $user->id,
                );
                $this->data['csrf'] = $this->_get_csrf_nonce();
                $this->data['code'] = $code;

                // render
                //$this->_render_page('auth/reset_password', $this->data);
                $this->template->auth_render('auth/reset_password', $this->data);
            } else {
                // do we have a valid request?
                if ($this->_valid_csrf_nonce() === FALSE || $user->id != $this->input->post('user_id')) {

                    // something fishy might be up
                    $this->ion_auth->clear_forgotten_password_code($code);

                    show_error($this->lang->line('error_csrf'));
                } else {
                    if ($this->ion_auth->check_login($user->email, $this->input->post('new'))) {
                        $this->session->set_flashdata('message', show_alert_error("Sua nova senha deverá ser diferente da antiga."));
                        redirect('auth/reset_password/' . $code, 'refresh');
                    }

                    // finally change the password
                    $identity = $user->{$this->config->item('identity', 'ion_auth')};

                    $change = $this->ion_auth->reset_password($identity, $this->input->post('new'));

                    if ($change) {
                        // if the password was successfully changed
                        $this->session->set_flashdata('message', show_alert_success($this->ion_auth->messages()));
                        redirect("auth/login", 'refresh');
                    } else {
                        $this->session->set_flashdata('message', show_alert_error($this->ion_auth->errors()));
                        redirect('auth/reset_password/' . $code, 'refresh');
                    }
                }
            }
        } else {
            // if the code is invalid then send them back to the forgot password page
            $this->session->set_flashdata('message', $this->ion_auth->errors());
            redirect("auth/forgot_password", 'refresh');
        }
    }

    function _get_csrf_nonce()
    {
        $this->load->helper('string');
        $key   = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);

        return array($key => $value);
    }

    function _valid_csrf_nonce()
    {
        if (
            $this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
            $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue')
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function avaliacao_chamado($user_id = "", $hash = "")
    {
        /* Load */
        $this->load->config('admin/dp_config');
        $this->load->config('common/dp_config');
        $this->data['title'] = $this->config->item('title');

        $this->load->model('chamados_model');

        if ($user_id == "" || $hash == "") {
            redirect('auth/login', 'refresh');
        } else {
            $chamado = $this->chamados_model->get(array(
                'select' => 'c.id, c.categoria_id, c.catalogoservico_id, c.modulo_id, c.resposta, c.referencia_id, c.unidade_id, c.produto_id, c.modulo_id, c.protocolo, c.titulo, c.descricao, c.horas, c.reincidencia, c.created, c.data_finalizacao, c.data_inicio, c.data_prazo, c.modified, c.status_id, c.prioridade_id, c.dono_id, c.responsavel_id, u.first_name, u.last_name, cs.descricao as catalogo_desc, un.unidade as cliente, cst.descricao as status, cp.descricao as prioridade, un.empresa_id, p.nome as produto_desc, m.nome as modulo_desc, mc.nome as mcategoria_desc, ud.first_name as first_name_dono, ud.last_name as last_name_dono, c.hash',
                'join' => array(array('produtos p', 'p.id = c.produto_id', 'left'), array('modulos m', 'm.id = c.modulo_id', 'left'), array('modulos_categoria mc', 'mc.id = c.categoria_id', 'left'), array('users u', 'u.id = c.responsavel_id', 'left'), array('users ud', 'ud.id = c.dono_id', 'left'), array('catalogo_servico cs', 'cs.id = c.catalogoservico_id', 'left'), array('unidades un', 'un.id = c.unidade_id', 'inner'), array('chamado_status cst', 'cst.id = c.status_id', 'inner'), array('chamado_prioridade cp', 'cp.id = c.prioridade_id', 'inner')),
                'where' => 'c.hash = "' . $hash . '" AND c.dono_id = "' . $user_id . '"',
                'order_by' => array('c.id', 'desc')
            ));

            $this->data['chamado']  = $chamado;

            if ($chamado->num_rows() > 0) {
                $data_chamado = new DateTime($chamado->row()->data_finalizacao);
                $data_atual = new DateTime(date("Y-m-d H:i:s"));

                $dateInterval = $data_chamado->diff($data_atual);

                if ($dateInterval->days > 30) {
                    $this->session->set_flashdata('message', show_alert_error('Avaliação do chamado expirada.'));
                    redirect('auth/login', 'refresh');
                }

                if ($_POST) {
                    // exit("<pre>".print_r($_POST,true)."</pre>");

                    $avaliacoes = $this->chamados_model->get_avaliacoes()->result_array();
                    foreach ($avaliacoes as $aval) {
                        if (array_key_exists("star-" . $aval['id'], $_POST)) {
                            $data = array(
                                'chamado_id' => $chamado->row()->id,
                                'pergunta_id' => $aval['id'],
                                'resposta' => $_POST["star-" . $aval['id']],
                                'user_id' => $user_id
                            );
                        } else {
                            $data = array(
                                'chamado_id' => $chamado->row()->id,
                                'pergunta_id' => $aval['id'],
                                'resposta' => 0,
                                'user_id' => $user_id
                            );
                        }
                        $this->chamados_model->salvar_avaliacao_chamado($data);
                    }

                    $this->chamados_model->update($chamado->row()->id, array("hash" => "", "status_id" => "5"));

                    /* Load Template */
                    $this->template->auth_render('auth/avaliacao_agradecimento', $this->data);
                } else {
                    $this->data['message'] = ($this->session->flashdata('message')) ? $this->session->flashdata('message') : '';

                    $this->data['has_avaliacao'] = false;
                    $this->data['has_avaliacao'] = true;
                    $avaliacoes = $this->chamados_model->get_avaliacoes()->result_array();
                    $i = 0;
                    foreach ($avaliacoes as $aval) {
                        $this->data['avaliacoes'][$i]['id'] = $aval['id'];
                        $this->data['avaliacoes'][$i]['descricao'] = $aval['descricao'];
                        $this->data['avaliacoes'][$i]['resposta'] = @$this->chamados_model->get_resposta(array('pergunta' => $aval['id'], 'chamado' => $chamado->row()->id))->row()->resposta;
                        $i++;
                    }

                    /* Load Template */
                    $this->template->auth_render('auth/avaliacao_chamado', $this->data);
                }
            } else {
                $this->session->set_flashdata('message', show_alert_error('Chamado não existente ou já avaliado.'));
                redirect('auth/login', 'refresh');
            }
        }
    }

    public function ver_boleto($ct_receb_id = NULL)
    {
        if (!$ct_receb_id) {
            show_404();
        }

        $ct_receb = $this->ion_auth->get_ct_receb($ct_receb_id);

        $this->data['password'] = array(
            'name' => 'password',
            'id'   => 'password',
            'type' => 'password',
            'pattern' => '^.{' . $this->config->item('min_password_length', 'ion_auth') . '}.*$',
            'class'       => 'form-control',
            'placeholder' => lang('password')
        );

        if ($ct_receb->num_rows() == 1 && $ct_receb->row()->VENCIMENTO >= date("Y-m-d")) {
            /* Load */
            $this->load->config('admin/dp_config');
            $this->load->config('common/dp_config');
            $this->data['title']               = $this->config->item('title');
            $this->data['title_lg']            = $this->config->item('title_lg');
            $this->data['ct_receb_id']         = $ct_receb_id;

            $this->form_validation->set_rules('password', 'lang:password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');

            if ($this->form_validation->run() == false) {
                if ($_POST)
                    $this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->ion_auth->errors() ? show_alert_error($this->ion_auth->errors()) : show_alert_error($this->session->flashdata('message'))));

                // render
                $this->template->auth_render('auth/ver_boleto', $this->data);
            } else {
                if ($this->ion_auth->check_login($ct_receb->row()->email, $this->input->post('password'))) {
                    // header("Content-type: application/pdf");
                    // header("Content-Disposition: inline; filename=filename.pdf");
                    // @readfile(FCPATH.'upload/boletos/'.$ct_receb_id.'.pdf');
                    redirect('https://docs.google.com/viewerng/viewer?url=' . base_url('upload/boletos/' . $ct_receb_id . '.pdf'), 'refresh');
                }
                $this->data['message'] = show_alert_error("Senha incorreta!");
                $this->template->auth_render('auth/ver_boleto', $this->data);
            }
        } else {
            $this->data['message'] = show_alert_error("Conta a receber não encontrada ou vencida!");
            $this->template->auth_render('auth/ver_boleto', $this->data);
        }
    }
}
