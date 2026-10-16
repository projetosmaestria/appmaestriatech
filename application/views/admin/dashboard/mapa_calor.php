<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<section class="col-lg-12 connectedSortable">
    <div class="box box-primary">
        <div class="box-header with-border">
            <i class="fa fa-map-marker"></i>
            <h3 class="box-title">Mapa de Localização dos Alunos</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
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
                        <div class="form-group">
                            <label for="filtro_status_label" class="col-sm-1 control-label">Status</label>
                            <div class="col-md-4 input-group">
                                <select name="filtro_status" id="filtro_status" class="form-control" value="">
                                    <option value=""></option>
                                    <?php foreach ($status->result() as $status) { ?>
                                        <option value="<?php echo $status->id ?>"><?php echo $status->descricao ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="filtro_idade_label" class="col-sm-1 control-label">Idade</label>
                            <div class="col-md-4 input-group">
                                <div class="radio">
                                    <label for="idade_todas"><input id="idade_todas" type="radio" name="idade" value="" checked>todas</label>
                                </div>
                                <div class="radio">
                                    <label for="idade_15"><input id="idade_15" type="radio" name="idade" value="0">até 15 anos</label>
                                </div>
                                <div class="radio">
                                    <label for="idade_16"><input id="idade_16" type="radio" name="idade" value="1">16 a 18 anos</label>
                                </div>
                                <div class="radio">
                                    <label for="idade_19"><input id="idade_19" type="radio" name="idade" value="2">19 a 23 anos</label>
                                </div>
                                <div class="radio">
                                    <label for="idade_24"><input id="idade_24" type="radio" name="idade" value="3">24 a 29 anos</label>
                                </div>
                                <div class="radio">
                                    <label for="idade_30"><input id="idade_30" type="radio" name="idade" value="4">30 a 35 anos</label>
                                </div>
                                <div class="radio">
                                    <label for="idade_36"><input id="idade_36" type="radio" name="idade" value="5">36 a 50 anos</label>
                                </div>
                                <div class="radio">
                                    <label for="idade_51"><input id="idade_51" type="radio" name="idade" value="6">acima de 51 anos</label>
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                      </div>
                      <!-- /.box -->
                    </div>
                </div>
                <div class="col-md-12">
                    <div id="map-canvas" style="width:100%; height: 500px;"></div>
                    <div id="mapa" style="height: auto;"></div>
                </div>
        </div>
    </div>
</section>
<script>
    var markersData = <?php echo $alunos; ?>;
</script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyABGth5fS35eiLEK8XtjYxOnEQv-IYAiPU"></script>
<script src="<?php echo base_url('assets/plugins/googlemap/map.js'); ?>"></script>