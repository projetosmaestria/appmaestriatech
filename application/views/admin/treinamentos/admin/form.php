<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-book text-primary"></i> <?php echo isset($treinamento) ? 'Editar Treinamento' : 'Novo Treinamento'; ?>
            <small><?php echo isset($treinamento) ? htmlspecialchars($treinamento->titulo) : 'Cadastro de capacitação'; ?></small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>
        <?php echo validation_errors() ? show_alert_error(validation_errors()) : ''; ?>

        <div class="box box-primary" style="border-radius: 10px;">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-pencil text-primary"></i> Dados Gerais do Treinamento</h3>
            </div>

            <?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal')); ?>
            <div class="box-body" style="padding: 25px 30px;">

                <!-- Título -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Título do Treinamento *</label>
                    <div class="col-sm-8">
                        <input type="text" name="titulo" class="form-control" required value="<?php echo set_value('titulo', $treinamento->titulo ?? ''); ?>" placeholder="Ex: Como cadastrar e configurar formas de pagamento">
                    </div>
                </div>

                <!-- Módulo do Sistema -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Módulo do Sistema *</label>
                    <div class="col-sm-8">
                        <select name="modulo_id" id="select_modulo_form" class="form-control" required style="width: 100%; font-size: 14px;">
                            <option value="">Selecione um módulo cadastrado...</option>
                            <?php foreach ($modulos as $mod): ?>
                                <option value="<?php echo $mod->id; ?>" <?php echo set_select('modulo_id', $mod->id, (isset($treinamento) && $treinamento->modulo_id == $mod->id)); ?>>
                                    <?php echo htmlspecialchars($mod->nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Descrição Resumida -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Descrição Resumida</label>
                    <div class="col-sm-8">
                        <textarea name="descricao_curta" rows="2" class="form-control" placeholder="Breve resumo exibido nos cards do catálogo..."><?php echo set_value('descricao_curta', $treinamento->descricao_curta ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Objetivo -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Objetivo do Treinamento</label>
                    <div class="col-sm-8">
                        <textarea name="objetivo" rows="3" class="form-control" placeholder="O que o colaborador aprenderá ao concluir este treinamento..."><?php echo set_value('objetivo', $treinamento->objetivo ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Descrição Completa -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Descrição Completa</label>
                    <div class="col-sm-8">
                        <textarea name="descricao_completa" rows="4" class="form-control" placeholder="Orientações complementares e detalhes do treinamento..."><?php echo set_value('descricao_completa', $treinamento->descricao_completa ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Carga Horária e Nível -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Carga Horária (min)</label>
                    <div class="col-sm-3">
                        <input type="number" name="carga_horaria_minutos" class="form-control" value="<?php echo set_value('carga_horaria_minutos', $treinamento->carga_horaria_minutos ?? 30); ?>" min="0">
                    </div>

                    <label class="col-sm-2 control-label">Nível de Dificuldade</label>
                    <div class="col-sm-3">
                        <select name="nivel" id="select_nivel_form" class="form-control" style="width: 100%; font-size: 14px;">
                            <option value="basico" <?php echo set_select('nivel', 'basico', (isset($treinamento) && $treinamento->nivel == 'basico')); ?>>Básico</option>
                            <option value="intermediario" <?php echo set_select('nivel', 'intermediario', (isset($treinamento) && $treinamento->nivel == 'intermediario')); ?>>Intermediário</option>
                            <option value="avancado" <?php echo set_select('nivel', 'avancado', (isset($treinamento) && $treinamento->nivel == 'avancado')); ?>>Avançado</option>
                        </select>
                    </div>
                </div>

                <!-- Imagem de Capa -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Imagem de Capa</label>
                    <div class="col-sm-5">
                        <input type="file" name="imagem_capa" class="form-control" accept="image/*">
                        <small class="text-muted">Recomendado: 800x450 pixels (16:9). Formatos: JPG, PNG, SVG ou WEBP.</small>
                    </div>
                    <?php if (isset($treinamento) && !empty($treinamento->imagem_capa)): ?>
                        <div class="col-sm-3">
                            <img src="<?php echo base_url($treinamento->imagem_capa); ?>" alt="Capa Atual" style="height: 60px; border-radius: 6px; border: 1px solid #ddd;">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tipo de Acesso e Ordem -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Tipo de Acesso</label>
                    <div class="col-sm-3">
                        <select name="tipo_acesso" id="select_tipo_acesso_form" class="form-control" style="width: 100%; font-size: 14px;">
                            <option value="livre" <?php echo set_select('tipo_acesso', 'livre', (!isset($treinamento) || $treinamento->tipo_acesso == 'livre')); ?>>Livre (Todos os usuários da empresa)</option>
                            <option value="restrito" <?php echo set_select('tipo_acesso', 'restrito', (isset($treinamento) && $treinamento->tipo_acesso == 'restrito')); ?>>Restrito (Usuários ou grupos específicos)</option>
                        </select>
                    </div>

                    <label class="col-sm-2 control-label">Ordem de Exibição</label>
                    <div class="col-sm-3">
                        <input type="number" name="ordem" class="form-control" value="<?php echo set_value('ordem', $treinamento->ordem ?? 1); ?>" min="0">
                    </div>
                </div>

                <!-- Flags: Destaque, Novidade, Avaliacao Obrigatoria, Ativo (Desamontoados) -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Configurações</label>
                    <div class="col-sm-8">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 22px;">
                            <div style="margin-bottom: 14px;">
                                <label style="cursor: pointer; font-weight: 500; font-size: 13px; display: inline-flex; align-items: center; gap: 10px; margin: 0;">
                                    <input type="checkbox" name="destaque" value="1" <?php echo set_checkbox('destaque', '1', (!empty($treinamento->destaque))); ?> style="width: 17px; height: 17px; margin: 0; cursor: pointer;">
                                    <i class="fa fa-star text-yellow" style="font-size: 16px; width: 18px; text-align: center;"></i>
                                    <span>Treinamento em Destaque no Dashboard</span>
                                </label>
                            </div>
                            <div style="margin-bottom: 14px;">
                                <label style="cursor: pointer; font-weight: 500; font-size: 13px; display: inline-flex; align-items: center; gap: 10px; margin: 0;">
                                    <input type="checkbox" name="novidade" value="1" <?php echo set_checkbox('novidade', '1', (!empty($treinamento->novidade))); ?> style="width: 17px; height: 17px; margin: 0; cursor: pointer;">
                                    <i class="fa fa-bolt text-green" style="font-size: 16px; width: 18px; text-align: center;"></i>
                                    <span>Marcar como Novidade / Atualização Recente</span>
                                </label>
                            </div>
                            <div style="margin-bottom: 14px;">
                                <label style="cursor: pointer; font-weight: 500; font-size: 13px; display: inline-flex; align-items: center; gap: 10px; margin: 0;">
                                    <input type="checkbox" name="avaliacao_obrigatoria" value="1" <?php echo set_checkbox('avaliacao_obrigatoria', '1', (!empty($treinamento->avaliacao_obrigatoria))); ?> style="width: 17px; height: 17px; margin: 0; cursor: pointer;">
                                    <i class="fa fa-commenting-o text-primary" style="font-size: 16px; width: 18px; text-align: center;"></i>
                                    <span>Exigir Avaliação de Satisfação ao Concluir o Treinamento</span>
                                </label>
                            </div>
                            <div>
                                <label style="cursor: pointer; font-weight: 500; font-size: 13px; display: inline-flex; align-items: center; gap: 10px; margin: 0;">
                                    <input type="checkbox" name="ativo" value="1" <?php echo set_checkbox('ativo', '1', (!isset($treinamento) || $treinamento->ativo == 1)); ?> style="width: 17px; height: 17px; margin: 0; cursor: pointer;">
                                    <i class="fa fa-check-circle text-success" style="font-size: 16px; width: 18px; text-align: center;"></i>
                                    <span>Treinamento Ativo e Disponível para Alunos</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="box-footer" style="padding: 15px 30px; background: #f8fafc;">
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2">
                        <button type="submit" class="btn btn-primary" style="font-weight: 700; border-radius: 6px; padding: 8px 24px;">
                            <i class="fa fa-check"></i> Salvar Treinamento
                        </button>
                        <a href="<?php echo site_url('treinamentos_admin'); ?>" class="btn btn-default" style="border-radius: 6px; margin-left: 8px;">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</div>
