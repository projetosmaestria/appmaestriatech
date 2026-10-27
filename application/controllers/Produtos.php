<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produtos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/produtos');
        $this->load->model('produtos_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_produtos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_produtos'), 'produtos');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos", "index"))
        {
            redirect('auth/login', 'refresh');
        }

        $empresa_id = (int) $this->session->userdata('empresa_id');
        $this->data['produtos'] = $this->db->query("SELECT p.id, p.nome, p.aplicacao, p.descricao, p.status,
                (SELECT COUNT(*) FROM modulos m WHERE m.id_produto = p.id AND m.status = 'S') AS modulos,
                (SELECT COUNT(DISTINCT hp.id_cliente) FROM habilitacoes_produto hp WHERE hp.id_produto = p.id AND hp.status = 'S' AND hp.periodo_fim >= CURDATE()) AS clientes,
                (SELECT COUNT(*) FROM chamado c WHERE c.produto_id = p.id AND c.created >= CURDATE() - INTERVAL 90 DAY) AS chamados_90,
                (SELECT pc.versao FROM _produtos_changelog pc WHERE pc.id_produto = p.id AND pc.status = 'S' ORDER BY pc.id DESC LIMIT 1) AS versao
            FROM produtos p
            WHERE p.empresa_id = ?
            ORDER BY p.status = 'S' DESC, p.nome", array($empresa_id))->result();
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['can_create'] = $this->ion_auth->has_permissao('produtos', 'create');
        $this->data['can_edit'] = $this->ion_auth->has_permissao('produtos', 'edit');
        $this->data['can_status'] = $this->ion_auth->has_permissao('produtos', 'deactivate') || $this->ion_auth->has_permissao('produtos', 'activate');
        $this->data['can_modulos'] = $this->ion_auth->has_permissao('produtos_modulos', 'index');

        $this->template->admin_render('admin/produtos/index', $this->data);
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos", "create"))
		{
			redirect('auth', 'refresh');
		}
        $this->formulario(null);
	}

	public function edit($id = null)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos", "edit"))
		{
			redirect('auth', 'refresh');
		}
        $produto = $this->produto_da_empresa($id);
        if (!$produto) {
            $this->session->set_flashdata('message', show_alert_error('Produto não encontrado.'));
            redirect('produtos', 'refresh');
        }
        $this->formulario($produto);
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
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao('produtos', $acao)) {
            redirect('auth/login', 'refresh');
        }
        $produto = $this->produto_da_empresa($id);
        $ok = $produto && ($acao === 'activate' ? $this->produtos_model->activate($produto->id) : $this->produtos_model->deactivate($produto->id));
        if ($ok) {
            $this->session->set_flashdata('message', show_alert_success('Produto "' . html_escape($produto->nome) . '" ' . ($acao === 'activate' ? 'ativado.' : 'inativado.')));
        } else {
            $this->session->set_flashdata('message', show_alert_error('Não foi possível alterar o produto.'));
        }
        redirect('produtos', 'refresh');
    }

    private function produto_da_empresa($id)
    {
        return $this->db->where('id', (int) $id)
            ->where('empresa_id', (int) $this->session->userdata('empresa_id'))
            ->get('produtos')->row();
    }

    // Criação ($produto = null) e edição compartilham o formulário admin/produtos/_form.
    private function formulario($produto)
    {
        $editando = $produto !== null;
        $this->breadcrumbs->unshift(2, lang($editando ? 'menu_produtos_edit' : 'menu_produtos_create'), $editando ? 'produtos/edit/' . (int) $produto->id : 'produtos/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->form_validation->set_rules('nome', 'Nome', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('aplicacao', 'Aplicação', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('descricao', 'Descrição', 'trim');
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[S,N]');

        if ($this->input->post() && $this->form_validation->run() == TRUE) {
            $dados = array(
                'nome' => $this->input->post('nome'),
                'aplicacao' => $this->input->post('aplicacao'),
                'descricao' => (string) $this->input->post('descricao'),
                'status' => $this->input->post('status'),
            );
            if ($editando) {
                $dados['data_atualizacao'] = date('Y-m-d H:i:s');
                $dados['atualizado_por'] = $this->session->userdata('user_id');
                $ok = $this->produtos_model->update($produto->id, $dados);
            } else {
                $dados['data_cadastro'] = date('Y-m-d H:i:s');
                $dados['cadastrado_por'] = $this->session->userdata('user_id');
                $dados['empresa_id'] = (int) $this->session->userdata('empresa_id');
                $ok = $this->produtos_model->create($dados);
            }
            if ($ok) {
                $this->session->set_flashdata('message', show_alert_success('Produto "' . html_escape($dados['nome']) . '" ' . ($editando ? 'atualizado.' : 'criado.')));
                redirect('produtos', 'refresh');
            }
            $this->data['message'] = show_alert_error($this->produtos_model->errors() ? $this->produtos_model->errors() : 'Não foi possível salvar o produto.');
        } elseif ($this->input->post()) {
            $this->data['message'] = show_alert_error(validation_errors());
        }

        $uso = null;
        if ($editando) {
            $uso = $this->db->query("SELECT
                    (SELECT COUNT(*) FROM modulos m WHERE m.id_produto = ? AND m.status = 'S') AS modulos,
                    (SELECT COUNT(DISTINCT hp.id_cliente) FROM habilitacoes_produto hp WHERE hp.id_produto = ? AND hp.status = 'S' AND hp.periodo_fim >= CURDATE()) AS clientes,
                    (SELECT pc.versao FROM _produtos_changelog pc WHERE pc.id_produto = ? AND pc.status = 'S' ORDER BY pc.id DESC LIMIT 1) AS versao",
                array((int) $produto->id, (int) $produto->id, (int) $produto->id))->row();
        }

        // Aplicações já usadas, para sugerir no campo.
        $aplicacoes = array();
        foreach ($this->db->distinct()->select('aplicacao')->where('empresa_id', (int) $this->session->userdata('empresa_id'))->where('aplicacao !=', '')->order_by('aplicacao')->get('produtos')->result() as $linha) {
            $aplicacoes[] = $linha->aplicacao;
        }

        $this->data['modo'] = $editando ? 'edit' : 'create';
        $this->data['produto'] = $produto;
        $this->data['uso'] = $uso;
        $this->data['aplicacoes'] = $aplicacoes;
        $this->data['can_modulos'] = $this->ion_auth->has_permissao('produtos_modulos', 'index');
        $campos = array('nome', 'aplicacao', 'descricao');
        $valores = array();
        foreach ($campos as $campo) {
            $valores[$campo] = $this->input->post() ? (string) $this->input->post($campo) : ($editando ? (string) $produto->$campo : '');
        }
        $valores['status'] = $this->input->post() ? (string) $this->input->post('status') : ($editando ? $produto->status : 'S');
        $this->data['valores'] = $valores;

        $this->template->admin_render('admin/produtos/_form', $this->data);
    }
}
