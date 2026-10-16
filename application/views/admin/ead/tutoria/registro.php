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
                        <h3 class="box-title"><?php echo lang('tutoria_info'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_registro_tutoria')); ?>
                            <input type="hidden" name="redirect" value="<?php echo $redirect ?>">
                            <?php if($tipo == "evento"){ ?>
                            <div class="form-group">
                                <?php echo lang('agenda', 'agenda', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <div class="radio"><?php echo $evento_agenda->descricao ?></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('evento', 'evento', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <div class="radio"><?php echo $evento_agenda->titulo ?></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('orcamento', 'orcamento', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($orcamentos);?>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if($mostrar_datas) { ?>
                            <div class="form-group">
                                <?php echo lang('data_hora_inicio', 'data_hora_inicio', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='datetimepickerInicio'>
                                    <?php echo form_input(array('id'=>'datetimepickerInicio','name'=>'data_hora_inicio','value'=>@$data_hora_inicio,'class'=>'form-control date_range_filter date'));?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('data_hora_fim', 'data_hora_fim', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='datetimepickerFim'>
                                    <?php echo form_input(array('id'=>'datetimepickerFim','name'=>'data_hora_fim','value'=>'','class'=>'form-control date_range_filter date'));?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="form-group">
                                <?php echo lang('info_progresso', 'info_progresso', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($info_progresso);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('meta_porcentagem', 'meta_porcentagem', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_dropdown($meta_porcentagem);?>
                                </div>
                                <?php if($meta != "") { ?>
                                <div class="col-sm-9">
                                    <span><em>*</em><?php echo $meta;?></span>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <?php echo lang('analise_acesso', 'analise_acesso', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($analise_acesso);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('analise_aulas', 'analise_aulas', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($analise_aulas);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('qtd_atividades', 'qtd_atividades', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="qtd_atividades_sim"><input type="radio" id="qtd_atividades_sim" name="qtd_atividades" value="S" checked="checked" <?php echo ($qtd_atividades == "S") ? 'checked="checked"' : '' ?>><?php echo lang('qtd_atividades_sim') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="qtd_atividades_nao"><input type="radio" id="qtd_atividades_nao" name="qtd_atividades" value="N" <?php echo ($qtd_atividades == "N") ? 'checked="checked"' : '' ?>><?php echo lang('qtd_atividades_nao') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('analise_grupos', 'analise_grupos', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($analise_grupos);?>
                                </div>
                            </div>
                            <!--<div class="form-group">
                                <?php echo lang('desempenho_geral', 'desempenho_geral', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label style="padding-left: 0px !important;" for="higher"><input type="radio" id="higher" name="desempenho_geral" value="higher" class="iradio_flat-green" <?php echo ($desempenho_geral == "higher") ? "checked='checked'" : "" ?>><?php echo lang('higher') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label style="padding-left: 0px !important;" for="higher_medium"><input type="radio" id="higher_medium" name="desempenho_geral" value="higher_medium" class="iradio_flat-yellow" <?php echo ($desempenho_geral == "higher_medium") ? "checked='checked'" : "" ?>><?php echo lang('higher_medium') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label style="padding-left: 0px !important;" for="medium"><input type="radio" id="medium" name="desempenho_geral" value="medium" class="iradio_flat-orange" <?php echo ($desempenho_geral == "medium" || $desempenho_geral == "") ? "checked='checked'" : "" ?>><?php echo lang('medium') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label style="padding-left: 0px !important; margin-right:5px;" for="emerging"><input type="radio" id="emerging" name="desempenho_geral" value="emerging" class="iradio_flat-red" <?php echo ($desempenho_geral == "emerging") ? "checked='checked'" : "" ?>><?php echo lang('emerging') ?></label>
                                    </div>
                                </div>
                            </div>-->
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor($redirect, lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
        $('#datetimepickerInicio').datetimepicker({
            defaultDate: "<?php echo @$data_inicio ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY H:mm',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });
        $('#datetimepickerFim').datetimepicker({
            defaultDate: "<?php echo @$data_fim ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY H:mm',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });

        $('.iradio_flat-red').iCheck({
            checkboxClass: 'icheckbox_flat-red',
            radioClass: 'iradio_flat-red'
        });
        $('.iradio_flat-orange').iCheck({
            checkboxClass: 'icheckbox_flat-orange',
            radioClass: 'iradio_flat-orange'
        });
        $('.iradio_flat-yellow').iCheck({
            checkboxClass: 'icheckbox_flat-yellow',
            radioClass: 'iradio_flat-yellow'
        });
        $('.iradio_flat-green').iCheck({
            checkboxClass: 'icheckbox_flat-green',
            radioClass: 'iradio_flat-green'
        });
    }
</script>