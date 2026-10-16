<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// exit("<pre>".print_r($grafico,true)."</pre>");
?>

<section class="col-lg-12 connectedSortable">
  <div class="box box-info">
    <div class="box-header with-border">
      <h3 class="box-title">Envio de respostas por semana</h3>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="box-body chart-responsive">
      <div class="chart" id="line-chart-audios" style="height: 300px;"></div>
    </div>
    <!-- /.box-body -->
  </div>
  <!-- /.box -->
</section>

<section class="col-lg-6 connectedSortable">
  <div class="box box-info">
    <div class="box-header with-border">
      <h3 class="box-title">Lista respostas enviadas</h3>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="box-body">
      <table id="datatable_lista" class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?php echo lang('aluno');?></th>
                <th><?php echo lang('unidade');?></th>
                <th><?php echo lang('data_envio');?></th>
                <th><?php echo lang('corrigido');?></th>
            </tr>
        </thead>
        <tbody>
      <?php foreach ($app_respostas as $resp):?>
            <tr>
                <td><?php echo htmlspecialchars($resp->first_name." ".$resp->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($resp->unidade_livro." - ".$resp->titulo, ENT_QUOTES, 'UTF-8'); ?> - <?php echo ($resp->tipo == "C") ? 'Complementar' : 'Obrigatória'; ?></td>
                <td><?php echo date("d/m/Y", strtotime($resp->data_resposta)); ?></td>
                <td><?php echo (@$resp->status == "C") ? 'Sim' : "Não"; ?></td>
            </tr>
      <?php endforeach;?>
        </tbody>
      </table>
    </div>
    <!-- /.box-body -->
  </div>
  <!-- /.box -->
</section>
<section class="col-lg-6 connectedSortable">
  <div class="box box-info">
    <div class="box-header with-border">
      <h3 class="box-title">Dispositivos do aluno</h3>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="box-body">
      <table id="datatable_device" class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?php echo lang('device_info');?></th>
                <th><?php echo lang('criado_em');?></th>
                <th><?php echo lang('expira_em');?></th>
            </tr>
        </thead>
        <tbody>
      <?php foreach ($dispositivos as $disp):?>
            <tr>
                <td><?php echo $disp->device_info; ?></td>
                <td><?php echo date("d/m/Y", strtotime($disp->token_criado_em)); ?></td>
                <td><?php echo date("d/m/Y", strtotime($disp->token_expira_em)); ?></td>
            </tr>
      <?php endforeach;?>
        </tbody>
      </table>
    </div>
    <!-- /.box-body -->
  </div>
  <!-- /.box -->
  <form id="form_dispositivos" action="<?php echo base_url('dashboard/remover_dispositivo') ?>" method="post">
    <input type="hidden" id="dispositivo_id" name="dispositivo_id" value="">
  </form>
</section>

<section class="col-lg-12 connectedSortable">
  <div class="box box-info">
    <div class="box-header with-border">
      <h3 class="box-title">Áudios</h3>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="box-body">
      <div class="row">
        <div class="col-sm-12">
          <div class="box box-default collapsed-box">
            <div class="box-header with-border" style="cursor:default !important;">
              <h3 class="box-title">Filtros Adicionais</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                </button>
              </div>
              <!-- /.box-tools -->
            </div>
            <?php echo form_open(base_url('dashboard'), array('class' => 'form-horizontal', 'id' => 'form-app_audios')); ?>
              <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
              <div class="box-body">
                <div class="row">
                  <div class="form-group">
                      <?php echo lang('filtro_livros', 'filtro_livros', array('class' => 'col-sm-1 control-label')); ?>
                      <div class="col-sm-3">
                          <?php echo form_dropdown($livros);?>
                      </div>
                  </div>
                </div>
                <div class="row">
                  <div class="form-group">
                      <?php echo lang('filtro_unidades', 'filtro_unidades', array('class' => 'col-sm-1 control-label')); ?>
                      <div class="col-sm-3">
                          <?php echo form_dropdown($unidades);?>
                      </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-sm-4 text-right">
                      <button type="submit" name="btn_filtrar" class="btn btn-flat"><i class="fa fa-search"></i> Filtrar</button>
                    </div>
                </div>
              </div>
              <!-- /.box-body -->
            <?php form_close(); ?>
          </div>
        </div>
      </div>
      <table id="datatable_audios" class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?php echo lang('ordem');?></th>
                <th><?php echo lang('titulo');?></th>
                <th><?php echo lang('livro');?></th>
                <th><?php echo lang('unidade');?></th>
                <th><?php echo lang('action');?></th>
            </tr>
        </thead>
        <tbody>
          <?php if($audios != false):?>
            <?php foreach ($audios->result() as $audio):?>
                  <tr>
                    <td><?php echo $audio->ordem; ?></td>
                    <td><?php echo htmlspecialchars($audio->titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($audio->livro, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($audio->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <button type="button" class="btn btn-default btn-lista" onclick="loadAudioBase64('<?php echo $audio->id ?>', '<?php echo base_url() ?>')"><i class="fa fa-play"></i></button>
                    </td>
                  </tr>
            <?php endforeach;?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <!-- /.box-body -->
  </div>
  <!-- /.box -->
</section>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <h3>Deseja remover este dispositivo?</h3>
            </div>
            <div class="modal-body">
              Confirme a remoção do item
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Remover</button>
            </div>
        </div>
    </div>
</div>

 <!-- Modal -->
<div class="modal fade" id="modalAudioBase64" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Ouvir Áudio</h4>                        
            </div><!--/modal-header-->
            <div class="modal-body">
                
            </div><!--/.modal-body-->
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>
            </div><!--/.modal-footer-->
        </div><!--/.modal-content-->
    </div><!--/.modal-dialog-->
</div><!--/.modal-->