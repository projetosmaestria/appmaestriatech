<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditorias_model extends CI_Model {

    protected $table = 'auditorias';

    protected $messages;

    protected $errors;

    public function __construct()
    {
        parent::__construct();
        $this->messages    = array();
        $this->errors      = array();
    }

    /**
     * get
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get()
    {
        $query = $this->db->select('a.*, u.first_name, u.last_name');
        $query->join('users u', 'u.id = a.user_id');
        $query->where("a.empresa_id", $this->session->userdata('empresa_id'));
        $query->order_by("a.id", "desc");
        return $query->get($this->table.' a');
    }

    /**
     * Filtros da listagem paginada. $permissoes = null mostra tudo (administrador); senão, só registros
     * de telas que o usuário acessa (index) ou de ações que ele pode executar.
     */
    private function filtros_lista($filtros, $permissoes)
    {
        $this->db->from($this->table . ' a');
        $this->db->join('users u', 'u.id = a.user_id', 'left');
        $this->db->where('a.empresa_id', (int) $this->session->userdata('empresa_id'));

        if ($permissoes !== null) {
            $condicoes = array();
            foreach ($permissoes as $controlador => $metodos) {
                if (!is_array($metodos) || !$metodos) {
                    continue;
                }
                $controlador = $this->db->escape($controlador);
                if (in_array('index', $metodos)) {
                    $condicoes[] = 'a.controlador = ' . $controlador;
                } else {
                    $condicoes[] = '(a.controlador = ' . $controlador . ' AND a.metodo IN (' . implode(',', array_map(array($this->db, 'escape'), $metodos)) . '))';
                }
            }
            $this->db->where($condicoes ? '(' . implode(' OR ', $condicoes) . ')' : '1 = 0', null, false);
        }

        if (!empty($filtros['inicio'])) {
            $this->db->where('a.created_at >=', $filtros['inicio'] . ' 00:00:00');
        }
        if (!empty($filtros['fim'])) {
            $this->db->where('a.created_at <=', $filtros['fim'] . ' 23:59:59');
        }
        if (!empty($filtros['usuario'])) {
            $this->db->where('a.user_id', (int) $filtros['usuario']);
        }
        if (!empty($filtros['controlador'])) {
            $this->db->where('a.controlador', $filtros['controlador']);
        }
        if (!empty($filtros['metodo'])) {
            $this->db->where('a.metodo', $filtros['metodo']);
        }
        if (!empty($filtros['q'])) {
            $this->db->like('a.obs', $filtros['q']);
        }
    }

    public function lista($filtros, $permissoes, $inicio, $quantidade, $direcao)
    {
        $this->db->select("a.id, a.user_id, a.controlador, a.metodo, a.obs, a.created_at, CONCAT_WS(' ', u.first_name, u.last_name) AS usuario, u.email", false);
        $this->filtros_lista($filtros, $permissoes);
        $this->db->order_by('a.id', $direcao === 'asc' ? 'ASC' : 'DESC');
        $this->db->limit((int) $quantidade, (int) $inicio);
        return $this->db->get()->result();
    }

    public function total($filtros, $permissoes)
    {
        $this->filtros_lista($filtros, $permissoes);
        return (int) $this->db->count_all_results();
    }

    public function resumo($filtros, $permissoes)
    {
        $this->db->select('COUNT(*) AS registros, COUNT(DISTINCT a.user_id) AS usuarios, COUNT(DISTINCT a.controlador) AS telas', false);
        $this->filtros_lista($filtros, $permissoes);
        return $this->db->get()->row();
    }

    // Opções dos filtros: usuários, telas e ações que aparecem nos registros da empresa.
    public function opcoes_filtro()
    {
        $empresa_id = (int) $this->session->userdata('empresa_id');
        return array(
            'usuarios' => $this->db->query("SELECT DISTINCT a.user_id AS id, CONCAT_WS(' ', u.first_name, u.last_name) AS nome
                FROM auditorias a JOIN users u ON u.id = a.user_id WHERE a.empresa_id = ? ORDER BY nome", array($empresa_id))->result(),
            'acoes' => $this->db->query("SELECT a.controlador, a.metodo, COUNT(*) AS total FROM auditorias a
                WHERE a.empresa_id = ? AND a.controlador IS NOT NULL GROUP BY a.controlador, a.metodo ORDER BY a.controlador, a.metodo", array($empresa_id))->result(),
        );
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($controlador, $metodo, $obs)
    {
        if($controlador == "")
        {
            $this->set_error('controlador_required');
            return FALSE;
        }
        if($metodo == "")
        {
            $this->set_error('metodo_required');
            return FALSE;
        }

        $this->db->insert($this->table, array("controlador"=>$controlador,"metodo"=>$metodo,"obs"=>$obs,"empresa_id"=>$this->session->userdata("empresa_id"),"user_id"=>$this->session->userdata("user_id")));
        $id = $this->db->insert_id();

        $this->set_message('creation_successful');

        return $id;
    }

    public function historico_create($data = array())
    {
        $data["empresa_id"] = $this->session->userdata('empresa_id');
        if($this->db->insert('cad_clie_auditoria', $data)) {
            $id = $this->db->insert_id();
            $this->set_message('creation_historico_successful');
        } else {
            $this->set_error('creation_historico_unsuccessful');
            return FALSE;
        }
        return $id;
    }

    public function set_message($message)
    {
        $this->messages[] = $message;

        return $message;
    }

    public function messages()
    {
        $_output = '';
        foreach ($this->messages as $message)
        {
            $messageLang = $this->lang->line($message) ? $this->lang->line($message) : '##' . $message . '##';
            $_output .= $messageLang;
        }

        return $_output;
    }

    public function set_error($error)
    {
        $this->errors[] = $error;

        return $error;
    }

    public function errors()
    {
        $_output = '';
        foreach ($this->errors as $error)
        {
            $errorLang = $this->lang->line($error) ? $this->lang->line($error) : '##' . $error . '##';
            $_output .= $errorLang;
        }

        return $_output;
    }
}
