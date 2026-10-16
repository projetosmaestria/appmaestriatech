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
                        <h3 class="box-title"><?php echo anchor('ead_tutoria/observacao_create/'.$user_id, '<i class="fa fa-plus"></i> '. lang('historico_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable_order_id" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('data_hora');?></th>
                                    <th><?php echo lang('situacao');?></th>
                                    <th><?php echo lang('mensagem');?></th>
                                    <th><?php echo lang('usuario');?></th>
                                    <th><?php echo lang('tutor');?></th>
                                    <th><?php echo lang('email_enviado');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($historico as $values):?>
                                <tr>
                                    <td data-order="<?php echo strtotime($values->DATA_REGISTRO." ".$values->HORA_REGISTRO);?>"><?php echo date("d/m/Y" , strtotime($values->DATA_REGISTRO))." ".$values->HORA_REGISTRO; ?></td>
                                    <td><?php echo htmlspecialchars($values->STATUS, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->MENSAGEM, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->nome_usu." ".$values->sobrenome_usu, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->nome_cli." ".$values->sobrenome_cli, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($values->email_enviado == "S") ? "Sim" : "Não"; ?></td>
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