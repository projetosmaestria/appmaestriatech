<?php
// defined('BASEPATH') OR exit('No direct script access allowed');

class Migracao extends Admin_Controller {

    protected $resposta_correcao = array(
    array( // row #0
        'id_aluno' => 5551,
        'id_unidade' => 86,
        'id_professor' => NULL,
        'data_envio' => '2018-10-21 15:10:23',
        'data_correcao' => NULL,
        'status' => 'E',
        'aluno' => 'rony.schmidt@hotmail.com',
    ),
    array( // row #1
        'id_aluno' => 5551,
        'id_unidade' => 88,
        'id_professor' => NULL,
        'data_envio' => '2018-10-21 15:51:35',
        'data_correcao' => NULL,
        'status' => 'E',
        'aluno' => 'rony.schmidt@hotmail.com',
    ),
);


    public function __construct()
    {
        parent::__construct();
        $this->load->model('migracao_model');
    }

    public function index()
    {
        $i = 1;
        foreach($this->users as $user) {
            if($i == 200) 
                exit("fim");
                
            $string = $user["ENDERECO"]." ".$user["NUMERO"]." ".$user["BAIRRO"].", ".$user["CIDADE"]." - ".$user["ESTADO"];
            $string_frmt = str_replace(" ", "+", $string);
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$string_frmt."&key=AIzaSyByk3od_uXvzUEp_VX0iaxrucTPd6OUteU";

            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
            $output = curl_exec($ch); 
            curl_close($ch);

            $output_decode = json_decode($output);

            $separa_nome = explode(" ", $user["NOME"], 2);
            $data_user = array(
                "username" => $user["NOME"],
                "password" => '$2y$08$JmPQJwP2AMquvH30NrqWOOCShxU.weokjVjajmvyTqD2BHYSRI/Xy',
                "email" => $user["EMAIL_FISCAL"],
                "created_on" => time(),
                "active" => "1",
                "first_name" => @$separa_nome[0],
                "last_name" => @$separa_nome[1]
            );
            $id_user = $this->migracao_model->create_user($data_user);
            if($id_user != false) {
                
                $data_pessoa = array(
                    "user_id" => $id_user,
                    "data_nascimento" => $user["DATA_NASC"],
                    "rg" => $user["RG"],
                    "cpf" => $user["CPF"],
                    "endereco" => $user["ENDERECO"],
                    "numero" => $user["NUMERO"],
                    "cep" => $user["CEP"],
                    "telefone" => $user["TELEFONE_RES"],
                    "celular" => $user["CELULAR"],
                    "bairro" => $user["BAIRRO"],
                    "cidade" => $user["CIDADE"],
                    "uf" => $user["ESTADO"],                    
                    "longitude" => $output_decode->results[0]->geometry->location->lng,
                    "latitude" => $output_decode->results[0]->geometry->location->lat
                );
                $id_pessoa = $this->migracao_model->create_pessoa($data_pessoa);

                $data_user_empresa = array(
                    "empresa_id" => 3,
                    "user_id" => $id_user
                );
                $id_user_empresa = $this->migracao_model->create_user_empresa($data_user_empresa);

                $data_user_group = array(
                    "group_id" => 25,
                    "user_id" => $id_user
                );
                $id_user_group = $this->migracao_model->create_user_group($data_user_group);
                echo $id_user;
                echo "<br>";

                $data_user_unid = array(
                    "unidade_id" => 363,
                    "user_id" => $id_user
                );
                $id_user_unid = $this->migracao_model->create_user_unid($data_user_unid);

            } else {                
                $aluno = $this->migracao_model->get_aluno($user['EMAIL_FISCAL']);

                if($aluno->id) {
                    $id_user = $aluno->id;

                    $string = $user["ENDERECO"]." ".$user["NUMERO"]." ".$user["BAIRRO"].", ".$user["CIDADE"]." - ".$user["ESTADO"];
                    $string_frmt = str_replace(" ", "+", $string);
                    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$string_frmt."&key=AIzaSyByk3od_uXvzUEp_VX0iaxrucTPd6OUteU";

                    $ch = curl_init(); 
                    curl_setopt($ch, CURLOPT_URL, $url); 
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
                    $output = curl_exec($ch); 
                    curl_close($ch);

                    $output_decode = json_decode($output);

                    $data_pessoa = array(
                        "user_id" => $aluno->id,
                        "data_nascimento" => $user["DATA_CADASTRO"],
                        "rg" => $user["RG"],
                        "cpf" => $user["CPF"],
                        "endereco" => $user["ENDERECO"],
                        "numero" => $user["NUMERO"],
                        "cep" => $user["CEP"],
                        "telefone" => $user["TELEFONE_RES"],
                        "celular" => $user["CELULAR"],
                        "bairro" => $user["BAIRRO"],
                        "cidade" => $user["CIDADE"],
                        "uf" => $user["ESTADO"],
                        "longitude" => $output_decode->results[0]->geometry->location->lng,
                        "latitude" => $output_decode->results[0]->geometry->location->lat
                    );

                    $id_pessoa = $this->migracao_model->create_pessoa($data_pessoa);

                    $data_user_empresa = array(
                        "empresa_id" => 3,
                        "user_id" => $aluno->id
                    );
                    $id_user_empresa = $this->migracao_model->create_user_empresa($data_user_empresa);

                    $data_user_group = array(
                        "group_id" => 25,
                        "user_id" => $aluno->id
                    );
                    $id_user_group = $this->migracao_model->create_user_group($data_user_group);
                    echo $aluno->id;
                    echo "<br>";

                    $data_user_unid = array(
                        "unidade_id" => 363,
                        "user_id" => $aluno->id
                    );
                    $id_user_unid = $this->migracao_model->create_user_unid($data_user_unid);
                }
            }

            if($id_user != "") {
               $data_contrato = array(                
                'user_id' => $id_user,
                'status' => $user["STATUS"],
                'filial' => $user["FILIAL"],
                'total_servicos' => $user["TOTAL_SERVICOS"],
                'total_geral' => $user["TOTAL_GERAL"],
                'val_serv_manip' => $user["VAL_SERV_MANIP"],
                'data_cadastro' => $user["DATA_CADASTRO"],
                'hora_cadastro' => $user["HORA_CADASTRO"],
                'situacao' => $user["SITUACAO"],
                'sit_separ' => $user["SIT_SEPAR"],
                'data_pedido' => $user["DATA_PEDIDO"],
                'hora_pedido' => $user["HORA_PEDIDO"],
                'qnt_parcelas' => $user["QTDE_PARCELAS"],
                'setor' => $user["SETOR"],
                'tipo' => $user["TIPO"]
               ); 
               $id_contrato = $this->migracao_model->create_contrato($data_contrato);
            }

            $i++;
            
            //exit("<pre>".print_r($aluno->id,true)."</pre>");
        }
    }

