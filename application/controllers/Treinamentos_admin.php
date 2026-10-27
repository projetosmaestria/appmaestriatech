<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Treinamentos_admin extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        // Checagem estrita: apenas administradores acessam qualquer tela ou ação administrativa
        $this->checar_permissao();

        // Carregar models necessarios
        $this->load->model('treinamentos_model');
        $this->load->model('treinamentos_etapas_model');
        $this->load->model('treinamentos_progresso_model');
        $this->load->model('treinamentos_avaliacoes_model');
        $this->load->model('treinamentos_banners_model');
        $this->load->model('produtos_modulos_model');
        $this->load->model('produtos_model');

        // Idiomas e bibliotecas
        $this->lang->load('admin/treinamentos');
        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->helper('text');

        // Titulo e Breadcrumbs padrao
        $this->page_title->push(lang('menu_treinamentos_admin'));
        $this->data['pagetitle'] = $this->page_title->show();
        $this->breadcrumbs->unshift(1, lang('menu_treinamentos'), 'treinamentos');
        $this->breadcrumbs->unshift(2, lang('menu_treinamentos_admin'), 'treinamentos_admin');
    }

    /**
     * Valida permissao de acesso a area administrativa
     */
    private function checar_permissao($metodo = 'index')
    {
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login');
        }
        if (!$this->ion_auth->is_admin()) {
            $this->session->set_flashdata('message', show_alert_error('Acesso restrito: apenas administradores possuem autorização para esta área.'));
            redirect('treinamentos');
        }
    }

    /**
     * Listagem de treinamentos cadastrados
     */
    public function index()
    {
        $this->checar_permissao('index');

        $empresaId = (int)$this->session->userdata('empresa_id');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message']    = $this->session->flashdata('message');

        // Filtros
        $moduloId = $this->input->get('modulo_id');
        $params = array('empresa_id' => $empresaId);
        if (!empty($moduloId)) $params['modulo_id'] = $moduloId;

        $treinamentos = $this->treinamentos_model->get_treinamentos($params);

        // Anexar contagens administrativas
        foreach ($treinamentos as &$t) {
            $this->db->where('treinamento_id', $t->id);
            $this->db->where('status', 'concluido');
            $t->total_concluidos = $this->db->count_all_results('treinamento_progresso');

            $this->db->where('treinamento_id', $t->id);
            $this->db->where('status', 'em_andamento');
            $t->total_em_andamento = $this->db->count_all_results('treinamento_progresso');
        }

        $this->data['treinamentos'] = $treinamentos;
        $this->data['modulos']      = $this->db->where('status', 'S')->order_by('nome', 'ASC')->get('modulos')->result();
        $this->data['modulo_selecionado'] = $moduloId;

        $this->template->admin_render('admin/treinamentos/admin/index', $this->data);
    }

    /**
     * Cadastro de novo treinamento
     */
    public function create()
    {
        $this->checar_permissao('create');

        $empresaId = (int)$this->session->userdata('empresa_id');
        $userId    = (int)$this->session->userdata('user_id');

        $this->breadcrumbs->unshift(3, lang('menu_treinamentos_create'), 'treinamentos_admin/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->form_validation->set_rules('titulo', 'Título', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('produto_id', 'Produto', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('modulo_id', 'Módulo', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('carga_horaria_minutos', 'Carga Horária', 'is_natural');

        if ($this->form_validation->run() == TRUE) {
            $produtoId = (int)$this->input->post('produto_id');
            $moduloId  = (int)$this->input->post('modulo_id');
            if (empty($produtoId) && !empty($moduloId)) {
                $modRow = $this->db->select('id_produto')->where('id', $moduloId)->get('modulos')->row();
                if ($modRow) {
                    $produtoId = (int)$modRow->id_produto;
                }
            }

            $data = array(
                'empresa_id'            => $empresaId,
                'produto_id'            => $produtoId,
                'modulo_id'             => $moduloId,
                'titulo'                => $this->input->post('titulo', TRUE),
                'slug'                  => url_title(convert_accented_characters($this->input->post('titulo')), 'dash', TRUE),
                'descricao_curta'       => $this->input->post('descricao_curta', TRUE),
                'descricao_completa'    => $this->input->post('descricao_completa'),
                'objetivo'              => $this->input->post('objetivo', TRUE),
                'carga_horaria_minutos' => (int)$this->input->post('carga_horaria_minutos'),
                'nivel'                 => $this->input->post('nivel'),
                'destaque'              => $this->input->post('destaque') ? 1 : 0,
                'novidade'              => $this->input->post('novidade') ? 1 : 0,
                'avaliacao_obrigatoria' => $this->input->post('avaliacao_obrigatoria') ? 1 : 0,
                'tipo_acesso'           => $this->input->post('tipo_acesso') ? $this->input->post('tipo_acesso') : 'livre',
                'ordem'                 => (int)$this->input->post('ordem'),
                'ativo'                 => $this->input->post('ativo') ? 1 : 0,
                'cadastrado_por'        => $userId
            );

            // Upload de Capa se fornecida
            $erro_imagem = '';
            if (!empty($_FILES['imagem_capa']['name'])) {
                $imagem = $this->enviar_imagem_treinamento('imagem_capa', 'capas', $erro_imagem);
                if ($imagem) {
                    $data['imagem_capa'] = $imagem;
                }
            } else {
                $data['imagem_capa'] = 'assets/images/treinamentos/curso_financeiro_1.svg';
            }

            $novoId = $erro_imagem === '' ? $this->treinamentos_model->create($data) : false;
            if ($novoId) {
                $this->session->set_flashdata('message', show_alert_success('Treinamento cadastrado com sucesso! Agora adicione as etapas.'));
                redirect('treinamentos_admin/etapas/' . $novoId);
            } else {
                $this->data['message'] = show_alert_error($erro_imagem !== '' ? 'A imagem de capa não foi enviada: ' . $erro_imagem : 'Erro ao cadastrar treinamento no banco de dados.');
            }
        }

        $this->data['produtos'] = $this->db->where('empresa_id', 1)
            ->where('status', 'S')
            ->order_by('nome', 'ASC')
            ->get('produtos')->result();

        $this->data['modulos'] = $this->db->select('m.*, m.id_produto as produto_id, p.nome as produto_nome')
            ->from('modulos m')
            ->join('produtos p', 'p.id = m.id_produto', 'inner')
            ->where('p.empresa_id', 1)
            ->where('p.status', 'S')
            ->where('m.status', 'S')
            ->order_by('m.nome', 'ASC')
            ->get()->result();

        $this->template->admin_render('admin/treinamentos/admin/form', $this->data);
    }

    /**
     * Edição de treinamento
     */
    public function edit($id)
    {
        $this->checar_permissao('edit');

        $treinamento = $this->treinamentos_model->get_treinamento($id);
        if (!$treinamento) {
            $this->session->set_flashdata('message', show_alert_error('Treinamento não encontrado.'));
            redirect('treinamentos_admin');
        }

        $this->breadcrumbs->unshift(3, lang('menu_treinamentos_edit'), 'treinamentos_admin/edit/' . $id);
        $this->data['breadcrumb']  = $this->breadcrumbs->show();
        $this->data['treinamento'] = $treinamento;

        $this->form_validation->set_rules('titulo', 'Título', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('produto_id', 'Produto', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('modulo_id', 'Módulo', 'required|is_natural_no_zero');

        if ($this->form_validation->run() == TRUE) {
            $produtoId = (int)$this->input->post('produto_id');
            $moduloId  = (int)$this->input->post('modulo_id');
            if (empty($produtoId) && !empty($moduloId)) {
                $modRow = $this->db->select('id_produto')->where('id', $moduloId)->get('modulos')->row();
                if ($modRow) {
                    $produtoId = (int)$modRow->id_produto;
                }
            }

            $data = array(
                'produto_id'            => $produtoId,
                'modulo_id'             => $moduloId,
                'titulo'                => $this->input->post('titulo', TRUE),
                'slug'                  => url_title(convert_accented_characters($this->input->post('titulo')), 'dash', TRUE),
                'descricao_curta'       => $this->input->post('descricao_curta', TRUE),
                'descricao_completa'    => $this->input->post('descricao_completa'),
                'objetivo'              => $this->input->post('objetivo', TRUE),
                'carga_horaria_minutos' => (int)$this->input->post('carga_horaria_minutos'),
                'nivel'                 => $this->input->post('nivel'),
                'destaque'              => $this->input->post('destaque') ? 1 : 0,
                'novidade'              => $this->input->post('novidade') ? 1 : 0,
                'avaliacao_obrigatoria' => $this->input->post('avaliacao_obrigatoria') ? 1 : 0,
                'tipo_acesso'           => $this->input->post('tipo_acesso') ? $this->input->post('tipo_acesso') : 'livre',
                'ordem'                 => (int)$this->input->post('ordem'),
                'ativo'                 => $this->input->post('ativo') ? 1 : 0
            );

            $erro_imagem = '';
            if (!empty($_FILES['imagem_capa']['name'])) {
                $imagem = $this->enviar_imagem_treinamento('imagem_capa', 'capas', $erro_imagem);
                if ($imagem) {
                    $data['imagem_capa'] = $imagem;
                }
            }

            if ($erro_imagem === '' && $this->treinamentos_model->update($id, $data)) {
                $this->session->set_flashdata('message', show_alert_success('Treinamento atualizado com sucesso!'));
                redirect('treinamentos_admin');
            } else {
                $this->data['message'] = show_alert_error($erro_imagem !== '' ? 'A imagem de capa não foi enviada: ' . $erro_imagem : 'Erro ao atualizar treinamento.');
            }
        }

        $this->data['produtos'] = $this->db->where('empresa_id', 1)
            ->where('status', 'S')
            ->order_by('nome', 'ASC')
            ->get('produtos')->result();

        $this->data['modulos'] = $this->db->select('m.*, m.id_produto as produto_id, p.nome as produto_nome')
            ->from('modulos m')
            ->join('produtos p', 'p.id = m.id_produto', 'inner')
            ->where('p.empresa_id', 1)
            ->where('p.status', 'S')
            ->where('m.status', 'S')
            ->order_by('m.nome', 'ASC')
            ->get()->result();

        $this->template->admin_render('admin/treinamentos/admin/form', $this->data);
    }

    /**
     * Excluir ou desativar treinamento
     */
    public function delete($id)
    {
        $this->checar_permissao('delete');

        $treinamento = $this->treinamentos_model->get_treinamento($id);
        if (!$treinamento) {
            $this->session->set_flashdata('message', show_alert_error('Treinamento não encontrado.'));
            redirect('treinamentos_admin');
        }

        // Se houver progresso historico, realizar desativacao logica
        $this->db->where('treinamento_id', (int)$id);
        $totalHistorico = $this->db->count_all_results('treinamento_progresso');

        if ($totalHistorico > 0) {
            $this->treinamentos_model->delete($id, true);
            $this->session->set_flashdata('message', show_alert_info('O treinamento possui histórico de alunos e foi desativado logicamente para preservar os dados.'));
        } else {
            $this->treinamentos_model->delete($id, false);
            $this->session->set_flashdata('message', show_alert_success('Treinamento excluído com sucesso.'));
        }

        redirect('treinamentos_admin');
    }

    // -------------------------------------------------------------
    // GESTÃO DE ETAPAS
    // -------------------------------------------------------------

    public function etapas($treinamento_id)
    {
        $this->checar_permissao('etapas');

        $treinamento = $this->treinamentos_model->get_treinamento($treinamento_id);
        if (!$treinamento) {
            $this->session->set_flashdata('message', show_alert_error('Treinamento não encontrado.'));
            redirect('treinamentos_admin');
        }

        $this->breadcrumbs->unshift(3, $treinamento->titulo, 'treinamentos_admin/edit/' . $treinamento_id);
        $this->breadcrumbs->unshift(4, lang('menu_treinamentos_etapas'), 'treinamentos_admin/etapas/' . $treinamento_id);
        $this->data['breadcrumb']  = $this->breadcrumbs->show();
        $this->data['message']     = $this->session->flashdata('message');
        $this->data['treinamento'] = $treinamento;
        $this->data['etapas']      = $this->treinamentos_etapas_model->get_etapas($treinamento_id, false);

        $this->template->admin_render('admin/treinamentos/admin/etapas', $this->data);
    }

    public function etapa_create($treinamento_id)
    {
        $this->checar_permissao('etapa_create');

        $treinamento = $this->treinamentos_model->get_treinamento($treinamento_id);
        if (!$treinamento) {
            $this->session->set_flashdata('message', show_alert_error('Treinamento não encontrado.'));
            redirect('treinamentos_admin');
        }

        $this->breadcrumbs->unshift(3, $treinamento->titulo, 'treinamentos_admin/etapas/' . $treinamento_id);
        $this->breadcrumbs->unshift(4, 'Nova Etapa', 'treinamentos_admin/etapa_create/' . $treinamento_id);
        $this->data['breadcrumb']  = $this->breadcrumbs->show();
        $this->data['treinamento'] = $treinamento;

        $this->form_validation->set_rules('titulo', 'Título da Etapa', 'trim|required|max_length[255]');

        if ($this->form_validation->run() == TRUE) {
            $ordemInformada = (int)$this->input->post('ordem');
            if ($ordemInformada <= 0) $ordemInformada = 1;

            // Se a ordem já existir em alguma etapa deste treinamento, reorganiza automaticamente as seguintes
            $ordemJaExiste = $this->db->where('treinamento_id', (int)$treinamento_id)
                ->where('ordem', $ordemInformada)
                ->count_all_results('treinamento_etapas');
            if ($ordemJaExiste > 0) {
                $this->db->set('ordem', 'ordem + 1', FALSE)
                    ->where('treinamento_id', (int)$treinamento_id)
                    ->where('ordem >=', $ordemInformada)
                    ->update('treinamento_etapas');
            }

            $videoTipo = $this->input->post('video_tipo');
            if ($videoTipo === 'arquivo') {
                $videoTipo = 'link_direto';
            }
            if (!in_array($videoTipo, array('youtube', 'vimeo', 'upload', 'link_direto', 'nenhum'))) {
                $videoTipo = 'nenhum';
            }

            $conteudo = $this->input->post('conteudo_detalhado');
            if ($conteudo === null || $conteudo === false) {
                $conteudo = $this->input->post('conteudo_html');
            }

            $data = array(
                'treinamento_id'         => (int)$treinamento_id,
                'titulo'                 => $this->input->post('titulo', TRUE),
                'descricao_curta'        => $this->input->post('descricao_curta', TRUE),
                'conteudo_detalhado'     => $conteudo,
                'conteudo_html'          => $conteudo,
                'video_tipo'             => $videoTipo,
                'video_url'              => $this->input->post('video_url', TRUE),
                'video_duracao_segundos' => (int)$this->input->post('video_duracao_segundos'),
                'video_obrigatorio'      => $this->input->post('video_obrigatorio') ? 1 : 0,
                'ordem'                  => $ordemInformada,
                'ativo'                  => $this->input->post('ativo') ? 1 : 0
            );

            $etapaId = $this->treinamentos_etapas_model->create($data);
            if ($etapaId) {
                // 1. Processar anexo opcional de material de apoio na criação
                if (!empty($_FILES['arquivo_apoio']['name'])) {
                    $tituloArq = $this->input->post('titulo_arquivo_apoio', TRUE);
                    if (empty($tituloArq)) {
                        $tituloArq = $_FILES['arquivo_apoio']['name'];
                    }
                    $uploadPath = './upload/treinamentos/materiais/';
                    if (!is_dir($uploadPath)) {
                        @mkdir($uploadPath, 0777, true);
                    }
                    $config['upload_path']   = $uploadPath;
                    $config['allowed_types'] = 'pdf|doc|docx|xls|xlsx|ppt|pptx|txt|zip|rar|jpg|jpeg|png';
                    $config['max_size']      = 20480;
                    $config['encrypt_name']  = TRUE;

                    $this->upload->initialize($config);
                    if ($this->upload->do_upload('arquivo_apoio')) {
                        $dataUpload = $this->upload->data();
                        $dataArq = array(
                            'treinamento_id' => (int)$treinamento_id,
                            'etapa_id'       => (int)$etapaId,
                            'titulo'         => $tituloArq,
                            'nome_original'  => $dataUpload['client_name'],
                            'nome_arquivo'   => $dataUpload['file_name'],
                            'extensao'       => strtolower(str_replace('.', '', $dataUpload['file_ext'])),
                            'tamanho_bytes'  => (int)$dataUpload['file_size'] * 1024,
                            'caminho'        => 'upload/treinamentos/materiais/' . $dataUpload['file_name']
                        );
                        $this->treinamentos_etapas_model->add_arquivo($dataArq);
                    }
                }

                // 2. Processar atividade prática orientativa opcional na criação
                $ativTitulo = $this->input->post('atividade_titulo', TRUE);
                if (!empty($ativTitulo)) {
                    $ativDesc = $this->input->post('atividade_descricao', TRUE);
                    $dataAtiv = array(
                        'treinamento_id' => (int)$treinamento_id,
                        'etapa_id'       => (int)$etapaId,
                        'titulo'         => $ativTitulo,
                        'descricao'      => $ativDesc,
                        'instrucoes'     => $ativDesc,
                        'obrigatoria'    => 0,
                        'ordem'          => 1
                    );
                    $this->treinamentos_etapas_model->add_atividade($dataAtiv);
                }

                $this->session->set_flashdata('message', show_alert_success('Etapa cadastrada com sucesso!'));
                redirect('treinamentos_admin/etapas/' . $treinamento_id);
            } else {
                $dbError = $this->db->error();
                $errorMsg = !empty($dbError['message']) ? ': ' . $dbError['message'] : '.';
                $this->data['message'] = show_alert_error('Erro ao cadastrar etapa' . $errorMsg);
            }
        }

        // Buscar etapas existentes para pré-carregar próxima ordem e alertar ordens em uso
        $etapasExistentes = $this->db->select('id, ordem, titulo')
            ->where('treinamento_id', (int)$treinamento_id)
            ->where('ativo', 1)
            ->order_by('ordem', 'ASC')
            ->get('treinamento_etapas')->result_array();

        $maxOrdem = 0;
        $ordensUtilizadas = array();
        foreach ($etapasExistentes as $ee) {
            $ordNum = (int)$ee['ordem'];
            $ordensUtilizadas[] = $ordNum;
            if ($ordNum > $maxOrdem) {
                $maxOrdem = $ordNum;
            }
        }
        $proximaOrdem = $maxOrdem + 1;

        $this->data['proxima_ordem']     = $proximaOrdem;
        $this->data['ordens_utilizadas'] = $ordensUtilizadas;
        $this->data['etapas_existentes'] = $etapasExistentes;

        $this->template->admin_render('admin/treinamentos/admin/etapa_form', $this->data);
    }

    public function etapa_edit($etapa_id)
    {
        $this->checar_permissao('etapa_edit');

        $etapa = $this->treinamentos_etapas_model->get_etapa($etapa_id);
        if (!$etapa) {
            $this->session->set_flashdata('message', show_alert_error('Etapa não encontrada.'));
            redirect('treinamentos_admin');
        }

        $treinamento = $this->treinamentos_model->get_treinamento($etapa->treinamento_id);
        $this->breadcrumbs->unshift(3, $treinamento->titulo, 'treinamentos_admin/etapas/' . $treinamento->id);
        $this->breadcrumbs->unshift(4, 'Editar Etapa', 'treinamentos_admin/etapa_edit/' . $etapa_id);
        $this->data['breadcrumb']  = $this->breadcrumbs->show();
        $this->data['message']     = $this->session->flashdata('message');
        $this->data['treinamento'] = $treinamento;
        $this->data['etapa']       = $etapa;
        $this->data['arquivos']    = $this->treinamentos_etapas_model->get_arquivos($etapa_id);
        $this->data['atividades']  = $this->treinamentos_etapas_model->get_atividades($etapa_id, false);

        $this->form_validation->set_rules('titulo', 'Título da Etapa', 'trim|required|max_length[255]');

        if ($this->form_validation->run() == TRUE) {
            $ordemInformada = (int)$this->input->post('ordem');
            if ($ordemInformada <= 0) $ordemInformada = 1;

            // Se a ordem foi alterada e a nova ordem já existe em outra etapa deste treinamento
            if ($ordemInformada != (int)$etapa->ordem) {
                $ordemJaExiste = $this->db->where('treinamento_id', (int)$etapa->treinamento_id)
                    ->where('ordem', $ordemInformada)
                    ->where('id !=', (int)$etapa_id)
                    ->count_all_results('treinamento_etapas');
                if ($ordemJaExiste > 0) {
                    $this->db->set('ordem', 'ordem + 1', FALSE)
                        ->where('treinamento_id', (int)$etapa->treinamento_id)
                        ->where('ordem >=', $ordemInformada)
                        ->where('id !=', (int)$etapa_id)
                        ->update('treinamento_etapas');
                }
            }

            $videoTipo = $this->input->post('video_tipo');
            if ($videoTipo === 'arquivo') {
                $videoTipo = 'link_direto';
            }
            if (!in_array($videoTipo, array('youtube', 'vimeo', 'upload', 'link_direto', 'nenhum'))) {
                $videoTipo = 'nenhum';
            }

            $conteudo = $this->input->post('conteudo_detalhado');
            if ($conteudo === null || $conteudo === false) {
                $conteudo = $this->input->post('conteudo_html');
            }

            $data = array(
                'titulo'                 => $this->input->post('titulo', TRUE),
                'descricao_curta'        => $this->input->post('descricao_curta', TRUE),
                'conteudo_detalhado'     => $conteudo,
                'conteudo_html'          => $conteudo,
                'video_tipo'             => $videoTipo,
                'video_url'              => $this->input->post('video_url', TRUE),
                'video_duracao_segundos' => (int)$this->input->post('video_duracao_segundos'),
                'video_obrigatorio'      => $this->input->post('video_obrigatorio') ? 1 : 0,
                'ordem'                  => $ordemInformada,
                'ativo'                  => $this->input->post('ativo') ? 1 : 0
            );

            if ($this->treinamentos_etapas_model->update($etapa_id, $data)) {
                $this->session->set_flashdata('message', show_alert_success('Etapa atualizada com sucesso!'));
                redirect('treinamentos_admin/etapas/' . $treinamento->id);
            } else {
                $dbError = $this->db->error();
                $errorMsg = !empty($dbError['message']) ? ': ' . $dbError['message'] : '.';
                $this->data['message'] = show_alert_error('Erro ao atualizar etapa' . $errorMsg);
            }
        }

        $this->data['etapas_existentes'] = $this->db->select('id, ordem, titulo')
            ->where('treinamento_id', (int)$treinamento->id)
            ->where('ativo', 1)
            ->order_by('ordem', 'ASC')
            ->get('treinamento_etapas')->result_array();

        $this->template->admin_render('admin/treinamentos/admin/etapa_form', $this->data);
    }

    public function etapa_delete($etapa_id)
    {
        $this->checar_permissao('etapa_delete');

        $etapa = $this->treinamentos_etapas_model->get_etapa($etapa_id);
        if ($etapa) {
            $treinamentoId = $etapa->treinamento_id;
            $this->treinamentos_etapas_model->delete($etapa_id);
            $this->session->set_flashdata('message', show_alert_success('Etapa removida com sucesso.'));
            redirect('treinamentos_admin/etapas/' . $treinamentoId);
        }

        redirect('treinamentos_admin');
    }

    // -------------------------------------------------------------
    // GESTÃO DE ARQUIVOS E MATERIAIS DE APOIO
    // -------------------------------------------------------------

    public function arquivo_upload($etapa_id)
    {
        $this->checar_permissao('arquivo_upload');

        $etapa = $this->treinamentos_etapas_model->get_etapa($etapa_id);
        if (!$etapa) {
            echo json_encode(array('success' => false, 'message' => 'Etapa não encontrada.'));
            return;
        }

        $titulo = $this->input->post('titulo', TRUE);
        if (empty($titulo)) {
            $titulo = isset($_FILES['arquivo']['name']) ? $_FILES['arquivo']['name'] : 'Material de Apoio';
        }

        $uploadPath = './upload/treinamentos/materiais/';
        if (!is_dir($uploadPath)) {
            @mkdir($uploadPath, 0755, true);
        }
        $config['upload_path']   = $uploadPath;
        $config['allowed_types'] = 'pdf|doc|docx|xls|xlsx|ppt|pptx|txt|zip|rar|jpg|jpeg|png';
        $config['max_size']      = 20480; // 20MB
        $config['encrypt_name']  = TRUE;

        $this->upload->initialize($config);
        if ($this->upload->do_upload('arquivo')) {
            $dataUpload = $this->upload->data();
            $data = array(
                'treinamento_id' => $etapa->treinamento_id,
                'etapa_id'       => (int)$etapa_id,
                'titulo'         => $titulo,
                'nome_original'  => $dataUpload['client_name'],
                'nome_arquivo'   => $dataUpload['file_name'],
                'extensao'       => strtolower(str_replace('.', '', $dataUpload['file_ext'])),
                'tamanho_bytes'  => (int)$dataUpload['file_size'] * 1024,
                'caminho'        => 'upload/treinamentos/materiais/' . $dataUpload['file_name']
            );
            $this->treinamentos_etapas_model->add_arquivo($data);

            $this->session->set_flashdata('message', show_alert_success('Arquivo anexado com sucesso!'));
        } else {
            $this->session->set_flashdata('message', show_alert_error($this->upload->display_errors()));
        }

        redirect('treinamentos_admin/etapa_edit/' . $etapa_id);
    }

    public function arquivo_delete($arquivo_id)
    {
        $this->checar_permissao('arquivo_delete');

        $arquivo = $this->treinamentos_etapas_model->get_arquivo($arquivo_id);
        if ($arquivo) {
            $etapaId = $arquivo->etapa_id;
            $this->treinamentos_etapas_model->delete_arquivo($arquivo_id);
            $this->session->set_flashdata('message', show_alert_success('Arquivo removido com sucesso.'));
            redirect('treinamentos_admin/etapa_edit/' . $etapaId);
        }

        redirect('treinamentos_admin');
    }

    // -------------------------------------------------------------
    // GESTÃO DE ATIVIDADES ORIENTATIVAS
    // -------------------------------------------------------------

    public function atividade_salvar()
    {
        $this->checar_permissao('atividades');

        $etapaId = (int)$this->input->post('etapa_id');
        $etapa   = $this->treinamentos_etapas_model->get_etapa($etapaId);
        if (!$etapa) {
            echo json_encode(array('success' => false, 'message' => 'Etapa não encontrada.'));
            return;
        }

        $id = $this->input->post('atividade_id');
        $data = array(
            'treinamento_id' => $etapa->treinamento_id,
            'etapa_id'       => $etapaId,
            'titulo'         => $this->input->post('titulo', TRUE),
            'descricao'      => $this->input->post('descricao', TRUE),
            'instrucoes'     => $this->input->post('instrucoes'),
            'obrigatoria'    => $this->input->post('obrigatoria') ? 1 : 0,
            'ordem'          => (int)$this->input->post('ordem', TRUE)
        );

        if (!empty($id)) {
            $this->treinamentos_etapas_model->update_atividade($id, $data);
            $this->session->set_flashdata('message', show_alert_success('Atividade prática atualizada!'));
        } else {
            $this->treinamentos_etapas_model->add_atividade($data);
            $this->session->set_flashdata('message', show_alert_success('Atividade prática cadastrada!'));
        }

        redirect('treinamentos_admin/etapa_edit/' . $etapaId);
    }

    public function atividade_delete($atividade_id)
    {
        $this->checar_permissao('atividades');

        $atividade = $this->treinamentos_etapas_model->get_atividade($atividade_id);
        if ($atividade) {
            $etapaId = $atividade->etapa_id;
            $this->treinamentos_etapas_model->delete_atividade($atividade_id);
            $this->session->set_flashdata('message', show_alert_success('Atividade removida com sucesso.'));
            redirect('treinamentos_admin/etapa_edit/' . $etapaId);
        }

        redirect('treinamentos_admin');
    }

    // -------------------------------------------------------------
    // GESTÃO DE LIBERAÇÃO DE ACESSO
    // -------------------------------------------------------------

    public function permissoes($treinamento_id)
    {
        $this->checar_permissao('permissoes');

        $treinamento = $this->treinamentos_model->get_treinamento($treinamento_id);
        if (!$treinamento) {
            $this->session->set_flashdata('message', show_alert_error('Treinamento não encontrado.'));
            redirect('treinamentos_admin');
        }

        $this->breadcrumbs->unshift(3, $treinamento->titulo, 'treinamentos_admin/edit/' . $treinamento_id);
        $this->breadcrumbs->unshift(4, 'Liberação de Acesso', 'treinamentos_admin/permissoes/' . $treinamento_id);
        $this->data['breadcrumb']  = $this->breadcrumbs->show();
        $this->data['message']     = $this->session->flashdata('message');
        $this->data['treinamento'] = $treinamento;

        if ($this->input->post()) {
            $tipoAcesso = $this->input->post('tipo_acesso');
            $this->treinamentos_model->update($treinamento_id, array('tipo_acesso' => $tipoAcesso));

            $userIds  = $this->input->post('usuarios');
            $groupIds = $this->input->post('grupos');

            $this->treinamentos_model->salvar_permissoes($treinamento_id, (array)$userIds, (array)$groupIds);

            $this->session->set_flashdata('message', show_alert_success('Permissões de acesso atualizadas com sucesso!'));
            redirect('treinamentos_admin/permissoes/' . $treinamento_id);
        }

        $this->data['usuarios'] = $this->db->where('active', 1)->order_by('first_name ASC')->get('users')->result();
        $this->data['grupos']   = $this->db->order_by('name ASC')->get('groups')->result();

        $permissoesAtuais = $this->treinamentos_model->get_permissoes_treinamento($treinamento_id);
        $userIdsAtuais  = array();
        $groupIdsAtuais = array();
        foreach ($permissoesAtuais as $p) {
            if ($p->user_id)  $userIdsAtuais[]  = (int)$p->user_id;
            if ($p->group_id) $groupIdsAtuais[] = (int)$p->group_id;
        }
        $this->data['usuarios_selecionados'] = $userIdsAtuais;
        $this->data['grupos_selecionados']   = $groupIdsAtuais;
        $this->data['userIdsAtuais']         = $userIdsAtuais;
        $this->data['groupIdsAtuais']        = $groupIdsAtuais;

        $this->template->admin_render('admin/treinamentos/admin/permissoes', $this->data);
    }

    // -------------------------------------------------------------
    // GESTÃO DE BANNERS DO CARROSSEL
    // -------------------------------------------------------------

    public function banners()
    {
        $this->checar_permissao('banners');

        $empresaId = (int)$this->session->userdata('empresa_id');
        $this->breadcrumbs->unshift(3, lang('menu_treinamentos_banners'), 'treinamentos_admin/banners');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message']    = $this->session->flashdata('message');

        $this->data['banners'] = $this->treinamentos_banners_model->get_all_banners($empresaId);
        $this->template->admin_render('admin/treinamentos/admin/banners', $this->data);
    }

    public function banner_create()
    {
        $this->checar_permissao('banner_create');

        $empresaId = (int)$this->session->userdata('empresa_id');
        $userId    = (int)$this->session->userdata('user_id');

        $this->breadcrumbs->unshift(3, lang('menu_treinamentos_banners'), 'treinamentos_admin/banners');
        $this->breadcrumbs->unshift(4, 'Novo Banner', 'treinamentos_admin/banner_create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $this->form_validation->set_rules('titulo', 'Título do Banner', 'trim|required|max_length[255]');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'empresa_id'     => $empresaId,
                'titulo'         => $this->input->post('titulo', TRUE),
                'subtitulo'      => $this->input->post('subtitulo', TRUE),
                'descricao'      => $this->input->post('descricao', TRUE),
                'texto_botao'    => $this->input->post('texto_botao', TRUE),
                'url_botao'      => $this->input->post('url_botao', TRUE),
                'treinamento_id' => !empty($this->input->post('treinamento_id')) ? (int)$this->input->post('treinamento_id') : null,
                'modulo_id'      => !empty($this->input->post('modulo_id')) ? (int)$this->input->post('modulo_id') : null,
                'ordem'          => (int)$this->input->post('ordem'),
                'ativo'          => $this->input->post('ativo') ? 1 : 0,
                'cadastrado_por' => $userId
            );

            $erro_imagem = '';
            if (!empty($_FILES['imagem']['name'])) {
                $imagem = $this->enviar_imagem_treinamento('imagem', 'banners', $erro_imagem);
                if ($imagem) {
                    $data['imagem'] = $imagem;
                }
            } else {
                $data['imagem'] = 'assets/images/treinamentos/banner_academy_1.svg';
            }

            if ($erro_imagem === '' && $this->treinamentos_banners_model->create($data)) {
                $this->session->set_flashdata('message', show_alert_success('Banner cadastrado com sucesso!'));
                redirect('treinamentos_admin/banners');
            } else {
                $this->data['message'] = show_alert_error($erro_imagem !== '' ? 'A imagem do banner não foi enviada: ' . $erro_imagem : 'Erro ao cadastrar banner.');
            }
        }

        $this->data['treinamentos'] = $this->treinamentos_model->get_treinamentos(array('ativo' => 1));
        $this->data['modulos']      = $this->db->where('status', 'S')->get('modulos')->result();
        $this->template->admin_render('admin/treinamentos/admin/banner_form', $this->data);
    }

    public function banner_edit($id)
    {
        $this->checar_permissao('banner_edit');

        $banner = $this->treinamentos_banners_model->get_banner($id);
        if (!$banner) {
            $this->session->set_flashdata('message', show_alert_error('Banner não encontrado.'));
            redirect('treinamentos_admin/banners');
        }

        $this->breadcrumbs->unshift(3, lang('menu_treinamentos_banners'), 'treinamentos_admin/banners');
        $this->breadcrumbs->unshift(4, 'Editar Banner', 'treinamentos_admin/banner_edit/' . $id);
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['banner']     = $banner;

        $this->form_validation->set_rules('titulo', 'Título do Banner', 'trim|required|max_length[255]');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'titulo'         => $this->input->post('titulo', TRUE),
                'subtitulo'      => $this->input->post('subtitulo', TRUE),
                'descricao'      => $this->input->post('descricao', TRUE),
                'texto_botao'    => $this->input->post('texto_botao', TRUE),
                'url_botao'      => $this->input->post('url_botao', TRUE),
                'treinamento_id' => !empty($this->input->post('treinamento_id')) ? (int)$this->input->post('treinamento_id') : null,
                'modulo_id'      => !empty($this->input->post('modulo_id')) ? (int)$this->input->post('modulo_id') : null,
                'ordem'          => (int)$this->input->post('ordem'),
                'ativo'          => $this->input->post('ativo') ? 1 : 0
            );

            $erro_imagem = '';
            if (!empty($_FILES['imagem']['name'])) {
                $imagem = $this->enviar_imagem_treinamento('imagem', 'banners', $erro_imagem);
                if ($imagem) {
                    $data['imagem'] = $imagem;
                }
            }

            if ($erro_imagem === '' && $this->treinamentos_banners_model->update($id, $data)) {
                $this->session->set_flashdata('message', show_alert_success('Banner atualizado com sucesso!'));
                redirect('treinamentos_admin/banners');
            } else {
                $this->data['message'] = show_alert_error($erro_imagem !== '' ? 'A imagem do banner não foi enviada: ' . $erro_imagem : 'Erro ao atualizar banner.');
            }
        }

        $this->data['treinamentos'] = $this->treinamentos_model->get_treinamentos(array('ativo' => 1));
        $this->data['modulos']      = $this->db->where('status', 'S')->get('modulos')->result();
        $this->template->admin_render('admin/treinamentos/admin/banner_form', $this->data);
    }

    public function banner_delete($id)
    {
        $this->checar_permissao('banner_delete');

        $this->treinamentos_banners_model->delete($id);
        $this->session->set_flashdata('message', show_alert_success('Banner removido com sucesso.'));
        redirect('treinamentos_admin/banners');
    }

    // -------------------------------------------------------------
    // PAINEL DE ACOMPANHAMENTO E AVALIAÇÕES
    // -------------------------------------------------------------

    public function relatorios()
    {
        $this->checar_permissao('relatorios');

        $this->breadcrumbs->unshift(3, lang('menu_treinamentos_relatorios'), 'treinamentos_admin/relatorios');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message']    = $this->session->flashdata('message');

        // Filtros recebidos
        $moduloId      = $this->input->get('modulo_id');
        $treinamentoId = $this->input->get('treinamento_id');
        $statusProg    = $this->input->get('status');
        $notaAval      = $this->input->get('nota');

        // Metricas Consolidadas
        $this->data['total_treinamentos'] = $this->db->where('ativo', 1)->count_all_results('treinamentos');
        $this->data['total_conclusoes']   = $this->db->where('status', 'concluido')->count_all_results('treinamento_progresso');
        $this->data['total_em_andamento'] = $this->db->where('status', 'em_andamento')->count_all_results('treinamento_progresso');
        $this->data['resumo_avaliacoes']  = $this->treinamentos_avaliacoes_model->get_resumo_geral();

        // 1. Tabela de Progresso dos Alunos
        $temEtapaAtual = $this->db->field_exists('etapa_atual_id', 'treinamento_progresso');
        $temUltimaEtapa = $this->db->field_exists('ultima_etapa_id', 'treinamento_progresso');
        if ($temEtapaAtual && $temUltimaEtapa) {
            $joinOn = 'te.id = COALESCE(tp.ultima_etapa_id, tp.etapa_atual_id)';
        } elseif ($temEtapaAtual) {
            $joinOn = 'te.id = tp.etapa_atual_id';
        } else {
            $joinOn = 'te.id = tp.ultima_etapa_id';
        }

        $this->db->select('tp.*, t.titulo as treinamento_titulo, m.nome as modulo_nome, u.first_name, u.last_name, u.email, te.titulo as ultima_etapa_titulo, te.ordem as ultima_etapa_ordem,
            (SELECT COUNT(te_sub.id) FROM treinamento_etapas te_sub WHERE te_sub.treinamento_id = tp.treinamento_id AND te_sub.ativo = 1) as total_etapas_calc,
            (SELECT COUNT(tep_sub.id) FROM treinamento_etapa_progresso tep_sub WHERE tep_sub.treinamento_id = tp.treinamento_id AND tep_sub.user_id = tp.user_id AND tep_sub.status = "concluido") as etapas_concluidas_calc');
        $this->db->from('treinamento_progresso tp');
        $this->db->join('treinamentos t', 't.id = tp.treinamento_id', 'inner');
        $this->db->join('modulos m', 'm.id = t.modulo_id', 'left');
        $this->db->join('users u', 'u.id = tp.user_id', 'left');
        $this->db->join('treinamento_etapas te', $joinOn, 'left');

        if (!empty($moduloId))      $this->db->where('t.modulo_id', (int)$moduloId);
        if (!empty($treinamentoId)) $this->db->where('tp.treinamento_id', (int)$treinamentoId);
        if (!empty($statusProg))    $this->db->where('tp.status', $statusProg);

        $this->db->order_by('tp.ultima_atividade DESC');
        $this->data['progresso_alunos'] = $this->db->get()->result();

        // 2. Tabela de Avaliações Recebidas
        $paramsAval = array();
        if (!empty($moduloId))      $paramsAval['modulo_id'] = $moduloId;
        if (!empty($treinamentoId)) $paramsAval['treinamento_id'] = $treinamentoId;
        if (!empty($notaAval))      $paramsAval['nota'] = $notaAval;

        $this->data['avaliacoes'] = $this->treinamentos_avaliacoes_model->get_relatorio_avaliacoes($paramsAval);

        // Dados para selects de filtros
        $this->data['modulos']      = $this->db->where('status', 'S')->get('modulos')->result();
        $this->data['treinamentos'] = $this->treinamentos_model->get_treinamentos(array('ativo' => 1));
        $this->data['filtro_modulo']      = $moduloId;
        $this->data['filtro_treinamento'] = $treinamentoId;
        $this->data['filtro_status']      = $statusProg;
        $this->data['filtro_nota']        = $notaAval;

        $this->template->admin_render('admin/treinamentos/admin/relatorios', $this->data);
    }

    /**
     * Endpoint AJAX para obter duracao automatica de video (YouTube / Vimeo / Direto)
     */
    public function obter_duracao_video()
    {
        $url  = $this->input->post('url', TRUE);
        $tipo = $this->input->post('tipo', TRUE);

        if (empty($url)) {
            echo json_encode(array('success' => false, 'message' => 'URL vazia'));
            return;
        }

        $duracao = 0;

        // 1. YouTube
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $m)) {
            $videoId = $m[1];
            $ctx = stream_context_create(array('http' => array('timeout' => 5, 'header' => "User-Agent: Mozilla/5.0\r\n")));
            $html = @file_get_contents("https://www.youtube.com/watch?v=" . $videoId, false, $ctx);
            if ($html && preg_match('/itemprop="duration" content="PT([^"]+)"/i', $html, $matches)) {
                $iso = $matches[1];
                if (preg_match('/(\d+)H/', $iso, $h))   $duracao += (int)$h[1] * 3600;
                if (preg_match('/(\d+)M/', $iso, $min)) $duracao += (int)$min[1] * 60;
                if (preg_match('/(\d+)S/', $iso, $s))   $duracao += (int)$s[1];
            }
        }
        // 2. Vimeo
        elseif (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)/i', $url, $m)) {
            $ctx = stream_context_create(array('http' => array('timeout' => 5)));
            $json = @file_get_contents('https://vimeo.com/api/oembed.json?url=' . urlencode($url), false, $ctx);
            if ($json) {
                $data = json_decode($json, true);
                if (isset($data['duration'])) {
                    $duracao = (int)$data['duration'];
                }
            }
        }

        if ($duracao > 0) {
            $minutos = floor($duracao / 60);
            $segs = $duracao % 60;
            $formatado = sprintf('%02d:%02d', $minutos, $segs);
            echo json_encode(array(
                'success'   => true,
                'duracao'   => $duracao,
                'formatado' => $formatado,
                'minutos'   => $minutos
            ));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Não foi possível detectar a duração automaticamente'));
        }
    }

    /**
     * Envia a imagem do campo $campo para upload/treinamentos/$pasta/, criando a pasta se ela não existir.
     * Retorna o caminho relativo gravado no banco, ou FALSE com o motivo da falha em $erro.
     */
    private function enviar_imagem_treinamento($campo, $pasta, &$erro)
    {
        $relativo = 'upload/treinamentos/' . $pasta . '/';
        $destino = FCPATH . $relativo;
        if (!is_dir($destino) && !@mkdir($destino, 0755, true)) {
            $erro = 'não foi possível criar a pasta ' . $relativo . ' no servidor. Verifique a permissão de escrita da pasta upload.';
            return FALSE;
        }
        if (!is_writable($destino)) {
            $erro = 'a pasta ' . $relativo . ' não tem permissão de escrita no servidor.';
            return FALSE;
        }
        if (!is_file($destino . 'index.html')) {
            @file_put_contents($destino . 'index.html', '<html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>');
        }

        $this->upload->initialize(array(
            'upload_path'   => $destino,
            'allowed_types' => 'gif|jpg|jpeg|png|svg|webp',
            'max_size'      => 5120,
            'encrypt_name'  => TRUE,
        ));
        if (!$this->upload->do_upload($campo)) {
            $erro = trim(strip_tags($this->upload->display_errors('', ' ')));
            return FALSE;
        }
        return $relativo . $this->upload->data('file_name');
    }
}