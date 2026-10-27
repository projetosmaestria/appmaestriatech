<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$situacao_labels = array(
    'done' => array('label-success', 'Concluído'),
    'late' => array('label-danger', 'Atrasado'),
    'active' => array('label-primary', 'Em andamento'),
);
$percent = function ($part, $total) {
    return $total > 0 ? round($part / $total * 100) : 0;
};
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if (!empty($message)) { echo $message; } ?>
        <div class="gantt-toolbar">
            <div>
                <?php if ($project_id) { ?>
                    <a href="<?php echo base_url('plano_acao/gantt') ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Todos os projetos</a>
                    <a href="<?php echo base_url('chamados/index?filtro_plano_acao=' . $project_id) ?>" class="btn btn-default"><i class="fa fa-list"></i> Chamados do projeto</a>
                    <?php if ($can_edit) { ?>
                        <a href="<?php echo base_url('plano_acao/edit/' . $project_id) ?>" class="btn btn-primary"><i class="fa fa-edit"></i> Editar projeto</a>
                    <?php } else { ?>
                        <a href="<?php echo base_url('plano_acao/view/' . $project_id) ?>" class="btn btn-default"><i class="fa fa-eye"></i> Dados do projeto</a>
                    <?php } ?>
                    <?php if ($can_novo_chamado) { ?>
                        <a href="<?php echo base_url('chamados/create/planoacao/' . $project_id) ?>" class="btn btn-default"><i class="fa fa-plus"></i> Novo chamado</a>
                    <?php } ?>
                <?php } else { ?>
                    <a href="<?php echo base_url('plano_acao') ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Lista de projetos</a>
                    <a href="<?php echo base_url('plano_acao/gantt' . ($include_finished ? '' : '?finalizados=1')) ?>" class="btn btn-default">
                        <i class="fa <?php echo $include_finished ? 'fa-eye-slash' : 'fa-eye' ?>"></i> <?php echo $include_finished ? 'Ocultar finalizados' : 'Mostrar finalizados' ?>
                    </a>
                <?php } ?>
            </div>
            <div class="btn-group" role="group" aria-label="Escala do cronograma">
                <button type="button" class="btn btn-default js-gantt-view" data-mode="Day">Dia</button>
                <button type="button" class="btn btn-default js-gantt-view active" data-mode="Week">Semana</button>
                <button type="button" class="btn btn-default js-gantt-view" data-mode="Month">Mês</button>
            </div>
        </div>

        <?php if ($project_id) {
            $row = $project_rows[0];
        ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="box gantt-summary">
                        <div class="box-body">
                            <div class="gantt-summary-label">Progresso</div>
                            <div class="gantt-summary-value"><?php echo (int) $row['task']['progress'] ?>%</div>
                            <div class="progress progress-sm"><div class="progress-bar progress-bar-success" style="width: <?php echo (int) $row['task']['progress'] ?>%"></div></div>
                            <div class="text-muted small"><?php echo (int) $summary['done'] ?> de <?php echo (int) $summary['total'] ?> chamados concluídos</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="box gantt-summary">
                        <div class="box-body">
                            <div class="gantt-summary-label">Período</div>
                            <div class="gantt-summary-value gantt-summary-period"><?php echo $row['task']['periodo'] ?></div>
                            <span class="label <?php echo $situacao_labels[$row['task']['situacao']][0] ?>"><?php echo html_escape($row['task']['situacao_label']) ?></span>
                            <span class="text-muted small"><?php echo html_escape($row['project']->nome_resp) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="box gantt-summary">
                        <div class="box-body gantt-summary-counts">
                            <div><strong class="text-blue"><?php echo (int) $summary['active'] ?></strong><span>Em andamento</span></div>
                            <div><strong class="text-red"><?php echo (int) $summary['late'] ?></strong><span>Atrasados</span></div>
                            <div><strong class="text-green"><?php echo (int) $summary['done'] ?></strong><span>Concluídos</span></div>
                            <div><strong class="text-muted"><?php echo (int) $summary['estimated'] ?></strong><span>Sem previsão</span></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-tasks"></i> <?php echo $project_id ? 'Cronograma do projeto' : 'Projetos no tempo' ?></h3>
                <div class="gantt-legend pull-right">
                    <span><i class="gantt-legend-swatch gantt-legend-active"></i> Em andamento</span>
                    <span><i class="gantt-legend-swatch gantt-legend-late"></i> Atrasado</span>
                    <span><i class="gantt-legend-swatch gantt-legend-done"></i> Concluído</span>
                    <?php if ($project_id) { ?><span><i class="gantt-legend-swatch gantt-legend-estimated"></i> Prazo estimado</span><?php } ?>
                </div>
            </div>
            <div class="box-body">
                <?php if (!$gantt_tasks) { ?>
                    <p class="text-muted text-center gantt-empty">Nenhum projeto para exibir.</p>
                <?php } else { ?>
                    <p class="text-muted small">Clique em uma barra para ver os detalhes. O cronograma é somente para visualização: as datas vêm do projeto e dos chamados.</p>
                    <div id="gantt-chart"></div>
                <?php } ?>
            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?php echo $project_id ? 'Chamados do projeto' : 'Resumo dos projetos' ?></h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover gantt-table">
                    <?php if ($project_id) { ?>
                        <thead><tr><th>Chamado</th><th>Status</th><th>Responsável</th><th>Período</th><th>Situação</th></tr></thead>
                        <tbody>
                            <?php if (!$tickets) { ?>
                                <tr><td colspan="5" class="text-muted text-center">Nenhum chamado vinculado a este projeto.</td></tr>
                            <?php } ?>
                            <?php foreach ($tickets as $ticket) { ?>
                                <tr>
                                    <td><a href="<?php echo $ticket['url'] ?>">#<?php echo (int) $ticket['chamado_id'] ?> — <?php echo html_escape($ticket['titulo']) ?></a><br><small class="text-muted"><?php echo html_escape($ticket['unidade']) ?></small></td>
                                    <td><?php echo html_escape($ticket['status']) ?></td>
                                    <td><?php echo html_escape($ticket['responsavel'] ?: '—') ?></td>
                                    <td class="gantt-nowrap"><?php echo $ticket['periodo'] ?></td>
                                    <td><span class="label <?php echo $situacao_labels[$ticket['situacao']][0] ?>"><?php echo $situacao_labels[$ticket['situacao']][1] ?></span><?php if ($ticket['estimado']) { ?> <small class="text-muted">prazo estimado</small><?php } ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    <?php } else { ?>
                        <thead><tr><th>Projeto</th><th>Responsável</th><th>Período</th><th>Progresso</th><th>Chamados</th><th>Situação</th></tr></thead>
                        <tbody>
                            <?php if (!$project_rows) { ?>
                                <tr><td colspan="6" class="text-muted text-center">Nenhum projeto em aberto.</td></tr>
                            <?php } ?>
                            <?php foreach ($project_rows as $row) { ?>
                                <tr>
                                    <td><a href="<?php echo $row['task']['url'] ?>"><?php echo html_escape($row['project']->desc) ?></a></td>
                                    <td><?php echo html_escape($row['project']->nome_resp) ?></td>
                                    <td class="gantt-nowrap"><?php echo $row['task']['periodo'] ?></td>
                                    <td class="gantt-progress-cell">
                                        <div class="progress progress-xs"><div class="progress-bar progress-bar-success" style="width: <?php echo (int) $row['task']['progress'] ?>%"></div></div>
                                        <small><?php echo (int) $row['task']['progress'] ?>%</small>
                                    </td>
                                    <td><?php echo (int) $row['done'] ?>/<?php echo (int) $row['tickets'] ?><?php if ($row['late']) { ?> <span class="label label-danger"><?php echo (int) $row['late'] ?> atrasado<?php echo $row['late'] > 1 ? 's' : '' ?></span><?php } ?></td>
                                    <td><span class="label <?php echo $situacao_labels[$row['task']['situacao']][0] ?>"><?php echo html_escape($row['task']['situacao_label']) ?></span></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    <?php } ?>
                </table>
            </div>
        </div>
    </section>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@1.2.2/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@1.2.2/dist/frappe-gantt.umd.js"></script>
<script type="text/javascript">
    window.addEventListener('load', function() {
        var tasks = <?php echo json_encode($gantt_tasks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        if (!tasks.length || typeof Gantt === 'undefined') {
            return;
        }
        function escapeHtml(text) {
            return String(text || '').replace(/[&<>"']/g, function(c) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
            });
        }
        var gantt = new Gantt('#gantt-chart', tasks, {
            readonly: true,
            language: 'pt-BR',
            view_mode: 'Week',
            bar_height: 24,
            padding: 14,
            today_button: false,
            scroll_to: 'today',
            popup: function(ctx) {
                ctx.set_title(escapeHtml(ctx.task.name));
                ctx.set_subtitle(escapeHtml(ctx.task.description));
                ctx.set_details(escapeHtml(ctx.task.periodo) + '<br>' + escapeHtml(ctx.task.situacao_label));
                ctx.add_action('Abrir', function(task) {
                    window.location = task.url;
                });
            }
        });
        document.querySelectorAll('.js-gantt-view').forEach(function(button) {
            button.addEventListener('click', function() {
                document.querySelectorAll('.js-gantt-view').forEach(function(other) {
                    other.classList.remove('active');
                });
                button.classList.add('active');
                gantt.change_view_mode(button.getAttribute('data-mode'));
            });
        });
    });
</script>
<style type="text/css">
    .gantt-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
    }
    .gantt-toolbar .btn {
        margin: 0 4px 4px 0;
    }
    .gantt-summary .box-body {
        min-height: 110px;
    }
    .gantt-summary-label {
        color: #777;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .gantt-summary-value {
        font-size: 30px;
        font-weight: 700;
        line-height: 1.2;
    }
    .gantt-summary-period {
        font-size: 18px;
        margin: 6px 0;
    }
    .gantt-summary-counts {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .gantt-summary-counts strong {
        display: block;
        font-size: 24px;
        line-height: 1.1;
    }
    .gantt-summary-counts span {
        color: #777;
        font-size: 12px;
    }
    .gantt-legend span {
        margin-left: 12px;
        font-size: 12px;
        color: #555;
    }
    .gantt-legend-swatch {
        display: inline-block;
        width: 14px;
        height: 10px;
        border-radius: 2px;
        vertical-align: middle;
        margin-right: 3px;
    }
    .gantt-legend-active { background: #3c8dbc; }
    .gantt-legend-late { background: #dd4b39; }
    .gantt-legend-done { background: #00a65a; }
    .gantt-legend-estimated { background: #fff; border: 1px dashed #3c8dbc; }
    .gantt-empty {
        padding: 30px;
    }
    #gantt-chart {
        overflow: auto;
    }
    #gantt-chart .bar-wrapper .bar,
    #gantt-chart .bar-wrapper .bar-progress {
        cursor: pointer;
    }
    #gantt-chart .gantt-active .bar,
    #gantt-chart .gantt-projeto-active .bar { fill: #3c8dbc; }
    #gantt-chart .gantt-late .bar,
    #gantt-chart .gantt-projeto-late .bar { fill: #dd4b39; }
    #gantt-chart .gantt-done .bar,
    #gantt-chart .gantt-done .bar-progress,
    #gantt-chart .gantt-projeto-done .bar,
    #gantt-chart .gantt-projeto-done .bar-progress { fill: #00a65a; }
    #gantt-chart .gantt-active-estimado .bar,
    #gantt-chart .gantt-late-estimado .bar {
        fill: #ffffff;
        stroke: #3c8dbc;
        stroke-dasharray: 4 3;
        stroke-width: 1.5;
    }
    #gantt-chart .gantt-late-estimado .bar { stroke: #dd4b39; }
    #gantt-chart .gantt-done-estimado .bar,
    #gantt-chart .gantt-done-estimado .bar-progress { fill: #00a65a; }
    #gantt-chart .gantt-active-estimado .bar-label,
    #gantt-chart .gantt-late-estimado .bar-label { fill: #333; }
    #gantt-chart [class*="gantt-projeto-"] .bar {
        fill-opacity: .85;
        stroke: #1f2d3d;
        stroke-width: 1;
    }
    #gantt-chart .bar-label {
        font-size: 12px;
    }
    .gantt-table .gantt-nowrap {
        white-space: nowrap;
    }
    .gantt-progress-cell {
        min-width: 120px;
    }
    .gantt-progress-cell .progress {
        margin-bottom: 2px;
    }
</style>
