<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('admin/niveis/_form', array(
    'form_id' => 'form-edit_niveis',
    'titulo_formulario' => lang('niveis_edit_group') . ' <small>' . html_escape($nivel->descricao) . '</small>',
    'texto_botao' => 'Salvar alterações',
));
