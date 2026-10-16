<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!-- Card Status Aguardando Teste - col-lg-6 -->
<section class="col-lg-6 connectedSortable">
    <div class="box box-primary">
        <div class="box-header with-border">
            <i class="fa fa-hourglass-end"></i>
            <h3 class="box-title">Status Aguardando Teste</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><?php echo lang('id'); ?></th>
                            <th><?php echo lang('cliente'); ?></th>
                            <th><?php echo lang('dono'); ?></th>
                            <th><?php echo lang('titulo'); ?></th>
                            <th><?php echo lang('criado_em'); ?></th>
                            <th><?php echo lang('previsao'); ?></th>
                            <th><?php echo lang('prioridade'); ?></th>
                            <th><?php echo lang('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aguardando_teste->result() as $values): ?>
                            <tr>
                                <td><?php echo $values->id ?></td>
                                <td><?php echo htmlspecialchars($values->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($values->dono, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($values->titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-order="<?php echo strtotime($values->created); ?>"><?php echo date("d/m/Y H:i", strtotime($values->created)); ?></td>
                                <td data-order="<?php echo strtotime($values->data_prazo); ?>"><?php echo date("d/m/Y H:i", strtotime($values->data_prazo)); ?></td>
                                <td><?php echo htmlspecialchars($values->prioridade_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo anchor("chamados/details/" . $values->id, '<i class="fa fa-eye"></i>', array('class' => 'btn btn-info', 'title' => lang('actions_view'))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Card Tarefas Pendentes -->
<section class="col-lg-6 connectedSortable">
    <div class="box box-primary">
        <div class="box-header with-border">
            <i class="fa fa-tasks"></i>
            <h3 class="box-title">
                Tarefas Pendentes
                <?php if (isset($total_tarefas_pendentes) && $total_tarefas_pendentes > 0): ?>
                    <span class="label label-danger"><?php echo $total_tarefas_pendentes; ?></span>
                <?php endif; ?>
            </h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <?php if (isset($tarefas_pendentes) && count($tarefas_pendentes) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Chamado</th>
                                <th>Etapa</th>
                                <th>Descrição</th>
                                <th>Prazo</th>
                                <th width="80">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tarefas_pendentes as $tarefa): ?>
                                <tr class="<?php echo (strtotime($tarefa->data_prazo) < strtotime(date('Y-m-d'))) ? 'danger' : ''; ?>">
                                    <td>
                                        <a href="<?php echo base_url('chamados/details/' . $tarefa->chamado_id); ?>">
                                            #<?php echo $tarefa->chamado_id; ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($tarefa->nome, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($tarefa->descricao, ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($tarefa->data_prazo && $tarefa->data_prazo != '0000-00-00') {
                                            echo date("d/m/Y", strtotime($tarefa->data_prazo));
                                            if (strtotime($tarefa->data_prazo) < strtotime(date('Y-m-d'))) {
                                                echo ' <span class="label label-danger">Atrasado</span>';
                                            } elseif (strtotime($tarefa->data_prazo) <= strtotime('+3 days')) {
                                                echo ' <span class="label label-warning">Próximo</span>';
                                            }
                                        } else {
                                            echo 'Sem prazo';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo base_url('chamados/details/' . $tarefa->chamado_id . '#checklist'); ?>"
                                            class="btn btn-default btn-xs"
                                            title="Ver tarefa">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center" style="margin-bottom: 0;">
                    <i class="fa fa-check-circle"></i> Nenhuma tarefa pendente no momento.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Informações de Chamados -->
<section class="col-lg-12 connectedSortable">
    <div class="box box-primary">
        <div class="box-header with-border">
            <i class="fa fa-headphones"></i>
            <h3 class="box-title">Informações de Chamados</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="<?php echo base_url('chamados/index?filtro_status=1') ?>">
                        <span class="info-box-icon" style="background-color: #f5f5f0; color: #1e293b;"><i class="fa fa-unlock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Em aberto</span>
                            <span class="info-box-number"><?php echo $abertos ?></span>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="<?php echo base_url('chamados/index?filtro_status=9') ?>">
                        <span class="info-box-icon" style="background-color: #f5f5f0; color: #1e293b;"><i class="fa fa-lightbulb-o"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Sugestão de Desenvolvimento</span>
                            <span class="info-box-number"><?php echo $sugestao ?></span>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="<?php echo base_url('chamados/index?filtro_status=10') ?>">
                        <span class="info-box-icon" style="background-color: #f5f5f0; color: #1e293b;"><i class="fa fa-share-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Implantação</span>
                            <span class="info-box-number"><?php echo $implantacao ?></span>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="<?php echo base_url('chamados/index?mostrar_responsaveis=1') ?>">
                        <span class="info-box-icon" style="background-color: #f5f5f0; color: #1e293b;"><i class="fa fa-chain"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Meus Chamados</span>
                            <span class="info-box-number"><?php echo $meus_chamados ?></span>
                        </div>
                    </a>
                </div>
            </div>

            <div class="clearfix visible-sm-block visible-xs-block"></div>

            <div class="col-md-12">
                <a href="<?php echo base_url('chamados/index') ?>" class="btn btn-block btn-primary btn-flat" title="Todos Chamados" alt="Todos Chamados"><i class="fa fa-list"></i> Todos Chamados</a>
                <a href="<?php echo base_url('chamados/create') ?>" class="btn btn-block btn-primary btn-flat" title="Novo Chamado" alt="Novo Chamado"><i class="fa fa-plus"></i> Novo Chamado</a>
            </div>
        </div>
    </div>
</section>

<section class="col-lg-9 connectedSortable">
    <div class="box box-primary">
        <div class="box-header with-border">
            <i class="fa fa-line-chart"></i>
            <h3 class="box-title">Chamados Abertos/Finalizados por Mês</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div class="box-body chart-responsive">
                <div class="chart" id="line-chart-chamados" style="height: 300px;"></div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</section>

<section class="col-lg-3 connectedSortable">
    <div class="box box-primary">
        <div class="box-header with-border">
            <i class="fa fa-pie-chart"></i>
            <h3 class="box-title">Média Chamados Avaliados por Mês</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div class="box-body text-center">
                <?php
                if ($media_avaliacoes <= 1)
                    $color = "#dd4b39";
                elseif ($media_avaliacoes <= 3)
                    $color = "#f39c12";
                else
                    $color = "#00a65a";
                ?>
                <form action="<?php base_url() ?>" method="get" class="form-horizontal" id="form-grafico-aval">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <?php echo form_input($filtro_range); ?>
                        </div>
                    </div>
                </form>
                <input type="text" class="knob-int" data-thickness="0.2" data-angleArc="250" data-angleOffset="-125" value="<?php echo $media_avaliacoes ?>" data-width="150" data-height="150" data-fgColor="<?php echo $color ?>" data-readonly="true" data-max="5">
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</section>

<section class="col-lg-12 connectedSortable">
    <div class="box box-primary">
        <div class="box-header with-border">
            <i class="fa fa-volume-control-phone"></i>
            <h3 class="box-title">Chamados Avaliados por Mês</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
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
                            <?php echo form_open('', array('method' => 'get', 'class' => 'form-horizontal', 'id' => 'form-avaliacoes')); ?>
                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_data', 'filtro_data', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-3">
                                            <?php echo form_input($filtro_range_aval); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo lang('filtro_unidades', 'filtro_unidades', array('class' => 'col-sm-1 control-label')); ?>
                                        <div class="col-sm-10">
                                            <?php echo form_dropdown($filtro_unidades); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 text-right">
                                        <button type="submit" name="btn_filtrar" class="btn btn-flat"><i class="fa fa-search"></i> Filtrar</button>
                                    </div>
                                </div>
                            </div>
                            <!-- /.box-body -->
                            <?php form_close(); ?>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="datatable_avaliacoes" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><?php echo lang('id'); ?></th>
                                <th><?php echo lang('titulo'); ?></th>
                                <th><?php echo lang('filial'); ?></th>
                                <th><?php echo lang('dono'); ?></th>
                                <th><?php echo lang('data_finalizacao'); ?></th>
                                <th><?php echo lang('data_avaliacao'); ?></th>
                                <th><?php echo lang('atendimento'); ?></th>
                                <th><?php echo lang('prazo'); ?></th>
                                <th><?php echo lang('satisfacao'); ?></th>
                                <th><?php echo lang('media'); ?></th>
                                <th><?php echo lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($avaliacoes as $values): ?>
                                <tr>
                                    <td><?php echo $values->id ?></td>
                                    <td><?php echo htmlspecialchars($values->titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->dono, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td data-order="<?php echo strtotime($values->data_finalizacao); ?>"><?php echo date("d/m/Y", strtotime($values->data_finalizacao)); ?></td>
                                    <td data-order="<?php echo strtotime($values->data_avaliacao); ?>"><?php echo date("d/m/Y", strtotime($values->data_avaliacao)); ?></td>
                                    <td><?php echo $values->atendimento ?></td>
                                    <td><?php echo $values->prazo ?></td>
                                    <td><?php echo $values->satisfacao ?></td>
                                    <td><?php echo round(($values->atendimento + $values->prazo + $values->satisfacao) / 3, 2) ?></td>
                                    <td><?php echo anchor("chamados/details/" . $values->id, '<i class="fa fa-eye"></i>', array('class' => 'btn btn-info', 'title' => lang('actions_view'))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer">
                <p><b>Legenda:</b> At. (Atendimento), Pr. (Prazo), Sat. (Satisfação)</p>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</section>