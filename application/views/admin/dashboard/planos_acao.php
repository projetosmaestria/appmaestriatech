<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// exit("<pre>".print_r($json_nivel_alunos,true)."</pre>");
?>
<section class="col-lg-12 connectedSortable">
	<div class="row">
		<div class="col-md-12">
	      <!-- USERS LIST -->
	      <div class="box box-danger">
	        <div class="box-header with-border">
              <i class="fa fa-users"></i>
	          <h3 class="box-title">Planos de Ação</h3>

	          <div class="box-tools pull-right">
	            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
	            </button>
	            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
	            </button>
	          </div>
	        </div>
	        <!-- /.box-header -->
	        <div class="box-body">
	        	<table id="datatable_order_id_plano_acao" class="table">
			        <thead>
			            <tr>
			                <th><?php echo lang('desc');?></th>
			                <th><?php echo lang('inicio_estimado');?></th>
			                <th><?php echo lang('termino_estimado');?></th>
			                <th><?php echo lang('inicio_real');?></th>
			                <th><?php echo lang('termino_real');?></th>
			                <th><?php echo lang('actions');?></th>
			            </tr>
			        </thead>
			        <tbody>
			        	<?php if($planos_acao->num_rows() > 0) { ?>
			        		<?php foreach($planos_acao->result() as $plano) { ?>
			        			<tr>
	                				<td><?php echo htmlspecialchars($plano->desc, ENT_QUOTES, 'UTF-8'); ?></td>
				        			<td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($plano->inicio_estimado)), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($plano->termino_estimado)), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($plano->inicio_real != "") ? date("d/m/Y H:i", strtotime($plano->inicio_real)) : "-"; ?></td>
                                    <td data-order="<?php echo $plano->termino_real ?>"><?php echo ($plano->termino_real != "") ? date("d/m/Y H:i", strtotime($plano->termino_real)) : "-"; ?></td>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><?php echo anchor("plano_acao/edit/".$plano->id, '<i class="fa fa-edit"></i>'.lang('actions_edit'), array()); ?></li>
                                                <li><?php echo anchor("plano_acao/view/".$plano->id, '<i class="fa fa-eye"></i>'.lang('actions_see'), array()); ?></li>
                                                <li><?php echo anchor("chamados/index/planoacao/".$plano->id, '<i class="fa fa-headphones"></i>'.lang('chamados_vinculados'), array()); ?></li>
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