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
                <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-agenda_configurar')); ?>
                    <input type="hidden" name="base_url" id="base_url" value="<?php echo base_url() ?>">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo lang('configurar_compartilhados'); ?></h3>
                        </div>
                        <div class="box-body">
                            <table id="datatable_select" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="col-md-1"><?php echo lang('remover');?></th>
                                        <th class="col-md-1"><?php echo lang('id');?></th>
                                        <th class="col-md-2"><?php echo lang('nome');?></th>
                                        <th class="col-md-2"><?php echo lang('email');?></th>
                                        <th class="col-md-2"><?php echo lang('compartilhado_em');?></th>
                                        <th class="col-md-2"><?php echo lang('compartilhado_por_id');?></th>
                                        <th class="col-md-2"><?php echo lang('compartilhado_por_nome');?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        if($usuarios_compartilhados->num_rows() > 0) {
                                            foreach ($usuarios_compartilhados->result() as $user) {
                                                echo "<tr>";
                                                echo "<td></td>";
                                                echo "<td>".$user->id_usu."</td>";
                                                echo "<td>".$user->nome_usu."</td>";
                                                echo "<td>".$user->email_usu."</td>";
                                                echo "<td>".date("d/m/Y H:i:s", strtotime($user->created_at))."</td>";
                                                echo "<td>".$user->id_por."</td>";
                                                echo "<td>".$user->nome_por."</td>";
                                                echo "</tr>";
                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer">
                            <div class="col-md-12">
                                <div class="btn-group">
                                    <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_save'))); ?>
                                    <?php echo anchor('agenda', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    <?php 
                                        if($can_excluir_agenda) {
                                            echo form_button(array('type' => 'button', 'class' => 'btn btn-danger btn-flat', 'content' => lang('actions_delete_agenda'), 'title'=>lang('actions_delete_agenda'), 'onclick'=>"deletar_agenda('".$agenda_id."')"));
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php echo form_close();?>
             </div>
        </div>
    </section>
</div>

<?php if($can_excluir_agenda) { ?>
<div id="confirm" class="modal fade" role="dialog">
    <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Tem certeza que deseja excluir a agenda?</h4>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete">Sim</button>
        <button type="button" data-dismiss="modal" class="btn">Não</button>
      </div>
    </div>

  </div>
</div>
<?php } ?>