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
                                    <th><?php echo lang('tipo');?></th>
                                    <th><?php echo lang('assunto');?></th>
                                    <th><?php echo lang('data_atualizacao');?></th>
                                    <th><?php echo lang('actions');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($config_email as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->tipo_email, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->assunto, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td data-order="<?php echo $values->data_atualizacao;?>"><?php echo date('d/m/Y H:i', strtotime($values->data_atualizacao)); ?></td>
                                    <td>
                                        <?php echo anchor("config_email/edit/".$values->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?>
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