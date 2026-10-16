<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ead_seguimentos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/ead');
        $this->load->helper('functions_helper');
        $this->load->model('ead_segments_model');
        $this->load->model('ead_users_model');
        $this->load->model('auditorias_model');
        $this->load->library('voxy');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_seguimentos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_ead_seguimentos'), 'ead_seguimentos');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_seguimentos", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            //$retorno_seguimentos = array();
            /*$dados_seguimentos = $this->voxy->make_request("/partners/segments/", "GET");

            if($dados_seguimentos["status_code"] == 200) {
                $retorno_seguimentos = json_decode($dados_seguimentos["content"]);
            }*/

            $this->data['seguimentos'] = $this->ead_segments_model->get()->result();
            $this->data['tutores'] = $this->ead_segments_model->get_tutores()->result();

            /* Load Template */
            $this->template->admin_render('admin/ead/seguimentos/index', $this->data);
        }
	}

    public function create()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_seguimentos", "create"))
        {
            redirect('auth', 'refresh');
        }

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_seguimentos_create'), 'ead_seguimentos/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Validate form input */
        $this->form_validation->set_rules('descricao', 'lang:seguimento_validation_descricao_label', 'trim|required');

        if ($this->form_validation->run() == TRUE)
        {
            $retorno_add_seguimento = $this->voxy->make_request("/partners/segments/", "POST", array("label"=>str_replace(" ", "_", $this->input->post("descricao"))));

            if($retorno_add_seguimento["status_code"] == 201) {
                $dados = array(
                    //"user_id" => $this->session->userdata('user_id'),
                    "segment_label" => str_replace(" ", "_", $this->input->post("descricao")),
                    "active" => 1
                );
                if ($id = $this->ead_segments_model->create($dados)) {
                    $this->session->set_flashdata('message', show_alert_success("Seguimento cadastrado no EAD."));
                    $this->auditorias_model->create("ead_seguimentos", "create", "Seguimento #".$id." cadastrado no EAD.");
                } else {
                    $this->voxy->make_request("/partners/segments/".str_replace(" ", "_", $this->input->post("descricao")), "DELETE");
                    $this->session->set_flashdata('message', show_alert_error($this->ead_segments_model->errors()));
                }
            } else {
                $mensagem_erro = json_decode($retorno_add_seguimento["content"]);
                $this->session->set_flashdata('message', show_alert_error($mensagem_erro->error_message));
            }
            redirect('ead_seguimentos', 'refresh');
        } else {
            if($_POST)
                $this->data['message'] = show_alert_error("Verifique os campos a serem preenchidos.");

            $this->data['descricao'] = array(
                'name'  => 'descricao',
                'id'    => 'descricao',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('descricao')
            );

            /* Load Template */
            $this->template->admin_render('admin/ead/seguimentos/create', $this->data);
        }
    }

    public function users($seg)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_seguimentos", "users"))
        {
            redirect('auth/login', 'refresh');
        }

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_seguimentos_users'), 'ead_seguimentos/users');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $seguimento_label = $this->ead_segments_model->get($seg)->row()->segment_label;
        $this->data['seguimento_label'] = $seguimento_label;


        if ($_POST) {
            if(count($this->input->post('users')) > 0) {
                $mensagens = "";
                $mensagens_success = "";
                $mensagens_error = "";
                foreach ($this->input->post('users') as $user) {
                    $retorno_delete_user = $this->voxy->make_request("/partners/segments/".$seguimento_label."/users/".$user, "DELETE");
                    if($retorno_delete_user["status_code"] == 200){
                        $mensagens_success .= "<p>Usuário #".$user." removido do seguimento.</p>";
                        $seg_id = $this->ead_segments_model->get_by_label($seguimento_label)->row()->id;
                        $this->ead_segments_model->remove_in_segment(array("user_id"=>$user, "segment_id"=>$seg_id));
                        $this->auditorias_model->create("ead_seguimentos", "users", "Usuário #".$user." removido do seguimento ".$seguimento_label.".");
                    }else {
                        $mensagem_erro = json_decode($retorno_delete_user["content"]);
                        $mensagens_error .= "<p>".$mensagem_erro->error_message."</p>";
                    }
                }
                if($mensagens_success != "")
                    $mensagens .= show_alert_success($mensagens_success);
                if($mensagens_error != "")
                    $mensagens .= show_alert_error($mensagens_error);
                $this->session->set_flashdata('message', $mensagens);
            }
            redirect('ead_seguimentos', 'refresh');
        } else {
            /*$users = array();
            $retorno_users = $this->voxy->make_request("/partners/segments/".$seg, "GET");
            if($retorno_users["status_code"] == 200) {
                foreach(json_decode($retorno_users["content"])->user_ids as $user) {
                    $dados_usuario_ead = $this->voxy->make_request("/partners/users/{$user}", "GET");
                    $dados_usuario_info = json_decode($dados_usuario_ead["content"]);
                    $users[] = array("id"=>$user,"nome"=>$dados_usuario_info->first_name,"data_expiracao"=>convertDateEUToBR($dados_usuario_info->expiration_date));
                }
            }*/

            $this->data['users'] = $this->ead_segments_model->get_users($seguimento_label)->result();

            /* Load Template */
            $this->template->admin_render('admin/ead/seguimentos/users', $this->data);
        }
    }

    /*public function add_user($seg)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_seguimentos", "add_user"))
        {
            redirect('auth/login', 'refresh');
        }

        $this->breadcrumbs->unshift(2, lang('menu_seguimentos_users'), 'ead_seguimentos/users');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['seguimento_label'] = urldecode($seg);

        if ($_POST) {
            if(count($this->input->post('users')) > 0) {
                $admin_pref = $this->preferences_model->get_interface('admin_preferences');
                if(count($this->input->post('users')) > $admin_pref->qtd_alunos) {
                    $this->session->set_flashdata('message', show_alert_error("Cada seguimento aceita até ".$admin_pref->qtd_alunos." aluno(s)."));
                    redirect('ead_seguimentos', 'refresh');
                }
            }
            
            //exit("<pre>".print_r($_POST, true)."</pre>");
            $mensagens = "";
            $mensagens_success = "";
            $mensagens_error = "";
            $seg_id = $this->ead_segments_model->get_by_label($seg)->row()->id;

            //$retorno_users_turma = $this->voxy->make_request("/partners/segments/".$seg, "GET");
            //foreach (json_decode($retorno_users_turma["content"])->user_ids as $user) {
              //  $retorno_delete_user = $this->voxy->make_request("/partners/segments/".$seg."/users/".$user, "DELETE");
            //}

            if(count($this->input->post('users_remove')) > 0) {
                foreach ($this->input->post('users_remove') as $user) {
                    $retorno_delete_user = $this->voxy->make_request("/partners/segments/".$seg."/users/".$user, "DELETE");
                    $this->ead_segments_model->remove_in_segment(array("user_id"=>$user, "segment_id"=>$seg_id));
                }
            }
            if(count($this->input->post('users')) > 0) {
                foreach ($this->input->post('users') as $user_to_add) {
                    if($this->ead_users_model->exists_in_segment(array("user_id"=>$user_to_add, "segment_id"=>$seg_id)) == false) { 
                        $retorno_users_add = $this->voxy->make_request("/partners/segments/".$seg."/users/".$user_to_add, "POST");
                        if($retorno_users_add["status_code"] == 201){
                            $this->ead_users_model->add_in_segment(array("user_id"=>$user_to_add, "segment_id"=>$seg_id));
                            $mensagens_success .= "<p>Usuário #".$user_to_add." adicionado no seguimento.</p>";
                            $this->auditorias_model->create("ead_seguimentos", "add_user", "Usuário #".$user_to_add." adicionado no seguimento ".$seg.".");
                        }else {
                            $mensagem_erro = json_decode($retorno_users_add["content"]);
                            $mensagens_error .= "<p>".$mensagem_erro->error_message."</p>";
                        }
                    }
                }
            }
            if($mensagens_success != "")
                $mensagens .= show_alert_success($mensagens_success);
            if($mensagens_error != "")
                $mensagens .= show_alert_error($mensagens_error);
            $this->session->set_flashdata('message', $mensagens);
            
            redirect('ead_seguimentos', 'refresh');
        } else {
            //$users = array();
            //$retorno_users = $this->voxy->make_request("/partners/users/", "GET");
            //if($retorno_users["status_code"] == 200) {
              //  foreach(json_decode($retorno_users["content"])->users as $user) {
                //    if($user->external_user_id) {
                  //      $dados_usuario_ead = $this->voxy->make_request("/partners/users/{$user->external_user_id}", "GET");
                    //    $dados_usuario_info = json_decode($dados_usuario_ead["content"]);
                       //$data_expiracao = "";
                      // if($dados_usuario_info->expiration_date != null) {
                        //    $data_expiracao = convertDateEUToBR($dados_usuario_info->expiration_date);
                        //}
                        //$users[$user->external_user_id] = $dados_usuario_info;
                        //$users[$user->external_user_id]->data_expiracao = $data_expiracao;
                    //}
                //}
            //}
            //$this->data['users'] = $users;

            //$users_seg = array();
            //$retorno_users_seg = $this->voxy->make_request("/partners/segments/".$seg, "GET");
            //if($retorno_users_seg["status_code"] == 200) {
               // foreach(json_decode($retorno_users_seg["content"])->user_ids as $user) {
                 //   $users_seg[] = array("id"=>$user,"nome"=>$users[$user]->first_name,"data_expiracao"=>convertDateEUToBR($users[$user]->expiration_date));
                //}
            //}
            //$this->data['users_seg'] = $users_seg;

            $this->data['users'] = $this->ead_users_model->get()->result();
            $this->data['users_seg'] = $this->ead_segments_model->get_users($seg)->result();

            $this->template->admin_render('admin/ead/seguimentos/add_user', $this->data);
        }
    }*/

    public function selecionar_tutor() {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_seguimentos", "selecionar_tutor"))
        {
            redirect('auth/login', 'refresh');
        }

        $update = $this->ead_segments_model->update($_POST['seg'], array('tutor_id' => $_POST['tutor']));
        if($update)
            $this->session->set_flashdata('message', show_alert_success($this->ead_segments_model->messages()));
        else
            $this->session->set_flashdata('message', show_alert_error($this->ead_segments_model->messages()));
        redirect('ead_seguimentos', 'refresh');
    }

    public function delete($seg)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_seguimentos", "delete"))
        {
            redirect('auth/login', 'refresh');
        }

        if ($seg != "") {
            $retorno = $this->voxy->make_request("/partners/segments/".$seg, "DELETE");
            if($retorno["status_code"] == 200) {
                $delete_seg = $this->ead_segments_model->delete($seg);
                $this->session->set_flashdata('message', show_alert_success("Seguimento excluído com sucesso."));
                $this->auditorias_model->create("ead_seguimentos", "delete", "Seguimento #".$seg." excluído.");
            }else {
                $mensagem_erro = json_decode($retorno["content"]);
                $this->session->set_flashdata('message', show_alert_error($mensagem_erro->error_message));
            }
            redirect('ead_seguimentos', 'refresh');
        } else {
            $this->session->set_flashdata('message', show_alert_error("Selecione um seguimento."));
        }
        redirect('ead_seguimentos', 'refresh');
    }

    function activate($id)
    {
        $id = (int) $id;
        if (! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_seguimentos", "activate"))
        {
            redirect('auth/login', 'refresh');
        }
        $activation = $this->ead_segments_model->activate($id);
        if($activation)
            $this->session->set_flashdata('message', show_alert_success($this->ead_segments_model->messages()));
        else
            $this->session->set_flashdata('message', show_alert_error($this->ead_segments_model->messages()));
        redirect('ead_seguimentos', 'refresh');
    }


    public function deactivate($id = NULL)
    {
        $id = (int) $id;
        if (! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_seguimentos", "deactivate"))
        {
            redirect('auth/login', 'refresh');
        }
        $deactivation = $this->ead_segments_model->deactivate($id);
        if($deactivation)
            $this->session->set_flashdata('message', show_alert_success($this->ead_segments_model->messages()));
        else
            $this->session->set_flashdata('message', show_alert_error($this->ead_segments_model->messages()));
        redirect('ead_seguimentos', 'refresh');
    }

}
