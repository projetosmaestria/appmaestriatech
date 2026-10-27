<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Empresas extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/empresas');
        $this->load->model('empresas_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_empresas'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_empresas'), 'empresas');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("empresas", "index"))
        {
            redirect('auth/login', 'refresh');
        }

        $this->data['empresas'] = $this->db->query("SELECT e.id, e.nome, e.create_on,
                (SELECT COUNT(*) FROM unidades u WHERE u.empresa_id = e.id AND u.actived = 's') AS filiais,
                (SELECT COUNT(DISTINCT eu.user_id) FROM empresas_users eu JOIN users us ON us.id = eu.user_id AND us.active = 1 WHERE eu.empresa_id = e.id) AS usuarios,
                (SELECT COUNT(*) FROM `groups` g WHERE g.empresa_id = e.id) AS grupos,
                (SELECT COUNT(*) FROM menus_empresas me WHERE me.empresas_id = e.id) AS menus
            FROM empresas e
            ORDER BY e.nome")->result();
        $this->data['empresa_atual'] = (int) $this->session->userdata('empresa_id');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['can_create'] = $this->ion_auth->has_permissao('empresas', 'create');
        $this->data['can_edit'] = $this->ion_auth->has_permissao('empresas', 'edit');

        $this->template->admin_render('admin/empresas/index', $this->data);
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("empresas", "create"))
		{
			redirect('auth', 'refresh');
		}
        $this->formulario(null);
	}

	public function edit($id = null)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("empresas", "edit"))
		{
			redirect('auth', 'refresh');
		}
        $empresa = $this->db->where('id', (int) $id)->get('empresas')->row();
        if (!$empresa) {
            $this->session->set_flashdata('message', show_alert_error('Empresa não encontrada.'));
            redirect('empresas', 'refresh');
        }
        $this->formulario($empresa);
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("empresas", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/empresas/delete');
        }
	}

    // Criação ($empresa = null) e edição compartilham o formulário admin/empresas/_form.
    private function formulario($empresa)
    {
        $editando = $empresa !== null;
        $this->breadcrumbs->unshift(2, lang($editando ? 'menu_empresas_edit' : 'menu_empresas_create'), $editando ? 'empresas/edit/' . (int) $empresa->id : 'empresas/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->form_validation->set_rules('empresa_name', 'Nome', 'required|trim|max_length[250]');

        if ($this->input->post() && $this->form_validation->run() == TRUE) {
            $nome = $this->input->post('empresa_name');
            if ($editando) {
                $ok = $this->empresas_model->update($empresa->id, $nome);
            } else {
                $ok = $id = $this->empresas_model->create($nome);
                if ($ok) {
                    foreach (get_padrao_email() as $key => $value) {
                        $mensagem = $this->load->view('admin/email/' . $value['id'] . '.tpl.php', array(), true);
                        $this->empresas_model->create_email_padrao(array('empresa_id' => $id, 'assunto' => $value['desc'], 'tipo_email' => $key, 'mensagem' => $mensagem));
                    }
                }
            }
            if ($ok) {
                $this->session->set_flashdata('message', show_alert_success('Empresa "' . html_escape($nome) . '" ' . ($editando ? 'atualizada.' : 'criada. Você foi incluído nela como administrador; habilite as telas em Menus.')));
                redirect('empresas', 'refresh');
            }
            $this->data['message'] = show_alert_error($this->empresas_model->errors() ? $this->empresas_model->errors() : 'Não foi possível salvar a empresa.');
        } elseif ($this->input->post()) {
            $this->data['message'] = show_alert_error(validation_errors());
        }

        $numeros = null;
        if ($editando) {
            $numeros = $this->db->query("SELECT
                    (SELECT COUNT(*) FROM unidades u WHERE u.empresa_id = ? AND u.actived = 's') AS filiais,
                    (SELECT COUNT(DISTINCT eu.user_id) FROM empresas_users eu JOIN users us ON us.id = eu.user_id AND us.active = 1 WHERE eu.empresa_id = ?) AS usuarios,
                    (SELECT COUNT(*) FROM `groups` g WHERE g.empresa_id = ?) AS grupos,
                    (SELECT COUNT(*) FROM menus_empresas me WHERE me.empresas_id = ?) AS menus",
                array((int) $empresa->id, (int) $empresa->id, (int) $empresa->id, (int) $empresa->id))->row();
        }

        $this->data['modo'] = $editando ? 'edit' : 'create';
        $this->data['empresa'] = $empresa;
        $this->data['numeros'] = $numeros;
        $this->data['nome'] = $this->input->post() ? (string) $this->input->post('empresa_name') : ($editando ? $empresa->nome : '');

        $this->template->admin_render('admin/empresas/_form', $this->data);
    }
}
