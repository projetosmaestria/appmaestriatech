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
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs">
                                    <li class=""><?php echo anchor('prefs/index', lang('menu_int_admin')); ?></li>
                                    <li class="active"><?php echo anchor('prefs/dashboard', lang('menu_int_dashboard')); ?></li>
                                    <li class=""><?php echo anchor('prefs/email', lang('menu_int_email')); ?></li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="tab_dashboard">
                                        <?php echo $message_admin; ?>

                                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-interface_dashboard')); ?>
                                            <?php foreach ($inputs as $input) { ?>
                                                <div class="form-group">
                                                    <label class="col-sm-2 control-label"><?php echo $input['id']; ?></label>
                                                    <div class="col-sm-10">
                                                        <?php echo form_dropdown($input);?>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <div class="form-group">
                                                <div class="col-sm-offset-2 col-sm-10">
                                                    <div class="btn-group">
                                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php echo form_close();?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

<script type="text/javascript">
    window.onload = function() {
        $('.multipleSelect').fastselect();
    };
</script>