    public function incluir_aluno_turma() {
        $i = 1;
        foreach($this->users_unid as $user) {
            if($i == 50) 
                exit("fim");

            $aluno = $this->migracao_model->get_aluno($user['email']);

            if($aluno->id) {
                $unid = $this->migracao_model->get_unidade($user['cnpj']);

                if($unid->id) {
                    $this->migracao_model->insert_aluno_unid(array('user_id'=>$aluno->id, 'unidade_id'=>$unid->id));
                    
                    $turmas = $this->migracao_model->get_turmas($unid->id);

                    if(count($turmas) > 0) {
                        foreach ($turmas as $value) {
                            $this->migracao_model->insert_aluno_turma(array('aluno'=>$aluno->id, 'turma'=>$value->ID));
                        }
                    }
                }
            }
            $i++;
            echo $aluno->id;
            echo "<br>";
            echo $user['email'];
            echo "<br>-";
            echo "<br>";
            //exit("<pre>".print_r($aluno->id,true)."</pre>");
        }
    }

    public function qrcode() {
        // exit("<pre>".print_r($this->qrcodes,true)."</pre>");
        $i = 1;
        foreach($this->qrcodes as $qr) {
            if($i == 100) 
                exit("fim");

            /*if($qr["livro"] == 1)
                $qr["livro"] = 12;
            if($qr["livro"] == 2)
                $qr["livro"] = 13;
            if($qr["livro"] == 3)
                $qr["livro"] = 14;
            if($qr["livro"] == 4)
                $qr["livro"] = 14;
            if($qr["livro"] == 5)
                $qr["livro"] = 14;
            if($qr["livro"] == 10)
                $qr["livro"] = 14;*/
            
            $data = array(
                "livro" => $qr["livro"],
                "qtd" => $qr["qtd"],
                "observacao" => $qr["observacao"],
                "usuario" => 1,
                "kit" => $qr["kit"],
                "data_cadastro" => $qr["data_cadastro"]
            );
            if($id = $this->migracao_model->create_qrcode_info($data)) {
                foreach($qr["qrcodes"] as $q) {
                    /*if($q["livro"] == 7)
                        $q["livro"] = 12;
                    if($q["livro"] == 8)
                        $q["livro"] = 13;
                    if($q["livro"] == 9)
                        $q["livro"] = 14;*/
                    $data_q = array(
                        "codigo" => $q["codigo"],
                        "livro" => $q["livro"],
                        "qrcode_info" => $id,
                        "sincronizado" => "N",
                        "data_cadastro" => $q["data_cadastro"]
                    );
                    $this->migracao_model->create_qrcode($data_q);
                }
            }
            $i++;
            // exit("<pre>".print_r("ok",true)."</pre>");
        }
    }

