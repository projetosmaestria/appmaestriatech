<?php

class Iugu
{
    private $iugu_url = "https://api.iugu.com/v1/";

    function cancelar_assinatura($token, $assinatura_id) {
        $requisicao = $this->requisicao($token, "subscriptions/".$assinatura_id, array(), "DELETE");

        if ($requisicao["erro"] == true) {
            return array("erro" => true, "msg" => $requisicao["msg"]);
        } else {
            $retorno = json_decode($requisicao["data"], true);
            
            if(!isset($retorno["errors"])) {
                return array("erro" => false, "data" => $retorno);
            } else {
                return array("erro" => true, "msg" => $retorno["errors"]);
            }
        }
    }

    function criar_fatura($token, $usuario, $expires_at, $cad_orca_id, $valor, $tipo_pagamento) {
        //$expires_at = date('Y-m-d', strtotime("+1 days"));
        $tel_celular_explode = explode(" ", $usuario["celular"]);
        $tel_celular_prefixo = str_replace("(", "", $tel_celular_explode[0]);
        $tel_celular_prefixo = str_replace(")", "", $tel_celular_prefixo);
        $tel_celular_numero = str_replace("-", "", $tel_celular_explode[1]);
        $data = array(
            "email" => $usuario["email"],
            "due_date" => $expires_at,
            "ensure_workday_due_date" => false,
            "items" => array(
                array(
                    "description" => "Cancelamento",
                    "price_cents" => number_format($valor * 100, 0, "", ""),
                    "quantity" => 1
                )
            ),
            // "return_url" => BASE_URL."matricula.php",
            // "expired_url" => BASE_URL."matricula.php",
            //"notification_url" => BASE_URL."webhook.php",
            "fines" => false,
            "per_day_interest" => false,
            "discount_cents" => 0,
            "customer_id" => $usuario["customer_id"],
            "ignore_due_email" => false,
            "payable_with" => $tipo_pagamento,
            "early_payment_discount" => false,
            "payer" => array(
                "cpf_cnpj" => $usuario["cpf"],
                "name" => utf8_encode($usuario['nome']),
                "phone" => $tel_celular_numero,
                "phone_prefix" => "0".$tel_celular_prefixo,
                "email" => $usuario["email"],
                "address" => array(
                    "zip_code" => $usuario["cep"],
                    "number" => $usuario["numero"],
                    "street" => utf8_encode($usuario["endereco"]),
                    "city" => utf8_encode($usuario["cidade"]),
                    "state" => $usuario["uf"],
                    "district" => utf8_encode($usuario["bairro"]),
                    "complement" => utf8_encode($usuario["complemento"])
                )
            ),
            "order_id" => $cad_orca_id."_CA"
        );

        $requisicao = $this->requisicao($token, "invoices", $data);

        if ($requisicao["erro"] == true) {
            return array("erro" => true, "msg" => $requisicao["msg"]);
        } else {
            $retorno = json_decode($requisicao["data"], true);

            if(!isset($retorno["errors"])) {
                return array("erro" => false, "data" => $retorno);
            } else {
                return array("erro" => true, "msg" => $retorno["errors"]);
            }
        }
    }
	
	function cancelar_fatura($token, $fatura_id) {
        $requisicao = $this->requisicao($token, "invoices/".$fatura_id."/cancel", array(), "PUT");

        if ($requisicao["erro"] == true) {
            return array("erro" => true, "msg" => $requisicao["msg"]);
        } else {
            $retorno = json_decode($requisicao["data"], true);
            
            if(!isset($retorno["errors"])) {
                return array("erro" => false, "data" => $retorno);
            } else {
                return array("erro" => true, "msg" => $retorno["errors"]);
            }
        }
    }

    private function requisicao($token, $endpoint, $data, $request = "POST") {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->iugu_url.$endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $request,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic ".base64_encode($token.":"),                                                                      
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
}