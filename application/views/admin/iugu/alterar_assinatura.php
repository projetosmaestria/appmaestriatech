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

        <?php if(@$assinatura_id != "") { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo lang('alterar_assinatura'); ?>:</h3>
                            <div class="assinaturaIdClass">
                                <b><?php echo $assinatura_id ?></b>
                                <?php if($can_alterar_id_assinatura) : ?>
                                    <a href="#" class="btn btn-tool" title="Editar ID assinatura" alt="Editar ID assinatura" onclick="openEditarIdAssinaturaIugu('<?php echo $assinatura_id ?>', '<?php echo $contrato_id ?>')">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="box-body">
                            <?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal', 'id' => 'form-iugu_alterar_assinatura')); ?>
                                <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
                                <input type="hidden" id="assinatura_id_base" name="assinatura_id_base" value="<?php echo $assinatura_id ?>">
                                <div class="form-group">
                                    <?php echo lang('condicao', 'condicao', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_dropdown($plano);?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo lang('proximo_vencimento', 'proximo_vencimento', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-2">
                                        <div class="input-group date" id='datetimepicker'>
                                            <?php echo form_input(array('id'=>'expires_at','name'=>'expires_at','value'=>date("d/m/Y", strtotime($expires_at)),'class'=>'form-control'));?>
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo lang('metodo_pagamento', 'metodo_pagamento', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_dropdown($payable_with);?>
                                    </div>
                                </div>
                                <?php if(count($subitems) == 0) { ?>
                                    <div class="form-group subitems">
                                        <?php echo lang('subitems', 'subitems', array('class' => 'col-sm-2 control-label'), false); ?>
                                        <div class="col-sm-3">
                                            <input type="text" id="description_1" name="description[1]" class="form-control" placeholder="Descrição">
                                        </div>
                                        <div class="col-sm-1">
                                            <input type="number" id="quantity_1" name="quantity[1]" class="form-control" placeholder="Qtd">
                                        </div>
                                        <div class="col-sm-1">
                                            <input type="text" id="price_1" name="price[1]" class="form-control" placeholder="Preço">
                                        </div>
                                        <div class="col-sm-1">
                                            <input type="checkbox" id="recurrent_1" name="recurrent[1]" class="form-control" value="1">
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="#" class="btn btn-success adiciona-regra-peso-venda">Adicionar</a>
                                        </div>
                                        <div class="col-md-offset-2 col-sm-10">
                                            <p>*os preços serão replicados da condição de pagamento selecionada acima</p>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <?php $i = 1; foreach ($subitems as $value) { ?>
                                        <div class="form-group subitems">
                                            <input type="hidden" name="id[<?php echo $i ?>]" value="<?php echo $value["id"] ?>">
                                            <?php if($i == 1) { 
                                                echo lang('subitems', 'subitems', array('class' => 'col-sm-2 control-label'), false);
                                            } else { ?>
                                                <label class="control-label col-sm-2"></label>
                                            <?php } ?>
                                            <div class="col-sm-3">
                                                <input type="text" id="description_<?php echo $i ?>" name="description[<?php echo $i ?>]" class="form-control" placeholder="Descrição" value="<?php echo $value["description"] ?>">
                                            </div>
                                            <div class="col-sm-1">
                                                <input type="number" id="quantity_<?php echo $i ?>" name="quantity[<?php echo $i ?>]" class="form-control" placeholder="Qtd" value="<?php echo $value["quantity"] ?>">
                                            </div>
                                            <div class="col-sm-1">
                                                <input type="text" id="price_<?php echo $i ?>" name="price[<?php echo $i ?>]" class="form-control" placeholder="Preço" value="<?php echo $value["price"] ?>">
                                            </div>
                                            <div class="col-sm-1">
                                                <label class="checkbox-inline" for="recurrent_<?php echo $i ?>"><input type="checkbox" id="recurrent_<?php echo $i ?>" name="recurrent[<?php echo $i ?>]" <?php echo ($value["recurrent"] == 1) ? "checked" : "" ?> value="1"> Recorrente</label>
                                            </div>
                                            <div class="col-sm-2">
                                                <?php if($i == 1) { ?>
                                                    <a href="#" class="btn btn-success adiciona-subitems">Adicionar</a>
                                                <?php } else { ?>
                                                    <a href="#" class="btn btn-danger remove-subitems">Remover</a>
                                                <?php } ?>
                                            </div>
                                            <div class="col-md-offset-2 col-sm-10">
                                                <p>*os preços serão replicados da condição de pagamento selecionada acima</p>
                                            </div>
                                        </div>
                                    <?php $i++; } ?>
                                <?php } ?>
                                <div class="box-footer">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'id' => 'btn-primary1', 'class' => 'btn btn-flat btn-primary', 'content' => 'Salvar')); ?>
                                        <?php echo anchor('/', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            <?php form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } else { ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Incluir Assinatura:</h3>
                        </div>
                        <div class="box-body">
                            <?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal', 'id' => 'form-iugu_incluir_assinatura')); ?>
                                <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
                                <div class="form-group">
                                    <?php echo lang('assinatura_id_input', 'assinatura_id_input', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_input($assinatura_id_input);?>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'id' => 'btn-primary1', 'class' => 'btn btn-flat btn-primary', 'content' => 'Salvar')); ?>
                                        <?php echo anchor('/', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            <?php form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </section>
</div>

<?php if(@$assinatura_id != "") { ?>
    <div id="confirmEditAssin" class="modal fade" role="dialog">
        <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirmação da edição da assinatura</h4>
              </div>
              <div class="modal-body">
                
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirmarEdicao">Sim</button>
                <button type="button" data-dismiss="modal" class="btn">Não</button>
              </div>
        </div>

      </div>
    </div>

    <script type="text/javascript">
        window.onload = function() {
            $('input[name*="price["]').maskMoney({prefix:'R$ ', allowNegative: true, thousands:'.', decimal:',', affixesStay: true});

            $("#form-iugu_alterar_assinatura .adiciona-subitems").click(function(){
                var uniqid = Math.random().toString(36).substr(2, 9);
                var nova_linha = $('<div class="form-group subitems">'+
                                    '<label class="control-label col-sm-2"></label>' + 
                                    '<div class="col-sm-3">' +
                                        '<input type="text" id="description_'+ uniqid +'" name="description['+ uniqid +']" class="form-control" placeholder="Descrição">' +
                                    '</div>' +
                                    '<div class="col-sm-1">'+
                                        '<input type="number" id="quantity_' + uniqid + '" name="quantity['+ uniqid +']" class="form-control" placeholder="Qtd">'+
                                    '</div>'+
                                    '<div class="col-sm-1">'+
                                        '<input type="text" id="price_' + uniqid + '" name="price['+ uniqid +']" class="form-control" placeholder="Preço">'+
                                    '</div>'+
                                    '<div class="col-sm-1">'+
                                        '<label class="checkbox-inline" for="recurrent_'+ uniqid +'"><input type="checkbox" id="recurrent_' + uniqid + '" name="recurrent['+ uniqid +']" value="1"> Recorrente</label>'+
                                    '</div>'+
                                    '<div class="col-sm-2">'+
                                        '<a href="#" class="btn btn-danger remove-subitems">Remover</a>'+
                                    '</div>'+
                                '</div>');
                nova_linha.hide();
                $(".subitems:last").after(nova_linha);
                nova_linha.fadeIn("slow");
                initMaskMoney();
                return false;
            });

            $('#form-iugu_alterar_assinatura').on('click', '.remove-subitems', function(){
                var id = $(this).parent().parent().find('input[name*="id["]');
                
                if(typeof id.val() != 'undefined') {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'subitems_removidos[]',
                        value: id.val()
                    }).appendTo('#form-iugu_alterar_assinatura');
                }

                $(this).parent().parent().fadeOut("slow", function() {
                    $(this).remove();
                });
                return false;
            });
        };

        function initMaskMoney() {
            $('input[name*="price["]').maskMoney({prefix:'R$ ', allowNegative: true, thousands:'.', decimal:',', affixesStay: true});
        }
    </script>
<?php } ?>