    public function turmas() {
        $i = 1;
        foreach($this->turmas as $t) {
            if($i == 50) 
                exit("fim");

            $unidade = $this->migracao_model->get_unidade($t["unidade_data"]["cnpj"]);
            
            if($unidade) {
                $unidade_id = $unidade->id;
            } else {
                $t["unidade_data"]["empresa_id"] = 3;
                $unidade_id = $this->migracao_model->create_unidade($t["unidade_data"]);
            }

            $data_turma = array(
                "LIG_ID_STATUS" => $t["LIG_ID_STATUS"],
                "LIG_ID_CURSO" => $t["LIG_ID_CURSO"],
                "LIG_ID_PACOTE" => $t["LIG_ID_PACOTE"],
                "DESCRICAO" => $t["DESCRICAO"],
                "CAPACIDADE_ALUNO" => $t["CAPACIDADE_ALUNO"],
                "OBS" => $t["OBS"],
                "DATA_INICIO" => $t["DATA_INICIO"],
                "DATA_TERMINO" => $t["DATA_TERMINO"],
                "DATA_CADASTRO" => $t["DATA_CADASTRO"],
                "DATA_ALTERACAO" => $t["DATA_ALTERACAO"],
                "unidade" => $unidade_id,
                "ID_MIGRACAO" => $t["ID"]
            );

            if($id = $this->migracao_model->create_turma($data_turma)) {
                foreach($t["turma_horario"] as $h) {
                    $professor = $this->migracao_model->get_aluno($h["professor_data"]["email"]);

                    if($professor) {
                        $professor_id = $professor->id;
                    } else {
                        $separa_nome = explode(" ", $h["professor_data"]["nome"], 2);
                        $data_user = array(
                            "username" => $h["professor_data"]["nome"],
                            "password" => '$2y$08$qQM751zMcgrMSYYh4ihFWeJasG6NsqnRviBjdybsHlFbSS44lx73W',
                            "email" => $h["professor_data"]["email"],
                            "created_on" => time(),
                            "active" => "1",
                            "first_name" => @$separa_nome[0],
                            "last_name" => @$separa_nome[1]
                        );
                        $professor_id = $this->migracao_model->create_user($data_user);
                    }

                    $data_user_empresa = array(
                        "empresa_id" => 3,
                        "user_id" => $professor_id
                    );
                    $id_user_empresa = $this->migracao_model->create_user_empresa($data_user_empresa);

                    $data_user_group = array(
                        "group_id" => 25,
                        "user_id" => $professor_id
                    );
                    $id_user_group = $this->migracao_model->create_user_group($data_user_group);

                    $data_user_unid = array(
                        "unidade_id" => $unidade_id,
                        "user_id" => $professor_id
                    );
                    $id_user_unid = $this->migracao_model->create_user_unid($data_user_unid);

                    $data_h = array(
                        "LIG_ID_TURMA" => $id,
                        "LIG_ID_PROFESSOR" => $professor_id,
                        "DIA_SEMANA" => $h["DIA_SEMANA"],
                        "HORA_INICIAL" => $h["HORA_INICIAL"],
                        "HORA_FINAL" => $h["HORA_FINAL"],
                        "DATA_CADASTRO" => $h["DATA_CADASTRO"]
                    );
                    
                    $this->migracao_model->create_turma_horario($data_h);
                }
            }
            $i++;
            // exit("<pre>".print_r("ok",true)."</pre>");
        }
    }

