<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Groups extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/groups');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_security_groups'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_security_groups'), 'groups');
    }


	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("groups", "index"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            $this->data['message'] = $this->session->flashdata('message');
            $this->ion_auth->ion_auth_model->_ion_where = array('empresa_id = '.$this->session->userdata('empresa_id'));
            $this->data['groups'] = $this->ion_auth->ion_auth_model->groups()->result();

            /* Load Template */
            $this->template->admin_render('admin/groups/index', $this->data);
        }
    }


	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("groups", "create"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_groups_create'), 'groups/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('group_name', 'lang:create_group_validation_name_label', 'required|alpha_dash');

		if ($this->form_validation->run() == TRUE)
		{
			$new_group_id = $this->ion_auth->create_group($this->input->post('group_name'), $this->input->post('description'), array("empresa_id"=>$this->session->userdata('empresa_id'), "limited"=>($this->input->post('limited')) ? $this->input->post('limited') : 0, "permissoes"=>serialize($this->input->post('permissoes'))));
			if ($new_group_id)
			{
				$this->session->set_flashdata('message', show_alert_success($this->ion_auth->messages()));
				redirect('groups', 'refresh');
			}
		}
		else
		{
			if($_POST)
            	$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->ion_auth->errors() ? show_alert_error($this->ion_auth->errors()) : show_alert_error($this->session->flashdata('message'))));

            $permissoes = array();
            if($this->input->post('permissoes'))
				$permissoes = $this->input->post('permissoes');

			$menus_habilitados_empresa_atual = $this->ion_auth->get_menus_habilitados_empresa($this->session->userdata('empresa_id'))->result();
	        $this->data['permissoes'] = get_permissoes($menus_habilitados_empresa_atual, $permissoes);

			$this->data['group_name'] = array(
				'name'  => 'group_name',
				'id'    => 'group_name',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('group_name')
			);
			$this->data['description'] = array(
				'name'  => 'description',
				'id'    => 'description',
				'type'  => 'text',
                'class' => 'form-control',
				'value' => $this->form_validation->set_value('description')
			);
			$this->data['limited'] = array(
				'name'  => 'limited',
				'id'    => 'limited',
                'class' => 'checkbox',
            	'checked' => ($this->input->post('limited')) ? true : false,
				'value' => 1
			);

            /* Load Template */
            $this->template->admin_render('admin/groups/create', $this->data);
		}
	}


	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("groups", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/groups/delete');
        }
	}


	public function edit($id)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $id OR empty($id) OR ! $this->ion_auth->has_permissao("groups", "edit"))
		{
			redirect('auth', 'refresh');
		}

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_groups_edit'), 'groups/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Variables */
		$group = $this->ion_auth->group($id)->row();

		if ($group->empresa_id != $this->session->userdata("empresa_id"))
		{
			redirect('groups', 'refresh');
		}

		/* Validate form input */
        $this->form_validation->set_rules('group_name', $this->lang->line('edit_group_validation_name_label'), 'required|alpha_dash');

		if (isset($_POST) && ! empty($_POST))
		{
			if ($this->form_validation->run() == TRUE)
			{
				$group_update = $this->ion_auth->update_group($id, $this->input->post('group_name'), array("description"=>$this->input->post('group_description'), "empresa_id"=>$this->session->userdata('empresa_id'), "limited"=>$this->input->post('limited'), "permissoes"=>$this->input->post('permissoes')));

				if ($group_update)
				{
					$this->session->set_flashdata('message', show_alert_success($this->lang->line('edit_group_saved')));

                    /* IN TEST */
                    $this->db->update('groups', array('bgcolor' => $_POST['group_bgcolor']), 'id = '.$id);
				}
				else
				{
					$this->session->set_flashdata('message', show_alert_error($this->ion_auth->errors()));
				}

				redirect('groups', 'refresh');
			} else {
        		$this->data['message'] = (validation_errors() ? show_alert_error(validation_errors()) : ($this->ion_auth->errors() ? show_alert_error($this->ion_auth->errors()) : show_alert_error($this->session->flashdata('message'))));
			}
		}

		if($this->input->post('permissoes'))
			$permissoes = $this->input->post('permissoes');
		else
			$permissoes = unserialize($group->permissoes);

		$menus_habilitados_empresa_atual = $this->ion_auth->get_menus_habilitados_empresa($this->session->userdata('empresa_id'))->result();
        $this->data['permissoes'] = get_permissoes($menus_habilitados_empresa_atual, $permissoes);

        $this->data['group']   = $group;

		$readonly = $this->config->item('admin_group', 'ion_auth') === $group->name ? 'readonly' : '';

		$this->data['group_name'] = array(
			'type'    => 'text',
			'name'    => 'group_name',
			'id'      => 'group_name',
			'value'   => $this->form_validation->set_value('group_name', $group->name),
            'class'   => 'form-control',
			$readonly => $readonly
		);
		$this->data['group_description'] = array(
			'type'  => 'text',
			'name'  => 'group_description',
			'id'    => 'group_description',
			'value' => $this->form_validation->set_value('group_description', $group->description),
            'class' => 'form-control'
		);
		$this->data['group_bgcolor'] = array(
			'type'     => 'text',
			'name'     => 'group_bgcolor',
			'id'       => 'group_bgcolor',
			'value'    => $this->form_validation->set_value('group_bgcolor', $group->bgcolor),
			'data-src' => $group->bgcolor,
            'class'    => 'form-control'
		);
		$this->data['limited'] = array(
			'name'  => 'limited',
			'id'    => 'limited',
            'class' => 'checkbox',
            'checked' => ($this->input->post('limited') || $group->limited) ? true : false,
			'value' => 1
		);

        /* Load Template */
        $this->template->admin_render('admin/groups/edit', $this->data);
	}
}
