<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<section class="col-lg-12 connectedSortable">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                  <i class="fa fa-headphones"></i>
                  <h3 class="box-title">Chamados Responsáveis</h3>

                  <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                    </button>
                  </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="datatable_order_chamados" class="table">
                        <thead>
                            <tr>
                                <th><?php echo lang('id');?></th>
                                <th><?php echo lang('criado_em');?></th>
                                <th><?php echo lang('titulo');?></th>
                                <th><?php echo lang('servico');?></th>
                                <th><?php echo lang('cliente');?></th>
                                <th><?php echo lang('status');?></th>
                                <th><?php echo lang('prioridade');?></th>
                                <th><?php echo lang('previsao');?></th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($chamados->result() as $chamado):?>
                            <?php 
                                if($chamado->status_desc == "Aberto") {
                                    if($chamado->data_prazo && $chamado->data_prazo < date('Y-m-d H:i:s'))
                                        $class_tr_baixo = "border-baixo-vencido";
                                    else
                                        $class_tr_baixo = "border-baixo-aberto";
                                } elseif($chamado->status_desc == "Retorno") {
                                    $class_tr_baixo = "border-baixo-retorno";
                                } elseif($chamado->status_desc == "Aguardando Testes Sanclá" || $chamado->status_desc == "aguardando testes sanclá") {
                                    $class_tr_baixo = "border-baixo-aguardando-testes-sancla";
                                } else {
                                    $class_tr_baixo = "border-baixo"; 
                                }
                            ?>
                            <tr class="<?php echo $class_tr_baixo; ?> clickable-row" data-href="<?php echo base_url('chamados/details/'.$chamado->id) ?>">
                                <td><?php echo $chamado->id; ?></td>
                                <td><?php echo date("d/m/Y H:i:s", strtotime($chamado->created)); ?></td>
                                <td><?php echo htmlspecialchars($chamado->titulo, ENT_QUOTES, 'UTF-8'); ?></td>                                   
                                <td><?php echo htmlspecialchars($chamado->catalogo_desc, ENT_QUOTES, 'UTF-8'); ?></td>                                   
                                <td><?php echo htmlspecialchars($chamado->unidade, ENT_QUOTES, 'UTF-8'); ?></td>                                   
                                <td><?php echo htmlspecialchars($chamado->status_desc, ENT_QUOTES, 'UTF-8'); ?></td>                                   
                                <td><?php echo htmlspecialchars($chamado->prioridade_desc, ENT_QUOTES, 'UTF-8'); ?></td>                               
                                <td><?php echo ($chamado->data_prazo)?date("d/m/Y H:i", strtotime($chamado->data_prazo)):'à definir'; ?></td>                               
                            </tr>
<?php endforeach;?>
                        </tbody>
                    </table>
                </div>
             </div>
        </div>
    </div>
</section>