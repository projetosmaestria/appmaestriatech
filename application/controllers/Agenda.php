<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Agenda extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/agenda');
        $this->load->model('agenda_model');
        $this->load->model('kanban_dashboard_model');
        $this->load->model('googlecalendar');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');
		//$this->load->library(array('email'));
        $this->load->library("phpmailer_library");
		
		/*if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
		{
			$this->email->initialize($email_config);
		}*/

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_agenda'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_agenda'), 'admin/agenda');
    }


	public function index($id = null)
	{
		if($id != null) {
			$has_acesso = false;
			//verificar acesso agenda
			if($this->agenda_model->has_acesso($id)) {
				$has_acesso = true;
			}
		}

        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
        	$can_config_google_calendar = true;
        	if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "configurar_google_calendar"))
        		$can_config_google_calendar = false;

        	$can_criar_agenda = true;
        	if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "criar_agenda"))
        		$can_criar_agenda = false;

        	$can_criar_evento = true;
        	if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "criar_evento"))
        		$can_criar_evento = false;

        	$can_atualizar_evento = true;
        	if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "atualizar_evento"))
        		$can_atualizar_evento = false;

        	$can_compartilhar_agenda = true;
        	if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "compartilhar_agenda"))
        		$can_compartilhar_agenda = false;

        	$can_registrar_tutoria = true;
        	if ( (! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("ead_tutoria", "registrar_tutoria")) OR !$this->ion_auth->is_tutor_ead($this->session->userdata('user_id')) )
        		$can_registrar_tutoria = false;
        	
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            $company_id = (int) $this->session->userdata('empresa_id');
            $viewer_id = (int) $this->session->userdata('user_id');
            $is_admin = $this->ion_auth->is_admin();
            $collaborators = $this->kanban_dashboard_model->get_visible_collaborators(
                $company_id,
                $viewer_id,
                $is_admin
            );
            $collaborator_ids = array_map(function ($collaborator) {
                return (int) $collaborator->id;
            }, $collaborators);
            if (!in_array($viewer_id, $collaborator_ids, true)) {
                $current_user = $this->db->select('id, CONCAT(first_name, " ", last_name) AS nome')
                    ->where('id', $viewer_id)
                    ->where('active', 1)
                    ->get('users')
                    ->row();
                if ($current_user) {
                    $collaborators[] = $current_user;
                    $collaborator_ids[] = $viewer_id;
                }
            }

            $requested_collaborator = $this->input->get('colaborador', true);
            $selected_collaborator_id = $requested_collaborator === null || $requested_collaborator === ''
                ? $viewer_id
                : (int) $requested_collaborator;
            if (!in_array($selected_collaborator_id, $collaborator_ids, true)) {
                show_error('Você não tem permissão para visualizar este colaborador na agenda.', 403);
                return;
            }
            $this->data['collaborators'] = $collaborators;
            $this->data['selected_collaborator_id'] = $selected_collaborator_id;

            $this->data['agendas'] = $this->agenda_model->get_agenda();
			$eventos_array_s = array();
			$eventos_array_n = array();
			$eventos_array_all = array();
            if($this->data['agendas']->num_rows() > 0) {
            	$select_agendas = array(""=>"Selecione");
            	$i = 0;
            	foreach ($this->data['agendas']->result() as $agenda) {
            		if(!$id || ($id && @$has_acesso == false)) {
	            		if($i == 0) {
		            		$this->data['agenda_first'] = $agenda;
		            		$eventos = $this->agenda_model->get_evento($agenda->id);
		            		if($eventos->num_rows() > 0) {
			            		foreach ($eventos->result() as $evento) {
			            			//if(date('Y-m-d H:i:s', strtotime($evento->inicio)) >= date('Y-m-d H:i:s') || $evento->inicio == "") {
				            			$data_evento = array(
			        						"id" => $evento->id,
			        						"title" => $evento->titulo,
			        						"start" => date('Y-m-d H:i:s', strtotime($evento->inicio)),
			        						"end" => date('Y-m-d H:i:s', strtotime($evento->fim)),
			        						"backgroundColor" => $evento->cor,
			        						"borderColor" => $evento->cor
			    						);
				            			if($evento->setado == "S") {
					            			$eventos_array_s[] = $data_evento;
				            			} else {
				            				$eventos_array_n[] = $data_evento;
				            			}
				            			$data_evento["setado"] = $evento->setado;
				            			$data_evento["desc"] = $evento->desc;
				            			$eventos_array_all[] = $data_evento;
			            			//}
			            		}
		            		}
		            		$this->data['agenda_first']->eventos_s = $eventos_array_s;
		            		$this->data['agenda_first']->eventos_n = $eventos_array_n;
		            		$this->data['agenda_first']->eventos_all = $eventos_array_all;
	            			$i++;
	            		}
            		} else {
            			if(@$id == $agenda->id) {
            				$this->data['agenda_first'] = $agenda;
		            		$eventos = $this->agenda_model->get_evento($agenda->id);
		            		if($eventos->num_rows() > 0) {
			            		foreach ($eventos->result() as $evento) {
			            			//if(date('Y-m-d H:i:s', strtotime($evento->inicio)) >= date('Y-m-d H:i:s') || $evento->inicio == "") {
			        					$data_evento = array(
			        						"id" => $evento->id,
			        						"title" => $evento->titulo,
			        						"start" => date('Y-m-d H:i:s', strtotime($evento->inicio)),
			        						"end" => date('Y-m-d H:i:s', strtotime($evento->fim)),
			        						"backgroundColor" => $evento->cor,
			        						"borderColor" => $evento->cor
			    						);
				            			if($evento->setado == "S") {
					            			$eventos_array_s[] = $data_evento;
				            			} else {
				            				$eventos_array_n[] = $data_evento;
				            			}
				            			$data_evento["setado"] = $evento->setado;
				            			$data_evento["desc"] = $evento->desc;
				            			$eventos_array_all[] = $data_evento;
			            			//}
			            		}
		            		}
		            		$this->data['agenda_first']->eventos_s = $eventos_array_s;
		            		$this->data['agenda_first']->eventos_n = $eventos_array_n;
		            		$this->data['agenda_first']->eventos_all = $eventos_array_all;
            			}
            		}

        			$select_agendas[$agenda->id] = $agenda->descricao;
            	}

        		$this->data['agenda'] = array(
	                'name'  => 'agenda',
	                'id'    => 'agenda',
	                'class' => 'form-control',
	                'options' => $select_agendas,
	                'selected' => $this->data['agenda_first']->id
	            );

	            $usuarios_compartilhar = $this->agenda_model->get_usuarios_compartilhar($this->data['agenda_first']->id);
	            $select_comp = array(""=>"Selecione o usuário");
	            if(count($usuarios_compartilhar) > 0) {
	            	foreach($usuarios_compartilhar as $u) {
	            		$select_comp[$u->id] = $u->email;
	            	}
	            }
	            $this->data['compartilhar'] = array(
	                'name'  => 'compartilhar',
	                'id'    => 'compartilhar',
	                'class' => 'singleInputDynamic form-control',
	                'options' => $select_comp,
	                'selected' => ''
	            );
	            $this->data['role'] = array(
	                'name'  => 'role',
	                'id'    => 'role',
	                'class' => 'form-control',
	                'options' => array(""=>"Selecione a permissão", "freeBusyReader"=>"free/busy", "reader"=>"reader", "writer"=>"writer", "owner"=>"owner"),
	                'selected' => ''
	            );

	  			if($can_criar_evento == true) {
	  				if($this->data['agenda_first']->role != "owner" && $this->data['agenda_first']->role != "writer") {
	  					$can_criar_evento = false;
	  				}
	  			}

	  			if($can_atualizar_evento == true) {
	  				if($this->data['agenda_first']->role != "owner" && $this->data['agenda_first']->role != "writer") {
	  					$can_atualizar_evento = false;
	  				}
	  			}

	  			if($can_compartilhar_agenda == true) {
	  				if($this->data['agenda_first']->dono != $this->session->userdata('user_id') && $this->data['agenda_first']->role != "owner") {
	  					$can_compartilhar_agenda = false;
	  				}
	  			}

   				$this->page_title->push_after("<small>(".$this->data['agenda_first']->descricao.")</small>");
            } else {
            	$can_compartilhar_agenda = false;
            	$can_criar_evento = false;
            	$can_atualizar_evento = false;
            }

            if (isset($this->data['agenda_first'])) {
                foreach ($this->kanban_dashboard_model->get_agenda_unavailability(
                    $company_id,
                    $selected_collaborator_id
                ) as $availability) {
                    $end_date = new DateTime($availability->data_fim);
                    $end_date->modify('+1 day');
                    $event_type = $availability->tipo === 'feriado'
                        ? 'Feriado'
                        : ($availability->tipo === 'ferias' ? 'Férias' : 'Indisponível');
                    $event = array(
                        'id' => 'indisponibilidade-' . (int) $availability->id,
                        'title' => '[' . $event_type . '] ' . $availability->titulo,
                        'start' => $availability->data_inicio . ' 00:00:00',
                        'end' => $end_date->format('Y-m-d') . ' 00:00:00',
                        'allDay' => true,
                        'editable' => false,
                        'backgroundColor' => $availability->tipo === 'feriado' ? '#dd4b39' : '#f39c12',
                        'borderColor' => $availability->tipo === 'feriado' ? '#dd4b39' : '#f39c12',
                    );
                    $eventos_array_s[] = $event;
                }
                $this->data['agenda_first']->eventos_s = $eventos_array_s;
            }

        	$this->data['can_config_google_calendar'] = $can_config_google_calendar;
        	$this->data['can_criar_agenda'] = $can_criar_agenda;
        	$this->data['can_criar_evento'] = $can_criar_evento;
        	$this->data['can_atualizar_evento'] = $can_atualizar_evento;
        	$this->data['can_registrar_tutoria'] = $can_registrar_tutoria;

        	$agenda_vinculada_google = false;
        	if(@$this->data['agenda_first']->google_id != "")
        		$agenda_vinculada_google = true;

    		$this->data['agenda_vinculada_google'] = $agenda_vinculada_google;

        	$this->data['user'] = $this->agenda_model->get_user()->row();

  			$this->data['pagetitle'] = $this->page_title->show();


			$this->data['can_compartilhar_agenda'] = $can_compartilhar_agenda;
            $this->data['message'] = $this->session->flashdata('message');

       		if(isset($this->data['user']->id) && $this->data['user']->access_token == "")
        		$this->data['login_google_calendar'] = $this->googlecalendar->loginUrl($this->data['user']->client_id, $this->data['user']->client_secret);
            
            // exit("<pre>".print_r($this->data,true)."</pre>");
            
            /* Load Template */
            $this->template->admin_render('admin/agenda/index', $this->data);
        }
	}

	public function criar_agenda()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "criar_agenda")) {
            exit(json_encode(array("erro"=>true)));
        }
        
        if(@trim($_POST["descricao"]) == "") {
			exit(json_encode(array("erro"=>true)));
		}

		$user = $this->agenda_model->get_user()->row();
		$data = array();

		if($user->access_token) {
			if(!$this->googlecalendar->isLogin($user->client_id, $user->client_secret, $user->access_token)) {
				$data_config = array(
					'user_id' => $this->session->userdata('user_id'),
					'access_token' => $this->session->userdata('google_calendar_access_token')
				);
				$this->agenda_model->salvar_config_google_calendar($data_config);
			}
			$calendar = $this->googlecalendar->addCalendar($_POST["descricao"]);
			$data["google_id"] = $calendar->getId();
		}
		$data["descricao"] = $_POST["descricao"];
		if($id = $this->agenda_model->criar_agenda($data)) {
			exit(json_encode(array("erro"=>false, "data" => array('id' => $id, 'descricao' => $_POST["descricao"]))));
		} else {
			exit(json_encode(array("erro"=>true)));
		}
    }

	public function criar_evento()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "criar_evento")) {
			exit(json_encode(array("erro"=>true)));
		}

		if(@trim($_POST["titulo"]) == "") {
			exit(json_encode(array("erro"=>true)));
		}
		if(!$this->agenda_model->has_acesso(@$_POST["agenda"], array("writer", "owner"))) {
			exit(json_encode(array("erro"=>true)));
		}

		$data = array();
		
		$data["titulo"] = trim($_POST["titulo"]);
		$data["desc"] = trim($_POST["desc"]);
		$data["cor"] = $_POST["cor"];
		$data["agenda_id"] = $_POST["agenda"];
		$data["setado"] = "N";

		if($id = $this->agenda_model->criar_evento($data)) {
			exit(json_encode(array("erro"=>false, "data" => array('id' => $id))));
		} else {
			exit(json_encode(array("erro"=>true)));
		}
	}

	public function atualizar_evento()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "atualizar_evento")) {
			return false;
		}

		if(@$_POST["id"] == "") {
			return false;
		}

		$evento = $this->agenda_model->get_evento_by_id($_POST["id"]);
		$agenda = $this->agenda_model->get_agenda($evento->row()->agenda_id);
		
		if(!$this->agenda_model->has_acesso($agenda->row()->id, array("writer", "owner"))) {
			return false;
		}
		if(!$this->agenda_model->exist_evento(array("id" => $_POST["id"], "agenda_id" => $agenda->row()->id))) {
			return false;
		}

		$data = array();

		$user = $this->agenda_model->get_user();

		if($user->num_rows() > 0) {
			if($user->row()->access_token && $agenda->row()->google_id != "") {
				if(!$this->googlecalendar->isLogin($user->row()->client_id, $user->row()->client_secret, $user->row()->access_token)) {
					$data_config = array(
						'user_id' => $this->session->userdata('user_id'),
						'access_token' => $this->session->userdata('google_calendar_access_token')
					);
					$this->agenda_model->salvar_config_google_calendar($data_config);
				}
	            
	            if(@$_POST["data_inicio"] && @$_POST["data_fim"]) {
	            	$data_event['summary'] = $evento->row()->titulo;
		            $data_event['description'] = $evento->row()->desc;
		            $data_event['start'] = $_POST["data_inicio"];
		            $data_event['end'] = $_POST["data_fim"];
	            } else {
					$data_event['summary'] = $_POST["titulo"];
		            $data_event['description'] = $_POST["desc"];
	            	$data_event['start'] = $evento->row()->inicio;
		            $data_event['end'] = $evento->row()->fim;
	            }

	            if($evento->row()->google_id == "") {
					$event = $this->googlecalendar->addEvent($agenda->row()->google_id, $data_event);
					$data["google_id"] = $event->getId();
				} else {
					$event = $this->googlecalendar->updateEvent($agenda->row()->google_id, $evento->row()->google_id, $data_event);
				}
			}
		}

		if(@$_POST["data_inicio"])
			$data["inicio"] = $_POST["data_inicio"];

		if(@$_POST["data_fim"])
			$data["fim"] = $_POST["data_fim"];

		if(@$_POST["titulo"])
			$data["titulo"] = $_POST["titulo"];

		if(@$_POST["desc"])
			$data["desc"] = $_POST["desc"];

		$data["setado"] = "S";

		if($id = $this->agenda_model->atualizar_evento($_POST["id"], $data)) {
			return true;
		} else {
			return false;
		}
	}

	public function remover_evento()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "remover_evento")) {
			return false;
		}

		if(@$_POST["id"] == "") {
			return false;
		}
		if(@$_POST["agenda"] != "") {
			if(!$this->agenda_model->has_acesso(@$_POST["agenda"], array("writer", "owner"))) {
				return false;
			}
			if(!$this->agenda_model->exist_evento(array("id" => $_POST["id"], "agenda_id" => $_POST["agenda"]))) {
				return false;
			}

			$user = $this->agenda_model->get_user();
			$agenda = $this->agenda_model->get_agenda($_POST["agenda"]);
			$evento = $this->agenda_model->get_evento_by_id($_POST["id"]);

			if($user->num_rows() > 0) {
				if($user->row()->access_token && $agenda->row()->google_id != "" && $evento->row()->google_id != "") {
					if(!$this->googlecalendar->isLogin($user->row()->client_id, $user->row()->client_secret, $user->row()->access_token)) {
						$data_config = array(
							'user_id' => $this->session->userdata('user_id'),
							'access_token' => $this->session->userdata('google_calendar_access_token')
						);
						$this->agenda_model->salvar_config_google_calendar($data_config);
					}

		            $this->googlecalendar->delEvent($agenda->row()->google_id, $evento->row()->google_id);
				}
			}
		}

		if($id = $this->agenda_model->atualizar_evento($_POST["id"], array("setado" => "N"))) {
			return true;
		} else {
			return false;
		}
	}

	public function compartilhar_agenda() {
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "compartilhar_agenda")) {
			$this->session->set_flashdata('message', show_alert_error("Seu usuário não possui acesso a esta função."));
			redirect('agenda', 'refresh');
		} else {
			if(!$this->agenda_model->has_acesso(@$_POST["agenda_compartilhar"], array("owner"))) {
				$this->session->set_flashdata('message', show_alert_error("Seu usuário não possui acesso a esta agenda."));
				redirect('agenda', 'refresh');
			}
			if($_POST["compartilhar"] == "") {
				$this->session->set_flashdata('message', show_alert_error("Usuário não encontrado."));
				redirect('agenda', 'refresh');
			}
			if(($_POST["role"] == "") || ($_POST["role"] != "freeBusyReader" && $_POST["role"] != "reader" && $_POST["role"] != "writer" && $_POST["role"] != "owner")) {
				$this->session->set_flashdata('message', show_alert_error("Selecione a permissão."));
				redirect('agenda', 'refresh');
			}

			$agenda = $this->agenda_model->get_agenda($_POST["agenda_compartilhar"])->row();
			if($agenda->dono != $this->session->userdata('user_id')) {
				$this->session->set_flashdata('message', show_alert_error("Você deve ser o dono da agenda para compartilhá-la."));
				redirect('agenda', 'refresh');
			}

			$data = array();
			$user_compartilhar = $this->ion_auth->user($_POST["compartilhar"])->row();
			$user = $this->agenda_model->get_user();

			if($user->num_rows() > 0) {
				if($user->row()->access_token && $agenda->google_id != "") {
					if(!$this->googlecalendar->isLogin($user->row()->client_id, $user->row()->client_secret, $user->row()->access_token)) {
						$data_config = array(
							'user_id' => $this->session->userdata('user_id'),
							'access_token' => $this->session->userdata('google_calendar_access_token')
						);
						$this->agenda_model->salvar_config_google_calendar($data_config);
					}

		            $share = $this->googlecalendar->shareCalendar($agenda->google_id, $user_compartilhar->email, $_POST["role"]);
		            $data["google_id"] = $share->getId();
				}
			}

			$data["agenda_id"] = $_POST["agenda_compartilhar"];
			$data["user_id"] = $_POST["compartilhar"];
			$data["criado_por"] = $this->session->userdata('user_id');
			$data["role"] = $_POST["role"];

			$this->agenda_model->compartilhar_agenda($data);
			$this->session->set_flashdata('message', show_alert_success('Agenda "'.$agenda->descricao.'" compartilhada com "'.$user_compartilhar->email.'.'));

			$email_config_db = $this->ion_auth->get_email_config()->row();

			if($email_config_db->test == 1) {
				$email_config = rowToConfigEmail($email_config_db);
                $from_email = $email_config_db->from_email;
                $from_name = $email_config_db->from_name;
			} else {
				$email_config = $this->config->item('email_config', 'ion_auth');
                $from_email = $this->config->item('admin_email', 'ion_auth');
                $from_name = $this->config->item('site_title', 'ion_auth');
			}

			$data = array(
				'identity'   => $user->email,
				'id'         => $user->id,
				'email'      => $user->email,
				'activation' => $user->activation_code
			);

			$padrao_email = $this->ion_auth->get_email_padrao(11)->row();
            $padrao_email_msg = $padrao_email->mensagem;
            $padrao_email_msg = str_replace("{link_agenda}", base_url('agenda/index/'.$_POST["agenda_compartilhar"]), $padrao_email_msg);
            $padrao_email_msg = str_replace("{agenda_id}", $_POST["agenda_compartilhar"], $padrao_email_msg);   
            $padrao_email_msg = str_replace("{agenda_desc}", $agenda->descricao, $padrao_email_msg);   

            $padrao_email_ass = $padrao_email->assunto;
            $padrao_email_ass = str_replace("{agenda_id}", $_POST["agenda_compartilhar"], $padrao_email_ass);

        	$mail = $this->phpmailer_library->load();
        	$mail->isSMTP();
	        $mail->Host     = $email_config['smtp_host'];
	        $mail->SMTPAuth = true;
	        $mail->Username = $email_config['smtp_user'];
	        $mail->Password = $email_config['smtp_pass'];
	        $mail->SMTPSecure = 'tsl';
	        $mail->Port     = $email_config['smtp_port'];

	        $mail->setFrom($from_email, utf8_decode($from_name));

	        $mail->addAddress($user->email);
	        $mail->Subject = utf8_decode($padrao_email_ass);
	        $mail->isHTML(true);

	        $mail->Body = utf8_decode($padrao_email_msg);

	        // Send email
	        $mail->send();

            /*$padrao_email = $this->ion_auth->get_email_padrao(11)->row();
            $padrao_email_msg = $padrao_email->mensagem;
            $padrao_email_msg = str_replace("{link_agenda}", base_url('agenda/index/'.$_POST["agenda_compartilhar"]), $padrao_email_msg);
            $padrao_email_msg = str_replace("{agenda_id}", $_POST["agenda_compartilhar"], $padrao_email_msg);   
            $padrao_email_msg = str_replace("{agenda_desc}", $agenda->descricao, $padrao_email_msg);   

            $padrao_email_ass = $padrao_email->assunto;
            $padrao_email_ass = str_replace("{agenda_id}", $_POST["agenda_compartilhar"], $padrao_email_ass);

			$this->email->clear();
			$this->email->from($from_email, utf8_decode($from_name));

			$this->email->to($user_compartilhar->email);
            $this->email->subject(utf8_decode($padrao_email_ass));
            $this->email->message(utf8_decode($padrao_email_msg));
			
			$this->email->send();*/

			redirect('agenda/index/'.$_POST["agenda_compartilhar"], 'refresh');
		}
	}

	public function configurar_agenda($id = null)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "configurar_agenda") OR !$this->agenda_model->has_acesso($id, array("owner")))
        {
            redirect('auth/login', 'refresh');
        }

        /* Breadcrumbs */
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        if($_POST) {
        	if(count(@$_POST["remover_usu"]) > 0) {
        		$user = $this->agenda_model->get_user();
        		$agenda = $this->agenda_model->get_agenda($id);

        		if($user->num_rows() > 0) {
					if($user->row()->access_token && $agenda->row()->google_id != "") {
						if(!$this->googlecalendar->isLogin($user->row()->client_id, $user->row()->client_secret, $user->row()->access_token)) {
							$data_config = array(
								'user_id' => $this->session->userdata('user_id'),
								'access_token' => $this->session->userdata('google_calendar_access_token')
							);
							$this->agenda_model->salvar_config_google_calendar($data_config);
						}

		        		foreach ($_POST["remover_usu"] as $value) {
		        			$user_compartilhado = $this->agenda_model->get_usuario_compartilhado($id, $value);

		        			if($user_compartilhado->row()->google_id != "") {
		        				$this->googlecalendar->unshareCalendar($agenda->row()->google_id, $user_compartilhado->row()->google_id);
		        			}
		    			}
					}
				}

    			$this->agenda_model->remover_usuarios_envolvido($id, $_POST["remover_usu"]);
        	}
        	$this->session->set_flashdata('message', show_alert_success('Usuário "'.$id.'" excluída.'));
        	redirect('agenda', 'refresh');
        }

        $this->data['agenda_id'] = $id;
        $this->data['can_excluir_agenda'] = $this->ion_auth->has_permissao("agenda", "excluir_agenda");
        $this->data['usuarios_compartilhados'] = $this->agenda_model->get_usuarios_compartilhados($id);
        
        /*$select_comp = array(""=>"Selecione");
        if($usuarios_compartilhados->num_rows() > 0) {
        	foreach($usuarios_compartilhados->result() as $u) {
        		$select_comp[$u->id] = $u->email;
        	}
        }
        $this->data['usuarios'] = array(
            'name'  => 'usuarios',
            'id'    => 'usuarios',
            'class' => 'singleInputDynamic form-control',
            'options' => $select_comp,
            'selected' => ''
        );*/


        $this->template->admin_render('admin/agenda/configurar', $this->data);
	}

	public function excluir_agenda($id = null)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "excluir_agenda") OR !$this->agenda_model->has_acesso($id, array("owner")))
        {
            redirect('auth/login', 'refresh');
        }

        $user = $this->agenda_model->get_user()->row();

		if($user->access_token) {
			$agenda = $this->agenda_model->get_agenda($id)->row();
			if($agenda->google_id) {
				if($this->googlecalendar->isLogin($user->client_id, $user->client_secret, $user->access_token)) {
					$this->googlecalendar->delCalendar($agenda->google_id);
				} else {
					$this->googlecalendar->delCalendar($agenda->google_id);
					$data_config = array(
						'user_id' => $this->session->userdata('user_id'),
						'access_token' => $this->session->userdata('google_calendar_access_token')
					);
					$this->agenda_model->salvar_config_google_calendar($data_config);
				}
			}
		}

		//$this->agenda_model->remover_usuarios_envolvido($id);
		//$this->agenda_model->excluir_evento($id);
        $this->agenda_model->excluir_agenda($id);
        $this->session->set_flashdata('message', show_alert_success('Agenda "'.$id.'" excluída.'));

    	redirect('agenda', 'refresh');
	}

	public function configurar_google_calendar()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("agenda", "configurar_google_calendar"))
        {
            redirect('auth/login', 'refresh');
        }

        /* Breadcrumbs */
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		$this->form_validation->set_rules('client_id', 'lang:client_id', 'required');
		$this->form_validation->set_rules('client_secret', 'lang:client_secret', 'required');

		if ($this->form_validation->run() == TRUE) {
			$data = array(
				'user_id' => $this->session->userdata('user_id'),
				'client_id' => $this->input->post('client_id'),
				'client_secret' => $this->input->post('client_secret'),
				'access_token' => ''
			);

			if($this->input->post('acao') == "remover") {
            	$user = $this->agenda_model->get_user()->row();
				$this->googlecalendar->revoke($user->client_id, $user->client_secret);
			}

			$this->agenda_model->salvar_config_google_calendar($data);
        	$this->session->set_flashdata('message', show_alert_success('Configuração Google Calendar salva.'));
        	redirect('agenda', 'refresh');
        } else {
        	if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->agenda_model->errors() ? show_alert_error($this->agenda_model->errors()) : show_alert_error($this->session->flashdata('message'))));
            
            $user = $this->agenda_model->get_user()->row();

            $this->data['client_id'] = array(
	            'name'  => 'client_id',
	            'id'    => 'client_id',
	            'class' => 'form-control',
	            'value' => $this->form_validation->set_value('client_id', @$user->client_id)
	        );
	        $this->data['client_secret'] = array(
	            'name'  => 'client_secret',
	            'id'    => 'client_secret',
	            'class' => 'form-control',
	            'value' => $this->form_validation->set_value('client_secret', @$user->client_secret)
	        );
	        $this->data['login'] = $this->googlecalendar->loginUrl();
        }

        $this->template->admin_render('admin/agenda/configurar_google_calendar', $this->data);
	}

	public function oauth()
    {
        $code = $this->input->get('code', true);
        $user = $this->agenda_model->get_user()->row();

        if($this->googlecalendar->login($user->client_id, $user->client_secret, $code)) {
        	$data = array(
				'user_id' => $this->session->userdata('user_id'),
				'access_token' => $this->session->userdata('google_calendar_access_token')
			);
			$this->agenda_model->salvar_config_google_calendar($data);
        }
        redirect('agenda', 'refresh');
    }
}
