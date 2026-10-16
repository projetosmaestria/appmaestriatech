<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Iugu_library
{
    //private $api_key = "VQFRC2OlxdOdOdFO"; //hom
    //private $api_secret = "RghiWnFU0vLGm3xHg9S3S2ho"; //hom
	
	//private $iugu_token = "4bc60189a2036e34abc611b1ecbe13ce"; //prod
    //private $iugu_acc_id = "EA47DC5FE049442C891AB1CCBF6E516F"; //prod
    private $iugu_url = "https://api.iugu.com/v1/";
    private $iugu_token = null;

    function criar_fatura($dados, $suffix = "P") {
        //$valores_entrada = $dados["valor_entrada"];
        
        $data = array(
            "email" => $dados["user"]->email,
            "due_date" => $dados["expires_at"],
            "ensure_workday_due_date" => false,
            /*"items" => array(
                array(
                    "description" => "Entrada",
                    "price_cents" => number_format($valores_entrada * 100, 0, "", ""),
                    "quantity" => 1
                )
            ),*/
            "return_url" => base_url(),
            "expired_url" => base_url(),
            //"notification_url" => BASE_URL."webhook.php",
            "fines" => false,
            "per_day_interest" => false,
            "discount_cents" => 0,
            "customer_id" => $dados["customer_id"],
            "ignore_due_email" => false,
            "payable_with" => $dados["payable_with"],
            "early_payment_discount" => false,
            "order_id" => $dados["cad_orca_id"]."_".$suffix
        );

        foreach ($dados["itens"] as $item) {
            $data["items"][] = array(
                "description" => $item["description"],
                "price_cents" => $item["price_cents"],
                "quantity" => $item["quantity"]
            );
        }

        if($dados["has_dependente"] == true) {
            $tel_celular_explode = explode(" ", $dados["pessoa_pagador"]->celular);
            $tel_celular_prefixo = str_replace("(", "", $tel_celular_explode[0]);
            $tel_celular_prefixo = str_replace(")", "", $tel_celular_prefixo);
            $tel_celular_numero = str_replace("-", "", $tel_celular_explode[1]);

            $data["payer"] = array(
                "cpf_cnpj" => $dados["pessoa_pagador"]->cpf,
                "name" => utf8_encode($dados["user_pagador"]->first_name." ".$dados["user_pagador"]->last_name),
                "phone" => $tel_celular_numero,
                "phone_prefix" => "0".$tel_celular_prefixo,
                "email" => $dados["user_pagador"]->email,
                "address" => array(
                    "zip_code" => $dados["pessoa_pagador"]->cep,
                    "number" => $dados["pessoa_pagador"]->numero,
                    "street" => utf8_encode($dados["pessoa_pagador"]->endereco),
                    "city" => utf8_encode($dados["pessoa_pagador"]->cidade),
                    "state" => $dados["pessoa_pagador"]->uf,
                    "district" => utf8_encode($dados["pessoa_pagador"]->bairro),
                    "complement" => utf8_encode($dados["pessoa_pagador"]->complemento)
                )
            );
        } else {
            $tel_celular_explode = explode(" ", $dados["pessoa"]->celular);
            $tel_celular_prefixo = str_replace("(", "", $tel_celular_explode[0]);
            $tel_celular_prefixo = str_replace(")", "", $tel_celular_prefixo);
            $tel_celular_numero = str_replace("-", "", $tel_celular_explode[1]);

            $data["payer"] = array(
                "cpf_cnpj" => $dados["pessoa"]->cpf,
                "name" => utf8_encode($dados["user"]->first_name." ".$dados["user"]->last_name),
                "phone" => $tel_celular_numero,
                "phone_prefix" => "0".$tel_celular_prefixo,
                "email" => $dados["user"]->email,
                "address" => array(
                    "zip_code" => $dados["pessoa"]->cep,
                    "number" => $dados["pessoa"]->numero,
                    "street" => utf8_encode($dados["pessoa"]->endereco),
                    "city" => utf8_encode($dados["pessoa"]->cidade),
                    "state" => $dados["pessoa"]->uf,
                    "district" => utf8_encode($dados["pessoa"]->bairro),
                    "complement" => utf8_encode($dados["pessoa"]->complemento)
                )
            );
        }

        $data["subscription_id"] = $dados["assinatura_id"];
        
        $requisicao = $this->requisicao("invoices", $data);

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
    
    function gerar_segunda_via($dados, $suffix = "M") {
        //$valores_entrada = $dados["valor_entrada"];
        //$expires_at = date('Y-m-d', strtotime("+".$dados["expiracao"]." days"));
		
        $data = array(
            "email" => $dados["user"]->email,
            "due_date" => $dados["expires_at"],
            "ensure_workday_due_date" => false,
            /*"items" => array(
                array(
                    "description" => "Entrada",
                    "price_cents" => number_format($valores_entrada * 100, 0, "", ""),
                    "quantity" => 1
                )
            ),*/
            "return_url" => base_url(),
            "expired_url" => base_url(),
            //"notification_url" => BASE_URL."webhook.php",
            "fines" => false,
            "per_day_interest" => false,
            "discount_cents" => 0,
            "customer_id" => $dados["customer_id"],
            "ignore_due_email" => false,
            "payable_with" => "bank_slip",
            "early_payment_discount" => false,
            "order_id" => $dados["cad_orca_id"]."_".$suffix
        );

        if($dados["has_dependente"] == true) {
            $tel_celular_explode = explode(" ", $dados["pessoa_pagador"]->celular);
            $tel_celular_prefixo = str_replace("(", "", $tel_celular_explode[0]);
            $tel_celular_prefixo = str_replace(")", "", $tel_celular_prefixo);
            $tel_celular_numero = str_replace("-", "", $tel_celular_explode[1]);

            $data["payer"] = array(
                "cpf_cnpj" => $dados["pessoa_pagador"]->cpf,
                "name" => utf8_encode($dados["user_pagador"]->first_name." ".$dados["user_pagador"]->last_name),
                "phone" => $tel_celular_numero,
                "phone_prefix" => "0".$tel_celular_prefixo,
                "email" => $dados["user_pagador"]->email,
                "address" => array(
                    "zip_code" => $dados["pessoa_pagador"]->cep,
                    "number" => $dados["pessoa_pagador"]->numero,
                    "street" => utf8_encode($dados["pessoa_pagador"]->endereco),
                    "city" => utf8_encode($dados["pessoa_pagador"]->cidade),
                    "state" => $dados["pessoa_pagador"]->uf,
                    "district" => utf8_encode($dados["pessoa_pagador"]->bairro),
                    "complement" => utf8_encode($dados["pessoa_pagador"]->complemento)
                )
            );
        } else {
            $tel_celular_explode = explode(" ", $dados["pessoa"]->celular);
            $tel_celular_prefixo = str_replace("(", "", $tel_celular_explode[0]);
            $tel_celular_prefixo = str_replace(")", "", $tel_celular_prefixo);
            $tel_celular_numero = str_replace("-", "", $tel_celular_explode[1]);

            $data["payer"] = array(
                "cpf_cnpj" => $dados["pessoa"]->cpf,
                "name" => utf8_encode($dados["user"]->first_name." ".$dados["user"]->last_name),
                "phone" => $tel_celular_numero,
                "phone_prefix" => "0".$tel_celular_prefixo,
                "email" => $dados["user"]->email,
                "address" => array(
                    "zip_code" => $dados["pessoa"]->cep,
                    "number" => $dados["pessoa"]->numero,
                    "street" => utf8_encode($dados["pessoa"]->endereco),
                    "city" => utf8_encode($dados["pessoa"]->cidade),
                    "state" => $dados["pessoa"]->uf,
                    "district" => utf8_encode($dados["pessoa"]->bairro),
                    "complement" => utf8_encode($dados["pessoa"]->complemento)
                )
            );
        }
        
        $requisicao = $this->requisicao("invoices/".$dados["invoice"]->iugu_id."/duplicate", $data);

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

    function criar_forma_pagamento($customer_id, $token) {
        $data = array(
            "token" => $token,
            "description" => "Cartão de Crédito",
            "set_as_default" => true
        );

        $requisicao = $this->requisicao("customers/".$customer_id."/payment_methods", $data);

        if ($requisicao["erro"] == true) {
            return array("erro" => true, "msg" => $requisicao["msg"]);
        } else {
            $retorno = json_decode($requisicao["data"], true);

            if(@$retorno["errors"] == false) {
                return array("erro" => false, "data" => $retorno);
            } else {
                $msg_err = "";
                if(is_array($retorno["errors"])) {
                    foreach ($retorno["errors"] as $key => $value) {
                        $msg_err .= $key.": ";
                        $k=0;
                        foreach ($value as $_v) {
                            if($k!=0)
                                $msg_err .= ", ";
                            $msg_err .= $_v;
                        }
                    }
                } else {
                    $msg_err .= $retorno["errors"];
                }
                return array("erro" => true, "msg" => $msg_err);
            }
        }
    }

    function buscar_forma_pagamento($customer_id, $iugu_id) {
        $requisicao = $this->requisicao("customers/".$customer_id."/payment_methods/".$iugu_id, array(), "GET");

        if ($requisicao["erro"] == true) {
            return array("erro" => true, "msg" => $requisicao["msg"]);
        } else {
            $retorno = json_decode($requisicao["data"], true);

            if(@$retorno["errors"] == false) {
                return array("erro" => false, "data" => $retorno);
            } else {
                $msg_err = "";
                if(is_array($retorno["errors"])) {
                    foreach ($retorno["errors"] as $key => $value) {
                        $msg_err .= $key.": ";
                        $k=0;
                        foreach ($value as $_v) {
                            if($k!=0)
                                $msg_err .= ", ";
                            $msg_err .= $_v;
                        }
                    }
                } else {
                    $msg_err .= $retorno["errors"];
                }
                return array("erro" => true, "msg" => $msg_err);
            }
        }
    }

    function buscar_fatura($iugu_id) {
        $requisicao = $this->requisicao("invoices/".$iugu_id, array(), "GET");

        if ($requisicao["erro"] == true) {
            return array("erro" => true, "msg" => $requisicao["msg"]);
        } else {
            $retorno = json_decode($requisicao["data"], true);

            if(@$retorno["errors"] == false) {
                return array("erro" => false, "data" => $retorno);
            } else {
                $msg_err = "";
                if(is_array($retorno["errors"])) {
                    foreach ($retorno["errors"] as $key => $value) {
                        $msg_err .= $key.": ";
                        $k=0;
                        foreach ($value as $_v) {
                            if($k!=0)
                                $msg_err .= ", ";
                            $msg_err .= $_v;
                        }
                    }
                } else {
                    $msg_err .= $retorno["errors"];
                }
                return array("erro" => true, "msg" => $msg_err);
            }
        }
    }
    
    function buscar_assinatura($iugu_id) {
        $requisicao = $this->requisicao("subscriptions/".$iugu_id, array(), "GET");

        if ($requisicao["erro"] == true) {
            return array("erro" => true, "msg" => $requisicao["msg"]);
        } else {
            $retorno = json_decode($requisicao["data"], true);

            if(@$retorno["errors"] == false) {
                return array("erro" => false, "data" => $retorno);
            } else {
                $msg_err = "";
                if(is_array($retorno["errors"])) {
                    foreach ($retorno["errors"] as $key => $value) {
                        $msg_err .= $key.": ";
                        $k=0;
                        foreach ($value as $_v) {
                            if($k!=0)
                                $msg_err .= ", ";
                            $msg_err .= $_v;
                        }
                    }
                } else {
                    $msg_err .= $retorno["errors"];
                }
                return array("erro" => true, "msg" => $msg_err);
            }
        }
    }

    function alterar_assinatura($post, $iugu_id) {
        $date = str_replace('/', '-', $post["expires_at"]);
        $date = date('Y-m-d', strtotime($date));

        $data = array();

        if(@isset($post["plano"]))
            $data["plan_identifier"] = $post["plano"];

        if(@isset($post["payable_with"]))
            $data["payable_with"] = ($post["payable_with"] == "bank_slip") ? $post["payable_with"] : "credit_card";

        if(@count($post["quantity"]) > 0) {
            $subitems = array();
            $i = 0;
            foreach ($post["quantity"] as $key => $value) {
                if(@$post["price"][$key] && @$post["quantity"][$key]) {
                    if(@$post["id"][$key])
                        $subitems[$i]["id"] = @$post["id"][$key];
                    $subitems[$i]["description"] = @$post["description"][$key];
                    $subitems[$i]["quantity"] = @$post["quantity"][$key];

                    if(str_replace("R$ ", "", $post["price"][$key]) == "" || str_replace("R$ ", "", $post["price"][$key]) == 0) {
                        if(@$post["condicao_desconto"])
                            $subitems[$i]["price_cents"] = -str_replace(".", "", $post["condicao_desconto"]);
                        else
                            $subitems[$i]["price_cents"] = 0;
                    } else {
                        $subitems[$i]["price_cents"] = str_replace(".", "", str_replace(",", "", str_replace("R$ ", "", $post["price"][$key])));
                    }
                    /*if(@$post["condicao_desconto"])
                        $subitems[$i]["price_cents"] = -str_replace(".", "", $post["condicao_desconto"]);
                    else
                        $subitems[$i]["price_cents"] = str_replace(".", "", str_replace(",", "", str_replace("R$ ", "", $post["price"][$key])));*/
                    $subitems[$i]["recurrent"] = (@$post["recurrent"][$key] == 1) ? "true" : "false";
                    $i++;
                }
            }
            if(@count($post["subitems_removidos"]) > 0) {
                foreach ($post["subitems_removidos"] as $value) {
                    $subitems[$i]["id"] = $value;
                    $subitems[$i]["_destroy"] = "true";
                    $i++;
                }
            }

            $data["subitems"] = $subitems;
        }

        if(@isset($post["suspended"]))
            $data["suspended"] = $post["suspended"];

        $data["expires_at"] = $date;
        $data["skip_charge"] = true;

        // exit("<pre>".print_r($data,true)."</pre>");
        $requisicao = $this->requisicao("subscriptions/".$iugu_id, $data, "PUT");

        if ($requisicao["erro"] == true) {
            return array("erro" => true, "msg" => $requisicao["msg"]);
        } else {
            $retorno = json_decode($requisicao["data"], true);

            if(@$retorno["errors"] == false) {
                return array("erro" => false, "data" => $retorno);
            } else {
                $msg_err = "";
                if(is_array($retorno["errors"])) {
                    foreach ($retorno["errors"] as $key => $value) {
                        $msg_err .= $key.": ";
                        $k=0;
                        foreach ($value as $_v) {
                            if($k!=0)
                                $msg_err .= ", ";
                            $msg_err .= $_v;
                        }
                    }
                } else {
                    $msg_err .= $retorno["errors"];
                }
                return array("erro" => true, "msg" => $msg_err);
            }
        }
    }

    function suspender_assinatura($iugu_id) {
        $data = array();

        $requisicao = $this->requisicao("subscriptions/".$iugu_id."/suspend", $data, "POST");

        if ($requisicao["erro"] == true) {
            return array("erro" => true, "msg" => $requisicao["msg"]);
        } else {
            $retorno = json_decode($requisicao["data"], true);

            if(@$retorno["errors"] == false) {
                return array("erro" => false, "data" => $retorno);
            } else {
                $msg_err = "";
                if(is_array($retorno["errors"])) {
                    foreach ($retorno["errors"] as $key => $value) {
                        $msg_err .= $key.": ";
                        $k=0;
                        foreach ($value as $_v) {
                            if($k!=0)
                                $msg_err .= ", ";
                            $msg_err .= $_v;
                        }
                    }
                } else {
                    $msg_err .= $retorno["errors"];
                }
                return array("erro" => true, "msg" => $msg_err);
            }
        }
    }
	
	function ativar_assinatura($iugu_id) {
        $data = array();

        $requisicao = $this->requisicao("subscriptions/".$iugu_id."/activate", $data, "POST");

        if ($requisicao["erro"] == true) {
            return array("erro" => true, "msg" => $requisicao["msg"]);
        } else {
            $retorno = json_decode($requisicao["data"], true);

            if(@$retorno["errors"] == false) {
                return array("erro" => false, "data" => $retorno);
            } else {
                $msg_err = "";
                if(is_array($retorno["errors"])) {
                    foreach ($retorno["errors"] as $key => $value) {
                        $msg_err .= $key.": ";
                        $k=0;
                        foreach ($value as $_v) {
                            if($k!=0)
                                $msg_err .= ", ";
                            $msg_err .= $_v;
                        }
                    }
                } else {
                    $msg_err .= $retorno["errors"];
                }
                return array("erro" => true, "msg" => $msg_err);
            }
        }
    }

    function listar_planos($id = null) {
        if($id == null)
            $requisicao = $this->requisicao("plans/", array(), "GET");
        else
            $requisicao = $this->requisicao("plans/identifier/".$id, array(), "GET");

        if ($requisicao["erro"] == true) {
            return array("erro" => true, "msg" => $requisicao["msg"]);
        } else {
            $retorno = json_decode($requisicao["data"], true);

            if(@$retorno["errors"] == false) {
                return array("erro" => false, "data" => $retorno);
            } else {
                $msg_err = "";
                if(is_array($retorno["errors"])) {
                    foreach ($retorno["errors"] as $key => $value) {
                        $msg_err .= $key.": ";
                        $k=0;
                        foreach ($value as $_v) {
                            if($k!=0)
                                $msg_err .= ", ";
                            $msg_err .= $_v;
                        }
                    }
                } else {
                    $msg_err .= $retorno["errors"];
                }
                return array("erro" => true, "msg" => $msg_err);
            }
        }
    }

    function setToken($token) {
        $this->iugu_token = $token;
    }

    private function requisicao($endpoint, $data, $request = "POST") {
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
}