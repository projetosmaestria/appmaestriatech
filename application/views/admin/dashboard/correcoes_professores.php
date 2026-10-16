<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// exit("<pre>".print_r($grafico,true)."</pre>");
?>

<section class="col-lg-6 connectedSortable">
  <div class="box box-info">
    <div class="box-header with-border">
      <i class="fa fa-book"></i>
      <h3 class="box-title">Respostas aguardando correção</h3>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="box-body">
      <table id="datatable_correcoes" class="table">
        <thead>
            <tr>
                <th><?php echo lang('professor');?></th>
                <th><?php echo lang('qtd_maior_2_dias');?></th>
                <th><?php echo lang('qtd_total');?></th>
            </tr>
        </thead>
        <tbody>
          <?php foreach ($correcoes as $correcao):?>
                <tr>
                    <td><?php echo htmlspecialchars($correcao["professor"], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $correcao["maior_2_dias"]; ?></td>
                    <td><?php echo $correcao["total"]; ?></td>
                </tr>
          <?php endforeach;?>
        </tbody>
      </table>
    </div>
    <!-- /.box-body -->
  </div>
  <!-- /.box -->
</section>