    public function alunos()
    {
        $i = 1;
        foreach($this->users as $user) {
            if($i == 200) 
                exit("fim");
                
            $aluno = $this->migracao_model->get_aluno($user["email"]);
            $is_professor = false;

            if($aluno) {
                if($this->migracao_model->is_professor($aluno->id))
                    $is_professor = true;
                $aluno_id = $aluno->id;
            } else {
                $separa_nome = explode(" ", $user["nome"], 2);
                $data_user = array(
                    "username" => $user["nome"],
                    "password" => '$2y$08$qQM751zMcgrMSYYh4ihFWeJasG6NsqnRviBjdybsHlFbSS44lx73W',
                    "email" => $user["email"],
                    "created_on" => time(),
                    "active" => "1",
                    "first_name" => @$separa_nome[0],
                    "last_name" => @$separa_nome[1]
                );
                $aluno_id = $this->migracao_model->create_user($data_user);
            }

            if($is_professor == false) {
                $unidade = $this->migracao_model->get_unidade($user["unidade_data"]["cnpj"]);

                if($unidade) {
                    $unidade_id = $unidade->id;
                } else {
                    $user["unidade_data"]["empresa_id"] = 3;
                    $unidade_id = $this->migracao_model->create_unidade($user["unidade_data"]);
                }

                $data_pessoa = array(
                    "user_id" => $aluno_id,
                    "data_nascimento" => $user["data_nascimento"],
                    "rg" => $user["rg"],
                    "cpf" => $user["cpf"],
                    "endereco" => $user["endereco"],
                    "numero" => $user["numero"],
                    "cep" => $user["cep"],
                    "telefone" => $user["telefone"],
                    "celular" => $user["celular"],
                    "bairro" => $user["bairro"],
                    "cidade" => $user["cidade"],
                    "uf" => $user["uf"]
                );
                $id_pessoa = $this->migracao_model->create_pessoa($data_pessoa);

                $data_user_empresa = array(
                    "empresa_id" => 3,
                    "user_id" => $aluno_id
                );
                $id_user_empresa = $this->migracao_model->create_user_empresa($data_user_empresa);

                $data_user_group = array(
                    "group_id" => 25,
                    "user_id" => $aluno_id
                );
                $id_user_group = $this->migracao_model->create_user_group($data_user_group);

                $data_user_unid = array(
                    "unidade_id" => $unidade_id,
                    "user_id" => $aluno_id
                );
                $id_user_unid = $this->migracao_model->create_user_unid($data_user_unid);

            }
            $i++;
            //exit("<pre>".print_r($aluno->id,true)."</pre>");
        }
    }

