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
                        <h3 class="box-title"><?php echo lang('cupom_view_resend'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-view_resend_cupom')); ?>
                        <div class="form-group">
                            <?php echo lang('cupom', 'cupom', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo form_input($cupom_text);?>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php echo lang('celular', 'celular', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-2">
                                <?php echo form_input($celular);?>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php echo lang('email', 'email', array('class' => 'col-sm-2 control-label'), false); ?>
                            <div class="col-sm-3">
                                <?php echo form_input($email);?>
                            </div>
                        </div>
                        <?php if($cupom->orc_id == "") { ?>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php if($cupom->email != "") { 
                                            echo form_button(array('type' => 'submit', 'class' => 'btn btn-info btn-flat', 'value' => 'email', 'name' => 'acao', 'content' => '<i class="fa fa-envelope-o" aria-hidden="true"></i> E-mail')); 
                                        } if($cupom->celular != "") { 
                                            echo form_button(array('type' => 'submit', 'class' => 'btn btn-default btn-flat', 'value' => 'sms', 'name' => 'acao', 'content' => '<i class="fa fa-mobile" aria-hidden="true"></i> SMS')); 
                                            if($cupom->unidade_id == 519)
                                                $site = "https://matriculaead.bslidiomas.com.br";
                                            else
                                                $site = "https://matricula.bslidiomas.com.br";
                                            echo anchor('https://web.whatsapp.com/send?phone='.$cupom->celular_formatado.'&text=Utilize%20o%20cupom%20'.$cupom->cupom.'%20para%20comprar%20seu%20curso%20em%20'.$site.'?cupom='.$cupom->cupom, '<i class="fa fa-whatsapp" aria-hidden="true"></i> Whatsapp', array('class' => 'btn btn-success btn-flat', 'target' => '_blank'));
                                        } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <?php echo form_close();?>
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="col-md-2"><?php echo lang('usuario');?></th>
                                    <th class="col-md-2"><?php echo lang('controlador');?></th>
                                    <th class="col-md-2"><?php echo lang('metodo');?></th>
                                    <th class="col-md-2"><?php echo lang('data');?></th>
                                    <th class="col-md-4"><?php echo lang('obs');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($auditorias->result() as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->first_name." ".$values->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->controlador, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->metodo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td data-order="<?php echo strtotime($values->created_at);?>"><?php echo htmlspecialchars(date("d/m/Y H:i:s", strtotime($values->created_at)), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->obs, ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
<?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
             </div>
        </div>

        <?php if($cupom->orc_id != "") { ?>
            <div class="row">
                <div class="col-md-12">
                     <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Cupom já utilizado</h3>
                        </div>
                        <div class="box-body">
                            <dl>
                                <dt>Orçamento:</dt>
                                <dd><?php echo $cupom->orc_id ?></dd>
                                <br>
                                <dt>Condição(ões):</dt>
                                <?php 
                                    if($condicoes->num_rows() > 0) {
                                        foreach ($condicoes->result() as $cond) {
                                            echo '<dd>'.$cond->DESCRICAO.'</dd>';
                                        }
                                    }
                                ?>
                            </dl>

                        </div>
                    </div>
                 </div>
            </div>
        <?php } ?>
    </section>
</div>