<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Habilitacoes extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/habilitacoes');
        $this->load->model('habilitacoes_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_habilitacoes'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_habilitacoes'), 'habilitacoes');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("habilitacoes", "index"))
        {
            redirect('auth/login', 'refresh');
        }

        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['indicadores'] = $this->habilitacoes_model->indicadores();
        $this->data['produtos_filtro'] = $this->habilitacoes_model->produtos_empresa();
        $this->data['pode_historico'] = $this->ion_auth->has_permissao("habilitacoes", "historico");
        $this->data['dias_alerta'] = Habilitacoes_model::DIAS_ALERTA;
        $this->data['message'] = $this->session->flashdata('message');

        $this->template->admin_render('admin/habilitacoes/index', $this->data);
	}

    public function server_side()
    {
        $this->output->set_content_type('application/json');
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao('habilitacoes', 'index')) {
            $this->output->set_status_header(403)->set_output(json_encode(array('data' => array(), 'recordsTotal' => 0, 'recordsFiltered' => 0)));
            return;
        }

        $situacoes = array('', 'vigente', 'vencendo', 'vencida', 'sem');
        $situacao = (string) $this->input->post('situacao');
        $filtros = array(
            'q' => trim((string) $this->input->post('q')),
            'situacao' => in_array($situacao, $situacoes, true) ? $situacao : '',
            'produto' => (int) $this->input->post('produto'),
        );

        $ordem_post = $this->input->post('order');
        $coluna = isset($ordem_post[0]['column']) ? (int) $ordem_post[0]['column'] : 0;
        $direcao = (isset($ordem_post[0]['dir']) && $ordem_post[0]['dir'] === 'desc') ? '_desc' : '';
        $ordem = ($coluna === 2 ? 'vencimento' : 'cliente') . $direcao;

        $inicio = max(0, (int) $this->input->post('start'));
        $quantidade = (int) $this->input->post('length');
        if ($quantidade <= 0 || $quantidade > 100) {
            $quantidade = 25;
        }

        $pode_historico = $this->ion_auth->has_permissao('habilitacoes', 'historico');
        $linhas = array();
        foreach ($this->habilitacoes_model->clientes_lista($filtros, $inicio, $quantidade, $ordem) as $cliente) {
            $linhas[] = array(
                $this->html_cliente($cliente),
                $this->html_produtos($cliente),
                $this->html_vencimento($cliente->proximo_vencimento),
                $this->html_acoes($cliente, $pode_historico),
            );
        }

        $this->output->set_output(json_encode(array(
            'draw' => (int) $this->input->post('draw'),
            'recordsTotal' => $this->habilitacoes_model->clientes_total(),
            'recordsFiltered' => $this->habilitacoes_model->clientes_total($filtros),
            'data' => $linhas,
        )));
    }

	public function historico($cliente = null)
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("habilitacoes", "historico"))
        {
            redirect('auth/login', 'refresh');
        }

        $cliente = $this->habilitacoes_model->get_cliente((int) $cliente);
        if (!$cliente) {
            $this->session->set_flashdata('message', show_alert_error('Cliente não encontrado na empresa selecionada.'));
            redirect('habilitacoes', 'refresh');
        }

        $habilitacoes = $this->habilitacoes_model->habilitacoes_cliente($cliente->id);
        $resumo = array('vigente' => 0, 'vencendo' => 0, 'vencida' => 0, 'desabilitada' => 0);
        foreach ($habilitacoes as $habilitacao) {
            $resumo[$habilitacao->situacao]++;
        }

        $this->breadcrumbs->unshift(2, $cliente->unidade, 'habilitacoes/historico/' . (int) $cliente->id);
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['cliente'] = $cliente;
        $this->data['habilitacoes'] = $habilitacoes;
        $this->data['resumo'] = $resumo;
        $this->data['pode_gerenciar'] = $this->ion_auth->has_permissao("habilitacoes", "index");
        $this->data['dias_alerta'] = Habilitacoes_model::DIAS_ALERTA;
        $this->data['message'] = $this->session->flashdata('message');

        $this->template->admin_render('admin/habilitacoes/historico', $this->data);
	}

    // Dados do modal "Produtos do cliente": habilitações atuais e produtos que podem ser habilitados.
    public function produtos_cliente()
    {
        if (!$this->pode_gerenciar()) {
            return;
        }
        $cliente = $this->habilitacoes_model->get_cliente((int) $this->input->post('id_cliente'));
        if (!$cliente) {
            $this->responder_json(array('erro' => true, 'msg' => 'Cliente não encontrado na empresa selecionada.'), 404);
            return;
        }

        $habilitados = array();
        foreach ($this->habilitacoes_model->habilitacoes_cliente($cliente->id) as $habilitacao) {
            $historico = array();
            foreach ($habilitacao->historico as $registro) {
                $historico[] = array(
                    'acao' => $registro->acao === 'D' ? 'Desabilitou' : 'Reabilitou',
                    'usuario' => $registro->usuario,
                    'data' => date('d/m/Y H:i', strtotime($registro->data_cadastro)),
                    'observacao' => $registro->observacao,
                );
            }
            $habilitados[] = array(
                'id' => (int) $habilitacao->id,
                'id_produto' => (int) $habilitacao->id_produto,
                'produto' => $habilitacao->produto,
                'aplicacao' => $habilitacao->aplicacao,
                'situacao' => $habilitacao->situacao,
                'dias' => (int) $habilitacao->dias_restantes,
                'demonstracao' => $habilitacao->demonstracao === 'S',
                'inicio' => $this->data_br($habilitacao->periodo_inicio),
                'fim' => $this->data_br($habilitacao->periodo_fim),
                'contrato_sax' => $habilitacao->contrato_sax !== null ? (string) $habilitacao->contrato_sax : '',
                'modulos' => $habilitacao->modulos,
                'historico' => $historico,
            );
        }

        $disponiveis = array();
        foreach ($this->habilitacoes_model->produtos_empresa() as $produto) {
            $disponiveis[] = array(
                'id' => (int) $produto->id,
                'nome' => $produto->nome,
                'aplicacao' => $produto->aplicacao,
                'versao' => $produto->versao,
                'modulos' => $produto->modulos,
            );
        }

        $this->responder_json(array(
            'erro' => false,
            'cliente' => array('id' => (int) $cliente->id, 'nome' => $cliente->unidade, 'razao_social' => $cliente->razao_social),
            'habilitados' => $habilitados,
            'disponiveis' => $disponiveis,
            'dias_alerta' => Habilitacoes_model::DIAS_ALERTA,
        ));
    }

    // Habilita um produto (id_habilitacao vazio) ou altera uma habilitação existente.
    public function salvar_habilitacao()
    {
        if (!$this->pode_gerenciar()) {
            return;
        }

        $id_habilitacao = (int) $this->input->post('id_habilitacao');
        $atual = null;
        if ($id_habilitacao > 0) {
            $atual = $this->habilitacoes_model->get_habilitacao($id_habilitacao);
            if (!$atual) {
                $this->responder_json(array('erro' => true, 'msg' => 'Habilitação não encontrada.'), 404);
                return;
            }
            $cliente = $this->habilitacoes_model->get_cliente($atual->id_cliente);
            $produto = $this->habilitacoes_model->get_produto($atual->id_produto);
        } else {
            $cliente = $this->habilitacoes_model->get_cliente((int) $this->input->post('id_cliente'));
            $produto = $this->habilitacoes_model->get_produto((int) $this->input->post('id_produto'));
        }
        if (!$cliente) {
            $this->responder_json(array('erro' => true, 'msg' => 'Cliente não encontrado na empresa selecionada.'), 400);
            return;
        }
        if (!$produto) {
            $this->responder_json(array('erro' => true, 'msg' => 'Selecione um produto ativo da empresa.'), 400);
            return;
        }

        $demonstracao = $this->input->post('demonstracao') === 'S' ? 'S' : 'N';
        if ($demonstracao === 'S') {
            // Demonstração dura 30 dias a partir de quando começa; salvar de novo não estende o prazo.
            if ($atual && $atual->demonstracao === 'S') {
                $inicio = $atual->periodo_inicio;
                $fim = $atual->periodo_fim;
            } else {
                $inicio = date('Y-m-d');
                $fim = date('Y-m-d', strtotime('+30 days'));
            }
        } else {
            $inicio = $this->data_iso($this->input->post('inicio'));
            $fim = $this->data_iso($this->input->post('fim'));
            if (!$inicio || !$fim) {
                $this->responder_json(array('erro' => true, 'msg' => 'Informe o início e o fim do período (dd/mm/aaaa).'), 400);
                return;
            }
            if ($fim < $inicio) {
                $this->responder_json(array('erro' => true, 'msg' => 'O fim do período precisa ser igual ou posterior ao início.'), 400);
                return;
            }
        }

        $contrato = trim((string) $this->input->post('contrato_sax'));
        if ($contrato !== '' && !ctype_digit($contrato)) {
            $this->responder_json(array('erro' => true, 'msg' => 'O contrato MaestriaTech deve ter apenas números.'), 400);
            return;
        }

        // Só módulos ativos do produto; status L (liberado), C (consulta) ou B (bloqueado).
        $modulos = array();
        $modulos_post = $this->input->post('modulos');
        $modulos_post = is_array($modulos_post) ? $modulos_post : array();
        foreach ($this->habilitacoes_model->modulos_produto($produto->id) as $id_modulo) {
            $item = isset($modulos_post[$id_modulo]) && is_array($modulos_post[$id_modulo]) ? $modulos_post[$id_modulo] : array();
            $status = isset($item['status']) && in_array($item['status'], array('L', 'C', 'B'), true) ? $item['status'] : 'B';
            $usuarios = isset($item['usuarios']) ? (int) $item['usuarios'] : 0;
            $modulos[$id_modulo] = array('status' => $status, 'usuarios' => max(0, min($usuarios, 99999)));
        }

        $salvo = $this->habilitacoes_model->salvar_habilitacao($id_habilitacao, array(
            'id_produto' => (int) $produto->id,
            'id_cliente' => (int) $cliente->id,
            'periodo_inicio' => $inicio,
            'periodo_fim' => $fim,
            'demonstracao' => $demonstracao,
            'contrato_sax' => $contrato !== '' ? (int) $contrato : null,
        ), $modulos);

        if (!$salvo) {
            $this->responder_json(array('erro' => true, 'msg' => 'Não foi possível salvar a habilitação. Tente novamente.'), 500);
            return;
        }
        $this->responder_json(array(
            'erro' => false,
            'id' => (int) $salvo,
            'msg' => ($id_habilitacao > 0 ? 'Habilitação de ' : 'Produto habilitado: ') . $produto->nome . ($id_habilitacao > 0 ? ' atualizada.' : '.'),
        ));
    }

    // Desabilita (status N) ou reabilita (status S) uma habilitação, com justificativa obrigatória.
    public function alterar_status()
    {
        if (!$this->pode_gerenciar()) {
            return;
        }
        $habilitacao = $this->habilitacoes_model->get_habilitacao((int) $this->input->post('id_habilitacao'));
        $status = $this->input->post('status') === 'S' ? 'S' : 'N';
        $justificativa = trim((string) $this->input->post('justificativa'));

        if (!$habilitacao) {
            $this->responder_json(array('erro' => true, 'msg' => 'Habilitação não encontrada.'), 404);
            return;
        }
        if ($justificativa === '') {
            $this->responder_json(array('erro' => true, 'msg' => 'Informe a justificativa.'), 400);
            return;
        }
        if ($habilitacao->status === $status) {
            $this->responder_json(array('erro' => true, 'msg' => $status === 'N' ? 'A habilitação já está desabilitada.' : 'A habilitação já está ativa.'), 400);
            return;
        }
        if (!$this->habilitacoes_model->alterar_status($habilitacao->id, $status, $justificativa)) {
            $this->responder_json(array('erro' => true, 'msg' => 'Não foi possível alterar a habilitação. Tente novamente.'), 500);
            return;
        }
        $this->responder_json(array('erro' => false, 'msg' => $status === 'N' ? 'Produto desabilitado para o cliente.' : 'Produto reabilitado para o cliente.'));
    }

    /* ---------------------------------------------------------------------
     * Auxiliares
     * ------------------------------------------------------------------- */

    private function pode_gerenciar()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->has_permissao('habilitacoes', 'index')) {
            return true;
        }
        $this->responder_json(array('erro' => true, 'msg' => 'Você não tem permissão para gerenciar habilitações.'), 403);
        return false;
    }

    private function responder_json($dados, $status = 200)
    {
        $this->output->set_content_type('application/json')->set_status_header($status)->set_output(json_encode($dados));
    }

    private function data_br($data)
    {
        return ($data && $data !== '0000-00-00') ? date('d/m/Y', strtotime($data)) : '';
    }

    private function data_iso($data)
    {
        $data = trim((string) $data);
        if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data, $partes) || !checkdate((int) $partes[2], (int) $partes[1], (int) $partes[3])) {
            return null;
        }
        return $partes[3] . '-' . $partes[2] . '-' . $partes[1];
    }

    private function html_cliente($cliente)
    {
        $documento = $cliente->cnpj != '' ? $cliente->cnpj : $cliente->cpf;
        $html = '<div class="habil-cliente-nome">' . html_escape($cliente->unidade) . '</div>';
        $detalhes = array();
        if ($cliente->razao_social != '' && $cliente->razao_social !== $cliente->unidade) {
            $detalhes[] = html_escape($cliente->razao_social);
        }
        if ($documento != '') {
            $detalhes[] = html_escape($documento);
        }
        if ($detalhes) {
            $html .= '<div class="habil-cliente-detalhe text-muted">' . implode(' · ', $detalhes) . '</div>';
        }
        return $html;
    }

    private function html_produtos($cliente)
    {
        $html = '';
        if ($cliente->produtos_vigentes) {
            foreach (explode('||', $cliente->produtos_vigentes) as $item) {
                $partes = explode('##', $item);
                if (count($partes) < 4) {
                    continue;
                }
                $dias = (int) floor((strtotime($partes[3]) - strtotime(date('Y-m-d'))) / 86400);
                $classe = $dias <= Habilitacoes_model::DIAS_ALERTA ? ' is-vencendo' : '';
                $html .= '<span class="habil-chip' . $classe . '" title="Até ' . date('d/m/Y', strtotime($partes[3])) . '">'
                    . html_escape($partes[1])
                    . ($partes[2] === 'S' ? ' <small>demo</small>' : '')
                    . '</span>';
            }
        }
        $extras = array();
        if ((int) $cliente->vencidas > 0) {
            $extras[] = '<span class="text-red">' . (int) $cliente->vencidas . ' vencida' . ((int) $cliente->vencidas === 1 ? '' : 's') . '</span>';
        }
        if ((int) $cliente->desabilitadas > 0) {
            $extras[] = (int) $cliente->desabilitadas . ' desabilitada' . ((int) $cliente->desabilitadas === 1 ? '' : 's');
        }
        if ($html === '' && !$extras) {
            return '<span class="text-muted">Nenhum produto habilitado</span>';
        }
        if ($html === '') {
            $html = '<span class="text-muted">Nenhum vigente</span>';
        }
        if ($extras) {
            $html .= '<div class="habil-extras text-muted">' . implode(' · ', $extras) . '</div>';
        }
        return $html;
    }

    private function html_vencimento($data)
    {
        if (!$data) {
            return '<span class="text-muted">—</span>';
        }
        $dias = (int) floor((strtotime($data) - strtotime(date('Y-m-d'))) / 86400);
        $html = '<div class="habil-vencimento">' . date('d/m/Y', strtotime($data)) . '</div>';
        if ($dias === 0) {
            $html .= '<small class="text-red">vence hoje</small>';
        } elseif ($dias <= 15) {
            $html .= '<small class="text-red">em ' . $dias . ' dia' . ($dias === 1 ? '' : 's') . '</small>';
        } elseif ($dias <= Habilitacoes_model::DIAS_ALERTA) {
            $html .= '<small class="text-yellow">em ' . $dias . ' dias</small>';
        } elseif ($dias <= 90) {
            $html .= '<small class="text-muted">em ' . $dias . ' dias</small>';
        }
        return $html;
    }

    private function html_acoes($cliente, $pode_historico)
    {
        $html = '<div class="btn-group habil-acoes">'
            . '<button type="button" class="btn btn-default btn-sm js-gerenciar-produtos" data-cliente="' . (int) $cliente->id . '" title="Habilitar ou alterar produtos"><i class="fa fa-th"></i> Produtos</button>';
        if ($pode_historico) {
            $html .= '<a href="' . base_url('habilitacoes/historico/' . (int) $cliente->id) . '" class="btn btn-default btn-sm" title="Produtos habilitados e histórico"><i class="fa fa-history"></i></a>';
        }
        return $html . '</div>';
    }
}
