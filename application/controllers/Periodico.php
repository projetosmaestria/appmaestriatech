<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Periodico extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/periodico');
        $this->load->model('periodico_model');
        $this->load->model('auditorias_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');
        $this->load->library('iugu_library');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_periodico'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_periodico'), 'periodico');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("periodico", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['periodico'] = $this->periodico_model->get();
            $this->data['message'] = $this->session->flashdata('message');

            $this->data['can_vincular'] = $this->ion_auth->has_permissao("periodico", "vincular");

            /* Load Template */
            $this->template->admin_render('admin/periodico/index', $this->data);
        }
	}

	public function view($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("periodico", "view"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_periodico_view'), 'periodico/view');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');

        $periodico = $this->periodico_model->get($id);

        if($periodico->num_rows() == 0) {
            $this->session->set_flashdata('message', show_alert_error("Seu usuário não tem acesso ao periódico específico."));
            redirect('periodico');
        }

        if($_POST) {
            $this->periodico_model->createByPeriodico($id, $_POST["planos"]);
            $this->session->set_flashdata('message', show_alert_success($this->periodico_model->messages()));
            redirect('periodico');
        }

        $this->data['periodico'] = $periodico->row();

        if($this->data['periodico']->iugu_status == "S") {
            $iugu = new Iugu_library();
            $iugu->setToken($this->data['periodico']->iugu_token);
            $retorno = $iugu->listar_planos();

            $planos_arr = array();
            if($retorno["erro"] == false) {
                foreach ($retorno["data"]["items"] as $item) {
                    $planos_arr = $retorno["data"]["items"];
                }
            }

            $planos_arr_check = array();
            $iugu_plan_periodico = $this->periodico_model->getIuguPlanPeriodico($id);
            foreach ($iugu_plan_periodico->result() as $value) {
                $planos_arr_check[] = $value->plano;
            }

            //exit("<pre>".print_r($planos_arr_check,true)."</pre>");
            $this->data['planos_iugu'] = $planos_arr;
            $this->data['planos_check'] = $planos_arr_check;
        } else {
            $this->session->set_flashdata('message', show_alert_error("Configure as informações IUGU na unidade."));
            redirect('periodico');
        }

        /* Load Template */
        $this->template->admin_render('admin/periodico/view', $this->data);
	}

}
