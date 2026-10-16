<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// exit("<pre>".print_r($grafico,true)."</pre>");
?>

<section class="col-lg-6 connectedSortable">
  <div class="box box-info">
    <div class="box-header with-border">
      <i class="fa fa-book"></i>
      <h3 class="box-title">Materiais (Livros)</h3>

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
                <th><?php echo lang('titulo');?></th>
                <th><?php echo lang('livro');?></th>
                <th><?php echo lang('data_criacao');?></th>
                <th><?php echo lang('actions');?></th>
            </tr>
        </thead>
        <tbody>
      <?php foreach ($materiais as $material):?>
            <tr>
                <td><?php echo htmlspecialchars($material->titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($material->titulo_livro, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo $material->data_criacao; ?></td>
                <td><a href="<?php echo base_url("upload/materiais/".$material->caminho) ?>" target="_blank" class="btn btn-primary btn-xs" rel="tooltip" data-placement="top" title="Download do anexo" download><i class="fa fa-floppy-o"></i> Download</a></td>
            </tr>
      <?php endforeach;?>
        </tbody>
      </table>
    </div>
    <!-- /.box-body -->
  </div>
  <!-- /.box -->
</section>