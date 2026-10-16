<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ead extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/ead');
        $this->load->helper('functions_helper');
        $this->load->library('voxy');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_login'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_ead'), 'admin/ead');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            $retorno = $this->voxy->make_request("/partners/users/{$this->session->userdata('user_id')}/auth_token", "GET");

            if($retorno["status_code"] == 200) {
            	$content = json_decode($retorno["content"]);
            	$this->data['url_login'] = $content->actions->start;
            }

            /* Load Template */
            $this->template->admin_render('admin/ead/index', $this->data);
        }
	}
}
