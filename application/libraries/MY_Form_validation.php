<?php
class MY_Form_validation extends CI_Form_validation{    
    function __construct($config = array()){
          parent::__construct($config);
    }

    function validar_cnpj($str)
    {
        if(validar_cnpj($str)) {
            return true;
        } else {
            //$this->form_validation->set_message('_validar_cnpj', lang('create_validation_validar_cnpj'));
            return false;
        }
    }

    function validar_cpf($str)
    {
        if(validar_cpf($str)) {
            return true;
        } else {
            //$this->form_validation->set_message('_validar_cpf', lang('create_validation_validar_cpf'));
            return false;
        }
    }

    function validar_data($date){
        $day = (int) substr($date, 0, 2);
        $month = (int) substr($date, 3, 2);
        $year = (int) substr($date, 6, 4);
        return checkdate($month, $day, $year);
    }

    function is_money($str) {
        $str = str_replace(".", "", $str);
        $str = str_replace(",", ".", $str);
        if(!parent::decimal($str))
        {
            return (bool) parent::integer($str);
        }
        return TRUE;
    }

    function validar_decimal($str) {
        $str = str_replace(",", ".", $str);
        if ( is_numeric($str) ) {
            // $this->form_validation->set_message('weight_check', 'The {field} field must be number or decimal.');
            return TRUE;
        } else {
            return FALSE;
        }
    }
	
	function is_password_strong($password) {
        if(!preg_match('/[a-z]/', $password)) {
            $this->set_message('is_password_strong', 'Digite pelo menos uma letra minúscula na senha.');
            return false;
        }
        if(!preg_match('/[A-Z]/', $password)) {
            $this->set_message('is_password_strong', 'Digite pelo menos uma letra maiúscula na senha.');
            return false;
        }

        if(!preg_match('/[0-9]/', $password)) {
            $this->set_message('is_password_strong', 'Digite pelo menos um número na senha.');
            return false;
        }

        if(!preg_match('/[!@#$&*]/', $password)) {
            $this->set_message('is_password_strong', 'Digite pelo menos um caractere especial (!, @, #, %, &, *) na senha.');
            return false;
        }

        return true;

    }
}