<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Novidades (avisos) por empresa, com imagens e registro de leitura por usuário.
 * Tabelas em sql/2026-11-12_novidades.sql.
 */
class Novidades_model extends CI_Model {

    /**
     * As tabelas já existem? (o cabeçalho consulta novidades em toda página: sem o SQL aplicado, nada quebra)
     */
    public function disponivel()
    {
        static $existe = null;
        if ($existe === null) {
            $existe = $this->db->table_exists('novidades') && $this->db->table_exists('novidades_leituras');
        }
        return $existe;
    }

    /**
     * Público por unidade e por grupo (sql/2026-11-16_novidades_publico.sql). Sem as tabelas, a novidade vale para todos.
     */
    public function aceita_publico()
    {
        static $existe = null;
        if ($existe === null) {
            $existe = $this->db->table_exists('novidades_unidades') && $this->db->table_exists('novidades_grupos');
        }
        return $existe;
    }

    /**
     * Filtra a consulta atual pelo público da novidade ($alias é a tabela/apelido de novidades na consulta):
     * - sem unidade escolhida vale para todos; com unidade(s), para admins e usuários vinculados a ela(s) em user_unidade;
     * - sem grupo escolhido vale para todos; com grupo(s), só para usuários desses grupos (admin incluído só se o grupo dele estiver na lista).
     */
    private function aplicar_publico($alias, $user_id, $eh_admin)
    {
        if (!$this->aceita_publico()) {
            return;
        }
        $user_id = (int) $user_id;
        if (!$eh_admin) {
            $this->db->where('(NOT EXISTS (SELECT 1 FROM novidades_unidades nu WHERE nu.novidade_id = ' . $alias . '.id)'
                . ' OR EXISTS (SELECT 1 FROM novidades_unidades nu JOIN user_unidade uu ON uu.unidade_id = nu.unidade_id'
                . ' WHERE nu.novidade_id = ' . $alias . '.id AND uu.user_id = ' . $user_id . '))', null, false);
        }
        $this->db->where('(NOT EXISTS (SELECT 1 FROM novidades_grupos ng WHERE ng.novidade_id = ' . $alias . '.id)'
            . ' OR EXISTS (SELECT 1 FROM novidades_grupos ng JOIN users_groups ug ON ug.group_id = ng.group_id'
            . ' WHERE ng.novidade_id = ' . $alias . '.id AND ug.user_id = ' . $user_id . '))', null, false);
    }

    // Opções do formulário: unidades e grupos de usuários da EMPRESA.
    public function unidades_empresa($empresa_id)
    {
        return $this->db->select('id, unidade, razao_social, actived')
            ->where('empresa_id', (int) $empresa_id)
            ->order_by('unidade', 'asc')
            ->get('unidades')
            ->result();
    }

    public function grupos_empresa($empresa_id)
    {
        return $this->db->select('id, name, description')
            ->where('empresa_id', (int) $empresa_id)
            ->order_by('name', 'asc')
            ->get('groups')
            ->result();
    }

    /**
     * Ids de unidades e grupos escolhidos para a novidade.
     */
    public function publico($novidade_id)
    {
        $publico = array('unidades' => array(), 'grupos' => array());
        if (!$this->aceita_publico() || (int) $novidade_id <= 0) {
            return $publico;
        }
        foreach ($this->db->select('unidade_id')->where('novidade_id', (int) $novidade_id)->get('novidades_unidades')->result() as $linha) {
            $publico['unidades'][] = (int) $linha->unidade_id;
        }
        foreach ($this->db->select('group_id')->where('novidade_id', (int) $novidade_id)->get('novidades_grupos')->result() as $linha) {
            $publico['grupos'][] = (int) $linha->group_id;
        }
        return $publico;
    }

