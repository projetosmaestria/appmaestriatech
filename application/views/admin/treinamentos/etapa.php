<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

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

        <div class="classroom-container">
            <div class="row">

                <!-- COLUNA PRINCIPAL: CONTEÚDO DA AULA -->
                <div class="col-md-8">

                    <!-- 1. PLAYER DE VÍDEO -->
                    <?php if ($etapa->video_tipo !== 'nenhum' && !empty($etapa->video_url)): ?>
                        <?php 
                            // Identificar ID do YouTube ou Vimeo
                            $videoEmbedUrl = '';
                            $isYoutube = false;
                            $youtubeId = '';

                            if (strpos($etapa->video_url, 'youtube.com') !== false || strpos($etapa->video_url, 'youtu.be') !== false) {
                                $isYoutube = true;
                                if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $etapa->video_url, $matches)) {
                                    $youtubeId = $matches[1];
                                    $videoEmbedUrl = 'https://www.youtube.com/embed/' . $youtubeId . '?enablejsapi=1&origin=' . urlencode(base_url()) . '&rel=0';
                                }
                            } elseif (strpos($etapa->video_url, 'vimeo.com') !== false) {
                                if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)/', $etapa->video_url, $matches)) {
                                    $vimeoId = end($matches);
                                    $videoEmbedUrl = 'https://player.vimeo.com/video/' . $vimeoId . '?api=1';
                                }
                            } else {
                                $videoEmbedUrl = $etapa->video_url;
                            }
                        ?>

                        <div class="video-box-wrapper">
                            <div class="video-responsive-container">
                                <?php if ($isYoutube && !empty($youtubeId)): ?>
                                    <div id="youtubePlayer" data-youtube-id="<?php echo $youtubeId; ?>" style="width: 100%; height: 100%;"></div>
                                <?php elseif (!empty($videoEmbedUrl)): ?>
                                    <iframe id="videoIframe" src="<?php echo $videoEmbedUrl; ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                <?php else: ?>
                                    <video id="html5Video" controls style="width: 100%; height: 100%;">
                                        <source src="<?php echo base_url($etapa->video_url); ?>">
                                        Seu navegador não suporta a tag de vídeo.
                                    </video>
                                <?php endif; ?>
                            </div>

                            <div class="video-status-bar">
                                <div>
                                    <i class="fa fa-video-camera text-primary"></i> 
                                    <span>Vídeo da Etapa</span>
                                    <?php if ($etapa->video_obrigatorio == 1): ?>
                                        <span class="label label-warning" style="margin-left: 8px;">Obrigatório</span>
                                    <?php endif; ?>
                                </div>
                                <div id="videoProgressText">
                                    <?php if (!empty($progresso_etapa) && $progresso_etapa->video_concluido == 1): ?>
                                        <span class="text-green"><i class="fa fa-check-circle"></i> Vídeo Concluído</span>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fa fa-clock-o"></i> Reproduza o conteúdo</span>
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

                    <!-- 5. AVALIAÇÃO DE SATISFAÇÃO (Exibida no final do treinamento) -->
                    <?php if ($eh_ultima_etapa || $treinamento->avaliacao_obrigatoria == 1): ?>
                    <div class="content-card" id="cardAvaliacao" style="border: 2px solid #fefcbf; background: #fffff0;">
                        <h4 style="font-size: 17px; font-weight: 800; text-align: center; color: #744210; margin: 0 0 6px 0;">
                            <i class="fa fa-star text-yellow"></i> Avaliação do Treinamento
                        </h4>
                        <p style="text-align: center; color: #975a16; font-size: 14px; margin-bottom: 15px;">
                            O que você achou deste treinamento? Sua opinião é essencial para aprimorarmos nossos conteúdos!
                        </p>

                        <form id="formAvaliacao" onsubmit="enviarAvaliacao(event)">
                            <input type="hidden" name="treinamento_id" value="<?php echo $treinamento->id; ?>">
                            <input type="hidden" id="inputNota" name="nota" value="<?php echo !empty($minha_avaliacao) ? $minha_avaliacao->nota : 5; ?>">

                            <div class="rating-container">
                                <?php 
                                    $notaAtual = !empty($minha_avaliacao) ? (int)$minha_avaliacao->nota : 5;
                                    $opcoes = [
                                        1 => ['Muito ruim', 'fa-frown-o'],
                                        2 => ['Ruim', 'fa-meh-o'],
                                        3 => ['Regular', 'fa-smile-o'],
                                        4 => ['Bom', 'fa-star-half-o'],
                                        5 => ['Excelente', 'fa-star']
                                    ];
                                ?>
                                <?php foreach ($opcoes as $val => $opt): ?>
                                    <div class="star-btn <?php echo ($notaAtual == $val) ? 'selected' : ''; ?>" onclick="selecionarNota(<?php echo $val; ?>)">
                                        <i class="fa <?php echo $opt[1]; ?>"></i>
                                        <span><?php echo $opt[0]; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="form-group" style="max-width: 600px; margin: 0 auto 15px auto;">
                                <label style="font-size: 13px; color: #744210; font-weight: 600;">Quer deixar alguma sugestão ou comentário? (Opcional)</label>
                                <textarea name="comentario" id="inputComentario" rows="2" class="form-control" placeholder="Conte-nos o que achou, dúvidas que surgiram ou sugestões de melhoria..." style="border-radius: 8px;"><?php echo htmlspecialchars($minha_avaliacao->comentario ?? ''); ?></textarea>
                            </div>

                            <div class="text-center">
                                <button type="submit" id="btnSalvarAvaliacao" class="btn btn-warning" style="font-weight: 700; border-radius: 20px; padding: 7px 25px;">
                                    <i class="fa fa-send"></i> Enviar Avaliação
                                </button>
                                <span id="msgFeedbackAvaliacao" style="margin-left: 10px; font-weight: 700; color: #276749; display: none;"></span>
                            </div>
                        </form>
                    </div>
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
                                   <?php if ($isLocked): ?>onclick="alert('Você precisa concluir as etapas anteriores antes de acessar esta etapa.');"<?php endif; ?>>
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

