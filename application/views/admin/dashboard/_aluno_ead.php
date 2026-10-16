<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php if(!$has_pagamento_entrada_aluno) { ?>
  <section class="col-lg-12">
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4 style="text-align:center;"><i class="icon fa fa-ban"></i> Ops! Programe o pagamento de suas parcelas através do botão abaixo para garantir seu acesso:</h4>
      <br>
      <p style="text-align:center;"><a href="<?php echo base_url('dashboard/pagar_entrada_ead') ?>" target="_blank" class="btn btn-success btn-pagar">Realizar Pagamento</a></p>
    </div>
  </section>
<?php } else { ?>
  <?php if(!$has_pagamento_contrato_aluno) { ?>
    <section class="col-lg-12">
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4 style="text-align:center;"><i class="icon fa fa-ban"></i> Pagamento do contrato não concluído!</h4>
        <br>
        <p style="text-align:center;"><a href="<?php echo base_url('dashboard/pagar_contrato_ead') ?>" target="_blank" class="btn btn-success btn-pagar">Realizar Pagamento</a></p>
      </div>
    </section>
  <?php } ?>
<?php } ?>
<section class="col-lg-6 connectedSortable">
	<div class="box box-primary">
        <div class="box-header with-border">
        	<i class="fa fa-pie-chart"></i>
          	<h3 class="box-title">Minhas Avaliações</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <!-- /.box-header -->
        <div class="box-body text-center">
          <?php if(@$adjusted_score): ?>
        	<h3>Seu nível de proficiência em inglês é</h3>
        	<?php $per = round(( $adjusted_score * 100 ) / $possible_points); ?>
            <input type="text" class="knob" value="<?php echo $per ?>" data-thickness="0.1" data-width="100" data-height="100" data-fgColor="#0066ad" data-readonly="true">
            <div class="knob-label"><b><?php echo $total_score ?></b> de <?php echo $possible_points ?></div>

            <h1><?php echo $overall_level_name ?></h1>

            <h3>Sua proficiência em inglês por habilidade</h3>
            <div class="row">
	            <div class="col-xs-6 col-md-4 text-center">
	            	<!--<?php $per = round(( $grammar_score * 100 ) / $total_score); ?>
	            	<input type="text" class="knob" value="<?php echo $per ?>" data-thickness="0.1" data-width="100" data-height="100" data-fgColor="#0066ad" data-readonly="true">
	            	<div class="knob-label"><b><?php echo $grammar_score ?></b> de <?php echo $total_score ?></div>
	                <h4><?php echo $grammar_level_name ?></h4>-->
	                <h4 style="color:#00c076;">Grammar</h4>
	                <h4><?php echo $grammar_level_name ?></h4>
	            </div>
	            <div class="col-xs-6 col-md-4 text-center">
	            	<!--<?php $per = round(( $listening_score * 100 ) / $total_score); ?>
	                <input type="text" class="knob" value="<?php echo $per ?>" data-thickness="0.1" data-width="100" data-height="100" data-fgColor="#0066ad" data-readonly="true">
	                <div class="knob-label"><b><?php echo $listening_score ?></b> de <?php echo $total_score ?></div>
	                <h4><?php echo $listening_level_name ?></h4>-->
	                <h4 style="color:#b9429e;">Listening</h4>
	                <h4><?php echo $listening_level_name ?></h4>
	            </div>
	            <div class="col-xs-6 col-md-4 text-center">
	            	<!--<?php $per = round(( $reading_score * 100 ) / $total_score); ?>
	                <input type="text" class="knob" value="<?php echo $per ?>" data-thickness="0.1" data-width="100" data-height="100" data-fgColor="#0066ad" data-readonly="true">
	                <div class="knob-label"><b><?php echo $reading_score ?></b> de <?php echo $total_score ?></div>
	                <h4><?php echo $reading_level_name ?></h4>-->
	                <h4 style="color:#5a60d7;">Reading</h4>
	                <h4><?php echo $reading_level_name ?></h4>
	            </div>
            </div>
          <?php else: ?>
            <p>Você não possui avaliações.</p>
          <?php endif; ?>

          <?php if(@$url_login): ?>
          	<div class="box-footer clearfix">
                <button type="button" class="btn btn-primary btn-flat" data-toggle="modal" data-target="#modalAcessarEad" style="margin-top:10px;"><i class="fa fa-key" aria-hidden="true"></i> Acessar EAD</button>
          	</div>
          <?php else: ?>
          	<div class="box-footer clearfix">
              	<p style="margin-top:10px;">Seu usuário está expirado ou não possui cadastro EAD.</p>
          	</div>
          <?php endif ?>
        </div>
        <!-- /.box -->
    </div>
</section>
<section class="col-lg-6 connectedSortable">
	<!-- quick email widget -->
    <div class="box box-info">
        <div class="box-header">
          <i class="fa fa-envelope"></i>

          <h3 class="box-title">Contate seu Tutor</h3>
          <!-- tools box -->
          <div class="pull-right box-tools">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
          </div>
          <!-- /. tools -->
        </div>
      	<form action="<?php echo base_url('dashboard/index') ?>" method="post">
	        <div class="box-body">
	          	<input type="hidden" name="contato_tutor" value="1">
	          	<div class="form-group">
	                <?php echo form_dropdown($tutor);?>
	            </div>
	            <div class="form-group">
	              <input type="text" class="form-control" name="assunto" placeholder="Assunto">
	            </div>
	            <div>
	              <textarea class="textarea" placeholder="Mensagem" name="mensagem"
	                        style="width: 100%; height: 125px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
	            </div>
	        </div>
	        <div class="box-footer clearfix">
	          <button type="submit" class="pull-right btn btn-primary btn-flat">Enviar</button>
	        </div>
      	</form>
    </div>
</section>

<div id="modalAcessarEad" class="modal fade" role="dialog">
    <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-info-circle" aria-hidden="true"></i> Informação</h4>
      </div>
      <div class="modal-body">
        <img src="<?php echo base_url('assets/images/info_voxy.png') ?>" width="100%">
      </div>
      <div class="modal-footer">
        <a class="btn btn-primary btn-flat" href="<?php echo $url_login ?>" target="_blank" style="margin-top:10px;"><i class="fa fa-key" aria-hidden="true"></i> Acessar EAD</a>
      </div>
    </div>

  </div>
</div>