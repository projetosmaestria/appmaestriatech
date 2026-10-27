<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Niveis extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/niveis');
        $this->load->model('niveis_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_niveis'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_niveis'), 'niveis');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("niveis", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['niveis'] = $this->niveis_model->get()->result();
            // Quantidade de chamados que usam cada nível, para mostrar o impacto antes de editar ou desativar.
            $uso = array();
            foreach ($this->db->select('c.nivel_id, COUNT(*) AS total', false)
                ->from('chamado c')
                ->join('unidades un', 'un.id = c.unidade_id')
                ->where('un.empresa_id', (int) $this->session->userdata('empresa_id'))
                ->where('c.nivel_id IS NOT NULL', null, false)
                ->group_by('c.nivel_id')
                ->get()->result() as $row) {
                $uso[(int) $row->nivel_id] = (int) $row->total;
            }
            $this->data['uso_niveis'] = $uso;
            $this->data['can_create'] = $this->ion_auth->has_permissao('niveis', 'create');
            $this->data['can_edit'] = $this->ion_auth->has_permissao('niveis', 'edit');
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/niveis/index', $this->data);
        }
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("niveis", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_niveis_create'), 'niveis/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('descricao', 'lang:nivel_validation_descricao_label', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('status', 'lang:nivel_validation_status_label', 'required|in_list[0,1]');
		$this->form_validation->set_rules('cor', 'Cor', 'regex_match[/^#[0-9a-fA-F]{6}$/]');

		$dados = array(
			"descricao" => $this->input->post("descricao"),
			"empresa_id" => $this->session->userdata("empresa_id"),
			"actived" => $this->input->post("status"),
			"cor" => $this->input->post("cor") ? $this->input->post("cor") : '#3c8dbc'
		);

		if ($this->form_validation->run() == TRUE && $id = $this->niveis_model->create($dados))
		{
			$this->session->set_flashdata('message', show_alert_success($this->niveis_model->messages()));
			redirect('niveis', 'refresh');
		} else {
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->niveis_model->errors() ? show_alert_error($this->niveis_model->errors()) : show_alert_error($this->session->flashdata('message'))));

            $this->data['status'] = $this->form_validation->set_value('status');
			$this->data['descricao'] = array(
				'name'  => 'descricao',
				'id'    => 'descricao',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('descricao')
			);
			$this->data['cor_valor'] = $this->form_validation->set_value('cor', '#3c8dbc');

            /* Load Template */
            $this->template->admin_render('admin/niveis/create', $this->data);
		}
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("niveis", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/niveis/delete');
        }
	}

	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("niveis", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_niveis_edit'), 'niveis/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		// O model já restringe à empresa logada.
		$nivel = $this->niveis_model->get(array('select'=>'*', 'where'=>'id = ' . (int) $id, 'order_by'=>array('id','desc')))->row();
		if (!$nivel) {
			$this->session->set_flashdata('message', show_alert_error('Nível não encontrado.'));
			redirect('niveis', 'refresh');
		}

		/* Validate form input */
        $this->form_validation->set_rules('descricao', $this->lang->line('nivel_validation_descricao_label'), 'trim|required|max_length[100]');
        $this->form_validation->set_rules('status', $this->lang->line('nivel_validation_status_label'), 'required|in_list[0,1]');
        $this->form_validation->set_rules('cor', 'Cor', 'regex_match[/^#[0-9a-fA-F]{6}$/]');

		if (isset($_POST) && ! empty($_POST))
		{	
			$dados = array(
				"descricao" => $this->input->post("descricao"),
				"actived" => $this->input->post("status"),
				"cor" => $this->input->post("cor")
			);
			if ($this->form_validation->run() == TRUE)
			{
				$nivel_update = $this->niveis_model->update((int) $id, $dados);

				if ($nivel_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_nivel_saved')));
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->niveis_model->errors()));
				}

				redirect('niveis', 'refresh');
			} else {
        		$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->niveis_model->errors() ? show_alert_error($this->niveis_model->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

        $this->data['nivel']   = $nivel;

		$this->data['status'] = $this->form_validation->set_value('status', $nivel->actived);
		$this->data['descricao'] = array(
			'type'    => 'text',
			'name'    => 'descricao',
			'id'      => 'descricao',
			'value'   => $this->form_validation->set_value('descricao', $nivel->descricao),
            'class'   => 'form-control'
		);
		$this->data['cor_valor'] = $this->form_validation->set_value('cor', $nivel->cor ? $nivel->cor : '#3c8dbc');

        /* Load Template */
        $this->template->admin_render('admin/niveis/edit', $this->data);
	}

	function activate($id)
	{
        $id = (int) $id;
		$activation = false;
		if ($this->ion_auth->has_permissao("niveis", "activate"))
		{
			$activation = $this->niveis_model->activate($id);
		}
		if($activation)
        	$this->session->set_flashdata('message', show_alert_success($this->niveis_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->niveis_model->messages()));
		redirect('niveis', 'refresh');
	}


	public function deactivate($id = NULL)
	{
		$id = (int) $id;
		$deactivation = false;
		if ($this->ion_auth->has_permissao("niveis", "deactivate"))
		{
			$deactivation = $this->niveis_model->deactivate($id);
		}
		if($deactivation)
        	$this->session->set_flashdata('message', show_alert_success($this->niveis_model->messages()));
        else
        	$this->session->set_flashdata('message', show_alert_error($this->niveis_model->messages()));
		redirect('niveis', 'refresh');
	}
}
