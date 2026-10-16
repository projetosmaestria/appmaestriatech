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
                        <h3 class="box-title"><?php echo lang('app_turmas_create'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_app_turmas')); ?>
                            <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
                            <div class="form-group">
                                <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="status_ativo"><input type="radio" id="status_ativo" name="status" value="1" checked="checked"><?php echo lang('actions_ativo') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="status_inativo"><input type="radio" id="status_inativo" name="status" value="0"><?php echo lang('actions_inativo') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('permitir_matricula', 'permitir_matricula', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="permitir_matricula_sim"><input type="radio" id="permitir_matricula_sim" name="permitir_matricula" value="S" checked="checked"><?php echo lang('actions_yes') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="permitir_matricula_nao"><input type="radio" id="permitir_matricula_nao" name="permitir_matricula" value="N"><?php echo lang('actions_no') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('unidade', 'unidade', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($unidade);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('curso', 'curso', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($curso);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('livro', 'livro', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($livro);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('descricao', 'descricao', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($descricao);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('obs', 'obs', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($obs);?>
                                </div>
                            </div>      
                            <div class="form-group">
                                <?php echo lang('capacidade', 'capacidade', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($capacidade);?>
                                </div>
                            </div>                      
                            <div class="form-group">
                                <?php echo lang('data_inicio', 'data_inicio', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='datetimepicker'>
                                    <?php echo form_input($data_inicio);?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>                  
                            <div class="form-group">
                                <?php echo lang('data_termino', 'data_termino', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='datetimepicker2'>
                                    <?php echo form_input($data_termino);?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('dias_semana', 'dias_semana', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_button(array('type' => 'button', 'class' => 'btn btn-info btn-flat adiciona-dia', 'content' => '<i class="fa fa-plus-square" aria-hidden="true"></i> '.lang('actions_add_dia_semana'))); ?>
                                    <?php if(@count($horarios) > 0) { $i=0; ?>
                                        <?php foreach($horarios as $horario) { $i++; ?>
                                            <div class="row dia_semana" style="margin-top:5px;">
                                                <input type="hidden" id="turma_horario_id<?php echo $i ?>" name="turma_horario_id[]">
                                                <div class="col-md-3">
                                                    <select class="form-control" id="turma_horario_professor<?php echo $i ?>" name="turma_horario_professor[]">
                                                        <option value=''>Selecione o Professor</option>
                                                        <?php 
                                                            foreach ($professores->result() as $prof) {
                                                                echo "<option value='".$prof->id."'";
                                                                if($prof->id == $horario["professor"])
                                                                    echo " selected";
                                                                echo ">".$prof->first_name." ".$prof->last_name."</option>";
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <select class="form-control" id="turma_horario_dia<?php echo $i ?>" name="turma_horario_dia[]">
                                                        <option value="">Selecione um Dia</option>
                                                        <option value="DOMINGO" <?php echo ($horario["dia"] == "DOMINGO")?'selected':'' ?>>DOMINGO</option>
                                                        <option value="SEGUNDA-FEIRA" <?php echo ($horario["dia"] == "SEGUNDA-FEIRA")?'selected':'' ?>>SEGUNDA-FEIRA</option>
                                                        <option value="TERCA-FEIRA" <?php echo ($horario["dia"] == "TERCA-FEIRA")?'selected':'' ?>>TERCA-FEIRA</option>
                                                        <option value="QUARTA-FEIRA" <?php echo ($horario["dia"] == "QUARTA-FEIRA")?'selected':'' ?>>QUARTA-FEIRA</option>
                                                        <option value="QUINTA-FEIRA" <?php echo ($horario["dia"] == "QUINTA-FEIRA")?'selected':'' ?>>QUINTA-FEIRA</option>
                                                        <option value="SEXTA-FEIRA" <?php echo ($horario["dia"] == "SEXTA-FEIRA")?'selected':'' ?>>SEXTA-FEIRA</option>
                                                        <option value="SABADO" <?php echo ($horario["dia"] == "SABADO")?'selected':'' ?>>SABADO</option>
                                                    </select>
                                                </div>
                                                <?php 
                                                    $hora_inicial = explode(":", $horario["inicio"]);
                                                    $hora_final = explode(":", $horario["fim"]);
                                                ?>
                                                <div class="col-md-2"><input type="time" class="form-control" id="turma_horario_inicio<?php echo $i ?>" name="turma_horario_inicio[]" value="<?php echo @$hora_inicial[0].":".@$hora_inicial[1] ?>"></div>
                                                <div class="col-md-2"><input type="time" class="form-control" id="turma_horario_fim<?php echo $i ?>" name="turma_horario_fim[]" value="<?php echo @$hora_final[0].":".@$hora_final[1] ?>"></div>
                                                <div class="col-md-2">
                                                    <a href="#" class="btn btn-danger btn-xs remove-dia">Remover</a>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <div class="dia_semana"></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('app_turmas', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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