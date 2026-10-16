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
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('produto');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('demonstracao');?></th>
                                    <th><?php echo lang('vencimento');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($historico as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->nome, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($values->status == "S") ? "Habilitado" : "Desabilitado"; ?></td>
                                    <td><?php echo ($values->demonstracao == "S") ? "Sim" : "Não"; ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($values->periodo_fim)); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-info btn-lista" title="Visualizar Histórico" <?php echo ($values->has_historicos) ? "onclick='visualizarHistorico(\"".$values->id."\", \"".base_url()."\");'" : "" ?> <?php echo ($values->has_historicos) ? '' : 'disabled' ?>><i class="fa fa-search-plus" aria-hidden="true"></i></a>
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

<div class="modal fade" id="historico" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
        </div>
    </div>
</div>