    public function turma_aluno()
    {
        $i = 1;
        $cont = 0;

        foreach($this->turma_aluno as $ta) {             
            $aluno = $this->migracao_model->get_aluno($ta["email"]);
            $turma = $this->migracao_model->get_turma_by_migracao($ta["turma"]);

            echo "<pre>".print_r($aluno,true)."</pre>";
            echo "<pre>".print_r($turma,true)."</pre>";
            if($aluno && $turma) {
                $this->migracao_model->insert_aluno_turma(array('aluno'=>$aluno->id, 'turma'=>$turma->ID));
            } else {
                exit("<pre>".print_r($ta,true)."</pre>");
            }
            // exit("<pre>".print_r($ta,true)."</pre>");
            $i++;
        }
    }

    public function aluno_livro()
    {
        $i = 1;

        foreach($this->aluno_livro as $al) {        
            //if($i == 200) 
                //exit("fim");

            $aluno = $this->migracao_model->get_aluno($al["email"]);

            if($aluno) {
                $data = array(
                    "aluno" => $aluno->id,
                    "livro" => $al["livro"],
                    "qrcode" => $al["qrcode"],
                    "data_cadastro" => $al["data_cadastro"]
                );
                $this->migracao_model->insert_aluno_livro($data);
            }
            $i++;
            //exit("<pre>".print_r($aluno->id,true)."</pre>");
        }
    }

    public function resposta_aluno()
    {
        $i = 1;

        foreach($this->resposta_aluno as $ra) {        
            if($i == 10000) { 
                echo "<pre>".print_r($ra,true)."</pre>";
                exit("fim");
            }

            $aluno = $this->migracao_model->get_aluno($ra["email"]);

            if($aluno) {
                if($ra["audio"] == "S") {
                    $ra["resposta"] = str_replace("view/upload/respostas/audio/".$ra["aluno"]."/", "upload/respostas/audio/".$aluno->id."/", $ra["resposta"]);
                }
                $ra["aluno"] = $aluno->id;
                $email = $ra["email"];
                unset($ra["email"]);
                $data = $ra;
                $id = $this->migracao_model->insert_resposta_aluno($data);
            } else {
                exit("<pre>".print_r($ra,true)."</pre>");
            }

            $i++;
            // exit("<pre>".print_r($data,true)."</pre>");
        }
    }

    public function resposta_correcao()
    {
        $i = 1;

        foreach($this->resposta_correcao as $ra) {        
            if($i == 100) { 
                echo $email;
                exit("fim");
            }
            // exit("<pre>".print_r($ra,true)."</pre>");
            $aluno = $this->migracao_model->get_aluno($ra["aluno"]);
            //$professor = $this->migracao_model->get_aluno($ra["professor"]);

            //if($aluno && $professor) {
            if($aluno) {
                $ra["id_aluno"] = $aluno->id;
                $ra["id_professor"] = null;
                $email = $ra["email_aluno"];
                unset($ra["aluno"]);
                unset($ra["professor"]);
                $data = $ra;
                $id = $this->migracao_model->insert_resposta_correcao($data);
            } else {
                exit("<pre>".print_r($ra,true)."</pre>");
            }

            $i++;
            //exit("<pre>".print_r($data,true)."</pre>");
        }
    }

    public function vincular_unidade()
    {
        $i = 1;

        foreach($this->users as $u) {        
            // if($i == 100) { 
            //     echo $email;
            //     exit("fim");
            // }
            $aluno = $this->migracao_model->get_aluno($u["email"]);

            if($aluno) {
                $data_user_unid = array(
                    "unidade_id" => 499,
                    "user_id" => $aluno->id
                );
                $this->migracao_model->create_user_unid($data_user_unid);
                echo "<pre>".print_r($aluno,true)."</pre>";
            } 
            // else {
            //     exit("<pre>".print_r($u,true)."</pre>");
            // }

            $i++;
            //exit("<pre>".print_r($data,true)."</pre>");
        }
    }
}