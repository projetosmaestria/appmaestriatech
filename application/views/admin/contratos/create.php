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
                        <h3 class="box-title"><?php echo lang('contratos_create'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_contrato')); ?>
                            <div class="form-group">
                                <?php echo lang('filial', 'filial', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($filial);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('cliente', 'cliente', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="input-group col-sm-3">
                                    <input type="hidden" id="cliente_id" name="cliente_id" value="">
                                    <?php echo form_input($cliente);?>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalUsers"><i class="fa fa-search" aria-hidden="true"></i></button>
                                    </span>
                                </div>
                            </div>                            
                            <div class="form-group">
                                <?php echo lang('responsavel', 'responsavel', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="input-group col-sm-3">
                                    <input type="hidden" id="responsavel_id" name="responsavel_id" value="">
                                    <?php echo form_input($responsavel);?>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalUsers2"><i class="fa fa-search" aria-hidden="true"></i></button>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('curso', 'curso', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_dropdown($curso);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('dados_contrato', 'dados_contrato', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-8">
                                    <ul class="nav nav-tabs">
                                      <li class="active"><a data-toggle="tab" href="#condicoes">Condições</a></li>
                                      <li><a data-toggle="tab" href="#turmas_disciplinas">Turmas / Disciplinas</a></li>
                                      <li><a data-toggle="tab" href="#produtos">Produtos</a></li>
                                      <li><a data-toggle="tab" href="#servicos">Serviços</a></li>
                                      <li><a data-toggle="tab" href="#parcelas">Parcelas</a></li>
                                    </ul>

                                    <div class="tab-content">
                                      <div id="condicoes" class="tab-pane fade in active">
                                        <h6>Selecione um curso</h6>
                                      </div>
                                      <div id="turmas_disciplinas" class="tab-pane fade">
                                        <h6>Selecione um curso</h6>
                                      </div>
                                      <div id="produtos" class="tab-pane fade">
                                        <h6>Selecione um curso</h6>
                                      </div>
                                      <div id="servicos" class="tab-pane fade">
                                        <h6>Selecione um curso</h6>
                                      </div>
                                      <div id="parcelas" class="tab-pane fade">
                                        <h6>Selecione um curso</h6>
                                      </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('contratos', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            </div>
                        <?php echo form_close();?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>

<!-- Modal -->
<div class="modal fade" id="modalUsers" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                 <h4 class="modal-title" id="myModalLabel">Usuários</h4>                        
             </div><!--/modal-header-->
             <div class="modal-body">
                <table id="clientesTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="col-md-1"></th>
                            <th><?php echo lang('id');?></th>
                            <th><?php echo lang('nome');?></th>
                            <th><?php echo lang('email');?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($users as $user):?>
                        <tr style="cursor:pointer;">
                            <td></td>
                            <td><?php echo $user->user_id; ?></td>
                            <td><?php echo htmlspecialchars($user->first_name." ".$user->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
<?php endforeach;?>
                    </tbody>
                </table>
             </div><!--/.modal-body-->
             <div class="modal-footer">
                 <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>
             </div><!--/.modal-footer-->
         </div><!--/.modal-content-->
     </div><!--/.modal-dialog-->
</div><!--/.modal-->

<!-- Modal -->
<div class="modal fade" id="modalUsers2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                 <h4 class="modal-title" id="myModalLabel">Usuários</h4>                        
             </div><!--/modal-header-->
             <div class="modal-body">
                <table id="responsavelTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="col-md-1"></th>
                            <th><?php echo lang('id');?></th>
                            <th><?php echo lang('nome');?></th>
                            <th><?php echo lang('email');?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($users as $user):?>
                        <tr style="cursor:pointer;">
                            <td></td>
                            <td><?php echo $user->user_id; ?></td>
                            <td><?php echo htmlspecialchars($user->first_name." ".$user->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
<?php endforeach;?>
                    </tbody>
                </table>
             </div><!--/.modal-body-->
             <div class="modal-footer">
                 <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>
             </div><!--/.modal-footer-->
         </div><!--/.modal-content-->
     </div><!--/.modal-dialog-->
</div><!--/.modal-->

<script type="text/javascript">
    window.onload = function() {
        var tableCli = $('#clientesTable').DataTable( {
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
                },
                "select": {
                    "rows": {
                        _: "%d itens selecionados",
                        0: "Selecione o(s) item(ns)",
                        1: "1 item selecionado"
                    }
                }
            },
            columnDefs: [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0
            } ],
            select: {
                style:    'os',
                selector: 'tr'
            },
            order: [[ 1, 'asc' ]]
        });

        $('#modalUsers tbody').on( 'click', 'tr', function () {
            var data = tableCli.row(this).data();
            $("#cliente_id").val(data[1]);
            $("#cliente").val(data[2]);
            $("#modalUsers").modal('hide');
        });

        var tableResp = $('#responsavelTable').DataTable( {
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
                },
                "select": {
                    "rows": {
                        _: "%d itens selecionados",
                        0: "Selecione o(s) item(ns)",
                        1: "1 item selecionado"
                    }
                }
            },
            columnDefs: [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0
            } ],
            select: {
                style:    'os',
                selector: 'tr'
            },
            order: [[ 1, 'asc' ]]
        });

        $('#modalUsers2 tbody').on( 'click', 'tr', function () {
            var data = tableResp.row(this).data();
            $("#responsavel_id").val(data[1]);
            $("#responsavel").val(data[2]);
            $("#modalUsers2").modal('hide');
        });
    };
</script>