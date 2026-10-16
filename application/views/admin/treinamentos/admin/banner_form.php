<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-sliders text-primary"></i> <?php echo isset($banner) ? 'Editar Banner' : 'Novo Banner'; ?>
            <small>Configuração do Carrossel do Dashboard</small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>
        <?php echo validation_errors() ? show_alert_error(validation_errors()) : ''; ?>

        <div class="box box-primary" style="border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.04);">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-picture-o text-primary"></i> Informações do Banner</h3>
            </div>

            <?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal', 'id' => 'form_banner')); ?>
            <div class="box-body" style="padding: 25px 30px;">

                <!-- Título -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Título do Banner *</label>
                    <div class="col-sm-8">
                        <input type="text" name="titulo" class="form-control" required value="<?php echo set_value('titulo', $banner->titulo ?? ''); ?>" placeholder="Ex: Módulo Financeiro: Formas de Pagamento e Títulos">
                    </div>
                </div>

                <!-- Subtítulo -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Subtítulo / Badge</label>
                    <div class="col-sm-8">
                        <input type="text" name="subtitulo" class="form-control" value="<?php echo set_value('subtitulo', $banner->subtitulo ?? ''); ?>" placeholder="Ex: Novidade ou Treinamento Recomendado">
                    </div>
                </div>

                <!-- Descrição -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Descrição</label>
                    <div class="col-sm-8">
                        <textarea name="descricao" rows="3" class="form-control" placeholder="Breve texto promocional ou explicativo do destaque..."><?php echo set_value('descricao', $banner->descricao ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Imagem do Banner com Prévia em Miniatura e Modal Grande -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Imagem de Fundo</label>
                    <div class="col-sm-5">
                        <input type="file" name="imagem" id="input_banner_imagem" class="form-control" accept="image/*">
                        <small class="text-muted">Recomendado: 1200x480 pixels (panorâmico). Formatos: JPG, PNG, SVG ou WEBP.</small>
                    </div>

                    <!-- Miniatura Clicável (funciona tanto no Criar quanto no Editar) -->
                    <div class="col-sm-5" id="banner_preview_area" style="<?php echo (isset($banner) && !empty($banner->imagem)) ? '' : 'display: none;'; ?> margin-top: 5px;">
                        <div style="display: inline-flex; align-items: center; gap: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px;">
                            <div style="position: relative; display: inline-block; cursor: pointer;" id="btn_open_banner_thumb" title="Clique para ampliar em tela cheia">
                                <img id="preview_banner_thumb" 
                                     src="<?php echo (isset($banner) && !empty($banner->imagem)) ? base_url($banner->imagem) : ''; ?>" 
                                     alt="Prévia do Banner" 
                                     style="width: 130px; height: 58px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); transition: transform 0.2s ease;">
                                <span style="position: absolute; bottom: 3px; right: 3px; background: rgba(0,0,0,0.65); color: #fff; font-size: 11px; padding: 1px 5px; border-radius: 3px;">
                                    <i class="fa fa-search-plus"></i>
                                </span>
                            </div>
                            <div>
                                <span id="banner_status_badge" class="label label-primary" style="font-size: 11px;">
                                    <?php echo (isset($banner) && !empty($banner->imagem)) ? 'Imagem Atual' : 'Nova Imagem'; ?>
                                </span>
                                <div style="margin-top: 5px;">
                                    <button type="button" class="btn btn-default btn-xs" id="btn_modal_trigger" style="border-radius: 4px; font-weight: 600;">
                                        <i class="fa fa-search-plus text-primary"></i> Ampliar em Modal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Texto e URL do Botão -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Texto do Botão</label>
                    <div class="col-sm-3">
                        <input type="text" name="texto_botao" class="form-control" value="<?php echo set_value('texto_botao', $banner->texto_botao ?? 'Começar agora'); ?>" placeholder="Ex: Iniciar Treinamento">
                    </div>

                    <label class="col-sm-2 control-label">Destino (URL)</label>
                    <div class="col-sm-3">
                        <input type="text" name="url_botao" class="form-control" value="<?php echo set_value('url_botao', $banner->url_botao ?? 'treinamentos'); ?>" placeholder="Ex: treinamentos/ver/1">
                    </div>
                </div>

                <!-- Ordem e Ativo -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Ordem de Exibição</label>
                    <div class="col-sm-3">
                        <input type="number" name="ordem" class="form-control" value="<?php echo set_value('ordem', $banner->ordem ?? 1); ?>" min="1">
                    </div>

                    <label class="col-sm-2 control-label">Situação</label>
                    <div class="col-sm-3" style="padding-top: 7px;">
                        <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="ativo" value="1" <?php echo set_checkbox('ativo', '1', (!isset($banner) || $banner->ativo == 1)); ?> style="width: 17px; height: 17px; margin: 0; cursor: pointer;">
                            <span style="font-weight: 600;">Banner Ativo no Carrossel</span>
                        </label>
                    </div>
                </div>

            </div>

            <div class="box-footer" style="padding: 15px 30px; background: #f8fafc;">
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2">
                        <button type="submit" class="btn btn-primary" style="font-weight: 700; border-radius: 6px; padding: 8px 24px;">
                            <i class="fa fa-check"></i> Salvar Banner
                        </button>
                        <a href="<?php echo site_url('treinamentos_admin/banners'); ?>" class="btn btn-default" style="border-radius: 6px; margin-left: 8px;">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</div>

<!-- Modal Grande para Visualização do Banner -->
<div class="modal fade" id="modalBannerPreview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="width: 85%; max-width: 1150px;">
        <div class="modal-content" style="border-radius: 10px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="modal-header" style="background: #1e293b; color: #fff; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
                <h4 class="modal-title" style="font-weight: 700; font-size: 16px; margin: 0;">
                    <i class="fa fa-picture-o text-primary"></i> Visualização Ampliada do Banner
                </h4>
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8; font-size: 24px; margin-top: -3px;">&times;</button>
            </div>
            <div class="modal-body text-center" style="padding: 20px; background: #0f172a;">
                <img id="modalBannerImg" src="" alt="Banner Ampliado" style="max-width: 100%; max-height: 75vh; height: auto; border-radius: 6px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
            </div>
            <div class="modal-footer" style="background: #f8fafc; padding: 10px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 5px;">Fechar</button>
            </div>
        </div>
    </div>
</div>
