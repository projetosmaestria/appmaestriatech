<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="box <?php echo $chamados_sem_planejamento ? 'box-warning' : 'box-default'; ?>">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-calendar-o text-yellow"></i> <?php echo html_escape($titulo); ?></h3>
    <div class="box-tools pull-right">
      <span class="label label-warning"><?php echo count($chamados_sem_planejamento); ?></span>
    </div>
  </div>
  <div class="box-body table-responsive">
    <?php if (!$chamados_sem_planejamento) { ?>
      <p class="text-muted mb-0">Não há chamados sem data de planejamento atribuídos a este colaborador.</p>
    <?php } else { ?>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Chamado</th><th>Unidade</th><th>Status</th><th>Prazo</th>
            <?php if ($can_edit_schedule) { ?><th>Ação</th><?php } ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($chamados_sem_planejamento as $chamado) { ?>
            <tr>
              <td>
                <a href="<?php echo base_url('chamados/details/' . (int) $chamado->id . '?redirect=kanban'); ?>" target="_blank">
                  #<?php echo (int) $chamado->id; ?> — <?php echo html_escape($chamado->titulo); ?>
                </a>
              </td>
              <td><?php echo html_escape($chamado->unidade); ?></td>
              <td><?php echo html_escape($chamado->status ?: ''); ?></td>
              <td><?php echo $chamado->data_prazo ? date('d/m/Y H:i', strtotime($chamado->data_prazo)) : '—'; ?></td>
              <?php if ($can_edit_schedule) { ?>
                <td>
                  <button type="button" class="btn btn-primary btn-sm js-programar-chamado"
                    data-toggle="modal" data-target="#programarChamadoModal"
                    data-ticket-id="<?php echo (int) $chamado->id; ?>"
                    data-collaborator-id="<?php echo (int) $collaborator_id; ?>"
                    data-scheduled-date="<?php echo date('Y-m-d'); ?>"
                    data-deadline="<?php echo $chamado->data_prazo ? date('Y-m-d\TH:i', strtotime($chamado->data_prazo)) : ''; ?>"
                    data-priority-id="<?php echo (int) $chamado->prioridade_id; ?>"
                    data-level-id="<?php echo (int) $chamado->nivel_id; ?>">
                    <i class="fa fa-calendar"></i> Programar
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
<?php if ($can_edit_schedule && (!isset($render_modal) || $render_modal)) { ?>
  <?php $this->load->view('admin/kanban/_modal_programar_chamado', array(
      'collaborator_id' => $collaborator_id,
      'priorities' => $priorities,
      'levels' => $levels,
      'start_date' => $start_date,
      'end_date' => $end_date,
      'status_filter_query' => $status_filter_query,
  )); ?>
<?php } ?>
