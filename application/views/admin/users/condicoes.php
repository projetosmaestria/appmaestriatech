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
                        <h3 class="box-title"><?php echo anchor('users/condicoes_vincular/'.$user_id, '<i class="fa fa-check-square-o"></i> '. lang('condicoes_vincular'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable_order_id" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('descricao');?></th>
                                    <th><?php echo lang('unidade');?></th>
                                    <th><?php echo lang('entrada_matricula');?></th>
                                    <th><?php echo lang('forma_pagamento');?></th>
                                    <th><?php echo lang('forma_pagamento_desc');?></th>
                                    <th><?php echo lang('tipo_receb_desc');?></th>
                                    <th><?php echo lang('data_cadastro');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($condicoes as $values):?>
                                <tr>
                                    <td><?php echo $values->DESCRICAO; ?></td>
                                    <td><?php echo $values->unidade; ?></td>
                                    <td><?php echo ($values->entrada_matricula == "True") ? "Sim" : "Não"; ?></td>
                                    <td><?php echo $values->FormaReceb ?></td>
                                    <td><?php echo $values->pag_desc ?></td>
                                    <td><?php echo $values->NOME; ?></td>
                                    <td data-order="<?php echo strtotime($values->DataCad);?>"><?php echo date("d/m/Y" , strtotime($values->DataCad)); ?></td>
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