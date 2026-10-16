<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?>

<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if($this->session->flashdata('message') != "") {echo $this->session->flashdata('message');} ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-header with-border">
                        <?php $tipo_unidade = ($unidade->tipo == "C") ? "Complementar" : "Obrigatória"; ?>
                        <h3 class="box-title"><i class="fa fa-paperclip" aria-hidden="true"></i>&nbsp; <?php echo $unidade->unidade.') '.$unidade->titulo.' - '.$tipo_unidade; ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'formCorrigirRespostas', 'name' => 'formCorrigirRespostas')); ?>
                            <input hidden="hidden" id="aluno_id" name="aluno_id" value="<?php echo $aluno->user_id; ?>">
                            <input hidden="hidden" id="unidade_id" name="unidade_id" value="<?php echo $unidade->id; ?>">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-sm-1">
                                            Aluno:
                                        </div>
                                        <div class="col-sm-5">
                                            <?php echo $aluno->first_name." ".$aluno->last_name; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <?php echo $html ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor(base_url('app_respostas/'.$referer), lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>