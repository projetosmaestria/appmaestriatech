<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Groups extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/groups');
        $this->load->helper('functions_helper');
        $this->load->model('permissoes_model');

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

        $empresa_id = (int) $this->session->userdata('empresa_id');
        $grupos = $this->db->select('g.id, g.name, g.description, g.bgcolor, g.limited, g.permissoes,
                COUNT(DISTINCT CASE WHEN u.active = 1 THEN u.id END) AS usuarios_ativos,
                COUNT(DISTINCT u.id) AS usuarios', false)
            ->from('groups g')
            ->join('users_groups ug', 'ug.group_id = g.id', 'left')
            ->join('users u', 'u.id = ug.user_id', 'left')
            ->where('g.empresa_id', $empresa_id)
            ->group_by('g.id, g.name, g.description, g.bgcolor, g.limited, g.permissoes')
            ->order_by('g.name', 'ASC')
            ->get()->result();

        $catalogo = $this->permissoes_model->catalogo($empresa_id);
        $total_permissoes = 0;
        foreach ($catalogo as $item) {
            $total_permissoes += count($item['metodos']);
        }
        $prefs = $this->ion_auth->get_admin_preferences();
        foreach ($grupos as $grupo) {
            $permissoes = Permissoes_model::desserializar($grupo->permissoes);
            $grupo->telas = 0;
            $grupo->acoes = 0;
            foreach ($permissoes as $controlador => $metodos) {
                if (is_array($metodos) && $metodos) {
                    $grupo->telas++;
                    $grupo->acoes += count($metodos);
                }
            }
            $grupo->papel = '';
            if ($prefs && (int) $prefs->grupo_admin === (int) $grupo->id) { $grupo->papel = 'Administradores'; }
            elseif ($prefs && (int) $prefs->grupo_aluno === (int) $grupo->id) { $grupo->papel = 'Alunos'; }
            elseif ($prefs && (int) $prefs->grupo_vendedor === (int) $grupo->id) { $grupo->papel = 'Vendedores'; }
            unset($grupo->permissoes);
        }

        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['groups'] = $grupos;
        $this->data['total_permissoes'] = $total_permissoes;
        $this->data['can_create'] = $this->ion_auth->has_permissao('groups', 'create');
        $this->data['can_edit'] = $this->ion_auth->has_permissao('groups', 'edit');

        $this->template->admin_render('admin/groups/index', $this->data);
    }

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("groups", "create"))
		{
			redirect('auth', 'refresh');
		}
        $this->formulario(null);
	}

	public function edit($id = null)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("groups", "edit"))
		{
			redirect('auth', 'refresh');
		}
		$group = $this->ion_auth->group((int) $id)->row();
		if (empty($group) || (int) $group->empresa_id !== (int) $this->session->userdata("empresa_id"))
		{
			redirect('groups', 'refresh');
		}
        $this->formulario($group);
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

    // Criação ($group = null) e edição compartilham o formulário admin/groups/_form.
    private function formulario($group)
    {
        $editando = $group !== null;
        $empresa_id = (int) $this->session->userdata('empresa_id');
        $catalogo = $this->permissoes_model->catalogo($empresa_id);
        $atuais = $editando ? Permissoes_model::desserializar($group->permissoes) : array();
        $is_admin_group = $editando && $this->config->item('admin_group', 'ion_auth') === $group->name;

        $this->breadcrumbs->unshift(2, lang($editando ? 'menu_groups_edit' : 'menu_groups_create'), $editando ? 'groups/edit/' . (int) $group->id : 'groups/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->form_validation->set_rules('group_name', 'Nome', 'required|trim|max_length[20]|callback__nome_unico[' . ($editando ? (int) $group->id : 0) . ']');
        $this->form_validation->set_rules('group_description', 'Descrição', 'trim|max_length[100]');
        $this->form_validation->set_rules('group_bgcolor', 'Cor', 'trim|regex_match[/^#[0-9a-fA-F]{6}$/]');

        if ($this->input->post() && $this->form_validation->run() == TRUE)
        {
            $nome = $is_admin_group ? $group->name : $this->input->post('group_name');
            $permissoes = $this->permissoes_model->mesclar($atuais, $this->input->post('permissoes'), $catalogo);
            $dados = array(
                'description' => (string) $this->input->post('group_description'),
                'bgcolor' => $this->input->post('group_bgcolor') ? strtolower($this->input->post('group_bgcolor')) : '#607d8b',
                'limited' => $this->input->post('limited') ? 1 : 0,
                'permissoes' => serialize($permissoes),
            );

            if ($editando) {
                // update_group só valida o nome; descrição, cor, limitação e permissões vão direto.
                $ok = $this->ion_auth->update_group($group->id, $nome, array('empresa_id' => $empresa_id));
                if ($ok) {
                    $this->db->update('groups', $dados, array('id' => (int) $group->id));
                }
                $id = (int) $group->id;
            } else {
                $id = $this->ion_auth->create_group($nome, $dados['description'], array_merge($dados, array('empresa_id' => $empresa_id)));
                $ok = (bool) $id;
            }

            if ($ok) {
                $this->ion_auth->create_auditoria(array(
                    'user_id' => $this->session->userdata('user_id'),
                    'empresa_id' => $empresa_id,
                    'controlador' => 'groups',
                    'metodo' => $editando ? 'edit' : 'create',
                    'obs' => 'Grupo ' . $id . ' (' . $nome . '): ' . count($permissoes) . ' telas com permissão.',
                ));
                $this->session->set_flashdata('message', show_alert_success($editando ? 'Grupo "' . html_escape($nome) . '" atualizado.' : 'Grupo "' . html_escape($nome) . '" criado.'));
                redirect('groups', 'refresh');
            }
            $this->data['message'] = show_alert_error($this->ion_auth->errors() ? $this->ion_auth->errors() : 'Não foi possível salvar o grupo.');
        }
        elseif ($this->input->post())
        {
            $this->data['message'] = show_alert_error(validation_errors());
        }

        // No reenvio com erro, mantém o que foi marcado.
        $marcadas = $this->input->post() ? (array) $this->input->post('permissoes') : $atuais;

        $usuarios = 0;
        if ($editando) {
            $usuarios = (int) $this->db->from('users_groups ug')->join('users u', 'u.id = ug.user_id')
                ->where('ug.group_id', (int) $group->id)->where('u.active', 1)->count_all_results();
        }

        $this->data['modo'] = $editando ? 'edit' : 'create';
        $this->data['group'] = $group;
        $this->data['is_admin_group'] = $is_admin_group;
        $this->data['usuarios_ativos'] = $usuarios;
        $this->data['catalogo'] = $catalogo;
        $this->data['marcadas'] = $marcadas;
        $this->data['rotulos'] = $this->permissoes_model;
        $this->data['valores'] = array(
            'group_name' => $this->input->post() ? $this->input->post('group_name') : ($editando ? $group->name : ''),
            'group_description' => $this->input->post() ? $this->input->post('group_description') : ($editando ? $group->description : ''),
            'group_bgcolor' => $this->input->post('group_bgcolor') ? $this->input->post('group_bgcolor') : ($editando && $group->bgcolor ? $group->bgcolor : '#607d8b'),
            'limited' => $this->input->post() ? (bool) $this->input->post('limited') : ($editando && $group->limited),
        );

        $this->template->admin_render('admin/groups/_form', $this->data);
    }

    // Nome único entre os grupos da empresa.
    public function _nome_unico($nome, $id_atual)
    {
        $existe = $this->db->where('empresa_id', (int) $this->session->userdata('empresa_id'))
            ->where('name', $nome)
            ->where('id !=', (int) $id_atual)
            ->count_all_results('groups') > 0;
        if ($existe) {
            $this->form_validation->set_message('_nome_unico', 'Já existe um grupo com esse nome nesta empresa.');
            return false;
        }
        return true;
    }
}
