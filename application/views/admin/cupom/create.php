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
                        <h3 class="box-title"><?php echo lang('cupom_create'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_cupom')); ?>
                            <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
                            <?php if($can_alterar_vendedor == true) { ?>
                            <div class="form-group">
                                <?php echo lang('vendedor', 'vendedor', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($vendedor);?>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="form-group">
                                <?php echo lang('unidades', 'unidades', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($unidades);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('cursos', 'cursos', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($cursos);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('turmas', 'turmas', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($turmas);?>
                                </div>
                            </div>
                            <?php if($can_alterar_data_expiracao == true) { ?>
                            <div class="form-group">
                                <?php echo lang('data_expiracao', 'data_expiracao', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='datetimehourpickerCupom'>
                                    <?php echo form_input(array('id'=>'datetimehourpickerCupom','name'=>'data_expiracao','value'=>'','class'=>'form-control date_range_filter date'));?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if($can_isentar_matricula == true) { ?>
                            <div class="form-group">
                                <?php echo lang('isento_matricula', 'isento_matricula', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_checkbox($isento_matricula);?>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="form-group tipo_entrada" <?php echo ($isento_matricula_checked) ? 'style="display:none;"' : '' ?>>
                                <?php echo lang('tipo_entrada', 'tipo_entrada', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($tipo_entrada);?>
                                </div>
                            </div>
                            <div class="form-group entrada" <?php echo ($isento_matricula_checked) ? 'style="display:none;"' : '' ?>>
                                <?php echo lang('entrada', 'entrada', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php if($entradas["count"] == 0) { ?>
                                        <?php if($entradas["html"] == "") { ?>
                                            <div class="msg-entradas"><p class="control-label" style="text-align: left !important;">Não há entradas disponíveis para o vendedor e unidade selecionada.</p></div>
                                        <?php } else { ?>
                                            <div class="msg-entradas"><p class="control-label" style="text-align: left !important;">Selecione uma unidade acima.</p></div>
                                        <?php } ?>
                                        <div class="list-entradas"></div>
                                    <?php } else { ?>
                                        <div class="msg-entradas"></div>
                                        <div class="list-entradas">
                                            <?php echo $entradas["html"]; ?>
                                        </div>
                                    <?php } ?>
                                    <div class="loading" style="display:none;">
                                      <i class="fa fa-refresh fa-spin" style="font-size: 30px;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('sequencial', 'sequencial', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_checkbox($sequencial);?>
                                </div>
                            </div>
                            <div class="form-group data_inicio_sequencial" <?php echo ($sequencial_checked == 1) ? '' : 'style="display:none;"' ?>>
                                <?php echo lang('data_inicio_sequencial', 'data_inicio_sequencial', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2 input-group date" id='datetimehourpickerSequencia'>
                                    <?php echo form_input(array('id'=>'datetimehourpickerSequencia','name'=>'data_inicio_sequencial','value'=>'','class'=>'form-control date_range_filter date'));?>
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('condicoes', 'condicoes', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php if($condicoes["count"] == 0) { ?>
                                        <?php if($condicoes["html"] == "") { ?>
                                            <div class="msg-condicoes"><p class="control-label" style="text-align: left !important;">Não há condições disponíveis para o vendedor e unidade selecionada.</p></div>
                                        <?php } else { ?>
                                            <div class="msg-condicoes"><p class="control-label" style="text-align: left !important;">Selecione uma unidade acima.</p></div>
                                        <?php } ?>
                                        <div class="list-condicoes"></div>
                                    <?php } else { ?>
                                        <div class="msg-condicoes"></div>
                                        <div class="list-condicoes">
                                            <?php echo $condicoes["html"]; ?>
                                            <?php
                                                if(@count($condicao_hidden) > 0) {
                                                    foreach ($condicao_hidden as $value) {
                                                        echo '<input type="hidden" id="condicao_hidden-'.$value.'" name="condicao_hidden[]" value="'.$value.'">';
                                                    }
                                                }
                                                if(@count($condicao_hidden_order) > 0) {
                                                    foreach ($condicao_hidden_order as $key => $value) {
                                                        echo '<input type="hidden" id="condicao_hidden_order-'.$condicao_hidden[$key].'" name="condicao_hidden_order[]" value="'.$value.'">';
                                                    }
                                                }
                                            ?>
                                        </div>
                                    <?php } ?>
                                    <div class="loading" style="display:none;">
                                      <i class="fa fa-refresh fa-spin" style="font-size: 30px;"></i>
                                    </div>
                                </div>
                                <div class="vencimentos" <?php echo ($sequencial_checked == 1) ? '' : 'style="display:none;"' ?>>
                                    <?php echo lang('vencimentos', 'vencimentos', array('class' => 'col-sm-1 control-label'), true); ?>
                                    <div class="col-sm-5">
                                        <table id="vencimentos_table" class="table">
                                            <thead>
                                                <tr>
                                                    <th>Tipo</th>
                                                    <th>Parc.</th>
                                                    <th>Primeira Parc.</th>
                                                    <th>Última Parc.</th>
                                                    <th>Vlr. Bruto</th>
                                                    <th>Desconto</th>
                                                    <th>Vlr. Líquido</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                    if($sequencial_checked == 1) {
                                                        foreach($vencimentos as $venc) {
                                                            echo "<tr><td>".$venc['desc']."</td><td>".$venc['parc']."</td><td>".$venc['primeira_parc']."</td><td>".$venc['ultima_parc']."</td><td>".$venc['vlr_bruto']."</td><td>".$venc['desconto']."</td><td>".$venc['vlr_liquido']."</td></tr>";
                                                        }
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                        <div class="loading" style="display:none;">
                                          <i class="fa fa-refresh fa-spin" style="font-size: 30px;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('disciplinas', 'disciplinas', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($disciplinas);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('celular', 'celular', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($celular);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('email', 'email', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-3">
                                    <?php echo form_input($email);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo anchor('cupom', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
        $('#datetimehourpickerCupom').datetimepicker({
            defaultDate: "<?php echo @$data_expiracao ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY HH:mm',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        });

        $('#datetimehourpickerSequencia').datetimepicker({
            defaultDate: "<?php echo @$data_inicio_sequencial ?>",
            locale:'pt-br',
            format: 'DD/MM/YYYY',
            showClose: true,
            icons: {
                close: 'closeText'
            }
        }).on("dp.change",function(date){ 
            $('.condicao').removeAttr('checked');
        });

        $('.multipleSelect').fastselect();
    };
</script>