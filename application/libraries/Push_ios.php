<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Push_ios
{
    private $url;
    private $pem;
    private $pem_pass;
    private $output;

    function __construct(){
        $this->pem = FCPATH.'assets/cert/ck.pem';
        // $this->pem_pass = 'asdf!@#$';
        $this->pem_pass = 'cebrac';
        //$this->url  = 'ssl://gateway.sandbox.push.apple.com:2195';
        $this->url  = 'ssl://api.sandbox.push.apple.com:443';
        //$this->url  = 'ssl://api.push.apple.com:443';
    }

    public function notify($regIds, $data){
        $fp = $this->connect();
        if($fp):
            //Criando o corpo do push
            $body['aps'] = array(
                'alert' => $data,
                'sound' => 'default'
            );

            //Encode do corpo
            $payload = json_encode($body);
            //Construindo a notificação binária
            $msg = chr(0).pack('n', 32).pack('H*', $regIds).pack('n', strlen($payload)).$payload;
            //Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));
            //Fechar a conexão
            fclose($fp);

            return $result;
        else:
            return FALSE;
        endif;
    }

    private function connect(){
        //Checar se o arquivo pem existe
        if(!file_exists($this->pem)):
            return FALSE;
        endif;

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->pem);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->pem_pass);

        //Abrindo conexão com servidor apns
        $fp = stream_socket_client(
            $this->url, 
            $err,
            $errstr, 
            60, 
            STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, 
            $ctx
        );

        return $fp;
    }
    
}