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
                        <?php if($can_create) ?>
                            <h3 class="box-title"><?php echo anchor('unidades/create', '<i class="fa fa-plus"></i> '. lang('unidades_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('fantasia');?></th>
                                    <th><?php echo lang('razao_social');?></th>
                                    <th><?php echo lang('unidades_administrador');?></th>
                                    <th><?php echo lang('unidades_telefone');?></th>
                                    <th><?php echo lang('unidades_cpf_cnpj');?></th>
                                    <th><?php echo lang('unidades_condicoes');?></th>
                                    <th><?php echo lang('unidades_action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($unidades as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->razao_social, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo isset($values->gestor)?htmlspecialchars($values->gestor, ENT_QUOTES, 'UTF-8'):''; ?></td>
                                    <td><?php echo htmlspecialchars($values->telefone1, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($values->cnpj != "") ? $values->cnpj : $values->cpf; ?></td>
                                    <td><?php echo ($values->actived == 's') ? anchor('unidades/deactivate/'.$values->id, '<span class="label label-success">'.lang('actions_ativar').'</span>') : anchor('unidades/activate/'. $values->id, '<span class="label label-default">'.lang('actions_desativar').'</span>'); ?></td>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <?php if($can_edit){ ?>
                                                    <li><?php echo anchor("unidades/edit/".$values->id, '<i class="fa fa-edit"></i>'.lang('actions_edit'), array()); ?></li>
                                                <?php } ?>
                                                <?php if($can_recursos_ws) { ?>
                                                    <li><?php echo anchor("unidades/recursos_ws/".$values->id, '<i class="fa fa-server"></i>'.lang('actions_recursos_ws'), array()); ?></li>
                                                <?php } ?>
                                                <?php if($can_facebook_api){ ?>
                                                    <li><?php echo anchor("unidades/facebook_api/".$values->id, '<i class="fa fa-facebook-square"></i>'.lang('actions_facebook_api'), array()); ?></li>
                                                <?php } ?>
                                                <?php if($can_rd_station) { ?>
                                                    <li><?php echo anchor("unidades/rd_station/".$values->id, '<i class="fa fa-registered"></i>'.lang('actions_rd_station'), array()); ?></li>
                                                <?php } ?>
                                                <?php if($can_parametros_contrato) { ?>
                                                    <li><?php echo anchor("unidades/parametros_contrato/".$values->id, '<i class="fa fa-cogs"></i>'.lang('actions_parametros_contrato'), array()); ?></li>
                                                <?php } ?>
                                                <?php if($can_iugu) { ?>
                                                    <li><?php echo anchor("unidades/iugu/".$values->id, '<i class="fa fa-credit-card"></i>'.lang('unidades_iugu'), array()); ?></li>
                                                <?php } ?>
                                                <li><a href="cursos/<?=$values->id?>"><i class="fa fa-book"></i></i>Cursos</a></li>
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