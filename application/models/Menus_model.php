<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Menus_model extends CI_Model {

    protected $table = 'menus';

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
    public function get($data = array('select'=>'m.*', 'order_by'=>'m.id desc'))
    {
        $query = $this->db->select($data['select']);
        if(@$data['join']) {
            foreach ($data['join'] as $join) {
                $query->join($join[0], $join[1], $join[2]);
            }
        }
        if(@$data['where']) {
            $query->where($data['where']);
        }
        $query->order_by($data['order_by']);
        return $query->get($this->table.' m');
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        if($data['nome'] == "")
        {
            $this->set_error('nome_required');
            return FALSE;
        }

        // bail if the group name already exists
        $existing = $this->db->get_where($this->table, array('nome' => $data['nome'], 'pai_id' => $data['pai_id'], 'controlador' => $data['controlador'], 'metodo' => $data['metodo']))->num_rows();
        //exit("<pre>".print_r($this->db->last_query(), true)."</pre>");
        if($existing !== 0)
        {
            $this->set_error('already_exists');
            return FALSE;
        }

        if($data["pai_id"] != null) {
            $existing_pai = $this->db->get_where($this->table, array('id' => $data['pai_id']))->num_rows();
            if($existing_pai == 0)
            {
                $this->set_error('pai_not_exist');
                return FALSE;
            }
        }

        $dados = array('nome'=>$data['nome'],'tipo'=>$data['tipo'],'icon_class'=>$data['icon_class'],'pai_id'=>$data['pai_id'],'controlador'=>$data['controlador'],'metodo'=>$data['metodo'],'order'=>$data['order'],'ativo'=>$data['ativo'],'create_on'=>time());

        $this->db->insert($this->table, $dados);
        $id = $this->db->insert_id();

        if (!empty($data['empresas']))
        {
            // add to empresas
            $this->add_to_emp($data['empresas'], $id);
        }

        if (!empty($data['modulos']))
        {
            // add to empresas
            $this->add_to_modulo($data['modulos'], $id);
        }

        $this->set_message('creation_successful');

        return $id;
    }

    /**
     * update
     *
     * @return bool
     * @author Ellan Garbelini
     **/
    public function update($id = FALSE, $data = array(), $empresas = array())
    {
        if (empty($id)) return FALSE;

        if (!empty($nome))
        {
            $existing = $this->db->get_where($this->table, array('nome' => $data['nome']))->row();
            if(isset($existing->id) && $existing->id != $id)
            {
                $this->set_error('already_exists');
                return FALSE;
            }
        }

        $this->db->update($this->table, $data, array('id' => $id));

        if($this->remove_from_emp('', $id)) {
            if (!empty($empresas))
            {
                // add to empresas
                $this->add_to_emp($empresas, $id);
            }
        }

        

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
            'ativo' => '1'
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
            'ativo' => '0'
        );

        $this->db->update($this->table, $data, array('id' => $id));

        $return = $this->db->affected_rows() == 1;
        if ($return)
            $this->set_message('deactivate_successful');
        else
            $this->set_error('deactivate_unsuccessful');

        return $return;
    }

    /**
     * add_to_emp
     *
     * @return bool
     * @author Ellan Garbelini
     **/
    public function add_to_emp($emp_ids, $menu_id=false)
    {
        if(!is_array($emp_ids))
        {
            $emp_ids = array($emp_ids);
        }

        $return = 0;

        foreach ($emp_ids as $emp_id)
        {           
            if ($this->db->insert('menus_empresas', array( 'menus_id' => (float)$menu_id, 'empresas_id' => (float)$emp_id)))
            {
                // Return the number of groups added
                $return += 1;
            }
        }

        return $return;
    }

    public function add_to_modulo($modulos_ids, $menu_id=false)
    {
        if(!is_array($modulos_ids))
        {
            $modulos_ids = array($modulos_ids);
        }

        $return = 0;

        foreach ($modulos_ids as $modulo_id)
        {           
            if ($this->db->insert('menus_modulos', array( 'menu_id' => (float)$menu_id, 'modulo_id' => (float)$modulo_id)))
            {
                // Return the number of groups added
                $return += 1;
            }
        }

        return $return;
    }

    /**
     * remove_from_emp
     *
     * @return bool
     * @author Ellan Garbelini
     **/
    public function remove_from_emp($emp_ids=false, $menu_id=false)
    {
        if(empty($menu_id))
        {
            return FALSE;
        }

        if( ! empty($emp_ids))
        {
            if(!is_array($emp_ids))
            {
                $emp_ids = array($emp_ids);
            }

            foreach($emp_ids as $emp_id)
            {
                $this->db->delete('menus_empresas', array('menus_id' => (float)$menu_id, 'empresas_id' => (float)$emp_id));
            }

            $return = TRUE;
        }

        else
        {
            $return = $this->db->delete('menus_empresas', array('menus_id' => (float)$menu_id));
        }
        return $return;
    }

    public function remove_from_modulo($modulos_ids=false, $menu_id=false)
    {
        if(empty($menu_id))
        {
            return FALSE;
        }

        if( ! empty($modulos_ids))
        {
            if(!is_array($modulos_ids))
            {
                $modulos_ids = array($modulos_ids);
            }

            foreach($modulos_ids as $modulo_id)
            {
                $this->db->delete('menus_modulos', array('menu_id' => (float)$menu_id, 'modulo_id' => (float)$modulo_id));
            }

            $return = TRUE;
        }

        else
        {
            $return = $this->db->delete('menus_modulos', array('menu_id' => (float)$menu_id));
        }
        return $return;
    }

    /**
     * get_menus_empresas
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_menus_empresas($menu_id = null)
    {
        return $this->db->select('empresas_id')
            ->where('menus_id', $menu_id)
            ->get('menus_empresas');
    }

    public function get_menus_modulos($menu_id = null)
    {
        return $this->db->select('modulo_id')
            ->where('menu_id', $menu_id)
            ->get('menus_modulos');
    }

    /**
     * get_empresas
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_empresas()
    {
        $query = $this->db->select('*');
        $query->order_by('nome', 'asc');
        return $query->get('empresas');
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
