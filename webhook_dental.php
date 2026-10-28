<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    ini_set('display_errors', true);
    error_reporting(1);

    //require("const.php");
    require("api/v3/conexao.php");
    require("api/v3/Voxy.php");

    $webhook = new Webhook();
    
    if($_POST) {
        $retorno = $webhook->insert_iugu_fila_execucao($_POST['event'], json_encode($_POST));
        $webhook->log($_POST);
        $webhook->log($retorno);
        exit;
    }    

    $fila_execucao = $webhook->get_iugu_fila_execucao();
    $fila_execucao = $webhook->organizar_fila_execucao($fila_execucao);
    $webhook->rodar_fila_execucao($fila_execucao);

    class Webhook {
        protected $iugu_token   = null;
        protected $iugu_url     = "https://api.iugu.com/v1/";
        protected $conn         = null;
        protected $conta        = null;
        protected $cartao       = null;
        protected $grupo        = null;
        protected $forma_pgto   = null;
        protected $tip_rece     = null;   
        protected $vendedor     = null;
        protected $status       = array(
            "pending"           => "EA", 
            "paid"              => "FI", 
            "canceled"          => "CA", 
            "partially_paid"    => "FP", 
            "refunded"          => "RE", 
            "expired"           => "CA", 
            "authorized"        => "AI", 
            "in_protest"        => "EA", 
            "chargeback"        => "CA"
        );
        protected $debug        = TRUE;
        protected $ambiente     = "prod";

        function __construct() {
            $conn = new Conexao();
            $this->conn = $conn->getDatabase();

            if($this->ambiente == "dev") {
                $this->conta        = 7;
                $this->cartao       = 33;
                $this->grupo        = 65;
                $this->forma_pgto   = 13;
                $this->tip_rece     = 35;   
                $this->vendedor     = 28170;
                $this->iugu_token   = "83F3E4C2534F04C2B485000D46F5E2675FBD7D368F25E307D4276619AC19C89D";
            } elseif($this->ambiente == "hom") {
                $this->conta        = 7;
                $this->cartao       = 33;
                $this->grupo        = 66;
                $this->forma_pgto   = 13;
                $this->tip_rece     = 35;   
                $this->vendedor     = 28729;
                $this->iugu_token   = "83F3E4C2534F04C2B485000D46F5E2675FBD7D368F25E307D4276619AC19C89D";
            } else {
                $this->conta        = 7;
                $this->cartao       = 33;
                $this->grupo        = 80;
                $this->forma_pgto   = 20;
                $this->tip_rece     = 39;   
                $this->vendedor     = 19490;
                $this->iugu_token   = "50E1E28B5437858A81FC6C3C1E8910624623D6079BF586CA1ACCD9FD5E38965A";
            }
        }

        private function requisicao($endpoint, $data) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->iugu_url.$endpoint."/".$data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => array(
                    "authorization: Basic ".base64_encode($this->iugu_token.":"),                                                                      
                    'Content-Type: application/json'         
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);
            if ($err) {
                return array("erro" => true, "msg" => "Curl Error: ".$err);
            } else {
                return array("erro" => false, "data" => $response);
            }
        }

        public function log($txt) {
            if($this->debug) {
                $fp = fopen("webhook_dental.txt", "a");
                $escreve = fwrite($fp, date('Y-m-d H:i:s'));
                $escreve = fwrite($fp, print_r($txt, TRUE));
                fclose($fp);
            }
        }

        public function insert_iugu_fila_execucao($event, $content) {
            if(!empty($content)) {
                try {
                    $qry = "INSERT INTO iugu_fila_execucao(event, content) VALUES('".$event."', '".$content."')";
                    $this->conn->query($qry);
                    return true;
                } catch(mysqli_sql_exception $e) {
                    return array("erro" => true, "data" => $e->getMessage());
                }
            }
            return false;
        }

        public function update_iugu_fila_execucao($id, $qty_exec, $errors) {
            if(!empty($id)) {
                try {
                    $qry = "UPDATE iugu_fila_execucao SET qty_exec = '".$qty_exec."', errors = '".$errors."' WHERE id = '".$id."'";
                    $this->conn->query($qry);
                    return true;
                } catch(mysqli_sql_exception $e) {
                    return array("erro" => true, "data" => $e->getMessage());
                }
            }
            return false;
        }

        public function get_iugu_fila_execucao() {
            $qry = 'SELECT * FROM iugu_fila_execucao WHERE qty_exec < "10" ORDER BY created_at ASC';
            $qry_result = $this->conn->query($qry);
            $rows = [];
            while($row = mysqli_fetch_assoc($qry_result)) {
                $rows[] = $row;
            }
            return $rows;
        }

        public function delete_iugu_fila_execucao($id = null) {
            if($id == null)
                return false;

            try {
                $qry = "DELETE FROM iugu_fila_execucao WHERE id = '".$id."'";
                $this->conn->query($qry);
                return true;
            } catch(mysqli_sql_exception $e) {
                return array("erro" => true, "data" => $e->getMessage());
            }

            return false;
        }

        private function has_unidade() {
            $qry = 'SELECT * FROM unidades WHERE iugu_token = "'.$this->iugu_token.'" AND iugu_status = "S"';
            $qry_result = $this->conn->query($qry);
            $qry_rows = mysqli_num_rows($qry_result);
            if($qry_rows == 0) 
                return false;
            else
                return true;
        }

        private function get_unidade() {
            $qry = 'SELECT * FROM unidades WHERE iugu_token = "'.$this->iugu_token.'" AND iugu_status = "S"';
            $qry_result = $this->conn->query($qry);
            return mysqli_fetch_assoc($qry_result);
        }

        private function get_iugu_subscriptions_info($assinatura_id) {
            $qry = 'SELECT i.* FROM iugu_subscriptions i WHERE i.iugu_id = "'.$assinatura_id.'"';
            $qry_result = $this->conn->query($qry);
            return mysqli_fetch_assoc($qry_result);
        }

        private function has_condicao_by_plan($plan_id, $unidade_id) {
            $qry = 'SELECT c.* FROM tipo_condicao_parcela_orcamento c WHERE c.plano_iugu = "'.$plan_id.'" AND c.unidade_id = "'.$unidade_id.'"';
            $qry_result = $this->conn->query($qry);
            $qry_rows = mysqli_num_rows($qry_result);
            if($qry_rows == 0) 
                return false;
            else
                return true;
        }

        private function get_condicao_by_plan($plan_id, $unidade_id) {
            $qry = '
                SELECT c.*, r.*, f.*, c.DESCRICAO as cond_desc, c.ID as cond_id
                FROM tipo_condicao_parcela_orcamento c 
                INNER JOIN tip_rece r ON r.CODIGO = c.Id_TipoReceb 
                INNER JOIN cad_fpgt f ON f.CODIGO = c.Id_formpag
                WHERE c.plano_iugu = "'.$plan_id.'" AND c.unidade_id = "'.$unidade_id.'"';
            $qry_result = $this->conn->query($qry);
            return mysqli_fetch_assoc($qry_result);
        }

        private function has_periodico_by_plan($unidade_id) {
            /*$qry = 'SELECT p.* FROM editora_periodico p WHERE p.plano_iugu = "'.$plan_id.'" AND p.unidade_id = "'.$unidade_id.'"';
            $qry_result = $this->conn->query($qry);
            $qry_rows = mysqli_num_rows($qry_result);
            if($qry_rows == 0) 
                return false;
            else
                return true;*/

            $qry = 'SELECT i.* FROM iugu_plan_periodico i INNER JOIN editora_periodico p ON p.id = i.periodico_id WHERE p.unidade_id = "'.$unidade_id.'"';
            $qry_result = $this->conn->query($qry);
            $qry_rows = mysqli_num_rows($qry_result);
            if($qry_rows == 0) 
                return false;
            else
                return true;
        }

        private function get_periodico_by_plan($plan_id, $unidade_id) {
            $qry = '
                SELECT p.*
                FROM editora_periodico p
                WHERE p.plano_iugu = "'.$plan_id.'" AND p.unidade_id = "'.$unidade_id.'"';
            $qry_result = $this->conn->query($qry);
            return mysqli_fetch_assoc($qry_result);
        }

        private function has_user_by_email($email) {
            $qry = 'SELECT *, CONCAT(first_name, " ", last_name) as nome_completo FROM users WHERE email = "'.$email.'"';
            $qry_result = $this->conn->query($qry);
            $qry_rows = mysqli_num_rows($qry_result);
            if($qry_rows == 0) 
                return false;
            else
                return true;
        }

        private function get_user_by_email($email) {
            $qry = 'SELECT *, CONCAT(first_name, " ", last_name) as nome_completo FROM users WHERE email = "'.$email.'"';
            $qry_result = $this->conn->query($qry);

            return mysqli_fetch_assoc($qry_result);
        }

        private function has_iugu_customer($iugu_id) {
            $qry = 'SELECT * FROM iugu_users WHERE iugu_id = "'.$iugu_id.'"';
            $qry_result = $this->conn->query($qry);
            $qry_rows = mysqli_num_rows($qry_result);
            if($qry_rows == 0) 
                return false;
            else
                return true;
        }

        private function insert_iugu_customer($data = array()) {
            if(!empty($data)) {
                $qry = "INSERT INTO iugu_users(user_id, iugu_id) VALUES('".$data["id"]."', '".$data["iugu_id"]."')";
                return $this->conn->query($qry);
            }
            return false;
        }

        private function insert_user($data) {
            //$separa_nome = explode(" ", utf8_decode($data["name"]), 2);
            $separa_nome = explode(" ", $data["name"], 2);
            $qry = 'INSERT INTO users(username, email, activation_code, created_on, active, first_name, last_name) VALUES("'.$data["name"].'", "'.$data["email"].'", "'.sha1(md5(microtime())).'", "'.time().'", "0", "'.@$separa_nome[0].'", "'.@$separa_nome[1].'")';
            
            $this->conn->query($qry);
            
            $this->insert_pessoa($data, $this->conn->insert_id);

            $user = $this->get_user_by_email($data["email"]);

            return $user;
        }

        private function insert_pessoa($data, $user_id) {
            $qry = 'INSERT INTO pessoas(user_id, cpf, endereco, numero, cep, telefone, bairro, complemento, cidade, uf) VALUES("'.$user_id.'", "'.$data["cpf_cnpj"].'", "'.$data["street"].'", "'.$data["number"].'", "'.$data["zip_code"].'", "'.("(".$data["phone_prefix"].") ".$data["phone"]).'", "'.$data["district"].'", "'.$data["complement"].'", "'.$data["city"].'", "'.$data["state"].'")';
            $this->conn->query($qry);

            return $this->conn->insert_id;
        }

        private function insert_empresas_users($empresa_id, $user_id) {
            $qry_check = 'SELECT * FROM empresas_users WHERE empresa_id = "'.$empresa_id.'" AND user_id = "'.$user_id.'"';
            $qry_check_result = $this->conn->query($qry_check);

            $rows = mysqli_num_rows($qry_check_result);

            if ($rows == 0):
                $qry = 'INSERT INTO empresas_users(empresa_id, user_id) VALUES("'.$empresa_id.'", "'.$user_id.'")';
                $this->conn->query($qry);

                return $this->conn->insert_id;
            endif;

            return true;
        }

        private function insert_user_unidade($user_id, $unidade_id) {
            $qry_check = 'SELECT * FROM user_unidade WHERE user_id = "'.$user_id.'" AND unidade_id = "'.$unidade_id.'"';
            $qry_check_result = $this->conn->query($qry_check);

            $rows = mysqli_num_rows($qry_check_result);

            if ($rows == 0):
                $qry = 'INSERT INTO user_unidade(user_id, unidade_id) VALUES("'.$user_id.'", "'.$unidade_id.'")';
                $this->conn->query($qry);

                return $this->conn->insert_id;
            endif;

            return true;
        }

        private function insert_users_groups($user_id, $group_id) {
            $qry_check = 'SELECT * FROM users_groups WHERE user_id = "'.$user_id.'" AND group_id = "'.$group_id.'"';
            $qry_check_result = $this->conn->query($qry_check);

            $rows = mysqli_num_rows($qry_check_result);

            if ($rows == 0):
                $qry = 'INSERT INTO users_groups(user_id, group_id) VALUES("'.$user_id.'", "'.$group_id.'")';
                $this->conn->query($qry);

                return $this->conn->insert_id;
            endif;

            return true;
        }

        private function get_forma_pgto($id) {
            $qry = 'SELECT * FROM cad_fpgt WHERE CODIGO = "'.$id.'"';
            $qry_result = $this->conn->query($qry);

            return mysqli_fetch_assoc($qry_result);
        }

        //private function insert_cad_orca($unidade_id, $condicao_id, $condicao_num_parc, $user_id) {
        private function insert_cad_orca($unidade_id, $forma_pgto_id, $user_id) {
            /***
            //vincular todos os serviços
            $valor_servicos = 0;
            $valor_servicos = $this->get_valor_servicos($condicao_id);

            //vincular todos os produtos
            $valor_produtos = 0;
            $valor_produtos = $this->get_valor_servicos($condicao_id);
            ***/

            $forma_pgto = $this->get_forma_pgto($forma_pgto_id);

            $user = $this->get_user_pessoa($user_id);
            
            $user_create_at = date('Y-m-d H:i:s', $user["created_on"]);
            $user_create_at = explode(" ", $user_create_at);

            try {
                $qry = 'INSERT INTO cad_orca(filial, lig_id_curso, status, user_id, situacao, tipo_saxweb, data_cadastro, hora_cadastro, total_produtos, total_servicos, total_geral, endereco, cep, numero, complemento, bairro, cidade, estado, data_pedido, hora_pedido, qnt_parcelas, responsavel_financeiro, vendedor, inicio_vigencia, fim_vigencia, data_matricula, ultima_atualizacao) VALUES(
                    "'.$unidade_id.'", 
                    NULL, 
                    "'.$this->get_status_prematricula($unidade_id).'",
                    "'.$user_id.'", 
                    "EA",
                    "3",
                    "'.$user_create_at[0].'",
                    "'.$user_create_at[1].'",
                    "0",
                    "0",
                    "0",
                    "'.$user["endereco"].'",
                    "'.$user["cep"].'",
                    "'.$user["numero"].'",
                    "'.$user["complemento"].'",
                    "'.$user["bairro"].'",
                    "'.$user["cidade"].'",
                    "'.$user["uf"].'",
                    "'.date('Y-m-d').'",
                    "'.date('H:i:s').'",
                    "'.$forma_pgto['NUM_PARCELAS'].'",
                    NULL,
                    "'.$this->vendedor.'",
                    "'.date('Y-m-d').'",
                    NULL,
                    NULL,
                    "'.date('Y-m-d H:i:s').'"
                    )';
                $this->conn->query($qry);
                $id = $this->conn->insert_id;

                /*$count_parcelas = 0;
                $vencimentos = $this->vencimentos(date("Y-m-d"), $forma_pgto["NUM_PARCELAS"]);
                for($i=1; $i <= $forma_pgto["NUM_PARCELAS"]; $i++) {
                    $valor_parcela = 0;

                    //insert par_orca
                    try{
                        $qry = 'INSERT INTO par_orca(orc_id, tipo_recebimento, parcela, valor, vencimento) VALUES("'.$id.'", "'.$this->tip_rece.'", "'.($count_parcelas+1).'", "'.$valor_parcela.'", "'.$vencimentos[$count_parcelas].'")';
                        $this->conn->query($qry);
                        $parc_id = $this->conn->insert_id;
                    } catch(mysqli_sql_exception $e) {
                        return array("erro" => true, "data" => $e->getMessage());
                    }   

                    $count_parcelas++;
                }*/
                
                return array("erro" => false, "data" => $id);
            } catch(mysqli_sql_exception $e) {
                return array("erro" => true, "data" => $e->getMessage());
            }
        }

        private function update_cad_orca($id = null, $status, $fim_vigencia = NULL) {
            if($id == null)
                return array("erro" => true);

            try {
                if($fim_vigencia == NULL) {
                    $qry = 'UPDATE cad_orca SET status = "'.$status.'", fim_vigencia = NULL, ultima_atualizacao = "'.date('Y-m-d H:i:s').'", sincronizado = "N" WHERE id = "'.$id.'"';
                } else {
                    $qry = 'UPDATE cad_orca SET status = "'.$status.'", fim_vigencia = "'.date('Y-m-d').'", ultima_atualizacao = "'.date('Y-m-d H:i:s').'", sincronizado = "N" WHERE id = "'.$id.'"';
                }
                $this->conn->query($qry);
                return array("erro" => false);
            } catch(mysqli_sql_exception $e) {
                return array("erro" => true, "data" => $e->getMessage());
            }
        }

        private function get_valor_servicos($condicao) {
            $qry = 'SELECT s.* FROM tipo_condicao_parcela_servicos s INNER JOIN tipo_condicao_parcela_orcamento c ON c.ID = s.LIG_ID_TIPO_CONDICAO WHERE s.LIG_ID_TIPO_CONDICAO = "'.$condicao.'"';
            $qry_result = $this->conn->query($qry);

            $qry_rows = mysqli_num_rows($qry_result);

            $valor = 0;
            if ($qry_rows > 0):
                while($row = mysqli_fetch_assoc($qry_result)):
                    $valor += $row["VALOR"];
                endwhile;
            endif;

            return $valor;
        }

        private function get_valor_produtos($condicao) {            
            $qry = 'SELECT p.* FROM tipo_condicao_parcela_produtos p INNER JOIN tipo_condicao_parcela_orcamento c ON c.ID = p.LIG_ID_TIPO_CONDICAO WHERE p.LIG_ID_TIPO_CONDICAO = "'.$condicao.'"';
            $qry_result = $this->conn->query($qry);

            $qry_rows = mysqli_num_rows($qry_result);

            $valor = 0;
            if ($qry_rows > 0):
                while($row = mysqli_fetch_assoc($qry_result)):
                    $valor += $row["VALOR"];
                endwhile;
            endif;

            return $valor;
        }

        private function get_user_pessoa($id) {
            $qry = 'SELECT u.*, p.* FROM users u INNER JOIN pessoas p ON p.user_id = u.id WHERE u.id = '.$id;
            $qry_result = $this->conn->query($qry);
            return mysqli_fetch_assoc($qry_result);
        }

        private function get_status_prematricula($unidade_id) {
            $qry = 'SELECT LIG_STATUS_CONTRATO_01 FROM parametro WHERE LIG_ID_FILIAL = '.$unidade_id;
            $qry_result = $this->conn->query($qry);
            $row = mysqli_fetch_assoc($qry_result);
            return $row["LIG_STATUS_CONTRATO_01"];
        }

        private function get_status_matricula($unidade_id) {
            $qry = 'SELECT LIG_STATUS_CONTRATO_02 FROM parametro WHERE LIG_ID_FILIAL = '.$unidade_id;
            $qry_result = $this->conn->query($qry);
            $row = mysqli_fetch_assoc($qry_result);
            return $row["LIG_STATUS_CONTRATO_02"];
        }

        private function get_status_anulado($unidade_id) {
            $qry = 'SELECT LIG_ID_ANULADO FROM parametro WHERE LIG_ID_FILIAL = '.$unidade_id;
            $qry_result = $this->conn->query($qry);
            $row = mysqli_fetch_assoc($qry_result);
            return $row["LIG_ID_ANULADO"];
        }

        private function insert_condicoes_orcamento($cad_orca_id, $condicao) {
            
            $data_inicio = date('Y-m-d');
            if($condicao["DIAS_ENTRADA"] > 0) {
                $data_inicio = date('Y-m-d', strtotime($data_inicio. ' + '.$condicao["DIAS_ENTRADA"].' days'));
            }

            $data_termino = date('Y-m-d', strtotime($data_inicio. ' + '.($condicao["NUM_PARCELAS"] - 1).' months'));

            try {               
                $qry = 'INSERT INTO condicoes_orcamento(ORCAMENTO, FORMA_DE_PAGAMENTO, DATA_INICIO, DATA_TERMINO, DATA_PRIMEIRA_PARCELA, TIPO_RECEBIMENTO, TIPO_CONDICAO_ID, NUM_PARCELAS, PLANO_FINANCEIRO_ID, VALORDESCONTOPONTUALIDADE, CARENCIADESCONTOPONTUALIDADE, TIPOVALORDESCONTOPONTUALIDADE) VALUES(
                    "'.$cad_orca_id.'", 
                    "'.$condicao["FormaReceb"].'", 
                    "'.$data_inicio.'", 
                    "'.$data_termino.'", 
                    "'.$data_inicio.'", 
                    "'.$condicao["Id_TipoReceb"].'", 
                    "'.$condicao["ID"].'", 
                    "'.$condicao["NUM_PARCELAS"].'", 
                    "'.$condicao["Id_planofin"].'", 
                    "'.$condicao["VALOR_DESCONTO_PONTUALIDADE"].'", 
                    "'.$condicao["CARENCIA_DESCONTO_PONTUALIDADE"].'", 
                    "'.$condicao["TIPOVALORDESCONTOPONTUALIDADE"].'"
                    )';
                $this->conn->query($qry);
                $id = $this->conn->insert_id;
                
                return array("erro" => false, "data" => $id);
            } catch(mysqli_sql_exception $e) {
                return array("erro" => true, "data" => $e->getMessage());
            }
        }

        private function get_servicos($condicao_id) {
            $qry = 'SELECT s.* FROM tipo_condicao_parcela_servicos s WHERE s.LIG_ID_TIPO_CONDICAO = "'.$condicao_id.'"';
            $qry_result = $this->conn->query($qry);
            $rows = [];
            while($row = mysqli_fetch_assoc($qry_result)) {
                $rows[] = $row;
            }
            return $rows;
        }

        private function get_produtos($condicao_id) {
            $qry = 'SELECT p.* FROM tipo_condicao_parcela_produtos p WHERE p.LIG_ID_TIPO_CONDICAO = "'.$condicao_id.'"';
            $qry_result = $this->conn->query($qry);
            $rows = [];
            while($row = mysqli_fetch_assoc($qry_result)) {
                $rows[] = $row;
            }
            return $rows;
        }

        private function get_condicao_orc($cond_orc_id) {
            $qry = 'SELECT c.* FROM condicoes_orcamento c WHERE c.CODIGO = "'.$cond_orc_id.'"';
            $qry_result = $this->conn->query($qry);
            return mysqli_fetch_assoc($qry_result);
        }

        private function vencimentos($data, $numero) {
            $parc = array();
            $parc[] = $data;
            list($ano, $mes, $dia) = explode("-", $data);
            for($i = 1; $i < $numero;$i++)
            {
                $mes++;
                if ((int)$mes == 13)
                {
                    $ano++;
                    $mes = 1;
                }
                $tira = $dia;
                while (!checkdate($mes, $tira, $ano))
                {
                    $tira--;
                }
                $parc[] = sprintf("%s-%02d-%02d", $ano, $mes, $tira);
            }
            return $parc;
        }

        private function insert_ser_orca($condicao_id, $cad_orca_id) {
            $i=1;
            $servicos = $this->get_servicos($condicao_id);
            if(count($servicos) > 0) {
                foreach ($servicos as $serv) {
                    $qry = 'INSERT INTO ser_orca(orc_id, serv_id, sequencia, preco, total_desconto, quantidade_item, total_item) VALUES("'.$cad_orca_id.'", "'.$serv['LIG_ID_SERVICO'].'", "'.$i.'", "'.$serv['VALOR'].'", "0", "1", "'.$serv['VALOR'].'")';
                    $this->conn->query($qry);                
                    $i++;
                }
            }
        }

        private function insert_parcelas_servicos_condicoes_orcamento($condicao, $cond_orc_id, $cad_orca_id) {
            $servicos = $this->get_servicos($condicao['cond_id']);
            if(count($servicos) > 0) {
                $k=1;
                
                $count_parcelas = 0;
                foreach ($servicos as $serv) {

                    //insert servico_condicoes_orcamento
                    try{                
                        $condicao_orc = $this->get_condicao_orc($cond_orc_id);

                        $qry = 'INSERT INTO servico_condicoes_orcamento(CONDICOES_ORCAMENTO, SERVICO, SEQUENCIA) VALUES("'.$cond_orc_id.'", "'.$serv['LIG_ID_SERVICO'].'", "'.$k.'")';
                        $this->conn->query($qry);
                        $serv_cond_id = $this->conn->insert_id;

                        $dados = array();
                        $valor_servicos = $this->get_valor_servicos($condicao["cond_id"]);

                        if($condicao["DIAS_ENTRADA"] > 0) 
                            $data_parcela = date('Y-m-d', strtotime('+'.$condicao["DIAS_ENTRADA"].' days'));
                        else
                            $data_parcela = date('Y-m-d');

                        $vencimentos = $this->vencimentos($data_parcela, $condicao["NUM_PARCELAS"]);
                        
                        for($i=1; $i <= $condicao["NUM_PARCELAS"]; $i++) {
                            if($condicao["CalcParc"] == 0) {
                                $valor_parcela = $valor_servicos;
                            } else {
                                $valor_parcela = $valor_servicos / $condicao["NUM_PARCELAS"];
                            }

                            //insert par_orca
                            try{
                                $qry = 'INSERT INTO par_orca(orc_id, tipo_recebimento, parcela, valor, vencimento, valor_desconto_pontualidade, carencia_desconto_pontualidade, tipo_valor_desconto_pontualidade) VALUES("'.$cad_orca_id.'", "'.$condicao["Id_TipoReceb"].'", "'.($count_parcelas+1).'", "'.$valor_parcela.'", "'.$vencimentos[$count_parcelas].'", "'.$condicao['VALOR_DESCONTO_PONTUALIDADE'].'", "'.$condicao['CARENCIA_DESCONTO_PONTUALIDADE'].'", "'.$condicao['TIPOVALORDESCONTOPONTUALIDADE'].'")';
                                $this->conn->query($qry);
                                $parc_id = $this->conn->insert_id;

                                //insert parcela_servico_condicoes_orca
                                try{
                                    $qry = 'INSERT INTO parcela_servico_condicoes_orca(SERVICO_CONDICOES_ORCA, VALOR, VENCIMENTO, TIPO_RECEBIMENTO, PARCELA, CODIGO_PARCELA_GERADA, VALORDESCONTOPONTUALIDADE, CARENCIADESCONTOPONTUALIDADE, TIPOVALORDESCONTOPONTUALIDADE, PLANO_FINANCEIRO_ID) VALUES("'.$serv_cond_id.'", "'.$valor_parcela.'", "'.$vencimentos[$count_parcelas].'", "'.$condicao["Id_TipoReceb"].'", "'.($count_parcelas+1).'", "'.$parc_id.'", "'.$condicao_orc['VALORDESCONTOPONTUALIDADE'].'", "'.$condicao_orc['CARENCIADESCONTOPONTUALIDADE'].'", "'.$condicao_orc['TIPOVALORDESCONTOPONTUALIDADE'].'", "'.$condicao_orc['PLANO_FINANCEIRO_ID'].'")';
                                    $this->conn->query($qry);
                                    $parc_serv_id = $this->conn->insert_id;
                                } catch(mysqli_sql_exception $e) {
                                    return array("erro" => true, "data" => $e->getMessage());
                                }       
                            } catch(mysqli_sql_exception $e) {
                                return array("erro" => true, "data" => $e->getMessage());
                            }   

                            $count_parcelas++;
                        }

                    } catch(mysqli_sql_exception $e) {
                        return array("erro" => true, "data" => $e->getMessage());
                    }

                    $k++;
                }

                return array("erro" => false, "data" => "OK");
            }
        }

        private function insert_iugu_subscription($cad_orca_id, $iugu_id) {
            $qry = 'INSERT INTO iugu_subscriptions(cad_orca_id, iugu_id) VALUES("'.$cad_orca_id.'", "'.$iugu_id.'")';
            $this->conn->query($qry);
            return $this->conn->insert_id;
        }

        private function update_iugu_subscription($id = null, $status) {
            if($id == null)
                return array("erro" => true);

            try {
                $qry = 'UPDATE iugu_subscriptions SET status = "'.$status.'" WHERE id = "'.$id.'"';
                $this->conn->query($qry);
                return array("erro" => false);
            } catch(mysqli_sql_exception $e) {
                return array("erro" => true, "data" => $e->getMessage());
            }
        }

        private function insert_iugu_invoices($data) {
            $qry = 'INSERT INTO iugu_invoices(cad_orca_id, ct_receb_id, iugu_id, tipo, status) VALUES("'.$data['cad_orca_id'].'", "'.$data['ct_receb_id'].'", "'.$data['iugu_id'].'", "'.$data['tipo'].'", "'.$data['status'].'")';
            $this->conn->query($qry);
            return $this->conn->insert_id;
        }

        private function get_planos($periodico, $unidade_id) {
            $qry = 'SELECT i.*, p.id, p.id_periodicidade FROM iugu_plan_periodico i INNER JOIN editora_periodico p ON p.id = i.periodico_id WHERE i.plano = "'.$periodico.'" AND p.unidade_id = "'.$unidade_id.'"';
            $qry_result = $this->conn->query($qry);
            $rows = [];
            while($row = mysqli_fetch_assoc($qry_result)) {
                $rows[] = $row;
            }
            return $rows;
        }

        //private function insert_pro_orca($condicao_id, $cad_orca_id, $periodico) {
        private function insert_pro_orca($cad_orca_id, $periodico, $unidade_id) {
            $i=1;
            /***
            $produtos = $this->get_produtos($condicao_id);
            if(count($produtos) > 0) {
                foreach ($produtos as $prod) {
                    $qry = 'INSERT INTO pro_orca(orc_id, prod_id, sequencia, preco, total_desconto, quantidade_item, PERIODICO_ID, Id_Periodicidade) VALUES("'.$cad_orca_id.'", "'.$prod['LIG_ID_PRODUTO'].'", "'.$i.'", "'.$prod['VALOR'].'", "0", "1", "'.$periodico['id'].'", "'.$periodico['id_periodicidade'].'")';
                    $this->conn->query($qry);                
                    $i++;
                }
            }***/

            $planos = $this->get_planos($periodico, $unidade_id);
            if(count($planos) > 0) {
                foreach ($planos as $plan) {
                    $qry = 'INSERT INTO pro_orca(orc_id, prod_id, sequencia, preco, total_desconto, quantidade_item, PERIODICO_ID, Id_Periodicidade) VALUES("'.$cad_orca_id.'", NULL, "'.$i.'", "0", "0", "1", "'.$plan['id'].'", "'.$plan['id_periodicidade'].'")';
                    $this->conn->query($qry);                
                    $i++;
                }
            }
        }

        private function insert_par_orca($cad_orca_id, $parcela, $valor, $vencimento) {
            try{
                $qry = 'INSERT INTO par_orca(orc_id, tipo_recebimento, parcela, valor, vencimento, valor_desconto_pontualidade, carencia_desconto_pontualidade) VALUES("'.$cad_orca_id.'", "'.$this->tip_rece.'", "'.$parcela.'", "'.$valor.'", "'.$vencimento.'", "0", "0")';
                $this->conn->query($qry);
                return $this->conn->insert_id;
            } catch(mysqli_sql_exception $e) {
                return false;
            }   
        }

        private function insert_parcelas_produtos_condicoes_orcamento($condicao, $cond_orc_id, $cad_orca_id) {
            $produtos = $this->get_produtos($condicao['cond_id']);
            if(count($produtos) > 0) {
                $k=1;
                
                $count_parcelas = 0;
                foreach ($produtos as $prod) {

                    //insert produto_condicoes_orcamento
                    try{                
                        $condicao_orc = $this->get_condicao_orc($cond_orc_id);

                        $qry = 'INSERT INTO produto_condicoes_orcamento(CONDICOES_ORCAMENTO, PRODUTO, SEQUENCIA) VALUES("'.$cond_orc_id.'", "'.$prod['LIG_ID_PRODUTO'].'", "'.$k.'")';
                        $this->conn->query($qry);
                        $prod_cond_id = $this->conn->insert_id;

                        $dados = array();
                        $valor_produtos = $this->get_valor_produtos($condicao["cond_id"]);

                        if($condicao["DIAS_ENTRADA"] > 0) 
                            $data_parcela = date('Y-m-d', strtotime('+'.$condicao["DIAS_ENTRADA"].' days'));
                        else
                            $data_parcela = date('Y-m-d');

                        $vencimentos = $this->vencimentos($data_parcela, $condicao["NUM_PARCELAS"]);
                        
                        for($i=1; $i <= $condicao["NUM_PARCELAS"]; $i++) {
                            if($condicao["CalcParc"] == 0) {
                                $valor_parcela = $valor_produtos;
                            } else {
                                $valor_parcela = $valor_produtos / $condicao["NUM_PARCELAS"];
                            }

                            //insert par_orca
                            try{
                                $qry = 'INSERT INTO par_orca(orc_id, tipo_recebimento, parcela, valor, vencimento, valor_desconto_pontualidade, carencia_desconto_pontualidade, tipo_valor_desconto_pontualidade) VALUES("'.$cad_orca_id.'", "'.$condicao["Id_TipoReceb"].'", "'.($count_parcelas+1).'", "'.$valor_parcela.'", "'.$vencimentos[$count_parcelas].'", "'.$condicao['VALOR_DESCONTO_PONTUALIDADE'].'", "'.$condicao['CARENCIA_DESCONTO_PONTUALIDADE'].'", "'.$condicao['TIPOVALORDESCONTOPONTUALIDADE'].'")';
                                $this->conn->query($qry);
                                $parc_id = $this->conn->insert_id;

                                //insert parcela_produto_condicoes_orca
                                try{
                                    $qry = 'INSERT INTO parcela_produto_condicoes_orca(PRODUTO_CONDICOES_ORCA, VALOR, VENCIMENTO, TIPO_RECEBIMENTO, PARCELA, CODIGO_PARCELA_GERADA, VALORDESCONTOPONTUALIDADE, CARENCIADESCONTOPONTUALIDADE, TIPOVALORDESCONTOPONTUALIDADE, PLANO_FINANCEIRO_ID) VALUES("'.$prod_cond_id.'", "'.$valor_parcela.'", "'.$vencimentos[$count_parcelas].'", "'.$condicao["Id_TipoReceb"].'", "'.($count_parcelas+1).'", "'.$parc_id.'", "'.$condicao_orc['VALORDESCONTOPONTUALIDADE'].'", "'.$condicao_orc['CARENCIADESCONTOPONTUALIDADE'].'", "'.$condicao_orc['TIPOVALORDESCONTOPONTUALIDADE'].'", "'.$condicao_orc['PLANO_FINANCEIRO_ID'].'")';
                                    $this->conn->query($qry);
                                    $parc_prod_id = $this->conn->insert_id;
                                } catch(mysqli_sql_exception $e) {
                                    return array("erro" => true, "data" => $e->getMessage());
                                }       
                            } catch(mysqli_sql_exception $e) {
                                return array("erro" => true, "data" => $e->getMessage());
                            }   

                            $count_parcelas++;
                        }

                    } catch(mysqli_sql_exception $e) {
                        return array("erro" => true, "data" => $e->getMessage());
                    }

                    $k++;
                }

                return array("erro" => false, "data" => "OK");
            }
        }

        private function get_par_orca($orc_id, $parc_id = null) {
            /*$qry = 'SELECT p.*, pa.valor_desconto_pontualidade, pa.carencia_desconto_pontualidade, pa.tipo_valor_desconto_pontualidade, co.TIPO_CONDICAO_ID FROM parcela_servico_condicoes_orca p INNER JOIN servico_condicoes_orcamento sc ON sc.CODIGO = p.SERVICO_CONDICOES_ORCA INNER JOIN condicoes_orcamento co ON co.CODIGO = sc.CONDICOES_ORCAMENTO INNER JOIN par_orca pa ON pa.id = p.CODIGO_PARCELA_GERADA WHERE co.ORCAMENTO = "'.$orc_id.'"';
            $qry_result = $this->conn->query($qry);
            $rows = [];
            while($row = mysqli_fetch_assoc($qry_result)) {
                $rows[] = $row;
            }
            return $rows;*/

            $qry = 'SELECT p.* FROM par_orca p WHERE p.orc_id = "'.$orc_id.'"';
            if($parc_id != null)
                $qry .= ' AND id = "'.$parc_id.'"';
            $qry_result = $this->conn->query($qry);
            $rows = [];
            while($row = mysqli_fetch_assoc($qry_result)) {
                $rows[] = $row;
            }
            return $rows;
        }

        private function get_ct_receb($id = null) {
            if($id == null)
                return false;

            $qry = 'SELECT * FROM ct_receb WHERE CODIGO = "'.$id.'"';
            $qry_result = $this->conn->query($qry);
            return mysqli_fetch_assoc($qry_result);
        }

        private function get_condicao($id) {
            $qry = 'SELECT * FROM tipo_condicao_parcela_orcamento WHERE ID = "'.$id.'"';
            $qry_result = $this->conn->query($qry);
            return mysqli_fetch_assoc($qry_result);
        }

        private function get_ct_cartao() {
            //$qry = 'SELECT * FROM ct_cartao WHERE CONTA = "'.$this->conta.'" AND CARTAO = "'.$this->cartao.'"';
            $qry = 'SELECT * FROM ct_cartao WHERE CODIGO = "'.$this->cartao.'"';
            $qry_result = $this->conn->query($qry);
            return mysqli_fetch_assoc($qry_result);
        }

        /*private function insert_ct_receb($cad_orca_id, $payable_with) {
            $qry = 'SELECT * FROM cad_orca WHERE id = "'.$cad_orca_id.'"';
            $qry_result = $this->conn->query($qry);
            $cad_orca = mysqli_fetch_assoc($qry_result);
            
            if($cad_orca["id"]) {
                $par_orca = $this->get_par_orca($cad_orca_id);
                if(count($par_orca) > 0) {
                    foreach ($par_orca as $par) {
                        $count_parc = 0;
                        foreach ($par_orca as $value) {
                            if($value["SERVICO_CONDICOES_ORCA"] == $par["SERVICO_CONDICOES_ORCA"]) {
                                $count_parc++;
                            }
                        }

                        $condicao = $this->get_condicao($par["TIPO_CONDICAO_ID"]);

                        $qry = '
                            SELECT * FROM ct_receb WHERE 
                            DOCUMENTO = "'."X".str_pad($cad_orca_id, 9, '0', STR_PAD_LEFT)."/".str_pad($par["PARCELA"], 2, '0', STR_PAD_LEFT)."-".str_pad($count_parc, 2, '0', STR_PAD_LEFT).'"
                            AND CLIENTE = '.$cad_orca["user_id"].'
                            AND TIPO_RECEBIMENTO = '.$par["TIPO_RECEBIMENTO"].'
                            AND PARCELA = '.$par["PARCELA"];
                        $this->conn->query($qry);
                        $rows = [];
                        while($row = mysqli_fetch_assoc($qry_result)) {
                            $rows[] = $row;
                        }
                            
                        if(count($rows) == 0) {

                            $doc = "X".str_pad($cad_orca_id, 9, '0', STR_PAD_LEFT)."/".str_pad($par["PARCELA"], 2, '0', STR_PAD_LEFT)."-".str_pad($count_parc, 2, '0', STR_PAD_LEFT);

                            if($payable_with == "credit_card" || $payable_with == "pix") {
                                $valor = $par["VALOR"] * $count_parc;
                                $valor_desconto_pontualidade = $par["valor_desconto_pontualidade"] * $count_parc;
                            } else {
                                $valor = $par["VALOR"];
                                $valor_desconto_pontualidade = $par["valor_desconto_pontualidade"];
                            }

                            $qry = 'INSERT INTO ct_receb(FILIAL, DOCUMENTO, CLIENTE, VALOR, VENCIMENTO, TIPO_RECEBIMENTO, VENDEDOR, DATA_LANCAMENTO, DESCONTO, PARCELA, VALORDESCONTOPONTUALIDADE, CARENCIADESCONTOPONTUALIDADE, TIPO_CONDICAO_PARCELA_ID, LINHA_DIGITAVEL, PARCELA_ID, VALOR_PAGO, DATA_PGTO, COMPETENCIA, CENTRO_CUSTO, DEPART) VALUES("'.$cad_orca["filial"].'", "'.$doc.'", "'.$cad_orca["user_id"].'", "'.$valor.'", "'.$par["VENCIMENTO"].'", "'.$par["TIPO_RECEBIMENTO"].'", NULL, "'.date("Y-m-d").'", "0", "'.$par["PARCELA"].'", "'.$valor_desconto_pontualidade.'", "'.$par["carencia_desconto_pontualidade"].'", "'.$par["TIPO_CONDICAO_ID"].'", "", "'.$par["CODIGO_PARCELA_GERADA"].'", "0", NULL, "'.date("m/Y", strtotime($par["VENCIMENTO"])).'", "'.$par["PLANO_FINANCEIRO_ID"].'", "'.$condicao["Id_centrocusto"].'")';
                            
                            $this->conn->query($qry);
                            $id_ct_receb = $this->conn->insert_id;

                            if($payable_with == "credit_card") {
                                $user = $this->get_user_pessoa($cad_orca["user_id"]);
                                $cartao = $this->get_ct_cartao();

                                $qry = 'INSERT INTO cartao_mvtos(CONTA, CARTAO, PARCELA, VALOR_BRUTO, VALOR_LIQ, JUROS, DATA_LANCAMENTO, DATA_CREDITO, BAIXADO, CLIENTE, DOC_CARTAO, CONTROLE_CARTAO, HISTORICO) VALUES("'.$this->conta.'", "'.$this->cartao.'", "'.$par["PARCELA"].'", "'.$par["VALOR"].'", "'.$par["VALOR"].'", "'.$cartao["JUROS"].'", "'.date("Y-m-d").'", "'.date("Y-m-d").'", "S", "'.$cad_orca["user_id"].'", "", "", "Cartão cadastrado na baixa do documento N.º "***" do cliente '.$cad_orca["user_id"].'")';

                                $this->conn->query($qry);
                                $id_cartao_mvto = $this->conn->insert_id;

                                $qry = 'INSERT INTO contas_cartoes(CT_RECEB, CARTAO) VALUES("'.$id_ct_receb.'", "'.$id_cartao_mvto.'")';
                                $this->conn->query($qry);
                            }
                            
                        }
                    }

                    return true;
                }
            }
        }*/

        private function insert_ct_receb($cad_orca_id, $parc_id, $payment_method, $CENTRO_CUSTO, $DEPART) {
            $qry = 'SELECT * FROM cad_orca WHERE id = "'.$cad_orca_id.'"';
            $qry_result = $this->conn->query($qry);
            $cad_orca = mysqli_fetch_assoc($qry_result);
            
            if(@$cad_orca["id"]) {
                $par_orca = $this->get_par_orca($cad_orca_id, $parc_id);
                $par_orca_count = $this->get_par_orca($cad_orca_id);
                if(count($par_orca) > 0) {
                    $par = $par_orca[0];
                    $par_orca_countador = count($par_orca_count);

                    $doc = "X".str_pad($cad_orca_id, 9, '0', STR_PAD_LEFT)."/".str_pad($par["parcela"], 2, '0', STR_PAD_LEFT)."-".str_pad($par_orca_countador, 2, '0', STR_PAD_LEFT);

                    $qry = 'INSERT INTO ct_receb(FILIAL, DOCUMENTO, CLIENTE, VALOR, VENCIMENTO, TIPO_RECEBIMENTO, VENDEDOR, DATA_LANCAMENTO, DESCONTO, PARCELA, VALORDESCONTOPONTUALIDADE, CARENCIADESCONTOPONTUALIDADE, LINHA_DIGITAVEL, PARCELA_ID, VALOR_PAGO, COMPETENCIA, CENTRO_CUSTO, DEPART) VALUES("'.$cad_orca["filial"].'", "'.$doc.'", "'.$cad_orca["user_id"].'", "'.$par['valor'].'", "'.$par["vencimento"].'", "'.$par["tipo_recebimento"].'", "'.$this->vendedor.'", "'.date('Y-m-d').'", "0", "'.$par["parcela"].'", "'.$par['valor_desconto_pontualidade'].'", "'.$par["carencia_desconto_pontualidade"].'", "", "'.$par["id"].'", "0", "'.date("m/Y", strtotime($par["vencimento"])).'", "'.$CENTRO_CUSTO.'", "'.$DEPART.'")';
                    
                    $this->conn->query($qry);
                    $id_ct_receb = $this->conn->insert_id;
                            
                    return array("erro" => false, "data" => $id_ct_receb);
                }
            }
            return array("erro" => true);
        }

        private function update_ct_receb($id, $parcela, $valor_pago, $data_pgto, $payment_method, $doc, $cad_orca_id, $nsu, $fatura_id, $financial_return_date) {
            $qry = 'UPDATE ct_receb SET VALOR_PAGO = "'.$valor_pago.'", DATA_PGTO = "'.$data_pgto.'", SINCRONIZADO = "0" WHERE CODIGO = "'.$id.'"';
            $this->conn->query($qry);

            if($payment_method == "iugu_credit_card_test" || $payment_method == "credit_card" || $payment_method == "iugu_credit_card") {
                $qry = 'SELECT * FROM cad_orca WHERE id = "'.$cad_orca_id.'"';
                $qry_result = $this->conn->query($qry);
                $cad_orca = mysqli_fetch_assoc($qry_result);

                if($cad_orca["id"]) {

                    $user = $this->get_user_pessoa($cad_orca["user_id"]);
                    $cartao = $this->get_ct_cartao();

                    $qry = 'INSERT INTO cartao_mvtos(CONTA, CARTAO, PARCELA, VALOR_BRUTO, VALOR_LIQ, JUROS, DATA_LANCAMENTO, DATA_CREDITO, BAIXADO, CLIENTE, DOC_CARTAO, CONTROLE_CARTAO, HISTORICO) VALUES("'.$this->conta.'", "'.$cartao['CARTAO'].'", "'.$parcela.'", "'.$valor_pago.'", "'.$valor_pago.'", "'.$cartao["JUROS"].'", "'.$data_pgto.'", "'.$financial_return_date.'", "S", "'.$cad_orca["user_id"].'", "'.$nsu.'", "'.$fatura_id.'", "Cartão cadastrado na baixa do documento N.º '.$doc.' do cliente '.$cad_orca["user_id"].'")';

                    $this->conn->query($qry);
                    $id_cartao_mvto = $this->conn->insert_id;

                    $qry = 'INSERT INTO contas_cartoes(CT_RECEB, CARTAO) VALUES("'.$id.'", "'.$id_cartao_mvto.'")';
                    $this->conn->query($qry);
                }
            }
            return true;
        }

        private function update_iugu_invoices($id, $status) {
            $qry = 'UPDATE iugu_invoices SET status = "'.$status.'" WHERE id = "'.$id.'"';
            $this->conn->query($qry);
        }

        private function get_iugu_invoices_info($invoice_id) {
            $qry = 'SELECT i.* FROM iugu_invoices i WHERE i.iugu_id = "'.$invoice_id.'"';
            $qry_result = $this->conn->query($qry);
            return mysqli_fetch_assoc($qry_result);
        }

        private function get_iugu_plan($plano) {
            $qry = 'SELECT i.* FROM iugu_plan i WHERE i.plano = "'.$plano.'"';
            $qry_result = $this->conn->query($qry);
            return mysqli_fetch_assoc($qry_result);
        }

        public function organizar_fila_execucao($fila_execucao) {
            $lista_assinatura_criar = [];
            $lista_assinatura_suspender = [];
            $lista_assinatura_expirar = [];
            $lista_fatura_criar = [];
            $lista_fatura_alterar = [];
            foreach ($fila_execucao as $prox) {
                if($prox["event"] == "subscription.created") {
                    $lista_assinatura_criar[] = $prox;
                }

                elseif($prox["event"] == "subscription.suspended") {
                    $lista_assinatura_suspender[] = $prox;
                }

                elseif($prox["event"] == "subscription.expired") {
                    $lista_assinatura_expirar[] = $prox;
                }

                elseif($prox["event"] == "invoice.created") {
                    $lista_fatura_criar[] = $prox;
                }

                elseif($prox["event"] == "invoice.status_changed") {
                    $lista_fatura_alterar[] = $prox;
                }
            }
            return array(
                "lista_assinatura_criar" => $lista_assinatura_criar,
                "lista_assinatura_suspender" => $lista_assinatura_suspender,
                "lista_assinatura_expirar" => $lista_assinatura_expirar,
                "lista_fatura_criar" => $lista_fatura_criar,
                "lista_fatura_alterar" => $lista_fatura_alterar,
            );
        }

        public function rodar_fila_execucao($fila_execucao) {
            $contagem_lista_exec = 0;

            foreach ($fila_execucao as $tipo => $proximo) {
                foreach ($proximo as $exec) {
                    
                    if($contagem_lista_exec == 15)
                        exit;

                    if($tipo == "lista_assinatura_criar") {
                        $retorno = $this->subscription_created(json_decode($exec["content"], true));
                    }
                    elseif ($tipo == "lista_assinatura_suspender") {
                        $retorno = $this->subscription_suspended(json_decode($exec["content"], true));
                    }
                    elseif ($tipo == "lista_assinatura_expirar") {
                        $retorno = $this->subscription_expired(json_decode($exec["content"], true));
                    }
                    elseif ($tipo == "lista_fatura_criar") {
                        $retorno = $this->invoice_created(json_decode($exec["content"], true));
                    }
                    elseif ($tipo == "lista_fatura_alterar") {
                        $retorno = $this->invoice_status_changed(json_decode($exec["content"], true));
                    }

                    if(! $retorno["erro"])
                        $this->delete_iugu_fila_execucao($exec["id"]);
                    else {
                        $qty_exec  = ((int)$exec["qty_exec"] + 1);
                        
                        $errors = [];
                        if(!is_array(unserialize($exec["errors"])) || empty($exec["errors"]) || count(unserialize($exec["errors"])) == 0) {
                            $errors[] = $retorno["msg"];
                        } else {
                            $errors = unserialize($exec["errors"]);
                            $errors[] = $retorno["msg"];
                        }
                        
                        $this->update_iugu_fila_execucao($exec["id"], $qty_exec, serialize($errors));
                        $this->log($retorno);
                    }

                    $contagem_lista_exec++;
                }
            }
        }

        public function subscription_created($input) {
            $retorno = [];
            if($this->has_unidade()) {
                $iugu_subscriptions_info = $this->get_iugu_subscriptions_info($input["data"]["id"]);
                    
                if(@!isset($iugu_subscriptions_info["iugu_id"])) {
                    $requisicao = $this->requisicao("subscriptions", $input["data"]["id"]);

                    if($requisicao["erro"] == false) {

                        $requisicao_data = json_decode($requisicao["data"], true);

                        if(isset($requisicao_data["errors"])) {
                            $retorno["erro"] = true;
                            $retorno["msg"] = $requisicao_data["errors"];
                            return $retorno;
                        }

                        $requisicao_customer = $this->requisicao("customers", $requisicao_data["customer_id"]);
                        if($requisicao_customer["erro"] == false) {
                            $unidade = $this->get_unidade();

                            //if($this->has_condicao_by_plan($requisicao_data["plan_identifier"], $unidade["id"]) && $this->has_periodico_by_plan($requisicao_data["plan_identifier"], $unidade["id"])) {
                            if($this->has_periodico_by_plan($unidade["id"])) {

                                $requisicao_customer_data = json_decode($requisicao_customer["data"], true);

                                if(isset($requisicao_customer_data["errors"])) {
                                    $retorno["erro"] = true;
                                    $retorno["msg"] = $requisicao_customer_data["errors"];
                                    return $retorno;
                                }

                                //buscando/cadastrando usuário no MaestriaTech
                                if($this->has_user_by_email($requisicao_customer_data["email"])) {
                                    $user = $this->get_user_by_email($requisicao_customer_data["email"]);
                                } else {
                                    $user = $this->insert_user($requisicao_customer_data);
                                    $this->insert_empresas_users($unidade["empresa_id"], $user['id']);
                                    $this->insert_user_unidade($user['id'], $unidade["id"]);
                                    $this->insert_users_groups($user['id'], $this->grupo);
                                }

                                if(!$this->has_iugu_customer($requisicao_customer_data["id"])) {
                                    $this->insert_iugu_customer(array("id" => $user["id"], "iugu_id" => $requisicao_customer_data["id"]));
                                }

                                /*--- alteração realizada para não utilizar mais condição ---*/
                                /***
                                $condicao = $this->get_condicao_by_plan($requisicao_data["plan_identifier"], $unidade["id"]);
                                $periodico = $this->get_periodico_by_plan($requisicao_data["plan_identifier"], $unidade["id"]);

                                $retorno_insert_cad_orca = $this->insert_cad_orca($unidade["id"], $condicao['cond_id'], $condicao["NUM_PARCELAS"], $user['id']);
                                
                                if($retorno_insert_cad_orca["erro"] == false) {
                                    $this->insert_iugu_subscription($retorno_insert_cad_orca["data"], $input["data"]["id"]);
                                    $retorno_insert_cond_orc = $this->insert_condicoes_orcamento($retorno_insert_cad_orca["data"], $condicao);
                                    $this->insert_ser_orca($condicao['cond_id'], $retorno_insert_cad_orca["data"]);
                                    $this->insert_parcelas_servicos_condicoes_orcamento($condicao, $retorno_insert_cond_orc["data"], $retorno_insert_cad_orca["data"]);
                                    $this->insert_pro_orca($condicao['cond_id'], $retorno_insert_cad_orca["data"], $periodico);
                                    $this->insert_parcelas_produtos_condicoes_orcamento($condicao, $retorno_insert_cond_orc["data"], $retorno_insert_cad_orca["data"]);

                                    $this->insert_ct_receb($retorno_insert_cad_orca["data"], $requisicao_data["payable_with"]);
                                }
                                ***/

                                $iugu_plan = $this->get_iugu_plan($requisicao_data["plan_identifier"]);
                                if($iugu_plan["id"]) {
                                    if($iugu_plan["vendedor"] != "")
                                        $this->vendedor = $iugu_plan["vendedor"];
                                }
                                $retorno_insert_cad_orca = $this->insert_cad_orca($unidade["id"], $this->forma_pgto, $user['id']);
                                if($retorno_insert_cad_orca["erro"] == false) {
                                    $this->insert_iugu_subscription($retorno_insert_cad_orca["data"], $input["data"]["id"]);
                                    $this->insert_pro_orca($retorno_insert_cad_orca["data"], $requisicao_data["plan_identifier"], $unidade["id"]);
                                    //$this->insert_ct_receb($retorno_insert_cad_orca["data"], $requisicao_data["payable_with"]);
                                }
                                
                                $retorno["erro"] = false;
                                $retorno["msg"] = 'Orçamento #'.$retorno_insert_cad_orca["data"].' da assinatura #'.$input["data"]["id"].' cadastrado.';
                                return $retorno;
                            } else {
                                $retorno["erro"] = true;
                                $retorno["msg"] = "Plano iugu não encontrado.";
                            }
                        }
                    } else {
                        $retorno["erro"] = true;
                        $retorno["msg"] = "Plano iugu não encontrado.";
                    }
                }
            } else {
                $retorno["erro"] = true;
                $retorno["msg"] = "Unidade não cadastrada ou inativada.";
            }
            return $retorno;
        }

        public function subscription_suspended($input) {
            $retorno = [];
            if($this->has_unidade()) {
                $iugu_subscriptions_info = $this->get_iugu_subscriptions_info($input["data"]["id"]);
                    
                if(@isset($iugu_subscriptions_info["iugu_id"])) {
                    $unidade = $this->get_unidade();
                    
                    $retorno = $this->update_iugu_subscription($iugu_subscriptions_info["id"], "0");

                    if($retorno["erro"] == false) {
                        $retorno = $this->update_cad_orca($iugu_subscriptions_info["cad_orca_id"], $this->get_status_anulado($unidade["id"]), date('Y-m-d'));
                    }

                } else {
                    $retorno["erro"] = true;
                    $retorno["msg"] = "Assinatura não encontrada no MaestriaTech.";
                }
            } else {
                $retorno["erro"] = true;
                $retorno["msg"] = "Unidade não cadastrada ou inativada.";
            }
            return $retorno;
        }

        public function subscription_expired($input) {
            $retorno = [];
            if($this->has_unidade()) {
                $iugu_subscriptions_info = $this->get_iugu_subscriptions_info($input["data"]["id"]);
                    
                if(@isset($iugu_subscriptions_info["iugu_id"])) {
                    $unidade = $this->get_unidade();
                    
                    $retorno = $this->update_iugu_subscription($iugu_subscriptions_info["id"], "0");

                    if($retorno["erro"] == false) {
                        $retorno = $this->update_cad_orca($iugu_subscriptions_info["cad_orca_id"], $this->get_status_anulado($unidade["id"]), date('Y-m-d'));
                    }

                } else {
                    $retorno["erro"] = true;
                    $retorno["msg"] = "Assinatura não encontrada no MaestriaTech.";
                }
            } else {
                $retorno["erro"] = true;
                $retorno["msg"] = "Unidade não cadastrada ou inativada.";
            }
            return $retorno;
        }

        public function invoice_created($input) {
            if($this->has_unidade()) {

                $iugu_subscriptions_info = $this->get_iugu_subscriptions_info($input["data"]["subscription_id"]);

                if(@isset($iugu_subscriptions_info["iugu_id"])) {

                    $iugu_invoices_info = $this->get_iugu_invoices_info($input["data"]["id"]);
                        
                    if(@!isset($iugu_invoices_info["iugu_id"])) {
                        $requisicao = $this->requisicao("invoices", $input["data"]["id"]);

                        if($requisicao["erro"] == false) {

                            $CENTRO_CUSTO = 0;
                            $DEPART = 0;

                            /*** buscar informacoes da assinatura para CENTRO_CUSTO E DEPART ***/
                            $requisicao_assinatura = $this->requisicao("subscriptions", $input["data"]["subscription_id"]);

                            if($requisicao_assinatura["erro"] == false) {

                                $requisicao_assinatura_data = json_decode($requisicao_assinatura["data"], true);

                                if(!isset($requisicao_assinatura_data["errors"])) {
                                    $iugu_plan = $this->get_iugu_plan($requisicao_assinatura_data["plan_identifier"]);
                                    if($iugu_plan["id"]) {
                                        $CENTRO_CUSTO = $iugu_plan["plano_financeiro"];
                                        $DEPART = $iugu_plan["centro_custo"];
                                    }
                                }

                            }
                            /*** ------------------------------------------------------------- ***/

                            $requisicao_data = json_decode($requisicao["data"], true);

                            //exit("<pre>".print_r($requisicao_data,true)."</pre>");

                            if(isset($requisicao_data["errors"])) {
                                $retorno["erro"] = true;
                                $retorno["msg"] = $requisicao_data["errors"];
                                return $retorno;
                            }

                            $par_orca = $this->get_par_orca($iugu_subscriptions_info["cad_orca_id"]);
                            $count_parc = count($par_orca);
                            $parc_id = $this->insert_par_orca($iugu_subscriptions_info["cad_orca_id"], ($count_parc+1), number_format($requisicao_data["total_cents"] / 100, 2, '.', ''), $requisicao_data["due_date"]);

                            if($parc_id) {
                                $retorno_insert_ct_receb = $this->insert_ct_receb($iugu_subscriptions_info["cad_orca_id"], $parc_id, $requisicao_data["payment_method"], $CENTRO_CUSTO, $DEPART);

                                if(! $retorno_insert_ct_receb["erro"]) {
                                    $this->insert_iugu_invoices(array("cad_orca_id" => $iugu_subscriptions_info["cad_orca_id"], "ct_receb_id" => $retorno_insert_ct_receb["data"], "iugu_id" => $input["data"]["id"], "tipo" => "P", "status" => $input["data"]["status"]));

                                    $retorno["erro"] = false;
                                }
                            }
                        } else {
                            $retorno["erro"] = true;
                            $retorno["msg"] = "Plano iugu não encontrado.";
                        }
                    }
                } else {
                    $retorno["erro"] = true;
                    $retorno["msg"] = "Assinatura não encontrada no MaestriaTech.";
                }
            } else {
                $retorno["erro"] = true;
                $retorno["msg"] = "Unidade não cadastrada ou inativada.";
            }

            return $retorno;
        }

        public function invoice_status_changed($input) {
            if($this->has_unidade()) {

                $iugu_invoices_info = $this->get_iugu_invoices_info($input["data"]["id"]);
                    
                if(@isset($iugu_invoices_info["iugu_id"])) {
                    $requisicao = $this->requisicao("invoices", $input["data"]["id"]);

                    if($requisicao["erro"] == false) {

                        $requisicao_data = json_decode($requisicao["data"], true);

                        //exit("<pre>".print_r($requisicao_data,true)."</pre>");

                        if(isset($requisicao_data["errors"])) {
                            $retorno["erro"] = true;
                            $retorno["msg"] = $requisicao_data["errors"];
                            return $retorno;
                        }

                        $ct_receb = $this->get_ct_receb($iugu_invoices_info["ct_receb_id"]);
                        if(@isset($ct_receb["CODIGO"])) {
                            $this->update_iugu_invoices($iugu_invoices_info["id"], $input["data"]["status"]);

                            if($input["data"]["status"] == "paid") {
                                $data_pgto = explode("T", $requisicao_data["paid_at"]);
                                $nsu = "";
                                foreach($requisicao_data["variables"] as $var) {
                                    if($var["variable"] == "payment_data.nsu") {
                                        $nsu = $var["value"];
                                        break;
                                    }
                                }
                                $this->update_ct_receb($ct_receb["CODIGO"], $ct_receb["PARCELA"], number_format($requisicao_data["total_paid_cents"] / 100, 2, '.', ''), $data_pgto[0], $requisicao_data["payment_method"], $ct_receb["DOCUMENTO"], $iugu_invoices_info["cad_orca_id"], $nsu, $input["data"]["id"], $requisicao_data["financial_return_date"]);

                                $iugu_subscriptions_info = $this->get_iugu_subscriptions_info($input["data"]["subscription_id"]);

                                if(@isset($iugu_subscriptions_info["iugu_id"])) {
                                    $unidade = $this->get_unidade();
                                    $retorno_update_assinatura = $this->update_iugu_subscription($iugu_subscriptions_info["id"], "1");

                                    if($retorno_update_assinatura["erro"] == false) {
                                        $this->update_cad_orca($iugu_subscriptions_info["cad_orca_id"], $this->get_status_prematricula($unidade["id"]), NULL);
                                    }
                                }
                            }
                            $retorno["erro"] = false;
                        } else {
                            $retorno["erro"] = true;
                            $retorno["msg"] = "CtReceb não encontrado.";
                        }
                    } else {
                        $retorno["erro"] = true;
                        $retorno["msg"] = "Plano iugu não encontrado.";
                    }
                } else {
                    $retorno["erro"] = true;
                    $retorno["msg"] = "Fatura iugu não encontrada.";
                }

            } else {
                $retorno["erro"] = true;
                $retorno["msg"] = "Unidade não cadastrada ou inativada.";
            }

            return $retorno;
        }
    }