<!-- SCRIPTS DE INTEGRAÇÃO DO PLAYER, ATIVIDADES E AVALIAÇÃO -->
<script>
    var etapaId = <?php echo (int)$etapa->id; ?>;
    var treinamentoId = <?php echo (int)$treinamento->id; ?>;
    var videoObrigatorio = <?php echo $etapa->video_obrigatorio == 1 ? 'true' : 'false'; ?>;
    var videoJaConcluido = <?php echo (!empty($progresso_etapa) && $progresso_etapa->video_concluido == 1) ? 'true' : 'false'; ?>;

    // 1. YouTube Player API Integration
    var ytPlayer;
    function onYouTubeIframeAPIReady() {
        var el = document.getElementById('youtubePlayer');
        if (el) {
            var ytId = el.getAttribute('data-youtube-id');
            ytPlayer = new YT.Player('youtubePlayer', {
                videoId: ytId,
                playerVars: {
                    'rel': 0,
                    'modestbranding': 1,
                    'enablejsapi': 1
                },
                events: {
                    'onStateChange': onPlayerStateChange
                }
            });
        }
    }

    // Carregar YouTube IFrame API dinamicamente se necessário
    if (document.getElementById('youtubePlayer')) {
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }

    var progressInterval;
    function onPlayerStateChange(event) {
        if (event.data == YT.PlayerState.PLAYING) {
            progressInterval = setInterval(function() {
                if (ytPlayer && ytPlayer.getCurrentTime) {
                    var cur = Math.floor(ytPlayer.getCurrentTime());
                    var dur = Math.floor(ytPlayer.getDuration());
                    if (cur > 0 && cur % 10 === 0) {
                        salvarProgressoVideo(cur, false);
                    }
                }
            }, 1000);
        } else if (event.data == YT.PlayerState.ENDED) {
            clearInterval(progressInterval);
            var dur = ytPlayer && ytPlayer.getDuration ? Math.floor(ytPlayer.getDuration()) : 0;
            salvarProgressoVideo(dur, true);
        } else {
            clearInterval(progressInterval);
        }
    }

    // Função de envio do progresso do vídeo via AJAX
    function salvarProgressoVideo(tempo, concluido) {
        $.ajax({
            url: '<?php echo site_url("treinamentos/salvar_progresso_video"); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                etapa_id: etapaId,
                tempo_segundos: tempo,
                concluido: concluido ? 1 : 0
            },
            success: function(resp) {
                if (resp && resp.concluido) {
                    videoJaConcluido = true;
                    $('#videoProgressText').html('<span class="text-green"><i class="fa fa-check-circle"></i> Vídeo Concluído</span>');
                }
            }
        });
    }

    // 2. Checklist de Atividades Práticas Orientativas
    function toggleAtividade(ativId, element) {
        var $el = $(element);
        var $chk = $el.find('.task-checkbox');

        $.ajax({
            url: '<?php echo site_url("treinamentos/toggle_atividade"); ?>',
            type: 'POST',
            dataType: 'json',
            data: { atividade_id: ativId },
            success: function(resp) {
                if (resp && resp.success) {
                    if (resp.concluida) {
                        $el.addClass('checked');
                        $chk.prop('checked', true);
                    } else {
                        $el.removeClass('checked');
                        $chk.prop('checked', false);
                    }
                }
            }
        });
    }

    // 3. Avaliação de Satisfação
    function selecionarNota(val) {
        $('#inputNota').val(val);
        $('.star-btn').removeClass('selected');
        $('.star-btn').each(function(i, btn) {
            if ($(btn).find('span').text().trim() === getTextoNota(val)) {
                $(btn).addClass('selected');
            }
        });
    }

    function getTextoNota(v) {
        switch(v) {
            case 1: return 'Muito ruim';
            case 2: return 'Ruim';
            case 3: return 'Regular';
            case 4: return 'Bom';
            case 5: return 'Excelente';
        }
        return '';
    }

    function enviarAvaliacao(e) {
        e.preventDefault();
        var nota = $('#inputNota').val();
        var com = $('#inputComentario').val();
        var $btn = $('#btnSalvarAvaliacao');

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');

        $.ajax({
            url: '<?php echo site_url("treinamentos/avaliar"); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                treinamento_id: treinamentoId,
                nota: nota,
                comentario: com
            },
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Avaliação Salva');
                if (resp && resp.success) {
                    $('#msgFeedbackAvaliacao').text('Obrigado pelo seu feedback!').fadeIn();
                    setTimeout(function() { $('#msgFeedbackAvaliacao').fadeOut(); }, 4000);
                } else {
                    alert(resp.message || 'Erro ao salvar avaliação.');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-send"></i> Enviar Avaliação');
                alert('Erro de conexão com o servidor.');
            }
        });
    }

    // 4. Concluir Etapa com Validação Rigorosa no Backend
    function concluirEtapa(etpId) {
        var $btn = $('#btnConcluirEtapa');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Validando requisitos...');

        $.ajax({
            url: '<?php echo site_url("treinamentos/concluir_etapa"); ?>',
            type: 'POST',
            dataType: 'json',
            data: { etapa_id: etpId },
            success: function(resp) {
                if (resp && resp.success) {
                    // Avanço suave para a próxima etapa ou conclusão
                    window.location.href = resp.proxima_url;
                } else {
                    $btn.prop('disabled', false).html('Concluir e Próxima Etapa <i class="fa fa-arrow-right"></i>');
                    alert(resp.message || 'Existem requisitos obrigatórios pendentes nesta etapa.');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('Concluir e Próxima Etapa <i class="fa fa-arrow-right"></i>');
                alert('Erro de comunicação ao validar a conclusão da etapa.');
            }
        });
    }
</script>
