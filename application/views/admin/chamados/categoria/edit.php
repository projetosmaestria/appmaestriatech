<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('admin/chamados/categoria/_form', array(
    'form_id' => 'form-edit_chamados_categoria',
    'titulo_formulario' => 'Editar categoria <small>#' . (int) $categoria->id . '</small>',
    'texto_botao' => 'Salvar alterações',
));
