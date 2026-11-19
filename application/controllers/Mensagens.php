<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tela de mensagens: conversas 1:1 entre usuários da empresa.
 * Acesso pelos grupos liberados nas Preferências (ver Mensagens_model). As ações da conversa
 * (abrir, atualizar e enviar) ficam em Mensagens_api, mais leve para a atualização periódica.
 */
class Mensagens extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('mensagens_model');
        $this->load->model('mensagens_recursos_model');
        $this->page_title->push('Mensagens');
        $this->data['pagetitle'] = $this->page_title->show();
        $this->breadcrumbs->unshift(1, 'Mensagens', 'mensagens');
    }

    // $chave: "5" ou "u5" abre a conversa com o usuário 5; "g3" abre o grupo 3.
    public function index($chave = null)
    {
        $user_id = (int) $this->session->userdata('user_id');
        $empresa_id = (int) $this->session->userdata('empresa_id');

        if (!$this->mensagens_model->disponivel()) {
            $this->session->set_flashdata('message', show_alert_error('As mensagens ainda não estão disponíveis: aplique o script sql/2026-11-16_mensagens.sql no banco.'));
            redirect('dashboard', 'refresh');
        }
        if (!$this->mensagens_model->tem_acesso($user_id, $empresa_id)) {
            $this->session->set_flashdata('message', show_alert_error('Seu grupo não tem acesso às mensagens.'));
            redirect('dashboard', 'refresh');
        }

        $this->mensagens_model->marcar_entregues($user_id, $empresa_id);

        $atual = null;
        $mensagens = array();
        $visto_ate = 0;
        $entregue_ate = 0;
        $fixadas = array();
        $config_conversa = null;
        $membros_atual = array();
        $alvo = Mensagens_model::ler_chave($chave);
        // A leitura já foi registrada no MY_Controller (antes de montar o cabeçalho); aqui só por garantia.
        if ($alvo && $alvo['tipo'] === 'grupo') {
            $grupo = $this->mensagens_model->grupo_do_membro($alvo['id'], $user_id, $empresa_id);
            if ($grupo) {
                $atual = $this->mensagens_model->grupo_para_conversa($grupo, $user_id);
                $mensagens = $this->mensagens_model->conversa_grupo($user_id, $grupo->id, 0, 100, $empresa_id);
                $this->mensagens_model->marcar_lido_grupo($user_id, $grupo->id);
                $fixadas = $this->mensagens_model->fixadas_conversa($atual['chave'], $user_id, $empresa_id);
                $config_conversa = $this->mensagens_model->obter_config_conversa($user_id, $empresa_id, $atual['chave']);
                $membros_atual = $this->mensagens_model->membros($grupo->id);
            }
        } elseif ($alvo && $alvo['tipo'] === 'canal') {
            $canal = $this->mensagens_model->canal_do_usuario($alvo['id'], $user_id, $empresa_id);
            if ($canal) {
                $atual = $this->mensagens_model->canal_para_conversa($canal, $user_id, $empresa_id);
                $mensagens = $this->mensagens_model->conversa_canal($user_id, $canal->id, 0, 100, $empresa_id);
                $this->mensagens_model->marcar_lido_canal($user_id, $canal->id);
                $fixadas = $this->mensagens_model->fixadas_conversa($atual['chave'], $user_id, $empresa_id);
                $config_conversa = $this->mensagens_model->obter_config_conversa($user_id, $empresa_id, $atual['chave']);
                $membros_atual = $this->mensagens_model->membros_canal($canal->id, $empresa_id, $user_id);
            }
        } elseif ($alvo && $alvo['id'] !== $user_id) {
            $atual = $this->mensagens_model->contato($alvo['id'], $empresa_id, $user_id);
            if ($atual) {
                $this->mensagens_model->marcar_lidas($user_id, $alvo['id'], $empresa_id);
                $mensagens = $this->mensagens_model->conversa($user_id, $alvo['id'], $empresa_id);
                $status_direta = $this->mensagens_model->status_direta($user_id, $alvo['id'], $empresa_id);
                $visto_ate = $status_direta['lida_ate'];
                $entregue_ate = $status_direta['entregue_ate'];
                $fixadas = $this->mensagens_model->fixadas_conversa($atual['chave'], $user_id, $empresa_id);
                $config_conversa = $this->mensagens_model->obter_config_conversa($user_id, $empresa_id, $atual['chave']);
            }
        }

        $this->mensagens_recursos_model->heartbeat_status($empresa_id, $user_id);
        $contatos = $this->mensagens_model->contatos($user_id, $empresa_id);
        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['pessoas'] = $contatos;
        $this->data['conversas'] = $this->mensagens_model->conversas($user_id, $empresa_id, $contatos);
        $this->data['grupos_ativos'] = $this->mensagens_model->aceita_grupos();
        // Cliente não cria canal (o canal alcança grupos inteiros, inclusive clientes de outras unidades).
        $this->data['canais_ativos'] = $this->mensagens_model->aceita_canais() && !$this->mensagens_model->eh_cliente($user_id, $empresa_id);
        $this->data['grupos_empresa'] = $this->mensagens_model->grupos_empresa($empresa_id);
        $this->data['etiquetas'] = $this->mensagens_recursos_model->listar_etiquetas($empresa_id, $user_id);
        $this->data['lembretes'] = $this->mensagens_recursos_model->listar_lembretes($empresa_id, $user_id);
        $this->data['agendadas'] = $this->mensagens_recursos_model->listar_agendadas($empresa_id, $user_id);
        $this->data['meu_status'] = $this->mensagens_recursos_model->status_usuario($empresa_id, $user_id);
        $this->data['opcoes_chamado'] = $this->mensagens_recursos_model->opcoes_chamado($empresa_id);
        $this->data['pode_criar_chamado'] = $this->ion_auth->has_permissao('chamados', 'create');
        // Cliente não inicia ligação: só atende ou entra numa ligação já ativa (conferido em Mensagens_api::chamada_entrar).
        $this->data['pode_ligar'] = !$this->mensagens_model->eh_cliente($user_id, $empresa_id);
        // Botão "Abrir chamado" na conversa com cliente: suporte ativo, admin e desenvolvedores com chamados/create.
        $this->data['pode_chamado_atendimento'] = $this->data['pode_criar_chamado'] && $this->mensagens_model->pode_abrir_chamado_atendimento($user_id, $empresa_id);
        $this->data['recursos_ativos'] = $this->mensagens_model->aceita_recursos();
        $this->data['minimo_grupo'] = Mensagens_model::MINIMO_GRUPO;
        $this->data['tamanho_nome_grupo'] = Mensagens_model::TAMANHO_NOME_GRUPO;
        $this->data['atual'] = $atual;
        $this->data['conversa'] = $mensagens;
        $this->data['visto_ate'] = $visto_ate;
        $this->data['entregue_ate'] = $entregue_ate;
        $this->data['fixadas'] = $fixadas;
        $this->data['config_conversa'] = $config_conversa;
        $this->data['membros_atual'] = $membros_atual;
        $this->data['tamanho_maximo'] = Mensagens_model::TAMANHO_MAXIMO;
        $this->data['tamanho_anexo_mb'] = 20; // igual a Mensagens_api::TAMANHO_ANEXO_MB
        $this->data['anexos_ativos'] = $this->mensagens_model->aceita_anexos();
        $this->data['aceitos'] = 'image/*,video/mp4,video/webm,video/quicktime,audio/*,.weba,.opus,.pdf,.txt,.csv,.rtf,.doc,.docx,.odt,.xls,.xlsx,.xlsm,.ods,.ppt,.pptx,.pps,.ppsx,.odp,.zip,.rar,.xml,.json,.avi';
        $this->template->admin_render('admin/mensagens/index', $this->data);
    }
}
