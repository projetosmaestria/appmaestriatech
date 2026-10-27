<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('admin/chamados/categoria/_form', array(
    'form_id' => 'form-create_chamados_categoria',
    'titulo_formulario' => lang('chamados_categoria_create'),
    'texto_botao' => 'Criar categoria',
));
