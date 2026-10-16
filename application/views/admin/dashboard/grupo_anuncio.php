<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// exit("<pre>".print_r($grafico,true)."</pre>");
?>

<section class="col-lg-12 connectedSortable">
  <div class="box box-info">
    <div class="box-header with-border">
      <i class="fa fa-facebook-square"></i>
      <h3 class="box-title"><?php echo lang('ad_groups') ?></h3>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="box-body">
      <table id="datatable" class="table">
        <thead>
            <tr>
                <th><?php echo lang('id');?></th>
                <th><?php echo lang('filial');?></th>
                <th><?php echo lang('midia');?></th>
                <th><?php echo lang('fb_id');?></th>
                <th><?php echo lang('descricao');?></th>
                <th><?php echo lang('criado_em');?></th>
                <th><?php echo lang('status');?></th>
                <th><?php echo lang('action');?></th>
            </tr>
        </thead>
        <tbody>
      <?php foreach ($eventos as $evento):?>
            <tr>
                <td><?php echo $evento->id; ?></td>
                <td><?php echo htmlspecialchars($evento->unidade_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($evento->midia_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo $evento->fb_id; ?></td>
                <td><?php echo htmlspecialchars($evento->descricao, ENT_QUOTES, 'UTF-8'); ?></td>
                <td data-order="<?php echo strtotime($evento->created_at);?>"><?php echo date("d/m/Y" , strtotime($evento->created_at)); ?></td>
                <td><?php echo ($evento->status == '1') ? '<span class="label label-success">'.lang('status_ativo').'</span>' : '<span class="label label-default">'.lang('status_inativo').'</span>'; ?></td>
                <td><?php echo anchor("dashboard/sincronizar_anuncios/".$evento->id, '<i class="fa fa-bullhorn"></i> Sincronizar Anúncios', array('class'=>'btn btn-info btn-lista', 'alt'=>lang('sincronizar_anuncios'),'title'=>lang('sincronizar_anuncios'))); ?></td>
            </tr>
      <?php endforeach;?>
        </tbody>
      </table>
    </div>
    <!-- /.box-body -->
  </div>
  <!-- /.box -->
</section>
