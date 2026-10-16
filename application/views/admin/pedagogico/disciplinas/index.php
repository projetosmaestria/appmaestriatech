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
                                    <h3 class="box-title"><?php echo anchor('disciplinas/create', '<i class="fa fa-plus"></i> '. lang('create_disciplinas'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                                </div>
                                <div class="box-body">
                                    <table id="datatable" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th><?php echo lang('descricao');?></th>
                                                <th><?php echo lang('sigla');?></th>
                                                <!-- <th><?php echo lang('curso');?></th>
                                                <th><?php echo lang('pacote');?></th>
                                                <th><?php echo lang('subpacote');?></th> -->
                                                <th><?php echo lang('situacao');?></th>
                                                <th><?php echo lang('data_cadastro');?></th>
                                                <th><?php echo lang('action');?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php foreach ($disciplinas as $disciplina):?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($disciplina->DESCRICAO, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($disciplina->SIGLA, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <!-- <td><?php echo htmlspecialchars($disciplina->curso_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($disciplina->pacote_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($disciplina->pacotesub_desc, ENT_QUOTES, 'UTF-8'); ?></td> -->
                                                <td data-order="<?php echo strtotime($disciplina->DATA_CADASTRO);?>"><?php echo date("d/m/Y", strtotime($disciplina->DATA_CADASTRO)); ?></td>
                                                <td><?php echo ($disciplina->SITUACAO == 'True') ? anchor('disciplinas/deactivate/'.$disciplina->ID_DISCIPLINA, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('disciplinas/activate/'. $disciplina->ID_DISCIPLINA, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                                <td>
                                                    <?php echo anchor('disciplinas/edit/'.$disciplina->ID_DISCIPLINA, '<i class="fa fa-edit"></i>', array('class'=>'btn btn-warning btn-lista', 'title'=>lang('actions_edit'))); ?>
                                                    <?php echo anchor('disciplinas/vincular_curso/'.$disciplina->ID_DISCIPLINA, '<i class="fa fa-object-group"></i>', array('class'=>'btn btn-info btn-lista', 'title'=>lang('actions_curso'))); ?>
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
