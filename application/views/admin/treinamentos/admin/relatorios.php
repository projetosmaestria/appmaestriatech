<?php 
defined('BASEPATH') OR exit('No direct script access allowed'); 

// Normalizacao polimorfica para evitar TypeError caso venha como stdClass ou nulo
if (is_object($resumo_avaliacoes ?? null)) {
    $resumo_avaliacoes = (array)$resumo_avaliacoes;
}
if (!isset($resumo_avaliacoes) || !is_array($resumo_avaliacoes)) {
    $resumo_avaliacoes = [];
}
$resumo_avaliacoes['media_geral'] = $resumo_avaliacoes['media_geral'] ?? $resumo_avaliacoes['media'] ?? 0;
$resumo_avaliacoes['total']       = $resumo_avaliacoes['total'] ?? $resumo_avaliacoes['total_avaliacoes'] ?? 0;
if (!isset($resumo_avaliacoes['distribuicao']) || !is_array($resumo_avaliacoes['distribuicao'])) {
    $resumo_avaliacoes['distribuicao'] = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-bar-chart text-primary"></i> Monitoramento
            <small>Acompanhamento de Alunos e Avaliações</small>
        </h1>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) echo $message; ?>

        <!-- Cards de Métricas Gerais -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="info-box" style="border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <span class="info-box-icon bg-blue" style="border-radius: 10px 0 0 10px;"><i class="fa fa-book"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Treinamentos Ativos</span>
                        <span class="info-box-number" style="font-size: 26px;"><?php echo $total_treinamentos; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box" style="border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <span class="info-box-icon bg-green" style="border-radius: 10px 0 0 10px;"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Cursos Concluídos</span>
                        <span class="info-box-number" style="font-size: 26px;"><?php echo $total_conclusoes; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box" style="border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <span class="info-box-icon bg-aqua" style="border-radius: 10px 0 0 10px;"><i class="fa fa-hourglass-half"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Em Andamento</span>
                        <span class="info-box-number" style="font-size: 26px;"><?php echo $total_em_andamento; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box" style="border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <span class="info-box-icon bg-yellow" style="border-radius: 10px 0 0 10px;"><i class="fa fa-star"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Nota Média de Satisfação</span>
                            <?php 
                                $mediaAval = is_array($resumo_avaliacoes) ? ($resumo_avaliacoes['media_geral'] ?? $resumo_avaliacoes['media'] ?? 0) : ($resumo_avaliacoes->media_geral ?? $resumo_avaliacoes->media ?? 0);
                                $totalAval = is_array($resumo_avaliacoes) ? ($resumo_avaliacoes['total'] ?? 0) : ($resumo_avaliacoes->total ?? 0);
                            ?>
                            <?php echo number_format($mediaAval, 1); ?>
                            <small style="font-size: 13px; font-weight: normal;">(<?php echo $totalAval; ?> aval.)</small>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros Gerenciais -->
        <div class="box box-default" style="border-radius: 10px; margin-bottom: 22px;">
            <div class="box-body" style="padding: 15px 20px;">
                <form id="form_relatorios_filtros" method="get" action="<?php echo site_url('treinamentos_admin/relatorios'); ?>" class="form-inline">
                    <div class="form-group" style="margin-right: 15px;">
                        <label style="margin-right: 6px;">Módulo:</label>
                        <select name="modulo_id" id="select_modulo_relatorio" class="form-control" style="width: 240px; height: 36px; border-radius: 6px; font-size: 14px; background-color: #fff; cursor: pointer;">
                            <option value="">Todos os Módulos</option>
                            <?php foreach ($modulos as $m): ?>
                                <option value="<?php echo $m->id; ?>" <?php echo ($filtro_modulo == $m->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($m->nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-right: 15px;">
                        <label style="margin-right: 6px;">Treinamento:</label>
                        <select name="treinamento_id" id="select_treinamento_relatorio" class="form-control" style="width: 260px; height: 36px; border-radius: 6px; font-size: 14px; background-color: #fff; cursor: pointer;">
                            <option value="">Todos os Treinamentos</option>
                            <?php foreach ($treinamentos as $tr): ?>
                                <option value="<?php echo $tr->id; ?>" <?php echo ($filtro_treinamento == $tr->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tr->titulo); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-right: 15px;">
                        <label style="margin-right: 6px;">Situação:</label>
                        <select name="status" id="select_status_relatorio" class="form-control" style="width: 170px; height: 36px; border-radius: 6px; font-size: 14px; background-color: #fff; cursor: pointer;">
                            <option value="">Todas</option>
                            <option value="em_andamento" <?php echo ($filtro_status === 'em_andamento') ? 'selected' : ''; ?>>Em Andamento</option>
                            <option value="concluido" <?php echo ($filtro_status === 'concluido') ? 'selected' : ''; ?>>Concluído</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="border-radius: 6px; font-weight: 600;">
                        <i class="fa fa-filter"></i> Filtrar
                    </button>
                    <?php if (!empty($filtro_modulo) || !empty($filtro_treinamento) || !empty($filtro_status)): ?>
                        <a href="<?php echo site_url('treinamentos_admin/relatorios'); ?>" class="btn btn-default" style="border-radius: 6px; margin-left: 6px;">
                            Limpar
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Abas de Relatórios -->
        <div class="nav-tabs-custom" style="border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.04);">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_progresso_alunos" data-toggle="tab"><i class="fa fa-users"></i> Progresso dos Colaboradores (<?php echo count($progresso_alunos); ?>)</a></li>
                <li><a href="#tab_avaliacoes_alunos" data-toggle="tab"><i class="fa fa-star text-yellow"></i> Avaliações & Sugestões (<?php echo count($avaliacoes); ?>)</a></li>
            </ul>

            <div class="tab-content" style="padding: 20px;">
                <!-- ABA 1: PROGRESSO DOS ALUNOS -->
                <div class="tab-pane active" id="tab_progresso_alunos">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" style="vertical-align: middle;">
                            <thead>
                                <tr style="background: #f8fafc; font-size: 13px; text-transform: uppercase; color: #4a5568;">
                                    <th>Colaborador</th>
                                    <th>Treinamento</th>
                                    <th>Módulo</th>
                                    <th>Onde Parou</th>
                                    <th>Atividade / Ação Atual</th>
                                    <th style="min-width: 130px;">Progresso</th>
                                    <th>Situação</th>
                                    <th>Início</th>
                                    <th>Última Atividade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($progresso_alunos)): ?>
                                    <tr><td colspan="9" class="text-center text-muted" style="padding: 30px;">Nenhum registro de progresso encontrado para os filtros selecionados.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($progresso_alunos as $pa): ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight: 700; color: #2d3748;">
                                                    <?php echo htmlspecialchars(($pa->first_name ?? '') . ' ' . ($pa->last_name ?? '')); ?>
                                                </div>
                                                <small class="text-muted"><?php echo htmlspecialchars($pa->email ?? ''); ?></small>
                                            </td>
                                            <td style="font-weight: 600; color: #4a5568;">
                                                <?php echo htmlspecialchars($pa->treinamento_titulo); ?>
                                            </td>
                                            <td>
                                                <span class="label label-default"><?php echo htmlspecialchars($pa->modulo_nome ?? 'Geral'); ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($pa->ultima_etapa_titulo)): ?>
                                                    <span class="badge bg-purple" style="font-size: 11px;">
                                                        <?php 
                                                            $etapaTitulo = $pa->ultima_etapa_titulo;
                                                            $etapaTituloTrunc = function_exists('character_limiter') 
                                                                ? character_limiter($etapaTitulo, 25) 
                                                                : (mb_strlen($etapaTitulo) > 25 ? mb_substr($etapaTitulo, 0, 22) . '...' : $etapaTitulo);
                                                        ?>
                                                        Etapa <?php echo $pa->ultima_etapa_ordem; ?>: <?php echo htmlspecialchars($etapaTituloTrunc); ?>
                                                    </span>
                                                <?php elseif ($pa->status === 'concluido'): ?>
                                                    <span class="badge bg-green">Todas as etapas concluídas</span>
                                                <?php else: ?>
                                                    <span class="text-muted">Início do curso</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small style="color: #2b6cb0; font-weight: 600;">
                                                    <i class="fa fa-eye"></i> <?php echo htmlspecialchars($pa->ultima_acao ?? 'Acessando treinamento'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 2px;">
                                                    <span><?php echo (float)$pa->percentual; ?>%</span>
                                                    <span>(<?php echo $pa->etapas_concluidas; ?>/<?php echo $pa->total_etapas; ?>)</span>
                                                </div>
                                                <div class="progress" style="height: 6px; margin: 0; background: #eee;">
                                                    <div class="progress-bar <?php echo $pa->status === 'concluido' ? 'progress-bar-success' : 'progress-bar-primary'; ?>" style="width: <?php echo (float)$pa->percentual; ?>%;"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($pa->status === 'concluido'): ?>
                                                    <span class="label label-success"><i class="fa fa-check"></i> Concluído</span>
                                                <?php else: ?>
                                                    <span class="label label-primary"><i class="fa fa-spinner fa-spin"></i> Em Andamento</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo !empty($pa->data_inicio) ? date('d/m/Y H:i', strtotime($pa->data_inicio)) : '-'; ?></td>
                                            <td><?php echo !empty($pa->ultima_atividade) ? date('d/m/Y H:i', strtotime($pa->ultima_atividade)) : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ABA 2: AVALIAÇÕES E SUGESTÕES -->
                <div class="tab-pane" id="tab_avaliacoes_alunos">
                    <!-- Distribuicao Geral -->
                    <div class="row" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #edf2f7;">
                        <div class="col-md-4 text-center">
                            <div style="font-size: 48px; font-weight: 800; color: #2c3b41;">
                                <?php echo number_format($resumo_avaliacoes['media_geral'] ?? $resumo_avaliacoes['media'] ?? 0, 1); ?>
                            </div>
                            <div style="color: #f39c12; font-size: 20px; margin-bottom: 6px;">
                                <?php 
                                    $mInt = round($resumo_avaliacoes['media_geral'] ?? $resumo_avaliacoes['media'] ?? 0);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= $mInt) ? '<i class="fa fa-star"></i> ' : '<i class="fa fa-star-o text-muted"></i> ';
                                    }
                                ?>
                            </div>
                            <span class="text-muted">Total de <?php echo $resumo_avaliacoes['total'] ?? 0; ?> avaliações</span>
                        </div>

                        <div class="col-md-6">
                            <?php for ($star = 5; $star >= 1; $star--): ?>
                                <?php 
                                    $qtdS = $resumo_avaliacoes['distribuicao'][$star] ?? 0;
                                    $totalAv = $resumo_avaliacoes['total'] ?? 0;
                                    $pctS = $totalAv > 0 ? ($qtdS / $totalAv) * 100 : 0;
                                ?>
                                <div style="display: flex; align-items: center; margin-bottom: 5px; font-size: 13px;">
                                    <span style="width: 75px; font-weight: 600;"><?php echo $star; ?> estrelas</span>
                                    <div class="progress" style="flex: 1; height: 8px; margin: 0 12px; background: #eee;">
                                        <div class="progress-bar progress-bar-warning" style="width: <?php echo $pctS; ?>%;"></div>
                                    </div>
                                    <span style="width: 40px; text-align: right; color: #718096;"><?php echo $qtdS; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Tabela de Avaliações -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr style="background: #f8fafc; font-size: 13px; text-transform: uppercase; color: #4a5568;">
                                    <th>Colaborador</th>
                                    <th>Treinamento</th>
                                    <th>Módulo</th>
                                    <th>Nota Atribuída</th>
                                    <th>Comentário / Sugestão</th>
                                    <th>Data e Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($avaliacoes)): ?>
                                    <tr><td colspan="6" class="text-center text-muted" style="padding: 30px;">Nenhuma avaliação registrada com estes filtros.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($avaliacoes as $av): ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight: 700; color: #2d3748;">
                                                    <?php echo htmlspecialchars(($av->first_name ?? '') . ' ' . ($av->last_name ?? '')); ?>
                                                </div>
                                                <small class="text-muted"><?php echo htmlspecialchars($av->email ?? ''); ?></small>
                                            </td>
                                            <td style="font-weight: 600;">
                                                <?php echo htmlspecialchars($av->treinamento_titulo); ?>
                                            </td>
                                            <td>
                                                <span class="label label-default"><?php echo htmlspecialchars($av->modulo_nome ?? 'Geral'); ?></span>
                                            </td>
                                            <td>
                                                <div style="color: #f39c12; font-size: 14px;">
                                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                                        <i class="fa <?php echo ($s <= $av->nota) ? 'fa-star' : 'fa-star-o text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                    <strong style="margin-left: 5px; color: #333;"><?php echo $av->nota; ?>.0</strong>
                                                </div>
                                            </td>
                                            <td style="max-width: 320px;">
                                                <?php if (!empty($av->comentario)): ?>
                                                    <span style="font-style: italic; color: #4a5568;">"<?php echo htmlspecialchars($av->comentario); ?>"</span>
                                                <?php else: ?>
                                                    <span class="text-muted">Sem comentário adicional</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($av->created_at)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
