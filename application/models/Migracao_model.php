<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migracao_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        
    }

    /**
     * create_contrato
     *
     * @author Ellan Garbelini
    */
    public function create_contrato($data = array())
    {
        $this->db->insert('cad_orca', $data);
        $id = $this->db->insert_id();

        return $id;
    }

    /**
     * create_user
     *
     * @author Ellan Garbelini
    */
    public function create_user($data = array())
    {
        $existing = $this->db->get_where('users', array('email' => $data["email"]))->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }

        $this->db->insert('users', $data);
        $id = $this->db->insert_id();

        return $id;
    }

    /**
     * create_pessoa
     *
     * @author Ellan Garbelini
    */
    public function create_pessoa($data = array())
    {
        $existing = $this->db->get_where('pessoas', array('user_id'=>$data['user_id']))->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }
        $this->db->insert('pessoas', $data);
        $id = $this->db->insert_id();

        return $id;
    }

    /**
     * create_user_empresa
     *
     * @author Ellan Garbelini
    */
    public function create_user_empresa($data = array())
    {
        $existing = $this->db->get_where('empresas_users', $data)->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }
        $this->db->insert('empresas_users', $data);
        $id = $this->db->insert_id();

        return $id;
    }

    /**
     * create_user_group
     *
     * @author Ellan Garbelini
    */
    public function create_user_group($data = array())
    {
        $existing = $this->db->get_where('users_groups', $data)->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }
        $this->db->insert('users_groups', $data);
        $id = $this->db->insert_id();

        return $id;
    }

    /**
     * create_user_unid
     *
     * @author Ellan Garbelini
    */
    public function create_user_unid($data = array())
    {
        $existing = $this->db->get_where('user_unidade', $data)->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }
        $this->db->insert('user_unidade', $data);
        $id = $this->db->insert_id();

        return $id;
    }

    public function create_qrcode_info($data = array())
    {
        // $existing = $this->db->get_where('users_groups', $data)->num_rows();
        // if($existing !== 0)
        // {
        //     return FALSE;
        // }
        $this->db->insert('qrcode_info', $data);
        $id = $this->db->insert_id();

        return $id;
    }

    public function create_qrcode($data = array())
    {
        $existing = $this->db->get_where('qrcodes', array("codigo"=>$data["codigo"]))->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }
        $this->db->insert('qrcodes', $data);
        $id = $this->db->insert_id();

        return $id;
    }

    public function get_aluno($email) {
        return $this->db->get_where('users', array("email"=>$email))->row();
    }

    public function get_unidade($cnpj) {
        return $this->db->get_where('unidades', array("cnpj"=>$cnpj, "empresa_id"=>3))->row();
    }

    public function get_turmas($unidade_id) {
        return $this->db->get_where('turma', array("unidade"=>$unidade_id))->result();
    }

    public function get_turma_by_migracao($id) {
        return $this->db->get_where('turma', array("ID_MIGRACAO"=>$id))->row();
    }

    public function insert_aluno_unid($data) {
        $existing = $this->db->get_where('user_unidade', $data)->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }
        return $this->db->insert('user_unidade', $data);
    }

    public function insert_aluno_turma($data) {
        $existing = $this->db->get_where('turma_aluno', $data)->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }
        return $this->db->insert('turma_aluno', $data);
    }

    public function insert_aluno_livro($data) {
        $existing = $this->db->get_where('aluno_livro', $data)->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }
        return $this->db->insert('aluno_livro', $data);
    }

    public function insert_resposta_aluno($data) {
        $this->db->insert('resposta_aluno', $data);
        $id = $this->db->insert_id();
        return $id;
    }

    public function insert_resposta_correcao($data) {
        $this->db->insert('resposta_aluno_correcao', $data);
        $id = $this->db->insert_id();
        return $id;
    }

    /**
     * create_turma
     *
     * @author Ellan Garbelini
    */
    public function create_turma($data = array())
    {
        /*$existing = $this->db->get_where('turma', array('email' => $data["email"]))->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }*/

        $this->db->insert('turma', $data);
        $id = $this->db->insert_id();

        return $id;
    }

    /**
     * create_turma_horario
     *
     * @author Ellan Garbelini
    */
    public function create_turma_horario($data = array())
    {
        /*$existing = $this->db->get_where('turma', array('email' => $data["email"]))->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }*/

        $this->db->insert('turma_horario', $data);
        $id = $this->db->insert_id();

        return $id;
    }

    /**
     * create_unidade
     *
     * @author Ellan Garbelini
    */
    public function create_unidade($data = array())
    {
        $existing = $this->db->get_where('unidades', array('cnpj' => $data["cnpj"], 'empresa_id' => $data["empresa_id"]))->num_rows();
        if($existing !== 0)
        {
            return FALSE;
        }

        $this->db->insert('unidades', $data);
        $id = $this->db->insert_id();

        return $id;
    }

    public function is_professor($id) {
        $existing = $this->db->get_where('users_groups', array('user_id' => $id, 'group_id' => 25))->num_rows();
        if($existing !== 0)
            return true;
        else
            return false;
    }
}
