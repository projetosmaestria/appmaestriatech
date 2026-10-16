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
                        <h3 class="box-title"><?php echo anchor('midias_eventos/create', '<i class="fa fa-plus"></i> '. lang('midias_eventos_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('id');?></th>
                                    <th><?php echo lang('unidade');?></th>
                                    <th><?php echo lang('midia');?></th>
                                    <th><?php echo lang('fb_id');?></th>
                                    <th><?php echo lang('descricao');?></th>
                                    <th><?php echo lang('criado_em');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($eventos as $values):?>
                                <tr>
                                    <td><?php echo $values->id; ?></td>
                                    <td><?php echo htmlspecialchars($values->unidade_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->midia_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->fb_id, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->descricao, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td data-order="<?php echo strtotime($values->created_at);?>"><?php echo $values->data_formatada; ?></td>
                                    <td><?php echo ($values->status == '1') ? anchor('midias_eventos/deactivate/'.$values->id, '<span class="label label-success">'.lang('status_ativo').'</span>') : anchor('midias_eventos/activate/'. $values->id, '<span class="label label-default">'.lang('status_inativo').'</span>'); ?></td>
                                    <td>
                                        <?php echo anchor("midias_eventos/edit/".$values->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?>
                                        <?php echo anchor("midias_eventos/anuncios/".$values->id, '<i class="fa fa-bullhorn"></i>', array('class'=>'btn btn-info btn-lista', 'title'=>lang('anuncios'))); ?>
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