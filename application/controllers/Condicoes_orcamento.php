<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Condicoes_orcamento extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/condicoes_orcamento');
        $this->load->model('condicoes_orcamento_model');
        $this->load->model('auditorias_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');
        $this->load->library('iugu_library');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_condicoes_orcamento'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_condicoes_orcamento'), 'condicoes_orcamento');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("condicoes_orcamento", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['condicoes'] = $this->condicoes_orcamento_model->get();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/condicoes_orcamento/index', $this->data);
        }
	}

	public function view($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("condicoes_orcamento", "view"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_condicoes_orcamento_view'), 'condicoes_orcamento/view');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');

        /* Variables */
		$this->data['condicao'] = $this->condicoes_orcamento_model->get($id)->row();

        if($_POST) {
            $this->condicoes_orcamento_model->update($id, array("plano_iugu" => (!empty($_POST["plano_iugu"])) ? $_POST["plano_iugu"] : null));
            $this->session->set_flashdata('message', show_alert_success($this->condicoes_orcamento_model->messages()));
            redirect('condicoes_orcamento/view/'.$id);
        }

        if($this->data['condicao']->iugu_status == "S") {
            $iugu = new Iugu_library();
            $iugu->setToken($this->data['condicao']->iugu_token);
            $retorno = $iugu->listar_planos();

            $planos_arr = array("" => "Selecionar");
            if($retorno["erro"] == false) {
                foreach ($retorno["data"]["items"] as $item) {
                    $planos_arr[$item['identifier']] = $item['name']." - ".number_format($item["prices"][0]["value_cents"] / 100, 2, ',', '.');
                }
            }

            $this->data['plano_iugu'] = array(
                'name'  => 'plano_iugu',
                'id'    => 'plano_iugu',
                'class' => 'form-control',
                'options' => $planos_arr,
                'selected' => $this->data['condicao']->plano_iugu
            );
        }

        /* Load Template */
        $this->template->admin_render('admin/condicoes_orcamento/view', $this->data);
	}


	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("produtos", "activate"))
		{
			$activation = $this->produtos_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->produtos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->produtos_model->messages()));
		redirect('produtos', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("produtos", "deactivate"))
		{
			$deactivation = $this->produtos_model->deactivate($id);
		}
		if(@$deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->produtos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error(lang('permission_denied')));
		redirect('produtos', 'refresh');
	}

}
