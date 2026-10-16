<?php
defined('BASEPATH') or exit('No direct script access allowed');
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
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('checklists_new'); ?></h3>
                    </div>

                    <?php echo form_open('checklists/create', array('class' => 'form-horizontal', 'id' => 'form-checklist')); ?>
                    <div class="box-body">
                        <?php if (isset($message) && $message): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <h4><i class="icon fa fa-check"></i> Sucesso!</h4>
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error) && $error): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <h4><i class="icon fa fa-ban"></i> Erro!</h4>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Dados do Checklist -->
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Informações do Checklist</h3>
                            </div>
                            <div class="box-body">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Nome *</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome do checklist" value="<?php echo set_value('nome'); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Descrição</label>
                                    <div class="col-sm-10">
                                        <textarea name="descricao" id="descricao" class="form-control" placeholder="Descrição do checklist" rows="3"><?php echo set_value('descricao'); ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Status</label>
                                    <div class="col-sm-10">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="status" id="status" value="1" <?php echo set_checkbox('status', '1', TRUE); ?>> Ativo
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Etapas do Checklist -->
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Etapas do Checklist</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-sm btn-primary" id="btnAddEtapa">
                                        <i class="fa fa-plus"></i> Adicionar Etapa
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div id="etapas-container">
                                    <!-- Primeira etapa -->
                                    <div class="etapa-item" id="etapa-0">
                                        <div style="background:#f5f5f5; padding:10px 15px; border-bottom:1px solid #ddd; overflow:hidden;">
                                            <h4 style="float:left; margin:0; font-size:16px;">Etapa 1</h4>
                                            <div style="float:right;">
                                                <button type="button" class="btn btn-sm btn-danger" style="display:none;" disabled>
                                                    <i class="fa fa-trash"></i> Remover
                                                </button>
                                            </div>
                                        </div>
                                        <div style="padding:15px;">
                                            <div style="margin-bottom:15px;">
                                                <label for="etapas_0_nome" style="display:block; margin-bottom:5px; font-weight:600;">Nome da Etapa *</label>
                                                <input type="text" name="etapas[0][nome]" id="etapas_0_nome" class="form-control" placeholder="Digite o nome da etapa" required style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;">
                                            </div>
                                            <div style="margin-bottom:15px;">
                                                <label for="etapas_0_descricao" style="display:block; margin-bottom:5px; font-weight:600;">Descrição</label>
                                                <textarea name="etapas[0][descricao]" id="etapas_0_descricao" class="form-control" rows="2" placeholder="Descrição da etapa" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;"></textarea>
                                            </div>

                                            <!-- Prazo Padrão (dias) -->
                                            <div style="margin-bottom:15px;">
                                                <label for="etapas_0_prazo_padrao" style="display:block; margin-bottom:5px; font-weight:600;">Prazo Padrão (dias)</label>
                                                <input type="number" name="etapas[0][prazo_padrao]" id="etapas_0_prazo_padrao" class="form-control" placeholder="Quantidade de dias" min="0" step="1" value="<?php echo set_value('etapas[0][prazo_padrao]'); ?>" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;">
                                                <small class="text-muted">Apenas números inteiros (dias)</small>
                                            </div>
                                        </div>
                                        <input type="hidden" name="etapas[0][ordem]" value="0">
                                        <input type="hidden" name="etapas[0][status]" value="1">
                                        <input type="hidden" name="etapas[0][responsavel]" value="">
                                        <input type="hidden" name="etapas[0][data_inicio]" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary" style="margin-right: 10px;">
                            <i class="fa fa-save"></i> Salvar
                        </button>
                        <a href="<?php echo site_url('checklists'); ?>" class="btn btn-default">
                            <i class="fa fa-times"></i> Cancelar
                        </a>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .etapa-item {
        margin-bottom: 20px;
        border: 1px solid #d2d6de;
        border-radius: 3px;
        background: #fff;
    }

    .etapa-item input:focus,
    .etapa-item textarea:focus {
        border-color: #3c8dbc;
        outline: none;
    }

    #btnAddEtapa {
        margin-bottom: 15px;
    }
</style>

<script type="text/javascript" src="<?php echo base_url('assets/xkey/checklists.js'); ?>"></script>