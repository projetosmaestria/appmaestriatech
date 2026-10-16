<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produtos_modulos_model extends CI_Model {

    protected $table = 'modulos';

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
    public function get($data = array('select'=>'modulos.*', 'order_by'=>array('modulos.id', 'desc')))
    {
        $query = $this->db->select($data['select']);
        if(@$data['join']) {
            foreach ($data['join'] as $join) {
                $query->join($join[0], $join[1]);
            }
        }
        if(@$data['where']) {
            $query->where($data['where']);
        }
        $query->order_by($data['order_by'][0], $data['order_by'][1]);
        return $query->get($this->table);
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        if($this->db->insert($this->table, $data)) {
            $id = $this->db->insert_id();
            $this->set_message('creation_modulos_successful');
        } else {
            $this->set_error('creation_modulos_unsuccessful');
            return FALSE;
        }
        return $id;
    }

    /**
     * update
     *
     * @return bool
     * @author Ellan Garbelini
     **/
    public function update($id = FALSE, $data = array())
    {
        if (empty($id)) return FALSE;

        $this->db->update($this->table, $data, array('id' => $id));

        $this->set_message('update_successful');

        return TRUE;
    }

    public function activate($id = NULL)
    {
        if (!isset($id))
        {
            $this->set_error('activate_unsuccessful');
            return FALSE;
        }

        $data = array(
            'status' => 'S'
        );

        $this->db->update($this->table, $data, array('id' => $id));

        $return = $this->db->affected_rows() == 1;
        if ($return){
            $this->set_message('activate_successful');
        } else{
            $this->set_error('activate_unsuccessful');
        }
        return $return;
    }


    public function deactivate($id = NULL)
    {
        if (!isset($id))
        {
            $this->set_error('deactivate_unsuccessful');
            return FALSE;
        }

        $data = array(
            'status' => 'N'
        );

        $this->db->update($this->table, $data, array('id' => $id));

        $return = $this->db->affected_rows() == 1;
        if ($return)
            $this->set_message('deactivate_successful');
        else
            $this->set_error('deactivate_unsuccessful');

        return $return;
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

    public function create_habilitacoes_produto($produto_id, $modulo_id) {
        $unidades = $this->db->get_where('unidades', array('empresa_id' => $this->session->userdata('empresa_id')));
        if($unidades->num_rows() > 0) {
            foreach ($unidades->result() as $unid) {
                $check_hb_prod = $this->db->get_where('habilitacoes_produto', array('id_produto' => $produto_id, 'id_cliente' => $unid->id));
                if($check_hb_prod->num_rows() == 0) {
                   $this->db->insert('habilitacoes_produto', array('id_produto'=>$produto_id, 'id_cliente'=>$unid->id, 'periodo_inicio'=>date('Y-m-d'), 'periodo_fim'=>date('Y-m-d'), 'data_cadastro'=>date('Y-m-d H:i:s'), 'cadastrado_por'=>$this->session->userdata('user_id'), 'status'=>'S', 'demonstracao'=>'S'));
                   $id_hb_prod = $this->db->insert_id();
                   $this->create_habilitacoes_modulo($id_hb_prod, $modulo_id);
                } else {
                    $this->create_habilitacoes_modulo($check_hb_prod->row()->id, $modulo_id);
                }
            }
        }
    }

    public function create_habilitacoes_modulo($habilitacao_id, $modulo_id) {
        $this->db->insert('habilitacoes_modulo', array('id_habilitacao'=>$habilitacao_id, 'id_modulo'=>$modulo_id, 'data_cadastro'=>date('Y-m-d H:i:s'), 'cadastrado_por'=>$this->session->userdata('user_id'), 'status'=>'L', 'usuarios'=>'10'));
    }
}
