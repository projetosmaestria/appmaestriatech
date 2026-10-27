<?php
defined('BASEPATH') or exit('No direct script access allowed');
$waiting_tests = $aguardando_teste->result();
$pending_tasks = isset($tarefas_pendentes) ? $tarefas_pendentes : array();
$media = (float) $media_avaliacoes;
$media_color = $media <= 1 ? '#dd4b39' : ($media <= 3 ? '#f39c12' : '#00a65a');
$kpi = function ($url, $icon, $color, $label, $value, $hint = '') {
    return '<div class="col-lg-3 col-sm-6"><a href="' . $url . '" class="dash-kpi">' .
        '<span class="dash-kpi-icon" style="background:' . $color . ';"><i class="fa ' . $icon . '"></i></span>' .
        '<span class="dash-kpi-text"><span class="dash-kpi-value">' . (int) $value . '</span>' .
        '<span class="dash-kpi-label">' . $label . '</span>' .
        ($hint !== '' ? '<span class="dash-kpi-hint">' . $hint . '</span>' : '') .
        '</span></a></div>';
};
?>
<section class="col-lg-12">
    <div class="dash-header">
        <div>
            <h1>Olá, <?php echo html_escape($usuario_nome) ?></h1>
            <p class="text-muted">Veja o que precisa da sua atenção hoje, <?php echo date('d/m/Y') ?>.</p>
        </div>
        <div class="dash-header-actions">
            <a href="<?php echo base_url('chamados/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Novo chamado</a>
            <a href="<?php echo base_url('chamados/index') ?>" class="btn btn-default"><i class="fa fa-list"></i> Todos os chamados</a>
            <a href="<?php echo base_url('kanban') ?>" class="btn btn-default"><i class="fa fa-columns"></i> Kanban</a>
        </div>
    </div>

    <h4 class="dash-section-title">Para você</h4>
    <div class="row">
        <?php echo $kpi(base_url('chamados/index?mostrar_responsaveis=1'), 'fa-user', '#3c8dbc', 'Meus chamados', $meus_chamados, 'Atribuídos a você'); ?>
        <?php echo $kpi(base_url('kanban/planejamento'), 'fa-exclamation-triangle', $meus_atrasados > 0 ? '#dd4b39' : '#9aa5b1', 'Atrasados', $meus_atrasados, 'Previsão vencida'); ?>
        <?php echo $kpi('#dash-aguardando-teste', 'fa-hourglass-half', '#f39c12', 'Aguardando teste', count($waiting_tests), 'Com o cliente'); ?>
        <?php echo $kpi('#dash-tarefas', 'fa-check-square-o', '#605ca8', 'Tarefas de checklist', isset($total_tarefas_pendentes) ? $total_tarefas_pendentes : 0, 'Pendentes'); ?>
    </div>

    <h4 class="dash-section-title">Visão geral</h4>
    <div class="row">
        <?php echo $kpi(base_url('chamados/index?filtro_status=1'), 'fa-unlock', '#00a65a', 'Em aberto', $abertos); ?>
        <?php echo $kpi(base_url('chamados/index'), 'fa-user-times', $sem_responsavel > 0 ? '#807e00' : '#9aa5b1', 'Sem responsável', $sem_responsavel); ?>
        <?php echo $kpi(base_url('chamados/index?filtro_status=9'), 'fa-lightbulb-o', '#605ca8', 'Sugestão de desenvolvimento', $sugestao); ?>
        <?php echo $kpi(base_url('chamados/index?filtro_status=10'), 'fa-share-alt', '#00c0ef', 'Implantação', $implantacao); ?>
    </div>
</section>

