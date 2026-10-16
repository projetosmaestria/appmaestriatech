<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-graduation-cap text-primary"></i> Catálogo de Treinamentos
            <small>Plataforma de Capacitação e Procedimentos</small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>

        <!-- CSS Exclusivo Treinamentos -->
        <style>
            .treinamentos-wrapper { font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; }
            
            /* Carousel Banner - Altura Retangular Ampliada (490px) para total legibilidade */
            .treinamentos-carousel { position: relative; border-radius: 14px; overflow: hidden; margin-bottom: 25px; box-shadow: 0 6px 25px rgba(0,0,0,0.12); background: #0b1329; }
            .carousel-inner > .item { height: 490px; transition: transform .6s ease-in-out; }
            .carousel-inner > .item img { width: 100%; height: 490px; object-fit: cover; object-position: center; }
            .carousel-caption {
                position: absolute; right: auto; left: 70px; bottom: 45px; text-align: left; text-shadow: none;
                background: rgba(11, 19, 41, 0.88);
                border: 1px solid rgba(255, 255, 255, 0.12);
                padding: 32px 38px; border-radius: 14px; backdrop-filter: blur(12px); max-width: 680px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            }
            .carousel-caption h2 { font-size: 28px; font-weight: 800; margin: 0 0 12px 0; color: #ffffff; line-height: 1.28; }
            .carousel-caption p { font-size: 15.5px; margin-bottom: 22px; color: #cbd5e1; line-height: 1.6; }
            .btn-treinamento-hero { background: #3c8dbc; color: #fff; font-weight: 700; border-radius: 22px; padding: 11px 28px; font-size: 14.5px; border: none; box-shadow: 0 4px 14px rgba(60, 141, 188, 0.4); transition: all 0.2s; display: inline-block; }
            .btn-treinamento-hero:hover { background: #2f759e; color: #fff; transform: translateY(-2px); box-shadow: 0 6px 18px rgba(60, 141, 188, 0.5); }

            /* Setas do Banner perfeitamente encostadas nas bordas, sem ficar sobre o conteúdo */
            .treinamentos-carousel .carousel-control {
                width: 48px; height: 48px; top: 50%; transform: translateY(-50%);
                background: rgba(11, 19, 41, 0.75); border-radius: 50%;
                border: 1px solid rgba(255, 255, 255, 0.1);
                display: flex; align-items: center; justify-content: center;
                opacity: 0.9; transition: all 0.2s; text-shadow: none;
            }
            .treinamentos-carousel .carousel-control:hover { background: rgba(11, 19, 41, 0.95); opacity: 1; transform: translateY(-50%) scale(1.08); }
            .treinamentos-carousel .carousel-control.left { left: 15px; right: auto; background-image: none; }
            .treinamentos-carousel .carousel-control.right { right: 15px; left: auto; background-image: none; }
            .treinamentos-carousel .carousel-control .glyphicon {
                font-size: 18px; position: static; margin: 0; line-height: 1;
            }

            /* Stats Counter Bar */
            .stats-card {
                background: #fff; border-radius: 10px; padding: 18px 20px; margin-bottom: 25px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.04); border: 1px solid #eef2f5; display: flex; align-items: center;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .stats-card:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
            .stats-icon {
                width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
                font-size: 22px; margin-right: 15px; flex-shrink: 0;
            }
            .stats-icon.blue { background: rgba(60, 141, 188, 0.12); color: #3c8dbc; }
            .stats-icon.green { background: rgba(0, 166, 90, 0.12); color: #00a65a; }
            .stats-icon.orange { background: rgba(243, 156, 18, 0.12); color: #f39c12; }
            .stats-icon.purple { background: rgba(102, 51, 153, 0.12); color: #663399; }
            .stats-info h4 { margin: 0 0 3px 0; font-size: 22px; font-weight: 700; color: #222; }
            .stats-info span { font-size: 13px; color: #777; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }

            /* Section Headers */
            .section-header { margin: 15px 0 18px 0; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #f0f3f6; padding-bottom: 8px; flex-wrap: wrap; gap: 10px; }
            .section-header h3 { font-size: 18px; font-weight: 700; margin: 0; color: #2c3b41; display: flex; align-items: center; }
            .section-header h3 i { margin-right: 9px; font-size: 19px; }
            .section-header a { font-size: 13px; font-weight: 600; color: #3c8dbc; }

            /* Grid Flexível de Cursos para Alturas Homogêneas e Alinhadas */
            .row-courses-flex {
                display: flex;
                flex-wrap: wrap;
            }
            .row-courses-flex > [class*="col-"] {
                display: flex;
                flex-direction: column;
                margin-bottom: 30px;
            }

            /* Cards de Treinamentos - Fundo e Corpo Ampliados sem Corte de Informações */
            .course-card {
                background: #fff; border-radius: 14px; overflow: hidden;
                border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.06);
                transition: all 0.25s ease-in-out; display: flex; flex-direction: column;
                flex: 1; width: 100%; min-height: 510px;
            }
            .course-card:hover { transform: translateY(-5px); box-shadow: 0 14px 30px rgba(0,0,0,0.12); border-color: #cbd5e1; }
            .course-cover { position: relative; height: 215px; background: #0f172a; overflow: hidden; flex-shrink: 0; }
            .course-cover img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
            .course-card:hover .course-cover img { transform: scale(1.03); }
            
            /* Badges sobre a capa - com respiro e sem tampar o conteúdo central */
            .course-badge-module { position: absolute; top: 14px; left: 14px; background: rgba(15,23,42,0.85); color: #fff; padding: 6px 14px; border-radius: 16px; font-size: 12px; font-weight: 700; backdrop-filter: blur(6px); border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index: 5; }
            .course-badge-destaque { position: absolute; top: 14px; right: 14px; background: #f59e0b; color: #fff; padding: 6px 14px; border-radius: 16px; font-size: 12px; font-weight: 700; box-shadow: 0 4px 10px rgba(245,158,11,0.35); z-index: 5; }
            .course-badge-novo { position: absolute; top: 14px; right: 14px; background: #10b981; color: #fff; padding: 6px 14px; border-radius: 16px; font-size: 12px; font-weight: 700; box-shadow: 0 4px 10px rgba(16,185,129,0.35); z-index: 5; }
            
            /* Corpo / Fundo do Card com Tipografia Enriquecida e Espaço Generoso */
            .course-body { padding: 24px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; background: #ffffff; }
            .course-title { font-size: 17.5px; font-weight: 700; color: #0f172a; margin: 0 0 10px 0; line-height: 1.4; min-height: 50px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; }
            .course-desc { font-size: 14.5px; color: #475569; margin-bottom: 16px; line-height: 1.6; min-height: 68px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; }
            .course-meta { display: flex; align-items: center; justify-content: space-between; font-size: 13.5px; color: #64748b; font-weight: 500; margin-top: auto; padding-top: 14px; border-top: 1px dashed #e2e8f0; }
            .course-meta i { margin-right: 5px; font-size: 14px; }


            
            /* Module Cards */
            .module-card {
                background: #fff; border-radius: 10px; padding: 18px; margin-bottom: 20px;
                border: 1px solid #eef2f5; box-shadow: 0 2px 8px rgba(0,0,0,0.03);
                display: flex; align-items: center; text-decoration: none !important; color: inherit;
                transition: all 0.2s;
            }
            .module-card:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,0.08); border-color: #3c8dbc; color: #3c8dbc; }
            .module-icon {
                width: 46px; height: 46px; border-radius: 10px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
                color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 14px; flex-shrink: 0;
            }
            .module-info h4 { margin: 0 0 3px 0; font-size: 15px; font-weight: 700; color: #222; }
            .module-info span { font-size: 12px; color: #888; }

            /* Continue onde parou */
            .continue-card {
                background: #fff; border-radius: 10px; padding: 15px; margin-bottom: 20px;
                border: 1px solid #e2e8f0; box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            }
            .continue-progress { height: 7px; border-radius: 4px; margin: 10px 0 12px 0; background: #eef2f5; }

            /* Filter Bar & Inputs Unificados */
            .filter-bar { background: #fff; border-radius: 10px; padding: 16px 20px; margin-bottom: 25px; border: 1px solid #eef2f5; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
            .search-box-unified {
                position: relative; display: inline-flex; align-items: center;
                background: #fff; border: 1px solid #d2d6de; border-radius: 20px;
                padding-left: 14px; padding-right: 36px; transition: border-color 0.2s, box-shadow 0.2s;
                height: 36px; width: 280px;
            }
            .search-box-unified:focus-within {
                border-color: #3c8dbc; box-shadow: 0 0 0 2px rgba(60, 141, 188, 0.15);
            }
            .search-box-unified input {
                border: none; outline: none; background: transparent; width: 100%; font-size: 13px; color: #333;
            }
            .search-box-unified button {
                position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
                background: transparent; border: none; color: #3c8dbc; cursor: pointer; padding: 4px; font-size: 14px;
            }
        </style>

        <div class="treinamentos-wrapper">

            <!-- 1. BANNERS EM CARROSSEL -->
            <?php if (!empty($banners)): ?>
            <div id="treinamentosCarousel" class="carousel slide treinamentos-carousel" data-ride="carousel" data-interval="6000">
                <ol class="carousel-indicators">
                    <?php foreach ($banners as $index => $banner): ?>
                        <li data-target="#treinamentosCarousel" data-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>"></li>
                    <?php endforeach; ?>
                </ol>

                <div class="carousel-inner" role="listbox">
                    <?php foreach ($banners as $index => $banner): ?>
                        <div class="item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo base_url($banner->imagem); ?>" alt="<?php echo htmlspecialchars($banner->titulo); ?>">
                            <div class="carousel-caption">
                                <?php if (!empty($banner->subtitulo)): ?>
                                    <span class="badge" style="background: rgba(255,255,255,0.25); color: #fff; margin-bottom: 6px; font-size: 11px;"><?php echo htmlspecialchars($banner->subtitulo); ?></span>
                                <?php endif; ?>
                                <h2><?php echo htmlspecialchars($banner->titulo); ?></h2>
                                <p><?php echo htmlspecialchars($banner->descricao); ?></p>
                                <?php if (!empty($banner->url_botao)): ?>
                                    <a href="<?php echo site_url($banner->url_botao); ?>" class="btn btn-treinamento-hero">
                                        <?php echo !empty($banner->texto_botao) ? htmlspecialchars($banner->texto_botao) : 'Começar agora'; ?> <i class="fa fa-arrow-right" style="margin-left: 5px;"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <a class="left carousel-control" href="#treinamentosCarousel" role="button" data-slide="prev" title="Anterior">
                    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                </a>
                <a class="right carousel-control" href="#treinamentosCarousel" role="button" data-slide="next" title="Próximo">
                    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                </a>
            </div>
            <?php endif; ?>

            <!-- 2. RESUMO DE PROGRESSO DO ALUNO -->
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stats-card">
                        <div class="stats-icon green"><i class="fa fa-check-circle"></i></div>
                        <div class="stats-info">
                            <h4><?php echo $meu_progresso['concluidos']; ?></h4>
                            <span>Cursos Concluídos</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-card">
                        <div class="stats-icon blue"><i class="fa fa-spinner fa-spin"></i></div>
                        <div class="stats-info">
                            <h4><?php echo $meu_progresso['em_andamento']; ?></h4>
                            <span>Em Andamento</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-card">
                        <div class="stats-icon purple"><i class="fa fa-book"></i></div>
                        <div class="stats-info">
                            <h4><?php echo $meu_progresso['total_disponiveis']; ?></h4>
                            <span>Treinamentos Totais</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-card">
                        <div class="stats-icon orange"><i class="fa fa-pie-chart"></i></div>
                        <div class="stats-info">
                            <h4><?php echo $meu_progresso['percentual_geral']; ?>%</h4>
                            <span>Média de Conclusão</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. TREINAMENTOS EM DESTAQUE (Agora logo abaixo dos cards de contagem) -->
            <?php if (!empty($destaques)): ?>
            <div class="section-header">
                <h3><i class="fa fa-star text-yellow"></i> Treinamentos em Destaque</h3>
            </div>

            <div class="row row-courses-flex">
                <?php foreach ($destaques as $dest): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="course-card">
                        <div class="course-cover">
                            <img src="<?php echo base_url($dest->imagem_capa); ?>" alt="<?php echo htmlspecialchars($dest->titulo); ?>">
                            <span class="course-badge-module"><?php echo htmlspecialchars($dest->modulo_nome ?? 'Sistema'); ?></span>
                            <span class="course-badge-destaque"><i class="fa fa-star"></i> Destaque</span>
                        </div>
                        <div class="course-body">
                            <h4 class="course-title" title="<?php echo htmlspecialchars($dest->titulo); ?>">
                                <?php echo htmlspecialchars($dest->titulo); ?>
                            </h4>
                            <p class="course-desc">
                                <?php echo htmlspecialchars($dest->descricao_curta); ?>
                            </p>
                            <div class="course-meta">
                                <span><i class="fa fa-clock-o"></i> <?php echo $dest->carga_horaria_minutos; ?> min</span>
                                <span><i class="fa fa-list-ol"></i> <?php echo $dest->total_etapas; ?> etapas</span>
                                <span><i class="fa fa-star text-yellow"></i> <?php echo number_format($dest->media_avaliacao, 1); ?></span>
                            </div>
                            <div style="margin-top: 14px;">
                                <a href="<?php echo site_url('treinamentos/ver/' . $dest->id); ?>" class="btn btn-sm btn-primary btn-block" style="border-radius: 6px; font-weight: 600;">
                                    Acessar Treinamento
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- 4. CONTINUE DE ONDE VOCÊ PAROU (Com link ao lado do título) -->
            <?php if (!empty($em_andamento)): ?>
            <div class="section-header" style="justify-content: flex-start; gap: 15px;">
                <h3 style="margin: 0;"><i class="fa fa-history text-primary"></i> Continue de onde você parou</h3>
                <a href="<?php echo site_url('treinamentos/meu_progresso'); ?>" class="btn btn-xs btn-default" style="border-radius: 4px; padding: 4px 10px; font-weight: 600; color: #3c8dbc; border-color: #d2d6de;" title="Ver histórico completo">
                    Ver histórico completo <i class="fa fa-arrow-right" style="margin-left: 3px;"></i>
                </a>
            </div>

            <div class="row">
                <?php foreach ($em_andamento as $ea): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="continue-card">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <span class="badge" style="background:#eef2f5; color:#555;"><?php echo htmlspecialchars($ea->modulo_nome ?? 'Geral'); ?></span>
                            <span class="pull-right" style="margin-left: auto; font-size: 13px; font-weight: 700; color: #3c8dbc;"><?php echo (float)$ea->percentual; ?>%</span>
                        </div>
                        <h4 style="font-size: 15px; font-weight: 700; margin: 0 0 6px 0; color: #222; height: 38px; overflow: hidden;">
                            <?php echo htmlspecialchars($ea->titulo); ?>
                        </h4>
                        <div class="progress continue-progress">
                            <div class="progress-bar progress-bar-primary" role="progressbar" style="width: <?php echo (float)$ea->percentual; ?>%;"></div>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 12px; color: #777;">
                                <i class="fa fa-step-forward"></i> <?php echo $ea->etapas_concluidas; ?> de <?php echo $ea->total_etapas; ?> etapas
                            </span>
                            <?php if (!empty($ea->proxima_etapa)): ?>
                                <a href="<?php echo site_url('treinamentos/etapa/' . $ea->treinamento_id . '/' . $ea->proxima_etapa->id); ?>" class="btn btn-xs btn-primary" style="border-radius: 12px; padding: 4px 12px;">
                                    Continuar <i class="fa fa-play"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo site_url('treinamentos/ver/' . $ea->treinamento_id); ?>" class="btn btn-xs btn-default" style="border-radius: 12px; padding: 4px 12px;">
                                    Ver Detalhes
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- 5. EXPLORE OS MÓDULOS -->
            <?php if (!empty($modulos)): ?>
            <div class="section-header">
                <h3><i class="fa fa-th-large text-primary"></i> Explore os Módulos do Sistema</h3>
            </div>

            <div class="row">
                <?php foreach ($modulos as $mod): ?>
                <div class="col-md-3 col-sm-6">
                    <a href="<?php echo site_url('treinamentos/modulo/' . $mod->id); ?>" class="module-card">
                        <div class="module-icon">
                            <i class="fa fa-cube"></i>
                        </div>
                        <div class="module-info">
                            <h4><?php echo htmlspecialchars($mod->nome); ?></h4>
                            <span><?php echo $mod->total_treinamentos; ?> treinamento(s)</span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- 6. CATÁLOGO COMPLETO COM BUSCA E FILTROS UNIFICADOS -->
            <div class="section-header">
                <h3><i class="fa fa-book text-primary"></i> Catálogo Completo de Treinamentos</h3>
            </div>

            <!-- Barra de Filtros com Estilo Unificado e Select2 -->
            <div class="filter-bar">
                <form id="form_catalogo_filtros" onsubmit="return false;" class="form-inline" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    
                    <!-- Campo de Pesquisa com Lupa Unificada (Filtro Instantâneo sem Reload) -->
                    <div class="search-box-unified">
                        <input type="text" id="input_busca_catalogo" name="q" value="<?php echo htmlspecialchars($busca_atual ?? ''); ?>" placeholder="Pesquisar por título, objetivo, módulo..." autocomplete="off">
                        <button type="button" id="btn_busca_catalogo" title="Pesquisar">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>

                    <!-- Filtro por Módulo -->
                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="font-weight: 600; color: #555; font-size: 13px;">Módulo:</span>
                        <select name="modulo" id="filtro_modulo_catalogo" class="form-control" style="width: 260px; border-radius: 6px; height: 36px; background-color: #fff; cursor: pointer; font-size: 14px;">
                            <option value="">Todos os Módulos</option>
                            <?php foreach (($todos_modulos ?? $modulos) as $m): ?>
                                <option value="<?php echo $m->id; ?>" <?php echo ($modulo_atual == $m->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($m->nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filtro por Nível -->
                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="font-weight: 600; color: #555; font-size: 13px;">Nível:</span>
                        <select name="nivel" id="filtro_nivel_catalogo" class="form-control" style="width: 170px; border-radius: 6px; height: 36px; background-color: #fff; cursor: pointer; font-size: 14px;">
                            <option value="">Todos os Níveis</option>
                            <option value="basico" <?php echo ($nivel_atual == 'basico') ? 'selected' : ''; ?>>Básico</option>
                            <option value="intermediario" <?php echo ($nivel_atual == 'intermediario') ? 'selected' : ''; ?>>Intermediário</option>
                            <option value="avancado" <?php echo ($nivel_atual == 'avancado') ? 'selected' : ''; ?>>Avançado</option>
                        </select>
                    </div>

                    <!-- Botão Limpar Filtros -->
                    <button type="button" id="btn_limpar_filtros_catalogo" class="btn btn-default" style="border-radius: 6px; height: 36px; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa fa-times text-danger"></i> Limpar Filtros
                    </button>
                </form>
            </div>

            <!-- Grade do Catálogo -->
            <div class="row row-courses-flex" id="catalogo_container">
                <div id="catalogo_sem_resultados" class="col-xs-12 text-center" style="display: none; padding: 40px 20px; background: #fff; border-radius: 10px; border: 1px dashed #dcdcdc; width: 100%; margin-bottom: 20px;">
                    <i class="fa fa-search fa-3x text-muted" style="margin-bottom: 15px;"></i>
                    <h4 style="color: #666;">Nenhum treinamento encontrado para "<strong id="termo_pesquisado"></strong>".</h4>
                    <p style="color: #888;">Tente alterar os termos da busca ou selecionar outro módulo/nível.</p>
                </div>

                <?php if (empty($catalogo)): ?>
                    <div class="col-xs-12 text-center" style="padding: 50px 20px; background: #fff; border-radius: 10px; border: 1px dashed #dcdcdc;">
                        <i class="fa fa-search fa-3x text-muted" style="margin-bottom: 15px;"></i>
                        <h4 style="color: #666;">Nenhum treinamento cadastrado no momento.</h4>
                        <p style="color: #888;">Novos cursos serão disponibilizados em breve.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($catalogo as $cat): ?>
                    <div class="col-md-4 col-sm-6 course-item-col"
                         data-titulo="<?php echo htmlspecialchars(mb_strtolower($cat->titulo, 'UTF-8')); ?>"
                         data-desc="<?php echo htmlspecialchars(mb_strtolower(($cat->descricao_curta ?? '') . ' ' . ($cat->objetivo ?? ''), 'UTF-8')); ?>"
                         data-modulo-id="<?php echo (int)$cat->modulo_id; ?>"
                         data-modulo-nome="<?php echo htmlspecialchars(mb_strtolower($cat->modulo_nome ?? '', 'UTF-8')); ?>"
                         data-nivel="<?php echo htmlspecialchars(mb_strtolower($cat->nivel ?? 'basico', 'UTF-8')); ?>">
                        <div class="course-card">
                            <div class="course-cover">
                                <img src="<?php echo base_url($cat->imagem_capa); ?>" alt="<?php echo htmlspecialchars($cat->titulo); ?>">
                                <span class="course-badge-module"><?php echo htmlspecialchars($cat->modulo_nome ?? 'Sistema'); ?></span>
                                <?php if ($cat->novidade == 1): ?>
                                    <span class="course-badge-novo"><i class="fa fa-sparkles"></i> Novo</span>
                                <?php elseif ($cat->destaque == 1): ?>
                                    <span class="course-badge-destaque"><i class="fa fa-star"></i> Destaque</span>
                                <?php endif; ?>
                            </div>
                            <div class="course-body">
                                <h4 class="course-title" title="<?php echo htmlspecialchars($cat->titulo); ?>">
                                    <?php echo htmlspecialchars($cat->titulo); ?>
                                </h4>
                                <p class="course-desc">
                                    <?php echo htmlspecialchars($cat->descricao_curta); ?>
                                </p>

                                <?php if (!empty($cat->progresso) && $cat->progresso->status !== 'nao_iniciado'): ?>
                                    <div style="margin-bottom: 10px;">
                                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 3px;">
                                            <span style="font-weight: 600; color: #555;">Seu Progresso:</span>
                                            <span style="font-weight: 700; color: #3c8dbc;"><?php echo (float)$cat->progresso->percentual; ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 5px; margin: 0; background: #eee;">
                                            <div class="progress-bar <?php echo $cat->progresso->status === 'concluido' ? 'progress-bar-success' : 'progress-bar-primary'; ?>" style="width: <?php echo (float)$cat->progresso->percentual; ?>%;"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="course-meta">
                                    <span><i class="fa fa-clock-o"></i> <?php echo $cat->carga_horaria_minutos; ?> min</span>
                                    <span><i class="fa fa-list-ol"></i> <?php echo $cat->total_etapas; ?> etapas</span>
                                    <span class="text-capitalize label label-default"><?php echo $cat->nivel; ?></span>
                                </div>

                                <div style="margin-top: 14px;">
                                    <a href="<?php echo site_url('treinamentos/ver/' . $cat->id); ?>" class="btn btn-sm btn-primary btn-block" style="border-radius: 6px; font-weight: 600;">
                                        <?php 
                                            if (!empty($cat->progresso) && $cat->progresso->status === 'concluido') {
                                                echo '<i class="fa fa-check"></i> Concluído (Rever)';
                                            } elseif (!empty($cat->progresso) && $cat->progresso->status === 'em_andamento') {
                                                echo '<i class="fa fa-play"></i> Continuar Curso';
                                            } else {
                                                echo 'Começar Treinamento';
                                            }
                                        ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </section>
</div>
