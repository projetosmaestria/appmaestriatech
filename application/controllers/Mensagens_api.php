<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ações AJAX da tela de mensagens. Estende MY_Controller (e não Admin_Controller) porque a conversa aberta
 * é atualizada a cada poucos segundos e não precisa montar menu e cabeçalho.
 * Tudo vale só para o usuário da sessão, na empresa da sessão, e com quem também tem acesso às mensagens.
 */
class Mensagens_api extends MY_Controller {

    const TAMANHO_ANEXO_MB = 20;
    // Acima disso o anexo é comprimido antes de ir para a conversa (só fica a versão comprimida se ela for 10% menor).
    const LIMITE_COMPRESSAO_MB = 5;

    // Extensões aceitas e como cada uma aparece na conversa.
    private $tipos = array(
        'imagem' => array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'),
        'video' => array('mp4', 'webm', 'mov'),
        'audio' => array('mp3', 'ogg', 'opus', 'wav', 'm4a', 'weba'),
        'arquivo' => array('pdf', 'txt', 'csv', 'rtf', 'doc', 'docx', 'odt', 'xls', 'xlsx', 'xlsm', 'ods', 'ppt', 'pptx', 'pps', 'ppsx', 'odp', 'zip', 'rar', 'xml', 'json', 'avi'),
    );

