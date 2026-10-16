<?php
//ini_set('display_errors', 1);
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


require_once ('Util.php');
require_once ('Bcrypt.php');
require_once ('Ion_auth_model.php');
require_once ('conexao.php');
require_once 'abstractServiceClass.php';
require_once 'Mobile_Detect.php';
require_once 'UploadImgBanner.php';


class ServiceModel extends Service {

    private $conexao = null;
    private $prefix = "";

    public function __construct($request, $origin) {
        parent::__construct($request);
        $this->conexao = new Conexao();
    }

    private function generateToken() {
        return md5(uniqid(rand(), true));
    }

    /*MÉTODOS PRINCIPAIS*/
    protected function login() {
        if($this->method == 'POST'):

            /*Se email, senha e id do aparelho não foram enviados.*/
            if(empty($_POST['device_id'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Id do aparelho não encontrado, não é possível realizar o login!';
                exit(json_encode($retorno));
            endif;

            if (empty($_POST['email'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1; 
                $retorno['mensagem'] = 'Por favor preencha o email!';
                exit(json_encode($retorno));
            endif;

            if (empty($_POST['senha'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Por favor preencha a senha!';
                exit(json_encode($retorno));
            endif;

            if (empty($_POST['empresa_id'])):
                $_POST['empresa_id'] = 2;
                /*$retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Erro de empresa não selecionada, contate o administrador!';
                exit(json_encode($retorno));*/
            endif;

            $email = $_POST['email'];
            $senha = md5($_POST['senha']);
            $device_id = $_POST['device_id'];
            $empresa_id = $_POST['empresa_id'];

            /*Se email não é válido*/
            if($this->validarEmail($email)):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 2;
                $retorno['mensagem'] = 'Endereço de email não é válido!';
                exit(json_encode($retorno));
            endif;

            $qry_user = 'SELECT id, email, active, password FROM users '
                    . 'WHERE lower(email) = "' . $email . '" ';
                    //. 'AND password = "'.$senha.'"';


            $mysqli = $this->conexao->getDatabase();
            $qry_result = $mysqli->query($qry_user);
            $qry_rows = mysqli_num_rows($qry_result);

            if ($qry_rows > 0):
                /*Carrega dados do usuário*/
                $dados = mysqli_fetch_assoc($qry_result);
                $bcrypt = new Bcrypt();
                if ($bcrypt->verify($_POST['senha'], $dados['password']))
                {
                    //Verificar se usuário possui acesso
                    if($dados['active'] == '0'):
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 30;
                        $retorno['mensagem'] = 'Usuário não possui acesso ao aplicativo!';
                    else:
                        /*Verificar se aparelho já está em uso, se já estiver será atualizado para o novo usuário*/
                        if($this->verificarAparelhoEmUso($dados['id'], $device_id)):
                            $this->alterarAparelhoUsuario($dados['id'], $device_id);
                        endif;

                        /*Atualiza aparelhos caso necessário*/
                        return $this->atualizarAparelho($dados['id'], $device_id, $_POST['empresa_id']);
                    endif;
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 5;
                    $retorno['mensagem'] = 'Senha não está correta, tente novamente!';
                }                
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 5;
                $retorno['mensagem'] = 'Usuário não está correto, tente novamente!';
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function logoff(){
        if($this->method == 'POST'):

            /*Se id do aparelho não foram enviados.*/
            if(empty($_POST['device_id'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Id do aparelho não informado!';
                exit(json_encode($retorno));
            endif;

            /*Se token não estiver no post*/
            if (empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Token não informado!';
                exit(json_encode($retorno));
            endif;

            $device_id = $_POST['device_id'];
            $token = $_POST['token'];

            if($this->desvincularAparelho($device_id, $token)):
                $retorno['erro'] = FALSE;
                $retorno['codigo'] = 15;
                $retorno['mensagem'] = 'Logoff executado com sucesso!';
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 16;
                $retorno['mensagem'] = 'Logoff falhou!';
            endif;            
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        return $retorno;
    }

    protected function authenticate(){
        if($this->method == 'POST'):

            /*Se id do aparelho não foram enviados.*/
            if(empty($_POST['device_id'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Id do aparelho não encontrado, não é possível realizar o login!';
                exit(json_encode($retorno));
            endif;

            /*Se token não estiver no post*/
            if (empty($_POST['token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'O token não foi encontrado!';
                exit(json_encode($retorno));
            endif;

            $token = $_POST['token'];
            $device_id = $_POST['device_id'];

            /*$qry_token = 'SELECT ad.id_aluno, u.nome, u.email, ad.id as id_device, ad.device_id, ad.device_token, ad.token, ad.token_criado_em, ad.token_expira_em, '
                        . 'l.id as id_livro, l.titulo as titulo_livro '
                        . 'FROM users u '
                        . 'INNER JOIN aluno_device ad '
                        . 'ON ad.id_aluno = u.id '
                        . 'INNER JOIN turma_aluno ta '
                        . 'ON ta.aluno = u.id '
                        . 'INNER JOIN turma_old t '
                        . 'ON ta.turma = t.id '
                        . 'INNER JOIN livros l '
                        . 'ON l.id = t.livro '
                        . 'WHERE ad.device_id = "'.$device_id.'"'
                        . 'AND ad.token = "'.$token.'"';*/

            $qry_token = 'SELECT ad.id_aluno, u.first_name as nome, u.active, u.email, ad.id as id_device, ad.device_id, ad.device_token, ad.token, ad.token_criado_em, ad.token_expira_em '
                        . 'FROM users u '
                        . 'INNER JOIN aluno_device ad '
                        . 'ON ad.id_aluno = u.id '
                        . 'WHERE ad.device_id = "'.$device_id.'"'
                        . 'AND ad.token = "'.$token.'"';

            $mysqli = $this->conexao->getDatabase();
            $qry_result = $mysqli->query($qry_token);
            $qry_rows = mysqli_num_rows($qry_result);

            if($qry_rows > 0):
                $dados = mysqli_fetch_assoc($qry_result);
                $curdate = date('Y-m-d H:i:s');

                //Verificar se usuário possui acesso
                if($dados['actived'] == 'n'):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 30;
                    $retorno['mensagem'] = 'Usuário não possui acesso ao aplicativo!';
                else:
                    if(strtotime($dados['token_expira_em']) < strtotime($curdate)):
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 4;
                        $retorno['mensagem'] = 'Token expirou, por favor execute o login!';
                    else:
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 12;
                        $retorno['mensagem'] = 'Autenticado com sucesso!';
                        $retorno['usuario'] = $dados;
                    endif;
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 7;
                $retorno['mensagem'] = 'Autenticação falhou, aparelho não encontrado!';
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido|';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function alterarSenha(){
        if($this->method == 'POST'):

            if (empty($_POST['senha_atual'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Senha atual não foi enviada!';
                exit(json_encode($retorno));
            endif;

            if (empty($_POST['nova_senha'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Nova senha não foi enviada!';
                exit(json_encode($retorno));
            endif;

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                /*Checar senha atual*/
                $check_senha = $this->checarSenhaAtual($auth_decode['usuario']['id_aluno'], md5($_POST['senha_atual']));
                if($check_senha):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 17;
                    $retorno['mensagem'] = 'Senha atual não confere!';
                else:
                    $senha = md5($_POST['nova_senha']);
                    $sql_update = "UPDATE users SET "
                        . " password = '{$senha}'"
                        . " WHERE id = ".$auth_decode['usuario']['id_aluno'];

                    $mysqli = $this->conexao->getDatabase();
                    if($mysqli->query($sql_update)):
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 18;
                        $retorno['mensagem'] = 'Senha alterada com sucesso!';
                    endif;
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function recuperarSenha(){
        include(BASEPATH . '/application/third_party/phpmailer/PHPMailerAutoload.php');
        if($this->method == 'POST'):
            if (empty($_POST['email'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Email não foi informado!';
                exit(json_encode($retorno));
            endif;

            /*Se email não é válido*/
            if($this->validarEmail($_POST['email'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 2;
                $retorno['mensagem'] = 'Endereço de email não é válido!';
                exit(json_encode($retorno));
            endif;

            $sql_qry = "SELECT * FROM users WHERE lower(email) = '{$_POST['email']}' AND active = '1' LIMIT 1";

            $mysqli = $this->conexao->getDatabase();
            $qry_res = $mysqli->query($sql_qry);
            $qry_rows = mysqli_num_rows($qry_res);

            if($qry_rows > 0):
                $ion_auth_model = new Ion_auth_model();
                if($ion_auth_model->forgotten_password($_POST['email'])) :
                    $sql_qry = "SELECT * FROM users WHERE lower(email) = '{$_POST['email']}' AND active = '1' LIMIT 1";

                    $qry_res = $mysqli->query($sql_qry);
                    $row = mysqli_fetch_assoc($qry_res);

                    $mensagem = '<h1>Redefinir senha para '.$_POST['email'].'</h1><p>Por favor, clique neste link para <a href="http://'.BASEURL.'/auth/reset_password/'.$row['forgotten_password_code'].'">Redefinir sua senha</a>.</p>';

                    $mail = new PHPMailer();
                    $mail->SetLanguage("br");
                    $mail->CharSet = "utf-8";
                    $mail->IsSMTP();
                    $mail->SMTPDebug = false;
                    $mail->SMTPAuth = true;
                    //$mail->SMTPSecure = 'ssl';
                    $mail->Host = 'smtp.maestriatech.com.br';
                    $mail->Port = 587;
                    $mail->Username = 'contato=maestriatech.com.br';
                    $mail->Password = 'Alf@7410147';
                    $mail->SetFrom('contato@maestriatech.com.br', 'Saxweb');
                    $mail->addAddress($row['email'],$row['first_name']." ".$row['last_name']);
                    $mail->isHTML(true);
                    $mail->Subject = ("MaestriaTech - Recuperação de Senha");
                    $mail->msgHTML($mensagem);

                    if ($mail->Send()):
                        $retorno['erro'] = false;
                        $retorno['codigo'] = 20;
                        $retorno['mensagem'] = 'E-mail para redefinir senha enviado, verique sua caixa de mensagens!';
                    else:
                        $retorno['erro'] = true;
                        $retorno['codigo'] = 19;
                        $retorno['mensagem'] = 'Erro ao tentar enviar email com acesso para redefinir senha, tente novamente!';
                    endif;
                endif;
            else:
                $retorno['erro'] = true;
                $retorno['codigo'] = 9;
                $retorno['mensagem'] = 'Usuário não encontrado!';
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }
    /*FIM - MÉTODOS PRINCIPAIS*/

    /*MÉTODOS GET*/
    protected function getUsuario(){
        if($this->method == 'POST'):
            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                /*$qry_token = 'SELECT u.id, u.nome, u.email, u.apelido, u.username, u.telefone, u.celular, u.unidade_id, u.actived, l.id as id_livro, l.titulo as titulo_livro, al.qrcode '
                            . 'FROM users u '
                            . 'LEFT JOIN aluno_livro al '
                            . 'ON al.aluno = u.id '
                            . 'INNER JOIN livros l '
                            . 'ON al.livro = l.id '
                            . 'WHERE u.actived = "s" '
                            . 'AND u.id = '.$auth_decode['usuario']['id_aluno'];*/

                $qry_token = 'SELECT u.id, u.nome, u.email, u.apelido, u.username, u.telefone, u.celular, u.unidade_id, u.actived '
                            . 'FROM users u '
                            . 'WHERE u.actived = "s" '
                            . 'AND u.id = '.$auth_decode['usuario']['id_aluno'];

                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_token);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Informações recuperadas com sucesso!';
                    while($row = mysqli_fetch_assoc($qry_result)):
                        $retorno['usuario'] = $row;
                    endwhile;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Registro não encontrado no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getEventos(){
        if($this->method == 'POST'):
            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                $qry_eve = 'SELECT id, titulo, texto, data, actived, created_at, unidades '
                    . 'FROM app_eventos '
                    . 'WHERE data >= NOW() AND actived = "S"';

                /*Se campo next_id for postado é incluído na query*/
                /*if(!empty($_POST['next_id'])):
                    $qry_eve .= ' AND id NOT IN('.base64_decode($_POST['next_id']).') ';
                endif;*/

                /*Se campo limit for postado é incluído na query*/
                /*if(empty($_POST['limit'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Limite não foi encontrado!';
                    exit(json_encode($retorno));
                endif;
                $limit = (int)$_POST['limit'];
                $qry_eve .= 'ORDER BY data ASC LIMIT '.$limit;*/
                $qry_eve .= 'ORDER BY data ASC';
            
                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_eve);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Lista de eventos!';

                    $qry_user = 'SELECT u.id '
                        . 'FROM users u '
                        . 'INNER JOIN aluno_device ad '
                        . 'ON ad.id_aluno = u.id '
                        . 'WHERE ad.device_id = "'.$_POST['device_id'].'"'
                        . 'AND ad.token = "'.$_POST['token'].'"';

                    $qry_result_user = $mysqli->query($qry_user);
                    $qry_rows_user = mysqli_num_rows($qry_result_user);

                    $user = mysqli_fetch_assoc($qry_result_user);

                    $qry_unidades = 'SELECT unidade_id '
                        . 'FROM user_unidade '
                        . 'WHERE user_id = "'.$user['id'].'"';

                    $qry_result_unidades = $mysqli->query($qry_unidades);
                    $qry_rows_unidades = mysqli_num_rows($qry_result_unidades);
                    
                    if($qry_rows_unidades > 0):
                        $unidade_user = array();
                        while($row_unidade_user = mysqli_fetch_assoc($qry_result_unidades)):
                            $unidade_user[] = $row_unidade_user['unidade_id'];
                        endwhile;
                        
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $unidades = unserialize($row['unidades']);
                            if(count($unidades) > 0) {
                                $include_event = false;
                                foreach ($unidades as $unid) {
                                    if(in_array($unid, $unidade_user))
                                        $include_event = true;
                                }
                                if($include_event) {
                                    /*Lista de eventos em acordo com o limit*/
                                    $retorno['eventos'][] = array(
                                        'id' => $row['id'],
                                        'titulo' => $row['titulo'],
                                        'texto' => $row['texto'],
                                        'data_evento' => $row['data'],
                                        'ativo' => $row['actived'],
                                        'data_cadastro' => $row['created_at'],
                                    );
                                }
                            }
                        endwhile;
                        
                        foreach($retorno['eventos'] as $key):
                            $ids[] = $key['id'];                        
                        endforeach;
                        $ids_posteriores = implode(',', $ids);
                        $retorno['search_data'] = array(
                            'next_id' => NULL,
                            'count' => $qry_rows
                        );

                        /*if(!empty($_POST['next_id'])):
                            $ids_anter_ret = explode(',', base64_decode($_POST['next_id']));
                            $ids_anteriores = implode(',', $ids_anter_ret);
                            $ids = $ids_anteriores.','.$ids_posteriores;
                        else:
                            $ids = $ids_posteriores;
                        endif;*/

                        /*Contador do limit da primeira query, recupera o id*/
                        //if($qry_rows == $limit):
                            /*Array de retorno para paginação*/
                            /*$retorno['search_data'] = array(
                                                    'next_id' => base64_encode($ids),
                                                    'count' => $qry_rows
                                                );*/
                        //elseif($qry_rows < $limit):
                            /*Array de retorno para paginação*/
                            /*$retorno['search_data'] = array(
                                                    'next_id' => NULL,
                                                    'count' => $qry_rows
                                                );
                        endif;*/     
                    endif;                              
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getPodcasts(){
        if($this->method == 'POST'):
            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                $mysqli = $this->conexao->getDatabase();
                $qry_empresa = 'SELECT eu.empresa_id '
                    . 'FROM empresas_users eu '
                    . 'INNER JOIN aluno_device ad '
                    . 'ON ad.id_aluno = eu.user_id '
                    . 'WHERE ad.device_id = "'.$_POST['device_id'].'"'
                    . 'AND ad.token = "'.$_POST['token'].'"';

                $qry_result_empresa = $mysqli->query($qry_empresa);
                $qry_rows_empresa = mysqli_num_rows($qry_result_empresa);

                $empresas_array = array();
                if($qry_rows_empresa > 0) {
                    while($row_empresa = mysqli_fetch_assoc($qry_result_empresa)) {
                        $empresas_array[] = $row_empresa["empresa_id"];
                    }
                } else {
                    $empresas_array = array(0);
                }

                $qry_pod = 'SELECT p.*, l.empresa '
                        . 'FROM podcasts p '
                        . 'INNER JOIN livros l ON l.id = p.livro '
                        . 'WHERE p.tipo = "P" '
                        . 'AND p.publicacao IN("A","T") AND p.ativo = "S" '
                        . 'AND l.empresa IN('.implode(",", $empresas_array).') ';

                /*Se campo next_id for postado é incluído na query*/
                if(!empty($_POST['next_id'])):
                    $qry_pod .= 'AND p.id NOT IN('.base64_decode($_POST['next_id']).') ';
                endif;

                /*Se houver data de atualização*/
                if(!empty($_POST['data'])):
                    $qry_pod .= "AND p.data_atualizacao >= '".$_POST['data']."' ";
                endif;

                /*Se campo limit for postado é incluído na query*/
                $limit = "";
                if(!empty($_POST['limit'])):
                    $limit = (int)$_POST['limit'];
                endif;

                $qry_pod .= 'ORDER BY p.data_publicacao DESC ';
                if($limit != "") {
                    $qry_pod .= 'LIMIT '.$limit;
                }
                
                $qry_result = $mysqli->query($qry_pod);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Lista de podcasts!';

                    while($row = mysqli_fetch_assoc($qry_result)):

                        /*Lista de podcasts em acordo com o limit*/
                        $retorno['podcasts'][] = array(
                            'id' => $row['id'],
                            'livro' => $row['livro'],
                            'tipo' => $row['tipo'],
                            'titulo' => $row['titulo'],
                            'texto' => $row['texto'],
                            'url_midida' => 'http://'.BASEURL.'/'.$row['caminho_midia'],
                            'titulo_midia' => $row['titulo_midia'],
                            'url_img' => 'http://'.BASEURL.'/'.$row['caminho_img'],
                            'titulo_imagem' => $row['titulo_imagem'],
                            'data_publicacao' => $row['data_publicacao'],
                            'data_atualizacao' => $row['data_atualizacao'],
                            'publicacao' => $row['publicacao'],
                            'ativo' => $row['ativo']
                        );
                    endwhile;

                    foreach($retorno['podcasts'] as $key):
                        $ids[] = $key['id'];                        
                    endforeach;
                    $ids_posteriores = implode(',', $ids);

                    if(!empty($_POST['next_id'])):
                        $ids_anter_ret = explode(',', base64_decode($_POST['next_id']));
                        $ids_anteriores = implode(',', $ids_anter_ret);
                        $ids = $ids_anteriores.','.$ids_posteriores;
                    else:
                        $ids = $ids_posteriores;
                    endif;

                    /*Contador do limit da primeira query, recupera o id*/
                    if($limit != "") {
                        if($qry_rows == $limit):
                            /*Array de retorno para paginação*/
                            $retorno['search_data'] = array(
                                                    'next_id' => base64_encode($ids),
                                                    'count' => $qry_rows
                                                );
                        elseif($qry_rows < $limit):
                            /*Array de retorno para paginação*/
                            $retorno['search_data'] = array(
                                                    'next_id' => NULL,
                                                    'count' => $qry_rows
                                                );
                        endif;
                    } else {
                        $retorno['search_data'] = array(
                            'next_id' => NULL,
                            'count' => $qry_rows
                        );
                    }
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getLivros(){
        if($this->method == 'POST'):
            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                if(empty($_POST['empresa'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Empresa não foi encontrado!';
                    exit(json_encode($retorno));
                endif;
                    
                $token = $_POST['token'];
                $device_id = $_POST['device_id'];

                /*$qry_livro = 'SELECT l.* FROM livros l 
                    WHERE l.empresa = "'.$_POST['empresa'].'" 
                    ORDER BY l.id ASC';*/

                $qry_livro = 'SELECT l.* FROM livros l 
                    INNER JOIN turma t ON 
                        t.LIG_ID_PACOTE = l.id  
                    INNER JOIN turma_aluno ta ON
                       ta.turma = t.ID 
                    INNER JOIN aluno_device ad ON 
                       ta.aluno = ad.id_aluno
                    WHERE ad.device_id = "'.$device_id.'" 
                        AND ad.token = "'.$token.'"
                        AND l.empresa = "'.$_POST['empresa'].'" 
                        AND l.ativo = "S"
                    GROUP BY l.id
                    ORDER BY l.id ASC';
                    
                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_livro);
                $qry_rows = mysqli_num_rows($qry_result);
                
                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Lista de livros!';

                    while($row = mysqli_fetch_assoc($qry_result)):
                        /*$qry_exec_percent = 'SELECT ((COUNT(*) * 100) / (SELECT COUNT(*) FROM pergunta_exercicio pe INNER JOIN exercicios e ON pe.exercicio = e.id INNER JOIN unidade_livro ul ON ul.id = e.unidade WHERE e.livro = "'.$row['id'].'" AND ul.tipo = "O")) AS percentual '
                                    . 'FROM resposta_aluno ra '
                                    . 'INNER JOIN pergunta_exercicio pe '
                                    . 'ON ra.pergunta = pe.id '
                                    . 'INNER JOIN exercicios e '
                                    . 'ON pe.exercicio = e.id '
                                    . 'INNER JOIN unidade_livro ul ON ul.id = e.unidade '
                                    . 'INNER JOIN aluno_device ad '
                                    . 'ON ad.id_aluno = ra.aluno '
                                    . 'WHERE ad.device_id = "'.$device_id.'" '
                                    . 'AND ad.token = "'.$token.'" '
                                    . 'AND e.livro = "'.$row['id'].'" AND ul.tipo= "O"';
                             
                        $qry_result_percent = $mysqli->query($qry_exec_percent);
                        $qry_rows_percent = mysqli_num_rows($qry_result_percent);

                        if($qry_rows_percent == 1):
                            $row_percent = mysqli_fetch_assoc($qry_result_percent);
                            $row['percentual'] = round($row_percent['percentual']);
                        else:
                            $row['percentual'] = "0";
                        endif;*/

                        $qry_exec_percent = 'SELECT al.percentual '
                                    . 'FROM aluno_livro al '
                                    . 'INNER JOIN aluno_device ad ON ad.id_aluno = al.aluno '
                                    . 'WHERE ad.device_id = "'.$device_id.'" '
                                    . 'AND ad.token = "'.$token.'" '
                                    . 'AND al.livro = "'.$row['id'].'"';
                             
                        $qry_result_percent = $mysqli->query($qry_exec_percent);
                        $qry_rows_percent = mysqli_num_rows($qry_result_percent);

                        if($qry_rows_percent == 1):
                            $row_percent = mysqli_fetch_assoc($qry_result_percent);
                            $row['percentual'] = round($row_percent['percentual']);
                        else:
                            $row['percentual'] = "0";
                        endif;

                        // $row['percentual'] = "0";
                        $retorno['livros'][] = $row;
                    endwhile;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getUnidadesLivro(){
        if($this->method == 'POST'):

            /*Se campo livro está em branco*/
            if(empty($_POST['livro'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Id do livro não encontrado!';
                exit(json_encode($retorno));
            endif;
            $livro = (int)$_POST['livro'];

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):

                /*Verificar se aluno possui acesso ao livro*/
                $this->checkQRCodeUnidadeLivro((int)$auth_decode['usuario']['id_aluno'], $livro);

                $qry_unid_livro = 'SELECT ul.id, ul.livro, ul.unidade, ul.titulo, ul.tipo, ul.grammar_file, ul.texto_grammar, ul.audio_ativo, ul.audio, ul.audio_titulo, ul.audio_transcrito, ul.video_ativo, '
                                . 'ul.video, ul.video_titulo, ul.video_yt, ul.ativo, ul.data_cadastro, rac.id as id_correcao, rac.data_correcao, rac.status '
                                . 'FROM unidade_livro ul '
                                . 'LEFT JOIN resposta_aluno_correcao rac '
                                . 'ON ul.id = rac.id_unidade AND rac.id_aluno = '.(int)$auth_decode['usuario']['id_aluno'].' '
                                . 'WHERE ul.livro = '.$livro.' '
                                . 'ORDER BY ul.livro ASC, ul.unidade ASC';

                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_unid_livro);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Lista de unidades!';

                    while($row = mysqli_fetch_assoc($qry_result)):

                        /*Verificar se há correção*/
                        if($row['id_correcao']):
                            $corrigida = $row['status'] == 'E' ? 'E' : 'C';
                        else:
                            $corrigida = NULL;
                        endif;

                        $retorno['unidades'][] = array(
                            'id' => $row['id'],
                            'livro' => $row['livro'],
                            'unidade' => $row['unidade'],
                            'titulo' => $row['titulo'],
                            'tipo' => $row['tipo'],
                            'grammar_file' => $row['grammar_file'],
                            'texto_grammar' => $row['texto_grammar'],
                            'audio_ativo' => $row['audio_ativo'],
                            'audio' => 'http://'.BASEURL.'/'.$row['audio'],
                            'audio_titulo' => $row['audio_titulo'],
                            'audio_transcrito' => $row['audio_transcrito'],
                            'video_ativo' => $row['video_ativo'],
                            'video' => 'http://'.BASEURL.'/'.$row['video'],
                            'video_titulo' => $row['video_titulo'],
                            'video_yt' => (unserialize($row['video_yt']))?unserialize($row['video_yt']):array(),
                            'ativo' => $row['ativo'], 
                            'data_cadastro' => $row['data_cadastro'],
                            'unidade_corrigida' => $corrigida,
                            'data_correcao' => $row['data_correcao']
                        );
                    endwhile;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getGrammarFile(){
        if($this->method == 'POST'):

            /*Se campo unidade está em branco*/
            if(empty($_POST['unidade'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Id da unidade não encontrado!';
                exit(json_encode($retorno));
            endif;
            $unidade = (int)$_POST['unidade'];

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                $qry_grammar = 'SELECT texto_grammar FROM unidade_livro '
                    . 'WHERE id = '.$unidade.' '
                    . 'LIMIT 1';
                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_grammar);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Grammar File relacionado a unidade';
                    while ($row = mysqli_fetch_assoc($qry_result)):
                        $retorno['grammar_file'] = $row['texto_grammar'];
                    endwhile;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getUnidadesAtualizadas(){
        if($this->method == 'POST'):

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):

                $qry_unids = 'SELECT ul.id, ul.livro, ul.unidade, ul.titulo, ul.tipo, ul.grammar_file, ul.texto_grammar, ul.audio_ativo, ul.audio, ul.audio_titulo, ul.audio_transcrito, '
                            . 'ul.video_ativo, ul.video, ul.video_titulo, ul.video_yt, ul.ativo, ul.data_cadastro, ul.data_atualizacao, rac.id as id_correcao, rac.data_correcao, rac.status '
                            . 'FROM unidade_livro ul '
                            . 'LEFT JOIN resposta_aluno_correcao rac '
                            . 'ON ul.id = rac.id_unidade AND rac.id_aluno = '.(int)$auth_decode['usuario']['id_aluno'].' '
                            . 'WHERE 1 = 1 ';

                /*Se houver livro*/
                if(!empty($_POST['livro'])):
                    $qry_unids .= "AND ul.livro = ".(int)$_POST['livro']." ";
                endif;

                /*Se houver data de atualização*/
                if(!empty($_POST['data'])):
                    $qry_unids .= "AND ul.data_atualizacao >= '".$_POST['data']."' ";
                endif;

                $qry_unids .= "ORDER BY ul.livro ASC, ul.unidade ASC";
                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_unids);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Lista de unidades atualizadas!';

                    while($row = mysqli_fetch_assoc($qry_result)):

                        /*Verificar se há correção*/
                        if($row['id_correcao']):
                            $corrigida = $row['status'] == 'E' ? 'E' : 'C';
                        else:
                            $corrigida = NULL;
                        endif;

                        $retorno['unidades'][] = array(
                            'id' => $row['id'],
                            'livro' => $row['livro'],
                            'unidade' => $row['unidade'],
                            'titulo' => $row['titulo'],
                            'tipo' => $row['tipo'],
                            'grammar_file' => $row['grammar_file'],
                            'texto_grammar' => $row['texto_grammar'],
                            'audio_ativo' => $row['audio_ativo'],
                            'audio' => 'http://'.BASEURL.'/'.$row['audio'],
                            'audio_titulo' => $row['audio_titulo'],
                            'audio_transcrito' => $row['audio_transcrito'],
                            'video_ativo' => $row['video_ativo'],
                            'video' => 'http://'.BASEURL.'/'.$row['video'],
                            'video_titulo' => $row['video_titulo'],
                            'video_yt' => (unserialize($row['video_yt']))?unserialize($row['video_yt']):array(),
                            'ativo' => $row['ativo'],
                            'data_cadastro' => $row['data_cadastro'], 
                            'data_atualizacao' => $row['data_atualizacao'],
                            'unidade_corrigida' => $corrigida,
                            'data_correcao' => $row['data_correcao']
                        );
                    endwhile;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;                
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getExercicios(){
        if($this->method == 'POST'):

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                $token = $_POST['token'];
                $device_id = $_POST['device_id'];
                /*$qry_exec_unid = 'SELECT e.id, e.livro, e.unidade, e.ordem_exercicio, e.tipo_exercicio, e.titulo_exercicio, e.exemplo, e.ativo, e.data_cadastro, e.data_atualizacao, '
                            . 'e.audio, e.arquivo, e.titulo_arquivo, e.texto_audio '
                            . 'FROM exercicios e '
                            . 'LEFT JOIN pergunta_exercicio pe '
                            . 'ON e.id = pe.exercicio '
                            . 'INNER JOIN livros l ON '
                            . 'l.id = e.livro '
                            . 'INNER JOIN unidades u ON '
                            . 'l.empresa = u.id_empresa '
                            . 'INNER JOIN users us ON '
                            . 'us.unidade_id = u.id '
                            . 'INNER JOIN aluno_device ad ON '
                            . 'ad.id_aluno = us.id '
                            . 'WHERE ad.device_id = "'.$device_id.'" '
                            . 'AND ad.token = "'.$token.'" ';*/
                            
                $qry_exec_unid = 'SELECT e.id, e.livro, e.unidade, e.ordem_exercicio, e.tipo_exercicio, e.titulo_exercicio, e.exemplo, e.ativo, e.data_cadastro, e.data_atualizacao, '
                            . 'e.audio, e.arquivo, e.titulo_arquivo, e.texto_audio '
                            . 'FROM exercicios e '
                            . 'INNER JOIN aluno_livro al ON '
                            . 'al.livro = e.livro '
                            . 'INNER JOIN aluno_device ad ON '
                            . 'ad.id_aluno = al.aluno '
                            . 'WHERE ad.device_id = "'.$device_id.'" '
                            . 'AND ad.token = "'.$token.'" '
                            . 'AND e.ativo = "S" ';

                //Se último id de exercício recuperado e ids anteriores forem passados
                if(!empty($_POST['id_exercicio']) && !empty($_POST['next_id'])):
                    $next_ids_array = explode(',', base64_decode($_POST['next_id']));
                    $posicao_array = array_search($_POST['id_exercicio'], $next_ids_array);

                    if($posicao_array > 0):
                        $last_id = $next_ids_array[$posicao_array];
                        $ids_not_in = '';
                        foreach($next_ids_array as $id_key => $id_value):
                            if($id_value < $last_id):
                                $ids_not_in .= $id_value.',';
                            endif;
                        endforeach;

                        $qry_exec_unid .= 'AND e.id >= '.$last_id.' AND e.id NOT IN('.rtrim($ids_not_in,',').') ';
                    else:
                        $qry_exec_unid .= 'AND e.id >= '.$_POST['id_exercicio'].' AND e.id NOT IN('.base64_decode($_POST['next_id']).') ';
                    endif;
                else:
                    //Se campo id_exercicio for postado é incluído na query
                    if(!empty($_POST['id_exercicio'])):
                        $qry_exec_unid .= 'AND e.id >= '.$_POST['id_exercicio'].' ';
                    endif;

                    //Se campo next_id for postado é incluído na query
                    if(!empty($_POST['next_id'])):
                        $qry_exec_unid .= 'AND e.id NOT IN('.base64_decode($_POST['next_id']).') ';
                    endif;
                endif;

                //Unidade não será mais obrigatória
                if(!empty($_POST['unidade'])):
                    $unidade = (int)$_POST['unidade'];
                    $qry_exec_unid .= 'AND e.unidade = '.$unidade.' ';
                endif;

                //Livro
                if(!empty($_POST['livro'])):
                    $livro = (int)$_POST['livro'];
                    $qry_exec_unid .= 'AND e.livro = '.$livro.' ';
                endif;

                //Se houver data de atualização
                if(!empty($_POST['data'])):
                    $data_atualizacao = $_POST['data'];
                    $qry_exec_unid .= 'AND (e.data_atualizacao >= "'.$data_atualizacao.'" OR pe.data_atualizacao >= "'.$data_atualizacao.'") ';
                else:
                    $data_atualizacao = '';
                endif;

                //Atualização de método para limitar quantidade de exercícios a serem extraídos da pesquisa
                //Se campo limit for postado é incluído na query
                $limit = 0;
                $limit_sql = '';
                if(!empty($_POST['limit'])):
                    $limit = (int)$_POST['limit'];
                    $limit_sql .= ' LIMIT '.(int)$_POST['limit'];
                endif;

                $qry_exec_unid.= 'GROUP BY e.id '
                            .'ORDER BY e.unidade, e.id, e.tipo_exercicio'.$limit_sql;
                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_exec_unid);
                $qry_rows = mysqli_num_rows($qry_result);

                $ids_exercicios_select = '';
                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Lista de exercícios da unidade!';

                    while($row = mysqli_fetch_assoc($qry_result)):
                        //Resgatar ids de exercicios
                        $ids_exercicios_select .= $row['id'].',';

                        $exercicios[] = array(
                            'id' => $row['id'],
                            'livro' => $row['livro'],
                            'unidade' => $row['unidade'],
                            'ordem_exercicio' => $row['ordem_exercicio'],
                            'tipo_exercicio' => $row['tipo_exercicio'],
                            'titulo_exercicio' => $row['titulo_exercicio'],
                            'exemplo' => $row['exemplo'],
                            'ativo' => $row['ativo'],
                            'data_cadastro' => $row['data_cadastro'],
                            'data_atualizacao' => $row['data_atualizacao'],
                            'audio' => $row['audio'],
                            'arquivo' => 'http://'.BASEURL.'/'.$row['arquivo'],
                            'titulo_arquivo' => $row['titulo_arquivo'],
                            'texto_audio' => $row['texto_audio']
                        );
                    endwhile;

                    /*Insere perguntas relacionadas ao exercício*/
                    foreach($exercicios as $exercicio):
                        /*Se houver necessidade de executar download de mídia*/
                        if(!empty($_POST['down']) && $exercicio['audio'] == 'S'):
                            $exercicio['midia'] = $this->downloadMidia($exercicio['id']);
                        else:
                            $exercicio['midia'] = NULL;
                        endif;
                        $exercicio['perguntas'] = $this->getPerguntasExercicio($exercicio['id'], $data_atualizacao);
                        $retorno['exercicios'][] = $exercicio;
                    endforeach;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;

                //Se postado next_id, contendo as ids posteriores ao limite original
                if(!empty($_POST['next_id'])):
                    $ids_anter_ret = explode(',', base64_decode($_POST['next_id']));
                    $ids_anteriores = implode(',', $ids_anter_ret);
                    $ids = $ids_anteriores.','.rtrim($ids_exercicios_select, ',');
                else:
                    $ids = rtrim($ids_exercicios_select, ',');
                endif;

                //Contador do limit da primeira query, recupera o id
                if($qry_rows == $limit):
                    //Array de retorno para paginação
                    $retorno['search_data'] = array(
                                            'next_id' => base64_encode($ids),
                                            'count' => $qry_rows
                                        );
                elseif($qry_rows < $limit):
                    //Array de retorno para paginação
                    $retorno['search_data'] = array(
                                            'next_id' => NULL,
                                            'count' => $qry_rows
                                        );
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getMidiasExercicio(){
        if($this->method == 'POST'):

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):

                /*$qry_unidades = 'SELECT ul.id, ul.titulo '
                            . 'FROM unidade_livro ul '
                            . 'WHERE ul.ativo = "S" ';
                */

                $token = $_POST['token'];
                $device_id = $_POST['device_id'];
                $qry_unidades = 'SELECT ul.id, ul.titulo 
                    FROM unidade_livro ul 
                    INNER JOIN aluno_livro al ON ul.livro = al.livro
                    INNER JOIN aluno_device ad ON al.aluno = ad.id_aluno
                    WHERE ul.ativo = "S" AND ad.device_id = "'.$device_id.'" AND ad.token = "'.$token.'"';

                /*Se campo next_id for postado é incluído na query*/
                if(!empty($_POST['next_id'])):
                    $qry_unidades .= 'AND ul.id NOT IN('.base64_decode($_POST['next_id']).') ';
                endif;

                /*Unidade não é obrigatória*/
                if(!empty($_POST['unidade'])):
                    $unidade = (int)$_POST['unidade'];
                    $qry_unidades .= 'AND ul.id = '.$unidade.' ';
                endif;

                $limit = '';
                /*Se campo limit for postado é incluído na query*/
                if(!empty($_POST['limit'])):
                    /*$retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Limite não foi encontrado!';
                    exit(json_encode($retorno));*/
                    $limit = ' LIMIT '.(int)$_POST['limit'];
                endif;

                $qry_unidades .= 'GROUP BY ul.id '
                                .'ORDER BY ul.id, ul.titulo '.$limit;

                $mysqli = $this->conexao->getDatabase();
                $qry_unidades_res = $mysqli->query($qry_unidades);
                $qry_num_rows_unidades = mysqli_num_rows($qry_unidades_res);

                if($qry_num_rows_unidades >  0):

                    /*Resgatar unidades*/
                    while($rows_unidade = mysqli_fetch_assoc($qry_unidades_res)):                        
                        $ids_unidades[] = $rows_unidade['id'];
                    endwhile;

                    /*Implode de unidades resgatadas*/
                    $ids_unidades_selec = implode(',', $ids_unidades);

                    $qry_exec_unid = 'SELECT e.id, e.livro, e.unidade, e.ordem_exercicio, e.tipo_exercicio, e.titulo_exercicio, e.exemplo, e.ativo, e.data_cadastro, e.data_atualizacao, '
                                    . 'e.audio, e.arquivo, e.titulo_arquivo, e.texto_audio '
                                    . 'FROM exercicios e '
                                    . 'INNER JOIN pergunta_exercicio pe '
                                    . 'ON e.id = pe.exercicio '
                                    . 'WHERE e.audio = "S" AND e.ativo = "S" '
                                    . 'AND e.unidade IN ('.$ids_unidades_selec.') ';

                    /*Exercício não é obrigatório*/
                    if(!empty($_POST['exercicio'])):
                        $exercicio = (int)$_POST['exercicio'];
                        $qry_exec_unid .= 'AND e.id = '.$exercicio.' ';
                    endif;

                    if(!empty($_POST['livro'])):
                        $livro = (int)$_POST['livro'];
                        $qry_exec_unid .= 'AND e.livro = '.$livro.' ';
                    endif;

                    /*Se houver data de atualização*/
                    if(!empty($_POST['data'])):
                        $data_atualizacao = $_POST['data'];
                        $qry_exec_unid .= 'AND (e.data_atualizacao >= "'.$data_atualizacao.'" OR pe.data_atualizacao >= "'.$data_atualizacao.'") ';
                    else:
                        $data_atualizacao = '';
                    endif;

                    $qry_exec_unid.= 'GROUP BY e.id '
                                    .'ORDER BY e.unidade, e.id, e.ordem_exercicio, e.tipo_exercicio';

                    $qry_result = $mysqli->query($qry_exec_unid);
                    $qry_rows = mysqli_num_rows($qry_result);

                    if($qry_rows > 0):
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 11;
                        $retorno['mensagem'] = 'Lista de exercícios por unidade contendo áudio!';
                        while($row = mysqli_fetch_assoc($qry_result)):
                            $exercicios[] = array(
                                'id' => $row['id'],
                                'livro' => $row['livro'],
                                'unidade' => $row['unidade'],
                                'ordem_exercicio' => $row['ordem_exercicio'],
                                'tipo_exercicio' => $row['tipo_exercicio'],
                                'titulo_exercicio' => $row['titulo_exercicio'],
                                'exemplo' => $row['exemplo'],
                                'ativo' => $row['ativo'],
                                'data_cadastro' => $row['data_cadastro'],
                                'data_atualizacao' => $row['data_atualizacao'],
                                'audio' => $row['audio'],
                                'arquivo' => 'http://'.BASEURL.'/'.$row['arquivo'],
                                'titulo_arquivo' => $row['titulo_arquivo'],
                                'texto_audio' => $row['texto_audio']
                            );                        
                        endwhile;

                        /*Insere mídia relacionada ao exercício*/
                        foreach($exercicios as $exercicio):
                            if(!empty($_POST['down']) && $exercicio['audio'] == 'S'):
                                $exercicio['midia'] = $this->downloadMidia($exercicio['id']);
                            else:
                                $exercicio['midia'] = NULL;
                            endif;
                            //$exercicio['midia'] = $this->downloadMidia($exercicio['id']);
                            $retorno['exercicios'][] = $exercicio;
                        endforeach;

                    else:
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 9;
                        $retorno['mensagem'] = 'Não foram encontrados registros de exercícios no banco!';
                    endif;

                    if(!empty($_POST['next_id'])):
                        $ids_anter_ret = explode(',', base64_decode($_POST['next_id']));
                        $ids_anteriores = implode(',', $ids_anter_ret);
                        $ids = $ids_anteriores.','.$ids_unidades_selec;
                    else:
                        $ids = $ids_unidades_selec;
                    endif;

                    /*Contador do limit da primeira query, recupera o id*/
                    if($qry_num_rows_unidades >= $limit):
                        /*Array de retorno para paginação*/
                        $retorno['search_data'] = array(
                            'next_id' => base64_encode($ids),
                            'count' => $qry_num_rows_unidades
                        );
                    elseif($qry_num_rows_unidades < $limit):
                        /*Array de retorno para paginação*/
                        $retorno['search_data'] = array(
                            'next_id' => NULL,
                            'count' => $qry_num_rows_unidades
                        );
                    endif;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;          
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getAudiosUnidade(){
        if($this->method == 'POST'):
            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):

                $qry_unidades = 'SELECT ul.id, ul.titulo '
                            . 'FROM unidade_livro ul '
                            . 'WHERE ul.ativo = "S" '
                            . 'AND ul.tipo = "O" ';

                /*Se campo next_id for postado é incluído na query*/
                if(!empty($_POST['next_id'])):
                    $qry_unidades .= 'AND ul.id NOT IN('.base64_decode($_POST['next_id']).') ';
                endif;

                if(!empty($_POST['livro'])):
                    $qry_unidades .= 'AND ul.livro = "'.$_POST['livro'].'" ';
                endif;

                /*Unidade não é obrigatória*/
                if(!empty($_POST['unidade'])):
                    $unidade = (int)$_POST['unidade'];
                    $qry_unidades .= 'AND ul.id = '.$unidade.' ';
                endif;

                /*Se campo limit for postado é incluído na query*/
                /*if(empty($_POST['limit'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Limite não foi encontrado!';
                    exit(json_encode($retorno));
                endif;*/

                if(!empty($_POST['limit'])):
                    $limit = (int)$_POST['limit'];
                    $qry_unidades .= 'GROUP BY ul.id '
                        .'ORDER BY ul.id, ul.titulo LIMIT '.$limit;
                else:
                    $qry_unidades .= 'GROUP BY ul.id '
                        .'ORDER BY ul.id, ul.titulo';
                endif;

                $mysqli = $this->conexao->getDatabase();
                $qry_unidades_res = $mysqli->query($qry_unidades);
                $qry_num_rows_unidades = mysqli_num_rows($qry_unidades_res);

                if($qry_num_rows_unidades >  0):

                    /*Resgatar unidades*/
                    while($rows_unidade = mysqli_fetch_assoc($qry_unidades_res)):                        
                        $ids_unidades[] = $rows_unidade['id'];
                    endwhile;

                    /*Implode de unidades resgatadas*/
                    $ids_unidades_selec = implode(',', $ids_unidades);

                    $qry_audios = 'SELECT a.id, a.livro, a.unidade, a.titulo, a.ordem, a.caminho_audio, a.titulo_audio, a.ativo '
                                . 'FROM audios a '
                                . 'INNER JOIN livros l '
                                . 'ON a.livro = l.id '
                                . 'WHERE a.ativo = "S" '
                                . 'AND a.unidade IN ('.$ids_unidades_selec.') ';

                    /*Se houver data de atualização*/
                    if(!empty($_POST['data'])):
                        $data_atualizacao = $_POST['data'];
                        $qry_audios .= 'AND (a.data_atualizacao >= "'.$data_atualizacao.'" ';
                    else:
                        $data_atualizacao = '';
                    endif;

                    $qry_audios .= 'GROUP BY a.id '
                                .'ORDER BY a.unidade, a.ordem';

                    $qry_res_audios = $mysqli->query($qry_audios);
                    $qry_num_rows = mysqli_num_rows($qry_res_audios);

                    if($qry_num_rows > 0):
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 11;
                        $retorno['mensagem'] = 'Lista de áudios existentes para unidade!';

                        while($row = mysqli_fetch_assoc($qry_res_audios)):
                            $audios[] = array(
                                'id' => $row['id'],
                                'livro' => $row['livro'],
                                'unidade' => $row['unidade'],
                                'titulo' => $row['titulo'],
                                'ordem' => $row['ordem'],
                                //'caminho_audio' => 'http://'.BASEURL.'/'.$row['caminho_audio'],
                                'caminho_audio' => 'http://cursos.bslidiomas.com.br/'.$row['caminho_audio'],
                                'titulo_audio' => $row['titulo_audio'],
                                'ativo' => $row['ativo']
                                //'midia' => $this->downloadAudio($row['id'])
                            );
                        endwhile;

                        /*Mídia*/
                        $retorno['audios'] = $audios;
                    else:
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 9;
                        $retorno['mensagem'] = 'Não foram encontrados registros de áudio no banco!';                        
                    endif;

                    if(!empty($_POST['next_id'])):
                        $ids_anter_ret = explode(',', base64_decode($_POST['next_id']));
                        $ids_anteriores = implode(',', $ids_anter_ret);
                        $ids = $ids_anteriores.','.$ids_unidades_selec;
                    else:
                        $ids = $ids_unidades_selec;
                    endif;

                    /*Contador do limit da primeira query, recupera o id*/
                    if($qry_num_rows_unidades == $limit):
                        /*Array de retorno para paginação*/
                        $retorno['search_data'] = array(
                                                'next_id' => base64_encode($ids),
                                                'count' => $qry_num_rows_unidades
                                            );
                    elseif($qry_num_rows_unidades < $limit):
                        /*Array de retorno para paginação*/
                        $retorno['search_data'] = array(
                                                'next_id' => NULL,
                                                'count' => $qry_num_rows_unidades
                                            );
                    endif;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getBanners(){
        if($this->method == 'POST'):

            /*Se campo unidade está em branco*/
            if(!empty($_POST['unidade'])):
                $unidade = (int)$_POST['unidade'];
            else:
                $unidade = '';
            endif;

            if(empty($_POST['width'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Informe a largura para a imagem!';
                exit(json_encode($retorno));
            endif;
            // if(empty($_POST['empresa'])):
            //     $retorno['erro'] = TRUE;
            //     $retorno['codigo'] = 1;
            //     $retorno['mensagem'] = 'Informe a largura para a imagem!';
            //     exit(json_encode($retorno));
            // endif;

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                $token = $_POST['token'];
                $device_id = $_POST['device_id'];
            
                /*$qry_banners = 'SELECT id, unidade, titulo, data_publicacao, periodo_indeterminado, inicio, fim, caminho_banner, titulo_banner, link, status FROM banners_app '
                    . 'WHERE ((inicio <= NOW() AND fim >= NOW()) '
                    . 'OR (inicio <= NOW() AND fim IS NULL) '
                    . 'OR (fim >= NOW() AND inicio IS NULL) '
                    . 'OR (periodo_indeterminado = "S")) '
                    . 'AND status = "S" ';

                if(!empty($unidade)):
                    $qry_banners .= 'AND unidade = '.(int)$unidade.' ';
                endif;*/
                
                $qry_banners = 'SELECT b.id, b.empresa_id, b.titulo, b.data_publicacao, b.periodo_indeterminado, b.inicio, b.fim, b.caminho, b.titulo, b.link, b.actived FROM app_banners b 
                    INNER JOIN empresas_users eu ON 
                    b.empresa_id = eu.empresa_id  
                    INNER JOIN users u ON 
                    eu.user_id = u.id 
                    INNER JOIN aluno_device ad ON 
                    ad.id_aluno = u.id 
                    WHERE ad.device_id = "'.$device_id.'" 
                    AND ad.token = "'.$token.'" 
                    AND ((b.inicio <= NOW() AND b.fim >= NOW()) 
                    OR (b.inicio <= NOW() AND b.fim IS NULL) 
                    OR (b.fim >= NOW() AND b.inicio IS NULL) 
                    OR (b.periodo_indeterminado = "S")) 
                    AND b.actived = "S" ';

                if(!empty($_POST['empresa'])):
                    $qry_banners .= ' AND b.empresa_id = "'.$_POST['empresa'].'" ';
                endif;

                $qry_banners .= 'ORDER BY id ASC';
                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_banners);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Lista de banners!';

                    while($row = mysqli_fetch_assoc($qry_result)):

                        if(!empty($_POST['width'])):
                            $arr_img = explode('.', $row['caminho']);
                            $nova_img_banner = BASEPATH.'/upload/app_banner/'.$arr_img[0].'_'.$_POST['width'].'.'.$arr_img[1];

                            $img_find = glob(BASEPATH.'/upload/app_banner/'.$arr_img[0].'_'.$_POST['width'].'.'.$arr_img[1]);
                            if(!$img_find):
                                $resize = new UploadImgBanner(BASEPATH.'/upload/app_banner/'.$row['caminho']);
                                $resize->resizeTo($_POST['width'], 0);
                                $resize->saveImage($nova_img_banner);
                            endif;
                        endif;
                        $retorno['banners'][] = array(
                            'id' => $row['id'],
                            'empresa' => $row['empresa_id'],
                            'titulo' => $row['titulo'],
                            'data_publicacao' => $row['data_publicacao'],
                            'periodo_indeterminado' => $row['periodo_indeterminado'],
                            'inicio' => $row['inicio'],
                            'fim' => $row['fim'],
                            'caminho_banner_orig' => 'http://'.BASEURL.'/upload/app_banner/'.$row['caminho'],
                            'caminho_banner_new' => 'http://'.BASEURL.'/upload/app_banner/'.$arr_img[0].'_'.$_POST['width'].'.'.$arr_img[1],
                            'titulo_banner' => $row['titulo'],
                            'link' => $row['link'],
                            'status' => $row['actived']
                        );
                    endwhile;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function sendRespostas(){
        if($this->method == 'POST'):
            
            // $fp = fopen("respostas_enviadas.txt", "a");
            // $escreve = fwrite($fp, print_r($_POST, TRUE));

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):

                if(empty($_POST['respostas'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Parâmetro contendo as respostas não foi enviado!';
                    exit(json_encode($retorno));
                endif;

                $device_info = $this->getAlunoAparelho($auth_decode['usuario']['device_id'], $auth_decode['usuario']['id_aluno']);
                
                /*Decode das respostas enviadas*/
                if($this->userAgent($device_info["device_info"]) != 'ios') {
                    $_POST['respostas'] = $this->convert_from_latin1_to_utf8_recursively($_POST['respostas']);
                }

                $resps_decode = json_decode($_POST['respostas']);

                switch (json_last_error()) {
                    case JSON_ERROR_DEPTH:
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 6;
                        $retorno['mensagem'] = 'Maximum stack depth exceeded.';
                        return $retorno;
                    break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 6;
                        $retorno['mensagem'] = 'Underflow or the modes mismatch.';
                        return $retorno;
                    break;
                    case JSON_ERROR_CTRL_CHAR:
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 6;
                        $retorno['mensagem'] = 'Unexpected control character found.';
                        return $retorno;
                    break;
                    case JSON_ERROR_SYNTAX:
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 6;
                        $retorno['mensagem'] = 'Syntax error, malformed JSON.';
                        return $retorno;
                    break;
                    case JSON_ERROR_UTF8:
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 6;
                        $retorno['mensagem'] = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                        return $retorno;
                    break;
                }

                $unidade_ant = 0;
                foreach($resps_decode as $resposta):
                    if($resposta->unidade != $unidade_ant && !empty($resposta->unidade)):
                        $unidades[] = $resposta->unidade;
                    endif;
                    $unidade_ant = $resposta->unidade;
                endforeach;

                if(isset($unidades)):
                    foreach($unidades as $key => $id_unidade):
                        //if($this->checkQTDRespostas($resps_decode, $id_unidade)):
                            foreach($resps_decode as $resposta):
                                if($resposta->unidade == $id_unidade):
                                    /*Confirmar se existe respostas contendo áudio*/
                                    if($resposta->audio == 'S'):
                                        $audio_ativo = $resposta->audio;
                                        $resp = $this->uploadMidia($resposta->resposta, $auth_decode['usuario']['id_aluno'], $auth_decode['usuario']['device_id'], $resposta->id_pergunta);
                                    else:
                                        $audio_ativo = 'N';
                                        $resp = addslashes($resposta->resposta);
                                    endif;

                                    $mysqli = $this->conexao->getDatabase();
                                    /*Se resposta já foi registrada executa update*/
                                    if($this->checkRespostaEnviada($auth_decode['usuario']['id_aluno'], $resposta->id_pergunta)):
                                        $sql_update = "UPDATE resposta_aluno SET "
                                                    . " resposta = '{$resp}',"
                                                    . " audio = '{$audio_ativo}',"
                                                    . " data_resposta = NOW()"
                                                    . " WHERE aluno = {$auth_decode['usuario']['id_aluno']} "
                                                    . " AND pergunta = {$resposta->id_pergunta}";
                                        $mysqli->query($sql_update);
                                    else:
                                        $sql_insert = "INSERT INTO resposta_aluno VALUES "
                                                    . "(NULL,"
                                                    . " {$auth_decode['usuario']['id_aluno']},"
                                                    . " {$resposta->id_pergunta},"
                                                    . " '{$resp}',"
                                                    . " '{$audio_ativo}',"
                                                    . " NOW(),"
                                                    . " NULL,"
                                                    . " NULL,"
                                                    . " NULL"
                                                    . ")";
                                        $mysqli->query($sql_insert);
                                    endif;
                                endif;
                            endforeach;
                            if($this->inserirCorrecao($auth_decode['usuario']['id_aluno'], $id_unidade)):
                                exec('php gerarPercentual.php '.$_POST['token'].' '.$_POST['device_id'].' '.$id_unidade.' &');
                                
                                $retorno['erro'] = FALSE;
                                $retorno['codigo'] = 10;
                                $retorno['mensagem'] = 'Respostas enviadas com sucesso!';
                            else:
                                $retorno['erro'] = TRUE;
                                $retorno['codigo'] = 3;
                                $retorno['mensagem'] = 'Erro ao tentar registrar as respostas!';
                                exit(json_encode($retorno));
                            endif;
                        /*else:
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 32;
                            $retorno['mensagem'] = 'Quantidade de respostas enviadas menores do que perguntas existentes!';
                            exit(json_encode($retorno));
                        endif;*/                    
                    endforeach;
                else:
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 27;
                    $retorno['mensagem'] = 'Unidade(s) para respostas não foram enviadas!';
                    exit(json_encode($retorno));
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
            // fclose($fp);
        return $retorno;
    }

    private static function convert_from_latin1_to_utf8_recursively($dat)
   {
      if (is_string($dat)) {
         return utf8_encode($dat);
      } elseif (is_array($dat)) {
         $ret = [];
         foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);

         return $ret;
      } elseif (is_object($dat)) {
         foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

         return $dat;
      } else {
         return $dat;
      }
   }

    protected function getRespostas(){
        if($this->method == 'POST'):

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):

                $qry_resps = 'SELECT r.id, r.pergunta, r.resposta, r.audio, r.data_resposta, r.observacao, r.data_observacao, r.opcao '
                    . 'FROM resposta_aluno r ';

                if(!empty($_POST['livro'])) {
                    $qry_resps .= 'INNER JOIN pergunta_exercicio p ON p.id = r.pergunta ';
                    $qry_resps .= 'INNER JOIN exercicios e ON e.id = p.exercicio ';
                }

                $qry_resps .= 'WHERE r.aluno = '.$auth_decode['usuario']['id_aluno'].' ';

                if(!empty($_POST['livro'])) {
                    $qry_resps .= 'AND e.livro = "'.$_POST['livro'].'" ';
                }

                /*Se houver data de atualização*/
                if(!empty($_POST['data'])):
                    $qry_resps .= 'AND (r.data_observacao >= "'.$_POST['data'].'" OR r.data_resposta >= "'.$_POST['data'].'") ';
                endif;

                $qry_resps .= 'ORDER BY r.id, r.pergunta, r.data_resposta, r.data_observacao';
                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_resps);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Respostas do aluno!';
                    while($row = mysqli_fetch_assoc($qry_result)):
                        if($row['audio'] == 'S'):
                            $resposta = 'http://'.BASEURL.'/'.$row['resposta'];
                        else:    
                            $resposta = $row['resposta'];
                        endif;

                        $retorno['respostas'][] = array(
                            'id' => $row['id'],
                            'pergunta' => $row['pergunta'],
                            'resposta' => $resposta,
                            'audio' => $row['audio'],
                            'data_resposta' => $row['data_resposta'],
                            'observacao' => $row['observacao'],
                            'data_observacao' => $row['data_observacao'],
                            'opcao' => $row['opcao']
                        );
                    endwhile;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getRecentActivities(){
        if($this->method == 'POST'):

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):

                $qry_activities = 'SELECT id, tabela, id_tabela, data_cadastro, data_evento '
                    . 'FROM recent_activities_app '
                    . 'WHERE data_cadastro <= NOW() AND tabela <> "app_eventos"';
                    //OR (data_evento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND data_evento IS NOT NULL)

                /*Se campo next_id for postado é incluído na query*/
                if(!empty($_POST['next_id'])):
                    $qry_activities .= ' AND id NOT IN('.base64_decode($_POST['next_id']).')';
                endif;

                /*Se campo limit for postado é incluído na query*/
                if(empty($_POST['limit'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Limite não foi encontrado!';
                    exit(json_encode($retorno));
                endif;

                $limit = (int)$_POST['limit'];
                $qry_activities .= ' ORDER BY data_cadastro DESC LIMIT 1000';
                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_activities);
                $qry_rows = mysqli_num_rows($qry_result); 

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Atividades Recentes do aluno '.$auth_decode['usuario']['id_aluno'].'!';

                    if($_POST['eventos']):
                        $qry_eventos_act = 'SELECT id, tabela, id_tabela, data_evento '
                            . 'FROM recent_activities_app '
                            . 'WHERE tabela = "app_eventos" '
                            . 'AND data_evento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) '
                            . 'AND data_evento IS NOT NULL '
                            . 'ORDER BY data_evento ASC ';

                        $qry_result_eve = $mysqli->query($qry_eventos_act);
                        $qry_rows_eve = mysqli_num_rows($qry_result_eve);

                        if($qry_rows_eve > 0):
                            while($row_eve = mysqli_fetch_assoc($qry_result_eve)):
                                $info_evento = $this->getEventosInfo($row_eve['id_tabela']);
                                $retorno['recent_activities']['eventos'][] = array(
                                    'id' => $row_eve['id'],
                                    'tipo_atividade' => $row_eve['tabela'],
                                    'data_evento' => $row_eve['data_evento'],
                                    'mensagem' => 'Novo Evento',
                                    'info' => $info_evento
                                );
                            endwhile;
                        endif;
                    endif;

                    while($row = mysqli_fetch_assoc($qry_result)):
                        if($row['tabela'] == 'podcasts'):
                            $info_atividade = $this->getPodcastInfo($row['id_tabela']);
                            $retorno['recent_activities']['atividades'][] = array(
                                'id' => $row['id'],
                                'tipo_atividade' => $row['tabela'],
                                'mensagem' => $row['tabela'] == 'podcasts' ? 'Novo Podcast' : 'Unidade Corrigida',
                                'data_cadastro' => $row['data_cadastro'],
                                'info' => $info_atividade 
                            );
                        elseif($row['tabela'] == 'resposta_aluno'):
                            $sql_aluno = "SELECT id_aluno FROM resposta_aluno_correcao WHERE id = '".$row['id_tabela']."'";
                            $qry_result_aluno = $mysqli->query($sql_aluno);
                            $row_aluno = mysqli_fetch_assoc($qry_result_aluno);
                            if($row_aluno['id_aluno'] == $auth_decode['usuario']['id_aluno']) {
                                $info_atividade = $this->getRespostaInfo($row['id_tabela']);  
                                $retorno['recent_activities']['atividades'][] = array(
                                    'id' => $row['id'],
                                    'tipo_atividade' => $row['tabela'],
                                    'mensagem' => $row['tabela'] == 'podcasts' ? 'Novo Podcast' : 'Unidade Corrigida',
                                    'data_cadastro' => $row['data_cadastro'],
                                    'info' => $info_atividade
                                );
                            }
                        endif;
                    endwhile;

                    foreach($retorno['recent_activities']['atividades'] as $key):
                        $ids[] = $key['id'];                        
                    endforeach;
                    $ids_posteriores = implode(',', $ids);

                    if(!empty($_POST['next_id'])):
                        $ids_anter_ret = explode(',', base64_decode($_POST['next_id']));
                        $ids_anteriores = implode(',', $ids_anter_ret);
                        $ids = $ids_anteriores.','.$ids_posteriores;
                    else:
                        $ids = $ids_posteriores;
                    endif;

                    /*Contador do limit da primeira query, recupera o id*/
                    if($qry_rows == $limit):
                        /*Array de retorno para paginação*/
                        $retorno['search_data'] = array(
                                                'next_id' => base64_encode($ids),
                                                'count' => $qry_rows
                                            );
                    elseif($qry_rows < $limit):
                        /*Array de retorno para paginação*/
                        $retorno['search_data'] = array(
                                                'next_id' => NULL,
                                                'count' => $qry_rows
                                            );
                    endif;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function setPushToken(){
        $detect = new Mobile_Detect;
        if($this->method == 'POST'):

            if(empty($_POST['device_token'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Parâmetro contendo o token não foi enviado!';
                exit(json_encode($retorno));
            endif;
            $device_token = $_POST['device_token'];

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                $sql_update = "UPDATE aluno_device SET "
                            . "device_info = '{$detect->getUserAgent()}',"
                            . "device_token = '{$device_token}'"
                            . "WHERE id_aluno = {$auth_decode['usuario']['id_aluno']} "
                            . "AND device_id = '{$auth_decode['usuario']['device_id']}' "
                            . "AND token = '{$auth_decode['usuario']['token']}'";
                $mysqli = $this->conexao->getDatabase();
                if($mysqli->query($sql_update)):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 10;
                    $retorno['mensagem'] = 'Token para notificações registrado com sucesso';
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 3;
                    $retorno['mensagem'] = 'Erro ao tentar registrar o token para notificações!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function setQRCode(){
        if($this->method == 'POST'):

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):

                if(empty($_POST['qrcode'])):
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Parâmetro contendo o qrcode não foi enviado!';
                    exit(json_encode($retorno));
                endif;

                $qrcode = $_POST['qrcode'];

                /*Checar QRCode válido/encontrado*/
                $retorno_qrcode = $this->checkQRCode($qrcode);
                if(!$retorno_qrcode['erro']):
                    /*Checar novo QRCode*/
                    $this->checkNewQRCode($qrcode, $retorno_qrcode['dados']['livro'], $auth_decode['usuario']['id_aluno']);

                    /*Checar se QRCode já foi utilizado*/
                    if(!$this->checkAlunoQRCode($qrcode, $auth_decode['usuario']['id_aluno'])):

                        /*Checar se aluno já possui código registrado para o livro*/
                        $this->checkAlunoLivro($auth_decode['usuario']['id_aluno'], $retorno_qrcode['dados']['livro']);

                        $sql_insert = "INSERT INTO aluno_livro VALUES "
                                    . "({$auth_decode['usuario']['id_aluno']},"
                                    . " {$retorno_qrcode['dados']['livro']},"
                                    . " '{$qrcode}',"
                                    . " NOW(),"
                                    . " '0'"
                                    . ")";
                        $mysqli = $this->conexao->getDatabase();
                        if($mysqli->query($sql_insert)):
                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 21;
                            $retorno['mensagem'] = 'QRCode registrado com sucesso!';
                            /*Resgatar dados do aluno*/
                            $dados_aluno = $this->getAlunoTurma($auth_decode['usuario']['id_aluno'], $retorno_qrcode['dados']['livro']);
                            $retorno['usuario'] = $dados_aluno;
                        else:
                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 3;
                            $retorno['mensagem'] = 'Erro ao tentar registrar qrcode!';
                        endif;
                    else:
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 23;
                        $retorno['mensagem'] = 'QRCode já registrado no sistema!';
                    endif;
                else:
                    exit(json_encode($retorno_qrcode));
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getQRcode(){
        if($this->method == 'POST'):

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                $qry_code = 'SELECT al.aluno, al.livro, al.qrcode '
                        . 'FROM aluno_livro al '
                        . 'WHERE al.aluno = '.$auth_decode['usuario']['id_aluno'];
                $mysqli = $this->conexao->getDatabase();
                $qry_res = $mysqli->query($qry_code);
                $qry_rows = mysqli_num_rows($qry_res);
                $arr_qrcodes = array();
                if($qry_rows > 0):
                    while($rows = mysqli_fetch_assoc($qry_res)):
                        $arr_qrcodes[] = $rows;
                    endwhile;
                    $retorno['erro'] = false;
                    $retorno['codigo'] = 29;
                    $retorno['mensagem'] = 'Encontrado(s) registro(s) de qrcode para o aluno!';
                    $retorno['info'] = $arr_qrcodes;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro de qrcode anterior para o aluno!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getUnidadesGrammarsRespostasExerciciosMidias(){
        if($this->method == 'POST'):

            /*Se campo livro está em branco*/
            if(empty($_POST['livro'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Id do livro não encontrado!';
                exit(json_encode($retorno));
            endif;
            $livro = (int)$_POST['livro'];

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):

                /*Verificar se aluno possui acesso ao livro*/
                $this->checkQRCodeUnidadeLivro((int)$auth_decode['usuario']['id_aluno'], $livro);
                $mysqli = $this->conexao->getDatabase();

                $qry_unid_livro = 'SELECT ul.id, ul.livro, ul.unidade, ul.titulo, ul.tipo, ul.grammar_file, ul.audio_ativo, ul.audio, ul.audio_titulo, ul.audio_transcrito, ul.video_ativo, '
                                . 'ul.video, ul.video_titulo, ul.video_yt, ul.ativo, ul.data_cadastro, rac.id as id_correcao, rac.data_correcao, rac.status '
                                . 'FROM unidade_livro ul '
                                . 'LEFT JOIN resposta_aluno_correcao rac '
                                . 'ON ul.id = rac.id_unidade AND rac.id_aluno = '.(int)$auth_decode['usuario']['id_aluno'].' '
                                . 'WHERE ul.livro = '.$livro.' '
                                . 'ORDER BY ul.livro ASC, ul.unidade ASC';

                $qry_result = $mysqli->query($qry_unid_livro);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $grammar_file = "";

                    while($row = mysqli_fetch_assoc($qry_result)):

                        /*Verificar se há correção*/
                        if($row['id_correcao']):
                            $corrigida = $row['status'] == 'E' ? 'E' : 'C';
                        else:
                            $corrigida = NULL;
                        endif;

                        $unidade = array(
                            'id' => $row['id'],
                            'livro' => $row['livro'],
                            'unidade' => $row['unidade'],
                            'titulo' => $row['titulo'],
                            'tipo' => $row['tipo'],
                            'grammar_file' => $row['grammar_file'],
                            'audio_ativo' => $row['audio_ativo'],
                            'audio' => 'http://'.BASEURL.'/'.$row['audio'],
                            'audio_titulo' => $row['audio_titulo'],
                            'audio_transcrito' => $row['audio_transcrito'],
                            'video_ativo' => $row['video_ativo'],
                            'video' => 'http://'.BASEURL.'/'.$row['video'],
                            'video_titulo' => $row['video_titulo'],
                            'video_yt' => (unserialize($row['video_yt']))?unserialize($row['video_yt']):array(),
                            'ativo' => $row['ativo'], 
                            'data_cadastro' => $row['data_cadastro'],
                            'unidade_corrigida' => $corrigida,
                            'data_correcao' => $row['data_correcao']
                        );

                        $qry_grammar = 'SELECT texto_grammar FROM unidade_livro '
                            . 'WHERE id = '.$row['id'].' '
                            . 'LIMIT 1';
                        $qry_result_grammar = $mysqli->query($qry_grammar);
                        $qry_rows_grammar = mysqli_num_rows($qry_result_grammar);

                        if($qry_rows_grammar > 0):
                            while ($row_grammar = mysqli_fetch_assoc($qry_result_grammar)):
                                $unidade['grammar_file'] = $row_grammar['texto_grammar'];
                            endwhile;
                        endif;


                        $qry_exec_unid = 'SELECT e.id, e.livro, e.unidade, e.ordem_exercicio, e.tipo_exercicio, e.titulo_exercicio, e.exemplo, e.ativo, e.data_cadastro, e.data_atualizacao, '
                            . 'e.audio, e.arquivo, e.titulo_arquivo, e.texto_audio '
                            . 'FROM exercicios e '
                            . 'INNER JOIN aluno_livro al ON '
                            . 'al.livro = e.livro '
                            . 'INNER JOIN aluno_device ad ON '
                            . 'ad.id_aluno = al.aluno '
                            . 'WHERE ad.device_id = "'.$_POST['device_id'].'" '
                            . 'AND ad.token = "'.$_POST['token'].'" '
                            . 'AND e.ativo = "S" '
                            . 'AND e.unidade = '.$row['id'].' ';

                        if(!empty($_POST['next_id_exercicios'])):
                            $qry_exec_unid .= 'AND e.id NOT IN('.base64_decode($_POST['next_id_exercicios']).') ';
                        endif;

                        $qry_exec_unid.= 'GROUP BY e.id '
                                    .'ORDER BY e.id, e.tipo_exercicio';
                         
                        $qry_result_exec = $mysqli->query($qry_exec_unid);
                        $qry_rows_exec = mysqli_num_rows($qry_result_exec);

                        $ids_exercicios_select = '';
                        if($qry_rows_exec > 0):
                            while($row_exerc = mysqli_fetch_assoc($qry_result_exec)):
                                $ids_exercicios_select .= $row_exerc['id'].',';
                                $exercicios[] = array(
                                    'id' => $row_exerc['id'],
                                    'livro' => $row_exerc['livro'],
                                    'unidade' => $row_exerc['unidade'],
                                    'ordem_exercicio' => $row_exerc['ordem_exercicio'],
                                    'tipo_exercicio' => $row_exerc['tipo_exercicio'],
                                    'titulo_exercicio' => $row_exerc['titulo_exercicio'],
                                    'exemplo' => $row_exerc['exemplo'],
                                    'ativo' => $row_exerc['ativo'],
                                    'data_cadastro' => $row_exerc['data_cadastro'],
                                    'data_atualizacao' => $row_exerc['data_atualizacao'],
                                    'audio' => $row_exerc['audio'],
                                    'arquivo' => 'http://'.BASEURL.'/'.$row_exerc['arquivo'],
                                    'titulo_arquivo' => $row_exerc['titulo_arquivo'],
                                    'texto_audio' => $row_exerc['texto_audio']
                                );
                            endwhile;


                            /*Insere perguntas relacionadas ao exercício*/
                            foreach($exercicios as $exercicio):
                            //     /*Se houver necessidade de executar download de mídia*/
                            //     if($exercicio['audio'] == 'S'):
                            //         $exercicio['midia'] = $this->downloadMidia($exercicio['id']);
                            //     else:
                            //         $exercicio['midia'] = NULL;
                            //     endif;
                                $exercicio['perguntas'] = $this->getPerguntasExercicio($exercicio['id'], '');
                                $unidade['exercicios'][] = $exercicio;
                            endforeach;
                        else:
                            $unidade['exercicios'] = array();
                        endif;


                        //Se postado next_id_exercicios, contendo as ids posteriores ao limite original
                        if(!empty($_POST['next_id_exercicios'])):
                            $ids_anter_ret = explode(',', base64_decode($_POST['next_id_exercicios']));
                            $ids_anteriores = implode(',', $ids_anter_ret);
                            $ids = $ids_anteriores.','.rtrim($ids_exercicios_select, ',');
                        else:
                            $ids = rtrim($ids_exercicios_select, ',');
                        endif;

                        $retorno['search_data'] = array(
                            'next_id_exercicios' => base64_encode($ids),
                            'count_exercicios' => $qry_rows
                        );

                        $retorno['unidades'][] = $unidade;
                    endwhile;
                endif;


                $qry_resps = 'SELECT id, pergunta, resposta, audio, data_resposta, observacao, data_observacao, opcao '
                    . 'FROM resposta_aluno '
                    . 'WHERE aluno = '.$auth_decode['usuario']['id_aluno'].' ';
                /*Se houver data de atualização*/
                if(!empty($_POST['data'])):
                    $qry_resps .= 'AND (data_observacao >= "'.$_POST['data'].'" OR data_resposta >= "'.$_POST['data'].'") ';
                endif;
                $qry_resps .= 'ORDER BY id, pergunta, data_resposta, data_observacao';
                $qry_result = $mysqli->query($qry_resps);
                $qry_rows = mysqli_num_rows($qry_result);

                $retorno['respostas'] = array();
                if($qry_rows > 0):

                    while($row = mysqli_fetch_assoc($qry_result)):
                        if($row['audio'] == 'S'):
                            $resposta_url = 'http://'.BASEURL.'/'.$row['resposta'];
                        else:    
                            $resposta_url = $row['resposta'];
                        endif;

                        $resposta = array(
                            'id' => $row['id'],
                            'pergunta' => $row['pergunta'],
                            'resposta' => $resposta_url,
                            'audio' => $row['audio'],
                            'data_resposta' => $row['data_resposta'],
                            'observacao' => $row['observacao'],
                            'data_observacao' => $row['data_observacao'],
                            'opcao' => $row['opcao']
                        );

                        $retorno['respostas'][] = $resposta;
                    endwhile;
                endif;

                $retorno['erro'] = FALSE;
                $retorno['codigo'] = 11;
                $retorno['mensagem'] = 'Lista de unidades!';

            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    /*FIM - MÉTODOS GET*/

    /*MÉTODOS PRIVADOS*/
    private function getPodcastInfo($id){
        /*Se não possuir id*/
        if(empty($id)):
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 1;
            $retorno['mensagem'] = 'Id do podcast não foi informado!';
            exit(json_encode($retorno));
        endif;

        $qry_pod = "SELECT id, titulo, data_publicacao "
                . "FROM podcasts "
                . "WHERE id = ".(int)$id." "
                . "AND tipo = 'P' "
                . "LIMIT 1";
        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry_pod);
        $qry_rows = mysqli_num_rows($qry_result);
        
        if($qry_rows > 0):
            $podcast = mysqli_fetch_assoc($qry_result);
        endif;
        mysqli_close($mysqli);
        return $podcast;
    }

    private function getRespostaInfo($id){
        /*Se não possuir id*/
        if(empty($id)):
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 1;
            $retorno['mensagem'] = 'Id da resposta não foi informado!';
            exit(json_encode($retorno));
        endif;

        $qry_resp = "SELECT rac.id, rac.id_aluno, rac.id_unidade, rac.id_professor, ul.titulo, ul.tipo "
                . "FROM resposta_aluno_correcao rac "
                . "INNER JOIN unidade_livro ul "
                . "ON rac.id_unidade = ul.id "
                . "WHERE rac.id = ".(int)$id." "
                . "LIMIT 1";
        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry_resp);
        $qry_rows = mysqli_num_rows($qry_result);
        
        if($qry_rows > 0):
            $resposta = mysqli_fetch_assoc($qry_result);
        endif;
        mysqli_close($mysqli);
        return $resposta;
    }

    private function getEventosInfo($id){
        /*Se não possuir id*/
        if(empty($id)):
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 1;
            $retorno['mensagem'] = 'Id do evento não foi informado!';
            exit(json_encode($retorno));
        endif;

        $qry_evento = "SELECT id, titulo, texto "
                . "FROM app_eventos "
                . "WHERE id = ".(int)$id." "
                . "AND data BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
                . "LIMIT 1";
        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry_evento);
        $qry_rows = mysqli_num_rows($qry_result);
        
        if($qry_rows > 0):
            $evento = mysqli_fetch_assoc($qry_result);
        else:
            $evento = '';
        endif;
        mysqli_close($mysqli);
        return $evento;
    }

    private function checkRespostaAluno($id_aluno, $id_pergunta){
        /*Se não possuir id*/
        if(empty($id_aluno) || empty($id_pergunta)):
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 1;
            $retorno['mensagem'] = 'Id do aluno/perguta não foi informado!';
            exit(json_encode($retorno));
        endif;

        $qry_resp = "SELECT id, resposta, audio FROM resposta_aluno "
                . "WHERE aluno = ".(int)$id_aluno." "
                . "AND pergunta = ".(int)$id_pergunta." "
                . "LIMIT 1";
        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry_resp);
        $qry_rows = mysqli_num_rows($qry_result);

        if($qry_rows > 0):
            $resposta = mysqli_fetch_assoc($qry_result);
        else:
            $resposta = '';
        endif;
        mysqli_close($mysqli);
        return $resposta;
    }

    private function getPerguntasExercicio($exercicio, $data_atualizacao){
        /*Se campo exercício estiver em branco*/
        if(empty($exercicio)):
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 1;
            $retorno['mensagem'] = 'Id do exercício não encontrado!';
            exit(json_encode($retorno));
        endif;

        $qry_perg_exec = 'SELECT * FROM pergunta_exercicio '
                . 'WHERE exercicio = '.$exercicio.' ';

        /*Verifica se possui atualização*/
        if(!empty($data_atualizacao)):
            $qry_perg_exec .= 'AND data_atualizacao >= "'.$data_atualizacao.'" ';
        endif;

        $qry_perg_exec .= 'ORDER BY ordem ASC';
        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry_perg_exec);
        $qry_rows = mysqli_num_rows($qry_result);

        $perguntas = [];
        if($qry_rows > 0):
            while($row = mysqli_fetch_assoc($qry_result)):
                $perguntas[] = $row;
            endwhile;
        //else:
            //return FALSE;
        endif;
        mysqli_close($mysqli);
        return $perguntas;
    }

    private function atualizarAparelho($id_aluno, $device_id, $empresa_id){
        /*Verifica a quantidade de aparelhos registrados*/
        $devices = $this->checkAparelhos($id_aluno);
        /*Data atual*/
        $curdate = date('Y-m-d H:i:s');

        /*Se igual a 2*/
        if($devices >= 2):
            $device = $this->getAlunoAparelho($device_id, $id_aluno);
            /*Se o retorno for false, aparelho não foi encontrado, irá apagar o aparelho mais antigo relacionado e então registrar o novo*/
            if($device):
                if(strtotime($device['token_expira_em']) < strtotime($curdate)):
                    $refresh_token = $this->refreshToken($device['id_aluno'], $device_id, $device['token']);
                    if($refresh_token):
                        $dados_novo_token = $this->getUsuarioToken($device['id_aluno'], $device['device_id'], $empresa_id);
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 13;
                        $retorno['mensagem'] = 'Tempo de expiração atualizada!';
                        $retorno['usuario'] = $dados_novo_token;
                    else:
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 3;
                        $retorno['mensagem'] = 'Erro ao atualizar o tempo de expiração, tente novamente!';
                    endif;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 14;
                    $retorno['mensagem'] = 'Login executado com sucesso!';
                    $retorno['usuario'] = $device;
                    $retorno['usuario']['is_professor'] = $this->isProfessor($id_aluno, $empresa_id);
                endif;
            else: 
                if($id_aluno == 213):
                    if($this->excluirAparelho($id_aluno)):
                        /*Parâmetros de registro do novo aparelho*/
                        $params = array(
                            'id_aluno' => $id_aluno,
                            'device_id' => $device_id,
                            'empresa_id' => $empresa_id
                        );

                        /*Registro do aparelho*/
                        return $this->registrarAparelho($params);
                    endif;
                endif;
                
                //bloquear cadastro de um novo aparelho
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 31;
                $retorno['mensagem'] = 'Erro ao autenticar aparelho, limite de dispositivos já alcançado!';
            endif;
            return $retorno;
        elseif($devices <= 1):
            $device = $this->getAlunoAparelho($device_id, $id_aluno);
            if($device):
                if(strtotime($device['token_expira_em']) < strtotime($curdate)):
                    $refresh_token = $this->refreshToken($device['id_aluno'], $device_id, $device['token']);
                    if($refresh_token):
                        $dados_novo_token = $this->getUsuarioToken($device['id_aluno'], $device['device_id'], $empresa_id);
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 13;
                        $retorno['mensagem'] = 'Tempo de expiração atualizada!';
                        $retorno['usuario'] = $dados_novo_token;
                    else:
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 3;
                        $retorno['mensagem'] = 'Erro ao atualizar o tempo de expiração, tente novamente!';
                    endif;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 14;
                    $retorno['mensagem'] = 'Login executado com sucesso!';
                    $retorno['usuario'] = $device;
                    $retorno['usuario']['is_professor'] = $this->isProfessor($id_aluno, $empresa_id);
                endif;
                return $retorno;
            else:
                /*Parâmetros de registro do novo aparelho*/
                $params = array(
                    'id_aluno' => $id_aluno,
                    'device_id' => $device_id,
                    'empresa_id' => $empresa_id
                );

                /*Registro do aparelho*/
                return $this->registrarAparelho($params);
            endif;
        endif;
    }

    private function registrarAparelho($params){
        $detect = new Mobile_Detect;
        $token = $this->generateToken();
        $sql_insert = "INSERT INTO aluno_device "
                    . " VALUES (null,"
                    . " {$params['id_aluno']},"
                    . " '{$params['device_id']}',"
                    . " '{$detect->getUserAgent()}',"
                    . " NULL,"
                    . " '{$token}',"
                    . " NOW(), "
                    . " (NOW() + INTERVAL 1 YEAR)"
                    . ")";
        $mysqli = $this->conexao->getDatabase();
        if($mysqli->query($sql_insert)):
            $device = $this->getAlunoAparelho($params['device_id'], $params['id_aluno']);
            $retorno['erro'] = FALSE;
            $retorno['codigo'] = 10;
            $retorno['mensagem'] = 'Novo aparelho registrado com sucesso!';
            $retorno['usuario'] = $device;
            $retorno['usuario']['is_professor'] = $this->isProfessor($params['id_aluno'], $params['empresa_id']);
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 3;
            $retorno['mensagem'] = 'Erro ao tentar registar o aparelho!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    private function isProfessor($id_aluno, $empresa_id) {
        return "N";
        $mysqli = $this->conexao->getDatabase();
        $is_professor = "N";
        $qry_admin_pref = 'SELECT * FROM admin_preferences WHERE empresa_id = "'.$empresa_id.'"';
        $qry_result_admin_pref = $mysqli->query($qry_admin_pref);
        $qry_rows_admin_pref = mysqli_num_rows($qry_result_admin_pref);
        if($qry_rows_admin_pref > 0):
            $data_admin_pref = mysqli_fetch_assoc($qry_result_admin_pref);

            $qry_groups = 'SELECT * FROM users_groups WHERE group_id = "'.$data_admin_pref["grupo_professor"].'" AND user_id = "'.$id_aluno.'"';
            $qry_result_groups = $mysqli->query($qry_groups);
            $qry_rows_groups = mysqli_num_rows($qry_result_groups);
            if($qry_rows_groups > 0):
                $is_professor = "S";
            endif;
        endif;
        return $is_professor;
    }

    private function refreshToken($id_aluno, $device_id, $token){
        $detect = new Mobile_Detect;
        $sql_update = "UPDATE aluno_device SET "
                . "token_criado_em = NOW(),"
                . "token_expira_em = (NOW() + INTERVAL 1 YEAR),"
                . "device_info = '{$detect->getUserAgent()}'"
                . "WHERE id_aluno = {$id_aluno} "
                . "AND device_id = '{$device_id}' "
                . "AND token = '{$token}'";
        $mysqli = $this->conexao->getDatabase();
        if($mysqli->query($sql_update)):
            mysql_close($mysqli);
            return TRUE;
        else:
            mysql_close($mysqli);
            return FALSE;
        endif;
    }

    private function getUsuarioToken($id_aluno, $device_id, $empresa_id){
        $qry_data = 'SELECT u.nome, u.email, ad.id as id_device, ad.id_aluno, ad.device_id, ad.device_token, ad.token, ad.token_criado_em, ad.token_expira_em '
                . 'FROM users u, aluno_device ad '
                . 'WHERE a.id = ad.id_aluno '
                . 'AND u.id = '.$id_aluno.' '
                . 'AND ad.device_id = "'.$device_id.'"';
        $mysqli = $this->conexao->getDatabase();
        $qry_result_data = $mysqli->query($qry_data);
        $qry_rows = mysqli_num_rows($qry_result_data);

        if($qry_rows > 0):
            $data_usuario = mysqli_fetch_assoc($qry_result_data);
        else:
            return FALSE;
        endif;
        mysqli_close($mysql_close);
        return $data_usuario;
    }

    private function getAlunoAparelho($device_id, $id_aluno){
        $qry_dev = 'SELECT ad.id, ad.id_aluno, ad.device_id, ad.device_info, ad.device_token, ad.token, ad.token_criado_em, ad.token_expira_em, u.email, CONCAT(u.first_name, " ", u.last_name) as nome '
                . 'FROM aluno_device ad '
                . 'INNER JOIN users u ON u.id = ad.id_aluno '
                . 'WHERE ad.device_id = "'.$device_id.'" '
                . 'AND ad.id_aluno = '.$id_aluno;

        $mysqli = $this->conexao->getDatabase();
        $qry_result_dev = $mysqli->query($qry_dev);
        $qry_rows_dev = mysqli_num_rows($qry_result_dev);

        if($qry_rows_dev > 0):
            $device = mysqli_fetch_assoc($qry_result_dev);
        else:
            return FALSE;
        endif;
        mysqli_close($mysqli);
        return $device;
    }

    private function checkAparelhos($id_aluno){
        $qry_dev = 'SELECT id_aluno '
                . 'FROM aluno_device '
                . 'WHERE id_aluno = '.$id_aluno;
        $mysqli = $this->conexao->getDatabase();
        $qry_result_dev = $mysqli->query($qry_dev);
        $qry_rows_dev = mysqli_num_rows($qry_result_dev);
        return $qry_rows_dev;
    }

    private function excluirAparelho($id_aluno){
        $qry_dev = 'DELETE FROM aluno_device '
                . 'WHERE id_aluno = '.$id_aluno.' '
                . 'AND token_criado_em IS NOT NULL '
                . 'ORDER BY token_criado_em ASC LIMIT 1';
        $mysqli = $this->conexao->getDatabase();
        if($mysqli->query($qry_dev)):
            mysql_close($mysqli);
            return true;
        else:
            mysql_close($mysqli);
            return false;
        endif;
    }

    private function desvincularAparelho($device, $token){
        //$qry_dev = 'DELETE FROM aluno_device '
          //      . 'WHERE device_id = "'.$device.'" '
            //    . 'AND token = "'.$token.'" '
              //  . 'AND token_criado_em IS NOT NULL';
              
        $qry_dev = 'UPDATE aluno_device '
                . 'SET token_expira_em = "0000-00-00 00:00:00" '
                . 'WHERE device_id = "'.$device.'" AND '
                . ' token = "'.$token.'" AND '
                . ' token_criado_em IS NOT NULL';
                

        $mysqli = $this->conexao->getDatabase();
        if($mysqli->query($qry_dev)):
            mysqli_close($mysqli);
            return true;
        else:
            mysqli_close($mysqli);
            return false;
        endif;
    }

    private function verificarAparelhoEmUso($id_aluno, $device_id){
        $qry_dev = 'SELECT * FROM aluno_device '
                . 'WHERE device_id = "'.$device_id.'" '
                . 'AND id_aluno <> '.$id_aluno.' '
                . 'AND token_criado_em IS NOT NULL';
        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry_dev);
        $qry_rows = mysqli_num_rows($qry_result);
        if($qry_rows > 0):
            $qtd = $this->checkAparelhos($id_aluno);
            if($qtd >= 2):
                mysql_close($mysqli);
                //$this->excluirAparelho($id_aluno);
                return FALSE;
            endif;
            mysql_close($mysqli);
            return TRUE;
        else:
            return FALSE;
        endif;
    }

    private function alterarAparelhoUsuario($id_aluno, $device_id){
        $detect = new Mobile_Detect;
        $token = $this->generateToken();
        $sql_update = "UPDATE aluno_device SET "
                . "id_aluno = {$id_aluno}, "
                . "token = '{$token}', "
                . "token_criado_em = NOW(), "
                . "token_expira_em = (NOW() + INTERVAL 1 YEAR),"
                . "device_info = '{$detect->getUserAgent()}'"
                . "WHERE device_id = '{$device_id}'";
        $mysqli = $this->conexao->getDatabase();
        if($mysqli->query($sql_update)):
            mysql_close($mysqli);
            return TRUE;
        else:
            mysql_close($mysqli);
            return FALSE;
        endif;
    }

    private function validarEmail($email){
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)):
            return TRUE;
        else:
            return FALSE;
        endif;
    }

    private function validarDuplicidadeEmail($email) {
        $qry_sel = 'SELECT email FROM users WHERE lower(email) = "'.$email.'"';
        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry_sel);
        $qry_rows = mysqli_num_rows($qry_result);
        if($qry_rows > 0):
            mysql_close($mysqli);
            return TRUE;
        else:
            mysql_close($mysqli);
            return FALSE;
        endif;
    }

    /*Método para comparar se senha atual coincide com o registro*/
    private function checarSenhaAtual($id, $senhaAtual){
        $sql_query = "SELECT * FROM users WHERE id = ".$id." AND lower(password) = '".$senhaAtual."' LIMIT 1";

        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($sql_query);
        $qry_rows = mysqli_num_rows($qry_result);

        if ($qry_rows > 0):
            return FALSE;
        else:
            return true;
        endif;
    }

    /**
     * @author: Willian Colognesi
     * Metódo responsável por gerar uma senha aleatória.
     * @param int $tamanho quantidade de caracteres da senha(Padrão 9)
     * @param int $forca força da senha à ser gerada(letras maisculas/números,etc).
     * 
     * @return String senha gerada.
     */
    private function gerarSenha($tamanho = 9, $forca = 0) {
        $vogais = 'aeuy';
        $consoantes = 'bdghjmnpqrstvz';
        if ($forca >= 1) {
            $consoantes .= 'BDGHJLMNPQRSTVWXZ';
        }
        if ($forca >= 2) {
            $vogais .= "AEUY";
        }
        if ($forca >= 4) {
            $consoantes .= '23456789';
        }
        if ($forca >= 8) {
            $vogais .= '@#$%';
        }

        $senha = '';
        $alt = time() % 2;
        for ($i = 0; $i < $tamanho; $i++) {
            if ($alt == 1) {
                $senha .= $consoantes[(rand() % strlen($consoantes))];
                $alt = 0;
            } else {
                $senha .= $vogais[(rand() % strlen($vogais))];
                $alt = 1;
            }
        }
        return $senha;
    }

    /*Cria o diretório e arquivo de áudio*/
    private function uploadMidia($midia, $aluno, $device_id, $pergunta){
        $diretorio_midia = BASEPATH . '/upload/respostas/audio/'.$aluno.'/'.$pergunta.'/';

        if (!is_dir($diretorio_midia)):
            mkdir($diretorio_midia, 0777, true);
            chmod($diretorio_midia, 0777);
        endif;

        $midia_anterior = $this->getMidiaResposta($aluno, $pergunta);
        /*Unlink de mídia*/
        @unlink(BASEPATH . $midia_anterior);

        $device_info = $this->getAlunoAparelho($device_id, $aluno);

        if($this->userAgent($device_info['device_info']) == 'ios'):
            $nome_arquivo = md5(uniqid(time())) . ".caf";
        elseif($this->userAgent($device_info['device_info']) == 'android'):
            $nome_arquivo = md5(uniqid(time())) . ".mp3";
        else:
            $nome_arquivo = md5(uniqid(time())) . ".mp3";
        endif;

        $audio_data = fopen($diretorio_midia.$nome_arquivo, 'w+');
        fwrite($audio_data, base64_decode($midia));
        fclose($audio_data);
        chmod($diretorio_midia.$nome_arquivo, 0777);

        return $caminho_midia = "upload/respostas/audio/".$aluno."/".$pergunta."/".$nome_arquivo;
    }

    private function downloadMidia($exercicio){
        $qry_exercicio = 'SELECT id, audio, arquivo, titulo_arquivo, texto_audio '
                    . 'FROM exercicios '
                    . 'WHERE id = '.(int)$exercicio.' '
                    . 'AND audio = "S" AND ativo = "S"';
        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry_exercicio);
        $qry_rows  = mysqli_num_rows($qry_result);
        if($qry_rows > 0):
            $row = mysqli_fetch_assoc($qry_result);

            $path = 'http://'.BASEURL.'/'.$row['arquivo'];
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = base64_encode($data);            
            $midia = array(
                'file_size' => strlen($data),
                'file_human_size' => $this->human_filesize(strlen($data), 2),
                'base64char_size' => strlen($base64),
                'base64_file' => $base64,
            );
            mysql_close($mysqli);
            return $midia;
        else:
            mysql_close($mysqli);
            return false;
        endif;
    }

    private function downloadAudio($audio){
        $qry_audios = 'SELECT id, caminho_audio '
                    . 'FROM audios '
                    . 'WHERE id = '.(int)$audio.' '
                    . 'AND ativo = "S"';

        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry_audios);
        $qry_rows  = mysqli_num_rows($qry_result);
        if($qry_rows > 0):
            $row = mysqli_fetch_assoc($qry_result);

            $path = 'http://'.BASEURL.'/'.$row['caminho_audio'];
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = base64_encode($data);

            $audio = array(
                'file_size' => strlen($data),
                'file_human_size' => $this->human_filesize(strlen($data), 2),
                'base64char_size' => strlen($base64),
                'base64_file' => $base64,
            );
            mysql_close($mysqli);
            return $audio;
        else:
            mysql_close($mysqli);
            return false;
        endif;
    }

    private function getMidiaResposta($aluno, $pergunta){
        $qry_resp = 'SELEct id, resposta, audio FROM resposta_aluno '
                . 'WHERE aluno = '.$aluno.' '
                . 'AND pergunta = '.$pergunta;
        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry_resp);
        $qry_rows = mysqli_num_rows($qry_result);
        if($qry_rows > 0):
            $row = mysqli_fetch_assoc($qry_result);
            mysql_close($mysqli);
            return $row['resposta'];
        else:
            mysql_close($mysqli);
            return FALSE;
        endif;
    }

    private function checkRespostaEnviada($aluno, $pergunta){
        $qry_resps = 'SELECT id FROM resposta_aluno '
                . 'WHERE aluno = '.$aluno.' '
                . 'AND pergunta = '.$pergunta;

        $mysqli = $this->conexao->getDatabase();
        $qry_result = $mysqli->query($qry_resps);
        $qry_rows = mysqli_num_rows($qry_result);
        if($qry_rows > 0):
            mysql_close($mysqli);
            return TRUE;
        else:
            mysql_close($mysqli);
            return FALSE;
        endif;
    }

    private function getAlunoTurma($id_aluno, $id_livro){
        /*$qry_data = 'SELECT ad.id_aluno, u.nome, u.email, ad.id as id_device, ad.device_id, ad.device_token, ad.token, ad.token_criado_em, ad.token_expira_em, l.id as id_livro, '
                    . 'l.titulo as titulo_livro, al.qrcode '
                    . 'FROM users u '
                    . 'INNER JOIN aluno_device ad '
                    . 'ON ad.id_aluno = u.id '
                    . 'INNER JOIN turma_aluno ta '
                    . 'ON ta.aluno = u.id '
                    . 'INNER JOIN turma_old t '
                    . 'ON ta.turma = t.id '
                    . 'INNER JOIN livros l '
                    . 'ON l.id = t.livro '
                    . 'INNER JOIN aluno_livro al '
                    . 'ON al.aluno = u.id AND al.livro = l.id '
                    . 'WHERE u.id = '.$id_aluno.' '
                    . 'AND l.id = '.$id_livro;*/

        $qry_data = 'SELECT ad.id_aluno, u.first_name, u.email, ad.id as id_device, ad.device_id, ad.device_token, ad.token, ad.token_criado_em, ad.token_expira_em, l.id as id_livro, '
                    . 'l.titulo as titulo_livro, al.qrcode '
                    . 'FROM users u '
                    . 'INNER JOIN aluno_device ad '
                    . 'ON ad.id_aluno = u.id '
                    . 'INNER JOIN aluno_livro al '
                    . 'ON al.aluno = u.id '
                    . 'INNER JOIN livros l '
                    . 'ON l.id = al.livro '
                    . 'WHERE al.aluno = '.$id_aluno.' '
                    . 'AND al.livro = '.$id_livro;

        $mysqli = $this->conexao->getDatabase();
        $qry_result_data = $mysqli->query($qry_data);
        $qry_rows = mysqli_num_rows($qry_result_data);

        if($qry_rows > 0):
            $data_aluno = mysqli_fetch_assoc($qry_result_data);
        else:
            return FALSE;
        endif;
        mysqli_close($mysqli);
        return $data_aluno;
    }

    private function checkAlunoQRCode($qrcode, $id_aluno){
        $qry_code = 'SELECT aluno, livro, qrcode '
                . 'FROM aluno_livro '
                . 'WHERE qrcode = "'.$qrcode.'" '
                . 'AND aluno <> '.$id_aluno.' ' 
                . 'LIMIT 1';
        $mysqli = $this->conexao->getDatabase();
        $qry_res = $mysqli->query($qry_code);
        $qry_rows = mysqli_num_rows($qry_res);

        if($qry_rows > 0):
            mysql_close($mysqli);
            return TRUE;
        else:
            mysql_close($mysqli);
            return FALSE;
        endif;
    }

    private function checkAlunoLivro($id_aluno, $id_livro){
        $qry_code = 'SELECT aluno, livro, qrcode '
                . 'FROM aluno_livro '
                . 'WHERE aluno = '.$id_aluno.' '
                . 'AND livro = '.$id_livro.' LIMIT 1';
        $mysqli = $this->conexao->getDatabase();
        $qry_res = $mysqli->query($qry_code);
        $qry_rows = mysqli_num_rows($qry_res);

        if($qry_rows > 0):
            $dados = $this->getAlunoTurma($id_aluno, $id_livro);

            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 25;
            $retorno['mensagem'] = 'Aluno já possui qrcode registrado para o livro!';
            $retorno['usuario'] = $dados;
            mysql_close($mysqli);
            exit(json_encode($retorno));
        else:
            mysql_close($mysqli);
            return FALSE;
        endif;
    }

    private function userAgent($device_info){
        $iPod = strpos($device_info,"iPod");
        $iPhone = strpos($device_info,"iPhone");
        $iPad = strpos($device_info,"iPad");
        $android = strpos($device_info,"Android");
        $java = strpos($device_info,"java");

        if($iPad||$iPhone||$iPod):
            return 'ios';
        elseif($android || $java):
            return 'android';
        else:
            return 'pc';
        endif;
    }

    private function checkNewQRCode($qrcode, $id_livro, $id_aluno){
        /*$qry_code = 'SELECT qr.id, qr.codigo '
                . 'FROM qrcodes qr '
                . 'INNER JOIN livros l '
                . 'ON l.id = qr.livro '
                . 'INNER JOIN turma_old t '
                . 'ON t.livro = l.id '
                . 'INNER JOIN turma_aluno ta '
                . 'ON ta.turma = t.id '
                . 'WHERE qr.codigo = "'.$qrcode.'" '
                . 'AND qr.livro = '.(int)$id_livro.' '
                . 'AND ta.aluno = '.$id_aluno.' '
                . 'LIMIT 1';*/
                
        $qry_code = 'SELECT qr.id, qr.codigo '
                . 'FROM qrcodes qr '
                . 'INNER JOIN livros l '
                . 'ON l.id = qr.livro '
                . 'INNER JOIN turma t '
                . 'ON t.LIG_ID_PACOTE = l.id '
                . 'INNER JOIN turma_aluno ta '
                . 'ON ta.turma = t.id '
                . 'WHERE qr.codigo = "'.$qrcode.'" '
                . 'AND qr.livro = '.(int)$id_livro.' '
                . 'AND ta.aluno = '.$id_aluno.' '
                . 'LIMIT 1';

        $mysqli = $this->conexao->getDatabase();
        $qry_res = $mysqli->query($qry_code);
        $qry_rows = mysqli_num_rows($qry_res);

        if($qry_rows > 0):
            mysql_close($mysqli);
            return FALSE;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 24;
            $retorno['mensagem'] = 'Usuário não possui acesso a este livro!';
            mysql_close($mysqli);
            exit(json_encode($retorno));
        endif;
    }

    private function checkQRCode($qrcode){
        $qry_code = 'SELECT id, codigo, livro '
                . 'FROM qrcodes '
                . 'WHERE codigo = "'.$qrcode.'" '
                . 'LIMIT 1';

        $mysqli = $this->conexao->getDatabase();
        $qry_res = $mysqli->query($qry_code);
        $qry_rows = mysqli_num_rows($qry_res);

        if($qry_rows > 0):
            $dados = mysqli_fetch_assoc($qry_res);
            $retorno['erro'] = false;
            $retorno['codigo'] = 1;
            $retorno['mensagem'] = 'QRCode encontrado!';
            $retorno['dados'] = $dados;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 26;
            $retorno['mensagem'] = 'QRCode inválido/não encontrado!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    private function countPerguntas($id_unidade){
        $qry_pergs = "SELECT pe.id "
                    . "FROM pergunta_exercicio pe "
                    . "INNER JOIN exercicios e "
                    . "ON pe.exercicio = e.id "
                    . "INNER JOIN unidade_livro ul "
                    . "ON e.unidade = ul.id "
                    . "WHERE ul.id = ".(int)$id_unidade;

        $mysqli = $this->conexao->getDatabase();
        $qry_res = $mysqli->query($qry_pergs);
        $qry_rows = mysqli_num_rows($qry_res);
        mysql_close($mysqli);
        return $qry_rows;
    }

    private function inserirCorrecao($id_aluno, $id_unidade){
        $qry_crc = "SELECT id, status "
                . "FROM resposta_aluno_correcao "
                . "WHERE id_aluno = ".(int)$id_aluno." "
                . "AND id_unidade = ".(int)$id_unidade;

        $mysqli = $this->conexao->getDatabase();
        $qry_res_crc = $mysqli->query($qry_crc);
        $qry_rows_crc = mysqli_num_rows($qry_res_crc);

        if($qry_rows_crc > 0):
            $dados_crc = mysqli_fetch_assoc($qry_res_crc);
            if($dados_crc['status'] == 'C'):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 28;
                $retorno['mensagem'] = 'Respostas enviadas já foram corrigidas, não é possível atualizá-las!';
                exit(json_encode($retorno));
            elseif($dados_crc['status'] == 'E'):
                return TRUE;
            endif;
        else:
            $sql_insert_correcao = "INSERT INTO resposta_aluno_correcao "
                                . "VALUES(NULL,"
                                . "{$id_aluno},"
                                . "{$id_unidade},"
                                . "NULL,"
                                . "NOW(),"
                                . "NULL,"
                                . "'E',"
                                . "'S'"
                                . ")";

            if($mysqli->query($sql_insert_correcao)):
                return TRUE;
            else:
                return FALSE;
            endif;
        endif;
    }

    private function checkQTDRespostas($respostas, $unidade){
        /*Verificar quantidade de perguntas/respostas a serem enviadas*/
        $count = 1;
        $qtd_pergs = $this->countPerguntas($unidade);
        foreach($respostas as $resposta):
            if($resposta->unidade == $unidade && !empty($resposta->unidade)):
                $count_respostas = $count++;
            endif;
        endforeach;

        if($count_respostas < $qtd_pergs):
            return FALSE;
        else:
            return TRUE;
        endif;
    }

    private function checkQRCodeUnidadeLivro($id_aluno, $id_livro){
        /*$qry_qrcode = 'SELECT ad.id_aluno, u.nome, u.email, ad.id as id_device, ad.device_id, ad.device_token, ad.token, ad.token_criado_em, ad.token_expira_em, l.id as id_livro, '
                    . 'l.titulo as titulo_livro, al.qrcode '
                    . 'FROM users u '
                    . 'INNER JOIN aluno_device ad '
                    . 'ON ad.id_aluno = u.id '
                    . 'INNER JOIN turma_aluno ta '
                    . 'ON ta.aluno = u.id '
                    . 'INNER JOIN turma t '
                    . 'ON ta.turma = t.id '
                    . 'INNER JOIN aluno_livro al '
                    . 'ON al.aluno = u.id '
                    . 'INNER JOIN livros l '
                    . 'ON l.id = al.livro '
                    . 'ON al.aluno = u.id AND al.livro = l.id '
                    . 'WHERE al.aluno = '.$id_aluno.' '
                    . 'AND al.livro = '.$id_livro;*/

        $qry_qrcode = 'SELECT ad.id_aluno, u.first_name as nome, u.email, ad.id as id_device, ad.device_id, ad.device_token, ad.token, ad.token_criado_em, ad.token_expira_em, l.id as id_livro, '
                    . 'l.titulo as titulo_livro, al.qrcode '
                    . 'FROM users u '
                    . 'INNER JOIN aluno_device ad '
                    . 'ON ad.id_aluno = u.id '
                    . 'INNER JOIN aluno_livro al '
                    . 'ON al.aluno = u.id '
                    . 'INNER JOIN livros l '
                    . 'ON l.id = al.livro '
                    . 'WHERE al.aluno = '.$id_aluno.' '
                    . 'AND al.livro = '.$id_livro;

        $mysqli = $this->conexao->getDatabase();
        $qry_res = $mysqli->query($qry_qrcode);
        $qry_rows = mysqli_num_rows($qry_res);

        if($qry_rows > 0):
            return FALSE;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 22;
            $retorno['mensagem'] = 'Usuário registrado, mas não possui código para acesso ao livro!';
            exit(json_encode($retorno));
        endif;
    }

    private function human_filesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

    /*FIM - MÉTODOS PRIVADOS*/
    
    public function criarAluno() {
        if($this->method == 'POST') {
            $mysqli = $this->conexao->getDatabase();
             
            $selUsername = 'SELECT username FROM users WHERE username="'.$_POST['email'].'" LIMIT 1';
            $selEmailU = 'SELECT email FROM users WHERE email="'.$_POST['email'].'" LIMIT 1';
            
            $selEmailP = 'SELECT email FROM pessoas WHERE email="'.$_POST['email'].'" LIMIT 1';
            
            $queryEmailP = $mysqli->query($selEmailP);
            $queryEmailU = $mysqli->query($selEmailU);
            $queryUsername = $mysqli->query($selUsername);
            
            if(mysqli_num_rows($queryEmailP) > 0 || mysqli_num_rows($queryEmailU) > 0 || mysqli_num_rows($queryUsername) > 0){
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'E-mail ou username já cadastrado!';
            } else {
                $unidade_id = $this->getUnidadeByCnpj($_POST['unidade']);
                
                if($unidade_id != false && $unidade_id != "") {
                    $insertUser = 'INSERT INTO users (username, password, nome, apelido, email, telefone, celular, unidade_id, role_id, setor_id, actived)
                                        VALUES("'.trim($_POST['email']).'", "'.md5('cebrac@123').'",
                                         "'.trim($_POST['nome']).'",
                                         "'.trim($_POST['apelido']).'",  "'.trim($_POST['email']).'",
                                         "'.$_POST['fixo'].'",  "'.$_POST['cel'].'",
                                         "'.$unidade_id.'", "1",
                                         "6", \'s\')';
                    if($mysqli->query($insertUser)) {
                        $selUsuario = 'SELECT id FROM users WHERE username="'.$_POST['email'].'" LIMIT 1';
                        $queryID = $mysqli->query($selUsuario);
                        $idUsuario = $queryID->fetch_array();

                        $insertPessoa = 'INSERT INTO pessoas (user_id, nome, data_nascimento,
                                        endereco, numero, cep, telefone, celular, email, 
                                        bairro, cidade, uf, unidade_id, actived) 
                                        VALUES("'.$idUsuario['id'].'", "'.trim($_POST['nome']).'",
                                        "'.$this->formata_data($_POST['data_nascimento']).'", "'.$_POST['endereco'].'",
                                        "'.$_POST['numero'].'", "'.$_POST['cep'].'", "'.$_POST['fixo'].'", "'.$_POST['cel'].'",
                                        "'.trim($_POST['email']).'", "'.$_POST['bairro'].'",
                                        "'.$_POST['cidade'].'", "'.$_POST['uf'].'", '.$unidade_id.', \'s\')';
                                        
                        if($mysqli->query($insertPessoa)) {
                            $this->cadastra_permissoes_role($idUsuario['id'] , "1");
                            
                            //buscar todas unidades do cebrac
                            $select_unids_cebrac = "SELECT * FROM unidades WHERE id_empresa = '1'";
                            $query_unids_cebrac = $mysqli->query($select_unids_cebrac);
                            if(mysqli_num_rows($query_unids_cebrac) > 0) {
                                $unids_cebrac = array();
                                while($linha_unids_cebrac = mysqli_fetch_assoc($query_unids_cebrac)) {
                                    $unids_cebrac[] = $linha_unids_cebrac['id'];
                                }
                                
                                //buscar todas turmas do cebrac
                                $select_turmas_cebrac = "SELECT * FROM turma WHERE ";
                                $i_select_turmas_cebrac = 1;
                                foreach($unids_cebrac as $_unid_cebrac) {
                                    if($i_select_turmas_cebrac > 1)
                                        $select_turmas_cebrac .= " OR ";
                                    $select_turmas_cebrac .= "unidade = '".$_unid_cebrac."'";
                                    $i_select_turmas_cebrac++;
                                }
                                
                                $query_turmas_cebrac = $mysqli->query($select_turmas_cebrac);
                                if(mysqli_num_rows($query_turmas_cebrac) > 0) {
                                    $turmas_cebrac = array();
                                    while($linha_turmas_cebrac = mysqli_fetch_assoc($query_turmas_cebrac)) {
                                        $turmas_cebrac[] = $linha_turmas_cebrac['ID'];
                                    }
                                    
                                    //inserindo aluno nas turmas do cebrac
                                    if(count($turmas_cebrac) > 0) {
                                        $sql_insert_aluno_turma = "INSERT INTO turma_aluno (id, aluno, turma, data_cadastro, status) VALUES ";
                                        $i_turmas_cebrac = 1; 
                                        foreach($turmas_cebrac as $_turma_cebrac) {
                                            if($i_turmas_cebrac > 1)
                                                $sql_insert_aluno_turma .= ",";
                                            $sql_insert_aluno_turma .= "('', '".$idUsuario['id']."', '".$_turma_cebrac."', NOW(), 'S')";
                                            $i_turmas_cebrac++;
                                        }
                                        
                                        $mysqli->query($sql_insert_aluno_turma);
                                    }
                                }
                            }
                            
                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 0;
                            $retorno['mensagem'] = 'Aluno cadastrado com sucesso!';
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = 'Erro ao inserir novo Aluno!';
                        }
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = 'Erro ao inserir novo Aluno!';
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Erro ao buscar cnpj da unidade!';
                }
            }
            mysql_close($mysqli);
            exit(json_encode($retorno));
        } 
    }
    
    public function editarAluno() {
        if($this->method == 'POST') {
            $mysqli = $this->conexao->getDatabase();
             
            if($_POST['email'] != $_POST['email_atual']) {
                $selUsername = 'SELECT username FROM users WHERE username="'.$_POST['email'].'" LIMIT 1';
                $selEmailU = 'SELECT email FROM users WHERE email="'.$_POST['email'].'" LIMIT 1';
                
                $selEmailP = 'SELECT email FROM pessoas WHERE email="'.$_POST['email'].'" LIMIT 1';
                
                $queryEmailP = $mysqli->query($selEmailP);
                $queryEmailU = $mysqli->query($selEmailU);
                $queryUsername = $mysqli->query($selUsername);
                
                if(mysqli_num_rows($queryEmailP) > 0 || mysqli_num_rows($queryEmailU) > 0 || mysqli_num_rows($queryUsername) > 0){
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'E-mail ou username já cadastrado!';
                    exit(json_encode($retorno));
                }
            } 
            
            $unidade_id = $this->getUnidadeByCnpj($_POST['unidade']);
            
            if($unidade_id != false && $unidade_id != "") {
                $updateUser = 'UPDATE users SET 
                    username = "'.trim($_POST['email']).'", 
                    nome = "'.trim($_POST['nome']).'", 
                    apelido = "'.trim($_POST['apelido']).'", 
                    email = "'.trim($_POST['email']).'", 
                    telefone = "'.$_POST['fixo'].'", 
                    celular = "'.$_POST['cel'].'", 
                    unidade_id = "'.$unidade_id.'" 
                    WHERE username = "'.$_POST['email_atual'].'"';
                
                if($mysqli->query($updateUser)) {
                    $selUsuario = 'SELECT id FROM users WHERE username="'.$_POST['email'].'" LIMIT 1';
                    $queryID = $mysqli->query($selUsuario);
                    $idUsuario = $queryID->fetch_array();

                    $updatePessoa = 'UPDATE pessoas SET 
                        nome = "'.trim($_POST['nome']).'", 
                        data_nascimento = "'.$this->formata_data($_POST['data_nascimento']).'", 
                        endereco = "'.$_POST['endereco'].'", 
                        numero = "'.$_POST['numero'].'", 
                        cep = "'.$_POST['cep'].'", 
                        telefone = "'.$_POST['fixo'].'", 
                        celular = "'.$_POST['cel'].'", 
                        email = "'.trim($_POST['email']).'",  
                        bairro = "'.$_POST['bairro'].'", 
                        cidade = "'.$_POST['cidade'].'", 
                        uf = "'.$_POST['uf'].'", 
                        unidade_id = "'.$unidade_id.'" 
                        WHERE user_id = "'.$idUsuario['id'].'"';
                                    
                    if($mysqli->query($updatePessoa)) {
                        $retorno['erro'] = FALSE;
                        $retorno['codigo'] = 0;
                        $retorno['mensagem'] = 'Aluno alterado com sucesso!';
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 1;
                        $retorno['mensagem'] = 'Erro ao alterar Aluno!';
                    }
                } else {
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 2;
                    $retorno['mensagem'] = 'Erro ao alterar Aluno!';
                }
            } else {
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Erro ao buscar cnpj da unidade!';
            }
            mysql_close($mysqli);
            exit(json_encode($retorno));
        } 
    }
    
    public function alteraStatusAluno() {
        if($this->method == 'POST') {
            $mysqli = $this->conexao->getDatabase();
            
            if($_POST['email']) {
                $selEmailU = 'SELECT id, email FROM users WHERE email="'.$_POST['email'].'" LIMIT 1';
                $queryEmailU = $mysqli->query($selEmailU);              
                if(mysqli_num_rows($queryEmailU) == 0){
                    $retorno['erro'] = TRUE;
                    $retorno['codigo'] = 1;
                    $retorno['mensagem'] = 'Cliente não encontrado!';
                    exit(json_encode($retorno));
                } else {
                    $resQueryUsers = mysqli_fetch_assoc($queryEmailU);
                    $updateUser = 'UPDATE users SET 
                        actived = "'.$_POST['actived'].'" 
                        WHERE id = "'.$resQueryUsers['id'].'"';
                    
                    if($mysqli->query($updateUser)) {
                        $updatePessoa = 'UPDATE pessoas SET 
                            actived = "'.$_POST['actived'].'" 
                            WHERE user_id = "'.$resQueryUsers['id'].'"';
                                        
                        if($mysqli->query($updatePessoa)) {
                            $retorno['erro'] = FALSE;
                            $retorno['codigo'] = 0;
                            $retorno['mensagem'] = 'Status alterado com sucesso!';
                        } else {
                            $retorno['erro'] = TRUE;
                            $retorno['codigo'] = 1;
                            $retorno['mensagem'] = 'Erro ao alterar Status!';
                        }
                    } else {
                        $retorno['erro'] = TRUE;
                        $retorno['codigo'] = 2;
                        $retorno['mensagem'] = 'Erro ao alterar Status!';
                    }
                }
            } else {
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 2;
                $retorno['mensagem'] = 'Houve um erro, entre em contato com o administrador!';
            }
            mysql_close($mysqli);
            exit(json_encode($retorno));
        }
    }
    
    private function cadastra_permissoes_role($idUsuario , $roleId){
        $mysqli = $this->conexao->getDatabase();
        
        $insertPermissao = 'INSERT INTO permissoes (user_id, menu_id) VALUES ('.$idUsuario.', 12) '; // 12=> home.
        $deletePermissoes = 'DELETE FROM permissoes WHERE user_id='.$idUsuario;
        
        try {
            $mysqli->query($deletePermissoes); // Primeiro apaga as permissões.
            $mysqli->query($insertPermissao); // Grava novas permissões.
            return true;
        } catch(Exception $e) {
            return false;
        }
    }
    
    private function formata_data($data){
        $SeparadoEspaco = explode(' ', $data);
        $dataSeparada = $SeparadoEspaco[0];
        $dataNew = explode('/', $dataSeparada);
        return $dataNew[2].'-'.$dataNew[1].'-'.$dataNew[0];
    }
    
    private function getUnidadeByCnpj($cnpj) {
        $mysqli = $this->conexao->getDatabase();
        $sql = "SELECT id FROM unidades WHERE cnpj like '%".$cnpj."%' LIMIT 1";
        try {
            $query = $mysqli->query($sql);
            $res = mysqli_fetch_array($query);
            return $res['id'];
        } catch(Exception $e) {
            return false;
        }
    }
    
    protected function getPercents(){
        if($this->method == 'POST'):

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                $token = $_POST['token'];
                $device_id = $_POST['device_id'];
                
                $qry_exec = 'SELECT ((COUNT(*) * 100) / (SELECT COUNT(*) FROM pergunta_exercicio pe INNER JOIN exercicios e ON pe.exercicio = e.id INNER JOIN unidade_livro ul ON ul.id = e.unidade WHERE e.livro = 1 AND ul.tipo = "O")) AS percentual '
                            . 'FROM resposta_aluno ra '
                            . 'INNER JOIN pergunta_exercicio pe '
                            . 'ON ra.pergunta = pe.id '
                            . 'INNER JOIN exercicios e '
                            . 'ON pe.exercicio = e.id '
                            . 'INNER JOIN unidade_livro ul ON ul.id = e.unidade '
                            . 'INNER JOIN aluno_device ad '
                            . 'ON ad.id_aluno = ra.aluno '
                            . 'WHERE ad.device_id = "'.$device_id.'" '
                            . 'AND ad.token = "'.$token.'" '
                            . 'AND e.livro = "1" AND ul.tipo= "O"';
                $qry_exec2 = 'SELECT ((COUNT(*) * 100) / (SELECT COUNT(*) FROM pergunta_exercicio pe INNER JOIN exercicios e ON pe.exercicio = e.id INNER JOIN unidade_livro ul ON ul.id = e.unidade WHERE e.livro = 1 AND ul.tipo = "O")) AS percentual '
                            . 'FROM resposta_aluno ra '
                            . 'INNER JOIN pergunta_exercicio pe '
                            . 'ON ra.pergunta = pe.id '
                            . 'INNER JOIN exercicios e '
                            . 'ON pe.exercicio = e.id '
                            . 'INNER JOIN unidade_livro ul ON ul.id = e.unidade '
                            . 'INNER JOIN aluno_device ad '
                            . 'ON ad.id_aluno = ra.aluno '
                            . 'WHERE ad.device_id = "'.$device_id.'" '
                            . 'AND ad.token = "'.$token.'" '
                            . 'AND e.livro = "2" AND ul.tipo= "O"';
                $qry_exec3 = 'SELECT ((COUNT(*) * 100) / (SELECT COUNT(*) FROM pergunta_exercicio pe INNER JOIN exercicios e ON pe.exercicio = e.id INNER JOIN unidade_livro ul ON ul.id = e.unidade WHERE e.livro = 1 AND ul.tipo = "O")) AS percentual '
                            . 'FROM resposta_aluno ra '
                            . 'INNER JOIN pergunta_exercicio pe '
                            . 'ON ra.pergunta = pe.id '
                            . 'INNER JOIN exercicios e '
                            . 'ON pe.exercicio = e.id '
                            . 'INNER JOIN unidade_livro ul ON ul.id = e.unidade '
                            . 'INNER JOIN aluno_device ad '
                            . 'ON ad.id_aluno = ra.aluno '
                            . 'WHERE ad.device_id = "'.$device_id.'" '
                            . 'AND ad.token = "'.$token.'" '
                            . 'AND e.livro = "3" AND ul.tipo= "O"';
                $qry_exec4 = 'SELECT ((COUNT(*) * 100) / (SELECT COUNT(*) FROM pergunta_exercicio pe INNER JOIN exercicios e ON pe.exercicio = e.id INNER JOIN unidade_livro ul ON ul.id = e.unidade WHERE e.livro = 1 AND ul.tipo = "O")) AS percentual '
                            . 'FROM resposta_aluno ra '
                            . 'INNER JOIN pergunta_exercicio pe '
                            . 'ON ra.pergunta = pe.id '
                            . 'INNER JOIN exercicios e '
                            . 'ON pe.exercicio = e.id '
                            . 'INNER JOIN unidade_livro ul ON ul.id = e.unidade '
                            . 'INNER JOIN aluno_device ad '
                            . 'ON ad.id_aluno = ra.aluno '
                            . 'WHERE ad.device_id = "'.$device_id.'" '
                            . 'AND ad.token = "'.$token.'" '
                            . 'AND e.livro = "4" AND ul.tipo= "O"';
                $qry_exec5 = 'SELECT ((COUNT(*) * 100) / (SELECT COUNT(*) FROM pergunta_exercicio pe INNER JOIN exercicios e ON pe.exercicio = e.id INNER JOIN unidade_livro ul ON ul.id = e.unidade WHERE e.livro = 1 AND ul.tipo = "O")) AS percentual '
                            . 'FROM resposta_aluno ra '
                            . 'INNER JOIN pergunta_exercicio pe '
                            . 'ON ra.pergunta = pe.id '
                            . 'INNER JOIN exercicios e '
                            . 'ON pe.exercicio = e.id '
                            . 'INNER JOIN unidade_livro ul ON ul.id = e.unidade '
                            . 'INNER JOIN aluno_device ad '
                            . 'ON ad.id_aluno = ra.aluno '
                            . 'WHERE ad.device_id = "'.$device_id.'" '
                            . 'AND ad.token = "'.$token.'" '
                            . 'AND e.livro = "5" AND ul.tipo= "O"';

                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_exec);
                $qry_result2 = $mysqli->query($qry_exec2);
                $qry_result3 = $mysqli->query($qry_exec3);
                $qry_result4 = $mysqli->query($qry_exec4);
                $qry_result5 = $mysqli->query($qry_exec5);
                $qry_rows = mysqli_num_rows($qry_result);
                $qry_rows2 = mysqli_num_rows($qry_result2);
                $qry_rows3 = mysqli_num_rows($qry_result3);
                $qry_rows4 = mysqli_num_rows($qry_result4);
                $qry_rows5 = mysqli_num_rows($qry_result5);

                if($qry_rows == 1 && $qry_rows2 == 1 && $qry_rows3 == 1 && $qry_rows4 == 1 && $qry_rows5 == 1):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Percentual!';

                    $row = mysqli_fetch_assoc($qry_result);
                    $row2 = mysqli_fetch_assoc($qry_result2);
                    $row3 = mysqli_fetch_assoc($qry_result3);
                    $row4 = mysqli_fetch_assoc($qry_result4);
                    $row5 = mysqli_fetch_assoc($qry_result5);
                    $retorno['percentual'][] = round($row['percentual']);
                    $retorno['percentual'][] = round($row2['percentual']);
                    $retorno['percentual'][] = round($row3['percentual']);
                    $retorno['percentual'][] = round($row4['percentual']);
                    $retorno['percentual'][] = round($row5['percentual']);
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getCtReceb(){
        if($this->method == 'POST'):

            if(empty($_POST['empresa'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Id da empresa não encontrado!';
                exit(json_encode($retorno));
            endif;

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                $token = $_POST['token'];
                $device_id = $_POST['device_id'];
                $empresa = $_POST['empresa'];
                
                $qry_exec = 'select r.CODIGO, r.FILIAL, r.DOCUMENTO, r.CLIENTE, r.VALOR, r.VENCIMENTO, r.VALOR_PAGO, r.DATA_PGTO, r.TIPO_RECEBIMENTO, r.TIPO_CONDICAO_PARCELA_ID, r.PARCELA_ID, cond.DESCRICAO, p.parcela, p.orc_id '
                            . 'from ct_receb r '
                            . 'INNER JOIN unidades u '
                            . 'ON u.id = r.FILIAL '
                            . 'INNER JOIN aluno_device ad '
                            . 'ON ad.id_aluno = r.CLIENTE '
                            . 'INNER JOIN tipo_condicao_parcela_orcamento cond '
                            . 'ON cond.ID = r.TIPO_CONDICAO_PARCELA_ID '
                            . 'INNER JOIN par_orca p '
                            . 'ON p.id = r.PARCELA_ID '
                            . 'WHERE ad.device_id = "'.$device_id.'" '
                            . 'AND ad.token = "'.$token.'" '
                            . 'AND u.empresa_id = "'.$empresa.'" '
                            . 'ORDER BY p.parcela, p.id ASC';
                

                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_exec);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Recebimentos!';

                    while($row = mysqli_fetch_assoc($qry_result)):
                        $retorno['recebimentos'][] = $row;
                    endwhile;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getContrato(){
        if($this->method == 'POST'):

            if(empty($_POST['empresa'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Id da empresa não encontrado!';
                exit(json_encode($retorno));
            endif;

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                $token = $_POST['token'];
                $device_id = $_POST['device_id'];
                $empresa = $_POST['empresa'];
                
                $qry_exec = 'select c.id, c.data_pedido, c.data_matricula, c.qnt_parcelas, l.DESCRICAO as curso_desc, s.descricao as status_desc '
                            . 'from cad_orca c '
                            . 'INNER JOIN cad_status s '
                            . 'ON s.id = c.status '
                            . 'INNER JOIN curso l '
                            . 'ON c.lig_id_curso = l.ID_CURSO '
                            . 'INNER JOIN unidades u '
                            . 'ON u.id = c.filial '
                            . 'INNER JOIN aluno_device ad '
                            . 'ON ad.id_aluno = c.user_id '
                            . 'WHERE ad.device_id = "'.$device_id.'" '
                            . 'AND ad.token = "'.$token.'" '
                            . 'AND u.empresa_id = "'.$empresa.'" '
                            . 'ORDER BY c.id ASC';
                

                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_exec);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Contratos!';

                    while($row = mysqli_fetch_assoc($qry_result)):
                        $retorno['contratos'][] = $row;
                    endwhile;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }

    protected function getNotas(){
        if($this->method == 'POST'):

            if(empty($_POST['empresa'])):
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = 1;
                $retorno['mensagem'] = 'Id da empresa não encontrado!';
                exit(json_encode($retorno));
            endif;

            $auth = $this->authenticate();
            $auth_decode = $auth;

            if(!$auth_decode['erro']):
                $token = $_POST['token'];
                $device_id = $_POST['device_id'];
                $empresa = $_POST['empresa'];
                
                $qry_exec = 'SELECT n.ID, n.LIG_ID_CONTRATO, n.NOTA, a.PESO, n.DATA_AVALIACAO, a.LIG_ID_DISCIPLINA, l.id as livro_id '
                            . 'FROM pedagogico_avaliacoes_notas n '
                            . 'INNER JOIN pedagogico_avaliacoes a '
                            . 'ON a.ID_AVALIACAO = n.LIG_ID_AVALIACAO '
                            . 'INNER JOIN livros l '
                            . 'ON l.LIG_ID_DISCIPLINA = a.LIG_ID_DISCIPLINA '
                            . 'INNER JOIN aluno_device ad '
                            . 'ON ad.id_aluno = n.LIG_ID_ALUNO '
                            . 'WHERE ad.device_id = "'.$device_id.'" '
                            . 'AND ad.token = "'.$token.'" '
                            . 'AND l.empresa = "'.$empresa.'" '
                            . 'ORDER BY n.DATA_AVALIACAO, n.ID ASC';
                

                $mysqli = $this->conexao->getDatabase();
                $qry_result = $mysqli->query($qry_exec);
                $qry_rows = mysqli_num_rows($qry_result);

                if($qry_rows > 0):
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 11;
                    $retorno['mensagem'] = 'Notas!';

                    while($row = mysqli_fetch_assoc($qry_result)):
                        $retorno['notas'][] = $row;
                    endwhile;
                else:
                    $retorno['erro'] = FALSE;
                    $retorno['codigo'] = 9;
                    $retorno['mensagem'] = 'Não foi encontrado registro no banco!';
                endif;
            else:
                $retorno['erro'] = TRUE;
                $retorno['codigo'] = $auth_decode['codigo'];
                $retorno['mensagem'] = $auth_decode['mensagem'];
            endif;
        else:
            $retorno['erro'] = TRUE;
            $retorno['codigo'] = 6;
            $retorno['mensagem'] = 'Nenhum parâmetro recebido!';
        endif;
        mysqli_close($mysqli);
        return $retorno;
    }
}