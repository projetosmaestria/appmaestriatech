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
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo anchor('app_qrcodes/create', '<i class="fa fa-plus"></i> '. lang('app_qrcodes_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable-qrcode" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('empresa');?></th>
                                    <th><?php echo lang('livro');?></th>
                                    <th><?php echo lang('data');?></th>
                                    <th><?php echo lang('qtd');?></th>
                                    <th><?php echo lang('usuario');?></th>
                                    <th><?php echo lang('acao');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($app_qrcodes as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->empresa_nome, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td data-order="<?php echo strtotime($values->data_cadastro);?>"><?php echo $values->data; ?></td>
                                    <td><?php echo $values->qtd; ?></td>
                                    <td><?php echo htmlspecialchars($values->first_name." ".$values->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php echo anchor("app_qrcodes/gerar_excel/".$values->id, '<i class="fa fa-file-excel-o"></i>', array('class'=>'btn btn-success btn-lista', 'title'=>lang('actions_excel'))); ?>
                                        &nbsp;
                                        <?php echo form_button(array('type' => 'submit', 'content'=>'<i class="fa fa-file-pdf-o"></i>', 'class'=>'btn btn-danger btn-lista', 'title'=>lang('actions_pdf'), 'data-id'=>base64_encode($values->id), 'data-id-min'=>$values->min_id, 'data-id-max'=>$values->max_id, 'data-toggle'=>'modal', 'data-target'=>'#modalImprimirQRCodes', 'data-placement'=>'top', 'data-original-title'=>'Gerar PDF', 'onclick'=>'carregarDadosQrcode(this)')); ?>
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

    <!-- Modal -->
    <div class="modal fade" id="modalImprimirQRCodes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Gerar PDF</h4>                        
                </div><!--/modal-header-->
                <div class="modal-body">
                    <form method="post" action="<?php echo base_url('app_qrcodes/gerar_pdf') ?>" id="formQRCodesPrint" name="formQRCodesPrint" class="form-inline" role="">
                        <div id="infomacoes-qrcode" class="alert alert-info">
                        </div>
                        <input type="hidden" id="inputQRCodeinfoId" name="inputQRCodeinfoId">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="control-label" for="inputQRCodeIdIni">DE</label>
                                <div class="form-group">
                                    <input type="text" class="form-control input-number" id="inputQRCodeIdIni" name="inputQRCodeIdIni" placeholder="ID Inicial" disabled>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="control-label" for="inputQRCodeIdEnd">ATÉ</label>
                                <div class="form-group">
                                    <input type="text" class="form-control input-number" id="inputQRCodeIdEnd" name="inputQRCodeIdEnd" placeholder="ID Final" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="checkbox" class="checkbox" id="inputQRCodeIdTodos" name="inputQRCodeIdTodos" checked> 
                                <label for="inputQRCodeIdTodos" class="checkbox">TODOS</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="checkbox" class="checkbox" id="inputQRCodePorPagina" name="inputQRCodePorPagina"> 
                                <label for="inputQRCodePorPagina" class="checkbox">UM POR PÁGINA</label>
                            </div>
                        </div>
                        <br class="clear">
                        <div class="row">
                            <div class="col-md-2">
                                <button type="submit" id="imprimir_qrcodes" class="btn btn-primary"><i class="glyphicon glyphicon-print"></i> Gerar</button>
                            </div>                                                            
                        </div>
                    </form><!--/form-->
                </div><!--/.modal-body-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>
                </div><!--/.modal-footer-->
            </div><!--/.modal-content-->
        </div><!--/.modal-dialog-->
    </div><!--/.modal-->
</div>
<script type="text/javascript">
    window.onload = function() {
        $('#datatable-qrcode').DataTable({
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
            "columnDefs": [ 
              { "responsivePriority": 1, targets: 0 },
              { "responsivePriority": 2, targets: -1 }
            ],
            "order": [[ 2, 'desc' ]]
          });

    };
</script>