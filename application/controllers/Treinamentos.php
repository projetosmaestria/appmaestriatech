<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Treinamentos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        // Carregar models necessarios
        $this->load->model('treinamentos_model');
        $this->load->model('treinamentos_etapas_model');
        $this->load->model('treinamentos_progresso_model');
        $this->load->model('treinamentos_avaliacoes_model');
        $this->load->model('treinamentos_banners_model');

        // Carregar idioma e helpers
        $this->lang->load('admin/treinamentos');
        $this->load->helper('download');
        $this->load->helper('text');

        // Titulo e Breadcrumbs padrao
        $this->page_title->push(lang('menu_treinamentos'));
        $this->data['pagetitle'] = $this->page_title->show();
        $this->breadcrumbs->unshift(1, lang('menu_treinamentos'), 'treinamentos');
    }

    /**
     * Dashboard principal estilo Academy (https://academy.avantejuntos.com.br/)
     */
    public function index()
    {
        $userId = (int)$this->session->userdata('user_id');
        $empresaId = (int)$this->session->userdata('empresa_id');

        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message']    = $this->session->flashdata('message');

        // 1. Banners ativos do carrossel
        $this->data['banners'] = $this->treinamentos_banners_model->get_banners_ativos($empresaId);

        // 2. Seção "Continue de onde parou"
        $this->data['em_andamento'] = $this->treinamentos_progresso_model->get_treinamentos_em_andamento($userId, 6);

        // 3. Seção "Explore os Módulos"
        $this->data['modulos'] = $this->treinamentos_model->get_modulos_com_treinamentos($empresaId, $userId);
        $this->data['todos_modulos'] = $this->db->where('status', 'S')->order_by('nome', 'ASC')->get('modulos')->result();

        // 4. Seção "Treinamentos em Destaque"
        $this->data['destaques'] = $this->treinamentos_model->get_treinamentos(array(
            'empresa_id' => $empresaId,
            'destaque'   => 1,
            'ativo'      => 1,
            'limit'      => 6
        ));

        // 5. Seção "Novidades e Atualizações"
        $this->data['novidades'] = $this->treinamentos_model->get_treinamentos(array(
            'empresa_id' => $empresaId,
            'novidade'   => 1,
            'ativo'      => 1,
            'limit'      => 6
        ));

        // 6. Catálogo com busca e filtros
        $busca = $this->input->get('q', TRUE);
        $filtroModulo = $this->input->get('modulo', TRUE);
        $filtroNivel = $this->input->get('nivel', TRUE);

        $paramsCatalogo = array(
            'empresa_id' => $empresaId,
            'ativo'      => 1
        );
        if (!empty($busca)) $paramsCatalogo['busca'] = $busca;
        if (!empty($filtroModulo)) $paramsCatalogo['modulo_id'] = $filtroModulo;
        if (!empty($filtroNivel)) $paramsCatalogo['nivel'] = $filtroNivel;

        $catalogo = $this->treinamentos_model->get_treinamentos($paramsCatalogo);

        // Anexar progresso do usuario em cada treinamento do catalogo
        foreach ($catalogo as &$cat) {
            $cat->progresso = $this->treinamentos_progresso_model->get_progresso_treinamento($cat->id, $userId);
        }
        $this->data['catalogo'] = $catalogo;
        $this->data['busca_atual'] = $busca;
        $this->data['modulo_atual'] = $filtroModulo;
        $this->data['nivel_atual'] = $filtroNivel;

        // 7. Seção "Meu Progresso"
        $this->data['meu_progresso'] = $this->treinamentos_progresso_model->get_resumo_progresso_usuario($userId);

        // Renderiza View
        $this->template->admin_render('admin/treinamentos/dashboard', $this->data);
    }

    /**
     * Treinamentos filtrados por módulo
     */
    public function modulo($modulo_id)
    {
        $userId = (int)$this->session->userdata('user_id');
        $empresaId = (int)$this->session->userdata('empresa_id');

        $this->load->model('produtos_modulos_model');
        $modulo = $this->db->where('id', (int)$modulo_id)->get('modulos')->row();

        if (!$modulo) {
            $this->session->set_flashdata('message', show_alert_error('Módulo não encontrado.'));
            redirect('treinamentos');
        }

        $this->breadcrumbs->unshift(2, $modulo->nome, 'treinamentos/modulo/' . $modulo_id);
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['modulo']     = $modulo;

        $treinamentos = $this->treinamentos_model->get_treinamentos(array(
            'empresa_id' => $empresaId,
            'modulo_id'  => (int)$modulo_id,
            'ativo'      => 1
        ));

        foreach ($treinamentos as &$tr) {
            $tr->progresso = $this->treinamentos_progresso_model->get_progresso_treinamento($tr->id, $userId);
        }

        $this->data['treinamentos'] = $treinamentos;
        $this->template->admin_render('admin/treinamentos/modulo', $this->data);
    }

    /**
     * Apresentação e detalhes do treinamento
     */
    public function ver($id)
    {
        $this->detalhes($id);
    }

    public function detalhes($id)
    {
        $userId = (int)$this->session->userdata('user_id');
        $treinamento = $this->treinamentos_model->get_treinamento($id);

        if (!$treinamento || $treinamento->ativo != 1) {
            $this->session->set_flashdata('message', show_alert_error('Treinamento não encontrado ou indisponível.'));
            redirect('treinamentos');
        }

        // Validação de acesso individual ou por perfil
        if (!$this->treinamentos_model->verificar_acesso($id, $userId)) {
            $this->session->set_flashdata('message', show_alert_error('Você não tem autorização para acessar este treinamento.'));
            redirect('treinamentos');
        }

        $this->breadcrumbs->unshift(2, $treinamento->titulo, 'treinamentos/ver/' . $id);
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['treinamento'] = $treinamento;

        // Lista ordenada de etapas com progresso do usuario
        $etapas = $this->treinamentos_etapas_model->get_etapas($id);
        $primeiraNaoConcluida = null;

        foreach ($etapas as &$ep) {
            $ep->progresso = $this->treinamentos_progresso_model->get_progresso_etapa($ep->id, $userId);
            $ep->liberada  = $this->treinamentos_progresso_model->verificar_etapa_liberada($id, $ep->id, $userId);

            if ($ep->liberada && (!$ep->progresso || $ep->progresso->status !== 'concluido') && !$primeiraNaoConcluida) {
                $primeiraNaoConcluida = $ep;
            }
        }
        $this->data['etapas'] = $etapas;
        $this->data['etapa_continuar'] = $primeiraNaoConcluida ? $primeiraNaoConcluida : (isset($etapas[0]) ? $etapas[0] : null);

        // Progresso geral do usuario
        $this->data['progresso'] = $this->treinamentos_progresso_model->get_progresso_treinamento($id, $userId);

        // Avaliacao existente se houver
        $this->data['minha_avaliacao'] = $this->treinamentos_avaliacoes_model->get_avaliacao_usuario($id, $userId);
        $this->data['estatisticas_avaliacao'] = $this->treinamentos_avaliacoes_model->get_estatisticas_treinamento($id);

        $this->template->admin_render('admin/treinamentos/detalhes', $this->data);
    }

    /**
     * Sala de aula interativa da etapa
     */
    public function etapa($treinamento_id, $etapa_id)
    {
        $userId = (int)$this->session->userdata('user_id');

        $treinamento = $this->treinamentos_model->get_treinamento($treinamento_id);
        if (!$treinamento || $treinamento->ativo != 1) {
            $this->session->set_flashdata('message', show_alert_error('Treinamento não encontrado.'));
            redirect('treinamentos');
        }

        // Validação de acesso ao treinamento
        if (!$this->treinamentos_model->verificar_acesso($treinamento_id, $userId)) {
            $this->session->set_flashdata('message', show_alert_error('Acesso não autorizado a este treinamento.'));
            redirect('treinamentos');
        }

        $etapa = $this->treinamentos_etapas_model->get_etapa($etapa_id);
        if (!$etapa || $etapa->treinamento_id != $treinamento_id || $etapa->ativo != 1) {
            $this->session->set_flashdata('message', show_alert_error('Etapa não encontrada.'));
            redirect('treinamentos/ver/' . $treinamento_id);
        }

        // Validação de progressão: verificar se etapas anteriores obrigatorias estao concluidas
        if (!$this->treinamentos_progresso_model->verificar_etapa_liberada($treinamento_id, $etapa_id, $userId)) {
            $this->session->set_flashdata('message', show_alert_error('Atenção: Você precisa concluir as etapas anteriores antes de acessar este conteúdo.'));
            redirect('treinamentos/ver/' . $treinamento_id);
        }

        // Registrar acesso a etapa atual e controle de atividade onde o usuario parou
        $this->treinamentos_progresso_model->registrar_acesso_etapa($treinamento_id, $etapa_id, $userId);

        // Breadcrumbs
        $this->breadcrumbs->unshift(2, $treinamento->titulo, 'treinamentos/ver/' . $treinamento_id);
        $this->breadcrumbs->unshift(3, $etapa->titulo, 'treinamentos/etapa/' . $treinamento_id . '/' . $etapa_id);
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->data['treinamento'] = $treinamento;
        $this->data['etapa']       = $etapa;

        // Todas as etapas para a sidebar de navegação
        $etapas = $this->treinamentos_etapas_model->get_etapas($treinamento_id);
        foreach ($etapas as &$ep) {
            $ep->progresso = $this->treinamentos_progresso_model->get_progresso_etapa($ep->id, $userId);
            $ep->liberada  = $this->treinamentos_progresso_model->verificar_etapa_liberada($treinamento_id, $ep->id, $userId);
        }
        $this->data['todas_etapas'] = $etapas;

        // Próxima e anterior
        $this->data['proxima_etapa']  = $this->treinamentos_etapas_model->get_proxima_etapa($treinamento_id, $etapa->ordem);
        $this->data['etapa_anterior'] = $this->treinamentos_etapas_model->get_etapa_anterior($treinamento_id, $etapa->ordem);

        // Materiais de apoio vinculados
        $this->data['arquivos'] = $this->treinamentos_etapas_model->get_arquivos($etapa_id);

        // Atividades orientativas
        $this->data['atividades'] = $this->treinamentos_etapas_model->get_atividades($etapa_id);
        $this->data['atividades_concluidas_ids'] = $this->treinamentos_progresso_model->get_atividades_concluidas_ids($etapa_id, $userId);

        // Progresso atual da etapa
        $this->data['progresso_etapa'] = $this->treinamentos_progresso_model->get_progresso_etapa($etapa_id, $userId);

        // Avaliação (se for última etapa ou geral)
        $this->data['minha_avaliacao'] = $this->treinamentos_avaliacoes_model->get_avaliacao_usuario($treinamento_id, $userId);
        $this->data['eh_ultima_etapa'] = ($this->data['proxima_etapa'] === null);

        $this->template->admin_render('admin/treinamentos/etapa', $this->data);
    }

    /**
     * AJAX: Salva progresso de reprodução do vídeo
     */
    public function salvar_progresso_video()
    {
        $userId = (int)$this->session->userdata('user_id');
        $etapaId = (int)$this->input->post('etapa_id');
        $tempoSegundos = (int)$this->input->post('tempo_segundos');
        $concluido = ($this->input->post('concluido') == '1' || $this->input->post('concluido') === 'true');

        if (!$etapaId) {
            echo json_encode(array('success' => false, 'message' => 'Etapa inválida.'));
            return;
        }

        $etapa = $this->treinamentos_etapas_model->get_etapa($etapaId);
        if ($etapa) {
            $acao = $concluido 
                ? 'Concluiu o vídeo da Etapa ' . $etapa->ordem . ': ' . $etapa->titulo
                : 'Assistindo vídeo da Etapa ' . $etapa->ordem . ' (' . gmdate('i:s', $tempoSegundos) . ')';
            $this->treinamentos_progresso_model->iniciar_treinamento($etapa->treinamento_id, $userId, $etapaId, $acao);
        }

        $res = $this->treinamentos_progresso_model->salvar_progresso_video($etapaId, $userId, $tempoSegundos, $concluido);
        echo json_encode(array('success' => $res, 'concluido' => $concluido));
    }

    /**
     * AJAX: Alterna o status de conclusão de atividade orientativa
     */
    public function toggle_atividade()
    {
        $userId = (int)$this->session->userdata('user_id');
        $atividadeId = (int)$this->input->post('atividade_id');

        if (!$atividadeId) {
            echo json_encode(array('success' => false, 'message' => 'Atividade inválida.'));
            return;
        }

        $atividade = $this->treinamentos_etapas_model->get_atividade($atividadeId);
        $novoStatus = $this->treinamentos_progresso_model->toggle_atividade($atividadeId, $userId);

        if ($atividade) {
            $statusStr = ($novoStatus == 1) ? 'Marcou tarefa: ' : 'Desmarcou tarefa: ';
            $this->treinamentos_progresso_model->iniciar_treinamento($atividade->treinamento_id, $userId, $atividade->etapa_id, $statusStr . $atividade->titulo);
        }

        echo json_encode(array('success' => true, 'concluida' => ($novoStatus == 1)));
    }

    /**
     * AJAX/POST: Conclui a etapa após validação completa dos requisitos
     */
    public function concluir_etapa()
    {
        $userId = (int)$this->session->userdata('user_id');
        $etapaId = (int)$this->input->post('etapa_id');

        if (!$etapaId) {
            echo json_encode(array('success' => false, 'message' => 'Etapa inválida.'));
            return;
        }

        $resultado = $this->treinamentos_progresso_model->concluir_etapa($etapaId, $userId);

        if ($resultado['permitido']) {
            $etapa = $this->treinamentos_etapas_model->get_etapa($etapaId);
            $proxima = $this->treinamentos_etapas_model->get_proxima_etapa($etapa->treinamento_id, $etapa->ordem);

            if ($proxima) {
                $this->treinamentos_progresso_model->iniciar_treinamento($etapa->treinamento_id, $userId, $proxima->id, 'Concluiu Etapa ' . $etapa->ordem . ' e avançou para Etapa ' . $proxima->ordem);
            } else {
                $this->treinamentos_progresso_model->iniciar_treinamento($etapa->treinamento_id, $userId, $etapaId, 'Concluiu todas as etapas do treinamento');
            }

            $proximaUrl = $proxima 
                ? site_url('treinamentos/etapa/' . $etapa->treinamento_id . '/' . $proxima->id)
                : site_url('treinamentos/ver/' . $etapa->treinamento_id);

            echo json_encode(array(
                'success' => true,
                'message' => $resultado['motivo'],
                'eh_ultima' => ($proxima === null),
                'proxima_url' => $proximaUrl
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => $resultado['motivo']
            ));
        }
    }

    /**
     * AJAX: Envio de avaliação de satisfação (1 a 5 estrelas)
     */
    public function avaliar()
    {
        $userId = (int)$this->session->userdata('user_id');
        $treinamentoId = (int)$this->input->post('treinamento_id');
        $nota = (int)$this->input->post('nota');
        $comentario = $this->input->post('comentario', TRUE);

        if (!$treinamentoId || $nota < 1 || $nota > 5) {
            echo json_encode(array('success' => false, 'message' => 'Por favor, selecione uma nota de 1 a 5 estrelas.'));
            return;
        }

        $salvou = $this->treinamentos_avaliacoes_model->salvar_avaliacao($treinamentoId, $userId, $nota, $comentario);

        if ($salvou) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Muito obrigado! Sua avaliação foi registrada com sucesso.'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Ocorreu um erro ao salvar sua avaliação. Tente novamente.'
            ));
        }
    }

    /**
     * Download seguro de material de apoio
     */
    public function download_arquivo($arquivo_id)
    {
        $userId = (int)$this->session->userdata('user_id');
        $arquivo = $this->treinamentos_etapas_model->get_arquivo($arquivo_id);

        if (!$arquivo) {
            show_404();
        }

        // Validação de acesso ao treinamento
        if (!$this->treinamentos_model->verificar_acesso($arquivo->treinamento_id, $userId)) {
            show_error('Acesso não autorizado a este arquivo.', 403);
        }

        $caminhoCompleto = FCPATH . $arquivo->caminho;
        if (!file_exists($caminhoCompleto)) {
            $this->session->set_flashdata('message', show_alert_error('O arquivo solicitado não foi encontrado no servidor.'));
            redirect('treinamentos/ver/' . $arquivo->treinamento_id);
        }

        force_download($arquivo->nome_original, file_get_contents($caminhoCompleto));
    }

    /**
     * Página do histórico e progresso do usuário
     */
    public function meu_progresso()
    {
        $userId = (int)$this->session->userdata('user_id');

        $this->breadcrumbs->unshift(2, lang('menu_treinamentos_meu_progresso'), 'treinamentos/meu_progresso');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        // Resumo estatístico
        $this->data['resumo'] = $this->treinamentos_progresso_model->get_resumo_progresso_usuario($userId);

        // Listagem de todos os treinamentos com progresso
        $this->db->select('tp.*, t.titulo, t.imagem_capa, t.carga_horaria_minutos, t.nivel, m.nome as modulo_nome');
        $this->db->from('treinamento_progresso tp');
        $this->db->join('treinamentos t', 't.id = tp.treinamento_id', 'inner');
        $this->db->join('modulos m', 'm.id = t.modulo_id', 'left');
        $this->db->where('tp.user_id', $userId);
        $this->db->where('t.ativo', 1);
        $this->db->order_by('tp.ultima_atividade DESC, tp.id DESC');
        $this->data['historico'] = $this->db->get()->result();

        $this->template->admin_render('admin/treinamentos/meu_progresso', $this->data);
    }
}
