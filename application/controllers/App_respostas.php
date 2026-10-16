<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_respostas extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/app_respostas');
        $this->load->model('app_respostas_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_app_respostas'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_app_respostas'), 'app_respostas');
    }

    public function q_geral() {
        if(@$this->input->post('limpar') == 1) {
            $this->session->unset_userdata('q');
        } else {
            if(@$this->input->post('q') || @$this->input->post('unidades') || @$this->input->post('mostrar_corrigidos') || @$this->input->post('turmas') || @$this->input->post('professores') || @$this->input->post('prazo')) {
                $this->session->set_userdata('q', array($this->input->post('unidades'), $this->input->post('mostrar_corrigidos'), urlencode(base64_encode($this->input->post('q'))), $this->input->post('turmas'), $this->input->post('professores'), $this->input->post('prazo')));
            } else {
                $this->session->unset_userdata('q');                
            }
        }
        redirect('app_respostas/index_geral', 'refresh');
    }

    public function index_geral()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_respostas", "index_geral"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');
            $this->data['mostrar_corrigidos'] = $this->session->q[1];
            $this->data['referer'] = "index_geral";

            // $offset = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $offset = ($this->input->get("per_page")) ? $this->input->get("per_page") : 0;

            $sort_by = 'data_envio';
            if(@$_GET["sort_by"]) {
                $sort_by = $_GET["sort_by"];
            }

            $order = 'asc';
            if(@$_GET["order"]) {
                $order = $_GET["order"];
            }

            $this->data['sort_by'] = $sort_by;
            $this->data['order'] = $order;

            $this->data['app_respostas'] = $this->app_respostas_model->get($this->session->q, 15, $offset, @$sort_by, @$order)->result();
            // exit($this->db->last_query());

            $get_num_rows = $this->app_respostas_model->get($this->session->q)->num_rows();
            $config = get_pagination_config(base_url('app_respostas/index_geral'), $get_num_rows, $offset, @$sort_by, @$order);
            $this->pagination->initialize($config);

            $this->data['pagination'] = "";

            if($this->session->q[5]) {
                if($this->session->q[5] == "S") {
                    $this->data['pagination'] .= "<div class='col-sm-12'>";
                    $this->data['pagination'] .= "<p>Total de registros no prazo: ".$get_num_rows."</p>";
                    $this->data['pagination'] .= "<p>Total de registros fora do prazo: ".$this->app_respostas_model->get_count_prazo($this->session->q, "N")."</p>";
                    $this->data['pagination'] .= "</div>";
                } else {
                    $this->data['pagination'] .= "<div class='col-sm-12'>";
                    $this->data['pagination'] .= "<p>Total de registros no prazo: ".$this->app_respostas_model->get_count_prazo($this->session->q, "S")."</p>";
                    $this->data['pagination'] .= "<p>Total de registros fora do prazo: ".$get_num_rows."</p>";
                    $this->data['pagination'] .= "</div>";
                }
            } else {
                $no_prazo = $this->app_respostas_model->get_count_prazo($this->session->q, "S");
                $fora_prazo = $this->app_respostas_model->get_count_prazo($this->session->q, "N");
                $this->data['pagination'] .= "<div class='col-sm-12'>";
                $this->data['pagination'] .= "<p>Total de registros no prazo: ".$no_prazo."</p>";
                $this->data['pagination'] .= "<p>Total de registros fora do prazo: ".$fora_prazo."</p>";
                $this->data['pagination'] .= "</div>";
            }

            $this->data['pagination'] .= $this->pagination->create_links();

            $this->data['q'] = "";
            if(@$this->session->q)
                $this->data['q'] = base64_decode(urldecode($this->session->q[2]));
            $this->data['limpar'] = ($this->data['q'] != "" || $this->data['mostrar_corrigidos'] != "") ? 1 : 0;

            $unidades = $this->app_respostas_model->get_users_unidades();
            $this->data['unidades'] = array(
                'id'=>'unidades',
                'name'=>'unidades[]',
                'class'=>'multipleSelect', 
                'multiple'=>'multiple', 
                'options' => $unidades, 
                'selected'=> ($this->session->q[0]) ? $this->session->q[0] : ''
            );

            $turmas = $this->app_respostas_model->get_turmas();
            $this->data['turmas'] = array(
                'id'=>'turmas',
                'name'=>'turmas[]',
                'class'=>'multipleSelect', 
                'multiple'=>'multiple', 
                'options' => $turmas, 
                'selected'=> ($this->session->q[3]) ? $this->session->q[3] : ''
            );

            $professores = $this->app_respostas_model->get_professores();
            $this->data['professores'] = array(
                'id'=>'professores',
                'name'=>'professores[]',
                'class'=>'multipleSelect', 
                'multiple'=>'multiple', 
                'options' => $professores, 
                'selected'=> ($this->session->q[4]) ? $this->session->q[4] : ''
            );

            $this->data['prazo'] = array(
                'id'=>'prazo',
                'name'=>'prazo',
                'class'=>'form-control', 
                'options' => array(""=>"Todos","S"=>"Sim","N"=>"Não"), 
                'selected'=> ($this->session->q[5]) ? $this->session->q[5] : ''
            );

            /* Load Template */
            $this->template->admin_render('admin/app/respostas/index', $this->data);
        }
    }

    public function q_professor() {
        if(@$this->input->post('limpar') == 1) {
            $this->session->unset_userdata('q'); 
        } else {
            if(@$this->input->post('q') || @$this->input->post('mostrar_corrigidos') || @$this->input->post('prazo') || @$this->input->post('unidades') || @$this->input->post('turmas')) {
                $this->session->set_userdata('q', array($this->input->post('mostrar_corrigidos'), urlencode(base64_encode($this->input->post('q'))), $this->input->post('prazo'), $this->input->post('unidades'), $this->input->post('turmas')));
            } else {
                $this->session->unset_userdata('q');           
            }
        }
        redirect('app_respostas/index_professor', 'refresh');
    }

    public function index_professor()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_respostas", "index_professor"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');
            $this->data['mostrar_corrigidos'] = $this->session->q[0];
            $this->data['referer'] = "index_professor";

            // $offset = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $offset = ($this->input->get("per_page")) ? $this->input->get("per_page") : 0;

            $sort_by = 'data_envio';
            if(@$_GET["sort_by"]) {
                $sort_by = $_GET["sort_by"];
            }

            $order = 'asc';
            if(@$_GET["order"]) {
                $order = $_GET["order"];
            }

            $this->data['sort_by'] = $sort_by;
            $this->data['order'] = $order;

            // if($this->session->q[0] || $this->session->q[1] || $this->session->q[2]) {
            $this->data['app_respostas'] = $this->app_respostas_model->get_para_professores($this->session->q, 15, $offset, @$sort_by, @$order)->result();

            $get_num_rows = $this->app_respostas_model->get_para_professores($this->session->q)->num_rows();
            $config = get_pagination_config(base_url('app_respostas/index_professor'), $get_num_rows, $offset, @$sort_by, @$order);

            $this->pagination->initialize($config);
            
            $this->data['pagination'] = "";

            if($this->session->q[2]) {
                if($this->session->q[2] == "S") {
                    $this->data['pagination'] .= "<div class='col-sm-12'>";
                    $this->data['pagination'] .= "<p>Total de registros no prazo: ".$get_num_rows."</p>";
                    $this->data['pagination'] .= "<p>Total de registros fora do prazo: ".$this->app_respostas_model->get_prof_count_prazo($this->session->q, "N")."</p>";
                    $this->data['pagination'] .= "</div>";
                } else {
                    $this->data['pagination'] .= "<div class='col-sm-12'>";
                    $this->data['pagination'] .= "<p>Total de registros no prazo: ".$this->app_respostas_model->get_prof_count_prazo($this->session->q, "S")."</p>";
                    $this->data['pagination'] .= "<p>Total de registros fora do prazo: ".$get_num_rows."</p>";
                    $this->data['pagination'] .= "</div>";
                }
            } else {
                $no_prazo = $this->app_respostas_model->get_prof_count_prazo($this->session->q, "S");
                $fora_prazo = $this->app_respostas_model->get_prof_count_prazo($this->session->q, "N");
                $this->data['pagination'] .= "<div class='col-sm-12'>";
                $this->data['pagination'] .= "<p>Total de registros no prazo: ".$no_prazo."</p>";
                $this->data['pagination'] .= "<p>Total de registros fora do prazo: ".$fora_prazo."</p>";
                $this->data['pagination'] .= "</div>";
            }

            $this->data['pagination'] .= $this->pagination->create_links();

            $this->data['q'] = "";
            if(@$this->session->q)
                $this->data['q'] = base64_decode(urldecode($this->session->q[1]));
            $this->data['limpar'] = ($this->data['q'] != "" || $this->data['mostrar_corrigidos'] != "") ? 1 : 0;
                
            $turmas_options = array(""=>"Selecione a Turma");
            $turmas = $this->app_respostas_model->get_turmas_professor($this->session->userdata('user_id'));

            if(count($turmas) > 0):
                foreach ($turmas as $key => $value) {
                    $turmas_options[$key] = $value;            
                }
            endif;

            $unidades = $this->app_respostas_model->get_users_unidades();
            $this->data['unidades'] = array(
                'id'=>'unidades',
                'name'=>'unidades[]',
                'class'=>'multipleSelect', 
                'multiple'=>'multiple', 
                'options' => $unidades, 
                'selected'=> ($this->session->q[3]) ? $this->session->q[3] : ''
            );

            //$turmas = $this->app_respostas_model->get_turmas();
            $this->data['turmas'] = array(
                'id'=>'turmas',
                'name'=>'turmas[]',
                'class'=>'multipleSelect', 
                'multiple'=>'multiple', 
                'options' => $turmas, 
                'selected'=> ($this->session->q[4]) ? $this->session->q[4] : ''
            );
            
            $this->data['prazo'] = array(
                'id'=>'prazo',
                'name'=>'prazo',
                'class'=>'form-control', 
                'options' => array(""=>"Todos","S"=>"Sim","N"=>"Não"), 
                'selected'=> ($this->session->q[2]) ? $this->session->q[2] : ''
            );

            /* Load Template */
            $this->template->admin_render('admin/app/respostas/index_professor', $this->data);
        }
    }

    public function corrigir($unidade, $aluno, $referer = 1)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("app_respostas", "corrigir") OR ! $unidade OR ! $aluno)
        {
            redirect('auth', 'refresh');
        }

        if($referer == 0) {
            $url_referer = "index_geral";
        } else {
            $url_referer = "index_professor";
        }

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_app_respostas_corrigir'), 'app_respostas/corrigir');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->data['unidade'] = $this->app_respostas_model->get_unidades($unidade)->row();
        $pref = $this->app_respostas_model->get_admin_preferences();
        $this->data['aluno'] = $this->app_respostas_model->get_alunos($pref->grupo_aluno, $aluno)->row();

        if($this->data['aluno'] == "") {
            $this->session->set_flashdata('message', show_alert_error('Aluno não encontrado, verifique se o grupo de Aluno na preferência está correto.'));
            redirect('app_respostas/'.$url_referer, 'refresh');
        }

        if ($_POST) {
            $erro = false;

            if(empty($this->input->post('inputRespostaCorrecao'))):
                $this->session->set_flashdata('message', show_alert_error('Por favor corrija todos os exercícios.'));
                redirect('app_respostas/corrigir/'.$unidade.'/'.$aluno, 'refresh');
            else:
                /*Verificar quantidade de perguntas/respostas a serem enviadas*/
                $qtd_pergs = $this->app_respostas_model->_count_perguntas($this->input->post('unidade_id'), $this->input->post('aluno_id'));
                $qtd_resps = count($this->input->post('inputRespostaCorrecao'));
                
                if($qtd_resps < $qtd_pergs):
                    $this->session->set_flashdata('message', show_alert_error('Por favor corrija todas as perguntas.'));
                    // redirect('app_respostas/corrigir/'.$unidade.'/'.$aluno, 'refresh');

                    $this->data['html'] = $this->_gerarHtmlExercicios($unidade, $aluno, $_POST);
                    $this->data['referer'] = $url_referer;

                    /* Load Template */
                    $this->template->admin_render('admin/app/respostas/corrigir', $this->data);
                    $erro = true;
                else:
                    foreach($this->input->post('inputRespostaCorrecao') as $key => $value):
                        if(empty($value)):
                            $this->session->set_flashdata('message', show_alert_error('Por favor corrija todas as perguntas. Ainda existem respostas não corrigidas.'));
                            //redirect('app_respostas/corrigir/'.$unidade.'/'.$aluno, 'refresh');

                            $this->data['html'] = $this->_gerarHtmlExercicios($unidade, $aluno, $_POST);
                            $this->data['referer'] = $url_referer;

                            /* Load Template */
                            $this->template->admin_render('admin/app/respostas/corrigir', $this->data);
                            $erro = true;
                        endif;
                    endforeach;
                endif;
            endif;

            if($erro == false) {
                $params = array(
                    'aluno' => $this->input->post('aluno_id'),
                    'unidade' => $this->input->post('unidade_id'),
                    'obsevacoes' => $this->input->post('inputRespostaObs'),
                    'correcoes' => $this->input->post('inputRespostaCorrecao'),
                    'prazo' => $this->input->post('inputPrazo')
                );

                if($this->app_respostas_model->registrar_correcao($params)) {
                    $this->session->set_flashdata('message', show_alert_success($this->app_respostas_model->messages()));
                } else {
                    $this->session->set_flashdata('message', show_alert_error($this->app_respostas_model->messages()));
                }
                redirect('app_respostas/'.$url_referer, 'refresh');
            }
        } else {
            if($_POST)
                $this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->app_respostas_model->errors() ? show_alert_error($this->app_respostas_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            //exit("<pre>".print_r($this->data['aluno'],true)."</pre>");

            $this->data['html'] = $this->_gerarHtmlExercicios($unidade, $aluno);

            $this->data['referer'] = $url_referer;

            /* Load Template */
            $this->template->admin_render('admin/app/respostas/corrigir', $this->data);
        }
    }

    private function _gerarHtmlExercicios($unidade, $aluno, $post = array()) {
        $html = '';
        $respostas = $this->app_respostas_model->get_exercicios_respostas_enviadas($unidade, $aluno);

        if($respostas->num_rows() > 0) {
            foreach ($respostas->result() as $resp) {
                $audio = $resp->audio == 'S' ? '<a href="'.base_url($resp->arquivo).'" class="btn btn-success btn-xs" rel="tooltip" data-toggle="tooltip" data-placement="top" data-original-title="Ouvir Áudio" target="_blank"><span class="glyphicon glyphicon-headphones"></span></a>' : '';
                /*Cabeçalho*/
                $html .= '<div class="container-fluid" style="border-bottom:2px solid #2D3874;">
                    <div class="form-group" style="padding:10px;">
                        <h4><span class="pull-left glyphicon glyphicon-bookmark"></span>&nbsp;'.$resp->ordem_exercicio.') '.$resp->titulo_exercicio.'&nbsp;'.$audio.'</h4>
                    </div>';
                /*Answer Keys*/
                if($resp->tipo_exercicio == 2 || $resp->tipo_exercicio == 3 || $resp->tipo_exercicio == 4):
                    $html .= '<div class="form-group alert-success" style="padding:10px;border-bottom:2px solid #3C763D;border-top:2px solid #3C763D;">
                                    <h4><span class="pull-left glyphicon glyphicon-check"></span> Answer Keys</h4>
                                    <hr>';
                    foreach($this->app_respostas_model->_answers_keys($resp->id) as $value):
                        $html .= '<div class="col-sm-4">'.$value['ordem'].') '.$value['chave'].'</div>';
                    endforeach;
                    $html .= '</div>';
                endif;
                /*Perguntas + Respostas*/
                foreach($this->app_respostas_model->_get_perguntas_exercicios($resp->id, $aluno) as $key => $pergunta):
                    $observacao = !empty($pergunta['observacao']) ? $pergunta['observacao'] : @$post["inputRespostaObs"][$pergunta['id_resposta']];
                    $html .= '<div class="form-group alert-info" style="padding:10px;border-bottom:2px solid #31708F;border-top:2px solid #31708F;">
                                <div>
                                    '.$pergunta['ordem'].') ' .$this->app_respostas_model->_replace_string($pergunta['sentenca'], $resp->tipo_exercicio).'
                                </div>
                            </div>
                            <div class="form-group" style="padding:10px;">';
                    if($resp->tipo_exercicio == 6):
                        $aux = explode("/",$pergunta['resposta']);
                        $html .= "Resposta: <a href='".base_url("app_respostas/respostas_aluno/".$aux[3]."/".$aux[4]."/".$aux[5])."' class='btn btn-warning btn-xs' rel='tooltip' data-toggle='tooltip' data-placement='top' data-original-title='Ouvir Resposta'><span class='glyphicon glyphicon-headphones'></span></a>";
                    else:
                        $html .= 'Resposta: '.$pergunta['resposta'];
                    endif;
                    $html .= '</div>';

                    /*Observação + Correção*/
                    
                    if($resp->status == 'C'):
                        $opcao_c = $pergunta['opcao'] == 'C' ? 'checked="checked"' : '';
                        $opcao_e = $pergunta['opcao'] == 'E' ? 'checked="checked"' : '';
                    else:
                        if(!empty($post["inputRespostaCorrecao"][$pergunta['id_resposta']])) {
                            $opcao_c = ($post["inputRespostaCorrecao"][$pergunta['id_resposta']] == "C") ? 'checked="checked"' : '';
                            $opcao_e = ($post["inputRespostaCorrecao"][$pergunta['id_resposta']] == "E") ? 'checked="checked"' : '';
                        } else {
                            if($resp->tipo_exercicio == 1 || ($resp->tipo_exercicio == 5 && $this->app_respostas_model->_check_tipo_tem_resposta($key))  || $resp->tipo_exercicio == 2 || $resp->tipo_exercicio == 3 || $resp->tipo_exercicio == 4):
                                $opcao_c = $this->app_respostas_model->_check_right_answer($key, $pergunta['resposta']) ? 'checked="checked"' : '';
                                $opcao_e = !$this->app_respostas_model->_check_right_answer($key, $pergunta['resposta']) ? 'checked="checked"' : '';
                            else:
                                $opcao_c = '';
                                $opcao_e = '';
                            endif;
                        }
                    endif;

                    $warning = '';
                    if($opcao_e == '' && $opcao_c == '')
                        $warning = 'style="background-color:#f39c12;"';

                    $html .= '<div class="form-group">
                                <div class="col-sm-8">
                                    <input type="text" id="inputRespostaObs" name="inputRespostaObs['.$pergunta['id_resposta'].']" class="form-control" placeholder="Observação" maxlength="200" value="'.htmlentities($observacao).'">
                                </div>
                                <div class="col-sm-4" '.$warning.'>
                                    <label for="inputRespostaCorrecao" class="radio-inline">Corrigir:</label>';


                    /*Fixar cores de certo e errado*/
                    $cor_c = !empty($opcao_c) ? 'style="color:#469B52;"' : '';
                    $cor_e = !empty($opcao_e) ? 'style="color:#DB353A;"' : '';

                    $html .= '<div class="radio-inline">
                                        <label>
                                            <input type="radio" '.$opcao_c.' id="inputRespostaCorrecao" name="inputRespostaCorrecao['.$pergunta['id_resposta'].']" value="C" rel="tooltip" data-toggle="tooltip" data-placement="top" data-original-title="Resposta Certa"><span class="fa fa-check fa-lg" '.$cor_c.'></span>
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" '.$opcao_e.' id="inputRespostaCorrecao" name="inputRespostaCorrecao['.$pergunta['id_resposta'].']" value="E" rel="tooltip" data-toggle="tooltip" data-placement="top" data-original-title="Resposta Errada"><span class="fa fa-times fa-lg" '.$cor_e.'></span>
                                        </label>
                                    </div>                                    
                                </div>
                            </div>';
                endforeach;

                $html .= '<div class="clear"></div>';
                $html .= '</div>';

                if($resp->prazo == 'S'):
                    $prazo_s = 'checked="checked"';
                    $prazo_n = '';
                else:
                    $prazo_s = '';
                    $prazo_n = 'checked="checked"';
                endif;
            }

            $html .= '
                <div class="container-fluid" style="border-bottom:2px solid #2D3874;">
                    <div class="form-group" style="padding:10px;">
                       <h4><span class="pull-left glyphicon glyphicon-check"></span>&nbsp;EXERCÍCIO DENTRO DO PRAZO?&nbsp;</h4>
                    </div>
                    <div class="radio-inline">
                        <label>
                            <input type="radio" id="inputPrazo" name="inputPrazo" value="S" rel="tooltip" data-toggle="tooltip" data-placement="top" data-original-title="Sim" '.$prazo_s.'><span class="fa fa-check fa-lg" style="color:#469B52;"></span>
                        </label>
                    </div>
                    <div class="radio-inline">
                        <label>
                            <input type="radio" id="inputPrazo" name="inputPrazo" value="N" rel="tooltip" data-toggle="tooltip" data-placement="top" data-original-title="Não" '.$prazo_n.'><span class="fa fa-times fa-lg" style="color:#DB353A;"></span>
                        </label>
                    </div>                                    
                </div>
            </div>';
        }

        return $html;
    }

    public function respostas_aluno($aluno, $pergunta, $mp3)
    {
        $file = FCPATH."upload/respostas/audio/".$aluno."/".$pergunta."/".$mp3;
        header('Content-Description: File Transfer');
        header('Content-Type: audios/mp3');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;
    }
}
