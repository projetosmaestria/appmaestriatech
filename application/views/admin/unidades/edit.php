<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?>

<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if(isset($message)) {echo $message;} ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('unidades_edit'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-edit_unidade')); ?>
                            <h5 class="box-title"><?php echo lang('unidades_dados_unidade'); ?></h5>
                            <hr>
                            <div class="form-group">
                                <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="status_ativo"><input type="radio" id="status_ativo" name="status" value="s"
                                        <?php
                                            echo ($status == "s" || $status == "")?' checked="checked"':'';
                                        ?>
                                        ><?php echo lang('status_ativo') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="status_inativo"><input type="radio" id="status_inativo" name="status" value="n"
                                        <?php
                                            echo ($status == "n")?' checked="checked"':'';
                                        ?>
                                        ><?php echo lang('status_inativo') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('tipo', 'tipo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="tipo_j"><input type="radio" id="tipo_j" name="tipo" value="J"
                                        <?php
                                            echo ($tipo == "J" || $tipo == "")?' checked="checked"':'';
                                        ?>
                                        ><?php echo lang('tipo_j') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="tipo_f"><input type="radio" id="tipo_f" name="tipo" value="F"
                                        <?php
                                            echo ($tipo == "F")?' checked="checked"':'';
                                        ?>
                                        ><?php echo lang('tipo_f') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('caracteristica', 'caracteristica', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-2">
                                    <?php echo form_dropdown($caracteristica);?>
                                </div>
                            </div>
                            <div id="div-tipo-j" <?php echo ($tipo == "J" || $tipo == "")?'':'style="display:none;"' ?>>
                                <div class="form-group">
                                    <?php echo lang('cnpj', 'cnpj', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-2">
                                        <?php echo form_input($cnpj);?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo lang('razao_social', 'razao_social', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_input($razao_social);?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo lang('fantasia', 'fantasia', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_input($fantasia);?>
                                    </div>
                                </div>
                            </div>
                        
                            <div id="div-tipo-f" <?php echo ($tipo == "F")?'':'style="display:none;"' ?>>
                                <div class="form-group">
                                    <?php echo lang('cpf', 'cpf', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-2">
                                        <?php echo form_input($cpf);?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo lang('rg', 'rg', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-2">
                                        <?php echo form_input($rg);?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo lang('nome', 'nome', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_input($nome);?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('gestor', 'gestor', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($gestor);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('responsavel', 'responsavel', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($responsavel);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('telefone1', 'telefone1', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($telefone1);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('telefone2', 'telefone2', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($telefone2);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('email', 'email', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($email);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('site', 'site', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($site);?>
                                </div>
                            </div>
                            <br>
                            <h5 class="box-title"><?php echo lang('unidades_endereco'); ?></h5>
                            <hr>
                            <div class="form-group">
                                <?php echo lang('cep', 'cep', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($cep);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('logradouro', 'logradouro', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($logradouro);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('numero', 'numero', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($numero);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('bairro', 'bairro', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($bairro);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('cidade', 'cidade', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($cidade);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('uf', 'uf', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2">
                                    <?php echo form_dropdown($uf);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('unidades', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            </div>
                        <?php echo form_close();?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>