<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper kanban">
  <section class="content-header">
      <?php echo $pagetitle; ?>
      <?php echo $breadcrumb; ?>
  </section>

  <section class="content pb-3">
    
      <?php if($this->session->flashdata('message') != "") {echo $this->session->flashdata('message');} ?>
    <div class="box">
      <?php echo form_open('', array('class' => 'form-horizontal', 'id' => 'form-kanban-filtro', 'method' => 'get')); ?>
        <input type="hidden" id="base_url" name="base_url" value="<?php echo base_url() ?>">
        <input type="hidden" id="json_data" name="json_data" value='<?php echo $json_data ?>'>
        <div class="row">
          <div class="col-sm-12">
              <div class="box box-default collapsed-box">
                  <div class="box-header with-border" style="cursor:default !important;">
                    <h3 class="box-title">Filtros</h3>

                    <div class="box-tools pull-right">
                      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                      </button>
                    </div>
                    <!-- /.box-tools -->
                  </div>
                  <!-- /.box-header -->
                  <div class="box-body">
                      <br>
                      <div class="row col-sm-12">
                        <div class="col-md-6 col-sm-12 form-group">
                            <?php echo form_dropdown($filtro_usuarios);?>
                        </div>
                      </div>
                      <div class="row col-sm-12">
                        <div class="col-lg-3 col-md-6 col-sm-12">
                          <div class="input-group date form-group" id='datetimepickerInicio'>
                              <?php 
                                echo form_input(array('placeholder'=>'Data Início','id'=>'datetimepickerInicio','name'=>'data_filtro_inicio','value'=>((@$_GET['data_filtro_inicio']) ? $_GET['data_filtro_inicio'] : date('d/m/Y', strtotime('-7 days'))),'class'=>'form-control date_range_filter date'));
                              ?>
                              <span class="input-group-addon">
                                  <i class="fa fa-calendar"></i>
                              </span>
                          </div>  
                        </div>  
                      </div>  
                      <div class="row col-sm-12">
                        <div class="col-lg-3  col-md-6 col-sm-12">
                            <div class="input-group date form-group" id='datetimepickerFim'>
                                <?php 
                                  echo form_input(array('placeholder'=>'Data Fim','id'=>'datetimepickerFim','name'=>'data_filtro_fim','value'=>((@$_GET['data_filtro_fim']) ? $_GET['data_filtro_fim'] : date('d/m/Y')),'class'=>'form-control date_range_filter date'));
                                ?>
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" id="search-btn" class="btn btn-default btn-flat" style="height: 35px;"><i class="fa fa-search"></i> Filtrar</button>
                        </div>
                      </div>
                      <!-- /.box-body -->
                  </div>
                  <!-- /.box -->
              </div>
          </div>
        </div>
      <?php form_close(); ?>

      <div class="row">
        <div class="container-fluid h-100">
          <div class="col-md-12">
            <b>Filtro(s) selecionado(s): </b>
            <?php
              if($filtros) {
                $filtro_html = "";
                foreach($filtros as $filtro) {
                  if($filtro_html != "")
                    $filtro_html .= " - ";
                  $filtro_html .= $filtro;
                }
                echo $filtro_html;
              }
            ?>
          </div>
        </div>
      </div>
      <hr>

      <div class="row">
        <div class="container-fluid h-100">
          <div class="col-md-6">
            <canvas id="myChart1"></canvas>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>