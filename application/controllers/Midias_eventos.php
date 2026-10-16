<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Midias_eventos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/midias');
        $this->load->model('midias_eventos_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_midias'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_midias'), 'admin/midias');
        $this->breadcrumbs->unshift(2, lang('menu_midias_eventos'), 'admin/midias_eventos');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("midias_eventos", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['eventos'] = $this->midias_eventos_model->get(array('select'=>'e.*, m.descricao as midia_desc, DATE_FORMAT(e.created_at, "%d/%m/%Y %H:%i:%s") as data_formatada, u.unidade as unidade_desc', 'join' => array(array('midias m', 'e.midia_id = m.id'),array('unidades u', 'u.id = e.unidade_id')), 'where'=>'m.empresa_id = '.$this->session->userdata('empresa_id') ,'order_by'=>array('e.id', 'desc')))->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/midias/eventos/index', $this->data);
        }
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("midias_eventos", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_midias_eventos_create'), 'midias_eventos/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('midia', 'lang:create_validation_midia_label', 'required');
		$this->form_validation->set_rules('unidade', 'lang:create_validation_unidade_label', 'required');
		$this->form_validation->set_rules('descricao', 'lang:create_validation_descricao_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');

		$dados["unidade_id"] = $this->input->post('unidade');
		$dados["midia_id"] = $this->input->post('midia');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["status"] = $this->input->post('status');
		$dados["fb_id"] = $this->input->post('fb_id');

		if ($this->form_validation->run() == TRUE && $id = $this->midias_eventos_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->midias_eventos_model->messages()));
			redirect('midias_eventos', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->midias_eventos_model->errors() ? show_alert_error($this->midias_eventos_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->input->post('status');
            $this->data['fb_id'] = array(
				'name'  => 'fb_id',
				'id'    => 'fb_id',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('fb_id')
			);
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);
			$array_unids = array(""=>"Selecione");
			foreach ($this->midias_eventos_model->get_unidades()->result() as $value) {
				$array_unids[$value->id] = $value->unidade;
			}
			$this->data['unidade'] = array(
				'name'  => 'unidade',
				'id'    => 'unidade',
                'class' => 'form-control',
				'options' => $array_unids,
				'selected' => $this->form_validation->set_value('unidade')
			);
			$array_midias = array(""=>"Selecione");
			foreach ($this->midias_eventos_model->get_midias()->result() as $value) {
				$array_midias[$value->id] = $value->descricao;
			}
			$this->data['midia'] = array(
				'name'  => 'midia',
				'id'    => 'midia',
                'class' => 'form-control',
				'options' => $array_midias,
				'selected' => $this->form_validation->set_value('midia')
			);

            /* Load Template */
            $this->template->admin_render('admin/midias/eventos/create', $this->data);
		}
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("midias_eventos", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/midias/eventos/delete');
        }
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("midias_eventos", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(3, lang('menu_midias_eventos_edit'), 'midias_eventos/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$evento = $this->midias_eventos_model->get(array('select'=>'e.*', 'join' => array(array('midias m', 'e.midia_id = m.id')), 'where'=>'e.id = '.$id.' AND m.empresa_id = "'.$this->session->userdata('empresa_id').'"', 'order_by'=>array('e.id','desc')));
		
		if($evento->num_rows() == 0) {
			$this->session->set_flashdata('message', show_alert_error("Evento não encontrado."));
			redirect('midias_eventos', 'refresh');
		}

		$evento = $evento->row();

		/* Validate form input */
		$this->form_validation->set_rules('midia', 'lang:create_validation_midia_label', 'required');
		$this->form_validation->set_rules('descricao', 'lang:create_validation_descricao_label', 'trim|required');
		$this->form_validation->set_rules('status', 'lang:create_validation_status_label', 'required');

		$dados["status"] = $this->input->post('status');
		$dados["midia_id"] = $this->input->post('midia');
		$dados["descricao"] = $this->input->post('descricao');
		$dados["fb_id"] = $this->input->post('fb_id');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$midia_evento_update = $this->midias_eventos_model->update($id, $dados);

				if ($midia_evento_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_midia_evento_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->midias_eventos_model->errors()));
				}

				redirect('midias_eventos', 'refresh');
			} else {
				$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->midias_eventos_model->errors() ? show_alert_error($this->midias_eventos_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['evento'] = $evento;

		$this->data['status'] = $this->form_validation->set_value('status', $evento->status);
		$this->data['fb_id'] = array(
			'name'  => 'fb_id',
			'id'    => 'fb_id',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('fb_id', $evento->fb_id)
		);
		$this->data['descricao'] = array(
			'name'  => 'descricao',
			'id'    => 'descricao',
			'type'  => 'text',
            'class' => 'form-control',
			'value' => $this->form_validation->set_value('descricao', $evento->descricao)
		);
		$array_midias = array(""=>"Selecione");
		foreach ($this->midias_eventos_model->get_midias()->result() as $value) {
			$array_midias[$value->id] = $value->descricao;
		}
		$this->data['midia'] = array(
			'name'  => 'midia',
			'id'    => 'midia',
            'class' => 'form-control',
			'options' => $array_midias,
			'selected' => $this->form_validation->set_value('midia', $evento->midia_id)
		);

        /* Load Template */
        $this->template->admin_render('admin/midias/eventos/edit', $this->data);
	}


	function activate($id)
	{
        $id = (int) $id;
		if ($this->ion_auth->has_permissao("midias_eventos", "activate"))
		{
			$activation = $this->midias_eventos_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->midias_eventos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->midias_eventos_model->messages()));
		redirect('midias_eventos', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		if ($this->ion_auth->has_permissao("midias_eventos", "deactivate"))
		{
			$deactivation = $this->midias_eventos_model->deactivate($id);
		}
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->midias_eventos_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->midias_eventos_model->messages()));
		redirect('midias_eventos', 'refresh');
	}

	public function anuncios($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("midias_eventos", "anuncios"))
        {
            redirect('auth/login', 'refresh');
        }

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_midias_eventos_anuncios'), 'midias_eventos/anuncios');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->data['evento'] = $this->midias_eventos_model->get(array('select'=>'e.*', 'join' => array(array('midias m', 'e.midia_id = m.id')), 'where'=>'e.id = '.$id.' AND m.empresa_id = "'.$this->session->userdata('empresa_id').'"', 'order_by'=>array('e.id','desc')))->row();

        if ($_POST) {  
            $mensagens = "";
            $mensagens_success = "";
            $mensagens_error = "";

            if(count($this->input->post('anuncios_remove')) > 0) {
                foreach ($this->input->post('anuncios_remove') as $fb_id) {
                    $this->midias_eventos_model->delete_anuncio($fb_id, $id);
                }
            }
            if(count($this->input->post('anuncios')) > 0) {
                foreach ($this->input->post('anuncios') as $anuncio_to_add) {
                    if($this->midias_eventos_model->exists_in_evento($anuncio_to_add, $id) == false) { 
                        if($this->midias_eventos_model->create_anuncio($anuncio_to_add, $id)){
                            $mensagens_success .= "<p>Anúncio #".$anuncio_to_add." adicionado no evento.</p>";
                        }else {
                            $mensagem_erro = "";
                            $mensagens_error .= "<p>".$mensagem_erro->error_message."</p>";
                        }
                    }
                }
            }
            if($mensagens_success != "")
                $mensagens .= show_alert_success($mensagens_success);
            if($mensagens_error != "")
                $mensagens .= show_alert_error($mensagens_error);
            $this->session->set_flashdata('message', $mensagens);
            
            redirect('midias_eventos', 'refresh');
        } else {
            $this->data['anuncios'] = $this->midias_eventos_model->get_anuncios($id)->result();

            $this->data['fb_id'] = array(
				'name'  => 'fb_id',
				'id'    => 'fb_id',
				'type'  => 'text',
	            'class' => 'form-control',
				'value' => $this->form_validation->set_value('fb_id', $evento->fb_id)
			);

            /* Load Template */
            $this->template->admin_render('admin/midias/eventos/anuncios', $this->data);
        }
    }
	
}
