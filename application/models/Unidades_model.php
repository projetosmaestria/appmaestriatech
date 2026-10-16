<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unidades_model extends CI_Model {

    protected $table = 'unidades';

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
    public function get($data = array('select'=>'unidades.*', 'order_by'=>array('unidades.id', 'desc')))
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
        $existing_empresa = $this->db->get_where('empresas', array('id' => $data["empresa_id"]))->num_rows();
        if($existing_empresa == 0)
        {
            $this->set_error('empresa_not_exist');
            return FALSE;
        }
        if($data["tipo"] == "J") {
            $existing_unidade_by_cnpj = $this->db->get_where($this->table, array('cnpj' => $data["cnpj"], 'empresa_id' => $data["empresa_id"]))->num_rows();
            if($existing_unidade_by_cnpj > 0)
            {
                $this->set_error('unit_yeat_exist');
                return FALSE;
            }
        } elseif($data["tipo"] == "F") {
            $existing_unidade_by_cpf = $this->db->get_where($this->table, array('cpf' => $data["cpf"], 'empresa_id' => $data["empresa_id"]))->num_rows();
            if($existing_unidade_by_cpf > 0)
            {
                $this->set_error('unit_yeat_exist');
                return FALSE;
            }
        }

        if($this->db->insert($this->table, $data)) {
            $id = $this->db->insert_id();
            $admin_preferences = $this->db->get_where("admin_preferences", array("empresa_id"=>$data["empresa_id"]))->row();
            $users_group_admin = $this->db->distinct()->select("user_id")->get_where("users_groups", array("group_id"=>$admin_preferences->grupo_admin))->result();
            $meu_id = false;
            foreach ($users_group_admin as $value) {
                $this->db->insert("user_unidade", array("user_id"=>$value->user_id, "unidade_id"=>$id));
                if($value->user_id == $this->session->userdata('user_id'))
                    $meu_id = true;
            }
            if($meu_id == false)
                $this->db->insert("user_unidade", array("user_id"=>$this->session->userdata('user_id'),"unidade_id"=>$id));
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
    public function update($id = FALSE, $data = array())
    {
        if (empty($id)) return FALSE;

        if(@$data["tipo"] == "J") {
            $existing_unidade_by_cnpj = $this->db->get_where($this->table, "id != '".$id."' AND cnpj = '".$data["cnpj"]."' AND empresa_id = '".$data["empresa_id"]."'")->num_rows();
            if($existing_unidade_by_cnpj > 0)
            {
                $this->set_error('unit_yeat_exist');
                return FALSE;
            }
        } elseif(@$data["tipo"] == "F") {
            $existing_unidade_by_cpf = $this->db->get_where($this->table, "id != '".$id."' AND cpf = '".$data["cpf"]."' AND empresa_id = '".$data["empresa_id"]."'")->num_rows();
            if($existing_unidade_by_cpf > 0)
            {
                $this->set_error('unit_yeat_exist');
                return FALSE;
            }
        }

        $this->db->update($this->table, $data, array('id' => $id));

        $this->set_message('update_successful');

        return TRUE;
    }

    public function config_update($id = FALSE, $data = array()) {
        if (empty($id)) return FALSE;

        $this->db->update('parametro', $data, array('LIG_ID_FILIAL' => $id));

        return TRUE;
    }

    public function config_create($data = array())
    {
        if($this->db->insert('parametro', $data)) {
            $id = $this->db->insert_id();
        }

        return $id;
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

    public function get_groups($user_id = null, $empresa_id = null) {
        $query = $this->db->select('g.*');
        if($user_id != null) {
            $query->join('users_groups ug', 'ug.group_id = g.id');
            $query->where('ug.user_id', $user_id);
        }
        if($empresa_id != null) {
            $query->where('g.empresa_id', $empresa_id);
        } else {
            $query->where('g.empresa_id', $this->session->userdata('empresa_id'));
        }
        
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

    public function get_config_contrato($id) {
        return $this->db->get_where('parametro', array('LIG_ID_FILIAL'=>$id));
    }

    public function get_status($id) {
        return $this->db->order_by('descricao', 'asc')->get_where('cad_status', array("empresa_id" => $id));
    }

    public function get_historicos_status($id) {
        return $this->db->order_by('descricao', 'asc')->get_where('users_historicos_status', array("empresa_id" => $id));
    }

    public function get_caracteristicas() {
        return $this->db->order_by('descricao', 'asc')->get_where('unid_carac', array("empresa_id" => $this->session->userdata('empresa_id')));
    }

    public function activate($id = NULL)
    {
        if (!isset($id))
        {
            $this->set_error('activate_unsuccessful');
            return FALSE;
        }

        $data = array(
            'actived' => 's'
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
            'actived' => 'n'
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

    public function extender_token($app_id, $app_secret) {
        $fb = new Facebook\Facebook([
            'app_id' => $app_id,
            'app_secret' => $app_secret,
            'default_graph_version' => 'v2.10',
        ]);

        $retorno = array();
        try {
            $helper = $fb->getJavaScriptHelper();
        }catch(Facebook\Exceptions\FacebookSDKException $e) {
            $retorno["erro"] = true;
            $retorno["msg"] = $e->getMessage();
            return $retorno;
        }

        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $retorno["erro"] = true;
            $retorno["msg"] = $e->getMessage();
            return $retorno;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $retorno["erro"] = true;
            $retorno["msg"] = $e->getMessage();
            return $retorno;
        }

        if (! isset($accessToken)) {
            $retorno["erro"] = true;
            $retorno["msg"] = 'No cookie set or no OAuth data could be obtained from cookie.';
            return $retorno;
        }

        $oAuth2Client = $fb->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $tokenMetadata->validateAppId($_POST["app_id"]);
        $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                $retorno["erro"] = true;
                $retorno["msg"] = "Error getting long-lived access token: " . $e->getMessage();
                return $retorno;
            }
        }

        if(!isset($retorno["erro"])) {
            $retorno["erro"] = false;
            $retorno['token'] = $accessToken->getValue();
            $retorno['expires_at'] = ($accessToken->getExpiresAt()) ? $accessToken->getExpiresAt()->format('Y-m-d H:i:s') : '';
            
            $data = array('fb_access_token'=>$accessToken->getValue(), 'fb_expires_at'=>$accessToken->getExpiresAt()->format('Y-m-d H:i:s'));
            $this->db->update($this->table, $data, array('fb_app_id'=>$app_id, 'fb_app_secret'=>$app_secret));
        }

        return $retorno;
    }

    public function get_midias() {        
        return $this->db->get_where('midias', array("empresa_id"=>$this->session->userdata("empresa_id")));
    }

    public function get_eventos($midia_id,$unidade_id) {        
        return $this->db->get_where('eventos', array("midia_id"=>$midia_id, "unidade_id"=>$unidade_id));
    }

    public function update_by_fb_app_id($app_id = FALSE, $data = array())
    {
        if (empty($app_id)) return FALSE;

        $this->db->update($this->table, $data, array('fb_app_id' => $app_id));

        return TRUE;
    }

    public function get_recebimentos($filial_id)
    {
        return $this->db->select('*')
            ->where('FILIAL', $filial_id)
            ->get("tip_rece");
    }
}
