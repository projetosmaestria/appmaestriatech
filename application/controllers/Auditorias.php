<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditorias extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/auditorias');
        $this->load->model('auditorias_model');
        $this->load->model('permissoes_model');
        $this->load->helper('functions_helper');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_auditorias'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_auditorias'), 'auditorias');
    }

	public function index()
	{
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("auditorias", "index"))
        {
            redirect('auth/login', 'refresh');
        }

        $opcoes = $this->auditorias_model->opcoes_filtro();
        $permissoes = $this->permissoes_usuario();
        $telas = array();
        foreach ($opcoes['acoes'] as $acao) {
            if ($permissoes !== null && !$this->pode_ver($permissoes, $acao->controlador, $acao->metodo)) {
                continue;
            }
            if (!isset($telas[$acao->controlador])) {
                $telas[$acao->controlador] = array('nome' => $this->nome_tela($acao->controlador), 'acoes' => array());
            }
            $telas[$acao->controlador]['acoes'][$acao->metodo] = $this->permissoes_model->rotulo($acao->metodo);
        }
        uasort($telas, function ($a, $b) { return strcasecmp($a['nome'], $b['nome']); });

        $this->data['breadcrumb'] = $this->breadcrumbs->show();
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['usuarios'] = $opcoes['usuarios'];
        $this->data['telas'] = $telas;
        $this->data['inicio_padrao'] = date('Y-m-d', strtotime('-30 days'));
        $this->data['fim_padrao'] = date('Y-m-d');

        $this->template->admin_render('admin/auditorias/index', $this->data);
    }

    public function server_side()
    {
        $this->output->set_content_type('application/json');
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao('auditorias', 'index')) {
            $this->output->set_status_header(403)->set_output(json_encode(array('data' => array(), 'recordsTotal' => 0, 'recordsFiltered' => 0)));
            return;
        }

        $data = function ($valor) {
            $valor = (string) $valor;
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ? $valor : '';
        };
        $filtros = array(
            'inicio' => $data($this->input->post('inicio')),
            'fim' => $data($this->input->post('fim')),
            'usuario' => (int) $this->input->post('usuario'),
            'controlador' => preg_replace('/[^a-z0-9_]/', '', strtolower((string) $this->input->post('controlador'))),
            'metodo' => preg_replace('/[^a-zA-Z0-9_]/', '', (string) $this->input->post('metodo')),
            'q' => trim((string) $this->input->post('q')),
        );
        $ordem = $this->input->post('order');
        $direcao = isset($ordem[0]['dir']) && $ordem[0]['dir'] === 'asc' ? 'asc' : 'desc';
        $inicio = max(0, (int) $this->input->post('start'));
        $quantidade = (int) $this->input->post('length');
        if ($quantidade <= 0 || $quantidade > 100) {
            $quantidade = 25;
        }

        $permissoes = $this->permissoes_usuario();
        $linhas = array();
        foreach ($this->auditorias_model->lista($filtros, $permissoes, $inicio, $quantidade, $direcao) as $registro) {
            $obs = (string) $registro->obs;
            $resumo = mb_strlen($obs, 'UTF-8') > 160 ? mb_substr($obs, 0, 160, 'UTF-8') . '…' : $obs;
            $linhas[] = array(
                '<div class="lista-nome">' . date('d/m/Y', strtotime($registro->created_at)) . '</div><div class="lista-detalhe">' . date('H:i:s', strtotime($registro->created_at)) . '</div>',
                '<div>' . html_escape(trim($registro->usuario) !== '' ? $registro->usuario : 'Usuário #' . (int) $registro->user_id) . '</div>' . ($registro->email ? '<div class="lista-detalhe">' . html_escape($registro->email) . '</div>' : ''),
                '<div>' . html_escape($this->nome_tela($registro->controlador)) . '</div><div class="lista-detalhe">' . html_escape($this->permissoes_model->rotulo((string) $registro->metodo)) . '</div>',
                '<div class="auditoria-obs" title="' . html_escape($obs) . '">' . nl2br(html_escape($resumo)) . '</div>'
                    . ($resumo !== $obs ? '<a href="#" class="js-obs-completa small" data-obs="' . html_escape($obs) . '">ver tudo</a>' : ''),
            );
        }

        $this->output->set_output(json_encode(array(
            'draw' => (int) $this->input->post('draw'),
            'recordsTotal' => $this->auditorias_model->total(array(), $permissoes),
            'recordsFiltered' => $this->auditorias_model->total($filtros, $permissoes),
            'resumo' => $this->auditorias_model->resumo($filtros, $permissoes),
            'data' => $linhas,
        )));
    }

    // null = administrador (vê tudo); senão, as permissões somadas dos grupos do usuário.
    private function permissoes_usuario()
    {
        if ($this->ion_auth->is_admin()) {
            return null;
        }
        $permissoes = array();
        foreach ($this->ion_auth_model->get_users_groups_permissoes()->result() as $grupo) {
            foreach (Permissoes_model::desserializar($grupo->permissoes) as $controlador => $metodos) {
                if (!is_array($metodos)) {
                    continue;
                }
                $permissoes[$controlador] = array_values(array_unique(array_merge(isset($permissoes[$controlador]) ? $permissoes[$controlador] : array(), $metodos)));
            }
        }
        return $permissoes;
    }

    private function pode_ver($permissoes, $controlador, $metodo)
    {
        return isset($permissoes[$controlador]) && is_array($permissoes[$controlador])
            && (in_array('index', $permissoes[$controlador]) || in_array($metodo, $permissoes[$controlador]));
    }

    private function nome_tela($controlador)
    {
        static $nomes = null;
        if ($nomes === null) {
            $nomes = array();
            foreach ($this->db->select('controlador, nome, pai_id')->order_by('pai_id IS NULL', 'DESC', false)->get('menus')->result() as $menu) {
                $chave = strtolower(trim($menu->controlador));
                if ($chave !== '' && !isset($nomes[$chave])) {
                    $nomes[$chave] = $menu->nome;
                }
            }
        }
        $controlador = strtolower((string) $controlador);
        return isset($nomes[$controlador]) ? $nomes[$controlador] : ucfirst(str_replace('_', ' ', $controlador));
    }
}
