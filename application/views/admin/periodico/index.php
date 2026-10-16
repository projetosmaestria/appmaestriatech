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
                                    <th><?php echo lang('filial');?></th>
                                    <th><?php echo lang('periodico');?></th>
                                    <th><?php echo lang('tipo_periodicidade');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($periodico->result() as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->nomePeriodico, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->periodicidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($values->status == '1') ? '<span class="label label-success">'.lang('actions_ativo').'</span>' : '<span class="label label-default">'.lang('actions_inativo').'</span>'; ?></td>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo base_url("periodico/view/".$values->id) ?>"><i class="fa fa-search" aria-hidden="true"></i>Ver</a></li>
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