    /**
     * Grava o público. Só aceita unidades e grupos da própria empresa (ids de outra empresa vindos do POST são descartados).
     */
    public function salvar_publico($novidade_id, $empresa_id, $unidades_ids, $grupos_ids)
    {
        if (!$this->aceita_publico()) {
            return false;
        }
        $novidade_id = (int) $novidade_id;
        $unidades_ids = array_values(array_unique(array_filter(array_map('intval', (array) $unidades_ids))));
        $grupos_ids = array_values(array_unique(array_filter(array_map('intval', (array) $grupos_ids))));
        $unidades = $unidades_ids ? $this->db->select('id')->where('empresa_id', (int) $empresa_id)->where_in('id', $unidades_ids)->get('unidades')->result() : array();
        $grupos = $grupos_ids ? $this->db->select('id')->where('empresa_id', (int) $empresa_id)->where_in('id', $grupos_ids)->get('groups')->result() : array();

        $this->db->trans_start();
        $this->db->delete('novidades_unidades', array('novidade_id' => $novidade_id));
        $this->db->delete('novidades_grupos', array('novidade_id' => $novidade_id));
        foreach ($unidades as $unidade) {
            $this->db->insert('novidades_unidades', array('novidade_id' => $novidade_id, 'unidade_id' => (int) $unidade->id));
        }
        foreach ($grupos as $grupo) {
            $this->db->insert('novidades_grupos', array('novidade_id' => $novidade_id, 'group_id' => (int) $grupo->id));
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Nomes das unidades e grupos de cada novidade, para a coluna "Público" da listagem.
     */
    private function publico_nomes($novidade_ids)
    {
        $mapa = array();
        $novidade_ids = array_values(array_filter(array_map('intval', (array) $novidade_ids)));
        if (!$novidade_ids || !$this->aceita_publico()) {
            return $mapa;
        }
        $unidades = $this->db->select('nu.novidade_id, un.unidade')
            ->from('novidades_unidades nu')
            ->join('unidades un', 'un.id = nu.unidade_id')
            ->where_in('nu.novidade_id', $novidade_ids)
            ->order_by('un.unidade', 'asc')
            ->get()->result();
        foreach ($unidades as $linha) {
            $mapa[(int) $linha->novidade_id]['unidades'][] = $linha->unidade;
        }
        $grupos = $this->db->select('ng.novidade_id, g.name')
            ->from('novidades_grupos ng')
            ->join('groups g', 'g.id = ng.group_id')
            ->where_in('ng.novidade_id', $novidade_ids)
            ->order_by('g.name', 'asc')
            ->get()->result();
        foreach ($grupos as $linha) {
            $mapa[(int) $linha->novidade_id]['grupos'][] = $linha->name;
        }
        return $mapa;
    }

    /**
     * Situação pela data e pelo status: vigente, agendada, encerrada ou inativa.
     */
    public function situacao($novidade, $agora = null)
    {
        $agora = $agora ? $agora : date('Y-m-d H:i:s');
        if ((int) $novidade->ativo !== 1) {
            return 'inativa';
        }
        if ($novidade->data_inicio > $agora) {
            return 'agendada';
        }
        if ($novidade->data_fim < $agora) {
            return 'encerrada';
        }
        return 'vigente';
    }

    /**
     * Listagem da administração, com contagem de imagens e de leituras.
     */
    public function listar($empresa_id, $filtros = array())
    {
        $agora = $this->db->escape(date('Y-m-d H:i:s'));
        $this->db->select('n.*, CONCAT(IFNULL(u.first_name, ""), " ", IFNULL(u.last_name, "")) AS autor, '
                . '(SELECT COUNT(*) FROM novidades_imagens ni WHERE ni.novidade_id = n.id) AS total_imagens, '
                . '(SELECT COUNT(*) FROM novidades_leituras nl WHERE nl.novidade_id = n.id) AS total_leituras', false)
            ->from('novidades n')
            ->join('users u', 'u.id = n.criado_por', 'left')
            ->where('n.empresa_id', (int) $empresa_id);

        $busca = isset($filtros['q']) ? trim((string) $filtros['q']) : '';
        if ($busca !== '') {
            $this->db->group_start()->like('n.titulo', $busca)->or_like('n.conteudo', $busca)->group_end();
        }

        $situacao = isset($filtros['situacao']) ? $filtros['situacao'] : '';
        if ($situacao === 'vigente') {
            $this->db->where('n.ativo', 1)->where('n.data_inicio <= ' . $agora, null, false)->where('n.data_fim >= ' . $agora, null, false);
        } elseif ($situacao === 'agendada') {
            $this->db->where('n.ativo', 1)->where('n.data_inicio > ' . $agora, null, false);
        } elseif ($situacao === 'encerrada') {
            $this->db->where('n.ativo', 1)->where('n.data_fim < ' . $agora, null, false);
        } elseif ($situacao === 'inativa') {
            $this->db->where('n.ativo', 0);
        }

        $novidades = $this->db->order_by('n.data_inicio', 'desc')->order_by('n.id', 'desc')->get()->result();

        $ids = array();
        foreach ($novidades as $novidade) {
            $ids[] = (int) $novidade->id;
        }
        $nomes = $this->publico_nomes($ids);
        foreach ($novidades as $novidade) {
            $id = (int) $novidade->id;
            $novidade->publico_unidades = isset($nomes[$id]['unidades']) ? $nomes[$id]['unidades'] : array();
            $novidade->publico_grupos = isset($nomes[$id]['grupos']) ? $nomes[$id]['grupos'] : array();
        }
        return $novidades;
    }

    /**
     * Totais por situação para os cards da listagem.
     */
    public function totais($empresa_id)
    {
        $totais = array('vigente' => 0, 'agendada' => 0, 'encerrada' => 0, 'inativa' => 0);
        $agora = date('Y-m-d H:i:s');
        $linhas = $this->db->select('ativo, data_inicio, data_fim')->where('empresa_id', (int) $empresa_id)->get('novidades')->result();
        foreach ($linhas as $linha) {
            $totais[$this->situacao($linha, $agora)]++;
        }
        return $totais;
    }

    public function get($id, $empresa_id)
    {
        return $this->db->where('id', (int) $id)->where('empresa_id', (int) $empresa_id)->get('novidades')->row();
    }

    public function imagens($novidade_id)
    {
        return $this->db->where('novidade_id', (int) $novidade_id)->order_by('ordem', 'asc')->order_by('id', 'asc')->get('novidades_imagens')->result();
    }

    public function criar($dados)
    {
        $dados['created'] = date('Y-m-d H:i:s');
        if (!$this->db->insert('novidades', $dados)) {
            return false;
        }
        return (int) $this->db->insert_id();
    }

    public function atualizar($id, $empresa_id, $dados)
    {
        $dados['modified'] = date('Y-m-d H:i:s');
        return $this->db->where('id', (int) $id)->where('empresa_id', (int) $empresa_id)->update('novidades', $dados);
    }

    /**
     * Exclui a novidade com imagens e leituras. Devolve os arquivos para o controller apagar do disco.
     */
    public function excluir($id, $empresa_id)
    {
        $novidade = $this->get($id, $empresa_id);
        if (!$novidade) {
            return false;
        }
        $arquivos = array();
        foreach ($this->imagens($novidade->id) as $imagem) {
            $arquivos[] = $imagem->arquivo;
        }
        $this->db->trans_start();
        $this->db->delete('novidades_imagens', array('novidade_id' => (int) $novidade->id));
        $this->db->delete('novidades_leituras', array('novidade_id' => (int) $novidade->id));
        if ($this->aceita_publico()) {
            $this->db->delete('novidades_unidades', array('novidade_id' => (int) $novidade->id));
            $this->db->delete('novidades_grupos', array('novidade_id' => (int) $novidade->id));
        }
        $this->db->delete('novidades', array('id' => (int) $novidade->id));
        $this->db->trans_complete();
        return $this->db->trans_status() ? $arquivos : false;
    }

    public function adicionar_imagem($novidade_id, $arquivo)
    {
        $ordem = (int) $this->db->select_max('ordem')->where('novidade_id', (int) $novidade_id)->get('novidades_imagens')->row()->ordem;
        return $this->db->insert('novidades_imagens', array(
            'novidade_id' => (int) $novidade_id,
            'arquivo' => $arquivo,
            'ordem' => $ordem + 1,
            'created' => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Remove as imagens indicadas da novidade e devolve os nomes dos arquivos removidos.
     */
    public function remover_imagens($novidade_id, $ids)
    {
        $ids = array_values(array_filter(array_map('intval', (array) $ids)));
        if (!$ids) {
            return array();
        }
        $imagens = $this->db->where('novidade_id', (int) $novidade_id)->where_in('id', $ids)->get('novidades_imagens')->result();
        $arquivos = array();
        foreach ($imagens as $imagem) {
            $arquivos[] = $imagem->arquivo;
        }
        if ($imagens) {
            $this->db->where('novidade_id', (int) $novidade_id)->where_in('id', $ids)->delete('novidades_imagens');
        }
        return $arquivos;
    }

    /**
     * Novidades vigentes da empresa para o usuário (respeitando unidade e grupo escolhidos),
     * separadas em não lidas (vão para o modal) e lidas (ficam no sino).
     */
    public function do_usuario($empresa_id, $user_id, $eh_admin = false)
    {
        $resultado = array('pendentes' => array(), 'lidas' => array());
        if (!$this->disponivel() || (int) $empresa_id <= 0 || (int) $user_id <= 0) {
            return $resultado;
        }

        $agora = date('Y-m-d H:i:s');
        $this->db->select('n.id, n.titulo, n.conteudo, n.data_inicio, n.data_fim, nl.lida_em')
            ->from('novidades n')
            ->join('novidades_leituras nl', 'nl.novidade_id = n.id AND nl.user_id = ' . (int) $user_id, 'left')
            ->where('n.empresa_id', (int) $empresa_id)
            ->where('n.ativo', 1)
            ->where('n.data_inicio <=', $agora)
            ->where('n.data_fim >=', $agora);
        $this->aplicar_publico('n', $user_id, $eh_admin);
        $linhas = $this->db->order_by('n.data_inicio', 'asc')
            ->order_by('n.id', 'asc')
            ->get()
            ->result();

        foreach ($linhas as $linha) {
            $resultado[$linha->lida_em ? 'lidas' : 'pendentes'][] = $linha;
        }
        // No sino, as mais recentes primeiro.
        $resultado['lidas'] = array_reverse($resultado['lidas']);
        return $resultado;
    }

    /**
     * Dados prontos para o modal: texto escapado (quebras de linha e links clicáveis), período e imagens.
     */
    public function para_exibicao($novidades)
    {
        // Carrega a biblioteca pelo nome padrão antes de usar a configuração das novidades. Se a classe fosse
        // carregada primeiro só com outro nome, o CI 3.1.5 ignoraria os $this->load->library('foto_usuario')
        // seguintes (foto do usuário no cabeçalho) e $this->foto_usuario ficaria nulo: erro 500 em todas as telas.
        $this->load->library('foto_usuario');
        $imagem_novidade = new Foto_usuario(array('pasta' => 'upload/novidades/', 'lado_miniatura' => 1600, 'miniatura_quadrada' => false));
        $lista = array();
        foreach ((array) $novidades as $novidade) {
            $imagens = array();
            foreach ($this->imagens($novidade->id) as $imagem) {
                $url = $imagem_novidade->url($imagem->arquivo);
                if ($url) {
                    $imagens[] = array('url' => $url, 'original' => $imagem_novidade->url($imagem->arquivo, false));
                }
            }
            $texto = nl2br(html_escape($novidade->conteudo));
            $texto = preg_replace('~(https?://[^\s<]+)~i', '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', $texto);
            $lista[] = array(
                'id' => (int) $novidade->id,
                'titulo' => $novidade->titulo,
                'conteudo_html' => $texto,
                'periodo' => 'Publicado em ' . date('d/m/Y', strtotime($novidade->data_inicio)),
                'imagens' => $imagens,
            );
        }
        return $lista;
    }

    /**
     * Novidade vigente da empresa que o usuário pode ver (público por unidade/grupo), ou null.
     */
    public function vigente_do_usuario($novidade_id, $empresa_id, $user_id, $eh_admin = false)
    {
        $agora = date('Y-m-d H:i:s');
        $this->db->select('n.*')
            ->from('novidades n')
            ->where('n.id', (int) $novidade_id)
            ->where('n.empresa_id', (int) $empresa_id)
            ->where('n.ativo', 1)
            ->where('n.data_inicio <=', $agora)
            ->where('n.data_fim >=', $agora);
        $this->aplicar_publico('n', $user_id, $eh_admin);
        return $this->db->get()->row();
    }

    /**
     * Registra que o usuário viu a novidade (só conta novidade vigente da empresa dele e do público dele).
     */
    public function marcar_lida($novidade_id, $empresa_id, $user_id, $eh_admin = false)
    {
        $agora = date('Y-m-d H:i:s');
        if (!$this->vigente_do_usuario($novidade_id, $empresa_id, $user_id, $eh_admin)) {
            return false;
        }
        $ja_lida = $this->db->where('novidade_id', (int) $novidade_id)->where('user_id', (int) $user_id)->count_all_results('novidades_leituras') > 0;
        if ($ja_lida) {
            return true;
        }
        return $this->db->insert('novidades_leituras', array(
            'novidade_id' => (int) $novidade_id,
            'user_id' => (int) $user_id,
            'lida_em' => $agora,
        ));
    }
}
