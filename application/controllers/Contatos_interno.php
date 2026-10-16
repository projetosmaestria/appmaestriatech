<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contatos_interno extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/contatos_interno');
        // $this->load->model('auditorias_model');
        $this->load->model('contatos_interno_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('contatos_interno'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('contatos_interno'), 'contatos_interno');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contatos_interno", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');
            $contatos = $this->contatos_interno_model->get()->result();
            foreach ($contatos as $key => $contato) {
                $user_para = $this->ion_auth_model->user($contato->user_id_para)->row();
                $contato->first_name_para = $user_para->first_name;
                $contato->last_name_para = $user_para->last_name;
                $contatos[$key] = $contato;
            }
            $this->data['contatos'] = $contatos;

            /* Load Template */
            $this->template->admin_render('admin/contatos_interno/index', $this->data);
        }
    }

    public function ver($id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contatos_interno", "ver"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->breadcrumbs->unshift(2, lang('view'), 'contatos_interno/ver');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');
            $contato = $this->contatos_interno_model->get($id)->row();
            $this->data['contato'] = $contato;

            if($contato->user_id_para != $this->session->userdata('user_id')) {
                 $this->session->set_flashdata('message', show_alert_error("Esta mensagem não pode ser lida pelo seu usuário."));
                redirect('contatos_interno/index', 'refresh');
            }

            if($contato->visualizada == "N")
                $this->contatos_interno_model->update($id, array('visualizada'=>'S', 'data_visualizada' => date('Y-m-d H:i:s')));

            $this->data['de'] = array(
                'name'  => 'de',
                'id'    => 'de',
                'type'  => 'text',
                'class' => 'form-control',
                'readonly' => 'readonly',
                'value' => $contato->first_name." ".$contato->last_name
            );
            $this->data['assunto'] = array(
                'name'  => 'assunto',
                'id'    => 'assunto',
                'type'  => 'text',
                'class' => 'form-control',
                'readonly' => 'readonly',
                'value' => $contato->assunto
            );
            $this->data['mensagem'] = array(
                'name'  => 'mensagem',
                'id'    => 'mensagem',
                'class' => 'form-control',
                'readonly' => 'readonly',
                'value' => $contato->mensagem
            );
            $this->data['data'] = array(
                'name'  => 'data',
                'id'    => 'data',
                'type'  => 'text',
                'class' => 'form-control',
                'readonly' => 'readonly',
                'value' => $contato->data_envio
            );

            /* Load Template */
            $this->template->admin_render('admin/contatos_interno/ver', $this->data);
        }
    }

}
