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
                        <h3 class="box-title"><?php echo anchor('app_livros/create', '<i class="fa fa-plus"></i> '. lang('create_livros'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('titulo');?></th>
                                    <th><?php echo lang('disciplina');?></th>
                                    <th><?php echo lang('cor');?></th>
                                    <th><?php echo lang('data_cadastro');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($livros as $livro):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($livro->titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($livro->DESCRICAO, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><i class="fa fa-stop" style="color:<?php echo $livro->cor; ?>"></i></td>
                                    <td data-order="<?php echo strtotime($livro->data_cadastro);?>"><?php echo date("d/m/Y H:i", strtotime($livro->data_cadastro)); ?></td>
                                    <td><?php echo ($livro->ativo == 'S') ? anchor('app_livros/deactivate/'.$livro->id, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('app_livros/activate/'. $livro->id, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                    <td>
                                        <?php echo anchor('app_livros/edit/'.$livro->id, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?>
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