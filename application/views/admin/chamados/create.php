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
                        <h3 class="box-title"><?php echo lang('chamados_create'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_chamados')); ?>
                            <input type="hidden" name="base_url" id="base_url" value="<?php echo base_url() ?>">

                            <?php if($is_full_create == true) { ?>
                                <div class="form-group" style="display: none;">
                                    <?php echo lang('chamados_quadro', 'quadro', array('class' => 'col-sm-2 control-label'), false); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_dropdown($quadro);?>
                                    </div>
                                </div>
                            <?php } ?>
                            
                            <div class="form-group">
                                <?php echo lang('chamados_referencia', 'referencia', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($referencia);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('chamados_unidade', 'unidade', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($unidade);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('chamados_produto', 'produto', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($produto);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('chamados_modulo', 'modulo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($modulo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('chamados_categoria', 'categoria', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($categoria);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('chamados_tipo_servico', 'tipo_servico', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($tipo_servico);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('chamados_nivel', 'nivel', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($nivel);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('chamados_titulo', 'titulo', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($titulo);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('chamados_desc', 'desc', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($desc);?>
                                </div>
                            </div>
                            <?php if($is_full_create == true) { ?>
                            <div class="form-group">
                                <?php echo lang('chamados_prioridade', 'prioridade', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($prioridade);?>
                                </div>
                            </div>
                            <?php if(@$dono): ?>
                                <div class="form-group">
                                    <?php echo lang('chamados_dono', 'dono', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_dropdown($dono);?>
                                    </div>
                                </div>
                            <?php endif ?>
                            <div class="form-group">
                                <?php echo lang('chamados_responsavel', 'responsavel', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($responsavel);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('chamados_desenvolvedor', 'desenvolvedor', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($desenvolvedor);?>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="form-group">
                                <?php echo lang('chamados_interessados', 'interessados', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($interessados);?>
                                </div>
                            </div>                            
                            <div class="form-group">
                                <?php echo lang('plano_acao', 'plano_acao', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($plano_acao);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('chamados', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
        $('.multipleSelect').fastselect();
    };
</script>