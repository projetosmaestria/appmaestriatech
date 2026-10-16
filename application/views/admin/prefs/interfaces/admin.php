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
                                    <li class="active"><?php echo anchor('prefs/index', lang('menu_int_admin')); ?></li>
                                    <li class=""><?php echo anchor('prefs/dashboard', lang('menu_int_dashboard')); ?></li>
                                    <li class=""><?php echo anchor('prefs/email', lang('menu_int_email')); ?></li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="tab_admin">
                                        <?php echo $message_admin; ?>

                                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-interface_admin')); ?>
<?php //foreach ($admin_pref_interface as $value): ?>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_user_panel'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="user_panel" id="user_panel1" value="1" <?php echo set_value('user_panel', $admin_pref_interface->user_panel) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="user_panel" id="user_panel0" value="0" <?php echo set_value('user_panel', $admin_pref_interface->user_panel) == 0 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_empresa_sidebar'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="empresa_sidebar" id="empresa_sidebar1" value="1" <?php echo set_value('empresa_sidebar', $admin_pref_interface->empresa_sidebar) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="empresa_sidebar" id="empresa_sidebar0" value="0" <?php echo set_value('empresa_sidebar', $admin_pref_interface->empresa_sidebar) == 0 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_sidebar_form'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="sidebar_form" id="sidebar_form1" value="1" <?php echo set_value('sidebar_form', $admin_pref_interface->sidebar_form) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="sidebar_form" id="sidebar_form0" value="0" <?php echo set_value('sidebar_form', $admin_pref_interface->sidebar_form) == 0 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_messages_menu'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="messages_menu" id="messages_menu1" value="1" <?php echo set_value('messages_menu', $admin_pref_interface->messages_menu) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="messages_menu" id="messages_menu0" value="0" <?php echo set_value('messages_menu', $admin_pref_interface->messages_menu) == 0 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_notifications_menu'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="notifications_menu" id="notifications_menu1" value="1" <?php echo set_value('notifications_menu', $admin_pref_interface->notifications_menu) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="notifications_menu" id="notifications_menu0" value="0" <?php echo set_value('notifications_menu', $admin_pref_interface->notifications_menu) == 0 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_tasks_menu'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="tasks_menu" id="tasks_menu1" value="1" <?php echo set_value('tasks_menu', $admin_pref_interface->tasks_menu) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="tasks_menu" id="tasks_menu0" value="0" <?php echo set_value('tasks_menu', $admin_pref_interface->tasks_menu) == 0 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_user_menu'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="user_menu" id="user_menu1" value="1" <?php echo set_value('user_menu', $admin_pref_interface->user_menu) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="user_menu" id="user_menu0" value="0" <?php echo set_value('user_menu', $admin_pref_interface->user_menu) == 0 ? 'checked' : NULL; ?> disabled> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_ctrl_sidebar'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="ctrl_sidebar" id="ctrl_sidebar1" value="1" <?php echo set_value('ctrl_sidebar', $admin_pref_interface->ctrl_sidebar) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="ctrl_sidebar" id="ctrl_sidebar0" value="0" <?php echo set_value('ctrl_sidebar', $admin_pref_interface->ctrl_sidebar) == 0 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_transition_page'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="transition_page" id="transition_page1" value="1" <?php echo set_value('transition_page', $admin_pref_interface->transition_page) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="transition_page" id="transition_page0" value="0" <?php echo set_value('transition_page', $admin_pref_interface->transition_page) == 0 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_grupo_admin'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_dropdown($grupo_admin);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_grupo_vendedor'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_dropdown($grupo_vendedor);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_grupo_tutor'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_dropdown($grupo_tutor);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_grupo_supervisor'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_dropdown($grupo_supervisor);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_grupo_aluno'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_dropdown($grupo_aluno);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_grupo_professor'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_dropdown($grupo_professor);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_qtd_alunos'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_input($qtd_alunos);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_qtd_creditos_ead'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_input($qtd_creditos_ead);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_grupo_responsavel_chamado'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_dropdown($grupos_responsavel_chamado);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_cat_grupo_responsavel'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_dropdown($cats_grupo_responsavel);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_grupo_dev_chamado'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_dropdown($grupos_dev_chamado);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_relatorio_tutor'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="relatorio_tutor" id="relatorio_tutor1" value="1" <?php echo set_value('relatorio_tutor', $admin_pref_interface->relatorio_tutor) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="relatorio_tutor" id="relatorio_tutor0" value="0" <?php echo set_value('relatorio_tutor', $admin_pref_interface->relatorio_tutor) == 0 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_relatorio_supervisor'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="relatorio_supervisor" id="relatorio_supervisor1" value="1" <?php echo set_value('relatorio_supervisor', $admin_pref_interface->relatorio_supervisor) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="relatorio_supervisor" id="relatorio_supervisor0" value="0" <?php echo set_value('relatorio_supervisor', $admin_pref_interface->relatorio_supervisor) == 0 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_relatorio_apontamentos'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_dropdown($relatorio_apontamentos);?>
                                                </div>
                                            </div>
											<div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_relatorio_planos_acao'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_dropdown($relatorio_planos_acao);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('prefs_horas_expiracao_cupom'); ?></label>
                                                <div class="col-sm-10">
                                                    <?php echo form_input($horas_expiracao_cupom);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo lang('cadastro_user_completo'); ?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="cadastro_user_completo" id="cadastro_user_completo" value="1" <?php echo set_value('cadastro_user_completo', $admin_pref_interface->cadastro_user_completo) == 1 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_yes')); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="cadastro_user_completo" id="cadastro_user_completo0" value="0" <?php echo set_value('cadastro_user_completo', $admin_pref_interface->cadastro_user_completo) == 0 ? 'checked' : NULL; ?>> <?php echo strtoupper(lang('actions_no')); ?>
                                                    </label>
                                                </div>
                                            </div>
<?php //endforeach; ?>
                                            <div class="form-group">
                                                <div class="col-sm-offset-2 col-sm-10">
                                                    <div class="btn-group">
                                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                                        <?php //echo anchor('prefs/reset_interfaces_admin', lang('actions_default_values'), array('class' => 'btn btn-danger btn-flat')); ?>
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