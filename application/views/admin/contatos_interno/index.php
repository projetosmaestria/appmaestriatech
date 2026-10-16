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
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group">
                                <?php echo lang('filtro_inicio_label', 'filtro_inicio_label', array('class' => 'col-sm-1 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='data_filtro_inicio'>
                                    <?php echo form_input(array('id'=>'data_filtro_inicio','name'=>'data_filtro_inicio','value'=>'','class'=>'form-control date_range_filter date'));?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <?php echo lang('filtro_fim_label', 'filtro_fim_label', array('class' => 'col-sm-1 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='data_filtro_fim'>
                                    <?php echo form_input(array('id'=>'data_filtro_fim','name'=>'data_filtro_fim','value'=>'','class'=>'form-control date_range_filter date'));?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row col-sm-12">
                            <table id="datatable2" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="col-md-2"><?php echo lang('de');?></th>
                                        <th class="col-md-2"><?php echo lang('para');?></th>
                                        <th class="col-md-2"><?php echo lang('assunto');?></th>
                                        <th class="col-md-4"><?php echo lang('mensagem');?></th>
                                        <th class="col-md-1"><?php echo lang('visualizado');?></th>
                                        <th class="col-md-2"><?php echo lang('data');?></th>
                                        <th class="col-md-1"><?php echo lang('acao');?></th>
                                    </tr>
                                </thead>
                                <tbody>
    <?php foreach ($contatos as $values):?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($values->first_name." ".$values->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->first_name_para." ".$values->last_name_para, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($values->assunto, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(mb_strimwidth($values->mensagem, 0, 100, "..."), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php 
                                            if($values->visualizada == "N")
                                                echo "Não";
                                            else
                                                echo "Sim";
                                        ?></td>
                                        <td><?php echo htmlspecialchars(date("d/m/Y", strtotime($values->created_at)), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php echo anchor("contatos_interno/ver/".$values->id, '<i class="fa fa-eye"></i>', array('class'=>'btn btn-info btn-lista', 'title'=>lang('view'))); ?>                                            
                                        </td>
                                    </tr>
    <?php endforeach;?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>