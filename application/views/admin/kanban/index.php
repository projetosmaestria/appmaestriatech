<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper kanban">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>

        <?php /* if($can_metricas) { ?>
          <a href="<?= base_url('kanban/metricas') ?>"><i class="fa fa-line-chart" aria-hidden="true"></i> Métricas</a>
        <?php } */ ?>
    </section>

    <section class="content pb-3">

      <div class="row">
        <div class="col-md-12">
          <div class="box box-default collapsed-box">
          <!-- <div class="box box-default "> -->
            <div class="box-header with-border" style="cursor:default !important;">
              <h3 class="box-title">Filtros</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
              </div>
              <!-- /.box-tools -->
            </div>
            
            <div class="box-body">
              <?php echo form_open('', array('class' => 'form-horizontal', 'id' => 'form-kanban-filtro', 'method' => 'get')); ?>
                <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
                <input type="hidden" name="acesso_interno" id="acesso_interno" value="1">
                <div class="nav-tabs-custom">
                  <ul class="nav nav-tabs">
                    <?php foreach ($quadros as $quadro) { ?>
                      <?php 
                        $class_nav = "";
                        if($quadros_sel == $quadro->id) { 
                          $class_nav = "active";
                        }
                      ?>
                      <input type="hidden" id="usuario" name="usuario[<?= $quadro->id ?>]" value="<?php echo @$_GET['filtro_usuarios'][$quadro->id] ?>">
                      <li class="<?php echo $class_nav ?>"><a href="#tab_<?php echo $quadro->id ?>" data-toggle="tab"><?php echo $quadro->nome ?></a></li>
                    <?php } ?>
                  </ul>
                  <div class="tab-content">
                    <?php foreach ($quadros as $quadro) { ?>
                      <?php 
                        $class_nav = "";
                        if($quadros_sel == $quadro->id) { 
                          $class_nav = "active";
                        }
                      ?>
                      <div class="tab-pane <?php echo $class_nav ?>" id="tab_<?php echo $quadro->id ?>">
                        <div class="form-group">
                          <?php echo lang('filtro_colunas', 'filtro_colunas', array('class' => 'col-md-1 control-label')); ?>
                          <div class="col-md-11">
                              <?php echo form_dropdown($filtros['filtro_colunas'][$quadro->id]);?>
                          </div>
                        </div>
                        <div class="form-group">
                            <?php echo lang('filtro_usuarios', 'filtro_usuarios', array('class' => 'col-md-1 control-label')); ?>
                            <div class="col-md-11">
                                <?php echo form_dropdown($filtros['filtro_usuarios'][$quadro->id]);?>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php echo lang('filtro_unidades', 'filtro_unidades', array('class' => 'col-md-1 control-label')); ?>
                            <div class="col-md-11">
                                <?php echo form_dropdown($filtros['filtro_unidades'][$quadro->id]);?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right">
                              <button type="submit" name="btn_filtrar" class="btn btn-flat btn_form_contato" value="<?php echo $quadro->id ?>"><i class="fa fa-search"></i> Filtrar</button>
                            </div>
                        </div>
                      </div>
                    <?php } ?>
                  </div>
                </div>
              <?php form_close(); ?>
            </div>
            <!-- /.box-body -->
            
          </div>
        </div>
      </div>
                        
      <div class="row">
        <div class="container-fluid h-100 quadros">
        <input type="hidden" name="quadro_id" id="quadro_id" value="<?php echo $quadros_sel ?>">
        
        <?php
          // Mapeamento de cores oficiais por status para os títulos das colunas do Kanban (famílias cromáticas distintas)
          $status_colors_map = array(
              1  => array('bg' => '#28a745', 'txt' => '#ffffff'), // Aberto (Verde)
              13 => array('bg' => '#ffc107', 'txt' => '#212529'), // Aguardando Aprovação (Amarelo Ouro)
              11 => array('bg' => '#007bff', 'txt' => '#ffffff'), // Fazendo (Azul)
              12 => array('bg' => '#795548', 'txt' => '#ffffff'), // Aguardando Atualização Sistema (Marrom)
              6  => array('bg' => '#9c27b0', 'txt' => '#ffffff'), // Aguardando Retorno do Cliente (Laranja)
              14 => array('bg' => '#ec8f24', 'txt' => '#ffffff'), // Aguardando Testes Sanclá
              8  => array('bg' => '#e84393', 'txt' => '#ffffff'), // Aguardando Testes com o Cliente (Rosa Choque)
              9  => array('bg' => '#605ca8', 'txt' => '#ffffff'), // Sugestão de Desenvolvimento (Roxo)
              10 => array('bg' => '#00c0ef', 'txt' => '#ffffff'), // Implantação (Ciano)
              7  => array('bg' => '#6c757d', 'txt' => '#ffffff'), // Suspenso (Cinza)
              3  => array('bg' => '#212529', 'txt' => '#ffffff'), // Fechado (Preto Grafite)
              5  => array('bg' => '#6b1d2f', 'txt' => '#ffffff'), // Avaliado (Vinho / Bordô)
          );

          $status_name_map = array(
              'aberto'                          => array('bg' => '#28a745', 'txt' => '#ffffff'),
              'aguardando aprovação'            => array('bg' => '#ffc107', 'txt' => '#212529'),
              'aguardando aprovacao'            => array('bg' => '#ffc107', 'txt' => '#212529'),
              'fazendo'                         => array('bg' => '#007bff', 'txt' => '#ffffff'),
              'aguardando atualização sistema'  => array('bg' => '#795548', 'txt' => '#ffffff'),
              'aguardando atualizacao sistema'  => array('bg' => '#795548', 'txt' => '#ffffff'),
              'aguardando retorno do cliente'   => array('bg' => '#9c27b0', 'txt' => '#ffffff'),
              'aguardando retorno'              => array('bg' => '#9c27b0', 'txt' => '#ffffff'),
              'aguardando testes sanclá'        => array('bg' => '#ec8f24', 'txt' => '#ffffff'),
              'aguardando testes sancla'        => array('bg' => '#ec8f24', 'txt' => '#ffffff'),
              'aguardando testes com o cliente' => array('bg' => '#e84393', 'txt' => '#ffffff'),
              'aguardando testes'               => array('bg' => '#e84393', 'txt' => '#ffffff'),
              'aguardando teste'                => array('bg' => '#e84393', 'txt' => '#ffffff'),
              'sugestão de desenvolvimento'     => array('bg' => '#605ca8', 'txt' => '#ffffff'),
              'sugestao de desenvolvimento'     => array('bg' => '#605ca8', 'txt' => '#ffffff'),
              'implantação'                     => array('bg' => '#00c0ef', 'txt' => '#ffffff'),
              'implantacao'                     => array('bg' => '#00c0ef', 'txt' => '#ffffff'),
              'suspenso'                        => array('bg' => '#6c757d', 'txt' => '#ffffff'),
              'fechado'                         => array('bg' => '#212529', 'txt' => '#ffffff'),
              'em avaliação'                    => array('bg' => '#212529', 'txt' => '#ffffff'),
              'em avaliacao'                    => array('bg' => '#212529', 'txt' => '#ffffff'),
              'avaliado'                        => array('bg' => '#6b1d2f', 'txt' => '#ffffff'),
          );

          // Forçar a ordenação visual das colunas com base no mapeamento `$status_colors_map`
          $ordem_colunas = array_keys($status_colors_map);
          if (!empty($quadros) && isset($quadros[$quadros_sel]) && !empty($quadros[$quadros_sel]->colunas)) {
              usort($quadros[$quadros_sel]->colunas, function($a, $b) use ($ordem_colunas) {
                  $pos_a = array_search(isset($a['status']) ? (int)$a['status'] : 0, $ordem_colunas);
                  $pos_b = array_search(isset($b['status']) ? (int)$b['status'] : 0, $ordem_colunas);
                  $pos_a = $pos_a !== false ? $pos_a : 999;
                  $pos_b = $pos_b !== false ? $pos_b : 999;
                  return $pos_a - $pos_b;
              });
          }
        ?>

        <?php if (!empty($quadros) && isset($quadros[$quadros_sel]) && !empty($quadros[$quadros_sel]->colunas)) { ?>
        <?php foreach ($quadros[$quadros_sel]->colunas as $coluna) { ?>

          <?php 
            $is_aprovacao = (isset($coluna['status']) && $coluna['status'] == 13) || (isset($coluna['nome']) && mb_strtolower(trim($coluna['nome'])) == 'aguardando aprovação');
            $mostrar_coluna = in_array($coluna['id'], $colunas_sel[$quadros_sel]) || (!isset($_GET['btn_filtrar']) && $is_aprovacao);
          ?>

          <?php if ($mostrar_coluna) { ?>
          <?php
            $bg_header = !empty($coluna['bg_cor']) ? $coluna['bg_cor'] : '#6c757d';
            $txt_header = !empty($coluna['txt_cor']) ? $coluna['txt_cor'] : '#ffffff';
            $status_col = isset($coluna['status']) ? (int)$coluna['status'] : 0;
            $nome_slug = isset($coluna['nome']) ? trim(mb_strtolower($coluna['nome'], 'UTF-8')) : '';

            if (isset($status_colors_map[$status_col])) {
                $bg_header = $status_colors_map[$status_col]['bg'];
                $txt_header = $status_colors_map[$status_col]['txt'];
            } elseif (isset($status_name_map[$nome_slug])) {
                $bg_header = $status_name_map[$nome_slug]['bg'];
                $txt_header = $status_name_map[$nome_slug]['txt'];
            }
          ?>
            <div class="card card-row card-secondary">
              <div class="card-header" style="background-color: <?php echo $bg_header ?>;">
                <h3 class="card-title" style="color: <?php echo $txt_header ?>;">
                  <?php echo $coluna['nome'] ?>
                </h3>
                <?php if($coluna['btn_criar_tarefa'] == "1") { ?>
                  <div class="card-tools">
                    <a href="<?php echo base_url('chamados/create?quadro='.$quadros_sel.'&coluna='.$coluna['id']) ?>" target="_blank" class="btn btn-tool">
                      <i class="fa fa-plus"></i>
                    </a>
                  </div>
                <?php } ?>
              </div>
              <div class="card-body">
                <section class="connectedSortable" data-coluna="<?php echo $coluna['id'] ?>">

                  <?php foreach ($coluna['tarefas'] as $tarefa) { ?>
                    <?php
                      // Não exibir chamado se pertencer a unidade não permitida ao usuário
                      if (isset($unidades_permitidas_ids) && !empty($unidades_permitidas_ids) && isset($tarefa['unidade_id']) && !in_array((int)$tarefa['unidade_id'], $unidades_permitidas_ids)) {
                        continue;
                      }

                      $color_border_top = "#3c8dbc";
                      $color_font_top = "#555";
                      $is_atrasado = false;
                      if($tarefa['data_prazo'] && $tarefa['data_prazo'] < date('Y-m-d H:i:s')) {
                        $color_font_top = "#d9534f";
                        $color_border_top = "#d9534f";
                        $is_atrasado = true;
                      }

                      $tag_info = '';
                      if($tarefa['prioridade'] == "Alta") {
                        $tag_info = '<span class="label label-danger" title="Prioridade Alta" style="font-size: 10px; font-weight: normal; padding: 2px 6px; display: inline-block; margin-top: 2px;"><i class="fa fa-exclamation-circle"></i> Alta</span>';
                      } elseif($tarefa['prioridade'] == "Baixa") {
                        $tag_info = '<span class="label label-info" title="Prioridade Baixa" style="font-size: 10px; font-weight: normal; padding: 2px 6px; display: inline-block; margin-top: 2px;"><i class="fa fa-arrow-down"></i> Baixa</span>';
                      }
                    ?>
                    <div class="box" data-tarefa="<?php echo $tarefa['tarefa'] ?>" data-ordem="<?php echo $tarefa['ordem'] ?>" style="border-top: 3px solid <?= $color_border_top ?>; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.12); margin-bottom: 12px; background: #fff;">
                      <div class="box-body box-header" style="padding: 12px; cursor: move;">
                        
                        <!-- Header: ID and Priority tag -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                          <a href="#" class="detalhesTarefa" data-id="<?php echo $tarefa['tarefa'] ?>" style="font-weight: bold; color: #3c8dbc; font-size: 13px;">#<?php echo $tarefa['tarefa'] ?></a>
                          <?= $tag_info ?>
                        </div>
                        
                        <!-- Title -->
                        <h5 style="margin-top: 0; font-weight: 600; font-size: 13px; line-height: 1.4; color: #333; margin-bottom: 10px; word-break: break-word;">
                            <?= mb_strimwidth($tarefa['titulo'], 0, 100, "...") ?>
                        </h5>

                        <!-- Unidade -->
                        <div style="font-size: 11px; color: #666; margin-bottom: 10px; background: #f4f4f4; padding: 4px 6px; border-radius: 3px; border: 1px solid #eee;">
                            <i class="fa fa-building-o" style="width: 14px; text-align: center; margin-right: 3px;"></i> <?= $tarefa['unidade'] ?>
                        </div>

                        <!-- Users -->
                        <div style="font-size: 11px; color: #555; margin-bottom: 10px; line-height: 1.6; background: #fafafa; padding: 6px; border-radius: 3px; border-left: 2px solid #ddd;">
                            <div title="Dono do chamado">
                                <i class="fa fa-user-circle" style="width: 14px; text-align: center; color: #888;"></i> <strong>Dono:</strong> <?= $tarefa['dono'] ?>
                            </div>
                            <div title="Responsável atual">
                                <i class="fa fa-user" style="width: 14px; text-align: center; color: #888;"></i> <strong>Resp.:</strong> <?= $tarefa['resp'] ?>
                            </div>
                            <?php if(@$tarefa['dev']) { ?>
                                <div title="Desenvolvedor alocado">
                                    <i class="fa fa-code" style="width: 14px; text-align: center; color: #888;"></i> <strong>Dev.:</strong> <?= $tarefa['dev'] ?>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Footer: Dates & Actions -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid #f0f0f0; padding-top: 8px;">
                            <!-- Dates -->
                            <div style="font-size: 10px; line-height: 1.5;">
                                <div style="color: #888;" title="Data de Criação">
                                    <i class="fa fa-calendar-plus-o"></i> Criação: <?= ($tarefa['created']) ? date("d/m/y", strtotime($tarefa['created'])) : "-" ?>
                                </div>
                                <div style="color: <?= $color_font_top ?>; <?php echo $is_atrasado ? 'font-weight: bold;' : ''; ?>" title="Data de Previsão">
                                    <i class="fa fa-calendar-check-o"></i> Previsão: <?= ($tarefa['data_prazo']) ? date("d/m/y", strtotime($tarefa['data_prazo'])) : "-" ?>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div>
                              <a href="<?php echo base_url('chamados/edit/'.$tarefa['tarefa'].'?redirect=kanban') ?>" target="_blank" class="btn btn-default btn-xs" title="Editar" style="color: #666 !important; padding: 1px 4px !important; font-size: 10px !important;">
                                <i class="fa fa-pencil"></i>
                              </a>
                              <a href="<?php echo base_url('chamados/details/'.$tarefa['tarefa'].'?redirect=kanban') ?>" target="_blank" class="btn btn-default btn-xs" title="Detalhes" style="color: #666 !important; padding: 1px 4px !important; margin-left: 2px !important; font-size: 10px !important;">
                                <i class="fa fa-list-ul"></i>
                              </a>
                            </div>
                        </div>

                      </div>
                    </div>

                  <?php } ?>

                </section>
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
                <!--<div class="file-loading">
                  <input id="file-3" type="file" name="files[]" multiple>
                </div>-->
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

              <br>

              <h5><strong>Prioridade</strong></h5>
              <select name="prioridade" id="prioridade" class="form-control">
                
              </select>

              <br>

              <h5><strong>Previsão</strong></h5>
              <p class="previsao"></p>

              <br>

              <h5><strong>Início</strong></h5>
              <p class="inicio"></p>

              <br>

              <h5><strong>Finalização</strong></h5>
              <p class="finalizacao"></p>

              <br>

              <h5><strong>Esforço</strong></h5>
              <p class="esforco"></p>

              <br>

              <h5><strong>Plano de Ação</strong></h5>
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


<style>
  .quadros {
    display: flex !important;
    flex-wrap: nowrap !important;
    overflow-x: auto !important;
    width: 100% !important;
  }
  .quadros .card-row {
    flex: 1 1 0 !important;
    min-width: 200px !important;
    margin: 0 5px !important;
    max-width: none !important;
  }
</style>

<script>
  window.onload = function() {
    $('.multipleSelect').fastselect();
  };
</script>