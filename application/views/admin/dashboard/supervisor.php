<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// exit("<pre>".print_r($json_nivel_alunos,true)."</pre>");
?>

<!--<section class="col-lg-12 connectedSortable">
	<div class="row">
		<div class="col-md-12">
	      <div class="box box-success">
	        <div class="box-header with-border">
              <i class="fa fa-bar-chart"></i>
	          <h3 class="box-title">Gráficos</h3>

	          <div class="box-tools pull-right">
	            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
	            </button>
	            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
	            </button>
	          </div>
	        </div>
	        <div class="box-body">
                <div class="row">
                    <div class="form-group">
                        <?php echo lang('filtro_tutor', 'filtro_tutor', array('class' => 'col-sm-1 control-label')); ?>
                        <div class="col-sm-3 input-group">
                            <?php echo form_dropdown($tutores_filtro);?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <?php echo lang('filtro_inicio_label', 'filtro_inicio_label', array('class' => 'col-sm-1 control-label')); ?>
                        <div class="col-sm-2 input-group date" id='datetimepickerInicio'>
                            <?php echo form_input(array('id'=>'datetimepickerInicio','name'=>'data_filtro_inicio','value'=>'','class'=>'form-control date_range_filter date'));?>
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <?php echo lang('filtro_fim_label', 'filtro_fim_label', array('class' => 'col-sm-1 control-label')); ?>
                        <div class="col-sm-2 input-group date" id='datetimepickerFim'>
                            <?php echo form_input(array('id'=>'datetimepickerFim','name'=>'data_filtro_fim','value'=>'','class'=>'form-control date_range_filter date'));?>
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row">
                      <div class="col-sm-4 text-right">
                        <button type="button" name="btn_filtrar" id="btn_filtrar" class="btn btn-flat"><i class="fa fa-search"></i> Aplicar</button>
                      </div>
                </div>
	        </div>
	        <div class="box-footer">
	        	<div class="row">
	        		<div class="col-md-6">
			          <div class="box box-primary" id="box-grafico1">
			            <div class="box-header with-border">
			              <i class="fa fa-pie-chart"></i>

			              <h3 class="box-title">Níveis Alunos</h3>

			              <div class="box-tools pull-right">
			                <button type="button" class="btn btn-box-tool" id="btn-grafico1" data-widget="collapse"><i class="fa fa-minus"></i>
			                </button>
			                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
			              </div>
			            </div>
			            <div class="box-body" id="grafico1">
			            	<div class="chart-responsive col-md-8">
			            		<canvas id="pieChart" height="150"></canvas>
		            		</div>
	            			<div class="col-md-4"> 
	            				<ul class="chart-legend clearfix"> 
	            					<li><i class="fa fa-circle text-green"></i> Higher</li> 
	            					<li><i class="fa fa-circle text-yellow"></i> Higher Medium</li> 
	            					<li><i class="fa fa-circle text-orange"></i> Medium</li> 
	            					<li><i class="fa fa-circle text-red"></i> Emerging</li> 
	            				</ul> 
            				</div>
			            </div>
			          </div>
		          </div>
		          <div class="col-md-6">
			          <div class="box box-primary">
			            <div class="box-header with-border">
			              <i class="fa fa-pie-chart"></i>

			              <h3 class="box-title">Metas</h3>

			              <div class="box-tools pull-right">
			                <button type="button" class="btn btn-box-tool" id="btn-grafico2" data-widget="collapse"><i class="fa fa-minus"></i>
			                </button>
			                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
			              </div>
			            </div>
			            <div class="box-body" id="grafico2">
			            	<div class="chart-responsive col-md-12">
			            		<div class="chart" id="line-chart-supervisor" style="height: 150px;"></div>
			            	</div>
			            </div>
			          </div>
		          </div>
                </div>
	        </div>
	      </div>
	    </div>
	</div>
</section>-->

