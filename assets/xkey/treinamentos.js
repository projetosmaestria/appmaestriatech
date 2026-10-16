/**
 * MaestriaTech - Módulo de Treinamentos (Academy)
 * Scripts e Comportamentos em JavaScript Puro
 * Versão: 2.0 - Sem Select2, com Busca e Filtros em Tempo Real (Zero Reload)
 */

(function($) {
    'use strict';

    // Inicialização quando o DOM estiver pronto
    $(function() {
        initCatalogoLiveSearch();
        initBannersPreview();
        initEtapasVideoAndOrder();
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

})(jQuery);
