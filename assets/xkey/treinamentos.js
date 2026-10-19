/**
 * MaestriaTech - Módulo de Treinamentos (Academy)
 * Scripts e Comportamentos em JavaScript Puro
 * Versão: 2.0 - Sem Select2, com Busca e Filtros em Tempo Real (Zero Reload)
 */

(function($) {
    'use strict';

    // Helper universal que garante SEMPRE o uso do SweetAlert2 (Swal.fire)
    function mostrarAlerta(titulo, html, icone, callback) {
        icone = icone || 'warning';

        function executarSwal() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: icone,
                    title: titulo,
                    html: html,
                    confirmButtonColor: '#3c8dbc',
                    confirmButtonText: 'Entendido'
                }).then(function(res) {
                    if (callback) callback(res);
                });
            }
        }

        if (typeof Swal !== 'undefined') {
            executarSwal();
        } else {
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            script.onload = executarSwal;
            document.head.appendChild(script);
        }
    }
    window.mostrarAlerta = mostrarAlerta;

    // Redefine universalmente o window.alert nativo para converter qualquer alert em Swal.fire
    window.alert = function(msg) {
        mostrarAlerta('Atenção', typeof msg === 'string' ? msg.replace(/\n/g, '<br>') : msg, 'warning');
    };

    // Inicialização quando o DOM estiver pronto
    $(function() {
        initCatalogoLiveSearch();
        initBannersPreview();
        initEtapasVideoAndOrder();
        initPermissoesTreinamento();
        initSalaDeAula();
    });

    /**
     * 1. FILTRO EM TEMPO REAL DO CATÁLOGO DE TREINAMENTOS
     * Filtra instantaneamente conforme o usuário digita no campo de busca ou altera os selects,
     * sem recarregar a página e sem rolar a tela pro topo.
     */
    function initCatalogoLiveSearch() {
        var $inputBusca = $('#input_busca_catalogo');
        var $selectModulo = $('#filtro_modulo_catalogo');
        var $selectNivel = $('#filtro_nivel_catalogo');
        var $btnBusca = $('#btn_busca_catalogo');
        var $btnLimpar = $('#btn_limpar_filtros_catalogo');
        var $form = $('#form_catalogo_filtros');
        var $courses = $('.course-item-col');
        var $emptyMsg = $('#catalogo_sem_resultados');

        if (!$courses.length && !$('#catalogo_container').length) {
            return;
        }

        // Função de normalização para ignorar acentos e maiúsculas
        function normalizarTexto(txt) {
            if (!txt) return '';
            return txt.toString().toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();
        }

        // Função principal que filtra os cards no DOM
        function aplicarFiltrosCatalogo() {
            var termo = normalizarTexto($inputBusca.val());
            var moduloId = ($selectModulo.val() || '').toString().trim();
            var nivel = ($selectNivel.val() || '').toString().toLowerCase().trim();
            var totalVisiveis = 0;

            $courses.each(function() {
                var $card = $(this);
                var cardTitulo = normalizarTexto($card.data('titulo'));
                var cardDesc = normalizarTexto($card.data('desc'));
                var cardModId = ($card.data('modulo-id') || '').toString().trim();
                var cardModNome = normalizarTexto($card.data('modulo-nome'));
                var cardNivel = ($card.data('nivel') || '').toString().toLowerCase().trim();

                // 1. Checagem de texto (título, descrição, objetivo ou módulo)
                var bateTexto = true;
                if (termo.length > 0) {
                    bateTexto = (
                        cardTitulo.indexOf(termo) !== -1 ||
                        cardDesc.indexOf(termo) !== -1 ||
                        cardModNome.indexOf(termo) !== -1
                    );
                }

                // 2. Checagem do módulo
                var bateModulo = true;
                if (moduloId !== '') {
                    bateModulo = (cardModId === moduloId);
                }

                // 3. Checagem do nível
                var bateNivel = true;
                if (nivel !== '') {
                    bateNivel = (cardNivel === nivel);
                }

                // Exibir ou ocultar o card
                if (bateTexto && bateModulo && bateNivel) {
                    $card.show();
                    totalVisiveis++;
                } else {
                    $card.hide();
                }
            });

            // Exibir mensagem quando nenhum treinamento for encontrado
            if ($emptyMsg.length) {
                if (totalVisiveis === 0) {
                    $('#termo_pesquisado').text($inputBusca.val() || 'critérios selecionados');
                    $emptyMsg.show();
                } else {
                    $emptyMsg.hide();
                }
            }
        }

        // Evento em tempo real ao digitar (conforme o usuário digita)
        $inputBusca.on('input keyup paste', function() {
            aplicarFiltrosCatalogo();
        });

        // Evento ao alterar os selects de Módulo e Nível
        $selectModulo.on('change', function() {
            aplicarFiltrosCatalogo();
        });

        $selectNivel.on('change', function() {
            aplicarFiltrosCatalogo();
        });

        // Botão de busca (apenas refiltra sem submeter form)
        $btnBusca.on('click', function(e) {
            e.preventDefault();
            aplicarFiltrosCatalogo();
        });

        // Previne submit do form (para não recarregar a página e não subir ao topo)
        $form.on('submit', function(e) {
            e.preventDefault();
            aplicarFiltrosCatalogo();
            return false;
        });

        // Botão Limpar Filtros
        $btnLimpar.on('click', function(e) {
            e.preventDefault();
            $inputBusca.val('');
            $selectModulo.val('');
            $selectNivel.val('');
            aplicarFiltrosCatalogo();
        });

        // Executar uma vez no carregamento para sincronizar estados se houver termo inicial
        if ($inputBusca.val() || $selectModulo.val() || $selectNivel.val()) {
            aplicarFiltrosCatalogo();
        }
    }

    /**
     * 2. BANNERS - Prévia em Modal Grande e Upload de Imagem
     */
    function initBannersPreview() {
        $(document).on('click', '.btn-ver-modal, .banner-thumb-hover', function(e) {
            e.preventDefault();
            var imgUrl = $(this).data('img') || $(this).attr('src');
            var titulo = $(this).data('titulo') || 'Prévia do Banner';

            if (imgUrl) {
                $('#modalBannerImg').attr('src', imgUrl);
                $('#modalBannerTitle').html('<i class="fa fa-picture-o text-primary"></i> ' + $('<div>').text(titulo).html());
                $('#modalBannerPreview').modal('show');
            }
        });

        var $inputBannerImg = $('#input_banner_imagem');
        if ($inputBannerImg.length) {
            $inputBannerImg.on('change', function() {
                var file = this.files && this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(evt) {
                        $('#preview_banner_thumb').attr('src', evt.target.result);
                        $('#banner_status_badge').removeClass('label-primary').addClass('label-success').text('Nova Foto Carregada');
                        $('#banner_preview_area').slideDown(150);
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#btn_open_banner_thumb, #btn_modal_trigger').on('click', function(e) {
                e.preventDefault();
                var src = $('#preview_banner_thumb').attr('src');
                if (src) {
                    $('#modalBannerImg').attr('src', src);
                    $('#modalBannerPreview').modal('show');
                }
            });
        }
    }

    /**
     * 3. ETAPAS - Preview de Vídeo e Validação de Ordem com SweetAlert2
     */
    function initEtapasVideoAndOrder() {
        var $inputVideoUrl = $('#input_video_url');
        if ($inputVideoUrl.length) {
            function detectarVideo(url) {
                if (!url || typeof url !== 'string') return null;
                url = url.trim();

                var ytMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})/);
                if (ytMatch && ytMatch[1]) {
                    return {
                        provedor: 'youtube',
                        id: ytMatch[1],
                        thumb: 'https://img.youtube.com/vi/' + ytMatch[1] + '/mqdefault.jpg'
                    };
                }

                var vimeoMatch = url.match(/(?:vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|))(\d+)(?:$|\/|\?)/);
                if (vimeoMatch && vimeoMatch[3]) {
                    return {
                        provedor: 'vimeo',
                        id: vimeoMatch[3],
                        thumb: 'https://vumbnail.com/' + vimeoMatch[3] + '.jpg'
                    };
                }

                return null;
            }

            function atualizarPreviewVideo() {
                var url = $inputVideoUrl.val();
                var info = detectarVideo(url);

                if (info) {
                    if (info.provedor === 'youtube') {
                        $('#video_thumb_img').attr('src', info.thumb);
                        $('#video_id_span').text(info.id + ' (YouTube)');
                        $('#video_badge_status').removeClass('label-warning label-info').addClass('label-success').html('<i class="fa fa-check"></i> Vídeo do YouTube Identificado');
                        $('#video_preview_wrapper').slideDown(150);
                    } else if (info.provedor === 'vimeo') {
                        $('#video_id_span').text(info.id + ' (Vimeo)');
                        $('#video_badge_status').removeClass('label-warning label-success').addClass('label-info').html('<i class="fa fa-video-camera"></i> Vídeo do Vimeo Identificado');
                        $('#video_thumb_img').attr('src', info.thumb);
                        $('#video_preview_wrapper').slideDown(150);
                    }
                } else {
                    if (url && url.length > 5) {
                        $('#video_thumb_img').attr('src', 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="120" height="68" viewBox="0 0 120 68"><rect width="120" height="68" fill="%23e2e8f0"/><text x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" fill="%2364748b" font-family="sans-serif" font-size="10">Link Direto</text></svg>');
                        $('#video_id_span').text('Link direto de vídeo');
                        $('#video_badge_status').removeClass('label-success label-info').addClass('label-warning').html('<i class="fa fa-link"></i> Link Externo');
                        $('#video_preview_wrapper').slideDown(150);
                    } else {
                        $('#video_preview_wrapper').slideUp(150);
                    }
                }
            }

            $inputVideoUrl.on('input change paste keyup', atualizarPreviewVideo);
            atualizarPreviewVideo();
        }

        var $formEtapa = $('#form_etapa');
        if ($formEtapa.length && typeof window.etapasExistentesData !== 'undefined') {
            var formConfirmado = false;

            $formEtapa.on('submit', function(e) {
                if (formConfirmado) {
                    return true;
                }

                var ordemDigitada = parseInt($('#input_etapa_ordem').val(), 10);
                if (isNaN(ordemDigitada) || ordemDigitada < 1) {
                    return true;
                }

                var etapasExistentes = window.etapasExistentesData || [];
                var etapaAtualId = window.etapaAtualIdData || 0;
                var conflito = null;

                for (var i = 0; i < etapasExistentes.length; i++) {
                    var item = etapasExistentes[i];
                    if (parseInt(item.ordem, 10) === ordemDigitada && parseInt(item.id, 10) !== etapaAtualId) {
                        conflito = item;
                        break;
                    }
                }

                if (conflito) {
                    e.preventDefault();
                    var proximaOrdem = ordemDigitada + 1;

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Ordem já em uso!',
                            html: '<div style="text-align: left; font-size: 14px; line-height: 1.6; color: #475569;">' +
                                  '<p>A <strong>Ordem ' + ordemDigitada + '</strong> já está ocupada pela etapa:<br>' +
                                  '<span style="display:inline-block; margin-top: 4px; padding: 4px 8px; background: #fee2e2; color: #b91c1c; border-radius: 4px; font-weight: 600;">' +
                                  '<i class="fa fa-tasks"></i> ' + $('<div>').text(conflito.titulo).html() + '</span></p>' +
                                  '<p style="margin-top: 10px;">Ao salvar com esta mesma posição, o sistema irá <strong>reorganizar automaticamente</strong> as etapas:</p>' +
                                  '<ul style="padding-left: 20px; margin-bottom: 0;">' +
                                  '<li>A etapa <em>"' + $('<div>').text(conflito.titulo).html() + '"</em> passará a ser <strong>Ordem ' + proximaOrdem + '</strong>;</li>' +
                                  '<li>Todas as etapas seguintes também serão avançadas em +1 posição.</li>' +
                                  '</ul>' +
                                  '</div>',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3c8dbc',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: '<i class="fa fa-refresh"></i> Reorganizar e Salvar',
                            cancelButtonText: '<i class="fa fa-pencil"></i> Escolher Outra Ordem'
                        }).then(function(result) {
                            if (result.isConfirmed) {
                                formConfirmado = true;
                                $formEtapa.submit();
                            } else {
                                $('#input_etapa_ordem').focus().select();
                            }
                        });
                    } else {
                        if (confirm('A ordem ' + ordemDigitada + ' já está em uso pela etapa "' + conflito.titulo + '". Deseja salvar e reorganizar as seguintes automaticamente?')) {
                            formConfirmado = true;
                            $formEtapa.submit();
                        }
                    }
                    return false;
                }

                return true;
            });
        }
    }

    /**
     * 4. GESTÃO DE PERMISSÕES DO TREINAMENTO (ACESSO LIVRE vs RESTRITO)
     */
    function initPermissoesTreinamento() {
        var $selectTipo = $('#selectTipoAcesso');
        var $bloco = $('#blocoAcessoRestrito');

        if (!$selectTipo.length) {
            return;
        }

        function alternar() {
            var val = $selectTipo.val();
            if (val === 'restrito') {
                $bloco.stop(true, true).slideDown(150);
            } else {
                $bloco.stop(true, true).slideUp(150);
            }
        }

        // Estado inicial
        if ($selectTipo.val() === 'restrito') {
            $bloco.show();
        } else {
            $bloco.hide();
        }

        // Evento de alteracao direto no select
        $selectTipo.off('change.permissoes').on('change.permissoes', alternar);

        // Delegacao global no document para garantir captura mesmo apos qualquer re-render
        $(document).off('change.perm_global', '#selectTipoAcesso').on('change.perm_global', '#selectTipoAcesso', function() {
            if ($(this).val() === 'restrito') {
                $('#blocoAcessoRestrito').slideDown(150);
            } else {
                $('#blocoAcessoRestrito').slideUp(150);
            }
        });

        // Filtro rapido de grupos por digitacao
        $(document).off('input.filtroGrupos', '#filtroBuscaGrupos').on('input.filtroGrupos', '#filtroBuscaGrupos', function() {
            var termo = ($(this).val() || '').toLowerCase().trim();
            $('.item-checkbox-grupo').each(function() {
                var txt = $(this).text().toLowerCase();
                $(this).toggle(txt.indexOf(termo) !== -1);
            });
        });

        // Filtro rapido de usuarios por digitacao
        $(document).off('input.filtroUsuarios', '#filtroBuscaUsuarios').on('input.filtroUsuarios', '#filtroBuscaUsuarios', function() {
            var termo = ($(this).val() || '').toLowerCase().trim();
            $('.item-checkbox-usuario').each(function() {
                var txt = $(this).text().toLowerCase();
                $(this).toggle(txt.indexOf(termo) !== -1);
            });
        });

        // Botao Marcar Todos Grupos
        $(document).off('click.marcarG', '#btnMarcarTodosGrupos').on('click.marcarG', '#btnMarcarTodosGrupos', function(e) {
            e.preventDefault();
            $('.item-checkbox-grupo:visible .chk-grupo').prop('checked', true);
        });

        // Botao Desmarcar Grupos
        $(document).off('click.desmarcarG', '#btnDesmarcarTodosGrupos').on('click.desmarcarG', '#btnDesmarcarTodosGrupos', function(e) {
            e.preventDefault();
            $('.item-checkbox-grupo:visible .chk-grupo').prop('checked', false);
        });

        // Botao Marcar Todos Usuarios
        $(document).off('click.marcarU', '#btnMarcarTodosUsuarios').on('click.marcarU', '#btnMarcarTodosUsuarios', function(e) {
            e.preventDefault();
            $('.item-checkbox-usuario:visible .chk-usuario').prop('checked', true);
        });

        // Botao Desmarcar Usuarios
        $(document).off('click.desmarcarU', '#btnDesmarcarTodosUsuarios').on('click.desmarcarU', '#btnDesmarcarTodosUsuarios', function(e) {
            e.preventDefault();
            $('.item-checkbox-usuario:visible .chk-usuario').prop('checked', false);
        });
    }

    /**
     * 5. SALA DE AULA - ETAPAS (PLAYER DE VÍDEO, ATIVIDADES E AVALIAÇÃO)
     */
    function initSalaDeAula() {
        var $classroom = $('.classroom-container');
        if (!$classroom.length) {
            return;
        }

        var etapaId = parseInt($classroom.data('etapa-id'), 10) || window.etapaId || 0;
        var treinamentoId = parseInt($classroom.data('treinamento-id'), 10) || window.treinamentoId || 0;
        var temVideo = ($classroom.data('tem-video') == '1' || window.temVideo === true);
        var duracaoEsperada = parseInt($classroom.data('video-duracao'), 10) || window.videoDuracao || 0;
        var tempoInicioPagina = Date.now();

        window.mostrarAvisoEtapaBloqueada = function() {
            mostrarAlerta(
                'Etapa Bloqueada',
                '<p style="font-size: 14px; color: #475569;">Você precisa concluir as etapas anteriores antes de acessar este conteúdo.</p>',
                'info'
            );
        };

        function formatarSegundos(sec) {
            if (!sec || sec <= 0) return 'alguns minutos';
            var min = Math.floor(sec / 60);
            var s = sec % 60;
            return min > 0 ? (min + 'min ' + (s > 0 ? s + 's' : '')) : (s + 's');
        }

        function normalizarUrlMesmaOrigem(rawUrl, fallbackPath) {
            if (rawUrl) {
                try {
                    var parsed = new URL(rawUrl, window.location.href);
                    return window.location.origin + parsed.pathname + parsed.search;
                } catch(e) {
                    return rawUrl;
                }
            }
            return fallbackPath;
        }

        var urlSalvarVideo = normalizarUrlMesmaOrigem($classroom.data('url-salvar-video'), '/treinamentos/salvar_progresso_video');
        var urlToggleAtividade = normalizarUrlMesmaOrigem($classroom.data('url-toggle-atividade'), '/treinamentos/toggle_atividade');
        var urlAvaliar = normalizarUrlMesmaOrigem($classroom.data('url-avaliar'), '/treinamentos/avaliar');
        var urlConcluir = normalizarUrlMesmaOrigem($classroom.data('url-concluir-etapa'), '/treinamentos/concluir_etapa');

        // Função de envio do progresso do vídeo
        window.salvarProgressoVideo = function(tempo, concluido) {
            var $btn = $('#btnMarcarVideoManual');
            
            $.ajax({
                url: urlSalvarVideo,
                type: 'POST',
                dataType: 'json',
                data: {
                    etapa_id: etapaId,
                    tempo_segundos: tempo || (duracaoEsperada || 300),
                    concluido: concluido ? 1 : 0
                },
                success: function(resp) {
                    if (resp && (resp.concluido || resp.success)) {
                        $('#videoProgressText').html('<span class="text-green" style="font-weight: 600;"><i class="fa fa-check-circle"></i> Vídeo Concluído</span>');
                        $btn.fadeOut(200);

                        // Atualiza os estados no DOM
                        $classroom.data('video-concluido', '1');
                        window.videoJaConcluido = true;

                        // Desbloqueia o formulário de avaliação se ainda não tiver sido avaliada
                        $('#cardAvaliacaoBloqueada').slideUp(200);
                        $('#cardAvaliacaoForm').slideDown(250);

                        if (concluido) {
                            mostrarAlerta(
                                'Vídeo Concluído!',
                                '<p style="font-size: 14px; color: #475569;">Progresso da etapa salvo com sucesso.</p>',
                                'success'
                            );
                        }
                    } else {
                        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Marcar como assistido');
                        if (concluido) {
                            mostrarAlerta('Atenção', (resp && resp.message) ? resp.message : 'Não foi possível confirmar o vídeo.', 'warning');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Falha na comunicação AJAX de progresso do vídeo:', xhr.responseText || error);
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Marcar como assistido');
                    if (concluido) {
                        mostrarAlerta('Erro de Comunicação', 'Não foi possível registrar o vídeo. Verifique se a sua sessão continua ativa.', 'error');
                    }
                }
            });
        };

        // Função para marcar o vídeo como assistido manualmente com validação rígida de tempo
        window.marcarVideoManual = function() {
            var videoJaConcluido = ($classroom.data('video-concluido') == '1' || window.videoJaConcluido === true);
            if (videoJaConcluido) {
                return;
            }

            var tempoNaPagina = Math.floor((Date.now() - tempoInicioPagina) / 1000);
            var tempoMinimoExigido = duracaoEsperada > 30 ? Math.floor(duracaoEsperada * 0.8) : 20;

            // Se ainda não assistiu ao conteúdo suficiente
            if (tempoNaPagina < tempoMinimoExigido) {
                mostrarAlerta(
                    'Vídeo em andamento!',
                    '<div style="text-align: left; font-size: 14px; line-height: 1.6; color: #475569;">' +
                    '<p>Para garantir seu aprendizado, você precisa <strong>assistir ao vídeo completo</strong> desta etapa antes de marcá-lo como concluído.</p>' +
                    '<div style="margin-top: 10px; padding: 10px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; font-size: 13px; color: #92400e;">' +
                    '<i class="fa fa-clock-o"></i> Duração do vídeo: <strong>' + formatarSegundos(duracaoEsperada) + '</strong><br>' +
                    '<i class="fa fa-hourglass-half"></i> Tempo acompanhado até o momento: <strong>' + formatarSegundos(tempoNaPagina) + '</strong>' +
                    '</div></div>',
                    'warning'
                );
                return false;
            }

            var $btn = $('#btnMarcarVideoManual');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');
            window.salvarProgressoVideo(duracaoEsperada || 300, true);
        };

        // Delegação de clique no botão manual de vídeo (garantido independentemente de inline onclick)
        $(document).off('click.marcarVideo', '#btnMarcarVideoManual').on('click.marcarVideo', '#btnMarcarVideoManual', function(e) {
            e.preventDefault();
            window.marcarVideoManual();
        });

        // Monitoramento de término de reprodução no YouTube via postMessage nativo
        window.addEventListener('message', function(event) {
            try {
                var data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
                if (data && data.event === 'infoDelivery' && data.info) {
                    // playerState === 0 é ENDED no YouTube
                    if (data.info.playerState === 0) {
                        window.salvarProgressoVideo(duracaoEsperada || 300, true);
                    }
                }
            } catch(e) {}
        });

        // 2. HTML5 Video Player
        var html5Vid = document.getElementById('html5Video');
        if (html5Vid) {
            html5Vid.addEventListener('timeupdate', function() {
                var cur = Math.floor(html5Vid.currentTime);
                if (cur > 0 && cur % 15 === 0) {
                    window.salvarProgressoVideo(cur, false);
                }
            });
            html5Vid.addEventListener('ended', function() {
                window.salvarProgressoVideo(Math.floor(html5Vid.duration || duracaoEsperada), true);
            });
        }

        // 3. Atividades Práticas Orientativas
        window.toggleAtividade = function(ativId, element) {
            var $el = $(element);
            var $chk = $el.find('.task-checkbox');

            $.ajax({
                url: urlToggleAtividade,
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
        };

        // 4. Avaliação de Satisfação
        window.selecionarNota = function(val) {
            $('#inputNota').val(val);
            $('.star-btn').removeClass('selected');
            $('.star-btn').each(function(i, btn) {
                if ($(btn).find('span').text().trim() === getTextoNota(val)) {
                    $(btn).addClass('selected');
                }
            });
        };

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

        window.enviarAvaliacao = function(e) {
            if (e && e.preventDefault) e.preventDefault();
            var nota = $('#inputNota').val();
            var com = $('#inputComentario').val();
            var $btn = $('#btnSalvarAvaliacao');
            var videoConcluido = ($classroom.data('video-concluido') == '1' || window.videoJaConcluido === true);

            // Validação no frontend: só pode avaliar depois de assistir o vídeo!
            if (temVideo && !videoConcluido) {
                mostrarAlerta(
                    'Vídeo Pendente!',
                    '<p style="font-size: 14px; color: #475569;">Você precisa assistir ao vídeo desta etapa integralmente antes de enviar a sua avaliação.</p>',
                    'warning'
                );
                return false;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');

            $.ajax({
                url: urlAvaliar,
                type: 'POST',
                dataType: 'json',
                data: {
                    treinamento_id: treinamentoId,
                    etapa_id: etapaId,
                    nota: nota,
                    comentario: com
                },
                success: function(resp) {
                    if (resp && resp.success) {
                        // Monta o card definitivo de avaliação (somente leitura)
                        var estrelasHtml = '';
                        for (var s = 1; s <= 5; s++) {
                            estrelasHtml += '<i class="fa ' + (s <= nota ? 'fa-star' : 'fa-star-o') + '"></i> ';
                        }
                        $('#estrelasDinamicas').html(estrelasHtml);
                        $('#textoNotaDinamica').text(getTextoNota(parseInt(nota, 10)) + ' (Nota ' + nota + ' de 5)');
                        if (com && com.trim()) {
                            $('#textoComentarioDinamico').text(com);
                            $('#boxComentarioDinamico').show();
                        }
                        $('#cardAvaliacaoForm').slideUp(150, function() {
                            $('#cardAvaliacaoSucessoDinamico').slideDown(200);
                        });

                        mostrarAlerta(
                            'Avaliação Registrada!',
                            '<p style="font-size: 14px; color: #475569;">Muito obrigado pela sua opinião sobre esta etapa.</p>',
                            'success'
                        );
                    } else {
                        $btn.prop('disabled', false).html('<i class="fa fa-send"></i> Enviar Avaliação');
                        mostrarAlerta('Atenção', resp.message || 'Erro ao registrar avaliação.', 'warning');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<i class="fa fa-send"></i> Enviar Avaliação');
                    mostrarAlerta('Erro de Conexão', 'Erro de conexão com o servidor ao registrar avaliação.', 'error');
                }
            });
        };

        // 5. Concluir Etapa
        window.concluirEtapa = function(etpId) {
            var videoConcluido = ($classroom.data('video-concluido') == '1' || window.videoJaConcluido === true);

            // Validação no frontend: só pode concluir e ir para a próxima se tiver assistido o vídeo todo
            if (temVideo && !videoConcluido) {
                mostrarAlerta(
                    'Vídeo Obrigatório Pendente!',
                    '<p style="font-size: 14px; color: #475569;">Você precisa assistir ao vídeo desta etapa integralmente antes de concluir e avançar para a próxima atividade.</p>',
                    'warning'
                );
                return false;
            }

            var $btn = $('#btnConcluirEtapa');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Validando requisitos...');

            $.ajax({
                url: urlConcluir,
                type: 'POST',
                dataType: 'json',
                data: { etapa_id: etpId || etapaId },
                success: function(resp) {
                    if (resp && resp.success) {
                        window.location.href = resp.proxima_url;
                    } else {
                        $btn.prop('disabled', false).html('Concluir e Próxima Etapa <i class="fa fa-arrow-right"></i>');
                        mostrarAlerta('Requisitos Pendentes', resp.message || 'Existem requisitos obrigatórios pendentes nesta etapa.', 'warning');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('Concluir e Próxima Etapa <i class="fa fa-arrow-right"></i>');
                    mostrarAlerta('Erro de Comunicação', 'Erro de comunicação ao validar a conclusão da etapa.', 'error');
                }
            });
        };
    }

})(jQuery);
