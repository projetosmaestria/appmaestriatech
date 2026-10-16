<?php

        date_default_timezone_set("America/Sao_Paulo");
        $challenge = @$_REQUEST['hub_challenge'];
        $verify_token = @$_REQUEST['hub_verify_token'];

        if($verify_token === 'abc123') {
            echo $challenge;
        }

        //$input = json_decode('{"object":"page","entry":[{"id":"281246795950092","time":1581529002,"changes":[{"value":{"form_id":"1397127113769778","leadgen_id":"195737164963626","created_time":1581529001,"page_id":"281246795950092"},"field":"leadgen"}]}]}',true);
        
        $input = json_decode(file_get_contents('php://input'), true);

        $fp = fopen("upload/webhook.txt", "a");
        $escreve = fwrite($fp, print_r($input, TRUE));
        fclose($fp);
        
        if(isset($input)) {
            $db['default'] = array(
                'dsn'   => '',
                'hostname' => 'mysql.xkeysti.com.br',
                //'hostname' => 'localhost',
                'username' => 'xkeysti1',
                'password' => 'WtUV]!3_T6V,',
                'database' => 'xkeysti1',
                'dbdriver' => 'mysqli',
                'dbprefix' => '',
                'pconnect' => FALSE,
                'cache_on' => FALSE,
                'cachedir' => '',
                'char_set' => 'utf8',
                'dbcollat' => 'utf8_general_ci',
                'swap_pre' => '',
                'encrypt' => FALSE,
                'compress' => FALSE,
                'stricton' => FALSE,
                'failover' => array(),
                'save_queries' => TRUE
            );

            $conn = mysqli_connect($db['default']["hostname"], $db['default']["username"], $db['default']["password"], $db['default']["database"], 3306);

            if (!$conn) {
                echo "Error: Unable to connect to MySQL." . PHP_EOL;
                echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
                echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
                exit;
            }

            $form_id = $input['entry'][0]['changes'][0]['value']['form_id'];
            $ad_id = @$input['entry'][0]['changes'][0]['value']['ad_id'];
            $page_id = $input['entry'][0]['changes'][0]['value']['page_id'];
            $leadgen_id = $input['entry'][0]['changes'][0]['value']['leadgen_id'];


            //$unidade_info = $this->webhooks_model->get_unidade_by_page_id($page_id);
            //$unidade_info = $this->db->get_where("unidades", array("fb_page_id" => $page_id, "fb_status_api" => "S", "empresa_id" => $this->session->userdata('empresa_id')));
            $sql = "SELECT * FROM unidades WHERE fb_page_id = '" . $page_id ."' AND fb_status_api = 'S'";
            $unidade_info_rs = $conn->query($sql);

            if($unidade_info_rs->num_rows > 0) {
                while($unidade = $unidade_info_rs->fetch_assoc()) {             
                    $unidade_info = $unidade; 
                    if($unidade_info['fb_expires_at'] >= date("Y-m-d H:i:s")) {
                        //$token = $this->webhooks_model->get_access_token_unids($unidade_info->fb_app_id, $unidade_info->fb_app_secret);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/oauth/access_token?client_id=" . $unidade_info['fb_app_id'] . "&client_secret=" . $unidade_info['fb_app_secret'] . "&grant_type=fb_exchange_token&fb_exchange_token=" . $unidade_info['fb_access_token']);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                        $result = curl_exec($ch);
                        $refreshData = json_decode($result, true);

                        if($refreshData["access_token"]) {
                            $data["fb_access_token"] = $refreshData["access_token"];
                        }
                        if(@$refreshData["expires_in"]) {
                            $data["fb_expires_at"] = date('Y/m/d H:i:s', strtotime("+".$refreshData["expires_in"]." seconds"));
                        }
                        //$this->webhooks_model->update_unidade($unidade_info->id, $data);
                        //$this->db->update("unidades", $data, array('id' => $unidade_info['id']));

                        $sql = "UPDATE unidades SET fb_access_token = '".$data["fb_access_token"]."', fb_expires_at = '".$data["fb_expires_at"]."' WHERE id = '".$unidade_info['id']."'";
                        $conn->query($sql);
                    }
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v3.3/" . $leadgen_id . "?access_token=" . $unidade_info['fb_access_token'] . "&fields=created_time,id,field_data,ad_name,adset_id");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                    $result = curl_exec($ch);
					
                    $leadData = json_decode($result, true);
                    
                    //exit("<Pre>".print_r($leadData,true)."</pre>");

                    if($ad_id != "") {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v3.3/".$ad_id."?access_token=" . $unidade_info['fb_access_token']."&fields=adset_id");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                        $result = curl_exec($ch);
                        $adData = json_decode($result, true);
                        
                        if($adData["error"]) {
                            $sql = "SELECT e.* FROM eventos_anuncios ea INNER JOIN eventos e ON e.id = ea.evento_id WHERE ea.fb_id = '" . $ad_id ."' AND e.unidade_id = '".$unidade_info['id']."'";
                            $anuncio_existe = $conn->query($sql);
                            if($anuncio_existe->num_rows > 0) {
                                $anuncio_existe_row = $anuncio_existe->fetch_assoc();
                                $anuncio_id = $anuncio_existe_row['id'];
                            }
                        } else {
                            //if($adData["adset_id"]) {
                            if($leadData["adset_id"]) {
                                /*$ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v3.3/".$adData["adset_id"]."?access_token=" . $unidade_info['fb_access_token']."&fields=name");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                                $result = curl_exec($ch);
                                $adSetData = json_decode($result, true);*/
                        
                                $sql = "SELECT e.* FROM eventos e INNER JOIN midias m ON m.id = e.midia_id WHERE e.fb_id = '" . $leadData["adset_id"] ."' AND m.empresa_id = '".$unidade_info['empresa_id']."' AND e.unidade_id = '".$unidade_info['id']."'";
                                $anuncio_existe = $conn->query($sql);

                                if($anuncio_existe->num_rows == 0) {
                                    exit;
                                    $data = array("midia_id" => $unidade_info['fb_midia_id'], "fb_id" => $leadData["adset_id"], "descricao" => utf8_decode($leadData["name"]), "status" => 1);
                                    $sql = "INSERT INTO eventos (unidade_id, midia_id, fb_id, descricao, status) VALUES('".$unidade_info['id']."', '".$unidade_info['fb_midia_id']."', '".$leadData["adset_id"]."', '".utf8_decode($leadData["name"])."', '1')";
                                    $conn->query($sql);
                                    $anuncio_id = $conn->insert_id;
                                } else {
                                    $anuncio_existe_row = $anuncio_existe->fetch_assoc();
                                    $anuncio_id = $anuncio_existe_row['id'];
                                }
                            }
                        }
                    } else {
                        $anuncio_id = $unidade_info['fb_evento_id'];
                    }

                    $lead = [];
                    $nome = "";
                    $email = "";
                    $telefone = "";
                    for($i = 0; $i < count($leadData["field_data"]); $i++)
                    {
                        if($leadData["field_data"][$i]["name"] == "first_name" || $leadData["field_data"][$i]["name"] == "name" || $leadData["field_data"][$i]["name"] == "full_name" || $leadData["field_data"][$i]["name"] == "nome_completo" || $leadData["field_data"][$i]["name"] == "nome") {
                            $nome = $leadData["field_data"][$i]["values"][0];
                        }
                        if($leadData["field_data"][$i]["name"] == "email") {
                            $email = $leadData["field_data"][$i]["values"][0];
                        }
                        if($leadData["field_data"][$i]["name"] == "phone_number" || $leadData["field_data"][$i]["name"] == "telefone/whats" || $leadData["field_data"][$i]["name"] == "telefone/whatsapp" || $leadData["field_data"][$i]["name"] == "whatsapp" || $leadData["field_data"][$i]["name"] == "telefone/whats_app" || $leadData["field_data"][$i]["name"] == "whats_app" || $leadData["field_data"][$i]["name"] == "número_de_whats_app" || $leadData["field_data"][$i]["name"] == "telefone") {
                            $telefone = $leadData["field_data"][$i]["values"][0];
                        }
                    }

                    //$data = array("unidade_id" => $unidade_info['id'], "evento_id" => $anuncio_id, "nome" => utf8_decode($nome), "email" => $email, "celular" => $telefone, "mensagem" => $result, "created_at" => date("Y-m-d H:i:s", strtotime($leadData["created_time"])), "lead_id" => $leadData["id"]);
                    $data = array("unidade_id" => $unidade_info['id'], "evento_id" => $anuncio_id, "nome" => utf8_decode($nome), "email" => $email, "celular" => $telefone, "mensagem" => $result, "created_at" => date("Y-m-d H:i:s"), "lead_id" => $leadData["id"]);
                    

                    $sql = "SELECT * FROM contatos WHERE lead_id = '" . $data["lead_id"] ."' AND unidade_id = '".$data["unidade_id"]."'";
                    $lead_exist = $conn->query($sql);
                    if($lead_exist->num_rows == 0) {
                        //$sql = "INSERT INTO contatos (unidade_id, evento_id, nome, email, celular, mensagem, created_at, lead_id) VALUES('".$unidade_info['id']."', '".$anuncio_id."', '".utf8_decode($nome)."', '".$email."', '".$telefone."', '".$result."', '".date("Y-m-d H:i:s", strtotime($leadData["created_time"]))."', '".$leadData["id"]."')";
                        $sql = "INSERT INTO contatos (unidade_id, evento_id, nome, email, celular, mensagem, created_at, lead_id) VALUES('".$unidade_info['id']."', '".$anuncio_id."', '".utf8_decode($nome)."', '".$email."', '".$telefone."', '".$result.json_encode($input)."', '".date("Y-m-d H:i:s")."', '".$leadData["id"]."')";
                      
                        $conn->query($sql);
                        
                        if($ad_id != "") {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v3.3/'.$ad_id.'/leads?filtering=[{"field":"time_created","operator":"GREATER_THAN","value":"'.strtotime(date("Y-m-d 00:00:00", strtotime("-6 days"))).'"}]&limit=9999&access_token=' . $unidade_info['fb_access_token']);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                            $result = curl_exec($ch);
                            $leads = json_decode($result, true);
                            
                            if(count($leads["data"]) > 0) {
                                foreach($leads["data"] as $l) {
                                    if($l["id"] != $input['entry'][0]['changes'][0]['value']['leadgen_id']) {                                   
                                        $sql = "SELECT id FROM contatos WHERE lead_id = '" . $l["id"] ."' AND unidade_id = '".$data["unidade_id"]."'";
                                        $anuncio_existe = $conn->query($sql);

                                        if($anuncio_existe->num_rows == 0) {
                                            for($i = 0; $i < count($l["field_data"]); $i++)
                                            {
                                                if($l["field_data"][$i]["name"] == "first_name" || $l["field_data"][$i]["name"] == "name" || $l["field_data"][$i]["name"] == "full_name" || $l["field_data"][$i]["name"] == "nome_completo" || $l["field_data"][$i]["name"] == "nome") {
                                                    $nome = $l["field_data"][$i]["values"][0];
                                                }
                                                if($l["field_data"][$i]["name"] == "email") {
                                                    $email = $l["field_data"][$i]["values"][0];
                                                }
                                                if($l["field_data"][$i]["name"] == "phone_number" || $l["field_data"][$i]["name"] == "telefone/whats" || $l["field_data"][$i]["name"] == "telefone/whatsapp" || $l["field_data"][$i]["name"] == "whatsapp" || $l["field_data"][$i]["name"] == "telefone/whats_app" || $l["field_data"][$i]["name"] == "whats_app" || $l["field_data"][$i]["name"] == "número_de_whats_app" || $l["field_data"][$i]["name"] == "telefone") {
                                                    $telefone = $l["field_data"][$i]["values"][0];
                                                }
                                            }
                                        
                                            $sql = "INSERT INTO contatos (unidade_id, evento_id, nome, email, celular, mensagem, created_at, lead_id) VALUES('".$unidade_info['id']."', '".$anuncio_id."', '".utf8_decode($nome)."', '".$email."', '".$telefone."', '".json_encode($l)."', '".date("Y-m-d H:i:s", strtotime($l["created_time"]))."', '".$l["id"]."')";
                                            $conn->query($sql);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //exit;
