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
                        <div class="row">
                            <?php echo lang('fb_id', 'fb_id', array('class' => 'col-md-12 control-label'), false); ?>
                            <div class="col-md-12">
                                <?php echo form_input($fb_id);?>
                            </div>
                        </div>
                        <br>
                        <div class="row col-md-12">
                            <input type="button" name="btn_add_user" value="Adicionar" class="btn btn-primary btn-flat btn_add_anuncio">
                        </div>
                    </div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $evento->descricao; ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if(count($anuncios > 0)) : ?>
                            <?php echo form_open(current_url(), array('class' => '', 'id' => 'form-evento_anuncio')); ?>
                                <div class="form-group div_form_anuncio">
                                    <?php foreach ($anuncios as $anuncio) : ?>
                                        <input type="hidden" name="anuncios[]" id="hidden-anuncio-<?php echo $anuncio->fb_id ?>" value="<?php echo $anuncio->fb_id ?>">
                                        <ul class="list-group" id="ul-anuncio-<?php echo $anuncio->fb_id ?>">
                                            <li class="list-group-item">
                                                <div class="row">
                                                    <?php echo form_button(array('type' => 'button', 'class' => 'btn btn-danger btn-lista', 'style' => 'float: right;margin-right: 10px;', 'content' => '<i class="fa fa-times" aria-hidden="true"></i>', 'onclick' => "remove_anuncio('".$anuncio->fb_id."')")); ?>
                                                    <div class="col-md-1">
                                                        <strong>ID: </strong> <label>#</label><?php echo $anuncio->id ?> 
                                                    </div>
                                                    <div class="col-md-2">
                                                        <strong>Facebook ID: </strong> <?php echo $anuncio->fb_id ?> 
                                                    </div>
                                                </div>
                                            </li>
                                        </ul> 
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-group">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo anchor('midias_eventos', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            <?php echo form_close();?>
                        <?php endif; ?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    window.onload = function() {
        $(".btn_add_anuncio").click(function() {
          var fb_id = $("#fb_id").val().trim();
          if(fb_id == "") {
            alert("Digite um facebook id.");
            return false;
          } else {
            if($("#ul-anuncio-"+fb_id).length) {
              $("#ul-anuncio-"+fb_id).html("<li class='list-group-item '><label>#</label>"+fb_id+"<button type='button' class='btn btn-danger btn-lista' style='float:right;' onclick='remove_anuncio(\""+fb_id+"\")'><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button></li> <li class='list-group-item'>"+$(".singleInputDynamic option:selected").text()+"</li>");
            } else {
              $("#form-evento_anuncio").append('<input type="hidden" name="anuncios[]" value="'+fb_id+'">');
              $(".div_form_anuncio").append("<ul class='list-group' id='ul-anuncio-"+fb_id+"'> <li class='list-group-item '><label>#</label>"+fb_id+"<button type='button' class='btn btn-danger btn-lista' style='float:right;' onclick='remove_anuncio(\""+fb_id+"\")'><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button></li> <li class='list-group-item'>"+$(".singleInputDynamic option:selected").text()+"</li> </ul>");
            }
          }
        });
    };

    function remove_anuncio(fb_id) {
      $("#ul-anuncio-"+fb_id).fadeOut("normal", function() {
          $(this).remove();
          $("#hidden-anuncio-"+fb_id).remove();
      });
      $("#form-evento_anuncio").append('<input type="hidden" name="anuncios_remove[]" value="'+fb_id+'">');
    }
</script>