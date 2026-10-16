<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Iugu extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/iugu');
        $this->load->model('iugu_model');
        $this->load->model('auditorias_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');
        $this->load->library('iugu_library');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_iugu'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_iugu'), 'iugu');
    }

    public function gerar_segunda_via_entrada()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "gerar_segunda_via_entrada"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $this->form_validation->set_rules('expiracao', 'lang:expiracao', 'required|numeric');

            if ($this->form_validation->run() == TRUE) {

                $contrato = $this->iugu_model->get_contratos(array('select'=>'cad_orca.user_id, cad_orca.filial', 'where'=>'cad_orca.id = '.$_POST['contrato_id'], 'order_by'=>array('cad_orca.id', 'desc')));
                $unidade = $this->iugu_model->get_unidade($contrato->row()->filial);

                if($unidade->row()->iugu_status == "N") {
                    $this->session->set_flashdata('message', show_alert_error("Dados de configuração da Iugu inativo."));
                    redirect('contratos', 'refresh');
                }

                $iugu = new Iugu_library();
                $iugu->setToken($unidade->row()->iugu_token);


                $user = $this->ion_auth->user($contrato->row()->user_id);
                $pessoa = $this->ion_auth->get_pessoa($contrato->row()->user_id);
                $responsavel = $this->iugu_model->get_responsavel($contrato->row()->user_id);

                $dados["has_dependente"] = false;
                if($responsavel->num_rows() > 0) {
                    $iugu_user = $responsavel->row()->user_id;
                    $dados["has_dependente"] = true;
                    $dados["user_pagador"] = $this->ion_auth->user($responsavel->row()->user_id)->row();
                    $dados["pessoa_pagador"] = $this->ion_auth->get_pessoa($responsavel->row()->user_id)->row();
                } else {
                    $iugu_user = $contrato->row()->user_id;
                }

                $invoice = $this->iugu_model->get_iugu_invoice($_POST['contrato_id'], "M");
                //$entrada = $this->contratos_model->get_entrada_valor($_POST['contrato_id']);
                $customer = $this->iugu_model->get_iugu_user($iugu_user);

                $dados["user"] = $user->row();
                $dados["pessoa"] = $pessoa->row();
                $dados["cad_orca_id"] = $_POST['contrato_id'];
                $dados["invoice"] = $invoice;
                //$dados["valor_entrada"] = $entrada->row()->VALOR;
                $dados["customer_id"] = $customer->iugu_id;
                //$dados["expiracao"] = $_POST['expiracao'];
				
                $dados["expires_at"] = date('Y-m-d', strtotime("+".$_POST['expiracao']." days"));
				
                $retorno = $iugu->gerar_segunda_via($dados);

                if($retorno["erro"] == true) {
                    $this->session->set_flashdata('message', show_alert_error($retorno["msg"]));
                } else {
                    $this->ion_auth_model->historico_create(array("LIG_ID_CLIENTE" => $iugu_user, "LIG_ID_USUARIO" => $this->session->userdata('user_id'), "STATUS" => 6, "MENSAGEM" => "Gerada segunda via da fatura ".$invoice, "DATA_REGISTRO" => date('Y-m-d'), "HORA_REGISTRO" => date("H:i:s")));
                    $this->iugu_model->update_invoice($_POST['contrato_id'], array("iugu_id" => $retorno['data']['id'], "linha_digitavel" => $retorno['data']["bank_slip"]["digitable_line"], "status" => "pending"), array('cad_orca_id' => $_POST['contrato_id'], 'tipo' => 'M'));
                    $this->session->set_flashdata('message', show_alert_success("Gerada a 2a via da entrada do contrato <b>#".$_POST['contrato_id']."</b> na Iugu.<br>Para imprimir <a href='".$retorno['data']['secure_url']."' target='_blank'>clique aqui</a>"));
                }
                //exit("<pre>".print_r($retorno,true)."</pre>");
            }
            else {
                $this->session->set_flashdata('message',show_alert_error(validation_errors()));
            }

            redirect('contratos', 'refresh');
        }
    }

    public function gerar_segunda_via_parcela()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "gerar_segunda_via_parcela"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $this->form_validation->set_rules('expiracao', 'lang:expiracao', 'required|numeric');

            $parcela = $this->iugu_model->get_parcela($_POST['parcela_id']);

            $contrato = $this->iugu_model->get_contratos(array('select'=>'cad_orca.id, cad_orca.user_id, cad_orca.filial', 'where'=>'cad_orca.id = '.$parcela->row()->orc_id, 'order_by'=>array('cad_orca.id', 'desc')));

            if ($this->form_validation->run() == TRUE) {

                $unidade = $this->iugu_model->get_unidade($contrato->row()->filial);

                if($unidade->row()->iugu_status == "N") {
                    $this->session->set_flashdata('message', show_alert_error("Dados de configuração da Iugu inativo."));
                    redirect('contratos/ver_parcelas/'.$contrato->row()->id, 'refresh');
                }

                if($parcela == null) {
                    $this->session->set_flashdata('message', show_alert_error("Parcela não encontrada."));
                    redirect('contratos/ver_parcelas/'.$contrato->row()->id, 'refresh');
                }

                $iugu = new Iugu_library();
                $iugu->setToken($unidade->row()->iugu_token);

                $user = $this->ion_auth->user($contrato->row()->user_id);
                $pessoa = $this->ion_auth->get_pessoa($contrato->row()->user_id);
                $responsavel = $this->iugu_model->get_responsavel($contrato->row()->user_id);

                $dados["has_dependente"] = false;
                if($responsavel->num_rows() > 0) {
                    $iugu_user = $responsavel->row()->user_id;
                    $dados["has_dependente"] = true;
                    $dados["user_pagador"] = $this->ion_auth->user($responsavel->row()->user_id)->row();
                    $dados["pessoa_pagador"] = $this->ion_auth->get_pessoa($responsavel->row()->user_id)->row();
                } else {
                    $iugu_user = $contrato->row()->user_id;
                }

                $invoice = $this->iugu_model->get_iugu_invoice($parcela->row()->orc_id, "P");
                //$entrada = $this->contratos_model->get_entrada_valor($_POST['contrato_id']);
                $customer = $this->iugu_model->get_iugu_user($iugu_user);

                $dados["user"] = $user->row();
                $dados["pessoa"] = $pessoa->row();
                $dados["cad_orca_id"] = $parcela->row()->orc_id;
                $dados["invoice"] = $invoice;
                $dados["assinatura_id"] = $this->iugu_model->get_iugu_assinatura($contrato->row()->id)->row()->iugu_id;
                
                //$dados["valor_entrada"] = $entrada->row()->VALOR;
                $dados["customer_id"] = $customer->iugu_id;

                $retorno_fatura = $iugu->buscar_fatura($parcela->row()->iugu_id);
                $dados['itens'] = $retorno_fatura["data"]["items"];
                $dados['payable_with'] = $retorno_fatura["data"]["payable_with"];

                $dados["expires_at"] = date('Y-m-d', strtotime("+".$_POST['expiracao']." days"));

                //$retorno = $iugu->criar_fatura($dados, "P_".$parcela->row()->parcela);
                $retorno = $iugu->gerar_segunda_via($dados);

                if($retorno["erro"] == true) {
                    $this->session->set_flashdata('message', show_alert_error($retorno["msg"]));
                } else {
                    //$retorno_alterar_assinatura = $iugu->alterar_assinatura(array("expires_at" => $dados["expires_at"], "suspended" => 'false'), $dados["assinatura_id"]);
                    $this->ion_auth_model->historico_create(array("LIG_ID_CLIENTE" => $iugu_user, "LIG_ID_USUARIO" => $this->session->userdata('user_id'), "STATUS" => 6, "MENSAGEM" => "Gerada segunda via da fatura ".$invoice->iugu_id, "DATA_REGISTRO" => date('Y-m-d'), "HORA_REGISTRO" => date("H:i:s")));
                    //$this->iugu_model->update_invoice($parcela->row()->orc_id, array("iugu_id" => $retorno['data']['id'], "linha_digitavel" => $retorno['data']["bank_slip"]["digitable_line"], "status" => "pending"), array('cad_orca_id' => $parcela->row()->orc_id, 'tipo' => 'P', 'ct_receb_id' => $parcela->row()->ct_receb_id));
                    $this->iugu_model->create_invoice(array("cad_orca_id" => $parcela->row()->orc_id, "iugu_id" => $retorno['data']['id'], "linha_digitavel" => @$retorno['data']["bank_slip"]["digitable_line"], "status" => "pending", "tipo" => "P", "ct_receb_id" => $parcela->row()->ct_receb_id));
                    $this->session->set_flashdata('message', show_alert_success("Gerada a 2a via da parcela ".$parcela->row()->parcela." do contrato <b>#".$parcela->row()->orc_id."</b> na Iugu.<br>Para imprimir <a href='".$retorno['data']['secure_url']."' target='_blank'>clique aqui</a>"));
                    
                }
                //exit("<pre>".print_r($retorno,true)."</pre>");
            }
            else {
                $this->session->set_flashdata('message',show_alert_error(validation_errors()));
            }

            redirect('contratos/ver_parcelas/'.$contrato->row()->id, 'refresh');
        }
    }

    public function historico_parcela()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "historico_parcela"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $post = $_POST;
            $retorno = array();
            $parcela = $this->iugu_model->get_parcela($post["id"]);
            if($parcela->num_rows() > 0)
            {
                $contrato = $this->iugu_model->get_contratos(array('select'=>'cad_orca.user_id, cad_orca.filial', 'where'=>'cad_orca.id = '.$parcela->row()->orc_id, 'order_by'=>array('cad_orca.id', 'desc')));
                $unidade = $this->iugu_model->get_unidade($contrato->row()->filial);

                if($unidade->row()->iugu_status == "N") {
                    // $this->session->set_flashdata('message', show_alert_error("Dados de configuração da Iugu inativo."));
                    // redirect('contratos/ver_parcelas/'.$contrato->row()->id, 'refresh');
                    $retorno["erro"] = true;
                    $retorno["data"] = "<p>Dados de configuração da Iugu inativo.</p>";
                }

                $iugu = new Iugu_library();
                $iugu->setToken($unidade->row()->iugu_token);

                $retorno = $iugu->buscar_fatura($parcela->row()->iugu_id);

                if($retorno["erro"] == true) {
                    // $this->session->set_flashdata('message', show_alert_error($retorno["msg"]));
                    // redirect('contratos/ver_parcelas/'.$contrato->row()->id, 'refresh');
                    $retorno["erro"] = true;
                    $retorno["data"] = $retorno["msg"];
                } else {
                    $html = "";
                    if(count($retorno["data"]["logs"]) > 0) {
                        $html .= "<table class='table'>";
                        $html .= "<tr>";
                        $html .= "<th>Descrição</th>";
                        $html .= "<th>Observação</th>";
                        $html .= "<th>Data</th>";
                        $html .= "</tr>";
                        foreach ($retorno["data"]["logs"] as $log) {
                            $html .= "<tr>";
                            $html .= "<td>".$log["description"]."</td>";
                            $html .= "<td>".$log["notes"]."</td>";
                            $html .= "<td>".$log["created_at"]."</td>";
                            $html .= "</tr>";
                        }
                        $html .= "</table>";
                    }
                }

                $retorno["erro"] = false;
                $retorno["data"] = $html;
            } else {
                // $contrato = $this->iugu_model->get_contrato_by_parcela($post["id"]);
                // $this->session->set_flashdata('message', show_alert_error("Parcela não encontrada."));
                // redirect('contratos/ver_parcelas/'.$contrato->row()->id, 'refresh');
                $retorno["erro"] = true;
                $retorno["data"] = "<p>Parcela não encontrada.</p>";
            }
            exit(json_encode($retorno));
        }
    }

    public function cartao_recorrencia($contrato_id, $user_id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "cartao_recorrencia"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $grupos_permissoes = $this->ion_auth->get_users_groups_permissoes();
            $usuario_limitado = false;
            if($grupos_permissoes->num_rows() > 0) {
                foreach ($grupos_permissoes->result() as $value) {
                    $permissoes = unserialize($value->permissoes);
            
                    if(count($permissoes) > 0) {
                        foreach ($permissoes as $_k => $_p) {
                            if($_k == "iugu" && in_array("cartao_recorrencia", $_p)) {
                                if($value->limited == 1) {
                                    $usuario_limitado = true;
                                }
                            }
                        }
                    }
                }
            }

            if($user_id != $this->session->userdata('user_id') && $usuario_limitado == true) {
                redirect('auth/login', 'refresh');
            }

            $contrato = $this->iugu_model->get_contrato($contrato_id);
            $unidade = $this->iugu_model->get_unidade($contrato->row()->filial);

            $iugu = new Iugu_library();
            $iugu->setToken($unidade->row()->iugu_token);

            if($_POST) {
                $retorno = $iugu->criar_forma_pagamento($_POST["customer_id"], $_POST["token"]);

                if($retorno["erro"] == true) {
                    $this->session->set_flashdata('message', show_alert_error($retorno["msg"]));
                } else {
                    $this->iugu_model->update_cartao_recorrente($_POST["customer_id"], $retorno['data']['id']);

                    if($this->iugu_model->has_iugu_user_cc($_POST["customer_id"])) {
                        $this->iugu_model->remove_iugu_user_cc_pendent(array("cad_orca_id" => $contrato_id, "iugu_customer_id" => $_POST["customer_id"]));
                        $this->session->unset_userdata('notifications_iugu');
                        $this->session->unset_userdata('notifications_iugu_count');
                    }

                    $this->auditorias_model->create("ead_users", "cartao_recorrencia", "Alterado cartão recorrente padrão na Iugu do usuário id: ".$user_id.", contrato: ".$contrato_id.".");
                    $this->session->set_flashdata('message', show_alert_success("Alterado cartão recorrente padrão do usuário."));
                }

                redirect('iugu/cartao_recorrencia/'.$contrato_id.'/'.$user_id, 'refresh');
            }

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');
            $this->data['user_id'] = $user_id;
            $this->data['user'] = $this->ion_auth->user($user_id);


            if($contrato->num_rows() > 0) {
                if($this->iugu_model->has_unidade($contrato->row()->filial)) {
                    $this->data['unidade'] = $unidade->row();
                    $this->data['customer_id'] = $this->iugu_model->get_iugu_user($user_id)->iugu_id;

                    $iugu_user_cc = $this->iugu_model->get_iugu_user_cc($this->data['customer_id']);

                    if($iugu_user_cc->num_rows() > 0) {
                        $retorno = $iugu->buscar_forma_pagamento($this->data['customer_id'], $iugu_user_cc->row()->iugu_id);
                        
                        if(@$retorno["erro"] == true) {

                        } else {
                            $this->data['dados_cartao'] = array(
                                'brand' => $retorno["data"]["data"]["brand"],
                                'holder_name' => $retorno["data"]["data"]["holder_name"],
                                'display_number' => $retorno["data"]["data"]["display_number"],
                                'bin' => $retorno["data"]["data"]["bin"],
                                'month' => $retorno["data"]["data"]["month"],
                                'year' => $retorno["data"]["data"]["year"]
                            );
                        }
                    }

                    /* Load Template */
                    $this->template->admin_render('admin/iugu/cartao_recorrencia', $this->data);
                } else {
                    redirect('users/profile/'.$user_id, 'refresh');
                }
            } else {
                redirect('users/profile/'.$user_id, 'refresh');
            }
        }
    }

    public function alterar_assinatura($contrato_id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "alterar_assinatura"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $contrato = $this->iugu_model->get_contrato($contrato_id);
            $unidade = $this->iugu_model->get_unidade($contrato->row()->filial);
            $parametros = $this->iugu_model->get_parametros($contrato->row()->filial);

            if($parametros->num_rows() == 0) { 
                $this->session->set_flashdata('message', show_alert_error("Parâmetros da filial não configurados, entre em contato com o administrador."));
                redirect('/', 'refresh');
            } else {
                if($parametros->row()->LIG_STATUS_CONTRATO_01 == $contrato->row()->status || $parametros->row()->LIG_ID_ANULADO == $contrato->row()->status || $parametros->row()->LIG_STATUS_CONTRATO_06 == $contrato->row()->status) {
                    $this->session->set_flashdata('message', show_alert_error("Não é possível alterar contrato com status ".$this->iugu_model->get_status($contrato->row()->status)->row()->descricao."."));
                    redirect('/', 'refresh');
                }
            }

            $this->load->library('iugu');

            $iugu = new Iugu_library();
            $iugu->setToken($unidade->row()->iugu_token);


            $assinatura_modelo = $this->iugu_model->get_iugu_assinatura($contrato_id);

            if($_POST) {
                if($assinatura_modelo->num_rows() == 1) {

                    $condicao = $this->iugu_model->get_condicao($_POST["plano"]);
                    if($condicao->num_rows() > 0) {
                        $_POST["plano"] = $condicao->row()->plano_iugu;
                        $_POST["condicao_desconto"] = $condicao->row()->VALOR_DESCONTO_PONTUALIDADE;
                        $retorno = $iugu->alterar_assinatura($_POST, $this->iugu_model->get_iugu_assinatura($contrato_id)->row()->iugu_id);

                        if($retorno["erro"] == true) {
                            $this->session->set_flashdata('message', show_alert_error($retorno["msg"]));
                        } else {
                            //alterar condicoes_orcamento/par_orca/ct_receb
                            $this->iugu_model->update_condicao($condicao->row()->ID, $contrato_id, $retorno["data"], $_POST);
                            $this->iugu_model->update_orcamento($contrato_id, array("ultima_atualizacao" => date("Y-m-d H:i:s"), "qnt_parcelas" => $condicao->row()->NUM_PARCELAS));

                            if($this->ion_auth->is_recorrencia($contrato_id, $contrato->row()->user_id) && $_POST["payable_with"] == "credit_card") {
                                if(!$this->iugu_model->has_iugu_user_cc($retorno["data"]["customer_id"])) {
                                    $this->iugu_model->insert_iugu_user_cc_pendent(array("cad_orca_id" => $contrato_id, "iugu_customer_id" => $retorno["data"]["customer_id"]));
                                    $this->session->set_flashdata('message', show_alert_warning("Adicione um cartão para recorrência."));
                                    redirect('iugu/cartao_recorrencia/'.$contrato_id.'/'.$contrato->row()->user_id, 'refresh');
                                }
                            }

                            $this->auditorias_model->create("iugu", "alterar_assinatura", "Alterada assinatura na Iugu do contrato: ".$contrato_id.".");
                            $this->session->set_flashdata('message', show_alert_success("Alterada assinatura na Iugu."));
                        }
                    } else {
                        $this->session->set_flashdata('message', show_alert_error("Condição inexistente."));
                    }
                } else {

                    $this->form_validation->set_rules('assinatura_id_input', 'lang:assinatura_id_input', 'required|trim');

                    if ($this->form_validation->run() == TRUE) {

                        if($this->iugu_model->has_subscriptions($this->input->post('assinatura_id_input'))) {
                            $this->session->set_flashdata('message',show_alert_error("Assinatura já existente."));
                        } else {
                            $retorno_assinatura = $iugu->buscar_assinatura($this->input->post('assinatura_id_input'));

                            if(@$retorno_assinatura["erro"] == true) {
                                $this->session->set_flashdata('message', show_alert_error("Assinatura não encontrada na iugu."));
                                redirect('iugu/alterar_assinatura/'.$contrato_id, 'refresh');
                            }

                            if($this->iugu_model->create_subscriptions(array('cad_orca_id' => $contrato_id, 'status' => '1', 'iugu_id' => $this->input->post('assinatura_id_input')))) {
                                $this->session->set_flashdata('message',show_alert_success("Assinatura criada com sucesso."));
                            } else {
                                $this->session->set_flashdata('message',show_alert_error("Houve um erro ao salvar a assinatura."));
                            }
                        }
                    } else {
                        $this->session->set_flashdata('message',show_alert_error(validation_errors()));
                    }
                }

                redirect('iugu/alterar_assinatura/'.$contrato_id, 'refresh');
            }



            if($assinatura_modelo->num_rows() == 1) {
            
                /* Breadcrumbs */
                $this->data['breadcrumb'] = $this->breadcrumbs->show();
                $this->data['message'] = $this->session->flashdata('message');

                if($contrato->num_rows() > 0) {
                    if($this->iugu_model->has_unidade($contrato->row()->filial)) {
                        $this->data['unidade'] = $unidade->row();

                        $this->data['assinatura_id'] = $assinatura_modelo->row()->iugu_id;
                        $this->data['contrato_id'] = $contrato_id;
                        $this->data['can_alterar_id_assinatura'] = $this->ion_auth->has_permissao("iugu", "alterar_id_assinatura");

                        $retorno_assinatura = $iugu->buscar_assinatura($this->data['assinatura_id']);

                        if(@$retorno_assinatura["erro"] == true) {
                            $this->session->set_flashdata('message', show_alert_error("Assinatura não encontrada na iugu."));
                            redirect('/', 'refresh');
                        }

                        $retorno_planos = $iugu->listar_planos();
                        $planos_iugu_arr = array();
                        if($retorno_planos["erro"] == false) {
                            foreach ($retorno_planos["data"]["items"] as $item) {
                                $planos_iugu_arr[$item['identifier']] = $item['name'];
                            }
                        }

                        $condicao = $this->iugu_model->get_condicao_atual($contrato_id);

                        $planos_arr = array("" => "Selecionar");
                        $planos_model = $this->iugu_model->get_planos_habilitados($contrato_id);
                        $has_plan = false;
                        if($planos_model->num_rows() > 0) {
                            foreach ($planos_model->result() as $plano) {
                                if(array_key_exists($plano->plano_iugu, $planos_iugu_arr)) {
                                    $planos_arr[$plano->ID] = $plano->DESCRICAO." [".$planos_iugu_arr[$plano->plano_iugu]."]";
                                    if($plano->ID == $condicao->row()->TIPO_CONDICAO_ID)
                                        $has_plan = true;
                                }
                            }
                        }

                        $this->data['expires_at'] = $retorno_assinatura["data"]["expires_at"];
                        $this->data['subitems'] = $retorno_assinatura["data"]["subitems"];
                        
                        if($has_plan == false) {
                            // $this->session->set_flashdata('message', show_alert_error('Condição "'.$retorno_assinatura["data"]["plan_name"].'" do contrato não configurada, acesse ver condição para personalizar ou entre em contato com o administrador.'));
                            $this->session->set_flashdata('message', show_alert_error('Condição "'.$condicao->row()->DESCRICAO.'" do contrato não configurada, acesse ver condição para personalizar ou entre em contato com o administrador.'));
                            redirect('/', 'refresh');
                        }

                        $this->data['payable_with'] = array(
                            'name'  => 'payable_with',
                            'id'    => 'payable_with',
                            'class' => 'form-control',
                            'options' => array("credit_card" => "Cartão de Crédito", "bank_slip" => "Boleto"),
                            'selected' => $retorno_assinatura["data"]["payable_with"]
                        );

                        $this->data['plano'] = array(
                            'name'  => 'plano',
                            'id'    => 'plano',
                            'class' => 'form-control',
                            'options' => $planos_arr,
                            'selected' => $condicao->row()->TIPO_CONDICAO_ID
                        );

                        /* Load Template */
                        $this->template->admin_render('admin/iugu/alterar_assinatura', $this->data);
                        
                    } else {
                        redirect('/', 'refresh');
                    }
                } else {
                    $this->session->set_flashdata('message', show_alert_error("Contrato inexistente."));
                    redirect('/', 'refresh');
                }
            } else {
                $this->data['breadcrumb'] = $this->breadcrumbs->show();
                $this->data['message'] = $this->session->flashdata('message');

                $this->data['assinatura_id_input'] = array(
                    'name'  => 'assinatura_id_input',
                    'id'    => 'assinatura_id_input',
                    'class' => 'form-control'
                );

                /* Load Template */
                $this->template->admin_render('admin/iugu/alterar_assinatura', $this->data);               
            }
        }
    }
	
    public function suspender_assinatura($contrato_id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "suspender_assinatura"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            if($_POST) {
                if($_POST["motivo"] == "" || $_POST["status"] == "") {
                    $this->session->set_flashdata('message', show_alert_error("Selecione um status e preencha o motivo."));
                    redirect('/', 'refresh');
                }

                $contrato = $this->iugu_model->get_contrato($contrato_id);
                $unidade = $this->iugu_model->get_unidade($contrato->row()->filial);

                $this->load->library('iugu');

                $iugu = new Iugu_library();
                $iugu->setToken($unidade->row()->iugu_token);

                $retorno = $iugu->suspender_assinatura($_POST["iugu_id"]);

                if($retorno["erro"] == true) {
                    $this->session->set_flashdata('message', show_alert_error($retorno["msg"]));
                } else {
                    $this->iugu_model->update_subscriptions($_POST['iugu_id'], array("status" => "0"));
                    $this->auditorias_model->create("iugu", "suspender_assinatura", "Suspensa assinatura na Iugu do contrato: ".$contrato_id.".");
                    $this->ion_auth_model->historico_create(array("LIG_ID_CLIENTE" => $contrato->row()->user_id, "LIG_ID_USUARIO" => $this->session->userdata('user_id'), "STATUS" => $_POST["status"], "MENSAGEM" => $_POST["motivo"], "DATA_REGISTRO" => date('Y-m-d'), "HORA_REGISTRO" => date("H:i:s")));
                    $this->session->set_flashdata('message', show_alert_success("Suspensa assinatura na Iugu."));
                }
            }
            redirect('/', 'refresh');
        }
    }

	public function ativar_assinatura($contrato_id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "ativar_assinatura"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            //if($_POST) {

                $assinatura_modelo = $this->iugu_model->get_iugu_assinatura($contrato_id);

                if($assinatura_modelo->num_rows() == 0) {
                    $this->session->set_flashdata('message', show_alert_error("Assinatura não encontrada."));
                    redirect('/', 'refresh');
                }
				
				/*if($assinatura_modelo->row()->status == 1) {
                    $this->session->set_flashdata('message', show_alert_error("Assinatura já se encontra ativa."));
                    redirect('/', 'refresh');
                }*/

                $contrato = $this->iugu_model->get_contrato($contrato_id);
                $unidade = $this->iugu_model->get_unidade($contrato->row()->filial);


                $this->load->library('iugu');

                $iugu = new Iugu_library();
                $iugu->setToken($unidade->row()->iugu_token);

                $retorno = $iugu->ativar_assinatura($assinatura_modelo->row()->iugu_id);

                if($retorno["erro"] == true) {
                    $this->session->set_flashdata('message', show_alert_error($retorno["msg"]));
                } else {
                    $this->iugu_model->update_subscriptions($assinatura_modelo->row()->iugu_id, array("status" => "1"));
                    $this->auditorias_model->create("iugu", "ativar_assinatura", "Ativada assinatura na Iugu do contrato: ".$contrato_id.".");
                    $this->ion_auth_model->historico_create(array("LIG_ID_CLIENTE" => $contrato->row()->user_id, "LIG_ID_USUARIO" => $this->session->userdata('user_id'), "STATUS" => $_POST["status"], "MENSAGEM" => "Assinatura ".$assinatura_modelo->row()->iugu_id." ativada.", "DATA_REGISTRO" => date('Y-m-d'), "HORA_REGISTRO" => date("H:i:s")));
                    $this->session->set_flashdata('message', show_alert_success("Ativada assinatura na Iugu."));
                }
            //}
            redirect('contratos', 'refresh');
        }
    }

    public function imprimir_fatura($id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "imprimir_fatura"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $iugu_assinatura = $this->iugu_model->get_contrato_by_iugu_assinatura($id);

            if($iugu_assinatura->num_rows() > 0) {
                $contrato = $this->iugu_model->get_contrato($iugu_assinatura->row()->cad_orca_id);
                $unidade = $this->iugu_model->get_unidade($contrato->row()->filial);

                if($unidade->row()->iugu_status == "N") {
                    $this->session->set_flashdata('message', show_alert_error("Dados de configuração da Iugu inativo."));
                    redirect('contratos/ver_parcelas/'.$contrato->row()->id, 'refresh');
                }

                $iugu = new Iugu_library();
                $iugu->setToken($unidade->row()->iugu_token);

                $retorno = $iugu->buscar_fatura($id);
                if($retorno["erro"] == true) {
                    $this->session->set_flashdata('message', show_alert_error($retorno["msg"]));
                } else {
                    redirect($retorno['data']['secure_url'].".pdf", 'refresh');
                    exit;
                }
                //exit("<pre>".print_r($retorno,true)."</pre>");
            }
            else {
                $this->session->set_flashdata('message',show_alert_error("Assinatura não encontrada."));
            }

            redirect('contratos/ver_parcelas/'.$contrato->row()->id, 'refresh');
        }
    }

    public function conferir_fatura($id)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "conferir_fatura"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $iugu_assinatura = $this->iugu_model->get_contrato_by_iugu_assinatura($id);

            if($iugu_assinatura->num_rows() > 0) {
                $contrato = $this->iugu_model->get_contrato($iugu_assinatura->row()->cad_orca_id);
                $unidade = $this->iugu_model->get_unidade($contrato->row()->filial);

                if($unidade->row()->iugu_status == "N") {
                    $this->session->set_flashdata('message', show_alert_error("Dados de configuração da Iugu inativo."));
                    redirect('contratos/ver_parcelas/'.$contrato->row()->id, 'refresh');
                }

                $iugu = new Iugu_library();
                $iugu->setToken($unidade->row()->iugu_token);

                $retorno = $iugu->buscar_fatura($id);
                if($retorno["erro"] == true) {
                    $this->session->set_flashdata('message', show_alert_error($retorno["msg"]));
                } else {

                    if($retorno['data']['status'] == "paid") {

                        $ct_receb_model = $this->iugu_model->get_iugu_invoice_by_iugu($id);

                        $ct_receb = $this->iugu_model->get_ct_receb($ct_receb_model->ct_receb_id);

                        if($ct_receb_model->status == "pending" || ($ct_receb_model->status == "paid" && $ct_receb->row()->VALOR_PAGO == 0 && $ct_receb->row()->DATA_PGTO == "0000-00-00")) {

                            $total_cents = substr_replace($retorno['data']["total_cents"], ".", -2, 0);

                            $this->iugu_model->update_invoice($id, array("iugu_id" => $retorno['data']['id'], "linha_digitavel" => $retorno['data']["bank_slip"]["digitable_line"], "status" => "paid"), array('cad_orca_id' => $contrato->row()->id, 'ct_receb_id' => $ct_receb_model->ct_receb_id, 'tipo' => 'P'));

                            
                            $paid_at = explode("T", $retorno['data']['paid_at']);
                            $this->iugu_model->update_ct_receb(array("VALOR_PAGO" => $total_cents, "DATA_PGTO" => $paid_at[0], "SINCRONIZADO" => '0', "DATA_ATUALIZADA" => date('Y-m-d')), array("CODIGO" => $ct_receb_model->ct_receb_id));

                            $nsu = "";
                            foreach($retorno['data']["variables"] as $var) {
                                if($var["variable"] == "payment_data.nsu") {
                                    $nsu = $var["value"];
                                    break;
                                }
                            }


                            $usuario_model = $this->iugu_model->get_user($contrato->row()->user_id);

                                                
                            if($retorno['data']["payable_with"] != "bank_slip") {

                                $data_create_mvtos["data"] = array(
                                    "number_of_installments" => $contrato->row()->qnt_parcelas,
                                    "installment" => $ct_receb->row()->PARCELA,
                                    "amount" => $total_cents,
                                    "id" => $retorno["data"]["id"]
                                );
                                
                                $this->iugu_model->create_cartao_mvtos($data_create_mvtos, $ct_receb->row()->CLIENTE, $ct_receb->row()->CODIGO, $nsu, "Cartão cadastrado na baixa do documento N.º ".$ct_receb->row()->DOCUMENTO." do cliente ".$ct_receb->row()->CLIENTE."-".$usuario_model->row()->nome, $retorno['data']["financial_return_date"], $ct_receb->row()->VALORDESCONTOPONTUALIDADE);
                            }

                            //atualizar cliente voxy
                            $creditos = null;
                            $prefs = $this->iugu_model->get_pref();
                            if($prefs->row()->qtd_creditos_ead > 0) {
                                $this->load->library('voxy');
                                $retorno_voxy = $this->voxy->make_request("/partners/users/{$contrato->row()->user_id}", "GET", array());
                                if($retorno_voxy["status_code"] == 200) {
                                    $user_voxy = json_decode($retorno_voxy["content"], true);
                                } else {
                                    $user_voxy["tutoring_credits"] = 0;
                                }            

                                if(@$user_voxy["tutoring_credits"] <= $prefs->row()->qtd_creditos_ead) {
                                    $creditos = $prefs->row()->qtd_creditos_ead;
                                }
                            }

                            $has_cliente_voxy = $this->iugu_model->has_cliente_voxy($ct_receb->row()->CLIENTE);
                            $expiration_date = date("Y-m-d", strtotime("+40 days"));
                            if($has_cliente_voxy) {
                                $params = array(
                                    'expiration_date' => $expiration_date
                                );
                                if($creditos != null) {
                                    $params["tutoring_credits"] = $creditos;
                                }

                                $retorno_registro_ead = $this->voxy->make_request("/partners/users/{$contrato->row()->user_id}", "PUT", $params);
                                if($retorno_registro_ead["status_code"] == 200) {
                                    $this->iugu_model->update_cliente_voxy(array("expiration_date" => $params["expiration_date"]), array("user_id" => $contrato->row()->user_id));
                                    // return true;
                                } else {
                                    // return false;
                                }
                            } else {
                                $params = array(
                                    'email_address' => $usuario_model->row()->email,
                                    'first_name' => $usuario_model->row()->nome,
                                    'native_language' => 'pt',
                                    'level' => '1',
                                    'expiration_date' => $expiration_date,
                                    'date_joined' => date("Y-m-d"),
                                    'feature_group_id' => 6242
                                );

                                $retorno_registro_ead = $this->voxy->make_request("/partners/users/{$contrato->row()->user_id}", "POST", $params);
                                if($retorno_registro_ead["status_code"] == 201) {
                                    $this->iugu_model->create_cliente_voxy(array("user_id" => $contrato->row()->user_id, "date_joined" => date("Y-m-d"), "level" => "1", "expiration_date" => $expiration_date));
                                    // return true;
                                } else {
                                    // return false;
                                }
                            }

                            //atualizar contrato fim vigencia
                            $this->iugu_model->update_orcamento($iugu_assinatura->row()->cad_orca_id, array("fim_vigencia" => date('Y-m-d', strtotime("+30 days"))));

                            $this->session->set_flashdata('message', show_alert_success("Fatura ".$id." conferida e alterada para 'paga'."));
                        } else {
                            $this->session->set_flashdata('message',show_alert_error("O invoice deve estar com status pending para consulta."));
                        }
                    }

                    // exit("<pre>".print_r($retorno['data'],true)."</pre>");
                }
                //exit("<pre>".print_r($retorno,true)."</pre>");
            }
            else {
                $this->session->set_flashdata('message',show_alert_error("Assinatura não encontrada."));
            }

            redirect('contratos/ver_parcelas/'.$contrato->row()->id, 'refresh');
        }
    }

    public function alterar_id_assinatura() {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "alterar_id_assinatura"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $post = $_POST;
            $retorno = array();

            $contrato = $this->iugu_model->get_contrato($post['orc_id']);
            $unidade = $this->iugu_model->get_unidade($contrato->row()->filial);

            $iugu = new Iugu_library();
            $iugu->setToken($unidade->row()->iugu_token);
            $assinatura_iugu = $iugu->buscar_assinatura($post["assinatura_id"]);

            // exit("<pre>".print_r($assinatura_iugu,true)."</pre>");

            if($assinatura_iugu["erro"] == false) {
                
                $retorno["erro"] = false;
                $retorno["msg"] = "<p><b>ID:</b> ".$assinatura_iugu["data"]["id"]."</p>".
                    "<p><b>Expira em:</b> ".date("d/m/Y", strtotime($assinatura_iugu["data"]["expires_at"]))."</p>".
                    "<p><b>Criado em:</b> ".date("d/m/Y", strtotime($assinatura_iugu["data"]["created_at"]))."</p>".
                    "<p><b>Nome cliente:</b> ".$assinatura_iugu["data"]["customer_name"]."</p>".
                    "<p><b>E-mail cliente:</b> ".$assinatura_iugu["data"]["customer_email"]."</p>".
                    "<p><b>Pago com:</b> ".$assinatura_iugu["data"]["payable_with"]."</p>".
                    "<p><b>Plano:</b> ".$assinatura_iugu["data"]["plan_name"]."</p>".
                    "<p><b>Preço:</b> ".number_format($assinatura_iugu["data"]["price_cents"] / 100, 2, ',', '.')."</p>";

                if(count($assinatura_iugu["data"]["subitems"]) > 0) {
                    $retorno["msg"] .= "<p><b>Subitens:</b></p><table class='table'><tr><th>Descrição</th><th>Qtd</th><th>Unit.</th><th>Total</th></tr>";
                    foreach ($assinatura_iugu["data"]["subitems"] as $subitem) {
                        $retorno["msg"] .= "<tr>";
                        $retorno["msg"] .= "<td>".$subitem["description"]."</td>";
                        $retorno["msg"] .= "<td>".$subitem["quantity"]."</td>";
                        $retorno["msg"] .= "<td>".$subitem["price"]."</td>";
                        $retorno["msg"] .= "<td>".$subitem["total"]."</td>";
                        $retorno["msg"] .= "</tr>";
                    }
                    $retorno["msg"] .= "</table>";
                }

                /*$assinatura_id = $this->iugu_model->get_iugu_assinatura($post['orc_id'])->row()->iugu_id;

                if($this->iugu_model->update_subscriptions($assinatura_id, array('iugu_id' => $post["assinatura_id"]))) {
                    $retorno["erro"] = false;
                    $retorno["msg"] = "";
                } else {
                    $retorno["erro"] = true;
                    $retorno["msg"] = "Houve um erro ao salvar a assinatura.";
                }*/

            } else {
                $retorno["erro"] = true;
                // $retorno["msg"] = $assinatura_iugu["msg"];
                $retorno["msg"] = "Assinatura não encontrada na iugu.";
            }

            exit(json_encode($retorno));
        }
    }

    public function alterar_id_assinatura_ajax($orc_id, $iugu_id) {
        // exit("<pre>".print_r($orc_id." - ".$iugu_id,true)."</pre>");

        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "alterar_id_assinatura"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            $retorno = array();

            $contrato = $this->iugu_model->get_contrato($orc_id);
            $unidade = $this->iugu_model->get_unidade($contrato->row()->filial);

            $iugu = new Iugu_library();
            $iugu->setToken($unidade->row()->iugu_token);
            $assinatura_iugu = $iugu->buscar_assinatura($iugu_id);

            if($assinatura_iugu["erro"] == false) {
                
                $assinatura_id = $this->iugu_model->get_iugu_assinatura($orc_id)->row()->iugu_id;

                if($this->iugu_model->update_subscriptions($assinatura_id, array('iugu_id' => $iugu_id))) {
                    $this->session->set_flashdata('message',show_alert_success("Assinatura alterada com sucesso."));
                } else {
                    $this->session->set_flashdata('message',show_alert_error("Houve um erro ao salvar a assinatura."));
                }

            } else {
                //$this->session->set_flashdata('message',show_alert_error($assinatura_iugu["msg"]));
                $this->session->set_flashdata('message',show_alert_error("Assinatura não encontrada na iugu."));
            }

            redirect('iugu/alterar_assinatura/'.$orc_id);
        }
    }
	
	public function planos()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("iugu", "planos"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->breadcrumbs->unshift(2, lang('planos'), 'iugu/planos');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->page_title->clear();
            $this->page_title->push(lang('planos'));
            $this->data['pagetitle'] = $this->page_title->show();

            $planos_iugu_arr = array();
            $mostrar_filtro = true;

            $unids_arr = array(""=>"Selecione");
            foreach ($this->iugu_model->get_unidades_permitidas()->result() as $unid) {
                $unids_arr[$unid->unidade_id] = $unid->unidade;
            }

            if($_POST) {
                if(!array_key_exists($_POST["filtro_unidades"], $unids_arr)) {
                    $this->session->set_flashdata('message', show_alert_error("Seu usuário não tem acesso a essa unidade."));
                    redirect('iugu/planos');
                }

                if(!$_POST["filtro_unidades"]) {
                    $this->session->set_flashdata('message',show_alert_error("Filtre uma unidade."));
                    redirect('iugu/planos', 'refresh');
                }
                $mostrar_filtro = false;
                $iugu = new Iugu_library();
                $unidade = $this->iugu_model->get_unidade($_POST["filtro_unidades"]);
                $iugu->setToken($unidade->row()->iugu_token);   
                $retorno_planos = $iugu->listar_planos();
                if($retorno_planos["erro"] == false) {
                    $i = 0;
                    foreach ($retorno_planos["data"]["items"] as $item) {
                        $planos_iugu_arr[$i] = $item;
                        $planos_iugu_arr[$i]["unidade"] = $unidade->row()->unidade;
                        $planos_iugu_arr[$i]["unidade_id"] = $unidade->row()->id;
                        $i++;
                    }
                }
            }
         
            $this->data['planos'] = $planos_iugu_arr;

            $this->data['unidades'] = array(
                'id'=>'filtro_unidades',
                'name'=>'filtro_unidades',
                'class'=>'form-control', 
                'options' => $unids_arr, 
                'selected'=> ($this->input->post('filtro_unidades')) ? $this->input->post('filtro_unidades') : ''
            );
            $this->data['message'] = $this->session->flashdata('message');
            $this->data['mostrar_filtro'] = $mostrar_filtro;

            /* Load Template */
            $this->template->admin_render('admin/iugu/planos', $this->data);
        }
    }

    public function ver_plano($unidade_id, $identificador)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $unidade_id OR empty($unidade_id) OR ! $identificador OR empty($identificador) OR ! $this->ion_auth->has_permissao("iugu", "ver_plano"))
        {
            redirect('auth', 'refresh');
        }

        $unids_arr = [];
        foreach ($this->iugu_model->get_unidades_permitidas()->result() as $unid) {
            $unids_arr[] = $unid->unidade_id;
        }
        if(!in_array($unidade_id, $unids_arr)) {
            $this->session->set_flashdata('message', show_alert_error("Seu usuário não tem acesso a essa unidade."));
            redirect('iugu/planos');
        }

        $this->load->model('periodico_model');

        $this->form_validation->set_rules('plano_financeiro', 'lang:plano_financeiro', 'required|numeric');
        $this->form_validation->set_rules('centro_custo', 'lang:centro_custo', 'required|numeric');

        if ($this->form_validation->run() == TRUE) {

            $this->periodico_model->insert_iugu_plan(array("plano" => $identificador, "plano_financeiro" => $this->input->post('plano_financeiro'), "centro_custo" => $this->input->post('centro_custo'), "vendedor" => $this->input->post('vendedor')));
            $this->periodico_model->createByPlano($identificador, $_POST["periodicos"]);
            $this->session->set_flashdata('message', show_alert_success("Plano atualizado."));
            redirect('iugu/planos');
        } else {
            if($_POST)
                $this->session->set_flashdata('message',show_alert_error(validation_errors()));
        }
           
        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('planos'), 'iugu/planos');
        $this->breadcrumbs->unshift(3, lang('ver_plano'), 'iugu/ver_plano');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');

        $iugu = new Iugu_library();
        $unidade = $this->iugu_model->get_unidade($unidade_id);
        $iugu->setToken($unidade->row()->iugu_token);   
        $retorno_plano = $iugu->listar_planos($identificador);

        if($retorno_plano["erro"] == false) {
            $retorno_plano["data"]["unidade"] = $unidade->row()->unidade;
            $this->data['plano'] = $retorno_plano["data"];

            $this->data['plano_local'] = $this->periodico_model->get_iugu_plano($identificador);

            $this->data['periodicos'] = $this->periodico_model->get(null, $unidade_id);

            $periodicos_arr_check = array();
            $iugu_plan_periodico = $this->periodico_model->getIuguPlanPeriodico(null, $retorno_plano["data"]["identifier"]);
            foreach ($iugu_plan_periodico->result() as $value) {
                $periodicos_arr_check[] = $value->periodico_id;
            }
            $this->data['periodicos_check'] = $periodicos_arr_check;

           // exit("<pre>".print_r($periodicos->result(),true)."</pre>");

        } else {
            $this->session->set_flashdata('message', show_alert_error("Plano não encontrado."));
            redirect('iugu/planos');
        }

        $this->data['plano_financeiro'] = array(
            'name'  => 'plano_financeiro',
            'id'    => 'plano_financeiro',
            'class' => 'form-control',
            'value' => ($this->input->post('plano_financeiro')) ? $this->input->post('plano_financeiro') : @$this->data['plano_local']->plano_financeiro
        );

        $this->data['centro_custo'] = array(
            'name'  => 'centro_custo',
            'id'    => 'centro_custo',
            'class' => 'form-control',
            'value' => ($this->input->post('centro_custo')) ? $this->input->post('centro_custo') : @$this->data['plano_local']->centro_custo
        );

        $vendedores_array = array(""=>"Selecione um vendedor");
        foreach ($this->iugu_model->get_vendedores()->result() as $value) {
            $vendedores_array[$value->vend_id] = $value->first_name." ".$value->last_name;
        }
        $this->data['vendedor'] = array(
            'name'  => 'vendedor',
            'id'    => 'vendedor',
            'class' => 'form-control',
            'options' => $vendedores_array,
            'selected' => ($this->input->post('vendedor')) ? $this->input->post('vendedor') : @$this->data['plano_local']->vendedor
        );

        /* Load Template */
        $this->template->admin_render('admin/iugu/ver_plano', $this->data);
    }
}
