<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$current_user_id = (int) $this->session->userdata('user_id');
$board_url = function ($board_id) use ($current_user_id) {
    return base_url('kanban') . '?' . http_build_query(array(
        'btn_filtrar' => $board_id,
        'filtro_usuarios' => array($board_id => $current_user_id),
    ));
};
$current_board = isset($quadros[$quadros_sel]) ? $quadros[$quadros_sel] : null;
$selected_user = ($current_board && isset($filtros['filtro_usuarios'][$quadros_sel]['selected'])) ? $filtros['filtro_usuarios'][$quadros_sel]['selected'] : '';
?>
<div class="content-wrapper kanban">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content pb-3">
      <input type="hidden" id="base_url" value="<?php echo base_url() ?>">
      <input type="hidden" id="acesso_interno" value="1">

      <div class="kanban-toolbar">
        <?php if (count($quadros) > 1) { ?>
          <ul class="nav nav-pills kanban-boards">
            <?php foreach ($quadros as $quadro) { ?>
              <li class="<?php echo (string) $quadros_sel === (string) $quadro->id ? 'active' : ''; ?>">
                <a href="<?php echo $board_url($quadro->id); ?>"><?php echo html_escape($quadro->nome) ?></a>
              </li>
            <?php } ?>
          </ul>
        <?php } ?>
        <div class="kanban-shortcuts">
          <?php if ($can_metricas) { ?>
            <a href="<?= base_url('kanban/metricas') ?>" class="btn btn-default btn-sm"><i class="fa fa-line-chart" aria-hidden="true"></i> Métricas</a>
          <?php } ?>
          <?php if ($this->ion_auth->is_admin() || $this->ion_auth->has_permissao('kanban', 'index')) { ?>
            <a href="<?= base_url('kanban/planejamento') ?>" class="btn btn-default btn-sm"><i class="fa fa-calendar" aria-hidden="true"></i> Planejamento da equipe</a>
          <?php } ?>
        </div>
      </div>

      <?php if ($current_board && isset($filtros)) { ?>
        <div class="box box-solid kanban-filter">
          <div class="box-body">
            <?php echo form_open('kanban', array('id' => 'form-kanban-filtro', 'method' => 'get')); ?>
              <input type="hidden" name="btn_filtrar" value="<?php echo (int) $quadros_sel ?>">
              <div class="kanban-filter-row">
                <?php if (isset($filtros['filtro_usuarios'][$quadros_sel])) { ?>
                  <div class="form-group kanban-filter-user">
                    <?php echo lang('filtro_usuarios', 'filtro_usuarios-' . $quadros_sel); ?>
                    <?php echo form_dropdown($filtros['filtro_usuarios'][$quadros_sel]); ?>
                  </div>
                <?php } ?>
                <?php if (isset($filtros['filtro_colunas'][$quadros_sel])) { ?>
                  <div class="form-group kanban-filter-multi">
                    <?php echo lang('filtro_colunas', 'filtro_colunas-' . $quadros_sel); ?>
                    <?php echo form_dropdown($filtros['filtro_colunas'][$quadros_sel]); ?>
                  </div>
                <?php } ?>
                <?php if (isset($filtros['filtro_unidades'][$quadros_sel])) { ?>
                  <div class="form-group kanban-filter-multi">
                    <?php echo lang('filtro_unidades', 'filtro_unidades-' . $quadros_sel); ?>
                    <?php echo form_dropdown($filtros['filtro_unidades'][$quadros_sel]); ?>
                  </div>
                <?php } ?>
                <div class="form-group">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filtrar</button>
                  <a href="<?php echo $board_url($quadros_sel); ?>" class="btn btn-default" title="Voltar para os meus chamados em todas as colunas">Limpar</a>
                </div>
              </div>
            <?php echo form_close(); ?>
          </div>
        </div>
      <?php } ?>

      <?php if (isset($chamados_atrasados)) {
          $this->load->view('admin/kanban/_chamados_atrasados', array(
              'chamados_atrasados' => $chamados_atrasados,
              'titulo' => 'Chamados atrasados atribuídos a você',
              'can_edit_schedule' => $can_edit_overdue_schedule,
              'collaborator_id' => $overdue_collaborator_id,
              'priorities' => $overdue_priorities,
              'levels' => $overdue_levels,
              'status_filter_query' => $status_filter_query,
              'collapsed' => true,
              'hide_when_empty' => true,
          ));
      } ?>

      <div class="row">
        <div class="container-fluid h-100 quadros">
        <input type="hidden" name="quadro_id" id="quadro_id" value="<?php echo $quadros_sel ?>">
        <input type="hidden" name="usuario[<?php echo (int) $quadros_sel ?>]" value="<?php echo html_escape(is_array($selected_user) ? '' : $selected_user) ?>">

        <?php if ($current_board && !empty($current_board->colunas)) { ?>
        <?php foreach ($current_board->colunas as $coluna) { ?>

          <?php if (isset($colunas_sel[$quadros_sel]) && in_array($coluna['id'], $colunas_sel[$quadros_sel])) { ?>

            <div class="card card-row card-secondary">
              <div class="card-header" style="background-color: <?php echo $coluna['bg_cor'] ?>;">
                <h3 class="card-title" style="color: <?php echo $coluna['txt_cor'] ?>;">
                  <?php echo html_escape($coluna['nome']) ?>
                  <span class="badge kanban-column-count"><?php echo count($coluna['tarefas']) ?></span>
                </h3>
                <?php if ($coluna['btn_criar_tarefa'] == "1") { ?>
                  <div class="card-tools">
                    <a href="<?php echo base_url('chamados/create?quadro='.$quadros_sel.'&coluna='.$coluna['id']) ?>" target="_blank" class="btn btn-tool" title="Novo chamado nesta coluna" style="color: <?php echo $coluna['txt_cor'] ?>;">
                      <i class="fa fa-plus"></i>
                    </a>
                  </div>
                <?php } ?>
              </div>
              <div class="card-body">
                <section class="connectedSortable" data-coluna="<?php echo $coluna['id'] ?>">

                  <?php foreach ($coluna['tarefas'] as $tarefa) {
                      // Não exibir chamado se pertencer a unidade não permitida ao usuário
                      if (isset($unidades_permitidas_ids) && !empty($unidades_permitidas_ids) && isset($tarefa['unidade_id']) && !in_array((int)$tarefa['unidade_id'], $unidades_permitidas_ids)) {
                          continue;
                      }

                      $is_overdue = $tarefa['data_prazo'] && $tarefa['data_prazo'] < date('Y-m-d H:i:s');
                      $is_high_priority = $tarefa['prioridade'] == "Alta";
                  ?>
                    <div class="box kanban-task <?php echo $is_overdue ? 'kanban-task-overdue' : ''; ?>" data-tarefa="<?php echo $tarefa['tarefa'] ?>" data-ordem="<?php echo $tarefa['ordem'] ?>">
                      <div class="kanban-task-top">
                        <a href="#" class="detalhesTarefa" data-id="<?php echo $tarefa['tarefa'] ?>" title="Abrir detalhes e apontamento">#<?php echo $tarefa['tarefa'] ?></a>
                        <?php if ($is_high_priority) { ?><span class="label label-danger">Alta</span><?php } ?>
                        <span class="kanban-task-actions">
                          <a href="<?php echo base_url('chamados/details/'.$tarefa['tarefa'].'?redirect=kanban') ?>" target="_blank" title="Detalhes do chamado"><i class="fa fa-external-link"></i></a>
                          <a href="<?php echo base_url('chamados/edit/'.$tarefa['tarefa'].'?redirect=kanban') ?>" target="_blank" title="Editar"><i class="fa fa-pencil"></i></a>
                        </span>
                      </div>
                      <div class="kanban-task-title"><?php echo html_escape(mb_strimwidth($tarefa['titulo'], 0, 100, "...")) ?></div>
                      <div class="kanban-task-unit"><?php echo html_escape($tarefa['unidade']) ?></div>
                      <div class="kanban-task-meta">
                        <span class="<?php echo $is_overdue ? 'text-red' : ''; ?>" title="Previsão">
                          <i class="fa fa-clock-o"></i>
                          <?php echo $tarefa['data_prazo'] ? date("d/m/y", strtotime($tarefa['data_prazo'])) : "Sem previsão" ?>
                          <?php if ($is_overdue) { ?><strong>atrasado</strong><?php } ?>
                        </span>
                        <span class="text-muted" title="Criação">criado <?php echo $tarefa['created'] ? date("d/m/y", strtotime($tarefa['created'])) : "-" ?></span>
                      </div>
                      <div class="kanban-task-people">
                        <span title="Responsável"><i class="fa fa-user"></i> <?php echo html_escape($tarefa['resp']) ?></span>
                        <?php if (@$tarefa['dev']) { ?>
                          <span title="Desenvolvedor"><i class="fa fa-code"></i> <?php echo html_escape($tarefa['dev']) ?></span>
                        <?php } ?>
                        <span class="text-muted" title="Dono (solicitante)"><i class="fa fa-user-o"></i> <?php echo html_escape($tarefa['dono']) ?></span>
                      </div>
                    </div>
                  <?php } ?>

                </section>
                <?php if (!$coluna['tarefas']) { ?>
                  <p class="kanban-column-empty text-muted">Nenhum chamado</p>
                <?php } ?>
              </div>
            </div>

          <?php } ?>
        <?php } ?>
        <?php } ?>

        <div class="overlay" style="display:none;"><i class="fa fa-refresh fa-spin"></i></div>
      </div>
    </div>
  </section>
