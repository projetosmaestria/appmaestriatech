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
                        <h3 class="box-title"><?php echo $periodico->nomePeriodico ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php echo lang('filial', 'filial', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-10">
                                <?php echo $periodico->unidade ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('descricao', 'descricao', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-10">
                                <?php echo $periodico->nomePeriodico ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-10">
                                <?php echo ($periodico->status == '1') ? '<span class="label label-success">'.lang('actions_ativo').'</span>' : '<span class="label label-default">'.lang('actions_inativo').'</span>' ?>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </div>

    <?php if($periodico->iugu_status == 'S') { ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-view_periodico')); ?>
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('vincular_plano_iugu'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <?php 
                                foreach ($planos_iugu as $plano) {
                                    echo '<div class="col-sm-4"><div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="planos[]" value="'.$plano['identifier'].'"';

                                            if(in_array($plano["identifier"], $planos_check))
                                                echo ' checked ';

                                            echo '>
                                            <b>Código:</b> '.$plano['id'].'<br>
                                            <b>Nome:</b> '.$plano['name'].'<br>
                                            <b>Intervalo:</b> '.$plano['interval'].'<br>
                                            <b>Tipo intervalo:</b> '.$plano['interval_type'].'<br>
                                            <b>Pagamento:</b> '.$plano['payable_with'].'<br>';
                                            
                                            if(count($plano['prices']) > 0) {
                                                echo '<b>Preço(s):</b><br>';
                                                foreach ($plano['prices'] as $preco) {
                                                    echo "R$ " . number_format($preco["value_cents"] / 100, 2, ',', '.')."<br>";
                                                }
                                            }
                                    echo '
                                        </label>
                                  </div><hr></div>';
                                }
                            ?>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="btn-group">
                            <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                            <?php echo anchor('periodico', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
                            <?php echo anchor('periodico', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    <?php } ?>

    </section>
</div>