<section class="col-lg-6">
    <div class="box" id="dash-aguardando-teste">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-hourglass-half text-yellow"></i> Aguardando teste do cliente <span class="badge"><?php echo count($waiting_tests) ?></span></h3>
        </div>
        <div class="box-body no-padding">
            <?php if (!$waiting_tests) { ?>
                <p class="dash-empty"><i class="fa fa-check-circle text-green"></i> Nenhum chamado seu aguardando teste.</p>
            <?php } else { ?>
                <ul class="dash-list">
                    <?php foreach ($waiting_tests as $values) {
                        $late = $values->data_prazo && $values->data_prazo < date('Y-m-d H:i:s');
                    ?>
                        <li class="<?php echo $late ? 'is-late' : '' ?>">
                            <a href="<?php echo base_url('chamados/details/' . $values->id) ?>">
                                <span class="dash-list-main">
                                    <strong>#<?php echo (int) $values->id ?></strong> <?php echo html_escape($values->titulo) ?>
                                    <span class="dash-list-sub"><?php echo html_escape($values->unidade) ?> · <?php echo html_escape($values->dono) ?></span>
                                </span>
                                <span class="dash-list-side <?php echo $late ? 'text-red' : 'text-muted' ?>">
                                    <?php echo $values->data_prazo ? date('d/m', strtotime($values->data_prazo)) : 'Sem previsão' ?>
                                    <?php if (mb_strtolower((string) $values->prioridade_desc, 'UTF-8') === 'alta') { ?><span class="label label-danger">Alta</span><?php } ?>
                                </span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </div>
</section>

<section class="col-lg-6">
    <div class="box" id="dash-tarefas">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-check-square-o text-purple"></i> Tarefas de checklist pendentes <span class="badge"><?php echo isset($total_tarefas_pendentes) ? (int) $total_tarefas_pendentes : 0 ?></span></h3>
        </div>
        <div class="box-body no-padding">
            <?php if (!$pending_tasks) { ?>
                <p class="dash-empty"><i class="fa fa-check-circle text-green"></i> Nenhuma tarefa pendente no momento.</p>
            <?php } else { ?>
                <ul class="dash-list">
                    <?php foreach ($pending_tasks as $tarefa) {
                        $has_deadline = $tarefa->data_prazo && $tarefa->data_prazo != '0000-00-00';
                        $late = $has_deadline && strtotime($tarefa->data_prazo) < strtotime(date('Y-m-d'));
                        $soon = $has_deadline && !$late && strtotime($tarefa->data_prazo) <= strtotime('+3 days');
                    ?>
                        <li class="<?php echo $late ? 'is-late' : '' ?>">
                            <a href="<?php echo base_url('chamados/details/' . $tarefa->chamado_id . '#checklist') ?>">
                                <span class="dash-list-main">
                                    <strong><?php echo html_escape($tarefa->nome) ?></strong>
                                    <span class="dash-list-sub">Chamado #<?php echo (int) $tarefa->chamado_id ?><?php echo $tarefa->descricao ? ' · ' . html_escape($tarefa->descricao) : '' ?></span>
                                </span>
                                <span class="dash-list-side">
                                    <?php if (!$has_deadline) { ?>
                                        <span class="text-muted">Sem prazo</span>
                                    <?php } else { ?>
                                        <span class="<?php echo $late ? 'text-red' : 'text-muted' ?>"><?php echo date('d/m', strtotime($tarefa->data_prazo)) ?></span>
                                        <?php if ($late) { ?><span class="label label-danger">Atrasada</span><?php } elseif ($soon) { ?><span class="label label-warning">Próxima</span><?php } ?>
                                    <?php } ?>
                                </span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </div>
</section>

<section class="col-lg-12">
    <div class="box box-solid dash-period">
        <div class="box-body">
            <?php echo form_open('', array('method' => 'get', 'id' => 'form-grafico-aval', 'class' => 'dash-period-form')); ?>
                <h4><i class="fa fa-star-half-o"></i> Avaliações dos clientes</h4>
                <div class="form-group">
                    <label for="filtro_range">Período</label>
                    <?php echo form_input($filtro_range); ?>
                </div>
                <div class="form-group dash-period-units">
                    <label for="filtro_unidades">Clientes</label>
                    <?php echo form_dropdown($filtro_unidades); ?>
                </div>
                <div class="form-group">
                    <button type="submit" name="btn_filtrar" class="btn btn-default"><i class="fa fa-search"></i> Atualizar</button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</section>

<section class="col-lg-4">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Média das avaliações</h3>
        </div>
        <div class="box-body text-center dash-rating">
            <div class="dash-rating-value" style="color: <?php echo $media_color ?>;"><?php echo number_format($media, 1, ',', '.') ?></div>
            <div class="dash-rating-scale">de 5 · <?php echo count($avaliacoes) ?> chamado<?php echo count($avaliacoes) === 1 ? '' : 's' ?> avaliado<?php echo count($avaliacoes) === 1 ? '' : 's' ?></div>
            <div class="progress progress-sm">
                <div class="progress-bar" style="width: <?php echo min(100, max(0, $media / 5 * 100)) ?>%; background-color: <?php echo $media_color ?>;"></div>
            </div>
            <p class="text-muted small">Média de atendimento, prazo e satisfação no período selecionado.</p>
        </div>
    </div>
</section>

<section class="col-lg-8">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Chamados abertos x finalizados <small>últimos 7 meses</small></h3>
        </div>
        <div class="box-body chart-responsive">
            <div class="chart" id="line-chart-chamados" style="height: 260px;"></div>
        </div>
    </div>
</section>

<section class="col-lg-12">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Chamados avaliados no período</h3>
        </div>
        <div class="box-body table-responsive">
            <table id="datatable_avaliacoes" class="table table-hover dash-ratings-table">
                <thead>
                    <tr>
                        <th><?php echo lang('id'); ?></th>
                        <th><?php echo lang('titulo'); ?></th>
                        <th><?php echo lang('filial'); ?></th>
                        <th><?php echo lang('dono'); ?></th>
                        <th><?php echo lang('data_finalizacao'); ?></th>
                        <th><?php echo lang('data_avaliacao'); ?></th>
                        <th title="Atendimento / Prazo / Satisfação">Notas (At. / Pr. / Sat.)</th>
                        <th><?php echo lang('media'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($avaliacoes as $values) {
                        $average = round(($values->atendimento + $values->prazo + $values->satisfacao) / 3, 2);
                        $average_class = $average <= 1 ? 'label-danger' : ($average <= 3 ? 'label-warning' : 'label-success');
                    ?>
                        <tr>
                            <td><?php echo (int) $values->id ?></td>
                            <td><?php echo html_escape($values->titulo); ?></td>
                            <td><?php echo html_escape($values->unidade); ?></td>
                            <td><?php echo html_escape($values->dono); ?></td>
                            <td data-order="<?php echo strtotime($values->data_finalizacao); ?>"><?php echo date("d/m/Y", strtotime($values->data_finalizacao)); ?></td>
                            <td data-order="<?php echo strtotime($values->data_avaliacao); ?>"><?php echo date("d/m/Y", strtotime($values->data_avaliacao)); ?></td>
                            <td><?php echo (int) $values->atendimento ?> / <?php echo (int) $values->prazo ?> / <?php echo (int) $values->satisfacao ?></td>
                            <td data-order="<?php echo $average ?>"><span class="label <?php echo $average_class ?>"><?php echo number_format($average, 1, ',', '.') ?></span></td>
                            <td><a href="<?php echo base_url('chamados/details/' . $values->id) ?>" class="btn btn-default btn-xs" title="<?php echo lang('actions_view') ?>"><i class="fa fa-eye"></i></a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
    .dash-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
    }
    .dash-header h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
    }
    .dash-header p {
        margin: 4px 0 0;
    }
    .dash-header-actions .btn {
        margin-left: 4px;
    }
    .dash-section-title {
        margin: 16px 0 10px;
        color: #777;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .dash-kpi {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 15px;
        padding: 14px;
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .1);
        color: #333;
        transition: box-shadow .15s;
    }
    .dash-kpi:hover {
        color: #333;
        box-shadow: 0 3px 10px rgba(0, 0, 0, .15);
    }
    .dash-kpi-icon {
        flex: 0 0 48px;
        height: 48px;
        border-radius: 50%;
        color: #fff;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .dash-kpi-text {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }
    .dash-kpi-value {
        font-size: 26px;
        font-weight: 700;
    }
    .dash-kpi-label {
        font-weight: 600;
    }
    .dash-kpi-hint {
        color: #999;
        font-size: 12px;
    }
    .dash-empty {
        margin: 0;
        padding: 18px;
        color: #777;
    }
    .dash-list {
        list-style: none;
        margin: 0;
        padding: 0;
        max-height: 330px;
        overflow-y: auto;
    }
    .dash-list li {
        border-bottom: 1px solid #d2d6de;
    }
    .dash-list li:last-child {
        border-bottom: 0;
    }
    .dash-list li:nth-child(even) a {
        background: #f7f9fb;
    }
    .dash-list li a {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border-left: 4px solid #d2d6de;
        color: #333;
    }
    .dash-list li.is-late a {
        border-left-color: #dd4b39;
    }
    .dash-list li a:hover {
        background: #eaf2f8;
    }
    .dash-list-main {
        min-width: 0;
    }
    .dash-list-sub {
        display: block;
        color: #888;
        font-size: 12px;
        margin-top: 2px;
    }
    .dash-list-side {
        flex: 0 0 auto;
        white-space: nowrap;
        font-size: 12px;
    }
    .dash-period-form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 12px;
    }
    .dash-period-form h4 {
        margin: 0 auto 8px 0;
        font-weight: 600;
    }
    .dash-period-form .form-group {
        margin-bottom: 0;
    }
    .dash-period-form #filtro_range {
        width: 210px;
    }
    .dash-period-units .fstElement {
        min-width: 280px;
    }
    .dash-rating-value {
        font-size: 56px;
        font-weight: 700;
        line-height: 1;
        margin-top: 10px;
    }
    .dash-rating-scale {
        color: #777;
        margin: 6px 0 14px;
    }
</style>
