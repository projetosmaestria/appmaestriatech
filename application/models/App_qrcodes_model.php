<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_qrcodes_model extends CI_Model {

    protected $table = 'qrcodes';

    protected $messages;

    protected $errors;

    public function __construct()
    {
        parent::__construct();
        $this->messages    = array();
        $this->errors      = array();
        $this->load->helper('functions_helper');
    }

    /**
     * get
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get($id = null)
    {
        $query = $this->db->select('MIN(qr.id) as min_id, MAX(qr.id) as max_id, qi.id, qi.livro, qi.qtd, qi.kit, qi.usuario, qi.observacao, DATE_FORMAT(qi.data_cadastro, "%d/%m/%Y %H:%i") as data, l.titulo, u.first_name, u.last_name, l.empresa, qi.data_cadastro')
            ->join('qrcodes qr', 'qr.qrcode_info = qi.id')
            ->join('livros l', 'qr.livro = l.id', 'left')
            ->join('users u', 'qi.usuario = u.id');
        if($id != null) {
            $query->where('qi.id', $id);
        }
        $query->where('l.empresa', $this->session->userdata('empresa_id'));
        $query->group_by('qi.id');
        return $query->get('qrcode_info qi');
    }

    /**
     * resgatar_qrcodes
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function resgatar_qrcodes($id_info = null)
    {
        $query = $this->db->select('qr.id, qi.qtd, DATE_FORMAT(qi.data_cadastro, "%d/%m/%Y %H:%i") as data, qr.codigo, l.titulo, al.aluno, l.empresa, e.nome as nome_empresa, u.first_name, u.last_name')
            ->join('qrcodes qr', 'qr.qrcode_info = qi.id')
            ->join('livros l', 'qr.livro = l.id', 'left')
            ->join('aluno_livro al', 'qr.codigo = al.qrcode', 'left')
            ->join('empresas e', 'e.id = l.empresa', 'left')
            ->join('users u', 'u.id = al.aluno', 'left');
        if($id_info != null) {
            $query->where('qi.id', $id_info);
        }
        return $query->get('qrcode_info qi');
    }

    public function get_qrcode($id = null)
    {
        return $this->db->select('qr.id, qr.codigo, DATE_FORMAT(qr.data_cadastro, "%d/%m/%Y %H:%i") as data, l.titulo, u.first_name, u.last_name')
            ->join('livros l', 'qr.livro = l.id')
            ->join('qrcode_info qi', 'qr.qrcode_info = qi.id')
            ->join('users u', 'qi.usuario = u.id')
            ->where('qr.id', $id)
            ->get($this->table." qr")->row();
    }

    public function get_status_historicos()
    {
        return $this->db->select('*')
            ->where('empresa_id', $this->session->userdata('empresa_id'))
            ->get("users_historicos_status");
    }

    public function pesquisar_qrcode() {
        return $this->db->select('qr.id, qr.codigo, qr.livro, DATE_FORMAT(qr.data_cadastro, "%d/%m/%Y %H:%i") as data, l.titulo, u.first_name, u.last_name, e.nome as empresa, u.id as alunodevice, al.qrcode as qrcode')
            ->join('livros l', 'qr.livro = l.id', 'left')
            ->join('aluno_livro al', 'al.qrcode = qr.codigo', 'left')
            ->join('users u', 'al.aluno = u.id', 'left')
            ->join('empresas e', 'e.id = l.empresa', 'left')
            ->where('l.empresa', $this->session->userdata('empresa_id'))
            ->group_by('qr.id')
            ->get($this->table." qr");
    }

    public function has_vinculo_qrcode($id) {
        if($id == null)
            return false;

        $res = $this->db->select('*')
            ->where('id', $id)
            ->get($this->table);

        if($res->num_rows() > 0) {
            $res2 = $this->db->select('*')
                ->where('qrcode', $res->row()->codigo)
                ->get("aluno_livro");
            if($res2->num_rows() > 0) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function reset_qrcode($id, $status) {
        if($id == null)
            return false;

        $res = $this->db->select('*')
            ->where('id', $id)
            ->get($this->table);

        if($res->num_rows() > 0) {
            $res2 = $this->db->select('*')
                ->where('qrcode', $res->row()->codigo)
                ->get("aluno_livro");
            if($res2->num_rows() > 0) {
                foreach ($res2->result() as $value) {
                    $this->db->insert("auditorias", array("user_id" => $this->session->userdata('user_id'), "empresa_id" => $this->session->userdata('empresa_id'), "controlador" => "App_qrcodes", "metodo" => "reset_qrcode", "obs" => "Reset do QRCode '".$res->row()->codigo."' ao aluno '".$res2->row()->aluno."'.", "created_at" => date("Y-m-d H:i:s")));
                    $this->db->insert("cad_clie_auditoria", array("LIG_ID_USUARIO" => $this->session->userdata('user_id'), "LIG_ID_CLIENTE" => $res2->row()->aluno, "empresa_id" => $this->session->userdata('empresa_id'), "STATUS" => $status, "REGISTRO" => "True", "MENSAGEM" => "Reset do QRCode '".$res->row()->codigo."' ao aluno '".$res2->row()->aluno."'.", "DATA_REGISTRO" => date("Y-m-d"), "HORA_REGISTRO" => date("H:i:s")));
                    $this->db->delete('aluno_livro', array('qrcode' => $res->row()->codigo));
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    function allserver_side_count()
    {   
        $query = $this
                ->db
                ->join('livros l', 'qr.livro = l.id', 'left')
                ->join('aluno_livro al', 'al.qrcode = qr.codigo', 'left')
                ->join('users u', 'al.aluno = u.id', 'left')
                ->join('empresas e', 'e.id = l.empresa', 'left')
                ->where('l.empresa', $this->session->userdata('empresa_id'))
                ->get($this->table." qr");
    
        return $query->num_rows();  

    }
    
    function allserver_side($limit,$start,$col,$dir)
    {   
       $query = $this
                ->db
                ->select('qr.id, qr.codigo, qr.livro, qr.data_cadastro, DATE_FORMAT(qr.data_cadastro, "%d/%m/%Y %H:%i") as data, l.titulo, u.first_name, u.last_name, u.email, e.nome as empresa, u.id as alunodevice, al.qrcode as qrcode')
                ->join('livros l', 'qr.livro = l.id', 'left')
                ->join('aluno_livro al', 'al.qrcode = qr.codigo', 'left')
                ->join('users u', 'al.aluno = u.id', 'left')
                ->join('empresas e', 'e.id = l.empresa', 'left')
                ->where('l.empresa', $this->session->userdata('empresa_id'))
                ->limit($limit,$start)
                ->order_by($col,$dir)
                ->get($this->table." qr");
        
        if($query->num_rows()>0)
        {
            return $query->result(); 
        }
        else
        {
            return null;
        }
        
    }
   
    function server_side_search($limit,$start,$search,$col,$dir)
    {
        $query = $this
                ->db
                ->select('qr.id, qr.codigo, qr.livro, qr.data_cadastro, DATE_FORMAT(qr.data_cadastro, "%d/%m/%Y %H:%i") as data, l.titulo, u.first_name, u.last_name, e.nome as empresa, u.email, u.id as alunodevice, al.qrcode as qrcode')
                ->join('livros l', 'qr.livro = l.id', 'left')
                ->join('aluno_livro al', 'al.qrcode = qr.codigo', 'left')
                ->join('users u', 'al.aluno = u.id', 'left')
                ->join('empresas e', 'e.id = l.empresa', 'left')
                ->where('(l.empresa = '.$this->session->userdata('empresa_id').') AND (qr.codigo LIKE "%'.$search.'%" OR l.titulo = "%'.$search.'%" OR qr.data_cadastro LIKE "%'.date('Y-m-d', strtotime(str_replace('/', '-', $search))).'%" OR u.email LIKE "%'.$search.'%" OR CONCAT(u.first_name, " ", u.last_name) LIKE "%'.$search.'%")')
                ->limit($limit,$start)
                ->order_by($col,$dir)
                ->get($this->table." qr");

        if($query->num_rows()>0)
        {
            return $query->result();  
        }
        else
        {
            return null;
        }
    }

    function server_side_search_count($search)
    {
        $query = $this
                ->db
                ->join('livros l', 'qr.livro = l.id', 'left')
                ->join('aluno_livro al', 'al.qrcode = qr.codigo', 'left')
                ->join('users u', 'al.aluno = u.id', 'left')
                ->join('empresas e', 'e.id = l.empresa', 'left')
                ->where('(l.empresa = '.$this->session->userdata('empresa_id').') AND (qr.codigo LIKE "%'.$search.'%" OR l.titulo = "%'.$search.'%" OR qr.data_cadastro LIKE "%'.date('Y-m-d', strtotime(str_replace('/', '-', $search))).'%" OR u.email LIKE "%'.$search.'%" OR CONCAT(u.first_name, " ", u.last_name) LIKE "%'.$search.'%")')
                ->get($this->table." qr");
    
        return $query->num_rows();
    } 

    /**
     * get_livros
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_livros()
    {
        $query = $this->db->select('*');
        $query->where('empresa', $this->session->userdata('empresa_id'));
        return $query->get('livros');
    }

    /**
     * get_empresa
     *
     * @return array
     * @author Ellan Garbelini
     **/
    public function get_empresa($id)
    {
        $query = $this->db->select('*');
        $query->where('id', $id);
        return $query->get('empresas');
    }

    /**
     * create
     *
     * @author Ellan Garbelini
    */
    public function create($data = array())
    {
        try {
            $qtd = $data['qtd'];
            unset($data['qtd']);
            unset($data['observacao']);
            if($data['livro'] == "0-new") {
                for($count = 0;$count < $qtd;$count++) {
                    foreach ($this->get_livros()->result() as $livro) {
                        if (strpos($livro->titulo, 'New') !== false && trim($livro->titulo) != "Book Evolution - New") {
                            $data['codigo'] = $this->generateCodigo(16);
                            $data['sincronizado'] = 'N';
                            $data['livro'] = $livro->id;
                            $this->db->insert($this->table, $data);
                        }
                    }
                }
            } elseif($data['livro'] == "0-antigo") {
                for($count = 0;$count < $qtd;$count++) {
                    foreach ($this->get_livros()->result() as $livro) {
                        if (strpos($livro->titulo, 'New') === false) {
                            $data['codigo'] = $this->generateCodigo(16);
                            $data['sincronizado'] = 'N';
                            $data['livro'] = $livro->id;
                            $this->db->insert($this->table, $data);
                        }
                    }
                }
            } elseif($data['livro'] == "0-videoaula") {
                for($count = 0;$count < $qtd;$count++) {
                    foreach ($this->get_livros()->result() as $livro) {
                        if (strpos($livro->titulo, 'Videoaulas') !== false) {
                            $data['codigo'] = $this->generateCodigo(16);
                            $data['sincronizado'] = 'N';
                            $data['livro'] = $livro->id;
                            $this->db->insert($this->table, $data);
                        }
                    }
                }
            } elseif($data['livro'] == "0-first") {
                for($count = 0;$count < $qtd;$count++) {
                    foreach ($this->get_livros()->result() as $livro) {
                        if (strpos($livro->titulo, 'First') !== false || strpos($livro->titulo, 'Second') !== false || strpos($livro->titulo, 'Third') !== false) {
                            $data['codigo'] = $this->generateCodigo(16);
                            $data['sincronizado'] = 'N';
                            $data['livro'] = $livro->id;
                            $this->db->insert($this->table, $data);
                        }
                    }
                }
            } else {
                for($count = 0;$count < $qtd;$count++) {
                    $data['codigo'] = $this->generateCodigo(16);
                    $data['sincronizado'] = 'N';
                    $this->db->insert($this->table, $data);
                }
            }
            $this->set_message('creation_successful');
            return true;
        } catch(Exception $e) {
            $this->set_message('creation_unsuccessful');
            return false;
        }
    }

     /**
     * create_info
     *
     * @author Ellan Garbelini
    */
    public function create_info($data = array())
    {
        try {
            if($data['livro'] == "0-new") {
                $data['livro'] = null;
                $data['kit'] = 'S';
            } elseif($data['livro'] == "0-antigo") {
                $data['livro'] = null;
                $data['kit'] = 'S';
            } elseif($data['livro'] == "0-videoaula") {
                $data['livro'] = null;
                $data['kit'] = 'S';
            } elseif($data['livro'] == "0-first") {
                $data['livro'] = null;
                $data['kit'] = 'S';
            } else {
                $data['kit'] = 'N';
            }
            $data["usuario"] = $this->session->userdata('user_id');

            $this->db->insert('qrcode_info', $data);
            $id = $this->db->insert_id();

            return $id;
        } catch(Exception $e) {
            $this->set_message('creation_info_unsuccessful');
            return false;
        }  
    }

    public function imprimir_qrcodes($params){
        $id_qrcode_info = $params['qrcod_info'];
        $id_ini = $params['qrcode_id_ini'];
        $id_end = $params['qrcode_id_end'];
        $id_todos = $params['todos'];

        $this->db->select('qr.id, qr.codigo, qr.livro, l.titulo, qi.kit')
            ->join('qrcode_info qi', 'qr.qrcode_info = qi.id')
            ->join('livros l', 'qr.livro = l.id')
            ->where('qi.id', $id_qrcode_info);
        if($id_todos == 'N'){
            if(!empty($id_ini) && !empty($id_end)) {
                $this->db->where('qr.id >= '.(int)$id_ini.' AND qr.id <= '.(int)$id_end);
            } elseif(!empty($id_ini) && empty($id_end)) {
                $this->db->where('qr.id >= '.(int)$id_ini);
            } elseif(empty($id_ini) && !empty($id_end)) {
                $this->db->where('qr.id <= '.(int)$id_end);
            }
        }
        $this->db->order_by('qr.id');
        return $this->db->get($this->table.' qr');
    }

    /**
     * @param int $length
     * @return string
     */
    public function generateCodigo($length){
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        //$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";

        for($i=0;$i<$length;$i++):
            $token .= $codeAlphabet[$this->getRandomInteger(0,strlen($codeAlphabet))];
        endfor;
        return $token;
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    protected function getRandomInteger($min, $max){
        $range = $max - $min;

        if($range < 0):
            return $min; //not so random...
        endif;

        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; //length in bytes
        $bits = (int) $log + 1; //length in bits
        $filter = (int) (1 << $bits) - 1; //set all lower bits to 1

        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; //discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
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
            $messageLang = $this->lang->line($message) ? $this->lang->line($message) : $message;
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
