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
                        <h3 class="box-title"><?php echo anchor('materiais/create', '<i class="fa fa-plus"></i> '. lang('materiais_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                    <table id="datatable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><?php echo lang('titulo');?></th>
                                <th><?php echo lang('grupos');?></th>
                                <th><?php echo lang('livro'); ?></th> 
                                <th><?php echo lang('unidade'); ?></th>
                                <th><?php echo lang('data_criacao');?></th>
                                <th><?php echo lang('status');?></th>
                                <th><?php echo lang('acao');?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materiais as $values): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values["titulo"], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <?php
                                        $groups = "";
                                        foreach ($values["grupos"] as $grupo) {
                                            $groups .= anchor('groups/edit/'.$grupo->id, '<span class="label" style="background:'.$grupo->bgcolor.';">'.htmlspecialchars($grupo->name, ENT_QUOTES, 'UTF-8').'</span>');
                                        }
                                    ?>
                                    <td><?php echo $groups; ?></td>
                                    <td><?php echo htmlspecialchars($values["titulo_livro"], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values["titulo_unidade"], ENT_QUOTES, 'UTF-8'); ?></td>
                                    
                                    <td><?php echo $values["data_criacao"]; ?></td>
                                    <td><?php echo ($values["actived"] == 'S') ? anchor('materiais/deactivate/'.$values["id"], '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('materiais/activate/'. $values["id"], '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                    <td><?php echo anchor("materiais/edit/".$values["id"], '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    </div>
                </div>
             </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    window.onload = function() {
        $( "#livro" ).change(function() {
            $.ajax({
                url: '<?php echo base_url('app_exercicios/get_unidades_livro') ?>',
                type: 'POST',
                data: {livro:$(this).val()},
                success: function(data){
                    $('#unidade').html(data);
                }
            });
        });
    };
</script>
