<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('admin/niveis/_form', array(
    'form_id' => 'form-create_niveis',
    'titulo_formulario' => lang('niveis_create'),
    'texto_botao' => 'Criar nível',
));
