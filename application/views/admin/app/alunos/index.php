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
                                <div class="box-header with-border">
                                    <h3 class="box-title"><?php echo anchor('users/create/1', '<i class="fa fa-plus"></i> '. lang('users_create_user'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                                </div>
                                <div class="box-body">
                                    <table id="datatable_server_side" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th><?php echo lang('users_id');?></th>
                                                <th><?php echo lang('users_firstname');?></th>
                                                <th><?php echo lang('users_lastname');?></th>
                                                <th><?php echo lang('users_email');?></th>
                                                <th><?php echo lang('users_unidade');?></th>
                                                <th><?php echo lang('users_status');?></th>
                                                <th><?php echo lang('users_action');?></th>
                                            </tr>
                                        </thead>
                                        
                                    </table>
                                </div>
                            </div>
                         </div>
                    </div>
                </section>
            </div>
<script type="text/javascript">
     window.onload = function() {
        $('#datatable_server_side').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {"url":"<?php echo base_url('app_alunos/server_side') ?>", "dataType": "json", "type":"post"},
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
            "columns": [
                null,
                null,
                null,
                null,
                { orderable: false},
                { orderable: false},
                { orderable: false}
            ]     
      });
    };
</script>