<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Novidades: cadastro de avisos com período e imagens. Cada usuário vê a novidade vigente uma vez
 * em um modal; ao fechar, a leitura é registrada e ela passa a ficar no sino de notificações.
 *
 * Acesso à gestão (index, create, edit, delete): só o grupo admin da empresa (is_admin), sem permissão por grupo.
 * detalhe() e marcar_lida() servem a todos os usuários logados (modal e sino) e só retornam novidades vigentes da empresa da sessão.
 */
class Novidades extends Admin_Controller {

    const MAX_IMAGENS = 10;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('novidades_model');
        $this->load->library('form_validation');
        $this->load->library('foto_usuario', array('pasta' => 'upload/novidades/', 'lado_miniatura' => 1600, 'miniatura_quadrada' => false), 'imagem_novidade');

        $this->page_title->push('Novidades');
        $this->data['pagetitle'] = $this->page_title->show();
        $this->breadcrumbs->unshift(1, 'Novidades', 'novidades');
    }

    public function index()
    {
        $this->exigir_admin();

        $empresa_id = (int) $this->session->userdata('empresa_id');
        $situacoes = array('vigente', 'agendada', 'encerrada', 'inativa');
        $filtros = array(
            'q' => trim((string) $this->input->get('q')),
            'situacao' => in_array($this->input->get('situacao'), $situacoes, true) ? $this->input->get('situacao') : '',
        );

        $novidades = $this->novidades_model->listar($empresa_id, $filtros);
        $agora = date('Y-m-d H:i:s');
        foreach ($novidades as $novidade) {
            $novidade->situacao = $this->novidades_model->situacao($novidade, $agora);
        }

        $this->data['novidades'] = $novidades;
        $this->data['totais'] = $this->novidades_model->totais($empresa_id);
        $this->data['filtros'] = $filtros;
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['pode_criar'] = true;
        $this->data['pode_editar'] = true;
        $this->data['pode_excluir'] = true;

        $this->template->admin_render('admin/novidades/index', $this->data);
    }

    public function create()
    {
        $this->exigir_admin();
        $this->breadcrumbs->unshift(2, 'Nova novidade', 'novidades/create');

        $novidade = (object) array(
            'id' => 0,
            'titulo' => '',
            'conteudo' => '',
            'data_inicio' => date('Y-m-d H:i'),
            'data_fim' => date('Y-m-d 23:59', strtotime('+7 days')),
            'ativo' => 1,
        );

        if ($this->input->method() === 'post') {
            $dados = $this->validar_formulario();
            if ($dados !== false) {
                $dados['empresa_id'] = (int) $this->session->userdata('empresa_id');
                $dados['criado_por'] = (int) $this->session->userdata('user_id');
                $id = $this->novidades_model->criar($dados);
                if ($id) {
                    $erros = $this->salvar_imagens($id, 0);
                    $this->auditar('create', 'Novidade #' . $id . ' criada: ' . $dados['titulo']);
                    $this->session->set_flashdata('message', show_alert_success('Novidade cadastrada.') . ($erros ? show_alert_error('Algumas imagens não foram salvas:<br>' . implode('<br>', $erros)) : ''));
                    redirect('novidades', 'refresh');
                }
                $this->data['message'] = show_alert_error('Não foi possível salvar a novidade. Verifique se o script sql/2026-11-12_novidades.sql foi aplicado no banco.');
            }
            $novidade = $this->novidade_do_post($novidade);
        }

        $this->renderizar_formulario($novidade, array());
    }

    public function edit($id = null)
    {
        $this->exigir_admin();
        $empresa_id = (int) $this->session->userdata('empresa_id');
        $novidade = $this->novidades_model->get($id, $empresa_id);
        if (!$novidade) {
            $this->session->set_flashdata('message', show_alert_error('Novidade não encontrada.'));
            redirect('novidades', 'refresh');
        }
        $this->breadcrumbs->unshift(2, 'Editar novidade', 'novidades/edit/' . (int) $novidade->id);

        if ($this->input->method() === 'post') {
            $dados = $this->validar_formulario();
            if ($dados !== false) {
                if ($this->novidades_model->atualizar($novidade->id, $empresa_id, $dados)) {
                    foreach ($this->novidades_model->remover_imagens($novidade->id, $this->input->post('remover_imagens')) as $arquivo) {
                        $this->imagem_novidade->remover($arquivo);
                    }
                    $restantes = count($this->novidades_model->imagens($novidade->id));
                    $erros = $this->salvar_imagens($novidade->id, $restantes);
                    $obs = 'Novidade #' . $novidade->id . ' alterada.';
                    if ($this->input->post('reexibir')) {
                        $this->db->delete('novidades_leituras', array('novidade_id' => (int) $novidade->id));
                        $obs .= ' Leituras zeradas para mostrar de novo a todos.';
                    }
                    $this->auditar('edit', $obs);
                    $this->session->set_flashdata('message', show_alert_success('Novidade atualizada.') . ($erros ? show_alert_error('Algumas imagens não foram salvas:<br>' . implode('<br>', $erros)) : ''));
                    redirect('novidades', 'refresh');
                }
                $this->data['message'] = show_alert_error('Não foi possível salvar a novidade.');
            }
            $novidade = $this->novidade_do_post($novidade);
        }

        $this->renderizar_formulario($novidade, $this->novidades_model->imagens($novidade->id));
    }

    public function delete($id = null)
    {
        $this->exigir_admin();
        if ($this->input->method() !== 'post') {
            redirect('novidades', 'refresh');
        }
        $arquivos = $this->novidades_model->excluir($id, (int) $this->session->userdata('empresa_id'));
        if ($arquivos === false) {
            $this->session->set_flashdata('message', show_alert_error('Novidade não encontrada.'));
        } else {
            foreach ($arquivos as $arquivo) {
                $this->imagem_novidade->remover($arquivo);
            }
            $this->auditar('delete', 'Novidade #' . (int) $id . ' excluída.');
            $this->session->set_flashdata('message', show_alert_success('Novidade excluída.'));
        }
        redirect('novidades', 'refresh');
    }

    /**
     * JSON de uma novidade vigente, para reabrir o modal pelo sino.
     */
    public function detalhe($id = null)
    {
        $this->output->set_content_type('application/json');
        $empresa_id = (int) $this->session->userdata('empresa_id');
        $novidade = $this->novidades_model->get($id, $empresa_id);
        if (!$novidade || $this->novidades_model->situacao($novidade) !== 'vigente') {
            $this->output->set_status_header(404)->set_output(json_encode(array('ok' => false, 'message' => 'Novidade não encontrada.')));
            return;
        }
        $exibicao = $this->novidades_model->para_exibicao(array($novidade));
        $this->output->set_output(json_encode(array('ok' => true, 'novidade' => $exibicao[0])));
    }

    /**
     * Registra que o usuário logado fechou o modal da novidade.
     */
    public function marcar_lida()
    {
        $this->output->set_content_type('application/json');
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405)->set_output(json_encode(array('ok' => false)));
            return;
        }
        $ok = $this->novidades_model->marcar_lida(
            (int) $this->input->post('id'),
            (int) $this->session->userdata('empresa_id'),
            (int) $this->session->userdata('user_id')
        );
        $this->output->set_output(json_encode(array('ok' => (bool) $ok)));
    }

    // Novidades valem para a empresa inteira: só o grupo admin (definido nas Preferências) cadastra.
    private function exigir_admin()
    {
        if (!$this->ion_auth->is_admin()) {
            $this->session->set_flashdata('message', show_alert_error('Só usuários do grupo admin podem gerenciar as novidades.'));
            redirect('/', 'refresh');
        }
    }

    /**
     * Valida o formulário e devolve os dados para gravar, ou false (mensagem em $this->data['message']).
     */
    private function validar_formulario()
    {
        $this->form_validation->set_rules('titulo', 'Título', 'trim|required|max_length[150]');
        $this->form_validation->set_rules('conteudo', 'O que será informado', 'trim|required|max_length[20000]');
        $this->form_validation->set_rules('data_inicio', 'Início', 'required');
        $this->form_validation->set_rules('data_fim', 'Fim', 'required');

        if ($this->form_validation->run() !== TRUE) {
            $this->data['message'] = show_alert_error(validation_errors());
            return false;
        }

        $inicio = $this->data_do_campo($this->input->post('data_inicio'));
        $fim = $this->data_do_campo($this->input->post('data_fim'));
        if (!$inicio || !$fim) {
            $this->data['message'] = show_alert_error('Informe início e fim no formato de data e hora.');
            return false;
        }
        if ($fim <= $inicio) {
            $this->data['message'] = show_alert_error('O fim do período precisa ser depois do início.');
            return false;
        }

        return array(
            'titulo' => $this->input->post('titulo'),
            'conteudo' => $this->input->post('conteudo'),
            'data_inicio' => $inicio,
            'data_fim' => $fim,
            'ativo' => $this->input->post('ativo') ? 1 : 0,
        );
    }

    // Campo datetime-local ("2026-11-12T08:30") para "2026-11-12 08:30:00".
    private function data_do_campo($valor)
    {
        $valor = str_replace('T', ' ', trim((string) $valor));
        $data = DateTime::createFromFormat('Y-m-d H:i', substr($valor, 0, 16));
        return $data ? $data->format('Y-m-d H:i') . ':00' : false;
    }

    // Mantém o que foi digitado quando a validação falha.
    private function novidade_do_post($novidade)
    {
        $novidade->titulo = (string) $this->input->post('titulo');
        $novidade->conteudo = (string) $this->input->post('conteudo');
        $novidade->data_inicio = str_replace('T', ' ', (string) $this->input->post('data_inicio'));
        $novidade->data_fim = str_replace('T', ' ', (string) $this->input->post('data_fim'));
        $novidade->ativo = $this->input->post('ativo') ? 1 : 0;
        return $novidade;
    }

    /**
     * Grava as imagens enviadas em imagens[] (cada uma com no máximo 5 MB; acima disso é compactada).
     */
    private function salvar_imagens($novidade_id, $existentes)
    {
        $erros = array();
        if (empty($_FILES['imagens']['name']) || !is_array($_FILES['imagens']['name'])) {
            return $erros;
        }
        $total = $existentes;
        foreach ($_FILES['imagens']['name'] as $i => $nome) {
            if ($_FILES['imagens']['error'][$i] === UPLOAD_ERR_NO_FILE || $nome === '') {
                continue;
            }
            if ($total >= self::MAX_IMAGENS) {
                $erros[] = html_escape($nome) . ': limite de ' . self::MAX_IMAGENS . ' imagens por novidade.';
                continue;
            }
            $arquivo = array(
                'name' => $nome,
                'type' => $_FILES['imagens']['type'][$i],
                'tmp_name' => $_FILES['imagens']['tmp_name'][$i],
                'error' => $_FILES['imagens']['error'][$i],
                'size' => $_FILES['imagens']['size'][$i],
            );
            $salvo = $this->imagem_novidade->salvar($arquivo, $novidade_id, 'n');
            if ($salvo === false) {
                $erros[] = html_escape($nome) . ': ' . html_escape($this->imagem_novidade->erro());
                continue;
            }
            $this->novidades_model->adicionar_imagem($novidade_id, $salvo);
            $total++;
        }
        return $erros;
    }

    private function renderizar_formulario($novidade, $imagens)
    {
        foreach ($imagens as $imagem) {
            $imagem->url = $this->imagem_novidade->url($imagem->arquivo);
            $imagem->original = $this->imagem_novidade->url($imagem->arquivo, false);
        }
        $this->data['novidade'] = $novidade;
        $this->data['imagens'] = $imagens;
        $this->data['max_imagens'] = self::MAX_IMAGENS;
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        if (!isset($this->data['message'])) {
            $this->data['message'] = $this->session->flashdata('message');
        }
        $this->template->admin_render('admin/novidades/form', $this->data);
    }

    private function auditar($metodo, $obs)
    {
        $this->ion_auth->create_auditoria(array(
            'user_id' => (int) $this->session->userdata('user_id'),
            'empresa_id' => (int) $this->session->userdata('empresa_id'),
            'controlador' => 'novidades',
            'metodo' => $metodo,
            'obs' => $obs,
        ));
    }
}
