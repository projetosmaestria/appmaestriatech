<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$selected_collaborator = null;
$config_tab_active = $is_admin && $this->input->get('config', true) === '1';
foreach ($collaborators as $collaborator) {
    if ((int) $collaborator->id === (int) $selected_id) {
        $selected_collaborator = $collaborator;
        break;
    }
}
$today = date('Y-m-d');
$current_filter_query = 'colaborador=' . (int) $selected_id .
    '&inicio=' . rawurlencode($start_date) .
    '&fim=' . rawurlencode($end_date) .
    '&' . $status_filter_query;
$selected_status_names = array();
foreach ($statuses as $status) {
    if (in_array((int) $status->id, $selected_status_ids, true)) {
        $selected_status_names[] = $status->descricao;
    }
}
$format_hours = function ($hours) {
    return number_format($hours, 1, ',', '.') . ' h';
};
$priority_label_class = function ($priority) {
    $priority = mb_strtolower(trim((string) $priority), 'UTF-8');
    if ($priority === 'alta') {
        return 'label-danger';
    }
    if (in_array($priority, array('média', 'media', 'normal'), true)) {
        return 'label-warning';
    }
    return 'label-default';
};
$goal_status_labels = array(
    'met' => array('label-success', 'Meta atingida'),
    'below' => array('label-danger', 'Abaixo da meta'),
    'pending' => array('label-default', 'A apurar'),
    'unavailable' => array('label-default', 'Não avaliado'),
);
$weekdays = array(1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado', 7 => 'Domingo');
?>
<div class="content-wrapper">
  <section class="content-header">
    <?php echo $pagetitle; ?>
    <?php echo $breadcrumb; ?>
  </section>
  <section class="content">
    <?php if (!empty($message)) { echo $message; } ?>

    <div class="box box-solid planning-filter">
      <div class="box-body">
        <?php echo form_open('kanban/planejamento', array('method' => 'get', 'id' => 'planningFilterForm')); ?>
          <input type="hidden" name="filtrar_status" value="1">
          <div class="planning-filter-row">
            <div class="form-group collaborator-filter">
              <label for="colaborador">Colaborador</label>
              <select name="colaborador" id="colaborador" class="form-control multipleSelect">
                <?php if (!$collaborators) { ?>
                  <option value="">Nenhum colaborador dos grupos suporte, desenvolvedor ou supervisor foi encontrado.</option>
                <?php } ?>
                <?php foreach ($collaborators as $collaborator) { ?>
                  <option value="<?php echo (int) $collaborator->id; ?>" <?php echo ((int) $collaborator->id === (int) $selected_id) ? 'selected' : ''; ?>>
                    <?php echo html_escape($collaborator->nome); ?>
                  </option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <label for="inicio">Período</label>
              <div class="input-group planning-period">
                <input type="date" name="inicio" id="inicio" class="form-control" value="<?php echo html_escape($start_date); ?>" required>
                <span class="input-group-addon">até</span>
                <input type="date" name="fim" id="fim" class="form-control" value="<?php echo html_escape($end_date); ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="hidden-xs">&nbsp;</label>
              <div class="btn-group" role="group" aria-label="Atalhos de período">
                <button type="button" class="btn btn-default js-period-shortcut" data-weeks="-1" title="Semana anterior"><i class="fa fa-chevron-left"></i></button>
                <button type="button" class="btn btn-default js-period-shortcut" data-weeks="0">Esta semana</button>
                <button type="button" class="btn btn-default js-period-shortcut" data-weeks="1" title="Próxima semana"><i class="fa fa-chevron-right"></i></button>
              </div>
            </div>
            <div class="form-group">
              <label class="hidden-xs">&nbsp;</label>
              <div>
                <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#statusFilterOptions" aria-expanded="false">
                  <i class="fa fa-filter"></i> Status
                  <span class="badge"><?php echo count($selected_status_ids); ?></span>
                </button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filtrar</button>
              </div>
            </div>
          </div>
          <p class="help-block planning-status-summary">
            Status: <?php echo $selected_status_names ? html_escape(implode(', ', $selected_status_names)) : 'nenhum selecionado'; ?>
          </p>
          <div class="collapse" id="statusFilterOptions">
            <div class="status-filter-options">
              <?php foreach ($statuses as $status) { ?>
                <label class="checkbox-inline">
                  <input type="checkbox" name="status_ids[]" value="<?php echo (int) $status->id; ?>"
                    <?php echo in_array((int) $status->id, $selected_status_ids, true) ? 'checked' : ''; ?>>
                  <?php echo html_escape($status->descricao); ?>
                </label>
              <?php } ?>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <div class="nav-tabs-custom">
      <ul class="nav nav-tabs">
        <li class="<?php echo $config_tab_active ? '' : 'active'; ?>">
          <a href="#tab_planejamento_chamados" data-toggle="tab"><i class="fa fa-calendar"></i> Agenda</a>
        </li>
        <li>
          <a href="#tab_resumo" data-toggle="tab"><i class="fa fa-bar-chart"></i> Resumo</a>
        </li>
        <?php if ($is_admin && isset($users)) { ?>
          <li class="<?php echo $config_tab_active ? 'active' : ''; ?>">
            <a href="#tab_configuracoes" data-toggle="tab"><i class="fa fa-cog"></i> Configurações</a>
          </li>
        <?php } ?>
      </ul>
      <div class="tab-content">
        <div class="tab-pane <?php echo $config_tab_active ? '' : 'active'; ?>" id="tab_planejamento_chamados">
          <?php if (!$selected_collaborator) { ?>
            <p class="text-muted">Selecione um colaborador para visualizar o planejamento.</p>
          <?php } else { ?>
            <h4 class="planning-section-title">Pendências de <?php echo html_escape($selected_collaborator->nome); ?></h4>
            <div class="row">
              <div class="col-lg-6">
                <?php $this->load->view('admin/kanban/_chamados_atrasados', array(
                    'chamados_atrasados' => $chamados_atrasados,
                    'titulo' => 'Chamados atrasados',
                    'can_edit_schedule' => $can_edit_overdue_schedule,
                    'collaborator_id' => $overdue_collaborator_id,
                    'priorities' => $overdue_priorities,
                    'levels' => $overdue_levels,
                    'render_modal' => false,
                )); ?>
              </div>
              <div class="col-lg-6">
                <?php $this->load->view('admin/kanban/_chamados_sem_planejamento', array(
                    'chamados_sem_planejamento' => $chamados_sem_planejamento,
                    'titulo' => 'Sem data de planejamento',
                    'can_edit_schedule' => $can_edit_overdue_schedule,
                    'collaborator_id' => $overdue_collaborator_id,
                    'priorities' => $overdue_priorities,
                    'levels' => $overdue_levels,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'status_filter_query' => $status_filter_query,
                    'render_modal' => false,
                )); ?>
              </div>
            </div>

            <h4 class="planning-section-title">
              Agenda do período
              <small><?php echo $format_hours($dashboard['summary']['planned_hours']); ?> previstas · <?php echo $format_hours($dashboard['summary']['logged_hours']); ?> apontadas</small>
            </h4>
            <?php if (!$dashboard['days']) { ?>
              <p class="text-muted">O período selecionado não possui dias úteis.</p>
            <?php } ?>
            <?php foreach ($dashboard['days'] as $date => $day) {
                $timestamp = strtotime($date);
                $is_unavailable = !empty($day['unavailability']);
                $is_empty = !$day['tasks'] && !$is_unavailable;
                $overloaded = $day['planned_hours'] > $day['goal_hours'];
                $load_percent = $day['goal_hours'] > 0 ? min(100, round($day['planned_hours'] / $day['goal_hours'] * 100)) : ($day['planned_hours'] > 0 ? 100 : 0);
                $box_class = $is_unavailable ? 'box-danger' : ($date === $today ? 'box-primary' : 'box-default');
            ?>
              <div class="box planning-day <?php echo $box_class; ?> <?php echo $is_empty ? 'collapsed-box' : ''; ?>">
                <div class="box-header with-border">
                  <h3 class="box-title">
                    <?php echo $weekdays[(int) date('N', $timestamp)]; ?>, <?php echo date('d/m', $timestamp); ?>
                    <?php if ($date === $today) { ?><span class="label label-primary">Hoje</span><?php } ?>
                  </h3>
                  <span class="planning-day-meta">
                    <?php echo count($day['tasks']); ?> atividade<?php echo count($day['tasks']) === 1 ? '' : 's'; ?>
                  </span>
                  <div class="box-tools pull-right">
                    <?php if ($is_unavailable) { ?>
                      <span class="label label-danger">Indisponível</span>
                    <?php } elseif (in_array($day['goal_status'], array('met', 'below'), true)) { ?>
                      <span class="label <?php echo $goal_status_labels[$day['goal_status']][0]; ?>" title="Horas apontadas no dia / meta mínima">
                        <?php echo $goal_status_labels[$day['goal_status']][1]; ?>: <?php echo number_format($day['goal_logged_hours'], 1, ',', '.'); ?> / <?php echo $format_hours($day['goal_hours']); ?>
                      </span>
                    <?php } ?>
                    <span class="planning-load" title="Estimativa planejada / capacidade diária">
                      <span class="progress progress-xs">
                        <span class="progress-bar <?php echo $overloaded ? 'progress-bar-danger' : 'progress-bar-success'; ?>" style="width: <?php echo (int) $load_percent; ?>%"></span>
                      </span>
                      <span class="<?php echo $overloaded ? 'text-red' : 'text-muted'; ?>">
                        <?php echo number_format($day['planned_hours'], 1, ',', '.'); ?> / <?php echo $format_hours($day['goal_hours']); ?>
                      </span>
                    </span>
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa <?php echo $is_empty ? 'fa-plus' : 'fa-minus'; ?>"></i></button>
                  </div>
                </div>
                <div class="box-body table-responsive no-padding">
                  <?php if ($is_unavailable) { ?>
                    <div class="planning-unavailable text-red">
                      <i class="fa fa-ban"></i> Dia indisponível para novos agendamentos:
                      <?php
                        $reasons = array();
                        foreach ($day['unavailability'] as $availability) {
                            $reasons[] = html_escape($availability->titulo) .
                                ($availability->tipo !== 'feriado' ? ' (' . html_escape($availability->colaborador) . ')' : '');
                        }
                        echo implode('; ', $reasons);
                      ?>
                    </div>
                  <?php } ?>
                  <?php if (!$day['tasks']) { ?>
                    <p class="text-muted planning-empty">Nenhuma atividade programada para este dia.</p>
                  <?php } else { ?>
                    <table class="table table-hover planning-tasks">
                      <thead>
                        <tr>
                          <th>Chamado</th><th>Prioridade / Nível</th><th>Responsável</th><th>Prazo</th>
                          <th class="text-right">Estimado</th><th class="text-right">Apontado</th>
                          <?php if ($is_admin) { ?><th></th><?php } ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($day['tasks'] as $task) {
                            $deadline_overdue = $task->data_prazo && strtotime($task->data_prazo) < time();
                        ?>
                          <tr>
                            <td>
                              <a href="<?php echo base_url('chamados/details/' . (int) $task->chamado_id); ?>" target="_blank">#<?php echo (int) $task->chamado_id; ?> — <?php echo html_escape($task->titulo); ?></a>
                              <br><small class="text-muted"><?php echo html_escape($task->unidade); ?><?php echo $task->status ? ' · ' . html_escape($task->status) : ''; ?></small>
                            </td>
                            <td>
                              <?php if ($task->prioridade) { ?>
                                <span class="label <?php echo $priority_label_class($task->prioridade); ?>"><?php echo html_escape($task->prioridade); ?></span>
                              <?php } ?>
                              <br><small class="text-muted"><?php echo html_escape($task->nivel ?: 'Sem nível'); ?></small>
                            </td>
                            <td>
                              <?php echo html_escape($task->responsavel ?: '—'); ?>
                              <?php if ($task->desenvolvedor) { ?><br><small class="text-muted">Dev: <?php echo html_escape($task->desenvolvedor); ?></small><?php } ?>
                            </td>
                            <td class="<?php echo $deadline_overdue ? 'text-red' : ''; ?>">
                              <?php echo $task->data_prazo ? date('d/m/Y H:i', strtotime($task->data_prazo)) : '—'; ?>
                            </td>
                            <td class="text-right"><?php echo $format_hours($task->estimated_hours); ?></td>
                            <td class="text-right"><?php echo $format_hours($task->hours_logged); ?></td>
                            <?php if ($is_admin) { ?>
                              <td class="text-right">
                                <button type="button" class="btn btn-default btn-sm js-programar-chamado" title="Reprogramar"
                                  data-toggle="modal" data-target="#programarChamadoModal"
                                  data-ticket-id="<?php echo (int) $task->chamado_id; ?>"
                                  data-collaborator-id="<?php echo (int) $selected_id; ?>"
                                  data-scheduled-date="<?php echo html_escape($date); ?>"
                                  data-deadline="<?php echo $task->data_prazo ? date('Y-m-d\TH:i', strtotime($task->data_prazo)) : ''; ?>"
                                  data-priority-id="<?php echo (int) $task->prioridade_id; ?>"
                                  data-level-id="<?php echo (int) $task->nivel_id; ?>">
                                  <i class="fa fa-calendar"></i> Reprogramar
                                </button>
                              </td>
                            <?php } ?>
                          </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  <?php } ?>
                </div>
              </div>
            <?php } ?>

            <?php if ($is_admin) { ?>
              <?php $this->load->view('admin/kanban/_modal_programar_chamado', array(
                  'collaborator_id' => $selected_id,
                  'priorities' => $priorities,
                  'levels' => $overdue_levels,
                  'start_date' => $start_date,
                  'end_date' => $end_date,
                  'status_filter_query' => $status_filter_query,
              )); ?>
            <?php } ?>
          <?php } ?>
        </div>

        <div class="tab-pane" id="tab_resumo">
          <?php if (!$selected_collaborator) { ?>
            <p class="text-muted">Selecione um colaborador para visualizar o resumo.</p>
          <?php } else { ?>
            <div class="row">
              <div class="col-md-3 col-sm-6">
                <div class="small-box bg-aqua"><div class="inner"><h3><?php echo $format_hours($dashboard['summary']['planned_hours']); ?></h3><p>Horas previstas (estimativa por nível)</p></div><div class="icon"><i class="fa fa-calendar-check-o"></i></div></div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="small-box bg-green"><div class="inner"><h3><?php echo $format_hours($dashboard['summary']['logged_hours']); ?></h3><p>Horas apontadas no período</p></div><div class="icon"><i class="fa fa-clock-o"></i></div></div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="small-box bg-yellow"><div class="inner"><h3><?php echo $format_hours($dashboard['summary']['unplanned_hours']); ?></h3><p>Fora do planejamento (<?php echo (int) $dashboard['summary']['unplanned_count']; ?> chamados)</p></div><div class="icon"><i class="fa fa-exclamation-triangle"></i></div></div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="small-box <?php echo $dashboard['summary']['below_goal_days'] > 0 ? 'bg-red' : 'bg-blue'; ?>"><div class="inner"><h3><?php echo (int) $dashboard['summary']['below_goal_days']; ?> dia<?php echo (int) $dashboard['summary']['below_goal_days'] === 1 ? '' : 's'; ?></h3><p>Abaixo da meta mínima de <?php echo $format_hours($dashboard['goal_hours']); ?>/dia</p></div><div class="icon"><i class="fa fa-bullseye"></i></div></div>
              </div>
            </div>

            <div class="box box-default">
              <div class="box-header with-border"><h3 class="box-title">Resumo diário — <?php echo html_escape($selected_collaborator->nome); ?></h3></div>
              <div class="box-body table-responsive no-padding">
                <table class="table table-striped">
                  <thead><tr><th>Dia</th><th class="text-right">Atividades</th><th class="text-right">Estimativa</th><th>Carga planejada</th><th class="text-right">Meta mínima</th><th class="text-right" title="Todos os chamados, independente do filtro de status">Apontado</th><th>Meta de apontamento</th><th class="text-right">Fora do plano</th></tr></thead>
                  <tbody>
                    <?php foreach ($dashboard['days'] as $date => $day) {
                        $overloaded = $day['planned_hours'] > $day['goal_hours'];
                        $goal_deviation = $day['goal_logged_hours'] - $day['goal_hours'];
                        $evaluated = in_array($day['goal_status'], array('met', 'below'), true);
                    ?>
                      <tr class="<?php echo $day['goal_status'] === 'below' ? 'danger' : ''; ?>">
                        <td><?php echo $weekdays[(int) date('N', strtotime($date))]; ?>, <?php echo date('d/m/Y', strtotime($date)); ?></td>
                        <td class="text-right"><?php echo count($day['tasks']); ?></td>
                        <td class="text-right"><?php echo $format_hours($day['planned_hours']); ?></td>
                        <td><?php echo $overloaded ? '<span class="label label-warning">Acima da capacidade</span>' : '<span class="text-muted">Dentro da capacidade</span>'; ?></td>
                        <td class="text-right"><?php echo $format_hours($day['goal_hours']); ?></td>
                        <td class="text-right">
                          <?php if ($day['goal_logged_hours'] > 0) { ?>
                            <a href="#" class="js-ver-apontamentos" title="Ver chamados apontados"
                              data-colaborador="<?php echo (int) $selected_id; ?>"
                              data-inicio="<?php echo html_escape($date); ?>"
                              data-fim="<?php echo html_escape($date); ?>"
                              data-titulo="<?php echo html_escape($selected_collaborator->nome . ' — ' . date('d/m/Y', strtotime($date))); ?>"><?php echo $format_hours($day['goal_logged_hours']); ?></a>
                          <?php } else { ?>
                            <?php echo $format_hours($day['goal_logged_hours']); ?>
                          <?php } ?>
                          <?php if ($evaluated) { ?>
                            <small class="<?php echo $goal_deviation < -0.01 ? 'text-red' : 'text-green'; ?>">(<?php echo ($goal_deviation > 0 ? '+' : '') . number_format($goal_deviation, 1, ',', '.'); ?>)</small>
                          <?php } ?>
                        </td>
                        <td><span class="label <?php echo $goal_status_labels[$day['goal_status']][0]; ?>"><?php echo $goal_status_labels[$day['goal_status']][1]; ?></span></td>
                        <td class="text-right"><?php echo (int) $day['unplanned_count']; ?> (<?php echo $format_hours($day['unplanned_hours']); ?>)</td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
              <div class="box-footer text-muted small">
                A meta é a quantidade mínima de horas que o colaborador deve apontar por dia útil, considerando todos os chamados.
                Dias futuros ficam "A apurar" e dias indisponíveis (feriado, férias) não são avaliados.
              </div>
            </div>

            <div class="box box-default">
              <div class="box-header with-border"><h3 class="box-title">Chamados apontados fora do planejamento diário</h3></div>
              <div class="box-body table-responsive no-padding">
                <table class="table table-striped">
                  <thead><tr><th>Dia</th><th>Chamado</th><th>Nível</th><th class="text-right">Impacto apontado</th><th>Data planejada</th></tr></thead>
                  <tbody>
                    <?php if (!$dashboard['unplanned']) { ?><tr><td colspan="5" class="text-muted">Nenhum chamado fora do planejamento no período.</td></tr><?php } ?>
                    <?php foreach ($dashboard['unplanned'] as $item) { ?>
                      <tr>
                        <td><?php echo date('d/m/Y', strtotime($item->data)); ?></td>
                        <td><a href="<?php echo base_url('chamados/details/' . (int) $item->chamado_id); ?>" target="_blank">#<?php echo (int) $item->chamado_id; ?> — <?php echo html_escape($item->titulo); ?></a></td>
                        <td><?php echo html_escape($item->nivel ?: 'Sem nível'); ?></td>
                        <td class="text-right"><?php echo $format_hours($item->hours); ?></td>
                        <td><?php echo $item->data_prevista ? date('d/m/Y', strtotime($item->data_prevista)) : 'Não programado'; ?></td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php } ?>

          <div class="box box-default">
            <div class="box-header with-border"><h3 class="box-title">Comparativo de colaboradores no período</h3></div>
            <div class="box-body table-responsive no-padding">
              <table class="table table-striped">
                <thead><tr><th>Colaborador</th><th class="text-right">Previstas</th><th class="text-right" title="Meta mínima × dias úteis decorridos e disponíveis">Meta mínima</th><th class="text-right">Apontadas</th><th class="text-right">Saldo da meta</th><th class="text-right">Dias abaixo da meta</th><th class="text-right">Fora do plano</th><th class="text-right">Dias acima da capacidade</th></tr></thead>
                <tbody>
                  <?php if (!$comparison) { ?><tr><td colspan="8" class="text-muted">Nenhum colaborador para comparar.</td></tr><?php } ?>
                  <?php foreach ($comparison as $entry) { ?>
                    <tr class="<?php echo $entry['below_goal_days'] > 0 ? 'danger' : ''; ?>">
                      <td><?php echo html_escape($entry['nome']); ?></td>
                      <td class="text-right"><?php echo $format_hours($entry['planned_hours']); ?></td>
                      <td class="text-right"><?php echo $format_hours($entry['goal_hours']); ?> <small class="text-muted">(<?php echo (int) $entry['evaluated_days']; ?> d)</small></td>
                      <td class="text-right">
                        <?php if ($entry['logged_hours'] > 0) { ?>
                          <a href="#" class="js-ver-apontamentos" title="Ver chamados apontados"
                            data-colaborador="<?php echo (int) $entry['id']; ?>"
                            data-inicio="<?php echo html_escape($start_date); ?>"
                            data-fim="<?php echo html_escape($end_date); ?>"
                            data-titulo="<?php echo html_escape($entry['nome'] . ' — ' . date('d/m/Y', strtotime($start_date)) . ' a ' . date('d/m/Y', strtotime($end_date))); ?>"><?php echo $format_hours($entry['logged_hours']); ?></a>
                        <?php } else { ?>
                          <?php echo $format_hours($entry['logged_hours']); ?>
                        <?php } ?>
                      </td>
                      <td class="text-right <?php echo $entry['pointing_deviation'] < -0.01 ? 'text-red' : 'text-green'; ?>"><?php echo ($entry['pointing_deviation'] > 0 ? '+' : '') . $format_hours($entry['pointing_deviation']); ?></td>
                      <td class="text-right">
                        <?php if ($entry['below_goal_days'] > 0) { ?>
                          <span class="label label-danger"><?php echo (int) $entry['below_goal_days']; ?></span>
                        <?php } else { ?>
                          <span class="text-green">0</span>
                        <?php } ?>
                      </td>
                      <td class="text-right"><?php echo $format_hours($entry['unplanned_hours']); ?></td>
                      <td class="text-right"><?php echo (int) $entry['overload_days']; ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
            <div class="box-footer text-muted small">
              Meta mínima e saldo consideram apenas dias úteis já decorridos no período, sem feriados e indisponibilidades do colaborador.
            </div>
          </div>
        </div>

        <?php if ($is_admin && isset($users)) { ?>
          <div class="tab-pane <?php echo $config_tab_active ? 'active' : ''; ?>" id="tab_configuracoes">
            <div class="row">
              <div class="col-md-6">
                <div class="box box-default">
                  <div class="box-header with-border"><h3 class="box-title">Acesso a outros colaboradores</h3></div>
                  <?php echo form_open('kanban/planejamento', array('method' => 'post')); ?>
                    <div class="box-body">
                      <input type="hidden" name="action" value="save_access">
                      <div class="form-group">
                        <label for="viewer_id">Usuário que receberá acesso</label>
                        <select id="viewer_id" name="viewer_id" class="form-control js-config-reload" data-param="viewer_id" required>
                          <?php foreach ($users as $user) { ?><option value="<?php echo (int) $user->id; ?>" <?php echo ((int) $user->id === (int) $configured_viewer_id) ? 'selected' : ''; ?>><?php echo html_escape($user->nome); ?></option><?php } ?>
                        </select>
                      </div>
                      <label>Colaboradores que poderá consultar (além de si próprio)</label>
                      <div class="access-list">
                        <?php foreach ($users as $user) { if ((int) $user->id !== (int) $configured_viewer_id) { ?>
                          <div class="checkbox"><label><input type="checkbox" name="collaborators[]" value="<?php echo (int) $user->id; ?>" <?php echo in_array((int) $user->id, $configured_access, true) ? 'checked' : ''; ?>> <?php echo html_escape($user->nome); ?></label></div>
                        <?php }} ?>
                      </div>
                    </div>
                    <div class="box-footer">
                      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Salvar acessos</button>
                    </div>
                  <?php echo form_close(); ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="box box-default">
                  <div class="box-header with-border"><h3 class="box-title">Meta mínima diária de apontamento</h3></div>
                  <?php echo form_open('kanban/planejamento', array('method' => 'post')); ?>
                    <div class="box-body">
                      <input type="hidden" name="action" value="save_goal">
                      <div class="form-group">
                        <label for="goal_user_id">Colaborador</label>
                        <select id="goal_user_id" name="collaborator_id" class="form-control js-config-reload" data-param="goal_user_id" required>
                          <?php foreach ($users as $user) { ?><option value="<?php echo (int) $user->id; ?>" <?php echo ((int) $user->id === (int) $configured_goal_id) ? 'selected' : ''; ?>><?php echo html_escape($user->nome); ?></option><?php } ?>
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="goal_hours">Horas por dia</label>
                        <input type="number" min="0" max="24" step="0.25" name="goal_hours" id="goal_hours" class="form-control" value="<?php echo html_escape(number_format($configured_goal, 2, '.', '')); ?>" required>
                        <p class="help-block">Quantidade mínima de horas que o colaborador deve apontar por dia útil. Dias com menos horas apontadas são sinalizados como abaixo da meta. O padrão é 8 horas para quem ainda não possui configuração.</p>
                      </div>
                    </div>
                    <div class="box-footer">
                      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Salvar meta</button>
                    </div>
                  <?php echo form_close(); ?>
                </div>
              </div>
            </div>

            <div class="box box-default">
              <div class="box-header with-border">
                <h3 class="box-title">Calendário de indisponibilidades</h3>
              </div>
              <div class="box-body">
                <p class="text-muted">Feriados valem para todos os colaboradores. Férias e outras indisponibilidades bloqueiam somente o colaborador escolhido e aparecem também na <a href="<?php echo base_url('agenda'); ?>">Agenda da plataforma</a>.</p>
                <?php echo form_open('kanban/planejamento', array('method' => 'post', 'class' => 'availability-form')); ?>
                  <input type="hidden" name="action" value="save_unavailability">
                  <input type="hidden" name="colaborador" value="<?php echo (int) $selected_id; ?>">
                  <input type="hidden" name="inicio" value="<?php echo html_escape($start_date); ?>">
                  <input type="hidden" name="fim" value="<?php echo html_escape($end_date); ?>">
                  <div class="row">
                    <div class="col-md-2 col-sm-6 form-group">
                      <label for="availability_type">Tipo</label>
                      <select id="availability_type" name="availability_type" class="form-control" required>
                        <option value="feriado">Feriado (todos)</option>
                        <option value="ferias">Férias</option>
                        <option value="indisponivel">Outra indisponibilidade</option>
                      </select>
                    </div>
                    <div class="col-md-3 col-sm-6 form-group availability-collaborator">
                      <label for="availability_collaborator_id">Colaborador</label>
                      <select id="availability_collaborator_id" name="availability_collaborator_id" class="form-control">
                        <option value="">Selecione</option>
                        <?php foreach ($users as $user) { ?>
                          <option value="<?php echo (int) $user->id; ?>"><?php echo html_escape($user->nome); ?></option>
                        <?php } ?>
                      </select>
                    </div>
                    <div class="col-md-3 col-sm-12 form-group">
                      <label for="availability_title">Descrição</label>
                      <input type="text" id="availability_title" name="availability_title" class="form-control" maxlength="150" required>
                    </div>
                    <div class="col-md-2 col-sm-6 form-group">
                      <label for="availability_start">De</label>
                      <input type="date" id="availability_start" name="availability_start" class="form-control" value="<?php echo html_escape($start_date); ?>" required>
                    </div>
                    <div class="col-md-2 col-sm-6 form-group">
                      <label for="availability_end">Até (inclusive)</label>
                      <input type="date" id="availability_end" name="availability_end" class="form-control" value="<?php echo html_escape($start_date); ?>" required>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-warning"><i class="fa fa-calendar-plus-o"></i> Cadastrar indisponibilidade</button>
                <?php echo form_close(); ?>
              </div>
              <div class="box-body table-responsive no-padding">
                <table class="table table-striped">
                  <thead><tr><th>Tipo</th><th>Descrição</th><th>Colaborador</th><th>Período</th><th></th></tr></thead>
                  <tbody>
                    <?php if (!$unavailabilities) { ?>
                      <tr><td colspan="5" class="text-muted">Nenhuma indisponibilidade cadastrada no período filtrado.</td></tr>
                    <?php } ?>
                    <?php foreach ($unavailabilities as $availability) { ?>
                      <tr>
                        <td><?php echo $availability->tipo === 'feriado' ? 'Feriado' : ($availability->tipo === 'ferias' ? 'Férias' : 'Outra indisponibilidade'); ?></td>
                        <td><?php echo html_escape($availability->titulo); ?></td>
                        <td><?php echo $availability->tipo === 'feriado' ? 'Todos' : html_escape($availability->colaborador); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($availability->data_inicio)); ?> a <?php echo date('d/m/Y', strtotime($availability->data_fim)); ?></td>
                        <td class="text-right">
                          <?php echo form_open('kanban/planejamento', array('method' => 'post', 'class' => 'form-inline')); ?>
                            <input type="hidden" name="action" value="delete_unavailability">
                            <input type="hidden" name="availability_id" value="<?php echo (int) $availability->id; ?>">
                            <input type="hidden" name="colaborador" value="<?php echo (int) $selected_id; ?>">
                            <input type="hidden" name="inicio" value="<?php echo html_escape($start_date); ?>">
                            <input type="hidden" name="fim" value="<?php echo html_escape($end_date); ?>">
                            <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Remover esta indisponibilidade da agenda?');"><i class="fa fa-trash"></i> Remover</button>
                          <?php echo form_close(); ?>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
  </section>
</div>
<div class="modal fade" id="apontamentosModal" tabindex="-1" role="dialog" aria-labelledby="apontamentosModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="apontamentosModalLabel">Horas apontadas <small id="apontamentosModalSubtitulo"></small></h4>
      </div>
      <div class="modal-body table-responsive">
        <p class="text-muted" id="apontamentosModalStatus"></p>
        <table class="table table-striped" id="apontamentosModalTabela" style="display: none;">
          <thead><tr><th class="js-col-dia">Dia</th><th>Chamado</th><th>Planejamento</th><th class="text-right">Horas</th></tr></thead>
          <tbody></tbody>
          <tfoot><tr><th class="js-col-dia"></th><th colspan="2" class="text-right">Total</th><th class="text-right" id="apontamentosModalTotal"></th></tr></tfoot>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  window.addEventListener('load', function() {
    function formatHours(hours) {
      return hours.toFixed(1).replace('.', ',') + ' h';
    }

    $('.js-ver-apontamentos').on('click', function(event) {
      event.preventDefault();
      var link = $(this);
      var singleDay = link.data('inicio') === link.data('fim');
      var table = $('#apontamentosModalTabela');
      var status = $('#apontamentosModalStatus');
      $('#apontamentosModalSubtitulo').text(link.data('titulo'));
      table.hide().find('tbody').empty();
      table.find('.js-col-dia').toggle(!singleDay);
      status.show().text('Carregando...');
      $('#apontamentosModal').modal('show');

      $.getJSON('<?php echo base_url('kanban/planejamento_apontamentos'); ?>', {
        colaborador: link.data('colaborador'),
        inicio: link.data('inicio'),
        fim: link.data('fim')
      }).done(function(response) {
        var items = response.itens || [];
        if (!items.length) {
          status.text('Nenhum apontamento encontrado no período.');
          return;
        }
        var total = 0;
        var body = table.find('tbody');
        $.each(items, function(index, item) {
          total += item.horas;
          var row = $('<tr>');
          if (!singleDay) {
            row.append($('<td>').text(item.data));
          }
          var ticket = $('<td>').append(
            $('<a target="_blank">').attr('href', item.url).text('#' + item.chamado_id + ' — ' + item.titulo)
          ).append('<br>').append(
            $('<small class="text-muted">').text(item.unidade + (item.status ? ' · ' + item.status : ''))
          );
          row.append(ticket);
          row.append($('<td>').append(
            item.planejado
              ? $('<span class="label label-success">').text('Planejado no dia')
              : $('<span class="label label-warning">').text('Fora do planejamento')
          ));
          row.append($('<td class="text-right">').text(formatHours(item.horas)));
          body.append(row);
        });
        $('#apontamentosModalTotal').text(formatHours(total));
        status.hide();
        table.show();
      }).fail(function(xhr) {
        var message = xhr.responseJSON && xhr.responseJSON.erro ? xhr.responseJSON.erro : 'Não foi possível carregar os apontamentos.';
        status.text(message);
      });
    });

    $('#colaborador').fastselect({searchPlaceholder: 'Buscar colaborador'});

    function formatDate(date) {
      var month = String(date.getMonth() + 1);
      var day = String(date.getDate());
      return date.getFullYear() + '-' + (month.length < 2 ? '0' : '') + month + '-' + (day.length < 2 ? '0' : '') + day;
    }

    $('.js-period-shortcut').on('click', function() {
      var weeks = parseInt($(this).data('weeks'), 10);
      var base = new Date();
      if (weeks !== 0) {
        var current = $('#inicio').val().split('-');
        if (current.length === 3) {
          base = new Date(parseInt(current[0], 10), parseInt(current[1], 10) - 1, parseInt(current[2], 10));
        }
        base.setDate(base.getDate() + weeks * 7);
      }
      var monday = new Date(base);
      monday.setDate(base.getDate() - ((base.getDay() + 6) % 7));
      var friday = new Date(monday);
      friday.setDate(monday.getDate() + 4);
      $('#inicio').val(formatDate(monday));
      $('#fim').val(formatDate(friday));
      $('#planningFilterForm').submit();
    });

    $('.js-config-reload').on('change', function() {
      window.location = '?config=1&<?php echo $current_filter_query; ?>&' + $(this).data('param') + '=' + encodeURIComponent(this.value);
    });

    $('#availability_type').on('change', function() {
      var isHoliday = this.value === 'feriado';
      $('.availability-collaborator').toggle(!isHoliday);
      $('#availability_collaborator_id').prop('required', !isHoliday);
      if (isHoliday) {
        $('#availability_collaborator_id').val('');
      }
    }).trigger('change');
  });
</script>
<style type="text/css">
  .planning-filter-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
  }
  .planning-filter-row .form-group {
    margin: 0 12px 8px 0;
  }
  .planning-filter-row label {
    display: block;
  }
  .planning-period .form-control {
    width: 150px;
  }
  .planning-status-summary {
    margin: 0;
  }
  .status-filter-options {
    padding-top: 8px;
  }
  .status-filter-options .checkbox-inline {
    margin: 4px 16px 4px 0;
  }
  .collaborator-filter .fstElement {
    width: 240px;
    height: 34px;
    vertical-align: middle;
    border-color: #d2d6de;
    border-radius: 3px;
  }
  .collaborator-filter .fstToggleBtn {
    min-width: 0;
    line-height: 20px;
  }
  .planning-section-title {
    margin: 4px 0 12px;
  }
  .planning-day {
    margin-bottom: 10px;
  }
  .planning-day-meta {
    margin-left: 8px;
    color: #777;
    font-size: 13px;
  }
  .planning-load {
    display: inline-block;
    margin-right: 6px;
    font-size: 13px;
    vertical-align: middle;
  }
  .planning-load .progress {
    display: inline-block;
    width: 80px;
    margin: 0 6px 0 0;
    vertical-align: middle;
  }
  .planning-unavailable {
    padding: 8px 10px;
    border-bottom: 1px solid #f4f4f4;
  }
  .planning-empty {
    margin: 0;
    padding: 8px 10px;
  }
  .planning-tasks td {
    vertical-align: middle !important;
  }
  .access-list {
    max-height: 260px;
    overflow-y: auto;
    border: 1px solid #f4f4f4;
    padding: 0 10px;
  }
</style>
