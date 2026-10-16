<?php

date_default_timezone_set('America/Sao_Paulo');
// ini_set('display_errors', 1);
// define("BASEPATH", $_SERVER['DOCUMENT_ROOT']."/xkeynet-refatoracao"); //localhost
// define("BASEPATH", $_SERVER['DOCUMENT_ROOT']."/hom"); //desenvol
define("BASEPATH", $_SERVER['DOCUMENT_ROOT']."/v2"); //producao

// define("BASEURL", $_SERVER['SERVER_NAME']."/xkeynet-refatoracao"); //localhost
// define("BASEURL", $_SERVER['SERVER_NAME']."/hom"); //desenvol
define("BASEURL", $_SERVER['SERVER_NAME']."/v2"); //producao

require_once ('conexao.php');
require_once 'abstractServiceClass.php';


// define("CAMINHO_BASE", "/xkeynet-refatoracao"); //localhost
// define("CAMINHO_BASE", "/hom"); //desenvol
define("CAMINHO_BASE", "/v2"); //producao

class ServiceModel extends Service {

    private $conexao = null;
    private $prefix = "";

    public function __construct($request, $origin) {
        parent::__construct($request);
        $this->conexao = new Conexao();
    }

    /* INÍCIO MÉTODOS UTILIZADOS NO SAX */
    /*protected function getUnidadeToken() {
        if($this->method == 'POST'):
            if(empty($_POST['cnpj'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o CNPJ!';
                exit(json_encode($retorno));
            endif;

            $qry = 'SELECT token FROM unidades WHERE cnpj = "' . $_POST['cnpj'] . '" ';

            $mysqli = $this->conexao->getDatabase();
            $qry_result = $mysqli->query($qry);
            $qry_rows = mysqli_num_rows($qry_result);

            if ($qry_rows > 0):
                $dados = mysqli_fetch_assoc($qry_result);
                if($dados['token'] != ""):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 30;
                    $retorno['mensagem'] = "Token encontrado com sucesso!";
                    $retorno['token'] = $dados['token'];
                else:
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 33;
                    $retorno['mensagem'] = "Token não registrado nesta unidade!";
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, tente novamente!';
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }*/

