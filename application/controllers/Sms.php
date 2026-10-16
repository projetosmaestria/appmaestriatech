<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/sms');
        $this->load->model('auditorias_model');
        $this->load->model('sms_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_sms'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_sms'), 'sms');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("sms", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            if($_POST) {
                // exit("<pre>".print_r($_POST,true)."</pre>");
                if(@$_POST["data_filtro_inicio"] != "")
                    $data_inicio = convertDateBRToEU($_POST["data_filtro_inicio"])." 00:00:01";
                else
                    $data_inicio = "";
                if(@$_POST["data_filtro_fim"] != "")
                    $data_fim = convertDateBRToEU($_POST["data_filtro_fim"])." 23:59:59";
                else
                    $data_fim = "";
                if(@$_POST["unidades"]) 
                    $unidades = $_POST["unidades"];
                else
                    $unidades = array();


                $where = "";
                if($data_inicio != "")
                    $where .= " s.data_cadastro >= '".$data_inicio."'";
                if($data_fim != "") {
                    if($where != "")
                        $where .= " AND ";
                    $where .= " s.data_cadastro <= '".$data_fim."'";
                }

                if(@$_POST["data_filtro_inicio"] != "")
                    $this->data['data_inicio'] = convertDateBRToEU($_POST["data_filtro_inicio"]);
                if(@$_POST["data_filtro_fim"] != "")
                    $this->data['data_fim'] = convertDateBRToEU($_POST["data_filtro_fim"]);
                $this->data['sms'] = $this->sms_model->get(null, $where, $unidades)->result();
            } else {
                $this->data['data_inicio'] = convertDateBRToEU(date('Y-m-d', strtotime("-3 days")));
                $this->data['data_fim'] = convertDateBRToEU(date('Y-m-d'));
                $where = " s.data_cadastro >= '".$this->data['data_inicio']." 00:00:00' AND s.data_cadastro <= '".$this->data['data_fim']." 23:59:59'";
                $this->data['sms'] = $this->sms_model->get(null, $where, array())->result();
            }

            if(count($this->data['sms']) == 0) {
                $this->data['message'] .= show_alert_warning("Não há sms cadastrados nos últimos 3 dias. Utilize o filtro para busca em períodos maiores.");
            }

            $unidades = $this->sms_model->get_unidades();
            $this->data['unidades'] = array(
                'id'=>'filtro_unid',
                'name'=>'unidades[]',
                'class'=>'multipleSelect', 
                'multiple'=>'multiple', 
                'options' => $unidades, 
                'selected'=> ($this->input->post('unidades')) ? $this->input->post('unidades') : ''
            );

            /* Load Template */
            $this->template->admin_render('admin/sms/index', $this->data);
        }
    }

    public function buscar_detalhes_sms()
    {
        $sms = $this->sms_model->get($_POST['id'])->row();
        $sms->mensagem = nl2br($sms->mensagem);
        if($sms->id)
            $sms->data_cadastro = convertDateEUToBR($sms->data_cadastro);
        echo json_encode($sms);
        exit;
    }
}
