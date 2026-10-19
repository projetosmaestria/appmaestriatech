<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- SweetAlert2 (CSS e JS) para Modais e Alertas Modernos -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-graduation-cap text-primary"></i> <?php echo htmlspecialchars($etapa->titulo); ?>
            <small><?php echo htmlspecialchars($treinamento->titulo); ?> (Etapa <?php echo $etapa->ordem; ?>)</small>
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
            .classroom-container { font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif; }
            
            /* Player Area */
            .video-box-wrapper {
                background: #000; border-radius: 12px; overflow: hidden; margin-bottom: 22px;
                box-shadow: 0 6px 25px rgba(0,0,0,0.15); position: relative;
            }
            .video-responsive-container {
                position: relative; padding-bottom: 56.25%; /* 16:9 aspect ratio */ height: 0; overflow: hidden;
            }
            .video-responsive-container iframe,
            .video-responsive-container video {
                position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;
            }
            .video-status-bar {
                background: #1e282c; color: #fff; padding: 10px 18px; display: flex; align-items: center; justify-content: space-between; font-size: 13px;
            }

            /* Content Area */
            .content-card {
                background: #fff; border-radius: 12px; padding: 25px 28px; margin-bottom: 22px;
                border: 1px solid #eef2f5; box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            }
            .content-rich-text { font-size: 15px; line-height: 1.7; color: #333; }
            .content-rich-text h2, .content-rich-text h3, .content-rich-text h4 { color: #2c3b41; font-weight: 700; margin-top: 22px; margin-bottom: 12px; }
            .content-rich-text ul, .content-rich-text ol { padding-left: 24px; margin-bottom: 18px; }
            .content-rich-text li { margin-bottom: 6px; }
            .content-rich-text .callout { border-radius: 8px; margin: 20px 0; }

            /* Materials & Downloads */
            .file-attachment-card {
                background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 10px;
                display: flex; align-items: center; justify-content: space-between; transition: all 0.2s;
            }
            .file-attachment-card:hover { background: #edf2f7; border-color: #cbd5e0; }
            .file-icon { font-size: 24px; margin-right: 14px; color: #e53e3e; }
            .file-icon.fa-file-excel-o { color: #38a169; }
            .file-icon.fa-file-word-o { color: #3182ce; }

            /* Practical Checklist */
            .task-item {
                background: #fff; border: 1px solid #edf2f7; border-radius: 8px; padding: 12px 16px; margin-bottom: 8px;
                display: flex; align-items: flex-start; transition: all 0.2s; cursor: pointer;
            }
            .task-item:hover { border-color: #3c8dbc; background: #fafcff; }
            .task-item.checked { background: #f0fff4; border-color: #c6f6d5; text-decoration: line-through; color: #718096; }
            .task-checkbox { width: 20px; height: 20px; margin-right: 14px; margin-top: 2px; cursor: pointer; flex-shrink: 0; }

            /* Sidebar Etapas */
            .step-nav-card {
                background: #fff; border-radius: 12px; padding: 18px; margin-bottom: 22px;
                border: 1px solid #eef2f5; box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            }
            .nav-step-item {
                display: flex; align-items: center; padding: 10px 12px; border-radius: 8px; margin-bottom: 6px;
                text-decoration: none !important; color: #4a5568; transition: all 0.2s; font-size: 13px; font-weight: 600;
            }
            .nav-step-item:hover { background: #edf2f7; color: #2b6cb0; }
            .nav-step-item.active { background: #ebf8ff; color: #2b6cb0; border-left: 4px solid #3182ce; }
            .nav-step-item.locked { color: #a0aec0; cursor: not-allowed; }
            .nav-step-status { margin-right: 10px; width: 22px; text-align: center; }

            /* Rating Stars */
            .rating-container { display: flex; justify-content: center; gap: 12px; margin: 20px 0; }
            .star-btn {
                background: #fff; border: 2px solid #e2e8f0; border-radius: 10px; padding: 12px 15px; cursor: pointer;
                transition: all 0.2s; text-align: center; width: 85px;
            }
            .star-btn:hover, .star-btn.selected { border-color: #f39c12; background: #fffaf0; transform: translateY(-3px); box-shadow: 0 4px 12px rgba(243, 156, 18, 0.2); }
            .star-btn i { font-size: 26px; color: #f39c12; display: block; margin-bottom: 4px; }
            .star-btn span { font-size: 11px; font-weight: 700; color: #4a5568; display: block; }
        </style>

        <div class="classroom-container"
             data-etapa-id="<?php echo (int)$etapa->id; ?>"
             data-treinamento-id="<?php echo (int)$treinamento->id; ?>"
             data-tem-video="<?php echo ($etapa->video_tipo !== 'nenhum' && !empty($etapa->video_url)) ? '1' : '0'; ?>"
             data-video-duracao="<?php echo (int)($etapa->video_duracao_segundos ?? 0); ?>"
             data-video-obrigatorio="1"
             data-video-concluido="<?php echo (!empty($progresso_etapa) && $progresso_etapa->video_concluido == 1) ? '1' : '0'; ?>"
             data-avaliacao-feita="<?php echo (!empty($minha_avaliacao)) ? '1' : '0'; ?>"
             data-url-salvar-video="<?php echo site_url('treinamentos/salvar_progresso_video'); ?>"
             data-url-toggle-atividade="<?php echo site_url('treinamentos/toggle_atividade'); ?>"
             data-url-avaliar="<?php echo site_url('treinamentos/avaliar'); ?>"
             data-url-concluir-etapa="<?php echo site_url('treinamentos/concluir_etapa'); ?>">
            <div class="row">

                <!-- COLUNA PRINCIPAL: CONTEÚDO DA AULA -->
                <div class="col-md-8">

                    <!-- 1. PLAYER DE VÍDEO -->
                    <?php if ($etapa->video_tipo !== 'nenhum' && !empty($etapa->video_url)): ?>
                        <?php 
                            $videoUrl = trim($etapa->video_url);
                            $videoEmbedUrl = '';
                            $isYoutube = false;
                            $youtubeId = '';
                            $isVimeo = false;
                            $vimeoId = '';
                            $isHtml5 = false;

                            // 1. Identificar YouTube
                            if ($etapa->video_tipo === 'youtube' || strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false) {
                                $isYoutube = true;
                                if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoUrl)) {
                                    $youtubeId = $videoUrl;
                                } elseif (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $videoUrl, $m)) {
                                    $youtubeId = $m[1];
                                }
                                if (!empty($youtubeId)) {
                                    $videoEmbedUrl = 'https://www.youtube-nocookie.com/embed/' . $youtubeId . '?rel=0';
                                }
                            } 
                            // 2. Identificar Vimeo
                            elseif ($etapa->video_tipo === 'vimeo' || strpos($videoUrl, 'vimeo.com') !== false) {
                                $isVimeo = true;
                                if (preg_match('/(?:vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|))(\d+)/i', $videoUrl, $m)) {
                                    $vimeoId = end($m);
                                    $videoEmbedUrl = 'https://player.vimeo.com/video/' . $vimeoId . '?api=1';
                                }
                            } 
                            // 3. Link Direto / HTML5
                            else {
                                $isHtml5 = true;
                                $videoEmbedUrl = (preg_match('/^https?:\/\//i', $videoUrl)) ? $videoUrl : base_url($videoUrl);
                            }
                        ?>

                        <div class="video-box-wrapper">
                            <div class="video-responsive-container">
                                <?php if ($isYoutube && !empty($youtubeId)): ?>
                                    <iframe id="youtubePlayer" 
                                            src="<?php echo $videoEmbedUrl; ?>" 
                                            title="Vídeo da Etapa"
                                            frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                            allowfullscreen 
                                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;">
                                    </iframe>
                                <?php elseif ($isVimeo && !empty($videoEmbedUrl)): ?>
                                    <iframe id="vimeoPlayer" 
                                            src="<?php echo $videoEmbedUrl; ?>" 
                                            title="Vídeo da Etapa"
                                            frameborder="0" 
                                            allow="autoplay; fullscreen; picture-in-picture" 
                                            allowfullscreen 
                                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;">
                                    </iframe>
                                <?php elseif (!empty($videoEmbedUrl)): ?>
                                    <video id="html5Video" controls style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;">
                                        <source src="<?php echo htmlspecialchars($videoEmbedUrl); ?>">
                                        Seu navegador não suporta a tag de vídeo.
                                    </video>
                                <?php else: ?>
                                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #1e293b; color: #fff;">
                                        <p class="text-muted"><i class="fa fa-info-circle"></i> O vídeo desta etapa estará disponível em breve.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="video-status-bar">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i class="fa fa-video-camera text-primary"></i> 
                                    <span>Vídeo da Etapa</span>
                                    <?php if ($etapa->video_obrigatorio == 1): ?>
                                        <span class="label label-warning">Obrigatório</span>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php if ($isYoutube && !empty($youtubeId)): ?>
                                        <a href="https://www.youtube.com/watch?v=<?php echo htmlspecialchars($youtubeId); ?>" 
                                           target="_blank" 
                                           rel="noopener noreferrer" 
                                           class="btn btn-xs btn-default" 
                                           style="font-size: 11px; border-radius: 4px; padding: 2px 8px; color: #cbd5e1; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);" 
                                           title="Caso o vídeo não carregue devido a bloqueadores de anúncios, abra diretamente no YouTube">
                                            <i class="fa fa-external-link"></i> Abrir no YouTube
                                        </a>
                                    <?php endif; ?>

                                    <div id="videoProgressText">
                                        <?php if (!empty($progresso_etapa) && $progresso_etapa->video_concluido == 1): ?>
                                            <span class="text-green" style="font-weight: 600;"><i class="fa fa-check-circle"></i> Vídeo Concluído</span>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fa fa-clock-o"></i> Reproduza o conteúdo</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (empty($progresso_etapa) || $progresso_etapa->video_concluido != 1): ?>
                                        <button type="button" id="btnMarcarVideoManual" onclick="marcarVideoManual()" class="btn btn-xs btn-success" style="font-size: 11px; font-weight: 600; border-radius: 4px; padding: 2px 10px;" title="Clique aqui caso já tenha assistido ao vídeo acima">
                                            <i class="fa fa-check"></i> Marcar como assistido
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 2. CONTEÚDO DETALHADO PASSO A PASSO -->
                    <div class="content-card">
                        <div style="border-bottom: 1px solid #edf2f7; padding-bottom: 14px; margin-bottom: 18px;">
                            <span class="label label-primary" style="font-size: 11px; padding: 4px 10px; border-radius: 12px;">Etapa <?php echo $etapa->ordem; ?></span>
                            <h2 style="font-size: 22px; font-weight: 800; color: #2c3b41; margin: 8px 0 0 0;">
                                <?php echo htmlspecialchars($etapa->titulo); ?>
                            </h2>
                            <?php if (!empty($etapa->descricao_curta)): ?>
                                <p style="font-size: 14px; color: #718096; margin: 6px 0 0 0;">
                                    <?php echo htmlspecialchars($etapa->descricao_curta); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Conteúdo Rico -->
                        <div class="content-rich-text">
                            <?php if (!empty($etapa->conteudo_detalhado)): ?>
                                <?php echo $etapa->conteudo_detalhado; ?>
                            <?php else: ?>
                                <p class="text-muted">Acompanhe as instruções apresentadas no vídeo explicativo desta etapa.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 3. MATERIAIS DE APOIO PARA DOWNLOAD -->
                    <?php if (!empty($arquivos)): ?>
                    <div class="content-card">
                        <h4 style="font-size: 16px; font-weight: 700; margin: 0 0 15px 0; color: #2c3b41;">
                            <i class="fa fa-paperclip text-primary"></i> Materiais e Documentos de Apoio
                        </h4>
                        <div>
                            <?php foreach ($arquivos as $arq): ?>
                                <?php 
                                    $icon = 'fa-file-pdf-o';
                                    if (in_array($arq->extensao, ['xls', 'xlsx', 'csv'])) $icon = 'fa-file-excel-o';
                                    elseif (in_array($arq->extensao, ['doc', 'docx'])) $icon = 'fa-file-word-o';
                                    elseif (in_array($arq->extensao, ['zip', 'rar'])) $icon = 'fa-file-archive-o';
                                ?>
                                <div class="file-attachment-card">
                                    <div style="display: flex; align-items: center;">
                                        <i class="fa <?php echo $icon; ?> file-icon"></i>
                                        <div>
                                            <div style="font-weight: 700; color: #2d3748; font-size: 14px;"><?php echo htmlspecialchars($arq->titulo); ?></div>
                                            <div style="font-size: 12px; color: #718096;">
                                                <?php echo htmlspecialchars($arq->nome_original); ?> &bull; <?php echo round($arq->tamanho_bytes / 1024, 1); ?> KB
                                            </div>
                                        </div>
                                    </div>
                                    <a href="<?php echo site_url('treinamentos/download_arquivo/' . $arq->id); ?>" class="btn btn-sm btn-default" style="border-radius: 6px; font-weight: 600;">
                                        <i class="fa fa-download"></i> Baixar
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- 4. ATIVIDADES PRÁTICAS ORIENTATIVAS -->
                    <?php if (!empty($atividades)): ?>
                    <div class="content-card">
                        <h4 style="font-size: 16px; font-weight: 700; margin: 0 0 10px 0; color: #2c3b41;">
                            <i class="fa fa-check-square-o text-green"></i> Atividades Práticas Orientativas
                        </h4>
                        <p style="font-size: 13px; color: #718096; margin-bottom: 15px;">
                            Execute os procedimentos práticos indicados abaixo no sistema e marque as tarefas conforme for concluindo.
                        </p>

                        <div>
                            <?php foreach ($atividades as $ativ): ?>
                                <?php $isCheck = in_array((int)$ativ->id, $atividades_concluidas_ids); ?>
                                <div class="task-item <?php echo $isCheck ? 'checked' : ''; ?>" onclick="toggleAtividade(<?php echo $ativ->id; ?>, this)">
                                    <input type="checkbox" class="task-checkbox" <?php echo $isCheck ? 'checked' : ''; ?> onclick="event.stopPropagation(); toggleAtividade(<?php echo $ativ->id; ?>, $(this).closest('.task-item'));">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; font-size: 14px;">
                                            <?php echo htmlspecialchars($ativ->titulo); ?>
                                            <?php if ($ativ->obrigatoria == 1): ?>
                                                <span class="label label-danger" style="margin-left: 6px; font-size: 10px;">Obrigatória</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($ativ->descricao)): ?>
                                            <div style="font-size: 13px; color: #4a5568; margin-top: 2px;">
                                                <?php echo htmlspecialchars($ativ->descricao); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($ativ->instrucoes)): ?>
                                            <div style="font-size: 12px; color: #718096; margin-top: 4px; font-style: italic;">
                                                <i class="fa fa-info-circle"></i> <?php echo htmlspecialchars($ativ->instrucoes); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- 5. AVALIAÇÃO DE SATISFAÇÃO DA ETAPA -->
                    <?php if ($eh_ultima_etapa || $treinamento->avaliacao_obrigatoria == 1): ?>
                        <?php 
                            $videoJaConcluido = (!empty($progresso_etapa) && $progresso_etapa->video_concluido == 1);
                            $temVideo = ($etapa->video_tipo !== 'nenhum' && !empty($etapa->video_url));
                            $jaAvaliou = !empty($minha_avaliacao);
                            $opcoesNotas = [
                                1 => ['Muito ruim', 'fa-frown-o'],
                                2 => ['Ruim', 'fa-meh-o'],
                                3 => ['Regular', 'fa-smile-o'],
                                4 => ['Bom', 'fa-star-half-o'],
                                5 => ['Excelente', 'fa-star']
                            ];
                        ?>

                        <!-- Cenário A: Usuário JÁ avaliou esta etapa (Modo Somente Leitura - Não permite reavaliar) -->
                        <?php if ($jaAvaliou): ?>
                            <div class="content-card" style="border: 2px solid #bbf7d0; background: #f0fdf4;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <span class="label label-success" style="font-size: 11px; padding: 4px 10px; border-radius: 12px; font-weight: 700;">
                                        <i class="fa fa-check-circle"></i> Etapa Avaliada
                                    </span>
                                    <span class="text-muted" style="font-size: 12px;">
                                        Registrada em <?php echo date('d/m/Y H:i', strtotime($minha_avaliacao->created_at)); ?>
                                    </span>
                                </div>
                                <h4 style="font-size: 16px; font-weight: 700; color: #166534; margin: 0 0 10px 0;">
                                    Sua Avaliação desta Etapa
                                </h4>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                    <div style="font-size: 20px; color: #f59e0b;">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <i class="fa <?php echo ($s <= $minha_avaliacao->nota) ? 'fa-star' : 'fa-star-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span style="font-weight: 700; color: #1e293b; font-size: 14px;">
                                        <?php echo $opcoesNotas[$minha_avaliacao->nota][0] ?? ''; ?> (Nota <?php echo $minha_avaliacao->nota; ?> de 5)
                                    </span>
                                </div>
                                <?php if (!empty($minha_avaliacao->comentario)): ?>
                                    <div style="background: #fff; border: 1px solid #dcfce7; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #334155; line-height: 1.6;">
                                        <i class="fa fa-quote-left text-muted" style="margin-right: 6px;"></i>
                                        <?php echo nl2br(htmlspecialchars($minha_avaliacao->comentario)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <!-- Cenário B: Usuário AINDA NÃO avaliou esta etapa -->
                        <?php else: ?>
                            <!-- B.1: Se tem vídeo e o vídeo AINDA NÃO foi concluído, mostra bloqueado com aviso -->
                            <div class="content-card" id="cardAvaliacaoBloqueada" style="border: 2px dashed #cbd5e1; background: #f8fafc; text-align: center; padding: 25px; <?php echo ($temVideo && !$videoJaConcluido) ? '' : 'display: none;'; ?>">
                                <div style="width: 48px; height: 48px; line-height: 48px; border-radius: 50%; background: #e2e8f0; margin: 0 auto 12px auto; font-size: 20px; color: #64748b;">
                                    <i class="fa fa-lock"></i>
                                </div>
                                <h4 style="font-size: 16px; font-weight: 700; color: #334155; margin: 0 0 6px 0;">Avaliação da Etapa Bloqueada</h4>
                                <p style="font-size: 13px; color: #64748b; max-width: 450px; margin: 0 auto;">
                                    Você precisa assistir ao vídeo desta etapa integralmente para liberar o envio da sua avaliação e comentário.
                                </p>
                            </div>

                            <!-- B.2: Formulário liberado (visível se vídeo concluído ou etapa sem vídeo) -->
                            <div class="content-card" id="cardAvaliacaoForm" style="border: 2px solid #fefcbf; background: #fffff0; <?php echo ($temVideo && !$videoJaConcluido) ? 'display: none;' : ''; ?>">
                                <h4 style="font-size: 17px; font-weight: 800; text-align: center; color: #744210; margin: 0 0 6px 0;">
                                    <i class="fa fa-star text-yellow"></i> Avaliação desta Etapa
                                </h4>
                                <p style="text-align: center; color: #975a16; font-size: 14px; margin-bottom: 15px;">
                                    O que você achou das instruções e conteúdos desta etapa? Sua opinião é essencial para aprimorarmos nossos materiais!
                                </p>

                                <form id="formAvaliacao" onsubmit="enviarAvaliacao(event)">
                                    <input type="hidden" name="treinamento_id" value="<?php echo $treinamento->id; ?>">
                                    <input type="hidden" name="etapa_id" id="inputAvaliacaoEtapaId" value="<?php echo (int)$etapa->id; ?>">
                                    <input type="hidden" id="inputNota" name="nota" value="5">

                                    <div class="rating-container">
                                        <?php foreach ($opcoesNotas as $val => $opt): ?>
                                            <div class="star-btn <?php echo (5 == $val) ? 'selected' : ''; ?>" onclick="selecionarNota(<?php echo $val; ?>)">
                                                <i class="fa <?php echo $opt[1]; ?>"></i>
                                                <span><?php echo $opt[0]; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="form-group" style="max-width: 600px; margin: 0 auto 15px auto;">
                                        <label style="font-size: 13px; color: #744210; font-weight: 600;">Quer deixar alguma sugestão ou comentário sobre esta etapa? (Opcional)</label>
                                        <textarea name="comentario" id="inputComentario" rows="2" class="form-control" placeholder="Conte-nos o que achou das instruções desta etapa, dúvidas que surgiram ou melhorias..." style="border-radius: 8px;"></textarea>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" id="btnSalvarAvaliacao" class="btn btn-warning" style="font-weight: 700; border-radius: 20px; padding: 7px 25px;">
                                            <i class="fa fa-send"></i> Enviar Avaliação
                                        </button>
                                        <span id="msgFeedbackAvaliacao" style="margin-left: 10px; font-weight: 700; color: #276749; display: none;"></span>
                                    </div>
                                </form>
                            </div>

                            <!-- Card de sucesso dinâmico após envio (substitui o form) -->
                            <div class="content-card" id="cardAvaliacaoSucessoDinamico" style="display: none; border: 2px solid #bbf7d0; background: #f0fdf4;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <span class="label label-success" style="font-size: 11px; padding: 4px 10px; border-radius: 12px; font-weight: 700;">
                                        <i class="fa fa-check-circle"></i> Etapa Avaliada
                                    </span>
                                    <span class="text-muted" style="font-size: 12px;">Agora mesmo</span>
                                </div>
                                <h4 style="font-size: 16px; font-weight: 700; color: #166534; margin: 0 0 10px 0;">
                                    Sua Avaliação desta Etapa
                                </h4>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                    <div id="estrelasDinamicas" style="font-size: 20px; color: #f59e0b;"></div>
                                    <span id="textoNotaDinamica" style="font-weight: 700; color: #1e293b; font-size: 14px;"></span>
                                </div>
                                <div id="boxComentarioDinamico" style="display: none; background: #fff; border: 1px solid #dcfce7; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #334155; line-height: 1.6;">
                                    <i class="fa fa-quote-left text-muted" style="margin-right: 6px;"></i>
                                    <span id="textoComentarioDinamico"></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- 6. BARRA DE AÇÕES INFERIOR -->
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 25px; margin-bottom: 40px; padding: 18px 24px; background: #fff; border-radius: 12px; border: 1px solid #eef2f5;">
                        <div>
                            <?php if (!empty($etapa_anterior)): ?>
                                <a href="<?php echo site_url('treinamentos/etapa/' . $treinamento->id . '/' . $etapa_anterior->id); ?>" class="btn btn-default" style="border-radius: 20px; font-weight: 600; padding: 8px 20px;">
                                    <i class="fa fa-arrow-left"></i> Etapa Anterior
                                </a>
                            <?php else: ?>
                                <a href="<?php echo site_url('treinamentos/ver/' . $treinamento->id); ?>" class="btn btn-default" style="border-radius: 20px; font-weight: 600; padding: 8px 20px;">
                                    <i class="fa fa-list"></i> Visão Geral
                                </a>
                            <?php endif; ?>
                        </div>

                        <div>
                            <button id="btnConcluirEtapa" onclick="concluirEtapa(<?php echo $etapa->id; ?>)" class="btn btn-success btn-lg" style="border-radius: 25px; font-weight: 700; padding: 10px 28px; box-shadow: 0 4px 15px rgba(0, 166, 90, 0.3);">
                                <?php if ($eh_ultima_etapa): ?>
                                    <i class="fa fa-check-circle"></i> Concluir Treinamento
                                <?php else: ?>
                                    Concluir e Próxima Etapa <i class="fa fa-arrow-right" style="margin-left: 5px;"></i>
                                <?php endif; ?>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- COLUNA LATERAL: PROGRESSO E NAVEGAÇÃO -->
                <div class="col-md-4">
                    <div class="step-nav-card">
                        <h4 style="font-size: 15px; font-weight: 700; color: #2c3b41; margin: 0 0 12px 0;">
                            <i class="fa fa-tasks text-primary"></i> Etapas do Treinamento
                        </h4>

                        <div class="nav-step-list">
                            <?php foreach ($todas_etapas as $s): ?>
                                <?php 
                                    $isCurrent = ($s->id == $etapa->id);
                                    $isDone = (!empty($s->progresso) && $s->progresso->status === 'concluido');
                                    $isLocked = !$s->liberada;
                                ?>
                                <a href="<?php echo $isLocked ? 'javascript:void(0);' : site_url('treinamentos/etapa/' . $treinamento->id . '/' . $s->id); ?>" 
                                   class="nav-step-item <?php echo $isCurrent ? 'active' : ''; ?> <?php echo $isLocked ? 'locked' : ''; ?>"
                                   <?php if ($isLocked): ?>onclick="window.mostrarAvisoEtapaBloqueada && window.mostrarAvisoEtapaBloqueada();"<?php endif; ?>>
                                    <span class="nav-step-status">
                                        <?php if ($isDone): ?>
                                            <i class="fa fa-check-circle text-green"></i>
                                        <?php elseif ($isCurrent): ?>
                                            <i class="fa fa-play-circle text-primary"></i>
                                        <?php elseif ($isLocked): ?>
                                            <i class="fa fa-lock text-muted"></i>
                                        <?php else: ?>
                                            <i class="fa fa-circle-o text-muted"></i>
                                        <?php endif; ?>
                                    </span>
                                    <span style="flex: 1;">
                                        <?php echo $s->ordem; ?>. <?php echo htmlspecialchars($s->titulo); ?>
                                    </span>
                                    <?php if ($isCurrent): ?>
                                        <span class="badge" style="background: #3182ce; font-size: 10px;">Atual</span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed #e2e8f0;">
                            <a href="<?php echo site_url('treinamentos'); ?>" class="btn btn-sm btn-default btn-block" style="border-radius: 6px;">
                                <i class="fa fa-th-large"></i> Voltar ao Catálogo de Treinamentos
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<!-- DADOS PARA INICIALIZAÇÃO DA SALA DE AULA (Processados pelo script oficial treinamentos.js) -->
<script>
    window.etapaId = <?php echo (int)$etapa->id; ?>;
    window.treinamentoId = <?php echo (int)$treinamento->id; ?>;
    window.temVideo = <?php echo ($etapa->video_tipo !== 'nenhum' && !empty($etapa->video_url)) ? 'true' : 'false'; ?>;
    window.videoDuracao = <?php echo (int)($etapa->video_duracao_segundos ?? 0); ?>;
    window.videoObrigatorio = true;
    window.videoJaConcluido = <?php echo (!empty($progresso_etapa) && $progresso_etapa->video_concluido == 1) ? 'true' : 'false'; ?>;
    window.etapaJaAvaliada = <?php echo (!empty($minha_avaliacao)) ? 'true' : 'false'; ?>;
</script>
