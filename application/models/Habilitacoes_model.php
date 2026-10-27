<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Habilitações de produtos (e módulos) por cliente (unidade).
 *
 * Situação de uma habilitação:
 * - desabilitada: status = 'N';
 * - vencida: status = 'S' e período encerrado;
 * - vigente: status = 'S' e período ainda em curso (vencendo = até 30 dias para o fim).
 */
class Habilitacoes_model extends CI_Model {

    const DIAS_ALERTA = 30;

    private function empresa_id()
    {
        return (int) $this->session->userdata('empresa_id');
    }

    /* ---------------------------------------------------------------------
     * Listagem de clientes
     * ------------------------------------------------------------------- */

    // Resumo das habilitações por cliente, usado como tabela derivada na listagem.
    private function sql_resumo_clientes()
    {
        $alerta = (int) self::DIAS_ALERTA;
        return "SELECT hp.id_cliente,
                SUM(hp.status = 'S' AND hp.periodo_fim >= CURDATE()) AS vigentes,
                SUM(hp.status = 'S' AND hp.periodo_fim >= CURDATE() AND hp.periodo_fim <= CURDATE() + INTERVAL {$alerta} DAY) AS vencendo,
                SUM(hp.status = 'S' AND hp.periodo_fim < CURDATE()) AS vencidas,
                SUM(hp.status = 'N') AS desabilitadas,
                MIN(CASE WHEN hp.status = 'S' AND hp.periodo_fim >= CURDATE() THEN hp.periodo_fim END) AS proximo_vencimento,
                GROUP_CONCAT(CASE WHEN hp.status = 'S' AND hp.periodo_fim >= CURDATE()
                    THEN CONCAT_WS('##', hp.id_produto, p.nome, hp.demonstracao, hp.periodo_fim) END
                    ORDER BY p.nome SEPARATOR '||') AS produtos_vigentes
            FROM habilitacoes_produto hp
            JOIN produtos p ON p.id = hp.id_produto
            GROUP BY hp.id_cliente";
    }

    private function filtros_clientes($filtros)
    {
        $this->db->from('unidades u');
        $this->db->join('(' . $this->sql_resumo_clientes() . ') h', 'h.id_cliente = u.id', 'left', false);
        $this->db->where('u.empresa_id', $this->empresa_id());
        $this->db->where('u.actived', 's');

        if (!empty($filtros['q'])) {
            $digitos = preg_replace('/\D/', '', $filtros['q']);
            $this->db->group_start();
            $this->db->like('u.unidade', $filtros['q']);
            $this->db->or_like('u.razao_social', $filtros['q']);
            if ($digitos !== '') {
                $digitos = $this->db->escape_like_str($digitos);
                $this->db->or_where("REPLACE(REPLACE(REPLACE(u.cnpj, '.', ''), '/', ''), '-', '') LIKE '%" . $digitos . "%' ESCAPE '!'", null, false);
                $this->db->or_where("REPLACE(REPLACE(u.cpf, '.', ''), '-', '') LIKE '%" . $digitos . "%' ESCAPE '!'", null, false);
            }
            $this->db->group_end();
        }

        switch (isset($filtros['situacao']) ? $filtros['situacao'] : '') {
            case 'vigente':
                $this->db->where('h.vigentes >', 0);
                break;
            case 'vencendo':
                $this->db->where('h.vencendo >', 0);
                break;
            case 'vencida':
                $this->db->where('h.vencidas >', 0);
                break;
            case 'sem':
                $this->db->where('(h.vigentes IS NULL OR h.vigentes = 0)', null, false);
                break;
        }

        if (!empty($filtros['produto'])) {
            $this->db->where('u.id IN (SELECT hpf.id_cliente FROM habilitacoes_produto hpf WHERE hpf.status = \'S\' AND hpf.periodo_fim >= CURDATE() AND hpf.id_produto = ' . (int) $filtros['produto'] . ')', null, false);
        }
    }

