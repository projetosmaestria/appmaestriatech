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
                                    <th><?php echo lang('id');?></th>
                                    <th><?php echo lang('filial');?></th>
                                    <th><?php echo lang('descricao');?></th>
                                    <th><?php echo lang('entrada');?></th>
                                    <th><?php echo lang('forma_pag');?></th>
                                    <th><?php echo lang('forma_receb');?></th>
                                    <th><?php echo lang('tipo_receb');?></th>
                                    <th><?php echo lang('desconto');?></th>
                                    <th><?php echo lang('data_cadastro');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($condicoes->result() as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->ID, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->unidade, ENT_QUOTES, 'UTF-8'); ?></td>  
                                    <td><?php echo htmlspecialchars($values->DESCRICAO, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo (strtoupper($values->entrada_matricula) == "TRUE")?'Sim':'Não'; ?></td>
                                    <td><?php echo htmlspecialchars($values->forma_pag, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->FormaReceb, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->tipo_receb, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->VALOR_DESCONTO_PONTUALIDADE, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td data-order="<?php echo strtotime($values->DataCad);?>"><?php echo date("d/m/Y" , strtotime($values->DataCad)); ?></td>
                                    <td><?php echo ($values->ATIVO == '1') ? anchor('condicoes_orcamento/deactivate/'.$values->ID, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('condicoes_orcamento/activate/'. $values->ID, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo base_url("condicoes_orcamento/view/".$values->ID) ?>"><i class="fa fa-search" aria-hidden="true"></i>Ver</a></li>
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