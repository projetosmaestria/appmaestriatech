<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notificações de chamado do usuário logado (sino do cabeçalho e avisos na tela).
 *
 * Estende MY_Controller, e não Admin_Controller, porque novas() é consultado a cada minuto por todas as abas:
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
        $this->responder(array(
            'ok' => true,
            'itens' => $this->chamado_notificacoes_model->novas_para_aviso($this->usuario(), $this->empresa()),
            'nao_lidas' => $this->chamado_notificacoes_model->contar_nao_lidas($this->usuario(), $this->empresa()),
        ));
    }

    public function marcar_todas()
    {
        if (!$this->exigir_ajax(true)) {
            return;
        }
        $this->chamado_notificacoes_model->marcar_todas_lidas($this->usuario(), $this->empresa());
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
