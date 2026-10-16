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
                        <h3 class="box-title"><?php echo lang('view'); ?> #<?php echo $contato->id; ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row col-sm-12">
                        <?php echo form_open('', array('class' => 'form-horizontal', 'id' => 'form-view_contatos_interno')); ?>
                            <div class="form-group">
                                <?php echo lang('de', 'de', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($de); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('assunto', 'assunto', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($assunto); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('mensagem', 'mensagem', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_textarea($mensagem); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('data', 'data', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($data); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo anchor('contatos_interno', lang('actions_back'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            </div>
                        <?php form_close(); ?>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>
