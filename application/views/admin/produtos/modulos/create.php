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
                        <h3 class="box-title"><?php echo lang('produtos_modulos_create'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_produto_modulos')); ?>
                            <div class="form-group">
                                <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="status_ativo"><input type="radio" id="status_ativo" name="status" value="S"
                                        <?php
                                            echo ($status == "S" || $status == "")?' checked="checked"':'';
                                        ?>
                                        ><?php echo lang('status_ativo') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="status_inativo"><input type="radio" id="status_inativo" name="status" value="N"
                                        <?php
                                            echo ($status == "N")?' checked="checked"':'';
                                        ?>
                                        ><?php echo lang('status_inativo') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('produto', 'produto', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($produto);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('nome', 'nome', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($nome);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('valor_licenca', 'valor_licenca', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($valor_licenca);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('valor_manutencao', 'valor_manutencao', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($valor_manutencao);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('valor_implantacao', 'valor_implantacao', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($valor_implantacao);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('descricao', 'descricao', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($descricao);?>
                                </div>
                            </div> 
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('produtos_modulos', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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

<script type="text/javascript">
    window.onload = function() {
        $("#valor_licenca").maskMoney({prefix:'', allowNegative: true, thousands:'.', decimal:',', affixesStay: true});
        $("#valor_manutencao").maskMoney({prefix:'', allowNegative: true, thousands:'.', decimal:',', affixesStay: true});
        $("#valor_implantacao").maskMoney({prefix:'', allowNegative: true, thousands:'.', decimal:',', affixesStay: true});
    };
</script>