</div>

<!-- Modal -->
<div class="modal fade" id="detalhesTarefaModal" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>

        <input type="hidden" name="apontamento_id" id="apontamento_id" value="">
        <div class="left-myday">

        </div>
        <div class="informations-apontamento">
            <div class="progress progress-lg active">
                <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                </div>
            </div>
            <div class="duration-clock pull-left">00:00:00</div>
            <div class="inicio-apontamento pull-right" style="display:none;">Início <span class="inicio-apontamento-span"></span></div>
        </div>

        <div class="msgs_apont"></div>
      </div>
      <div class="modal-body">
        <input type="hidden" id="chamado_id" name="chamado_id" value="">
        <input type="hidden" id="obs_apontamento" name="obs_apontamento" value="">
        <div class="row">
          <div class=" col-md-9">

            <h3 class="modal-title"></h3>

            <p class="criada_em"></p>

            <hr>

            <div class="btn-group">
                <a class="btn btn-primary add_resp_dialogo" href="javascript:void(0);"><i class="fa fa-plus-circle" aria-hidden="true"></i> Diálogo</a>
            </div>
            &nbsp;
            <div class="btn-group">
                <a class="btn btn-primary add_resp_anexo" href="javascript:void(0);"><i class="fa fa-plus-circle" aria-hidden="true"></i> Anexo</a>
            </div>
            <div class="dialogo-anexo">

            </div>

            <hr>

            <ul class="nav nav-tabs">
              <li class="active"><a data-toggle="tab" href="#desc">Descrição</a></li>
              <li><a data-toggle="tab" href="#anexo">Anexo</a></li>
              <li><a data-toggle="tab" href="#hist">Histórico</a></li>
            </ul>

            <div class="tab-content">
              <div id="desc" class="tab-pane fade in active">
                <br><p></p>
              </div>
              <div id="anexo" class="tab-pane fade">
                <br>
                <ul class="timeline timeline-inverse timeline-anexo">

                </ul>
              </div>
              <div id="hist" class="tab-pane fade">
                <br>
                <ul class="timeline timeline-inverse timeline-hist">

                </ul>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="kanban-tarefa-info">
              <h5><strong>Status</strong></h5>
              <select name="status" id="status" class="form-control">

              </select>

              <h5><strong>Prioridade</strong></h5>
              <select name="prioridade" id="prioridade" class="form-control">

              </select>

              <h5><strong>Previsão</strong></h5>
              <p class="previsao"></p>

              <h5><strong>Início</strong></h5>
              <p class="inicio"></p>

              <h5><strong>Finalização</strong></h5>
              <p class="finalizacao"></p>

              <h5><strong>Esforço</strong></h5>
              <p class="esforco"></p>

              <h5><strong>Projeto</strong></h5>
              <p class="plano_acao"></p>
            </div>
          </div>

        </div>

        <div class="overlay" style="display:none;">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
  window.onload = function() {
    $('.multipleSelect').fastselect();
  };
