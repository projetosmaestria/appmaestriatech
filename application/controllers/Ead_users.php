<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ead_users extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/ead');
        $this->load->helper('functions_helper');
        $this->load->model('ead_users_model');
        $this->load->model('ead_segments_model');
        $this->load->model('auditorias_model');
        $this->load->model('preferences_model');
        $this->load->library('voxy');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_users'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_ead_users'), 'ead_users');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_users", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            /* Get all users */
            $this->ion_auth->join(array("user_unidade", "users.id = user_unidade.user_id"));
            $this->ion_auth->join(array("unidades", "unidades.id = user_unidade.unidade_id"));
            $this->ion_auth->join(array("empresas", "empresas.id = unidades.empresa_id"));
        	$this->ion_auth->where("empresas.id = ".$this->session->userdata('empresa_id'));
            $this->data['users'] = $this->ion_auth->users()->result();

            $seguimentos_usuario_array = array();
            $seguimentos_usuario_session = $this->ead_segments_model->get()->result();
            if(count($seguimentos_usuario_session) > 0) {
                foreach ($seguimentos_usuario_session as $seg) {
                    $seguimentos_usuario_array[] = $seg->segment_label;
                }
            }
            $this->data['seguimentos_lista'] = $seguimentos_usuario_array;

            foreach ($this->data['users'] as $k => $user)
            {
                $ead_user = $this->ead_users_model->get($user->id)->row();

                $segments_array = array();
                if($ead_user) {
                    $ead_user_contratos = $this->ead_users_model->get_contratos($user->id)->result();
                    $array_contratos = array();
                    foreach ($ead_user_contratos as $contrato) {
                        $array_contratos[] = $contrato->id;  
                    };

                    $segments = $this->ead_users_model->get_segments(implode(",", $array_contratos));
                    if($segments) {
                        foreach ($segments->result() as $seg) {
                            $segments_array[] = $seg->segment_label;
                        }
                    }

                    $this->data['users'][$k]->level = $ead_user->level;
                    $this->data['users'][$k]->data_expiracao = $ead_user->data_expiracao;
                    $this->data['users'][$k]->data_proximo_vpa = ($ead_user->data_proximo_vpa != "00/00/0000") ? $ead_user->data_proximo_vpa : '';
                    $this->data['users'][$k]->ead = 1;
                    $this->data['users'][$k]->seguimentos = $segments_array;
                } else {                    
                    $this->data['users'][$k]->level = '';
                    $this->data['users'][$k]->data_expiracao = '';
                    $this->data['users'][$k]->ead = 0;
                    $this->data['users'][$k]->seguimentos = $segments_array;
                }

                $this->data['users'][$k]->groups = $this->ion_auth->get_users_groups($user->id)->result();
            }
            /* Load Template */
            $this->template->admin_render('admin/ead/users/index', $this->data);
        }
	}

    public function create()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_users", "create"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $pref = $this->preferences_model->get_interface('admin_preferences');

            $this->form_validation->set_rules('level', 'lang:ead_users_validation_level_label', 'numeric|less_than[5]|greater_than[0]');
            if($pref->qtd_creditos_ead > 0) {
                $this->form_validation->set_rules('creditos', 'lang:ead_users_validation_creditos_label', 'numeric|less_than['.($pref->qtd_creditos_ead+1).']|greater_than[0]');
            } else {
                $this->form_validation->set_rules('creditos', 'lang:ead_users_validation_creditos_label', 'numeric');
            }

            if ($this->form_validation->run() == false) {
                $this->session->set_flashdata('message', show_alert_error(validation_errors()));
                redirect('ead_users/index', 'refresh');
            }

            if(!validateDate($_POST["data_expiracao"])) {
                $this->session->set_flashdata('message', show_alert_error("Data inválida."));
                redirect('ead_users/index', 'refresh');
            }

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            $data_user = $this->ion_auth->user($_POST['user_id'])->row();

            if($data_user->id) {
                $params = array(
                    'email_address' => $data_user->email,
                    'first_name' => $data_user->first_name." ".$data_user->last_name,
                    'native_language' => 'pt',
                    'level' => $_POST["level"],
                    'expiration_date' => convertDateBRToEU($_POST["data_expiracao"]),
                    'date_joined' => date("Y-m-d"),
                    'tutoring_credits' => $_POST["creditos"]
                );
                $retorno_registro_ead = $this->voxy->make_request("/partners/users/{$data_user->id}", "POST", $params);

                if($retorno_registro_ead["status_code"] == 201 || $retorno_registro_ead["status_code"] == 409) {
                    if($retorno_registro_ead["status_code"] == 201) {
                        $this->session->set_flashdata('message', show_alert_success("Usuário cadastrado no EAD."));

                        $this->ead_users_model->create(array("user_id"=>$_POST['user_id'], "level"=>$_POST["level"], "expiration_date"=>convertDateBRToEU($_POST["data_expiracao"])));
                        $this->auditorias_model->create("ead_users", "create", "Usuário ".$data_user->id." cadastrado no EAD.");
                    } elseif($retorno_registro_ead["status_code"] == 409) {
                        $this->session->set_flashdata('message', show_alert_success("Usuário cadastrado no EAD."));

                        $retorno_registro_ead_update = $this->voxy->make_request("/partners/users/{$data_user->id}", "PUT", $params);
                        if($retorno_registro_ead_update["status_code"] == 200) {
                            $this->ead_users_model->create(array("user_id"=>$_POST['user_id'], "level"=>$_POST["level"], "expiration_date"=>convertDateBRToEU($_POST["data_expiracao"])));
                            $this->auditorias_model->create("ead_users", "create", "Usuário ".$data_user->id." cadastrado no EAD.");
                        }
                    }
                } else {
                    $mensagem_erro = json_decode($retorno_registro_ead["content"]);
                    $this->session->set_flashdata('message', show_alert_error($mensagem_erro->error_message));
                }
            } else {
                $this->session->set_flashdata('message', show_alert_error("Usuário #".$_POST['user_id']." não existe."));
            }

            redirect('ead_users/index', 'refresh');
        }
    }

    public function update()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_users", "update"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $pref = $this->preferences_model->get_interface('admin_preferences');

            $this->form_validation->set_rules('level', 'lang:ead_users_validation_level_label', 'numeric|less_than[8]|greater_than[0]');
            if($pref->qtd_creditos_ead > 0) {
                $qtd_creditos_ead = $pref->qtd_creditos_ead + 1;
                $this->form_validation->set_rules('creditos', 'lang:ead_users_validation_creditos_label', 'numeric|less_than['.$qtd_creditos_ead.']|greater_than[0]');
            } else {
                $this->form_validation->set_rules('creditos', 'lang:ead_users_validation_creditos_label', 'numeric');
            }

            if ($this->form_validation->run() == false) {
                $this->session->set_flashdata('message', show_alert_error(validation_errors()));
                redirect('ead_users/index', 'refresh');
            }

            if(!validateDate($_POST["data_expiracao"])) {
                $this->session->set_flashdata('message', show_alert_error("Data Expiração inválida."));
                redirect('ead_users/index', 'refresh');
            }

            if(!empty($_POST["data_proximo_vpa"])) {
                if(!validateDate($_POST["data_proximo_vpa"])) {
                    $this->session->set_flashdata('message', show_alert_error("Data Próximo VPA inválida."));
                    redirect('ead_users/index', 'refresh');
                }
            }

        
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            $data_user = $this->ion_auth->user($_POST['user_id'])->row();

            if($data_user->id) {
                $params = array(
                    'email_address' => $data_user->email,
                    'first_name' => $data_user->first_name." ".$data_user->last_name,
                    'native_language' => 'pt',
                    'level' => $_POST["level"],
                    'expiration_date' => convertDateBRToEU($_POST["data_expiracao"]),
                    'date_of_next_vpa' => ($_POST["data_proximo_vpa"])?convertDateBRToEU($_POST["data_proximo_vpa"]):'',
                    'tutoring_credits' => $_POST["creditos"]
                );
                
                $retorno_registro_ead = $this->voxy->make_request("/partners/users/{$data_user->id}", "PUT", $params);
                if($retorno_registro_ead["status_code"] == 200) {
                    if($_POST["nova_metodologia"] == 1) {
                        $this->voxy->make_request("/partners/feature_groups/6242/users/{$data_user->id}", "POST", array());
                    }

                    $this->session->set_flashdata('message', show_alert_success("Usuário atualizado no EAD."));                    
                    $this->ead_users_model->update($data_user->id, array("level"=>$_POST["level"], "expiration_date"=>convertDateBRToEU($_POST["data_expiracao"]), "date_of_next_vpa"=>$params['date_of_next_vpa']));
                    $this->session->unset_userdata('notifications_ead');
                    $this->session->unset_userdata('notifications_ead_count');
                    $this->auditorias_model->create("ead_users", "update", "Usuário ".$data_user->id." atualizado no EAD");

                    //provisorio
                    $this->auditorias_model->historico_create(array("LIG_ID_CLIENTE" => $data_user->id, "LIG_ID_USUARIO" => $this->session->userdata('user_id'), "REGISTRO" => "true", "STATUS" => "10", "MENSAGEM" => "Seu perfil foi alterado para: level ".$_POST['level'].", data de expiração: ".$_POST["data_expiracao"].", data do próximo vpa: ".$_POST["data_proximo_vpa"].", créditos de tutoria: ".$_POST["creditos"], "DATA_REGISTRO" => date("Y-m-d"), "HORA_REGISTRO" => date("H:i:s")));
                } else {
                    $mensagem_erro = json_decode($retorno_registro_ead["content"]);
                    $this->session->set_flashdata('message', show_alert_error($mensagem_erro->error_message));
                }
            } else {
                $this->session->set_flashdata('message', show_alert_error("Usuário #".$_POST['user_id']." não existe."));
            }

            redirect('ead_users/index', 'refresh');
        }
    }

    public function buscar_dados_user_voxy() {
        $dados_usuario_ead = $this->voxy->make_request("/partners/users/{$_POST['user_id']}", "GET");
        $data_expiracao = "";
        if($dados_usuario_ead["status_code"] == 200) {
            $retorno = json_decode($dados_usuario_ead["content"], true);
            $retorno["nova_metodologia"] = false;
            foreach ($retorno["feature_group"] as $key => $value) {
                if($key == "id") {
                    if($value == 6242)
                        $retorno["nova_metodologia"] = true;
                }
            }
        }
        exit(json_encode($retorno));
    }
	
	/*public function teste() {
		//2021-08-31
		$params = array(
			'expiration_date' => "2021-07-22"
		);
        $dados_usuario_ead = $this->voxy->make_request("/partners/users/17139", "GET");
        //$dados_usuario_ead = $this->voxy->make_request("/partners/users/17139", "PUT", $params);
        
        if($dados_usuario_ead["status_code"] == 200) {
            $retorno = json_decode($dados_usuario_ead["content"], true);
            exit("<pre>".print_r($retorno,true)."</pre>");
        }
		exit;
    }*/

}
