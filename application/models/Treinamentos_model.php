<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Treinamentos_model extends CI_Model {

    protected $table = 'treinamentos';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retorna lista de treinamentos com filtros e dados do modulo
     */
    public function get_treinamentos($params = array())
    {
        $this->db->select('t.*, m.nome as modulo_nome, p.nome as produto_nome,
            (SELECT COUNT(te.id) FROM treinamento_etapas te WHERE te.treinamento_id = t.id AND te.ativo = 1) as total_etapas,
            (SELECT COALESCE(AVG(ta.nota), 0) FROM treinamento_avaliacoes ta WHERE ta.treinamento_id = t.id) as media_avaliacao,
            (SELECT COUNT(ta.id) FROM treinamento_avaliacoes ta WHERE ta.treinamento_id = t.id) as total_avaliacoes');
        $this->db->from($this->table . ' t');
        $this->db->join('modulos m', 'm.id = t.modulo_id', 'left');
        $this->db->join('produtos p', 'p.id = t.produto_id', 'left');

        if (isset($params['empresa_id'])) {
            $this->db->where('t.empresa_id', (int)$params['empresa_id']);
        }
        if (isset($params['modulo_id']) && !empty($params['modulo_id'])) {
            $this->db->where('t.modulo_id', (int)$params['modulo_id']);
        }
        if (isset($params['produto_id']) && !empty($params['produto_id'])) {
            $this->db->where('t.produto_id', (int)$params['produto_id']);
        }
        if (isset($params['ativo'])) {
            $this->db->where('t.ativo', (int)$params['ativo']);
        }
        if (isset($params['destaque'])) {
            $this->db->where('t.destaque', (int)$params['destaque']);
        }
        if (isset($params['novidade'])) {
            $this->db->where('t.novidade', (int)$params['novidade']);
        }
        if (isset($params['nivel']) && !empty($params['nivel'])) {
            $this->db->where('t.nivel', $params['nivel']);
        }
        if (isset($params['busca']) && !empty($params['busca'])) {
            $busca = $params['busca'];
            $this->db->group_start();
            $this->db->like('t.titulo', $busca);
            $this->db->or_like('t.descricao_curta', $busca);
            $this->db->or_like('t.objetivo', $busca);
            $this->db->or_like('m.nome', $busca);
            $this->db->group_end();
        }

        $orderBy = isset($params['order_by']) ? $params['order_by'] : 't.ordem ASC, t.id DESC';
        $this->db->order_by($orderBy);

        if (isset($params['limit'])) {
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            $this->db->limit((int)$params['limit'], $offset);
        }

        return $this->db->get()->result();
    }

    /**
     * Retorna um treinamento por ID com informacoes adicionais
     */
    public function get_treinamento($id)
    {
        $this->db->select('t.*, m.nome as modulo_nome, m.descricao as modulo_descricao, p.nome as produto_nome,
            (SELECT COUNT(te.id) FROM treinamento_etapas te WHERE te.treinamento_id = t.id AND te.ativo = 1) as total_etapas,
            (SELECT COALESCE(AVG(ta.nota), 0) FROM treinamento_avaliacoes ta WHERE ta.treinamento_id = t.id) as media_avaliacao,
            (SELECT COUNT(ta.id) FROM treinamento_avaliacoes ta WHERE ta.treinamento_id = t.id) as total_avaliacoes');
        $this->db->from($this->table . ' t');
        $this->db->join('modulos m', 'm.id = t.modulo_id', 'left');
        $this->db->join('produtos p', 'p.id = t.produto_id', 'left');
        $this->db->where('t.id', (int)$id);

        return $this->db->get()->row();
    }

    /**
     * Retorna modulos que possuem treinamentos cadastrados
     */
    public function get_modulos_com_treinamentos($empresa_id = 1, $user_id = null)
    {
        $this->db->select('m.id, m.nome, m.descricao, p.nome as produto_nome,
            COUNT(t.id) as total_treinamentos');
        $this->db->from('modulos m');
        $this->db->join('produtos p', 'p.id = m.id_produto', 'left');
        $this->db->join('treinamentos t', 't.modulo_id = m.id AND t.ativo = 1', 'inner');
        $this->db->where('m.status', 'S');
        
        if ($empresa_id) {
            $this->db->where('(t.empresa_id = ' . (int)$empresa_id . ' OR t.empresa_id IS NULL)');
        }

        $this->db->group_by('m.id, m.nome, m.descricao, p.nome');
        $this->db->order_by('m.nome ASC');

        return $this->db->get()->result();
    }

    /**
     * Valida se um usuario tem acesso a um treinamento
     */
    public function verificar_acesso($treinamento_id, $user_id)
    {
        $treinamento = $this->get_treinamento($treinamento_id);
        if (!$treinamento || $treinamento->ativo != 1) {
            return false;
        }

        // Se o acesso for livre, qualquer usuario logado pode acessar
        if ($treinamento->tipo_acesso === 'livre') {
            return true;
        }

        // Se for restrito, verificar permissoes individuais ou por grupo
        $this->db->from('treinamento_permissoes tp');
        $this->db->where('tp.treinamento_id', (int)$treinamento_id);
        $this->db->group_start();
        $this->db->where('tp.user_id', (int)$user_id);

        // Checar grupos do usuario
        $this->load->model('ion_auth_model');
        $user_groups = $this->ion_auth->get_users_groups($user_id)->result();
        $groupIds = array();
        foreach ($user_groups as $g) {
            $groupIds[] = $g->id;
        }
        if (!empty($groupIds)) {
            $this->db->or_where_in('tp.group_id', $groupIds);
        }
        $this->db->group_end();

        return ($this->db->count_all_results() > 0);
    }

    /**
     * Retorna permissoes cadastradas para um treinamento
     */
    public function get_permissoes_treinamento($treinamento_id)
    {
        $this->db->select('tp.*, u.first_name, u.last_name, u.email, g.name as group_name, g.description as group_description');
        $this->db->from('treinamento_permissoes tp');
        $this->db->join('users u', 'u.id = tp.user_id', 'left');
        $this->db->join('groups g', 'g.id = tp.group_id', 'left');
        $this->db->where('tp.treinamento_id', (int)$treinamento_id);
        return $this->db->get()->result();
    }

    /**
     * Salva permissoes de acesso para um treinamento
     */
    public function salvar_permissoes($treinamento_id, $userIds = array(), $groupIds = array())
    {
        $this->db->where('treinamento_id', (int)$treinamento_id);
        $this->db->delete('treinamento_permissoes');

        $now = date('Y-m-d H:i:s');
        if (!empty($userIds)) {
            foreach ($userIds as $uid) {
                if (!empty($uid)) {
                    $this->db->insert('treinamento_permissoes', array(
                        'treinamento_id' => (int)$treinamento_id,
                        'user_id' => (int)$uid,
                        'created_at' => $now
                    ));
                }
            }
        }

        if (!empty($groupIds)) {
            foreach ($groupIds as $gid) {
                if (!empty($gid)) {
                    $this->db->insert('treinamento_permissoes', array(
                        'treinamento_id' => (int)$treinamento_id,
                        'group_id' => (int)$gid,
                        'created_at' => $now
                    ));
                }
            }
        }

        return true;
    }

    /**
     * Inserir treinamento
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Atualizar treinamento
     */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int)$id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Excluir logicamente ou fisicamente
     */
    public function delete($id, $logical = true)
    {
        if ($logical) {
            return $this->update($id, array('ativo' => 0));
        } else {
            $this->db->where('id', (int)$id);
            return $this->db->delete($this->table);
        }
    }
}
