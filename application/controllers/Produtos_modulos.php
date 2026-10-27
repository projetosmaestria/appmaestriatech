<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produtos_modulos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/produtos');
        $this->load->model('produtos_model');
        $this->load->model('produtos_modulos_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_produtos_modulos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_produtos_modulos'), 'produtos_modulos');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos_modulos", "index"))
        {
            redirect('auth/login', 'refresh');
        }

        $this->data['modulos'] = $this->db->query("SELECT m.id, m.nome, m.descricao, m.status, m.id_produto, p.nome AS produto,
                m.valor_licenca, m.valor_manutencao, m.valor_implantacao,
                (SELECT COUNT(DISTINCT hp.id_cliente) FROM habilitacoes_modulo hm
                    JOIN habilitacoes_produto hp ON hp.id = hm.id_habilitacao
                    WHERE hm.id_modulo = m.id AND hm.status IN ('L', 'C') AND hp.status = 'S' AND hp.periodo_fim >= CURDATE()) AS clientes
            FROM modulos m
            JOIN produtos p ON p.id = m.id_produto
            WHERE p.empresa_id = ?
            ORDER BY m.status = 'S' DESC, p.nome, m.nome", array((int) $this->session->userdata('empresa_id')))->result();
        $this->data['produtos_filtro'] = $this->produtos_empresa();
        $this->data['produto_selecionado'] = (int) $this->input->get('produto');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['can_create'] = $this->ion_auth->has_permissao('produtos_modulos', 'create');
        $this->data['can_edit'] = $this->ion_auth->has_permissao('produtos_modulos', 'edit');
        $this->data['can_status'] = $this->ion_auth->has_permissao('produtos_modulos', 'deactivate') || $this->ion_auth->has_permissao('produtos_modulos', 'activate');

        $this->template->admin_render('admin/produtos/modulos/index', $this->data);
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos_modulos", "create"))
		{
			redirect('auth', 'refresh');
		}
        $this->formulario(null);
	}

	public function edit($id = null)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("produtos_modulos", "edit"))
		{
			redirect('auth', 'refresh');
		}
        $modulo = $this->modulo_da_empresa($id);
        if (!$modulo) {
            $this->session->set_flashdata('message', show_alert_error('Módulo não encontrado.'));
            redirect('produtos_modulos', 'refresh');
        }
        $this->formulario($modulo);
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
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao('produtos_modulos', $acao)) {
            redirect('auth/login', 'refresh');
        }
        $modulo = $this->modulo_da_empresa($id);
        $ok = $modulo && ($acao === 'activate' ? $this->produtos_modulos_model->activate($modulo->id) : $this->produtos_modulos_model->deactivate($modulo->id));
        if ($ok) {
            $this->session->set_flashdata('message', show_alert_success('Módulo "' . html_escape($modulo->nome) . '" ' . ($acao === 'activate' ? 'ativado.' : 'inativado.')));
        } else {
            $this->session->set_flashdata('message', show_alert_error('Não foi possível alterar o módulo.'));
        }
        redirect('produtos_modulos' . ($modulo ? '?produto=' . (int) $modulo->id_produto : ''), 'refresh');
    }

    private function modulo_da_empresa($id)
    {
        return $this->db->select('m.*')
            ->join('produtos p', 'p.id = m.id_produto')
            ->where('m.id', (int) $id)
            ->where('p.empresa_id', (int) $this->session->userdata('empresa_id'))
            ->get('modulos m')->row();
    }

    private function produtos_empresa()
    {
        return $this->db->select('id, nome, status')
            ->where('empresa_id', (int) $this->session->userdata('empresa_id'))
            ->order_by('status', 'DESC')
            ->order_by('nome', 'ASC')
            ->get('produtos')->result();
    }

    // "1.234,56" ou "1234.56" -> "1234.56"
    private function valor_decimal($valor)
    {
        $valor = trim((string) $valor);
        if (strpos($valor, ',') !== false) {
            $valor = str_replace(',', '.', str_replace('.', '', $valor));
        }
        return number_format((float) $valor, 2, '.', '');
    }

    public function _produto_da_empresa($id)
    {
        $existe = $this->db->where('id', (int) $id)->where('empresa_id', (int) $this->session->userdata('empresa_id'))->count_all_results('produtos') > 0;
        if (!$existe) {
            $this->form_validation->set_message('_produto_da_empresa', 'Selecione um produto da empresa.');
        }
        return $existe;
    }

    // Criação ($modulo = null) e edição compartilham o formulário admin/produtos/modulos/_form.
    private function formulario($modulo)
    {
        $editando = $modulo !== null;
        $this->breadcrumbs->unshift(2, lang($editando ? 'menu_produtos_modulos_edit' : 'menu_produtos_modulos_create'), $editando ? 'produtos_modulos/edit/' . (int) $modulo->id : 'produtos_modulos/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->form_validation->set_rules('produto', 'Produto', 'required|callback__produto_da_empresa');
        $this->form_validation->set_rules('nome', 'Nome', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('descricao', 'Descrição', 'trim');
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[S,N]');
        foreach (array('valor_licenca' => 'Valor da licença', 'valor_manutencao' => 'Valor da manutenção', 'valor_implantacao' => 'Valor da implantação') as $campo => $rotulo) {
            $this->form_validation->set_rules($campo, $rotulo, 'trim|required|is_money');
        }

        if ($this->input->post() && $this->form_validation->run() == TRUE) {
            $dados = array(
                'id_produto' => (int) $this->input->post('produto'),
                'nome' => $this->input->post('nome'),
                'descricao' => (string) $this->input->post('descricao'),
                'status' => $this->input->post('status'),
                'valor_licenca' => $this->valor_decimal($this->input->post('valor_licenca')),
                'valor_manutencao' => $this->valor_decimal($this->input->post('valor_manutencao')),
                'valor_implantacao' => $this->valor_decimal($this->input->post('valor_implantacao')),
            );
            if ($editando) {
                $dados['data_atualizacao'] = date('Y-m-d H:i:s');
                $dados['atualizado_por'] = $this->session->userdata('user_id');
                $ok = $this->produtos_modulos_model->update($modulo->id, $dados);
            } else {
                $dados['data_cadastro'] = date('Y-m-d H:i:s');
                $dados['cadastrado_por'] = $this->session->userdata('user_id');
                $ok = $this->produtos_modulos_model->create($dados);
            }
            if ($ok) {
                $this->session->set_flashdata('message', show_alert_success('Módulo "' . html_escape($dados['nome']) . '" ' . ($editando ? 'atualizado.' : 'criado.')));
                redirect('produtos_modulos?produto=' . $dados['id_produto'], 'refresh');
            }
            $this->data['message'] = show_alert_error($this->produtos_modulos_model->errors() ? $this->produtos_modulos_model->errors() : 'Não foi possível salvar o módulo.');
        } elseif ($this->input->post()) {
            $this->data['message'] = show_alert_error(validation_errors());
        }

        $clientes = 0;
        if ($editando) {
            $clientes = (int) $this->db->query("SELECT COUNT(DISTINCT hp.id_cliente) AS total FROM habilitacoes_modulo hm
                JOIN habilitacoes_produto hp ON hp.id = hm.id_habilitacao
                WHERE hm.id_modulo = ? AND hm.status IN ('L', 'C') AND hp.status = 'S' AND hp.periodo_fim >= CURDATE()", array((int) $modulo->id))->row()->total;
        }

        $moeda = function ($valor) {
            return $valor === null || $valor === '' ? '' : number_format((float) $valor, 2, ',', '.');
        };
        $this->data['modo'] = $editando ? 'edit' : 'create';
        $this->data['modulo'] = $modulo;
        $this->data['clientes'] = $clientes;
        $this->data['produtos'] = $this->produtos_empresa();
        $this->data['valores'] = array(
            'produto' => $this->input->post() ? (int) $this->input->post('produto') : ($editando ? (int) $modulo->id_produto : (int) $this->input->get('produto')),
            'nome' => $this->input->post() ? (string) $this->input->post('nome') : ($editando ? $modulo->nome : ''),
            'descricao' => $this->input->post() ? (string) $this->input->post('descricao') : ($editando ? $modulo->descricao : ''),
            'status' => $this->input->post() ? (string) $this->input->post('status') : ($editando ? $modulo->status : 'S'),
            'valor_licenca' => $this->input->post() ? (string) $this->input->post('valor_licenca') : ($editando ? $moeda($modulo->valor_licenca) : '0,00'),
            'valor_manutencao' => $this->input->post() ? (string) $this->input->post('valor_manutencao') : ($editando ? $moeda($modulo->valor_manutencao) : '0,00'),
            'valor_implantacao' => $this->input->post() ? (string) $this->input->post('valor_implantacao') : ($editando ? $moeda($modulo->valor_implantacao) : '0,00'),
        );

        $this->template->admin_render('admin/produtos/modulos/_form', $this->data);
    }
}
