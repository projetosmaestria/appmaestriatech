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
                        <h3 class="box-title"><?php echo lang('app_qrcodes_create'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_app_qrcodes')); ?>
                            <div class="form-group">
                                <?php echo lang('qtd', 'qtd', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($qtd);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('obs', 'obs', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($obs);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('livro', 'livro', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php if (count($livros) > 0) { ?>
                                        <?php foreach ($livros as $livro) { ?>
                                            <div class="radio">
                                                <label for="livro-<?php echo $livro->id ?>"><input type="radio" id="livro-<?php echo $livro->id ?>" name="livro" value="<?php echo $livro->id ?>"><?php echo $livro->titulo ?></label>
                                            </div>
                                        <?php } ?>
                                        <?php if($empresa == 2){ ?>
                                            <div class="radio">
                                                <label for="livro-0-new"><input type="radio" id="livro-0-new" name="livro" value="0-new">Kit - New</label>
                                            </div>
                                            <div class="radio">
                                                <label for="livro-0-antigo"><input type="radio" id="livro-0-antigo" name="livro" value="0-antigo">Kit - Antigo</label>
                                            </div>
                                            <div class="radio">
                                                <label for="livro-0-videoaula"><input type="radio" id="livro-0-videoaula" name="livro" value="0-videoaula">Kit - Vídeoaula</label>
                                            </div>
                                            <div class="radio">
                                                <label for="livro-0-first"><input type="radio" id="livro-0-first" name="livro" value="0-first">Kit - First, Second, Third</label>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('app_qrcodes', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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