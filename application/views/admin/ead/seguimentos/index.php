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
                        <h3 class="box-title"><?php echo anchor('ead_seguimentos/create', '<i class="fa fa-plus"></i> '. lang('seguimentos_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="datatable" class="table">
                            <thead>
                                <tr>
                                    <th class="col-md-7"><?php echo lang('seguimentos_nome');?></th>
                                    <th><?php echo lang('seguimentos_tutor');?></th>
                                    <th><?php echo lang('seguimentos_status');?></th>
                                    <th><?php echo lang('seguimentos_action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($seguimentos as $seg):?>
                                <tr>
                                    <input type="hidden" name="tutor_<?php echo $seg->id ?>" id="tutor_<?php echo $seg->id ?>" value="<?php echo $seg->tutor_id ?>">
                                    <td><?php echo htmlspecialchars($seg->segment_label, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php if($seg->first_name.$seg->last_name != "") {
                                            echo htmlspecialchars($seg->first_name." ".$seg->last_name, ENT_QUOTES, 'UTF-8'); 
                                        } else {
                                            echo '-';
                                        } ?>                                        
                                    </td>
                                    <td><?php echo ($seg->active == 1) ? anchor('ead_seguimentos/deactivate/'.$seg->id, '<span class="label label-success">'.lang('seguimentos_active').'</span>') : anchor('ead_seguimentos/activate/'. $seg->id, '<span class="label label-default">'.lang('seguimentos_inactive').'</span>'); ?></td>
                                    <td>
                                        <?php 
                                            $icon = '<i class="fa fa-user-secret"></i>';
                                            $class = "btn btn-warning btn-lista btn_ead_tutor_seg";
                                            $title = lang('actions_tutor');
                                        
                                            $data_btn = array(
                                                'name'          => 'button',
                                                'title'         => $title,
                                                'value'         => $seg->id,
                                                'class'         => $class,
                                                'type'          => 'button',
                                                'content'       => $icon
                                            );
                                        ?>
                                        <?php echo form_button($data_btn); ?>
                                        &nbsp;                                              
                                        <?php echo anchor('ead_seguimentos/users/'.$seg->id, '<i class="fa fa-users"></i>', array('class'=>'btn btn-info btn-lista', 'title'=>lang('actions_see_users'))); ?>
                                        &nbsp;
                                        <?php echo form_button(array('type' => 'button', 'class' => 'btn btn-danger btn-lista', 'content' => '<i class="fa fa-times"></i>', 'title'=>lang('actions_remove'), 'onclick'=>"remove_seguimento_confirm('".$seg->segment_label."')")); ?>
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
<div id="eadTutorModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Selecionar Tutor ao Seguimento</h4>
      </div>
      <div class="modal-body">
        <?php echo form_open(base_url(), array('class' => 'form-horizontal', 'id' => 'form-ead_tutor')); ?>
            <input type='hidden' id='base_url' name='base_url' value='<?php echo base_url();?>'>
            <input type='hidden' id='seg' name='seg' value=''>
            <div class="form-group">
                <?php echo lang('tutor', 'tutor', array('class' => 'col-sm-3 control-label'), true); ?>
                <div class="col-sm-8">
                    <?php //if(count($tutores) > 0): ?>
                        <select class="form-control" name="tutor" id="tutor">
                            <option value="">Selecione</option>
                            <?php foreach ($tutores as $tutor):
                                echo '<option value="'.$tutor->id.'"';
                                //if($tutor->id == 1)
                                    //echo ' selected';
                                echo '>'.$tutor->first_name.' '.$tutor->last_name.'</option>';
                            endforeach; ?>
                        </select>
                    <?php //endif ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-11 text-right">
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

<div id="confirm" class="modal fade" role="dialog">
    <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Tem certeza que deseja excluir o seguimento?</h4>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete">Sim</button>
        <button type="button" data-dismiss="modal" class="btn">Não</button>
      </div>
    </div>

  </div>
</div>