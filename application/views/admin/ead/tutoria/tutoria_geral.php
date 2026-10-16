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
                        <?php echo form_open(base_url('ead_tutoria/tutoria_geral'), array('class' => '', 'id' => 'form-ead_tutoria_geral')); ?>
                            <div class="form-group">
                                <?php echo lang('tutor', 'filtro_tutor', array('class' => '')); ?>
                                <?php echo form_dropdown($filtro_tutor);?>
                            </div>
                            <div class="form-group">
                                <?php echo lang('seguimento', 'filtro_seguimento', array('class' => '')); ?>
                                <?php echo form_dropdown($filtro_seguimento);?>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-flat">Filtrar</button>
                            </div>
                        <?php form_close(); ?>
                    </div>
                    <div class="box-body">
                        
                        <table id="datatable" class="table">
                            <thead>
                                <tr>
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
                                <tr>
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
                                                        //echo '<li>'.anchor($user['url_login'], '<i class="fa fa-key"></i>'.lang('actions_login_expired'), array('title'=>lang('actions_login_expired'), 'disabled'=>'disabled')).'</li>'; 
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
<script type="text/javascript">
    window.onload = function() {
        $('.multipleSelect').fastselect();
    };
</script>