<section class="col-lg-12 connectedSortable">
	<div class="row">
		<div class="col-md-12">
	      <!-- USERS LIST -->
	      <div class="box box-info">
	        <div class="box-header with-border">
              <i class="fa fa-user"></i>
	          <h3 class="box-title">Informações Tutores</h3>

	          <div class="box-tools pull-right">
	            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
	            </button>
	            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
	            </button>
	          </div>
	        </div>
	        <!-- /.box-header -->
	        <div class="box-body">
	        	<table id="datatable" class="table">
			        <thead>
			            <tr>
			                <th><?php echo lang('tutor');?></th>
			                <th><?php echo lang('email');?></th>
			                <th><?php echo lang('ultimo_acesso');?></th>
			                <th><?php echo lang('qtd_alunos_validos');?></th>
			                <th><?php echo lang('qtd_alunos_desistentes');?></th>
			                <th><?php echo lang('actions');?></th>
			            </tr>
			        </thead>
			        <tbody>
			        	<?php if(count($detalhes_tutores) > 0) { ?>
			        		<?php foreach($detalhes_tutores as $tutor) { ?>
			        			<tr>
	                				<td><?php echo htmlspecialchars($tutor->nome_tutor, ENT_QUOTES, 'UTF-8'); ?></td>
	                				<td><?php echo htmlspecialchars($tutor->email, ENT_QUOTES, 'UTF-8'); ?></td>
				        			<td data-order="<?php echo $tutor->last_login ?>"><?php echo date('d/m/Y H:i:s', $tutor->last_login); ?></td>
				        			<td><?php echo $tutor->qtd_alunos_validos; ?></td>
				        			<td><?php echo $tutor->qtd_alunos_desistentes; ?></td>
				        			<td>
					                  <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
					                      <ul class="dropdown-menu" role="menu">
					                          <li><a href="<?php echo base_url("users/profile/".$tutor->id) ?>"><i class="fa fa-user" aria-hidden="true"></i>Ver Profile Tutor</a></li>
					                          <li><a href="<?php echo base_url("ead_tutoria/observacoes/".$tutor->id) ?>"><i class="fa fa-history" aria-hidden="true"></i>Observações</a></li>
					                      </ul>
					                  </div>
					              </td>
			        			</tr>
		        			<?php } ?>
		        		<?php } ?>
			        </tbody>
		        </table>
	        	<!-- <?php if(count($ultimo_acesso_tutores) > 0) { ?>
			          <ul class="users-list clearfix">
			          	<?php foreach($ultimo_acesso_tutores as $tutor) { ?>
				            <li>
				              	<i class="fa fa-user fa-4x"></i>
				              	<a class="users-list-name" href="<?php echo base_url('users/profile/'.$tutor->id) ?>"><?php echo $tutor->first_name." ".$tutor->last_name ?></a>
				              	<span class="users-list-date">
			              			<?php 
			              				if(date("d/m/Y", $tutor->last_login) == date("d/m/Y")) {
			              					echo "Hoje";
			              				} elseif(date("d/m/Y", $tutor->last_login) == date("d/m/Y", strtotime("-1 days"))) {
			              					echo "Ontem";
			              				} else {
			              					echo date("d/m/Y", $tutor->last_login);
			              				}
			              			 ?>				              		
			              		</span>
			              		<span class="users-list-date">
			              			Qtd Alunos (<?php echo $tutor->qtd_alunos ?>)
			              		</span>
				            </li>
			            <?php } ?>
			          </ul>
			          <!-- /.users-list -->
		        <?php } else { ?>
		        	<p>Não há tutores para apresentação.</p>
		        <?php } ?> -->
	        </div>
	        <!-- /.box-body -->
	        <!-- <div class="box-footer text-center">
	          <a href="<?php echo base_url('users') ?>" class="uppercase">Ver todos usuários</a>
	        </div> -->
	        <!-- /.box-footer -->
	      </div>
	      <!--/.box -->
	    </div>
	</div>
</section>

<section class="col-lg-12 connectedSortable">
	<div class="row">
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
                  <table id="datatable_order_id" class="table">
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
	                  <?php foreach ($cancelamentos->result() as $cancel):?>
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
</section>

<section class="col-lg-12 connectedSortable">
	<div class="row">
		<div class="col-md-12">
		    <div class="box box-info">
		        <div class="box-header with-border">
		          <i class="fa fa-file-text-o"></i>
		          <h3 class="box-title">Tutorias Últimos 10 dias</h3>

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
		                    <th><?php echo lang('tutor');?></th>
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
		          <?php foreach ($tutorias_realizadas as $tutoria):?>
		                <tr>
		                    <td><?php echo $tutoria->orc_id; ?></td>
		                    <td><?php echo $tutoria->nome_tutor; ?></td>
		                    <td><?php echo $tutoria->nome_aluno; ?></td>
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
		        <div class="box-footer text-center">
	          		<a href="<?php echo base_url('ead_tutoria/tutorias_executadas') ?>" class="uppercase">Ver todas tutorias</a>
		        </div>
		        <!-- /.box -->
		    </div>
		</div>
	</div>
</section>

<section class="col-lg-12 connectedSortable">
	<div class="row">
		<div class="col-md-12">
	      <!-- USERS LIST -->
	      <div class="box box-danger">
	        <div class="box-header with-border">
              <i class="fa fa-users"></i>
	          <h3 class="box-title">Alunos Desistentes</h3>

	          <div class="box-tools pull-right">
	            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
	            </button>
	            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
	            </button>
	          </div>
	        </div>
	        <!-- /.box-header -->
	        <div class="box-body">
	        	<table id="datatable_order_id3" class="table">
			        <thead>
			            <tr>
			                <th><?php echo lang('contrato');?></th>
			                <th><?php echo lang('aluno');?></th>
			                <th><?php echo lang('email');?></th>
			                <th><?php echo lang('ultimo_acesso');?></th>
			                <th><?php echo lang('actions');?></th>
			            </tr>
			        </thead>
			        <tbody>
			        	<?php if($desistentes->num_rows() > 0) { ?>
			        		<?php foreach($desistentes->result() as $aluno) { ?>
			        			<tr>
	                				<td><?php echo $aluno->contrato_id; ?></td>
	                				<td><?php echo htmlspecialchars($aluno->nome_aluno, ENT_QUOTES, 'UTF-8'); ?></td>
	                				<td><?php echo htmlspecialchars($aluno->email, ENT_QUOTES, 'UTF-8'); ?></td>
				        			<td data-order="<?php echo strtotime($aluno->ultimo_acesso) ?>"><?php echo date('d/m/Y', strtotime($aluno->ultimo_acesso)); ?></td>
				        			<td>
					                  <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
					                      <ul class="dropdown-menu" role="menu">
					                          <li><a href="<?php echo base_url("users/profile/".$aluno->id) ?>"><i class="fa fa-user" aria-hidden="true"></i>Ver Profile</a></li>
					                      </ul>
					                  </div>
					              </td>
			        			</tr>
		        			<?php } ?>
		        		<?php } ?>
			        </tbody>
		        </table>
	        </div>
	      </div>
	      <!--/.box -->
	    </div>
	</div>
</section>