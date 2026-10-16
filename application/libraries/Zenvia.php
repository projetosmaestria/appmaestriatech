<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Zenvia
{
    private $user = 'xkey.api';
    private $pass = 'XwtXSLC1DH';

    public function createSingleJsonSms($sms) {
        $obj_sms = array(
            "from" => $sms['from'],
            "to" => $sms['to'],
            "schedule" => @$sms['schedule'],
            "msg" => $sms['msg'],
            "callbackOption" => "ALL",
            "id" => @$sms['id'],
            "aggregateId" => ''
        );

        $send_single = array(
            "sendSmsRequest" => $obj_sms
        );
        return json_encode($send_single);
    }

    public function zenviaCallSingle($endpoint, $json_sms) {
        $basic = base64_encode($this->user.':'.$this->pass);

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
}