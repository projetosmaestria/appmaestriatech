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
                                    <h3 class="box-title"><?php echo lang('menus_create'); ?></h3>
                                </div>
                                <div class="box-body">
                                    <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-menu_empresa')); ?>
                                        <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
                                        <div class="form-group">
                                            <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), true); ?>
                                            <div class="col-sm-10">
                                                <div class="radio">
                                                    <label for="status_ativo"><input type="radio" id="status_ativo" name="status" value="1" checked="checked"><?php echo lang('status_ativo') ?></label>
                                                </div>
                                                <div class="radio">
                                                    <label for="status_inativo"><input type="radio" id="status_inativo" name="status" value="0"><?php echo lang('status_inativo') ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('menus_tipo', 'tipo', array('class' => 'col-sm-2 control-label')); ?>
                                            <div class="col-sm-10">
                                                <?php echo form_dropdown($tipo);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('menus_nome', 'nome', array('class' => 'col-sm-2 control-label')); ?>
                                            <div class="col-sm-10">
                                                <?php echo form_input($nome);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('menus_icon_class', 'icon_class', array('class' => 'col-sm-2 control-label')); ?>
                                            <div class="col-sm-10">
                                                <?php echo form_input($icon_class);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('menus_pai', 'pai', array('class' => 'col-sm-2 control-label')); ?>
                                            <div class="col-sm-10">
                                                <?php if($menu): ?>
                                                    <input type="hidden" name="pai_id" value="" id="pai_id" />
                                                    <?php echo $menu ?>
                                                <?php endif ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('menus_order', 'order', array('class' => 'col-sm-2 control-label')); ?>
                                            <div class="col-sm-10">
                                                <?php echo form_input($order);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('menus_controlador', 'controlador', array('class' => 'col-sm-2 control-label')); ?>
                                            <div class="col-sm-10">
                                                <?php echo form_dropdown($controlador);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('menus_metodo', 'metodo', array('class' => 'col-sm-2 control-label')); ?>
                                            <div class="col-sm-10">
                                                <?php echo form_dropdown($metodo);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('menus_empresas', 'empresas', array('class' => 'col-sm-2 control-label')); ?>
                                            <div class="col-sm-10">
                                                <?php echo form_multiselect($empresas); ?>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class="form-group">
                                            <div class="col-sm-offset-2 col-sm-10">
                                                <div class="btn-group">
                                                    <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                                    <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                                    <?php echo anchor('menus', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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