    public function clientes_lista($filtros, $inicio, $quantidade, $ordem)
    {
        $this->db->select('u.id, u.unidade, u.razao_social, u.cnpj, u.cpf, h.vigentes, h.vencendo, h.vencidas, h.desabilitadas, h.proximo_vencimento, h.produtos_vigentes');
        $this->filtros_clientes($filtros);
        if ($ordem === 'vencimento') {
            // Clientes sem habilitação vigente vão para o fim.
            $this->db->order_by('h.proximo_vencimento IS NULL', 'ASC', false);
            $this->db->order_by('h.proximo_vencimento', 'ASC');
        } elseif ($ordem === 'vencimento_desc') {
            $this->db->order_by('h.proximo_vencimento', 'DESC');
        } elseif ($ordem === 'cliente_desc') {
            $this->db->order_by('u.unidade', 'DESC');
        } else {
            $this->db->order_by('u.unidade', 'ASC');
        }
        $this->db->limit((int) $quantidade, (int) $inicio);
        return $this->db->get()->result();
    }

    public function clientes_total($filtros = array())
    {
        $this->filtros_clientes($filtros);
        return (int) $this->db->count_all_results();
    }

    public function indicadores()
    {
        $alerta = (int) self::DIAS_ALERTA;
        return $this->db->query("SELECT
                COUNT(DISTINCT CASE WHEN hp.status = 'S' AND hp.periodo_fim >= CURDATE() THEN hp.id_cliente END) AS clientes_vigentes,
                SUM(hp.status = 'S' AND hp.periodo_fim >= CURDATE()) AS vigentes,
                SUM(hp.status = 'S' AND hp.periodo_fim >= CURDATE() AND hp.periodo_fim <= CURDATE() + INTERVAL {$alerta} DAY) AS vencendo,
                SUM(hp.status = 'S' AND hp.periodo_fim < CURDATE()) AS vencidas,
                SUM(hp.status = 'S' AND hp.periodo_fim >= CURDATE() AND hp.demonstracao = 'S') AS demonstracoes
            FROM habilitacoes_produto hp
            JOIN unidades u ON u.id = hp.id_cliente AND u.actived = 's'
            WHERE u.empresa_id = ?", array($this->empresa_id()))->row();
    }

    /* ---------------------------------------------------------------------
     * Cliente, produtos e habilitações
     * ------------------------------------------------------------------- */

    public function get_cliente($id_cliente)
    {
        return $this->db->select('u.id, u.unidade, u.razao_social, u.cnpj, u.cpf, u.gestor, u.telefone1, u.email, u.actived')
            ->where('u.id', (int) $id_cliente)
            ->where('u.empresa_id', $this->empresa_id())
            ->get('unidades u')
            ->row();
    }

    public function produtos_empresa()
    {
        $produtos = $this->db->select('p.id, p.nome, p.aplicacao')
            ->where('p.status', 'S')
            ->where('p.empresa_id', $this->empresa_id())
            ->order_by('p.nome')
            ->get('produtos p')
            ->result();
        if (!$produtos) {
            return array();
        }

        $ids = array();
        foreach ($produtos as $produto) {
            $ids[] = (int) $produto->id;
            $produto->versao = '';
            $produto->modulos = array();
        }
        $por_id = array();
        foreach ($produtos as $produto) {
            $por_id[$produto->id] = $produto;
        }

        foreach ($this->db->select('m.id, m.id_produto, m.nome')
            ->where_in('m.id_produto', $ids)
            ->where('m.status', 'S')
            ->order_by('m.nome')
            ->get('modulos m')->result() as $modulo) {
            $por_id[$modulo->id_produto]->modulos[] = array('id' => (int) $modulo->id, 'nome' => $modulo->nome);
        }

        // Última versão publicada de cada produto.
        foreach ($this->db->query('SELECT c.id_produto, c.versao FROM _produtos_changelog c
                JOIN (SELECT id_produto, MAX(id) AS id FROM _produtos_changelog WHERE status = \'S\' AND id_produto IN (' . implode(',', $ids) . ') GROUP BY id_produto) u ON u.id = c.id')->result() as $versao) {
            $por_id[$versao->id_produto]->versao = $versao->versao;
        }

        return $produtos;
    }

    public function get_produto($id_produto)
    {
        return $this->db->select('p.id, p.nome')
            ->where('p.id', (int) $id_produto)
            ->where('p.status', 'S')
            ->where('p.empresa_id', $this->empresa_id())
            ->get('produtos p')
            ->row();
    }

    public function modulos_produto($id_produto)
    {
        $ids = array();
        foreach ($this->db->select('id')->where('id_produto', (int) $id_produto)->where('status', 'S')->get('modulos')->result() as $modulo) {
            $ids[] = (int) $modulo->id;
        }
        return $ids;
    }

    // Habilitação do cliente, conferindo que ele é da empresa selecionada.
    public function get_habilitacao($id_habilitacao)
    {
        return $this->db->select('hp.*, u.unidade AS cliente')
            ->join('unidades u', 'u.id = hp.id_cliente')
            ->where('hp.id', (int) $id_habilitacao)
            ->where('u.empresa_id', $this->empresa_id())
            ->get('habilitacoes_produto hp')
            ->row();
    }

    public function habilitacoes_cliente($id_cliente)
    {
        $alerta = (int) self::DIAS_ALERTA;
        $habilitacoes = $this->db->select("hp.id, hp.id_produto, hp.periodo_inicio, hp.periodo_fim, hp.status, hp.demonstracao, hp.contrato_sax,
                hp.data_cadastro, hp.data_atualizacao, p.nome AS produto, p.aplicacao,
                CONCAT_WS(' ', uc.first_name, uc.last_name) AS cadastrado_por_nome,
                CONCAT_WS(' ', ua.first_name, ua.last_name) AS atualizado_por_nome,
                DATEDIFF(hp.periodo_fim, CURDATE()) AS dias_restantes,
                CASE WHEN hp.status = 'N' THEN 'desabilitada'
                     WHEN hp.periodo_fim < CURDATE() THEN 'vencida'
                     WHEN hp.periodo_fim <= CURDATE() + INTERVAL {$alerta} DAY THEN 'vencendo'
                     ELSE 'vigente' END AS situacao", false)
            ->join('produtos p', 'p.id = hp.id_produto')
            ->join('users uc', 'uc.id = hp.cadastrado_por', 'left')
            ->join('users ua', 'ua.id = hp.atualizado_por', 'left')
            ->where('hp.id_cliente', (int) $id_cliente)
            ->order_by("FIELD(situacao, 'vencendo', 'vigente', 'vencida', 'desabilitada')", '', false)
            ->order_by('p.nome', 'ASC')
            ->order_by('hp.periodo_fim', 'DESC')
            ->get('habilitacoes_produto hp')
            ->result();
        if (!$habilitacoes) {
            return array();
        }

        $por_id = array();
        $produtos = array();
        foreach ($habilitacoes as $habilitacao) {
            $habilitacao->modulos = array();
            $habilitacao->historico = array();
            $por_id[$habilitacao->id] = $habilitacao;
            $produtos[] = (int) $habilitacao->id_produto;
        }
        $ids = array_keys($por_id);

        // Todos os módulos ativos do produto; os que ainda não foram configurados aparecem como bloqueados.
        $configurados = array();
        foreach ($this->db->select('hm.id_habilitacao, hm.id_modulo, hm.usuarios, hm.status')
            ->where_in('hm.id_habilitacao', $ids)
            ->get('habilitacoes_modulo hm')->result() as $modulo) {
            $configurados[$modulo->id_habilitacao][$modulo->id_modulo] = $modulo;
        }
        $modulos_produto = array();
        foreach ($this->db->select('m.id, m.id_produto, m.nome')
            ->where_in('m.id_produto', array_unique($produtos))
            ->where('m.status', 'S')
            ->order_by('m.nome')
            ->get('modulos m')->result() as $modulo) {
            $modulos_produto[$modulo->id_produto][] = $modulo;
        }
        foreach ($por_id as $id => $habilitacao) {
            if (empty($modulos_produto[$habilitacao->id_produto])) {
                continue;
            }
            foreach ($modulos_produto[$habilitacao->id_produto] as $modulo) {
                $atual = isset($configurados[$id][$modulo->id]) ? $configurados[$id][$modulo->id] : null;
                $habilitacao->modulos[] = array(
                    'id' => (int) $modulo->id,
                    'nome' => $modulo->nome,
                    'usuarios' => $atual ? (int) $atual->usuarios : 0,
                    'status' => $atual ? $atual->status : 'B',
                );
            }
        }

        foreach ($this->db->select("h.id_habilitacao, h.observacao, h.data_cadastro, h.acao, CONCAT_WS(' ', u.first_name, u.last_name) AS usuario", false)
            ->join('users u', 'u.id = h.cadastrado_por', 'left')
            ->where_in('h.id_habilitacao', $ids)
            ->order_by('h.data_cadastro', 'DESC')
            ->get('historico_habilitacao h')->result() as $registro) {
            $por_id[$registro->id_habilitacao]->historico[] = $registro;
        }

        return $habilitacoes;
    }

    /* ---------------------------------------------------------------------
     * Gravação
     * ------------------------------------------------------------------- */

    // Insere ou atualiza a habilitação e os módulos (status L/B/C e usuários) em uma transação.
    public function salvar_habilitacao($id_habilitacao, $dados, $modulos)
    {
        $user_id = (int) $this->session->userdata('user_id');
        $agora = date('Y-m-d H:i:s');

        $this->db->trans_start();
        if ($id_habilitacao > 0) {
            $dados['data_atualizacao'] = $agora;
            $dados['atualizado_por'] = $user_id;
            $this->db->update('habilitacoes_produto', $dados, array('id' => (int) $id_habilitacao));
            $acao = 'atualizar_habilitacao';
        } else {
            $dados['data_cadastro'] = $agora;
            $dados['cadastrado_por'] = $user_id;
            $dados['status'] = 'S';
            $this->db->insert('habilitacoes_produto', $dados);
            $id_habilitacao = (int) $this->db->insert_id();
            $acao = 'cadastrar_habilitacao';
        }

        $existentes = array();
        foreach ($this->db->select('id, id_modulo')->where('id_habilitacao', $id_habilitacao)->get('habilitacoes_modulo')->result() as $modulo) {
            $existentes[(int) $modulo->id_modulo] = (int) $modulo->id;
        }
        foreach ($modulos as $id_modulo => $modulo) {
            if (isset($existentes[$id_modulo])) {
                $this->db->update('habilitacoes_modulo', array(
                    'usuarios' => $modulo['usuarios'],
                    'status' => $modulo['status'],
                    'data_atualizacao' => $agora,
                    'atualizado_por' => $user_id,
                ), array('id' => $existentes[$id_modulo]));
            } else {
                $this->db->insert('habilitacoes_modulo', array(
                    'id_habilitacao' => $id_habilitacao,
                    'id_modulo' => $id_modulo,
                    'usuarios' => $modulo['usuarios'],
                    'status' => $modulo['status'],
                    'data_cadastro' => $agora,
                    'cadastrado_por' => $user_id,
                ));
            }
        }

        $this->db->insert('auditorias', array(
            'controlador' => 'habilitacoes',
            'metodo' => $acao,
            'obs' => 'id=' . $id_habilitacao . ',id_produto=' . (int) $dados['id_produto'] . ',id_cliente=' . (int) $dados['id_cliente'] . ',periodo=' . $dados['periodo_inicio'] . '..' . $dados['periodo_fim'] . ',demonstracao=' . $dados['demonstracao'] . ',modulos=' . count($modulos),
            'empresa_id' => $this->empresa_id(),
            'user_id' => $user_id,
        ));
        $this->db->trans_complete();

        return $this->db->trans_status() ? $id_habilitacao : false;
    }

    // Desabilita ('N') ou reabilita ('S') a habilitação, registrando a justificativa.
    public function alterar_status($id_habilitacao, $status, $justificativa)
    {
        $user_id = (int) $this->session->userdata('user_id');
        $this->db->trans_start();
        $this->db->update('habilitacoes_produto', array(
            'status' => $status,
            'data_atualizacao' => date('Y-m-d H:i:s'),
            'atualizado_por' => $user_id,
        ), array('id' => (int) $id_habilitacao));
        $this->db->insert('historico_habilitacao', array(
            'id_habilitacao' => (int) $id_habilitacao,
            'observacao' => $justificativa,
            'data_cadastro' => date('Y-m-d H:i:s'),
            'cadastrado_por' => $user_id,
            'acao' => $status === 'N' ? 'D' : 'R',
        ));
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
