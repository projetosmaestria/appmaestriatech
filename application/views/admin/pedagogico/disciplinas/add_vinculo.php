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
                    <div class="box-header">
                        <div class="msg"></div>
                        <?php echo form_open(base_url('disciplinas/add_vinculo'), array('class' => '', 'id' => 'form-vincular_disciplina')) ?>
                            <input type="hidden" name="disciplina" value="<?php echo $disciplina->ID_DISCIPLINA ?>">
                            <div class="form-group">
                                <label for="user">Curso:</label>
                                <?php echo form_dropdown($curso);?>
                            </div>
                            <div class="form-group">
                                <label for="user">Pacote:</label>
                                <?php echo form_dropdown($pacote);?>
                            </div>
                            <div class="form-group">
                                <label for="user">Sub Pacote:</label>
                                <?php echo form_dropdown($pacotesub);?>
                            </div>
                            <div class="form-group">
                                <label for="user">Prioridade:</label>
                                <?php echo form_input($prioridade);?>
                            </div>
                            <div class="form-group">
                                <label for="user">Média:</label>
                                <?php echo form_input($media);?>
                            </div>
                            <div class="form-group">
                                <label for="user">Percentual Frequência:</label>
                                <?php echo form_input($perc_frequencia);?>
                            </div>
                            <div class="form-group">
                                <input type="submit" name="btn_add_vinculo_disciplina" value="Adicionar" class="btn btn-primary btn-flat btn_add_vinculo_disciplina">
                            </div>
                        <?php echo form_close() ?>
                    </div>
                    <div class="box-header with-border">
                        <h3 class="box-title">Vínculos disciplina <?php echo $disciplina->DESCRICAO; ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if(count($vinculos > 0)) : ?>
                            <div class="form-group div_form_vinculos_curso">
                                <?php foreach ($vinculos as $vin) : ?>
                                    <ul class="list-group ul-vinculo-<?php echo $vin->ID_CONTROLE ?>">
                                        <li class="list-group-item ">
                                            <?php echo form_button(array('type' => 'button', 'class' => 'btn btn-danger btn-lista', 'style' => 'float: right;', 'content' => '<i class="fa fa-times" aria-hidden="true"></i>', 'onclick' => "remove_vinculo_disciplina('".$vin->ID_CONTROLE."')")); ?>
                                            <label>Curso:</label> <?php echo $vin->curso_desc ?><br>
                                            <label>Pacote:</label> <?php echo $vin->pacote_desc ?><br>
                                            <label>SubPacote:</label> <?php echo $vin->pacotesub_desc ?><br>
                                            <label>Média:</label> <?php echo $vin->MEDIA ?><br>
                                            <label>Percentual Frequência:</label> <?php echo $vin->PERC_FREQUENCIA ?><br>
                                            <label>Prioridade:</label> <?php echo $vin->PRIORIDADE ?><br>
                                        </li>
                                    </ul> 
                                <?php endforeach; ?>
                            </div>
                            <div class="form-group">
                                <div class="btn-group">
                                    <?php echo anchor('disciplinas', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    window.onload = function() {
        $('#media').maskMoney({prefix:'', allowNegative: true, thousands:'.', decimal:',', affixesStay: true});
        $('#perc_frequencia').maskMoney({prefix:'', allowNegative: true, thousands:'.', decimal:',', affixesStay: true});
        
        $( "#form-vincular_disciplina").submit(function( event ) {
          event.preventDefault();
          var form = $(this);
          var url = form.attr('action');
          $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function(data){
              var result = $.parseJSON(data);
              if(result.erro) {
                $(".msg").html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><h4><i class="icon fa fa-ban"></i> Erro!</h4>'+result.msg+'</div>');
              } else {
                $(".div_form_vinculos_curso").append(result.data);
                $(".msg").html('<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><h4><i class="icon fa fa-check"></i> Sucesso!</h4>Vínculo registrado.</div>');
              }
            }
          });
        });

        $( "#pacote" ).change(function() {
            $.ajax({
                url: '<?php echo base_url('disciplinas/get_pacotesub') ?>',
                type: 'POST',
                data: {id:$(this).val()},
                success: function(data){
                    $('#pacotesub').html(data)
                }
            });
        });

    };
</script>