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
                        <table id="datatable_tutoria_ex" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('contrato');?></th>
                                    <th><?php echo lang('cliente');?></th>
                                    <th><?php echo lang('tutor');?></th>
                                    <th><?php echo lang('data_hora_inicio');?></th>
                                    <th><?php echo lang('data_hora_fim');?></th>
                                    <th><?php echo lang('meta_porcentagem');?></th>
                                    <th><?php echo lang('actions');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($tutorias as $values):?>
                                <tr>
                                    <td><?php echo $values->orc_id; ?></td>
                                    <td><?php echo htmlspecialchars($values->nome_cliente, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->nome_tutor, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td data-order="<?php echo strtotime($values->data_hora_inicio);?>"><?php echo date('d/m/Y H:i', strtotime($values->data_hora_inicio)); ?></td>
                                    <td data-order="<?php echo strtotime($values->data_hora_fim);?>"><?php echo date('d/m/Y H:i', strtotime($values->data_hora_fim)); ?></td>
                                    <td><?php echo $values->meta_porcentagem; ?>%</td>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><?php echo anchor("ead_tutoria/edit/".$values->id, '<i class="fa fa-edit"></i>'.lang('actions_edit'), array()); ?></li>
                                                <li><?php echo anchor("ead_tutoria/view/".$values->id, '<i class="fa fa-eye"></i>'.lang('actions_see'), array()); ?></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
<?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    window.onload = function() {
        $('#datatable_tutoria_ex').DataTable({
          'paging'      : true,
          'lengthChange': false,
          'searching'   : true,
          'ordering'    : true,
          'info'        : true,
          'autoWidth'   : false,
          "oLanguage": {
            "sProcessing":   "Processando...",
            "sLengthMenu":   "Mostrar _MENU_ registros",
            "sZeroRecords":  "Não foram encontrados resultados",
            "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
            "sInfoFiltered": "",
            "sInfoPostFix":  "",
            "sSearch":       "Buscar:",
            "sUrl":          "",
            "oPaginate": {
                "sFirst":    "Primeiro",
                "sPrevious": "Anterior",
                "sNext":     "Seguinte",
                "sLast":     "Último"
            }
          },
          columnDefs: [
             { orderable: false, targets: -1 }
          ],
          "order": [[ 4, 'desc' ]]
        });
    };
</script>