</script>
<style type="text/css">
  .kanban-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
  }
  .kanban-boards {
    margin-bottom: 4px;
  }
  .kanban-shortcuts .btn {
    margin: 0 0 4px 4px;
  }
  .kanban-filter {
    margin-bottom: 10px;
  }
  .kanban-filter .box-body {
    padding: 10px;
  }
  .kanban-filter-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
  }
  .kanban-filter-row .form-group {
    margin: 0 12px 0 0;
  }
  .kanban-filter-row label {
    display: block;
  }
  .kanban-filter-user .fstElement {
    width: 220px;
  }
  .kanban-filter-multi .fstElement {
    width: 280px;
  }
  .kanban-column-count {
    margin-left: 4px;
    background-color: rgba(0, 0, 0, .25);
  }
  .kanban-column-empty {
    margin: 8px 4px;
    font-size: 12px;
    text-align: center;
  }
  .content-wrapper.kanban .kanban-task {
    margin-bottom: 8px;
    padding: 8px 10px;
    border-top: 0;
    border-left: 3px solid #ccc;
    cursor: grab;
  }
  .content-wrapper.kanban .kanban-task-overdue {
    border-left-color: #8b0000;
  }
  .kanban-task-top {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
  }
  .kanban-task-top .detalhesTarefa {
    font-weight: bold;
  }
  .kanban-task-actions {
    margin-left: auto;
  }
  .kanban-task-actions a {
    color: #777;
    margin-left: 8px;
  }
  .kanban-task-title {
    margin: 4px 0 2px;
    font-weight: 600;
    line-height: 1.3;
  }
  .kanban-task-unit {
    color: #777;
    font-size: 11px;
    margin-bottom: 4px;
  }
  .kanban-task-meta,
  .kanban-task-people {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 10px;
    font-size: 11px;
  }
  .kanban-task-meta {
    justify-content: space-between;
    margin-bottom: 2px;
  }
  .kanban-tarefa-info h5 {
    margin-top: 14px;
  }
</style>