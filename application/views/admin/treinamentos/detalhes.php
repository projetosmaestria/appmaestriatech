<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-book text-primary"></i> <?php echo htmlspecialchars($treinamento->titulo); ?>
            <small><?php echo htmlspecialchars($treinamento->modulo_nome ?? 'Treinamento'); ?></small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>

        <style>
            /* Organização do cabeçalho padrão AdminLTE sem sobreposição */
            .content-header {
                position: relative;
                padding: 15px 15px 5px 15px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 10px;
            }
            .content-header > h1 {
                margin: 0;
                font-size: 24px;
                flex: 1 1 auto;
                min-width: 0;
            }
            .content-header > .breadcrumb {
                position: static !important;
                float: none !important;
                margin: 0 !important;
                padding: 0 !important;
                background: transparent !important;
                flex: 0 0 auto;
                white-space: nowrap;
            }
            @media (max-width: 767px) {
                .content-header {
                    display: block;
                }
                .content-header > .breadcrumb {
                    margin-top: 6px !important;
                }
            }
            .course-header-box {
                background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
                color: #fff; border-radius: 12px; padding: 30px; margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }
            .step-item {
                background: #fff; border-radius: 10px; padding: 18px 22px; margin-bottom: 14px;
                border: 1px solid #eef2f5; box-shadow: 0 2px 6px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;
                transition: all 0.2s;
            }
            .step-item:hover { transform: translateX(4px); border-color: #3c8dbc; }
            .step-icon {
                width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
                font-size: 18px; margin-right: 18px; flex-shrink: 0;
            }
            .step-icon.done { background: #e8f8f0; color: #00a65a; }
            .step-icon.current { background: #eaf3f8; color: #3c8dbc; border: 2px solid #3c8dbc; }
            .step-icon.locked { background: #f5f5f5; color: #bbb; }
            .star-rating i { color: #f39c12; font-size: 18px; }
        </style>

        <!-- Banner de Apresentação -->
        <div class="course-header-box">
            <div class="row">
                <div class="col-md-8">
                    <span class="badge" style="background: rgba(255,255,255,0.25); color: #fff; margin-bottom: 10px; font-size: 12px; padding: 5px 12px;">
                        Módulo: <?php echo htmlspecialchars($treinamento->modulo_nome ?? 'Sistema'); ?>
                    </span>
                    <h2 style="font-size: 26px; font-weight: 800; margin: 0 0 12px 0;">
                        <?php echo htmlspecialchars($treinamento->titulo); ?>
                    </h2>
                    <p style="font-size: 15px; color: rgba(255,255,255,0.9); margin-bottom: 18px; line-height: 1.5;">
                        <?php echo htmlspecialchars($treinamento->descricao_curta); ?>
                    </p>

                    <div style="display: flex; gap: 20px; font-size: 13px; color: rgba(255,255,255,0.85); flex-wrap: wrap;">
                        <span><i class="fa fa-clock-o"></i> <strong><?php echo $treinamento->carga_horaria_minutos; ?></strong> min</span>
                        <span><i class="fa fa-list-ol"></i> <strong><?php echo $treinamento->total_etapas; ?></strong> etapas</span>
                        <span><i class="fa fa-signal"></i> Nível <strong><?php echo ucfirst($treinamento->nivel); ?></strong></span>
                        <span><i class="fa fa-star text-yellow"></i> <strong><?php echo number_format($treinamento->media_avaliacao, 1); ?></strong> (<?php echo $treinamento->total_avaliacoes; ?> avaliações)</span>
                    </div>
                </div>

                <div class="col-md-4 text-center" style="margin-top: 15px;">
                    <div style="background: rgba(255,255,255,0.12); padding: 22px; border-radius: 12px; backdrop-filter: blur(5px);">
                        <h4 style="margin: 0 0 10px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Seu Progresso Atual</h4>
                        <div style="font-size: 32px; font-weight: 800; margin-bottom: 8px;">
                            <?php echo (float)($progresso->percentual ?? 0); ?>%
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px; background: rgba(255,255,255,0.25); margin-bottom: 15px;">
                            <div class="progress-bar progress-bar-success" style="width: <?php echo (float)($progresso->percentual ?? 0); ?>%;"></div>
                        </div>

                        <?php if (!empty($etapa_continuar)): ?>
                            <a href="<?php echo site_url('treinamentos/etapa/' . $treinamento->id . '/' . $etapa_continuar->id); ?>" class="btn btn-warning btn-lg btn-block" style="font-weight: 700; border-radius: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                                <i class="fa fa-play"></i> <?php echo (!empty($progresso) && $progresso->status === 'concluido') ? 'Rever Treinamento' : 'Acessar Etapas'; ?>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-default btn-lg btn-block" disabled style="border-radius: 25px;">Sem etapas ativas</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Coluna de Etapas -->
            <div class="col-md-8">
                <!-- Objetivo do Curso -->
                <?php if (!empty($treinamento->objetivo)): ?>
                <div class="box box-primary" style="border-radius: 10px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bullseye text-primary"></i> Objetivo do Treinamento</h3>
                    </div>
                    <div class="box-body" style="font-size: 14px; line-height: 1.6; color: #444;">
                        <?php echo nl2br(htmlspecialchars($treinamento->objetivo)); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Lista Ordenada de Etapas -->
                <div class="box box-default" style="border-radius: 10px;">
                    <div class="box-header with-border" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="box-title"><i class="fa fa-tasks text-primary"></i> Conteúdo Programático (Etapas)</h3>
                        <span class="label label-primary"><?php echo count($etapas); ?> Etapas</span>
                    </div>
                    <div class="box-body">
                        <?php if (empty($etapas)): ?>
                            <p class="text-muted text-center" style="padding: 20px;">Nenhuma etapa cadastrada ainda.</p>
                        <?php else: ?>
                            <?php foreach ($etapas as $index => $ep): ?>
                                <?php 
                                    $isDone = (!empty($ep->progresso) && $ep->progresso->status === 'concluido');
                                    $isLocked = !$ep->liberada;
                                ?>
                                <div class="step-item" style="<?php echo $isLocked ? 'opacity: 0.65; background: #fafafa;' : ''; ?>">
                                    <div style="display: flex; align-items: center; flex: 1;">
                                        <div class="step-icon <?php echo $isDone ? 'done' : ($isLocked ? 'locked' : 'current'); ?>">
                                            <?php if ($isDone): ?>
                                                <i class="fa fa-check"></i>
                                            <?php elseif ($isLocked): ?>
                                                <i class="fa fa-lock"></i>
                                            <?php else: ?>
                                                <i class="fa fa-play"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <span style="font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase;">Etapa <?php echo $ep->ordem; ?></span>
                                            <h4 style="margin: 2px 0 4px 0; font-size: 15px; font-weight: 700; color: #222;">
                                                <?php echo htmlspecialchars($ep->titulo); ?>
                                            </h4>
                                            <?php if (!empty($ep->descricao_curta)): ?>
                                                <p style="margin: 0; font-size: 13px; color: #666;"><?php echo htmlspecialchars($ep->descricao_curta); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div style="margin-left: 20px; text-align: right; flex-shrink: 0;">
                                        <?php if ($ep->video_obrigatorio == 1): ?>
                                            <span class="label label-warning" style="margin-right: 8px;"><i class="fa fa-video-camera"></i> Vídeo Obrigatório</span>
                                        <?php endif; ?>

                                        <?php if ($isLocked): ?>
                                            <button class="btn btn-sm btn-default" disabled style="border-radius: 6px;">
                                                <i class="fa fa-lock"></i> Bloqueado
                                            </button>
                                        <?php else: ?>
                                            <a href="<?php echo site_url('treinamentos/etapa/' . $treinamento->id . '/' . $ep->id); ?>" class="btn btn-sm <?php echo $isDone ? 'btn-default' : 'btn-primary'; ?>" style="border-radius: 6px; font-weight: 600;">
                                                <?php echo $isDone ? '<i class="fa fa-refresh"></i> Rever' : '<i class="fa fa-arrow-right"></i> Iniciar'; ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral de Informações -->
            <div class="col-md-4">
                <!-- Avaliações dos Alunos -->
                <div class="box box-warning" style="border-radius: 10px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-star text-yellow"></i> Avaliações de Satisfação</h3>
                    </div>
                    <div class="box-body text-center">
                        <div style="font-size: 42px; font-weight: 800; color: #2c3b41; margin-bottom: 4px;">
                            <?php echo number_format($estatisticas_avaliacao['media'], 1); ?>
                        </div>
                        <div class="star-rating" style="margin-bottom: 8px;">
                            <?php 
                                $roundedMedia = round($estatisticas_avaliacao['media']);
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $roundedMedia) {
                                        echo '<i class="fa fa-star"></i> ';
                                    } else {
                                        echo '<i class="fa fa-star-o text-muted"></i> ';
                                    }
                                }
                            ?>
                        </div>
                        <p class="text-muted" style="font-size: 13px;">
                            Com base em <?php echo $estatisticas_avaliacao['total']; ?> avaliação(ões) de colaboradores
                        </p>

                        <!-- Distribuicao -->
                        <div style="text-align: left; margin-top: 15px; font-size: 12px;">
                            <?php for ($n = 5; $n >= 1; $n--): ?>
                                <?php 
                                    $qtd = $estatisticas_avaliacao['distribuicao'][$n] ?? 0;
                                    $pct = $estatisticas_avaliacao['total'] > 0 ? ($qtd / $estatisticas_avaliacao['total']) * 100 : 0;
                                ?>
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <span style="width: 45px;"><?php echo $n; ?> estrelas</span>
                                    <div class="progress" style="flex: 1; height: 6px; margin: 0 10px; background: #eee;">
                                        <div class="progress-bar progress-bar-warning" style="width: <?php echo $pct; ?>%;"></div>
                                    </div>
                                    <span style="width: 25px; text-align: right; color: #888;"><?php echo $qtd; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Detalhes Adicionais -->
                <div class="box box-default" style="border-radius: 10px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle text-primary"></i> Informações do Treinamento</h3>
                    </div>
                    <div class="box-body" style="font-size: 13px;">
                        <ul class="list-unstyled" style="line-height: 2;">
                            <li><i class="fa fa-building-o text-muted"></i> <strong> Empresa:</strong> Maestria Tech</li>
                            <li><i class="fa fa-cube text-muted"></i> <strong> Módulo:</strong> <?php echo htmlspecialchars($treinamento->modulo_nome ?? 'Geral'); ?></li>
                            <li><i class="fa fa-clock-o text-muted"></i> <strong> Duração média:</strong> <?php echo $treinamento->carga_horaria_minutos; ?> minutos</li>
                            <li><i class="fa fa-unlock-alt text-muted"></i> <strong> Acesso:</strong> <?php echo ucfirst($treinamento->tipo_acesso); ?></li>
                            <li><i class="fa fa-calendar-check-o text-muted"></i> <strong> Data de Publicação:</strong> <?php echo date('d/m/Y', strtotime($treinamento->created_at)); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
