<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contatos extends Admin_Controller {

    private $FORM = [
        0 => "Cebrac Landing Page"
    ];

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/contatos');
        $this->load->model('auditorias_model');
        $this->load->model('contatos_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_contatos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_contatos'), 'contatos');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contatos", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            if($_POST) {
                if(@$_POST["data_filtro_inicio"] != "")
                    $data_inicio = convertDateBRToEU($_POST["data_filtro_inicio"])." 00:00:01";
                else
                    $data_inicio = "";
                if(@$_POST["data_filtro_fim"] != "")
                    $data_fim = convertDateBRToEU($_POST["data_filtro_fim"])." 23:59:59";
                else
                    $data_fim = "";
                if(@$_POST["exportados_filtro"] != "")
                    $exportado = $_POST["exportados_filtro"];
                else
                    $exportado = "";                
                if(@$_POST["q"] != "")
                    $q = $_POST["q"];
                else
                    $q = "";
                if(@$_POST["unidades"]) 
                    $unidades = $_POST["unidades"];
                else
                    $unidades = array();

                $idade_de = "";
                $idade_ate = "";
                if(@$_POST["idade_de"] != "" || @$_POST["idade_ate"] != "") {
                    if(@$_POST["idade_de"] != "")
                        $idade_de = $_POST["idade_de"];
                    if(@$_POST["idade_ate"] != "")
                        $idade_ate = $_POST["idade_ate"];
                } 

                $where = "";
                if($data_inicio != "")
                    $where .= " c.created_at >= '".$data_inicio."'";
                if($data_fim != "") {
                    if($where != "")
                        $where .= " AND ";
                    $where .= " c.created_at <= '".$data_fim."'";
                }
                if($q != "") {
                    if($where != "")
                        $where .= " AND ";
                    $encoding = mb_internal_encoding();
                    $where .= " (UPPER(m.descricao) LIKE '%".mb_strtoupper($q, $encoding)."%' OR UPPER(e.descricao) LIKE '%".mb_strtoupper($q, $encoding)."%' OR UPPER(c.nome) LIKE '%".mb_strtoupper($q, $encoding)."%' OR c.celular LIKE '%".$q."%' OR UPPER(c.cidade) LIKE '%".mb_strtoupper($q, $encoding)."%')";
                }
                if($exportado != "") {
                    if($where != "")
                        $where .= " AND ";
                    $where .= " exportado = '".$exportado."'";
                }
                if($idade_de != "" || $idade_ate != "") {
                    if($where != "")
                        $where .= " AND ";
                    if($idade_de != "" && $idade_ate != "") {
                        $where .= " (idade >= '".$idade_de."' AND idade <= '".$idade_ate."')";
                    } else {
                        if($idade_de != "")
                            $where .= " idade >= '".$idade_de."'";
                        if($idade_ate != "")
                            $where .= " idade <= '".$idade_ate."'";
                    }
                }

                if(@$_POST["data_filtro_inicio"] != "")
                    $this->data['data_inicio'] = convertDateBRToEU($_POST["data_filtro_inicio"]);
                if(@$_POST["data_filtro_fim"] != "")
                    $this->data['data_fim'] = convertDateBRToEU($_POST["data_filtro_fim"]);
                $this->data['exportado'] = $exportado;
                $this->data['q'] = $q;
                $this->data['contatos'] = $this->contatos_model->get(null, $where, $unidades)->result();
            } else {
                $this->data['data_inicio'] = convertDateBRToEU(date('Y-m-d', strtotime("-3 days")));
                $this->data['data_fim'] = convertDateBRToEU(date('Y-m-d'));
                $where = " c.created_at >= '".$this->data['data_inicio']." 00:00:00' AND c.created_at <= '".$this->data['data_fim']." 23:59:59'";
                $this->data['contatos'] = $this->contatos_model->get(null, $where, array())->result();
            }

            if(count($this->data['contatos']) == 0) {
                $this->data['message'] .= show_alert_warning("Não há contatos cadastrados nos últimos 3 dias. Utilize o filtro para busca em períodos maiores.");
            }

            $unidades = $this->contatos_model->get_unidades();
            $this->data['unidades'] = array(
                'id'=>'filtro_unid',
                'name'=>'unidades[]',
                'class'=>'multipleSelect', 
                'multiple'=>'multiple', 
                'options' => $unidades, 
                'selected'=> ($this->input->post('unidades')) ? $this->input->post('unidades') : ''
            );
            $this->data['idade_de'] = array(
                'id'=>'idade_de',
                'name'=>'idade_de',
                'class'=>'form-control',
                'value'=>($this->input->post('idade_de')) ? $this->input->post('idade_de') : ''
            );
            $this->data['idade_ate'] = array(
                'id'=>'idade_ate',
                'name'=>'idade_ate',
                'class'=>'form-control',
                'value'=>($this->input->post('idade_ate')) ? $this->input->post('idade_ate') : ''
            );

            $this->data['has_exportar'] = false;
            $this->data['has_alterar_unidade'] = false;
            $this->data['select_acoes'] = '<select name="acoes" id="acoes" class="form-control"><option value="">Selecione</option>';
            if($this->ion_auth->has_permissao("contatos", "exportar")) {
                $this->data['has_exportar'] = true;
                $this->data['select_acoes'] .= '<option value="exportar">Exportar</option>';

                if(!$this->ion_auth->has_permissao("contatos", "alterar_unidade")) {
                    $this->data['select_acoes'] .= '</select>';
                }
            }

            if($this->ion_auth->has_permissao("contatos", "alterar_unidade")) {
                $empresas = $this->contatos_model->get_empresas();
                $this->data['select_acoes'] .= '<option value="alterar_unidade">Alterar Unidade</option></select>';
                $this->data['select_acoes'] .= '<select name="empresas" id="empresas" class="form-control" style="display:none;"><option value="">Selecione a Empresa</option>';
                foreach ($empresas as $key => $value) {
                    $this->data['select_unidades_acoes'][$key] = '<option value="">Selecione a Unidade</option>';
                    $this->data['select_acoes'] .= '<option value="'.$key.'">'.str_replace("'", "", $value).'</option>';
                    foreach ($this->contatos_model->get_unidades($key) as $key_u => $value_u) {
                        $this->data['select_unidades_acoes'][$key] .= '<option value="'.$key_u.'">'.str_replace("'", "", $value_u).'</option>';
                    }
                }
                $this->data['select_acoes'] .= '</select>';
                $this->data['select_acoes'] .= '<select name="unidades" id="unidades" class="form-control" style="display:none;"><option value="">Selecione a Unidade</option></select>';
                $this->data['has_alterar_unidade'] = true;
            }

            /* Load Template */
            $this->template->admin_render('admin/contatos/index', $this->data);
        }
    }

    public function exportar() {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contatos", "exportar"))
        {
            redirect('auth/login', 'refresh');
        } else {
            if(@$_POST["data_filtro_inicio"] != "")
                $data_inicio = convertDateBRToEU($_POST["data_filtro_inicio"])." 00:00:01";
            else
                $data_inicio = "";
            if(@$_POST["data_filtro_fim"] != "")
                $data_fim = convertDateBRToEU($_POST["data_filtro_fim"])." 23:59:59";
            else
                $data_fim = "";
            /*if(@$_POST["exportados_filtro"] != "")
                $exportados = $_POST["exportados_filtro"];
            else
                $exportados = "";*/
            if(@$_POST["q"])
                $filter = $_POST["q"];
            else
                $filter = "";
            if(@$_POST["unidades"]) 
                $unidades = $_POST["unidades"];
            else
                $unidades = array();

            $contatos = $this->contatos_model->get_to_export(@$data_inicio, @$data_fim, @$filter, @explode(",", $_POST["contatos"]), $unidades);
            // exit("<pre>".print_r($contatos->result(),true)."</pre>");
            // exit($this->db->last_query());

            $nome_arquivo = "contato_".time().".xls";
            $caminho_arquivo = "./upload/contatos/".$nome_arquivo;
            $this->load->library('PHPExcel');
            $this->load->helper('download');

            $planilha = $this->phpexcel;

            $planilha->setActiveSheetIndex(0)->setCellValue('A1', 'Unidade');
            $planilha->setActiveSheetIndex(0)->setCellValue('B1', 'Mídia');
            $planilha->setActiveSheetIndex(0)->setCellValue('C1', 'Evento');
            $planilha->setActiveSheetIndex(0)->setCellValue('D1', 'Nome');
            $planilha->setActiveSheetIndex(0)->setCellValue('E1', 'Celular');
            $planilha->setActiveSheetIndex(0)->setCellValue('F1', 'E-mail');
            $planilha->setActiveSheetIndex(0)->setCellValue('G1', 'Visualizado');
            $planilha->setActiveSheetIndex(0)->setCellValue('H1', 'Data');
            $planilha->setActiveSheetIndex(0)->setCellValue('I1', 'Cidade');
            $planilha->setActiveSheetIndex(0)->setCellValue('J1', 'Estado');
            $planilha->setActiveSheetIndex(0)->setCellValue('K1', 'Facebook');
            $planilha->setActiveSheetIndex(0)->setCellValue('L1', 'Instagram');
            $planilha->setActiveSheetIndex(0)->setCellValue('M1', 'Mensagem');
            $planilha->setActiveSheetIndex(0)->setCellValue('N1', 'Idade');

            $contador = 1;
            foreach ($contatos->result_array() as $linha) {
                $contador++;
                $planilha->setActiveSheetIndex(0)->setCellValue("A".$contador, $linha["unidade"]);
                $planilha->setActiveSheetIndex(0)->setCellValue("B".$contador, $linha["midia_desc"]);
                $planilha->setActiveSheetIndex(0)->setCellValue("C".$contador, $linha["evento_desc"]);
                $planilha->setActiveSheetIndex(0)->setCellValue("D".$contador, $linha["nome"]);
                $planilha->setActiveSheetIndex(0)->setCellValueExplicit("E".$contador, $linha["celular"], PHPExcel_Cell_DataType::TYPE_STRING);
                $planilha->setActiveSheetIndex(0)->setCellValue("F".$contador, $linha["email"]);
                $planilha->setActiveSheetIndex(0)->setCellValue("G".$contador, $linha["visualizado"]);
                $planilha->setActiveSheetIndex(0)->setCellValue("H".$contador, $linha["data"]);
                $planilha->setActiveSheetIndex(0)->setCellValue('I'.$contador, $linha["cidade"]);
                $planilha->setActiveSheetIndex(0)->setCellValue('J'.$contador, $linha["estado"]);
                $planilha->setActiveSheetIndex(0)->setCellValue('K'.$contador, $linha["facebook"]);
                $planilha->setActiveSheetIndex(0)->setCellValue('L'.$contador, $linha["instagram"]);
                $planilha->setActiveSheetIndex(0)->setCellValue('M'.$contador, $linha["mensagem"]);
                $planilha->setActiveSheetIndex(0)->setCellValue('N'.$contador, $linha["idade"]);
            }

            $planilha->getActiveSheet()->setTitle("Contatos");

            $objgravar = PHPExcel_IOFactory::createWriter($planilha, 'Excel2007');
            $objgravar->save($caminho_arquivo);

            $data = file_get_contents(str_replace("https", "http", base_url($caminho_arquivo)));
            unlink(FCPATH."upload/contatos/".$nome_arquivo);
            
            $this->contatos_model->update_exportados(@$data_inicio, @$data_fim, @$filter, $unidades, @explode(",", $_POST["contatos"]));
            $this->auditorias_model->create("contatos", "exportar", "Contatos exportados pelo usuário ".$this->session->userdata('user_id')." com filtros busca: '".@$filter."', data inicio: '".@$_POST["data_filtro_inicio"]."', data fim: '".@$_POST["data_filtro_fim"]."', unidades selecionadas (".implode(", ", $unidades)."), qtd leads: ".$contatos->num_rows());
            force_download($nome_arquivo, $data);
        }
    }

    public function alterar_unidade() {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("contatos", "alterar_unidade"))
        {
            redirect('auth/login', 'refresh');
        } else {
            if(@count(explode(",", $_POST["contatos"]) > 0) && @$_POST["unidades"] != "") {
                $where = "id IN (".$_POST["contatos"].")";
                $this->contatos_model->update(null, array('unidade_id'=>$_POST["unidades"]), $where);

                $this->auditorias_model->create("contatos", "alterar_unidade", "Contatos (".$_POST["contatos"].") alterados pelo usuário ".$this->session->userdata('user_id')." para unidade: '".@$_POST["unidades"]."'");

                $this->session->set_flashdata('message', show_alert_success("Contato(s) alterado(s) com sucesso."));
            }
            redirect('contatos/index', 'refresh');
        }
    } 

    public function buscar_detalhes_contato()
    {
        $contato = $this->contatos_model->get($_POST['id'])->row();
        $anexos = $this->contatos_model->get_anexos($_POST['id']);
        //if($contato->lead_id != "") {
            //$contato->mensagem = "";
        //}
        $contato->mensagem = nl2br($contato->mensagem);
        if($contato->id)
            $contato->created_at = convertDateEUToBR($contato->created_at);

        $this->contatos_model->update($_POST['id'], array('visualizado'=>'S'));

        $html = "";
        $html .= "<p><strong>Unidade: </strong>".$contato->unidade."</p>";
        $html .= "<p><strong>Nome: </strong>".$contato->nome."</p>";
        $html .= "<p><strong>Celular: </strong>".$contato->celular."</p>";
        $html .= "<p><strong>E-mail: </strong>".$contato->email."</p>";
        $html .= "<p><strong>Data: </strong>".$contato->created_at."</p>";
        $html .= "<p><strong>Cidade: </strong>".$contato->cidade."-".$contato->estado."</p>";
        $html .= "<p><strong>Facebook: </strong>".$contato->facebook."</p>";
        $html .= "<p><strong>Instagram: </strong>".$contato->instagram."</p>";
        $html .= "<p><strong>Mensagem: </strong>".$contato->mensagem."</p>";

		if($anexos != false) {
			if($anexos->num_rows() > 0) {
				$html .= "<hr>";
				$count = 1;
				foreach ($anexos->result() as $anexo) {
					$html .= "<p><strong>Anexo ".$count.": </strong><a href='".base_url($anexo->caminho)."' download>".$anexo->nome."</a></p>";
					$count++;
				}            
			}
		}

        echo $html;
        exit;
    }
}