    // Formatos que já são comprimidos: colocar em .zip não diminui, então nem tenta.
    private $ja_comprimidos = array('zip', 'rar', '7z', 'docx', 'xlsx', 'xlsm', 'pptx', 'ppsx', 'odt', 'ods', 'odp', 'mp4', 'webm', 'mov', 'mp3', 'm4a', 'ogg', 'opus', 'weba', 'jpg', 'jpeg', 'png', 'gif', 'webp');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('mensagens_model');
        $this->load->model('mensagens_recursos_model');
        $this->output->set_content_type('application/json', 'utf-8');
    }

    // Abre uma conversa ("u5" pessoa, "g3" grupo ou "c2" canal): mensagens recentes, marcando como lidas as recebidas.
    public function conversa($chave = null)
    {
        $alvo = $this->exigir_conversa($chave);
        if (!$alvo) {
            return;
        }
        $this->mensagens_model->marcar_entregues($this->usuario(), $this->empresa());
        // Ao abrir explicitamente a conversa, limpa a marcação manual de "não lida".
        $this->mensagens_recursos_model->marcar_conversa_nao_lida($this->empresa(), $this->usuario(), (string) $chave, false);
        $this->responder(array_merge(array('ok' => true), $this->abrir_conversa($alvo, 0, true)));
    }

    // Carrega mensagens anteriores (paginação para cima ou salto até uma citação/busca).
    public function anteriores($chave = null)
    {
        $alvo = $this->exigir_conversa($chave);
        if (!$alvo) {
            return;
        }
        $antes = max(0, (int) $this->input->get('antes'));
        $ate = max(0, (int) $this->input->get('ate'));
        $mensagens = $this->mensagens_model->conversa_anteriores($alvo, $this->usuario(), $this->empresa(), $antes, $ate);
        $this->responder(array('ok' => true, 'mensagens' => $mensagens));
    }

    // Atualização periódica: mensagens novas da conversa aberta (se houver), status, digitação e lista de conversas.
    public function atualizar()
    {
        if (!$this->exigir_acesso()) {
            return;
        }
        $this->mensagens_recursos_model->processar_agendadas();
        $this->mensagens_recursos_model->heartbeat_status($this->empresa(), $this->usuario());
        $this->mensagens_model->marcar_entregues($this->usuario(), $this->empresa());
        $resposta = array('ok' => true, 'novas' => array(), 'visto_ate' => 0, 'entregue_ate' => 0);
        $chave = (string) $this->input->get('com');
        if ($chave !== '') {
            $alvo = $this->localizar_conversa($chave);
            if ($alvo) {
                $depois = max(0, (int) $this->input->get('depois'));
                $aberta = $this->abrir_conversa($alvo, $depois, false);
                $resposta['novas'] = $aberta['mensagens'];
                $resposta['visto_ate'] = $aberta['visto_ate'];
                $resposta['entregue_ate'] = $aberta['entregue_ate'];
                $resposta['conversa'] = $aberta['conversa'];
                $resposta['fixadas'] = $aberta['fixadas'];
                $resposta['digitando'] = $aberta['digitando'];
                $resposta['chamada_ativa'] = $aberta['chamada_ativa'];
                $resposta['sincronizadas'] = $this->mensagens_model->mensagens_sincronizadas($chave, $this->usuario(), $this->empresa(), $depois);
                $resposta['reacoes'] = $this->mensagens_recursos_model->reacoes_conversa($chave, $this->usuario(), $this->empresa(), $depois);
                if ($alvo['tipo'] === 'grupo') {
                    $resposta['membros'] = $aberta['membros'];
                    $resposta['status_grupo'] = $this->mensagens_model->status_recentes_grupo($alvo['grupo']->id, $this->usuario(), $depois);
                } elseif ($alvo['tipo'] === 'canal') {
                    $resposta['membros'] = $aberta['membros'];
                    $resposta['enquetes'] = $this->mensagens_recursos_model->enquetes_canal($alvo['canal']->id, $this->usuario());
                    $resposta['reunioes'] = $this->mensagens_recursos_model->reunioes_canal($alvo['canal']->id, $this->usuario());
                }
            } else {
                // Foi removido do grupo, o grupo/canal foi excluído ou a pessoa perdeu o acesso.
                $resposta['removido'] = true;
            }
        }
        $contatos = $this->mensagens_model->contatos($this->usuario(), $this->empresa());
        $resposta['conversas'] = $this->mensagens_model->conversas($this->usuario(), $this->empresa(), $contatos);
        $resposta['pessoas'] = $contatos;
        $resposta['digitando_lista'] = $this->mensagens_model->digitando_nas_conversas($this->usuario(), $this->empresa());
        $resposta['agendadas'] = $this->mensagens_recursos_model->listar_agendadas($this->empresa(), $this->usuario());
        $resposta['lembretes'] = $this->mensagens_recursos_model->listar_lembretes($this->empresa(), $this->usuario());
        $resposta['etiquetas'] = $this->mensagens_recursos_model->listar_etiquetas($this->empresa(), $this->usuario());
        $resposta['meu_status'] = $this->mensagens_recursos_model->status_usuario($this->empresa(), $this->usuario());
        $resposta['chamadas_recebidas'] = $this->mensagens_recursos_model->chamadas_recebidas($this->empresa(), $this->usuario());
        if ($this->input->get('reacao_depois') !== null) {
            $resposta['reacoes_novas'] = $this->mensagens_recursos_model->reacoes_para_notificar($this->empresa(), $this->usuario(), max(0, (int) $this->input->get('reacao_depois')));
        }
        $this->responder($resposta);
    }

    /* ---------- Grupos ---------- */

    public function grupo_criar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $resultado = $this->mensagens_model->criar_grupo($this->empresa(), $this->usuario(), $this->input->post('nome'), (array) $this->input->post('membros'));
        if (is_string($resultado)) {
            $this->falhar(422, $resultado);
            return;
        }
        $grupo = $this->mensagens_model->grupo_do_membro($resultado, $this->usuario(), $this->empresa());
        $this->responder(array_merge(array('ok' => true), $this->abrir_conversa(array('tipo' => 'grupo', 'grupo' => $grupo), 0)));
    }

    // Participantes do grupo (detalhes).
    public function grupo($grupo_id = null)
    {
        $grupo = $this->exigir_grupo($grupo_id);
        if (!$grupo) {
            return;
        }
        $this->responder(array(
            'ok' => true,
            'grupo' => $this->mensagens_model->grupo_para_conversa($grupo, $this->usuario()),
            'membros' => $this->mensagens_model->membros($grupo->id),
        ));
    }

    public function grupo_adicionar()
    {
        $grupo = $this->exigir_post() ? $this->exigir_grupo($this->input->post('grupo'), true) : null;
        if (!$grupo) {
            return;
        }
        $resultado = $this->mensagens_model->adicionar_membros($grupo, $this->empresa(), $this->usuario(), (array) $this->input->post('membros'));
        $this->responder_acao($resultado);
    }

    public function grupo_remover()
    {
        $grupo = $this->exigir_post() ? $this->exigir_grupo($this->input->post('grupo'), true) : null;
        if (!$grupo) {
            return;
        }
        $user_id = (int) $this->input->post('usuario');
        if ($user_id === $this->usuario()) {
            $this->falhar(422, 'Para sair do grupo, use "Sair do grupo".');
            return;
        }
        $this->responder_acao($this->mensagens_model->remover_membro($grupo, $this->empresa(), $this->usuario(), $user_id));
    }

    public function grupo_sair()
    {
        $grupo = $this->exigir_post() ? $this->exigir_grupo($this->input->post('grupo')) : null;
        if (!$grupo) {
            return;
        }
        $this->responder_acao($this->mensagens_model->sair_do_grupo($grupo, $this->empresa(), $this->usuario()));
    }

    public function grupo_excluir()
    {
        $grupo = $this->exigir_post() ? $this->exigir_grupo($this->input->post('grupo'), true) : null;
        if (!$grupo) {
            return;
        }
        $this->responder_acao($this->mensagens_model->excluir_grupo($grupo));
    }

    /* ---------- Conversa: localizar e abrir ---------- */

    // Resolve a chave em pessoa, grupo ou canal que o usuário pode acessar (null se não pode).
    private function localizar_conversa($chave)
    {
        $alvo = Mensagens_model::ler_chave($chave);
        if (!$alvo) {
            return null;
        }
        if ($alvo['tipo'] === 'grupo') {
            $grupo = $this->mensagens_model->grupo_do_membro($alvo['id'], $this->usuario(), $this->empresa());
            return $grupo ? array('tipo' => 'grupo', 'grupo' => $grupo) : null;
        }
        if ($alvo['tipo'] === 'canal') {
            $canal = $this->mensagens_model->canal_do_usuario($alvo['id'], $this->usuario(), $this->empresa());
            return $canal ? array('tipo' => 'canal', 'canal' => $canal) : null;
        }
        if ($alvo['id'] === $this->usuario()) {
            return null;
        }
        $contato = $this->mensagens_model->contato($alvo['id'], $this->empresa(), $this->usuario());
        return $contato ? array('tipo' => 'direta', 'contato' => $contato) : null;
    }

    // Marca como lido e devolve cabeçalho, mensagens (as novas, se $depois_id), status, fixadas, digitação e chamada ativa.
    private function abrir_conversa($alvo, $depois_id, $incluir_config = true)
    {
        if ($alvo['tipo'] === 'grupo') {
            $grupo = $alvo['grupo'];
            $chave = 'g' . (int) $grupo->id;
            $mensagens = $this->mensagens_model->conversa_grupo($this->usuario(), $grupo->id, $depois_id, 100, $this->empresa());
            $this->mensagens_model->marcar_lido_grupo($this->usuario(), $grupo->id);
            $dados = array(
                'conversa' => $this->mensagens_model->grupo_para_conversa($grupo, $this->usuario()),
                'mensagens' => $mensagens,
                'visto_ate' => 0,
                'entregue_ate' => 0,
                'fixadas' => $this->mensagens_model->fixadas_conversa($chave, $this->usuario(), $this->empresa()),
                'digitando' => $this->mensagens_model->quem_esta_digitando($this->usuario(), $this->empresa(), $chave),
                'chamada_ativa' => $this->mensagens_recursos_model->chamada_ativa_conversa($this->empresa(), $chave, $this->usuario()),
                'membros' => $this->mensagens_model->membros($grupo->id),
            );
            if ($incluir_config) {
                $dados['config'] = $this->mensagens_model->obter_config_conversa($this->usuario(), $this->empresa(), $chave);
            }
            return $dados;
        }
        if ($alvo['tipo'] === 'canal') {
            $canal = $alvo['canal'];
            $chave = 'c' . (int) $canal->id;
            $mensagens = $this->mensagens_model->conversa_canal($this->usuario(), $canal->id, $depois_id, 100, $this->empresa());
            $this->mensagens_model->marcar_lido_canal($this->usuario(), $canal->id);
            $dados = array(
                'conversa' => $this->mensagens_model->canal_para_conversa($canal, $this->usuario(), $this->empresa()),
                'mensagens' => $mensagens,
                'visto_ate' => 0,
                'entregue_ate' => 0,
                'fixadas' => $this->mensagens_model->fixadas_conversa($chave, $this->usuario(), $this->empresa()),
                'digitando' => $this->mensagens_model->quem_esta_digitando($this->usuario(), $this->empresa(), $chave),
                'chamada_ativa' => $this->mensagens_recursos_model->chamada_ativa_conversa($this->empresa(), $chave, $this->usuario()),
                'membros' => $this->mensagens_model->membros_canal($canal->id, $this->empresa(), $this->usuario()),
            );
            if ($incluir_config) {
                $dados['config'] = $this->mensagens_model->obter_config_conversa($this->usuario(), $this->empresa(), $chave);
            }
            return $dados;
        }
        $outro = $alvo['contato'];
        $chave = $outro['chave'];
        $this->mensagens_model->marcar_lidas($this->usuario(), $outro['id'], $this->empresa());
        $status = $this->mensagens_model->status_direta($this->usuario(), $outro['id'], $this->empresa());
        $dados = array(
            'conversa' => $outro,
            'mensagens' => $this->mensagens_model->conversa($this->usuario(), $outro['id'], $this->empresa(), $depois_id),
            'visto_ate' => $status['lida_ate'],
            'entregue_ate' => $status['entregue_ate'],
            'fixadas' => $this->mensagens_model->fixadas_conversa($chave, $this->usuario(), $this->empresa()),
            'digitando' => $this->mensagens_model->quem_esta_digitando($this->usuario(), $this->empresa(), $chave),
            'chamada_ativa' => $this->mensagens_recursos_model->chamada_ativa_conversa($this->empresa(), $chave, $this->usuario()),
        );
        if ($incluir_config) {
            $dados['config'] = $this->mensagens_model->obter_config_conversa($this->usuario(), $this->empresa(), $chave);
        }
        return $dados;
    }

    // Envia texto e/ou um arquivo (campo "arquivo"), com suporte a resposta e menções (@).
    public function enviar()
    {
        if ($this->input->method() !== 'post') {
            $this->falhar(405, 'Método inválido.');
            return;
        }
        // Acima do post_max_size do PHP o POST chega vazio: avisa em vez de dizer que o contato não existe.
        if (empty($_POST) && empty($_FILES) && (int) $this->input->server('CONTENT_LENGTH') > 0) {
            $this->falhar(413, 'O arquivo passa do limite de envio do servidor (' . ini_get('post_max_size') . ').');
            return;
        }
        $alvo = $this->exigir_conversa($this->input->post('para'));
        if (!$alvo) {
            return;
        }
        if ($alvo['tipo'] === 'canal' && !$this->mensagens_model->pode_enviar_canal($alvo['canal']->id, $this->usuario(), $this->empresa())) {
            $this->falhar(403, 'Você não tem permissão para enviar mensagens neste canal.');
            return;
        }
        if ($alvo['tipo'] === 'direta' && !empty($alvo['contato']['is_bot'])) {
            $this->falhar(403, 'Este usuário é um bot automático e não aceita respostas.');
            return;
        }
        $texto = (string) $this->input->post('texto');
        if (mb_strlen(trim($texto), 'UTF-8') > Mensagens_model::TAMANHO_MAXIMO) {
            $this->falhar(422, 'A mensagem passa de ' . Mensagens_model::TAMANHO_MAXIMO . ' caracteres.');
            return;
        }

        $anexo = null;
        if (!empty($_FILES['arquivo']['name'])) {
            $anexo = $this->salvar_anexo();
            if (is_string($anexo)) {
                $this->falhar(422, $anexo);
                return;
            }
        } elseif (trim($texto) === '') {
            $this->falhar(422, 'Escreva a mensagem ou escolha um arquivo antes de enviar.');
            return;
        }

        $resposta_id = (int) $this->input->post('resposta_id');
        $mencoes = (array) $this->input->post('mencoes');
        $transcricao = trim((string) $this->input->post('transcricao'));
        if ($anexo && $anexo['tipo'] === 'audio' && $transcricao === '') {
            $auto = $this->mensagens_recursos_model->transcrever_arquivo_servidor(FCPATH . Mensagens_model::PASTA_ANEXOS . $anexo['arquivo'], $anexo['nome']);
            if (!empty($auto['ok'])) {
                $transcricao = $auto['texto'];
            }
        }

        if ($alvo['tipo'] === 'grupo') {
            $mensagem = $this->mensagens_model->enviar($this->usuario(), 0, $this->empresa(), $texto, $anexo, $alvo['grupo']->id, $resposta_id ?: null, $mencoes, false, null, $transcricao);
        } elseif ($alvo['tipo'] === 'canal') {
            $mensagem = $this->mensagens_model->enviar($this->usuario(), 0, $this->empresa(), $texto, $anexo, null, $resposta_id ?: null, $mencoes, false, $alvo['canal']->id, $transcricao);
        } else {
            $mensagem = $this->mensagens_model->enviar($this->usuario(), $alvo['contato']['id'], $this->empresa(), $texto, $anexo, null, $resposta_id ?: null, $mencoes, false, null, $transcricao);
        }
        if (!$mensagem) {
            if ($anexo) {
                @unlink(FCPATH . Mensagens_model::PASTA_ANEXOS . $anexo['arquivo']);
            }
            $this->falhar(500, 'Não foi possível enviar a mensagem. Tente de novo.');
            return;
        }
        $chave_env = $alvo['tipo'] === 'canal' ? ('c' . $alvo['canal']->id) : ($alvo['tipo'] === 'grupo' ? ('g' . $alvo['grupo']->id) : $alvo['contato']['chave']);
        $this->mensagens_recursos_model->salvar_rascunho($this->empresa(), $this->usuario(), $chave_env, '');
        $resposta = array('ok' => true, 'mensagem' => $mensagem);
        if ($anexo && !empty($anexo['comprimido_de'])) {
            $resposta['comprimido'] = array('de' => $this->tamanho_legivel($anexo['comprimido_de']), 'para' => $this->tamanho_legivel($anexo['tamanho']));
        }
        $this->responder($resposta);
    }

    /* ---------- Ações nas mensagens (editar, excluir, fixar, favoritar, encaminhar, buscar, etc.) ---------- */

    public function editar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $resultado = $this->mensagens_model->editar_mensagem(
            (int) $this->input->post('id'),
            $this->usuario(),
            $this->empresa(),
            $this->input->post('texto'),
            (array) $this->input->post('mencoes')
        );
        if (is_string($resultado)) {
            $this->falhar(422, $resultado);
            return;
        }
        $this->responder(array('ok' => true, 'mensagem' => $resultado));
    }

    public function excluir()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $para_todos = $this->input->post('modo') === 'para_todos';
        $resultado = $this->mensagens_model->excluir_mensagem((int) $this->input->post('id'), $this->usuario(), $this->empresa(), $para_todos);
        if (is_string($resultado)) {
            $this->falhar(422, $resultado);
            return;
        }
        $this->responder(array_merge(array('ok' => true), $resultado));
    }

    public function fixar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $chave = (string) $this->input->post('para');
        $alvo = $this->exigir_conversa($chave);
        if (!$alvo) {
            return;
        }
        $fixar = $this->input->post('fixar') !== '0';
        $resultado = $this->mensagens_model->fixar_mensagem((int) $this->input->post('id'), $this->usuario(), $this->empresa(), $fixar);
        if (is_string($resultado)) {
            $this->falhar(422, $resultado);
            return;
        }
        $this->responder(array(
            'ok' => true,
            'fixadas' => $this->mensagens_model->fixadas_conversa($chave, $this->usuario(), $this->empresa()),
        ));
    }

    public function favoritar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $resultado = $this->mensagens_model->alternar_favorita((int) $this->input->post('id'), $this->usuario(), $this->empresa());
        if (is_string($resultado)) {
            $this->falhar(422, $resultado);
            return;
        }
        $this->responder(array('ok' => true, 'favorita' => (bool) $resultado));
    }

    public function favoritas()
    {
        if (!$this->exigir_acesso()) {
            return;
        }
        $itens = $this->mensagens_model->favoritas($this->usuario(), $this->empresa(), (string) $this->input->get('conversa'));
        $this->responder(array('ok' => true, 'itens' => $itens));
    }

    public function visualizacoes($mensagem_id = null)
    {
        if (!$this->exigir_acesso()) {
            return;
        }
        $dados = $this->mensagens_model->visualizacoes_mensagem((int) $mensagem_id, $this->usuario(), $this->empresa());
        if (!$dados) {
            $this->falhar(404, 'Mensagem não encontrada.');
            return;
        }
        $this->responder(array_merge(array('ok' => true), $dados));
    }

    public function buscar()
    {
        if (!$this->exigir_acesso()) {
            return;
        }
        $conversa = trim((string) $this->input->get('conversa'));
        if ($conversa === '' || !$this->localizar_conversa($conversa)) {
            $this->falhar(422, 'Selecione uma conversa para buscar mensagens.');
            return;
        }
        $filtros = array(
            'q' => $this->input->get('q'),
            'usuario' => $this->input->get('usuario'),
            'de' => $this->input->get('de'),
            'ate' => $this->input->get('ate'),
            'tipo' => $this->input->get('tipo'),
            'conversa' => $conversa,
        );
        $itens = $this->mensagens_model->buscar_mensagens($this->usuario(), $this->empresa(), $filtros);
        $this->responder(array('ok' => true, 'itens' => $itens));
    }

    public function encaminhar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $resultado = $this->mensagens_model->encaminhar_mensagem(
            (int) $this->input->post('id'),
            $this->usuario(),
            $this->empresa(),
            (array) $this->input->post('destinos')
        );
        if (is_string($resultado)) {
            $this->falhar(422, $resultado);
            return;
        }
        $this->responder(array('ok' => true, 'enviadas' => $resultado));
    }

    public function digitando()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $chave = (string) $this->input->post('para');
        $estado = (string) $this->input->post('estado');
        if ($estado !== '' && !$this->localizar_conversa($chave)) {
            $this->responder(array('ok' => true));
            return;
        }
        $this->mensagens_model->definir_digitando($this->usuario(), $this->empresa(), $chave, $estado);
        $this->responder(array('ok' => true));
    }

    public function notificacao_config()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $chave = (string) $this->input->post('para');
        if (!$this->exigir_conversa($chave)) {
            return;
        }
        $modo = (string) $this->input->post('modo');
        $silenciar = $this->input->post('silenciar');
        $resultado = $this->mensagens_model->salvar_config_conversa($this->usuario(), $this->empresa(), $chave, $modo, $silenciar);
        if (is_string($resultado)) {
            $this->falhar(422, $resultado);
            return;
        }
        $this->responder(array('ok' => true, 'config' => $resultado));
    }

    /* ---------- Agendamento, rascunhos, não lida, etiquetas, lembretes e notas ---------- */

    public function agendar()
    {
        if (!$this->exigir_post()) {
            return;
        }
        $chave = (string) $this->input->post('para');
        $alvo = $this->exigir_conversa($chave);
        if (!$alvo) {
            return;
        }
        if ($alvo['tipo'] === 'canal' && !$this->mensagens_model->pode_enviar_canal($alvo['canal']->id, $this->usuario(), $this->empresa())) {
            $this->falhar(403, 'Você não tem permissão para enviar mensagens neste canal.');
            return;
        }
        if ($alvo['tipo'] === 'direta' && !empty($alvo['contato']['is_bot'])) {
            $this->falhar(403, 'Este usuário é um bot automático e não aceita respostas.');
            return;
        }
        if ($alvo['tipo'] === 'direta' && !empty($alvo['contato']['is_bot'])) {
            $this->falhar(403, 'Este usuário é um bot automático e não aceita respostas.');
            return;
        }
        $res = $this->mensagens_recursos_model->agendar(
            $this->empresa(),
            $this->usuario(),
            $chave,
            $this->input->post('texto'),
            $this->input->post('enviar_em'),
            $this->input->post('resposta_id'),
            (array) $this->input->post('mencoes')
        );
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->mensagens_recursos_model->salvar_rascunho($this->empresa(), $this->usuario(), $chave, '');
        $this->responder(array('ok' => true, 'id' => $res, 'agendadas' => $this->mensagens_recursos_model->listar_agendadas($this->empresa(), $this->usuario())));
    }

    public function agendadas()
    {
        if (!$this->exigir_acesso()) {
            return;
        }
        $this->mensagens_recursos_model->processar_agendadas();
        $this->responder(array('ok' => true, 'itens' => $this->mensagens_recursos_model->listar_agendadas($this->empresa(), $this->usuario(), (string) $this->input->get('para'))));
    }

    public function agendada_editar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $res = $this->mensagens_recursos_model->editar_agendada((int) $this->input->post('id'), $this->empresa(), $this->usuario(), $this->input->post('texto'), $this->input->post('enviar_em'));
        $this->responder_acao($res);
    }

    public function agendada_cancelar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $res = $this->mensagens_recursos_model->cancelar_agendada((int) $this->input->post('id'), $this->empresa(), $this->usuario());
        $this->responder_acao($res);
    }

    public function rascunho()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $chave = (string) $this->input->post('para');
        if (!$this->localizar_conversa($chave)) {
            $this->responder(array('ok' => true));
            return;
        }
        $this->mensagens_recursos_model->salvar_rascunho($this->empresa(), $this->usuario(), $chave, $this->input->post('texto'));
        $this->responder(array('ok' => true));
    }

    public function marcar_nao_lida()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $chave = (string) $this->input->post('para');
        if (!$this->exigir_conversa($chave)) {
            return;
        }
        $nao_lida = $this->input->post('nao_lida') !== '0';
        $res = $this->mensagens_recursos_model->marcar_conversa_nao_lida($this->empresa(), $this->usuario(), $chave, $nao_lida);
        $this->responder_acao($res);
    }

    public function etiquetas()
    {
        if (!$this->exigir_acesso()) {
            return;
        }
        $this->responder(array('ok' => true, 'itens' => $this->mensagens_recursos_model->listar_etiquetas($this->empresa(), $this->usuario())));
    }

    public function etiqueta_criar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $res = $this->mensagens_recursos_model->criar_etiqueta($this->empresa(), $this->usuario(), $this->input->post('nome'), $this->input->post('cor'));
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'etiqueta' => $res));
    }

    public function etiqueta_excluir()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $this->mensagens_recursos_model->excluir_etiqueta((int) $this->input->post('id'), $this->empresa(), $this->usuario());
        $this->responder(array('ok' => true));
    }

    public function etiqueta_conversa()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $chave = (string) $this->input->post('para');
        if (!$this->exigir_conversa($chave)) {
            return;
        }
        $res = $this->mensagens_recursos_model->alternar_etiqueta_conversa($this->empresa(), $this->usuario(), $chave, (int) $this->input->post('etiqueta_id'));
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'ativa' => (bool) $res));
    }

    public function lembrete_criar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $msg_id = (int) $this->input->post('mensagem_id');
        $m = $this->mensagens_model->mensagem_do_usuario($msg_id, $this->usuario(), $this->empresa());
        if (!$m) {
            $this->falhar(404, 'Mensagem não encontrada.');
            return;
        }
        $chave = !empty($m->canal_id) ? ('c' . (int) $m->canal_id) : (!empty($m->grupo_id) ? ('g' . (int) $m->grupo_id) : ('u' . ((int) $m->de_id === $this->usuario() ? (int) $m->para_id : (int) $m->de_id)));
        $res = $this->mensagens_recursos_model->criar_lembrete($this->empresa(), $this->usuario(), $msg_id, $chave, $this->input->post('nota'), $this->input->post('lembrar_em'));
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'id' => $res, 'lembretes' => $this->mensagens_recursos_model->listar_lembretes($this->empresa(), $this->usuario())));
    }

    public function lembretes()
    {
        if (!$this->exigir_acesso()) {
            return;
        }
        $this->responder(array('ok' => true, 'itens' => $this->mensagens_recursos_model->listar_lembretes($this->empresa(), $this->usuario())));
    }

    public function lembrete_concluir()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $this->mensagens_recursos_model->concluir_lembrete((int) $this->input->post('id'), $this->empresa(), $this->usuario());
        $this->responder(array('ok' => true, 'lembretes' => $this->mensagens_recursos_model->listar_lembretes($this->empresa(), $this->usuario())));
    }

    public function lembrete_excluir()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $this->mensagens_recursos_model->excluir_lembrete((int) $this->input->post('id'), $this->empresa(), $this->usuario());
        $this->responder(array('ok' => true, 'lembretes' => $this->mensagens_recursos_model->listar_lembretes($this->empresa(), $this->usuario())));
    }

    public function notas($chave = null)
    {
        $alvo = $this->exigir_conversa($chave);
        if (!$alvo) {
            return;
        }
        $this->responder(array('ok' => true, 'itens' => $this->mensagens_recursos_model->listar_notas($this->empresa(), $this->usuario(), (string) $chave)));
    }

    public function nota_salvar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $chave = (string) $this->input->post('para');
        if (!$this->exigir_conversa($chave)) {
            return;
        }
        $res = $this->mensagens_recursos_model->salvar_nota($this->empresa(), $this->usuario(), $chave, $this->input->post('texto'), (int) $this->input->post('id'));
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'itens' => $this->mensagens_recursos_model->listar_notas($this->empresa(), $this->usuario(), $chave)));
    }

    public function nota_excluir()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $this->mensagens_recursos_model->excluir_nota((int) $this->input->post('id'), $this->empresa(), $this->usuario());
        $this->responder(array('ok' => true));
    }

    /* ---------- Transcrição, Chamados, Status, Mídias, Chamadas e Canais ---------- */

    public function transcrever()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $msg_id = (int) $this->input->post('id');
        $m = $this->mensagens_model->anexo($msg_id, $this->usuario(), $this->empresa());
        if (!$m || $m->anexo_tipo !== 'audio') {
            $this->falhar(404, 'Áudio não encontrado.');
            return;
        }
        // Transcrição digitada só pelo autor do áudio: senão qualquer membro poderia trocar o texto exibido a todos.
        $eh_autor = (int) $m->de_id === $this->usuario();
        $texto_cliente = trim((string) $this->input->post('texto'));
        if ($texto_cliente !== '' && !$eh_autor) {
            $this->falhar(403, 'Só quem enviou o áudio pode escrever a transcrição.');
            return;
        }
        if ($texto_cliente !== '') {
            $this->mensagens_recursos_model->salvar_transcricao($msg_id, $this->empresa(), $texto_cliente);
            $this->responder(array('ok' => true, 'transcricao' => $texto_cliente));
            return;
        }
        $caminho = FCPATH . Mensagens_model::PASTA_ANEXOS . basename($m->anexo);
        $auto = $this->mensagens_recursos_model->transcrever_arquivo_servidor($caminho, $m->anexo_nome);
        if (!empty($auto['ok'])) {
            $this->mensagens_recursos_model->salvar_transcricao($msg_id, $this->empresa(), $auto['texto']);
            $this->responder(array('ok' => true, 'transcricao' => $auto['texto']));
            return;
        }
        $this->responder(array(
            'ok' => false,
            'precisa_cliente' => $eh_autor,
            'erro' => $eh_autor ? 'Transcrição automática no servidor indisponível sem chave API configurada.' : 'A transcrição automática não está disponível. Só quem enviou o áudio pode escrever a transcrição.',
        ));
    }

    public function criar_chamado()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        // Mesma permissão da tela de abertura de chamados (CLAUDE.md: has_permissao em cada endpoint).
        if (!$this->ion_auth->has_permissao('chamados', 'create')) {
            $this->falhar(403, 'Seu grupo não tem permissão para abrir chamados.');
            return;
        }
        $m = $this->mensagens_model->mensagem_do_usuario((int) $this->input->post('mensagem_id'), $this->usuario(), $this->empresa());
        if (!$m || !empty($m->sistema) || !empty($m->excluida_em)) {
            $this->falhar(404, 'Mensagem indisponível para gerar chamado.');
            return;
        }
        $res = $this->mensagens_recursos_model->criar_chamado_da_mensagem($m, $this->usuario(), $this->empresa(), $this->input->post());
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'chamado' => $res));
    }

    /**
     * Dados do modal "Abrir chamado" do atendimento ao cliente (conversa direta com um cliente): o cliente como
     * solicitante, as unidades dele (a primeira já vem escolhida), responsáveis, prioridades e as mídias da conversa
     * para anexar. Só para suporte (suporte_ativo = 'S'), admin e desenvolvedores com permissão chamados/create.
     * O chamado em si é criado por chamados/create (origem=mensagens), com as mesmas validações da tela de chamados.
     */
    public function atendimento_chamado($chave = null)
    {
        $alvo = $this->exigir_conversa($chave);
        if (!$alvo) {
            return;
        }
        session_write_close();
        if (!$this->ion_auth->has_permissao('chamados', 'create')) {
            $this->falhar(403, 'Seu grupo não tem permissão para abrir chamados.');
            return;
        }
        $atendimento = $this->mensagens_recursos_model->atendimento_para_chamado((string) $chave, $this->usuario(), $this->empresa());
        if (is_string($atendimento)) {
            $this->falhar(422, $atendimento);
            return;
        }
        $this->load->model('chamados_model');
        $responsaveis = array();
        $grupos_resp = $this->chamados_model->get_groups();
        if ($grupos_resp) {
            foreach ($grupos_resp->result() as $u) {
                $responsaveis[] = array('id' => (int) $u->id, 'nome' => trim($u->first_name . ' ' . $u->last_name));
            }
        }
        $prioridades = array();
        foreach ($this->chamados_model->get_prioridades()->result() as $p) {
            $prioridades[] = array('id' => (int) $p->id, 'nome' => (string) $p->descricao);
        }
        $this->responder(array(
            'ok' => true,
            'cliente' => $atendimento['cliente'],
            'unidades' => $atendimento['unidades'],
            'responsaveis' => $responsaveis,
            'prioridades' => $prioridades,
            'completo' => (bool) $this->chamados_model->is_full_create(),
            'midias' => $this->mensagens_recursos_model->midias_conversa((string) $chave, $this->usuario(), $this->empresa(), ''),
            'url_criar' => site_url('chamados/create'),
            'url_base' => base_url(),
        ));
    }

    public function status_usuario()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $res = $this->mensagens_recursos_model->definir_status_usuario(
            $this->empresa(),
            $this->usuario(),
            (string) $this->input->post('status'),
            (string) $this->input->post('texto')
        );
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'status' => $res));
    }

    public function midias($chave = null)
    {
        $alvo = $this->exigir_conversa($chave);
        if (!$alvo) {
            return;
        }
        $itens = $this->mensagens_recursos_model->midias_conversa((string) $chave, $this->usuario(), $this->empresa(), (string) $this->input->get('tipo'));
        $this->responder(array('ok' => true, 'itens' => $itens));
    }

    public function chamada_entrar()
    {
        if (!$this->exigir_post()) {
            return;
        }
        $chave = (string) $this->input->post('para');
        $alvo = $this->exigir_conversa($chave);
        if (!$alvo) {
            return;
        }
        if ($alvo['tipo'] === 'direta' && !empty($alvo['contato']['is_bot'])) {
            $this->falhar(422, 'Não é possível iniciar ligação com o bot.');
            return;
        }
        $tipo = (string) $this->input->post('tipo');
        $tela = $this->input->post('tela') === '1';
        // Cliente só recebe ligação (o suporte liga para ele): entra numa ativa, mas não inicia uma nova.
        $pode_iniciar = !$this->mensagens_model->eh_cliente($this->usuario(), $this->empresa());
        $res = $this->mensagens_recursos_model->iniciar_ou_entrar_chamada($this->empresa(), $this->usuario(), $chave, $tipo, $tela, $pode_iniciar);
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'chamada' => $res));
    }

    public function chamada_recusar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $this->mensagens_recursos_model->recusar_chamada($this->empresa(), $this->usuario(), (int) $this->input->post('chamada_id'));
        $this->responder(array('ok' => true));
    }

    public function chamada_estado($chamada_id = null)
    {
        if (!$this->exigir_acesso()) {
            return;
        }
        $ultimo = max(0, (int) $this->input->get('depois'));
        $estado = $this->mensagens_recursos_model->estado_chamada((int) $chamada_id, $this->usuario(), $ultimo, $this->empresa());
        if (!$estado) {
            $this->falhar(404, 'Chamada não encontrada.');
            return;
        }
        $this->responder(array('ok' => true, 'chamada' => $estado));
    }

    public function chamada_atualizar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $this->mensagens_recursos_model->atualizar_participante_chamada(
            (int) $this->input->post('chamada_id'),
            $this->usuario(),
            $this->input->post('mudo') === '1',
            $this->input->post('tela') === '1',
            $this->input->post('falando') === '1'
        );
        $this->responder(array('ok' => true));
    }

    public function chamada_sinal()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $ok = $this->mensagens_recursos_model->enviar_sinal_chamada(
            (int) $this->input->post('chamada_id'),
            $this->usuario(),
            (int) $this->input->post('para_id'),
            (string) $this->input->post('tipo'),
            $this->input->post('dados'),
            $this->empresa()
        );
        $this->responder(array('ok' => $ok));
    }

    public function chamada_sair()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $this->mensagens_recursos_model->sair_chamada((int) $this->input->post('chamada_id'), $this->usuario(), $this->empresa());
        $this->responder(array('ok' => true));
    }

    public function canal_criar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        // Canal alcança grupos inteiros: um cliente chegaria a clientes de outras unidades.
        if ($this->mensagens_model->eh_cliente($this->usuario(), $this->empresa())) {
            $this->falhar(403, 'Clientes não podem criar canais.');
            return;
        }
        $res = $this->mensagens_model->criar_canal(
            $this->empresa(),
            $this->usuario(),
            $this->input->post('nome'),
            $this->input->post('descricao'),
            (array) $this->input->post('grupos'),
            (array) $this->input->post('escritores')
        );
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $canal = $this->mensagens_model->canal_do_usuario($res, $this->usuario(), $this->empresa());
        $this->responder(array_merge(array('ok' => true), $this->abrir_conversa(array('tipo' => 'canal', 'canal' => $canal), 0, true)));
    }

    public function canal_atualizar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $canal = $this->mensagens_model->canal_do_usuario((int) $this->input->post('canal_id'), $this->usuario(), $this->empresa());
        if (!$canal || (int) $canal->criador_id !== $this->usuario()) {
            $this->falhar(403, 'Só quem criou o canal pode editá-lo.');
            return;
        }
        if ($this->mensagens_model->eh_cliente($this->usuario(), $this->empresa())) {
            $this->falhar(403, 'Clientes não podem editar canais.');
            return;
        }
        $res = $this->mensagens_model->atualizar_canal(
            $canal,
            $this->input->post('nome'),
            $this->input->post('descricao'),
            (array) $this->input->post('grupos'),
            (array) $this->input->post('escritores')
        );
        $this->responder_acao($res);
    }

    public function canal_excluir()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $canal = $this->mensagens_model->canal_do_usuario((int) $this->input->post('canal_id'), $this->usuario(), $this->empresa());
        if (!$canal || (int) $canal->criador_id !== $this->usuario()) {
            $this->falhar(403, 'Só quem criou o canal pode excluí-lo.');
            return;
        }
        $this->responder_acao($this->mensagens_model->excluir_canal($canal));
    }

    // Entrega o anexo só para quem participa da conversa. Aceita Range (o Safari exige para tocar vídeo).
    public function arquivo($mensagem_id = null)
    {
        if (!$this->ion_auth->logged_in() || !$this->mensagens_model->tem_acesso($this->usuario(), $this->empresa())) {
            log_message('error', 'Mensagens_api::arquivo acesso negado para mensagem ' . (int) $mensagem_id);
            show_404();
        }
        $m = $this->mensagens_model->anexo((int) $mensagem_id, $this->usuario(), $this->empresa());
        $caminho = $m ? FCPATH . Mensagens_model::PASTA_ANEXOS . basename($m->anexo) : '';
        if (!$m || !is_file($caminho)) {
            log_message('error', 'Mensagens_api::arquivo indisponível para mensagem ' . (int) $mensagem_id . ($m ? (' (arquivo ausente: ' . $caminho . ')') : ' (anexo não autorizado/inexistente)'));
            show_404();
        }

        // A sessão fica travada enquanto a requisição dura; libera antes de transmitir arquivos grandes.
        session_write_close();
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $tamanho = filesize($caminho);
        $inicio = 0;
        $fim = $tamanho - 1;
        if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $faixa)) {
            if ($faixa[1] !== '') {
                $inicio = (int) $faixa[1];
                $fim = $faixa[2] !== '' ? min((int) $faixa[2], $tamanho - 1) : $tamanho - 1;
            } elseif ($faixa[2] !== '') {
                $inicio = max(0, $tamanho - (int) $faixa[2]);
            }
            if ($inicio > $fim || $inicio >= $tamanho) {
                header('HTTP/1.1 416 Range Not Satisfiable');
                header('Content-Range: bytes */' . $tamanho);
                exit;
            }
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes ' . $inicio . '-' . $fim . '/' . $tamanho);
        }

        $extensao = strtolower(pathinfo($m->anexo, PATHINFO_EXTENSION));
        // ?baixar=1 força o download (botão "Baixar"); sem ele, mídia e PDF abrem no navegador.
        $em_linha = !$this->input->get('baixar') && (in_array($m->anexo_tipo, array('imagem', 'video', 'audio'), true) || $extensao === 'pdf');
        $nome = str_replace(array('"', "\r", "\n"), '', (string) $m->anexo_nome);
        header('Content-Type: ' . $this->tipo_mime($extensao, $m->anexo_tipo));
        header('Content-Length: ' . ($fim - $inicio + 1));
        header('Accept-Ranges: bytes');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=86400');
        header('Content-Disposition: ' . ($em_linha ? 'inline' : 'attachment') . '; filename="' . preg_replace('/[^\x20-\x7E]/', '_', $nome) . '"; filename*=UTF-8\'\'' . rawurlencode($nome));

        $arquivo = fopen($caminho, 'rb');
        fseek($arquivo, $inicio);
        $restante = $fim - $inicio + 1;
        while ($restante > 0 && !feof($arquivo) && !connection_aborted()) {
            $bloco = fread($arquivo, min(8192, $restante));
            echo $bloco;
            flush();
            $restante -= strlen($bloco);
        }
        fclose($arquivo);
        exit;
    }

    // Salva o arquivo enviado em upload/mensagens/. Devolve os dados do anexo ou a mensagem de erro.
    private function salvar_anexo()
    {
        if (!$this->mensagens_model->aceita_anexos()) {
            return 'Os anexos ainda não estão disponíveis: aplique o script sql/2026-11-16_mensagens_anexos.sql no banco.';
        }
        $erro = (int) $_FILES['arquivo']['error'];
        if ($erro === UPLOAD_ERR_INI_SIZE || $erro === UPLOAD_ERR_FORM_SIZE) {
            return 'O arquivo passa do limite de envio do servidor (' . ini_get('upload_max_filesize') . ').';
        }
        if ($erro !== UPLOAD_ERR_OK) {
            return 'Não foi possível receber o arquivo. Tente de novo.';
        }
        $pasta = FCPATH . Mensagens_model::PASTA_ANEXOS;
        if (!$this->preparar_pasta($pasta)) {
            return 'A pasta de anexos não pôde ser criada no servidor.';
        }

        $extensoes = array();
        foreach ($this->tipos as $lista) {
            $extensoes = array_merge($extensoes, $lista);
        }
        $this->load->library('upload');
        $this->upload->initialize(array(
            'upload_path' => $pasta,
            'allowed_types' => implode('|', $extensoes),
            'max_size' => 1024 * self::TAMANHO_ANEXO_MB,
            'encrypt_name' => true,
            'file_ext_tolower' => true,
        ));
        if (!$this->upload->do_upload('arquivo')) {
            return strip_tags($this->upload->display_errors('', ''));
        }
        $dados = $this->upload->data();
        $extensao = strtolower(ltrim($dados['file_ext'], '.'));
        $tipo = 'arquivo';
        foreach ($this->tipos as $nome_tipo => $lista) {
            if (in_array($extensao, $lista, true)) {
                $tipo = $nome_tipo;
                break;
            }
        }
        // Gravações de voz no navegador costumam vir em .webm: força o tipo 'audio' para tocar no player de áudio.
        if ($this->input->post('gravacao_audio') === '1' && in_array($extensao, array('webm', 'weba', 'ogg', 'opus', 'wav', 'm4a', 'mp3'), true)) {
            $tipo = 'audio';
        }
        $nome = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', basename((string) $_FILES['arquivo']['name'])));
        clearstatcache();
        $anexo = array(
            'arquivo' => $dados['file_name'],
            'nome' => $nome !== '' ? $nome : $dados['file_name'],
            'tipo' => $tipo,
            'tamanho' => (int) filesize($pasta . $dados['file_name']),
        );
        if ($anexo['tamanho'] > self::LIMITE_COMPRESSAO_MB * 1048576) {
            $anexo = $this->comprimir($anexo, $pasta, $extensao);
        }
        return $anexo;
    }

    /* ---------- Compressão (anexos acima de LIMITE_COMPRESSAO_MB) ---------- */

    /**
     * Imagem: redimensiona e regrava em JPEG (GD). Vídeo e áudio: recodifica com ffmpeg, se existir no servidor.
     * Documentos e outros: compacta em .zip. A versão comprimida só substitui a original se ficar 10% menor.
     */
    private function comprimir($anexo, $pasta, $extensao)
    {
        @set_time_limit(300);
        $origem = $pasta . $anexo['arquivo'];
        $novo = null;
        if ($anexo['tipo'] === 'imagem') {
            $novo = $this->comprimir_imagem($origem, $pasta, $extensao);
        } elseif ($anexo['tipo'] === 'video' || $anexo['tipo'] === 'audio') {
            $novo = $this->comprimir_midia($origem, $pasta, $anexo['tipo']);
        }
        if (!$novo && !in_array($extensao, $this->ja_comprimidos, true)) {
            $novo = $this->comprimir_zip($origem, $pasta, $anexo['nome']);
        }
        if (!$novo) {
            return $anexo;
        }

        clearstatcache();
        $tamanho = is_file($pasta . $novo['arquivo']) ? (int) filesize($pasta . $novo['arquivo']) : 0;
        if ($tamanho <= 0 || $tamanho > $anexo['tamanho'] * 0.9) {
            @unlink($pasta . $novo['arquivo']);
            return $anexo;
        }
        @unlink($origem);
        $base = pathinfo($anexo['nome'], PATHINFO_FILENAME);
        return array(
            'arquivo' => $novo['arquivo'],
            'nome' => $novo['extensao'] === 'zip' ? $anexo['nome'] . '.zip' : ($base !== '' ? $base : 'arquivo') . '.' . $novo['extensao'],
            'tipo' => $novo['tipo'],
            'tamanho' => $tamanho,
            'comprimido_de' => $anexo['tamanho'],
        );
    }

    private function comprimir_imagem($origem, $pasta, $extensao)
    {
        if ($extensao === 'gif' || !function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')) {
            return null; // GIF perde a animação se for regravado
        }
        $info = @getimagesize($origem);
        if (!$info || $info[0] * $info[1] > 50000000) {
            return null; // acima de 50 megapixels o GD precisaria de memória demais
        }
        @ini_set('memory_limit', '512M');
        $abrir = array(IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng');
        if (defined('IMAGETYPE_WEBP')) {
            $abrir[IMAGETYPE_WEBP] = 'imagecreatefromwebp';
        }
        if (defined('IMAGETYPE_BMP')) {
            $abrir[IMAGETYPE_BMP] = 'imagecreatefrombmp';
        }
        if (!isset($abrir[$info[2]]) || !function_exists($abrir[$info[2]])) {
            return null;
        }
        $imagem = @call_user_func($abrir[$info[2]], $origem);
        if (!$imagem) {
            return null;
        }
        $escala = min(1, 2560 / max($info[0], $info[1]));
        $largura = max(1, (int) round($info[0] * $escala));
        $altura = max(1, (int) round($info[1] * $escala));
        $tela = imagecreatetruecolor($largura, $altura);
        imagefill($tela, 0, 0, imagecolorallocate($tela, 255, 255, 255)); // transparência vira fundo branco no JPEG
        imagecopyresampled($tela, $imagem, 0, 0, 0, 0, $largura, $altura, $info[0], $info[1]);
        imagedestroy($imagem);
        $arquivo = $this->nome_aleatorio('jpg');
        $ok = imagejpeg($tela, $pasta . $arquivo, 80);
        imagedestroy($tela);
        return $ok ? array('arquivo' => $arquivo, 'extensao' => 'jpg', 'tipo' => 'imagem') : null;
    }

    private function comprimir_midia($origem, $pasta, $tipo)
    {
        $ffmpeg = $this->ffmpeg();
        if ($ffmpeg === '') {
            return null;
        }
        if ($tipo === 'video') {
            $arquivo = $this->nome_aleatorio('mp4');
            $parametros = '-vf ' . escapeshellarg("scale='min(1280,iw)':-2") . ' -c:v libx264 -preset veryfast -crf 28 -c:a aac -b:a 96k -movflags +faststart';
            $extensao = 'mp4';
        } else {
            $arquivo = $this->nome_aleatorio('mp3');
            $parametros = '-vn -c:a libmp3lame -b:a 96k';
            $extensao = 'mp3';
        }
        $saida = array();
        $codigo = 1;
        @exec(escapeshellarg($ffmpeg) . ' -y -i ' . escapeshellarg($origem) . ' ' . $parametros . ' ' . escapeshellarg($pasta . $arquivo) . ' 2>&1', $saida, $codigo);
        if ($codigo !== 0) {
            @unlink($pasta . $arquivo);
            log_message('error', 'Mensagens: ffmpeg não comprimiu o anexo: ' . implode(' | ', array_slice($saida, -3)));
            return null;
        }
        return array('arquivo' => $arquivo, 'extensao' => $extensao, 'tipo' => $tipo);
    }

    private function comprimir_zip($origem, $pasta, $nome)
    {
        if (!class_exists('ZipArchive')) {
            return null;
        }
        $arquivo = $this->nome_aleatorio('zip');
        $zip = new ZipArchive();
        if ($zip->open($pasta . $arquivo, ZipArchive::CREATE) !== true) {
            return null;
        }
        $zip->addFile($origem, $nome);
        if (!$zip->close()) {
            @unlink($pasta . $arquivo);
            return null;
        }
        return array('arquivo' => $arquivo, 'extensao' => 'zip', 'tipo' => 'arquivo');
    }

    // Caminho do ffmpeg, ou '' quando o servidor não tem (ou não permite exec).
    private function ffmpeg()
    {
        static $binario = null;
        if ($binario !== null) {
            return $binario;
        }
        $binario = '';
        $bloqueadas = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (!function_exists('exec') || in_array('exec', $bloqueadas, true)) {
            return $binario;
        }
        foreach (array('ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg') as $candidato) {
            $saida = array();
            $codigo = 1;
            @exec(escapeshellarg($candidato) . ' -version 2>&1', $saida, $codigo);
            if ($codigo === 0) {
                $binario = $candidato;
                break;
            }
        }
        return $binario;
    }

    private function nome_aleatorio($extensao)
    {
        return md5(uniqid(mt_rand(), true)) . '.' . $extensao;
    }

    private function tamanho_legivel($bytes)
    {
        return $bytes >= 1048576 ? number_format($bytes / 1048576, 1, ',', '.') . ' MB' : max(1, (int) round($bytes / 1024)) . ' KB';
    }

    // Cria a pasta com bloqueio de acesso direto (os arquivos só saem por arquivo()).
    private function preparar_pasta($pasta)
    {
        if (!is_dir($pasta) && !@mkdir($pasta, 0755, true)) {
            return false;
        }
        if (!is_file($pasta . '.htaccess')) {
            @file_put_contents($pasta . '.htaccess', "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n");
        }
        if (!is_file($pasta . 'index.html')) {
            @file_put_contents($pasta . 'index.html', '<html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>');
        }
        return is_writable($pasta);
    }

    private function tipo_mime($extensao, $tipo = null)
    {
        if ($extensao === 'webm' && $tipo === 'audio') {
            return 'audio/webm';
        }
        $mimes = array(
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp', 'bmp' => 'image/bmp',
            'mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime',
            'mp3' => 'audio/mpeg', 'ogg' => 'audio/ogg', 'opus' => 'audio/opus', 'wav' => 'audio/wav', 'm4a' => 'audio/mp4', 'weba' => 'audio/webm',
            'pdf' => 'application/pdf',
        );
        return isset($mimes[$extensao]) ? $mimes[$extensao] : 'application/octet-stream';
    }

    public function reagir()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $res = $this->mensagens_recursos_model->alternar_reacao(
            (int) $this->input->post('mensagem_id'),
            $this->usuario(),
            $this->empresa(),
            $this->input->post('emoji')
        );
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'reacoes' => $res));
    }

    public function enquete_criar()
    {
        if (!$this->exigir_post()) {
            return;
        }
        $alvo = $this->exigir_conversa($this->input->post('para'));
        if (!$alvo) {
            return;
        }
        if ($alvo['tipo'] !== 'canal') {
            $this->falhar(422, 'Enquetes só podem ser criadas dentro de canais.');
            return;
        }
        if (!$this->mensagens_model->pode_enviar_canal($alvo['canal']->id, $this->usuario(), $this->empresa())) {
            $this->falhar(403, 'Você não tem permissão para enviar enquetes neste canal.');
            return;
        }
        $res = $this->mensagens_recursos_model->criar_enquete(
            $alvo['canal'],
            $this->usuario(),
            $this->empresa(),
            $this->input->post('pergunta'),
            (array) $this->input->post('opcoes'),
            $this->input->post('anonima') === '1',
            $this->input->post('multipla') === '1',
            $this->input->post('encerrar_em')
        );
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'mensagem' => $res));
    }

    public function enquete_votar()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $res = $this->mensagens_recursos_model->votar_enquete(
            (int) $this->input->post('enquete_id'),
            (int) $this->input->post('opcao_id'),
            $this->usuario(),
            $this->empresa()
        );
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'enquete' => $res));
    }

    public function reuniao_criar()
    {
        if (!$this->exigir_post()) {
            return;
        }
        $alvo = $this->exigir_conversa($this->input->post('para'));
        if (!$alvo) {
            return;
        }
        if ($alvo['tipo'] !== 'canal') {
            $this->falhar(422, 'Reuniões só podem ser criadas dentro de canais.');
            return;
        }
        if (!$this->mensagens_model->pode_enviar_canal($alvo['canal']->id, $this->usuario(), $this->empresa())) {
            $this->falhar(403, 'Você não tem permissão para criar reuniões neste canal.');
            return;
        }
        $res = $this->mensagens_recursos_model->criar_reuniao(
            $alvo['canal'],
            $this->usuario(),
            $this->empresa(),
            $this->input->post('titulo'),
            $this->input->post('descricao'),
            $this->input->post('inicio_em'),
            (array) $this->input->post('participantes')
        );
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'mensagem' => $res));
    }

    public function reuniao_responder()
    {
        if (!$this->exigir_post() || !$this->exigir_acesso()) {
            return;
        }
        $res = $this->mensagens_recursos_model->responder_reuniao(
            (int) $this->input->post('reuniao_id'),
            $this->usuario(),
            $this->empresa(),
            (string) $this->input->post('status')
        );
        if (is_string($res)) {
            $this->falhar(422, $res);
            return;
        }
        $this->responder(array('ok' => true, 'reuniao' => $res));
    }

    private function exigir_acesso()
    {
        if (!$this->ion_auth->logged_in()) {
            $this->falhar(401, 'Sua sessão expirou. Entre de novo.');
            return false;
        }
        if (!$this->mensagens_model->tem_acesso($this->usuario(), $this->empresa())) {
            $this->falhar(403, 'Seu grupo não tem acesso às mensagens.');
            return false;
        }
        return true;
    }

    private function exigir_conversa($chave)
    {
        if (!$this->exigir_acesso()) {
            return null;
        }
        $alvo = $this->localizar_conversa($chave);
        if (!$alvo) {
            $this->falhar(404, 'Conversa não encontrada. A pessoa pode ter perdido o acesso, ou você não participa mais do grupo.');
            return null;
        }
        return $alvo;
    }

    // Grupo ativo do qual o usuário participa; com $so_criador, só o criador passa.
    private function exigir_grupo($grupo_id, $so_criador = false)
    {
        if (!$this->exigir_acesso()) {
            return null;
        }
        $grupo = $this->mensagens_model->grupo_do_membro((int) $grupo_id, $this->usuario(), $this->empresa());
        if (!$grupo) {
            $this->falhar(404, 'Grupo não encontrado, ou você não participa mais dele.');
            return null;
        }
        if ($so_criador && (int) $grupo->criador_id !== $this->usuario()) {
            $this->falhar(403, 'Só quem criou o grupo pode fazer isso.');
            return null;
        }
        return $grupo;
    }

    private function exigir_post()
    {
        if ($this->input->method() !== 'post') {
            $this->falhar(405, 'Método inválido.');
            return false;
        }
        return true;
    }

    private function responder_acao($resultado)
    {
        if (is_string($resultado)) {
            $this->falhar(422, $resultado);
            return;
        }
        $this->responder(array('ok' => true));
    }

    private function falhar($status, $erro)
    {
        $this->output->set_status_header($status);
        $this->responder(array('ok' => false, 'erro' => $erro));
    }

    private function responder($dados)
    {
        $this->output->set_output(json_encode($dados));
    }

    private function usuario()
    {
        return (int) $this->session->userdata('user_id');
    }

    private function empresa()
    {
        return (int) $this->session->userdata('empresa_id');
    }
}
