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
                        <h3 class="box-title"><?php echo $plano['name'] ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php echo lang('filial', 'filial', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-10">
                                <?php echo $plano['unidade'] ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('codigo', 'codigo', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-10">
                                <?php echo $plano['id'] ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('identificador', 'identificador', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-10">
                                <?php echo $plano['identifier'] ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('nome', 'nome', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-10">
                                <?php echo $plano['name'] ?>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </div>

    <?php if($periodicos->num_rows() > 0) { ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <?php echo form_open(current_url(), array('class' => '', 'id' => 'form-view_periodico')); ?>
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('vincular_plano_iugu'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <?php echo lang('vendedor', 'vendedor', array('class' => 'control-label'), false); ?>
                                    <?php echo form_dropdown($vendedor);?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php echo lang('plano_financeiro', 'plano_financeiro', array('class' => 'control-label'), false); ?>
                                    <?php echo form_input($plano_financeiro);?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php echo lang('centro_custo', 'centro_custo', array('class' => 'control-label'), false); ?>
                                    <?php echo form_input($centro_custo);?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <?php 
                                        foreach ($periodicos->result_array() as $periodico) {
                                            echo '<div class="col-md-6 col-sm-12"><div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="periodicos[]" value="'.$periodico['id'].'"';

                                                    if(in_array($periodico["id"], $periodicos_check))
                                                        echo ' checked ';

                                                    echo '>
                                                    <b>Nome:</b> '.$periodico['nomePeriodico'].'<br>
                                                    <b>Periodicidade:</b> '.$periodico['periodicidade'];
                                                    
                                            echo '
                                                </label>
                                          </div><hr></div>';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="btn-group">
                            <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                            <?php echo anchor('iugu/planos', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
                            <?php echo anchor('iugu/planos', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    <?php } ?>

    </section>
</div>