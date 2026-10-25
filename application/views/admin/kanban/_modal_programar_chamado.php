<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$redirect_query = 'colaborador=' . (int) $collaborator_id .
    '&inicio=' . rawurlencode($start_date) .
    '&fim=' . rawurlencode($end_date) .
    (!empty($status_filter_query) ? '&' . $status_filter_query : '');
?>
<div class="modal fade" id="programarChamadoModal" tabindex="-1" role="dialog" aria-labelledby="programarChamadoModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?php echo form_open('kanban/planejamento?' . $redirect_query, array('id' => 'formProgramarChamado')); ?>
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="programarChamadoModalLabel">Programar chamado <span id="programarChamadoNumero"></span></h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="update_schedule">
          <input type="hidden" name="ticket_id" id="programarTicketId">
          <input type="hidden" name="collaborator_id" value="<?php echo (int) $collaborator_id; ?>">
          <div class="form-group">
            <label for="programarData">Data planejada</label>
            <input type="date" class="form-control" name="scheduled_date" id="programarData" required>
          </div>
          <div class="form-group">
            <label for="programarPrazo">Novo prazo</label>
            <input type="datetime-local" class="form-control" name="deadline" id="programarPrazo" required>
          </div>
          <div class="form-group">
            <label for="programarPrioridade">Prioridade</label>
            <select class="form-control" name="priority_id" id="programarPrioridade" required>
              <?php foreach ($priorities as $priority) { ?>
                <option value="<?php echo (int) $priority->id; ?>"><?php echo html_escape($priority->descricao); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group">
            <label for="programarNivel">Nível</label>
            <select class="form-control" name="level_id" id="programarNivel" required>
              <?php foreach ($levels as $level) { ?>
                <option value="<?php echo (int) $level->id; ?>"><?php echo html_escape($level->descricao); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Salvar</button>
        </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>
<script type="text/javascript">
  window.addEventListener('load', function() {
    jQuery('.js-programar-chamado').on('click', function() {
      var button = jQuery(this);
      jQuery('#programarTicketId').val(button.data('ticket-id'));
      jQuery('#programarChamadoNumero').text('#' + button.data('ticket-id'));
      jQuery('#programarData').val(button.data('scheduled-date'));
      jQuery('#programarPrazo').val(button.data('deadline') || '');
      jQuery('#programarPrioridade').val(button.data('priority-id'));
      var levelSelect = jQuery('#programarNivel');
      var currentLevelId = String(button.data('level-id') || '');
      var hasCurrentLevel = levelSelect.find('option').filter(function() {
        return this.value === currentLevelId;
      }).length > 0;
      levelSelect.val(hasCurrentLevel ? currentLevelId : (levelSelect.find('option:first').val() || ''));
    });
  });
</script>
