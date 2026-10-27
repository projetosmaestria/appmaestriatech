<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalogo_servicos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/catalogo_servicos');
        $this->load->model('catalogo_servicos_model');
        $this->load->model('produtos_model');
        $this->load->model('produtos_modulos_model');
        $this->load->model('produtos_modulos_categorias_model');
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_catalogo_servicos'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_catalogo_servicos'), 'catalogo_servicos');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("catalogo_servicos", "index"))
        {
            redirect('auth/login', 'refresh');
        }

        $this->data['servicos'] = $this->db->query("SELECT cs.id, cs.descricao, cs.tempo_padrao, cs.prazo_padrao, cs.actived,
                n.id AS nivel_id, n.descricao AS nivel, n.cor AS nivel_cor,
                mc.nome AS categoria, m.nome AS modulo, p.id AS produto_id, p.nome AS produto,
                COALESCE(uso.chamados, 0) AS chamados_90
            FROM catalogo_servico cs
            JOIN modulos_categoria mc ON mc.id = cs.modulo_categoria_id
            JOIN modulos m ON m.id = mc.id_modulo
            JOIN produtos p ON p.id = m.id_produto
            LEFT JOIN nivel n ON n.id = cs.nivel_id
            LEFT JOIN (SELECT catalogoservico_id, COUNT(*) AS chamados FROM chamado
                       WHERE created >= CURDATE() - INTERVAL 90 DAY GROUP BY catalogoservico_id) uso ON uso.catalogoservico_id = cs.id
            WHERE p.empresa_id = ?
            ORDER BY cs.actived DESC, p.nome, m.nome, mc.nome, cs.descricao", array((int) $this->session->userdata('empresa_id')))->result();
        $this->data['niveis'] = $this->niveis_empresa();
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['can_create'] = $this->ion_auth->has_permissao('catalogo_servicos', 'create');
        $this->data['can_edit'] = $this->ion_auth->has_permissao('catalogo_servicos', 'edit');
        $this->data['can_status'] = $this->ion_auth->has_permissao('catalogo_servicos', 'deactivate') || $this->ion_auth->has_permissao('catalogo_servicos', 'activate');

        $this->template->admin_render('admin/catalogo_servicos/index', $this->data);
	}

	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("catalogo_servicos", "create"))
		{
			redirect('auth', 'refresh');
		}
        $this->formulario(null);
	}

	public function edit($id = null)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("catalogo_servicos", "edit"))
		{
			redirect('auth', 'refresh');
		}
        $servico = $this->servico_da_empresa($id);
        if (!$servico) {
            $this->session->set_flashdata('message', show_alert_error('Serviço não encontrado.'));
            redirect('catalogo_servicos', 'refresh');
        }
        $this->formulario($servico);
	}

	public function activate($id = NULL)
	{
        $this->alterar_status($id, 'activate');
	}

	public function deactivate($id = NULL)
	{
        $this->alterar_status($id, 'deactivate');
	}

	public function get_modulos_habilitados()
	{
		$modulos = $this->produtos_modulos_categorias_model->get_modulos((int) $this->input->post('id'));
        $select = array();
        foreach ($modulos->result() as $value) {
            $select[] = array('id' => $value->id, 'nome' => $value->nome);
        }
        exit(json_encode($select ? $select : false));
    }

    private function alterar_status($id, $acao)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao('catalogo_servicos', $acao)) {
            redirect('auth/login', 'refresh');
        }
        $servico = $this->servico_da_empresa($id);
        $ok = $servico && ($acao === 'activate' ? $this->catalogo_servicos_model->activate($servico->id) : $this->catalogo_servicos_model->deactivate($servico->id));
        if ($ok) {
            $this->session->set_flashdata('message', show_alert_success('Serviço "' . html_escape($servico->descricao) . '" ' . ($acao === 'activate' ? 'ativado.' : 'inativado.')));
        } else {
            $this->session->set_flashdata('message', show_alert_error('Não foi possível alterar o serviço.'));
        }
        redirect('catalogo_servicos', 'refresh');
    }

    private function servico_da_empresa($id)
    {
        return $this->db->select('cs.*, mc.id_modulo AS modulo_id, m.id_produto AS produto_id')
            ->join('modulos_categoria mc', 'mc.id = cs.modulo_categoria_id')
            ->join('modulos m', 'm.id = mc.id_modulo')
            ->join('produtos p', 'p.id = m.id_produto')
            ->where('cs.id', (int) $id)
            ->where('p.empresa_id', (int) $this->session->userdata('empresa_id'))
            ->get('catalogo_servico cs')->row();
    }

    private function niveis_empresa()
    {
        return $this->db->select('id, descricao, cor, actived')
            ->where('empresa_id', (int) $this->session->userdata('empresa_id'))
            ->order_by('actived', 'DESC')
            ->order_by('descricao', 'ASC')
            ->get('nivel')->result();
    }

    // Produtos, módulos e categorias da empresa, para a cascata do formulário.
    private function arvore_empresa()
    {
        $empresa_id = (int) $this->session->userdata('empresa_id');
        $produtos = $this->db->select('id, nome, status')->where('empresa_id', $empresa_id)->order_by('nome')->get('produtos')->result();
        $modulos = $this->db->select('m.id, m.id_produto, m.nome, m.status')->join('produtos p', 'p.id = m.id_produto')
            ->where('p.empresa_id', $empresa_id)->order_by('m.nome')->get('modulos m')->result();
        $categorias = $this->db->select('mc.id, mc.id_modulo, mc.nome, mc.status')->join('modulos m', 'm.id = mc.id_modulo')->join('produtos p', 'p.id = m.id_produto')
            ->where('p.empresa_id', $empresa_id)->order_by('mc.nome')->get('modulos_categoria mc')->result();
        return array('produtos' => $produtos, 'modulos' => $modulos, 'categorias' => $categorias);
    }

    public function _categoria_da_empresa($id)
    {
        $existe = $this->db->join('modulos m', 'm.id = mc.id_modulo')->join('produtos p', 'p.id = m.id_produto')
            ->where('mc.id', (int) $id)->where('p.empresa_id', (int) $this->session->userdata('empresa_id'))
            ->count_all_results('modulos_categoria mc') > 0;
        if (!$existe) {
            $this->form_validation->set_message('_categoria_da_empresa', 'Selecione produto, módulo e categoria da empresa.');
        }
        return $existe;
    }

    public function _nivel_da_empresa($id)
    {
        $existe = $this->db->where('id', (int) $id)->where('empresa_id', (int) $this->session->userdata('empresa_id'))->count_all_results('nivel') > 0;
        if (!$existe) {
            $this->form_validation->set_message('_nivel_da_empresa', 'Selecione um nível da empresa.');
        }
        return $existe;
    }

    // Criação ($servico = null) e edição compartilham o formulário admin/catalogo_servicos/_form.
    private function formulario($servico)
    {
        $editando = $servico !== null;
        $this->breadcrumbs->unshift(2, lang($editando ? 'menu_catalogo_servicos_edit' : 'menu_catalogo_servicos_create'), $editando ? 'catalogo_servicos/edit/' . (int) $servico->id : 'catalogo_servicos/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->form_validation->set_rules('descricao', 'Serviço', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('categoria', 'Categoria', 'required|callback__categoria_da_empresa');
        $this->form_validation->set_rules('nivel', 'Nível', 'required|callback__nivel_da_empresa');
        $this->form_validation->set_rules('tempo_padrao', 'Tempo estimado', 'trim|required|validar_decimal');
        $this->form_validation->set_rules('prazo_padrao', 'Prazo', 'trim|required|is_natural');
        $this->form_validation->set_rules('texto', 'Orientações', 'trim');
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[0,1]');

        if ($this->input->post() && $this->form_validation->run() == TRUE) {
            $dados = array(
                'descricao' => $this->input->post('descricao'),
                'modulo_categoria_id' => (int) $this->input->post('categoria'),
                'nivel_id' => (int) $this->input->post('nivel'),
                'tempo_padrao' => number_format((float) str_replace(',', '.', $this->input->post('tempo_padrao')), 2, '.', ''),
                'prazo_padrao' => (int) $this->input->post('prazo_padrao'),
                'texto' => (string) $this->input->post('texto'),
                'actived' => (int) $this->input->post('status'),
            );
            if ($editando) {
                $ok = $this->catalogo_servicos_model->update($servico->id, $dados);
            } else {
                $dados['created'] = date('Y-m-d H:i:s');
                $dados['aplicavel_os'] = 0;
                $ok = $this->catalogo_servicos_model->create($dados);
            }
            if ($ok) {
                $this->session->set_flashdata('message', show_alert_success('Serviço "' . html_escape($dados['descricao']) . '" ' . ($editando ? 'atualizado.' : 'criado.')));
                redirect('catalogo_servicos', 'refresh');
            }
            $this->data['message'] = show_alert_error($this->catalogo_servicos_model->errors() ? $this->catalogo_servicos_model->errors() : 'Não foi possível salvar o serviço.');
        } elseif ($this->input->post()) {
            $this->data['message'] = show_alert_error(validation_errors());
        }

        $uso = 0;
        if ($editando) {
            $uso = (int) $this->db->where('catalogoservico_id', (int) $servico->id)->where('created >=', date('Y-m-d', strtotime('-90 days')))->count_all_results('chamado');
        }

        $post = $this->input->post();
        $this->data['modo'] = $editando ? 'edit' : 'create';
        $this->data['servico'] = $servico;
        $this->data['uso_90'] = $uso;
        $this->data['arvore'] = $this->arvore_empresa();
        $this->data['niveis'] = $this->niveis_empresa();
        $this->data['valores'] = array(
            'descricao' => $post ? (string) $this->input->post('descricao') : ($editando ? $servico->descricao : ''),
            'produto' => $post ? (int) $this->input->post('produto') : ($editando ? (int) $servico->produto_id : 0),
            'modulo' => $post ? (int) $this->input->post('modulo') : ($editando ? (int) $servico->modulo_id : 0),
            'categoria' => $post ? (int) $this->input->post('categoria') : ($editando ? (int) $servico->modulo_categoria_id : 0),
            'nivel' => $post ? (int) $this->input->post('nivel') : ($editando ? (int) $servico->nivel_id : 0),
            'tempo_padrao' => $post ? (string) $this->input->post('tempo_padrao') : ($editando ? str_replace('.', ',', rtrim(rtrim($servico->tempo_padrao, '0'), '.')) : ''),
            'prazo_padrao' => $post ? (string) $this->input->post('prazo_padrao') : ($editando ? (string) $servico->prazo_padrao : ''),
            'texto' => $post ? (string) $this->input->post('texto') : ($editando ? $servico->texto : ''),
            'status' => $post ? (string) $this->input->post('status') : ($editando ? (string) (int) $servico->actived : '1'),
        );

        $this->template->admin_render('admin/catalogo_servicos/_form', $this->data);
    }
}
