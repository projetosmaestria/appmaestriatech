<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-cube text-primary"></i> Módulo: <?php echo htmlspecialchars($modulo->nome); ?>
            <small>Treinamentos disponíveis</small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>

        <div style="background: #fff; border-radius: 10px; padding: 22px 25px; margin-bottom: 25px; border: 1px solid #eef2f5; box-shadow: 0 2px 10px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="margin: 0 0 6px 0; font-size: 20px; font-weight: 700; color: #2c3b41;">
                    <?php echo htmlspecialchars($modulo->nome); ?>
                </h3>
                <p style="margin: 0; color: #666; font-size: 14px;">
                    <?php echo !empty($modulo->descricao) ? htmlspecialchars($modulo->descricao) : 'Aprenda os procedimentos operacionais e funcionalidades deste módulo.'; ?>
                </p>
            </div>
            <a href="<?php echo site_url('treinamentos'); ?>" class="btn btn-default" style="border-radius: 6px;">
                <i class="fa fa-arrow-left"></i> Voltar ao Catálogo
            </a>
        </div>

        <div class="row">
            <?php if (empty($treinamentos)): ?>
                <div class="col-xs-12 text-center" style="padding: 50px 20px; background: #fff; border-radius: 10px; border: 1px dashed #dcdcdc;">
                    <i class="fa fa-folder-open-o fa-3x text-muted" style="margin-bottom: 15px;"></i>
                    <h4 style="color: #666;">Ainda não há treinamentos cadastrados para este módulo.</h4>
                    <p style="color: #888;">Em breve novos conteúdos serão disponibilizados.</p>
                </div>
            <?php else: ?>
                <?php foreach ($treinamentos as $t): ?>
                <div class="col-md-4 col-sm-6">
                    <div style="background: #fff; border-radius: 10px; overflow: hidden; margin-bottom: 25px; border: 1px solid #eef2f5; box-shadow: 0 2px 10px rgba(0,0,0,0.04); display: flex; flex-direction: column; height: 100%;">
                        <div style="position: relative; height: 165px; background: #1e293b;">
                            <img src="<?php echo base_url($t->imagem_capa); ?>" alt="<?php echo htmlspecialchars($t->titulo); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php if ($t->destaque == 1): ?>
                                <span style="position: absolute; top: 12px; right: 12px; background: #f59e0b; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;"><i class="fa fa-star"></i> Destaque</span>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 18px; flex: 1; display: flex; flex-direction: column;">
                            <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0; min-height: 44px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($t->titulo); ?>">
                                <?php echo htmlspecialchars($t->titulo); ?>
                            </h4>
                            <p style="font-size: 13px; color: #64748b; margin-bottom: 14px; min-height: 38px; line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($t->descricao_curta); ?>
                            </p>

                            <?php if (!empty($t->progresso) && $t->progresso->status !== 'nao_iniciado'): ?>
                                <div style="margin-bottom: 12px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px;">
                                        <span style="font-weight: 600; color: #555;">Progresso:</span>
                                        <span style="font-weight: 700; color: #3c8dbc;"><?php echo (float)$t->progresso->percentual; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 6px; margin: 0; background: #eee; border-radius: 3px;">
                                        <div class="progress-bar <?php echo $t->progresso->status === 'concluido' ? 'progress-bar-success' : 'progress-bar-primary'; ?>" style="width: <?php echo (float)$t->progresso->percentual; ?>%;"></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #94a3b8; margin-top: auto; padding-top: 12px; border-top: 1px dashed #e2e8f0;">
                                <span><i class="fa fa-clock-o"></i> <?php echo $t->carga_horaria_minutos; ?> min</span>
                                <span><i class="fa fa-list-ol"></i> <?php echo $t->total_etapas; ?> etapas</span>
                                <span class="text-capitalize label label-default"><?php echo $t->nivel; ?></span>
                            </div>

                            <div style="margin-top: 14px;">
                                <a href="<?php echo site_url('treinamentos/ver/' . $t->id); ?>" class="btn btn-sm btn-primary btn-block" style="border-radius: 6px; font-weight: 600;">
                                    Acessar Conteúdo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
