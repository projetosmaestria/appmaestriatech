<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setores extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/setores');
        $this->load->model('setores_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_setores'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_setores'), 'setores');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("setores", "index"))
        {
            redirect('auth/login', 'refresh');
        }

        $this->data['setores'] = $this->db->select('s.id, s.descricao, s.actived, COUNT(DISTINCT CASE WHEN u.active = 1 THEN u.id END) AS usuarios', false)
            ->from('setores s')
            ->join('users_setores us', 'us.setor_id = s.id', 'left')
            ->join('users u', 'u.id = us.user_id', 'left')
            ->where('s.empresa_id', (int) $this->session->userdata('empresa_id'))
            ->group_by('s.id, s.descricao, s.actived')
            ->order_by('s.actived', 'DESC')
            ->order_by('s.descricao', 'ASC')
            ->get()->result();
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['can_create'] = $this->ion_auth->has_permissao('setores', 'create');
        $this->data['can_edit'] = $this->ion_auth->has_permissao('setores', 'edit');
        $this->data['can_status'] = $this->ion_auth->has_permissao('setores', 'deactivate') || $this->ion_auth->has_permissao('setores', 'activate');

        $this->template->admin_render('admin/setores/index', $this->data);
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("setores", "create"))
		{
			redirect('auth', 'refresh');
		}
        $this->formulario(null);
	}

	public function edit($id = null)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("setores", "edit"))
		{
			redirect('auth', 'refresh');
		}
        $setor = $this->setor_da_empresa($id);
        if (!$setor) {
            $this->session->set_flashdata('message', show_alert_error('Setor não encontrado.'));
            redirect('setores', 'refresh');
        }
        $this->formulario($setor);
	}

	public function delete()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("setores", "delete"))
        {
            redirect('auth/login', 'refresh');
        }
        elseif ( ! $this->ion_auth->is_admin())
		{
            return show_error('You must be an administrator to view this page.');
        }
        else
        {
            $this->load->view('admin/setores/delete');
        }
	}

	public function activate($id = NULL)
	{
        $this->alterar_status($id, 'activate');
	}

	public function deactivate($id = NULL)
	{
        $this->alterar_status($id, 'deactivate');
	}

    private function alterar_status($id, $acao)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao('setores', $acao)) {
            redirect('auth/login', 'refresh');
        }
        $setor = $this->setor_da_empresa($id);
        $ok = $setor && ($acao === 'activate' ? $this->setores_model->activate($setor->id) : $this->setores_model->deactivate($setor->id));
        if ($ok) {
            $this->session->set_flashdata('message', show_alert_success('Setor "' . html_escape($setor->descricao) . '" ' . ($acao === 'activate' ? 'ativado.' : 'desativado.')));
        } else {
            $this->session->set_flashdata('message', show_alert_error('Não foi possível alterar o setor.'));
        }
        redirect('setores', 'refresh');
    }

    private function setor_da_empresa($id)
    {
        return $this->db->where('id', (int) $id)
            ->where('empresa_id', (int) $this->session->userdata('empresa_id'))
            ->get('setores')->row();
    }

    // Criação ($setor = null) e edição compartilham o formulário admin/setores/_form.
    private function formulario($setor)
    {
        $editando = $setor !== null;
        $this->breadcrumbs->unshift(2, lang($editando ? 'menu_setores_edit' : 'menu_setores_create'), $editando ? 'setores/edit/' . (int) $setor->id : 'setores/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->form_validation->set_rules('descricao', 'Nome do setor', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[0,1]');

        if ($this->input->post() && $this->form_validation->run() == TRUE) {
            $dados = array(
                'empresa_id' => (int) $this->session->userdata('empresa_id'),
                'descricao' => $this->input->post('descricao'),
                'actived' => (int) $this->input->post('status'),
            );
            $ok = $editando ? $this->setores_model->update($setor->id, $dados) : $this->setores_model->create($dados);
            if ($ok) {
                $this->session->set_flashdata('message', show_alert_success('Setor "' . html_escape($dados['descricao']) . '" ' . ($editando ? 'atualizado.' : 'criado.')));
                redirect('setores', 'refresh');
            }
            $this->data['message'] = show_alert_error($this->setores_model->errors() ? $this->setores_model->errors() : 'Não foi possível salvar o setor.');
        } elseif ($this->input->post()) {
            $this->data['message'] = show_alert_error(validation_errors());
        }

        $usuarios = 0;
        if ($editando) {
            $usuarios = (int) $this->db->from('users_setores us')->join('users u', 'u.id = us.user_id')
                ->where('us.setor_id', (int) $setor->id)->where('u.active', 1)->count_all_results();
        }

        $this->data['modo'] = $editando ? 'edit' : 'create';
        $this->data['setor'] = $setor;
        $this->data['usuarios'] = $usuarios;
        $this->data['valores'] = array(
            'descricao' => $this->input->post() ? $this->input->post('descricao') : ($editando ? $setor->descricao : ''),
            'status' => $this->input->post() ? (string) $this->input->post('status') : ($editando ? (string) (int) $setor->actived : '1'),
        );

        $this->template->admin_render('admin/setores/_form', $this->data);
    }
}
