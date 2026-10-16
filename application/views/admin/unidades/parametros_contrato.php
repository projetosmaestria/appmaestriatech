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
                <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-parametros_contrato')); ?>
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo lang('unidades_parametros_contrato'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <?php echo lang('matricula_aluno', 'matricula_aluno', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($matricula_aluno);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('pagamentro_matricula', 'pagamentro_matricula', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($pagamentro_matricula);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('primeira_presenca', 'primeira_presenca', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($primeira_presenca);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('sem_presenca', 'sem_presenca', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($sem_presenca);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('cancelamento_contrato', 'cancelamento_contrato', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($cancelamento_contrato);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('anulado', 'anulado', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($anulado);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('resgate_financeiro', 'resgate_financeiro', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($resgate_financeiro);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('resgate_pedagogico', 'resgate_pedagogico', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($resgate_pedagogico);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('concluido', 'concluido', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($concluido);?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo lang('unidades_parametros_pauta'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <?php echo lang('matricula_aluno_pauta', 'matricula_aluno_pauta', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($matricula_aluno_pauta);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('num_faltas', 'num_faltas', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($num_faltas);?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo lang('unidades_parametros_turma'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <?php echo lang('inicio_turma', 'inicio_turma', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($inicio_turma);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('lancamento_pauta_inicial', 'lancamento_pauta_inicial', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($lancamento_pauta_inicial);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('lancamento_pauta_final', 'lancamento_pauta_final', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($lancamento_pauta_final);?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo lang('unidades_parametros_cron'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <?php echo lang('matricula_desistencia', 'matricula_desistencia', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($matricula_desistencia);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('dias_desistencia', 'dias_desistencia', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($dias_desistencia);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('matricula_nunca_acessou', 'matricula_nunca_acessou', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($matricula_nunca_acessou);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('dias_nunca_acessou', 'dias_nunca_acessou', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($dias_nunca_acessou);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('status_historico', 'status_historico', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($status_historico);?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="btn-group">
                            <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                            <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                            <?php echo anchor('unidades', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                        </div>
                    </div>
                <?php echo form_close();?>
             </div>
        </div>
    </section>
</div>