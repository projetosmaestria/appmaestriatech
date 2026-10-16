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
                        <h3 class="box-title"><?php echo anchor('app_exercicios/create', '<i class="fa fa-plus"></i> '. lang('create_exercicios'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                          <div class="col-sm-12">
                            <div class="box box-default collapsed-box">
                              <div class="box-header with-border" style="cursor:default !important;">
                                <h3 class="box-title">Filtros Adicionais</h3>

                                <div class="box-tools pull-right">
                                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                  </button>
                                </div>
                                <!-- /.box-tools -->
                              </div>
                              <?php echo form_open('', array('class' => 'form-horizontal', 'id' => 'form-exercicios')); ?>
                                <div class="box-body">
                                    <div class="form-group">
                                        <?php echo lang('livro', 'livro', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-10">
                                            <?php echo form_dropdown($livro);?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <?php echo lang('tipo', 'tipo', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-10">
                                            <?php echo form_dropdown($tipo);?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <?php echo lang('unidade', 'unidade', array('class' => 'col-sm-2 control-label')); ?>
                                        <div class="col-sm-10">
                                            <?php echo form_dropdown($unidade);?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                          <div class="text-right col-sm-12">
                                            <button type="submit" name="btn_filtrar" class="btn btn-flat btn_form_contato"><i class="fa fa-search"></i> Filtrar</button>
                                          </div>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                              <?php form_close(); ?>
                            </div>
                          </div>
                        </div>

                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo lang('ordem');?></th>
                                    <th><?php echo lang('exercicio');?></th>
                                    <th><?php echo lang('unidade');?></th>
                                    <th><?php echo lang('livro');?></th>
                                    <th><?php echo lang('data_cadastro');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($exercicios as $exerc):?>
    <?php $tipo = ($exerc->tipo == "C") ? "Complementar" : "Obrigatória"; ?>
                                <tr>
                                    <td><?php echo $exerc->ordem_exercicio; ?></td>
                                    <td><?php echo htmlspecialchars($exerc->titulo_exercicio, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($exerc->ordem_unidade.'-'.$exerc->titulo_unidade . ' ('.$tipo.')', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($exerc->titulo_livro, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td data-order="<?php echo strtotime($exerc->data_cadastro);?>"><?php echo date("d/m/Y H:i", strtotime($exerc->data_cadastro)); ?></td>
                                    <td><?php echo ($exerc->ativo == 'S') ? anchor('app_exercicios/deactivate/'.$exerc->id, '<span class="label label-success">'.lang('actions_ativo').'</span>') : anchor('app_exercicios/activate/'. $exerc->id, '<span class="label label-default">'.lang('actions_inativo').'</span>'); ?></td>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><?php echo anchor("app_exercicios/edit/".$exerc->id, '<i class="fa fa-edit"></i>'.lang('actions_edit'), array()); ?></li>
                                                <li><?php echo anchor("app_exercicios/perguntas/".$exerc->id, '<i class="fa fa-tasks"></i>'.lang('actions_tasks'), array()); ?></li>
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
        $( "#livro" ).change(function() {
            $.ajax({
                url: '<?php echo base_url('app_exercicios/get_unidades_livro') ?>',
                type: 'POST',
                data: {livro:$(this).val()},
                success: function(data){
                    $('#tipo').html('<option value="">Selecione</option><option value="O">Obrigatório</option><option value="C">Complementar</option>');
                    $('#unidade').html(data);
                }
            });
        });

        $( "#tipo" ).change(function() {
            $.ajax({
                url: '<?php echo base_url('app_exercicios/get_tipo_unidade') ?>',
                type: 'POST',
                data: {livro:$("#livro").val(), tipo:$(this).val()},
                success: function(data){
                    $('#unidade').html(data);
                }
            });
        });
    };
</script>