    protected function gravarUsuario() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            /*if(empty($_POST['email'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o E-mail do usuário!';
                exit(json_encode($retorno));
            endif;*/
            if(empty($_POST['tabela'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Tabela refência para grupos do usuário!';
                exit(json_encode($retorno));
            endif;
            /*if($this->validarEmail($_POST['email'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 2;
                $retorno['mensagem'] = 'Endereço de e-mail do usuário não é válido!';
                exit(json_encode($retorno));
            endif;*/

            $ws_ativo = false;
            $mysqli = $this->conexao->getDatabase();

            $qry_admin_pref = 'SELECT a.*, u.id as unidade_id, u.recursos, u.recursos_cliente FROM admin_preferences a INNER JOIN unidades u ON u.empresa_id = a.empresa_id WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_admin_pref = $mysqli->query($qry_admin_pref);
            $qry_rows_admin_pref = mysqli_num_rows($qry_result_admin_pref);
            if ($qry_rows_admin_pref == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Empresa não encontrada, contate o administrador!';
            else:
                $dados_admin_pref = mysqli_fetch_assoc($qry_result_admin_pref);
                
                if(!empty(unserialize($dados_admin_pref["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_admin_pref["recursos"]) as $key => $value) {
                        //Clientes
                        if($key == 0 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    if (count(unserialize($dados_admin_pref['recursos_cliente'])) == 0 || unserialize($dados_admin_pref['recursos_cliente']) == ""):
                        /*$retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = 'O grupo aluno não está referenciado nas preferências da empresa, acesse o MaestriaTech para resolver o problema!';
                        exit(json_encode($retorno));*/
                        $recursos_cliente = array();
                    else:
                        $recursos_cliente = unserialize($dados_admin_pref['recursos_cliente']);
                    endif;

                    // exit("<pre>".print_r($recursos_cliente,true)."</pre>");

                    if ($_POST['user_id'] == 0):
                        //criar usuario
                        /*$qry = 'SELECT * FROM users WHERE email = "'.$_POST['email'].'"';
                        $qry_result = $mysqli->query($qry);
                        $qry_rows = mysqli_num_rows($qry_result);

                        if($qry_rows == 0):*/
                            $qry_insert_user = 'INSERT INTO users (email, active, created_on, activation_code, first_name, last_name) VALUES("'.$_POST['email'].'", "0", "'.time().'", "'.sha1(md5(microtime())).'", "'.@$_POST['nome'].'", "'.@$_POST['sobrenome'].'")';
                            if($qry_result_insert_user = $mysqli->query($qry_insert_user)):
                                $user_id = $mysqli->insert_id;
                                $qry_new_user = 'SELECT * FROM users WHERE id = "'.$user_id.'"';
                                $qry_result_new_user = $mysqli->query($qry_new_user);
                                $dados_new_user = mysqli_fetch_assoc($qry_result_new_user);

                                $qry_insert_user_emp = 'INSERT INTO empresas_users (empresa_id, user_id) VALUES("'.$dados_admin_pref['empresa_id'].'", "'.$user_id.'")';
                                $qry_result_insert_user_emp = $mysqli->query($qry_insert_user_emp);

                                $qry_insert_user_unidade = 'INSERT INTO user_unidade (user_id, unidade_id) VALUES("'.$user_id.'", "'.$dados_admin_pref['unidade_id'].'")';
                                $qry_result_insert_user_unidade = $mysqli->query($qry_insert_user_unidade);

                                if(count($recursos_cliente[$_POST["tabela"]]) > 0) {
                                    foreach ($recursos_cliente[$_POST["tabela"]] as $grupo) {
                                        $qry_insert_user_group = 'INSERT INTO users_groups (user_id, group_id) VALUES("'.$user_id.'", "'.$grupo.'")';
                                        $qry_result_insert_user_group = $mysqli->query($qry_insert_user_group);
                                    }
                                }

                                $qry_insert_pessoa = 'INSERT INTO pessoas (user_id, data_nascimento, telefone, celular, cep, endereco, numero, bairro, cidade, uf, cpf, rg) VALUES("'.$user_id.'", "'.$_POST['data_nascimento'].'", "'.$_POST['telefone'].'", "'.$_POST['celular'].'", "'.$_POST['cep'].'", "'.$_POST['endereco'].'", "'.$_POST['numero'].'", "'.$_POST['bairro'].'", "'.$_POST['cidade'].'", "'.$_POST['uf'].'", "'.@$_POST['cpf'].'", "'.@$_POST['rg'].'")'; 
                                $qry_result_insert_pessoa = $mysqli->query($qry_insert_pessoa);

                                if(@$_POST["enviar_email"] == "S") {
                                    //enviar email -----------------------------------------------
                                    include(BASEPATH . '/application/third_party/phpmailer/PHPMailerAutoload.php');
                                    $mail = new PHPMailer();
                                    $mail->SetLanguage("br");
                                    $mail->CharSet = "utf-8";
                                    $mail->IsSMTP();
                                    $mail->SMTPDebug = false;
                                    $mail->SMTPAuth = true;
                                    $mail->isHTML(true);
                                    
                                    $email_config_db = $this->get_email_config($dados_unid["empresa_id"]);

                                    if($email_config_db['test'] == 1) {
                                        $mail->Host = $email_config_db['host'];
                                        $mail->Port = $email_config_db['port'];
                                        $mail->Username = $email_config_db['user'];
                                        $mail->Password = $email_config_db['pass'];
                                        $mail->SetFrom($email_config_db['from_email'], $email_config_db['from_name']);
                                    } else {
                                        $mail->Host = 'smtp.maestriatech.com.br';
                                        $mail->Port = 587;
                                        $mail->Username = 'contato=maestriatech.com.br';
                                        $mail->Password = 'Alf@7410147';
                                        $mail->SetFrom('contato@maestriatech.com.br', 'MaestriaTech');
                                    }

                                    $mensagem = '<h1>Ativar conta para '.$_POST['email'].'</h1><p>Por favor, clique neste link para <a href="http://'.BASEURL.'/auth/activate/'.$user_id.'/'.$dados_new_user['activation_code'].'">Ativar sua conta</a>.</p>';

                                    $mail->addAddress($_POST['email']);
                                    $mail->Subject = ("MaestriaTech - Ativação de conta");
                                    $mail->msgHTML($mensagem);

                                    $mail->Send();
                                    //enviar email -----------------------------------------------
                                }
                                $retorno['erro'] = FALSE;
                                $retorno['codigo'] = 10;
                                $retorno['mensagem'] = "Usuário gravado com sucesso!";
                                $retorno['data'] = $dados_new_user;
                            endif;
                        /*else:
                            $dados = mysqli_fetch_assoc($qry_result);
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 34;
                            $retorno['mensagem'] = 'E-mail já existente, utilize o método de vincular usuário!';
                            $retorno['data'] = $dados;
                        endif;*/
                    else:
                        //alterar usuario
                        $qry = 'SELECT * FROM users WHERE email = "'.$_POST['email'].'" AND id != "'.$_POST['user_id'].'"';
                        $qry_result = $mysqli->query($qry);
                        $qry_rows = mysqli_num_rows($qry_result);

                        if($qry_rows == 0):
                            $qry_updade_user = 'UPDATE users SET email = "'.$_POST['email'].'" WHERE id = "'.$_POST['user_id'].'"';
                            if($qry_result_updade_user = $mysqli->query($qry_updade_user)):
                                $qry_update_pessoa = 'UPDATE pessoas SET data_nascimento = "'.$_POST['data_nascimento'].'", telefone = "'.$_POST['telefone'].'", celular = "'.$_POST['celular'].'", cep = "'.$_POST['cep'].'", endereco = "'.$_POST['endereco'].'", numero = "'.$_POST['numero'].'", bairro = "'.$_POST['bairro'].'", cidade = "'.$_POST['cidade'].'", uf = "'.$_POST['uf'].'" WHERE user_id = "'.$_POST['user_id'].'"';
                                $qry_result_update_pessoa = $mysqli->query($qry_update_pessoa);

                                $retorno['erro'] = FALSE;
                                $retorno['codigo'] = 10;
                                $retorno['mensagem'] = "Usuário gravado com sucesso!";
                            endif;
                        else:
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 2;
                            $retorno['mensagem'] = 'E-mail já existente por outro usuário!';
                        endif;
                    endif;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recursos 'Clientes - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function vincularUsuario() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['email'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o E-mail do usuário!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['tabela'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Tabela refência para grupos do usuário!';
                exit(json_encode($retorno));
            endif;
            if($this->validarEmail($_POST['email'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 2;
                $retorno['mensagem'] = 'Endereço de e-mail do usuário não é válido!';
                exit(json_encode($retorno));
            endif;

            $ws_ativo = false;
            $mysqli = $this->conexao->getDatabase();

            $qry_admin_pref = 'SELECT a.*, u.id as unidade_id, u.recursos, u.recursos_cliente FROM admin_preferences a INNER JOIN unidades u ON u.empresa_id = a.empresa_id WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_admin_pref = $mysqli->query($qry_admin_pref);
            $qry_rows_admin_pref = mysqli_num_rows($qry_result_admin_pref);
            if ($qry_rows_admin_pref == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Empresa não encontrada, contate o administrador!';
            else:
                $dados_admin_pref = mysqli_fetch_assoc($qry_result_admin_pref);

                if (count(unserialize($dados_admin_pref['recursos_cliente'])) == 0 || unserialize($dados_admin_pref['recursos_cliente']) == ""):
                    /*$retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'O grupo aluno não está referenciado nas preferências da empresa, acesse o MaestriaTech para resolver o problema!';
                    exit(json_encode($retorno));*/
                    $recursos_cliente = array();
                else:
                    $recursos_cliente = unserialize($dados_admin_pref['recursos_cliente']);
                endif;

                if(!empty(unserialize($dados_admin_pref["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_admin_pref["recursos"]) as $key => $value) {
                        //Clientes - SAX para MaestriaTech
                        if($key == 0 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT * FROM users WHERE email = "'.$_POST['email'].'"';
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);

                    if($qry_rows > 0):
                        $dados = mysqli_fetch_assoc($qry_result);

                        $qry_verif_emp = 'SELECT * FROM empresas_users WHERE empresa_id = "'.$dados_admin_pref['empresa_id'].'" AND user_id = "'.$dados['id'].'"';
                        $qry_result_verif_emp = $mysqli->query($qry_verif_emp);
                        if(mysqli_num_rows($qry_result_verif_emp) == 0):
                            $qry_insert_user_emp = 'INSERT INTO empresas_users (empresa_id, user_id) VALUES("'.$dados_admin_pref['empresa_id'].'", "'.$dados['id'].'")';
                            $qry_result_insert_user_emp = $mysqli->query($qry_insert_user_emp);
                        endif;

                        $qry_verif_unidade = 'SELECT * FROM user_unidade WHERE unidade_id = "'.$dados_admin_pref['unidade_id'].'" AND user_id = "'.$dados['id'].'"';
                        $qry_result_verif_unidade = $mysqli->query($qry_verif_unidade);
                        if(mysqli_num_rows($qry_result_verif_unidade) == 0):
                            $qry_insert_user_unidade = 'INSERT INTO user_unidade (user_id, unidade_id) VALUES("'.$dados['id'].'", "'.$dados_admin_pref['unidade_id'].'")';
                            $qry_result_insert_user_unidade = $mysqli->query($qry_insert_user_unidade);
                        endif;

                        if(count($recursos_cliente[$_POST["tabela"]]) > 0) {
                            foreach ($recursos_cliente[$_POST["tabela"]] as $grupo) {
                                $qry_verif_group = 'SELECT * FROM users_groups WHERE group_id = "'.$grupo.'" AND user_id = "'.$dados['id'].'"';
                                $qry_result_verif_group = $mysqli->query($qry_verif_group);
                                if(mysqli_num_rows($qry_result_verif_group) == 0):
                                    $qry_insert_user_group = 'INSERT INTO users_groups (user_id, group_id) VALUES("'.$dados['id'].'", "'.$grupo.'")';
                                    $qry_result_insert_user_group = $mysqli->query($qry_insert_user_group);
                                endif;
                            }
                        }

                        $qry_verif_pessoa = 'SELECT * FROM pessoas WHERE user_id = "'.$dados['id'].'"';
                        $qry_result_verif_pessoa = $mysqli->query($qry_verif_pessoa);

                        $string = $_POST['endereco']." ".$_POST['numero']." ".$_POST['bairro'].", ".$_POST['cidade']." - ".$_POST['uf'];
                        $string_frmt = str_replace(" ", "+", $string);
                        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$string_frmt."&key=AIzaSyByk3od_uXvzUEp_VX0iaxrucTPd6OUteU";

                        $ch = curl_init(); 
                        curl_setopt($ch, CURLOPT_URL, $url); 
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
                        $output = curl_exec($ch); 
                        curl_close($ch);

                        $output_decode = json_decode($output);

                        if(mysqli_num_rows($qry_result_verif_pessoa) == 0):
                            $qry_insert_pessoa = 'INSERT INTO pessoas (user_id, data_nascimento, telefone, celular, cep, endereco, numero, bairro, cidade, uf, longitude, latitude) VALUES("'.$dados['id'].'", "'.$_POST['data_nascimento'].'", "'.$_POST['telefone'].'", "'.$_POST['celular'].'", "'.$_POST['cep'].'", "'.$_POST['endereco'].'", "'.$_POST['numero'].'", "'.$_POST['bairro'].'", "'.$_POST['cidade'].'", "'.$_POST['uf'].'", "'.@$output_decode->results[0]->geometry->location->lng.'", "'.@$output_decode->results[0]->geometry->location->lat.'")'; 
                            $qry_result_insert_pessoa = $mysqli->query($qry_insert_pessoa);
                        else:
                            $qry_update_pessoa = 'UPDATE pessoas SET data_nascimento = "'.$_POST['data_nascimento'].'", telefone = "'.$_POST['telefone'].'", celular = "'.$_POST['celular'].'", cep = "'.$_POST['cep'].'", endereco = "'.$_POST['endereco'].'", numero = "'.$_POST['numero'].'", bairro = "'.$_POST['bairro'].'", cidade = "'.$_POST['cidade'].'", uf = "'.$_POST['uf'].'", longitude = "'.@$output_decode->results[0]->geometry->location->lng.'", latitude = "'.@$output_decode->results[0]->geometry->location->lat.'" WHERE user_id = "'.$dados['id'].'"';
                            $qry_result_update_pessoa = $mysqli->query($qry_update_pessoa);
                        endif;

                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['mensagem'] = "Usuário vinculado com sucesso!";
                        $retorno['data'] = $dados;
                    else:
                        $dados = mysqli_fetch_assoc($qry_result);
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 9;
                        $retorno['mensagem'] = 'Usuário não encontrado!';
                        $retorno['data'] = $dados;
                    endif;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recursos 'Clientes - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getLivro() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Pedagógico - MaestriaTech para SAX
                        if($key == 4 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT l.* FROM livros l 
                        INNER JOIN empresas e ON e.id = l.empresa
                        INNER JOIN unidades u ON u.empresa_id = e.id 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["id"])) {
                        $qry .= " AND l.id = '".$_POST["id"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $livros = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $livros[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $livros;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Pedagógico - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getProfessor() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_admin_pref = 'SELECT a.*, u.id as unidade_id, u.recursos, u.recursos_cliente FROM admin_preferences a INNER JOIN unidades u ON u.empresa_id = a.empresa_id WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_admin_pref = $mysqli->query($qry_admin_pref);
            $qry_rows_admin_pref = mysqli_num_rows($qry_result_admin_pref);
            if ($qry_rows_admin_pref == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_admin_pref = mysqli_fetch_assoc($qry_result_admin_pref);
                
                if(!empty(unserialize($dados_admin_pref["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_admin_pref["recursos"]) as $key => $value) {
                        //Pedagógico - MaestriaTech para SAX
                        if($key == 4 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                    }
                }

                if($ws_ativo == true) {
                    if($dados_admin_pref["grupo_professor"] == "" || $dados_admin_pref["grupo_professor"] == 0) {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Selecione um grupo de professores no MaestriaTech.";
                        mysqli_close($mysqli);
                        return $retorno;
                    }

                    $qry = 'SELECT u.* FROM users u 
                        INNER JOIN users_groups ug ON ug.user_id = u.id
                        WHERE ug.group_id = "' . $dados_admin_pref['grupo_professor'] . '"';

                    if(!empty($_POST["id"])) {
                        $qry .= " AND u.id = '".$_POST["id"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $profs = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $profs[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $profs;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Pedagógico - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getCliente() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_admin_pref = 'SELECT a.*, u.id as unidade_id, u.recursos, u.recursos_cliente FROM admin_preferences a INNER JOIN unidades u ON u.empresa_id = a.empresa_id WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_admin_pref = $mysqli->query($qry_admin_pref);
            $qry_rows_admin_pref = mysqli_num_rows($qry_result_admin_pref);
            if ($qry_rows_admin_pref == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_admin_pref = mysqli_fetch_assoc($qry_result_admin_pref);
                
                if(!empty(unserialize($dados_admin_pref["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_admin_pref["recursos"]) as $key => $value) {
                        //Pedagógico - MaestriaTech para SAX
                        if($key == 4 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                    }
                }

                if($ws_ativo == true) {

                    $qry = 'SELECT c.*, p.tipo, p.nome_razao_social, p.apelido_nome_fantasia, p.data_nascimento, p.rg, p.cpf, p.endereco, p.numero, p.cep, p.telefone, p.celular, p.bairro, p.complemento, p.cidade, p.uf, p.data_cadastro, p.data_recadastro FROM clientes c 
                        INNER JOIN pessoas p ON c.pessoa_id = p.id';

                    if(!empty($_POST["id"])) {
                        $qry .= " WHERE c.id = '".$_POST["id"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $clies = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $clies[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $clies;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Pedagógico - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getUsuario() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_admin_pref = 'SELECT a.*, u.id as unidade_id, u.recursos, u.recursos_cliente, u.empresa_id FROM admin_preferences a INNER JOIN unidades u ON u.empresa_id = a.empresa_id WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_admin_pref = $mysqli->query($qry_admin_pref);
            $qry_rows_admin_pref = mysqli_num_rows($qry_result_admin_pref);
            if ($qry_rows_admin_pref == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_admin_pref = mysqli_fetch_assoc($qry_result_admin_pref);
                
                if(!empty(unserialize($dados_admin_pref["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_admin_pref["recursos"]) as $key => $value) {
                        //Pedagógico - MaestriaTech para SAX
                        if($key == 4 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT DISTINCT u.* FROM users u 
                        INNER JOIN users_groups ug ON ug.user_id = u.id
                        INNER JOIN groups g ON g.id = ug.group_id
                        WHERE g.empresa_id = "' . $dados_admin_pref['empresa_id'] . '"';

                    if(!empty($_POST["id"])) {
                        $qry .= " AND u.id = '".$_POST["id"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $users = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $qry_pessoa = 'SELECT p.* FROM pessoas p 
                                WHERE p.user_id = "' . $row['id'] . '"';

                            $qry_pessoa_result = $mysqli->query($qry_pessoa);
                            $qry_pessoa_rows = mysqli_num_rows($qry_pessoa_result);
                            if($qry_pessoa_rows > 0) {
                                $row_pessoa = mysqli_fetch_assoc($qry_pessoa_result);
                                $row['pessoa'] = $row_pessoa;
								
								$qry_pessoas_email = 'SELECT p.* FROM pessoas_email p WHERE p.pessoa_id = "' . $row_pessoa['id'] . '"';
                                $qry_pessoas_email_result = $mysqli->query($qry_pessoas_email);
                                while($row_pessoas_email = mysqli_fetch_assoc($qry_pessoas_email_result)):
                                    $row['pessoa']['emails_financeiro'][] = $row_pessoas_email;
                                endwhile;
                            }

                            $users[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $users;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Pedagógico - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getTurma() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Pedagógico - MaestriaTech para SAX
                        if($key == 4 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT t.* FROM turma t 
                        INNER JOIN unidades u ON u.id = t.unidade 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["id"])) {
                        $qry .= " AND t.ID = '".$_POST["id"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $turmas = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $turmas[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $turmas;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Pedagógico - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarTurma() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['id'])):
                if(empty($_POST['livro'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Livro!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['descricao'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Descrição!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['capacidade_aluno'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Capacidade Aluno!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['data_inicio'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Data Início!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['data_termino'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Data Término!';
                    exit(json_encode($retorno));
                endif;
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
                
                $qry_verif_livro = 'SELECT l.id FROM livros l WHERE l.id = "'.$_POST["livro"].'" AND l.empresa = "'.$dados_unid["empresa_id"].'"';
                $qry_result_livro = $mysqli->query($qry_verif_livro);
                if(mysqli_num_rows($qry_result_livro) == 0) {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Livro não encontrado.";
                    mysqli_close($mysqli);
                    return $retorno;
                }

                $data_inicio = explode("-", $_POST["data_inicio"]);
                $data_termino = explode("-", $_POST["data_termino"]);
                if(!checkdate($data_inicio[1],$data_inicio[2],$data_inicio[0])) {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Data Início incorreta, verifique o formato (Y-m-d).";
                    mysqli_close($mysqli);
                    return $retorno;
                }
                if(!checkdate($data_termino[1],$data_termino[2],$data_termino[0])) {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Data Término incorreta, verifique o formato (Y-m-d).";
                    mysqli_close($mysqli);
                    return $retorno;
                }
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Pedagógico - SAX para MaestriaTech
                        if($key == 4 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }
                
                if($ws_ativo == true) {
                    $acao = "";
                    if($_POST["id"]) {
                        $acao = "update";
                        $qry = "UPDATE turma SET unidade = '".$dados_unid["id"]."', LIG_ID_PACOTE = '".$_POST["livro"]."', DESCRICAO = '".$_POST["descricao"]."', CAPACIDADE_ALUNO = '".$_POST["capacidade_aluno"]."', OBS = '".$_POST["obs"]."', DATA_INICIO = '".$_POST["data_inicio"]."', DATA_TERMINO = '".$_POST["data_termino"]."', DATA_ALTERACAO = '".date('Y-m-d')."' WHERE ID = '".$_POST["id"]."';";
                    } else {
                        $acao = "insert";
                        $qry = "INSERT INTO turma (unidade, LIG_ID_PACOTE, DESCRICAO, CAPACIDADE_ALUNO, OBS, DATA_INICIO, DATA_TERMINO, DATA_CADASTRO) VALUES ('".$dados_unid["id"]."', '".$_POST["livro"]."', '".$_POST["descricao"]."', '".$_POST["capacidade_aluno"]."', '".$_POST["obs"]."', '".$_POST["data_inicio"]."', '".$_POST["data_termino"]."', '".date('Y-m-d')."');";
                    }
                    if($qry_result = $mysqli->query($qry)) {
                        if($acao == "insert")
                            $turma_id = $mysqli->insert_id;
                        else
                            $turma_id = $_POST["id"];

                        if(count($_POST['horario']) > 0) {
                            $qry_del_hor = "DELETE FROM turma_horario WHERE LIG_ID_TURMA = '".$turma_id."'";
                            if(!$mysqli->query($qry_del_hor)) {
                                $retorno['erro'] = TRUE;
                                $retorno['codigo'] = 1;
                                $retorno['mensagem'] = "Houve um problema ao atualizar os horários da turma.";
                                mysqli_close($mysqli);
                                return $retorno;
                            }
                            $qry_horario = "";
                            foreach ($_POST['horario'] as $horario) {
                                $qry_horario .= "INSERT INTO turma_horario (LIG_ID_TURMA, LIG_ID_PROFESSOR, DIA_SEMANA, HORA_INICIAL, HORA_FINAL, DATA_CADASTRO) VALUES ('".$turma_id."', '".$horario["professor"]."', '".$horario["dia_semana"]."', '".$horario["hora_inicial"]."', '".$horario["hora_final"]."', '".date('Y-m-d')."');";
                            }
                            $mysqli->query($qry_horario);                            
                        }
                        $qry_new_turma = 'SELECT * FROM turma WHERE ID = "'.$turma_id.'"';
                        $qry_result_new_turma = $mysqli->query($qry_new_turma);
                        $dados_new_turma = mysqli_fetch_assoc($qry_result_new_turma);

                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['data'] = $dados_new_turma;
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Houve um problema ao gravar a turma.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Pedagógico - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getRecursosWs() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);

                $ids_recursos = array(
                    0 => "Clientes",
                    1 => "Orçamento/Pedido",
                    2 => "Faturamento",
                    3 => "Financeiro",
                    4 => "Pedagógico",
                    5 => "Contatos"
                );
                $ids_recursos_ = array(
                    1=>"MaestriaTech para SAX",
                    2=>"SAX para MaestriaTech"
                );
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        if(count($value) > 0) {
                            foreach ($value as $v) {
                                $dados[$ids_recursos[$key]][] = $ids_recursos_[$v];
                            }
                        }
                        
                    }
                }

                $retorno['erro'] = FALSE;
                $retorno['codigo'] = 10;
                $retorno['data'] = $dados;
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarStatusSinc() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['data'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Data!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 

                $qry_status = 'SELECT * FROM sincronizacao_status WHERE unidade_id = "' . $dados_unid['id'] . '"';
                $qry_result_status = $mysqli->query($qry_status);
                $qry_rows_status = mysqli_num_rows($qry_result_status);
                if ($qry_rows_status == 0):
                    $qry = "INSERT INTO sincronizacao_status (unidade_id, ultima_sinc) VALUES ('".$dados_unid["id"]."', '".$_POST["data"]."');";
                else:
                    $qry = "UPDATE sincronizacao_status SET ultima_sinc = '".$_POST["data"]."' WHERE unidade_id = '".$dados_unid["id"]."';";
                endif;

                if($qry_result = $mysqli->query($qry)) {
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Houve um problema ao gravar a sincronizacao_status.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getContato() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Contatos - MaestriaTech para SAX
                        if($key == 5 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT c.*, e.descricao as evento_desc, m.id as midia_id, m.descricao as midia_desc, u.token 
                        FROM contatos c 
                        INNER JOIN unidades u ON u.id = c.unidade_id 
                        INNER JOIN eventos e ON e.id = c.evento_id 
                        INNER JOIN midias m ON m.id = e.midia_id 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["id"])) {
                        $qry .= " AND c.id > '".$_POST["id"]."'";
                    }
                    
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $contatos = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $contatos[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $contatos;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recursos 'Contatos - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarContato() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['id'])):
                if(empty($_POST['evento'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Evento!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['nome'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Nome!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['celular'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Celular!';
                    exit(json_encode($retorno));
                endif;
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = true;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                /*if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Contato
                        if($key == 5 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }*/
                
                if($ws_ativo == true) {
                    $acao = "";
                    if(@$_POST["id"]) {
                        $acao = "update";
                        $qry = "UPDATE contatos SET unidade_id = '".$dados_unid["id"]."' ";
                        if($_POST["evento"])
                            $qry .= ", evento_id = '".$_POST["evento"]."'";
                        if($_POST["nome"])
                            $qry .= ", nome = '".$_POST["nome"]."'";
                        if($_POST["email"])
                            $qry .= ", email = '".$_POST["email"]."'";
                        if($_POST["celular"])
                            $qry .= ", celular = '".$_POST["celular"]."'";
                        if($_POST["mensagem"])
                            $qry .= ", mensagem = '".$_POST["mensagem"]."'";
                        if(@$_POST["cidade"])
                            $qry .= ", cidade = '".@$_POST["cidade"]."'";
                        if(@$_POST["estado"])
                            $qry .= ", estado = '".@$_POST["estado"]."'";
                        if(@$_POST["facebook"])
                            $qry .= ", facebook = '".@$_POST["facebook"]."'";
                        if(@$_POST["instagram"])
                            $qry .= ", instagram = '".@$_POST["instagram"]."'";
                        if(@$_POST["idade"])
                            $qry .= ", idade = '".@$_POST["idade"]."'";
                        $qry .= " WHERE id = '".$_POST["id"]."';";
                        
                    } else {
                        $acao = "insert";
                        $qry = "INSERT INTO contatos (unidade_id, evento_id, nome, email, celular, mensagem, visualizado, exportado, created_at, cidade, estado, facebook, instagram, idade) VALUES ('".$dados_unid["id"]."', '".$_POST["evento"]."', '".$_POST["nome"]."', '".$_POST["email"]."', '".$_POST["celular"]."', '".@$_POST["mensagem"]."', 'N', 'N','".date('Y-m-d H:i:s')."', '".@$_POST["cidade"]."', '".@$_POST["estado"]."', '".@$_POST["facebook"]."', '".@$_POST["instagram"]."', '".@$_POST["idade"]."');";
                    }

                    if($qry_result = $mysqli->query($qry)) {
                        if($acao == "insert")
                            $contato_id = $mysqli->insert_id;
                        else
                            $contato_id = $_POST["id"];

                        if(@$_POST["anexos"]) {
                            if(is_array($_POST["anexos"])) {
                                foreach ($_POST["anexos"] as $anexo) {
                                    $encoded_string = trim($anexo["dados"]);
                                    $data = base64_decode($encoded_string);

                                    $f = finfo_open();
                                    $mime_type = finfo_buffer($f, $data, FILEINFO_MIME_TYPE);

                                    if($mime_type != "text/plain" && $mime_type != "application/msword" && $mime_type != "application/x-rar-compressed" && $mime_type != "application/zip" && $mime_type != "application/pdf" && $mime_type != "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
                                        $retorno_anexos[] = "Tipo do anexo ".$anexo["nome"]." não permitido.";
                                    }
                                    elseif(strlen($data) > "2097152") {
                                        $retorno_anexos[] = "Tamanho do anexo ".$anexo["nome"]." excedido.";
                                    } else {

                                        $extension = $this->get_extension($mime_type);

                                        $caminho_arquivo = '/upload/anexo/'.time().$extension;
                                        
                                        if(file_put_contents(BASEPATH.$caminho_arquivo, $data)) {
                                            $retorno_anexos[] = BASEURL.$caminho_arquivo;
                                            $qry = "INSERT INTO contatos_anexo (contato_id, caminho, nome) VALUES ('".$contato_id."', '".$caminho_arquivo."', '".$anexo["nome"]."');";
                                            $qry_result = $mysqli->query($qry);
                                        }
                                    }                             
                                }
                            }
                        }

                        $qry_new_contato = 'SELECT * FROM contatos WHERE id = "'.$contato_id.'"';
                        $qry_result_new_contato = $mysqli->query($qry_new_contato);
                        $dados_new_contato = mysqli_fetch_assoc($qry_result_new_contato);

                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['data'] = $dados_new_contato;
                        if(@count($retorno_anexos) > 0)
                            $retorno['data']['anexos'] = $retorno_anexos;
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Houve um problema ao gravar o contato.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Contato' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }
    
    protected function gravarContatoUnbounce() {
        if($this->method == 'POST'):
            $input = json_decode($_POST['data_json'], true);
            
            if(empty($input["token"])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            
            if(empty($input["id"])):
                if(empty($input["evento"])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Evento!';
                    exit(json_encode($retorno));
                endif;
                if(empty($input["nome"])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Nome!';
                    exit(json_encode($retorno));
                endif;
                if(empty($input["celular"])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Celular!';
                    exit(json_encode($retorno));
                endif;
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = true;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $input["token"][0] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                /*if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Contato
                        if($key == 5 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }*/
                
                if($ws_ativo == true) {
                    $qry_48h = 'SELECT * FROM contatos WHERE evento_id = "'.$input["evento"][0].'" AND unidade_id = "'.$dados_unid["id"].'" AND celular = "'.$input["celular"][0].'" AND (created_at BETWEEN "'.date("Y-m-d H:i:s", strtotime("-48 hours")).'" AND "'.date("Y-m-d H:i:s").'")';
                    
                    $qry_result_48h = $mysqli->query($qry_48h);
                    $dados_48h = mysqli_fetch_assoc($qry_result_48h);
                    $qry_rows_48h = mysqli_num_rows($qry_result_48h);

                    if($qry_rows_48h == 0) {
                        $acao = "";
                        if($input["id"]) {
                            $acao = "update";
                            $qry = "UPDATE contatos SET unidade_id = '".$dados_unid["id"]."', evento_id = '".$input["evento"][0]."', nome = '".$input["nome"][0]."', email = '".$input["email"][0]."', celular = '".$input["celular"][0]."', mensagem = '".$input["mensagem"][0]."', idade = '".$input["idade"][0]."' WHERE id = '".$input["id"][0]."';";
                        } else {
                            $acao = "insert";
                            $qry = "INSERT INTO contatos (unidade_id, evento_id, nome, email, celular, mensagem, idade, cidade, visualizado, exportado, created_at) VALUES ('".$dados_unid["id"]."', '".$input["evento"][0]."', '".$input["nome"][0]."', '".$input["email"][0]."', '".$input["celular"][0]."', '".$input["mensagem"][0]."', '".$input["idade"][0]."', '".$input["cidade"][0]."', 'N', 'N','".date('Y-m-d H:i:s')."');";
                        }

                        if($qry_result = $mysqli->query($qry)) {
                            if($acao == "insert")
                                $contato_id = $mysqli->insert_id;
                            else
                                $contato_id = $input["id"][0];

                            $qry_new_contato = 'SELECT * FROM contatos WHERE id = "'.$contato_id.'"';
                            $qry_result_new_contato = $mysqli->query($qry_new_contato);
                            $dados_new_contato = mysqli_fetch_assoc($qry_result_new_contato);

                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 10;
                            $retorno['data'] = $dados_new_contato;
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Houve um problema ao gravar o contato.";
                        }
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Contato já incluido a menos de 48 horas.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Contato' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarContatoZapier() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['id'])):
                if(empty($_POST['nome'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Nome!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['celular'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Celular!';
                    exit(json_encode($retorno));
                endif;
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = true;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
                
                if($ws_ativo == true) {
                    $qry_contato = "SELECT id FROM contatos WHERE lead_id = '" . $_POST["lead_id"] ."'";
                    $qry_result_contato = $mysqli->query($qry_contato);
                    $qry_rows_contato = mysqli_num_rows($qry_result_contato);
                    if ($qry_rows_contato == 0):                    
                        $qry_evento = "SELECT e.* FROM eventos e INNER JOIN midias m ON m.id = e.midia_id WHERE e.fb_id = '" . $_POST["adset_id"] ."' AND m.empresa_id = '".$dados_unid['empresa_id']."' AND e.unidade_id = '".$dados_unid['id']."'";
                        
                        $qry_result_evento = $mysqli->query($qry_evento);
                        $qry_rows_evento = mysqli_num_rows($qry_result_evento);
                        if ($qry_rows_evento == 0):
                            $insert_evento = "INSERT INTO eventos (unidade_id, midia_id, fb_id, descricao, status) VALUES('".$dados_unid['id']."', '".$dados_unid['fb_midia_id']."', '".$_POST["adset_id"]."', '".utf8_decode($_POST["adset_name"])."', '1')";
                            $mysqli->query($insert_evento);
                            $evento_id = $mysqli->insert_id;
                        else:
                            $dados_evento = mysqli_fetch_assoc($qry_result_evento);
                            $evento_id = $dados_evento["id"];
                        endif;

                        
                        $qry = "INSERT INTO contatos (unidade_id, evento_id, nome, email, celular, mensagem, visualizado, exportado, created_at, cidade, estado, facebook, instagram, idade, lead_id) VALUES ('".$dados_unid["id"]."', '".$evento_id."', '".$_POST["nome"]."', '".$_POST["email"]."', '".$_POST["celular"]."', 'Contato recebido da integração.', 'N', 'N','".date('Y-m-d H:i:s')."', '".@$_POST["cidade"]."', '".@$_POST["estado"]."', '".@$_POST["facebook"]."', '".@$_POST["instagram"]."', '".@$_POST["idade"]."', '".$_POST["lead_id"]."');";

                        if($qry_result = $mysqli->query($qry)) {
                            $contato_id = $mysqli->insert_id;

                            $qry_new_contato = 'SELECT * FROM contatos WHERE id = "'.$contato_id.'"';
                            $qry_result_new_contato = $mysqli->query($qry_new_contato);
                            $dados_new_contato = mysqli_fetch_assoc($qry_result_new_contato);

                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 10;
                            $retorno['data'] = $dados_new_contato;
                            if(count($retorno_anexos) > 0)
                                $retorno['data']['anexos'] = $retorno_anexos;
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Houve um problema ao gravar o contato.";
                        }
                    else:
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Contato já existente.";
                    endif;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Contato' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function enviarSms() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['from'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o campo From!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['to'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o campo To!';
                exit(json_encode($retorno));
            endif;
            if(empty(trim(@$_POST['msg']))):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o campo Msg!';
                exit(json_encode($retorno));
            endif;
            if(count(trim(@$_POST['msg'])) > 160):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o campo Msg com até 160 caracteres!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Contato
                        if($key == 5 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    $formated_schedule = "";
                    if(@$_POST["schedule"]) {
                        $format_schedule = explode("T", @$_POST["schedule"]);
                        $formated_schedule = $format_schedule[0]." ".$format_schedule[1];
                    }

                    $qry = "INSERT INTO envios_sms (unidade_id, remetente, telefone, mensagem, data_agendamento) VALUES ('".$dados_unid["id"]."', '".$_POST["from"]."', '".$_POST["to"]."', '".$_POST["msg"]."', '".$formated_schedule."');";
                    
                    if($qry_result = $mysqli->query($qry)) {
                        $sms_id = $mysqli->insert_id;
                        
                        $data = array();
                        $data['from'] = $_POST["from"];
                        $data['to'] = $this->formatCelApi($_POST['to']);
                        $data['msg'] = trim($_POST['msg']);
                        $data['schedule'] = @$_POST['schedule'];
                        $data['id'] = 'sw_'.$sms_id;

                        $retorno_call = $this->zenviaCallSingle("send-sms", $this->createSingleJsonSms($data));

                        if($retorno_call["erro"] == false) {
                            $data_decode = json_decode($retorno_call["data"], true);

                            $qry_update = "UPDATE envios_sms SET status_code = '".$data_decode["sendSmsResponse"]["statusCode"]."', status_description = '".$this->getCodesDescription($data_decode["sendSmsResponse"]["statusCode"], 'status')."', detail_code = '".$data_decode["sendSmsResponse"]["detailCode"]."', detail_description = '".$this->getCodesDescription($data_decode["sendSmsResponse"]["detailCode"], 'detail')."' WHERE id = '".$sms_id."';";
                            
                            $qry_update_result = $mysqli->query($qry_update);

                            $select = "SELECT * FROM envios_sms WHERE id = ".$sms_id;
                            $qry_select_result = $mysqli->query($select);
                            $dados_envio = mysqli_fetch_assoc($qry_select_result);
                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 10;
                            $retorno['data'] = $dados_envio;
                        } else {
                            $qry_del = "DELETE FROM envios_sms WHERE id = '".$sms_id."'";
                            $mysqli->query($qry_del);

                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = $retorno_call["mensagem"];
                        }

                        // exit("<pre>".print_r($retorno_call,true)."</pre>");                        
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Houve um problema ao enviar o sms.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Contato' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function enviarMultiSms() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['lista'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a lista!';
                exit(json_encode($retorno));
            else:
                foreach ($_POST['lista'] as $value) {
                    if(empty($value['from'])):
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = 'Por favor preencha o campo From!';
                        exit(json_encode($retorno));
                    endif;
                    if(empty($value['to'])):
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = 'Por favor preencha o campo To!';
                        exit(json_encode($retorno));
                    endif;
                    if(empty(trim(@$value['msg']))):
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = 'Por favor preencha o campo Msg!';
                        exit(json_encode($retorno));
                    endif;
                    if(count(trim(@$value['msg'])) > 160):
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = 'Por favor preencha o campo Msg com até 160 caracteres!';
                        exit(json_encode($retorno));
                    endif;
                }
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Contato
                        if($key == 5 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    $data_sms = array();
                    foreach ($_POST['lista'] as $value) {
                        $formated_schedule = "";
                        if(@$value["schedule"]) {
                            $format_schedule = explode("T", @$value["schedule"]);
                            $formated_schedule = $format_schedule[0]." ".$format_schedule[1];
                        }

                        $qry = "INSERT INTO envios_sms (unidade_id, remetente, telefone, mensagem, data_agendamento) VALUES ('".$dados_unid["id"]."', '".$value["from"]."', '".$value["to"]."', '".$value["msg"]."', '".$formated_schedule."');";
                        
                        if($qry_result = $mysqli->query($qry)) {
                            $sms_id = $mysqli->insert_id;

                            $data_sms[] = array(
                                "from" => $value['from'],
                                "to" => $this->formatCelApi($value['to']),
                                "schedule" => @$value['schedule'],
                                "msg" => trim($value['msg']),
                                "callbackOption" => "ALL",
                                "id" => 'sw_'.$sms_id
                            );
                        }
                    }

                    $retorno_call = $this->zenviaCallSingle("send-sms-multiple", $this->createMultiJsonSms($data_sms));
                    
                    if($retorno_call["erro"] == false) {
                        $data_decode = json_decode($retorno_call["data"], true);

                        $dados_envio = array();
                        foreach ($data_sms as $value) {
                            $sms_id = str_replace("sw_", "", $value["id"]);
                            $qry_update = "UPDATE envios_sms SET status_code = '".$data_decode["sendSmsResponse"]["statusCode"]."', status_description = '".$this->getCodesDescription($data_decode["sendSmsResponse"]["statusCode"], 'status')."', detail_code = '".$data_decode["sendSmsResponse"]["detailCode"]."', detail_description = '".$this->getCodesDescription($data_decode["sendSmsResponse"]["detailCode"], 'detail')."' WHERE id = '".$sms_id."';";
                            
                            $qry_update_result = $mysqli->query($qry_update);

                            $select = "SELECT * FROM envios_sms WHERE id = ".$sms_id;
                            $qry_select_result = $mysqli->query($select);
                            $dados_envio[] = mysqli_fetch_assoc($qry_select_result);
                        }

                        
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['data'] = $dados_envio;
                    } else {
                        foreach ($data_sms as $value) {
                            $sms_id = str_replace("sw_", "", $value["id"]);
                            $qry_del = "DELETE FROM envios_sms WHERE id = '".$sms_id."'";
                            $mysqli->query($qry_del);
                        }

                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = $retorno_call["mensagem"];
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Contato' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getMidia() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Contatos - MaestriaTech para SAX
                        if($key == 5 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    if(!empty($_POST["id"])) {
                        $qry = 'SELECT m.* 
                            FROM midias m 
                            WHERE m.id = "'.$_POST["id"].'"';
                    } else {
                        $qry = 'SELECT m.*, u.token 
                            FROM midias m 
                            INNER JOIN unidades u ON u.empresa_id = m.empresa_id 
                            WHERE u.token = "' . $_POST['token'] . '"';
                    }
                    if(!empty($_POST["data_atualizacao"])) {
                        $qry .= " AND m.updated_at >= '".$_POST["data_atualizacao"]."'";
                    }
                    
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $midias = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $midias[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $midias;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recursos 'Contatos - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getEvento() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Contatos - MaestriaTech para SAX
                        if($key == 5 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    if(!empty($_POST["id"])) {
                        $qry = "SELECT e.* FROM eventos e WHERE e.id = '".$_POST["id"]."'";
                    } else {
                        $qry = 'SELECT e.*, u.token FROM eventos e INNER JOIN unidades u ON u.id = e.unidade_id WHERE u.token = "' . $_POST['token'] . '"';
                    }
                    if(!empty($_POST["data_atualizacao"])) {
                        $qry .= " AND e.updated_at >= '".$_POST["data_atualizacao"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $eventos = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $eventos[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $eventos;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recursos 'Contatos - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getDisciplina() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Pedagógico - MaestriaTech para SAX
                        if($key == 4 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT d.* FROM disciplina d 
                        INNER JOIN curso_disciplina cd ON d.ID_DISCIPLINA = cd.LIG_ID_DISCIPLINA
                        INNER JOIN curso c ON cd.LIG_ID_CURSO = c.ID_CURSO
                        INNER JOIN empresas e ON e.id = c.empresa_id
                        INNER JOIN unidades u ON u.empresa_id = e.id 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["id"])) {
                        $qry .= " AND d.ID_DISCIPLINA = '".$_POST["id"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $livros = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $livros[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $livros;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Pedagógico - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarPauta() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['turma'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Turma!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['professor'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Professor!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['disciplina'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Disciplina!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['situacao_pauta'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Situação Pauta!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['situacao_disciplina'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Situação Disciplina!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
                
                $qry_verif_prof = 'SELECT u.id FROM users u INNER JOIN empresas_users eu ON eu.user_id = u.id WHERE u.id = "'.$_POST["professor"].'" AND eu.empresa_id = "'.$dados_unid["empresa_id"].'"';
                $qry_result_prof = $mysqli->query($qry_verif_prof);
                if(mysqli_num_rows($qry_result_prof) == 0) {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Professor não encontrado.";
                    mysqli_close($mysqli);
                    return $retorno;
                }

                $qry_verif_turma = 'SELECT t.ID FROM turma t WHERE t.ID = "'.$_POST["turma"].'" AND t.unidade = "'.$dados_unid["id"].'"';
                $qry_result_turma = $mysqli->query($qry_verif_turma);
                if(mysqli_num_rows($qry_result_turma) == 0) {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Turma não encontrada.";
                    mysqli_close($mysqli);
                    return $retorno;
                }

                /*$qry_verif_disc = 'SELECT d.ID_DISCIPLINA FROM disciplina d WHERE d.ID_DISCIPLINA = "'.$_POST["disciplina"].'" AND d.empresa_id = "'.$dados_unid["empresa_id"].'"';
                $qry_result_disc = $mysqli->query($qry_verif_disc);
                if(mysqli_num_rows($qry_result_disc) == 0) {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Disciplina não encontrada.";
                    mysqli_close($mysqli);
                    return $retorno;
                }*/

 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Pedagógico - SAX para MaestriaTech
                        if($key == 4 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }
                
                if($ws_ativo == true) {
                    $acao = "";
                    if($_POST["id"]) {
                        $acao = "update";
                        $qry = "UPDATE pauta SET LIG_ID_TURMA = '".$_POST["turma"]."', LIG_ID_PROFESSOR = '".$_POST["professor"]."', LIG_ID_DISCIPLINA = '".$_POST["disciplina"]."', SITUACAO_PAUTA = '".$_POST["situacao_pauta"]."', SITUACAO_DISCIPLINA = '".$_POST["situacao_disciplina"]."', JUSTIFICATIVA = '".$_POST["justificativa"]."', OBS = '".$_POST["obs"]."', AULA_DATA = '".$_POST["aula_data"]."', AULA_TEMPO = '".$_POST["aula_tempo"]."', FREQUENTE = '".$_POST["frequente"]."', EVASAO = '".$_POST["evasao"]."', DATA_ALTERACAO = '".date('Y-m-d')."', AULA_HORA = '".$_POST["aula_hora"]."' WHERE ID_PAUTA = '".$_POST["id"]."';";
                    } else {
                        $acao = "insert";
                        $qry = "INSERT INTO pauta (LIG_ID_TURMA, LIG_ID_PROFESSOR, LIG_ID_DISCIPLINA, SITUACAO_PAUTA, SITUACAO_DISCIPLINA, JUSTIFICATIVA, OBS, AULA_DATA, AULA_TEMPO, FREQUENTE, EVASAO, HORA_CADASTRO, DATA_CADASTRO, DATA_ALTERACAO, AULA_HORA) VALUES ('".$_POST["turma"]."', '".$_POST["professor"]."', '".$_POST["disciplina"]."', '".$_POST["situacao_pauta"]."', '".$_POST["situacao_disciplina"]."', '".$_POST["justificativa"]."', '".$_POST["obs"]."', '".$_POST["aula_data"]."', '".$_POST["aula_tempo"]."', '".$_POST["frequente"]."', '".$_POST["evasao"]."', '".date('H:i:s')."', '".date('Y-m-d')."', '".date('Y-m-d')."', '".$_POST["aula_hora"]."');";
                    }
                    if($qry_result = $mysqli->query($qry)) {
                        if($acao == "insert")
                            $pauta_id = $mysqli->insert_id;
                        else
                            $pauta_id = $_POST["id"];

                        $qry_new_pauta = 'SELECT * FROM pauta WHERE ID_PAUTA = "'.$pauta_id.'"';
                        $qry_result_new_pauta = $mysqli->query($qry_new_pauta);
                        $dados_new_pauta = mysqli_fetch_assoc($qry_result_new_pauta);

                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['data'] = $dados_new_pauta;
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Houve um problema ao gravar a pauta.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Pedagógico - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getProduto() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento/Pedido - MaestriaTech para SAX
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT p.* FROM cad_prod p 
                        INNER JOIN unidades u ON u.empresa_id = p.empresa_id 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["codigo"])) {
                        $qry .= " AND p.id = '".$_POST["codigo"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $produtos = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $row["APLICACAO"] = base64_encode($row["APLICACAO"]);
                            $produtos[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $produtos;
                    // exit("<pre>".print_r($produtos,true)."</pre>");
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarProduto() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            // if(empty($_POST['grupo'])):
            //     $retorno['erro'] = TRUE;
            //     $retorno['codigo'] = 1;
            //     $retorno['mensagem'] = 'Por favor preencha o Grupo!';
            //     exit(json_encode($retorno));
            // endif;
            if(empty($_POST['descricao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Descrição!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['data_cadastro'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Data do Cadastro!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['ativo'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Ativo!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['peso'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Peso!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento/Pedido - SAX para MaestriaTech
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    $acao = "";
                    if(@$_POST["id"]) {
                        $acao = "update";
                        $qry = "UPDATE cad_prod SET GRUPO = '".@$_POST["grupo"]."', SUBGRUPO = '".@$_POST["subgrupo"]."', DESCRICAO = '".$_POST["descricao"]."', NUM_ORIGINAL = '".@$_POST["num_original"]."', OUTRO_COD = '".@$_POST["outro_cod"]."', ATIVO = '".$_POST["ativo"]."', OBSERVACOES1 = '".@$_POST["obs"]."', PESO = '".$_POST["peso"]."', CUSTO_MEDIO = '".@$_POST["custo_medio"]."', TIPO = '".@$_POST["tipo"]."', VOLUME_MIN_VENDA = '".@$_POST["volume_min_venda"]."' WHERE CODIGO = '".$_POST["id"]."';";
                    } else {
                        $acao = "insert";
                        $qry = "INSERT INTO cad_prod (empresa_id, GRUPO, SUBGRUPO, DESCRICAO, NUM_ORIGINAL, OUTRO_COD, OBSERVACOES1, ATIVO, PESO, DATA_CADASTRO, SINCRONIZADO, CUSTO_MEDIO, TIPO, VOLUME_MIN_VENDA) VALUES ('".$dados_unid["empresa_id"]."', '".@$_POST["grupo"]."', '".@$_POST["subgrupo"]."', '".$_POST["descricao"]."', '".@$_POST["num_original"]."', '".@$_POST["outro_cod"]."', '".@$_POST["obs"]."', '".$_POST["ativo"]."', '".$_POST["peso"]."', '".$_POST["data_cadastro"]."', '1', '".@$_POST["custo_medio"]."', '".@$_POST["tipo"]."', '".@$_POST["volume_min_venda"]."');";
                    }

                    if($qry_result = $mysqli->query($qry)) {
                        if($acao == "insert")
                            $prod_id = $mysqli->insert_id;
                        else
                            $prod_id = $_POST["id"];


                        $qry_del_pgru = "DELETE FROM cad_prod_pgru WHERE cad_prod_id = '".$prod_id."'";
                        $mysqli->query($qry_del_pgru);

                        if(@$_POST["grupo"]) {
                            $qry_pgru = 'SELECT * FROM cad_pgru WHERE empresa_id = '.$dados_unid["empresa_id"].' AND codigo_sax = "'.$_POST["grupo"].'" AND parent_id = 0';
                            $qry_result_pgru = $mysqli->query($qry_pgru);
                            $qry_rows_pgru = mysqli_num_rows($qry_result_pgru);

                            if($qry_rows_pgru > 0) {
                                $dados_pgru = mysqli_fetch_assoc($qry_result_pgru);
                                $qry_insert_pgru = "INSERT INTO cad_prod_pgru (cad_prod_id, cad_pgru_id) VALUES (".$prod_id.", ".$dados_pgru["id"].")";
                                $mysqli->query($qry_insert_pgru);

                                if(@$_POST["subgrupo"]) {
                                    $qry_subgru = 'SELECT * FROM cad_pgru WHERE empresa_id = '.$dados_unid["empresa_id"].' AND codigo_sax = "'.$_POST["subgrupo"].'" AND parent_id != 0';
                                    $qry_result_subgru = $mysqli->query($qry_subgru);
                                    $qry_rows_subgru = mysqli_num_rows($qry_result_subgru);

                                    if($qry_rows_subgru > 0) {
                                        $dados_subgru = mysqli_fetch_assoc($qry_result_subgru);
                                        $qry_insert_subgru = "INSERT INTO cad_prod_pgru (cad_prod_id, cad_pgru_id) VALUES (".$prod_id.", ".$dados_subgru["id"].")";
                                        $mysqli->query($qry_insert_subgru);
                                    }
                                }
                            }
                            
                        }

                        $qry_new_prod = 'SELECT * FROM cad_prod WHERE CODIGO = "'.$prod_id.'"';
                        $qry_result_new_prod = $mysqli->query($qry_new_prod);
                        $dados_new_prod = mysqli_fetch_assoc($qry_result_new_prod);

                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['data'] = $dados_new_prod;
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Houve um problema ao gravar o produto.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarGrupo() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['tipo'])):
                if($_POST['tipo'] == '2') {
                    if(empty($_POST['pai'])):
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = 'Por favor preencha o Menu Pai!';
                        exit(json_encode($retorno));
                    endif;
                }
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Tipo!'; //ENUM('1','2') 1 = grupo; 2 = subgrupo
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['descricao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Descrição!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['status'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Status!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['codigo_sax'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Código SAX!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento/Pedido - SAX para MaestriaTech
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    $acao = "";
                    $pai_existente = true;

                    $qry_pgru = 'SELECT * FROM cad_pgru WHERE codigo_sax = "' . $_POST['codigo_sax'] . '" AND empresa_id = "' . $dados_unid["empresa_id"] . '" AND ';

                    if($_POST["tipo"] == 1)
                        $qry_pgru .= 'parent_id = 0';
                    else {
                        $qry_pgru .= 'parent_id != 0';

                        $qry_pai = 'SELECT * FROM cad_pgru WHERE codigo_sax = "' . $_POST['pai'] . '" AND empresa_id = "' . $dados_unid["empresa_id"] . '" AND parent_id = 0';
                        $qry_result_pai = $mysqli->query($qry_pai);
                        $qry_rows_pai = mysqli_num_rows($qry_result_pai);

                        if($qry_rows_pai > 0) {
                            $dados_pai = mysqli_fetch_assoc($qry_result_pai);
                        } else {
                            $pai_existente = false;
                        }
                    }

                    if($pai_existente) {
                        $qry_result_pgru = $mysqli->query($qry_pgru);
                        $qry_rows_pgru = mysqli_num_rows($qry_result_pgru);

                        if($qry_rows_pgru > 0) {
                            $dados_pgru = mysqli_fetch_assoc($qry_result_pgru);
                            $acao = "update";
                            $qry = "UPDATE cad_pgru SET descricao = '".@$_POST["descricao"]."', status = '".@$_POST["status"]."', sinc = 'S' ";
                            
                            if($_POST["tipo"] == 2) {
                                $qry .= ", parent_id = ".$dados_pai["id"];
                            }
                            
                            $qry .= " WHERE id = '" . $dados_pgru["id"] . "';";
                        } else {
                            $acao = "insert";
                            $qry = "INSERT INTO cad_pgru (empresa_id, codigo_sax, descricao, status, sinc, parent_id) VALUES ('".$dados_unid["empresa_id"]."', '".$_POST["codigo_sax"]."', '".$_POST["descricao"]."', '".@$_POST["status"]."', 'S'";

                            if($_POST["tipo"] == 2) {
                                $qry .= ", ".$dados_pai["id"];
                            } else {
                                $qry .= ", 0";
                            }

                            $qry .= ");";
                        }

                        if($qry_result = $mysqli->query($qry)) {
                            if($acao == "insert")
                                $pgru_id = $mysqli->insert_id;
                            else
                                $pgru_id = $_POST["id"];


                            $qry_new_pgru = 'SELECT * FROM cad_pgru WHERE id = "'.$pgru_id.'"';
                            $qry_result_new_pgru = $mysqli->query($qry_new_pgru);
                            $dados_new_pgru = mysqli_fetch_assoc($qry_result_new_pgru);

                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 10;
                            $retorno['data'] = $dados_new_pgru;
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Houve um problema ao gravar o grupo.";
                        }
                    } else {
                        $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Pai inexistente, faça a sincronização antes do grupo.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getServico() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento/Pedido - MaestriaTech para SAX
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT s.* FROM cad_serv s 
                        INNER JOIN unidades u ON u.empresa_id = s.empresa_id 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["codigo"])) {
                        $qry .= " AND s.id = '".$_POST["codigo"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $servicos = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $servicos[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $servicos;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getTipoCondicao() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Financeiro - MaestriaTech para SAX
                        if($key == 3 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT c.* FROM tipo_condicao_parcela_orcamento c 
                        INNER JOIN unidades u ON u.id = c.unidade_id 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["codigo"])) {
                        $qry .= " AND c.ID = '".$_POST["codigo"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $cond = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $cond[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $cond;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Financeiro - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getTipRece() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Financeiro - MaestriaTech para SAX
                        if($key == 3 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT r.* FROM tip_rece r 
                        INNER JOIN unidades u ON u.id = r.FILIAL 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["codigo"])) {
                        $qry .= " AND r.CODIGO = '".$_POST["codigo"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $rece = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $rece[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $rece;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Financeiro - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getFormPgto() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Financeiro - MaestriaTech para SAX
                        if($key == 3 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT p.* FROM cad_fpgt p 
                        INNER JOIN unidades u ON u.empresa_id = p.empresa_id 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["codigo"])) {
                        $qry .= " AND p.CODIGO = '".$_POST["codigo"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $pgto = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $pgto[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $pgto;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Financeiro - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getStatus() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento/Pedido - MaestriaTech para SAX
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT s.* FROM cad_status s 
                        INNER JOIN unidades u ON u.empresa_id = s.empresa_id 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["codigo"])) {
                        $qry .= " AND s.id = '".$_POST["codigo"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $status = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $status[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $status;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getTipoPedido() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento/Pedido - MaestriaTech para SAX
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT t.* FROM tipos_pedidos t 
                        INNER JOIN unidades u ON u.empresa_id = t.empresa_id 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["codigo"])) {
                        $qry .= " AND t.CODIGO = '".$_POST["codigo"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $tipos = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $tipos[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $tipos;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarTipoPedido() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['tipo'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Tipo!'; //ENUM('1','2') 1 = grupo; 2 = subgrupo
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['ativo'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Ativo!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['compras'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Compras!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['vendas'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Vendas!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento/Pedido - SAX para MaestriaTech
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    $acao = "";
                    if($_POST["codigo"]) {
                        $acao = "update";
                        $qry = "UPDATE tipos_pedidos SET TIPO = '".$_POST["tipo"]."', ATIVO = '".$_POST["ativo"]."', COMPRAS = '".$_POST["compras"]."', VENDAS = '".$_POST["vendas"]."' WHERE CODIGO = '".$_POST["codigo"]."';";
                    } else {
                        $acao = "insert";
                        $qry = "INSERT INTO tipos_pedidos (empresa_id, TIPO, ATIVO, COMPRAS, VENDAS, DATA_CADASTRO) VALUES ('".$dados_unid['empresa_id']."', '".$_POST["tipo"]."', '".$_POST["ativo"]."', '".$_POST["compras"]."', '".$_POST["vendas"]."', '".date('Y-m-d H:i:s')."');";
                    }

                    if($qry_result = $mysqli->query($qry)) {
                        if($acao == "insert")
                            $codigo = $mysqli->insert_id;
                        else
                            $codigo = $_POST["codigo"];

                        $qry_new_nota = 'SELECT * FROM tipos_pedidos WHERE CODIGO = "'.$codigo.'"';
                        $qry_result_new_nota = $mysqli->query($qry_new_nota);
                        $dados_new_nota = mysqli_fetch_assoc($qry_result_new_nota);

                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['data'] = $dados_new_nota;
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Houve um problema ao gravar o tipo.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getCurso() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Pedagogico - MaestriaTech para SAX
                        if($key == 4 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT c.* FROM curso c 
                        INNER JOIN unidades u ON u.empresa_id = c.empresa_id 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["codigo"])) {
                        $qry .= " AND c.ID_CURSO = '".$_POST["codigo"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $curso = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $curso[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $curso;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Pedagógico - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getContrato() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento/Pedido - MaestriaTech para SAX
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
					$fp = fopen("getContratoPOST.txt", "a");
					$escreve = fwrite($fp, print_r($_POST, TRUE));
					fclose($fp);
				
                    $qry = 'SELECT c.* FROM cad_orca c 
                        INNER JOIN unidades u ON u.id = c.filial 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["id"])) {
                        $qry .= " AND c.id = '".$_POST["id"]."'";
                    }
                    if(!empty($_POST["status"])) {
                        $qry .= " AND c.status = '".$_POST["status"]."'";
                    }
                    if(!empty($_POST["sincronizado"])) {
                        $qry .= " AND c.sincronizado = '".$_POST["sincronizado"]."'";
                    }
                    if(!empty($_POST["cliente"])) {
                        $qry .= " AND c.user_id = '".$_POST["cliente"]."'";
                    }
                    if(!empty($_POST["curso"])) {
                        $qry .= " AND c.lig_id_curso = '".$_POST["curso"]."'";
                    }
                    if(!empty($_POST["situacao"])) {
                        $qry .= " AND c.situacao = '".$_POST["situacao"]."'";
                    }
                    if(!empty($_POST["ultima_atualizacao"])) {
                        $qry .= " AND c.ultima_atualizacao >= '".$_POST["ultima_atualizacao"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $orcamentos = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $qry_ser_orca = 'SELECT s.* FROM ser_orca s WHERE s.orc_id = "'.$row['id'].'"';
                            $qry_result_ser_orca = $mysqli->query($qry_ser_orca);
                            $qry_rows_ser_orca = mysqli_num_rows($qry_result_ser_orca);
                            if ($qry_rows_ser_orca > 0):
                                while($row_ser_orca = mysqli_fetch_assoc($qry_result_ser_orca)):
                                    $row["ser_orca"][] = $row_ser_orca;
                                endwhile;
                            endif;
							
							$qry_pro_orca = 'SELECT p.* FROM pro_orca p WHERE p.orc_id = "'.$row['id'].'"';
                            $qry_result_pro_orca = $mysqli->query($qry_pro_orca);
                            $qry_rows_pro_orca = mysqli_num_rows($qry_result_pro_orca);
                            if ($qry_rows_pro_orca > 0):
                                while($row_pro_orca = mysqli_fetch_assoc($qry_result_pro_orca)):
                                    $row["pro_orca"][] = $row_pro_orca;
                                endwhile;
                            endif;

                            $qry_par_orca = 'SELECT p.* FROM par_orca p WHERE p.orc_id = "'.$row['id'].'"';
                            $qry_result_par_orca = $mysqli->query($qry_par_orca);
                            $qry_rows_par_orca = mysqli_num_rows($qry_result_par_orca);
                            if ($qry_rows_par_orca > 0):
                                while($row_par_orca = mysqli_fetch_assoc($qry_result_par_orca)):
                                    $qry_tipo_condicao_id = 'SELECT c.TIPO_CONDICAO_ID FROM parcela_servico_condicoes_orca p INNER JOIN servico_condicoes_orcamento s ON p.SERVICO_CONDICOES_ORCA = s.CODIGO INNER JOIN condicoes_orcamento c ON s.CONDICOES_ORCAMENTO = c.CODIGO WHERE p.CODIGO_PARCELA_GERADA = "'.$row_par_orca['id'].'"';
                                    $qry_result_tipo_condicao_id = $mysqli->query($qry_tipo_condicao_id);
                                    $qry_rows_tipo_condicao_id = mysqli_num_rows($qry_result_tipo_condicao_id);
                                    if ($qry_rows_tipo_condicao_id > 0):
                                        $row_tipo_condicao_id = mysqli_fetch_assoc($qry_result_tipo_condicao_id);
                                        $row_par_orca['tipo_condicao_id'] = $row_tipo_condicao_id['TIPO_CONDICAO_ID'];
                                    endif;
                                    $row["par_orca"][] = $row_par_orca;
                                endwhile;
                            endif;

                            $qry_disciplina = 'SELECT d.* FROM contrato_disciplina d WHERE d.LIG_ID_CONTRATO = "'.$row['id'].'"';
                            $qry_result_disciplina = $mysqli->query($qry_disciplina);
                            $qry_rows_disciplina = mysqli_num_rows($qry_result_disciplina);
                            if ($qry_rows_disciplina > 0):
                                while($row_disciplina = mysqli_fetch_assoc($qry_result_disciplina)):
                                    $row["disciplinas"][] = $row_disciplina;
                                endwhile;
                            endif;

                            $qry_turma = 'SELECT t.* FROM contrato_turma t WHERE t.id_contrato = "'.$row['id'].'"';
                            $qry_result_turma = $mysqli->query($qry_turma);
                            $qry_rows_turma = mysqli_num_rows($qry_result_turma);
                            if ($qry_rows_turma > 0):
                                while($row_turma = mysqli_fetch_assoc($qry_result_turma)):
                                    $row["turmas"][] = $row_turma;
                                endwhile;
                            endif;

                            $qry_cond_orc = 'SELECT co.* FROM condicoes_orcamento co WHERE co.ORCAMENTO = "'.$row['id'].'"';
                            $qry_result_cond_orc = $mysqli->query($qry_cond_orc);
                            $qry_rows_cond_orc = mysqli_num_rows($qry_result_cond_orc);
                            if ($qry_rows_cond_orc > 0):
                                $i = 0;
                                while($row_cond_orc = mysqli_fetch_assoc($qry_result_cond_orc)):
                                    $row["condicoes_orcamento"][$i] = $row_cond_orc;

                                    $qry_ser_cond_orc = 'SELECT sc.* FROM servico_condicoes_orcamento sc WHERE sc.CONDICOES_ORCAMENTO = "'.$row_cond_orc['CODIGO'].'"';
                                    $qry_result_ser_cond_orc = $mysqli->query($qry_ser_cond_orc);
                                    $qry_rows_ser_cond_orc = mysqli_num_rows($qry_result_ser_cond_orc);
                                    if ($qry_rows_ser_cond_orc > 0):
                                        while($row_ser_cond_orc = mysqli_fetch_assoc($qry_result_ser_cond_orc)):
                                            $qry_par_ser_cond_orc = 'SELECT ps.* FROM parcela_servico_condicoes_orca ps WHERE ps.SERVICO_CONDICOES_ORCA = "'.$row_ser_cond_orc['CODIGO'].'"';
                                            $qry_result_par_ser_cond_orc = $mysqli->query($qry_par_ser_cond_orc);
                                            $qry_rows_par_ser_cond_orc = mysqli_num_rows($qry_result_par_ser_cond_orc);
                                            if ($qry_rows_par_ser_cond_orc > 0):
                                                while($row_par_ser_cond_orc = mysqli_fetch_assoc($qry_result_par_ser_cond_orc)):
                                                    $row_ser_cond_orc['parcela_servico_condicoes_orca'][] = $row_par_ser_cond_orc;
                                                endwhile;
                                            endif;

                                            $row["condicoes_orcamento"][$i]['servico_condicoes_orcamento'][] = $row_ser_cond_orc;
                                        endwhile;
                                    endif;

                                    $qry_pro_cond_orc = 'SELECT pc.* FROM produto_condicoes_orcamento pc WHERE pc.CONDICOES_ORCAMENTO = "'.$row_cond_orc['CODIGO'].'"';
                                    $qry_result_pro_cond_orc = $mysqli->query($qry_pro_cond_orc);
                                    $qry_rows_pro_cond_orc = mysqli_num_rows($qry_result_pro_cond_orc);
                                    if ($qry_rows_pro_cond_orc > 0):
                                        while($row_pro_cond_orc = mysqli_fetch_assoc($qry_result_pro_cond_orc)):
                                            $qry_par_pro_cond_orc = 'SELECT pp.* FROM parcela_produto_condicoes_orca pp WHERE pp.PRODUTO_CONDICOES_ORCA = "'.$row_ser_cond_orc['CODIGO'].'"';
                                            $qry_result_par_pro_cond_orc = $mysqli->query($qry_par_pro_cond_orc);
                                            $qry_rows_par_pro_cond_orc = mysqli_num_rows($qry_result_par_pro_cond_orc);
                                            if ($qry_rows_par_pro_cond_orc > 0):
                                                while($row_par_pro_cond_orc = mysqli_fetch_assoc($qry_result_par_pro_cond_orc)):
                                                    $row_pro_cond_orc['parcela_produto_condicoes_orca'][] = $row_par_pro_cond_orc;
                                                endwhile;
                                            endif;

                                            $row["condicoes_orcamento"][$i]['produto_condicoes_orcamento'][] = $row_pro_cond_orc;
                                        endwhile;
                                    endif;

                                    $i++;
                                endwhile;
                            endif;

                            $qry_mov_status = 'SELECT c.LIG_ID_USUARIO FROM cad_orca_status c 
                                WHERE c.LIG_ID_MOVIMENTACAO = "' . $row['id'] . '" AND c.LIG_ID_STATUS = "'.$row["status"].'"
                                ORDER BY c.ID_REGISTRO DESC
                                LIMIT 1';
                            $qry_result_mov_status = $mysqli->query($qry_mov_status);
                            $qry_rows_mov_status = mysqli_num_rows($qry_result_mov_status);
                            if($qry_rows_mov_status > 0) {
                                $row_mov_status = mysqli_fetch_assoc($qry_result_mov_status);
                                $row["usu_ultima_mov"] = $row_mov_status["LIG_ID_USUARIO"];
                            } else {
                                $row["usu_ultima_mov"] = $row["user_id"];
                            }

                            $orcamentos[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $orcamentos;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getOportunidade() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento/Pedido - MaestriaTech para SAX
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $qry = 'SELECT o.* FROM oportunidades o 
                        INNER JOIN unidades u ON u.empresa_id = o.empresa_id 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["id"])) {
                        $qry .= " AND o.id = '".$_POST["id"]."'";
                    }
                    if(!empty($_POST["status"])) {
                        $qry .= " AND o.status = '".$_POST["status"]."'";
                    }
                    if(!empty($_POST["cliente"])) {
                        $qry .= " AND o.cliente_id = '".$_POST["cliente"]."'";
                    }
                    
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $oportunidades = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            if($row['cliente'] != "") {
                                $qry_cli = 'SELECT c.* FROM clientes c INNER JOIN pessoas p ON p.id = c.pessoa_id WHERE c.id = "'.$row['cliente'].'"';
                                $qry_result_cli = $mysqli->query($qry_cli);
                                $qry_rows_cli = mysqli_num_rows($qry_result_cli);
                                if ($qry_rows_cli > 0):
                                    while($row_cli = mysqli_fetch_assoc($qry_result_cli)):
                                        $row["cliente"] = $row_cli;
                                    endwhile;
                                endif;
                            }

                            $qry_equip = 'SELECT e.* FROM oportunidades_equip e WHERE e.oportunidade_id = "'.$row['id'].'"';
                            $qry_result_equip = $mysqli->query($qry_equip);
                            $qry_rows_equip = mysqli_num_rows($qry_result_equip);
                            if ($qry_rows_equip > 0):
                                while($row_equip = mysqli_fetch_assoc($qry_result_equip)):
                                    $row["equipamentos"][] = $row_equip;
                                endwhile;
                            endif;

                            $qry_prod = 'SELECT p.* FROM oportunidades_produto p WHERE p.oportunidade_id = "'.$row['id'].'"';
                            $qry_result_prod = $mysqli->query($qry_prod);
                            $qry_rows_prod = mysqli_num_rows($qry_result_prod);
                            if ($qry_rows_prod > 0):
                                while($row_prod = mysqli_fetch_assoc($qry_result_prod)):
                                    $row["produtos"][] = $row_prod;
                                endwhile;
                            endif;

                            $oportunidades[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $oportunidades;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarContrato() {
        if($this->method == 'POST'):
            // exit("<pre>".print_r($_POST,true)."</pre>");
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['codigos'])):
                if(empty($_POST['user_id'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Usuário!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['status']) && @$_POST['status'] != 0):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Status!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['total_geral']) && @$_POST['total_geral'] != 0):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Total Geral!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['peso'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Peso!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['total_produtos']) && @$_POST['total_produtos'] != 0):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Total dos Produtos!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['total_servicos']) && @$_POST['total_servicos'] != 0):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Total dos Serviços!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['qnt_parcelas'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Quantidade de Parcelas!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['situacao'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Situação!';
                    exit(json_encode($retorno));
                endif;
                /*if(empty($_POST['curso'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Curso!';
                    exit(json_encode($retorno));
                endif;*/
                if(empty($_POST['frete'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Frete!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['valor_frete']) && @$_POST['valor_frete'] != 0):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Valor do Frete!';
                    exit(json_encode($retorno));
                endif;
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    if(empty($_POST["codigos"])) {
                        $qry_user = 'SELECT u.* FROM users u WHERE u.id = "' . $_POST['user_id'] . '"';
                        $qry_user_result = $mysqli->query($qry_user);
                        $qry_user_rows = mysqli_num_rows($qry_user_result);
                        if($qry_user_rows == 0) {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = 'Cliente não encontrado!';
                            exit(json_encode($retorno));
                        } else {
                            $dados_user = mysqli_fetch_assoc($qry_user_result);
                        }

                        $vendedor = "";
                        if(@$_POST["vendedor"]) {
                            $qry_pref = 'SELECT * FROM admin_preferences where empresa_id = "'.$dados_unid['empresa_id'].'"';
                            $qry_pref_result = $mysqli->query($qry_pref);
                            $dados_pref = mysqli_fetch_assoc($qry_pref_result);
                            if($dados_pref["grupo_vendedor"] != "") {
                                $qry_vend = 'SELECT u.id FROM users u  
                                    INNER JOIN users_groups ug on ug.user_id = u.id 
                                    WHERE ug.group_id = "' . $dados_pref['grupo_vendedor'] . '" AND u.id = "'.$_POST["vendedor"].'"';
                                $qry_vend_result = $mysqli->query($qry_vend);
                                $qry_vend_rows = mysqli_num_rows($qry_vend_result);
                                if($qry_vend_rows > 0) {
                                    $dados_vend = mysqli_fetch_assoc($qry_vend_result);
                                    $vendedor = $dados_vend["id"];
                                }
                            }
                        }

                        $_POST["data_matricula"] = (@$_POST["data_matricula"]) ? $_POST["data_matricula"] : "0000-00-00";
                        $_POST["inicio_vigencia"] = (@$_POST["inicio_vigencia"]) ? $_POST["inicio_vigencia"] : "0000-00-00";
                        $_POST["fim_vigencia"] = (@$_POST["fim_vigencia"]) ? $_POST["fim_vigencia"] : "0000-00-00";
                        $qry = "INSERT INTO cad_orca (user_id, cod_site, status, vendedor, endereco, cep, numero, complemento, bairro, cidade, estado, total_geral, peso_bruto, data_cadastro, data_matricula, data_pedido, data_baixa, hora_baixa, hora_cadastro, hora_pedido, total_produtos, total_servicos, valor_frete, frete, forma_pagamento, qnt_parcelas, filial, tipo_MaestriaTech, situacao, observacoes, observacoes2, lig_id_curso, sincronizado, inicio_vigencia, fim_vigencia, previsao_entrega, data_prazo_faturamento, tipo_pedido) VALUES ('".$_POST["user_id"]."', '".@$_POST["cod_site"]."', '".$_POST["status"]."', '".$vendedor."', '".@$_POST["endereco"]."', '".@$_POST["cep"]."', '".@$_POST["numero"]."', '".@$_POST["complemento"]."', '".@$_POST["bairro"]."', '".@$_POST["cidade"]."', '".@$_POST["estado"]."', '".$_POST["total_geral"]."', '".$_POST["peso"]."', '".date('Y-m-d', $dados_user["created_on"])."', '".@$_POST["data_matricula"]."', '".@$_POST["data_pedido"]."', '".@$_POST["data_baixa"]."', '".@$_POST["hora_baixa"]."', '".date('H:i:s', $dados_user["created_on"])."', '".@$_POST["hora_pedido"]."', '".$_POST["total_produtos"]."', '".$_POST["total_servicos"]."', '".$_POST["valor_frete"]."', '".$_POST["frete"]."', '".@$_POST["forma_pagamento"]."', '".$_POST["qnt_parcelas"]."', '".$dados_unid["id"]."', '2', '".$_POST["situacao"]."', '".@$_POST["obs"]."', '".@$_POST["obs2"]."', '".$_POST["curso"]."', 'S', '".@$_POST["inicio_vigencia"]."', '".@$_POST["fim_vigencia"]."', '".@$_POST["previsao_entrega"]."', '".@$_POST["data_prazo_faturamento"]."', '".@$_POST["tipo_pedido"]."');";

                        if($qry_result = $mysqli->query($qry)) {
                            $orc_id = $mysqli->insert_id;

                            $qry_ead_user = 'SELECT u.* FROM ead_users u WHERE u.user_id = "' . $_POST['user_id'] . '"';
                            $qry_ead_user_result = $mysqli->query($qry_ead_user);
                            $qry_ead_user_rows = mysqli_num_rows($qry_ead_user_result);
                            if($qry_ead_user_rows == 0) {
                                $dados = array(
                                    'email_address' => $dados_user["email"],
                                    'first_name' => $dados_user["first_name"]." ".$dados_user["last_name"],
                                    'native_language' => 'pt',
                                    'level' => '1',
                                    'expiration_date' => date("Y-m-d", strtotime("+40 days")),
                                    'date_joined' => date("Y-m-d")
                                );

                                $this->gravar_user_voxy($_POST['user_id'], $dados);

                                $qry_create_ead_user = "INSERT INTO ead_users (user_id, date_joined, level, expiration_date) VALUES ('".$_POST['user_id']."', '".date('Y-m-d')."', '1', '".date("Y-m-d", strtotime("+40 days"))."');";
                                $mysqli->query($qry_create_ead_user);
                            } else {
                                $qry_update_ead_user = "UPDATE ead_users SET expiration_date = '".date("Y-m-d", strtotime("+40 days"))."' WHERE user_id = '".$_POST['user_id']."';";
                                $mysqli->query($qry_update_ead_user);
                            }

                            if(count(@$_POST["pro_orca"]) > 0) {
                                foreach ($_POST["pro_orca"] as $pro_orca) {
                                    $qry_pro_orca = "INSERT INTO pro_orca (orc_id, prod_id, sequencia, unidade, quantidade_item, total_desconto, preco, peso) VALUES ('".$orc_id."', '".$pro_orca["prod_id"]."', '".$pro_orca["sequencia"]."', '".$pro_orca["unidade"]."', '".$pro_orca["quantidade_item"]."', '".$pro_orca["total_desconto"]."', '".$pro_orca["preco"]."', '".@$pro_orca["peso"]."');";
                                    $mysqli->query($qry_pro_orca);
                                }
                            }

                            if(count(@$_POST["ser_orca"]) > 0) {
                                foreach ($_POST["ser_orca"] as $ser_orca) {
                                    $qry_ser_orca = "INSERT INTO ser_orca (orc_id, serv_id, sequencia, preco, total_desconto, quantidade_item, total_item) VALUES ('".$orc_id."', '".$ser_orca["serv_id"]."', '".$ser_orca["sequencia"]."', '".$ser_orca["preco"]."', '".@$ser_orca["total_desconto"]."', '".$ser_orca["quantidade_item"]."', '".$ser_orca["total_item"]."');";
                                    $mysqli->query($qry_ser_orca);
                                }
                            }

                            if(count(@$_POST["par_orca"]) > 0) {
                                $parcelas_array = array();
                                foreach ($_POST["par_orca"] as $par_orca) {
                                    $qry_par_orca = "INSERT INTO par_orca (orc_id, tipo_recebimento, parcela, valor, vencimento, valor_desconto_pontualidade, carencia_desconto_pontualidade, tipo_valor_desconto_pontualidade) VALUES ('".$orc_id."', '".$par_orca["tipo_recebimento"]."', '".$par_orca["parcela"]."', '".$par_orca["valor"]."', '".$par_orca["vencimento"]."', '".@$par_orca["valor_desconto_pontualidade"]."', '".@$par_orca["carencia_desconto_pontualidade"]."', '".@$par_orca["tipo_valor_desconto_pontualidade"]."');";
                                    $mysqli->query($qry_par_orca);
                                    $par_orca["id"] = $mysqli->insert_id;
                                    $parcelas_array[$par_orca["tipo_recebimento"]."-".$par_orca["parcela"]] = $par_orca;
                                }
                            }

                            if(count(@$_POST["condicoes_orcamento"]) > 0) {
                                foreach ($_POST["condicoes_orcamento"] as $condicoes_orcamento) {
                                    $qry_condicoes_orcamento = "INSERT INTO condicoes_orcamento (ORCAMENTO, FORMA_DE_PAGAMENTO, DATA_INICIO, DATA_TERMINO, SINCRONIZADO, TIPO_RECEBIMENTO, TIPO_CONDICAO_ID, PLANO_FINANCEIRO_ID, VALORDESCONTOPONTUALIDADE, CARENCIADESCONTOPONTUALIDADE, NUM_PARCELAS, INTERVALO, DIA_VENCIMENTO, DATA_PRIMEIRA_PARCELA, CalcParc) VALUES ('".$orc_id."', '".$condicoes_orcamento["forma_pagamento"]."', '".@$condicoes_orcamento["data_inicio"]."', '".@$condicoes_orcamento["data_termino"]."', '1', '".$condicoes_orcamento["tipo_recebimento"]."', '".$condicoes_orcamento["tipo_condicao_id"]."', '".$condicoes_orcamento["plano_financeiro_id"]."', '".@$condicoes_orcamento["valor_desconto_pontualidade"]."', '".@$condicoes_orcamento["carencia_desconto_pontualidade"]."', '".$condicoes_orcamento["num_parcelas"]."', '".@$condicoes_orcamento["intervalo"]."', '".@$condicoes_orcamento["dia_vencimento"]."', '".@$condicoes_orcamento["data_primeira_parcela"]."', '".@$condicoes_orcamento["calc_parc"]."');";
                                    $mysqli->query($qry_condicoes_orcamento);

                                    $condicoes_orcamento_id = $mysqli->insert_id;
                                    if(count(@$condicoes_orcamento["servico_condicoes_orcamento"]) > 0) {
                                        foreach ($condicoes_orcamento["servico_condicoes_orcamento"] as $servico_condicoes_orcamento) {
                                            $qry_servico_condicoes_orcamento = "INSERT INTO servico_condicoes_orcamento (CONDICOES_ORCAMENTO, SERVICO, SEQUENCIA, SINCRONIZADO) VALUES ('".$condicoes_orcamento_id."', '".$servico_condicoes_orcamento["servico"]."', '".$servico_condicoes_orcamento["sequencia"]."', '1');";
                                            $mysqli->query($qry_servico_condicoes_orcamento);

                                            if(count(@$servico_condicoes_orcamento["parcela_servico_condicoes_orca"]) > 0) {
                                                $servico_condicoes_orcamento_id = $mysqli->insert_id;
                                                foreach ($servico_condicoes_orcamento["parcela_servico_condicoes_orca"] as $parcela_servico_condicoes_orca) {
                                                    $qry_parcela_servico_condicoes_orca = "INSERT INTO parcela_servico_condicoes_orca (SERVICO_CONDICOES_ORCA, VALOR, VENCIMENTO, TIPO_RECEBIMENTO, PARCELA, CODIGO_PARCELA_GERADA, SINCRONIZADO) VALUES ('".$servico_condicoes_orcamento_id."', '".$parcela_servico_condicoes_orca["valor"]."', '".$parcela_servico_condicoes_orca["vencimento"]."', '".$parcela_servico_condicoes_orca["tipo_recebimento"]."', '".$parcela_servico_condicoes_orca["parcela"]."', '".$parcelas_array[$parcela_servico_condicoes_orca["tipo_recebimento"]."-".$parcela_servico_condicoes_orca["parcela"]]["id"]."', '1');";
                                                    unset($parcelas_array[$parcela_servico_condicoes_orca["tipo_recebimento"]."-".$parcela_servico_condicoes_orca["parcela"]]);
                                                    $mysqli->query($qry_parcela_servico_condicoes_orca);
                                                }
                                            }
                                        }
                                    }
                                    if(count(@$condicoes_orcamento["produto_condicoes_orcamento"]) > 0) {
                                        /*foreach ($condicoes_orcamento["servico_condicoes_orcamento"] as $servico_condicoes_orcamento) {
                                            $qry_servico_condicoes_orcamento = "INSERT INTO servico_condicoes_orcamento (CONDICOES_ORCAMENTO, SERVICO, SEQUENCIA, SINCRONIZADO) VALUES ('".$condicoes_orcamento_id."', '".$servico_condicoes_orcamento["servico"]."', '".$servico_condicoes_orcamento["sequencia"]."', '1');";
                                            $mysqli->query($qry_servico_condicoes_orcamento);

                                            if(count(@$servico_condicoes_orcamento["parcela_servico_condicoes_orca"]) > 0) {
                                                $servico_condicoes_orcamento_id = $mysqli->insert_id;
                                                foreach (@$servico_condicoes_orcamento["parcela_servico_condicoes_orca"] as $parcela_servico_condicoes_orca) {
                                                    $qry_parcela_servico_condicoes_orca = "INSERT INTO parcela_servico_condicoes_orca (SERVICO_CONDICOES_ORCAMENTO, VALOR, VENCIMENTO, TIPO_RECEBIMENTO, PARCELA, CODIGO_PARCELA_GERADA, SINCRONIZADO) VALUES ('".$servico_condicoes_orcamento_id."', '".$parcela_servico_condicoes_orca["valor"]."', '".$parcela_servico_condicoes_orca["vencimento"]."', '".$parcela_servico_condicoes_orca["tipo_recebimento"]."', '".$parcela_servico_condicoes_orca["parcela"]."', '".$parcelas_array[$parcela_servico_condicoes_orca["tipo_recebimento"]."-".$parcela_servico_condicoes_orca["parcela"]]["id"]."', '1');";
                                                    unset($parcelas_array[$parcela_servico_condicoes_orca["tipo_recebimento"]."-".$parcela_servico_condicoes_orca["parcela"]]);
                                                    $mysqli->query($qry_parcela_servico_condicoes_orca);
                                                }
                                            }
                                        }*/
                                    }
                                }
                            }

                            $qry_new_orc = 'SELECT * FROM cad_orca WHERE id = "'.$orc_id.'"';
                            $qry_result_new_orc = $mysqli->query($qry_new_orc);
                            $row = mysqli_fetch_assoc($qry_result_new_orc);

                            $qry_ser_orca = 'SELECT s.* FROM ser_orca s WHERE s.orc_id = "'.$row['id'].'"';
                            $qry_result_ser_orca = $mysqli->query($qry_ser_orca);
                            $qry_rows_ser_orca = mysqli_num_rows($qry_result_ser_orca);
                            if ($qry_rows_ser_orca > 0):
                                while($row_ser_orca = mysqli_fetch_assoc($qry_result_ser_orca)):
                                    $row["ser_orca"][] = $row_ser_orca;
                                endwhile;
                            endif;

                            $qry_pro_orca = 'SELECT p.* FROM pro_orca p WHERE p.orc_id = "'.$row['id'].'"';
                            $qry_result_pro_orca = $mysqli->query($qry_pro_orca);
                            $qry_rows_pro_orca = mysqli_num_rows($qry_result_pro_orca);
                            if ($qry_rows_pro_orca > 0):
                                while($row_pro_orca = mysqli_fetch_assoc($qry_result_pro_orca)):
                                    $row["pro_orca"][] = $row_pro_orca;
                                endwhile;
                            endif;

                            $qry_par_orca = 'SELECT p.* FROM par_orca p WHERE p.orc_id = "'.$row['id'].'"';
                            $qry_result_par_orca = $mysqli->query($qry_par_orca);
                            $qry_rows_par_orca = mysqli_num_rows($qry_result_par_orca);
                            if ($qry_rows_par_orca > 0):
                                while($row_par_orca = mysqli_fetch_assoc($qry_result_par_orca)):
                                    $row["par_orca"][] = $row_par_orca;
                                endwhile;
                            endif;

                            $qry_cond_orc = 'SELECT co.* FROM condicoes_orcamento co WHERE co.ORCAMENTO = "'.$row['id'].'"';
                            $qry_result_cond_orc = $mysqli->query($qry_cond_orc);
                            $qry_rows_cond_orc = mysqli_num_rows($qry_result_cond_orc);
                            if ($qry_rows_cond_orc > 0):
                                $i = 0;
                                while($row_cond_orc = mysqli_fetch_assoc($qry_result_cond_orc)):
                                    $row["condicoes_orcamento"][$i] = $row_cond_orc;

                                    $qry_ser_cond_orc = 'SELECT sc.* FROM servico_condicoes_orcamento sc WHERE sc.CONDICOES_ORCAMENTO = "'.$row_cond_orc['CODIGO'].'"';
                                    $qry_result_ser_cond_orc = $mysqli->query($qry_ser_cond_orc);
                                    $qry_rows_ser_cond_orc = mysqli_num_rows($qry_result_ser_cond_orc);
                                    if ($qry_rows_ser_cond_orc > 0):
                                        while($row_ser_cond_orc = mysqli_fetch_assoc($qry_result_ser_cond_orc)):
                                            $qry_par_ser_cond_orc = 'SELECT ps.* FROM parcela_servico_condicoes_orca ps WHERE ps.SERVICO_CONDICOES_ORCA = "'.$row_ser_cond_orc['CODIGO'].'"';
                                            $qry_result_par_ser_cond_orc = $mysqli->query($qry_par_ser_cond_orc);
                                            $qry_rows_par_ser_cond_orc = mysqli_num_rows($qry_result_par_ser_cond_orc);
                                            if ($qry_rows_par_ser_cond_orc > 0):
                                                while($row_par_ser_cond_orc = mysqli_fetch_assoc($qry_result_par_ser_cond_orc)):
                                                    $row_ser_cond_orc['parcela_servico_condicoes_orca'][] = $row_par_ser_cond_orc;
                                                endwhile;
                                            endif;

                                            $row["condicoes_orcamento"][$i]['servico_condicoes_orcamento'][] = $row_ser_cond_orc;
                                        endwhile;
                                    endif;

                                    $qry_pro_cond_orc = 'SELECT pc.* FROM produto_condicoes_orcamento pc WHERE pc.CONDICOES_ORCAMENTO = "'.$row_cond_orc['CODIGO'].'"';
                                    $qry_result_pro_cond_orc = $mysqli->query($qry_pro_cond_orc);
                                    $qry_rows_pro_cond_orc = mysqli_num_rows($qry_result_pro_cond_orc);
                                    if ($qry_rows_pro_cond_orc > 0):
                                        while($row_pro_cond_orc = mysqli_fetch_assoc($qry_result_pro_cond_orc)):
                                            $qry_par_pro_cond_orc = 'SELECT pp.* FROM parcela_produto_condicoes_orca pp WHERE pp.PRODUTO_CONDICOES_ORCA = "'.$row_pro_cond_orc['CODIGO'].'"';
                                            $qry_result_par_pro_cond_orc = $mysqli->query($qry_par_pro_cond_orc);
                                            $qry_rows_par_pro_cond_orc = mysqli_num_rows($qry_result_par_pro_cond_orc);
                                            if ($qry_rows_par_pro_cond_orc > 0):
                                                while($row_par_pro_cond_orc = mysqli_fetch_assoc($qry_result_par_pro_cond_orc)):
                                                    $row_pro_cond_orc['parcela_produto_condicoes_orca'][] = $row_par_pro_cond_orc;
                                                endwhile;
                                            endif;

                                            $row["condicoes_orcamento"][$i]['produto_condicoes_orcamento'][] = $row_pro_cond_orc;
                                        endwhile;
                                    endif;

                                    $i++;
                                endwhile;
                            endif;


                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 10;
                            $retorno['data'] = $row;
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Houve um problema ao gravar o contrato.";
                        }
                    } else {
                        $qry = "UPDATE cad_orca SET sincronizado = 'S', sincronizado_loja = 'N', ultima_atualizacao = '".date('Y-m-d H:i:s')."'";
                        if(!empty($_POST['status'])):
                            $qry .= ", status = '".$_POST["status"]."'";
                        endif;
                        if(!empty($_POST['situacao'])):
                            $qry .= ", situacao = '".$_POST["situacao"]."'";
                        endif;
                        if(!empty($_POST['data_prazo_faturamento'])):
                            $qry .= ", data_prazo_faturamento = '".$_POST["data_prazo_faturamento"]."'";
                        endif;
                        $qry .= " WHERE";
                        foreach ($_POST['codigos'] as $value) {
                            if($i > 0)
                                $qry .= " OR";
                            $qry .= " id = '".$value."'";
                            $i++;
                        }
                        if($mysqli->query($qry)) {
                            if(!empty($_POST['status'])):
                                foreach ($_POST['codigos'] as $value) {
                                    $qry_orc = 'SELECT c.*, u.empresa_id, s.descricao as status_desc FROM cad_orca c INNER JOIN unidades u ON u.id = c.filial INNER JOIN cad_status s ON s.id = c.status WHERE c.id = "'.$value.'"';
                                    $qry_result_orc = $mysqli->query($qry_orc);
                                    $row_orc = mysqli_fetch_assoc($qry_result_orc);

                                    $qry_aud = 'INSERT INTO cad_clie_auditoria (empresa_id, LIG_ID_CLIENTE, LIG_ID_USUARIO, REGISTRO, STATUS, MENSAGEM, DATA_REGISTRO, HORA_REGISTRO) VALUES("'.$row_orc["empresa_id"].'", "'.$row_orc['user_id'].'", "'.$_POST['user_id'].'", "true", 6, "Contrato '.$value.' alterado para '.$row_orc["status_desc"].'.", "'.date("Y-m-d").'", "'.date("H:i:s").'")';
                                    $mysqli->query($qry_aud);

                                    $qry_mov = 'INSERT INTO cad_orca_status (LIG_ID_USUARIO, LIG_ID_MOVIMENTACAO, LIG_ID_STATUS, DATA_CADASTRO, HORA_CADASTRO) VALUES("'.$_POST['user_id'].'", '.$value.', '.$_POST['status'].', "'.date("Y-m-d").'", "'.date("H:i:s").'")';
                                    $mysqli->query($qry_mov);
                                }
                            endif;

                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 10;
                            $retorno['data'] = $row;
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Houve um problema ao atualizar o contrato.";
                        }
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function cancelarContrato() {
        require_once ('Iugu.php');
        $iugu = new Iugu();

        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['codigo'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Código do Contrato!';
                exit(json_encode($retorno));
            endif;            
            if(empty($_POST['usuario'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Código do Usuário!';
                exit(json_encode($retorno));
            endif;        
            if(empty($_POST['motivo'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Motivo!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    $qry_param = "SELECT LIG_STATUS_CONTRATO_06 FROM parametro WHERE LIG_ID_FILIAL = '".$dados_unid["id"]."'";
                    $qry_result_param = $mysqli->query($qry_param);
                    $dados_param = mysqli_fetch_assoc($qry_result_param);

                    if($dados_param["LIG_STATUS_CONTRATO_06"] != "") {
                        $qry_user = "SELECT id FROM users WHERE id = '".$_POST['usuario']."'";
                        $qry_result_user = $mysqli->query($qry_user);
                        $qry_rows_user = mysqli_num_rows($qry_result_user);

                        if($qry_rows_user > 0) {
                            $test_iugu = true;
                            
                            $qry_cur_orc = 'SELECT * FROM cad_orca WHERE id = "'.$_POST['codigo'].'"';
                            $qry_result_cur_orc = $mysqli->query($qry_cur_orc);
                            $row_cur = mysqli_fetch_assoc($qry_result_cur_orc);
                            
                            if($dados_unid["iugu_status"] == "S") {
                                $qry_iugu = "SELECT iugu_id FROM iugu_subscriptions WHERE cad_orca_id = '".$_POST['codigo']."'";
                                $qry_result_iugu = $mysqli->query($qry_iugu);
                                $qry_rows_iugu = mysqli_num_rows($qry_result_iugu);
                                if($qry_rows_iugu > 0) {
                                    $row_iugu = mysqli_fetch_assoc($qry_result_iugu);
                                    $retorno_iugu = $iugu->cancelar_assinatura($dados_unid["iugu_token"], $row_iugu["iugu_id"]);
            
                                    if($retorno_iugu["erro"] == true)
                                        $test_iugu = false;
                                    else {
										$qry_del = 'DELETE FROM iugu_subscriptions WHERE iugu_id = "'.$row_iugu["iugu_id"].'" AND cad_orca_id = '.$_POST['codigo'];
                                        $mysqli->query($qry_del);
										
                                        $qry_aud = 'INSERT INTO cad_clie_auditoria (empresa_id, LIG_ID_CLIENTE, LIG_ID_USUARIO, REGISTRO, STATUS, MENSAGEM, DATA_REGISTRO, HORA_REGISTRO) VALUES("'.$dados_unid["empresa_id"].'", "'.$row_cur['user_id'].'", "'.$_POST['usuario'].'", "true", "3", "Contrato na Iugu ID '.$row_iugu["iugu_id"].' cancelado.", "'.date("Y-m-d").'", "'.date("H:i:s").'")';
                                        $mysqli->query($qry_aud);
                                    }
                                }
                            }

                            if($test_iugu == true) {
                                $qry = "UPDATE cad_orca SET status = '".$dados_param["LIG_STATUS_CONTRATO_06"]."', sincronizado_loja = 'N', ultima_atualizacao = '".date('Y-m-d H:i:s')."' WHERE id = ".$_POST['codigo'];

                                if($qry_result = $mysqli->query($qry)) {
                                    $qry_motivo = 'INSERT INTO motivo_can_contrato (LIG_ID_USUARIO, LIG_ID_CONTRATO, DATA, HORA, MOTIVO) VALUES("'.$_POST['usuario'].'", "'.$_POST['codigo'].'", "'.date("Y-m-d").'", "'.date("H:i:s").'", "'.$_POST['motivo'].'")';
                                    $mysqli->query($qry_motivo);

                                    $qry_aud = 'INSERT INTO cad_clie_auditoria (empresa_id, LIG_ID_CLIENTE, LIG_ID_USUARIO, REGISTRO, STATUS, MENSAGEM, DATA_REGISTRO, HORA_REGISTRO) VALUES("'.$dados_unid["empresa_id"].'", "'.$row_cur['user_id'].'", "'.$_POST['usuario'].'", "true", "3", "Contrato código '.$_POST['codigo'].' cancelado.", "'.date("Y-m-d").'", "'.date("H:i:s").'")';
                                    $mysqli->query($qry_aud);
									
									$dados = array('expiration_date' => date("Y-m-d"));
                                    $this->gravar_user_voxy($row_cur['user_id'], $dados);
									
									$qry_update_ead_user = "UPDATE ead_users SET expiration_date = '".date("Y-m-d")."' WHERE user_id = '".$row_cur['user_id']."';";
                                    $mysqli->query($qry_update_ead_user);

                                    $qry_new_orc = 'SELECT c.*, u.email FROM cad_orca c INNER JOIN users u ON u.id = c.user_id WHERE c.id = "'.$_POST['codigo'].'"';
                                    $qry_result_new_orc = $mysqli->query($qry_new_orc);
                                    $row = mysqli_fetch_assoc($qry_result_new_orc);

                                    //enviar email -----------------------------------------------
                                    include(BASEPATH . '/application/third_party/phpmailer/PHPMailerAutoload.php');
                                    $mail = new PHPMailer();
                                    $mail->SetLanguage("br");
                                    $mail->CharSet = "utf-8";
                                    $mail->IsSMTP();
                                    $mail->SMTPDebug = false;
                                    $mail->SMTPAuth = true;
                                    $mail->isHTML(true);
                                    
                                    $email_config_db = $this->get_email_config($dados_unid["empresa_id"]);

                                    if($email_config_db['test'] == 1) {
                                        $mail->Host = $email_config_db['host'];
                                        $mail->Port = $email_config_db['port'];
                                        $mail->Username = $email_config_db['user'];
                                        $mail->Password = $email_config_db['pass'];
                                        $mail->SetFrom($email_config_db['from_email'], $email_config_db['from_name']);
                                    } else {
                                        $mail->Host = 'smtp.maestriatech.com.br';
                                        $mail->Port = 587;
                                        $mail->Username = 'contato=maestriatech.com.br';
                                        $mail->Password = 'Bet@0147410';
                                        $mail->SetFrom('contato@maestriatech.com.br', 'MaestriaTech');
                                    }

                                    $padrao_email = $this->get_email_padrao(13, $dados_unid["empresa_id"]);
                                    $padrao_email_msg = $padrao_email['mensagem'];
                                    $padrao_email_msg = str_replace("{contrato_id}", $_POST['codigo'], $padrao_email_msg);
                                    $padrao_email_msg = str_replace("{motivo}", $_POST['motivo'], $padrao_email_msg);

                                    $padrao_email_ass = $padrao_email['assunto'];
                                    $padrao_email_ass = str_replace("{contrato_id}", $_POST['codigo'], $padrao_email_ass);

                                    $mail->addAddress($row['email']);
                                    // $mail->addAddress('thiago@maestriatech.com.br');
                                    $mail->Subject = ("MaestriaTech - ".$padrao_email_ass);
                                    $mail->msgHTML($padrao_email_msg);

                                    if(!$mail->Send()) {
                                        $qry_aud = 'INSERT INTO cad_clie_auditoria (empresa_id, LIG_ID_CLIENTE, LIG_ID_USUARIO, REGISTRO, STATUS, MENSAGEM, DATA_REGISTRO, HORA_REGISTRO) VALUES("'.$dados_unid["empresa_id"].'", "'.$row_cur['user_id'].'", "'.$_POST['usuario'].'", "true", "3", "Houve um problema ao tentar enviar o e-mail ao cliente. Motivo: '.$mail->ErrorInfo.'", "'.date("Y-m-d").'", "'.date("H:i:s").'")';
                                        $mysqli->query($qry_aud);
                                    } else {
                                        $qry_aud = 'INSERT INTO cad_clie_auditoria (empresa_id, LIG_ID_CLIENTE, LIG_ID_USUARIO, REGISTRO, STATUS, MENSAGEM, DATA_REGISTRO, HORA_REGISTRO) VALUES("'.$dados_unid["empresa_id"].'", "'.$row_cur['user_id'].'", "'.$_POST['usuario'].'", "true", "3", "E-mail enviado ao cliente.", "'.date("Y-m-d").'", "'.date("H:i:s").'")';
                                        $mysqli->query($qry_aud);
                                    }
                                    //enviar email -----------------------------------------------

                                    $retorno['erro'] = FALSE;
                                    $retorno['codigo'] = 10;
                                    $retorno['data'] = $row;
                                } else {
                                    $retorno['erro'] = TRUE;
                                    $retorno['codigo'] = 1;
                                    $retorno['mensagem'] = "Houve um problema ao alterar o status do contrato.";
                                }
                            } else {
                                $retorno['erro'] = TRUE;
                                $retorno['codigo'] = 1;
                                $retorno['mensagem'] = "Houve um erro ao cancelar a assinatura na Iugu.";
                            }
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Usuário não encontrado.";
                        }
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Parâmetros da unidade não registrada.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarCompra() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['codigos'])):
                if(empty($_POST['user_id'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Usuário Comprador!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['status']) && @$_POST['status'] != 0):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Status!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['total_geral']) && @$_POST['total_geral'] != 0):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Total Geral!';
                    exit(json_encode($retorno));
                endif;
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    if(empty($_POST["codigos"])) {
                        $qry_user = 'SELECT u.* FROM users u WHERE u.id = "' . $_POST['user_id'] . '"';
                        $qry_user_result = $mysqli->query($qry_user);
                        $qry_user_rows = mysqli_num_rows($qry_user_result);
                        if($qry_user_rows == 0) {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = 'Usuário não encontrado!';
                            exit(json_encode($retorno));
                        } else {
                            $dados_user = mysqli_fetch_assoc($qry_user_result);
                        }

                        $qry = "INSERT INTO cad_comp (filial, sku, fornecedor, forma_pagamento, qtde_pgto, primeiro_pgto, intervalo_pgto, dia_intervalo, data_receb, total_produtos, total_servicos, desc_prod, desc_serv, total_geral, data_cadastro, observacoes, observacoes2, contato, comprador, situacao, sit_separ, data_pedido, destacar, frete, transportadora, placa, estado, quantidade, numero, outras_despesas, valor_frete, valor_seguro, setor, hora_receb, info_entrega, sincronizado, data_inicio_separacao, numero_pedido_fornecedor, status, data_baixa, tipo_pedido) VALUES ('".$dados_unid["id"]."', '".@$_POST["sku"]."', '".@$_POST["fornecedor"]."', '".@$_POST["forma_pagamento"]."', '".@$_POST["qtde_pgto"]."', '".@$_POST["intervalo_pgto"]."', '".@$_POST["dia_intervalo"]."', '".@$_POST["primeiro_pgto"]."', '".@$_POST["data_receb"]."', '".@$_POST["total_produtos"]."', '".@$_POST["total_servicos"]."', '".@$_POST["desc_prod"]."', '".@$_POST["desc_serv"]."', '".@$_POST["total_geral"]."', '".date('Y-m-d', $dados_user["created_on"])."', '".@$_POST["obs"]."', '".@$_POST["obs2"]."', '".@$_POST["contato"]."', '".@$_POST["user_id"]."', '".@$_POST["situacao"]."', '".@$_POST["sit_separ"]."', '".@$_POST["data_pedido"]."', '".@$_POST["destacar"]."', '".@$_POST["frete"]."', '".@$_POST["transportadora"]."', '".@$_POST["placa"]."', '".@$_POST["estado"]."', '".@$_POST["quantidade"]."', '".@$_POST["numero"]."', '".@$_POST["outras_despesas"]."', '".@$_POST["valor_frete"]."', '".@$_POST["valor_seguro"]."', '".@$_POST["setor"]."', '".@$_POST["hora_receb"]."', '".@$_POST["info_entrega"]."', '1', '".@$_POST["data_inicio_separacao"]."', '".@$_POST["numero_pedido_fornecedor"]."', '".@$_POST["status"]."', '".@$_POST["data_baixa"]."', '".@$_POST["tipo_pedido"]."');";

                        if($qry_result = $mysqli->query($qry)) {
                            $orc_id = $mysqli->insert_id;

                            if(count(@$_POST["pro_comp"]) > 0) {
                                foreach ($_POST["pro_comp"] as $pro_comp) {
                                    $qry_pro_orca = "INSERT INTO pro_comp (ordem, sequencia, produto, unidade, item_unid, preco, quantidade, qtde_separada, desconto, outras_desp, frete, seguro, sincronizado) VALUES ('".$orc_id."', '".@$pro_comp["sequencia"]."', '".@$pro_comp["prod_id"]."', '".@$pro_comp["unidade"]."', '".@$pro_comp["item_unid"]."', '".@$pro_comp["preco"]."', '".@$pro_comp["quantidade"]."', '".@$pro_comp["qtde_separada"]."', '".@$pro_comp["desconto"]."', '".@$pro_comp["outras_desp"]."', '".@$pro_comp["frete"]."', '".@$pro_comp["seguro"]."', '1');";
                                    $mysqli->query($qry_pro_orca);
                                }
                            }

                            $qry_new_orc = 'SELECT * FROM cad_comp WHERE id = "'.$orc_id.'"';
                            $qry_result_new_orc = $mysqli->query($qry_new_orc);
                            $row = mysqli_fetch_assoc($qry_result_new_orc);

                            $qry_pro_orca = 'SELECT p.* FROM pro_comp p WHERE p.ordem = "'.$row['id'].'"';
                            $qry_result_pro_orca = $mysqli->query($qry_pro_orca);
                            $qry_rows_pro_orca = mysqli_num_rows($qry_result_pro_orca);
                            if ($qry_rows_pro_orca > 0):
                                while($row_pro_orca = mysqli_fetch_assoc($qry_result_pro_orca)):
                                    $row["pro_comp"][] = $row_pro_orca;
                                endwhile;
                            endif;

                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 10;
                            $retorno['data'] = $row;
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Houve um problema ao gravar o pedido de compra.";
                        }
                    } else {
                        $qry = "UPDATE cad_comp SET sincronizado = '1'";
                        if(!empty($_POST['sku'])):
                            $qry .= ", sku = '".$_POST["sku"]."'";
                        endif;
                        if(!empty($_POST['status'])):
                            $qry .= ", status = '".$_POST["status"]."'";
                        endif;
                        if(!empty($_POST['situacao'])):
                            $qry .= ", situacao = '".$_POST["situacao"]."'";
                        endif;
                        if(!empty($_POST['data_baixa'])):
                            $qry .= ", data_baixa = '".$_POST["data_baixa"]."'";
                        endif;
                        if(!empty($_POST['data_receb'])):
                            $qry .= ", data_receb = '".$_POST["data_receb"]."'";
                        endif;
                        if(!empty($_POST['data_pedido'])):
                            $qry .= ", data_pedido = '".$_POST["data_pedido"]."'";
                        endif;
                        if(!empty($_POST['hora_receb'])):
                            $qry .= ", hora_receb = '".$_POST["hora_receb"]."'";
                        endif;
                        $qry .= " WHERE";
                        foreach ($_POST['codigos'] as $value) {
                            if($i > 0)
                                $qry .= " OR";
                            $qry .= " id = '".$value."'";
                            $i++;
                        }
                        if($mysqli->query($qry)) {
                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 10;
                            $retorno['data'] = $row;
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Houve um problema ao atualizar o pedido de compra.";
                        }
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function excluirCompra() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['codigos'])):
                
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    if(!empty($_POST["codigos"])) {
                        $qry_del = 'DELETE FROM pro_comp WHERE ordem IN (';
                        $i = 0;
                        foreach ($_POST["codigos"] as $cod) {
                            if($i > 0)
                                $qry_del .= ',';
                            $qry_del .= $cod;
                        }
                        $qry_del .= ')';
                        $mysqli->query($qry_del);

                        $qry_del = 'DELETE FROM cad_comp WHERE id IN (';
                        $i = 0;
                        foreach ($_POST["codigos"] as $cod) {
                            if($i > 0)
                                $qry_del .= ',';
                            $qry_del .= $cod;
                        }
                        $qry_del .= ')';
                        $mysqli->query($qry_del);

                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Código(s) não recebido(s).";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function excluirVenda() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['codigos'])):
                
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Orçamento
                        if($key == 1 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    if(!empty($_POST["codigos"])) {
                        $mensagem = "";
                        foreach ($_POST["codigos"] as $codigo) {                            
                            $remove = true;
                            $ct_receb = 'SELECT ct.* FROM ct_receb ct INNER JOIN par_orca p ON p.id = ct.PARCELA_ID WHERE p.orc_id = '.$codigo;
                            $res_ct_receb = $mysqli->query($ct_receb);
                            while($row_ct_receb = mysqli_fetch_assoc($res_ct_receb)):
                                if($row_ct_receb['VALOR_PAGO'] > 0)
                                    $remove = false;
                            endwhile;

                            if($remove == true) {
                                $ct_receb = 'SELECT ct.* FROM ct_receb ct INNER JOIN par_orca p ON p.id = ct.PARCELA_ID WHERE p.orc_id = '.$codigo;
                                $res_ct_receb = $mysqli->query($ct_receb);
                                while($row_ct_receb = mysqli_fetch_assoc($res_ct_receb)):
                                    $qry_del = 'DELETE FROM ct_receb WHERE CODIGO = '.$row_ct_receb["CODIGO"];
                                    $mysqli->query($qry_del);
                                endwhile;

                                $sel_cond = 'SELECT CODIGO FROM condicoes_orcamento WHERE ORCAMENTO = '.$codigo;
                                $res_cond = $mysqli->query($sel_cond);
                                while($row_cond = mysqli_fetch_assoc($res_cond)):
                                    $sel_ser_cond = 'SELECT CODIGO FROM servico_condicoes_orcamento WHERE CONDICOES_ORCAMENTO = '.$row_cond["CODIGO"];
                                    $res_ser_cond = $mysqli->query($sel_ser_cond);
                                    while($row_ser_cond = mysqli_fetch_assoc($res_ser_cond)):
                                        $qry_del = 'DELETE FROM parcela_servico_condicoes_orca WHERE SERVICO_CONDICOES_ORCA = '.$row_ser_cond["CODIGO"];
                                        $mysqli->query($qry_del);
                                    endwhile;
                                    $qry_del = 'DELETE FROM servico_condicoes_orcamento WHERE CONDICOES_ORCAMENTO = '.$row_cond["CODIGO"];
                                    $mysqli->query($qry_del);

                                    $sel_pro_cond = 'SELECT CODIGO FROM produto_condicoes_orcamento WHERE CONDICOES_ORCAMENTO = '.$row_cond["CODIGO"];
                                    $res_pro_cond = $mysqli->query($sel_pro_cond);
                                    while($row_pro_cond = mysqli_fetch_assoc($res_pro_cond)):
                                        $qry_del = 'DELETE FROM parcela_produto_condicoes_orca WHERE PRODUTO_CONDICOES_ORCA = '.$row_pro_cond["CODIGO"];
                                        $mysqli->query($qry_del);
                                    endwhile;
                                    $qry_del = 'DELETE FROM produto_condicoes_orcamento WHERE CONDICOES_ORCAMENTO = '.$row_cond["CODIGO"];
                                    $mysqli->query($qry_del);
                                endwhile;

                                $qry_del = 'DELETE FROM condicoes_orcamento WHERE ORCAMENTO = '.$codigo;
                                $mysqli->query($qry_del);

                                $qry_del = 'DELETE FROM ser_orca WHERE orc_id = '.$codigo;
                                $mysqli->query($qry_del);

                                $qry_del = 'DELETE FROM pro_orca WHERE orc_id = '.$codigo;
                                $mysqli->query($qry_del);

                                $qry_del = 'DELETE FROM par_orca WHERE orc_id = '.$codigo;
                                $mysqli->query($qry_del);

                                $qry_del = 'DELETE FROM cad_orca WHERE id = '.$codigo;
                                $mysqli->query($qry_del);
                            } else {
                                $mensagem .= "Venda '".$codigo."' não pode ser excluída por conter pagamento(s).\n";
                            }
                        }
                        
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['mensagem'] = $mensagem;
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Código(s) não recebido(s).";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Orçamento/Pedido - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getCtReceb() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Faturamento - MaestriaTech para SAX
                        if($key == 2 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 1)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    if($_POST["sincronizado"] == "S") {
                        $sincronizado = 1;
                    } else {
                        $sincronizado = 0;
                    }

                    $qry = 'SELECT c.*, p.orc_id FROM ct_receb c 
                        INNER JOIN unidades u ON u.id = c.FILIAL 
                        INNER JOIN par_orca p ON p.id = c.PARCELA_ID 
                        WHERE u.token = "' . $_POST['token'] . '"';
                    if(!empty($_POST["sincronizado"])) {
                        $qry .= " AND c.SINCRONIZADO = '".$sincronizado."'";
                    }
                    if(!empty($_POST["orcamento"])) {
                        $qry .= " AND p.orc_id = '".$_POST["orcamento"]."'";
                    }
                    $qry_result = $mysqli->query($qry);
                    $qry_rows = mysqli_num_rows($qry_result);
                    $orcamentos = array();
                    if ($qry_rows > 0):
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $qry_cc = 'SELECT c.*, ct.CODIGO as cod_ct_cartao FROM cartao_mvtos c 
                                INNER JOIN contas_cartoes cc ON cc.CARTAO = c.CODIGO 
                                INNER JOIN ct_cartao ct ON ct.CARTAO = c.CARTAO AND ct.CONTA = c.CONTA
                                WHERE cc.CT_RECEB = "'.$row["CODIGO"].'"';
                            $qry_cc_result = $mysqli->query($qry_cc);
                            $qry_cc_rows = mysqli_num_rows($qry_cc_result);

                            if ($qry_cc_rows > 0):
                                $row["cartao_mvtos"] = array();
                                while($row_cc = mysqli_fetch_assoc($qry_cc_result)):
                                    $row_cc["HISTORICO"] = utf8_encode($row_cc["HISTORICO"]);
                                    $row["cartao_mvtos"][] = $row_cc;
                                endwhile;
                            endif;
                            $ct_receb[] = $row;
                        endwhile;
                    endif;
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['data'] = $ct_receb;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Faturamento - MaestriaTech para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarCtReceb() {
        require_once ('Iugu.php');
        $iugu = new Iugu();
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(!empty($_POST['codigo'])):
                if(empty($_POST['valor_pago'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Valor Pago!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['data_pgto'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Data do Pagamento!';
                    exit(json_encode($retorno));
                endif;
            else:
                if(empty($_POST['documento'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Documento!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['cliente'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Cliente!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['valor'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Valor!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['vencimento'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Vencimento!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['competencia'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Competencia!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['tipo_recebimento'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Tipo do Recebimento!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['data_lancamento'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Data de Lançamento!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['parcela'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Parcela!';
                    exit(json_encode($retorno));
                endif;
                /*if(empty($_POST['parcela_id'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o ID da Parcela!';
                    exit(json_encode($retorno));
                endif;*/
                if(empty($_POST['tipo_condicao_parcela_id'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o ID do Tipo Condição Parcela!';
                    exit(json_encode($retorno));
                endif;
            endif;


            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Faturamento
                        if($key == 2 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    $acao = "";
                    if(@$_POST["codigo"]) {
                        $acao = "update";
                        $qry = "UPDATE ct_receb SET VALOR_PAGO = '".$_POST["valor_pago"]."', DATA_PGTO = '".$_POST["data_pgto"]."', DATA_ATUALIZADA = '".date('Y-m-d')."' , SINCRONIZADO = '1' WHERE CODIGO = '".$_POST["codigo"]."';";
                    } else {
                        $acao = "insert";
                        if($_POST["orc_id"] != "") {
                            $test_iugu = true;
                            
                            if($dados_unid["iugu_status"] == "S") {
                                $qry_cur_orc = 'SELECT * FROM cad_orca WHERE id = "'.$_POST['orc_id'].'"';
                                $qry_result_cur_orc = $mysqli->query($qry_cur_orc);
                                $row_cur = mysqli_fetch_assoc($qry_result_cur_orc);

                                $qry_user = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS nome, u.email, p.celular, p.cpf, p.cep, p.numero, p.endereco, p.cidade, p.uf, p.bairro, p.complemento, i.iugu_id FROM users u INNER JOIN pessoas p ON u.id = p.user_id INNER JOIN iugu_users i ON i.user_id = u.id WHERE u.id = '".$row_cur['user_id']."'";
                                $qry_result_user = $mysqli->query($qry_user);
                                $qry_rows_user = mysqli_num_rows($qry_result_user);

                                if($qry_rows_user > 0) {
                                    $row_user = mysqli_fetch_assoc($qry_result_user);

                                    $tipo_pagamento = "";
                                    if(in_array($_POST["tipo_recebimento"], (array)unserialize($dados_unid["iugu_receb_credito"]))) {
                                        $tipo_pagamento = "credit_card";
                                    } elseif(in_array($_POST["tipo_recebimento"], (array)unserialize($dados_unid["iugu_receb_boleto"]))) {
                                        $tipo_pagamento = "bank_slip";
                                    }

                                    if($tipo_pagamento != "") {
                                        $retorno_iugu = $iugu->criar_fatura($dados_unid["iugu_token"], array("nome" => $row_user["nome"], "celular" => $row_user["celular"], "email" => $row_user["email"], "cpf" => $row_user["cpf"], "cep" => $row_user["cep"], "numero" => $row_user["numero"], "endereco" => $row_user["endereco"], "cidade" => $row_user["cidade"], "uf" => $row_user["uf"], "bairro" => $row_user["bairro"], "complemento" => $row_user["complemento"], "customer_id" => $row_user["iugu_id"]), $_POST["vencimento"], $_POST["orc_id"], $_POST["valor"], $tipo_pagamento);
                                        
                                        if($retorno_iugu["erro"] == false) {
                                            $qry_invoice = 'INSERT INTO iugu_invoices (cad_orca_id, iugu_id, tipo, linha_digitavel) VALUES("'.$_POST["orc_id"].'", "'.$retorno_iugu['data']['id'].'", "CA", "'.@$retorno_iugu['data']["bank_slip"]["digitable_line"].'")';
                                            $mysqli->query($qry_invoice);

                                            $qry_aud = 'INSERT INTO cad_clie_auditoria (empresa_id, LIG_ID_CLIENTE, LIG_ID_USUARIO, REGISTRO, STATUS, MENSAGEM, DATA_REGISTRO, HORA_REGISTRO) VALUES("'.$dados_unid["empresa_id"].'", "'.$row_cur['user_id'].'", "'.$_POST['usuario'].'", "true", "3", "Fatura de cancelamento criada na Iugu ID '.$row_iugu["iugu_id"].'.", "'.date("Y-m-d").'", "'.date("H:i:s").'")';
                                            $mysqli->query($qry_aud);
                                        }
                                    } else {
                                        $qry_aud = 'INSERT INTO cad_clie_auditoria (empresa_id, LIG_ID_CLIENTE, LIG_ID_USUARIO, REGISTRO, STATUS, MENSAGEM, DATA_REGISTRO, HORA_REGISTRO) VALUES("'.$dados_unid["empresa_id"].'", "'.$row_cur['user_id'].'", "'.$_POST['usuario'].'", "true", "3", "Fatura de cancelamento não cadastrada na Iugu por não encontrar tipo de recebimento enviado.", "'.date("Y-m-d").'", "'.date("H:i:s").'")';
                                            $mysqli->query($qry_aud);
                                    }
                                }
                            }

                            $qry_par_orca = "INSERT INTO par_orca (orc_id, tipo_recebimento, parcela, valor, vencimento, carencia_desconto_pontualidade, tipo_valor_desconto_pontualidade) VALUES ('".$_POST["orc_id"]."', '".$_POST["tipo_recebimento"]."', '".$_POST["parcela"]."', '".$_POST["valor"]."', '".$_POST["vencimento"]."', '0', 'VALOR');";
                            $mysqli->query($qry_par_orca);
                            $_POST["parcela_id"] = $mysqli->insert_id;
                        }

                        $qry = "INSERT INTO ct_receb (FILIAL, DOCUMENTO, CLIENTE, VALOR, VENCIMENTO, TIPO_RECEBIMENTO, COMPETENCIA, VENDEDOR, DATA_LANCAMENTO, PARCELA, PARCELA_ID, TIPO_CONDICAO_PARCELA_ID, CENTRO_CUSTO, DEPART, SINCRONIZADO) VALUES ('".$dados_unid["id"]."', '".$_POST["documento"]."', '".$_POST["cliente"]."', '".$_POST["valor"]."', '".$_POST["vencimento"]."', '".$_POST["tipo_recebimento"]."', '".$_POST["competencia"]."', '".@$_POST["vendedor"]."', '".$_POST["data_lancamento"]."', '".$_POST["parcela"]."', '".$_POST["parcela_id"]."', '".$_POST["tipo_condicao_parcela_id"]."', '".@$_POST["centro_custo"]."', '".@$_POST["depart"]."', '1');";
                    }

                    if($qry_result = $mysqli->query($qry)) {
                        if($acao == "insert")
                            $ctreceb_id = $mysqli->insert_id;
                        else
                            $ctreceb_id = $_POST["codigo"];

                        $qry_new_ctreceb = 'SELECT c.*, p.orc_id FROM ct_receb c INNER JOIN par_orca p ON p.id = c.PARCELA_ID WHERE c.CODIGO = "'.$ctreceb_id.'"';
                        $qry_result_new_ctreceb = $mysqli->query($qry_new_ctreceb);
                        $dados_new_ctreceb = mysqli_fetch_assoc($qry_result_new_ctreceb);

                        if(($dados_new_ctreceb["DATA_PGTO"] != "" && $dados_new_ctreceb["DATA_PGTO"] != "0000-00-00") && $dados_new_ctreceb["VALOR_PAGO"] != 0) {

                            //alteracao realizada dia 23/07/2021
                            //correção para não extender prazo do usuário na voxy caso contrato esteja cancelado ou desistido
                            $qry_param = "SELECT LIG_STATUS_CONTRATO_06, LIG_ID_ANULADO FROM parametro WHERE LIG_ID_FILIAL = '".$dados_unid["id"]."'";
                            $qry_result_param = $mysqli->query($qry_param);
                            $dados_param = mysqli_fetch_assoc($qry_result_param);

                            if($dados_param["LIG_STATUS_CONTRATO_06"] != "" && $dados_param["LIG_ID_ANULADO"] != "") {

                                $qry_cur_orc = 'SELECT * FROM cad_orca WHERE id = "'.$_POST['orc_id'].'"';
                                $qry_result_cur_orc = $mysqli->query($qry_cur_orc);
                                $row_cur = mysqli_fetch_assoc($qry_result_cur_orc);

                                if($row_cur['status'] != $dados_param["LIG_STATUS_CONTRATO_06"] && $row_cur['status'] != $dados_param["LIG_ID_ANULADO"]) {

                                    $qry_cond = "SELECT c.*, p.NUM_PARCELAS FROM tipo_condicao_parcela_orcamento c INNER JOIN cad_fpgt p ON p.CODIGO = c.Id_formpag WHERE c.ID = '".$dados_new_ctreceb["TIPO_CONDICAO_PARCELA_ID"]."' AND c.entrada_matricula = 'False'";
                                    $qry_cond_res = $mysqli->query($qry_cond);
                                    $dados_cond = mysqli_fetch_assoc($qry_cond_res);

                                    $fim_vigencia = "";
                                    if(strstr(utf8_encode($dados_cond["DESCRICAO"]), 'CARTÃO DE CRÉDITO')) {
                                        $fim_vigencia = date('Y-m-d', strtotime("+".$dados_cond["NUM_PARCELAS"]." months"));
                                    } elseif(strstr(utf8_encode($dados_cond["DESCRICAO"]), 'RECORR')) {
                                        $fim_vigencia = date('Y-m-d', strtotime("+40 days"));
                                    }

                                    $qry_update_orc = "UPDATE cad_orca SET fim_vigencia = '".$fim_vigencia."' WHERE id = '".$dados_new_ctreceb["orc_id"]."';";
                                    $mysqli->query($qry_update_orc);

                                    $qry_update_ead_user = "UPDATE ead_users SET expiration_date = '".date('Y-m-d', strtotime("+40 days"))."' WHERE user_id = '".$dados_new_ctreceb["CLIENTE"]."';";
                                    $mysqli->query($qry_update_ead_user);

                                    $dados = array('expiration_date' => date("Y-m-d", strtotime("+40 days")));
                                    $this->gravar_user_voxy($dados_new_ctreceb["CLIENTE"], $dados);
                                }
                            }
                            
                        }


                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['data'] = $dados_new_ctreceb;
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Houve um problema ao gravar o ct receb.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Faturamento - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarCtRecebSincronizado() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(!$_POST['codigos']):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha pelo menos um código!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Faturamento
                        if($key == 2 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    $acao = "";
                    $msg_retorno = array();

                    foreach ($_POST['codigos'] as $key => $value) {
                        $qry_ct_receb = "SELECT CODIGO FROM ct_receb WHERE CODIGO = '".$value."'";
                        $qry_result_ct_receb = $mysqli->query($qry_ct_receb);
                        $qry_rows_ct_receb = mysqli_num_rows($qry_result_ct_receb);
                        if($qry_rows_ct_receb == 0):
                            $msg_retorno[] = array("resposta" => false, "mensagem" => "ct receb ".$value." não encontrado.");
                            unset($_POST['codigos'][$key]);
                        endif;
                    }

                    $acao = "update";
                    $qry = "UPDATE ct_receb SET DATA_ATUALIZADA = '".date('Y-m-d')."' , SINCRONIZADO = '1' WHERE";

                    $i = 0;
                    foreach ($_POST['codigos'] as $value) {
                        if($i > 0)
                            $qry .= " OR";
                        $qry .= " CODIGO = '".$value."'";
                        $msg_retorno[] = array("resposta" => true, "mensagem" => "ct receb ".$value." sincronizado.");
                        $i++;
                    }

                    $qry .= ";";

                    if($qry_result = $mysqli->query($qry)) {
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['data'] = $msg_retorno;
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Houve um problema ao gravar o ct receb.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Faturamento - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function removerCtReceb() {
        require_once ('Iugu.php');
        $iugu = new Iugu();

        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(!$_POST['codigos']):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha pelo menos um código!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Faturamento
                        if($key == 2 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                        
                    }
                }
                
                if($ws_ativo == true) {
                    $msg_retorno = array();
                    $msg_retorno_verif = "";

                    foreach ($_POST['codigos'] as $key => $value) {
                        $qry_ct_receb = "SELECT CODIGO FROM ct_receb WHERE VALOR_PAGO = 0 AND DATA_PGTO = '0000-00-00' AND CODIGO = '".$value."'";
                        $qry_result_ct_receb = $mysqli->query($qry_ct_receb);
                        $qry_rows_ct_receb = mysqli_num_rows($qry_result_ct_receb);
                        if($qry_rows_ct_receb == 0):
                            $msg_retorno_verif .= "ct receb ".$value." pago.\n";
                            //unset($_POST['codigos'][$key]);
                        endif;
                    }

                    if(empty($msg_retorno_verif)) {
                        $qry = "DELETE FROM ct_receb WHERE";
                        $qry_iugu_invoice = "";
                        $txt_aud = "";

                        $i = 0;
                        foreach ($_POST['codigos'] as $value) {
                            if($i > 0) {
                                $qry .= " OR";
                                $qry_iugu_invoice .= " OR";
                                $txt_aud .= ",";
                            }
                            $txt_aud .= " CODIGO = '".$value."'";
                            $qry .= " CODIGO = '".$value."'";
                            $qry_iugu_invoice .= " ct_receb_id = '".$value."'";
                            $msg_retorno[] = array("resposta" => true, "mensagem" => "ct receb ".$value." removido.");
                            $i++;
                        }

                        $qry .= ";";
                        
                        
                        if($qry_iugu_invoice != "") {
                        
                            //busca o id do usuario no cad_orca
                            $qry_orc = 'SELECT * FROM cad_orca WHERE id = "' . $_POST['orcamento_id'] . '"';
                            $qry_result_orc = $mysqli->query($qry_orc);
                            $dados_orc = mysqli_fetch_assoc($qry_result_orc);
                        
                            //busca os iugu_invoices a serem cancelados
                            $sel_iugu_invoice = "SELECT * FROM iugu_invoices WHERE".$qry_iugu_invoice;
                            $res_iugu_invoice = $mysqli->query($sel_iugu_invoice);
                            while($row_iugu = mysqli_fetch_assoc($res_iugu_invoice)):
                                $qry_aud = 'INSERT INTO cad_clie_auditoria (empresa_id, LIG_ID_CLIENTE, LIG_ID_USUARIO, REGISTRO, STATUS, MENSAGEM, DATA_REGISTRO, HORA_REGISTRO) VALUES("'.$dados_unid["empresa_id"].'", "'.$dados_orc['user_id'].'", "'.$_POST['usuario'].'", "true", "3", "Iugu invoice ID #'.$row_iugu["iugu_id"].' removido.", "'.date("Y-m-d").'", "'.date("H:i:s").'")';
                                $mysqli->query($qry_aud);

                                //alteracao realizada dia 13/07/2021
                                if($row_iugu["tipo"] == "P" && $row_iugu["status"] == "pending") {

                                    $retorno_iugu = $iugu->cancelar_fatura($dados_unid["iugu_token"], $row_iugu["iugu_id"]);
            
                                    if($retorno_iugu["erro"] == false) {
                                        $qry_delete_iugu_invoices = "DELETE FROM iugu_invoices WHERE id = ".$row_iugu["id"];
                                        if($qry_delete_iugu_invoices_result = $mysqli->query($qry_delete_iugu_invoices)) {
                                            
                                        } else {
                                            $retorno['erro'] = TRUE;
                                            $retorno['codigo'] = 1;
                                            $retorno['mensagem'] = "Houve um problema ao remover iugu_invoices id ".$row_iugu["id"].".";
                                            mysqli_close($mysqli);
                                            return $retorno;
                                        }
                                    }
                                }
                                //---
                            endwhile;
                            
                        }
                        
                        if($qry_result = $mysqli->query($qry)) {
                            $qry_aud = 'INSERT INTO cad_clie_auditoria (empresa_id, LIG_ID_CLIENTE, LIG_ID_USUARIO, REGISTRO, STATUS, MENSAGEM, DATA_REGISTRO, HORA_REGISTRO) VALUES("'.$dados_unid["empresa_id"].'", "'.$dados_orc['user_id'].'", "'.$_POST['usuario'].'", "true", "3", "Contas a receber removidas '.$txt_aud.'.", "'.date("Y-m-d").'", "'.date("H:i:s").'")';
                            $mysqli->query($qry_aud);
                        
                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 10;
                            $retorno['data'] = $msg_retorno;
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Houve um problema ao gravar o ct receb.";
                        }
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = $msg_retorno_verif;
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Faturamento - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarBoletoPdf(){
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty(trim($_POST['codigo']))):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Código CT RECEB não encontrado!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['boleto'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Boleto não encontrado!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Faturamento - MaestriaTech para SAX
                        if($key == 2 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }

                if($ws_ativo == true) {
                    $encoded_string = trim($_POST['boleto']);
                    $data = base64_decode($encoded_string);

                    $f = finfo_open();
                    $mime_type = finfo_buffer($f, $data, FILEINFO_MIME_TYPE);

                    if($mime_type != "application/pdf"):
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = 'Tipo do arquivo incorreto!';
                        exit(json_encode($retorno));
                    endif;
                    
                    file_put_contents(BASEPATH.'/upload/boletos/'.$_POST['codigo'].'.pdf', $data);

                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Boleto cadastrado!';
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Faturamento - SAX para SAX' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarCondicao() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['descricao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Descrição!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['entrada_matricula'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Entrada Matrícula!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['id_formpag'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Forma Pagamento!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['id_planofin'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Plano Financeiro!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['calc_parc']) && $_POST['calc_parc'] != 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Calc Parc!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['forma_receb'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Forma Recebimento!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['id_tiporeceb'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Tipo Recebimento!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['tipo_valor_desconto_pontualidade'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Tipo Valor Desconto Pontualidade!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['valor_desconto_pontualidade']) && $_POST['valor_desconto_pontualidade'] != 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Valor Desconto Pontualidade!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['carencia_desconto_pontualidade']) && $_POST['carencia_desconto_pontualidade'] != 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Carência Desconto Pontualidade!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);

                $qry_verif_fpgto = 'SELECT p.CODIGO FROM cad_fpgt p WHERE p.CODIGO = "'.$_POST["id_formpag"].'" AND p.empresa_id = "'.$dados_unid["empresa_id"].'"';
                $qry_result_fpgto = $mysqli->query($qry_verif_fpgto);
                if(mysqli_num_rows($qry_result_fpgto) == 0) {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Forma Pagamento não encontrada.";
                    mysqli_close($mysqli);
                    return $retorno;
                }

                $qry_verif_tiprece = 'SELECT r.CODIGO FROM tip_rece r WHERE r.CODIGO = "'.$_POST["id_tiporeceb"].'" AND r.FILIAL = "'.$dados_unid["id"].'"';
                $qry_result_tiprece = $mysqli->query($qry_verif_tiprece);
                if(mysqli_num_rows($qry_result_tiprece) == 0) {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Tipo Recebimento não encontrado.";
                    mysqli_close($mysqli);
                    return $retorno;
                }

 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Financeiro - SAX para MaestriaTech
                        if($key == 3 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }
                        
                    }
                }
                
                if($ws_ativo == true) {
                    $acao = "";
                    if($_POST["id"]) {
                        $acao = "update";
                        $qry = "UPDATE tipo_condicao_parcela_orcamento SET DESCRICAO = '".utf8_encode($_POST["descricao"])."', material_didatico = '".$_POST["material_didatico"]."', entrada_matricula = '".$_POST["entrada_matricula"]."', Id_formpag = '".$_POST["id_formpag"]."', Id_planofin = '".$_POST["id_planofin"]."', Id_centrocusto = '".$_POST["id_centrocusto"]."', CalcParc = '".$_POST["calc_parc"]."', FormaReceb = '".$_POST["forma_receb"]."', AgrupaParc = '".$_POST["agrupa_parc"]."', Id_TipoReceb = '".$_POST["id_tiporeceb"]."', TIPOVALORDESCONTOPONTUALIDADE = '".$_POST["tipo_valor_desconto_pontualidade"]."', VALOR_DESCONTO_PONTUALIDADE = '".$_POST["valor_desconto_pontualidade"]."', CARENCIA_DESCONTO_PONTUALIDADE = '".$_POST["carencia_desconto_pontualidade"]."' WHERE ID = '".$_POST["id"]."';";
                    } else {
                        $acao = "insert";
                        $qry = "INSERT INTO tipo_condicao_parcela_orcamento (unidade_id, DESCRICAO, material_didatico, entrada_matricula, Id_formpag, Id_planofin, Id_centrocusto, CalcParc, DataCad, FormaReceb, AgrupaParc, Id_TipoReceb, TIPOVALORDESCONTOPONTUALIDADE, VALOR_DESCONTO_PONTUALIDADE, CARENCIA_DESCONTO_PONTUALIDADE) VALUES ('".$dados_unid["id"]."', '".utf8_encode($_POST["descricao"])."', '".$_POST["material_didatico"]."', '".$_POST["entrada_matricula"]."', '".$_POST["id_formpag"]."', '".$_POST["id_planofin"]."', '".$_POST["id_centrocusto"]."', '".$_POST["calc_parc"]."', '".date('Y-m-d')."', '".$_POST["forma_receb"]."', '".$_POST["agrupa_parc"]."', '".$_POST["id_tiporeceb"]."', '".$_POST["tipo_valor_desconto_pontualidade"]."', '".$_POST["valor_desconto_pontualidade"]."', '".$_POST["carencia_desconto_pontualidade"]."');";
                    }
                    if($qry_result = $mysqli->query($qry)) {
                        if($acao == "insert") {
                            $condicao_id = $mysqli->insert_id;

                            $qry_curso_desconto = "INSERT INTO curso_desconto (CURSO_ID, TIPO_CONDICAO_PARCELA_ID) VALUES ('7', '".$condicao_id."');";
                            $mysqli->query($qry_curso_desconto);
                        }
                        else
                            $condicao_id = $_POST["id"];


                        $qry_ver_serv = 'SELECT * FROM tipo_condicao_parcela_servicos WHERE LIG_ID_TIPO_CONDICAO = "'.$condicao_id.'"';
                        $res_ver_serv = $mysqli->query($qry_ver_serv);
                        $servicos_cond_existentes = array();
                        while($row = mysqli_fetch_assoc($res_ver_serv)):
                            $servicos_cond_existentes[] = $row;
                        endwhile;
                        if(count(@$_POST["servicos"]) > 0) {
                            foreach ($_POST["servicos"] as $serv) {
                                $acao = "";
                                foreach ($servicos_cond_existentes as $key => $value) {
                                    if($ser["id"] == $value["SERVICO"]) {
                                        $acao = "update";
                                        unset($servicos_cond_existentes[$key]);
                                    } else
                                        $acao = "insert";
                                }
                                if($acao == "update") {
                                    $qry_servico = "UPDATE tipo_condicao_parcela_servicos SET VALOR = '".$serv["valor"]."', SINCRONIZADO = '1' WHERE LIG_ID_TIPO_CONDICAO = '".$condicao_id."' AND LIG_ID_SERVICO = '".$serv["id"]."';";
                                    $mysqli->query($qry_servico);
                                } else {
                                    $qry_servico = "INSERT INTO tipo_condicao_parcela_servicos (LIG_ID_TIPO_CONDICAO, LIG_ID_SERVICO, VALOR, SINCRONIZADO) VALUES ('".$condicao_id."', '".$serv["id"]."', '".$serv["valor"]."', '1');";
                                    $mysqli->query($qry_servico);
                                }
                            }
                            if(count($servicos_cond_existentes) > 0) {
                                foreach ($servicos_cond_existentes as $key => $value) {
                                    $qry_del = "DELETE FROM tipo_condicao_parcela_servicos WHERE LIG_ID_TIPO_CONDICAO = '".$condicao_id."' AND LIG_ID_SERVICO = '".$value["SERVICO"]."'";
                                    $mysqli->query($qry_del);
                                }
                            }
                        } else {
                            $qry_del = "DELETE FROM tipo_condicao_parcela_servicos WHERE LIG_ID_TIPO_CONDICAO = '".$condicao_id."'";
                            $mysqli->query($qry_del);
                        }

                        $qry_ver_prod = 'SELECT * FROM tipo_condicao_parcela_produtos WHERE LIG_ID_TIPO_CONDICAO = "'.$condicao_id.'"';
                        $res_ver_prod = $mysqli->query($qry_ver_prod);
                        $produtos_cond_existentes = array();
                        while($row = mysqli_fetch_assoc($res_ver_prod)):
                            $produtos_cond_existentes[] = $row;
                        endwhile;
                        if(count(@$_POST["produtos"]) > 0) {
                            foreach ($_POST["produtos"] as $prod) {
                                $acao = "";
                                foreach ($produtos_cond_existentes as $key => $value) {
                                    if($prod["id"] == $value["LIG_ID_PRODUTO"]) {
                                        $acao = "update";
                                        unset($produtos_cond_existentes[$key]);
                                    } else
                                        $acao = "insert";
                                }
                                if($acao == "update") {
                                    $qry_produto = "UPDATE tipo_condicao_parcela_produtos SET VALOR = '".$prod["valor"]."', SINCRONIZADO = '1' WHERE LIG_ID_TIPO_CONDICAO = '".$condicao_id."' AND LIG_ID_PRODUTO = '".$prod["id"]."';";
                                    $mysqli->query($qry_produto);
                                } else {
                                    $qry_produto = "INSERT INTO tipo_condicao_parcela_produtos (LIG_ID_TIPO_CONDICAO, LIG_ID_PRODUTO, VALOR, SINCRONIZADO) VALUES ('".$condicao_id."', '".$prod["id"]."', '".$prod["valor"]."', '1');";
                                    $mysqli->query($qry_produto);
                                }
                            }
                            if(count($produtos_cond_existentes) > 0) {
                                foreach ($produtos_cond_existentes as $key => $value) {
                                    $qry_del = "DELETE FROM tipo_condicao_parcela_produtos WHERE LIG_ID_TIPO_CONDICAO = '".$condicao_id."' AND LIG_ID_PRODUTO = '".$value["PRODUTO"]."'";
                                    $mysqli->query($qry_del);
                                }
                            }
                        } else {
                            $qry_del = "DELETE FROM tipo_condicao_parcela_produtos WHERE LIG_ID_TIPO_CONDICAO = '".$condicao_id."'";
                            $mysqli->query($qry_del);
                        }

                        $qry_new_condicao = 'SELECT * FROM tipo_condicao_parcela_orcamento WHERE ID = "'.$condicao_id.'"';
                        $qry_result_new_condicao = $mysqli->query($qry_new_condicao);
                        $dados_new_condicao = mysqli_fetch_assoc($qry_result_new_condicao);

                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['data'] = $dados_new_condicao;
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Houve um problema ao gravar a condição.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Financeiro - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function gravarAvaliacaoNota() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            //if(empty($_POST['id_avaliacao'])):
                if(empty($_POST['curso'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Curso!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['turma'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Turma!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['disciplina'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Disciplina!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['peso'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Peso!';
                    exit(json_encode($retorno));
                endif;
            //endif;
            if(empty($_POST['tipo_avaliacao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Tipo Avaliação!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['aluno'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Aluno!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['contrato'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Contrato!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['nota'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Nota!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['data_avaliacao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Data Avaliação!';
                exit(json_encode($retorno));
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);

                $qry_verif_turma = 'SELECT t.ID FROM turma t WHERE t.ID = "'.$_POST["turma"].'" AND t.unidade = "'.$dados_unid["id"].'"';
                $qry_result_turma = $mysqli->query($qry_verif_turma);
                if(mysqli_num_rows($qry_result_turma) == 0) {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Turma não encontrada.";
                    mysqli_close($mysqli);
                    return $retorno;
                }

                /*$qry_verif_disc = 'SELECT d.ID_DISCIPLINA FROM disciplina d WHERE d.ID_DISCIPLINA = "'.$_POST["disciplina"].'" AND d.empresa_id = "'.$dados_unid["empresa_id"].'"';
                $qry_result_disc = $mysqli->query($qry_verif_disc);
                if(mysqli_num_rows($qry_result_disc) == 0) {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Disciplina não encontrada.";
                    mysqli_close($mysqli);
                    return $retorno;
                }*/

 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Pedagógico - SAX para MaestriaTech
                        if($key == 4 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }
                    }
                }
                
                if($ws_ativo == true) {
                    $acao = "";
                    if($_POST["id_avaliacao"]) {
                        $acao = "update";
                        $qry = "UPDATE pedagogico_avaliacoes SET LIG_ID_CURSO = '".$_POST["curso"]."', LIG_ID_TURMA = '".$_POST["turma"]."', LIG_ID_DISCIPLINA = '".$_POST["disciplina"]."', LIG_ID_TIPOAVALIACAO = '".$_POST["tipo_avaliacao"]."', PESO = '".$_POST["peso"]."', OBS = '".$_POST["obs"]."', DATA_ALTERACAO = '".date('Y-m-d')."' WHERE ID_AVALIACAO = '".$_POST["id_avaliacao"]."';";
                    } else {
                        $acao = "insert";
                        $qry = "INSERT INTO pedagogico_avaliacoes (LIG_ID_CURSO, LIG_ID_TURMA, LIG_ID_DISCIPLINA, LIG_ID_TIPOAVALIACAO, PESO, DATA_CADASTRO, DATA_ALTERACAO, OBS, SINCRONIZADO) VALUES ('".$_POST["curso"]."', '".$_POST["turma"]."', '".$_POST["disciplina"]."', '".$_POST["tipo_avaliacao"]."', '".$_POST["peso"]."', '".date('Y-m-d')."', '".date('Y-m-d')."', '".$_POST["obs"]."', '1');";
                    }
                    if($qry_result = $mysqli->query($qry)) {
                        if($acao == "insert")
                            $avaliacao_id = $mysqli->insert_id;
                        else
                            $avaliacao_id = $_POST["id_avaliacao"];

                        $qry = "INSERT INTO pedagogico_avaliacoes_notas (LIG_ID_AVALIACAO, LIG_ID_ALUNO, LIG_ID_CONTRATO, LIG_ID_TIPOAVALIACAO, NOTA, DATA_AVALIACAO, DATA_ALTERACAO) VALUES ('".$_POST["id_avaliacao"]."', '".$_POST["aluno"]."', '".$_POST["contrato"]."', '".$_POST["tipo_avaliacao"]."', '".$_POST["nota"]."', '".date('Y-m-d')."', '".date('Y-m-d')."');";
                        $mysqli->query($qry);
                        $avaliacao_nota_id = $mysqli->insert_id;

                        $qry_new_nota = 'SELECT * FROM pedagogico_avaliacoes_notas WHERE ID = "'.$avaliacao_nota_id.'"';
                        $qry_result_new_nota = $mysqli->query($qry_new_nota);
                        $dados_new_nota = mysqli_fetch_assoc($qry_result_new_nota);

                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 10;
                        $retorno['data'] = $dados_new_nota;
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = "Houve um problema ao gravar a nota.";
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Pedagógico - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }


    protected function gravarNf() {
        if($this->method == 'POST'):
            if(empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o Token!';
                exit(json_encode($retorno));
            endif;
            if(empty($_POST['codigos'])):
                if(empty($_POST['serie'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Série!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['numero_nf'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha o Número Nf!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['cli_for'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha Cliente ou Fornecedor!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['codigo_cli_for'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha Código Cliente/Fornecedor!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['forma_pagamento'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Forma Pagamento!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['data_emissao'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Data Emissão!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['data_saida_entrada'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Data Saída Entrada!';
                    exit(json_encode($retorno));
                endif;
                if(empty($_POST['hora_saida_entrada'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Por favor preencha a Hora Saída Entrada!';
                    exit(json_encode($retorno));
                endif;
            endif;

            $mysqli = $this->conexao->getDatabase();

            $ws_ativo = false;

            $qry_unid = 'SELECT u.* FROM unidades u WHERE u.token = "' . $_POST['token'] . '"';
            $qry_result_unid = $mysqli->query($qry_unid);
            $qry_rows_unid = mysqli_num_rows($qry_result_unid);
            if ($qry_rows_unid == 0):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Unidade não encontrada, contate o administrador!';
            else:
                $dados_unid = mysqli_fetch_assoc($qry_result_unid);
 
                if(!empty(unserialize($dados_unid["recursos"]))) {
                    $dados = array();
                    foreach (unserialize($dados_unid["recursos"]) as $key => $value) {
                        //Faturamento
                        if($key == 2 && count($value) > 0) {
                            foreach ($value as $v) {
                                if($v == 2)
                                    $ws_ativo = true;
                            }
                        }                   
                    }
                }
                
                if($ws_ativo == true) {
                    if(empty($_POST["codigos"])) {
                        if($_POST['cli_for'] == "C") {
                            $qry_user = 'SELECT c.* FROM clientes c WHERE c.id = "' . $_POST['codigo_cli_for'] . '"';
                            $qry_user_result = $mysqli->query($qry_user);
                            $qry_user_rows = mysqli_num_rows($qry_user_result);
                            if($qry_user_rows == 0) {
                                $retorno['erro'] = TRUE;
                                $retorno['codigo'] = 1;
                                $retorno['mensagem'] = 'Cliente não encontrado!';
                                exit(json_encode($retorno));
                            } else {
                                $dados_user = mysqli_fetch_assoc($qry_user_result);
                            }
                        } elseif($_POST["cli_for"] == "F") {
                            $qry_user = 'SELECT f.* FROM fornecedores f WHERE f.id = "' . $_POST['codigo_cli_for'] . '"';
                            $qry_user_result = $mysqli->query($qry_user);
                            $qry_user_rows = mysqli_num_rows($qry_user_result);
                            if($qry_user_rows == 0) {
                                $retorno['erro'] = TRUE;
                                $retorno['codigo'] = 1;
                                $retorno['mensagem'] = 'Fornecedor não encontrado!';
                                exit(json_encode($retorno));
                            } else {
                                $dados_user = mysqli_fetch_assoc($qry_user_result);
                            }
                        }

                        $qry = "INSERT INTO cad_nf (FILIAL, SERIE, NUMERO_NF, CHAVE_NFE, ULT_MOTIVO_NFE, SITUACAO_NFE, CLI_FOR, CODIGO_CLI_FOR, ENDERECO_CLI_FOR, NUMERO_CLI_FOR, COMPLEMENTO_CLI_FOR, BAIRRO_CLI_FOR, CIDADE_CLI_FOR, ESTADO_CLI_FOR, CEP_CLI_FOR, FORMA_PAGAMENTO, TOTAL_PRODUTOS, VALOR_FRETE, TOTAL_SERVICOS, TOTAL_NF, FRETE, DATA_EMISSAO, DATA_SAIDA_ENTRADA, HORA_SAIDA_ENTRADA, DATA_CANCELAMENTO, COMPETENCIA, QTDE_PARCELAS, DIAS_PRIMEIRA, INTERVALO_PARCELAS, OBSERVACOES, ID_INTERNA, SINCRONIZADO, PREVISAO_ENTREGA) VALUES ('".$dados_unid["id"]."', '".@$_POST["serie"]."', '".@$_POST["numero_nf"]."', '".@$_POST["chave_nfe"]."', '".@$_POST["ult_motivo_nfe"]."', '".@$_POST["situacao_nfe"]."', '".@$_POST["cli_for"]."', '".@$_POST["codigo_cli_for"]."', '".@$_POST["endereco_cli_for"]."', '".@$_POST["numero_cli_for"]."', '".@$_POST["complemento_cli_for"]."', '".@$_POST["bairro_cli_for"]."', '".@$_POST["cidade_cli_for"]."', '".@$_POST["estado_cli_for"]."', '".@$_POST["cep_cli_for"]."', '".@$_POST["forma_pagamento"]."', '".@$_POST["total_produtos"]."', '".@$_POST["valor_frete"]."', '".@$_POST["total_servicos"]."', '".@$_POST["total_nf"]."', '".@$_POST["frete"]."', '".@$_POST["data_emissao"]."', '".@$_POST["data_saida_entrada"]."', '".@$_POST["hora_saida_entrada"]."', '".@$_POST["data_cancelamento"]."', '".@$_POST["competencia"]."', '".@$_POST["qtde_parcelas"]."', '".@$_POST["dias_primeira"]."', '".@$_POST["intervalo_parcelas"]."', '".@$_POST["observacoes"]."', '".@$_POST["id_interna"]."', '1', '".@$_POST["previsao_entrega"]."');";

                        if($qry_result = $mysqli->query($qry)) {
                            $nf_id = $mysqli->insert_id;

                            if(count(@$_POST["mvto_produtos"]) > 0) {
                                foreach ($_POST["mvto_produtos"] as $mvto_produto) {
                                    $qry_mvto_produto = "INSERT INTO mvto_produto (CHAVE_MVTO, ITEM, TIPO, ENT_SAI, MOV_SALDO, PRODUTO, UNIDADE, QUANTIDADE, QTDE_SEPARADA, QTDE_EXPEDIDA, VALOR_UNITARIO, VALOR_DESCONTO, TOTAL, CUSTO_ADIC, OUTRAS_DESP, FRETE, DATA_MVTO, HORA_MVTO, CUSTO_MEDIO, ULT_CUSTO, LBV, CANCELADA, STATUS, SINCRONIZADO) VALUES ('".$nf_id."', '".@$mvto_produto["item"]."', '".@$mvto_produto["tipo"]."', '".@$mvto_produto["ent_sai"]."', '".@$mvto_produto["mov_saldo"]."', '".@$mvto_produto["produto"]."', '".@$mvto_produto["unidade"]."', '".@$mvto_produto["quantidade"]."', '".@$mvto_produto["qtde_separada"]."', '".@$mvto_produto["qtde_expedida"]."', '".@$mvto_produto["valor_unitario"]."', '".@$mvto_produto["valor_desconto"]."', '".@$mvto_produto["total"]."', '".@$mvto_produto["custo_adic"]."', '".@$mvto_produto["outras_desp"]."', '".@$mvto_produto["frete"]."', '".@$mvto_produto["data_mvto"]."', '".@$mvto_produto["hora_mvto"]."', '".@$mvto_produto["custo_medio"]."', '".@$mvto_produto["ult_custo"]."', '".@$mvto_produto["lbv"]."', '".@$mvto_produto["cancelada"]."', '".@$mvto_produto["status"]."', '1');";
                                    $mysqli->query($qry_mvto_produto);
                                }
                            }

                            $qry_new_nf = 'SELECT * FROM cad_nf WHERE CODIGO = "'.$nf_id.'"';
                            $qry_result_new_nf = $mysqli->query($qry_new_nf);
                            $row = mysqli_fetch_assoc($qry_result_new_nf);

                            $qry_mvto_produto = 'SELECT p.* FROM mvto_produtos p WHERE p.CHAVE_MVTO = "'.$row['CODIGO'].'"';
                            $qry_result_mvto_produto = $mysqli->query($qry_mvto_produto);
                            $qry_rows_mvto_produto = mysqli_num_rows($qry_result_mvto_produto);
                            if ($qry_rows_mvto_produto > 0):
                                while($row_mvto_produto = mysqli_fetch_assoc($qry_result_mvto_produto)):
                                    $row["mvto_produtos"][] = $row_mvto_produto;
                                endwhile;
                            endif;

                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 10;
                            $retorno['data'] = $row;
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Houve um problema ao gravar a nf.";
                        }
                    } else {
                        $qry = "UPDATE cad_comp SET sincronizado = '1'";
                        if(!empty($_POST['sku'])):
                            $qry .= ", sku = '".$_POST["sku"]."'";
                        endif;
                        if(!empty($_POST['status'])):
                            $qry .= ", status = '".$_POST["status"]."'";
                        endif;
                        if(!empty($_POST['situacao'])):
                            $qry .= ", situacao = '".$_POST["situacao"]."'";
                        endif;
                        if(!empty($_POST['data_baixa'])):
                            $qry .= ", data_baixa = '".$_POST["data_baixa"]."'";
                        endif;
                        if(!empty($_POST['data_receb'])):
                            $qry .= ", data_receb = '".$_POST["data_receb"]."'";
                        endif;
                        if(!empty($_POST['data_pedido'])):
                            $qry .= ", data_pedido = '".$_POST["data_pedido"]."'";
                        endif;
                        if(!empty($_POST['hora_receb'])):
                            $qry .= ", hora_receb = '".$_POST["hora_receb"]."'";
                        endif;
                        $qry .= " WHERE";
                        foreach ($_POST['codigos'] as $value) {
                            if($i > 0)
                                $qry .= " OR";
                            $qry .= " id = '".$value."'";
                            $i++;
                        }
                        if($mysqli->query($qry)) {
                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 10;
                            $retorno['data'] = $row;
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = "Houve um problema ao atualizar a nf.";
                        }
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = "Recurso 'Financeiro - SAX para MaestriaTech' não ativo.";
                }
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }
    /* FIM MÉTODOS UTILIZADOS NO SAX */

    protected function getUnidade() {
        if($this->method == 'POST'):
            if(empty($_POST['empresa_id'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a Empresa!';
                exit(json_encode($retorno));
            endif;

            $qry = 'SELECT u.*, c.descricao as carac_desc FROM unidades u LEFT JOIN unid_carac c ON c.id = u.caracteristica WHERE u.empresa_id = "' . $_POST['empresa_id'] . '" ';

            $mysqli = $this->conexao->getDatabase();
            $qry_result = $mysqli->query($qry);
            $qry_rows = mysqli_num_rows($qry_result);

            $unidades = array();
            if ($qry_rows > 0):
                while($row = mysqli_fetch_assoc($qry_result)):
                    unset($row["token"]);
                    $unidades[] = $row;
                endwhile;
            endif;
            
            $retorno['erro'] = FALSE;
            $retorno['codigo'] = 10;
            $retorno['data'] = $unidades;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }
    
    /* MÉTODOS UTILIZADOS NO SAX PARA WEBSERVICE PRECODE */
    protected function getPedidoPrecode() {
        if($this->method == 'POST'):
            $post = json_decode(file_get_contents('php://input'), true);
            
            if(empty($post['conteudo'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o conteúdo!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['autorizacao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a autorização!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['password'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o password!';
                exit(json_encode($retorno));
            endif;
        
            $conteudo = $post['conteudo'];
            $url = 'http://www.attually.com.br/api/v1/pedido/pedido';

            $ch = curl_init($url);
            $cabecalho = array(
                'Content-Type:application/json',
                'Authorization: '.$post["autorizacao"]
            );

            curl_setopt($ch, CURLOPT_USERPWD, $post["password"]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $cabecalho);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($conteudo));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
            $resp = curl_exec($ch);
            $resposta = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $retorno['erro'] = FALSE;
            $retorno['data'] = json_decode($resp);
        else:
            $retorno['erro'] = TRUE;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }
    
    protected function getPedidoStatusPrecode() {
        if($this->method == 'POST'):
            $post = json_decode(file_get_contents('php://input'), true);
            
            if(empty($post['conteudo'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o conteúdo!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['autorizacao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a autorização!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['password'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o password!';
                exit(json_encode($retorno));
            endif;
        
            $conteudo = $post['conteudo'];
            $url = 'http://www.attually.com.br/api/v1/pedido/pedidoStatus';

            $ch = curl_init($url);
            $cabecalho = array(
                'Content-Type:application/json',
                'Authorization: '.$post["autorizacao"]
            );

            curl_setopt($ch, CURLOPT_USERPWD, $post["password"]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $cabecalho);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($conteudo));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
            $resp = curl_exec($ch);
            $resposta = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $retorno['erro'] = FALSE;
            $retorno['data'] = json_decode($resp);
        else:
            $retorno['erro'] = TRUE;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }
    
    protected function getPedidoNovoPrecode() {
        if($this->method == 'POST'):
            $post = json_decode(file_get_contents('php://input'), true);
            
            if(empty($post['autorizacao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a autorização!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['password'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o password!';
                exit(json_encode($retorno));
            endif;
        
            $url = 'http://www.attually.com.br/api/v1/erp/pedido';

            $ch = curl_init($url);
            $cabecalho = array(
                'Content-Type:application/json',
                'Authorization: '.$post["autorizacao"]
            );

            curl_setopt($ch, CURLOPT_USERPWD, $post["password"]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $cabecalho);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "NEW");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
            $resp = curl_exec($ch);
            $resposta = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            //$retorno['erro'] = FALSE;
            //$retorno['data'] = json_decode($resp);
            return json_decode($resp);
        else:
            $retorno['erro'] = TRUE;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getPedidoAprovadoPrecode() {
        if($this->method == 'POST'):
            $post = json_decode(file_get_contents('php://input'), true);
            
            if(empty($post['autorizacao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a autorização!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['password'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o password!';
                exit(json_encode($retorno));
            endif;
        
            $url = 'http://www.attually.com.br/api/v1/erp/pedido';

            $ch = curl_init($url);
            $cabecalho = array(
                'Content-Type:application/json',
                'Authorization: '.$post["autorizacao"]
            );

            curl_setopt($ch, CURLOPT_USERPWD, $post["password"]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $cabecalho);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
            $resp = curl_exec($ch);
            $resposta = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            //$retorno['erro'] = FALSE;
            //$retorno['data'] = json_decode($resp);
            return json_decode($resp);
        else:
            $retorno['erro'] = TRUE;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getProdutoPrecode() {
        if($this->method == 'POST'):
            $post = json_decode(file_get_contents('php://input'), true);
            
            /*if(empty($post['conteudo'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o conteúdo!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['autorizacao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a autorização!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['password'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o password!';
                exit(json_encode($retorno));
            endif;*/
        
            $conteudo = $post['conteudo'];
            $url = 'http://www.attually.com.br/api/v1/produtoLoja/ProdutoSku';

            $ch = curl_init($url);
            $cabecalho = array(
                'Content-Type:application/json',
                'Authorization: '.$post["autorizacao"]
            );

            curl_setopt($ch, CURLOPT_USERPWD, $post["password"]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $cabecalho);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($conteudo));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
            $resp = curl_exec($ch);
            $resposta = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            // $retorno['erro'] = FALSE;
            // $retorno['data'] = json_decode($resp);
            $retorno = json_decode($resp);
        else:
            $retorno['erro'] = TRUE;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getSaldoProdutoPrecode() {
        if($this->method == 'POST'):
            $post = json_decode(file_get_contents('php://input'), true);
            
            /*if(empty($post['conteudo'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o conteúdo!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['autorizacao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a autorização!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['password'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o password!';
                exit(json_encode($retorno));
            endif;*/
        
            $conteudo = $post['conteudo'];
            $url = 'http://www.attually.com.br/api/v1/produtoLoja/saldoSku';

            $ch = curl_init($url);
            $cabecalho = array(
                'Content-Type:application/json',
                'Authorization: '.$post["autorizacao"]
            );

            curl_setopt($ch, CURLOPT_USERPWD, $post["password"]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $cabecalho);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($conteudo));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
            $resp = curl_exec($ch);
            $resposta = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            // $retorno['erro'] = FALSE;
            // $retorno['data'] = json_decode($resp);
            $retorno = json_decode($resp);
        else:
            $retorno['erro'] = TRUE;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getPrecoProdutoPrecode() {
        if($this->method == 'POST'):
            $post = json_decode(file_get_contents('php://input'), true);
            
            /*if(empty($post['conteudo'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o conteúdo!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['autorizacao'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a autorização!';
                exit(json_encode($retorno));
            endif;
            if(empty($post['password'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha o password!';
                exit(json_encode($retorno));
            endif;*/
        
            $conteudo = $post['conteudo'];
            $url = 'http://www.attually.com.br/api/v1/produtoLoja/precoSku';

            $ch = curl_init($url);
            $cabecalho = array(
                'Content-Type:application/json',
                'Authorization: '.$post["autorizacao"]
            );

            curl_setopt($ch, CURLOPT_USERPWD, $post["password"]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $cabecalho);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($conteudo));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
            $resp = curl_exec($ch);
            $resposta = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            // $retorno['erro'] = FALSE;
            // $retorno['data'] = json_decode($resp);
            $retorno = json_decode($resp);
        else:
            $retorno['erro'] = TRUE;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }
    /* FIM MÉTODOS UTILIZADOS NO SAX PARA WEBSERVICE PRECODE */

    private function validarEmail($email){
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)):
            return TRUE;
        else:
            return FALSE;
        endif;
    }

    private function gravar_user_voxy($user_id, $dados){
        require_once ('Voxy.php');
        $voxy = new Voxy();

        $retorno_registro_user_ead = $voxy->make_request("/partners/users/{$user_id}", "GET", array());
        if($retorno_registro_user_ead["status_code"] == 200) {
            $retorno_registro_ead = $voxy->make_request("/partners/users/{$user_id}", "PUT", $dados);
            if($retorno_registro_ead["status_code"] == 200) {
                return true;
            } else {
                return false;
            }
        } else {
            $retorno_registro_ead = $voxy->make_request("/partners/users/{$user_id}", "POST", $dados);
            if($retorno_registro_ead["status_code"] == 201) {
                return true;
            } else {
                return false;
            }
        }
    }

    private function formatCelApi($cel) {
        $cel = str_replace("(", "", $cel);
        $cel = str_replace(")", "", $cel);
        $cel = str_replace(" ", "", $cel);
        $cel = str_replace("-", "", $cel);
        return "55".$cel;
    }

    private function createSingleJsonSms($sms) {
        $obj_sms = array(
            "from" => $sms['from'],
            "to" => $sms['to'],
            "schedule" => $sms['schedule'],
            "msg" => $sms['msg'],
            "callbackOption" => "ALL",
            "id" => $sms['id'],
            "aggregateId" => ''
        );

        $send_single = array(
            "sendSmsRequest" => $obj_sms
        );
        return json_encode($send_single);
    }

    private function createMultiJsonSms($sms) {
        $send_multi = array(
            "sendSmsMultiRequest" => array(
                "aggregateId" => '',
                "sendSmsRequestList" => $sms
            )
        );

        return json_encode($send_multi);
    }

    public function zenviaCallSingle($endpoint, $json_sms) {
        $user = 'xkey.api';
        $pass = 'XwtXSLC1DH';
        $basic = base64_encode($user.':'.$pass);

        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_URL, "https://private-anon-02c2557b2-zenviasms.apiary-mock.com/services/send-sms");
        curl_setopt($ch, CURLOPT_URL, "https://api-rest.zenvia360.com.br/services/".$endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_sms);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "Authorization: Basic {$basic}",
          "Accept: application/json"
        ));

        $response = curl_exec($ch);
        
        if (curl_error($ch)):
            $retorno['erro'] = true;
            $retorno['mensagem'] = "Ocorreu um erro ao tentar enviar a mensagem, Mensagem:'".curl_error($ch)."', por favor entre em contato com o administrador do sistema!";
        else:
            $retorno['erro'] = false;
            $retorno['mensagem'] = "Mensagem enviada com sucesso!";
            $retorno['data'] = $response;
        endif;

        curl_close($ch);
        return $retorno;
    }

    private function getCodesDescription($code, $array){
        if($array == 'status'):
            foreach(self::$status as $key => $value):
                if($key == $code):
                    $description = $value;
                endif;                
            endforeach;
        elseif($array == 'detail'):
            foreach(self::$detail as $key => $value):
                if($key == $code):
                    $description = $value;
                endif;
            endforeach;
        endif;
        return $description;
    }

    private function get_extension($mime){
        $extensions = array(
            "text/plain" => ".txt",
            "application/msword" => ".doc",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => ".docx",
            "application/x-rar-compressed" => ".rar",
            "application/zip" => ".zip",
            "application/pdf" => ".pdf"
        );
        return $extensions[$mime];
    }

    private static $status = array(
        0 => 'Ok',
        1 => 'Agendada',
        2 => 'Enviada',
        3 => 'Entregue',
        4 => 'Não recebida',
        5 => 'Bloqueada - Sem corbetura',
        6 => 'Bloqueada - Lista negra',
        7 => 'Bloqueada - Número inválido',
        8 => 'Bloqueada - Conteúdo não permitido',
        8 => 'Bloqueada - Mensagem expirada',
        9 => 'Bloqueada',
        10 => 'Erro'
    );

    private static $detail = array(
        0 => 'Mensagem enviada',
        2 => 'Mensagem cancelada com sucesso',
        10 => 'Empty message content',
        11 => 'Message body invalid',
        12 => 'Message content overflow',
        13 => 'Incorrect or incomplete ‘to’ mobile number',
        14 => 'Empty ‘to’ mobile number',
        15 => 'Scheduling date invalid or incorrect',
        16 => 'ID overflow',
        17 => 'Parameter ‘url’ is invalid or incorrect',
        18 => 'Field ‘from’ invalid',
        21 => '‘id’ fieldismandatory',
        80 => 'Message with same ID already sent',
        100 => 'Mensagem em fila',
        110 => 'Message sent to operator',
        111 => 'Message confirmation unavailable',
        120 => 'Mensagem recebida por celular',
        130 => 'Mensagem bloqueada',
        131 => 'Message blocked by predictive cleansing',
        132 => 'Mensagem já cancelada',
        133 => 'Message content in analysis',
        134 => 'Mensagem bloqueada por conteúdo indevido',
        135 => 'Aggregate is Invalid or Inactive',
        136 => 'Message expired',
        140 => 'Mobile number not covered',
        141 => 'International sending not allowed',
        145 => 'Número de celular inativo',
        150 => 'Mensagem expirada na operadora',
        160 => 'Erro na rede da operadora',
        161 => 'Mensagem rejeitada pela operadora',
        162 => 'Mensagem cancelada ou bloqueada pela operadora',
        170 => 'Bad message',
        171 => 'Bad number',
        172 => 'Missing parameter',
        180 => 'Message ID notfound',
        190 => 'Unknown error',
        200 => 'Mensagens enviadas',
        210 => 'Messages scheduled but Account Limit Reached',
        240 => 'File empty or not sent',
        241 => 'File too large',
        242 => 'File readerror',
        300 => 'Received messages found',
        301 => 'No received messages found',
        400 => 'Entity saved',
        900 => 'Authentication error',
        901 => 'Account type not support this operation.',
        990 => 'Account Limit Reached – Please contact support',
        998 => 'Wrong operation requested',
        999 => 'Erro desconhecido'
    );

    private function get_email_config($empresa_id = null)
    {
        $qry = 'SELECT * FROM config_email WHERE empresa_id = "'.$empresa_id.'"';
        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry);
        $dados = mysqli_fetch_assoc($qry_result);
        return $dados;
    }

    private function get_email_padrao($tipo_email, $empresa_id)
    {
        $qry = 'SELECT * FROM email_padrao WHERE empresa_id = "'.$empresa_id.'" AND tipo_email = "'.$tipo_email.'"';
        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry);
        $dados = mysqli_fetch_assoc($qry_result);
        return $dados;
    }
	
}