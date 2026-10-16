<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if($this->session->flashdata('message') != "") {echo $this->session->flashdata('message');} ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-body">
                        <table id="datatable_server_side" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('codigo');?></th>
                                    <th><?php echo lang('livro');?></th>
                                    <th><?php echo lang('data');?></th>
                                    <th><?php echo lang('usuario');?></th>
                                    <th><?php echo lang('email');?></th>
                                    <th><?php echo lang('acao');?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>

<!-- Modal -->
<div id="resetQrcodeModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Reset QRCode</h4>
      </div>
      <div class="modal-body">
        <?php echo form_open(base_url('app_qrcodes/reset_qrcode'), array('class' => 'form-horizontal', 'id' => 'form-app_qrcode_reset')); ?>
            <input type='hidden' id='id' name='id' value=''>
            <div class="form-group">
                <?php echo lang('status_historico', 'status_historico', array('class' => 'col-sm-4 control-label'), true); ?>
                <div class="col-sm-6 input-group date">
                    <?php echo form_dropdown($status_historico);?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 text-right">
                    <div class="btn-group">
                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                    </div>
                </div>
            </div>
        <?php echo form_close();?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
     window.onload = function() {

        $('#datatable_server_side').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {"url":"<?php echo base_url('app_qrcodes/server_side') ?>", "dataType": "json", "type":"post"},
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
                {render: function(d){
                    return moment(d).format("DD/MM/YYYY HH:mm");
                }},
                null,
                null,
                { orderable: false}
            ]
      });
    };
</script>