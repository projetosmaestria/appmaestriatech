<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('aluno_livro'); ?> de <?php echo $aluno->first_name." ".$aluno->last_name ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if(@count($livros > 0)) : ?>
                            <?php echo form_open(current_url(), array('class' => '', 'id' => 'form-alunos_dispositivos_resplivros')); ?>
                                <input type="hidden" name="aluno_id" value="<?php echo $aluno->id ?>">
                                <div class="form-group">
                                    <?php foreach ($livros as $livro) : ?>
                                        <ul class="list-group">
                                            <li class="list-group-item">
                                                <div class="row">
                                                    <div class="checkbox col-md-12">
                                                        <label for="livro-<?php echo $livro->id ?>">
                                                            <input id="livro-<?php echo $livro->id ?>" type="checkbox" name="livro[]" value="<?php echo $livro->id ?>">Selecionar
                                                        </label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>livro: </strong> <label>#</label><?php echo $livro->titulo ?> 
                                                    </div>
                                                    <div class="col-md-4">
                                                        <strong>qrcode:</strong> <?php echo $livro->qrcode ?> 
                                                    </div>
                                                    <div class="col-md-2">
                                                        <strong>cadastro em:</strong> <?php echo date("d/m/Y", strtotime($livro->data_cadastro)) ?>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul> 
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-group">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-danger btn-flat', 'content' => 'Limpar respostas', 'name' => 'limpar_respostas', 'value' => '1')); ?>
                                    </div>
                                </div>
                            <?php echo form_close();?>
                        <?php endif; ?>
                    </div>
                </div>
             </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('app_alunos_dispositivos'); ?> de <?php echo $aluno->first_name." ".$aluno->last_name ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if(count($dispositivos > 0)) : ?>
                            <?php echo form_open(current_url(), array('class' => '', 'id' => 'form-alunos_dispositivos')); ?>
                                <div class="form-group">
                                    <?php foreach ($dispositivos as $disp) : ?>
                                        <input type="hidden" name="dispositivos[]" id="hidden-dispositivo-<?php echo $disp->id ?>" value="<?php echo $disp->id ?>">
                                        <ul class="list-group" id="ul-dispositivo-<?php echo $disp->id ?>">
                                            <li class="list-group-item">
                                                <div class="row">
                                                    <?php echo form_button(array('type' => 'button', 'class' => 'btn btn-danger btn-lista', 'style' => 'float: right;margin-right: 10px;', 'content' => '<i class="fa fa-times" aria-hidden="true"></i>', 'onclick' => "remove_dispositivo('".$disp->id."')")); ?>
                                                    <div class="col-md-3">
                                                        <strong>device id: </strong> <label>#</label><?php echo $disp->device_id ?> 
                                                    </div>
                                                    <div class="col-md-4">
                                                        <strong>device info:</strong> <?php echo $disp->device_info ?> 
                                                    </div>
                                                    <div class="col-md-2">
                                                        <strong>criado em:</strong> <?php echo date("d/m/Y", strtotime($disp->token_criado_em)) ?>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <strong>expira em:</strong> <?php echo date("d/m/Y", strtotime($disp->token_expira_em)) ?>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul> 
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-group">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'name' => 'limpar_dispositivos', 'value' => '1', 'content' => lang('actions_submit'))); ?>
                                        <?php echo anchor('app_alunos', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            <?php echo form_close();?>
                        <?php endif; ?>
                    </div>
                </div>
             </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('historico_alunos_dispositivos'); ?> de <?php echo $aluno->first_name." ".$aluno->last_name ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if(count($historico > 0)) : ?>
                            <div class="form-group">
                                <?php foreach ($historico as $disp) : ?>
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>device id: </strong> <?php echo $disp->device_id ?> 
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>device info:</strong> <?php echo $disp->device_info ?> 
                                                </div>
                                                <div class="col-md-2">
                                                    <strong>liberado em:</strong> <?php echo date("d/m/Y", strtotime($disp->liberado_em)) ?>
                                                </div>
                                            </div>
                                        </li>
                                    </ul> 
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>
