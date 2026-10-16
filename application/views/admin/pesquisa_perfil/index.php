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
                        <h3 class="box-title"><?php echo anchor('pesquisa_perfil/create', '<i class="fa fa-plus"></i> '. lang('pesquisa_perfil_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('orc');?></th>
                                    <th><?php echo lang('cliente');?></th>
                                    <th><?php echo lang('curso');?></th>
                                    <th><?php echo lang('criado_em');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($pesquisas as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->orc_id, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->cliente, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->curso, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td data-order="<?php echo strtotime($values->created_at);?>"><?php echo $values->data_formatada; ?></td>
                                    <td>
                                        <?php echo anchor("pesquisa_perfil/edit/".$values->pesquisa_id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?>
                                        <?php echo anchor("pesquisa_perfil/view/".$values->pesquisa_id, '<i class="fa fa-eye"></i>', array('class'=>'btn btn-info btn-lista', 'title'=>lang('actions_view'))); ?>
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