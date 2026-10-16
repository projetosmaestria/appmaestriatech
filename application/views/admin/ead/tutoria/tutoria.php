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
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-info"></i> Informação!</h4>
            Olá tutor! Lembre-se que o ambiente que está acessando é do aluno e não deve-se executar atividades. Utilize somente para efetuar a consulta dos dados e evolução do seu aluno.
        </div>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-header">
                        <?php echo form_open(base_url('ead_tutoria/tutoria'), array('class' => '', 'id' => 'form-ead_tutoria')); ?>
                            <div class="form-group">
                                <?php echo lang('seguimento', 'filtro_seguimento', array('class' => '')); ?>
                                <?php echo form_dropdown($filtro_seguimento);?>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-flat btn-space">Filtrar</button>
                                <button type="button" class="btn btn-success btn-flat btn-tutoria-msg" data-toggle="modal" data-target="#eadTutorMsgModal">Mensagem</button>
                            </div>
                        <?php echo form_close(); ?>
                    </div>
                    <div class="box-body">                        
                        <table id="datatable_select" class="table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th><?php echo lang('contrato');?></th>
                                    <th><?php echo lang('users_name');?></th>
                                    <th><?php echo lang('tutor_ultimo_acesso');?></th>
                                    <th><?php echo lang('tutor_data_matricula');?></th>
                                    <th><?php echo lang('tutor_ultimo_contato');?></th>
                                    <th><?php echo lang('tutor_status');?></th>
                                    <th><?php echo lang('users_action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($users as $user):?>
                                <tr id="<?php echo $user["user_id"] ?>">
                                    <td></td>
                                    <td><?php echo $user["orc_id"]; ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name']." ".$user['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($user['ultimo_acesso'] != "") ? date("d/m/Y", strtotime($user['ultimo_acesso'])) : "-"; ?></td>
                                    <td><?php echo $user['data_matricula'] ?></td>
                                    <td><?php echo $user['ultimo_contato'] ?></td>
                                    <td><?php echo $user['status_desc'] ?></td>
                                    <td>     
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo base_url("users/historico/".$user['user_id']) ?>" title="Histórico Cliente"><i class="fa fa-history"></i>Histórico Cliente</a></li>
                                                <li><a href="<?php echo base_url("ead_tutoria/licoes_aluno/".$user['user_id']) ?>" title="Lições do Aluno"><i class="fa fa-pencil"></i>Lições do Aluno</a></li>
                                                <?php 
                                                    //if($user['url_login'] != "") {
                                                        echo '<li>'.anchor(base_url().'ead_tutoria/acessar_ead_aluno/'.$user['user_id'], '<i class="fa fa-key"></i>'.lang('actions_login'), array('title'=>lang('actions_login'), 'target'=>'_blank')).'</li>';
                                                        echo '<li>'.anchor(base_url().'ead_tutoria/registrar_tutoria/orcamento/'.$user['orc_id'], '<i class="fa fa-tasks"></i>'.lang('registrar_tutoria'), array('title'=>lang('registrar_tutoria'))).'</li>';
                                                    //} else {
                                                      //  echo '<li>'.anchor($user['url_login'], '<i class="fa fa-key"></i>'.lang('actions_login_expired'), array('title'=>lang('actions_login_expired'), 'disabled'=>'disabled')).'</li>'; 
                                                    //}
                                                ?>
                                                <li><a href="<?php echo base_url("users/profile/".$user['user_id']) ?>" title="Ver Profile Cliente"><i class="fa fa-user"></i>Ver Profile Cliente</a></li>
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

<div id="eadTutorMsgModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Contate seu(s) aluno(s)</h4>
      </div>
      <div class="modal-body">
        <?php echo form_open(base_url('ead_tutoria/enviar_msg'), array('class' => '', 'id' => 'form-tutoria_msg')); ?>
            <input type="hidden" id="users_to_hidden" name="users_to_hidden" value="">
            <div class="box-body">
                <div class="form-group">
                  <input type="text" class="form-control" name="assunto" placeholder="Assunto">
                </div>
                <div>
                  <textarea class="textarea" placeholder="Mensagem" name="mensagem"
                            style="width: 100%; height: 125px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                </div>
            </div>
            <div class="box-footer clearfix">
              <button type="submit" class="pull-right btn btn-primary btn-flat">Enviar</button>
            </div>
        <?php echo form_close(); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
    window.onload = function() {
        $('.multipleSelect').fastselect();
    };
</script>