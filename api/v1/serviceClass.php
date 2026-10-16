<?php
//ini_set('display_errors', 1);

require_once ('Util.php');
require_once ('Bcrypt.php');
require_once ('conexao.php'); // Produção.
require_once 'abstractServiceClass.php';

class ServiceModel extends Service {

    private $conexao = null;
    private $prefix = "";

    public function __construct($request, $origin) {
        parent::__construct($request);
        $this->conexao = new Conexao();
    }

    protected function login()
    {    
        if ($this->method == 'POST'):
            $post = json_decode(file_get_contents('php://input'), true);
            
            $util = new Util();
            
            $mysqli = $this->conexao->getDatabase();
            $sql = "SELECT id FROM ".$this->prefix."users WHERE email = '".$post["username"]."'";
            $res = $mysqli->query($sql);
            $rows = mysqli_num_rows($res);

            if($rows === 1) {
                $linha = mysqli_fetch_assoc($res);
                $password = $this->hash_password_db($linha["id"], $post["password"]);
            
                if ($password === TRUE)
                {
                    header('HTTP/1.1 200 Authorized');
                    header("Content-Type: application/json");
                    $headers = $util->sign_request(array("voxy_user_id"=>$linha["id"]) );
                    header($headers[0]);
                    return json_encode(array("voxy_user_id"=>$linha["id"]));
                } else {
                    header("Content-Type: text/html");
                    header('HTTP/1.1 401 Unauthorized');
                    return utf8_encode("Usuário não encontrado.");
                }
            } else {
                header("Content-Type: text/html");
                header('HTTP/1.1 401 Unauthorized');
                return utf8_encode("Usuário não encontrado.");
            }           
        endif;
    }

    private function hash_password_db($id, $password, $use_sha1_override=FALSE)
    {
        $bcrypt = new Bcrypt();

        if (empty($id) || empty($password))
        {
            return FALSE;
        }

        $mysqli = $this->conexao->getDatabase();
        $query = "SELECT password, salt FROM users WHERE id = '".$id."' ORDER BY id DESC LIMIT 1";
        $res = $mysqli->query($query);
        $rows = mysqli_num_rows($res);

        $hash_password_db = mysqli_fetch_assoc($res);

        if ($rows !== 1)
        {
            return FALSE;
        }

        // bcrypt
        if ($use_sha1_override === FALSE)
        {
            if ($bcrypt->verify($password,$hash_password_db['password']))
            {
                return TRUE;
            }

            return FALSE;
        }
    }

}