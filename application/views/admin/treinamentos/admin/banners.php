<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
.table-banners-container {
    overflow: visible !important;
    min-height: 400px;
    padding-bottom: 90px;
}
.banner-thumb-hover {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.banner-thumb-hover:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-sliders text-primary"></i> Banners do Carrossel
            <small>Gestão de Destaques e Campanhas do Dashboard</small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>

        <div class="box box-primary" style="border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); min-height: calc(100vh - 210px);">
            <!-- Cabeçalho com botão Novo Banner alinhado à esquerda e sem botão de voltar -->
            <div class="box-header with-border" style="padding: 15px 20px;">
                <a href="<?php echo site_url('treinamentos_admin/banner_create'); ?>" class="btn btn-primary" style="border-radius: 6px; font-weight: 600; padding: 7px 18px;">
                    <i class="fa fa-plus"></i> Novo Banner
                </a>
            </div>

            <div class="box-body table-banners-container">
                <table class="table table-hover table-striped" style="vertical-align: middle;">
                    <thead>
                        <tr style="background: #f8fafc; font-size: 13px; text-transform: uppercase; color: #4a5568;">
                            <th style="width: 130px;">Prévia</th>
                            <th>Título do Banner</th>
                            <th>Subtítulo / Descrição</th>
                            <th>Texto Botão</th>
                            <th>Destino (URL)</th>
                            <th class="text-center" style="width: 70px;">Ordem</th>
                            <th>Situação</th>
                            <th class="text-right" style="width: 90px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($banners)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted" style="padding: 40px;">
                                    <i class="fa fa-picture-o fa-3x" style="color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                                    Nenhum banner cadastrado para o carrossel.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($banners as $b): ?>
                                <tr>
                                    <td>
                                        <div style="position: relative; display: inline-block;">
                                            <img src="<?php echo base_url($b->imagem); ?>" 
                                                 alt="<?php echo htmlspecialchars($b->titulo); ?>" 
                                                 class="banner-thumb-hover btn-ver-modal"
                                                 data-img="<?php echo base_url($b->imagem); ?>"
                                                 data-titulo="<?php echo htmlspecialchars($b->titulo); ?>"
                                                 title="Clique para ampliar"
                                                 style="width: 110px; height: 50px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                            <span style="position: absolute; bottom: 3px; right: 3px; background: rgba(0,0,0,0.6); color: #fff; font-size: 10px; padding: 1px 4px; border-radius: 3px; pointer-events: none;">
                                                <i class="fa fa-search-plus"></i>
                                            </span>
                                        </div>
                                    </td>
                                    <td style="font-weight: 700; color: #2d3748;">
                                        <?php echo htmlspecialchars($b->titulo); ?>
                                    </td>
                                    <td>
                                        <small class="text-muted" style="display: block; max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php echo htmlspecialchars($b->descricao ?? $b->subtitulo ?? ''); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-gray"><?php echo htmlspecialchars($b->texto_botao ?? 'Acessar'); ?></span>
                                    </td>
                                    <td>
                                        <code style="font-size: 11px;"><?php echo htmlspecialchars($b->url_botao ?? '-'); ?></code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-navy"><?php echo $b->ordem; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($b->ativo == 1): ?>
                                            <span class="label label-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <div class="dropdown">
                                            <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 4px; padding: 4px 10px;">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right" style="box-shadow: 0 4px 16px rgba(0,0,0,0.15); border-radius: 6px; padding: 6px 0; border: 1px solid #e2e8f0;">
                                                <li>
                                                    <a href="<?php echo site_url('treinamentos_admin/banner_edit/' . $b->id); ?>" style="padding: 6px 16px;">
                                                        <i class="fa fa-pencil text-primary" style="margin-right: 6px;"></i> Editar Banner
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" class="btn-ver-modal" data-img="<?php echo base_url($b->imagem); ?>" data-titulo="<?php echo htmlspecialchars($b->titulo); ?>" style="padding: 6px 16px;">
                                                        <i class="fa fa-search-plus text-info" style="margin-right: 6px;"></i> Visualizar Imagem
                                                    </a>
                                                </li>
                                                <li class="divider" style="margin: 4px 0;"></li>
                                                <li>
                                                    <a href="<?php echo site_url('treinamentos_admin/banner_delete/' . $b->id); ?>" onclick="return confirm('Deseja realmente remover este banner?');" style="color: #dc2626; padding: 6px 16px;">
                                                        <i class="fa fa-trash text-danger" style="margin-right: 6px;"></i> Excluir Banner
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Modal Grande de Prévia do Banner -->
<div class="modal fade" id="modalBannerPreview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="width: 85%; max-width: 1150px;">
        <div class="modal-content" style="border-radius: 10px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="modal-header" style="background: #1e293b; color: #fff; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
                <h4 class="modal-title" id="modalBannerTitle" style="font-weight: 700; font-size: 16px; margin: 0;">
                    <i class="fa fa-picture-o text-primary"></i> Prévia do Banner
                </h4>
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8; font-size: 24px; margin-top: -3px;">&times;</button>
            </div>
            <div class="modal-body text-center" style="padding: 20px; background: #0f172a;">
                <img id="modalBannerImg" src="" alt="Banner Completo" style="max-width: 100%; max-height: 75vh; height: auto; border-radius: 6px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
            </div>
            <div class="modal-footer" style="background: #f8fafc; padding: 10px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 5px;">Fechar</button>
            </div>
        </div>
    </div>
</div>
