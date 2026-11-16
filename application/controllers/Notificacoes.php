<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notificações de chamado do usuário logado (sino do cabeçalho e avisos na tela). novas() também traz o resumo
 * das mensagens internas (envelope e sino) para quem tem acesso a elas.
 *
 * Estende MY_Controller, e não Admin_Controller, porque novas() é consultado a cada 30 segundos por todas as abas:
 * não precisa montar menu, mensagens e demais dados do cabeçalho. Todas as ações valem só para as notificações
 * do próprio usuário na empresa da sessão.
 */
class Notificacoes extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('chamado_notificacoes_model');
    }

    // Clique no sino ou no aviso: marca como lida e abre o chamado.
    public function abrir($id = null)
    {
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        $chamado_id = $this->chamado_notificacoes_model->marcar_lida((int) $id, $this->usuario(), $this->empresa());
        redirect($chamado_id ? 'chamados/details/' . $chamado_id : 'chamados', 'refresh');
    }

    // Notificações que ainda não apareceram como aviso na tela (já ficam marcadas como exibidas).
    public function novas()
    {
        if (!$this->exigir_ajax()) {
            return;
        }
        $resposta = array(
            'ok' => true,
            'itens' => $this->chamado_notificacoes_model->novas_para_aviso($this->usuario(), $this->empresa()),
            'nao_lidas' => $this->chamado_notificacoes_model->contar_nao_lidas($this->usuario(), $this->empresa()),
        );
        // Mensagens internas: não lidas por remetente e as que chegaram depois de msg_depois (aviso na tela).
        $this->load->model('mensagens_model');
        if ($this->input->get('msg_depois') !== null && $this->mensagens_model->tem_acesso($this->usuario(), $this->empresa())) {
            $resposta['mensagens'] = $this->mensagens_model->nao_lidas($this->usuario(), $this->empresa(), max(0, (int) $this->input->get('msg_depois')));
            $this->load->model('mensagens_recursos_model');
            $resposta['chamadas'] = $this->mensagens_recursos_model->chamadas_recebidas($this->empresa(), $this->usuario());
            if ($this->input->get('reacao_depois') !== null) {
                $resposta['reacoes_novas'] = $this->mensagens_recursos_model->reacoes_para_notificar($this->empresa(), $this->usuario(), max(0, (int) $this->input->get('reacao_depois')));
            }
        }
        $this->responder($resposta);
    }

    // Consulta leve de ligações recebidas pendentes para tocar no navegador em qualquer tela.
    public function chamadas()
    {
        if (!$this->exigir_ajax()) {
            return;
        }
        // Consulta feita a cada poucos segundos e que não grava nada na sessão: libera a trava da sessão
        // para não atrasar as outras requisições do mesmo usuário (envio de anexo, troca de tela).
        session_write_close();
        $chamadas = array();
        $this->load->model('mensagens_model');
        if ($this->mensagens_model->tem_acesso($this->usuario(), $this->empresa())) {
            $this->load->model('mensagens_recursos_model');
            $chamadas = $this->mensagens_recursos_model->chamadas_recebidas($this->empresa(), $this->usuario());
        }
        $this->responder(array('ok' => true, 'chamadas' => $chamadas));
    }

    public function marcar_todas()
    {
        if (!$this->exigir_ajax(true)) {
            return;
        }
        $this->chamado_notificacoes_model->marcar_todas_lidas($this->usuario(), $this->empresa());
        $chat_ultimo_id = (int) $this->input->post('chat_ultimo_id');
        if ($chat_ultimo_id <= 0) {
            $this->load->model('mensagens_model');
            if ($this->mensagens_model->tem_acesso($this->usuario(), $this->empresa())) {
                $chat_ultimo_id = (int) $this->mensagens_model->ultimo_id_recebido($this->usuario(), $this->empresa());
            }
        }
        if ($chat_ultimo_id > (int) $this->session->userdata('sino_chat_visto_id')) {
            $this->session->set_userdata('sino_chat_visto_id', $chat_ultimo_id);
        }
        $sino_legado_hash = (string) $this->session->userdata('sino_legado_hash_atual');
        if ($sino_legado_hash !== '') {
            $this->session->set_userdata('sino_legado_visto_hash', $sino_legado_hash);
        }
        $this->responder(array('ok' => true));
    }

    private function exigir_ajax($post = false)
    {
        if (!$this->ion_auth->logged_in()) {
            $this->output->set_status_header(401);
            $this->responder(array('ok' => false));
            return false;
        }
        if ($post && $this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            $this->responder(array('ok' => false));
            return false;
        }
        return true;
    }

    private function responder($dados)
    {
        $this->output->set_content_type('application/json', 'utf-8')->set_output(json_encode($dados));
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
