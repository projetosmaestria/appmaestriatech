<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?>

<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if(isset($message)) {echo $message;} ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-body">
                        <div class="row">
                          <div class="col-sm-12">
                            <div class="alert alert-info alert-dismissible">
                              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                              <h4><i class="icon fa fa-info"></i> Informação!</h4>
                              Para ouvir áudios recebidos da plataforma iOS, faça o download do Codec <a href="http://www.codecguide.com/" target="_blank"><b>aqui!</b></a>
                            </div>
                            <div class="box box-default collapsed-box">
                              <div class="box-header with-border" style="cursor:default !important;">
                                <h3 class="box-title">Filtros Adicionais</h3>

                                <div class="box-tools pull-right">
                                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                  </button>
                                </div>
                                <!-- /.box-tools -->
                              </div>
                              <form id="form-respostas" action="<?php echo base_url('app_respostas/q_geral') ?>" method="post" class='form-horizontal'>
                                <input type="hidden" id="mostrar_corrigidos" name="mostrar_corrigidos" value="<?php echo @($mostrar_corrigidos == 1) ? 1 : 0 ?>">
                                <div class="box-body">
                                  <div class="row">
                                    <div class="form-group">
                                      <?php echo lang('buscar', 'buscar', array('class' => 'col-sm-1 control-label')); ?>
                                      <div class="col-sm-4">
                                        <input name="q" class="form-control" placeholder="Buscar..." type="text" value="<?php echo $q ?>">
                                      </div>
                                    </div>
                                    <div class="form-group">
                                      <?php echo lang('unidades', 'unidades', array('class' => 'col-sm-1 control-label')); ?>
                                      <div class="col-sm-4">
                                        <?php echo form_dropdown($unidades) ?>
                                      </div>
                                    </div>
                                    <div class="form-group">
                                      <?php echo lang('turmas', 'turmas', array('class' => 'col-sm-1 control-label')); ?>
                                      <div class="col-sm-4">
                                        <?php echo form_dropdown($turmas) ?>
                                      </div>
                                    </div>
                                    <div class="form-group">
                                      <?php echo lang('professores', 'professores', array('class' => 'col-sm-1 control-label')); ?>
                                      <div class="col-sm-4">
                                        <?php echo form_dropdown($professores) ?>
                                      </div>
                                    </div>
                                    <div class="form-group">
                                      <?php echo lang('mostrar_corrigidos', 'mostrar_corrigidos', array('class' => 'col-sm-1 control-label')); ?>
                                      <div class="col-sm-4">
                                        <input name="mostrar_corrigidos_toggle" id="mostrar_corrigidos_toggle" data-toggle="toggle" data-on="Sim" data-off="Não" type="checkbox" value="1"
                                        <?php
                                            echo (@$mostrar_corrigidos == 1) ? ' checked ':'';
                                        ?>
                                        >
                                      </div>
                                    </div>
                                    <div class="form-group">
                                      <?php echo lang('prazo', 'prazo', array('class' => 'col-sm-1 control-label')); ?>
                                      <div class="col-sm-1">
                                        <?php echo form_dropdown($prazo) ?>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                      <div class="col-sm-5 text-right">
                                          <button type="submit" id="search-btn" class="btn btn-flat" style="height: 35px;"><i class="fa fa-search"></i> Buscar</button>
                                          <?php if($limpar == 1): ?>
                                              &nbsp;<button type="submit" id="clear-btn" class="btn btn-flat" style="height: 35px;" name="limpar" value="1"><i class="fa fa-times" ></i> Limpar</button>
                                          <?php endif ?>
                                      </div>
                                  </div>
                                </div>
                                <!-- /.box-body -->
                              </form>
                            </div>
                          </div>
                        </div>
                        <table class="table table-striped table-hover table-respostas">
                            <thead>
                                <tr>
                                  <?php
                                    $class_th_aluno = 'sorting';
                                    $class_th_unidade = 'sorting';
                                    $class_th_data_envio = 'sorting';
                                    $order_aluno = 'asc';
                                    $order_unidade = 'asc';
                                    $order_data_envio = 'asc';
                                    if($sort_by == 'aluno') {
                                      if($order == "asc") {
                                        $class_th_aluno = 'sorting_asc';
                                        $order_aluno = 'desc';
                                      } else                                         
                                        $class_th_aluno = 'sorting_desc';
                                    }
                                    if($sort_by == 'unidade_livro') {
                                      if($order == "asc") {
                                        $class_th_unidade = 'sorting_asc';
                                        $order_unidade = 'desc';
                                      } else                                         
                                        $class_th_unidade = 'sorting_desc';
                                    }
                                    if($sort_by == 'data_envio') {
                                      if($order == "asc") {
                                        $class_th_data_envio = 'sorting_asc';
                                        $order_data_envio = 'desc';
                                      } else                                         
                                        $class_th_data_envio = 'sorting_desc';
                                    }
                                  ?>
                                    <th class="<?php echo $class_th_aluno ?>" href="<?php echo base_url(uri_string()."?sort_by=aluno&order=".$order_aluno) ?>"><?php echo lang('aluno');?></th>
                                    <th class="<?php echo $class_th_unidade ?>" href="<?php echo base_url(uri_string()."?sort_by=unidade_livro&order=".$order_unidade) ?>"><?php echo lang('unidade');?></th>
                                    <th class="<?php echo $class_th_data_envio ?>" href="<?php echo base_url(uri_string()."?sort_by=data_envio&order=".$order_data_envio) ?>"><?php echo lang('data_envio');?></th>
                                    <th><?php echo lang('corrigido');?></th>
                                    <th><?php echo lang('prazo');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($app_respostas as $value) { ?>
                                <tr>
                                  <td><?php echo $value->first_name." ".$value->last_name ?></td>
                                  <td><?php echo $value->unidade_livro." - ".$value->titulo." - ".$value->tipo ?></td>
                                  <td><?php echo $value->data_envio_formatada ?></td>
                                  <td><?php echo $value->status_convert ?></td>
                                  <td><?php echo $value->prazo_convert ?></td>
                                  <td><?php echo anchor("app_respostas/corrigir/".$value->unidade.'/'.$value->aluno.'/0', '<i class="fa fa-check-square-o"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_check'))) ?></td>
                                </tr>
                              <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer">
                        <?php echo $pagination; ?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    window.onload = function() {
        $('.multipleSelect').fastselect();

        $('.table-respostas th.sorting, .table-respostas th.sorting_asc, .table-respostas th.sorting_desc').click(function(){
            window.location = $(this).attr('href');
            return false;
        });
    };
</script>