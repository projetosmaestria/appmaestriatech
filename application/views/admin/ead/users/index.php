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
                        <table id="datatable" class="table">
                            <thead>
                                <tr>
                                    <th><?php echo lang('users_name');?></th>
                                    <th><?php echo lang('users_email');?></th>
                                    <th><?php echo lang('users_groups');?></th>
                                    <th><?php echo lang('users_status');?></th>
                                    <th><?php echo lang('users_expiration_date');?></th>
                                    <th><?php echo lang('users_segments');?></th>
                                    <th><?php echo lang('users_action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($users as $user):?>
                                <?php
                                    $tr_class = '';
                                    if($user->ead == 1) {
                                        $d = str_replace('/', '-', $user->data_expiracao);
                                        if(date('Y-m-d',strtotime($d)) < date('Y-m-d', strtotime("+30 days")))
                                            $tr_class = 'class="warning"';
                                        else
                                            $tr_class = 'class="success"';
                                    }
                                ?>
                                <tr <?php echo $tr_class ?>>
                                    <input type="hidden" id="level_<?php echo $user->id ?>" value="<?php echo $user->level ?>">
                                    <input type="hidden" id="data_expiracao_<?php echo $user->id ?>" value="<?php echo $user->data_expiracao ?>">
                                    <input type="hidden" id="data_proximo_vpa_<?php echo $user->id ?>" value="<?php echo @$user->data_proximo_vpa ?>">
                                    <input type="hidden" id="seguimentos_<?php echo $user->id ?>" value="<?php echo htmlentities(json_encode($user->seguimentos)) ?>">
                                    <td><?php echo htmlspecialchars($user->first_name." ".$user->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
<?php foreach ($user->groups as $group):?>
                                        <?php echo anchor('groups/edit/'.$group->id, '<span class="label" style="background:'.$group->bgcolor.';">'.htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8').'</span>'); ?>
<?php endforeach?>
                                    </td>
                                    <td><?php echo ($user->active) ? '<span class="label label-success">'.lang('users_active').'</span>' : '<span class="label label-default">'.lang('users_inactive').'</span>'; ?></td>
                                    <td><?php echo $user->data_expiracao ?></td>
                                    <td><?php 
                                        foreach ($user->seguimentos as $seg) {
                                            echo '<span class="label" style="background:#2196F3;">'.$seg.'</span>';
                                        }
                                    ?></td>
                                    <td>                                                    
                                        <?php echo anchor('users/profile/'.$user->id, '<i class="fa fa-eye"></i>', array('class'=>'btn btn-info btn-lista', 'title'=>lang('actions_see'))); ?>
                                        &nbsp;
                                        <?php 
                                            if($user->ead == 1) {
                                                $icon = "<i class='fa fa-refresh'></i>";
                                                $class = "btn btn-success btn-lista btn_ead_user_update";
                                                $title = lang('actions_update_user');
                                            } else {
                                                $icon = "<i class='fa fa-external-link-square'></i>";
                                                $class = "btn btn-primary btn-lista btn_ead_user_create";
                                                $title = lang('actions_create_user');
                                            }
                                            $data_btn = array(
                                                'name'          => 'button',
                                                'title'         => $title,
                                                'id'         => $user->id,
                                                'class'         => $class,
                                                'type'          => 'button',
                                                'content'       => $icon
                                            );
                                        ?>
                                        <?php echo form_button($data_btn); ?>
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

<!-- Modal -->
<div id="eadUserModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Integrar Usuário ao EAD</h4>
      </div>
      <div class="modal-body">
        <?php echo form_open(base_url(), array('class' => 'form-horizontal', 'id' => 'form-ead_user')); ?>
            <input type='hidden' id='user_id' name='user_id' value=''>
            <input type='hidden' id='base_url' name='base_url' value='<?php echo base_url();?>'>
            <div class="form-group">
                <?php echo lang('level', 'level', array('class' => 'col-sm-4 control-label'), true); ?>
                <div class="col-sm-6 input-group date">
                    <?php echo form_dropdown(array('id'=>'level','name'=>'level','options'=>array(""=>"Selecione","1"=>"1 A","2"=>"1 B","3"=>"2 A","4"=>"2 B","5"=>"3 A","6"=>"3 B","7"=>"4"),'value'=>'','class'=>'form-control'));?>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('data_expiracao', 'data_expiracao', array('class' => 'col-sm-4 control-label'), true); ?>
                <div class="col-sm-6 input-group date" id='datetimepicker'>
                    <?php echo form_input(array('id'=>'data_expiracao','name'=>'data_expiracao','value'=>'','class'=>'form-control'));?>
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('data_proximo_vpa', 'data_proximo_vpa', array('class' => 'col-sm-4 control-label'), false); ?>
                <div class="col-sm-6 input-group date" id='datetimepicker2'>
                    <?php echo form_input(array('id'=>'data_proximo_vpa','name'=>'data_proximo_vpa','value'=>'','class'=>'form-control'));?>
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('creditos', 'creditos', array('class' => 'col-sm-4 control-label'), false); ?>
                <div class="col-sm-2">
                    <?php echo form_input(array('id'=>'creditos','name'=>'creditos','value'=>'','type'=>'number','class'=>'form-control'));?>
                </div>
            </div>
            <div class="form-group div-nova_metodologia">
                <?php echo lang('nova_metodologia', 'nova_metodologia', array('class' => 'col-sm-4 control-label'), false); ?>
                <div class="col-sm-2">
                    <?php echo form_checkbox(array('id'=>'nova_metodologia','name'=>'nova_metodologia','value'=>'1','class'=>'checkbox'));?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 text-right">
                    <div class="btn-group">
                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit_user'))); ?>
                    </div>
                </div>
            </div>
        <?php echo form_close();?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>
