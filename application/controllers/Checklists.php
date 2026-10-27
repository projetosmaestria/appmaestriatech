<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Checklists extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        /* Load :: Common */
        $this->lang->load('admin/checklists');
        $this->load->model('checklists_model');
        $this->load->model('checklists_etapas_model'); // Vamos criar este model
        $this->load->helper('functions_helper');
        $this->load->library('form_validation');
        $this->load->library('iugu_library');

        /* Title Page :: Common */
        $this->page_title->push(lang('menu_checklists'));
        $this->data['pagetitle'] = $this->page_title->show();

        /* Breadcrumbs :: Common */
        $this->breadcrumbs->unshift(1, lang('menu_checklists'), 'checklists');
    }

    public function index()
    {
        if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("checklists", "index")) {
            redirect('auth/login', 'refresh');
        } else {
            /* Breadcrumbs */
            $this->breadcrumbs->unshift(2, lang('menu_checklists_list'), 'checklists');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            $checklists_result = $this->checklists_model->get();
            $this->data['checklists'] = $checklists_result->result();
            $this->data['message'] = $this->session->flashdata('message');

            /* Load Template */
            $this->template->admin_render('admin/checklists/index', $this->data);
        }
    }

    public function create()
    {
        if (! $this->ion_auth->logged_in() or ! $this->ion_auth->has_permissao("checklists", "create")) {
            redirect('auth/login', 'refresh');
        }

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_checklists_list'), 'checklists');
        $this->breadcrumbs->unshift(3, lang('menu_checklists_create'), 'checklists/create');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        /* Validações */
        $this->form_validation->set_rules('nome', lang('checklists_name'), 'required|trim|min_length[3]|max_length[255]');
        $this->form_validation->set_rules('descricao', lang('checklists_description'), 'trim|max_length[500]');
        $this->form_validation->set_rules('status', 'Status', 'integer|in_list[0,1]');

        // Validação customizada para as etapas
        $etapas = $this->input->post('etapas');
        if (!empty($etapas) && is_array($etapas)) {
            foreach ($etapas as $index => $etapa) {
                // Validar se prazo_padrao é número inteiro
                if (isset($etapa['prazo_padrao']) && $etapa['prazo_padrao'] !== '') {
                    if (!ctype_digit((string)$etapa['prazo_padrao']) || intval($etapa['prazo_padrao']) < 0) {
                        $this->form_validation->set_rules("etapas[$index][prazo_padrao]", "Prazo Padrão da Etapa " . ($index + 1), 'integer|greater_than_equal_to[0]');
                    }
                }
            }
        }

        if ($this->form_validation->run() == TRUE) {
            // Dados do checklist
            $checklist_data = array(
                'nome' => $this->input->post('nome'),
                'descricao' => $this->input->post('descricao'),
                'status' => $this->input->post('status') ? 1 : 0,
                'data_criacao' => date('Y-m-d H:i:s'),
                'data_atualizacao' => date('Y-m-d H:i:s')
            );

            // Inserir checklist
            $checklist_id = $this->checklists_model->insert($checklist_data);

            if ($checklist_id) {
                // Processar etapas
                $etapas = $this->input->post('etapas');

                if (!empty($etapas) && is_array($etapas)) {
                    $this->load->model('checklists_etapas_model');

                    foreach ($etapas as $etapa) {
                        // Validar dados da etapa
                        if (!empty($etapa['nome'])) {

                            // CORREÇÃO: Verificar se prazo_padrao está definido e não é vazio
                            $prazo_padrao = null;
                            if (isset($etapa['prazo_padrao']) && $etapa['prazo_padrao'] !== '') {
                                $prazo_padrao = intval($etapa['prazo_padrao']);
                            }

                            $etapa_data = array(
                                'checklist_id' => $checklist_id,
                                'nome' => trim($etapa['nome']),
                                'descricao' => isset($etapa['descricao']) ? trim($etapa['descricao']) : null,
                                'data_inicio' => !empty($etapa['data_inicio']) ? $etapa['data_inicio'] : null,
                                'prazo_padrao' => $prazo_padrao, // CORRIGIDO: usando a variável tratada
                                'responsavel' => isset($etapa['responsavel']) ? trim($etapa['responsavel']) : null,
                                'ordem' => isset($etapa['ordem']) ? (int)$etapa['ordem'] : 0,
                                'status' => 1,
                                'data_criacao' => date('Y-m-d H:i:s'),
                                'data_atualizacao' => date('Y-m-d H:i:s')
                            );

                            // Log para debug (opcional - pode remover depois)
                            log_message('debug', 'Dados da etapa: ' . print_r($etapa_data, true));

                            $this->checklists_etapas_model->insert($etapa_data);
                        }
                    }
                }

                $this->session->set_flashdata('message', lang('checklists_create_success'));
                redirect('checklists', 'refresh');
            } else {
                $this->data['error'] = lang('checklists_create_error');
            }
        }

        // Carregar view
        $this->data['message'] = $this->session->flashdata('message');
        $this->template->admin_render('admin/checklists/create', $this->data);
    }

    public function edit($id)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao("checklists", "edit")) {
            redirect('auth/login', 'refresh');
        }

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_checklists_list'), 'checklists');
        $this->breadcrumbs->unshift(3, lang('menu_checklists_edit'), 'checklists/edit');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        // Carregar checklist
        $checklist = $this->checklists_model->get($id);
        if (!$checklist) {
            $this->session->set_flashdata('error', 'Checklist não encontrado.');
            redirect('checklists', 'refresh');
        }
        $this->data['checklist'] = $checklist;

        // Carregar etapas
        $this->load->model('checklists_etapas_model');
        $this->data['etapas'] = $this->checklists_etapas_model->get_by_checklist($id);

        // Validações
        $this->form_validation->set_rules('nome', lang('checklists_name'), 'required|trim|min_length[3]|max_length[255]');

        // Validação customizada para as etapas
        $etapas = $this->input->post('etapas');
        if (!empty($etapas) && is_array($etapas)) {
            foreach ($etapas as $index => $etapa) {
                // Validar se prazo_padrao é número inteiro
                if (isset($etapa['prazo_padrao']) && $etapa['prazo_padrao'] !== '') {
                    if (!ctype_digit((string)$etapa['prazo_padrao']) || intval($etapa['prazo_padrao']) < 0) {
                        $this->form_validation->set_rules("etapas[$index][prazo_padrao]", "Prazo Padrão da Etapa " . ($index + 1), 'integer|greater_than_equal_to[0]');
                    }
                }
            }
        }

        if ($this->form_validation->run() == TRUE) {
            // Dados do checklist
            $checklist_data = array(
                'nome' => $this->input->post('nome'),
                'descricao' => $this->input->post('descricao'),
                'status' => $this->input->post('status') ? 1 : 0,
                'data_atualizacao' => date('Y-m-d H:i:s')
            );

            // Atualizar checklist
            if ($this->checklists_model->update($id, $checklist_data)) {
                // Processar etapas
                $etapas = $this->input->post('etapas');

                if (!empty($etapas) && is_array($etapas)) {
                    // Marcar todas as etapas existentes como inativas (soft delete)
                    $this->checklists_etapas_model->delete_by_checklist($id);

                    foreach ($etapas as $etapa) {
                        if (!empty($etapa['nome'])) {

                            // CORREÇÃO: Verificar se prazo_padrao está definido e não é vazio
                            $prazo_padrao = null;
                            if (isset($etapa['prazo_padrao']) && $etapa['prazo_padrao'] !== '') {
                                $prazo_padrao = intval($etapa['prazo_padrao']);
                            }

                            $etapa_data = array(
                                'checklist_id' => $id,
                                'nome' => trim($etapa['nome']),
                                'descricao' => isset($etapa['descricao']) ? trim($etapa['descricao']) : null,
                                'prazo_padrao' => $prazo_padrao, // CORRIGIDO: usando a variável tratada
                                'ordem' => isset($etapa['ordem']) ? (int)$etapa['ordem'] : 0,
                                'status' => 1,
                                'data_criacao' => date('Y-m-d H:i:s'),
                                'data_atualizacao' => date('Y-m-d H:i:s')
                            );

                            // Log para debug (opcional - pode remover depois)
                            log_message('debug', 'Dados da etapa (edit): ' . print_r($etapa_data, true));

                            // Se tiver ID, atualiza, senão insere
                            if (isset($etapa['id']) && !empty($etapa['id'])) {
                                $this->checklists_etapas_model->update($etapa['id'], $etapa_data);
                            } else {
                                $this->checklists_etapas_model->insert($etapa_data);
                            }
                        }
                    }
                }

                $this->session->set_flashdata('message', 'Checklist atualizado com sucesso.');
                redirect('checklists', 'refresh');
            } else {
                $this->data['error'] = 'Erro ao atualizar o checklist.';
            }
        }

        $this->data['message'] = $this->session->flashdata('message');
        $this->template->admin_render('admin/checklists/edit', $this->data);
    }

    public function view($id)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao("checklists", "view")) {
            redirect('auth/login', 'refresh');
        }

        /* Breadcrumbs */
        $this->breadcrumbs->unshift(2, lang('menu_checklists_list'), 'checklists');
        $this->breadcrumbs->unshift(3, lang('menu_checklists_view'), 'checklists/view');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        // Carregar checklist
        $checklist = $this->checklists_model->get($id);
        if (!$checklist) {
            $this->session->set_flashdata('error', 'Checklist não encontrado.');
            redirect('checklists', 'refresh');
        }
        $this->data['checklist'] = $checklist;

        // Carregar etapas
        $this->load->model('checklists_etapas_model');
        $this->data['etapas'] = $this->checklists_etapas_model->get_by_checklist($id);

        $this->data['message'] = $this->session->flashdata('message');
        $this->template->admin_render('admin/checklists/view', $this->data);
    }

    public function delete($id)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->has_permissao("checklists", "delete")) {
            redirect('auth/login', 'refresh');
        }

        $checklist = $this->checklists_model->get($id);
        if (!$checklist) {
            $this->session->set_flashdata('error', 'Checklist não encontrado.');
            redirect('checklists', 'refresh');
        }

        $this->load->model('checklists_etapas_model');

        $this->db->trans_start();

        $etapas = $this->checklists_etapas_model->get_by_checklist($id);
        foreach ($etapas as $etapa) {
            $this->checklists_etapas_model->force_delete($etapa->id);
        }

        // Excluir o checklist
        $deleted = $this->checklists_model->delete($id);

        // Finalizar transação
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE || !$deleted) {
            $this->session->set_flashdata('error', 'Erro ao excluir o checklist e suas etapas.');
        } else {
            $this->session->set_flashdata('message', 'Checklist e suas etapas excluídos com sucesso.');
        }

        redirect('checklists', 'refresh');
    }
}

