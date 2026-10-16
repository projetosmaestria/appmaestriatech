<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-tasks text-primary"></i> <?php echo isset($etapa) ? 'Editar Etapa' : 'Nova Etapa'; ?>
            <small><?php echo htmlspecialchars($treinamento->titulo); ?></small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>
        <?php echo validation_errors() ? show_alert_error(validation_errors()) : ''; ?>

        <div class="nav-tabs-custom" style="border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.04);">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_conteudo" data-toggle="tab"><i class="fa fa-file-text-o"></i> Conteúdo & Vídeo</a></li>
                <?php if (isset($etapa)): ?>
                    <li><a href="#tab_materiais" data-toggle="tab"><i class="fa fa-paperclip"></i> Materiais de Apoio (<?php echo count($arquivos ?? []); ?>)</a></li>
                    <li><a href="#tab_atividades" data-toggle="tab"><i class="fa fa-check-square-o"></i> Atividades Práticas (<?php echo count($atividades ?? []); ?>)</a></li>
                <?php endif; ?>
            </ul>

            <div class="tab-content" style="padding: 25px;">
                <!-- ABA 1: CONTEÚDO E VÍDEO -->
                <div class="tab-pane active" id="tab_conteudo">
                    <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form_etapa')); ?>

                    <!-- Título -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Título da Etapa *</label>
                        <div class="col-sm-9">
                            <input type="text" name="titulo" class="form-control" required value="<?php echo set_value('titulo', $etapa->titulo ?? ''); ?>" placeholder="Ex: Introdução às Formas de Pagamento">
                        </div>
                    </div>

                    <!-- Descrição Resumida -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Descrição Resumida</label>
                        <div class="col-sm-9">
                            <textarea name="descricao_curta" rows="2" class="form-control" placeholder="Resumo do procedimento apresentado nesta etapa..."><?php echo set_value('descricao_curta', $etapa->descricao_curta ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Configuração do Vídeo -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Provedor do Vídeo</label>
                        <div class="col-sm-3">
                            <select name="video_tipo" id="select_video_tipo" class="form-control" style="width: 100%; height: 34px; font-size: 14px; background-color: #fff; cursor: pointer;">
                                <option value="nenhum" <?php echo set_select('video_tipo', 'nenhum', (isset($etapa) && $etapa->video_tipo == 'nenhum')); ?>>Sem Vídeo</option>
                                <option value="youtube" <?php echo set_select('video_tipo', 'youtube', (!isset($etapa) || $etapa->video_tipo == 'youtube')); ?>>YouTube</option>
                                <option value="vimeo" <?php echo set_select('video_tipo', 'vimeo', (isset($etapa) && $etapa->video_tipo == 'vimeo')); ?>>Vimeo</option>
                                <option value="arquivo" <?php echo set_select('video_tipo', 'arquivo', (isset($etapa) && $etapa->video_tipo == 'arquivo')); ?>>Link / Arquivo Direto</option>
                            </select>
                        </div>

                        <label class="col-sm-2 control-label">URL do Vídeo</label>
                        <div class="col-sm-4">
                            <input type="text" name="video_url" id="input_video_url" class="form-control" value="<?php echo set_value('video_url', $etapa->video_url ?? ''); ?>" placeholder="Ex: https://www.youtube.com/watch?v=...">
                            
                            <!-- Miniatura do Vídeo em Tempo Real -->
                            <div id="video_preview_wrapper" style="display: none; margin-top: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <img id="video_thumb_img" src="" alt="Thumbnail" style="width: 110px; height: 62px; object-fit: cover; border-radius: 5px; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">
                                    <div>
                                        <span id="video_badge_status" class="label label-success" style="font-size: 11px;"><i class="fa fa-check"></i> Vídeo Identificado</span>
                                        <div id="video_id_preview" style="font-size: 11px; color: #64748b; margin-top: 5px;">ID: <strong id="video_id_span"></strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Duração Prevista (segundos)</label>
                        <div class="col-sm-3">
                            <input type="number" name="video_duracao_segundos" class="form-control" value="<?php echo set_value('video_duracao_segundos', $etapa->video_duracao_segundos ?? 300); ?>" min="0">
                            <small class="text-muted">Ex: 300 = 5 minutos.</small>
                        </div>

                        <label class="col-sm-2 control-label">Obrigatoriedade</label>
                        <div class="col-sm-4" style="padding-top: 5px;">
                            <label style="cursor: pointer; font-weight: 500; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; margin: 0;">
                                <input type="checkbox" name="video_obrigatorio" value="1" <?php echo set_checkbox('video_obrigatorio', '1', (!empty($etapa->video_obrigatorio))); ?> style="width: 17px; height: 17px; margin: 0; cursor: pointer;">
                                <i class="fa fa-lock text-warning" style="font-size: 15px;"></i>
                                <span>Vídeo Obrigatório para Concluir a Etapa</span>
                            </label>
                        </div>
                    </div>

                    <!-- Conteúdo Rico com Editor -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Procedimento Detalhado *</label>
                        <div class="col-sm-9">
                            <textarea name="conteudo_detalhado" id="editorConteudo" rows="10" class="form-control" placeholder="Descreva os passos operacionais, botões a clicar, telas do sistema e observações..."><?php echo set_value('conteudo_detalhado', $etapa->conteudo_detalhado ?? ''); ?></textarea>
                            <small class="text-muted">Você pode utilizar formatação HTML, listas numeradas, negrito e caixas de alerta.</small>
                        </div>
                    </div>

                    <!-- Ordem e Ativo -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Ordem da Etapa *</label>
                        <div class="col-sm-3">
                            <input type="number" name="ordem" id="input_etapa_ordem" class="form-control" value="<?php echo set_value('ordem', $etapa->ordem ?? 1); ?>" min="1" required>
                            <small class="text-muted">Posição na trilha do treinamento.</small>
                        </div>

                        <label class="col-sm-2 control-label">Situação</label>
                        <div class="col-sm-4" style="padding-top: 5px;">
                            <label style="cursor: pointer; font-weight: 500; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; margin: 0;">
                                <input type="checkbox" name="ativo" value="1" <?php echo set_checkbox('ativo', '1', (!isset($etapa) || $etapa->ativo == 1)); ?> style="width: 17px; height: 17px; margin: 0; cursor: pointer;">
                                <i class="fa fa-check-circle text-success" style="font-size: 15px;"></i>
                                <span>Etapa Ativa e Visível para os Alunos</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 30px;">
                        <div class="col-sm-9 col-sm-offset-2">
                            <button type="submit" id="btn_submit_etapa" class="btn btn-primary" style="font-weight: 700; border-radius: 6px; padding: 8px 24px;">
                                <i class="fa fa-check"></i> Salvar Etapa
                            </button>
                            <a href="<?php echo site_url('treinamentos_admin/etapas/' . $treinamento->id); ?>" class="btn btn-default" style="border-radius: 6px; margin-left: 8px;">
                                Voltar
                            </a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>

                <!-- ABA 2: MATERIAIS DE APOIO -->
                <?php if (isset($etapa)): ?>
                <div class="tab-pane" id="tab_materiais">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="box box-default" style="border-radius: 8px;">
                                <div class="box-header with-border">
                                    <h4 class="box-title"><i class="fa fa-upload"></i> Anexar Novo Documento</h4>
                                </div>
                                <?php echo form_open_multipart('treinamentos_admin/arquivo_upload/' . $etapa->id); ?>
                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Título / Identificação do Arquivo *</label>
                                        <input type="text" name="titulo" class="form-control" placeholder="Ex: Manual de Configuração de Formas de Pagamento">
                                    </div>
                                    <div class="form-group">
                                        <label>Selecionar Arquivo *</label>
                                        <input type="file" name="arquivo" class="form-control" required>
                                        <small class="text-muted">Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX, PPT, ZIP (Max: 20MB).</small>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary" style="font-weight: 600; border-radius: 6px;">
                                        <i class="fa fa-upload"></i> Enviar Arquivo
                                    </button>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <h4 style="font-weight: 700; margin-top: 0; color: #2c3b41;">Arquivos Anexados nesta Etapa</h4>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr style="background: #f8fafc; font-size: 12px; text-transform: uppercase;">
                                            <th>Título</th>
                                            <th>Arquivo Original</th>
                                            <th>Tamanho</th>
                                            <th class="text-right">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($arquivos)): ?>
                                            <tr><td colspan="4" class="text-center text-muted" style="padding: 20px;">Nenhum arquivo anexado ainda.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($arquivos as $arq): ?>
                                                <tr>
                                                    <td style="font-weight: 700; color: #333;"><?php echo htmlspecialchars($arq->titulo); ?></td>
                                                    <td><small><?php echo htmlspecialchars($arq->nome_original); ?></small></td>
                                                    <td><?php echo round($arq->tamanho_bytes / 1024, 1); ?> KB</td>
                                                    <td class="text-right">
                                                        <a href="<?php echo site_url('treinamentos/download_arquivo/' . $arq->id); ?>" class="btn btn-xs btn-default" title="Baixar">
                                                            <i class="fa fa-download"></i>
                                                        </a>
                                                        <a href="<?php echo site_url('treinamentos_admin/arquivo_delete/' . $arq->id); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Deseja excluir este arquivo?');" title="Excluir">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ABA 3: ATIVIDADES PRÁTICAS ORIENTATIVAS -->
                <div class="tab-pane" id="tab_atividades">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="box box-default" style="border-radius: 8px;">
                                <div class="box-header with-border">
                                    <h4 class="box-title"><i class="fa fa-plus-circle"></i> Nova Atividade Prática</h4>
                                </div>
                                <?php echo form_open('treinamentos_admin/atividade_salvar'); ?>
                                <input type="hidden" name="etapa_id" value="<?php echo $etapa->id; ?>">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Título da Tarefa *</label>
                                        <input type="text" name="titulo" class="form-control" required placeholder="Ex: Localizar a tela de Formas de Pagamento">
                                    </div>
                                    <div class="form-group">
                                        <label>Descrição Resumida</label>
                                        <input type="text" name="descricao" class="form-control" placeholder="Ex: Navegue pelo menu Financeiro > Configurações">
                                    </div>
                                    <div class="form-group">
                                        <label>Instruções Complementares</label>
                                        <textarea name="instrucoes" rows="3" class="form-control" placeholder="Dicas para o colaborador executar no sistema..."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Ordem</label>
                                        <input type="number" name="ordem" class="form-control" value="1" min="1">
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="obrigatoria" value="1">
                                            <strong>Tarefa Obrigatória para Concluir a Etapa</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary" style="font-weight: 600; border-radius: 6px;">
                                        <i class="fa fa-save"></i> Salvar Atividade
                                    </button>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <h4 style="font-weight: 700; margin-top: 0; color: #2c3b41;">Checklist de Atividades desta Etapa</h4>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr style="background: #f8fafc; font-size: 12px; text-transform: uppercase;">
                                            <th style="width: 50px;">Ordem</th>
                                            <th>Título / Instruções</th>
                                            <th>Tipo</th>
                                            <th class="text-right">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($atividades)): ?>
                                            <tr><td colspan="4" class="text-center text-muted" style="padding: 20px;">Nenhuma atividade prática cadastrada.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($atividades as $ativ): ?>
                                                <tr>
                                                    <td><span class="badge bg-gray"><?php echo $ativ->ordem; ?></span></td>
                                                    <td>
                                                        <div style="font-weight: 700; color: #333;"><?php echo htmlspecialchars($ativ->titulo); ?></div>
                                                        <?php if (!empty($ativ->descricao)): ?>
                                                            <small class="text-muted"><?php echo htmlspecialchars($ativ->descricao); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($ativ->obrigatoria == 1): ?>
                                                            <span class="label label-danger">Obrigatória</span>
                                                        <?php else: ?>
                                                            <span class="label label-default">Opcional</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <a href="<?php echo site_url('treinamentos_admin/atividade_delete/' . $ativ->id); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Deseja excluir esta atividade prática?');">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
</div>

<!-- SweetAlert2 e Dados para o script treinamentos.js -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.etapasExistentesData = <?php echo json_encode($etapas_existentes ?? []); ?>;
    window.etapaAtualIdData = <?php echo isset($etapa) ? (int)$etapa->id : 0; ?>;
</script>
