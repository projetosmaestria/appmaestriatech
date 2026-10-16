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
                        <h3 class="box-title"><?php echo lang('app_exercicios_perguntas'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-perguntas_app_exercicios')); ?>
                            <?php 
                                if($perguntas->num_rows() > 0) {
                                    $count = 1;
                                    foreach ($perguntas->result() as $perg) {
                            ?>
                                        <input type="hidden" id="sentencaId<?php echo $perg->id ?>" name="sentencaId[]" value="<?php echo $perg->id ?>">
                                        <input type="hidden" id="sentencaStatus<?php echo $perg->id ?>" name="sentencaStatus[]" value="<?php echo $perg->ativo ?>">
                                        <div class="form-group pergunta">
                                            <?php echo lang('sentenca', 'sentenca', array('class' => 'col-sm-2 control-label'), false); ?>
                                            <div class="col-sm-1">
                                                <input type="number" id="ordem<?php echo $count ?>" name="ordem[]" class="form-control" placeholder="Ordem" value="<?php echo $perg->ordem ?>">
                                            </div>
                                            <div class="col-sm-7">
                                                <input type="text" id="sentenca<?php echo $count ?>" name="sentenca[]" class="form-control" placeholder="Sentença" value="<?php echo $perg->sentenca ?>">
                                            </div>
                                            <div class="col-sm-2">
                                                <?php if($count == 1) { ?>
                                                    <a href="#" class="btn btn-success adiciona-pergunta">Adicionar</a>
                                                <?php } else { ?>
                                                    <?php if($perg->ativo == "S"): ?>
                                                        <a href="#" class="btn btn-danger" onclick="ativar_desativar_pergunta('<?php echo $perg->id ?>', this);">Desativar</a>
                                                    <?php else: ?>
                                                        <a href="#" class="btn btn-info" onclick="ativar_desativar_pergunta('<?php echo $perg->id ?>', this);">Ativar</a>
                                                    <?php endif; ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                            <?php          
                                    $count++; }
                                } else {
                            ?>
                                <div class="form-group pergunta">
                                    <?php echo lang('sentenca', 'sentenca', array('class' => 'col-sm-2 control-label'), false); ?>
                                    <div class="col-sm-1">
                                        <input type="number" id="ordem1" name="ordem[]" class="form-control" placeholder="Ordem">
                                    </div>
                                    <div class="col-sm-7">
                                        <input type="text" id="sentenca1" name="sentenca[]" class="form-control" placeholder="Sentença">
                                    </div>
                                    <div class="col-sm-2">
                                        <a href="#" class="btn btn-success adiciona-pergunta">Adicionar</a>
                                    </div>
                                </div>
                            <?php } ?>
                            
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo anchor('app_exercicios', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
        $("#form-perguntas_app_exercicios .adiciona-pergunta").click(function(){
            var uniqid = '_'+Math.random().toString(36).substr(2, 9);
            var nova_linha = $('<div class="form-group pergunta">'+
                                '<label for="sentenca'+ uniqid +'" class="control-label col-sm-2">Sentença</label>' + 
                                '<div class="col-sm-1">' +
                                    '<input type="number" id="ordem'+ uniqid +'" name="ordem[]" class="form-control" placeholder="Ordem">' +
                                '</div>' +
                                '<div class="col-sm-7">'+
                                    '<input type="text" id="sentenca' + uniqid + '" name="sentenca[]" class="form-control" placeholder="Sentença">'+
                                '</div>'+
                                '<div class="col-sm-2">'+
                                    '<a href="#" class="btn btn-danger remove-pergunta">Remover</a>'+
                                '</div>'+
                            '</div>');
            
            nova_linha.hide();
            $(".pergunta:last").after(nova_linha);
            nova_linha.fadeIn("slow");
            return false;
        });

        $('#form-perguntas_app_exercicios').on('click', '.remove-pergunta', function(){
            $(this).parent().parent().css( 'background-color', '#FF6C6C' );
            $(this).parent().parent().fadeOut("slow", function() {
                $(this).parent().parent().css( 'background-color', '#FFFFFF' );
                $(this).remove();
            });
            return false;
        });
    };

    function ativar_desativar_pergunta(id, f) {
        if($("#sentencaStatus"+id).val() == "N") {
            $("#sentencaStatus"+id).val("S");
            $(f).addClass("btn-danger");
            $(f).removeClass("btn-info");
            $(f).html("Desativar");
        } else {
            $("#sentencaStatus"+id).val("N");
            $(f).addClass("btn-info");
            $(f).removeClass("btn-danger");
            $(f).html("Ativar");
        }
    };
</script>