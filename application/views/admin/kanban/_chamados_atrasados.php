<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$collapsed = !empty($collapsed);
if (!empty($hide_when_empty) && !$chamados_atrasados) {
    return;
}
?>
<div class="box <?php echo $chamados_atrasados ? 'box-danger' : 'box-default'; ?> <?php echo $collapsed ? 'collapsed-box' : ''; ?>">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-exclamation-triangle text-red"></i> <?php echo html_escape($titulo); ?></h3>
    <div class="box-tools pull-right">
      <span class="label label-danger"><?php echo count($chamados_atrasados); ?></span>
      <?php if ($collapsed) { ?>
        <button type="button" class="btn btn-box-tool" data-widget="collapse" title="Mostrar/ocultar"><i class="fa fa-plus"></i></button>
      <?php } ?>
    </div>
  </div>
  <div class="box-body table-responsive">
    <?php if (!$chamados_atrasados) { ?>
      <p class="text-muted mb-0">Não há chamados em atraso atribuídos a este usuário.</p>
    <?php } else { ?>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Chamado</th><th>Unidade</th><th>Status</th><th>Prazo atual</th>
            <?php if ($can_edit_schedule) { ?><th>Ação</th><?php } ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($chamados_atrasados as $chamado_atrasado) { ?>
            <tr>
              <td>
                <a href="<?php echo base_url('chamados/details/' . (int) $chamado_atrasado->id . '?redirect=kanban'); ?>" target="_blank">
                  #<?php echo (int) $chamado_atrasado->id; ?> — <?php echo html_escape($chamado_atrasado->titulo); ?>
                </a>
              </td>
              <td><?php echo html_escape($chamado_atrasado->unidade); ?></td>
              <td><?php echo html_escape($chamado_atrasado->status ?: ''); ?></td>
              <td class="text-red"><strong><?php echo date('d/m/Y H:i', strtotime($chamado_atrasado->data_prazo)); ?></strong></td>
              <?php if ($can_edit_schedule) {
                $form_id = 'programar_atrasado_' . (int) $chamado_atrasado->id;
                $scheduled_date = $chamado_atrasado->data_prevista ?: date('Y-m-d');
                $deadline = date('Y-m-d\TH:i', strtotime($chamado_atrasado->data_prazo));
              ?>
                <td>
                  <button type="button" class="btn btn-primary btn-sm js-programar-chamado"
                    data-toggle="modal" data-target="#programarChamadoModal"
                    data-ticket-id="<?php echo (int) $chamado_atrasado->id; ?>"
                    data-collaborator-id="<?php echo (int) $collaborator_id; ?>"
                    data-scheduled-date="<?php echo html_escape($scheduled_date); ?>"
                    data-deadline="<?php echo html_escape($deadline); ?>"
                    data-priority-id="<?php echo (int) $chamado_atrasado->prioridade_id; ?>"
                    data-level-id="<?php echo (int) $chamado_atrasado->nivel_id; ?>">
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
      'start_date' => date('Y-m-d'),
      'end_date' => date('Y-m-d'),
      'status_filter_query' => isset($status_filter_query) ? $status_filter_query : '',
  )); ?>
<?php } ?>
