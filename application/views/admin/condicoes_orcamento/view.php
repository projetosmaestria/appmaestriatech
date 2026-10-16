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
                        <h3 class="box-title"><?php echo lang('condicoes_view'); ?> #<?php echo $condicao->ID ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php echo lang('id', 'id', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo $condicao->ID ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('filial', 'filial', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo $condicao->unidade ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('descricao', 'descricao', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo $condicao->DESCRICAO ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('material_didatico', 'material_didatico', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo (strtoupper($condicao->material_didatico) == "TRUE") ? "Sim" : "Não" ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('entrada_matricula', 'entrada_matricula', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo (strtoupper($condicao->entrada_matricula) == "TRUE") ? "Sim" : "Não" ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('forma_pag', 'forma_pag', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo $condicao->forma_pag ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('forma_receb', 'forma_receb', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo $condicao->FormaReceb ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('tipo_receb', 'tipo_receb', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo $condicao->tipo_receb ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('tipo_valor_desc_pontualidade', 'tipo_valor_desc_pontualidade', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo $condicao->TIPOVALORDESCONTOPONTUALIDADE ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('valor_desc_pontualidade', 'valor_desc_pontualidade', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo $condicao->VALOR_DESCONTO_PONTUALIDADE ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('carencia_desc_pontualidade', 'carencia_desc_pontualidade', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo $condicao->CARENCIA_DESCONTO_PONTUALIDADE ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('plano_financeiro', 'plano_financeiro', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo $condicao->Id_planofin ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo ($condicao->ATIVO == '1') ? '<span class="label label-success">'.lang('actions_ativo').'</span>' : '<span class="label label-default">'.lang('actions_inativo').'</span>' ?>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </div>

    <?php if($condicao->iugu_status == 'S') { ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-view_condicoes_orcamento')); ?>
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('vincular_plano_iugu'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <?php echo lang('plano', 'plano', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-10">
                                <?php echo form_dropdown($plano_iugu);?>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="btn-group">
                            <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                            <?php echo anchor('condicoes_orcamento', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                        </div>
                    </div>
                    <?php echo form_close();?>
                </div>
             </div>
        </div>
    <?php } else { ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-body">
                        <div class="btn-group">
                            <?php echo anchor('condicoes_orcamento', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    <?php } ?>

    </section>
</div>