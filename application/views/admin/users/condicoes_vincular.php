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
                        <h3 class="box-title"><?php echo lang('users_condicoes_vincular'); ?> Cliente #<?php echo $user_id ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-condicoes_vincular')); ?>
                            <input type="hidden" name="base_url" id="base_url" value="<?php echo base_url() ?>">
                            <div class="form-group">
                                <?php echo lang('unidades', 'unidades', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <?php echo form_dropdown($unidades);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('condicoes', 'condicoes', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-10">
                                    <div class="msg-condicoes"><p class="control-label" style="text-align: left !important;">Selecione uma unidade acima.</p></div>
                                    <div class="list-condicoes">
                                        <?php
                                            if($condicoes->num_rows() > 0) {
                                                $last_unid = 0;
                                                foreach ($condicoes->result() as $key => $value) {
                                                    if($last_unid != $value->unidade_id || $last_unid == 0) {
                                                        if($last_unid != 0)
                                                            echo '</ul>';
                                                        echo '<ul style="display:none;" class="list-group list-condicao-unid-'.$value->unidade_id.'">';
                                                    }
                                                    echo '
                                                        <li class="list-group-item">
                                                            <div class="checkbox">
                                                                <label><input type="checkbox" name="condicoes[]" value="'.$value->ID.'"';
                                                    if(in_array($value->ID, $users_cond)) {
                                                        echo ' checked';
                                                    }
                                                    echo '>'.$value->DESCRICAO.'</label>
                                                            </div>
                                                        </li>';

                                                    $last_unid = $value->unidade_id;
                                                }
                                            } else {
                                                echo '<p class="control-label" style="text-align: left !important;">Não há condições disponíveis para essa unidade.</p>';
                                            }
                                        ?>
                                    </div>
                                    <div class="loading" style="display:none;">
                                      <i class="fa fa-refresh fa-spin" style="font-size: 30px;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo anchor('users/condicoes/'.$user_id, lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            </div>
                        <?php echo form_close();?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>