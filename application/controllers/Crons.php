<?php
ini_set('display_errors', 1);

class Crons extends CI_Controller {
    const rfc2047header = '/=\?([^ ?]+)\?([BQbq])\?([^ ?]+)\?=/';

    const rfc2047header_spaces = '/(=\?[^ ?]+\?[BQbq]\?[^ ?]+\?=)\s+(=\?[^ ?]+\?[BQbq]\?[^ ?]+\?=)/';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('crons_model');
        $this->load->model('dashboard_model');
        $this->load->model('ion_auth_model');
        $this->load->helper('functions_helper');
        $this->load->library('voxy');
        //$this->load->library(array('email'));
        $this->load->helper('url');
        //$this->load->library('pdf');
        $this->load->library('phpmailer_library');
    }


    public function first_load()
    {
        $this->atualizar_ultimo_acesso_alunos_ead(date("Y-m-d", strtotime("-6 months")));
    }

    public function index()
    {
        //log_message('error', 'crons');

        $this->alert_failed_day();

        $array_resultados = array();
        for($i = 2; $i >= 1; $i--) {
            if($_GET["data"])
                $array_resultados[date('Y-m-d', strtotime("-".$i." days", strtotime($_GET["data"])))] = $this->atualizar_ultimo_acesso_alunos_ead(date('Y-m-d', strtotime("-".$i." days", strtotime($_GET["data"]))));
            else
                $array_resultados[date("Y-m-d", strtotime("-".$i." days"))] = $this->atualizar_ultimo_acesso_alunos_ead(date("Y-m-d", strtotime("-".$i." days")));
        }

        //exit("<pre>".print_r($array_resultados,true)."</pre>");

        $count_1 = 0;
        $count_2 = 0;

        if($_GET["data"]) {
            $count_1 = count($array_resultados[date('Y-m-d', strtotime("-1 days", strtotime($_GET["data"])))]);
            $count_2 = count($array_resultados[date('Y-m-d', strtotime("-2 days", strtotime($_GET["data"])))]);
        } else {
            $count_1 = count($array_resultados[date("Y-m-d", strtotime("-1 days"))]);
            $count_2 = count($array_resultados[date("Y-m-d", strtotime("-2 days"))]);
        }

        if(count($array_resultados) == 2 && $count_2 > 0 && $count_1 > 0) {
            $orcamentos = $this->crons_model->get_orcamentos();
            if($orcamentos->num_rows() > 0 && !$this->has_evasao()) {
                $frequentes = 0;
                $evasao = 0;
                $evasao_id = $this->get_evasao_id($array_resultados);
                
                foreach ($orcamentos->result() as $orc) {
                    $this->verificar_nunca_acessou($orc);
                    $this->verificar_desistente($orc);
                    if($this->calcular_evasao($orc, $evasao_id) == false) {
                        $evasao++;
                    } else {
                        $frequentes++;
                    }
                }
                $this->crons_model->update_evasao($evasao_id, array("frequentes" => $frequentes, "evasao" => $evasao));
            }
        
            $this->gerar_relatorio_tutor();
           
            $this->gerar_relatorio_supervisor();
        }
    }

    private function gerar_relatorio_tutor() {
        $tutores_model = $this->crons_model->get_tutores();

        if(count($tutores_model) > 0) {
            foreach ($tutores_model as $obj) {
                if($obj->num_rows() > 0) {
                    $i = 1;
                    $last_empresa = "";
                    foreach ($obj->result() as $usr) {
                        if($this->ativo_relatorio_tutor($usr->empresa_id) == 1 && $this->diff_data_relatorio_tutor($usr->empresa_id)) {
                        // if($this->ativo_relatorio_tutor($usr->empresa_id) == 1) {
                            if($usr->empresa_id != $last_empresa && $last_empresa != "") {
                                $this->crons_model->update_data_relatorio_tutor($last_empresa);
                            }

                            /*$nivel_alunos = $this->crons_model->get_nivel_alunos($usr->empresa_id, $usr->id);

                            $na_array = array(
                                "higher" => 0,
                                "higher_medium" => 0,
                                "medium" => 0,
                                "emerging" => 0,
                                "total" => 0
                            );
                            if($nivel_alunos->num_rows() > 0) {
                                $last_na = "";
                                foreach ($nivel_alunos->result() as $na) {
                                    if($last_na != $na->id) {
                                        $na_array[$na->desempenho_geral] = $na_array[$na->desempenho_geral] + 1;
                                        $na_array["total"] = $na_array["total"] + 1;
                                    }
                                    $last_na = $na->id;
                                }
                            }

                            $json_nivel_alunos = $na_array;*/


                            $count_contratos_ativos_anterior = 0;
                            $count_contratos_ativos_atual = 0;

                            $count_contratos_nuncaveio_anterior = 0;
                            $count_contratos_nuncaveio_atual = 0;

                            $count_contratos_desistentes_anterior = 0;
                            $count_contratos_desistentes_atual = 0;

                            $count_contratos_matriculas_anterior = 0;
                            $count_contratos_matriculas_atual = 0;

                            $count_contratos_rpedag_anterior = 0;
                            $count_contratos_rpedag_atual = 0;

                            $count_contratos_rfinan_anterior = 0;
                            $count_contratos_rfinan_atual = 0;

                            $count_contratos_prematricula_anterior = 0;
                            $count_contratos_prematricula_atual = 0;

                            $parametros = array();

                            $evasao_by_date_atual = $this->crons_model->get_evasao_by_date($usr->id, $usr->empresa_id, date('Y-m-d'));

                            foreach ($evasao_by_date_atual->result() as $ev) {
                                $parametro = array();
                                if(array_key_exists($ev->filial, $parametros))
                                    $parametro = $parametros[$ev->filial];
                                else {
                                    $parametro = $this->crons_model->get_parametro($ev->filial)->row();
                                    $parametros[$ev->filial] = $parametro;
                                }

                                //PRE MATRICULA
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_01)
                                    $count_contratos_prematricula_atual++;

                                //MATRICULA
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_02)
                                    $count_contratos_matriculas_atual++;

                                //ATIVO
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_03)
                                    $count_contratos_ativos_atual++;

                                //NUNCA VEIO
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_04)
                                    $count_contratos_nuncaveio_atual++;

                                //DESISTENTE
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_05 || $ev->status_atual == $parametro->LIG_ID_STATUS_DESISTENCIA)
                                    $count_contratos_desistentes_atual++;

                                //RESGATE FINANCEIRO
                                if($ev->status_atual == $parametro->LIG_ID_RESGATE_FINANCEIRO)
                                    $count_contratos_rfinan_atual++;

                                //RESGATE PEDAGOGICO
                                if($ev->status_atual == $parametro->LIG_ID_RESGATE_PEDAGOGICO)
                                    $count_contratos_rpedag_atual++;
                            }

                            $evasao_by_date_anterior = $this->crons_model->get_evasao_by_date($usr->id, $usr->empresa_id, date("Y-m-d", strtotime("-7 days")));

                            foreach ($evasao_by_date_anterior->result() as $ev) {
                                $parametro = array();
                                if(array_key_exists($ev->filial, $parametros))
                                    $parametro = $parametros[$ev->filial];
                                else {
                                    $parametro = $this->crons_model->get_parametro($ev->filial)->row();
                                    $parametros[$ev->filial] = $parametro;
                                }

                                //PRE MATRICULA
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_01)
                                    $count_contratos_prematricula_anterior++;

                                //MATRICULA
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_02)
                                    $count_contratos_matriculas_anterior++;

                                //ATIVO
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_03)
                                    $count_contratos_ativos_anterior++;

                                //NUNCA VEIO
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_04)
                                    $count_contratos_nuncaveio_anterior++;

                                //DESISTENTE
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_05 || $ev->status_atual == $parametro->LIG_ID_STATUS_DESISTENCIA)
                                    $count_contratos_desistentes_anterior++;

                                //RESGATE FINANCEIRO
                                if($ev->status_atual == $parametro->LIG_ID_RESGATE_FINANCEIRO)
                                    $count_contratos_rfinan_anterior++;

                                //RESGATE PEDAGOGICO
                                if($ev->status_atual == $parametro->LIG_ID_RESGATE_PEDAGOGICO)
                                    $count_contratos_rpedag_anterior++;
                            }

                            $data["count_contratos_ativos_anterior"] = $count_contratos_ativos_anterior;
                            $data["count_contratos_ativos_atual"] = $count_contratos_ativos_atual;

                            $data["count_contratos_nuncaveio_anterior"] = $count_contratos_nuncaveio_anterior;
                            $data["count_contratos_nuncaveio_atual"] = $count_contratos_nuncaveio_atual;

                            $data["count_contratos_desistentes_anterior"] = $count_contratos_desistentes_anterior;
                            $data["count_contratos_desistentes_atual"] = $count_contratos_desistentes_atual;

                            $data["count_contratos_matriculas_anterior"] = $count_contratos_matriculas_anterior;
                            $data["count_contratos_matriculas_atual"] = $count_contratos_matriculas_atual;

                            $data["count_contratos_rpedag_anterior"] = $count_contratos_rpedag_anterior;
                            $data["count_contratos_rpedag_atual"] = $count_contratos_rpedag_atual;

                            $data["count_contratos_rfinan_anterior"] = $count_contratos_rfinan_anterior;
                            $data["count_contratos_rfinan_atual"] = $count_contratos_rfinan_atual;

                            $data["count_contratos_prematricula_anterior"] = $count_contratos_prematricula_anterior;
                            $data["count_contratos_prematricula_atual"] = $count_contratos_prematricula_atual;

                            $alunos_meta_atingida = $this->crons_model->get_alunos_meta_atigida($usr->id, $usr->empresa_id)->result();

                            $tutorias_realizadas = $this->crons_model->get_tutorias_realizadas($usr->id)->result();
                            
                            $cancelamentos = $this->crons_model->get_contratos_ead_cancelados_por_tutor($usr->id);

                            $data["tutor"] = $usr->first_name." ".$usr->last_name;
                            //$data["json_nivel_alunos"] = $json_nivel_alunos;
                            $data["tutorias_realizadas"] = $tutorias_realizadas;
                            $data["alunos_meta_atingida"] = $alunos_meta_atingida;
                            $data["cancelamentos"] = $cancelamentos;
                            $data["bootstrap_css"] = $this->config->base_url().'assets/frameworks/bootstrap/css/bootstrap.min.css';
                            $data["adminlte_css"] = $this->config->base_url().'assets/frameworks/adminlte/css/adminlte.min.css';
                            $data["font_css"] = $this->config->base_url().'assets/frameworks/font-awesome/css/font-awesome.min.css';
                            $data["chart_js"] = $this->config->base_url().'assets/frameworks/chart.js/Chart.js';
                            $data["jquery_js"] = $this->config->base_url().'assets/frameworks/jquery/jquery.min.js';
                
                            //$nome_relatorio = "upload/relatorios/relatorio-tutor-".$usr->id."-".date('Y-m-d');
                            //$this->pdf->load_view_save('admin/email/relatorio_tutor.tpl.php', $nome_relatorio, $data);

                            $message = $this->load->view("admin/email/relatorio_tutor.tpl.php", $data, true);
                            $email_config_db = $this->ion_auth_model->get_email_config($usr->empresa_id)->row();

                            if($email_config_db->test == 1) {
                                $email_config = rowToConfigEmail($email_config_db);
                                $from_email = $email_config_db->from_email;
                                $from_name = $email_config_db->from_name;
                            } else {
                                $email_config = $this->config->item('email_config', 'ion_auth');
                                $from_email = $this->config->item('admin_email', 'ion_auth');
                                $from_name = $this->config->item('site_title', 'ion_auth');
                            }

                            // if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
                            // {
                            //     $this->email->initialize($email_config);
                            // }

                            $mail = $this->phpmailer_library->load();
                            $mail->isSMTP();
                            $mail->Host     = $email_config['smtp_host'];
                            $mail->SMTPAuth = true;
                            $mail->Username = $email_config['smtp_user'];
                            $mail->Password = $email_config['smtp_pass'];
                            $mail->SMTPSecure = 'tsl';
                            $mail->Port     = $email_config['smtp_port'];

                            $mail->setFrom($from_email, utf8_decode($from_name));
                            $emails_to = array($usr->email);
                            $mail->addAddress(array_unique($emails_to));

                            $mail->Subject = utf8_decode('MaestriaTech - Relatório Semanal');
                            $mail->isHTML(true);

                            $mail->Body = utf8_decode($message);
                            $mail->send();

                            //$this->email->clear(true);
                            //$this->email->from($from_email, utf8_decode($from_name));

                            //$emails_to = array($usr->email);

                            //$this->email->to(array_unique($emails_to));
                            //$this->email->subject(utf8_decode('MaestriaTech - Relatório Semanal'));
                            // $this->email->message(utf8_decode($message));
                            // $this->email->set_mailtype("html");
                            // $this->email->send();

                            $last_empresa = $usr->empresa_id;
                            if($i == $obj->num_rows()) {
                                $this->crons_model->update_data_relatorio_tutor($last_empresa);
                            }
                        }
                        $i++;
                    }
                }                
            }
        }
    }

    private function gerar_relatorio_supervisor() {
        $supervisores_model = $this->crons_model->get_supervisores();
        if(count($supervisores_model) > 0) {
            foreach ($supervisores_model as $obj) {
                if($obj->num_rows() > 0) {
                    $i = 1;
                    $last_empresa = "";
                    foreach ($obj->result() as $usr) {
                        if($this->ativo_relatorio_supervisor($usr->empresa_id) == 1 && $this->diff_data_relatorio_supervisor($usr->empresa_id)) {
                        //if($this->ativo_relatorio_supervisor($usr->empresa_id) == 1) {
                            if($usr->empresa_id != $last_empresa && $last_empresa != "") { 
                                $this->crons_model->update_data_relatorio_supervisor($last_empresa);
                            }

                            //$nivel_alunos = $this->crons_model->get_nivel_alunos($usr->empresa_id, null, date('Y-m-d', strtotime("-7 days")), date('Y-m-d'));
                            /*$nivel_alunos = $this->crons_model->get_nivel_alunos($usr->empresa_id, null);
                            $na_array = array(
                                "higher" => 0,
                                "higher_medium" => 0,
                                "medium" => 0,
                                "emerging" => 0,
                                "total" => 0
                            );
                            if($nivel_alunos->num_rows() > 0) {
                                $last_na = "";
                                foreach ($nivel_alunos->result() as $na) {
                                    if($last_na != $na->id) {
                                        $na_array[$na->desempenho_geral] = $na_array[$na->desempenho_geral] + 1;
                                        $na_array["total"] = $na_array["total"] + 1;
                                    }
                                    $last_na = $na->id;
                                }
                            }

                            $json_nivel_alunos = $na_array;

                            $metas_alunos = $this->crons_model->get_metas_alunos($usr->id, date('Y-m-d', strtotime("-7 days")), date('Y-m-d'));*/

                            $count_contratos_ativos_anterior = 0;
                            $count_contratos_ativos_atual = 0;

                            $count_contratos_nuncaveio_anterior = 0;
                            $count_contratos_nuncaveio_atual = 0;

                            $count_contratos_desistentes_anterior = 0;
                            $count_contratos_desistentes_atual = 0;

                            $count_contratos_matriculas_anterior = 0;
                            $count_contratos_matriculas_atual = 0;

                            $count_contratos_rpedag_anterior = 0;
                            $count_contratos_rpedag_atual = 0;

                            $count_contratos_rfinan_anterior = 0;
                            $count_contratos_rfinan_atual = 0;

                            $count_contratos_prematricula_anterior = 0;
                            $count_contratos_prematricula_atual = 0;

                            $parametros = array();

                            $evasao_by_date_atual = $this->crons_model->get_evasao_by_date($usr->id, $usr->empresa_id, date('Y-m-d'));

                            foreach ($evasao_by_date_atual->result() as $ev) {
                                $parametro = array();
                                if(array_key_exists($ev->filial, $parametros))
                                    $parametro = $parametros[$ev->filial];
                                else {
                                    $parametro = $this->crons_model->get_parametro($ev->filial)->row();
                                    $parametros[$ev->filial] = $parametro;
                                }

                                //PRE MATRICULA
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_01)
                                    $count_contratos_prematricula_atual++;

                                //MATRICULA
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_02)
                                    $count_contratos_matriculas_atual++;

                                //ATIVO
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_03)
                                    $count_contratos_ativos_atual++;

                                //NUNCA VEIO
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_04)
                                    $count_contratos_nuncaveio_atual++;

                                //DESISTENTE
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_05 || $ev->status_atual == $parametro->LIG_ID_STATUS_DESISTENCIA)
                                    $count_contratos_desistentes_atual++;

                                //RESGATE FINANCEIRO
                                if($ev->status_atual == $parametro->LIG_ID_RESGATE_FINANCEIRO)
                                    $count_contratos_rfinan_atual++;

                                //RESGATE PEDAGOGICO
                                if($ev->status_atual == $parametro->LIG_ID_RESGATE_PEDAGOGICO)
                                    $count_contratos_rpedag_atual++;
                            }

                            $evasao_by_date_anterior = $this->crons_model->get_evasao_by_date($usr->id, $usr->empresa_id, date("Y-m-d", strtotime("-7 days")));

                            foreach ($evasao_by_date_anterior->result() as $ev) {
                                $parametro = array();
                                if(array_key_exists($ev->filial, $parametros))
                                    $parametro = $parametros[$ev->filial];
                                else {
                                    $parametro = $this->crons_model->get_parametro($ev->filial)->row();
                                    $parametros[$ev->filial] = $parametro;
                                }

                                //PRE MATRICULA
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_01)
                                    $count_contratos_prematricula_anterior++;

                                //MATRICULA
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_02)
                                    $count_contratos_matriculas_anterior++;

                                //ATIVO
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_03)
                                    $count_contratos_ativos_anterior++;

                                //NUNCA VEIO
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_04)
                                    $count_contratos_nuncaveio_anterior++;

                                //DESISTENTE
                                if($ev->status_atual == $parametro->LIG_STATUS_CONTRATO_05 || $ev->status_atual == $parametro->LIG_ID_STATUS_DESISTENCIA)
                                    $count_contratos_desistentes_anterior++;

                                //RESGATE FINANCEIRO
                                if($ev->status_atual == $parametro->LIG_ID_RESGATE_FINANCEIRO)
                                    $count_contratos_rfinan_anterior++;

                                //RESGATE PEDAGOGICO
                                if($ev->status_atual == $parametro->LIG_ID_RESGATE_PEDAGOGICO)
                                    $count_contratos_rpedag_anterior++;
                            }

                            $data["count_contratos_ativos_anterior"] = $count_contratos_ativos_anterior;
                            $data["count_contratos_ativos_atual"] = $count_contratos_ativos_atual;

                            $data["count_contratos_nuncaveio_anterior"] = $count_contratos_nuncaveio_anterior;
                            $data["count_contratos_nuncaveio_atual"] = $count_contratos_nuncaveio_atual;

                            $data["count_contratos_desistentes_anterior"] = $count_contratos_desistentes_anterior;
                            $data["count_contratos_desistentes_atual"] = $count_contratos_desistentes_atual;

                            $data["count_contratos_matriculas_anterior"] = $count_contratos_matriculas_anterior;
                            $data["count_contratos_matriculas_atual"] = $count_contratos_matriculas_atual;

                            $data["count_contratos_rpedag_anterior"] = $count_contratos_rpedag_anterior;
                            $data["count_contratos_rpedag_atual"] = $count_contratos_rpedag_atual;

                            $data["count_contratos_rfinan_anterior"] = $count_contratos_rfinan_anterior;
                            $data["count_contratos_rfinan_atual"] = $count_contratos_rfinan_atual;

                            $data["count_contratos_prematricula_anterior"] = $count_contratos_prematricula_anterior;
                            $data["count_contratos_prematricula_atual"] = $count_contratos_prematricula_atual;

                            $data["supervisor"] = $usr->first_name." ".$usr->last_name;
                            //$data["json_nivel_alunos"] = $json_nivel_alunos;
                            //$data["metas_alunos"] = $metas_alunos;
                            $data["tutorias_realizadas"] = $this->crons_model->get_tutorias_realizadas()->result();
                            $data['cancelamentos'] = $this->crons_model->get_contratos_ead_cancelados($usr->id);
                            $data['detalhes_tutores'] = $this->crons_model->get_detalhes_tutores($usr->empresa_id, $usr->id);
                            $data['contratos_sem_tutor'] = $this->crons_model->get_contratos_sem_tutor($usr->empresa_id);
                            $data['desistentes_antes'] = $this->crons_model->get_desistentes($usr->empresa_id, $usr->id, "antes");
                            $data['desistentes_apos'] = $this->crons_model->get_desistentes($usr->empresa_id, $usr->id, "apos");
                            $data["bootstrap_css"] = $this->config->base_url().'assets/frameworks/bootstrap/css/bootstrap.min.css';
                            $data["adminlte_css"] = $this->config->base_url().'assets/frameworks/adminlte/css/adminlte.min.css';
                            $data["font_css"] = $this->config->base_url().'assets/frameworks/font-awesome/css/font-awesome.min.css';
                            $data["chart_js"] = $this->config->base_url().'assets/frameworks/chart.js/Chart.js';
                            $data["jquery_js"] = $this->config->base_url().'assets/frameworks/jquery/jquery.min.js';
                
                            //$nome_relatorio = "upload/relatorios/relatorio-supervisor-".$usr->id."-".date('Y-m-d');
                            //$this->pdf->load_view_save('admin/email/relatorio_supervisor.tpl.php', $nome_relatorio, $data);

                            $message = $this->load->view("admin/email/relatorio_supervisor.tpl.php", $data, true);
                            // exit($message);
                            $email_config_db = $this->ion_auth_model->get_email_config($usr->empresa_id);

                            if($email_config_db->test == 1) {
                                $email_config = rowToConfigEmail($email_config_db);
                                $from_email = $email_config_db->from_email;
                                $from_name = $email_config_db->from_name;
                            } else {
                                $email_config = $this->config->item('email_config', 'ion_auth');
                                $from_email = $this->config->item('admin_email', 'ion_auth');
                                $from_name = $this->config->item('site_title', 'ion_auth');
                            }

                            // if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
                            // {
                            //     $this->email->initialize($email_config);
                            // }

                            // $this->email->clear();
                            // $this->email->from($from_email, utf8_decode($from_name));

                            // $emails_to = array($usr->email);

                            // $this->email->to(array_unique($emails_to));
                            // $this->email->subject(utf8_decode('MaestriaTech - Relatório Semanal'));
                            // $this->email->message(utf8_decode($message));
                            // $this->email->set_mailtype("html");
                            // $this->email->send();

                            $mail = $this->phpmailer_library->load();
                            $mail->isSMTP();
                            $mail->Host     = $email_config['smtp_host'];
                            $mail->SMTPAuth = true;
                            $mail->Username = $email_config['smtp_user'];
                            $mail->Password = $email_config['smtp_pass'];
                            $mail->SMTPSecure = 'tsl';
                            $mail->Port     = $email_config['smtp_port'];

                            $mail->setFrom($from_email, utf8_decode($from_name));
                            $emails_to = array($usr->email);
                            $mail->addAddress(array_unique($emails_to));

                            $mail->Subject = utf8_decode('MaestriaTech - Relatório Semanal');
                            $mail->isHTML(true);

                            $mail->Body = utf8_decode($message);
                            $mail->send();

                            //unlink($nome_relatorio.".pdf");
                            $last_empresa = $usr->empresa_id;

                            if($i == $obj->num_rows()) {
                                $this->crons_model->update_data_relatorio_supervisor($last_empresa);
                            }
                        }
                        $i++;
                    }
                }                
            }
        }
    }

    private function atualizar_ultimo_acesso_alunos_ead($start_date) {
        $params["start_date"] = $start_date;

        if($_GET["data"])
            $params["end_date"] = $_GET["data"];
        else
            $params["end_date"] = date("Y-m-d");

        $params["has_entries"] = true;
        
        /*$licoes = array();
        $page = 1;
        while(1 == 1) {
            $retorno = $this->get_lessons($params);
            $licoes = array_merge_recursive($licoes, $retorno->results);
            if(count($retorno->results) < 99) {
                break;
            } else {
                $page++;
                $params["page"] = $page;
            }
        }

        if(count($licoes) > 0) {
            $user_id = "";
            $i = 0;
            foreach ($licoes as $licao) {
                if($licao->external_user_id != "") {
                    if($i == 0) {                        
                        $user_ead = $this->crons_model->get_user_ead($licao->external_user_id)->row();
                    }
                    if($user_ead) {
                        if($licao->external_user_id != $user_id) {
                            $user_ead = $this->crons_model->get_user_ead($licao->external_user_id)->row();
                            $user_id = $user_ead->user_id;
                        }
            
                        $data_licao = explode("T", $licao->date_started);
                        if($data_licao[0] > $user_ead->ultimo_acesso) {
                            $user_ead->ultimo_acesso = $data_licao[0];
                            $this->crons_model->update_user_ead($user_ead->user_id, array("ultimo_acesso" => $data_licao[0]));
                        }
                    }
                    $i++;
                }
            }
        }*/

        $atividades = array();
        $retornos = array();
        $page = 1;
        while(1 == 1) {
            $retorno = $this->get_activities($params);
            $retornos[] = $retorno;
            $atividades = array_merge_recursive($atividades, $retorno->results);
            if(count($retorno->results) <= 100) {
                break;
            } else {
                $page++;
                $params["page"] = $page;
            }
        }

        if(count($atividades) > 0) {
            foreach ($atividades as $atividade) {
                if($atividade->external_user_id != "" && $atividade->activities > 0) {
                    $user_ead = $this->crons_model->get_user_ead($atividade->external_user_id)->row();
                    if($start_date > $user_ead->ultimo_acesso) {
                        $this->crons_model->update_user_ead($user_ead->user_id, array("ultimo_acesso" => $start_date));
                    }
                }
            }
        }

        return $atividades;
    }

    private function get_lessons($params) {
        $retorno_acesso = $this->voxy->make_request("/partners/lessons", "GET", $params);
        if($retorno_acesso["status_code"] == 200) {
            $content = json_decode($retorno_acesso["content"]);
            return $content;
        } else {
            exit("<pre>".print_r($retorno_acesso,true)."</pre>");
            exit("ERRO AO BUSCAR LICOES DO ALUNO EAD!");
        }
    }

    private function get_activities($params) {
        $retorno_acesso = $this->voxy->make_request("/partners/user_activities", "GET", $params);
        if($retorno_acesso["status_code"] == 200) {
            $content = json_decode($retorno_acesso["content"]);
            return $content;
        } else {
            exit("<pre>".print_r($retorno_acesso,true)."</pre>");
            exit("ERRO AO BUSCAR ATIVIDADES DO ALUNO EAD!");
        }
    }

    private function verificar_nunca_acessou($orc) {
        $LIG_ID_STATUS_NUNCA_ACESSOU = $orc->LIG_ID_STATUS_NUNCA_ACESSOU;
        $NUM_DIAS_NUNCA_ACESSOU = $orc->NUM_DIAS_NUNCA_ACESSOU;
        $LIG_ID_STATUS_ATIVO = $orc->LIG_STATUS_CONTRATO_03;
        $STATUS_HISTORICO = $orc->STATUS_HISTORICO;
        $STATUS_RESGATE_FINANCEIRO = $orc->LIG_ID_RESGATE_FINANCEIRO;
        $STATUS_RESGATE_PEDAGOGICO = $orc->LIG_ID_RESGATE_PEDAGOGICO;

        if(($LIG_ID_STATUS_NUNCA_ACESSOU != "" || $NUM_DIAS_NUNCA_ACESSOU != "") && ($STATUS_RESGATE_FINANCEIRO != $orc->status && $STATUS_RESGATE_PEDAGOGICO != $orc->status)) {
            $user_ead = $this->crons_model->get_user_ead($orc->user_id)->row();
            if($user_ead) {
                /*if($user_ead->ultimo_acesso != "") {
                    $data_ultimo_acesso = new DateTime($user_ead->ultimo_acesso);
                    $data_atual = new DateTime(date("Y-m-d"));

                    $dateInterval = $data_ultimo_acesso->diff($data_atual);

                    if($dateInterval->days > $NUM_DIAS_NUNCA_ACESSOU) {
                        $this->crons_model->update_orcamento($orc->id, array("sincronizado" => "N", "ultima_atualizacao" => date("Y-m-d"), "status" => $LIG_ID_STATUS_NUNCA_ACESSOU));
                        $this->crons_model->historico_create(array("LIG_ID_CLIENTE" => $orc->user_id, "LIG_ID_USUARIO" => 1, "REGISTRO" => "true", "STATUS" => "ALTERAÇÃO STATUS CONTRATO", "MENSAGEM" => "Contrato do usuário '".$orc->user_id."' alterado para NUNCA ACESSOU.", "DATA_REGISTRO" => date("Y-m-d"), "HORA_REGISTRO" => date("H:i:s")));
                    }
                } else {*/
                    if($orc->data_matricula != "0000-00-00") {
                        if($_GET["data"]) {
                            $data = $_GET["data"];
                        } else {
                            $data = date("Y-m-d");
                        }

                        $data_ultimo_acesso = new DateTime($orc->data_matricula);
                        $data_atual = new DateTime($data);
                        
                        $dateInterval = $data_ultimo_acesso->diff($data_atual);

                        if($user_ead->ultimo_acesso != "" && $LIG_ID_STATUS_ATIVO != $orc->status) {
                            /*$this->crons_model->update_orcamento($orc->id, array("sincronizado" => "N", "ultima_atualizacao" => date("Y-m-d"), "status" => $LIG_ID_STATUS_ATIVO));
                            if($STATUS_HISTORICO != "") {
                                $this->crons_model->historico_create(array("LIG_ID_CLIENTE" => $orc->user_id, "LIG_ID_USUARIO" => 1, "REGISTRO" => "true", "STATUS" => $STATUS_HISTORICO, "MENSAGEM" => "Contrato '".$orc->id."' alterado para ATIVO.", "DATA_REGISTRO" => date("Y-m-d"), "HORA_REGISTRO" => date("H:i:s"), "empresa_id" => $orc->empresa_id));
                            }*/
                        } else {
                            if(($dateInterval->days > $NUM_DIAS_NUNCA_ACESSOU && $LIG_ID_STATUS_ATIVO != $orc->status) && $dateInterval->invert == 0) {
                                if($LIG_ID_STATUS_NUNCA_ACESSOU != $orc->status) {
                                    $this->crons_model->update_orcamento($orc->id, array("sincronizado" => "N", "ultima_atualizacao" => date("Y-m-d"), "status" => $LIG_ID_STATUS_NUNCA_ACESSOU));
                                    if($STATUS_HISTORICO != "") {
                                        $this->crons_model->historico_create(array("LIG_ID_CLIENTE" => $orc->user_id, "LIG_ID_USUARIO" => 1, "REGISTRO" => "true", "STATUS" => $STATUS_HISTORICO, "MENSAGEM" => "Contrato '".$orc->id."' alterado para NUNCA ACESSOU.", "DATA_REGISTRO" => date("Y-m-d"), "HORA_REGISTRO" => date("H:i:s"), "empresa_id" => $orc->empresa_id));
                                    }
                                }
                            }
                        }
                    }
                //}
            }
        }
    }

    private function verificar_desistente($orc) {
        $LIG_ID_STATUS_DESISTENCIA = $orc->LIG_ID_STATUS_DESISTENCIA;
        $NUM_DIAS_DESISTENCIA = $orc->NUM_DIAS_DESISTENCIA;
        $LIG_ID_STATUS_ATIVO = $orc->LIG_STATUS_CONTRATO_03;
        $STATUS_HISTORICO = $orc->STATUS_HISTORICO;
        $STATUS_RESGATE_FINANCEIRO = $orc->LIG_ID_RESGATE_FINANCEIRO;
        $STATUS_RESGATE_PEDAGOGICO = $orc->LIG_ID_RESGATE_PEDAGOGICO;

        if(($LIG_ID_STATUS_DESISTENCIA != "" || $NUM_DIAS_DESISTENCIA != "") && ($STATUS_RESGATE_FINANCEIRO != $orc->status && $STATUS_RESGATE_PEDAGOGICO != $orc->status)) {
            /*$ultimo_exercicio = $this->crons_model->get_ultimo_exercicio($orc->user_id);
            if($ultimo_exercicio->num_rows() > 0) {
                $data_exercicio = new DateTime($ultimo_exercicio->row()->data_envio);

                $data_atual = new DateTime(date("Y-m-d"));

                $dateInterval = $data_exercicio->diff($data_atual);

                if($dateInterval->days > $NUM_DIAS_DESISTENCIA) {
                    $this->crons_model->update_orcamento($orc->id, array("sincronizado" => "N", "ultima_atualizacao" => date("Y-m-d"), "status" => $LIG_ID_STATUS_DESISTENCIA));
                }
            }*/
            $user_ead = $this->crons_model->get_user_ead($orc->user_id)->row();
            if($user_ead) {
                if($user_ead->ultimo_acesso != "") {
                    if($_GET["data"]) {
                        $data = $_GET["data"];
                    } else {
                        $data = date("Y-m-d");
                    }

                    $data_ultimo_acesso = new DateTime($user_ead->ultimo_acesso);
                    $data_atual = new DateTime($data);

                    $dateInterval = $data_ultimo_acesso->diff($data_atual);

                    if(($dateInterval->days > $NUM_DIAS_DESISTENCIA) && $dateInterval->invert == 0) {
                        if($LIG_ID_STATUS_DESISTENCIA != $orc->status) {
                            $this->crons_model->update_orcamento($orc->id, array("sincronizado" => "N", "ultima_atualizacao" => date("Y-m-d"), "status" => $LIG_ID_STATUS_DESISTENCIA));
                            if($STATUS_HISTORICO != "") {
                                $this->crons_model->historico_create(array("LIG_ID_CLIENTE" => $orc->user_id, "LIG_ID_USUARIO" => 1, "REGISTRO" => "true", "STATUS" => $STATUS_HISTORICO, "MENSAGEM" => "Contrato '".$orc->id."' alterado para DESISTENTE.", "DATA_REGISTRO" => date("Y-m-d"), "HORA_REGISTRO" => date("H:i:s"), "empresa_id" => $orc->empresa_id));
                            }
                        }
                    } else {
                        if($LIG_ID_STATUS_ATIVO != $orc->status) {
                            $this->crons_model->update_orcamento($orc->id, array("sincronizado" => "N", "ultima_atualizacao" => date("Y-m-d"), "status" => $LIG_ID_STATUS_ATIVO));
                            if($STATUS_HISTORICO != "") {
                                $this->crons_model->historico_create(array("LIG_ID_CLIENTE" => $orc->user_id, "LIG_ID_USUARIO" => 1, "REGISTRO" => "true", "STATUS" => $STATUS_HISTORICO, "MENSAGEM" => "Contrato '".$orc->id."' alterado para ATIVO.", "DATA_REGISTRO" => date("Y-m-d"), "HORA_REGISTRO" => date("H:i:s"), "empresa_id" => $orc->empresa_id));
                            }
                        }
                    }
                }
            }
        }
    }

    private function calcular_evasao($orc, $evasao_id) {
        $status_anterior = $orc->status;
        $status_atual = $this->crons_model->get_orcamentos($orc->id)->row()->status;
        $this->crons_model->insert_evasao_orc(array("orc_id" => $orc->id, "evasao_id" => $evasao_id, "status_atual" => $status_atual, "status_anterior" => $status_anterior));

        if($status_atual == $orc->LIG_STATUS_CONTRATO_05 || $status_atual == $orc->LIG_ID_STATUS_DESISTENCIA || $status_atual == $orc->LIG_ID_STATUS_NUNCA_ACESSOU) {
            return false;
        } else {
            return true;
        }
    }

    private function has_evasao() {
        if($_GET["data"])
            $data = $_GET["data"];
        else
            $data = date('Y-m-d');

        return $this->crons_model->has_evasao($data);
    }

    private function get_evasao_id($resultado) {
        if($_GET["data"])
            $data = $_GET["data"];
        else
            $data = date('Y-m-d');


        $has_evasao = $this->crons_model->has_evasao($data);
        if($has_evasao)
            return $this->crons_model->get_evasao($data)->row()->id;
        else
            return $this->crons_model->insert_evasao($resultado, $data);
    }

    private function ativo_relatorio_tutor($empresa_id) {
        $ativo = $this->crons_model->ativo_relatorio_tutor($empresa_id);
        return $ativo;
    }

    private function ativo_relatorio_supervisor($empresa_id) {
        $ativo = $this->crons_model->ativo_relatorio_supervisor($empresa_id);
        return $ativo;
    }

    private function diff_data_relatorio_tutor($empresa_id) {
        $data_ultimo_relatorio = $this->crons_model->get_data_relatorio_tutor($empresa_id);
        if($data_ultimo_relatorio == "") {
            return true;
        }
        $data_ultimo_acesso = new DateTime($data_ultimo_relatorio);
        $data_atual = new DateTime(date("Y-m-d"));

        $dateInterval = $data_ultimo_acesso->diff($data_atual);

        if($dateInterval->days >= 7) {
            return true;
        }
        return false;
    }

    private function diff_data_relatorio_supervisor($empresa_id) {
        $data_ultimo_relatorio = $this->crons_model->get_data_relatorio_supervisor($empresa_id);
        if($data_ultimo_relatorio == "") {
            return true;
        }
        $data_ultimo_acesso = new DateTime($data_ultimo_relatorio);
        $data_atual = new DateTime(date("Y-m-d"));

        $dateInterval = $data_ultimo_acesso->diff($data_atual);

        if($dateInterval->days >= 7) {
            return true;
        }
        return false;
    }

    /*public function import_csv() {
        $nome_arquivo = "Bsl";
        $campos = array(
            "lead_id" => 0,
            "evento_id" => 4,
            "desc_evento" => 5,
            "nome" => 12,
            "email" => 13,
            "cidade" => 14,
            "celular" => 15,
            "mensagem" => 16
        );
        $mensagem = false;

        $handle = fopen("http://app.xkeysti.com.br/v2/upload/csv/".$nome_arquivo.".csv","r");
        
        $row = 0;
        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
            if($row > 0) {
                $data = array_map("utf8_encode", $data); 
                $data_explode = explode(";", $data[0]);

                $has_contato = $this->db->select('id')->where('lead_id', str_replace("l:", "", $data_explode[$campos["lead_id"]]))->get("contatos");

                if($has_contato->num_rows() == 0) {
                    $evento = $this->db->select('*')->where('fb_id', str_replace("as:", "", $data_explode[$campos["evento_id"]]))->get("eventos");
                    if($evento->num_rows() == 0) {
                        $this->db->insert("eventos", array("unidade_id" => 519, "midia_id" => 3, "fb_id" => str_replace("as:", "", $data_explode[$campos["evento_id"]]), "descricao" => $data_explode[$campos["desc_evento"]], "status" => 1));
                        $evento_id = $this->db->insert_id();
                    } else {
                        $evento_id = $evento->row()->id;
                    }

                    if($mensagem)
                        $this->db->insert("contatos", array("unidade_id" => 519, "evento_id" => $evento_id, "lead_id" => str_replace("l:", "", $data_explode[$campos["lead_id"]]), "nome" => $data_explode[$campos["nome"]], "email" => $data_explode[$campos["email"]], "cidade" => $data_explode[$campos["cidade"]], "celular" => str_replace("p:", "", $data_explode[$campos["celular"]]), "mensagem" => $data_explode[$campos["mensagem"]]));
                    else
                        $this->db->insert("contatos", array("unidade_id" => 519, "evento_id" => $evento_id, "lead_id" => str_replace("l:", "", $data_explode[$campos["lead_id"]]), "nome" => $data_explode[$campos["nome"]], "email" => $data_explode[$campos["email"]], "cidade" => $data_explode[$campos["cidade"]], "celular" => str_replace("p:", "", $data_explode[$campos["celular"]]), "mensagem" => ''));
                    
                    //echo "<pre>".print_r($data_explode,true)."</pre>";
                    //exit;
                }
            }
            $row++;
        }
        fclose($handle);
    }

    public function import_csv_unbounce() {
        $nome_arquivo = "unbounce";
        $campos = array(
            "lead_id" => 0,
            "evento_id" => 4,
            "desc_evento" => 5,
            "nome" => 12,
            "email" => 13,
            "cidade" => 14,
            "celular" => 15,
            "mensagem" => 16
        );
        $mensagem = false;

        $handle = fopen("http://app.xkeysti.com.br/v2/upload/csv/".$nome_arquivo.".csv","r");
        
        $row = 0;
        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
            if($row > 0) {
                $has_contato = $this->db->select('id')->where('evento_id', '1078')->where('celular', $data[8])->where('cidade', $data[9])->where('nome', $data[7])->get("contatos");
                
                if($has_contato->num_rows() == 0) {
                    $hora = explode(" ", $data[1]);
                    $this->db->insert("contatos", array("unidade_id" => 340, "evento_id" => 1078, "nome" => $data[7], "cidade" => $data[9], "celular" => $data[8], "created_at" => $data[0]." ".$hora[0].":00"));
                } 
                //else {
                //    echo "<pre>".print_r($data,true)."</pre>";
                //    exit;
                //}
                //if($row == 100) {
                //    echo "<pre>".print_r($data,true)."</pre>";
                //    exit;
                //}
            }
            $row++;
        }
        fclose($handle);
    }*/

    public function ler_contas_email() {

        $configs_saida = $this->crons_model->get_configs_saida();

        if($configs_saida->num_rows() > 0) {
            foreach ($configs_saida->result() as $config) {
                $mbox = imap_open("{".$config->host_saida.":".$config->port_saida."/".$config->protocol_saida."}", $config->user_saida, $config->pass_saida)or die("can't connect: " . imap_last_error());

                if($config->last_run_saida != "" && $config->last_run_saida > date("Y-m-d H:i:s", strtotime("-30 days"))) {
                    $data_filtro = date("j F Y", strtotime(date("Y-m-d H:i:s", strtotime($config->last_run_saida))));
                    $data_check = $config->last_run_saida;
                } else {
                    $data_filtro = date("j F Y");
                    $data_check = date("Y-m-d")." 00:00:00";
                }

                $emails = imap_search($mbox, 'SINCE "'.$data_filtro.'"');

                if ($emails) {
                    foreach ($emails as $n) {
                        $overview = imap_fetch_overview($mbox, $n);
                        $email = current($overview);
						
						
						if(date('Y-m-d H:i:s', strtotime($email->date)) > $data_check && $this->decode_header($email->subject) != "Undelivered Mail Returned to Sender") {
							
                            $header = imap_headerinfo($mbox, $n);
                            $fromaddr = $header->from[0]->mailbox . "@" . $header->from[0]->host;

                            if (filter_var($fromaddr, FILTER_VALIDATE_EMAIL)) {
							
                                $user = $this->crons_model->get_user_by_email($fromaddr);
                                
                                if($user->num_rows() > 0) {
                                    $usuario_selecionado = $user->row();
									$user_unidade_id = $this->crons_model->insert_user_unidade(array("user_id" => $usuario_selecionado->id, "unidade_id" => $config->unidade_padrao, "padrao" => 1));

                                    if($usuario_selecionado->active == 0 && $config->empresa_id != 12) {
                                        //enviar email -----------------------------------------------
                                        $message = '<h1>Ativar conta para '.$fromaddr.'</h1><p>Por favor, clique neste link para <a href="'.base_url('/auth/activate/'.$usuario_selecionado->id.'/'.$usuario_selecionado->activation_code).'">Ativar sua conta</a>.</p>';

                                        if($config->test == 1) {
                                            $email_config = rowToConfigEmail($config);
                                            $from_email = $config->from_email;
                                            $from_name = $config->from_name;
                                        } else {
                                            $email_config = $this->config->item('email_config', 'ion_auth');
                                            $from_email = $this->config->item('admin_email', 'ion_auth');
                                            $from_name = $this->config->item('site_title', 'ion_auth');
                                        }

                                        // if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
                                        // {
                                        //     $this->email->initialize($email_config);
                                        // }

                                        // $this->email->clear(true);
                                        // $this->email->from($from_email, utf8_decode($from_name));

                                        // $emails_to = array($fromaddr);

                                        // $this->email->to(array_unique($emails_to));
                                        // $this->email->subject(utf8_decode('MaestriaTech - Ativação de conta'));
                                        // $this->email->message(utf8_decode($message));
                                        // $this->email->set_mailtype("html");
                                        // $this->email->send();

                                        $mail = $this->phpmailer_library->load();
                                        $mail->isSMTP();
                                        $mail->Host     = $email_config['smtp_host'];
                                        $mail->SMTPAuth = true;
                                        $mail->Username = $email_config['smtp_user'];
                                        $mail->Password = $email_config['smtp_pass'];
                                        $mail->SMTPSecure = 'tsl';
                                        $mail->Port     = $email_config['smtp_port'];

                                        $mail->setFrom($from_email, utf8_decode($from_name));
                                        $emails_to = array($fromaddr);
                                        $mail->addAddress(array_unique($emails_to));

                                        $mail->Subject = utf8_decode('MaestriaTech - Ativação de conta');
                                        $mail->isHTML(true);

                                        $mail->Body = utf8_decode($message);
                                        $mail->send();
                                        //enviar email -----------------------------------------------
                                    }
                                } else {
                                    //if cadastrar usuário is true
                                    if($config->cadastrar_usu == 1) {
	
										if($config->empresa_id == 12) {
                                            $user_id = $this->crons_model->insert_user(array("email" => $fromaddr, "active" => "1", "created_on" => time(), "activation_code" => NULL, 'first_name' => $header->from[0]->personal, 'password' => $this->ion_auth_model->hash_password("leaoleao")));
                                        } else {
											$user_id = $this->crons_model->insert_user(array("email" => $fromaddr, "active" => "0", "created_on" => time(), "activation_code" => sha1(md5(microtime()))));
										}
										
                                        $dados_new_user = $this->crons_model->get_user_by_id($user_id)->row();

                                        $empresas_users_id = $this->crons_model->insert_empresas_users(array("empresa_id" => $config->empresa_id, "user_id" => $user_id));
                                        $user_unidade_id = $this->crons_model->insert_user_unidade(array("user_id" => $user_id, "unidade_id" => $config->unidade_padrao, "padrao" => 1));
                                        //$pessoas_id = $this->crons_model->insert_pessoas(array("user_id" => $user_id, "empresa_id" => "0", "tipo" => "F"));
                                        $pessoas_id = $this->crons_model->insert_pessoas(array("user_id" => $user_id));

                                        //enviar email -----------------------------------------------
                                        if($config->empresa_id != 12) {
											$message = '<h1>Ativar conta para '.$fromaddr.'</h1><p>Por favor, clique neste link para <a href="'.base_url('/auth/activate/'.$user_id.'/'.$dados_new_user->activation_code).'">Ativar sua conta</a>.</p>';

											if($config->test == 1) {
												$email_config = rowToConfigEmail($config);
												$from_email = $config->from_email;
												$from_name = $config->from_name;
											} else {
												$email_config = $this->config->item('email_config', 'ion_auth');
												$from_email = $this->config->item('admin_email', 'ion_auth');
												$from_name = $this->config->item('site_title', 'ion_auth');
											}

											// if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
											// {
											// 	$this->email->initialize($email_config);
											// }

											// $this->email->clear(true);
											// $this->email->from($from_email, utf8_decode($from_name));

											// $emails_to = array($fromaddr);

											// $this->email->to(array_unique($emails_to));
											// $this->email->subject(utf8_decode('MaestriaTech - Ativação de conta'));
											// $this->email->message(utf8_decode($message));
											// $this->email->set_mailtype("html");
											// $this->email->send();

                                            $mail = $this->phpmailer_library->load();
                                            $mail->isSMTP();
                                            $mail->Host     = $email_config['smtp_host'];
                                            $mail->SMTPAuth = true;
                                            $mail->Username = $email_config['smtp_user'];
                                            $mail->Password = $email_config['smtp_pass'];
                                            $mail->SMTPSecure = 'tsl';
                                            $mail->Port     = $email_config['smtp_port'];

                                            $mail->setFrom($from_email, utf8_decode($from_name));
                                            $emails_to = array($fromaddr);
                                            $mail->addAddress(array_unique($emails_to));

                                            $mail->Subject = utf8_decode('MaestriaTech - Ativação de conta');
                                            $mail->isHTML(true);

                                            $mail->Body = utf8_decode($message);
                                            $mail->send();
											//enviar email -----------------------------------------------
										}

                                        $usuario_selecionado = $dados_new_user;

                                    } else {
                                        //exit;
                                    }
                                }
                                
                                $unidade_padrao = $this->crons_model->get_unid_padrao_usu($usuario_selecionado->id, $config->empresa_id);
                                if($unidade_padrao->num_rows() > 0) {
                                    $assunto_decode = $this->decode_header($email->subject);
                                    $texto_verif_chamado = $assunto_decode;
                                    $texto_verif_chamado = explode(' #', $texto_verif_chamado);
                                    $texto_verif_chamado = explode(' - ', @$texto_verif_chamado[1]);

                                    $structure = imap_fetchstructure($mbox, $n);

                                    $attachments = array();
                                    $has_attachments = false;
                                    if(isset($structure->parts) && count($structure->parts)) {
                                        for($i = 0; $i < count($structure->parts); $i++) {
                                            $attachments[$i] = array(
                                              'is_attachment' => false,
                                              'filename' => '',
                                              'name' => '',
                                              'attachment' => '');

                                            if($structure->parts[$i]->ifdparameters) {
                                                foreach($structure->parts[$i]->dparameters as $object) {
                                                    if(strtolower($object->attribute) == 'filename') {
                                                        $attachments[$i]['is_attachment'] = true;
                                                        $attachments[$i]['filename'] = $object->value;
                                                    }
                                                }
                                            }

                                            if($structure->parts[$i]->ifparameters) {
                                                foreach($structure->parts[$i]->parameters as $object) {
                                                    if(strtolower($object->attribute) == 'name') {
                                                        $attachments[$i]['is_attachment'] = true;
                                                        $attachments[$i]['name'] = $object->value;
                                                    }
                                                }
                                            }

                                            if($attachments[$i]['is_attachment']) {
                                                $attachments[$i]['attachment'] = imap_fetchbody($mbox, $n, $i+1);
                                                if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                                                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                                }
                                                elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                                                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                                }
                                            }
                                        }
                                    }

                                    $body = preg_replace( "/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags(quoted_printable_decode(imap_fetchbody($mbox, $n, "1.1"))))) );
                                    // $body = strip_tags(str_replace(array("<i>", "</i>"), array("_", "_"), quoted_printable_decode(imap_fetchbody($mbox, $n, "1.2"))));
                                    // $body = str_replace("|a", "<a", strip_tags(str_replace("<a", "|a", quoted_printable_decode(imap_fetchbody($mbox, $n, "1.2")))));

                                    if ($body == "") {
                                        $body = quoted_printable_decode(imap_fetchbody($mbox, $n, 1));
                                    }

                                    if(!$this->crons_model->has_chamado(@$texto_verif_chamado[0])) {
                                        $data = array(
                                            "categoria_id" => 5,
                                            "unidade_id" => $unidade_padrao->row()->unidade_id,
                                            "dono_id" => $usuario_selecionado->id,
                                            "status_id" => 1,
                                            "atualizado_por" => $usuario_selecionado->id,
                                            "responsavel_id" => null,
                                            "titulo" => $assunto_decode,
                                            // "descricao" => nl2br(htmlentities(quoted_printable_decode(imap_body($mbox, $n))))
                                            "descricao" => $body
                                        );
                                        $chamado_id = $this->crons_model->create_chamado($data);

                                        if(count($attachments)!=0){
                                            foreach($attachments as $at){
                                                if($at['is_attachment']==1){
                                                    $has_attachments = true;
                                                    file_put_contents(FCPATH.'upload/anexo/'.$at['filename'], $at['attachment']);
                                                    $this->crons_model->create_chamado_anexo(array("chamado_id" => $chamado_id, "responsavel_id" => $usuario_selecionado->id, "anexo" => $at['filename']));
                                                }
                                            }
                                        }
                                        
                                        // exit($this->db->last_query());
                                        // echo "<pre>".print_r($email->subject,true)."</pre>";
                                        // echo "<pre>".print_r(nl2br(htmlentities(quoted_printable_decode(imap_body($mbox, $n)))),true)."</pre>";
                                    } else {
                                        $chamado = $this->crons_model->get_chamado($texto_verif_chamado[0])->row();

                                        if(count($attachments)!=0){
                                            foreach($attachments as $at){
                                                if($at['is_attachment']==1){
                                                    $has_attachments = true;
                                                    file_put_contents(FCPATH.'upload/anexo/'.$at['filename'], $at['attachment']);
                                                    $this->crons_model->create_chamado_anexo(array("chamado_id" => $texto_verif_chamado[0], "responsavel_id" => $usuario_selecionado->id, "descricao" => $body, "anexo" => $at['filename']));
                                                }
                                            }
                                        }

                                        if($has_attachments == false) {
                                            $this->crons_model->create_chamado_dialogo(array("chamado_id" => $texto_verif_chamado[0], "responsavel_id" => $usuario_selecionado->id, "descricao" => $body));
                                        }

                                        $timeline = $this->gerarTimeLineEmail($texto_verif_chamado[0]);

                                        $email_config_db = $this->ion_auth_model->get_email_config($config->empresa_id)->row();

                                        if($email_config_db->test == 1) {
                                            $email_config = rowToConfigEmail($email_config_db);
                                            $from_email = $email_config_db->from_email;
                                            $from_name = $email_config_db->from_name;
                                        } else {
                                            $from_email = $this->config->item('admin_email', 'ion_auth');
                                            $from_name = $this->config->item('site_title', 'ion_auth');
                                            $email_config = $this->config->item('email_config', 'ion_auth');
                                        }

                                        // if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
                                        // {
                                        //     $this->email->initialize($email_config);
                                        // }

                                        if($has_attachments == true)
                                            $padrao_email = $this->crons_model->get_email_padrao($config->empresa_id, 7)->row();
                                        else
                                            $padrao_email = $this->crons_model->get_email_padrao($config->empresa_id, 6)->row();
                                        $padrao_email_msg = $padrao_email->mensagem;
                                        $padrao_email_msg = str_replace("{link_chamado}", $this->config->base_url('chamados/details/'.$texto_verif_chamado[0]), $padrao_email_msg);
                                        $padrao_email_msg = str_replace("{chamado_id}", $texto_verif_chamado[0], $padrao_email_msg);
                                        $padrao_email_msg = str_replace("{unidade_desc}", $unidade_padrao->row()->unidade, $padrao_email_msg);
                                        $padrao_email_msg = str_replace("{timeline}", generate_timeline_html($timeline), $padrao_email_msg);  

                                        $padrao_email_ass = $padrao_email->assunto;
                                        $padrao_email_ass = str_replace("{chamado_id}", $texto_verif_chamado[0], $padrao_email_ass);
                                        $padrao_email_ass = str_replace("{titulo}", $assunto_decode, $padrao_email_ass);

                                        // $this->email->clear();
                                        // $this->email->from($from_email, utf8_decode($from_name));

                                        // $emails_to = array();
                                        // $emails_to[] = $chamado->email;

                                        // $this->email->to(array_unique($emails_to));
                                        // $this->email->subject(utf8_decode($padrao_email_ass));
                                        // $this->email->message(utf8_decode($padrao_email_msg));
                                        // $this->email->send();

                                        $mail = $this->phpmailer_library->load();
                                        $mail->isSMTP();
                                        $mail->Host     = $email_config['smtp_host'];
                                        $mail->SMTPAuth = true;
                                        $mail->Username = $email_config['smtp_user'];
                                        $mail->Password = $email_config['smtp_pass'];
                                        $mail->SMTPSecure = 'tsl';
                                        $mail->Port     = $email_config['smtp_port'];

                                        $mail->setFrom($from_email, utf8_decode($from_name));
                                        $emails_to = array();
                                        $emails_to[] = $chamado->email;
                                        $mail->addAddress(array_unique($emails_to));

                                        $mail->Subject = utf8_decode($padrao_email_ass);
                                        $mail->isHTML(true);

                                        $mail->Body = utf8_decode($padrao_email_msg);
                                        $mail->send();
                                    }
                                }

                            }
                        }
                    }
                }
                imap_close($mbox);

                $this->crons_model->update_config_email($config->id, array("last_run_saida" => date("Y-m-d H:i:s")));
            }
        }
    }

    public function lembrete_avaliacao() {
        $this->crons_model->update_chamados_em_avaliacao();
        
        $chamados = $this->crons_model->get_chamados_em_avaliacao();

        $count = 0;
        $count_total = count($chamados);
        $last_user = 0;
        $array_chamados = array();
        $last_chamado = null;

        // exit("<pre>".print_r($chamados,true)."</pre>");
        foreach ($chamados as $chamado) {
            if($last_user != $chamado->dono_id && $last_user != 0) {
                $data = array(
                    "dono" => $last_chamado->first_name." ".$last_chamado->last_name,
                    "chamados" => $array_chamados
                );
                $message = $this->load->view("admin/email/lembrete_avaliacao.tpl.php", $data, true);
                // echo $message."<hr>";
                $email_config_db = $this->ion_auth_model->get_email_config($last_chamado->empresa_id);

                if(@$email_config_db->test == 1) {
                    $email_config = rowToConfigEmail($email_config_db);
                    $from_email = $email_config_db->from_email;
                    $from_name = $email_config_db->from_name;
                } else {
                    $email_config = $this->config->item('email_config', 'ion_auth');
                    $from_email = $this->config->item('admin_email', 'ion_auth');
                    $from_name = $this->config->item('site_title', 'ion_auth');
                }

                // if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
                // {
                //     $this->email->initialize($email_config);
                // }

                // $this->email->clear();
                // $this->email->from($from_email, utf8_decode($from_name));

                // $emails_to = array($last_chamado->email);

                // $this->email->to(array_unique($emails_to));
                // $this->email->subject(utf8_decode("Lembrete de avaliação de chamado"));
                // $this->email->message(utf8_decode($message));
                // $this->email->set_mailtype("html");
                // $this->email->send();

                $mail = $this->phpmailer_library->load();
                $mail->isSMTP();
                $mail->Host     = $email_config['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $email_config['smtp_user'];
                $mail->Password = $email_config['smtp_pass'];
                $mail->SMTPSecure = 'tsl';
                $mail->Port     = $email_config['smtp_port'];

                $mail->setFrom($from_email, utf8_decode($from_name));
                $emails_to = array($last_chamado->email);
                $mail->addAddress(array_unique($emails_to));

                $mail->Subject = utf8_decode("Lembrete de avaliação de chamado");
                $mail->isHTML(true);

                $mail->Body = utf8_decode($message);
                $mail->send();

                $array_chamados = array();
            }
                
            $array_chamados[] = $chamado;
            $count++;
            $last_user = $chamado->dono_id;

            if($count == $count_total) {
                $data = array(
                    "dono" => $chamado->first_name." ".$chamado->last_name,
                    "chamados" => $array_chamados
                );
                $message = $this->load->view("admin/email/lembrete_avaliacao.tpl.php", $data, true);
                // echo $message."<hr>";
                $email_config_db = $this->ion_auth_model->get_email_config($chamado->empresa_id);

                if(@$email_config_db->test == 1) {
                    $email_config = rowToConfigEmail($email_config_db);
                    $from_email = $email_config_db->from_email;
                    $from_name = $email_config_db->from_name;
                } else {
                    $email_config = $this->config->item('email_config', 'ion_auth');
                    $from_email = $this->config->item('admin_email', 'ion_auth');
                    $from_name = $this->config->item('site_title', 'ion_auth');
                }

                // if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
                // {
                //     $this->email->initialize($email_config);
                // }

                // $this->email->clear();
                // $this->email->from($from_email, utf8_decode($from_name));

                // $emails_to = array($chamado->email);

                // $this->email->to(array_unique($emails_to));
                // $this->email->subject(utf8_decode("Lembrete de avaliação de chamado"));
                // $this->email->message(utf8_decode($message));
                // $this->email->set_mailtype("html");
                // $this->email->send();

                $mail = $this->phpmailer_library->load();
                $mail->isSMTP();
                $mail->Host     = $email_config['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $email_config['smtp_user'];
                $mail->Password = $email_config['smtp_pass'];
                $mail->SMTPSecure = 'tsl';
                $mail->Port     = $email_config['smtp_port'];

                $mail->setFrom($from_email, utf8_decode($from_name));
                $emails_to = array($chamado->email);
                $mail->addAddress(array_unique($emails_to));

                $mail->Subject = utf8_decode("Lembrete de avaliação de chamado");
                $mail->isHTML(true);

                $mail->Body = utf8_decode($message);
                $mail->send();
            }
            $last_chamado = $chamado;
        }
    }

    private function gerarTimeLineEmail($id) {
        $i = 0;
        $dialogos = array();
        foreach ($this->crons_model->get_dialogos($id)->result() as $value) {
            $dialogos[$i]['id'] = $value->id;
            $dialogos[$i]['chamado_id'] = $value->chamado_id;
            $dialogos[$i]['responsavel_id'] = $value->responsavel_id;
            $dialogos[$i]['descricao'] = $value->descricao;
            $dialogos[$i]['created'] = $value->data_envio;
            $dialogos[$i]['data_leitura'] = $value->data_leitura;
            $dialogos[$i]['first_name'] = $value->first_name;
            $dialogos[$i]['last_name'] = $value->last_name;
            $i++;
        }
        $anexos = $this->crons_model->get_anexos($id)->result_array();

        $timeline = array_merge($dialogos, $anexos);

        usort($timeline, function($a, $b) {
            return strtotime($b['created']) - strtotime($a['created']);
        });

        return $timeline;
    }

    public static function is_encoded_header($header) {
        // e.g. =?utf-8?q?Re=3a=20Support=3a=204D09EE9A=20=2d=20Re=3a=20Support=3a=204D078032=20=2d=20Wordpress=20Plugin?=
        // e.g. =?utf-8?q?Wordpress=20Plugin?=
        return preg_match(self::rfc2047header, $header) !== 0;
    }

    public static function header_charsets($header) {
        $matches = null;
        if (!preg_match_all(self::rfc2047header, $header, $matches, PREG_PATTERN_ORDER)) {
            return array();
        }
        return array_map('strtoupper', $matches[1]);
    }

    public static function decode_header($header) {
        $matches = null;

        /* Repair instances where two encodings are together and separated by a space (strip the spaces) */
        $header = preg_replace(self::rfc2047header_spaces, "$1$2", $header);

        /* Now see if any encodings exist and match them */
        if (!preg_match_all(self::rfc2047header, $header, $matches, PREG_SET_ORDER)) {
            return $header;
        }
        foreach ($matches as $header_match) {
            list($match, $charset, $encoding, $data) = $header_match;
            $encoding = strtoupper($encoding);
            switch ($encoding) {
                case 'B':
                    $data = base64_decode($data);
                    break;
                case 'Q':
                    $data = quoted_printable_decode(str_replace("_", " ", $data));
                    break;
                default:
                    throw new Exception("preg_match_all is busted: didn't find B or Q in encoding $header");
            }
            // This part needs to handle every charset
            switch (strtoupper($charset)) {
                case "UTF-8":
                    break;
                default:
                    $data = utf8_encode($data);
                    /* Here's where you should handle other character sets! */
                    //throw new Exception("Unknown charset in header - time to write some code.");
            }
            $header = str_replace($match, $data, $header);
        }
        return $header;
    }

    public function chamado_apontamentos() {
        $admin_prefs = $this->crons_model->get_admin_prefs();

        foreach ($admin_prefs->result() as $admin_pref) {

            $grupos = unserialize($admin_pref->relatorio_apontamentos);
            
            if(is_array($grupos)) {
                foreach ($grupos as $g) {
                    $usuarios_grupo = $this->crons_model->get_users_by_group($g);

                    if($usuarios_grupo->num_rows() > 0) {
                        foreach ($usuarios_grupo->result() as $usu) {
                            $emails_to[] = $usu->email;
                        }

                        $data['apontamentos'] = $this->crons_model->get_apontamento("un.empresa_id = ".$admin_pref->empresa_id." AND (a.data BETWEEN '".date('Y-m-d', strtotime("-7 days"))."' AND '".date('Y-m-d')."')");

                        $users = array();
                        foreach ($data['apontamentos']->result() as $apont) {
                            if(!in_array($apont->user_id, $users))
                                $users[] = $apont->user_id;
                        }

                        $datas_acumulado = array();
                        for($i = 5; $i >= 0; $i--) {
                            $date = date("m-Y", strtotime(date('Y-m-d').' -'.$i.' months'));
                            $date_explode = explode("-", $date);
                            $datas_acumulado[$i]["extenso"] = get_mes_extenso($date_explode[0])."/".$date_explode[1];
                            $datas_acumulado[$i]["code"] = $date;
                        }
                        $data['datas_acumulado'] = $datas_acumulado;

                        $total_esforco = array();
                        foreach ($users as $user) {
                            $total_esforco[$user] = $this->get_total_esforco($user);

                            foreach ($datas_acumulado as $dt) {
                                $total_acumulado[$dt["code"]][$user] = $this->crons_model->get_acumulado_mes($dt["code"], $user);
                            }
                        }

                        $data['total_esforco'] = $total_esforco;
                        $data['total_acumulado'] = $total_acumulado;

                        $message = $this->load->view("admin/email/apontamentos.tpl.php", $data, true);
                        //exit($message);
                        //exit("<pre>".print_r($emails_to,true)."</pre>");

                        $email_config_db = $this->ion_auth_model->get_email_config($admin_pref->empresa_id)->row();

                        if($email_config_db->test == 1) {
                            $email_config = rowToConfigEmail($email_config_db);
                            $from_email = $email_config_db->from_email;
                            $from_name = $email_config_db->from_name;
                        } else {
                            $from_email = $this->config->item('admin_email', 'ion_auth');
                            $from_name = $this->config->item('site_title', 'ion_auth');
                            $email_config = $this->config->item('email_config', 'ion_auth');
                        }

                        // if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
                        // {
                        //     $this->email->initialize($email_config);
                        // }

                        // $this->email->clear();
                        // $this->email->from($from_email, utf8_decode($from_name));

                        // $this->email->to(array_unique($emails_to));
                        // $this->email->subject(utf8_decode("Apontamentos"));
                        // $this->email->message(utf8_decode($message));
                        // $this->email->set_mailtype("html");
                        // $this->email->send();

                        $mail = $this->phpmailer_library->load();
                        $mail->isSMTP();
                        $mail->SMTPDebug = 1;
                        $mail->Host     = $email_config['smtp_host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $email_config['smtp_user'];
                        $mail->Password = $email_config['smtp_pass'];
                        $mail->SMTPSecure = 'tsl';
                        $mail->Port     = $email_config['smtp_port'];

                        $mail->setFrom($from_email, utf8_decode($from_name));

                        foreach($emails_to as $eto)
                            $mail->addAddress($eto);

                        $mail->Subject = utf8_decode("Apontamentos");
                        $mail->isHTML(true);

                        $mail->Body = utf8_decode($message);
                        
                        $mail->send();
                    }
                }
            }
        }
    }

    /**
     * Envia por mensagem direta no Discord os chamados atrasados e programados para o dia
     * de cada colaborador com Discord cadastrado no planejamento do Kanban.
     *
     * Uso (dias úteis às 08h): CI_ENV=production php index.php crons kanban_discord
     * Teste sem enviar:        php index.php crons kanban_discord teste
     */
    public function kanban_discord($mode = '') {
        if (!is_cli()) {
            show_404();
            return;
        }
        $dry_run = $mode === 'teste';
        $this->load->model('kanban_model');
        $this->load->model('kanban_dashboard_model');
        $this->load->library('discord_notifier');

        $today = date('Y-m-d');
        if ((int) date('N') > 5) {
            echo "Fim de semana: nenhuma mensagem enviada.\n";
            return;
        }
        if (!$this->kanban_dashboard_model->has_discord_tables()) {
            echo "Tabelas do Discord não encontradas. Execute install/sql/kanban_discord.sql.\n";
            return;
        }
        if (!$dry_run && !$this->discord_notifier->is_configured()) {
            echo "DISCORD_BOT_TOKEN não configurado.\n";
            return;
        }

        $status_names = array('aberto', 'fazendo', 'em andamento', 'aguardando testes com o cliente', 'aguardando teste com o cliente');
        $status_ids = array();
        foreach ($this->db->select('id, descricao')->get('chamado_status')->result() as $status) {
            if (in_array(mb_strtolower(trim($status->descricao), 'UTF-8'), $status_names, true)) {
                $status_ids[] = (int) $status->id;
            }
        }

        $sent = 0;
        foreach ($this->kanban_dashboard_model->get_discord_companies() as $company_id) {
            $discord_ids = $this->kanban_dashboard_model->get_discord_ids($company_id);
            foreach ($this->kanban_dashboard_model->get_company_users($company_id) as $collaborator) {
                $collaborator_id = (int) $collaborator->id;
                if (empty($discord_ids[$collaborator_id]) ||
                    $this->kanban_dashboard_model->was_discord_sent($company_id, $collaborator_id, $today) ||
                    $this->kanban_dashboard_model->is_date_unavailable($company_id, $collaborator_id, $today)) {
                    continue;
                }

                $overdue = $this->kanban_model->get_chamados_atrasados($collaborator_id, $company_id, $status_ids);
                $dashboard = $this->kanban_dashboard_model->get_dashboard(
                    $company_id, array($collaborator_id), $collaborator_id, $today, $today, $status_ids
                );
                $day = isset($dashboard['days'][$today]) ? $dashboard['days'][$today] : null;
                $today_tasks = $day ? $day['tasks'] : array();
                if (!$overdue && !$today_tasks) {
                    continue;
                }

                $message = $this->build_kanban_discord_message($collaborator->nome, $overdue, $today_tasks, $day);
                if ($dry_run) {
                    echo "----- " . $collaborator->nome . " (" . $discord_ids[$collaborator_id] . ")\n" . $message . "\n";
                    continue;
                }
                if ($this->discord_notifier->send_direct_message($discord_ids[$collaborator_id], $message)) {
                    $this->kanban_dashboard_model->mark_discord_sent($company_id, $collaborator_id, $today);
                    $sent++;
                } else {
                    log_message('error', 'Kanban Discord: falha ao enviar para o colaborador ' . $collaborator_id . ' — ' . $this->discord_notifier->last_error());
                    echo "Falha ao enviar para " . $collaborator->nome . ": " . $this->discord_notifier->last_error() . "\n";
                }
            }
        }
        if (!$dry_run) {
            echo $sent . " mensagem(ns) enviada(s).\n";
        }
    }

    private function build_kanban_discord_message($name, $overdue, $today_tasks, $day) {
        $escape = function ($text) {
            return addcslashes((string) $text, '\\*_~`|>[]');
        };
        $ticket_link = function ($id) {
            return '[#' . (int) $id . '](<' . base_url('chamados/details/' . (int) $id) . '>)';
        };
        $hours = function ($value) {
            return number_format($value, 1, ',', '.') . ' h';
        };
        $name_parts = explode(' ', trim($name));
        $footer = "\n🔗 Abrir planejamento: <" . base_url('kanban/planejamento') . ">";

        $title = function ($text) use ($escape) {
            $text = trim((string) $text);
            return $escape(mb_strlen($text, 'UTF-8') > 70 ? rtrim(mb_substr($text, 0, 67, 'UTF-8')) . '...' : $text);
        };
        $overdue_ids = array();
        foreach ($overdue as $ticket) {
            $overdue_ids[(int) $ticket->id] = true;
        }

        // Os programados para hoje vêm primeiro; cada seção tem limite de itens para caber em uma mensagem.
        $sections = array();
        if ($today_tasks) {
            $items = array();
            foreach ($today_tasks as $task) {
                $items[] = '• ' . $ticket_link($task->chamado_id) . ' ' . $title($task->titulo) .
                    ($task->prioridade ? ' [' . $escape($task->prioridade) . ']' : '') .
                    (isset($overdue_ids[(int) $task->chamado_id]) ? ' ⚠️ atrasado' : '');
            }
            $sections[] = array(
                'header' => '📅 **Programados para hoje (' . count($today_tasks) . ')** — ' .
                    $hours($day['planned_hours']) . ' estimadas / meta ' . $hours($day['goal_hours']),
                'items' => $items,
                'limit' => 12,
            );
        }
        if ($overdue) {
            $items = array();
            foreach ($overdue as $ticket) {
                $items[] = '• ' . $ticket_link($ticket->id) . ' ' . $title($ticket->titulo) .
                    ' — prazo ' . date('d/m/Y', strtotime($ticket->data_prazo));
            }
            $sections[] = array(
                'header' => '⚠️ **Atrasados (' . count($overdue) . ')**',
                'items' => $items,
                'limit' => 8,
            );
        }

        $message = '📋 **Bom dia, ' . $escape($name_parts[0]) . '** — pendências de ' . date('d/m/Y');
        foreach ($sections as $section) {
            $message .= "\n\n" . $section['header'];
            $shown = 0;
            foreach ($section['items'] as $item) {
                $more = "\n… e mais " . (count($section['items']) - $shown) . ' no planejamento.';
                if ($shown >= $section['limit'] ||
                    mb_strlen($message . "\n" . $item . $more . "\n" . $footer, 'UTF-8') > Discord_notifier::MAX_MESSAGE_LENGTH - 200) {
                    $message .= $more;
                    break;
                }
                $message .= "\n" . $item;
                $shown++;
            }
        }
        return $message . "\n" . $footer;
    }


        private function get_total_esforco($user) {
        $times = array();
        $apontamentos = $this->crons_model->get_apontamento("user_id = ".$user." AND (data BETWEEN '".date('Y-m-d', strtotime("-7 days"))."' AND '".date('Y-m-d')."')");
        
        foreach ($apontamentos->result() as $apont) {
            $times[] = $apont->esforco;
        }
        return AddPlayTime($times);
    }

    private function alert_failed_day() {
        if(!$this->crons_model->has_evasao(date('Y-m-d', strtotime("-1 day")))) {
            $from_email = $this->config->item('admin_email', 'ion_auth');
            $from_name = $this->config->item('site_title', 'ion_auth');
            $email_config = $this->config->item('email_config', 'ion_auth');

            // if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
            // {
            //     $this->email->initialize($email_config);
            // }

            // $this->email->clear();
            // $this->email->from($from_email, utf8_decode($from_name));

            // $emails_to = array("thiago@maestriatech.com.br", "rodney@maestriatech.com.br");

            // $this->email->to(array_unique($emails_to));
            // $this->email->subject(utf8_decode("Aviso Evasão MaestriaTech!"));
            // $this->email->message(utf8_decode("Verificar evasão do dia: ".date('Y-m-d', strtotime("-1 day"))));
            // $this->email->set_mailtype("html");
            // $this->email->send();

            $mail = $this->phpmailer_library->load();
            $mail->isSMTP();
            $mail->Host     = $email_config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $email_config['smtp_user'];
            $mail->Password = $email_config['smtp_pass'];
            $mail->SMTPSecure = 'tsl';
            $mail->Port     = $email_config['smtp_port'];

            $mail->setFrom($from_email, utf8_decode($from_name));
            $mail->addAddress("thiago@maestriatech.com.br");
            $mail->addAddress("rodney@maestriatech.com.br");

            $mail->Subject = utf8_decode("Aviso Evasão MaestriaTech!");
            $mail->isHTML(true);

            $mail->Body = utf8_decode("Verificar evasão do dia: ".date('Y-m-d', strtotime("-1 day")));
            $mail->send();
        }
    }

    public function create_cliente_tiny() {

        $this->load->library('Curl');

        $tini_api = $this->crons_model->get_tini_api();

        if($tini_api->num_rows() > 0) {

            $empresa_id = 12;
            $unidade_id = 647;
            $group_id = 68;

            foreach ($tini_api->result() as $value) {

                $parametros = array(
                    'formato' => 'json', 
                    'token' => $value->token, 
                    'dataCriacao' => date("d/m/Y H:i:s", strtotime($value->ultima_data_clientes))
                );
                
                $retorno = $this->curl->post('https://api.tiny.com.br/api2/contatos.pesquisa.php', $parametros);
                $retorno = json_decode($retorno, 1);

                if($retorno["retorno"]["status_processamento"] == 3) {

                    $ultima_data_clientes = $value->ultima_data_clientes;

                    foreach ($retorno["retorno"]["contatos"] as $contato) {
                        $contato["contato"]["empresa_id"] = $empresa_id;
                        $contato["contato"]["unidade_id"] = $unidade_id;
                        $contato["contato"]["group_id"] = $group_id;
                        $this->inserir_cliente($contato["contato"]);
                    }

                    if($retorno["retorno"]["numero_paginas"] > 1) {

                        $pagina_atual = 2;
                        
                        while ($pagina_atual <= $retorno["retorno"]["numero_paginas"]) {
                            
                            $parametros = array(
                                'pagina' => $pagina_atual,
                                'formato' => 'json', 
                                'token' => $value->token, 
                                'dataCriacao' => date("d/m/Y H:i:s", strtotime($value->ultima_data_clientes))
                            );

                            $retorno = $this->curl->post('https://api.tiny.com.br/api2/contatos.pesquisa.php', $parametros);
                            $retorno = json_decode($retorno, 1);
                            foreach ($retorno["retorno"]["contatos"] as $contato) {
                                $contato["contato"]["empresa_id"] = $empresa_id;
                                $contato["contato"]["unidade_id"] = $unidade_id;
                                $contato["contato"]["group_id"] = $group_id;
                                $this->inserir_cliente($contato["contato"]);

                            }

                            $pagina_atual++;
                        }
                    }

                    $this->crons_model->update_tini_api($value->id, array("ultima_data_clientes" => date("Y-m-d H:i:s")));
                }
            }
        }

        //exit("<pre>".print_r(json_decode($retorno, 1),true)."</pre>");
    }

    private function inserir_cliente($dados) {
        if($dados["tipo_pessoa"] == "J" && $dados["cpf_cnpj"] != "") {
            $data = array(
                "empresa_id" => $dados["empresa_id"],
                "unidade" => (@$dados["fantasia"]) ? $dados["fantasia"] : $dados["nome"],
                "email" => $dados["email"],
                "tipo" => "J",
                "endereco" => $dados["endereco"].", ".$dados["bairro"].", ".$dados["numero"],
                "rua" => $dados["endereco"],
                "numero" => $dados["numero"],
                "bairro" => $dados["bairro"],
                "cnpj" => $dados["cpf_cnpj"],
                "razao_social" => $dados["nome"],
                "cidade" => $dados["cidade"],
                "uf" => $dados["uf"],
                "cep" => str_replace(".", "", $dados["cep"]),
                "telefone1" => $dados["fone"],
                "caracteristica" => 0,
                "actived" => ($dados["situacao"] == "Ativo") ? "s" : "n"
            );

            if($this->crons_model->insert_unidade($data)) {
                return true;
            }
        } elseif($dados["tipo_pessoa"] == "F" && $dados["cpf_cnpj"] != "") {

            $explode_nome = explode(" ", $dados["nome"], 2);
            $activation_code = sha1(md5(microtime()));
			
			$user = $this->crons_model->get_user_by_email($dados["email"]);
            if($user->num_rows() == 0) {
				$qry_insert_user = $this->crons_model->insert_user(array('email' => $dados["email"], 'active' => '0', 'created_on' => time(), 'activation_code' => $activation_code, 'first_name' => $explode_nome[0], 'last_name' => @$explode_nome[1]));
				if($qry_insert_user):
					$user_id = $qry_insert_user;

					$qry_insert_user_emp = $this->crons_model->insert_empresas_users(array('empresa_id' => $dados["empresa_id"], 'user_id' => $user_id));

					$user_unidade_id = $this->crons_model->insert_user_unidade(array("user_id" => $user_id, "unidade_id" => $dados["unidade_id"], "padrao" => 1));

					$users_groups_id = $this->crons_model->insert_users_groups(array("user_id" => $user_id, "group_id" => $dados["group_id"]));
					
					$pessoas_id = $this->crons_model->insert_pessoas(array("user_id" => $user_id, "empresa_id" => "0", "tipo" => "F", "telefone" => $dados["fone"], "cep" => str_replace(".", "", $dados["cep"]), "endereco" => $dados["endereco"], "numero" => $dados["numero"], "bairro" => $dados["bairro"], "cidade" => $dados["cidade"], "uf" => $dados["uf"], "cpf" => $dados["cpf_cnpj"]));

					
					return true;
				endif;
			} else {
                $user_id = $user->row()->id;

                $qry_insert_user_emp = $this->crons_model->insert_empresas_users(array('empresa_id' => $dados["empresa_id"], 'user_id' => $user_id));

                $user_unidade_id = $this->crons_model->insert_user_unidade(array("user_id" => $user_id, "unidade_id" => $dados["unidade_id"], "padrao" => 1));

                $users_groups_id = $this->crons_model->insert_users_groups(array("user_id" => $user_id, "group_id" => $dados["group_id"]));
            }
        }
        return false;
    }
	
	public function relatorio_planos_acao() {
        $admin_prefs = $this->crons_model->get_admin_prefs();

        foreach ($admin_prefs->result() as $admin_pref) {

            $grupos = unserialize($admin_pref->relatorio_planos_acao);
             
            if(is_array($grupos)) {
                foreach ($grupos as $g) {
                    $usuarios_grupo = $this->crons_model->get_users_by_group($g);

                    if($usuarios_grupo->num_rows() > 0) {
                        $this->load->model('plano_acao_model');

                        foreach ($usuarios_grupo->result() as $usu) {
                            $emails_to[] = $usu->email;
                        }

                        $planos = $this->plano_acao_model->get_relatorio(null, "p.status = 'EA' AND p.empresa_id = ".$admin_pref->empresa_id, array('p.tipo' => 'asc', 'p.status' => 'asc'))->result();

                        if(count($planos) > 0) {
                            foreach ($planos as $key => $plano) {
                                $times = array();
                                $apontamentos = $this->plano_acao_model->get_apontamentos($plano->id);
                                if($apontamentos->num_rows() > 0) {
                                    foreach ($apontamentos->result() as $apont) {
                                        $times[] = $apont->esforco;
                                    }
                                }

                                $planos[$key]->horas_utilizadas = AddPlayTime($times);
                                $planos[$key]->tarefas = $this->plano_acao_model->get_tarefas($plano->id)->result();
                            }
                        }

                        $planos_finalizados = $this->plano_acao_model->get_relatorio(null, "MONTH(p.termino_real) = MONTH(CURRENT_DATE())
AND YEAR(p.termino_real) = YEAR(CURRENT_DATE()) AND p.status = 'FI' AND p.empresa_id = ".$admin_pref->empresa_id, array('p.tipo' => 'asc', 'p.status' => 'asc'))->result();

                        if(count($planos_finalizados) > 0) {
                            foreach ($planos_finalizados as $key => $plano) {
                                $times = array();
                                $apontamentos = $this->plano_acao_model->get_apontamentos($plano->id);
                                if($apontamentos->num_rows() > 0) {
                                    foreach ($apontamentos->result() as $apont) {
                                        $times[] = $apont->esforco;
                                    }
                                }

                                $planos_finalizados[$key]->horas_utilizadas = AddPlayTime($times);
                            }
                        }


                        $message = $this->load->view("admin/email/relatorio_planos_acao.tpl.php", array("planos" => $planos, "planos_finalizados" => $planos_finalizados), true);
                        //exit($message);
                        //exit("<pre>".print_r($data['apontamentos']->result(),true)."</pre>");

                        $email_config_db = $this->ion_auth_model->get_email_config($admin_pref->empresa_id)->row();

                        if($email_config_db->test == 1) {
                            $email_config = rowToConfigEmail($email_config_db);
                            $from_email = $email_config_db->from_email;
                            $from_name = $email_config_db->from_name;
                        } else {
                            $from_email = $this->config->item('admin_email', 'ion_auth');
                            $from_name = $this->config->item('site_title', 'ion_auth');
                            $email_config = $this->config->item('email_config', 'ion_auth');
                        }

                        // if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
                        // {
                        //     $this->email->initialize($email_config);
                        // }

                        // $this->email->clear();
                        // $this->email->from($from_email, utf8_decode($from_name));

                        // $emails_to = array("thiago@maestriatech.com.br");

                        // $this->email->to(array_unique($emails_to));
                        // $this->email->subject(utf8_decode("Planos de Ação"));
                        // $this->email->message(utf8_decode($message));
                        // $this->email->set_mailtype("html");
                        // $this->email->send();
                        

                        $mail = $this->phpmailer_library->load();
                        $mail->isSMTP();
                        $mail->Host     = $email_config['smtp_host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $email_config['smtp_user'];
                        $mail->Password = $email_config['smtp_pass'];
                        $mail->SMTPSecure = 'tsl';
                        $mail->Port     = $email_config['smtp_port'];

                        $mail->setFrom($from_email, utf8_decode($from_name));
                        $mail->addAddress("thiago@maestriatech.com.br");

                        $mail->Subject = utf8_decode("Planos de Ação");
                        $mail->isHTML(true);

                        $mail->Body = utf8_decode($message);
                        $mail->send();
                    }
                }
            }
        }
    }
}
