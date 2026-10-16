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
                <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-agenda_configurar_google_calendar')); ?>
                    <input type="hidden" name="base_url" id="base_url" value="<?php echo base_url() ?>">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo lang('configurar_google_calendar'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="alert alert-info alert-dismissible">
                              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                              <h4><i class="icon fa fa-info"></i> Informação!</h4>
                              <p>Link do tutorial para obter os dados abaixo <a href="https://developers.google.com/adwords/api/docs/guides/authentication#webapp" target="_blank"><b>aqui!</b></a></p>
                              <p><b>Atenção:</b> não esqueça de adicionar a URL no campo "URIs de redirecionamento autorizados" na configuração.</p>
                            </div>
                            <div class="form-group">
                                <?php echo lang('client_id', 'client_id', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($client_id);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('client_secret', 'client_secret', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($client_secret);?>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="col-md-12">
                                <div class="btn-group">
                                    <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat classAndDocsFormSubmit', 'name' => 'salvar', 'content' => lang('actions_save'))); ?>
                                    <?php echo anchor('agenda', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-danger btn-flat classAndDocsFormSubmit', 'name' => 'remover', 'content' => lang('actions_remove'), 'title'=>lang('actions_remove'))); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php echo form_close();?>
             </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    
    window.onload = function() {
        var buttonpressed;
        $('.classAndDocsFormSubmit').click(function() {
            buttonpressed = $(this).attr('name')
        });
        $('#form-agenda_configurar_google_calendar').submit(function() {
            $('<input>').attr({
                type: 'hidden',
                name: 'acao',
                value: buttonpressed
            }).appendTo('#form-agenda_configurar_google_calendar');
        });
    }

</script>