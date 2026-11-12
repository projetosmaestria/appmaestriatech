<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Abas extras do perfil para aluno do aplicativo, aluno EAD e tutor EAD (incluído por profile.php).
?>
                        <?php if(@$this->data['user_info']->is_aluno_ead) { ?>
                            <div class="chart tab-pane<?php echo $aba_extra_ativa === 'ead' ? ' active' : '' ?>" id="ead" style="position: relative;">
                                <?php //echo "<pre>".print_r($user_info->contratos->result(),true)."</pre>"; ?>
                                <div class="col-md-12">
                                    <div class="box">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Contratos do Cliente</h3>
                                        </div>
                                        <div class="box-body">
                                          <table id="datatable_order_id" class="table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo lang('id');?></th>
                                                    <th><?php echo lang('filial');?></th>
                                                    <th><?php echo lang('cliente');?></th>
                                                    <th><?php echo lang('curso');?></th>
                                                    <th><?php echo lang('status');?></th>
                                                    <th><?php echo lang('data_matricula');?></th>
                                                    <th><?php echo lang('situacao');?></th>
                                                    <th><?php echo lang('preco_produtos');?></th>
                                                    <th><?php echo lang('preco_servicos');?></th>
                                                    <th><?php echo lang('preco_total');?></th>
                                                    <th><?php echo lang('actions');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($user_info->contratos as $contrato):?>
                                                    <tr>
                                                        <td><?php echo $contrato->id; ?></td>
                                                        <td><?php echo htmlspecialchars($contrato->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars($contrato->first_name." ".$contrato->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars($contrato->curso_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars($contrato->status_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td data-order="<?php echo strtotime($contrato->data_pedido);?>"><?php echo date("d/m/Y" , strtotime($contrato->data_pedido)); ?></td>
                                                        <td><?php echo htmlspecialchars($contrato->situacao, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars("R$".number_format($contrato->total_produtos, 2, ",", "."), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars("R$".number_format($contrato->total_servicos, 2, ",", "."), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars("R$".number_format($contrato->total_geral, 2, ",", "."), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td>
                                                            <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                                                <ul class="dropdown-menu" role="menu">
                                                                    <li><a href="<?php echo base_url("users/historico/".$contrato->user_id) ?>"><i class="fa fa-history"></i>Histórico Cliente</a></li>
                                                                    <li><a href="<?php echo base_url("contratos/ver_parcelas/".$contrato->id) ?>"><i class="fa fa-bars" aria-hidden="true"></i>Parcelas</a></li>
                                                                    <?php 
                                                                        if($contrato->has_pesquisa == true) { 
                                                                            echo '<li><a href="'.base_url("pesquisa_perfil/view/".$contrato->pesquisa_perfil->id).'"><i class="fa fa-tasks" aria-hidden="true"></i>Ver Pesquisa Perfil</a></li>';
                                                                        }
                                                                        if($contrato->is_recorrencia == true) { 
                                                                            echo '<li><a href="'.base_url("iugu/cartao_recorrencia/".$contrato->id."/".$contrato->user_id).'"><i class="fa fa-credit-card" aria-hidden="true"></i>Ver Cartão da Recorrência (Iugu)</a></li>';
                                                                        }
                                                                    ?>
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
                                <div class="col-md-6">
                                    <!-- LINE CHART -->
                                    <div class="box">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Percentual atingido da meta</h3>
                                        </div>
                                        <div class="box-body chart-responsive">
                                            <?php
                                                if(count($user_info->porcentagem_meta) > 0) { 
                                                    foreach ($user_info->porcentagem_meta as $key => $porc) {
                                                        echo '<div>Orçamento #'.$key.'</div>';
                                                        echo '<div class="chart" id="line-chart_'.$key.'" style="height: 300px;"></div>';
                                                    }
                                                } else {
                                                    echo "<p>Não há registro de meta atingida até o momento.</p>";
                                                }
                                            ?>
                                        </div>
                                    <!-- /.box-body -->
                                    </div>
                                    
                                    <script type="text/javascript">

                                        window.addEventListener('load', function() {
                                            "use strict";
                                            <?php 
                                                if(count($user_info->porcentagem_meta) > 0) { 
                                                    foreach ($user_info->porcentagem_meta as $key => $porc) {
                                                        echo "var line_".$key." = new Morris.Area({element   : 'line-chart_".$key."', resize    : true, data      : ".json_encode($porc).", xkey : 'y', ykeys : ['item_".$key."'], labels : ['%'], ymin: 0, ymax: 100, gridIntegers: true, lineColors: ['#a0d0e0'], hideHover : 'auto', resize: true, yLabelFormat: function(y){return y != Math.round(y)?'':y;}, xLabelAngle: 40, setAxisAlignFirstX: true, parseTime: false }); $('#ead_tab').click(function() {line_".$key.".redraw(); $(window).trigger('resize'); return true; }); ";
                                                    }
                                                }
                                            ?>
                                        });
                                    </script>


                                </div>
                                <div class="col-md-6">
                                    <div class="box">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Últimas Auditorias</h3>
                                        </div>
                                        <div class="box-body">
                                            <?php if($user_info->timeline->num_rows() > 0) { ?>

                                                <!-- The time line -->
                                                <ul class="timeline">
                                                    <?php 
                                                        $ultima_data_registro = ""; 
                                                        foreach ($user_info->timeline->result() as $tline) { 
                                                            if($tline->DATA_REGISTRO != $ultima_data_registro) { 
                                                                echo '<li class="time-label">
                                                                    <span class="bg-red">'.date("d M. Y", strtotime($tline->DATA_REGISTRO)).'</span>
                                                                </li>';
                                                            }
                                                            echo '
                                                                <li>
                                                                  <i class="fa fa-envelope bg-blue"></i>

                                                                  <div class="timeline-item">
                                                                    <span class="time"><i class="fa fa-clock-o"></i> '.date("H:i", strtotime($tline->HORA_REGISTRO)).'</span>

                                                                    <h3 class="timeline-header"><b>'.html_escape($tline->nome).'</b> registrou uma auditoria</h3>

                                                                    <div class="timeline-body">'.$tline->MENSAGEM.'</div>
                                                                  </div>
                                                                </li>';
                                                            $ultima_data_registro = $tline->DATA_REGISTRO;
                                                        }
                                                    ?>
                                                    <li>
                                                        <i class="fa fa-clock-o bg-gray"></i>
                                                        <div class="timeline-item">
                                                            <span class="time"><a href="<?php echo base_url("users/historico/".$tline->LIG_ID_CLIENTE) ?>">ver todos</a></span>
                                                        </div>
                                                    </li>
                                                </ul>
                                            <?php } else { ?>
                                                <p>Não há auditoria até o momento.</p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if(@$this->data['user_info']->is_aluno) { ?>
                            <div class="chart tab-pane<?php echo $aba_extra_ativa === 'aplicativo' ? ' active' : '' ?>" id="aplicativo" style="position: relative;">
                                <div class="col-md-12">
                                     <div class="box">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Envio de respostas por semana</h3>
                                        </div>
                                        <div class="box-body chart-responsive">
                                          <div class="chart" id="line-chart" style="height: 300px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                     <div class="box">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Lista respostas enviadas</h3>
                                        </div>
                                        <div class="box-body">
                                          <table id="datatable" class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?php echo lang('aluno');?></th>
                                                    <th><?php echo lang('unidade');?></th>
                                                    <th><?php echo lang('data_envio');?></th>
                                                    <th><?php echo lang('corrigido');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                          <?php foreach ($this->data['user_info']->app_respostas as $resp):?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($resp->first_name." ".$resp->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($resp->unidade_livro." - ".$resp->titulo, ENT_QUOTES, 'UTF-8'); ?> - <?php echo ($resp->tipo == "C") ? 'Complementar' : 'Obrigatória'; ?></td>
                                                    <td data-order="<?php echo strtotime($resp->data_envio);?>"><?php echo date("d/m/Y", strtotime($resp->data_envio)); ?></td>
                                                    <td><?php echo (@$resp->status == "C") ? 'Sim' : "Não"; ?></td>
                                                </tr>
                                          <?php endforeach;?>
                                            </tbody>
                                          </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script type="text/javascript">
                              window.addEventListener('load', function() {
                                "use strict";
                                var current_week = getWeekNumber(new Date());
                                var i = 1;
                                var line = new Morris.Line({
                                  element: 'line-chart',
                                  resize: true,
                                  data: <?php echo $this->data['user_info']->grafico ?>,
                                  xkey: 'y',
                                  ykeys: ['qtd'],
                                  labels: ['Qtd'],
                                  xLabels: 'week',
                                  lineColors: ['#3c8dbc'],
                                  hideHover: 'auto',
                                  xLabelFormat: function(d) {
                                    return d.getDate()+'/'+(d.getMonth()+1)+'/'+d.getFullYear(); 
                                    }
                                });
                              });
                            </script>
                        <?php } ?>

                        <?php if(@$this->data['user_info']->is_tutor_ead) { ?>
                            <div class="chart tab-pane<?php echo $aba_extra_ativa === 'tutoria' ? ' active' : '' ?>" id="tutoria" style="position: relative;">
                                <div class="col-md-12">
                                  <div class="box box-default">
                                    <div class="box-header with-border">
                                      <h3 class="box-title">Nível dos Alunos</h3>

                                      <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                                      </div>
                                    </div>
                                    <!-- /.box-header -->
                                    <div class="box-body">
                                      <div class="row">
                                        <div class="col-md-4">
                                          <div class="chart-responsive">
                                            <canvas id="pieChart" height="150"></canvas>
                                          </div>
                                          <!-- ./chart-responsive -->
                                        </div>
                                        <!-- /.col -->
                                        <div class="col-md-8">
                                          <ul class="chart-legend clearfix">
                                            <li><i class="fa fa-circle text-green"></i> Higher</li>
                                            <li><i class="fa fa-circle text-yellow"></i> Higher Medium</li>
                                            <li><i class="fa fa-circle text-orange"></i> Medium</li>
                                            <li><i class="fa fa-circle text-red"></i> Emerging</li>
                                          </ul>
                                        </div>
                                        <!-- /.col -->
                                      </div>
                                      <!-- /.row -->
                                    </div>
                                  </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="box box-info">
                                        <div class="box-header with-border">
                                          <i class="fa fa-file-text-o"></i>
                                          <h3 class="box-title">Suas Últimas 30 Tutorias</h3>

                                          <div class="box-tools pull-right">
                                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                                            </button>
                                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                                          </div>
                                        </div>
                                        <div class="box-body">
                                          <table id="datatable_tutorias" class="table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo lang('contrato');?></th>
                                                    <th><?php echo lang('aluno');?></th>
                                                    <th><?php echo lang('data_hora_inicio');?></th>
                                                    <th><?php echo lang('data_hora_fim');?></th>
                                                    <th><?php echo lang('meta_porcentagem');?></th>
                                                    <!-- <th><?php echo lang('qtd_atividades');?></th> -->
                                                    <th><?php echo lang('desempenho_geral');?></th>
                                                    <th><?php echo lang('actions');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                          <?php foreach ($this->data['user_info']->tutorias_realizadas as $tutoria):?>
                                                <tr>
                                                    <td><?php echo $tutoria->orc_id; ?></td>
                                                    <td><?php echo html_escape($tutoria->nome_aluno); ?></td>
                                                    <td><?php echo date("d/m/Y H:i", strtotime($tutoria->data_hora_inicio)); ?></td>
                                                    <td><?php echo date("d/m/Y H:i", strtotime($tutoria->data_hora_fim)); ?></td>
                                                    <td><?php echo $tutoria->meta_porcentagem; ?>%</td>
                                                    <!-- <td><?php echo ($tutoria->qtd_atividades == "S") ? "Sim" : "Não"; ?></td> -->
                                                    <td><?php echo $tutoria->desempenho_geral; ?></td>
                                                    <td>
                                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                <li><?php echo anchor("ead_tutoria/edit/".$tutoria->id, '<i class="fa fa-edit"></i>'.lang('actions_edit'), array()); ?></li>
                                                                <li><?php echo anchor("ead_tutoria/view/".$tutoria->id, '<i class="fa fa-eye"></i>'.lang('actions_see'), array()); ?></li>
                                                            </ul>
                                                        </div>  
                                                    </td>
                                                </tr>
                                          <?php endforeach;?>
                                            </tbody>
                                          </table>
                                        </div>
                                        <!-- /.box -->
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="box box-info">
                                        <div class="box-header with-border">
                                          <i class="fa fa-check-square-o"></i>
                                          <h3 class="box-title">Alunos com Meta Atingida</h3>

                                          <div class="box-tools pull-right">
                                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                                            </button>
                                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                                          </div>
                                        </div>
                                        <div class="box-body">
                                          <table id="datatable_order_id2" class="table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo lang('contrato');?></th>
                                                    <th><?php echo lang('nome');?></th>
                                                    <th><?php echo lang('celular');?></th>
                                                    <th><?php echo lang('data_hora_fim');?></th>
                                                    <th><?php echo lang('meta');?></th>
                                                    <th><?php echo lang('desempenho_geral');?></th>
                                                    <th><?php echo lang('actions');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                          <?php foreach ($this->data['user_info']->alunos_meta_atingida as $aluno_meta_atingida):?>
                                                <tr>
                                                    <td><?php echo $aluno_meta_atingida->orc_id; ?></td>
                                                    <td><?php echo html_escape($aluno_meta_atingida->nome); ?></td>
                                                    <td><?php echo html_escape($aluno_meta_atingida->celular); ?></td>
                                                    <td><?php echo date("d/m/Y H:i", strtotime($aluno_meta_atingida->data_hora_fim)); ?></td>
                                                    <td><?php echo $aluno_meta_atingida->meta; ?></td>
                                                    <td><?php echo $aluno_meta_atingida->desempenho_geral; ?></td>
                                                    <td>
                                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                <li><a href="<?php echo base_url("pesquisa_perfil/view/".$aluno_meta_atingida->pesquisa_id) ?>" title="Ver Pesquisa Perfíl"><i class="fa fa-tasks"></i>Ver Pesquisa Perfíl</a></li>
                                                            </ul>
                                                        </div>   
                                                    </td>
                                                </tr>
                                          <?php endforeach;?>
                                            </tbody>
                                          </table>
                                        </div>
                                        <!-- /.box -->
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="box box-info">
                                        <div class="box-header with-border">
                                          <i class="fa fa-file-text-o"></i>
                                          <h3 class="box-title">Contratos Cancelados nos Últimos 30 dias</h3>

                                          <div class="box-tools pull-right">
                                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                                            </button>
                                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                                          </div>
                                        </div>
                                        <div class="box-body">
                                          <table id="datatable_order_id3" class="table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo lang('id');?></th>
                                                    <th><?php echo lang('filial');?></th>
                                                    <th><?php echo lang('cliente');?></th>
                                                    <th><?php echo lang('curso');?></th>
                                                    <th><?php echo lang('status');?></th>
                                                    <th><?php echo lang('data_pedido');?></th>
                                                    <th><?php echo lang('data_matricula');?></th>
                                                    <th><?php echo lang('preco_produtos');?></th>
                                                    <th><?php echo lang('preco_servicos');?></th>
                                                    <th><?php echo lang('preco_total');?></th>
                                                    <th><?php echo lang('actions');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                          <?php foreach ($this->data['user_info']->cancelamentos->result() as $cancel):?>
                                                <tr>
                                                <td><?php echo $cancel->id; ?></td>
                                                <td><?php echo htmlspecialchars($cancel->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($cancel->first_name." ".$cancel->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($cancel->curso_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($cancel->status_desc, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td data-order="<?php echo strtotime($cancel->data_pedido);?>"><?php echo date("d/m/Y" , strtotime($cancel->data_pedido)); ?></td>
                                                <?php if($cancel->data_matricula == "0000-00-00") { ?>
                                                  <td data-order="00/00/0000">-</td>
                                                <?php } else { ?>
                                                  <td data-order="<?php echo date("d/m/Y" , strtotime($cancel->data_matricula)); ?>"><?php echo date("d/m/Y" , strtotime($cancel->data_matricula)); ?></td>
                                                <?php } ?>
                                                <td><?php echo htmlspecialchars("R$".number_format($cancel->total_produtos, 2, ",", "."), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars("R$".number_format($cancel->total_servicos, 2, ",", "."), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars("R$".number_format($cancel->total_geral, 2, ",", "."), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                  <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                                      <ul class="dropdown-menu" role="menu">
                                                          <li><a href="<?php echo base_url("users/profile/".$cancel->user_id) ?>"><i class="fa fa-user" aria-hidden="true"></i>Ver Profile Cliente</a></li>
                                                          <li><a href="<?php echo base_url("users/historico/".$cancel->user_id) ?>"><i class="fa fa-history"></i>Histórico Cliente</a></li>
                                                          <li><a href="<?php echo base_url("contratos/ver_parcelas/".$cancel->id) ?>"><i class="fa fa-bars" aria-hidden="true"></i>Parcelas</a></li>
                                                      </ul>
                                                  </div>
                                              </td>
                                            </tr>
                                          <?php endforeach;?>
                                            </tbody>
                                          </table>
                                        </div>
                                        <!-- /.box -->
                                    </div>
                                </div>
                            </div>

                            <script type="text/javascript">
                                window.addEventListener('load', function() {
                                    

                                    $('#datatable_tutorias').DataTable({
                                      'paging': true, 'lengthChange': false, 'searching': true, 'ordering': true, 'info': true, 'autoWidth': false,
                                      'oLanguage': { 'sZeroRecords': 'Não foram encontrados resultados', 'sInfo': 'Mostrando de _START_ até _END_ de _TOTAL_ registros', 'sInfoEmpty': 'Mostrando de 0 até 0 de 0 registros', 'sInfoFiltered': '', 'sSearch': 'Buscar:', 'oPaginate': { 'sFirst': 'Primeiro', 'sPrevious': 'Anterior', 'sNext': 'Seguinte', 'sLast': 'Último' } },
                                      columnDefs: [{ orderable: false, targets: -1 }],
                                      'order': [[ 0, 'desc' ]]
                                    });

                                    $('#datatable_order_id2').DataTable({
                                      'paging'      : true,
                                      'lengthChange': false,
                                      'searching'   : true,
                                      'ordering'    : true,
                                      'info'        : true,
                                      'autoWidth'   : false,
                                      "oLanguage": {
                                        "sProcessing":   "Processando...",
                                        "sLengthMenu":   "Mostrar _MENU_ registros",
                                        "sZeroRecords":  "Não foram encontrados resultados",
                                        "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                                        "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
                                        "sInfoFiltered": "",
                                        "sInfoPostFix":  "",
                                        "sSearch":       "Buscar:",
                                        "sUrl":          "",
                                        "oPaginate": {
                                            "sFirst":    "Primeiro",
                                            "sPrevious": "Anterior",
                                            "sNext":     "Seguinte",
                                            "sLast":     "Último"
                                        }
                                      },
                                      columnDefs: [
                                         { orderable: false, targets: -1 }
                                      ],
                                      "order": [[ 0, 'desc' ]]
                                    });

                                    $('#datatable_order_id3').DataTable({
                                      'paging'      : true,
                                      'lengthChange': false,
                                      'searching'   : true,
                                      'ordering'    : true,
                                      'info'        : true,
                                      'autoWidth'   : false,
                                      "oLanguage": {
                                        "sProcessing":   "Processando...",
                                        "sLengthMenu":   "Mostrar _MENU_ registros",
                                        "sZeroRecords":  "Não foram encontrados resultados",
                                        "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                                        "sInfoEmpty":    "Mostrando de 0 até 0 de 0 registros",
                                        "sInfoFiltered": "",
                                        "sInfoPostFix":  "",
                                        "sSearch":       "Buscar:",
                                        "sUrl":          "",
                                        "oPaginate": {
                                            "sFirst":    "Primeiro",
                                            "sPrevious": "Anterior",
                                            "sNext":     "Seguinte",
                                            "sLast":     "Último"
                                        }
                                      },
                                      columnDefs: [
                                         { orderable: false, targets: -1 }
                                      ],
                                      "order": [[ 0, 'desc' ]]
                                    });

                                    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                                        var target = $(e.target).attr("href");
                                        if(target == "#tutoria") {
                                            var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
                                            var pieChart       = new Chart(pieChartCanvas);
                                            var PieData        = <?php echo $this->data['user_info']->nivel_alunos ?>;
                                            var pieOptions     = {
                                            segmentShowStroke    : true,
                                            segmentStrokeColor   : '#fff',
                                            segmentStrokeWidth   : 1,
                                            percentageInnerCutout: 50, 
                                            animationSteps       : 100,
                                            animationEasing      : 'easeOutBounce',
                                            animateRotate        : true,
                                            animateScale         : false,
                                            responsive           : true,
                                            maintainAspectRatio  : false,
                                            legendTemplate       : '<ul class=\'<%=name.toLowerCase()%>-legend\'><% for (var i=0; i<segments.length; i++){%><li><span style=\'background-color:<%=segments[i].fillColor%>\'></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',
                                            tooltipTemplate      : '<%=value %> aluno(s) <%=label%>'
                                            };
                                            pieChart.Doughnut(PieData, pieOptions);
                                        }
                                    });
                                });
                            </script>
                        <?php } ?>
