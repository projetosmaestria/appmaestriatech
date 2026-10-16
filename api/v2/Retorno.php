<?php

class Retorno{

    public $codigo;
    public $mensagem;
    public $data;
    public $erro;

    function getCodigo() {
        return $this->codigo;
    }

    function getMensagem() {
        return $this->mensagem;
    }

    function getErro() {
        return $this->erro;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setMensagem($mensagem) {
        $this->mensagem = utf8_encode($mensagem);
    }

    function setData($data) {
        $this->data = $data;
    }

    function setErro($erro) {
        $this->erro = $erro;
    }
    
    function toString(){
        $jsonRetorno = array();
        $json = array("codigo" => $this->codigo, "erro" => $this->erro, "mensagem" => $this->mensagem);
        array_push($jsonRetorno, $json);
        
        return json_encode($jsonRetorno);
    }

    public function __toString() {
        $jsonRetorno = array();
        $json = array("codigo" => $this->codigo, "erro" => $this->erro, "mensagem" => $this->mensagem);
        array_push($jsonRetorno, $json);
        
        return json_encode($jsonRetorno);
    }
}