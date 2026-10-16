<?php
defined('BASEPATH') OR exit('No direct scrip access allowed');

class Midias_eventos_orcamentos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/midias');
        $this->load->model('midias_eventos_orcamentos_model');
        $this->load->model('auditorias_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_midias_eventos_orcamentos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_midias_eventos_orcamentos'), 'midias_eventos_orcamentos');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("midias_eventos_orcamentos", "index"))
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
                    $data_inicio = convertDateBRToEU($_POST["data_filtro_inicio"]);
                else
                    $data_inicio = "";
                if(@$_POST["data_filtro_fim"] != "")
                    $data_fim = convertDateBRToEU($_POST["data_filtro_fim"]);
                else
                    $data_fim = "";
                if(@$_POST["q"] != "")
                    $q = $_POST["q"];
                else
                    $q = "";
                if(@$_POST["unidades"]) 
                    $unidades = $_POST["unidades"];
                else
                    $unidades = "";
                if(@$_POST["midias"]) 
                    $midias = $_POST["midias"];
                else
                    $midias = "";
                if(@$_POST["eventos"]) 
                    $eventos = $_POST["eventos"];
                else
                    $eventos = "";


                $where = "";
                if($data_inicio != "")
                    $where .= " eo.data >= '".$data_inicio."'";
                if($data_fim != "") {
                    if($where != "")
                        $where .= " AND ";
                    $where .= " eo.data <= '".$data_fim."'";
                }
                if($q != "") {
                    if($where != "")
                        $where .= " AND ";
                    $encoding = mb_internal_encoding();
                    $where .= " (UPPER(m.descricao) LIKE '%".mb_strtoupper($q, $encoding)."%' OR UPPER(e.descricao) LIKE '%".mb_strtoupper($q, $encoding)."%')";
                }
                if($unidades != "") {
                    if($where != "")
                        $where .= " AND ";
                    $where .= " u.id = '".$unidades."'";
                }
                if($midias != "") {
                    if($where != "")
                        $where .= " AND ";
                    $where .= " m.id = '".$midias."'";
                }
                if($eventos != "") {
                    if($where != "")
                        $where .= " AND ";
                    $where .= " e.id = '".$eventos."'";
                }

                if(@$_POST["data_filtro_inicio"] != "")
                    $this->data['data_inicio'] = convertDateBRToEU($_POST["data_filtro_inicio"]);
                if(@$_POST["data_filtro_fim"] != "")
                    $this->data['data_fim'] = convertDateBRToEU($_POST["data_filtro_fim"]);
                $this->data['q'] = $q;
                $orcamentos = $this->midias_eventos_orcamentos_model->get(null, $where)->result();
            } else {
                $this->data['data_inicio'] = convertDateBRToEU(date('Y-m-d', strtotime("-1 weeks")));
                $this->data['data_fim'] = convertDateBRToEU(date('Y-m-d'));
                $where = " eo.data >= '".$this->data['data_inicio']."' AND eo.data <= '".$this->data['data_fim']."'";
                $orcamentos = $this->midias_eventos_orcamentos_model->get(null, $where)->result();
            }

            $total_valor_gasto = 0;
            $total_leads = 0;
            $this->data['orcamentos'] = array();
            foreach ($orcamentos as $value) {
                $where = " (created_at >= '".$value->data." 00:00:00' AND created_at <= '".$value->data." 23:59:59')";
                $qtd_leads = $this->midias_eventos_orcamentos_model->get_qtd_leads($value->evento_id, $where);
                $value->qtd_leads = $qtd_leads;
                $value->valor_lead = ($qtd_leads > 0) ? $value->valor_gasto / $qtd_leads : 0;
                $total_valor_gasto += $value->valor_gasto;
                $total_leads += $qtd_leads;
                $this->data['orcamentos'][] = $value;
            }

            $this->data['total_valor_gasto'] = $total_valor_gasto;
            $this->data['total_leads'] = $total_leads;
            $this->data['total_leads_por_valor_gasto'] = ($total_leads > 0) ? $total_valor_gasto / $total_leads : 0;

            $unidades = $this->midias_eventos_orcamentos_model->get_unidades();
            $this->data['unidades'] = array(
                'id'=>'filtro_unid',
                'name'=>'unidades',
                'class'=>'form-control', 
                'onchange'=>"get_eventos(this.value, '".base_url()."')",
                'options' => $unidades, 
                'selected'=> ($this->input->post('unidades')) ? $this->input->post('unidades') : ''
            );

            $midias = $this->midias_eventos_orcamentos_model->get_midias($this->input->post('unidades'));
            $this->data['midias'] = array(
                'id'=>'filtro_midias',
                'name'=>'midias',
                'class'=>'form-control', 
                'options' => $midias, 
                'selected'=> ($this->input->post('midias')) ? $this->input->post('midias') : ''
            );
            if($this->input->post('unidades')) {
                $eventos = $this->midias_eventos_orcamentos_model->get_eventos($this->input->post('unidades'));
            }else {
                $eventos = array(""=>"Selecione");
            }
            $this->data['eventos'] = array(
                'id'=>'filtro_eventos',
                'name'=>'eventos',
                'class'=>'form-control', 
                'options' => $eventos, 
                'selected'=> ($this->input->post('eventos')) ? $this->input->post('eventos') : ''
            );

            /* Load Template */
            $this->template->admin_render('admin/midias/eventos/orcamentos/index', $this->data);
        }
    }

    public function exportar() {
        if(@$_POST["data_filtro_inicio"] != "")
            $data_inicio = convertDateBRToEU($_POST["data_filtro_inicio"]);
        else
            $data_inicio = "";
        if(@$_POST["data_filtro_fim"] != "")
            $data_fim = convertDateBRToEU($_POST["data_filtro_fim"]);
        else
            $data_fim = "";
        if(@$_POST["q"])
            $q = $_POST["q"];
        else
            $q = "";
        if(@$_POST["unidades"]) 
            $unidades = $_POST["unidades"];
        else
            $unidades = "";
        if(@$_POST["midias"]) 
            $midias = $_POST["midias"];
        else
            $midias = "";
        if(@$_POST["eventos"]) 
            $eventos = $_POST["eventos"];
        else
            $eventos = "";

        $where = "";
        if($data_inicio != "")
            $where .= " eo.data >= '".$data_inicio."'";
        if($data_fim != "") {
            if($where != "")
                $where .= " AND ";
            $where .= " eo.data <= '".$data_fim."'";
        }
        if($q != "") {
            if($where != "")
                $where .= " AND ";
            $encoding = mb_internal_encoding();
            $where .= " (UPPER(m.descricao) LIKE '%".mb_strtoupper($q, $encoding)."%' OR UPPER(e.descricao) LIKE '%".mb_strtoupper($q, $encoding)."%')";
        }
        if($unidades != "") {
            if($where != "")
                $where .= " AND ";
            $where .= " u.id = '".$unidades."'";
        }
        if($midias != "") {
            if($where != "")
                $where .= " AND ";
            $where .= " m.id = '".$midias."'";
        }
        if($eventos != "") {
            if($where != "")
                $where .= " AND ";
            $where .= " e.id = '".$eventos."'";
        }

        $orcamentos = $this->midias_eventos_orcamentos_model->get(null, $where);
        
        $nome_arquivo = "orcamentos_".time().".xls";
        $caminho_arquivo = "./upload/contatos/".$nome_arquivo;
        $this->load->library('PHPExcel');
        $this->load->helper('download');

        $planilha = $this->phpexcel;

        $planilha->setActiveSheetIndex(0)->setCellValue('A1', 'Evento');
        $planilha->setActiveSheetIndex(0)->setCellValue('B1', 'Mídia');
        $planilha->setActiveSheetIndex(0)->setCellValue('C1', 'Unidade');
        $planilha->setActiveSheetIndex(0)->setCellValue('D1', 'Data');
        $planilha->setActiveSheetIndex(0)->setCellValue('E1', 'Qtd Leads');
        $planilha->setActiveSheetIndex(0)->setCellValue('F1', 'Valor por Lead');
        $planilha->setActiveSheetIndex(0)->setCellValue('G1', 'Valor Gasto');

        $contador = 1;
        foreach ($orcamentos->result_array() as $linha) {
            $where = " (created_at >= '".$linha['data']." 00:00:00' AND created_at <= '".$linha['data']." 23:59:59')";
            $qtd_leads = $this->midias_eventos_orcamentos_model->get_qtd_leads($linha['evento_id'], $where);
            $contador++;
            $planilha->setActiveSheetIndex(0)->setCellValue("A".$contador, $linha["evento_desc"]);
            $planilha->setActiveSheetIndex(0)->setCellValue("B".$contador, $linha["midia_desc"]);
            $planilha->setActiveSheetIndex(0)->setCellValue("C".$contador, $linha["unidade"]);
            $planilha->setActiveSheetIndex(0)->setCellValue("D".$contador, $linha["data_formatada"]);
            $planilha->setActiveSheetIndex(0)->setCellValue("E".$contador, $qtd_leads);
            $planilha->setActiveSheetIndex(0)->setCellValue("F".$contador, ($qtd_leads > 0) ? $linha['valor_gasto'] / $qtd_leads : 0);
            $planilha->setActiveSheetIndex(0)->setCellValue("G".$contador, $linha["valor_gasto"]);
        }

        $planilha->getActiveSheet()->setTitle("Orçamentos Leads");

        $objgravar = PHPExcel_IOFactory::createWriter($planilha, 'Excel2007');
        $objgravar->save($caminho_arquivo);

        $data = file_get_contents(str_replace("https", "http", base_url($caminho_arquivo)));
        unlink(FCPATH."upload/contatos/".$nome_arquivo);
        
        //$this->contatos_model->update_exportados(@$data_inicio, @$data_fim, @$q, $unidades);
        $this->auditorias_model->create("Midias_eventos_orcamentos", "exportar", "Orçamentos de leads exportados pelo usuário ".$this->session->userdata('user_id')." com filtros busca: '".@$q."', data inicio: '".@$_POST["data_filtro_inicio"]."', data fim: '".@$_POST["data_filtro_fim"]."'");
        force_download($nome_arquivo, $data);
    }

    public function get_eventos()
    {
        $eventos = $this->midias_eventos_orcamentos_model->get_eventos($_POST['unidade']);
        if(count($eventos) > 0) {
            $html = "";
            foreach ($eventos as $k => $e) {
                $html .= "<option value='".$k."'>".$e."</option>";
            }
        }
        echo $html;
        exit;
    }
}
