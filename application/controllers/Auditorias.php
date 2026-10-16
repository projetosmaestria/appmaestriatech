<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditorias extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/auditorias');
        $this->load->model('auditorias_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_auditorias'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_auditorias'), 'auditorias');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("auditorias", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            $permissoes = array();

			foreach ($this->ion_auth_model->get_users_groups_permissoes()->result() as $key => $value) {
				$unserialized = unserialize($value->permissoes);
				if(is_array($unserialized)) {
					foreach ($unserialized as $key => $perm) {
						if(array_key_exists($key, $permissoes)) {
							foreach ($perm as $_p) {
								if(!in_array($_p, $permissoes[$key])) {
									$permissoes[$key][] = $_p;
								}
							}
						} else {
							$permissoes[$key] = $perm;
						}
					}
				}
			}

			$auditoria_array = array();
            foreach ($this->auditorias_model->get()->result() as $auditoria) {
            	if(@$permissoes[$auditoria->controlador]) {
            		if(in_array($auditoria->metodo, $permissoes[$auditoria->controlador]))
            			$auditoria_array[] = $auditoria;
            	}            	
            }
            $this->data['auditorias'] = $auditoria_array;

            /* Load Template */
            $this->template->admin_render('admin/auditorias/index', $this->data);
        }
    }
}
