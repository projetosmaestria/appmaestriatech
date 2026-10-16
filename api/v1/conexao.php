<?php
//senha auth: nvwj9f6i

#@author: Ellan Garbelini

class Conexao{
    //local
    /*private $local = "127.0.0.1";
    private $login = "root";
    private $senha = "";
    private $banco = "xkeynet_refac";*/
    
    //desenvolvimento
    /*private $local = "localhost";
    private $login = "xkeynet_hom";
    private $senha = "NGIyYzM0NjhlZjFmN2Rj";
    private $banco = "xkey_xkeynet_hom";*/

    //produção
    private $local = "mysql.xkeysti.com.br";
    private $login = "xkeysti1";
    private $senha = "WtUV]!3_T6V,";
    private $banco = "xkeysti1";
    
    /**
     * @author: Ellan Garbelini
     * Função responsável pela conexão ao banco de dados.
     * 
     * @return mysql_connect faz a conexão com o banco.
     */
    public function getDatabase() {
        $conect = new mysqli($this->local, $this->login, $this->senha,$this->banco);

        if(!$conect){
            throw new Exception('Erro ao conectar ao Banco de Dados!');
        }else{
            $conect->query("SET NAMES 'utf8'");
            $conect->query('SET character_set_connection=utf8');
            $conect->query('SET character_set_client=utf8');
            $conect->query('SET character_set_results=utf8');
            return $conect;
     }
    }
}
?>