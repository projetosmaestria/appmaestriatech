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
                        <?php if($can_create) : ?>
                            <?php echo anchor('plano_acao/create', '<i class="fa fa-plus"></i> '. lang('plano_acao_create'), array('class' => 'btn btn-primary')); ?>
                        <?php endif ?>
                            
                        <!-- <a href="javascript:void(0);" id="btn-filtros" class="btn btn-default margin"><i class="fa fa-search"></i> Filtros</a> -->
                        
                        <?php if($can_exportar_pdf) : ?>  
                            <div class="btn-group margin">
                              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Outras Opções</button>
                              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                              </button>
                              <ul class="dropdown-menu" role="menu">
                                <li><a href="javascript:void(0);" id="exportar_xls">Exportar .pdf</a></li>
                              </ul>
                            </div>
                        <?php endif ?>
                    </div>
                    <div class="box-body">
                        <!--<div class="row">
                          <div class="col-sm-12">
                            <div class="box box-default collapsed-box">
                              <div class="box-header with-border" style="cursor:default !important;">
                                <h3 class="box-title">Filtros Adicionais</h3>

                                <div class="box-tools pull-right">
                                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                  </button>
                                </div>
                              </div>
                              <?php echo form_open('', array('class' => 'form-horizontal', 'id' => 'form-planos_acao_index')); ?>
                                <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
                                <div class="box-body">
                                  <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('status', 'status', array('class' => 'col-md-1 control-label')); ?>
                                        <div class="col-md-6">
                                            <?php echo form_dropdown($status);?>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                      <div class="col-sm-12 text-right">
                                        <button type="submit" name="btn_filtrar" class="btn btn-flat btn_form_contato"><i class="fa fa-search"></i> Filtrar</button>
                                      </div>
                                  </div>
                                </div>
                              <?php form_close(); ?>
                            </div>
                          </div>
                        </div>-->

                        <div class="row col-sm-12">
                            <table id="datatable_multiselect" class="table table-striped table-hover">
                                <input type="hidden" name="url_exportar_xls" id="url_exportar_xls" value="<?php echo base_url('plano_acao/exportar_pdf') ?> ">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th class="col-md-1"><?php echo lang('codigo');?></th>
                                        <th class="col-md-2"><?php echo lang('desc');?></th>
                                        <th class="col-md-1"><?php echo lang('status');?></th>
                                        <th class="col-md-1"><?php echo lang('inicio_estimado');?></th>
                                        <th class="col-md-1"><?php echo lang('termino_estimado');?></th>
                                        <th class="col-md-1"><?php echo lang('horas_prevista');?></th>
                                        <th class="col-md-1"><?php echo lang('inicio_real');?></th>
                                        <th class="col-md-1"><?php echo lang('termino_real');?></th>
                                        <th class="col-md-1"><?php echo lang('horas_utilizadas');?></th>
                                        <th class="col-md-1"><?php echo lang('acao');?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($planos as $values):?>
                                        <tr>
                                            <td></td>
                                            <td><?php echo $values->id; ?></td>
                                            <td><?php echo htmlspecialchars($values->desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($values->status, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($values->inicio_estimado)), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($values->termino_estimado)), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <?php
                                                if($values->horas_prevista != "" && $values->horas_prevista != null) {
                                                    //$horas_prevista = explode(":", $values->horas_prevista);
                                                    //echo '<td>'.$horas_prevista[0].":".$horas_prevista[1].'</td>';
                                                    echo '<td>'.$values->horas_prevista.'h</td>';
                                                } else
                                                    echo '<td>-</td>';
                                            ?>

                                            <td><?php echo ($values->inicio_real != "") ? date("d/m/Y H:i", strtotime($values->inicio_real)) : "-"; ?></td>
                                            <td><?php echo ($values->termino_real != "") ? date("d/m/Y H:i", strtotime($values->termino_real)) : "-"; ?></td>
                                            <td><?php echo $values->horas_utilizadas; ?></td>
                                            <td>
                                                <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                                    <ul class="dropdown-menu" role="menu">
                                                        <li><?php echo anchor("plano_acao/edit/".$values->id, '<i class="fa fa-edit"></i>'.lang('actions_edit'), array()); ?></li>
                                                        <li><?php echo anchor("plano_acao/view/".$values->id, '<i class="fa fa-eye"></i>'.lang('actions_see'), array()); ?></li>
                                                        <li><?php echo anchor("plano_acao/finalizar/".$values->id, '<i class="fa fa-check"></i>'.lang('actions_finalizar'), array()); ?></li>
                                                        <li><?php echo anchor("chamados/index?filtro_plano_acao=".$values->id, '<i class="fa fa-headphones"></i>'.lang('chamados_vinculados'), array()); ?></li>
                                                        <li><?php echo anchor("chamados/create/planoacao/".$values->id, '<i class="fa fa-headphones"></i>'.lang('novo_chamado'), array()); ?></li>
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
        </div>
    </section>
</div>