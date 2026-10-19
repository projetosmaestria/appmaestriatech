<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if(isset($message)) { echo $message; } ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-pencil-square-o text-primary"></i> <?php echo lang('menus_edit') ? lang('menus_edit') : 'Editar Menu'; ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-menu_empresa')); ?>
                            <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url(); ?>">
                            
                            <div class="form-group">
                                <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="status_ativo">
                                            <input type="radio" id="status_ativo" name="status" value="1" <?php if($status == 1) echo 'checked="checked"'; ?>>
                                            <?php echo lang('status_ativo'); ?>
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label for="status_inativo">
                                            <input type="radio" id="status_inativo" name="status" value="0" <?php if($status == 0) echo 'checked="checked"'; ?>>
                                            <?php echo lang('status_inativo'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php echo lang('menus_tipo', 'tipo', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($tipo); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php echo lang('menus_nome', 'nome', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($nome); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php echo lang('menus_icon_class', 'icon_class', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($icon_class); ?>
                                    <span class="help-block"><small class="text-muted">Ex: <code>&lt;i class="fa fa-folder"&gt;&lt;/i&gt;</code></small></span>
                                </div>
                            </div>

                            <!-- MENU PAI - SELEÇÃO EM ÁRVORE HIERÁRQUICA COLAPSÁVEL -->
                            <div class="form-group">
                                <?php echo lang('menus_pai', 'pai_id', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <input type="hidden" name="pai_id" id="pai_id" value="<?php echo htmlspecialchars((string)$pai_id_selecionado); ?>">

                                    <div class="menu-tree-wrapper">
                                        <!-- Barra Superior de Controle e Status -->
                                        <div class="menu-tree-header clearfix">
                                            <div class="menu-tree-status pull-left">
                                                <span class="text-muted" style="font-size: 13px; font-weight: 600; margin-right: 6px;">
                                                    <i class="fa fa-sitemap text-primary"></i> Menu Pai Selecionado:
                                                </span>
                                                <span id="badge-pai-selecionado" class="label <?php echo empty($pai_id_selecionado) ? 'label-primary' : 'label-success'; ?>" style="font-size: 13px; font-weight: 600; padding: 4px 10px; display: inline-block;">
                                                    <i class="fa <?php echo empty($pai_id_selecionado) ? 'fa-home' : 'fa-folder-open'; ?>"></i>
                                                    <span id="text-pai-selecionado"><?php echo htmlspecialchars($nome_pai_selecionado); ?></span>
                                                </span>
                                            </div>

                                            <div class="menu-tree-actions pull-right">
                                                <button type="button" class="btn btn-xs btn-default btn-flat" id="btn-set-root" title="Definir este item como Menu Principal de 1º Nível (sem pai)">
                                                    <i class="fa fa-level-up text-primary"></i> <strong>Menu Raiz</strong>
                                                </button>
                                                <button type="button" class="btn btn-xs btn-default btn-flat" id="btn-tree-expand-all" title="Expandir todas as pastas">
                                                    <i class="fa fa-plus-square-o"></i> Expandir Tudo
                                                </button>
                                                <button type="button" class="btn btn-xs btn-default btn-flat" id="btn-tree-collapse-all" title="Recolher todas as pastas">
                                                    <i class="fa fa-minus-square-o"></i> Recolher Tudo
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Campo de Busca em Tempo Real -->
                                        <div class="menu-tree-search-bar">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-addon" style="background: #fafafa;"><i class="fa fa-search text-muted"></i></span>
                                                <input type="text" id="tree-search-input" class="form-control" placeholder="Digite para filtrar menus por nome...">
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default btn-flat" id="btn-clear-search" title="Limpar filtro"><i class="fa fa-times text-muted"></i></button>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Container com Scroll da Árvore -->
                                        <div class="menu-tree-scroll-container">
                                            <!-- Opção Fixa de Menu Raiz -->
                                            <div class="tree-root-card <?php echo empty($pai_id_selecionado) ? 'is-selected' : ''; ?>" id="tree-root-card" data-id="" data-nome="Nenhum (Menu Raiz / 1º Nível)">
                                                <div class="tree-node-row">
                                                    <i class="fa fa-home tree-icon text-primary" style="font-size: 15px;"></i>
                                                    <span class="tree-node-label">
                                                        <strong style="color: #337ab7;">-- Nenhum (Menu Principal / 1º Nível) --</strong>
                                                        <small class="text-muted" style="margin-left: 8px;">Este menu ficará no primeiro nível da navegação lateral</small>
                                                        <i class="fa fa-check tree-check-mark pull-right text-success" <?php echo empty($pai_id_selecionado) ? '' : 'style="display:none;"'; ?>></i>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Árvore Hierárquica -->
                                            <div id="tree-menu-list-content">
                                                <?php 
                                                    $proibidos = isset($todos_menus) ? get_menu_prohibited_ids($todos_menus, isset($menu_model) ? $menu_model->id : null) : array();
                                                    echo render_menu_tree_collapsible(isset($menus_tree) ? $menus_tree : array(), $pai_id_selecionado, isset($menu_model) ? $menu_model->id : null, $proibidos);
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="help-block" style="margin-top: 6px;">
                                        <small class="text-muted"><i class="fa fa-info-circle text-info"></i> Clique na setinha <code><i class="fa fa-chevron-right"></i></code> para abrir ou recolher os submenus. Clique no nome do item desejado para selecioná-lo como pai, ou clique em <strong>Menu Raiz</strong>.</small>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php echo lang('menus_order', 'order', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($order); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php echo lang('menus_controlador', 'controlador', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($controlador); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php echo lang('menus_metodo', 'metodo', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($metodo); ?>
                                </div>
                            </div>

                            <!-- SELECT MÚLTIPLO DE EMPRESAS MODERNO COM FASTSELECT -->
                            <div class="form-group">
                                <?php echo lang('menus_empresas', 'empresas', array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <select name="empresas[]" id="empresas" multiple="multiple" class="form-control multipleSelect" style="width: 100%; max-width: 650px;">
                                        <?php if (isset($empresas_options) && is_array($empresas_options)): ?>
                                            <?php foreach ($empresas_options as $empId => $empNome): ?>
                                                <option value="<?php echo $empId; ?>" <?php echo (isset($menus_empresas) && in_array($empId, (array)$menus_empresas)) ? 'selected="selected"' : ''; ?>>
                                                    <?php echo htmlspecialchars($empNome); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <span class="help-block" style="margin-top: 6px;">
                                        <small class="text-muted"><i class="fa fa-building-o text-primary"></i> Clique para abrir e selecionar as empresas. As filiais selecionadas aparecem como tags removíveis.</small>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 25px;">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => '<i class="fa fa-check"></i> ' . lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('menus', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>

<style>
.menu-tree-wrapper {
    background: #ffffff;
    border: 1px solid #d2d6de;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    max-width: 750px;
}
.menu-tree-header {
    padding: 10px 14px;
    background: #f9f9f9;
    border-bottom: 1px solid #e5e5e5;
    border-radius: 4px 4px 0 0;
}
.menu-tree-status {
    line-height: 24px;
}
.menu-tree-actions .btn {
    margin-left: 4px;
}
.menu-tree-search-bar {
    padding: 8px 14px;
    background: #ffffff;
    border-bottom: 1px solid #eee;
}
.menu-tree-scroll-container {
    max-height: 380px;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 10px 14px;
    background: #fafafa;
}
.tree-root-card {
    padding: 8px 12px;
    margin-bottom: 10px;
    background: #ffffff;
    border: 1px dashed #3c8dbc;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.tree-root-card:hover {
    background: #f0f7fd;
    border-style: solid;
}
.tree-root-card.is-selected {
    background: #e8f4fc;
    border: 1px solid #3c8dbc;
    box-shadow: 0 0 0 1px #3c8dbc;
}
.tree-menu-branch {
    list-style: none;
    margin: 0;
    padding-left: 22px;
}
.tree-menu-branch.tree-menu-root {
    padding-left: 0;
}
.tree-menu-node {
    margin: 2px 0;
    position: relative;
}
.tree-node-row {
    display: flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 4px;
    background: #ffffff;
    border: 1px solid #e8e8e8;
    margin-bottom: 3px;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.tree-node-row:hover:not(.disabled) {
    background: #f5f9fc;
    border-color: #bcd9f2;
}
.tree-node-row.selected {
    background: #e8f4fc;
    border-color: #3c8dbc;
    box-shadow: 0 0 0 1px #3c8dbc;
}
.tree-node-row.disabled {
    background: #fbfbfb;
    border-color: #eee;
    opacity: 0.6;
    cursor: not-allowed;
}
.tree-toggle {
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #555;
    margin-right: 5px;
    border-radius: 3px;
    background: #f0f0f0;
    border: 1px solid #ddd;
    transition: all 0.15s ease;
}
.tree-toggle:hover {
    background: #3c8dbc;
    border-color: #367fa9;
    color: #ffffff;
}
.tree-spacer {
    width: 24px;
    display: inline-block;
    margin-right: 5px;
}
.tree-icon {
    font-size: 15px;
    margin-right: 8px;
    width: 18px;
    text-align: center;
    cursor: pointer;
}
.tree-node-label {
    flex: 1;
    cursor: pointer;
    user-select: none;
    display: inline-flex;
    align-items: center;
}
.tree-node-row.disabled .tree-node-label {
    cursor: not-allowed;
}
.tree-node-title {
    font-weight: 500;
    color: #333;
}
.tree-node-row.selected .tree-node-title {
    color: #1f6497;
    font-weight: 600;
}
.tree-check-mark {
    font-size: 15px;
    margin-left: auto;
}

/* FastSelect Custom Styling para AdminLTE */
.fstElement {
    border: 1px solid #d2d6de !important;
    border-radius: 0px !important;
    box-shadow: none !important;
    font-size: 13px !important;
    background: #ffffff !important;
    min-height: 34px !important;
    padding: 3px 5px !important;
    max-width: 650px;
}
.fstElement.fstFocused, .fstElement.fstActive {
    border-color: #3c8dbc !important;
    box-shadow: 0 0 4px rgba(60, 141, 188, 0.4) !important;
}
.fstMultipleMode .fstControls {
    padding: 0 !important;
}
.fstChoiceItem {
    background-color: #3c8dbc !important;
    border: 1px solid #367fa9 !important;
    color: #ffffff !important;
    border-radius: 3px !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    padding: 3px 8px 3px 22px !important;
    margin: 2px 4px 2px 0 !important;
}
.fstChoiceRemove {
    color: #ffffff !important;
    font-size: 14px !important;
    left: 4px !important;
    opacity: 0.85;
}
.fstChoiceRemove:hover {
    opacity: 1;
}
.fstQueryInput {
    font-size: 13px !important;
    padding: 4px 6px !important;
    color: #333 !important;
}
.fstResults {
    max-height: 250px !important;
    z-index: 9999 !important;
    border-color: #d2d6de !important;
    box-shadow: 0 3px 8px rgba(0,0,0,0.15) !important;
}
.fstResultItem {
    font-size: 13px !important;
    padding: 6px 12px !important;
}
.fstResultItem.fstFocused {
    background-color: #3c8dbc !important;
    border-color: #367fa9 !important;
}
.fstResultItem.fstSelected {
    background-color: #2694f1 !important;
}
</style>

<script src="<?php echo base_url('assets/xkey/menus.js?v=' . time()); ?>"></script>