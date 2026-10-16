<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_alunos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/app_alunos');
        $this->load->model('app_alunos_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_app_alunos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_app_alunos'), 'app_alunos');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_alunos", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            // $app_alunos = $this->app_alunos_model->get();
            // foreach ($app_alunos as $key => $aluno) {
            //     $unidades = $this->app_alunos_model->get_unidades($aluno->user_id);
            //     $unidades_string = "";
            //     if($unidades->num_rows() > 0) {
            //         $i=0;
            //         foreach ($unidades->result() as $value) {
            //             if($i > 0 && $i < 3)
            //                 $unidades_string .= ", ";
            //             if($i == 3) {
            //                 $unidades_string .= "...";
            //                 break;
            //             }
            //             $unidades_string .= $value->unidade;
            //             $i++;
            //         }
            //     }
            //     $app_alunos[$key]->unidades = $unidades_string;
            // }
            // $this->data['app_alunos'] = $app_alunos;
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/app/alunos/index', $this->data);
        }
    }

    public function create()
    {

    }

    public function edit()
    {

    }

    public function delete($id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_alunos", "delete") OR ! $id OR $id == null)
        {
            redirect('auth/login', 'refresh');
        }
        if(!$this->ion_auth->can_delete_user($id)) {
            $this->session->set_flashdata('message', show_alert_error("Usuário não está permitido ser deletado."));
            redirect('users', 'refresh');
        }
        if($this->ion_auth->delete_user($id)) {
            $this->ion_auth->create_auditoria(array("user_id" => $this->session->userdata('user_id'), "empresa_id" => $this->session->userdata('empresa_id'), "controlador" => "users", "metodo" => "delete", "obs" => "Usuário ".$id." deletado."));
            $this->session->set_flashdata('message', show_alert_success("Usuário deletado com sucesso."));
        }
        redirect('app_alunos', 'refresh');
    }

    public function dispositivos($id = null)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_alunos", "dispositivos") OR ! $id OR $id == null)
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->breadcrumbs->unshift(2, lang('menu_app_alunos_dispositivos'), 'app_alunos/dispositivos');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            if ($_POST) {  
                $mensagens = "";
                $mensagens_success = "";
                $mensagens_error = "";

                if($this->input->post('limpar_respostas')) {
                    if(count($this->input->post('livro')) > 0) {
                        $livros_str = "";
                        $unidades_livros_str = "";
                        foreach ($this->input->post('livro') as $livro) {
                            
                            if($livros_str != "")
                                $livros_str = $livros_str.",";

                            $livros_str = $livros_str.$livro;

                            $unidades_livros = $this->app_alunos_model->get_unidade_livro($livro);
                            if($unidades_livros->num_rows() > 0) {
                                $unidades_livros_str = "";
                                foreach ($unidades_livros->result() as $unid_livr) {
                                    
                                    if($unidades_livros_str != "")
                                        $unidades_livros_str = $unidades_livros_str.",";

                                    $unidades_livros_str = $unidades_livros_str.$unid_livr->id;
                                }
                            }
                        }

                        if($livros_str != "" && $unidades_livros_str != "") {

                            $exercicios = $this->app_alunos_model->get_exercicios($livros_str);
                            if($exercicios->num_rows() > 0) {
                                $exercicios_str = "";
                                foreach ($exercicios->result() as $exerc) {
                                    
                                    if($exercicios_str != "")
                                        $exercicios_str = $exercicios_str.",";

                                    $exercicios_str = $exercicios_str.$exerc->id;
                                }

                                $perguntas = $this->app_alunos_model->get_perguntas($exercicios_str);
                                if($perguntas->num_rows() > 0) {
                                    $perguntas_str = "";
                                    foreach ($perguntas->result() as $perg) {
                                        
                                        if($perguntas_str != "")
                                            $perguntas_str = $perguntas_str.",";

                                        $perguntas_str = $perguntas_str.$perg->id;
                                    }
                                }
                            }

                            if($this->app_alunos_model->remover_respostas($this->input->post('aluno_id'), $perguntas_str, $livros_str)) {
                                $mensagens_success = $this->app_alunos_model->messages();

                                if($this->app_alunos_model->remover_correcoes($this->input->post('aluno_id'), $unidades_livros_str)) {
                                    $mensagens_success = $this->app_alunos_model->messages();
                                } else {
                                    $mensagens_error = $this->app_alunos_model->messages();
                                }
                            } else {
                                $mensagens_error = $this->app_alunos_model->messages();
                            }
                        }
                    }
                }

                if($this->input->post('limpar_dispositivos')) {
                    if(count($this->input->post('dispositivos_remove')) > 0) {
                        foreach ($this->input->post('dispositivos_remove') as $disp) {
                            if($this->app_alunos_model->remover_dispositivo($disp)) {
                                $mensagens_success .= $this->app_alunos_model->messages();
                            } else {
                                $mensagens_error .= $this->app_alunos_model->messages();
                            }
                        }
                    }
                }

                if($mensagens_success != "")
                    $mensagens .= show_alert_success($mensagens_success);
                if($mensagens_error != "")
                    $mensagens .= show_alert_error($mensagens_error);
                $this->session->set_flashdata('message', $mensagens);
                
                redirect('app_alunos', 'refresh');
            } else {
                $this->data['aluno'] = $this->app_alunos_model->get_aluno($id)->row();
                $this->data['livros'] = $this->app_alunos_model->get_livros($id)->result();
                $this->data['dispositivos'] = $this->app_alunos_model->get_dispositivos($id)->result();
                $this->data['historico'] = $this->app_alunos_model->get_historico_dispositivos($id)->result();

                /* Load Template */
                $this->template->admin_render('admin/app/alunos/dispositivos', $this->data);
            }
        }
    }

    public function enviar_email($id) {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_alunos", "enviar_email"))
        {
            redirect('auth', 'refresh');
        }

        $user = $this->ion_auth_model->user($id)->row();

        if(@$user->id) {
            if($user->active == 1 || $user->activation_code == "") {
                $this->session->set_flashdata('message', show_alert_info("Este usuário já se encontra ativo."));
                redirect('app_alunos', 'refresh');
            }

            /*$this->load->library(array('email'));

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

            if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
            {
                $this->email->initialize($email_config);
            }

            $data = array(
                'identity'   => $user->email,
                'id'         => $user->id,
                'email'      => $user->email,
                'activation' => $user->activation_code
            );

            if($this->ion_auth_model->is_aluno($id)) {
                $padrao_email = $this->ion_auth->get_email_padrao(2)->row();
                $padrao_email_msg = $padrao_email->mensagem;
                $padrao_email_msg = str_replace("{email}", $data["email"], $padrao_email_msg);
                $padrao_email_msg = str_replace("{link_ativacao}", base_url("auth/activate/".$data["id"]."/".$data["activation"]), $padrao_email_msg);
            }
            else {
                $padrao_email = $this->ion_auth->get_email_padrao(1)->row();
                $padrao_email_msg = $padrao_email->mensagem;
                $padrao_email_msg = str_replace("{email}", $data["email"], $padrao_email_msg);
                $padrao_email_msg = str_replace("{link}", $data["activation"], $padrao_email_msg);
            }

            $this->email->clear();
            $this->email->from($from_email, utf8_decode($from_name));
            $this->email->to($user->email);
            $this->email->subject(utf8_decode($padrao_email->assunto));
            $this->email->message(utf8_decode($padrao_email_msg));

            if ($this->email->send() == TRUE)
            {
                $this->session->set_flashdata('message', show_alert_success("E-mail reenviado com sucesso."));
                redirect('app_alunos', 'refresh');
            } else {
                $this->session->set_flashdata('message', show_alert_error("Houve um problema ao enviar o e-mail, tente novamente ou entre em contato com o administrador."));
                redirect('app_alunos', 'refresh');
            }*/

            $this->load->library("phpmailer_library");

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

            $data = array(
                'identity'   => $user->email,
                'id'         => $user->id,
                'email'      => $user->email,
                'activation' => $user->activation_code
            );

            if($this->ion_auth_model->is_aluno($id)) {
                $padrao_email = $this->ion_auth->get_email_padrao(2)->row();
                $padrao_email_msg = $padrao_email->mensagem;
                $padrao_email_msg = str_replace("{email}", $data["email"], $padrao_email_msg);
                $padrao_email_msg = str_replace("{link_ativacao}", base_url("auth/activate/".$data["id"]."/".$data["activation"]), $padrao_email_msg);
            }
            else {
                $padrao_email = $this->ion_auth->get_email_padrao(1)->row();
                $padrao_email_msg = $padrao_email->mensagem;
                $padrao_email_msg = str_replace("{email}", $data["email"], $padrao_email_msg);
                $padrao_email_msg = str_replace("{link}", $data["activation"], $padrao_email_msg);
            }

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

            // Send email
            if(!$mail->send()){
                $this->session->set_flashdata('message', show_alert_error("Houve um problema ao enviar o e-mail, tente novamente ou entre em contato com o administrador."));
                redirect('users', 'refresh');
            }else{
                $this->session->set_flashdata('message', show_alert_success("E-mail reenviado com sucesso."));
                redirect('users', 'refresh');
            }
        }
        $this->session->set_flashdata('message', show_alert_error("Usuário não encontrado."));
        redirect('app_alunos', 'refresh');
    }

    function activate($id, $code = FALSE)
    {
        $id = (int) $id;

        if ($code !== FALSE)
        {
            $activation = $this->ion_auth->activate($id, $code);
        }
        else if ( $this->ion_auth->logged_in() OR $this->ion_auth->has_permissao("app_alunos", "activate"))
        {
            $user = $this->ion_auth->user($id)->row();
            if($user->last_login == "") {
                $this->session->set_flashdata('message', show_alert_error('Este usuário ainda não completou seu cadastro, no momento não é possível ativá-lo.'));
                redirect('app_alunos', 'refresh');
            }
            $activation = $this->ion_auth->activate($id);
        }

        if ($activation)
        {
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect('app_alunos', 'refresh');
        }
        else
        {
            $this->session->set_flashdata('message', $this->ion_auth->errors());
            redirect('app_alunos', 'refresh');
        }
    }


    public function deactivate($id = NULL)
    {
        if ( !$this->ion_auth->logged_in() OR !$this->ion_auth->has_permissao("app_alunos", "deactivate"))
        {
            redirect('app_alunos', 'refresh');
        }

        if($this->ion_auth->is_admin($id)) {
        	if(!$this->ion_auth->is_admin($this->session->userdata('user_id'))) {
        		$this->session->set_flashdata('message', show_alert_error("Para desativar um usuário do grupo admin é preciso que você também esteja no grupo."));
        		redirect('auth/login', 'refresh');
        	}
        }

        $this->breadcrumbs->unshift(2, lang('menu_users_deactivate'), 'admin/users/deactivate');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->form_validation->set_rules('confirm', 'lang:deactivate_validation_confirm_label', 'required');
        $this->form_validation->set_rules('id', 'lang:deactivate_validation_user_id_label', 'required|alpha_numeric');

        $id = (int) $id;

        if ($this->form_validation->run() === FALSE)
        {
            $user = $this->ion_auth->user($id)->row();

            $this->data['csrf']       = $this->_get_csrf_nonce();
            $this->data['id']         = (int) $user->id;
            $this->data['firstname']  = ! empty($user->first_name) ? htmlspecialchars($user->first_name, ENT_QUOTES, 'UTF-8') : NULL;
            $this->data['lastname']   = ! empty($user->last_name) ? ' '.htmlspecialchars($user->last_name, ENT_QUOTES, 'UTF-8') : NULL;

            $this->template->admin_render('admin/app/alunos/deactivate', $this->data);
        }
        else
        {
            if ($this->input->post('confirm') == 'yes')
            {
                if ($this->_valid_csrf_nonce() === FALSE OR $id != $this->input->post('id'))
                {
                    show_error($this->lang->line('error_csrf'));
                }

                if ($this->ion_auth->logged_in() && $this->ion_auth->has_permissao("app_alunos", "deactivate"))
                {
                    $this->ion_auth->deactivate($id);
                }
            }

            redirect('app_alunos', 'refresh');
        }
    }

    private function _get_csrf_nonce()
    {
        $this->load->helper('string');
        $key   = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);

        return array($key => $value);
    }

    private function _valid_csrf_nonce()
    {
        if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE && $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue'))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function server_side() {
        $columns = array( 
            0 =>'u.id', 
            1 =>'first_name', 
            2 =>'last_name',
            3=> 'email',
            4=> 'unidade',
            5=> 'status',
            6=> 'actions'
        );

        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $order = $columns[$this->input->post('order')[0]['column']];
        $dir = $this->input->post('order')[0]['dir'];
  
        $totalData = $this->app_alunos_model->allserver_side_count();
            
        $totalFiltered = $totalData; 
            
        if(empty($this->input->post('search')['value']))
        {            
            $posts = $this->app_alunos_model->allserver_side($limit,$start,$order,$dir);
        }
        else {
            $search = $this->input->post('search')['value']; 

            $posts =  $this->app_alunos_model->server_side_search($limit,$start,$search,$order,$dir);

            $totalFiltered = $this->app_alunos_model->server_side_search_count($search);
        }

        $data = array();
        if(!empty($posts))
        {
            foreach ($posts as $post)
            {
                $nestedData[0] = $post->user_id;
                $nestedData[1] = $post->first_name;
                $nestedData[2] = $post->last_name;
                $nestedData[3] = $post->email;

                $unidades = $this->app_alunos_model->get_unidades($post->user_id);
                $unidades_string = "";
                if($unidades->num_rows() > 0) {
                    $i=0;
                    foreach ($unidades->result() as $value) {
                        if($i > 0 && $i < 3)
                            $unidades_string .= ", ";
                        if($i == 3) {
                            $unidades_string .= "...";
                            break;
                        }
                        $unidades_string .= $value->unidade;
                        $i++;
                    }
                }

                $nestedData[4] = $unidades_string;
                $nestedData[5] = ($post->active) ? '<a href="'.base_url('app_alunos/deactivate/'.$post->user_id).'"><span class="label label-success">'.lang('users_active').'</span></a>' : '<a href="'.base_url('app_alunos/activate/'.$post->user_id).'"><span class="label label-default">'.lang('users_inactive').'</span></a>';

                $actions = '<div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                    <ul class="dropdown-menu" role="menu">';

                $actions .= '<li>'.anchor('users/edit/'.$post->user_id, '<i class="fa fa-edit"></i>'.lang('actions_edit'), array('title'=>lang('actions_edit'))).'</li>';
                $actions .= '<li>'.anchor('users/profile/'.$post->user_id, '<i class="fa fa-eye"></i>'.lang('actions_see'), array('title'=>lang('actions_see'))).'</li>';
                if($post->activation_code != "" && $post->active == 0) {
                    $actions .= '<li>'.anchor('app_alunos/enviar_email/'.$post->user_id, '<i class="fa fa-envelope"></i>'.lang('actions_send_email'), array('title'=>lang('actions_send_email'))).'</li>';
                }
                if($post->first_name == "" && $post->last_name == "" && $post->active == 0) {
                    $actions .= '<li>'.anchor('app_alunos/delete/'.$post->user_id, '<i class="fa fa-trash"></i>'.lang('actions_delete'), array('title'=>lang('actions_delete'))).'</li>';
                }
                $actions .= '<li>'.anchor('app_alunos/dispositivos/'.$post->user_id, '<i class="fa fa-mobile"></i>'.lang('actions_dispositivo'), array('title'=>lang('actions_dispositivo'))).'</li>';
                $actions .= '</ul></div>';
                
                $nestedData[6] = $actions;
                $data[] = $nestedData;

            }
        }
          
        $json_data = array(
            "draw"            => intval($this->input->post('draw')),  
            "recordsTotal"    => intval($totalData),  
            "recordsFiltered" => intval($totalFiltered), 
            "data"            => $data   
        );
            
        exit(json_encode($json_data)); 
    }

}
