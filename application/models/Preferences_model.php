<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Preferences_model extends CI_Model {


    public function __construct()

    {

        parent::__construct();

    }



    public function get_interface($table)

    {

        $query = $this->db->get_where($table, array("empresa_id"=>$this->session->userdata('empresa_id')));


        if ($query->num_rows() > 0)

        {

            return $query->row();

        }

        else

        {

            return FALSE;

        }

    }



    public function update_interfaces($table, $data)

    {

        $where = "empresa_id = ".$this->session->userdata('empresa_id');


        return $this->db->update($table, $data, $where);

    }


    public function get_groups($user_id = null) {

        $query = $this->db->select('g.*');

        if($user_id != null) {

            $query->join('users_groups ug', 'ug.group_id = g.id');

            $query->where('ug.user_id', $user_id);

        }

        $query->where('g.empresa_id', $this->session->userdata('empresa_id'));

        $query = $query->get('groups g');


        if ($query->num_rows() > 0)

        {

            $return_array = [];

            foreach ($query->result() as $group) {

                $return_array[$group->id] = $group->description;

            }

            return $return_array;

        }

        else

        {

            return FALSE;

        }

    }


    public function get_cats() {

        $query = $this->db->select('p.id as p_id, p.nome as p_nome, m.id as m_id, m.nome as m_nome, c.id as c_id, c.nome as c_nome');

        $query->join('modulos m', 'm.id = c.id_modulo');

        $query->join('produtos p', 'p.id = m.id_produto');

        $query->where('p.empresa_id', $this->session->userdata('empresa_id'));

        $query->order_by('p.nome asc, m.nome asc, c.nome asc');

        $query = $query->get('modulos_categoria c');


        if ($query->num_rows() > 0)

        {

            $return_array = [];

            foreach ($query->result() as $cat) {

                $return_array[$cat->c_id] = $cat->p_nome." > ".$cat->m_nome." > ".$cat->c_nome;

            }

            return $return_array;

        }

        else

        {

            return FALSE;

        }

    }


    public function get_email_config()

    {

        $query = $this->db->get_where('config_email', array("empresa_id"=>$this->session->userdata('empresa_id')));


        if ($query->num_rows() > 0)

        {

            return $query->row();

        }

        else

        {

            return FALSE;

        }

    }


    public function update_config_email($data)

    {

        $where = "empresa_id = ".$this->session->userdata('empresa_id');


        return $this->db->update('config_email', $data, $where);

    }

	public function get_unidades() {
        $unids = array("" => "Selecione");

        $query_unids = $this->db->select('u.*')
            ->join('unidades u', 'u.id = uu.unidade_id')
            ->where('u.empresa_id', $this->session->userdata('empresa_id'))
            ->where('uu.user_id', $this->session->userdata('user_id'))
            ->get('user_unidade uu');
        foreach ($query_unids->result() as $value) {
            $unids[$value->id] = $value->unidade;
        }

        return $unids;
    }

}
