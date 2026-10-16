<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_qrcodes extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/app_qrcodes');
        $this->load->model('app_qrcodes_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_app_qrcodes'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_app_qrcodes'), 'app_qrcodes');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_qrcodes", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');
            $app_qrcodes = $this->app_qrcodes_model->get()->result();
            foreach ($app_qrcodes as $key => $value) {
            	$app_qrcodes[$key]->empresa_nome = $this->app_qrcodes_model->get_empresa($value->empresa)->row()->nome;
            	if($value->kit == "S") {
            		$app_qrcodes[$key]->titulo = "KIT";
            	}
            }
            $this->data['app_qrcodes'] = $app_qrcodes;
            //exit($this->db->last_query());
            /* Load Template */
            $this->template->admin_render('admin/app/qrcodes/index', $this->data);
        }
    }

    public function pesquisar_qrcode()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_qrcodes", "pesquisar_qrcode"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');

            $sh_arr = array(""=>"Selecione o status");
            $historicos = $this->app_qrcodes_model->get_status_historicos();
            if($historicos->num_rows() > 0) {
                foreach ($historicos->result() as $hist) {
                    $sh_arr[$hist->id] = $hist->descricao;
                }
            }
            $this->data['status_historico'] = array(
                'name'  => 'status_historico',
                'id'    => 'status_historico',
                'class' => 'form-control',
                'options' => $sh_arr
            );
            //$this->data['app_qrcodes'] = $this->app_qrcodes_model->pesquisar_qrcode()->result();
            //exit("<pre>".print_r($this->data['app_qrcodes'],true)."</pre>");
            /* Load Template */
            $this->template->admin_render('admin/app/qrcodes/pesquisar', $this->data);
        }
    }

    public function server_side() {
        $columns = array( 
            0 =>'qr.codigo', 
            1 =>'l.titulo', 
            2=> 'qr.data_cadastro', 
            3=> 'u.first_name',
            4=> 'u.email'
        );

        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $order = $columns[$this->input->post('order')[0]['column']];
        $dir = $this->input->post('order')[0]['dir'];
  
        $totalData = $this->app_qrcodes_model->allserver_side_count();
            
        $totalFiltered = $totalData; 
            
        if(empty($this->input->post('search')['value']))
        {            
            $posts = $this->app_qrcodes_model->allserver_side($limit,$start,$order,$dir);
        }
        else {
            $search = $this->input->post('search')['value']; 

            $posts =  $this->app_qrcodes_model->server_side_search($limit,$start,$search,$order,$dir);

            $totalFiltered = $this->app_qrcodes_model->server_side_search_count($search);
        }

        $data = array();
        if(!empty($posts))
        {
            foreach ($posts as $post)
            {
                $nestedData[0] = $post->codigo;
                $nestedData[1] = $post->titulo;
                $nestedData[2] = $post->data_cadastro;
                $nestedData[3] = $post->first_name." ".$post->last_name;
                $nestedData[4] = $post->email;
                $nestedData[5] =
                    "<div class='btn-group btn-orders-all'><button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-expanded='true'><i class='fa fa-ellipsis-v' aria-hidden='true'></i></i></button>
                        <ul class='dropdown-menu' role='menu'>
                            <li>".anchor('app_qrcodes/gerar_qrcode/'.$post->id, "<i class='fa fa-qrcode'></i>".lang('actions_qrcode'), array())."</li>
                            <li><a href='#' onclick='resetQrcodeModal(\"".$post->id."\")'><i class='fa fa-chain-broken'></i>".lang('actions_reset_qrcode')."</a></li>
                        </ul>
                    </div>";
                $data[] = $nestedData;
            }
        }
          
        $json_data = array(
            "draw"            => intval($this->input->post('draw')),  
            "recordsTotal"    => intval($totalData),  
            "recordsFiltered" => intval($totalFiltered), 
            "data"            => $data   
        );
            
        exit(json_encode($json_data)); 
    }

    public function reset_qrcode()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_qrcodes", "reset_qrcode"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            if(@$_POST["id"] == "" || @$_POST["status_historico"] == "") {
                if(@$_POST["id"] == "") {
                    $this->session->set_flashdata('message', show_alert_error("Código do QRCode não encontrado."));
                }
                if(@$_POST["status_historico"] == "") {
                    $this->session->set_flashdata('message', show_alert_error("Status não selecionado."));
                }
            } else {
                if($this->app_qrcodes_model->has_vinculo_qrcode($_POST["id"])) {
                    $this->app_qrcodes_model->reset_qrcode($_POST["id"], $_POST["status_historico"]);
                    $this->session->set_flashdata('message', show_alert_success("QRCode resetado com sucesso."));
                }else {
                    $this->session->set_flashdata('message', show_alert_error("Qrcode não existe vínculo."));
                }
            }
            redirect('app_qrcodes/pesquisar_qrcode', 'refresh');
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_qrcodes", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_qrcodes_create'), 'app_qrcodes/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('qtd', 'lang:validation_qtd_label', 'numeric|required');
		$this->form_validation->set_rules('livro', 'lang:validation_livro_label', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$dados = array(
				"livro" => $this->input->post("livro"),
				"qtd" => $this->input->post("qtd"),
				"observacao" => $this->input->post("obs"),
				"data_cadastro" => date('Y-m-d H:i:s')
			);

			$id = $this->app_qrcodes_model->create_info($dados);
			if($id != false) {
				$dados['qrcode_info'] = $id;
				if($this->app_qrcodes_model->create($dados))
					$this->session->set_flashdata('message', show_alert_success($this->app_qrcodes_model->messages()));
				else
					$this->session->set_flashdata('message', show_alert_error($this->app_qrcodes_model->messages()));
			}
			else
				$this->session->set_flashdata('message', show_alert_error("Houve um problema ao criar o QRCode."));
			redirect('app_qrcodes', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_qrcodes_model->errors() ? show_alert_error($this->app_qrcodes_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['qtd'] = array(
				'name'  => 'qtd',
				'id'    => 'qtd',
				'type'  => 'number',
                'class' => 'form-control',
				'value' => $this->input->post('qtd')
			);
			$this->data['obs'] = array(
				'name'  => 'obs',
				'id'    => 'obs',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('obs')
			);
            $this->data['livros'] = $this->app_qrcodes_model->get_livros()->result();
			$this->data['empresa'] = $this->session->userdata('empresa_id');

            /* Load Template */
            $this->template->admin_render('admin/app/qrcodes/create', $this->data);
		}
	}

	public function gerar_excel($id) {
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_qrcodes", "gerar_excel"))
		{
			redirect('auth', 'refresh');
		}

        $qrcode_info = $this->app_qrcodes_model->get($id)->row();
        $qrcodes = $this->app_qrcodes_model->resgatar_qrcodes($id)->result();

        $nome_arquivo = "qrcodes_".time().".xls";
        $caminho_arquivo = "./upload/qrcodes/".$nome_arquivo;
        $this->load->library('PHPExcel');
        $this->load->helper('download');

        $planilha = $this->phpexcel;

        $planilha->setActiveSheetIndex(0)->setCellValue('A1', 'USUÁRIO');
        $planilha->setActiveSheetIndex(0)->setCellValue('B1', 'OBSERVAÇÃO');
        $planilha->setActiveSheetIndex(0)->setCellValue('C1', 'TIPO');
        $planilha->setActiveSheetIndex(0)->setCellValue('D1', 'QTD');

        $planilha->setActiveSheetIndex(0)->setCellValue('A2', $qrcode_info->first_name." ".$qrcode_info->last_name);
        $planilha->setActiveSheetIndex(0)->setCellValue('B2', $qrcode_info->observacao);
        $planilha->setActiveSheetIndex(0)->setCellValue('C2', ($qrcode_info->kit == "S") ? "KIT" : $qrcode_info->titulo);
        $planilha->setActiveSheetIndex(0)->setCellValue('D2', $qrcode_info->qtd);

        $planilha->setActiveSheetIndex(0)->setCellValue('A4', 'ID');
        $planilha->setActiveSheetIndex(0)->setCellValue('B4', 'CÓDIGO');
        $planilha->setActiveSheetIndex(0)->setCellValue('C4', 'LIVRO');
        $planilha->setActiveSheetIndex(0)->setCellValue('D4', 'DATA');
        $planilha->setActiveSheetIndex(0)->setCellValue('E4', 'ALUNO');
        $planilha->setActiveSheetIndex(0)->setCellValue('F4', 'EMPRESA');
        $contador = 5;
        foreach ($qrcodes as $qr) {
            $planilha->setActiveSheetIndex(0)->setCellValue("A".$contador, $qr->id);
            $planilha->setActiveSheetIndex(0)->setCellValue("B".$contador, $qr->codigo);
            $planilha->setActiveSheetIndex(0)->setCellValue("C".$contador, $qr->titulo);
            $planilha->setActiveSheetIndex(0)->setCellValue("D".$contador, $qr->data);
            $planilha->setActiveSheetIndex(0)->setCellValue("E".$contador, $qr->first_name." ".$qr->last_name);
            $planilha->setActiveSheetIndex(0)->setCellValue("F".$contador, $qr->nome_empresa);
            $contador++;
        }

        $planilha->getActiveSheet()->setTitle("QRCodes");

        $objgravar = PHPExcel_IOFactory::createWriter($planilha, 'Excel2007');
        $objgravar->save($caminho_arquivo);

        $data = file_get_contents(base_url($caminho_arquivo));
        unlink(FCPATH."upload/qrcodes/".$nome_arquivo);
        force_download($nome_arquivo, $data);
    }

    public function gerar_pdf(){
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_qrcodes", "gerar_pdf"))
        {
            redirect('auth', 'refresh');
        }

        $cod_qrcode_ini = !empty($_POST['inputQRCodeIdIni']) && $_POST['inputQRCodeIdIni'] > 0 ? (int)$_POST['inputQRCodeIdIni'] : NULL;
        $cod_qrcode_end = !empty($_POST['inputQRCodeIdEnd']) && $_POST['inputQRCodeIdEnd'] > 0 ? (int)$_POST['inputQRCodeIdEnd'] : NULL;
        $qrcode_info_id = !empty($_POST['inputQRCodeinfoId']) ? (int)base64_decode($_POST['inputQRCodeinfoId']) : NULL;
        $todos = isset($_POST['inputQRCodeIdTodos']) ? 'S' : 'N';
        $por_pagina = isset($_POST['inputQRCodePorPagina']) ? 'S' : 'N';

        $data = array(
            'qrcod_info' => $qrcode_info_id,
            'qrcode_id_ini' => $cod_qrcode_ini,
            'qrcode_id_end' => $cod_qrcode_end,
            'todos' => $todos
        );

        $this->load->library("tcpdf");

        $count = 1;
        $count_total = 1;
        $nova_pagina = false;
        $nova_pagina_false = false;

        //set style for barcode
        $style = array(
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );

        $qrcodes_data = $this->app_qrcodes_model->imprimir_qrcodes($data);
        if($qrcodes_data->num_rows() > 0) {
            $i = 0;
            /*FOREACH para montar a ordem de apresentação dos qrcodes resgatados*/
            foreach ($qrcodes_data->result_array() as $key => $value) :

                if($i == 0)
                    $pdf = $this->gerar_inicio_pdf();

                if($por_pagina == "S") {
                    if($value['kit'] == 'S'):
                        if($count == 3):
                            $pdf->WriteHTMLCell(50, '', 140, '', $value['codigo'].' - '.$value['titulo'], 0, 0, 0, true, 'C', false);
                            $pdf->write2DBarcode($value['codigo'], 'QRCODE,H', 140, '', 50, 50, $style, 'N');

                            if($count_total % 15 == 0):
                                $nova_pagina = true;
                                $count_total = 0;
                            endif;
                        elseif($count == 2):     
                            $pdf->write2DBarcode($value['codigo'], 'QRCODE,H', 80, '', 50, 50, $style, '');
                            $pdf->WriteHTMLCell(50, '', 80, '', $value['codigo'].' - '.$value['titulo'], 0, 0, 0, true, 'C', false);
                        else:
                            $pdf->Ln(10);
                            $pdf->write2DBarcode($value['codigo'], 'QRCODE,H', 20, '', 50, 50, $style, '');
                            $pdf->WriteHTMLCell(50, '', 20, '', $value['codigo'].' - '.$value['titulo'], 0, 0, 0, true, 'C', false);
                        endif;
                    else:
                        $pdf->Ln(10);
                        $pdf->write2DBarcode($value['codigo'], 'QRCODE,H', 20, '', 50, 50, $style, '');
                        $pdf->WriteHTMLCell(50, '', 20, '', $value['codigo'].' - '.$value['titulo'], 0, 0, 0, true, 'C', false);
                    endif;

                    if(($value['kit'] == 'N') || ($value['kit'] == 'S' && $count == 3)) {
                        $pdf->lastPage();
                        $pdf->Output(FCPATH.'upload/qrcodes/QRCodes_'.$i.'.pdf', 'F');
                        $pdf = $this->gerar_inicio_pdf();
                        $retorno_path[] = FCPATH.'upload/qrcodes/QRCodes_'.$i.'.pdf';
                        if($count == 3)
                            $count = 0;
                    }
                }
            
                if($por_pagina == "N") {

                    if($nova_pagina == true):
                        $pdf->AddPage();
                        /*if($value['kit'] == 'S'):
                            $pdf->Ln(10);
                        else:
                            if($qrcodes_data->num_rows() <= 3):
                                $pdf->Ln(110);
                            elseif($qrcodes_data->num_rows() <= 6):
                                $pdf->Ln(70);
                            elseif($qrcodes_data->num_rows() <= 9):
                                $pdf->Ln(48);
                            elseif($qrcodes_data->num_rows() <= 12):
                                $pdf->Ln(24);
                            elseif($qrcodes_data->num_rows() <= 15):
                                $pdf->Ln(10);   
                            endif;
                        endif;*/
                        $pdf->Ln(10);
                        $nova_pagina = false;
                    else:
                        if($nova_pagina_false == false):
                            /*if($value['kit'] == 'S'):
                                $pdf->Ln(10);
                            else:
                                if($qrcodes_data->num_rows() <= 3):
                                    $pdf->Ln(110);
                                elseif($qrcodes_data->num_rows() <= 6):
                                    $pdf->Ln(70);
                                elseif($qrcodes_data->num_rows() <= 9):
                                    $pdf->Ln(48);
                                elseif($qrcodes_data->num_rows() <= 12):
                                    $pdf->Ln(24);
                                elseif($qrcodes_data->num_rows() <= 15):
                                    $pdf->Ln(10);   
                                endif;
                            endif;*/
                            $pdf->Ln(10);
                            $nova_pagina_false = true;
                        endif;
                    endif;
                    
                    
                    if($count == 3):
                        //QRCODE,H : QR-CODE Best error correction
                        $pdf->WriteHTMLCell(50, '', 140, '', $value['codigo'].' - '.$value['titulo'], 0, 0, 0, true, 'C', false);
                        $pdf->write2DBarcode($value['codigo'], 'QRCODE,H', 140, '', 50, 50, $style, 'N');
                        //$pdf->Ln(6);
                        $count = 0;       

                        if($count_total % 15 == 0):
                            $nova_pagina = true;
                            $count_total = 0;
                        endif;
                    elseif($count == 2):     
                        //QRCODE,H : QR-CODE Best error correction
                        $pdf->write2DBarcode($value['codigo'], 'QRCODE,H', 80, '', 50, 50, $style, '');
                        $pdf->WriteHTMLCell(50, '', 80, '', $value['codigo'].' - '.$value['titulo'], 0, 0, 0, true, 'C', false);
                    else:               
                        //QRCODE,H : QR-CODE Best error correction
                        $pdf->write2DBarcode($value['codigo'], 'QRCODE,H', 20, '', 50, 50, $style, '');
                        $pdf->WriteHTMLCell(50, '', 20, '', $value['codigo'].' - '.$value['titulo'], 0, 0, 0, true, 'C', false);
                    endif;
                }

                $i++;
                if($qrcodes_data->num_rows() == $i && $por_pagina == "N") {
                    $pdf->lastPage();
                    $pdf->Output('QRCodes.pdf', 'D');
                }
                
                $count++;
                $count_total++;
            endforeach;

            if(@count($retorno_path) > 0) {
                $this->load->library('zip');
                foreach ($retorno_path as $path) {
                   $this->zip->read_file($path);
                   unlink($path);
                }
                $this->zip->download('QRCodes.zip');
            }
        } else {
            $pdf = $this->gerar_inicio_pdf();
            $pdf->WriteHTMLCell(180, '', '', '', 'Não existem qrcodes gerados dentro do intervalo informado: '.$cod_qrcode_ini.' à '.$cod_qrcode_end.'!', 0, 1, 0, true, 'L', false);
            $pdf->lastPage();
            $pdf->Output(FCPATH.'upload/qrcodes/QRCodes.pdf', 'F');
        }
    }

    private function gerar_inicio_pdf() {
        $pdf = new Mytcpdf('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        //Informações do documento
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Saxweb');
        $pdf->SetTitle('QRCode');
        $pdf->SetSubject('Lista de QRCodes');
        $pdf->SetKeywords('qrcode, livro');

        //set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 5, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 5);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


        // ---------------------------------------------------------
        //Add a page
        //This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        //get current vertical position
        $y = $pdf->getY();


        $pdf->SetFont('helvetica', 'B', 10, '', true);
        //$pdf->WriteHTMLCell(180, '', '', $y, 'LISTA QRCODES', 0, 1, 0, true, 'C', false);

        $pdf->SetFont('helvetica', 'B', 7, '', true);

        return $pdf;
    }

    /*public function gerar_pdf() {
    	if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_qrcodes", "gerar_pdf"))
		{
			redirect('auth', 'refresh');
		}

    	$this->load->library('ciqrcode');
    	$cod_qrcode_ini = !empty($_POST['inputQRCodeIdIni']) && $_POST['inputQRCodeIdIni'] > 0 ? (int)$_POST['inputQRCodeIdIni'] : NULL;
		$cod_qrcode_end = !empty($_POST['inputQRCodeIdEnd']) && $_POST['inputQRCodeIdEnd'] > 0 ? (int)$_POST['inputQRCodeIdEnd'] : NULL;
		$qrcode_info_id = !empty($_POST['inputQRCodeinfoId']) ? (int)base64_decode($_POST['inputQRCodeinfoId']) : NULL;
		$todos = isset($_POST['inputQRCodeIdTodos']) ? 'S' : 'N';

		$data = array(
		    'qrcod_info' => $qrcode_info_id,
		    'qrcode_id_ini' => $cod_qrcode_ini,
		    'qrcode_id_end' => $cod_qrcode_end,
		    'todos' => $todos
		);

		$params['level'] = 'L';
		$params['size'] = 5;
		$qrcodes_data = $this->app_qrcodes_model->imprimir_qrcodes($data)->result();
		foreach ($qrcodes_data as $key => $value) {
			$params['data'] = $value->codigo;
			$params['savename'] = FCPATH.'upload/qrcodes/'.$value->id.'.png';
			$this->ciqrcode->generate($params);
			$qrcodes_data[$key]->qrcode = base_url()."upload/qrcodes/".$value->id.".png";
			$to_unlink[] = FCPATH.'upload/qrcodes/'.$value->id.'.png';
		}
		$this->data['qrcodes_data'] = $qrcodes_data;
		$this->load->library('pdf');

        $this->pdf->load_view('admin/app/qrcodes/imprimir_qrcodes', "qrcodes", $this->data);

        foreach ($to_unlink as $value) {
        	unlink($value);
        }
    }

    public function gerar_qrcode($id) {
    	if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_qrcodes", "gerar_qrcode"))
		{
			redirect('auth', 'refresh');
		}

    	$this->load->library('ciqrcode');
    	
		$params['level'] = 'H';
		$params['size'] = 10;
		$qrcode_data = $this->app_qrcodes_model->get_qrcode($id);
		$params['data'] = $qrcode_data->codigo;
		$params['savename'] = FCPATH.'upload/qrcodes/'.$qrcode_data->id.'.png';
		$this->ciqrcode->generate($params);
		$qrcode_data->qrcode = base_url()."upload/qrcodes/".$qrcode_data->id.".png";
		$this->data['qrcode_data'] = $qrcode_data;
		$this->load->library('pdf');

        $this->pdf->load_view('admin/app/qrcodes/imprimir_qrcode', "qrcode-".$qrcode_data->codigo, $this->data);
        unlink(FCPATH.'upload/qrcodes/'.$qrcode_data->id.'.png');
    }*/

    public function gerar_qrcode($id){
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_qrcodes", "gerar_qrcode"))
        {
            redirect('auth', 'refresh');
        }

        $qrcode_data = $this->app_qrcodes_model->get_qrcode($id);

        $this->load->library("tcpdf");
        $pdf = new Mytcpdf('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        //Informações do documento
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Saxweb');
        $pdf->SetTitle('QRCode');
        $pdf->SetSubject('Lista de QRCodes');
        $pdf->SetKeywords('qrcode, livro');

        //set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 5, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 5);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


        // ---------------------------------------------------------
        //Add a page
        //This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        //get current vertical position
        $y = $pdf->getY();

        //set style for barcode
        $style = array(
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255)
        );

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('helvetica', 'B', 10, '', true);
        $pdf->Ln(80);

        $left_column = 'GERADO EM: '.$qrcode_data->data."<br>".'ID: '.$qrcode_data->id;
        $right_column = 'LIVRO: '.$qrcode_data->titulo."<br>".'GERADO POR: '.$qrcode_data->first_name." ".$qrcode_data->last_name;
        $pdf->writeHTMLCell(100, '', '', '', $left_column, 0, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(100, '', '', '', $right_column, 0, 1, 1, true, 'L', true);
        $pdf->Ln(4);
        $pdf->write2DBarcode($qrcode_data->codigo, 'QRCODE,H', 80, '', 50, 50, $style, 'N');
        $pdf->Ln(4);
        $pdf->writeHTMLCell(200, '', 85, '', $qrcode_data->codigo, 0, 1, 1, true, 'L', true);

        

        //reset pointer to the last page
        $pdf->lastPage();

        // ---------------------------------------------------------
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        $pdf->Output('qrcode-'.$qrcode_data->codigo.'.pdf', 'D');
    }
}
